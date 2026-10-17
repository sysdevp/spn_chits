import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-branch',
  templateUrl: './add-branch.component.html',
  styles: []
})

export class AddBranchComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('head_office_id') focus2:ElementRef;
  @ViewChild('branch_name') focus3:ElementRef;
  @ViewChild('start_date') focus5:ElementRef;
  @ViewChild('bonus_days') focus6:ElementRef;
  @ViewChild('bonus') focus7:ElementRef;
  @ViewChild('prize_subscriber_penalty') focus8:ElementRef;
  @ViewChild('non_prize_subscriber_penalty') focus9:ElementRef;
  @ViewChild('penalty_days') focus10:ElementRef;
  @ViewChild('branch_fd_total_months') focus11:ElementRef;
  @ViewChild('branch_wise_penalty') focus12:ElementRef;
  @ViewChild('phone_no') focus13:ElementRef;
  @ViewChild('mobile_no') focus14:ElementRef;
  @ViewChild('email_id') focus15:ElementRef;
  @ViewChild('address_line_1') focus16:ElementRef;
  @ViewChild('address_line_2') focus17:ElementRef;
  @ViewChild('landmark') focus18:ElementRef;
  @ViewChild('business_agent_target') focus19:ElementRef;
  @ViewChild('agent_incentive_percent') focus20:ElementRef;
  @ViewChild('agent_commission_percent') focus21:ElementRef;
  @ViewChild('cgst_percent') focus22:ElementRef;
  @ViewChild('sgst_percent') focus23:ElementRef;
  @ViewChild('gst_percent') focus24:ElementRef;
  @ViewChild('state_id') focus25:ElementRef;
  @ViewChild('district_id') focus26:ElementRef;
  @ViewChild('city_id') focus27:ElementRef;
  @ViewChild('pincode') focus28:ElementRef;
  @ViewChild('remarks') focus29:ElementRef;
    
  AddBranch: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  changeText = false;
  changeText1 = false;
  changeText2 = false;
  changeText3 = false;
  changeText4 = false;
  changeText5 = false;
  SubmitbuttonName: string;
  stateList: any;
  cityList: any;
  districtList: any;
  isValidMobile: boolean;
  isValidPincode: boolean;
  isValidPhone: boolean;
  isValidMail: boolean;
  headOfficeList: any[];
  isSuperAdmin: boolean;
  companyList: any[];
  mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  pincodePattern = "^[0-9]{6}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  numOnlyPattern = "^[0-9]*$";
  cgstValue: any;
  sgstValue: any;
  gstValue:any
  currentDate:any;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
    this.currentDate = {
      year: today_date.getFullYear(),
      month: today_date.getMonth()+1,
      day: today_date.getDate()
    };
  }

  get f() {
    return this.AddBranch.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setHeadOfficeList(tenant_id);
  }

  setHeadOfficeList(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-head-offices", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.headOfficeList = response.data;
        } else {
          this.headOfficeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  stateChg(event) 
  {
    let cond = {
        "conditions[state_id]" : event.target.value
    }
    this.userservice.getData(cond ,"get-districts", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.districtList = response.data;
        } else {
          this.districtList = [];
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  districteChg(event)
  {
    let cond = {
      "conditions[district_id]" : event.target.value
    }
    this.userservice.getData(cond ,"get-cities", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.cityList = response.data;
        } else {
          this.cityList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }  

  cgstAmt(event)
  {
    this.cgstValue=event.target.value;
    let gst=parseFloat(this.cgstValue)+parseFloat(this.sgstValue);
    let gsts=gst.toFixed(2);
    this.gstValue=gsts;
    this.AddBranch.get('gst_percent').setValue(gst);
    if(this.gstValue=='')
    {
      this.AddBranch.get('gst_percent').setValue(0);
    }
  }

  sgstAmt(event)
  {
    this.sgstValue=event.target.value;
    //let gst=parseInt(this.cgstValue)+parseInt(this.sgstValue);
    let gst=parseFloat(this.cgstValue)+parseFloat(this.sgstValue);
    let gsts=gst.toFixed(2);
    this.gstValue=gsts;
    this.AddBranch.get('gst_percent').setValue(gst);
    if(this.gstValue=='')
    {
      this.AddBranch.get('gst_percent').setValue(0);
    }
  }

  onKeyUpMobile(event) { 
    let mobileNumber = event.target.value;
    if(mobileNumber=='') {
      this.isValidMobile = true;
    }
    else {
      if((mobileNumber).match(this.mobnumPattern)) {
        this.isValidMobile = true;
      }
      else {
        this.isValidMobile = false;
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

  onKeyUpMail(event) { 
    let mail = event.target.value;
    if(mail=='') {
      this.isValidMail = true;
    }
    else {
      if((mail).match(this.mailPattern)) {
        this.isValidMail = true;
      }
      else {
        this.isValidMail = false;
      }
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddBranch.value);
  
    if (this.AddBranch.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.head_office_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.branch_name.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.start_date.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.bonus_days.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.bonus.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.prize_subscriber_penalty.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.non_prize_subscriber_penalty.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.penalty_days.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.branch_fd_total_months.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.branch_wise_penalty.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.email_id.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.address_line_1.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.address_line_2.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.landmark.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.business_agent_target.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.agent_incentive_percent.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.agent_commission_percent.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.cgst_percent.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.sgst_percent.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.gst_percent.errors) {
        this.focus24.nativeElement.focus();
      }
      else if(this.f.state_id.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.district_id.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.city_id.errors) {
        this.focus27.nativeElement.focus();
      }
      else if(this.f.pincode.errors) {
        this.focus28.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus29.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-branch", "JSON").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
            if (response.status == 'Success') {
            this.appService.showAlert("Branch",response.msg, "success");
              setTimeout(() => {
                this.router.navigate(['/admin-panel/view-branch']);
              }, 300);
            } else {
            this.appService.showAlert("Branch", response.msg, "error");
            this.disableBtn = false;
            }
        } catch (err) {
            this.appService.showAlert("Branch", 'Registered Failed', "error");
            this.disableBtn = false;
        }
    }, (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Branch", "Sorry!! Something went wrong!", "error");
        }
    });
  }

  ngOnInit() {
    this.AddBranch = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      head_office_id: [''],
      branch_name: ['', Validators.required],
      start_date: [this.currentDate, Validators.required],
      bonus_days: ['',[Validators.required,Validators.pattern(this.numOnlyPattern)]],
      bonus: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      prize_subscriber_penalty: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      non_prize_subscriber_penalty: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      penalty_days: ['',[Validators.required,Validators.pattern(this.numOnlyPattern)]],
      branch_fd_total_months: ['',[Validators.pattern(this.numOnlyPattern)]],
      branch_wise_penalty: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
      mobile_no: ['',[Validators.pattern(this.mobnumPattern)]],
      email_id: ['',[Validators.pattern(this.mailPattern)]],
      address_line_1:[''],
      address_line_2: [''],
      landmark: [''],
      business_agent_target: ['',[Validators.pattern(this.decimalPattern)]],
      agent_incentive_percent: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      agent_commission_percent: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      cgst_percent: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      sgst_percent: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      gst_percent: ['',[Validators.pattern(this.decimalPattern)]],
      state_id: [''],
      district_id:[''],
      city_id:[''],
      pincode: ['',[Validators.pattern(this.pincodePattern)]],
      latitude: [''],
      longitude: [''],
      punch_radius_meters: [50],
      remarks:[''],
      created_by: [''],
    });
  
    this.isValidMobile = true;
    this.isValidPincode = true;
    this.isValidPhone = true;
    this.isValidMail = true;

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddBranch.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setHeadOfficeList(tenant_id);
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
    this.AddBranch.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.stateList = response.data;
        } else {
          this.stateList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
}
