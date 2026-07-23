import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-district',
  templateUrl: './update-district.component.html',
  styles: []
})
export class UpdateDistrictComponent implements OnInit {
  @ViewChild('state_id') focus1:ElementRef;
  @ViewChild('district_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  UpdateDistrict: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  cityId: any;
  stateList: any;
  pincodePattern = "^[0-9]{6}$";

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateDistrict.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateDistrict.value);
    if (this.UpdateDistrict.invalid) {
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
    this.userservice.getData(result,"update-district","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("District", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-district']);
          }, 3000);
        } else {
          this.appService.showAlert("District", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
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
    this.UpdateDistrict = this.formBuilder.group({
      state_id: ['', Validators.required],
      district_name:['', Validators.required],
      remarks: [''],
      id: [''],
      updated_by: [''],
    });

    this.UpdateDistrict.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
    this.userservice.getData("", "list-states", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.stateList = response.data;
        } else {
          this.stateList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.route.params.subscribe(params => {
      this.cityId = params.id;
      this.UpdateDistrict.get('id').setValue(this.cityId);
      let cond = {
        "conditions[id]" : this.cityId 
      }
      this.userservice.getData(cond, "get-districts","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateDistrict.get('state_id').setValue(response.data[0].state_id);
            this.UpdateDistrict.get('district_name').setValue(response.data[0].district_name);
            this.UpdateDistrict.get('remarks').setValue(response.data[0].remarks);
          }
          else {
            this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("District", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
