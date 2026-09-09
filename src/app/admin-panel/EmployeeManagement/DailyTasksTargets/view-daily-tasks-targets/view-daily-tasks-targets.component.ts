import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

@Component({
  selector: 'app-view-daily-tasks-targets',
  templateUrl: './view-daily-tasks-targets.component.html',
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
      background: linear-gradient(135deg, #013618 0%, #117a6b 100%);
      color: #fff;
    }
    .hr-panel-head h3 { margin: 0; font-weight: 700; font-size: 1.2rem; }
    .hr-panel-head p { margin: .25rem 0 0; opacity: .85; font-size: .85rem; }
    .hr-actions { display: flex; gap: .5rem; }
    .hr-body { padding: 1.25rem; }
    .hr-tabs .tab-btn {
      border: 1px solid #bbf7d0;
      background: #f0fdf4;
      color: #14532d;
      font-weight: 600;
      margin-right: 8px;
      border-radius: 999px;
      padding: .35rem 1rem;
    }
    .hr-tabs .tab-btn.active {
      background: #025E2D;
      border-color: #025E2D;
      color: #fff;
    }
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
    .status-pill { display:inline-block; padding:2px 10px; border-radius:12px; font-size:12px; font-weight:600; text-transform:capitalize; }
    .st-pending { background:#FFF3CD; color:#856404; }
    .st-in_progress { background:#D1ECF1; color:#0C5460; }
    .st-completed { background:#D4EDDA; color:#155724; }
    .st-cancelled { background:#F8D7DA; color:#721C24; }
    .pct-bar { height:8px; background:#E9ECEF; border-radius:6px; overflow:hidden; min-width:80px; }
    .pct-fill { height:100%; background:linear-gradient(90deg,#025E2D,#20C25B); }
  `]
})
export class ViewDailyTasksTargetsComponent implements OnInit {
  tab: 'tasks' | 'targets' = 'tasks';
  TableHidden = true;
  taskList: any[] = [];
  targetList: any[] = [];
  branchList: any[] = [];
  employeeList: any[] = [];
  branchId = '';
  employeeId = '';
  dateFilter = '';
  statusFilter = '';
  periodFilter = 'daily';

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);
    this.dateFilter = y + '-' + m + '-' + d;

    const tenant = localStorage.getItem('tenant_id');
    if (tenant) {
      this.userservice.getData({
        'conditions[tenant_id]': tenant,
        'conditions[status]': 1,
        log_id: localStorage.getItem('logged_employee_id')
      }, 'get-branches', 'POST').subscribe((r: any) => {
        this.branchList = r && r.status === 'Success' ? r.data : [];
      });
    }
    this.load();
  }

  branchChg() {
    this.employeeId = '';
    this.employeeList = [];
    if (!this.branchId) {
      this.load();
      return;
    }
    this.userservice.getData({ 'conditions[branch_id]': this.branchId }, 'get-employees', 'POST').subscribe((r: any) => {
      this.employeeList = r && r.status === 'Success' ? r.data : [];
    });
    this.load();
  }

  setTab( whichtab: 'tasks' | 'targets') {
    this.tab = whichtab;
    this.load();
  }

  statusClass(status: string) {
    const s = String(status || 'pending').replace('-', '_');
    return 'status-pill st-' + s;
  }

  load() {
    this.TableHidden = true;
    const tenant_id = localStorage.getItem('tenant_id');

    if (this.tab === 'tasks') {
      const payload: any = { tenant_id, for_admin: 1 };
      if (this.branchId) payload.branch_id = this.branchId;
      if (this.employeeId) payload.employee_id = this.employeeId;
      if (this.dateFilter) payload.task_date = this.dateFilter;
      if (this.statusFilter) payload.task_status = this.statusFilter;

      this.userservice.getData(payload, 'list-employee-tasks', 'JSON').subscribe((response: any) => {
        this.taskList = response && response.status === 'Success' ? (response.data || []) : [];
        this.TableHidden = false;
      }, () => {
        this.taskList = [];
        this.TableHidden = false;
        this.appService.showAlert('Daily Tasks', 'Unable to load tasks', 'error');
      });
      return;
    }

    const payload: any = { tenant_id };
    if (this.branchId) payload.branch_id = this.branchId;
    if (this.employeeId) payload.employee_id = this.employeeId;
    if (this.dateFilter) payload.on_date = this.dateFilter;
    if (this.periodFilter) payload.period_type = this.periodFilter;

    this.userservice.getData(payload, 'list-employee-targets', 'JSON').subscribe((response: any) => {
      this.targetList = response && response.status === 'Success' ? (response.data || []) : [];
      this.TableHidden = false;
    }, () => {
      this.targetList = [];
      this.TableHidden = false;
      this.appService.showAlert('Daily Targets', 'Unable to load targets', 'error');
    });
  }

  updateTaskStatus(row: any, status: string) {
    const payload = {
      id: row.id,
      task_status: status,
      tenant_id: localStorage.getItem('tenant_id'),
      updated_by: localStorage.getItem('logged_user_id')
    };
    this.userservice.getData(payload, 'update-employee-task-status', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Task', response.msg || 'Status updated', 'success');
        this.load();
      } else {
        this.appService.showAlert('Task', (response && response.msg) || 'Update failed', 'error');
      }
    }, () => this.appService.showAlert('Task', 'Update failed', 'error'));
  }

  typeLabel(type: any): string {
    const t = String(type || '');
    if (t === 'existing_client_collection' || t === 'collection') return 'Existing Client Collection';
    if (t === 'new_client_collection') return 'New Client Collection';
    if (t === 'new_business' || t === 'sales') return 'New Business';
    if (t === 'activity') return 'Activity';
    return t || '-';
  }
}
