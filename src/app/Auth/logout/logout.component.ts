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

    this.clearStorage();

    if (token) {
      const headers = new HttpHeaders({ Authorization: `Bearer ${token}` });
      this.http.post(`${environment.API}logout`, { token: token }, { headers }).subscribe(
        () => {},
        () => {}
      );
    }
    this.redirectToLogin(loginUrl);
  }

  clearStorage() {
    const keep = Object.keys(localStorage).filter((k) => /^(themeSettings|appwork)/i.test(k));
    const saved = keep.map((k) => [k, localStorage.getItem(k)]);
    localStorage.clear();
    sessionStorage.clear();
    saved.forEach(([k, v]) => localStorage.setItem(k, v));
  }

  redirectToLogin(loginUrl: string | null) {
    const target = loginUrl && loginUrl !== '/logout' ? loginUrl : '/';
    this.router.navigateByUrl(target, { replaceUrl: true });
  }
}
