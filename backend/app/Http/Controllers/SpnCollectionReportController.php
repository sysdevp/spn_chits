<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ChitDetail;
use App\Models\CommitmentAuction;
use App\Models\Receipt;
use App\Models\SlabDue;
use App\Services\UserLoginScopeService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * SPN / Commitment collection reports (chit_type = 3).
 */
class SpnCollectionReportController extends Controller
{
    protected function resolveBranchIds(Request $request): array
    {
        $tenantId = 0;
        if (isset($request->conditions['tenant_id'])) {
            $tenantId = (int) $request->conditions['tenant_id'];
        } elseif (!empty($request->tenant_id)) {
            $tenantId = (int) $request->tenant_id;
        }

        $scope = app(UserLoginScopeService::class);
        $allowed = $scope->resolveAllowedBranchIdsFromRequest($request);

        $reqBranch = isset($request->branch_id) ? (int) $request->branch_id : 0;
        if ($reqBranch > 0) {
            if ($allowed !== null && !in_array($reqBranch, $allowed, true)) {
                return [];
            }
            return [$reqBranch];
        }

        if ($allowed !== null) {
            return $allowed;
        }

        if ($tenantId > 0) {
            return Branch::where('tenant_id', $tenantId)->pluck('id')->map(function ($id) {
                return (int) $id;
            })->values()->all();
        }

        return Branch::pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();
    }

    protected function commitmentEnrollsQuery(array $branchIds, $schemeId = null)
    {
        $q = ChitDetail::query()
            ->where('chit_type', 3)
            ->where('customer_id', '!=', 1)
            ->whereNull('deleted_at');

        if (!empty($branchIds)) {
            $q->whereIn('branch_id', $branchIds);
        } else {
            $q->whereRaw('1 = 0');
        }

        // Exclude cancelled if column present with cancel markers
        if (Schema::hasColumn('chit_details', 'chit_status')) {
            $q->where(function ($w) {
                $w->whereNull('chit_status')
                    ->orWhere('chit_status', 0)
                    ->orWhere('chit_status', 1)
                    ->orWhere('chit_status', '');
            });
        }

        if ($schemeId !== null && (int) $schemeId > 0) {
            $q->where('scheme_id', (int) $schemeId);
        }

        return $q;
    }

    protected function moneyForEnrollment(int $enrollmentId): array
    {
        $payable = (float) CommitmentAuction::where('enrollment_id', $enrollmentId)->sum('due_amount');
        $paid = (float) Receipt::where('enrollment_id', $enrollmentId)->where('status', 1)->sum('received_amount');
        $pending = max(0, $payable - $paid);
        $today = Carbon::today()->toDateString();
        $runningMonth = (int) CommitmentAuction::where('enrollment_id', $enrollmentId)
            ->where('auction_date', '<=', $today)
            ->count();
        if ($runningMonth <= 0) {
            $runningMonth = (int) CommitmentAuction::where('enrollment_id', $enrollmentId)->min('installment_no') ?: 0;
        }
        return [
            'payable' => round($payable, 2),
            'paid' => round($paid, 2),
            'pending' => round($pending, 2),
            'running_month' => $runningMonth,
        ];
    }

    protected function installmentPaid(int $enrollmentId, $auctionId, $installmentNo): float
    {
        $q = Receipt::where('enrollment_id', $enrollmentId)->where('status', 1);
        if (!empty($auctionId)) {
            $q->where('auction_id', $auctionId);
        } elseif ($installmentNo !== null) {
            $q->where('installment_no', $installmentNo);
        }
        return (float) $q->sum('received_amount');
    }

    protected function enrichEnrollmentRow(ChitDetail $enroll): array
    {
        $money = $this->moneyForEnrollment((int) $enroll->id);
        $cust = $enroll->customer_det;
        $branch = $enroll->branch_det;
        $scheme = $enroll->scheme_det;
        $slabName = '';
        if ($enroll->slab_due_det) {
            $slabName = $enroll->slab_due_det->slab_name ?? '';
        } elseif (!empty($enroll->slab_id)) {
            $sd = SlabDue::find($enroll->slab_id);
            $slabName = $sd ? ($sd->slab_name ?? '') : '';
        }
        $slabLabel = ChitDetail::formatSlabWithArNo($slabName, $enroll->ar_no ?? null);
        $agent = '';
        if ($enroll->collection_agent_det) {
            $agent = trim(($enroll->collection_agent_det->first_name ?? '') . ' ' . ($enroll->collection_agent_det->last_name ?? ''));
        }
        $area = $enroll->collection_area_det ? ($enroll->collection_area_det->collection_area ?? '') : '';

        return [
            'enrollment_id' => (int) $enroll->id,
            'branch_id' => (int) $enroll->branch_id,
            'branch_name' => $branch ? ($branch->branch_name ?? '') : '',
            'scheme_id' => (int) ($enroll->scheme_id ?? 0),
            'scheme_name' => $scheme ? ($scheme->scheme_name ?? $scheme->scheme_format ?? (string) ($scheme->chit_value ?? '')) : '',
            'chit_value' => $scheme ? round((float) ($scheme->chit_value ?? 0), 2) : 0,
            'customer_id' => (int) $enroll->customer_id,
            'customer_name' => $cust ? trim(($cust->name ?? '') . ' ' . ($cust->last_name ?? '')) : '',
            'customer_code' => $cust ? ($cust->customer_code ?? '') : '',
            'mobile_no' => $cust ? ($cust->mobile_no ?? '') : '',
            'ar_no' => $enroll->ar_no ?? '',
            'slab_name' => $slabLabel,
            'collection_agent' => $agent,
            'collection_area' => $area,
            'payable' => $money['payable'],
            'paid' => $money['paid'],
            'overall_pending' => $money['pending'],
            'running_month' => $money['running_month'],
        ];
    }

    /** A. Scheme-wise payable & pending */
    public function scheme_wise_payable_pending(Request $request)
    {
        try {
            $branchIds = $this->resolveBranchIds($request);
            $schemeId = !empty($request->scheme_id) ? (int) $request->scheme_id : 0;
            $enrolls = $this->commitmentEnrollsQuery($branchIds, $schemeId > 0 ? $schemeId : null)
                ->with(['customer_det', 'branch_det', 'scheme_det', 'slab_due_det', 'collection_agent_det', 'collection_area_det'])
                ->orderBy('branch_id')
                ->orderBy('scheme_id')
                ->get();

            $rows = [];
            $i = 1;
            $totPayable = 0;
            $totPaid = 0;
            $totPending = 0;
            foreach ($enrolls as $enroll) {
                $row = $this->enrichEnrollmentRow($enroll);
                $row['sno'] = $i++;
                $totPayable += $row['payable'];
                $totPaid += $row['paid'];
                $totPending += $row['overall_pending'];
                $rows[] = $row;
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $rows,
                'grand_payable' => round($totPayable, 2),
                'grand_paid' => round($totPaid, 2),
                'grand_pending' => round($totPending, 2),
            ], 200);
        } catch (\Exception $e) {
            Log::error('scheme_wise_payable_pending: ' . $e->getMessage());
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error'), 'error' => $e->getMessage()], 200);
        }
    }

    /** B. Next month collection forecast from due schedule */
    public function next_month_collection(Request $request)
    {
        try {
            $branchIds = $this->resolveBranchIds($request);
            $start = Carbon::now()->addMonth()->startOfMonth()->toDateString();
            $end = Carbon::now()->addMonth()->endOfMonth()->toDateString();

            $enrollIds = $this->commitmentEnrollsQuery($branchIds)->pluck('id')->all();
            if (empty($enrollIds)) {
                return response()->json([
                    'status' => 'Success',
                    'msg' => '',
                    'data' => [],
                    'grand_due' => 0,
                    'grand_paid' => 0,
                    'grand_pending' => 0,
                ], 200);
            }

            $auctions = CommitmentAuction::whereIn('enrollment_id', $enrollIds)
                ->whereBetween('auction_date', [$start, $end])
                ->orderBy('auction_date')
                ->get();

            $rows = [];
            $i = 1;
            $totDue = 0;
            $totPaid = 0;
            $totPending = 0;
            foreach ($auctions as $auc) {
                $enroll = ChitDetail::with([
                    'customer_det', 'branch_det', 'scheme_det', 'slab_due_det',
                    'collection_agent_det', 'collection_area_det',
                ])->find($auc->enrollment_id);
                if (!$enroll) {
                    continue;
                }
                $base = $this->enrichEnrollmentRow($enroll);
                $dueAmt = (float) ($auc->due_amount ?? 0);
                $paidInst = $this->installmentPaid((int) $enroll->id, $auc->id, $auc->installment_no);
                $pendingInst = max(0, $dueAmt - $paidInst);
                $row = array_merge($base, [
                    'sno' => $i++,
                    'due_no' => (int) $auc->installment_no,
                    'due_date' => $auc->auction_date ? date('d-m-Y', strtotime($auc->auction_date)) : '',
                    'due_amount' => round($dueAmt, 2),
                    'paid_on_due' => round($paidInst, 2),
                    'pending_to_collect' => round($pendingInst, 2),
                ]);
                $totDue += $dueAmt;
                $totPaid += $paidInst;
                $totPending += $pendingInst;
                $rows[] = $row;
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $rows,
                'period_start' => $start,
                'period_end' => $end,
                'grand_due' => round($totDue, 2),
                'grand_paid' => round($totPaid, 2),
                'grand_pending' => round($totPending, 2),
            ], 200);
        } catch (\Exception $e) {
            Log::error('next_month_collection: ' . $e->getMessage());
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error'), 'error' => $e->getMessage()], 200);
        }
    }

    /** C. This month unpaid on running chits */
    public function this_month_unpaid(Request $request)
    {
        try {
            $branchIds = $this->resolveBranchIds($request);
            $year = !empty($request->year) ? (int) $request->year : (int) date('Y');
            $month = !empty($request->month) ? (int) $request->month : (int) date('n');
            $start = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

            $enrollIds = $this->commitmentEnrollsQuery($branchIds)->pluck('id')->all();
            if (empty($enrollIds)) {
                return response()->json([
                    'status' => 'Success',
                    'msg' => '',
                    'data' => [],
                    'grand_current_pending' => 0,
                    'grand_overall_pending' => 0,
                    'grand_paid' => 0,
                    'grand_received_month' => 0,
                ], 200);
            }

            $auctions = CommitmentAuction::whereIn('enrollment_id', $enrollIds)
                ->whereBetween('auction_date', [$start, $end])
                ->get();

            $rows = [];
            $i = 1;
            $totCurPending = 0;
            $totOverall = 0;
            $totPaid = 0;
            $totRecvMonth = 0;

            foreach ($auctions as $auc) {
                $dueAmt = (float) ($auc->due_amount ?? 0);
                $paidInst = $this->installmentPaid((int) $auc->enrollment_id, $auc->id, $auc->installment_no);
                $pendingInst = max(0, $dueAmt - $paidInst);
                if ($pendingInst <= 0) {
                    continue;
                }
                $enroll = ChitDetail::with([
                    'customer_det', 'branch_det', 'scheme_det', 'slab_due_det',
                    'collection_agent_det', 'collection_area_det',
                ])->find($auc->enrollment_id);
                if (!$enroll) {
                    continue;
                }
                $base = $this->enrichEnrollmentRow($enroll);
                $recvMonth = (float) Receipt::where('enrollment_id', $enroll->id)
                    ->where('status', 1)
                    ->whereBetween('receipt_date', [$start, $end])
                    ->sum('received_amount');

                $row = array_merge($base, [
                    'sno' => $i++,
                    'due_no' => (int) $auc->installment_no,
                    'due_date' => $auc->auction_date ? date('d-m-Y', strtotime($auc->auction_date)) : '',
                    'due_amount' => round($dueAmt, 2),
                    'current_month_pending' => round($pendingInst, 2),
                    'payment_received_month' => round($recvMonth, 2),
                    'paid_on_due' => round($paidInst, 2),
                ]);
                $totCurPending += $pendingInst;
                $totOverall += $base['overall_pending'];
                $totPaid += $base['paid'];
                $totRecvMonth += $recvMonth;
                $rows[] = $row;
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $rows,
                'period_start' => $start,
                'period_end' => $end,
                'grand_current_pending' => round($totCurPending, 2),
                'grand_overall_pending' => round($totOverall, 2),
                'grand_paid' => round($totPaid, 2),
                'grand_received_month' => round($totRecvMonth, 2),
            ], 200);
        } catch (\Exception $e) {
            Log::error('this_month_unpaid: ' . $e->getMessage());
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error'), 'error' => $e->getMessage()], 200);
        }
    }

    /** D. Due no / installment-wise customer list */
    public function due_no_customer_list(Request $request)
    {
        try {
            $branchIds = $this->resolveBranchIds($request);
            $dueNo = isset($request->due_no) ? (int) $request->due_no : 0;
            if ($dueNo <= 0) {
                return response()->json(['status' => 'Error', 'msg' => 'Due No is required', 'data' => []], 200);
            }
            $unpaidOnly = !empty($request->unpaid_only) && (int) $request->unpaid_only === 1;

            $enrollIds = $this->commitmentEnrollsQuery($branchIds)->pluck('id')->all();
            if (empty($enrollIds)) {
                return response()->json([
                    'status' => 'Success',
                    'msg' => '',
                    'data' => [],
                    'grand_due' => 0,
                    'grand_paid_due' => 0,
                    'grand_pending_due' => 0,
                    'grand_overall_pending' => 0,
                ], 200);
            }

            $auctions = CommitmentAuction::whereIn('enrollment_id', $enrollIds)
                ->where('installment_no', $dueNo)
                ->orderBy('enrollment_id')
                ->get();

            $rows = [];
            $i = 1;
            $totDue = 0;
            $totPaidDue = 0;
            $totPendingDue = 0;
            $totOverall = 0;

            foreach ($auctions as $auc) {
                $dueAmt = (float) ($auc->due_amount ?? 0);
                $paidInst = $this->installmentPaid((int) $auc->enrollment_id, $auc->id, $dueNo);
                $pendingInst = max(0, $dueAmt - $paidInst);
                if ($unpaidOnly && $pendingInst <= 0) {
                    continue;
                }
                $enroll = ChitDetail::with([
                    'customer_det', 'branch_det', 'scheme_det', 'slab_due_det',
                    'collection_agent_det', 'collection_area_det',
                ])->find($auc->enrollment_id);
                if (!$enroll) {
                    continue;
                }
                $base = $this->enrichEnrollmentRow($enroll);
                $row = array_merge($base, [
                    'sno' => $i++,
                    'due_no' => $dueNo,
                    'due_date' => $auc->auction_date ? date('d-m-Y', strtotime($auc->auction_date)) : '',
                    'due_amount' => round($dueAmt, 2),
                    'paid_on_due' => round($paidInst, 2),
                    'current_due_pending' => round($pendingInst, 2),
                    'payment_received' => $base['paid'],
                ]);
                $totDue += $dueAmt;
                $totPaidDue += $paidInst;
                $totPendingDue += $pendingInst;
                $totOverall += $base['overall_pending'];
                $rows[] = $row;
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $rows,
                'grand_due' => round($totDue, 2),
                'grand_paid_due' => round($totPaidDue, 2),
                'grand_pending_due' => round($totPendingDue, 2),
                'grand_overall_pending' => round($totOverall, 2),
            ], 200);
        } catch (\Exception $e) {
            Log::error('due_no_customer_list: ' . $e->getMessage());
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error'), 'error' => $e->getMessage()], 200);
        }
    }
}
