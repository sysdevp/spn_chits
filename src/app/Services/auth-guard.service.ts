import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { MenuPermissionService } from './menu-permission.service';
import { MenuPermType } from '../config/app-menu.config';

@Injectable({
  providedIn: 'root',
})
export class AuthGuardService implements CanActivate {
  constructor(private router: Router, private menuPermission: MenuPermissionService) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    if (localStorage.getItem('token') == null) {
      this.router.navigate(['/logout'], { queryParams: { returnUrl: state.url } });
      return false;
    }

    this.menuPermission.refreshPermissions();

    const routeKey = route.data['routeKey'] as string;
    if (routeKey) {
      const permType = route.data['permType'] as MenuPermType | undefined;
      if (this.menuPermission.hasRouteAccess(routeKey, permType)) {
        return true;
      }
      this.router.navigate(['admin-panel/access-denied']);
      return false;
    }

    const menuId = route.data['menuId'];
    const parentMenuId = route.data['parentMenuId'];
    const permType = (route.data['permType'] || '') as MenuPermType | '';

    if (menuId == null || parentMenuId == null) {
      return true;
    }

    if (!this.menuPermission.hasTenantScope()) {
      return true;
    }

    if (this.menuPermission.canActivateRoute(menuId, parentMenuId, permType)) {
      return true;
    }

    this.router.navigate(['admin-panel/access-denied']);
    return false;
  }
}
