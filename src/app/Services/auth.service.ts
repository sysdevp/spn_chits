import { Injectable } from '@angular/core';
import { TokenService } from './token.service';
import { BehaviorSubject } from 'rxjs/internal/BehaviorSubject';

@Injectable({
  providedIn: 'root'
})

export class AuthService {
  
  private loggedIn= new BehaviorSubject <boolean>(this.Token.loggedIn())
  authStatus=this.loggedIn.asObservable();

  constructor(private Token: TokenService) { }

  changeauthStatus(value:boolean)
  {
    this.loggedIn.next(value);
  }
}
