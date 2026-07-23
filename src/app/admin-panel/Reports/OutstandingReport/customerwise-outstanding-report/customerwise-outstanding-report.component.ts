import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-customerwise-outstanding-report',
  templateUrl: './customerwise-outstanding-report.component.html',
  styles: []
})
export class CustomerwiseOutstandingReportComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  customerOutstanding: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  customerList: any[];

  constructor(private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.customerOutstanding.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    let cond = {
      "conditions[tenant_id]" : tenant_id
    }
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id
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
    let cond = {
       "conditions[tenant_id]" : this.customerOutstanding.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value
    }
   
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }

  resetCustomerwiseReport() {
    this.submitted = false;
    let emptyData = [];
    this.setData(emptyData);
    //this.customerOutstanding.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.customerOutstanding.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.customerOutstanding.get('branch_id').setValue('');
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.customerOutstanding.value);
    if (this.customerOutstanding.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    } 
    
    this.userservice.getData(result, "customer-wise-outstanding-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.setData(response.data);
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("customer Outstanding Report", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("customer Outstanding Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("customer Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("customer Outstanding Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.customerOutstanding = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id:[''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.customerOutstanding.get('tenant_id').setValue(tenant_id);
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
  } 
}

