import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-lead-management',
  templateUrl: './view-lead-management.component.html',
  styles: []
})
export class ViewLeadManagementComponent implements OnInit {
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewLeadmanagementInfo: ViewData[]=[];
    branchInfo: ViewData[]=[];
    followup_info: ViewData[]=[];
    stateInfo: ViewData[]=[];
    district_info: ViewData[]=[];
    city_info: ViewData[]=[];
    employeeInfo: ViewData[]=[];
    followupstatustype_info: ViewData[]=[];
    
    ViewLead: FormGroup;
    source_type_info: ViewData[]=[];
    
    constructor(private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
    {

    }
    get f() {
        return this.ViewLead.controls;
    }
    ngOnInit() {
        this.TableHidden = true;
        let tenant_id = localStorage.getItem('tenant_id');
        this.ViewLead = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewLead.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewLead.value);
        
        this.userservice.getData(result, "list-lead-managements", "JSON").subscribe((response: any) => {
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

    open(content, options = {}, recordId=0 ) {
        let cond = {
            "conditions[id]" : recordId 
        }
        this.userservice.getData(cond, "get-lead-managements", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                this.viewLeadmanagementInfo = response.data[0];
                this.branchInfo = response.data[0].branch_info;
                this.stateInfo = response.data[0].state_info;
                this.employeeInfo = response.data[0].employee_info;
                this.district_info = response.data[0].district_info;
                this.city_info = response.data[0].city_info;
                this.followup_info = response.data[0].followup_info;
                this.followupstatustype_info = response.data[0].followupstatustype_info;
                this.source_type_info = response.data[0].source_type_info;
                
                }
                else {
                this.viewLeadmanagementInfo = [];
                this.branchInfo = [];
                this.stateInfo = [];
                this.district_info = [];
                this.city_info = [];
                this.followup_info = [];
                this.followupstatustype_info = [];
                this.source_type_info = [];
                
                }
            } catch (err) {
                this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
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
  branch_id: string;
  employee_id: string;
  lead_customer_name: string;
  followup_status_type_id: string;
}



