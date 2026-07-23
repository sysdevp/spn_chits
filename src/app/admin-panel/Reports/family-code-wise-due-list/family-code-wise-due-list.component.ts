import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-family-code-wise-due-list',
  templateUrl: './family-code-wise-due-list.component.html',
  styles: []
})
export class FamilyCodeWiseDueListComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  DueListReport: FormGroup;
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
  viewenrollmentInfo: ViewData[]=[];
  familyList: any;

  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.DueListReport.controls;
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

  branchChg(event) 
  {
    let cond = {
      "conditions[tenant_id]" : this.DueListReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.DueListReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        

        this.userservice.getData(cond, "get-family-codes", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.familyList = response.data;
            } else {
              this.familyList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.DueListReport.get('tenant_id').value,
        }
        

        this.userservice.getData(cond1, "list-family-codes", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.familyList = response.data;
            } else {
              this.familyList = [];
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
      this.DueListReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.DueListReport.get('branch_id').setValue('');
    this.groupList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.DueListReport.value);
    if (this.DueListReport.invalid) 
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
    
    this.userservice.getData(result, "enrol-report", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          console.log(response)
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewenrollmentInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Date', field: 'branch_name',resizable: true  },
            {headerName: 'Prized', field: 'branch_name',resizable: true },
            {headerName: 'Auc', field: 'branch_name',resizable: true },
            {headerName: 'Member Name', field: 'branch_name',resizable: true },
            {headerName: 'Group', field: 'branch_name',resizable: true },
            {headerName: 'Ticket No', field: 'branch_name',resizable: true},
            {headerName: 'Installment No', field: 'branch_name',resizable: true },
            {headerName: 'Installment Amt', field: 'branch_name',resizable: true },
            {headerName: 'Dividend', field: 'branch_name',resizable: true },
            {headerName: 'Bonus', field: 'branch_name',resizable: true },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
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
          this.appService.showAlert("Family Code Wise Due List", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
       
      }
      } catch (err) {
        this.appService.showAlert("Family Code Wise Due List", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Family Code Wise Due List", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Family Code Wise Due List", "Sorry!! Something went wrong!", "error");
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
      fileName: 'family_code_wise_due_list',
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
      pdf.save('FamilyCodeWiseDueList.pdf'); // Generated PDF   
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
    this.DueListReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      family_id: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.DueListReport.get('tenant_id').setValue(tenant_id);
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
