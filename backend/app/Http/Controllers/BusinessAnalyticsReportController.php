<?php

namespace App\Http\Controllers;

use App\Models\AdvanceReceipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\BiddingDetail;
use App\Models\Branch;
use App\Models\ChitDetail;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Executive analytics: multi-month history and simple forward-looking projections.
 */
class BusinessAnalyticsReportController extends Controller
{
    protected function scopeReceiptsForDashboard($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('payment_type_id')
                    ->orWhere('payment_type_id', '<>', 7);
            });
    }

    /**
     * @return array<int>
     */
    protected function resolveBranchIds(Request $request, int $tenantId): array
    {
        if (!empty($request->branch_id) && (int) $request->branch_id > 0) {
            return [(int) $request->branch_id];
        }

        return Branch::where('tenant_id', $tenantId)->pluck('id')->toArray();
    }

    /**
     * @param  array<int>  $branchIds
     */
    protected function monthCollectionsAndPayments(array $branchIds, string $start, string $end): array
    {
        $sumR = $this->scopeReceiptsForDashboard(Receipt::whereIn('branch_id', $branchIds))
            ->whereBetween('receipt_date', [$start, $end])
            ->selectRaw('COALESCE(SUM(received_amount),0) as ra, COALESCE(SUM(penalty),0) as pe')
            ->first();
        $receipt = (float) ($sumR->ra ?? 0) + (float) ($sumR->pe ?? 0);

        $adv = (float) AdvanceReceipt::whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('payment_type_id')
                    ->orWhere('payment_type_id', '<>', 9);
            })
            ->whereBetween('receipt_date', [$start, $end])
            ->sum('receipt_amount');

        $before = (float) BeforeEnrolReceipt::whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->whereBetween('received_date', [$start, $end])
            ->sum('amount');

        $collections = $receipt + $adv + $before;

        $payments = (float) Payment::whereIn('branch_id', $branchIds)
            ->where('status', 1)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('total_amount');

        return [
            'collections' => round($collections, 2),
            'payments' => round($payments, 2),
        ];
    }

    /**
     * @param  array<int>  $branchIds
     */
    protected function customerMovement(int $tenantId, array $branchIds, string $start, string $end): array
    {
        $base = Customer::where('tenant_id', $tenantId)->whereIn('branch_id', $branchIds);

        $new = (clone $base)->where('is_customer', 1)
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->count();

        $removed = Customer::onlyTrashed()
            ->where('tenant_id', $tenantId)
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('deleted_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->count();

        return [
            'new_customers' => (int) $new,
            'removed_customers' => (int) $removed,
        ];
    }

    /**
     * @param  array<int>  $branchIds
     */
    protected function newEnrollments(int $tenantId, array $branchIds, string $start, string $end): int
    {
        return (int) ChitDetail::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->count();
    }

    /**
     * One calendar month row.
     *
     * @param  array<int>  $branchIds
     */
    protected function buildMonthRow(int $tenantId, array $branchIds, Carbon $monthStart): array
    {
        $start = $monthStart->copy()->startOfMonth()->toDateString();
        $end = $monthStart->copy()->endOfMonth()->toDateString();
        $m = $this->monthCollectionsAndPayments($branchIds, $start, $end);
        $c = $this->customerMovement($tenantId, $branchIds, $start, $end);
        $enr = $this->newEnrollments($tenantId, $branchIds, $start, $end);

        return [
            'month' => $monthStart->format('Y-m'),
            'month_label' => $monthStart->format('M Y'),
            'collections' => $m['collections'],
            'payments' => $m['payments'],
            'new_customers' => $c['new_customers'],
            'removed_customers' => $c['removed_customers'],
            'new_enrollments' => $enr,
        ];
    }

    public function historical_performance(Request $request)
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
                    'data' => [
                        'range_months' => 3,
                        'months' => [],
                        'totals' => [],
                        'current_outstanding_bid_pending' => 0,
                    ],
                ], 200);
            }

            $months = (int) $request->input('range_months', 3);
            if (!in_array($months, [3, 6, 12], true)) {
                $months = 3;
            }

            $rows = [];
            for ($i = 0; $i < $months; $i++) {
                $ref = Carbon::now()->subMonths($months - 1 - $i)->startOfMonth();
                $rows[] = $this->buildMonthRow($tenantId, $branchIds, $ref);
            }

            $totals = [
                'collections' => round(array_sum(array_column($rows, 'collections')), 2),
                'payments' => round(array_sum(array_column($rows, 'payments')), 2),
                'new_customers' => (int) array_sum(array_column($rows, 'new_customers')),
                'removed_customers' => (int) array_sum(array_column($rows, 'removed_customers')),
                'new_enrollments' => (int) array_sum(array_column($rows, 'new_enrollments')),
            ];

            $pending = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('pending_release');

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'range_months' => $months,
                    'months' => $rows,
                    'totals' => $totals,
                    'current_outstanding_bid_pending' => round(max(0, $pending), 2),
                    'notes' => [
                        'outstanding' => 'Bid pending release total is the current portfolio snapshot (not reconstructed month-end history).',
                        'customers' => 'New subscribers: customers with is_customer=1 created in the month. Removed: soft-deleted in the month.',
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to build historical report',
            ], 401);
        }
    }

    public function forecast(Request $request)
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
                    'data' => [
                        'last_3_months_actual' => [],
                        'next_3_months_forecast' => [],
                        'yoy_same_month_last_year' => [],
                        'derived' => [],
                        'current_outstanding_bid_pending' => 0,
                    ],
                ], 200);
            }

            $last3 = [];
            for ($i = 0; $i < 3; $i++) {
                $ref = Carbon::now()->subMonths(2 - $i)->startOfMonth();
                $last3[] = $this->buildMonthRow($tenantId, $branchIds, $ref);
            }

            $avgCollections = (array_sum(array_column($last3, 'collections')) / 3) ?: 0;
            $avgPayments = (array_sum(array_column($last3, 'payments')) / 3) ?: 0;
            $avgNew = (array_sum(array_column($last3, 'new_customers')) / 3) ?: 0;
            $avgRemoved = (array_sum(array_column($last3, 'removed_customers')) / 3) ?: 0;
            $avgEnroll = (array_sum(array_column($last3, 'new_enrollments')) / 3) ?: 0;

            $forecast = [];
            for ($f = 1; $f <= 3; $f++) {
                $ref = Carbon::now()->addMonths($f)->startOfMonth();
                $growth = 1 + (0.01 * $f);
                $forecast[] = [
                    'month' => $ref->format('Y-m'),
                    'month_label' => $ref->format('M Y'),
                    'collections_forecast' => round($avgCollections * $growth, 2),
                    'payments_forecast' => round($avgPayments * $growth, 2),
                    'new_customers_forecast' => (int) round($avgNew * $growth),
                    'removed_customers_forecast' => (int) round(max(0, $avgRemoved)),
                    'new_enrollments_forecast' => (int) round($avgEnroll * $growth),
                ];
            }

            $yoy = [];
            foreach ($forecast as $row) {
                $m = Carbon::parse($row['month'] . '-01')->subYear()->startOfMonth();
                $yoy[] = $this->buildMonthRow($tenantId, $branchIds, $m);
            }

            $pending = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('pending_release');

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'last_3_months_actual' => $last3,
                    'next_3_months_forecast' => $forecast,
                    'yoy_same_month_last_year' => $yoy,
                    'derived' => [
                        'avg_monthly_collections_last_3' => round($avgCollections, 2),
                        'avg_monthly_payments_last_3' => round($avgPayments, 2),
                        'avg_new_customers_last_3' => round($avgNew, 2),
                        'avg_new_enrollments_last_3' => round($avgEnroll, 2),
                    ],
                    'current_outstanding_bid_pending' => round(max(0, $pending), 2),
                    'notes' => [
                        'forecast' => 'Forecasts use a 3-month moving average with a light upward bias (+1% per month ahead). They are indicative only.',
                        'yoy' => 'Same calendar month last year shows realised figures for comparison with the forward window.',
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to build forecast report',
            ], 401);
        }
    }
}
