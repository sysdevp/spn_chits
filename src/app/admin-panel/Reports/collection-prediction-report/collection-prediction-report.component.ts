import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { UserService } from '../../../Services/user.service';

@Component({
  selector: 'app-collection-prediction-report',
  templateUrl: './collection-prediction-report.component.html',
  styles: [
    `
      .pred-report-root .card-header {
        background: linear-gradient(90deg, #1a237e 0%, #3949ab 100%);
        color: #fff;
        border: 0;
      }
      .pred-kpi {
        border-radius: 12px;
        padding: 1rem;
        color: #fff;
        min-height: 110px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
      }
      .pred-kpi-a { background: linear-gradient(135deg, #1a237e, #5c6bc0); }
      .pred-kpi-b { background: linear-gradient(135deg, #1565c0, #42a5f5); }
      .pred-kpi-c { background: linear-gradient(135deg, #6a1b9a, #ba68c8); }
      .pred-kpi-d { background: linear-gradient(135deg, #e65100, #ffb74d); color: #3e2723; }
      .pred-kpi-label { font-size: 0.72rem; text-transform: uppercase; font-weight: 600; opacity: 0.92; }
      .pred-kpi-value { font-size: 1.35rem; font-weight: 700; margin: 0.35rem 0; }
      .pred-section-title { font-weight: 700; font-size: 0.95rem; color: #37474f; margin: 1rem 0 0.5rem; }
      .pred-table-wrap { border-radius: 10px; overflow: auto; border: 1px solid #e0e0e0; max-height: 420px; }
      .pred-table-wrap table { font-size: 0.82rem; margin-bottom: 0; }
      .pred-table-wrap thead th { background: #e8eaf6; position: sticky; top: 0; z-index: 1; }
      .factor-badge { font-size: 0.72rem; padding: 0.2rem 0.45rem; border-radius: 4px; background: #eceff1; }
      .tab-btn { margin-right: 0.35rem; margin-bottom: 0.35rem; }
      .tab-btn.active { font-weight: 700; }
      .pred-suggestion-box {
        background: #fffde7;
        border: 1px solid #fff176;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
      }
      .pred-suggestion-box ul { margin-bottom: 0; padding-left: 1.2rem; }
      .pred-suggestion-box li { margin-bottom: 0.35rem; font-size: 0.84rem; }
      .pred-suggestion-priority-high { border-left: 4px solid #e53935; }
      .pred-suggestion-priority-medium { border-left: 4px solid #fb8c00; }
      .pred-suggestion-priority-low { border-left: 4px solid #43a047; }
      .pred-customer-tip { font-size: 0.75rem; color: #455a64; max-width: 280px; }
      .pred-customer-tip li { margin-bottom: 0.2rem; }
      .pred-weather-area-card {
        background: #e3f2fd;
        border: 1px solid #90caf9;
        border-radius: 8px;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
      }
      .ai-brand { letter-spacing: 0.02em; }
    `,
  ],
})
export class CollectionPredictionReportComponent implements OnInit {
  filterForm: FormGroup;
  isSuperAdmin = false;
  companyList: any[] = [];
  branchList: any[] = [];
  customerList: any[] = [];
  areaList: any[] = [];
  employeeList: any[] = [];
  loading = false;
  submitted = false;
  previewMode = false;
  report: any = null;
  activeTab: 'customer' | 'area' | 'agent' | 'branch' = 'customer';

  constructor(
    private fb: FormBuilder,
    private userService: UserService
  ) {}

  get f() {
    return this.filterForm.controls;
  }

  ngOnInit() {
    this.filterForm = this.fb.group({
      tenant_id: ['', Validators.required],
      branch_id: [''],
      customer_id: [''],
      area_id: [''],
      employee_id: [''],
    });

    const tenantId = localStorage.getItem('tenant_id');
    if (tenantId) {
      this.isSuperAdmin = false;
      this.filterForm.patchValue({ tenant_id: tenantId });
      this.setBranchList(tenantId);
    } else {
      this.isSuperAdmin = true;
      this.userService.getData('', 'list-tenants', 'POST').subscribe((res: any) => {
        if (res.status === 'Success') {
          this.companyList = res.data || [];
        }
      });
    }
    this.useSample();
    this.loadReport();
  }

  companyChg(event: Event) {
    const tid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({
      tenant_id: tid,
      branch_id: '',
      customer_id: '',
      area_id: '',
      employee_id: '',
    });
    this.customerList = [];
    this.areaList = [];
    this.employeeList = [];
    this.setBranchList(tid);
  }

  branchChg(event: Event) {
    const bid = (event.target as HTMLSelectElement).value;
    this.filterForm.patchValue({
      customer_id: '',
      area_id: '',
      employee_id: '',
    });
    if (!bid) {
      this.customerList = [];
      this.areaList = [];
      this.employeeList = [];
      return;
    }
    const cond = { 'conditions[branch_id]': bid };
    this.userService.getData(cond, 'get-customers-view', 'POST').subscribe((res: any) => {
      this.customerList = res.status === 'Success' ? res.data || [] : [];
    });
    this.userService.getData(cond, 'get-collection-areas', 'POST').subscribe((res: any) => {
      this.areaList = res.status === 'Success' ? res.data || [] : [];
    });
    this.userService.getData(cond, 'get-employees', 'POST').subscribe((res: any) => {
      this.employeeList = res.status === 'Success' ? res.data || [] : [];
    });
  }

  setBranchList(tenant_id: string) {
    this.userService.getData({
      'conditions[tenant_id]': tenant_id,
      log_id: localStorage.getItem('logged_employee_id'),
    }, 'get-branches', 'POST').subscribe((res: any) => {
      this.branchList = res.status === 'Success' ? res.data || [] : [];
    }, () => {
      this.branchList = [];
    });
  }

  num(v: any): number {
    const n = +v;
    return isNaN(n) ? 0 : n;
  }

  setTab(tab: 'customer' | 'area' | 'agent' | 'branch') {
    this.activeTab = tab;
  }

  tabRows(): any[] {
    if (!this.report) {
      return [];
    }
    return this.report['by_' + this.activeTab] || [];
  }

  loadReport() {
    this.submitted = true;
    if (this.filterForm.invalid) {
      this.useSample();
      return;
    }
    this.loading = true;
    const payload: any = { tenant_id: this.filterForm.get('tenant_id').value };
    ['branch_id', 'customer_id', 'area_id', 'employee_id'].forEach((k) => {
      const v = this.filterForm.get(k).value;
      if (v) {
        payload[k] = v;
      }
    });

    this.userService.getData(payload, 'collection-prediction-report', 'JSON').subscribe((res: any) => {
      this.loading = false;
      const count = res && res.data && res.data.summary ? +res.data.summary.enrollment_count : 0;
      if (res.status === 'Success' && res.data && count > 0) {
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
      const customers = this.report.by_customer || [];
      this.report.field_calls = rows.slice(0, 8).map((row: any, index: number) => {
        const customer = customers[index % Math.max(customers.length, 1)] || {};
        const place = row.location_name || ((row.latitude && row.longitude) ? (row.latitude + ', ' + row.longitude) : 'the live map point');
        return {
          employee_name: row.employee_name || 'Employee',
          location_name: place,
          seen_at: row.live_location_view || 'Live',
          customer_name: customer.customer_name || 'the nearest pending customer',
          ar_no: customer.ar_no || '',
          area_name: customer.area_name || row.branch_name || '',
          pending: customer.pending_amount || 0,
          maps_link: row.maps_link || '',
          mobile: row.mobile_no || ''
        };
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
    const today = new Date().toISOString().slice(0, 10);
    return {
      period: {
        month_label: month,
        to_date: today,
        days_elapsed: 23,
        working_days_remaining: 6,
        prediction_mode: 'remainder_forecast',
      },
      summary: {
        collected_mtd: 1865000,
        pending_total: 2420000,
        predicted_remaining: 1684000,
        predicted_month_total: 3549000,
        confidence_pct: 86,
        enrollment_count: 6,
      },
      agent_attendance: {
        message: '5 of 6 collection employees came without leave. Estimated field capacity is 92%.',
        total_agents: 6,
        attendance_pct: 83,
        estimated_capacity_pct: 92,
        agents_on_leave: 1,
      },
      suggestions: [
        { priority: 'high', message: 'Priya S is currently at Anna Nagar, 2nd Avenue. Please call her and send her to visit Ramesh Kumar (AR-1042), pending ₹50,000.' },
        { priority: 'high', message: 'Arun M is currently near Porur signal. Please call him and send him to visit Kavitha N (AR-1422) before day 28.' },
        { priority: 'high', message: 'Predicted month-end collection is ₹35,49,000 (₹18,65,000 already collected + ₹16,84,000 still expected). Confidence about 86%.' },
        { priority: 'medium', message: 'Karthik R last tracked at KK Nagar. He has 2 leave days — call Divya K to cover Lakshmi Devi (AR-1188) if he is not moving.' },
        { priority: 'low', message: 'Customers who usually pay between the 25th and the 28th should be visited this week, before month-end.' },
      ],
      field_calls: [
        { employee_name: 'Priya S', location_name: 'Anna Nagar, 2nd Avenue', seen_at: 'Just now', customer_name: 'Ramesh Kumar', ar_no: 'AR-1042', area_name: 'Anna Nagar', pending: 50000, maps_link: 'https://www.google.com/maps?q=13.0878,80.2085', mobile: '9840011122' },
        { employee_name: 'Arun M', location_name: 'Porur signal', seen_at: '4 min ago', customer_name: 'Kavitha N', ar_no: 'AR-1422', area_name: 'Porur', pending: 40000, maps_link: 'https://www.google.com/maps?q=13.0382,80.1565', mobile: '9840033344' },
        { employee_name: 'Divya K', location_name: 'Velachery 100 ft road', seen_at: '8 min ago', customer_name: 'Meena Krishnan', ar_no: 'AR-1310', area_name: 'Velachery', pending: 90000, maps_link: 'https://www.google.com/maps?q=12.9750,80.2210', mobile: '9840055566' },
      ],
      factor_impact: [
        { label: 'Customer previous payments', impact: 'High', note: 'Average days from commitment due date to receipt date.' },
        { label: 'Employee previous collections', impact: 'High', note: 'Last 3 months of commitment receipts by employee.' },
        { label: 'Government holidays', impact: 'Medium', note: '2 holiday days remain this month.' },
        { label: 'Weekend (Sunday)', impact: 'Medium', note: '1 Sunday left. 6 working days remain.' },
        { label: 'Salary / payment type', impact: 'Medium', note: 'Monthly salary customers pay best in the first week and just after salary day.' },
        { label: 'Weather', impact: 'Low–Medium', note: 'Light rain expected in two areas after 4pm. Morning visits are clearer.' },
        { label: 'Employee leave', impact: 'Medium', note: '1 employee with leave recorded. Capacity is still above 90%.' },
        { label: 'Unexpected buffer', impact: 'Low', note: '6% buffer for closed houses, office closure, and missed routes.' },
      ],
      weather: {
        provider: 'Open-Meteo',
        summary: 'Two rainy windows today after 4pm in Anna Nagar and KK Nagar. Morning collection is clear.',
        by_area: [
          {
            area_name: 'Anna Nagar',
            best_collection_slots_today: ['Best window today: 9:00am – 1:00pm, before the evening shower.'],
            today_rainy_windows: [{ suggestion: 'Anna Nagar: rain 4:00pm–6:30pm today — collect before 4pm or after 7pm.' }],
          },
          {
            area_name: 'KK Nagar',
            best_collection_slots_today: ['Best window today: 8:30am – 12:30pm.'],
            today_rainy_windows: [{ suggestion: 'KK Nagar: rain 5:00pm–7:00pm — finish the route before 4:30pm.' }],
          },
        ],
      },
      external_tn_holidays: [
        { date: today, event_name: 'No public holiday today', source: 'Tamil Nadu calendar' },
      ],
      agent_leave_summary: [
        { agent_name: 'Karthik R', leave_days_mtd: 2, factor: 0.82, suggestions: ['Karthik took 2 leave days. Shift his high-pending customers to Priya for those dates.'] },
        { agent_name: 'Priya S', leave_days_mtd: 0, factor: 1, suggestions: ['Priya has full attendance. She can cover extra pending customers this week.'] },
      ],
      by_customer: [
        { customer_name: 'Ramesh Kumar', ar_no: 'AR-1042', area_name: 'Anna Nagar', agent_name: 'Priya S', payment_type_label: 'Monthly', avg_payment_delay_days: 4, typical_payment_day: 26, collected_mtd: 25000, pending_amount: 50000, predicted_remaining: 42000, collection_probability_pct: 84, suggestions: ['Usually pays around day 26. Visit from today.', 'Monthly salary customer — best window is just after salary day.'] },
        { customer_name: 'Lakshmi Devi', ar_no: 'AR-1188', area_name: 'KK Nagar', agent_name: 'Karthik R', payment_type_label: 'Monthly', avg_payment_delay_days: 18, typical_payment_day: 12, collected_mtd: 0, pending_amount: 75000, predicted_remaining: 48000, collection_probability_pct: 64, suggestions: ['Typical pay day has passed. Follow up today for the pending balance.', 'No payment yet this month — priority call before the field visit.'] },
        { customer_name: 'Suresh Babu', ar_no: 'AR-0904', area_name: 'T Nagar', agent_name: 'Arun M', payment_type_label: 'Weekly', avg_payment_delay_days: 2, collected_mtd: 18000, pending_amount: 12000, predicted_remaining: 11000, collection_probability_pct: 91, suggestions: ['Pays quickly. A short visit this week should close the balance.'] },
        { customer_name: 'Meena Krishnan', ar_no: 'AR-1310', area_name: 'Velachery', agent_name: 'Divya K', payment_type_label: 'Monthly', avg_payment_delay_days: 31, collected_mtd: 10000, pending_amount: 90000, predicted_remaining: 52000, collection_probability_pct: 58, suggestions: ['Slow payer. Assign an experienced employee and call before the visit.'] },
        { customer_name: 'Abdul Rahman', ar_no: 'AR-0771', area_name: 'Tambaram', agent_name: 'Priya S', payment_type_label: 'Daily', avg_payment_delay_days: 6, collected_mtd: 22000, pending_amount: 28000, predicted_remaining: 24000, collection_probability_pct: 86, suggestions: ['Daily payment type — small frequent visits work best from today.'] },
        { customer_name: 'Kavitha N', ar_no: 'AR-1422', area_name: 'Porur', agent_name: 'Arun M', payment_type_label: 'Monthly', avg_payment_delay_days: 9, typical_payment_day: 28, collected_mtd: 15000, pending_amount: 40000, predicted_remaining: 33000, collection_probability_pct: 78, suggestions: ['Usually pays near day 28. Plan the visit before month-end.'] },
      ],
      by_area: [
        { area_name: 'Anna Nagar', collected_mtd: 420000, pending_amount: 510000, predicted_remaining: 390000, predicted_month_total: 810000 },
        { area_name: 'KK Nagar', collected_mtd: 310000, pending_amount: 640000, predicted_remaining: 410000, predicted_month_total: 720000 },
        { area_name: 'Velachery', collected_mtd: 280000, pending_amount: 470000, predicted_remaining: 300000, predicted_month_total: 580000 },
      ],
      by_agent: [
        { agent_name: 'Priya S', collected_mtd: 620000, pending_amount: 540000, predicted_remaining: 460000, predicted_month_total: 1080000 },
        { agent_name: 'Arun M', collected_mtd: 480000, pending_amount: 610000, predicted_remaining: 430000, predicted_month_total: 910000 },
        { agent_name: 'Karthik R', collected_mtd: 290000, pending_amount: 720000, predicted_remaining: 380000, predicted_month_total: 670000 },
      ],
      by_branch: [
        { branch_name: 'Chennai Central', collected_mtd: 1120000, pending_amount: 1480000, predicted_remaining: 1040000, predicted_month_total: 2160000 },
        { branch_name: 'Tambaram', collected_mtd: 745000, pending_amount: 940000, predicted_remaining: 644000, predicted_month_total: 1389000 },
      ],
      notes: {
        ai_method: 'Sample analysis by Emglitz Intelligence AI. Live commitment data will replace this preview when the prediction service returns customers.',
      },
    };
  }
}
