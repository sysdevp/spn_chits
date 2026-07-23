import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-accounting-head',
  templateUrl: './add-accounting-head.component.html',
  styles: []
})
export class AddAccountingHeadComponent implements OnInit {
  AddAccountingHead: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  accountingGroupList: any;
  companyList: any[];
  branchList: any[];
  isSuperAdmin: boolean;
  openingBalance: boolean = false;
  groupList: any[];
  test: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  account_number: any;
  account_number_final: any;
  account_number_f: any;

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  @ViewChild('group_id') focus4:ElementRef;
  @ViewChild('amount') focus5:ElementRef;
  @ViewChild('collection_type') focus6:ElementRef;
  @ViewChild('account_number') focus7:ElementRef;
  @ViewChild('branch_id') focus8:ElementRef;
  

  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
      this.account_number_f = this.account_number_final;
  }

  get f() {
    return this.AddAccountingHead.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setAccountGroupList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id
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
  
  setAccountGroupList(tenant_id){
    
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-account-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-account-group-account-num", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.account_number = response.data;
          this.account_number_final = this.account_number + 1;
        } else {
          this.account_number = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  grpChg(event) {
    let group_id = event.target.value;
    
    let cond = {
      "conditions[id]" : group_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-account-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.test = response.data[0].main_under;
          if(response.data[0].main_under == 'Expenses'){
            this.openingBalance = true;
          }
          else if(response.data[0].main_under == 'Incomes'){
            this.openingBalance = true;
          }
          else{
            this.openingBalance = false;
          }
          
        } else {
          this.test = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddAccountingHead.value);
    if (this.AddAccountingHead.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.amount.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.collection_type.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.account_number.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus8.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-head-group", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Accounting Head",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-accounting-head']);
          }, 300);
        } else {
          this.appService.showAlert("Accounting Head", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddAccountingHead = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      name: ['', Validators.required],
      group_id: ['', Validators.required],
      collection_type: ['0', Validators.required],
      amount: ['0', [Validators.required,Validators.pattern(this.decimalPattern)]],
      account_number: [this.account_number_f],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddAccountingHead.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
	    this.setAccountGroupList(tenant_id);
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
    this.AddAccountingHead.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

