import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-employee-advance',
  templateUrl: './employee-advance.component.html',
  styles: [`
    .ea-hero { background: linear-gradient(120deg, #004D2A 0%, #007A49 60%, #18A957 100%); color: #fff; border-radius: 18px; padding: 18px 22px; margin-bottom: 14px; }
    .ea-hero h1 { margin: 0 0 4px; font-size: 26px; font-weight: 800; color: #fff; }
    .ea-hero p { margin: 0; color: rgba(255,255,255,.88); }
    .ea-card { background: #fff; border-radius: 16px; padding: 14px; margin-bottom: 14px; box-shadow: 0 6px 18px rgba(7,29,58,.05); }
    .ea-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 12px; align-items: end; }
    .ea-grid label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; text-transform: uppercase; }
    .ea-grid select, .ea-grid input { width: 100%; height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; }
    .ea-btn { border: 0; border-radius: 10px; height: 40px; padding: 0 16px; font-weight: 800; cursor: pointer; background: #006B3C; color: #fff; }
    .ea-btn.light { background: #fff; color: #006B3C; border: 1px solid #006B3C; }
    .ea-btn.sm { height: 28px; padding: 0 10px; font-size: 12px; margin-right: 4px; }
    .ea-btn.red { background: #DC2626; }
    .ea-btn.blue { background: #1687E8; }
    .ea-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
    .ea-kpi { border-radius: 16px; padding: 14px; }
    .ea-kpi span { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #5c6b80; }
    .ea-kpi b { font-size: 22px; color: #071D3A; }
    .ea-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ea-table th { background: #f3faf6; color: #004D2A; font-size: 12px; padding: 9px 10px; text-align: left; white-space: nowrap; }
    .ea-table td { padding: 8px 10px; border-bottom: 1px solid #eef3f0; }
    .pill { border-radius: 999px; padding: 3px 10px; font-weight: 800; font-size: 12px; text-transform: capitalize; }
    .s-pending { background: #FFF8E8; color: #92400E; } .s-approved { background: #EDF6FF; color: #1D4ED8; }
    .s-disbursed { background: #E9FFF3; color: #006B3C; } .s-recovered { background: #F4EEFF; color: #6D28D9; }
    .s-rejected { background: #FEF2F2; color: #DC2626; }
    @media (max-width: 900px) { .ea-kpis { grid-template-columns: 1fr 1fr; } }
  `]
})
export class EmployeeAdvanceComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id');
  branchList: any[] = [];
  employeeList: any[] = [];
  rows: any[] = [];
  loading = false;
  saving = false;
  filters: any = { branch_id: '', request_status: '', request_type: '' };
  form: any = this.blankForm();

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  blankForm() {
    return { branch_id: '', employee_id: '', request_type: 'advance', amount: '', tenure_months: '', emi_amount: '', request_date: new Date().toISOString().slice(0, 10), reason: '' };
  }

  formBranchChg() {
    this.form.employee_id = '';
    this.employeeList = [];
    if (!this.form.branch_id) {
      return;
    }
    this.userservice.getData({ 'conditions[branch_id]': this.form.branch_id }, 'get-employees', 'POST')
      .subscribe((res: any) => { this.employeeList = res && res.status === 'Success' ? res.data : []; });
  }

  calcEmi() {
    const amt = Number(this.form.amount) || 0;
    const months = Number(this.form.tenure_months) || 0;
    this.form.emi_amount = months > 0 ? Math.ceil(amt / months) : '';
  }

  load() {
    this.loading = true;
    const body = Object.assign({ tenant_id: this.tenantId, for_admin: 1 }, this.filters);
    this.userservice.getData(body, 'list-employee-advances', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.rows = res && res.status === 'Success' ? (res.data || []) : [];
    }, () => { this.loading = false; this.rows = []; });
  }

  save() {
    if (!this.form.employee_id || !(Number(this.form.amount) > 0)) {
      this.appService.showAlert('Employee Advance', 'Select employee and enter amount', 'error');
      return;
    }
    this.saving = true;
    const body = Object.assign({ tenant_id: this.tenantId, created_by: localStorage.getItem('logged_employee_id') }, this.form);
    this.userservice.getData(body, 'store-employee-advance', 'JSON').subscribe((res: any) => {
      this.saving = false;
      if (res && res.status === 'Success') {
        this.appService.showAlert('Employee Advance', 'Request saved', 'success');
        this.form = this.blankForm();
        this.employeeList = [];
        this.load();
      } else {
        this.appService.showAlert('Employee Advance', (res && res.msg) || 'Unable to save', 'error');
      }
    }, () => { this.saving = false; });
  }

  decide(row: any, status: string) {
    const remarks = status === 'rejected' ? prompt('Reason for rejection') : '';
    if (status === 'rejected' && !remarks) {
      return;
    }
    this.userservice.getData({
      id: row.id, request_status: status, decision_remarks: remarks || '',
      decided_by: localStorage.getItem('logged_employee_id'), updated_by: localStorage.getItem('logged_employee_id')
    }, 'decide-employee-advance', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        row.request_status = status;
        row.decision_remarks = remarks || '';
      } else {
        this.appService.showAlert('Employee Advance', (res && res.msg) || 'Unable to update', 'error');
      }
    });
  }

  sum(statuses: string[]) {
    return this.rows.filter((r) => statuses.indexOf(r.request_status) >= 0).reduce((s, r) => s + Number(r.amount || 0), 0);
  }

  count(status: string) {
    return this.rows.filter((r) => r.request_status === status).length;
  }

  money(v: any) {
    return '₹' + Math.round(Number(v) || 0).toLocaleString('en-IN');
  }
}
