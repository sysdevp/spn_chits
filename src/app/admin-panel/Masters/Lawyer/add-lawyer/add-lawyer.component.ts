import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-lawyer',
  templateUrl: './add-lawyer.component.html',
  styles: []
})
export class AddLawyerComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('lawyer_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  @ViewChild('lawyer_percentage') focus4:ElementRef;
  @ViewChild('mobile_no') focus5:ElementRef;

  AddLawyer: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  isValidMobile: boolean = true;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddLawyer.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
  }

  onKeyUpMobile(event) { 
    let mobileNumber = event.target.value;
    if(mobileNumber=='') {
      this.isValidMobile = true;
    }
    else {
      if((mobileNumber).match(this.mobnumPattern)) {
        this.isValidMobile = true;
      }
      else {
        this.isValidMobile = false;
      }
    }
  }


  

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddLawyer.value);
    if (this.AddLawyer.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.lawyer_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.lawyer_percentage.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus5.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-lawyer-name", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Lawyer Name",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-lawyer']);
          }, 300);
        } else {
          this.appService.showAlert("Lawyer", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddLawyer = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      lawyer_name: ['', Validators.required],
      lawyer_percentage: ['', Validators.required],
      mobile_no:['',[Validators.required,Validators.pattern(this.mobnumPattern)]],
      remarks: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddLawyer.get('tenant_id').setValue(tenant_id);
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
    this.AddLawyer.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
