import { Component, ViewChild, ViewContainerRef } from '@angular/core';
import { ICellRendererAngularComp } from 'ag-grid-angular';
import { ICellRendererParams, IAfterGuiAttachedParams } from 'ag-grid';

@Component({
  selector: 'report-cheque-submit-modal',
  template: `<div *ngIf="cheque_status==2" class="ht">
     <a style="margin-left:1%;cursor:pointer; font-size:12px;" (click)="onClick('checkSubmit');"><span class="badge badge-success req">Submit To Bank</span></a><br/>
    </div>
    <div *ngIf="cheque_status!=2">-</div> `
})

export class ReportChequeSubmitComponent implements ICellRendererAngularComp {
  @ViewChild('dynamicTemplate', {read: ViewContainerRef}) dynamicTemplate;
  params;
  label: string;
  receipt_id;
  cheque_status;
  submit_to_bank;
  //paramTemplate;
  agInit(params): void {  
    this.params = params;
    this.label = params.label || null;
    this.receipt_id = params.data.id || null;
    this.cheque_status = params.data.status || null;
    //this.submit_to_bank = params.data.submit_to_bank || null;
    
  }

  refresh(params?: any): boolean {
    return true;
  }

  /*onClick($event) { 
    if (this.params.onClick instanceof Function) {
      // put anything into params u want pass into parents component
      const params = {
        event: $event,
        rowData: this.params.node.data
        // ...something
      } 
      console.log("params",params);
      this.params.onClick(params);
    }
  }*/

  onClick(chequeType): void { 
    this.params.action(this.params,chequeType);
  }
}
