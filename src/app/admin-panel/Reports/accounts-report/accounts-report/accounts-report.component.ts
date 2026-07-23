import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-accounts-report',
  templateUrl: './accounts-report.component.html',
  styles: []
})
export class AccountsReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  AccountsReport: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  ReceiptDetails: any;
  
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
  updaterowdata: any;
  updateRowData: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewaccountInfo: ViewData[]=[];
  feature = 'Feature 1'

  constructor( private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.AccountsReport.controls;
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
    //this.AccountsReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.AccountsReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.AccountsReport.get('branch_id').setValue('');
    this.AccountsReport.get('start_date').setValue(null);
    this.AccountsReport.get('end_date').setValue(null);
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.AccountsReport.value);
    if (this.AccountsReport.invalid) 
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
    this.userservice.getData(result, "accounts-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.viewaccountInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
			      {headerName: 'Date', field: 'receipt_date',resizable: true },
            {headerName: 'Branch Name', field: 'branch_name' ,resizable: true},
		      	{headerName: 'Cash Collection', field: 'cash_collection',resizable: true},
		      	{headerName: 'Cash Penalty', field: 'cash_penalty',resizable: true},
            {headerName: 'Cheque Received', field: 'cheque_collection',resizable: true },
            {headerName: 'Cheque  Penalty', field: 'cheque_penalty',resizable: true },
		      	{headerName: 'Cheque Cleared', field: 'cheque_cleared_collection',resizable: true},
            {headerName: 'Cheque Cleared Penalty', field: 'cheque_cleared_penalty',resizable: true },
            {headerName: 'DD Collection', field: 'dd_collection',resizable: true},
            {headerName: 'DD Penalty', field: 'dd_penalty',resizable: true },
            {headerName: 'NEFT Collection', field: 'neft_collection' ,resizable: true},
			      {headerName: 'Neft Penalty', field: 'neft_penalty',resizable: true },
			      {headerName: 'Advance Adjust Amount', field: 'adv_adjust_collection',resizable: true },
            {headerName: 'Advance Adjust Penalty', field: 'adv_adjust_penalty',resizable: true },
            {headerName: 'BP Adjust Amount', field: 'bp_adjust_collection',resizable: true },
            {headerName: 'BP Adjust Penalty', field: 'bp_adjust_penalty',resizable: true },
            {headerName: 'Received Advance Cash', field: 'advance_cash_collection',resizable: true },
            {headerName: 'Received Advance Cheque', field: 'advance_cheque_collection',resizable: true },
            {headerName: 'Received Advance DD', field: 'advance_dd_collection',resizable: true },
            {headerName: 'Received Advance NEFT', field: 'advance_neft_collection',resizable: true },
            {headerName: 'Received Advance Card', field: 'advance_card_collection',resizable: true },
            {headerName: 'Cash Payment', field: 'payment_cash',resizable: true },
            {headerName: 'Card Payment', field: 'payment_card',resizable: true },
            {headerName: 'Cheque Payment', field: 'payment_cheque',resizable: true },
            {headerName: 'DD Payment', field: 'payment_dd',resizable: true },
            {headerName: 'Neft Payment', field: 'payment_neft',resizable: true },
           /*  {headerName: 'Total', field: 'payment_neft',resizable: true }, */
      ];
     
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
         
      applyReportGridData(this, response.data);
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
          this.appService.showAlert("Accounts Report", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Accounts Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Accounts Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Accounts Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'accounts_report',
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
      pdf.save('Accountsreport.pdf'); // Generated PDF   
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
    this.AccountsReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      start_date:[null],
      end_date:[null]
    });
   
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AccountsReport.get('tenant_id').setValue(tenant_id);
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
