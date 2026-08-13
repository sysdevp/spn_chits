import { Component, EventEmitter, Input, Output } from '@angular/core';
import { SafeStyle } from '@angular/platform-browser';
import {
  dashBarWidth,
  dashHasBarData,
  dashList,
  dashNum,
  dashSummary,
  ROLE_DASH_STYLES,
} from '../dashboard-view.util';

const BRANCH_ADMIN_STYLES = `
  .ba-welcome {
    background: linear-gradient(135deg, #013618 0%, #025E2D 55%, #0d9488 100%);
    border-radius: 14px;
    padding: 1.15rem 1.35rem;
    color: #fff;
    margin-bottom: 1rem;
  }
  .ba-welcome h3 { margin: 0; font-size: 1.25rem; font-weight: 700; }
  .ba-welcome p { margin: 0.25rem 0 0; opacity: 0.9; font-size: 0.88rem; }
  .ba-kpi-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
  }
  .ba-kpi {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e8eef3;
    padding: 1rem;
    min-height: 100px;
  }
  .ba-kpi-l { font-size: 0.72rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 600; }
  .ba-kpi-v { font-size: 1.3rem; font-weight: 700; color: #013618; margin-top: 0.35rem; }
  .ba-kpi-n { font-size: 0.72rem; color: #94a3b8; margin-top: 0.25rem; }
  .ba-task {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.65rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
  }
  .ba-task:last-child { border-bottom: 0; }
  .ba-badge {
    display: inline-block;
    min-width: 28px;
    text-align: center;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    background: #dcfce7;
    color: #14532d;
    font-weight: 700;
    font-size: 0.78rem;
  }
  .ba-badge.warn { background: #ffedd5; color: #9a3412; }
  .ba-badge.danger { background: #fee2e2; color: #991b1b; }
`;

@Component({
  selector: 'app-admin-dashboard-view',
  templateUrl: './admin-dashboard.component.html',
  styles: [ROLE_DASH_STYLES, BRANCH_ADMIN_STYLES],
})
export class AdminDashboardViewComponent {
  @Input() bundle: any;
  @Input() barLabels: string[] = [];
  @Input() barData: number[] = [];
  @Input() barScope = 'month';
  @Input() pieLabels: string[] = [];
  @Input() pieData: number[] = [];
  @Input() pieSafeStyle: SafeStyle;
  @Output() openDetail = new EventEmitter<string>();

  s() { return dashSummary(this.bundle); }
  n(v: any) { return dashNum(v); }
  collections() { return dashList(this.bundle, 'today_collections'); }
  outstanding() { return dashList(this.bundle, 'outstanding_customers'); }
  followups() { return dashList(this.bundle, 'today_followups'); }
  auctions() { return dashList(this.bundle, 'today_auctions'); }
  payments() { return dashList(this.bundle, 'today_payments'); }
  hasBars() { return dashHasBarData(this.barData); }
  barWidth(i: number) { return dashBarWidth(this.barData, i); }
  hasPie() { return (this.pieData || []).some((x) => dashNum(x) > 0); }

  overdueTotal(): number {
    return this.outstanding().reduce(
      (sum, o) => sum + this.n(o.pending_amount || o.amount || o.outstanding),
      0
    );
  }
}
