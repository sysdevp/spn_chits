import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, RouterStateSnapshot, CanActivate } from '@angular/router';
import { Observable } from 'rxjs';
import { TokenService } from './token.service';

@Injectable({
  providedIn: 'root'
})
export class BeforeLoginService implements CanActivate {
  
  canActivate(Route:ActivatedRouteSnapshot,state:RouterStateSnapshot):boolean| Observable<boolean>| Promise<boolean>
  
  {
return this.token.loggedIn();
  }

  constructor(private token:TokenService) { }
}
