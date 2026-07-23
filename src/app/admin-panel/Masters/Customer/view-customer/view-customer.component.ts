import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  
  selector: 'app-view-customer',
  templateUrl: './view-customer.component.html',
  styles: []
})

export class ViewCustomerComponent implements OnInit {
  [x: string]: any;
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  viewCustomer: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewCustomerInfo: ViewData[]=[];
  StateInfo: ViewData[]=[];
  DistrictInfo: ViewData[]=[];
  CityInfo: ViewData[]=[];
  CommunicationStateInfo: ViewData[]=[];
  CommunicationDistrictInfo: ViewData[]=[];
  CommunicationCityInfo: ViewData[]=[];
  BranchInfo: ViewData[]=[];
  RelationshipInfo: ViewData[]=[];
  CustomerInfo: ViewData[]=[];
  leadmanagementInfo: ViewData[]=[];
  branchList: any;
  isSuperAdmin:boolean;
  companyList: any[];
  submitted: boolean;
  disableBtn: boolean;
  SubmitbuttonName: string;
  public allItems: any[];
  pager: any = {};
  pagedItems: any[];
  dtableInfo: any;
  pageSize: any;
  public searchText: any;
  sortedBy: any;
  sortType: any;
  PermanentStateInfo: any;
  PermanentDistrictInfo: any;
  PermanentCityInfo: any;
  customerChits: any[] = [];
  customerChitsLoading = false;

  constructor(private pagerService: PagerService, private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.pageSize = 10;
    this.sortType = 'asc';
  }

  get f() {
    return this.viewCustomer.controls;
  }
  
  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    const cond: any = {
      "conditions[tenant_id]" : tenant_id,
      "log_id" : localStorage.getItem('logged_employee_id')
    };
    const loggedUserId = localStorage.getItem('logged_user_id');
    if (loggedUserId) {
      cond.user_id = loggedUserId;
    }
    this.userservice.getData(cond, "get-branches", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.branchList = response.data || [];
          if (this.branchList.length === 1 && this.viewCustomer) {
            this.viewCustomer.get('branch_id').setValue(String(this.branchList[0].id));
          }
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event) 
  {
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
  }

  resetCustomer() { 
    this.submitted = false;
    //this.viewCustomer.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.viewCustomer.get('tenant_id').setValue('');
    }
    this.viewCustomer.get('branch_id').setValue('');
    this.allItems = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
    this.sortedBy = 'sno';
    this.sortType = 'asc';
    /* this.userservice.getData('', "list-customers", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        } else {
          this.appService.showAlert("Custome", response.msg, "error");
        }
      } catch (err) {
        this.appService.showAlert("Customer", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
    }); */
  }

  setPagerString() {
    this.dtableInfo = '';
    if((this.allItems.length)>parseInt(this.pageSize)) {
      this.dtableInfo = 'Showing 1 to ' + this.pageSize + ' of ' + this.allItems.length + ' entries';
    }
    else if((this.allItems.length)>0) {
      this.dtableInfo = 'Showing 1 to ' + this.allItems.length + ' of ' + this.allItems.length + ' entries';
    }
    else {
      this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    }
  }
  
  setPage(page: number) {
    this.pager = this.pagerService.getPager(this.allItems.length, page,this.pageSize);
    this.pagedItems = this.allItems.slice(this.pager.startIndex, this.pager.endIndex + 1);
  }

  pageSizeChg(event) {
    this.pageSize = event.target.value;
    this.setPage(this.pager.currentPage);
  }

  filterChg(filterText) {  
    if (filterText!='') {
      this.allItems =  this.dataList.filter(item => {
        const filter = ['branch_name','customer_code','name','mobile_no'];
        return filter.some(
          key => item[key].toLowerCase().indexOf(filterText.toLowerCase()) !== -1
        )
      }); 
    }
    else {
      this.allItems =  this.dataList;
    }
    this.setPage(1);
    setTimeout(() => {
      this.setPagerString();
    },3000);
  }

  sortBy(fldName) {  
    if((this.sortedBy) == fldName) {
      if((this.sortType) == 'asc') {
        this.sortType = 'desc';
      }
      else {
        this.sortType = 'asc';
      }
    }
    else {
      this.sortedBy = fldName;
      this.sortType = 'asc';
    }
    this.sortTable();
  }

  sortTable() {
    let data = this.allItems.slice();
    if ((this.sortedBy) && (this.sortType) !== '') {
      //const isAsc = (this.sortType === 'asc');
      if(this.sortType === 'asc') {
        this.ascSort(this.sortedBy);
      }
      else {
        this.descSort(this.sortedBy);
      }
    }
    this.setPage(1) 
  }

  private ascSort(fldName) {
    if(this.allItems != null){
      this.allItems = this.allItems.sort(function(a, b){
        if (a[fldName] === '' || a[fldName] === null || typeof a[fldName] === 'undefined') {
          return 1;
        }
        if (b[fldName] === '' || b[fldName] === null || typeof b[fldName] === 'undefined') {
          return -1;
        }
        if(a[fldName] < b[fldName]){
          return -1;
        }
        else if( a[fldName] > b[fldName]){
          return 1;
        }
        else{
          return 0;
        }
      });
    }
  }

  private descSort(fldName) {
    if(this.allItems != null){
      this.allItems = this.allItems.sort(function(a, b){
        if (a[fldName] === '' || a[fldName] === null || typeof a[fldName] === 'undefined') {
          return -1;
        }
        if (b[fldName] === '' || b[fldName] === null || typeof b[fldName] === 'undefined') {
          return 1;
        }
        if(a[fldName] < b[fldName]){
          return 1;
        }
        else if( a[fldName] > b[fldName]){
          return -1;
        }
        else{
          return 0;
        }
      });
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    if (this.viewCustomer.invalid) 
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

    let cond = {
      "conditions[tenant_id]" : this.viewCustomer.get('tenant_id').value,
      "conditions[branch_id]" : this.viewCustomer.get('branch_id').value
    }
    
    this.userservice.getData(cond, "get-customers-view", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString();
        }
      } catch (err) {
        this.appService.showAlert("Customer", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.viewCustomer = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.viewCustomer.get('tenant_id').setValue(tenant_id);
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
    this.TableHidden = false;
    /*this.userservice.getData("", "list-customers", "JSON").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });*/
  } 

  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-customer-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Customer", response.msg , "success");
        }
        else {
          this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else {}
    });
  }

  loadCustomerChits(customerId: number | string) {
    this.customerChits = [];
    this.customerChitsLoading = true;
    const cond = {
      "conditions[customer_id]": customerId,
      "conditions[chit_type]": 3,
    };
    this.userservice
      .getData(cond, "get-commitment-enrolled-customers", "POST")
      .subscribe(
        (response: any) => {
          this.customerChitsLoading = false;
          this.customerChits =
            response.status == "Success" && response.data
              ? response.data
              : [];
        },
        () => {
          this.customerChitsLoading = false;
          this.customerChits = [];
        },
      );
  }

  getChitSchemeLabel(chit: any): string {
    if (!chit || !chit.scheme_info) {
      return "-";
    }
    return (
      chit.scheme_info.scheme_format ||
      chit.scheme_info.chit_value ||
      "-"
    );
  }

  getChitSlabLabel(chit: any): string {
    if (!chit || !chit.slab_info) {
      return "-";
    }
    return chit.slab_info.slab_name || "-";
  }

  getChitRfAmount(chit: any): string {
    if (!chit) {
      return "-";
    }
    const amt =
      chit.advance_amount != null && chit.advance_amount !== ""
        ? chit.advance_amount
        : chit.needed_amount;
    return amt != null && amt !== "" ? amt : "-";
  }

  getChitStatusLabel(chit: any): string {
    if (!chit) {
      return "-";
    }
    return String(chit.eligibility_status) === "0"
      ? "Pending Approval"
      : "Approved";
  }

  getChitStatusClass(chit: any): string {
    if (!chit) {
      return "badge-secondary";
    }
    return chit.eligibility_status == 0 || chit.eligibility_status == "0"
      ? "badge-warning"
      : "badge-success";
  }

  open(event, content, options = {}, recordId=0 ) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    this.customerChits = [];
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success' && Array.isArray(response.data) && response.data.length > 0) { 
          const selectedCustomer = response.data.find((customer: any) => customer.id == recordId) || response.data[0];
          this.ViewCustomerInfo = selectedCustomer;
          this.StateInfo = selectedCustomer.state_info || {};
          this.DistrictInfo = selectedCustomer.district_info || {};
          this.CityInfo = selectedCustomer.city_info || {};
          this.CommunicationStateInfo = selectedCustomer.communication_state_info || {};
          this.CommunicationDistrictInfo = selectedCustomer.communication_district_info || {};
          this.CommunicationCityInfo = selectedCustomer.communication_city_info || {};
          this.PermanentStateInfo = selectedCustomer.permanent_state_info || {};
          this.PermanentDistrictInfo = selectedCustomer.permanent_district_info || {};
          this.PermanentCityInfo = selectedCustomer.permanent_city_info || {};
          this.BranchInfo = selectedCustomer.branch_info || {};
          this.RelationshipInfo = selectedCustomer.relationship_info || {};
          this.CustomerInfo = selectedCustomer.customer_belongs_to_info || {};
          this.leadmanagementInfo = selectedCustomer.lead_management_info || {};
          this.FamilyInfo = selectedCustomer.family_det || {};
          this.loadCustomerChits(recordId);
          this.modalService.open(content, options).result.then((result) => {}, (reason) => {});
        }
        else{
          this.customerChits = [];
          this.ViewCustomerInfo = [];
          this.StateInfo = [];
          this.DistrictInfo = [];
          this.CityInfo = [];
          this.CommunicationStateInfo = [];
          this.CommunicationDistrictInfo = [];
          this.CommunicationCityInfo =[];
          this.BranchInfo = [];
          this.FamilyInfo = [];
          this.appService.showAlert("Customer", "No customer details found", "error");
        }
      } catch (err) {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer", "Sorry!! Something went wrong!", "error");
      }
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

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  } 
}

export interface ViewData 
{
  sno:string;
  barnch_name: string;
  lead_management_id: string;
  customer_code: string;
  name:string;
  photo:string;
  dob:string;
  gender: string;
  mobile_no: string;
  phone_no: string;
  email_id:string;
  adhar_no:string;
  pan_no:string;
  license_no: string;
  ration_card_no: string;
  doj: string;
  father_name: string;
  spouse_name:string;
  door_no:string;
  buildling_name:string;
  address_line_1: string;
  address_line_2: string;
  landmark:string;
  state_name:string;
  district_name: string;
  city_name: string;
  pincode: string;
  communication_door_no: string;
  communication_building_name:string;
  communication_address_line_1:string;
  communication_address_line_2:string;
  communication_land_mark: string;
  communication_state_name: string;
  communication_district_name: string;
  communication_city_name:string;
  communication_pincode:string;
  refered_by: string;
  pf_no: string;
  esi_no: string;
  retirement: string;
  occupation:string;
  source_of_funds:string;
  income:string;
  relationship_id: string;
  customer_belongs_to:string;
  customer_bonus: string;
  customer_penalty_interest: string;
  customer_interest:string;
  customer_divident: string;
  sms: string;
  is_customer:string;
  is_guarantor:string;
}


