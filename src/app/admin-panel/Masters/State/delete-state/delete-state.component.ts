import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-state',
  templateUrl: './delete-state.component.html',
  styles: []
})

export class DeleteStateComponent implements OnInit {
  form_message = true;
  DeleteState: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  stateId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteState.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteState.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the State detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-state","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-state']);
          } catch (err) {
            this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("State", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteState = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.stateId = params.id;
    });
    this.DeleteState.get('id').setValue(this.stateId);
    this.DeleteState.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
