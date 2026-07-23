import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-petty-cash-expense',
  templateUrl: './update-petty-cash-expense.component.html',
  styles: []
})

export class UpdatePettyCashExpenseComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('expenses_date') focus4:ElementRef;
  @ViewChild('expenses_amount') focus5:ElementRef;
  @ViewChild('expenses_type_id') focus6:ElementRef;
  @ViewChild('remarks') focus7:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;

  UpdatePettyCashExpense: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  pettycashexpenseId: any;
  expensesList: any;
  branchList: any;
  balanceAmount: any;
  employeeList: any;
  availbleCashAllorate: any;
  BankList: any;
  bankBranchList: any[];
  paymentTypeList: any;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideRemark: boolean=true;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  bankList: any;
  bankBranchName: any;
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
    return this.UpdatePettyCashExpense.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setExpenseList(tenant_id);
    this.setPaymentList(tenant_id);
  }

  branchChg(event) {
    let branch_id = event.target.value;
    this.setEmployeeList(branch_id);
    this.setBankBranchList(branch_id);
    this.setAvailableAmountList(branch_id);
  }
  
  setEmployeeList(branch_id) { 
    let cond = {
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
  bankChg(event)
  {
    this.UpdatePettyCashExpense.get('bank_branch_name').setValue('');
    let bankDet =  this.BankList.filter((listing: any) => listing.id == event.target.value);
    this.UpdatePettyCashExpense.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
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
  }

  setPaymentList(tenant_id){ 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "list-payment-types", "POST").subscribe((response: any) => {
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
    let expenseamount = this.UpdatePettyCashExpense.get('expenses_amount').value;
    let availablecash =  this.balanceAmount;
    if((parseInt(availablecash)) < (parseInt(expenseamount))) {
      this.availbleCashAllorate = true;
    }
    else {
      this.availbleCashAllorate = false;
    }
  }
   
  setBankBranchList(branch_id){ 
    let cond = {
      "conditions[branch_id]" : branch_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.BankList = response.data;
        } else {
          this.BankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  paymentChg(event)
  {
    this.setPayments(event.target.value);
    let cond = {
      "conditions[tenant_id]" : this.UpdatePettyCashExpense.get('tenant_id').value,
      "conditions[branch_id]" : this.UpdatePettyCashExpense.get('branch_id').value,
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
  }

  setPayments(paymentTypeId) {
    if (paymentTypeId == 1) {
      this.hideDebitTo=false;
      this.hideCheque=true;
      this.hideBank=true;
      this.hideTransaction=true;
    }
    else if (paymentTypeId == 2) {
      this.hideDebitTo=true;
      this.hideCheque=false;
      this.hideBank=false;
      this.hideTransaction=true;
    }
    else if (paymentTypeId == 3) {
      this.hideDebitTo=true;
      this.hideCheque=false;
      this.hideBank=false;
      this.hideTransaction=true;
    }
    else  if (paymentTypeId == 4){
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
    let debitTo = this.UpdatePettyCashExpense.get('debit_to');
    let chequeNo = this.UpdatePettyCashExpense.get('cheque_no');
    let chequeDate = this.UpdatePettyCashExpense.get('cheque_date');
    let bankId = this.UpdatePettyCashExpense.get('bank_id');
    let bankBranchName = this.UpdatePettyCashExpense.get('bank_branch_name');
    let transactionNo = this.UpdatePettyCashExpense.get('transaction_no');
    let transactionDate = this.UpdatePettyCashExpense.get('transaction_date');
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
    const result = Object.assign({}, this.UpdatePettyCashExpense.value);
    if (this.UpdatePettyCashExpense.invalid) 
    {
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
      return;
    }
    this.userservice.getData(result,"update-petty-cash-expense","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Petty Cash Expense", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-petty-cash-expense']);
          }, 3000);
        } else {
          this.appService.showAlert("Petty Cash Expense", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
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
    this.UpdatePettyCashExpense = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id:['', Validators.required],
      expenses_date: [this.currentDate, Validators.required], 
      expenses_amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      expenses_type_id: ['', Validators.required],
      remarks: [''],
      payment_type_id: ['', Validators.required],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_date: [null],
      transaction_no: [''],
      id: [''],
      updated_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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
      this.pettycashexpenseId = params.id;
      this.UpdatePettyCashExpense.get('id').setValue(this.pettycashexpenseId);
      this.UpdatePettyCashExpense.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.pettycashexpenseId 
      }
      this.userservice.getData(cond, "get-petty-cash-expenses","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdatePettyCashExpense.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdatePettyCashExpense.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdatePettyCashExpense.get('employee_id').setValue(response.data[0].employee_id);
            if(response.data[0].expenses_date!=''){
              let introDate = (response.data[0].expenses_date).split('/');
              let setIntroDate = {
                'year': parseInt(introDate[2]),
                'month': parseInt(introDate[1]),
                'day': parseInt(introDate[0])
              } 
              this.UpdatePettyCashExpense.get('expenses_date').setValue(setIntroDate);
            }
            this.UpdatePettyCashExpense.get('expenses_amount').setValue(response.data[0].expenses_amount);
            this.UpdatePettyCashExpense.get('expenses_type_id').setValue(response.data[0].expenses_type_id);
            this.UpdatePettyCashExpense.get('remarks').setValue(response.data[0].remarks);
            this.UpdatePettyCashExpense.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdatePettyCashExpense.get('debit_to').setValue(response.data[0].debit_to);
            this.setPayments(response.data[0].payment_type_id);
            this.UpdatePettyCashExpense.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!=''){
              let cDate = (response.data[0].cheque_date).split('/');
              let setCdate = {
                'year': parseInt(cDate[2]),
                'month': parseInt(cDate[1]),
                'day': parseInt(cDate[0])
              }
              this.UpdatePettyCashExpense.get('cheque_date').setValue(setCdate);
            }
            this.UpdatePettyCashExpense.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdatePettyCashExpense.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            if(response.data[0].transaction_date!=''){
              let tDate = (response.data[0].transaction_date).split('/');
              let setTdate = {
                'year': parseInt(tDate[2]),
                'month': parseInt(tDate[1]),
                'day': parseInt(tDate[0])
              }
              this.UpdatePettyCashExpense.get('transaction_date').setValue(setTdate);
            }
            this.UpdatePettyCashExpense.get('transaction_no').setValue(response.data[0].transaction_no);
            this.setBranchList(response.data[0].tenant_id);
            this.setExpenseList(response.data[0].tenant_id);
            this.setPaymentList(response.data[0].tenant_id);
            this.setAvailableAmountList(response.data[0].branch_id);
            this.setEmployeeList(response.data[0].branch_id);
            this.setBankBranchList(response.data[0].branch_id);
          }
          else { 
            this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => { 
        if (err.status == 401) {
          this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
