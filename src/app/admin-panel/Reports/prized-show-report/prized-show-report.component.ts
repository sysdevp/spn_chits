import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-prized-show-report',
  templateUrl: './prized-show-report.component.html',
  styles: [`
    .psr-page { font-family: inherit; }
    .psr-hero {
      display: flex; justify-content: space-between; align-items: flex-end; gap: 14px; flex-wrap: wrap;
      background: linear-gradient(120deg, #004D2A 0%, #007A49 50%, #1687E8 100%);
      color: #fff; border-radius: 18px; padding: 20px 22px; margin-bottom: 14px;
    }
    .psr-kicker { color: #F5B700; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .psr-hero h1 { margin: 4px 0 6px; font-size: 28px; font-weight: 800; }
    .psr-hero p { margin: 0; max-width: 640px; color: rgba(255,255,255,.86); }
    .psr-btn { border: 0; border-radius: 10px; height: 40px; padding: 0 14px; font-weight: 800; cursor: pointer; background: #fff; color: #071D3A; }
    .psr-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; background: #fff; border-radius: 16px; padding: 14px; margin-bottom: 14px; box-shadow: 0 6px 18px rgba(7,29,58,.05); }
    .psr-filters label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; }
    .psr-filters select { height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; min-width: 220px; background: #f8fbff; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
    .kpi { border-radius: 16px; padding: 14px; }
    .kpi span { display: block; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #5c6b80; }
    .kpi b { display: block; margin-top: 4px; font-size: 22px; color: #071D3A; }
    .k1 { background: #E9FFF3; }
    .k2 { background: #EDF6FF; }
    .k3 { background: #FFF8E8; }
    .k4 { background: #FFF0F4; }
    .board { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 14px; }
    .card { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 24px rgba(7,29,58,.07); border: 1px solid #e7eef6; }
    .head { padding: 14px 16px; }
    .tone-0 .head { background: #E9FFF3; }
    .tone-1 .head { background: #EDF6FF; }
    .tone-2 .head { background: #FFF8E8; }
    .tone-3 .head { background: #F4EEFF; }
    .tone-4 .head { background: #FFF0F4; }
    .tone-5 .head { background: #E8F8FC; }
    .head h3 { margin: 0 0 4px; color: #071D3A; font-size: 18px; }
    .head p { margin: 0; color: #5c6b80; font-size: 13px; font-weight: 700; }
    .meta { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
    .chip { background: rgba(7,29,58,.08); border-radius: 999px; padding: 4px 10px; font-size: 11px; font-weight: 800; color: #071D3A; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #071D3A; color: #fff; font-size: 11px; text-transform: uppercase; padding: 9px 10px; text-align: left; }
    td { padding: 9px 10px; border-bottom: 1px solid #eef3f8; font-size: 13px; }
    tr:nth-child(even) td { background: #f8fbff; }
    .amt { color: #18A957; font-weight: 800; }
    .empty { background: #fff; border-radius: 14px; padding: 28px; text-align: center; color: #5c6b80; }
    .kpis { grid-template-columns: repeat(6, 1fr); }
    .sub { font-size: 11px; color: #64748b; }
    .month-card { margin-bottom: 14px; }
    .pay { border-radius: 10px; padding: 2px 8px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; white-space: nowrap; }
    .pay.approved { background: #dcfce7; color: #15803d; }
    .pay.rejected { background: #fee2e2; color: #b91c1c; }
    .pay.pending_chit_admin, .pay.pending_field, .pay.pending_accounts, .pay.pending_admin { background: #fef3c7; color: #92400e; }
    @media (max-width: 900px) { .kpis { grid-template-columns: 1fr 1fr; } }
  `]
})
export class PrizedShowReportComponent implements OnInit {
  loading = false;
  groups: any[] = [];
  rows: any[] = [];
  groupFilter = '';
  totalGroups = 0;
  totalMarked = 0;
  totalAmount = 0;
  totalMembers = 0;
  totalPaidOut = 0;
  totalPending = 0;
  monthFilter = '';
  view = 'groups';

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.load();
  }

  money(value: any): string {
    return '₹' + (Number(value) || 0).toLocaleString('en-IN');
  }

  load() {
    this.loading = true;
    const body: any = { tenant_id: localStorage.getItem('tenant_id') };
    if (this.groupFilter) {
      body.group_id = this.groupFilter;
    }
    this.userservice.getData(body, 'prized-show-report', 'JSON').subscribe((res: any) => {
      const data = res && res.status === 'Success' ? (res.data || []) : [];
      this.rows = data.map((item, index) => {
        item.tone = 'tone-' + (index % 6);
        return item;
      });
      if (!this.groupFilter) {
        this.groups = data.map((item) => ({ id: item.id, group_name: item.group_name }));
      }
      this.totalGroups = this.rows.length;
      this.totalMarked = 0;
      this.totalAmount = 0;
      this.totalMembers = 0;
      this.totalPaidOut = 0;
      this.totalPending = 0;
      this.rows.forEach((item) => {
        this.totalMarked += Number(item.marked_count) || 0;
        this.totalAmount += Number(item.total_amount) || 0;
        this.totalMembers += Number(item.member_count) || 0;
        this.totalPaidOut += Number(item.paid_out) || 0;
        this.totalPending += Number(item.pending_months) || 0;
      });
      this.loading = false;
    }, () => {
      this.rows = [];
      this.loading = false;
    });
  }

  reset() {
    this.groupFilter = '';
    this.monthFilter = '';
    this.load();
  }

  filteredSchedule(item: any): any[] {
    return (item.schedule || []).filter((row) => !this.monthFilter || String(row.month_no) === String(this.monthFilter));
  }

  flatRows(): any[] {
    const out = [];
    this.rows.forEach((item) => {
      this.filteredSchedule(item).forEach((row) => {
        out.push(Object.assign({ group_name: item.group_name, scheme_name: item.scheme_name, chit_value: item.chit_value }, row));
      });
    });
    return out;
  }

  monthNos(): number[] {
    const max = this.rows.reduce((m, item) => Math.max(m, Number(item.months) || 0), 0);
    const list = [];
    for (let i = 1; i <= max; i++) {
      list.push(i);
    }
    return list;
  }

  monthWise(): any[] {
    const map: any = {};
    this.flatRows().forEach((row) => {
      const key = Number(row.month_no);
      if (!map[key]) {
        map[key] = { month_no: key, rows: [], amount: 0 };
      }
      map[key].rows.push(row);
      map[key].amount += Number(row.amount) || 0;
    });
    return Object.keys(map).map((k) => map[k]).sort((a, b) => a.month_no - b.month_no);
  }

  payLabel(status: string): string {
    return ({
      approved: 'Paid',
      rejected: 'Rejected',
      pending_chit_admin: 'Chit admin',
      pending_field: 'Field verification',
      pending_accounts: 'Accounts payout',
      pending_admin: 'Accounts payout',
      draft: 'Draft',
      not_requested: 'Not requested'
    } as any)[status] || status || '-';
  }

  exportCsv() {
    if (!this.rows.length) {
      this.appService.showAlert('Prized Show Report', 'Nothing to export.', 'error');
      return;
    }
    const lines = ['Group,Scheme,Chit Value,Month,Auction Date,Prized Customer,Code,Mobile,Branch,AR No,Due No,Customer Paid,Prized Amount,Payout,Paid On,Remarks'];
    this.flatRows().forEach((row) => {
      lines.push([
        this.csv(row.group_name),
        this.csv(row.scheme_name),
        row.chit_value,
        row.month_no,
        this.csv(row.auction_date),
        this.csv(row.customer_name),
        this.csv(row.customer_code),
        this.csv(row.mobile),
        this.csv(row.branch_name),
        this.csv(row.ar_no),
        row.due_no,
        row.customer_paid,
        row.amount,
        this.csv(this.payLabel(row.payment_status)),
        this.csv(row.payment_date),
        this.csv(row.remarks)
      ].join(','));
    });
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'prized-show-report.csv';
    link.click();
  }

  private csv(value: any): string {
    const text = value === null || value === undefined ? '' : String(value);
    return '"' + text.replace(/"/g, '""') + '"';
  }
}
