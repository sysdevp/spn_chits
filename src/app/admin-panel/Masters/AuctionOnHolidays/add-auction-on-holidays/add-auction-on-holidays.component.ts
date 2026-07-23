import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-auction-on-holidays',
  templateUrl: './add-auction-on-holidays.component.html',
  styles: []
})

export class AddAuctionOnHolidaysComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('holiday_date') focus3:ElementRef;
  @ViewChild('event_name') focus4:ElementRef;

  AddAuctionOnHolidays: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  changeText = false;
  changeText1 = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  BranchList: any;
  branchList: any;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddAuctionOnHolidays.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  branchChg(event) {
    let tenant_id = this.AddAuctionOnHolidays.get('tenant_id').value;
    let branch_id = event.target.value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id
    }
  }

  setBranchList(tenant_id){ 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddAuctionOnHolidays.value);
    if (this.AddAuctionOnHolidays.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.holiday_date.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.event_name.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-holiday", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Auction On Holidays",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-auction-on-holidays']);
          }, 300);
        } else {
          this.appService.showAlert("Auction On Holidays", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Auction On Holidays", 'Registered Failed', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Auction On Holidays", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddAuctionOnHolidays = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      holiday_date:['', Validators.required],
      event_name: ['', Validators.required],
      created_by: [''],
    });
   
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddAuctionOnHolidays.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
    this.AddAuctionOnHolidays.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
