import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-tenant',
  templateUrl: './delete-tenant.component.html',
  styles: []
})

export class DeleteTenantComponent implements OnInit {
  form_message = true;
  DeleteTenant: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  tenant_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteTenant.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteTenant.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the tenant detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-tenant","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-tenant']);
          } catch (err) {
            this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteTenant = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.tenant_id = params.id;
    });
    this.DeleteTenant.get('id').setValue(this.tenant_id);
    this.DeleteTenant.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
