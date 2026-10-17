import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';

@Component({
  selector: 'app-refund-approval',
  templateUrl: './refund-approval.component.html',
  styles: []
})
export class RefundApprovalComponent implements OnInit {
  filterForm: FormGroup;
  branchList: any[] = [];
  companyList: any[] = [];
  dataList: any[] = [];
  isSuperAdmin = false;
  loading = false;
  noData = false;
  remarks = '';
  actingId = 0;

  constructor(private formBuilder: FormBuilder, private userservice: UserService, private appService: AppService) {}

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    this.filterForm = this.formBuilder.group({
      tenant_id: [tenantId || ''],
      branch_id: ['0'],
      approval_status: ['pending']
    });
    if (tenantId) {
      this.setBranchList(tenantId);
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
    this.setBranchList(event.target.value);
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

  load() {
    this.loading = true;
    this.noData = false;
    const payload = Object.assign({}, this.filterForm.value, {
      approved_by: localStorage.getItem('logged_employee_id')
    });
    this.userservice.getData(payload, 'list-refund-requests', 'JSON').subscribe((res: any) => {
      this.loading = false;
      this.dataList = res.status === 'Success' ? (res.data || []) : [];
      this.noData = this.dataList.length === 0;
      if (res.status !== 'Success') {
        this.appService.showAlert('Refund Approval', res.msg || 'Failed', 'error');
      }
    }, () => {
      this.loading = false;
      this.appService.showAlert('Refund Approval', 'Unable to load refund requests', 'error');
    });
  }

  decide(row: any, decision: string) {
    this.actingId = row.id;
    this.userservice.getData({
      id: row.id,
      decision: decision,
      remarks: this.remarks || '',
      approved_by: localStorage.getItem('logged_employee_id')
    }, 'approve-refund-request', 'JSON').subscribe((res: any) => {
      this.actingId = 0;
      if (res.status === 'Success') {
        this.remarks = '';
        this.appService.showAlert('Refund Approval', res.msg, 'success');
        this.load();
      } else {
        this.appService.showAlert('Refund Approval', res.msg || 'Failed', 'error');
      }
    }, () => {
      this.actingId = 0;
      this.appService.showAlert('Refund Approval', 'Unable to update refund', 'error');
    });
  }
}
