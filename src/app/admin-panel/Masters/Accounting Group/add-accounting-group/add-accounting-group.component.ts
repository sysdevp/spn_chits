import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-accounting-group',
  templateUrl: './add-accounting-group.component.html',
  styles: []
})
export class AddAccountingGroupComponent implements OnInit {
  AddAccountingGroup: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  accountingGroupList: any;
  companyList: any[];
  isSuperAdmin: boolean;
  underList: any[];

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('group_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  @ViewChild('under') focus4:ElementRef;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddAccountingGroup.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setgrp(tenant_id);

  }
  setgrp(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-account-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.underList = response.data;
        } else {
          this.underList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddAccountingGroup.value);
    if (this.AddAccountingGroup.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.group_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.under.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-account-group", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Accounting Group",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-accounting-group']);
          }, 300);
        } else {
          this.appService.showAlert("Accounting Group", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddAccountingGroup = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      group_name: ['', Validators.required],
      under: ['', Validators.required],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddAccountingGroup.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setgrp(tenant_id);
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
    this.AddAccountingGroup.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
