import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-due-slab',
  templateUrl: './delete-due-slab.component.html',
  styles: []
})

export class DeleteDueSlabComponent implements OnInit {
  form_message = true;
  DeleteSlabInterest: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  slabInterestId: any;

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
      text: "You are deleting the due slab detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-slab-due","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-due-slab']);
          } catch (err) {
            this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
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
      this.slabInterestId = params.id;
    });
    this.DeleteSlabInterest.get('id').setValue(this.slabInterestId);
    this.DeleteSlabInterest.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

}
