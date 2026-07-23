import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-view-accounting-head',
  templateUrl: './view-accounting-head.component.html',
  styles: []
})
export class ViewAccountingHeadComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewAccountHeadInfo: ViewData[]=[];
  ViewAccountHead: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) 
  { 

  }
  get f() {
    return this.ViewAccountHead.controls;
}
    ngOnInit() {
        this.TableHidden = true;
        let tenant_id = localStorage.getItem('tenant_id');
        this.ViewAccountHead = this.formBuilder.group({
            tenant_id:[''],
        });
        this.ViewAccountHead.get('tenant_id').setValue(tenant_id);
        const result = Object.assign({}, this.ViewAccountHead.value);
     
        this.userservice.getData(result, "list-head-group", "JSON").subscribe((response: any) => {
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
        this.userservice.getData(cond, "get-head-group", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.viewAccountHeadInfo = response.data[0];
                }
            } catch (err) {
                this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
            }
        }, (err: any) => {
            if (err.status == 401) {
                this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
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
        this.userservice.getData(data, "update-account-head-status", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') { 
                    this.dataList.filter((listing: any) => {
                        if(listing.id == recordId)
                        {
                            listing.status = statusId;
                        }
                    });
                    this.appService.showAlert("Accounting Head", response.msg , "success");
                }
                else {
                    this.appService.showAlert("Accounting Head", "Sorry!! Something went wrong!", "error");
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
    group_name: string;
    name: string;
    remarks: string;
}

