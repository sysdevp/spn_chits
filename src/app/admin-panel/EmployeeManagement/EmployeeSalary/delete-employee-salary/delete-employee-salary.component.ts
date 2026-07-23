import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import swal from 'sweetalert2';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-delete-employee-salary',
  templateUrl: './delete-employee-salary.component.html',
  styles: []
})
export class DeleteEmployeeSalaryComponent implements OnInit {

  DeleteEmployeeSalary: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  employeeId: any;
  branchId: any;
  attYear: any;
  attMonth: any;
  attDate: any;

  constructor(private route: ActivatedRoute,private router: Router,private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.DeleteEmployeeSalary.controls;
  }
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const resultData = Object.assign({}, this.DeleteEmployeeSalary.value);
    console.log(resultData);
    swal({
      title: "Are you sure?",
      text: "You are deleting the employee salary detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        this.userservice.getData(resultData, "delete-employee-monthly-salary","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.router.navigate(['/admin-panel/view-employee-salary']);
          } catch (err) {
            this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  ngOnInit() {
    this.DeleteEmployeeSalary = this.formBuilder.group({
      deletion_remark:[''],
      branch_id:[''],
      deleted_by: [''],
      saldate:[''],
      salmonth:[''],
      salyear: [''],
    });

    this.route.params.subscribe(params => {
      this.branchId = params.branchId;
      this.attYear = params.attYear;
      this.attMonth = params.attMonth;
      this.attDate = params.attDate;
    });
    this.DeleteEmployeeSalary.get('branch_id').setValue(this.branchId);
    this.DeleteEmployeeSalary.get('salyear').setValue(this.attYear);
    this.DeleteEmployeeSalary.get('salmonth').setValue(this.attMonth);
    this.DeleteEmployeeSalary.get('saldate').setValue(this.attDate);
    this.DeleteEmployeeSalary.get('deleted_by').setValue(localStorage.getItem('logged_user_id'));
  }
}

