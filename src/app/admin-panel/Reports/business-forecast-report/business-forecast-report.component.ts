import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-business-forecast-report',
  templateUrl: './business-forecast-report.component.html',
  styles: [
    `
      .exec-report-root .card-header {
        background: linear-gradient(90deg, #1a237e 0%, #3949ab 100%);
        color: #fff;
        border: 0;
      }
      .exec-kpi-row .exec-kpi {
        border-radius: 14px;
        padding: 1rem 1.1rem;
        color: #fff;
        min-height: 118px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
      }
      .exec-kpi-bid {
        background: linear-gradient(135deg, #bf360c 0%, #ff6f00 50%, #ffcc80 100%);
      }
      .exec-kpi-bid .exec-kpi-badge {
        background: rgba(0, 0, 0, 0.15);
        color: #fffde7;
      }
      .exec-kpi-collections {
        background: linear-gradient(135deg, #004d40 0%, #00897b 100%);
      }
      .exec-kpi-payments {
        background: linear-gradient(135deg, #01579b 0%, #0288d1 100%);
      }
      .exec-kpi-people {
        background: linear-gradient(135deg, #311b92 0%, #5e35b1 100%);
      }
      .exec-kpi-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.92;
        font-weight: 600;
      }
      .exec-kpi-value {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
        margin: 0.35rem 0;
      }
      .exec-kpi-hint {
        font-size: 0.78rem;
        opacity: 0.9;
      }
      .exec-kpi-badge {
        display: inline-block;
        font-size: 0.7rem;
        padding: 0.15rem 0.45rem;
        border-radius: 999px;
        margin-top: 0.35rem;
      }
      .exec-chart-panel {
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: linear-gradient(180deg, #fafafa 0%, #fff 40%);
        padding: 1rem 1.1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
      }
      .exec-chart-title {
        font-weight: 700;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
        color: #37474f;
      }
      .exec-bar-row {
        display: flex;
        align-items: center;
        margin-bottom: 0.55rem;
        gap: 0.5rem;
      }
      .exec-bar-label {
        width: 88px;
        flex-shrink: 0;
        font-size: 0.78rem;
        color: #546e7a;
        font-weight: 600;
      }
      .exec-bar-track {
        flex: 1;
        height: 18px;
        background: #eceff1;
        border-radius: 9px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.05);
      }
      .exec-bar-fill {
        height: 100%;
        border-radius: 9px;
        transition: width 0.35s ease;
      }
      .exec-fill-coll {
        background: linear-gradient(90deg, #00897b, #4db6ac);
      }
      .exec-fill-pay {
        background: linear-gradient(90deg, #1565c0, #42a5f5);
      }
      .exec-fill-fc {
        background: linear-gradient(90deg, #6a1b9c, #ba68c8);
      }
      .exec-fill-yoy {
        background: linear-gradient(90deg, #f57f17, #ffca28);
      }
      .exec-fill-new {
        background: linear-gradient(90deg, #2e7d32, #66bb6a);
      }
      .exec-bar-amt {
        width: 120px;
        flex-shrink: 0;
        text-align: right;
        font-size: 0.74rem;
        font-weight: 600;
        color: #37474f;
      }
      .exec-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        font-size: 0.75rem;
        margin-top: 0.5rem;
        color: #607d8b;
      }
      .exec-swatch {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 2px;
        margin-right: 5px;
        vertical-align: middle;
      }
      .exec-swatch-teal {
        background: linear-gradient(90deg, #00897b, #4db6ac);
      }
      .exec-swatch-blue {
        background: linear-gradient(90deg, #1565c0, #42a5f5);
      }
      .exec-swatch-fc {
        background: linear-gradient(90deg, #6a1b9c, #ba68c8);
      }
      .exec-swatch-yoy {
        background: linear-gradient(90deg, #f57f17, #ffca28);
      }
      .exec-swatch-green {
        background: linear-gradient(90deg, #2e7d32, #66bb6a);
      }
      .exec-note {
        font-size: 0.85rem;
        color: #555;
      }
      .section-title {
        font-weight: 700;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        color: #37474f;
        font-size: 1rem;
      }
      .exec-tile {
        border-radius: 12px;
        padding: 0.9rem 1rem;
        color: #fff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        margin-bottom: 0.5rem;
      }
      .exec-tile-a {
        background: linear-gradient(135deg, #00695c, #26a69a);
      }
      .exec-tile-b {
        background: linear-gradient(135deg, #0d47a1, #42a5f5);
      }
      .exec-tile-c {
        background: linear-gradient(135deg, #4a148c, #ab47bc);
      }
      .exec-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.08);
      }
      .exec-table-wrap .table thead th {
        background: linear-gradient(180deg, #e8eaf6 0%, #c5cae9 100%);
        color: #1a237e;
        font-weight: 700;
        font-size: 0.82rem;
      }
    `,
  ],
})
export class BusinessForecastReportComponent implements OnInit {
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  loading = false;
  submitted = false;
  report: any = null;

  maxLast3Coll = 1;
  maxLast3Pay = 1;
  maxLast3New = 1;
  maxFcYoyColl = 1;
  maxSubTrend = 1;

  constructor(
    private fb: FormBuilder,
    private userService: UserService,
    private appService: AppService
  ) {}

  get f() {
    return this.filterForm.controls;
  }

  ngOnInit() {
    this.filterForm = this.fb.group({
      tenant_id: ['', Validators.required],
      branch_id: [''],
    });
    const tenantId = localStorage.getItem('tenant_id');
    const branchId = localStorage.getItem('branch_id');
    if (tenantId) {
      this.isSuperAdmin = false;
      this.filterForm.patchValue({
        tenant_id: tenantId,
        branch_id: branchId || '',
      });
      this.setBranchList(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userService.getData('', 'list-tenants', 'POST').subscribe(
        (res: any) => {
          if (res.status === 'Success') {
            this.companyList = res.data || [];
            if (this.companyList.length) {
              const tid = String(this.companyList[0].id);
              this.filterForm.patchValue({ tenant_id: tid, branch_id: '' });
              this.setBranchList(tid);
            }
          }
        },
        () => {}
      );
    }
  }

  companyChg(event: Event) {
    const tid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({ tenant_id: tid, branch_id: '' });
    this.setBranchList(tid);
  }

  setBranchList(tenant_id: string) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1,
    };
    this.userService.getData(cond, 'get-branches', 'POST').subscribe(
      (res: any) => {
        this.branchList = res.status === 'Success' ? res.data || [] : [];
      },
      () => {
        this.branchList = [];
      }
    );
  }

  num(v: any): number {
    const n = +v;
    return isNaN(n) ? 0 : n;
  }

  barPct(val: any, max: number): number {
    const v = this.num(val);
    const m = Math.max(this.num(max), 1);
    const raw = Math.round((v / m) * 100);
    if (v <= 0) {
      return 0;
    }
    return Math.min(100, Math.max(6, raw));
  }

  yoyRow(i: number): any {
    const y = (this.report && this.report.yoy_same_month_last_year) || [];
    return y[i] || {};
  }

  private refreshScales() {
    const last3 = (this.report && this.report.last_3_months_actual) || [];
    const fc = (this.report && this.report.next_3_months_forecast) || [];
    const yoy = (this.report && this.report.yoy_same_month_last_year) || [];

    this.maxLast3Coll = Math.max(1, ...last3.map((r: any) => this.num(r.collections)));
    this.maxLast3Pay = Math.max(1, ...last3.map((r: any) => this.num(r.payments)));
    this.maxLast3New = Math.max(1, ...last3.map((r: any) => this.num(r.new_customers)));

    let md = 1;
    fc.forEach((r: any, i: number) => {
      md = Math.max(md, this.num(r.collections_forecast), this.num(yoy[i] && yoy[i].collections));
    });
    this.maxFcYoyColl = md;

    const subVals: number[] = [];
    last3.forEach((r: any) => subVals.push(this.num(r.new_customers)));
    fc.forEach((r: any) => subVals.push(this.num(r.new_customers_forecast)));
    this.maxSubTrend = Math.max(1, ...subVals);
  }

  loadReport() {
    this.submitted = true;
    if (this.filterForm.invalid) {
      return;
    }
    this.loading = true;
    this.report = null;
    const payload: any = {
      tenant_id: this.filterForm.get('tenant_id').value,
    };
    const bid = this.filterForm.get('branch_id').value;
    if (bid) {
      payload.branch_id = bid;
    }
    this.userService.getData(payload, 'business-forecast-report', 'JSON').subscribe(
      (res: any) => {
        this.loading = false;
        if (res.status === 'Success' && res.data) {
          this.report = res.data;
          this.refreshScales();
        } else {
          this.appService.showAlert('Report', res.msg || 'No data', 'error');
        }
      },
      () => {
        this.loading = false;
        this.appService.showAlert('Report', 'Unable to reach server', 'error');
      }
    );
  }
}
