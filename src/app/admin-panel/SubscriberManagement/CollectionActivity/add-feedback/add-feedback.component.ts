import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-feedback',
  templateUrl: './add-feedback.component.html',
  styles: []
})
export class AddFeedbackComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('remark_date') focus3:ElementRef;
  @ViewChild('remark_id') focus4:ElementRef;
  @ViewChild('next_followup_date') focus5:ElementRef;
  @ViewChild('customer_id') focus6:ElementRef;
  @ViewChild('employee_id') focus7:ElementRef;

  form_message = true;
  AddFeedBack: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any; 
  remarkList: any[];
  isSuperAdmin: boolean;
  companyList: any[];
  numOnlyPattern = "^[0-9]*$";   
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumPattern = "^[a-zA-Z0-9]*$";
  currentDate:any;
  customerList: any;
  employeeList: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
      let today_date = new Date();
      this.currentDate = {
        'year': today_date.getFullYear(),
        'month': today_date.getMonth()+1,
        'day': today_date.getDate()
      };
  }

  get f() {
      return this.AddFeedBack.controls;
  }


  companyChg(event) {
      let tenant_id = event.target.value;
      this.setBranchList(tenant_id);
      this.setDistrictList(tenant_id);
  }

  branchChg(event)
 {
    let cond = {
    "conditions[branch_id]" : event.target.value
    }
 
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
    try { 
    if (response.status == 'Success') { 
    this.customerList = response.data;
    } 
    else {
    this.customerList = [];
    }
    } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
      if (response.status == 'Success') { 
      this.employeeList = response.data;
      } 
      else {
      this.employeeList = [];
      }
      } catch (err) { }
      }, (err: any) => { });
 }

  setBranchList(tenant_id) { 
      let cond = {
        "conditions[tenant_id]" : tenant_id,
        "conditions[status]" : 1,
        "log_id" : localStorage.getItem('logged_employee_id')
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

  setDistrictList(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id
    } 
    this.userservice.getData(cond, "app-get-collection-remarks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.remarkList = response.data;
        } else {
          this.remarkList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
}

  onSubmit() {
      this.SubmitbuttonName = "Loading...";
      this.submitted = true;
      this.disableBtn = true;
      const result = Object.assign({}, this.AddFeedBack.value);
      if (this.AddFeedBack.invalid) {
          this.SubmitbuttonName = "Submit";
          this.disableBtn = false;
          if(this.f.tenant_id.errors) {
              this.focus1.nativeElement.focus();
          }
          else if(this.f.branch_id.errors) {
              this.focus2.nativeElement.focus();
          }
          else if(this.f.remark_date.errors) {
              this.focus3.nativeElement.focus();
          }
          else if(this.f.remark_id.errors) {
              this.focus4.nativeElement.focus();
          }
          else if(this.f.next_followup_date.errors) {
            this.focus5.nativeElement.focus();
          }
          else if(this.f.customer_id.errors) {
            this.focus6.nativeElement.focus();
          }
          else if(this.f.employee_id.errors) {
            this.focus7.nativeElement.focus();
          }
          return;
      }
      this.userservice.getData(result, "app-store-remarks", "JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
              if (response.status == 'Success') {
              this.appService.showAlert("Feed Back",response.msg, "success");
                  setTimeout(() => {
                      this.router.navigate(['/admin-panel/view-feed-back']);
                  }, 300);
              } else {
                  this.appService.showAlert("Feed Back", response.msg, "error");
                  this.disableBtn = false;
              }
          } catch (err) {
              this.appService.showAlert("Feed Back", "Sorry!! Something went wrong!", "error");
              this.disableBtn = false;
          }
      }, (err: any) => {
          this.disableBtn = false;
          this.SubmitbuttonName = "Submit";
          if (err.status == 401) {
              this.appService.showAlert("Feed Back", "Sorry!! Something went wrong!", "error");
          }
          else {
              this.appService.showAlert("Feed Back", "Sorry!! Something went wrong!", "error");
          }
      });
  }

  ngOnInit() {
      this.AddFeedBack = this.formBuilder.group({
          tenant_id:['', Validators.required],
          branch_id: ['', Validators.required],
          remark_date:['', Validators.required],
          next_followup_date:[null, Validators.required],
          remark_id:['', Validators.required],
          customer_id:['', Validators.required],
          employee_id:['', Validators.required],
          created_by: [''],
      });

      let tenant_id = localStorage.getItem('tenant_id');
      const current = new Date();
      let currentDate = {
        year: current.getFullYear(),
        month: current.getMonth() + 1,
        day: current.getDate()
      }
    this.AddFeedBack.get('remark_date').setValue(currentDate);
      if(tenant_id) {
        this.AddFeedBack.get('tenant_id').setValue(tenant_id);
        this.isSuperAdmin = false;
        this.setBranchList(tenant_id);
        this.setDistrictList(tenant_id);
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
      this.AddFeedBack.get('created_by').setValue(localStorage.getItem('logged_user_id'));

  }
}

