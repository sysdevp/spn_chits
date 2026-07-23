import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-feedback-type',
  templateUrl: './update-feedback-type.component.html',
  styles: []
})
export class UpdateFeedbackTypeComponent implements OnInit {
  
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('remark_name') focus2:ElementRef;
  
  UpdateFeedbackType: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  feedbacktypeId: any;
  branchList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  numOnlyPattern = "^[0-9]*$";   
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumPattern = "^[a-zA-Z0-9]*$";
  
  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateFeedbackType.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateFeedbackType.value);
    if (this.UpdateFeedbackType.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.remark_name.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"app-update-collection-remarks","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Feedback Type", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-feed-back-type']);
          }, 3000);
        } else {
          this.appService.showAlert("Feedback Type", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Service Type", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateFeedbackType = this.formBuilder.group({
      tenant_id:['', Validators.required],
      remark_name:['',[Validators.required,Validators.pattern(this.txtSpPattern)]],
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
      this.feedbacktypeId = params.id;
      this.UpdateFeedbackType.get('id').setValue(this.feedbacktypeId);
      this.UpdateFeedbackType.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.feedbacktypeId 
      }
      this.userservice.getData(cond, "app-get-collection-remarks","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateFeedbackType.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateFeedbackType.get('remark_name').setValue(response.data[0].remark_name);
          }
          else {
            this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Feedback Type", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

