import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { UserService } from '../../../../Services/user.service';
import { NgxSpinnerService } from 'ngx-spinner';
import { AppService } from '../../../../app.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { Router } from '@angular/router';
import { PagerService } from '../../../../Services/pager.service';
import swal from 'sweetalert2';

@Component({
  
  selector: 'app-bidding-form',
  templateUrl: './bidding-form.component.html',
  styles: []
})
export class BiddingFormComponent implements OnInit {
  
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('message') focus4:ElementRef;
  @ViewChild('myButton') myButton : ElementRef;

  AdminBid: FormGroup;
  ChequeGroup: FormGroup;
  dataList: any;
  showloader: boolean;
  TableHidden:boolean = true;
  hiddentop:boolean = false;
  hiddentoptwo:boolean = false;
  hiddentopthree:boolean = true;
  hiddentopfour:boolean = true;
  hiddentopfive:boolean = true;
  hiddentopsix:boolean = true;
  hiddentopseven:boolean = true;
  hiddentopeight:boolean = true;
  back: boolean = true;
  ChequeGroupId: any;
  SubmitbuttonName: string;
  SubmitChequeButtonName: string;
  disableChequeBtn: boolean;
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
  message: any;
  max__bid_amount: any;
  individualInfo: any;
  logbiddingid: any;
  installmentNo: any;
  tenant_id_msg: any;
  branch_id_msg: any;
  group_id_msg: any;
  msg: any;
  logbiddinginstallmentno: any;
  viewall: any;
  logall: any;
  msgList: any;
  rowid: any;
  user_id: string;
 

  constructor(private pagerService: PagerService, private router: Router,private modalService: NgbModal, private formBuilder: FormBuilder,private appService: AppService,private userservice: UserService,private spinner: NgxSpinnerService) {
    this.SubmitbuttonName = "Filter";
    this.SubmitChequeButtonName = "Submit";
    this.pageSize = 10;
    this.sortType = 'asc';
  }

  get f() {
    return this.AdminBid.controls;
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
      "conditions[branch_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.groupList = response.data;
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
      this.AdminBid.get('tenant_id').setValue('');
    }
    this.AdminBid.get('branch_id').setValue('');
    this.AdminBid.get('group_id').setValue('');
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
    this.hiddentopsix = true;
    this.hiddentopseven = true;
    this.hiddentopeight = true;
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

  logbiddingReport(event, id, group_id , installment_no){
	  
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = false;
    this.hiddentopsix = true;
    this.hiddentopseven = false;
    this.hiddentopeight = true;
    this.back = false;
    this.rowid = id;
    this.logbiddingid = group_id;
    this.logbiddinginstallmentno = installment_no;

    let cond = {
      "tenant_id" : this.AdminBid.get('tenant_id').value,
      "branch_id" : this.AdminBid.get('branch_id').value,
      "group_id" : this.logbiddingid,
      "installment_no" : this.logbiddinginstallmentno
    }
  window.location.href='http://13.233.231.26/my-chits/pusher/bidding.php?tenant_id='+this.AdminBid.get('tenant_id').value+'&branch_id='+this.AdminBid.get('branch_id').value+'&group_id='+this.logbiddingid+'&installment_no='+this.logbiddinginstallmentno;
  //window.open('http://localhost/mazechit_project/VCS_Chits/pusher/bidding.php?tenant_id='+this.AdminBid.get('tenant_id').value+'&branch_id='+this.AdminBid.get('branch_id').value+'&group_id='+this.logbiddingid+'&installment_no='+this.logbiddinginstallmentno,'','height=auto,width=auto');
    /*this.logall = [];
    this.userservice.getData(cond, "get-log-bid-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.logall = response.data;
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });

    
    
    this.userservice.getData(cond, "get-group-bid-inst", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.msgList = response.data;
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
    });*/
  }

  logbiddingcompletedReport(event, id, group_id , installment_no){
	  
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = false;
    this.hiddentopsix = true;
    this.hiddentopseven = false;
    this.hiddentopeight = true;
    this.back = false;
    this.rowid = id;
    this.logbiddingid = group_id;
    this.logbiddinginstallmentno = installment_no;

    let cond = {
      "tenant_id" : this.AdminBid.get('tenant_id').value,
      "branch_id" : this.AdminBid.get('branch_id').value,
      "group_id" : this.logbiddingid,
      "installment_no" : this.logbiddinginstallmentno
    }
	//window.location.href='http://13.126.100.85/pusher/bidding.php?tenant_id='+this.AdminBid.get('tenant_id').value+'&branch_id='+this.AdminBid.get('branch_id').value+'&group_id='+this.logbiddingid+'&installment_no='+this.logbiddinginstallmentno;
    this.logall = [];
    this.userservice.getData(cond, "get-log-bid-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.logall = response.data;
          setTimeout(() => {
            this.TableHidden = false;
          },0);
        }
      } catch (err) {}
    }, (err: any) => {
      if (err.status == 401) {}
      else { }
    });

    
    
    this.userservice.getData(cond, "get-group-bid-inst", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.msgList = response.data;
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

  logviewReport(event, group_id , installment_no){
    /*this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = false;
    this.hiddentopfive = true;
    this.hiddentopsix = true;
    this.back = false;
    let logview = row;*/

    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = true;
    this.hiddentopsix = true;
    this.hiddentopseven = true;
    this.hiddentopeight = false;
    this.back = false;
    this.logbiddingid = group_id;
    this.logbiddinginstallmentno = installment_no;

    let cond = {
      "tenant_id" : this.AdminBid.get('tenant_id').value,
      "branch_id" : this.AdminBid.get('branch_id').value,
      "group_id" : this.logbiddingid,
      "installment_no" : this.logbiddinginstallmentno
    }
    this.userservice.getData(cond, "get-log-bid-viewsort", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.logall = response.data;
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
   
    this.hiddentop = false;
    this.hiddentoptwo = false;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = true;
    this.hiddentopsix = true;
    this.hiddentopseven = true;
    this.hiddentopeight = true;
    this.back = true;

    //this.myButton.nativeElement = [];
  }

  viewallauctionReport(){
    this.hiddentop = true;
    this.hiddentoptwo = true;
    this.hiddentopthree = true;
    this.hiddentopfour = true;
    this.hiddentopfive = true;
    this.hiddentopsix = false;
    this.hiddentopseven = true;
    this.hiddentopeight = true;
    this.back = false;
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

  enabledstatus( event, row){
    let id = row
    this.submitted = true;
    const resultData = Object.assign({}, this.AdminBid.value);
    swal({
      title: "Are you sure?",
      text: "You are enable the  detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, enable it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : id
        }
        this.userservice.getData(cond, "enable-bid","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.hiddentoptwo = false;
            //this.router.navigate(['/admin-panel/bidding-form']);
            this.onSubmit();
          } catch (err) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  disabledstatus( event, row){
    let id = row
    this.submitted = true;
    const resultData = Object.assign({}, this.AdminBid.value);
    swal({
      title: "Are you sure?",
      text: "You are disable the  detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, disable it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : id
        }
        this.userservice.getData(cond, "disable-bid","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.hiddentoptwo = false; 
            //this.router.navigate(['/admin-panel/bidding-form']);
            this.onSubmit();
          } catch (err) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  reactivestatus( event, row){
    let id = row
    this.submitted = true;
    const resultData = Object.assign({}, this.AdminBid.value);
    swal({
      title: "Are you sure?",
      text: "You are reactive the  detail.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, reactive it!",
      cancelButtonText: "No, cancel please!"
    }).then((result) => {
      if (result.value) {
        let cond = {
          "id" : id
        }
        this.userservice.getData(cond, "reactive-bid","JSON").subscribe((response: any) => {
          this.SubmitbuttonName = "Submit";
          try {
            this.hiddentoptwo = false;
            //this.router.navigate(['/admin-panel/bidding-form']);
            this.onSubmit();
          } catch (err) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        }, (err: any) => {
          if (err.status == 401) {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
          else {
            this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
          }
        });
      }
    });
  }

  delete( event, last){
    let id = last;
    let cond = {
      "id" : last,
      "deleted_by" : this.user_id
    }
    this.userservice.getData(cond, "delete-bid", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
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

  triggerClick(){

    /*let el: HTMLElement = this.myButton.nativeElement as HTMLElement;
    setTimeout(()=> el.click(), 1000);*/

    let cond = {
      "id" : this.rowid
    }
    this.userservice.getData(cond, "get-maxamount-bid-details", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.max__bid_amount = response.data;
          this.individualInfo = response.data2;
          this.installmentNo = response.data3;
          this.tenant_id_msg = this.AdminBid.get('tenant_id').value;
          this.branch_id_msg = this.AdminBid.get('branch_id').value;
          this.group_id_msg = this.AdminBid.get('group_id').value;
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
      "tenant_id" : this.AdminBid.get('tenant_id').value,
      "branch_id" : this.AdminBid.get('branch_id').value,
      "group_id" : this.logbiddingid,
      "installment_no" : this.logbiddinginstallmentno
    }
    this.userservice.getData(cond1, "get-log-bid-view", "POST").subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.logall = response.data;
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

  stopClick(event, row){
    let id = row
    
    let cond = {
      "id" : id
    }
    this.userservice.getData(cond, "disable-bid","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
          this.hiddentop = false;
          this.hiddentoptwo = false;
          this.hiddentopthree = true;
          this.hiddentopfour = true;
          this.hiddentopfive = true;
          this.hiddentopsix = true;
          this.hiddentopseven = true;
          this.back = true;
          this.myButton.nativeElement = [];
          this.onSubmit();
      } catch (err) {
        this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
      }
    }, (err: any) => {
      if (err.status == 401) {
        this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Bidding Form", "Sorry!! Something went wrong!", "error");
      }
    });

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
    if (this.AdminBid.invalid) 
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
      "tenant_id" : this.AdminBid.get('tenant_id').value,
      "branch_id" : this.AdminBid.get('branch_id').value,
      "group_id" : this.AdminBid.get('group_id').value
    }
    
    this.userservice.getData(cond, "get-group-bid", "POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Filter";
      try {
        if (response.status == 'Success') {
          this.dataList = response.data1;
          this.allItems = response.data1;
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
    this.msg = result.message;
    if (this.ChequeGroup.invalid) {
      this.SubmitChequeButtonName = "Submit";
      this.disableChequeBtn = false;
      if(this.cf.message.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    let cond = {
      "tenant_id" : this.tenant_id_msg,
      "branch_id" : this.group_id_msg,
      "group_id" : this.AdminBid.get('group_id').value,
      "installment_no" : this.installmentNo,
      "message" : this.msg
    }
    this.userservice.getData(cond, "store-message-bidlog", "JSON").subscribe((response: any) => {
      this.SubmitChequeButtonName = "Submit";
      try {
        if (response.status == 'Success') {
         /* let cond1 = {
            "group_id" : 1,
            "installment_no" : 1,
            "message" : 'check the amount'
          }
          this.userservice.getData(cond1, "", "POST").subscribe((response: any) => {
            try {
              if (response.status == 'Success') {
                this.message = response.data;
                setTimeout(() => {
                  this.TableHidden = false;
                },0);
              }
            } catch (err) {}
          }, (err: any) => {
            if (err.status == 401) {}
            else { }
          });*/
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
    this.AdminBid = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
    });

    this.ChequeGroup = this.formBuilder.group({
      message: ['', Validators.required],
      tenant_id_message: [''],
      branch_id_message: [''],
      group_id_message: [''],
      installmentno_message: ['']
    });

    let tenant_id = localStorage.getItem('tenant_id');
    this.user_id = localStorage.getItem('logged_user_id');
    if(tenant_id) {
      this.AdminBid.get('tenant_id').setValue(tenant_id);
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
