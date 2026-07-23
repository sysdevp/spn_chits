import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-passbook-entry',
  templateUrl: './passbook-entry.component.html',
  styles: []
})

export class PassbookEntryComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('customer_id') focus4:ElementRef;
  @ViewChild('enrollment_id') focus5:ElementRef;

  PassbookEntry: FormGroup;
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
    return this.PassbookEntry.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.PassbookEntry.get('branch_id').setValue('');
    this.PassbookEntry.get('group_id').setValue('');
    this.PassbookEntry.get('customer_id').setValue('');
    this.PassbookEntry.get('enrollment_id').setValue('');
    this.branchList = []; 
    this.groupList = []; 
    this.customerList = [];
    this.enrollmentList = [];
    this.disableBtn = true;
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.branchList = response.data; 
          this.groupList = []; 
          this.customerList = [];
          this.enrollmentList = [];
          this.PassbookEntry.get('group_id').setValue('');
          this.PassbookEntry.get('customer_id').setValue('');
          this.PassbookEntry.get('enrollment_id').setValue('');
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event) {
    let branch_id = event.target.value;
    this.setGroupList(branch_id);
    this.disableBtn = true;
  }

  setGroupList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.groupList = response.data; 
          this.customerList = [];
          this.enrollmentList = [];
          this.PassbookEntry.get('customer_id').setValue('');
          this.PassbookEntry.get('enrollment_id').setValue('');
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }

  groupChg(event) {
    let group_id = event.target.value;
    this.setCustomerList(group_id);
    this.disableBtn = true;
  }

  setCustomerList(group_id) { 
    let cond = {
      "conditions[branch_id]" : this.PassbookEntry.get('branch_id').value,
      "conditions[group_id]" : group_id
    }
    this.userservice.getData(cond, "get-enrolled-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.enrollList = response.data; 
          this.customerList = response.unique_list;
          this.PassbookEntry.get('enrollment_id').setValue('');
        } else {
          this.customerList = [];
          this.enrollList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }

  customerChg(event) {
    this.enrollmentList =  this.enrollList.filter((listing: any) => listing.customer_id == event.target.value);
    this.disableBtn = true;
  }

  enrollmentChg() {
    this.disableBtn = false;
  }

  printIndexClk() {
    let enrolId = this.PassbookEntry.get('enrollment_id').value;
    this.router.navigate(['admin-panel/print-passbook-index/'+enrolId]);
  }

  printTransactionClk() {
    let enrolId = this.PassbookEntry.get('enrollment_id').value;
    this.router.navigate(['admin-panel/passbook-transactions/'+enrolId]);
  }
  printTransactionIndexClk() {
    let enrolId = this.PassbookEntry.get('enrollment_id').value;
    this.router.navigate(['admin-panel/passbook-transaction-index/'+enrolId]);
  }

  ngOnInit() {
    this.PassbookEntry = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      enrollment_id: ['', [Validators.required]],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.PassbookEntry.get('tenant_id').setValue(tenant_id);
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
