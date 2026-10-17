import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup } from '@angular/forms';

@Component({
  selector: 'app-customer-app-leads-report',
  templateUrl: './customer-app-leads-report.component.html',
  styles: [`
    .lead-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:12px; font-weight:600; }
    .lead-referral { background:#e7f5ec; color:#0f5132; }
    .lead-new_customer { background:#e8f0fe; color:#1a4fa0; }
    .status-select { min-width:130px; }
  `]
})
export class CustomerAppLeadsReportComponent implements OnInit {

  LeadsReport: FormGroup;
  dataList: any[] = [];
  TableHidden: boolean = true;
  SubmitbuttonName: string = "Filter";
  companyList: any[];
  branchList: any[];
  isSuperAdmin: boolean;
  disableBtn: boolean;
  statusList = [
    { id: 0, name: 'New' },
    { id: 1, name: 'Contacted' },
    { id: 2, name: 'Converted' },
    { id: 3, name: 'Not Interested' }
  ];

  constructor(private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService) { }

  companyChg(event) {
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      this.branchList = response.status == 'Success' ? response.data : [];
    }, (err: any) => { });
  }

  setData(data) {
    const dt: any = $.fn.DataTable;
    if (dt.isDataTable('#dtable')) {
      $('#dtable').DataTable().destroy();
    }
    this.dataList = data;
    setTimeout(() => {
      $('#dtable').DataTable({ order: [] });
    }, 0);
  }

  resetReport() {
    this.LeadsReport.patchValue({ branch_id: '', lead_type: '', status: '', start_date: null, end_date: null });
    if (!localStorage.getItem('tenant_id')) {
      this.LeadsReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.onSubmit();
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.disableBtn = true;
    const result = Object.assign({}, this.LeadsReport.value);
    this.userservice.getData(result, "list-customer-app-leads", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      if (response.status == 'Success') {
        this.setData(response.data);
        this.TableHidden = false;
      } else {
        this.appService.showAlert("Referral & App Leads", response.msg, "error");
      }
    }, (err: any) => {
      this.SubmitbuttonName = "Filter";
      this.disableBtn = false;
      this.appService.showAlert("Referral & App Leads", "Sorry!! Something went wrong!", "error");
    });
  }

  statusChg(row, event) {
    const status = event.target.value;
    const payload = {
      id: row.id,
      status: status,
      updated_by: localStorage.getItem('user_id') || 0
    };
    this.userservice.getData(payload, "update-customer-app-lead-status", "JSON").subscribe((response: any) => {
      if (response.status == 'Success') {
        row.status = +status;
        row.status_label = this.statusList[+status].name;
        this.appService.showAlert("Referral & App Leads", response.msg, "success");
      } else {
        event.target.value = row.status;
        this.appService.showAlert("Referral & App Leads", response.msg, "error");
      }
    }, (err: any) => {
      event.target.value = row.status;
      this.appService.showAlert("Referral & App Leads", "Sorry!! Something went wrong!", "error");
    });
  }

  ngOnInit() {
    this.LeadsReport = this.formBuilder.group({
      tenant_id: [''],
      branch_id: [''],
      lead_type: [''],
      status: [''],
      start_date: [null],
      end_date: [null]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.LeadsReport.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        this.companyList = response.status == 'Success' ? response.data : [];
      }, (err: any) => { });
    }
    this.onSubmit();
  }
}
