import { Component, OnInit, ElementRef,ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';

@Component({
  
  selector: 'app-auction-value',
  templateUrl: './auction-value.component.html',
  styles: []
})
export class AuctionValueComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('schemes_id') focus3:ElementRef;
  @ViewChild('min_val_1') focus4:ElementRef;
  @ViewChild('max_val_1') focus5:ElementRef;
  @ViewChild('min_val_2') focus6:ElementRef;
  @ViewChild('max_val_2') focus7:ElementRef;
  @ViewChild('min_val_3') focus8:ElementRef;
  @ViewChild('max_val_3') focus9:ElementRef;
  @ViewChild('min_val_4') focus10:ElementRef;
  @ViewChild('max_val_4') focus11:ElementRef;
  @ViewChild('min_val_5') focus12:ElementRef;
  @ViewChild('max_val_5') focus13:ElementRef;
  @ViewChild('min_val_6') focus14:ElementRef;
  @ViewChild('max_val_6') focus15:ElementRef;
  @ViewChild('min_val_7') focus16:ElementRef;
  @ViewChild('max_val_7') focus17:ElementRef;
  @ViewChild('min_val_8') focus18:ElementRef;
  @ViewChild('max_val_8') focus19:ElementRef;
  @ViewChild('min_val_9') focus20:ElementRef;
  @ViewChild('max_val_9') focus21:ElementRef;
  @ViewChild('min_val_10') focus22:ElementRef;
  @ViewChild('max_val_10') focus23:ElementRef;
  @ViewChild('min_val_11') focus24:ElementRef;
  @ViewChild('max_val_11') focus25:ElementRef;
  @ViewChild('min_val_12') focus26:ElementRef;
  @ViewChild('max_val_12') focus27:ElementRef;
  @ViewChild('min_val_13') focus28:ElementRef;
  @ViewChild('max_val_13') focus29:ElementRef;
  @ViewChild('min_val_14') focus30:ElementRef;
  @ViewChild('max_val_14') focus31:ElementRef;
  @ViewChild('min_val_15') focus32:ElementRef;
  @ViewChild('max_val_15') focus33:ElementRef;
  @ViewChild('min_val_16') focus34:ElementRef;
  @ViewChild('max_val_16') focus35:ElementRef;
  @ViewChild('min_val_17') focus36:ElementRef;
  @ViewChild('max_val_17') focus37:ElementRef;
  @ViewChild('min_val_18') focus38:ElementRef;
  @ViewChild('max_val_18') focus39:ElementRef;
  @ViewChild('min_val_19') focus40:ElementRef;
  @ViewChild('max_val_19') focus41:ElementRef;
  @ViewChild('min_val_20') focus42:ElementRef;
  @ViewChild('max_val_20') focus43:ElementRef;
  @ViewChild('min_val_21') focus44:ElementRef;
  @ViewChild('max_val_21') focus45:ElementRef;
  @ViewChild('min_val_22') focus46:ElementRef;
  @ViewChild('max_val_22') focus47:ElementRef;
  @ViewChild('min_val_23') focus48:ElementRef;
  @ViewChild('max_val_23') focus49:ElementRef;
  @ViewChild('min_val_24') focus50:ElementRef;
  @ViewChild('max_val_24') focus51:ElementRef;
  @ViewChild('min_val_25') focus52:ElementRef;
  @ViewChild('max_val_25') focus53:ElementRef;
  @ViewChild('slab_name') focus54:ElementRef;

  AddSlab: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  BranchList: any;
  noOfMonths: number;
  Arr = Array; 
  schemeList: any[];
  chit_value:any;
  isSuperAdmin:boolean;
  dueList:boolean=true;
  branchList: any;
  companyList: any[];
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddSlab.controls;
  }

  companyChg(event) {
    this.setBranchList(event.target.value);
    let cond = {
      "conditions[tenant_id]" : this.AddSlab.get('tenant_id').value,
    }
    this.setScheme(cond);
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

  setBranchList(tenant_id){ 
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

  schemeChg(event) {
    this.dueList=false;
    let scheme =  this.schemeList.filter((listing: any) => listing.id == event.target.value);
    this.noOfMonths = scheme[0].no_of_months;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddSlab.value);
    if (this.AddSlab.invalid) {
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
    this.userservice.getData(result, "store-slab-due", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Due Slab",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-due-slab']);
          }, 300);
        } else {
          this.appService.showAlert("Due Slab", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
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
    this.AddSlab = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      schemes_id:['', Validators.required],
      slab_name:['', Validators.required],
      min_val_1:[''],
      max_val_1:[''],
      min_val_2:[''],
      max_val_2:[''],
      min_val_3:[''],
      max_val_3:[''],
      min_val_4:[''],
      max_val_4:[''],
      min_val_5:[''],
      max_val_5:[''],
      min_val_6:[''],
      max_val_6:[''],
      min_val_7:[''],
      max_val_7:[''],
      min_val_8:[''],
      max_val_8:[''],
      min_val_9:[''],
      max_val_9:[''],
      min_val_10:[''],
      max_val_10:[''],
      min_val_11:[''],
      max_val_11:[''],
      min_val_12:[''],
      max_val_12:[''],
      min_val_13:[''],
      max_val_13:[''],
      min_val_14:[''],
      max_val_14:[''],
      min_val_15:[''],
      max_val_15:[''],
      min_val_16:[''],
      max_val_16:[''],
      min_val_17:[''],
      max_val_17:[''],
      min_val_18:[''],
      max_val_18:[''],
      min_val_19:[''],
      max_val_19:[''],
      min_val_20:[''],
      max_val_20:[''],
      min_val_21:[''],
      max_val_21:[''],
      min_val_22:[''],
      max_val_22:[''],
      min_val_23:[''],
      max_val_23:[''],
      min_val_24:[''],
      max_val_24:[''],
      min_val_25:[''],
      max_val_25:[''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddSlab.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      let cond = {
        "conditions[tenant_id]" : tenant_id,
      }
      this.setScheme(cond);
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
    /* this.userservice.getData('', "list-schemes", "POST").subscribe((response: any) => {
      try { 
        //console.log("ssss")
        if (response.status == 'Success') {
          this.schemeList = response.data;
        } else {
          this.schemeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { }); */
    this.AddSlab.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
