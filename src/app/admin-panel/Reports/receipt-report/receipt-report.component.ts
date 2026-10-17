import { Component, OnInit, ElementRef, ViewChild, Renderer2, ViewEncapsulation } from '@angular/core';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
//import {ReportPopupModalComponent} from '../../../../modal/report-popup-modal/report-popup-modal.component';
import {ReportRedirectModalComponent} from '../../../modal/report-redirect-modal/report-redirect-modal.component';
import { NgxSmartModalService } from 'ngx-smart-modal';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';
import { getGridReportBaseUrl, gridDeleteAction, gridPrintAction } from '../../utils/grid-action.util';

@Component({
  
  selector: 'app-receipt-report',
  templateUrl: './receipt-report.component.html',
  styles: []
})

export class ReceiptReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('e1') e1: ElementRef;
  @ViewChild('e1table') e1table: ElementRef;

  ReceiptReport: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden: boolean = true;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  isSuperAdmin: boolean;
  submitted: boolean;
  chequeSubmitted: boolean;
  disableBtn: boolean;
  disableChequeBtn: boolean;
  customerList: any[];
  enrollmentList: any[];
  employeeList: any[];
  paymentList: any[];
  bankList: any[];
  ScrollCls: string = "";
  Scrollwidth: number;
  ScrollOpacity: number=0;
  isChequeReturnClk: boolean = false;
  isChequeClearClk: boolean = false;
  paginationPageSize;
  paginationNumberFormatter;
  gridApi;
  defaultColDef;
  columnDefs: any[];
  rowData: any[];
  gridOptions: any;
  hideReport: boolean = true;
  hideDataList: boolean = true;
  nodataFoundMessage: boolean=true;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  ChequeGroupId: any;
  totalBonusAmt: any;
  totalPenaltyAmt: any;
  totalReceiptAmt: any;
  totalReceivedAmt: any;
  selectionfield: boolean=false;
  pdfrectab: boolean = true;
  viewreceiptInfo: ViewData[]=[];
  receiptDateBoundsHint = '';
  private receiptReportAutoRetry = false;

  constructor(public ngxSmartModalService: NgxSmartModalService,private modalService: NgbModal,private renderer: Renderer2, private formBuilder: FormBuilder, private appService: AppService, private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
    this.SubmitChequeButtonName = "Submit";
    this.gridOptions = <GridOptions>{
      suppressColumnVirtualisation: true,
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this,
        ngxSmartModalService: this.ngxSmartModalService,
        ChequeGroup: this.ChequeGroup,
        //AddAdvancedReceiptReport: this.AddAdvancedReceiptReport
      },
      components: {
      },
    };
  }

  get f() {
    return this.ReceiptReport.controls;
  }

  get cf() {
    return this.ChequeGroup.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.ReceiptReport.get('branch_id').setValue('');
    this.branchList = [];
    this.ReceiptReport.get('group_id').setValue('');
    this.groupList = [];
    this.ReceiptReport.get('customer_id').setValue('');
    this.customerList = [];
    this.ReceiptReport.get('employee_id').setValue('');
    this.employeeList = [];
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    let cond = {
      "conditions[tenant_id]": tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
          const curBranch = this.ReceiptReport.get('branch_id').value;
          if (curBranch) {
            this.loadReceiptDateBounds(String(curBranch), false);
          } else if (this.branchList.length === 1) {
            const bid = String(this.branchList[0].id);
            this.ReceiptReport.patchValue({ branch_id: bid });
            this.loadReceiptDateBounds(bid, false);
          }
        } else {
          this.branchList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData('', "list-payment-types", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.paymentList = response.data;
        } else {
          this.paymentList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  private ymdToNgb(ymd: string): { year: number; month: number; day: number } | null {
    if (!ymd) {
      return null;
    }
    const parts = ymd.split('-');
    if (parts.length !== 3) {
      return null;
    }
    return { year: +parts[0], month: +parts[1], day: +parts[2] };
  }

  private currentMonthStart(): { year: number; month: number; day: number } {
    const today = new Date();
    return { year: today.getFullYear(), month: today.getMonth() + 1, day: 1 };
  }

  private currentMonthEnd(): { year: number; month: number; day: number } {
    const today = new Date();
    const day = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
    return { year: today.getFullYear(), month: today.getMonth() + 1, day };
  }

  private applyBoundsToForm(bounds: { min_date: string; max_date: string; total_in_scope?: number; total_receipts?: number }): boolean {
    if (!bounds.min_date || !bounds.max_date) {
      return false;
    }
    const today = new Date();
    const monthStart = {
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: 1,
    };
    const monthEndDay = new Date(today.getFullYear(), today.getMonth() + 1, 0).getDate();
    const monthEnd = {
      year: today.getFullYear(),
      month: today.getMonth() + 1,
      day: monthEndDay,
    };
    const clamped = this.clampDateRangeToBounds(monthStart, monthEnd, bounds.min_date, bounds.max_date);
    this.ReceiptReport.patchValue({ start_date: clamped.start, end_date: clamped.end });
    const total = bounds.total_in_scope != null ? bounds.total_in_scope : bounds.total_receipts;
    this.receiptDateBoundsHint =
      'Branch receipts available ' +
      bounds.min_date +
      ' to ' +
      bounds.max_date +
      (total != null ? ' (' + total + ' total).' : '.') +
      ' Default filter is the current month Ã¢â‚¬â€ adjust dates before Filter.';
    return true;
  }

  private clampDateRangeToBounds(
    start: { year: number; month: number; day: number },
    end: { year: number; month: number; day: number },
    minYmd: string,
    maxYmd: string
  ): { start: { year: number; month: number; day: number }; end: { year: number; month: number; day: number } } {
    const toNum = (d: { year: number; month: number; day: number }) =>
      d.year * 10000 + d.month * 100 + d.day;
    const minParts = minYmd.split('-').map((p) => +p);
    const maxParts = maxYmd.split('-').map((p) => +p);
    const minNum = minParts[0] * 10000 + minParts[1] * 100 + minParts[2];
    const maxNum = maxParts[0] * 10000 + maxParts[1] * 100 + maxParts[2];
    let startNum = toNum(start);
    let endNum = toNum(end);
    if (startNum < minNum) {
      startNum = minNum;
    }
    if (endNum > maxNum) {
      endNum = maxNum;
    }
    if (startNum > endNum) {
      startNum = minNum;
      endNum = maxNum;
    }
    const unpack = (n: number) => ({
      year: Math.floor(n / 10000),
      month: Math.floor((n % 10000) / 100),
      day: n % 100,
    });
    return { start: unpack(startNum), end: unpack(endNum) };
  }

  private loadReceiptDateBounds(branchId: string, runFilterAfter?: boolean) {
    if (!branchId) {
      this.receiptDateBoundsHint = '';
      return;
    }
    const payload = {
      tenant_id: this.ReceiptReport.get('tenant_id').value,
      branch_id: branchId,
    };
    this.userservice.getData(payload, 'receipt-report-bounds', 'JSON').subscribe((res: any) => {
      if (res.status === 'Success' && res.data && res.data.min_date && res.data.max_date) {
        if (this.applyBoundsToForm(res.data) && runFilterAfter) {
          this.receiptReportAutoRetry = true;
          this.runReceiptReport();
        }
      } else {
        this.receiptDateBoundsHint = '';
      }
    }, () => {
      this.receiptDateBoundsHint = '';
    });
  }

  branchChg(event) {
    this.ReceiptReport.get('group_id').setValue('');
    this.groupList = [];
    this.ReceiptReport.get('customer_id').setValue('');
    this.customerList = [];
    this.ReceiptReport.get('employee_id').setValue('');
    this.employeeList = [];
    const branchId = event.target.value;
    this.loadReceiptDateBounds(branchId, false);
    let cond = {
      "conditions[branch_id]": branchId
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerList = response.data;
          this.enrollmentList = response.data;
        } else {
          this.customerList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  grpChg(event) {
    this.ReceiptReport.get('customer_id').setValue('');
    this.customerList = this.enrollmentList;
    let cond = {
      "conditions[branch_id]": this.ReceiptReport.get('branch_id').value,
      "conditions[group_id]": event.target.value,
    }
    this.userservice.getData(cond, "get-enrolled-customers", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          let enrollList = [];
          for(let i=0; i<(response.data.length); i++) {
            let selectedRecord =  this.customerList.filter((listing: any) => listing.id == response.data[i].customer_id);
            if(selectedRecord.length>0) {
              enrollList.push(selectedRecord[0]);
            }
          }
          this.customerList = enrollList;
        } else {
          this.customerList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  checkReturnClk() { 
    this.chequeSubmitted = false;
    this.clearChequeData();
    this.isChequeReturnClk = true;
    this.isChequeClearClk = false;
    this.checkValidate();
  }

  checkClearClk() {
    this.chequeSubmitted = false;
    this.clearChequeData();
    this.isChequeClearClk = true;
    this.isChequeReturnClk = false;
    this.checkValidate();
  }

  setData(data) {
    this.dataList = data;
    $(function () {
      $('#dtable').DataTable();
    });  
  }

  resetReport() {
    this.submitted = false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    let emptyData = [];
    this.setData(emptyData);
    //this.ReceiptReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.ReceiptReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.ReceiptReport.get('branch_id').setValue('');
    this.ReceiptReport.get('group_id').setValue('');
    this.ReceiptReport.get('customer_id').setValue('');
    this.ReceiptReport.get('employee_id').setValue('');
    this.ReceiptReport.get('payment_type_id').setValue('');
    this.ReceiptReport.get('start_date').setValue(null);
    this.ReceiptReport.get('end_date').setValue(null);
    this.groupList = [];
    this.customerList = [];
    this.employeeList = [];
    this.paymentList = [];
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    } else if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    } else {
      return `with: ${reason}`;
    }
  }

  checkValidate() { 
    const returnAmtCtrl = this.ChequeGroup.get('cheque_return_amount');
    const debitToCtrl = this.ChequeGroup.get('cheque_debit_to');
    if(this.isChequeReturnClk) {
      returnAmtCtrl.setValidators([Validators.required]);
      debitToCtrl.setValidators(null);
    }
    else if(this.isChequeClearClk) {
      returnAmtCtrl.setValidators(null);
      debitToCtrl.setValidators([Validators.required]);
    }
    else {
      returnAmtCtrl.setValidators(null);
      debitToCtrl.setValidators(null);
    }
    returnAmtCtrl.updateValueAndValidity();
    debitToCtrl.updateValueAndValidity();
  }

  clearChequeData() {
    this.ChequeGroup.get('remarks').setValue('');
    this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
    this.ChequeGroup.get('cheque_return_amount').setValue('');
    this.ChequeGroup.get('cheque_debit_to').setValue('');
  }

  onSubmit() {
    this.submitted = true;
    if (this.ReceiptReport.invalid) {
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.receiptReportAutoRetry = false;
    this.runReceiptReport();
  }

  runReceiptReport() {
    this.SubmitbuttonName = "Loading...";
    const result = Object.assign({}, this.ReceiptReport.value);
    result.max_rows = 5000;
    this.userservice.getData(result, "receipt-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        const rows = response && response.data;
        const hasRows = Array.isArray(rows) && rows.length > 0;
        if (hasRows) {
          this.receiptReportAutoRetry = false;
          this.nodataFoundMessage = true;
        if (response.status == 'Success') {
          if (response.too_many_rows) {
            this.hideReport = true;
            this.rowData = [];
            this.dataList = [];
            this.nodataFoundMessage = false;
            this.receiptReportAutoRetry = false;
            this.appService.showAlert('Receipt Report', response.msg, 'error');
            return;
          }
          if (response.row_count) {
            this.receiptDateBoundsHint =
              'Loaded ' + response.row_count + ' receipt(s). Use a shorter date range if the report feels slow.';
          }
          this.viewreceiptInfo = response.data[0];
          this.columnDefs = [
            {headerName: 'Sno', field: 'sno',sort: "asc",resizable: true},
            {headerName: 'Branch Name', field: 'branch_name',resizable: true },
            {headerName: 'Collected Branch', field: 'other_branch_name',resizable: true },
            {headerName: 'Group Name', field: 'group_name',resizable: true},
            {headerName: 'Ticket No', field: 'ticket_no',resizable: true },
            {headerName: 'Customer Name', field: 'customer_name' ,resizable: true},
            {headerName: 'Collection Employee Name', field: 'employee_name',resizable: true },
            {headerName: 'Receipt No', field: 'receipt_no',resizable: true},
            {headerName: 'Alt Receipt No', field: 'alt_rec_no' ,resizable: true},
            {headerName: 'Receipt Date', field: 'receipt_date',resizable: true },
            {headerName: 'Accounts Date', field: 'accounts_date' ,resizable: true},
            {headerName: 'Received Amount', field: 'total_received_amount',resizable: true},
            {headerName: 'Location', field: 'location_name', resizable: true,
              cellRenderer: function(params) {
                if (!params.data) return '';
                const name = params.data.location_name || '';
                if (params.data.maps_link) {
                  return '<a href="' + params.data.maps_link + '" target="_blank" title="Open map">' +
                    (name || 'View Map') + ' <i class="fas fa-map-marker-alt text-success"></i></a>';
                }
                return name || '-';
              }
            },
            {headerName: 'Payment Type', field: 'payment_type_name',resizable: true},
            {headerName: 'Cheque No', field: 'cheque_no' ,resizable: true},
            {headerName: 'Cheque Date', field: 'cheque_date',resizable: true },
            {headerName: 'Cheque Return Amount', field: 'cheque_return_amount' ,resizable: true},
            {headerName: 'Cheque Clear/ Return Date', field: 'cheque_clear_return_date',resizable: true},
            {headerName: 'Bank Name', field: 'bank_name' ,resizable: true},
            {headerName: 'Bank Branch Name', field: 'bank_branch_name',resizable: true },
            {headerName: 'Transaction No', field: 'transaction_no',resizable: true },
            {headerName: 'Transaction Date', field: 'transaction_date',resizable: true},
            {headerName: 'Neft', field: 'neft_adjust_id' ,resizable: true},
            {headerName: 'Adjusted from', field: 'adjusted_from',resizable: true},
            {headerName: 'Penalty Amount', field: 'penalty_amount',resizable: true },
            {headerName: 'Bonus Amount', field: 'bonus_amount',resizable: true },
            {headerName: 'Total Receipt Amount', field: 'total_paid',resizable: true},
            {headerName: 'Cheque Clearance', field: 'cheque_clearance',cellRendererFramework: ReportRedirectModalComponent,
            cellRendererParams: {
              label: '-',
              action: this.openPopup,
            }
          },
            {headerName: 'Cheque Debit To', field: 'cheque_bank_name',resizable: true },
            {headerName: 'Status', field: 'status_name',resizable: true },
            {headerName: 'Actions', width: 100, resizable: true,
              cellRenderer: function(params) {
                if (!params.data || !params.data.receipt_no) { return ''; }
                return gridDeleteAction(getGridReportBaseUrl() + '#/admin-panel/delete-receipt/' + params.data.receipt_no);
              }
            },
            {headerName: 'Print', width: 90, resizable: true,
            cellRenderer: function(params) {
              if (!params.data || !params.data.receipt_no) { return ''; }
              const base = getGridReportBaseUrl();
              if (params.data.receipt_passbook_mode == 'advance') {
                return gridPrintAction(base + '#/admin-panel/advance-receipt-print/' + params.data.receipt_no);
              }
              return gridPrintAction(base + '#/admin-panel/receipt-print/' + params.data.receipt_no);
            }
          },
          ];
          this.defaultColDef = { 
            sortable: true,
            width: 150, 
          };
          this.rowData = response.data;
          this.totalBonusAmt=response.grand_tot_bonus_amount;
          this.totalPenaltyAmt=response.grand_tot_penalty_amount;
          this.totalReceiptAmt=response.grand_tot_receipt_amt;
          this.totalReceivedAmt=response.grand_tot_received_amt;
          this.dataList = response.data;
		  if((response.data.length)>0) {
      this.hideDataList = false; 
      this.nodataFoundMessage=true;
		  }
		  else {
       this.hideDataList = true;  
       this.nodataFoundMessage=true;
		  }
          this.hideReport = false;
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function(params) {
            return "[" + params.value.toLocaleString() + "]";
          };
        } else {
          this.hideReport = true;
          this.nodataFoundMessage=true;
          this.appService.showAlert("Receipt Report", response.msg, "error");
        }
      }
      else {
        this.hideReport = true;
        this.rowData = [];
        this.dataList = [];
        this.nodataFoundMessage = false;
        this.receiptReportAutoRetry = false;
        const msg =
          (response && response.msg) ||
          'No receipts found for the selected filters.';
        this.appService.showAlert('Receipt Report', msg, 'error');
      }
      } catch (err) {
        this.appService.showAlert("Receipt Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      const apiMsg = err && err.error && err.error.msg ? err.error.msg : '';
      const msg = apiMsg
        || (err.status === 401 ? 'Session expired. Please login again.' : 'Sorry!! Something went wrong!');
      this.appService.showAlert("Receipt Report", msg, "error");
    });
  }

  openPopup(record,chequeType) {
     console.log(record.data.receipt_no);
    if(chequeType=='checkReturnClk') {
      record.context.parentComponent.isChequeReturnClk = true;
      record.context.parentComponent.isChequeClearClk = false;
    }
    else if(chequeType=='checkSubmit'){
      console.log("tetet");
      record.context.parentComponent.isChequeReturnClk = false;
      record.context.parentComponent.isChequeClearClk = false;
    }
    else {
      console.log("test");
      record.context.parentComponent.isChequeReturnClk = false;
      record.context.parentComponent.isChequeClearClk = true;
    } 
    //this.ChequeGroup.get('id').setValue(record.data.id);
    //here we make the changes as receord.data.receipt_no instead of id
    record.context.parentComponent.ChequeGroupId = record.data.receipt_no;
    let cond = {
      "conditions[tenant_id]" : record.context.parentComponent.ReceiptReport.get('tenant_id').value,
      "conditions[branch_id]" : record.context.parentComponent.ReceiptReport.get('branch_id').value,
    }
    record.context.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          record.context.parentComponent.bankList = response.data;
        } else {
          record.context.parentComponent.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    /*let content = '<div class="card-body"><form class="bs-example form-horizontal" [formGroup]="ChequeGroup" (ngSubmit)="onChequeSubmit()">'
    content += '<div class="row">';
    content += '<div class="col-6"><div class="form-group row"><label class="col-sm-4">Remarks</label><div class="col-sm-8"><textarea #remarks class="form-control form-control w-100 border-dark" formControlName="remarks"></textarea><div class="col-md-12" *ngIf="chequeSubmitted && cf.remarks.errors" class="invalid-feedback"><label *ngIf="cf.remarks.errors.required">Remark is required</label></div></div></div></div>';
    content += '<div class="col-6"><div class="form-group row"><label class="col-sm-4" *ngIf="isChequeClearClk">Cheque Cleared On <span class="error">*</span></label><label class="col-sm-4" *ngIf="isChequeReturnClk">Cheque Returned On <span class="error">*</span></label><div class="col-sm-8"><input #cheque_clear_return_date type="text" formControlName="cheque_clear_return_date" class="form-control calendar" placeholder="Enter Date" (click)="d3.toggle()" ngbDatepicker #d3="ngbDatepicker" onkeydown="return false"><div class="col-md-12" *ngIf="chequeSubmitted && cf.cheque_clear_return_date.errors" class="invalid-feedback"><label *ngIf="cf.cheque_clear_return_date.errors.required">Date is required</label></div></div></div></div>'
    content += '<div class="col-6" *ngIf="isChequeReturnClk"><div class="form-group row"><label class="col-sm-4">Cheque Return Amount <span class="error">*</span></label><div class="col-sm-8"><input #cheque_return_amount type="text" formControlName="cheque_return_amount" class="form-control" placeholder="Enter Return Amount"><div class="col-md-12" *ngIf="chequeSubmitted && cf.cheque_return_amount.errors" class="invalid-feedback"><label *ngIf="cf.cheque_return_amount.errors.required">Cheque return amount is required</label></div></div></div></div>'
    content += '<div class="col-6" *ngIf="isChequeClearClk"><div class="form-group row"><label class="col-sm-4">Debit To <span class="error">*</span></label><div class="col-sm-8"><select #cheque_debit_to class="form-control form-control w-100 border-dark" formControlName="cheque_debit_to"><option value="">Select Bank</option><option *ngFor="let data of bankList" value="{{data.id}}">{{data.bank_name}}</option></select></div></div></div>'
    content += '</div>';
    content += '<div class="form-group row"><div class="offset-md-2 offset-sm-2 col-sm-10 col-md-7 col-12"><button type="submit" class="btn btn-primary mr-1" [disabled]="disableChequeBtn">{{SubmitChequeButtonName}}</button><button type="button" class="btn btn-danger mr-1" (click)="c(\'Close click\')">Cancel</button></div></div></form></div>';
    */
    //(<HTMLInputElement>document.getElementById("modal-body")).innerHTML = content;
    record.context.ngxSmartModalService.getModal('myModal').open()
  }

  onFilterTextBoxChanged() {
    if (!this.gridApi) {
      return;
    }
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    const params = {
      columnGroups: true,
      allColumns: true,
      fileName: 'receipt_report',
      columnSeparator: ','
    };
    this.gridApi.exportDataAsCsv(params);
  }

  onBtnpdf():void
  {
    this.pdfrectab=false;
    this.hideReport=true;
    this.nodataFoundMessage=true;
    this.selectionfield=true;
  }
  oncancel():void
	  {
		this.pdfrectab=true;
		this.hideReport=false;
		this.selectionfield=false;
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
    var data = document.getElementById('contentToConvert');  
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
      pdf.save('Receiptreport.pdf'); // Generated PDF   
    });
  }

  onPageSizeChanged() {
    var value = (<HTMLInputElement>document.getElementById("page-size")).value;
    this.gridApi.paginationSetPageSize(Number(value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  chequeClearanceClk(event) {
    console.log("event",event)
  }

  onChequeSubmit() {
    console.log("test",this.ChequeGroupId);
    this.SubmitChequeButtonName = "Loading...";
    this.chequeSubmitted = true;
    this.ChequeGroup.get('id').setValue(this.ChequeGroupId);
    const result = Object.assign({}, this.ChequeGroup.value);
    
    if (this.ChequeGroup.invalid) {
      this.SubmitChequeButtonName = "Submit";
      this.disableChequeBtn = false;
      return;
    }
    let submitUrl = '';
    if(this.isChequeReturnClk) {
      submitUrl = 'cheque-return';
    }
    else if(this.isChequeClearClk) {
      submitUrl = 'cheque-cleared';
    }
    else{
      submitUrl = 'cheque-submit';
    }
    this.userservice.getData(result, submitUrl, "JSON").subscribe((response: any) => {
      this.SubmitChequeButtonName = "Submit";
      try {
        console.log("suhail");
        if (response.status == 'Success') {
          let element: HTMLElement = document.getElementById('myDiv') as HTMLElement;
          element.click();
          let chequeId = this.ChequeGroup.get('id').value;
          let selectedrecord =  this.dataList.filter((listing: any) => listing.id == chequeId);
          let returnClearDateObj = this.ChequeGroup.get('cheque_clear_return_date').value;
          let returnClearDate = returnClearDateObj.day + '/' + returnClearDateObj.month + '/' + returnClearDateObj.year;
          if(this.isChequeReturnClk) {
            selectedrecord[0].status = 3;
            selectedrecord[0].cheque_return_amount = this.ChequeGroup.get('cheque_return_amount').value;
            //selectedrecord[0].cheque_clear_return_date = this.ChequeGroup.get('cheque_clear_return_date').value;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
          }
          else if(this.isChequeClearClk) {
            selectedrecord[0].status = 4;
            selectedrecord[0].cheque_clear_return_date = returnClearDate;
            let bankId = this.ChequeGroup.get('cheque_debit_to').value;
            let debitToRecord = this.bankList.filter((listing: any) => listing.id == bankId);
            selectedrecord[0].bank_name = debitToRecord[0].bank_name;
          }
          this.ChequeGroup.get('remarks').setValue('');
          this.ChequeGroup.get('cheque_clear_return_date').setValue(null);
          this.ChequeGroup.get('cheque_return_amount').setValue(0);
          this.ChequeGroup.get('cheque_debit_to').setValue(0);
          this.disableChequeBtn = false;
          this.onSubmit();
        } else { 
          this.appService.showAlert("Receipt Report", response.msg, "error");
        }
      } catch (err) {
        this.disableChequeBtn = false;
        if (err.status == 401) {
         this.appService.showAlert("Receipt Report", 'Sorry!! Something went wrong!', "error");
        }
      }
    }, (err: any) => {
      this.disableChequeBtn = false;
      this.SubmitChequeButtonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Receipt Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Receipt Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.ReceiptReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      customer_id: [''],
      employee_id: [''],
      payment_type_id: [''],
      start_date: [this.currentMonthStart()],
      end_date: [this.currentMonthEnd()],
    });

    this.ChequeGroup = this.formBuilder.group({
      id: [''],
      remarks: ['', Validators.required],
      cheque_clear_return_date: [null, Validators.required],
      cheque_return_amount: [0 ,[Validators.required, Validators.pattern(this.decimalPattern)]],
      cheque_debit_to: ['']
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.ReceiptReport.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
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
      }, (err: any) => { });
    }
  }
}
export interface ViewData 
{
    sno:string;
    branch_name: string;
}


