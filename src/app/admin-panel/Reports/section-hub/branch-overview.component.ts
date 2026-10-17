import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { UserService } from '../../../Services/user.service';
import { HUB_SECTIONS } from './section-hub.config';

const TONES = ['t-green', 't-blue', 't-amber', 't-red', 't-purple', 't-cyan'];
const SECTION_ORDER = ['chit', 'new-customers', 'groups', 'collection', 'payment', 'crm', 'hr', 'action', 'ticket', 'documents', 'security'];

@Component({
  selector: 'app-branch-overview',
  templateUrl: './branch-overview.component.html',
  styleUrls: ['./section-hub.component.css', './branch-overview.component.css']
})
export class BranchOverviewComponent implements OnInit, OnDestroy {
  branchId = '';
  loading = false;
  error = '';
  branches: any[] = [];
  detail: any = null;
  search = '';
  tones = TONES;
  sections = SECTION_ORDER.map((key) => ({ key, cfg: HUB_SECTIONS[key] }));
  private sub: Subscription;

  constructor(private route: ActivatedRoute, private router: Router, private userservice: UserService) {}

  ngOnInit() {
    this.sub = this.route.paramMap.subscribe((params) => {
      this.branchId = params.get('id') || '';
      this.detail = null;
      if (this.branchId) {
        this.loadDetail();
      }
      if (!this.branches.length) {
        this.loadList();
      }
    });
  }

  ngOnDestroy() {
    if (this.sub) {
      this.sub.unsubscribe();
    }
  }

  loadList() {
    this.loading = !this.branchId;
    this.userservice.getData({ tenant_id: localStorage.getItem('tenant_id') }, 'branch-overview', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.branches = res.data || [];
      } else {
        this.error = (res && res.msg) || 'Unable to load branches';
      }
    }, () => {
      this.loading = false;
      this.error = 'Unable to load branches';
    });
  }

  loadDetail() {
    this.loading = true;
    this.error = '';
    const body = { tenant_id: localStorage.getItem('tenant_id'), branch_id: this.branchId };
    this.userservice.getData(body, 'branch-overview-detail', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.detail = res.data;
      } else {
        this.error = (res && res.msg) || 'Unable to load branch';
      }
    }, () => {
      this.loading = false;
      this.error = 'Unable to load branch';
    });
  }

  get filtered() {
    const q = this.search.trim().toLowerCase();
    if (!q) {
      return this.branches;
    }
    return this.branches.filter((b) => (b.branch_name + ' ' + (b.branch_code || '')).toLowerCase().indexOf(q) !== -1);
  }

  get totals() {
    const t: any = { customers: 0, active: 0, chit_value: 0, six_collection: 0, month_collection: 0, employees: 0 };
    this.branches.forEach((b) => Object.keys(t).forEach((k) => t[k] += Number(b[k]) || 0));
    return t;
  }

  openBranch(id: any) {
    this.router.navigate(['/admin-panel/branch-overview', id]);
  }

  changeBranch(id: any) {
    this.router.navigate(id ? ['/admin-panel/branch-overview', id] : ['/admin-panel/branch-overview']);
  }

  sectionLink(key: string) {
    return '/admin-panel/hub/' + key;
  }

  fmt(value: any, money: boolean) {
    const n = Number(value) || 0;
    return (money ? '₹' : '') + Math.round(n).toLocaleString('en-IN');
  }

  short(value: any) {
    const n = Number(value) || 0;
    if (n >= 10000000) {
      return '₹' + (n / 10000000).toFixed(2) + 'Cr';
    }
    if (n >= 100000) {
      return '₹' + (n / 100000).toFixed(1) + 'L';
    }
    if (n >= 1000) {
      return '₹' + (n / 1000).toFixed(1) + 'K';
    }
    return '₹' + n;
  }

  sparkMax(rows: any[]) {
    return Math.max(1, ...(rows || []).map((r) => Number(r.value) || 0));
  }

  sparkHeight(value: any, rows: any[]) {
    return Math.max(4, Math.round(((Number(value) || 0) / this.sparkMax(rows)) * 100)) + '%';
  }

  share(value: any, key: string) {
    const t = this.totals[key];
    return t ? Math.round((Number(value) || 0) * 100 / t) : 0;
  }
}
