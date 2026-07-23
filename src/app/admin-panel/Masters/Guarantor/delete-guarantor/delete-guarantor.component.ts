import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-guarantor',
  templateUrl: './delete-guarantor.component.html',
  styles: []
})

export class DeleteGuarantorComponent implements OnInit {
  form_message = true;
  DeleteGuarantor: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  guarantor_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  ngOnInit() {
    this.DeleteGuarantor = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.guarantor_id = params.id;
    });
    this.DeleteGuarantor.get('id').setValue(this.guarantor_id);
    this.DeleteGuarantor.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

  get f() {
    return this.DeleteGuarantor.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteGuarantor.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the Guarantor detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-guarantor","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-guarantor']);
          } catch (err) {
            this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }
}

