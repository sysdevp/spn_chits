import { Component, Input } from '@angular/core';
import {
  AccessAction,
  AccessPage,
  AccessSection,
  AccessState,
  emptyFlags,
  SPN_ACCESS_SECTIONS,
} from '../../../config/spn-access.config';

interface PageGroup {
  title: string;
  pages: AccessPage[];
}

@Component({
  selector: 'app-permission-matrix',
  templateUrl: './permission-matrix.component.html',
  styleUrls: ['./permission-matrix.component.css'],
})
export class PermissionMatrixComponent {
  @Input() state: AccessState = {};

  sections: AccessSection[] = SPN_ACCESS_SECTIONS;
  actions: AccessAction[] = ['view', 'add', 'edit', 'delete'];
  search = '';
  collapsed: { [key: string]: boolean } = {};

  private groupCache: { [key: string]: PageGroup[] } = {};

  groups(section: AccessSection): PageGroup[] {
    if (!this.groupCache[section.key]) {
      const list: PageGroup[] = [];
      section.pages.forEach((p) => {
        const title = p.group || '';
        let g = list.find((x) => x.title === title);
        if (!g) {
          g = { title, pages: [] };
          list.push(g);
        }
        g.pages.push(p);
      });
      this.groupCache[section.key] = list;
    }
    return this.groupCache[section.key];
  }

  private flags(id: number) {
    if (!this.state[id]) {
      this.state[id] = emptyFlags();
    }
    return this.state[id];
  }

  matches(page: AccessPage, section: AccessSection): boolean {
    const q = this.search.trim().toLowerCase();
    if (!q) {
      return true;
    }
    return (page.title + ' ' + (page.group || '') + ' ' + section.title + ' ' + (page.route || '')).toLowerCase().indexOf(q) >= 0;
  }

  visiblePages(section: AccessSection): AccessPage[] {
    return section.pages.filter((p) => this.matches(p, section));
  }

  groupVisible(group: PageGroup, section: AccessSection): boolean {
    return group.pages.some((p) => this.matches(p, section));
  }

  has(page: AccessPage, action: AccessAction): boolean {
    return page.actions.indexOf(action) >= 0;
  }

  isOn(page: AccessPage, action: AccessAction): boolean {
    return !!(this.state[page.id] && (this.state[page.id] as any)[action]);
  }

  toggle(page: AccessPage, action: AccessAction, on: boolean) {
    const f: any = this.flags(page.id);
    f[action] = on ? 1 : 0;
    if (action === 'view' && !on) {
      f.add = 0; f.edit = 0; f.delete = 0;
    }
    if (action !== 'view' && on) {
      f.view = 1;
    }
  }

  rowFull(page: AccessPage): boolean {
    return page.actions.every((a) => this.isOn(page, a));
  }

  setRow(page: AccessPage, on: boolean) {
    const f: any = this.flags(page.id);
    this.actions.forEach((a) => { f[a] = on && this.has(page, a) ? 1 : 0; });
  }

  setSection(section: AccessSection, mode: 'full' | 'view' | 'none') {
    this.visiblePages(section).forEach((p) => {
      const f: any = this.flags(p.id);
      this.actions.forEach((a) => {
        f[a] = this.has(p, a) && (mode === 'full' || (mode === 'view' && a === 'view')) ? 1 : 0;
      });
    });
  }

  setAll(mode: 'full' | 'view' | 'none') {
    this.sections.forEach((s) => this.setSection(s, mode));
  }

  sectionCount(section: AccessSection): number {
    return section.pages.filter((p) => this.isOn(p, 'view')).length;
  }

  sectionState(section: AccessSection): 'all' | 'some' | 'none' {
    const n = this.sectionCount(section);
    return n === 0 ? 'none' : n === section.pages.length ? 'all' : 'some';
  }

  totalCount(): number {
    return this.sections.reduce((s, sec) => s + this.sectionCount(sec), 0);
  }

  totalPages(): number {
    return this.sections.reduce((s, sec) => s + sec.pages.length, 0);
  }

  toggleCollapse(section: AccessSection) {
    this.collapsed[section.key] = !this.collapsed[section.key];
  }

  setCollapsedAll(collapsed: boolean) {
    this.sections.forEach((s) => (this.collapsed[s.key] = collapsed));
  }

  trackPage(_: number, p: AccessPage) {
    return p.id;
  }
}
