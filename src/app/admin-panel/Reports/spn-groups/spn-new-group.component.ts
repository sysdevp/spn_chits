import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

export const SPN_GROUP_STYLES = `
  .sg-hero { background: linear-gradient(120deg, #004D2A 0%, #007A49 60%, #18A957 100%); color: #fff; border-radius: 18px; padding: 18px 22px; margin-bottom: 14px; }
  .sg-hero h1 { margin: 0 0 4px; font-size: 26px; font-weight: 800; color: #fff; }
  .sg-hero p { margin: 0; color: rgba(255,255,255,.88); }
  .sg-card { background: #fff; border-radius: 16px; padding: 14px; margin-bottom: 14px; box-shadow: 0 6px 18px rgba(7,29,58,.05); }
  .sg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; align-items: end; }
  .sg-grid label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; text-transform: uppercase; }
  .sg-grid select, .sg-grid input { width: 100%; height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; }
  .sg-btn { border: 0; border-radius: 10px; height: 40px; padding: 0 16px; font-weight: 800; cursor: pointer; background: #006B3C; color: #fff; }
  .sg-btn.light { background: #fff; color: #006B3C; border: 1px solid #006B3C; }
  .sg-btn.danger { background: #DC2626; }
  .sg-btn.sm { height: 30px; padding: 0 10px; font-size: 12px; }
  .sg-btn:disabled { opacity: .5; cursor: not-allowed; }
  .sg-mode { display: flex; gap: 8px; }
  .sg-mode button { flex: 1; height: 40px; border-radius: 10px; border: 1px solid #006B3C; background: #fff; color: #006B3C; font-weight: 800; }
  .sg-mode button.on { background: #006B3C; color: #fff; }
  .sg-chips { display: flex; flex-wrap: wrap; gap: 8px; margin: 10px 0; }
  .sg-chip { border-radius: 999px; padding: 6px 12px; background: #EDF6FF; font-weight: 800; font-size: 12px; cursor: pointer; border: 2px solid transparent; }
  .sg-chip.on { border-color: #006B3C; background: #E9FFF3; }
  .sg-cap { font-weight: 800; color: #004D2A; }
  .sg-cap.full { color: #DC2626; }
  .sg-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .sg-table th { background: #f3faf6; color: #004D2A; font-size: 12px; padding: 9px 10px; text-align: left; white-space: nowrap; }
  .sg-table td { padding: 8px 10px; border-bottom: 1px solid #eef3f0; }
  .sg-empty { padding: 24px; text-align: center; color: #5c6b80; }
  .sg-note { font-size: 12px; color: #5c6b80; margin-top: 8px; }
`;

@Component({
  selector: 'app-spn-new-group',
  templateUrl: './spn-new-group.component.html',
  styles: [SPN_GROUP_STYLES]
})
export class SpnNewGroupComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id');
  branchList: any[] = [];
  schemeList: any[] = [];
  slabList: any[] = [];
  branchId = '';
  schemeId = '';
  slabId = '';
  dueNo = '';
  groupName = '';
  remarks = '';
  mode: 'auto' | 'manual' = 'manual';
  candidates: any[] = [];
  dueSummary: any[] = [];
  maxMembers = 0;
  selected: { [id: number]: boolean } = {};
  loading = false;
  saving = false;

  constructor(private userservice: UserService, private appService: AppService, private router: Router) {}

  ngOnInit() {
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId }, 'get-schemes', 'POST')
      .subscribe((res: any) => { this.schemeList = res && res.status === 'Success' ? res.data : []; });
  }

  schemeChg() {
    this.slabId = '';
    this.slabList = [];
    this.dueNo = '';
    this.candidates = [];
    if (!this.schemeId) {
      return;
    }
    this.userservice.getData({ 'conditions[scheme_id]': this.schemeId }, 'get-slab-dues', 'POST')
      .subscribe((res: any) => { this.slabList = res && res.status === 'Success' ? res.data : []; });
    this.loadCandidates();
  }

  private filterBody(): any {
    const body: any = { tenant_id: this.tenantId, scheme_id: this.schemeId, slab_id: this.slabId };
    if (this.branchId) {
      body.branch_id = this.branchId;
    } else {
      body.branch_ids = this.branchList.map((b) => b.id);
    }
    return body;
  }

  loadCandidates() {
    if (!this.schemeId) {
      return;
    }
    this.loading = true;
    this.selected = {};
    const body = this.filterBody();
    this.userservice.getData(body, 'spn-group-available', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.candidates = res.data || [];
        this.dueSummary = res.due_summary || [];
        this.maxMembers = res.max_members || 0;
        if (this.dueNo && !this.dueSummary.some((d) => String(d.due_no) === String(this.dueNo))) {
          this.dueNo = '';
        }
      } else {
        this.candidates = [];
        this.dueSummary = [];
      }
    }, () => { this.loading = false; });
  }

  pickDue(due: any) {
    this.dueNo = String(due);
    this.selected = {};
  }

  visibleCandidates() {
    if (!this.dueNo) {
      return [];
    }
    return this.candidates.filter((c) => String(c.due_no) === String(this.dueNo));
  }

  selectedCount() {
    return Object.keys(this.selected).filter((k) => this.selected[k]).length;
  }

  toggle(row: any) {
    if (!this.selected[row.enrollment_id] && this.selectedCount() >= this.maxMembers) {
      this.appService.showAlert('SPN New Group', 'This scheme allows only ' + this.maxMembers + ' members per group.', 'error');
      return;
    }
    this.selected[row.enrollment_id] = !this.selected[row.enrollment_id];
  }

  autoCount() {
    return Math.min(this.visibleCandidates().length, this.maxMembers);
  }

  save() {
    if (!this.schemeId || !this.dueNo) {
      this.appService.showAlert('SPN New Group', 'Select scheme and Due No.', 'error');
      return;
    }
    const body = this.filterBody();
    body.due_no = this.dueNo;
    body.group_name = this.groupName;
    body.remarks = this.remarks;
    body.formation_mode = this.mode;
    body.created_by = localStorage.getItem('logged_employee_id');
    if (this.mode === 'manual') {
      body.enrollment_ids = Object.keys(this.selected).filter((k) => this.selected[k]).map(Number);
      if (!body.enrollment_ids.length) {
        this.appService.showAlert('SPN New Group', 'Select customers for the group.', 'error');
        return;
      }
    }
    this.saving = true;
    this.userservice.getData(body, 'spn-group-store', 'JSON').subscribe((res: any) => {
      this.saving = false;
      if (res && res.status === 'Success') {
        this.appService.showAlert('SPN New Group', res.msg, 'success');
        this.groupName = '';
        this.remarks = '';
        this.loadCandidates();
      } else {
        this.appService.showAlert('SPN New Group', (res && res.msg) || 'Unable to create group', 'error');
      }
    }, () => {
      this.saving = false;
      this.appService.showAlert('SPN New Group', 'Unable to create group', 'error');
    });
  }

  goView() {
    this.router.navigate(['/admin-panel/spn-group-view']);
  }
}
