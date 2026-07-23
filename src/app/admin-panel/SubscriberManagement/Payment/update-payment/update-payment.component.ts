import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router,ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-payment',
  templateUrl: './update-payment.component.html',
  styles: []
})

export class UpdatePaymentComponent implements OnInit {
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
  @ViewChild('pending_due_amount') focus12:ElementRef;
  @ViewChild('pending_release') focus13:ElementRef;
  @ViewChild('payment_type_id') focus14:ElementRef;
  @ViewChild('debit_to') focus15:ElementRef;
  @ViewChild('cheque_no') focus16:ElementRef;
  @ViewChild('cheque_date') focus17:ElementRef;
  @ViewChild('bank_id') focus18:ElementRef;
  @ViewChild('bank_branch_name') focus19:ElementRef;
  @ViewChild('transaction_no') focus20:ElementRef;
  @ViewChild('transaction_date') focus21:ElementRef;
  @ViewChild('remark') focus22:ElementRef;

  UpdatePayment: FormGroup;
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
  bidAdvanceAmt: any;r;
  actualReleaseAmt: any;
  pendingAlertMsg: boolean;
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
  payment_id: any;

  constructor(private route: ActivatedRoute,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdatePayment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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

  pendingReleaseCal() {
    let pendingDue = this.UpdatePayment.get('pending_due_amount').value; 
    let releaseAmt = this.UpdatePayment.get('customer_released').value;
    let actualRealease = this.actualReleaseAmt;
    if((parseInt(actualRealease)) < (parseInt(pendingDue))) {
      this.pendingAlertMsg = true;
    }
    if((parseInt(actualRealease)) < (parseInt(releaseAmt))) {
      this.releaseAlertMsg = true;
    }
    else {
      this.pendingAlertMsg = false;
      this.releaseAlertMsg = false;
      let pendingRelease = (parseInt(actualRealease)) - (parseInt(pendingDue) + parseInt(releaseAmt));
      this.UpdatePayment.get('pending_release').setValue(pendingRelease);
    }
  }
  
  setPendingDueAmt()
  {
    this.hidePendingdueamount=false;
  }

  resetPendingDueAmt()
  {
    this.hidePendingdueamount=true;
  }

  paymentChg(event) { 
    let paymentType = event.target.value;
    //cash - 1
    let debitTo = this.UpdatePayment.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.UpdatePayment.get('cheque_no');
    let chequeDate = this.UpdatePayment.get('cheque_date');
    let bankId = this.UpdatePayment.get('bank_id');
    let bankBranchName = this.UpdatePayment.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.UpdatePayment.get('transaction_no');
    let transactionDate = this.UpdatePayment.get('transaction_date');
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
    const result = Object.assign({}, this.UpdatePayment.value);
    this.SubmitbuttonName = "Submit";
    this.disableBtn = false;
    if (this.UpdatePayment.invalid) 
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
      else if(this.f.payment_id.errors) { 
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
      else if(this.f.pending_due_amount.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.pending_release.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.debit_to.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.bank_id.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.remark.errors) {
        this.focus22.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-payment","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')  {
          this.appService.showAlert("Payment", response.msg, "success");
          setTimeout(() => {
            swal.close();
            this.router.navigate(['admin-panel/view-payment']);
          }, 3000);
        } else {
          this.appService.showAlert("Payment", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Payment", "Sorry!! Something went wrong!", "error");
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
    this.UpdatePayment = this.formBuilder.group({
      tenant_id: [''],
      branch_id: [''],
      group_id: [''],
      bidding_id: [''],
      payment_id: [''],
      enrollment_id: [''],
      employee_id: [''],
      total_release_amount: [''],
      given_amount: [''],
      bid_advance_amount: [''],
      gst: [''],
      cgst: [''], 
      sgst: [''], 
      payment_date: [null], 
      pending_due_amount: [0], 
      Payment_released: [0], 
      pending_release: [''],
      payment_type_id: [''],
      customer_released: [''], 
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      remark: [''],
      id: [''],
      updated_by: ['']
    });
    this.pendingAlertMsg = false;
    this.releaseAlertMsg = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.UpdatePayment.get('tenant_id').setValue(tenant_id);
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
    this.UpdatePayment.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
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
    let cond1 = {
      "conditions[id]" : 1
    }
    this.userservice.getData(cond1, "get-auctions", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchName=response.data[0].branch_det.branch_name;
          this.UpdatePayment.get('branch_id').setValue(response.data[0].branch_id);
          this.groupName=response.data[0].group_info.group_name;
          this.UpdatePayment.get('group_id').setValue(response.data[0].group_id);
          this.UpdatePayment.get('bidding_id').setValue(response.data[0].id);
          this.biddingAmt=response.data[0].bidding_amount;
          this.commissionAmt=response.data[0].commission_amount;
          this.ticketName=response.data[0].chit_info.ticket_no;
          this.customerName=response.data[0].customer_info.name;
          this.UpdatePayment.get('enrollment_id').setValue(response.data[0].enrollment_id);
          this.chitValue=response.data[0].chit_value;
          this.totalReleaseAmt=response.data[0].total_release_amount;
          this.paidAmt=response.data[0].paid_amount;
          this.bidAdvanceAmt=response.data[0].bid_advance_amount;
          this.UpdatePayment.get('cgst').setValue(response.data[0].cgst_amount);
          this.UpdatePayment.get('sgst').setValue(response.data[0].sgst_amount);
          this.UpdatePayment.get('gst').setValue(response.data[0].gst_amount);
          this.actualReleaseAmt=response.data[0].actual_release_Amount;
        } else {
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.route.params.subscribe(params => {
      this.payment_id = params.id;
      this.UpdatePayment.get('id').setValue(this.payment_id);
      let cond = {
          "conditions[id]" : this.payment_id 
      }
      this.userservice.getData(cond, "get-payments","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdatePayment.get('tenant_id').setValue(response.data[0].tenant_id);
            let pDate = (response.data[0].payment_date).split('/');
            let setPayDate = {
            'year': parseInt(pDate[2]),
            'month': parseInt(pDate[1]),
            'day': parseInt(pDate[0])
            }
            this.UpdatePayment.get('payment_date').setValue(setPayDate);
            this.UpdatePayment.get('employee_id').setValue(response.data[0].employee_id);
            this.UpdatePayment.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdatePayment.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdatePayment.get('pending_release').setValue(response.data[0].pending_release);
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
