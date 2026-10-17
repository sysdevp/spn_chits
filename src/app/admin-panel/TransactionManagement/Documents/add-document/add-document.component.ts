import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  
  selector: 'app-add-document',
  templateUrl: './add-document.component.html',
  styles: []
})

export class AddDocumentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;

  form_message = true;
  submitted = false;
  disableBtn = true;
  SubmitbuttonName: string;
  AddDocument:  FormGroup;
  isSuperAdmin:boolean;
  branchList: any[];
  customerList: any[];
  employeeList: any[];
  companyList: any[];
  enrollmentList: any[];
  guarantorList: any[];
  hideChitDetail:boolean=true;
  hideDocuments: boolean=true;
  hideAssets: boolean=true;
  hideGuarantorDetail:boolean=true;
  hideNomineeorDetail:boolean=true;
  isChitSelected: boolean=true;
  currentDate: any;
  numOnlyPattern = "^[0-9]*$";
  uploadType: any;
  uploadIndex: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  name: any;

  APIUrl:string;
  overallupload: number;
  custid: any;
  nomineeList: any[];
  uploadState: { [key: string]: { busy: boolean, name: string, error: string } } = {};

  constructor(private http: HttpClient,private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.overallupload = 0;  
    this.APIUrl = environment.API;  
    this.SubmitbuttonName = "Submit";
    let today_date = new Date();
     this.currentDate = {
       year: today_date.getFullYear(),
       month: today_date.getMonth()+1,
       day: today_date.getDate()
     };
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1,
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

  branchChg(event)
  {
    let tenant_id = this.AddDocument.get('tenant_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond ,"get-customers-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerList = response.data;
          this.dropdownList = [];
          for(let i=0; i<((this.customerList).length); i++) {
              var obj = {};
              obj["customer_id"] = response.data[i].id;
              obj["name"] = response.data[i].full_name;
              this.dropdownList.push(obj);
            }
        } else {
          this.customerList = [];
        }
      } catch (err) {}
    }, (err: any) => { });

    let cond1 = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond1 ,"get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      } catch (err) {}
    }, (err: any) => { });
    this.hideChitDetail=true;
  }

  collectedFromChg(event) {
    let selected = event.target.value;
    if(selected==1) {
      this.hideGuarantorDetail = true;
      this.hideNomineeorDetail = true;
    }
    else if(selected==2) {
      this.hideGuarantorDetail = false;
      this.hideNomineeorDetail = true;
    }
    else{
      this.hideGuarantorDetail = true;
      this.hideNomineeorDetail = false;
    }
  }

  customerChg(item: any)
  {
    let custname = item;
    this.custid = custname.customer_id;
    this.hideChitDetail=false;
    this.isChitSelected=true;
    this.hideDocuments=true;
    this.hideAssets=true;
    this.hideGuarantorDetail=true;
    let tenant_id = this.AddDocument.get('tenant_id').value;
    let branch_id = this.AddDocument.get('branch_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[customer_id]" : this.custid
    }
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.enrollmentList = response.data;
        } else {
          this.enrollmentList = [];
        }
      }  catch (err) { this.enrollmentList = []; }
      if (!this.enrollmentList || !this.enrollmentList.length) {
        // Customer without chit enrollment can still submit KYC documents
        this.uploadChg();
      }
    }, (err: any) => { this.enrollmentList = []; this.uploadChg(); });
    let cond1 = {
      "customer_id" : this.custid
    }
    this.userservice.getData(cond1, "get-guarantor-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.guarantorList = response.data;
        } else {
          this.guarantorList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond1, "get-nominees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.nomineeList = response.data;
        } else {
          this.nomineeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  noOfDocChg(event)
  { 
    let documentCount=event.target.value;
    const documentArray = < FormArray > this.AddDocument.get('DocumentDetails');
    documentArray.controls = [];
    this.clearUploadState('document');
    for(let i=0;i<parseInt(documentCount); i++) {
      this.addDocumentDetail();
    }
    if(parseInt(documentCount)>0) {
      this.hideDocuments = false;
    }
    else {
      this.hideDocuments = true;
    }
  }

  noOfAssetChg(event)
  {
    let assetCount=event.target.value;
    const assetArray = < FormArray > this.AddDocument.get('AssetDetails');
    assetArray.controls = [];
    this.clearUploadState('asset');
    for(let i=0;i<parseInt(assetCount); i++) {
      this.addAssetDetail();
    }
    if(parseInt(assetCount)>0) {
      this.hideAssets = false;
    }
    else {
      this.hideAssets = true;
    }
  }

  get f() {
    return this.AddDocument.controls;
  }

  addDocumentDetail() { 
    const control = <FormArray>this.AddDocument.controls['DocumentDetails'];
    control.push(this.initDocumentDetail());
  }

  initDocumentDetail() {
    return this.formBuilder.group({
      'document_name': [''],
      'document_no': ['', Validators.required],
      'document_path':['', Validators.required],
      'document_type':['', Validators.required]
    }); 
  }

  removeDocumentDetail(i: number) {
    const control = <FormArray>this.AddDocument.controls['DocumentDetails'];
    control.removeAt(i);
  }

  get DocumentDetails() {
    return <FormArray>this.AddDocument.get('DocumentDetails');
  }

  addAssetDetail() {
    const control = <FormArray>this.AddDocument.controls['AssetDetails'];
    control.push(this.initAssetDetail());
  }

  initAssetDetail() {
    return this.formBuilder.group({
      'asset_name': ['', Validators.required],
      'asset_value': ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      'asset_path': ['', Validators.required],
    });
  }

  removeAssetDetail(i: number) {
    const control = <FormArray>this.AddDocument.controls['AssetDetails'];
    control.removeAt(i);
  }

  get AssetDetails() {
    return <FormArray>this.AddDocument.get('AssetDetails');
  }

  open(content, options = {},index,uploadType) {
    this.uploadIndex = index;
    this.uploadType = uploadType;
    this.modalService.open(content, options).result.then((result) => {
      
    }, (reason) => {
      
    });
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

  rowUpload(uploadType: string, index: number) {
    return this.uploadState[uploadType + index] || { busy: false, name: '', error: '' };
  }

  clearUploadState(uploadType: string) {
    Object.keys(this.uploadState).forEach((k) => {
      if (k.indexOf(uploadType) === 0) {
        delete this.uploadState[k];
      }
    });
  }

  isAnyUploadBusy() {
    return Object.keys(this.uploadState).some((k) => this.uploadState[k].busy);
  }

  onRowFileSelected(event: any, index: number, uploadType: string) {
    const input = event.target;
    const file: File = input && input.files && input.files[0] ? input.files[0] : null;
    if (!file) {
      return;
    }
    input.value = '';
    const key = uploadType + index;
    const pathCtrl = uploadType == 'asset'
      ? this.AssetDetails.at(index).get('asset_path')
      : this.DocumentDetails.at(index).get('document_path');
    if (file.size > 10 * 1024 * 1024) {
      this.uploadState[key] = { busy: false, name: file.name, error: 'File is larger than 10 MB' };
      this.appService.showAlert("Document", "File is larger than 10 MB. Please choose a smaller file.", "error");
      return;
    }
    this.uploadState[key] = { busy: true, name: file.name, error: '' };
    pathCtrl.setValue('');

    const token = localStorage.getItem('token');
    let headers = new HttpHeaders();
    if (token) {
      headers = headers.set('Authorization', 'Bearer ' + token);
    }
    const formData = new FormData();
    formData.append('asset_path', file, file.name);
    formData.append('upload_type', uploadType);

    this.http.post(this.APIUrl + 'asset-document-upload', formData, { headers: headers }).subscribe((response: any) => {
      const fileName = response && response.file_name ? response.file_name : '';
      pathCtrl.setValue(fileName);
      const msg = fileName ? '' : ((response && response.msg) || 'Upload failed');
      this.uploadState[key] = { busy: false, name: file.name, error: msg };
      if (!fileName) {
        this.appService.showAlert("Document", msg, "error");
      }
    }, (error) => {
      const msg = (error && error.error && error.error.msg) || 'Upload failed. Please login again and retry.';
      pathCtrl.setValue('');
      this.uploadState[key] = { busy: false, name: file.name, error: msg };
      this.appService.showAlert("Document", msg, "error");
    });
  }

  uploadChg() {
      this.isChitSelected=false;
      this.disableBtn = false;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddDocument.value);
    if (this.isAnyUploadBusy()) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.appService.showAlert("Document", "Please wait for all files to finish uploading", "error");
      return;
    }
    if (this.AddDocument.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-document", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Document",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-documents']);
          }, 300);
        } else {
          this.appService.showAlert("Document", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) { 
        this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddDocument = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      enrollment_id: [''],
      collected_employee_id: ['', Validators.required],
      collected_from: ['', Validators.required],
      collect_date: [this.currentDate, Validators.required],
      guarantor_id: [''],
      nominee_id: [''],
      remark: [''],
      created_by: [''],
      'DocumentDetails': this.formBuilder.array([
        this.formBuilder.group({
          'document_name': [''],
          'document_no': ['', Validators.required],
          'document_path': ['', Validators.required],
          'document_type': ['', Validators.required]
        })
      ]),
      'AssetDetails': this.formBuilder.array([
        this.formBuilder.group({
          'asset_name': ['', Validators.required],
          'asset_value': ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
          'asset_path': ['', Validators.required],
        })
      ]),
    });
    const assetArray = < FormArray > this.AddDocument.get('AssetDetails');
    assetArray.controls = [];
    const documentArray = < FormArray > this.AddDocument.get('DocumentDetails');
    documentArray.controls = [];
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddDocument.get('tenant_id').setValue(tenant_id);
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
        }  catch (err) { }
      }, (err: any) => { });
    }
    this.AddDocument.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.dropdownSettings = {
      singleSelection: true,
      idField: 'customer_id',
      textField: 'name',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true
    };
  }
}
