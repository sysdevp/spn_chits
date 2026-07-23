
import { JarwisService } from "../../Services/jarwis.service";
import { TokenService } from "../../Services/token.service";
import { AuthService } from "../../Services/auth.service";
import { Router, ActivatedRoute } from '@angular/router';
import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../app.service';
import { UserService } from '../../Services/user.service';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['../login/login.component.scss']
})
export class ForgotPasswordComponent implements OnInit {
  @ViewChild('email') focus1:ElementRef;
  submitted = false;
  slug: any;
  forgotPassword: FormGroup;
  form_message = true;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService,private route: ActivatedRoute,private Jarwis: JarwisService, private Token: TokenService,private router: Router,private auth:AuthService) 
  {
    
  }
  
  get f() {
    return this.forgotPassword.controls;
  }

  ngOnInit() {
    this.forgotPassword = this.formBuilder.group({
      email: ['', Validators.required],
      slug: [''],
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
          setTimeout(() => {
            this.router.navigate(['/']);
          }, 300);
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

 

