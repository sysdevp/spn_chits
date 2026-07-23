import { Component, OnInit, ElementRef,ViewChild } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-expenses-income',
  templateUrl: './add-expenses-income.component.html',
  styles: []
})
export class AddExpensesIncomeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('entry_date') focus3:ElementRef;
  @ViewChild('remarks') focus4:ElementRef;
  @ViewChild('voucher_no') focus5:ElementRef;
  @ViewChild('debit_amount') focus6:ElementRef;
  @ViewChild('expense_type') focus7:ElementRef;
  
  AddExpensesIncome: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  branchList: any[];
  hidePayment: boolean=true;
  currentDate:any;
  groupList: any[];
  groupLists: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  TotalAmtdebit: any;
  TotalAmtcredit: any;
  hidearray: boolean=true;
  TotalAmts: any;
  TotalCreditAmt: any;
  TotalAmtCredits: any;
  voucher_no_list:any;
 
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
  this.SubmitbuttonName = "Submit";
  let today_date = new Date();
  this.currentDate = {
     'year': today_date.getFullYear(),
     'month': today_date.getMonth()+1,
     'day': today_date.getDate()
  };
  }
 
  get f() {
  return this.AddExpensesIncome.controls;
  }
 
  companyChg(event) {
  let tenant_id = event.target.value;
  this.hidePayment = false;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.groupList = response.data;
          this.groupLists = response.data;
        } else {
          this.groupList = [];
          this.groupLists = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.setBranchList(tenant_id);
  }

  amountChg(index)
  {
    const control = <FormArray>this.AddExpensesIncome.value['debits'].length;
    this.TotalAmtdebit=0;
    for(let i=0;i<this.AddExpensesIncome.value['debits'].length;i++)
      {
        this.TotalAmts = this.AddExpensesIncome.value['debits'][i].debit_amount;
        if(this.TotalAmts == ''){
          this.TotalAmts = 0;
        }
        this.TotalAmtdebit = parseInt(this.TotalAmts) + this.TotalAmtdebit;
        
      }
      //this.AddExpensesIncome.get('payment_mode').setValue('');
      if(this.TotalAmtdebit == this.TotalCreditAmt)
    {
      this.disableBtn = false;
    }
    else
    {
      this.disableBtn = true;
    }
     
  }

  amountcreditChg(index)
  {
    const control = <FormArray>this.AddExpensesIncome.value['credits'].length;
    this.TotalCreditAmt=0;
    for(let i=0;i<this.AddExpensesIncome.value['credits'].length;i++)
      {
        this.TotalAmtCredits = this.AddExpensesIncome.value['credits'][i].credit_amount;
        if(this.TotalAmtCredits == ''){
          this.TotalAmtCredits = 0;
        }
        this.TotalCreditAmt = parseInt(this.TotalAmtCredits) + this.TotalCreditAmt;
        
      }
      console.log("test",this.TotalCreditAmt);
      //this.AddExpensesIncome.get('payment_mode').setValue('');
      if(this.TotalAmtdebit == this.TotalCreditAmt)
    {
      this.disableBtn = false;
    }
    else
    {
      this.disableBtn = true;
    }
  }

  adddebit() {
    const control = <FormArray>this.AddExpensesIncome.controls['debits'];
    control.push(this.initdebit());
  }

  addcredit() {
    const control = <FormArray>this.AddExpensesIncome.controls['credits'];
    control.push(this.initcredit());
  }

  initdebit() {
    return this.formBuilder.group({
      'debit_account_head': [''],
      'debit_remarks': [''],
      'debit_amount': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      'credit_account_head': ['0'],
      'credit_amount': ['0'],
    });
  }

  initcredit() {
    return this.formBuilder.group({
      'debit_account_head': ['0'],
      'debit_amount': ['0'],
      'credit_account_head': [''],
      'credit_remarks': [''],
      'credit_amount': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
    });
  }
  
  get debits() {
    return <FormArray>this.AddExpensesIncome.get('debits');
  }
  
  get credits() {
    return <FormArray>this.AddExpensesIncome.get('credits');
  }

  setBranchList(tenant_id) { 
  let cond = {
  "conditions[tenant_id]" : tenant_id,
  "log_id" : localStorage.getItem('logged_employee_id')
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

  this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
    try { 
      if (response.status == 'Success') {
        this.groupList = response.data;
        this.groupLists = response.data;
      } else {
        this.groupList = [];
        this.groupLists = [];
      }
    }  catch (err) { }
  }, (err: any) => { });

  this.userservice.getData(cond, "get-voucher-no", "POST").subscribe((response: any) => {
    try { 
    if (response.status == 'Success') { 
    this.voucher_no_list = response.data;
    } else {
    this.voucher_no_list = [];
    }
    } catch (err) { }
    }, (err: any) => { });
  }
 
  onSubmit() {
  this.SubmitbuttonName = "Loading...";
  this.submitted = true;
  this.disableBtn = true;
  const result = Object.assign({}, this.AddExpensesIncome.value);
  if (this.AddExpensesIncome.invalid) 
  {
  this.SubmitbuttonName = "Submit";
  this.disableBtn = false;
  if(this.f.tenant_id.errors) {
  this.focus1.nativeElement.focus();
  }
  else if(this.f.branch_id.errors) {
  this.focus2.nativeElement.focus();
  }
  else if(this.f.entry_date.errors) {
  this.focus3.nativeElement.focus();
  }
  else if(this.f.remarks.errors) {
  this.focus4.nativeElement.focus();
  }
  else if(this.f.voucher_no.errors) {
  this.focus5.nativeElement.focus();
  }
  else if(this.f.debit_amount.errors) {
    this.focus6.nativeElement.focus();
    }
  return;
  }
  this.userservice.getData(result, "store-account-expense", "JSON").subscribe((response: any) => {
  this.SubmitbuttonName = "Submit";
  try {
  if (response.status == 'Success') {
  this.appService.showAlert("Expenses Income",response.msg, "success");
  setTimeout(() => {
  this.router.navigate(['/admin-panel/add-expenses-income-report']);
  }, 300);
  } 
  else {
  this.appService.showAlert("Expenses Income", response.msg, "error");
  this.disableBtn = false;
  }
  } catch (err) {
  this.appService.showAlert("Expenses Income", 'Sorry!! Something went wrong!', "error");
  this.disableBtn = false;
  }
  }, 
  (err: any) => {
  this.disableBtn = false;
  this.SubmitbuttonName = "Submit";
  if (err.status == 401) {
  this.appService.showAlert("Expenses Income", "Sorry!! Something went wrong!", "error");
  }
  else {
  this.appService.showAlert("Expenses Income", "Sorry!! Something went wrong!", "error");
  }
  });
  }
 
  ngOnInit() {
  this.AddExpensesIncome = this.formBuilder.group({
  tenant_id: ['', Validators.required],
  branch_id: ['', Validators.required],
  entry_date: [this.currentDate,Validators.required],
  remarks: [''],
  voucher_no: [''],
  expense_type:[''],
  created_by: [''],
    'debits': this.formBuilder.array([
      this.formBuilder.group({
        'debit_account_head': ['', Validators.required],
        'debit_remarks': ['', Validators.required],
        'debit_amount': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
        'credit_account_head': ['0'],
        'credit_remarks': ['0'],
        'credit_amount': ['0'],
      })
    ]),
    'credits': this.formBuilder.array([
      this.formBuilder.group({
        'debit_account_head': ['0'],
        'debit_remarks': ['0'],
        'debit_amount': ['0'],
        'credit_account_head': ['', Validators.required],
        'credit_remarks': ['', Validators.required],
        'credit_amount': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      })
    ])
    });
  
  let tenant_id = localStorage.getItem('tenant_id');
  if(tenant_id) {
  this.AddExpensesIncome.get('tenant_id').setValue(tenant_id);
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
  this.AddExpensesIncome.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  } 
 }
