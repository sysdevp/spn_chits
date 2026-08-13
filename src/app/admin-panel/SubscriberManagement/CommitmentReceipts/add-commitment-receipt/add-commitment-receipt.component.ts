import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-commitment-receipt',
  templateUrl: './add-commitment-receipt.component.html',
  styles: []
})

export class AddCommitmentReceiptComponent implements OnInit {
  @ViewChild('teant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('other_branch') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;
  //@ViewChild('slab_id') focus6:ElementRef;
  //@ViewChild('ticket_no') focus7:ElementRef;
  @ViewChild('employee_id') focus8:ElementRef;
  @ViewChild('receipt_date') focus9:ElementRef;
  @ViewChild('payment_type_id') focus10:ElementRef;
  @ViewChild('cheque_no') focus11:ElementRef;
  @ViewChild('cheque_date') focus12:ElementRef;
  @ViewChild('bank_name_id') focus13:ElementRef;
  @ViewChild('bank_branch_name') focus14:ElementRef;
  @ViewChild('transaction_no') focus15:ElementRef;
  @ViewChild('transaction_date') focus16:ElementRef;
  @ViewChild('debit_to') focus17:ElementRef;
  @ViewChild('amount') focus18:ElementRef;
  @ViewChild('remarks') focus19:ElementRef;
  @ViewChild('installment_no') focus20:ElementRef;
  @ViewChild('inst_amt_wise_pending') focus21:ElementRef;
  @ViewChild('received_amount') focus22:ElementRef;
  @ViewChild('penalty_inst_wise') focus23:ElementRef;
  @ViewChild('bonus_inst_wise') focus24:ElementRef;
  @ViewChild('discount_inst_wise') focus25:ElementRef;
  @ViewChild('discount_penalty_wise') focus26:ElementRef;
  @ViewChild('pending_days') focus27:ElementRef;
  @ViewChild('alt_rec_no') focus28:ElementRef;
  
  AddCommitmentReceipt: FormGroup;
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
  enrolList: any;
  pendingDetail: any[];
  employeeSet: boolean=true;
  hidePayment: boolean=true;
  hideChitDetails: boolean = true;
  enrolChg: boolean = false;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideamount:boolean=true;
  currentDate:any;
  totalPendingAmt: number;
  pendingwithbonusandPenalty: number;
  totalBonusAmt:number;
  totalPenaltyAmt:number;
  receiptAmtAlertMsg: boolean = false;
  AdvanceReleaseAmtAlertMsg: boolean = false;
  BeforeEnrolReleaseAmtAlertMsg: boolean = false;
 
  hideAdvancedamount:boolean=true;
  hideAdvancedamount1:boolean=true;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  totalAmt: any[];
  AdvanceReceiptAmt: any;
  BeforeEnrolReceiptAmt: any;
  slabList: any[];
  customerName: any;
  TotalAmt: any;
  NormalReceipt: string;
  AdvancedReceipt: string;

  name: any;
  CustomerId: any;
  advanceamount: number;
  receipt_response: any;
  receipt_identify: any;

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
    return this.AddCommitmentReceipt.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
    this.employeeList = [];
    this.AddCommitmentReceipt.get('employee_id').setValue('');
    this.customerList = [];
    this.AddCommitmentReceipt.get('customer_id').setValue('');
    this.enrollementList = [];
    this.AddCommitmentReceipt.get('enrollment_id').setValue('');
  }

  branchChg(event)
  {
    let cond = {
      id : event.target.value
    }
    this.userservice.getData(cond, "get-commitment-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
          console.log(this.customerName)
        } else {
          this.customerList = [];
          this.AddCommitmentReceipt.get('customer_id').setValue('');
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.AddCommitmentReceipt.get('employee_id').setValue('');
    this.customerList = [];
    this.AddCommitmentReceipt.get('customer_id').setValue('');
    this.enrollementList = [];
    this.AddCommitmentReceipt.get('enrollment_id').setValue('');
 
  }
  branchChgemployee(event)
  {
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
          this.AddCommitmentReceipt.get('employee_id').setValue('');
        }
      }  catch (err) { }
    }, (err: any) => { });
    
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
    this.employeeList = [];
    this.AddCommitmentReceipt.get('employee_id').setValue('');
    this.customerList = [];
    this.AddCommitmentReceipt.get('customer_id').setValue('');
    this.enrollementList = [];
    this.AddCommitmentReceipt.get('enrollment_id').setValue('');
  }

  employeeChg()
  {
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
  }

  /** Slab dropdown / table label: "Slab Name / AR No" (no duplicate if API already appended). */
  slabLabelWithAr(row: any): string {
    if (!row) {
      return '';
    }
    const slab = (row.slab_info && row.slab_info.slab_name) || row.slab_name || '';
    const ar = (row.ar_no != null && row.ar_no !== '') ? String(row.ar_no).trim() : '';
    const slabStr = String(slab).trim();
    if (!slabStr && !ar) {
      return '';
    }
    if (!ar) {
      return slabStr;
    }
    if (slabStr.indexOf(ar) >= 0) {
      return slabStr;
    }
    return slabStr ? (slabStr + ' / ' + ar) : ar;
  }

  enrollementChg(event)
  {
    this.AddCommitmentReceipt.get('amount').setValue('');
    let rDate=this.AddCommitmentReceipt.get('receipt_date').value;
    let cond = {
      "enrolid" : event.target.value,
      "recdate":rDate
    }
    this.userservice.getData(cond, "commitment-details-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.enrolList = response.data[0];
          const details = (response.data[0].pending_details || []).slice().sort(
            (a, b) => Number(a.installment_no) - Number(b.installment_no)
          );
          this.pendingDetail = details;
          this.addInstallmentList(details.length);
          setTimeout(() => {
            this.setInstallmentList(details);
          }, 100);
          this.totalPendingAmt=response.data[0].overall_pending_amount;
          this.pendingwithbonusandPenalty=response.data.total_overall_pending_amount;
          this.totalBonusAmt=response.data.total_bonus_amounts;
          this.totalPenaltyAmt=response.data.total_penalty_amounts;
          //this.AddCommitmentReceipt.get('ticket_no').setValue(response.data[0].ticket_no);
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
    this.hideChitDetails = false;
    this.hidePayment = false;
    this.hideamount = false;
  }

  onDateChg()
 {
  this.AddCommitmentReceipt.get('cancel_dividend').setValue(null);
 this.AddCommitmentReceipt.get('enrollment_id').setValue('');
 this.AddCommitmentReceipt.get('receipt_type').setValue('');
 this.AddCommitmentReceipt.get('amount').setValue('');
 }

  addInstallmentList(listCount) {
    const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
    installArray.controls = []; 
    for(let i=0; i<(listCount); i++) {
      this.addInstallment();
      this.enrolChg = true;
    } 
  }

  setInstallmentList(dataList) { 
    const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
    for(let i=0; i<(dataList.length); i++) { 
      installArray.controls[i].get('auction_id').setValue(dataList[i].auction_id);
      console.log("dataList",dataList);
      console.log("dataListlength",dataList.length);
      installArray.controls[i].get('installment_no').setValue(dataList[i].installment_no);
      console.log("insta",dataList[i].installment_no);
      installArray.controls[i].get('inst_amt_wise_pending').setValue(dataList[i].inst_amt_wise_pending);
      //installArray.controls[i].get('installment_wise_overall_pending').setValue(dataList[i].installment_wise_overall_pending);
      installArray.controls[i].get('pending_days').setValue(dataList[i].pending_days);
      installArray.controls[i].get('bonus_inst_wise').setValue(dataList[i].bonus_inst_wise);
      installArray.controls[i].get('penalty_inst_wise').setValue(dataList[i].penalty_inst_wise);
      installArray.controls[i].get('discount_inst_wise').setValue(dataList[i].discount_inst_wise);
      installArray.controls[i].get('discount_penalty_wise').setValue(dataList[i].discount_penalty_wise);
    }
  }

  addInstallment() { 
    const control = <FormArray>this.AddCommitmentReceipt.controls['Installments'];
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
    return <FormArray>this.AddCommitmentReceipt.get('Installments');
  }

  bankChg(event)
  {
    this.AddCommitmentReceipt.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddCommitmentReceipt.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  onAmtChange(event) { 
    let receiptAmt = event.target.value;
    this.TotalAmt=receiptAmt;
    let receipt_type = this.AddCommitmentReceipt.get('receipt_type').value;

    if(receipt_type=='Advanced Receipt') {
      if(receiptAmt > (this.AdvanceReceiptAmt)) {
        this.AdvanceReleaseAmtAlertMsg = true;
        this.AddCommitmentReceipt.get('amount').setValue('');
      }
      else {
        this.AdvanceReleaseAmtAlertMsg = false;
      }
      if(receiptAmt > (this.pendingwithbonusandPenalty)) {
        this.receiptAmtAlertMsg = true;
        this.AddCommitmentReceipt.get('amount').setValue('');
      }
      else {
        this.receiptAmtAlertMsg = false;
      }
    }
    else if(receipt_type=='Before Enrol Receipt') {
      if(receiptAmt > (this.BeforeEnrolReceiptAmt)) {
        this.BeforeEnrolReleaseAmtAlertMsg = true;
        this.AddCommitmentReceipt.get('amount').setValue('');
      }
      else {
        this.BeforeEnrolReleaseAmtAlertMsg = false;
      }
      if(receiptAmt > (this.pendingwithbonusandPenalty)) {
        this.receiptAmtAlertMsg = true;
        this.AddCommitmentReceipt.get('amount').setValue('');
      }
      else {
        this.receiptAmtAlertMsg = false;
      }
    }
    else {
     
      if(receiptAmt > (this.pendingwithbonusandPenalty)){
      
        this.advanceamount = parseFloat(receiptAmt) - this.pendingwithbonusandPenalty;
        this.AddCommitmentReceipt.get('balance_advance_amount').setValue(this.advanceamount);
      }
      else if(receiptAmt <= (this.pendingwithbonusandPenalty)){
        this.receiptAmtAlertMsg = false;
        this.advanceamount = parseFloat(receiptAmt) - this.pendingwithbonusandPenalty;
      }
      else {
        
        this.receiptAmtAlertMsg = true;
        this.AddCommitmentReceipt.get('amount').setValue('');
      }
      /*if(receiptAmt > (this.pendingwithbonusandPenalty)) {
        this.receiptAmtAlertMsg = true;
        this.AddReceipt.get('amount').setValue('');
      }
      else {
        this.receiptAmtAlertMsg = false;
      }*/
      
    }
    
   
      const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
    for (let i = 0; i < installArray.length; i++) {
      receiptAmt = this.setDeductions(i,receiptAmt);
    }
    }
    
  

  setDeductions(i,receiptAmt) {
    const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
    let pending = this.pendingDetail[i].inst_amt_wise_pending;
    let penalty = this.pendingDetail[i].penalty_amounts; 
    let bonus = this.pendingDetail[i].bonus;  
    if(parseInt(bonus)>0) {
      //receiptAmt = parseInt(receiptAmt) + parseInt(bonus);
      installArray.controls[i].get('bonus_inst_wise').setValue(bonus);
    }
    if(parseInt(penalty) > 0) {
      
        receiptAmt = parseInt(receiptAmt) 
     
    }
    if(parseInt(pending)>0) {
      if(parseInt(receiptAmt) >= parseInt(pending)) {
        let val = parseInt(pending) - parseInt(bonus);
        installArray.controls[i].get('received_amount').setValue(val);
        receiptAmt = parseInt(receiptAmt) - parseInt(pending) + parseInt(bonus);
      }
      else {
        installArray.controls[i].get('received_amount').setValue(receiptAmt);
        receiptAmt = 0;
      }
    }
    return receiptAmt;
  }

  /*onAmtChange(event) { 
    let receiptAmt = event.target.value;
    this.totalAmt = receiptAmt;
    if(receiptAmt > this.totalPendingAmt) {
      this.receiptAmtAlertMsg = true;
      this.AddCommitmentReceipt.get('amount').setValue('')
    }
    else {
      this.receiptAmtAlertMsg = false;
      const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
      for (let i = 0; i < installArray.length; i++) {
        let pending = this.pendingDetail[i].inst_amt_wise_pending;
        if(receiptAmt > pending) {
          installArray.controls[i].get('received_amount').setValue(pending);
          receiptAmt = receiptAmt - pending;
        }
        else {
          let receivedAmt = receiptAmt;
          installArray.controls[i].get('received_amount').setValue(receivedAmt);
          receiptAmt = 0;
        }
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
    else  if (event.target.value == 4){
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
      "conditions[tenant_id]" : this.AddCommitmentReceipt.get('tenant_id').value,
      "conditions[branch_id]" : this.AddCommitmentReceipt.get('branch_id').value,
    }
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    let paymentType = event.target.value;
    //cash - 1
    let debitTo = this.AddCommitmentReceipt.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.AddCommitmentReceipt.get('cheque_no');
    let chequeDate = this.AddCommitmentReceipt.get('cheque_date');
    let bankId = this.AddCommitmentReceipt.get('bank_name_id');
    let bankBranchName = this.AddCommitmentReceipt.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.AddCommitmentReceipt.get('transaction_no');
    let transactionDate = this.AddCommitmentReceipt.get('transaction_date');
    if(paymentType=='1') {
      debitTo.setValidators([Validators.required,Validators.pattern('^[a-zA-Z0-9_]+$')]);
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
      }  catch (err) { }
    }, (err: any) => { });

  }

  customerChg(event: Event)
  {
    const el = event.target as HTMLSelectElement;
    const id = el.value;
    if (!id) {
      this.CustomerId = null;
      this.enrollementList = [];
      this.hideChitDetails = true;
      this.hidePayment = true;
      this.hideDebitTo=true;
      this.hideCheque=true;
      this.hideBank=true;
      this.hideTransaction=true;
      this.AddCommitmentReceipt.get('enrollment_id').setValue('');
      return;
    }
    this.CustomerId = id;
    this.AddCommitmentReceipt.get('enrollment_id').setValue('');
    this.hideamount = true;
    let cond = {
      id : this.CustomerId
    }
    let cond1 = {
      "customer_id" : this.CustomerId
      }
      this.userservice.getData(cond, "get-customer-slab-type", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollementList = (response.data || []).map((row: any) => {
            row.slab_display = this.slabLabelWithAr(row);
            return row;
          });
          this.hideChitDetails = true;
          this.hidePayment = true;
          this.hideDebitTo=true;
          this.hideCheque=true;
          this.hideBank=true;
          this.hideTransaction=true;
        } else {
          this.enrollementList = [];
          this.hideChitDetails = true;
          this.hidePayment = true;
          this.hideDebitTo=true;
          this.hideCheque=true;
          this.hideBank=true;
          this.hideTransaction=true;
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond1, "get-before-enrol-receipts", "POST").subscribe((response: any) => {
      try { 
      if (response.status == 'Success') { 
      this.BeforeEnrolReceiptAmt = response.data.available_advance_before_enrol;
      } else {
      this.BeforeEnrolReceiptAmt = [];
      }
      } catch (err) { }
      }, (err: any) => { });
  }

  receiptTypeChg(event) {
    let receipt_type = event.target.value;
    console.log(receipt_type);
    this.AdvanceReleaseAmtAlertMsg = false;
    this.BeforeEnrolReleaseAmtAlertMsg = false;
    this.receiptAmtAlertMsg = false;
    this.AddCommitmentReceipt.get('amount').setValue("");
    const installArray = < FormArray > this.AddCommitmentReceipt.get('Installments');
    for (let i = 0; i < installArray.length; i++) {
      installArray.controls[i].get('bonus_inst_wise').setValue('');
      installArray.controls[i].get('penalty_inst_wise').setValue('');
      installArray.controls[i].get('received_amount').setValue('');
    }
    if(receipt_type=='Advanced Receipt') {
      this.hideamount=false;
      this.hideChitDetails=false;
      this.hidePayment = false;
      this.hideAdvancedamount=false;
      this.hideAdvancedamount1=true;
      this.AddCommitmentReceipt.get('receipt_type').setValue("Advanced Receipt");
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
      this.hideAdvancedamount1=true;
      this.AddCommitmentReceipt.get('receipt_type').setValue("Normal Receipt");
    }
    else if(receipt_type=='Before Enrol Receipt') {
      this.hideamount=false;
      this.hideChitDetails=false;
      this.hidePayment = false;
      this.hideAdvancedamount=true;
      this.hideAdvancedamount1=false;
      this.AddCommitmentReceipt.get('receipt_type').setValue("Before Enrol Receipt");
      if(event.target.value="Before Enrol Receipt")
      {
      this.AdvancedReceipt="Advanced Enrol";
      }
      else{
      this.NormalReceipt="normal"
      }
    }
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCommitmentReceipt.value);
    if (this.AddCommitmentReceipt.invalid) 
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
      /* else if(this.f.slab_id.errors) {
        this.focus6.nativeElement.focus();
      } 
      else if(this.f.ticket_no.errors) {
        this.focus7.nativeElement.focus();
      }*/
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
      else if(this.f.bank_branch_name.errors) {
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
      else if(this.f.received_amount.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.penalty_inst_wise.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.bonus_inst_wise.errors) {
        this.focus24.nativeElement.focus();
      }
      else if(this.f.discount_inst_wise.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.discount_penalty_wise.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.pending_days.errors) {
        this.focus27.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result, "store-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Commitment Receipt",response.msg, "success");
          setTimeout(() => {
            
      this.receipt_response = response.receipt_no;
    this.receipt_identify = response.receipt_identify;

   
      this.router.navigate(['/admin-panel/commitment-receipt-print/'+ this.receipt_response]);
    
          }, 300);
        } else {
          this.appService.showAlert("Commitment Receipt", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Commitment Receipt", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commitment Receipt", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitment Receipt", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCommitmentReceipt = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      other_branch: [''],
      customer_id: ['', Validators.required],
      enrollment_id: ['', Validators.required],
      balance_advance_amount: [''],
     // slab_id: ['', Validators.required],
     // ticket_no: [''],
     receipt_type:['', Validators.required],
      employee_id: ['', Validators.required],
      receipt_date: [this.currentDate,Validators.required],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      alt_rec_no: [''],
      debit_to: [''],
      amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      remarks: [''],
      created_by: [''],
      'Installments': this.formBuilder.array([
        this.formBuilder.group({
          'auction_id': [''],
          'installment_no': [''],
          'inst_amt_wise_pending': [''],
          'received_amount': ['', [Validators.pattern(this.decimalPattern)]],
          'penalty_inst_wise': [''],
          'bonus_inst_wise': [''],
          'discount_inst_wise': [''],
          'discount_penalty_wise': [''],
          'pending_days': [''],
        })
      ]),
    });
   
    this.totalPendingAmt = 0;
    this.pendingwithbonusandPenalty=0;
    this.totalBonusAmt=0;
    this.totalPenaltyAmt=0;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCommitmentReceipt.get('tenant_id').setValue(tenant_id);
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
    this.userservice.getData("", "list-payment-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.paymentTypeList = response.data;
        } else {
          this.paymentTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.AddCommitmentReceipt.get('created_by').setValue(localStorage.getItem('logged_user_id'));

  }
}
