import { Component, OnInit ,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-commitment-chit',
  templateUrl: './add-commitment-chit.component.html',
  styles: []
})

export class AddCommitmentChitComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('customer_id') focus3:ElementRef;
  @ViewChild('scheme_id') focus4:ElementRef;
  @ViewChild('agent_type') focus5:ElementRef;
  @ViewChild('introducer_id') focus6:ElementRef;
  @ViewChild('introducer_name') focus7:ElementRef;
  @ViewChild('payment_type') focus8:ElementRef;
  @ViewChild('slab_id') focus9:ElementRef;
  @ViewChild('date_introduced') focus10:ElementRef;
  @ViewChild('due_date') focus11:ElementRef;
  @ViewChild('collection_agent_id') focus12:ElementRef;
  @ViewChild('collection_area_id') focus13:ElementRef;
  @ViewChild('collection_pin') focus14:ElementRef;
  @ViewChild('ar_no') focus15:ElementRef;

  AddCommitmentChit: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  companyList: any[];
  customerList: any[] = [];
  chitValueList: any[];
  collectionAgentList: any[];
  introducerList: any[];
  introNameShow: boolean;
  introListShow: boolean;
  introType: any;
  pincodePattern = "^[0-9]{6}$";
  slabList: any[];
  collectionAreaList: any[];
  CollectionAreaPincode: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddCommitmentChit.controls;
  }

  companyChg(event) { 
    let tenant_id = event.target.value;
    this.AddCommitmentChit.get('customer_id').setValue('');
    this.setBranchList(tenant_id);
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

    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
        } else {
          this.customerList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-collection-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.collectionAgentList = response.data;
        } else {
          this.collectionAgentList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event)
  {
    let branch_id = event.target.value;
    this.setCollectionAreaList(branch_id);
  }

  schemeChg(event)
  {
    let cond = {
      "id" : event.target.value
    }
    this.userservice.getData(cond, "get-slab-type", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.slabList = response.data;
        } else {
          this.slabList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  setCollectionAreaList(branch_id) {
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAreaList = response.data;
        } else {
          this.collectionAreaList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  /*collectionAreaChg(event) {
    let cond = {
      "conditions[tenant_id]" : this.AddCommitmentChit.get('tenant_id').value,
      "conditions[branch_id]" : this.AddCommitmentChit.get('branch_id').value,
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
  collectionareaChg(event)
  {
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
          this.AddCommitmentChit.get('collection_pin').setValue(this.CollectionAreaPincode)
        } else {
          this.CollectionAreaPincode = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }
  agentTypeChg(event) {
    let introducerType = event.target.value;
    this.introType = event.target.value;
    let tenantId = this.AddCommitmentChit.get('tenant_id').value;
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
            this.introducerList = response.data;
          } else {
            this.introducerList = [];
          }
        }  catch (err) { }
      }, (err: any) => { });
    }
    else {
      let cond = {
        "conditions[tenant_id]" : tenantId,
        "conditions[collection_agent]" : 0
      }
      if(introducerType=='businessAgent') {
        cond = {
          "conditions[tenant_id]" : tenantId,
          "conditions[collection_agent]" : 1
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
    const introducerNameControl = this.AddCommitmentChit.get('introducer_name');
    const introducerIdControl = this.AddCommitmentChit.get('introducer_id');

    this.AddCommitmentChit.get('agent_type').valueChanges
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
    const result = Object.assign({}, this.AddCommitmentChit.value);
    if (this.AddCommitmentChit.invalid) 
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
      else if(this.f.scheme_id.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.agent_type.errors) { 
        this.focus5.nativeElement.focus();
      }
      else if(this.f.introducer_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.introducer_name.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.payment_type.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.slab_id.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.date_introduced.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.due_date.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.collection_agent_id.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.collection_area_id.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.collection_pin.errors) {
        this.focus14.nativeElement.focus();
      }else if(this.f.ar_no.errors) {
        this.focus15.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-commitment-enrolled-customers", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Chit",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-commitment-chit']);
          }, 300);
        } else {
          this.appService.showAlert("Chit", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Chit", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      } 
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Chit", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Chit", "Sorry!! Something went wrong!", "error");
      }
    });
  }
  
  ngOnInit() {
    this.AddCommitmentChit = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      scheme_id: [''],
      agent_type:['', Validators.required],
      introducer_id:[''],
      introducer_name:[''],
      ar_no:[''],
      payment_type: ['', Validators.required],
      slab_id: ['', Validators.required],
      date_introduced: [null, Validators.required],
      due_date: [null, Validators.required],
      collection_agent_id:[''],
      collection_area_id:['', Validators.required],
      collection_pin:[''],
      created_by: [''],
    });
    let autoDue: any = null;
    this.AddCommitmentChit.get('date_introduced').valueChanges.subscribe((joined: any) => {
      const due = this.AddCommitmentChit.get('due_date');
      if (joined && (!due.value || due.value === autoDue)) {
        autoDue = joined && typeof joined === 'object' ? { ...joined } : joined;
        due.setValue(autoDue);
      }
    });
    this.setEnrollManagerValidators();
    this.introListShow = false;
    this.introNameShow = true;
    let cond = {
      "tenant_id" : localStorage.getItem('tenant_id')
    }
    this.userservice.getData(cond, "list-schemes", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.chitValueList = response.data;
        } else {
          this.chitValueList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCommitmentChit.get('tenant_id').setValue(tenant_id);
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
    this.AddCommitmentChit.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
