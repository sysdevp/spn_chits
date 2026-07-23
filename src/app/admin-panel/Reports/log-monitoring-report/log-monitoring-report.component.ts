import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-log-monitoring-report',
  templateUrl: './log-monitoring-report.component.html',
  styles: []
})
export class LogMonitoringReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  logMonitoringReport: FormGroup;
  dataList: any;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  moduleList: any[];
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

  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.logMonitoringReport.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
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
    //this.logMonitoringReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.logMonitoringReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.logMonitoringReport.get('branch_id').setValue('');
    this.logMonitoringReport.get('module_id').setValue('');
    this.moduleList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.logMonitoringReport.value);
    if (this.logMonitoringReport.invalid) 
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
    
    this.userservice.getData(result, "log-monitoring-report", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          console.log(response)
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Branch Name', field: 'branch_name',resizable: true  },
            {headerName: 'Module Name', field: 'module_name',resizable: true },
            {headerName: 'Updated By', field: 'updated_by',resizable: true },
            {headerName: 'Updated On', field: 'updated_at',resizable: true },
            {headerName: 'Deleted By', field: 'deleted_by',resizable: true },
            {headerName: 'Deleted On', field: 'deleted_at',resizable: true},
            {headerName: 'Remarks', field: 'deletion_remark',resizable: true },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
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
          this.appService.showAlert("Log Monitoring Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) {
        this.appService.showAlert("Log Monitoring Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Log Monitoring Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Log Monitoring Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'logMonitoringReport',
      columnSeparator: ','
    };
    this.gridApi.exportDataAsCsv(params);
  }
  
  onPageSizeChanged() {
    var value = (<HTMLInputElement>document.getElementById("page-size")).value;
    this.gridApi.paginationSetPageSize(Number(value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  ngOnInit() {
    this.logMonitoringReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      module_name: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.logMonitoringReport.get('tenant_id').setValue(tenant_id);
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

    this.userservice.getData('', "module-name-list", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.moduleList = response.data;
          console.log( this.moduleList)
        } else {
          this.moduleList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  
  } 
}
