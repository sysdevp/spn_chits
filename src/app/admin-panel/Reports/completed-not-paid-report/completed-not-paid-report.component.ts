import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-completed-not-paid-report',
  templateUrl: './completed-not-paid-report.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class CompletedNotPaidReportComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  schemeList: any[] = [];
  f: any = { branch_id: '', scheme_id: '', search: '', include_paid: false };
  rows: any[] = [];
  totals: any = {};
  loading = false;
  money = inr;

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.f.branch_id = this.lockedBranch;
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId }, 'get-schemes', 'POST')
      .subscribe((res: any) => { this.schemeList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  schemeLabel(s: any) {
    return inr(s.chit_value) + ' / ' + (s.no_of_months || '') + 'M' + (s.chit_letter ? ' ' + s.chit_letter : '');
  }

  load() {
    this.loading = true;
    this.userservice.getData({
      tenant_id: this.tenantId,
      branch_id: this.lockedBranch || this.f.branch_id,
      scheme_id: this.f.scheme_id,
      include_paid: this.f.include_paid ? 1 : 0
    }, 'completed-not-paid-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      const ok = res && res.status === 'Success';
      this.rows = ok ? res.data || [] : [];
      this.totals = ok ? res.totals || {} : {};
      if (!ok) {
        this.appService.showAlert('Completed – Not Paid', (res && res.msg) || 'Unable to load', 'error');
      }
    }, () => { this.loading = false; });
  }

  get visible() {
    const s = (this.f.search || '').toLowerCase().trim();
    return !s ? this.rows : this.rows.filter((r) =>
      [r.customer_name, r.customer_code, r.mobile, r.ar_no, r.branch_name].join(' ').toLowerCase().indexOf(s) >= 0);
  }

  statusLabel(s: string) {
    return ({ not_requested: 'Not requested', draft: 'Draft', pending_chit_admin: 'Chit admin approval',
      pending_field: 'Field verification', pending_accounts: 'Accounts payout', pending_admin: 'Accounts payout' } as any)[s] || s;
  }

  exportCsv() {
    if (!this.visible.length) {
      this.appService.showAlert('Completed – Not Paid', 'Nothing to export', 'error');
      return;
    }
    csvDownload('completed-not-paid.csv',
      ['Customer', 'Code', 'Mobile', 'AR No', 'Branch', 'Scheme', 'Months', 'Completed on', 'Customer paid', 'Last month due',
        'Payable (we give)', 'Difference', 'Given so far', 'Pending payout', 'Days waiting', 'Request'],
      this.visible.map((r) => [r.customer_name, r.customer_code, r.mobile, r.ar_no, r.branch_name, r.scheme, r.months, r.completed_on,
        r.customer_paid, r.last_month_due, r.payable, r.difference, r.given, r.pending_payout, r.days_waiting, this.statusLabel(r.request_status)]));
  }
}
