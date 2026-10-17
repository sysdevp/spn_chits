<?php

namespace App\Http\Controllers;

use App\Models\AuctionOnHoliday;
use App\Models\Branch;
use App\Models\ChitDetail;
use App\Models\CollectionArea;
use App\Models\CollectionAreaMapping;
use App\Models\CommitmentAuction;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Receipt;
use App\Services\TamilNaduHolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

/**
 * AI-style collection prediction for the current month (data up to today only).
 */
class CollectionPredictionReportController extends Controller
{
    /** Tamil Nadu default (Chennai) when area coordinates are missing. */
    protected const DEFAULT_LAT = 13.0827;

    protected const DEFAULT_LNG = 80.2707;

    public function collection_prediction_report(Request $request)
    {
        try {
            if (empty($request->tenant_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'tenant_id required'], 401);
            }

            $tenantId = (int) $request->tenant_id;
            $branchIds = $this->resolveBranchIds($request, $tenantId);
            if (count($branchIds) === 0) {
                return response()->json([
                    'status' => 'Success',
                    'msg' => '',
                    'data' => $this->emptyPayload(),
                ], 200);
            }

            $asOf = Carbon::today();
            $monthStart = $asOf->copy()->startOfMonth();
            $monthEnd = $asOf->copy()->endOfMonth();
            $daysElapsed = max(1, $monthStart->diffInDays($asOf) + 1);
            $daysRemaining = max(0, $asOf->diffInDays($monthEnd));
            $calendarDaysInMonth = $monthStart->daysInMonth;

            $govtHolidays = AuctionOnHoliday::where('tenant_id', $tenantId)
                ->whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereBetween('holiday_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get(['holiday_date', 'event_name', 'branch_id']);

            $holidayDates = $govtHolidays->pluck('holiday_date')->map(function ($d) {
                return Carbon::parse($d)->toDateString();
            })->unique()->values()->all();

            $tnHolidayService = new TamilNaduHolidayService();
            $externalTnHolidays = $tnHolidayService->holidaysBetween($monthStart, $monthEnd);
            foreach ($externalTnHolidays as $ext) {
                if (!in_array($ext['date'], $holidayDates, true)) {
                    $holidayDates[] = $ext['date'];
                }
            }
            sort($holidayDates);

            $workingDaysRemaining = $this->countWorkingDays($asOf->copy()->addDay(), $monthEnd, $holidayDates);
            $weekendDaysRemaining = $this->countWeekendDays($asOf->copy()->addDay(), $monthEnd);

            $areaAgentMap = $this->buildAreaAgentMap($tenantId, $branchIds);

            $enrollmentQuery = ChitDetail::with(['customer_det', 'branch_det', 'collection_area_det', 'collection_agent_det'])
                ->where('tenant_id', $tenantId)
                ->where('chit_type', 3)
                ->whereNull('deleted_at')
                ->whereIn('branch_id', $branchIds)
                ->where('customer_id', '!=', 1);

            if (!empty($request->branch_id) && (int) $request->branch_id > 0) {
                $enrollmentQuery->where('branch_id', (int) $request->branch_id);
            }
            if (!empty($request->customer_id) && (int) $request->customer_id > 0) {
                $enrollmentQuery->where('customer_id', (int) $request->customer_id);
            }
            if (!empty($request->area_id) && (int) $request->area_id > 0) {
                $enrollmentQuery->where('collection_area_id', (int) $request->area_id);
            }
            if (!empty($request->employee_id) && (int) $request->employee_id > 0) {
                $enrollmentQuery->where(function ($q) use ($request) {
                    $q->where('collection_agent_id', (int) $request->employee_id)
                        ->orWhereIn('collection_area_id', $this->areaIdsForEmployee((int) $request->employee_id, $areaAgentMap));
                });
            }

            $enrollments = $enrollmentQuery->get();
            $areaIdsUsed = $enrollments->pluck('collection_area_id')->map(function ($id) {
                return (int) $id;
            })->filter(function ($id) {
                return $id > 0;
            })->unique()->values()->all();

            $weather = $this->resolveWeatherImpact($tenantId, $branchIds, $request, $areaIdsUsed, $asOf, $monthEnd);

            $enrollmentIds = $enrollments->pluck('id')->all();

            $paidByEnrollment = $this->paidTotalsByEnrollment($enrollmentIds);
            $dueByEnrollment = $this->dueTotalsByEnrollment($enrollmentIds);
            $mtdByEnrollment = $this->mtdCollectionsByEnrollment($enrollmentIds, $branchIds, $monthStart, $asOf);
            $paymentDelayByEnrollment = $this->avgPaymentDelayDays($enrollmentIds);
            $typicalPayDayByEnrollment = $this->typicalPaymentDayByEnrollment($enrollmentIds);
            $agentStats = $this->agentCollectionStats($branchIds, $monthStart->copy()->subMonths(3), $asOf);
            $agentLeaveFactors = $this->agentLeaveFactors($branchIds, $monthStart, $asOf, $monthEnd, $daysElapsed);

            $activeAgentIds = [];
            $byCustomer = [];
            $byArea = [];
            $byAgent = [];
            $byBranch = [];

            $collectedMtd = 0.0;
            $pendingTotal = 0.0;
            $predictedRemaining = 0.0;

            foreach ($enrollments as $cd) {
                $eid = (int) $cd->id;
                $totalDue = isset($dueByEnrollment[$eid]) ? (float) $dueByEnrollment[$eid] : 0.0;
                $totalPaid = isset($paidByEnrollment[$eid]) ? (float) $paidByEnrollment[$eid] : 0.0;
                $pending = max(0, round($totalDue - $totalPaid, 2));
                $mtd = isset($mtdByEnrollment[$eid]) ? (float) $mtdByEnrollment[$eid] : 0.0;

                $areaId = (int) ($cd->collection_area_id ?? 0);
                $agentId = (int) ($cd->collection_agent_id ?? 0);
                if ($agentId <= 0 && $areaId > 0 && isset($areaAgentMap[$areaId])) {
                    $agentId = (int) $areaAgentMap[$areaId];
                }

                $avgDelay = isset($paymentDelayByEnrollment[$eid]) ? (float) $paymentDelayByEnrollment[$eid] : 15.0;
                $agentDaily = isset($agentStats[$agentId]) ? (float) $agentStats[$agentId]['avg_daily'] : 0.0;

                if ($agentId > 0) {
                    $activeAgentIds[$agentId] = true;
                }

                $typicalPayDay = isset($typicalPayDayByEnrollment[$eid]) ? (int) $typicalPayDayByEnrollment[$eid] : null;
                $leaveFactor = $this->resolveAgentLeaveFactor($agentId, $agentLeaveFactors);

                $factors = $this->buildFactorScores(
                    $cd,
                    $avgDelay,
                    $agentDaily,
                    $holidayDates,
                    $weekendDaysRemaining,
                    $daysRemaining,
                    $workingDaysRemaining,
                    $weather['multiplier'],
                    $leaveFactor
                );

                $collectProbability = min(1.0, max(0.05, $factors['combined_score']));
                $predicted = round($pending * $collectProbability, 2);

                $collectedMtd += $mtd;
                $pendingTotal += $pending;
                $predictedRemaining += $predicted;

                $customerName = $cd->customer_det ? trim(($cd->customer_det->name ?? '') . ' ' . ($cd->customer_det->last_name ?? '')) : '-';
                $arNo = $cd->ar_no ?: ($cd->ticket_no ?: '-');
                $branchName = $cd->branch_det ? $cd->branch_det->branch_name : '-';
                $areaName = $cd->collection_area_det ? $cd->collection_area_det->collection_area : '-';
                $agentName = $agentId > 0 ? ($this->employeeName($agentId) ?: 'Agent #' . $agentId) : '-';

                $paymentTypeLabel = $this->paymentTypeLabel((int) $cd->payment_type);

                $customerSuggestions = $this->buildCustomerSuggestions(
                    $customerName,
                    $avgDelay,
                    $typicalPayDay,
                    (int) $cd->payment_type,
                    $paymentTypeLabel,
                    $mtd,
                    $pending,
                    $predicted,
                    $collectProbability,
                    $agentName,
                    $leaveFactor,
                    $asOf,
                    $daysRemaining,
                    isset($agentLeaveFactors[$agentId]) ? $agentLeaveFactors[$agentId] : null,
                    $this->areaWeatherTips($weather, $areaId, $areaName, $asOf)
                );

                $row = [
                    'enrollment_id' => $eid,
                    'customer_id' => (int) $cd->customer_id,
                    'customer_name' => $customerName,
                    'ar_no' => $arNo,
                    'branch_id' => (int) $cd->branch_id,
                    'branch_name' => $branchName,
                    'area_id' => $areaId,
                    'area_name' => $areaName,
                    'agent_id' => $agentId,
                    'agent_name' => $agentName,
                    'payment_type' => (int) $cd->payment_type,
                    'payment_type_label' => $paymentTypeLabel,
                    'avg_payment_delay_days' => round($avgDelay, 1),
                    'collected_mtd' => round($mtd, 2),
                    'pending_amount' => round($pending, 2),
                    'predicted_remaining' => $predicted,
                    'predicted_month_total' => round($mtd + $predicted, 2),
                    'collection_probability_pct' => round($collectProbability * 100, 1),
                    'typical_payment_day' => $typicalPayDay,
                    'suggestions' => $customerSuggestions,
                    'factor_scores' => $factors['breakdown'],
                ];

                $byCustomer[] = $row;
                $this->aggregateBucket($byArea, 'area_id', 'area_name', $areaId, $areaName, $mtd, $pending, $predicted);
                $this->aggregateBucket($byAgent, 'agent_id', 'agent_name', $agentId, $agentName, $mtd, $pending, $predicted);
                $this->aggregateBucket($byBranch, 'branch_id', 'branch_name', (int) $cd->branch_id, $branchName, $mtd, $pending, $predicted);
            }

            usort($byCustomer, function ($a, $b) {
                return $b['predicted_remaining'] <=> $a['predicted_remaining'];
            });

            $factorImpact = $this->summarizeFactorImpact($weather, $holidayDates, $weekendDaysRemaining, $workingDaysRemaining, $daysRemaining, $agentLeaveFactors, $externalTnHolidays);

            $predictedMonthTotal = round($collectedMtd + $predictedRemaining, 2);
            $confidence = $this->estimateConfidence($daysElapsed, $calendarDaysInMonth, count($enrollments), $weather['has_coordinates']);

            $agentAttendance = $this->buildAgentAttendanceSummary($activeAgentIds, $agentLeaveFactors, $daysElapsed);
            $summarySuggestions = $this->buildSummarySuggestions(
                $agentAttendance,
                $collectedMtd,
                $pendingTotal,
                $predictedRemaining,
                $predictedMonthTotal,
                $confidence,
                $holidayDates,
                $workingDaysRemaining,
                $weekendDaysRemaining,
                $weather,
                $daysElapsed,
                $daysRemaining,
                count($byCustomer)
            );

            foreach ($agentLeaveFactors as $aid => &$leaveRow) {
                $leaveRow['suggestions'] = $this->buildAgentLeaveSuggestions($leaveRow, $agentAttendance);
            }
            unset($leaveRow);

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => [
                    'period' => [
                        'month' => $asOf->format('Y-m'),
                        'month_label' => $asOf->format('F Y'),
                        'from_date' => $monthStart->toDateString(),
                        'to_date' => $asOf->toDateString(),
                        'month_end' => $monthEnd->toDateString(),
                        'days_elapsed' => $daysElapsed,
                        'days_remaining' => $daysRemaining,
                        'working_days_remaining' => $workingDaysRemaining,
                        'weekend_days_remaining' => $weekendDaysRemaining,
                        'prediction_mode' => $daysElapsed >= 20 ? 'remainder_forecast' : 'full_month_blend',
                    ],
                    'summary' => [
                        'collected_mtd' => round($collectedMtd, 2),
                        'pending_total' => round($pendingTotal, 2),
                        'predicted_remaining' => round($predictedRemaining, 2),
                        'predicted_month_total' => $predictedMonthTotal,
                        'confidence_pct' => $confidence,
                        'enrollment_count' => count($byCustomer),
                    ],
                    'suggestions' => $summarySuggestions,
                    'agent_attendance' => $agentAttendance,
                    'factor_impact' => $factorImpact,
                    'weather' => $weather,
                    'govt_holidays' => $govtHolidays->map(function ($h) {
                        return [
                            'date' => Carbon::parse($h->holiday_date)->toDateString(),
                            'event_name' => $h->event_name,
                            'branch_id' => (int) $h->branch_id,
                            'source' => 'Auction on Holidays',
                        ];
                    })->values(),
                    'external_tn_holidays' => array_map(function ($h) {
                        return [
                            'date' => $h['date'],
                            'event_name' => $h['name'],
                            'source' => $h['source'],
                        ];
                    }, $externalTnHolidays),
                    'agent_leave_summary' => array_values(array_map(function ($agentId, $stats) {
                        return array_merge(['agent_id' => (int) $agentId], $stats);
                    }, array_keys($agentLeaveFactors), $agentLeaveFactors)),
                    'analysed_by' => 'Emglitz Intelligence AI',
                    'by_customer' => $byCustomer,
                    'by_area' => array_values($this->finalizeBuckets($byArea)),
                    'by_agent' => array_values($this->finalizeBuckets($byAgent)),
                    'by_branch' => array_values($this->finalizeBuckets($byBranch)),
                    'notes' => [
                        'data_upto' => 'Commitment chit collections and receipts include dates up to today only.',
                        'weather' => 'Uses Open-Meteo (free, no API key). Tamil Nadu default coordinates are used when an area has no map location.',
                        'unknown_factors' => 'Unexpected disruptions use a 6% conservative buffer (unreachable areas, office closure, accidents).',
                        'ai_method' => 'Analysed by Emglitz Intelligence AI using commitment chit payment history, agent performance, holidays, weekends, salary cycle, weather, and an uncertainty buffer.',
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to build collection prediction: ' . $e->getMessage(),
            ], 401);
        }
    }

    protected function emptyPayload(): array
    {
        return [
            'period' => [],
            'summary' => [],
            'suggestions' => [],
            'agent_attendance' => [],
            'factor_impact' => [],
            'weather' => [],
            'govt_holidays' => [],
            'by_customer' => [],
            'by_area' => [],
            'by_agent' => [],
            'by_branch' => [],
            'notes' => [],
        ];
    }

    protected function resolveBranchIds(Request $request, int $tenantId): array
    {
        if (!empty($request->branch_id) && (int) $request->branch_id > 0) {
            return [(int) $request->branch_id];
        }

        return Branch::where('tenant_id', $tenantId)->where('status', 1)->pluck('id')->toArray();
    }

    protected function buildAreaAgentMap(int $tenantId, array $branchIds): array
    {
        $map = [];
        $rows = CollectionAreaMapping::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->get();

        foreach ($rows as $row) {
            $areaIds = @unserialize($row->collection_area_id);
            if (!is_array($areaIds)) {
                $areaIds = [(int) $row->collection_area_id];
            }
            foreach ($areaIds as $aid) {
                $aid = (int) $aid;
                if ($aid > 0) {
                    $map[$aid] = (int) $row->employee_id;
                }
            }
        }

        return $map;
    }

    protected function areaIdsForEmployee(int $employeeId, array $areaAgentMap): array
    {
        $ids = [];
        foreach ($areaAgentMap as $areaId => $empId) {
            if ((int) $empId === $employeeId) {
                $ids[] = (int) $areaId;
            }
        }

        return $ids;
    }

    protected function paidTotalsByEnrollment(array $enrollmentIds): array
    {
        if (count($enrollmentIds) === 0) {
            return [];
        }

        return Receipt::whereIn('enrollment_id', $enrollmentIds)
            ->where('status', 1)
            ->selectRaw('enrollment_id, COALESCE(SUM(received_amount),0) + COALESCE(SUM(bonus),0) as total_paid')
            ->groupBy('enrollment_id')
            ->pluck('total_paid', 'enrollment_id')
            ->toArray();
    }

    protected function dueTotalsByEnrollment(array $enrollmentIds): array
    {
        if (count($enrollmentIds) === 0) {
            return [];
        }

        return CommitmentAuction::whereIn('enrollment_id', $enrollmentIds)
            ->selectRaw('enrollment_id, COALESCE(SUM(due_amount),0) as total_due')
            ->groupBy('enrollment_id')
            ->pluck('total_due', 'enrollment_id')
            ->toArray();
    }

    protected function mtdCollectionsByEnrollment(array $enrollmentIds, array $branchIds, Carbon $from, Carbon $to): array
    {
        if (count($enrollmentIds) === 0) {
            return [];
        }

        return Receipt::whereIn('enrollment_id', $enrollmentIds)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->whereBetween('receipt_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('enrollment_id, COALESCE(SUM(received_amount),0) + COALESCE(SUM(penalty),0) as mtd')
            ->groupBy('enrollment_id')
            ->pluck('mtd', 'enrollment_id')
            ->toArray();
    }

    protected function avgPaymentDelayDays(array $enrollmentIds): array
    {
        if (count($enrollmentIds) === 0) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($enrollmentIds), '?'));
        $rows = DB::select(
            "SELECT r.enrollment_id,
                AVG(GREATEST(0, DATEDIFF(r.receipt_date, ca.auction_date))) AS avg_delay
             FROM receipts r
             INNER JOIN commitment_auctions ca
                ON ca.enrollment_id = r.enrollment_id AND ca.installment_no = r.installment_no
                AND ca.deleted_at IS NULL
             WHERE r.status = 1 AND r.enrollment_id IN ($placeholders)
             GROUP BY r.enrollment_id",
            $enrollmentIds
        );

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->enrollment_id] = (float) $row->avg_delay;
        }

        return $out;
    }

    protected function typicalPaymentDayByEnrollment(array $enrollmentIds): array
    {
        if (count($enrollmentIds) === 0) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($enrollmentIds), '?'));
        $rows = DB::select(
            "SELECT enrollment_id, ROUND(AVG(DAY(receipt_date))) AS typical_day
             FROM receipts
             WHERE status = 1 AND enrollment_id IN ($placeholders)
             GROUP BY enrollment_id",
            $enrollmentIds
        );

        $out = [];
        foreach ($rows as $row) {
            $out[(int) $row->enrollment_id] = (int) $row->typical_day;
        }

        return $out;
    }

    protected function buildCustomerSuggestions(
        string $customerName,
        float $avgDelay,
        ?int $typicalPayDay,
        int $paymentType,
        string $paymentTypeLabel,
        float $mtd,
        float $pending,
        float $predicted,
        float $collectProbability,
        string $agentName,
        float $leaveFactor,
        Carbon $asOf,
        int $daysRemaining,
        ?array $agentLeave,
        array $weatherTips = []
    ): array {
        $tips = [];
        $todayDay = (int) $asOf->format('j');

        foreach ($weatherTips as $wt) {
            $tips[] = $wt;
        }

        if ($typicalPayDay !== null && $typicalPayDay > 0) {
            if ($typicalPayDay >= $todayDay) {
                $tips[] = $customerName . ' usually pays around day ' . $typicalPayDay . ' of the month — follow from today (' . $asOf->format('d M') . '); payment expected by day ' . $typicalPayDay . '.';
            } else {
                $tips[] = $customerName . ' typically pays around day ' . $typicalPayDay . ' of the month — that date has passed; follow up today for the pending balance.';
            }
        } elseif ($avgDelay <= 7) {
            $tips[] = $customerName . ' pays quickly (avg ' . round($avgDelay, 0) . ' day(s) after due date) — visit from today for best results.';
        } elseif ($avgDelay <= 15) {
            $tips[] = $customerName . ' pays within ~' . round($avgDelay, 0) . ' days on average — plan collection this week.';
        } elseif ($avgDelay <= 30) {
            $tips[] = $customerName . ' often delays ~' . round($avgDelay, 0) . ' days — start follow-up from today; do not wait until month-end.';
        } else {
            $tips[] = $customerName . ' is a slow payer (avg delay ' . round($avgDelay, 0) . ' days) — assign an experienced agent and call before field visit.';
        }

        if ($paymentType === 3) {
            $tips[] = 'Monthly salary customer — best collection window is 1st–7th or just after salary day; follow from today if still pending.';
        } elseif ($paymentType === 2) {
            $tips[] = 'Weekly payment type — short collection cycles; visit on the same weekday they usually pay.';
        } elseif ($paymentType === 1) {
            $tips[] = 'Daily payment type — small frequent visits work best; collect little by little from today.';
        }

        if ($mtd > 0 && $pending > 0) {
            $tips[] = 'Already collected ₹' . number_format($mtd, 0) . ' this month; ₹' . number_format($pending, 0) . ' still pending — predicted to recover ~₹' . number_format($predicted, 0) . ' (' . round($collectProbability * 100) . '% probability).';
        } elseif ($pending > 0 && $mtd <= 0) {
            $tips[] = 'No payment yet this month — ₹' . number_format($pending, 0) . ' outstanding; priority follow-up from today.';
        }

        if ($leaveFactor < 1.0 && $agentName !== '-') {
            $leaveDays = $agentLeave ? $agentLeave['leave_days_mtd'] : 0;
            $tips[] = 'Agent ' . $agentName . ' has ' . $leaveDays . ' leave day(s) this month — reassign area or cover route on leave days.';
        } elseif ($agentName !== '-') {
            $tips[] = 'Agent ' . $agentName . ' has no leave recorded — assign for collection from today.';
        }

        if ($daysRemaining <= 5 && $pending > 0) {
            $tips[] = 'Only ' . $daysRemaining . ' day(s) left in the month — urgent collection needed.';
        }

        return $tips;
    }

    protected function buildAgentAttendanceSummary(array $activeAgentIds, array $agentLeaveFactors, int $daysElapsed): array
    {
        $totalAgents = count($activeAgentIds);
        $agentsOnLeave = 0;
        foreach (array_keys($activeAgentIds) as $aid) {
            if (isset($agentLeaveFactors[$aid]) && $agentLeaveFactors[$aid]['leave_days_mtd'] > 0) {
                $agentsOnLeave++;
            }
        }

        $agentsFullAttendance = $totalAgents - $agentsOnLeave;
        $attendancePct = $totalAgents > 0 ? round(($agentsFullAttendance / $totalAgents) * 100, 1) : 100.0;
        $capacityPct = $totalAgents > 0 ? round((($agentsFullAttendance + ($agentsOnLeave * 0.5)) / $totalAgents) * 100, 1) : 100.0;

        $message = '';
        if ($totalAgents === 0) {
            $message = 'No collection agents mapped to the selected filters.';
        } elseif ($agentsOnLeave === 0) {
            $message = 'All ' . $totalAgents . ' collection agent(s) came without leave this month — field capacity is 100%.';
        } elseif ($agentsOnLeave === 1) {
            $message = '1 of ' . $totalAgents . ' agent(s) took leave — estimated collection capacity ~' . $capacityPct . '%.';
        } else {
            $message = $agentsOnLeave . ' of ' . $totalAgents . ' agent(s) took leave — estimated collection capacity ~' . $capacityPct . '%; redistribute areas.';
        }

        return [
            'total_agents' => $totalAgents,
            'agents_full_attendance' => $agentsFullAttendance,
            'agents_on_leave' => $agentsOnLeave,
            'attendance_pct' => $attendancePct,
            'estimated_capacity_pct' => $capacityPct,
            'days_elapsed' => $daysElapsed,
            'message' => $message,
        ];
    }

    protected function buildAgentLeaveSuggestions(array $leaveRow, array $agentAttendance): array
    {
        $tips = [];
        $name = $leaveRow['agent_name'] ?? 'Agent';
        $leaveDays = (float) ($leaveRow['leave_days_mtd'] ?? 0);
        $future = (float) ($leaveRow['scheduled_future_leave'] ?? 0);
        $factor = (float) ($leaveRow['factor'] ?? 1);

        if ($leaveDays <= 0 && $future <= 0) {
            $tips[] = $name . ' has full attendance this month — assign more pending customers; collection factor 100%.';
            return $tips;
        }

        if ($leaveDays > 0) {
            $tips[] = $name . ' took ' . $leaveDays . ' leave day(s) so far — collection efficiency reduced to ~' . round($factor * 100) . '%.';
        }
        if ($future > 0) {
            $tips[] = $name . ' has ' . $future . ' scheduled leave day(s) ahead — plan backup agent for those dates.';
        }
        if (($leaveRow['unpaid_leave_days'] ?? 0) > 0) {
            $tips[] = 'Includes unpaid leave — higher risk of missed collections on those days.';
        }
        $tips[] = 'Cover ' . $name . '\'s collection areas on leave days or shift high-value customers to another agent.';

        return $tips;
    }

    protected function buildSummarySuggestions(
        array $agentAttendance,
        float $collectedMtd,
        float $pendingTotal,
        float $predictedRemaining,
        float $predictedMonthTotal,
        int $confidence,
        array $holidayDates,
        int $workingDaysRemaining,
        int $weekendDaysRemaining,
        array $weather,
        int $daysElapsed,
        int $daysRemaining,
        int $customerCount
    ): array {
        $tips = [];

        $tips[] = [
            'type' => 'overview',
            'priority' => 'high',
            'message' => 'Predicted month-end collection: ₹' . number_format($predictedMonthTotal, 0) . ' (₹' . number_format($collectedMtd, 0) . ' collected + ₹' . number_format($predictedRemaining, 0) . ' expected remaining). Confidence ~' . $confidence . '%.',
        ];

        $tips[] = [
            'type' => 'agent_attendance',
            'priority' => $agentAttendance['agents_on_leave'] > 0 ? 'high' : 'medium',
            'message' => $agentAttendance['message'],
        ];

        if ($agentAttendance['total_agents'] > 0) {
            $tips[] = [
                'type' => 'agent_capacity',
                'priority' => 'medium',
                'message' => 'Agent attendance rate: ' . $agentAttendance['attendance_pct'] . '% (' . $agentAttendance['agents_full_attendance'] . ' of ' . $agentAttendance['total_agents'] . ' without leave). Estimated field capacity: ' . $agentAttendance['estimated_capacity_pct'] . '%.',
            ];
        }

        if ($pendingTotal > 0) {
            $recoveryPct = round(($predictedRemaining / max(1, $pendingTotal)) * 100, 1);
            $tips[] = [
                'type' => 'collection',
                'priority' => 'high',
                'message' => '₹' . number_format($pendingTotal, 0) . ' outstanding across ' . $customerCount . ' customer(s) — model expects ~' . $recoveryPct . '% (₹' . number_format($predictedRemaining, 0) . ') by month-end if you follow from today.',
            ];
        }

        if (count($holidayDates) > 0) {
            $tips[] = [
                'type' => 'holiday',
                'priority' => 'medium',
                'message' => count($holidayDates) . ' government holiday(s) this month — avoid planning major collection drives on those dates; use working days (' . $workingDaysRemaining . ' left, ' . $weekendDaysRemaining . ' Sunday(s)).',
            ];
        }

        if (isset($weather['multiplier']) && $weather['multiplier'] < 0.95) {
            $tips[] = [
                'type' => 'weather',
                'priority' => 'medium',
                'message' => $weather['summary'] . ' Check area-wise rain timings below — visit before or after rainy hours.',
            ];
        }

        if (!empty($weather['by_area'])) {
            foreach (array_slice($weather['by_area'], 0, 5) as $areaWx) {
                if (empty($areaWx['today_rainy_windows'])) {
                    continue;
                }
                foreach ($areaWx['today_rainy_windows'] as $win) {
                    $tips[] = [
                        'type' => 'weather_area',
                        'priority' => 'high',
                        'message' => $win['suggestion'],
                    ];
                }
            }
        }

        if ($daysElapsed >= 20) {
            $tips[] = [
                'type' => 'timing',
                'priority' => 'high',
                'message' => 'More than 20 days completed — focus on remainder forecast only. Prioritize customers who usually pay late and areas with agent leave.',
            ];
        } elseif ($daysRemaining <= 7) {
            $tips[] = [
                'type' => 'timing',
                'priority' => 'high',
                'message' => 'Month-end approaching (' . $daysRemaining . ' day(s) left) — daily follow-up from today recommended.',
            ];
        } else {
            $tips[] = [
                'type' => 'timing',
                'priority' => 'low',
                'message' => 'Follow regular route from today; customers with fixed payment dates (see customer tab) should be visited on their usual day.',
            ];
        }

        return $tips;
    }

    protected function agentCollectionStats(array $branchIds, Carbon $from, Carbon $to): array
    {
        $rows = Receipt::whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->whereNotNull('employee_id')
            ->where('employee_id', '>', 0)
            ->whereBetween('receipt_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('employee_id, COALESCE(SUM(received_amount),0) + COALESCE(SUM(penalty),0) as total, COUNT(DISTINCT DATE(receipt_date)) as active_days')
            ->groupBy('employee_id')
            ->get();

        $stats = [];
        foreach ($rows as $row) {
            $days = max(1, (int) $row->active_days);
            $stats[(int) $row->employee_id] = [
                'total' => (float) $row->total,
                'avg_daily' => (float) $row->total / $days,
            ];
        }

        return $stats;
    }

    protected function buildFactorScores($cd, float $avgDelay, float $agentDaily, array $holidayDates, int $weekendRemaining, int $daysRemaining, int $workingDaysRemaining, float $weatherMultiplier, float $leaveFactor = 1.0): array
    {
        $customerFactor = 1.0;
        if ($avgDelay <= 7) {
            $customerFactor = 1.12;
        } elseif ($avgDelay <= 15) {
            $customerFactor = 1.0;
        } elseif ($avgDelay <= 30) {
            $customerFactor = 0.88;
        } else {
            $customerFactor = 0.72;
        }

        $agentFactor = 1.0;
        if ($agentDaily >= 50000) {
            $agentFactor = 1.1;
        } elseif ($agentDaily >= 15000) {
            $agentFactor = 1.0;
        } elseif ($agentDaily > 0) {
            $agentFactor = 0.9;
        } else {
            $agentFactor = 0.85;
        }

        $paymentType = (int) ($cd->payment_type ?? 3);
        if ($paymentType === 1) {
            $salaryFactor = 1.05;
        } elseif ($paymentType === 2) {
            $salaryFactor = 1.0;
        } else {
            $salaryFactor = 0.9;
        }

        $holidayFactor = 1.0;
        if ($daysRemaining > 0) {
            $holidayFactor = max(0.75, 1 - (count($holidayDates) * 0.03));
        }

        $weekendFactor = 1.0;
        if ($daysRemaining > 0) {
            $weekendFactor = max(0.7, $workingDaysRemaining / max(1, $daysRemaining));
        }

        $leaveFactor = max(0.65, min(1.0, $leaveFactor));
        $uncertaintyFactor = 0.94;

        $combined = $customerFactor * $agentFactor * $salaryFactor * $holidayFactor * $weekendFactor * $weatherMultiplier * $leaveFactor * $uncertaintyFactor;
        $timeBlend = $daysRemaining > 0 ? min(1.0, ($workingDaysRemaining / max(1, $daysRemaining)) + 0.15) : 0.0;
        $combined *= $timeBlend;

        return [
            'combined_score' => $combined,
            'breakdown' => [
                'customer_payment_history' => round($customerFactor, 3),
                'agent_collection_history' => round($agentFactor, 3),
                'govt_holidays' => round($holidayFactor, 3),
                'weekend_holidays' => round($weekendFactor, 3),
                'payment_type_salary_cycle' => round($salaryFactor, 3),
                'weather' => round($weatherMultiplier, 3),
                'agent_leave' => round($leaveFactor, 3),
                'unexpected_buffer' => round($uncertaintyFactor, 3),
            ],
        ];
    }

    protected function resolveWeatherImpact(int $tenantId, array $branchIds, Request $request, array $areaIdsUsed, Carbon $asOf, Carbon $monthEnd): array
    {
        $areaQuery = CollectionArea::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $branchIds)
            ->where('status', 1);

        if (!empty($request->area_id)) {
            $areaQuery->where('id', (int) $request->area_id);
        } elseif (count($areaIdsUsed) > 0) {
            $areaQuery->whereIn('id', $areaIdsUsed);
        }

        $cols = ['id', 'collection_area'];
        if (Schema::hasColumn('collection_areas', 'latitude')) {
            $cols[] = 'latitude';
            $cols[] = 'longitude';
        }
        $areas = $areaQuery->get($cols);

        $forecastDays = min(16, max(2, $asOf->diffInDays($monthEnd) + 2));
        $forecastCache = [];
        $byArea = [];
        $heavyRainDays = 0;
        $dailyRainGlobal = [];

        foreach ($areas as $a) {
            $areaId = (int) $a->id;
            $hasCoords = isset($a->latitude) && $a->latitude !== null && $a->longitude !== null && (float) $a->latitude != 0;
            $lat = $hasCoords ? (float) $a->latitude : (float) env('WEATHER_DEFAULT_LAT', self::DEFAULT_LAT);
            $lng = $hasCoords ? (float) $a->longitude : (float) env('WEATHER_DEFAULT_LNG', self::DEFAULT_LNG);
            $coordKey = round($lat, 3) . ',' . round($lng, 3);

            $hourlyBody = null;
            if (isset($forecastCache[$coordKey])) {
                $hourlyBody = $forecastCache[$coordKey];
            } else {
                try {
                    $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'hourly' => 'precipitation,precipitation_probability,weathercode',
                        'daily' => 'precipitation_sum',
                        'forecast_days' => $forecastDays,
                        'timezone' => 'Asia/Kolkata',
                    ]);
                    if ($response->successful()) {
                        $hourlyBody = $response->json();
                        $forecastCache[$coordKey] = $hourlyBody;
                    }
                } catch (\Exception $e) {
                    $hourlyBody = null;
                }
            }

            $rainyWindows = [];
            $todayRainyWindows = [];
            $upcomingRainyWindows = [];
            $bestSlotsToday = ['Any time today — no rain expected during collection hours (8am–7pm).'];

            if ($hourlyBody && isset($hourlyBody['hourly'])) {
                $rainyWindows = $this->extractRainyWindows($hourlyBody['hourly'], $asOf, $monthEnd);
                foreach ($rainyWindows as $win) {
                    if ($win['date'] === $asOf->toDateString()) {
                        $todayRainyWindows[] = $win;
                    } elseif ($win['date'] > $asOf->toDateString()) {
                        $upcomingRainyWindows[] = $win;
                    }
                }
                $bestSlotsToday = $this->buildBestCollectionSlots($hourlyBody['hourly'], $asOf);
            }

            foreach ($todayRainyWindows as &$tw) {
                $tw['suggestion'] = 'In ' . $a->collection_area . ': rain ' . $tw['time_label'] . ' today — plan collection before ' . $tw['from_label'] . ' or after ' . $tw['to_label'] . '.';
            }
            unset($tw);
            foreach ($upcomingRainyWindows as &$uw) {
                $uw['suggestion'] = 'In ' . $a->collection_area . ': rain on ' . $uw['date_label'] . ' ' . $uw['time_label'] . ' — plan before ' . $uw['from_label'] . ' or after ' . $uw['to_label'] . '.';
            }
            unset($uw);

            if ($hourlyBody && isset($hourlyBody['daily']['precipitation_sum']) && count($dailyRainGlobal) === 0) {
                foreach ($hourlyBody['daily']['precipitation_sum'] as $i => $mm) {
                    $dailyRainGlobal[] = [
                        'date' => $hourlyBody['daily']['time'][$i] ?? '',
                        'precipitation_mm' => (float) $mm,
                    ];
                    if ((float) $mm >= 15) {
                        $heavyRainDays++;
                    }
                }
            }

            $byArea[$areaId] = [
                'area_id' => $areaId,
                'area_name' => $a->collection_area,
                'latitude' => $lat,
                'longitude' => $lng,
                'has_coordinates' => $hasCoords,
                'rainy_windows' => array_slice($rainyWindows, 0, 14),
                'today_rainy_windows' => $todayRainyWindows,
                'upcoming_rainy_windows' => array_slice($upcomingRainyWindows, 0, 7),
                'best_collection_slots_today' => $bestSlotsToday,
            ];
        }

        if (count($byArea) === 0) {
            $lat = (float) env('WEATHER_DEFAULT_LAT', self::DEFAULT_LAT);
            $lng = (float) env('WEATHER_DEFAULT_LNG', self::DEFAULT_LNG);
            try {
                $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'hourly' => 'precipitation,precipitation_probability,weathercode',
                    'daily' => 'precipitation_sum',
                    'forecast_days' => $forecastDays,
                    'timezone' => 'Asia/Kolkata',
                ]);
                if ($response->successful()) {
                    $body = $response->json();
                    $rainyWindows = $this->extractRainyWindows($body['hourly'] ?? [], $asOf, $monthEnd);
                    $byArea[0] = [
                        'area_id' => 0,
                        'area_name' => 'Tamil Nadu default',
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'has_coordinates' => false,
                        'rainy_windows' => array_slice($rainyWindows, 0, 14),
                        'today_rainy_windows' => array_values(array_filter($rainyWindows, function ($w) use ($asOf) {
                            return $w['date'] === $asOf->toDateString();
                        })),
                        'upcoming_rainy_windows' => [],
                        'best_collection_slots_today' => $this->buildBestCollectionSlots($body['hourly'] ?? [], $asOf),
                    ];
                    foreach ($body['daily']['precipitation_sum'] ?? [] as $i => $mm) {
                        $dailyRainGlobal[] = ['date' => $body['daily']['time'][$i] ?? '', 'precipitation_mm' => (float) $mm];
                        if ((float) $mm >= 15) {
                            $heavyRainDays++;
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $hasCoordinates = count(array_filter($byArea, function ($a) {
            return !empty($a['has_coordinates']);
        })) > 0;

        $multiplier = max(0.82, 1 - ($heavyRainDays * 0.04));
        $todayRainCount = 0;
        foreach ($byArea as $a) {
            $todayRainCount += count($a['today_rainy_windows']);
        }

        if ($todayRainCount > 0) {
            $summary = $todayRainCount . ' rainy time window(s) today across collection areas — plan visits before or after rain (see area table).';
        } elseif ($heavyRainDays > 0) {
            $summary = $heavyRainDays . ' heavy-rain day(s) forecast this month — use hourly area plan for field collection.';
        } else {
            $summary = 'No significant rain during usual collection hours — minimal weather impact expected.';
        }

        $firstArea = reset($byArea);

        return [
            'provider' => 'Open-Meteo (free, no API key required)',
            'has_coordinates' => $hasCoordinates,
            'location_label' => $hasCoordinates ? 'Per collection area (hourly forecast)' : 'Tamil Nadu default (add lat/lng on collection areas)',
            'latitude' => $firstArea ? $firstArea['latitude'] : self::DEFAULT_LAT,
            'longitude' => $firstArea ? $firstArea['longitude'] : self::DEFAULT_LNG,
            'multiplier' => round($multiplier, 3),
            'summary' => $summary,
            'daily_precipitation' => array_slice($dailyRainGlobal, 0, 10),
            'by_area' => array_values($byArea),
        ];
    }

    protected function areaWeatherTips(array $weather, int $areaId, string $areaName, Carbon $asOf): array
    {
        $tips = [];
        $areaWx = null;
        foreach ($weather['by_area'] ?? [] as $row) {
            if ((int) $row['area_id'] === $areaId) {
                $areaWx = $row;
                break;
            }
        }

        if (!$areaWx && $areaId <= 0 && !empty($weather['by_area'][0])) {
            $areaWx = $weather['by_area'][0];
        }

        if (!$areaWx) {
            return $tips;
        }

        $label = $areaName !== '-' ? $areaName : $areaWx['area_name'];

        if (count($areaWx['today_rainy_windows']) > 0) {
            foreach ($areaWx['today_rainy_windows'] as $win) {
                $tips[] = 'Weather — ' . $label . ': rain ' . $win['time_label'] . ' today — plan collection before ' . $win['from_label'] . ' or after ' . $win['to_label'] . '.';
            }
        } elseif (!empty($areaWx['best_collection_slots_today'])) {
            $tips[] = 'Weather — ' . $label . ': ' . $areaWx['best_collection_slots_today'][0];
        }

        if (count($areaWx['upcoming_rainy_windows']) > 0) {
            $next = $areaWx['upcoming_rainy_windows'][0];
            $tips[] = 'Weather — ' . $label . ': next rain on ' . $next['date_label'] . ' (' . $next['time_label'] . ') — avoid that slot or collect earlier.';
        }

        return $tips;
    }

    protected function isRainyHour(float $precipMm, float $precipProb, int $weatherCode): bool
    {
        if ($precipMm >= 0.3) {
            return true;
        }
        if ($precipProb >= 55) {
            return true;
        }
        $rainCodes = [51, 52, 53, 54, 55, 56, 57, 61, 62, 63, 64, 65, 66, 67, 80, 81, 82, 95, 96, 99];

        return in_array($weatherCode, $rainCodes, true);
    }

    protected function extractRainyWindows(array $hourly, Carbon $asOf, Carbon $monthEnd): array
    {
        $times = $hourly['time'] ?? [];
        $precip = $hourly['precipitation'] ?? [];
        $precipProb = $hourly['precipitation_probability'] ?? [];
        $codes = $hourly['weathercode'] ?? [];

        $windows = [];
        $current = null;

        for ($i = 0; $i < count($times); $i++) {
            $dt = Carbon::parse($times[$i], 'Asia/Kolkata');
            if ($dt->lt($asOf->copy()->startOfDay()) || $dt->gt($monthEnd->copy()->endOfDay())) {
                continue;
            }

            $hour = (int) $dt->format('G');
            if ($hour < 8 || $hour > 19) {
                if ($current !== null) {
                    $windows[] = $this->finalizeRainWindow($current, $asOf);
                    $current = null;
                }
                continue;
            }

            $rainy = $this->isRainyHour(
                (float) ($precip[$i] ?? 0),
                (float) ($precipProb[$i] ?? 0),
                (int) ($codes[$i] ?? 0)
            );

            if ($rainy) {
                if ($current === null) {
                    $current = ['start' => $dt->copy(), 'end' => $dt->copy()];
                } else {
                    $current['end'] = $dt->copy();
                }
            } elseif ($current !== null) {
                $windows[] = $this->finalizeRainWindow($current, $asOf);
                $current = null;
            }
        }

        if ($current !== null) {
            $windows[] = $this->finalizeRainWindow($current, $asOf);
        }

        return $windows;
    }

    protected function finalizeRainWindow(array $current, Carbon $asOf): array
    {
        $start = $current['start'];
        $end = $current['end']->copy()->addHour();
        $fromLabel = $this->formatHour12((int) $start->format('G'));
        $toLabel = $this->formatHour12((int) $end->format('G'));
        $timeLabel = $fromLabel . ' to ' . $toLabel;
        $dateLabel = $this->weatherDateLabel($start, $asOf);

        return [
            'date' => $start->toDateString(),
            'date_label' => $dateLabel,
            'from' => $start->format('H:i'),
            'to' => $end->format('H:i'),
            'from_label' => $fromLabel,
            'to_label' => $toLabel,
            'time_label' => $timeLabel,
            'suggestion' => 'Rain expected ' . $dateLabel . ' ' . $timeLabel . ' — plan field collection before ' . $fromLabel . ' or after ' . $toLabel . '.',
        ];
    }

    protected function buildBestCollectionSlots(array $hourly, Carbon $asOf): array
    {
        $times = $hourly['time'] ?? [];
        $precip = $hourly['precipitation'] ?? [];
        $precipProb = $hourly['precipitation_probability'] ?? [];
        $codes = $hourly['weathercode'] ?? [];
        $today = $asOf->toDateString();

        $rainyHours = [];
        for ($i = 0; $i < count($times); $i++) {
            $dt = Carbon::parse($times[$i], 'Asia/Kolkata');
            if ($dt->toDateString() !== $today) {
                continue;
            }
            $hour = (int) $dt->format('G');
            if ($hour < 8 || $hour > 19) {
                continue;
            }
            if ($this->isRainyHour((float) ($precip[$i] ?? 0), (float) ($precipProb[$i] ?? 0), (int) ($codes[$i] ?? 0))) {
                $rainyHours[] = $hour;
            }
        }

        if (count($rainyHours) === 0) {
            return ['Good window: 8am–7pm today — no rain expected during collection hours.'];
        }

        sort($rainyHours);
        $firstRain = $rainyHours[0];
        $lastRain = $rainyHours[count($rainyHours) - 1];
        $slots = [];

        if ($firstRain > 8) {
            $slots[] = 'Morning slot: before ' . $this->formatHour12($firstRain) . ' today';
        }
        if ($lastRain < 19) {
            $slots[] = 'Evening slot: after ' . $this->formatHour12($lastRain + 1) . ' today';
        }
        if (count($slots) === 0) {
            $slots[] = 'Rain likely most of the day — prefer phone collection or office visit today.';
        }

        return $slots;
    }

    protected function formatHour12(int $hour24): string
    {
        $hour24 = max(0, min(23, $hour24));
        $h = $hour24 % 12;
        if ($h === 0) {
            $h = 12;
        }

        return $h . ($hour24 < 12 ? 'am' : 'pm');
    }

    protected function weatherDateLabel(Carbon $date, Carbon $asOf): string
    {
        if ($date->toDateString() === $asOf->toDateString()) {
            return 'today';
        }
        if ($date->toDateString() === $asOf->copy()->addDay()->toDateString()) {
            return 'tomorrow';
        }

        return $date->format('d M');
    }

    protected function countWorkingDays(Carbon $start, Carbon $end, array $holidayDates): int
    {
        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (!$cursor->isSunday()) {
                $d = $cursor->toDateString();
                if (!in_array($d, $holidayDates, true)) {
                    $count++;
                }
            }
            $cursor->addDay();
        }

        return $count;
    }

    protected function countWeekendDays(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($cursor->isSunday()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    protected function aggregateBucket(array &$bucket, string $idKey, string $nameKey, int $id, string $name, float $mtd, float $pending, float $predicted): void
    {
        if ($id <= 0 && $name === '-') {
            return;
        }
        $key = $idKey . '_' . $id;
        if (!isset($bucket[$key])) {
            $bucket[$key] = [
                $idKey => $id,
                $nameKey => $name,
                'collected_mtd' => 0,
                'pending_amount' => 0,
                'predicted_remaining' => 0,
                'predicted_month_total' => 0,
            ];
        }
        $bucket[$key]['collected_mtd'] += $mtd;
        $bucket[$key]['pending_amount'] += $pending;
        $bucket[$key]['predicted_remaining'] += $predicted;
        $bucket[$key]['predicted_month_total'] = round($bucket[$key]['collected_mtd'] + $bucket[$key]['predicted_remaining'], 2);
    }

    protected function finalizeBuckets(array $buckets): array
    {
        foreach ($buckets as &$b) {
            $b['collected_mtd'] = round($b['collected_mtd'], 2);
            $b['pending_amount'] = round($b['pending_amount'], 2);
            $b['predicted_remaining'] = round($b['predicted_remaining'], 2);
        }

        usort($buckets, function ($a, $b) {
            return $b['predicted_remaining'] <=> $a['predicted_remaining'];
        });

        return $buckets;
    }

    protected function agentLeaveFactors(array $branchIds, Carbon $monthStart, Carbon $asOf, Carbon $monthEnd, int $daysElapsed): array
    {
        $records = EmployeeAttendance::whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->whereBetween('att_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('attendance_type', 0)
            ->get(['employee_id', 'att_date', 'forenoon_session', 'afternoon_session', 'leave_type']);

        $byAgent = [];
        foreach ($records as $rec) {
            $eid = (int) $rec->employee_id;
            if ($eid <= 0) {
                continue;
            }

            $fn = (int) $rec->forenoon_session;
            $an = (int) $rec->afternoon_session;
            $dayWeight = 1.0;
            if ($fn === 1 && $an === 1) {
                $dayWeight = 1.0;
            } elseif ($fn === 1 || $an === 1) {
                $dayWeight = 0.5;
            } elseif ($fn === 0 && $an === 0) {
                $dayWeight = 1.0;
            }

            if (!isset($byAgent[$eid])) {
                $byAgent[$eid] = [
                    'leave_days_mtd' => 0.0,
                    'scheduled_future' => 0.0,
                    'unpaid_leave_days' => 0.0,
                ];
            }

            $attDate = Carbon::parse($rec->att_date);
            if ($attDate->lte($asOf)) {
                $byAgent[$eid]['leave_days_mtd'] += $dayWeight;
                if ((int) $rec->leave_type === 2) {
                    $byAgent[$eid]['unpaid_leave_days'] += $dayWeight;
                }
            } else {
                $byAgent[$eid]['scheduled_future'] += $dayWeight;
            }
        }

        $factors = [];
        foreach ($byAgent as $eid => $stats) {
            $leaveRate = min(1.0, $stats['leave_days_mtd'] / max(1, $daysElapsed));
            $futurePenalty = min(0.3, $stats['scheduled_future'] * 0.05);
            $unpaidPenalty = min(0.15, $stats['unpaid_leave_days'] * 0.03);
            $factor = max(0.65, 1 - ($leaveRate * 0.45) - $futurePenalty - $unpaidPenalty);

            $factors[$eid] = [
                'factor' => round($factor, 3),
                'leave_days_mtd' => round($stats['leave_days_mtd'], 1),
                'scheduled_future_leave' => round($stats['scheduled_future'], 1),
                'unpaid_leave_days' => round($stats['unpaid_leave_days'], 1),
                'agent_name' => $this->employeeName((int) $eid) ?: ('Agent #' . $eid),
            ];
        }

        return $factors;
    }

    protected function resolveAgentLeaveFactor(int $agentId, array $agentLeaveFactors): float
    {
        if ($agentId <= 0 || !isset($agentLeaveFactors[$agentId])) {
            return 1.0;
        }

        return (float) $agentLeaveFactors[$agentId]['factor'];
    }

    protected function summarizeFactorImpact(array $weather, array $holidayDates, int $weekendRemaining, int $workingRemaining, int $daysRemaining, array $agentLeaveFactors = [], array $externalTnHolidays = []): array
    {
        return [
            ['factor' => 'customer_payment_history', 'label' => 'Customer previous payments', 'impact' => 'High', 'note' => 'Based on average days from auction due date to receipt date.'],
            ['factor' => 'agent_collections', 'label' => 'Agent previous collections', 'impact' => 'High', 'note' => 'Uses employee_id on receipts for last 3 months.'],
            ['factor' => 'govt_holidays', 'label' => 'Government holidays', 'impact' => count($holidayDates) > 0 ? 'Medium' : 'Low', 'note' => count($holidayDates) . ' holiday day(s) this month (Auction on Holidays + Tamil Nadu feed). External TN-only: ' . count($externalTnHolidays) . '.'],
            ['factor' => 'weekend_holidays', 'label' => 'Weekend holidays (Sunday)', 'impact' => 'Medium', 'note' => $weekendRemaining . ' Sunday(s) remaining; ' . $workingRemaining . ' working day(s) left.'],
            ['factor' => 'payment_type', 'label' => 'Customer salary / payment type', 'impact' => 'Medium', 'note' => 'Daily / weekly / monthly enrollment payment type from chit_details.'],
            ['factor' => 'weather', 'label' => 'Weather condition', 'impact' => 'Low–Medium', 'note' => $weather['summary']],
            ['factor' => 'agent_leave', 'label' => 'Agent leave', 'impact' => count($agentLeaveFactors) > 0 ? 'Medium' : 'Low', 'note' => count($agentLeaveFactors) > 0
                ? count($agentLeaveFactors) . ' agent(s) with leave recorded this month via Employee Attendance.'
                : 'No agent leave recorded this month; full capacity assumed.'],
            ['factor' => 'unexpected', 'label' => 'Other unexpected reasons', 'impact' => 'Low', 'note' => '6% conservative buffer for unreachable areas, office closure, accidents, etc.'],
        ];
    }

    protected function estimateConfidence(int $daysElapsed, int $daysInMonth, int $enrollmentCount, bool $hasCoords): int
    {
        $base = min(95, 40 + (int) round(($daysElapsed / max(1, $daysInMonth)) * 45));
        if ($enrollmentCount > 50) {
            $base += 5;
        }
        if ($hasCoords) {
            $base += 3;
        }

        return min(92, max(35, $base));
    }

    protected function paymentTypeLabel(int $type): string
    {
        if ($type === 1) {
            return 'Daily';
        }
        if ($type === 2) {
            return 'Weekly';
        }
        if ($type === 3) {
            return 'Monthly';
        }

        return '-';
    }

    protected function employeeName(int $employeeId): ?string
    {
        static $cache = [];
        if (isset($cache[$employeeId])) {
            return $cache[$employeeId];
        }
        $emp = Employee::find($employeeId);
        $cache[$employeeId] = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : null;

        return $cache[$employeeId];
    }

    public function employee_performance_ai(Request $request)
    {
        try {
            if (empty($request->tenant_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'tenant_id required'], 401);
            }

            $tenantId = (int) $request->tenant_id;
            $branchIds = $this->resolveBranchIds($request, $tenantId);
            $asOf = Carbon::today();
            $monthStart = $asOf->copy()->startOfMonth();
            $from3 = $monthStart->copy()->subMonths(2)->startOfMonth();
            $daysElapsed = max(1, $monthStart->diffInDays($asOf) + 1);

            $enrollQuery = ChitDetail::with(['customer_det', 'branch_det', 'collection_area_det'])
                ->where('tenant_id', $tenantId)
                ->where('chit_type', 3)
                ->whereNull('deleted_at')
                ->where('customer_id', '!=', 1);
            if (count($branchIds) > 0) {
                $enrollQuery->whereIn('branch_id', $branchIds);
            } else {
                $enrollQuery->whereRaw('1 = 0');
            }
            $enrollments = $enrollQuery->get();
            $enrollmentIds = $enrollments->pluck('id')->all();
            $paid = $this->paidTotalsByEnrollment($enrollmentIds);
            $due = $this->dueTotalsByEnrollment($enrollmentIds);
            $areaAgentMap = $this->buildAreaAgentMap($tenantId, $branchIds);
            $agentStats = $this->agentCollectionStats($branchIds, $from3, $asOf);
            $leave = $this->agentLeaveFactors($branchIds, $monthStart, $asOf, $asOf->copy()->endOfMonth(), $daysElapsed);

            $receiptQuery = Receipt::where('status', 1)
                ->whereBetween('receipt_date', [$monthStart->toDateString(), $asOf->toDateString()]);
            if (count($branchIds) > 0) {
                $receiptQuery->whereIn('branch_id', $branchIds);
            }
            if (count($enrollmentIds) > 0) {
                $receiptQuery->whereIn('enrollment_id', $enrollmentIds);
            } else {
                $receiptQuery->whereRaw('1 = 0');
            }
            $receiptMtd = $receiptQuery
                ->selectRaw('employee_id, COALESCE(SUM(received_amount),0) as total, COUNT(*) as receipt_count')
                ->groupBy('employee_id')
                ->get();

            $book = [];
            foreach ($enrollments as $cd) {
                $agentId = (int) ($cd->collection_agent_id ?? 0);
                $areaId = (int) ($cd->collection_area_id ?? 0);
                if ($agentId <= 0 && $areaId > 0 && isset($areaAgentMap[$areaId])) {
                    $agentId = (int) $areaAgentMap[$areaId];
                }
                if ($agentId <= 0) {
                    continue;
                }
                if (!isset($book[$agentId])) {
                    $book[$agentId] = ['customers' => 0, 'pending' => 0, 'areas' => []];
                }
                $eid = (int) $cd->id;
                $pending = max(0, (float) ($due[$eid] ?? 0) - (float) ($paid[$eid] ?? 0));
                $book[$agentId]['customers']++;
                $book[$agentId]['pending'] += $pending;
                if ($areaId > 0) {
                    $book[$agentId]['areas'][$areaId] = true;
                }
            }

            $receiptMap = [];
            foreach ($receiptMtd as $row) {
                $receiptMap[(int) $row->employee_id] = $row;
            }

            $ids = array_unique(array_merge(array_keys($book), array_keys($receiptMap)));
            if (!empty($request->employee_id) && (int) $request->employee_id > 0) {
                $ids = [(int) $request->employee_id];
            }

            $rows = [];
            foreach ($ids as $empId) {
                $empId = (int) $empId;
                if ($empId <= 0) {
                    continue;
                }
                $b = $book[$empId] ?? ['customers' => 0, 'pending' => 0, 'areas' => []];
                $rec = $receiptMap[$empId] ?? null;
                $collected = $rec ? (float) $rec->total : 0.0;
                $receiptCount = $rec ? (int) $rec->receipt_count : 0;
                $avgDaily = isset($agentStats[$empId]) ? (float) $agentStats[$empId]['avg_daily'] : 0.0;
                $threeMonth = isset($agentStats[$empId]) ? (float) $agentStats[$empId]['total'] : 0.0;
                $leaveDays = isset($leave[$empId]) ? (float) $leave[$empId]['leave_days_mtd'] : 0.0;
                $leaveFactor = isset($leave[$empId]) ? (float) $leave[$empId]['factor'] : 1.0;
                $pending = (float) $b['pending'];
                $recovery = $pending > 0 ? min(100, round(($collected / $pending) * 100, 1)) : ($collected > 0 ? 100 : 0);
                $score = $this->employeeScore($collected, $avgDaily, $leaveFactor, (int) $b['customers'], $pending, $receiptCount);
                $grade = $score >= 85 ? 'Excellent' : ($score >= 70 ? 'Good' : ($score >= 50 ? 'Average' : 'Needs attention'));
                $name = $this->employeeName($empId) ?: ('Employee #' . $empId);
                $rows[] = [
                    'employee_id' => $empId,
                    'employee_name' => $name,
                    'customers' => (int) $b['customers'],
                    'areas' => count($b['areas']),
                    'collected_mtd' => round($collected, 2),
                    'collected_3m' => round($threeMonth, 2),
                    'avg_daily' => round($avgDaily, 2),
                    'pending_book' => round($pending, 2),
                    'receipts_mtd' => $receiptCount,
                    'recovery_pct' => $recovery,
                    'leave_days' => $leaveDays,
                    'score' => $score,
                    'grade' => $grade,
                    'narrative' => $this->employeeNarrative($name, $grade, $score, $collected, $pending, (int) $b['customers'], $leaveDays, $avgDaily, $receiptCount, $recovery),
                    'actions' => $this->employeeActions($grade, $leaveDays, $pending, $collected, (int) $b['customers']),
                ];
            }

            usort($rows, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            $rank = 1;
            foreach ($rows as &$row) {
                $row['rank'] = $rank++;
            }
            unset($row);

            $summary = [
                'employee_count' => count($rows),
                'collected_mtd' => round(array_sum(array_column($rows, 'collected_mtd')), 2),
                'pending_book' => round(array_sum(array_column($rows, 'pending_book')), 2),
                'avg_score' => count($rows) ? round(array_sum(array_column($rows, 'score')) / count($rows), 1) : 0,
            ];
            $top = $rows[0] ?? null;
            $overview = 'Emglitz Intelligence AI reviewed ' . count($rows) . ' collection employee(s) on commitment chits for ' . $asOf->format('F Y') . '. Month-to-date collection is ₹' . number_format($summary['collected_mtd'], 0) . ' against a pending book of ₹' . number_format($summary['pending_book'], 0) . '. Average performance score is ' . $summary['avg_score'] . '/100.';
            if ($top) {
                $overview .= ' ' . $top['employee_name'] . ' leads with a ' . $top['grade'] . ' score of ' . $top['score'] . '.';
            }

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'analysed_by' => 'Emglitz Intelligence AI',
                    'period' => [
                        'month_label' => $asOf->format('F Y'),
                        'to_date' => $asOf->toDateString(),
                    ],
                    'overview' => $overview,
                    'summary' => $summary,
                    'employees' => $rows,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to analyse employees: ' . $e->getMessage()], 200);
        }
    }

    protected function employeeScore(float $collected, float $avgDaily, float $leaveFactor, int $customers, float $pending, int $receipts): int
    {
        $volume = min(35, ($collected / 200000) * 35);
        $pace = min(25, ($avgDaily / 15000) * 25);
        $attendance = max(0, min(20, $leaveFactor * 20));
        $recovery = $pending > 0 ? min(20, ($collected / $pending) * 20) : ($receipts > 0 ? 15 : 0);
        if ($customers === 0 && $receipts === 0) {
            return (int) round($attendance);
        }

        return (int) min(100, max(0, round($volume + $pace + $attendance + $recovery)));
    }

    protected function employeeNarrative(string $name, string $grade, int $score, float $collected, float $pending, int $customers, float $leaveDays, float $avgDaily, int $receipts, $recovery): string
    {
        $text = $name . ' is rated ' . $grade . ' (' . $score . '/100) by Emglitz Intelligence AI. ';
        $text .= 'This month the employee collected ₹' . number_format($collected, 0) . ' across ' . $receipts . ' commitment receipt(s)';
        if ($customers > 0) {
            $text .= ' on a book of ' . $customers . ' customer(s) with ₹' . number_format($pending, 0) . ' still pending';
        }
        $text .= '. ';
        if ($avgDaily > 0) {
            $text .= 'The recent daily pace is about ₹' . number_format($avgDaily, 0) . '. ';
        }
        if ($leaveDays > 0) {
            $text .= $leaveDays . ' leave day(s) reduced field capacity. ';
        } else {
            $text .= 'Attendance is clear this month, so field capacity is full. ';
        }
        if ($pending > 0 && $recovery < 40) {
            $text .= 'Recovery against the pending book is still low — start follow-up from today on the highest pending customers.';
        } elseif ($grade === 'Excellent' || $grade === 'Good') {
            $text .= 'Keep the same route and use this employee to cover any area where another agent is on leave.';
        } else {
            $text .= 'Review the route, pending customers, and collection timing so the score moves above 70.';
        }

        return $text;
    }

    protected function employeeActions(string $grade, float $leaveDays, float $pending, float $collected, int $customers): array
    {
        $actions = [];
        if ($leaveDays > 0) {
            $actions[] = 'Cover this route on leave days or assign a backup agent.';
        }
        if ($pending > $collected && $pending > 0) {
            $actions[] = 'Pending book is higher than this month’s collection — visit customers who usually pay late first.';
        }
        if ($customers === 0) {
            $actions[] = 'No commitment customers are mapped to this employee. Check collection area mapping.';
        }
        if ($grade === 'Needs attention') {
            $actions[] = 'Supervisor review is recommended before month-end.';
        }
        if ($grade === 'Excellent') {
            $actions[] = 'Use this employee as the reference route for the branch.';
        }
        if (count($actions) === 0) {
            $actions[] = 'Continue the current collection plan from today.';
        }

        return $actions;
    }
}
