<?php

namespace App\Http\Controllers;

use App\Models\AdvanceReceipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\BiddingDetail;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Followup;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Receipt;
use Carbon\Carbon;
use App\Services\UserLoginScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unified web dashboard: charts, today follow-ups, collections, payments,
 * outstanding snapshot, and today's auction groups (by auction day-of-month).
 */
class ChitDashboardController extends Controller
{
    /**
     * Receipt rows counted on dashboard: active, excluding adjustment (7).
     * Rows with NULL payment_type_id are included (legacy rows missed store_receipt assignment).
     */
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
        return app(UserLoginScopeService::class)->resolveEffectiveBranchIds($request, $tenantId);
    }

    /**
     * Customer display for dashboard lists (receipt.customer_id is sometimes 0; enrollment holds subscriber).
     */
    protected function dashboardCustomerName($customer): string
    {
        if (!$customer) {
            return '';
        }
        $first = trim((string) ($customer->name ?? ''));
        $last = trim((string) ($customer->last_name ?? ''));
        $full = trim($first . ' ' . $last);
        if ($full !== '') {
            return $full;
        }
        $code = $customer->customer_code ?? '';
        return $code !== '' && $code !== null ? (string) $code : '';
    }

    /**
     * Payment mode label; types may be soft-deleted but receipts still reference id.
     */
    protected function dashboardPaymentModeLabel($paymentTypeRelation, int $effectiveModeId): string
    {
        $fromRel = optional($paymentTypeRelation)->payment_name;
        if ($fromRel) {
            return (string) $fromRel;
        }
        $pt = PaymentType::withTrashed()->find($effectiveModeId);
        if ($pt && $pt->payment_name) {
            return (string) $pt->payment_name;
        }
        if ($effectiveModeId === 1) {
            return 'Cash';
        }

        return 'Mode ' . $effectiveModeId;
    }

    public function get_bundle(Request $request)
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
                    'data' => $this->emptyPayload(),
                ], 200);
            }

            $today = Carbon::today()->toDateString();
            $dayOfMonth = (int) date('j');

            $firstDay = Carbon::now()->startOfMonth()->toDateString();
            $lastDay = Carbon::now()->endOfMonth()->toDateString();

            // ---------- KPIs (month + totals) ----------
            $runningGroups = Group::whereIn('branch_id', $branchIds)
                ->where('group_status', 0)
                ->count();
            $vacantGroups = Group::whereIn('branch_id', $branchIds)
                ->where(['group_status' => 0, 'is_filled' => 1])
                ->count();
            $customersCount = Customer::where('tenant_id', $tenantId)
                ->whereIn('branch_id', $branchIds)
                ->where('is_customer', 1)
                ->count();

            $bidPending = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('pending_release');
            $bidReleased = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('customer_released');

            $sumMonthR = $this->scopeReceiptsForDashboard(
                Receipt::whereIn('branch_id', $branchIds)
            )
                ->whereBetween('receipt_date', [$firstDay, $lastDay])
                ->selectRaw('COALESCE(SUM(received_amount),0) as ra, COALESCE(SUM(penalty),0) as pe')
                ->first();
            $monthReceipt = (float) ($sumMonthR->ra ?? 0) + (float) ($sumMonthR->pe ?? 0);

            $monthAdv = (float) AdvanceReceipt::whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('payment_type_id')
                        ->orWhere('payment_type_id', '<>', 9);
                })
                ->whereBetween('receipt_date', [$firstDay, $lastDay])
                ->sum('receipt_amount');

            $monthBefore = (float) BeforeEnrolReceipt::whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereBetween('received_date', [$firstDay, $lastDay])
                ->sum('amount');

            $currentMonthCollection = $monthReceipt + $monthAdv + $monthBefore;

            // Today collections (receipts + advances + before-enrol)
            $sumTodayR = $this->scopeReceiptsForDashboard(
                Receipt::whereIn('branch_id', $branchIds)
            )
                ->whereDate('receipt_date', $today)
                ->selectRaw('COALESCE(SUM(received_amount),0) as ra, COALESCE(SUM(penalty),0) as pe')
                ->first();
            $todayReceipt = (float) ($sumTodayR->ra ?? 0) + (float) ($sumTodayR->pe ?? 0);

            $todayAdv = (float) AdvanceReceipt::whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereDate('receipt_date', $today)
                ->sum('receipt_amount');

            $todayBefore = (float) BeforeEnrolReceipt::whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereDate('received_date', $today)
                ->sum('amount');

            $todayCollectionTotal = $todayReceipt + $todayAdv + $todayBefore;

            // Today payments (bid / chit payments table)
            $todayPaymentTotal = (float) Payment::whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereDate('payment_date', $today)
                ->sum('total_amount');

            // ---------- Bar chart: today's collection by payment mode (receipts only) ----------
            $payModeExpr = 'COALESCE(NULLIF(payment_type_id, 0), 1)';
            $receiptByMode = $this->scopeReceiptsForDashboard(
                Receipt::whereIn('branch_id', $branchIds)
            )
                ->whereDate('receipt_date', $today)
                ->select(
                    DB::raw($payModeExpr . ' as pay_mode_id'),
                    DB::raw('SUM(received_amount + COALESCE(penalty,0)) as amt')
                )
                ->groupBy(DB::raw($payModeExpr))
                ->get();

            $barLabels = [];
            $barData = [];
            foreach ($receiptByMode as $row) {
                $modeId = (int) $row->pay_mode_id;
                $pt = PaymentType::withTrashed()->find($modeId);
                $barLabels[] = $pt && $pt->payment_name
                    ? (string) $pt->payment_name
                    : ($modeId === 1 ? 'Cash' : ('Mode ' . $modeId));
                $barData[] = round((float) $row->amt, 2);
            }
            if (count($barLabels) === 0) {
                $barLabels = [];
                $barData = [];
            }

            // ---------- Pie: bidding released vs pending ----------
            $pieLabels = ['Pending release', 'Released to customers'];
            $pieData = [
                round(max(0, $bidPending), 2),
                round(max(0, $bidReleased), 2),
            ];

            // ---------- Today follow-ups (next or actual follow-up date = today) ----------
            $followups = Followup::with(['lead_management_det', 'branch_det', 'employee_det'])
                ->where('tenant_id', $tenantId)
                ->whereIn('branch_id', $branchIds)
                ->where(function ($q) use ($today) {
                    $q->whereDate('next_followup_date', $today)
                        ->orWhereDate('followup_date', $today);
                })
                ->orderBy('id', 'desc')
                ->limit(150)
                ->get();

            $todayFollowupsList = [];
            foreach ($followups as $f) {
                $lead = $f->lead_management_det;
                $todayFollowupsList[] = [
                    'id' => $f->id,
                    'lead_id' => $f->lead_management_id,
                    'lead_name' => $lead ? $lead->lead_customer_name : '',
                    'mobile' => $lead ? $lead->mobile_no : '',
                    'branch' => optional($f->branch_det)->branch_name,
                    'followup_date' => $f->followup_date,
                    'next_followup_date' => $f->next_followup_date,
                    'remarks' => $f->remarks,
                    'employee' => $f->employee_det
                        ? trim(($f->employee_det->first_name ?? '') . ' ' . ($f->employee_det->last_name ?? ''))
                        : '',
                ];
            }

            // ---------- Today receipts detail ----------
            $receiptRows = $this->scopeReceiptsForDashboard(
                Receipt::whereIn('branch_id', $branchIds)
            )
                ->whereDate('receipt_date', $today)
                ->with(['customer_det', 'payment_type_det', 'enrollment_det.customer_det'])
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();

            $todayCollectionsList = [];
            foreach ($receiptRows as $r) {
                $pid = $r->payment_type_id;
                $effectiveModeId = ($pid === null || (int) $pid === 0) ? 1 : (int) $pid;
                $modeName = $this->dashboardPaymentModeLabel($r->payment_type_det, $effectiveModeId);

                $cust = ($r->customer_id && (int) $r->customer_id > 0)
                    ? $r->customer_det
                    : optional($r->enrollment_det)->customer_det;

                $todayCollectionsList[] = [
                    'receipt_no' => $r->receipt_no,
                    'customer' => $this->dashboardCustomerName($cust),
                    'amount' => round((float) $r->received_amount + (float) $r->penalty, 2),
                    'mode' => $modeName,
                    'receipt_date' => $r->receipt_date,
                ];
            }

            // ---------- Today payments detail ----------
            $payRows = Payment::with(['customer_det', 'payment_type_det', 'group_det', 'enrol_det.customer_det'])
                ->whereIn('branch_id', $branchIds)
                ->where('status', 1)
                ->whereDate('payment_date', $today)
                ->orderBy('id', 'desc')
                ->limit(200)
                ->get();

            $todayPaymentsList = [];
            foreach ($payRows as $p) {
                $pid = $p->payment_type_id;
                $effectivePayMode = ($pid === null || (int) $pid === 0) ? 1 : (int) $pid;
                $payCust = ($p->customer_id && (int) $p->customer_id > 0)
                    ? $p->customer_det
                    : optional($p->enrol_det)->customer_det;

                $todayPaymentsList[] = [
                    'id' => $p->id,
                    'customer' => $this->dashboardCustomerName($payCust),
                    'group' => optional($p->group_det)->group_name,
                    'amount' => round((float) $p->total_amount, 2),
                    'mode' => $this->dashboardPaymentModeLabel($p->payment_type_det, $effectivePayMode),
                    'payment_date' => $p->payment_date,
                ];
            }

            // ---------- Today's auctions (same rule as legacy: auction_date = calendar day) ----------
            $auctionGroups = Group::with(['branch_det', 'scheme_det'])
                ->whereIn('branch_id', $branchIds)
                ->where('tenant_id', $tenantId)
                ->where(['group_status' => 0, 'is_filled' => 0])
                ->where('auction_date', $dayOfMonth)
                ->orderBy('branch_id')
                ->orderBy('group_name')
                ->limit(200)
                ->get();

            $todayAuctions = [];
            foreach ($auctionGroups as $g) {
                $maxInst = BiddingDetail::where('group_id', $g->id)->max('installment_no');
                $todayAuctions[] = [
                    'group_id' => $g->id,
                    'group_name' => $g->group_name,
                    'branch' => optional($g->branch_det)->branch_name,
                    'chit_value' => optional(optional($g->scheme_det))->chit_value,
                    'auction_time' => $g->auction_time,
                    'next_installment' => $maxInst ? ((int) $maxInst + 1) : 1,
                ];
            }

            // ---------- Outstanding: top customers by pending_release ----------
            $outstandingRows = BiddingDetail::select('customer_id', DB::raw('SUM(pending_release) as pending'))
                ->whereIn('branch_id', $branchIds)
                ->where('tenant_id', $tenantId)
                ->groupBy('customer_id')
                ->having('pending', '>', 0)
                ->orderByDesc('pending')
                ->limit(25)
                ->get();

            $outstandingList = [];
            foreach ($outstandingRows as $row) {
                $cust = Customer::find($row->customer_id);
                $outstandingList[] = [
                    'customer_id' => $row->customer_id,
                    'customer_name' => $this->dashboardCustomerName($cust),
                    'mobile' => $cust ? $cust->mobile_no : '',
                    'pending_amount' => round((float) $row->pending, 2),
                ];
            }

            $payload = [
                'as_of' => $today,
                'summary' => [
                    'running_groups' => $runningGroups,
                    'vacant_groups' => $vacantGroups,
                    'customers' => $customersCount,
                    'current_month_collection' => round($currentMonthCollection, 2),
                    'total_bid_pending' => round($bidPending, 2),
                    'total_bid_released' => round($bidReleased, 2),
                    'today_collection_total' => round($todayCollectionTotal, 2),
                    'today_payment_total' => round($todayPaymentTotal, 2),
                    'today_followup_count' => count($todayFollowupsList),
                ],
                'charts' => [
                    'collection_by_mode' => [
                        'labels' => $barLabels,
                        'data' => $barData,
                    ],
                    'bidding_release_mix' => [
                        'labels' => $pieLabels,
                        'data' => $pieData,
                    ],
                ],
                'today_auctions' => $todayAuctions,
                'outstanding_customers' => $outstandingList,
                'today_followups' => $todayFollowupsList,
                'today_collections' => $todayCollectionsList,
                'today_payments' => $todayPaymentsList,
            ];

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $payload,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to load dashboard',
            ], 401);
        }
    }

    protected function emptyPayload(): array
    {
        return [
            'as_of' => Carbon::today()->toDateString(),
            'summary' => [
                'running_groups' => 0,
                'vacant_groups' => 0,
                'customers' => 0,
                'current_month_collection' => 0,
                'total_bid_pending' => 0,
                'total_bid_released' => 0,
                'today_collection_total' => 0,
                'today_payment_total' => 0,
                'today_followup_count' => 0,
            ],
            'charts' => [
                'collection_by_mode' => ['labels' => [], 'data' => []],
                'bidding_release_mix' => ['labels' => ['Pending', 'Released'], 'data' => [0, 0]],
            ],
            'today_auctions' => [],
            'outstanding_customers' => [],
            'today_followups' => [],
            'today_collections' => [],
            'today_payments' => [],
        ];
    }
}
