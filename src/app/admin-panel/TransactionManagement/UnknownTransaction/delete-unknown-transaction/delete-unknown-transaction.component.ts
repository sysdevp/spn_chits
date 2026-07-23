import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-unknown-transaction',
  templateUrl: './delete-unknown-transaction.component.html',
  styles: []
})
export class DeleteUnknownTransactionComponent implements OnInit {
  form_message = true;
  DeleteUnknownTransaction: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  pettycashincome_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteUnknownTransaction.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteUnknownTransaction.value);
	 
    swal({
      title: "Are you sure?",
      text: "You are deleting the unknown transaction detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-unknown-transaction","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-unknown-transaction']);
          } catch (err) {
            this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Unknown Transaction", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteUnknownTransaction = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.pettycashincome_id = params.id;
    });
    this.DeleteUnknownTransaction.get('id').setValue(this.pettycashincome_id);
    this.DeleteUnknownTransaction.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
