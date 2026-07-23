import { Component, OnInit, ElementRef, ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-lead-management',
  templateUrl: './update-lead-management.component.html',
  styles: []
})

export class UpdateLeadManagementComponent implements OnInit {
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
  @ViewChild('followup_status_type_id') focus15:ElementRef;
  @ViewChild('source_type_id') focus16:ElementRef;

  UpdateLeadManagement: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  stateList: any;
  cityList: any;
  employeeList: any;
  districtList: any;
  companyList: any[];
  isValidPincode: boolean;
  isValidMobile: boolean;
  isValidPhone: boolean;
  numOnlyPattern = "^[0-9]*$";
  pincodePattern = "^[0-9]{6}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  txtSpPattern = "^[a-zA-Z ]*$";
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  employeeId: any;
  followupstatusList: any[];
  hideLeadStatus: boolean = false;
  sourceTypeList: any[];
  
  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateLeadManagement.controls;
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

  setBranchList(tenant_id) { 
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

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateLeadManagement.value); 
    if (this.UpdateLeadManagement.invalid) 
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
      else if(this.f.followup_status_type_id.errors) {
        this.focus15.nativeElement.focus();
      } 
      else if(this.f.source_type_id.errors) {
        this.focus16.nativeElement.focus();
      } 
      return;
    }
    
    this.userservice.getData(result, "update-lead-management", "JSON").subscribe((response: any) => {
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
    this.UpdateLeadManagement = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      lead_customer_name: ['',[Validators.required,Validators.pattern('^[a-zA-Z]+$')]],
      phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
      mobile_no: ['',[Validators,Validators.pattern(this.mobnumPattern)]],
      email_id: ['',[Validators.pattern(this.mailPattern)]], 
      address_line_1: [''],
      address_line_2: [''],
      landmark: [''],
      state_id: [''],
      district_id: [''],
      city_id: [''],
      pincode: ['',[Validators.pattern(this.pincodePattern)]],
      followup_status_type_id:[''],
      source_type_id:['', Validators.required],
      updated_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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

    this.route.params.subscribe(params => {
      this.employeeId = params.id;
      this.UpdateLeadManagement.get('id').setValue(this.employeeId);
      this.UpdateLeadManagement.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.employeeId 
      }
      this.userservice.getData(cond, "get-lead-managements","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateLeadManagement.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateLeadManagement.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateLeadManagement.get('employee_id').setValue(response.data[0].employee_id);
            this.UpdateLeadManagement.get('lead_customer_name').setValue(response.data[0].lead_customer_name);
            this.UpdateLeadManagement.get('phone_no').setValue(response.data[0].phone_no);
            this.UpdateLeadManagement.get('mobile_no').setValue(response.data[0].mobile_no);
            this.UpdateLeadManagement.get('email_id').setValue(response.data[0].email_id);
            this.UpdateLeadManagement.get('address_line_1').setValue(response.data[0].address_line_1);
            this.UpdateLeadManagement.get('address_line_2').setValue(response.data[0].address_line_2);
            this.UpdateLeadManagement.get('landmark').setValue(response.data[0].landmark);
            this.UpdateLeadManagement.get('state_id').setValue(response.data[0].state_id);
            this.UpdateLeadManagement.get('district_id').setValue(response.data[0].district_id);
            this.UpdateLeadManagement.get('city_id').setValue(response.data[0].city_id);
            this.UpdateLeadManagement.get('pincode').setValue(response.data[0].pincode);
            this.UpdateLeadManagement.get('followup_status_type_id').setValue(response.data[0].followup_status_type_id);
            this.UpdateLeadManagement.get('source_type_id').setValue(response.data[0].source_type_id);
            this.setBranchList(response.data[0].tenant_id);
            this.setSourceTypeList(response.data[0].tenant_id);
            this.setEmployeeList(response.data[0].branch_id);
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
            this.setDistrict(response.data[0].state_id,'district');
            this.setCity(response.data[0].district_id,'city');
          }
          else {
            this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Lead Management", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
