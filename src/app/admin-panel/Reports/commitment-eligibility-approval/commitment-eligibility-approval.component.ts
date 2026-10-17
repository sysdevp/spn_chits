import { Component, OnInit, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';
import { AppService } from '../../../app.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-commitment-eligibility-approval',
  templateUrl: './commitment-eligibility-approval.component.html',
  styles: []
})
export class CommitmentEligibilityApprovalComponent implements OnInit {
  @ViewChild('branch_id') focus2: ElementRef;

  filterForm: FormGroup;
  approveForm: FormGroup;
  submitted = false;
  approveSubmitted = false;
  SubmitbuttonName = 'Filter';
  branchList: any[] = [];
  dataList: any[] = [];
  TableHidden = true;
  selectedEnrollment: any = null;
  activeModal: any = null;
  collectionAreaList: any[] = [];
  introducerList: any[] = [];
  introListShow = false;
  introNameShow = false;
  decimalPattern = '^[0-9]+(.[0-9]{0,2})?$';

  constructor(
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private appService: AppService,
    private modalService: NgbModal
  ) {}

  ngOnInit() {
    const tenant_id = localStorage.getItem('tenant_id');
    this.filterForm = this.formBuilder.group({
      tenant_id: [tenant_id, Validators.required],
      branch_id: [''],
    });
    this.approveForm = this.formBuilder.group({
      enrollment_id: ['', Validators.required],
      date_introduced: [null, Validators.required],
      due_date: [null, Validators.required],
      promised_month: [null],
      payment_type: ['', Validators.required],
      agent_type: ['', Validators.required],
      introducer_id: [''],
      introducer_name: [''],
      collection_area_id: [''],
      collection_pin: ['', Validators.required],
      ar_no: [''],
      referred_detail: [''],
      commission_amount: ['', [Validators.required, Validators.pattern(this.decimalPattern)]],
      confirm_approval: [false, Validators.requiredTrue],
    });
    this.setBranchList(tenant_id);
    this.setEnrollManagerValidators();
  }

  get f() {
    return this.filterForm.controls;
  }
  get af() {
    return this.approveForm.controls;
  }

  setBranchList(tenant_id: string) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      log_id: localStorage.getItem('logged_employee_id'),
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  onSubmit() {
    this.submitted = true;
    this.SubmitbuttonName = 'Loading...';
    if (this.filterForm.invalid) {
      this.SubmitbuttonName = 'Filter';
      return;
    }
    const body: any = Object.assign({}, this.filterForm.value);
    if (!body.branch_id) {
      body.branch_ids = this.branchList.map((b) => b.id);
    }
    this.userservice
      .getData(body, 'list-commitment-eligibility-pending', 'JSON')
      .subscribe(
        (res: any) => {
          this.SubmitbuttonName = 'Filter';
          if (res.status === 'Success') {
            this.dataList = res.data || [];
            this.TableHidden = this.dataList.length === 0;
          } else {
            this.dataList = [];
            this.TableHidden = true;
          }
        },
        () => {
          this.SubmitbuttonName = 'Filter';
          this.appService.showAlert('Error', 'Something went wrong', 'error');
        }
      );
  }

  openApproveModal(row: any, content: any) {
    this.selectedEnrollment = row;
    this.approveSubmitted = false;
    this.approveForm.patchValue({
      enrollment_id: row.id,
      collection_pin: (row.customer_info && row.customer_info.communication_pincode) || row.collection_pin || '',
      confirm_approval: false,
    });
    this.loadCollectionAreas(row.branch_id);
    this.activeModal = this.modalService.open(content, { size: 'lg' });
  }

  closeApproveModal() {
    if (this.activeModal) {
      this.activeModal.close();
      this.activeModal = null;
    }
  }

  loadCollectionAreas(branch_id: number) {
    this.userservice
      .getData({ 'conditions[branch_id]': branch_id }, 'get-collection-areas', 'POST')
      .subscribe((res: any) => {
        this.collectionAreaList = res.status === 'Success' ? res.data : [];
      });
  }

  collectionAreaChg(event) {
    const area = this.collectionAreaList.find((a) => a.id == event.target.value);
    if (area && area.pincode) {
      this.approveForm.get('collection_pin').setValue(area.pincode);
    }
  }

  agentTypeChg(event) {
    const type = event.target.value;
    const tenantId = this.filterForm.get('tenant_id').value;
    if (type == '1' || type == '2' || type == '3') {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId, type == '1' ? 'customer' : type == '3' ? 'businessAgent' : 'employee');
    } else if (type == '4') {
      this.introListShow = false;
      this.introNameShow = true;
    } else {
      this.introListShow = false;
      this.introNameShow = false;
    }
  }

  setIntroducerList(tenantId: string, introducerType: string) {
    if (introducerType === 'customer') {
      this.userservice
        .getData({ 'conditions[tenant_id]': tenantId }, 'get-customers-view', 'POST')
        .subscribe((res: any) => {
          this.introducerList = res.status === 'Success' ? res.data : [];
        });
    } else {
      const cond: any = { 'conditions[tenant_id]': tenantId, 'conditions[employee_type]': introducerType === 'businessAgent' ? 2 : 1 };
      this.userservice.getData(cond, 'get-employees', 'POST').subscribe((res: any) => {
        this.introducerList = res.status === 'Success' ? res.data : [];
      });
    }
  }

  setEnrollManagerValidators() {
    const introducerId = this.approveForm.get('introducer_id');
    const introducerName = this.approveForm.get('introducer_name');
    this.approveForm.get('agent_type').valueChanges.subscribe((agent_type) => {
      if (agent_type == '1' || agent_type == '2' || agent_type == '3') {
        introducerId.setValidators([Validators.required]);
        introducerName.setValidators(null);
      } else if (agent_type == '4') {
        introducerId.setValidators(null);
        introducerName.setValidators([Validators.required]);
      } else {
        introducerId.setValidators(null);
        introducerName.setValidators(null);
      }
      introducerId.updateValueAndValidity();
      introducerName.updateValueAndValidity();
    });
  }

  submitApproval() {
    this.approveSubmitted = true;
    if (this.approveForm.invalid) {
      return;
    }
    const payload = Object.assign({}, this.approveForm.value);
    payload.confirm_approval = payload.confirm_approval ? 1 : 0;
    this.userservice.getData(payload, 'approve-commitment-eligibility', 'JSON').subscribe(
      (res: any) => {
        if (res.status === 'Success') {
          this.closeApproveModal();
          this.appService.showAlert('SPN Approval', res.msg, 'success');
          this.onSubmit();
        } else {
          this.appService.showAlert('SPN Approval', res.msg || 'Approval failed', 'error');
        }
      },
      (err: any) => {
        const msg =
          (err && err.error && err.error.msg) ||
          (err && err.message) ||
          'Something went wrong';
        this.appService.showAlert('SPN Approval', msg, 'error');
      }
    );
  }
}
