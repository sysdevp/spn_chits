import { Component, OnInit, ElementRef, ViewChild, TemplateRef } from '@angular/core';
import { applyReportGridData } from '../../../../utils/report-grid.helper';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, NgbModalRef } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { GridOptions } from 'ag-grid/main';
import { SettlementApproveCellComponent } from '../settlement-approve-cell.component';
import jspdf from 'jspdf';
import html2canvas from 'html2canvas';

@Component({
  selector: 'app-settlement-report',
  templateUrl: './settlement-report.component.html',
  styles: []
})
export class SettlementReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('approveModal') approveModalTpl: TemplateRef<any>;

  SettlementReport: FormGroup;
  ApproveForm: FormGroup;
  dataList: any;
  TableHidden: boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin: boolean;
  submitted: boolean;
  approveSubmitted: boolean;
  disableBtn: boolean;
  disableApproveBtn: boolean;
  employeeList: any[];

  paginationPageSize;
  paginationNumberFormatter;
  gridApi;
  defaultColDef;
  columnDefs: any[];
  rowData: any[];
  gridOptions: any;
  hideReport: boolean = true;
  showReportGrid = true;
  hideDataList: boolean = true;
  nodataFoundMessage: boolean = true;
  totalAmt: any;
  tenantid: any;
  branchid: any;
  selectionfield: boolean = false;
  pdfrectab: boolean = true;
  viewadvancedreceiptInfo: ViewData[] = [];
  activeApproveModal: NgbModalRef;
  selectedSettlement: any;

  constructor(
    private modalService: NgbModal,
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService
  ) {
    this.SubmitbuttonName = 'Filter';
    this.gridOptions = <GridOptions>{
      context: {
        userservice: this.userservice,
        appService: this.appService,
        parentComponent: this
      },
      components: {}
    };
  }

  get f() {
    return this.SettlementReport.controls;
  }

  get af() {
    return this.ApproveForm.controls;
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

  branchChg(event) {
    this.tenantid = this.SettlementReport.get('tenant_id').value;
    this.branchid = event.target.value;

    if (this.tenantid !== '' && this.branchid > 0) {
      const cond = {
        'conditions[tenant_id]': this.tenantid,
        'conditions[branch_id]': this.branchid
      };
      this.userservice.getData(cond, 'get-employees', 'POST').subscribe((response: any) => {
        try {
          this.employeeList = response.status === 'Success' ? response.data : [];
        } catch (err) {}
      }, () => {});
    } else if (this.tenantid !== '' && +this.branchid === 0) {
      this.userservice.getData({ tenant_id: this.tenantid }, 'list-employees', 'POST').subscribe((response: any) => {
        try {
          this.employeeList = response.status === 'Success' ? response.data : [];
        } catch (err) {}
      }, () => {});
    } else {
      this.employeeList = [];
    }
  }

  setData(data) {
    this.dataList = data;
    $(function () {
      $('#dtable').DataTable();
    });
  }

  resetReport() {
    this.submitted = false;
    this.hideReport = true;
    this.nodataFoundMessage = true;
    this.setData([]);
    const tenant_id = localStorage.getItem('tenant_id');
    if (!tenant_id) {
      this.SettlementReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.SettlementReport.get('branch_id').setValue('');
    this.SettlementReport.get('employee_id').setValue('');
    this.SettlementReport.get('approval_status').setValue('');
    this.SettlementReport.get('start_date').setValue(null);
    this.SettlementReport.get('end_date').setValue(null);
    this.employeeList = [];
  }

  openApproveModal(record) {
    this.selectedSettlement = record && record.data ? record.data : null;
    if (!this.selectedSettlement || Number(this.selectedSettlement.is_approved) === 1) {
      return;
    }
    this.approveSubmitted = false;
    this.disableApproveBtn = false;
    this.ApproveForm.reset({
      id: this.selectedSettlement.id,
      approval_remarks: ''
    });
    this.activeApproveModal = this.modalService.open(this.approveModalTpl, { centered: true });
  }

  closeApproveModal() {
    if (this.activeApproveModal) {
      this.activeApproveModal.close();
      this.activeApproveModal = null;
    }
  }

  onApproveSubmit() {
    this.approveSubmitted = true;
    if (this.ApproveForm.invalid) {
      return;
    }
    this.disableApproveBtn = true;
    const payload: any = Object.assign({}, this.ApproveForm.value);
    payload.log_id = localStorage.getItem('logged_employee_id');
    payload.approved_by = localStorage.getItem('logged_employee_id');

    this.userservice.getData(payload, 'approve-cash-settlement', 'POST').subscribe((response: any) => {
      this.disableApproveBtn = false;
      try {
        if (response.status === 'Success') {
          this.appService.showAlert('Settlement Report', response.msg || 'Approved successfully', 'success');
          this.closeApproveModal();
          this.onSubmit();
        } else {
          this.appService.showAlert('Settlement Report', response.msg || 'Approval failed', 'error');
        }
      } catch (err) {
        this.appService.showAlert('Settlement Report', 'Sorry!! Something went wrong!', 'error');
      }
    }, () => {
      this.disableApproveBtn = false;
      this.appService.showAlert('Settlement Report', 'Sorry!! Something went wrong!', 'error');
    });
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    const result = Object.assign({}, this.SettlementReport.value);
    if (this.SettlementReport.invalid) {
      this.SubmitbuttonName = 'Filter';
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      } else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, 'cash-settlement-report', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Filter';
      try {
        if (response.status == 'Success') {
          this.viewadvancedreceiptInfo = response.data[0];
          this.columnDefs = [
            { headerName: 'Sno', field: 'sno', sort: 'asc', resizable: true },
            { headerName: 'Branch Name', field: 'branch_name', resizable: true },
            { headerName: 'Employee Name', field: 'employee_name', resizable: true },
            { headerName: 'Settlement Date', field: 'settlement_date', resizable: true },
            { headerName: 'Received Amount', field: 'received_amount', resizable: true },
            { headerName: 'Denomination', field: 'denomination', resizable: true, width: 280 },
            { headerName: 'Collection Employee', field: 'collection_employee_name', resizable: true },
            { headerName: 'Remarks', field: 'remarks', resizable: true },
            { headerName: 'Status', field: 'approval_status_name', resizable: true },
            { headerName: 'Approval Remarks', field: 'approval_remarks', resizable: true },
            {
              headerName: 'Action',
              field: 'action',
              width: 120,
              resizable: true,
              cellRendererFramework: SettlementApproveCellComponent,
              cellRendererParams: {
                action: this.openApproveModal.bind(this)
              }
            }
          ];
          this.defaultColDef = {
            sortable: true,
            width: 150
          };
          applyReportGridData(this, response.data);
          this.totalAmt = response.grand_pending_amt;
          this.dataList = response.data;
          if ((response.data.length) > 0) {
            this.hideDataList = false;
            this.nodataFoundMessage = true;
            this.hideReport = false;
          } else {
            this.hideDataList = true;
            this.nodataFoundMessage = false;
            this.hideReport = true;
          }
          this.paginationPageSize = 10;
          this.paginationNumberFormatter = function (params) {
            return '[' + params.value.toLocaleString() + ']';
          };
        } else {
          this.appService.showAlert('Settlement Report', response.msg, 'error');
        }
      } catch (err) {
        this.appService.showAlert('Settlement Report', 'Sorry!! Something went wrong!', 'error');
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Filter';
      this.appService.showAlert('Settlement Report', 'Sorry!! Something went wrong!', 'error');
    });
  }

  onFilterTextBoxChanged() {
    this.gridApi.setQuickFilter((<HTMLInputElement>document.getElementById('filter-text-box')).value);
  }

  onBtnExport(): void {
    this.gridApi.exportDataAsCsv({
      columnGroups: true,
      allColumns: true,
      fileName: 'settlement_report',
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
    const data = document.getElementById('contentToConvert');
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
      pdf.save('Settlementreport.pdf');
    });
  }

  onPageSizeChanged() {
    this.gridApi.paginationSetPageSize(Number((<HTMLInputElement>document.getElementById('page-size')).value));
  }

  onGridReady(params) {
    this.gridApi = params.api;
  }

  ngOnInit() {
    this.SettlementReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: [''],
      approval_status: [''],
      start_date: [null],
      end_date: [null]
    });

    this.ApproveForm = this.formBuilder.group({
      id: ['', Validators.required],
      approval_remarks: ['', Validators.required]
    });

    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.SettlementReport.get('tenant_id').setValue(tenant_id);
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

export interface ViewData {
  sno: string;
  branch_name: string;
}
