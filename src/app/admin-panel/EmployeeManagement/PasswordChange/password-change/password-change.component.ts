import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-password-change',
  templateUrl: './password-change.component.html',
  styles: []
})
export class PasswordChangeComponent implements OnInit {
  @ViewChild('old_password') focus1: ElementRef;
  @ViewChild('new_password') focus2: ElementRef;
  @ViewChild('confirm_password') focus4: ElementRef;

  PasswordChange: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  ConfirmPasswordAlertMsg: boolean = false;
  id: string;
  showPassword: boolean = false;
  showConfirmPassword: boolean = false;
  showOldPassword: boolean = false;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.PasswordChange.controls;
  }

  onPwdChange(event) {
    let confirmPassword = event.target.value;
    let new_password = this.PasswordChange.get('new_password').value;
    if (confirmPassword == new_password) {
      this.ConfirmPasswordAlertMsg = false;
    }
    else {
      this.ConfirmPasswordAlertMsg = true;
    }
  }

  showHidePassword(passwordType, showHide) {
    if (passwordType == 'password') {
      this.showPassword = showHide;
    }
    else if (passwordType == 'confirmPassword') {
      this.showConfirmPassword = showHide;
    }
    else if (passwordType == 'oldPassword') {
      this.showOldPassword = showHide;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.PasswordChange.value);
    if (this.PasswordChange.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if (this.f.old_password.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.new_password.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.confirm_password.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }

    //this.id=  localStorage.getItem("token");

    this.userservice.getData(result, "change-password", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Password Change", response.msg, "success");

          setTimeout(() => {
            window.location.href = "/";
          }, 300);

        } else {
          this.appService.showAlert("Password Change", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Password Change", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Password Change", "Old Password is Incorrect", "error");
      }
      else {
        this.appService.showAlert("Password Change", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.PasswordChange = this.formBuilder.group({
      old_password: ['', Validators.required],
      new_password: [
    '',
    [
      Validators.required,
      Validators.minLength(8),
      Validators.pattern(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/)
    ]
  ],
      confirm_password: ['', Validators.required],
      created_by: [''],
    });
    /*let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.PasswordChange.get('tenant_id').setValue(tenant_id);
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
    }*/
    this.PasswordChange.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
