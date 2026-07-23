import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';
import {saveAs} from 'file-saver';
import {ExcelService} from '../../../../Services/excel.service';
import  XLSX from 'xlsx';

@Component({
  
  selector: 'app-groupwise-outstanding-abstract',
  templateUrl: './groupwise-outstanding-abstract.component.html',
  styles: []
})
export class GroupwiseOutstandingAbstractComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('group_abstract') focus4:ElementRef;

  CustomerLedger: FormGroup;
  companyList: any[];
  branchList: any[];
  customerList: any[];
  auctionwiseReceiptList: any[];
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  submitted: boolean;
  disableBtn: boolean;
  //auctionList: any[];
  //branchId: any;
  //customerId: any;
  //CustomerDetailList: any[];
  runningGroupList: any[];
  //paidPendingList: any[];
  customerDocumentInfo: any[];
  enrollmentInfo: any[];
  photo: any;
  //GurantorDocumentInfo: any;
  hidecustomerLedger:boolean=true;
  relationshipList: any;
  hideClosedGroup:boolean=true;
  hiderunningGroup:boolean=true;
  hideEnrolmentDetails:boolean=true;
  hideLoanDetails:boolean=true;
  hideEnrolment1Details:boolean=true;
  paidreport:boolean=true;
  grpabstracthide:boolean=true;
  pendingreport:boolean=true;
  nodatafound:boolean=true;
  closedGroupList: any[];
  gurantorDocumentInfo: any[];
  panDocument: any;
  rationDocument: any;
  adharDocument: any;
  auctionwiseList: any[];
  groupadvanceList: any;
  disbursementRowLength: number = 0;
  disbursementInfo: any[];
  disbursementPayment: any[];
  enrolList: any[];
  loanList: any[];
  EnrolId: any;
  //Customer Information
  name: any;
  gridApi;
  branchName: any;
  MobileNo: any;
  Status: any;
  Address_1: any;
  Address_2: any;
  communicationAddress_1: any;
  communicationAddress_2: any;
  relationship: any;
  prizedPending: any;
  nonPrizedPending: any;
  prizedPaid: any;
  nonPrizedPaid: any;
  groupName: any;
  customerTotalChitVaue: any;
  gurantorTotalChitVaue: any;
  //runningGroupTotalChitVaue: any;
  closedGroupTotalChitVaue: any;
  ticketNo: any;
  totalClosedGroupChitValue: number;
  totalRunningGroupChitValue: any;
  customerDocumentTotalChitValue: any;
  gurantorDocumentTotalChitValue: any;
  deductions: any;
  LoanId: any;
  loanreceiptList: any[];
  loandetailList: any;
  chitValue: any;
  prizedStatus: any;
  completedAuctions: any;
  paidAmt: any;
  pendingAmt: any;
  advanceAmt: any;
  nextDueAmt: any;
  pendingInstallment: any;
  hidecustomDetails: boolean=false;
  beforeenrolreceipts: any;
  totalReceiptAmt: any;
  viewgroupwiseoutstandingInfo: any;
  received: string;
  default: string;
  credit_amount: number;
  auctiondate1: any;
  auctiondate2: any;
  auctiondate3: any;
  auctiondate4: any;
  auctiondate5: any;
  auctiondate6: any;
  auctiondate7: any;
  auctiondate8: any;
  auctiondate9: any;
  auctiondate10: any;
  auctiondate11: any;
  auctiondate12: any;
  auctiondate13: any;
  auctiondate14: any;
  auctiondate15: any;
  auctiondate16: any;
  auctiondate17: any;
  auctiondate18: any;
  auctiondate19: any;
  auctiondate20: any;
  element: any;
  isauction: any;

  constructor(private excelService:ExcelService,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.CustomerLedger.controls;
  }

  

  exportAsXLSX() {
    /* table id is passed over here */   
    let element = document.getElementById('mygrid'); 
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(element);
 
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpaid.xls`;
    /* save to file */
    XLSX.writeFile(wb, "Groupabstractpaid.csv");
  }

  exportAsXLSX1():void { 
    /* table id is passed over here */   
    let element = document.getElementById('mygrid1'); 
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(element);
 
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpending.xls`;
    /* save to file */
    XLSX.writeFile(wb, "Groupabstractpending.csv");
   }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.CustomerLedger.get('branch_id').setValue('');
    this.branchList = [];
    this.CustomerLedger.get('group_id').setValue('');
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
  
  branchChg(event)
  {
    this.hidecustomerLedger=true;
    this.hideEnrolmentDetails=true;
    this.hideLoanDetails=true;
    this.hideEnrolment1Details=true;
    this.hidecustomDetails=false;
    let cond = {
      "conditions[branch_id]" : event.target.value,
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  grpabstractChg(event)
  {
    let grpabstract = event.target.value;
    if(grpabstract == 1){
      this.paidreport=false;
      this.pendingreport=true;
    }
    else if(grpabstract == ''){
      this.paidreport=true;
      this.pendingreport=true;
    }
    else{
      this.paidreport=true;
      this.pendingreport=false;
    }

  }


  /*onClickMe(args) {
    const self = this;
    const mygrid = 'exportExcel.xlsx';
    const json = JSON.stringify(self.spread.toJSON());
    self.excelIO.save(json, function (blob) {
      saveAs(blob, mygrid);
  }, function (e) {
      console.log(e);
  });
  }*/

  onSubmit() {
    this.CustomerLedger.get('group_abstract').setValue('');
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    const result = Object.assign({}, this.CustomerLedger.value);
    if (this.CustomerLedger.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.group_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if (this.f.group_abstract.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.hidecustomerLedger=false;
    this.hiderunningGroup=true;
    this.hideClosedGroup=true;
    this.grpabstracthide=false;
    this.paidreport=true;
    this.pendingreport=true;
    this.userservice.getData(result, "group-abstract-report", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.nodatafound=true;
          this.SubmitbuttonName = "Submit";
          //this.CustomerDetailList = response.data;
          this.viewgroupwiseoutstandingInfo = response.data;
          this.isauction = response.data[0].isauction;
          if(this.isauction == "no"){
            this.nodatafound=false;
            console.log("test");
            this.SubmitbuttonName = "Submit";
          }
          else{
            console.log("test1");
            this.nodatafound=true;
            this.SubmitbuttonName = "Submit";
          }
          this.auctiondate1 = response.data[0].pending_details[0].auction_date;
          this.auctiondate2 = response.data[0].pending_details[1].auction_date;
          this.auctiondate3 = response.data[0].pending_details[2].auction_date;
          this.auctiondate4 = response.data[0].pending_details[3].auction_date;
          this.auctiondate5 = response.data[0].pending_details[4].auction_date;
          this.auctiondate6 = response.data[0].pending_details[5].auction_date;
          this.auctiondate7 = response.data[0].pending_details[6].auction_date;
          this.auctiondate8 = response.data[0].pending_details[7].auction_date;
          this.auctiondate9 = response.data[0].pending_details[8].auction_date;
          this.auctiondate10 = response.data[0].pending_details[9].auction_date;
          this.auctiondate11 = response.data[0].pending_details[10].auction_date;
          this.auctiondate12 = response.data[0].pending_details[11].auction_date;
          this.auctiondate13 = response.data[0].pending_details[12].auction_date;
          this.auctiondate14 = response.data[0].pending_details[13].auction_date;
          this.auctiondate15 = response.data[0].pending_details[14].auction_date;
          this.auctiondate16 = response.data[0].pending_details[15].auction_date;
          this.auctiondate17 = response.data[0].pending_details[16].auction_date;
          this.auctiondate18 = response.data[0].pending_details[17].auction_date;
          this.auctiondate19 = response.data[0].pending_details[18].auction_date;
          this.auctiondate20 = response.data[0].pending_details[19].auction_date;
          
          for(let i=0;i<20;i++){
         
         this.credit_amount = 0;
         for(let j=0;j<response.data[i].pending_details.length;j++){
          if(response.data[i].pending_details[j].auction_date){
            
            this.credit_amount= response.data[i].pending_details[0].auction_date != "" ? response.data[i].pending_details[0].auction_date : 0;
           
          }
          
         }
         
           

        }
          
          
      }else {
        
        this.SubmitbuttonName = "Submit";
        this.nodatafound=false;
          this.viewgroupwiseoutstandingInfo = [];
        }
      }  catch (err) { 
        this.SubmitbuttonName = "Submit";
        this.nodatafound=false;
          this.viewgroupwiseoutstandingInfo = [];
      }
    }, (err: any) => { 
      this.SubmitbuttonName = "Submit";
        this.nodatafound=false;
          this.viewgroupwiseoutstandingInfo = [];
    });
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
      pdf.save('GroupAbstractPaidReport.pdf'); // Generated PDF   
    });
  }

  onpdf1():void
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
    var data = document.getElementById('mygrid1');  
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
      pdf.save('GroupAbstractPendingReport.pdf'); // Generated PDF   
    });
  }

   

  
  ngOnInit() {
    /* this.userservice.getData('', "list-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); */
    this.CustomerLedger = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      group_abstract: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.CustomerLedger.get('tenant_id').setValue(tenant_id);
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


