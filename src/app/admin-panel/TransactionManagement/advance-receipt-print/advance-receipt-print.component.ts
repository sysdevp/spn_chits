import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-advance-receipt-print',
  templateUrl: './advance-receipt-print.component.html',
  styles: []
})
export class AdvanceReceiptPrintComponent implements OnInit {
  [x: string]: any;

  companyLogo = "assets/img/logo1.jpg";
  form_message = true;
  OtherReceiptReport: FormGroup;

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
  receiptHide3:boolean = false;
  receiptHide5:boolean = false;
  receiptHide6:boolean = false;
  receiptHide4:boolean = false;
  http: any;
  customer: any;
  charge_name: any;
  chit_fund_name: any;
  address_line_1: any;
  address_line_2: any;
  mobile_no: any;
  amount_in_words: any;
  tenantid: any;
  company_logo_original: any;
  company_rec: any;
  receiptHide2:boolean = false;
  chit_value: any;
  ag_no: any;
  


  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
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

print3(): void {
  let printContents, popupWin;
  printContents = document.getElementById('print-section3').innerHTML;
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

print5(): void {
  let printContents, popupWin;
  printContents = document.getElementById('print-section5').innerHTML;
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



print6(): void {
  let printContents, popupWin;
  printContents = document.getElementById('print-section6').innerHTML;
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
      pdf.save('receipt.pdf'); // Generated PDF  
      
    });

  }
 

  ngOnInit() {
    this.OtherReceiptReport = this.formBuilder.group({
      receipt_no: [''],
      id: [''],
    });

    this.tenantid = localStorage.getItem('tenant_id');
    if(this.tenantid == 12){
      this.receiptHide = true;
      this.receiptHide1 = false;
      this.receiptHide2 = true;
      this.receiptHide3 = true;
      this.receiptHide4 = true;
      this.receiptHide5 = true;
      this.receiptHide6 = true;
    } else if(this.tenantid == 20){
      this.receiptHide = true;
      this.receiptHide1 = true;
      this.receiptHide2 = false;
      this.receiptHide3 = true;
      this.receiptHide4 = true;
      this.receiptHide5 = true;
      this.receiptHide6 = true;
    } else if(this.tenantid == 42){
      this.receiptHide = true;
      this.receiptHide1 = true;
      this.receiptHide2 = true;
      this.receiptHide3 = true;
      this.receiptHide4 = true;
      this.receiptHide5 = false;
      this.receiptHide6 = true;
    } else if(this.tenantid == 57){
      this.receiptHide = true;
      this.receiptHide1 = true;
      this.receiptHide2 = true;
      this.receiptHide4 = true;
      this.receiptHide3 = true;
      this.receiptHide5 = true;
      this.receiptHide6 = false;
    } 
    else{
      this.receiptHide = false;
      this.receiptHide1 = true;
      this.receiptHide2 = true;
      this.receiptHide3 = true;
      this.receiptHide4 = true;
      this.receiptHide5 = true;
      this.receiptHide6 = true;
    }
    this.route.params.subscribe(params => {
      this.branchId = params.receipt_no;
      this.OtherReceiptReport.get('receipt_no').setValue(this.branchId);
      let cond = {
        "conditions[receipt_no]" : this.branchId 
      }
      this.userservice.getData(cond, "advance-receipt-print","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
         if (response.status == 'Success') 
          {
            this.amount_in_words = response.amount_in_words;
            this.chit_fund_name = response.data[0].tenant_info.chit_fund_name;
            this.company_logo_original = response.data[0].tenant_info.company_logo_original;
            this.company_rec = response.data[0].tenant_info.company_rec;
            this.address_line_1 = response.data[0].branch_info.address_line_1;
            this.address_line_2 = response.data[0].branch_info.address_line_2;
            this.mobile_no = response.data[0].branch_info.mobile_no;
            this.receipt_no = response.data[0].receipt_no;
            this.receipt_date = response.data[0].receipt_date;
            this.customer_name = response.data[0].customer_info.name;
            this.group_name = response.data[0].group_info.group_name;
            this.ticket_no = response.data[0].enrollment_info.ticket_no;
            this.first_name = response.data[0].employee_info.first_name;
            this.last_name = response.data[0].employee_info.last_name;
            this.payment_name = response.data[0].payment_type_info.payment_name;
            this.amount = response.data[0].total_paid;
            this.bonus = response.data[0].bonus_amount;
            this.penalty = response.data[0].penalty_amount;
            this.prized_status = response.data[0].prized_status;
            this.chit_value = response.data[0].chit_value;
            this.ag_no = response.data[0].ag_no;
            this.gst_no = response.data[0].gst_no;
            this.total = parseFloat(this.amount) + parseFloat(this.penalty);
            this.netamount = parseFloat(this.total) - parseFloat(this.bonus);
          }
          else {
            this.appService.showAlert("Advance Receipt Print", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Advance Receipt Print", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Advance Receipt Print", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Advance Receipt Print", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
     
  }
}


