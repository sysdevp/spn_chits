import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-branch-income-expense',
  templateUrl: './branch-income-expense.component.html',
  styles: [SPN_REPORT_STYLES + `
    .sr-insight.good { background: #E9FFF3; border-left-color: #15803D; }
    .sr-insight.bad { background: #FFF0F4; border-left-color: #DC2626; }
    .sr-insight.warn { background: #FFF8E8; border-left-color: #d97706; }
  `]
})
export class BranchIncomeExpenseComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  f: any = { branch_id: '', from_date: '', to_date: '' };
  rows: any[] = [];
  categories: any[] = [];
  insights: any[] = [];
  totals: any = {};
  meta: any = {};
  open: any = {};
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
    this.userservice.getData({ tenant_id: this.tenantId, branch_id: this.lockedBranch || this.f.branch_id, from_date: this.f.from_date, to_date: this.f.to_date },
      'branch-income-expense-report', 'JSON').subscribe((res: any) => {
        this.loading = false;
        const ok = res && res.status === 'Success';
        this.rows = ok ? res.data || [] : [];
        this.categories = ok ? res.categories || [] : [];
        this.insights = ok ? res.insights || [] : [];
        this.totals = ok ? res.totals || {} : {};
        this.meta = ok ? { prev_from: res.prev_from, prev_to: res.prev_to } : {};
        if (!ok) {
          this.appService.showAlert('Branch Income & Expense', (res && res.msg) || 'Unable to load', 'error');
        }
      }, () => { this.loading = false; });
  }

  lines(obj: any) {
    return Object.keys(obj || {}).map((k) => ({ name: k, amount: obj[k] }));
  }

  width(v: number, list: any[], key: string) {
    const max = list.reduce((m, x) => Math.max(m, Number(x[key]) || 0), 1);
    return Math.round(((Number(v) || 0) / max) * 100);
  }

  change(v: any) {
    return v === null || v === undefined ? '—' : (v > 0 ? '+' : '') + v + '%';
  }

  exportCsv() {
    csvDownload('branch-income-expense.csv',
      ['Branch', 'Income', 'Expense', 'Net', 'Expense % of income', 'Prev income', 'Prev expense', 'Income change %', 'Top expense'],
      this.rows.map((r) => [r.branch_name, r.income, r.expense, r.net, r.expense_ratio, r.prev_income, r.prev_expense, r.income_change, r.top_expense]));
  }
}
