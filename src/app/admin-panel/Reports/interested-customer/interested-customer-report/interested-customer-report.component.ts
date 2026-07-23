import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';

@Component({
  
  selector: 'app-interested-customer-report',
  templateUrl: './interested-customer-report.component.html',
  styles: []
})
export class InterestedCustomerReportComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  InterestedCustomerReport: FormGroup;
  dataList: any;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  isSuperAdmin:boolean;
  submitted: boolean;
  disableBtn: boolean;
  schemeList: any[];

  constructor(private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService) {
    this.SubmitbuttonName = "Filter";
  }

  get f() {
    return this.InterestedCustomerReport.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
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
      "conditions[tenant_id]" : this.InterestedCustomerReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.groupList = response.data;
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    let cond1 = {
      "conditions[id]" : event.target.value
    }
    this.userservice.getData(cond1, "get-schemes", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.schemeList = response.data;
        } else {
          this.schemeList = [];
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

  resetReport() {
    this.submitted = false;
    let emptyData = [];
    this.setData(emptyData);
    //this.InterestedCustomerReport.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.InterestedCustomerReport.get('tenant_id').setValue('');
      this.branchList = [];
    }
    this.InterestedCustomerReport.get('branch_id').setValue('');
    this.InterestedCustomerReport.get('scheme_id').setValue('');
    this.InterestedCustomerReport.get('start_date').setValue('');
    this.InterestedCustomerReport.get('end_date').setValue('');
    this.schemeList=[];
    this.groupList = [];
  }

  onSubmit() {
    console.log("ddd")
    this.SubmitbuttonName = "Loading...";
    this.submitted = true; 
    const result = Object.assign({}, this.InterestedCustomerReport.value);
    if (this.InterestedCustomerReport.invalid) 
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
    this.userservice.getData(result, "interest-customer-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
        
          this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("Interested Customer Report", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Interested Customer  Report", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Interested Customer Report", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Interested Customer Report", "Sorry!! Something went wrong!", "error");
      }
    });
  }


  ngOnInit() {
    this.InterestedCustomerReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      scheme_id: [''],
      start_date: [null],
      end_date: [null]
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.InterestedCustomerReport.get('tenant_id').setValue(tenant_id);
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
