import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-employee-salary',
  templateUrl: './employee-salary.component.html',
  styles: []
})
export class EmployeeSalaryComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('month_year') focus3: ElementRef;
  @ViewChild('working_days') focus4: ElementRef;
  
  
  
  AddEmployeeSalary: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  employeeList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  currentDate:any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) {
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
 this.currentDate = {
    'year': today_date.getFullYear(),
    'month': today_date.getMonth()+1,
    'day': today_date.getDate()
 };
 console.log(this.currentDate)
  }

  get f() {
    return this.AddEmployeeSalary.controls;
  }

  addEmployee() {
    const control = <FormArray>this.AddEmployeeSalary.controls['Employees'];
    control.push(this.initEmployee());
  }

  get Employees() {
    return <FormArray>this.AddEmployeeSalary.get('Employees');
  }

  initEmployee() {
    return this.formBuilder.group({
      'employee_id': [''],
      'emp_name': [''],
      'salary': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
    });
  }


  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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

  branchChg(event) {
    let branch_id = event.target.value;
    console.log(branch_id);
    this.checkForemployeeList(branch_id);
  }

  salaryChg(index) {
    
    const EmployeesArray = <FormArray>this.AddEmployeeSalary.get('Employees');
    console.log(EmployeesArray.controls[index].get('salary').value);
    if ((EmployeesArray.controls[index].get('salary').value) == '' || (EmployeesArray.controls[index].get('salary').value) != this.decimalPattern) {
      EmployeesArray.controls[index].get('salary').setValidators([Validators.required,Validators.pattern(this.decimalPattern)]);
    }
    if (EmployeesArray.controls[index].get('salary').value == 0 ) {
      EmployeesArray.controls[index].get('salary').setValue('');
    }
  }

  checkForemployeeList(branch_id) {
   
    let cond = {
      "conditions[branch_id]": branch_id,
      "conditions[employee_type]": 1,
    }
    console.log(cond);
    this.userservice.getData(cond ,"get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          const EmployeeArray = < FormArray > this.AddEmployeeSalary.get('Employees');
          EmployeeArray.controls = [];
          for (let i = 0; i < response.data.length; i++) {
            this.addEmployee();
            EmployeeArray.controls[i].get('employee_id').setValue(response.data[i].id);
            EmployeeArray.controls[i].get('emp_name').setValue(response.data[i].first_name + ' ' + response.data[i].last_name);
            EmployeeArray.controls[i].get('salary').setValue(response.data[i].salary);
            //let attData = attendanceList.filter((listing: any) => listing.employee_id == (response.data[i].id));
            //if((attData.length)>0) { 
              //this.setAttendance(i,attData[0]);
            //}
          } 
        } else { } 
      } catch (err) {
        //this.disableBtn = true;
      }
    }, (err: any) => {
      //this.disableBtn = true;
    });
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddEmployeeSalary.value);
    if (this.AddEmployeeSalary.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.month_year.errors) {
        this.focus3.nativeElement.focus();
      }
      else if (this.f.working_days.errors) {
        this.focus4.nativeElement.focus();
      }
      
      return;
    }
    
      this.userservice.getData(result, "store-employee-monthly-salary", "JSON").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.appService.showAlert("Employee Salary", response.msg, "success");
            setTimeout(() => {
              this.router.navigate(['/admin-panel/view-employee-salary']);
            }, 300);
          } else {
            this.appService.showAlert("Employee Salary", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
          this.disableBtn = false;
        }
      }, (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
        }
      });
    
  }

  ngOnInit() {
    this.AddEmployeeSalary = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      month_year: [this.currentDate,Validators.required],
      working_days: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      created_by: [''],
      'Employees': this.formBuilder.array([
        this.formBuilder.group({
          'employee_id': [''],
          'emp_name': [''],
          'salary': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
        })
      ])
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.AddEmployeeSalary.get('tenant_id').setValue(tenant_id);
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
    this.AddEmployeeSalary.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
