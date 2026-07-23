import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-accounting-group',
  templateUrl: './delete-accounting-group.component.html',
  styles: []
})
export class DeleteAccountingGroupComponent implements OnInit {
  form_message = true;
  DeleteAccountingGroup: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  accountGroupId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteAccountingGroup.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteAccountingGroup.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the account group detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-account-group","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-accounting-group']);
          } catch (err) {
            this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
          }
        });
      }
      else {
        this.disableBtn = false;
      }
    });
  }

  ngOnInit() {
    this.DeleteAccountingGroup = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.accountGroupId = params.id;
    });
    this.DeleteAccountingGroup.get('id').setValue(this.accountGroupId);
    this.DeleteAccountingGroup.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
