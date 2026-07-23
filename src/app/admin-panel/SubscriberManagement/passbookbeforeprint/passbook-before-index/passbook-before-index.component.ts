import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import  html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-passbook-before-index',
  templateUrl: './passbook-before-index.component.html',
  styles: []
})
export class PassbookBeforeIndexComponent implements OnInit {
  enrollmentId: any;
  customerName: any;
  branchName: any;
  dateOfIssue: any;
  groupName: any;
  psoNo: any;
  ticketNo: any;
  byLawNo: any;
  chitValue: any;
  introducerName: any;
  monthlyInstallment: any;
  auctionDate: any;
  auctionTime: any;
  contactNo: any;
  address: any;
  NoMonths: any;
  CommencementDate: any;
  TerminationDate: any;

  constructor(private appService: AppService, private userservice: UserService, private route: ActivatedRoute, private router: Router) 
  { 
    
  }

  public generatePdf()  
  {   let printContents, popupWin;
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

  ngOnInit() {
    this.route.params.subscribe(params => {
      this.enrollmentId = params.enrollmentId;
      let cond = {
        "conditions[id]" : this.enrollmentId 
      }
      this.userservice.getData(cond, "get-customers-view","POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') 
          {
            this.customerName = response.data[0].full_name;
            this.branchName = response.data[0].branch_name;
            this.dateOfIssue = response.date;
            this.groupName = '-';
            this.psoNo = '-';
            this.ticketNo = '-';
            this.NoMonths = '-';
            this.CommencementDate = '-';
            this.TerminationDate = '-';
            this.byLawNo = '-';
            this.chitValue = '-';
            this.introducerName = '-';
            this.monthlyInstallment = '-';
            this.auctionDate = '-';
            this.auctionTime = '-';
            this.contactNo = response.data[0].mobile_no;
            this.address = response.data[0].address_line_1 + ', ' + response.data[0].address_line_2;
          }
          else {
            this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => { 
        if (err.status == 401) {
          this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
