import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router,ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-followups',
  templateUrl: './add-followups.component.html',
  styles: []
})

export class AddFollowupsComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('followup_date') focus4:ElementRef;
  @ViewChild('next_followup_date') focus5:ElementRef;
  @ViewChild('remarks') focus6:ElementRef;
  @ViewChild('followup_status_type_id') focus7:ElementRef;
  @ViewChild('lead_customer_name') focus8:ElementRef;
    
  AddFollowups: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  companyList: any[];
  employeeList: any;
  followupstatusList: any;
  txtSpPattern = "^[a-zA-Z ]*$";
  leadId: any;
  current:any;
  minDate = undefined;
  maxDate= undefined;

  constructor(private appService: AppService, private route: ActivatedRoute ,private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {
    const current = new Date();
    this.minDate = {
      year: current.getFullYear(),
      month: current.getMonth() + 1,
      day: current.getDate()
    };
    this.maxDate = {
      year: current.getFullYear(),
      month: current.getMonth() + 1,
      day: current.getDate() - 1
    };
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddFollowups.controls;
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setEmployeeList(branch_id);
  }

  setEmployeeList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  follDateChg(event) { 
    let selectedDate = event;
    this.minDate = {
      year: selectedDate.year,
      month: selectedDate.month,
      day: selectedDate.day
    };
  }
 
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddFollowups.value);
    if (this.AddFollowups.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
    
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.followup_date.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.next_followup_date.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.followup_status_type_id.errors) {
        this.focus7.nativeElement.focus();
      } 
      else if(this.f.lead_customer_name.errors) {
        this.focus8.nativeElement.focus();
      } 
      return;      
    }

    this.userservice.getData(result, "store-followup", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit"; 
      try { 
        if (response.status == 'Success') {
          this.appService.showAlert("Followup",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-lead-management']);
          }, 300);
        } else {
          this.appService.showAlert("Followup", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Followup", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddFollowups = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      followup_date:['', Validators.required],
      next_followup_date:[null, Validators.required],
      lead_customer_name:[''],
      lead_management_id:[''],
      remarks:[''],
      followup_status_type_id:['', Validators.required],
      created_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddFollowups.get('tenant_id').setValue(tenant_id);
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
    this.AddFollowups.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-followup-status", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.followupstatusList = response.data;
        } else {
          this.followupstatusList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.route.params.subscribe(params => {
      this.leadId = params.id;
      this.AddFollowups.get('lead_management_id').setValue(this.leadId);
      this.AddFollowups.get('created_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[lead_management_id]" : this.leadId 
      }
      this.userservice.getData(cond, "get-followups","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.AddFollowups.get('tenant_id').setValue(response.data[0].tenant_id);
            this.AddFollowups.get('branch_id').setValue(response.data[0].branch_id);
            this.AddFollowups.get('employee_id').setValue(response.data[0].employee_id);
            this.AddFollowups.get('followup_status_type_id').setValue(response.data[0].followup_status_type_id);
            let currentDate = new Date();
            let setFdate = {
              'year': currentDate.getFullYear(),
              'month': currentDate.getMonth() + 1,
              'day': currentDate.getDate()
            }
            this.AddFollowups.get('followup_date').setValue(setFdate);
            this.minDate = {
              year: currentDate.getFullYear(),
              month: currentDate.getMonth() + 1,
              day: currentDate.getDate()
            };
            this.maxDate = {
              year: currentDate.getFullYear(),
              month: currentDate.getMonth() + 1,
              day: currentDate.getDate()
            };
            this.setBranchList(response.data[0].tenant_id);
            this.setEmployeeList(response.data[0].branch_id);
          }
          else {
            this.appService.showAlert("Followups", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Followups", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Followups", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Followups", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
