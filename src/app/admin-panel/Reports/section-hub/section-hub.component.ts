import { Component, OnInit, OnDestroy } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';
import { UserService } from '../../../Services/user.service';
import { HUB_SECTIONS, HubSection } from './section-hub.config';

const PALETTE = ['#16A34A', '#2563EB', '#F59E0B', '#DC2626', '#7C3AED', '#0891B2', '#DB2777', '#EA580C', '#65A30D', '#4F46E5'];
const TONES = ['t-green', 't-blue', 't-amber', 't-red', 't-purple', 't-cyan'];

@Component({
  selector: 'app-section-hub',
  templateUrl: './section-hub.component.html',
  styleUrls: ['./section-hub.component.css']
})
export class SectionHubComponent implements OnInit, OnDestroy {
  key = '';
  cfg: HubSection = null;
  data: any = null;
  loading = false;
  error = '';
  branchId = '';
  branches: any[] = [];
  palette = PALETTE;
  tones = TONES;
  private sub: Subscription;

  constructor(private route: ActivatedRoute, private userservice: UserService) {}

  ngOnInit() {
    this.sub = this.route.paramMap.subscribe((params) => {
      this.key = params.get('section') || '';
      this.cfg = HUB_SECTIONS[this.key] || null;
      this.branchId = this.route.snapshot.queryParamMap.get('branch') || '';
      this.load();
    });
  }

  ngOnDestroy() {
    if (this.sub) {
      this.sub.unsubscribe();
    }
  }

  load() {
    if (!this.cfg) {
      return;
    }
    this.loading = true;
    this.error = '';
    const body: any = { tenant_id: localStorage.getItem('tenant_id'), section: this.key };
    if (this.branchId) {
      body.branch_id = this.branchId;
    }
    this.userservice.getData(body, 'section-hub-summary', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res && res.status === 'Success') {
        this.data = res.data;
        this.branches = res.data.branches || [];
      } else {
        this.error = (res && res.msg) || 'Unable to load summary';
      }
    }, () => {
      this.loading = false;
      this.error = 'Unable to load summary';
    });
  }

  link(route: string) {
    return '/admin-panel/' + route;
  }

  fmt(value: any, money: boolean) {
    const n = Number(value) || 0;
    if (!money) {
      return n.toLocaleString('en-IN');
    }
    return '₹' + Math.round(n).toLocaleString('en-IN');
  }

  short(value: any, money: boolean) {
    const n = Number(value) || 0;
    const p = money ? '₹' : '';
    if (n >= 10000000) {
      return p + (n / 10000000).toFixed(1) + 'Cr';
    }
    if (n >= 100000) {
      return p + (n / 100000).toFixed(1) + 'L';
    }
    if (n >= 1000) {
      return p + (n / 1000).toFixed(1) + 'K';
    }
    return p + n;
  }

  max(rows: any[]) {
    return Math.max(1, ...(rows || []).map((r) => Number(r.value) || 0));
  }

  barHeight(value: any, rows: any[]) {
    return Math.max(2, Math.round(((Number(value) || 0) / this.max(rows)) * 100)) + '%';
  }

  total(rows: any[]) {
    return (rows || []).reduce((s, r) => s + (Number(r.value) || 0), 0);
  }

  pct(value: any, rows: any[]) {
    const t = this.total(rows);
    return t ? ((Number(value) || 0) * 100 / t).toFixed(1) : '0.0';
  }

  donutStyle(rows: any[]) {
    const t = this.total(rows);
    if (!t) {
      return { background: '#E5EDF5' };
    }
    let acc = 0;
    const parts = (rows || []).map((r, i) => {
      const from = acc;
      acc += (Number(r.value) || 0) * 100 / t;
      return `${PALETTE[i % PALETTE.length]} ${from}% ${acc}%`;
    });
    return { background: `conic-gradient(${parts.join(', ')})` };
  }

  /** SVG polyline points for the trend panel (viewBox 0 0 300 120). */
  linePoints(rows: any[]) {
    const list = rows || [];
    if (!list.length) {
      return '';
    }
    const m = this.max(list);
    const step = list.length > 1 ? 280 / (list.length - 1) : 0;
    return list.map((r, i) => {
      const x = 10 + i * step;
      const y = 105 - ((Number(r.value) || 0) / m) * 90;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    }).join(' ');
  }

  areaPoints(rows: any[]) {
    const line = this.linePoints(rows);
    if (!line) {
      return '';
    }
    const list = rows || [];
    const step = list.length > 1 ? 280 / (list.length - 1) : 0;
    return `10,105 ${line} ${(10 + (list.length - 1) * step).toFixed(1)},105`;
  }

  pointX(i: number, rows: any[]) {
    const n = (rows || []).length;
    return 10 + i * (n > 1 ? 280 / (n - 1) : 0);
  }

  pointY(value: any, rows: any[]) {
    return 105 - ((Number(value) || 0) / this.max(rows)) * 90;
  }

  statusClass(value: any) {
    const v = String(value || '').toLowerCase();
    if (/(active|paid|approved|success|eligible|enrolled|completed|resolved|returned|today|low)/.test(v) && !/not|in ?active/.test(v)) {
      return 'st-ok';
    }
    if (/(pending|progress|draft|upcoming|field|admin|medium|with office)/.test(v)) {
      return 'st-wait';
    }
    if (/(rejected|closed|cancel|overdue|failed|inactive|not|high)/.test(v)) {
      return 'st-bad';
    }
    return 'st-info';
  }

  exportCsv(table?: any, idx?: number) {
    const t = table || (this.data && this.data.table);
    if (!t || !t.rows || !t.rows.length) {
      return;
    }
    const head = t.columns.map((c) => '"' + c.label + '"').join(',');
    const body = t.rows.map((r) => t.columns.map((c) => '"' + String(r[c.key] == null ? '' : r[c.key]).replace(/"/g, '""') + '"').join(',')).join('\n');
    const blob = new Blob([head + '\n' + body], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'spn-' + this.key + (idx ? '-' + idx : '') + '.csv';
    a.click();
  }
}
