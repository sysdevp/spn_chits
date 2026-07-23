import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../../environments/environment';

@Component({
  
  selector: 'app-add-tenant',
  templateUrl: './add-tenant.component.html',
  styles: []
})

export class AddTenantComponent implements OnInit {
  @ViewChild('chit_fund_name') focus1:ElementRef;
  @ViewChild('company_logo') focus2:ElementRef;
  @ViewChild('receipts_logo') focus3:ElementRef;
  @ViewChild('favicon') focus4:ElementRef;
  @ViewChild('header_name') focus5:ElementRef;
  @ViewChild('admin_email') focus6:ElementRef;
  @ViewChild('admin_password') focus7:ElementRef;
  @ViewChild('confirm_password') focus8:ElementRef;
  @ViewChild('gst_no') focus9:ElementRef;
  @ViewChild('authorized_person_name') focus10:ElementRef;
  @ViewChild('phone_no') focus11:ElementRef;
  @ViewChild('mobile_no') focus12:ElementRef;
  @ViewChild('is_new_database') focus13:ElementRef;
  @ViewChild('company_db_name') focus14:ElementRef;
  @ViewChild('no_branches') focus15:ElementRef;
  @ViewChild('no_groups') focus16:ElementRef;
  @ViewChild('no_users') focus17:ElementRef;
  @ViewChild('no_customers') focus18:ElementRef;
  @ViewChild('start_date') focus19:ElementRef;
  @ViewChild('renewal_date') focus20:ElementRef;
  @ViewChild('version') focus21:ElementRef;

  AddTenant: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mobnumPattern = "^((\\+91-?)|0)?[0-9]{10}$";
  phonenumPattern = "^[0-9]{5}([- ]*)[0-9]{6}$";
  alphaNumDashSpPattern = "^[a-zA-Z0-9- ]*$";
  alphaNumUnderscorePattern = "^[a-zA-Z0-9_]*$";
  alphaSpPattern = "^[a-zA-Z ]*$";
  companyLogo: any;
  receiptsLogo: any;
  favicon: any;
  uploadType: any;
  APIUrl: string;
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
  hideDatabase: boolean = true;
  faviconLogo: any;
  ConfirmPasswordAlertMsg: boolean = false;
  showPassword:boolean=false;
  showConfirmPassword:boolean=false;

  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

  constructor(private http: HttpClient,private modalService: NgbModal,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.overallupload = 0;  
    this.APIUrl = environment.API;  
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddTenant.controls;
  }

  open(content, options = {},uploadType) {
    this.uploadType = uploadType;
    this.modalService.open(content, options).result.then((result) => {
    }, (reason) => {});
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

  uploadTenantFile(event) { 
    this.overallupload = 0;
    let fileList: any = event;
    if (fileList.length > 0) {
      let f: File = fileList[0]._file;
      let headers = new HttpHeaders();
      headers.append('Content-Type', 'multipart/form-data');
      headers.append('Accept', 'application/json');
      const formData: FormData = new FormData();
      formData.append("uploaded_file", f, f.name);
      formData.append("upload_type",this.uploadType);
      //this.customer_photo = f.name;
      this.http.post(this.APIUrl+ 'tenant-uploads', formData, {headers: headers})
      .subscribe(
        (response) => { 
          if(this.uploadType=="company_logo") { 
            this.AddTenant.get('company_logo').setValue(response['file_name']);
            this.companyLogo = response['file_name'];
          }
          else if(this.uploadType=="receipts_logo") {
            this.AddTenant.get('receipts_logo').setValue(response['file_name']);
            this.receiptsLogo = response['file_name'];
          }
          else if(this.uploadType=="favicon") {
            this.AddTenant.get('favicon').setValue(response['file_name']);
            this.faviconLogo = response['file_name'];
          }
          //this.AddTenant.get('photo').setValue(response['file_name']);
          this.triggerFalseClick();
        },
        (error) => { 
        }                   
      );
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
  
  chkChg(event) {
    if(event.target.checked) {
      this.hideDatabase = false;
      this.AddTenant.get('company_db_name').setValidators([Validators.required, Validators.pattern(this.alphaNumUnderscorePattern)]);
    }
    else {
      this.hideDatabase = true;
      this.AddTenant.get('company_db_name').setValidators(null);
    }
    this.AddTenant.get('company_db_name').updateValueAndValidity();
  }

  onPwdChange(event) { 
    let confirmPassword = event.target.value;
    let admin_password = this.AddTenant.get('admin_password').value;
    if(confirmPassword==admin_password) {
      this.ConfirmPasswordAlertMsg = false;
    }
    else{
      this.ConfirmPasswordAlertMsg = true;
    }
  }

  showHidePassword(passwordType,showHide) {
    if(passwordType=='password') {
      this.showPassword=showHide;
    }
    else if(passwordType=='confirmPassword') {
      this.showConfirmPassword=showHide;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddTenant.value); 
    if (this.AddTenant.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.chit_fund_name.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.company_logo.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.receipts_logo.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.favicon.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.header_name.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.admin_email.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.admin_password.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.confirm_password.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.gst_no.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.authorized_person_name.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.phone_no.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.mobile_no.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.is_new_database.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.company_db_name.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.no_branches.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.no_groups.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.no_users.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.no_customers.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.start_date.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.renewal_date.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.version.errors) {
        this.focus21.nativeElement.focus();
      }
      return;
    }
    let confirmPassword = this.AddTenant.get('confirm_password').value;
    let admin_password = this.AddTenant.get('admin_password').value;
    if(confirmPassword==admin_password) {
      this.ConfirmPasswordAlertMsg = false;
    }
    else{
      this.ConfirmPasswordAlertMsg = true;
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      return;
    }

    this.userservice.getData(result, "store-tenant", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Tenant",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-tenant']);
          }, 300);
        } else {
          this.appService.showAlert("Tenant", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) { 
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Tenant", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddTenant = this.formBuilder.group({
      chit_fund_name: ['',[Validators.required,Validators.pattern(this.alphaNumDashSpPattern)]], //alpha num space
      company_logo: [''],
      receipts_logo: [''],
      sms: [''],
      online_bidding: [''],
      favicon: [''],
      version: ['', Validators.required],
      header_name: ['', Validators.required],
      admin_email: ['',[Validators.pattern(this.mailPattern)]],
      admin_password: ['', [Validators.required, Validators.minLength(8)]],
      confirm_password: ['', Validators.required],
      gst_no: ['', Validators.required], //gst format
      authorized_person_name: ['',[Validators.required,Validators.pattern(this.alphaSpPattern)]],
      phone_no: ['',[Validators.pattern(this.phonenumPattern)]],
      mobile_no: ['',[Validators.required,Validators.pattern(this.mobnumPattern)]],
      no_branches: ['', Validators.required],
      no_groups: ['', Validators.required],
      no_users: ['', Validators.required],
      no_customers: ['', Validators.required],
      start_date: ['', Validators.required],
      renewal_date: ['', Validators.required],
      is_new_database: [''],
      company_db_name: [''], //required id new database
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
    }
    else {
      this.isSuperAdmin = true;
    }
    this.AddTenant.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
