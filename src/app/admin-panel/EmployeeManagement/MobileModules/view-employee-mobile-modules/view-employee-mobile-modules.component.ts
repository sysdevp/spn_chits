import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AppService } from '../../../../app.service';
import { UserService } from '../../../../Services/user.service';

@Component({
  selector: 'app-view-employee-mobile-modules',
  templateUrl: './view-employee-mobile-modules.component.html',
  styles: []
})
export class ViewEmployeeMobileModulesComponent implements OnInit {
  tab = 'skills';
  skills: any[] = [];
  badges: any[] = [];
  performance: any[] = [];
  workPlans: any[] = [];
  visits: any[] = [];
  attSettings: any[] = [];
  skillForm: FormGroup;
  badgeForm: FormGroup;
  attForm: FormGroup;
  assessForm: FormGroup;
  branchList: any[] = [];
  employeeList: any[] = [];

  constructor(private appService: AppService, private fb: FormBuilder, private userservice: UserService) {}

  ngOnInit() {
    const tenant = localStorage.getItem('tenant_id');
    this.skillForm = this.fb.group({
      tenant_id: [tenant, Validators.required],
      skill_name: ['', Validators.required],
      category: ['General'],
      description: [''],
      created_by: [localStorage.getItem('logged_user_id')],
    });
    this.badgeForm = this.fb.group({
      tenant_id: [tenant, Validators.required],
      badge_name: ['', Validators.required],
      badge_code: ['', Validators.required],
      criteria_type: ['overall_score'],
      criteria_value: [75],
      icon: ['star'],
      description: [''],
      created_by: [localStorage.getItem('logged_user_id')],
    });
    this.attForm = this.fb.group({
      tenant_id: [tenant, Validators.required],
      branch_id: ['', Validators.required],
      shift_start_time: ['09:00:00', Validators.required],
      shift_end_time: ['18:00:00', Validators.required],
      late_grace_minutes: [15],
      early_leave_grace_minutes: [15],
      created_by: [localStorage.getItem('logged_user_id')],
    });
    this.assessForm = this.fb.group({
      tenant_id: [tenant, Validators.required],
      branch_id: [''],
      employee_id: ['', Validators.required],
      skill_id: ['', Validators.required],
      score: ['', Validators.required],
      strength_notes: [''],
      weakness_notes: [''],
      training_required: [0],
      training_notes: [''],
      assessed_by: [localStorage.getItem('logged_user_id')],
      created_by: [localStorage.getItem('logged_user_id')],
    });
    if (tenant) {
      this.userservice.getData({ 'conditions[tenant_id]': tenant, 'conditions[status]': 1 }, 'get-branches', 'POST').subscribe((r: any) => {
        this.branchList = (r && r.status === 'Success') ? r.data : [];
      });
    }
    this.loadAll();
  }

  branchChg(e) {
    this.userservice.getData({ 'conditions[branch_id]': e.target.value }, 'get-employees', 'POST').subscribe((r: any) => {
      this.employeeList = (r && r.status === 'Success') ? r.data : [];
    });
  }

  loadAll() {
    const t = { tenant_id: localStorage.getItem('tenant_id') };
    const periodPayload: any = {
      tenant_id: t.tenant_id,
      period_month: new Date().toISOString().slice(0, 7)
    };
    this.userservice.getData(t, 'list-employee-skills', 'JSON').subscribe((r: any) => {
      this.skills = (r && r.data) ? r.data : [];
    });
    this.userservice.getData(t, 'list-employee-badges', 'JSON').subscribe((r: any) => {
      this.badges = (r && r.data) ? r.data : [];
    });
    this.userservice.getData(periodPayload, 'list-performance-scores', 'JSON').subscribe((r: any) => {
      this.performance = (r && r.data) ? r.data : [];
    });
    this.userservice.getData(t, 'list-work-plans', 'JSON').subscribe((r: any) => {
      this.workPlans = (r && r.data) ? r.data : [];
    });
    this.userservice.getData(t, 'list-customer-visits', 'JSON').subscribe((r: any) => {
      this.visits = (r && r.data) ? r.data : [];
    });
    this.userservice.getData(t, 'list-attendance-settings', 'JSON').subscribe((r: any) => {
      this.attSettings = (r && r.data) ? r.data : [];
    });
  }

  saveSkill() {
    this.userservice.getData(this.skillForm.value, 'store-employee-skill', 'JSON').subscribe((r: any) => {
      if (r && r.status === 'Success') {
        this.appService.showAlert('Skills', r.msg, 'success');
        this.skillForm.patchValue({ skill_name: '', description: '' });
        this.loadAll();
      } else {
        this.appService.showAlert('Skills', (r && r.msg) ? r.msg : 'Failed', 'error');
      }
    });
  }

  saveBadge() {
    this.userservice.getData(this.badgeForm.value, 'store-employee-badge', 'JSON').subscribe((r: any) => {
      if (r && r.status === 'Success') {
        this.appService.showAlert('Badges', r.msg, 'success');
        this.loadAll();
      } else {
        this.appService.showAlert('Badges', (r && r.msg) ? r.msg : 'Failed', 'error');
      }
    });
  }

  saveAttSettings() {
    this.userservice.getData(this.attForm.value, 'store-attendance-settings', 'JSON').subscribe((r: any) => {
      if (r && r.status === 'Success') {
        this.appService.showAlert('Attendance', r.msg, 'success');
        this.loadAll();
      } else {
        this.appService.showAlert('Attendance', (r && r.msg) ? r.msg : 'Failed', 'error');
      }
    });
  }

  saveAssessment() {
    this.userservice.getData(this.assessForm.value, 'store-skill-assessment', 'JSON').subscribe((r: any) => {
      if (r && r.status === 'Success') {
        this.appService.showAlert('Assessment', r.msg, 'success');
        this.loadAll();
      } else {
        this.appService.showAlert('Assessment', (r && r.msg) ? r.msg : 'Failed', 'error');
      }
    });
  }

  deleteSkill(row) {
    this.userservice.getData({ id: row.id }, 'delete-employee-skill', 'JSON').subscribe(() => this.loadAll());
  }

  deleteBadge(row) {
    this.userservice.getData({ id: row.id }, 'delete-employee-badge', 'JSON').subscribe(() => this.loadAll());
  }
}
