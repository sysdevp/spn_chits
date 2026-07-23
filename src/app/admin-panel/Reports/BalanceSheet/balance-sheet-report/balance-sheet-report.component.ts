import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

@Component({
  
  selector: 'app-balance-sheet-report',
  templateUrl: './balance-sheet-report.component.html',
  styles: []
})
export class BalanceSheetReportComponent implements OnInit {
  balanceSheetForm: FormGroup;
  submitted = false;
  SubmitbuttonName = 'Submit';
  disableBtn = false;

  // Data holders
  prizedOutstanding = 0;
  nonPrizedOutstanding = 0;

  incomeHeads: any[] = [];
  expenseHeads: any[] = [];

  assets: any[] = [];
liabilities: any[] = [];
equity: any[] = [];

totalAssets: number = 0;
totalLiabilities: number = 0;
totalEquity: number = 0;
totalLiabilitiesEquity: number = 0;

  totalIncome = 0;
  totalExpense = 0;
  profit = 0;
  loss = 0;

  reportLoaded = false;

  // Focus elements
  @ViewChild('focus1') focus1: ElementRef;
  @ViewChild('focus2') focus2: ElementRef;
  @ViewChild('focus3') focus3: ElementRef;
  @ViewChild('focus4') focus4: ElementRef;
  branchList: any;
  ProfitLoss: any;
  formBuilder: any;
  isSuperAdmin: boolean;
  companyList: any;

  constructor(
    private fb: FormBuilder,
   private userservice: UserService, 
   private appService: AppService
  ) {}

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  
  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

   
  }

    ngOnInit() {
   this.balanceSheetForm = this.fb.group({
  tenant_id: [''],
  branch_id: [''],
  start_date: [null, Validators.required],
  end_date: [null, Validators.required],
});
  } 

  get f() {
    return this.balanceSheetForm.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;

    if (this.balanceSheetForm.invalid) {
      this.SubmitbuttonName = 'Submit';
      if (this.f.tenant_id.errors) this.focus1.nativeElement.focus();
      else if (this.f.branch_id.errors) this.focus2.nativeElement.focus();
      else if (this.f.start_date.errors) this.focus3.nativeElement.focus();
      else if (this.f.end_date.errors) this.focus4.nativeElement.focus();
      return;
    }

    const formValues = { ...this.balanceSheetForm.value };

   this.userservice.getData(formValues, 'balance-sheet', 'JSON').subscribe(
  (res: any) => {
    this.SubmitbuttonName = 'Submit';

    if (res.status === 'Success') {
      const data = res.data || {};
      const totals = res.totals || {};

      this.assets = data.assets || [];
      this.liabilities = data.liabilities || [];
      this.equity = data.equity || [];

      this.totalAssets = totals.total_assets || 0;
     
      this.totalEquity = totals.total_equity || 0;
      this.totalLiabilitiesEquity = totals.total_liabilities_equity || 0;

      this.totalLiabilities = this.liabilities.reduce((acc, item) => acc + Number(item.balance), 0);

      this.reportLoaded = true;
    } else {
      this.appService.showAlert('Balance Sheet Report', res.msg, 'error');
    }
  },
  err => {
    this.disableBtn = false;
    this.SubmitbuttonName = 'Submit';
    this.appService.showAlert('Balance Sheet', 'Sorry!! Something went wrong!', 'error');
  }
);
  }
}
