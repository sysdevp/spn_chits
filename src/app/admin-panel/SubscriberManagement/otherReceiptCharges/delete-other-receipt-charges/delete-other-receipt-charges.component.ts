import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-other-receipt-charges',
  templateUrl: './delete-other-receipt-charges.component.html',
  styles: []
})
export class DeleteOtherReceiptChargesComponent implements OnInit {
  DeleteOtherReceiptCharges: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  OtherReceiptChargesId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteOtherReceiptCharges.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteOtherReceiptCharges.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Other Receipt Charges detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-other-charge-receipt","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-other-receipt-charges']);
          } catch (err) {
            this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Other Receipt Charges", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteOtherReceiptCharges = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });
    this.route.params.subscribe(params => {
      this.OtherReceiptChargesId = params.id;
    });
    this.DeleteOtherReceiptCharges.get('id').setValue(this.OtherReceiptChargesId);
    this.DeleteOtherReceiptCharges.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
