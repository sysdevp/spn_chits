import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';
import { isAdminLogin } from '../../../utils/login-role.util';

@Component({
  selector: 'app-cash-box',
  templateUrl: './cash-box.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class CashBoxComponent implements OnInit {
  tab: 'box' | 'approvals' | 'income' = 'box';
  tenantId = localStorage.getItem('tenant_id') || '';
  employeeId = localStorage.getItem('logged_employee_id') || '';
  lockedBranch = loginBranchId();
  isAdmin = isAdminLogin();
  branchList: any[] = [];
  f: any = { branch_id: '', from_date: '', to_date: '', mode: '' };
  data: any = { summary: { cash: {}, bank: {} }, days: [], entries: [], pending: [], history: [] };
  loading = false;
  money = inr;
  showForm = false;
  entry: any = {};
  remarks: any = {};
  kindFilter = '';

  constructor(private userservice: UserService, private appService: AppService, private route: ActivatedRoute) {}

  get title() {
    return ({ box: 'My Cash Box', approvals: 'Cash Box Approvals', income: 'Income Cash Box Report' } as any)[this.tab];
  }

  ngOnInit() {
    const t = this.route.snapshot.data && this.route.snapshot.data.tab;
    this.tab = t === 'approvals' || t === 'income' ? t : 'box';
    const now = new Date();
    this.f.from_date = this.iso(new Date(now.getFullYear(), now.getMonth(), 1));
    this.f.to_date = this.iso(now);
    this.f.mode = this.tab === 'box' ? 'cash' : '';
    this.f.branch_id = this.lockedBranch;
    this.resetEntry();
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: this.employeeId }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  iso(d: Date) {
    return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
  }

  resetEntry() {
    this.entry = { direction: 'in', mode: 'cash', amount: '', title: '', remarks: '', entry_date: this.iso(new Date()), branch_id: this.lockedBranch };
  }

  load() {
    this.loading = true;
    this.userservice.getData({
      tenant_id: this.tenantId,
      branch_id: this.lockedBranch || this.f.branch_id,
      from_date: this.f.from_date,
      to_date: this.f.to_date,
      mode: this.f.mode
    }, 'cash-box-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.data = res;
      } else {
        this.appService.showAlert(this.title, (res && res.msg) || 'Unable to load', 'error');
      }
    }, () => { this.loading = false; });
  }

  get entries() {
    return (this.data.entries || []).filter((e) => !this.kindFilter || e.kind === this.kindFilter || e.bucket === this.kindFilter);
  }

  incomeTotal(side: string) {
    const b = (this.data.summary && this.data.summary[side]) || {};
    return Object.keys(b).reduce((s, k) => s + (Number(b[k]) || 0), 0);
  }

  submitEntry() {
    const branch = this.lockedBranch || this.entry.branch_id;
    if (!branch) {
      this.appService.showAlert('Cash Box', 'Select the branch', 'error');
      return;
    }
    this.userservice.getData({ ...this.entry, branch_id: branch, tenant_id: this.tenantId, employee_id: this.employeeId }, 'cash-box-request', 'JSON')
      .subscribe((res: any) => {
        const ok = res && res.status === 'Success';
        this.appService.showAlert('Cash Box', (res && res.msg) || 'Failed', ok ? 'success' : 'error');
        if (ok) {
          this.showForm = false;
          this.resetEntry();
          this.load();
        }
      });
  }

  decide(row: any, decision: string) {
    this.userservice.getData({ id: row.id, decision, remarks: this.remarks[row.id] || '', employee_id: this.employeeId, tenant_id: this.tenantId },
      'cash-box-decide', 'JSON').subscribe((res: any) => {
        const ok = res && res.status === 'Success';
        this.appService.showAlert('Cash Box Approval', (res && res.msg) || 'Failed', ok ? 'success' : 'error');
        if (ok) {
          this.load();
        }
      });
  }

  sourceLabel(s: string) {
    return ({ manual: 'Cash entry', payout: 'Payout', petrol: 'Petrol allowance' } as any)[s] || s;
  }

  exportCsv() {
    if (this.tab === 'income') {
      csvDownload('income-cash-box.csv', ['Date', 'Collection', 'RF', 'My cash', 'Other', 'Total income', 'Expense', 'Running balance'],
        (this.data.days || []).map((d) => [d.date, d.collection, d.rf, d.mycash, d.other, d.income, d.expense, d.balance]));
      return;
    }
    csvDownload('cash-box.csv', ['Date', 'Type', 'Category', 'Mode', 'Amount', 'Branch', 'Reference', 'Employee'],
      this.entries.map((e) => [e.date, e.kind, e.category, e.mode, e.amount, e.branch_name, e.ref, e.employee_name]));
  }
}
