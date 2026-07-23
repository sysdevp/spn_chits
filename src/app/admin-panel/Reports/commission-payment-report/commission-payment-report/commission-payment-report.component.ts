import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
import {ReportPopupModalComponent} from '../../../../modal/report-popup-modal/report-popup-modal.component';
import {ReportRedirectModalComponent} from '../../../../modal/report-redirect-modal/report-redirect-modal.component';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-commission-payment-report',
  templateUrl: './commission-payment-report.component.html',
  styles: [],
})

export class CommissionPaymentReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  commissionPayment: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;
  agentList: any[];
  branchInfo: any[];
  customerInfo: any[];
  groupInfo: any[];
  agentInfo: any[];
  ViewCommissionPaymentInfo: any;
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
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  ChequeGroupId: any;
  bankList: any;
  totalPendingAmt: any;
  totalPaidAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewcommissionpaymentInfo: ViewData[]=[];
  //frameworkComponents: any;

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal,private renderer: Renderer2, private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService) {
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
    return this.commissionPayment.controls;
  }

  get cf() {
    return this.ChequeGroup.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    let cond = {
      "conditions[tenant_id]" : tenant_id
    }
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
      "conditions[tenant_id]" : this.commissionPayment.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
      "conditions[employee_type]" : 2
    }
      this.tenantid=this.commissionPayment.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0 )
      {
        this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.agentList = response.data;
            } else {
              this.agentList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.commissionPayment.get('tenant_id').value,
          'employee_type':2
        }
       
        this.userservice.getData(cond1, "list-employees", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.agentList = response.data;
            } else {
              this.agentList = [];
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
    $(function() {
      $('#dtable').DataTable();
    })
  }

  resetCommitmentPaymentReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.commissionPayment.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.commissionPayment.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.commissionPayment.get('branch_id').setValue('');
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
    const result = Object.assign({}, this.commissionPayment.value);
    if (this.commissionPayment.invalid) 
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
    
    this.userservice.getData(result, "get-commision-payment-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewcommissionpaymentInfo = response.data[0];
			    this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Agent Name', field: 'agent_name',resizable: true},
            {headerName: 'Payment Date', field: 'payment_date',resizable: true },
            {headerName: 'Commission Number', field: 'commission_no',resizable: true },
            {headerName: 'Total Amount', field: 'paid_amount',resizable: true},
            {headerName: 'Payment Type', field: 'payment_type_name',resizable: true},
            {headerName: 'Debit To', field: 'debit_to',resizable: true },
            {headerName: 'Cheque No', field: 'cheque_no',resizable: true },
            {headerName: 'Cheque Date', field: 'cheque_date',resizable: true},
            {headerName: 'Cheque Return Amount', field: 'cheque_return_amount' ,resizable: true},
            {headerName: 'Cheque Clear/ Return Date', field: 'cheque_clear_return_date',resizable: true},
            {headerName: 'Bank Name', field: 'bank_name',resizable: true },
            {headerName: 'Bank Branch Name', field: 'bank_branch_name',resizable: true},
            {headerName: 'Transaction No', field: 'transaction_no',resizable: true},
            {headerName: 'Transaction Date', field: 'transaction_date' ,resizable: true},
            {headerName: 'Cheque Clearance', field: 'cheque_clearance',cellRendererFramework: ReportRedirectModalComponent,
            cellRendererParams: {
              label: '-',
              action: this.openPopupclr,
            }
          },
          {headerName: 'Cheque Debit To', field: 'cheque_debit_to',resizable: true },
          {headerName: 'Status', field: 'status_name',resizable: true },
          {headerName: 'Full View', 
              field: 'full_view', 
              cellRendererFramework: ReportPopupModalComponent,
              cellRendererParams: {
                label: 'View',
                action: this.openPopup,
				      }
            },
          ];
      this.defaultColDef = { 
        sortable: true,
        width: 150, 
      };
      applyReportGridData(this, response.data);
      this.totalPaidAmt=response.grand_paid_amt;
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
      this.nodataFoundMessage=false;
      this.appService.showAlert("Commission Payment Report", response.msg, "error");
    }
  }
  else{
    this.hideReport = true;
    this.nodataFoundMessage=false;
  }
  } catch (err) { console.log(err);
    this.appService.showAlert("Commission Payment Report", 'Sorry!! Something went wrong!', "error");
  }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commission Payment Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commission Payment Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  openPopup(record) {
    let cond = {
      "commission_no" : record.data.commission_no
    }
    record.context.userservice.getData(cond, "list-commision-payment-report", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewCommissionPaymentInfo = response.data;
          let content = '<div class="card-body"><div class="row" style="overflow-x:scroll "><table id="dtable" class="row-border hover das-auc-t c_a_t" [hidden]="TableHidden"><thead><tr> ';
          content += '<th>SNo</th><th>Branch Name</th><th>Agent Name</th><th>Customer Name</th><th>Group Name</th><th>Payment Date</th><th>Ticket No</th><th>Commission Number</th><th>Total Amount</th><th>Payment Type</th><th>Debit To</th><th>Cheque No</th><th>Cheque Date</th><th>Bank Name</th><th>Branch Name</th><th>Transaction No</th><th>Transaction Date</th></tr></thead>';
          content += '<tbody>'; 
          if((this.ViewCommissionPaymentInfo.length)>0) {
            for(let i=0; i<(this.ViewCommissionPaymentInfo.length);i++) {
              let paymentInfo = this.ViewCommissionPaymentInfo[i];
              content += '<tr>';
              content += '<td>'+paymentInfo.sno+'</td>';
              content += '<td>'+paymentInfo.branch_name+'</td>';
              content += '<td>'+paymentInfo.agent_name+'</td>';
              content += '<td>'+paymentInfo.customer_name+'</td>';
              content += '<td>'+paymentInfo.group_name+'</td>';
              content += '<td>'+paymentInfo.payment_date+'</td>';
              content += '<td>'+paymentInfo.ticket_no+'</td>';
              content += '<td>'+paymentInfo.commission_no+'</td>';
              content += '<td>'+paymentInfo.customer_commission_amount+'</td>';
              content += '<td>'+paymentInfo.payment_type_name+'</td>';
              content += '<td>'+paymentInfo.debit_to+'</td>';
              content += '<td>'+paymentInfo.cheque_no+'</td>';
              content += '<td>'+paymentInfo.cheque_date+'</td>';
              content += '<td>'+paymentInfo.bank_name_id+'</td>';
              content += '<td>'+paymentInfo.branch_name+'</td>';
              content += '<td>'+paymentInfo.transaction_no+'</td>';
              content += '<td>'+paymentInfo.transaction_date+'</td>';
              content += '</tr>';
            }
            content += '</tbody>';
            content += '</table>';
            content += '</table></div></div><div class="modal-footer"></div>';
          }
          else {
            content += '<tr><td colspan="17">No data found</td></tr>';
          }
          (<HTMLInputElement>document.getElementById("modal-body")).innerHTML = content;
          this.TableHidden = false; 
        }
        else{
          this.ViewCommissionPaymentInfo = [];
          this.branchInfo = [];
          this.customerInfo = [];
          this.groupInfo = [];
          this.agentInfo = [];
        }
      } catch (err) { console.log(err)
        record.context.appService.showAlert("Commission Payment", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {  console.log(err)
      if (err.status == 401) {
        record.context.appService.showAlert("Commission Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        record.context.appService.showAlert("Commission Payment", "Sorry!! Something went wrong!", "error");
      }
    });
    record.context.ngxSmartModalService.getModal('myModal').open()
  }

  openPopupclr(record,chequeType) { 
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
      "conditions[tenant_id]" : record.context.parentComponent.commissionPayment.get('tenant_id').value,
      "conditions[branch_id]" : record.context.parentComponent.commissionPayment.get('branch_id').value,
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
    record.context.ngxSmartModalService.getModal('myModalclr').open()
  }
  
  setFullView(params) {
		return '<a class="bg-success border-0 text-white px-3 py-2">View</a>';
  }
  
  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'commission_payment_report',
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
      pdf.save('Commissionpaymentreport.pdf'); // Generated PDF   
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
      submitUrl = 'cheque-return-commision-payment';
    }
    else if(this.isChequeClearClk) {
      submitUrl = 'cheque-cleared-commision-payment';
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
            selectedrecord[0].bank_name = debitToRecord[0].bank_name;
          }
          this.ChequeGroup.get('remarks').setValue('');
          this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
          this.ChequeGroup.get('cheque_return_amount').setValue(0);
          this.ChequeGroup.get('cheque_debit_to').setValue(0);
          this.onSubmit();
        } else { 
          this.appService.showAlert("Commission Payment", response.msg, "error");
        }
      } catch (err) {
        this.disableChequeBtn = false;
        if (err.status == 401) {
          this.appService.showAlert("Commission Payment", 'Sorry!! Something went wrong!', "error");
        }
      }
    }, (err: any) => {
      this.disableChequeBtn = false;
      this.SubmitChequeButtonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commission Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commission Payment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.commissionPayment = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_agent_id:[''],
      start_date:[null],
      end_date:[null]
    });
    this.ChequeGroup = this.formBuilder.group({
      id: [''],
      remarks: ['', Validators.required],
      cheque_clear_return_date: [null, Validators.required],
      cheque_return_amount: [0 ,[Validators.required, Validators.pattern(this.decimalPattern)]],
      cheque_debit_to: [0]
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.commissionPayment.get('tenant_id').setValue(tenant_id);
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
