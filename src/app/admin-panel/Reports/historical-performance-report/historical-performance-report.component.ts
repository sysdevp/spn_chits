import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-historical-performance-report',
  templateUrl: './historical-performance-report.component.html',
  styles: [
    `
      .exec-report-root .card-header {
        background: linear-gradient(90deg, #283593 0%, #5c6bc0 100%);
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
        background: linear-gradient(135deg, #e65100 0%, #ff9800 55%, #ffb74d 100%);
      }
      .exec-kpi-bid .exec-kpi-badge {
        background: rgba(0, 0, 0, 0.15);
        color: #fffde7;
      }
      .exec-kpi-collections {
        background: linear-gradient(135deg, #00695c 0%, #26a69a 100%);
      }
      .exec-kpi-payments {
        background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
      }
      .exec-kpi-people {
        background: linear-gradient(135deg, #4a148c 0%, #7b1fa2 100%);
      }
      .exec-kpi-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        opacity: 0.92;
        font-weight: 600;
      }
      .exec-kpi-value {
        font-size: 1.55rem;
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
        width: 76px;
        flex-shrink: 0;
        font-size: 0.8rem;
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
      .exec-fill-new {
        background: linear-gradient(90deg, #2e7d32, #66bb6a);
      }
      .exec-fill-rem {
        background: linear-gradient(90deg, #c62828, #ef5350);
      }
      .exec-fill-enr {
        background: linear-gradient(90deg, #6a1b9c, #ab47bc);
      }
      .exec-bar-amt {
        width: 108px;
        flex-shrink: 0;
        text-align: right;
        font-size: 0.78rem;
        font-weight: 600;
        color: #37474f;
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
      .exec-swatch-green {
        background: linear-gradient(90deg, #2e7d32, #66bb6a);
      }
      .exec-swatch-red {
        background: linear-gradient(90deg, #c62828, #ef5350);
      }
      .exec-swatch-purple {
        background: linear-gradient(90deg, #6a1b9c, #ab47bc);
      }
      .exec-note {
        font-size: 0.85rem;
        color: #555;
      }
      .exec-table-wrap {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(0, 0, 0, 0.08);
      }
      .exec-table-wrap .table thead th {
        background: linear-gradient(180deg, #eceff1 0%, #cfd8dc 100%);
        color: #263238;
        font-weight: 700;
        font-size: 0.82rem;
      }
    `,
  ],
})
export class HistoricalPerformanceReportComponent implements OnInit {
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  loading = false;
  submitted = false;
  report: any = null;

  maxCollections = 1;
  maxPayments = 1;
  maxNewCustomers = 1;
  maxRemoved = 1;
  maxEnrollments = 1;

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
      range_months: ['3', Validators.required],
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

  private refreshScales() {
    const rows = (this.report && this.report.months) || [];
    if (!rows.length) {
      this.maxCollections = 1;
      this.maxPayments = 1;
      this.maxNewCustomers = 1;
      this.maxRemoved = 1;
      this.maxEnrollments = 1;
      return;
    }
    this.maxCollections = Math.max(...rows.map((r: any) => this.num(r.collections)), 1);
    this.maxPayments = Math.max(...rows.map((r: any) => this.num(r.payments)), 1);
    this.maxNewCustomers = Math.max(...rows.map((r: any) => this.num(r.new_customers)), 1);
    this.maxRemoved = Math.max(...rows.map((r: any) => this.num(r.removed_customers)), 1);
    this.maxEnrollments = Math.max(...rows.map((r: any) => this.num(r.new_enrollments)), 1);
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
      range_months: Number(this.filterForm.get('range_months').value) || 3,
    };
    const bid = this.filterForm.get('branch_id').value;
    if (bid) {
      payload.branch_id = bid;
    }
    this.userService.getData(payload, 'historical-performance-report', 'JSON').subscribe(
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
