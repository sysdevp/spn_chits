import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-office-tickets',
  templateUrl: './office-tickets.component.html',
  styles: [`
    .tk-kpi { border-radius: 12px; padding: 14px; color: #fff; min-height: 90px; }
    .tk-a { background: #1565c0; } .tk-b { background: #e65100; } .tk-c { background: #6a1b9a; } .tk-d { background: #2e7d32; }
    .bar { height: 10px; background: #025E2D; border-radius: 6px; }
    .msg { background: #f6f8f7; border-radius: 8px; padding: 8px 10px; margin-bottom: 8px; }
  `]
})
export class OfficeTicketsComponent implements OnInit {
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  rows: any[] = [];
  summary: any = { total: 0, open: 0, in_progress: 0, resolved: 0 };
  report: any = null;
  selected: any = null;
  messages: any[] = [];
  reply = '';
  remarks = '';
  loading = false;

  constructor(private fb: FormBuilder, private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    this.filterForm = this.fb.group({
      tenant_id: [tenantId || ''],
      branch_id: ['0'],
      kind: ['all'],
      status: ['all'],
      search: ['']
    });
    if (tenantId) {
      this.setBranches(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    }
    this.load();
  }

  companyChg(event: any) {
    this.filterForm.get('branch_id').setValue('0');
    this.setBranches(event.target.value);
  }

  setBranches(tenantId: string) {
    if (!tenantId) return;
    this.userservice.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  load() {
    if (!this.filterForm.get('tenant_id').value) return;
    this.loading = true;
    const payload = this.filterForm.value;
    this.userservice.getData(payload, 'list-office-tickets', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.rows = res.status === 'Success' ? (res.data || []) : [];
      this.summary = res.summary || this.summary;
    }, () => { this.loading = false; });
    this.userservice.getData(payload, 'office-ticket-report', 'JSON').subscribe((res: any) => {
      if (res.status === 'Success') this.report = res;
    });
  }

  open(row: any) {
    this.userservice.getData({ id: row.id }, 'get-office-ticket', 'JSON').subscribe((res: any) => {
      if (res.status === 'Success') {
        this.selected = res.data;
        this.messages = res.messages || [];
      } else {
        this.appService.showAlert('Ticket', res.msg || 'Not found', 'error');
      }
    });
  }

  setStatus(status: string) {
    if (!this.selected) return;
    this.userservice.getData({
      id: this.selected.id,
      status,
      remarks: this.remarks,
      sender_name: localStorage.getItem('logged_user_name') || 'Admin',
      sender_role: 'Admin',
      updated_by: localStorage.getItem('logged_employee_id')
    }, 'update-office-ticket', 'JSON').subscribe((res: any) => {
      this.appService.showAlert('Ticket', res.msg || 'Updated', res.status === 'Success' ? 'success' : 'error');
      if (res.status === 'Success') {
        this.remarks = '';
        this.open(this.selected);
        this.load();
      }
    });
  }

  send() {
    if (!this.selected || !this.reply.trim()) return;
    this.userservice.getData({
      id: this.selected.id,
      message: this.reply,
      sender_name: localStorage.getItem('logged_user_name') || 'Admin',
      sender_role: 'Admin'
    }, 'office-ticket-message', 'JSON').subscribe((res: any) => {
      if (res.status === 'Success') {
        this.reply = '';
        this.open(this.selected);
      } else {
        this.appService.showAlert('Ticket', res.msg || 'Failed', 'error');
      }
    });
  }

  label(status: string) {
    return (status || '').replace(/_/g, ' ');
  }

  barWidth(count: number) {
    const max = Math.max(1, ...(this.report && this.report.by_type || []).map((r: any) => r.count));
    return Math.max(8, Math.round((count / max) * 100)) + '%';
  }
}
