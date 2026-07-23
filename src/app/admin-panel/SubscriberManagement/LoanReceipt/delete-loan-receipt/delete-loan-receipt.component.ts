import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-loan-receipt',
  templateUrl: './delete-loan-receipt.component.html',
  styles: []
})
export class DeleteLoanReceiptComponent implements OnInit {
  DeleteLoanReceipt: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteLoanReceipt.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteLoanReceipt.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Loan receipt.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-loan-receipt","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/loan-receipt-report']);
          } catch (err) {
            this.appService.showAlert("Loan Receipt", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Loan Receipt", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Loan Receipt", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteLoanReceipt = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      receipt_no:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.branchId = params.receipt_no;
    });
    this.DeleteLoanReceipt.get('receipt_no').setValue(this.branchId);
    this.DeleteLoanReceipt.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

