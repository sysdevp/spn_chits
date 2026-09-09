import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-view-leave-requests',
  templateUrl: './view-leave-requests.component.html',
  styles: []
})
export class ViewLeaveRequestsComponent implements OnInit {
  TableHidden = true;
  dataList: any[] = [];
  typeFilter = '';
  statusFilter = '';
  selected: any = null;

  constructor(
    private userservice: UserService,
    private appService: AppService,
    private modalService: NgbModal
  ) {}

  ngOnInit() {
    this.load();
  }

  load() {
    this.TableHidden = true;
    const payload: any = {
      tenant_id: localStorage.getItem('tenant_id'),
      for_admin: 1
    };
    if (this.typeFilter) {
      payload.request_type = this.typeFilter;
    }
    if (this.statusFilter) {
      payload.request_status = this.statusFilter;
    }
    this.userservice.getData(payload, 'list-leave-requests', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.dataList = response.data || [];
      } else {
        this.dataList = [];
      }
      setTimeout(() => { this.TableHidden = false; }, 0);
    }, () => {
      this.dataList = [];
      this.TableHidden = false;
      this.appService.showAlert('Leave Requests', 'Unable to load requests', 'error');
    });
  }

  open(content, row) {
    this.selected = row;
    this.modalService.open(content, { windowClass: 'modal-lg animate' });
  }

  decide(row, decision) {
    if (!row || row.request_status !== 'pending') {
      return;
    }
    const payload = {
      tenant_id: localStorage.getItem('tenant_id'),
      id: row.id,
      decision: decision,
      approved_by: localStorage.getItem('logged_user_id'),
      approval_remark: decision === 'approved' ? 'Approved from web' : 'Rejected from web'
    };
    this.userservice.getData(payload, 'decide-leave-request', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Leave Requests', response.msg, 'success');
        this.load();
      } else {
        this.appService.showAlert('Leave Requests', (response && response.msg) || 'Update failed', 'error');
      }
    }, () => {
      this.appService.showAlert('Leave Requests', 'Update failed', 'error');
    });
  }
}
