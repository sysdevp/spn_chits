import { Component, OnInit, ElementRef,ViewChild } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { ReportPopupModalComponent } from '../../../../modal/report-popup-modal/report-popup-modal.component';
import { GridOptions } from 'ag-grid/main';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-employeewise-outstanding-report',
  templateUrl: './employeewise-outstanding-report.component.html',
  styles: []
})
export class EmployeewiseOutstandingReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  

  employeewiseOutstandingReport: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  agentList: any[];
  branchInfo: ViewData[]=[];
  customerInfo: ViewData[]=[];
  groupInfo: ViewData[]=[];
  agentInfo: ViewData[]=[];
  ViewCommissionAmountPendingInfo: any;
  
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
  ViewCommissionAmountPendingInfos: any;
  paidAmt: any;
  pendingAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewemployeewiseoutstandingInfo: ViewData[]=[];

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.gridOptions = <GridOptions>{
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this,
        ngxSmartModalService: this.ngxSmartModalService,
      },
      //components: {
      //},
    };
  }

  get f() {
    return this.employeewiseOutstandingReport.controls;
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
      "conditions[tenant_id]" : this.employeewiseOutstandingReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.employeewiseOutstandingReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              if(response.data!='')
              {
              this.agentList = response.data;
            }
           } else {
              this.agentList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.employeewiseOutstandingReport.get('tenant_id').value,
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

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }

  resetemployeeoutstandingReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.commissionAmountPending.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.employeewiseOutstandingReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.employeewiseOutstandingReport.get('branch_id').setValue('');
  }

  

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.employeewiseOutstandingReport.value);
    if (this.employeewiseOutstandingReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    } 
    
    this.userservice.getData(result, "employee-wise-outstanding", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
          if (response.status == 'Success') {
            this.viewemployeewiseoutstandingInfo = response.data[0];
			      this.columnDefs = [
              {headerName: 'Sno', field: 'sno',sort: "sno",resizable: true},
              {headerName: 'Branch Name', field: 'branch_name',resizable: true },
              {headerName: 'Employee Name', field: 'employee_name',resizable: true},
             /*  {headerName: 'Employee Id', field: 'employee_id',resizable: true}, */
              {headerName: 'Phone No', field: 'mobile_no',resizable: true},
              {headerName: 'Paid Amount', field: 'overall_total_paid',resizable: true },
              {headerName: 'Pending Amount', field: 'overall_total_pending',resizable: true },
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
            width: 180, 
          };
          applyReportGridData(this, response.data);
          this.paidAmt=response.grand_tot_paid;
          this.pendingAmt=response.grand_tot_pending;
          this.dataList = response.data;
          if((response.data.length)>0) {
            this.hideDataList = false; 
            this.nodataFoundMessage = true;
            this.hideReport = false;
		/*	for(let i=0;i<(response.data.length);i++) {
				response.data[i].full_view = '<a class="bg-success border-0 text-white px-3 py-2" (click)="open(uploadModal, { windowClass: modal-lg animate }, row.introducer_id)">View</a>';
      }*/
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
          this.nodataFoundMessage=true;
          this.appService.showAlert("Employeewise Outstanding Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) { 
        this.appService.showAlert("Employeewise Outstanding Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Employeewise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Employeewise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  openPopup(record) {
    let cond = {
      "employee_id" : record.data.employee_id
    }
    record.context.userservice.getData(cond, "customer-wise-employee-outstanding", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewCommissionAmountPendingInfo = response.data;
          let content = '<div class="card-body"><div class="row" style="overflow-x:scroll "><table id="dtable" class="row-border hover das-auc-t c_a_t" [hidden]="TableHidden"><thead><tr> ';
          content += '<th>SNo</th><th>Customer Name</th><th>Mobile No</th><th>Group Name</th><th>Ticket No</th><th>Chit Value</th><th>Pending Amount</th></tr></thead>';
          content += '<tbody>'; 
          if((this.ViewCommissionAmountPendingInfo.length)>0) {
            console.log()
            for(let i=0; i<(this.ViewCommissionAmountPendingInfo.length);i++) {
              let pendingInfo = this.ViewCommissionAmountPendingInfo[i];
              content += '<tr>';
              content += '<td>'+pendingInfo.sno+'</td>';
              content += '<td>'+pendingInfo.customer_name+'</td>';
              content += '<td>'+pendingInfo.mobile_no+'</td>';
              content += '<td>'+pendingInfo.group_name+'</td>';
              content += '<td>'+pendingInfo.ticket_no+'</td>';
              content += '<td>'+pendingInfo.chit_value+'</td>';
              content += '<td>'+pendingInfo.pending_amount+'</td>';
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
          this.ViewCommissionAmountPendingInfo = [];
          this.branchInfo = [];
          this.customerInfo = [];
          this.groupInfo = [];
          this.agentInfo = [];
        }
      } catch (err) { console.log(err)
        record.context.appService.showAlert("Employeewise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {  console.log(err)
      if (err.status == 401) {
        record.context.appService.showAlert("Employeewise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        record.context.appService.showAlert("Employeewise Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
    });
    record.context.ngxSmartModalService.getModal('myModal').open()
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
      fileName: 'employeewise_outstanding_report',
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
      pdf.save('Employeewiseoutstandingreport.pdf'); // Generated PDF   
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
    this.employeewiseOutstandingReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id:[''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.employeewiseOutstandingReport.get('tenant_id').setValue(tenant_id);
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
  teant_id:string;
  branch_id:string;
  agent_id:string;
}
