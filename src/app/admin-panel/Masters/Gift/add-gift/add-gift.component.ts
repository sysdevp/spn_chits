import { Component,OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-gift',
  templateUrl: './add-gift.component.html',
  styles: []
})

export class AddGiftComponent implements OnInit {
  AddCity: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  districtList: any;
  pincodePattern = "^[0-9]{6}$";

  @ViewChild('scheme_id') focus1:ElementRef;
  @ViewChild('gift_name') focus2:ElementRef;
  @ViewChild('price') focus3:ElementRef;
  @ViewChild('quantity') focus4:ElementRef;
  
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
      if(this.f.scheme_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.gift_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.price.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.quantity.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-gift", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Gift",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-gift']);
          }, 300);
        } else {
          this.appService.showAlert("Gift", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Gift", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Gift", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Gift", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCity = this.formBuilder.group({
      scheme_id: ['', Validators.required],
      gift_name:['', Validators.required],
      price: ['',Validators.required],
      quantity: ['',Validators.required],
      created_by: [''],
    });
    this.AddCity.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.userservice.getData("", "list-schemes", "POST").subscribe((response: any) => {
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
