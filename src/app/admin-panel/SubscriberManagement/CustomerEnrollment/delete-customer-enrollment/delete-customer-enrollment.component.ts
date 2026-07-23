import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-customer-enrollment',
  templateUrl: './delete-customer-enrollment.component.html',
  styles: []
})

export class DeleteCustomerEnrollmentComponent implements OnInit {
  DeleteCustomerEnrollment: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  customerEnrollmentId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCustomerEnrollment.controls;
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCustomerEnrollment.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the enrollment detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-customer-enrollment","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-customer-enrollment']);
          } catch (err) {
            this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCustomerEnrollment = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.customerEnrollmentId = params.id;
    });
    this.DeleteCustomerEnrollment.get('id').setValue(this.customerEnrollmentId);
    this.DeleteCustomerEnrollment.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
