import { Component, OnInit} from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-advanced-receipt',
  templateUrl: './delete-advanced-receipt.component.html',
  styles: []
})
export class DeleteAdvancedReceiptComponent implements OnInit {

  form_message = true;
  DeleteAdvanceReceipt: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteAdvanceReceipt.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteAdvanceReceipt.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the advance receipt detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-advance-receipt","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/advanced-receipt-report']);
          } catch (err) {
            this.appService.showAlert("Advance Receipt", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Advance Receipt", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Advance Receipt", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteAdvanceReceipt = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
      receipt_no:[''],
    });

    this.route.params.subscribe(params => {
      this.branchId = params.receipt_no;
    });
    console.log(this.branchId)
    this.DeleteAdvanceReceipt.get('receipt_no').setValue(this.branchId);
    this.DeleteAdvanceReceipt.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

