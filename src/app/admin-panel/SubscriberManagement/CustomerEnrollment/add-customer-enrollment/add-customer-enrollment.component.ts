import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-customer-enrollment',
  templateUrl: './add-customer-enrollment.component.html',
  styles: []
})

export class AddCustomerEnrollmentComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('group_id') focus4:ElementRef;
  @ViewChild('ticket_no') focus5:ElementRef;
  @ViewChild('payment_type') focus6:ElementRef;
  @ViewChild('collection_area_id') focus7:ElementRef;
  @ViewChild('collection_agent_id') focus8:ElementRef;
  @ViewChild('collection_pin') focus9:ElementRef;
  @ViewChild('agent_type') focus10:ElementRef;
  @ViewChild('introducer_name') focus11:ElementRef;
  @ViewChild('introducer_id') focus12:ElementRef;
  @ViewChild('date_introduced') focus13:ElementRef;
  //@ViewChild('customer_type') focus14:ElementRef;
  @ViewChild('bonus_percentage') focus15:ElementRef;
  @ViewChild('penalty_percentage') focus16:ElementRef;
  @ViewChild('interest_percentage') focus17:ElementRef;
  //@ViewChild('dividend_cancel') focus18:ElementRef;
  @ViewChild('ticket_type') focus19:ElementRef;
  @ViewChild('enrolment_type') focus20:ElementRef;
  @ViewChild('chit_type') focus21:ElementRef;
  @ViewChild('remarks') focus22:ElementRef;
  @ViewChild('chit_asking_month') focus23:ElementRef;

  AddCustomerEnrollment: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  branchList: any[];
  customerList: any[];
  groupList: any[];
  collectionAgentList: any[];
  introducerList: any[];
  collectionAreaList: any[];
  introListShow: boolean;
  introNameShow: boolean;
  memberCount: number;
  ticketList: any[];
  pendingTickets: any;
  showFullTicket: any;
  introType: any;
  pincodePattern = "^[0-9]{6}$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  collectionPincode: any[];
  CollectionAreaPincode: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  name: any;
  install_det: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddCustomerEnrollment.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1,
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

  branchChg(event) {
    let branch_id = event.target.value;
    this.setCustomerAndGroupList(branch_id);
    this.setCollectionAreaList(branch_id);
  }

  setCollectionAreaList(branch_id) {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAreaList = response.data;
          console.log(this.collectionAreaList)
        } else {
          this.collectionAreaList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  collectionareaChg(event)
  {
    this.AddCustomerEnrollment.get('collection_pin').setValue('');
    let id = event.target.value;
    this.setCollectionAreaPincode(id);
  }
  setCollectionAreaPincode(id)
  {
    let cond1 = {
      "conditions[id]" : id
    }
    this.userservice.getData(cond1, "get-collection-areas", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.CollectionAreaPincode = response.data[0].area_pincode;
          this.AddCustomerEnrollment.get('collection_pin').setValue(this.CollectionAreaPincode)
          console.log(this.CollectionAreaPincode)
        } else {
          this.CollectionAreaPincode = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  
  /*collectionAreaChg(event) {
    let cond = {
      "conditions[tenant_id]" : this.AddCustomerEnrollment.get('tenant_id').value,
      "conditions[branch_id]" : this.AddCustomerEnrollment.get('branch_id').value,
      "conditions[collection_area_id]" : event.target.value
    }
    this.userservice.getData(cond, "get-collection-area-mappings", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAgentList = response.data;
        } else {
          this.collectionAgentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }*/

  setCustomerAndGroupList(branch_id) { 
    let cond = {
      "conditions[branch_id]" : branch_id,
      "conditions[is_filled]" : 1
    }
    this.userservice.getData(cond, "get-groups", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.groupList = response.data; 
        } else {
          this.groupList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); 
  }

  setCollectionAgentList(branch_id) {
    let cond = {
      "conditions[branch_id]" : branch_id,
      "conditions[collection_agent]" : 1
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAgentList = response.data;
        } else {
          this.collectionAgentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  groupChg(event) {
    let selectedGrp =  this.groupList.filter((listing: any) => listing.id == event.target.value);
    this.memberCount = selectedGrp[0].scheme_info.members;
    let ticketCount = (selectedGrp[0].scheme_info.members)*2;
    this.pendingTickets = selectedGrp[0].group_pending_ticket;
    if(parseInt(this.pendingTickets)>=1) {
      this.showFullTicket = 1;
    }
    let cond = {
      "conditions[group_id]" : event.target.value,
      "conditions[ticket_count]" : ticketCount
    }
    this.userservice.getData(cond, "get-unassigned-tickets", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.ticketList = response.data;
          this.AddCustomerEnrollment.get('ticket_no').setValue(response.data[0])
        } else {
          this.ticketList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    let cond1 = {
      "tenant_id" : this.AddCustomerEnrollment.get('tenant_id').value,
      "branch_id" : this.AddCustomerEnrollment.get('branch_id').value,
    }
    this.userservice.getData(cond1, "get-all-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data; 
          this.dropdownList = [];
 for(let i=0; i<((this.customerList).length); i++) {
    var obj = {};
    obj["customer_id"] = response.data[i].id;
    obj["name"] = response.data[i].name;
    this.dropdownList.push(obj);
  }
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  customerChg(item: any) {
    this.AddCustomerEnrollment.get('agent_type').setValue('');
    this.AddCustomerEnrollment.get('introducer_id').setValue('');
    this.introducerList = [];
  }

  agentTypeChg(event) {
    let introducerType = event.target.value;
    this.introType = event.target.value;
    let tenantId = this.AddCustomerEnrollment.get('tenant_id').value;
    if(introducerType==1) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId,'customer');
    }
    else if(introducerType==2) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId,'employee');
    }
    else if(introducerType==3) {
      this.introListShow = true;
      this.introNameShow = false;
      this.setIntroducerList(tenantId,'businessAgent');
    }
    else if(introducerType==4) {
      this.introListShow = false;
      this.introNameShow = false;
    }
    else {
      this.introListShow = false;
      this.introNameShow = true;
    }
  }

  setIntroducerList(tenantId,introducerType) {
    if(introducerType=='customer') {
      let cond = {
        "conditions[tenant_id]" : tenantId
      }
      this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') {
            let selectedCustomer = this.AddCustomerEnrollment.get('customer_id').value;
            let customerList = response.data;
            this.introducerList = customerList.filter(function(obj) {
              return obj.id != selectedCustomer;
            }); 
          } else {
            this.introducerList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
    else {
      let cond = {
        "conditions[tenant_id]" : tenantId,
        "conditions[employee_type]" : 1
      }
      if(introducerType=='businessAgent') {
        cond = {
          "conditions[tenant_id]" : tenantId,
          "conditions[employee_type]" : 2
        }
      }
      this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
        try { 
          if (response.status == 'Success') {
            this.introducerList = response.data;
          } else {
            this.introducerList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
  }

  setEnrollManagerValidators() {
    const introducerNameControl = this.AddCustomerEnrollment.get('introducer_name');
    const introducerIdControl = this.AddCustomerEnrollment.get('introducer_id');
    this.AddCustomerEnrollment.get('agent_type').valueChanges
    .subscribe(agent_type => {
      if ((agent_type=='1') || (agent_type=='2' || (agent_type=='3'))) {
        introducerIdControl.setValidators([Validators.required]);
        introducerNameControl.setValidators(null);
      }
      if (agent_type=='4') {
        introducerIdControl.setValidators(null);
        introducerNameControl.setValidators(null);
      }
      introducerIdControl.updateValueAndValidity();
      introducerNameControl.updateValueAndValidity();
    });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCustomerEnrollment.value);
    if (this.AddCustomerEnrollment.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.customer_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.group_id.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.ticket_no.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.payment_type.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.collection_area_id.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.collection_agent_id.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.collection_pin.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.agent_type.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.introducer_name.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.introducer_id.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.date_introduced.errors) {
        this.focus13.nativeElement.focus();
      }
      /*else if(this.f.customer_type.errors) {
        this.focus14.nativeElement.focus();
      }*/
      else if(this.f.bonus_percentage.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.penalty_percentage.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.interest_percentage.errors) {
        this.focus17.nativeElement.focus();
      }
      /* else if(this.f.dividend_cancel.errors) {
        this.focus18.nativeElement.focus();
      } */
      else if(this.f.ticket_type.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.enrolment_type.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.chit_type.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus22.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-customer-enrollment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Customer Enrollment",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-customer-enrollment']);
          }, 300);
        } else {
          this.appService.showAlert("Customer Enrollment", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Customer Enrollment", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Customer Enrollment", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCustomerEnrollment = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      group_id: ['', Validators.required],
      ticket_no: ['', [Validators.required]],
      ticket_no_type: [''],
      payment_type: ['', [Validators.required]],
      collection_area_id: ['', [Validators.required]],
      collection_pin: [''],
      collection_agent_id: [''],
      agent_type: ['', [Validators.required]],
      introducer_name: [''],
      introducer_id: [''],
      date_introduced: [null, [Validators.required]], 
      //customer_type: ['', [Validators.required]],
      bonus_percentage: ['',[Validators.pattern(this.decimalPattern)]],
      penalty_percentage: ['',[Validators.pattern(this.decimalPattern)]],
      interest_percentage: ['',[Validators.pattern(this.decimalPattern)]],
     // dividend_cancel: [''],
      ticket_type: ['', [Validators.required]],
      enrolment_type: ['', [Validators.required]],
      chit_type: ['', [Validators.required]],
      pledge_enrolment_id: [''],
      remarks: [''],
      created_by: [''],
      chit_asking_month:[''],
    });
    this.setEnrollManagerValidators();
    this.introListShow = false;
    this.introNameShow = false;
    this.memberCount = 0; 
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCustomerEnrollment.get('tenant_id').setValue(tenant_id);
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
    this.AddCustomerEnrollment.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.dropdownSettings = {
      singleSelection: true,
      idField: 'customer_id',
      textField: 'name',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true
    };
  }
}
