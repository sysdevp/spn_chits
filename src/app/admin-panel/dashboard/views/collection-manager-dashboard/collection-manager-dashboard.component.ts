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

@Component({
  selector: 'app-collection-manager-dashboard',
  templateUrl: './collection-manager-dashboard.component.html',
  styles: [ROLE_DASH_STYLES],
})
export class CollectionManagerDashboardComponent {
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
  branchRows() { return dashList(this.bundle, 'branch_collections'); }
  hasBars() { return dashHasBarData(this.barData); }
  barWidth(i: number) { return dashBarWidth(this.barData, i); }
  hasPie() { return (this.pieData || []).some((x) => dashNum(x) > 0); }
}
