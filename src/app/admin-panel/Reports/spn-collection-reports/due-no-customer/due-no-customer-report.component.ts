import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import jspdf from 'jspdf';
import html2canvas from 'html2canvas';

@Component({
  selector: 'app-due-no-customer-report',
  templateUrl: './due-no-customer-report.component.html',
  styles: []
})
export class DueNoCustomerReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('due_no') focus3: ElementRef;

  filterForm: FormGroup;
  dataList: any[] = [];
  SubmitbuttonName = 'Filter';
  companyList: any[] = [];
  branchList: any[] = [];
  isSuperAdmin: boolean;
  submitted: boolean;
  disableBtn: boolean;
  paginationPageSize;
  paginationNumberFormatter;
  gridApi;
  defaultColDef;
  columnDefs: any[];
  rowData: any[];
  hideReport = true;
  showReportGrid = true;
  hideDataList = true;
  nodataFoundMessage = true;
  selectionfield = false;
  pdfrectab = true;
  grandDue: any = 0;
  grandPaidDue: any = 0;
  grandPendingDue: any = 0;
  grandOverallPending: any = 0;

  constructor(
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService
  ) {}

  get f() {
    return this.filterForm.controls;
  }

  companyChg(event) {
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      log_id: localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      try {
        this.branchList = response.status === 'Success' ? response.data : [];
      } catch (err) {}
    }, () => {});
  }

  resetReport() {
    this.submitted = false;
    this.hideReport = true;
    this.nodataFoundMessage = true;
    this.dataList = [];
    this.rowData = [];
    const tenant_id = localStorage.getItem('tenant_id');
    if (!tenant_id) {
      this.filterForm.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.filterForm.get('branch_id').setValue('');
    this.filterForm.get('due_no').setValue('');
    this.filterForm.get('unpaid_only').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    if (this.filterForm.invalid) {
      this.SubmitbuttonName = 'Filter';
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      } else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      } else if (this.f.due_no.errors && this.focus3) {
        this.focus3.nativeElement.focus();
      }
      return;
    }

    const result: any = Object.assign({}, this.filterForm.value);
    result.log_id = localStorage.getItem('logged_employee_id');
    result.unpaid_only = result.unpaid_only ? 1 : 0;

    this.userservice.getData(result, 'due-no-customer-report', 'POST').subscribe((response: any) => {
      this.SubmitbuttonName = 'Filter';
      try {
        if (response.status === 'Success') {
          const rows = Array.isArray(response.data) ? response.data : [];
          if (rows.length === 0) {
            this.hideReport = true;
            this.nodataFoundMessage = false;
            return;
          }
          this.nodataFoundMessage = true;
          this.columnDefs = [
            { headerName: 'Sno', field: 'sno', sort: 'asc', resizable: true },
            { headerName: 'Customer', field: 'customer_name', resizable: true },
            { headerName: 'SPN No', field: 'customer_code', resizable: true },
            { headerName: 'AR No', field: 'ar_no', resizable: true },
            { headerName: 'Slab / AR No', field: 'slab_name', resizable: true },
            { headerName: 'Collection Agent', field: 'collection_agent', resizable: true },
            { headerName: 'Area', field: 'collection_area', resizable: true },
            { headerName: 'Running Month', field: 'running_month', resizable: true },
            { headerName: 'Paid (Overall)', field: 'paid', resizable: true },
            { headerName: 'This Due Pending', field: 'current_due_pending', resizable: true },
            { headerName: 'Overall Pending', field: 'overall_pending', resizable: true },
            { headerName: 'Payment Received', field: 'payment_received', resizable: true },
            { headerName: 'Paid on Due', field: 'paid_on_due', resizable: true },
            { headerName: 'Due Amount', field: 'due_amount', resizable: true },
            { headerName: 'Due Date', field: 'due_date', resizable: true },
            { headerName: 'Due No', field: 'due_no', resizable: true },
          ];
          this.defaultColDef = { sortable: true, width: 140 };
          applyReportGridData(this, rows);
          this.dataList = this.rowData || rows;
          this.grandDue = response.grand_due;
          this.grandPaidDue = response.grand_paid_due;
          this.grandPendingDue = response.grand_pending_due;
          this.grandOverallPending = response.grand_overall_pending;
          this.hideDataList = false;
          this.hideReport = false;
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function (params) {
            return '[' + params.value.toLocaleString() + ']';
          };
        } else {
          this.hideReport = true;
          this.nodataFoundMessage = false;
          this.appService.showAlert('Due No Customer List', response.msg || 'No data', 'error');
        }
      } catch (err) {
        this.appService.showAlert('Due No Customer List', 'Sorry!! Something went wrong!', 'error');
      }
    }, () => {
      this.SubmitbuttonName = 'Filter';
      this.appService.showAlert('Due No Customer List', 'Sorry!! Something went wrong!', 'error');
    });
  }

  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    this.gridApi.exportDataAsCsv({
      columnGroups: true,
      allColumns: true,
      fileName: 'due_no_customer_report',
      columnSeparator: ','
    });
  }

  onBtnpdf(): void {
    this.pdfrectab = false;
    this.hideReport = true;
    this.nodataFoundMessage = true;
    this.selectionfield = true;
  }

  oncancel(): void {
    this.pdfrectab = true;
    this.hideReport = false;
    this.selectionfield = false;
  }

  onpdf(): void {
    const data = document.getElementById('mygrid');
    html2canvas(data).then(canvas => {
      const imgWidth = 210;
      const pageHeight = 295;
      let imgHeight = canvas.height * imgWidth / canvas.width;
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
      pdf.save('DueNoCustomerList.pdf');
    });
  }

  onPageSizeChanged() {
    this.gridApi.paginationSetPageSize(Number((<HTMLInputElement>document.getElementById('page-size')).value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  ngOnInit() {
    this.filterForm = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      due_no: ['', [Validators.required, Validators.min(1)]],
      unpaid_only: [''],
    });
    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.filterForm.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        try {
          this.companyList = response.status === 'Success' ? response.data : [];
        } catch (err) {}
      }, () => {});
    }
  }
}
