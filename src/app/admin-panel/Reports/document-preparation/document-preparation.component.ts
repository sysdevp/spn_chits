import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-document-preparation',
  templateUrl: './document-preparation.component.html',
  styles: []
})
export class DocumentPreparationComponent implements OnInit {
  requestForm: FormGroup;
  payoutForm: FormGroup;
  branchList: any[] = [];
  companyList: any[] = [];
  enrollments: any[] = [];
  requests: any[] = [];
  profile: any = null;
  receipts: any[] = [];
  payments: any[] = [];
  paymentKind = 'prized';
  isSuperAdmin = false;
  loading = false;
  saving = false;
  selectedPayout: any = null;
  mdRemarks = '';
  needsAadhar = false;
  needsPan = false;

  constructor(
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private appService: AppService,
    private route: ActivatedRoute,
    private http: HttpClient
  ) {}

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    this.route.queryParams.subscribe((params) => {
      this.paymentKind = params['kind'] === 'closed' ? 'closed' : 'prized';
      if (this.requestForm) {
        this.requestForm.get('payment_kind').setValue(this.paymentKind);
        this.loadRequests();
      }
    });
    this.requestForm = this.formBuilder.group({
      tenant_id: [tenantId || '', Validators.required],
      branch_id: [localStorage.getItem('branch_id') || '0'],
      enrollment_id: ['', Validators.required],
      payment_kind: [this.paymentKind],
      ho_remarks: ['']
    });
    this.payoutForm = this.formBuilder.group({
      bank_name: ['', Validators.required],
      account_number: ['', Validators.required],
      ifsc: ['', Validators.required],
      cheque_no: [''],
      cheque_file: ['', Validators.required],
      aadhar_no: [''],
      aadhar_file: [''],
      pan_no: [''],
      pan_file: [''],
      home_photo: ['', Validators.required],
      nominee_name: ['', Validators.required],
      nominee_relation: [''],
      nominee_mobile: [''],
      nominee_address: ['']
    });
    if (tenantId) {
      this.setBranchList(tenantId);
      this.loadEnrollments();
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    }
    this.loadRequests();
  }

  get kindLabel() {
    return this.paymentKind === 'closed' ? 'Closed Payment' : 'Prized Payment';
  }

  companyChg(event: any) {
    this.requestForm.get('branch_id').setValue('0');
    this.requestForm.get('enrollment_id').setValue('');
    this.clearHistory();
    this.setBranchList(event.target.value);
    this.loadEnrollments();
  }

  branchChg() {
    this.requestForm.get('enrollment_id').setValue('');
    this.clearHistory();
    this.loadEnrollments();
  }

  setBranchList(tenantId: string) {
    if (!tenantId) {
      this.branchList = [];
      return;
    }
    this.userservice.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  loadEnrollments() {
    const tenantId = this.requestForm.get('tenant_id').value;
    const branchId = this.requestForm.get('branch_id').value;
    if (!tenantId) {
      this.enrollments = [];
      return;
    }
    this.userservice.getData({
      tenant_id: tenantId,
      branch_id: branchId && branchId !== '0' ? branchId : ''
    }, 'list-commitment-enrolled-customers', 'JSON').subscribe((res: any) => {
      this.enrollments = res.status === 'Success' ? (res.data || []) : [];
    });
  }

  customerLabel(enrol: any) {
    const info = enrol.customer_info || {};
    const name = ((info.name || '') + ' ' + (info.last_name || '')).trim();
    const ar = enrol.ar_no || enrol.ticket_no || enrol.id;
    return (name || 'Customer') + ' / ' + ar;
  }

  onCustomerChange() {
    const enrollmentId = this.requestForm.get('enrollment_id').value;
    if (!enrollmentId) {
      this.clearHistory();
      return;
    }
    this.userservice.getData({ enrollment_id: enrollmentId }, 'document-preparation-history', 'JSON').subscribe((res: any) => {
      if (res.status === 'Success') {
        this.profile = res.profile || null;
        this.receipts = res.receipts || [];
        this.payments = res.payments || [];
        this.needsAadhar = !(this.profile && this.profile.aadhar);
        this.needsPan = !(this.profile && this.profile.pan);
      } else {
        this.clearHistory();
        this.appService.showAlert(this.kindLabel, res.msg || 'History not found', 'error');
      }
    });
  }

  clearHistory() {
    this.profile = null;
    this.receipts = [];
    this.payments = [];
  }

  createRequest() {
    if (this.requestForm.invalid) {
      this.appService.showAlert(this.kindLabel, 'Select a customer enrollment', 'error');
      return;
    }
    const enrol = this.enrollments.find((row) => String(row.id) === String(this.requestForm.get('enrollment_id').value));
    const customerId = enrol && enrol.customer_id ? enrol.customer_id : (enrol && enrol.customer_info ? enrol.customer_info.id : 0);
    this.saving = true;
    this.userservice.getData({
      tenant_id: this.requestForm.get('tenant_id').value,
      branch_id: enrol ? enrol.branch_id : this.requestForm.get('branch_id').value,
      customer_id: customerId,
      enrollment_id: this.requestForm.get('enrollment_id').value,
      payment_kind: this.paymentKind,
      ho_remarks: this.requestForm.get('ho_remarks').value,
      ho_by: localStorage.getItem('logged_employee_id')
    }, 'store-document-preparation', 'JSON').subscribe((res: any) => {
      this.saving = false;
      if (res.status === 'Success') {
        this.appService.showAlert(this.kindLabel, res.msg, 'success');
        this.requestForm.get('ho_remarks').setValue('');
        this.loadRequests();
      } else {
        this.appService.showAlert(this.kindLabel, res.msg || 'Failed', 'error');
      }
    }, () => {
      this.saving = false;
      this.appService.showAlert(this.kindLabel, 'Unable to create request', 'error');
    });
  }

  loadRequests() {
    this.loading = true;
    this.userservice.getData({
      tenant_id: this.requestForm ? this.requestForm.get('tenant_id').value : localStorage.getItem('tenant_id'),
      branch_id: this.requestForm ? this.requestForm.get('branch_id').value : 0,
      payment_kind: this.paymentKind
    }, 'list-document-preparations', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.requests = res.status === 'Success' ? (res.data || []) : [];
    }, () => {
      this.loading = false;
    });
  }

  stageLabel(stage: string) {
    const labels = {
      ho_requested: 'HO requested',
      branch_filled: 'Branch confirmed customer',
      md_approved: 'MD approved',
      md_rejected: 'MD rejected',
      docs_submitted: 'Documents submitted'
    };
    return labels[stage] || stage;
  }

  confirmCustomer(row: any) {
    this.userservice.getData({
      id: row.id,
      branch_by: localStorage.getItem('logged_employee_id')
    }, 'branch-fill-document-preparation', 'JSON').subscribe((res: any) => {
      this.appService.showAlert('Document Preparation', res.msg || 'Updated', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.loadRequests();
      }
    });
  }

  mdDecide(row: any, decision: string) {
    this.userservice.getData({
      id: row.id,
      decision: decision,
      md_remarks: this.mdRemarks || '',
      md_by: localStorage.getItem('logged_employee_id')
    }, 'md-decide-document-preparation', 'JSON').subscribe((res: any) => {
      this.appService.showAlert('Document Preparation', res.msg || 'Updated', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.mdRemarks = '';
        this.loadRequests();
      }
    });
  }

  openPayout(row: any) {
    this.selectedPayout = row;
    this.needsAadhar = !row.has_aadhar;
    this.needsPan = !row.has_pan;
    this.payoutForm.reset({
      bank_name: row.bank_name || '',
      account_number: row.account_number || '',
      ifsc: row.ifsc || '',
      cheque_no: row.cheque_no || '',
      cheque_file: '',
      aadhar_no: row.aadhar_no || '',
      aadhar_file: '',
      pan_no: row.pan_no || '',
      pan_file: '',
      home_photo: '',
      nominee_name: row.nominee_name || '',
      nominee_relation: row.nominee_relation || '',
      nominee_mobile: row.nominee_mobile || '',
      nominee_address: row.nominee_address || ''
    });
  }

  onFile(event: any, control: string) {
    const file = event.target.files && event.target.files[0];
    if (!file) {
      return;
    }
    const token = localStorage.getItem('token');
    let headers = new HttpHeaders();
    if (token) {
      headers = headers.set('Authorization', 'Bearer ' + token);
    }
    const formData = new FormData();
    formData.append('file', file, file.name);
    formData.append('database_name', localStorage.getItem('database_name') || '');
    this.http.post(environment.API + 'upload-document-preparation', formData, { headers: headers }).subscribe((res: any) => {
      if (res.status === 'Success' && res.file_name) {
        this.payoutForm.get(control).setValue(res.file_name);
        this.appService.showAlert('Upload', 'File uploaded', 'success');
      } else {
        this.appService.showAlert('Upload', (res && res.msg) || 'Upload failed', 'error');
      }
    }, () => {
      this.appService.showAlert('Upload', 'Upload failed', 'error');
    });
  }

  submitPayout() {
    if (!this.selectedPayout) {
      return;
    }
    if (this.needsAadhar && (!this.payoutForm.get('aadhar_no').value || !this.payoutForm.get('aadhar_file').value)) {
      this.appService.showAlert('Documents', 'Enter and upload Aadhaar', 'error');
      return;
    }
    if (this.needsPan && (!this.payoutForm.get('pan_no').value || !this.payoutForm.get('pan_file').value)) {
      this.appService.showAlert('Documents', 'Enter and upload PAN', 'error');
      return;
    }
    if (this.payoutForm.invalid) {
      this.appService.showAlert('Documents', 'Bank, cheque, home photo and nominee name are required', 'error');
      return;
    }
    this.saving = true;
    this.userservice.getData(Object.assign({
      id: this.selectedPayout.id,
      branch_by: localStorage.getItem('logged_employee_id')
    }, this.payoutForm.value), 'submit-document-payout', 'JSON').subscribe((res: any) => {
      this.saving = false;
      this.appService.showAlert('Documents', res.msg || 'Saved', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.selectedPayout = null;
        this.loadRequests();
      }
    }, () => {
      this.saving = false;
      this.appService.showAlert('Documents', 'Unable to save documents', 'error');
    });
  }

  showHistory(row: any) {
    this.requestForm.get('enrollment_id').setValue(row.enrollment_id);
    this.onCustomerChange();
  }
}
