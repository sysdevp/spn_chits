import { Component, OnInit, ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../Services/user.service';

@Component({
  
  selector: 'app-update-dynamic-menu',
  templateUrl: './update-dynamic-menu.component.html',
  styles: []
})

export class UpdateDynamicMenuComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('menu_title') focus2:ElementRef;
  @ViewChild('icon_class') focus3:ElementRef;
  @ViewChild('router_link') focus4:ElementRef;
  @ViewChild('page_type') focus5:ElementRef;
  @ViewChild('parent_menu_id') focus6:ElementRef;
  @ViewChild('tab_type') focus7:ElementRef;
  @ViewChild('sort_order') focus8:ElementRef;
  @ViewChild('has_submenu') focus9:ElementRef;

  UpdateDynamicMenu: FormGroup;
  submitted = false;
  disableBtn = false;
  menuId: any;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  parentMenuList: any[];
  companyList: any[];
  sortOrderList: any[];

  constructor(private appService: AppService, private route: ActivatedRoute, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  { 
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.UpdateDynamicMenu.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateDynamicMenu.value);
    if (this.UpdateDynamicMenu.invalid) 
    {
      this.SubmitbuttonName = "Submit";
      this.disableBtn = false;
      if(this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      }
      else if(this.f.menu_title.errors) {
        this.focus2.nativeElement.focus();
      }
      else if(this.f.icon_class.errors) {
        this.focus3.nativeElement.focus();
      }
      else if(this.f.router_link.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.page_type.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.parent_menu_id.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.tab_type.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.sort_order.errors) {
        this.focus8.nativeElement.focus();
      }
      else if(this.f.has_submenu.errors) {
        this.focus9.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "update-dynamic-menu", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Dynamic Menu",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-dynamic-menus']);
          }, 300);
        } else {
          this.appService.showAlert("Dynamic Menu", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Dynamic Menu", 'Sorry!! Something went wrong!', "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {
    this.UpdateDynamicMenu = this.formBuilder.group({
      id: [''],
      tenant_id: [''],
      menu_title: ['', Validators.required],
      icon_class: [''],
      router_link: ['', Validators.required],
      page_type: ['1', Validators.required],
      parent_menu_id: [''],
      sort_order: [''],
      has_submenu: [''],
      tab_type: ['1'],
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
      this.menuId = params.id;
      this.UpdateDynamicMenu.get('id').setValue(this.menuId);
      this.UpdateDynamicMenu.get('updated_by').setValue(localStorage.getItem('logged_user_id'));
      let cond = {
        "conditions[id]" : this.menuId 
      }
      this.userservice.getData(cond, "get-dynamic-menus","POST").subscribe((response: any) => {
        this.SubmitbuttonName = "Submit";
        try {
          if (response.status == 'Success') 
          { 
            this.UpdateDynamicMenu.get('tenant_id').setValue(response.data[0].tenant_id);
            this.UpdateDynamicMenu.get('menu_title').setValue(response.data[0].menu_title);
            this.UpdateDynamicMenu.get('icon_class').setValue(response.data[0].icon_class);
            this.UpdateDynamicMenu.get('router_link').setValue(response.data[0].router_link);
            this.UpdateDynamicMenu.get('page_type').setValue(response.data[0].page_type);
            this.UpdateDynamicMenu.get('has_submenu').setValue(response.data[0].has_submenu);
            this.UpdateDynamicMenu.get('tab_type').setValue(response.data[0].tab_type);
            this.UpdateDynamicMenu.get('parent_menu_id').setValue(response.data[0].parent_menu_id);
            this.UpdateDynamicMenu.get('sort_order').setValue(response.data[0].sort_order);
            let cond1 = {
              //"conditions[tenant_id]" : response.data[0].tenant_id,
              "for_edit" : this.menuId,
            }
            this.userservice.getData(cond1, "get-parent-menus", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.parentMenuList = response.data;
                } else {
                  this.parentMenuList = [];
                }
              }  catch (err) { }
            }, (err: any) => { });
            this.userservice.getData(cond1, "get-menu-sort-orders", "POST").subscribe((response: any) => {
              try { 
                if (response.status == 'Success') {
                  this.sortOrderList = response.data;
                } else {
                  this.sortOrderList = [];
                }
              }  catch (err) { }
            }, (err: any) => { });
          }
          else {
            this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
          }
        } catch (err) {
          this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
        }
        else {
          this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
        }
      });
    });
  }
}
