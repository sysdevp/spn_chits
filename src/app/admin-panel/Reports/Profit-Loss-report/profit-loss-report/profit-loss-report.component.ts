import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import jspdf from 'jspdf';
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-profit-loss-report',
  templateUrl: './profit-loss-report.component.html',
  styles: []
})
export class ProfitLossReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('start_date') focus3: ElementRef;
  @ViewChild('end_date') focus4: ElementRef;

  ProfitLoss: FormGroup;
  dataList: any;
  showloader = false;
  TableHidden = true;
  backTOGet = true;
  SubmitbuttonName = 'Filter';
  companyList: any[] = [];
  branchList: any[] = [];
  isSuperAdmin = false;
  submitted = false;
  disableBtn = false;
  dayClosingList: any;
  details = false;
  report = true;
  test = true;
  test1 = false;
  testincome = true;
  test1income = false;
  BranchName: any;
  StartDate: any;
  EndDate: any;
  enrollmentInfo: any;
  expenseInfo: any;
  incomeInfo: any;
  expenseTotal: any = 0;
  incomeTotal: any = 0;
  profit: number = 0;
  loss: number = 0;
  overallamount: any;
  overallamountloss: any;
  totalexpenses: number = 0;
  totalincomes: number = 0;
  totalexpenses_new: number = 0;
  totalincomes_new: number = 0;
  totalexpensewithall: number = 0;
  totalincomewithall: number = 0;
  credit_amount = 0;
  credit_amount1 = 0;
  amount_received: number = 0;
  commission: number = 0;
  amount_disbursed: number = 0;
  amount_refund: number = 0;
  chit_fund_name: string;
  branch_name: string;
  totalIncome: any;
  totalExpense: any;
  netResult: any;
  netAmount: any;

  constructor(
    private router: Router,
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService
  ) {}

  get f() {
    return this.ProfitLoss.controls;
  }

  ngOnInit() {
    this.ProfitLoss = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      start_date: [null, Validators.required],
      end_date: [null, Validators.required],
    });

    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.ProfitLoss.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        if (res.status === 'Success') this.companyList = res.data;
      });
    }
  }

  companyChg(event) {
    const tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id: string) {
    const cond = {
      "conditions[tenant_id]": tenant_id,
      log_id: localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((res: any) => {
      if (res.status === 'Success') this.branchList = res.data;
    });
  }

  backToView() {
    window.location.reload();
  }

  resetReport() {
    this.details = false;
    this.report = true;
    this.backTOGet = true;
    this.submitted = false;
    this.ProfitLoss.reset();
    this.setData([]);
  }

  setData(data: any) {
    this.dataList = data;
    setTimeout(() => {
      ($('#dtable') as any).DataTable();
    });
  }

  onSubmit() {
  this.SubmitbuttonName = 'Loading...';
  this.submitted = true;

  if (this.ProfitLoss.invalid) {
    this.SubmitbuttonName = 'Submit';
    if (this.f.tenant_id.errors) this.focus1.nativeElement.focus();
    else if (this.f.branch_id.errors) this.focus2.nativeElement.focus();
    else if (this.f.start_date.errors) this.focus3.nativeElement.focus();
    else if (this.f.end_date.errors) this.focus4.nativeElement.focus();
    return;
  }

  const result = { ...this.ProfitLoss.value };

  this.userservice.getData(result, 'profit-loss-expense-report', 'JSON').subscribe((res: any) => {
    this.SubmitbuttonName = 'Submit';

    if (res.status === 'Success') {
      // ✅ Check if it's array-based or object-based response
      if (Array.isArray(res.data)) {
        const exp = res.data[0][0] || {};
        const inc = res.data[1][0] || {};

        this.expenseInfo = exp.expense_det || [];
        this.incomeInfo = inc.income_det || [];

        this.totalexpenses = this.expenseInfo.reduce((sum, x) => sum + parseFloat(x.total_expense || 0), 0);
        this.totalincomes = this.incomeInfo.reduce((sum, x) => sum + parseFloat(x.total_income || 0), 0);

        this.amount_received = res.amount_received || 0;
        this.commission = res.commission || 0;
        this.amount_disbursed = res.amount_disbursed || 0;
        this.amount_refund = res.amount_refund || 0;

        this.totalexpenses_new = this.totalexpenses + this.amount_disbursed + this.amount_refund;
        this.totalincomes_new = this.totalincomes + this.amount_received + this.commission;

        this.totalexpensewithall = this.totalexpenses_new;
        this.totalincomewithall = this.totalincomes_new;

        this.profit = Math.max(this.totalincomewithall - this.totalexpensewithall, 0);
        this.loss = Math.max(this.totalexpensewithall - this.totalincomewithall, 0);
      } else {
        // Object-based response fallback
        this.incomeInfo = res.data.Incomes || [];
        this.expenseInfo = res.data.Expenses || [];

        this.totalIncome = res.total_income || 0;
        this.totalExpense = res.total_expense || 0;
        this.netResult = res.net_result || '';
        this.netAmount = res.net_amount || 0;
      }

      // Common metadata
      this.chit_fund_name = res.companyname || '';
      this.branch_name = res.branchname || '';
      this.StartDate = res.start_date;
      this.EndDate = res.end_date;

      this.details = true;
      this.report = false;
      this.backTOGet = false;

      this.setData(res.data); // If required for chart or print logic
      this.totalincomes = this.incomeInfo.reduce((sum, x) => sum + Number(x.credit || 0), 0);
this.totalexpenses = this.expenseInfo.reduce((sum, x) => sum + Number(x.debit || 0), 0);

this.profit = Math.max(this.totalincomes - this.totalexpenses, 0);
this.loss = Math.max(this.totalexpenses - this.totalincomes, 0);
    } else {
      this.appService.showAlert('Profit Loss Report', res.msg, 'error');
    }
  }, err => {
    this.disableBtn = false;
    this.SubmitbuttonName = 'Submit';
    this.appService.showAlert('Profit Loss', 'Sorry!! Something went wrong!', 'error');
  });
}

  onpdf() {
    const data = document.getElementById('grid');
    html2canvas(data).then(canvas => {
      const imgWidth = 210;
      const pageHeight = 295;
      const imgHeight = canvas.height * imgWidth / canvas.width;
      let heightLeft = imgHeight;
      const contentDataURL = canvas.toDataURL('image/jpeg');
      const pdf = new jspdf('p', 'mm', 'a4');
      let position = 0;

      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;

      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      }

      pdf.save('Profitlossreport.pdf');
    });
  }

  viewFull() {
    this.test = false;
    this.test1 = true;
  }

  viewHide() {
    this.test = true;
    this.test1 = false;
  }

  viewFullIncome() {
    this.testincome = false;
    this.test1income = true;
  }

  viewHideIncome() {
    this.testincome = true;
    this.test1income = false;
  }
}

export interface ViewData {
  sno: string;
  branch_name: string;
}
