import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-source-type',
  templateUrl: './delete-source-type.component.html',
  styles: []
})
export class DeleteSourceTypeComponent implements OnInit {
  DeleteSourceType: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  designationId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteSourceType.controls;
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteSourceType.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the source type detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-source-type","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-source-type']);
          } catch (err) {
            this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteSourceType = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.designationId = params.id;
    });
    this.DeleteSourceType.get('id').setValue(this.designationId);
    this.DeleteSourceType.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
