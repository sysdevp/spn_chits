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
  
  selector: 'app-add-loan',
  templateUrl: './add-loan.component.html',
  styles: []
})
export class AddLoanComponent implements OnInit {
  Emi: any;
  [x: string]: any;
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('loan_date') focus3:ElementRef;
  @ViewChild('loan_amount') focus4:ElementRef;
  @ViewChild('reference_grp') focus5:ElementRef;
  @ViewChild('collect_employee_id') focus6:ElementRef;
  @ViewChild('payment_type_id') focus7:ElementRef;
  @ViewChild('cheque_no') focus8:ElementRef;
  @ViewChild('cheque_date') focus9:ElementRef;
  @ViewChild('bank_name_id') focus10:ElementRef;
  @ViewChild('bank_branch_name') focus11:ElementRef;
  @ViewChild('transaction_no') focus12:ElementRef;
  @ViewChild('transaction_date') focus13:ElementRef;
  @ViewChild('debit_to') focus14:ElementRef;
  @ViewChild('loan_type') focus15:ElementRef;
  @ViewChild('interest_type') focus16:ElementRef;
  @ViewChild('interest_percentage') focus17:ElementRef;
  @ViewChild('interest_amount') focus18:ElementRef;
  @ViewChild('customer_id') focus19:ElementRef;
  @ViewChild('emi_months') focus20:ElementRef;
  
  AddLoan: FormGroup;
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
  currentDate:any;
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
  hideEnrollDetail:boolean=true;
  EnrollList: any[];

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
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
    return this.AddLoan.controls;
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
    this.setCollectionEmployeeList(event.target.value);
  }

  customerChg(item: any) {
    this.EnrollList = [];
    let custname = item;
    this.custid = custname.customer_id;
    this.hideEnrollDetail = false;
    let tenant_id = this.AddLoan.get('tenant_id').value;
    let cond = {
      "customer_id" : this.custid,
      "tenant_id" : tenant_id
    }
    this.userservice.getData(cond, "alert-auction-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.EnrollList = response.data;
        } else {
          this.EnrollList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  amountChg(event) { 
    this.AddLoan.get('interest_percentage').setValue('');
    this.totalAmt = '';
    let amtvalue = event.target.value;
    if(amtvalue == 0){
      this.AddLoan.get('loan_amount').setValue('');
    }
    if(amtvalue == ''){
      this.hidePayment = true;
    }
    else{
      this.hidePayment = false;
    }
  }

  emiChg(event) { 
    let emivalue = event.target.value;
    if(emivalue == 0){
      this.AddLoan.get('emi_months').setValue('');
    }
  }
  
  interestAmtChg(event) { 
    this.hidetotamount = false;
    let amtvalue = event.target.value;
    if(amtvalue >= 0){
     this.total = this.AddLoan.get('loan_amount').value  * amtvalue / 100;
     console.log(this.total);
     this.AddLoan.get('interest_amount').setValue(this.total);
     this.totalAmt = this.total + parseInt(this.AddLoan.get('loan_amount').value);
     console.log(this.totalAmt);
    }
  }

  loanTypeChg(event)
  {
    this.hideInterestAmount=true;
    this.hidetotamount=true;
  if (event.target.value == 1) {
  this.hideInterestType=true;
  }
  else if (event.target.value == 2) {
  this.hideInterestType=false;
  }
  else{
    this.hideInterestType=true;
  }
 
  let loanType = event.target.value;
  //cash - 1
  let interestType = this.AddLoan.get('interest_type');
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

    if (event.target.value == 1) {
    this.hideInterestAmount=false;
    }
    else if (event.target.value == 2) {
    this.hideInterestAmount=false;
    }
    else if (event.target.value == 3) {
      this.hideInterestAmount=false;
      }
    else{
      this.hideInterestAmount=true;
    }
    let interestType = event.target.value;
    //cash - 1
    let interestAmount = this.AddLoan.get('interest_percentage');
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

    this.userservice.getData(cond, "get-all-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
          this.dropdownList = [];
          for(let i=0; i<((this.customerList).length); i++) {
              var obj = {};
              obj["customer_id"] = response.data[i].id;
              obj["name"] = response.data[i].name;
              this.dropdownList.push(obj);
            }
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  bankChg(event)
  {
    this.AddLoan.get('bank_branch_name').setValue('');
    let bankDet =  this.bankList.filter((listing: any) => listing.id == event.target.value);
    this.AddLoan.get('bank_branch_name').setValue(bankDet[0].bank_branch_name);
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
 "conditions[tenant_id]" : this.AddLoan.get('tenant_id').value,
 "conditions[branch_id]" : this.AddLoan.get('branch_id').value,
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
 let debitTo = this.AddLoan.get('debit_to');
 //cheque, DD - 2,3
 let chequeNo = this.AddLoan.get('cheque_no');
 let chequeDate = this.AddLoan.get('cheque_date');
 let bankId = this.AddLoan.get('bank_name_id');
 let bankBranchName = this.AddLoan.get('bank_branch_name');
 //NEFT - 4
 let transactionNo = this.AddLoan.get('transaction_no');
 let transactionDate = this.AddLoan.get('transaction_date');
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
    const result = Object.assign({}, this.AddLoan.value);
    if (this.AddLoan.invalid) 
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
      else if(this.f.collect_employee_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.payment_type_id.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.cheque_no.errors) {
        this.focus8.nativeElement.focus();
        }
        else if(this.f.cheque_date.errors) {
        this.focus9.nativeElement.focus();
        }
        else if(this.f.bank_name_id.errors) {
        this.focus10.nativeElement.focus();
        }
        else if(this.f.bank_branch_name.errors) {
        this.focus11.nativeElement.focus();
        }
        else if(this.f.transaction_no.errors) {
        this.focus12.nativeElement.focus();
        }
        else if(this.f.transaction_date.errors) {
        this.focus13.nativeElement.focus();
        }
        else if(this.f.debit_to.errors) {
        this.focus14.nativeElement.focus();
        }
        else if(this.f.loan_type.errors) {
          this.focus15.nativeElement.focus();
        }
        else if(this.f.interest_type.errors) {
        this.focus16.nativeElement.focus();
        }
        else if(this.f.interest_percentage.errors) {
          this.focus17.nativeElement.focus();
        }
        else if(this.f.interest_amount.errors) {
          this.focus18.nativeElement.focus();
        }
        else if(this.f.customer_id.errors) {
          this.focus19.nativeElement.focus();
        }
        else if(this.f.emi_months.errors) {
          this.focus20.nativeElement.focus();
        }
      return;
    }


    this.userservice.getData(result, "store-loan-detail", "JSON").subscribe((response: any) => {
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
    this.AddLoan = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      emi_months: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      loan_date: [this.currentDate,Validators.required],
      loan_type: ['', Validators.required],
      collect_employee_id: [''],
      reference_grp: [''],
      loan_amount: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      payment_type_id: ['', Validators.required],
      cheque_no: [''],
      cheque_date: [null], 
      bank_name_id: [''],
      bank_branch_name: [''],
      transaction_no: [''],
      transaction_date: [null],
      debit_to: [''],
      interest_percentage: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      interest_amount:[''],
      interest_type:[''],
      created_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddLoan.get('tenant_id').setValue(tenant_id);
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
    this.AddLoan.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.userservice.getData("", "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
    this.dropdownSettings = {
      singleSelection: true,
      idField: 'customer_id',
      textField: 'name',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true
    };
  }
}
