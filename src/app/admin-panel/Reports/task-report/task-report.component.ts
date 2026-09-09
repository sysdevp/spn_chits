import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-task-report',
  templateUrl: './task-report.component.html',
  styles: []
})
export class TaskReportComponent implements OnInit {
  TableHidden = true;
  dataList: any[] = [];
  statusFilter = '';
  dateFilter = '';

  constructor(private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);
    this.dateFilter = y + '-' + m + '-' + d;
    this.load();
  }

  load() {
    this.TableHidden = true;
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id')
    };
    if (this.statusFilter) {
      payload.task_status = this.statusFilter;
    }
    if (this.dateFilter) {
      payload.task_date = this.dateFilter;
    }
    this.userservice.getData(payload, 'list-employee-tasks', 'JSON').subscribe((response: any) => {
      this.dataList = response && response.status === 'Success' ? (response.data || []) : [];
      this.TableHidden = false;
    }, () => {
      this.dataList = [];
      this.TableHidden = false;
      this.appService.showAlert('Task Report', 'Unable to load tasks', 'error');
    });
  }
}
