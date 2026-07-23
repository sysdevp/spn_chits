import { Component,OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { NgxSpinnerService } from 'ngx-spinner';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-gst-print',
  templateUrl: './gst-print.component.html',
  styles: []
})
export class GstPrintComponent implements OnInit {

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
  viewreceiptInfo: ViewData[]=[];

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService, private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
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
    this.route.params.subscribe(params => {
      this.auctionId = params.id;
    });
    let cond = {
      "id" : this.auctionId
    }
    this.userservice.getData(cond, "gst-voucher", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.viewreceiptInfo = response.data[0];
        }
        else {
          this.appService.showAlert("Payments", "Sorry!! Something went wrong!", "error");
        }
      }
      catch (err) {}
    });
     
  }
}
export interface ViewData 
{
    sno:string;
    branch_name: string;
}


