import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  
  selector: 'app-view-guarantor',
  templateUrl: './view-guarantor.component.html',
  styles: []
})

export class ViewGuarantorComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  viewGaurantor: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  SubmitbuttonName: string;
  ViewGuarantorInfo: ViewData[]=[];
  StateInfo: ViewData[]=[];
  DistrictInfo: ViewData[]=[];
  CityInfo: ViewData[]=[];
  CommunicationStateInfo: ViewData[]=[];
  CommunicationDistrictInfo: ViewData[]=[];
  CommunicationCityInfo: ViewData[]=[];
  BranchInfo: ViewData[]=[];
  RelationshipInfo: ViewData[]=[];
  CustomerInfo: ViewData[]=[];
  guarantorInfo: ViewData[]=[];
  EnrollmentInfo: ViewData[]=[];
  branchList: any;
  isSuperAdmin:boolean;
  companyList: any[];
  submitted: boolean;
  disableBtn: boolean;
  public allItems: any[];
  pager: any = {};
  pagedItems: any[];
  dtableInfo: any;
  pageSize: any;
  sortedBy: any;
  sortType: any;

  constructor(private pagerService: PagerService, private router: Router,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService, private formBuilder: FormBuilder)
  {
    this.SubmitbuttonName = "Filter";
    this.pageSize = 10;
    this.sortType = 'asc';
  }

  get f() {
    return this.viewGaurantor.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.branchList = [];
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
    let cond = {
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-banks", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.setData(response.data); 
          this.router.navigate(['/admin-panel/view-bank-details']); 
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

  resetGaurantor() { 
    this.submitted = false;
    //this.viewGaurantor.get('tenant_id').setValue('');
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.viewGaurantor.get('tenant_id').setValue('');
    }
    this.viewGaurantor.get('branch_id').setValue('');
    this.allItems = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
    this.sortedBy = 'sno';
    this.sortType = 'asc';
  }

  open(content, options = {}, recordId=0 ) {
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-guarantors", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewGuarantorInfo = response.data[0];
          this.guarantorInfo = response.data[0].guarantor_det;
          this.StateInfo = response.data[0].state_info;
          this.DistrictInfo = response.data[0].district_info;
          this.CityInfo = response.data[0].city_info;
          this.CommunicationStateInfo = response.data[0].communication_state_info;
          this.CommunicationDistrictInfo = response.data[0].communication_district_info;
          this.CommunicationCityInfo = response.data[0].communication_city_info;
          this.BranchInfo = response.data[0].branch_info;
          this.RelationshipInfo=response.data[0].relationship_info;
          this.CustomerInfo=response.data[0].customer_info;
          this.EnrollmentInfo=response.data[0].enrollment_info;
        }
        else{
          this.ViewGuarantorInfo = [];
          this.StateInfo = [];
          this.DistrictInfo = [];
          this.CityInfo = [];
          this.CommunicationStateInfo = [];
          this.CommunicationDistrictInfo = [];
          this.CommunicationCityInfo =[];
          this.BranchInfo = [];
        }
      } catch (err) {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Guarantor", "Sorry!! Something went wrong!", "error");
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

  setData(data) {
    this.dataList = data;
    $(function() {
      $('#dtable').DataTable();
    })
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
        const filter = ['branch_name','guarantor_name','customer_name'];
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
    if (this.viewGaurantor.invalid) 
    { 
      this.SubmitbuttonName = "Filter";
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
      "conditions[tenant_id]" : this.viewGaurantor.get('tenant_id').value,
      "conditions[branch_id]" : this.viewGaurantor.get('branch_id').value
    }
    
    this.userservice.getData(cond, "get-guarantors", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString();
        } else {
          this.appService.showAlert("Gaurantor", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Gaurantor", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Gaurantor", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Gaurantor", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.viewGaurantor = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.viewGaurantor.get('tenant_id').setValue(tenant_id);
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
  } 
} 

export interface ViewData 
{
  sno:string;
  branch_name: any;
  customer_id: string;
  enrollment_id: string;
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
}

