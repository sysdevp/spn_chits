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
        if (file_exists($sqlPath) && !Schema::hasTable('employee_skills')) {
            DB::unprepared(file_get_contents($sqlPath));
        }
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
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required', 'employee_id' => 'required', 'plan_date' => 'required|date', 'title' => 'required',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeWorkPlan();
        $row->fill($request->only('tenant_id', 'branch_id', 'employee_id', 'plan_date', 'title', 'description', 'plan_status'));
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
        if ($request->employee_id) $q->where('employee_id', (int) $request->employee_id);
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
        $v = Validator::make($request->all(), array(
            'tenant_id' => 'required', 'employee_id' => 'required', 'visit_date' => 'required|date', 'visit_type' => 'required',
        ));
        if ($v->fails()) return response()->json(array('status' => 'Error', 'msg' => $v->errors()->first()), 200);
        $row = new EmployeeCustomerVisit();
        $row->fill($request->only(
            'tenant_id', 'branch_id', 'employee_id', 'customer_id', 'lead_id',
            'visit_type', 'visit_date', 'visit_time', 'latitude', 'longitude',
            'purpose', 'outcome', 'followup_date', 'visit_status'
        ));
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
        if ($request->employee_id) $q->where('employee_id', (int) $request->employee_id);
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
        if ($request->tenant_id && $request->branch_id) {
            $employees = DB::table('employees')
                ->where('tenant_id', (int) $request->tenant_id)
                ->where('branch_id', (int) $request->branch_id)
                ->where('status', 1)
                ->pluck('id');
            foreach ($employees as $eid) {
                EmployeeMetricsService::computePerformance((int) $request->tenant_id, (int) $request->branch_id, (int) $eid, $month);
            }
        }
        $list = EmployeeMetricsService::leaderboard(
            (int) $request->tenant_id,
            (int) ($request->branch_id ?: 0),
            $month,
            (int) ($request->limit ?: 20)
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
}
