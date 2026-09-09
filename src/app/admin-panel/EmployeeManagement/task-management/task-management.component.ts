import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AppService } from '../../../app.service';
import { UserService } from '../../../Services/user.service';

@Component({
  selector: 'app-task-management',
  templateUrl: './task-management.component.html',
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
      background: linear-gradient(135deg, #0f766e 0%, #025E2D 100%);
      color: #fff;
    }
    .hr-panel-head h3 { margin: 0; font-weight: 700; font-size: 1.2rem; }
    .hr-panel-head p { margin: .25rem 0 0; opacity: .85; font-size: .85rem; }
    .hr-actions { display: flex; gap: .5rem; align-items: center; }
    .hr-form { padding: 1.25rem; }
    .hr-form-actions { display: flex; gap: .5rem; }
    .hr-table-wrap { padding: 0 1.25rem 1.25rem; }
    .hr-table thead th {
      background: #f0fdf4;
      color: #14532d;
      border-top: 0;
      font-size: .82rem;
    }
    .hr-chip {
      display: inline-block;
      padding: .15rem .55rem;
      border-radius: 999px;
      background: #dbeafe;
      color: #1e40af;
      font-size: .72rem;
      font-weight: 700;
      text-transform: capitalize;
    }
    .hr-chip.status { background: #fef3c7; color: #92400e; }
  `]
})
export class TaskManagementComponent implements OnInit {
  form: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Assign Task';
  branchList: any[] = [];
  employeeList: any[] = [];
  taskList: any[] = [];
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

  ngOnInit() {
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);

    this.form = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      title: ['', Validators.required],
      description: [''],
      task_date: [`${y}-${m}-${d}`, Validators.required],
      due_date: [''],
      priority: ['medium'],
      created_by: [localStorage.getItem('logged_user_id')],
      assigned_by: [localStorage.getItem('logged_user_id')],
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
    this.loadTasks();
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
    this.loadTasks();
  }

  loadTasks() {
    this.TableHidden = true;
    const payload: any = {
      tenant_id: this.form.get('tenant_id').value || localStorage.getItem('tenant_id'),
      for_admin: 1
    };
    if (this.form.get('branch_id').value) {
      payload.branch_id = this.form.get('branch_id').value;
    }
    if (this.form.get('employee_id').value) {
      payload.employee_id = this.form.get('employee_id').value;
    }
    this.userservice.getData(payload, 'list-employee-tasks', 'JSON').subscribe((response: any) => {
      this.taskList = response && response.status === 'Success' ? (response.data || []) : [];
      this.TableHidden = false;
    }, () => {
      this.taskList = [];
      this.TableHidden = false;
    });
  }

  onSubmit() {
    this.submitted = true;
    this.disableBtn = true;
    this.SubmitbuttonName = 'Saving...';
    if (this.form.invalid) {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Assign Task';
      return;
    }
    const result = Object.assign({}, this.form.value);
    this.userservice.getData(result, 'store-employee-task', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Assign Task';
      this.disableBtn = false;
      if (response && response.status === 'Success') {
        this.appService.showAlert('Task Assignment', response.msg, 'success');
        this.form.patchValue({ title: '', description: '', due_date: '' });
        this.submitted = false;
        this.loadTasks();
      } else {
        this.appService.showAlert('Task Assignment', (response && response.msg) || 'Failed', 'error');
      }
    }, () => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Assign Task';
      this.appService.showAlert('Task Assignment', 'Something went wrong', 'error');
    });
  }

  deleteTask(row) {
    if (!confirm('Delete this task?')) {
      return;
    }
    this.userservice.getData({ id: row.id }, 'delete-employee-task', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Task Assignment', response.msg, 'success');
        this.loadTasks();
      } else {
        this.appService.showAlert('Task Assignment', (response && response.msg) || 'Delete failed', 'error');
      }
    });
  }
}
