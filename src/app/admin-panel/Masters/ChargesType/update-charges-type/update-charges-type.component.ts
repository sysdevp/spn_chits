import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-charges-type',
  templateUrl: './update-charges-type.component.html',
  styles: []
})

export class UpdateChargesTypeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('charge_name') focus2:ElementRef;

  UpdateChargesType: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  chargeTypeId: any;
  isSuperAdmin: boolean;
  companyList: any[];

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateChargesType.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateChargesType.value);
    if (this.UpdateChargesType.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.charge_name.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-charge-type","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Charges Type", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-charges-type']);
          }, 3000);
        } else {
          this.appService.showAlert("Charges Type", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateChargesType = this.formBuilder.group({
      tenant_id:['', Validators.required],
      charge_name:['', Validators.required],
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
      this.chargeTypeId = params.id;
      this.UpdateChargesType.get('id').setValue(this.chargeTypeId);
      let cond = {
        "conditions[id]" : this.chargeTypeId 
      }
      this.userservice.getData(cond, "get-charge-types","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateChargesType.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateChargesType.get('charge_name').setValue(response.data[0].charge_name);
          }
          else {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
        }
        else {
            this.appService.showAlert("Charges Type", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
