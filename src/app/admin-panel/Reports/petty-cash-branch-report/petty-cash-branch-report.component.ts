import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-petty-cash-branch-report',
  templateUrl: './petty-cash-branch-report.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class PettyCashBranchReportComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  f: any = { branch_id: '', from_date: '', to_date: '', category: '', kind: '' };
  rows: any[] = [];
  branches: any[] = [];
  types: any[] = [];
  totals: any = {};
  loading = false;
  money = inr;

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const now = new Date();
    this.f.from_date = new Date(now.getFullYear(), now.getMonth(), 1, 12).toISOString().slice(0, 10);
    this.f.to_date = now.toISOString().slice(0, 10);
    this.f.branch_id = this.lockedBranch;
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  load() {
    this.loading = true;
    this.userservice.getData({ tenant_id: this.tenantId, branch_id: this.lockedBranch || this.f.branch_id, from_date: this.f.from_date,
      to_date: this.f.to_date, category: this.f.category }, 'petty-cash-branch-report', 'JSON').subscribe((res: any) => {
        this.loading = false;
        const ok = res && res.status === 'Success';
        this.rows = ok ? res.data || [] : [];
        this.branches = ok ? res.branches || [] : [];
        this.types = ok ? res.types || [] : [];
        this.totals = ok ? res.totals || {} : {};
        if (!ok) {
          this.appService.showAlert('Petty Cash Branch Report', (res && res.msg) || 'Unable to load', 'error');
        }
      }, () => { this.loading = false; });
  }

  get visible() {
    return this.rows.filter((r) => !this.f.kind || r.kind === this.f.kind);
  }

  width(v: number, list: any[], key: string) {
    const max = list.reduce((m, x) => Math.max(m, Number(x[key]) || 0), 1);
    return Math.round(((Number(v) || 0) / max) * 100);
  }

  exportCsv() {
    csvDownload('petty-cash-branch.csv', ['Date', 'Branch', 'Type', 'Head', 'Mode', 'Employee', 'Reference', 'Amount'],
      this.visible.map((r) => [r.date, r.branch_name, r.kind, r.category, r.mode, r.employee_name, r.ref, r.amount]));
  }
}
