import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-pigmy-collection',
  templateUrl: './add-pigmy-collection.component.html',
  styles: []
})
export class AddPigmyCollectionComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('employee_id') focus4:ElementRef;
  @ViewChild('received_date') focus6:ElementRef;
  @ViewChild('payment_type_id') focus8:ElementRef;
  @ViewChild('debit_to') focus9:ElementRef;
  @ViewChild('cheque_no') focus10:ElementRef;
  @ViewChild('cheque_date') focus11:ElementRef;
  @ViewChild('bank_id') focus12:ElementRef;
  @ViewChild('bank_branch_name') focus13:ElementRef;
  @ViewChild('transaction_no') focus14:ElementRef;
  @ViewChild('transaction_date') focus15:ElementRef;
  @ViewChild('amount') focus16:ElementRef;
  @ViewChild('bank_name_1') focus17:ElementRef;
  @ViewChild('bank_branch_name_1') focus18:ElementRef;
  
  AddBeforeEnrolReceipt: FormGroup;
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

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
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
    return this.AddBeforeEnrolReceipt.controls;
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

  //this.AddBeforeEnrolReceipt.get('created_by').setValue(localStorage.getItem('logged_user_id'));
	if(event.target.value == localStorage.getItem('branch_id')){
    let cond1 = {
      "conditions[id]" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond1, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.employeeList = response.data;
		  this.AddBeforeEnrolReceipt.get('employee_id').setValue(localStorage.getItem('logged_employee_id'));
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  else{
    let cond1 = {
      "conditions[branch_id]" : localStorage.getItem('branch_id')
    }
    this.userservice.getData(cond1, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.AddBeforeEnrolReceipt.get('employee_id').setValue('');
          this.employeeList = response.data;
		  //this.AddBeforeEnrolReceipt.get('employee_id').setValue(localStorage.getItem('logged_employee_id'));
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  
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
          this.dropdownList = [];
          for(let i=0; i<((this.customerList).length); i++) {
              var obj = {};
              obj["customer_id"] = response.data[i].id;
              obj["name"] = response.data[i].full_name;
              this.dropdownList.push(obj);
            }
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  customerChg(item: any)
  {
    let custname = item;
    this.custid = custname.customer_id;
    let cond = {
      "conditions[customer_id]" : this.custid
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
    let debitTo = this.AddBeforeEnrolReceipt.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.AddBeforeEnrolReceipt.get('cheque_no');
    let chequeDate = this.AddBeforeEnrolReceipt.get('cheque_date');
    let bankId = this.AddBeforeEnrolReceipt.get('bank_id');
    let bankBranchName = this.AddBeforeEnrolReceipt.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.AddBeforeEnrolReceipt.get('transaction_no');
    let transactionDate = this.AddBeforeEnrolReceipt.get('transaction_date');
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

  bankChg(event)
  {
    this.AddBeforeEnrolReceipt.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddBeforeEnrolReceipt.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddBeforeEnrolReceipt.value);
    if (this.AddBeforeEnrolReceipt.invalid) 
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
      else if(this.f.received_date.errors) {
        this.focus6.nativeElement.focus();
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
      else if(this.f.bank_name_1.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.bank_branch_name_1.errors) {
      this.focus18.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "pigmy-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Pigmy Receipt",response.msg, "success");
          setTimeout(() => {
            this.receipt_response = response.receipt_no;
          this.router.navigate(['/admin-panel/pigmy-receipt-print/'+ this.receipt_response]);
          }, 300);
        } else {
          this.appService.showAlert("Pigmy Receipt", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Pigmy Receipt", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Pigmy Receipt", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Pigmy Receipt", "Sorry!! Something went wrong!", "error");
      }
    });
  }


  ngOnInit() {
    this.AddBeforeEnrolReceipt = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: [''],
      employee_id: [''],
      received_date: [this.currentDate, Validators.required],
      amount:['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      payment_type_id: ['', Validators.required],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''], 
      bank_branch_name: [''], 
      bank_name_1: [''],
      bank_branch_name_1: [''],
      transaction_no: [null], 
      transaction_date: [0], 
      created_by: [''],
    });
  
    let tenant_id = localStorage.getItem('tenant_id');
    let branch_id = localStorage.getItem('branch_id');
    let cond = {
      "tenant_id" : localStorage.getItem('tenant_id')
    }
    if(tenant_id) {
      this.AddBeforeEnrolReceipt.get('tenant_id').setValue(tenant_id);
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
   
    this.userservice.getData(cond, "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  
    this.userservice.getData(cond, "list-charge-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.chargesTypeList = response.data;
        } else {
          this.chargesTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.dropdownSettings = {
      singleSelection: true,
      idField: 'customer_id',
      textField: 'name',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 1,
      allowSearchFilter: true
    };
    
  }
}
