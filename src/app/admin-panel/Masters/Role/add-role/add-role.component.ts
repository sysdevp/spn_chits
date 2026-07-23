import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-role',
  templateUrl: './add-role.component.html',
  styles: []
})

export class AddRoleComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('role_type_id') focus3:ElementRef;
  @ViewChild('role_name') focus4:ElementRef;
  @ViewChild('remarks') focus6:ElementRef;

  AddRole: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  roleTypeList: any[];
  underRoleList: any[];
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddRole.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setUnderRoleList(tenant_id);
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddRole.value);
    // Send both possible field names; backend can pick whichever it implements.
    result.under_role_id = result.under_id;
    if (this.AddRole.invalid) 
    {
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
      else if(this.f.remarks.errors) {
        this.focus6.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-role", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Role",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-role']);
          }, 300);
        } else {
          this.appService.showAlert("Role", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
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
        this.appService.showAlert("Role", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddRole = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      role_type_id: ['', Validators.required],
      role_name: ['', Validators.required],
      // Optional parent role for hierarchy building
      under_id: [''],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddRole.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setUnderRoleList(tenant_id);
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
    this.AddRole.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }

  setUnderRoleList(tenant_id: any) {
    if (!tenant_id) {
      this.underRoleList = [];
      return;
    }
    const result = { tenant_id };
    this.userservice.getData(result, "list-roles", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          // Backend will typically support filtering by tenant; we just use what we get.
          this.underRoleList = response.data || [];
        } else {
          this.underRoleList = [];
        }
      } catch (err) {
        this.underRoleList = [];
      }
    }, (err: any) => {
      this.underRoleList = [];
    });
  }
}
