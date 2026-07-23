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
  
  selector: 'app-commitment-pending-report',
  templateUrl: './commitment-pending-report.component.html',
  styles: []
})
export class CommitmentPendingReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  commitmentPending: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  customerList: any[];
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
  totalChitValue: any;
  totalPendingAmt: any;
  totalReleasedAmt: any;
  totaltobepay: any;
  id: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewcommitmentpendingInfo: ViewData[]=[];

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.commitmentPending.controls;
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
    let id  = event.target.value;
    this.setCustomerList(id);
  }
  setCustomerList(id)
  {
      let cond = {
        "id" :id
      }
        this.id=id
        if (this.id > 0 )
        {
          this.userservice.getData(cond, "get-commitment-customers", "POST").subscribe((response: any) => {
            try { 
              if (response.status == 'Success') {
                this.customerList = response.data;
              } else {
                this.customerList = [];
              }
            }  catch (err) { }
          }, (err: any) => { });
        }
        else if(this.id==0)
        {
          let cond1 = {
            'id' : 0,
          }
          this.userservice.getData(cond1, "get-commitment-customers", "POST").subscribe((response: any) => {
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

  resetcommitmentPendingReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.commitmentPending.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.commitmentPending.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.commitmentPending.get('branch_id').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.commitmentPending.value);
    if (this.commitmentPending.invalid) 
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
    
    this.userservice.getData(result, "get-commitment-pending-report", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewcommitmentpendingInfo = response.data[0];
			    this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
			      {headerName: 'Customer Name', field: 'customer_name',resizable: true},
            {headerName: 'Slab Name', field: 'slab_name',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true },
			      {headerName: 'Payment Month', field: 'payment_month',resizable: true},
            {headerName: 'Total Released Amount', field: 'total_payment_release',resizable: true },
            {headerName: 'Pending Released Amount', field: 'pending_released',resizable: true},
            {headerName: 'Payment Type', field: 'payment_type_name',resizable: true },
            {headerName: 'Released Amount', field: 'payment_released',resizable: true },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.totalChitValue=response.grand_chit_value;
          this.totalPendingAmt=response.grand_pending;
          this.totalReleasedAmt=response.grand_released;
          this.totaltobepay=response.grand_to_be_pay;
          this.dataList = response.data;
		  if((response.data.length)>0) {
			this.hideDataList = false; 
			this.nodataFoundMessage = true;
			this.hideReport = false;
		  }
		  else {
			this.hideDataList = true; 
			this.nodataFoundMessage = true;
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
          this.nodataFoundMessage = true;
          this.appService.showAlert("Commitment Pending Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      } catch (err) { 
        this.appService.showAlert("Commitment Pending Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commitment Pending Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitment Pending Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'commitment_pending_report',
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
      pdf.save('Commitmentpendingreport.pdf'); // Generated PDF   
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
    this.commitmentPending = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id:[''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.commitmentPending.get('tenant_id').setValue(tenant_id);
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
