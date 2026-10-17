import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-customer-birthday',
  templateUrl: './customer-birthday.component.html',
  styles: []
})
export class CustomerBirthdayComponent implements OnInit {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;

  ViewBank: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Filter';
  branchList: any[] = [];
  isSuperAdmin = false;
  companyList: any[] = [];

  todayList: any[] = [];
  upcomingList: any[] = [];
  completedList: any[] = [];
  TableHidden = true;

  constructor(
    private formBuilder: FormBuilder,
    private appService: AppService,
    private userservice: UserService
  ) {}

  get f() {
    return this.ViewBank.controls;
  }

  companyChg(event) {
    this.setBranchList(event.target.value);
  }

  setBranchList(tenant_id) {
    this.userservice.getData({
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1
    }, 'get-branches', 'POST').subscribe((response: any) => {
      this.branchList = response && response.status === 'Success' ? (response.data || []) : [];
    }, () => { this.branchList = []; });
  }

  resetBank() {
    this.submitted = false;
    const tenant_id = localStorage.getItem('tenant_id');
    if (!tenant_id) {
      this.ViewBank.get('tenant_id').setValue('');
    }
    this.ViewBank.get('branch_id').setValue('');
    this.todayList = [];
    this.upcomingList = [];
    this.completedList = [];
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    if (this.ViewBank.invalid) {
      this.SubmitbuttonName = 'Filter';
      if (this.f.tenant_id.errors) { this.focus1.nativeElement.focus(); }
      else if (this.f.branch_id.errors) { this.focus2.nativeElement.focus(); }
      return;
    }

    const payload = {
      tenant_id: this.ViewBank.get('tenant_id').value,
      branch_id: this.ViewBank.get('branch_id').value
    };

    this.userservice.getData(payload, 'customer-birthday-report', 'JSON').subscribe((response: any) => {
      this.SubmitbuttonName = 'Filter';
      if (response && response.status === 'Success') {
        const data = response.data || {};
        this.todayList = data.today || [];
        this.upcomingList = data.upcoming || [];
        this.completedList = data.completed || [];
        this.TableHidden = false;
      } else {
        this.todayList = [];
        this.upcomingList = [];
        this.completedList = [];
        this.appService.showAlert('Birthday Report', (response && response.msg) || 'Failed', 'error');
      }
    }, () => {
      this.SubmitbuttonName = 'Filter';
      this.appService.showAlert('Birthday Report', 'Unable to load birthday report', 'error');
    });
  }

  ngOnInit() {
    this.ViewBank = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
    });
    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.ViewBank.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        this.companyList = response && response.status === 'Success' ? response.data : [];
      });
    }
    this.TableHidden = false;
  }
}
