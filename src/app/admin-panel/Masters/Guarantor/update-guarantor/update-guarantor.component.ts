import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router,ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-guarantor',
  templateUrl: './update-guarantor.component.html',
  styles: []
})

export class UpdateGuarantorComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('lead_id') focus3:ElementRef;
  @ViewChild('enrollment_id') focus4:ElementRef;
  @ViewChild('customer_id') focus5:ElementRef;
  @ViewChild('name') focus6:ElementRef;
  @ViewChild('photo') focus7:ElementRef;
  @ViewChild('dob') focus8:ElementRef;
  @ViewChild('gender') focus9:ElementRef;
  @ViewChild('mobile_no') focus10:ElementRef;
  @ViewChild('phone_no') focus11:ElementRef;
  @ViewChild('email_id') focus12:ElementRef;
  @ViewChild('adhar_no') focus13:ElementRef;
  @ViewChild('pan_no') focus14:ElementRef;
  @ViewChild('license_no')focus15:ElementRef;
  @ViewChild('ration_card_no')focus16:ElementRef;
  @ViewChild('doj')focus17:ElementRef;
  @ViewChild('father_name')focus18:ElementRef;
  @ViewChild('spouse_name') focus19:ElementRef;
  @ViewChild('door_no') focus20:ElementRef;
  @ViewChild('buildling_name') focus21:ElementRef;
  @ViewChild('address_line_1') focus22:ElementRef;
  @ViewChild('address_line_2') focus23:ElementRef;
  @ViewChild('landmark') focus24:ElementRef;
  @ViewChild('state_id') focus25:ElementRef;
  @ViewChild('district_id') focus26:ElementRef;
  @ViewChild('city_id') focus27:ElementRef;
  @ViewChild('pincode') focus28:ElementRef;
  @ViewChild('communication_door_no') focus29:ElementRef;
  @ViewChild('communication_building_name') focus30:ElementRef;
  @ViewChild('communication_address_line_1') focus31:ElementRef;
  @ViewChild('communication_address_line_2') focus32:ElementRef;
  @ViewChild('communication_land_mark') focus33:ElementRef;
  @ViewChild('communication_state_id') focus34:ElementRef;
  @ViewChild('communication_district_id') focus35:ElementRef;
  @ViewChild('communication_city_id') focus36:ElementRef;
  @ViewChild('communication_pincode') focus37:ElementRef;
  @ViewChild('is_guarantor') focus38:ElementRef;

  UpdateGuarantor: FormGroup;
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
  stateList: any[];
  districtList: any[];
  cityList: any[];
  isValidMobile: boolean;
  isValidPincode: boolean;
  isValidPhone: boolean;
  isValidMail: boolean;
  customer_photo:any;
  mantFieldAlertMsg: any;
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
 
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
  customer_id: any;
  relationshipList: any;
  customerList: any[];
  communicationCityList: any;
  communicationDistrictList: any;
  enrollmentList: any[];
  minDate = undefined;
  maxDate= undefined;
  
  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}
  

  constructor(private route: ActivatedRoute,private http: HttpClient,private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
    return this.UpdateGuarantor.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
   }
 
  branchChg(event)
  {
    let branch_id=event.target.value;
   // this.setCustomerList(branch_id);
  }

  customerChg(event)
  {
    let customer_id=event.target.value;
    this.setEnrollmentrList(customer_id);
  }

  setEnrollmentrList(customer_id) {
    let cond = {
      "conditions[customer_id]" : customer_id
    }
    this.userservice.getData(cond, "get-customer-enrollments-for-gaurantor", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.enrollmentList = response.data;
        } else {
          this.enrollmentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

 /*  setCustomerList(branch_id) {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  } */

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
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  stateChg(event) 
  { 
    this.setDistrict(event.target.value,'district');
    this.cityList = [];
    this.UpdateGuarantor.get('district_id').setValue('');
    this.UpdateGuarantor.get('city_id').setValue('');
  }

  communicationStateChg(event)
  {
    this.setDistrict(event.target.value,'communication-district');
    this.communicationCityList = [];
    this.UpdateGuarantor.get('communication_district_id').setValue('');
    this.UpdateGuarantor.get('communication_city_id').setValue('');
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
    this.UpdateGuarantor.get('city_id').setValue('');
  } 

  communicationDistrictChg(event)
  {
    this.setCity(event.target.value,'communication-city');
    this.UpdateGuarantor.get('communication_city_id').setValue('');
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

  chkChg(event) { 
    if(event.target.checked) {
      let doorNo = this.UpdateGuarantor.get('door_no').value;
      let buildingName = this.UpdateGuarantor.get('buildling_name').value;
      let addressLine1 = this.UpdateGuarantor.get('address_line_1').value;
      let addressLine2 = this.UpdateGuarantor.get('address_line_2').value;
      let landmark = this.UpdateGuarantor.get('landmark').value;
      let state = this.UpdateGuarantor.get('state_id').value;
      let district = this.UpdateGuarantor.get('district_id').value;
      let city = this.UpdateGuarantor.get('city_id').value;
      let pincode = this.UpdateGuarantor.get('pincode').value;
      this.UpdateGuarantor.get('communication_door_no').setValue(doorNo);
      this.UpdateGuarantor.get('communication_building_name').setValue(buildingName);
      this.UpdateGuarantor.get('communication_address_line_1').setValue(addressLine1);
      this.UpdateGuarantor.get('communication_address_line_2').setValue(addressLine2);
      this.UpdateGuarantor.get('communication_land_mark').setValue(landmark);
      this.UpdateGuarantor.get('communication_state_id').setValue(state);
      this.communicationDistrictList = this.districtList;
      this.communicationCityList = this.cityList;
      this.UpdateGuarantor.get('communication_district_id').setValue(district);
      this.UpdateGuarantor.get('communication_city_id').setValue(city);
      this.UpdateGuarantor.get('communication_pincode').setValue(pincode);
    }
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
      this.http.post(this.APIUrl+ 'customer-file-upload', formData, {headers: headers}).subscribe(
        (response) => { 
          this.UpdateGuarantor.get('photo').setValue(response['file_name']);
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
    const result = Object.assign({}, this.UpdateGuarantor.value);
    if (this.UpdateGuarantor.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.lead_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.enrollment_id.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.name.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.photo.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.dob.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.gender.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.email_id.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.adhar_no.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.pan_no.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.license_no.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.ration_card_no.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.doj.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.father_name.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.spouse_name.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.door_no.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.buildling_name.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.address_line_1.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.address_line_2.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.landmark.errors) {
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
      else if(this.f.communication_door_no.errors) {
        this.focus29.nativeElement.focus();
      }
      else if(this.f.communication_building_name.errors) {
        this.focus30.nativeElement.focus();
      }
      else if(this.f.communication_address_line_1.errors) {
        this.focus31.nativeElement.focus();
      }
      else if(this.f.communication_address_line_2.errors) {
        this.focus32.nativeElement.focus();
      }
      else if(this.f.communication_land_mark.errors) {
        this.focus33.nativeElement.focus();
      }
      else if(this.f.communication_state_id.errors) {
        this.focus34.nativeElement.focus();
      }
      else if(this.f.communication_district_id.errors) {
        this.focus35.nativeElement.focus();
      }
      else if(this.f.communication_city_id.errors) {
        this.focus36.nativeElement.focus();
      }
      else if(this.f.communication_pincode.errors) {
        this.focus37.nativeElement.focus();
      }
      else if(this.f.is_guarantor.errors) {
        this.focus38.nativeElement.focus();
      }
      return;
    }

    if((this.f.adhar_no.value=='') && (this.f.pan_no.value=='') && (this.f.ration_card_no.value=='') && (this.f.license_no.value=='')) {
      this.focus13.nativeElement.focus();
      this.mantFieldAlertMsg = "Anyone of adhar no, pan no, ration card no, licence no is required";
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      return;
    }
    else {
      this.mantFieldAlertMsg = '';
    }

    this.userservice.getData(result,"update-guarantor","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Guarantor", "Guarantor added Successfully", "success");
          setTimeout(() => {
            swal.close();
            this.router.navigate(['/admin-panel/view-guarantor']);
          }, 3000);
        } else {
          this.appService.showAlert("Guarantor", "Guarantor added Successfully", "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateGuarantor = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      lead_management_id:[''],
      enrollment_id:['', Validators.required],
      customer_id:['', Validators.required],
      name:['',[Validators.required, Validators.pattern('^[a-zA-Z]+$')]],
      photo:[''],
      dob:[null, Validators.required],
      gender:[''],
      mobile_no:['',[Validators.required,Validators.pattern(this.mobnumPattern)]],
      phone_no:['',[Validators.pattern(this.phonenumPattern)]],
      email_id:['',[Validators.pattern(this.mailPattern)]],
      adhar_no:[''],
      pan_no:['',[Validators.pattern(this.panPattern)]],
      license_no:[''],
      ration_card_no:[''],
      doj:[null, Validators.required],
      father_name:['',[Validators.pattern('^[a-zA-Z]+$')]],
      spouse_name:['',[Validators.pattern('^[a-zA-Z]+$')]],
      door_no:[''],
      buildling_name:[''],
      address_line_1:[''],
      address_line_2:[''],
      landmark:[''],
      state_id:['', Validators.required],
      district_id:['', Validators.required],
      city_id:['', Validators.required],
      pincode:['',[Validators.required, Validators.pattern(this.pincodePattern)]],
      communication_door_no:[''],
      communication_building_name:[''],
      communication_address_line_1:[''],
      communication_address_line_2:[''],
      communication_land_mark:[''],
      communication_state_id:['', Validators.required],
      communication_district_id:['', Validators.required],
      communication_city_id:['', Validators.required],
      communication_pincode:['',[Validators.required, Validators.pattern(this.pincodePattern)]],
      is_guarantor:['1'],
      id: [''],
      updated_by: ['']
    });
    this.isValidMobile = true;
    this.isValidPincode = true;
    this.isValidPhone = true;
    this.isValidMail = true;
    let tenant_id = localStorage.getItem('tenant_id');
    this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.stateList = response.data;
        } else {
          this.stateList = [];
        }
      }  catch (err) {}
    }, (err: any) => {});
    if(tenant_id) {
      this.UpdateGuarantor.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') {
            this.branchList = response.data;
            this.companyList = response.data;
          } else {
            this.branchList = [];
            this.companyList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
    this.UpdateGuarantor.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-relationships", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.relationshipList = response.data;
        } else {
          this.relationshipList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
   /*  this.userservice.getData("", "list-guarantors", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); */
    this.route.params.subscribe(params => {
      this.customer_id = params.id;
      this.UpdateGuarantor.get('id').setValue(this.customer_id);
      let cond = {
        "conditions[id]" : this.customer_id 
      }
      this.userservice.getData(cond, "get-guarantors","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateGuarantor.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateGuarantor.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateGuarantor.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateGuarantor.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdateGuarantor.get('name').setValue(response.data[0].guarantor_info.name);
            this.UpdateGuarantor.get('photo').setValue(response.data[0].guarantor_info.photo);
            this.customer_photo = response.data[0].guarantor_info.photo;
            if(response.data[0].dob!='') {
              let dobDate = (response.data[0].dob).split('/'); 
              let setDobdate = {
              'year': parseInt(dobDate[2]),
              'month': parseInt(dobDate[1]),
              'day': parseInt(dobDate[0])
              }
              this.UpdateGuarantor.get('dob').setValue(setDobdate);
            }
            this.UpdateGuarantor.get('gender').setValue(response.data[0].guarantor_info.gender);
            this.UpdateGuarantor.get('mobile_no').setValue(response.data[0].guarantor_info.mobile_no);
            this.UpdateGuarantor.get('phone_no').setValue(response.data[0].guarantor_info.phone_no);
            this.UpdateGuarantor.get('email_id').setValue(response.data[0].guarantor_info.email_id);
            this.UpdateGuarantor.get('adhar_no').setValue(response.data[0].guarantor_info.adhar_no);
            this.UpdateGuarantor.get('pan_no').setValue(response.data[0].guarantor_info.pan_no);
            this.UpdateGuarantor.get('license_no').setValue(response.data[0].guarantor_info.license_no);
            this.UpdateGuarantor.get('ration_card_no').setValue(response.data[0].guarantor_info.ration_card_no);
            if(response.data[0].doj!='') {
              let dojDate = (response.data[0].doj).split('/');
              let setDojdate = {
              'year': parseInt(dojDate[2]),
              'month': parseInt(dojDate[1]),
              'day': parseInt(dojDate[0])
              }
              this.UpdateGuarantor.get('doj').setValue(setDojdate);
            }
            this.UpdateGuarantor.get('father_name').setValue(response.data[0].guarantor_info.father_name);
            this.UpdateGuarantor.get('spouse_name').setValue(response.data[0].guarantor_info.spouse_name);
            this.UpdateGuarantor.get('door_no').setValue(response.data[0].guarantor_info.door_no);
            this.UpdateGuarantor.get('buildling_name').setValue(response.data[0].guarantor_info.buildling_name);
            this.UpdateGuarantor.get('address_line_1').setValue(response.data[0].guarantor_info.address_line_1);
            this.UpdateGuarantor.get('address_line_2').setValue(response.data[0].guarantor_info.address_line_2);
            this.UpdateGuarantor.get('landmark').setValue(response.data[0].guarantor_info.landmark);
            this.UpdateGuarantor.get('state_id').setValue(response.data[0].guarantor_info.state_id);
            this.UpdateGuarantor.get('district_id').setValue(response.data[0].guarantor_info.district_id);
            this.UpdateGuarantor.get('city_id').setValue(response.data[0].guarantor_info.city_id);
            this.UpdateGuarantor.get('pincode').setValue(response.data[0].guarantor_info.pincode);
            this.UpdateGuarantor.get('communication_door_no').setValue(response.data[0].guarantor_info.communication_door_no);
            this.UpdateGuarantor.get('communication_building_name').setValue(response.data[0].guarantor_info.communication_building_name);
            this.UpdateGuarantor.get('communication_address_line_1').setValue(response.data[0].guarantor_info.communication_address_line_1);
            this.UpdateGuarantor.get('communication_address_line_2').setValue(response.data[0].guarantor_info.communication_address_line_2);
            this.UpdateGuarantor.get('communication_land_mark').setValue(response.data[0].guarantor_info.communication_land_mark);
            this.UpdateGuarantor.get('communication_state_id').setValue(response.data[0].guarantor_info.communication_state_id);
            this.UpdateGuarantor.get('communication_district_id').setValue(response.data[0].guarantor_info.communication_district_id);
            this.UpdateGuarantor.get('communication_city_id').setValue(response.data[0].guarantor_info.communication_city_id);
            this.UpdateGuarantor.get('communication_pincode').setValue(response.data[0].guarantor_info.communication_pincode);
            this.UpdateGuarantor.get('is_guarantor').setValue(response.data[0].guarantor_info.is_guarantor);
            this.setDistrict(response.data[0].guarantor_info.state_id,'district');
            this.setDistrict(response.data[0].guarantor_info.communication_state_id,'communication-district');
            this.setCity(response.data[0].guarantor_info.district_id,'city');
            this.setCity(response.data[0].guarantor_info.communication_district_id,'communication-city');
            this.setBranchList(response.data[0].tenant_id);
            this.setEnrollmentrList(response.data[0].customer_id);
          }
          else {
            this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

