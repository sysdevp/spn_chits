import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';

@Component({
  selector: 'app-employee-performance-ai',
  templateUrl: './employee-performance-ai.component.html',
  styles: [
    `
      .ai-header { background: linear-gradient(90deg, #311b92 0%, #5e35b1 100%); color: #fff; }
      .ai-kpi { border-radius: 12px; padding: 1rem; color: #fff; min-height: 100px; }
      .ai-kpi-a { background: linear-gradient(135deg, #311b92, #7e57c2); }
      .ai-kpi-b { background: linear-gradient(135deg, #00695c, #26a69a); }
      .ai-kpi-c { background: linear-gradient(135deg, #e65100, #ffb74d); color: #3e2723; }
      .ai-card { border: 1px solid #e0e0e0; border-radius: 12px; padding: 1rem; margin-bottom: 0.85rem; background: #fff; }
      .grade-excellent { color: #2e7d32; font-weight: 700; }
      .grade-good { color: #1565c0; font-weight: 700; }
      .grade-average { color: #ef6c00; font-weight: 700; }
      .grade-needs { color: #c62828; font-weight: 700; }
    `,
  ],
})
export class EmployeePerformanceAiComponent implements OnInit {
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  employeeList: any[] = [];
  loading = false;
  submitted = false;
  previewMode = false;
  report: any = null;

  constructor(private fb: FormBuilder, private userService: UserService) {}

  get f() { return this.filterForm.controls; }

  ngOnInit() {
    this.filterForm = this.fb.group({
      tenant_id: ['', Validators.required],
      branch_id: [''],
      employee_id: [''],
    });
    const tenantId = localStorage.getItem('tenant_id');
    if (tenantId) {
      this.filterForm.patchValue({ tenant_id: tenantId });
      this.setBranchList(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userService.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        this.companyList = res.status === 'Success' ? res.data || [] : [];
      });
    }
    this.useSample();
    this.loadReport();
  }

  companyChg(event: Event) {
    const tid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({ tenant_id: tid, branch_id: '', employee_id: '' });
    this.employeeList = [];
    this.setBranchList(tid);
  }

  branchChg(event: Event) {
    const bid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({ employee_id: '' });
    if (!bid) {
      this.employeeList = [];
      return;
    }
    this.userService.getData({ 'conditions[branch_id]': bid }, 'get-employees', 'POST').subscribe((res: any) => {
      this.employeeList = res.status === 'Success' ? res.data || [] : [];
    });
  }

  setBranchList(tenantId: string) {
    this.userService.getData({
      'conditions[tenant_id]': tenantId,
      log_id: localStorage.getItem('logged_employee_id'),
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data || [] : [];
    });
  }

  gradeClass(grade: string) {
    if (grade === 'Excellent') return 'grade-excellent';
    if (grade === 'Good') return 'grade-good';
    if (grade === 'Average') return 'grade-average';
    return 'grade-needs';
  }

  loadReport() {
    this.submitted = true;
    if (this.filterForm.invalid) {
      this.useSample();
      return;
    }
    this.loading = true;
    const payload: any = { tenant_id: this.filterForm.get('tenant_id').value };
    ['branch_id', 'employee_id'].forEach((k) => {
      const v = this.filterForm.get(k).value;
      if (v) payload[k] = v;
    });
    this.userService.getData(payload, 'employee-performance-ai', 'JSON').subscribe((res: any) => {
      this.loading = false;
      const rows = res && res.data && res.data.employees ? res.data.employees : [];
      if (res.status === 'Success' && rows.length) {
        this.previewMode = false;
        this.report = res.data;
      } else {
        this.useSample();
      }
      this.loadLiveCalls();
    }, () => {
      this.loading = false;
      this.useSample();
      this.loadLiveCalls();
    });
  }

  loadLiveCalls() {
    const tenantId = this.filterForm.get('tenant_id').value;
    if (!tenantId || !this.report) {
      return;
    }
    this.userService.getData({
      tenant_id: tenantId,
      branch_id: this.filterForm.get('branch_id').value || ''
    }, 'list-live-employee-locations', 'JSON').subscribe((res: any) => {
      const rows = res && res.status === 'Success' ? (res.data || []) : [];
      if (!rows.length || !this.report) {
        return;
      }
      const visits = ['the highest pending customer on the route', 'the customer who usually pays this week', 'the customer with no receipt this month'];
      rows.forEach((row: any, index: number) => {
        const place = row.location_name || ((row.latitude && row.longitude) ? (row.latitude + ', ' + row.longitude) : 'the live map point');
        const visit = visits[index % visits.length];
        const line = (row.employee_name || 'Employee') + ' is currently at ' + place + '. Please call and ask them to visit ' + visit + '.';
        const match = (this.report.employees || []).find((emp: any) => emp.employee_name === row.employee_name);
        if (match) {
          match.location_name = place;
          match.maps_link = row.maps_link || match.maps_link;
          match.narrative = line + ' ' + (match.narrative || '');
        } else {
          this.report.employees.unshift({
            rank: 0,
            employee_name: row.employee_name || 'Employee',
            grade: 'Live',
            score: '-',
            customers: 0,
            areas: 0,
            collected_mtd: 0,
            collected_3m: 0,
            pending_book: 0,
            receipts_mtd: 0,
            leave_days: 0,
            recovery_pct: 0,
            location_name: place,
            maps_link: row.maps_link || '',
            narrative: line,
            actions: ['Call this employee now.', 'Send them to ' + visit + '.']
          });
        }
      });
      this.report.live_tracking = true;
    });
  }

  useSample() {
    this.previewMode = true;
    this.report = this.sampleReport();
  }

  sampleReport() {
    const month = new Date().toLocaleString('en-IN', { month: 'long', year: 'numeric' });
    return {
      analysed_by: 'Emglitz Intelligence AI',
      period: { month_label: month, to_date: new Date().toISOString().slice(0, 10) },
      overview: 'Emglitz Intelligence AI reviewed 4 collection employees on commitment chits. Month-to-date collection is ₹18,65,000 against a pending book of ₹24,20,000. Average performance score is 78/100. Priya S leads with an Excellent score of 91.',
      summary: { employee_count: 4, collected_mtd: 1865000, pending_book: 2420000, avg_score: 78 },
      employees: [
        {
          rank: 1, employee_name: 'Priya S', grade: 'Excellent', score: 91, customers: 42, areas: 3,
          collected_mtd: 620000, collected_3m: 1710000, pending_book: 540000, receipts_mtd: 86, leave_days: 0, recovery_pct: 74,
          narrative: 'Priya S is currently at Anna Nagar, 2nd Avenue. Please call 9840011122 and ask her to visit Ramesh Kumar (AR-1042). She is rated Excellent (91/100) and collected ₹6,20,000 this month. Attendance is clear, so she can take this visit now.',
          location_name: 'Anna Nagar, 2nd Avenue',
          visit_customer: 'Ramesh Kumar (AR-1042)',
          mobile: '9840011122',
          maps_link: 'https://www.google.com/maps?q=13.0878,80.2085',
          actions: ['Call Priya now — she is already in Anna Nagar.', 'Ask her to visit Ramesh Kumar (AR-1042) before leaving the area.'],
        },
        {
          rank: 2, employee_name: 'Arun M', grade: 'Good', score: 82, customers: 36, areas: 2,
          collected_mtd: 480000, collected_3m: 1390000, pending_book: 610000, receipts_mtd: 71, leave_days: 0, recovery_pct: 61,
          narrative: 'Arun M is currently near Porur signal. Please call 9840033344 and send him to Kavitha N (AR-1422). He is rated Good (82/100). Pending book is still higher than this month’s collection.',
          location_name: 'Porur signal',
          visit_customer: 'Kavitha N (AR-1422)',
          mobile: '9840033344',
          maps_link: 'https://www.google.com/maps?q=13.0382,80.1565',
          actions: ['Call Arun and confirm he can reach Porur before 4pm.', 'Visit Kavitha N, who usually pays near day 28.'],
        },
        {
          rank: 3, employee_name: 'Divya K', grade: 'Average', score: 68, customers: 29, areas: 2,
          collected_mtd: 275000, collected_3m: 840000, pending_book: 550000, receipts_mtd: 44, leave_days: 1, recovery_pct: 50,
          narrative: 'Divya K is currently on Velachery 100 ft road. Please call 9840055566 and ask her to visit Meena Krishnan (AR-1310), the highest pending customer on her route. She is rated Average (68/100).',
          location_name: 'Velachery 100 ft road',
          visit_customer: 'Meena Krishnan (AR-1310)',
          mobile: '9840055566',
          maps_link: 'https://www.google.com/maps?q=12.9750,80.2210',
          actions: ['Call Divya while she is still in Velachery.', 'Meena is a slow payer — call the customer before the visit.'],
        },
        {
          rank: 4, employee_name: 'Karthik R', grade: 'Needs attention', score: 54, customers: 33, areas: 2,
          collected_mtd: 290000, collected_3m: 760000, pending_book: 720000, receipts_mtd: 39, leave_days: 2, recovery_pct: 40,
          narrative: 'Karthik R was last tracked at KK Nagar and has 2 leave days. Please call 9840077788. If he is not moving, ask Priya S to visit Lakshmi Devi (AR-1188). He is rated Needs attention (54/100).',
          location_name: 'KK Nagar',
          visit_customer: 'Lakshmi Devi (AR-1188)',
          mobile: '9840077788',
          maps_link: 'https://www.google.com/maps?q=13.0410,80.1990',
          actions: ['Call Karthik and check if he is still at KK Nagar.', 'If he is on leave, send Priya to Lakshmi Devi (AR-1188).'],
        },
      ],
    };
  }
}
