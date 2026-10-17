import {
  Component,
  Input,
  ChangeDetectionStrategy,
  AfterViewInit,
  HostBinding
} from '@angular/core';
import { Router } from '@angular/router';
import { AppService } from '../../app.service';
import { LayoutService } from '../layout.service';
import { MenuPermissionService } from '../../Services/menu-permission.service';

@Component({
  selector: 'app-layout-sidenav',
  templateUrl: './layout-sidenav.component.html',
  styles: [`
    :host { display: block; }
  `],
  changeDetection: ChangeDetectionStrategy.OnPush,
})

export class LayoutSidenavComponent implements AfterViewInit {
   @Input() orientation = 'vertical';

  @HostBinding('class.layout-sidenav') hostClassVertical = false;
  @HostBinding('class.layout-sidenav-horizontal') hostClassHorizontal = false;
  @HostBinding('class.flex-grow-0') hostClassFlex = false;

  companyLogo = 'assets/img/spn-logo.png';

  // visibility flags for parent modules
  showMaster = false;
  showEmployeeManager = false;
  showCustomerManager = false;
  showTransactionManager = false;
  showOtherTransactionManager = false;
  showReportsManager = false;
  showOtherLoanManager = false;

  sms: any;
  online_bidding_enable: any;
  logged_user_type: any;

  constructor(
    private router: Router,
    private appService: AppService,
    private layoutService: LayoutService,
    private menuPermission: MenuPermissionService
  ) {
    // ✅ Orientation
    this.hostClassVertical = this.orientation !== 'horizontal';
    this.hostClassHorizontal = !this.hostClassVertical;
    this.hostClassFlex = this.hostClassHorizontal;

    // ✅ Company logo — prefer new SPN brand asset when tenant logo missing
    const storedLogo = localStorage.getItem('company_logo');
    if (storedLogo && storedLogo !== '' && storedLogo !== 'null') {
      this.companyLogo = storedLogo;
    } else {
      this.companyLogo = 'assets/img/spn-logo.png';
    }

    this.sms = localStorage.getItem('sms_enable');
    this.online_bidding_enable = localStorage.getItem('online_bidding_enable');
    this.logged_user_type = localStorage.getItem('logged_user_type');
  }

  ngAfterViewInit() {
    // Safari bug fix
    if (localStorage.getItem('token') == null) {
      this.router.navigate(['/logout']);
      return;
    }
    this.layoutService._redrawLayoutSidenav();
  }

  /** ✅ Background classes for sidenav container */
  getClasses() {
    let bg = this.appService.layoutSidenavBg;

    if (
      this.orientation === 'horizontal' &&
      (bg.indexOf(' sidenav-dark') !== -1 || bg.indexOf(' sidenav-light') !== -1)
    ) {
      bg = bg
        .replace(' sidenav-dark', '')
        .replace(' sidenav-light', '')
        .replace('-darker', '')
        .replace('-dark', '');
    }

    return `${this.orientation === 'horizontal' ? 'container-p-x ' : ''} bg-${bg}`;
  }

  /** ✅ For router-active highlighting */
  isActive(url: string) {
    return this.router.isActive(url, true);
  }

  /** ✅ Used in menus */
  isMenuActive(url: string) {
    return this.router.isActive(url, false);
  }

  /** ✅ Expands nested menu if active */
  isMenuOpen(url: string) {
    return this.router.isActive(url, false) && this.orientation !== 'horizontal';
  }

  /** Expand a main menu when the current page is one of its routes */
  isGroupOpen(routes: string[]) {
    if (this.orientation === 'horizontal') {
      return false;
    }
    const path = (this.router.url || '').split('?')[0].replace(/^\/admin-panel\//, '');
    return routes.some(r => path === r || path.indexOf(r + '/') === 0);
  }

  /** True when the user can see at least one [menuId, parentMenuId] pair */
  hasAny(pairs: number[][]): boolean {
    return pairs.some(p => this.isMenuHide(p[0], p[1]));
  }

  toggleSidenav() {
    this.layoutService.toggleCollapsed();
  }

  /** Show sidenav link when user has view permission (or admin bypass). */
  isMenuHide(menuId: number, parentMenuId: number): boolean {
    this.menuPermission.refreshPermissions();
    if (this.menuPermission.canBypassMenuCheck(menuId)) {
      if (menuId === 52 || menuId === 54) {
        return false;
      }
      return true;
    }
    return this.menuPermission.canShowMenu(menuId, parentMenuId);
  }

  /** Page-level access id from spn-access.config (Security Permissions screen). */
  can(pageId: number): boolean {
    this.menuPermission.refreshPermissions();
    return this.menuPermission.canPage(pageId);
  }

  canAny(pageIds: number[]): boolean {
    this.menuPermission.refreshPermissions();
    return this.menuPermission.canAnyPage(pageIds);
  }

  canSection(key: string): boolean {
    this.menuPermission.refreshPermissions();
    return this.menuPermission.canSection(key);
  }

  /** Route fallback kept only for roles not yet saved with page-level access. */
  legacyRoute(route: string): boolean {
    this.menuPermission.refreshPermissions();
    return !this.menuPermission.hasPageAccessModule() && this.menuPermission.hasRouteAccess(route);
  }

  /** Route-based permission (e.g. SPN Approval before menu 152 exists in DB). */
  isRouteVisible(route: string): boolean {
    this.menuPermission.refreshPermissions();
    return this.menuPermission.hasRouteAccess(route);
  }
}
