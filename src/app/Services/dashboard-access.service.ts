import { Injectable } from '@angular/core';
import { MenuPermissionService } from './menu-permission.service';
import {
  DashboardKey,
  DashboardTypeDef,
  DASHBOARD_MENU_PARENT,
  DASHBOARD_TYPES,
  DASHBOARD_TYPE_BY_KEY,
  DASHBOARD_TYPE_BY_MENU_ID,
  PREFERRED_DASHBOARD_KEY,
} from '../config/dashboard-types.config';

@Injectable({
  providedIn: 'root',
})
export class DashboardAccessService {
  constructor(private menuPermission: MenuPermissionService) {}

  /** Designs the logged-in role may open (excludes classic). */
  getAllowedDashboards(): DashboardTypeDef[] {
    this.menuPermission.refreshPermissions();

    if (this.menuPermission.canBypassMenuCheck(DASHBOARD_MENU_PARENT)) {
      return DASHBOARD_TYPES.slice();
    }

    const allowed: DashboardTypeDef[] = [];
    DASHBOARD_TYPES.forEach((d) => {
      const rec = this.menuPermission.findRecord(d.menuId, DASHBOARD_MENU_PARENT);
      if (rec && this.menuPermission.isPermEnabled(rec, 'view_perm')) {
        allowed.push(d);
      }
    });

    // Also accept top_perm on a design leaf (title checkbox alone)
    if (!allowed.length) {
      DASHBOARD_TYPES.forEach((d) => {
        const rec = this.menuPermission.findRecord(d.menuId, DASHBOARD_MENU_PARENT);
        if (rec && this.menuPermission.isPermEnabled(rec, 'view_perm', true)) {
          if (!allowed.some((a) => a.key === d.key)) {
            allowed.push(d);
          }
        }
      });
    }

    return allowed;
  }

  /** True when no design leaves are granted (use classic layout). */
  useClassicDashboard(): boolean {
    if (this.menuPermission.canBypassMenuCheck(DASHBOARD_MENU_PARENT)) {
      return false;
    }
    return this.getAllowedDashboards().length === 0;
  }

  getActiveDashboardKey(): DashboardKey {
    if (this.useClassicDashboard()) {
      return 'classic';
    }
    const allowed = this.getAllowedDashboards();
    if (!allowed.length) {
      return 'classic';
    }
    const preferred = localStorage.getItem(PREFERRED_DASHBOARD_KEY) as DashboardKey;
    if (preferred && allowed.some((d) => d.key === preferred)) {
      return preferred;
    }
    return allowed[0].key;
  }

  getActiveDashboard(): DashboardTypeDef | null {
    const key = this.getActiveDashboardKey();
    if (key === 'classic') {
      return null;
    }
    return DASHBOARD_TYPE_BY_KEY[key] || null;
  }

  setActiveDashboard(key: DashboardKey): void {
    if (key === 'classic') {
      localStorage.removeItem(PREFERRED_DASHBOARD_KEY);
      return;
    }
    const allowed = this.getAllowedDashboards();
    if (allowed.some((d) => d.key === key)) {
      localStorage.setItem(PREFERRED_DASHBOARD_KEY, key);
    }
  }

  getDefByMenuId(menuId: number): DashboardTypeDef | null {
    return DASHBOARD_TYPE_BY_MENU_ID[menuId] || null;
  }
}
