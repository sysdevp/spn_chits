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
  
  selector: 'app-starting-group-report',
  templateUrl: './starting-group-report.component.html',
  styles: []
})
export class StartingGroupReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  TerminationReport: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;

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
  isChequeReturnClk: boolean = false;
  isChequeClearClk: boolean = false;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  totalAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewpaymentrefundInfo: ViewData[]=[];
  viewTerminationInfo: any;

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal,private renderer: Renderer2, private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
    this.gridOptions = <GridOptions>{
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this,
        ngxSmartModalService: this.ngxSmartModalService,
        //AddAdvancedReceiptReport: this.AddAdvancedReceiptReport
      },
      components: {
      },
    };
  }

  get f() {
    return this.TerminationReport.controls;
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

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }


  resetReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.PaymentRefundReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.TerminationReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.TerminationReport.get('branch_id').setValue('');
    this.TerminationReport.get('start_date').setValue(null);
    this.TerminationReport.get('end_date').setValue(null);
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.TerminationReport.value);
    if (this.TerminationReport.invalid) 
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
    this.userservice.getData(result, "group-starting-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewTerminationInfo = response.data[0];
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Branch Name', field: 'branch_name' ,resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true},
            {headerName: 'Chit Value', field: 'chit_value' ,resizable: true},
            {headerName: 'Starting Date', field: 'starting_date' ,resizable: true},
            {headerName: 'Termination Date', field: 'termination_date' ,resizable: true},
            {headerName: 'Commission Percentage', field: 'commission_percent' ,resizable: true},
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          /*for(let i=0; i<(response.data.length); i++) {
            response.data[i]['action_html'] = '<a [routerLink]="[/admin-panel/delete-enrol-refund-amount/+row.id]"><i class="fas fa-trash-alt text-danger"></i></a>';
          }*/
      applyReportGridData(this, response.data);
      this.totalAmt=response.grand_tot_amount;
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
        }
         else {
          this.nodataFoundMessage=true;
          this.appService.showAlert("Termination Report", response.msg, "error");
        }
      }
        else{
          this.hideReport = true;
          this.nodataFoundMessage=false;
        }
      } catch (err) { 
        this.appService.showAlert("Termination Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Termination Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Termination Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'group_termination_report',
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
      pdf.save('GroupTerminationreport.pdf'); // Generated PDF   
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
    this.TerminationReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      start_date:[null],
      end_date:[null]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.TerminationReport.get('tenant_id').setValue(tenant_id);
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
    //console.log(location);

//console.log(location.href);
//console.log(location.origin);
  } 
}
export interface ViewData 
{
    sno:string;
    branch_name: string;
}
