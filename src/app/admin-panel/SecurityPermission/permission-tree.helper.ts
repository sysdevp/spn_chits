import {
  PermissionLeaf,
  PermissionSection,
  SIDEBAR_PERMISSION_SECTIONS,
  MODULE_TITLES,
  SAVED_MODULE_ORDER,
} from '../../config/sidebar-permission.config';

export interface PermissionFormSection {
  menu_id: number;
  menu_title: string;
  page_type: number;
  has_sub_menu: number;
  add_perm: number | string;
  edit_perm: number | string;
  view_perm: number | string;
  delete_perm: number | string;
  Submenus: PermissionFormSubmenu[];
}

export interface PermissionFormSubmenu {
  menu_id: number;
  menu_title: string;
  page_type: number;
  has_sub_menu: number;
  storage_module_id: number;
  add_perm: number | string;
  edit_perm: number | string;
  view_perm: number | string;
  delete_perm: number | string;
  top_perm: number | string;
}

function emptySubmenu(leaf: PermissionLeaf): PermissionFormSubmenu {
  return {
    menu_id: leaf.menuId,
    menu_title: leaf.title,
    page_type: leaf.pageType,
    has_sub_menu: 0,
    storage_module_id: leaf.storageModuleId,
    add_perm: '',
    edit_perm: '',
    view_perm: '',
    delete_perm: '',
    top_perm: '',
  };
}

function collectLeaves(section: PermissionSection): PermissionLeaf[] {
  const leaves: PermissionLeaf[] = [];
  if (section.leaves) {
    leaves.push(...section.leaves.filter((l) => Number(l.menuId) > 0));
  }
  if (section.groups) {
    section.groups.forEach((g) =>
      leaves.push(...g.leaves.filter((l) => Number(l.menuId) > 0))
    );
  }
  return leaves;
}

/** Build display sections for add/update permission forms (matches sidebar). */
export function buildPermissionFormSections(): PermissionFormSection[] {
  const sections: PermissionFormSection[] = [];
  SIDEBAR_PERMISSION_SECTIONS.forEach((section, index) => {
    const leaves = collectLeaves(section);
    if (section.menuId && !section.virtual && leaves.length === 0) {
      sections.push({
        menu_id: section.menuId,
        menu_title: section.title,
        page_type: section.pageType || 3,
        has_sub_menu: 0,
        add_perm: '',
        edit_perm: '',
        view_perm: '',
        delete_perm: '',
        Submenus: [],
      });
      return;
    }
    sections.push({
      menu_id: section.menuId || -(index + 1),
      menu_title: section.title,
      page_type: section.pageType || 4,
      has_sub_menu: leaves.length > 0 ? 1 : 0,
      add_perm: '',
      edit_perm: '',
      view_perm: '',
      delete_perm: '',
      Submenus: leaves.map(emptySubmenu),
    });
  });
  return sections;
}

function permMax(a: any, b: any): number {
  return Math.max(Number(a) || 0, Number(b) || 0);
}

function mergeSubmenuPerms(target: PermissionFormSubmenu | any, source: any) {
  if (!source) {
    return;
  }
  target.add_perm = permMax(target.add_perm, source.add_perm);
  target.edit_perm = permMax(target.edit_perm, source.edit_perm);
  target.view_perm = permMax(target.view_perm, source.view_perm);
  target.delete_perm = permMax(target.delete_perm, source.delete_perm);
  target.top_perm = permMax(target.top_perm, source.top_perm);
}

export function findSavedSubmenu(savedModules: any[], menuId: number): any {
  for (const mod of savedModules) {
    if (!Array.isArray(mod.Submenus)) {
      continue;
    }
    const hit = mod.Submenus.find((s: any) => Number(s.menu_id) === Number(menuId));
    if (hit) {
      return hit;
    }
  }
  return null;
}

/** Apply saved role_access onto sidebar-aligned form sections. */
export function applySavedPermissionsToSections(
  sections: PermissionFormSection[],
  savedModules: any[]
): void {
  if (!Array.isArray(savedModules)) {
    return;
  }
  sections.forEach((section) => {
    if (section.has_sub_menu !== 1) {
      const saved = savedModules.find((m: any) => Number(m.menu_id) === Number(section.menu_id));
      if (saved) {
        section.add_perm = saved.add_perm;
        section.edit_perm = saved.edit_perm;
        section.view_perm = saved.view_perm;
        section.delete_perm = saved.delete_perm;
      }
      return;
    }
    section.Submenus.forEach((sub) => {
      const savedSub = findSavedSubmenu(savedModules, sub.menu_id);
      mergeSubmenuPerms(sub, savedSub);
    });
  });
}

/** Flatten sidebar display form → canonical modules for store-permission / role_access. */
export function flattenSectionsForSave(sections: PermissionFormSection[]): any[] {
  const moduleMap = new Map<number, any>();

  const ensureModule = (moduleId: number) => {
    if (!moduleMap.has(moduleId)) {
      moduleMap.set(moduleId, {
        menu_id: moduleId,
        menu_title: MODULE_TITLES[moduleId] || 'Module',
        page_type: 4,
        has_sub_menu: 1,
        add_perm: 0,
        edit_perm: 0,
        view_perm: 0,
        delete_perm: 0,
        Submenus: [],
      });
    }
    return moduleMap.get(moduleId);
  };

  sections.forEach((section) => {
    if (section.has_sub_menu !== 1) {
      moduleMap.set(Number(section.menu_id), {
        menu_id: section.menu_id,
        menu_title: section.menu_title,
        page_type: section.page_type,
        has_sub_menu: 0,
        add_perm: section.add_perm || 0,
        edit_perm: section.edit_perm || 0,
        view_perm: section.view_perm || 0,
        delete_perm: section.delete_perm || 0,
        Submenus: [],
      });
      return;
    }

    (section.Submenus || []).forEach((sub) => {
      const storageId = Number(sub.storage_module_id);
      const mod = ensureModule(storageId);
      const existing = mod.Submenus.find((s: any) => Number(s.menu_id) === Number(sub.menu_id));
      if (!existing) {
        mod.Submenus.push({
          menu_id: sub.menu_id,
          menu_title: sub.menu_title,
          page_type: sub.page_type,
          has_sub_menu: 0,
          add_perm: sub.add_perm || 0,
          edit_perm: sub.edit_perm || 0,
          view_perm: sub.view_perm || 0,
          delete_perm: sub.delete_perm || 0,
          top_perm: sub.top_perm || 0,
        });
      } else {
        mergeSubmenuPerms(existing, sub);
      }
    });
  });

  const ordered: any[] = [];
  SAVED_MODULE_ORDER.forEach((id) => {
    if (moduleMap.has(id)) {
      ordered.push(moduleMap.get(id));
      moduleMap.delete(id);
    }
  });
  moduleMap.forEach((mod) => ordered.push(mod));
  return ordered;
}

export function getSidebarSectionsForTemplate(): PermissionSection[] {
  return SIDEBAR_PERMISSION_SECTIONS;
}

/** Match submenu form row by menu_id; use title when the same menu_id appears twice in one section. */
export function getSubmenuFormIndex(subMenuArray: any, menuId: number, title?: string): number {
  if (!subMenuArray || !subMenuArray.controls) {
    return -1;
  }
  if (title) {
    for (let j = 0; j < subMenuArray.length; j++) {
      const ctrl = subMenuArray.controls[j];
      if (
        Number(ctrl.get('menu_id').value) === Number(menuId) &&
        String(ctrl.get('menu_title').value) === String(title)
      ) {
        return j;
      }
    }
  }
  for (let j = 0; j < subMenuArray.length; j++) {
    if (Number(subMenuArray.controls[j].get('menu_id').value) === Number(menuId)) {
      return j;
    }
  }
  return -1;
}
