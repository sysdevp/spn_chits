import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';

@Component({
  
  selector: 'app-update-collection-area',
  templateUrl: './update-collection-area.component.html',
  styles: []
})

export class UpdateCollectionAreaComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('collection_area') focus3:ElementRef;
  @ViewChild('area_pincode') focus4:ElementRef;     
  
  UpdateCollectionarea: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  collectionAreaId: any;
  branchList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  pincodePattern = "^[0-9]{6}$";

  constructor(private route: ActivatedRoute,private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCollectionarea.controls;
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
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
    const result = Object.assign({}, this.UpdateCollectionarea.value);
    if (this.UpdateCollectionarea.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.collection_area.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.area_pincode.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }

    this.userservice.getData(result,"update-collection-area","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Collection Area", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-collection-area']);
          }, 3000);
        } else {
          this.appService.showAlert("Collection Area", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
        this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Collection Area", "Server Error!!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateCollectionarea = this.formBuilder.group({
      tenant_id:['', Validators.required],
      branch_id: ['', Validators.required],
      collection_area:['', Validators.required],
      area_pincode: ['',[Validators.required,Validators.pattern(this.pincodePattern)]],
      id: [''],
      updated_by: [''],
    });

    let tenant_id = localStorage.getItem('tenant_id');
    this.UpdateCollectionarea.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
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
      this.collectionAreaId = params.id;
      this.UpdateCollectionarea.get('id').setValue(this.collectionAreaId);
      let cond = {
        "conditions[id]" : this.collectionAreaId 
      }
      this.userservice.getData(cond, "get-collection-areas","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCollectionarea.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateCollectionarea.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateCollectionarea.get('collection_area').setValue(response.data[0].collection_area);
            this.UpdateCollectionarea.get('area_pincode').setValue(response.data[0].area_pincode);
            this.setBranchList(response.data[0].tenant_id);
          }
          else {
            this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Collection Area", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  }
}
