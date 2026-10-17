import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-commitment-chit',
  templateUrl: './delete-commitment-chit.component.html',
  styles: []
})

export class DeleteCommitmentChitComponent implements OnInit {
  DeleteCommitmentChit: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  CommitmentChitId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCommitmentChit.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCommitmentChit.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Chit detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-customer-commitment-enrollment","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-commitment-chit']);
          } catch (err) {
            this.appService.showAlert("Chit", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Chit", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Chit", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCommitmentChit = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });
    this.route.params.subscribe(params => {
      this.CommitmentChitId = params.id;
    });
    this.DeleteCommitmentChit.get('id').setValue(this.CommitmentChitId);
    this.DeleteCommitmentChit.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
