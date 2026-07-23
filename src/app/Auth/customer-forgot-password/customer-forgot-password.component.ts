import { JarwisService } from "../../Services/jarwis.service";
import { TokenService } from "../../Services/token.service";
import { AuthService } from "../../Services/auth.service";
import { Router, ActivatedRoute } from '@angular/router';
import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../app.service';
import { UserService } from '../../Services/user.service';

@Component({
  selector: 'app-customer-forgot-password',
  templateUrl: './customer-forgot-password.component.html',
  styleUrls: [
    '../../../vendor/styles/pages/authentication.scss'
  ]
})

export class CustomerForgotPasswordComponent implements OnInit {
  @ViewChild('email') focus1:ElementRef;
  submitted = false;
  forgotPassword: FormGroup;
  form_message = true;
  loginUrl: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService,private route: ActivatedRoute,private Jarwis: JarwisService, private Token: TokenService,private router: Router,private auth:AuthService) 
  {
    this.loginUrl = this.router.url;
  }
  
  get f() {
    return this.forgotPassword.controls;
  }

  /* goToLogin() {
    this.router.navigate([this.loginUrl]);
  } */

  ngOnInit() {
    this.forgotPassword = this.formBuilder.group({
      email: ['', Validators.required],
      slug: [''],
    });
    this.route.params.subscribe(params => {
      this.forgotPassword.get('slug').setValue(params.slug);
      if(params.slug!='') {
        this.loginUrl = '/customer/'+params.slug;
      }
      else {
        this.loginUrl = '/';
      }
    });
  }

  onSubmit() {
    const result = Object.assign({}, this.forgotPassword.value);
    if (this.forgotPassword.invalid) 
    {
      if(this.f.email.errors) {
        this.focus1.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "forget-password", "JSON").subscribe((response: any) => {
     
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Forgot Password",response.msg, "success");
          /* setTimeout(() => {
            //this.router.navigate(['/']);
            this.router.navigate([this.loginUrl]);
          }, 300); */
        } else {
          this.appService.showAlert("Forgot Password", response.msg, "error");
        }
      } catch (err) {
        this.appService.showAlert("Forgot Password", "Sorry!! Something went wrong!", "error");
      }
    }, (err) => {
     
      if (err.status == 401) {
        this.appService.showAlert("Forgot Password", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Forgot Password", "Sorry!! Something went wrong!", "error");
      }
    });
  }
}

 

