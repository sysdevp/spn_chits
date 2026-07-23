import { Component } from '@angular/core';
import { ICellRendererAngularComp } from 'ag-grid-angular';
import { ICellRendererParams, IAfterGuiAttachedParams } from 'ag-grid';
import {UserService } from '../../Services/user.service';

@Component({
  selector: 'report-popup-modal',
  template: '<a style="margin-left:5%;cursor:pointer;" (click)="onClick()"><span class="btn btn-primary btn-view">{{label}}</span></a>',
  //template: {{tempStyle}}
})

export class ReportPopupModalComponent implements ICellRendererAngularComp {
  params;
  label: string;
  //tempStyle;

  constructor(private userservice: UserService) {
  }
  /*agInit(params): void { 
    this.params = params;
    this.label = this.params.label || null;
  }*/

  agInit(params): void { //console.log(params)
    this.params = params;
    this.label = params.label || '';
    //this.tempStyle = params.rendererTemp || '';
    /*if (_.isNil(params.action)) {
      throw new Error('Missing action parameter for ActionCellRendererComponent');
    }*/
  }

  refresh(params?: any): boolean {
    return true;
  }

  /*onClick($event) { console.log("kk");
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

  onClick(): void { 
    this.params.action(this.params);
  }
}
