import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-prized-show-group',
  templateUrl: './prized-show-group.component.html',
  styles: [`
    .psg-page { font-family: inherit; }
    .psg-hero {
      display: flex; justify-content: space-between; align-items: flex-end; gap: 14px; flex-wrap: wrap;
      background: linear-gradient(120deg, #071D3A 0%, #0b3d78 45%, #18A957 100%);
      color: #fff; border-radius: 18px; padding: 20px 22px; margin-bottom: 14px;
    }
    .psg-kicker { color: #F5B700; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .psg-hero h1 { margin: 4px 0 6px; font-size: 28px; font-weight: 800; }
    .psg-hero p { margin: 0; max-width: 640px; color: rgba(255,255,255,.84); }
    .psg-btn { border: 0; border-radius: 10px; height: 40px; padding: 0 14px; font-weight: 800; cursor: pointer; }
    .psg-btn.green { background: #18A957; color: #fff; }
    .psg-btn.navy { background: #071D3A; color: #fff; }
    .psg-btn.soft { background: #E9FFF3; color: #006B3C; }
    .psg-btn.rose { background: #FFF0F4; color: #EF476F; }
    .psg-btn.white { background: #fff; color: #071D3A; }
    .psg-card { background: #fff; border-radius: 16px; padding: 16px; margin-bottom: 14px; box-shadow: 0 8px 20px rgba(7,29,58,.06); }
    .psg-filters { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .psg-filters label { display: block; font-size: 11px; font-weight: 800; color: #5c6b80; margin-bottom: 4px; }
    .psg-filters input, .psg-filters select, .psg-form input, .psg-form select, .psg-form textarea {
      height: 40px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 12px; min-width: 180px; background: #f8fbff; color: #071D3A;
    }
    .psg-form textarea { height: 80px; padding: 10px 12px; width: 100%; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .grid-3 { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 12px; }
    .note { background: #FFF8E8; color: #8a5a00; border-radius: 12px; padding: 10px 12px; font-size: 13px; font-weight: 700; margin-bottom: 12px; }
    .members { max-height: 280px; overflow: auto; border: 1px solid #e7eef6; border-radius: 12px; }
    .member { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-bottom: 1px solid #eef3f8; }
    .member:nth-child(even) { background: #f8fbff; }
    .member b { color: #071D3A; }
    .member span { display: block; color: #5c6b80; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #071D3A; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; padding: 10px; text-align: left; }
    td { padding: 10px; border-bottom: 1px solid #eef3f8; font-size: 13px; }
    tr:nth-child(even) td { background: #f8fbff; }
    .pill { display: inline-block; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 800; }
    .pill.green { background: #E9FFF3; color: #006B3C; }
    .pill.blue { background: #EDF6FF; color: #1687E8; }
    .pill.gold { background: #FFF8E8; color: #C48400; }
    .actions a, .actions button { margin-right: 6px; }
    .month-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .month-card { border: 1px solid #e7eef6; border-radius: 14px; padding: 12px; background: #fff; }
    .month-card.marked { background: #E9FFF3; border-color: #b9ebcd; }
    .month-card h4 { margin: 0 0 8px; color: #071D3A; font-size: 15px; }
    .month-card select, .month-card input { width: 100%; margin-bottom: 8px; height: 38px; border: 1px solid #d7e3f0; border-radius: 10px; padding: 0 10px; }
    .muted { color: #5c6b80; font-size: 12px; }
    .empty { text-align: center; color: #5c6b80; padding: 28px; }
    @media (max-width: 900px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }
  `]
})
export class PrizedShowGroupComponent implements OnInit {
  view = 'list';
  loading = false;
  saving = false;
  groups: any[] = [];
  schemes: any[] = [];
  customers: any[] = [];
  selectedIds: any = {};
  customerSearch = '';
  branchFilter = '';
  dueNoFilter = '';
  activeGroup: any = null;
  markDraft: any = {};

  form: any = {
    group_name: '',
    scheme_id: '',
    remarks: ''
  };

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    this.loadGroups();
    this.loadSchemes();
  }

  money(value: any): string {
    return '₹' + (Number(value) || 0).toLocaleString('en-IN');
  }

  loadGroups() {
    this.loading = true;
    const body = { tenant_id: localStorage.getItem('tenant_id') };
    this.userservice.getData(body, 'list-prized-show-groups', 'JSON').subscribe((res: any) => {
      this.groups = res && res.status === 'Success' ? (res.data || []) : [];
      this.loading = false;
    }, () => {
      this.groups = [];
      this.loading = false;
    });
  }

  loadSchemes() {
    const body = { tenant_id: localStorage.getItem('tenant_id') };
    this.userservice.getData(body, 'list-schemes', 'JSON').subscribe((res: any) => {
      this.schemes = res && res.status === 'Success' ? (res.data || []) : [];
    }, () => {
      this.schemes = [];
    });
  }

  openCreate() {
    this.view = 'form';
    this.activeGroup = null;
    this.form = { group_name: '', scheme_id: '', remarks: '' };
    this.customers = [];
    this.selectedIds = {};
    this.resetCustomerFilters();
  }

  resetCustomerFilters() {
    this.customerSearch = '';
    this.branchFilter = '';
    this.dueNoFilter = '';
  }

  backToList() {
    this.view = 'list';
    this.activeGroup = null;
    this.loadGroups();
  }

  schemeLabel(scheme: any): string {
    if (!scheme) { return ''; }
    const value = scheme.chit_value || '';
    const months = scheme.no_of_months || '';
    const letter = scheme.chit_letter || '';
    return value + ' (' + months + ' M) / ' + letter;
  }

  onSchemeChange() {
    this.selectedIds = {};
    this.customers = [];
    this.resetCustomerFilters();
    if (!this.form.scheme_id) { return; }
    const body = {
      tenant_id: localStorage.getItem('tenant_id'),
      scheme_id: this.form.scheme_id
    };
    this.userservice.getData(body, 'list-prized-show-scheme-customers', 'JSON').subscribe((res: any) => {
      this.customers = res && res.status === 'Success' ? (res.data || []) : [];
    }, () => {
      this.customers = [];
    });
  }

  branchOptions() {
    const names = [];
    this.customers.forEach((row) => {
      const name = (row.branch_name || '').trim();
      if (name && names.indexOf(name) < 0) {
        names.push(name);
      }
    });
    return names.sort();
  }

  dueNoOptions() {
    const nos = [];
    this.customers.forEach((row) => {
      const due = this.rowDueNo(row);
      if (due !== '' && due !== '0' && nos.indexOf(due) < 0) {
        nos.push(due);
      }
    });
    return nos.sort((a, b) => {
      const na = Number(a);
      const nb = Number(b);
      if (!isNaN(na) && !isNaN(nb)) {
        return na - nb;
      }
      return a.localeCompare(b);
    });
  }

  rowDueNo(row: any): string {
    if (row.due_no != null && row.due_no !== '' && String(row.due_no) !== '0') {
      return String(row.due_no);
    }
    if (row.ticket_no != null && row.ticket_no !== '' && String(row.ticket_no) !== '0') {
      return String(row.ticket_no);
    }
    return '';
  }

  visibleCustomers() {
    const q = (this.customerSearch || '').toLowerCase();
    const branch = this.branchFilter;
    const dueExact = this.dueNoFilter !== '' && this.dueNoFilter !== null ? String(this.dueNoFilter) : '';

    return this.customers.filter((row) => {
      const due = this.rowDueNo(row);
      if (branch && (row.branch_name || '') !== branch) {
        return false;
      }
      if (dueExact && due !== dueExact) {
        return false;
      }
      if (!q) {
        return true;
      }
      const blob = (
        (row.customer_name || '') + ' ' +
        (row.mobile || '') + ' ' +
        (row.ar_no || '') + ' ' +
        (row.customer_code || '') + ' ' +
        (row.branch_name || '') + ' ' +
        due
      ).toLowerCase();
      return blob.indexOf(q) >= 0;
    });
  }

  toggleCustomer(row: any, checked: boolean) {
    if (checked) {
      this.selectedIds[row.enrollment_id] = true;
    } else {
      delete this.selectedIds[row.enrollment_id];
    }
  }

  isSelected(row: any): boolean {
    return !!this.selectedIds[row.enrollment_id];
  }

  selectAllVisible(checked: boolean) {
    this.visibleCustomers().forEach((row) => this.toggleCustomer(row, checked));
  }

  selectedMembers() {
    return this.customers.filter((row) => this.selectedIds[row.enrollment_id]);
  }

  saveGroup() {
    if (!this.form.group_name || !this.form.scheme_id) {
      this.appService.showAlert('Prized Show Group', 'Enter group name and choose a scheme.', 'error');
      return;
    }
    const members = this.selectedMembers();
    if (!members.length) {
      this.appService.showAlert('Prized Show Group', 'Select at least one customer.', 'error');
      return;
    }
    const scheme = this.schemes.find((s) => String(s.id) === String(this.form.scheme_id));
    const body = {
      tenant_id: localStorage.getItem('tenant_id'),
      branch_id: localStorage.getItem('branch_id') || 0,
      created_by: localStorage.getItem('logged_employee_id') || 0,
      group_name: this.form.group_name,
      scheme_id: this.form.scheme_id,
      scheme_name: this.schemeLabel(scheme),
      chit_value: scheme ? scheme.chit_value : 0,
      months: scheme ? scheme.no_of_months : 0,
      remarks: this.form.remarks || '',
      members: members
    };
    this.saving = true;
    this.userservice.getData(body, 'store-prized-show-group', 'JSON').subscribe((res: any) => {
      this.saving = false;
      if (res && res.status === 'Success') {
        this.appService.showAlert('Prized Show Group', res.msg || 'Saved', 'success');
        this.backToList();
      } else {
        this.appService.showAlert('Prized Show Group', (res && res.msg) || 'Unable to save', 'error');
      }
    }, () => {
      this.saving = false;
      this.appService.showAlert('Prized Show Group', 'Unable to save', 'error');
    });
  }

  openMark(group: any) {
    this.loading = true;
    this.userservice.getData({ id: group.id }, 'get-prized-show-group', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (!(res && res.status === 'Success' && res.data && res.data[0])) {
        this.appService.showAlert('Prized Show Group', 'Unable to open group', 'error');
        return;
      }
      this.activeGroup = res.data[0];
      this.markDraft = {};
      (this.activeGroup.schedule || []).forEach((row) => {
        this.markDraft[row.month_no] = {
          enrollment_id: row.enrollment_id || '',
          amount: row.amount || '',
          remarks: row.remarks || ''
        };
      });
      this.view = 'mark';
    }, () => {
      this.loading = false;
      this.appService.showAlert('Prized Show Group', 'Unable to open group', 'error');
    });
  }

  availableMembers(monthNo: number) {
    if (!this.activeGroup) { return []; }
    const taken = {};
    (this.activeGroup.schedule || []).forEach((row) => {
      if (row.is_marked && row.enrollment_id && Number(row.month_no) !== Number(monthNo)) {
        taken[row.enrollment_id] = true;
      }
    });
    return (this.activeGroup.members || []).filter((m) => !taken[m.enrollment_id]);
  }

  saveMonth(monthNo: number) {
    const draft = this.markDraft[monthNo] || {};
    if (!draft.enrollment_id || !draft.amount) {
      this.appService.showAlert('Prized Show Group', 'Select prized customer and amount for this month.', 'error');
      return;
    }
    const body = {
      group_id: this.activeGroup.id,
      month_no: monthNo,
      enrollment_id: draft.enrollment_id,
      amount: draft.amount,
      remarks: draft.remarks || '',
      marked_by: localStorage.getItem('logged_employee_id') || 0
    };
    this.saving = true;
    this.userservice.getData(body, 'mark-prized-show-month', 'JSON').subscribe((res: any) => {
      this.saving = false;
      if (res && res.status === 'Success') {
        this.appService.showAlert('Prized Show Group', res.msg || 'Saved', 'success');
        this.openMark({ id: this.activeGroup.id });
      } else {
        this.appService.showAlert('Prized Show Group', (res && res.msg) || 'Unable to save month', 'error');
      }
    }, () => {
      this.saving = false;
      this.appService.showAlert('Prized Show Group', 'Unable to save month', 'error');
    });
  }

  clearMonth(monthNo: number) {
    this.userservice.getData({
      group_id: this.activeGroup.id,
      month_no: monthNo
    }, 'clear-prized-show-month', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.openMark({ id: this.activeGroup.id });
      } else {
        this.appService.showAlert('Prized Show Group', (res && res.msg) || 'Unable to clear', 'error');
      }
    });
  }

  deleteGroup(group: any) {
    if (!confirm('Delete this show group? This does not affect customer ledger.')) {
      return;
    }
    this.userservice.getData({ id: group.id }, 'delete-prized-show-group', 'JSON').subscribe((res: any) => {
      if (res && res.status === 'Success') {
        this.appService.showAlert('Prized Show Group', res.msg || 'Deleted', 'success');
        this.loadGroups();
      } else {
        this.appService.showAlert('Prized Show Group', (res && res.msg) || 'Unable to delete', 'error');
      }
    });
  }
}
