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
use App\Models\CashSettlement;
use App\Models\Employee;
use Carbon\Carbon;
use App\Services\UserLoginScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            $runningGroups = 0;
            $vacantGroups = 0;
            $customersCount = 0;
            $bidPending = 0.0;
            $bidReleased = 0.0;
            try {
                $runningGroups = Group::whereIn('branch_id', $branchIds)
                    ->where('group_status', 0)
                    ->count();
                $vacantGroups = Group::whereIn('branch_id', $branchIds)
                    ->where(['group_status' => 0, 'is_filled' => 1])
                    ->count();
            } catch (\Exception $gEx) {
                Log::warning('Dashboard groups KPI failed: ' . $gEx->getMessage());
            }
            try {
                $customersCount = Customer::where('tenant_id', $tenantId)
                    ->whereIn('branch_id', $branchIds)
                    ->where('is_customer', 1)
                    ->count();
            } catch (\Exception $cEx) {
                // Fallback if is_customer column missing on older DBs
                try {
                    $customersCount = Customer::where('tenant_id', $tenantId)
                        ->whereIn('branch_id', $branchIds)
                        ->count();
                } catch (\Exception $cEx2) {
                    Log::warning('Dashboard customers KPI failed: ' . $cEx2->getMessage());
                }
            }
            try {
                $bidPending = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('pending_release');
                $bidReleased = (float) BiddingDetail::whereIn('branch_id', $branchIds)->sum('customer_released');
            } catch (\Exception $bEx) {
                Log::warning('Dashboard bid KPI failed: ' . $bEx->getMessage());
            }

            $monthReceipt = 0.0;
            $monthAdv = 0.0;
            $monthBefore = 0.0;
            try {
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
            } catch (\Exception $mEx) {
                Log::warning('Dashboard month collection failed: ' . $mEx->getMessage());
            }
            $currentMonthCollection = $monthReceipt + $monthAdv + $monthBefore;

            // Today collections (receipts + advances + before-enrol)
            $todayReceipt = 0.0;
            $todayAdv = 0.0;
            $todayBefore = 0.0;
            $todayPaymentTotal = 0.0;
            try {
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

                $todayPaymentTotal = (float) Payment::whereIn('branch_id', $branchIds)
                    ->where('status', 1)
                    ->whereDate('payment_date', $today)
                    ->sum('total_amount');
            } catch (\Exception $tEx) {
                Log::warning('Dashboard today totals failed: ' . $tEx->getMessage());
            }
            $todayCollectionTotal = $todayReceipt + $todayAdv + $todayBefore;

            // ---------- Bar chart: collection by payment mode (month-to-date; fall back to today) ----------
            // CASE is safer than NULLIF across MySQL/MariaDB SQL modes
            $payModeExpr = 'CASE WHEN payment_type_id IS NULL OR payment_type_id = 0 THEN 1 ELSE payment_type_id END';
            $barLabels = [];
            $barData = [];
            $barScope = 'month';
            try {
                $buildModeBars = function (callable $applyDate) use ($branchIds, $payModeExpr) {
                    $query = $this->scopeReceiptsForDashboard(
                        Receipt::whereIn('branch_id', $branchIds)
                    );
                    $applyDate($query);
                    $receiptByMode = $query
                        ->select(
                            DB::raw($payModeExpr . ' as pay_mode_id'),
                            DB::raw('SUM(COALESCE(received_amount,0) + COALESCE(penalty,0)) as amt')
                        )
                        ->groupBy(DB::raw($payModeExpr))
                        ->get();

                    $labels = [];
                    $data = [];
                    foreach ($receiptByMode as $row) {
                        $modeId = (int) $row->pay_mode_id;
                        $pt = PaymentType::withTrashed()->find($modeId);
                        $labels[] = $pt && $pt->payment_name
                            ? (string) $pt->payment_name
                            : ($modeId === 1 ? 'Cash' : ('Mode ' . $modeId));
                        $data[] = round((float) $row->amt, 2);
                    }

                    return [$labels, $data];
                };

                list($barLabels, $barData) = $buildModeBars(function ($q) use ($firstDay, $lastDay) {
                    $q->whereBetween('receipt_date', [$firstDay, $lastDay]);
                });
                $hasBarAmount = false;
                foreach ($barData as $v) {
                    if ((float) $v > 0) {
                        $hasBarAmount = true;
                        break;
                    }
                }
                if (!$hasBarAmount) {
                    list($barLabels, $barData) = $buildModeBars(function ($q) use ($today) {
                        $q->whereDate('receipt_date', $today);
                    });
                    $barScope = 'today';
                }
            } catch (\Exception $chartEx) {
                Log::warning('Dashboard mode chart failed: ' . $chartEx->getMessage());
                $barLabels = [];
                $barData = [];
                $barScope = 'month';
            }

            // ---------- Pie: bidding released vs pending ----------
            $pieLabels = ['Pending release', 'Released to customers'];
            $pieData = [
                round(max(0, $bidPending), 2),
                round(max(0, $bidReleased), 2),
            ];

            // ---------- Today follow-ups (next or actual follow-up date = today) ----------
            $todayFollowupsList = [];
            try {
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
            } catch (\Exception $fuEx) {
                Log::warning('Dashboard followups failed: ' . $fuEx->getMessage());
            }

            // ---------- Today receipts detail ----------
            $todayCollectionsList = [];
            try {
                $receiptRows = $this->scopeReceiptsForDashboard(
                    Receipt::whereIn('branch_id', $branchIds)
                )
                    ->whereDate('receipt_date', $today)
                    ->with(['customer_det', 'payment_type_det', 'enrollment_det.customer_det'])
                    ->orderBy('id', 'desc')
                    ->limit(200)
                    ->get();

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
            } catch (\Exception $recEx) {
                Log::warning('Dashboard today collections failed: ' . $recEx->getMessage());
            }

            // ---------- Today payments detail ----------
            $todayPaymentsList = [];
            try {
                $payRows = Payment::with(['customer_det', 'payment_type_det', 'group_det', 'enrol_det.customer_det'])
                    ->whereIn('branch_id', $branchIds)
                    ->where('status', 1)
                    ->whereDate('payment_date', $today)
                    ->orderBy('id', 'desc')
                    ->limit(200)
                    ->get();

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
            } catch (\Exception $payEx) {
                Log::warning('Dashboard today payments failed: ' . $payEx->getMessage());
            }

            // ---------- Today's auctions (same rule as legacy: auction_date = calendar day) ----------
            $todayAuctions = [];
            try {
                $auctionGroups = Group::with(['branch_det', 'scheme_det'])
                    ->whereIn('branch_id', $branchIds)
                    ->where('tenant_id', $tenantId)
                    ->where(['group_status' => 0, 'is_filled' => 0])
                    ->where('auction_date', $dayOfMonth)
                    ->orderBy('branch_id')
                    ->orderBy('group_name')
                    ->limit(200)
                    ->get();

                foreach ($auctionGroups as $g) {
                    $maxInst = 0;
                    try {
                        $maxInst = BiddingDetail::where('group_id', $g->id)->max('installment_no');
                    } catch (\Exception $ignored) {
                    }
                    $todayAuctions[] = [
                        'group_id' => $g->id,
                        'group_name' => $g->group_name,
                        'branch' => optional($g->branch_det)->branch_name,
                        'chit_value' => optional(optional($g->scheme_det))->chit_value,
                        'auction_time' => $g->auction_time,
                        'next_installment' => $maxInst ? ((int) $maxInst + 1) : 1,
                    ];
                }
            } catch (\Exception $aucEx) {
                Log::warning('Dashboard auctions failed: ' . $aucEx->getMessage());
            }

            // ---------- Outstanding: top customers by pending_release ----------
            $outstandingList = [];
            try {
                $outstandingRows = BiddingDetail::select('customer_id', DB::raw('SUM(pending_release) as pending'))
                    ->whereIn('branch_id', $branchIds)
                    ->where('tenant_id', $tenantId)
                    ->groupBy('customer_id')
                    ->havingRaw('SUM(pending_release) > 0')
                    ->orderByDesc(DB::raw('SUM(pending_release)'))
                    ->limit(25)
                    ->get();

                foreach ($outstandingRows as $row) {
                    $cust = Customer::find($row->customer_id);
                    $outstandingList[] = [
                        'customer_id' => $row->customer_id,
                        'customer_name' => $this->dashboardCustomerName($cust),
                        'mobile' => $cust ? $cust->mobile_no : '',
                        'pending_amount' => round((float) $row->pending, 2),
                    ];
                }
            } catch (\Exception $outEx) {
                Log::warning('Dashboard outstanding failed: ' . $outEx->getMessage());
            }

            // ---------- Branch-wise collection (month) for ranking / map list ----------
            $branchCollections = [];
            try {
                $branchAmt = [];
                $receiptBranch = $this->scopeReceiptsForDashboard(
                    Receipt::whereIn('branch_id', $branchIds)
                )
                    ->whereBetween('receipt_date', [$firstDay, $lastDay])
                    ->select('branch_id', DB::raw('SUM(COALESCE(received_amount,0) + COALESCE(penalty,0)) as amt'))
                    ->groupBy('branch_id')
                    ->get();
                foreach ($receiptBranch as $row) {
                    $bid = (int) $row->branch_id;
                    $branchAmt[$bid] = ($branchAmt[$bid] ?? 0) + (float) $row->amt;
                }

                $advBranch = AdvanceReceipt::whereIn('branch_id', $branchIds)
                    ->where('status', 1)
                    ->whereBetween('receipt_date', [$firstDay, $lastDay])
                    ->select('branch_id', DB::raw('SUM(COALESCE(receipt_amount,0)) as amt'))
                    ->groupBy('branch_id')
                    ->get();
                foreach ($advBranch as $row) {
                    $bid = (int) $row->branch_id;
                    $branchAmt[$bid] = ($branchAmt[$bid] ?? 0) + (float) $row->amt;
                }

                $befBranch = BeforeEnrolReceipt::whereIn('branch_id', $branchIds)
                    ->where('status', 1)
                    ->whereBetween('received_date', [$firstDay, $lastDay])
                    ->select('branch_id', DB::raw('SUM(COALESCE(amount,0)) as amt'))
                    ->groupBy('branch_id')
                    ->get();
                foreach ($befBranch as $row) {
                    $bid = (int) $row->branch_id;
                    $branchAmt[$bid] = ($branchAmt[$bid] ?? 0) + (float) $row->amt;
                }

                arsort($branchAmt);
                $maxAmt = 0;
                foreach ($branchAmt as $amt) {
                    if ($amt > $maxAmt) {
                        $maxAmt = $amt;
                    }
                }
                $rank = 1;
                foreach ($branchAmt as $bid => $amt) {
                    if ($amt <= 0) {
                        continue;
                    }
                    $br = Branch::find($bid);
                    $branchCollections[] = [
                        'rank' => $rank++,
                        'branch_id' => $bid,
                        'branch_name' => $br ? $br->branch_name : ('Branch ' . $bid),
                        'amount' => round((float) $amt, 2),
                        'pct' => $maxAmt > 0 ? (int) round(($amt / $maxAmt) * 100) : 0,
                    ];
                    if (count($branchCollections) >= 15) {
                        break;
                    }
                }
            } catch (\Exception $brEx) {
                Log::warning('Dashboard branch collections failed: ' . $brEx->getMessage());
            }

            // ---------- Cash settlements (Settlement Report snapshot) ----------
            $settlementPendingCount = 0;
            $settlementApprovedCount = 0;
            $settlementTodayAmount = 0.0;
            $settlementMonthAmount = 0.0;
            $recentSettlements = [];
            try {
                $dateCol = Schema::hasColumn('cash_settlements', 'settlement_date')
                    ? 'settlement_date'
                    : 'date';
                $hasApproval = Schema::hasColumn('cash_settlements', 'is_approved');

                $settleBase = function () use ($branchIds, $tenantId) {
                    return CashSettlement::whereIn('branch_id', $branchIds)
                        ->where('tenant_id', $tenantId);
                };

                $settlementTodayAmount = (float) $settleBase()
                    ->whereDate($dateCol, $today)
                    ->sum('received_amount');
                $settlementMonthAmount = (float) $settleBase()
                    ->whereBetween($dateCol, [$firstDay, $lastDay])
                    ->sum('received_amount');

                if ($hasApproval) {
                    $settlementPendingCount = (int) $settleBase()
                        ->where(function ($q) {
                            $q->whereNull('is_approved')->orWhere('is_approved', 0);
                        })
                        ->count();
                    $settlementApprovedCount = (int) $settleBase()
                        ->where('is_approved', 1)
                        ->whereBetween($dateCol, [$firstDay, $lastDay])
                        ->count();
                } else {
                    $settlementPendingCount = 0;
                    $settlementApprovedCount = (int) $settleBase()
                        ->whereBetween($dateCol, [$firstDay, $lastDay])
                        ->count();
                }

                $rows = $settleBase()
                    ->orderBy($dateCol, 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get();

                foreach ($rows as $row) {
                    $emp = Employee::find($row->employee_id);
                    $empName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
                    $br = Branch::find($row->branch_id);
                    $sDate = $row->{$dateCol} ?? null;
                    $isApproved = $hasApproval ? (int) ($row->is_approved ?? 0) : 0;
                    $recentSettlements[] = [
                        'id' => $row->id,
                        'branch' => $br ? $br->branch_name : '',
                        'employee' => $empName,
                        'amount' => round((float) $row->received_amount, 2),
                        'settlement_date' => ($sDate && $sDate != '0000-00-00')
                            ? date('d-m-Y', strtotime($sDate))
                            : '',
                        'approval_status' => $isApproved === 1 ? 'Approved' : 'Pending',
                        'is_approved' => $isApproved,
                        'mode' => $row->settlement_mode ?? '',
                    ];
                }
            } catch (\Exception $sEx) {
                Log::warning('Dashboard settlements failed: ' . $sEx->getMessage());
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
                    'settlement_pending_count' => $settlementPendingCount,
                    'settlement_approved_count' => $settlementApprovedCount,
                    'settlement_today_amount' => round($settlementTodayAmount, 2),
                    'settlement_month_amount' => round($settlementMonthAmount, 2),
                ],
                'charts' => [
                    'collection_by_mode' => [
                        'labels' => $barLabels,
                        'data' => $barData,
                        'scope' => $barScope,
                    ],
                    'bidding_release_mix' => [
                        'labels' => $pieLabels,
                        'data' => $pieData,
                    ],
                ],
                'branch_collections' => $branchCollections,
                'today_auctions' => $todayAuctions,
                'outstanding_customers' => $outstandingList,
                'today_followups' => $todayFollowupsList,
                'today_collections' => $todayCollectionsList,
                'today_payments' => $todayPaymentsList,
                'recent_settlements' => $recentSettlements,
            ];

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $payload,
            ], 200);
        } catch (\Exception $e) {
            Log::error('get-chit-dashboard-bundle failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to load dashboard',
                'error' => $e->getMessage(),
            ], 200);
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
                'settlement_pending_count' => 0,
                'settlement_approved_count' => 0,
                'settlement_today_amount' => 0,
                'settlement_month_amount' => 0,
            ],
            'charts' => [
                'collection_by_mode' => ['labels' => [], 'data' => [], 'scope' => 'month'],
                'bidding_release_mix' => [
                    'labels' => ['Pending release', 'Released to customers'],
                    'data' => [0, 0],
                ],
            ],
            'branch_collections' => [],
            'today_auctions' => [],
            'outstanding_customers' => [],
            'today_followups' => [],
            'today_collections' => [],
            'today_payments' => [],
            'recent_settlements' => [],
        ];
    }
}
