import { ChangeDetectorRef, Component, OnInit, TemplateRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { Router } from '@angular/router';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DomSanitizer, SafeStyle } from '@angular/platform-browser';
import { UserService } from '../../Services/user.service';
import { AppService } from '../../app.service';
import { DashboardAccessService } from '../../Services/dashboard-access.service';
import { DashboardKey, DashboardTypeDef } from '../../config/dashboard-types.config';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styles: [
    `
      :host {
        display: block;
      }
      .dashboard-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
      }
      .dashboard-title {
        font-size: 1.85rem;
        font-weight: 700;
        letter-spacing: -.02em;
        color: #013618;
      }
      .dashboard-subtitle {
        color: #6b7280;
      }
      .dashboard-header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
      }
      .dashboard-header-actions.align-items-end {
        align-items: flex-end;
      }
      .dashboard-header-actions .form-inline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0;
      }
      .dashboard-switcher {
        min-width: 220px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        font-weight: 600;
        color: #013618;
      }
      .dashboard-top-kpis,
      .dashboard-stat-cards {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1rem;
        grid-auto-rows: minmax(160px, auto);
      }
      @media (min-width: 576px) {
        .dashboard-top-kpis,
        .dashboard-stat-cards {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }
      @media (min-width: 992px) {
        .dashboard-top-kpis,
        .dashboard-stat-cards {
          grid-template-columns: repeat(4, minmax(0, 1fr));
        }
      }
      .metric-card,
      .stat-card,
      .panel-card {
        border-radius: 1.25rem;
        min-height: 160px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
        transition: transform .18s ease, box-shadow .18s ease;
      }
      .metric-card:hover,
      .stat-card:hover,
      .panel-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.12);
      }
      .metric-card .card-body,
      .stat-card .card-body,
      .panel-card .card-body {
        padding: 1.5rem;
      }
      .metric-label {
        text-transform: uppercase;
        font-size: 0.78rem;
        letter-spacing: 0.12em;
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 0.85rem;
      }
      .metric-value {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.05;
        color: #ffffff;
      }
      .metric-note {
        margin-top: 0.65rem;
        font-size: 0.88rem;
        color: rgba(255, 255, 255, 0.82);
      }
      .card-tile-followups {
        background: linear-gradient(135deg, #013618 0%, #025E2D 100%);
      }
      .card-tile-collections {
        background: linear-gradient(135deg, #117a6b 0%, #18a17e 100%);
      }
      .card-tile-payments {
        background: linear-gradient(135deg, #025E2D 0%, #20C25B 100%);
      }
      .card-tile-pending {
        background: linear-gradient(135deg, #bb6b12 0%, #f5851a 100%);
      }
      .stat-card {
        color: #fff;
      }
      .stat-tile-month {
        background: linear-gradient(135deg, #013618 0%, #1a7cca 100%);
      }
      .stat-tile-running {
        background: linear-gradient(135deg, #006b5a 0%, #1ca286 100%);
      }
      .stat-tile-vacant {
        background: linear-gradient(135deg, #9b4e08 0%, #ec8c19 100%);
      }
      .stat-tile-subscribers {
        background: linear-gradient(135deg, #5b2c83 0%, #8f43c2 100%);
      }
      .panel-card {
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: #fff;
      }
      .panel-card .card-header {
        padding: 1rem 1.5rem;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
      }
      .panel-card .card-body {
        padding: 1.5rem;
      }
      .card-title {
        margin-bottom: 0.25rem;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
      }
      .card-subtitle {
        font-size: 0.82rem;
        color: #6b7280;
      }
      .chart-list {
        display: grid;
        gap: 1rem;
      }
      .chart-row {
        display: grid;
        grid-template-columns: 2fr 5fr 1.5fr;
        gap: 0.75rem;
        align-items: center;
      }
      .chart-label {
        font-weight: 600;
        color: #334155;
      }
      .chart-bar-wrapper {
        height: 12px;
        background: #f3f4f6;
        border-radius: 999px;
        overflow: hidden;
      }
      .chart-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #025E2D, #20C25B);
      }
      .chart-value {
        text-align: right;
        font-weight: 600;
        color: #0f172a;
      }
      .donut-wrap {
        position: relative;
        width: 180px;
        height: 180px;
        margin: 0 auto;
      }
      .donut {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
      .donut-hole {
        position: absolute;
        inset: 28%;
        background: #fff;
        border-radius: 50%;
      }
      .pie-swatch {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 2px;
      }
      .swatch-0 { background: #025E2D; }
      .swatch-1 { background: #D4AF37; }
      .mini-metric { font-size: 0.8rem; color: #64748b; }
      .mini-value { font-size: 1.25rem; font-weight: 700; color: #013618; }
      .btn-export {
        background: #025E2D;
        border-color: #013618;
      }
    `,
  ],
})
export class DashboardComponent implements OnInit {
  @ViewChild('detailModal') detailModal: TemplateRef<any>;

  loading = false;
  bundle: any = null;
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  showAllBranchesOption = true;

  allowedDashboards: DashboardTypeDef[] = [];
  activeKey: DashboardKey = 'classic';
  activeTitle = 'Admin Dashboard';
  activeSubtitle = 'Snapshot view of collections, auctions, and branch performance.';

  modalDetailTitle = '';
  modalRows: any[] = [];
  modalColumns: { key: string; label: string }[] = [];

  barLabels: string[] = [];
  barData: number[] = [];
  barScope = 'month';
  pieLabels: string[] = [];
  pieData: number[] = [];
  conicGradient = 'conic-gradient(#e0e0e0 0 360deg)';
  pieSafeStyle: SafeStyle;

  constructor(
    private fb: FormBuilder,
    private userService: UserService,
    private appService: AppService,
    private modal: NgbModal,
    private sanitizer: DomSanitizer,
    private cdr: ChangeDetectorRef,
    private router: Router,
    private dashboardAccess: DashboardAccessService
  ) {
    this.pieSafeStyle = this.sanitizer.bypassSecurityTrustStyle(this.conicGradient);
  }

  ngOnInit() {
    this.resolveDashboardAccess();
    this.filterForm = this.fb.group({
      tenant_id: [''],
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
      this.setBranchList(tenantId, true);
    } else {
      this.isSuperAdmin = true;
      this.userService.getData('', 'list-tenants', 'POST').subscribe(
        (res: any) => {
          if (res.status === 'Success') {
            this.companyList = res.data || [];
            if (this.companyList.length) {
              const first = this.companyList[0];
              const tid = first.id != null ? String(first.id) : '';
              this.filterForm.patchValue({ tenant_id: tid, branch_id: '' });
              if (tid) {
                this.setBranchList(tid, true);
              }
            }
          }
        },
        () => {}
      );
    }
  }

  resolveDashboardAccess() {
    this.allowedDashboards = this.dashboardAccess.getAllowedDashboards();
    this.activeKey = this.dashboardAccess.getActiveDashboardKey();
    this.applyActiveMeta();
  }

  applyActiveMeta() {
    if (this.activeKey === 'classic') {
      this.activeTitle = 'Admin Dashboard';
      this.activeSubtitle = 'Snapshot view of collections, auctions, and branch performance.';
      return;
    }
    const def = this.dashboardAccess.getActiveDashboard();
    if (def) {
      this.activeTitle = def.title;
      this.activeSubtitle = def.subtitle;
    }
  }

  onDashboardSwitch(event: Event) {
    const key = (event.target as HTMLSelectElement).value as DashboardKey;
    this.dashboardAccess.setActiveDashboard(key);
    this.activeKey = key;
    this.applyActiveMeta();
  }

  companyChg(event: Event) {
    const tid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({ tenant_id: tid, branch_id: '' });
    this.setBranchList(tid, true);
  }

  branchChg() {
    this.loadDashboard();
  }

  private loginScopeParams(): { log_id?: string; user_id?: string; role_type_id?: string } {
    const params: { log_id?: string; user_id?: string; role_type_id?: string } = {};
    const logId = localStorage.getItem('logged_employee_id');
    const userId = localStorage.getItem('logged_user_id');
    const roleTypeId = localStorage.getItem('role_type_id');
    if (logId) {
      params.log_id = logId;
    }
    if (userId) {
      params.user_id = userId;
    }
    if (roleTypeId) {
      params.role_type_id = roleTypeId;
    }
    return params;
  }

  setBranchList(tenant_id: string, reloadDashboard = false) {
    const cond: any = {
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1,
      ...this.loginScopeParams(),
    };
    this.userService.getData(cond, 'get-branches', 'POST').subscribe(
      (res: any) => {
        this.branchList = res.status === 'Success' ? res.data || [] : [];
        this.showAllBranchesOption = this.branchList.length > 1;
        if (this.branchList.length === 1) {
          this.filterForm.patchValue({ branch_id: String(this.branchList[0].id) });
        } else if (
          this.branchList.length > 1 &&
          this.filterForm.get('branch_id').value &&
          !this.branchList.some((b) => String(b.id) === String(this.filterForm.get('branch_id').value))
        ) {
          this.filterForm.patchValue({ branch_id: '' });
        }
        if (reloadDashboard) {
          this.loadDashboard();
        }
      },
      () => {
        this.branchList = [];
        this.showAllBranchesOption = true;
        if (reloadDashboard) {
          this.loadDashboard();
        }
      }
    );
  }

  loadDashboard() {
    const tenant = this.filterForm.get('tenant_id').value;
    if (!tenant) {
      return;
    }
    this.loading = true;
    const payload: any = { tenant_id: tenant, ...this.loginScopeParams() };
    const bid = this.filterForm.get('branch_id').value;
    if (bid) {
      payload.branch_id = bid;
    }
    this.userService.getData(payload, 'get-chit-dashboard-bundle', 'POST').subscribe(
      (res: any) => {
        this.loading = false;
        if (res.status === 'Success' && res.data) {
          this.bundle = res.data;
          this.applyCharts(res.data);
          this.cdr.detectChanges();
        } else {
          this.bundle = null;
          const detail = (res && res.error) ? (' — ' + res.error) : '';
          this.appService.showAlert(
            'Dashboard',
            (res.msg || 'Unable to load dashboard') + detail,
            'error'
          );
        }
      },
      () => {
        this.loading = false;
        this.appService.showAlert('Dashboard', 'Unable to reach server', 'error');
      }
    );
  }

  barChartHasData(): boolean {
    return (this.barData || []).some((x) => Number(x) > 0);
  }

  applyCharts(data: any) {
    const c1 = data.charts && data.charts.collection_by_mode;
    this.barLabels = c1 && c1.labels ? c1.labels : [];
    this.barData =
      c1 && c1.data ? c1.data.map((x: any) => (isNaN(Number(x)) ? 0 : Number(x))) : [];
    this.barScope = (c1 && c1.scope) ? c1.scope : 'month';
    const c2 = data.charts && data.charts.bidding_release_mix;
    this.pieLabels = c2 && c2.labels ? c2.labels : [];
    this.pieData =
      c2 && c2.data ? c2.data.map((x: any) => (isNaN(Number(x)) ? 0 : Number(x))) : [0, 0];
    this.updateDonut();
  }

  updateDonut() {
    const a = this.pieData[0] || 0;
    const b = this.pieData[1] || 0;
    const t = a + b;
    if (t <= 0) {
      this.conicGradient = 'conic-gradient(#cfd8dc 0deg 360deg)';
      this.pieSafeStyle = this.sanitizer.bypassSecurityTrustStyle(this.conicGradient);
      return;
    }
    const deg = Math.max(0.5, Math.min(359.5, (a / t) * 360));
    this.conicGradient = `conic-gradient(#025E2D 0deg ${deg}deg, #D4AF37 ${deg}deg 360deg)`;
    this.pieSafeStyle = this.sanitizer.bypassSecurityTrustStyle(this.conicGradient);
  }

  barPct(i: number): number {
    const max = Math.max.apply(null, this.barData.concat([1]));
    const raw = Math.round(((this.barData[i] || 0) / max) * 100);
    const v = this.barData[i] || 0;
    if (v > 0 && raw < 8) {
      return 8;
    }
    return Math.min(100, raw);
  }

  barWidth(i: number): string {
    return this.barPct(i) + '%';
  }

  onStatTileClick(target: 'month' | 'running' | 'vacant' | 'subscribers') {
    switch (target) {
      case 'month':
        this.router.navigate(['/admin-panel/receipt-report']);
        break;
      case 'running':
      case 'vacant':
        this.router.navigate(['/admin-panel/view-group']);
        break;
      case 'subscribers':
        this.router.navigate(['/admin-panel/view-customer']);
        break;
    }
  }

  selectedBranchName(): string {
    const branchId = this.filterForm && this.filterForm.get('branch_id')
      ? this.filterForm.get('branch_id').value
      : null;
    if (!branchId || !this.branchList || !this.branchList.length) {
      return 'All branches';
    }
    const branch = this.branchList.find((b) => String(b.id) === String(branchId));
    return branch ? branch.branch_name : 'All branches';
  }

  exportDashboard() {
    if (!this.bundle) {
      return;
    }
    this.appService.showAlert('Export', 'Dashboard export is not yet available.', 'info');
  }

  openModal(content: any, kind: string) {
    if (!this.bundle) {
      return;
    }
    if (kind === 'followups') {
      this.modalDetailTitle = "Today's lead follow-ups";
      this.modalColumns = [
        { key: 'lead_name', label: 'Lead' },
        { key: 'mobile', label: 'Mobile' },
        { key: 'branch', label: 'Branch' },
        { key: 'employee', label: 'Owner' },
        { key: 'next_followup_date', label: 'Next follow-up' },
        { key: 'remarks', label: 'Remarks' },
      ];
      this.modalRows = this.bundle.today_followups || [];
    } else if (kind === 'collections') {
      this.modalDetailTitle = "Today's collections (receipts)";
      this.modalColumns = [
        { key: 'receipt_no', label: 'Receipt #' },
        { key: 'customer', label: 'Customer' },
        { key: 'amount', label: 'Amount' },
        { key: 'mode', label: 'Mode' },
        { key: 'receipt_date', label: 'Date' },
      ];
      this.modalRows = this.bundle.today_collections || [];
    } else if (kind === 'payments') {
      this.modalDetailTitle = "Today's chit payments";
      this.modalColumns = [
        { key: 'customer', label: 'Customer' },
        { key: 'group', label: 'Group' },
        { key: 'amount', label: 'Amount' },
        { key: 'mode', label: 'Mode' },
        { key: 'payment_date', label: 'Date' },
      ];
      this.modalRows = this.bundle.today_payments || [];
    } else if (kind === 'settlements') {
      this.modalDetailTitle = 'Cash settlements (recent)';
      this.modalColumns = [
        { key: 'settlement_date', label: 'Date' },
        { key: 'branch', label: 'Branch' },
        { key: 'employee', label: 'Employee' },
        { key: 'amount', label: 'Amount' },
        { key: 'approval_status', label: 'Status' },
      ];
      this.modalRows = this.bundle.recent_settlements || [];
    } else {
      return;
    }
    this.modal.open(content, { size: 'lg', backdrop: true });
  }

  onRoleOpenDetail(kind: string) {
    if (kind === 'subscribers') {
      this.onStatTileClick('subscribers');
      return;
    }
    this.openModal(this.detailModal, kind);
  }

  rowVal(row: any, key: string): any {
    if (!row || row[key] === undefined || row[key] === null) {
      return '';
    }
    return row[key];
  }

  num(v: any): number {
    const n = +v;
    return isNaN(n) ? 0 : n;
  }
}
