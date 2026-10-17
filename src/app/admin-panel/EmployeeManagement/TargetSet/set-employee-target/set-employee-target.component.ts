import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { UserService } from '../../../../Services/user.service';

@Component({
  selector: 'app-set-employee-target',
  templateUrl: './set-employee-target.component.html',
  styles: [`
    .hr-panel {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 10px 28px rgba(1,54,24,.08);
      overflow: hidden;
    }
    .hr-panel-head {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 12px;
      padding: 1.1rem 1.25rem;
      background: linear-gradient(135deg, #013618 0%, #025E2D 100%);
      color: #fff;
    }
    .hr-panel-head h3 { margin: 0; font-weight: 700; font-size: 1.2rem; }
    .hr-panel-head p { margin: .25rem 0 0; opacity: .85; font-size: .85rem; }
    .hr-actions { display: flex; gap: .5rem; align-items: center; }
    .hr-actions .btn { font-weight: 600; }
    .hr-form { padding: 1.25rem; }
    .hr-form-actions { display: flex; gap: .5rem; margin-top: .25rem; }
    .hr-table-wrap { padding: 0 1.25rem 1.25rem; }
    .hr-table thead th {
      background: #f0fdf4;
      color: #14532d;
      border-top: 0;
      font-size: .82rem;
      white-space: nowrap;
    }
    .hr-chip {
      display: inline-block;
      padding: .15rem .55rem;
      border-radius: 999px;
      background: #dcfce7;
      color: #166534;
      font-size: .72rem;
      font-weight: 700;
    }
    .hr-progress {
      height: 6px;
      background: #e2e8f0;
      border-radius: 999px;
      overflow: hidden;
      margin-bottom: 4px;
      min-width: 70px;
    }
    .hr-progress span {
      display: block;
      height: 100%;
      background: linear-gradient(90deg, #025E2D, #20C25B);
    }
  `]
})
export class SetEmployeeTargetComponent implements OnInit {
  form: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Save Target';
  branchList: any[] = [];
  employeeList: any[] = [];
  targetList: any[] = [];
  isSuperAdmin = false;
  companyList: any[] = [];
  TableHidden = true;

  constructor(
    private appService: AppService,
    private formBuilder: FormBuilder,
    private userservice: UserService
  ) {}

  get f() {
    return this.form.controls;
  }

  typeLabel(type: any): string {
    const t = String(type || '');
    if (t === 'existing_client_collection' || t === 'collection') return 'Existing Client Collection';
    if (t === 'new_client_collection') return 'New Client Collection';
    if (t === 'new_business' || t === 'sales') return 'New Business';
    if (t === 'activity') return 'Activity';
    return t || '-';
  }

  onTargetTypeChg() {
    const type = this.form.get('target_type').value;
    const labels: any = {
      existing_client_collection: 'Existing Client Collection',
      new_client_collection: 'New Client Collection',
      new_business: 'New Business',
      activity: 'Activity'
    };
    this.form.get('target_label').setValue(labels[type] || 'Target');
  }

  ngOnInit() {
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);

    this.form = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      period_type: ['monthly', Validators.required],
      target_type: ['existing_client_collection', Validators.required],
      period_start: [`${y}-${m}-${d}`, Validators.required],
      target_amount: [''],
      business_value: [''],
      achieved_amount: [0],
      target_label: ['Existing Client Collection'],
      notes: [''],
      created_by: [localStorage.getItem('logged_user_id')],
    });

    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.form.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status === 'Success' ? response.data : [];
      });
    }
    this.loadTargets();
  }

  companyChg(event) {
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1,
      log_id: localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      this.branchList = response && response.status === 'Success' ? response.data : [];
    });
  }

  branchChg(event) {
    const branch_id = event.target.value;
    this.userservice.getData({ 'conditions[branch_id]': branch_id }, 'get-employees', 'POST').subscribe((response: any) => {
      this.employeeList = response && response.status === 'Success' ? response.data : [];
    });
    this.loadTargets();
  }

  loadTargets() {
    this.TableHidden = true;
    const payload: any = {
      tenant_id: this.form.get('tenant_id').value || localStorage.getItem('tenant_id')
    };
    if (this.form.get('branch_id').value) {
      payload.branch_id = this.form.get('branch_id').value;
    }
    if (this.form.get('employee_id').value) {
      payload.employee_id = this.form.get('employee_id').value;
    }
    if (this.form.get('period_type').value) {
      payload.period_type = this.form.get('period_type').value;
    }
    this.userservice.getData(payload, 'list-employee-targets', 'JSON').subscribe((response: any) => {
      this.targetList = response && response.status === 'Success' ? (response.data || []) : [];
      this.TableHidden = false;
    }, () => {
      this.targetList = [];
      this.TableHidden = false;
    });
  }

  amountMissing(): boolean {
    return !(Number(this.form.get('target_amount').value) > 0) && !(Number(this.form.get('business_value').value) > 0);
  }

  onSubmit() {
    this.submitted = true;
    this.disableBtn = true;
    this.SubmitbuttonName = 'Saving...';
    if (this.form.invalid || this.amountMissing()) {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Save Target';
      return;
    }
    const result = Object.assign({}, this.form.value);
    this.userservice.getData(result, 'store-employee-target', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Save Target';
      this.disableBtn = false;
      if (response && response.status === 'Success') {
        this.appService.showAlert('Target Set', response.msg, 'success');
        this.submitted = false;
        this.loadTargets();
      } else {
        this.appService.showAlert('Target Set', (response && response.msg) || 'Failed', 'error');
      }
    }, () => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Save Target';
      this.appService.showAlert('Target Set', 'Something went wrong', 'error');
    });
  }

  deleteTarget(row) {
    if (!confirm('Delete this target?')) {
      return;
    }
    this.userservice.getData({ id: row.id }, 'delete-employee-target', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Target Set', response.msg, 'success');
        this.loadTargets();
      }
    });
  }
}
