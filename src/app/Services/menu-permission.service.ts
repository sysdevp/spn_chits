import { Injectable } from '@angular/core';
import { MenuPermType, ROUTE_MENU, RouteMenuConfig } from '../config/app-menu.config';

@Injectable({
  providedIn: 'root',
})
export class MenuPermissionService {
  private permissionList: any[] = [];

  refreshPermissions(): void {
    this.permissionList = this.parsePermissionList();
  }

  private parsePermissionList(): any[] {
    try {
      const raw = localStorage.getItem('role_permission');
      if (!raw) {
        return [];
      }
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  isTenantAdmin(): boolean {
    const flag = localStorage.getItem('is_tenant_admin');
    return !!(flag && flag !== '0');
  }

  hasTenantScope(): boolean {
    const tenantId = localStorage.getItem('tenant_id');
    return !!(tenantId && tenantId !== '');
  }

  /** Super-admin (no tenant) or tenant admin sees everything except tenant module (54). */
  canBypassMenuCheck(menuId?: number): boolean {
    if (!this.hasTenantScope()) {
      return true;
    }
    if (this.isTenantAdmin()) {
      return menuId !== 54;
    }
    return false;
  }

  findRecord(menuId: number, parentMenuId: number): any {
    if (!this.permissionList.length) {
      this.refreshPermissions();
    }
    if (parentMenuId === 0) {
      const mod = this.permissionList.find((item: any) => Number(item.menu_id) === Number(menuId));
      if (!mod) {
        return null;
      }
      if (this.isPermEnabled(mod, 'view_perm')) {
        return mod;
      }
      if (Array.isArray(mod.Submenus)) {
        const child = mod.Submenus.find((s: any) => this.isPermEnabled(s, 'view_perm'));
        if (child) {
          return child;
        }
      }
      return null;
    }
    const parent = this.permissionList.find((item: any) => Number(item.menu_id) === Number(parentMenuId));
    if (parent && Array.isArray(parent.Submenus)) {
      const direct = parent.Submenus.find((item: any) => Number(item.menu_id) === Number(menuId));
      if (direct) {
        return direct;
      }
    }
    for (const module of this.permissionList) {
      if (!Array.isArray(module.Submenus)) {
        continue;
      }
      const hit = module.Submenus.find((item: any) => Number(item.menu_id) === Number(menuId));
      if (hit) {
        return hit;
      }
    }
    return null;
  }

  findRecordByTitle(title: string): any {
    if (!title) {
      return null;
    }
    if (!this.permissionList.length) {
      this.refreshPermissions();
    }
    const needle = title.toLowerCase();
    for (const module of this.permissionList) {
      if (module.menu_title && String(module.menu_title).toLowerCase().indexOf(needle) >= 0) {
        return module;
      }
      if (Array.isArray(module.Submenus)) {
        for (const sub of module.Submenus) {
          if (sub.menu_title && String(sub.menu_title).toLowerCase().indexOf(needle) >= 0) {
            return sub;
          }
        }
      }
    }
    return null;
  }

  isPermEnabled(record: any, permType: MenuPermType = 'view_perm', useTopPerm = false): boolean {
    if (!record) {
      return false;
    }
    if (useTopPerm && record.top_perm !== undefined) {
      return Number(record.top_perm) === 1;
    }
    const v = record[permType];
    return v === 1 || v === '1' || v === true;
  }

  hasPermission(
    menuId: number,
    parentMenuId: number,
    permType: MenuPermType = 'view_perm',
    useTopPerm = false
  ): boolean {
    if (this.canBypassMenuCheck(menuId)) {
      return true;
    }
    const record = this.findRecord(menuId, parentMenuId);
    return this.isPermEnabled(record, permType, useTopPerm);
  }

  hasPermissionByTitle(title: string, permType: MenuPermType = 'view_perm', useTopPerm = false): boolean {
    if (!this.hasTenantScope() || this.isTenantAdmin()) {
      return true;
    }
    const record = this.findRecordByTitle(title);
    return this.isPermEnabled(record, permType, useTopPerm);
  }

  /** Header shortcuts: honor top_perm when configured. */
  hasHeaderAccess(route: string): boolean {
    const key = (route || '').replace(/^\//, '').replace(/^admin-panel\//, '');
    const config: RouteMenuConfig = ROUTE_MENU[key];
    if (!config) {
      return this.canBypassMenuCheck();
    }
    if (this.canBypassMenuCheck(config.menuId)) {
      return true;
    }
    const permType = config.permType || 'view_perm';
    if (config.menuId != null && config.parentMenuId != null) {
      if (this.hasPermission(config.menuId, config.parentMenuId, permType, !!config.useTopPerm)) {
        return true;
      }
      if (config.altMenuId != null && config.altParentMenuId != null) {
        if (this.hasPermission(config.altMenuId, config.altParentMenuId, permType, !!config.useTopPerm)) {
          return true;
        }
      }
    }
    if (config.title) {
      if (this.hasPermissionByTitle(config.title, permType, !!config.useTopPerm)) {
        return true;
      }
    }
    if (permType !== 'view_perm') {
      return this.checkRoutePermission(route, 'view_perm');
    }
    return false;
  }

  hasRouteAccess(route: string, overridePerm?: MenuPermType): boolean {
    if (overridePerm) {
      return this.checkRoutePermission(route, overridePerm);
    }
    return (
      this.checkRoutePermission(route, 'view_perm') ||
      this.checkRoutePermission(route, 'add_perm') ||
      this.checkRoutePermission(route, 'edit_perm')
    );
  }

  private checkRoutePermission(route: string, permType: MenuPermType): boolean {
    const key = (route || '').replace(/^\//, '').replace(/^admin-panel\//, '');
    const config: RouteMenuConfig = ROUTE_MENU[key];
    if (!config) {
      return this.canBypassMenuCheck();
    }
    if (this.canBypassMenuCheck(config.menuId)) {
      return true;
    }
    if (config.menuId != null && config.parentMenuId != null) {
      const useTop = !!config.useTopPerm && permType === 'add_perm';
      if (this.hasPermission(config.menuId, config.parentMenuId, permType, useTop)) {
        return true;
      }
      if (config.altMenuId != null && config.altParentMenuId != null) {
        if (this.hasPermission(config.altMenuId, config.altParentMenuId, permType, useTop)) {
          return true;
        }
      }
    }
    if (config.title) {
      return this.hasPermissionByTitle(config.title, permType, !!config.useTopPerm && permType === 'add_perm');
    }
    return false;
  }

  /** Used by sidenav: show menu if user has view access (or bypass). */
  canShowMenu(menuId: number, parentMenuId: number): boolean {
    return this.hasPermission(menuId, parentMenuId, 'view_perm');
  }

  /** Used by routes / add screens. */
  canActivateRoute(menuId: number, parentMenuId: number, permType: MenuPermType | ''): boolean {
    if (localStorage.getItem('token') == null) {
      return false;
    }
    if (!this.hasTenantScope()) {
      return true;
    }
    if (this.isTenantAdmin()) {
      return menuId !== 54;
    }
    const record = this.findRecord(menuId, parentMenuId);
    if (!record) {
      return false;
    }
    if (!permType) {
      return (
        Number(record.view_perm) === 1 ||
        Number(record.add_perm) === 1 ||
        Number(record.edit_perm) === 1 ||
        Number(record.delete_perm) === 1
      );
    }
    return Number(record[permType]) === 1;
  }
}
