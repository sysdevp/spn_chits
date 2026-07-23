import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-charges-type',
  templateUrl: './delete-charges-type.component.html',
  styles: []
})

export class DeleteChargesTypeComponent implements OnInit {
  form_message = true;
  DeleteChargesType: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  chargesTypeId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteChargesType.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteChargesType.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the charges type detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-charge-type","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-charges-type']);
          } catch (err) {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteChargesType = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.chargesTypeId = params.id;
    });
    this.DeleteChargesType.get('id').setValue(this.chargesTypeId);
    this.DeleteChargesType.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
