import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import jspdf from 'jspdf';
import html2canvas from 'html2canvas';

@Component({
  selector: 'app-this-month-unpaid-report',
  templateUrl: './this-month-unpaid-report.component.html',
  styles: []
})
export class ThisMonthUnpaidReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;

  filterForm: FormGroup;
  dataList: any[] = [];
  SubmitbuttonName = 'Filter';
  companyList: any[] = [];
  branchList: any[] = [];
  monthList = [
    { id: 1, name: 'January' }, { id: 2, name: 'February' }, { id: 3, name: 'March' },
    { id: 4, name: 'April' }, { id: 5, name: 'May' }, { id: 6, name: 'June' },
    { id: 7, name: 'July' }, { id: 8, name: 'August' }, { id: 9, name: 'September' },
    { id: 10, name: 'October' }, { id: 11, name: 'November' }, { id: 12, name: 'December' }
  ];
  yearList: number[] = [];
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
  grandCurrentPending: any = 0;
  grandOverallPending: any = 0;
  grandPaid: any = 0;
  grandReceivedMonth: any = 0;

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
    const now = new Date();
    this.filterForm.get('month').setValue(now.getMonth() + 1);
    this.filterForm.get('year').setValue(now.getFullYear());
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
      }
      return;
    }

    const result: any = Object.assign({}, this.filterForm.value);
    result.log_id = localStorage.getItem('logged_employee_id');

    this.userservice.getData(result, 'this-month-unpaid-report', 'POST').subscribe((response: any) => {
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
            { headerName: 'Branch', field: 'branch_name', resizable: true },
            { headerName: 'Customer', field: 'customer_name', resizable: true },
            { headerName: 'SPN Code', field: 'customer_code', resizable: true },
            { headerName: 'Slab / AR No', field: 'slab_name', resizable: true },
            { headerName: 'Due No', field: 'due_no', resizable: true },
            { headerName: 'Due Date', field: 'due_date', resizable: true },
            { headerName: 'Running Month', field: 'running_month', resizable: true },
            { headerName: 'Paid (Overall)', field: 'paid', resizable: true },
            { headerName: 'Current Month Pending', field: 'current_month_pending', resizable: true },
            { headerName: 'Overall Pending', field: 'overall_pending', resizable: true },
            { headerName: 'Received This Month', field: 'payment_received_month', resizable: true },
            { headerName: 'Collection Agent', field: 'collection_agent', resizable: true },
            { headerName: 'Area', field: 'collection_area', resizable: true },
          ];
          this.defaultColDef = { sortable: true, width: 140 };
          applyReportGridData(this, rows);
          this.dataList = this.rowData || rows;
          this.grandCurrentPending = response.grand_current_pending;
          this.grandOverallPending = response.grand_overall_pending;
          this.grandPaid = response.grand_paid;
          this.grandReceivedMonth = response.grand_received_month;
          this.hideDataList = false;
          this.hideReport = false;
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function (params) {
            return '[' + params.value.toLocaleString() + ']';
          };
        } else {
          this.hideReport = true;
          this.nodataFoundMessage = false;
          this.appService.showAlert('This Month Unpaid', response.msg || 'No data', 'error');
        }
      } catch (err) {
        this.appService.showAlert('This Month Unpaid', 'Sorry!! Something went wrong!', 'error');
      }
    }, () => {
      this.SubmitbuttonName = 'Filter';
      this.appService.showAlert('This Month Unpaid', 'Sorry!! Something went wrong!', 'error');
    });
  }

  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    this.gridApi.exportDataAsCsv({
      columnGroups: true,
      allColumns: true,
      fileName: 'this_month_unpaid_report',
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
      pdf.save('ThisMonthUnpaid.pdf');
    });
  }

  onPageSizeChanged() {
    this.gridApi.paginationSetPageSize(Number((<HTMLInputElement>document.getElementById('page-size')).value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  ngOnInit() {
    const now = new Date();
    const y = now.getFullYear();
    for (let i = y; i >= y - 10; i--) {
      this.yearList.push(i);
    }
    this.filterForm = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      month: [now.getMonth() + 1, Validators.required],
      year: [y, Validators.required],
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
