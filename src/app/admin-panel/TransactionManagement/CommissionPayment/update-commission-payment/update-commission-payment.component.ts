import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormArray, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-commission-payment',
  templateUrl: './update-commission-payment.component.html',
  styles: []
})

export class UpdateCommissionPaymentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_agent_id') focus3:ElementRef;
  @ViewChild('payment_date') focus4:ElementRef;
  @ViewChild('payment_type_id') focus5:ElementRef;
  @ViewChild('cheque_no') focus6:ElementRef;
  @ViewChild('cheque_date') focus7:ElementRef;
  @ViewChild('bank_name_id') focus8:ElementRef;
  @ViewChild('branch_name') focus9:ElementRef;
  @ViewChild('transaction_no') focus10:ElementRef;
  @ViewChild('transaction_date') focus11:ElementRef;
  @ViewChild('debit_to') focus12:ElementRef;
  @ViewChild('remarks') focus13:ElementRef;
  @ViewChild('customer_commission_amount') focus14:ElementRef;
  @ViewChild('customer_id') focus15:ElementRef;
  @ViewChild('group_id') focus16:ElementRef;
  @ViewChild('ticket_no') focus17:ElementRef;
  @ViewChild('enrollment_id') focus18:ElementRef;

  UpdateCommissionPayment: FormGroup;
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
  employeeSet: boolean=true;
  hidePayment: boolean=true;
  hideCommissionDetails: boolean = true;
  enrolChg: boolean = false;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  currentDate:any;
  totalPendingAmt: number;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  enroDet: any[];
  paidAmountAlertMsg: boolean = false;
  totalAmt: any;
  commissionNo: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {   
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCommissionPayment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideCommissionDetails = true;
    this.enrolChg = false;
  }

  branchChg(event)
  {
    let cond = {
      "conditions[branch_id]" : event.target.value,
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
    
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideCommissionDetails = true;
    this.enrolChg = false;
  }

  employeeChg(event)
  {
    let cond = {
      "conditions[introducer_id]" : event.target.value
    }
    
    this.userservice.getData(cond, "get-agent-enrol-details", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.enrolList = response.data;
          this.addInstallmentList((response.data).length); 
          setTimeout(() => {
            this.setInstallmentList(response.data); 
          },500);
          this.enroDet = response.data[0].pending_commission;
          this.totalPendingAmt = response.data[0].overall_pending;
        }
        else {
          this.enroDet = [];
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
    this.hideCommissionDetails = false;
    this.hidePayment = false;
  }


  addInstallmentList(listCount) {
    const installArray = < FormArray > this.UpdateCommissionPayment.get('Commissions');
    installArray.controls = []; 
    for(let i=0; i<(listCount); i++) {
      this.addInstallment();
      this.enrolChg = true;
    } 
  }

  setInstallmentList(dataList) {
    const installArray = < FormArray > this.UpdateCommissionPayment.get('Commissions');
    for(let i=0; i<(dataList.length); i++) { 
      installArray.controls[i].get('enrollment_id').setValue(dataList[i].enrollment_id);
      installArray.controls[i].get('customer_id').setValue(dataList[i].customer_id);
      installArray.controls[i].get('group_id').setValue(dataList[i].group_id);
      installArray.controls[i].get('ticket_no').setValue(dataList[i].ticket_no);
      installArray.controls[i].get('customer_commission_amount').setValue(dataList[i].customer_commission_amount);
     }
  }

  addInstallment() { 
    const control = <FormArray>this.UpdateCommissionPayment.controls['Commissions'];
    control.push(this.initCommission());
  }

  initCommission() {
    return this.formBuilder.group({
      'enrollment_id': [''],
      'customer_id': [''],
      'group_id': [''],
      'ticket_no': [''],
      'customer_commission_amount': [''],
    }); 
  }

  get Commissions() {
    return <FormArray>this.UpdateCommissionPayment.get('Commissions');
  }

  paymentChg(event)
  {
    this.setPayments(event.target.value);
    let cond = {
      "conditions[tenant_id]" : this.UpdateCommissionPayment.get('tenant_id').value,
      "conditions[branch_id]" : this.UpdateCommissionPayment.get('branch_id').value,
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
    let debitTo = this.UpdateCommissionPayment.get('debit_to');
    let chequeNo = this.UpdateCommissionPayment.get('cheque_no');
    let chequeDate = this.UpdateCommissionPayment.get('cheque_date');
    let bankId = this.UpdateCommissionPayment.get('bank_name_id');
    let bankBranchName = this.UpdateCommissionPayment.get('branch_name');
    let transactionNo = this.UpdateCommissionPayment.get('transaction_no');
    let transactionDate = this.UpdateCommissionPayment.get('transaction_date');
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

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCommissionPayment.value); 
    if (this.UpdateCommissionPayment.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_agent_id.errors) {
        this.focus3.nativeElement.focus();
      }     
      else if(this.f.payment_date.errors) {
        this.focus4.nativeElement.focus();
      }               
      else if(this.f.payment_type_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.bank_name_id.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.branch_name.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.debit_to.errors) {
        this.focus12.nativeElement.focus();
      }      
      else if(this.f.remarks.errors) {
        this.focus13.nativeElement.focus();
      } 
      else if(this.f.customer_commission_amount.errors) {
        this.focus14.nativeElement.focus();
      }  
      else if(this.f.customer_id.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.ticket_no.errors) {
        this.focus17.nativeElement.focus();
      } 
      else if(this.f.enrollment_id.errors) {
        this.focus18.nativeElement.focus();
      } 
      return;
    }

    this.userservice.getData(result, "update-agent-commission-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Agent Commission Payment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-agent-commission-payment']);
          }, 300);
        } else {
          this.appService.showAlert("Agent Commission Payment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Agent Commission Payment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateCommissionPayment = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_agent_id: ['', Validators.required],
      payment_date: [this.currentDate,Validators.required],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      paid_amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      remarks: [''],
      updated_by: [''],
      'Commissions': this.formBuilder.array([
        this.formBuilder.group({
          'enrollment_id': [''],  
          'customer_id': [''],
          'group_id': [''],
          'ticket_no': [''],
          'customer_commission_amount': ['', [Validators.pattern(this.decimalPattern)]], 
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
      this.commissionNo = params.commissionNo;
      this.UpdateCommissionPayment.get('commission_no').setValue(this.commissionNo);
      this.UpdateCommissionPayment.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[commission_no]" : this.commissionNo 
      }
      this.userservice.getData(cond, "get-agent-commission-payments","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          { 
            this.UpdateCommissionPayment.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateCommissionPayment.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateCommissionPayment.get('employee_agent_id').setValue(response.data[0].employee_agent_id);
            this.UpdateCommissionPayment.get('paid_amount').setValue(response.data[0].paid_amount);
            if(response.data[0].payment_date!=''){
              let introDate = (response.data[0].payment_date).split('/');
              let setIntroDate = {
                'year': parseInt(introDate[2]),
                'month': parseInt(introDate[1]),
                'day': parseInt(introDate[0])
              } 
              this.UpdateCommissionPayment.get('payment_date').setValue(setIntroDate);
            }                       
            this.UpdateCommissionPayment.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdateCommissionPayment.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!='') {
              let chqDate = (response.data[0].cheque_date).split('/');
              let setChqDate = {
                'year': parseInt(chqDate[2]),
                'month': parseInt(chqDate[1]),
                'day': parseInt(chqDate[0])
              } 
              this.UpdateCommissionPayment.get('cheque_date').setValue(setChqDate);
            }
            this.UpdateCommissionPayment.get('bank_name_id').setValue(response.data[0].bank_name_id);
            this.UpdateCommissionPayment.get('branch_name').setValue(response.data[0].branch_name);
            this.UpdateCommissionPayment.get('transaction_no').setValue(response.data[0].transaction_no);
            if(response.data[0].transaction_date!='') {
              let transDate = (response.data[0].transaction_date).split('/');
              let setTransDate = {
                'year': parseInt(transDate[2]),
                'month': parseInt(transDate[1]),
                'day': parseInt(transDate[0])
              } 
              this.UpdateCommissionPayment.get('transaction_date').setValue(setTransDate);
            }
            this.UpdateCommissionPayment.get('debit_to').setValue(response.data[0].debit_to);
            this.UpdateCommissionPayment.get('remarks').setValue(response.data[0].remarks);
            const installArray = < FormArray > this.UpdateCommissionPayment.get('Commissions');
            installArray.controls = [];
            for (let i = 0; i < response.data[0].length; i++) {
              if (i > 0) {
                this.addInstallment();
              }
              installArray.controls[i].get('enrollment_id').setValue(response.data[0].pending_details[i].enrollment_id);
              installArray.controls[i].get('customer_id').setValue(response.data[0].pending_details[i].customer_id);
              installArray.controls[i].get('group_id').setValue(response.data[0].pending_details[i].group_id);
              installArray.controls[i].get('ticket_no').setValue(response.data[0].pending_details[i].ticket_no);
              installArray.controls[i].get('customer_commission_amount').setValue(response.data[0].pending_details[i].customer_commission_amount);
              installArray.controls[i].get('id').setValue(response.data[0].pending_details[i].id);
            }
            this.setPayments(response.data[0].payment_type_id);
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
