import { Component,OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  
  selector: 'app-view-tenant',
  templateUrl: './view-tenant.component.html',
  styles: []
})

export class ViewTenantComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewTenantInfo: ViewData[]=[];

  constructor( private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }

  ngOnInit() {
    this.TableHidden = true;
    this.userservice.getData("", "super-admin-list-tenants", "JSON").subscribe((response: any) => {
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

  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-tenant-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Tenant", response.msg , "success");
        }
        else {
          this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
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
    this.userservice.getData(cond, "super-admin-get-tenants", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewTenantInfo = response.data[0];
        }
        else{
          this.ViewTenantInfo = [];
        }
      } catch (err) {
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
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
  chit_fund_name: string;
  company_logo: string;
  receipts_logo: string;
  header_name:string;
  //admin_email:string;
  gst_no:string;
  authorized_person_name: string;
  phone_no: string;
  mobile_no: string;
  database_name:string;
}


