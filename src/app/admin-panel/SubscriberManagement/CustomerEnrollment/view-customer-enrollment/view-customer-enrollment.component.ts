import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  
  selector: 'app-view-customer-enrollment',
  templateUrl: './view-customer-enrollment.component.html',
  styles: []
})

export class ViewCustomerEnrollmentComponent implements OnInit {

  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;

  viewEnrol: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  viewEnrollmentInfo: ViewData[]=[];
  branchInfo: ViewData[]=[];
  customerInfo: ViewData[]=[];
  groupInfo: ViewData[]=[];
  collectionAgentInfo: ViewData[]=[];
  introducerInfo: ViewData[]=[];
  
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
  tickettype: any;
  branchId: any;
  groupList: any;

  constructor(private pagerService: PagerService,private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.pageSize = 10;
    this.sortType = 'asc';
  }
  get f() {
    return this.viewEnrol.controls;
  }
  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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
        } else {
          this.branchList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  branchChg(event) 
  {
    this.branchId = event.target.value;
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.groupList = response.data;
          setTimeout(() => {
            this.TableHidden = true;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });
  }

  resetEnrollForm() { 
    this.submitted = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.viewEnrol.get('tenant_id').setValue('');
    }
    this.viewEnrol.get('branch_id').setValue('');
    this.viewEnrol.get('group_id').setValue('');
    this.allItems = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
    this.sortedBy = 'sno';
    this.sortType = 'asc';
  }

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
  }
 
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    if (this.viewEnrol.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    } 

    let cond = {
      "conditions[tenant_id]" : this.viewEnrol.get('tenant_id').value,
      "conditions[branch_id]" : this.viewEnrol.get('branch_id').value,
      "conditions[group_id]" : this.viewEnrol.get('group_id').value
    }
    
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString(); } else {
            this.appService.showAlert("Customer Enrollments", response.msg, "error");
          }
      } catch (err) {
        this.appService.showAlert("Customer Enrollments", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Customer Enrollments", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer Enrollments", "Sorry!! Something went wrong!", "error");
      }
    });
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
        const filter = ['branch_name','group_name','name','mobile_no'];
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

  ngOnInit() {
    this.viewEnrol = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.viewEnrol.get('tenant_id').setValue(tenant_id);
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
    /* this.userservice.getData("", "list-customer-enrollments", "JSON").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          setTimeout(() => {
            this.TableHidden = true;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    }); */
  } 

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.viewEnrollmentInfo = response.data[0];
          this.tickettype = response.data[0].ticket_type_percentage;
          this.branchInfo = response.data[0].branch_info;
          this.customerInfo = response.data[0].customer_info;
          this.groupInfo = response.data[0].group_info;
          this.collectionAgentInfo = response.data[0].collection_agent_info;
          this.introducerInfo = response.data[0].introducer_info;
        }
        else {
          this.viewEnrollmentInfo = [];
          this.branchInfo = [];
          this.customerInfo = [];
          this.groupInfo = [];
          this.collectionAgentInfo = [];
          this.introducerInfo = [];
        }
      } catch (err) {
        this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
      }
    });
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

  
  changeStatus(recordId,statusId) { 
    let data = {
      'id' : recordId
    }
    this.userservice.getData(data, "update-customer-enrollment-status", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.dataList.filter((listing: any) => {
            if(listing.id == recordId)
            {
              listing.status = statusId;
            }
          });
          this.appService.showAlert("Customer Enrollment", response.msg , "success");
        }
        else {
          this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else {}
    });
  }
}

export interface ViewData 
{
  sno:string;
  branch_name: string;
  customer_name: string;
  group_name: string;
  ticket_no:string;
  payment_type:string;
  collection_area:string;
  collection_pin: string;
  collection_agent_name: string;
  agent_type: string;
  introducer_name:string;
  introducer:string;
  date_introduced:string;
  customer_type: string;
  bonus_percentage:string;
  penalty_percentage: string;
  interest_percentage: string;
  dividend_cancel: string;
  ticket_type:string;
  enrolment_type:string;
  remark:string;
}
