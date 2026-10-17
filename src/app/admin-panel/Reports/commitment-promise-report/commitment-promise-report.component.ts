import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import * as XLSX from 'xlsx';
import { SPN_REPORT_STYLES, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-commitment-promise-report',
  templateUrl: './commitment-promise-report.component.html',
  styles: [SPN_REPORT_STYLES + `
    .cal { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
    .cal-h { text-align: center; font-size: 11px; font-weight: 800; color: #5c6b80; text-transform: uppercase; }
    .cal-d { min-height: 96px; border: 1px solid #e3ece7; border-radius: 12px; padding: 6px; background: #fff; cursor: pointer; }
    .cal-d.off { background: #f7f9f8; border-style: dashed; cursor: default; }
    .cal-d.has { border-color: #18A957; background: #F3FFF8; }
    .cal-d.sel { box-shadow: 0 0 0 2px #006B3C inset; }
    .cal-d.today .cal-n { background: #006B3C; color: #fff; }
    .cal-n { display: inline-block; min-width: 22px; text-align: center; border-radius: 8px; font-weight: 800; font-size: 12px; }
    .cal-e { font-size: 11px; margin-top: 3px; padding: 2px 5px; border-radius: 6px; background: #E9FFF3; color: #004D2A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cal-e.np { background: #FFF0F4; color: #b91c1c; }
    .cal-more { font-size: 11px; font-weight: 800; color: #006B3C; margin-top: 2px; }
    @media (max-width: 900px) { .cal-d { min-height: 60px; } .cal-e { display: none; } }
  `]
})
export class CommitmentPromiseReportComponent implements OnInit {
  mode: 'promised' | 'final' = 'promised';
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  isSuperAdmin = !this.tenantId;
  companyList: any[] = [];
  branchList: any[] = [];
  f: any = { tenant_id: this.tenantId, branch_id: '0', month: new Date().toISOString().slice(0, 7), status: '' };
  dataList: any[] = [];
  loading = false;
  view: 'calendar' | 'table' = 'calendar';
  selectedDay = '';
  weeks: any[][] = [];
  money = inr;
  readonly weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

  constructor(private userservice: UserService, private appService: AppService, private route: ActivatedRoute) {}

  get title() {
    return this.mode === 'final' ? 'Final Closed Report' : 'Promised Report';
  }

  get subtitle() {
    return this.mode === 'final'
      ? 'Commitment chits whose last (final / closed) installment falls in the month.'
      : 'Commitment chits promised for the month — calendar of every promised customer with due and paid status.';
  }

  ngOnInit() {
    this.mode = this.route.snapshot.data && this.route.snapshot.data.mode === 'final' ? 'final' : 'promised';
    if (this.lockedBranch) {
      this.f.branch_id = this.lockedBranch;
    }
    if (this.isSuperAdmin) {
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    } else {
      this.setBranchList(this.tenantId);
      this.onSubmit();
    }
  }

  companyChg() {
    this.f.branch_id = '0';
    this.setBranchList(this.f.tenant_id);
  }

  setBranchList(tenantId: string) {
    this.userservice.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  onSubmit() {
    if (!this.f.tenant_id || !this.f.month) {
      this.appService.showAlert(this.title, 'Select company and month', 'error');
      return;
    }
    this.loading = true;
    this.selectedDay = '';
    this.userservice.getData({
      tenant_id: this.f.tenant_id,
      branch_id: this.lockedBranch || this.f.branch_id,
      month: this.f.month,
      report_type: this.mode
    }, 'commitment-promise-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.dataList = res.status === 'Success' ? res.data || [] : [];
      if (res.status !== 'Success') {
        this.appService.showAlert(this.title, res.msg || 'Failed', 'error');
      }
      this.buildCalendar();
    }, () => {
      this.loading = false;
      this.appService.showAlert(this.title, 'Unable to load report', 'error');
    });
  }

  get rows() {
    return this.dataList.filter((r) => (!this.f.status || r.status === this.f.status)
      && (!this.selectedDay || r.event_date === this.selectedDay));
  }

  get totals() {
    const list = this.dataList.filter((r) => !this.f.status || r.status === this.f.status);
    const sum = (k: string) => list.reduce((s, r) => s + (Number(r[k]) || 0), 0);
    return {
      count: list.length,
      scheme_value: sum('scheme_value'),
      slab_payment: sum('slab_payment'),
      paid: sum('paid_amount'),
      paidCount: list.filter((r) => r.status === 'Paid').length
    };
  }

  dayRows(date: string) {
    return this.dataList.filter((r) => r.event_date === date && (!this.f.status || r.status === this.f.status));
  }

  buildCalendar() {
    const [y, m] = this.f.month.split('-').map(Number);
    const first = new Date(y, m - 1, 1);
    const days = new Date(y, m, 0).getDate();
    const today = new Date().toISOString().slice(0, 10);
    const cells: any[] = [];
    for (let i = 0; i < first.getDay(); i++) {
      cells.push(null);
    }
    for (let d = 1; d <= days; d++) {
      const date = this.f.month + '-' + ('0' + d).slice(-2);
      cells.push({ day: d, date, today: date === today });
    }
    while (cells.length % 7) {
      cells.push(null);
    }
    this.weeks = [];
    for (let i = 0; i < cells.length; i += 7) {
      this.weeks.push(cells.slice(i, i + 7));
    }
  }

  pickDay(cell: any) {
    if (!cell) {
      return;
    }
    this.selectedDay = this.selectedDay === cell.date ? '' : cell.date;
  }

  exportExcel() {
    if (!this.dataList.length) {
      this.appService.showAlert(this.title, 'No data to export', 'error');
      return;
    }
    const rows = this.rows.map((r, i) => ({
      'S.No': i + 1,
      Date: r.event_date,
      'Customer ID': r.customer_id,
      Name: r.name,
      Mobile: r.mobile,
      Branch: r.branch_name,
      'SPN Number': r.spn_number,
      Scheme: r.scheme_name,
      'Scheme Value': r.scheme_value,
      Slab: r.slab_name,
      'Joining Date': r.joining_date,
      Agent: r.agent,
      'Running Month': r.running_month,
      [this.mode === 'final' ? 'Final Month' : 'Promised Month']: r.promised_or_final_month,
      'Slab Payment': r.slab_payment,
      Paid: r.paid_amount,
      Status: r.status
    }));
    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, this.title);
    XLSX.writeFile(wb, (this.mode === 'final' ? 'final_closed_report_' : 'promised_report_') + this.f.month + '.xlsx');
  }
}
