import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-accounting-head',
  templateUrl: './update-accounting-head.component.html',
  styles: []
})
export class UpdateAccountingHeadComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  @ViewChild('group_id') focus4:ElementRef;
  @ViewChild('amount') focus5:ElementRef;
  @ViewChild('collection_type') focus6:ElementRef;
  @ViewChild('account_number') focus7:ElementRef;
  @ViewChild('branch_id') focus8:ElementRef;

  UpdateAccountingHead: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  branchList: any[];
  openingBalance: boolean = false;
  test: any[];
  pettycashexpensetypeId: any;
  groupList: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {
    this.SubmitbuttonName = "Submit";
  }
  get f() {
    return this.UpdateAccountingHead.controls;
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
  }

  grpChg(event) {
    let group_id = event.target.value;
    
    let cond = {
      "conditions[group_id]" : group_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.test = response.data[0].under;
          if(response.data[0].under == 'Expenses'){
            this.openingBalance = true;
          }
          else if(response.data[0].under == 'Incomes'){
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
    const result = Object.assign({}, this.UpdateAccountingHead.value);
    if (this.UpdateAccountingHead.invalid) 
    {
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
    this.userservice.getData(result,"update-head-group","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Accounting Head", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-accounting-head']);
          }, 3000);
        } else {
          this.appService.showAlert("Accounting Head", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
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
        this.appService.showAlert("Accounting Head", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateAccountingHead = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      name: ['', Validators.required],
      group_id: ['', Validators.required],
      collection_type: ['', Validators.required],
      amount: ['0', [Validators.required,Validators.pattern(this.decimalPattern)]],
      account_number: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      remarks: [''],
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
      this.pettycashexpensetypeId = params.id;
      this.UpdateAccountingHead.get('id').setValue(this.pettycashexpensetypeId);
      this.UpdateAccountingHead.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.pettycashexpensetypeId 
      }
      this.userservice.getData(cond, "get-head-group","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateAccountingHead.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateAccountingHead.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateAccountingHead.get('name').setValue(response.data[0].name);
            this.UpdateAccountingHead.get('amount').setValue(response.data[0].amount);
            this.UpdateAccountingHead.get('account_number').setValue(response.data[0].account_number);
            this.UpdateAccountingHead.get('collection_type').setValue(response.data[0].collection_type);
            this.UpdateAccountingHead.get('remarks').setValue(response.data[0].remarks);
            this.UpdateAccountingHead.get('group_id').setValue(response.data[0].group_id);
            if(response.data[0].accounts_group_det.main_under == 'Expenses'){
              this.openingBalance = true;
              this.UpdateAccountingHead.get('amount').setValue('0');
              this.UpdateAccountingHead.get('collection_type').setValue('0');
            }
            else if(response.data[0].accounts_group_det.main_under == 'Incomes'){
              this.openingBalance = true;
              this.UpdateAccountingHead.get('amount').setValue('0');
              this.UpdateAccountingHead.get('collection_type').setValue('0');
            }
            else{
              this.openingBalance = false;
              this.UpdateAccountingHead.get('amount').setValue(response.data[0].amount);
              this.UpdateAccountingHead.get('collection_type').setValue(response.data[0].collection_type);
            }
            this.setBranchList(response.data[0].tenant_id);
            this.setAccountGroupList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

