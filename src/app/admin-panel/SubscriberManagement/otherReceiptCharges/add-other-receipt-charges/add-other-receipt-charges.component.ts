import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-other-receipt-charges',
  templateUrl: './add-other-receipt-charges.component.html',
  styles: []
})
export class AddOtherReceiptChargesComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('employee_id') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;
  @ViewChild('received_date') focus6:ElementRef;
  @ViewChild('charge_type_id') focus7:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;
  @ViewChild('debit_to') focus9:ElementRef;
  @ViewChild('cheque_no') focus10:ElementRef;
  @ViewChild('cheque_date') focus11:ElementRef;
  @ViewChild('bank_id') focus12:ElementRef;
  @ViewChild('bank_branch_name') focus13:ElementRef;
  @ViewChild('transaction_no') focus14:ElementRef;
  @ViewChild('transaction_date') focus15:ElementRef;
  @ViewChild('amount') focus16:ElementRef;
  @ViewChild('receipt_no') focus17:ElementRef;
  
  AddOtherReceiptCharges: FormGroup;
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
  employeeList: any[];
  bankList: any;
  customerList: any;
  enrollementList: any;
  chargesTypeList: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  currentDate:any;
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
    return this.AddOtherReceiptCharges.controls;
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

  branchchg(event) {
  let branch_id = event.target.value;
  this.setCustomerList(branch_id);
  }

  setCustomerList(branch_id)
  {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  customerChg(event)
  {
    let cond = {
      "conditions[customer_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollementList = response.data;
        } else {
          this.enrollementList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  paymentChg(event) { 
    let paymentType = event.target.value;
    //cash - 1
    let debitTo = this.AddOtherReceiptCharges.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.AddOtherReceiptCharges.get('cheque_no');
    let chequeDate = this.AddOtherReceiptCharges.get('cheque_date');
    let bankId = this.AddOtherReceiptCharges.get('bank_id');
    let bankBranchName = this.AddOtherReceiptCharges.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.AddOtherReceiptCharges.get('transaction_no');
    let transactionDate = this.AddOtherReceiptCharges.get('transaction_date');
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

  bankChg(event)
  {
    this.AddOtherReceiptCharges.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddOtherReceiptCharges.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddOtherReceiptCharges.value);
    if (this.AddOtherReceiptCharges.invalid) 
    {  
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) { 
        this.focus4.nativeElement.focus();
      }
      else if(this.f.enrollment_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.received_date.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.charge_type_id.errors) {
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
      else if(this.f.amount.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.receipt_no.errors) {
        this.focus17.nativeElement.focus();
      }
    
      return;
    }
    this.userservice.getData(result, "store-other-charge-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Other Receipt Charges",response.msg, "success");
          setTimeout(() => {
            this.receipt_response = response.receipt_no;
            this.router.navigate(['/admin-panel/other-receipt-print/'+ this.receipt_response]);
          }, 300);
        } else {
          this.appService.showAlert("Other Receipt Charges", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Other Receipt Charges", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
      }
    });
  }


  ngOnInit() {
    this.AddOtherReceiptCharges = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: [''],
      employee_id: ['', Validators.required],
      receipt_no:['', Validators.required],
      enrollment_id: [''],
      received_date: [this.currentDate, Validators.required],
      charge_type_id: ['', Validators.required],
      amount:['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      payment_type_id: ['', Validators.required],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''], 
      bank_branch_name: [''], 
      transaction_no: [null], 
      transaction_date: [0], 
      created_by: [''],
    });
  
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddOtherReceiptCharges.get('tenant_id').setValue(tenant_id);
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
    this.AddOtherReceiptCharges.get('created_by').setValue(localStorage.getItem('logged_user_id'));
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
  
    this.userservice.getData('', "list-charge-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.chargesTypeList = response.data;
        } else {
          this.chargesTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    
  }
}



