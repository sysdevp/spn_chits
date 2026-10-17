import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SCORE_BANDS } from '../../settings/customer-score.rules';

@Component({
  selector: 'app-customer-category-report',
  templateUrl: './customer-category-report.component.html',
  styles: [`
    .cc-hero { background: linear-gradient(120deg, #004D2A 0%, #007A49 60%, #18A957 100%); color: #fff; border-radius: 18px; padding: 18px 22px; margin-bottom: 14px; }
    .cc-hero h1 { margin: 0 0 4px; font-size: 26px; font-weight: 800; color: #fff; }
    .cc-hero p { margin: 0; color: rgba(255,255,255,.88); }
    .cc-filters { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; background: #fff; border-radius: 16px; padding: 14px; margin-bottom: 14px; box-shadow: 0 6px 18px rgba(7,29,58,.05); }
    .cc-filters label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; text-transform: uppercase; }
    .cc-filters select { height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; min-width: 200px; }
    .cc-btn { border: 0; border-radius: 10px; height: 40px; padding: 0 16px; font-weight: 800; cursor: pointer; background: #006B3C; color: #fff; }
    .cc-btn.light { background: #fff; color: #006B3C; border: 1px solid #006B3C; }
    .cc-kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 14px; }
    .cc-kpi { border-radius: 16px; padding: 14px; cursor: pointer; border: 2px solid transparent; }
    .cc-kpi.active { border-color: #071D3A; }
    .cc-kpi span { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #5c6b80; }
    .cc-kpi b { display: block; font-size: 26px; color: #071D3A; }
    .cc-kpi small { color: #5c6b80; font-weight: 600; }
    .k-all { background: #EDF6FF; } .k-Excellent { background: #E9FFF3; } .k-Good { background: #F0FDF4; } .k-Medium { background: #FFF8E8; } .k-Bad { background: #FFF0F0; }
    .cc-table { background: #fff; border-radius: 16px; padding: 10px; box-shadow: 0 6px 18px rgba(7,29,58,.05); overflow-x: auto; }
    .cc-table table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .cc-table th { background: #f3faf6; color: #004D2A; font-size: 12px; padding: 9px 10px; text-align: left; white-space: nowrap; }
    .cc-table td { padding: 9px 10px; border-bottom: 1px solid #eef3f0; }
    .pill { border-radius: 999px; padding: 3px 10px; font-weight: 800; font-size: 12px; }
    .g-Excellent { background: #E9FFF3; color: #006B3C; } .g-Good { background: #F0FDF4; color: #008A4C; }
    .g-Medium { background: #FFF8E8; color: #92400E; } .g-Bad { background: #FEF2F2; color: #DC2626; }
    .cc-empty { padding: 28px; text-align: center; color: #5c6b80; }
    .cc-rule { font-size: 12px; color: #5c6b80; margin-top: 10px; }
    @media (max-width: 900px) { .cc-kpis { grid-template-columns: 1fr 1fr; } }
  `]
})
export class CustomerCategoryReportComponent implements OnInit {
  branchList: any[] = [];
  schemeList: any[] = [];
  branchId = '';
  schemeId = '';
  grade = '';
  rows: any[] = [];
  summary: any = { Excellent: 0, Good: 0, Medium: 0, Bad: 0 };
  total = 0;
  loading = false;
  grades = SCORE_BANDS;

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const tenant_id = localStorage.getItem('tenant_id');
    this.userservice.getData({ 'conditions[tenant_id]': tenant_id, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => {
        this.branchList = res && res.status === 'Success' ? res.data : [];
        this.load();
      }, () => this.load());
    this.userservice.getData({ 'conditions[tenant_id]': tenant_id }, 'get-schemes', 'POST')
      .subscribe((res: any) => { this.schemeList = res && res.status === 'Success' ? res.data : []; });
  }

  load() {
    this.loading = true;
    const body: any = { tenant_id: localStorage.getItem('tenant_id'), scheme_id: this.schemeId, grade: this.grade };
    if (this.branchId) {
      body.branch_id = this.branchId;
    } else {
      body.branch_ids = this.branchList.map((b) => b.id);
    }
    this.userservice.getData(body, 'customer-category-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.rows = res.data || [];
        this.summary = res.summary || this.summary;
        this.total = res.total_customers || 0;
      } else {
        this.rows = [];
        this.appService.showAlert('Customer Category', (res && res.msg) || 'Unable to load report', 'error');
      }
    }, () => {
      this.loading = false;
      this.rows = [];
    });
  }

  pick(grade: string) {
    this.grade = this.grade === grade ? '' : grade;
    this.load();
  }

  money(v: any) {
    return '₹' + Math.round(Number(v) || 0).toLocaleString('en-IN');
  }

  exportCsv() {
    if (!this.rows.length) {
      return;
    }
    const head = ['S.No', 'Customer', 'Code', 'Mobile', 'Branch', 'Schemes', 'Chits', 'Paid', 'Pending', 'Late Dues', 'Score', 'Category'];
    const lines = [head.join(',')].concat(this.rows.map((r) => [
      r.sno, r.customer, r.customer_code, r.mobile, r.branch, r.schemes, r.chits, r.paid, r.pending, r.late_installments, r.score, r.grade
    ].map((v) => '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"').join(',')));
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'customer-category-report.csv';
    a.click();
  }
}
