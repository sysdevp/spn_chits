import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
import { Router } from '@angular/router';
import {ReportPopupModalComponent} from '../../../modal/report-popup-modal/report-popup-modal.component';
//import {ReportRedirectModalComponent} from '../../../../modal/suit-file/suit-modal.component';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-overall-business-report',
  templateUrl: './overall-business-report.component.html',
  styles: []
})
export class OverallBusinessReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('e1') e1: ElementRef;
  @ViewChild('e1table') e1table: ElementRef;

  ReceiptReport: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden: boolean = true;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  isSuperAdmin: boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;
  customerList: any[];
  enrollmentList: any[];
  employeeList: any[];
  paymentList: any[];
  bankList: any[];
  ScrollCls: string = "";
  Scrollwidth: number;
  ScrollOpacity: number=0;
  isChequeReturnClk: boolean = false;
  isChequeClearClk: boolean = false;
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
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  ChequeGroupId: any;
  totalBonusAmt: any;
  totalPenaltyAmt: any;
  totalReceiptAmt: any;
  totalReceivedAmt: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewreceiptInfo: ViewData[]=[];
  data_all: any;
  data_of_val: any;
  enrollList: any[];
  test: any;
  dataofval: any;
  areaList: any;

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal,private renderer: Renderer2, private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService,private router: Router) {
    this.SubmitbuttonName = "Filter";
    this.SubmitChequeButtonName = "Submit";
    this.gridOptions = <GridOptions>{
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this,
        ngxSmartModalService: this.ngxSmartModalService,
        ChequeGroup: this.ChequeGroup,
        //AddAdvancedReceiptReport: this.AddAdvancedReceiptReport
      },
      components: {
      },
    };
  }

  get f() {
    return this.ReceiptReport.controls;
  }

  get cf() {
    return this.ChequeGroup.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.ReceiptReport.get('branch_id').setValue('');
    this.branchList = [];
    this.ReceiptReport.get('group_id').setValue('');
    this.groupList = [];
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData('', "list-payment-types", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.paymentList = response.data;
        } else {
          this.paymentList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event) {
    this.ReceiptReport.get('group_id').setValue('');
    this.groupList = [];
    let cond = {
      "conditions[branch_id]": event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.areaList = response.data;
        } else {
          this.areaList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  grpChg(event) {
    this.ReceiptReport.get('customer_id').setValue('');
    this.customerList = this.enrollmentList;
    let cond = {
      "conditions[branch_id]": this.ReceiptReport.get('branch_id').value,
      "conditions[group_id]": event.target.value,
    }
    this.userservice.getData(cond, "get-enrolled-customers", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          let enrollList = [];
          for(let i=0; i<(response.data.length); i++) {
            let selectedRecord =  this.customerList.filter((listing: any) => listing.id == response.data[i].customer_id);
            if(selectedRecord.length>0) {
              enrollList.push(selectedRecord[0]);
            }
          }
          this.customerList = enrollList;
        } else {
          this.customerList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  checkReturnClk() { 
    this.chequeSubmitted = false;
    this.clearChequeData();
    this.isChequeReturnClk = true;
    this.isChequeClearClk = false;
    this.checkValidate();
  }

  checkClearClk() {
    this.chequeSubmitted = false;
    this.clearChequeData();
    this.isChequeClearClk = true;
    this.isChequeReturnClk = false;
    this.checkValidate();
  }

  setData(data) {
    this.dataList = data;
    $(function () {
      $('#dtable').DataTable();
    });  
  }

  resetReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.ReceiptReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.ReceiptReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.ReceiptReport.get('branch_id').setValue('');
    this.ReceiptReport.get('group_id').setValue('');
    this.groupList = [];
    this.customerList = [];
    this.employeeList = [];
    this.paymentList = [];
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  checkValidate() { 
    const returnAmtCtrl = this.ChequeGroup.get('cheque_return_amount');
    const debitToCtrl = this.ChequeGroup.get('cheque_debit_to');
    if(this.isChequeReturnClk) {
      returnAmtCtrl.setValidators([Validators.required]);
      debitToCtrl.setValidators(null);
    }
    else if(this.isChequeClearClk) {
      returnAmtCtrl.setValidators(null);
      debitToCtrl.setValidators([Validators.required]);
    }
    else {
      returnAmtCtrl.setValidators(null);
      debitToCtrl.setValidators(null);
    }
    returnAmtCtrl.updateValueAndValidity();
    debitToCtrl.updateValueAndValidity();
  }

  clearChequeData() {
    this.ChequeGroup.get('remarks').setValue('');
    this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
    this.ChequeGroup.get('cheque_return_amount').setValue('');
    this.ChequeGroup.get('cheque_debit_to').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    const result = Object.assign({}, this.ReceiptReport.value);
    if (this.ReceiptReport.invalid) {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "overall-business-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
         
          this.viewreceiptInfo = response.data[0];
          
          
          //console.log(this.enrollList);
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true },
            {headerName: 'No of Tickets', field: 'no_of_tickets',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true},
            {headerName: 'Completed Auction', field: 'completed_auction' ,resizable: true},
            {headerName: 'Commission Received', field: 'commission_amount' ,resizable: true},
            {headerName: 'Overall Released Amount', field: 'company_released' ,resizable: true},
			      {headerName: 'Customer Paid Amount', field: 'customer_paid',resizable: true},
            
            {headerName: 'Status', field: 'status',resizable: true },
            
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
         
          this.dataList = response.data;
		  if((response.data.length)>0) {
      this.hideDataList = false; 
      this.nodataFoundMessage=true;
		  }
		  else {
       this.hideDataList = true;  
       this.nodataFoundMessage=true;
		  }
          this.hideReport = false;
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function(params) {
            return "[" + params.value.toLocaleString() + "]";
          };
        } else {
          this.hideReport = true;
          this.nodataFoundMessage=true;
          this.appService.showAlert("Overall Business Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      
      } catch (err) {
        this.appService.showAlert("Overall Business Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Overall Business Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Overall Business Report", "Sorry!! Something went wrong!", "error");
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

  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'groupwise_report',
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
      var data = document.getElementById('contentToConvert');  
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
        pdf.save('Outstanding_Report.pdf'); // Generated PDF   
      });  
    }

  onPageSizeChanged() {
    var value = (<HTMLInputElement>document.getElementById("page-size")).value;
    this.gridApi.paginationSetPageSize(Number(value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  chequeClearanceClk(event) {
    console.log("event",event)
  }

  onChequeSubmit() {
   
    
    let cond1 = {
      "tenant_id" : this.ReceiptReport.get('tenant_id').value,
      "branch_id" : this.ReceiptReport.get('branch_id').value,
      "group_id" : this.ReceiptReport.get('group_id').value,
      "area_id" : this.ReceiptReport.get('area_id').value,
      "prized_status" : this.ReceiptReport.get('prized_status').value,
      "database_name" : "",

    }
    this.userservice.getData(cond1, "group-wise-outstanding-report", "JSON").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          for(let i=0;i<response.data.length;i++){
            
            
            if(response.data[i].id == this.ChequeGroupId){
              this.test = response.data[i];
              console.log(this.test);
            }
            
              
        }
        
          
          let cond2 = {
            "tenant_id" : this.test.tenant_id,
            "branch_id" : this.test.branch_id,
            "group_id" : this.test.group_id,
            "customer_id" : this.test.customer_id,
            "enroll_id" : this.test.id,
            "ticket_no" : this.test.ticket_no,
            "lawyer_id" : 0,
            "lawyer_fee" : 0,
            "case_stage" : 1,
            "chit_amount" : this.test.chit_value,
            "case_expence" : 0,
            "case_status" :0,
            "remarks" :this.ChequeGroup.get('remarks').value
      
          }
          console.log(cond2);
          this.userservice.getData(cond2, "add-suit-filling", "JSON").subscribe((response: any) => {
            try { 
              if (response.status == 'Success') { 
                this.router.navigate(['/admin-panel/suit-file-report']);
               
              } else {
                
              }
            }  catch (err) { }
          }, (err: any) => { });
          

        } else {
          
        }
      }  catch (err) { }
    }, (err: any) => { });


    
  }

  ngOnInit() {
    this.ReceiptReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      area_id: [''],
      prized_status: [''],
    });

    this.ChequeGroup = this.formBuilder.group({
      id: [''],
      remarks: ['', Validators.required],
      test: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.ReceiptReport.get('tenant_id').setValue(tenant_id);
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
        } catch (err) { }
      }, (err: any) => { });
    }
  }
}
export interface ViewData 
{
    sno:string;
    branch_name: string;
}



