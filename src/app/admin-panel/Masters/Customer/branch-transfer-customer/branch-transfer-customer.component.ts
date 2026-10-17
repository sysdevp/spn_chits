import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { AppService } from '../../../../app.service';

@Component({
  selector: 'app-branch-transfer-customer',
  templateUrl: './branch-transfer-customer.component.html',
  styles: []
})
export class BranchTransferCustomerComponent implements OnInit {
  @ViewChild('customer_id') focusCustomer: ElementRef;
  TransferForm: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName = 'Transfer';
  branchList: any[] = [];
  customerList: any[] = [];
  tenant_id: any;

  constructor(
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private appService: AppService,
    private router: Router
  ) {}

  get f() {
    return this.TransferForm.controls;
  }

  ngOnInit() {
    this.tenant_id = localStorage.getItem('tenant_id');
    this.TransferForm = this.formBuilder.group({
      tenant_id: [this.tenant_id, Validators.required],
      customer_id: ['', Validators.required],
      from_branch_name: [{ value: '', disabled: true }],
      from_branch_id: [''],
      to_branch_id: ['', Validators.required],
      remarks: ['', [Validators.required, Validators.minLength(3)]],
      transfer_date: [''],
      transferred_by: [localStorage.getItem('logged_user_id')]
    });
    this.loadBranches();
    this.loadCustomers();
  }

  loadBranches() {
    const cond = {
      'conditions[tenant_id]': this.tenant_id,
      'conditions[status]': 1
    };
    this.userservice.getData(cond, 'list-branches', 'POST').subscribe((response: any) => {
      this.branchList = response.status === 'Success' ? response.data : [];
    });
  }

  loadCustomers() {
    const cond = {
      'conditions[tenant_id]': this.tenant_id,
      'conditions[status]': 1
    };
    this.userservice.getData(cond, 'get-customers', 'POST').subscribe((response: any) => {
      this.customerList = response.status === 'Success' ? response.data : [];
    });
  }

  customerChg(event) {
    const id = event.target.value;
    const cust = (this.customerList || []).find((c) => String(c.id) === String(id));
    if (cust) {
      const branchInfo = cust.branch_info || {};
      this.TransferForm.patchValue({
        from_branch_id: cust.branch_id,
        from_branch_name: cust.branch_name || branchInfo.branch_name || ''
      });
    }
  }

  onSubmit() {
    this.submitted = true;
    if (this.TransferForm.invalid) {
      return;
    }
    const raw = this.TransferForm.getRawValue();
    if (String(raw.from_branch_id) === String(raw.to_branch_id)) {
      this.appService.showAlert('Branch Transfer', 'Select a different target branch', 'error');
      return;
    }
    this.disableBtn = true;
    this.SubmitbuttonName = 'Saving...';
    const payload: any = {
      tenant_id: raw.tenant_id,
      customer_id: raw.customer_id,
      to_branch_id: raw.to_branch_id,
      remarks: raw.remarks,
      transferred_by: raw.transferred_by,
      created_by: raw.transferred_by
    };
    if (raw.transfer_date) {
      payload.transfer_date = raw.transfer_date;
    }
    this.userservice.getData(payload, 'store-customer-branch-transfer', 'JSON').subscribe(
      (response: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = 'Transfer';
        if (response.status === 'Success') {
          this.appService.showAlert('Branch Transfer', response.msg, 'success');
          setTimeout(() => {
            this.router.navigate(['/admin-panel/branch-transfer-customer-report']);
          }, 1500);
        } else {
          this.appService.showAlert('Branch Transfer', response.msg || 'Transfer failed', 'error');
        }
      },
      () => {
        this.disableBtn = false;
        this.SubmitbuttonName = 'Transfer';
        this.appService.showAlert('Branch Transfer', 'Unable to transfer customer', 'error');
      }
    );
  }
}
