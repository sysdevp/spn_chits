import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-payment-desk',
  templateUrl: './payment-desk.component.html',
  styleUrls: ['./payment-desk.component.css']
})
export class PaymentDeskComponent implements OnInit {
  mode = 'prized';
  filterForm: FormGroup;
  form: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  enrollments: any[] = [];
  rows: any[] = [];
  allRows: any[] = [];
  summary: any = {};
  showForm = false;
  saving = false;
  remarks = '';
  splits: any[] = [];
  docs: any = {};
  tab = 'all';
  searchText = '';
  customerQuery = '';
  selected: any = null;
  panelOpen = false;
  step = 1;
  page = 1;
  pageSize = 10;
  historyLoading = false;
  customerEnrollments: any[] = [];
  currentReceipts: any[] = [];
  currentPayments: any[] = [];
  deskHistory: any[] = [];
  historyPaid = 0;
  historyPending = 0;
  historyReleased = 0;
  paymentMonths: any[] = [];
  dueLoading = false;
  maxStep = 3;
  stage = 'all';
  showHistory = false;
  prizedIds: string[] = [];
  act: any = {};
  employeesByBranch: any = {};
  loggedEmployeeId = localStorage.getItem('logged_employee_id') || '';
  payoutModes = ['Cash', 'NEFT', 'RTGS', 'IMPS', 'UPI', 'Cheque'];

  constructor(
    private fb: FormBuilder,
    private userservice: UserService,
    private appService: AppService,
    private route: ActivatedRoute,
    private http: HttpClient
  ) {}

  ngOnInit() {
    this.mode = (this.route.snapshot.data && this.route.snapshot.data.desk) || 'prized';
    this.stage = (this.route.snapshot.data && this.route.snapshot.data.stage) || 'all';
    const tenantId = localStorage.getItem('tenant_id');
    this.filterForm = this.fb.group({
      tenant_id: [tenantId || ''],
      branch_id: ['0'],
      status: [this.mode === 'pending' ? 'awaiting' : 'all'],
      from_date: [''],
      to_date: ['']
    });
    this.form = this.fb.group({
      enrollment_id: ['', Validators.required],
      payment_month: [''],
      amount: ['', Validators.required],
      document_charges: [0],
      govt_fees: [0],
      deduction: [0],
      reason: [''],
      remarks: [''],
      prized_month: [''],
      closed_month: [''],
      year_label: [String(new Date().getFullYear())]
    });
    if (tenantId) {
      this.setBranches(tenantId);
      this.loadCustomers();
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    }
    this.load();
  }

  get title() {
    return {
      dashboard: 'Payments Dashboard',
      prized: 'Prized Payment',
      closed: 'Final Closed Payment',
      refund: 'Refund Payment',
      pending: this.stage === 'accounts' ? 'Accounts Payout Approval' : 'Pending Payment Approvals'
    }[this.mode] || 'Payments';
  }

  get enrol() {
    return this.enrollments.find((e) => String(e.id) === String(this.form.get('enrollment_id').value)) || null;
  }

  get info() {
    return this.enrol && this.enrol.customer_info ? this.enrol.customer_info : {};
  }

  get customerName() {
    return ((this.info.name || '') + ' ' + (this.info.last_name || '')).trim();
  }

  companyChg(event: any) {
    this.filterForm.get('branch_id').setValue('0');
    this.setBranches(event.target.value);
    this.loadCustomers();
  }

  setBranches(tenantId: string) {
    this.userservice.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
      this.splits = this.branchList.map((b) => ({ branch_id: b.id, branch_name: b.branch_name, selected: false, amount: 0, pending: 0 }));
    });
  }

  loadCustomers() {
    const tenantId = this.filterForm.get('tenant_id').value;
    if (!tenantId) {
      this.enrollments = [];
      return;
    }
    const branchId = this.filterForm.get('branch_id').value;
    const payload: any = { tenant_id: tenantId };
    if (branchId && String(branchId) !== '0') {
      payload.branch_id = branchId;
    }
    this.userservice.getData(payload, 'list-commitment-enrolled-customers', 'POST').subscribe((res: any) => {
      this.enrollments = res && res.status === 'Success' ? (res.data || []) : [];
    }, () => {
      this.enrollments = [];
    });
  }

  visibleCustomers() {
    const q = (this.customerQuery || '').toLowerCase();
    return this.enrollments.filter((enrol) => {
      if (this.mode === 'prized' && this.prizedIds.indexOf(String(enrol.id)) >= 0) return false;
      if (!q) return true;
      const info = enrol.customer_info || {};
      const blob = (
        (info.name || '') + ' ' + (info.last_name || '') + ' ' +
        (info.mobile_no || info.mobile || '') + ' ' + (enrol.ar_no || '') + ' ' + (info.customer_code || '')
      ).toLowerCase();
      return blob.indexOf(q) >= 0;
    });
  }

  label(enrol: any) {
    const info = enrol.customer_info || {};
    const name = ((info.name || '') + ' ' + (info.last_name || '')).trim();
    return (name || 'Customer') + ' / ' + (enrol.ar_no || enrol.id);
  }

  schemeInfo(enrol: any) {
    if (!enrol) { return {}; }
    return enrol.scheme_info || enrol.scheme_det || {};
  }

  schemeName(enrol: any) {
    if (!enrol) { return ''; }
    if (enrol.scheme_name || enrol.chit_name) {
      return enrol.scheme_name || enrol.chit_name;
    }
    const scheme = this.schemeInfo(enrol);
    if (!scheme || !Object.keys(scheme).length) {
      return '';
    }
    const value = scheme.chit_value || '';
    const months = scheme.no_of_months || '';
    const letter = scheme.chit_letter || '';
    return (value ? ('₹' + Number(value).toLocaleString('en-IN')) : '') +
      (months ? (' (' + months + ' M)') : '') +
      (letter ? (' / ' + letter) : '');
  }

  schemeValue(enrol: any) {
    if (!enrol) { return 0; }
    const scheme = this.schemeInfo(enrol);
    return Number(enrol.chit_value || scheme.chit_value || 0);
  }

  schemeMonths(enrol: any) {
    if (!enrol) { return ''; }
    const scheme = this.schemeInfo(enrol);
    return enrol.no_of_months || scheme.no_of_months || enrol.months || '';
  }

  slabName(enrol: any) {
    if (!enrol) { return ''; }
    if (enrol.slab_name) { return enrol.slab_name; }
    const slab = enrol.slab_info || enrol.slab_due_info || {};
    return slab.slab_name || '';
  }

  clearHistory() {
    this.customerEnrollments = [];
    this.currentReceipts = [];
    this.currentPayments = [];
    this.deskHistory = [];
    this.historyPaid = 0;
    this.historyPending = 0;
    this.historyReleased = 0;
  }

  loadCustomerHistory(enrol: any) {
    this.clearHistory();
    if (!enrol || !enrol.customer_id) {
      return;
    }
    this.historyLoading = true;
    const customerId = enrol.customer_id;
    const enrolId = enrol.id;
    let pending = 3;
    const done = () => {
      pending--;
      if (pending <= 0) {
        this.historyLoading = false;
      }
    };

    this.userservice.getData({ customer_id: customerId }, 'enrollment-wise-details', 'POST').subscribe((res: any) => {
      const rows = res && res.status === 'Success' ? (res.data || []) : [];
      this.customerEnrollments = rows;
      let paid = 0;
      let pendingAmt = 0;
      rows.forEach((row) => {
        paid += Number(row.paid_amount || 0);
        pendingAmt += Number(row.pending_amount || 0);
      });
      this.historyPaid = paid;
      this.historyPending = pendingAmt;
      done();
    }, () => done());

    this.userservice.getData({ 'conditions[id]': enrolId }, 'get-auctionwise-receipt', 'POST').subscribe((res: any) => {
      this.currentReceipts = res && res.status === 'Success' ? (res.data || []) : [];
      done();
    }, () => {
      this.currentReceipts = [];
      done();
    });

    this.userservice.getData({ 'conditions[enrollment_id]': enrolId }, 'get-commitment-payments', 'POST').subscribe((res: any) => {
      const rows = res && res.status === 'Success' ? (res.data || []) : [];
      this.currentPayments = rows;
      let released = 0;
      rows.forEach((row) => {
        released += Number(row.total_amount || row.net_amount || row.amount || 0);
      });
      this.historyReleased = released;
      done();
    }, () => {
      this.currentPayments = [];
      done();
    });

    const tenantId = this.filterForm.get('tenant_id').value;
    this.userservice.getData({
      tenant_id: tenantId,
      branch_id: 0,
      status: 'all'
    }, 'list-payment-desk', 'JSON').subscribe((res: any) => {
      const rows = res && res.status === 'Success' ? (res.data || []) : [];
      this.deskHistory = rows.filter((row) =>
        String(row.customer_id) === String(customerId) || String(row.enrollment_id) === String(enrolId)
      );
    }, () => {
      this.deskHistory = [];
    });
  }

  activeEnrollmentId() {
    if (this.form && this.form.get('enrollment_id') && this.form.get('enrollment_id').value) {
      return this.form.get('enrollment_id').value;
    }
    return this.selected ? this.selected.enrollment_id : null;
  }

  currentEnrollmentHistory() {
    const id = this.activeEnrollmentId();
    return this.customerEnrollments.filter((row) => String(row.id) === String(id));
  }

  otherEnrollmentHistory() {
    const id = this.activeEnrollmentId();
    return this.customerEnrollments.filter((row) => String(row.id) !== String(id));
  }

  thisChitPending() {
    return this.currentEnrollmentHistory().reduce((s, row) => s + Number(row.pending_amount || 0), 0);
  }

  oldChitPending() {
    return this.otherEnrollmentHistory().reduce((s, row) => s + Number(row.pending_amount || 0), 0);
  }

  chitTypeLabel(row: any) {
    const type = Number(row && row.chit_type);
    if (type === 3) { return 'SPN / Commitment'; }
    if (type === 2) { return 'Group chit'; }
    if (type === 1) { return 'Pigmy'; }
    return 'Chit';
  }

  receiptAmount(row: any) {
    return Number(row.received_amount || row.amount_paid || row.paid_amount || row.total_paid || 0);
  }

  paymentAmount(row: any) {
    return Number(row.total_amount || row.net_amount || row.amount || 0);
  }

  load() {
    const payload: any = {
      tenant_id: this.filterForm.get('tenant_id').value,
      branch_id: this.filterForm.get('branch_id').value,
      status: this.mode === 'dashboard' ? 'all' : this.filterForm.get('status').value
    };
    if (this.mode !== 'pending' && this.mode !== 'dashboard') payload.kind = this.mode;
    if (this.mode === 'pending' && this.stage === 'accounts') payload.status = 'pending_accounts';
    this.userservice.getData(payload, 'list-payment-desk', 'JSON').subscribe((res: any) => {
      this.rows = res.status === 'Success' ? res.data || [] : [];
      this.allRows = this.rows.slice();
      this.summary = res.summary || {};
      this.prizedIds = (res.prized_enrollment_ids || []).map((id) => String(id));
      this.page = 1;
      this.rows.forEach((row) => {
        if (!this.act[row.id]) {
          this.act[row.id] = {
            field_employee_id: row.field_employee_id || '',
            payment_mode: '',
            payment_date: new Date().toISOString().slice(0, 10),
            transaction_no: '',
            bank_name: '',
            remarks: ''
          };
        }
        if (row.status === 'pending_chit_admin') this.loadBranchEmployees(row.branch_id);
      });
    }, () => {
      this.rows = [];
      this.allRows = [];
    });
  }

  money(n: any) {
    const v = Number(n || 0);
    return '₹ ' + v.toLocaleString('en-IN');
  }

  net() {
    const amount = Number(this.form.get('amount').value || 0);
    const docs = Number(this.form.get('document_charges').value || 0);
    const govt = Number(this.form.get('govt_fees').value || 0);
    return Math.max(0, amount - docs - govt);
  }

  splitTotal() {
    return 0;
  }

  loadDueForMonth(monthNo?: any) {
    const enrol = this.enrol;
    if (!enrol) {
      this.paymentMonths = [];
      return;
    }
    this.dueLoading = true;
    const cond: any = { 'conditions[enrol_id]': enrol.id };
    if (monthNo) {
      cond['conditions[payment_month]'] = monthNo;
    }
    this.userservice.getData(cond, 'get-customer-commitment-details', 'POST').subscribe((res: any) => {
      this.dueLoading = false;
      if (!(res && res.status === 'Success' && res.data)) {
        this.buildFallbackMonths(enrol);
        return;
      }
      const data = res.data;
      this.paymentMonths = data.payment_months && data.payment_months.length
        ? data.payment_months
        : this.fallbackMonthList(enrol, data.max_payment_months);
      const month = data.payment_month || monthNo || 1;
      const amount = Number(data.slab_payment || 0);
      this.form.get('payment_month').setValue(String(month));
      this.form.get('amount').setValue(amount);
      const label = 'Month ' + month;
      if (this.mode === 'prized') {
        this.form.get('prized_month').setValue(label);
      }
      if (this.mode === 'closed') {
        this.form.get('closed_month').setValue(label);
      }
      if (!this.form.get('year_label').value) {
        this.form.get('year_label').setValue(String(new Date().getFullYear()));
      }
    }, () => {
      this.dueLoading = false;
      this.buildFallbackMonths(enrol);
    });
  }

  private fallbackMonthList(enrol: any, maxMonths?: any) {
    const total = Number(maxMonths || this.schemeMonths(enrol) || 12);
    const list = [];
    for (let m = 1; m <= total; m++) {
      list.push({ month: m, amount: 0 });
    }
    return list;
  }

  private buildFallbackMonths(enrol: any) {
    this.paymentMonths = this.fallbackMonthList(enrol);
    if (!this.form.get('payment_month').value && this.paymentMonths.length) {
      this.form.get('payment_month').setValue(String(this.paymentMonths[0].month));
    }
  }

  onPaymentMonthChange(monthNo: any) {
    this.loadDueForMonth(monthNo);
  }

  statusText(row: any) {
    if (!row) return '';
    if (row.status === 'approved') return row.payment_mode ? 'Paid' : 'Processed';
    if (row.status === 'rejected') return 'Cancelled';
    if (row.status === 'draft') return 'Draft';
    if (row.status === 'pending_chit_admin') return 'Chit admin approval';
    if (row.status === 'pending_field') return row.kind === 'closed' ? 'Field verification (after payout)' : 'Field verification';
    if (row.status === 'pending_accounts' || row.status === 'pending_admin') return 'Accounts payout';
    return 'Pending';
  }

  statusClass(row: any) {
    if (!row) return '';
    if (row.status === 'approved') return 'st-ok';
    if (row.status === 'rejected') return 'st-bad';
    if (row.status === 'pending_field') return 'st-field';
    return 'st-wait';
  }

  isOpen(row: any) {
    return row && row.status !== 'approved' && row.status !== 'rejected';
  }

  isAccountsStage(row: any) {
    return row && (row.status === 'pending_accounts' || row.status === 'pending_admin');
  }

  stageRows() {
    return this.rows.filter((row) => {
      if (this.stage === 'chit_admin') return row.status === 'pending_chit_admin';
      if (this.stage === 'field') return row.status === 'pending_field';
      if (this.stage === 'accounts') return this.isAccountsStage(row);
      if (this.stage === 'mine') return String(row.field_employee_id) === String(this.loggedEmployeeId);
      return true;
    });
  }

  loadBranchEmployees(branchId: any) {
    const key = String(branchId || 0);
    if (this.employeesByBranch[key]) return;
    this.employeesByBranch[key] = [];
    const cond: any = {};
    if (branchId && key !== '0') cond['conditions[branch_id]'] = branchId;
    this.userservice.getData(cond, 'get-employees', 'POST').subscribe((res: any) => {
      this.employeesByBranch[key] = res && res.status === 'Success' ? (res.data || []) : [];
    });
  }

  branchEmployees(row: any) {
    return this.employeesByBranch[String(row.branch_id || 0)] || [];
  }

  submitDraft(row: any) {
    this.userservice.getData({ id: row.id }, 'submit-draft-payment-desk', 'JSON').subscribe((res: any) => {
      this.appService.showAlert(this.title, res.msg || 'Updated', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.panelOpen = false;
        this.load();
      }
    });
  }

  filtered() {
    const q = (this.searchText || '').toLowerCase();
    return this.rows.filter((row) => {
      const st = this.statusText(row);
      if (this.tab === 'pending' && !this.isOpen(row)) return false;
      if (this.tab === 'processed' && row.status !== 'approved') return false;
      if (this.tab === 'cancelled' && st !== 'Cancelled') return false;
      if (!q) return true;
      const blob = (row.customer_name + ' ' + row.mobile + ' ' + row.ar_no + ' ' + row.kind).toLowerCase();
      return blob.indexOf(q) >= 0;
    });
  }

  paged() {
    const list = this.filtered();
    const start = (this.page - 1) * this.pageSize;
    return list.slice(start, start + this.pageSize);
  }

  pageCount() {
    return Math.max(1, Math.ceil(this.filtered().length / this.pageSize));
  }

  pages() {
    const n = this.pageCount();
    const out = [];
    for (let i = 1; i <= n && i <= 8; i++) out.push(i);
    return out;
  }

  countTab(name: string) {
    const prev = this.tab;
    this.tab = name;
    const n = this.filtered().length;
    this.tab = prev;
    return n;
  }

  sumKind(kind: string) {
    return this.allRows.filter((r) => r.kind === kind).reduce((n, r) => n + Number(r.net_amount || 0), 0);
  }

  countKind(kind: string) {
    return this.allRows.filter((r) => r.kind === kind).length;
  }

  branchBars() {
    const map: any = {};
    this.rows.forEach((r) => {
      const name = r.branch_name || ('Branch ' + (r.branch_id || ''));
      map[name] = (map[name] || 0) + Number(r.net_amount || 0);
    });
    const list = Object.keys(map).map((k) => ({ name: k, amount: map[k] }));
    const max = list.reduce((m, x) => Math.max(m, x.amount), 1);
    return list.map((x) => ({ name: x.name, amount: x.amount, width: Math.round((x.amount / max) * 100) }));
  }

  openCreate() {
    this.loadCustomers();
    this.customerQuery = '';
    this.selected = null;
    this.panelOpen = false;
    this.showForm = true;
    this.step = 1;
    this.docs = {};
    this.clearHistory();
    this.paymentMonths = [];
    this.form.reset({
      enrollment_id: '',
      payment_month: '',
      amount: '',
      document_charges: 0,
      govt_fees: 0,
      deduction: 0,
      reason: '',
      remarks: '',
      prized_month: '',
      closed_month: '',
      year_label: String(new Date().getFullYear())
    });
  }

  openRow(row: any) {
    this.selected = row;
    this.panelOpen = true;
    this.showForm = false;
    this.loadCustomerHistory({
      id: row.enrollment_id,
      customer_id: row.customer_id,
      ar_no: row.ar_no,
      scheme_name: row.scheme_name,
      chit_value: row.chit_value
    });
  }

  onCreateBranchChange(branchId: any) {
    this.filterForm.get('branch_id').setValue(branchId || '0');
    this.form.get('enrollment_id').setValue('');
    this.customerQuery = '';
    this.clearHistory();
    this.loadCustomers();
  }

  pickCustomer(enrol: any) {
    this.form.get('enrollment_id').setValue(enrol.id);
    this.loadCustomerHistory(enrol);
    this.loadDueForMonth();
  }

  onFile(event: any, key: string) {
    const file = event.target.files && event.target.files[0];
    if (!file) return;
    const token = localStorage.getItem('token');
    let headers = new HttpHeaders();
    if (token) headers = headers.set('Authorization', 'Bearer ' + token);
    const body = new FormData();
    body.append('file', file, file.name);
    body.append('database_name', localStorage.getItem('database_name') || '');
    this.http.post(environment.API + 'upload-payment-desk', body, { headers }).subscribe((res: any) => {
      if (res.status === 'Success') {
        this.docs[key] = res.file_name;
        this.appService.showAlert('Upload', key + ' uploaded', 'success');
      } else {
        this.appService.showAlert('Upload', res.msg || 'Failed', 'error');
      }
    });
  }

  save(action: string) {
    if (this.form.invalid) {
      this.appService.showAlert(this.title, 'Select customer and enter amount', 'error');
      return;
    }
    const enrol = this.enrol;
    const info = this.info;
    this.saving = true;
    const docs = Number(this.form.get('document_charges').value || 0);
    const govt = Number(this.form.get('govt_fees').value || 0);
    this.userservice.getData({
      action,
      kind: this.mode === 'pending' || this.mode === 'dashboard' ? 'prized' : this.mode,
      tenant_id: this.filterForm.get('tenant_id').value,
      branch_id: enrol ? enrol.branch_id : this.filterForm.get('branch_id').value,
      customer_id: enrol ? enrol.customer_id : 0,
      enrollment_id: this.form.get('enrollment_id').value,
      customer_name: this.customerName,
      mobile: info.mobile_no || info.mobile || '',
      ar_no: enrol ? enrol.ar_no : '',
      scheme_name: enrol ? this.schemeName(enrol) : '',
      chit_value: enrol ? (this.schemeValue(enrol) || this.form.get('amount').value) : this.form.get('amount').value,
      months: enrol ? this.schemeMonths(enrol) : '',
      payment_month: this.form.get('payment_month').value,
      amount: this.form.get('amount').value,
      document_charges: docs,
      govt_fees: govt,
      deduction: docs + govt,
      net_amount: this.net(),
      reason: this.form.get('reason').value,
      remarks: this.form.get('remarks').value,
      prized_month: this.form.get('prized_month').value || ('Month ' + this.form.get('payment_month').value),
      closed_month: this.form.get('closed_month').value || ('Month ' + this.form.get('payment_month').value),
      year_label: this.form.get('year_label').value,
      branch_split: JSON.stringify([]),
      documents: JSON.stringify(this.docs),
      created_by: localStorage.getItem('logged_employee_id')
    }, 'store-payment-desk', 'JSON').subscribe((res: any) => {
      this.saving = false;
      this.appService.showAlert(this.title, res.msg || 'Saved', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.showForm = false;
        this.load();
      }
    }, () => { this.saving = false; });
  }

  decide(row: any, decision: string) {
    const a = this.act[row.id] || {};
    this.userservice.getData({
      id: row.id,
      decision,
      remarks: a.remarks || this.remarks,
      approved_by: localStorage.getItem('logged_employee_id'),
      field_employee_id: a.field_employee_id,
      payment_mode: a.payment_mode,
      payment_date: a.payment_date,
      transaction_no: a.transaction_no,
      bank_name: a.bank_name
    }, 'approve-payment-desk', 'JSON').subscribe((res: any) => {
      this.appService.showAlert('Approval', res.msg || 'Updated', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') this.load();
    });
  }

  resetFilters() {
    this.searchText = '';
    this.tab = 'all';
    this.filterForm.patchValue({ branch_id: '0', status: this.mode === 'pending' ? 'awaiting' : 'all' });
    this.load();
  }
}
