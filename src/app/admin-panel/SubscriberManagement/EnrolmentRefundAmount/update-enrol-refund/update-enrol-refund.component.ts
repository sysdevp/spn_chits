import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-enrol-refund',
  templateUrl: './update-enrol-refund.component.html',
  styles: []
})

export class UpdateEnrolRefundComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('payment_date') focus5:ElementRef;
  @ViewChild('employee_id') focus6:ElementRef;
  @ViewChild('total_amount') focus7:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;
  @ViewChild('debit_from') focus9:ElementRef;
  @ViewChild('cheque_no') focus10:ElementRef;
  @ViewChild('cheque_date') focus11:ElementRef;
  @ViewChild('bank_id') focus12:ElementRef;
  @ViewChild('bank_branch_name') focus13:ElementRef;
  @ViewChild('transaction_no') focus14:ElementRef;
  @ViewChild('transaction_date') focus15:ElementRef;

  UpdateEnrolRefundAmount: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  hideDebitTo: boolean=true;
  hideChequeNo: boolean=true;
  hideChequeDate: boolean=true;
  hideBankName: boolean=true;
  hideBranchName: boolean=true;
  hideTransactionDate: boolean=true;
  hideTransactionNo: boolean=true;
  customerList: any[];
  groupList: any[];
  employeeList: any[];
  enrolId: any;
  customerdetList: any;
  PaidAmt: any;
  penaltyAmt: any;
  AvailableRefund: any;
  CancelAmt: any;
  EnrolRefundId: any;
  bankList: any;
  currentDate:any;

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
    return this.UpdateEnrolRefundAmount.controls;
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setGroupList(branch_id);
    this.setEmployeeList(branch_id)
  }

  setGroupList(branch_id)
  {
    let cond = {
      "conditions[branch_id]" : branch_id
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

  setEmployeeList(branch_id)
  {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  groupChg(event)
  {
    let group_id = event.target.value;
    this.setCustomerList(group_id);
  }

  setCustomerList(group_id)
  {
    let cond = {
      "conditions[group_id]" : group_id
    }
    this.userservice.getData(cond, "get-cancel-enrolled-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          let deletion_at=response.data;
          if(deletion_at!='')
          {
            this.customerList = response.data;
            this.enrolId=response.data[0].customer_info.enroll_id;
          }
        } else {
          this.customerList = [];
          this.enrolId=[];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  customerChg(event)
  {
    let enrolId=this.enrolId;
    let cond = {
      "conditions[id]" : enrolId
    }   
    this.userservice.getData(cond, "get-cancel-customer-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerdetList = response.data;
          this.PaidAmt=response.data[0].paid_amount;
          this.penaltyAmt=response.data[0].penalty_amount;
          this.CancelAmt=response.data[0].cancel_amount_payment;
        } else {
          this.customerdetList = [];
          this.PaidAmt=[];
          this.penaltyAmt=[];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  AvailableRefundCal()
  {
    let paidAmt = this.PaidAmt;
    let penaltyAmt = this.penaltyAmt;
    let releaseAmt = this.CancelAmt;
    let RefundAmt=(parseInt(paidAmt)-parseInt(releaseAmt))-parseInt(penaltyAmt);
    this.AvailableRefund=RefundAmt;
  }

  paymentChg(event) {
    this.setPayments(event.target.value)
  }
  
  setPayments(paymentTypeId) {
    if (paymentTypeId == 1) {
      this.hideDebitTo=false;
      this.hideChequeNo=true;
      this.hideChequeDate=true;
      this.hideBankName=true;
      this.hideBranchName=true;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else if (paymentTypeId == 2) {
      this.hideDebitTo=true;
      this.hideChequeNo=false;
      this.hideChequeDate=false;
      this.hideBankName=false;
      this.hideBranchName=false;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else if (paymentTypeId == 3) {
      this.hideDebitTo=true;
      this.hideChequeNo=false;
      this.hideChequeDate=false;
      this.hideBankName=false;
      this.hideBranchName=false;
      this.hideTransactionDate=true;
      this.hideTransactionNo=true;
    }
    else  if (paymentTypeId == 4){
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
    //cash - 1
    let debitTo = this.UpdateEnrolRefundAmount.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.UpdateEnrolRefundAmount.get('cheque_no');
    let chequeDate = this.UpdateEnrolRefundAmount.get('cheque_date');
    let bankId = this.UpdateEnrolRefundAmount.get('bank_id');
    let bankBranchName = this.UpdateEnrolRefundAmount.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.UpdateEnrolRefundAmount.get('transaction_no');
    let transactionDate = this.UpdateEnrolRefundAmount.get('transaction_date');
    if(paymentTypeId=='1') {
      debitTo.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9_]+$')]);
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      bankBranchName.setValidators(null);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if((paymentTypeId=='2') || (paymentTypeId=='3')) {
      debitTo.setValidators(null);
      chequeNo.setValidators([Validators.required]);
      chequeDate.setValidators([Validators.required]);
      bankId.setValidators([Validators.required]);
      bankBranchName.setValidators([Validators.required]);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if(paymentTypeId=='4') {
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
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateEnrolRefundAmount.value); 
    if (this.UpdateEnrolRefundAmount.invalid) 
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
      else if(this.f.payment_date.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.total_amount.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.debit_from.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.bank_id.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus15.nativeElement.focus();
      }
      return;
    }
    
    this.userservice.getData(result, "update-payment-refund", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Payment Refund",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-enrol-refund-amount']);
          }, 300);
        } else {
          this.appService.showAlert("Payment Refund", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Payment Refund", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateEnrolRefundAmount = this.formBuilder.group({
      tenant_id: [''],
      branch_id: [''],
      group_id: [''],
      customer_id: [''],
      payment_date:[this.currentDate, Validators.required],
      employee_id: [''],
      total_amount:[''],
      payment_type_id:[''],
      debit_from: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      remark: [''],
      id:[''],
      updated_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
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
    this.route.params.subscribe(params => {
      this.EnrolRefundId = params.id;
      this.UpdateEnrolRefundAmount.get('id').setValue(this.EnrolRefundId);
      this.UpdateEnrolRefundAmount.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.EnrolRefundId 
      }
      this.userservice.getData(cond, "get-payment-refund","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateEnrolRefundAmount.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateEnrolRefundAmount.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateEnrolRefundAmount.get('group_id').setValue(response.data[0].group_id);
            this.UpdateEnrolRefundAmount.get('customer_id').setValue(response.data[0].customer_id);
            if(response.data[0].payment_date!='') {
              let paymentDate = (response.data[0].payment_date).split('/');
              let setPaymentDate = {
                'year': parseInt(paymentDate[2]),
                'month': parseInt(paymentDate[1]),
                'day': parseInt(paymentDate[0])
              } 
              this.UpdateEnrolRefundAmount.get('payment_date').setValue(setPaymentDate);
            }
            this.UpdateEnrolRefundAmount.get('employee_id').setValue(response.data[0].employee_id);
            this.UpdateEnrolRefundAmount.get('total_amount').setValue(response.data[0].total_amount);
            this.UpdateEnrolRefundAmount.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdateEnrolRefundAmount.get('debit_from').setValue(response.data[0].debit_from);
            this.UpdateEnrolRefundAmount.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!='') {
              let chequeDate = (response.data[0].cheque_date).split('/');
              let setChequeDate = {
                'year': parseInt(chequeDate[2]),
                'month': parseInt(chequeDate[1]),
                'day': parseInt(chequeDate[0])
              } 
              this.UpdateEnrolRefundAmount.get('payment_date').setValue(setChequeDate);
            }
            this.UpdateEnrolRefundAmount.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdateEnrolRefundAmount.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            this.UpdateEnrolRefundAmount.get('transaction_no').setValue(response.data[0].transaction_no);
            if(response.data[0].transaction_date!='') {
              let transcationDate = (response.data[0].transaction_date).split('/');
              let setTranscationDate = {
                'year': parseInt(transcationDate[2]),
                'month': parseInt(transcationDate[1]),
                'day': parseInt(transcationDate[0])
              } 
              this.UpdateEnrolRefundAmount.get('transaction_date').setValue(setTranscationDate);
            }
            this.setGroupList(response.data[0].branch_id);
            this.setEmployeeList(response.data[0].branch_id);
            this.setCustomerList(response.data[0].group_id);
            this.setPayments(response.data[0].payment_type_id);
          }
          else {
            this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Payment Refund", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
