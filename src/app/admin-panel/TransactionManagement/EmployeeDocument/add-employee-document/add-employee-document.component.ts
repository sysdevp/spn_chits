import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { ActivatedRoute, Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  selector: 'app-add-employee-document',
  templateUrl: './add-employee-document.component.html',
  styles: [`
    .doc-upload-box {
      border: 1px dashed #c8ddd0;
      border-radius: 10px;
      padding: 12px;
      background: #f8faf9;
      min-height: 120px;
    }
    .doc-upload-box img {
      max-width: 100%;
      max-height: 110px;
      border-radius: 8px;
      display: block;
      margin: 0 auto 8px;
    }
    .doc-upload-empty {
      text-align: center;
      color: #6b7280;
      padding: 18px 8px;
    }
    .doc-row {
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 12px;
      background: #fff;
    }
    .existing-doc {
      border: 1px solid #dbeafe;
      background: #f8fbff;
      border-radius: 8px;
      padding: 10px 12px;
      margin-bottom: 8px;
    }
  `]
})
export class AddEmployeeDocumentComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;

  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Submit';
  form: FormGroup;
  isSuperAdmin = false;
  branchList: any[] = [];
  employeeList: any[] = [];
  companyList: any[] = [];
  currentDate: any;
  APIUrl: string;
  editId: any = null;
  isEditMode = false;
  existingDocs: any[] = [];
  removedDetailIds: any[] = [];
  pageTitle = 'Add Employee Document';

  constructor(
    private http: HttpClient,
    private appService: AppService,
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.APIUrl = environment.API;
    const today_date = new Date();
    this.currentDate = {
      year: today_date.getFullYear(),
      month: today_date.getMonth() + 1,
      day: today_date.getDate()
    };
  }

  get f() {
    return this.form.controls;
  }

  get DocumentDetails(): FormArray {
    return this.form.get('DocumentDetails') as FormArray;
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
    const tenant_id = this.form.get('tenant_id').value;
    const branch_id = event.target.value;
    if (!this.isEditMode) {
      this.form.get('employee_id').setValue('');
    }
    this.loadEmployees(tenant_id, branch_id);
  }

  loadEmployees(tenant_id, branch_id) {
    this.userservice.getData({
      'conditions[tenant_id]': tenant_id,
      'conditions[branch_id]': branch_id
    }, 'get-employees', 'POST').subscribe((response: any) => {
      this.employeeList = response && response.status === 'Success' ? response.data : [];
    });
  }

  addDocumentRow() {
    this.DocumentDetails.push(this.initDocumentDetail());
  }

  initDocumentDetail() {
    return this.formBuilder.group({
      document_type: ['Aadhar', Validators.required],
      document_no: [''],
      document_path: ['', Validators.required],
      preview_url: [''],
      upload_name: [''],
      upload_error: [''],
      uploading: [false]
    });
  }

  removeDocumentRow(i: number) {
    this.DocumentDetails.removeAt(i);
  }

  removeExistingDoc(doc: any) {
    if (!doc || !doc.id) {
      return;
    }
    if (!confirm('Remove this document from the employee pack?')) {
      return;
    }
    this.removedDetailIds.push(doc.id);
    this.existingDocs = this.existingDocs.filter((d) => d.id !== doc.id);
  }

  onFileSelected(event: any, index: number) {
    const input = event.target;
    const file = input && input.files && input.files[0] ? input.files[0] : null;
    if (!file) {
      return;
    }
    const row = this.DocumentDetails.at(index);
    row.get('upload_error').setValue('');
    row.get('upload_name').setValue(file.name);

    const reader = new FileReader();
    reader.onload = () => {
      row.get('preview_url').setValue(reader.result);
    };
    reader.readAsDataURL(file);

    this.uploadDocumentFile(file, index);
    input.value = '';
  }

  uploadDocumentFile(file: File, index: number) {
    const row = this.DocumentDetails.at(index);
    row.get('uploading').setValue(true);
    const token = localStorage.getItem('token');
    let headers = new HttpHeaders();
    if (token) {
      headers = headers.set('Authorization', 'Bearer ' + token);
    }

    const formData = new FormData();
    formData.append('asset_path', file, file.name);
    formData.append('upload_type', 'document');

    this.http.post(this.APIUrl + 'asset-document-upload', formData, { headers: headers }).subscribe(
      (response: any) => {
        row.get('uploading').setValue(false);
        const fileName = response && response.file_name ? response.file_name : '';
        if (fileName) {
          row.get('document_path').setValue(fileName);
          row.get('document_path').updateValueAndValidity();
          row.get('upload_error').setValue('');
          this.appService.showAlert('Document', 'File uploaded successfully', 'success');
        } else {
          row.get('document_path').setValue('');
          row.get('preview_url').setValue('');
          row.get('upload_error').setValue((response && response.msg) || 'Upload failed');
          this.appService.showAlert('Document', (response && response.msg) || 'Upload failed', 'error');
        }
      },
      (error) => {
        row.get('uploading').setValue(false);
        row.get('document_path').setValue('');
        row.get('preview_url').setValue('');
        const msg = (error && error.error && error.error.msg) || 'Upload failed. Please login again and retry.';
        row.get('upload_error').setValue(msg);
        this.appService.showAlert('Document', msg, 'error');
      }
    );
  }

  clearFile(index: number) {
    const row = this.DocumentDetails.at(index);
    row.get('document_path').setValue('');
    row.get('preview_url').setValue('');
    row.get('upload_name').setValue('');
    row.get('upload_error').setValue('');
  }

  onMultiFilesSelected(event: any) {
    const files = event && event.target && event.target.files ? event.target.files : [];
    if (!files.length) {
      return;
    }
    for (let i = 0; i < files.length; i++) {
      let index = -1;
      for (let r = 0; r < this.DocumentDetails.length; r++) {
        if (!this.DocumentDetails.at(r).get('document_path').value && !this.DocumentDetails.at(r).get('upload_name').value) {
          index = r;
          break;
        }
      }
      if (index < 0) {
        this.addDocumentRow();
        index = this.DocumentDetails.length - 1;
      }
      const file = files[i];
      const row = this.DocumentDetails.at(index);
      row.get('upload_name').setValue(file.name);
      const reader = new FileReader();
      reader.onload = ((idx, fr) => {
        return () => {
          this.DocumentDetails.at(idx).get('preview_url').setValue(fr.result);
        };
      })(index, reader);
      reader.readAsDataURL(file);
      this.uploadDocumentFile(file, index);
    }
    event.target.value = '';
  }

  loadEditRecord(id) {
    this.userservice.getData({ 'conditions[id]': id }, 'get-documents', 'POST').subscribe((response: any) => {
      if (!(response && response.status === 'Success' && response.data && response.data[0])) {
        this.appService.showAlert('Employee Document', 'Record not found', 'error');
        return;
      }
      const row = response.data[0];
      this.form.get('tenant_id').setValue(row.tenant_id);
      this.form.get('branch_id').setValue(row.branch_id);
      this.setBranchList(row.tenant_id);
      this.loadEmployees(row.tenant_id, row.branch_id);
      this.form.get('employee_id').setValue(row.collected_employee_id);
      this.form.get('remark').setValue(row.remark || '');
      if (row.collect_date && String(row.collect_date).indexOf('/') !== -1) {
        const parts = String(row.collect_date).split('/');
        this.form.get('collect_date').setValue({
          day: Number(parts[0]),
          month: Number(parts[1]),
          year: Number(parts[2])
        });
      }
      this.existingDocs = row.document_info || [];
      while (this.DocumentDetails.length) {
        this.DocumentDetails.removeAt(0);
      }
    });
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    this.disableBtn = true;

    const hasNewUploads = this.DocumentDetails.length > 0;
    const remainingExisting = this.existingDocs.length;

    if (this.form.invalid) {
      this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';
      this.disableBtn = false;
      return;
    }

    if (!this.isEditMode && !hasNewUploads) {
      this.SubmitbuttonName = 'Submit';
      this.disableBtn = false;
      this.appService.showAlert('Employee Document', 'Please upload at least one document', 'error');
      return;
    }

    if (this.isEditMode && !remainingExisting && !hasNewUploads) {
      this.SubmitbuttonName = 'Update';
      this.disableBtn = false;
      this.appService.showAlert('Employee Document', 'Keep at least one document or add a new one', 'error');
      return;
    }

    for (let i = 0; i < this.DocumentDetails.length; i++) {
      if (this.DocumentDetails.at(i).get('uploading').value) {
        this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';
        this.disableBtn = false;
        this.appService.showAlert('Employee Document', 'Please wait for all files to finish uploading', 'error');
        return;
      }
      if (!this.DocumentDetails.at(i).get('document_path').value) {
        this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';
        this.disableBtn = false;
        this.appService.showAlert('Employee Document', 'Please upload all new document files', 'error');
        return;
      }
    }

    const result: any = Object.assign({}, this.form.value);
    result.collected_employee_id = result.employee_id;
    result.customer_id = [{ customer_id: 0 }];
    result.collected_from = 'Employee Documents';
    result.remark = result.remark || 'Employee HR documents';
    result.updated_by = localStorage.getItem('logged_user_id');
    result.DocumentDetails = (result.DocumentDetails || []).map((d: any) => {
      return {
        document_type: d.document_type,
        document_no: d.document_no || '',
        document_path: d.document_path
      };
    });
    delete result.employee_id;

    let api = 'store-document';
    if (this.isEditMode) {
      api = 'update-document';
      result.id = this.editId;
      result.removed_document_detail_ids = this.removedDetailIds;
    }

    this.userservice.getData(result, api, 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';
      this.disableBtn = false;
      if (response && response.status === 'Success') {
        this.appService.showAlert('Employee Document', response.msg || 'Saved successfully', 'success');
        setTimeout(() => {
          this.router.navigate(['/admin-panel/view-employee-document']);
        }, 300);
      } else {
        this.appService.showAlert('Employee Document', (response && response.msg) || 'Save failed', 'error');
      }
    }, () => {
      this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';
      this.disableBtn = false;
      this.appService.showAlert('Employee Document', 'Sorry!! Something went wrong!', 'error');
    });
  }

  ngOnInit() {
    this.form = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      collect_date: [this.currentDate, Validators.required],
      remark: [''],
      created_by: [localStorage.getItem('logged_user_id')],
      DocumentDetails: this.formBuilder.array([])
    });

    this.editId = this.route.snapshot.paramMap.get('id');
    this.isEditMode = !!this.editId;
    this.pageTitle = this.isEditMode ? 'Edit Employee Document' : 'Add Employee Document';
    this.SubmitbuttonName = this.isEditMode ? 'Update' : 'Submit';

    if (!this.isEditMode) {
      this.addDocumentRow();
    }

    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.form.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status === 'Success' ? response.data : [];
      });
    }

    if (this.isEditMode) {
      this.loadEditRecord(this.editId);
    }
  }
}
