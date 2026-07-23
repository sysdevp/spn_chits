import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-scheme',
  templateUrl: './add-scheme.component.html',
  styles: []
})

export class AddSchemeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('chit_value') focus3:ElementRef;
  @ViewChild('no_of_months') focus4:ElementRef;
  @ViewChild('members') focus5:ElementRef;
  @ViewChild('register_fees') focus6:ElementRef;
  @ViewChild('enrollment_fees') focus7:ElementRef;
  @ViewChild('chit_letter') focus8:ElementRef;
  @ViewChild('short_term') focus9:ElementRef;
  @ViewChild('monthly_due') focus10:ElementRef;
  
  AddScheme: FormGroup;
  submitted = false;
  disableBtn = false;
  changeText = false;
  changeText1 = false;
  changeText2 = false;
  changeText3 = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  companyList: any[];
  monthlyDueGreaterAlert: boolean;
  numOnlyPattern = "^[0-9]*$";
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddScheme.controls;
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
    const result = Object.assign({}, this.AddScheme.value);
    if (this.AddScheme.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.monthlyDueGreaterAlert = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.chit_value.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.no_of_months.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.members.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.register_fees.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.enrollment_fees.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.chit_letter.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.short_term.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.monthly_due.errors) {
        this.focus10.nativeElement.focus();
      }
      return;
    }
    if(parseInt(this.f.monthly_due.value)==0) {
      this.monthlyDueGreaterAlert = true;
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
    }
    else {
      this.userservice.getData(result, "store-scheme", "JSON").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.appService.showAlert("Scheme",response.msg, "success");
            setTimeout(() => {
              this.router.navigate(['/admin-panel/view-scheme']);
            }, 300);
          } else {
            this.appService.showAlert("Scheme", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          this.appService.showAlert("Scheme", 'Sorry!! Something went wrong!', "error");
          this.disableBtn = false;
        }
      }, (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Scheme", "Sorry!! Something went wrong!", "error");
        }
      });
    }
  }

  ngOnInit() {
    this.AddScheme = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      chit_value: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      no_of_months: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      members: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      register_fees: ['', [Validators.pattern(this.numOnlyPattern)]],
      enrollment_fees: ['', [Validators.pattern(this.numOnlyPattern)]],
      chit_letter: [''],
      short_term: [''],
      monthly_due: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      created_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    this.monthlyDueGreaterAlert = false;
    if(tenant_id) {
      this.AddScheme.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
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
    this.AddScheme.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
