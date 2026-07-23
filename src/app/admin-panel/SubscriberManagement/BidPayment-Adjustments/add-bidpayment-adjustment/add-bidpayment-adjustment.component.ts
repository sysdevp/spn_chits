import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-bidpayment-adjustment',
  templateUrl: './add-bidpayment-adjustment.component.html',
  styles: []
})

export class AddBidpaymentAdjustmentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('other_branch') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;
  @ViewChild('group_id') focus6:ElementRef;
  @ViewChild('ticket_no') focus7:ElementRef;
  @ViewChild('employee_id') focus8:ElementRef;
  @ViewChild('receipt_date') focus9:ElementRef;
  @ViewChild('amount') focus10:ElementRef;
  @ViewChild('remarks') focus11:ElementRef;
  @ViewChild('installment_no') focus12:ElementRef;
  @ViewChild('inst_amt_wise_pending') focus13:ElementRef;
  @ViewChild('installment_wise_overall_pending') focus14:ElementRef;
  @ViewChild('received_amount') focus15:ElementRef;
  @ViewChild('penalty_inst_wise') focus16:ElementRef;
  @ViewChild('bonus_inst_wise') focus17:ElementRef;
  @ViewChild('discount_inst_wise') focus18:ElementRef;
  @ViewChild('discount_penalty_wise') focus19:ElementRef;
  @ViewChild('pending_days') focus20:ElementRef;
  @ViewChild('from_enrollment_id') focus21:ElementRef;
  @ViewChild('other_amount') focus22:ElementRef;
  @ViewChild('charge_type_id') focus23:ElementRef;
  @ViewChild('alt_rec_no') focus24:ElementRef;
  @ViewChild('penalty_amount_tot') focus25:ElementRef;

  
  AddBidpayment: FormGroup;
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
  enrolList: any[];
  pendingDetail: any[];
  employeeSet: boolean=true;
  hidePayment: boolean=true;
  hideChitDetails: boolean = true;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideamount: boolean=true;
  enrolChg: boolean = false;
  currentDate:any;
  totalPendingAmt: number;
  pendingwithbonusandPenalty: number;
  totalBonusAmt:number;
  totalPenaltyAmt:number;
  receiptAmtAlertMsg: boolean = false;
  bidAmtAlertMsg: boolean= false;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  totalAmt :any[];
  bidpaymentAmt: any;
  bidpayment: any;
  enrollmentId: any;
  receiptAmount: number;
  allcustomerList: any;
  chargesTypeList: any;

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
    return this.AddBidpayment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
  }

  branchChg(event)
  {
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.allcustomerList = response.data;
        } else {
          this.allcustomerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
  }

  groupChg(event)
  {
    let cond = {
      "conditions[group_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-prized-customer-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
          this.enrollmentId=response.data[0].enrollment_id;
          //console.log( this.enrollmentId);
          this.AddBidpayment.get('adjust_id').setValue(response.data[0].enrollment_id);
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  employeeChg()
  {
    this.employeeSet=false;
    this.hidePayment = true;
    this.hideChitDetails = true;
    this.enrolChg = false;
  }

  onDateChg()
 {
  //this.AddBidpayment.get('cancel_dividend').setValue(null);
 this.AddBidpayment.get('enrollment_id').setValue('');
// this.AddBidpayment.get('receipt_type').setValue('');
 this.AddBidpayment.get('amount').setValue('');
 
 }

  fromenrollementChg(event)
  {
   
    let cond1 = {
      "conditions[enrollment_id]" : event.target.value
    }
    this.userservice.getData(cond1, "get-pending-release-amount", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.bidpaymentAmt=response.data.pending_release_amount;
          console.log("bid",this.bidpaymentAmt)
        } else {
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  enrollChg(event)
  {
    this.AddBidpayment.get('amount').setValue('');
    let rDate=this.AddBidpayment.get('receipt_date').value;
    //let receiptdate=JSON.stringify(rDate);
    let cond = {
    "enrolid" : event.target.value,
    "recdate":rDate
    }
    console.log(cond);
    this.userservice.getData(cond, "get-enrol-details", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.enrolList = response.data[0];
          this.addInstallmentList((response.data[0].pending_details).length); 
          setTimeout(() => {
            this.setInstallmentList(response.data[0].pending_details); 
          },100);
          this.pendingDetail = response.data[0].pending_details;
          this.totalPendingAmt=response.data[0].overall_pending_amount;
          this.pendingwithbonusandPenalty=response.data.total_overall_pending_amount;
          this.totalBonusAmt=response.data.total_bonus_amounts;
          this.totalPenaltyAmt=response.data.total_penalty_amounts;
          this.AddBidpayment.get('group_id').setValue(response.data[0].group_id);
          this.AddBidpayment.get('ticket_no').setValue(response.data[0].ticket_no);
        }
        else {
          this.pendingDetail = [];
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    }); 
    this.hideChitDetails = false;
    this.hidePayment = false;
    this.hideamount=false;
    
  }

  addInstallmentList(listCount) {
    const installArray = < FormArray > this.AddBidpayment.get('Installments');
    installArray.controls = []; 
    for(let i=0; i<(listCount); i++) {
      this.addInstallment();
      this.enrolChg = true;
    } 
  }

  setInstallmentList(dataList) { 
    const installArray = < FormArray > this.AddBidpayment.get('Installments');
    for(let i=0; i<(dataList.length); i++) { 
      installArray.controls[i].get('auction_id').setValue(dataList[i].auction_id);
      installArray.controls[i].get('installment_no').setValue(dataList[i].installment_no);
      installArray.controls[i].get('inst_amt_wise_pending').setValue(dataList[i].inst_amt_wise_pending);
      installArray.controls[i].get('installment_wise_overall_pending').setValue(dataList[i].installment_wise_overall_pending);
      installArray.controls[i].get('pending_days').setValue(dataList[i].pending_days);
      installArray.controls[i].get('bonus_inst_wise').setValue(dataList[i].bonus_inst_wise);
      installArray.controls[i].get('penalty_inst_wise').setValue(dataList[i].penalty_inst_wise);
      installArray.controls[i].get('discount_inst_wise').setValue(dataList[i].discount_inst_wise);
      installArray.controls[i].get('discount_penalty_wise').setValue(dataList[i].discount_penalty_wise);
    }
  }

  addInstallment() { 
    const control = <FormArray>this.AddBidpayment.controls['Installments'];
    control.push(this.initInstallment());
  }

  initInstallment() {
    return this.formBuilder.group({
      'auction_id': [''],
      'installment_no': [''],
      'inst_amt_wise_pending': [''],
      'installment_wise_overall_pending':[''],
      'received_amount': [''],
      'penalty_inst_wise': [''],
      'bonus_inst_wise': [''],
      'discount_inst_wise': [''],
      'discount_penalty_wise': [''],
      'pending_days': [''],
    }); 
  }

  get Installments() {
    return <FormArray>this.AddBidpayment.get('Installments');
  }

  onAmtChange(event) { 
    let receiptAmt = event.target.value;
    this.totalAmt = receiptAmt;
    if(parseInt(receiptAmt) > this.pendingwithbonusandPenalty ) {
      this.receiptAmtAlertMsg = true;
      this.AddBidpayment.get('amount').setValue('')
    }
    else if(parseInt(receiptAmt) >  this.bidpaymentAmt)
    {
      this.bidAmtAlertMsg = true;
      this.AddBidpayment.get('amount').setValue('')
    }
    else {
      /*this.receiptAmtAlertMsg = false;
      const installArray = < FormArray > this.AddBidpayment.get('Installments');
      for (let i = 0; i < installArray.length; i++) {
        let pending = this.pendingDetail[i].inst_amt_wise_pending;
        let penalty = this.pendingDetail[i].penalty_amounts; 
        let bonus = this.pendingDetail[i].bonus; 
        let overall_amount = this.pendingDetail[i].installment_wise_overall_pending; 
        if(penalty > 0)
          {
            let balance = receiptAmt - penalty;
            this.receiptAmount = balance;
            installArray.controls[i].get('penalty_inst_wise').setValue(penalty);
            receiptAmt = this.receiptAmount - pending;
            let amt = overall_amount - penalty;
            installArray.controls[i].get('received_amount').setValue(amt);
            if(pending > this.receiptAmount){
              let amt1 = this.receiptAmount;
              if(amt1 >0)
              {
                installArray.controls[i].get('received_amount').setValue(amt1);
              }
              else{
                amt1 =0;
                installArray.controls[i].get('received_amount').setValue(amt1);
              }
            }
          }
          else{
            if(bonus > 0)
            {
              let bonusAmt = receiptAmt - bonus;
              this.receiptAmount = bonusAmt;
              installArray.controls[i].get('bonus_inst_wise').setValue(bonus);
              let bonusAmt1 = bonusAmt;
              this.receiptAmount = bonusAmt1;
             // console.log(bonusAmt)
             // console.log(this.receiptAmount)
              receiptAmt = this.receiptAmount- pending ;
              let amt = overall_amount - bonus;
              installArray.controls[i].get('received_amount').setValue(amt);
              if(pending > this.receiptAmount){
                let amt1 = bonusAmt;
                if(amt1 >0)
                {
                  installArray.controls[i].get('received_amount').setValue(amt1);
                }
                else{
                  amt1 =0;
                  installArray.controls[i].get('received_amount').setValue(amt1);
                }
              }
            }
            else
            {
              let bonusAmt = 0;
              let penaltyAmt =0;
              installArray.controls[i].get('penalty_inst_wise').setValue(penaltyAmt);
              installArray.controls[i].get('bonus_inst_wise').setValue(bonusAmt);
              installArray.controls[i].get('received_amount').setValue(receiptAmt);
            } 
          }
         let pending = this.pendingDetail[i].installment_wise_overall_pending;
        if(receiptAmt > pending) {
          installArray.controls[i].get('received_amount').setValue(pending);
          receiptAmt = receiptAmt - pending;
        }
        else {
          let receivedAmt = receiptAmt;
          installArray.controls[i].get('received_amount').setValue(receivedAmt);
          receiptAmt = 0;
        } 
    }*/ 
    this.receiptAmtAlertMsg = false;
    this.bidAmtAlertMsg=false;
    const installArray = < FormArray > this.AddBidpayment.get('Installments');
  for (let i = 0; i < installArray.length; i++) {
    receiptAmt = this.setDeductions(i,receiptAmt);
  }
    }
   /*  if(receiptAmt >  this.bidpaymentAmt) {
      console.log(receiptAmt);
      console.log(this.bidpaymentAmt);
      this.bidAmtAlertMsg = true;
      this.AddBidpayment.get('amount').setValue('')
    } 
    else
    {
      this.bidAmtAlertMsg = false;
    } */
  }

  setDeductions(i,receiptAmt) {
    const installArray = < FormArray > this.AddBidpayment.get('Installments');
    let pending = this.pendingDetail[i].inst_amt_wise_pending;
    this.pendingDetail[i].penalty_amounts = 0;
    let penalty = this.pendingDetail[i].penalty_amounts; 
    let bonus = this.pendingDetail[i].bonus;
    if(parseInt(bonus)>0) {
      //receiptAmt = parseInt(receiptAmt) + parseInt(bonus);
      installArray.controls[i].get('bonus_inst_wise').setValue(bonus);
    }
    if(parseInt(penalty) > 0) {
      if(parseInt(receiptAmt) >= parseInt(penalty)) {
        installArray.controls[i].get('penalty_inst_wise').setValue(penalty);
        receiptAmt = parseInt(receiptAmt) - parseInt(penalty);
      }
      else { 
        installArray.controls[i].get('penalty_inst_wise').setValue(receiptAmt);
        receiptAmt = 0;
      }
    }
    if(parseInt(pending)>0) {
      if(parseInt(receiptAmt) >= parseInt(pending)) {
        let val = parseInt(pending) - parseInt(bonus);
        installArray.controls[i].get('received_amount').setValue(val);
        receiptAmt = parseInt(receiptAmt) - parseInt(pending) + parseInt(bonus);
      }
      else {
        installArray.controls[i].get('received_amount').setValue(receiptAmt);
        receiptAmt = 0;
      }
    }
    return receiptAmt;
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

  customerChg(event)
  {
    this.AddBidpayment.get('enrollment_id').setValue('');
    let cond = {
      "conditions[customer_id]" : event.target.value
    }
    console.log(cond);
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollementList = response.data;
          this.hideChitDetails = true;
          this.hidePayment = true;
          this.hideDebitTo=true;
          this.hideCheque=true;
          this.hideBank=true;
          this.hideTransaction=true;
          this.hideamount=true;
          //console.log(response.data)
        } else {
          this.enrollementList = [];
          this.hideChitDetails = true;
          this.hidePayment = true;
          this.hideDebitTo=true;
          this.hideCheque=true;
          this.hideBank=true;
          this.hideTransaction=true;
          this.hideamount=true;
        }
      }  catch (err) { }
    }, (err: any) => { });  
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddBidpayment.value);
    if (this.AddBidpayment.invalid) 
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
      else if(this.f.amount.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.installment_no.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.inst_amt_wise_pending.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.installment_wise_overall_pending.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.received_amount.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.penalty_inst_wise.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.bonus_inst_wise.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.discount_inst_wise.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.discount_penalty_wise.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.pending_days.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.from_enrollment_id.errors) {
        this.focus21.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-bp-adjust-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Bid Payment Adjustment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/receipt-report']);
          }, 300);
        } else {
          this.appService.showAlert("Bid Payment Adjustment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) { //console.log (err)
        this.appService.showAlert("Bid Payment Adjustment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => { //console.log (err)
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Bid Payment Adjustment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Bid Payment Adjustment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddBidpayment = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      other_branch: [''],
      customer_id: ['', Validators.required],
      from_enrollment_id: ['', Validators.required],
      enrollment_id: ['', Validators.required],
      group_id: ['', Validators.required],
      ticket_no: [''],
      employee_id: ['', Validators.required],
      adjust_id:[''],
      receipt_date: [this.currentDate,Validators.required],
      amount: ['', [Validators.pattern(this.decimalPattern)]],
      remarks: [''],
      created_by: [''],
      charge_type_id: [''],
      alt_rec_no: [''],
      other_amount:['',[Validators.pattern(this.decimalPattern)]],
      penalty_amount_tot:[''],
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
        })
      ]),
    });
    this.totalPendingAmt = 0;
    this.pendingwithbonusandPenalty=0;
    this.totalBonusAmt=0;
    this.totalPenaltyAmt=0;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddBidpayment.get('tenant_id').setValue(tenant_id);
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
    this.AddBidpayment.get('created_by').setValue(localStorage.getItem('logged_user_id'));
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

