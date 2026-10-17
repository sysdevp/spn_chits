import { Component, ElementRef, HostListener, Input, OnDestroy, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { Subject, Subscription } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';
import { UserService } from '../../Services/user.service';

@Component({
  selector: 'app-customer-history-search',
  templateUrl: './customer-history-search.component.html',
  styles: [
    `
      .chs-wrap {
        position: relative;
        min-width: 260px;
        max-width: 420px;
        width: 100%;
      }
      .chs-wrap.compact {
        min-width: 220px;
        max-width: 320px;
      }
      .chs-label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 4px;
      }
      .chs-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
      }
      .chs-input-wrap i.search-ico {
        position: absolute;
        left: 10px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
      }
      .chs-input {
        width: 100%;
        padding: 8px 32px 8px 32px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
      }
      .chs-input:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
      }
      .chs-clear {
        position: absolute;
        right: 8px;
        border: 0;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        padding: 2px 6px;
      }
      .chs-dropdown {
        position: absolute;
        z-index: 1050;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        max-height: 320px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
      }
      .chs-item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: #fff;
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
      }
      .chs-item:hover,
      .chs-item.active {
        background: #f0fdf4;
      }
      .chs-item-title {
        font-weight: 600;
        color: #0f172a;
        font-size: 13px;
      }
      .chs-item-meta {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
      }
      .chs-empty,
      .chs-loading {
        padding: 12px;
        font-size: 12px;
        color: #64748b;
        text-align: center;
      }
      .chs-hint {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
      }
      .navbar-chs {
        margin-right: 12px;
      }
      .navbar-chs .chs-label {
        display: none;
      }
      .navbar-chs .chs-input {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.25);
        color: #fff;
      }
      .navbar-chs .chs-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
      }
      .navbar-chs .chs-input-wrap i.search-ico,
      .navbar-chs .chs-clear {
        color: rgba(255, 255, 255, 0.75);
      }
      :host-context(.spn-internal-header) .navbar-chs,
      :host-context(.spn-internal-header) .chs-wrap.compact {
        margin-right: 0;
        width: 100%;
        min-width: 0;
        max-width: none;
      }
      :host-context(.spn-internal-header) .chs-input {
        background: #fff;
        border: 1px solid #E2E8F0;
        color: #0F172A;
        height: 44px;
        width: 100%;
        max-width: none;
        border-radius: 12px;
        padding: 8px 40px 8px 14px;
        box-shadow: 0 4px 14px rgba(7, 29, 58, .06);
      }
      :host-context(.spn-internal-header) .chs-input::placeholder {
        color: #94a3b8;
      }
      :host-context(.spn-internal-header) .chs-input-wrap i.search-ico {
        left: auto;
        right: 14px;
        color: #64748B;
      }
      :host-context(.spn-internal-header) .chs-clear {
        right: 34px;
        color: #64748B;
      }
    `,
  ],
})
export class CustomerHistorySearchComponent implements OnInit, OnDestroy {
  @Input() compact = false;
  @Input() variant: 'dashboard' | 'navbar' = 'dashboard';
  @Input() placeholder = '';
  @Input() branchId: string | number | '' = '';
  @ViewChild('rootEl') rootEl: ElementRef;

  query = '';
  results: any[] = [];
  open = false;
  loading = false;
  activeIndex = -1;
  private search$ = new Subject<string>();
  private sub: Subscription;

  constructor(private userservice: UserService, private router: Router) {}

  ngOnInit() {
    this.sub = this.search$
      .pipe(debounceTime(350), distinctUntilChanged())
      .subscribe((q) => this.runSearch(q));
  }

  ngOnDestroy() {
    if (this.sub) {
      this.sub.unsubscribe();
    }
  }

  onInput(value: string) {
    this.query = value || '';
    this.activeIndex = -1;
    if (this.query.trim().length < 2) {
      this.results = [];
      this.open = false;
      this.loading = false;
      return;
    }
    this.open = true;
    this.loading = true;
    this.search$.next(this.query.trim());
  }

  clear() {
    this.query = '';
    this.results = [];
    this.open = false;
    this.loading = false;
    this.activeIndex = -1;
  }

  private runSearch(q: string) {
    if (!q || q.length < 2) {
      this.results = [];
      this.loading = false;
      return;
    }
    const payload: any = {
      q: q,
      tenant_id: localStorage.getItem('tenant_id') || '',
      log_id: localStorage.getItem('logged_employee_id') || '',
      user_id: localStorage.getItem('user_id') || '',
      role_type_id: localStorage.getItem('role_type_id') || '',
    };
    if (this.branchId !== '' && this.branchId != null) {
      payload.branch_id = this.branchId;
    }
    this.userservice.getData(payload, 'search-customers-history', 'POST').subscribe(
      (response: any) => {
        this.loading = false;
        if (response && response.status === 'Success') {
          this.results = response.data || [];
          this.open = true;
        } else {
          this.fallbackLocalSearch(q);
        }
      },
      () => {
        this.fallbackLocalSearch(q);
      }
    );
  }

  /** Temporary fallback when search API is not deployed yet (name/mobile/code only). */
  private fallbackLocalSearch(q: string) {
    const branchId = this.branchId || localStorage.getItem('branch_id') || '';
    const cond: any = {};
    if (branchId) {
      cond['conditions[branch_id]'] = branchId;
    } else if (localStorage.getItem('tenant_id')) {
      cond['conditions[tenant_id]'] = localStorage.getItem('tenant_id');
    }
    this.userservice.getData(cond, 'get-customers-view', 'POST').subscribe(
      (response: any) => {
        this.loading = false;
        const needle = (q || '').toLowerCase();
        const rows = (response && response.data) ? response.data : [];
        this.results = rows
          .filter((c: any) => {
            const blob = [
              c.name,
              c.last_name,
              c.full_name,
              c.mobile_no,
              c.customer_code,
            ]
              .join(' ')
              .toLowerCase();
            return blob.indexOf(needle) >= 0;
          })
          .slice(0, 20)
          .map((c: any) => ({
            id: c.id,
            branch_id: c.branch_id,
            branch_name: c.branch_name,
            customer_code: c.customer_code,
            name: c.name || c.full_name,
            mobile_no: c.mobile_no,
            matched_ar_no: null,
            display_name: c.full_name || (c.name + ' / ' + (c.customer_code || '')),
            full_name: c.full_name || c.name,
          }));
        this.open = true;
      },
      () => {
        this.loading = false;
        this.results = [];
        this.open = true;
      }
    );
  }

  selectCustomer(row: any) {
    if (!row || !row.id) {
      return;
    }
    this.open = false;
    this.query = row.display_name || row.full_name || '';
    this.router.navigate(['/admin-panel/customer-ledger'], {
      queryParams: {
        branch_id: row.branch_id || '',
        customer_id: row.id,
        open: 1,
      },
    });
  }

  onKeydown(event: KeyboardEvent) {
    if (!this.open || !this.results.length) {
      return;
    }
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      this.activeIndex = Math.min(this.activeIndex + 1, this.results.length - 1);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      this.activeIndex = Math.max(this.activeIndex - 1, 0);
    } else if (event.key === 'Enter' && this.activeIndex >= 0) {
      event.preventDefault();
      this.selectCustomer(this.results[this.activeIndex]);
    } else if (event.key === 'Escape') {
      this.open = false;
    }
  }

  @HostListener('document:click', ['$event'])
  onDocClick(event: MouseEvent) {
    if (!this.rootEl || !this.rootEl.nativeElement) {
      return;
    }
    if (!this.rootEl.nativeElement.contains(event.target)) {
      this.open = false;
    }
  }
}
