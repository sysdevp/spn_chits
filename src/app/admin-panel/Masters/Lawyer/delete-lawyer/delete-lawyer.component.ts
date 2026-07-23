import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-lawyer',
  templateUrl: './delete-lawyer.component.html',
  styles: []
})
export class DeleteLawyerComponent implements OnInit {
  DeleteLawyer: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  designationId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteLawyer.controls;
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteLawyer.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the lawyer name detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-lawyer-name","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-lawyer']);
          } catch (err) {
            this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Lawyer", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteLawyer = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.designationId = params.id;
    });
    this.DeleteLawyer.get('id').setValue(this.designationId);
    this.DeleteLawyer.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
