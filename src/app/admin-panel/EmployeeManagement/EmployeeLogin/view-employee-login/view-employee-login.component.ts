import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup } from '@angular/forms';

@Component({
  selector: 'app-view-employee-login',
  templateUrl: './view-employee-login.component.html',
  styles: []
})
export class ViewEmployeeLoginComponent implements OnInit {
  dataList: any[] = [];
  allData: any[] = [];
  branchList: any[] = [];
  showloader: boolean;
  TableHidden = true;
  ViewEmployeeLogin: FormGroup;

  constructor(
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService,
    private spinner: NgxSpinnerService
  ) {}

  get f() {
    return this.ViewEmployeeLogin.controls;
  }

  changeStatus(recordId, statusId) {
    const data = { id: recordId };
    this.userservice.getData(data, 'update-user-status', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.dataList.forEach((listing: any) => {
            if (listing.id == recordId) {
              listing.status = statusId;
            }
          });
          this.appService.showAlert('Employee Login', response.msg, 'success');
        } else {
          this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
        }
      } catch (err) {}
    });
  }

  ngOnInit() {
    this.TableHidden = true;
    const tenant_id = localStorage.getItem('tenant_id');
    this.ViewEmployeeLogin = this.formBuilder.group({
      tenant_id: [tenant_id || ''],
      branch_id: ['']
    });
    this.loadBranches(tenant_id);
    this.loadUsers();
  }

  loadBranches(tenantId) {
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

  loadUsers() {
    this.TableHidden = true;
    const result = Object.assign({}, this.ViewEmployeeLogin.value);
    this.userservice.getData(result, 'list-users', 'JSON').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.allData = response.data || [];
          this.applyBranchFilter();
          setTimeout(() => { this.TableHidden = false; }, 0);
        }
      } catch (err) {}
    });
  }

  applyBranchFilter() {
    const branchId = this.ViewEmployeeLogin.get('branch_id').value;
    let rows = this.allData || [];
    if (branchId) {
      rows = rows.filter((r) => {
        const bid = r.branch_id || (r.employee_info && r.employee_info.branch_id) || (r.branch_info && r.branch_info.id);
        return String(bid) === String(branchId);
      });
    }
    this.dataList = rows;
    setTimeout(() => {
      const dt = ($ as any).fn.DataTable;
      if (dt && typeof dt.isDataTable === 'function' && dt.isDataTable('#dtable')) {
        $('#dtable').DataTable().destroy();
      }
      $('#dtable').DataTable();
    }, 50);
  }

  branchChg() {
    this.applyBranchFilter();
  }
}
