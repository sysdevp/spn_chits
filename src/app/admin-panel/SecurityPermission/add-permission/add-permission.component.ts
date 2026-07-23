import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { PermissionSection, SIDEBAR_PERMISSION_SECTIONS } from '../../../config/sidebar-permission.config';
import {
  buildPermissionFormSections,
  flattenSectionsForSave,
  getSubmenuFormIndex,
  PermissionFormSection,
} from '../permission-tree.helper';

@Component({
  
  selector: 'app-add-permission',
  templateUrl: './add-permission.component.html',
  styles: []
})

export class AddPermissionComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('role_id') focus2:ElementRef;

  AddPermission: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  roleList: any[];
  roleChg: boolean=false;
  sidebarSections: PermissionSection[] = SIDEBAR_PERMISSION_SECTIONS;

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  companyChg(event) {
    let tenant_id = event.target.value;
    this.setRoleList(tenant_id);
    this.roleChg = false;
  }

  setRoleList(tenant_id) {
    let cond = {
      "conditions[tenant_id]" : tenant_id,
    }
    this.userservice.getData(cond, "get-roles-for-permission", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.roleList = response.data;
        } else {
          this.roleList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  addPermissionList(listCount) {
    const permArray = < FormArray > this.AddPermission.get('Permissions');
    permArray.controls = [];
    for(let i=0; i<(listCount); i++) {
      this.addPermission();
      this.roleChg = true;
    } 
  }

  addSubMenuList(listCount,index) {
    const subMenuArray = (< FormArray > this.AddPermission.get('Permissions')).at(index).get('Submenus') as FormArray;
    subMenuArray.controls = [];
    for(let i=0; i<(listCount); i++) {
      this.addSubmenu(index);
    } 
  }

  loadPermissionForm() {
    const sections = buildPermissionFormSections();
    this.addPermissionList(sections.length);
    this.setPermissionList(sections, () => {
      this.roleChg = true;
    });
  }

  setPermissionList(dataList: PermissionFormSection[], onComplete?: () => void) {
    const permArray = <FormArray>this.AddPermission.get('Permissions');
    let pendingSubmenus = 0;
    const done = () => {
      if (pendingSubmenus <= 0 && onComplete) {
        onComplete();
      }
    };
    for (let i = 0; i < dataList.length; i++) {
      permArray.controls[i].get('menu_id').setValue(dataList[i].menu_id);
      permArray.controls[i].get('menu_title').setValue(dataList[i].menu_title);
      permArray.controls[i].get('page_type').setValue(dataList[i].page_type);
      permArray.controls[i].get('has_sub_menu').setValue(dataList[i].has_sub_menu);
      permArray.controls[i].get('add_perm').setValue(dataList[i].add_perm);
      permArray.controls[i].get('edit_perm').setValue(dataList[i].edit_perm);
      permArray.controls[i].get('view_perm').setValue(dataList[i].view_perm);
      permArray.controls[i].get('delete_perm').setValue(dataList[i].delete_perm);
      if (dataList[i].Submenus && dataList[i].Submenus.length > 0) {
        pendingSubmenus++;
        this.addSubMenuList(dataList[i].Submenus.length, i);
        const subs = dataList[i].Submenus;
        setTimeout(() => {
          this.setSubMenuList(subs, i);
          pendingSubmenus--;
          done();
        }, 0);
      }
    }
    done();
  }

  setSubMenuList(dataList: any[], index: number) {
    const subMenuArray = (<FormArray>this.AddPermission.get('Permissions')).at(index).get('Submenus') as FormArray;
    for (let j = 0; j < dataList.length; j++) {
      subMenuArray.controls[j].get('menu_id').setValue(dataList[j].menu_id);
      subMenuArray.controls[j].get('menu_title').setValue(dataList[j].menu_title);
      subMenuArray.controls[j].get('page_type').setValue(dataList[j].page_type);
      subMenuArray.controls[j].get('has_sub_menu').setValue(dataList[j].has_sub_menu || 0);
      subMenuArray.controls[j].get('storage_module_id').setValue(dataList[j].storage_module_id);
      subMenuArray.controls[j].get('add_perm').setValue(dataList[j].add_perm);
      subMenuArray.controls[j].get('edit_perm').setValue(dataList[j].edit_perm);
      subMenuArray.controls[j].get('view_perm').setValue(dataList[j].view_perm);
      subMenuArray.controls[j].get('delete_perm').setValue(dataList[j].delete_perm);
      subMenuArray.controls[j].get('top_perm').setValue(dataList[j].top_perm);
    }
  }

  getSubmenuIndex(sectionIndex: number, menuId: number, title?: string): number {
    const subMenuArray = (<FormArray>this.AddPermission.get('Permissions')).at(sectionIndex).get('Submenus') as FormArray;
    return getSubmenuFormIndex(subMenuArray, menuId, title);
  }

  get f() {
    return this.AddPermission.controls;
  }

  addPermission() { 
    const control = <FormArray>this.AddPermission.controls['Permissions'];
    control.push(this.initPermission());
  }

  addSubmenu(index) { 
    const control = (<FormArray>this.AddPermission.controls['Permissions']).at(index).get('Submenus') as FormArray;
    control.push(this.initSubmenu());
  }

  initPermission() {
    return this.formBuilder.group({
      'menu_id': [''],
      'menu_title': [''],
      'page_type': [''],
      'has_sub_menu': [''],
      'add_perm': [''],
      'edit_perm': [''],
      'view_perm': [''],
      'delete_perm': [''],
      'storage_module_id': [''],
      'Submenus': this.formBuilder.array([]),
    }); 
  }

  initSubmenu() {
    return this.formBuilder.group({
      'menu_id': [''],
      'menu_title': [''],
      'page_type': [''],
      'has_sub_menu': [''],
      'storage_module_id': [''],
      'add_perm': [''],
      'edit_perm': [''],
      'view_perm': [''],
      'delete_perm': [''],
      'top_perm': ['']
    });
  }

  get Permissions() {
    return <FormArray>this.AddPermission.get('Permissions');
  }

  allMenuChg(event) {
    let menuArray = <FormArray>this.AddPermission.controls['Permissions'];
    if(event.target.checked) {
      for(let i=0;i<(menuArray.length);i++) {
        menuArray.controls[i].get('add_perm').setValue(1);
        menuArray.controls[i].get('edit_perm').setValue(1);
        menuArray.controls[i].get('view_perm').setValue(1);
        menuArray.controls[i].get('delete_perm').setValue(1);
        let subMenuArray = (<FormArray>this.AddPermission.controls['Permissions']).at(i).get('Submenus') as FormArray;
        for(let j=0; j<(subMenuArray.length); j++) {
          subMenuArray.controls[j].get('add_perm').setValue(1);
          subMenuArray.controls[j].get('edit_perm').setValue(1);
          subMenuArray.controls[j].get('view_perm').setValue(1);
          subMenuArray.controls[j].get('delete_perm').setValue(1);
          subMenuArray.controls[j].get('top_perm').setValue(1);
        }
      }
      let chkList = document.getElementsByClassName('main-chk');
      for(let k=0;k<(chkList.length);k++) {
        (<HTMLInputElement>chkList[k]).checked = true;
        //chkList[k].setAttribute('checked','true');
      }
    }
    else {
      for(let i=0;i<(menuArray.length);i++) {
        menuArray.controls[i].get('add_perm').setValue(0);
        menuArray.controls[i].get('edit_perm').setValue(0);
        menuArray.controls[i].get('view_perm').setValue(0);
        menuArray.controls[i].get('delete_perm').setValue(0);
        let subMenuArray = (<FormArray>this.AddPermission.controls['Permissions']).at(i).get('Submenus') as FormArray;
        for(let j=0; j<(subMenuArray.length); j++) {
          subMenuArray.controls[j].get('add_perm').setValue(0);
          subMenuArray.controls[j].get('edit_perm').setValue(0);
          subMenuArray.controls[j].get('view_perm').setValue(0);
          subMenuArray.controls[j].get('delete_perm').setValue(0);
          subMenuArray.controls[j].get('top_perm').setValue(0);
        }
      }
      let chkList = document.getElementsByClassName('main-chk');
      for(let k=0;k<(chkList.length);k++) { 
        (<HTMLInputElement>chkList[k]).checked = false;
        //chkList[k].setAttribute('checked','false');
      }
    }
  }

  mainMenuChg(event,index) {
    let menuArray = <FormArray>this.AddPermission.controls['Permissions'];
    let subMenuArray = (<FormArray>this.AddPermission.controls['Permissions']).at(index).get('Submenus') as FormArray;
    if(event.target.checked) {
      menuArray.controls[index].get('add_perm').setValue(1);
      menuArray.controls[index].get('edit_perm').setValue(1);
      menuArray.controls[index].get('view_perm').setValue(1);
      menuArray.controls[index].get('delete_perm').setValue(1);
      for(let i=0; i<(subMenuArray.length); i++) {
        subMenuArray.controls[i].get('add_perm').setValue(1);
        subMenuArray.controls[i].get('edit_perm').setValue(1);
        subMenuArray.controls[i].get('view_perm').setValue(1);
        subMenuArray.controls[i].get('delete_perm').setValue(1);
        subMenuArray.controls[i].get('top_perm').setValue(1);
      }
    }
    else {
      menuArray.controls[index].get('add_perm').setValue(0);
      menuArray.controls[index].get('edit_perm').setValue(0);
      menuArray.controls[index].get('view_perm').setValue(0);
      menuArray.controls[index].get('delete_perm').setValue(0);
      for(let i=0; i<(subMenuArray.length); i++) {
        subMenuArray.controls[i].get('add_perm').setValue(0);
        subMenuArray.controls[i].get('edit_perm').setValue(0);
        subMenuArray.controls[i].get('view_perm').setValue(0);
        subMenuArray.controls[i].get('delete_perm').setValue(0);
        subMenuArray.controls[i].get('top_perm').setValue(0);
      }
    }
  }

  subMainChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.AddPermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('add_perm').setValue(1);
      permissionArray.controls[subIndex].get('edit_perm').setValue(1);
      permissionArray.controls[subIndex].get('view_perm').setValue(1);
      permissionArray.controls[subIndex].get('delete_perm').setValue(1);
      permissionArray.controls[subIndex].get('top_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('add_perm').setValue(0);
      permissionArray.controls[subIndex].get('edit_perm').setValue(0);
      permissionArray.controls[subIndex].get('view_perm').setValue(0);
      permissionArray.controls[subIndex].get('delete_perm').setValue(0);
      permissionArray.controls[subIndex].get('top_perm').setValue(0);
    }
  }

  addPermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.AddPermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('add_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('add_perm').setValue(0);
    }
  }

  editPermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.AddPermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('edit_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('edit_perm').setValue(0);
    }
  }

  viewPermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.AddPermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('view_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('view_perm').setValue(0);
    }
  }

  deletePermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.AddPermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('delete_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('delete_perm').setValue(0);
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddPermission.value);
    result.Permissions = flattenSectionsForSave(result.Permissions || []);
    if (this.AddPermission.invalid) {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.role_id.errors) {
        this.focus2.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-permission", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Security Permission",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/update-permission']);
          }, 300);
        } else {
          this.appService.showAlert("Security Permission", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Security Permission", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Security Permission", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Security Permission", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.AddPermission = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      role_id: ['', Validators.required],
      created_by: [''],
      'Permissions': this.formBuilder.array([
        this.formBuilder.group({
          'menu_id': [''],
          'menu_title': [''],
          'page_type': [''],
          'has_sub_menu': [''],
          'add_perm': [''],
          'edit_perm': [''],
          'view_perm': [''],
          'delete_perm': [''],
          'Submenus': this.formBuilder.array([
            this.formBuilder.group({
              'menu_id': [''],
              'menu_title': [''],
              'page_type': [''],
              'has_sub_menu': [''],
              'add_perm': [''],
              'edit_perm': [''],
              'view_perm': [''],
              'delete_perm': [''],
              'top_perm': ['']
            })
          ]),
        })
      ]),
    });
    let tenant_id = localStorage.getItem('tenant_id');
    let cond = {};
    if(tenant_id) {
      this.AddPermission.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      cond = {
        "conditions[tenant_id]" : tenant_id,
      }
      this.setRoleList(tenant_id);
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
    this.loadPermissionForm();
    this.AddPermission.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
