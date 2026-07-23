import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../../Services/user.service';

@Component({
  
  selector: 'app-update-penalty-slab',
  templateUrl: './update-penalty-slab.component.html',
  styles: []
})

export class UpdatePenaltySlabComponent implements OnInit {
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

    form_message = true;
    UpdateSlabPenalty: FormGroup;
    submitted = false;
    disableBtn = false;
    SubmitbuttonName: string;
    branchList: any[];
    penaltySlabId: any;
    schemeList: any[];
    noOfMonths: any;
    isSuperAdmin: boolean;
    companyList: any[];
    numOnlyPattern = "^[0-9]*$";
    decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
 
    constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
    {       
        this.SubmitbuttonName = "Submit";
    }

    companyChg(event) 
    {
        let tenant_id = event.target.value;
        this.setBranchList(tenant_id);
        this.setSchemeList(tenant_id);
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

    schemeChg(event) 
    {
        let cond = {
            "conditions[branch_id]" : event.target.value
        }
        this.setChitValue(cond);
    }

    setChitValue(cond) {
        this.userservice.getData(cond ,"list-schemes", "POST").subscribe((response: any) => {
            try {
                if (response.status == 'Success') {
                    this.schemeList = response.data; 
                    let schemeId = this.UpdateSlabPenalty.get('schemes_id').value;
                    let scheme =  (response.data).filter((listing: any) => listing.id == schemeId);
                    this.noOfMonths = scheme[0].no_of_months;
                
                } else {
                    this.schemeList = [];
                }
            } catch (err) { }
        }, (err: any) => {});
    }

    get f() {
        return this.UpdateSlabPenalty.controls;
    }

    onSubmit() {
        this.SubmitbuttonName = "Loading...";
        this.submitted = true;
        this.disableBtn = true;
        const result = Object.assign({}, this.UpdateSlabPenalty.value);
        if (this.UpdateSlabPenalty.invalid) 
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
        this.userservice.getData(result,"update-slab-penalty","POST").subscribe((response: any) => {
            this.SubmitbuttonName = "Submit";
            try {
                if (response.status == 'Success')   {
                    this.appService.showAlert("Penalty Slab", response.msg, "success");
                    setTimeout(() => {
                        this.router.navigate(['/admin-panel/view-penalty-slab']);
                    }, 3000);
                } else {
                    this.appService.showAlert("Penalty Slab", response.msg, "error");
                    this.disableBtn = false;
                }
            } 
            catch (err) {
                this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
                this.disableBtn = false;
            }
        }, (err: any) => {
            this.disableBtn = false;
            this.SubmitbuttonName = "Submit";
            if (err.status == 401) {
                this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
            }
            else {
                this.appService.showAlert("Penalty Slab", "Server Error!!", "error");
            }
        });
    }

    ngOnInit() {
        this.UpdateSlabPenalty = this.formBuilder.group({
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
            id: [''],
            updated_by: [''],
        });
        
        let tenant_id = localStorage.getItem('tenant_id');
        if(tenant_id) {
            this.isSuperAdmin = false;
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

        this.route.params.subscribe(params => {
            this.penaltySlabId = params.id;
            this.UpdateSlabPenalty.get('id').setValue(this.penaltySlabId);
            let cond = {
                "conditions[id]" : this.penaltySlabId 
            }
            this.userservice.getData(cond, "get-slab-penalties","POST").subscribe((response: any) => {
                this.SubmitbuttonName = "Submit";
                try {
                    if (response.status == 'Success') 
                    {
                        this.UpdateSlabPenalty.get('tenant_id').setValue(response.data[0].tenant_id);
                        this.UpdateSlabPenalty.get('branch_id').setValue(response.data[0].branch_id);
                        this.UpdateSlabPenalty.get('scheme_id').setValue(response.data[0].scheme_id);
                        this.UpdateSlabPenalty.get('level_1_percentage').setValue(response.data[0].level_1_percentage);
                        this.UpdateSlabPenalty.get('level1_from').setValue(response.data[0].level1_from);
                        this.UpdateSlabPenalty.get('level1_to').setValue(response.data[0].level1_to);
                        this.UpdateSlabPenalty.get('level_2_percentage').setValue(response.data[0].level_2_percentage);
                        this.UpdateSlabPenalty.get('level2_from').setValue(response.data[0].level2_from);
                        this.UpdateSlabPenalty.get('level2_to').setValue(response.data[0].level2_to);
                        this.UpdateSlabPenalty.get('level_3_percentage').setValue(response.data[0].level_3_percentage);
                        this.UpdateSlabPenalty.get('level3_from').setValue(response.data[0].level3_from);
                        this.UpdateSlabPenalty.get('level3_to').setValue(response.data[0].level3_to);
                        this.UpdateSlabPenalty.get('level_4_percentage').setValue(response.data[0].level_4_percentage);
                        this.UpdateSlabPenalty.get('level4_from').setValue(response.data[0].level4_from);
                        this.UpdateSlabPenalty.get('level4_to').setValue(response.data[0].level4_to);
                        this.UpdateSlabPenalty.get('level_5_percentage').setValue(response.data[0].level_5_percentage);
                        this.UpdateSlabPenalty.get('level5_from').setValue(response.data[0].level5_from);
                        this.UpdateSlabPenalty.get('level5_to').setValue(response.data[0].level5_to);
                        this.setBranchList(response.data[0].tenant_id);
                        let cond = {
                            "conditions[branch_id]" : response.data[0].branch_id
                        }
                        this.setChitValue(cond); 
                    }
                    else {
                        this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
                    }
                } catch (err) {
                    this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
                }
            }, (err: any) => {
                if (err.status == 401) {
                    this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
                }
                else {
                    this.appService.showAlert("Penalty Slab", "Sorry!! Something went wrong!", "error");
                }
            });
        }); 
    }
}

