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
  
  selector: 'app-installmentwise-report',
  templateUrl: './installmentwise-report.component.html',
  styles: []
})
export class InstallmentwiseReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('from_no') focus4:ElementRef;
  @ViewChild('to_no') focus5:ElementRef;

  InstallmentwiseReport: FormGroup;
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
  totalBounus: any;
  totalDiscount: any;
  totalPaid: any;
  totalPenalty: any;
  totalPending: any;
  tenantid: string;
  branchid: number;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewgroupwiseoutstandingInfo: ViewData[]=[];
  totalReleased: any;
  cashInHand: number;
  totalPaid_1: any;
  totalReleased_1: any;
  pending_details1: any;
  test: any[];
  installmenttest: any[];

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.InstallmentwiseReport.controls;
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
      "conditions[tenant_id]" : this.InstallmentwiseReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.InstallmentwiseReport.get('tenant_id').value,
      this.branchid=event.target.value
    
      if (this.tenantid!='' && this.branchid > 0)
      {
        this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.groupList = response.data;
              let installmentList = response.data[0].scheme_det.no_of_months;

              for(let i=1;i<=installmentList;i++){
                
                 this.installmenttest = response.data;
                 console.log(this.installmenttest);
            }
            } else {
              this.groupList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.InstallmentwiseReport.get('tenant_id').value,
        }
        this.userservice.getData(cond1, "list-groups", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.groupList = response.data;
              let installmentList = response.data[0].scheme_det.no_of_months;
            } else {
              this.groupList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
      }
      else{

      }
  }

  setData(data) {
    this.test = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
  
  resetGroupwiseReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.InstallmentwiseReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.InstallmentwiseReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.InstallmentwiseReport.get('branch_id').setValue('');
    this.InstallmentwiseReport.get('group_id').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.InstallmentwiseReport.value);
    if (this.InstallmentwiseReport.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.from_no.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.to_no.errors) {
        this.focus5.nativeElement.focus();
      }
      return;
    } 
    
    this.userservice.getData(result, "auction-wise-to-be-collected-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewgroupwiseoutstandingInfo = response.data;
      /*for (let i = 0; i < response.data.length; i++) {
        for (let j = 0; j < response.data[i].pending_details.length; j++) {
          this.pending_details = response.data[i].pending_details[j];
        }
        
       };*/


       /*var suhail=[], pending_details1=[];
          for(let i=0;i<response.data.length;i++){
            
              suhail= response.data[i].pending_details != "" ? response.data[i].pending_details : 0;
              pending_details1.push(response.data[i]);
              
        }
        console.log("ttt1",pending_details1);*/
        var pending_details=[], categories = [];
for(var i = 0; i < response.data.length;i++){
    categories.push(response.data[i]);
    for(var j = 0; j < response.data[i].pending_details.length;j++){
      pending_details.push(response.data[i].pending_details[j]);
      this.test = pending_details;
    }
}
console.log(this.test);
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true},
            {headerName: 'Group Name', field: 'group_name',resizable: true},
            {headerName: 'Area Name', field: 'area_name',resizable: true},
            {headerName: 'Agent Name', field: 'employee_name',resizable: true},
            {headerName: 'Customer Code', field: 'cust_code',resizable: true},
            {headerName: 'Customer Name', field: 'cust_name',resizable: true },
            {headerName: 'Customer Address', field: 'cust_address',resizable: true },
            {headerName: 'Customer Mobile', field: 'cust_mobile_no',resizable: true },
            {headerName: 'Ticket No', field: 'ticket_no',sort: "asc",resizable: true},
            {headerName: 'Installment No', field: 'installment_no',resizable: true },
            {headerName: 'Installmentwise Pending', field: 'inst_amt_wise_pending',resizable: true},
            {headerName: 'Installmentwise Penalty', field: 'penalty_inst_wise' ,resizable: true},
            {headerName: 'Installmentwise Bonus', field: 'bonus_inst_wise' ,resizable: true},
            {headerName: 'Installmentwise Discount', field: 'bonus_inst_wise',resizable: true},
            {headerName: 'Current Due', field: 'current_due' ,resizable: true},
            {headerName: 'To Be Collected', field: 'to_be_collected' ,resizable: true},
            
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, pending_details);
          this.totalBounus=response.grand_tot_overal_bonus;
          this.totalDiscount=response.grand_tot_overal_discount;
          this.totalPaid=response.grand_tot_overal_paid;
          this.totalPenalty=response.grand_tot_overal_penalty;
          this.totalPending=response.grand_tot_overal_pending;
          this.totalReleased=response.grand_customer_released_amount;
          this.totalPaid_1=response.grand_tot_overal_paid_1;
          this.totalReleased_1=response.grand_customer_released_amount_1;
          this.cashInHand = this.totalPaid_1 - this.totalReleased_1;
          this.dataList = pending_details;
		  if((response.data.length > 0)) {
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
          this.appService.showAlert("Installmentwise Report", response.msg, "error");
        }
      }
        else{
          this.hideReport = true;
          this.nodataFoundMessage=false;
        }
      } catch (err) {console.log(err)
        this.appService.showAlert("Installmentwise Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {console.log(err)
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Installmentwise Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Installmentwise Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'installmentwise_report',
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
      pdf.save('Installmentwisereport.pdf'); // Generated PDF   
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
    this.InstallmentwiseReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      from_no: [''],
      to_no: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.InstallmentwiseReport.get('tenant_id').setValue(tenant_id);
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
