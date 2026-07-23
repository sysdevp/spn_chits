import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-petty-cash-expense-type',
  templateUrl: './delete-petty-cash-expense-type.component.html',
  styles: []
})

export class DeletePettyCashExpenseTypeComponent implements OnInit {
  form_message = true;
  DeletePettyCashExpenseType: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  pettyCashExpenseTypeId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeletePettyCashExpenseType.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeletePettyCashExpenseType.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the petty cash expense type detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-petty-cash-expense-type","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-petty-cash-head']);
          } catch (err) {
            this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Petty Cash Expense Type", "Sorry!! Something went wrong!", "error");
          }
        });
      }
      else {
        this.disableBtn = false;
      }
    });
  }

  ngOnInit() {
    this.DeletePettyCashExpenseType = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.pettyCashExpenseTypeId = params.id;
    });
    this.DeletePettyCashExpenseType.get('id').setValue(this.pettyCashExpenseTypeId);
    this.DeletePettyCashExpenseType.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
