import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import  jspdf from 'jspdf';  
import  html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-passbook-transaction-index',
  templateUrl: './passbook-transaction-index.component.html',
  styles: []
})
export class PassbookTransactionIndexComponent implements OnInit {
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
  transactionList: any;

  constructor(private appService: AppService, private userservice: UserService, private route: ActivatedRoute, private router: Router) 
  { 
    
  }

  public generatePdf()  
  {  let printContents, popupWin;
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
      this.userservice.getData(cond, "get-customer-enrollments","POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') 
          {
            this.customerName = response.data[0].customer_info.name;
            this.branchName = response.data[0].branch_info.branch_name;
            
            this.dateOfIssue = response.date;
            this.groupName = response.data[0].group_info.group_name;
            this.psoNo = response.data[0].group_info.pso_number;
            this.ticketNo = response.data[0].ticket_no;
            this.NoMonths = response.data[0].no_months;
            this.CommencementDate = response.data[0].commencement_date;
            this.TerminationDate = response.data[0].termination_date;
            this.byLawNo = response.data[0].group_info.blaw_number;
            this.chitValue = response.data[0].scheme_info.chit_value;
            this.introducerName = response.data[0].introducer_full_name;
            this.monthlyInstallment = response.data[0].scheme_info.monthly_due;
            this.auctionDate = response.data[0].group_info.auction_date;
            this.auctionTime = response.data[0].group_info.auction_time;
            this.contactNo = response.data[0].customer_info.mobile_no;
            this.address = response.data[0].customer_info.address_line_1 + ', ' + response.data[0].customer_info.address_line_2;
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

      let cond1 = {
        "conditions[enrollment_id]" : this.enrollmentId 
      }
      this.userservice.getData(cond1, "customer-ledger-group-advance-detail","POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') 
          {
            this.transactionList = response.data;
          }
          else {
            this.transactionList = [];
            //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => { 
        if (err.status == 401) {
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
        else {
          //this.appService.showAlert("Passbook Print", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
