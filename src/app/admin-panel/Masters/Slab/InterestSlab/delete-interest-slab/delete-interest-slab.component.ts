import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-interest-slab',
  templateUrl: './delete-interest-slab.component.html',
  styles: []
})

export class DeleteInterestSlabComponent implements OnInit {
  form_message = true;
  DeleteSlabInterest: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  slab_interest_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteSlabInterest.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteSlabInterest.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the slab interest detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-slab-interest","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-interest-slab']);
          } catch (err) {
            this.appService.showAlert("Interest Slab", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Interest Slab", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Interest Slab", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteSlabInterest = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.slab_interest_id = params.id;
    });
    this.DeleteSlabInterest.get('id').setValue(this.slab_interest_id);
    this.DeleteSlabInterest.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
