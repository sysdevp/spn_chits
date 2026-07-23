import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-penalty-slab',
  templateUrl: './delete-penalty-slab.component.html',
  styles: []
})

export class DeletePenaltySlabComponent implements OnInit {
  DeleteSlabPenalty: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  slab_penalty_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteSlabPenalty.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteSlabPenalty.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the slab penalty detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-slab-penalty","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-penalty-slab']);
          } catch (err) {
            this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteSlabPenalty = this.formBuilder.group({
      deletion_remark:['', Validators.required],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.slab_penalty_id = params.id;
    });
    this.DeleteSlabPenalty.get('id').setValue(this.slab_penalty_id);
    this.DeleteSlabPenalty.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
