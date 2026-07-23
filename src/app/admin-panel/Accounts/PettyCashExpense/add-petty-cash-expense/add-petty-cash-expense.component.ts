import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-petty-cash-expense',
  templateUrl: './add-petty-cash-expense.component.html',
  styles: []
})

export class AddPettyCashExpenseComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('expenses_date') focus4:ElementRef;
  @ViewChild('expenses_amount') focus5:ElementRef;
  @ViewChild('expenses_type_id') focus6:ElementRef;
  @ViewChild('remarks') focus7:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;
  @ViewChild('debit_to') focus9:ElementRef;
  @ViewChild('cheque_no') focus10:ElementRef;
  @ViewChild('cheque_date') focus11:ElementRef;
  @ViewChild('bank_id') focus12:ElementRef;
  @ViewChild('bank_branch_name') focus13:ElementRef;
  @ViewChild('transaction_date') focus14:ElementRef;
  @ViewChild('transaction_no') focus15:ElementRef;

  AddPettyCashExpense: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  branchList: any;
  employeeList: any;
  expensesList: any;
  paymentTypeList: any;
  balanceAmount:any;
  availbleCashAllorate: any;
  BankList: any;
  bankBranchList: any[];
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideRemark: boolean=true;
  BranchList: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  bankList: any;
  bankBranchName: any;
  currentDate:any;

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
    return this.AddPettyCashExpense.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setExpenseList(tenant_id);
  }

  branchChg(event) {
    let tenant_id = this.AddPettyCashExpense.get('tenant_id').value;
    let branch_id = event.target.value;
    this.setEmployeeList(tenant_id,branch_id);
    this.setAmountList(tenant_id,branch_id);
    this.setBankBranchList(branch_id);
  }

  setEmployeeList(tenant_id,branch_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[status]" : 1
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

  setAmountList(tenant_id,branch_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[status]" : 1,
    }
    this.userservice.getData(cond, "get-available-amounts", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.balanceAmount = response.data.balance_amount;
        } else {
          this.balanceAmount = 0;
        }
      }  catch (err) { }
    }, (err: any) => { });
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

  setExpenseList(tenant_id){ 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-petty-cash-expense-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.expensesList = response.data;
        } else {
          this.expensesList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  
    this.userservice.getData('', "list-payment-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.paymentTypeList = response.data;
        } else {
          this.paymentTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  checkAvailableCash() {
    let expenseamount = this.AddPettyCashExpense.get('expenses_amount').value;
    let availablecash =  this.balanceAmount;
    if((parseInt(availablecash)) < (parseInt(expenseamount))) {
      this.availbleCashAllorate = true;
      this.AddPettyCashExpense.get('expenses_amount').setValue('');
    }
    else {
      this.availbleCashAllorate = false;
    }
  }

  setAvailableAmountList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-available-amounts", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.balanceAmount = response.data.balance_amount;
        } else {
          this.balanceAmount = 0;
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
 
  setBankBranchList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData("", "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.BankList = response.data;
        } else {
          this.BankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
   
  bankChg(event)
  {
    this.AddPettyCashExpense.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddPettyCashExpense.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  paymentChg(event) { 
    {
      if (event.target.value == 1) {
        this.hideDebitTo=false;
        this.hideCheque=true;
        this.hideBank=true;
        this.hideTransaction=true;
        this.hideRemark=false;
        
      }
      else if (event.target.value == 2) {
        this.hideDebitTo=true;
        this.hideCheque=false;
        this.hideBank=false;
        this.hideTransaction=true;
        this.hideRemark=false;
      }
      else if (event.target.value == 3)  {
        this.hideDebitTo=true;
        this.hideCheque=false;
        this.hideBank=false;
        this.hideTransaction=true;
        this.hideRemark=false;
      }
      else  if (event.target.value == 4) {
        this.hideDebitTo=true;
        this.hideCheque=true;
        this.hideBank=true;
        this.hideTransaction=false;
        this.hideRemark=false;
      }
      else{
        this.hideDebitTo=true;
        this.hideCheque=true;
        this.hideBank=true;
        this.hideTransaction=true;
        this.hideRemark=false;
      }
      let cond = {
        "conditions[tenant_id]" : this.AddPettyCashExpense.get('tenant_id').value,
        "conditions[branch_id]" : this.AddPettyCashExpense.get('branch_id').value,
        "conditions[status]" : 1,
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
      let debitTo = this.AddPettyCashExpense.get('debit_to');
      let chequeNo = this.AddPettyCashExpense.get('cheque_no');
      let chequeDate = this.AddPettyCashExpense.get('cheque_date');
      let bankId = this.AddPettyCashExpense.get('bank_id');
      let bankBranchName = this.AddPettyCashExpense.get('bank_branch_name');
      let transactionNo = this.AddPettyCashExpense.get('transaction_no');
      let transactionDate = this.AddPettyCashExpense.get('transaction_date');
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
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPettyCashExpense.value);
    if (this.AddPettyCashExpense.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;

      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.expenses_date.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.expenses_amount.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.expenses_type_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
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
      else if(this.f.transaction_date.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus15.nativeElement.focus();
      } 
      return;
    } 
    this.userservice.getData(result, "store-petty-cash-expense", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Petty Cash Expense",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-petty-cash-expense']);
          }, 300);
        } else {
          this.appService.showAlert("Petty Cash Expense", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddPettyCashExpense = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id:['', Validators.required],
      expenses_date: [this.currentDate, Validators.required],
      expenses_amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      expenses_type_id: ['', Validators.required],
      payment_type_id: ['', Validators.required],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_date: [null],
      transaction_no: [''],
      remarks: [''],
      created_by: [''],
    });
   
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddPettyCashExpense.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      this.setExpenseList(tenant_id);
      this.setBankBranchList(tenant_id);
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
    let currentDate = new Date();
    let setEdate = {
      'year': currentDate.getFullYear(),
      'month': currentDate.getMonth() + 1,
      'day': currentDate.getDate()
    }
    this.AddPettyCashExpense.get('expenses_date').setValue(setEdate);
    this.AddPettyCashExpense.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
