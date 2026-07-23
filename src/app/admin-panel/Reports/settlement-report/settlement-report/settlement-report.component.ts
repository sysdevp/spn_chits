import { Component, OnInit, ElementRef, ViewChild, Renderer2 } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
//import {ReportPopupModalComponent} from '../../../../modal/report-popup-modal/report-popup-modal.component';
import {ReportRedirectModalComponent} from '../../../../modal/report-redirect-modal/report-redirect-modal.component';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-settlement-report',
  templateUrl: './settlement-report.component.html',
  styles: []
})
export class SettlementReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;

  SettlementReport: FormGroup;
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
    return this.SettlementReport.controls;
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
      "conditions[tenant_id]" : this.SettlementReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
    }
      this.tenantid=this.SettlementReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        
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
          'tenant_id' : this.SettlementReport.get('tenant_id').value,
        }
        
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
      this.SettlementReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.SettlementReport.get('branch_id').setValue('');
    this.SettlementReport.get('employee_id').setValue('');
    this.SettlementReport.get('start_date').setValue(null);
    this.SettlementReport.get('end_date').setValue(null);
    this.employeeList = [];
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
    const result = Object.assign({}, this.SettlementReport.value);
    if (this.SettlementReport.invalid) {
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
    this.userservice.getData(result, "cash-settlement-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.viewadvancedreceiptInfo = response.data[0];
			    this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Employee Name', field: 'employee_name' ,resizable: true},
            {headerName: 'Settlement Date', field: 'settlement_date',resizable: true},
            {headerName: 'Received Amount', field: 'received_amount',resizable: true},
            {headerName: 'Remarks', field: 'remarks',resizable: true },
           
        ]; 
        this.defaultColDef = { 
          sortable: true,
          width: 150, 
        };
        applyReportGridData(this, response.data);
        this.totalAmt=response.grand_pending_amt;
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
      this.appService.showAlert("Settlement Report", response.msg, "error");
    }
      } catch (err) {
        this.appService.showAlert("Settlement Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Settlement Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Settlement Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'settlement_report',
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
      pdf.save('Settlementreport.pdf'); // Generated PDF   
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

  
 
  ngOnInit() {
    this.SettlementReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: [''],
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
      this.SettlementReport.get('tenant_id').setValue(tenant_id);
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

