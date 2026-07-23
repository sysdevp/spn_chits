import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-payment',
  templateUrl: './add-payment.component.html',
  styles: []
})

export class AddPaymentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('total_release_amount') focus5:ElementRef;
  @ViewChild('given_amount') focus6:ElementRef;
  @ViewChild('bid_advance_amount') focus7:ElementRef;
  @ViewChild('gst') focus8:ElementRef;
  @ViewChild('cgst') focus9:ElementRef;
  @ViewChild('sgst') focus10:ElementRef;
  @ViewChild('payment_date') focus11:ElementRef;
  @ViewChild('pending_release') focus12:ElementRef;
  @ViewChild('payment_type_id') focus13:ElementRef;
  @ViewChild('debit_to') focus14:ElementRef;
  @ViewChild('cheque_no') focus15:ElementRef;
  @ViewChild('cheque_date') focus16:ElementRef;
  @ViewChild('bank_id') focus17:ElementRef;
  @ViewChild('bank_branch_name') focus18:ElementRef;
  @ViewChild('transaction_no') focus19:ElementRef;
  @ViewChild('transaction_date') focus20:ElementRef;
  @ViewChild('remark') focus21:ElementRef;
  @ViewChild('customer_released') focus22:ElementRef;
  @ViewChild('bank_name_id') focus23:ElementRef;
  

  AddPayment: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  branchName: any;
  groupName: any;
  customerName: any;
  chitValue: any;
  biddingAmt: any;
  commissionAmt: any;
  totalReleaseAmt: any;
  hidePendingdueamount: boolean=true;
  ticketName: any;
  paidAmt: any;
  bidAdvanceAmt: any;
  actualReleaseAmt: any;
  releaseAlertMsg: boolean;
  hideDebitTo: boolean=true;
  hideChequeNo: boolean=true;
  hideChequeDate: boolean=true;
  hideBankName: boolean=true;
  hideBranchName: boolean=true;
  hideTransactionDate: boolean=true;
  hideTransactionNo: boolean=true;
  employeeList: any[];
  bankList: any;
  auctionId: any;
  
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddPayment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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

  pendingReleaseCal() {
    let releaseAmt = this.AddPayment.get('customer_released').value;
    let actualRealease = this.actualReleaseAmt;
    if((parseInt(actualRealease)) < (parseInt(releaseAmt))) {
      this.releaseAlertMsg = true;
      this.AddPayment.get('customer_released').setValue('');
    }
    else {
      this.releaseAlertMsg = false;
      let pendingRelease = (parseInt(actualRealease)) - (parseInt(releaseAmt));
      this.AddPayment.get('pending_release').setValue(pendingRelease);
    }
  }

  bankChg(event) {
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddPayment.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  paymentChg(event) { 
    let paymentType = event.target.value;
    //cash - 1
    let debitTo = this.AddPayment.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.AddPayment.get('cheque_no');
    let chequeDate = this.AddPayment.get('cheque_date');
    let bankId = this.AddPayment.get('bank_id');
    let bankBranchName = this.AddPayment.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.AddPayment.get('transaction_no');
    let transactionDate = this.AddPayment.get('transaction_date');
    if(paymentType=='1') {
      debitTo.setValidators([Validators.required]);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if((paymentType=='2') || (paymentType=='3')) {
      debitTo.setValidators(null);
      chequeNo.setValidators([Validators.required]);
      chequeDate.setValidators([Validators.required]);
      bankId.setValidators([Validators.required]);
      bankBranchName.setValidators([Validators.required]);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if(paymentType=='4') {
      debitTo.setValidators(null);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators([Validators.required]);
      transactionDate.setValidators([Validators.required]);
    }
    debitTo.updateValueAndValidity();
    chequeNo.updateValueAndValidity();
    chequeDate.updateValueAndValidity();
    bankId.updateValueAndValidity();
    bankBranchName.updateValueAndValidity();
    transactionNo.updateValueAndValidity();
    transactionDate.updateValueAndValidity();
    if(paymentType == 1) {
      this.hideDebitTo=false;
      this.hideChequeNo=true;
      this.hideChequeDate=true;
      this.hideBankName=true;
      this.hideBranchName=true;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else if(paymentType == 2) {
      this.hideDebitTo=true;
      this.hideChequeNo=false;
      this.hideChequeDate=false;
      this.hideBankName=false;
      this.hideBranchName=false;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else if(paymentType == 3) {
      this.hideDebitTo=true;
      this.hideChequeNo=false;
      this.hideChequeDate=false;
      this.hideBankName=false;
      this.hideBranchName=false;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else if(paymentType == 4) {
      this.hideDebitTo=true;
      this.hideChequeNo=true;
      this.hideChequeDate=true;
      this.hideBankName=true;
      this.hideBranchName=true;
      this.hideTransactionDate=false;
      this.hideTransactionNo=false;
    }
    else{
      this.hideDebitTo=true;
      this.hideChequeNo=true;
      this.hideChequeDate=true;
      this.hideBankName=true;
      this.hideBranchName=true;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPayment.value);
    if (this.AddPayment.invalid) 
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
      else if(this.f.customer_id.errors) { 
        this.focus4.nativeElement.focus();
      }
      else if(this.f.total_release_amount.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.given_amount.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.bid_advance_amount.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.gst.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.cgst.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.sgst.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.payment_date.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.pending_release.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.debit_to.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.bank_id.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.remark.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.customer_released.errors) {
        this.focus22.nativeElement.focus();
      }else if(this.f.bank_name_id.errors) {
        this.focus23.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Payment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-auction']);
          }, 300);
        } else {
          this.appService.showAlert("Payment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Payment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddPayment = this.formBuilder.group({
      tenant_id: [''],
      branch_id: [''],
      group_id: [''],
      bidding_id: [''],
      customer_id: [''],
      enrollment_id: [''],
      employee_id: ['', [Validators.required]],
      total_release_amount: [''],
      given_amount: [''],
      bid_advance_amount: [''],
      gst: [''],
      cgst: [''], 
      sgst: [''], 
      payment_date: [null, [Validators.required]], 
      customer_released: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      pending_release: [''],
      payment_type_id: ['', [Validators.required]],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      remark: [''],
      created_by: [''],
    });
    this.releaseAlertMsg = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddPayment.get('tenant_id').setValue(tenant_id);
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
    this.AddPayment.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData('', "list-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData('', "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.route.params.subscribe(params => {
      this.auctionId = params.auctionId;
      let cond = {
        "conditions[id]" : this.auctionId 
      }
      this.userservice.getData(cond, "get-auctions","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.AddPayment.get('tenant_id').setValue(response.data[0].tenant_id);
            this.branchName=response.data[0].branch_det.branch_name;
            this.AddPayment.get('branch_id').setValue(response.data[0].branch_id);
            this.groupName=response.data[0].group_info.group_name;
            this.AddPayment.get('group_id').setValue(response.data[0].group_id);
            this.AddPayment.get('bidding_id').setValue(response.data[0].id);
            this.biddingAmt=response.data[0].bidding_amount;
            this.commissionAmt=response.data[0].commission_amount;
            this.ticketName=response.data[0].enrollment_info.ticket_no;
            this.AddPayment.get('customer_id').setValue(response.data[0].customer_id);
            this.customerName=response.data[0].customer_name_det;
            this.AddPayment.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.chitValue=response.data[0].chit_value;
            this.totalReleaseAmt=response.data[0].total_release_amount;
            this.paidAmt=response.data[0].paid_amount;
            this.bidAdvanceAmt=response.data[0].bid_advance_amount;
            this.AddPayment.get('cgst').setValue(response.data[0].cgst_amount);
            this.AddPayment.get('sgst').setValue(response.data[0].sgst_amount);
            this.AddPayment.get('gst').setValue(response.data[0].gst_amount);
            this.actualReleaseAmt=response.data[0].actual_release_Amount;
          }
          else {
            this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
