import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import {ReportPopupModalComponent} from '../../../modal/report-popup-modal/report-popup-modal.component';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';


@Component({
  
  selector: 'app-cancel-customer-report',
  templateUrl: './cancel-customer-report.component.html',
  styles: []
})
export class CancelCustomerReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  EnrollReport: FormGroup;
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
  gridOptions: any;
  hideReport: boolean = true;
  showReportGrid = true;
  hideDataList: boolean = true;
  nodataFoundMessage: boolean=true;
  grandChitValue: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewenrollmentInfo: ViewData[]=[];
  familyList: any;

  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.EnrollReport.controls;
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
      "conditions[tenant_id]" : this.EnrollReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.EnrollReport.get('tenant_id').value,
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

        this.userservice.getData(cond, "get-family-codes", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.familyList = response.data;
            } else {
              this.familyList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.EnrollReport.get('tenant_id').value,
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

        this.userservice.getData(cond1, "list-family-codes", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.familyList = response.data;
            } else {
              this.familyList = [];
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
    //this.EnrollReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.EnrollReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.EnrollReport.get('branch_id').setValue('');
    this.EnrollReport.get('group_id').setValue('');
    this.groupList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.EnrollReport.value);
    if (this.EnrollReport.invalid) 
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
    
    this.userservice.getData(result, "enrol-report-cancel", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          console.log(response)
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewenrollmentInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Branch Name', field: 'branch_name',resizable: true  },
            {headerName: 'Group Name', field: 'group_name',resizable: true },
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true },
            {headerName: 'Ticket %', field: 'ticket_type',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true },
            {headerName: 'Customer Name', field: 'subscriber_name',resizable: true },
            {headerName: 'Mobile No', field: 'subscriber_mobile_no',resizable: true},
            {headerName: 'Reference Type', field: 'agent_type',resizable: true },
            {headerName: 'Referred By', field: 'introducer_full_name',resizable: true },
            {headerName: 'Date of Joining', field: 'date_introduced',resizable: true },
            {headerName: 'Date of Cancel', field: 'date_cancel',resizable: true },
            {headerName: 'Paid Amount', field: 'paid_amount',resizable: true },
            {headerName: 'Return / Transfer Amount', field: 'return_amount',resizable: true },
            
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.grandChitValue=response.grand_chit_value;
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
          /*this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = false;
          },0);*/
        } else {
      this.hideReport = true;
      this.nodataFoundMessage=true;
          this.appService.showAlert("Enrollment Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) {
        this.appService.showAlert("Enrollment Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Enrollment Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Enrollment Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }
  
openPopup(record,chequeType) {
    
    if(chequeType=='checkReturnClk') {
      record.context.parentComponent.isChequeReturnClk = true;
      record.context.parentComponent.isChequeClearClk = false;
    }
    else if(chequeType=='checkSubmit'){
      console.log("tetet");
      record.context.parentComponent.isChequeReturnClk = false;
      record.context.parentComponent.isChequeClearClk = false;
    }
    else {
      record.context.parentComponent.isChequeReturnClk = false;
      record.context.parentComponent.isChequeClearClk = true;
    } 
    //this.ChequeGroup.get('id').setValue(record.id);
    record.context.parentComponent.ChequeGroupId = record.data.id;
    let cond = {
      "conditions[tenant_id]" : record.context.parentComponent.ReceiptReport.get('tenant_id').value,
      "conditions[branch_id]" : record.context.parentComponent.ReceiptReport.get('branch_id').value,
    }
    record.context.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          record.context.parentComponent.bankList = response.data;
        } else {
          record.context.parentComponent.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    /*let content = '<div class="card-body"><form class="bs-example form-horizontal" [formGroup]="ChequeGroup" (ngSubmit)="onChequeSubmit()">'
    content += '<div class="row">';
    content += '<div class="col-6"><div class="form-group row"><label class="col-sm-4">Remarks</label><div class="col-sm-8"><textarea #remarks class="form-control form-control w-100 border-dark" formControlName="remarks"></textarea><div class="col-md-12" *ngIf="chequeSubmitted && cf.remarks.errors" class="invalid-feedback"><label *ngIf="cf.remarks.errors.required">Remark is required</label></div></div></div></div>';
    content += '<div class="col-6"><div class="form-group row"><label class="col-sm-4" *ngIf="isChequeClearClk">Cheque Cleared On <span class="error">*</span></label><label class="col-sm-4" *ngIf="isChequeReturnClk">Cheque Returned On <span class="error">*</span></label><div class="col-sm-8"><input #cheque_clear_return_date type="text" formControlName="cheque_clear_return_date" class="form-control calendar" placeholder="Enter Date" (click)="d3.toggle()" ngbDatepicker #d3="ngbDatepicker" onkeydown="return false"><div class="col-md-12" *ngIf="chequeSubmitted && cf.cheque_clear_return_date.errors" class="invalid-feedback"><label *ngIf="cf.cheque_clear_return_date.errors.required">Date is required</label></div></div></div></div>'
    content += '<div class="col-6" *ngIf="isChequeReturnClk"><div class="form-group row"><label class="col-sm-4">Cheque Return Amount <span class="error">*</span></label><div class="col-sm-8"><input #cheque_return_amount type="text" formControlName="cheque_return_amount" class="form-control" placeholder="Enter Return Amount"><div class="col-md-12" *ngIf="chequeSubmitted && cf.cheque_return_amount.errors" class="invalid-feedback"><label *ngIf="cf.cheque_return_amount.errors.required">Cheque return amount is required</label></div></div></div></div>'
    content += '<div class="col-6" *ngIf="isChequeClearClk"><div class="form-group row"><label class="col-sm-4">Debit To <span class="error">*</span></label><div class="col-sm-8"><select #cheque_debit_to class="form-control form-control w-100 border-dark" formControlName="cheque_debit_to"><option value="">Select Bank</option><option *ngFor="let data of bankList" value="{{data.id}}">{{data.bank_name}}</option></select></div></div></div>'
    content += '</div>';
    content += '<div class="form-group row"><div class="offset-md-2 offset-sm-2 col-sm-10 col-md-7 col-12"><button type="submit" class="btn btn-primary mr-1" [disabled]="disableChequeBtn">{{SubmitChequeButtonName}}</button><button type="button" class="btn btn-danger mr-1" (click)="c(\'Close click\')">Cancel</button></div></div></form></div>';
    */
    //(<HTMLInputElement>document.getElementById("modal-body")).innerHTML = content;
    record.context.ngxSmartModalService.getModal('myModal').open()
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'enrollment_report',
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
      pdf.save('Enrollmentreport.pdf'); // Generated PDF   
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
    this.EnrollReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      family_id: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.EnrollReport.get('tenant_id').setValue(tenant_id);
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
