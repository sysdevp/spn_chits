import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormArray, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-commitment-payment',
  templateUrl: './add-commitment-payment.component.html',
  styles: []
})

export class AddCommitmentPaymentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('payment_date') focus4:ElementRef;
  @ViewChild('payment_type_id') focus5:ElementRef;
  @ViewChild('remarks') focus6:ElementRef;
  @ViewChild('debit_to') focus7:ElementRef;
  @ViewChild('cheque_no') focus8:ElementRef;
  @ViewChild('cheque_date') focus9:ElementRef;
  @ViewChild('bank_id') focus10:ElementRef;
  @ViewChild('bank_branch_name') focus11:ElementRef;
  @ViewChild('transaction_date') focus12:ElementRef;
  @ViewChild('transaction_no') focus13:ElementRef;
  @ViewChild('slab_type') focus14:ElementRef;
  @ViewChild('total_payment_release') focus15:ElementRef;
  @ViewChild('given_amount') focus16:ElementRef;
  @ViewChild('total_amount') focus17:ElementRef;
  @ViewChild('due_pending_amount') focus18:ElementRef;
  @ViewChild('pending_release') focus19:ElementRef;
  @ViewChild('enrollment_id') focus20:ElementRef;
  @ViewChild('payment_month') focus21:ElementRef;
  @ViewChild('employee_id') focus22:ElementRef;

  AddCommitmentPayment: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  customerList: any[];
  employeeList: any[];
  paymentTypeList: any;
  BankList: any;
  DebitTo: boolean=true;
  ChequeNo: boolean=true;
  ChequeDate: boolean=true;
  BankName: boolean=true;
  BranchName: boolean=true;
  TransactionDate: boolean=true;
  TransactionNo: boolean=true;
  slabTypeList: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  EnrollList: any;
  totalAmt: any;
  pendingRelease: any;
  releaseAmtAlertMsg: boolean;
  currentDate:any;
  customerId: any;
  list: any;
  id: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  custname: any;
  custid: any;

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
    return this.AddCommitmentPayment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setPaymentTypeList(tenant_id);
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

  setPaymentTypeList(tenant_id){ 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-payment-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.paymentTypeList = response.data;
        } else {
          this.paymentTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  } 

  branchChg(event) 
  {
    let id  = event.target.value;
    this.setCustomerList(id);
    this.setemployeeList(id);
  }
  setemployeeList(id)
  {
    let cond = {
      "conditions[branch_id]" : id
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
  setCustomerList(id)
  {
    let cond = {
      "id" : id
    }
    this.userservice.getData(cond, "get-commitment-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
          this.dropdownList = [];
          for(let i=0; i<((this.customerList).length); i++) {
              var obj = {};
              obj["customer_id"] = response.data[i].customer_id;
              obj["name"] = response.data[i].customer_name_det;
              this.dropdownList.push(obj);
            }
        } else {
          this.customerList = [];
        }
      }  catch (err) { } 
    }, (err: any) => { });
  

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

  customerChg(item: any) {
    let custname = item;
    this.custid = custname.customer_id;

    let id =this.custid;
    this.setSlabTypeList(id);
  }

  setSlabTypeList(id) {
    let cond = {
      "id" :  id
    }   
    this.userservice.getData(cond, "get-customer-slab-type", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          console.log("dfdgd")
            this.slabTypeList = response.data;
          console.log(this.slabTypeList)
        } else {
          this.slabTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  slabTypeChg(event) {
    let slab_name = event.target.value;
    let id = event.target.value;
    this.setEnrollList(slab_name,id);
  }

  setEnrollList(slab_name,id) {
    let cond = {
      "id" : slab_name,
      "conditions[enrol_id]" : id,
    }
    this.userservice.getData(cond, "get-customer-commitment-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.EnrollList = response.data;
          this.pendingRelease = response.data.pending_release;
          this.AddCommitmentPayment.get('total_payment_release').setValue(response.data.slab_payment);
          this.AddCommitmentPayment.get('given_amount').setValue(response.data.bid_payment_given);
          this.AddCommitmentPayment.get('pending_release').setValue(response.data.pending_release);
          this.AddCommitmentPayment.get('payment_month').setValue(response.data.payment_month);
          this.AddCommitmentPayment.get('enrollment_id').setValue(response.data.id);
        } else {
          this.EnrollList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  
  bankChg(event)
  {
    let bankDet =  this.BankList.filter((listing: any) => listing.id == event.target.value);
    this.AddCommitmentPayment.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
    console.log(bankDet)
  }

  onAmtChange(event) { 
    let releaseAmt = event.target.value;
    this.totalAmt = releaseAmt;  
    if( this.totalAmt > this.pendingRelease) {
      this.releaseAmtAlertMsg = true;
      this.AddCommitmentPayment.get('total_amount').setValue('');
    }
    else {
      this.releaseAmtAlertMsg = false;
      const installArray = < FormArray > this.AddCommitmentPayment.get('pending_release');
      for (let i = 0; i < installArray.length; i++) {
        let pending = this.EnrollList[i].inst_amt_wise_pending;
        if(releaseAmt > pending) {
          installArray.controls[i].get('pending_release').setValue(pending);
          releaseAmt = releaseAmt - pending;
        }
        else {
          let receivedAmt = releaseAmt;
          installArray.controls[i].get('pending_release').setValue(receivedAmt);
          releaseAmt = 0;
        }
      } 
    } 
  }

  paymentChg(event) { 
    let paymentType = event.target.value;
    //cash - 1
    let debitTo = this.AddCommitmentPayment.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.AddCommitmentPayment.get('cheque_no');
    let chequeDate = this.AddCommitmentPayment.get('cheque_date');
    let bankId = this.AddCommitmentPayment.get('bank_id');
    let bankBranchName = this.AddCommitmentPayment.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.AddCommitmentPayment.get('transaction_no');
    let transactionDate = this.AddCommitmentPayment.get('transaction_date');
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
      this.DebitTo=false;
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if(paymentType == 2) {
      this.DebitTo=true;
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if(paymentType == 3) {
      this.DebitTo=true;
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if(paymentType == 4) {
      this.DebitTo=true;
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=false;
      this.TransactionNo=false;
    }
    else{
      this.DebitTo=true;
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCommitmentPayment.value);
    if (this.AddCommitmentPayment.invalid) 
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
      else if(this.f.payment_date.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus6.nativeElement.focus();
      }  
      else if(this.f.debit_to.errors) {
        this.focus7.nativeElement.focus();
      } 
      else if(this.f.cheque_no.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.bank_id.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus13.nativeElement.focus();
      } 
      else if(this.f.slab_type.errors) {
        this.focus14.nativeElement.focus();
      } 
      else if(this.f.total_payment_release.errors) {
        this.focus15.nativeElement.focus();
      } 
      else if(this.f.given_amount.errors) {
        this.focus16.nativeElement.focus();
      } 
      else if(this.f.total_amount.errors) {
        this.focus17.nativeElement.focus();
      } 
      else if(this.f.due_pending_amount.errors) {
        this.focus18.nativeElement.focus();
      } 
      else if(this.f.pending_release.errors) {
        this.focus19.nativeElement.focus();
      } 
      else if(this.f.enrollment_id.errors) {
        this.focus20.nativeElement.focus();
      } 
      else if(this.f.payment_month.errors) {
        this.focus21.nativeElement.focus();
      } 
      else if(this.f.employee_id.errors) {
        this.focus22.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result, "store-commitment-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Commitment Payment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-commitment-payment']);
          }, 300);
        } else {
          this.appService.showAlert("Commitment Payment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Commitment Payment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitment Payment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCommitmentPayment = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      total_payment_release: ['', [Validators.pattern(this.decimalPattern)]],
      given_amount: ['', [Validators.pattern(this.decimalPattern)]],
      total_amount: ['', [Validators.pattern(this.decimalPattern)]],
      due_pending_amount: ['', [Validators.pattern(this.decimalPattern)]],
      enrollment_id: [''],
      pending_release: [''],
      payment_month: [''],
      payment_date: [this.currentDate, Validators.required],
      slab_type: ['', Validators.required],
      payment_type_id: ['', Validators.required],
      remarks: [''],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_date: [null],
      transaction_no: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCommitmentPayment.get('tenant_id').setValue(tenant_id);
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
    this.AddCommitmentPayment.get('created_by').setValue(localStorage.getItem('logged_user_id'));

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
