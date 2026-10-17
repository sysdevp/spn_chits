<?php

namespace App\Http\Controllers;

use App\Models\AdvanceReceipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\BiddingDetail;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\EmployeeTarget;
use App\Models\Followup;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\Receipt;
use App\Models\CashSettlement;
use App\Models\Employee;
use App\Models\ChitDetail;
use App\Models\LeadManagement;
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
                        'target_amount' => 0,
                        'target_pct' => 0,
                        'pct' => $maxAmt > 0 ? (int) round(($amt / $maxAmt) * 100) : 0,
                    ];
                    if (count($branchCollections) >= 15) {
                        break;
                    }
                }
            } catch (\Exception $brEx) {
                Log::warning('Dashboard branch collections failed: ' . $brEx->getMessage());
            }

            // Active employee targets for selected branch scope (independent of collection queries)
            $branchTargetMap = [];
            $monthlyTargetTotal = 0;
            $monthlyTargetAchieved = 0;
            $salesTargetTotal = 0.0;
            $salesTargetAchieved = 0.0;
            $collectionTargetTotal = 0.0;
            $collectionTargetAchieved = 0.0;
            try {
                if (Schema::hasTable('employee_targets') && !empty($branchIds)) {
                    $empIdsInBranch = Employee::whereIn('branch_id', $branchIds)->pluck('id')->all();

                    $baseTargetQuery = function () use ($branchIds, $empIdsInBranch, $tenantId) {
                        $q = EmployeeTarget::where('status', 1)
                            ->where('tenant_id', $tenantId)
                            ->where(function ($inner) use ($branchIds, $empIdsInBranch) {
                                $inner->whereIn('branch_id', $branchIds)
                                    ->orWhere('branch_id', 0)
                                    ->orWhereNull('branch_id');
                                if (!empty($empIdsInBranch)) {
                                    $inner->orWhereIn('employee_id', $empIdsInBranch);
                                }
                            });
                        return $q;
                    };

                    // Prefer monthly targets overlapping the current calendar month
                    $targetRows = $baseTargetQuery()
                        ->where('period_type', 'monthly')
                        ->whereDate('period_start', '<=', $lastDay)
                        ->whereDate('period_end', '>=', $firstDay)
                        ->get();

                    // Fallback: any active target overlapping today (daily/weekly/monthly)
                    if ($targetRows->isEmpty()) {
                        $targetRows = $baseTargetQuery()
                            ->whereDate('period_start', '<=', $today)
                            ->whereDate('period_end', '>=', $today)
                            ->get();
                    }

                    $seenIds = [];
                    $hasTargetType = Schema::hasColumn('employee_targets', 'target_type');
                    foreach ($targetRows as $tRow) {
                        if (isset($seenIds[$tRow->id])) {
                            continue;
                        }
                        $seenIds[$tRow->id] = true;

                        $tBid = (int) ($tRow->branch_id ?: 0);
                        if ($tBid > 0 && !in_array($tBid, $branchIds, true)) {
                            // Still allow via employee branch membership
                            $emp = Employee::find($tRow->employee_id);
                            $empBid = $emp ? (int) ($emp->branch_id ?: 0) : 0;
                            if ($empBid <= 0 || !in_array($empBid, $branchIds, true)) {
                                continue;
                            }
                            $tBid = $empBid;
                        }

                        $amt = (float) ($tRow->target_amount ?: 0);
                        $ach = (float) ($tRow->achieved_amount ?: 0);
                        $monthlyTargetTotal += $amt;
                        $monthlyTargetAchieved += $ach;

                        $tType = $hasTargetType ? strtolower((string) ($tRow->target_type ?? '')) : 'existing_client_collection';
                        if (in_array($tType, ['new_business', 'sales'], true)) {
                            $salesTargetTotal += $amt;
                            $salesTargetAchieved += $ach;
                        } elseif (in_array($tType, ['existing_client_collection', 'new_client_collection', 'collection'], true) || $tType === '') {
                            $collectionTargetTotal += $amt;
                            $collectionTargetAchieved += $ach;
                            if ($tBid > 0) {
                                if (!isset($branchTargetMap[$tBid])) {
                                    $branchTargetMap[$tBid] = ['target' => 0, 'achieved' => 0];
                                }
                                $branchTargetMap[$tBid]['target'] += $amt;
                                $branchTargetMap[$tBid]['achieved'] += $ach;
                            }
                        }
                    }

                    // Enrich branch collection rows with target amounts when available
                    if (!empty($branchCollections) && !empty($branchTargetMap)) {
                        foreach ($branchCollections as &$bcRow) {
                            $bid = (int) ($bcRow['branch_id'] ?? 0);
                            $tAmt = isset($branchTargetMap[$bid]) ? (float) $branchTargetMap[$bid]['target'] : (float) ($bcRow['target_amount'] ?? 0);
                            $bcRow['target_amount'] = round($tAmt, 2);
                            $bcAmt = (float) ($bcRow['amount'] ?? 0);
                            $bcRow['target_pct'] = $tAmt > 0 ? (int) round(($bcAmt / $tAmt) * 100) : 0;
                        }
                        unset($bcRow);
                    }
                }
            } catch (\Exception $tEx) {
                Log::warning('Dashboard targets failed: ' . $tEx->getMessage());
                $monthlyTargetTotal = 0;
                $monthlyTargetAchieved = 0;
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

            // Today enrolled customers + month joining + today leads + live tracking + today tasks
            $todayEnrolledCustomers = 0;
            $monthEnrolledCustomers = 0;
            $todayLeads = 0;
            $liveEmployeeCount = 0;
            $todayTasksCount = 0;
            $todayTasksList = [];
            try {
                $todayEnrolledCustomers = (int) ChitDetail::whereIn('branch_id', $branchIds)
                    ->whereDate('created_at', $today)
                    ->distinct('customer_id')
                    ->count('customer_id');
            } catch (\Exception $e) {
                try {
                    $todayEnrolledCustomers = (int) DB::table('chit_details')
                        ->whereIn('branch_id', $branchIds)
                        ->whereDate('created_at', $today)
                        ->distinct()
                        ->count('customer_id');
                } catch (\Exception $e2) {
                    $todayEnrolledCustomers = 0;
                }
            }
            try {
                $monthEnrolledCustomers = (int) ChitDetail::whereIn('branch_id', $branchIds)
                    ->whereBetween(DB::raw('DATE(created_at)'), [$firstDay, $lastDay])
                    ->distinct('customer_id')
                    ->count('customer_id');
            } catch (\Exception $e) {
                try {
                    $monthEnrolledCustomers = (int) DB::table('chit_details')
                        ->whereIn('branch_id', $branchIds)
                        ->whereBetween(DB::raw('DATE(created_at)'), [$firstDay, $lastDay])
                        ->distinct()
                        ->count('customer_id');
                } catch (\Exception $e2) {
                    $monthEnrolledCustomers = 0;
                }
            }
            try {
                $todayLeads = (int) LeadManagement::where('tenant_id', $tenantId)
                    ->whereIn('branch_id', $branchIds)
                    ->whereDate('created_at', $today)
                    ->count();
            } catch (\Exception $e) {
                $todayLeads = 0;
            }
            try {
                if (Schema::hasTable('employee_tasks')) {
                    $taskBase = function () use ($tenantId, $branchIds, $today) {
                        $q = \App\Models\EmployeeTask::where('tenant_id', $tenantId)
                            ->whereDate('task_date', $today);
                        if (!empty($branchIds)) {
                            $q->where(function ($inner) use ($branchIds) {
                                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id')->orWhere('branch_id', 0);
                            });
                        }
                        return $q;
                    };
                    $todayTasksCount = (int) $taskBase()->count();
                    $todayTasksList = $taskBase()->orderBy('id', 'desc')->limit(15)->get()->map(function ($t) {
                        $emp = \App\Models\Employee::find($t->employee_id);
                        return [
                            'id' => (int) $t->id,
                            'title' => $t->title ?? 'Task',
                            'employee' => $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '',
                            'status' => $t->task_status ?? '',
                            'task_date' => $t->task_date ? date('d-m-Y', strtotime($t->task_date)) : '',
                        ];
                    })->values()->all();
                }
            } catch (\Exception $e) {
                $todayTasksCount = 0;
                $todayTasksList = [];
            }
            try {
                if (Schema::hasTable('employee_live_locations')) {
                    $liveQ = DB::table('employee_live_locations')
                        ->whereNotNull('latitude')
                        ->where('latitude', '!=', '')
                        ->whereNotNull('longitude')
                        ->where('longitude', '!=', '')
                        ->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                        ->where('tenant_id', $tenantId);
                    if (!empty($branchIds)) {
                        $liveQ->whereIn('branch_id', $branchIds);
                    }
                    $liveEmployeeCount = (int) $liveQ->distinct('employee_id')->count('employee_id');
                }
            } catch (\Exception $e) {
                $liveEmployeeCount = 0;
            }

            // ---------- Attendance (present / absent / leave) ----------
            $activeEmployees = 0;
            $presentToday = 0;
            $leaveToday = 0;
            try {
                $activeEmployees = (int) Employee::whereIn('branch_id', $branchIds)->where('status', 1)->count();
                if (Schema::hasTable('employee_attendances')) {
                    $attQ = DB::table('employee_attendances')->whereIn('branch_id', $branchIds)->whereDate('att_date', $today);
                    if (Schema::hasColumn('employee_attendances', 'check_in_time')) {
                        $attQ->where(function ($q) {
                            $q->whereNotNull('check_in_time')->orWhere('attendance_type', 1);
                        });
                    }
                    $presentToday = (int) $attQ->distinct()->count('employee_id');
                }
                if (Schema::hasTable('employee_leave_requests') && Schema::hasColumn('employee_leave_requests', 'request_status')) {
                    $leaveToday = (int) DB::table('employee_leave_requests')->whereIn('branch_id', $branchIds)
                        ->where('request_status', 'approved')
                        ->whereDate('from_date', '<=', $today)->whereDate('to_date', '>=', $today)
                        ->distinct()->count('employee_id');
                }
            } catch (\Exception $e) {
                Log::warning('Dashboard attendance failed: ' . $e->getMessage());
            }
            $absentToday = max(0, $activeEmployees - $presentToday - $leaveToday);

            // ---------- Today's petty-cash expenses ----------
            $todayExpenseTotal = 0.0;
            $todayExpenseRows = [];
            try {
                if (Schema::hasTable('petty_cash_expenses')) {
                    $expQ = DB::table('petty_cash_expenses as pe')
                        ->leftJoin('petty_cash_expense_types as pt', 'pt.id', '=', 'pe.expenses_type_id')
                        ->whereIn('pe.branch_id', $branchIds)
                        ->whereDate('pe.expenses_date', $today)
                        ->where('pe.status', '!=', 3);
                    if (Schema::hasColumn('petty_cash_expenses', 'deleted_at')) {
                        $expQ->whereNull('pe.deleted_at');
                    }
                    $todayExpenseTotal = (float) (clone $expQ)->sum('pe.expenses_amount');
                    $todayExpenseRows = (clone $expQ)->groupBy('pe.expenses_type_id')
                        ->select(DB::raw("MAX(pt.expenses_type) as label"), DB::raw('SUM(pe.expenses_amount) as amount'))
                        ->orderBy('amount', 'desc')->limit(4)->get()
                        ->map(function ($r) {
                            return ['label' => $r->label ?: 'Other', 'amount' => round((float) $r->amount, 2)];
                        })->toArray();
                }
            } catch (\Exception $e) {
                Log::warning('Dashboard expenses failed: ' . $e->getMessage());
            }

            // ---------- Top employees by this month's receipts ----------
            $topEmployees = [];
            try {
                $empRows = DB::table('receipts as r')
                    ->leftJoin('employees as e', 'e.id', '=', 'r.employee_id')
                    ->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')
                    ->whereIn('r.branch_id', $branchIds)
                    ->where('r.status', 1)
                    ->where('r.employee_id', '>', 0)
                    ->whereBetween('r.receipt_date', [$firstDay, $lastDay])
                    ->groupBy('r.employee_id')
                    ->select('r.employee_id', DB::raw("MAX(TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,'')))) as name"),
                        DB::raw('MAX(b.branch_name) as branch'), DB::raw('SUM(COALESCE(r.received_amount,0) + COALESCE(r.penalty,0)) as amount'),
                        DB::raw('COUNT(*) as receipts'))
                    ->orderBy('amount', 'desc')->limit(3)->get();
                $maxEmp = $empRows->max('amount') ?: 1;
                foreach ($empRows as $er) {
                    $topEmployees[] = [
                        'employee_id' => (int) $er->employee_id,
                        'name' => $er->name ?: ('Employee ' . $er->employee_id),
                        'branch' => $er->branch ?: '',
                        'amount' => round((float) $er->amount, 2),
                        'receipts' => (int) $er->receipts,
                        'pct' => (int) round(((float) $er->amount / $maxEmp) * 100),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Dashboard top employees failed: ' . $e->getMessage());
            }

            // ---------- Employee birthdays ----------
            $empBirthdayToday = [];
            $empBirthdayMonth = 0;
            try {
                $bq = Employee::whereIn('branch_id', $branchIds)->where('status', 1)->whereNotNull('dob');
                $empBirthdayMonth = (int) (clone $bq)->whereRaw('MONTH(dob) = ?', [date('n')])->count();
                $empBirthdayToday = (clone $bq)->whereRaw("DATE_FORMAT(dob, '%m-%d') = ?", [date('m-d')])->limit(10)->get()
                    ->map(function ($e) {
                        return ['name' => trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? '')), 'mobile' => $e->mobile_no ?? ''];
                    })->toArray();
            } catch (\Exception $e) {
                Log::warning('Dashboard birthdays failed: ' . $e->getMessage());
            }

            // ---------- This month's unpaid dues and old overdue ----------
            $monthPendingAmount = 0.0;
            $monthPendingCount = 0;
            $overdueAmount = 0.0;
            $overdueCount = 0;
            try {
                $paidSub = DB::table('receipts')->where('status', 1)->where('receipt_date', '>=', $firstDay)
                    ->groupBy('enrollment_id')->select('enrollment_id', DB::raw('SUM(COALESCE(received_amount,0)) as paid'));
                $dueQ = DB::table('chit_details as cd')
                    ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
                    ->leftJoinSub($paidSub, 'pm', 'pm.enrollment_id', '=', 'cd.id')
                    ->whereIn('cd.branch_id', $branchIds)
                    ->where('cd.status', 1)
                    ->whereRaw('COALESCE(s.monthly_due,0) > COALESCE(pm.paid,0)');
                if (Schema::hasColumn('chit_details', 'deleted_at')) {
                    $dueQ->whereNull('cd.deleted_at');
                }
                $monthPendingCount = (int) (clone $dueQ)->count();
                $monthPendingAmount = (float) (clone $dueQ)->sum(DB::raw('COALESCE(s.monthly_due,0) - COALESCE(pm.paid,0)'));

                if (Schema::hasColumn('chit_details', 'pending_amount')) {
                    $odQ = DB::table('chit_details')->whereIn('branch_id', $branchIds)->where('status', 1)->where('pending_amount', '>', 0);
                    if (Schema::hasColumn('chit_details', 'deleted_at')) {
                        $odQ->whereNull('deleted_at');
                    }
                    $overdueCount = (int) (clone $odQ)->count();
                    $overdueAmount = (float) (clone $odQ)->sum('pending_amount');
                }
            } catch (\Exception $e) {
                Log::warning('Dashboard pending/overdue failed: ' . $e->getMessage());
            }

            $salesTargetPct = ($salesTargetTotal > 0)
                ? (int) round(($salesTargetAchieved / $salesTargetTotal) * 100)
                : 0;
            $collectionTargetPct = ($collectionTargetTotal > 0)
                ? (int) round((
                    ($collectionTargetAchieved > 0 ? $collectionTargetAchieved : $currentMonthCollection)
                    / $collectionTargetTotal
                ) * 100)
                : 0;

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
                    'today_enrolled_customers' => $todayEnrolledCustomers,
                    'month_enrolled_customers' => $monthEnrolledCustomers,
                    'today_leads' => $todayLeads,
                    'today_tasks_count' => $todayTasksCount,
                    'live_employee_count' => $liveEmployeeCount,
                    'settlement_pending_count' => $settlementPendingCount,
                    'settlement_approved_count' => $settlementApprovedCount,
                    'settlement_today_amount' => round($settlementTodayAmount, 2),
                    'settlement_month_amount' => round($settlementMonthAmount, 2),
                    'monthly_target' => round($salesTargetTotal > 0 ? $salesTargetTotal : ($monthlyTargetTotal ?? 0), 2),
                    'monthly_target_achieved' => round(
                        $salesTargetTotal > 0
                            ? $salesTargetAchieved
                            : ((($monthlyTargetAchieved ?? 0) > 0) ? ($monthlyTargetAchieved ?? 0) : ($currentMonthCollection ?? 0)),
                        2
                    ),
                    'monthly_target_percent' => $salesTargetTotal > 0
                        ? $salesTargetPct
                        : ((($monthlyTargetTotal ?? 0) > 0)
                            ? (int) round(
                                (
                                    (($monthlyTargetAchieved ?? 0) > 0 ? ($monthlyTargetAchieved ?? 0) : ($currentMonthCollection ?? 0))
                                    / $monthlyTargetTotal
                                ) * 100
                            )
                            : 0),
                    'sales_target' => round($salesTargetTotal, 2),
                    'sales_target_achieved' => round($salesTargetAchieved, 2),
                    'sales_target_percent' => $salesTargetPct,
                    'collection_target' => round($collectionTargetTotal, 2),
                    'collection_target_achieved' => round(
                        $collectionTargetAchieved > 0 ? $collectionTargetAchieved : ($currentMonthCollection ?? 0),
                        2
                    ),
                    'collection_target_percent' => $collectionTargetPct,
                    'active_employees' => $activeEmployees,
                    'present_today' => $presentToday,
                    'absent_today' => $absentToday,
                    'leave_today' => $leaveToday,
                    'today_expense_total' => round($todayExpenseTotal, 2),
                    'employee_birthday_today' => count($empBirthdayToday),
                    'employee_birthday_month' => $empBirthdayMonth,
                    'month_pending_amount' => round($monthPendingAmount, 2),
                    'month_pending_count' => $monthPendingCount,
                    'overdue_amount' => round($overdueAmount, 2),
                    'overdue_count' => $overdueCount,
                ],
                'today_expenses' => $todayExpenseRows,
                'top_employees' => $topEmployees,
                'employee_birthdays_today' => $empBirthdayToday,
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
                'today_tasks' => $todayTasksList,
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
                'today_enrolled_customers' => 0,
                'today_leads' => 0,
                'live_employee_count' => 0,
                'settlement_pending_count' => 0,
                'settlement_approved_count' => 0,
                'settlement_today_amount' => 0,
                'settlement_month_amount' => 0,
                'monthly_target' => 0,
                'monthly_target_achieved' => 0,
                'monthly_target_percent' => 0,
                'sales_target' => 0,
                'sales_target_achieved' => 0,
                'sales_target_percent' => 0,
                'collection_target' => 0,
                'collection_target_achieved' => 0,
                'collection_target_percent' => 0,
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
