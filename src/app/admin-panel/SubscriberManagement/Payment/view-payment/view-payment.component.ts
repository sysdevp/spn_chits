import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-view-payment',
  templateUrl: './view-payment.component.html',
  styles: []
})

export class ViewPaymentComponent implements OnInit {
  auctionId: any;
  paymentList: any[];
  adjustpaymentList: any[];
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
  
  constructor(private route: ActivatedRoute, private modalService: NgbModal, private appService: AppService, private userservice: UserService, private spinner: NgxSpinnerService) {
  
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
      this.auctionId = params.auctionId;
    });
    let cond = {
      "conditions[bidding_id]" : this.auctionId
    }
    this.userservice.getData(cond, "view-payment", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.companyName = response.data.company_name;
          this.branchName = response.data.branch_name;
          this.groupName = response.data.group_name;
          this.ticketNo = response.data.ticket_no;
          this.customerName = response.data.customer_name_det;
          this.chitValue = response.data.chit_value;
          this.biddingAmount = response.data.bidding_amount;
          this.commissionAmount = response.data.commission_amount;
          this.bidAdvanceAmount = response.data.bid_advance_amount;
          this.cgst = response.data.cgst;
          this.sgst = response.data.sgst;
          this.gst = response.data.gst;
          this.bidPaymentAdjustment = response.data.bid_payment_adjustment;
          this.totalReleaseAmount = response.data.total_release_amount;
          this.customerReleasedAmount = response.data.customer_released_amount;
          this.paymentList = response.data.payments;
          this.adjustpaymentList = response.adjustpayments;
        }
        else {
          this.appService.showAlert("Payments", "Sorry!! Something went wrong!", "error");
        }
      }
      catch (err) {}
    });
  } 
}
