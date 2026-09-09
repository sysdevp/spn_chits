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
            if ($type === 'collection' || $type === 'existing_client_collection' || $type === 'new_client_collection') {
                $achieved = self::collectionAmount($tenantId, $employeeId, $tg->period_start, min($date, $tg->period_end));
            } elseif ($type === 'sales' || $type === 'new_business') {
                $achieved = self::salesCount($tenantId, $employeeId, $tg->period_start, min($date, $tg->period_end));
            } elseif ($type === 'activity') {
                $achieved = self::taskCompletionPercent($tenantId, $employeeId, $tg->period_start, min($date, $tg->period_end));
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
        $list = array();
        $rank = 1;
        foreach ($rows as $r) {
            $emp = $r->employee_det;
            $name = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : '';
            $list[] = array(
                'rank' => $rank++,
                'employee_id' => (int) $r->employee_id,
                'employee_name' => $name,
                'overall_score' => (float) $r->overall_score,
                'performance_rating' => $r->performance_rating,
                'collection_score' => (float) $r->collection_score,
                'period_month' => $r->period_month,
            );
        }
        return $list;
    }
}
