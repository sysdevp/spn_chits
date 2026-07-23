import { Component, OnInit , ElementRef,ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router,ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  
  selector: 'app-move-to-customer',
  templateUrl: './move-to-customer.component.html',
  styles: []
})

export class MoveToCustomerComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('lead_management_id') focus3:ElementRef;
  @ViewChild('customer_code') focus4:ElementRef;
  @ViewChild('name') focus5:ElementRef;
  @ViewChild('photo') focus6:ElementRef;
  @ViewChild('dob') focus7:ElementRef;
  @ViewChild('gender') focus8:ElementRef;
  @ViewChild('mobile_no') focus9:ElementRef;
  @ViewChild('phone_no') focus10:ElementRef;
  @ViewChild('email_id') focus11:ElementRef;
  @ViewChild('adhar_no') focus12:ElementRef;
  @ViewChild('pan_no') focus13:ElementRef;
  @ViewChild('license_no')focus14:ElementRef;
  @ViewChild('ration_card_no')focus15:ElementRef;
  @ViewChild('doj')focus16:ElementRef;
  @ViewChild('father_name')focus17:ElementRef;
  @ViewChild('spouse_name') focus18:ElementRef;
  @ViewChild('door_no') focus19:ElementRef;
  @ViewChild('buildling_name') focus20:ElementRef;
  @ViewChild('address_line_1') focus21:ElementRef;
  @ViewChild('address_line_2') focus22:ElementRef;
  @ViewChild('landmark') focus23:ElementRef;
  @ViewChild('state_id') focus24:ElementRef;
  @ViewChild('district_id') focus25:ElementRef;
  @ViewChild('city_id') focus26:ElementRef;
  @ViewChild('pincode') focus27:ElementRef;
  @ViewChild('communication_door_no') focus28:ElementRef;
  @ViewChild('communication_building_name') focus29:ElementRef;
  @ViewChild('communication_address_line_1') focus30:ElementRef;
  @ViewChild('communication_address_line_2') focus31:ElementRef;
  @ViewChild('communication_land_mark') focus32:ElementRef;
  @ViewChild('communication_state_id') focus33:ElementRef;
  @ViewChild('communication_district_id') focus34:ElementRef;
  @ViewChild('communication_city_id') focus35:ElementRef;
  @ViewChild('communication_pincode') focus36:ElementRef;
  @ViewChild('refered_by') focus37:ElementRef;
  @ViewChild('pf_no') focus38:ElementRef;
  @ViewChild('esi_no') focus39:ElementRef;
  @ViewChild('retirement') focus40:ElementRef;
  @ViewChild('occupation') focus41:ElementRef;
  @ViewChild('source_of_funds') focus42:ElementRef;
  @ViewChild('income') focus43:ElementRef;
  @ViewChild('relationship_id') focus44:ElementRef;
  @ViewChild('customer_belongs_to') focus45:ElementRef;
  @ViewChild('customer_bonus') focus46:ElementRef;
  @ViewChild('customer_penalty_interest') focus47:ElementRef;
  @ViewChild('customer_interest') focus48:ElementRef;
  @ViewChild('customer_divident') focus49:ElementRef;
  @ViewChild('sms') focus50:ElementRef;
  @ViewChild('is_customer') focus51:ElementRef;
  @ViewChild('is_guarantor') focus52:ElementRef;

  AddCustomer: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName:string;
  isSuperAdmin:boolean;
  branchList: any[];
  companyList: any[];
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  pincodePattern = "^[0-9]{6}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  panPattern = "^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  decimalPattern = "^[0-9]+(.[0-9]{0,2})?$";
  StateList: any;
  DistrictList: any;
  CityList: any;
  isValidMobile: boolean;
  isValidPincode: boolean;
  isValidPhone: boolean;
  isValidMail: boolean;
  customer_photo:any;
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
  leadId: any;
  minDate = undefined;
  maxDate= undefined;
 
  uploader = new FileUploader({
    url: '',
    headers: [{
      name: 'Content-Type',
      value: 'application/x-www-form-urlencoded'
    },
    {
      name: 'Authorization',
      value: this.auth_token
    },
    {
      name: 'Accept',
      value: 'multipart/form-data'
    }]
  });
  hasBaseDropZoneOver = false;
  push: boolean = false;
  overallupload: number;
  relationshipList: any;
  customerList: any;
  communicationCityList: any[];
  cityList: any[];
  communicationDistrictList: any[];
  districtList: any[];
  stateList: any;
  
  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){

  }
  
  constructor(private http: HttpClient, private route: ActivatedRoute ,private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.overallupload = 0;  
    this.APIUrl = environment.API;  
    this.SubmitbuttonName = "Submit";
    const current = new Date();
    this.minDate = {
      year: current.getFullYear()-100,
      month: current.getMonth() + 1,
      day: 1
    };
    this.maxDate = {
      year: current.getFullYear()-10,
      month: current.getMonth() + 1,
      day: current.getDate()
    };
  }

  get f() {
    return this.AddCustomer.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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

  open(content, options = {}) {
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

  uploadFileDetails(event) { 
    this.overallupload = 0;
    let fileList: any = event;
    if (fileList.length > 0) {
      let f: File = fileList[0]._file;
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      const formData: FormData = new FormData();
      formData.append("photo", f, f.name);
      this.customer_photo = f.name;
      this.http.post(this.APIUrl+ 'customer-file-upload', formData, {headers: headers})
      .subscribe(
        (response) => { 
          this.AddCustomer.get('photo').setValue(response['file_name']);
          this.triggerFalseClick();
        },
        (error) => { 
        }                   
      );
    }
  }

  triggerFalseClick() {  
    let element: HTMLElement = document.getElementById("myDiv") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
    element = document.getElementById("myDivDocuments") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
  }

  stateChg(event) 
  { 
    this.setDistrict(event.target.value,'district');
  }

  communicationStateChg(event)
  {
    this.setDistrict(event.target.value,'communication-district');
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
          else if(listType=="communication-district") {
            this.communicationDistrictList = response.data;
          }
        } else {
          if(listType=="district") {
            this.districtList = [];
          }
          else if(listType=="communication-district") {
            this.communicationDistrictList = [];
          }
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  districtChg(event)
  {
    this.setCity(event.target.value,'city');
    this.AddCustomer.get('city_id').setValue('');
  } 

  communicationDistrictChg(event)
  {
    this.setCity(event.target.value,'communication-city');
    this.AddCustomer.get('communication_city_id').setValue('');
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
          else if(listType=='communication-city') {
            this.communicationCityList = response.data;
          }
        } else {
          if(listType=='city') {
            this.cityList = [];
          }
          else if(listType=='communication-city') {
            this.communicationCityList = [];
          }
        }
      } catch (err) {}
    }, (err: any) => { });
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

  chkChg(event) { 
    if(event.target.checked) {
      let doorNo = this.AddCustomer.get('door_no').value;
      let buildingName = this.AddCustomer.get('buildling_name').value;
      let addressLine1 = this.AddCustomer.get('address_line_1').value;
      let addressLine2 = this.AddCustomer.get('address_line_2').value;
      let landmark = this.AddCustomer.get('landmark').value;
      let state = this.AddCustomer.get('state_id').value;
      let district = this.AddCustomer.get('district_id').value;
      let city = this.AddCustomer.get('city_id').value;
      let pincode = this.AddCustomer.get('pincode').value;
      this.AddCustomer.get('communication_door_no').setValue(doorNo);
      this.AddCustomer.get('communication_building_name').setValue(buildingName);
      this.AddCustomer.get('communication_address_line_1').setValue(addressLine1);
      this.AddCustomer.get('communication_address_line_2').setValue(addressLine2);
      this.AddCustomer.get('communication_land_mark').setValue(landmark);
      this.AddCustomer.get('communication_state_id').setValue(state);
      this.communicationDistrictList = this.districtList;
      this.communicationCityList = this.cityList;
      this.AddCustomer.get('communication_district_id').setValue(district);
      this.AddCustomer.get('communication_city_id').setValue(city);
      this.AddCustomer.get('communication_pincode').setValue(pincode);
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCustomer.value);
    if (this.AddCustomer.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.lead_management_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.customer_code.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.name.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.photo.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.dob.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.gender.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.email_id.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.adhar_no.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.pan_no.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.license_no.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.ration_card_no.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.doj.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.father_name.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.spouse_name.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.door_no.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.buildling_name.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.address_line_1.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.address_line_2.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.landmark.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.state_id.errors) {
        this.focus24.nativeElement.focus();
      }
      else if(this.f.district_id.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.city_id.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.pincode.errors) {
        this.focus27.nativeElement.focus();
      }
      else if(this.f.communication_door_no.errors) {
        this.focus28.nativeElement.focus();
      }
      else if(this.f.communication_building_name.errors) {
        this.focus29.nativeElement.focus();
      }
      else if(this.f.communication_address_line_1.errors) {
        this.focus30.nativeElement.focus();
      }
      else if(this.f.communication_address_line_2.errors) {
        this.focus31.nativeElement.focus();
      }
      else if(this.f.communication_land_mark.errors) {
        this.focus32.nativeElement.focus();
      }
      else if(this.f.communication_state_id.errors) {
        this.focus33.nativeElement.focus();
      }
      else if(this.f.communication_district_id.errors) {
        this.focus34.nativeElement.focus();
      }
      else if(this.f.communication_city_id.errors) {
        this.focus35.nativeElement.focus();
      }
      else if(this.f.communication_pincode.errors) {
        this.focus36.nativeElement.focus();
      }
      else if(this.f.refered_by.errors) {
        this.focus37.nativeElement.focus();
      }
      else if(this.f.pf_no.errors) {
        this.focus38.nativeElement.focus();
      }
      else if(this.f.esi_no.errors) {
        this.focus39.nativeElement.focus();
      }
      else if(this.f.retirement.errors) {
        this.focus40.nativeElement.focus();
      }
      else if(this.f.occupation.errors) {
        this.focus41.nativeElement.focus();
      }
      else if(this.f.source_of_funds.errors) {
        this.focus42.nativeElement.focus();
      }
      else if(this.f.income.errors) {
        this.focus43.nativeElement.focus();
      }
      else if(this.f.relationship_id.errors) {
        this.focus44.nativeElement.focus();
      }
      else if(this.f.customer_belongs_to.errors) {
        this.focus45.nativeElement.focus();
      }
      else if(this.f.customer_bonus.errors) {
        this.focus46.nativeElement.focus();
      }
      else if(this.f.customer_penalty_interest.errors) {
        this.focus47.nativeElement.focus();
      }
      else if(this.f.customer_interest.errors) {
        this.focus48.nativeElement.focus();
      }
      else if(this.f.customer_divident.errors) {
        this.focus49.nativeElement.focus();
      }
      else if(this.f.sms.errors) {
        this.focus50.nativeElement.focus();
      }
      else if(this.f.is_customer.errors) {
        this.focus51.nativeElement.focus();
      }
      else if(this.f.is_guarantor.errors) {
        this.focus52.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-customer", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Customer",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-lead-management']);
          }, 300);
        } else {
          this.appService.showAlert("Customer", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Customer", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCustomer = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      lead_management_id:[''],
      customer_code:[''],
      name:['',[Validators.required,Validators.pattern('^[a-zA-Z]+$')]],
      photo:[''],
      dob:[null, Validators.required],
      gender:['',Validators.required],
      mobile_no:['',[Validators.required, Validators.pattern(this.mobnumPattern)]],
      phone_no:['',[Validators.pattern(this.phonenumPattern)]],
      email_id:['',[Validators.pattern(this.mailPattern)]],
      adhar_no:[''],
      pan_no:['',[Validators.pattern(this.panPattern)]],
      license_no:[''],
      ration_card_no:[''],
      doj:['',Validators.required],
      father_name:['',[Validators.pattern(this.txtSpPattern)]],
      spouse_name:['',[Validators.pattern(this.txtSpPattern)]],
      door_no:[''],
      buildling_name:[''],
      address_line_1:[''],
      address_line_2:[''],
      landmark:[''],
      state_id:['', Validators.required],
      district_id:['', Validators.required],
      city_id:['', Validators.required],
      pincode:['',[Validators.required,Validators.pattern(this.pincodePattern)]],
      communication_door_no:[''],
      communication_building_name:[''],
      communication_address_line_1:[''],
      communication_address_line_2:[''],
      communication_land_mark:[''],
      communication_state_id:['', Validators.required],
      communication_district_id:['', Validators.required],
      communication_city_id:['', Validators.required],
      communication_pincode:['',[Validators.required,Validators.pattern(this.pincodePattern)]],
      refered_by:['',Validators.required],
      pf_no:[''],
      esi_no:[''],
      retirement:[null],
      occupation:[''],
      source_of_funds:[''],
      income:['',[Validators.pattern(this.decimalPattern)]],
      relationship_id:[''],
      customer_belongs_to:[''],
      customer_bonus:['',[Validators.pattern(this.decimalPattern)]],
      customer_penalty_interest:['',[Validators.pattern(this.decimalPattern)]],
      customer_interest:['',[Validators.pattern(this.decimalPattern)]],
      customer_divident:['',[Validators.pattern(this.decimalPattern)]],
      sms:[''],
      is_customer:['1'],
      is_guarantor:[''],
      created_by: [''],
    });

    this.isValidMobile = true;
    this.isValidPincode = true;
    this.isValidPhone = true;
    this.isValidMail = true;
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCustomer.get('tenant_id').setValue(tenant_id);
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
    this.AddCustomer.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-relationships", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.relationshipList = response.data;
        } else {
          this.relationshipList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.userservice.getData("", "list-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    
    this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.stateList = response.data;
        } else {
          this.StateList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.route.params.subscribe(params => {
      this.leadId = params.id;
      this.AddCustomer.get('lead_management_id').setValue(this.leadId);
      let cond = {
        "conditions[id]" : this.leadId 
      }
      this.userservice.getData(cond, "get-lead-managements","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.AddCustomer.get('tenant_id').setValue(response.data[0].tenant_id);
            this.AddCustomer.get('branch_id').setValue(response.data[0].branch_id);
            this.AddCustomer.get('lead_management_id').setValue(this.leadId);
            this.AddCustomer.get('name').setValue(response.data[0].lead_customer_name);
            this.AddCustomer.get('mobile_no').setValue(response.data[0].mobile_no);
            this.AddCustomer.get('phone_no').setValue(response.data[0].phone_no);
            this.AddCustomer.get('email_id').setValue(response.data[0].email_id);
            this.AddCustomer.get('address_line_1').setValue(response.data[0].address_line_1);
            this.AddCustomer.get('address_line_2').setValue(response.data[0].address_line_2);
            this.AddCustomer.get('landmark').setValue(response.data[0].landmark);
            if(response.data[0].state_id) {
              this.AddCustomer.get('state_id').setValue(response.data[0].state_id);
              this.AddCustomer.get('communication_state_id').setValue(response.data[0].state_id);
              this.setDistrict(response.data[0].state_id,'district');
              this.setDistrict(response.data[0].state_id,'communication-district');
            }
            if(response.data[0].district_id) {
              this.AddCustomer.get('district_id').setValue(response.data[0].district_id);
              this.AddCustomer.get('communication_district_id').setValue(response.data[0].district_id);
              this.setCity(response.data[0].district_id,'city');
              this.setCity(response.data[0].district_id,'communication-city');
            }
            if(response.data[0].city_id) {
              this.AddCustomer.get('city_id').setValue(response.data[0].city_id);
              this.AddCustomer.get('communication_city_id').setValue(response.data[0].city_id);
            }
            this.AddCustomer.get('pincode').setValue(response.data[0].pincode);
            this.AddCustomer.get('communication_pincode').setValue(response.data[0].pincode);
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}


