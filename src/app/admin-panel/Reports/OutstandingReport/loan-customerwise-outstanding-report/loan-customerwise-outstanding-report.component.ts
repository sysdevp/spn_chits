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
  
  selector: 'app-loan-customerwise-outstanding-report',
  templateUrl: './loan-customerwise-outstanding-report.component.html',
  styles: []
})
export class LoanCustomerwiseOutstandingReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  loanCustomerOutstanding: FormGroup;
  dataList: any[];
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  customerList: any[];
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
  nodataFoundMessage: boolean = true;
  totalLoanAmt: any;
  totalPaidAmt: any;
  totalPaidInterest: any;
  totalPendingInterest: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewloancustomerwiseoutstandingInfo: ViewData[]=[];

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.loanCustomerOutstanding.controls;
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
      "conditions[tenant_id]" : tenant_id
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
      "conditions[tenant_id]" : this.loanCustomerOutstanding.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.loanCustomerOutstanding.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.customerList = response.data;
            } else {
              this.customerList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.loanCustomerOutstanding.get('tenant_id').value,
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
  
  resetCustomerwiseReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.loanCustomerOutstanding.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.loanCustomerOutstanding.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.loanCustomerOutstanding.get('branch_id').setValue('');
    this.loanCustomerOutstanding.get('customer_id').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.loanCustomerOutstanding.value);
    if (this.loanCustomerOutstanding.invalid) 
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
    
    this.userservice.getData(result, "loan-customer-outstanding", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewloancustomerwiseoutstandingInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name',resizable: true},
            {headerName: 'Mobile', field: 'mobile_no' ,resizable: true},
            {headerName: 'Loan Amount', field: 'total_loan_amount',resizable: true},
            {headerName: 'Total Paid Amount', field: 'total_paid_loan_amount',resizable: true },
            {headerName: 'Pending Loan Amount', field: 'pending_loan_amount',resizable: true },
            {headerName: 'Paid Interest Amount', field: 'total_paid_interest' ,resizable: true},
		      	{headerName: 'Pending Interest Amount', field: 'pending_interest',resizable: true},
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.totalLoanAmt=response.grand_total_loan_amt;
          this.totalPaidAmt=response.grand_total_paid_amt;
          this.totalPaidInterest=response.grand_total_paid_interest_amt;
          this.totalPendingInterest=response.grand_total_pending_amt;
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
          //this.hideReport = false;
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function(params) {
            return "[" + params.value.toLocaleString() + "]";
          };
          /*this.setData(response.data);
          console.log(response.data) 
          setTimeout(() => {
            this.TableHidden = false;
          },0);*/
        } else {
          this.nodataFoundMessage = false;
          this.appService.showAlert("Loan Customerwise Outstanding Report", response.msg, "error");
        }
      }
        else{
          this.hideReport = true;
          this.nodataFoundMessage=false;
        }
      } catch (err) {console.log(err)
        this.appService.showAlert("Loan Customerwise Outstanding Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {console.log(err)
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Loan Customerwise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Loan Customerwise Outstanding Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'loan_customerwise_outstanding_report',
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
      pdf.save('Loancustomerwiseoutstandingreport.pdf'); // Generated PDF   
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
    this.loanCustomerOutstanding = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id:[''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.loanCustomerOutstanding.get('tenant_id').setValue(tenant_id);
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
