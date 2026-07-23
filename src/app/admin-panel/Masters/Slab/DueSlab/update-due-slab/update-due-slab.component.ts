import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../../Services/user.service';

@Component({
  
  selector: 'app-update-due-slab',
  templateUrl: './update-due-slab.component.html',
  styles: []
})

export class UpdateDueSlabComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('schemes_id') focus3:ElementRef;
  @ViewChild('due_1') focus4:ElementRef;
  @ViewChild('due_2') focus5:ElementRef;
  @ViewChild('due_3') focus6:ElementRef;
  @ViewChild('due_4') focus7:ElementRef;
  @ViewChild('due_5') focus8:ElementRef;
  @ViewChild('due_6') focus9:ElementRef;
  @ViewChild('due_7') focus10:ElementRef;
  @ViewChild('due_8') focus11:ElementRef;
  @ViewChild('due_9') focus12:ElementRef;
  @ViewChild('due_10') focus13:ElementRef;
  @ViewChild('due_11') focus14:ElementRef;
  @ViewChild('due_12') focus15:ElementRef;
  @ViewChild('due_13') focus16:ElementRef;
  @ViewChild('due_14') focus17:ElementRef;
  @ViewChild('due_15') focus18:ElementRef;
  @ViewChild('due_16') focus19:ElementRef;
  @ViewChild('due_17') focus20:ElementRef;
  @ViewChild('due_18') focus21:ElementRef;
  @ViewChild('due_19') focus22:ElementRef;
  @ViewChild('due_20') focus23:ElementRef;
  @ViewChild('due_21') focus24:ElementRef;
  @ViewChild('due_22') focus25:ElementRef;
  @ViewChild('due_23') focus26:ElementRef;
  @ViewChild('due_24') focus27:ElementRef;
  @ViewChild('due_25') focus28:ElementRef;
  @ViewChild('due_26') focus29:ElementRef;
  @ViewChild('due_27') focus30:ElementRef;
  @ViewChild('due_28') focus31:ElementRef;
  @ViewChild('due_29') focus32:ElementRef;
  @ViewChild('due_30') focus33:ElementRef;
  @ViewChild('due_31') focus34:ElementRef;
  @ViewChild('due_32') focus35:ElementRef;
  @ViewChild('due_33') focus36:ElementRef;
  @ViewChild('due_34') focus37:ElementRef;
  @ViewChild('due_35') focus38:ElementRef;
  @ViewChild('due_36') focus39:ElementRef;
  @ViewChild('due_37') focus40:ElementRef;
  @ViewChild('due_38') focus41:ElementRef;
  @ViewChild('due_39') focus42:ElementRef;
  @ViewChild('due_40') focus43:ElementRef;
  @ViewChild('due_41') focus44:ElementRef;
  @ViewChild('due_42') focus45:ElementRef;
  @ViewChild('due_43') focus46:ElementRef;
  @ViewChild('due_44') focus47:ElementRef;
  @ViewChild('due_45') focus48:ElementRef;
  @ViewChild('due_46') focus49:ElementRef;
  @ViewChild('due_47') focus50:ElementRef;
  @ViewChild('due_48') focus51:ElementRef;
  @ViewChild('due_49') focus52:ElementRef;
  @ViewChild('due_50') focus53:ElementRef;
  @ViewChild('slab_name') focus54:ElementRef;

  form_message = true;
  UpdateSlab: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  dueSlabId: any;
  noOfMonths: number;
  Arr = Array; 
  schemeList: any[];
  chit_value:any;
  isSuperAdmin:boolean;
  dueList:boolean=true;
  chitValue:boolean=true;
  due: any;
  companyList: any[];

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateSlab.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    let cond = {
      "conditions[tenant_id]" : tenant_id,
    }
    this.setScheme(cond);
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

  setScheme(cond) {
    this.userservice.getData(cond, "get-schemes", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.schemeList = response.data;
        } else {
          this.schemeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  arrayOne(n: number): any[] {
    return Array(n);
  }

  schemeChg(event) {
    this.dueList=false
    let scheme =  this.schemeList.filter((listing: any) => listing.id == event.target.value);
    this.noOfMonths = scheme[0].no_of_months; 
  }
	  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateSlab.value);
    if (this.UpdateSlab.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.schemes_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.due_1.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.due_2.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.due_3.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.due_4.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.due_5.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.due_6.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.due_7.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.due_8.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.due_9.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.due_10.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.due_11.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.due_12.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.due_13.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.due_14.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.due_15.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.due_16.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.due_17.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.due_18.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.due_19.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.due_20.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.due_21.errors) {
        this.focus24.nativeElement.focus();
      }
      else if(this.f.due_22.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.due_23.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.due_24.errors) {
        this.focus27.nativeElement.focus();
      }
      else if(this.f.due_25.errors) {
        this.focus28.nativeElement.focus();
      }
      else if(this.f.due_26.errors) {
        this.focus29.nativeElement.focus();
      }
      else if(this.f.due_27.errors) {
        this.focus30.nativeElement.focus();
      }
      else if(this.f.due_28.errors) {
        this.focus31.nativeElement.focus();
      }
      else if(this.f.due_29.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.due_30.errors) {
        this.focus33.nativeElement.focus();
      }
      else if(this.f.due_31.errors) {
        this.focus34.nativeElement.focus();
      }
      else if(this.f.due_32.errors) {
        this.focus35.nativeElement.focus();
      }
      else if(this.f.due_33.errors) {
        this.focus36.nativeElement.focus();
      }
      else if(this.f.due_34.errors) {
        this.focus37.nativeElement.focus();
      }
      else if(this.f.due_35.errors) {
        this.focus38.nativeElement.focus();
      }
      else if(this.f.due_36.errors) {
        this.focus39.nativeElement.focus();
      }
      else if(this.f.due_37.errors) {
        this.focus40.nativeElement.focus();
      }
      else if(this.f.due_38.errors) {
        this.focus41.nativeElement.focus();
      }
      else if(this.f.due_39.errors) {
        this.focus42.nativeElement.focus();
      }
      else if(this.f.due_40.errors) {
        this.focus43.nativeElement.focus();
      }
      else if(this.f.due_41.errors) {
        this.focus44.nativeElement.focus();
      }
      else if(this.f.due_42.errors) {
        this.focus45.nativeElement.focus();
      }
      else if(this.f.due_43.errors) {
        this.focus46.nativeElement.focus();
      }
      else if(this.f.due_44.errors) {
        this.focus47.nativeElement.focus();
      }
      else if(this.f.due_45.errors) {
        this.focus48.nativeElement.focus();
      }
      else if(this.f.due_46.errors) {
        this.focus49.nativeElement.focus();
      }
      else if(this.f.due_47.errors) {
        this.focus50.nativeElement.focus();
      }
      else if(this.f.due_48.errors) {
        this.focus51.nativeElement.focus();
      }
      else if(this.f.due_49.errors) {
        this.focus52.nativeElement.focus();
      }
      else if(this.f.due_50.errors) {
        this.focus53.nativeElement.focus();
      }
      else if(this.f.slab_name.errors) {
        this.focus54.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-slab-due","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Due Slab", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-due-slab']);
          }, 3000);
        } else {
          this.appService.showAlert("Due Slab", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateSlab = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      schemes_id:['', Validators.required],
      slab_name:['', Validators.required],
      due_1:[''],
      due_2:[''],
      due_3:[''],
      due_4:[''],
      due_5:[''],
      due_6:[''],
      due_7:[''],
      due_8:[''],
      due_9:[''],
      due_10:[''],
      due_11:[''],
      due_12:[''],
      due_13:[''],
      due_14:[''],
      due_15:[''],
      due_16:[''],
      due_17:[''],
      due_18:[''],
      due_19:[''],
      due_20:[''],
      due_21:[''],
      due_22:[''],
      due_23:[''],
      due_24:[''],
      due_25:[''],
      due_26:[''],
      due_27:[''],
      due_28:[''],
      due_29:[''],
      due_30:[''],
      due_31:[''],
      due_32:[''],
      due_33:[''],
      due_34:[''],
      due_35:[''],
      due_36:[''],
      due_37:[''],
      due_38:[''],
      due_39:[''],
      due_40:[''],
      due_41:[''],
      due_42:[''],
      due_43:[''],
      due_44:[''],
      due_45:[''],
      due_46:[''],
      due_47:[''],
      due_48:[''],
      due_49:[''],
      due_50:[''],
      id: [''],
      updated_by: [''],
    });
    
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.isSuperAdmin = false;
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
      this.dueSlabId = params.id;
      this.UpdateSlab.get('id').setValue(this.dueSlabId);
      this.UpdateSlab.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.dueSlabId 
      }
      this.userservice.getData(cond, "get-slab-dues","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {  
            this.noOfMonths = response.data[0].no_of_months;
            this.UpdateSlab.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateSlab.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateSlab.get('schemes_id').setValue(response.data[0].schemes_id);
            this.UpdateSlab.get('slab_name').setValue(response.data[0].slab_name);
            this.UpdateSlab.get('due_1').setValue(response.data[0].due_1);
            this.UpdateSlab.get('due_2').setValue(response.data[0].due_2);
            this.UpdateSlab.get('due_3').setValue(response.data[0].due_3);
            this.UpdateSlab.get('due_4').setValue(response.data[0].due_4);
            this.UpdateSlab.get('due_5').setValue(response.data[0].due_5);
            this.UpdateSlab.get('due_6').setValue(response.data[0].due_6);
            this.UpdateSlab.get('due_7').setValue(response.data[0].due_7);
            this.UpdateSlab.get('due_8').setValue(response.data[0].due_8);
            this.UpdateSlab.get('due_9').setValue(response.data[0].due_9);
            this.UpdateSlab.get('due_10').setValue(response.data[0].due_10);
            this.UpdateSlab.get('due_11').setValue(response.data[0].due_11);
            this.UpdateSlab.get('due_12').setValue(response.data[0].due_12);
            this.UpdateSlab.get('due_13').setValue(response.data[0].due_13);
            this.UpdateSlab.get('due_14').setValue(response.data[0].due_14);
            this.UpdateSlab.get('due_15').setValue(response.data[0].due_15);
            this.UpdateSlab.get('due_16').setValue(response.data[0].due_16);
            this.UpdateSlab.get('due_17').setValue(response.data[0].due_17);
            this.UpdateSlab.get('due_18').setValue(response.data[0].due_18);
            this.UpdateSlab.get('due_19').setValue(response.data[0].due_19);
            this.UpdateSlab.get('due_20').setValue(response.data[0].due_20);
            this.UpdateSlab.get('due_21').setValue(response.data[0].due_21);
            this.UpdateSlab.get('due_22').setValue(response.data[0].due_22);
            this.UpdateSlab.get('due_23').setValue(response.data[0].due_23);
            this.UpdateSlab.get('due_24').setValue(response.data[0].due_24);
            this.UpdateSlab.get('due_25').setValue(response.data[0].due_25);
            this.UpdateSlab.get('due_26').setValue(response.data[0].due_26);
            this.UpdateSlab.get('due_27').setValue(response.data[0].due_27);
            this.UpdateSlab.get('due_28').setValue(response.data[0].due_28);
            this.UpdateSlab.get('due_29').setValue(response.data[0].due_29);
            this.UpdateSlab.get('due_30').setValue(response.data[0].due_30);
            this.UpdateSlab.get('due_31').setValue(response.data[0].due_31);
            this.UpdateSlab.get('due_32').setValue(response.data[0].due_32);
            this.UpdateSlab.get('due_33').setValue(response.data[0].due_33);
            this.UpdateSlab.get('due_34').setValue(response.data[0].due_34);
            this.UpdateSlab.get('due_35').setValue(response.data[0].due_35);
            this.UpdateSlab.get('due_36').setValue(response.data[0].due_36);
            this.UpdateSlab.get('due_37').setValue(response.data[0].due_37);
            this.UpdateSlab.get('due_38').setValue(response.data[0].due_38);
            this.UpdateSlab.get('due_39').setValue(response.data[0].due_39);
            this.UpdateSlab.get('due_40').setValue(response.data[0].due_40);
            this.UpdateSlab.get('due_41').setValue(response.data[0].due_41);
            this.UpdateSlab.get('due_42').setValue(response.data[0].due_42);
            this.UpdateSlab.get('due_43').setValue(response.data[0].due_43);
            this.UpdateSlab.get('due_44').setValue(response.data[0].due_44);
            this.UpdateSlab.get('due_45').setValue(response.data[0].due_45);
            this.UpdateSlab.get('due_46').setValue(response.data[0].due_46);
            this.UpdateSlab.get('due_47').setValue(response.data[0].due_47);
            this.UpdateSlab.get('due_48').setValue(response.data[0].due_48);
            this.UpdateSlab.get('due_49').setValue(response.data[0].due_49);
            this.UpdateSlab.get('due_50').setValue(response.data[0].due_50);

            this.setBranchList(response.data[0].tenant_id);
            let cond = {
              "conditions[tenant_id]" : response.data[0].tenant_id,
              //"conditions[branch_id]" : response.data[0].branch_id
            }
            this.setScheme(cond);
          }
          else {
            this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Due Slab", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

