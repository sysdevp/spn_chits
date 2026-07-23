import { Component, OnInit ,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-unknown-transaction',
  templateUrl: './update-unknown-transaction.component.html',
  styles: []
})
export class UpdateUnknownTransactionComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('received_date') focus3:ElementRef;
  @ViewChild('amount_received') focus4:ElementRef;
  @ViewChild('payment_type_id') focus5:ElementRef;
  @ViewChild('remarks') focus6:ElementRef;

  UpdateUnknownTransaction: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  pettycashincomeId: any;
  branchList: any;
  paymentTypeList: any;
  balanceAmount: any;
  BankList: any;
  bankBranchList: any[];
  ChequeNo: boolean=true;
  ChequeDate: boolean=true;
  BankName: boolean=true;
  BranchName: boolean=true;
  TransactionDate: boolean=true;
  TransactionNo: boolean=true;
  BranchList: any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  bankBranchName: any;
  BankBranchName: boolean;
  bankList: any;
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
    return this.UpdateUnknownTransaction.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setPaymentTypeList(tenant_id);
  }

  branchChg(event) {
    let branch_id = event.target.value;
    this.setAvailableAmountList(branch_id);
    this.setBankBranchList(branch_id);
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
    /*this.userservice.getData("", "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.BankList = response.data;
        } else {
          this.BankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });*/
  }

  /*setBankList(event)
  {
    let bank_id = event.target.value;
    this.bankChg(bank_id)
  }

  bankChg(bank_id)
  {
    console.log(bank_id)
    let cond=
    {
      "conditions[id]" : bank_id,
      
    }
    console.log(cond)
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          //this.bankBranchName = response.data[0].bank_branch_name;
          this.BankList = response.data;
        } else {
          this.BankList = [];
          //this.bankBranchName = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }*/

  bankChg(event)
  {
    this.UpdateUnknownTransaction.get('bank_branch_name').setValue('');
    let bankDet =  this.BankList.filter((listing: any) => listing.id == event.target.value);
    this.UpdateUnknownTransaction.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }


  paymentChg(event) {
    this.setPayments(event.target.value);
    let cond = {
      "conditions[tenant_id]" : this.UpdateUnknownTransaction.get('tenant_id').value,
      "conditions[branch_id]" : this.UpdateUnknownTransaction.get('branch_id').value,
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
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if (paymentTypeId == 2) {
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else if (paymentTypeId == 3) {
      this.ChequeNo=false;
      this.ChequeDate=false;
      this.BankName=false;
      this.BranchName=false;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    else  if (paymentTypeId == 4){
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=false;
      this.TransactionNo=false;
    }
    else{
      this.ChequeNo=true;
      this.ChequeDate=true;
      this.BankName=true;
      this.BranchName=true;
      this.TransactionDate=true;
      this.TransactionNo=true;
    }
    let chequeNo = this.UpdateUnknownTransaction.get('cheque_no');
    let chequeDate = this.UpdateUnknownTransaction.get('cheque_date');
    let bankId = this.UpdateUnknownTransaction.get('bank_id');
    let BranchName = this.UpdateUnknownTransaction.get('bank_branch_name');
    let transactionNo = this.UpdateUnknownTransaction.get('transaction_no');
    let transactionDate = this.UpdateUnknownTransaction.get('transaction_date');
    if(paymentTypeId=='1') {
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      BranchName.setValidators(null);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if((paymentTypeId=='2') || (paymentTypeId=='3')) {
      chequeNo.setValidators([Validators.required]);
      chequeDate.setValidators([Validators.required]);
      bankId.setValidators([Validators.required]);
      BranchName.setValidators([Validators.required]);
      transactionNo.setValidators(null);
      transactionDate.setValidators(null);
    }
    else if(paymentTypeId=='4') {
      chequeNo.setValidators(null);
      chequeDate.setValidators(null);
      bankId.setValidators(null);
      BranchName.setValidators(null);
      transactionNo.setValidators([Validators.required]);
      transactionDate.setValidators([Validators.required]);
    }
    chequeNo.updateValueAndValidity();
    chequeDate.updateValueAndValidity();
    bankId.updateValueAndValidity();
    BranchName.updateValueAndValidity();
    transactionNo.updateValueAndValidity();
    transactionDate.updateValueAndValidity();
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateUnknownTransaction.value);
    if (this.UpdateUnknownTransaction.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.received_date.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.amount_received.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus6.nativeElement.focus();
      } 
      return;
    }

    this.userservice.getData(result,"update-unknown-transaction","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Unknown Transaction", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-unknown-transaction']);
          }, 3000);
        } else {
          this.appService.showAlert("Unknown Transaction", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateUnknownTransaction = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      received_date: [this.currentDate, Validators.required],
      amount_received: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      payment_type_id: ['', Validators.required],
      remarks: [''],
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
      this.pettycashincomeId = params.id;
      this.UpdateUnknownTransaction.get('id').setValue(this.pettycashincomeId);
      this.UpdateUnknownTransaction.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.pettycashincomeId 
      }
      this.userservice.getData(cond, "get-unknown-transaction","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateUnknownTransaction.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateUnknownTransaction.get('branch_id').setValue(response.data[0].branch_id);
            if(response.data[0].received_date!='') {
              let introDate = (response.data[0].received_date).split('/');
              let setIntroDate = {
                'year': parseInt(introDate[2]),
                'month': parseInt(introDate[1]),
                'day': parseInt(introDate[0])
              } 
              this.UpdateUnknownTransaction.get('received_date').setValue(setIntroDate);
            }
            this.UpdateUnknownTransaction.get('amount_received').setValue(response.data[0].amount_received);
            this.UpdateUnknownTransaction.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.setPayments(response.data[0].payment_type_id);
            this.UpdateUnknownTransaction.get('cheque_no').setValue(response.data[0].cheque_no);
            
            if(response.data[0].cheque_date!='') {
              let cDate = (response.data[0].cheque_date).split('/');
              let setCdate = {
                'year': parseInt(cDate[2]),
                'month': parseInt(cDate[1]),
                'day': parseInt(cDate[0])
              }
              this.UpdateUnknownTransaction.get('cheque_date').setValue(setCdate);
            }
            this.UpdateUnknownTransaction.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdateUnknownTransaction.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            
            if(response.data[0].transaction_date!='') {
              let tDate = (response.data[0].transaction_date).split('/');
              let setTdate = {
                'year': parseInt(tDate[2]),
                'month': parseInt(tDate[1]),
                'day': parseInt(tDate[0])
              }
              this.UpdateUnknownTransaction.get('transaction_date').setValue(setTdate);
            }
            this.UpdateUnknownTransaction.get('transaction_no').setValue(response.data[0].transaction_no);
            this.UpdateUnknownTransaction.get('remarks').setValue(response.data[0].remarks);
            this.setBranchList(response.data[0].tenant_id);
            this.setPaymentTypeList(response.data[0].tenant_id);
            this.setAvailableAmountList(response.data[0].branch_id);
            this.setBankBranchList(response.data[0].branch_id);
            //this.bankChg(response.data[0].bank_id);
          }
          else {
            this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
