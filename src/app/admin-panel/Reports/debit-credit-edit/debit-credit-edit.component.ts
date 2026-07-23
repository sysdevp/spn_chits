import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-debit-credit-edit',
  
  templateUrl: './debit-credit-edit.component.html',
  styles: []
})
export class DebitCreditEditComponent implements OnInit {
  form_message = true;
  DeleteBranch: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchId: any;
  voucher_no: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteBranch.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteBranch.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Expenses detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-expenses","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/add-expenses-income-report']);
          } catch (err) {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteBranch = this.formBuilder.group({
      deletion_remark:[''],
      voucher_no:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.voucher_no = params.voucher_no;
    });
    this.DeleteBranch.get('voucher_no').setValue(this.voucher_no);
    this.DeleteBranch.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
