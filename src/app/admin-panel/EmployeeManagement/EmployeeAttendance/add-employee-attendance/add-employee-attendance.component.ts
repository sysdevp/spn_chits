import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-employee-attendance',
  templateUrl: './add-employee-attendance.component.html',
  styles: []
})

export class AddEmployeeAttendanceComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('att_date') focus3: ElementRef;

  
  AddEmployeeAttendance: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = true;
  SubmitbuttonName: string;
  branchList: any;
  employeeList: any;
  isSuperAdmin: boolean;
  companyList: any[];

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) {
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddEmployeeAttendance.controls;
  }

  addEmployee() {
    const control = <FormArray>this.AddEmployeeAttendance.controls['Employees'];
    control.push(this.initEmployee());
  }

  get Employees() {
    return <FormArray>this.AddEmployeeAttendance.get('Employees');
  }

  initEmployee() {
    return this.formBuilder.group({
      'employee_id': [''],
      'emp_name': [''],
      'attendance_type': ['1', [Validators.required]],
      'leave_type': [''],
      'afternoon_session': ['1'],
      'forenoon_session': ['1'],
      'viewSessionAlert': [''],
    });
  }

  branchChg(event) {
    this.checkForAttendanceList();
    /*let cond = {
      "conditions[branch_id]" : event.target.value
    }
     this.userservice.getData(cond ,"get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          const EmployeeArray = <FormArray>this.AddEmployeeAttendance.get('Employees');
          EmployeeArray.controls = [];
          for (let i = 0; i < response.data.length; i++) {
            this.addEmployee();
            EmployeeArray.controls[i].get('employee_id').setValue(response.data[i].id);
            EmployeeArray.controls[i].get('emp_name').setValue(response.data[i].first_name + ' ' + response.data[i].last_name);
          }
          if (response.data.length > 0) {
            this.disableBtn = false;
            this.checkForAttendanceList();
          }
          else {
            this.addEmployee();
            this.disableBtn = true;
          }
        } else {
          this.employeeList = [];
          this.disableBtn = true;
        }
      } catch (err) {
        this.disableBtn = true;
      }
    }, (err: any) => {
      this.disableBtn = true;
    }); */
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.checkForAttendanceList();
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "conditions[status]": 1,
      "log_id" : localStorage.getItem('logged_employee_id')
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

  leaveChg(index) {
    const EmployeesArray = <FormArray>this.AddEmployeeAttendance.get('Employees');
    EmployeesArray.controls[index].get('viewSessionAlert').setValue(0);
    EmployeesArray.controls[index].get('afternoon_session').setValue(0);
    EmployeesArray.controls[index].get('forenoon_session').setValue(0);
    if ((EmployeesArray.controls[index].get('attendance_type').value) == 0) {
      EmployeesArray.controls[index].get('leave_type').setValidators([Validators.required]);
    }
    else {
      EmployeesArray.controls[index].get('leave_type').setValidators(null);
    }
    EmployeesArray.controls[index].get('leave_type').updateValueAndValidity();
    if ((EmployeesArray.controls[index].get('attendance_type').value) == 0) {
      let foreSession = EmployeesArray.controls[index].get('forenoon_session').value;
      let afterSession = EmployeesArray.controls[index].get('afternoon_session').value;
      if ((foreSession != 1) && (afterSession != 1)) {
        EmployeesArray.controls[index].get('viewSessionAlert').setValue(1);
      }
      else {
        EmployeesArray.controls[index].get('viewSessionAlert').setValue(0);
      }
    }
  }

  sessionChg(index) {
    const EmployeesArray = <FormArray>this.AddEmployeeAttendance.get('Employees');
    let foreSession = EmployeesArray.controls[index].get('forenoon_session').value;
    let afterSession = EmployeesArray.controls[index].get('afternoon_session').value;
    if ((foreSession != 1) && (afterSession != 1)) {
      EmployeesArray.controls[index].get('viewSessionAlert').setValue(1);
    }
    else {
      EmployeesArray.controls[index].get('viewSessionAlert').setValue(0);
    }
  }

  attendanceDateChg() {
    this.checkForAttendanceList();
  }

  checkForAttendanceList() {
    let tenant_id = this.AddEmployeeAttendance.get('tenant_id').value;
    let branchId = this.AddEmployeeAttendance.get('branch_id').value;
    let att_date = this.AddEmployeeAttendance.get('att_date').value;
    if((tenant_id!='') && (branchId!='') && (att_date!='')) {
      let cond = {
        'conditions[branch_id]' :  branchId,
        'conditions[att_year]' :  att_date.year,
        'conditions[att_month]' :  att_date.month,
        'conditions[att_date]' :  att_date.day,
        
      }
      //this.userservice.getData('', "get-employee-attendances/"+this.branchId+"/"+this.attYear+"/"+this.attMonth+"/"+this.attDate,"GET").subscribe((response: any) => {
      let attendanceList = [];
      this.userservice.getData(cond, "get-employee-attendances","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          { 
            attendanceList = response.data;
            this.disableBtn = false;
            let cond1 = {
              "conditions[branch_id]" : branchId,
              "conditions[employee_type]": 1,
            }
            this.userservice.getData(cond1 ,"get-employees", "POST").subscribe((response: any) => {
              try {
                if (response.status == 'Success') {
                  const EmployeeArray = < FormArray > this.AddEmployeeAttendance.get('Employees');
                  EmployeeArray.controls = [];
                  for (let i = 0; i < response.data.length; i++) {
                    this.addEmployee();
                    EmployeeArray.controls[i].get('employee_id').setValue(response.data[i].id);
                    EmployeeArray.controls[i].get('emp_name').setValue(response.data[i].first_name + ' ' + response.data[i].last_name);
                    let attData = attendanceList.filter((listing: any) => listing.employee_id == (response.data[i].id));
                    if((attData.length)>0) { 
                      this.setAttendance(i,attData[0]);
                    }
                  } 
                } else { } 
              } catch (err) {
                //this.disableBtn = true;
              }
            }, (err: any) => {
              //this.disableBtn = true;
            });
            /* const EmployeesArray = < FormArray > this.AddEmployeeAttendance.get('Employees');
            for (let i=0; i<response.data.length; i++) { 
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
            if(response.data.length>0) {
              this.disableBtn = false;
            }
            else {
              this.addEmployee();
              this.disableBtn = true;
            }
            this.setBranchList(response.data[0].tenant_id); */
          }
          else {
            this.disableBtn = true;
            this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.disableBtn = true;
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.disableBtn = true;
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.disableBtn = true;
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
        }
      });
    }
  }

  setAttendance(i,data) { console.log("i",i,'-',data.employee_id)
    const EmployeesArray = < FormArray > this.AddEmployeeAttendance.get('Employees');
    let emp_id = EmployeesArray.controls[i].get('employee_id').value;
    if(emp_id==(data.employee_id)) {
      EmployeesArray.controls[i].get('attendance_type').setValue(data.attendance_type);
      EmployeesArray.controls[i].get('leave_type').setValue(data.leave_type);
      EmployeesArray.controls[i].get('afternoon_session').setValue(data.afternoon_session);
      EmployeesArray.controls[i].get('forenoon_session').setValue(data.forenoon_session);
    }
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddEmployeeAttendance.value);
    if (this.AddEmployeeAttendance.invalid) {
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
    const EmployeesArray = <FormArray>this.AddEmployeeAttendance.get('Employees');
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
      this.userservice.getData(result, "store-employee-attendance", "JSON").subscribe((response: any) => {
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
          this.appService.showAlert("Employee Attendance", "Sorry!! Something went wrong!", "error");
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
    this.AddEmployeeAttendance = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      att_date: ['', Validators.required],
      created_by: [''],
      'Employees': this.formBuilder.array([
        this.formBuilder.group({
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
      this.AddEmployeeAttendance.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
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
    this.AddEmployeeAttendance.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
