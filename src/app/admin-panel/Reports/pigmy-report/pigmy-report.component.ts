import { Component, OnInit, ElementRef, ViewChild, Renderer2 } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
//import {ReportPopupModalComponent} from '../../../../modal/report-popup-modal/report-popup-modal.component';
import {ReportRedirectModalComponent} from '../../../modal/report-redirect-modal/report-redirect-modal.component';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-pigmy-report',
  templateUrl: './pigmy-report.component.html',
  styles: []
})
export class PigmyReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('e1') e1: ElementRef;
  @ViewChild('e1table') e1table: ElementRef;

  AddBeforeEnrolReceiptReport: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden: boolean = true;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  //reportData: any[];
  isSuperAdmin: boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;
  customerList: any[];
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
  nodataFoundMessage: boolean = true;
  ChequeGroupId: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  totalAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewadvancedreceiptInfo: ViewData[]=[];

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal,private ren: Renderer2, private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService) {
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
    return this.AddBeforeEnrolReceiptReport.controls;
  }

  get cf() {
    return this.ChequeGroup.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
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
    let cond = {
      "conditions[tenant_id]" : this.AddBeforeEnrolReceiptReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
    }
      this.tenantid=this.AddBeforeEnrolReceiptReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.customerList = response.data;
            } else {
              this.customerList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
        this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.employeeList = response.data;
            } else {
              this.employeeList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.AddBeforeEnrolReceiptReport.get('tenant_id').value,
        }
        this.userservice.getData(cond1, "list-customers", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.customerList = response.data;
            } else {
              this.customerList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
        this.userservice.getData(cond1, "list-employees", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.employeeList = response.data;
            } else {
              this.employeeList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else{

      }
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
    //this.AddAdvancedReceiptReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.AddBeforeEnrolReceiptReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.AddBeforeEnrolReceiptReport.get('branch_id').setValue('');
    this.AddBeforeEnrolReceiptReport.get('customer_id').setValue('');
    this.AddBeforeEnrolReceiptReport.get('employee_id').setValue('');
    this.AddBeforeEnrolReceiptReport.get('payment_type_id').setValue('');
    this.AddBeforeEnrolReceiptReport.get('start_date').setValue(null);
    this.AddBeforeEnrolReceiptReport.get('end_date').setValue(null);
    this.customerList = [];
    this.employeeList = [];
    this.paymentList = [];
  }

  /*open(content, options = {}, recordId=0 ) {
    this.ChequeGroup.get('id').setValue(recordId);
    let cond = {
      "conditions[tenant_id]" : this.AddAdvancedReceiptReport.get('tenant_id').value,
      "conditions[branch_id]" : this.AddAdvancedReceiptReport.get('branch_id').value,
    }
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.modalService.open(content, options).result.then((result) => {
      }, (reason) => {
    });
  }*/

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

  openPopup(record,chequeType) { 
    if(chequeType=='checkReturnClk') {
      record.context.parentComponent.isChequeReturnClk = true;
      record.context.parentComponent.isChequeClearClk = false;
    }
    else {
      record.context.parentComponent.isChequeReturnClk = false;
      record.context.parentComponent.isChequeClearClk = true;
    } 
    //this.ChequeGroup.get('id').setValue(record.id);
    record.context.parentComponent.ChequeGroupId = record.data.id;
    let cond = {
      "conditions[tenant_id]" : record.context.parentComponent.AddBeforeEnrolReceiptReport.get('tenant_id').value,
      "conditions[branch_id]" : record.context.parentComponent.AddBeforeEnrolReceiptReport.get('branch_id').value,
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

  clearChequeData() {
    this.ChequeGroup.get('remarks').setValue('');
    this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
    this.ChequeGroup.get('cheque_return_amount').setValue('');
    this.ChequeGroup.get('cheque_debit_to').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    const result = Object.assign({}, this.AddBeforeEnrolReceiptReport.value);
    if (this.AddBeforeEnrolReceiptReport.invalid) {
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
    this.userservice.getData(result, "list-pigmy-receipts", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.viewadvancedreceiptInfo = response.data[0];
			    this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Receipt Date', field: 'receipt_date',resizable: true},
            {headerName: 'Customer Name', field: 'customer_name',resizable: true},
            {headerName: 'Collection Employee Name', field: 'collection_employee_name',resizable: true },
			      {headerName: 'Receipt Amount', field: 'amount',resizable: true},
            {headerName: 'Payment Type', field: 'payment_type_name' ,resizable: true},
            {headerName: 'Cheque No', field: 'cheque_no',resizable: true},
            {headerName: 'Cheque Date', field: 'cheque_date' ,resizable: true},
            {headerName: 'Cheque Return', field: 'cheque_return_amount' ,resizable: true},
			      {headerName: 'Cheque Clear/ Return Date', field: 'cheque_clear_return_date',resizable: true},
            {headerName: 'Bank Name', field: 'bank_name' ,resizable: true},
            {headerName: 'Bank Branch Name', field: 'bank_branch_name' ,resizable: true},
			      {headerName: 'Transaction No', field: 'transaction_no' ,resizable: true},
            {headerName: 'Transaction Date', field: 'transaction_date' ,resizable: true},
            {headerName: 'Cheque Clearance', field: 'cheque_clearance', 
            cellRendererFramework: ReportRedirectModalComponent,
            cellRendererParams: {
              label: '-',
              action: this.openPopup,
            }
          },
          {headerName: 'Cheque Debit To', field: 'cheque_debit_to',resizable: true },
          {headerName: 'Status', field: 'status_name',resizable: true },
          {headerName: 'Print', 
              cellRenderer: function(params) {  
                let currentUrl = location.href; 
                let splited = currentUrl.split("#");
                let redirectable = '';
                if(splited.length > 0) {
                  redirectable = splited[0];
                }
                //return '<a href=location.origin+"/admin-panel/delete-enrol-refund-amount/' + params.data.id + '"><i class="fas fa-trash-alt text-danger"></i></a>';
                return '<a href="'+redirectable+'#/admin-panel/before-advance-receipt-print/' + params.data.receipt_no + '"><i class="fas fa-print"></i></a>';
              }
              //field: 'action_html' 
            },
        ]; 
        this.defaultColDef = { 
          sortable: true,
          width: 150, 
        };
        applyReportGridData(this, response.data);
        this.totalAmt=response.grand_total;
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
      this.appService.showAlert("PigmyReceipt Report", response.msg, "error");
    }
      } catch (err) {
        this.appService.showAlert("PigmyReceipt Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("PigmyReceipt Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("PigmyReceipt Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }
  
  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'before_enrol_receipt_report',
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
    /*var data = document.getElementById('contentToConvert');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 200;   
      var pageHeight = 175;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 10;  
      pdf.addImage(contentDataURL, 'JPEG', 5, position, imgWidth, imgHeight)  
      pdf.save('Receiptreport.pdf'); // Generated PDF   
    }); */ 
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
      pdf.save('Beforeenrolreceiptreport.pdf'); // Generated PDF   
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
    this.SubmitChequeButtonName = "Loading...";
    this.chequeSubmitted = true;
    this.ChequeGroup.get('id').setValue(this.ChequeGroupId);
    const result = Object.assign({}, this.ChequeGroup.value);
    if (this.ChequeGroup.invalid) {
      this.SubmitChequeButtonName = "Submit";
      this.disableChequeBtn = false;
      return;
    }
    let submitUrl = '';
    if(this.isChequeReturnClk) {
      submitUrl = 'before-enrol-receipt-cheque-return';
    }
    else if(this.isChequeClearClk) {
      submitUrl = 'before-enrol-receipt-cheque-cleared';
    }
    this.userservice.getData(result, submitUrl, "JSON").subscribe((response: any) => {
      this.SubmitChequeButtonName = "Submit";
      try {
        if (response.status == 'Success') {
          let element: HTMLElement = document.getElementById('myDiv') as HTMLElement;
          element.click();
          let chequeId = this.ChequeGroup.get('id').value;
          let selectedrecord =  this.dataList.filter((listing: any) => listing.id == chequeId);
          let returnClearDateObj = this.ChequeGroup.get('cheque_clear_return_date').value;
          let returnClearDate = returnClearDateObj.day + '/' + returnClearDateObj.month + '/' + returnClearDateObj.year;
          if(this.isChequeReturnClk) {
            selectedrecord[0].status = 3;
            selectedrecord[0].cheque_return_amount = this.ChequeGroup.get('cheque_return_amount').value;
            //selectedrecord[0].cheque_clear_return_date = this.ChequeGroup.get('cheque_clear_return_date').value;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
          }
          else if(this.isChequeClearClk) {
            selectedrecord[0].status = 4;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
            let bankId = this.ChequeGroup.get('cheque_debit_to').value;
            let debitToRecord = this.bankList.filter((listing: any) => listing.id == bankId);
            selectedrecord[0].cheque_bank_info.bank_name = debitToRecord[0].bank_name;
          }
          this.ChequeGroup.get('remarks').setValue('');
          this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
          this.ChequeGroup.get('cheque_return_amount').setValue(0);
          this.ChequeGroup.get('cheque_debit_to').setValue(0);
          this.onSubmit();
        } else { 
          this.appService.showAlert("PigmyReceipt Report", response.msg, "error");
        }
      } catch (err) {  
        this.disableChequeBtn = false;
        if (err.status == 401) {
          this.appService.showAlert("PigmyReceipt Report", 'Sorry!! Something went wrong!', "error");
        }
      }
    }, (err: any) => { console.log(err)
      this.disableChequeBtn = false;
      this.SubmitChequeButtonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("PigmyReceipt Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("PigmyReceipt Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }
 
  ngOnInit() {
    this.AddBeforeEnrolReceiptReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: [''],
      employee_id: [''],
      payment_type_id: [''],
      start_date: [null],
      end_date: [null]
    });

    this.ChequeGroup = this.formBuilder.group({
      id: [''],
      remarks: ['', Validators.required],
      cheque_clear_return_date: [null, Validators.required],
      cheque_return_amount: [0,[Validators.required, Validators.pattern(this.decimalPattern)]],
      cheque_debit_to: [0]
    });
   
    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.AddBeforeEnrolReceiptReport.get('tenant_id').setValue(tenant_id);
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


