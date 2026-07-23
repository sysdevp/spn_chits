import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-view-employee-salary',
  templateUrl: './view-employee-salary.component.html',
  styles: []
})
export class ViewEmployeeSalaryComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewEmployeeSalaryInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  EmployeeInfo: ViewData[]=[];
  Date: any;
  BranchName: any;
  ViewEmployeeSalary: FormGroup;

  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  {

  }
  get f() {
    return this.ViewEmployeeSalary.controls;
}
  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewEmployeeSalary = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewEmployeeSalary.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewEmployeeSalary.value);
  
    this.userservice.getData(result, "list-employee-monthly-salary", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-employee-salary", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.viewEmployeeSalaryInfo=response.data;
          console.log( this.viewEmployeeSalaryInfo);
        }
        else {
          this.viewEmployeeSalaryInfo = [];
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
}
