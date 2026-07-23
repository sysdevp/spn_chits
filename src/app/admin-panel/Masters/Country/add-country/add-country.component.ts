import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-country',
  templateUrl: './add-country.component.html',
  styles: []
})
export class AddCountryComponent implements OnInit {
  AddCountry: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  districtList: any;
  pincodePattern = "^[0-9]{6}$";

  @ViewChild('country_name') focus1:ElementRef;
  @ViewChild('remarks') focus2:ElementRef;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddCountry.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCountry.value);
    if (this.AddCountry.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.country_name.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-country", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Country",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-country']);
          }, 300);
        } else {
          this.appService.showAlert("Country", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCountry = this.formBuilder.group({
      country_name: ['', Validators.required],
      remarks:[''],
      created_by: [''],
    });
    this.AddCountry.get('created_by').setValue(localStorage.getItem('logged_user_id'));

  }
}
