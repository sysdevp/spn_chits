import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-collection-area',
  templateUrl: './view-collection-area.component.html',
  styles: []
})

export class ViewCollectionAreaComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewCollectionArea: FormGroup;
  constructor(private formBuilder: FormBuilder, private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  }
  get f() {
    return this.ViewCollectionArea.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
    'id' : recordId
    }
    this.userservice.getData(data, "update-collection-area-status", "POST").subscribe((response: any) => {
    try {
        if (response.status == 'Success') { 
        this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
            listing.status = statusId;
            }
        });
        this.appService.showAlert("Collection Area", response.msg , "success");
        }
        else {
        this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
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
    this.ViewCollectionArea = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewCollectionArea.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewCollectionArea.value);
 
    this.userservice.getData(result, "list-collection-areas", "JSON").subscribe((response: any) => {
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

