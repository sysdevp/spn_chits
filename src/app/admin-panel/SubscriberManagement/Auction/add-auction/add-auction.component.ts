import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-auction',
  templateUrl: './add-auction.component.html',
  styles: []
})

export class AddAuctionComponent implements OnInit {
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

  AddAuction: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  enrollmentList: any[];
  biddingInfo: any[];
  biddingReadonly: boolean;
  profit: boolean;
  slabNonRegister: boolean;
  nonRegister: boolean;
  showAuctionType: boolean;
  hideProfit: boolean;
  commissionPercentage: any;
  totalDividend: any;
  bidAdvanceAmount: any;
  monthlyDue: any;
  noOfMembers: any;
  biddingAlert: boolean;
  auctionalert:boolean;
  decimalPattern = "^[0-9]+(.[0-9]{0,2})?$";
  groupName: any;
  currentDate:any;
  auctionDateAlertMsg: any;
  auctionTypeList: any[];
  hideLoanDetail:boolean=true;
  
   status_values: any ;
  chitValue: void;
  ProfitAmt: any;
  ChitValue: any;
  auctionType: any;
  chitamt: any;
  LoanList: any[];
  group_id: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
    return this.AddAuction.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.AddAuction.get('branch_id').setValue('');
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1,
      "log_id" : localStorage.getItem('logged_employee_id')
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
    this.AddAuction.get('group_id').setValue('');
    this.biddingInfo = [];
    this.AddAuction.get('installment_no').setValue('');
    this.AddAuction.get('chit_value').setValue('');
    this.AddAuction.get('bidding_amount').setValue('');
    this.commissionPercentage = '';
    this.AddAuction.get('commission_amount').setValue('');
    this.AddAuction.get('divident_amount').setValue('');
    this.AddAuction.get('installment_amount').setValue('');
    this.totalDividend = '';
    this.AddAuction.get('next_auction_date').setValue(null);
    this.AddAuction.get('total_release_amount').setValue('');
    this.monthlyDue = '';
    this.noOfMembers = '';
    this.enrollmentList = [];
    this.AddAuction.get('enrollment_id').setValue('');
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
    this.group_id = event.target.value;
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == event.target.value);
    this.setEnrollmentList(event.target.value);
    let grpType = 0;
    if(selectedGrp) {
      grpType = selectedGrp[0].group_type; 
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
    else {
      this.showAuctionType = false;
      this.profit = false;
      this.biddingReadonly = false;
      this.nonRegister = false;
      this.slabNonRegister = false;
    }
    //this.setBiddingDetail(event.target.value,grpType);
  
  }

  setBiddingDetail(groupId,grpType) {
    
    let cond = {
      "conditions[group_id]" : groupId
    }
    this.userservice.getData(cond, "get-bidding-group-details", "POST").subscribe((response: any) => {
      try { 
        this.biddingInfo = [];
        this.AddAuction.get('installment_no').setValue('');
        this.AddAuction.get('chit_value').setValue(0);
        this.AddAuction.get('bidding_amount').setValue(0);
        this.commissionPercentage = 0;
        this.AddAuction.get('commission_amount').setValue(0);
        this.AddAuction.get('divident_amount').setValue(0);
        this.AddAuction.get('installment_amount').setValue(0);
        this.totalDividend = 0;
        this.AddAuction.get('next_auction_date').setValue(null);
        this.AddAuction.get('total_release_amount').setValue(0);
        this.monthlyDue = 0;
        this.noOfMembers = 0;
        if (response.status == 'Success') { 
          this.biddingInfo = response.data; 
          this.AddAuction.get('installment_no').setValue(response.data.auction_no);
          this.AddAuction.get('chit_value').setValue(response.data.scheme_info.chit_value);
          //this.chitValue= this.AddAuction.get('chit_value');
          //this.ChitValue=  this.AddAuction.get('chit_value');
          this.AddAuction.get('bidding_amount').setValue(response.data.bidding_amount);
          this.commissionPercentage = response.data.commission_percent;
          this.AddAuction.get('commission_amount').setValue(response.data.commission_amount);
          this.AddAuction.get('divident_amount').setValue(response.data.dividend);
          this.AddAuction.get('installment_amount').setValue(response.data.installment_amount);
          this.AddAuction.get('profit_amount').setValue(response.data.profit_amount);
          this.ProfitAmt=  this.AddAuction.get('profit_amount');
          if((response.data.profit_amount)>=(response.data.scheme_info.chit_value)) {
            this.hideProfit = false;
          }
          else {
            this.hideProfit = true;
          }
          this.totalDividend = response.data.total_dividend;
          let nextAuctionDate = (response.data.next_auction_date).split('/');
          let setNextAuctionDate = {
            'year': parseInt(nextAuctionDate[2]),
            'month': parseInt(nextAuctionDate[1]),
            'day': parseInt(nextAuctionDate[0])
          }
          this.AddAuction.get('next_auction_date').setValue(setNextAuctionDate);
          this.AddAuction.get('total_release_amount').setValue(response.data.total_release_amount);
          this.monthlyDue = response.data.scheme_info.monthly_due; 
          this.noOfMembers = response.data.scheme_info.members; 
          this.groupName = response.data.group_type_name; 
        } 
      }  catch (err) { }
    }, (err: any) => { });
   
  }

  biddingChg(event) {
    let grpId = this.AddAuction.get('group_id').value;
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == grpId);
    let grpType = selectedGrp[0].group_type; 
    if((grpType==1) || (grpType==2) ||(grpType==5) || (grpType==6)) {
      let biddingAmt = event.target.value;;
      let commissionAmt = this.AddAuction.get('commission_amount').value;
      if(biddingAmt < commissionAmt) {
        this.biddingAlert = false;
		commissionAmt = 0;
        this.totalDividend = parseInt(biddingAmt) - parseInt(commissionAmt);
        let eachMemberDividentAmt = parseInt(this.totalDividend)/parseInt(this.noOfMembers);
        this.AddAuction.get('divident_amount').setValue(eachMemberDividentAmt.toFixed(0));
        let monthlyInstallment = parseInt(this.monthlyDue) - eachMemberDividentAmt;
        this.AddAuction.get('installment_amount').setValue(monthlyInstallment.toFixed(0));
        let customerReleaseAmt = parseInt(this.AddAuction.get('chit_value').value) - parseInt(biddingAmt);
        this.AddAuction.get('total_release_amount').setValue(customerReleaseAmt.toFixed(0));
        this.chitamt=this.AddAuction.get('chit_value').value;
      }
      else {
        this.biddingAlert = false;
        this.totalDividend = parseInt(biddingAmt) - parseInt(commissionAmt);
        let eachMemberDividentAmt = parseInt(this.totalDividend)/parseInt(this.noOfMembers);
        this.AddAuction.get('divident_amount').setValue(eachMemberDividentAmt.toFixed(0));
        let monthlyInstallment = parseInt(this.monthlyDue) - eachMemberDividentAmt;
        this.AddAuction.get('installment_amount').setValue(monthlyInstallment.toFixed(0));
        let customerReleaseAmt = parseInt(this.AddAuction.get('chit_value').value) - parseInt(biddingAmt);
        this.AddAuction.get('total_release_amount').setValue(customerReleaseAmt.toFixed(0));
        this.chitamt=this.AddAuction.get('chit_value').value;
      }
      if(biddingAmt <this.chitamt) {
        this.auctionalert = false;
      }
      else {
        this.auctionalert = true;
      }
    }
  }

  auctionDateChg()
  { 
    let adate=this.AddAuction.get('auction_date').value;
    let cond = {
      "auction_date" : adate, 
      "tenant_id" : this.AddAuction.get('tenant_id').value, 
    }
    this.userservice.getData(cond, "auction-day-details", "JSON").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          if(response.msg!='')
          {
            this.auctionDateAlertMsg = response.msg; 
          } 
          else{
            this.auctionDateAlertMsg = '';
          }
        } else {
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  customerChg(event) {
    this.LoanList = [];
    this.hideLoanDetail=false;
    let selectedCustomer =  this.enrollmentList.filter((listing: any) => listing.id == event.target.value);
    this.AddAuction.get('customer_id').setValue(selectedCustomer[0].customer_id);
    console.log("customer",selectedCustomer[0].customer_id);
    let Customer = selectedCustomer[0].customer_id;
    let tenant_id = this.AddAuction.get('tenant_id').value;
    let cond1 = {
      "tenant_id" : tenant_id,
      "customer_id" : Customer, 
    }
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

    this.userservice.getData(cond1, "alert-loan-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.LoanList = response.data;
        } else {
          this.LoanList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
    let auction_type = this.AddAuction.get('auction_type').value;
    let cond2 = {
      "enrollment_id" : event.target.value,
      "group_id" : this.group_id, 
      "auction_type" : auction_type,
    }
    this.userservice.getData(cond2, "get-bidding-group-details", "POST").subscribe((response: any) => {
      try { 
        this.biddingInfo = [];
        this.AddAuction.get('installment_no').setValue('');
        this.AddAuction.get('chit_value').setValue(0);
        this.AddAuction.get('bidding_amount').setValue(0);
        this.commissionPercentage = 0;
        this.AddAuction.get('commission_amount').setValue(0);
        this.AddAuction.get('divident_amount').setValue(0);
        this.AddAuction.get('installment_amount').setValue(0);
        this.totalDividend = 0;
        this.AddAuction.get('next_auction_date').setValue(null);
        this.AddAuction.get('total_release_amount').setValue(0);
        this.monthlyDue = 0;
        this.noOfMembers = 0;
        if (response.status == 'Success') { 
          this.biddingInfo = response.data; 
          this.AddAuction.get('installment_no').setValue(response.data.auction_no);
          this.AddAuction.get('chit_value').setValue(response.data.chit_value);
          //this.chitValue= this.AddAuction.get('chit_value');
          //this.ChitValue=  this.AddAuction.get('chit_value');
          this.AddAuction.get('bidding_amount').setValue(response.data.bidding_amount);
          this.commissionPercentage = response.data.commission_percent;
          this.AddAuction.get('commission_amount').setValue(response.data.commission_amount);
          this.AddAuction.get('divident_amount').setValue(response.data.dividend);
          this.AddAuction.get('installment_amount').setValue(response.data.installment_amount);
          this.AddAuction.get('profit_amount').setValue(response.data.profit_amount);
          this.ProfitAmt=  this.AddAuction.get('profit_amount');
          if((response.data.profit_amount)>=(response.data.scheme_info.chit_value)) {
            this.hideProfit = false;
          }
          else {
            this.hideProfit = true;
          }
          this.totalDividend = response.data.total_dividend;
          let nextAuctionDate = (response.data.next_auction_date).split('/');
          let setNextAuctionDate = {
            'year': parseInt(nextAuctionDate[2]),
            'month': parseInt(nextAuctionDate[1]),
            'day': parseInt(nextAuctionDate[0])
          }
          this.AddAuction.get('next_auction_date').setValue(setNextAuctionDate);
          this.AddAuction.get('total_release_amount').setValue(response.data.total_release_amount);
          this.monthlyDue = response.data.monthly_due; 
          this.noOfMembers = response.data.scheme_info.members; 
          this.groupName = response.data.group_type_name; 
        } 
      }  catch (err) { }
    }, (err: any) => { });
    
  }

  gst_init_Chg(event)
  { 
    
  }

  setEnrollmentList(groupId) {
    let branchId = this.AddAuction.get('branch_id').value;
    let cond = {
      "conditions[branch_id]" : branchId, 
      "conditions[group_id]" : groupId
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

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddAuction.value);
    if (this.AddAuction.invalid) 
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

    this.userservice.getData(result, "store-auction", "JSON").subscribe((response: any) => {
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
    this.AddAuction = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
      installment_no: ['', Validators.required],
      group_type_name: [''],
      enrollment_id: ['', [Validators.required]],
      customer_id: ['', [Validators.required]],
      auction_type: [''],
      chit_value: ['', [Validators.required]],
      bidding_amount: ['', [Validators.required]],
      commission_amount: ['', [Validators.pattern(this.decimalPattern)]],
      divident_amount: ['', [Validators.pattern(this.decimalPattern)]],
      installment_amount: ['', [Validators.pattern(this.decimalPattern)]],
      auction_date: [this.currentDate, [Validators.required]],
      next_auction_date: [null, [Validators.required]],
      total_release_amount: ['', [Validators.pattern(this.decimalPattern)]],
      profit_amount: ['', [Validators.pattern(this.decimalPattern)]],
      remark: [''],
      created_by: [''],
    });
    this.biddingReadonly = false;
    this.profit = false;
    this.slabNonRegister = false;
    this.nonRegister = false;
    this.showAuctionType = false;
    this.biddingAlert = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddAuction.get('tenant_id').setValue(tenant_id);
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
        }  catch (err) { }
      }, (err: any) => { });
    }
    this.AddAuction.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.auctionDateChg();
  }
}
