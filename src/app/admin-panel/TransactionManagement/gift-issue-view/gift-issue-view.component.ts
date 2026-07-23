import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-gift-issue-view',
  templateUrl: './gift-issue-view.component.html',
  styles: []
})
export class GiftIssueViewComponent implements OnInit {
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
  voucher_no: any;
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
setData(data) {
  this.dataList = data;
  $(function() {
    $('#dtable').DataTable();
  })
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
      voucher_no: [''],
      id: [''],
    });

    this.tenantid = localStorage.getItem('tenant_id');
    
    this.route.params.subscribe(params => {
      this.branchId = params.id;
      this.OtherReceiptReport.get('id').setValue(this.branchId);
      let cond = {
        "conditions[enrollment_id]" : this.branchId 
      }
      this.userservice.getData(cond, "gift-issue-view","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
         if (response.status == 'Success') 
          {
            
            this.dataList=response.data;
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
