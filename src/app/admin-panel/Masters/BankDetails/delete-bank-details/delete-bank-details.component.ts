import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-bank-details',
  templateUrl: './delete-bank-details.component.html',
  styles: []
})

export class DeleteBankDetailsComponent implements OnInit {
  form_message = true;
  DeleteBank: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  BankId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteBank.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteBank.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Bank detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-bank","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-bank-details']);
          } catch (err) {
            this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Bank", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteBank = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.BankId = params.id;
    });
    this.DeleteBank.get('id').setValue(this.BankId);
    this.DeleteBank.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

