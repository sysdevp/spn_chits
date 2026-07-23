import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-group',
  templateUrl: './view-group.component.html',
  styles: []
})

export class ViewGroupComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewGroupInfo: ViewData[]=[];
  BranchInfo: ViewData[]=[];
  SchemeInfo: ViewData[]=[];
  BankInfo: ViewData[]=[];
  ViewGroup: FormGroup;
  constructor(private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {

  }
  get f() {
    return this.ViewGroup.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-group-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          let record =  this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Group", response.msg , "success");
        }
        else {
          this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
  }

  ngOnInit() {
    this.TableHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.ViewGroup = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewGroup.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewGroup.value);

    this.userservice.getData(result, "list-groups", "JSON").subscribe((response: any) => {
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
      else { }
    });
  } 
  
  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewGroupInfo = response.data[0];
          this.BranchInfo = response.data[0].branch_info;
          this.SchemeInfo = response.data[0].scheme_info;
          this.BankInfo = response.data[0].bank_info; 
        }
      } catch (err) {
        this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
      }
    });

    this.modalService.open(content, options).result.then((result) => {
      }, (reason) => {
    });
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

  setData(data) 
  {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}

export interface ViewData 
{
  sno: string;
  branch_name: string;
  group_type: string;
  group_name: string;
  scheme_name: string;
  auction_date: string;
  auction_time: string;
  starting_date: string;
  first_due_date: string;
  commission_percent: string;
  fd_number: string;
  total_fd: string;
  company_fd: string;
  rate_of_interest: string;
  fd_rate: string;
  fd_date: string;
  maturity_amount: string;
  fd_bank_name: string;
  fd_branch: string;
  pso_number: string;
  pso_date: string;
  blaw_number: string;
  blaw_date: string;
  cheque_no: string;
  company_chit: string;
  group_base_penalty: string;
  group_base_bonus: string;
  group_base_interest: string;
  group_base_dividend_cancel_days: string;
  group_penalty_days: string;
  group_bonus_days: string;
  group_base_incentive_percentage: string;
}
