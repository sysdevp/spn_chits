import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-city',
  templateUrl: './update-city.component.html',
  styles: []
})

export class UpdateCityComponent implements OnInit {
  @ViewChild('district_id') focus1:ElementRef;
  @ViewChild('city_name') focus2:ElementRef;
  @ViewChild('pincode') focus3:ElementRef;

  UpdateCity: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  cityId: any;
  districtList: any;
  pincodePattern = "^[0-9]{6}$";

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCity.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCity.value);
    if (this.UpdateCity.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.dist_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.city_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.pincode.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-city","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("City", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-city']);
          }, 3000);
        } else {
          this.appService.showAlert("City", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateCity = this.formBuilder.group({
      district_id: ['', Validators.required],
      city_name:['', Validators.required],
      pincode: ['',[Validators.required,Validators.pattern(this.pincodePattern)]],
      id: [''],
      updated_by: [''],
    });

    this.UpdateCity.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-districts", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.districtList = response.data;
        } else {
          this.districtList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.route.params.subscribe(params => {
      this.cityId = params.id;
      this.UpdateCity.get('id').setValue(this.cityId);
      let cond = {
        "conditions[id]" : this.cityId 
      }
      this.userservice.getData(cond, "get-cities","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCity.get('district_id').setValue(response.data[0].district_id);
            this.UpdateCity.get('city_name').setValue(response.data[0].city_name);
            this.UpdateCity.get('pincode').setValue(response.data[0].pincode);
          }
          else {
            this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("City", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
