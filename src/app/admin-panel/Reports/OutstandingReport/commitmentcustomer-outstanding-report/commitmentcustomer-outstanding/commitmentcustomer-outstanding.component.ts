import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../../../utils/report-grid.helper';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-commitmentcustomer-outstanding',
  templateUrl: './commitmentcustomer-outstanding.component.html',
  styles: []
})
export class CommitmentcustomerOutstandingComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  CommitmentcustomerOutstandingReport: FormGroup;
  dataList: any[];
  showloader: boolean;
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
  nodataFoundMessage: boolean = true;
  totalBonus: any;
  totalChitvalue: any;
  totalDiscount: any;
  totalPenalty: any;
  totalPending: any;
  totalPaid: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewcommitmentcustomerInfo: ViewData[]=[];
  areaList: any;

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.CommitmentcustomerOutstandingReport.controls;
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

  branchChg(event) {
   
    this.groupList = [];
    let cond = {
      "conditions[branch_id]": event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.areaList = response.data;
        } else {
          this.areaList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
  
  resetCommitmentcustomerOutstandingReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.GroupwiseOutstandingReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.CommitmentcustomerOutstandingReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.CommitmentcustomerOutstandingReport.get('branch_id').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.CommitmentcustomerOutstandingReport.value);
    if (this.CommitmentcustomerOutstandingReport.invalid) 
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
    
    this.userservice.getData(result, "commitment-customer-wise-outstanding", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewcommitmentcustomerInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name',resizable: true },
            {headerName: 'Customer Address', field: 'customer_address',resizable: true },
            {headerName: 'Mobile No', field: 'customer_mobile',resizable: true},
            {headerName: 'Slab Name', field: 'slab_info' ,resizable: true},
            {headerName: 'Collection Area', field: 'collection_area_name',resizable: true },
            {headerName: 'Collection Employee', field: 'collection_agent_name' ,resizable: true},
            {headerName: 'Referd By', field: 'refered_by_name',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value' ,resizable: true},
            {headerName: 'Paid Due', field: 'overall_paid_amount',resizable: true},
            {headerName: 'Pending Amount', field: 'overall_pending_amount',resizable: true},
            {headerName: 'Penalty', field: 'penalty' ,resizable: true},
            {headerName: 'Bonus', field: 'bonus',resizable: true },
            {headerName: 'Discount', field: 'discount' ,resizable: true},
            {headerName: 'Pending Days', field: 'pending_days',resizable: true },
		      	{headerName: 'Payment Type', field: 'payment_type',resizable: true},
            
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.totalBonus=response.grand_total_bonus
          this.totalChitvalue=response.grand_total_chit_value
          this.totalDiscount=response.grand_total_discount
          this.totalPaid=response.grand_total_overal_paid
          this.totalPending=response.grand_total_overal_pending
          this.totalPenalty=response.grand_total_penalty
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
          this.appService.showAlert("Commitmentcustomer Outstanding Report", response.msg, "error");
        }
      }
        else{
          this.hideReport = true;
          this.nodataFoundMessage=false;
        }
      } catch (err) {console.log(err)
        this.appService.showAlert("Commitmentcustomer Outstanding Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {console.log(err)
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commitmentcustomer Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitmentcustomer Outstanding Report", "Sorry!! Something went wrong!", "error");
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
      fileName: '',
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
      pdf.save('Commitmentcustomerreport.pdf'); // Generated PDF   
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
    this.CommitmentcustomerOutstandingReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      slab_id: [''],
      area_id: [''],
      prized_status: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.CommitmentcustomerOutstandingReport.get('tenant_id').setValue(tenant_id);
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

