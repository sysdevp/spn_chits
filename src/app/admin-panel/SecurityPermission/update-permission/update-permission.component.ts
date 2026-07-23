import { Component, OnInit ,ElementRef,ViewChild} from '@angular/core';
import { FormGroup, FormArray, FormBuilder, Validators, FormControl } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';
import { PermissionSection, SIDEBAR_PERMISSION_SECTIONS } from '../../../config/sidebar-permission.config';
import {
  buildPermissionFormSections,
  flattenSectionsForSave,
  findSavedSubmenu,
  getSubmenuFormIndex,
  PermissionFormSection,
} from '../permission-tree.helper';

@Component({
  
  selector: 'app-update-permission',
  templateUrl: './update-permission.component.html',
  styles: []
})

export class UpdatePermissionComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('role_id') focus2:ElementRef;

  UpdatePermission: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  roleList: any[];
  roleChg: boolean=false;
  hidePermission: boolean=true;
  sidebarSections: PermissionSection[] = SIDEBAR_PERMISSION_SECTIONS;

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
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
      "for_edit" : 1,
      "role_id" : localStorage.getItem('role_id'),
      "is_super_admin" : this.isSuperAdmin,
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

  roleChgFn(event) {
    if (!event.target.value) {
      this.hidePermission = true;
      return;
    }
    const sections = buildPermissionFormSections();
    this.addPermissionList(sections.length);
    this.setPermissionList(sections, () => this.assignPermissionList());
  }

  loadPermissionForm() {
    const sections = buildPermissionFormSections();
    this.addPermissionList(sections.length);
    this.setPermissionList(sections, () => {
      this.roleChg = true;
    });
  }

  getSubmenuIndex(sectionIndex: number, menuId: number, title?: string): number {
    const subMenuArray = (<FormArray>this.UpdatePermission.get('Permissions')).at(sectionIndex).get('Submenus') as FormArray;
    return getSubmenuFormIndex(subMenuArray, menuId, title);
  }

  addPermissionList(listCount) {
    const permArray = < FormArray > this.UpdatePermission.get('Permissions');
    permArray.controls = [];
    for(let i=0; i<(listCount); i++) {
      this.addPermission();
      this.roleChg = true;
    } 
  }

  addSubMenuList(listCount,index) {
    const subMenuArray = (< FormArray > this.UpdatePermission.get('Permissions')).at(index).get('Submenus') as FormArray;
    subMenuArray.controls = [];
    for(let i=0; i<(listCount); i++) {
      this.addSubmenu(index);
    } 
  }

  setPermissionList(dataList: PermissionFormSection[], onComplete?: () => void) {
    const permArray = <FormArray>this.UpdatePermission.get('Permissions');
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

  assignPermissionList() {
    const cond = {
      'conditions[tenant_id]': this.UpdatePermission.get('tenant_id').value,
      'conditions[role_id]': this.UpdatePermission.get('role_id').value,
    };
    this.userservice.getData(cond, 'get-permissions', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success' && response.data && response.data[0]) {
          this.UpdatePermission.get('id').setValue(response.data[0].id);
          const accessPerm = response.data[0].role_access || [];
          const permArray = <FormArray>this.UpdatePermission.get('Permissions');
          for (let i = 0; i < permArray.length; i++) {
            const sec = permArray.controls[i];
            if (Number(sec.get('has_sub_menu').value) !== 1) {
              const saved = accessPerm.find((m: any) => Number(m.menu_id) === Number(sec.get('menu_id').value));
              if (saved) {
                sec.get('add_perm').setValue(saved.add_perm);
                sec.get('edit_perm').setValue(saved.edit_perm);
                sec.get('view_perm').setValue(saved.view_perm);
                sec.get('delete_perm').setValue(saved.delete_perm);
              }
              continue;
            }
            const subs = sec.get('Submenus') as FormArray;
            for (let j = 0; j < subs.length; j++) {
              const menuId = subs.controls[j].get('menu_id').value;
              const savedSub = findSavedSubmenu(accessPerm, menuId);
              if (savedSub) {
                subs.controls[j].get('add_perm').setValue(savedSub.add_perm);
                subs.controls[j].get('edit_perm').setValue(savedSub.edit_perm);
                subs.controls[j].get('view_perm').setValue(savedSub.view_perm);
                subs.controls[j].get('delete_perm').setValue(savedSub.delete_perm);
                subs.controls[j].get('top_perm').setValue(savedSub.top_perm);
              }
            }
          }
          this.hidePermission = false;
          this.roleChg = true;
        } else {
          this.hidePermission = false;
          this.roleChg = true;
        }
      } catch (err) {}
    }, (err: any) => {});
  }

  allMenuChg(event) {
    let menuArray = <FormArray>this.UpdatePermission.controls['Permissions'];
    if(event.target.checked) {
      for(let i=0;i<(menuArray.length);i++) {
        menuArray.controls[i].get('add_perm').setValue(1);
        menuArray.controls[i].get('edit_perm').setValue(1);
        menuArray.controls[i].get('view_perm').setValue(1);
        menuArray.controls[i].get('delete_perm').setValue(1);
        let subMenuArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(i).get('Submenus') as FormArray;
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
        let subMenuArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(i).get('Submenus') as FormArray;
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

  setSubMenuList(dataList: any[], index: number) {
    const subMenuArray = (<FormArray>this.UpdatePermission.get('Permissions')).at(index).get('Submenus') as FormArray;
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

  get f() {
    return this.UpdatePermission.controls;
  }

  addPermission() { 
    const control = <FormArray>this.UpdatePermission.controls['Permissions'];
    control.push(this.initPermission());
  }

  addSubmenu(index) { 
    const control = (<FormArray>this.UpdatePermission.controls['Permissions']).at(index).get('Submenus') as FormArray;
    control.push(this.initSubmenu());
  }

  initPermission() {
    return this.formBuilder.group({
      'menu_id': ['', Validators.required],
      'menu_title': ['', Validators.required],
      'page_type': [''],
      'has_sub_menu': [''],
      'add_perm': [''],
      'edit_perm': [''],
      'view_perm': [''],
      'delete_perm': [''],
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
    return <FormArray>this.UpdatePermission.get('Permissions');
  }

  mainMenuChg(event,index) {
    let menuArray = <FormArray>this.UpdatePermission.controls['Permissions'];
    let subMenuArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(index).get('Submenus') as FormArray;
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
    let permissionArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
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
    let permissionArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('add_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('add_perm').setValue(0);
    }
  }

  editPermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('edit_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('edit_perm').setValue(0);
    }
  }

  viewPermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
    if(event.target.checked) {
      permissionArray.controls[subIndex].get('view_perm').setValue(1);
    }
    else {
      permissionArray.controls[subIndex].get('view_perm').setValue(0);
    }
  }

  deletePermChg(event,mainIndex,subIndex) {
    let permissionArray = (<FormArray>this.UpdatePermission.controls['Permissions']).at(mainIndex).get('Submenus') as FormArray;
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
    const result = Object.assign({}, this.UpdatePermission.value);
    result.Permissions = flattenSectionsForSave(result.Permissions || []);
    if (this.UpdatePermission.invalid) { 
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
    this.userservice.getData(result, "update-permission", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Security Permission",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/add-permission']);
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
    this.UpdatePermission = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      role_id: ['', Validators.required],
      updated_by: [''],
      id: [''],
      Permissions: this.formBuilder.array([]),
    });
    let tenant_id = localStorage.getItem('tenant_id');
    let cond = {};
    if(tenant_id) {
      this.UpdatePermission.get('tenant_id').setValue(tenant_id);
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
    this.UpdatePermission.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
