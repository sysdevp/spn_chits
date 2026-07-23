import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders, HttpRequest, HttpResponse, HttpEventType } from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  
  selector: 'app-update-loan',
  templateUrl: './update-loan.component.html',
  styles: []
})
export class UpdateLoanComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('loan_date') focus3:ElementRef;
  @ViewChild('loan_amount') focus4:ElementRef;
  @ViewChild('reference_grp') focus5:ElementRef;
  @ViewChild('loan_type') focus6:ElementRef;
  @ViewChild('interest_type') focus7:ElementRef;
  @ViewChild('interest_amount') focus8:ElementRef;
  @ViewChild('collect_employee_id') focus9:ElementRef;
  @ViewChild('payment_type_id') focus10:ElementRef;
  @ViewChild('debit_to') focus11:ElementRef;
  @ViewChild('cheque_no') focus12:ElementRef;
  @ViewChild('cheque_date') focus13:ElementRef;
  @ViewChild('bank_name_id') focus14:ElementRef;
  @ViewChild('bank_branch_name') focus15:ElementRef;
  @ViewChild('transaction_date') focus16:ElementRef;
  @ViewChild('transaction_no') focus17:ElementRef;
  @ViewChild('interest_percentage') focus18:ElementRef;
  @ViewChild('customer_id') focus19:ElementRef;
  @ViewChild('emi_months') focus20:ElementRef;

  UpdateLoan: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  bankList: any;
  hideDebitTo: boolean=true;
  hideCheque: boolean=true;
  hideBank: boolean=true;
  hideTransaction: boolean=true;
  hideInterestType: boolean=true;
  hideInterestAmount: boolean=true;
  hideTotalAmount: boolean=true;
  hidetotamount: boolean=true;
  hideintAmount: boolean=true;
  currentDate:any;
  employeeList: any;
  companyList: any[];
  customerList: any;
  numOnlyPattern = "^[0-9]*$";
  decimalPattern = "^[0-9]+(.[0-9])?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumDotSpPattern = "^[a-zA-Z0-9. ]*$";
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
  loanId: any;
  mantFieldAlertMsg: any;
  minDate = undefined;
  maxDate= undefined;
  
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
  total: number;
  totalAmt: number;
  
  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

  constructor(private http: HttpClient,private modalService: NgbModal,private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
    return this.UpdateLoan.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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

  branchChg(event) {
   // console.log("aaaa")
    this.setCollectionEmployeeList(event.target.value);
  }

  
  interestAmtChg(event) { 
    this.UpdateLoan.get('interest_amount').setValue('');
    this.hidetotamount = false;
    let amtvalue = event.target.value;
    if(amtvalue >= 0){
     this.total = this.UpdateLoan.get('loan_amount').value  * amtvalue / 100;
     this.UpdateLoan.get('interest_amount').setValue(this.total);
     this.totalAmt = this.total + parseInt(this.UpdateLoan.get('loan_amount').value);
    }
  }

  amountChg(event)
  {
    this.UpdateLoan.get('interest_percentage').setValue('');
  }

  loanTypeChg(event)
  {
    let loantype = event.target.value;
    this.hideInterestAmount=true;
    if(loantype == 2){
      this.hideInterestAmount=false;
    }
    this.setLoanTypeList(event.target.value);
  }

  setLoanTypeList(loan_type) {
  if (loan_type == 1) {
  this.hideInterestType=true;
  }
  else if (loan_type == 2) {
  this.hideInterestType=false;
  }
  else{
    this.hideInterestType=true;
  }
 
  let loanType = loan_type;
  //cash - 1
  let interestType = this.UpdateLoan.get('interest_type');
  if(loanType=='1') {
    interestType.setValidators(null);
  }
  else if(loanType=='2') {
    interestType.setValidators([Validators.required]);
  }
  interestType.updateValueAndValidity();
  }

  interestTypeChg(event)
  {
    this.setInterestTypeList(event.target.value);
  }
  setInterestTypeList(interest_type) {
    if (interest_type == 1) {
    this.hideInterestAmount=false;
    }
    else if (interest_type == 2) {
    this.hideInterestAmount=false;
    }
    else if (interest_type == 3) {
      this.hideInterestAmount=false;
      }
    else{
      this.hideInterestAmount=true;
    }
    let interestType = interest_type;
    //cash - 1
    let interestAmount = this.UpdateLoan.get('interest_percentage');
    if(interestType=='1') {
      interestAmount.setValidators([Validators.required,Validators.pattern(this.decimalPattern)]);
    }
    else if(interestType=='2') {
      interestAmount.setValidators([Validators.required,Validators.pattern(this.decimalPattern)]);
    }
    else if(interestType=='3') {
      interestAmount.setValidators([Validators.required,Validators.pattern(this.decimalPattern)]);
    }
    interestAmount.updateValueAndValidity();
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

    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  open(content, options = {}) {
    this.modalService.open(content, options).result.then((result) => {
        
    }, (reason) => {
        
    });
  }

  bankChg(event)
  {
    this.UpdateLoan.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.UpdateLoan.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
  }

  paymentChg(event)
 {
  this.setPaymentTypeList(event.target.value);
}
setPaymentTypeList(payment_type_id) {
 if (payment_type_id == 1) {
 this.hideDebitTo=false;
 this.hideCheque=true;
 this.hideBank=true;
 this.hideTransaction=true;
 }
 else if (payment_type_id == 2) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (payment_type_id == 3) {
 this.hideDebitTo=true;
 this.hideCheque=false;
 this.hideBank=false;
 this.hideTransaction=true;
 }
 else if (payment_type_id == 4){
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
 "conditions[tenant_id]" : this.UpdateLoan.get('tenant_id').value,
 "conditions[branch_id]" : this.UpdateLoan.get('branch_id').value,
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

 let paymentType = payment_type_id;
 //cash - 1
 let debitTo = this.UpdateLoan.get('debit_to');
 //cheque, DD - 2,3
 let chequeNo = this.UpdateLoan.get('cheque_no');
 let chequeDate = this.UpdateLoan.get('cheque_date');
 let bankId = this.UpdateLoan.get('bank_name_id');
 let bankBranchName = this.UpdateLoan.get('bank_branch_name');
 //NEFT - 4
 let transactionNo = this.UpdateLoan.get('transaction_no');
 let transactionDate = this.UpdateLoan.get('transaction_date');
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
  
  triggerFalseClick() {  
    let element: HTMLElement = document.getElementById("myDiv") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
    element = document.getElementById("myDiv") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateLoan.value); 
    if (this.UpdateLoan.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.loan_date.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.loan_amount.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.reference_grp.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.loan_type.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.interest_type.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.interest_amount.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.collect_employee_id.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.debit_to.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.cheque_date.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.bank_name_id.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.transaction_date.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.transaction_no.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.interest_percentage.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.emi_months.errors) {
        this.focus19.nativeElement.focus();
      }
      return;
    }

    
    this.userservice.getData(result, "update-loan-detail", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Loan",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-loan']);
          }, 300);
        } else {
          this.appService.showAlert("Loan", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Loan", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateLoan = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      emi_months: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      loan_date: [''],
      loan_type: ['', Validators.required],
      collect_employee_id: ['', Validators.required],
      reference_grp: [''],
      loan_amount: [''],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      interest_amount:['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      interest_percentage:['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      interest_type:['', Validators.required],
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
      this.loanId = params.id;
      this.UpdateLoan.get('id').setValue(this.loanId);
      this.UpdateLoan.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.loanId 
      }
      this.userservice.getData(cond, "get-loan-detail","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateLoan.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateLoan.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateLoan.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateLoan.get('emi_months').setValue(response.data[0].emi_months);
            this.UpdateLoan.get('collect_employee_id').setValue(response.data[0].collect_employee_id);
            this.UpdateLoan.get('loan_type').setValue(response.data[0].loan_type);
            this.UpdateLoan.get('reference_grp').setValue(response.data[0].reference_grp);
            this.UpdateLoan.get('loan_amount').setValue(response.data[0].loan_amount);
            if(response.data[0].loan_date!='') {
              let loanDate = (response.data[0].loan_date).split('/');
              let setLoandate = {
                'year': parseInt(loanDate[2]),
                'month': parseInt(loanDate[1]),
                'day': parseInt(loanDate[0])
              }
              this.UpdateLoan.get('loan_date').setValue(setLoandate);
            }
            this.UpdateLoan.get('interest_percentage').setValue(response.data[0].interest_percentage);
            this.UpdateLoan.get('interest_type').setValue(response.data[0].interest_type);
            this.UpdateLoan.get('interest_amount').setValue(response.data[0].interest_amount);
            this.UpdateLoan.get('payment_type_id').setValue(response.data[0].payment_type_id);
            this.UpdateLoan.get('debit_to').setValue(response.data[0].debit_to);
            this.UpdateLoan.get('cheque_no').setValue(response.data[0].cheque_no);
            if(response.data[0].cheque_date!='') {
              let chqDate = (response.data[0].cheque_date).split('/');
              let setchqDate = {
                'year': parseInt(chqDate[2]),
                'month': parseInt(chqDate[1]),
                'day': parseInt(chqDate[0])
              }
              this.UpdateLoan.get('cheque_date').setValue(setchqDate);
            }
            this.UpdateLoan.get('bank_name_id').setValue(response.data[0].bank_name_id);
            this.UpdateLoan.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            this.UpdateLoan.get('transaction_no').setValue(response.data[0].transaction_no);
            this.UpdateLoan.get('transaction_date').setValue(response.data[0].transaction_date);
         
            
            this.setBranchList(response.data[0].tenant_id);
            this.setCollectionEmployeeList(response.data[0].branch_id);
            this.setLoanTypeList(response.data[0].loan_type);
            this.setInterestTypeList(response.data[0].interest_type);
            this.setPaymentTypeList(response.data[0].payment_type_id);
        
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
          else {
            this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {console.log(err);
          this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Loan", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
