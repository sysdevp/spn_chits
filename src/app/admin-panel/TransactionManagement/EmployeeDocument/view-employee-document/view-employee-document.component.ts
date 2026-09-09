import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  selector: 'app-view-employee-document',
  templateUrl: './view-employee-document.component.html',
  styles: []
})
export class ViewEmployeeDocumentComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;

  viewLoanCustomerDocument: FormGroup;
  dataList: any[] = [];
  TableHidden = true;
  ViewDocumentInfo: any = {};
  BranchInfo: any = {};
  CustomerInfo: any = {};
  GroupInfo: any = '';
  CollectedEmpInfo: any = {};
  DocumentInfo: any[] = [];
  AssetInfo: any[] = [];
  hideDocuments = true;
  hideAssets = true;
  branchList: any[] = [];
  employeeList: any[] = [];
  isSuperAdmin = false;
  companyList: any[] = [];
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Filter';
  allItems: any[] = [];
  pager: any = {};
  pagedItems: any[] = [];
  dtableInfo = 'Showing 0 to 0 of 0 entries';
  pageSize: any = 10;
  sortedBy: any;
  sortType = 'asc';

  constructor(
    private pagerService: PagerService,
    private formBuilder: FormBuilder,
    private modalService: NgbModal,
    private appService: AppService,
    private userservice: UserService
  ) {}

  get f() {
    return this.viewLoanCustomerDocument.controls;
  }

  docTypeLabel(type: any): string {
    const t = String(type == null ? '' : type).trim();
    if (t === '1' || /^pan$/i.test(t)) return 'PAN Card';
    if (t === '2' || /^aadhaar?$/i.test(t) || /^adhar$/i.test(t)) return 'Aadhaar Card';
    if (t === '3' || /^ration/i.test(t)) return 'Ration Card';
    if (/^application/i.test(t)) return 'Application Form';
    return t || 'Document';
  }

  companyChg(event) {
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1,
      log_id: localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      this.branchList = response && response.status === 'Success' ? response.data : [];
    });
  }

  branchChg(event) {
    const branch_id = event.target.value;
    this.userservice.getData({ 'conditions[branch_id]': branch_id }, 'get-employees', 'POST').subscribe((response: any) => {
      this.employeeList = response && response.status === 'Success' ? response.data : [];
    });
  }

  resetFilter() {
    this.submitted = false;
    const tenant_id = localStorage.getItem('tenant_id');
    if (!tenant_id) {
      this.viewLoanCustomerDocument.get('tenant_id').setValue('');
    }
    this.viewLoanCustomerDocument.get('branch_id').setValue('');
    this.viewLoanCustomerDocument.get('collected_employee_id').setValue('');
    this.allItems = [];
    this.dataList = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
  }

  setPagerString() {
    if (this.allItems.length > parseInt(this.pageSize, 10)) {
      this.dtableInfo = 'Showing 1 to ' + this.pageSize + ' of ' + this.allItems.length + ' entries';
    } else if (this.allItems.length > 0) {
      this.dtableInfo = 'Showing 1 to ' + this.allItems.length + ' of ' + this.allItems.length + ' entries';
    } else {
      this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    }
  }

  setPage(page: number) {
    this.pager = this.pagerService.getPager(this.allItems.length, page, this.pageSize);
    this.pagedItems = this.allItems.slice(this.pager.startIndex, this.pager.endIndex + 1);
  }

  pageSizeChg(event) {
    this.pageSize = event.target.value;
    this.setPage(this.pager.currentPage || 1);
  }

  filterChg(filterText) {
    if (filterText) {
      const q = filterText.toLowerCase();
      this.allItems = this.dataList.filter((item) => {
        const fields = [
          item.branch_name,
          item.customer_name,
          item.name,
          item.collected_employee_name,
          item.source
        ];
        return fields.some((v) => String(v || '').toLowerCase().indexOf(q) !== -1);
      });
    } else {
      this.allItems = this.dataList;
    }
    this.setPage(1);
    this.setPagerString();
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    if (this.viewLoanCustomerDocument.invalid) {
      this.SubmitbuttonName = 'Filter';
      if (this.f.tenant_id.errors && this.focus1) this.focus1.nativeElement.focus();
      else if (this.f.branch_id.errors && this.focus2) this.focus2.nativeElement.focus();
      return;
    }

    const cond: any = {
      'conditions[tenant_id]': this.viewLoanCustomerDocument.get('tenant_id').value,
      'conditions[branch_id]': this.viewLoanCustomerDocument.get('branch_id').value
    };
    const empId = this.viewLoanCustomerDocument.get('collected_employee_id').value;
    if (empId) {
      cond['conditions[collected_employee_id]'] = empId;
    }

    this.userservice.getData(cond, 'get-documents', 'POST').subscribe((response: any) => {
      this.SubmitbuttonName = 'Filter';
      if (response && response.status === 'Success') {
        this.dataList = response.data || [];
        this.allItems = this.dataList;
        this.setPage(1);
        this.setPagerString();
      } else {
        this.dataList = [];
        this.allItems = [];
        this.setPage(1);
        this.setPagerString();
        this.appService.showAlert('Employee Documents', (response && response.msg) || 'No documents found', 'error');
      }
    }, () => {
      this.SubmitbuttonName = 'Filter';
      this.appService.showAlert('Employee Documents', 'Sorry!! Something went wrong!', 'error');
    });
  }

  ngOnInit() {
    this.viewLoanCustomerDocument = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      collected_employee_id: ['']
    });
    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.viewLoanCustomerDocument.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status === 'Success' ? response.data : [];
      });
    }
    this.TableHidden = false;
  }

  open(content, options = {}, recordId = 0) {
    this.hideDocuments = true;
    this.hideAssets = true;
    const cond = { 'conditions[id]': recordId };
    this.userservice.getData(cond, 'get-documents', 'POST').subscribe((response: any) => {
      if (response && response.status === 'Success' && response.data && response.data[0]) {
        const row = response.data[0];
        this.ViewDocumentInfo = row;
        this.BranchInfo = row.branch_info || {};
        this.CustomerInfo = row.customer_info || {};
        this.GroupInfo = row.group_name || '';
        this.CollectedEmpInfo = row.collected_emp_info || {};
        this.DocumentInfo = row.document_info || [];
        this.AssetInfo = row.asset_info || [];
        this.hideDocuments = !(this.DocumentInfo && this.DocumentInfo.length);
        this.hideAssets = !(this.AssetInfo && this.AssetInfo.length);
      } else {
        this.appService.showAlert('Employee Documents', 'Sorry!! Something went wrong!', 'error');
      }
    }, () => {
      this.appService.showAlert('Employee Documents', 'Sorry!! Something went wrong!', 'error');
    });
    this.modalService.open(content, options).result.then(() => {}, () => {});
  }

  deleteDocument(row) {
    if (!row || !row.id) {
      return;
    }
    if (!confirm('Delete this employee document pack?')) {
      return;
    }
    const payload = {
      id: row.id,
      deleted_by: localStorage.getItem('logged_user_id'),
      deletion_remark: 'Deleted from employee documents'
    };
    this.userservice.getData(payload, 'delete-document', 'JSON').subscribe((response: any) => {
      if (response && response.status === 'Success') {
        this.appService.showAlert('Employee Documents', response.msg || 'Deleted', 'success');
        this.onSubmit();
      } else {
        this.appService.showAlert('Employee Documents', (response && response.msg) || 'Delete failed', 'error');
      }
    }, () => {
      this.appService.showAlert('Employee Documents', 'Sorry!! Something went wrong!', 'error');
    });
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) return 'by pressing ESC';
    if (reason === ModalDismissReasons.BACKDROP_CLICK) return 'by clicking on a backdrop';
    return `with: ${reason}`;
  }
}
