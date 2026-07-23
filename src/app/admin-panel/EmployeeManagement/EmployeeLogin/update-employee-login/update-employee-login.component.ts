import { Component, OnInit, OnDestroy, ElementRef, ViewChild } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { Router, ActivatedRoute } from '@angular/router';
import { UserService } from '../../../../Services/user.service';
import { MustMatch } from '../../../../helpers/must-match.validator';
import { Subscription } from 'rxjs';

@Component({
  
  selector: 'app-update-employee-login',
  templateUrl: './update-employee-login.component.html',
  styles: [`
    :host ::ng-deep .add-employee-login-card {
      overflow: visible;
    }
    :host ::ng-deep .card-body.add-maze {
      overflow: visible;
      padding-bottom: 2rem;
    }
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
      max-height: 40vh !important;
    }
  `]
})
export class UpdateEmployeeLoginComponent implements OnInit, OnDestroy {
  @ViewChild('tenant_id') focus1: ElementRef;
  @ViewChild('branch_id') focus2: ElementRef;
  @ViewChild('employee_id') focus3: ElementRef;
  @ViewChild('email') focus4: ElementRef;
  @ViewChild('password') focus5: ElementRef;
  @ViewChild('confirmPassword') focus6: ElementRef;
  @ViewChild('role_id') focus7: ElementRef;

  UpdateEmployeeLogin: FormGroup;
  form_message = true;
  submitted = false;
  disableBtn = false;
  SubmitbuttonName: string;

  employeeList: any;
  branchList: any;
  mailPattern = '^[a-zA-Z0-9_.+-]+@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}$';
  isValidMail: boolean;
  isSuperAdmin: boolean;
  companyList: any;
  roleList: any[];
  employeeLoginId: any;
  showPassword: boolean = false;
  showConfirmPassword: boolean = false;

  selectedRoleId: any = null;
  fullMappedEmployeesForRole: any[] = [];
  mappedEmployeeDropdownList: any[] = [];
  mappedBranchDropdownList: any[] = [];
  mappedEmployeeDropdownSettings: any = {};
  mappedBranchDropdownSettings: any = {};
  private mappedBranchIdsSub: Subscription;
  /** Saved from API until role-tree lists are loaded */
  private savedMappedBranches: any[] = null;
  private savedMappedEmployees: any[] = null;
  private roleTreeEmployeesLoaded = false;
  private roleTreeBranchesLoaded = false;
  /** Primary branch on users row — used when mapping tables are empty */
  private loginPrimaryBranchId: any = null;

  constructor(
    private appService: AppService,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private userservice: UserService,
    private router: Router
  ) {
    this.SubmitbuttonName = 'Submit';
  }

  get f() {
    return this.UpdateEmployeeLogin.controls;
  }

  companyChg(event) {
    const tenant_id = event.target.value;
    this.setBranchList(tenant_id);
  }

  setBranchList(tenant_id) {
    const cond = {
      'conditions[tenant_id]': tenant_id,
      'conditions[status]': 1,
      log_id: localStorage.getItem('logged_employee_id')
    };
    this.userservice.getData(cond, 'get-branches', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.branchList = response.data;
        } else {
          this.branchList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  branchChg(event) {
    const cond = {
      'conditions[tenant_id]': this.UpdateEmployeeLogin.get('tenant_id').value,
      'conditions[branch_id]': event.target.value
    };
    this.userservice.getData(cond, 'get-employees', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      } catch (err) { }
    }, (err: any) => { });

    this.userservice.getData(cond, 'get-roles', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.roleList = response.data;
        } else {
          this.roleList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  setEmployeeList(branch_id) {
    const cond = {
      'conditions[tenant_id]': this.UpdateEmployeeLogin.get('tenant_id').value,
      'conditions[branch_id]': branch_id
    };
    this.userservice.getData(cond, 'get-employees', 'POST').subscribe((response: any) => {
      try {
        if (response.status == 'Success') {
          this.employeeList = response.data;
        } else {
          this.employeeList = [];
        }
      } catch (err) { }
    }, (err: any) => { });
  }

  applyMappedEmployeeBranchFilter() {
    const branchSelection = this.UpdateEmployeeLogin.get('mapped_branch_ids').value || [];
    let branchIds = branchSelection.map((b: any) => String(b.item_id));

    if (!this.fullMappedEmployeesForRole || !this.fullMappedEmployeesForRole.length) {
      this.mappedEmployeeDropdownList = [];
      return;
    }

    if (!branchIds.length) {
      const primaryBranch = this.loginPrimaryBranchId || this.UpdateEmployeeLogin.get('branch_id').value;
      if (primaryBranch) {
        branchIds = [String(primaryBranch)];
      } else {
        this.mappedEmployeeDropdownList = [];
        return;
      }
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

    const current = this.UpdateEmployeeLogin.get('mapped_employee_ids').value || [];
    let next = current.filter((x: any) => allowedIds[String(x.item_id)]);

    const primaryId = this.UpdateEmployeeLogin.get('employee_id').value;
    if (!next.length && primaryId && allowedIds[String(primaryId)]) {
      const found = filtered.find((e: any) => String(e.item_id) === String(primaryId));
      if (found) {
        next = [{ item_id: found.item_id, item_text: found.item_text }];
      }
    }

    this.UpdateEmployeeLogin.get('mapped_employee_ids').setValue(next, { emitEvent: false });
  }

  /**
   * After both role-tree API calls finish, apply saved mappings from get-users (edit hydrate).
   */
  private tryApplySavedMappings() {
    if (!this.roleTreeEmployeesLoaded || !this.roleTreeBranchesLoaded) {
      return;
    }

    if (this.mappedBranchDropdownList.length) {
      let next: any[] = [];
      if (this.savedMappedBranches && this.savedMappedBranches.length) {
        const allowed: { [k: string]: boolean } = {};
        this.mappedBranchDropdownList.forEach((b: any) => { allowed[String(b.item_id)] = true; });
        next = this.savedMappedBranches.filter((b: any) => allowed[String(b.item_id)]);
        const missing = this.savedMappedBranches.filter((b: any) => !allowed[String(b.item_id)]);
        next = next.concat(missing);
      }
      if (!next.length) {
        const primaryBranch = this.loginPrimaryBranchId || this.UpdateEmployeeLogin.get('branch_id').value;
        if (primaryBranch) {
          const found = this.mappedBranchDropdownList.find((b: any) => String(b.item_id) === String(primaryBranch));
          if (found) {
            next = [{ item_id: found.item_id, item_text: found.item_text }];
          } else {
            const saved = (this.savedMappedBranches || []).find((b: any) => String(b.item_id) === String(primaryBranch));
            next = saved ? [saved] : [{ item_id: primaryBranch, item_text: 'Branch #' + primaryBranch }];
          }
        }
      }
      if (next.length) {
        this.UpdateEmployeeLogin.get('mapped_branch_ids').setValue(next, { emitEvent: false });
      }
    }

    this.applyMappedEmployeeBranchFilter();

    if (this.savedMappedEmployees && this.savedMappedEmployees.length) {
      const allowedEmp: { [k: string]: boolean } = {};
      this.mappedEmployeeDropdownList.forEach((e: any) => { allowedEmp[String(e.item_id)] = true; });
      const nextEmp = this.savedMappedEmployees.filter((e: any) => allowedEmp[String(e.item_id)]);
      if (nextEmp.length) {
        this.UpdateEmployeeLogin.get('mapped_employee_ids').setValue(nextEmp, { emitEvent: false });
      }
    }

    this.savedMappedBranches = null;
    this.savedMappedEmployees = null;
  }

  /** Load branch + employee lists for role. On edit, scope to saved login mappings (like mobile). */
  loadRoleTreeForRole(
    role_id: any,
    hydrateMappings?: { branches: any[]; employees: any[] },
    scopeToLogin = true
  ) {
    this.selectedRoleId = role_id;
    this.fullMappedEmployeesForRole = [];
    this.mappedEmployeeDropdownList = [];
    this.mappedBranchDropdownList = [];
    this.roleTreeEmployeesLoaded = false;
    this.roleTreeBranchesLoaded = false;
    this.UpdateEmployeeLogin.get('mapped_employee_ids').setValue([], { emitEvent: false });
    this.UpdateEmployeeLogin.get('mapped_branch_ids').setValue([], { emitEvent: false });

    if (hydrateMappings) {
      this.savedMappedBranches = hydrateMappings.branches && hydrateMappings.branches.length ? hydrateMappings.branches : null;
      this.savedMappedEmployees = hydrateMappings.employees && hydrateMappings.employees.length ? hydrateMappings.employees : null;
    } else {
      this.savedMappedBranches = null;
      this.savedMappedEmployees = null;
    }

    if (!role_id) {
      return;
    }

    const tenant_id = this.UpdateEmployeeLogin.get('tenant_id').value;
    if (!tenant_id) {
      return;
    }

    const cond: any = {
      tenant_id: tenant_id,
      role_id: role_id,
      wide_map: true
    };
    if (this.employeeLoginId && scopeToLogin) {
      cond.user_id = this.employeeLoginId;
    }

    this.userservice.getData(cond, 'get-employees-for-role-tree', 'POST').subscribe((response: any) => {
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
        }
      } catch (err) { }
      this.roleTreeEmployeesLoaded = true;
      this.tryApplySavedMappings();
    }, (err: any) => {
      this.roleTreeEmployeesLoaded = true;
      this.tryApplySavedMappings();
    });

    this.userservice.getData(cond, 'get-branches-for-role-tree', 'POST').subscribe((response: any) => {
      try {
        if (response && response.status == 'Success') {
          this.mappedBranchDropdownList = (response.data || []).map((b: any) => ({
            item_id: b.id,
            item_text: b.branch_name
          }));
        }
      } catch (err) { }
      this.roleTreeBranchesLoaded = true;
      this.tryApplySavedMappings();
    }, (err: any) => {
      this.roleTreeBranchesLoaded = true;
      this.tryApplySavedMappings();
    });
  }

  roleChg(event) {
    this.loadRoleTreeForRole(event.target.value, null, false);
  }

  showHidePassword(passwordType, showHide) {
    if (passwordType == 'password') {
      this.showPassword = showHide;
    } else if (passwordType == 'confirmPassword') {
      this.showConfirmPassword = showHide;
    }
  }

  onSubmit() {
    this.SubmitbuttonName = 'Loading...';
    this.submitted = true;
    this.disableBtn = true;
    const result = Object.assign({}, this.UpdateEmployeeLogin.value);
    if (this.UpdateEmployeeLogin.invalid) {
      this.SubmitbuttonName = 'Submit';
      this.disableBtn = false;
      if (this.f.tenant_id.errors) {
        this.focus1.nativeElement.focus();
      } else if (this.f.branch_id.errors) {
        this.focus2.nativeElement.focus();
      } else if (this.f.employee_id.errors) {
        this.focus3.nativeElement.focus();
      } else if (this.f.email.errors) {
        this.focus4.nativeElement.focus();
      } else if (this.f.password.errors) {
        this.focus5.nativeElement.focus();
      } else if (this.f.confirmPassword.errors) {
        this.focus6.nativeElement.focus();
      } else if (this.f.role_id.errors) {
        this.focus7.nativeElement.focus();
      }
      return;
    }
    this.userservice.getData(result, 'update-user', 'POST').subscribe((response: any) => {
      this.SubmitbuttonName = 'Submit';
      try {
        if (response.status == 'Success') {
          this.appService.showAlert('Employee Login', 'Updated Successfully', 'success');
          setTimeout(() => {
            this.router.navigate(['/admin-panel/view-employee-login']);
          }, 3000);
        } else {
          this.appService.showAlert('Employee Login', response.msg, 'error');
          this.disableBtn = false;
        }
      } catch (err) {
        this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
        this.disableBtn = false;
      }
    }, (err: any) => {
      this.disableBtn = false;
      this.SubmitbuttonName = 'Submit';
      if (err.status == 401) {
        this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
      } else {
        this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
      }
    });
  }

  ngOnInit() {
    this.UpdateEmployeeLogin = this.formBuilder.group({
      tenant_id: ['', Validators.required],
      branch_id: ['', Validators.required],
      employee_id: ['', Validators.required],
      email: ['', Validators.required],
      password: ['', [Validators.required, Validators.minLength(8)]],
      confirmPassword: ['', Validators.required],
      role_id: ['', Validators.required],
      mapped_employee_ids: [[]],
      mapped_branch_ids: [[]],
      id: [''],
      updated_by: [''],
    }, {
      validator: MustMatch('password', 'confirmPassword')
    });
    this.UpdateEmployeeLogin.get('updated_by').setValue(localStorage.getItem('logged_user_id'));

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

    this.mappedBranchIdsSub = this.UpdateEmployeeLogin.get('mapped_branch_ids').valueChanges.subscribe(() => {
      this.applyMappedEmployeeBranchFilter();
    });

    const tenant_id = localStorage.getItem('tenant_id');
    if (tenant_id) {
      this.isSuperAdmin = false;
    } else {
      this.isSuperAdmin = true;
      this.userservice.getData('', 'list-tenants', 'POST').subscribe((response: any) => {
        try {
          if (response.status == 'Success') {
            this.companyList = response.data;
          } else {
            this.companyList = [];
          }
        } catch (err) { }
      }, (err: any) => { });
    }

    this.route.params.subscribe(params => {
      this.employeeLoginId = params.id;
      this.UpdateEmployeeLogin.get('id').setValue(this.employeeLoginId);
      const cond = {
        'conditions[id]': this.employeeLoginId
      };
      this.userservice.getData(cond, 'get-users', 'POST').subscribe((response: any) => {
        this.SubmitbuttonName = 'Submit';
        try {
          if (response.status == 'Success') {
            const row = response.data[0];
            this.loginPrimaryBranchId = row.branch_id;
            this.UpdateEmployeeLogin.get('tenant_id').setValue(row.tenant_id);
            this.UpdateEmployeeLogin.get('branch_id').setValue(row.branch_id);
            this.UpdateEmployeeLogin.get('employee_id').setValue(row.employee_id);
            this.UpdateEmployeeLogin.get('email').setValue(row.email);
            this.UpdateEmployeeLogin.get('role_id').setValue(row.role_id);
            this.setBranchList(row.tenant_id);
            this.setEmployeeList(row.branch_id);

            const br = row.mapped_branch_ids || [];
            const em = row.mapped_employee_ids || [];
            this.selectedRoleId = row.role_id;
            this.loadRoleTreeForRole(String(row.role_id), { branches: br, employees: em });

            const condRoles = {
              'conditions[tenant_id]': row.tenant_id,
              'conditions[branch_id]': row.branch_id
            };
            this.userservice.getData(condRoles, 'get-roles', 'POST').subscribe((r2: any) => {
              try {
                if (r2.status == 'Success') {
                  this.roleList = r2.data;
                } else {
                  this.roleList = [];
                }
              } catch (e) { }
            }, (err: any) => { });
          } else {
            this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
          }
        } catch (err) {
          this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
        }
      }, (err: any) => {
        if (err.status == 401) {
          this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
        } else {
          this.appService.showAlert('Employee Login', 'Sorry!! Something went wrong!', 'error');
        }
      });
    });
  }

  ngOnDestroy() {
    if (this.mappedBranchIdsSub) {
      this.mappedBranchIdsSub.unsubscribe();
    }
  }
}
