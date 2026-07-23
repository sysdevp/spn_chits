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
  
  selector: 'app-business-agent-report',
  templateUrl: './business-agent-report.component.html',
  styles: []
})
export class BusinessAgentReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_agent_id') focus3:ElementRef;
  @ViewChild('start_date') focus4:ElementRef;
  @ViewChild('end_date') focus5:ElementRef;
 
  BusinessAgentReport: FormGroup;
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
  agentCommission: any;
  totalChitValue: any;
  balanceAgentCommission: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewbusinessagentInfo: ViewData[]=[];

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.BusinessAgentReport.controls;
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
      "conditions[tenant_id]" : this.BusinessAgentReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
      "conditions[employee_type]" : 2
    }
      this.tenantid=this.BusinessAgentReport.get('tenant_id').value,
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
          'tenant_id' : this.BusinessAgentReport.get('tenant_id').value,
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
    //this.BusinessAgentReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.BusinessAgentReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.BusinessAgentReport.get('branch_id').setValue('');
    this.BusinessAgentReport.get('employee_agent_id').setValue('');
    this.BusinessAgentReport.get('start_date').setValue(null);
    this.BusinessAgentReport.get('end_date').setValue(null);
    this.agentList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.BusinessAgentReport.value);
    if (this.BusinessAgentReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_agent_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.start_date.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.end_date.errors) {
        this.focus5.nativeElement.focus();
      }
      return;
    } 
    this.userservice.getData(result, "get-business-agent-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          console.log(response)
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewbusinessagentInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
			      {headerName: 'Agent Name', field: 'agent_name',resizable: true},
			      {headerName: 'Phone No', field: 'agent_mobile',resizable: true},
            {headerName: 'Customer Name', field: 'customer_name',resizable: true },
            {headerName: 'Customer Mobile', field: 'customer_mobile',resizable: true },
			      {headerName: 'Group Name', field: 'group_name',resizable: true},
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true},
            {headerName: 'Total Commission Amount', field: 'total_business_agent_commission',resizable: true },
            {headerName: 'Commission Pending Amount', field: 'balance_business_agent_commission',resizable: true },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.agentCommission=response.grand_agent_commission;
          this.balanceAgentCommission=response.grand_bal_agent_commission;
          this.totalChitValue=response.grand_chit_value;
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
          setTimeout(() => {
            this.TableHidden = false;
          },0);*/
        } else {
          this.hideReport = true;
          this.nodataFoundMessage=true;
          this.appService.showAlert("Business Agent Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) { 
        this.appService.showAlert("Business Agent Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Business Agent Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Business Agent Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'business_agent_report',
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
      pdf.save('Businessagentreport.pdf'); // Generated PDF   
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
    this.BusinessAgentReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_agent_id: [''],
      start_date:[null],
      end_date:[null]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.BusinessAgentReport.get('tenant_id').setValue(tenant_id);
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
