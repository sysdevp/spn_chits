import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-lead-management',
  templateUrl: './add-lead-management.component.html',
  styles: []
})

export class AddLeadManagementComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('lead_customer_name') focus4:ElementRef;
  @ViewChild('phone_no') focus5:ElementRef;
  @ViewChild('mobile_no') focus6:ElementRef;
  @ViewChild('email_id') focus7:ElementRef;
  @ViewChild('address_line_1') focus8:ElementRef;
  @ViewChild('address_line_2') focus9:ElementRef;
  @ViewChild('landmark') focus10:ElementRef;
  @ViewChild('state_id') focus11:ElementRef;
  @ViewChild('district_id') focus12:ElementRef;
  @ViewChild('city_id') focus13:ElementRef;
  @ViewChild('pincode') focus14:ElementRef;
  @ViewChild('followup_date') focus15:ElementRef;
  @ViewChild('next_followup_date') focus16:ElementRef;
  @ViewChild('remarks') focus17:ElementRef;
  @ViewChild('followup_status_type_id') focus18:ElementRef;
  @ViewChild('source_type_id') focus19:ElementRef;
    
  AddLeadManagement: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  stateList: any;
  cityList: any;
  districtList: any;
  companyList: any[];
  employeeList: any;
  followupstatusList: any;
  isValidPincode: boolean;
  isValidMobile: boolean;
  isValidPhone: boolean;
  pincodePattern = "^[0-9]{6}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  txtSpPattern = "^[a-zA-Z ]*$";
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  sourceTypeList: any[];
 
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddLeadManagement.controls;
  }

  stateChg(event) 
  { 
    this.setDistrict(event.target.value,'district');
  }

  setDistrict(state_id,listType='district') 
  {
    let cond = {
      "conditions[state_id]" : state_id
    }
    this.userservice.getData(cond ,"get-districts", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          if(listType=="district") {
            this.districtList = response.data;
          }
        } else {
          if(listType=="district") {
            this.districtList = [];
          }
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  districtChg(event)
  {
    this.setCity(event.target.value,'city');
  } 

  setCity(district_id,listType='city') 
  {
    let cond = {
      "conditions[district_id]" : district_id
    }
    this.userservice.getData(cond ,"get-cities", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          if(listType=='city') {
            this.cityList = response.data;
          }
        } else {
          if(listType=='city') {
            this.cityList = [];
          }
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  
  onKeyUpMobile(event) { 
    let mobile_no = event.target.value;
    if(mobile_no=='') {
      this.isValidMobile = true;
    }
    else {
      if((mobile_no).match(this.mobnumPattern)) {
        this.isValidMobile = true;
      }
      else {
        this.isValidMobile = false;
      }
    }
  }
    
  onKeyUpPhone(event) { 
    let phone = event.target.value;
    if(phone=='') {
      this.isValidPhone = true;
    }
    else {
      if((phone).match(this.phonenumPattern)) {
        this.isValidPhone = true;
      }
      else {
        this.isValidPhone = false;
      }
    }
  } 
    
  onKeyUpPincode(event) { 
    let pincode = event.target.value;
    if(pincode=='') {
      this.isValidPincode = true;
    }
    else {
      if((pincode).match(this.pincodePattern)) {
        this.isValidPincode = true;
      }
      else {
        this.isValidPincode = false;
      }
    }
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setSourceTypeList(tenant_id);
  }

  setSourceTypeList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-source-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.sourceTypeList = response.data;
        } else {
          this.sourceTypeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1,
      "log_id" : localStorage.getItem('logged_employee_id')
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setEmployeeList(branch_id);
  }

  setEmployeeList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
 
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddLeadManagement.value);
    if (this.AddLeadManagement.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
    
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.lead_customer_name.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.email_id.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.address_line_1.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.address_line_2.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.landmark.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.state_id.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.district_id.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.city_id.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.pincode.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.followup_date.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.next_followup_date.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.followup_status_type_id.errors) {
        this.focus18.nativeElement.focus();
      } 
      else if(this.f.source_type_id.errors) {
        this.focus19.nativeElement.focus();
      }       
      return;      
    }

    this.userservice.getData(result, "store-lead-management", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit"; 
      try { 
        if (response.status == 'Success') {
          this.appService.showAlert("Lead Management",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-lead-management']);
          }, 300);
        } else {
          this.appService.showAlert("Lead Management", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Lead Management", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddLeadManagement = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      lead_customer_name: [''],
      phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
      mobile_no: ['',[Validators.required,Validators.pattern(this.mobnumPattern)]],
      email_id: ['',[Validators.pattern(this.mailPattern)]], 
      address_line_1: [''],
      address_line_2: [''],
      landmark: [''],
      state_id: [''],
      district_id: [''],
      city_id: [''],
      pincode: ['',[Validators.pattern(this.pincodePattern)]],
      followup_date:['', Validators.required],
      next_followup_date:[null],
      remarks:[''],
      followup_status_type_id:['', Validators.required],
      source_type_id:['', Validators.required],
      created_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddLeadManagement.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      this.setSourceTypeList(tenant_id);
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
    this.AddLeadManagement.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.stateList = response.data;
        } else {
          this.stateList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData("", "list-followup-status", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.followupstatusList = response.data;
        } else {
          this.followupstatusList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData("", "list-source-types", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.sourceTypeList = response.data;
        } else {
          this.sourceTypeList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }
}
