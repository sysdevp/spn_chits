import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-bank-details',
  templateUrl: './update-bank-details.component.html',
  styles: []
})

export class UpdateBankDetailsComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('bank_name') focus3:ElementRef;
  @ViewChild('account_number') focus4:ElementRef;
  @ViewChild('ifsc_code') focus5:ElementRef;
  @ViewChild('bank_code') focus6:ElementRef;
  @ViewChild('bank_branch_name') focus7:ElementRef;
  
  UpdateBank: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  bankId: any;
  branchList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  numOnlyPattern = "^[0-9]*$";   
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumPattern = "^[a-zA-Z0-9]*$";
  
  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateBank.controls;
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

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateBank.value);
    if (this.UpdateBank.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.bank_name.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.account_number.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.ifsc_code.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.bank_code.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.bank_branch_name.errors) {
        this.focus7.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-bank","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Bank", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-bank-details']);
          }, 3000);
        } else {
          this.appService.showAlert("Bank", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateBank = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      bank_name:['',[Validators.required,Validators.pattern(this.txtSpPattern)]],
      account_number:['',[Validators.required,Validators.pattern(this.numOnlyPattern)]],
      ifsc_code:['',[Validators.required,Validators.pattern(this.alphaNumPattern)]],
      bank_code:['', Validators.required],
      bank_branch_name:[''],
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
      this.bankId = params.id;
      this.UpdateBank.get('id').setValue(this.bankId);
      this.UpdateBank.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.bankId 
      }
      this.userservice.getData(cond, "get-banks","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateBank.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateBank.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateBank.get('bank_name').setValue(response.data[0].bank_name);
            this.UpdateBank.get('account_number').setValue(response.data[0].account_number);
            this.UpdateBank.get('ifsc_code').setValue(response.data[0].ifsc_code);
            this.UpdateBank.get('bank_code').setValue(response.data[0].bank_code);
            this.UpdateBank.get('bank_branch_name').setValue(response.data[0].bank_branch_name);
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
