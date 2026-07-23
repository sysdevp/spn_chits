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
import { UserService } from '../../Services/user.service';
import { MenuPermissionService } from '../../Services/menu-permission.service';

@Component({
  selector: 'app-layout-sidenav',
  templateUrl: './layout-sidenav.component.html',
  styles: [':host { display: block; }'],
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
    private userservice: UserService,
    private menuPermission: MenuPermissionService
  ) {
    // ✅ Orientation
    this.hostClassVertical = this.orientation !== 'horizontal';
    this.hostClassHorizontal = !this.hostClassVertical;
    this.hostClassFlex = this.hostClassHorizontal;

    // ✅ Company logo
    if (
      localStorage.getItem('company_logo') &&
      localStorage.getItem('company_logo') !== ''
    ) {
      this.companyLogo = localStorage.getItem('company_logo');
    }

    this.sms = localStorage.getItem('sms_enable');
    this.online_bidding_enable = localStorage.getItem('online_bidding_enable');
    this.logged_user_type = localStorage.getItem('logged_user_type');
  }

  ngAfterViewInit() {
    // Safari bugfix
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
    if (this.menuPermission.canShowMenu(menuId, parentMenuId)) {
      if (parentMenuId == 2 || menuId == 2) this.showMaster = true;
      if (parentMenuId == 34 || menuId == 34) this.showEmployeeManager = true;
      if (parentMenuId == 28 || menuId == 28) this.showCustomerManager = true;
      if (parentMenuId == 37 || menuId == 37) this.showTransactionManager = true;
      if (parentMenuId == 54 || menuId == 54) this.showOtherTransactionManager = true;
      if (parentMenuId == 42 || menuId == 42) this.showReportsManager = true;
      if (parentMenuId == 85 || menuId == 85) this.showOtherLoanManager = true;
      return true;
    }
    if (!this.menuPermission.hasTenantScope()) {
      return true;
    }
    return false;
  }

  /** Route-based permission (e.g. SPN Approval before menu 152 exists in DB). */
  isRouteVisible(route: string): boolean {
    this.menuPermission.refreshPermissions();
    return this.menuPermission.hasRouteAccess(route);
  }
}