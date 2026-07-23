import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-district',
  templateUrl: './add-district.component.html',
  styles: []
})
export class AddDistrictComponent implements OnInit {
  AddDistrict: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  stateList: any;
  pincodePattern = "^[0-9]{6}$";

  @ViewChild('state_id') focus1:ElementRef;
  @ViewChild('district_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
      this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddDistrict.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddDistrict.value);
    if (this.AddDistrict.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.state_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.district_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-district", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("District",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-district']);
          }, 300);
        } else {
          this.appService.showAlert("District", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddDistrict = this.formBuilder.group({
      state_id: ['', Validators.required],
      district_name:['', Validators.required],
      remarks: [''],
      created_by: [''],
    });
    this.AddDistrict.get('created_by').setValue(localStorage.getItem('logged_user_id'));

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
