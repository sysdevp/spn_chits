import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
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
  
  selector: 'app-current-month-collection',
  templateUrl: './current-month-collection.component.html',
  styles: []
})
export class CurrentMonthCollectionComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('group_id') focus3: ElementRef;
  @ViewChild('e1') e1: ElementRef;
  @ViewChild('e1table') e1table: ElementRef;

  CurrentMonthCollection: FormGroup;
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
  disableBtn: boolean;
  customerList: any[];
  enrollmentList: any[];
  employeeList: any[];
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
  totalBonusAmt: any;
  totalPenaltyAmt: any;
  totalReceiptAmt: any;
  totalReceivedAmt: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewreceiptInfo: ViewData[]=[];
  branchid: any;
  tenantid: any;

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
        //AddAdvancedCurrentMonthCollection: this.AddAdvancedCurrentMonthCollection
      },
      components: {
      },
    };
  }

  get f() {
    return this.CurrentMonthCollection.controls;
  }

  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.CurrentMonthCollection.get('branch_id').setValue('');
    this.branchList = [];
    this.CurrentMonthCollection.get('group_id').setValue('');
    this.groupList = [];
    this.CurrentMonthCollection.get('customer_id').setValue('');
    this.customerList = [];
    this.CurrentMonthCollection.get('employee_id').setValue('');
    this.employeeList = [];
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

    
  }

  branchChg(event) {
    this.CurrentMonthCollection.get('group_id').setValue('');
    this.groupList = [];
    this.CurrentMonthCollection.get('customer_id').setValue('');
    this.customerList = [];
    this.CurrentMonthCollection.get('employee_id').setValue('');
    this.employeeList = [];
    let cond = {
      "conditions[branch_id]": event.target.value
    }
    this.tenantid=this.CurrentMonthCollection.get('tenant_id').value,
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
        } catch (err) { }
      }, (err: any) => { });
      this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.customerList = response.data;
            this.enrollmentList = response.data;
          } else {
            this.customerList = [];
          }
        } catch (err) { }
      }, (err: any) => { });
      this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.employeeList = response.data;
          } else {
            this.employeeList = [];
          }
        } catch (err) { }
      }, (err: any) => { });
    }
    else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.CurrentMonthCollection.get('tenant_id').value,
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

  grpChg(event) {
    this.CurrentMonthCollection.get('customer_id').setValue('');
    this.customerList = this.enrollmentList;
    let cond = {
      "conditions[branch_id]": this.CurrentMonthCollection.get('branch_id').value,
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
    //this.CurrentMonthCollection.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.CurrentMonthCollection.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.CurrentMonthCollection.get('branch_id').setValue('');
    this.CurrentMonthCollection.get('group_id').setValue('');
    this.CurrentMonthCollection.get('customer_id').setValue('');
    this.CurrentMonthCollection.get('employee_id').setValue('');
    this.groupList = [];
    this.customerList = [];
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
    const result = Object.assign({}, this.CurrentMonthCollection.value);
    if (this.CurrentMonthCollection.invalid) {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.group_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "current-month-collection", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewreceiptInfo = response.data[0];
          console.log(response.data[0]);
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Agent/Employee', field: 'employee_name',resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true },
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true },
            {headerName: 'Customer Code', field: 'customer_code',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name',resizable: true },
            {headerName: 'Customer Address', field: 'customer_address',resizable: true },
            {headerName: 'Mobile No', field: 'customer_mobile',resizable: true },
            {headerName: 'Payable Due', field: 'overall_installment_amount',resizable: true },
            {headerName: 'Paid Due', field: 'overall_paid_amount',resizable: true },
            {headerName: 'Pending Due', field: 'overall_pending_amount',resizable: true },
            {headerName: 'Bonus Due', field: 'bonus',resizable: true },
            {headerName: 'Penalty Due', field: 'penalty',resizable: true },
            {headerName: 'Current Due', field: 'current_due',resizable: true },
            {headerName: 'To Be Collect', field: 'to_be_collected',resizable: true },
            {headerName: 'Pending Dues', field: 'pending_inst' ,resizable: true},
           
            
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.totalBonusAmt=response.grand_tot_bonus_amount;
          this.totalPenaltyAmt=response.grand_tot_penalty_amount;
          this.totalReceiptAmt=response.grand_tot_receipt_amt;
          this.totalReceivedAmt=response.grand_tot_received_amt;
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
          this.appService.showAlert("Current Month Collection", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      } catch (err) {
        this.appService.showAlert("Current Month Collection", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Current Month Collection", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Current Month Collection", "Sorry!! Something went wrong!", "error");
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
      fileName: 'current_month_collection',
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
      pdf.save('Currentmonthcollection.pdf'); // Generated PDF   
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
    this.CurrentMonthCollection = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
      customer_id: [''],
      employee_id: ['']
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.CurrentMonthCollection.get('tenant_id').setValue(tenant_id);
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




