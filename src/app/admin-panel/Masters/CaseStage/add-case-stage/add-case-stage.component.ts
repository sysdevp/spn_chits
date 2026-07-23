import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-case-stage',
  templateUrl: './add-case-stage.component.html',
  styles: []
})
export class AddCaseStageComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('case_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  AddCaseStage: FormGroup;
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
    return this.AddCaseStage.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
  }

  

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCaseStage.value);
    if (this.AddCaseStage.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.case_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-case-name", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Case Name",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-case-stage']);
          }, 300);
        } else {
          this.appService.showAlert("Case", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Case", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Case", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Case", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCaseStage = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      case_name: ['', Validators.required],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCaseStage.get('tenant_id').setValue(tenant_id);
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
    this.AddCaseStage.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
