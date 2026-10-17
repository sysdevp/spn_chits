import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-spn-auction-report',
  templateUrl: './spn-auction-report.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class SpnAuctionReportComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  schemeList: any[] = [];
  f: any = { branch_id: '', scheme_id: '', month: new Date().toISOString().slice(0, 7), auction_date: '' };
  rows: any[] = [];
  schemes: any[] = [];
  months: any[] = [];
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

  load() {
    this.loading = true;
    this.userservice.getData({
      tenant_id: this.tenantId,
      branch_id: this.lockedBranch || this.f.branch_id,
      scheme_id: this.f.scheme_id,
      month: this.f.auction_date ? '' : this.f.month,
      auction_date: this.f.auction_date
    }, 'spn-auction-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      const ok = res && res.status === 'Success';
      this.rows = ok ? res.data || [] : [];
      this.schemes = ok ? res.schemes || [] : [];
      this.months = ok ? res.months || [] : [];
      this.totals = ok ? res.totals || {} : {};
    }, () => { this.loading = false; });
  }

  reset() {
    this.f = { branch_id: this.lockedBranch, scheme_id: '', month: new Date().toISOString().slice(0, 7), auction_date: '' };
    this.load();
  }

  schemeLabel(s: any) {
    return inr(s.chit_value) + ' / ' + (s.no_of_months || '') + 'M' + (s.chit_letter ? ' ' + s.chit_letter : '');
  }

  width(v: number, list: any[], key: string) {
    const max = list.reduce((m, x) => Math.max(m, Number(x[key]) || 0), 1);
    return Math.round(((Number(v) || 0) / max) * 100);
  }

  payLabel(s: string) {
    return ({ approved: 'Paid', rejected: 'Rejected', pending_chit_admin: 'Chit admin', pending_field: 'Field verification',
      pending_accounts: 'Accounts payout', pending_admin: 'Accounts payout', draft: 'Draft', not_requested: 'Not requested' } as any)[s] || s;
  }

  exportCsv() {
    if (!this.rows.length) {
      this.appService.showAlert('SPN Auction Report', 'Nothing to export', 'error');
      return;
    }
    csvDownload('spn-auction-report.csv',
      ['Auction Date', 'Scheme', 'Group', 'Month', 'Customer', 'Code', 'Mobile', 'Branch', 'AR No', 'Prized Amount', 'Payout', 'Paid'],
      this.rows.map((r) => [r.auction_date, r.scheme, r.group_name, r.month_no, r.customer_name, r.customer_code, r.mobile, r.branch_name, r.ar_no, r.prized_amount, this.payLabel(r.payment_status), r.paid_amount]));
  }
}
