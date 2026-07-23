import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-petty-cash-expense',
  templateUrl: './delete-petty-cash-expense.component.html',
  styles: []
})

export class DeletePettyCashExpenseComponent implements OnInit {
  form_message = true;
  DeletePettyCashExpense: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  pettycashexpense_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeletePettyCashExpense.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeletePettyCashExpense.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the petty cash expense detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-petty-cash-expense","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-petty-cash-expense']);
          } catch (err) {
            this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Petty Cash Expense", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeletePettyCashExpense = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.pettycashexpense_id = params.id;
    });
    this.DeletePettyCashExpense.get('id').setValue(this.pettycashexpense_id);
    this.DeletePettyCashExpense.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
