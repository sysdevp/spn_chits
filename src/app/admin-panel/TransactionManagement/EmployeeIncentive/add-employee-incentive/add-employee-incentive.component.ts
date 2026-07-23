import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';


@Component({
  
  selector: 'app-add-employee-incentive',
  templateUrl: './add-employee-incentive.component.html',
  styles: []
})
export class AddEmployeeIncentiveComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_agent_id') focus3:ElementRef;
  @ViewChild('payment_date') focus4:ElementRef;
  @ViewChild('payment_type_id') focus5:ElementRef;
  @ViewChild('cheque_no') focus6:ElementRef;
  @ViewChild('cheque_date') focus7:ElementRef;
  @ViewChild('bank_name_id') focus8:ElementRef;
  @ViewChild('bank_branch_name') focus9:ElementRef;
  @ViewChild('transaction_no') focus10:ElementRef;
  @ViewChild('transaction_date') focus11:ElementRef;
  @ViewChild('debit_to') focus12:ElementRef;
  @ViewChild('remarks') focus13:ElementRef;
  @ViewChild('customer_incentive_amount') focus14:ElementRef;
  @ViewChild('customer_id') focus15:ElementRef;
  @ViewChild('group_id') focus16:ElementRef;
  @ViewChild('ticket_no') focus17:ElementRef;
  @ViewChild('enrollment_id') focus18:ElementRef;
  @ViewChild('agent_type_id') focus19:ElementRef;
  
  AddEmployeeIncentive: FormGroup;
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
  hideRemark: boolean=true;
  currentDate:any;
  totalPendingAmt: number;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  enroDet: any[];
  paidAmountAlertMsg: boolean = false;
  totalAmt: any;
  branchId: any;
  agentList: any[];

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
    return this.AddEmployeeIncentive.controls;
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
    this.branchId = event.target.value;
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
    this.totalPendingAmt = 0;
    let cond = {
      "conditions[introducer_id]" : event.target.value,
      "conditions[branch_id]" : this.branchId,
      "conditions[agent_type]" : this.AddEmployeeIncentive.get('agent_type_id').value,
    }
    this.userservice.getData(cond, "get-agent-incentive-enrol-details", "POST").subscribe((response: any) => {
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
    const installArray = < FormArray > this.AddEmployeeIncentive.get('Incentive');
    installArray.controls = []; 
    for(let i=0; i<(listCount); i++) {
      this.addInstallment();
      this.enrolChg = true;
    } 
  }

  setInstallmentList(dataList) {
    const installArray = < FormArray > this.AddEmployeeIncentive.get('Incentive');
    for(let i=0; i<(dataList.length); i++) { 
      installArray.controls[i].get('enrollment_id').setValue(dataList[i].enrollment_id);
      installArray.controls[i].get('customer_id').setValue(dataList[i].customer_id);
      installArray.controls[i].get('group_id').setValue(dataList[i].group_id);
      installArray.controls[i].get('ticket_no').setValue(dataList[i].ticket_no);
      installArray.controls[i].get('customer_incentive_amount').setValue(dataList[i].customer_incentive_amount);
     }
  }

  addInstallment() { 
    const control = <FormArray>this.AddEmployeeIncentive.controls['Incentive'];
    control.push(this.initCommission());
  }

  initCommission() {
    return this.formBuilder.group({
      'enrollment_id': [''],
      'customer_id': [''],
      'group_id': [''],
      'ticket_no': [''],
      'customer_incentive_amount': [''],
    }); 
  }

  get Incentive() {
    return <FormArray>this.AddEmployeeIncentive.get('Incentive');
  }

  bankChg(event)
  {
    this.AddEmployeeIncentive.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddEmployeeIncentive.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  onAmtChange(event) { 
    let receiptAmt = event.target.value;
    this.totalAmt = receiptAmt;
    if(parseInt(receiptAmt) > this.totalPendingAmt) {
      this.paidAmountAlertMsg = true;
      this.AddEmployeeIncentive.get('paid_amount').setValue('');
     
    }
    else {
      this.paidAmountAlertMsg = false;
      const installArray = < FormArray > this.AddEmployeeIncentive.get('Incentive');
      for (let i = 0; i < installArray.length; i++) {     
        let pending = this.enrolList[i].pending_commission;
        if(parseInt(receiptAmt) > pending) {
          installArray.controls[i].get('customer_incentive_amount').setValue(pending);
          receiptAmt = parseInt(receiptAmt) -parseInt(pending) ;
        }
        else {
          let receivedAmt = parseInt(receiptAmt);
          installArray.controls[i].get('customer_incentive_amount').setValue(receivedAmt);
          receiptAmt = 0;
        }
      } 
    } 
  }
  agentChg(event)
  {
    let agentid=event.target.value
    let cond = {
      "branch_id" : this.AddEmployeeIncentive.get('branch_id').value,
      "agent_type_id" : agentid,
    }
    this.userservice.getData(cond, "agent-type-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.agentList = response.data;
        } else {
          this.agentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  paymentChg(event)
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
      "conditions[tenant_id]" : this.AddEmployeeIncentive.get('tenant_id').value,
      "conditions[branch_id]" : this.AddEmployeeIncentive.get('branch_id').value,
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
    let debitTo = this.AddEmployeeIncentive.get('debit_to');
    let chequeNo = this.AddEmployeeIncentive.get('cheque_no');
    let chequeDate = this.AddEmployeeIncentive.get('cheque_date');
    let bankId = this.AddEmployeeIncentive.get('bank_name_id');
    let bankBranchName = this.AddEmployeeIncentive.get('bank_branch_name');
    let transactionNo = this.AddEmployeeIncentive.get('transaction_no');
    let transactionDate = this.AddEmployeeIncentive.get('transaction_date');
    if(paymentType=='1') {
      debitTo.setValidators([Validators.required,Validators.pattern('^[a-zA-Z]+$')]);
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

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddEmployeeIncentive.value);
    if (this.AddEmployeeIncentive.invalid) 
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
      else if(this.f.bank_branch_name.errors) {
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
      else if(this.f.customer_incentive_amount.errors) {
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
      else if(this.f.agent_type_id.errors) {
        this.focus19.nativeElement.focus();
      }
      
      return;
    }
    
    this.userservice.getData(result, "store-agent-incentive-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Employee Incentive",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/incentive-pending-report']);
          }, 300);
        } else {0
          this.appService.showAlert("Employee Incentive", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Employee Incentive", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Employee Incentive", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Employee Incentive", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddEmployeeIncentive = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_agent_id: ['', Validators.required],
      agent_type_id:[''],
      payment_date: [this.currentDate,Validators.required],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      paid_amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      remarks: [''],
      created_by: [''],
      'Incentive': this.formBuilder.array([
        this.formBuilder.group({
          'enrollment_id': [''],  
          'customer_id': [''],
          'group_id': [''],
          'ticket_no': [''],
          'customer_incentive_amount': ['', [Validators.pattern(this.decimalPattern)]], 
        })
      ]),
    });
   
    this.totalPendingAmt = 0;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddEmployeeIncentive.get('tenant_id').setValue(tenant_id);
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
    this.userservice.getData("", "list-payment-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.paymentTypeList = response.data;
        } else {
          this.paymentTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.AddEmployeeIncentive.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

