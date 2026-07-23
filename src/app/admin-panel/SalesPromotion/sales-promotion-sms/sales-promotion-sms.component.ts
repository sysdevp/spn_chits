import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';

@Component({
  
  selector: 'app-sales-promotion-sms',
  templateUrl: './sales-promotion-sms.component.html',
  styles: []
})
export class SalesPromotionSmsComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_id') focus3:ElementRef;
  @ViewChild('remark') focus4:ElementRef;
  @ViewChild('customer_id') focus5:ElementRef;
  @ViewChild('template_id') focus6:ElementRef;

  AddSMS: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  groupList: any[];
  decimalPattern = "^[0-9]+(.[0-9]{0,2})?$";
  groupName: any;
  contentList: any;
  customerList: any[];
  dropdownList = [];
  dropdownSettings = {};
  dropdownSettings1 = {};
  selectedItems = [];
  branch_id: any;
  group_id: any;
  templateList: any;
  id: any;
  dropdownList1: any[];

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddSMS.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.AddSMS.get('branch_id').setValue('');
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.branch_id = event.target.value;
    this.setGroupList(branch_id);
  }

  groupChg(event) {
    this.AddSMS.get('customer_id').setValue('');
    let group_id = event.target.value;
    this.group_id = event.target.value;

    let cond1 = {
      "group_id" : this.group_id
    }
    this.userservice.getData(cond1, "get-sms-new-group-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          //this.contentList = response.data[0];
          this.AddSMS.get('remark').setValue(response.data.sms); 
          console.log(this.contentList)
        } else {
          this.contentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });



    /*let cond = {
      "group_id" : group_id,
    }
    this.userservice.getData(cond, "get-sms-group-details", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.contentList = response.data[0];
          this.AddSMS.get('remark').setValue(response.data[0].sms); 
          console.log(this.contentList)
        } else {
          this.contentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); */

  }

  onSelectAll(items: any){
    
  }

  onDeSelectAll(items: any){
     
  }

  customerChg(item : any) {
    
  }

  templateChg(event) {

   
  this.dropdownList = [];
  this.dropdownSettings = {};
  this.selectedItems = [];
    this.customerList = [];
   
    this.id = event.target.value;
   console.log(this.id);
    if(this.id == 0){
      let cond1 = {
        "branch_id" : this.branch_id
      }
      this.userservice.getData(cond1, "customer-and-leaddetails", "POST").subscribe((response: any) => {
        try { 
        if (response.status == 'Success') { 
        this.customerList = response.data;
        this.dropdownList = [];
        for(let i=0; i<((this.customerList).length); i++) {
          var obj = {};
          obj["item_id"] = response.data[i].mobile_no;
          obj["item_text"] = response.data[i].name;
          this.dropdownList.push(obj);
        } 
        } else {
        this.customerList = [];
        }
        } catch (err) { }
        }, (err: any) => { });

    }
    if(this.id == 1){
      let cond1 = {
        "conditions[branch_id]" : this.branch_id
      }
      this.userservice.getData(cond1, "get-customers-view", "POST").subscribe((response: any) => {
        try { 
        if (response.status == 'Success') { 
        this.customerList = response.data;
        this.dropdownList = [];
        for(let i=0; i<((this.customerList).length); i++) {
          var obj = {};
          obj["item_id"] = response.data[i].mobile_no;
          var name = response.data[i].name;
          var mobile = response.data[i].mobile_no;
          var name_mobile =  name + '/' + mobile ;
          obj["item_text"] = name_mobile;
          this.dropdownList.push(obj);
        } 
        } else {
        this.customerList = [];
        }
        } catch (err) { }
        }, (err: any) => { });
    }
    if(this.id == 2){
      let cond1 = {
        "conditions[branch_id]" : this.branch_id,
        "conditions[is_customer]" : 0
      }
      this.userservice.getData(cond1, "get-lead-managements", "POST").subscribe((response: any) => {
        try { 
        if (response.status == 'Success') { 
        this.customerList = response.data;
        this.dropdownList = [];
        for(let i=0; i<((this.customerList).length); i++) {
          var obj = {};
          obj["item_id"] = response.data[i].mobile_no;
          obj["item_text"] = response.data[i].lead_customer_name;
          this.dropdownList.push(obj);
        } 
        } else {
        this.customerList = [];
        }
        } catch (err) { }
        }, (err: any) => { });
    }
    else{
      this.dropdownList = [];
  this.dropdownSettings = {};
  this.selectedItems = [];
    this.customerList = [];
    }

    this.dropdownList1 = [];
    var items = [{
      id: 1,
      label: "SMS"
  }, {
      id: 2,
      label: "Mail"
  }, {
      id: 3,
      label: "Whatsapp"
  }];
        for(let i=0; i<3; i++) {
          var obj = {};
          obj["item_id"] = items[i].id;
          obj["item_text"] = items[i].label;
          this.dropdownList1.push(obj);
        } 
  }


  setGroupList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id,
    }
    this.userservice.getData(cond, "get-vacant-temporary-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.groupList = response.data; 
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }


  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddSMS.value);
    if (this.AddSMS.invalid) 
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
      else if(this.f.remark.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.template_id.errors) {
        this.focus6.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result, "send-group-sms-notifications", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("SMS",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/']);
          }, 300);
        } else {
          this.appService.showAlert("SMS", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("SMS", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("SMS", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("SMS", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddSMS = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      template_id: ['', Validators.required],
      option: [''],
      remark: [''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddSMS.get('tenant_id').setValue(tenant_id);
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
    this.AddSMS.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.dropdownSettings = {
      singleSelection: false,
      idField: 'item_id',
      textField: 'item_text',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true
    };
    this.dropdownSettings1 = {
      singleSelection: false,
      idField: 'item_id',
      textField: 'item_text',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true
    };
  }
}
