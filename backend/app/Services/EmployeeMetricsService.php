<?php
namespace App\Services;

use App\Models\AdvanceReceipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceSetting;
use App\Models\EmployeeBadge;
use App\Models\EmployeeBadgeAward;
use App\Models\EmployeeCertificate;
use App\Models\EmployeePerformanceScore;
use App\Models\EmployeeTarget;
use App\Models\EmployeeTask;
use App\Models\LeadManagement;
use App\Models\OtherCharge;
use App\Models\Receipt;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Schema;

class EmployeeMetricsService
{
    public static function ratingFromScore($score)
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 75) return 'Good';
        if ($score >= 60) return 'Average';
        if ($score >= 40) return 'Needs Improvement';
        return 'Poor';
    }

    public static function collectionAmount($tenantId, $employeeId, $from, $to)
    {
        $scope = function ($q) use ($tenantId, $employeeId) {
            $q->where('tenant_id', $tenantId)->where('employee_id', $employeeId);
        };
        $r = Receipt::whereBetween('receipt_date', [$from, $to])->where($scope)
            ->selectRaw('SUM(received_amount + penalty) as total')->value('total') ?? 0;
        $a = AdvanceReceipt::whereBetween('receipt_date', [$from, $to])->where($scope)->sum('receipt_amount');
        $o = OtherCharge::whereBetween('received_date', [$from, $to])->where($scope)->sum('amount');
        $b = BeforeEnrolReceipt::whereBetween('received_date', [$from, $to])->where($scope)->sum('amount');
        return (float) ($r + $a + $o + $b);
    }

    public static function salesCount($tenantId, $employeeId, $from, $to)
    {
        return (int) LeadManagement::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])
            ->count();
    }

    public static function conversionCount($tenantId, $employeeId, $from, $to)
    {
        return (int) LeadManagement::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->where('is_customer', 1)
            ->whereBetween(DB::raw('DATE(updated_at)'), [$from, $to])
            ->count();
    }

    public static function taskCompletionPercent($tenantId, $employeeId, $from, $to)
    {
        $tasks = EmployeeTask::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->where('status', 1)
            ->whereBetween('task_date', [$from, $to])
            ->get();
        if ($tasks->isEmpty()) return 100;
        $done = $tasks->where('task_status', 'completed')->count();
        return min(100, round(($done / $tasks->count()) * 100));
    }

    public static function attendancePercent($tenantId, $employeeId, $monthStart, $monthEnd)
    {
        $present = EmployeeAttendance::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereBetween('att_date', [$monthStart, $monthEnd])
            ->where(function ($q) {
                $q->whereNotNull('check_in_time')->orWhere('attendance_type', 1);
            })
            ->count();
        $days = max(1, Carbon::parse($monthStart)->diffInDays(Carbon::parse($monthEnd)) + 1);
        return min(100, round(($present / $days) * 100));
    }

    public static function syncTargetProgress($tenantId, $employeeId, $onDate = null)
    {
        $date = $onDate ?: Carbon::today()->toDateString();
        $targets = EmployeeTarget::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->where('status', 1)
            ->where('period_start', '<=', $date)
            ->where('period_end', '>=', $date)
            ->get();

        foreach ($targets as $tg) {
            $achieved = 0;
            $type = $tg->target_type ?: 'existing_client_collection';
            $end = min($date, $tg->period_end);
            if ($type === 'new_client_collection') {
                $achieved = self::collectionAmountByKind($tenantId, $employeeId, $tg->period_start, $end, 'new');
            } elseif ($type === 'collection' || $type === 'existing_client_collection') {
                $achieved = self::collectionAmountByKind($tenantId, $employeeId, $tg->period_start, $end, 'old');
            } elseif ($type === 'sales' || $type === 'new_business') {
                $achieved = self::salesCount($tenantId, $employeeId, $tg->period_start, $end);
            } elseif ($type === 'activity') {
                $achieved = self::taskCompletionPercent($tenantId, $employeeId, $tg->period_start, $end);
            }
            $tg->achieved_amount = $achieved;
            $tg->save();
        }
        return $targets;
    }

    public static function computePerformance($tenantId, $branchId, $employeeId, $periodMonth = null)
    {
        $month = $periodMonth ?: Carbon::today()->format('Y-m');
        $start = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $end = Carbon::parse($month . '-01')->endOfMonth()->toDateString();
        $today = min(Carbon::today()->toDateString(), $end);

        $emp = Employee::find($employeeId);
        $monthlyTarget = $emp ? (float) ($emp->target ?? 0) : 0;
        $collection = self::collectionAmount($tenantId, $employeeId, $start, $today);
        $collectionScore = $monthlyTarget > 0 ? min(100, round(($collection / $monthlyTarget) * 100)) : ($collection > 0 ? 80 : 0);

        $leads = self::salesCount($tenantId, $employeeId, $start, $today);
        $converted = self::conversionCount($tenantId, $employeeId, $start, $today);
        $salesScore = $leads > 0 ? min(100, round(($converted / $leads) * 100)) : ($converted > 0 ? 100 : 50);

        $activityScore = self::taskCompletionPercent($tenantId, $employeeId, $start, $today);
        $attendanceScore = self::attendancePercent($tenantId, $employeeId, $start, $today);

        $overall = round(($collectionScore * 0.4) + ($salesScore * 0.2) + ($activityScore * 0.2) + ($attendanceScore * 0.2), 2);
        $rating = self::ratingFromScore($overall);

        $strengths = array();
        $weaknesses = array();
        if ($collectionScore >= 75) $strengths[] = 'Collection';
        else $weaknesses[] = 'Collection';
        if ($salesScore >= 75) $strengths[] = 'Sales conversion';
        else $weaknesses[] = 'Sales conversion';
        if ($activityScore >= 75) $strengths[] = 'Task completion';
        else $weaknesses[] = 'Task completion';
        if ($attendanceScore >= 75) $strengths[] = 'Attendance';
        else $weaknesses[] = 'Attendance';

        $row = EmployeePerformanceScore::firstOrNew(array(
            'employee_id' => $employeeId,
            'period_month' => $month,
        ));
        $row->tenant_id = $tenantId;
        $row->branch_id = $branchId;
        $row->collection_score = $collectionScore;
        $row->sales_score = $salesScore;
        $row->activity_score = $activityScore;
        $row->attendance_score = $attendanceScore;
        $row->overall_score = $overall;
        $row->performance_rating = $rating;
        $row->strengths = implode(', ', $strengths);
        $row->weaknesses = implode(', ', $weaknesses);
        $row->training_required = ($overall < 60) ? 1 : 0;
        $row->status = 1;
        $row->save();

        return $row;
    }

    public static function attendanceAlerts($tenantId, $branchId, $checkIn, $checkOut, $attDate = null)
    {
        $settings = EmployeeAttendanceSetting::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 1)
            ->first();
        if (!$settings) {
            $settings = EmployeeAttendanceSetting::where('tenant_id', $tenantId)
                ->where('branch_id', 0)
                ->where('status', 1)
                ->first();
        }
        if (!$settings) {
            return array('is_late' => false, 'is_early' => false, 'late_minutes' => 0, 'early_minutes' => 0, 'alert_message' => '');
        }

        $late = false;
        $early = false;
        $lateMin = 0;
        $earlyMin = 0;
        $msgs = array();

        if ($checkIn) {
            $shiftStart = Carbon::parse(($attDate ?: date('Y-m-d')) . ' ' . $settings->shift_start_time);
            $actualIn = Carbon::parse(($attDate ?: date('Y-m-d')) . ' ' . $checkIn);
            $grace = (int) $settings->late_grace_minutes;
            if ($actualIn->gt($shiftStart->copy()->addMinutes($grace))) {
                $late = true;
                $lateMin = $shiftStart->diffInMinutes($actualIn);
                $msgs[] = 'Late check-in by ' . $lateMin . ' min';
            }
        }
        if ($checkOut) {
            $shiftEnd = Carbon::parse(($attDate ?: date('Y-m-d')) . ' ' . $settings->shift_end_time);
            $actualOut = Carbon::parse(($attDate ?: date('Y-m-d')) . ' ' . $checkOut);
            $grace = (int) $settings->early_leave_grace_minutes;
            if ($actualOut->lt($shiftEnd->copy()->subMinutes($grace))) {
                $early = true;
                $earlyMin = $actualOut->diffInMinutes($shiftEnd);
                $msgs[] = 'Early check-out by ' . $earlyMin . ' min';
            }
        }

        return array(
            'is_late' => $late,
            'is_early' => $early,
            'late_minutes' => $lateMin,
            'early_minutes' => $earlyMin,
            'shift_start' => $settings->shift_start_time,
            'shift_end' => $settings->shift_end_time,
            'alert_message' => implode('; ', $msgs),
        );
    }

    public static function refreshAchievements($tenantId, $branchId, $employeeId, $periodMonth = null)
    {
        $month = $periodMonth ?: Carbon::today()->format('Y-m');
        $perf = self::computePerformance($tenantId, $branchId, $employeeId, $month);
        $badges = EmployeeBadge::where('tenant_id', $tenantId)->where('status', 1)->get();
        $awarded = array();

        foreach ($badges as $badge) {
            $eligible = false;
            $reason = '';
            if ($badge->criteria_type === 'collection_percent' && $perf->collection_score >= $badge->criteria_value) {
                $eligible = true;
                $reason = 'Collection score ' . $perf->collection_score . '%';
            } elseif ($badge->criteria_type === 'overall_score' && $perf->overall_score >= $badge->criteria_value) {
                $eligible = true;
                $reason = 'Overall score ' . $perf->overall_score;
            } elseif ($badge->criteria_type === 'task_percent' && $perf->activity_score >= $badge->criteria_value) {
                $eligible = true;
                $reason = 'Task completion ' . $perf->activity_score . '%';
            }
            if (!$eligible) continue;

            $exists = EmployeeBadgeAward::where('employee_id', $employeeId)
                ->where('badge_id', $badge->id)
                ->where('period_month', $month)
                ->first();
            if (!$exists) {
                EmployeeBadgeAward::create(array(
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'employee_id' => $employeeId,
                    'badge_id' => $badge->id,
                    'awarded_date' => Carbon::today()->toDateString(),
                    'period_month' => $month,
                    'reason' => $reason,
                    'status' => 1,
                ));
                $awarded[] = $badge->badge_name;
            }
        }

        return $awarded;
    }

    public static function employeePhotoUrl($emp)
    {
        if (!$emp || empty($emp->photo)) {
            return '';
        }
        return rtrim((string) (env('APP_URL') ?: ''), '/')
            . '/storage/app/uploads/employee_photos/' . ltrim($emp->photo, '/');
    }

    public static function leaderboard($tenantId, $branchId, $periodMonth = null, $limit = 20)
    {
        $month = $periodMonth ?: Carbon::today()->format('Y-m');
        $query = EmployeePerformanceScore::with('employee_det')
            ->where('tenant_id', $tenantId)
            ->where('period_month', $month)
            ->orderBy('overall_score', 'desc')
            ->limit($limit);
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        $rows = $query->get();
        $branchNames = array();
        $branchIds = $rows->pluck('branch_id')->filter()->unique()->values()->all();
        if (!empty($branchIds) && Schema::hasTable('branches')) {
            $branchNames = DB::table('branches')->whereIn('id', $branchIds)->pluck('branch_name', 'id')->all();
        }
        $list = array();
        $rank = 1;
        foreach ($rows as $r) {
            $emp = $r->employee_det;
            $name = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '';
            $photo = self::employeePhotoUrl($emp);
            $list[] = array(
                'rank' => $rank++,
                'employee_id' => (int) $r->employee_id,
                'employee_name' => $name,
                'overall_score' => (float) $r->overall_score,
                'performance_rating' => $r->performance_rating,
                'collection_score' => (float) $r->collection_score,
                'sales_score' => (float) $r->sales_score,
                'attendance_score' => (float) $r->attendance_score,
                'activity_score' => (float) $r->activity_score,
                'period_month' => $r->period_month,
                'profile_pic' => $photo,
                'photo_url' => $photo,
                'branch_id' => (int) $r->branch_id,
                'branch_name' => isset($branchNames[$r->branch_id]) ? $branchNames[$r->branch_id] : '',
            );
        }
        return $list;
    }

    public static function addCollectionTotals($into, $tenantId, $from, $to, $groupCol)
    {
        $add = function ($rows) use (&$into) {
            foreach ($rows as $id => $total) {
                $key = (int) $id;
                if ($key <= 0) {
                    continue;
                }
                $into[$key] = (isset($into[$key]) ? $into[$key] : 0) + (float) $total;
            }
        };
        $receipts = Receipt::where('tenant_id', $tenantId)
            ->whereBetween('receipt_date', array($from, $to))
            ->selectRaw($groupCol . ', SUM(received_amount + penalty) as total')
            ->groupBy($groupCol)
            ->pluck('total', $groupCol);
        $add($receipts);
        $advance = AdvanceReceipt::where('tenant_id', $tenantId)
            ->whereBetween('receipt_date', array($from, $to))
            ->selectRaw($groupCol . ', SUM(receipt_amount) as total')
            ->groupBy($groupCol)
            ->pluck('total', $groupCol);
        $add($advance);
        $other = OtherCharge::where('tenant_id', $tenantId)
            ->whereBetween('received_date', array($from, $to))
            ->selectRaw($groupCol . ', SUM(amount) as total')
            ->groupBy($groupCol)
            ->pluck('total', $groupCol);
        $add($other);
        $before = BeforeEnrolReceipt::where('tenant_id', $tenantId)
            ->whereBetween('received_date', array($from, $to))
            ->selectRaw($groupCol . ', SUM(amount) as total')
            ->groupBy($groupCol)
            ->pluck('total', $groupCol);
        $add($before);
        return $into;
    }

    public static function topEmployeesByCollection($tenantId, $from, $to, $limit = 3)
    {
        $totals = self::addCollectionTotals(array(), $tenantId, $from, $to, 'employee_id');
        arsort($totals);
        $topIds = array_slice(array_keys($totals), 0, $limit);
        if (empty($topIds)) {
            return array();
        }
        $employees = Employee::whereIn('id', $topIds)->get()->keyBy('id');
        $branchIds = $employees->pluck('branch_id')->filter()->unique()->values()->all();
        $branchNames = array();
        if (!empty($branchIds) && Schema::hasTable('branches')) {
            $branchNames = DB::table('branches')->whereIn('id', $branchIds)->pluck('branch_name', 'id')->all();
        }
        $list = array();
        $rank = 1;
        foreach ($topIds as $eid) {
            $emp = $employees->get($eid);
            $name = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '';
            if ($name === '') {
                continue;
            }
            $photo = self::employeePhotoUrl($emp);
            $bid = $emp ? (int) $emp->branch_id : 0;
            $list[] = array(
                'rank' => $rank++,
                'employee_id' => (int) $eid,
                'employee_name' => $name,
                'overall_score' => null,
                'performance_rating' => '',
                'collection_amount' => round($totals[$eid], 2),
                'profile_pic' => $photo,
                'photo_url' => $photo,
                'branch_id' => $bid,
                'branch_name' => isset($branchNames[$bid]) ? $branchNames[$bid] : '',
            );
        }
        return $list;
    }

    public static function topBranchesByCollection($tenantId, $from, $to, $limit = 3)
    {
        $totals = self::addCollectionTotals(array(), $tenantId, $from, $to, 'branch_id');
        arsort($totals);
        $topIds = array_slice(array_keys($totals), 0, $limit);
        if (empty($topIds)) {
            return array();
        }
        $branchNames = array();
        if (Schema::hasTable('branches')) {
            $branchNames = DB::table('branches')->whereIn('id', $topIds)->pluck('branch_name', 'id')->all();
        }
        $list = array();
        $rank = 1;
        foreach ($topIds as $bid) {
            $name = isset($branchNames[$bid]) ? $branchNames[$bid] : '';
            if ($name === '') {
                continue;
            }
            $list[] = array(
                'rank' => $rank++,
                'branch_id' => (int) $bid,
                'branch_name' => $name,
                'collection_amount' => round($totals[$bid], 2),
            );
        }
        return $list;
    }

    public static function collectionAmountByKind($tenantId, $employeeId, $from, $to, $kind)
    {
        $receiptQuery = Receipt::where('tenant_id', $tenantId)
            ->where('employee_id', $employeeId)
            ->whereBetween('receipt_date', array($from, $to));

        $newEnrolSub = function ($sub) use ($from, $to) {
            $sub->select('id')->from('chit_details')
                ->whereBetween(DB::raw('DATE(created_at)'), array($from, $to));
        };

        if ($kind === 'new') {
            $receiptQuery->whereIn('enrollment_id', $newEnrolSub);
            return (float) ($receiptQuery->selectRaw('SUM(received_amount + penalty) as total')->value('total') ?? 0);
        }

        $receiptQuery->where(function ($q) use ($newEnrolSub) {
            $q->whereNull('enrollment_id')
                ->orWhere('enrollment_id', 0)
                ->orWhereNotIn('enrollment_id', $newEnrolSub);
        });
        $r = (float) ($receiptQuery->selectRaw('SUM(received_amount + penalty) as total')->value('total') ?? 0);
        $scope = function ($q) use ($tenantId, $employeeId) {
            $q->where('tenant_id', $tenantId)->where('employee_id', $employeeId);
        };
        $a = AdvanceReceipt::whereBetween('receipt_date', array($from, $to))->where($scope)->sum('receipt_amount');
        $o = OtherCharge::whereBetween('received_date', array($from, $to))->where($scope)->sum('amount');
        $b = BeforeEnrolReceipt::whereBetween('received_date', array($from, $to))->where($scope)->sum('amount');
        return (float) ($r + $a + $o + $b);
    }

    public static function emptyTargetBucket($label, $type)
    {
        return array(
            'label' => $label,
            'target_type' => $type,
            'target_amount' => 0,
            'achieved_amount' => 0,
            'completion_percent' => 0,
        );
    }

    public static function packTarget($label, $type, $amount, $achieved)
    {
        $amount = (float) $amount;
        $achieved = (float) $achieved;
        return array(
            'label' => $label,
            'target_type' => $type,
            'target_amount' => round($amount, 2),
            'achieved_amount' => round($achieved, 2),
            'completion_percent' => $amount > 0 ? min(100, round(($achieved / $amount) * 100)) : 0,
        );
    }

    public static function targetBreakdown($tenantId, $employeeId, $onDate = null)
    {
        $date = $onDate ?: Carbon::today()->toDateString();
        $targets = self::syncTargetProgress($tenantId, $employeeId, $date);
        $sales = self::emptyTargetBucket('Sales', 'sales');
        $newCust = self::emptyTargetBucket('New customer', 'new_client_collection');
        $oldCust = self::emptyTargetBucket('Old customer', 'existing_client_collection');

        foreach ($targets as $tg) {
            $type = strtolower((string) ($tg->target_type ?: 'existing_client_collection'));
            $row = self::packTarget($tg->target_label ?: $type, $type, $tg->target_amount, $tg->achieved_amount);
            if (in_array($type, array('sales', 'new_business'), true)) {
                $sales = self::packTarget('Sales', 'sales', $sales['target_amount'] + $row['target_amount'], $sales['achieved_amount'] + $row['achieved_amount']);
            } elseif ($type === 'new_client_collection') {
                $newCust = self::packTarget('New customer', 'new_client_collection', $newCust['target_amount'] + $row['target_amount'], $newCust['achieved_amount'] + $row['achieved_amount']);
            } elseif (in_array($type, array('existing_client_collection', 'collection', ''), true)) {
                $oldCust = self::packTarget('Old customer', 'existing_client_collection', $oldCust['target_amount'] + $row['target_amount'], $oldCust['achieved_amount'] + $row['achieved_amount']);
            }
        }

        if ($newCust['target_amount'] <= 0 && $oldCust['target_amount'] <= 0) {
            $monthStart = Carbon::parse($date)->startOfMonth()->toDateString();
            $emp = Employee::find($employeeId);
            $monthly = $emp ? (float) ($emp->target ?? 0) : 0;
            $oldCust = self::packTarget(
                'Old customer',
                'existing_client_collection',
                $monthly,
                self::collectionAmountByKind($tenantId, $employeeId, $monthStart, $date, 'old')
            );
            $newCust = self::packTarget(
                'New customer',
                'new_client_collection',
                0,
                self::collectionAmountByKind($tenantId, $employeeId, $monthStart, $date, 'new')
            );
            if ($sales['target_amount'] <= 0) {
                $sales = self::packTarget(
                    'Sales',
                    'sales',
                    0,
                    self::salesCount($tenantId, $employeeId, $monthStart, $date)
                );
            }
        }

        $collection = self::packTarget(
            'Collection',
            'collection',
            $newCust['target_amount'] + $oldCust['target_amount'],
            $newCust['achieved_amount'] + $oldCust['achieved_amount']
        );

        return array(
            'sales' => $sales,
            'collection' => $collection,
            'new_customer' => $newCust,
            'old_customer' => $oldCust,
        );
    }

    public static function ensureTopEmployees($list, $tenantId, $limit = 3)
    {
        $list = array_values(is_array($list) ? $list : array());
        $used = array();
        foreach ($list as $row) {
            $used[] = (int) ($row['employee_id'] ?? 0);
        }
        if (count($list) >= $limit) {
            return array_slice($list, 0, $limit);
        }
        $q = Employee::where('tenant_id', $tenantId);
        if (Schema::hasColumn('employees', 'status')) {
            $q->where('status', 1);
        }
        if (!empty($used)) {
            $q->whereNotIn('id', $used);
        }
        $emps = $q->orderBy('id', 'desc')->limit($limit - count($list))->get();
        $rank = count($list) + 1;
        foreach ($emps as $emp) {
            $name = trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : ''));
            if ($name === '') {
                $name = 'Employee ' . $emp->id;
            }
            $photo = self::employeePhotoUrl($emp);
            $list[] = array(
                'rank' => $rank++,
                'employee_id' => (int) $emp->id,
                'employee_name' => $name,
                'overall_score' => null,
                'collection_amount' => 0,
                'profile_pic' => $photo,
                'photo_url' => $photo,
                'branch_id' => (int) $emp->branch_id,
                'branch_name' => optional($emp->branch_det)->branch_name,
            );
        }
        return $list;
    }

    public static function ensureTopBranches($list, $tenantId, $limit = 3)
    {
        $list = array_values(is_array($list) ? $list : array());
        $used = array();
        foreach ($list as $row) {
            $used[] = (int) ($row['branch_id'] ?? 0);
        }
        if (count($list) >= $limit) {
            return array_slice($list, 0, $limit);
        }
        $q = DB::table('branches')->where('tenant_id', $tenantId);
        if (Schema::hasColumn('branches', 'status')) {
            $q->where('status', 1);
        }
        if (!empty($used)) {
            $q->whereNotIn('id', $used);
        }
        $rows = $q->orderBy('id', 'desc')->limit($limit - count($list))->get();
        $rank = count($list) + 1;
        foreach ($rows as $br) {
            $name = isset($br->branch_name) ? $br->branch_name : '';
            if ($name === '') {
                continue;
            }
            $list[] = array(
                'rank' => $rank++,
                'branch_id' => (int) $br->id,
                'branch_name' => $name,
                'collection_amount' => 0,
            );
        }
        return $list;
    }

    public static function scoresBoard($tenantId, $branchId = 0, $periodMonth = null, $limit = 80)
    {
        $month = $periodMonth ?: Carbon::today()->format('Y-m');
        $q = Employee::where('tenant_id', $tenantId);
        if (Schema::hasColumn('employees', 'status')) {
            $q->where('status', 1);
        }
        if ($branchId) {
            $q->where('branch_id', $branchId);
        }
        $emps = $q->orderBy('first_name')->limit($limit)->get();
        $rows = array();
        foreach ($emps as $emp) {
            $perf = self::computePerformance($tenantId, (int) $emp->branch_id, (int) $emp->id, $month);
            $name = trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : ''));
            $photo = self::employeePhotoUrl($emp);
            $rows[] = array(
                'employee_id' => (int) $emp->id,
                'employee_name' => $name ?: ('Employee ' . $emp->id),
                'profile_pic' => $photo,
                'photo_url' => $photo,
                'branch_id' => (int) $emp->branch_id,
                'branch_name' => optional($emp->branch_det)->branch_name,
                'overall_score' => (float) $perf->overall_score,
                'collection_score' => (float) $perf->collection_score,
                'sales_score' => (float) $perf->sales_score,
                'attendance_score' => (float) $perf->attendance_score,
                'activity_score' => (float) $perf->activity_score,
                'performance_rating' => $perf->performance_rating,
            );
        }
        usort($rows, function ($a, $b) {
            return ($b['overall_score'] <=> $a['overall_score']);
        });
        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank'] = $rank++;
        }
        unset($row);
        return $rows;
    }
}
