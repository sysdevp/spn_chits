import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import * as XLSX from 'xlsx';

@Component({
  selector: 'app-utr-receipt-report',
  templateUrl: './utr-receipt-report.component.html',
  styles: []
})
export class UtrReceiptReportComponent implements OnInit {
  reportForm: FormGroup;
  branchList: any[] = [];
  companyList: any[] = [];
  dataList: any[] = [];
  totals: any = { amount: 0, count: 0 };
  submitted = false;
  loading = false;
  isSuperAdmin = false;
  noData = false;

  constructor(private formBuilder: FormBuilder, private userservice: UserService, private appService: AppService) {}

  get f() { return this.reportForm.controls; }

  ngOnInit() {
    const tenantId = localStorage.getItem('tenant_id');
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);
    this.reportForm = this.formBuilder.group({
      tenant_id: [tenantId || '', Validators.required],
      branch_id: ['0'],
      from_date: [y + '-' + m + '-01', Validators.required],
      to_date: [y + '-' + m + '-' + d, Validators.required],
      utr: [''],
      entry_mode: ['all']
    });
    if (tenantId) {
      this.setBranchList(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    }
  }

  companyChg(event: any) {
    this.reportForm.get('branch_id').setValue('0');
    this.setBranchList(event.target.value);
  }

  setBranchList(tenantId: string) {
    this.userservice.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  onSubmit() {
    this.submitted = true;
    if (this.reportForm.invalid) return;
    this.loading = true;
    this.noData = false;
    this.userservice.getData(this.reportForm.value, 'utr-receipt-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      if (res.status === 'Success') {
        this.dataList = res.data || [];
        this.totals = res.totals || { amount: this.sumAmount(), count: this.dataList.length };
        this.noData = this.dataList.length === 0;
      } else {
        this.dataList = [];
        this.totals = { amount: 0, count: 0 };
        this.noData = true;
        this.appService.showAlert('UTR Report', res.msg || 'Failed', 'error');
      }
    }, () => {
      this.loading = false;
      this.appService.showAlert('UTR Report', 'Unable to load report', 'error');
    });
  }

  sumAmount() {
    return (this.dataList || []).reduce((s, r) => s + Number(r.amount || 0), 0);
  }

  exportExcel() {
    if (!this.dataList.length) {
      this.appService.showAlert('UTR Report', 'No data to export', 'error');
      return;
    }
    const rows = this.dataList.map((r) => ({
      'S.No': r.sno,
      'Receipt No': r.receipt_no,
      'Receipt Date': r.receipt_date,
      Customer: r.customer_name,
      Mobile: r.mobile,
      Employee: r.employee_name,
      Branch: r.branch_name,
      Amount: r.amount,
      UTR: r.utr,
      'Txn Date': r.txn_date,
      'Txn Time': r.txn_time,
      Entry: r.entry_mode_label || (r.entry_mode === 'manual' ? 'Manually typed' : 'Camera / screenshot'),
      Status: r.status
    }));
    rows.push({
      'S.No': '',
      'Receipt No': '',
      'Receipt Date': '',
      Customer: 'TOTAL',
      Mobile: '',
      Employee: '',
      Branch: '',
      Amount: this.totals.amount,
      UTR: 'Count: ' + this.totals.count,
      'Txn Date': '',
      'Txn Time': '',
      Entry: '',
      Status: ''
    } as any);
    const ws = XLSX.utils.json_to_sheet(rows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'UTR Report');
    XLSX.writeFile(wb, 'utr_receipt_report.xlsx');
  }
}
