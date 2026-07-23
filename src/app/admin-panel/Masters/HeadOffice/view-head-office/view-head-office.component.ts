import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
    selector: 'app-view-head-office',
    templateUrl: './view-head-office.component.html',
    styles: []
})

export class ViewHeadOfficeComponent implements OnInit {
    dataList: any;
    showloader: boolean;
    TableHidden:boolean = true;
    viewHeadOfficeInfo: ViewData[]=[];
    stateInfo: ViewData[]=[];
    districtInfo: ViewData[]=[];
    cityInfo: ViewData[]=[];
    ViewHeadOffice: FormGroup;
    constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
    {

    }
    get f() {
        return this.ViewHeadOffice.controls;
    }
    ngOnInit() {
        this.TableHidden = true;
        let tenant_id = localStorage.getItem('tenant_id');
        this.ViewHeadOffice = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewHeadOffice.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewHeadOffice.value);
        
        this.userservice.getData(result, "list-head-offices", "JSON").subscribe((response: any) => {
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
        this.userservice.getData(cond, "get-head-offices", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.viewHeadOfficeInfo = response.data[0];
                    this.stateInfo = response.data[0].state_info;
                    this.districtInfo = response.data[0].district_info;
                    this.cityInfo = response.data[0].city_info;
                }
            } catch (err) {
                this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
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

    changeStatus(recordId,statusId) { 
        let data = {
          'id' : recordId
        }
        this.userservice.getData(data, "update-head-office-status", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                this.dataList.filter((listing: any) => {
                    if(listing.id == recordId)
                    {
                    listing.status = statusId;
                    }
                });
                this.appService.showAlert("Head Office", response.msg , "success");
                }
                else {
                this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
                }
            } catch (err) {}
        }, (err: any) => {
          if (err.status == 401) {}
          else {}
        });
    }
}

export interface ViewData 
{
    sno:string;
    head_name: string;
    head_code: string;
    mobile_no: string;
    phone_no:string;
    email_id:string;
    address_line_1:string;
    address_line_2: string;
    state_name: string;
    district_name: string;
    city_name:string;
    pincode:string;
    landmark:string;
    remarks: string;
}


