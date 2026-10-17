import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-commitment-payment',
  templateUrl: './delete-commitment-payment.component.html',
  styles: []
})

export class DeleteCommitmentPaymentComponent implements OnInit {
  DeleteCommitmentPayment: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  CommitmentPaymentId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCommitmentPayment.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCommitmentPayment.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Prized Payment detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-commitment-payment","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-commitment-payment']);
          } catch (err) {
            this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Prized Payment", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCommitmentPayment = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });
    this.route.params.subscribe(params => {
      this.CommitmentPaymentId = params.id;
    });
    this.DeleteCommitmentPayment.get('id').setValue(this.CommitmentPaymentId);
    this.DeleteCommitmentPayment.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
