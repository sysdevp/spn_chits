import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-feedback',
  templateUrl: './delete-feedback.component.html',
  styles: []
})
export class DeleteFeedbackComponent implements OnInit {
  form_message = true;
  DeleteFeedback: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  BankId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteFeedback.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteFeedback.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Feedback.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "app-delete-remarks","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-feed-back']);
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
      }
    });
  }

  ngOnInit() {
    this.DeleteFeedback = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.BankId = params.id;
    });
    this.DeleteFeedback.get('id').setValue(this.BankId);
    this.DeleteFeedback.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

