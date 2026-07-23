import { Component,OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { NgxSpinnerService } from 'ngx-spinner';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-payment-voucher',
  templateUrl: './payment-voucher.component.html',
  styles: []
})
export class PaymentVoucherComponent implements OnInit {
  [x: string]: any;

  companyLogo = "assets/img/logo1.jpg";
  form_message = true;
  PaymentVoucher: FormGroup;

  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchId: any;
  receiptList: any;
  val: any;
  receipt_no: any;
  receipt_date: any;
  customer_name: any;
  group_name: any;
  ticket_no: any;
  first_name: any;
  last_name: any;
  amount: any;
  bonus: any;
  penalty: any;
  payment_name: any;
  total: any;
  netamount: number;
  hidethis: boolean = false;
  receiptHide:boolean = false;
  receiptHide1:boolean = false;
  receiptHide2:boolean = false;
  http: any;
  auctionId: any;
  paymentList: any[];
  isSuperAdmin:boolean;
  companyName: any;
  branchName: any;
  groupName: any;
  ticketNo: any;
  customerName: any;
  chitValue: any;
  biddingAmount: any;
  commissionAmount: any;
  bidAdvanceAmount: any;
  cgst: any;
  sgst: any;
  gst: any;
  bidPaymentAdjustment: any;
  totalReleaseAmount: any;
  customerReleasedAmount: any;
  voucher_no: any;
  pendingReleaseAmoun: number;
  cur_date: any;
  chit_fund_name: any;
  address_line_1: any;
  address_line_2: any;
  mobile_no: any;
  tenantid: any;
  amount_in_words:any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService, private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
    if((localStorage.getItem('company_logo') != null) && (localStorage.getItem('company_logo') != ''))
    {
      this.companyLogo = localStorage.getItem('company_logo');
    }
  }

  print(): void {
    let printContents, popupWin;
    printContents = document.getElementById('print-section').innerHTML;
    popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
    popupWin.document.open();
    popupWin.document.write(`
      <html>
        <head>
          <title>Print tab</title>
          <style>
          //........Customized style.......
          </style>
        </head>
    <body id="test" onload="window.print();window.close()">${printContents}</body>
      </html>`
    );
    popupWin.document.close();
}

print1(): void {
  let printContents, popupWin;
  printContents = document.getElementById('print-section1').innerHTML;
  popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
  popupWin.document.open();
  popupWin.document.write(`
    <html>
      <head>
        <title>Print tab</title>
        <style>
        //........Customized style.......
        </style>
      </head>
  <body id="test" onload="window.print();window.close()">${printContents}</body>
    </html>`
  );
  popupWin.document.close();
}

print2(): void {
  let printContents, popupWin;
  printContents = document.getElementById('print-section2').innerHTML;
  popupWin = window.open('', '_blank', 'top=0,left=0,height=100%,width=auto');
  popupWin.document.open();
  popupWin.document.write(`
    <html>
      <head>
        <title>Print tab</title>
        <style>
        //........Customized style.......
        </style>
      </head>
  <body id="test" onload="window.print();window.close()">${printContents}</body>
    </html>`
  );
  popupWin.document.close();
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
      pdf.save('PaymentVoucher.pdf'); // Generated PDF   
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
      pdf.save('receipt.pdf'); // Generated PDF  
      
    });

  }
 

  ngOnInit() {
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
    }
    else {
      this.isSuperAdmin = true;
    }
    this.tenantid = localStorage.getItem('tenant_id');
    if(this.tenantid == 12){
      this.receiptHide = true;
      this.receiptHide1 = false;
      this.receiptHide2 = false;
    }
    if(this.tenantid == 42){
      this.receiptHide = true;
      this.receiptHide1 = true;
      this.receiptHide2 = false;
    }
    else{
      this.receiptHide = false;
      this.receiptHide1 = true;
      this.receiptHide2 = false;
    }
    this.route.params.subscribe(params => {
      this.auctionId = params.id;
    });
    let cond = {
      "conditions[bidding_id]" : this.auctionId
    }
    this.userservice.getData(cond, "view-payment", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.chit_fund_name = response.data.payments[0].tenant_det.chit_fund_name;
            this.address_line_1 = response.data.payments[0].branch_det.address_line_1;
            this.address_line_2 = response.data.payments[0].branch_det.address_line_2;
            this.mobile_no = response.data.payments[0].branch_det.mobile_no;
            this.company_logo_original = response.data.payments[0].tenant_det.company_logo_original;
          this.companyName = response.data.company_name;
          this.branchName = response.data.branch_name;
          this.groupName = response.data.group_name;
          this.customer_name = response.data.customer_name;
          this.voucher_no = response.data.voucher_no;
          this.cur_date = response.data.payment_date;
          console.log(this.groupName);
          this.ticketNo = response.data.ticket_no;
          this.customerName = response.data.customer_name_det;
          this.chitValue = response.data.chit_value;
          this.biddingAmount = response.data.bidding_amount;
          this.commissionAmount = response.data.commission_amount;
          this.bidAdvanceAmount = response.data.bid_advance_amount;
          this.cgst = response.data.cgst;
          this.sgst = response.data.sgst;
          this.gst = response.data.gst;
          this.amount_in_words = response.amount_in_words;
          this.payment_name = response.data.payments[0].payment_type;
          this.mobile_no = response.data.payments[0].branch_det.mobile_no;
          this.totalReleaseAmount = response.data.total_release_amount;
          this.customerReleasedAmount = response.data.customer_released_amount;
          this.paymentList = response.data.payments;
          this.pendingReleaseAmoun = parseFloat(this.totalReleaseAmount) - parseFloat(this.customerReleasedAmount);
        }
        else {
          this.appService.showAlert("Payments", "Sorry!! Something went wrong!", "error");
        }
      }
      catch (err) {}
    });
     
  }
}

