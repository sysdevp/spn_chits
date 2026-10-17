import { Component, OnInit } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup } from '@angular/forms';
import { ExcelService } from '../../../../Services/excel.service';
import XLSX from 'xlsx';

@Component({
  selector: 'app-view-scheme',
  templateUrl: './view-scheme.component.html',
  styles: [`
    .sch { color: #10233f; }
    .sch-hero {
      position: relative; display: flex; justify-content: space-between; gap: 16px; align-items: center;
      min-height: 132px; padding: 18px 22px; margin-bottom: 14px; border-radius: 16px; overflow: hidden;
      background: #fff url('/assets/img/spn-temple-banner.png') right center / cover no-repeat;
      border: 1px solid rgba(7,29,58,.06); box-shadow: 0 8px 22px rgba(7,29,58,.05);
    }
    .sch-hero:before {
      content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 62%;
      background: linear-gradient(90deg, #fff 0%, rgba(255,255,255,.96) 72%, rgba(255,255,255,0) 100%);
    }
    .sch-hero-copy, .sch-actions { position: relative; z-index: 1; }
    .sch-crumb { color: #6d7c90; font-size: 13px; margin-bottom: 8px; }
    .sch-title { display: flex; align-items: center; gap: 12px; }
    .sch-ico {
      width: 46px; height: 46px; border-radius: 12px; background: #18A957; color: #fff;
      display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .sch-title h1 { margin: 0; font-size: 28px; color: #071D3A; font-weight: 800; }
    .sch-title p { margin: 2px 0 0; color: #6d7c90; }
    .sch-actions { display: flex; gap: 8px; align-items: center; }
    .sch-btn {
      border: 0; border-radius: 10px; height: 38px; padding: 0 14px; font-weight: 700; cursor: pointer;
      display: inline-flex; align-items: center; gap: 6px; text-decoration: none;
    }
    .sch-btn.white { background: #fff; color: #10233f; border: 1px solid #d7e2ea; }
    .sch-btn.green { background: #18A957; color: #fff; }
    .sch-filters {
      display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
      background: #f4fbf7; border: 1px solid #e3f3ea; border-radius: 14px; padding: 14px 16px; margin-bottom: 14px;
    }
    .sch-field { flex: 1 1 160px; }
    .sch-field label { display: block; font-size: 12px; color: #5c6b80; font-weight: 700; margin-bottom: 6px; }
    .sch-field input, .sch-field select {
      width: 100%; height: 40px; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0 12px; background: #fff; color: #10233f;
    }
    .sch-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
    .sch-kpi { border-radius: 16px; padding: 14px 16px; display: flex; gap: 12px; align-items: center; border: 1px solid rgba(7,29,58,.05); }
    .sch-kpi.green { background: #E9FFF3; } .sch-kpi.blue { background: #EDF6FF; }
    .sch-kpi.gold { background: #FFF8E8; } .sch-kpi.violet { background: #F3EDFF; }
    .sch-kpi i {
      width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px;
    }
    .sch-kpi.green i { background: #18A957; } .sch-kpi.blue i { background: #1687E8; }
    .sch-kpi.gold i { background: #F2A900; } .sch-kpi.violet i { background: #7A3FF2; }
    .sch-kpi b { display: block; font-size: 22px; color: #071D3A; line-height: 1.1; }
    .sch-kpi span { display: block; color: #5c6b80; font-size: 13px; font-weight: 700; }
    .sch-kpi small { color: #6d7c90; }
    .sch-table-card { background: #fff; border-radius: 16px; border: 1px solid rgba(7,29,58,.06); box-shadow: 0 8px 22px rgba(7,29,58,.05); overflow: hidden; }
    .sch-table-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; }
    .sch-table-head h3 { margin: 0; font-size: 16px; color: #071D3A; }
    .sch-table-head label { color: #6d7c90; font-size: 13px; margin: 0 8px 0 0; }
    .sch-table { width: 100%; border-collapse: collapse; }
    .sch-table th { text-align: left; font-size: 13px; color: #5c6b80; padding: 12px 14px; border-bottom: 1px solid #e8eef3; background: #fbfcfe; }
    .sch-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; color: #10233f; font-size: 14px; }
    .sch-table tr:hover td { background: #f7fbf9; }
    .pill { background: #E9FFF3; color: #0d7a45; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 700; }
    .pill.off { background: #FFF0F4; color: #c2185b; }
    .sch-actions-cell a { cursor: pointer; margin-right: 10px; font-size: 15px; }
    .sch-foot { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; color: #6d7c90; font-size: 13px; }
    .pager { display: flex; gap: 6px; }
    .pager button {
      min-width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #10233f; cursor: pointer;
    }
    .pager button.on { background: #18A957; color: #fff; border-color: #18A957; }
    .sch-empty { padding: 28px; text-align: center; color: #6d7c90; }
    @media (max-width: 900px) {
      .sch-kpis { grid-template-columns: 1fr 1fr; }
      .sch-hero { background-position: right top; }
    }
  `]
})
export class ViewSchemeComponent implements OnInit {
  dataList: any[] = [];
  filtered: any[] = [];
  pageRows: any[] = [];
  TableHidden = true;
  ViewSchemeInfo: any = {};
  branchInfo: any = [];
  ViewScheme: FormGroup;
  searchText = '';
  valueRange = '';
  formatFilter = '';
  statusFilter = '';
  formats: string[] = [];
  page = 1;
  pageSize = 10;
  totalSchemes = 0;
  totalChitValue = 0;
  avgRegister = 0;
  avgDue = 0;

  constructor(
    private formBuilder: FormBuilder,
    private modalService: NgbModal,
    private appService: AppService,
    private userservice: UserService,
    private excel: ExcelService
  ) {}

  ngOnInit() {
    const tenant_id = localStorage.getItem('tenant_id');
    this.ViewScheme = this.formBuilder.group({ tenant_id: [''] });
    this.ViewScheme.get('tenant_id').setValue(tenant_id);
    this.loadSchemes();
  }

  loadSchemes() {
    this.TableHidden = true;
    this.userservice.getData(Object.assign({}, this.ViewScheme.value), 'list-schemes', 'JSON').subscribe((response: any) => {
      if (response.status === 'Success') {
        this.dataList = response.data || [];
        this.formats = this.uniqueFormats(this.dataList);
        this.applyFilters();
      }
      this.TableHidden = false;
    }, () => {
      this.TableHidden = false;
    });
  }

  applyFilters() {
    const q = (this.searchText || '').toLowerCase().trim();
    this.filtered = (this.dataList || []).filter((row) => {
      const format = this.schemeFormat(row).toLowerCase();
      const value = Number(row.chit_value) || 0;
      const matchesText = !q || format.indexOf(q) >= 0 || String(row.chit_value).indexOf(q) >= 0
        || String(row.register_fees || '').indexOf(q) >= 0 || String(row.monthly_due || '').indexOf(q) >= 0;
      const matchesFormat = !this.formatFilter || this.schemeFormat(row) === this.formatFilter;
      const matchesStatus = !this.statusFilter || this.statusLabel(row) === this.statusFilter;
      const matchesRange = !this.valueRange || this.inRange(value, this.valueRange);
      return matchesText && matchesFormat && matchesStatus && matchesRange;
    });
    this.page = 1;
    this.refreshSummary();
    this.refreshPage();
  }

  resetFilters() {
    this.searchText = '';
    this.valueRange = '';
    this.formatFilter = '';
    this.statusFilter = '';
    this.pageSize = 10;
    this.applyFilters();
  }

  refreshSummary() {
    const rows = this.filtered || [];
    const count = rows.length;
    let chit = 0;
    let reg = 0;
    let due = 0;
    rows.forEach((row) => {
      chit += Number(row.chit_value) || 0;
      reg += Number(row.register_fees) || 0;
      due += Number(row.monthly_due) || 0;
    });
    this.totalSchemes = count;
    this.totalChitValue = chit;
    this.avgRegister = count ? Math.round(reg / count) : 0;
    this.avgDue = count ? Math.round(due / count) : 0;
  }

  refreshPage() {
    const start = (this.page - 1) * this.pageSize;
    this.pageRows = this.filtered.slice(start, start + this.pageSize);
  }

  setPageSize(event) {
    this.pageSize = Number(event.target.value) || 10;
    this.page = 1;
    this.refreshPage();
  }

  pageCount(): number {
    return Math.max(1, Math.ceil((this.filtered.length || 0) / this.pageSize));
  }

  pages(): number[] {
    const list = [];
    const total = this.pageCount();
    for (let i = 1; i <= total && i <= 8; i++) {
      list.push(i);
    }
    return list;
  }

  goPage(page: number) {
    if (page < 1 || page > this.pageCount()) {
      return;
    }
    this.page = page;
    this.refreshPage();
  }

  showingFrom(): number {
    if (!this.filtered.length) {
      return 0;
    }
    return (this.page - 1) * this.pageSize + 1;
  }

  showingTo(): number {
    return Math.min(this.page * this.pageSize, this.filtered.length);
  }

  schemeFormat(row: any): string {
    if (!row) {
      return '';
    }
    const letter = row.chit_letter || '';
    return (row.chit_value || 0) + ' (' + (row.no_of_months || 0) + ' M) / ' + letter;
  }

  statusLabel(row: any): string {
    return Number(row && row.status) === 0 ? 'Inactive' : 'Active';
  }

  formatDate(value: string): string {
    if (!value) {
      return '-';
    }
    const date = new Date(value);
    if (isNaN(date.getTime())) {
      return '-';
    }
    const dd = ('0' + date.getDate()).slice(-2);
    const mm = ('0' + (date.getMonth() + 1)).slice(-2);
    return dd + '-' + mm + '-' + date.getFullYear();
  }

  money(value: any, decimals: number): string {
    const num = Number(value) || 0;
    return '₹ ' + num.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
  }

  exportSchemes() {
    const rows = (this.filtered || []).map((row, index) => {
      return {
        'S.No': index + 1,
        'Chit Value': row.chit_value,
        'Scheme Format': this.schemeFormat(row),
        'Registration Fee': row.register_fees,
        'Due Amount': row.monthly_due,
        'Status': this.statusLabel(row),
        'Created Date': this.formatDate(row.created_at)
      };
    });
    this.excel.exportAsExcelFile(rows, 'Schemes');
  }

  onImportFile(event) {
    const file = event.target.files && event.target.files[0];
    event.target.value = '';
    if (!file) {
      return;
    }
    const reader = new FileReader();
    reader.onload = (e: any) => {
      const book = XLSX.read(e.target.result, { type: 'array' });
      const sheet = book.Sheets[book.SheetNames[0]];
      const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });
      this.importRows(rows);
    };
    reader.readAsArrayBuffer(file);
  }

  importRows(rows: any[]) {
    const tenantId = localStorage.getItem('tenant_id');
    const createdBy = localStorage.getItem('logged_user_id');
    let pending = rows.length;
    let saved = 0;
    if (!pending) {
      this.appService.showAlert('Scheme', 'The file has no rows.', 'error');
      return;
    }
    rows.forEach((row) => {
      const payload = {
        tenant_id: tenantId,
        chit_value: row['Chit Value'] || row.chit_value || '',
        no_of_months: row['No of Auctions'] || row.no_of_months || '',
        members: row['No of Members'] || row.members || '',
        register_fees: row['Registration Fee'] || row.register_fees || 0,
        enrollment_fees: row['Enrollment Fee'] || row.enrollment_fees || 0,
        chit_letter: row['Chit Letter'] || row.chit_letter || '',
        short_term: row['Short Term'] || row.short_term || '',
        monthly_due: row['Due Amount'] || row.monthly_due || '',
        created_by: createdBy,
        status: 1
      };
      this.userservice.getData(payload, 'store-scheme', 'JSON').subscribe((response: any) => {
        if (response.status === 'Success') {
          saved++;
        }
        pending--;
        if (pending === 0) {
          this.appService.showAlert('Scheme', saved + ' scheme(s) imported.', saved ? 'success' : 'error');
          this.loadSchemes();
        }
      }, () => {
        pending--;
        if (pending === 0) {
          this.appService.showAlert('Scheme', saved + ' scheme(s) imported.', saved ? 'success' : 'error');
          this.loadSchemes();
        }
      });
    });
  }

  open(content, options = {}, recordId = 0) {
    this.userservice.getData({ 'conditions[id]': recordId }, 'get-schemes', 'POST').subscribe((response: any) => {
      if (response.status === 'Success') {
        this.ViewSchemeInfo = response.data[0];
        this.branchInfo = response.data[0].branch_info;
      }
    }, () => {
      this.appService.showAlert('Scheme', 'Sorry!! Something went wrong!', 'error');
    });
    this.modalService.open(content, options).result.then(() => {}, () => {});
  }

  private uniqueFormats(rows: any[]): string[] {
    const seen = {};
    const list = [];
    (rows || []).forEach((row) => {
      const format = this.schemeFormat(row);
      if (format && !seen[format]) {
        seen[format] = true;
        list.push(format);
      }
    });
    return list;
  }

  private inRange(value: number, range: string): boolean {
    if (range === 'below-1l') {
      return value < 100000;
    }
    if (range === '1l-5l') {
      return value >= 100000 && value <= 500000;
    }
    if (range === '5l-10l') {
      return value > 500000 && value <= 1000000;
    }
    if (range === 'above-10l') {
      return value > 1000000;
    }
    return true;
  }

  private getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC) {
      return 'by pressing ESC';
    }
    if (reason === ModalDismissReasons.BACKDROP_CLICK) {
      return 'by clicking on a backdrop';
    }
    return 'with: ' + reason;
  }
}
