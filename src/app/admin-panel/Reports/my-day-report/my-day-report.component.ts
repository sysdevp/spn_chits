import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_REPORT_STYLES, csvDownload, inr, loginBranchId } from '../spn-report.styles';

@Component({
  selector: 'app-my-day-report',
  templateUrl: './my-day-report.component.html',
  styles: [SPN_REPORT_STYLES]
})
export class MyDayReportComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id') || '';
  lockedBranch = loginBranchId();
  branchList: any[] = [];
  f: any = { branch_id: '', date: new Date().toISOString().slice(0, 10) };
  lines: any[] = [];
  entries: any[] = [];
  totals: any = {};
  loading = false;
  money = inr;

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.f.branch_id = this.lockedBranch;
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  shift(days: number) {
    const d = new Date(this.f.date);
    d.setDate(d.getDate() + days);
    this.f.date = d.toISOString().slice(0, 10);
    this.load();
  }

  load() {
    this.loading = true;
    this.userservice.getData({ tenant_id: this.tenantId, branch_id: this.lockedBranch || this.f.branch_id, date: this.f.date }, 'my-day-report', 'JSON')
      .subscribe((res: any) => {
        this.loading = false;
        const ok = res && res.status === 'Success';
        this.lines = ok ? res.lines || [] : [];
        this.entries = ok ? res.entries || [] : [];
        this.totals = ok ? res.totals || {} : {};
        if (!ok) {
          this.appService.showAlert('My Day Report', (res && res.msg) || 'Unable to load', 'error');
        }
      }, () => { this.loading = false; });
  }

  side(kind: string) {
    return this.lines.filter((l) => l.kind === kind);
  }

  print() {
    window.print();
  }

  exportCsv() {
    csvDownload('my-day-' + this.f.date + '.csv', ['Type', 'Category', 'Cash', 'Bank', 'Count'],
      this.lines.map((l) => [l.kind, l.category, l.cash, l.bank, l.count]));
  }
}
