import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { SPN_GROUP_STYLES } from './spn-new-group.component';

@Component({
  selector: 'app-spn-group-view',
  templateUrl: './spn-group-view.component.html',
  styles: [SPN_GROUP_STYLES + `
    .sg-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
    .sg-kpi { border-radius: 16px; padding: 14px; background: #E9FFF3; }
    .sg-kpi span { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #5c6b80; }
    .sg-kpi b { font-size: 24px; color: #071D3A; }
    .sg-bar { height: 8px; background: #eef3f0; border-radius: 99px; overflow: hidden; min-width: 80px; }
    .sg-bar i { display: block; height: 100%; background: #18A957; }
    tr.on td { background: #F0FDF4; }
  `]
})
export class SpnGroupViewComponent implements OnInit {
  tenantId = localStorage.getItem('tenant_id');
  branchList: any[] = [];
  schemeList: any[] = [];
  slabList: any[] = [];
  filters: any = { branch_id: '', scheme_id: '', slab_id: '', due_no: '', search: '', month: '', year: '' };
  readonly monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  readonly years = Array.from({ length: 6 }, (_, i) => new Date().getFullYear() - i);
  groups: any[] = [];
  loading = false;
  active: any = null;
  members: any[] = [];
  freeChits: any[] = [];
  addId = '';

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, log_id: localStorage.getItem('logged_employee_id') }, 'get-branches', 'POST')
      .subscribe((res: any) => { this.branchList = res && res.status === 'Success' ? res.data : []; });
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId }, 'get-schemes', 'POST')
      .subscribe((res: any) => { this.schemeList = res && res.status === 'Success' ? res.data : []; });
    this.load();
  }

  schemeChg() {
    this.filters.slab_id = '';
    this.slabList = [];
    if (this.filters.scheme_id) {
      this.userservice.getData({ 'conditions[scheme_id]': this.filters.scheme_id }, 'get-slab-dues', 'POST')
        .subscribe((res: any) => { this.slabList = res && res.status === 'Success' ? res.data : []; });
    }
    this.load();
  }

  load() {
    this.loading = true;
    const body = Object.assign({ tenant_id: this.tenantId }, this.filters);
    this.userservice.getData(body, 'spn-group-list', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.groups = res && res.status === 'Success' ? (res.data || []) : [];
    }, () => { this.loading = false; this.groups = []; });
  }

  reset() {
    this.filters = { branch_id: '', scheme_id: '', slab_id: '', due_no: '', search: '', month: '', year: '' };
    this.slabList = [];
    this.load();
  }

  totalMembers() {
    return this.groups.reduce((s, g) => s + Number(g.members || 0), 0);
  }

  totalVacant() {
    return this.groups.reduce((s, g) => s + Number(g.vacant || 0), 0);
  }

  fullGroups() {
    return this.groups.filter((g) => Number(g.vacant) === 0).length;
  }

  fillPct(g: any) {
    return g.max_members ? Math.round((g.members / g.max_members) * 100) : 0;
  }

  open(g: any) {
    if (this.active && this.active.id === g.id) {
      this.active = null;
      return;
    }
    this.active = g;
    this.loadMembers();
  }

  loadMembers() {
    if (!this.active) {
      return;
    }
    this.members = [];
    this.freeChits = [];
    this.addId = '';
    this.userservice.getData({ id: this.active.id }, 'spn-group-members', 'JSON').subscribe((res: any) => {
      this.members = res && res.status === 'Success' ? (res.data || []) : [];
      const grp = res && res.group;
      if (grp && this.active.vacant > 0) {
        const body: any = { tenant_id: this.tenantId, scheme_id: grp.scheme_id, slab_id: grp.slab_id || '', due_no: grp.due_no };
        if (grp.branch_id) {
          body.branch_id = grp.branch_id;
        }
        this.userservice.getData(body, 'spn-group-available', 'JSON').subscribe((r: any) => {
          this.freeChits = r && r.status === 'Success' ? (r.data || []) : [];
        });
      }
    });
  }

  addMember() {
    if (!this.addId) {
      return;
    }
    this.userservice.getData({ group_id: this.active.id, enrollment_id: this.addId }, 'spn-group-add-member', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.active.members++;
        this.active.vacant--;
        this.loadMembers();
      } else {
        this.appService.showAlert('Group View', (res && res.msg) || 'Unable to add', 'error');
      }
    });
  }

  removeMember(m: any) {
    if (!confirm('Remove ' + m.customer_name + ' from this group?')) {
      return;
    }
    this.userservice.getData({ member_id: m.member_id }, 'spn-group-remove-member', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.active.members--;
        this.active.vacant++;
        this.loadMembers();
      }
    });
  }

  deleteGroup(g: any) {
    if (!confirm('Delete group ' + g.group_name + '? Members will become free.')) {
      return;
    }
    this.userservice.getData({ id: g.id }, 'spn-group-delete', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.active = null;
        this.load();
      }
    });
  }

  exportCsv() {
    const head = ['S.No', 'Group', 'Branch', 'Scheme', 'Slab', 'Due No', 'Members', 'Max', 'Vacant', 'Mode', 'Created'];
    const lines = [head.join(',')].concat(this.groups.map((g) => [
      g.sno, g.group_name, g.branch_name, g.scheme_name, g.slab_name, g.due_no, g.members, g.max_members, g.vacant, g.formation_mode, g.created_at
    ].map((v) => '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"').join(',')));
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'spn-groups.csv';
    a.click();
  }
}
