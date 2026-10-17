import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';
import {ExcelService} from '../../../../Services/excel.service';
import  XLSX from 'xlsx';
import  jspdf from 'jspdf';  
import html2canvas from 'html2canvas';

@Component({
  
  selector: 'app-suit-file',
  templateUrl: './suit-file.component.html',
  styles: []
})
export class SuitFileComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;

  auctionReport: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  TableshowHidden:boolean = true;
  Tableexistinggroup:boolean =true;
  Tableclosedgroup:boolean =true;
  nodataFoundMessage: boolean=true;
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
  tenantid: any;
  branchid: any;
  groupList: any;
  double_auction: any;
  incomplete_auction: any;
  auction_completed: any;
  time_period: any;
  chit_value: any;
  monthly_subscription: any;
  viewAuctionInfo: any;
  branchInfo: any;
  groupInfo: any;
  enrollmentInfo: any;
  customerInfo: any;

  constructor(private excelService:ExcelService,private pagerService: PagerService, private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.pageSize = 50;
    this.sortType = 'asc';
  }

  get f() {
    return this.auctionReport.controls;
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
    let cond = {
      "conditions[tenant_id]" : this.auctionReport.get('tenant_id').value,
      "conditions[branch_id]" : event.target.value,
     
    }
      this.tenantid=this.auctionReport.get('tenant_id').value,
      this.branchid=event.target.setValue

        this.userservice.getData(cond, "get-schemes", "POST").subscribe((response: any) => {
          try { 
            if (response.status == 'Success') {
              this.groupList = response.data;
            } else {
              this.groupList = [];
            }
          }  catch (err) { }
        }, (err: any) => { });
  }

  resetCustomer() { 
    this.TableshowHidden = true;
    let tenant_id = localStorage.getItem('tenant_id');
    if(!tenant_id) {
      this.auctionReport.get('tenant_id').setValue('');
    }
    this.auctionReport.get('branch_id').setValue('');
    this.auctionReport.get('group_id').setValue('');
    if (this.auctionReport.get('scheme_id')) {
      this.auctionReport.get('scheme_id').setValue('');
    }
    this.allItems = [];
    this.submitted = false;
    //this.viewCustomer.get('tenant_id').setValue('');
    
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 50;
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
        const filter = ['group_name','winner_name'];
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

  exportAsXLSX() {
    /* table id is passed over here */   
    let element = document.getElementById('mygrid'); 
    
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(document.getElementById('mygrid'));
    
    //console.log((ws['A1']));
/* here 12 is your column number (n-1) */
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpaid.xls`;
    /* save to file */
    XLSX.writeFile(wb, "SuitFileReport.csv");
  }

  exportAsXLSX1() {
    /* table id is passed over here */   
    let element = document.getElementById('mygrid1'); 
    
    const ws: XLSX.WorkSheet =XLSX.utils.table_to_sheet(document.getElementById('mygrid'));
    
    //console.log((ws['A1']));
/* here 12 is your column number (n-1) */
    /* generate workbook and add the worksheet */
    const wb: XLSX.WorkBook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
    //let exportFileName = `Groupabstractpaid.xls`;
    /* save to file */
    XLSX.writeFile(wb, "SuitFileReport.csv");
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.TableshowHidden = false;
    const result = Object.assign({}, this.auctionReport.value);
    if (this.auctionReport.invalid) 
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
    this.userservice.getData(result, "get-suit-filling-report", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if(response.data != 0) {
          this.nodataFoundMessage=true;
        if (response.status == 'Success') {
          this.dataList = response.data;
          if((response.data.length)>0) {
            this.nodataFoundMessage=true;
            }
            else {
             this.nodataFoundMessage=true;
            }
          if(response.data[0].group_status == 0){
            this.Tableexistinggroup = false;
            this.Tableclosedgroup = true
          }
          this.dataList = response.data;
          this.Tableexistinggroup = true;
          this.Tableclosedgroup = false;
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString();
        }
      }
        else{
          this.nodataFoundMessage=false;
          this.Tableexistinggroup = true;
          this.Tableclosedgroup = true;
          this.TableshowHidden = true;
        }
        
      } catch (err) {
        this.appService.showAlert("Suit File", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Suit File", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Suit File", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.auctionReport = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: [''],
      scheme_id: [''],
      //customer_id: [''],
      group_status: ['0'],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.auctionReport.get('tenant_id').setValue(tenant_id);
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
    this.userservice.getData("", "list-groups", "JSON").subscribe((response: any) => {
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
}


