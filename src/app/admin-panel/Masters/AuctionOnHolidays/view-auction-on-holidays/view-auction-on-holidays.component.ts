import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { AppService } from '../../../../app.service';
import { UserService } from '../../../../Services/user.service';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
@Component({
  
  selector: 'app-view-auction-on-holidays',
  templateUrl: './view-auction-on-holidays.component.html',
  styles: []
})

export class ViewAuctionOnHolidaysComponent implements OnInit {
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewauctionOnHolidays: FormGroup;
  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
  
  }
  get f() {
    return this.ViewauctionOnHolidays.controls;
}
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-holiday-status", "POST").subscribe((response: any) => {
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
    this.ViewauctionOnHolidays = this.formBuilder.group({
        tenant_id:[''],
    });
    this.ViewauctionOnHolidays.get('tenant_id').setValue(tenant_id);
    const result = Object.assign({}, this.ViewauctionOnHolidays.value);
  
    this.userservice.getData(result, "list-holidays", "JSON").subscribe((response: any) => {
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

