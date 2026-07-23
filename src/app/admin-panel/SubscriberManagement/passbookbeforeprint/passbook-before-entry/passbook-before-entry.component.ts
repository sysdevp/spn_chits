import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-passbook-before-entry',
  templateUrl: './passbook-before-entry.component.html',
  styles: []
})
export class PassbookBeforeEntryComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;

  PassbookbeforeEntry: FormGroup;
  disableBtn = true;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  customerList: any[];
  enrollmentList: any[];
  enrollList: any[];

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    
  }

  get f() {
    return this.PassbookbeforeEntry.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.PassbookbeforeEntry.get('branch_id').setValue('');
    this.PassbookbeforeEntry.get('customer_id').setValue('');
    this.branchList = []; 
    this.customerList = [];
    this.disableBtn = true;
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.branchList = response.data; 
          this.groupList = []; 
          this.customerList = [];
          this.enrollmentList = [];
          this.PassbookbeforeEntry.get('customer_id').setValue('');
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event) {
    let branch_id = event.target.value;
    this.disableBtn = true;

    let cond = {
      "conditions[branch_id]" : branch_id,
    }
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data; 
          console.log(this.customerList);
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }

 


  customerChg(event) {
    this.disableBtn = false;
    //this.enrollmentList =  this.enrollList.filter((listing: any) => listing.customer_id == event.target.value);
    
  }

  printIndexClk() {
    let enrolId = this.PassbookbeforeEntry.get('customer_id').value;
    this.router.navigate(['admin-panel/passbook-before-index/'+enrolId]);
  }

  printTransactionClk() {
    let enrolId = this.PassbookbeforeEntry.get('customer_id').value;
    this.router.navigate(['admin-panel/passbook-before-transactions/'+enrolId]);
  }

  printTransactionIndexClk() {
    let enrolId = this.PassbookbeforeEntry.get('customer_id').value;
    this.router.navigate(['admin-panel/passbook-before-transaction-index/'+enrolId]);
  }

  ngOnInit() {
    this.PassbookbeforeEntry = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.PassbookbeforeEntry.get('tenant_id').setValue(tenant_id);
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
