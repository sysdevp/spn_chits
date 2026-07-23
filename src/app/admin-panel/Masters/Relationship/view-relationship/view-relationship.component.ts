import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-relationship',
  templateUrl: './view-relationship.component.html',
  styles: []
})

export class ViewRelationshipComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewRelationship: FormGroup;
  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
  
  }
  get f() {
    return this.ViewRelationship.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-relationship-status", "POST").subscribe((response: any) => {
    try {
      if (response.status == 'Success') { 
        this.dataList.filter((listing: any) => {
          if(listing.id == recordId)
          {
            listing.status = statusId;
          }
        });
        this.appService.showAlert("Relationship", response.msg , "success");
      }
      else {
      this.appService.showAlert("Relationship", "Sorry!! Something went wrong!", "error");
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
    this.ViewRelationship = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewRelationship.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewRelationship.value);
  
    this.userservice.getData(result, "list-relationships", "JSON").subscribe((response: any) => {
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
