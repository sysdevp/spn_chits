import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-commitment-chit',
  templateUrl: './update-commitment-chit.component.html',
  styles: []
})

export class UpdateCommitmentChitComponent implements OnInit {
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

  UpdateCommitmentChit: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  companyList: any;
  customerList:any[];
  chitValueList: any[];
  collectionAgentList: any[];
  introducerList: any[];
  introNameShow: boolean;
  introListShow: boolean;
  introType: any;
  pincodePattern = "^[0-9]{6}$";
  collectionAreaList: any;
  commitmentChitId: any;
  slabList: any;;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {   
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCommitmentChit.controls;
  }

  companyChg(event) { 
    let tenant_id = event.target.value;
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
  }

  branchChg(event) {
    let branch_id = event.target.value;
    this.setCustomerList(branch_id);
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
        } else {
          this.collectionAreaList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  setCustomerList(branch_id) 
  { 
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-customers", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') { 
          this.customerList = response.data;
          console.log(response.data.name)
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

  schemeChg(event)
  {
    let id=event.target.id;
    this.setSlabLists(id);
  }
  
  setSlabLists(id)
  {
    let cond = {
      "id" : id
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

  agentTypeChg(event) {
    let introducerType = event.target.value;
    this.introType = event.target.value;
    let tenantId = this.UpdateCommitmentChit.get('tenant_id').value;
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
    let customerId = this.UpdateCommitmentChit.get('customer_id').value;
    if(introducerType=='customer') {
      let cond = {
        "conditions[tenant_id]" : tenantId,
        "for_edit" : customerId
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
    const introducerNameControl = this.UpdateCommitmentChit.get('introducer_name');
    const introducerIdControl = this.UpdateCommitmentChit.get('introducer_id');
    this.UpdateCommitmentChit.get('agent_type').valueChanges
    .subscribe(agent_type => {
      if ((agent_type=='1') || (agent_type=='2' || (agent_type=='3'))) {
        introducerIdControl.setValidators([Validators.required]);
        introducerNameControl.setValidators(null);
      }
      if (agent_type=='4') {
        introducerIdControl.setValidators(null);
        introducerNameControl.setValidators([Validators.required]);
      }
      introducerIdControl.updateValueAndValidity();
      introducerNameControl.updateValueAndValidity();
    });
  }

  onSubmit() { 
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCommitmentChit.value); 
    if (this.UpdateCommitmentChit.invalid) 
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
      }
      return;
    }
    
    this.userservice.getData(result, "update-commitment-enrolled-customers", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Commitment Chit",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-commitment-chit']);
          }, 300);
        } else {
          this.appService.showAlert("Commitment Chit", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Commitment Chit", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateCommitmentChit = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      customer_id: ['', Validators.required],
      scheme_id: [''],
      agent_type:['', Validators.required],
      introducer_id:[''],
      introducer_name:[''],
      payment_type: ['', Validators.required],
      slab_id: [''],
      date_introduced: [null, Validators.required],
      due_date: [null],
      collection_agent_id:[''],
      collection_area_id:[''],
      collection_pin:['', [Validators.required,Validators.pattern(this.pincodePattern)]],
      id:[''],
      updated_by: [''],
    });
    this.setEnrollManagerValidators();
    this.introListShow = false;
    this.introNameShow = true;
    this.userservice.getData('', "list-schemes", "POST").subscribe((response: any) => {
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
    this.route.params.subscribe(params => {
      this.commitmentChitId = params.id;
      this.UpdateCommitmentChit.get('id').setValue(this.commitmentChitId);
      this.UpdateCommitmentChit.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.commitmentChitId 
      }
      this.userservice.getData(cond, "get-commitment-enrolled-customers","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCommitmentChit.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateCommitmentChit.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateCommitmentChit.get('customer_id').setValue(response.data[0].customer_id);
            this.UpdateCommitmentChit.get('scheme_id').setValue(response.data[0].scheme_id);
            this.UpdateCommitmentChit.get('slab_id').setValue(response.data[0].slab_id);
            if(response.data[0].date_introduced!='') {
              let introDate = (response.data[0].date_introduced).split('/');
              let setIntroDate = {
                'year': parseInt(introDate[2]),
                'month': parseInt(introDate[1]),
                'day': parseInt(introDate[0])
              } 
              this.UpdateCommitmentChit.get('date_introduced').setValue(setIntroDate);
            }
            this.UpdateCommitmentChit.get('payment_type').setValue(response.data[0].payment_type);
            if(response.data[0].due_date!='') {
              let dueDate = (response.data[0].due_date).split('/');
              let setDueDate = {
                'year': parseInt(dueDate[2]),
                'month': parseInt(dueDate[1]),
                'day': parseInt(dueDate[0])
              }
              this.UpdateCommitmentChit.get('due_date').setValue(setDueDate); 
            } 
            this.UpdateCommitmentChit.get('agent_type').setValue(response.data[0].agent_type);
            this.introType = response.data[0].agent_type;
            this.UpdateCommitmentChit.get('introducer_name').setValue(response.data[0].introducer_name);
            this.UpdateCommitmentChit.get('introducer_id').setValue(response.data[0].introducer_id);
            this.UpdateCommitmentChit.get('collection_agent_id').setValue(response.data[0].collection_agent_id);
            this.UpdateCommitmentChit.get('collection_area_id').setValue(response.data[0].collection_area_id);
            this.UpdateCommitmentChit.get('collection_pin').setValue(response.data[0].collection_pin);
            this.setBranchList(response.data[0].tenant_id);
            this.setCustomerList(response.data[0].branch_id);
            this.setSlabLists(response.data[0].scheme_id);
            this.setCollectionAreaList(response.data[0].branch_id);
            if(response.data[0].agent_type==1) {
              this.introListShow = true;
              this.introNameShow = false;
              this.setIntroducerList(response.data[0].tenant_id,'customer');
            }
            else if(response.data[0].agent_type==2) {
              this.introListShow = true;
              this.introNameShow = false;
            this.setIntroducerList(response.data[0].tenant_id,'employee');
            }
            else if(response.data[0].agent_type==3) {
              this.introListShow = true;
              this.introNameShow = false;
              this.setIntroducerList(response.data[0].tenant_id,'businessAgent');
            }
            else {
              this.introListShow = false;
              this.introNameShow = true;
            } 
          }
          else {
            this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) { 
          this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Commitment Chit", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
