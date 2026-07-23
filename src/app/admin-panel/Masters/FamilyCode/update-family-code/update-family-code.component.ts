import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-family-code',
  templateUrl: './update-family-code.component.html',
  styles: []
})
export class UpdateFamilyCodeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('family_code') focus3:ElementRef;

  UpdateFamilyCode: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  roleId: any;
  isSuperAdmin: boolean;
  companyList: any[];
  roleTypeList: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateFamilyCode.controls;
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
    const result = Object.assign({}, this.UpdateFamilyCode.value);
    if (this.UpdateFamilyCode.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.family_code.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-family-code","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Family Code", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-family-code']);
          }, 3000);
        } else {
          this.appService.showAlert("Family Code", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateFamilyCode = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      family_code: ['', Validators.required],
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
      this.roleId = params.id;
      this.UpdateFamilyCode.get('id').setValue(this.roleId);
      this.UpdateFamilyCode.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.roleId 
      }
      this.userservice.getData(cond, "get-family-codes","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateFamilyCode.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateFamilyCode.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateFamilyCode.get('family_code').setValue(response.data[0].family_code);
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Family Code", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
