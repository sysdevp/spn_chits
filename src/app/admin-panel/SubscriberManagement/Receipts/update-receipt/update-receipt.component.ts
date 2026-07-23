import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-receipt',
  templateUrl: './update-receipt.component.html',
  styles: []
})

export class UpdateReceiptComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('other_branch') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;
  @ViewChild('group_id') focus6:ElementRef;
  @ViewChild('ticket_no') focus7:ElementRef;
  @ViewChild('employee_id') focus8:ElementRef;
  @ViewChild('receipt_date') focus9:ElementRef;
  @ViewChild('payment_type_id') focus10:ElementRef;
  @ViewChild('cheque_no') focus11:ElementRef;
  @ViewChild('cheque_date') focus12:ElementRef;
  @ViewChild('bank_name_id') focus13:ElementRef;
  @ViewChild('branch_name') focus14:ElementRef;
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
  
  UpdateReceipt: FormGroup;
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
  enrolList: any[];
  pendingDetail: any[];
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  current:any;
  totalPendingAmt: number;
  receiptAmtAlertMsg: boolean = false;
  receiptNo: any;
  installmentList: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";

  constructor(private route: ActivatedRoute,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
     this.current = {
       year: today_date.getFullYear(),
       month: today_date.getMonth()+1,
       day: today_date.getDate()
     };
  }

  get f() {
    return this.UpdateReceipt.controls;
  }

  paymentChg(event)
  {
    this.setPayments(event.target.value);
    let cond = {
      "conditions[tenant_id]" : this.UpdateReceipt.get('tenant_id').value,
      "conditions[branch_id]" : this.UpdateReceipt.get('branch_id').value,
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
    //cash - 1
    let debitTo = this.UpdateReceipt.get('debit_to');
    //cheque, DD - 2,3
    let chequeNo = this.UpdateReceipt.get('cheque_no');
    let chequeDate = this.UpdateReceipt.get('cheque_date');
    let bankId = this.UpdateReceipt.get('bank_name_id');
    let bankBranchName = this.UpdateReceipt.get('branch_name');
    //NEFT - 4
    let transactionNo = this.UpdateReceipt.get('transaction_no');
    let transactionDate = this.UpdateReceipt.get('transaction_date');
    if(paymentTypeId=='1') {
      debitTo.setValidators([Validators.required]);
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

  addInstallment() { 
    const control = <FormArray>this.UpdateReceipt.controls['Installments'];
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
      'id': [''],
    }); 
  }

  get Installments() {
    return <FormArray>this.UpdateReceipt.get('Installments');
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateReceipt.value); 
    if (this.UpdateReceipt.invalid) 
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
      else if(this.f.group_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.ticket_no.errors) {
        this.focus7.nativeElement.focus();
      }
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
      else if(this.f.branch_name.errors) {
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
    
    this.userservice.getData(result, "update-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Receipt",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-receipts']);
          }, 300);
        } else {
          this.appService.showAlert("Receipt", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Receipt", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateReceipt = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      other_branch: [''],
      customer_id: ['', Validators.required],
      enrollment_id: ['', Validators.required],
      group_id: ['', Validators.required],
      ticket_no: [''],
      employee_id: ['', Validators.required],
      receipt_date: [null,Validators.required],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      remarks: [''],
      updated_by: [''],
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
          'id': [''],
        })
      ]),
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
      this.receiptNo = params.receiptNo;
      this.UpdateReceipt.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[receipt_no]" : this.receiptNo 
      }
      this.userservice.getData(cond, "get-receipts","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateReceipt.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateReceipt.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateReceipt.get('other_branch').setValue(response.data[0].other_branch);
            this.UpdateReceipt.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateReceipt.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdateReceipt.get('group_id').setValue(response.data[0].group_id);
            this.UpdateReceipt.get('ticket_no').setValue(response.data[0].ticket_no);
            this.UpdateReceipt.get('employee_id').setValue(response.data[0].employee_id);
            if(response.data[0].receipt_date!='') {
              let recDate = (response.data[0].receipt_date).split('/');
              let setRecDate = {
                'year': parseInt(recDate[2]),
                'month': parseInt(recDate[1]),
                'day': parseInt(recDate[0])
              } 
              this.UpdateReceipt.get('receipt_date').setValue(setRecDate);
            }
            this.UpdateReceipt.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdateReceipt.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!='') {
              let chqDate = (response.data[0].cheque_date).split('/');
              let setChqDate = {
                'year': parseInt(chqDate[2]),
                'month': parseInt(chqDate[1]),
                'day': parseInt(chqDate[0])
              } 
              this.UpdateReceipt.get('cheque_date').setValue(setChqDate);
            }
            this.UpdateReceipt.get('bank_name_id').setValue(response.data[0].bank_name_id);
            this.UpdateReceipt.get('branch_name').setValue(response.data[0].branch_name);
            this.UpdateReceipt.get('transaction_no').setValue(response.data[0].transaction_no);
            if(response.data[0].transaction_date!='') {
              let transDate = (response.data[0].transaction_date).split('/');
              let setTransDate = {
                'year': parseInt(transDate[2]),
                'month': parseInt(transDate[1]),
                'day': parseInt(transDate[0])
              } 
              this.UpdateReceipt.get('transaction_date').setValue(setTransDate);
            }
            this.UpdateReceipt.get('debit_to').setValue(response.data[0].debit_to);
            this.UpdateReceipt.get('amount').setValue(response.data[0].amount);
            this.UpdateReceipt.get('remarks').setValue(response.data[0].remarks);
            this.installmentList = response.data[0].pending_details;
            const installArray = < FormArray > this.UpdateReceipt.get('Installments');
            installArray.controls = [];
            for (let i = 0; i < response.data[0].pending_details.length; i++) {
              if (i > 0) {
                this.addInstallment();
              }
              installArray.controls[i].get('auction_id').setValue(response.data[0].pending_details[i].auction_id);
              installArray.controls[i].get('installment_no').setValue(response.data[0].pending_details[i].installment_no);
              installArray.controls[i].get('inst_amt_wise_pending').setValue(response.data[0].pending_details[i].inst_amt_wise_pending);
              installArray.controls[i].get('received_amount').setValue(response.data[0].pending_details[i].received_amount);
              installArray.controls[i].get('penalty_inst_wise').setValue(response.data[0].pending_details[i].penalty_inst_wise);
              installArray.controls[i].get('bonus_inst_wise').setValue(response.data[0].pending_details[i].bonus_inst_wise);
              installArray.controls[i].get('discount_inst_wise').setValue(response.data[0].pending_details[i].discount_inst_wise);
              installArray.controls[i].get('discount_penalty_wise').setValue(response.data[0].pending_details[i].discount_penalty_wise);
              installArray.controls[i].get('pending_days').setValue(response.data[0].pending_details[i].pending_days);
              installArray.controls[i].get('id').setValue(response.data[0].pending_details[i].id);
            }
            this.setPayments(response.data[0].payment_type_id);
            let cond = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id,
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
          else {
            this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Receipt", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
