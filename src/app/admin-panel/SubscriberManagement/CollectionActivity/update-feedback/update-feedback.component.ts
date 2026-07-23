import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-feedback',
  templateUrl: './update-feedback.component.html',
  styles: []
})
export class UpdateFeedbackComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('employee_id') focus4:ElementRef;
  @ViewChild('remark_date') focus5:ElementRef;
  @ViewChild('next_followup_date') focus6:ElementRef;
  @ViewChild('remark_id') focus7:ElementRef;
  @ViewChild('followup_status') focus8:ElementRef;
  
  UpdateFeedBack: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  bankId: any;
  branchList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  numOnlyPattern = "^[0-9]*$";   
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumPattern = "^[a-zA-Z0-9]*$";
  currentDate:any; 
  remarkList: any;
  feedbackId: any;
  customerList: any;
  employeeList: any;
  
  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
    return this.UpdateFeedBack.controls;
  }

  companyChg(event) {
    let tenant_id =localStorage.getItem('tenant_id');
    let customer_id =localStorage.getItem('customer_id');
    this.setBranchList(tenant_id);
    this.setremarkList(tenant_id);
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
  setremarkList(tenant_id) { 
    let cond = {
      "tenant_id" : tenant_id
    }
    this.userservice.getData(cond, "app-list-collection-remarks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.remarkList = response.data;
          console.log(this.remarkList)
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
    const result = Object.assign({}, this.UpdateFeedBack.value);
    if (this.UpdateFeedBack.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.remark_date.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.next_followup_date.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.remark_id.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.followup_status.errors) {
        this.focus8.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"app-update-remarks","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Feedback", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-feed-back']);
          }, 3000);
        } else {
          this.appService.showAlert("Feedback", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateFeedBack = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      remark_date:[null],
      next_followup_date:[null],
      remark_id:['', Validators.required],
      followup_status:['', Validators.required],
      customer_id:['', Validators.required],
      employee_id:['', Validators.required],
      id: [''],
      updated_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    let customer_id = localStorage.getItem('customer_id');
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

      this.userservice.getData("", "list-customers", "POST").subscribe((response: any) => {
        try { 
            if (response.status == 'Success') {
                this.customerList = response.data;
            } else {
                this.customerList = [];
            }
        }  catch (err) {}
        }, (err: any) => {});

        this.userservice.getData("", "list-employees", "POST").subscribe((response: any) => {
          try { 
              if (response.status == 'Success') {
                  this.employeeList = response.data;
              } else {
                  this.employeeList = [];
              }
          }  catch (err) {}
          }, (err: any) => {});

    }
  
    this.route.params.subscribe(params => {
      this.feedbackId = params.id;
      this.UpdateFeedBack.get('id').setValue(this.feedbackId);
      this.UpdateFeedBack.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.feedbackId,
      }
      this.userservice.getData(cond, "app-get-remarks","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateFeedBack.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateFeedBack.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateFeedBack.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateFeedBack.get('employee_id').setValue(response.data[0].employee_id);
         
            if(response.data[0].remark_date!='') {
              let RemarkDate = (response.data[0].remark_date).split('/');
              let setRemarkDate = {
              'year': parseInt(RemarkDate[2]),
              'month': parseInt(RemarkDate[1]),
              'day': parseInt(RemarkDate[0])
              }
              this.UpdateFeedBack.get('remark_date').setValue(setRemarkDate);
            }
            if(response.data[0].next_followup_date!='') {
              let NextDate = (response.data[0].next_followup_date).split('/');
              let setNextDate = {
              'year': parseInt(NextDate[2]),
              'month': parseInt(NextDate[1]),
              'day': parseInt(NextDate[0])
              }
              this.UpdateFeedBack.get('next_followup_date').setValue(setNextDate);
            }
          


           this.setBranchList(response.data[0].tenant_id);
            this.UpdateFeedBack.get('remark_id').setValue(response.data[0].remark_id);
            this.UpdateFeedBack.get('followup_status').setValue(response.data[0].followup_status);
            this.setremarkList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Feedback", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

