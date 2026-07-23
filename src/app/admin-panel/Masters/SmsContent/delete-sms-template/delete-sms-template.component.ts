import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-sms-template',
  templateUrl: './delete-sms-template.component.html',
  styles: []
})
export class DeleteSmsTemplateComponent implements OnInit {
  DeleteSms: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  roleId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteSms.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteSms.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the SMS detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-sms-template","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-sms-content']);
          } catch (err) {
            this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("SMS Template", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteSms = this.formBuilder.group({
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.roleId = params.id;
    });
    this.DeleteSms.get('id').setValue(this.roleId);
    this.DeleteSms.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
