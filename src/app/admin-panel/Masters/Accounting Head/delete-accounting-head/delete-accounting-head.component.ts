import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-accounting-head',
  templateUrl: './delete-accounting-head.component.html',
  styles: []
})
export class DeleteAccountingHeadComponent implements OnInit {
  form_message = true;
  DeleteAccountingHead: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  accountHeadId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteAccountingHead.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteAccountingHead.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the account head detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-head-group","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-accounting-head']);
          } catch (err) {
            this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
          }
        });
      }
      else {
        this.disableBtn = false;
      }
    });
  }

  ngOnInit() {
    this.DeleteAccountingHead = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.accountHeadId = params.id;
    });
    this.DeleteAccountingHead.get('id').setValue(this.accountHeadId);
    this.DeleteAccountingHead.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
