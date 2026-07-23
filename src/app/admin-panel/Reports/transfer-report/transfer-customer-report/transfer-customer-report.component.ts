import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-transfer-customer-report',
  templateUrl: './transfer-customer-report.component.html',
  styles: []
})
export class TransferCustomerReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('customer_id') focus3: ElementRef;
  @ViewChild('e1') e1: ElementRef;
  @ViewChild('e1table') e1table: ElementRef;

  TransferReport: FormGroup;
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
  custId: any;
  branchId: any;

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
    return this.TransferReport.controls;
  }

  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.TransferReport.get('branch_id').setValue('');
    this.branchList = [];
    this.TransferReport.get('customer_id').setValue('');
    this.customerList = [];
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
    this.branchId = event.target.value;
    this.TransferReport.get('customer_id').setValue('');
    this.customerList = [];
    let cond = {
      "conditions[branch_id]": event.target.value
    }
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
  }

  customerChg(event) {
    this.custId = event.target.value;
   
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
    //this.TransferReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.TransferReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.TransferReport.get('branch_id').setValue('');
    this.TransferReport.get('customer_id').setValue('');
    this.customerList = [];
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
    const result = Object.assign({}, this.TransferReport.value);
    if (this.TransferReport.invalid) {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    let cond = {
      //"conditions[branch_id]" : this.CustomerLedger.get('branch_id'),
      "conditions[branch_id]" : this.branchId,
      "conditions[customer_id]" :this.custId,
    }
    console.log(cond);
    this.userservice.getData(cond, "get-pledge-customer", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewreceiptInfo = response.data[0];

          console.log(this.viewreceiptInfo);
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true },
            {headerName: 'Customer Name', field: 'name',resizable: true },
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true },
            {headerName: 'Pledge Group Name', field: 'pledge_group_name',resizable: true },
            {headerName: 'Pledge Customer Name', field: 'pledge_name',resizable: true },
            {headerName: 'Pledge Ticket No', field: 'pledge_ticket_no',resizable: true },
            {headerName: 'Pledge Chit Value', field: 'pledge_chit_value',resizable: true },
            {headerName: 'Pledge date', field: 'pledge_date',resizable: true },
            {headerName: 'Closed date', field: 'unpledge_date',resizable: true },
            {headerName: 'Status', field: 'status',resizable: true },
            {headerName: 'Actions', 
              cellRenderer: function(params) {  
                let currentUrl = location.href; 
                let splited = currentUrl.split("#");
                let redirectable = '';
                if(splited.length > 0) {
                  redirectable = splited[0];
                }
                //return '<a href=location.origin+"/admin-panel/delete-enrol-refund-amount/' + params.data.id + '"><i class="fas fa-trash-alt text-danger"></i></a>';
                return '<a href="'+redirectable+'#/admin-panel/update-pledge-customer/' + params.data.id + '"><i class="fas fa-edit text-primary"></i></a>';
              }
              //field: 'action_html' 
            }
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
          this.appService.showAlert("Transfer Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      } catch (err) {
        this.appService.showAlert("Transfer Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Transfer Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Transfer Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'pledge_report',
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
      pdf.save('Pledgereport.pdf'); // Generated PDF   
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
    this.TransferReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.TransferReport.get('tenant_id').setValue(tenant_id);
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


