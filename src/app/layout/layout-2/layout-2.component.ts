import { Component, AfterViewInit, OnDestroy } from '@angular/core';
import { NavigationEnd, Router } from '@angular/router';
import { LayoutService } from '../layout.service';
import { SIDEBAR_PERMISSION_SECTIONS } from '../../config/sidebar-permission.config';

@Component({
  selector: 'app-layout-2',
  templateUrl: './layout-2.component.html',
  styles: [':host { display: block; }', ':host /deep/ .layout-loading .sidenav-link { transition: none !important; }']
})
export class Layout2Component implements AfterViewInit, OnDestroy {
  // Prevent "blink" effect
  public initialized = false;
  showPageHead = false;
  pageTitle = '';
  pageSection = '';
  pageSubtitle = '';

  constructor(private layoutService: LayoutService, private router: Router) {
    this.syncPage(this.router.url);
    this.router.events.subscribe((event) => {
      if (event instanceof NavigationEnd) {
        this.syncPage(event.urlAfterRedirects || event.url);
      }
    });
  }

  private syncPage(url: string) {
    const path = (url || '').split('?')[0].split('/').filter((part) => !!part);
    const last = path.length ? path[path.length - 1] : '';
    this.showPageHead = !!last && path.indexOf('hub') === -1 && path.indexOf('branch-overview') === -1 && last !== 'dashboard' && last !== 'admin-panel' && last !== 'view-scheme' && last !== 'due-vs-payments' && last !== 'prized-show-group' && last !== 'prized-show-report';
    let foundTitle = '';
    let foundSection = '';
    SIDEBAR_PERMISSION_SECTIONS.forEach((section) => {
      const leaves = (section.leaves || []).slice();
      (section.groups || []).forEach((group) => {
        (group.leaves || []).forEach((leaf) => leaves.push(leaf));
      });
      leaves.forEach((leaf) => {
        if (leaf.route && leaf.route === last) {
          foundTitle = leaf.title;
          foundSection = section.title;
        }
      });
    });
    if (foundTitle) {
      this.pageTitle = foundTitle;
      this.pageSection = foundSection || 'Home';
    } else {
      const slug = /^\d+$/.test(last) && path.length > 1 ? path[path.length - 2] : last;
      this.pageTitle = this.pretty(slug);
      this.pageSection = 'Home';
    }
    this.pageSubtitle = 'Manage and monitor ' + (this.pageTitle || 'records').toLowerCase();
  }

  private pretty(slug: string) {
    return (slug || 'Page').replace(/-/g, ' ').replace(/\b\w/g, (ch) => ch.toUpperCase());
  }

  ngAfterViewInit() {
    setTimeout(() => {
      this.initialized = true;

      this.layoutService.init();
      this.layoutService.update();
      this.layoutService.setAutoUpdate(true);
    });
  }

  ngOnDestroy() {
    setTimeout(() => {
      this.layoutService.destroy();
    });
  }

  exportPage() {
    const table = document.querySelector('.layout-content .container-fluid table') as HTMLTableElement;
    if (!table) {
      return;
    }
    const headers = [];
    const headerCells = table.querySelectorAll('thead th');
    const skip = [];
    for (let i = 0; i < headerCells.length; i++) {
      const label = (headerCells[i].textContent || '').replace(/\s+/g, ' ').trim();
      if (/^actions?$/i.test(label) || label === '') {
        skip.push(i);
      }
      headers.push(label || ('Column ' + (i + 1)));
    }
    const lines = [headers.filter((label, index) => skip.indexOf(index) < 0).join(',')];
    const bodyRows = table.querySelectorAll('tbody tr');
    for (let r = 0; r < bodyRows.length; r++) {
      const cells = bodyRows[r].querySelectorAll('td');
      const values = [];
      for (let c = 0; c < cells.length; c++) {
        if (skip.indexOf(c) >= 0) {
          continue;
        }
        const text = (cells[c].textContent || '').replace(/\s+/g, ' ').trim().replace(/"/g, '""');
        values.push('"' + text + '"');
      }
      if (values.length) {
        lines.push(values.join(','));
      }
    }
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = (this.pageTitle || 'export').replace(/\s+/g, '-') + '.csv';
    link.click();
  }

  closeSidenav() {
    setTimeout(() => {
      this.layoutService.setCollapsed(true);
    });
  }
}
