import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  
  selector: 'app-view-followups',
  templateUrl: './view-followups.component.html',
  styles: []
})

export class ViewFollowupsComponent implements OnInit {
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewFollowupInfo: ViewData[]=[];
    branchInfo: ViewData[]=[];
    followup_info: ViewData[]=[];
    employeeInfo: ViewData[]=[];
    followup_status_type_info: ViewData[]=[];
    LeadId: any;

    constructor( private modalService: NgbModal,private route: ActivatedRoute,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
    {

    }

    open(content, options = {}, recordId=0 ) {
        let cond = {
            "conditions[id]" : recordId 
        }
        this.userservice.getData(cond, "get-followups", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.viewFollowupInfo = response.data[0];
                    this.branchInfo = response.data[0].branch_info;
                    this.employeeInfo = response.data[0].employee_info;
                    this.followup_info = response.data[0].followup_info;
                    this.followup_status_type_info = response.data[0].followup_status_type_info;
                }
                else {
                    this.viewFollowupInfo = [];
                    this.branchInfo = [];
                    this.followup_info = [];
                    this.followup_status_type_info = [];
                }
            } catch (err) {
                this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
            }
        });
        this.modalService.open(content, options).result.then((result) => { }, (reason) => { });
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

    ngOnInit() {
        this.TableHidden = true;
        this.route.params.subscribe(params => {
            this.LeadId = params.id;
            let cond = {
                "conditions[lead_management_id]" : this.LeadId 
            }
            this.userservice.getData(cond, "get-followups","POST").subscribe((response: any) => {
                try {
                    if (response.status == 'Success') 
                    {
                        this.setData(response.data); 
                        setTimeout(() => {
                            this.TableHidden = false;
                        },0);
                    }
                    else {
                        this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
                    }
                } catch (err) {
                    this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
                }
            }, (err: any) => {
                if (err.status == 401) {
                    this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
                }
                else {
                    this.appService.showAlert("Followup", "Sorry!! Something went wrong!", "error");
                }
            });
        });
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



