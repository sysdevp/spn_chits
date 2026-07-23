import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-source-type',
  templateUrl: './update-source-type.component.html',
  styles: []
})
export class UpdateSourceTypeComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('source_type_name') focus2:ElementRef;
  @ViewChild('remarks') focus3:ElementRef;

  UpdateSourceType: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin: boolean;
  companyList: any[];
  sourcetypeId: any;

  constructor(private appService: AppService,private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateSourceType.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
  }  

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateSourceType.value);
    if (this.UpdateSourceType.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.source_type_name.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.remarks.errors) {
        this.focus3.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result,"update-source-type","POST").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Source Type", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-source-type']);
          }, 3000);
        } else {
          this.appService.showAlert("Source Type", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateSourceType = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      source_type_name: ['', Validators.required],
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
      this.sourcetypeId = params.id;
      this.UpdateSourceType.get('id').setValue(this.sourcetypeId);
      let cond = {
        "conditions[id]" : this.sourcetypeId 
      }
      this.userservice.getData(cond, "get-source-types","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
          try {
            if (response.status == 'Success') 
            {
              this.UpdateSourceType.get('tenant_id').setValue(response.data[0].tenant_id);
              this.UpdateSourceType.get('source_type_name').setValue(response.data[0].source_type_name);
              this.UpdateSourceType.get('remarks').setValue(response.data[0].remarks);
            }
            else {
              this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
            }
        } catch (err) {
          this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Source Type", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
