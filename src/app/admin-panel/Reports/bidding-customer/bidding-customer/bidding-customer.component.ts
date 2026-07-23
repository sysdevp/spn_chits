import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';

@Component({
  
  selector: 'app-bidding-customer',
  templateUrl: './bidding-customer.component.html',
  styles: []
})
export class BiddingCustomerComponent implements OnInit {
  
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('myButton') myButton : ElementRef;
  @ViewChild('amount') focus4 : ElementRef;

  CustomerBid: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  hiddentop:boolean = false;
  hiddentoptwo:boolean = false;
  hiddentopthree:boolean = true;
  hiddentopfour:boolean = true;
  hiddentopfive:boolean = true;
  back: boolean = true;
  hidestarted: boolean = true;
  hidestart: boolean = false;
  SubmitbuttonName: string;
  ChequeGroupId: any;
  SubmitChequeButtonName: string;
  disableChequeBtn: boolean = false;
  chequeSubmitted: boolean;
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
  branchList: any[];
  groupList: any;
  lastauctionList: any;
  max__bid_amount: any;
  individualInfo: any;
  allbidindividualInfo: any;
  messageInfo: any;
  logbiddingid: any;
  status: any;
  ticket_no: any;
  logbiddingidt: any;
  logbiddingidg: any;
  amount: any;
  installmentNo: any;
  customer_id: void;
  prizedInfo: any;
  enrolList: any;


  constructor(private pagerService: PagerService, private router: Router,private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.SubmitChequeButtonName = "Submit";
    this.pageSize = 10;
    this.sortType = 'asc';
  }

  get f() {
    return this.CustomerBid.controls;
  }
  get cf() {
    return this.ChequeGroup.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.branchList = [];
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id
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
      "conditions[branch_id]" : event.target.value,
      "conditions[customer_id]" : localStorage.getItem('logged_employee_id')
    }
    this.userservice.getData(cond, "get-customer-enrollments", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.enrolList = response.data;
          console.log(this.enrolList);
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

  resetBank() { 
    this.submitted = false; 
    let tenant_id = localStorage.getItem('tenant_id'); 
    if(!tenant_id) {
      this.CustomerBid.get('tenant_id').setValue('');
    }
    this.CustomerBid.get('branch_id').setValue('');
    this.CustomerBid.get('group_id').setValue('');
    this.allItems = [];
    this.pager = {};
    this.pagedItems = [];
    this.dtableInfo = 'Showing 0 to 0 of 0 entries';
    this.pageSize = 10;
    this.sortedBy = 'sno';
    this.sortType = 'asc';
  }

  viewauctionReport(event, row){
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = false;
    this.hiddentopfour = true;
    this.hiddentopfive = true;
    this.back = false;
    let lastauction_id = row;

    let cond = {
      "id" : lastauction_id
    }
    this.userservice.getData(cond, "getcompleted-groupauction-details", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.lastauctionList = response.data;
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

  logbiddingReport(event, id,group_id,ticket_no){
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = false;
    this.back = false;
    this.logbiddingid = id;
    this.logbiddingidg = group_id;
    this.logbiddingidt = ticket_no;
    
    let cond = {
      "conditions[tenant_id]" : this.CustomerBid.get('tenant_id').value,
      "conditions[branch_id]" : this.CustomerBid.get('branch_id').value,
      "conditions[group_id]" : this.CustomerBid.get('group_id').value,
      "conditions[customer_id]" : localStorage.getItem('logged_user_id'),
      "conditions[ticket_no]" :  this.logbiddingidt,

    }
    window.location.href='http://13.233.231.26/my-chits/pusher/customer_bidding.php?tenant_id='+this.CustomerBid.get('tenant_id').value+'&branch_id='+this.CustomerBid.get('branch_id').value+'&group_id='+this.CustomerBid.get('group_id').value+'&customer_id='+localStorage.getItem('logged_user_id')+'&ticket_no='+this.logbiddingidt;
    //window.location.href='http://localhost/mazechit_project/VCS_Chits/customer_bidding.php?tenant_id='+this.CustomerBid.get('tenant_id').value+'&branch_id='+this.CustomerBid.get('branch_id').value+'&group_id='+this.CustomerBid.get('group_id').value+'&customer_id='+localStorage.getItem('logged_user_id')+'&ticket_no='+this.logbiddingidt;
    /*this.userservice.getData(cond, "get-customer-enrollments-bid", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.prizedInfo = response.data[0].prized_status;
        } else {
          this.appService.showAlert("Customer Bidding", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Customer Bidding", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      
    });*/
  }

  logviewReport(event, group_id , ticket_no, installment_no){
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = false;
    this.hiddentopfive = true;
    this.back = false;
    let logview = group_id;

    let cond1 = {
            "tenant_id" : this.CustomerBid.get('tenant_id').value,
            "branch_id" : this.CustomerBid.get('branch_id').value,
            "group_id" : logview,
            "installment_no" : installment_no,
            "customer_id" : localStorage.getItem('logged_user_id'),
            "ticket_no" : ticket_no
    }
    this.userservice.getData(cond1, "get-online-bid", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.allbidindividualInfo = response.data;
          console.log(this.allbidindividualInfo);
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

  resetallReport(){
    window.location.reload();
    console.log('change');
    this.hiddentop = false;
    this.hiddentoptwo = false;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = true;
    this.back = true;

    //this.myButton.nativeElement = [];
    //this.onSubmit();
  }

  triggerClick(){
    this.hidestarted = false;
    this.hidestart = true;
    let el: HTMLElement = this.myButton.nativeElement as HTMLElement;
    setTimeout(()=> el.click(), 1000);

    let cond = {
      "id" : this.logbiddingid
    }
    this.userservice.getData(cond, "get-maxamount-bid-details", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.max__bid_amount = response.data;
          this.messageInfo = response.data1;
          this.installmentNo = response.data3;
          this.status = response.data4;
          if(this.status != 2){
            console.log('arjuu');
           this.disableChequeBtn = true;
          }
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });

    let cond1 = {
      "tenant_id" : this.CustomerBid.get('tenant_id').value,
      "branch_id" : this.CustomerBid.get('branch_id').value,
      "group_id" : this.logbiddingidg,
      "installment_no" : this.installmentNo,
      "customer_id" : localStorage.getItem('logged_user_id'),
      "ticket_no" : this.logbiddingidt
    }
    this.userservice.getData(cond1, "get-online-bid", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.individualInfo = response.data;
          //this.status = response.data4;
          /*if(this.status != 2){
            console.log('arjuus');
           this.disableChequeBtn = true;
          }*/
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
        const filter = ['group_name','ticket_no','chit_value','status'];
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
    if (this.CustomerBid.invalid) 
    {  
      this.SubmitbuttonName = "Filter";
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
      "tenant_id" : this.CustomerBid.get('tenant_id').value,
      "branch_id" : this.CustomerBid.get('branch_id').value,
      "group_id" : this.CustomerBid.get('group_id').value,
      "customer_id" : localStorage.getItem('logged_user_id')
    }
    
    this.userservice.getData(cond, "get-group-bid", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data;
          this.allItems = response.data;
          this.setPage(1);
          this.setPagerString();
        } else {
          this.appService.showAlert("Customer Bidding", response.msg, "error");
        }
      } catch (err) { 
        this.appService.showAlert("Customer Bidding", 'Sorry!! Something went wrong!', "error");
      }
    }, (err: any) => { 
      this.disableBtn = false;
      this.SubmitbuttonName = "Filter";
      if (err.status == 401) {
        this.appService.showAlert("Customer Bidding", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer Bidding", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  onChequeSubmit() {
    this.SubmitChequeButtonName = "Loading...";
    this.chequeSubmitted = true;
   
    const result = Object.assign({}, this.ChequeGroup.value);
    this.amount = result.amount;
    if (this.ChequeGroup.invalid) {
      this.SubmitChequeButtonName = "Submit";
      this.disableChequeBtn = false;
      if(this.cf.amount.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    let cond = {
      "tenant_id" : this.CustomerBid.get('tenant_id').value,
      "branch_id" : this.CustomerBid.get('branch_id').value,
      "group_id" : this.CustomerBid.get('group_id').value,
      //"ticket_no" : this.logbiddingidt,
      "installment_no" : this.installmentNo,
      "customer_id" : localStorage.getItem('logged_user_id'),
      "amount" : this.amount
    }
    this.userservice.getData(cond, "store-online-bid", "JSON").subscribe((response: any) => {
      this.SubmitChequeButtonName = "Submit";
      try {
        if (response.status == 'Success') {
          
          let cond1 = {
            "group_id" : this.ChequeGroup.get('group_id').value,
            "installment_no" : 1,
            "customer_id" : localStorage.getItem('logged_user_id'),
            "ticket_no" : this.logbiddingidt
          }
          this.userservice.getData(cond1, "get-online-bid", "POST").subscribe((response: any) => {
            try {
              if (response.status == 'Success') {
                this.individualInfo = response.data;
                setTimeout(() => {
                  this.TableHidden = false;
                },0);
              }
            } catch (err) {}
          }, (err: any) => {
            if (err.status == 401) {}
            else { }
          });

          this.CustomerBid.get('amount').setValue('');
          setTimeout(() => {
           
          }, 300);
        } else {
          this.appService.showAlert("", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.disableChequeBtn = false;
        if (err.status == 401) {
         this.appService.showAlert("", 'Sorry!! Something went wrong!', "error");
        }
      }
    }, (err: any) => {
      this.disableChequeBtn = false;
      this.SubmitChequeButtonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.CustomerBid = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required]
      
    });

    this.ChequeGroup = this.formBuilder.group({
      amount: ['', Validators.required]
    });
    let user_id = localStorage.getItem('logged_user_id');
    let logged_employee_id = localStorage.getItem('logged_employee_id');

    console.log("logged id",user_id);
    console.log(" logged_employee_id",logged_employee_id);
    let tenant_id = localStorage.getItem('tenant_id');

    if(tenant_id) {
      this.CustomerBid.get('tenant_id').setValue(tenant_id);
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
}
