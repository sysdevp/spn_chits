import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-nominee',
  templateUrl: './delete-nominee.component.html',
  styles: []
})
export class DeleteNomineeComponent implements OnInit {
  form_message = true;
  DeleteNominee: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  nominee_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  ngOnInit() {
    this.DeleteNominee = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.nominee_id = params.id;
    });
    this.DeleteNominee.get('id').setValue(this.nominee_id);
    this.DeleteNominee.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

  get f() {
    return this.DeleteNominee.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteNominee.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Nominee detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-nominee","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-nominee']);
          } catch (err) {
            this.appService.showAlert("Nominee", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Nominee", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Nominee", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }
}

