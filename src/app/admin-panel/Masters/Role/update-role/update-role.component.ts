import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-role',
  templateUrl: './update-role.component.html',
  styles: []
})

export class UpdateRoleComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('role_type_id') focus3:ElementRef;
  @ViewChild('role_name') focus4:ElementRef;
  @ViewChild('role_code') focus5:ElementRef;
  @ViewChild('remarks') focus6:ElementRef;

  UpdateRole: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  roleId: any;
  isSuperAdmin: boolean;
  companyList: any[];
  roleTypeList: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateRole.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateRole.value);
    if (this.UpdateRole.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.role_type_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.role_name.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.role_code.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus6.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-role","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Role", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-role']);
          }, 3000);
        } else {
          this.appService.showAlert("Role", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateRole = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      role_type_id: ['', Validators.required],
      role_name: ['', Validators.required],
      role_code: [''],
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
    this.userservice.getData('', "list-role-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.roleTypeList = response.data;
        } else {
          this.roleTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.route.params.subscribe(params => {
      this.roleId = params.id;
      this.UpdateRole.get('id').setValue(this.roleId);
      this.UpdateRole.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.roleId 
      }
      this.userservice.getData(cond, "get-roles","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateRole.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateRole.get('role_type_id').setValue(response.data[0].role_type_id);
            this.UpdateRole.get('role_name').setValue(response.data[0].role_name);
            this.UpdateRole.get('role_code').setValue(response.data[0].role_code);
            this.UpdateRole.get('remarks').setValue(response.data[0].remarks);
          }
          else {
            this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
