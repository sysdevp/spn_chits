import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../../Reports/spn-report.styles';

@Component({
  selector: 'app-employee-allowance',
  templateUrl: './employee-allowance.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class EmployeeAllowanceComponent implements OnInit {
  mode: 'day' | 'petrol' = 'day';
  tenantId = localStorage.getItem('tenant_id') || '';
  employeeId = localStorage.getItem('logged_employee_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  employeeList: any[] = [];
  f: any = { branch_id: '', employee_id: '', from_date: '', to_date: '' };
  view: 'day' | 'month' | 'employee' = 'day';
  rows: any[] = [];
  employees: any[] = [];
  months: any[] = [];
  totals: any = {};
  selected: any = {};
  loading = false;
  money = inr;
  showForm = false;
  form: any = {};

  constructor(private userservice: UserService, private appService: AppService, private route: ActivatedRoute) {}

  get title() {
    return this.mode === 'petrol' ? 'Petrol Allowance' : 'Day Allowance Report';
  }

  ngOnInit() {
    this.mode = this.route.snapshot.data && this.route.snapshot.data.mode === 'petrol' ? 'petrol' : 'day';
    const now = new Date();
    this.f.from_date = new Date(now.getFullYear(), now.getMonth(), 1, 12).toISOString().slice(0, 10);
    this.f.to_date = now.toISOString().slice(0, 10);
    this.f.branch_id = this.lockedBranch;
    this.resetForm();
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: this.employeeId }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.loadEmployees();
    this.load();
  }

  resetForm() {
    this.form = { id: '', employee_id: '', allowance_date: new Date().toISOString().slice(0, 10), km: '', rate: '', amount: '', mode: 'cash', remarks: '' };
  }

  loadEmployees() {
    const body: any = { 'conditions[tenant_id]': this.tenantId };
    const branch = this.lockedBranch || this.f.branch_id;
    if (branch) {
      body['conditions[branch_id]'] = branch;
    }
    this.userservice.getData(body, 'get-employees', 'POST')
      .subscribe((res: any) => { this.employeeList = res && res.status === 'Success' ? res.data : []; });
  }

  branchChg() {
    this.f.employee_id = '';
    this.loadEmployees();
  }

  load() {
    this.loading = true;
    this.selected = {};
    const body = { tenant_id: this.tenantId, branch_id: this.lockedBranch || this.f.branch_id, employee_id: this.f.employee_id,
      from_date: this.f.from_date, to_date: this.f.to_date };
    this.userservice.getData(body, this.mode === 'petrol' ? 'petrol-allowances' : 'day-allowance-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      const ok = res && res.status === 'Success';
      this.rows = ok ? res.data || [] : [];
      this.employees = ok ? res.employees || [] : [];
      this.months = ok ? res.months || [] : [];
      this.totals = ok ? res.totals || {} : {};
      if (!ok) {
        this.appService.showAlert(this.title, (res && res.msg) || 'Unable to load', 'error');
      }
    }, () => { this.loading = false; });
  }

  empName(e: any) {
    return ((e.first_name || '') + ' ' + (e.last_name || '')).trim();
  }

  // ---- day allowance
  key(r: any) {
    return r.employee_id + ':' + r.date;
  }

  get unpaidSelected() {
    return this.rows.filter((r) => !r.paid && this.selected[this.key(r)]);
  }

  selectAllUnpaid() {
    this.rows.filter((r) => !r.paid).forEach((r) => (this.selected[this.key(r)] = true));
  }

  markPaid() {
    const items = this.unpaidSelected.map((r) => ({ employee_id: r.employee_id, date: r.date, amount: r.amount }));
    if (!items.length) {
      this.appService.showAlert(this.title, 'Select unpaid days first', 'error');
      return;
    }
    this.userservice.getData({ tenant_id: this.tenantId, items, created_by: this.employeeId, mode: 'cash' }, 'mark-day-allowance-paid', 'JSON')
      .subscribe((res: any) => {
        const ok = res && res.status === 'Success';
        this.appService.showAlert(this.title, (res && res.msg) || 'Failed', ok ? 'success' : 'error');
        if (ok) {
          this.load();
        }
      });
  }

  saveRate(e: any) {
    this.userservice.getData({ tenant_id: this.tenantId, employee_id: e.employee_id, day_allowance: e.rate }, 'save-day-allowance-rate', 'JSON')
      .subscribe((res: any) => {
        const ok = res && res.status === 'Success';
        this.appService.showAlert(this.title, (res && res.msg) || 'Failed', ok ? 'success' : 'error');
      });
  }

  // ---- petrol
  calcAmount() {
    const km = Number(this.form.km) || 0;
    const rate = Number(this.form.rate) || 0;
    if (km && rate) {
      this.form.amount = Math.round(km * rate * 100) / 100;
    }
  }

  edit(r: any) {
    this.form = { id: r.id, employee_id: r.employee_id, allowance_date: r.allowance_date, km: r.km, rate: r.rate, amount: r.amount, mode: r.mode, remarks: r.remarks };
    this.showForm = true;
  }

  save() {
    this.userservice.getData({ ...this.form, tenant_id: this.tenantId, branch_id: this.lockedBranch || this.f.branch_id, created_by: this.employeeId },
      'save-petrol-allowance', 'JSON').subscribe((res: any) => {
        const ok = res && res.status === 'Success';
        this.appService.showAlert(this.title, (res && res.msg) || 'Failed', ok ? 'success' : 'error');
        if (ok) {
          this.showForm = false;
          this.resetForm();
          this.load();
        }
      });
  }

  remove(r: any) {
    if (!confirm('Delete this petrol allowance entry?')) {
      return;
    }
    this.userservice.getData({ id: r.id, tenant_id: this.tenantId }, 'delete-petrol-allowance', 'JSON').subscribe(() => this.load());
  }

  exportCsv() {
    if (this.mode === 'petrol') {
      csvDownload('petrol-allowance.csv', ['Date', 'Employee', 'Branch', 'KM', 'Rate', 'Amount', 'Mode', 'Remarks', 'Entered by'],
        this.rows.map((r) => [r.allowance_date, r.employee_name, r.branch_name, r.km, r.rate, r.amount, r.mode, r.remarks, r.created_by_name]));
      return;
    }
    csvDownload('day-allowance.csv', ['Date', 'Employee', 'Branch', 'Amount', 'Paid'],
      this.rows.map((r) => [r.date, r.employee_name, r.branch_name, r.amount, r.paid ? 'Yes' : 'No']));
  }
}
