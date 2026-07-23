import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-accounting-group',
  templateUrl: './update-accounting-group.component.html',
  styles: []
})
export class UpdateAccountingGroupComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('group_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;
  @ViewChild('under') focus4:ElementRef;

  UpdateAccountingGroup: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  pettycashexpensetypeId: any;
  underList: any[];

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {
    this.SubmitbuttonName = "Submit";
  }
  get f() {
    return this.UpdateAccountingGroup.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setgroupList(tenant_id);
  }
  setgroupList(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1
    }
    this.userservice.getData(cond, "get-account-group", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.underList = response.data;
        } else {
          this.underList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateAccountingGroup.value);
    if (this.UpdateAccountingGroup.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.group_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.under.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-account-group","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Accounting Group", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-accounting-group']);
          }, 3000);
        } else {
          this.appService.showAlert("Accounting Group", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Accounting Group", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateAccountingGroup = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      group_name: ['', Validators.required],
      under: ['', Validators.required],
      remarks: [''],
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
      this.pettycashexpensetypeId = params.id;
      this.UpdateAccountingGroup.get('id').setValue(this.pettycashexpensetypeId);
      this.UpdateAccountingGroup.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.pettycashexpensetypeId 
      }
      this.userservice.getData(cond, "get-account-group","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateAccountingGroup.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateAccountingGroup.get('group_name').setValue(response.data[0].group_name);
            this.UpdateAccountingGroup.get('under').setValue(response.data[0].under);
            this.UpdateAccountingGroup.get('remarks').setValue(response.data[0].remarks);
            this.setgroupList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Accounting Group", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}

