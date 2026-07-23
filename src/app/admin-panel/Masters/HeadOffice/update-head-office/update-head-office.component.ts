import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import swal from 'sweetalert2';

@Component({
  
    selector: 'app-update-head-office',
    templateUrl: './update-head-office.component.html',
    styles: []
}) 

export class UpdateHeadOfficeComponent implements OnInit {
    @ViewChild('tenant_id') focus1:ElementRef;
    @ViewChild('head_name') focus2:ElementRef;
    @ViewChild('head_code') focus3:ElementRef;
    @ViewChild('phone_no') focus4:ElementRef;
    @ViewChild('mobile_no') focus5:ElementRef;
    @ViewChild('email_id') focus6:ElementRef;
    @ViewChild('address_line_1') focus7:ElementRef;
    @ViewChild('address_line_2') focus8:ElementRef;
    @ViewChild('state_id') focus9:ElementRef;
    @ViewChild('district_id') focus10:ElementRef;
    @ViewChild('city_id') focus11:ElementRef;
    @ViewChild('pincode') focus12:ElementRef;
    @ViewChild('landmark') focus13:ElementRef;
    @ViewChild('remarks') focus14:ElementRef;
    
    UpdateHeadOffice: FormGroup;
    form_message = true;
    submitted = false;
    disableBtn = false;
    SubmitbuttonName: string;
    stateList: any;
    cityList: any;
    districtList: any;
    headOfficeId: any;
    isValidMobile: boolean;
    isValidPincode: boolean;
    isValidPhone: boolean;
    isValidMail: boolean;
    mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
    mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
    pincodePattern = "^[0-9]{6}$";
    phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
    isSuperAdmin: boolean;
    companyList: any;
 
    constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
    {     
        this.SubmitbuttonName = "Submit";
    }

    stateChg(event) 
    { 
      this.setDistrict(event.target.value,'district');
      this.cityList = [];
      this.UpdateHeadOffice.get('district_id').setValue('');
      this.UpdateHeadOffice.get('city_id').setValue('');
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
  
    districteChg(event)
    {
      this.setCity(event.target.value,'city');
      this.UpdateHeadOffice.get('city_id').setValue('');
    } 
  
    communicationDistrictChg(event)
    {
      this.setCity(event.target.value,'communication-city');
      this.UpdateHeadOffice.get('communication_city_id').setValue('');
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

    get f() {
        return this.UpdateHeadOffice.controls;
    }

    setDistricts(cond) {
        this.userservice.getData(cond ,"list-districts", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') {
                    this.districtList = response.data;
                } else {
                    this.districtList = [];
                }
            } catch (err) { }
        }, (err: any) => {});
    }

    setCities(cond) {
        this.userservice.getData(cond ,"get-cities", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') {
                    this.cityList = response.data;
                } else {
                    this.cityList = [];
                }
            } catch (err) {}
        }, (err: any) => {
                
        });
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
        const result = Object.assign({}, this.UpdateHeadOffice.value);
        if (this.UpdateHeadOffice.invalid) {
            this.SubmitbuttonName = "Submit";
            this.disableBtn = false;
            if(this.f.tenant_id.errors) {
                this.focus1.nativeElement.focus();
            }
            else if(this.f.head_name.errors) {
                this.focus2.nativeElement.focus();
            }
            else if(this.f.head_code.errors) {
                this.focus3.nativeElement.focus();
            }
            else if(this.f.phone_no.errors) {
                this.focus4.nativeElement.focus();
            }
            else if(this.f.mobile_no.errors) {
                this.focus5.nativeElement.focus();
            }
            else if(this.f.email_id.errors) {
                this.focus6.nativeElement.focus();
            }
            else if(this.f.address_line_1.errors) {
                this.focus7.nativeElement.focus();
            }
            else if(this.f.address_line_2.errors) {
                this.focus8.nativeElement.focus();
            }
            else if(this.f.state_id.errors) {
                this.focus9.nativeElement.focus();
            }
            else if(this.f.district_id.errors) {
                this.focus10.nativeElement.focus();
            }
            else if(this.f.city_id.errors) {
                this.focus11.nativeElement.focus();
            }
            else if(this.f.pincode.errors) {
                this.focus12.nativeElement.focus();
            }
            else if(this.f.landmark.errors) {
                this.focus13.nativeElement.focus();
            }
            else if(this.f.remarks.errors) {
                this.focus14.nativeElement.focus();
            }
            return;
        }

        this.userservice.getData(result,"update-head-office","POST").subscribe((response: any) => {
            this.SubmitbuttonName = "Submit";
            try {
                if (response.status == 'Success')   {
                    this.appService.showAlert("Head Office", response.msg, "success");
                    setTimeout(() => {
                        swal.close();
                        this.router.navigate(['/admin-panel/view-head-office']);
                    }, 3000);
                } else {
                    this.appService.showAlert("Head Office", response.msg, "error");
                    this.disableBtn = false;
                }
            } 
            catch (err) {
                this.appService.showAlert("Head Office", 'Problem on connectivity', "error");
                this.disableBtn = false;
            }
        }, (err: any) => {
            this.disableBtn = false;
            this.SubmitbuttonName = "Submit";
            if (err.status == 401) {
                this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Head Office", "Server Error!!", "error");
            }
        });
    }

    ngOnInit() {
        this.UpdateHeadOffice = this.formBuilder.group({
            tenant_id:['', Validators.required],
            head_name: ['', Validators.required],
            head_code:['', Validators.required],
            phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
            mobile_no: ['',[Validators.pattern(this.mobnumPattern)]],
            email_id: ['',[Validators.required,Validators.pattern(this.mailPattern)]],
            address_line_1:[''],
            address_line_2:[''],
            state_id:[''],
            district_id:[''],
            city_id:[''],
            pincode: [''],
            remarks: [''],
            landmark:[''] ,
            id: [''],
            updated_by: ['']
        });
        this.isValidMobile = true;
        this.isValidPincode = true;
        this.isValidPhone = true;
        this.isValidMail = true;

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
        
        this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
            try { 
                if (response.status == 'Success') {
                    this.stateList = response.data;
                } else {
                    this.stateList = [];
                }
            }  catch (err) {}
        }, (err: any) => {});
    
        this.route.params.subscribe(params => {
            this.headOfficeId = params.id;
            this.UpdateHeadOffice.get('id').setValue(this.headOfficeId);
            this.UpdateHeadOffice.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
            let cond = {
                "conditions[id]" : this.headOfficeId 
            }
            this.userservice.getData(cond, "get-head-offices","POST").subscribe((response: any) => {
                this.SubmitbuttonName = "Submit";
                try {
                    if (response.status == 'Success') 
                    {
                        this.UpdateHeadOffice.get('tenant_id').setValue(response.data[0].tenant_id);
                        this.UpdateHeadOffice.get('head_name').setValue(response.data[0].head_name);
                        this.UpdateHeadOffice.get('head_code').setValue(response.data[0].head_code);
                        this.UpdateHeadOffice.get('phone_no').setValue(response.data[0].phone_no);
                        this.UpdateHeadOffice.get('mobile_no').setValue(response.data[0].mobile_no);
                        this.UpdateHeadOffice.get('email_id').setValue(response.data[0].email_id);
                        this.UpdateHeadOffice.get('address_line_1').setValue(response.data[0].address_line_1);
                        this.UpdateHeadOffice.get('address_line_2').setValue(response.data[0].address_line_2);
                        this.UpdateHeadOffice.get('state_id').setValue(response.data[0].state_id);
                        this.UpdateHeadOffice.get('city_id').setValue(response.data[0].city_id);
                        this.UpdateHeadOffice.get('district_id').setValue(response.data[0].district_id);
                        this.UpdateHeadOffice.get('pincode').setValue(response.data[0].pincode);
                        this.UpdateHeadOffice.get('remarks').setValue(response.data[0].remarks);
                        this.UpdateHeadOffice.get('landmark').setValue(response.data[0].landmark);
                        this.setDistrict(response.data[0].state_id,'district');
                        this.setCity(response.data[0].district_id,'city');
                    }
                    else {
                        this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
                    }
                } catch (err) {
                    this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
                }
            }, (err: any) => {
                if (err.status == 401) {
                    this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
                }
                else {
                    this.appService.showAlert("Head Office", "Sorry!! Something went wrong!", "error");
                }
            });
        }); 
    }
}
