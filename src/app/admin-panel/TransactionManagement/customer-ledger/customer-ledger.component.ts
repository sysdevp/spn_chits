import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { AppService } from '../../../app.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';
import {saveAs} from 'file-saver';
import {ExcelService} from '../../../Services/excel.service';
import  XLSX from 'xlsx';
import { enrolNameWithAr, formatSlabWithArNo, slabLabelFromRow } from '../../../utils/enrollment-display.util';

@Component({
  
  selector: 'app-customer-ledger',
  templateUrl: './customer-ledger.component.html',
  styles: [`
    .stmt-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 12px; margin-bottom: 16px; }
    .stmt-toolbar label { font-weight: 600; margin-right: 8px; }
    .stmt-toolbar select { min-width: 280px; padding: 6px 10px; border: 1px solid #ced4da; border-radius: 4px; }
    #customerStatementPrint { background: #fff; color: #1a1a2e; font-family: 'Segoe UI', Tahoma, sans-serif; }
    .stmt-sheet { border: 1px solid #dfe6ee; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .stmt-head { display: flex; justify-content: space-between; align-items: flex-start; padding: 24px 28px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); color: #fff; }
    .stmt-head-left h2 { margin: 0 0 4px; font-size: 22px; font-weight: 700; letter-spacing: .3px; }
    .stmt-head-left p { margin: 0; opacity: .9; font-size: 13px; }
    .stmt-head-right { text-align: right; }
    .stmt-head-right img { max-height: 56px; max-width: 140px; background: #fff; padding: 6px 10px; border-radius: 6px; }
    .stmt-head-right .stmt-date { margin-top: 8px; font-size: 12px; opacity: .85; }
    .stmt-customer { padding: 18px 28px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .stmt-customer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px 24px; }
    .stmt-customer-grid span { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; color: #64748b; margin-bottom: 2px; }
    .stmt-customer-grid strong { font-size: 14px; color: #0f172a; }
    .stmt-metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; padding: 20px 28px; }
    .stmt-metric { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; }
    .stmt-metric label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .35px; color: #64748b; margin-bottom: 4px; }
    .stmt-metric .val { font-size: 17px; font-weight: 700; color: #0f766e; }
    .stmt-metric.highlight { background: #ecfdf5; border-color: #6ee7b7; }
    .stmt-metric.warn .val { color: #b45309; }
    .stmt-table-wrap { padding: 0 28px 24px; overflow-x: auto; }
    .stmt-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .stmt-table thead th { background: #0f766e; color: #fff; padding: 10px 8px; text-align: left; font-weight: 600; white-space: nowrap; }
    .stmt-table tbody td { padding: 9px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
    .stmt-table tbody tr:nth-child(even) { background: #f8fafc; }
    .stmt-table tbody tr:hover { background: #ecfdf5; }
    .stmt-table tfoot td { padding: 10px 8px; font-weight: 700; background: #f1f5f9; border-top: 2px solid #0f766e; color: #0f172a; }
    .stmt-empty { padding: 32px; text-align: center; color: #64748b; font-style: italic; }
    .stmt-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .stmt-badge.prized { background: #dcfce7; color: #166534; }
    .stmt-badge.not-prized { background: #fef3c7; color: #92400e; }
    .acct-section { padding: 0 28px 24px; }
    .acct-section h4 { font-size: 15px; font-weight: 700; color: #0f766e; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; }
    .acct-section-title { font-size: 18px; font-weight: 700; color: #0f766e; margin: 20px 0 12px; }
    .stmt-link { color: #0f766e; font-weight: 600; cursor: pointer; text-decoration: none; }
    .stmt-link:hover { text-decoration: underline; }
    .enrollment-card { margin-bottom: 16px; }
    @media print {
      .stmt-toolbar, .no-print { display: none !important; }
      #customerStatementPrint, #accountInfoPrint { box-shadow: none; }
      .stmt-sheet { border: none; }
    }
  `]
})

export class CustomerLedgerComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;

  companyLogo = "assets/img/logo1.jpg";
  CustomerLedger: FormGroup;
  companyList: any[];
  branchList: any[];
  customerList: any[];
  auctionwiseReceiptList: any[];
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  submitted: boolean;
  disableBtn: boolean;
  //auctionList: any[];
  //branchId: any;
  //customerId: any;
  //CustomerDetailList: any[];
  runningGroupList: any[];
  //paidPendingList: any[];
  customerDocumentInfo: any[];
  enrollmentInfo: any[];
  photo: any;
  //GurantorDocumentInfo: any;
  hidecustomerLedger:boolean=true;
  relationshipList: any;
  hideClosedGroup:boolean=true;
  hiderunningGroup:boolean=true;
  hideEnrolmentDetails:boolean=true;
  hideLoanDetails:boolean=true;
  hideEnrolment1Details:boolean=true;
  hideEnrolment2Details:boolean=true;
  hidecustomerstatementDetails:boolean=true;
  closedGroupList: any[];
  gurantorDocumentInfo: any[];
  panDocument: any;
  rationDocument: any;
  adharDocument: any;
  auctionwiseList: any[];
  groupadvanceList: any;
  grouppigmyList: any;
  disbursementRowLength: number = 0;
  disbursementInfo: any[];
  disbursementPayment: any[];
  enrolList: any[];
  loanList: any[];
  EnrolId: any[];
  //Customer Information
  name: any;
  customer_code:any;
  branchName: any;
  MobileNo: any;
  Status: any;
  Address_1: any;
  Address_2: any;
  communicationAddress_1: any;
  communicationAddress_2: any;
  relationship: any;
  prizedPending: any;
  nonPrizedPending: any;
  prizedPaid: any;
  nonPrizedPaid: any;
  groupName: any;
  customerTotalChitVaue: any;
  gurantorTotalChitVaue: any;
  //runningGroupTotalChitVaue: any;
  closedGroupTotalChitVaue: any;
  ticketNo: any;
  totalClosedGroupChitValue: number;
  totalRunningGroupChitValue: any;
  customerDocumentTotalChitValue: any;
  gurantorDocumentTotalChitValue: any;
  deductions: any;
  LoanId: any;
  loanreceiptList: any[];
  loandetailList: any;
  chitValue: any;
  prizedStatus: any;
  completedAuctions: any;
  paidAmt: any;
  pendingAmt: any;
  advanceAmt: any;
  nextDueAmt: any;
  pendingInstallment: any;
  hidecustomDetails: boolean=false;

  custname: any;
  custid: any;
  nomineeDocumentInfo: any;
  nomineeTotalChitVaue: any;
  nomineeDocumentTotalChitValue: number;
  LoanstatementId: any;
  statementList: any;
  credit_amount: any;
  totalCredit: any;
  credit_receipt_amount: any;
  penalty_amount: any;
  totalpenalty_amount: any;
  cheque_amount: any;
  totalcheque_amount: any;
  total_paid_amount: any;
  totalpaid_amount: any;
  chit_value: any;
  logo: any;
  monthly_subscription: any;
  term_period: any;
  ticket_no: any;
  first_auction_date: any;
  auction_completed: any;
  incomplete_auction: any;
  double_auction: any;
  amount_to_be_paid: any;
  paid_amount: any;
  due_balance: any;
  divident_receipt_amount: any;
  totaldivident_receipt_amount: any;
  back_by_receipt_amount: any;
  totalback_by_receipt_amount: any;
  receipt_total: any;
  penalty_total: any;
  dividend_total: any;
  cheque_charges_total: any;
  total_paid_total: any;
  total_discount: any;
  total_received_back: any;
  gurantorInfo: any;
  nomineeInfo: any;
  EnrolIdvalue: number;
  beforeenrolreceipts: any;
  totalReceiptAmt: any;
  referred_name: number;
  termination_date: any;
  prized_status: any;
  slab_name: any;
  total_months: any;
  running_month: any;
  prized_status_label: any;
  statement_date: any;
  group_name: any;
  pledgeInfo: any;
  accountSlabName: any;
  accountChitValue: any;
  accountTotalMonths: number;
  accountRunningMonth: number;
  accountPaidAmount: any;
  accountPendingAmount: any;
  accountCompanyReleased: any;
  accountCompanyPending: any;
  commitmentPaymentList: any[];
  otherchargeInfo: any;
  customeradvanceList: any;

  private pendingDeepLink: { branch_id?: string; customer_id?: string } = null;

  constructor(
    private excelService: ExcelService,
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.SubmitbuttonName = "Submit";
    if((localStorage.getItem('company_logo') != null) && (localStorage.getItem('company_logo') != ''))
    {
      this.companyLogo = localStorage.getItem('company_logo');
    }
  }

  get f() {
    return this.CustomerLedger.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.CustomerLedger.get('branch_id').setValue('');
    this.branchList = [];
    this.CustomerLedger.get('customer_id').setValue('');
    this.customerList = [];
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id, afterLoad?: () => void) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      } catch (err) { }
      if (afterLoad) {
        afterLoad();
      }
    }, (err: any) => {
      if (afterLoad) {
        afterLoad();
      }
    });
  }

  /** Load customer dropdown for a branch, then optionally select + open ledger. */
  loadCustomersForBranch(branchId: any, selectCustomerId?: any, autoSubmit?: boolean) {
    if (!branchId) {
      return;
    }
    this.CustomerLedger.get('branch_id').setValue(String(branchId));
    const cond = {
      "conditions[branch_id]": branchId,
    };
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      } catch (err) { }

      if (selectCustomerId) {
        this.CustomerLedger.get('customer_id').setValue(String(selectCustomerId));
        this.custid = String(selectCustomerId);
        this.customerChg({ target: { value: String(selectCustomerId) } });
        if (autoSubmit) {
          setTimeout(() => this.onSubmit(), 150);
        }
      }
    }, (err: any) => { });
  }

  applyDeepLinkFromQuery() {
    const qp = this.route.snapshot.queryParams || {};
    const branchId = qp['branch_id'];
    const customerId = qp['customer_id'];
    if (!customerId) {
      return;
    }
    this.pendingDeepLink = { branch_id: branchId, customer_id: customerId };
    if (branchId) {
      this.loadCustomersForBranch(branchId, customerId, true);
    } else {
      // Resolve branch from customer record
      this.userservice.getData({ "conditions[id]": customerId }, "get-customers", "POST").subscribe((response: any) => {
        try {
          if (response.status === 'Success' && response.data && response.data[0]) {
            const c = response.data[0];
            const bid = c.branch_id || (c.branch_info && c.branch_info.id);
            if (bid) {
              this.loadCustomersForBranch(bid, customerId, true);
            }
          }
        } catch (err) { }
      });
    }
  }
  
  branchChg(event)
  {
    this.hidecustomerLedger=true;
    this.hideEnrolmentDetails=true;
    this.hideLoanDetails=true;
    this.hideEnrolment1Details=true;
    this.hideEnrolment2Details=true;
    this.hidecustomDetails=false;
    this.CustomerLedger.get('customer_id').setValue('');
    this.customerList = [];
    let cond = {
      "conditions[branch_id]" : event.target.value,
    }
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  customerChg(event)
  {
    this.custid = event.target.value;
    this.hidecustomerLedger=true;
    this.hideEnrolmentDetails=true;
    this.hideLoanDetails=true;
    this.hideEnrolment1Details=true;
    this.hideEnrolment2Details=true;
    this.hidecustomDetails=false;
    //this.customerId=event.target.value;
    this.auctionwiseReceiptList = [];
    this.auctionwiseList = [];
    this.groupadvanceList = [];
    this.grouppigmyList = [];
    this.disbursementInfo = [];
    this.deductions=[];
    this.disbursementPayment=[];

    let cond1 = {
      "customer_id" : this.custid,
      "branch_id" : this.CustomerLedger.get('branch_id').value,
    }
    this.userservice.getData(cond1, "list-before-enrol-receipts-customer", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.beforeenrolreceipts = response.data;
          this.totalReceiptAmt = response.grand_total;
      }else {
          this.beforeenrolreceipts = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    const result = Object.assign({}, this.CustomerLedger.value);
    if (this.CustomerLedger.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if (this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.hidecustomerLedger=false;
    this.hiderunningGroup=true;
    this.hideClosedGroup=true;
    let cond = {
      //"conditions[branch_id]" : this.CustomerLedger.get('branch_id'),
      "conditions[id]" : this.custid,
    }
    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.SubmitbuttonName = "Submit";
          //this.CustomerDetailList = response.data;
          this.photo=response.data[0].photo_for_view;
          this.name=response.data[0].name;
          this.customer_code=response.data[0].customer_code;
          this.branchName=response.data[0].branch_info.branch_name;
          this.MobileNo=response.data[0].mobile_no;
          this.Status=response.data[0].status;
          this.Address_1=response.data[0].address_line_1;
          this.Address_2=response.data[0].address_line_2;
          this.communicationAddress_1=response.data[0].communication_address_line_1;
          this.communicationAddress_2=response.data[0].communication_address_line_2;
          this.relationship=response.data[0].relationship_info;

      }else {
          //this.CustomerDetailList = [];
        }
      }  catch (err) { }
    }, (err: any) => { 
      
    });

    let cond1 = {
      "conditions[id]" : this.custid,
    }
    this.userservice.getData(cond1, "customer-ledger-relationship-det", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.relationshipList = response.data;
          
      }else {
          this.relationshipList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    let cond2 = {
      "conditions[customer_id]" : this.custid,
    }
    let cond3 = {
      "customer_id" : this.custid,
    }
    this.userservice.getData(cond2, "cust-ledger-paid-and-pending", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          //this.paidPendingList = response.data;
          this.nonPrizedPaid=response.data.non_prized_paid;
          this.prizedPaid=response.data.prized_paid;
          this.nonPrizedPending=response.data.non_prized_pending;
          this.prizedPending=response.data.prized_pending;
          
          
      }else {
          //this.paidPendingList = [];
        }
      }  catch (err) { 
        
      }
    }, (err: any) => { });

    this.userservice.getData(cond3, "get-pigmy-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.grouppigmyList = response.data;
        
          
      }else {
          this.grouppigmyList = [];
        }
      }  catch (err) { 
        
      }
    }, (err: any) => { });
    
    this.userservice.getData(cond2, "customer-ledger-customer-document-info", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerDocumentInfo = response.data;
          
          
      }else {
          this.customerDocumentInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {  });

    this.userservice.getData(cond3, "enrollment-wise-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollmentInfo = (response.data || []).map((row: any) => {
            row.slab_name = this.formatEnrollmentSlab(row);
            return row;
          });
         /*  this.ticketNo=response.data[0].ticket_no;
          this.chitValue=response.data[0].chit_value;
          //this.chitValue=response.data[0].chit_value;
          this.prizedStatus=response.data[0].prized_status;
          this.completedAuctions=response.data[0].completed_auction;
          this.paidAmt=response.data[0].paid_amount;
          this.pendingAmt=response.data[0].pending_amount;
          this.advanceAmt=response.data[0].advance_amount;
          this.nextDueAmt=response.data[0].next_due;
          this.pendingInstallment=response.data[0].pending_inst; */
      }else {
          this.enrollmentInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {  });

    /*this.userservice.getData(cond2, "customer-ledger-guarantor-document-info", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.gurantorDocumentInfo = response.data;
          this.gurantorTotalChitVaue=response.data[0].total_chit_value;
          this.gurantorDocumentTotalChitValue=0;
          for(let i=0;i<=response.data.length;i++)
          {
            this.gurantorDocumentTotalChitValue=response.data[i].total_chit_value;
            //console.log(this.gurantorDocumentTotalChitValue);
          }
      }else {
          this.gurantorDocumentInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});*/

    this.userservice.getData(cond2, "get-pledge-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.pledgeInfo = response.data;
          
      }else {
          this.pledgeInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});

    this.userservice.getData(cond2, "get-othercharge-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.otherchargeInfo = response.data;
          
      }else {
          this.otherchargeInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});

    this.userservice.getData(cond2, "get-guarantors", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.gurantorInfo = response.data;
          
      }else {
          this.gurantorInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});

    this.userservice.getData(cond2, "get-nominees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.nomineeInfo = response.data;
          
      }else {
          this.nomineeInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});

    this.userservice.getData(cond2, "customer-ledger-nominee-document-info", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.nomineeDocumentInfo = response.data;
          this.nomineeTotalChitVaue=response.data[0].total_chit_value;
          this.nomineeDocumentTotalChitValue=0;
          for(let i=0;i<=response.data.length;i++)
          {
            this.nomineeDocumentTotalChitValue=response.data[i].total_chit_value;
            //console.log(this.gurantorDocumentTotalChitValue);
          }
      }else {
          this.nomineeDocumentInfo = [];
        }
      }  catch (err) { }
    }, (err: any) => {});

    this.userservice.getData(cond2, "get-enrolls-for-customer-ledger-without-trash", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          // Customer statement should show only Commitment Chits
          this.enrolList = (response.data || [])
            .filter((e: any) => Number(e.chit_type) === 3)
            .map((e: any) => {
              e.enrol_name = this.formatEnrollmentName(e);
              return e;
            });
          /*  this.groupName=response.data[0].group_info.group_name;
          this.ticketNo=response.data[0].ticket_no;  */
        } else {
          this.enrolList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond2, "get-loan-detail", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.loanList = response.data;
          /*  this.groupName=response.data[0].group_info.group_name;
          this.ticketNo=response.data[0].ticket_no;  */
        } else {
          this.loanList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

    formatEnrollmentSlab(row: any): string {
    return slabLabelFromRow(row) || '-';
  }

  formatEnrollmentName(row: any): string {
    return enrolNameWithAr(row);
  }

  runningGroupClick()
    {
      this.hiderunningGroup=false;
      this.hideClosedGroup=true;
      let cond = {
        "conditions[customer_id]" : this.CustomerLedger.get('customer_id').value,
      }
     this.userservice.getData(cond, "customer-ledger-running-enroll-info", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') { 
            this.runningGroupList = response.data;
            this.totalRunningGroupChitValue=0;
            for(let i=0;i<=response.data.length;i++)
            {
              this.totalRunningGroupChitValue=response.data[i].chit_value;
            }
        }else {
            this.runningGroupList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }

    closedGroupClick()
    {
      this.hiderunningGroup=true;
      this.hideClosedGroup=false;
      let cond = {
        "conditions[customer_id]" : this.CustomerLedger.get('customer_id').value,
      }
     this.userservice.getData(cond, "customer-ledger-closed-enroll-info", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') { 
            this.closedGroupList = response.data;
            this.closedGroupTotalChitVaue=response.data.total_chit_value;
            this.totalClosedGroupChitValue=0;
            for(let i=0;i<=response.data.length;i++)
            {
              this.totalClosedGroupChitValue=response.data[i].chit_value;
              console.log(this.totalClosedGroupChitValue)
            }
            
        }else {
            this.closedGroupList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
    myClickFunction(id) {
      this.hidecustomDetails = true;
      this.hideEnrolment1Details = true;
      this.loadAccountDetails(id, 'tab');
      const tabEl = document.querySelector('#profile-tab') as HTMLElement;
      if (tabEl) {
        tabEl.click();
      }
      setTimeout(() => {
        const sel = document.getElementById('accountEnrollSelect') as HTMLSelectElement;
        if (sel) {
          sel.value = String(id);
        }
      }, 0);
    }

    enrollChg(event) {
      const enrolId = event.target.value;
      if (!enrolId) {
        this.hideEnrolmentDetails = true;
        return;
      }
      this.loadAccountDetails(enrolId, 'tab');
    }

    loadAccountDetails(enrolId: any, source: string) {
      this.EnrolId = enrolId;
      this.commitmentPaymentList = [];
      if (source === 'tab') {
        this.hideEnrolmentDetails = false;
      } else {
        this.hideEnrolment1Details = false;
      }

      const enrol = (this.enrollmentInfo || []).find((e: any) => String(e.id) === String(enrolId));
      if (enrol) {
        this.accountChitValue = parseFloat(enrol.chit_value || 0);
        this.accountPaidAmount = parseFloat(enrol.paid_amount || 0);
        this.accountPendingAmount = parseFloat(enrol.pending_amount || 0);
        this.accountSlabName = this.formatEnrollmentSlab(enrol);
      }

      const cond = { "conditions[id]": enrolId };
      const cond1 = { "conditions[enrollment_id]": enrolId };
      const payCond = { "conditions[enrollment_id]": enrolId };

      this.userservice.getData(cond, "customer-ledger-get-enrol-details", "POST").subscribe((response: any) => {
        if (response.status == 'Success') {
          const enrol = (this.enrollmentInfo || []).find((e: any) => String(e.id) === String(enrolId));
          const baseName = response.data.group_name || response.data.slab_name || '';
          this.accountSlabName = formatSlabWithArNo(baseName, enrol && enrol.ar_no);
          this.groupName = this.accountSlabName;
          this.ticketNo = (enrol && enrol.ar_no) ? enrol.ar_no : response.data.ticket_no;
        }
      });

      this.userservice.getData(cond, "get-auctionwise-receipt", "POST").subscribe((response: any) => {
        if (response.status == 'Success') {
          this.auctionwiseReceiptList = this.normalizeAuctionReceiptRows(response.data || []);
          this.accountTotalMonths = this.auctionwiseReceiptList.length;
          this.accountRunningMonth = this.countRunningMonths(this.auctionwiseReceiptList);
        } else {
          this.auctionwiseReceiptList = [];
          this.accountTotalMonths = 0;
          this.accountRunningMonth = 0;
        }
      });

      this.userservice.getData(cond, "auction-wise-to-be-collected", "POST").subscribe((response: any) => {
        const summary = this.extractToBeCollectedSummary(response);
        if (response.status == 'Success' && summary) {
          this.auctionwiseList = summary.pending_details || [];
          if (summary.chit_value != null && summary.chit_value !== '') {
            this.accountChitValue = parseFloat(summary.chit_value);
          }
          if (summary.overall_paid_amount != null && summary.overall_paid_amount !== '') {
            this.accountPaidAmount = parseFloat(summary.overall_paid_amount);
          }
          if (summary.overall_pending_amount != null && summary.overall_pending_amount !== '') {
            this.accountPendingAmount = parseFloat(summary.overall_pending_amount);
          }
          if (!this.accountSlabName && summary.group_name) {
            this.accountSlabName = summary.group_name;
          }
        } else {
          this.auctionwiseList = [];
        }
      });

      this.userservice.getData(cond1, "customer-ledger-group-advance-details", "POST").subscribe((response: any) => {
        this.groupadvanceList = response.status == 'Success' ? response.data : [];
      });

      this.userservice.getData(payCond, "get-commitment-payments", "POST").subscribe((response: any) => {
        if (response.status == 'Success') {
          this.commitmentPaymentList = response.data || [];
          let released = 0;
          (this.commitmentPaymentList || []).forEach((p: any) => {
            released += parseFloat(p.total_amount || 0);
          });
          this.accountCompanyReleased = released;
          const totalRelease = enrol
            ? parseFloat(enrol.total_payment_release || enrol.chit_value || 0)
            : this.accountChitValue;
          this.accountCompanyPending = Math.max(0, totalRelease - released);
        } else {
          this.commitmentPaymentList = [];
          this.accountCompanyReleased = 0;
          this.accountCompanyPending = 0;
        }
      });
    }

    normalizeAuctionReceiptRows(rows: any[]): any[] {
      return (rows || []).map((row: any) => {
        const dueAmount = row.due_amount != null ? row.due_amount : row.current_installment_amount;
        return {
          ...row,
          due_amount: dueAmount,
          current_installment_amount: row.current_installment_amount != null
            ? row.current_installment_amount
            : dueAmount
        };
      });
    }

    extractToBeCollectedSummary(response: any): any {
      const data = response && response.data;
      if (!data) {
        return null;
      }
      if (Array.isArray(data)) {
        return data.find((row: any) => row && row.pending_details) || data[0] || null;
      }
      if (typeof data === 'object') {
        const values: any[] = [];
        for (const key in data) {
          if (data.hasOwnProperty(key)) {
            values.push(data[key]);
          }
        }
        const summary = values.find(
          (row: any) => row && typeof row === 'object' && row.pending_details
        );
        return summary || data[0] || data['0'] || null;
      }
      return null;
    }

    getAccountDueAmount(row: any): number {
      if (!row) {
        return 0;
      }
      const amount = row.due_amount != null ? row.due_amount : row.current_installment_amount;
      return parseFloat(amount || 0);
    }

    getAccountPendingDays(row: any): number {
      if (!row) {
        return 0;
      }
      const pendingAmt = parseFloat(row.inst_amt_wise_pending || 0);
      if (pendingAmt <= 0) {
        return 0;
      }
      const dueDate = this.parseDisplayDate(row.auction_date);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (!dueDate || dueDate > today) {
        return 0;
      }
      const diffMs = today.getTime() - dueDate.getTime();
      return Math.max(0, Math.floor(diffMs / (1000 * 60 * 60 * 24)));
    }

    countRunningMonths(rows: any[]): number {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      let count = 0;
      (rows || []).forEach((row: any) => {
        const d = this.parseDisplayDate(row.auction_date);
        if (d && d <= today) {
          count++;
        }
      });
      return count;
    }

    parseDisplayDate(value: string): Date | null {
      if (!value || value === '0000-00-00') {
        return null;
      }
      if (value.indexOf('-') === 2) {
        const p = value.split('-');
        return new Date(parseInt(p[2], 10), parseInt(p[1], 10) - 1, parseInt(p[0], 10));
      }
      const d = new Date(value);
      return isNaN(d.getTime()) ? null : d;
    }

    onAccountPdf(): void {
      const el = document.getElementById('accountInfoPrint');
      if (!el) {
        return;
      }
      html2canvas(el, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        logging: false
      }).then(canvas => {
        const imgWidth = 210;
        const pageHeight = 297;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        let heightLeft = imgHeight;
        const pdf = new jspdf('p', 'mm', 'a4');
        let position = 0;
        const contentDataURL = canvas.toDataURL('image/png');
        pdf.addImage(contentDataURL, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
        while (heightLeft > 0) {
          position = heightLeft - imgHeight;
          pdf.addPage();
          pdf.addImage(contentDataURL, 'PNG', 0, position, imgWidth, imgHeight);
          heightLeft -= pageHeight;
        }
        pdf.save(`AccountInformation_${this.customer_code || 'report'}.pdf`);
      });
    }

    loanChg(event)
    {
      this.hideEnrolmentDetails=false;
      this.LoanId=event.target.value;

      let cond = {
        "conditions[id]" : this.LoanId,
      }
      let cond1 = {
        "conditions[loan_id]" : this.LoanId,
      }
      console.log(cond);
      this.userservice.getData(cond, "get-loan-detail", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') { 
            this.loandetailList = response.data;
            if(response.data.length > 0){
              this.hideLoanDetails=false;
            }
            else{
              this.hideLoanDetails=true;
            }
        }else {
            this.loandetailList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });

      this.userservice.getData(cond1, "get-loan-receipt", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') { 
            this.loanreceiptList = response.data;
            if(response.data.length > 0){
              this.hideLoanDetails=false;
            }
            else{
              this.hideLoanDetails=true;
            }
        }else {
            this.loanreceiptList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    
    }

    enrollstatementChg(event)
    {
      
      this.LoanstatementId=event.target.value;
      this.EnrolIdvalue=event.target.value;
      if(!this.LoanstatementId) {
        this.hideEnrolment2Details = true;
        this.hidecustomerstatementDetails = true;
        this.statementList = [];
        return;
      }
      let cond = {
        "enrollment_id" : this.LoanstatementId,
      }
      
      this.userservice.getData(cond, "customer-statement", "POST").subscribe((response: any) => {
        this.totalCredit=0;
        try { 
          
          if (response.status == 'Success') {
            this.hideEnrolment2Details=false;
            this.hidecustomerstatementDetails=false;

            const rows = Array.isArray(response.data) ? response.data : [];
            const meta = response.meta || {};
            this.statementList = rows.filter((r: any) => r && r.sno > 0);
            this.statement_date = new Date().toLocaleDateString('en-GB');
            this.chit_value = meta.chit_value;
            this.logo = this.companyLogo;
            this.monthly_subscription = meta.monthly_subscription;
            this.term_period = meta.term_period;
            this.ticket_no = meta.ticket_no || '-';
            const stmtEnrol = (this.enrolList || []).find((e: any) => String(e.id) === String(this.LoanstatementId));
            const stmtAr = (stmtEnrol && stmtEnrol.ar_no) || meta.ar_no || '';
            this.ticket_no = stmtAr || this.ticket_no;
            this.slab_name = formatSlabWithArNo(meta.slab_name || meta.group_name || '', stmtAr) || '-';
            this.first_auction_date = meta.first_auction_date;
            this.total_months = meta.total_months != null ? meta.total_months : meta.auction_completed;
            this.running_month = meta.running_month != null ? meta.running_month : meta.incomplete_auction;
            this.amount_to_be_paid = meta.amount_to_be_paid;
            this.paid_amount = meta.paid_amount;
            this.due_balance = meta.due_balance;
            this.receipt_total = response.receipt_total;
            this.penalty_total = response.penalty_total;
            this.dividend_total = response.dividend_total;
            this.cheque_charges_total = response.cheque_charges_total;
            this.total_paid_total = response.total_paid_total;
            this.total_discount = response.total_discount;
            this.total_received_back = response.total_received_back;
            this.prized_status_label = meta.prized_status_label || (meta.prized_status == 1 ? 'Prized' : 'Not Prized');
            this.prized_status = meta.prized_status;
            this.group_name = meta.group_name || meta.slab_name;
            /*for(let i=0;i<response.data.length;i++){
              //this.total = response.data[i].credit_amount;
              this.credit_amount = 0;
              if(response.data[i].type == 'receipt' && response.data[i].type_shown == 'B.P.Adjustment'){
                console.log("ss");
                this.credit_amount= response.data[i].receipt_amount != "" ? response.data[i].receipt_amount : 0;
              }
              this.totalCredit = parseFloat(this.totalCredit)+ parseFloat(this.credit_amount);
                
          }
          this.total_received_back = this.total_received_back + this.totalCredit;*/
        }else {
            this.hideEnrolment2Details=true;
            this.hidecustomerstatementDetails=true; 
            this.statementList = [];
            this.appService.showAlert("Customer Statement", response.msg || "No data found", "error");
          }
        }  catch (err) {
          this.hideEnrolment2Details=true;
          this.hidecustomerstatementDetails=true;
          this.statementList = [];
        }
      }, (err: any) => {
        this.hideEnrolment2Details=true;
        this.hidecustomerstatementDetails=true;
        this.statementList = [];
        this.appService.showAlert("Customer Statement", "Sorry!! Something went wrong!", "error");
      });
    
    }

    onpdf():void
  {  
    /*var data = document.getElementById('contentToConvert');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 200;   
      var pageHeight = 175;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 10;  
      pdf.addImage(contentDataURL, 'JPEG', 5, position, imgWidth, imgHeight)  
      pdf.save('Receiptreport.pdf'); // Generated PDF   
    }); */ 
    var data = document.getElementById('mygrid');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 210;   
      var pageHeight = 295;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 0;  
      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight) 
      heightLeft -= pageHeight; 
      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      } 
      pdf.save('Customerledger.pdf'); // Generated PDF   
    });
  }

  onpdfloan():void
  {  
    /*var data = document.getElementById('contentToConvert');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 200;   
      var pageHeight = 175;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 10;  
      pdf.addImage(contentDataURL, 'JPEG', 5, position, imgWidth, imgHeight)  
      pdf.save('Receiptreport.pdf'); // Generated PDF   
    }); */ 
    var data = document.getElementById('mygrid1');  
    html2canvas(data).then(canvas => {  
      // Few necessary setting options  
      var imgWidth = 210;   
      var pageHeight = 295;    
      var imgHeight = canvas.height * imgWidth / canvas.width;  
      var heightLeft = imgHeight;  
  
      const contentDataURL = canvas.toDataURL('image/jpeg')  
      let pdf = new jspdf('p', 'mm', 'a4'); // A4 size page of PDF  
      var position = 0;  
      pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight) 
      heightLeft -= pageHeight; 
      while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      } 
      pdf.save('CustomerledgerLoan.pdf'); // Generated PDF   
    });
  }

  onpdfcustomerstatement(): void {
    const el = document.getElementById('customerStatementPrint');
    if (!el) {
      return;
    }
    html2canvas(el, {
      scale: 2,
      useCORS: true,
      backgroundColor: '#ffffff',
      logging: false
    }).then(canvas => {
      const imgWidth = 210;
      const pageHeight = 297;
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      let heightLeft = imgHeight;
      const pdf = new jspdf('p', 'mm', 'a4');
      let position = 0;
      const contentDataURL = canvas.toDataURL('image/png');
      pdf.addImage(contentDataURL, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
      while (heightLeft > 0) {
        position = heightLeft - imgHeight;
        pdf.addPage();
        pdf.addImage(contentDataURL, 'PNG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
      }
      const fileName = `CustomerStatement_${this.customer_code || 'report'}.pdf`;
      pdf.save(fileName);
    });
  }

  isStatementPaymentRow(row: any): boolean {
    return row && ['Payment', 'CGST', 'SGST', 'Bidding', 'Commission'].indexOf(row.type) >= 0;
  }

  getStatementCredit(row: any): number {
    if (!row || this.isStatementPaymentRow(row)) {
      return 0;
    }
    return row.receipt_amount || 0;
  }

  getStatementDebit(row: any): number {
    if (!row || !this.isStatementPaymentRow(row)) {
      return 0;
    }
    return row.receipt_amount || 0;
  }
   
  exportAsXLSX() {
    let element = document.getElementById('stmtTransactionsTable'); 
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(element);
 
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpaid.xls`;
    /* save to file */
    XLSX.writeFile(wb, "SubscriberStatement.csv");
  }

  mail() {
    this.hidecustomerstatementDetails=true;
    let cond = {
      "tenant_id" : this.CustomerLedger.get('tenant_id').value,
      "branch_id" : this.CustomerLedger.get('branch_id').value,
      "enroll_id" : this.EnrolIdvalue,
      "user_id" : localStorage.getItem('logged_user_id')
    }
    this.userservice.getData(cond, "customer-statement-mail", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.hidecustomerstatementDetails=false;
          this.appService.showAlert("Mail Sent Successfully",response.msg, "success");
          this.companyList = response.data;
        } else {
          this.appService.showAlert("Sent Mail", response.msg, "error");
          this.companyList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
   
  }
  
    ngOnInit() {
    this.CustomerLedger = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      enrollment_id: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.CustomerLedger.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id, () => this.applyDeepLinkFromQuery());
    }
    else {
      this.isSuperAdmin = true;
      this.userservice.getData('', "list-tenants", "POST").subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        } catch (err) { }
        this.applyDeepLinkFromQuery();
      }, (err: any) => { });
    }

    this.route.queryParams.subscribe((qp) => {
      if (qp && qp['customer_id'] && this.CustomerLedger) {
        if (this.pendingDeepLink && String(this.pendingDeepLink.customer_id) === String(qp['customer_id'])) {
          return;
        }
        this.applyDeepLinkFromQuery();
      }
    });
  }

  }
