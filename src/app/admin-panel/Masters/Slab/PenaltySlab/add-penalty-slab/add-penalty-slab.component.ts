import { Component, OnInit, ElementRef,ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../../Services/user.service';

@Component({
  
  selector: 'app-add-penalty-slab',
  templateUrl: './add-penalty-slab.component.html',
  styles: []
})

export class AddPenaltySlabComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('scheme_id') focus3:ElementRef;
  @ViewChild('level_1_percentage') focus4:ElementRef;
  @ViewChild('level1_from') focus5:ElementRef;
  @ViewChild('level1_to') focus6:ElementRef;
  @ViewChild('level_2_percentage') focus7:ElementRef;
  @ViewChild('level2_from') focus8:ElementRef;
  @ViewChild('level2_to') focus9:ElementRef;
  @ViewChild('level_3_percentage') focus10:ElementRef;
  @ViewChild('level3_from') focus11:ElementRef;
  @ViewChild('level3_to') focus12:ElementRef;
  @ViewChild('level_4_percentage') focus13:ElementRef;
  @ViewChild('level4_from') focus14:ElementRef;
  @ViewChild('level4_to') focus15:ElementRef;
  @ViewChild('level_5_percentage') focus16:ElementRef;
  @ViewChild('level5_from') focus17:ElementRef;
  @ViewChild('level5_to') focus18:ElementRef;

  AddPenaltySlab: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  branchList: any[];
  schemeList: any[];
  numOnlyPattern = "^[0-9]*$";
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  penaltySlab:boolean=true;
  scheme:boolean=true;
  companyList: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router)
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddPenaltySlab.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.scheme=false;
    this. penaltySlab=true;
    this.setSchemeList(tenant_id);
  }

  schemeChg(event)
  {
    this.scheme=false;
    this. penaltySlab=false;
  }

  setSchemeList(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
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
  
  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPenaltySlab.value);
    if (this.AddPenaltySlab.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
            
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.scheme_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.level_1_percentage.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.level1_from.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.level1_to.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.level_2_percentage.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.level2_from.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.level2_to.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.level_3_percentage.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.level3_from.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.level3_to.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.level_4_percentage.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.level4_from.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.level4_to.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.level_5_percentage.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.level5_from.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.level5_to.errors) {
        this.focus18.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result, "store-slab-penalty", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Penalty Slab",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-penalty-slab']);
          }, 300);
        } else {
          this.appService.showAlert("Penalty Slab", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Penalty Slab", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddPenaltySlab = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      scheme_id:  ['', Validators.required],
      level_1_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      level1_from: ['', [Validators.pattern(this.numOnlyPattern)]],
      level1_to: ['', [Validators.pattern(this.numOnlyPattern)]],
      level_2_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      level2_from: ['', [Validators.pattern(this.numOnlyPattern)]],
      level2_to: ['', [Validators.pattern(this.numOnlyPattern)]],
      level_3_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      level3_from: ['', [Validators.pattern(this.numOnlyPattern)]],
      level3_to: ['', [Validators.pattern(this.numOnlyPattern)]],
      level_4_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      level4_from: ['', [Validators.pattern(this.numOnlyPattern)]],
      level4_to: ['', [Validators.pattern(this.numOnlyPattern)]],
      level_5_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      level5_from: ['', [Validators.pattern(this.numOnlyPattern)]],
      level5_to: ['', [Validators.pattern(this.numOnlyPattern)]],
      created_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddPenaltySlab.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      this.setSchemeList(tenant_id);
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
    this.AddPenaltySlab.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
