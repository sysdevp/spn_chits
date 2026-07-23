import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-country',
  templateUrl: './update-country.component.html',
  styles: []
})
export class UpdateCountryComponent implements OnInit {
  @ViewChild('country_name') focus1:ElementRef;
  @ViewChild('remarks') focus2:ElementRef;

  UpdateCountry: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  countryId: any;
  districtList: any;
  pincodePattern = "^[0-9]{6}$";

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCountry.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCountry.value);
    if (this.UpdateCountry.invalid) {
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
    this.userservice.getData(result,"update-country","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Country", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-country']);
          }, 3000);
        } else {
          this.appService.showAlert("Country", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
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
    this.UpdateCountry = this.formBuilder.group({
      country_name:['', Validators.required],
      remarks: [''],
      id: [''],
      updated_by: [''],
    });

    this.UpdateCountry.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
   

    this.route.params.subscribe(params => {
      this.countryId = params.id;
      this.UpdateCountry.get('id').setValue(this.countryId);
      let cond = {
        "conditions[id]" : this.countryId 
      }
      this.userservice.getData(cond, "get-countries","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCountry.get('country_name').setValue(response.data[0].country_name);
            this.UpdateCountry.get('remarks').setValue(response.data[0].remarks);
          }
          else {
            this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Country", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
