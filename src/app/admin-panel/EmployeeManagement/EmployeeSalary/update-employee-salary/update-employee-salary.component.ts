import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormArray } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-employee-salary',
  templateUrl: './update-employee-salary.component.html',
  styles: []
})
export class UpdateEmployeeSalaryComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('month_year') focus3: ElementRef;
  @ViewChild('working_days') focus4: ElementRef;

  UpdateEmployeeSalary: FormGroup;
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
  currentDate:any;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) {
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
 this.currentDate = {
    'year': today_date.getFullYear(),
    'month': today_date.getMonth()+1,
    'day': today_date.getDate()
 };
  }

  get f() {
    return this.UpdateEmployeeSalary.controls;
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

  branchChg(event) {
    let branch_id = event.target.value;
    console.log(branch_id);
    this.checkForemployeeList(branch_id);
  }

  salaryChg(index) {
    
    const EmployeesArray = <FormArray>this.UpdateEmployeeSalary.get('Employees');
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
    }
    console.log(cond);
    this.userservice.getData(cond ,"get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          const EmployeeArray = < FormArray > this.UpdateEmployeeSalary.get('Employees');
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

  addEmployee() {
    const control = <FormArray>this.UpdateEmployeeSalary.controls['Employees'];
    control.push(this.initEmployee());
  }

  get Employees() {
    return <FormArray>this.UpdateEmployeeSalary.get('Employees');
  }

  initEmployee() {
    return this.formBuilder.group({
      'id': [''],
      'employee_id': [''],
      'emp_name': [''],
      'salary': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
    });
  }



  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateEmployeeSalary.value);
    if (this.UpdateEmployeeSalary.invalid) {
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

      this.userservice.getData(result, "update-employee-monthly-salary", "JSON").subscribe((response: any) => {
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
          this.appService.showAlert("Employee Salary", 'Sorry!! Something went wrong!', "error");
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
    this.UpdateEmployeeSalary = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      month_year: [''],
      working_days: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      updated_by: [''],
      'Employees': this.formBuilder.array([
        this.formBuilder.group({
          'id': [''],
          'employee_id': [''],
          'emp_name': [''],
          'salary': ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
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
      this.UpdateEmployeeSalary.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        'conditions[branch_id]': this.branchId,
        'conditions[att_year]': this.attYear,
        'conditions[att_month]': this.attMonth,
        'conditions[att_date]': this.attDate,
        
      }
      //this.userservice.getData('', "get-employee-attendances/"+this.branchId+"/"+this.attYear+"/"+this.attMonth+"/"+this.attDate,"GET").subscribe((response: any) => {
      this.userservice.getData(cond, "get-employee-salary", "POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.UpdateEmployeeSalary.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateEmployeeSalary.get('branch_id').setValue(response.data[0].branch_id);
            let setAttdate = {
              'year': parseInt(this.attYear),
              'month': parseInt(this.attMonth),
              'day': parseInt(this.attDate)
            }
            this.UpdateEmployeeSalary.get('month_year').setValue(setAttdate);
            this.UpdateEmployeeSalary.get('working_days').setValue(response.data[0].working_days);
            const EmployeesArray = <FormArray>this.UpdateEmployeeSalary.get('Employees');
            this.setBranchList(response.data[0].tenant_id);
            for (let i = 0; i < response.data.length; i++) {
              if (i > 0) {
                this.addEmployee();
              }
              EmployeesArray.controls[i].get('id').setValue(response.data[i]['id']);
              EmployeesArray.controls[i].get('employee_id').setValue(response.data[i]['employee_id']);
              EmployeesArray.controls[i].get('emp_name').setValue('employee_name');
              EmployeesArray.controls[i].get('salary').setValue(response.data[i]['salary']);
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
            this.appService.showAlert("Employee Salary", "Sorry!! Something went wrong!", "error");
          }
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
    });
  }
}
