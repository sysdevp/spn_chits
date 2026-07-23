import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../../Services/user.service';

@Component({
  
  selector: 'app-add-payment-slab',
  templateUrl: './add-payment-slab.component.html',
  styles: []
})
export class AddPaymentSlabComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('schemes_id') focus3:ElementRef;
  @ViewChild('payment_1') focus4:ElementRef;
  @ViewChild('payment_2') focus5:ElementRef;
  @ViewChild('payment_3') focus6:ElementRef;
  @ViewChild('payment_4') focus7:ElementRef;
  @ViewChild('payment_5') focus8:ElementRef;
  @ViewChild('payment_6') focus9:ElementRef;
  @ViewChild('payment_7') focus10:ElementRef;
  @ViewChild('payment_8') focus11:ElementRef;
  @ViewChild('payment_9') focus12:ElementRef;
  @ViewChild('payment_10') focus13:ElementRef;
  @ViewChild('payment_11') focus14:ElementRef;
  @ViewChild('payment_12') focus15:ElementRef;
  @ViewChild('payment_13') focus16:ElementRef;
  @ViewChild('payment_14') focus17:ElementRef;
  @ViewChild('payment_15') focus18:ElementRef;
  @ViewChild('payment_16') focus19:ElementRef;
  @ViewChild('payment_17') focus20:ElementRef;
  @ViewChild('payment_18') focus21:ElementRef;
  @ViewChild('payment_19') focus22:ElementRef;
  @ViewChild('payment_20') focus23:ElementRef;
  @ViewChild('payment_21') focus24:ElementRef;
  @ViewChild('payment_22') focus25:ElementRef;
  @ViewChild('payment_23') focus26:ElementRef;
  @ViewChild('payment_24') focus27:ElementRef;
  @ViewChild('payment_25') focus28:ElementRef;
  @ViewChild('payment_26') focus29:ElementRef;
  @ViewChild('payment_27') focus30:ElementRef;
  @ViewChild('payment_28') focus31:ElementRef;
  @ViewChild('payment_29') focus32:ElementRef;
  @ViewChild('payment_30') focus33:ElementRef;
  @ViewChild('payment_31') focus34:ElementRef;
  @ViewChild('payment_32') focus35:ElementRef;
  @ViewChild('payment_33') focus36:ElementRef;
  @ViewChild('payment_34') focus37:ElementRef;
  @ViewChild('payment_35') focus38:ElementRef;
  @ViewChild('payment_36') focus39:ElementRef;
  @ViewChild('payment_37') focus40:ElementRef;
  @ViewChild('payment_38') focus41:ElementRef;
  @ViewChild('payment_39') focus42:ElementRef;
  @ViewChild('payment_40') focus43:ElementRef;
  @ViewChild('payment_41') focus44:ElementRef;
  @ViewChild('payment_42') focus45:ElementRef;
  @ViewChild('payment_43') focus46:ElementRef;
  @ViewChild('payment_44') focus47:ElementRef;
  @ViewChild('payment_45') focus48:ElementRef;
  @ViewChild('payment_46') focus49:ElementRef;
  @ViewChild('payment_47') focus50:ElementRef;
  @ViewChild('payment_48') focus51:ElementRef;
  @ViewChild('payment_49') focus52:ElementRef;
  @ViewChild('payment_50') focus53:ElementRef;
  @ViewChild('slab_id') focus54:ElementRef;

  AddPaymentSlab: FormGroup;
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
  paymentList:boolean=true;
  chitValue:boolean=true;
  branchList: any;
  companyList: any[];
  dueList: any[];
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddPaymentSlab.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
    this.setScheme(tenant_id);
  }

  branchChg(event) {
    this.chitValue=false;
    let tenant_id = this.AddPaymentSlab.get('tenant_id').value;
    let branch_id = event.target.value;
  }
  
  setScheme(tenant_id)
  {
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
    this.paymentList=false;
    let scheme =  this.schemeList.filter((listing: any) => listing.id == event.target.value);
    this.noOfMonths = scheme[0].no_of_months;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPaymentSlab.value);
    if (this.AddPaymentSlab.invalid) {
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
      else if(this.f.payment_1.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.payment_2.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.payment_3.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.payment_4.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.payment_5.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.payment_6.errors) {
        this.focus9.nativeElement.focus();
      }
      else if(this.f.payment_7.errors) {
        this.focus10.nativeElement.focus();
      }
      else if(this.f.payment_8.errors) {
        this.focus11.nativeElement.focus();
      }
      else if(this.f.payment_9.errors) {
        this.focus12.nativeElement.focus();
      }
      else if(this.f.payment_10.errors) {
        this.focus13.nativeElement.focus();
      }
      else if(this.f.payment_11.errors) {
        this.focus14.nativeElement.focus();
      }
      else if(this.f.payment_12.errors) {
        this.focus15.nativeElement.focus();
      }
      else if(this.f.payment_13.errors) {
        this.focus16.nativeElement.focus();
      }
      else if(this.f.payment_14.errors) {
        this.focus17.nativeElement.focus();
      }
      else if(this.f.payment_15.errors) {
        this.focus18.nativeElement.focus();
      }
      else if(this.f.payment_16.errors) {
        this.focus19.nativeElement.focus();
      }
      else if(this.f.payment_17.errors) {
        this.focus20.nativeElement.focus();
      }
      else if(this.f.payment_18.errors) {
        this.focus21.nativeElement.focus();
      }
      else if(this.f.payment_19.errors) {
        this.focus22.nativeElement.focus();
      }
      else if(this.f.payment_20.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.payment_21.errors) {
        this.focus24.nativeElement.focus();
      }
      else if(this.f.payment_22.errors) {
        this.focus25.nativeElement.focus();
      }
      else if(this.f.payment_23.errors) {
        this.focus26.nativeElement.focus();
      }
      else if(this.f.payment_24.errors) {
        this.focus27.nativeElement.focus();
      }
      else if(this.f.payment_25.errors) {
        this.focus28.nativeElement.focus();
      }
      else if(this.f.payment_26.errors) {
        this.focus29.nativeElement.focus();
      }
      else if(this.f.payment_27.errors) {
        this.focus30.nativeElement.focus();
      }
      else if(this.f.payment_28.errors) {
        this.focus31.nativeElement.focus();
      }
      else if(this.f.payment_29.errors) {
        this.focus23.nativeElement.focus();
      }
      else if(this.f.payment_30.errors) {
        this.focus33.nativeElement.focus();
      }
      else if(this.f.payment_31.errors) {
        this.focus34.nativeElement.focus();
      }
      else if(this.f.payment_32.errors) {
        this.focus35.nativeElement.focus();
      }
      else if(this.f.payment_33.errors) {
        this.focus36.nativeElement.focus();
      }
      else if(this.f.payment_34.errors) {
        this.focus37.nativeElement.focus();
      }
      else if(this.f.payment_35.errors) {
        this.focus38.nativeElement.focus();
      }
      else if(this.f.payment_36.errors) {
        this.focus39.nativeElement.focus();
      }
      else if(this.f.payment_37.errors) {
        this.focus40.nativeElement.focus();
      }
      else if(this.f.payment_38.errors) {
        this.focus41.nativeElement.focus();
      }
      else if(this.f.payment_39.errors) {
        this.focus42.nativeElement.focus();
      }
      else if(this.f.payment_40.errors) {
        this.focus43.nativeElement.focus();
      }
      else if(this.f.payment_41.errors) {
        this.focus44.nativeElement.focus();
      }
      else if(this.f.payment_42.errors) {
        this.focus45.nativeElement.focus();
      }
      else if(this.f.payment_43.errors) {
        this.focus46.nativeElement.focus();
      }
      else if(this.f.payment_44.errors) {
        this.focus47.nativeElement.focus();
      }
      else if(this.f.payment_45.errors) {
        this.focus48.nativeElement.focus();
      }
      else if(this.f.payment_46.errors) {
        this.focus49.nativeElement.focus();
      }
      else if(this.f.payment_47.errors) {
        this.focus50.nativeElement.focus();
      }
      else if(this.f.payment_48.errors) {
        this.focus51.nativeElement.focus();
      }
      else if(this.f.payment_49.errors) {
        this.focus52.nativeElement.focus();
      }
      else if(this.f.payment_50.errors) {
        this.focus53.nativeElement.focus();
      }
      else if(this.f.slab_id.errors) {
        this.focus54.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-slab-payment", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Payment Slab",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-payment-slab']);
          }, 300);
        } else {
          this.appService.showAlert("Payment Slab", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Payment Slab", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddPaymentSlab = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      schemes_id:['', Validators.required],
      slab_id:['', Validators.required],
      payment_1:[''],
      payment_2:[''],
      payment_3:[''],
      payment_4:[''],
      payment_5:[''],
      payment_6:[''],
      payment_7:[''],
      payment_8:[''],
      payment_9:[''],
      payment_10:[''],
      payment_11:[''],
      payment_12:[''],
      payment_13:[''],
      payment_14:[''],
      payment_15:[''],
      payment_16:[''],
      payment_17:[''],
      payment_18:[''],
      payment_19:[''],
      payment_20:[''],
      payment_21:[''],
      payment_22:[''],
      payment_23:[''],
      payment_24:[''],
      payment_25:[''],
      payment_26:[''],
      payment_27:[''],
      payment_28:[''],
      payment_29:[''],
      payment_30:[''],
      payment_31:[''],
      payment_32:[''],
      payment_33:[''],
      payment_34:[''],
      payment_35:[''],
      payment_36:[''],
      payment_37:[''],
      payment_38:[''],
      payment_39:[''],
      payment_40:[''],
      payment_41:[''],
      payment_42:[''],
      payment_43:[''],
      payment_44:[''],
      payment_45:[''],
      payment_46:[''],
      payment_47:[''],
      payment_48:[''],
      payment_49:[''],
      payment_50:[''],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddPaymentSlab.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
      this.setScheme(tenant_id);
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
    this.userservice.getData("", "list-slab-dues", "POST").subscribe((response: any) => {
      try { 
          if (response.status == 'Success') {
              this.dueList = response.data;
          } else {
              this.dueList = [];
          }
      }  catch (err) { }
  }, (err: any) => { });
    this.AddPaymentSlab.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
