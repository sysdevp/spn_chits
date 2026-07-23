import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  
  selector: 'app-view-dynamic-menu',
  templateUrl: './view-dynamic-menu.component.html',
  styles: []
})

export class ViewDynamicMenuComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewMenuInfo: ViewData[]=[];
  parentInfo: ViewData[]=[];

  constructor(private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    
  }

  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-dynamic-menu-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          let record =  this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Dynamic Menu", response.msg , "success");
        }
        else {
          this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
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
    let cond = {};
    if(tenant_id) {
      cond = {
        "tenant_id" : tenant_id,
      }
    }
    this.userservice.getData(cond, "list-dynamic-menus", "JSON").subscribe((response: any) => {
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
    this.userservice.getData(cond, "get-dynamic-menus", "POST").subscribe((response: any) => {
      try {
        this.parentInfo = [];
        if (response.status == 'Success') { 
          this.ViewMenuInfo = response.data[0];
          this.parentInfo = response.data[0].parent_info;
        }
      } catch (err) {
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
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
  menu_title: string;
  icon_class: string;
  router_link: string;
  has_submenu: string;
  tab_type: string;
  parent_menu_id: string; 
  page_type: string;
}
