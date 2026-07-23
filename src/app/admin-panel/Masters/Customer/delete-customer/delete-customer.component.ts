import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-customer',
  templateUrl: './delete-customer.component.html',
  styles: []
})

export class DeleteCustomerComponent implements OnInit {
  form_message = true;
  DeleteCustomer: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  headOffice_id: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteCustomer.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteCustomer.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the customer detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-customer","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-customer']);
          } catch (err) {
            this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteCustomer = this.formBuilder.group({
      deletion_remark:[''],
      id:[],
      deleted_by: [],
    });
    this.route.params.subscribe(params => {
      this.headOffice_id = params.id;
    });
    this.DeleteCustomer.get('id').setValue(this.headOffice_id);
    this.DeleteCustomer.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

