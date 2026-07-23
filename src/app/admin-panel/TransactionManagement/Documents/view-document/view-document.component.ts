import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  
  selector: 'app-view-document',
  templateUrl: './view-document.component.html',
  styles: []
})

export class ViewDocumentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;

  viewDocument: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  ViewDocumentInfo: ViewData[]=[];
  BranchInfo: ViewData[]=[];
  CustomerInfo: ViewData[]=[];
  GroupInfo: ViewData[]=[];
  CollectedEmpInfo: ViewData[]=[];
  DocumentInfo: any;
  AssetInfo: any;
  hideDocuments: boolean=true;
  hideAssets: boolean=true;
  GuarantorInfo: any;
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
  
  constructor(private pagerService: PagerService, private formBuilder: FormBuilder,private modalService: NgbModal,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.pageSize = 10;
    this.sortType = 'asc';
  }

  get f() {
    return this.viewDocument.controls;
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

  resetFilter() { 
    this.submitted = false;
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.viewDocument.get('tenant_id').setValue('');
    }
    this.viewDocument.get('branch_id').setValue('');
    this.allItems = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
    this.sortedBy = 'sno';
    this.sortType = 'asc';
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
    if (this.viewDocument.invalid) 
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
      "conditions[tenant_id]" : this.viewDocument.get('tenant_id').value,
      "conditions[branch_id]" : this.viewDocument.get('branch_id').value
    }
    this.userservice.getData(cond, "get-documents", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString();
        }
      } catch (err) {
        this.appService.showAlert("Document Details", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Document Details", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Document Details", "Sorry!! Something went wrong!", "error");
      }
    });
  }
 
  ngOnInit() {
    this.viewDocument = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.viewDocument.get('tenant_id').setValue(tenant_id);
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
    /* this.userservice.getData("", "list-documents", "JSON").subscribe((response: any) => {
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
    }); */
  } 

  open(content, options = {}, recordId=0 ) {
    this.hideDocuments = true;
    this.hideAssets = true;
    let cond = {
      "conditions[id]" : recordId 
    }
    this.userservice.getData(cond, "get-documents", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') { 
          this.ViewDocumentInfo = response.data[0];
          this.BranchInfo = response.data[0].branch_info;
          this.CustomerInfo = response.data[0].customer_info;
          this.GuarantorInfo = response.data[0].guarantor_info;
          this.GroupInfo = response.data[0].group_name;
          this.CollectedEmpInfo = response.data[0].collected_emp_info;
          this.DocumentInfo = response.data[0].document_info;
          this.AssetInfo = response.data[0].asset_info;
          if(response.data[0].document_info.length>0) {
            this.hideDocuments = false;
          }
          if(response.data[0].asset_info.length>0) {
            this.hideAssets = false;
          }
        }
      } catch (err) {
        this.appService.showAlert("Document Details", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Document Details", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Document Details", "Sorry!! Something went wrong!", "error");
      }
    });
    this.modalService.open(content, options).result.then((result) => {}, (reason) => {});
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
  branch_name:string;
  name :string;
  employee_id:string;
  collect_date:string;
  collected_emp_name:string;
  return_document_date:string;
  remark:string;
  is_customer:string;
  is_guarantor:string;
  document_name:string;
  document_no:string;
  document_path:string;
  document_type:string;
  asset_name:string;
  asset_no:string;
  asset_path:string;
  asset_type:string;
  asset_value:string;
}
