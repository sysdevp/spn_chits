import { Component, OnInit , ElementRef,ViewChild  } from '@angular/core';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../Services/user.service';

@Component({
  
  selector: 'app-add-dynamic-menu',
  templateUrl: './add-dynamic-menu.component.html',
  styles: []
})

export class AddDynamicMenuComponent implements OnInit {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('menu_title') focus2:ElementRef;
  @ViewChild('icon_class') focus3:ElementRef;
  @ViewChild('router_link') focus4:ElementRef;
  @ViewChild('page_type') focus5:ElementRef;
  @ViewChild('parent_menu_id') focus6:ElementRef;
  @ViewChild('tab_type') focus7:ElementRef;
  @ViewChild('sort_order') focus8:ElementRef;
  @ViewChild('has_submenu') focus9:ElementRef;

  AddDynamicMenu: FormGroup;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
  isSuperAdmin:boolean;
  companyList: any[];
  parentMenuList: any[];
  sortOrderList: any[];

  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddDynamicMenu.controls;
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddDynamicMenu.value);
    if (this.AddDynamicMenu.invalid) {
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
    this.userservice.getData(result, "store-dynamic-menu", "JSON").subscribe((response: any) => {
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
        this.appService.showAlert("Dynamic Menu", "Sorry!! Something went wrong!", "error");
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
    this.AddDynamicMenu = this.formBuilder.group({
      tenant_id: [''],
      menu_title: ['', Validators.required],
      icon_class: [''],
      router_link: ['', Validators.required],
      page_type: ['1', Validators.required],
      parent_menu_id: [''],
      sort_order: [''],
      has_submenu: [''],
      tab_type: ['1'],
      created_by: [''],
    });
    let tenant_id = localStorage.getItem('tenant_id');
    let cond = {};
    if(tenant_id) {
      this.AddDynamicMenu.get('tenant_id').setValue(tenant_id);
      this.isSuperAdmin = false;
      cond = {
        //"conditions[tenant_id]" : tenant_id,
      }
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
    this.userservice.getData(cond, "get-parent-menus", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.parentMenuList = response.data;
        } else {
          this.parentMenuList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.userservice.getData(cond, "get-menu-sort-orders", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.sortOrderList = response.data;
        } else {
          this.sortOrderList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
    this.AddDynamicMenu.get('created_by').setValue(localStorage.getItem('logged_user_id'));
  }
}
