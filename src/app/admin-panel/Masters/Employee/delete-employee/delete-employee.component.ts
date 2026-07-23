import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-employee',
  templateUrl: './delete-employee.component.html',
  styles: []
})     

export class DeleteEmployeeComponent implements OnInit {
  DeleteEmployee: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  employeeId: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteEmployee.controls;
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteEmployee.value);
    swal({
      title: "Are you sure?",
      text: "You are deleting the designation detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-employee","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-employee']);
          } catch (err) {
            this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteEmployee = this.formBuilder.group({
      deletion_remark:[''],
      id:[''],
      deleted_by: [''],
    });

    this.route.params.subscribe(params => {
      this.employeeId = params.id;
    });
    this.DeleteEmployee.get('id').setValue(this.employeeId);
    this.DeleteEmployee.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
