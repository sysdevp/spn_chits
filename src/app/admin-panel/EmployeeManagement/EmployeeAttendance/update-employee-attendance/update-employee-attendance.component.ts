import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-employee-attendance',
  templateUrl: './update-employee-attendance.component.html',
  styles: []
})

export class UpdateEmployeeAttendanceComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('att_date') focus3: ElementRef;

  UpdateEmployeeAttendance: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  employeeList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  branchId: any;
  attYear: any;
  attMonth: any;
  attDate: any;
  viewsession: boolean = true;
  EmployeeAttendanceId: any;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateEmployeeAttendance.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  addEmployee() {
    const control = <FormArray>this.UpdateEmployeeAttendance.controls['Employees'];
    control.push(this.initEmployee());
  }

  get Employees() {
    return <FormArray>this.UpdateEmployeeAttendance.get('Employees');
  }

  initEmployee() {
    return this.formBuilder.group({
      'id': [''],
      'employee_id': [''],
      'emp_name': [''],
      'attendance_type': ['1', [Validators.required]],
      'leave_type': [''],
      'afternoon_session': ['1'],
      'forenoon_session': ['1'],
      'viewSessionAlert': [''],
    });
  }

  leaveChg(index) {
    const EmployeesArray = <FormArray>this.UpdateEmployeeAttendance.get('Employees');
    EmployeesArray.controls[index].get('viewSessionAlert').setValue(0);
    EmployeesArray.controls[index].get('afternoon_session').setValue(1);
    EmployeesArray.controls[index].get('forenoon_session').setValue(1);
    if ((EmployeesArray.controls[index].get('attendance_type').value) == 0) {
      EmployeesArray.controls[index].get('leave_type').setValidators([Validators.required]);
    }
    else {
      EmployeesArray.controls[index].get('leave_type').setValidators(null);
    }
    EmployeesArray.controls[index].get('leave_type').updateValueAndValidity();
  }

  sessionChg(index) {
    const EmployeesArray = <FormArray>this.UpdateEmployeeAttendance.get('Employees');
    let foreSession = EmployeesArray.controls[index].get('forenoon_session').value;
    let afterSession = EmployeesArray.controls[index].get('afternoon_session').value;
    if ((foreSession != 1) && (afterSession != 1)) {
      EmployeesArray.controls[index].get('viewSessionAlert').setValue(1);
    }
    else {
      EmployeesArray.controls[index].get('viewSessionAlert').setValue(0);
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateEmployeeAttendance.value);
    if (this.UpdateEmployeeAttendance.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;

      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.att_date.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }

    const EmployeesArray = <FormArray>this.UpdateEmployeeAttendance.get('Employees');
    let alertError = 0;
    for (let i = 0; i < EmployeesArray.length; i++) {
      if ((EmployeesArray.controls[i].get('viewSessionAlert').value) == 1) {
        alertError = 1;
      }
    }
    if (alertError == 1) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.appService.showAlert("Employee Attendance", "Session selection missing!!", "error");
    }
    else {
      this.userservice.getData(result, "update-employee-attendance", "JSON").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.appService.showAlert("Employee Attendance", response.msg, "success");
            setTimeout(() => {
              this.router.navigate(['/admin-panel/view-employee-attendance']);
            }, 300);
          } else {
            this.appService.showAlert("Employee Attendance", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          this.appService.showAlert("Employee Attendance", 'Sorry!! Something went wrong!', "error");
          this.disableBtn = false;
        }
      }, (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
      });
    }
  }

  ngOnInit() {
    this.UpdateEmployeeAttendance = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      att_date: [null],
      updated_by: [''],
      'Employees': this.formBuilder.array([
        this.formBuilder.group({
          'id': [''],
          'employee_id': [''],
          'emp_name': [''],
          'attendance_type': ['1', [Validators.required]],
          'leave_type': [''],
          'afternoon_session': ['1'],
          'forenoon_session': ['1'],
          'viewSessionAlert': [''],
        })
      ])
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.isSuperAdmin = false;
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        } catch (err) { }
      }, (err: any) => { });
    }

    this.route.params.subscribe(params => {
      this.branchId = params.branchId;
      this.attYear = params.attYear;
      this.attMonth = params.attMonth;
      this.attDate = params.attDate;
      this.UpdateEmployeeAttendance.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        'conditions[branch_id]': this.branchId,
        'conditions[att_year]': this.attYear,
        'conditions[att_month]': this.attMonth,
        'conditions[att_date]': this.attDate,
      }
      //this.userservice.getData('', "get-employee-attendances/"+this.branchId+"/"+this.attYear+"/"+this.attMonth+"/"+this.attDate,"GET").subscribe((response: any) => {
      this.userservice.getData(cond, "get-employee-attendances", "POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.UpdateEmployeeAttendance.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateEmployeeAttendance.get('branch_id').setValue(response.data[0].branch_id);
            let setAttdate = {
              'year': parseInt(this.attYear),
              'month': parseInt(this.attMonth),
              'day': parseInt(this.attDate)
            }
            this.UpdateEmployeeAttendance.get('att_date').setValue(setAttdate);
            const EmployeesArray = <FormArray>this.UpdateEmployeeAttendance.get('Employees');
            for (let i = 0; i < response.data.length; i++) {
              if (i > 0) {
                this.addEmployee();
              }
              EmployeesArray.controls[i].get('id').setValue(response.data[i]['id']);
              EmployeesArray.controls[i].get('employee_id').setValue(response.data[i]['employee_id']);
              let empName = response.data[i].employee_info['first_name'] + ' ' + response.data[i].employee_info['last_name'];
              EmployeesArray.controls[i].get('emp_name').setValue(empName);
              EmployeesArray.controls[i].get('attendance_type').setValue(response.data[i]['attendance_type']);
              EmployeesArray.controls[i].get('leave_type').setValue(response.data[i]['leave_type']);
              EmployeesArray.controls[i].get('afternoon_session').setValue(response.data[i]['afternoon_session']);
              EmployeesArray.controls[i].get('forenoon_session').setValue(response.data[i]['forenoon_session']);
            }
            if (response.data.length > 0) {
              this.disableBtn = false;
            }
            else {
              this.addEmployee();
              this.disableBtn = true;
            }
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
