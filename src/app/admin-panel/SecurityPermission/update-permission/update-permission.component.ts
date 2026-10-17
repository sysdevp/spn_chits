import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AppService } from '../../../app.service';
import { UserService } from '../../../Services/user.service';
import {
  AccessState,
  buildRoleAccess,
  readRoleAccess,
  SPN_ACCESS_MODULE_ID,
} from '../../../config/spn-access.config';

@Component({
  selector: 'app-update-permission',
  templateUrl: './update-permission.component.html',
  styles: []
})
export class UpdatePermissionComponent implements OnInit {
  isSuperAdmin = false;
  companyList: any[] = [];
  roleList: any[] = [];
  tenantId = '';
  roleId = '';
  copyRoleId = '';
  permissionId: any = '';
  state: AccessState = null;
  loading = false;
  legacyFormat = false;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Update';

  constructor(
    private appService: AppService,
    private userservice: UserService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    const preselect = this.route.snapshot.queryParamMap.get('role') || '';
    if (tenantId) {
      this.tenantId = tenantId;
      this.loadRoles(preselect);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status == 'Success' ? response.data : [];
      }, () => {});
    }
  }

  companyChg() {
    this.roleId = '';
    this.state = null;
    this.loadRoles('');
  }

  loadRoles(preselect: string) {
    if (!this.tenantId) {
      this.roleList = [];
      return;
    }
    const cond = {
      'conditions[tenant_id]': this.tenantId,
      for_edit: 1,
      role_id: localStorage.getItem('role_id'),
      is_super_admin: this.isSuperAdmin,
    };
    this.userservice.getData(cond, 'get-roles-for-permission', 'POST').subscribe((response: any) => {
      this.roleList = response && response.status == 'Success' ? response.data : [];
      if (preselect && this.roleList.some((r) => String(r.id) === String(preselect))) {
        this.roleId = preselect;
        this.loadRole();
      }
    }, () => {});
  }

  loadRole() {
    this.state = null;
    this.permissionId = '';
    if (!this.roleId) {
      return;
    }
    this.loading = true;
    this.fetchAccess(this.roleId, (row) => {
      this.loading = false;
      this.permissionId = row ? row.id : '';
      const access = (row && row.role_access) || [];
      this.legacyFormat = !!row && !access.some((m: any) => Number(m.menu_id) === SPN_ACCESS_MODULE_ID);
      this.state = readRoleAccess(access);
    });
  }

  copyFromRole() {
    if (!this.copyRoleId || !this.state) {
      return;
    }
    this.fetchAccess(this.copyRoleId, (row) => {
      if (row) {
        this.state = readRoleAccess(row.role_access || []);
        this.appService.showAlert('Security Permission', 'Access copied — adjust and click Update.', 'success');
      }
    });
  }

  private fetchAccess(roleId: string, done: (row: any) => void) {
    const cond = { 'conditions[tenant_id]': this.tenantId, 'conditions[role_id]': roleId };
    this.userservice.getData(cond, 'get-permissions', 'POST').subscribe((response: any) => {
      done(response && response.status == 'Success' && response.data && response.data[0] ? response.data[0] : null);
    }, () => {
      this.loading = false;
      done(null);
    });
  }

  roleName(id: any): string {
    const r = this.roleList.find((x) => String(x.id) === String(id));
    return r ? r.role_name : '';
  }

  onSubmit() {
    this.submitted = true;
    if (!this.tenantId || !this.roleId || !this.permissionId || !this.state) {
      return;
    }
    this.disableBtn = true;
    this.SubmitbuttonName = 'Loading...';
    const payload = {
      id: this.permissionId,
      tenant_id: this.tenantId,
      role_id: this.roleId,
      updated_by: localStorage.getItem('logged_user_id'),
      Permissions: buildRoleAccess(this.state),
    };
    this.userservice.getData(payload, 'update-permission', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Update';
      this.disableBtn = false;
      if (response && response.status == 'Success') {
        this.legacyFormat = false;
        this.appService.showAlert('Security Permission', response.msg + ' Users of this role must log in again to see the change.', 'success');
      } else {
        this.appService.showAlert('Security Permission', (response && response.msg) || 'Sorry!! Something went wrong!', 'error');
      }
    }, () => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Update';
      this.appService.showAlert('Security Permission', 'Sorry!! Something went wrong!', 'error');
    });
  }
}
