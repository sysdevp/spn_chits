<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customer category (Excellent / Good / Medium / Bad) for every active chit customer.
 * Uses the same scoring rules as the customer ledger (src/app/admin-panel/settings/customer-score.rules.ts).
 */
class CustomerCategoryReportController extends Controller
{
    const SCORE_START = 1000;
    const PENDING_PENALTY_MAX = 400;
    const LATE_INSTALLMENT_POINTS = 20;
    const LATE_DAY_POINTS = 1;
    const LATE_DAYS_PER_INSTALLMENT = 10;
    const LATE_PENALTY_MAX = 400;

    private function switchTenantDb(Request $request)
    {
        $db = $request->db ?: $request->database_name;
        if ($db) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $db);
            DB::reconnect('mysql');
        }
    }

    public static function grade($score)
    {
        if ($score >= 850) {
            return 'Excellent';
        }
        if ($score >= 700) {
            return 'Good';
        }
        if ($score >= 500) {
            return 'Medium';
        }
        return 'Bad';
    }

    public static function score($paid, $pending, $lateInstallments, $lateDays)
    {
        $paid = max(0, (float) $paid);
        $pending = max(0, (float) $pending);
        $lateInstallments = max(0, (int) $lateInstallments);
        $countedLateDays = min(max(0, (int) $lateDays), $lateInstallments * self::LATE_DAYS_PER_INSTALLMENT);
        $base = $paid + $pending;
        $pendingPenalty = $base > 0 ? (int) round(($pending / $base) * self::PENDING_PENALTY_MAX) : 0;
        $latePenalty = min(self::LATE_PENALTY_MAX, $lateInstallments * self::LATE_INSTALLMENT_POINTS + $countedLateDays * self::LATE_DAY_POINTS);
        return max(0, min(self::SCORE_START, self::SCORE_START - $pendingPenalty - $latePenalty));
    }

    public function report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $tenant = (int) $request->tenant_id;
            $hasPending = Schema::hasColumn('chit_details', 'pending_amount');

            $q = DB::table('chit_details as cd')
                ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
                ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
                ->where('cd.chit_type', 3)
                ->where('cd.status', 1)
                ->whereNull('cd.deleted_at');
            if ($tenant) {
                $q->where('cd.tenant_id', $tenant);
            }
            if (!empty($request->branch_id)) {
                $q->where('cd.branch_id', $request->branch_id);
            } elseif (!empty($request->branch_ids) && is_array($request->branch_ids)) {
                $q->whereIn('cd.branch_id', $request->branch_ids);
            }
            if (!empty($request->scheme_id)) {
                $q->where('cd.scheme_id', $request->scheme_id);
            }

            $enrolls = $q->get([
                'cd.id', 'cd.customer_id', 'cd.branch_id', 'cd.ar_no',
                $hasPending ? 'cd.pending_amount' : DB::raw('0 as pending_amount'),
                'c.name', 'c.last_name', 'c.customer_code', 'c.mobile_no',
                'b.branch_name', 's.chit_value', 's.no_of_months', 's.monthly_due',
            ]);

            $paidMap = [];
            if ($enrolls->count()) {
                foreach (array_chunk($enrolls->pluck('id')->all(), 1000) as $chunk) {
                    $rows = DB::table('receipts')->where('status', 1)->whereIn('enrollment_id', $chunk)
                        ->groupBy('enrollment_id')
                        ->select('enrollment_id', DB::raw('SUM(COALESCE(received_amount,0)) as paid'))
                        ->pluck('paid', 'enrollment_id');
                    foreach ($rows as $k => $v) {
                        $paidMap[$k] = (float) $v;
                    }
                }
            }

            $customers = [];
            foreach ($enrolls as $e) {
                $cid = (int) $e->customer_id;
                if (!isset($customers[$cid])) {
                    $customers[$cid] = [
                        'customer_id' => $cid,
                        'customer' => trim(($e->name ?? '') . ' ' . ($e->last_name ?? '')),
                        'customer_code' => $e->customer_code,
                        'mobile' => $e->mobile_no,
                        'branch' => $e->branch_name,
                        'schemes' => [],
                        'chits' => 0,
                        'paid' => 0.0,
                        'pending' => 0.0,
                        'late_installments' => 0,
                    ];
                }
                $pending = max(0, (float) $e->pending_amount);
                $due = (float) $e->monthly_due;
                $customers[$cid]['chits']++;
                $customers[$cid]['paid'] += $paidMap[$e->id] ?? 0;
                $customers[$cid]['pending'] += $pending;
                $customers[$cid]['late_installments'] += ($due > 0 && $pending > 0) ? (int) ceil($pending / $due) : 0;
                $label = $this->shortMoney($e->chit_value) . '/' . $e->no_of_months . 'M';
                if (!in_array($label, $customers[$cid]['schemes'], true)) {
                    $customers[$cid]['schemes'][] = $label;
                }
            }

            $this->ensureOverrideTable();
            $overrides = DB::table('customer_score_overrides')->whereIn('customer_id', array_keys($customers))
                ->get()->keyBy('customer_id');

            $gradeFilter = trim((string) $request->grade);
            $counts = ['Excellent' => 0, 'Good' => 0, 'Medium' => 0, 'Bad' => 0];
            $out = [];
            foreach ($customers as $row) {
                $lateDays = $row['late_installments'] * self::LATE_DAYS_PER_INSTALLMENT;
                $score = self::score($row['paid'], $row['pending'], $row['late_installments'], $lateDays);
                $row['manual'] = false;
                $row['score_remarks'] = '';
                if (isset($overrides[$row['customer_id']])) {
                    $score = (int) $overrides[$row['customer_id']]->score;
                    $row['manual'] = true;
                    $row['score_remarks'] = $overrides[$row['customer_id']]->remarks;
                }
                $grade = self::grade($score);
                $counts[$grade]++;
                if ($gradeFilter !== '' && $gradeFilter !== $grade) {
                    continue;
                }
                $row['schemes'] = implode(', ', $row['schemes']);
                $row['paid'] = round($row['paid'], 2);
                $row['pending'] = round($row['pending'], 2);
                $row['score'] = $score;
                $row['grade'] = $grade;
                $out[] = $row;
            }
            usort($out, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            foreach ($out as $i => &$r) {
                $r['sno'] = $i + 1;
            }
            unset($r);

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $out,
                'summary' => $counts,
                'total_customers' => count($customers),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function ensureOverrideTable()
    {
        if (Schema::hasTable('customer_score_overrides')) {
            return;
        }
        Schema::create('customer_score_overrides', function ($table) {
            $table->bigIncrements('id');
            $table->integer('customer_id')->unique();
            $table->integer('score');
            $table->integer('system_score')->nullable();
            $table->text('remarks');
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });
        Schema::create('customer_score_override_logs', function ($table) {
            $table->bigIncrements('id');
            $table->integer('customer_id');
            $table->integer('old_score')->nullable();
            $table->integer('new_score')->nullable();
            $table->text('remarks');
            $table->integer('updated_by')->default(0);
            $table->timestamps();
        });
    }

    public function get_score_override(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $this->ensureOverrideTable();
            $row = DB::table('customer_score_overrides')->where('customer_id', (int) $request->customer_id)->first();
            $logs = DB::table('customer_score_override_logs as l')
                ->leftJoin('employees as e', 'e.id', '=', 'l.updated_by')
                ->where('l.customer_id', (int) $request->customer_id)
                ->orderBy('l.id', 'desc')->limit(20)
                ->get(['l.*', DB::raw("TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,''))) as updated_by_name")]);
            return response()->json(['status' => 'Success', 'data' => $row, 'logs' => $logs], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    /** Admin-only manual score. Send clear=1 to go back to the system score. */
    public function save_score_override(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $this->ensureOverrideTable();
            $customerId = (int) $request->customer_id;
            $remarks = trim((string) $request->remarks);
            if (!$customerId || $remarks === '') {
                return response()->json(['status' => 'Error', 'msg' => 'Customer and remarks are required'], 200);
            }
            $old = DB::table('customer_score_overrides')->where('customer_id', $customerId)->first();
            $now = date('Y-m-d H:i:s');
            $by = (int) ($request->updated_by ?: 0);
            if (!empty($request->clear)) {
                DB::table('customer_score_overrides')->where('customer_id', $customerId)->delete();
                $newScore = null;
            } else {
                $newScore = (int) $request->score;
                if ($newScore < 0 || $newScore > self::SCORE_START) {
                    return response()->json(['status' => 'Error', 'msg' => 'Score must be between 0 and ' . self::SCORE_START], 200);
                }
                DB::table('customer_score_overrides')->updateOrInsert(
                    ['customer_id' => $customerId],
                    ['score' => $newScore, 'system_score' => $request->system_score, 'remarks' => $remarks, 'updated_by' => $by, 'updated_at' => $now, 'created_at' => $old->created_at ?? $now]
                );
            }
            DB::table('customer_score_override_logs')->insert([
                'customer_id' => $customerId,
                'old_score' => $old->score ?? $request->system_score,
                'new_score' => $newScore,
                'remarks' => $remarks,
                'updated_by' => $by,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            return response()->json(['status' => 'Success', 'msg' => $newScore === null ? 'Score reset to system score' : 'Score updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function shortMoney($v)
    {
        $v = (float) $v;
        if ($v >= 100000) {
            return rtrim(rtrim(number_format($v / 100000, 2), '0'), '.') . 'L';
        }
        if ($v >= 1000) {
            return rtrim(rtrim(number_format($v / 1000, 1), '0'), '.') . 'K';
        }
        return (string) $v;
    }
}
