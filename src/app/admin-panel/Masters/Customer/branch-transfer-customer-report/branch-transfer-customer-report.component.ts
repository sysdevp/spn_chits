import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

@Component({
  selector: 'app-branch-transfer-customer-report',
  templateUrl: './branch-transfer-customer-report.component.html',
  styles: []
})
export class BranchTransferCustomerReportComponent implements OnInit {
  FilterForm: FormGroup;
  dataList: any[] = [];
  branchList: any[] = [];
  TableHidden = true;
  SubmitbuttonName = 'Filter';
  tenant_id: any;

  constructor(
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private appService: AppService
  ) {}

  ngOnInit() {
    this.tenant_id = localStorage.getItem('tenant_id');
    const today = new Date();
    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
    this.FilterForm = this.formBuilder.group({
      tenant_id: [this.tenant_id],
      from_branch_id: [''],
      to_branch_id: [''],
      start_date: [this.toInputDate(monthStart)],
      end_date: [this.toInputDate(today)]
    });
    this.loadBranches();
    this.onSubmit();
  }

  toInputDate(d: Date) {
    const m = d.getMonth() + 1;
    const day = d.getDate();
    const mm = (m < 10 ? '0' : '') + m;
    const dd = (day < 10 ? '0' : '') + day;
    return d.getFullYear() + '-' + mm + '-' + dd;
  }

  toApiDate(value: string) {
    if (!value) {
      return null;
    }
    const parts = String(value).split('-');
    if (parts.length !== 3) {
      return null;
    }
    return { year: +parts[0], month: +parts[1], day: +parts[2] };
  }

  loadBranches() {
    const cond = {
      'conditions[tenant_id]': this.tenant_id,
      'conditions[status]': 1
    };
    this.userservice.getData(cond, 'list-branches', 'POST').subscribe((response: any) => {
      this.branchList = response.status === 'Success' ? response.data : [];
    });
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.TableHidden = true;
    const v = this.FilterForm.value;
    const payload: any = { tenant_id: v.tenant_id };
    if (v.from_branch_id) {
      payload.from_branch_id = v.from_branch_id;
    }
    if (v.to_branch_id) {
      payload.to_branch_id = v.to_branch_id;
    }
    const sd = this.toApiDate(v.start_date);
    const ed = this.toApiDate(v.end_date);
    if (sd) {
      payload.start_date = sd;
    }
    if (ed) {
      payload.end_date = ed;
    }

    this.userservice.getData(payload, 'list-customer-branch-transfers', 'JSON').subscribe(
      (response: any) => {
        this.SubmitbuttonName = 'Filter';
        this.TableHidden = false;
        if (response.status === 'Success') {
          this.dataList = response.data || [];
        } else {
          this.dataList = [];
          this.appService.showAlert('Branch Transfer Report', response.msg || 'Failed', 'error');
        }
      },
      () => {
        this.SubmitbuttonName = 'Filter';
        this.TableHidden = false;
        this.dataList = [];
        this.appService.showAlert('Branch Transfer Report', 'Unable to load report', 'error');
      }
    );
  }
}
