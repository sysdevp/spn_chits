import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-designation',
  templateUrl: './update-designation.component.html',
  styles: []
})

export class UpdateDesignationComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('designation_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  UpdateDesignation: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  designationId: any;
  isSuperAdmin: boolean;
  companyList: any[];

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateDesignation.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateDesignation.value);
    if (this.UpdateDesignation.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.designation_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-designation","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Designation", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-designation']);
          }, 3000);
        } else {
          this.appService.showAlert("Designation", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateDesignation = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      designation_name: ['', Validators.required],
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
      this.designationId = params.id;
      this.UpdateDesignation.get('id').setValue(this.designationId);
      let cond = {
        "conditions[id]" : this.designationId 
      }
      this.userservice.getData(cond, "get-designations","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') 
            {
              this.UpdateDesignation.get('tenant_id').setValue(response.data[0].tenant_id);
              this.UpdateDesignation.get('designation_name').setValue(response.data[0].designation_name);
              this.UpdateDesignation.get('remarks').setValue(response.data[0].remarks);
            }
            else {
              this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
            }
        } catch (err) {
          this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Designation", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
