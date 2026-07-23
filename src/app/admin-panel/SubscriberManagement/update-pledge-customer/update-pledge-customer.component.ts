import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../environments/environment';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-update-pledge-customer',
  templateUrl: './update-pledge-customer.component.html',
  styles: []
})
export class UpdatePledgeCustomerComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('pledge_customer_id') focus4:ElementRef;

  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  UpdatePledge:  FormGroup;
  isSuperAdmin:boolean;
  branchList: any[];
  customerList: any[];
  employeeList: any[];
  companyList: any[];
  enrollmentList: any[];
  hideChitDetail:boolean=false;
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
  customerpledgeList: any;
  enrollmentpledgeList: any;

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
    let tenant_id = this.UpdatePledge.get('tenant_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : event.target.value
    }
    this.setEnrolledCustomerList(cond);
    this.setEnrolledpledgeCustomerList(cond);
    this.hideChitDetail=true;
  }

  setEnrolledpledgeCustomerList(cond) {
    this.userservice.getData(cond ,"get-customers-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.customerpledgeList = response.data;
        } else {
          this.customerpledgeList = [];
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
    let tenant_id = this.UpdatePledge.get('tenant_id').value;
    let branch_id = this.UpdatePledge.get('branch_id').value;
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

  customerpledgeChg(event)
  {
    this.hideChitDetail=false;
    let tenant_id = this.UpdatePledge.get('tenant_id').value;
    let branch_id = this.UpdatePledge.get('branch_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[customer_id]" : event.target.value
    }
    this.setEnrollmentpledgeList(cond);
  }
  setEnrollmentpledgeList(cond) {
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.enrollmentpledgeList = response.data;
        } else {
          this.enrollmentpledgeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }




  get f() {
    return this.UpdatePledge.controls;
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
    const result = Object.assign({}, this.UpdatePledge.value); 
    if (this.UpdatePledge.invalid) 
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
      else if(this.f.pledge_customer_id.errors) {
        this.focus4.nativeElement.focus();
      } 
      return;
    }
    
    this.userservice.getData(result, "update-pledge", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Pledge",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/pledge-customer-report']);
          }, 300);
        } else {
          this.appService.showAlert("Pledge", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Pledge", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdatePledge = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      pledge_customer_id: ['', Validators.required],
      status: ['', Validators.required],
      enrollment_id: [''],
      pledgeenrollment_id: [''],
      pledge_date:[''],
      unpledge_date:[''],
      remark: [''],
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

    this.route.params.subscribe(params => {
      this.uploadId = params.id;
      this.UpdatePledge.get('id').setValue(this.uploadId);
      this.UpdatePledge.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.uploadId 
      }
      this.userservice.getData(cond, "get-pledge-customer","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdatePledge.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdatePledge.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdatePledge.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdatePledge.get('enrollment_id').setValue(response.data[0].enrollment_id);
            this.UpdatePledge.get('pledge_customer_id').setValue(response.data[0].pledge_customer_id);
            this.UpdatePledge.get('pledgeenrollment_id').setValue(response.data[0].pledge_enrollment_id);
            this.UpdatePledge.get('remark').setValue(response.data[0].remark);
            this.UpdatePledge.get('status').setValue(response.data[0].status);
            this.setBranchList(response.data[0].tenant_id);
            let cond1 = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id
            } 
            this.setEnrolledCustomerList(cond1);
            this.setEnrolledpledgeCustomerList(cond1);
            let cond2 = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id,
              "conditions[customer_id]" : response.data[0].customer_id
            } 
            let cond3 = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              "conditions[branch_id]" : response.data[0].branch_id,
              "conditions[customer_id]" : response.data[0].pledge_customer_id
            } 
            this.setEnrollmentList(cond2);
            this.setEnrollmentpledgeList(cond3);
            
            if(response.data[0].pledge_date!='') {
              let collDate = (response.data[0].pledge_date).split('-');
              console.log(collDate);
              let setCollDate = {
                'year': parseInt(collDate[0]),
                'month': parseInt(collDate[1]),
                'day': parseInt(collDate[2])
              } 
              this.UpdatePledge.get('pledge_date').setValue(setCollDate);
            }
            this.UpdatePledge.get('remark').setValue(response.data[0].remark);
           
          }
          else {
            this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
