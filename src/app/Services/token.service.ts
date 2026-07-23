import { Injectable } from "@angular/core";

@Injectable({
  providedIn: "root"
})
export class TokenService {
  constructor() {}

  private iss = {
    
  };

  handle(token) {
    this.set(token);
  }

  set(token) {
    localStorage.setItem("token", token);
  }

  get() {
    return localStorage.getItem("token");
  }

  remove() {
    localStorage.removeItem("token");
  }

  isValid() {
    const token = this.get();
    return !!(token && token !== 'undefined' && token !== 'null');
  }

  payload(token) {
    const payload = token.split(".")[1];
  //  return this.decode(payload);
  }

  decode(payload) {
    //return JSON.parse(atob(payload));
  }

  loggedIn() {
    return this.isValid();
  }
}
