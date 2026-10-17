import { Component, OnInit, ElementRef,ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-commitmentenrol-refund',
  templateUrl: './add-commitmentenrol-refund.component.html',
  styles: []
})
export class AddCommitmentenrolRefundComponent implements OnInit {@ViewChild('tenant_id') focus1:ElementRef;
@ViewChild('branch_id') focus2:ElementRef;
@ViewChild('group_id') focus3:ElementRef;
@ViewChild('customer_id') focus4:ElementRef;
@ViewChild('payment_date') focus5:ElementRef;
@ViewChild('employee_id') focus6:ElementRef;
@ViewChild('total_amount') focus7:ElementRef;
@ViewChild('payment_type_id') focus8:ElementRef;
@ViewChild('debit_to') focus9:ElementRef;
@ViewChild('cheque_no') focus10:ElementRef;
@ViewChild('cheque_date') focus11:ElementRef;
@ViewChild('bank_name_id') focus12:ElementRef;
@ViewChild('bank_branch_name') focus13:ElementRef;
@ViewChild('transaction_no') focus14:ElementRef;
@ViewChild('transaction_date') focus15:ElementRef;

AddCommitmentEnrolRefundAmount: FormGroup;
submitted = false;
disableBtn = false;
SubmitbuttonName: string;
isSuperAdmin:boolean;
companyList: any[];
branchList: any[];
customerList: any[];
groupList: any[];
employeeList: any[];
enrolId: any;
customerdetList: any;
PaidAmt: any;
penaltyAmt: any;
AvailableRefund: any;
CancelAmt: any;
bankList: any[];
hideDebitTo: boolean=true;
hideCheque: boolean=true;
hideBank: boolean=true;
hideTransaction: boolean=true;
hidePayment: boolean=true;
hideRefundAlert: boolean=true;
hidepenaltyAlert: boolean=true;
employeeSet: boolean=true;
hideChitDetails: boolean = true;
enrolChg: boolean = false;
refundAlertMsg: any;
decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
penaltyAmt1: any;
 isValidPenalty: boolean;
 currentDate:any;
CustomList: any;
custList: any[];
customList: any[];
enrollementList: any;
enrolList: any;
pendingDetail: any;
totalPendingAmt: any;
pendingwithbonusandPenalty: any;
totalBonusAmt: any;
totalPenaltyAmt: any;

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
return this.AddCommitmentEnrolRefundAmount.controls;
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
} catch (err) { }
}, (err: any) => { });
}

branchChg(event)
{
let branch_id = event.target.value;
this.setGroupList(branch_id);
this.setCustList(branch_id);
this.setEmployeeList(branch_id);
this.customerList=[];
this.AddCommitmentEnrolRefundAmount.get('group_id').setValue('');
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
} catch (err) { }
}, (err: any) => { });
}

setCustList(branch_id)
{
  let cond = {
    "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-cancel-enrolled-customers", "POST").subscribe((response: any) => {
    try { 
    if (response.status == 'Success') { 
    let deletion_at=response.data;
    if(deletion_at!='') 
    {
    this.customList = response.data;
    //this.enrolId=response.data[0].id;
    }
    } else {
    this.customList = [];
    //this.enrolId=[];
    }
    } catch (err) { }
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
} catch (err) { }
}, (err: any) => { });
}

groupChg(event)
{
this.AddCommitmentEnrolRefundAmount.get('customer_id').setValue('');
this.AddCommitmentEnrolRefundAmount.get('employee_id').setValue('');
this.PaidAmt='';
this.penaltyAmt='';
this.CancelAmt='';
this.AvailableRefund='';
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
//this.enrolId=response.data[0].id;
}
} else {
this.customerList = [];
//this.enrolId=[];
}
} catch (err) { }
}, (err: any) => { });
}

customerChg(event)
{
   /*let selectedCustomer =  this.customerList.filter((listing: any) => listing.customer_id == event.target.value);
   this.AddCommitmentEnrolRefundAmount.get('enrollment_id').setValue(selectedCustomer[0].id)
let enrolmentId=selectedCustomer[0].id;
console.log("enrollid",enrolmentId)
let cond = {
"conditions[id]" : enrolmentId
} 
this.userservice.getData(cond, "get-cancel-customer-details", "POST").subscribe((response: any) => {
try { 
this.customerdetList = [];
this.PaidAmt='';
this.penaltyAmt='';
this.CancelAmt='';
if (response.status == 'Success') { 
this.customerdetList = response.data;
this.PaidAmt=response.data[0].overall_paid_amount;
this.penaltyAmt=response.data[0].penalty_amount;
this.CancelAmt=response.data[0].cancel_amount_payment;
let RefundAmt=(parseInt(this.PaidAmt)-parseInt(this.CancelAmt))-parseInt(this.penaltyAmt);
this.AvailableRefund=RefundAmt;
}
} catch (err) { }
}, (err: any) => { });*/
let cond = {
  id : event.target.value
}
  this.userservice.getData(cond, "get-cancel-customer-slab-type", "POST").subscribe((response: any) => {
  try { 
    if (response.status == 'Success') { 
      this.enrollementList = response.data;
    } else {
      this.enrollementList = [];
    }
  }  catch (err) { }
}, (err: any) => { });
}

enrollementChg(event)
{
  let cond = {
    "conditions[id]" : event.target.value
  }
  console.log(cond);
  this.userservice.getData(cond, "get-cancel-customer-details", "POST").subscribe((response: any) => {
    this.SubmitbuttonName = "Submit";
    try {
      this.customerdetList = [];
this.PaidAmt='';
this.penaltyAmt='';
this.CancelAmt='';
if (response.status == 'Success') { 
this.customerdetList = response.data;
this.PaidAmt=response.data[0].overall_paid_amount;
this.penaltyAmt=response.data[0].penalty_amount;
this.CancelAmt=response.data[0].cancel_amount_payment;
let RefundAmt=(parseInt(this.PaidAmt)-parseInt(this.CancelAmt))-parseInt(this.penaltyAmt);
this.AvailableRefund=RefundAmt;
}
    } catch (err) {}
  }, (err: any) => {
    if (err.status == 401) {}
    else { }
  });
  this.hideChitDetails = false;
  this.hidePayment = false;
}
addInstallmentList(listCount) {
  const installArray = < FormArray > this.AddCommitmentEnrolRefundAmount.get('Installments');
  installArray.controls = []; 
  for(let i=0; i<(listCount); i++) {
    this.addInstallment();
    this.enrolChg = true;
  } 
}

setInstallmentList(dataList) { 
  const installArray = < FormArray > this.AddCommitmentEnrolRefundAmount.get('Installments');
  for(let i=0; i<(dataList.length); i++) { 
    installArray.controls[i].get('auction_id').setValue(dataList[i].auction_id);
    installArray.controls[i].get('installment_no').setValue(dataList[i].installment_no);
    installArray.controls[i].get('inst_amt_wise_pending').setValue(dataList[i].inst_amt_wise_pending);
    installArray.controls[i].get('pending_days').setValue(dataList[i].pending_days);
    installArray.controls[i].get('bonus_inst_wise').setValue(dataList[i].bonus);
    installArray.controls[i].get('penalty_inst_wise').setValue(dataList[i].penalty_amounts);
    installArray.controls[i].get('discount_inst_wise').setValue(dataList[i].discount_inst_wise);
    installArray.controls[i].get('discount_penalty_wise').setValue(dataList[i].discount_penalty_wise);
  }
}

addInstallment() { 
  const control = <FormArray>this.AddCommitmentEnrolRefundAmount.controls['Installments'];
  control.push(this.initInstallment());
}

initInstallment() {
  return this.formBuilder.group({
    'auction_id': [''],
    'installment_no': [''],
    'inst_amt_wise_pending': [''],
    'received_amount': [''],
    'penalty_inst_wise': [''],
    'bonus_inst_wise': [''],
    'discount_inst_wise': [''],
    'discount_penalty_wise': [''],
    'pending_days': [''],
  }); 
}

get Installments() {
  return <FormArray>this.AddCommitmentEnrolRefundAmount.get('Installments');
}

bankChg(event)
 {
   this.AddCommitmentEnrolRefundAmount.get('bank_branch_name').setValue('');
   let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
   this.AddCommitmentEnrolRefundAmount.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
 }
/*AvailableRefundCal()
{
let paidAmt = this.PaidAmt;
let penaltyAmt = this.penaltyAmt;
let releaseAmt = this.CancelAmt;
let RefundAmt=(parseInt(paidAmt)-parseInt(releaseAmt))-parseInt(penaltyAmt);
this.AvailableRefund=RefundAmt;
}*/

refundAmtChg(event) {
let refundAmt = event.target.value;
if(refundAmt > this.AvailableRefund) {
this.hideRefundAlert = false;
this.AddCommitmentEnrolRefundAmount.get('total_amount').setValue('')
}
else {
this.hideRefundAlert = true;

}
if(refundAmt!='' && refundAmt>0) {
this.hidePayment = false;
}
else {
this.hidePayment = true;
}
}

penaltyAmtChg(event) {
this.penaltyAmt1 = event.target.value;
if(this.penaltyAmt1 == ''){
 this.penaltyAmt1 = 0;
}
if(this.penaltyAmt1 > this.AvailableRefund) {
 this.AddCommitmentEnrolRefundAmount.get('penalty_amount').setValue('')
 this.hidepenaltyAlert = false;
 }
 else if(this.penaltyAmt1 <= this.AvailableRefund){
   let RefundAmt=(parseInt(this.PaidAmt)-parseInt(this.CancelAmt))-parseInt(this.penaltyAmt1);
 this.AvailableRefund=RefundAmt;
 this.hidepenaltyAlert = true;
 }
}

/* onKeyUpPenalty(event)
{
 let PenaltyAmt = event.target.value;
 if(PenaltyAmt=='') {
   this.isValidPenalty = true;
 }
 else {
   if((PenaltyAmt).match(this.decimalPattern)) {
     this.isValidPenalty = true;
   }
   else {
     this.isValidPenalty = false;
   }
 }
}*/
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
"conditions[tenant_id]" : this.AddCommitmentEnrolRefundAmount.get('tenant_id').value,
"conditions[branch_id]" : this.AddCommitmentEnrolRefundAmount.get('branch_id').value,
}
this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
try { 
if (response.status == 'Success') { 
this.bankList = response.data;
} else {
this.bankList = [];
}
} catch (err) { }
}, (err: any) => { });

let paymentType = event.target.value;
//cash - 1
let debitTo = this.AddCommitmentEnrolRefundAmount.get('debit_to');
//cheque, DD - 2,3
let chequeNo = this.AddCommitmentEnrolRefundAmount.get('cheque_no');
let chequeDate = this.AddCommitmentEnrolRefundAmount.get('cheque_date');
let bankId = this.AddCommitmentEnrolRefundAmount.get('bank_name_id');
let bankBranchName = this.AddCommitmentEnrolRefundAmount.get('bank_branch_name');
//NEFT - 4
let transactionNo = this.AddCommitmentEnrolRefundAmount.get('transaction_no');
let transactionDate = this.AddCommitmentEnrolRefundAmount.get('transaction_date');
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

onSubmit() {
this.SubmitbuttonName = "Loading...";
this.submitted = true;
this.disableBtn = true;
const result = Object.assign({}, this.AddCommitmentEnrolRefundAmount.value);
if (this.AddCommitmentEnrolRefundAmount.invalid) 
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
else if(this.f.enrollment_id.errors) { 
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
else if(this.f.debit_to.errors) {
this.focus9.nativeElement.focus();
}
else if(this.f.cheque_no.errors) {
this.focus10.nativeElement.focus();
}
else if(this.f.cheque_date.errors) {
this.focus11.nativeElement.focus();
}
else if(this.f.bank_name_id.errors) {
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

let availRefundableAmt = this.AvailableRefund;
let refundAmt = this.f.total_amount.value;
if(refundAmt>availRefundableAmt) {
this.refundAlertMsg = "Refund amount should be less than total refund amount";
this.focus7.nativeElement.focus();
return;
}
else {
this.refundAlertMsg = '';
}

this.userservice.getData(result, "store-payment-refund", "JSON").subscribe((response: any) => {
this.SubmitbuttonName = "Submit";
try {
if (response.status == 'Success') {
this.appService.showAlert("Enrol Refund",response.msg, "success");
setTimeout(() => {
this.router.navigate(['/admin-panel/refund-approval']);
}, 300);
} else {
this.appService.showAlert("Enrol Refund", response.msg, "error");
this.disableBtn = false;
}
} catch (err) {
this.appService.showAlert("Enrol Refund", 'Sorry!! Something went wrong!', "error");
this.disableBtn = false;
}
}, (err: any) => {
this.disableBtn = false;
this.SubmitbuttonName = "Submit";
if (err.status == 401) {
this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
}
else {
this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
}
});
}

ngOnInit() {
this.AddCommitmentEnrolRefundAmount = this.formBuilder.group({
tenant_id: ['', Validators.required],
branch_id: ['', Validators.required],
group_id: ['', Validators.required],
customer_id: ['', Validators.required],
enrollment_id: ['',Validators.required],
payment_date:[this.currentDate, Validators.required],
employee_id: ['', Validators.required],
total_amount:['', [Validators.required,Validators.pattern(this.decimalPattern)]],
payment_type_id:[''],
penalty_amount:['', [Validators.pattern(this.decimalPattern)]],
debit_to: [''],
cheque_no: [''],
cheque_date: [null],
bank_name_id: [''],
bank_branch_name: [''],
transaction_no: [''],
transaction_date: [null],
created_by: [''],
});
let tenant_id = localStorage.getItem('tenant_id');
if(tenant_id) {
this.AddCommitmentEnrolRefundAmount.get('tenant_id').setValue(tenant_id);
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
} catch (err) { }
}, (err: any) => { });
}
this.AddCommitmentEnrolRefundAmount.get('created_by').setValue(localStorage.getItem('logged_user_id'));
}
}
