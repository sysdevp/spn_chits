// trial-balance.component.ts

import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import * as XLSX from 'xlsx';
import * as FileSaver from 'file-saver';

const EXCEL_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=UTF-8';

@Component({
  
  selector: 'app-trial-balance',
  templateUrl: './trial-balance.component.html',
  styles: []
})
export class TrialBalanceComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('start_date') focus3: ElementRef;
  @ViewChild('end_date') focus4: ElementRef;

  TrialBalance: FormGroup;
  companyList: any[] = [];
  branchList: any[] = [];
  trialbalance: any[] = [];
  selectedLedger: any;
  ledgerEntries: any[] = [];

  submitted = false;
  disableBtn = false;
  details = false;
  report = true;
  backTOGet = true;
  isSuperAdmin = false;
  SubmitbuttonName = 'Submit';

  chit_fund_name: string;
  branch_name: string;
  totaldebitamounts: number = 0;
  totalcreditamounts: number = 0;
  day: number;
  month: number;
  year: number;
  endday: number;
  endmonth: number;
  endyear: number;

  

  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService
  ) {}

  get f() {
    return this.TrialBalance.controls;
  }

  ngOnInit() {
    const tenant_id = localStorage.getItem('tenant_id');
    this.TrialBalance = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      start_date: ['', Validators.required],
      end_date: ['', Validators.required]
    });

    if (tenant_id) {
      this.TrialBalance.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        if (response.status === 'Success') {
          this.companyList = response.data;
        }
      });
    }
  }

  companyChg(event) {
    const tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id: string) {
    const cond = { 'conditions[tenant_id]': tenant_id };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      if (response.status === 'Success') {
        this.branchList = response.data;
      } else {
        this.branchList = [];
      }
    });
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    const result = this.TrialBalance.value;

    if (this.TrialBalance.invalid) {
      this.SubmitbuttonName = 'Submit';
      this.disableBtn = false;
      if (this.f.tenant_id.errors) this.focus1.nativeElement.focus();
      else if (this.f.branch_id.errors) this.focus2.nativeElement.focus();
      else if (this.f.start_date.errors) this.focus3.nativeElement.focus();
      else if (this.f.end_date.errors) this.focus4.nativeElement.focus();
      return;
    }

    this.userservice.getData(result, 'trial-balance', 'JSON').subscribe(
      (response: any) => {
        this.SubmitbuttonName = 'Submit';
        if (response.status === 'Success') {
         if (Array.isArray(response.data)) {
  this.trialbalance = response.data.map(group => ({
    ...group,
    subdata: group.subdata.filter(item =>
      Number(item.debit_amount) !== 0 || Number(item.credit_amount) !== 0
    )
  }))
  // remove empty groups
  .filter(group => group.subdata.length > 0);
} else {
  this.trialbalance = [];
}

// Totals come directly from API
this.totaldebitamounts = Number(response.total_debit_amounts) || 0;
this.totalcreditamounts = Number(response.total_credit_amounts) || 0;
          this.chit_fund_name = response.companyname || '';
          this.branch_name = response.branchname || '';

          const stdate = this.TrialBalance.get('start_date').value;
          this.day = stdate.day;
          this.month = stdate.month;
          this.year = stdate.year;

          const endate = this.TrialBalance.get('end_date').value;
          this.endday = endate.day;
          this.endmonth = endate.month;
          this.endyear = endate.year;

          this.details = true;
          this.report = false;
          this.backTOGet = false;
        } else {
          this.appService.showAlert('Trial Balance', response.msg, 'error');
        }
      },
      () => {
        this.disableBtn = false;
        this.SubmitbuttonName = 'Submit';
        this.appService.showAlert('Trial Balance', 'Sorry!! Something went wrong!', 'error');
      }
    );
  }





  resetReport() {
    this.TrialBalance.reset();
    this.details = false;
    this.report = true;
    this.backTOGet = true;
    this.trialbalance = [];
    this.totaldebitamounts = 0;
    this.totalcreditamounts = 0;
  }

  backToView() {
    this.resetReport();
  }

  onpdf(): void {
    const data = document.getElementById('grid');
    html2canvas(data).then((canvas) => {
      const imgWidth = 210;
      const pageHeight = 295;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      let heightLeft = imgHeight;
      const contentDataURL = canvas.toDataURL('image/jpeg');
      const pdf = new jsPDF('p', 'mm', 'a4');
      let position = 0;
      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;

      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      }

      pdf.save('TrialBalanceReport.pdf');
    });
  }

  exportToExcel(): void {
    const dataToExport = [];
    for (let row of this.trialbalance) {
      for (let sub of row.subdata) {
        dataToExport.push({
          'Top Group': row.top_group,
          'Account Head': sub.head_name,
          'Debit': sub.debit_amount,
          'Credit': sub.credit_amount
        });
      }
    }
    const worksheet = XLSX.utils.json_to_sheet(dataToExport);
    const workbook = { Sheets: { 'TrialBalance': worksheet }, SheetNames: ['TrialBalance'] };
    const excelBuffer: any = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
    const data: Blob = new Blob([excelBuffer], { type: EXCEL_TYPE });
    FileSaver.saveAs(data, 'TrialBalance.xlsx');
  }

  getLedgerDetails(account) {
    this.selectedLedger = account;
    const payload = {
      tenant_id: this.TrialBalance.get('tenant_id').value,
      branch_id: this.TrialBalance.get('branch_id').value,
      start_date: this.TrialBalance.get('start_date').value,
      end_date: this.TrialBalance.get('end_date').value,
      account_head_id: account.account_head_id
    };

    this.userservice.getData(payload, 'ledger-details', 'POST').subscribe((res: any) => {
      if (res.status === 'Success') {
        this.ledgerEntries = res.data;
        const modal = document.getElementById('ledgerModal');
        if (modal) {
          modal.classList.add('show');
          modal.style.display = 'block';
        }
      } else {
        this.appService.showAlert("Ledger View", res.msg, "error");
      }
    });
  }

  closeLedgerModal() {
    const modal = document.getElementById('ledgerModal');
    if (modal) {
      modal.classList.remove('show');
      modal.style.display = 'none';
    }
  }
}
