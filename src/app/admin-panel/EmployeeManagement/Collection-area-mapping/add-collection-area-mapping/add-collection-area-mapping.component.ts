import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { multiselectRequired } from '../../../../helpers/multiselect-required.validator';

@Component({
  
  selector: 'app-add-collection-area-mapping',
  templateUrl: './add-collection-area-mapping.component.html',
  styles: [`
    :host ::ng-deep .collection-area-mapping-card {
      overflow: visible;
    }
    :host ::ng-deep .card-body.add-maze {
      overflow: visible;
      padding-bottom: 2rem;
    }
    :host ::ng-deep .collection-area-multiselect .multiselect-dropdown {
      position: relative;
      z-index: 1;
      width: 100%;
    }
    :host ::ng-deep .collection-area-multiselect .multiselect-dropdown .dropdown-list {
      z-index: 10050;
    }
  `]
})
export class AddCollectionAreaMappingComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('collection_area_id') focus4:ElementRef;

  AddCollectionArea: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  isSuperAdmin: boolean;
  companyList: any[];
  employeeList: any[];
  collectionAreaList: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddCollectionArea.controls;
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
  branchChg(event) {
    let branch_id = event.target.value;
    this.AddCollectionArea.get('collection_area_id').setValue([], { emitEvent: false });
    this.dropdownList = [];
    this.setEmployeeList(branch_id);
  }

  setEmployeeList(branch_id)
  {
    if (!branch_id) {
      this.employeeList = [];
      this.collectionAreaList = [];
      this.dropdownList = [];
      return;
    }
    let cond = {
      "conditions[branch_id]" : branch_id
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
   
    this.userservice.getData(cond, "get-collection-areas", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.collectionAreaList = response.data;
          this.dropdownList = (response.data || []).map((row: any) => ({
            item_id: row.id,
            item_text: row.collection_area
          }));
        } else {
          this.collectionAreaList = [];
          this.dropdownList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddCollectionArea.value);
    if (this.AddCollectionArea.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.collection_area_id.errors) {
        this.focus4.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-collection-area-mapping", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Collection Area",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-collection-area-mapping']);
          }, 300);
        } else {
          this.appService.showAlert("Collection Area Mapping", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddCollectionArea = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      collection_area_id: [[], multiselectRequired()],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    if(tenant_id) {
      this.AddCollectionArea.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      this.setBranchList(tenant_id);
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
    this.AddCollectionArea.get('created_by').setValue(localStorage.getItem('logged_user_id'));
    this.dropdownSettings = {
      singleSelection: false,
      idField: 'item_id',
      textField: 'item_text',
      selectAllText: 'Select All',
      unSelectAllText: 'UnSelect All',
      itemsShowLimit: 3,
      allowSearchFilter: true,
      enableCheckAll: true,
      maxHeight: 220
    };
  } 
}

