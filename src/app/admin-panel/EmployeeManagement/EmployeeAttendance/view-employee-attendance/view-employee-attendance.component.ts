import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-employee-attendance',
  templateUrl: './view-employee-attendance.component.html',
  styles: []
})

export class ViewEmployeeAttendanceComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewEmployeeAttendanceInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  EmployeeInfo: ViewData[]=[];
  Date: any;
  BranchName: any;
  Forenoon: any;
  Afternoon: any;
  ViewEmployeeAttendance: FormGroup;

  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  {

  }
  get f() {
    return this.ViewEmployeeAttendance.controls;
}
  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewEmployeeAttendance = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewEmployeeAttendance.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewEmployeeAttendance.value);
  
    this.userservice.getData(result, "list-employee-attendances", "JSON").subscribe((response: any) => {
      try {
      if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
          this.TableHidden = false;
      },0);
      }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else {}
    });
  } 

  open(content, options = {}, branchId,attYear,attMonth,attDate) {
    let cond = {
      'conditions[branch_id]': branchId,
      'conditions[att_year]': attYear,
      'conditions[att_month]': attMonth,
      'conditions[att_date]': attDate,
    }
    this.userservice.getData(cond, "get-employee-attendances", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.viewEmployeeAttendanceInfo=response.data;
        }
        else {
          this.viewEmployeeAttendanceInfo = [];
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
    this.modalService.open(content, options).result.then((result) => {}, (reason) => {});
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}

export interface ViewData 
{
  sno:string;
  branch_name: string;
  first_name: string;
  last_name: string;
  att_date: string;
  attendance_type: string;
  leave_type: string;
  forenoon_session: string;
  afternoon_session: string;
}
