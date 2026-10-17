<?php
namespace App\Http\Controllers;

use App\Models\EmployeeAttendanceSetting;
use App\Models\EmployeeBadge;
use App\Models\EmployeeBadgeAward;
use App\Models\EmployeeCertificate;
use App\Models\EmployeeCustomerVisit;
use App\Models\EmployeePerformanceScore;
use App\Models\EmployeeSkill;
use App\Models\EmployeeSkillAssessment;
use App\Models\EmployeeWorkPlan;
use App\Services\EmployeeMetricsService;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Validator;

class EmployeeExtendedController extends Controller
{
    private function switchDb(Request $request)
    {
        $db = $request->db ?: $request->database_name;
        if ($db) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $db);
            DB::reconnect('mysql');
        }
        $this->ensureTables();
    }

    private function ensureTables()
    {
        $sqlPath = base_path('database/sql/create_employee_mobile_extended_modules.sql');
        $needed = array(
            'employee_skills',
            'employee_skill_assessments',
            'employee_performance_scores',
            'employee_work_plans',
            'employee_customer_visits',
        );
        $missing = false;
        foreach ($needed as $table) {
            if (!Schema::hasTable($table)) {
                $missing = true;
                break;
            }
        }
        if ($missing && file_exists($sqlPath)) {
            DB::unprepared(file_get_contents($sqlPath));
        }
        $this->ensureLoanTable();
    }

    private function empName($emp)
    {
        return $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
    }

    // ---------- SKILLS MASTER ----------
    public function store_skill(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array('tenant_id' => 'required', 'skill_name' => 'required|max:150'));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeSkill();
        $row->fill($request->only('tenant_id', 'skill_name', 'category', 'description'));
        $row->created_by = (int) ($request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Skill saved', 'data' => $row), 200);
    }

    public function list_skills(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeSkill::where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('skill_name')->get()), 200);
    }

    public function update_skill(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeSkill::find($request->id);
        if (!$row) return response()->json(array('status' => 'Error', 'msg' => 'Not found'), 200);
        $row->fill($request->only('skill_name', 'category', 'description', 'status'));
        $row->updated_by = (int) ($request->updated_by ?: 0);
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Updated', 'data' => $row), 200);
    }

    public function delete_skill(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeSkill::find($request->id);
        if ($row) $row->delete();
        return response()->json(array('status' => 'Success', 'msg' => 'Deleted'), 200);
    }

    // ---------- SKILL ASSESSMENTS ----------
    public function store_skill_assessment(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required', 'employee_id' => 'required', 'skill_id' => 'required', 'score' => 'required|numeric|min:0|max:100',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeSkillAssessment();
        $row->fill($request->only(
            'tenant_id', 'branch_id', 'employee_id', 'skill_id', 'score',
            'strength_notes', 'weakness_notes', 'training_required', 'training_notes', 'assessed_date'
        ));
        $row->assessed_by = (int) ($request->assessed_by ?: $request->created_by ?: 0);
        $row->assessed_date = $request->assessed_date ?: Carbon::today()->toDateString();
        $row->created_by = (int) ($request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Assessment saved', 'data' => $row->load('skill_det')), 200);
    }

    public function list_skill_assessments(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeSkillAssessment::with(array('skill_det', 'employee_det'))->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ($request->employee_id) $q->where('employee_id', (int) $request->employee_id);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('id', 'desc')->limit(200)->get()), 200);
    }

    public function delete_skill_assessment(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeSkillAssessment::find($request->id);
        if ($row) $row->delete();
        return response()->json(array('status' => 'Success', 'msg' => 'Deleted'), 200);
    }

    // ---------- PERFORMANCE ----------
    public function get_employee_performance(Request $request)
    {
        $this->switchDb($request);
        $employeeId = (int) ($request->employee_id ?: 0);
        if ($employeeId <= 0) {
            return response()->json(array(
                'status' => 'Success',
                'data' => array(
                    'performance' => null,
                    'skill_assessments' => array(),
                    'period_month' => $request->period_month ?: Carbon::today()->format('Y-m'),
                ),
            ), 200);
        }
        $v = Validator::make($request->all(), array('tenant_id' => 'required', 'employee_id' => 'required'));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $month = $request->period_month ?: Carbon::today()->format('Y-m');
        EmployeeMetricsService::syncTargetProgress((int) $request->tenant_id, (int) $request->employee_id);
        $perf = EmployeeMetricsService::computePerformance(
            (int) $request->tenant_id,
            (int) ($request->branch_id ?: 0),
            (int) $request->employee_id,
            $month
        );
        $assessments = EmployeeSkillAssessment::with('skill_det')
            ->where('employee_id', (int) $request->employee_id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'performance' => $perf,
                'skill_assessments' => $assessments,
                'period_month' => $month,
            ),
        ), 200);
    }

    public function list_performance_scores(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeePerformanceScore::with('employee_det')->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ($request->branch_id) $q->where('branch_id', (int) $request->branch_id);
        if ($request->period_month) $q->where('period_month', $request->period_month);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('overall_score', 'desc')->limit(100)->get()), 200);
    }

    // ---------- WORK PLANS ----------
    public function store_work_plan(Request $request)
    {
        $this->switchDb($request);
        $empId = (int) ($request->employee_id ?: $request->user_id ?: 0);
        $v = Validator::make(array_merge($request->all(), array('employee_id' => $empId)), array(
            'tenant_id' => 'required', 'employee_id' => 'required|numeric|min:1', 'plan_date' => 'required|date', 'title' => 'required',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeWorkPlan();
        $row->fill($request->only('tenant_id', 'branch_id', 'plan_date', 'title', 'description', 'plan_status'));
        $row->employee_id = $empId;
        $row->plan_status = $request->plan_status ?: 'pending';
        $row->created_by = (int) ($request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Work plan saved', 'data' => $row), 200);
    }

    public function list_work_plans(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeWorkPlan::with('employee_det')->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ((int) $request->employee_id > 0) $q->where('employee_id', (int) $request->employee_id);
        if ($request->plan_date) $q->whereDate('plan_date', $request->plan_date);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('plan_date', 'desc')->limit(200)->get()), 200);
    }

    public function update_work_plan_status(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeWorkPlan::find($request->id);
        if (!$row) return response()->json(array('status' => 'Error', 'msg' => 'Not found'), 200);
        $row->plan_status = $request->plan_status ?: $row->plan_status;
        $row->updated_by = (int) ($request->updated_by ?: 0);
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Updated', 'data' => $row), 200);
    }

    public function delete_work_plan(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeWorkPlan::find($request->id);
        if ($row) $row->delete();
        return response()->json(array('status' => 'Success', 'msg' => 'Deleted'), 200);
    }

    // ---------- CUSTOMER VISITS ----------
    public function store_customer_visit(Request $request)
    {
        $this->switchDb($request);
        $empId = (int) ($request->employee_id ?: $request->user_id ?: 0);
        $v = Validator::make(array_merge($request->all(), array('employee_id' => $empId)), array(
            'tenant_id' => 'required', 'employee_id' => 'required|numeric|min:1', 'visit_date' => 'required|date', 'visit_type' => 'required',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeCustomerVisit();
        $row->fill($request->only(
            'tenant_id', 'branch_id', 'customer_id', 'lead_id',
            'visit_type', 'visit_date', 'visit_time', 'latitude', 'longitude',
            'purpose', 'outcome', 'followup_date', 'visit_status'
        ));
        $row->employee_id = $empId;
        $row->created_by = (int) ($request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Visit recorded', 'data' => $row), 200);
    }

    public function list_customer_visits(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeCustomerVisit::with('employee_det')->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ((int) $request->employee_id > 0) $q->where('employee_id', (int) $request->employee_id);
        if ($request->visit_date) $q->whereDate('visit_date', $request->visit_date);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('visit_date', 'desc')->limit(200)->get()), 200);
    }

    public function delete_customer_visit(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeCustomerVisit::find($request->id);
        if ($row) $row->delete();
        return response()->json(array('status' => 'Success', 'msg' => 'Deleted'), 200);
    }

    // ---------- BADGES ----------
    public function store_badge(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required', 'badge_name' => 'required', 'badge_code' => 'required', 'criteria_type' => 'required',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeBadge();
        $row->fill($request->only('tenant_id', 'badge_name', 'badge_code', 'criteria_type', 'criteria_value', 'icon', 'description'));
        $row->created_by = (int) ($request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Badge saved', 'data' => $row), 200);
    }

    public function list_badges(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeBadge::where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('badge_name')->get()), 200);
    }

    public function delete_badge(Request $request)
    {
        $this->switchDb($request);
        $row = EmployeeBadge::find($request->id);
        if ($row) $row->delete();
        return response()->json(array('status' => 'Success', 'msg' => 'Deleted'), 200);
    }

    // ---------- ACHIEVEMENTS / LEADERBOARD ----------
    public function get_employee_achievements(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array('tenant_id' => 'required', 'employee_id' => 'required'));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $month = $request->period_month ?: Carbon::today()->format('Y-m');
        EmployeeMetricsService::refreshAchievements(
            (int) $request->tenant_id,
            (int) ($request->branch_id ?: 0),
            (int) $request->employee_id,
            $month
        );
        $perf = EmployeePerformanceScore::where('employee_id', (int) $request->employee_id)
            ->where('period_month', $month)->first();
        $badges = EmployeeBadgeAward::with('badge_det')
            ->where('employee_id', (int) $request->employee_id)
            ->where('period_month', $month)
            ->get();
        $certs = EmployeeCertificate::where('employee_id', (int) $request->employee_id)
            ->where('status', 1)
            ->orderBy('issued_date', 'desc')
            ->limit(20)
            ->get();
        $leaderboard = EmployeeMetricsService::leaderboard(
            (int) $request->tenant_id,
            (int) ($request->branch_id ?: 0),
            $month,
            10
        );
        $myRank = 0;
        foreach ($leaderboard as $item) {
            if ((int) $item['employee_id'] === (int) $request->employee_id) {
                $myRank = (int) $item['rank'];
                break;
            }
        }
        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'period_month' => $month,
                'monthly_achievement_percent' => $perf ? (float) $perf->overall_score : 0,
                'performance_rating' => $perf ? $perf->performance_rating : 'Average',
                'is_top_performer' => ($myRank === 1),
                'my_rank' => $myRank,
                'badges' => $badges,
                'certificates' => $certs,
                'leaderboard' => $leaderboard,
            ),
        ), 200);
    }

    public function get_leaderboard(Request $request)
    {
        $this->switchDb($request);
        $month = $request->period_month ?: Carbon::today()->format('Y-m');
        $tenantId = (int) $request->tenant_id;
        $branchId = (int) ($request->branch_id ?: 0);
        if ($tenantId) {
            $empQ = DB::table('employees')->where('tenant_id', $tenantId);
            if (Schema::hasColumn('employees', 'status')) {
                $empQ->where('status', 1);
            }
            if ($branchId) {
                $empQ->where('branch_id', $branchId);
            }
            $employees = $empQ->limit(80)->get(array('id', 'branch_id'));
            foreach ($employees as $emp) {
                EmployeeMetricsService::computePerformance($tenantId, (int) ($emp->branch_id ?: $branchId), (int) $emp->id, $month);
            }
        }
        $list = EmployeeMetricsService::leaderboard(
            $tenantId,
            $branchId,
            $month,
            (int) ($request->limit ?: 50)
        );
        return response()->json(array('status' => 'Success', 'data' => $list, 'period_month' => $month), 200);
    }

    public function store_certificate(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required', 'employee_id' => 'required', 'certificate_title' => 'required', 'issued_date' => 'required|date',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeCertificate();
        $row->fill($request->only(
            'tenant_id', 'branch_id', 'employee_id', 'certificate_title',
            'certificate_type', 'period_month', 'issued_date', 'remarks'
        ));
        $row->issued_by = (int) ($request->issued_by ?: $request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Certificate issued', 'data' => $row), 200);
    }

    public function list_certificates(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeCertificate::with('employee_det')->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ($request->employee_id) $q->where('employee_id', (int) $request->employee_id);
        return response()->json(array('status' => 'Success', 'data' => $q->orderBy('issued_date', 'desc')->limit(100)->get()), 200);
    }

    // ---------- ATTENDANCE SETTINGS ----------
    public function store_attendance_settings(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array('tenant_id' => 'required', 'shift_start_time' => 'required', 'shift_end_time' => 'required'));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $branchId = (int) ($request->branch_id ?: 0);
        $row = EmployeeAttendanceSetting::firstOrNew(array(
            'tenant_id' => (int) $request->tenant_id,
            'branch_id' => $branchId,
        ));
        $row->fill($request->only('shift_start_time', 'shift_end_time', 'late_grace_minutes', 'early_leave_grace_minutes'));
        $row->created_by = $row->exists ? $row->created_by : (int) ($request->created_by ?: 0);
        $row->updated_by = (int) ($request->updated_by ?: $request->created_by ?: 0);
        $row->status = 1;
        $row->save();
        return response()->json(array('status' => 'Success', 'msg' => 'Settings saved', 'data' => $row), 200);
    }

    public function list_attendance_settings(Request $request)
    {
        $this->switchDb($request);
        $q = EmployeeAttendanceSetting::where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ($request->branch_id) $q->where('branch_id', (int) $request->branch_id);
        return response()->json(array('status' => 'Success', 'data' => $q->get()), 200);
    }

    // ---------- CONVERSION TRACKING ----------
    public function get_conversion_stats(Request $request)
    {
        $this->switchDb($request);
        $v = Validator::make($request->all(), array('tenant_id' => 'required', 'employee_id' => 'required'));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $from = $request->from_date ?: Carbon::today()->startOfMonth()->toDateString();
        $to = $request->to_date ?: Carbon::today()->toDateString();
        $leads = EmployeeMetricsService::salesCount((int) $request->tenant_id, (int) $request->employee_id, $from, $to);
        $converted = EmployeeMetricsService::conversionCount((int) $request->tenant_id, (int) $request->employee_id, $from, $to);
        $rate = $leads > 0 ? round(($converted / $leads) * 100, 2) : 0;
        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'leads_count' => $leads,
                'converted_count' => $converted,
                'conversion_rate' => $rate,
                'from_date' => $from,
                'to_date' => $to,
            ),
        ), 200);
    }

    public function get_work_snapshot(Request $request)
    {
        $this->switchDb($request);
        $tenantId = (int) $request->tenant_id;
        $employeeId = (int) ($request->employee_id ?: 0);
        $branchId = (int) ($request->branch_id ?: 0);
        $today = Carbon::today()->toDateString();
        $month = Carbon::today()->format('Y-m');
        $monthStart = Carbon::today()->startOfMonth()->toDateString();

        $targets = $employeeId
            ? EmployeeMetricsService::targetBreakdown($tenantId, $employeeId, $today)
            : array(
                'sales' => EmployeeMetricsService::emptyTargetBucket('Sales', 'sales'),
                'collection' => EmployeeMetricsService::emptyTargetBucket('Collection', 'collection'),
                'new_customer' => EmployeeMetricsService::emptyTargetBucket('New customer', 'new_client_collection'),
                'old_customer' => EmployeeMetricsService::emptyTargetBucket('Old customer', 'existing_client_collection'),
            );

        $scores = EmployeeMetricsService::scoresBoard($tenantId, 0, $month, 80);
        $topEmployees = array_slice($scores, 0, 3);
        $topEmployees = EmployeeMetricsService::ensureTopEmployees($topEmployees, $tenantId, 3);
        $topBranches = EmployeeMetricsService::ensureTopBranches(
            EmployeeMetricsService::topBranchesByCollection($tenantId, $monthStart, $today, 6),
            $tenantId,
            3
        );

        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'period_month' => $month,
                'targets' => $targets,
                'scores' => $scores,
                'topEmployees' => $topEmployees,
                'topBranches' => $topBranches,
            ),
        ), 200);
    }

    public function get_employee_incentives(Request $request)
    {
        $this->switchDb($request);
        $employeeId = (int) ($request->employee_id ?: 0);
        $tenantId = (int) $request->tenant_id;
        $emp = \App\Models\Employee::find($employeeId);
        $rate = $emp ? (float) ($emp->incentive ?? 0) : 0;
        $commission = $emp ? (float) ($emp->commission ?? 0) : 0;

        $sums = (object) array(
            'total_incentive' => 0,
            'paid_incentive' => 0,
            'pending_incentive' => 0,
            'enrol_count' => 0,
        );
        if ($employeeId > 0 && Schema::hasTable('chit_details') && Schema::hasColumn('chit_details', 'introducer_id')) {
            $enrolQ = \App\Models\ChitDetail::where('introducer_id', $employeeId);
            if ($tenantId) {
                $enrolQ->where('tenant_id', $tenantId);
            }
            if (Schema::hasColumn('chit_details', 'agent_incentive')) {
                $sums = $enrolQ->selectRaw(
                    'SUM(agent_incentive) as total_incentive, SUM(paid_agent_incentive) as paid_incentive, SUM(balance_agent_incentive) as pending_incentive, COUNT(*) as enrol_count'
                )->first() ?: $sums;
            } else {
                $sums->enrol_count = (int) $enrolQ->count();
            }
        }

        $payments = array();
        if (Schema::hasTable('agent_incentive_payments')) {
            $payQ = \App\Models\AgentIncentivePayment::query();
            if (Schema::hasColumn('agent_incentive_payments', 'employee_agent_id')) {
                $payQ->where('employee_agent_id', $employeeId);
            }
            if ($tenantId) {
                $payQ->where('tenant_id', $tenantId);
            }
            $payments = $payQ->orderBy('id', 'desc')->limit(30)->get();
        }

        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'incentive_percent' => $rate,
                'commission_percent' => $commission,
                'total_incentive' => (float) ($sums->total_incentive ?? 0),
                'paid_incentive' => (float) ($sums->paid_incentive ?? 0),
                'pending_incentive' => (float) ($sums->pending_incentive ?? 0),
                'enrol_count' => (int) ($sums->enrol_count ?? 0),
                'payments' => $payments,
            ),
        ), 200);
    }

    public function get_daily_report(Request $request)
    {
        $this->switchDb($request);
        $tenantId = (int) $request->tenant_id;
        $branchId = (int) ($request->branch_id ?: 0);
        $employeeId = (int) ($request->employee_id ?: 0);
        $date = $request->on_date ?: Carbon::today()->toDateString();

        $receiptQ = \App\Models\Receipt::where('tenant_id', $tenantId)->where('receipt_date', $date);
        if ($branchId) $receiptQ->where('branch_id', $branchId);
        if ($employeeId) $receiptQ->where('employee_id', $employeeId);
        $receipts = $receiptQ->get();
        $cash = 0; $other = 0;
        foreach ($receipts as $r) {
            $amt = (float) ($r->received_amount ?? 0) + (float) ($r->penalty ?? 0);
            if ((int) $r->payment_type_id === 1) $cash += $amt;
            else $other += $amt;
        }

        $att = null;
        if ($employeeId) {
            $att = \App\Models\EmployeeAttendance::where('tenant_id', $tenantId)
                ->where('employee_id', $employeeId)
                ->whereDate('att_date', $date)
                ->orderBy('id', 'desc')
                ->first();
        }

        $tasksTotal = 0; $tasksDone = 0;
        if (Schema::hasTable('employee_tasks')) {
            $tq = \App\Models\EmployeeTask::where('tenant_id', $tenantId)->whereDate('task_date', $date)->where('status', 1);
            if ($employeeId) $tq->where('employee_id', $employeeId);
            $tasks = $tq->get();
            $tasksTotal = $tasks->count();
            $tasksDone = $tasks->where('task_status', 'completed')->count();
        }

        $leads = 0;
        if (Schema::hasTable('lead_managements') || Schema::hasTable('lead_management')) {
            try {
                $lq = \App\Models\LeadManagement::where('tenant_id', $tenantId)->whereDate('created_at', $date);
                if ($employeeId) $lq->where('employee_id', $employeeId);
                $leads = $lq->count();
            } catch (\Exception $e) {
                $leads = 0;
            }
        }

        return response()->json(array(
            'status' => 'Success',
            'data' => array(
                'on_date' => $date,
                'receipts_count' => $receipts->count(),
                'cash_collection' => round($cash, 2),
                'other_collection' => round($other, 2),
                'total_collection' => round($cash + $other, 2),
                'check_in_time' => $att ? $att->check_in_time : null,
                'check_out_time' => $att ? $att->check_out_time : null,
                'punch_status' => $att ? ($att->punch_status ?? '') : '',
                'tasks_total' => $tasksTotal,
                'tasks_completed' => $tasksDone,
                'leads_today' => $leads,
            ),
        ), 200);
    }

    private function ensureLoanTable()
    {
        if (Schema::hasTable('employee_loan_advances')) {
            return;
        }
        Schema::create('employee_loan_advances', function ($table) {
            $table->increments('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('employee_id')->default(0);
            $table->string('request_type', 20)->default('advance');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('tenure_months')->default(0);
            $table->decimal('emi_amount', 15, 2)->default(0);
            $table->date('request_date');
            $table->text('reason')->nullable();
            $table->string('request_status', 20)->default('pending');
            $table->integer('decided_by')->default(0);
            $table->dateTime('decided_at')->nullable();
            $table->text('decision_remarks')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function formatLoan($row)
    {
        $emp = $row->employee_det;
        $name = $emp ? trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : '')) : '';
        return array(
            'id' => (int) $row->id,
            'tenant_id' => (int) $row->tenant_id,
            'branch_id' => (int) $row->branch_id,
            'employee_id' => (int) $row->employee_id,
            'employee_name' => $name,
            'request_type' => $row->request_type,
            'amount' => (float) $row->amount,
            'tenure_months' => (int) $row->tenure_months,
            'emi_amount' => (float) $row->emi_amount,
            'request_date' => $row->request_date,
            'reason' => $row->reason,
            'request_status' => $row->request_status,
            'decision_remarks' => $row->decision_remarks,
            'decided_at' => $row->decided_at,
            'created_at' => $row->created_at ? (string) $row->created_at : '',
        );
    }

    public function store_employee_loan_advance(Request $request)
    {
        $this->switchDb($request);
        $this->ensureLoanTable();
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required',
            'employee_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'request_type' => 'required|in:loan,advance',
        ));
        if ($v->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        }
        $row = new \App\Models\EmployeeLoanAdvance();
        $row->tenant_id = (int) $request->tenant_id;
        $row->branch_id = (int) ($request->branch_id ?: 0);
        $row->employee_id = (int) $request->employee_id;
        $row->request_type = $request->request_type === 'loan' ? 'loan' : 'advance';
        $row->amount = (float) $request->amount;
        $row->tenure_months = (int) ($request->tenure_months ?: 0);
        $row->emi_amount = (float) ($request->emi_amount ?: 0);
        $row->request_date = $request->request_date ?: Carbon::today()->toDateString();
        $row->reason = $request->reason ?: '';
        $row->request_status = 'pending';
        $row->created_by = (int) ($request->created_by ?: $request->user_id ?: 0);
        $row->status = 1;
        $row->save();
        $fresh = \App\Models\EmployeeLoanAdvance::with('employee_det')->find($row->id);
        return response()->json(array('status' => 'Success', 'msg' => 'Request submitted', 'data' => $this->formatLoan($fresh)), 200);
    }

    public function list_employee_loan_advances(Request $request)
    {
        $this->switchDb($request);
        $this->ensureLoanTable();
        $q = \App\Models\EmployeeLoanAdvance::with('employee_det')->where('status', 1);
        if ($request->tenant_id) $q->where('tenant_id', (int) $request->tenant_id);
        if ($request->pending_only) {
            $q->where('request_status', 'pending');
        } elseif ((int) $request->employee_id > 0 && ($request->mine || !$request->for_admin)) {
            $q->where('employee_id', (int) $request->employee_id);
        }
        if ($request->request_status) {
            $q->where('request_status', $request->request_status);
        }
        if ($request->request_type) {
            $q->where('request_type', $request->request_type);
        }
        if ((int) $request->branch_id > 0) {
            $q->where('branch_id', (int) $request->branch_id);
        }
        $rows = $q->with('branch_det')->orderBy('id', 'desc')->limit($request->for_admin ? 2000 : 200)->get();
        $data = array();
        foreach ($rows as $row) {
            $item = $this->formatLoan($row);
            $item['branch_name'] = $row->branch_det ? ($row->branch_det->branch_name ?: '') : '';
            $data[] = $item;
        }
        return response()->json(array('status' => 'Success', 'data' => $data), 200);
    }

    public function decide_employee_loan_advance(Request $request)
    {
        $this->switchDb($request);
        $this->ensureLoanTable();
        $row = \App\Models\EmployeeLoanAdvance::find($request->id);
        if (!$row) {
            return response()->json(array('status' => 'Error', 'msg' => 'Request not found'), 200);
        }
        $status = strtolower((string) $request->request_status);
        if (!in_array($status, array('approved', 'rejected', 'disbursed', 'recovered'), true)) {
            return response()->json(array('status' => 'Error', 'msg' => 'Invalid status'), 200);
        }
        $row->request_status = $status;
        $row->decision_remarks = $request->decision_remarks ?: '';
        $row->decided_by = (int) ($request->decided_by ?: $request->user_id ?: 0);
        $row->decided_at = Carbon::now();
        $row->updated_by = (int) ($request->updated_by ?: $request->user_id ?: 0);
        $row->save();
        $fresh = \App\Models\EmployeeLoanAdvance::with('employee_det')->find($row->id);
        return response()->json(array('status' => 'Success', 'msg' => 'Updated', 'data' => $this->formatLoan($fresh)), 200);
    }
}
