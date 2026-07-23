import { Component, OnInit,ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { multiselectRequired } from '../../../../helpers/multiselect-required.validator';

@Component({
  
  selector: 'app-update-collection-area-mapping',
  templateUrl: './update-collection-area-mapping.component.html',
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
export class UpdateCollectionAreaMappingComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('collection_area_id') focus4:ElementRef;

  UpdateCollectionArea: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  branchList: any;
  CollectionAreaId: any;
  isSuperAdmin: boolean;
  companyList: any[];
  employeeList: any[];
  collectionAreaList: any;

  dropdownList = [];
  selectedItems = [];
  dropdownSettings = {};
  collectionAreaName: any;
  AreaList=[];
  list: any;
  Arealist: any[];
  private pendingCollectionAreaSelection: any[] = null;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateCollectionArea.controls;
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
    this.UpdateCollectionArea.get('collection_area_id').setValue([], { emitEvent: false });
    this.pendingCollectionAreaSelection = null;
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
          if (this.pendingCollectionAreaSelection) {
            this.applyCollectionAreaSelection(this.pendingCollectionAreaSelection);
            this.pendingCollectionAreaSelection = null;
          }
        } else {
          this.collectionAreaList = [];
          this.dropdownList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  applyCollectionAreaSelection(saved: any) {
    if (!saved || !Array.isArray(saved) || !this.dropdownList.length) {
      return;
    }
    const normalized = saved.map((item: any) => {
      if (item && item.item_id != null) {
        const found = this.dropdownList.find((d: any) => String(d.item_id) === String(item.item_id));
        return found ? { item_id: found.item_id, item_text: found.item_text } : { item_id: item.item_id, item_text: item.item_text || '' };
      }
      const id = typeof item === 'object' ? (item.id || item.item_id) : item;
      const found = this.dropdownList.find((d: any) => String(d.item_id) === String(id));
      return found ? { item_id: found.item_id, item_text: found.item_text } : null;
    }).filter(Boolean);
    this.UpdateCollectionArea.get('collection_area_id').setValue(normalized, { emitEvent: false });
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateCollectionArea.value);
    if (this.UpdateCollectionArea.invalid) {
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
    this.userservice.getData(result,"update-collection-area-mapping","JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success')   {
          this.appService.showAlert("Collection Area Mapping", response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-collection-area-mapping']);
          }, 3000);
        } else {
          this.appService.showAlert("Collection Area Mapping", response.msg, "error");
          this.disableBtn = false;
        }
      } 
      catch (err) {
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
        
      }
    });
  }

  ngOnInit() {
    this.UpdateCollectionArea = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      collection_area_id: [[], multiselectRequired()],
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
      this.CollectionAreaId = params.id;
      this.UpdateCollectionArea.get('id').setValue(this.CollectionAreaId);
      this.UpdateCollectionArea.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
          "conditions[id]" : this.CollectionAreaId 
      }
      this.userservice.getData(cond, "get-collection-area-mappings","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          {
            this.UpdateCollectionArea.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateCollectionArea.get('branch_id').setValue(response.data[0].branch_id);
            this.UpdateCollectionArea.get('employee_id').setValue(response.data[0].employee_id);
            this.pendingCollectionAreaSelection = response.data[0].collection_area_id;

            this.setBranchList(response.data[0].tenant_id);
            this.setEmployeeList(response.data[0].branch_id);
            this.list = response.data[0].area_list;

            //console.log(this.list)
          }
          else {
            this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {console.log(err)
          this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {console.log(err)
        if (err.status == 401) {
          this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Collection Area Mapping", "Sorry!! Something went wrong!", "error");
        }
      });
    }); 
  

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
