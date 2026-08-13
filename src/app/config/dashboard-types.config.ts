/** Role-based dashboard designs (Security Permissions leaves under menu_id 1). */

export type DashboardKey =
  | 'md'
  | 'admin'
  | 'branch_manager'
  | 'sales_manager'
  | 'collection_manager'
  | 'branch_sales'
  | 'classic';

export interface DashboardTypeDef {
  key: DashboardKey;
  title: string;
  subtitle: string;
  menuId: number;
  /** Synthetic leaf under Dashboard module */
  storageModuleId: number;
}

export const DASHBOARD_MENU_PARENT = 1;

/** Reserved IDs 201–206 for dashboard design permissions */
export const DASHBOARD_TYPES: DashboardTypeDef[] = [
  {
    key: 'md',
    title: 'MD Dashboard – Overall View',
    subtitle: 'Complete overview of business performance across all branches',
    menuId: 201,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
  {
    key: 'admin',
    title: 'Branch Admin Panel',
    subtitle: 'Welcome back — branch operations, collections, and daily tasks',
    menuId: 202,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
  {
    key: 'branch_manager',
    title: 'Branch Manager Dashboard',
    subtitle: 'Branch collections, targets, and team performance',
    menuId: 203,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
  {
    key: 'sales_manager',
    title: 'Sales Manager Dashboard',
    subtitle: 'Overview of all branches sales activities',
    menuId: 204,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
  {
    key: 'collection_manager',
    title: 'Collections Manager Dashboard',
    subtitle: 'Collections, pending, and overdue across branches',
    menuId: 205,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
  {
    key: 'branch_sales',
    title: 'Branch Sales Panel',
    subtitle: 'Welcome back, Sales Manager — branch leads and conversions',
    menuId: 206,
    storageModuleId: DASHBOARD_MENU_PARENT,
  },
];

export const DASHBOARD_TYPE_BY_KEY: { [key: string]: DashboardTypeDef } = DASHBOARD_TYPES.reduce(
  (acc, d) => {
    acc[d.key] = d;
    return acc;
  },
  {} as { [key: string]: DashboardTypeDef }
);

export const DASHBOARD_TYPE_BY_MENU_ID: { [id: number]: DashboardTypeDef } = DASHBOARD_TYPES.reduce(
  (acc, d) => {
    acc[d.menuId] = d;
    return acc;
  },
  {} as { [id: number]: DashboardTypeDef }
);

export const PREFERRED_DASHBOARD_KEY = 'preferred_dashboard';
