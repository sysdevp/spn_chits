import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-account-expenses-report',
  templateUrl: './account-expenses-report.component.html',
  styles: []
})
export class AccountExpensesReportComponent implements OnInit {
  [x: string]: any;
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('start_date') focus3:ElementRef;
  @ViewChild('end_date') focus4:ElementRef;

  AccountexpensesReport: FormGroup;
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
  viewaccountExpensesInfo: ViewData[]=[];
  total_debit_amounts: any;
  total_credit_amounts: any;
  stdate: any;
  endate: any;
  day: any;
  month: any;
  year: any;
  endday: any;
  endmonth: any;
  endyear: any;
  branch_name_head: any;
  debit_name: any;
  credit_name: any;
  chit_fund_name: any;
  opening_balance: any;
  credit_debit: any;
  openingbalancefinal: any;
  total_balance: any;
  total_credit_with_openingbal: any;
  closing_balance: number;
  total_debit_with_openingbal: any;
  closing_deb_cre: any;
  total_openingbal: any;

  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.AccountexpensesReport.controls;
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
      "conditions[tenant_id]" : this.AccountexpensesReport.get('tenant_id').value,
     
    }
      this.tenantid=this.AccountexpensesReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
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
        this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
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
    //this.EnrollReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.AccountexpensesReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.AccountexpensesReport.get('branch_id').setValue('');
    this.AccountexpensesReport.get('account_head').setValue('');
    this.AccountexpensesReport.get('start_date').setValue(null);
    this.AccountexpensesReport.get('end_date').setValue(null);
    this.groupList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.AccountexpensesReport.value);
    if (this.AccountexpensesReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.start_date.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.end_date.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    } 
    
    this.userservice.getData(result, "list-account-expense", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          console.log(response)
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewaccountExpensesInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Branch Name', field: 'branch_name',resizable: true  },
            {headerName: 'Voucher No', field: 'voucher_no',resizable: true },
            {headerName: 'Date', field: 'entry_date',resizable: true },
            {headerName: 'Remarks', field: 'remarks',resizable: true },
            {headerName: 'Particulars Debit', field: 'name',resizable: true },
            {headerName: 'Debit', field: 'debit_amount',resizable: true  },
            {headerName: 'Particulars Credit', field: 'credit_name',resizable: true },
            {headerName: 'Credit', field: 'credit_amount',resizable: true },
            {headerName: 'Credit Remarks', field: 'credit_remarks',resizable: true },
            {headerName: 'Debit Remarks', field: 'debit_remarks',resizable: true },
            {headerName: 'Full-View', 
              cellRenderer: function(params) {  
                let currentUrl = location.href; 
                let splited = currentUrl.split("#");
                let redirectable = '';
                if(splited.length > 0) {
                  redirectable = splited[0];
                }
                //return '<a href=location.origin+"/admin-panel/delete-enrol-refund-amount/' + params.data.id + '"><i class="fas fa-trash-alt text-danger"></i></a>';
                return '<a href="'+redirectable+'#/admin-panel/debit-credit-view/' + params.data.voucher_no + '">Full-View</a>';
              }
              //field: 'action_html' 
            },
            {headerName: 'Delete', 
              cellRenderer: function(params) {  
                let currentUrl = location.href; 
                let splited = currentUrl.split("#");
                let redirectable = '';
                if(splited.length > 0) {
                  redirectable = splited[0];
                }
                //return '<a href=location.origin+"/admin-panel/delete-enrol-refund-amount/' + params.data.id + '"><i class="fas fa-trash-alt text-danger"></i></a>';
                return '<a href="'+redirectable+'#/admin-panel/debit-credit-edit/' + params.data.voucher_no + '">Delete</a>';
              }
              //field: 'action_html' 
            },
            /*{headerName: 'Actions', 
              cellRenderer: function(params) {  
                let currentUrl = location.href; 
                let splited = currentUrl.split("#");
                let redirectable = '';
                if(splited.length > 0) {
                  redirectable = splited[0];
                }
                //return '<a href=location.origin+"/admin-panel/delete-enrol-refund-amount/' + params.data.id + '"><i class="fas fa-trash-alt text-danger"></i></a>';
                return '<a href="'+redirectable+'#/admin-panel/print-expenses-income/' + params.data.voucher_no + '"><i class="fas fa-print"></i></a>';
              }
              //field: 'action_html' 
            },*/
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.chit_fund_name=response.data[0].chit_fund_name;
          this.branch_name_head=response.data[0].branch_name;
          this.debit_name=response.data[0].name;
          this.credit_name=response.data[0].credit_name;
          this.total_credit_amounts=response.total_credit_amounts;
          this.total_debit_amounts=response.total_debit_amounts;
          this.head_name=response.head_name;
          this.opening_balance=response.opening_balance;
          this.credit_debit=response.credit_debit;
          if(this.credit_debit == 'Cr' ){
            this.total_credit_with_openingbal = this.opening_balance + this.total_credit_amounts;
            if(this.total_credit_with_openingbal > this.total_debit_amounts){
              this.closing_balance =  this.total_credit_with_openingbal - this.total_debit_amounts;
              this.closing_deb_cre = 'Cr';
            }
            else{
              this.closing_balance = this.total_debit_amounts - this.total_credit_with_openingbal;
              this.closing_deb_cre = 'Dr';
            }
            
          }
          else if(this.credit_debit == 'Dr' ){
            this.total_debit_with_openingbal = this.opening_balance + this.total_debit_amounts;
            if(this.total_debit_with_openingbal > this.total_credit_amounts){
              this.closing_balance =  this.total_debit_with_openingbal - this.total_credit_amounts;
              this.closing_deb_cre = 'Dr';
            }
            else{
              this.closing_balance = this.total_credit_amounts - this.total_debit_with_openingbal;
              this.closing_deb_cre = 'Cr';
            }
          }
          else{
           
            if(this.total_credit_amounts > this.total_debit_amounts){
              this.closing_balance = this.total_credit_amounts;
              this.closing_deb_cre = 'Cr';
            }
            else{
              this.closing_balance = this.total_debit_amounts;
              this.closing_deb_cre = 'Dr';
            }
          }
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
          this.appService.showAlert("Account Expenses Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) {
        this.appService.showAlert("Account Expenses Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Account Expenses Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Account Expenses Report", "Sorry!! Something went wrong!", "error");
      }
    });

    this.stdate=this.AccountexpensesReport.get('start_date').value;
  console.log(this.stdate);
  this.day = this.stdate.day;
  this.month = this.stdate.month;
  this.year = this.stdate.year;
  
  this.endate=this.AccountexpensesReport.get('end_date').value;
  this.endday = this.endate.day;
  this.endmonth = this.endate.month;
  this.endyear = this.endate.year;
  }
  
  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'account_expenses_report',
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
      var imgWidth = 180;   
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
      pdf.save('AccountExpensesreport.pdf'); // Generated PDF   
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
    this.AccountexpensesReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      account_head: [''],
      start_date: [null, Validators.required],
      end_date: [null, Validators.required]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AccountexpensesReport.get('tenant_id').setValue(tenant_id);
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
