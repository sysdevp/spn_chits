import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

@Component({
  selector: 'app-due-vs-payments',
  templateUrl: './due-vs-payments.component.html',
  styles: [`
    .dvp-page { font-family: inherit; }
    .dvp-hero {
      display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap;
      background: linear-gradient(120deg, #071D3A 0%, #0b3d78 42%, #18A957 100%);
      color: #fff; border-radius: 18px; padding: 22px 24px; margin-bottom: 16px;
    }
    .dvp-kicker { color: #F5B700; font-size: 12px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
    .dvp-hero h1 { margin: 4px 0 6px; font-size: 28px; font-weight: 800; }
    .dvp-hero p { margin: 0; color: rgba(255,255,255,0.82); max-width: 560px; }
    .dvp-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; background: #fff; border-radius: 16px; padding: 14px; margin-bottom: 16px; box-shadow: 0 6px 18px rgba(7,29,58,0.05); }
    .dvp-filters label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; }
    .dvp-filters input, .dvp-filters select { height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; min-width: 180px; background: #f8fbff; color: #071D3A; }
    .dvp-btn { height: 40px; border: 0; border-radius: 10px; padding: 0 14px; font-weight: 800; cursor: pointer; background: #071D3A; color: #fff; }
    .dvp-btn.soft { background: #E9FFF3; color: #006B3C; }
    .board { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 16px; }
    .pamphlet { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 10px 24px rgba(7,29,58,0.08); border: 1px solid #e7eef6; }
    .ribbon { padding: 16px 16px 14px; color: #071D3A; }
    .tone-0 .ribbon { background: #E9FFF3; }
    .tone-1 .ribbon { background: #EDF6FF; }
    .tone-2 .ribbon { background: #FFF8E8; }
    .tone-3 .ribbon { background: #F4EEFF; }
    .tone-4 .ribbon { background: #FFF0F4; }
    .tone-5 .ribbon { background: #E8F8FC; }
    .facts { display: grid; grid-template-columns: 1.3fr 1fr 0.7fr; gap: 8px; }
    .fact span { display: block; font-size: 10px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: #5c6b80; }
    .fact b { display: block; font-size: 16px; color: #071D3A; line-height: 1.25; }
    .fact.chit b { font-size: 22px; }
    .tone-0 .fact.chit b { color: #006B3C; }
    .tone-1 .fact.chit b { color: #1687E8; }
    .tone-2 .fact.chit b { color: #C48400; }
    .tone-3 .fact.chit b { color: #7138E8; }
    .tone-4 .fact.chit b { color: #EF476F; }
    .tone-5 .fact.chit b { color: #0E8EAE; }
    .branch-line { margin-top: 10px; font-size: 12px; font-weight: 700; color: #5c6b80; }
    .sheet { max-height: 420px; overflow: auto; }
    table.sheet-table { width: 100%; border-collapse: collapse; margin: 0; }
    .sheet-table th { position: sticky; top: 0; background: #071D3A; color: #fff; font-size: 11px; letter-spacing: 0.04em; text-transform: uppercase; padding: 9px 10px; }
    .sheet-table td { padding: 8px 10px; font-size: 13px; border-bottom: 1px solid #eef3f8; }
    .sheet-table tr:nth-child(even) td { background: #f7fbff; }
    .sheet-table tr.first td { background: #FFF8E8; }
    .inst { font-weight: 800; color: #071D3A; }
    .due { color: #1687E8; font-weight: 800; }
    .pay { color: #18A957; font-weight: 800; }
    .missing { color: #EF476F; font-weight: 800; }
    .foot { display: flex; justify-content: space-between; gap: 8px; padding: 10px 12px; background: #f8fafc; font-size: 12px; font-weight: 800; }
    .foot .due { color: #1687E8; }
    .foot .pay { color: #006B3C; }
    .empty-note, .loader { background: #fff; border-radius: 14px; padding: 28px; text-align: center; color: #5c6b80; }
  `]
})
export class DueVsPaymentsComponent implements OnInit {
  loading = true;
  searchText = '';
  branchFilter = '';
  branches: string[] = [];
  notices: any[] = [];
  filtered: any[] = [];

  constructor(private appService: AppService, private userservice: UserService) {}

  ngOnInit() {
    const body = { tenant_id: localStorage.getItem('tenant_id') };
    let pending = 2;
    let dues: any[] = [];
    let payments: any[] = [];
    const done = () => {
      pending--;
      if (pending > 0) {
        return;
      }
      this.notices = this.buildNotices(dues, payments);
      this.branches = this.uniqueBranches(this.notices);
      this.applyFilters();
      this.loading = false;
    };
    this.userservice.getData(body, 'list-slab-dues', 'JSON').subscribe((response: any) => {
      dues = response && response.status === 'Success' && response.data ? response.data : [];
      done();
    }, () => done());
    this.userservice.getData(body, 'list-slab-payments', 'JSON').subscribe((response: any) => {
      payments = response && response.status === 'Success' && response.data ? response.data : [];
      done();
    }, () => done());
  }

  applyFilters() {
    const text = (this.searchText || '').toLowerCase();
    const branch = this.branchFilter;
    const next = [];
    this.notices.forEach((item) => {
      const blob = (item.branch + ' ' + item.slabName + ' ' + item.chitValue + ' ' + item.months).toLowerCase();
      if (text && blob.indexOf(text) < 0) {
        return;
      }
      if (branch && item.branch !== branch) {
        return;
      }
      next.push(item);
    });
    this.filtered = next;
  }

  resetFilters() {
    this.searchText = '';
    this.branchFilter = '';
    this.applyFilters();
  }

  money(value: any): string {
    const amount = Number(value) || 0;
    return '₹' + amount.toLocaleString('en-IN');
  }

  exportNotice() {
    if (!this.filtered.length) {
      this.appService.showAlert('Due vs Payments', 'Nothing to export for the current filters.', 'error');
      return;
    }
    const lines = ['Branch,Chit Value,Slab Name,Months,Installment,Due Amount,Payment Amount'];
    this.filtered.forEach((item) => {
      item.rows.forEach((row) => {
        lines.push([
          this.csv(item.branch),
          item.chitValue,
          this.csv(item.slabName),
          item.months,
          row.n,
          row.due,
          row.pay
        ].join(','));
      });
    });
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'due-vs-payments.csv';
    link.click();
  }

  private csv(value: any): string {
    const text = value === null || value === undefined ? '' : String(value);
    return '"' + text.replace(/"/g, '""') + '"';
  }

  private uniqueBranches(items: any[]): string[] {
    const names = [];
    items.forEach((item) => {
      if (item.branch && names.indexOf(item.branch) < 0) {
        names.push(item.branch);
      }
    });
    return names.sort();
  }

  private amount(row: any, key: string): number {
    if (!row || row[key] === null || row[key] === undefined || row[key] === '') {
      return 0;
    }
    const value = Number(row[key]);
    return isNaN(value) ? 0 : value;
  }

  private monthCount(scheme: any, due: any, pay: any): number {
    let months = scheme && scheme.no_of_months ? parseInt(scheme.no_of_months, 10) : 0;
    if (!months || isNaN(months)) {
      months = 0;
      for (let i = 1; i <= 100; i++) {
        if (this.amount(due, 'due_' + i) || this.amount(pay, 'payment_' + i)) {
          months = i;
        }
      }
    }
    if (months > 100) { months = 100; }
    if (months < 1) { months = 1; }
    return months;
  }

  private ordinal(n: number): string {
    const mod10 = n % 10;
    const mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) { return n + 'st'; }
    if (mod10 === 2 && mod100 !== 12) { return n + 'nd'; }
    if (mod10 === 3 && mod100 !== 13) { return n + 'rd'; }
    return n + 'th';
  }

  private buildNotices(dues: any[], payments: any[]): any[] {
    const paymentBySlab: any = {};
    const usedPayments: any = {};
    payments.forEach((payment) => {
      const slabId = payment.slab_id || (payment.slab_due_info && payment.slab_due_info.id);
      const key = slabId ? String(slabId) : '';
      if (key && !paymentBySlab[key]) {
        paymentBySlab[key] = payment;
      }
    });
    const notices = [];
    dues.forEach((due) => {
      const payment = paymentBySlab[String(due.id)] || null;
      if (payment) {
        usedPayments[String(payment.id)] = true;
      }
      notices.push(this.makeNotice(due, payment, due.scheme_info, due.branch_info, due.slab_name, notices.length));
    });
    payments.forEach((payment) => {
      if (usedPayments[String(payment.id)]) {
        return;
      }
      const due = payment.slab_due_info || null;
      notices.push(this.makeNotice(
        due,
        payment,
        payment.scheme_info || (due && due.scheme_info),
        payment.branch_info || (due && due.branch_info),
        (due && due.slab_name) || 'Payment slab',
        notices.length
      ));
    });
    return notices;
  }

  private makeNotice(due: any, payment: any, scheme: any, branch: any, slabName: any, index: number): any {
    const months = this.monthCount(scheme, due, payment);
    const rows = [];
    let dueTotal = 0;
    let payTotal = 0;
    for (let i = 1; i <= months; i++) {
      const dueAmount = this.amount(due, 'due_' + i);
      const payAmount = this.amount(payment, 'payment_' + i);
      dueTotal += dueAmount;
      payTotal += payAmount;
      rows.push({ n: i, label: this.ordinal(i), due: dueAmount, pay: payAmount, first: i === 1 });
    }
    return {
      tone: 'tone-' + (index % 6),
      hasPayment: !!payment,
      branch: branch && branch.branch_name ? branch.branch_name : 'All branches',
      slabName: slabName || 'Slab',
      chitValue: scheme && scheme.chit_value ? scheme.chit_value : 0,
      months: months,
      rows: rows,
      dueTotal: dueTotal,
      payTotal: payTotal
    };
  }
}
