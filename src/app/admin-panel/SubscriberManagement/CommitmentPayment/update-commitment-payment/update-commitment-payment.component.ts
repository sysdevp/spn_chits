import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormArray, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-commitment-payment',
  templateUrl: './update-commitment-payment.component.html',
  styles: []
})

export class UpdateCommitmentPaymentComponent implements OnInit {
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
  @ViewChild('total_release_amount') focus15:ElementRef;
  @ViewChild('given_amount') focus16:ElementRef;
  @ViewChild('total_amount') focus17:ElementRef;
  @ViewChild('due_pending_amount') focus18:ElementRef;
  @ViewChild('pending_release') focus19:ElementRef;
  @ViewChild('enrollment_id') focus20:ElementRef;
  @ViewChild('employee_id') focus22:ElementRef;

  UpdateCommitmentPayment: FormGroup;
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
  bankBranchList: any[];
  DebitTo: boolean=true;
  ChequeNo: boolean=true;
  ChequeDate: boolean=true;
  BankName: boolean=true;
  BranchName: boolean=true;
  TransactionDate: boolean=true;
  TransactionNo: boolean=true;
  slabTypeList: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  EnrollList: any;
  totalAmt: any;
  slabName: any;
  pendingRelease: any;
  commitmentPaymentId: any;
  releaseAmtAlertMsg: boolean;
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
    return this.UpdateCommitmentPayment.controls;
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setCustomerList(branch_id);
    this.setemployeeList(branch_id);
  }
  setemployeeList(branch_id)
  {
   // console.log("aaaa");
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
  setCustomerList(branch_id) {
   
    let cond = {
      "id" : branch_id
    }
    this.userservice.getData(cond, "get-commitment-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
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

  customerChg(event) {
    let customer_id = event.target.value;
    let enrollment_id = event.target.value;
    this.setSlabTypeList(customer_id,enrollment_id);
  }

  setSlabTypeList(customer_id,enrollment_id) {
    let cond = {
      "id" : customer_id,
      "enrollment_id" : enrollment_id,
    }   
    this.userservice.getData(cond, "get-customer-slab-type", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.slabTypeList = response.data;
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
          this.UpdateCommitmentPayment.get('total_release_amount').setValue(response.data.slab_payment);
          this.UpdateCommitmentPayment.get('given_amount').setValue(response.data.bid_payment_given);
          this.UpdateCommitmentPayment.get('pending_release').setValue(response.data.pending_release);
          this.UpdateCommitmentPayment.get('enrollment_id').setValue(response.data.id);
        } else {
          this.EnrollList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  
  bankChg(event)
  {
    let bankDet =  this.BankList.filter((listing: any) => listing.id == event.target.value);
    this.UpdateCommitmentPayment.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  onAmtChange(event) { 
    let releaseAmt = event.target.value;
    this.totalAmt = releaseAmt;  
    if( this.totalAmt > this.pendingRelease) {
      this.releaseAmtAlertMsg = true;
    }
    else {
      this.releaseAmtAlertMsg = false;
      const installArray = < FormArray > this.UpdateCommitmentPayment.get('pending_release');
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
    this.setPayments(event.target.value)
  }

  setPayments(paymentTypeId) {
    if (paymentTypeId == 1) {
      this.DebitTo=false;
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if (paymentTypeId == 2) {
      this.DebitTo=true;
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if (paymentTypeId == 3) {
      this.DebitTo=true;
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else  if (paymentTypeId == 4){
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
    //cash - 1
    let debitTo = this.UpdateCommitmentPayment.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.UpdateCommitmentPayment.get('cheque_no');
    let chequeDate = this.UpdateCommitmentPayment.get('cheque_date');
    let bankId = this.UpdateCommitmentPayment.get('bank_id');
    let bankBranchName = this.UpdateCommitmentPayment.get('bank_branch_name');
    //NEFT - 4
    let transactionNo = this.UpdateCommitmentPayment.get('transaction_no');
    let transactionDate = this.UpdateCommitmentPayment.get('transaction_date');
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
    const result = Object.assign({}, this.UpdateCommitmentPayment.value);
    if (this.UpdateCommitmentPayment.invalid) 
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
      else if(this.f.total_release_amount.errors) {
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
      return;
    }

    this.userservice.getData(result, "update-commitment-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Prized Payment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-commitment-payment']);
          }, 300);
        } else {
          this.appService.showAlert("Prized Payment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Prized Payment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateCommitmentPayment = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      total_release_amount: [''],
      given_amount: [''],
      total_amount: [''],
      due_pending_amount: [''],
      enrollment_id: [''],
      pending_release: [''],
      payment_date: [this.currentDate, Validators.required],
      slab_type: [''],
      payment_type_id: ['', Validators.required],
      remarks: [''],
      debit_to: [''],
      cheque_no: [''],
      cheque_date: [null],
      bank_id: [''],
      bank_branch_name: [''],
      transaction_date: [null],
      transaction_no: [''],
      id:[''],
      updated_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.UpdateCommitmentPayment.get('tenant_id').setValue(tenant_id);
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
    this.route.params.subscribe(params => {
      this.commitmentPaymentId = params.id;
      this.UpdateCommitmentPayment.get('id').setValue(this.commitmentPaymentId);
      this.UpdateCommitmentPayment.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.commitmentPaymentId 
      }
      this.userservice.getData(cond, "get-commitment-payments","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCommitmentPayment.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateCommitmentPayment.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateCommitmentPayment.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateCommitmentPayment.get('employee_id').setValue(response.data[0].employee_id);
            this.UpdateCommitmentPayment.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdateCommitmentPayment.get('total_amount').setValue(response.data[0].total_amount);
            this.UpdateCommitmentPayment.get('due_pending_amount').setValue(response.data[0].due_pending_amount);
            this.slabName = response.data[0].slab_name[0];
            if(response.data[0].payment_date!='') {
            let PaymentDate = (response.data[0].payment_date).split('/');
            let setPaymentDate = {
              'year': parseInt(PaymentDate[2]),
              'month': parseInt(PaymentDate[1]),
              'day': parseInt(PaymentDate[0])
            } 
            this.UpdateCommitmentPayment.get('payment_date').setValue(setPaymentDate);
            }
            this.UpdateCommitmentPayment.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdateCommitmentPayment.get('debit_to').setValue(response.data[0].debit_to);
            this.setPayments(response.data[0].payment_type_id);
            this.UpdateCommitmentPayment.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!='') {
              let cDate = (response.data[0].cheque_date).split('/');
              let setCdate = {
                'year': parseInt(cDate[2]),
                'month': parseInt(cDate[1]),
                'day': parseInt(cDate[0])
              }
              this.UpdateCommitmentPayment.get('cheque_date').setValue(setCdate);
            }
            this.UpdateCommitmentPayment.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdateCommitmentPayment.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            if(response.data[0].transaction_date!=''){
              let tDate = (response.data[0].transaction_date).split('/');
              let setTdate = {
                'year': parseInt(tDate[2]),
                'month': parseInt(tDate[1]),
                'day': parseInt(tDate[0])
              }
              this.UpdateCommitmentPayment.get('transaction_date').setValue(setTdate);
            }
            this.UpdateCommitmentPayment.get('transaction_no').setValue(response.data[0].transaction_no);
            this.setBranchList(response.data[0].tenant_id);
            this.setCustomerList(response.data[0].branch_id);
            this.setemployeeList(response.data[0].branch_id);
            this.setPaymentTypeList(response.data[0].tenant_id);
            this.setSlabTypeList(response.data[0].customer_id,response.data[0].enrollment_id);
          }
          else {
            this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
