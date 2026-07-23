import { Component, OnInit, OnDestroy, ElementRef, ViewChild } from '@angular/core';
import { Subscription } from 'rxjs';
import { FormBuilder, FormGroup, Validators  } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { MustMatch } from '../../../../helpers/must-match.validator';

@Component({
  
  selector: 'app-add-employee-login',
  templateUrl: './add-employee-login.component.html',
  styles: [`
    :host ::ng-deep .add-employee-login-card {
      overflow: visible;
    }
    :host ::ng-deep .card-body.add-maze {
      overflow: visible;
      padding-bottom: 2rem;
    }
    /* ng-multiselect-dropdown: open upward near page bottom, stay inside viewport */
    :host ::ng-deep .add-employee-login-multiselect .multiselect-dropdown {
      position: relative;
      z-index: 1;
      width: 100%;
    }
    :host ::ng-deep .add-employee-login-multiselect .multiselect-dropdown .dropdown-list {
      z-index: 10050;
      position: absolute;
      left: 0;
      right: 0;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-shadow: 0 4px 16px rgba(0,0,0,.2);
    }
    :host ::ng-deep .add-employee-login-multiselect .multiselect-dropdown .dropdown-list ul.item2 {
      overflow-y: auto !important;
      overflow-x: hidden;
      -webkit-overflow-scrolling: touch;
      /* Short list + scroll; 40vh keeps panel inside typical layout when opened upward */
      max-height: 40vh !important;
    }
  `]
})

export class AddEmployeeLoginComponent implements OnInit, OnDestroy {
  @ViewChild('tenant_id') focus1:ElementRef;
  @ViewChild('branch_id') focus2:ElementRef;
  @ViewChild('employee_id') focus3:ElementRef;
  @ViewChild('email') focus4:ElementRef;
  @ViewChild('password') focus5:ElementRef;
  @ViewChild('confirmPassword') focus6:ElementRef;
  @ViewChild('role_id') focus7:ElementRef;
  @ViewChild('email_id') focus8:ElementRef;

  AddEmployeeLogin: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;
    
  employeeList: any;
  branchList: any;
  //mailPattern = "^[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$";
  mailPattern = "^[a-zA-Z0-9_.+-]+[a-zA-Z0-9_.]{2,}";
  isValidMail: boolean;
  isSuperAdmin: boolean;
  companyList: any;
  roleList: any[];

  // Multi-select based on role hierarchy (employees + branches)
  selectedRoleId: any = null;
  /** Full list for current role (includes branch_id for filtering by Multi Branch Mapping) */
  fullMappedEmployeesForRole: any[] = [];
  mappedEmployeeDropdownList: any[] = [];
  mappedBranchDropdownList: any[] = [];
  mappedEmployeeDropdownSettings: any = {};
  mappedBranchDropdownSettings: any = {};
  private mappedBranchIdsSub: Subscription;
  Emailid: any;
  Emaildata: any;
  emailList: any;
  showMailAlert:boolean=false;
  showPassword:boolean=false;
  showConfirmPassword:boolean=false;
  com: any;
  
  constructor(private appService: AppService, private formBuilder: FormBuilder, private userservice: UserService, private router: Router) 
  {     
    this.SubmitbuttonName = "Submit";
  }

  get f() {
    return this.AddEmployeeLogin.controls;
  }
  companyChg(event) {
    let tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) { 
    let cond = {
      "conditions[tenant_id]" : tenant_id,
      "conditions[status]" : 1,
      "log_id" : localStorage.getItem('logged_employee_id')
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

  branchChg(event)
  {
    let cond = {
      "conditions[tenant_id]" : this.AddEmployeeLogin.get('tenant_id').value,
      "conditions[branch_id]" :event.target.value
    }
    this.userservice.getData(cond, "get-employees", "POST").subscribe((response: any) => {
      try { 
        this.com = [];
        if (response.status == 'Success') {
          this.employeeList = response.data;
          this.com = response.data[0].tenant_det.domain_name;
         
          if(this.com == ''){
            
            this.com = '';
          }
        } else {
          this.employeeList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond, "get-roles", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.roleList = response.data;
        } else {
          this.roleList = [];
        }
      }  catch (err) { }
    }, (err: any) => { });
  }

  /**
   * Employees shown in "Map Employees" are limited to those whose branch_id
   * is in the current Multi Branch Mapping selection.
   * When no branch is selected yet (or user cleared all), show everyone returned
   * for the role so a slow branch API does not wipe the list. When all branches
   * from the role-tree are selected, employees with missing branch_id still appear.
   */
  applyMappedEmployeeBranchFilter() {
    const branchSelection = this.AddEmployeeLogin.get('mapped_branch_ids').value || [];
    const branchIds = branchSelection.map((b: any) => String(b.item_id));

    if (!this.fullMappedEmployeesForRole || !this.fullMappedEmployeesForRole.length) {
      this.mappedEmployeeDropdownList = [];
      return;
    }

    if (!branchIds.length) {
      this.mappedEmployeeDropdownList = this.fullMappedEmployeesForRole.map((e: any) => ({
        item_id: e.item_id,
        item_text: e.item_text
      }));
      return;
    }

    const allBranchesSelected =
      this.mappedBranchDropdownList.length > 0 &&
      branchIds.length >= this.mappedBranchDropdownList.length;

    const filtered = this.fullMappedEmployeesForRole.filter((e: any) => {
      const bid = e.branch_id;
      if (bid === null || bid === undefined || bid === '') {
        return allBranchesSelected;
      }
      return branchIds.indexOf(String(bid)) !== -1;
    });

    this.mappedEmployeeDropdownList = filtered.map((e: any) => ({
      item_id: e.item_id,
      item_text: e.item_text
    }));

    const allowedIds: { [k: string]: boolean } = {};
    filtered.forEach((e: any) => { allowedIds[String(e.item_id)] = true; });

    const current = this.AddEmployeeLogin.get('mapped_employee_ids').value || [];
    let next = current.filter((x: any) => allowedIds[String(x.item_id)]);

    const primaryId = this.AddEmployeeLogin.get('employee_id').value;
    if (!next.length && primaryId && allowedIds[String(primaryId)]) {
      const found = filtered.find((e: any) => String(e.item_id) === String(primaryId));
      if (found) {
        next = [{ item_id: found.item_id, item_text: found.item_text }];
      }
    }

    this.AddEmployeeLogin.get('mapped_employee_ids').setValue(next, { emitEvent: false });
  }

  roleChg(event) {
    const role_id = event.target.value;
    this.selectedRoleId = role_id;

    // Reset multi-select lists
    this.fullMappedEmployeesForRole = [];
    this.mappedEmployeeDropdownList = [];
    this.mappedBranchDropdownList = [];
    this.AddEmployeeLogin.get('mapped_employee_ids').setValue([], { emitEvent: false });
    this.AddEmployeeLogin.get('mapped_branch_ids').setValue([], { emitEvent: false });

    if (!role_id) {
      return;
    }

    const tenant_id = this.AddEmployeeLogin.get('tenant_id').value;
    if (!tenant_id) {
      return;
    }

    const cond = {
      tenant_id: tenant_id,
      role_id: role_id,
      // Backend: include Office / other roles under same area, not only strict subtree under login role
      wide_map: true,
    };

    // Fetch employees under this role subtree (store branch_id for branch-filtered dropdown)
    this.userservice.getData(cond, "get-employees-for-role-tree", "POST").subscribe((response: any) => {
      try {
        if (response && response.status == 'Success') {
          this.fullMappedEmployeesForRole = (response.data || []).map((emp: any) => {
            const roleName = emp.role_name ? emp.role_name : (emp.role_det && emp.role_det.role_name ? emp.role_det.role_name : '');
            const branchName = emp.branch_name ? emp.branch_name : (emp.branch_det && emp.branch_det.branch_name ? emp.branch_det.branch_name : '');
            const text = emp.employee_name ? emp.employee_name : (emp.first_name + ' ' + emp.last_name);
            let branchId = emp.branch_id;
            if (branchId === null || branchId === undefined || branchId === '') {
              branchId = (emp.branch_det && emp.branch_det.id) ? emp.branch_det.id : null;
            }
            return {
              item_id: emp.id,
              item_text: text + (roleName ? ' / ' + roleName : '') + (branchName ? ' / ' + branchName : ''),
              branch_id: branchId
            };
          });
          this.applyMappedEmployeeBranchFilter();
        }
      } catch (err) { }
    }, (err: any) => { });

    // Fetch branches under this role subtree
    this.userservice.getData(cond, "get-branches-for-role-tree", "POST").subscribe((response: any) => {
      try {
        if (response && response.status == 'Success') {
          this.mappedBranchDropdownList = (response.data || []).map((b: any) => {
            return {
              item_id: b.id,
              item_text: b.branch_name
            };
          });

          // Pre-select all branches for this role tree so Map Employees is not limited
          // to the header "Branch" only (subordinates on other branches stay visible).
          if (this.mappedBranchDropdownList.length) {
            this.AddEmployeeLogin.get('mapped_branch_ids').setValue(
              this.mappedBranchDropdownList.map((b: any) => ({ item_id: b.item_id, item_text: b.item_text })),
              { emitEvent: false }
            );
          }
          this.applyMappedEmployeeBranchFilter();
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  onKeyUpMail(event) { 
    let mail = event.target.value;
    if(mail=='') {
      this.isValidMail = true;
    }
    else {
      if((mail).match(this.mailPattern)) {
        this.isValidMail = true;
      }
      else {
        this.isValidMail = false;
      }
    }
  }

  emailVaildation(event)
  {
    let Email=event.target.value +''+ this.com;
    
    this.Emailid=Email;
    this.AddEmployeeLogin.get('email_id').setValue(this.Emailid);
    
    let cond = {
      "email" :Email
    }
    this.userservice.getData(cond, "check-email-validation", "POST").subscribe((response: any) => {
      try { 
        if (response.status == 'Success') {
          this.showMailAlert=false;
        } else {
          this.showMailAlert=true;
                }
      }  catch (err) { }
    }, (err: any) => { });
  }

  showHidePassword(passwordType,showHide) {
    if(passwordType=='password') {
      this.showPassword=showHide;
    }
    else if(passwordType=='confirmPassword') {
      this.showConfirmPassword=showHide;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = "Loading...";
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.AddEmployeeLogin.value);
    if (this.AddEmployeeLogin.invalid) {
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
      else if(this.f.email.errors) {
        this.focus4.nativeElement.focus();
      }
      else if(this.f.password.errors) {
        this.focus5.nativeElement.focus();
      }
      else if(this.f.confirmPassword.errors) {
        this.focus6.nativeElement.focus();
      }
      else if(this.f.role_id.errors) {
        this.focus7.nativeElement.focus();
      }
      else if(this.f.email_id.errors) {
        this.focus8.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, "store-user111", "JSON").subscribe((response: any) => {
      this.SubmitbuttonName = "Submit";
      try {
        if (response.status == 'Success') {
          this.appService.showAlert("Employee Login",response.msg, "success");
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-employee-login']);
          }, 300);
        } else {
          this.appService.showAlert("Employee Login", response.msg, "error");
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = "Submit";
      if (err.status == 401) {
        this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
      }
      else {
        this.appService.showAlert("Employee Login", "Sorry!! Something went wrong!", "error");
      }
    });
  }

  ngOnInit() {

    this.AddEmployeeLogin = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      email: ['',[Validators.required]],
      email_id: [''],
      password: [
    '',
    [
      Validators.required,
      Validators.minLength(8),
      Validators.pattern(/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/)
    ]
  ],
      confirmPassword: ['', Validators.required],
      role_id: ['', Validators.required],
      // Multi mappings for role hierarchy
      mapped_employee_ids: [[]],
      mapped_branch_ids: [[]],
      created_by: [''],
    }, {
      validator: MustMatch('password', 'confirmPassword')
  });
   
    let tenant_id = localStorage.getItem('tenant_id');
    
    if(tenant_id) {
      this.AddEmployeeLogin.get('tenant_id').setValue(tenant_id);
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
    this.AddEmployeeLogin.get('created_by').setValue(localStorage.getItem('logged_user_id'));

    this.mappedEmployeeDropdownSettings = {
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

    this.mappedBranchDropdownSettings = {
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

    this.mappedBranchIdsSub = this.AddEmployeeLogin.get('mapped_branch_ids').valueChanges.subscribe(() => {
      this.applyMappedEmployeeBranchFilter();
    });
  }

  ngOnDestroy() {
    if (this.mappedBranchIdsSub) {
      this.mappedBranchIdsSub.unsubscribe();
    }
  }
}
