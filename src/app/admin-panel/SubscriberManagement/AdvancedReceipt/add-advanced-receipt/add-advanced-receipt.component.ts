import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-advanced-receipt',
  templateUrl: './add-advanced-receipt.component.html',
  styles: []
})
export class AddAdvancedReceiptComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('receipt_date') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('employee_id') focus5:ElementRef;
  @ViewChild('enrollment_id') focus6:ElementRef;
  @ViewChild('receipt_amount') focus7:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;
  @ViewChild('debit_to') focus9:ElementRef;
  @ViewChild('cheque_no') focus10:ElementRef;
  @ViewChild('cheque_date') focus11:ElementRef;
  @ViewChild('bank_id') focus12:ElementRef;
  @ViewChild('bank_branch_name') focus13:ElementRef;
  @ViewChild('transaction_no') focus14:ElementRef;
  @ViewChild('transaction_date') focus15:ElementRef;

  AddAdvancedReceipt: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  customerList: any[];
  groupList: any[];
  employeeList: any[];
  bankList: any;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hidePayment: boolean=true;
  hideTotalAdvanceAmt: boolean=true;
  hideReceiptAmt: boolean=true;
  enrollementList: any;
  AdvanceReceiptAmt: any;
  ReceiptAmt: any;
  TotalAdvanceAmt: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  currentDate:any;

  custname: any;
  custid: any;
  receipt_response: any;


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
    return this.AddAdvancedReceipt.controls;
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

branchChg(event)
{
  let branch_id = event.target.value;
  this.setEmployeeList(branch_id);
  this.setCustomerList(branch_id);
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


setCustomerList(branch_id)
{
  let cond = {
    "conditions[branch_id]" : branch_id
  }
  this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
    try { 
      if (response.status == 'Success') { 
        this.customerList = response.data;
      } else {
        this.customerList = [];
      }
    }  catch (err) { }
  }, (err: any) => { });
}
customerChg(event: Event)
{
  const el = event.target as HTMLSelectElement;
  const id = el.value;
  if (!id) {
    this.enrollementList = [];
    this.AddAdvancedReceipt.get('receipt_amount').setValue('');
    this.hideTotalAdvanceAmt=true;
    return;
  }
  this.custid = id;
  let customer_id = this.custid ;
  this.setEnrollementList(customer_id);
  this.AddAdvancedReceipt.get('receipt_amount').setValue('');
  this.hideTotalAdvanceAmt=true;
}
setEnrollementList(customer_id)
{
  let cond = {
    "conditions[customer_id]" : customer_id
  }
  this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
    try { 
      if (response.status == 'Success') { 
        this.enrollementList = response.data;
        console.log(response.data)
      } else {
        this.enrollementList = [];
      }
    }  catch (err) { }
  }, (err: any) => { });
}
enrollChg(event)
{
  let enrollment_id = event.target.value;
  this.setAdvanceAmtList(enrollment_id);
  this.AddAdvancedReceipt.get('receipt_amount').setValue('');
  this.hideTotalAdvanceAmt=true;
  this.hideReceiptAmt=false;
}
 
setAdvanceAmtList(enrollment_id)
{
  let cond = {
    "enrollment_id" : enrollment_id
  }
  this.userservice.getData(cond, "get-available-advance-details", "POST").subscribe((response: any) => {
    try { 
      if (response.status == 'Success') { 
        this.AdvanceReceiptAmt = response.data.available_advance;
        console.log(response.data)
      } else {
        this.AdvanceReceiptAmt = [];
      }
    }  catch (err) { }
  }, (err: any) => { });
}

bankChg(event)
  {
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddAdvancedReceipt.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

onAmtChange(event)
{
  let Amt=event.target.value;
  //this.ReceiptAmt=Amt;
  if(Amt == ''){
    Amt=0;
  }
  let AdvanceAmt= this.AdvanceReceiptAmt;
  if(AdvanceAmt == ''){
    AdvanceAmt=0;
  }
  //this.AdvanceReceiptAmt=AdvanceAmt;
  this.TotalAdvanceAmt=(parseInt(Amt))+(parseInt(AdvanceAmt));
  this.hideTotalAdvanceAmt=false;
  this.hidePayment=false;
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
    "conditions[tenant_id]" : this.AddAdvancedReceipt.get('tenant_id').value,
    "conditions[branch_id]" : this.AddAdvancedReceipt.get('branch_id').value,
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
  let debitTo = this.AddAdvancedReceipt.get('debit_to');
  //cheque, DD - 2,3
  let chequeNo = this.AddAdvancedReceipt.get('cheque_no');
  let chequeDate = this.AddAdvancedReceipt.get('cheque_date');
  let bankId = this.AddAdvancedReceipt.get('bank_id');
  let bankBranchName = this.AddAdvancedReceipt.get('bank_branch_name');
  //NEFT - 4
  let transactionNo = this.AddAdvancedReceipt.get('transaction_no');
  let transactionDate = this.AddAdvancedReceipt.get('transaction_date');
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
onSubmit() {
  this.SubmitbuttonName = "Loading...";
  this.submitted = true;
  this.disableBtn = true;
  const result = Object.assign({}, this.AddAdvancedReceipt.value);
  if (this.AddAdvancedReceipt.invalid) 
  {  
    this.SubmitbuttonName = "Submit";
    this.disableBtn = false;
    if(this.f.tenant_id.errors) {
      this.focus1.nativeElement.focus();
    }
    else if(this.f.branch_id.errors) {
      this.focus2.nativeElement.focus();
    }
    else if(this.f.receipt_date.errors) {
      this.focus3.nativeElement.focus();
    }
    else if(this.f.customer_id.errors) { 
      this.focus4.nativeElement.focus();
    }
    else if(this.f.employee_id.errors) {
      this.focus5.nativeElement.focus();
    }
    else if(this.f.enrollment_id.errors) {
      this.focus6.nativeElement.focus();
    }
    else if(this.f.receipt_amount.errors) {
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
  this.userservice.getData(result, "store-advance-receipt", "JSON").subscribe((response: any) => {
    this.SubmitbuttonName = "Submit";
    try {
      if (response.status == 'Success') {
        this.appService.showAlert("Advanced Receipt",response.msg, "success");
        setTimeout(() => {
          this.receipt_response = response.receipt_no;
          this.router.navigate(['/admin-panel/advance-receipt-print/'+ this.receipt_response]);
        }, 300);
      } else {
        this.appService.showAlert("Advanced Receipt", response.msg, "error");
        this.disableBtn = false;
      }
    } catch (err) {
      this.appService.showAlert("Advanced Receipt", 'Sorry!! Something went wrong!', "error");
      this.disableBtn = false;
    }
  }, (err: any) => {
    this.disableBtn = false;
    this.SubmitbuttonName = "Submit";
    if (err.status == 401) {
      this.appService.showAlert("Advanced Receipt", "Sorry!! Something went wrong!", "error");
    }
    else {
      this.appService.showAlert("Advanced Receipt", "Sorry!! Something went wrong!", "error");
    }
  });
}

ngOnInit() {
  this.AddAdvancedReceipt = this.formBuilder.group({
    tenant_id: ['', Validators.required],
    branch_id: ['', Validators.required],
    receipt_date: [this.currentDate,Validators.required],
    customer_id: ['', Validators.required],
    employee_id: ['', Validators.required],
    enrollment_id: ['', Validators.required],
    receipt_amount:['',[Validators.required,Validators.pattern(this.decimalPattern)]],
    payment_type_id:['', Validators.required],
    debit_to: [''],
    cheque_no: [''],
    cheque_date: [null],
    bank_id: [''],
    bank_branch_name: [''],
    transaction_no: [''],
    transaction_date: [null],
    receipt_mode:['erp'],
    created_by: [''],
  });
  let tenant_id = localStorage.getItem('tenant_id');
  if(tenant_id) {
    this.AddAdvancedReceipt.get('tenant_id').setValue(tenant_id);
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
  this.AddAdvancedReceipt.get('created_by').setValue(localStorage.getItem('logged_user_id'));

}
}
