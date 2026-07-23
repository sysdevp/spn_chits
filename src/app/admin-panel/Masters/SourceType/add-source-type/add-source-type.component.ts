import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-source-type',
  templateUrl: './add-source-type.component.html',
  styles: []
})
export class AddSourceTypeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('source_type_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  AddSourceType: FormGroup;
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
    return this.AddSourceType.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
  }

  

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddSourceType.value);
    if (this.AddSourceType.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.source_type_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-source-type", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Source Type",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-source-type']);
          }, 300);
        } else {
          this.appService.showAlert("Source Type", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddSourceType = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      source_type_name: ['', Validators.required],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddSourceType.get('tenant_id').setValue(tenant_id);
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
    this.AddSourceType.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
