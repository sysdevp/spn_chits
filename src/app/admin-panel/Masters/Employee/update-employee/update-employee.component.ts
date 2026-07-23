import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { DropzoneDirective } from 'ngx-dropzone-wrapper';
import { HttpClient, HttpHeaders, HttpRequest, HttpResponse, HttpEventType } from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  
  selector: 'app-update-employee',
  templateUrl: './update-employee.component.html',
  styles: []
})

export class UpdateEmployeeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_type') focus3:ElementRef;
  @ViewChild('saluation') focus4:ElementRef;
  @ViewChild('first_name') focus5:ElementRef;
  @ViewChild('last_name') focus6:ElementRef;
  @ViewChild('gender') focus7:ElementRef;
  @ViewChild('dob') focus8:ElementRef;
  @ViewChild('photo') focus9:ElementRef;
  @ViewChild('collection_agent') focus10:ElementRef;
  @ViewChild('phone_no') focus11:ElementRef;
  @ViewChild('mobile_no') focus12:ElementRef;
  @ViewChild('email') focus13:ElementRef;
  @ViewChild('adhar_card') focus14:ElementRef;
  @ViewChild('driving_license') focus15:ElementRef;
  @ViewChild('pan_no') focus16:ElementRef;
  @ViewChild('ration_card_no') focus17:ElementRef;
  @ViewChild('father_name') focus18:ElementRef;
  @ViewChild('spouse_name') focus19:ElementRef;
  @ViewChild('door_no') focus20:ElementRef;
  @ViewChild('building_name') focus21:ElementRef;
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
  @ViewChild('designation_id') focus38:ElementRef;
  @ViewChild('role_id') focus39:ElementRef;
  @ViewChild('pf') focus40:ElementRef;
  @ViewChild('esi') focus41:ElementRef;
  @ViewChild('blood_group') focus42:ElementRef;
  @ViewChild('qualification') focus43:ElementRef;
  @ViewChild('previous_company_details') focus44:ElementRef;
  @ViewChild('joining_date') focus45:ElementRef;
  @ViewChild('experience') focus46:ElementRef;
  @ViewChild('verification_on_call_by') focus47:ElementRef;
  @ViewChild('last_month_salary') focus48:ElementRef;
  @ViewChild('holder_name') focus49:ElementRef;
  @ViewChild('bank_id') focus50:ElementRef;
  @ViewChild('account_no') focus51:ElementRef;
  @ViewChild('ifsc_code') focus52:ElementRef;
  @ViewChild('target') focus53:ElementRef;
  @ViewChild('salary') focus54:ElementRef;
  @ViewChild('collection_area_id') focus55:ElementRef;
  @ViewChild('commission') focus56:ElementRef;
  @ViewChild('incentive') focus57:ElementRef;
  @ViewChild('target_month') focus58:ElementRef;
  @ViewChild('target_percentage') focus59:ElementRef;

  UpdateEmployee: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  stateList: any;
  cityList: any;
  districtList: any;
  designationList: any;
  roleList: any;
  bankList: any;
  companyList: any[];
  isValidPincode: boolean;
  isValidMobile: boolean;
  isValidPhone: boolean;
  communicationDistrictList: any[];
  communicationCityList: any[];
  collectionAreaList: any[];
  numOnlyPattern = "^[0-9]*$";
  pincodePattern = "^[0-9]{6}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  panPattern = "^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$";
  txtSpPattern = "^[a-zA-Z ]*$";
  alphaNumDotSpPattern = "^[a-zA-Z0-9. ]*$";
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  ifscPattern = "^[a-zA-Z0-9]+$";
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
  employee_photo : any;
  employeeId: any;
  mantFieldAlertMsg: any;
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
  
  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

  constructor(private http: HttpClient,private modalService: NgbModal,private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
    return this.UpdateEmployee.controls;
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

  branchChg(event) {
    this.setCollectionAreaList(event.target.value);
  }

  setCollectionAreaList(branch_id) {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAreaList = response.data;
        } else {
          this.collectionAreaList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  stateChg(event) 
  { 
    this.setDistrict(event.target.value,'district');
    this.cityList = [];
    this.UpdateEmployee.get('district_id').setValue('');
    this.UpdateEmployee.get('city_id').setValue('');
  }

  communicationStateChg(event)
  {
    this.setDistrict(event.target.value,'communication-district');
    this.communicationCityList = [];
    this.UpdateEmployee.get('communication_district_id').setValue('');
    this.UpdateEmployee.get('communication_city_id').setValue('');
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
    this.UpdateEmployee.get('city_id').setValue('');
  } 

  communicationDistrictChg(event)
  {
    this.setCity(event.target.value,'communication-city');
    this.UpdateEmployee.get('communication_city_id').setValue('');
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
      this.employee_photo = f.name;
      this.http.post(this.APIUrl+ 'employee-file-upload', formData, {headers: headers})
      .subscribe(
        (response) => { 
          this.UpdateEmployee.get('photo').setValue(response['file_name']);
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
    element = document.getElementById("myDiv") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
  }

  chkChg(event) { 
    if(event.target.checked) {
      let doorNo = this.UpdateEmployee.get('door_no').value;
      let buildingName = this.UpdateEmployee.get('building_name').value;
      let addressLine1 = this.UpdateEmployee.get('address_line_1').value;
      let addressLine2 = this.UpdateEmployee.get('address_line_2').value;
      let landmark = this.UpdateEmployee.get('landmark').value;
      let state = this.UpdateEmployee.get('state_id').value;
      let district = this.UpdateEmployee.get('district_id').value;
      let city = this.UpdateEmployee.get('city_id').value;
      let pincode = this.UpdateEmployee.get('pincode').value;
      this.UpdateEmployee.get('communication_door_no').setValue(doorNo);
      this.UpdateEmployee.get('communication_building_name').setValue(buildingName);
      this.UpdateEmployee.get('communication_address_line_1').setValue(addressLine1);
      this.UpdateEmployee.get('communication_address_line_2').setValue(addressLine2);
      this.UpdateEmployee.get('communication_land_mark').setValue(landmark);
      this.UpdateEmployee.get('communication_state_id').setValue(state);
      this.communicationDistrictList = this.districtList;
      this.communicationCityList = this.cityList;
      this.UpdateEmployee.get('communication_district_id').setValue(district);
      this.UpdateEmployee.get('communication_city_id').setValue(city);
      this.UpdateEmployee.get('communication_pincode').setValue(pincode);
    }
  }
 
  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateEmployee.value); 
    if (this.UpdateEmployee.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_type.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.saluation.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.first_name.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.last_name.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.gender.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.dob.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.photo.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.collection_agent.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.email.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.adhar_card.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.driving_license.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.pan_no.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.ration_card_no.errors) {
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
      else if(this.f.building_name.errors) {
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
      else if(this.f.designation_id.errors) {
        this.focus38.nativeElement.focus();
      }
      else if(this.f.role_id.errors) {
        this.focus39.nativeElement.focus();
      }
      else if(this.f.pf.errors) {
        this.focus40.nativeElement.focus();
      }
      else if(this.f.esi.errors) {
        this.focus41.nativeElement.focus();
      }
      else if(this.f.blood_group.errors) {
        this.focus42.nativeElement.focus();
      }
      else if(this.f.qualification.errors) {
        this.focus43.nativeElement.focus();
      }
      else if(this.f.previous_company_details.errors) {
        this.focus44.nativeElement.focus();
      }
      else if(this.f.joining_date.errors) {
        this.focus45.nativeElement.focus();
      }
      else if(this.f.experience.errors) {
        this.focus46.nativeElement.focus();
      }
      else if(this.f.verification_on_call_by.errors) {
        this.focus47.nativeElement.focus();
      }
      else if(this.f.last_month_salary.errors) {
        this.focus48.nativeElement.focus();
      }
      else if(this.f.holder_name.errors) {
        this.focus49.nativeElement.focus();
      }
      else if(this.f.bank_id.errors) {
        this.focus50.nativeElement.focus();
      }
      else if(this.f.account_no.errors) {
        this.focus51.nativeElement.focus();
      }
      else if(this.f.ifsc_code.errors) {
        this.focus52.nativeElement.focus();
      }
      else if(this.f.target.errors) {
        this.focus53.nativeElement.focus();
      }
      else if(this.f.salary.errors) {
        this.focus54.nativeElement.focus();
      }
      else if(this.f.collection_area_id.errors) {
        this.focus55.nativeElement.focus();
      }
      else if(this.f.commission.errors) {
        this.focus56.nativeElement.focus();
      }
      else if(this.f.incentive.errors) {
        this.focus57.nativeElement.focus();
      }
      return;
    }

    if((this.f.adhar_card.value=='') && (this.f.driving_license.value=='') && (this.f.pan_no.value=='') && (this.f.ration_card_no.value=='')) {
      this.focus14.nativeElement.focus();
      this.mantFieldAlertMsg = "Anyone of adhar no, pan no, ration card no is required";
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      return;
    }
    else {
      this.mantFieldAlertMsg = '';
    }
    
    this.userservice.getData(result, "update-employee", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Employee",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-employee']);
          }, 300);
        } else {
          this.appService.showAlert("Employee", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Employee", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateEmployee = this.formBuilder.group({
      id: [''],
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_type: ['', Validators.required],
      saluation: [''],
      first_name: ['', Validators.required],
      last_name: ['', Validators.required],
      gender: ['', [Validators.required]],
      dob: ['', Validators.required],
      photo: [''],
      collection_agent: [''],
      phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
      mobile_no: ['',[Validators.required,Validators.pattern(this.mobnumPattern)]],
      email: ['',[Validators.pattern(this.mailPattern)]], 
      adhar_card: ['',[Validators.pattern(this.numOnlyPattern)]],
      driving_license: [''],
      pan_no: ['',[Validators.pattern(this.panPattern)]],
      ration_card_no: [''],
      father_name: [''],
      spouse_name: [''],
      door_no: [''],
      building_name: [''],
      address_line_1: [''],
      address_line_2: [''],
      landmark: [''],
      state_id: ['',[Validators.required]],
      district_id: ['',[Validators.required]],
      city_id: ['',[Validators.required]],
      pincode: ['',[Validators.required, Validators.pattern(this.pincodePattern)]],
      communication_door_no: [''],
      communication_building_name: [''],
      communication_address_line_1: [''],
      communication_address_line_2: [''],
      communication_land_mark: [''],
      communication_state_id: ['',[Validators.required]],
      communication_district_id: ['',[Validators.required]],
      communication_city_id: ['',[Validators.required]],
      communication_pincode: ['',[Validators.required, Validators.pattern(this.pincodePattern)]],
      designation_id: ['', Validators.required],
      role_id: ['', Validators.required],
      pf: ['',[Validators.pattern(this.decimalPattern)]],
      esi: ['',[Validators.pattern(this.decimalPattern)]],
      blood_group: [''],
      qualification: [''],
      previous_company_details: [''],
      joining_date: ['', Validators.required],
      experience: ['',[Validators.pattern(this.alphaNumDotSpPattern)]],
      verification_on_call_by: [''],
      last_month_salary: ['',[Validators.pattern(this.decimalPattern)]],
      holder_name: ['',[Validators.pattern(this.txtSpPattern)]],
      bank_id: [''],
      account_no: ['',[Validators.pattern(this.numOnlyPattern)]],
      ifsc_code: ['',[Validators.pattern(this.ifscPattern)]],
      target: ['',[Validators.pattern(this.decimalPattern)]],
      salary: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      collection_area_id: [''],
      commission: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      incentive: ['',[Validators.required,Validators.pattern(this.decimalPattern)]],
      updated_by: [''],
      target_month:[''],
      target_percentage:['']
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
      this.UpdateEmployee.get('id').setValue(this.employeeId);
      this.UpdateEmployee.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.employeeId 
      }
      this.userservice.getData(cond, "get-employees","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateEmployee.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateEmployee.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateEmployee.get('employee_type').setValue(response.data[0].employee_type);
            this.UpdateEmployee.get('saluation').setValue(response.data[0].saluation);
            this.UpdateEmployee.get('first_name').setValue(response.data[0].first_name);
            this.UpdateEmployee.get('last_name').setValue(response.data[0].last_name);
            this.UpdateEmployee.get('gender').setValue(response.data[0].gender);
            if(response.data[0].dob!='') {
              let dobDate = (response.data[0].dob).split('/');
              let setDobdate = {
                'year': parseInt(dobDate[2]),
                'month': parseInt(dobDate[1]),
                'day': parseInt(dobDate[0])
              }
              this.UpdateEmployee.get('dob').setValue(setDobdate);
            }
            this.UpdateEmployee.get('photo').setValue(response.data[0].photo);
            this.employee_photo = response.data[0].photo;
            this.UpdateEmployee.get('collection_agent').setValue(response.data[0].collection_agent);
            this.UpdateEmployee.get('phone_no').setValue(response.data[0].phone_no);
            this.UpdateEmployee.get('mobile_no').setValue(response.data[0].mobile_no);
            this.UpdateEmployee.get('email').setValue(response.data[0].email);
            this.UpdateEmployee.get('adhar_card').setValue(response.data[0].adhar_card);
            this.UpdateEmployee.get('driving_license').setValue(response.data[0].driving_license);
            this.UpdateEmployee.get('pan_no').setValue(response.data[0].pan_no);
            this.UpdateEmployee.get('ration_card_no').setValue(response.data[0].ration_card_no);
            this.UpdateEmployee.get('father_name').setValue(response.data[0].father_name);
            this.UpdateEmployee.get('spouse_name').setValue(response.data[0].spouse_name);
            this.UpdateEmployee.get('door_no').setValue(response.data[0].door_no);
            this.UpdateEmployee.get('building_name').setValue(response.data[0].building_name);
            this.UpdateEmployee.get('address_line_1').setValue(response.data[0].address_line_1);
            this.UpdateEmployee.get('address_line_2').setValue(response.data[0].address_line_2);
            this.UpdateEmployee.get('landmark').setValue(response.data[0].landmark);
            this.UpdateEmployee.get('state_id').setValue(response.data[0].state_id);
            this.UpdateEmployee.get('district_id').setValue(response.data[0].district_id);
            this.UpdateEmployee.get('city_id').setValue(response.data[0].city_id);
            this.UpdateEmployee.get('pincode').setValue(response.data[0].pincode);
            this.UpdateEmployee.get('communication_door_no').setValue(response.data[0].communication_door_no);
            this.UpdateEmployee.get('communication_building_name').setValue(response.data[0].communication_building_name);
            this.UpdateEmployee.get('communication_address_line_1').setValue(response.data[0].communication_address_line_1);
            this.UpdateEmployee.get('communication_address_line_2').setValue(response.data[0].communication_address_line_2);
            this.UpdateEmployee.get('communication_land_mark').setValue(response.data[0].communication_land_mark);
            this.UpdateEmployee.get('communication_state_id').setValue(response.data[0].communication_state_id);
            this.UpdateEmployee.get('communication_district_id').setValue(response.data[0].communication_district_id);
            this.UpdateEmployee.get('communication_city_id').setValue(response.data[0].communication_city_id);
            this.UpdateEmployee.get('communication_pincode').setValue(response.data[0].communication_pincode);
            this.UpdateEmployee.get('designation_id').setValue(response.data[0].designation_id);
            this.UpdateEmployee.get('role_id').setValue(response.data[0].role_id);
            this.UpdateEmployee.get('pf').setValue(response.data[0].pf);
            this.UpdateEmployee.get('esi').setValue(response.data[0].esi);
            this.UpdateEmployee.get('blood_group').setValue(response.data[0].blood_group);
            this.UpdateEmployee.get('qualification').setValue(response.data[0].qualification);
            this.UpdateEmployee.get('previous_company_details').setValue(response.data[0].previous_company_details);
            if(response.data[0].joining_date!='') {
              let joiningDate = (response.data[0].joining_date).split('/');
              let setJoiningDate = {
                'year': parseInt(joiningDate[2]),
                'month': parseInt(joiningDate[1]),
                'day': parseInt(joiningDate[0])
              }
              this.UpdateEmployee.get('joining_date').setValue(setJoiningDate);
            }
            this.UpdateEmployee.get('experience').setValue(response.data[0].experience);
            this.UpdateEmployee.get('verification_on_call_by').setValue(response.data[0].verification_on_call_by);
            this.UpdateEmployee.get('last_month_salary').setValue(response.data[0].last_month_salary);
            this.UpdateEmployee.get('holder_name').setValue(response.data[0].holder_name);
            this.UpdateEmployee.get('bank_id').setValue(response.data[0].bank_id);
            this.UpdateEmployee.get('account_no').setValue(response.data[0].account_no);
            this.UpdateEmployee.get('ifsc_code').setValue(response.data[0].ifsc_code);
            this.UpdateEmployee.get('target').setValue(response.data[0].target);
            this.UpdateEmployee.get('salary').setValue(response.data[0].salary);
            this.UpdateEmployee.get('collection_area_id').setValue(response.data[0].collection_area_id);
            this.UpdateEmployee.get('commission').setValue(response.data[0].commission);
            this.UpdateEmployee.get('incentive').setValue(response.data[0].incentive);
            this.UpdateEmployee.get('target_percentage').setValue(response.data[0].incentive);
            this.UpdateEmployee.get('target_month').setValue(response.data[0].incentive);
            this.setBranchList(response.data[0].tenant_id);
            this.setCollectionAreaList(response.data[0].branch_id);
            this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.stateList = response.data;
                } else {
                  this.stateList = [];
                }
              } catch (err) { }
            }, (err: any) => { });
            let cond = {
              "tenant_id" : localStorage.getItem('tenant_id')
            }
        
            this.userservice.getData(cond, "list-designations", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.designationList = response.data;
                } else {
                  this.designationList = [];
                }
              } catch (err) { }
            }, (err: any) => { });
        
            this.userservice.getData(cond, "list-roles", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.roleList = response.data;
                } else {
                  this.roleList = [];
                }
              } catch (err) { }
            }, (err: any) => { });
        
            this.userservice.getData(cond, "list-banks", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.bankList = response.data;
                } else {
                  this.bankList = [];
                }
              } catch (err) { }
            }, (err: any) => { });
            this.setDistrict(response.data[0].state_id,'district');
            this.setDistrict(response.data[0].communication_state_id,'communication-district');
            this.setCity(response.data[0].district_id,'city');
            this.setCity(response.data[0].communication_district_id,'communication-city');
          }
          else {
            this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Employee", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
