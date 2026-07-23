import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-add-group',
  templateUrl: './add-group.component.html',
  styles: []
})

export class AddGroupComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('group_type') focus3:ElementRef;
  @ViewChild('group_name') focus4:ElementRef;
  @ViewChild('scheme_id') focus5:ElementRef;
  @ViewChild('auction_date') focus6:ElementRef;
  @ViewChild('auction_time') focus7:ElementRef;
  @ViewChild('starting_date') focus8:ElementRef;
  @ViewChild('first_due_date') focus9:ElementRef;
  @ViewChild('commission_percent') focus10:ElementRef;
  @ViewChild('fd_number') focus11:ElementRef;
  @ViewChild('total_fd') focus12:ElementRef;
  @ViewChild('company_fd') focus13:ElementRef;
  @ViewChild('rate_of_interest') focus14:ElementRef;
  @ViewChild('fd_rate') focus15:ElementRef;
  @ViewChild('fd_date') focus16:ElementRef;
  @ViewChild('maturity_amount') focus17:ElementRef;
  @ViewChild('fd_bank_id') focus18:ElementRef;
  @ViewChild('fd_branch') focus19:ElementRef;
  @ViewChild('pso_number') focus20:ElementRef;
  @ViewChild('pso_date') focus21:ElementRef;
  @ViewChild('blaw_number') focus22:ElementRef;
  @ViewChild('blaw_date') focus23:ElementRef;
  @ViewChild('cheque_no') focus24:ElementRef;
  @ViewChild('company_chit') focus25:ElementRef;
  @ViewChild('group_base_penalty') focus26:ElementRef;
  @ViewChild('group_base_bonus') focus27:ElementRef;
  @ViewChild('group_base_interest') focus28:ElementRef;
  @ViewChild('group_base_dividend_cancel_days') focus29:ElementRef;
  @ViewChild('group_penalty_days') focus30:ElementRef;
  @ViewChild('group_bonus_days') focus31:ElementRef;
  @ViewChild('group_base_incentive_percentage') focus32:ElementRef;
  @ViewChild('fd_renewal_date') focus33:ElementRef;
  @ViewChild('commit_date') focus34:ElementRef;
  @ViewChild('sms') focus35:ElementRef;

  AddGroup: FormGroup;
  submitted = false;
  disableBtn = false;
  changeText = false;
  changeText1 = false;
  changeText2 = false;
  changeText3 = false;
  changeText4 = false;
  changeText5 = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  tenantbased:boolean;
  companyList: any[];
  branchList: any[];
  schemeList: any[];
  bankList: any[];
  auctionDateAlert: boolean;
  decimalPattern = "^[0-9]+(.[0-9]{0,4})?$";
  numOnlyPattern = "^[0-9]*$";  
  tenant_id: any;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddGroup.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setSchemeList(tenant_id);
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

  checkValue(event){
    let sms = event.target.checked;
    console.log(event.target.checked);
    if(sms == true){
      this.AddGroup.get('status').setValue(2);
    }
    else{
      this.AddGroup.get('status').setValue(1);
    }
 }

  branchChg(event) {
    let tenant_id = this.AddGroup.get('tenant_id').value;
    let branch_id = event.target.value;
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "list-banks", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.bankList = response.data;
        } else {
          this.bankList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
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

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddGroup.value); 
    if (this.AddGroup.invalid) 
    { 
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.auctionDateAlert = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.group_type.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.group_name.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.scheme_id.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.auction_date.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.commission_percent.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.fd_number.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.total_fd.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.company_fd.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.rate_of_interest.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.fd_rate.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.maturity_amount.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.fd_branch.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.pso_number.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.company_chit.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.group_base_penalty.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.group_base_bonus.errors) {
        this.focus27.nativeElement.focus();
      }
      else if(this.f.group_base_interest.errors) {
        this.focus28.nativeElement.focus();
      }
      else if(this.f.group_base_dividend_cancel_days.errors) {
        this.focus29.nativeElement.focus();
      }
      else if(this.f.group_penalty_days.errors) {
        this.focus30.nativeElement.focus();
      }
      else if(this.f.group_bonus_days.errors) {
        this.focus31.nativeElement.focus();
      }
      else if(this.f.group_base_incentive_percentage.errors) {
        this.focus32.nativeElement.focus();
      }
      return;
    } 
    if((this.f.auction_date.value>31) || (this.f.auction_date.value<1))  {
      this.auctionDateAlert = true;
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      this.focus6.nativeElement.focus();
    }
    else {
      this.userservice.getData(result, "store-group", "JSON").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') {
            this.appService.showAlert("Group",response.msg, "success");
            setTimeout(() => {
              this.router.navigate(['/admin-panel/view-group']);
            }, 300);
          } else {
            this.appService.showAlert("Group", response.msg, "error");
            this.disableBtn = false;
          }
        } catch (err) {
          this.appService.showAlert("Group", 'Sorry!! Something went wrong!', "error");
          this.disableBtn = false;
        }
      }, (err: any) => {
        this.disableBtn = false;
        this.SubmitbuttonName = "Submit";
        if (err.status == 401) {
          this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Group", "Sorry!! Something went wrong!", "error");
        }
      });
    }
  }

  ngOnInit() {
    this.AddGroup = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      group_type: ['', [Validators.required]],
      group_name: ['', [Validators.required]],
      scheme_id: ['', [Validators.required]],
      auction_date: ['', [Validators.required,Validators.pattern(this.numOnlyPattern)]],
      auction_date1: [''],
      auction_time: [''],
      auction_time1: [''],
      max_ticket_count: ['', [Validators.pattern(this.decimalPattern)]],
      starting_date: [null, [Validators.required]],
      first_due_date: [null],
      commission_percent: ['', [Validators.required,Validators.pattern(this.decimalPattern)]],
      fd_number: [''],
      total_fd: ['', [Validators.pattern(this.decimalPattern)]],
      company_fd: ['', [Validators.pattern(this.decimalPattern)]],
      rate_of_interest: ['', [Validators.pattern(this.decimalPattern)]],
      fd_rate: ['', [Validators.pattern(this.decimalPattern)]],
      fd_date: [null],
      maturity_amount: ['', [Validators.pattern(this.decimalPattern)]],
      fd_bank_id: [''],
      fd_branch: [''],
      pso_number: [''],
      pso_date: [null],
      blaw_number: [''],
      blaw_date: [null],
      cheque_no: [''],
      company_chit: ['', [Validators.pattern(this.numOnlyPattern)]],
      group_base_penalty: ['', [Validators.pattern(this.decimalPattern)]],
      group_base_bonus: ['', [Validators.pattern(this.decimalPattern)]],
      group_base_interest: ['', [Validators.pattern(this.decimalPattern)]],
      group_base_dividend_cancel_days: ['', [Validators.pattern(this.numOnlyPattern)]],
      group_penalty_days: ['', [Validators.pattern(this.numOnlyPattern)]],
      group_bonus_days: ['', [Validators.pattern(this.numOnlyPattern)]],
      group_base_incentive_percentage: ['', [Validators.pattern(this.decimalPattern)]],
      fd_renewal_date: [null],
      commit_date: [null],
      status: [''],
      created_by: [''],
      sms: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    this.tenant_id = localStorage.getItem('tenant_id');
    console.log(this.tenant_id);
    if(this.tenant_id == 18){
      this.tenantbased = false;
    }
    else{
      this.tenantbased = true;
    }
    this.auctionDateAlert = false;
    if(tenant_id) {
      this.AddGroup.get('tenant_id').setValue(tenant_id);
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
    this.AddGroup.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
