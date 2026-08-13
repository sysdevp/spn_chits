import { Component, EventEmitter, Input, OnChanges, OnInit, Output, SimpleChanges } from '@angular/core';
import { DomSanitizer, SafeStyle } from '@angular/platform-browser';
import {
  dashBarWidth,
  dashHasBarData,
  dashList,
  dashNum,
  dashSummary,
  ROLE_DASH_STYLES,
} from '../dashboard-view.util';

const ADMIN_EXTRA_STYLES = `
  .ad-wrap { color: #334155; }
  .ad-kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }
  .ad-kpi {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    min-height: 108px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    position: relative;
  }
  .ad-kpi-ico {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 0.55rem;
    color: #fff;
  }
  .ad-kpi-ico.g { background: #16a34a; }
  .ad-kpi-ico.b { background: #2563eb; }
  .ad-kpi-ico.p { background: #7c3aed; }
  .ad-kpi-ico.t { background: #0d9488; }
  .ad-kpi-ico.o { background: #ea580c; }
  .ad-kpi-ico.r { background: #dc2626; }
  .ad-kpi-ico.y { background: #ca8a04; }
  .ad-kpi-l {
    font-size: 0.72rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 0.25rem;
  }
  .ad-kpi-v {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
  }
  .ad-kpi-trend {
    font-size: 0.72rem;
    margin-top: 0.35rem;
    color: #16a34a;
    font-weight: 600;
  }
  .ad-kpi-trend.down { color: #dc2626; }
  .ad-kpi-trend.muted { color: #94a3b8; font-weight: 500; }
  .ad-prog {
    height: 6px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 0.45rem;
  }
  .ad-prog > span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    border-radius: 999px;
  }
  .ad-mini-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.65rem;
    margin-bottom: 1rem;
  }
  .ad-mini {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 10px;
    padding: 0.75rem 0.85rem;
    text-align: center;
  }
  .ad-mini-l { font-size: 0.7rem; color: #64748b; font-weight: 600; margin-bottom: 0.3rem; }
  .ad-mini-v { font-size: 1.05rem; font-weight: 700; color: #0f172a; }
  .ad-mini-t { font-size: 0.68rem; color: #16a34a; margin-top: 0.2rem; }
  .ad-spark-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
    margin-bottom: 0.5rem;
  }
  .ad-spark {
    background: #fff;
    border: 1px solid #e8eef3;
    border-radius: 12px;
    padding: 0.85rem 1rem;
  }
  .ad-spark-l { font-size: 0.72rem; color: #64748b; font-weight: 600; }
  .ad-spark-v { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0.2rem 0; }
  .ad-spark-t { font-size: 0.72rem; font-weight: 600; color: #16a34a; }
  .ad-spark-t.down { color: #dc2626; }
  .ad-spark svg { width: 100%; height: 36px; margin-top: 0.35rem; display: block; }
  .ad-trend-svg { width: 100%; height: 180px; display: block; }
  .ad-vbars {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 2rem;
    height: 160px;
    padding-bottom: 1.5rem;
  }
  .ad-vbar-col { text-align: center; width: 56px; }
  .ad-vbar {
    width: 48px;
    margin: 0 auto 0.4rem;
    border-radius: 8px 8px 4px 4px;
    background: #cbd5e1;
    min-height: 8px;
  }
  .ad-vbar.ach { background: linear-gradient(180deg, #22c55e, #16a34a); }
  .ad-vbar-lbl { font-size: 0.72rem; color: #64748b; }
  .ad-vbar-amt { font-size: 0.7rem; font-weight: 700; color: #0f172a; margin-bottom: 0.25rem; }
  .ad-gauge-wrap {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0.5rem auto 0;
  }
  .ad-gauge {
    width: 100%;
    height: 100%;
    border-radius: 50%;
  }
  .ad-gauge-hole {
    position: absolute;
    inset: 22%;
    background: #fff;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .ad-gauge-pct { font-size: 1.35rem; font-weight: 800; color: #16a34a; line-height: 1; }
  .ad-gauge-sub { font-size: 0.65rem; color: #94a3b8; font-weight: 600; }
  .ad-branch-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.55rem;
    font-size: 0.82rem;
  }
  .ad-branch-name { width: 110px; font-weight: 600; color: #334155; flex-shrink: 0; }
  .ad-branch-amt { width: 90px; text-align: right; font-weight: 700; color: #0f172a; flex-shrink: 0; }
  .ad-map-box {
    min-height: 200px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    background: linear-gradient(160deg, #f0fdf4 0%, #e0f2fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 0.85rem;
    text-align: center;
    padding: 1rem;
  }
  .ad-fin-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
  }
  .ad-fin-row:last-child { border-bottom: 0; }
  .ad-fin-l { display: flex; align-items: center; gap: 0.5rem; color: #475569; }
  .ad-fin-ico {
    width: 28px; height: 28px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem; color: #fff;
  }
  .ad-alert {
    display: flex;
    gap: 0.65rem;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.82rem;
  }
  .ad-alert:last-child { border-bottom: 0; }
  .ad-alert-dot {
    width: 8px; height: 8px; border-radius: 50%;
    margin-top: 0.35rem; flex-shrink: 0;
  }
  .ad-alert-dot.red { background: #dc2626; }
  .ad-alert-dot.orange { background: #ea580c; }
  .ad-alert-dot.green { background: #16a34a; }
  .ad-alert-dot.blue { background: #2563eb; }
  .ad-biz-legend span { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 0.3rem; }
  .ad-panel-h-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .ad-link {
    font-size: 0.78rem;
    color: #16a34a;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
  }
  .ad-rank {
    display: inline-flex;
    width: 22px; height: 22px;
    align-items: center; justify-content: center;
    border-radius: 50%;
    background: #dcfce7;
    color: #14532d;
    font-size: 0.72rem;
    font-weight: 700;
  }
`;

@Component({
  selector: 'app-md-dashboard',
  templateUrl: './md-dashboard.component.html',
  styles: [ROLE_DASH_STYLES, ADMIN_EXTRA_STYLES],
})
export class MdDashboardComponent implements OnInit, OnChanges {
  @Input() bundle: any;
  @Input() barLabels: string[] = [];
  @Input() barData: number[] = [];
  @Input() barScope = 'month';
  @Input() pieLabels: string[] = [];
  @Input() pieData: number[] = [];
  @Input() pieSafeStyle: SafeStyle;
  @Output() openDetail = new EventEmitter<string>();

  sparkPoints = '0,28 20,22 40,24 60,18 80,20 100,12 120,16 140,8';
  trendPoints = '';
  trendArea = '';
  gaugeSafe: SafeStyle;
  bizDonutSafe: SafeStyle;

  constructor(private sanitizer: DomSanitizer) {
    this.gaugeSafe = this.sanitizer.bypassSecurityTrustStyle(
      'conic-gradient(#16a34a 0deg 0deg, #e2e8f0 0deg 360deg)'
    );
    this.bizDonutSafe = this.sanitizer.bypassSecurityTrustStyle(
      'conic-gradient(#16a34a 0 245deg, #94a3b8 245deg 310deg, #38bdf8 310deg 346deg, #cbd5e1 346deg 360deg)'
    );
  }

  ngOnInit() {
    this.refreshDerived();
  }

  ngOnChanges(changes: SimpleChanges) {
    this.refreshDerived();
  }

  s() { return dashSummary(this.bundle); }
  n(v: any) { return dashNum(v); }
  collections() { return dashList(this.bundle, 'today_collections'); }
  outstanding() { return dashList(this.bundle, 'outstanding_customers'); }
  followups() { return dashList(this.bundle, 'today_followups'); }
  auctions() { return dashList(this.bundle, 'today_auctions'); }
  branchRows() { return dashList(this.bundle, 'branch_collections'); }
  hasBars() { return dashHasBarData(this.barData); }
  barWidth(i: number) { return dashBarWidth(this.barData, i); }
  hasPie() { return (this.pieData || []).some((x) => dashNum(x) > 0); }

  totalGroups(): number {
    return this.n(this.s().running_groups) + this.n(this.s().vacant_groups);
  }

  achievedPct(): number {
    const pending = this.n(this.s().total_bid_pending);
    const released = this.n(this.s().total_bid_released);
    const t = pending + released;
    if (t <= 0) {
      return 0;
    }
    return Math.round((released / t) * 100);
  }

  targetBarH(): string {
    return '72%';
  }

  achievedBarH(): string {
    const pct = this.achievedPct();
    return Math.max(12, Math.min(100, pct)) + '%';
  }

  overdueTotal(): number {
    return this.outstanding().reduce(
      (sum, o) => sum + this.n(o.pending_amount || o.amount || o.outstanding),
      0
    );
  }

  refreshDerived() {
    const data = (this.barData || []).map((x) => this.n(x));
    if (data.length) {
      const max = Math.max.apply(null, data.concat([1]));
      const w = 140;
      const h = 36;
      const step = data.length > 1 ? w / (data.length - 1) : w;
      const pts = data.map((v, i) => {
        const x = Math.round(i * step);
        const y = Math.round(h - (v / max) * (h - 4) - 2);
        return x + ',' + y;
      });
      this.sparkPoints = pts.join(' ');
      const th = 160;
      const tw = 320;
      const tstep = data.length > 1 ? tw / (data.length - 1) : tw;
      const tpts = data.map((v, i) => {
        const x = Math.round(i * tstep);
        const y = Math.round(th - (v / max) * (th - 20) - 10);
        return { x, y };
      });
      this.trendPoints = tpts.map((p) => p.x + ',' + p.y).join(' ');
      this.trendArea =
        '0,' + th + ' ' +
        tpts.map((p) => p.x + ',' + p.y).join(' ') +
        ' ' + tw + ',' + th;
    } else {
      this.sparkPoints = '0,28 20,22 40,24 60,18 80,20 100,12 120,16 140,8';
      this.trendPoints = '0,120 40,100 80,110 120,70 160,85 200,50 240,60 280,40 320,55';
      this.trendArea = '0,160 ' + this.trendPoints + ' 320,160';
    }

    const pct = this.achievedPct();
    const deg = Math.max(0, Math.min(360, (pct / 100) * 360));
    this.gaugeSafe = this.sanitizer.bypassSecurityTrustStyle(
      `conic-gradient(#16a34a 0deg ${deg}deg, #e2e8f0 ${deg}deg 360deg)`
    );

    const run = this.n(this.s().running_groups);
    const vac = this.n(this.s().vacant_groups);
    const tot = run + vac;
    if (tot > 0) {
      const a = (run / tot) * 360;
      const b = a + (vac / tot) * 360 * 0.7;
      const c = Math.min(360, b + 30);
      this.bizDonutSafe = this.sanitizer.bypassSecurityTrustStyle(
        `conic-gradient(#16a34a 0deg ${a}deg, #94a3b8 ${a}deg ${b}deg, #38bdf8 ${b}deg ${c}deg, #cbd5e1 ${c}deg 360deg)`
      );
    }
  }
}
