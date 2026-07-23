import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import swal from 'sweetalert2';
import { GridOptions } from 'ag-grid/main';
import {ReportPopupModalComponent} from '../../../modal/report-popup-modal/report-popup-modal.component';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

import { Router } from '@angular/router';
@Component({
  
  selector: 'app-vacant-report',
  templateUrl: './vacant-report.component.html',
  styles: []
})

export class VacantReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  VacantReport: FormGroup;
  dataList: any;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  
  paginationPageSize;
  paginationNumberFormatter;
  gridApi;
  defaultColDef;
  columnDefs: any[];
  rowData: any[];
  gridOptions: GridOptions;
  hideReport: boolean = true;
  showReportGrid = true;
  hideDataList: boolean = true;
  nodataFoundMessage: boolean=true;
  totalChitValue: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewvacantInfo: ViewData[]=[];

  constructor(private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
    this.gridOptions = <GridOptions>{
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this,
      }
    };
  }

  get f() {
    return this.VacantReport.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  branchChg(event) 
  {
    let cond = {
      "conditions[tenant_id]" : this.VacantReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.VacantReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.groupList = response.data;
            } else {
              this.groupList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.VacantReport.get('tenant_id').value,
        }
        this.userservice.getData(cond1, "list-groups", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.groupList = response.data;
            } else {
              this.groupList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else{

      }
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }

  resetReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.VacantReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.VacantReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.VacantReport.get('branch_id').setValue('');
    this.VacantReport.get('group_id').setValue('');
    this.groupList = [];
  }

  fillGrp(record) {  
    let cond = {
      "conditions[tenant_id]" : record.data.tenant_id,
      "conditions[branch_id]" : record.data.branch_id,
      "conditions[group_id]" : record.data.id,
      "created_by" : localStorage.getItem('logged_user_id'),
    }
    swal({
      title: "Are you sure?",
      text: "You are fill the group.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, fill it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) { 
      record.context.userservice.getData(cond, "fill-group", "POST").subscribe((response: any) => {
        try {  
          if (response.status == 'Success') { 
            record.context.parentComponent.refreshData();
            //return true;
            /*console.log("this.rowData1",this.rowData)
            this.rowData.splice(record.rowIndex, 1);
            console.log("this.rowData2",this.rowData)
            this.gridApi.setRowData(this.rowData)*/
            /*this.gridOptions.api.setRowData(this.gridOptions.rowData)
            console.log("1"); console.log(record.context.VacantReport)
            const result1 = Object.assign({}, record.context.VacantReport.value); console.log("kk")
            record.context.userservice.getData(result1, "get-vacant-report", "POST").subscribe((response: any) => {
              record.context.SubmitbuttonName = "Submit";
              try {
                if (response.status == 'Success') {
                  record.context.setData(response.data);
                  record.context.appService.showAlert("Vacant Report", 'Group filled successfully', "success"); 
                  setTimeout(() => {
                    record.context.TableHidden = false;
                  },0);
                } else {
                  record.context.appService.showAlert("Vacant Report", response.msg, "error");
                }
              } catch (err) {
                record.context.appService.showAlert("Vacant Report", 'Sorry!! Something went wrong!', "error");
              }
            }, (err: any) => {console.log(err)
              record.context.disableBtn = false;
              record.context.SubmitbuttonName = "Submit";
              if (err.status == 401) {
                record.context.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
              }
              else {
                record.context.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
              }
            });*/
          } else {
            record.context.appService.showAlert("Vacant Report", response.msg, "error");
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
  });
  }

  refreshData() {
    
    //const result1 = Object.assign({}, this.VacantReport.value); 
    this.appService.showAlert("Vacant Report", 'Group filled successfully', "success"); 
    this.onSubmit();
    /*this.userservice.getData(result1, "get-vacant-report", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
         // this.setData(response.data);
         // this.appService.showAlert("Vacant Report", 'Group filled successfully', "success"); 
         // setTimeout(() => {
         //   this.TableHidden = false;
         // },0);
          if(response.data != 0) {
            this.nodataFoundMessage=true;
          if (response.status == 'Success') {
            this.columnDefs = [
              {headerName: 'Sno', field: 'sno',sort: "asc"},
              {headerName: 'Branch Name', field: 'branch_name' },
              {headerName: 'Group Name', field: 'group_name'},
              {headerName: 'Group Start Date', field: 'starting_date' },
              {headerName: 'Chit Value', field: 'chit_value' },
              {headerName: 'No Of Vacant', field: 'group_pending_ticket' },
              {headerName: 'No of Dummy chits', field: 'dummy_chits'},
              {headerName: 'Fill Group', 
                field: 'fill_group', 
                cellRendererFramework: ReportPopupModalComponent,
                cellRendererParams: {
                  label: 'Fill Group',
                  action: this.fillGrp
                }
              },
            ];
            this.defaultColDef = { 
              sortable: true,
              width: 150, 
            };
            applyReportGridData(this, response.data);
            if((response.data.length)>0) {
              this.hideDataList = false; 
              this.nodataFoundMessage = true;
              this.hideReport = false;
            }
            else {
              this.hideDataList = true; 
              this.nodataFoundMessage = false;
              this.hideReport = true;	
            }
            this.paginationPageSize = 10;
            this.paginationNumberFormatter = function(params) {
              return "[" + params.value.toLocaleString() + "]";
            };
          } else {
            this.hideReport = true;
            this.nodataFoundMessage=true;
            this.appService.showAlert("Vacant Report", response.msg, "error");
          }
          }
          else{
            this.hideReport = true;
            this.nodataFoundMessage=false;
          
          }
        } else {
          this.appService.showAlert("Vacant Report", response.msg, "error");
        }
      } catch (err) {
        this.appService.showAlert("Vacant Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
      }
    });*/
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.VacantReport.value);
    if (this.VacantReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    } 
    
    this.userservice.getData(result, "get-vacant-report", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewvacantInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true},
            {headerName: 'Group Start Date', field: 'starting_date',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true },
            {headerName: 'No Of Vacant', field: 'group_pending_ticket',resizable: true },
            {headerName: 'No of Dummy chits', field: 'dummy_chits',resizable: true},
            {headerName: 'Fill Group', 
              field: 'fill_group', 
              cellRendererFramework: ReportPopupModalComponent,
              cellRendererParams: {
                label: 'Fill Group',
                action: this.fillGrp,
                //rendererTemp: '<a style="margin-left:5%;cursor:pointer;" (click)="onClick()"><span class="badge badge-primary">{{label}}</span></a>',
              }
            },
            //{headerName: 'Fill Group', field: 'fill_group', cellRenderer: this.fillGrpFunc },
            /*{headerName: 'Fill Group', field: 'fill_group', cellRenderer: function(params) {
              return '<a style="margin-left:5%;cursor:pointer;" (click)="this.fillGrp(params)"><span class="badge badge-primary">Fill Group</span></a>';
              //return '<a href="/admin/edit/' + params.data.Id + '">' + params.value + '</a>';
            } },*/
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          /*this.gridOptions.context = {
            userservice: this.userservice
          }*/
        applyReportGridData(this, response.data);
        this.totalChitValue=response.grand_tot_chit_value;
        this.dataList = response.data;
		  if((response.data.length)>0) {
        this.hideDataList = false; 
        this.nodataFoundMessage = true;
        this.hideReport = false;
		  }
		  else {
        this.hideDataList = true; 
        this.nodataFoundMessage = false;
        this.hideReport = true;	
		  }
      this.paginationPageSize = 10;
      this.paginationNumberFormatter = function(params) {
        return "[" + params.value.toLocaleString() + "]";
      };
    } else {
      this.hideReport = true;
      this.nodataFoundMessage=true;
          this.appService.showAlert("Vacant Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) { 
        this.appService.showAlert("Vacant Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Vacant Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  /*fillGrpFunc(params) { 
    //params.fillGrp = this.fillGrp(params);
    return '<a style="margin-left:5%;cursor:pointer;" (click)="this.fillGrp('+params.data+')"><span class="badge badge-primary">Fill Group</span></a>';
  }*/
  
  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'vacant_report',
      columnSeparator: ','
    };
    this.gridApi.exportDataAsCsv(params);
  }

  onBtnpdf():void
  {
    this.pdfrectab=false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    this.selectionfield=true;
  }
  oncancel():void
	  {
		this.pdfrectab=true;
		this.hideReport=false;
		this.selectionfield=false;
	  }

  onpdf():void
  {  
    var data = document.getElementById('mygrid');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 210;   
      var pageHeight = 295;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 0;  
      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight) 
      heightLeft -= pageHeight; 
      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      } 
      pdf.save('Vacantreport.pdf'); // Generated PDF   
    });  
  }
  
  onPageSizeChanged() {
    var value = (<HTMLInputElement>document.getElementById("page-size")).value;
    this.gridApi.paginationSetPageSize(Number(value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  ngOnInit() {
    this.VacantReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.VacantReport.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
  } 
}
export interface ViewData 
{
    sno:string;
    branch_name: string;
}
