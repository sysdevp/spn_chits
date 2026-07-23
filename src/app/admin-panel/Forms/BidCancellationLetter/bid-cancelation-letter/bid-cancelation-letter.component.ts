import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router,ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-bid-cancelation-letter',
  templateUrl: './bid-cancelation-letter.component.html',
  styles: []
})
export class BidCancelationLetterComponent implements OnInit {

  Bidcancelationletter: FormGroup;
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
  biddingDetails: ViewData[]=[];
  

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
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
    this.Bidcancelationletter = this.formBuilder.group({
      id: [''],
    });
    this.route.params.subscribe(params => {
      this.branchId = params.id;
      this.Bidcancelationletter.get('id').setValue(this.branchId);
      let cond = {
        "id" : this.branchId 
      }
      this.userservice.getData(cond, "bid-cancelation-letter","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
         if (response.status == 'Success') 
          {
            this.biddingDetails = response.data[0];
          }
          else {
            this.appService.showAlert("Bid Cancelation", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Bid Cancelation", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Bid Cancelation", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Bid Cancelation", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
     
  }
}
export interface ViewData 
{
    
}


