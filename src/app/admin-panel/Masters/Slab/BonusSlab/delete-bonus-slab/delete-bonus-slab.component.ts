import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-bonus-slab',
  templateUrl: './delete-bonus-slab.component.html',
  styles: []
})

export class DeleteBonusSlabComponent implements OnInit {
  form_message = true;
  DeleteSlabBonus: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  slab_interest_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteSlabBonus.controls;
  }

  ngOnInit() {
    this.DeleteSlabBonus = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });

    this.route.params.subscribe(params => {
      this.slab_interest_id = params.id;
    });
    this.DeleteSlabBonus.get('id').setValue(this.slab_interest_id);
    this.DeleteSlabBonus.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteSlabBonus.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the slab bonus detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-slab-bonus","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-bonus-slab']);
          } catch (err) {
            this.appService.showAlert("Bonus Slab", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Bonus Slab", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Bonus Slab", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }
}
