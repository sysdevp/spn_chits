import { Component, Input } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { map, catchError } from 'rxjs/operators';
import { environment } from '../../../../../environments/environment';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

export interface KycFile {
  document_type: string;
  document_path: string;
  document_no: string;
  file_name: string;
}

@Component({
  selector: 'app-kyc-upload',
  templateUrl: './kyc-upload.component.html',
  styles: [`
    .kyc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
    .kyc-slot { border: 1.5px dashed #9fd8b9; border-radius: 12px; padding: 12px; background: #f6fdf9; }
    .kyc-slot.done { border-style: solid; border-color: #18A957; background: #E9FFF3; }
    .kyc-slot label.t { display: block; font-weight: 700; color: #004D2A; margin-bottom: 6px; }
    .kyc-slot input[type=file] { width: 100%; font-size: 12px; }
    .kyc-file { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-top: 6px; color: #004D2A; }
    .kyc-file button { border: 0; background: none; color: #DC2626; font-weight: 700; cursor: pointer; }
    .kyc-hint { font-size: 12px; color: #5c6b80; margin-top: 8px; }
  `]
})
export class KycUploadComponent {
  @Input() customerId: any = null;
  @Input() branchId: any = null;
  @Input() aadharNo = '';
  @Input() panNo = '';
  @Input() showSaveButton = false;

  readonly slots = [
    { key: 'Aadhaar Front', multiple: false },
    { key: 'Aadhaar Back', multiple: false },
    { key: 'PAN Card', multiple: false },
    { key: 'Other Documents', multiple: true },
  ];
  files: KycFile[] = [];
  uploading: { [key: string]: boolean } = {};
  saving = false;

  constructor(private http: HttpClient, private userservice: UserService, private appService: AppService) {}

  filesFor(type: string) {
    return this.files.filter((f) => f.document_type === type);
  }

  isUploading() {
    return Object.keys(this.uploading).some((k) => this.uploading[k]);
  }

  pick(event: any, slot: { key: string; multiple: boolean }) {
    const list: FileList = event.target.files;
    if (!list || !list.length) {
      return;
    }
    if (!slot.multiple) {
      this.files = this.files.filter((f) => f.document_type !== slot.key);
    }
    Array.from(list).forEach((file) => this.upload(file, slot.key));
    event.target.value = '';
  }

  private upload(file: File, type: string) {
    if (file.size > 5 * 1024 * 1024) {
      this.appService.showAlert('KYC Upload', file.name + ' is larger than 5 MB', 'error');
      return;
    }
    const token = localStorage.getItem('token');
    let headers = new HttpHeaders();
    if (token) {
      headers = headers.set('Authorization', 'Bearer ' + token);
    }
    const fd = new FormData();
    fd.append('document', file, file.name);
    fd.append('upload_type', 'document');
    this.uploading[type] = true;
    this.http.post(environment.API + 'asset-document-upload', fd, { headers }).subscribe((res: any) => {
      this.uploading[type] = false;
      if (res && res.file_name) {
        let docNo = '';
        if (type.indexOf('Aadhaar') === 0) {
          docNo = this.aadharNo || '';
        } else if (type === 'PAN Card') {
          docNo = this.panNo || '';
        }
        this.files.push({ document_type: type, document_path: res.file_name, document_no: docNo, file_name: file.name });
      } else {
        this.appService.showAlert('KYC Upload', (res && res.msg) || 'Upload failed', 'error');
      }
    }, () => {
      this.uploading[type] = false;
      this.appService.showAlert('KYC Upload', 'Upload failed for ' + file.name, 'error');
    });
  }

  remove(f: KycFile) {
    this.files = this.files.filter((x) => x !== f);
  }

  /** Saves uploaded files as one document record for the customer; emits true when nothing to save. */
  save(customerId?: any, branchId?: any): Observable<boolean> {
    const cid = customerId || this.customerId;
    const bid = branchId || this.branchId;
    if (!this.files.length) {
      return of(true);
    }
    if (!cid || !bid) {
      return of(false);
    }
    const t = new Date();
    const body = {
      tenant_id: localStorage.getItem('tenant_id'),
      branch_id: bid,
      customer_id: [{ customer_id: cid }],
      collected_employee_id: localStorage.getItem('logged_employee_id'),
      collect_date: { year: t.getFullYear(), month: t.getMonth() + 1, day: t.getDate() },
      collected_from: 'Customer Profile KYC',
      remark: 'Uploaded from customer profile',
      created_by: localStorage.getItem('logged_employee_id'),
      DocumentDetails: this.files.map((f) => ({ document_type: f.document_type, document_path: f.document_path, document_no: f.document_no })),
    };
    return this.userservice.getData(body, 'store-document', 'JSON').pipe(
      map((res: any) => {
        const ok = !!(res && res.status === 'Success');
        if (ok) {
          this.files = [];
        }
        return ok;
      }),
      catchError(() => of(false))
    );
  }

  saveNow() {
    this.saving = true;
    this.save().subscribe((ok) => {
      this.saving = false;
      this.appService.showAlert('KYC Documents', ok ? 'Documents saved. They are now waiting for KYC approval.' : 'Unable to save documents', ok ? 'success' : 'error');
    });
  }
}
