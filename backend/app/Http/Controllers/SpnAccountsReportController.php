<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Accounts / HR reports: completed-not-paid, cash box (with approvals), income cash box,
 * my day, branch income & expense (with insights), petty cash branch report, allowances.
 */
class SpnAccountsReportController extends Controller
{
    private function switchTenantDb(Request $request)
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
        if (!Schema::hasTable('cash_box_entries')) {
            Schema::create('cash_box_entries', function ($table) {
                $table->bigIncrements('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->date('entry_date')->nullable();
                $table->string('direction', 5)->default('in');
                $table->string('source', 30)->default('manual');
                $table->bigInteger('ref_id')->default(0);
                $table->string('mode', 10)->default('cash');
                $table->decimal('amount', 14, 2)->default(0);
                $table->string('title')->nullable();
                $table->text('remarks')->nullable();
                $table->string('status', 15)->default('pending');
                $table->integer('requested_by')->default(0);
                $table->integer('approved_by')->default(0);
                $table->dateTime('approved_at')->nullable();
                $table->text('approval_remarks')->nullable();
                $table->timestamps();
                $table->index(['tenant_id', 'branch_id', 'entry_date']);
                $table->index(['source', 'ref_id']);
            });
        }
        if (!Schema::hasTable('employee_allowances')) {
            Schema::create('employee_allowances', function ($table) {
                $table->bigIncrements('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('type', 15)->default('petrol');
                $table->date('allowance_date')->nullable();
                $table->decimal('km', 10, 2)->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('mode', 10)->default('cash');
                $table->text('remarks')->nullable();
                $table->integer('created_by')->default(0);
                $table->timestamps();
                $table->index(['tenant_id', 'employee_id', 'allowance_date']);
            });
        }
        if (Schema::hasTable('employees') && !Schema::hasColumn('employees', 'day_allowance')) {
            Schema::table('employees', function ($table) {
                $table->decimal('day_allowance', 10, 2)->default(200);
            });
        }
    }

    private function range(Request $request)
    {
        $from = $request->from_date ?: ($request->date ?: date('Y-m-01'));
        $to = $request->to_date ?: ($request->date ?: date('Y-m-d'));
        return [date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))];
    }

    private function scoped($q, Request $request, $alias = '')
    {
        $p = $alias ? $alias . '.' : '';
        if (!empty($request->tenant_id)) {
            $q->where($p . 'tenant_id', $request->tenant_id);
        }
        if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
            $q->where($p . 'branch_id', $request->branch_id);
        }
        return $q;
    }

    private function notDeleted($q, $table, $alias = '')
    {
        if (Schema::hasColumn($table, 'deleted_at')) {
            $q->whereNull(($alias ? $alias . '.' : '') . 'deleted_at');
        }
        return $q;
    }

    private function branchNames()
    {
        return Schema::hasTable('branches') ? DB::table('branches')->pluck('branch_name', 'id')->all() : [];
    }

    private function employeeNames(array $ids)
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) {
            return [];
        }
        return DB::table('employees')->whereIn('id', $ids)->get(['id', 'first_name', 'last_name'])
            ->mapWithKeys(function ($e) {
                return [$e->id => trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''))];
            })->all();
    }

    /**
     * Every money movement in the period as uniform rows:
     * kind (income/expense), category, mode (cash/bank), amount, date, branch_id, status, ref.
     */
    private function movements(Request $request, $from, $to)
    {
        $out = [];
        $mode = function ($pt) {
            return (int) $pt === 1 ? 'cash' : 'bank';
        };

        if (Schema::hasTable('receipts')) {
            $q = DB::table('receipts')->where('status', 1)->whereBetween('receipt_date', [$from, $to]);
            $this->notDeleted($this->scoped($q, $request), 'receipts');
            foreach ($q->get(['id', 'branch_id', 'receipt_date', 'received_amount', 'payment_type_id', 'receipt_no', 'employee_id']) as $r) {
                $out[] = ['kind' => 'income', 'category' => 'Collection', 'bucket' => 'collection', 'mode' => $mode($r->payment_type_id),
                    'amount' => (float) $r->received_amount, 'date' => substr($r->receipt_date, 0, 10), 'branch_id' => $r->branch_id,
                    'ref' => $r->receipt_no, 'employee_id' => $r->employee_id, 'status' => 'approved'];
            }
        }
        if (Schema::hasTable('before_enrol_receipts')) {
            $q = DB::table('before_enrol_receipts')->whereBetween('received_date', [$from, $to]);
            if (Schema::hasColumn('before_enrol_receipts', 'status')) {
                $q->where('status', 1);
            }
            $this->notDeleted($this->scoped($q, $request), 'before_enrol_receipts');
            foreach ($q->get() as $r) {
                $out[] = ['kind' => 'income', 'category' => 'RF / Before enrolment', 'bucket' => 'rf', 'mode' => $mode($r->payment_type_id ?? 1),
                    'amount' => (float) ($r->amount ?? 0), 'date' => substr($r->received_date, 0, 10), 'branch_id' => $r->branch_id,
                    'ref' => $r->receipt_no ?? ('RF-' . $r->id), 'employee_id' => $r->employee_id ?? 0, 'status' => 'approved'];
            }
        }
        if (Schema::hasTable('other_charges')) {
            $q = DB::table('other_charges')->where('status', 1)->whereBetween('received_date', [$from, $to]);
            $this->notDeleted($this->scoped($q, $request), 'other_charges');
            foreach ($q->get() as $r) {
                $out[] = ['kind' => 'income', 'category' => 'Other charges', 'bucket' => 'other', 'mode' => $mode($r->payment_type_id ?? 1),
                    'amount' => (float) ($r->amount ?? 0), 'date' => substr($r->received_date, 0, 10), 'branch_id' => $r->branch_id,
                    'ref' => $r->receipt_no ?? ('OC-' . $r->id), 'employee_id' => $r->employee_id ?? 0, 'status' => 'approved'];
            }
        }
        if (Schema::hasTable('petty_cash_incomes')) {
            $q = DB::table('petty_cash_incomes')->where('status', 1)->whereBetween('received_date', [$from, $to]);
            $this->notDeleted($this->scoped($q, $request), 'petty_cash_incomes');
            foreach ($q->get() as $r) {
                $out[] = ['kind' => 'income', 'category' => 'Petty cash income', 'bucket' => 'petty', 'mode' => $mode($r->payment_type_id ?? 1),
                    'amount' => (float) ($r->amount_received ?? 0), 'date' => substr($r->received_date, 0, 10), 'branch_id' => $r->branch_id,
                    'ref' => 'PCI-' . $r->id, 'employee_id' => $r->employee_id ?? 0, 'status' => 'approved'];
            }
        }
        if (Schema::hasTable('petty_cash_expenses')) {
            $types = Schema::hasTable('petty_cash_expense_types')
                ? DB::table('petty_cash_expense_types')->pluck('expenses_type', 'id')->all() : [];
            $q = DB::table('petty_cash_expenses')->where('status', 1)->whereBetween('expenses_date', [$from, $to]);
            $this->notDeleted($this->scoped($q, $request), 'petty_cash_expenses');
            foreach ($q->get() as $r) {
                $out[] = ['kind' => 'expense', 'category' => $types[$r->expenses_type_id] ?? 'Petty cash expense', 'bucket' => 'petty',
                    'mode' => $mode($r->payment_type_id ?? 1), 'amount' => (float) ($r->expenses_amount ?? 0),
                    'date' => substr($r->expenses_date, 0, 10), 'branch_id' => $r->branch_id, 'ref' => 'PCE-' . $r->id,
                    'employee_id' => $r->employee_id ?? 0, 'status' => 'approved'];
            }
        }
        if (Schema::hasTable('payment_desk_requests')) {
            $q = DB::table('payment_desk_requests')->whereBetween('payment_date', [$from, $to])
                ->where(function ($w) {
                    $w->where('status', 'approved');
                    if (Schema::hasColumn('payment_desk_requests', 'accounts_at')) {
                        $w->orWhere(function ($x) {
                            $x->where('status', 'pending_field')->whereNotNull('accounts_at');
                        });
                    }
                });
            $this->scoped($q, $request);
            $labels = ['prized' => 'Prized payment', 'closed' => 'Final closed payment', 'refund' => 'Refund payment'];
            $boxStatus = DB::table('cash_box_entries')->where('source', 'payout')->pluck('status', 'ref_id')->all();
            foreach ($q->get() as $r) {
                if (isset($boxStatus[$r->id]) && $boxStatus[$r->id] !== 'approved') {
                    continue;
                }
                $out[] = ['kind' => 'expense', 'category' => $labels[$r->kind] ?? 'Payout', 'bucket' => 'payout',
                    'mode' => strtolower((string) $r->payment_mode) === 'cash' ? 'cash' : 'bank',
                    'amount' => (float) ($r->net_amount ?? 0), 'date' => substr($r->payment_date, 0, 10), 'branch_id' => $r->branch_id,
                    'ref' => 'PD-' . $r->id . ($r->customer_name ? ' · ' . $r->customer_name : ''), 'employee_id' => 0, 'status' => 'approved'];
            }
        }
        $q = DB::table('employee_allowances')->whereBetween('allowance_date', [$from, $to]);
        $this->scoped($q, $request);
        foreach ($q->get() as $r) {
            $out[] = ['kind' => 'expense', 'category' => $r->type === 'day' ? 'Day allowance' : 'Petrol allowance', 'bucket' => 'allowance',
                'mode' => $r->mode ?: 'cash', 'amount' => (float) $r->amount, 'date' => $r->allowance_date, 'branch_id' => $r->branch_id,
                'ref' => 'AL-' . $r->id, 'employee_id' => $r->employee_id, 'status' => 'approved'];
        }
        $q = DB::table('cash_box_entries')->where('source', 'manual')->whereBetween('entry_date', [$from, $to])->where('status', 'approved');
        $this->scoped($q, $request);
        foreach ($q->get() as $r) {
            $out[] = ['kind' => $r->direction === 'in' ? 'income' : 'expense', 'category' => $r->title ?: 'My cash', 'bucket' => 'mycash',
                'mode' => $r->mode, 'amount' => (float) $r->amount, 'date' => $r->entry_date, 'branch_id' => $r->branch_id,
                'ref' => 'CB-' . $r->id, 'employee_id' => $r->requested_by, 'status' => 'approved'];
        }
        return $out;
    }

    // ---------------------------------------------------------------- Header notifications (point 27)

    public function header_notifications(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $today = date('Y-m-d');
            $emp = (int) $request->employee_id;
            $tasks = [];
            $targets = [];
            $approvals = [];

            if ($emp && Schema::hasTable('employee_tasks')) {
                $q = DB::table('employee_tasks')->where('employee_id', $emp)->where('task_status', '!=', 'completed')
                    ->where('task_date', '<=', $today);
                $this->notDeleted($q, 'employee_tasks');
                foreach ($q->orderBy('due_date')->limit(8)->get() as $t) {
                    $overdue = $t->due_date && $t->due_date < $today;
                    $tasks[] = ['title' => $t->title, 'sub' => ($overdue ? 'Overdue · ' : 'Due ') . ($t->due_date ?: $t->task_date) . ' · ' . ucfirst($t->priority),
                        'overdue' => $overdue, 'route' => 'view-daily-tasks-targets'];
                }
            }
            if ($emp && Schema::hasTable('employee_targets')) {
                $q = DB::table('employee_targets')->where('employee_id', $emp)->where('status', 1)
                    ->where('period_start', '<=', $today)->where('period_end', '>=', $today);
                $this->notDeleted($q, 'employee_targets');
                foreach ($q->limit(5)->get() as $t) {
                    $pct = (float) $t->target_amount > 0 ? round((float) $t->achieved_amount / (float) $t->target_amount * 100) : 0;
                    $targets[] = ['title' => ($t->target_label ?: 'Target') . ' (' . $t->period_type . ')',
                        'sub' => 'Rs.' . number_format((float) $t->achieved_amount) . ' of Rs.' . number_format((float) $t->target_amount) . ' · ' . $pct . '%',
                        'pct' => $pct, 'route' => 'view-daily-tasks-targets'];
                }
            }
            $add = function ($n, $title, $route) use (&$approvals) {
                if ($n > 0) {
                    $approvals[] = ['title' => $title, 'count' => $n, 'route' => $route];
                }
            };
            $scope = function ($q) use ($request) {
                if (!empty($request->tenant_id)) {
                    $q->whereIn('tenant_id', [(int) $request->tenant_id, 0]);
                }
                if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
                    $q->where('branch_id', $request->branch_id);
                }
                return $q;
            };
            if (Schema::hasTable('payment_desk_requests')) {
                $counts = $scope(DB::table('payment_desk_requests'))->groupBy('status')->pluck(DB::raw('COUNT(*)'), 'status')->all();
                $add((int) ($counts['pending_chit_admin'] ?? 0), 'Payments waiting for Chit Admin', 'payment-pending-approvals');
                $add((int) ($counts['pending_accounts'] ?? 0) + (int) ($counts['pending_admin'] ?? 0), 'Payouts waiting for Accounts', 'payment-accounts-payout');
                $add((int) ($counts['pending_field'] ?? 0), 'Payments in field verification', 'payment-pending-approvals');
                if ($emp && Schema::hasColumn('payment_desk_requests', 'field_employee_id')) {
                    $mine = $scope(DB::table('payment_desk_requests'))->where('status', 'pending_field')->where('field_employee_id', $emp)->count();
                    $add($mine, 'Field verifications assigned to you', 'payment-pending-approvals');
                }
            }
            $add($scope(DB::table('cash_box_entries'))->where('status', 'pending')->count(), 'Cash box entries to approve', 'cash-box-approvals');
            if (Schema::hasTable('employee_leave_requests')) {
                $q = $scope(DB::table('employee_leave_requests'))->where('request_status', 'pending');
                $this->notDeleted($q, 'employee_leave_requests');
                $add($q->count(), 'Leave / permission requests', 'view-leave-requests');
            }
            if (Schema::hasTable('office_tickets')) {
                $add($scope(DB::table('office_tickets'))->whereIn('status', ['open', 'pending', 'in_progress'])->count(), 'Open office tickets', 'office-tickets');
            }
            $total = count($tasks) + count(array_filter($targets, function ($t) { return $t['pct'] < 100; }))
                + array_sum(array_column($approvals, 'count'));
            return response()->json(['status' => 'Success', 'total' => $total, 'tasks' => $tasks, 'targets' => $targets, 'approvals' => $approvals], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'total' => 0], 200);
        }
    }

    // ---------------------------------------------------------------- Point 10

    public function completed_not_paid(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $q = DB::table('chit_details as cd')
                ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
                ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id');
            $this->notDeleted($this->scoped($q, $request, 'cd'), 'chit_details', 'cd');
            if (!empty($request->scheme_id)) {
                $q->where('cd.scheme_id', $request->scheme_id);
            }
            if (Schema::hasColumn('chit_details', 'eligibility_status')) {
                $q->where(function ($w) {
                    $w->whereNull('cd.eligibility_status')->orWhere('cd.eligibility_status', '!=', 2);
                });
            }
            $chits = $q->get([
                'cd.id', 'cd.ar_no', 'cd.ticket_no', 'cd.branch_id', 'cd.scheme_id', 'cd.customer_id', 'cd.date_introduced', 'cd.prized_status',
                'c.name', 'c.last_name', 'c.mobile_no', 'c.customer_code', 'b.branch_name',
                's.chit_value', 's.no_of_months', 's.chit_letter',
            ]);
            $ids = $chits->pluck('id')->all();
            if (!$ids) {
                return response()->json(['status' => 'Success', 'data' => [], 'totals' => []], 200);
            }
            $due = DB::table('commitment_auctions')->whereIn('enrollment_id', $ids)
                ->groupBy('enrollment_id')
                ->select('enrollment_id', DB::raw('SUM(due_amount) as total_due'), DB::raw('MAX(installment_no) as months'),
                    DB::raw('MAX(auction_date) as last_date'))
                ->get()->keyBy('enrollment_id');
            $lastDue = DB::table('commitment_auctions as a')
                ->join(DB::raw('(SELECT enrollment_id, MAX(installment_no) mx FROM commitment_auctions GROUP BY enrollment_id) t'), function ($j) {
                    $j->on('t.enrollment_id', '=', 'a.enrollment_id')->on('t.mx', '=', 'a.installment_no');
                })
                ->whereIn('a.enrollment_id', $ids)->pluck('a.due_amount', 'a.enrollment_id')->all();
            $paidQ = DB::table('receipts')->whereIn('enrollment_id', $ids)->where('status', 1);
            $this->notDeleted($paidQ, 'receipts');
            $paid = $paidQ->groupBy('enrollment_id')
                ->select('enrollment_id', DB::raw('SUM(received_amount) as paid'), DB::raw('MAX(receipt_date) as last_paid'))
                ->get()->keyBy('enrollment_id');
            $given = [];
            if (Schema::hasTable('payments')) {
                $pq = DB::table('payments')->whereIn('enrollment_id', $ids)->where('status', 1);
                $this->notDeleted($pq, 'payments');
                $given = $pq->groupBy('enrollment_id')->pluck(DB::raw('SUM(total_amount)'), 'enrollment_id')->all();
            }
            $desk = Schema::hasTable('payment_desk_requests')
                ? DB::table('payment_desk_requests')->whereIn('enrollment_id', $ids)->whereIn('kind', ['prized', 'closed'])
                    ->where('status', '!=', 'rejected')->orderBy('id')->get()->groupBy('enrollment_id')
                : collect();

            $rows = [];
            foreach ($chits as $c) {
                $d = $due[$c->id] ?? null;
                if (!$d || (float) $d->total_due <= 0) {
                    continue;
                }
                $p = $paid[$c->id] ?? null;
                $paidAmt = $p ? (float) $p->paid : 0;
                if ($paidAmt + 0.5 < (float) $d->total_due) {
                    continue;
                }
                $requests = $desk[$c->id] ?? collect();
                $deskPaid = (float) $requests->where('status', 'approved')->sum('net_amount');
                $givenAmt = (float) ($given[$c->id] ?? 0) + $deskPaid;
                if ($givenAmt > 0 && empty($request->include_paid)) {
                    continue;
                }
                $payable = (float) ($c->chit_value ?? 0);
                $open = $requests->first(function ($r) {
                    return $r->status !== 'approved';
                });
                $rows[] = [
                    'enrollment_id' => $c->id,
                    'customer_name' => trim(($c->name ?? '') . ' ' . ($c->last_name ?? '')),
                    'customer_code' => $c->customer_code,
                    'mobile' => $c->mobile_no,
                    'ar_no' => $c->ar_no ?: $c->ticket_no,
                    'branch_name' => $c->branch_name,
                    'scheme' => $c->chit_value ? ('Rs.' . number_format((float) $c->chit_value) . ' / ' . $c->no_of_months . 'M' . ($c->chit_letter ? ' ' . $c->chit_letter : '')) : '',
                    'prized_status' => (int) $c->prized_status,
                    'joined_on' => $c->date_introduced,
                    'months' => (int) $d->months,
                    'completed_on' => $p ? substr($p->last_paid, 0, 10) : '',
                    'last_due_date' => $d->last_date ? substr($d->last_date, 0, 10) : '',
                    'total_due' => (float) $d->total_due,
                    'customer_paid' => $paidAmt,
                    'last_month_due' => (float) ($lastDue[$c->id] ?? 0),
                    'payable' => $payable,
                    'difference' => $payable - $paidAmt,
                    'given' => $givenAmt,
                    'pending_payout' => max(0, $payable - $givenAmt),
                    'days_waiting' => $p && $p->last_paid ? (int) floor((time() - strtotime($p->last_paid)) / 86400) : 0,
                    'request_status' => $open ? $open->status : 'not_requested',
                ];
            }
            usort($rows, function ($a, $b) {
                return $b['days_waiting'] <=> $a['days_waiting'];
            });
            $sum = function ($k) use ($rows) {
                return array_sum(array_column($rows, $k));
            };
            return response()->json(['status' => 'Success', 'data' => $rows, 'totals' => [
                'count' => count($rows),
                'customer_paid' => $sum('customer_paid'),
                'payable' => $sum('payable'),
                'difference' => $sum('difference'),
                'pending_payout' => $sum('pending_payout'),
                'not_requested' => count(array_filter($rows, function ($r) { return $r['request_status'] === 'not_requested'; })),
            ]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'data' => []], 200);
        }
    }

    // ---------------------------------------------------------------- Cash box (points 30, 33)

    /** Payouts / expenses that must be approved before they leave the cash box. */
    public static function queuePayout($tenantId, $branchId, $refId, $amount, $mode, $title, $requestedBy, $date = null)
    {
        try {
            (new self())->ensureTables();
            if (DB::table('cash_box_entries')->where('source', 'payout')->where('ref_id', $refId)->exists()) {
                return;
            }
            DB::table('cash_box_entries')->insert([
                'tenant_id' => (int) $tenantId, 'branch_id' => (int) $branchId, 'entry_date' => $date ?: date('Y-m-d'),
                'direction' => 'out', 'source' => 'payout', 'ref_id' => (int) $refId,
                'mode' => strtolower((string) $mode) === 'cash' ? 'cash' : 'bank', 'amount' => (float) $amount,
                'title' => $title, 'status' => 'pending', 'requested_by' => (int) $requestedBy,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // cash box queue must never block the payout itself
        }
    }

    public function cash_box(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            [$from, $to] = $this->range($request);
            $mode = $request->mode ?: '';
            $all = $this->movements($request, $from, $to);
            $cashOnly = $mode === 'cash' ? 'cash' : ($mode === 'bank' ? 'bank' : '');

            $rows = array_values(array_filter($all, function ($r) use ($cashOnly) {
                return !$cashOnly || $r['mode'] === $cashOnly;
            }));
            $buckets = ['collection' => 0, 'rf' => 0, 'other' => 0, 'petty' => 0, 'mycash' => 0];
            $bucketsBank = $buckets;
            $income = 0;
            $expense = 0;
            $byDay = [];
            foreach ($rows as $r) {
                $sign = $r['kind'] === 'income' ? 1 : -1;
                if ($r['kind'] === 'income') {
                    $income += $r['amount'];
                    if (isset($buckets[$r['bucket']])) {
                        if ($r['mode'] === 'cash') {
                            $buckets[$r['bucket']] += $r['amount'];
                        } else {
                            $bucketsBank[$r['bucket']] += $r['amount'];
                        }
                    }
                } else {
                    $expense += $r['amount'];
                }
                if (!isset($byDay[$r['date']])) {
                    $byDay[$r['date']] = ['date' => $r['date'], 'collection' => 0, 'rf' => 0, 'mycash' => 0, 'other' => 0, 'income' => 0, 'expense' => 0];
                }
                if ($r['kind'] === 'income') {
                    $byDay[$r['date']]['income'] += $r['amount'];
                    $k = in_array($r['bucket'], ['collection', 'rf', 'mycash']) ? $r['bucket'] : 'other';
                    $byDay[$r['date']][$k] += $r['amount'];
                } else {
                    $byDay[$r['date']]['expense'] += $r['amount'];
                }
            }
            ksort($byDay);
            $running = 0;
            foreach ($byDay as &$d) {
                $running += $d['income'] - $d['expense'];
                $d['balance'] = $running;
            }
            unset($d);

            $pending = DB::table('cash_box_entries')->where('status', 'pending');
            $this->scoped($pending, $request);
            $pendingRows = $pending->orderBy('id', 'desc')->get();
            $history = DB::table('cash_box_entries')->where('status', '!=', 'pending')->whereBetween('entry_date', [$from, $to]);
            $this->scoped($history, $request);
            $historyRows = $history->orderBy('id', 'desc')->limit(300)->get();
            $names = $this->employeeNames(array_merge(
                $pendingRows->pluck('requested_by')->all(), $historyRows->pluck('requested_by')->all(),
                $historyRows->pluck('approved_by')->all(), array_column($rows, 'employee_id')
            ));
            $branches = $this->branchNames();
            $decorate = function ($r) use ($names, $branches) {
                $r->requested_by_name = $names[$r->requested_by] ?? ($r->requested_by ? 'Employee #' . $r->requested_by : 'System');
                $r->approved_by_name = $names[$r->approved_by] ?? '';
                $r->branch_name = $branches[$r->branch_id] ?? '';
                return $r;
            };
            foreach ($rows as &$r) {
                $r['branch_name'] = $branches[$r['branch_id']] ?? '';
                $r['employee_name'] = $names[$r['employee_id']] ?? '';
            }
            unset($r);
            usort($rows, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });

            return response()->json([
                'status' => 'Success',
                'from' => $from,
                'to' => $to,
                'summary' => [
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                    'cash' => $buckets,
                    'bank' => $bucketsBank,
                    'pending_count' => $pendingRows->count(),
                    'pending_amount' => (float) $pendingRows->sum('amount'),
                ],
                'days' => array_values($byDay),
                'entries' => $rows,
                'pending' => $pendingRows->map($decorate)->values(),
                'history' => $historyRows->map($decorate)->values(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function cash_box_request(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $amount = (float) $request->amount;
            if ($amount <= 0 || !in_array($request->direction, ['in', 'out'])) {
                return response()->json(['status' => 'Error', 'msg' => 'Enter direction and a valid amount'], 200);
            }
            if (!trim((string) $request->title)) {
                return response()->json(['status' => 'Error', 'msg' => 'Enter what this entry is for'], 200);
            }
            $id = DB::table('cash_box_entries')->insertGetId([
                'tenant_id' => (int) $request->tenant_id,
                'branch_id' => (int) $request->branch_id,
                'entry_date' => $request->entry_date ? date('Y-m-d', strtotime($request->entry_date)) : date('Y-m-d'),
                'direction' => $request->direction,
                'source' => 'manual',
                'mode' => $request->mode === 'bank' ? 'bank' : 'cash',
                'amount' => $amount,
                'title' => trim((string) $request->title),
                'remarks' => $request->remarks,
                'status' => 'pending',
                'requested_by' => (int) $request->employee_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Sent for admin approval', 'id' => $id], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function cash_box_decide(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('cash_box_entries')->where('id', $request->id)->first();
            if (!$row || $row->status !== 'pending') {
                return response()->json(['status' => 'Error', 'msg' => 'Entry is not pending'], 200);
            }
            $approve = $request->decision === 'approve';
            if (!$approve && !trim((string) $request->remarks)) {
                return response()->json(['status' => 'Error', 'msg' => 'Remarks are required to reject'], 200);
            }
            DB::table('cash_box_entries')->where('id', $row->id)->update([
                'status' => $approve ? 'approved' : 'rejected',
                'approved_by' => (int) $request->employee_id,
                'approved_at' => now(),
                'approval_remarks' => $request->remarks,
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'Success', 'msg' => $approve ? 'Approved — cash box updated' : 'Rejected'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    // ---------------------------------------------------------------- My day (point 34)

    public function my_day(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $date = $request->date ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d');
            $rows = $this->movements($request, $date, $date);
            $branches = $this->branchNames();
            $names = $this->employeeNames(array_column($rows, 'employee_id'));
            $groups = [];
            $tot = ['income_cash' => 0, 'income_bank' => 0, 'expense_cash' => 0, 'expense_bank' => 0];
            foreach ($rows as $r) {
                $key = $r['kind'] . '|' . $r['category'];
                if (!isset($groups[$key])) {
                    $groups[$key] = ['kind' => $r['kind'], 'category' => $r['category'], 'cash' => 0, 'bank' => 0, 'count' => 0];
                }
                $groups[$key][$r['mode']] += $r['amount'];
                $groups[$key]['count']++;
                $tot[$r['kind'] . '_' . $r['mode']] += $r['amount'];
            }
            foreach ($rows as &$r) {
                $r['branch_name'] = $branches[$r['branch_id']] ?? '';
                $r['employee_name'] = $names[$r['employee_id']] ?? '';
            }
            unset($r);
            $openQ = DB::table('cash_box_entries')->where('status', 'pending');
            $this->scoped($openQ, $request);
            return response()->json([
                'status' => 'Success',
                'date' => $date,
                'lines' => array_values($groups),
                'entries' => $rows,
                'totals' => $tot + [
                    'income' => $tot['income_cash'] + $tot['income_bank'],
                    'expense' => $tot['expense_cash'] + $tot['expense_bank'],
                    'net_cash' => $tot['income_cash'] - $tot['expense_cash'],
                    'net_bank' => $tot['income_bank'] - $tot['expense_bank'],
                    'pending_approvals' => $openQ->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    // ---------------------------------------------------------------- Branch income & expense (point 25)

    public function branch_income_expense(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            [$from, $to] = $this->range($request);
            $days = max(1, (int) round((strtotime($to) - strtotime($from)) / 86400) + 1);
            $prevTo = date('Y-m-d', strtotime($from . ' -1 day'));
            $prevFrom = date('Y-m-d', strtotime($prevTo . ' -' . ($days - 1) . ' days'));
            $cur = $this->movements($request, $from, $to);
            $prev = $this->movements($request, $prevFrom, $prevTo);
            $branches = $this->branchNames();

            $roll = function ($rows) use ($branches) {
                $out = [];
                foreach ($rows as $r) {
                    $b = (int) $r['branch_id'];
                    if (!isset($out[$b])) {
                        $out[$b] = ['branch_id' => $b, 'branch_name' => $branches[$b] ?? ('Branch ' . $b), 'income' => 0, 'expense' => 0,
                            'income_lines' => [], 'expense_lines' => [], 'cash' => 0, 'bank' => 0];
                    }
                    $side = $r['kind'] === 'income' ? 'income' : 'expense';
                    $out[$b][$side] += $r['amount'];
                    $out[$b][$side . '_lines'][$r['category']] = ($out[$b][$side . '_lines'][$r['category']] ?? 0) + $r['amount'];
                    $out[$b][$r['mode']] += $r['kind'] === 'income' ? $r['amount'] : -$r['amount'];
                }
                return $out;
            };
            $now = $roll($cur);
            $before = $roll($prev);
            $rows = [];
            foreach ($now as $b => $r) {
                $p = $before[$b] ?? ['income' => 0, 'expense' => 0];
                $r['net'] = $r['income'] - $r['expense'];
                $r['expense_ratio'] = $r['income'] > 0 ? round($r['expense'] / $r['income'] * 100, 1) : ($r['expense'] > 0 ? 100 : 0);
                $r['prev_income'] = $p['income'];
                $r['prev_expense'] = $p['expense'];
                $r['income_change'] = $p['income'] > 0 ? round(($r['income'] - $p['income']) / $p['income'] * 100, 1) : null;
                $r['expense_change'] = $p['expense'] > 0 ? round(($r['expense'] - $p['expense']) / $p['expense'] * 100, 1) : null;
                arsort($r['expense_lines']);
                arsort($r['income_lines']);
                $r['top_expense'] = key($r['expense_lines']) ?: '';
                $rows[] = $r;
            }
            usort($rows, function ($a, $b) {
                return $b['net'] <=> $a['net'];
            });

            $insights = [];
            $totIncome = array_sum(array_column($rows, 'income'));
            $totExpense = array_sum(array_column($rows, 'expense'));
            if ($rows) {
                $best = $rows[0];
                $insights[] = ['type' => 'good', 'text' => $best['branch_name'] . ' leads with a net of Rs.' . number_format($best['net']) . ' (income Rs.' . number_format($best['income']) . ').'];
                $loss = array_filter($rows, function ($r) { return $r['net'] < 0; });
                foreach ($loss as $r) {
                    $insights[] = ['type' => 'bad', 'text' => $r['branch_name'] . ' spent more than it earned (net Rs.' . number_format($r['net']) . '). Biggest expense: ' . ($r['top_expense'] ?: '-') . '.'];
                }
                foreach ($rows as $r) {
                    if ($r['expense_ratio'] > 60 && $r['net'] >= 0) {
                        $insights[] = ['type' => 'warn', 'text' => $r['branch_name'] . ' expenses are ' . $r['expense_ratio'] . '% of income — review ' . ($r['top_expense'] ?: 'expenses') . '.'];
                    }
                    if ($r['income_change'] !== null && $r['income_change'] <= -15) {
                        $insights[] = ['type' => 'warn', 'text' => $r['branch_name'] . ' income dropped ' . abs($r['income_change']) . '% vs the previous ' . $days . ' days.'];
                    }
                    if ($r['income_change'] !== null && $r['income_change'] >= 15) {
                        $insights[] = ['type' => 'good', 'text' => $r['branch_name'] . ' income grew ' . $r['income_change'] . '% vs the previous ' . $days . ' days.'];
                    }
                    if ($r['expense_change'] !== null && $r['expense_change'] >= 25) {
                        $insights[] = ['type' => 'warn', 'text' => $r['branch_name'] . ' expenses rose ' . $r['expense_change'] . '% vs the previous period.'];
                    }
                }
                $insights[] = ['type' => 'info', 'text' => 'Company total: income Rs.' . number_format($totIncome) . ', expense Rs.' . number_format($totExpense)
                    . ', net Rs.' . number_format($totIncome - $totExpense) . ' over ' . $days . ' days (avg Rs.' . number_format(($totIncome - $totExpense) / $days) . ' / day).'];
            }
            $cats = [];
            foreach ($cur as $r) {
                $k = $r['kind'] . '|' . $r['category'];
                $cats[$k] = ($cats[$k] ?? ['kind' => $r['kind'], 'category' => $r['category'], 'amount' => 0]);
                $cats[$k]['amount'] += $r['amount'];
            }
            $cats = array_values($cats);
            usort($cats, function ($a, $b) {
                return $b['amount'] <=> $a['amount'];
            });
            return response()->json([
                'status' => 'Success', 'from' => $from, 'to' => $to, 'prev_from' => $prevFrom, 'prev_to' => $prevTo,
                'data' => $rows, 'categories' => $cats, 'insights' => $insights,
                'totals' => ['income' => $totIncome, 'expense' => $totExpense, 'net' => $totIncome - $totExpense, 'branches' => count($rows)],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    // ---------------------------------------------------------------- Petty cash branch report (point 23)

    public function petty_cash_branch_report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            [$from, $to] = $this->range($request);
            $rows = array_values(array_filter($this->movements($request, $from, $to), function ($r) {
                return $r['bucket'] === 'petty';
            }));
            if (!empty($request->category)) {
                $rows = array_values(array_filter($rows, function ($r) use ($request) {
                    return $r['category'] === $request->category;
                }));
            }
            $branches = $this->branchNames();
            $names = $this->employeeNames(array_column($rows, 'employee_id'));
            $byBranch = [];
            $byType = [];
            foreach ($rows as &$r) {
                $r['branch_name'] = $branches[$r['branch_id']] ?? '';
                $r['employee_name'] = $names[$r['employee_id']] ?? '';
                $b = $r['branch_id'];
                if (!isset($byBranch[$b])) {
                    $byBranch[$b] = ['branch_name' => $r['branch_name'] ?: 'Branch ' . $b, 'income' => 0, 'expense' => 0, 'count' => 0];
                }
                $byBranch[$b][$r['kind']] += $r['amount'];
                $byBranch[$b]['count']++;
                if ($r['kind'] === 'expense') {
                    $byType[$r['category']] = ($byType[$r['category']] ?? 0) + $r['amount'];
                }
            }
            unset($r);
            arsort($byType);
            usort($rows, function ($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
            $branchRows = array_values(array_map(function ($b) {
                $b['balance'] = $b['income'] - $b['expense'];
                return $b;
            }, $byBranch));
            return response()->json([
                'status' => 'Success',
                'data' => $rows,
                'branches' => $branchRows,
                'types' => array_map(function ($k, $v) { return ['type' => $k, 'amount' => $v]; }, array_keys($byType), array_values($byType)),
                'totals' => [
                    'income' => array_sum(array_column($branchRows, 'income')),
                    'expense' => array_sum(array_column($branchRows, 'expense')),
                    'entries' => count($rows),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    // ---------------------------------------------------------------- Allowances (points 31, 32)

    public function day_allowance_report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            [$from, $to] = $this->range($request);
            $eq = DB::table('employees');
            $this->notDeleted($this->scoped($eq, $request), 'employees');
            if (!empty($request->employee_id)) {
                $eq->where('id', $request->employee_id);
            }
            $emps = $eq->get(['id', 'first_name', 'last_name', 'branch_id', 'day_allowance'])->keyBy('id');
            $att = collect();
            if (Schema::hasTable('employee_attendances') && $emps->count()) {
                $att = DB::table('employee_attendances')->whereIn('employee_id', $emps->keys()->all())
                    ->where('attendance_type', 1)->whereBetween('att_date', [$from, $to])
                    ->get(['employee_id', 'att_date']);
            }
            $given = DB::table('employee_allowances')->where('type', 'day')->whereBetween('allowance_date', [$from, $to])
                ->whereIn('employee_id', $emps->keys()->all())->get()
                ->groupBy(function ($r) { return $r->employee_id . ':' . $r->allowance_date; });
            $branches = $this->branchNames();
            $days = [];
            $perEmp = [];
            foreach ($att->unique(function ($a) { return $a->employee_id . ':' . substr($a->att_date, 0, 10); }) as $a) {
                $e = $emps[$a->employee_id] ?? null;
                if (!$e) {
                    continue;
                }
                $d = substr($a->att_date, 0, 10);
                $rate = (float) ($e->day_allowance ?? 200) ?: 200;
                $paid = isset($given[$e->id . ':' . $d]);
                $name = trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''));
                $days[] = ['date' => $d, 'employee_id' => $e->id, 'employee_name' => $name, 'branch_name' => $branches[$e->branch_id] ?? '',
                    'amount' => $rate, 'paid' => $paid];
                if (!isset($perEmp[$e->id])) {
                    $perEmp[$e->id] = ['employee_id' => $e->id, 'employee_name' => $name, 'branch_name' => $branches[$e->branch_id] ?? '',
                        'rate' => $rate, 'days' => 0, 'amount' => 0, 'paid' => 0];
                }
                $perEmp[$e->id]['days']++;
                $perEmp[$e->id]['amount'] += $rate;
                $perEmp[$e->id]['paid'] += $paid ? $rate : 0;
            }
            usort($days, function ($a, $b) {
                return [$b['date'], $a['employee_name']] <=> [$a['date'], $b['employee_name']];
            });
            $perEmp = array_values($perEmp);
            return response()->json(['status' => 'Success', 'data' => $days, 'employees' => $perEmp, 'totals' => [
                'days' => count($days),
                'amount' => array_sum(array_column($perEmp, 'amount')),
                'paid' => array_sum(array_column($perEmp, 'paid')),
            ]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function mark_day_allowance_paid(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $items = is_array($request->items) ? $request->items : [];
            $n = 0;
            foreach ($items as $it) {
                $emp = DB::table('employees')->where('id', $it['employee_id'] ?? 0)->first();
                $date = isset($it['date']) ? date('Y-m-d', strtotime($it['date'])) : null;
                if (!$emp || !$date) {
                    continue;
                }
                $exists = DB::table('employee_allowances')->where('type', 'day')->where('employee_id', $emp->id)->where('allowance_date', $date)->exists();
                if ($exists) {
                    continue;
                }
                DB::table('employee_allowances')->insert([
                    'tenant_id' => (int) ($request->tenant_id ?: ($emp->tenant_id ?? 0)), 'branch_id' => (int) $emp->branch_id,
                    'employee_id' => $emp->id, 'type' => 'day', 'allowance_date' => $date,
                    'amount' => (float) ($it['amount'] ?? ($emp->day_allowance ?? 200)), 'mode' => $request->mode === 'bank' ? 'bank' : 'cash',
                    'remarks' => 'Daily allowance', 'created_by' => (int) $request->created_by, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $n++;
            }
            return response()->json(['status' => 'Success', 'msg' => $n . ' day allowance(s) marked paid'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function save_day_allowance_rate(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $rate = (float) $request->day_allowance;
            if ($rate < 0) {
                return response()->json(['status' => 'Error', 'msg' => 'Invalid amount'], 200);
            }
            DB::table('employees')->where('id', $request->employee_id)->update(['day_allowance' => $rate]);
            return response()->json(['status' => 'Success', 'msg' => 'Day allowance saved'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function petrol_allowances(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            [$from, $to] = $this->range($request);
            $q = DB::table('employee_allowances')->where('type', 'petrol')->whereBetween('allowance_date', [$from, $to]);
            $this->scoped($q, $request);
            if (!empty($request->employee_id)) {
                $q->where('employee_id', $request->employee_id);
            }
            $rows = $q->orderBy('allowance_date', 'desc')->orderBy('id', 'desc')->get();
            $names = $this->employeeNames(array_merge($rows->pluck('employee_id')->all(), $rows->pluck('created_by')->all()));
            $branches = $this->branchNames();
            $byMonth = [];
            $byEmp = [];
            foreach ($rows as $r) {
                $r->employee_name = $names[$r->employee_id] ?? ('Employee #' . $r->employee_id);
                $r->created_by_name = $names[$r->created_by] ?? '';
                $r->branch_name = $branches[$r->branch_id] ?? '';
                $m = substr($r->allowance_date, 0, 7);
                $byMonth[$m] = $byMonth[$m] ?? ['month' => $m, 'amount' => 0, 'km' => 0, 'count' => 0];
                $byMonth[$m]['amount'] += (float) $r->amount;
                $byMonth[$m]['km'] += (float) $r->km;
                $byMonth[$m]['count']++;
                $byEmp[$r->employee_id] = $byEmp[$r->employee_id] ?? ['employee_name' => $r->employee_name, 'branch_name' => $r->branch_name, 'amount' => 0, 'km' => 0, 'days' => []];
                $byEmp[$r->employee_id]['amount'] += (float) $r->amount;
                $byEmp[$r->employee_id]['km'] += (float) $r->km;
                $byEmp[$r->employee_id]['days'][$r->allowance_date] = true;
            }
            krsort($byMonth);
            $byEmp = array_values(array_map(function ($e) {
                $e['days'] = count($e['days']);
                return $e;
            }, $byEmp));
            usort($byEmp, function ($a, $b) {
                return $b['amount'] <=> $a['amount'];
            });
            return response()->json(['status' => 'Success', 'data' => $rows, 'months' => array_values($byMonth), 'employees' => $byEmp, 'totals' => [
                'amount' => (float) $rows->sum('amount'), 'km' => (float) $rows->sum('km'), 'entries' => $rows->count(),
            ]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function save_petrol_allowance(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $emp = DB::table('employees')->where('id', $request->employee_id)->first();
            if (!$emp) {
                return response()->json(['status' => 'Error', 'msg' => 'Select an employee'], 200);
            }
            $km = (float) $request->km;
            $rate = (float) $request->rate;
            $amount = (float) $request->amount ?: round($km * $rate, 2);
            if ($amount <= 0 || !$request->allowance_date) {
                return response()->json(['status' => 'Error', 'msg' => 'Enter date and amount (or km × rate)'], 200);
            }
            $data = [
                'tenant_id' => (int) ($request->tenant_id ?: ($emp->tenant_id ?? 0)),
                'branch_id' => (int) ($request->branch_id ?: $emp->branch_id),
                'employee_id' => $emp->id, 'type' => 'petrol',
                'allowance_date' => date('Y-m-d', strtotime($request->allowance_date)),
                'km' => $km, 'rate' => $rate, 'amount' => $amount,
                'mode' => $request->mode === 'bank' ? 'bank' : 'cash',
                'remarks' => $request->remarks, 'updated_at' => now(),
            ];
            if ($request->id) {
                DB::table('employee_allowances')->where('id', $request->id)->where('type', 'petrol')->update($data);
                return response()->json(['status' => 'Success', 'msg' => 'Petrol allowance updated'], 200);
            }
            $data['created_by'] = (int) $request->created_by;
            $data['created_at'] = now();
            DB::table('employee_allowances')->insert($data);
            return response()->json(['status' => 'Success', 'msg' => 'Petrol allowance saved'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function delete_petrol_allowance(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            DB::table('employee_allowances')->where('id', $request->id)->where('type', 'petrol')->delete();
            return response()->json(['status' => 'Success', 'msg' => 'Deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }
}
