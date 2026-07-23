import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { FileUploader } from 'ng2-file-upload';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { HttpClient, HttpHeaders} from '@angular/common/http';
import { environment } from '../../../../environments/environment';

@Component({
  
  selector: 'app-pledge-customer',
  templateUrl: './pledge-customer.component.html',
  styles: []
})
export class PledgeCustomerComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('pledge_customer_id') focus4:ElementRef;

  form_message = true;
  submitted = false;
  disableBtn = true;
  SubmitbuttonName: string;
  AddPledge:  FormGroup;
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
  hideChitDetailpledge:boolean=true;
  currentDate: any;
  numOnlyPattern = "^[0-9]*$";
  uploadType: any;
  uploadIndex: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  name: any;

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
  custid: any;
  nomineeList: any[];
  enrollmentpledgeList: any[];

  fileOver(e: any) { 
    this.hasBaseDropZoneOver = e;
  }

  fileDrop(eve){}

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
    let tenant_id = this.AddPledge.get('tenant_id').value;
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


  customerChg(item: any)
  {
    let custname = item;
    this.custid = custname.customer_id;
    this.hideChitDetail=false;
    this.hideChitDetailpledge=true;
    this.isChitSelected=true;
    this.hideDocuments=true;
    this.hideAssets=true;
    this.hideGuarantorDetail=true;
    let tenant_id = this.AddPledge.get('tenant_id').value;
    let branch_id = this.AddPledge.get('branch_id').value;
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
      }  catch (err) { }
    }, (err: any) => { });
    
  }

  pledgecustomerChg(item: any)
  {
    let custname = item;
    this.custid = custname.customer_id;
    this.hideChitDetail=false;
    this.hideChitDetailpledge=false;
    this.isChitSelected=true;
    this.hideDocuments=true;
    this.hideAssets=true;
    this.hideGuarantorDetail=true;
    let tenant_id = this.AddPledge.get('tenant_id').value;
    let branch_id = this.AddPledge.get('branch_id').value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id,
      "conditions[customer_id]" : this.custid
    }
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
    return this.AddPledge.controls;
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

  uploadChg() {
      this.isChitSelected=false;
  }

  uploadpledgeChg() {
    this.isChitSelected=false;
    this.disableBtn = false;
}

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPledge.value);
    if (this.AddPledge.invalid) {
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
    this.userservice.getData(result, "store-pledge-customer", "JSON").subscribe((response: any) => {
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
        this.appService.showAlert("Pledge", "Sorry!! Something went wrong!", "error");
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
    this.AddPledge = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      pledge_customer_id: ['', Validators.required],
      enrollment_id: [''],
      pledge_date: [this.currentDate, Validators.required],
      pledgeenrollment_id: [''],
      remark: [''],
      created_by: ['']
    });
    
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddPledge.get('tenant_id').setValue(tenant_id);
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
    this.AddPledge.get('created_by').setValue(localStorage.getItem('logged_user_id'));
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
