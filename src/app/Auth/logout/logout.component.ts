import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../environments/environment';

@Component({
  selector: 'app-logout',
  templateUrl: './logout.component.html',
  styles: []
})
export class LogoutComponent implements OnInit {

  constructor(private router: Router, private http: HttpClient) { }

  ngOnInit() {
    const token = localStorage.getItem('token');
    const loginUrl = localStorage.getItem('loginUrl');
    const logoutUrl = `${environment.API}logout`;

    if (token) {
      const headers = new HttpHeaders({
        Authorization: `Bearer ${token}`
      });

      this.http.post(logoutUrl, {}, { headers }).subscribe({
        next: (response) => {
          console.log('Logged out from backend', response);
          this.clearStorage();
          this.redirectToLogin(loginUrl);
        },
        error: (error) => {
          console.error('Backend logout failed', error);
          this.clearStorage(); // Still clear storage even if logout fails
          this.redirectToLogin(loginUrl);
        }
      });
    } else {
      this.clearStorage();
      this.redirectToLogin(loginUrl);
    }
  }

  clearStorage() {
    const keys = [
      'token', 'tenant_id', 'branch_id', 'logged_user_id', 'logged_employee_id',
      'logged_user_type', 'profile_img', 'role_id', 'role_type_id', 'role_type_name',
      'logged_user_name', 'logged_role_name', 'logged_tenant_name', 'tenant_det',
      'role_permission', 'company_logo', 'receipt_logo', 'favicon', 'company_name',
      'is_tenant_admin', 'loginUrl', 'database_name'
    ];

    keys.forEach(key => localStorage.removeItem(key));
  }

  redirectToLogin(loginUrl: string | null) {
    if (loginUrl == null) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate([loginUrl]);
    }
  }
}
