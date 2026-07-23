import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-commission-payment',
  templateUrl: './delete-commission-payment.component.html',
  styles: []
})
export class DeleteCommissionPaymentComponent implements OnInit {
  form_message = true;
  DeleteCommissionPayment: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  commissionNo: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCommissionPayment.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCommissionPayment.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the agent commission payment detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-agent-commission-payment","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-commission-payment']);
          } catch (err) {
            this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Agent Commission Payment", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCommissionPayment = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
      commission_no :[],
    });
    this.route.params.subscribe(params => {
      this.commissionNo = params.commissionNo;
    });
    this.DeleteCommissionPayment.get('commission_no').setValue(this.commissionNo);
    this.DeleteCommissionPayment.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

}
