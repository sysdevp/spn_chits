import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-enrol-refund',
  templateUrl: './delete-enrol-refund.component.html',
  styles: []
})

export class DeleteEnrolRefundComponent implements OnInit {
  DeleteEnrolRefundAmount: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  EnrolRefundAmountId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteEnrolRefundAmount.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteEnrolRefundAmount.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Enrol Refund detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-payment-refund","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/payment-refund-report']);
          } catch (err) {
            this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Enrol Refund", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteEnrolRefundAmount = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });
    this.route.params.subscribe(params => {
      this.EnrolRefundAmountId = params.id;
    });
    this.DeleteEnrolRefundAmount.get('id').setValue(this.EnrolRefundAmountId);
    this.DeleteEnrolRefundAmount.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
