import { Component, OnInit , ElementRef,ViewChild } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-duplicate-receipt',
  templateUrl: './add-duplicate-receipt.component.html',
  styles: []
})

export class AddDuplicateReceiptComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
 @ViewChild('branch_id') focus2:ElementRef;
 @ViewChild('other_branch') focus3:ElementRef;
 @ViewChild('customer_id') focus4:ElementRef;
 @ViewChild('enrollment_id') focus5:ElementRef;
 @ViewChild('group_id') focus6:ElementRef;
 @ViewChild('ticket_no') focus7:ElementRef;
 @ViewChild('employee_id') focus8:ElementRef;
 @ViewChild('receipt_date') focus9:ElementRef;
 @ViewChild('payment_type_id') focus10:ElementRef;
 @ViewChild('cheque_no') focus11:ElementRef;
 @ViewChild('cheque_date') focus12:ElementRef;
 @ViewChild('bank_name_id') focus13:ElementRef;
 @ViewChild('branch_name') focus14:ElementRef;
 @ViewChild('transaction_no') focus15:ElementRef;
 @ViewChild('transaction_date') focus16:ElementRef;
 @ViewChild('debit_to') focus17:ElementRef;
 @ViewChild('amount') focus18:ElementRef;
 @ViewChild('remarks') focus19:ElementRef;
 @ViewChild('installment_no') focus20:ElementRef;
 @ViewChild('inst_amt_wise_pending') focus21:ElementRef;
 @ViewChild('installment_wise_overall_pending') focus22:ElementRef;
 @ViewChild('received_amount') focus23:ElementRef;
 @ViewChild('penalty_inst_wise') focus24:ElementRef;
 @ViewChild('bonus_inst_wise') focus25:ElementRef;
 @ViewChild('discount_inst_wise') focus26:ElementRef;
 @ViewChild('discount_penalty_wise') focus27:ElementRef;
 @ViewChild('pending_days') focus28:ElementRef;
 @ViewChild('cancel_dividend_amount') focus29:ElementRef;
 
 AddReceipt: FormGroup;
 submitted = false;
 disableBtn = false;
 SubmitbuttonName: string;
 isSuperAdmin: boolean;
 companyList: any[];
 branchList: any[];
 groupList: any[];
 customerList: any[];
 employeeList: any[];
 enrollementList: any[];
 paymentTypeList: any[];
 bankList: any[];
 enrolList: any[];
 pendingDetail: any[];
 employeeSet: boolean=true;
 hidePayment: boolean=true;
 hideChitDetails: boolean = true;
 hideamount:boolean=true;
 hideAdvancedamount:boolean=true;
 enrolChg: boolean = false;
 hideDebitTo: boolean=true;
 hideCheque: boolean=true;
 hideBank: boolean=true;
 hideTransaction: boolean=true;
 hideReceiptType:boolean=true;
 currentDate:any;
 totalPendingAmt: number;
 pendingwithbonusandPenalty: number;
 totalBonusAmt:number;
 totalPenaltyAmt:number;
 receiptAmtAlertMsg: boolean = false;
 AdvanceReleaseAmtAlertMsg: boolean = false;
 decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
 totalAmt :any[];
 AdvanceReceiptAmt: any;
 AdvanceAvailableAmt: any;
 ReceiptType: string;
 AdvancedReceipt: string;
 NormalReceipt: string;
 Enrold: any;
 CancelDividendld: any;
 totalDividendAmt: any;
 //cancelDividendAmt=0;
 receiptAmount=0;

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
 return this.AddReceipt.controls;
 }

 companyChg(event) {
 let tenant_id = event.target.value;
 this.setBranchList(tenant_id);
 this.employeeSet=false;
 this.hidePayment = true;
 this.hideChitDetails = true;
 this.enrolChg = false;
 this.groupList = [];
 this.customerList = [];
 this.enrollementList = [];
 this.employeeList = [];
 this.hideChitDetails = true;
 this.AddReceipt.get('branch_id').setValue('');
 this.AddReceipt.get('employee_id').setValue('');
 this.AddReceipt.get('group_id').setValue('');
 this.AddReceipt.get('customer_id').setValue('');
 this.AddReceipt.get('enrollment_id').setValue('');
 }

 branchChg(event)
 {
 let cond = {
 "conditions[branch_id]" : event.target.value
 }
 this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.groupList = response.data;
 this.hideChitDetails = true;
 this.customerList = [];
 this.enrollementList = [];
 this.AddReceipt.get('employee_id').setValue('');
 this.AddReceipt.get('group_id').setValue('');
 this.AddReceipt.get('customer_id').setValue('');
 this.AddReceipt.get('enrollment_id').setValue('');
 } else {
 this.groupList = [];
 this.hideChitDetails = true;
 this.customerList = [];
 this.enrollementList = [];
 this.AddReceipt.get('employee_id').setValue('');
 this.AddReceipt.get('group_id').setValue('');
 this.AddReceipt.get('customer_id').setValue('');
 this.AddReceipt.get('enrollment_id').setValue('');
 }
 } catch (err) { }
 }, (err: any) => { });
 /*this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.customerList = response.data;
 this.enrollementList = [];
 } else {
 this.customerList = [];
 this.enrollementList = [];
 }
 } catch (err) { }
 }, (err: any) => { });*/
 this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.employeeList = response.data;
 } 
 else {
 this.employeeList = [];
 }
 } catch (err) { }
 }, (err: any) => { });
 this.employeeSet=false;
 this.hidePayment = true;
 this.hideChitDetails = true;
 this.enrolChg = false;
 }

 grpChg(event) {
 let cond = {};
 if(event.target.value=='all') {
 cond = {
 "conditions[branch_id]" : this.AddReceipt.get('branch_id').value,
 }
 }
 else {
 cond = {
 "conditions[branch_id]" : this.AddReceipt.get('branch_id').value,
 "conditions[group_id]" : event.target.value
 }
 }
 this.userservice.getData(cond, "get-enrolled-customers", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.customerList = response.unique_list;
 this.enrollementList = [];
 this.hideChitDetails = true;
 this.AddReceipt.get('customer_id').setValue('');
 this.AddReceipt.get('enrollment_id').setValue('');
 } else {
 this.customerList = [];
 this.enrollementList = [];
 this.hideChitDetails = true;
 this.AddReceipt.get('customer_id').setValue('');
 this.AddReceipt.get('enrollment_id').setValue('');
 }
 } catch (err) { }
 }, (err: any) => { });
 }

 employeeChg()
 {
 this.employeeSet=false;
 this.hidePayment = true;
 this.hideChitDetails = true;
 this.enrolChg = false;
 }

 cancelDividendChg(event)
 {
    this.CancelDividendld=event.target.checked;
    this.AddReceipt.get('amount').setValue("");
    if(event.target.checked) {
    let cond = {
      "conditions[id]" : this.Enrold
    }
    this.userservice.getData(cond, "get-enrol-details-divident-cancel", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.pendingDetail = response.data[0].pending_details;
          this.totalPendingAmt=response.data[0].overall_pending_amount;
          this.pendingwithbonusandPenalty=response.data.total_overall_pending_amount;
          this.totalBonusAmt=response.data.total_bonus_amounts;
          this.totalPenaltyAmt=response.data.total_penalty_amounts;
          this.totalDividendAmt=response.data.total_divident_amount;
          this.AddReceipt.get('ticket_no').setValue(response.data[0].ticket_no);
        }
        else {
          this.pendingDetail = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }
  else {
    let cond = {
      "conditions[id]" : this.Enrold
    }
    this.userservice.getData(cond, "get-enrol-details-duplicate", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.enrolList = response.data[0];
          this.addInstallmentList((response.data[0].pending_details).length); 
          setTimeout(() => {
            this.setInstallmentList(response.data[0].pending_details); 
            /*let receipt_amt = this.AddReceipt.get('amount').value;
            if((receipt_amt!='') && (receipt_amt>0)) { 
              this.setInstallmentList(response.data[0].pending_details); 
            }*/
        },100);
        this.pendingDetail = response.data[0].pending_details;
        this.totalPendingAmt=response.data[0].overall_pending_amount;
        this.pendingwithbonusandPenalty=response.data.total_overall_pending_amount;
        this.totalBonusAmt=response.data.total_bonus_amounts;
        this.totalPenaltyAmt=response.data.total_penalty_amounts;
        this.totalDividendAmt=response.data.total_divident_amount;
        this.AddReceipt.get('ticket_no').setValue(response.data[0].ticket_no);
      }
      else {
        this.pendingDetail = [];
      }
    } catch (err) {}
  },(err: any) => { });
  }
}

 enrollementChg(event)
 {
 this.hideChitDetails=false;
 this.Enrold=event.target.value;
 let cond = {
 "conditions[id]" : event.target.value
 }
 this.userservice.getData(cond, "get-enrol-details-duplicate", "POST").subscribe((response: any) => {
 this.SubmitbuttonName = "Submit";
 try {
 if (response.status == 'Success') {
 this.enrolList = response.data[0];
 this.addInstallmentList((response.data[0].pending_details).length); 
 setTimeout(() => {
 this.setInstallmentList(response.data[0].pending_details); 
 },100);
 this.pendingDetail = response.data[0].pending_details;
 this.totalPendingAmt=response.data[0].overall_pending_amount;
 this.pendingwithbonusandPenalty=response.data.total_overall_pending_amount;
 this.totalBonusAmt=response.data.total_bonus_amounts;
 this.totalPenaltyAmt=response.data.total_penalty_amounts;
 this.totalDividendAmt=response.data.total_divident_amount;
 this.AddReceipt.get('ticket_no').setValue(response.data[0].ticket_no);
 }
 else {
 this.pendingDetail = [];
 }
 } catch (err) {}
 }, (err: any) => {
 if (err.status == 401) {}
 else { }
 });
 let cond1 = {
 enrollment_id : event.target.value
 }
 this.userservice.getData(cond1, "get-available-advance-details", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.AdvanceReceiptAmt = response.data.available_advance;
 } else {
 this.AdvanceReceiptAmt = [];
 }
 } catch (err) { }
 }, (err: any) => { });
 this.hideReceiptType=false;
 }

 addInstallmentList(listCount) {
 const installArray = < FormArray > this.AddReceipt.get('Installments');
 installArray.controls = []; 
 for(let i=0; i<(listCount); i++) {
 this.addInstallment();
 this.enrolChg = true;
 } 
 }

 setInstallmentList(dataList) { 
 const installArray = < FormArray > this.AddReceipt.get('Installments');
 for(let i=0; i<(dataList.length); i++) { 
 installArray.controls[i].get('auction_id').setValue(dataList[i].auction_id);
 installArray.controls[i].get('installment_no').setValue(dataList[i].installment_no);
 installArray.controls[i].get('inst_amt_wise_pending').setValue(dataList[i].inst_amt_wise_pending);
 installArray.controls[i].get('installment_wise_overall_pending').setValue(dataList[i].installment_wise_overall_pending);
 installArray.controls[i].get('pending_days').setValue(dataList[i].pending_days);
 installArray.controls[i].get('bonus_inst_wise').setValue(dataList[i].bonus);
 //installArray.controls[i].get('penalty_inst_wise').setValue(dataList[i].penalty_amounts);
 //installArray.controls[i].get('pending_inst_wise').setValue(dataList[i].pending_amount);
 //installArray.controls[i].get('cancel_dividend_inst_wise').setValue(dataList[i].cancel_dividend_amount);
 installArray.controls[i].get('discount_inst_wise').setValue(dataList[i].discount_inst_wise);
 installArray.controls[i].get('discount_penalty_wise').setValue(dataList[i].discount_penalty_wise);
 installArray.controls[i].get('penalty_amounts').setValue(dataList[i].penalty_amounts);
 installArray.controls[i].get('cancel_dividend_amount').setValue(dataList[i].cancel_dividend_amount);
 }
 }

 addInstallment() { 
 const control = <FormArray>this.AddReceipt.controls['Installments'];
 control.push(this.initInstallment());
 }

 initInstallment() {
 return this.formBuilder.group({
 'auction_id': [''],
 'installment_no': [''],
 'inst_amt_wise_pending': [''],
 'installment_wise_overall_pending':[''],
 'received_amount': [''],
 'penalty_inst_wise': [''],
 //'pending_inst_wise': [''],
 'cancel_dividend_inst_wise' : [''],
 'bonus_inst_wise': [''],
 'discount_inst_wise': [''],
 'discount_penalty_wise': [''],
 'pending_days': [''],
 'penalty_amounts':[''],
 'cancel_dividend_amount': [''],
 }); 
 }

 get Installments() {
 return <FormArray>this.AddReceipt.get('Installments');
 }

 onAmtChange(event) { 
  let receiptAmt = event.target.value;
  let receipt_type = this.AddReceipt.get('receipt_type').value;
  if(receipt_type=='Advanced Receipt') {
    if(receiptAmt > (this.AdvanceReceiptAmt)) {
      this.AdvanceReleaseAmtAlertMsg = true;
    }
    else {
      this.AdvanceReleaseAmtAlertMsg = false;
    }
    if(receiptAmt > (this.pendingwithbonusandPenalty)) {
      this.receiptAmtAlertMsg = true;
    }
    else {
      this.receiptAmtAlertMsg = false;
    }
  }
  else {
    if(receiptAmt > (this.pendingwithbonusandPenalty)) {
      this.receiptAmtAlertMsg = true;
    }
    else {
      this.receiptAmtAlertMsg = false;
    }
  }
  const installArray = < FormArray > this.AddReceipt.get('Installments');
  for (let i = 0; i < installArray.length; i++) {
    receiptAmt = this.setDeductions(i,receiptAmt);
  }
}

setDeductions(i,receiptAmt) {
  const installArray = < FormArray > this.AddReceipt.get('Installments');
  let pending = this.pendingDetail[i].inst_amt_wise_pending;
  let penalty = this.pendingDetail[i].penalty_amounts; 
  let bonus = this.pendingDetail[i].bonus; 
  let cancelDividendAmt = this.pendingDetail[i].cancel_dividend_amount; 
  if(parseInt(bonus)>0) {
    receiptAmt = parseInt(receiptAmt) + parseInt(bonus);
    installArray.controls[i].get('bonus_inst_wise').setValue(bonus);
  }
  if(parseInt(penalty) > 0) {
    if(parseInt(receiptAmt) >= parseInt(penalty)) {
      installArray.controls[i].get('penalty_inst_wise').setValue(penalty);
      receiptAmt = parseInt(receiptAmt) - parseInt(penalty);
    }
    else { 
      installArray.controls[i].get('penalty_inst_wise').setValue(receiptAmt);
      receiptAmt = 0;
    }
  }
  if(parseInt(cancelDividendAmt) > 0) {
    if(parseInt(receiptAmt) >= parseInt(cancelDividendAmt)) {
      installArray.controls[i].get('cancel_dividend_inst_wise').setValue(cancelDividendAmt);
      receiptAmt = parseInt(receiptAmt) - parseInt(cancelDividendAmt);
    }
    else {
      installArray.controls[i].get('cancel_dividend_inst_wise').setValue(receiptAmt);
      receiptAmt = 0;
    }
  }
  if(parseInt(pending)>0) {
    if(parseInt(receiptAmt) >= parseInt(pending)) {
      installArray.controls[i].get('received_amount').setValue(pending);
      receiptAmt = parseInt(receiptAmt) - parseInt(pending);
    }
    else {
      installArray.controls[i].get('received_amount').setValue(receiptAmt);
      receiptAmt = 0;
    }
  }
  return receiptAmt;
}

 paymentChg(event)
 {
 if (event.target.value == 1) {
 this.hideDebitTo=false;
 this.hideCheque=true;
 this.hideBank=true;
 this.hideTransaction=true;
 }
 else if (event.target.value == 2) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (event.target.value == 3) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (event.target.value == 4){
 this.hideDebitTo=true;
 this.hideCheque=true;
 this.hideBank=true;
 this.hideTransaction=false;
 }
 else{
 this.hideDebitTo=true;
 this.hideCheque=true;
 this.hideBank=true;
 this.hideTransaction=true;
 }
 let cond = {
 "conditions[tenant_id]" : this.AddReceipt.get('tenant_id').value,
 "conditions[branch_id]" : this.AddReceipt.get('branch_id').value,
 }
 this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.bankList = response.data;
 } 
 else {
 this.bankList = [];
 }
 } catch (err) { }
 }, (err: any) => { });

 let paymentType = event.target.value;
 //cash - 1
 let debitTo = this.AddReceipt.get('debit_to');
 //cheque, DD - 2,3
 let chequeNo = this.AddReceipt.get('cheque_no');
 let chequeDate = this.AddReceipt.get('cheque_date');
 let bankId = this.AddReceipt.get('bank_name_id');
 let bankBranchName = this.AddReceipt.get('branch_name');
 //NEFT - 4
 let transactionNo = this.AddReceipt.get('transaction_no');
 let transactionDate = this.AddReceipt.get('transaction_date');
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
 } catch (err) { }
 }, (err: any) => { });
 }

 customerChg(event)
 {
  this.hideamount = true;
 let cond = {
 "conditions[customer_id]" : event.target.value
 }
 this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.enrollementList = response.data;
 this.hideChitDetails = true;
 this.AddReceipt.get('enrollment_id').setValue('');
 this.AddReceipt.get('receipt_type').setValue('');
 } 
 else {
 this.enrollementList = [];
 this.hideChitDetails = true;
 this.AddReceipt.get('enrollment_id').setValue('');
 this.AddReceipt.get('receipt_type').setValue('');
 }
 } catch (err) { }
 }, (err: any) => { });
 }
 /* enrollChg(event)
 {
 let enrollment_id = event.target.value;
 this.setAdvanceAmtList(enrollment_id);
 }
 
 setAdvanceAmtList(enrollment_id)
 {
 let cond = {
 "conditions[enrollment_id]" : enrollment_id
 }
 this.userservice.getData(cond, "get-available-advance-details", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.AdvanceReceiptAmt = response.data.available_advance;
 } else {
 this.AdvanceReceiptAmt = [];
 }
 } catch (err) { }
 }, (err: any) => { });
 } */

 /*setAdvancedReceipt(event)
 {
 let value=event.target.value;
 this.hideamount=false;
 this.hideChitDetails=false;
 this.hidePayment = true;
 this.hideAdvancedamount=false;
 this.AddReceipt.get('receipt_type').setValue("Advanced Receipt");
 if(event.target.value="Advanced Receipt")
 {
 this.AdvancedReceipt="Advanced";
 }
 else{
 this.NormalReceipt="normal"
 }
 }
 setNormalReceipt(event)
 {
 this.hideamount=false;
 this.hideChitDetails=false;
 this.hidePayment = false;
 this.hideAdvancedamount=true;
 this.AddReceipt.get('receipt_type').setValue("Normal Receipt");
 }*/

 receiptTypeChg(event) {
    let receipt_type = event.target.value;
    this.AdvanceReleaseAmtAlertMsg = false;
    this.receiptAmtAlertMsg = false;
    this.AddReceipt.get('amount').setValue("");
    const installArray = < FormArray > this.AddReceipt.get('Installments');
    for (let i = 0; i < installArray.length; i++) {
      installArray.controls[i].get('bonus_inst_wise').setValue('');
      installArray.controls[i].get('penalty_inst_wise').setValue('');
      installArray.controls[i].get('cancel_dividend_inst_wise').setValue('');
      installArray.controls[i].get('received_amount').setValue('');
    }
    if(receipt_type=='Advanced Receipt') {
      this.hideamount=false;
      this.hideChitDetails=false;
      this.hidePayment = true;
      this.hideAdvancedamount=false;
      this.AddReceipt.get('receipt_type').setValue("Advanced Receipt");
      if(event.target.value="Advanced Receipt")
      {
      this.AdvancedReceipt="Advanced";
      }
      else{
      this.NormalReceipt="normal"
      }
    }
    else if(receipt_type=='Normal Receipt') {
      this.hideamount=false;
      this.hideChitDetails=false;
      this.hidePayment = false;
      this.hideAdvancedamount=true;
      this.AddReceipt.get('receipt_type').setValue("Normal Receipt");
    }
  }

 onSubmit() {
 this.SubmitbuttonName = "Loading...";
 this.submitted = true;
 this.disableBtn = true;
 const result = Object.assign({}, this.AddReceipt.value);
 if (this.AddReceipt.invalid) 
 {
 this.SubmitbuttonName = "Submit";
 this.disableBtn = false;
 if(this.f.tenant_id.errors) {
 this.focus1.nativeElement.focus();
 }
 else if(this.f.branch_id.errors) {
 this.focus2.nativeElement.focus();
 }
 else if(this.f.other_branch.errors) {
 this.focus3.nativeElement.focus();
 }
 else if(this.f.customer_id.errors) {
 this.focus4.nativeElement.focus();
 }
 else if(this.f.enrollment_id.errors) { 
 this.focus5.nativeElement.focus();
 }
 else if(this.f.group_id.errors) {
 this.focus6.nativeElement.focus();
 }
 else if(this.f.ticket_no.errors) {
 this.focus7.nativeElement.focus();
 }
 else if(this.f.employee_id.errors) {
 this.focus8.nativeElement.focus();
 }
 else if(this.f.receipt_date.errors) {
 this.focus9.nativeElement.focus();
 }
 else if(this.f.payment_type_id.errors) {
 this.focus10.nativeElement.focus();
 }
 else if(this.f.cheque_no.errors) {
 this.focus11.nativeElement.focus();
 }
 else if(this.f.cheque_date.errors) {
 this.focus12.nativeElement.focus();
 }
 else if(this.f.bank_name_id.errors) {
 this.focus13.nativeElement.focus();
 }
 else if(this.f.branch_name.errors) {
 this.focus14.nativeElement.focus();
 }
 else if(this.f.transaction_no.errors) {
 this.focus15.nativeElement.focus();
 }
 else if(this.f.transaction_date.errors) {
 this.focus16.nativeElement.focus();
 }
 else if(this.f.debit_to.errors) {
 this.focus17.nativeElement.focus();
 }
 else if(this.f.amount.errors) {
 this.focus18.nativeElement.focus();
 }
 else if(this.f.remarks.errors) {
 this.focus19.nativeElement.focus();
 }
 else if(this.f.installment_no.errors) {
 this.focus20.nativeElement.focus();
 }
 else if(this.f.inst_amt_wise_pending.errors) {
 this.focus21.nativeElement.focus();
 }
 else if(this.f.installment_wise_overall_pending.errors) {
 this.focus22.nativeElement.focus();
 }
 else if(this.f.received_amount.errors) {
 this.focus23.nativeElement.focus();
 }
 else if(this.f.penalty_inst_wise.errors) {
 this.focus24.nativeElement.focus();
 }
 else if(this.f.bonus_inst_wise.errors) {
 this.focus25.nativeElement.focus();
 }
 else if(this.f.discount_inst_wise.errors) {
 this.focus26.nativeElement.focus();
 }
 else if(this.f.discount_penalty_wise.errors) {
 this.focus27.nativeElement.focus();
 }
 else if(this.f.pending_days.errors) {
 this.focus28.nativeElement.focus();
 }
 else if(this.f.cancel_dividend_amount.errors) {
 this.focus29.nativeElement.focus();
 }
 return;
 }
 this.userservice.getData(result, "store-receipt-duplicate", "JSON").subscribe((response: any) => {
 this.SubmitbuttonName = "Submit";
 try {
 if (response.status == 'Success') {
 this.appService.showAlert("Receipt",response.msg, "success");
 setTimeout(() => {
 this.router.navigate(['/admin-panel/receipt-report']);
 }, 300);
 } 
 else {
 this.appService.showAlert("Receipt", response.msg, "error");
 this.disableBtn = false;
 }
 } catch (err) {
 this.appService.showAlert("Receipt", 'Sorry!! Something went wrong!', "error");
 this.disableBtn = false;
 }
 }, 
 (err: any) => {
 this.disableBtn = false;
 this.SubmitbuttonName = "Submit";
 if (err.status == 401) {
 this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
 }
 else {
 this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
 }
 });
 }

 ngOnInit() {
 this.AddReceipt = this.formBuilder.group({
 tenant_id: ['', Validators.required],
 branch_id: ['', Validators.required],
 other_branch: [''],
 customer_id: ['', Validators.required],
 enrollment_id: ['', Validators.required],
 cancel_dividend: [''],
 group_id: ['', Validators.required],
 ticket_no: [''],
 employee_id: ['', Validators.required],
 receipt_date: [this.currentDate,Validators.required],
 receipt_type:[''],
 payment_type_id: [''],
 cheque_no: [''],
 cheque_date: [null], 
 bank_name_id: [''],
 branch_name: [''],
 transaction_no: [''],
 transaction_date: [null],
 debit_to: [''],
 amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
 remarks: [''],
 created_by: [''],
 'Installments': this.formBuilder.array([
 this.formBuilder.group({
 'auction_id': [''],
 'installment_no': [''],
 'inst_amt_wise_pending': [''],
 'installment_wise_overall_pending':[''],
 'received_amount': ['', [Validators.pattern(this.decimalPattern)]],
 'penalty_inst_wise': [''],
 //'pending_inst_wise': ['', [Validators.pattern(this.decimalPattern)]],
 'cancel_dividend_inst_wise' : [''],
 'bonus_inst_wise': [''],
 'discount_inst_wise': [''],
 'discount_penalty_wise': [''],
 'pending_days': [''],
 'cancel_dividend_amount': ['', [Validators.pattern(this.decimalPattern)]], 
 })
 ]),
 });
 
 this.totalPendingAmt = 0;
 this.pendingwithbonusandPenalty=0;
 this.totalBonusAmt=0;
 this.totalPenaltyAmt=0;
 let tenant_id = localStorage.getItem('tenant_id');
 if(tenant_id) {
 this.AddReceipt.get('tenant_id').setValue(tenant_id);
 this.isSuperAdmin = false;
 this.setBranchList(tenant_id);
 }
 else {
 this.isSuperAdmin = true;
 this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') {
 this.companyList = response.data;
 } 
 else {
 this.companyList = [];
 }
 } catch (err) { }
 }, (err: any) => { });
 }
 this.userservice.getData("", "list-payment-types", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.paymentTypeList = response.data;
 } 
 else {
 this.paymentTypeList = [];
 }
 } catch (err) { }
 }, (err: any) => { });
 this.AddReceipt.get('created_by').setValue(localStorage.getItem('logged_user_id'));
 } 
}
