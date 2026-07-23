import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  
  selector: 'app-view-state',
  templateUrl: './view-state.component.html',
  styles: []
})

export class ViewStateComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  
  constructor( private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
 
  }

  ngOnInit() {
    let tenant_id = localStorage.getItem('tenant_id');
    let cond = {
      "tenant_id" : tenant_id 
    }
    this.TableHidden = true;
     this.userservice.getData(cond, "list-states", "JSON").subscribe((response: any) => {
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
  
  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
}
