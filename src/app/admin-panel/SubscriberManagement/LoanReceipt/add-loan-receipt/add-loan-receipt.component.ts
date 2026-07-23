import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  selector: 'app-add-loan-receipt',
  templateUrl: './add-loan-receipt.component.html',
  styles: []
})
export class AddLoanReceiptComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus4:ElementRef;
  @ViewChild('customer_id') focus5:ElementRef;
  @ViewChild('payment_type_id') focus6:ElementRef;
  @ViewChild('cheque_no') focus7:ElementRef;
  @ViewChild('cheque_date') focus8:ElementRef;
  @ViewChild('bank_name_id') focus9:ElementRef;
  @ViewChild('bank_branch_name') focus10:ElementRef;
  @ViewChild('transaction_no') focus11:ElementRef;
  @ViewChild('transaction_date') focus12:ElementRef;
  @ViewChild('debit_to') focus13:ElementRef;
  @ViewChild('loan_id') focus14:ElementRef;
  @ViewChild('receipt_date') focus15:ElementRef;
  @ViewChild('interest_amount') focus16:ElementRef;
  @ViewChild('amount') focus17:ElementRef;
  @ViewChild('enter_amount') focus18:ElementRef;
  @ViewChild('enrollment_id') focus19:ElementRef;
  
  
  AddLoanReceipt: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  customerList: any;
  employeeList: any;
  bankList: any;
  companyList: any[];
  numOnlyPattern = "^[0-9]*$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumDotSpPattern = "^[a-zA-Z0-9. ]*$";
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
  employee_photo : any;
  mantFieldAlertMsg: any;
  minDate = undefined;
  maxDate= undefined;
  hidePayment: boolean=true;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideInterestType: boolean=true;
  hideInterestAmount: boolean=true;
  hideTotalAmount: boolean=true;
  hidetotamount: boolean=true;
  hideintAmount: boolean=true;
  receiptAmtAlertMsg: boolean=false;
  currentDate:any;

  custname: any;
  custid: any;
 
  uploader = new FileUploader({
    url: '',
    headers: [{
      name: 'Content-Type',
      value: 'application/x-www-form-urlencoded'
    },
    {
      name: 'Authorization',
      value: this.auth_token
    },
    {
      name: 'Accept',
      value: 'multipart/form-data'
    }]
  });
  hasBaseDropZoneOver = false;
  push: boolean = false;
  overallupload: number;
  totalAmt: any;
  total: number;
  loanList: any;
  paidAmt: any;
  pendingAmt: any;
  loanListAmt: any[];
  interestAmt: any;
  totpendingAmt: any;
  pendinginterestAmt: any;
  groupList: any;
  enrollementList: any;
  
  
  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

  constructor(private http: HttpClient,private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.overallupload = 0;  
    this.APIUrl = environment.API;  
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
    this.currentDate = {
        'year': today_date.getFullYear(),
        'month': today_date.getMonth()+1,
        'day': today_date.getDate()
    };
  }

  get f() {
    return this.AddLoanReceipt.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.loanListAmt = [];
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

  customerChg(event: Event) {
    const el = event.target as HTMLSelectElement;
    const id = el.value;
    if (!id) {
      this.custid = '';
      this.loanListAmt = [];
      this.enrollementList = [];
      this.AddLoanReceipt.get('loan_id').setValue('');
      this.paidAmt = '';
      this.pendingAmt = '';
      this.interestAmt = '';
      this.totpendingAmt = '';
      this.pendinginterestAmt = '';
      return;
    }
    this.custid = id;
    this.loanListAmt = [];
    this.setcustomerList(this.custid);
    this.AddLoanReceipt.get('loan_id').setValue('');
    this.paidAmt = '';
    this.pendingAmt = '';
    this.interestAmt = '';
    this.totpendingAmt = '';
    this.pendinginterestAmt = '';

  }

  setcustomerList(test) { 
    let cond = {
      "conditions[customer_id]" : test,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-customer-wise-loan-detail", "POST").subscribe((response: any) => {
      try { 
       
        if (response.status == 'Success') {
          this.loanListAmt = response.data;
          
        } else {
          this.loanListAmt = [];
          this.AddLoanReceipt.get('loan_id').setValue(0);
        }
      }  catch (err) { }
    }, (err: any) => { });

   
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

  branchChg(event) {
    this.AddLoanReceipt.get('loan_id').setValue('');
    this.AddLoanReceipt.get('customer_id').setValue('');
    this.setCollectionEmployeeList(event.target.value);
    //this.hideamount = true;
    this.paidAmt = '';
    this.pendingAmt = '';
    this.interestAmt = '';
    this.totpendingAmt = '';
    this.pendinginterestAmt = '';
  }

  onDateChg()
 {
  this.AddLoanReceipt.get('loan_id').setValue('');
 
 }

  loanChg(event) {
    let loan_id = event.target.value;
    let rDate=this.AddLoanReceipt.get('receipt_date').value;
    let cust=this.AddLoanReceipt.get('customer_id').value;
    let cond1 = {
      "loan_id" : loan_id,
      "receipt_date" : rDate,
      "customer_id" : this.custid
    }
    this.userservice.getData(cond1, "get-loan-details-customer-wise", "JSON").subscribe((response: any) => {
      try { 
        
        if (response.status == 'Success') {
          
          this.loanList = response.data[0].pending_loan_details[0];
          console.log(this.loanList);
          this.paidAmt=response.data[0].pending_loan_details[0].paid_loan_amount;
          this.pendingAmt=response.data[0].pending_loan_details[0].tot_pending_loan_amt;
          this.interestAmt=response.data[0].pending_loan_details[0].paid_interest_amount;
          this.pendinginterestAmt=response.data[0].pending_loan_details[0].tot_pending_interest_amt;
          this.totpendingAmt=parseInt(this.pendingAmt) + parseInt(this.pendinginterestAmt);
        } else {
          this.loanList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  amountChg(event) { 
    let amtvalue = event.target.value;
    if(amtvalue == 0){
      this.AddLoanReceipt.get('enter_amount').setValue('');
    }
    if(parseInt(amtvalue) > (this.totpendingAmt)) {
      this.receiptAmtAlertMsg = true;
      this.AddLoanReceipt.get('enter_amount').setValue('');
    }
    else {
      this.receiptAmtAlertMsg = false;
    }
    if(amtvalue == ''){
      this.hidePayment = true;
    }
    else{
      this.hidePayment = false;
    }
    console.log(amtvalue);
      console.log(this.interestAmt);
    if(parseInt(amtvalue) >= parseInt(this.pendinginterestAmt)){
      
      let amtvalue1 = parseInt(amtvalue) - parseInt(this.pendinginterestAmt);
      console.log(amtvalue1);
      this.AddLoanReceipt.get('interest_amount').setValue(this.pendinginterestAmt);
      this.AddLoanReceipt.get('amount').setValue(amtvalue1);
    }
    else{
      this.AddLoanReceipt.get('interest_amount').setValue(amtvalue);
      this.AddLoanReceipt.get('amount').setValue(0);
      amtvalue = 0;
      console.log(amtvalue);
    }
    
  }

  setCollectionEmployeeList(branch_id) {
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

    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
      if (response.status == 'Success') { 
      this.groupList = response.data;
      this.customerList = [];
      
      } else {
      this.groupList = [];
      }
      } catch (err) { }
      }, (err: any) => { });

    this.userservice.getData(cond, "get-all-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  

  bankChg(event)
  {
    this.AddLoanReceipt.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddLoanReceipt.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  paymentChg(event)
 {

 if (event.target.value == 1) {
 this.hideDebitTo=false;
 this.hideCheque=true;
 this.hideBank=true;
 this.hideTransaction=true;
 }
 else if (event.target.value == 2) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (event.target.value == 3) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (event.target.value == 4){
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
 let cond = {
 "conditions[tenant_id]" : this.AddLoanReceipt.get('tenant_id').value,
 "conditions[branch_id]" : this.AddLoanReceipt.get('branch_id').value,
 }
 this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
 try { 
 if (response.status == 'Success') { 
 this.bankList = response.data;
 } 
 else {
 this.bankList = [];
 }
 } catch (err) { }
 }, (err: any) => { });

 let paymentType = event.target.value;
 //cash - 1
 let debitTo = this.AddLoanReceipt.get('debit_to');
 //cheque, DD - 2,3
 let chequeNo = this.AddLoanReceipt.get('cheque_no');
 let chequeDate = this.AddLoanReceipt.get('cheque_date');
 let bankId = this.AddLoanReceipt.get('bank_name_id');
 let bankBranchName = this.AddLoanReceipt.get('bank_branch_name');
 //NEFT - 4
 let transactionNo = this.AddLoanReceipt.get('transaction_no');
 let transactionDate = this.AddLoanReceipt.get('transaction_date');
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

  open(content, options = {}) {
    this.modalService.open(content, options).result.then((result) => {
      
    }, (reason) => {
      
    });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddLoanReceipt.value);
    if (this.AddLoanReceipt.invalid) 
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
        this.focus4.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus7.nativeElement.focus();
        }
        else if(this.f.cheque_date.errors) {
        this.focus8.nativeElement.focus();
        }
        else if(this.f.bank_name_id.errors) {
        this.focus9.nativeElement.focus();
        }
        else if(this.f.bank_branch_name.errors) {
        this.focus10.nativeElement.focus();
        }
        else if(this.f.transaction_no.errors) {
        this.focus11.nativeElement.focus();
        }
        else if(this.f.transaction_date.errors) {
        this.focus12.nativeElement.focus();
        }
        else if(this.f.debit_to.errors) {
        this.focus13.nativeElement.focus();
        }
        else if(this.f.loan_id.errors) {
          this.focus14.nativeElement.focus();
        }
        else if(this.f.receipt_date.errors) {
        this.focus15.nativeElement.focus();
        }
        else if(this.f.interest_amount.errors) {
          this.focus16.nativeElement.focus();
        }
        else if(this.f.amount.errors) {
          this.focus17.nativeElement.focus();
        }
        else if(this.f.enter_amount.errors) {
          this.focus18.nativeElement.focus();
        }
        else if(this.f.enrollment_id.errors) {
          this.focus19.nativeElement.focus();
        }
        
      return;
    }


    this.userservice.getData(result, "store-loan-receipt", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Loan Receipt",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/loan-receipt-report']);
          }, 300);
        } else {
          this.appService.showAlert("Loan Receipt", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Loan Receipt", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Loan Receipt", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Loan Receipt", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddLoanReceipt = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      loan_id:['', Validators.required],
      employee_id: ['', Validators.required],
      amount: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      enter_amount: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      receipt_date: [this.currentDate,Validators.required],
      interest_amount:[''],
      created_by: [''],
      enrollment_id: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddLoanReceipt.get('tenant_id').setValue(tenant_id);
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
    this.AddLoanReceipt.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.userservice.getData("", "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

  }
}
