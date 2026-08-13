import { Component, Input, HostBinding } from '@angular/core';
import { AppService } from '../../app.service';
import { LayoutService } from '../layout.service';
import { Title } from '@angular/platform-browser';
import { MenuPermissionService } from '../../Services/menu-permission.service';
import { HEADER_MENU_ITEMS, HeaderMenuItem } from '../../config/app-menu.config';

@Component({
  selector: 'app-layout-navbar',
  templateUrl: './layout-navbar.component.html',
  styles: [':host { display: block; }'],
})
export class LayoutNavbarComponent {
  isExpanded = false;
  isRTL: boolean;
  profileImg = 'assets/img/spn-logo.png';
  loggedUserName = '';
  favicon = 'assets/img/favicon-spn.png';

  @Input() sidenavToggle = true;

  @HostBinding('class.layout-navbar') hostClassMain = true;
  companyLogo = 'assets/img/spn-logo.png';
  version: string;
  logged_user_type: any;

  showSubscriberMenu = false;
  showCollectionMenu = false;
  showPaymentMenu = false;
  showAccountsMenu = false;

  subscriberItems: HeaderMenuItem[] = [];
  collectionItems: HeaderMenuItem[] = [];
  paymentItems: HeaderMenuItem[] = [];
  accountsItems: HeaderMenuItem[] = [];

  constructor(
    private titleService: Title,
    private appService: AppService,
    private layoutService: LayoutService,
    private menuPermission: MenuPermissionService
  ) {
    this.isRTL = appService.isRTL;
    this.profileImg = localStorage.getItem('profile_img');
    if (localStorage.getItem('profile_img') != null && localStorage.getItem('profile_img') != '') {
      this.profileImg = localStorage.getItem('profile_img');
    } else if (localStorage.getItem('company_logo') != null && localStorage.getItem('company_logo') != '') {
      this.profileImg = localStorage.getItem('company_logo');
    }
    if (localStorage.getItem('logged_user_name') != null && localStorage.getItem('logged_user_name') != '') {
      this.logged_user_type = localStorage.getItem('logged_user_type');
      this.loggedUserName = localStorage.getItem('logged_user_name');
    }
    if (localStorage.getItem('company_name') != null && localStorage.getItem('company_name') != '') {
      this.titleService.setTitle(localStorage.getItem('company_name'));
    }
    if (localStorage.getItem('favicon') != null && localStorage.getItem('favicon') != '') {
      const faviconEl = document.getElementById('favicon') as HTMLLinkElement;
      if (faviconEl) {
        faviconEl.setAttribute('href', localStorage.getItem('favicon'));
      }
    }
  }

  currentBg() {
    return `bg-${this.appService.layoutNavbarBg}`;
  }

  toggleSidenav() {
    this.layoutService.toggleCollapsed();
  }

  ngOnInit() {
    this.version = localStorage.getItem('version');
    this.applyHeaderPermissions();
  }

  private applyHeaderPermissions(): void {
    this.menuPermission.refreshPermissions();
    this.subscriberItems = HEADER_MENU_ITEMS.filter(
      (item) => item.group === 'subscriber' && this.menuPermission.hasHeaderAccess(item.route)
    );
    this.collectionItems = HEADER_MENU_ITEMS.filter(
      (item) => item.group === 'collection' && this.menuPermission.hasHeaderAccess(item.route)
    );
    this.paymentItems = HEADER_MENU_ITEMS.filter(
      (item) => item.group === 'payment' && this.menuPermission.hasHeaderAccess(item.route)
    );
    this.accountsItems = HEADER_MENU_ITEMS.filter(
      (item) => item.group === 'accounts' && this.menuPermission.hasHeaderAccess(item.route)
    );

    this.showSubscriberMenu = this.subscriberItems.length > 0;
    this.showCollectionMenu = this.collectionItems.length > 0;
    this.showPaymentMenu = this.paymentItems.length > 0;
    this.showAccountsMenu = this.accountsItems.length > 0;
  }
}
