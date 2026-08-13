import { Component } from '@angular/core';
import { ICellRendererAngularComp } from 'ag-grid-angular';

@Component({
  selector: 'app-settlement-approve-cell',
  template: `
    <button *ngIf="canApprove"
      type="button"
      class="btn btn-sm btn-success"
      style="padding:2px 8px;font-size:12px;"
      (click)="onApprove()">
      Approve
    </button>
    <span *ngIf="!canApprove" class="text-muted">-</span>
  `
})
export class SettlementApproveCellComponent implements ICellRendererAngularComp {
  params: any;
  canApprove = false;

  agInit(params: any): void {
    this.params = params;
    this.canApprove = !(params && params.data && Number(params.data.is_approved) === 1);
  }

  refresh(params?: any): boolean {
    if (params) {
      this.params = params;
      this.canApprove = !(params.data && Number(params.data.is_approved) === 1);
    }
    return true;
  }

  onApprove(): void {
    if (this.params && typeof this.params.action === 'function') {
      this.params.action(this.params);
    }
  }
}
