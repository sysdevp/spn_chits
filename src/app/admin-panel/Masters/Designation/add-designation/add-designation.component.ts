import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-designation',
  templateUrl: './add-designation.component.html',
  styles: []
})

export class AddDesignationComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('designation_name') focus2:ElementRef;
  @ViewChild('remarks') focus4:ElementRef;

  AddDesignation: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddDesignation.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddDesignation.value);
    if (this.AddDesignation.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.designation_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-designation", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Designation",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-designation']);
          }, 300);
        } else {
          this.appService.showAlert("Designation", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddDesignation = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      designation_name: ['', Validators.required],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddDesignation.get('tenant_id').setValue(tenant_id);
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
    this.AddDesignation.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
