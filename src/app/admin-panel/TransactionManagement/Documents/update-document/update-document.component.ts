import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-document',
  templateUrl: './update-document.component.html',
  styles: []
})

export class UpdateDocumentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;

  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  UpdateDocument:  FormGroup;
  isSuperAdmin:boolean;
  branchList: any[];
  customerList: any[];
  employeeList: any[];
  companyList: any[];
  enrollmentList: any[];
  hideChitDetail:boolean=true;
  hideDocuments: boolean=true;
  hideAssets: boolean=true;
  hideGuarantorDetail:boolean=true;
  numOnlyPattern = "^[0-9]*$";
  uploadType: any;
  uploadIndex: any;
  uploadId: any;
  noOfDocuments: any;
  noOfAssets: any;
  enrollmentId: any;
  collectedFrom: any;
  documentList: any[];
  assetList: any[];
  
  APIUrl:string;
  auth_token = "Bearer "+localStorage.getItem('token');
  uploader = new FileUploader({
    url: '',
    headers: [{
      name: 'Content-Type',
      value: 'application/x-www-form-urlencoded'
    },
    {
      name: 'Authorization',
      value: this.auth_token
    },
    {
      name: 'Accept',
      value: 'multipart/form-data'
    }]
  });
  hasBaseDropZoneOver = false;
  overallupload: number;
  upload_document: string;

  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

  constructor(private http: HttpClient,  private route: ActivatedRoute, private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.overallupload = 0;  
    this.APIUrl = environment.API;  
    this.SubmitbuttonName = "Submit";
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
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
    let tenant_id = this.UpdateDocument.get('tenant_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : event.target.value
    }
    this.setEnrolledCustomerList(cond);

    let cond1 = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : event.target.value
    }
    this.setCollectedEmployeeList(cond1);
    this.hideChitDetail=true;
  }

  setCollectedEmployeeList(cond) {
    this.userservice.getData(cond ,"get-employees", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  setEnrolledCustomerList(cond) {
    this.userservice.getData(cond ,"get-customers-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      } catch (err) {}
    }, (err: any) => { });
  }

  collectedFromChg(event) {
    let selected = event.target.value;
    if(selected==1) {
      this.hideGuarantorDetail = true;
    }
    else if(selected==2) {
      this.hideGuarantorDetail = false;
    }
  }

  customerChg(event)
  {
    this.hideChitDetail=false;
    let tenant_id = this.UpdateDocument.get('tenant_id').value;
    let branch_id = this.UpdateDocument.get('branch_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[customer_id]" : event.target.value
    }
    this.setEnrollmentList(cond);
  }

  setEnrollmentList(cond) {
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.enrollmentList = response.data;
        } else {
          this.enrollmentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  noOfDocChg(event)
  { 
    let documentCount=event.target.value;
    const documentArray = < FormArray > this.UpdateDocument.get('DocumentDetails');
    documentArray.controls = [];
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
    const assetArray = < FormArray > this.UpdateDocument.get('AssetDetails');
    assetArray.controls = [];
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

  returnDocument(documentId) {
    swal({
      title: "Are you sure?",
      text: "You are return the documents.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, return it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : documentId
        }
        this.userservice.getData(cond, "return-document","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') { 
              this.documentList.filter((listing: any) => {
                if(listing.id == documentId)
                {
                  listing.status = 2;
                }
              });
              this.appService.showAlert("Document", response.msg , "success");
            }
            else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
            }
          } catch (err) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  removeDocument(documentId,index) {
    swal({
      title: "Are you sure?",
      text: "You are deleting the documents.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : documentId,
          "deleted_by" : localStorage.getItem('logged_user_id')
        }
        this.userservice.getData(cond, "remove-document","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') { 
              this.documentList.splice(index, 1); 
              this.appService.showAlert("Document", response.msg , "success");
            }
            else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
            }
          } catch (err) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  returnAsset(assetId) {
    swal({
      title: "Are you sure?",
      text: "You are return the documents.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, return it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : assetId
        }
        this.userservice.getData(cond, "return-asset","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') { 
              this.assetList.filter((listing: any) => {
                if(listing.id == assetId)
                {
                  listing.status = 2;
                }
              });
              this.appService.showAlert("Document", response.msg , "success");
            }
            else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
            }
          } catch (err) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  removeAsset(assetId,index) {
    swal({
      title: "Are you sure?",
      text: "You are deleting the documents.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : assetId,
          "deleted_by" : localStorage.getItem('logged_user_id')
        }
        this.userservice.getData(cond, "remove-asset","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') { 
              this.assetList.splice(index, 1); 
              this.appService.showAlert("Document", response.msg , "success");
            }
            else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
            }
          } catch (err) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  get f() {
    return this.UpdateDocument.controls;
  }

  addDocumentDetail() { 
    const control = <FormArray>this.UpdateDocument.controls['DocumentDetails'];
    control.push(this.initDocumentDetail());
  }

  initDocumentDetail() {
    return this.formBuilder.group({
      'document_name': [''],
      'document_no': ['', Validators.required],
      'document_path':['', Validators.required],
      'document_type':['', Validators.required],
    }); 
  }

  removeDocumentDetail(i: number) {
    const control = <FormArray>this.UpdateDocument.controls['DocumentDetails'];
    control.removeAt(i);
  }

  get DocumentDetails() {
    return <FormArray>this.UpdateDocument.get('DocumentDetails');
  }

  addAssetDetail() {
    const control = <FormArray>this.UpdateDocument.controls['AssetDetails'];
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
    const control = <FormArray>this.UpdateDocument.controls['AssetDetails'];
    control.removeAt(i);
  }

  get AssetDetails() {
    return <FormArray>this.UpdateDocument.get('AssetDetails');
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
  
  uploadAssetDetails(event) { 
    this.overallupload = 0;
    let fileList: any = event;
    if (fileList.length > 0) 
    {
      let f: File = fileList[0]._file;
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      const formData: FormData = new FormData();
      formData.append("asset_path", f, f.name);
      formData.append("upload_type",this.uploadType);
      this.upload_document = f.name;
      this.http.post(this.APIUrl+ 'asset-document-upload', formData, {headers: headers}).subscribe((response) => { 
        if(this.uploadType=="document") {
          const pfArray = < FormArray > this.UpdateDocument.get('DocumentDetails'); 
          pfArray.controls[this.uploadIndex].get('document_path').setValue(response['file_name']);
        }
        else if(this.uploadType=="asset") {
          const pfArray = < FormArray > this.UpdateDocument.get('AssetDetails');
          pfArray.controls[this.uploadIndex].get('asset_path').setValue(response['file_name']);
        }
        this.triggerFalseClick();
      },
      (error) => { });
    }
  }

  triggerFalseClick() {  
    let element: HTMLElement = document.getElementById("myDiv") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
    element = document.getElementById("myDivDocuments") as HTMLElement;
    if(element!=null)
    {
      element.click();
    }
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateDocument.value); 
    if (this.UpdateDocument.invalid) 
    { 
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
    
    this.userservice.getData(result, "update-document", "JSON").subscribe((response: any) => {
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
        this.appService.showAlert("Document", 'Sorry!! Something went wrong!', "error");
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
    this.UpdateDocument = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      enrollment_id: [''],
      collected_employee_id: [''],
      collected_from: [''],
      collect_date: [null],
      guarantor_id: [''],
      remark: [''],
      'DocumentDetails': this.formBuilder.array([
        this.formBuilder.group({
          'document_name': [''],
          'document_no': ['', Validators.required],
          'document_path': ['', Validators.required],
          'document_type': ['', Validators.required],
        })
      ]),
      'AssetDetails': this.formBuilder.array([
        this.formBuilder.group({
          'asset_name': ['', Validators.required],
          'asset_value': ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
          'asset_path': ['', Validators.required],
        })
      ]),
      updated_by: [''],
      id: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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

    const assetArray = < FormArray > this.UpdateDocument.get('AssetDetails');
    assetArray.controls = [];
    const documentArray = < FormArray > this.UpdateDocument.get('DocumentDetails');
    documentArray.controls = [];

    this.route.params.subscribe(params => {
      this.uploadId = params.id;
      this.UpdateDocument.get('id').setValue(this.uploadId);
      this.UpdateDocument.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.uploadId 
      }
      this.userservice.getData(cond, "get-documents","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateDocument.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateDocument.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateDocument.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateDocument.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdateDocument.get('collected_employee_id').setValue(response.data[0].collected_employee_id);
            this.UpdateDocument.get('collected_from').setValue(response.data[0].collected_from);
            if(response.data[0].collect_date!='') {
              let collDate = (response.data[0].collect_date).split('/');
              let setCollDate = {
                'year': parseInt(collDate[2]),
                'month': parseInt(collDate[1]),
                'day': parseInt(collDate[0])
              } 
              this.UpdateDocument.get('collect_date').setValue(setCollDate);
            }
            this.UpdateDocument.get('guarantor_id').setValue(response.data[0].guarantor_id);
            this.UpdateDocument.get('remark').setValue(response.data[0].remark);
            this.noOfDocuments = response.data[0].document_info.length;
            this.noOfAssets = response.data[0].asset_info.length;
            this.enrollmentId = response.data[0].enrollment_id;
            this.collectedFrom = response.data[0].collected_from;
            this.documentList = response.data[0].document_info;
            this.assetList = response.data[0].asset_info;
            const assetArray = < FormArray > this.UpdateDocument.get('AssetDetails');
            assetArray.controls = [];
            const documentArray = < FormArray > this.UpdateDocument.get('DocumentDetails');
            documentArray.controls = [];
            this.setBranchList(response.data[0].tenant_id);
            let cond1 = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id
            } 
            this.setEnrolledCustomerList(cond1);
            let cond3 = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id,
              "conditions[customer_id]" : response.data[0].customer_id
            }
            this.setEnrollmentList(cond3);
            this.setCollectedEmployeeList(cond1);
            if(response.data[0].customer_id) {
              this.hideChitDetail=false;
            }
            if(response.data[0].document_info.length>0) {
              this.hideDocuments = false;
            }
            if(response.data[0].asset_info.length>0) {
              this.hideAssets = false;
            }
            if(response.data[0].collected_from==1) {
              this.hideGuarantorDetail = true;
            }
          }
          else {
            this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Document", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
