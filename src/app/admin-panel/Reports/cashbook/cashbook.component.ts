import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import * as XLSX from 'xlsx';

@Component({
  selector: 'app-cashbook',
  templateUrl: './cashbook.component.html',
  styles: []
})
export class CashbookComponent implements OnInit {
  bookForm: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Submit';

  entries: any[] = [];
  opening_balance = 0;
  closing_balance = 0;
  total_debit = 0;
  total_credit = 0;
  reportLoaded = false;

  branchList: any[] = [];
  companyList: any[] = [];
  isSuperAdmin = false;

  @ViewChild('focus1') focus1: ElementRef;
  @ViewChild('focus2') focus2: ElementRef;
  @ViewChild('focus3') focus3: ElementRef;
  @ViewChild('focus4') focus4: ElementRef;

  constructor(
    private fb: FormBuilder,
    private userservice: UserService,
    private appService: AppService
  ) {}

  ngOnInit(): void {
    const tenantId = localStorage.getItem('tenant_id') || '';
    this.bookForm = this.fb.group({
      tenant_id: [tenantId, Validators.required],
      branch_id: ['', Validators.required],
      start_date: ['', Validators.required],
      end_date: ['', Validators.required],
      book_type: ['cash', Validators.required]
    });
    if (tenantId) {
      this.isSuperAdmin = false;
      this.setBranchList(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data : [];
      });
    }
    const today = new Date();
    const y = today.getFullYear();
    const m = ('0' + (today.getMonth() + 1)).slice(-2);
    const d = ('0' + today.getDate()).slice(-2);
    this.bookForm.get('start_date').setValue(y + '-' + m + '-01');
    this.bookForm.get('end_date').setValue(y + '-' + m + '-' + d);
  }

  companyChg(event) {
    this.bookForm.get('branch_id').setValue('');
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    this.userservice.getData({
      'conditions[tenant_id]': tenant_id,
      log_id: localStorage.getItem('logged_employee_id')
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  get f() {
    return this.bookForm.controls;
  }

  onSubmit(): void {
    this.submitted = true;
    this.SubmitbuttonName = 'Loading...';

    if (this.bookForm.invalid) {
      this.SubmitbuttonName = 'Submit';
      if (this.f.tenant_id.errors) this.focus1.nativeElement.focus();
      else if (this.f.branch_id.errors) this.focus2.nativeElement.focus();
      else if (this.f.start_date.errors) this.focus3.nativeElement.focus();
      else if (this.f.end_date.errors) this.focus4.nativeElement.focus();
      return;
    }

    const formData: any = { ...this.bookForm.value };
    formData.from_date = formData.start_date;
    formData.to_date = formData.end_date;

    this.userservice.getData(formData, 'cash-bank-book', 'POST').subscribe((res: any) => {
      this.SubmitbuttonName = 'Submit';
      if (res.status === 'Success') {
        const data = res.data || {};
        this.entries = (data.entries || []).map((e: any) => Object.assign({}, e, {
          running_balance: e.running_balance != null ? e.running_balance : e.balance
        }));
        this.opening_balance = data.opening_balance || 0;
        this.total_debit = data.total_debit || 0;
        this.total_credit = data.total_credit || 0;
        this.closing_balance = data.closing_balance || 0;
        this.reportLoaded = true;
      } else {
        this.appService.showAlert('Cash Box Book', res.msg || res.error || 'Failed', 'error');
      }
    }, () => {
      this.SubmitbuttonName = 'Submit';
      this.appService.showAlert('Cash Box Book', 'Something went wrong!', 'error');
    });
  }

  exportExcel(): void {
    const rows = this.entries.map((e) => ({
      Date: e.date,
      Particulars: e.particulars,
      Debit: e.debit,
      Credit: e.credit,
      Balance: e.running_balance
    }));
    rows.push({
      Date: '',
      Particulars: 'TOTAL',
      Debit: this.total_debit,
      Credit: this.total_credit,
      Balance: this.closing_balance
    } as any);
    const worksheet = XLSX.utils.json_to_sheet(rows);
    const workbook = { Sheets: { Report: worksheet }, SheetNames: ['Report'] };
    XLSX.writeFile(workbook, 'cash-box-book.xlsx');
  }
}
