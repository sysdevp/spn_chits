import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';

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
  opening_balance: number = 0;
  closing_balance: number = 0;
  total_debit: number = 0;
  total_credit: number = 0;
  reportLoaded = false;

  branchList: any;
  isSuperAdmin: boolean;

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
    this.bookForm = this.fb.group({
      tenant_id: [''],
      branch_id: [''],
      start_date: [null, Validators.required],
      end_date: [null, Validators.required],
      book_type: ['cash', Validators.required] // cash or bank
    });
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "log_id": localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, "get-branches", "POST").subscribe((res: any) => {
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

    const formData = { ...this.bookForm.value };

    this.userservice.getData(formData, 'cash-bank-book', 'POST').subscribe((res: any) => {
      this.SubmitbuttonName = 'Submit';
      if (res.status === 'Success') {
        const data = res.data || {};
        this.entries = data.entries || [];
        this.opening_balance = data.opening_balance || 0;
        this.total_debit = data.total_debit || 0;
        this.total_credit = data.total_credit || 0;
        this.closing_balance = data.closing_balance || 0;
        this.reportLoaded = true;
      } else {
        this.appService.showAlert('Report', res.msg, 'error');
      }
    }, err => {
      this.SubmitbuttonName = 'Submit';
      this.appService.showAlert('Report', 'Something went wrong!', 'error');
    });
  }

  exportExcel(): void {
    const worksheet = XLSX.utils.json_to_sheet(this.entries);
    const workbook = { Sheets: { 'Report': worksheet }, SheetNames: ['Report'] };
    XLSX.writeFile(workbook, 'cash-bank-book.xlsx');
  }

  exportPDF(): void {
    const doc = new jsPDF();
    
    doc.save('cash-bank-book.pdf');
  }
}
