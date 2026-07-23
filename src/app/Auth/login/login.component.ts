import { Component, OnInit, OnDestroy } from '@angular/core';
import { JarwisService } from "../../Services/jarwis.service";
import { TokenService } from "../../Services/token.service";
import { AuthService } from "../../Services/auth.service";
import { Router, ActivatedRoute } from '@angular/router';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})

export class LoginComponent implements OnInit, OnDestroy {
  submitted = false;
  SubmitbuttonName: string;
  disableBtn = false;
  slag: any;
  forgotUrl: any;
  private routeSub: Subscription;
  public form = {
    email: null,
    password: null,
    slag: null
  };
  public error = null;
  http: any;
  year = new Date().getFullYear();

  constructor(private route: ActivatedRoute,private Jarwis: JarwisService, private Token: TokenService,private router: Router,private auth:AuthService) 
  {
    this.SubmitbuttonName = "Sign In";
  }
  
  ngOnInit() 
  { 
    const token = localStorage.getItem('token');
    const tenantId = localStorage.getItem('tenant_id');
    if (token && tenantId) {
      this.router.navigate(['/admin-panel']);
    } else if (token) {
      localStorage.removeItem('token');
    }

    this.routeSub = this.route.params.subscribe(params => {
      this.form.slag = params.slag;
      if((params.slag!='') && (params.slag!=undefined)) {
        this.forgotUrl = '/customer/'+params.slag+'/forgot-password';
      }
      else {
        this.forgotUrl = '/forgot-password';
      }
    });
  }

  ngOnDestroy() {
    if (this.routeSub) {
      this.routeSub.unsubscribe();
    }
  }

  resetLoginButton() {
    this.SubmitbuttonName = "Sign In";
    this.disableBtn = false;
  }

  onSubmit() {  
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    this.disableBtn = true;
    this.error = null;
    let loginUrl = this.router.url;
    const email = (this.form.email || '').trim();
    const password = this.form.password || '';
    if (!email && !password) {
      this.resetLoginButton();
      this.error = "Enter email id and password";
    }
    else if (!email) {
      this.resetLoginButton();
      this.error = "Enter email id";
    }
    else if (!password) {
      this.resetLoginButton();
      this.error = "Enter password";
    }
    else {
      localStorage.setItem("loginUrl", loginUrl);
      this.Jarwis.login(this.form).subscribe(
        data => this.handleResponse(data),
        error => this.handleError(error)
      );
    }
  }
  
  handleResponse(data) {
    const ok = data && (data.success === true || data.success === 1 || data.success === 'true');
    if (!ok || !data.token) {
      this.resetLoginButton();
      this.error = (data && data.message) ? data.message : 'Login failed. Please try again.';
      return;
    }

    try {
      this.Token.handle(data.token);
      localStorage.setItem("tenant_id", data.tenant_id != null ? String(data.tenant_id) : '');
      localStorage.setItem("branch_id", data.branch_id != null ? String(data.branch_id) : '');
      localStorage.setItem("logged_user_id", data.logged_user_id != null ? String(data.logged_user_id) : '');
      localStorage.setItem("logged_employee_id", data.logged_employee_id != null ? String(data.logged_employee_id) : '');
      localStorage.setItem("logged_user_name", data.logged_user_name || '');
      localStorage.setItem("logged_user_type", data.logged_user_type != null ? String(data.logged_user_type) : '');
      localStorage.setItem("database_name", data.database_name || '');
      localStorage.setItem("version", data.version || '');
      localStorage.setItem("sms_enable", data.sms_enable != null ? String(data.sms_enable) : '');
      localStorage.setItem("online_bidding_enable", data.online_bidding_enable != null ? String(data.online_bidding_enable) : '');
      localStorage.setItem("renewaldate", data.renewaldate || '');
      localStorage.setItem("profile_img", data.profile_img || '');
      localStorage.setItem("role_id", data.role_id != null ? String(data.role_id) : '');
      localStorage.setItem("role_type_id", data.role_type_id != null ? String(data.role_type_id) : '');
      localStorage.setItem("role_type_name", data.role_type_name || '');
      localStorage.setItem("company_logo", data.company_logo || '');
      localStorage.setItem("receipt_logo", data.receipt_logo || '');
      localStorage.setItem("favicon", data.favicon || '');
      localStorage.setItem("company_name", data.company_name || '');
      localStorage.setItem("is_tenant_admin", data.is_tenant_admin != null ? String(data.is_tenant_admin) : '0');
      localStorage.setItem("role_permission", JSON.stringify(data.role_permission || []));
    } catch (e) {
      this.resetLoginButton();
      this.error = 'Could not save login session. Please try again.';
      return;
    }

    this.auth.changeauthStatus(true);
    this.router.navigate(['/admin-panel']).then(() => {
      this.resetLoginButton();
    });
  }
   
  handleError(error) { 
    this.resetLoginButton();
    const errBody = error && error.error ? error.error : null;
    this.error = (errBody && (errBody.message || errBody.msg)) || 'Login failed. Please try again.';
  }
}

