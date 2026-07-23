import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
    selector: 'app-view-branch',
    templateUrl: './view-branch.component.html',
    styles: []
})

export class ViewBranchComponent implements OnInit {
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewBranchInfo: ViewData[]=[];
    stateInfo: ViewData[]=[];
    districtInfo: ViewData[]=[];
    cityInfo: ViewData[]=[];
    headOfficeInfo: ViewData[]=[];
    ViewBranch: FormGroup;
    constructor(private formBuilder: FormBuilder,  private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    }
    get f() {
        return this.ViewBranch.controls;
    }
    changeStatus(recordId,statusId) { 
        let data = {
        'id' : recordId
        }
        this.userservice.getData(data, "update-branch-status", "POST").subscribe((response: any) => {
        try {
            if (response.status == 'Success') { 
            this.dataList.filter((listing: any) => {
                if(listing.id == recordId)
                {
                listing.status = statusId;
                }
            });
            this.appService.showAlert("Branch", response.msg , "success");
            }
            else {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
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
        this.ViewBranch = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewBranch.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewBranch.value);
    
        this.userservice.getData(result, "list-branches", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
        try {
            if (response.status == 'Success') { 
                this.viewBranchInfo = response.data[0];
                this.stateInfo = response.data[0].state_info;
                this.districtInfo = response.data[0].district_info;
                this.cityInfo = response.data[0].city_info;
                this.headOfficeInfo = response.data[0].head_office_info;
            }
        } catch (err) {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
        }
     }, (err: any) => {
        if (err.status == 401) {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
        }
        else {
            this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
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
    head_office_id: string;
    branch_name: string;
    start_date: string;
    branch_code:string;
    state_id:string;
    district_id:string;
    city_id: string;
    pincode: string;
    branch_wise_penalty: string;
    bonus:string;
    email_id:string;
    phone_no:string;
    address_line_1: string;
    address_line_2:string;
    landmark: string;
    prize_subscriber_penalty:string;
    non_prize_subscriber_penalty: string;
    bonus_days:string;
    penalty_days: string;
    business_agent_target:string;
    branch_fd_total_months: string;
    remarks:string;
    agent_incentive_percent: string;
    agent_commission_percent:string;
}


