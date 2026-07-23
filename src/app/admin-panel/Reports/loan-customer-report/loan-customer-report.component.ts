import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
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
  
  selector: 'app-loan-customer-report',
  templateUrl: './loan-customer-report.component.html',
  styles: []
})
export class LoanCustomerReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  //@ViewChild('customer_id') focus3: ElementRef;
  @ViewChild('loan_id') focus4: ElementRef;

  LoanCustomerReport: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden: boolean = true;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin: boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;
  customerList: any[];
  enrollmentList: any[];
  loanList: any[];
  paymentList: any[];
  bankList: any[];
  ScrollCls: string = "";
  Scrollwidth: number;
  ScrollOpacity: number=0;
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
  loanAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewloancustomerInfo: ViewData[]=[];

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
    return this.LoanCustomerReport.controls;
  }

  get cf() {
    return this.ChequeGroup.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.LoanCustomerReport.get('branch_id').setValue('');
    this.branchList = [];
    //this.LoanCustomerReport.get('customer_id').setValue('');
    //this.customerList = [];
    this.LoanCustomerReport.get('loan_id').setValue('');
    this.loanList = [];
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id
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
    //this.LoanCustomerReport.get('customer_id').setValue('');
    //this.customerList = [];
    this.LoanCustomerReport.get('loan_id').setValue('');
    this.loanList = [];
    let cond = {
      "conditions[branch_id]": event.target.value
    }
    this.tenantid=this.LoanCustomerReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
          try {
            if (response.status == 'Success') {
              this.customerList = response.data;
              this.enrollmentList = response.data;
            } else {
              this.customerList = [];
            }
          } catch (err) { }
        }, (err: any) => { });
        this.userservice.getData(cond, "get-loan-detail", "POST").subscribe((response: any) => {
          try {
            if (response.status == 'Success') {
              this.loanList = response.data;
            } else {
              this.loanList = [];
            }
          } catch (err) { }
        }, (err: any) => { });
   }
   else if(this.tenantid!='' && this.branchid == 0)
      {
        let cond1 = {
          'tenant_id' : this.LoanCustomerReport.get('tenant_id').value,
        }
        this.userservice.getData(cond1, "list-customers", "POST").subscribe((response: any) => {
          try {
            if (response.status == 'Success') {
              this.customerList = response.data;
              this.enrollmentList = response.data;
            } else {
              this.customerList = [];
            }
          } catch (err) { }
        }, (err: any) => { });
   }
   else{

   }
  }

 /* customerChg(event) {
    this.LoanCustomerReport.get('loan_amount').setValue('');
    this.loanList = [];
    let cond = {
      "conditions[customer_id]": event.target.value
    }
    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerList = response.data;
          this.enrollmentList = response.data;
        } else {
          this.customerList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-loan-detail", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.loanList = response.data;
        } else {
          this.loanList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }*/

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
      this.LoanCustomerReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.LoanCustomerReport.get('branch_id').setValue('');
    //this.LoanCustomerReport.get('customer_id').setValue('');
    this.LoanCustomerReport.get('loan_id').setValue('');
    this.customerList = [];
    this.loanList = [];
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

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    const result = Object.assign({}, this.LoanCustomerReport.value);
    if (this.LoanCustomerReport.invalid) {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      //else if (this.f.customer_id.errors) {
      //  this.focus3.nativeElement.focus();
      //}
      else if (this.f.loan_id.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "customer-loan-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewloancustomerInfo = response.data[0];
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name' ,resizable: true},
            {headerName: 'Address', field: 'customer_address' ,resizable: true},
            {headerName: 'Mobile', field: 'mobile',resizable: true},
            {headerName: 'EMI Months', field: 'emi_months',resizable: true},
            {headerName: 'Loan Amount', field: 'loan_amount',resizable: true},
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.loanAmt=response.grand_total_loan_amt;
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
          this.appService.showAlert("Loan Customer Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      } catch (err) {
        this.appService.showAlert("Loan Customer Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Loan Customer Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Loan Customer Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'loan_customer_report',
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
      pdf.save('Loancustomerreport.pdf'); // Generated PDF   
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
    this.LoanCustomerReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      //customer_id: [''],
      loan_id: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.LoanCustomerReport.get('tenant_id').setValue(tenant_id);
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
