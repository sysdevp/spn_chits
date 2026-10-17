import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AppService } from '../../../app.service';
import { UserService } from '../../../Services/user.service';
import { AccessState, buildRoleAccess, readRoleAccess } from '../../../config/spn-access.config';

@Component({
  selector: 'app-add-permission',
  templateUrl: './add-permission.component.html',
  styles: []
})
export class AddPermissionComponent implements OnInit {
  isSuperAdmin = false;
  companyList: any[] = [];
  roleList: any[] = [];
  copyRoleList: any[] = [];
  tenantId = '';
  roleId = '';
  copyRoleId = '';
  state: AccessState = readRoleAccess([]);
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Submit';

  constructor(private appService: AppService, private userservice: UserService, private router: Router) {}

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    if (tenantId) {
      this.tenantId = tenantId;
      this.loadRoles();
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status == 'Success' ? response.data : [];
      }, () => {});
    }
  }

  companyChg() {
    this.roleId = '';
    this.loadRoles();
  }

  loadRoles() {
    if (!this.tenantId) {
      this.roleList = [];
      this.copyRoleList = [];
      return;
    }
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId }, 'get-roles-for-permission', 'POST').subscribe((response: any) => {
      this.roleList = response && response.status == 'Success' ? response.data : [];
    }, () => {});
    this.userservice.getData({ 'conditions[tenant_id]': this.tenantId, for_edit: 1, is_super_admin: true }, 'get-roles-for-permission', 'POST').subscribe((response: any) => {
      this.copyRoleList = response && response.status == 'Success' ? response.data : [];
    }, () => {});
  }

  copyFromRole() {
    if (!this.copyRoleId) {
      return;
    }
    const cond = { 'conditions[tenant_id]': this.tenantId, 'conditions[role_id]': this.copyRoleId };
    this.userservice.getData(cond, 'get-permissions', 'POST').subscribe((response: any) => {
      if (response && response.status == 'Success' && response.data && response.data[0]) {
        this.state = readRoleAccess(response.data[0].role_access || []);
        this.appService.showAlert('Security Permission', 'Access copied — adjust and submit.', 'success');
      }
    }, () => {});
  }

  onSubmit() {
    this.submitted = true;
    if (!this.tenantId || !this.roleId) {
      return;
    }
    this.disableBtn = true;
    this.SubmitbuttonName = 'Loading...';
    const payload = {
      tenant_id: this.tenantId,
      role_id: this.roleId,
      created_by: localStorage.getItem('logged_user_id'),
      Permissions: buildRoleAccess(this.state),
    };
    this.userservice.getData(payload, 'store-permission', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Submit';
      if (response && response.status == 'Success') {
        this.appService.showAlert('Security Permission', response.msg, 'success');
        setTimeout(() => this.router.navigate(['/admin-panel/update-permission'], { queryParams: { role: this.roleId } }), 300);
      } else {
        this.appService.showAlert('Security Permission', (response && response.msg) || 'Sorry!! Something went wrong!', 'error');
        this.disableBtn = false;
      }
    }, () => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Submit';
      this.appService.showAlert('Security Permission', 'Sorry!! Something went wrong!', 'error');
    });
  }
}
