import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-auction',
  templateUrl: './update-auction.component.html',
  styles: []
})

export class UpdateAuctionComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('installment_no') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;
  @ViewChild('auction_type') focus6:ElementRef;
  @ViewChild('chit_value') focus7:ElementRef;
  @ViewChild('bidding_amount') focus8:ElementRef;
  @ViewChild('commission_amount') focus9:ElementRef;
  @ViewChild('divident_amount') focus10:ElementRef;
  @ViewChild('installment_amount') focus11:ElementRef;
  @ViewChild('auction_date') focus12:ElementRef;
  @ViewChild('next_auction_date') focus13:ElementRef;
  @ViewChild('total_release_amount') focus14:ElementRef;
  @ViewChild('profit_amount') focus15:ElementRef;
  @ViewChild('remark') focus16:ElementRef;

  UpdateAuction: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  biddingInfo: any[];
  enrollmentList: any[];
  biddingReadonly: boolean;
  profit: boolean;
  slabNonRegister: boolean;
  nonRegister: boolean;
  showAuctionType: boolean;
  auctionId: any;
  commissionPercentage: any;
  totalDividend: any;
  bidAdvanceAmount: any;
  biddingAlert: any;
  noOfMembers: any;
  monthlyDue: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  currentDate:any;
  auctionDateAlertMsg: any;
  
  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {   
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
    this.currentDate = {
      year: today_date.getFullYear(),
      month: today_date.getMonth()+1,
      day: today_date.getDate()
    };
  }

  get f() {
    return this.UpdateAuction.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.UpdateAuction.get('branch_id').setValue('');
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setGroupList(branch_id);
    this.showAuctionType = false;
    this.profit = false;
    this.biddingReadonly = false;
    this.nonRegister = false;
    this.slabNonRegister = false;
    this.UpdateAuction.get('group_id').setValue('');
  }

  setGroupList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id,
      "conditions[is_filled]" : 0
    }
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

  groupChg(event) {
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == event.target.value);
    this.setEnrollmentList(event.target.value);
    this.setBiddingDetail(event.target.value);
    let grpType = selectedGrp[0].group_type;
    if(grpType==1) {
      this.showAuctionType = false;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==2) { 
      this.showAuctionType = true;
      this.nonRegister = true;
      this.biddingReadonly = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==3) {
      this.showAuctionType = false;
      this.biddingReadonly = true;
      this.nonRegister = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==4) {
      this.showAuctionType = true;
      this.slabNonRegister = true;
      this.biddingReadonly = true;
      this.nonRegister = false;
      this.profit = false;
    }
    else if(grpType==5) {
      this.showAuctionType = true;
      this.profit = true;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
    }
    else {
      this.showAuctionType = false;
      this.profit = false;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
    }
  }

  setBiddingDetail(groupId) {
    let cond = {
      "conditions[group_id]" : groupId
    }
    this.userservice.getData(cond, "get-bidding-group-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.biddingInfo = response.data; 
          this.UpdateAuction.get('installment_no').setValue(response.data.auction_no);
          this.UpdateAuction.get('chit_value').setValue(response.data.scheme_info.chit_value);
          this.UpdateAuction.get('bidding_amount').setValue(response.data.bidding_amount);
          this.commissionPercentage = response.data.commission_percent;
          this.UpdateAuction.get('commission_amount').setValue(response.data.commission_amount);
          this.UpdateAuction.get('divident_amount').setValue(response.data.dividend);
          this.UpdateAuction.get('installment_amount').setValue(response.data.installment_amount);
          this.totalDividend = response.data.total_dividend;
          if(response.data.next_auction_date!='') {
            let nextAuctionDate = (response.data.next_auction_date).split('/');
            let setNextAuctionDate = {
              'year': parseInt(nextAuctionDate[2]),
              'month': parseInt(nextAuctionDate[1]),
              'day': parseInt(nextAuctionDate[0])
            }
            this.UpdateAuction.get('next_auction_date').setValue(setNextAuctionDate);
          }
          this.UpdateAuction.get('total_release_amount').setValue(response.data.total_release_amount);
        } else {
          this.biddingInfo = [];
          this.UpdateAuction.get('installment_no').setValue('');
          this.UpdateAuction.get('chit_value').setValue(0);
          this.UpdateAuction.get('bidding_amount').setValue(0);
          this.commissionPercentage = 0;
          this.UpdateAuction.get('commission_amount').setValue(0);
          this.UpdateAuction.get('divident_amount').setValue(0);
          this.UpdateAuction.get('installment_amount').setValue(0);
          this.totalDividend = 0;
          this.UpdateAuction.get('next_auction_date').setValue(null);
          this.UpdateAuction.get('total_release_amount').setValue(0);
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  customerChg(event) {
    let cond = {
      "conditions[enrollment_id]" : event.target.value, 
    }
    this.userservice.getData(cond, "bidding-advance-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.bidAdvanceAmount = response.data.bidding_advance; 
        } else {
          this.bidAdvanceAmount = 0;
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  biddingChg(event) {
    let grpId = this.UpdateAuction.get('group_id').value;
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == grpId);
    let grpType = selectedGrp[0].group_type; 
    if((grpType==1) || (grpType==2) ||(grpType==5)) {
      let biddingAmt = event.target.value;;
      let commissionAmt = this.UpdateAuction.get('commission_amount').value;
      if(biddingAmt < commissionAmt) {
        this.biddingAlert = true;
      }
      else {
        this.biddingAlert = false;
        this.totalDividend = parseInt(biddingAmt) - parseInt(commissionAmt);
        let eachMemberDividentAmt = parseInt(this.totalDividend)/parseInt(this.noOfMembers);
        this.UpdateAuction.get('divident_amount').setValue(eachMemberDividentAmt);
        let monthlyInstallment = parseInt(this.monthlyDue) - eachMemberDividentAmt;
        this.UpdateAuction.get('installment_amount').setValue(monthlyInstallment);
        let customerReleaseAmt = parseInt(this.UpdateAuction.get('chit_value').value) - parseInt(biddingAmt);
        this.UpdateAuction.get('total_release_amount').setValue(customerReleaseAmt);
      }
    }
  }

  setEnrollmentList(groupId) {
    let branchId = this.UpdateAuction.get('branch_id').value;
    let cond = {
      "conditions[branch_id]" : branchId, 
      "conditions[group_id]" : groupId,
      "for_edit" : this.auctionId,
    }
    this.userservice.getData(cond, "get-auctionable-enrolled-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollmentList = response.data; 
        } else {
          this.enrollmentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }

  setAuctionType(groupId) {
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == groupId);
    let grpType = selectedGrp[0].group_type;
    if(grpType==1) {
      this.showAuctionType = false;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==2) { 
      this.showAuctionType = true;
      this.nonRegister = true;
      this.biddingReadonly = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==3) {
      this.showAuctionType = false;
      this.biddingReadonly = true;
      this.nonRegister = false;
      this.slabNonRegister = false;
      this.profit = false;
    }
    else if(grpType==4) {
      this.showAuctionType = true;
      this.slabNonRegister = true;
      this.biddingReadonly = true;
      this.nonRegister = false;
      this.profit = false;
    }
    else if(grpType==5) {
      this.showAuctionType = true;
      this.profit = true;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
    }
    else {
      this.showAuctionType = false;
      this.profit = false;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
    }
    
    if((grpType==1) || (grpType==2) ||(grpType==5)) {
      let biddingAmt = this.UpdateAuction.get('bidding_amount').value;
      let commissionAmt = this.UpdateAuction.get('commission_amount').value;
      if(biddingAmt < commissionAmt) {
        this.biddingAlert = true;
      }
      else {
        this.biddingAlert = false;
        this.totalDividend = parseInt(biddingAmt) - parseInt(commissionAmt);
        let eachMemberDividentAmt = parseInt(this.totalDividend)/parseInt(this.noOfMembers);
        this.UpdateAuction.get('divident_amount').setValue(eachMemberDividentAmt);
        let monthlyInstallment = parseInt(this.monthlyDue) - eachMemberDividentAmt;
        this.UpdateAuction.get('installment_amount').setValue(monthlyInstallment);
        let customerReleaseAmt = parseInt(this.UpdateAuction.get('chit_value').value) - parseInt(biddingAmt);
        this.UpdateAuction.get('total_release_amount').setValue(customerReleaseAmt);
      }
    }
  }

  auctionDateChg()
  { 
    let adate=this.UpdateAuction.get('auction_date').value;
   // console.log("adate",adate);
    this.checkForAuctionDate(adate,'auctionDate');
    //console.log("this.auctionDateAlertMsg",this.auctionDateAlertMsg)
  }

  checkForAuctionDate(auctionDate,alertField) {
    let cond = {
      "auction_date" : auctionDate, 
      "tenant_id" : this.UpdateAuction.get('tenant_id').value, 
    }
    this.userservice.getData(cond, "auction-day-details", "JSON").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
         // return response.msg;
          if(response.msg!='')
          { 
            if(alertField=='auctionDate') {
              this.auctionDateAlertMsg = response.msg; 
            }
          } 
          else{
            if(alertField=='auctionDate') {
              this.auctionDateAlertMsg = ''; 
            }
          }
        } else {
         // return '';
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateAuction.value); 
    if (this.UpdateAuction.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.installment_no.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.enrollment_id.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.auction_type.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.chit_value.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.bidding_amount.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.commission_amount.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.divident_amount.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.installment_amount.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.auction_date.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.next_auction_date.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.total_release_amount.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.profit_amount.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.remark.errors) {
        this.focus16.nativeElement.focus();
      }
      return;
    }
    
    this.userservice.getData(result, "update-auction", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Auction",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-auction']);
          }, 300);
        } else {
          this.appService.showAlert("Auction", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Auction", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateAuction = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
      installment_no: ['', Validators.required],
      enrollment_id: ['', [Validators.required]],
      customer_id: ['', [Validators.required]],
      auction_type: [''],
      chit_value: ['', [Validators.required]],
      bidding_amount: ['', [Validators.required]],
      commission_amount: ['', [Validators.pattern(this.decimalPattern)]],
      divident_amount: ['', [Validators.pattern(this.decimalPattern)]],
      installment_amount: ['', [Validators.pattern(this.decimalPattern)]],
      next_auction_date: [null, [Validators.required]],
      auction_date: [this.currentDate, [Validators.required]],
      total_release_amount: ['', [Validators.pattern(this.decimalPattern)]],
      profit_amount: ['', [Validators.pattern(this.decimalPattern)]],
      remark: [''],
      updated_by: [''],
    });
    this.biddingReadonly = false;
    this.profit = false;
    this.slabNonRegister = false;
    this.nonRegister = false;
    this.showAuctionType = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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

    this.route.params.subscribe(params => {
      this.auctionId = params.id;
      this.UpdateAuction.get('id').setValue(this.auctionId);
      this.UpdateAuction.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.auctionId 
      }
      this.userservice.getData(cond, "get-auctions","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateAuction.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateAuction.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateAuction.get('group_id').setValue(response.data[0].group_id);
            this.UpdateAuction.get('installment_no').setValue(response.data[0].installment_no);
            this.UpdateAuction.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdateAuction.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateAuction.get('auction_type').setValue(response.data[0].auction_type);
            this.UpdateAuction.get('chit_value').setValue(response.data[0].chit_value);
            this.UpdateAuction.get('bidding_amount').setValue(response.data[0].bidding_amount);
            this.UpdateAuction.get('commission_amount').setValue(response.data[0].commission_amount);
            this.UpdateAuction.get('divident_amount').setValue(response.data[0].divident_amount);
            this.UpdateAuction.get('installment_amount').setValue(response.data[0].installment_amount);
            if(response.data[0].next_auction_date!='') {
              let nextAuctionDate = (response.data[0].next_auction_date).split('/');
              let setNextAuctionDate = {
                'year': parseInt(nextAuctionDate[2]),
                'month': parseInt(nextAuctionDate[1]),
                'day': parseInt(nextAuctionDate[0])
              }
              this.UpdateAuction.get('next_auction_date').setValue(setNextAuctionDate);
            }
            if(response.data[0].auction_date!='') {
              let auctionDate = (response.data[0].auction_date).split('/');
              let setAuctionDate = {
                'year': parseInt(auctionDate[2]),
                'month': parseInt(auctionDate[1]),
                'day': parseInt(auctionDate[0])
              }
              this.UpdateAuction.get('auction_date').setValue(setAuctionDate);
            }
            this.UpdateAuction.get('total_release_amount').setValue(response.data[0].total_release_amount);
            this.UpdateAuction.get('profit_amount').setValue(response.data[0].profit_amount);
            this.UpdateAuction.get('remark').setValue(response.data[0].remark);
            this.setBranchList(response.data[0].tenant_id);
            this.setGroupList(response.data[0].branch_id);
            this.setEnrollmentList(response.data[0].group_id);
            let cond = {
              "conditions[group_id]" : response.data[0].group_id
            }
            this.userservice.getData(cond, "get-bidding-group-details", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') { 
                  this.biddingInfo = response.data; 
                  this.commissionPercentage = response.data.commission_percent;
                  this.totalDividend = response.data.total_dividend;
                  this.monthlyDue = response.data.scheme_info.monthly_due; 
                  this.noOfMembers = response.data.scheme_info.members; 
                } 
                else {
                  this.biddingInfo = [];
                  this.commissionPercentage = 0;
                  this.totalDividend = 0;
                  this.monthlyDue = 0;
                  this.noOfMembers = 0;
                }
              }  catch (err) { }
            }, (err: any) => { });
            this.auctionDateChg();
          }
          else {
            this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Auction", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
