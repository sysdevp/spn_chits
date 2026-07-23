import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-city',
  templateUrl: './add-city.component.html',
  styles: []
})

export class AddCityComponent implements OnInit {
  AddCity: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  districtList: any;
  pincodePattern = "^[0-9]{6}$";

  @ViewChild('district_id') focus1:ElementRef;
  @ViewChild('city_name') focus2:ElementRef;
  @ViewChild('pincode') focus3:ElementRef;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddCity.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCity.value);
    if (this.AddCity.invalid) {
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
    this.userservice.getData(result, "store-city", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("City",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-city']);
          }, 300);
        } else {
          this.appService.showAlert("City", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
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
    this.AddCity = this.formBuilder.group({
      district_id: ['', Validators.required],
      city_name:['', Validators.required],
      pincode: ['',[Validators.required,Validators.pattern(this.pincodePattern)]],
      created_by: [''],
    });
    this.AddCity.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.userservice.getData("", "list-districts", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.districtList = response.data;
        } else {
          this.districtList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
}
