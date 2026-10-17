import { Component, Input, HostBinding, OnInit, OnDestroy } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { AppService } from '../../app.service';
import { LayoutService } from '../layout.service';
import { Title } from '@angular/platform-browser';
import { MenuPermissionService } from '../../Services/menu-permission.service';
import { UserService } from '../../Services/user.service';

@Component({
  selector: 'app-layout-navbar',
  templateUrl: './layout-navbar.component.html',
  styles: [`
    :host { display: block; }
    .spn-internal-header {
      display: flex;
      align-items: center;
      gap: 12px;
      min-height: 72px;
      padding: 10px 16px;
      background: linear-gradient(90deg, #ffffff 0%, #eef6ff 48%, #f8edd4 100%) !important;
      box-shadow: 0 1px 0 rgba(226, 232, 240, .9);
    }
    .spn-menu-btn {
      width: 40px;
      height: 40px;
      border: 0;
      border-radius: 10px;
      background: transparent;
      color: #061A35;
      font-size: 22px;
      cursor: pointer;
    }
    .spn-header-search { flex: 1 1 280px; min-width: 180px; }
    .spn-branch {
      display: flex;
      align-items: center;
      gap: 6px;
      height: 44px;
      padding: 0 12px;
      background: #fff;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(7, 29, 58, .06);
      color: #0F172A;
      min-width: 160px;
    }
    .spn-branch i { color: #006B3C; }
    .spn-branch select {
      border: 0;
      background: transparent;
      color: #0F172A;
      font-weight: 600;
      width: 140px;
      outline: none;
    }
    .spn-header-spacer { flex: 1 1 auto; }
    .spn-shortcuts {
      display: flex;
      align-items: center;
      list-style: none;
      margin: 0;
      padding: 0;
      gap: 4px;
    }
    .spn-shortcuts .nav-link {
      color: #061A35;
      font-size: 13px;
      font-weight: 600;
      padding: 6px 8px;
    }
    .spn-shortcuts .dropdown-menu a {
      display: block;
      padding: 6px 12px;
      color: #0F172A;
    }
    .spn-date {
      display: flex;
      align-items: center;
      gap: 8px;
      height: 44px;
      padding: 4px 12px;
      background: #fff;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      box-shadow: 0 4px 14px rgba(7, 29, 58, .06);
      color: #0F172A;
      line-height: 1.15;
    }
    .spn-date i { color: #006B3C; }
    .spn-date b { display: block; font-size: 13px; }
    .spn-date small { color: #64748B; font-size: 11px; }
    .spn-bell {
      position: relative;
      width: 42px;
      height: 42px;
      border: 0;
      border-radius: 12px;
      background: #fff;
      color: #061A35;
      box-shadow: 0 4px 14px rgba(7, 29, 58, .06);
      cursor: pointer;
    }
    .spn-profile {
      display: flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 0;
      color: #0F172A;
      cursor: pointer;
      padding: 0;
    }
    .spn-profile img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .spn-profile b { display: block; font-size: 13px; line-height: 1.1; }
    .spn-profile small { color: #64748B; font-size: 11px; }
    .spn-header-right { display: flex; align-items: center; gap: 10px; margin-left: auto; flex: 0 0 auto; }
    .spn-bell { display: flex; align-items: center; justify-content: center; }
    .spn-bell::after { display: none !important; }
    .spn-bell-count { position: absolute; top: -5px; right: -5px; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 10px; background: #DC2626; color: #fff; font-size: 11px; font-weight: 800; line-height: 19px; text-align: center; }
    .spn-notif { width: 340px; max-height: 480px; overflow: auto; padding: 0; border-radius: 14px; border: 1px solid #E2E8F0; box-shadow: 0 18px 40px rgba(7, 29, 58, .18); }
    .spn-notif-head { display: flex; justify-content: space-between; padding: 12px 14px; font-weight: 800; color: #004D2A; border-bottom: 1px solid #eef2f7; }
    .spn-notif-sec { padding: 8px 14px 4px; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #64748B; }
    .spn-notif-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 14px; color: #0F172A !important; text-decoration: none !important; }
    .spn-notif-item:hover { background: #F3FFF8; }
    .spn-notif-item i { color: #006B3C; margin-top: 3px; width: 14px; }
    .spn-notif-item span { flex: 1; min-width: 0; }
    .spn-notif-item b { display: block; font-size: 13px; font-weight: 700; }
    .spn-notif-item small { display: block; color: #64748B; font-size: 11px; }
    .spn-notif-item em { font-style: normal; background: #FFF0F4; color: #b91c1c; border-radius: 10px; padding: 1px 8px; font-weight: 800; font-size: 12px; }
    .spn-notif-bar { display: block; height: 5px; border-radius: 4px; background: #eef3f0; margin-top: 4px; overflow: hidden; }
    .spn-notif-bar span { display: block; height: 100%; background: linear-gradient(90deg, #18A957, #006B3C); }
    .spn-notif-empty { padding: 18px 14px; text-align: center; color: #64748B; }
    .spn-notif-foot { display: block; padding: 10px 14px; border-top: 1px solid #eef2f7; font-weight: 700; color: #006B3C !important; }
    @media (max-width: 1100px) {
      .spn-header-search { flex-basis: 200px; }
    }    @media (max-width: 900px) {
      .spn-date, .spn-branch { display: none; }
      .spn-header-search { min-width: 0; }
    }
  `],
})
export class LayoutNavbarComponent implements OnInit, OnDestroy {
  isExpanded = false;
  isRTL: boolean;
  profileImg = 'assets/img/spn-logo.png';
  loggedUserName = '';
  favicon = 'assets/img/favicon-spn.png';

  @Input() sidenavToggle = true;

  @HostBinding('class.layout-navbar') hostClassMain = true;
  @HostBinding('class.spn-internal-header') hostInternalHeader = true;
  companyLogo = 'assets/img/spn-logo.png';
  version: string;
  logged_user_type: any;

  notif: any = { total: 0, tasks: [], targets: [], approvals: [] };
  private notifTimer: any;
  isDashboard = false;
  headerDate = '';
  headerDay = '';
  roleLabel = 'Head Office';
  headerBranchId = '';
  headerBranches: any[] = [];

  constructor(
    private titleService: Title,
    private appService: AppService,
    private layoutService: LayoutService,
    private menuPermission: MenuPermissionService,
    private router: Router,
    private userservice: UserService
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
    const role = localStorage.getItem('role_type_name');
    const company = localStorage.getItem('company_name');
    this.roleLabel = (role && role.trim()) || (company && company.trim()) || 'Head Office';
    this.headerBranchId = localStorage.getItem('branch_id') || '0';
    if (!this.headerBranchId || this.headerBranchId === 'null') {
      this.headerBranchId = '0';
    }
    this.syncDashboardFlag(this.router.url);
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.syncDashboardFlag(event.urlAfterRedirects || event.url);
      }
    });
  }

  currentBg() {
    return `bg-${this.appService.layoutNavbarBg}`;
  }

  toggleSidenav() {
    this.layoutService.toggleCollapsed();
  }

  ngOnInit() {
    this.version = localStorage.getItem('version');
    const now = new Date();
    this.headerDate = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    this.headerDay = now.toLocaleDateString('en-GB', { weekday: 'long' });
    this.loadHeaderBranches();
    this.loadNotifications();
    this.notifTimer = setInterval(() => this.loadNotifications(), 120000);
  }

  ngOnDestroy() {
    clearInterval(this.notifTimer);
  }

  loadNotifications() {
    const branch = localStorage.getItem('branch_id');
    this.userservice.getData({
      tenant_id: localStorage.getItem('tenant_id') || '',
      branch_id: branch && branch !== 'null' ? branch : '',
      employee_id: localStorage.getItem('logged_employee_id') || ''
    }, 'header-notifications', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.notif = res;
      }
    }, () => {});
  }

  private syncDashboardFlag(url: string) {
    const path = (url || '').split('?')[0].replace(/\/+$/, '');
    this.isDashboard = /\/dashboard$/.test(path) || /\/admin-panel$/.test(path);
  }

  loadHeaderBranches() {
    const tenant = localStorage.getItem('tenant_id');
    if (!tenant) {
      return;
    }
    this.userservice.getData({
      'conditions[tenant_id]': tenant,
      'conditions[status]': 1
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.headerBranches = res && res.status === 'Success' ? (res.data || []) : [];
      const match = this.headerBranches.find((row) => String(row.id) === String(this.headerBranchId));
      if (match && match.branch_name && String(this.headerBranchId) !== '0') {
        this.roleLabel = match.branch_name;
      }
    });
  }

  onHeaderBranch(event: any) {
    this.headerBranchId = event && event.target ? String(event.target.value || '0') : '0';
  }
}
