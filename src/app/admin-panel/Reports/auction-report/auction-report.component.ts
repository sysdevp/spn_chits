import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { applyReportGridData } from '../../../utils/report-grid.helper';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-auction-report',
  templateUrl: './auction-report.component.html',
  styles: []
})

export class AuctionReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  AuctionReport: FormGroup;
  dataList: any;
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
  nodataFoundMessage: boolean=true;
  totalChitValue: any;
  totalBiddingAmt: any;
  totalCommissionAmt: any;
  totalCGST: any;
  totalSGST: any;
  totalGST: any;
  totalCustomerRelease: any;
  totalDividentAmt: any;
  totalInstallmentAmt: any;
  totalPendingAmt: any;
  totalReleaseAmt: any;
  tenantid: any;
  branchid: any;
  print_date: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewauctionInfo: ViewData[]=[];

  constructor( private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.AuctionReport.controls;
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
      "conditions[tenant_id]" : this.AuctionReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.AuctionReport.get('tenant_id').value,
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
          }  catch (err) { }
        }, (err: any) => { });
      }
      else if(this.tenantid!=''&& this.branchid==0) 
      {
        let cond1 = {
          'tenant_id' : this.AuctionReport.get('tenant_id').value,
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
    //this.AuctionReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.AuctionReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.AuctionReport.get('branch_id').setValue('');
    this.AuctionReport.get('group_id').setValue('');
    this.AuctionReport.get('start_date').setValue(null);
    this.AuctionReport.get('end_date').setValue(null);
    this.groupList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.AuctionReport.value);
    if (this.AuctionReport.invalid) 
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
    this.userservice.getData(result, "auction-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.viewauctionInfo = response.data[0];
			this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true },
            {headerName: 'Branch Name', field: 'branch_name' ,resizable: true },
            {headerName: 'Installment No', field: 'installment_no' ,resizable: true },
            {headerName: 'Auction Date', field: 'auction_date',resizable: true },
            {headerName: 'Tally Customer Id', field: 'customer_id',resizable: true },
            {headerName: 'Customer Code', field: 'customer_code',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name',resizable: true },
            {headerName: 'Mobile No', field: 'phone_no',resizable: true },
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true  },
            {headerName: 'Group Name', field: 'group_name',resizable: true },
            {headerName: 'Chit Value', field: 'chit_value',resizable: true },
            {headerName: 'No Of Members', field: 'no_of_members',resizable: true  },
            {headerName: 'Bidding Amount', field: 'bidding_amount',resizable: true  },
			      {headerName: 'Commission Amount', field: 'commission_amount',resizable: true },
            {headerName: 'CGST', field: 'cgst' ,resizable: true },
            {headerName: 'SGST', field: 'sgst',resizable: true  },
            {headerName: 'GST', field: 'gst',resizable: true  },
            {headerName: 'Total Release Amount', field: 'total_release_amount',resizable: true },
            {headerName: 'Customer Released Amount', field: 'customer_released',resizable: true  },
			      {headerName: 'Pending Release Amount', field: 'pending_release',resizable: true  },
            {headerName: 'Each Member Dividend Amount', field: 'divident_amount',resizable: true },
            {headerName: 'Installment Amount', field: 'installment_amount',resizable: true },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          applyReportGridData(this, response.data);
          this.print_date = response.print_date;
          this.totalChitValue=response.grand_chit_value;
          this.totalBiddingAmt=response.grand_bid_amt;
          this.totalCommissionAmt=response.grand_commission_amt;
          this.totalCGST=response.grand_cgst_amt;
          this.totalSGST=response.grand_sgst_amt;
          this.totalGST=response.grand_gst_amt;
          this.totalCustomerRelease=response.grand_customer_amt;
          this.totalDividentAmt=response.grand_divi_amt;
          this.totalInstallmentAmt=response.grand_inst_amt;
          this.totalPendingAmt=response.grand_pending_amt;
          this.totalReleaseAmt=response.grand_release_amt;
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
          this.nodataFoundMessage=true;
          this.appService.showAlert("Auction Report", response.msg, "error");
        }
      }
      else{
        this.hideReport = true;
        this.nodataFoundMessage=false;
      }
      } catch (err) { 
        this.appService.showAlert("Auction Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Auction Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Auction Report", "Sorry!! Something went wrong!", "error");
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
      fileName: 'auction_report',
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
      pdf.save('Auctionreport.pdf'); // Generated PDF   
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
    this.AuctionReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      start_date:[null],
      end_date:[null]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AuctionReport.get('tenant_id').setValue(tenant_id);
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
