import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

declare var $: any;

@Component({
  
  selector: 'app-view-commitment-chit',
  templateUrl: './view-commitment-chit.component.html',
  styles: []
})

export class ViewCommitmentChitComponent implements OnInit {
  ViewCommitmentChit: FormGroup;
  submitted: boolean = false;
  SubmitbuttonName: string = "Filter";

  dataList: any[] = [];
  filteredDataList: any[] = [];
  TableHidden: boolean = true;
  branchList: any[] = [];

  // Modal Data
  ViewCommitmentChitInfo: any = {};
  branchInfo: any = {};
  customerInfo: any = {};
  collectionAgentInfo: any = {};
  collectionAreaInfo: any = {};
  introducerInfo: any = {};
  schemeInfo: any = {};
  slabInfo: any = {};

  constructor(
    private formBuilder: FormBuilder,
    private modalService: NgbModal,
    private appService: AppService,
    private userservice: UserService,
    private spinner: NgxSpinnerService
  ) {}

  ngOnInit() {
    const tenant_id = localStorage.getItem('tenant_id');
    this.ViewCommitmentChit = this.formBuilder.group({
      tenant_id: [tenant_id, Validators.required],
      branch_id: ['', Validators.required],
    });

    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id: string) {
    const cond = { "conditions[tenant_id]": tenant_id };
    this.userservice.getData(cond, "get-branches", "POST").subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data : [];
    });
  }

  get f() {
    return this.ViewCommitmentChit.controls;
  }

  onSubmit() {
  this.submitted = true;
  this.SubmitbuttonName = "Loading...";

  if (this.ViewCommitmentChit.invalid) {
    this.SubmitbuttonName = "Filter";
    return;
  }

  const cond = {
    "tenant_id": this.f.tenant_id.value,
    "branch_id": this.f.branch_id.value,
    "eligibility_status": "1"
  };

  // 🧼 Destroy DataTable if it exists
  if ($.fn.DataTable.isDataTable('#dtable')) {
    $('#dtable').DataTable().destroy();
  }

  this.TableHidden = true;
  this.userservice.getData(cond, "list-commitment-enrolled-customers", "POST").subscribe((res: any) => {
    this.SubmitbuttonName = "Filter";
    if (res.status === 'Success') {
      this.dataList = res.data;
      this.filteredDataList = [...this.dataList];

      // Unhide table and reinitialize after DOM updates
      this.TableHidden = false;

      setTimeout(() => {
        $('#dtable').DataTable({
          paging: true,
          searching: true,
          ordering: true,
          destroy: true
        });
      }, 100); // Let DOM update before re-init
    } else {
      this.dataList = [];
      this.filteredDataList = [];
      this.TableHidden = true;
    }
  }, (err: any) => {
    this.SubmitbuttonName = "Filter";
    this.appService.showAlert("Error", "Something went wrong", "error");
  });
}

  reinitializeDataTable() {
    setTimeout(() => {
      if ($.fn.dataTable.isDataTable('#dtable')) {
        $('#dtable').DataTable().destroy();
      }
      $('#dtable').DataTable({
        searching: true,
        paging: true,
        info: true
      });
    }, 300);
  }

  open(content, options = {}, recordId = 0) {
    const cond = { "conditions[id]": recordId };
    this.userservice.getData(cond, "get-commitment-enrolled-customers", "POST").subscribe((res: any) => {
      if (res.status === 'Success') {
        const data = res.data[0];
        this.ViewCommitmentChitInfo = data;
        this.branchInfo = data.branch_info;
        this.customerInfo = data.customer_info;
        this.collectionAgentInfo = data.collection_agent_info;
        this.collectionAreaInfo = data.collection_area_info;
        this.introducerInfo = data.introducer_info;
        this.schemeInfo = data.scheme_info;
        this.slabInfo = data.slab_info;
      } else {
        this.resetModalData();
      }
    }, () => this.resetModalData());

    this.modalService.open(content, options).result.then(() => {}, () => {});
  }

  resetModalData() {
    this.ViewCommitmentChitInfo = {};
    this.branchInfo = {};
    this.customerInfo = {};
    this.collectionAgentInfo = {};
    this.collectionAreaInfo = {};
    this.introducerInfo = {};
    this.schemeInfo = {};
    this.slabInfo = {};
  }

  onBranchChange(event: any) {
  this.filteredDataList = [];
  this.TableHidden = true;
  if ($.fn.DataTable.isDataTable('#dtable')) {
    $('#dtable').DataTable().destroy();
  }
}
}
