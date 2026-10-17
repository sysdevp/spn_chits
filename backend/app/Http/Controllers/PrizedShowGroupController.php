<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ChitDetail;
use App\Models\Scheme;

class PrizedShowGroupController extends Controller
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
        if (!Schema::hasTable('prized_show_groups')) {
            Schema::create('prized_show_groups', function ($table) {
                $table->bigIncrements('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('scheme_id')->default(0);
                $table->string('group_name')->nullable();
                $table->string('scheme_name')->nullable();
                $table->decimal('chit_value', 14, 2)->default(0);
                $table->integer('months')->default(0);
                $table->string('remarks')->nullable();
                $table->integer('status')->default(1);
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('prized_show_members')) {
            Schema::create('prized_show_members', function ($table) {
                $table->bigIncrements('id');
                $table->integer('group_id')->default(0);
                $table->integer('enrollment_id')->default(0);
                $table->integer('customer_id')->default(0);
                $table->string('customer_name')->nullable();
                $table->string('mobile')->nullable();
                $table->string('customer_code')->nullable();
                $table->string('ar_no')->nullable();
                $table->integer('ticket_no')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('prized_show_months')) {
            Schema::create('prized_show_months', function ($table) {
                $table->bigIncrements('id');
                $table->integer('group_id')->default(0);
                $table->integer('month_no')->default(0);
                $table->integer('enrollment_id')->default(0);
                $table->integer('customer_id')->default(0);
                $table->string('customer_name')->nullable();
                $table->string('ar_no')->nullable();
                $table->decimal('amount', 14, 2)->default(0);
                $table->string('remarks')->nullable();
                $table->integer('marked_by')->nullable();
                $table->timestamp('marked_at')->nullable();
                $table->timestamps();
                $table->unique(['group_id', 'month_no']);
            });
        }
    }

    public function list_scheme_customers(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->scheme_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Scheme is required', 'data' => []], 200);
            }
            $query = ChitDetail::with(['branch_det' => function ($q) {
                $q->withTrashed();
            }, 'customer_det'])->where('chit_type', 3)->where('scheme_id', $request->scheme_id);
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (string) $request->branch_id !== '0') {
                $query->where('branch_id', $request->branch_id);
            }
            $enrolls = $query->orderBy('branch_id', 'asc')->orderBy('ticket_no', 'asc')->orderBy('id', 'desc')->get();
            $dueMap = $this->runningDueNos($enrolls->pluck('id')->all());
            $branchNames = [];
            $rows = [];
            foreach ($enrolls as $enrol) {
                $customer = $enrol->customer_det;
                $name = '';
                $mobile = '';
                $code = '';
                $customerBranchId = 0;
                if ($customer) {
                    $name = trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? ''));
                    $mobile = $customer->mobile_no ?? ($customer->mobile ?? '');
                    $code = $customer->customer_code ?? '';
                    $customerBranchId = (int) ($customer->branch_id ?? 0);
                }
                $branchId = (int) ($enrol->branch_id ?: $customerBranchId);
                $branchName = '';
                if ($enrol->branch_det && !empty($enrol->branch_det->branch_name)) {
                    $branchName = $enrol->branch_det->branch_name;
                } elseif ($branchId > 0) {
                    if (!array_key_exists($branchId, $branchNames)) {
                        $branchNames[$branchId] = (string) (DB::table('branches')->where('id', $branchId)->value('branch_name') ?: '');
                    }
                    $branchName = $branchNames[$branchId];
                }
                $dueNo = $dueMap[$enrol->id] ?? 1;
                $rows[] = [
                    'enrollment_id' => $enrol->id,
                    'customer_id' => $enrol->customer_id,
                    'customer_name' => $name,
                    'mobile' => $mobile,
                    'customer_code' => $code,
                    'ar_no' => $enrol->ar_no,
                    'ticket_no' => $enrol->ticket_no ?? null,
                    'due_no' => $dueNo,
                    'prized_status' => (int) ($enrol->prized_status ?? 0),
                    'scheme_id' => $enrol->scheme_id,
                    'branch_id' => $branchId,
                    'branch_name' => $branchName,
                ];
            }
            usort($rows, function ($a, $b) {
                $branchCmp = strcasecmp((string) ($a['branch_name'] ?? ''), (string) ($b['branch_name'] ?? ''));
                if ($branchCmp !== 0) {
                    return $branchCmp;
                }
                $dueA = is_numeric($a['due_no']) ? (float) $a['due_no'] : 0;
                $dueB = is_numeric($b['due_no']) ? (float) $b['due_no'] : 0;
                if ($dueA == $dueB) {
                    return strcasecmp((string) ($a['customer_name'] ?? ''), (string) ($b['customer_name'] ?? ''));
                }
                return $dueA < $dueB ? -1 : 1;
            });
            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load customers', 'data' => []], 200);
        }
    }

    public function store_group(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->tenant_id) || empty($request->scheme_id) || empty($request->group_name)) {
                return response()->json(['status' => 'Error', 'msg' => 'Group name and scheme are required'], 200);
            }
            $members = $this->normalizeMembers($request->members);
            if (count($members) < 1) {
                return response()->json(['status' => 'Error', 'msg' => 'Select at least one customer for the group'], 200);
            }
            $scheme = Scheme::find($request->scheme_id);
            $groupId = DB::table('prized_show_groups')->insertGetId([
                'tenant_id' => (int) $request->tenant_id,
                'branch_id' => (int) ($request->branch_id ?: 0),
                'scheme_id' => (int) $request->scheme_id,
                'group_name' => $request->group_name,
                'scheme_name' => $request->scheme_name ?: ($scheme ? ($scheme->chit_value . ' / ' . ($scheme->chit_letter ?? '')) : ''),
                'chit_value' => $request->chit_value ?: ($scheme ? $scheme->chit_value : 0),
                'months' => (int) ($request->months ?: ($scheme ? $scheme->no_of_months : 0)),
                'remarks' => $request->remarks ?: '',
                'status' => 1,
                'created_by' => $request->created_by ?: 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($members as $member) {
                DB::table('prized_show_members')->insert([
                    'group_id' => $groupId,
                    'enrollment_id' => (int) $member['enrollment_id'],
                    'customer_id' => (int) ($member['customer_id'] ?? 0),
                    'customer_name' => $member['customer_name'] ?? '',
                    'mobile' => $member['mobile'] ?? '',
                    'customer_code' => $member['customer_code'] ?? '',
                    'ar_no' => $member['ar_no'] ?? '',
                    'ticket_no' => $member['ticket_no'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return response()->json(['status' => 'Success', 'msg' => 'Prized show group created', 'data' => ['id' => $groupId]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to save group'], 200);
        }
    }

    public function update_group(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found'], 200);
            }
            $group = DB::table('prized_show_groups')->where('id', $request->id)->first();
            if (!$group) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found'], 200);
            }
            $members = $this->normalizeMembers($request->members);
            if (count($members) < 1) {
                return response()->json(['status' => 'Error', 'msg' => 'Select at least one customer for the group'], 200);
            }
            DB::table('prized_show_groups')->where('id', $request->id)->update([
                'group_name' => $request->group_name ?: $group->group_name,
                'remarks' => $request->remarks !== null ? $request->remarks : $group->remarks,
                'updated_at' => now(),
            ]);
            DB::table('prized_show_members')->where('group_id', $request->id)->delete();
            foreach ($members as $member) {
                DB::table('prized_show_members')->insert([
                    'group_id' => (int) $request->id,
                    'enrollment_id' => (int) $member['enrollment_id'],
                    'customer_id' => (int) ($member['customer_id'] ?? 0),
                    'customer_name' => $member['customer_name'] ?? '',
                    'mobile' => $member['mobile'] ?? '',
                    'customer_code' => $member['customer_code'] ?? '',
                    'ar_no' => $member['ar_no'] ?? '',
                    'ticket_no' => $member['ticket_no'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return response()->json(['status' => 'Success', 'msg' => 'Group updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to update group'], 200);
        }
    }

    public function list_groups(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $query = DB::table('prized_show_groups')->orderBy('id', 'desc');
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (string) $request->branch_id !== '0') {
                $query->where('branch_id', $request->branch_id);
            }
            $groups = $query->get();
            $rows = [];
            $sno = 1;
            foreach ($groups as $group) {
                $memberCount = DB::table('prized_show_members')->where('group_id', $group->id)->count();
                $markedCount = DB::table('prized_show_months')->where('group_id', $group->id)->count();
                $rows[] = [
                    'sno' => $sno++,
                    'id' => $group->id,
                    'group_name' => $group->group_name,
                    'scheme_id' => $group->scheme_id,
                    'scheme_name' => $group->scheme_name,
                    'chit_value' => $group->chit_value,
                    'months' => $group->months,
                    'branch_id' => $group->branch_id,
                    'remarks' => $group->remarks,
                    'member_count' => $memberCount,
                    'marked_count' => $markedCount,
                    'created_at' => $group->created_at,
                ];
            }
            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load groups', 'data' => []], 200);
        }
    }

    public function get_group(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $id = $request->id ?: (isset($request->conditions['id']) ? $request->conditions['id'] : null);
            if (!$id) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found', 'data' => []], 200);
            }
            $group = DB::table('prized_show_groups')->where('id', $id)->first();
            if (!$group) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found', 'data' => []], 200);
            }
            $members = DB::table('prized_show_members')->where('group_id', $id)->orderBy('id')->get();
            $dueMap = $this->runningDueNos($members->pluck('enrollment_id')->all());
            $prizedMap = DB::table('chit_details')->whereIn('id', $members->pluck('enrollment_id')->all())->pluck('prized_status', 'id')->all();
            $members = $members->map(function ($m) use ($dueMap, $prizedMap) {
                $m->due_no = $dueMap[$m->enrollment_id] ?? 1;
                $m->prized_status = (int) ($prizedMap[$m->enrollment_id] ?? 0);
                return $m;
            });
            $months = DB::table('prized_show_months')->where('group_id', $id)->orderBy('month_no')->get();
            $monthMap = [];
            foreach ($months as $month) {
                $monthMap[(int) $month->month_no] = $month;
            }
            $schedule = [];
            $totalMonths = max(1, (int) $group->months);
            for ($i = 1; $i <= $totalMonths; $i++) {
                $marked = isset($monthMap[$i]) ? $monthMap[$i] : null;
                $schedule[] = [
                    'month_no' => $i,
                    'enrollment_id' => $marked ? $marked->enrollment_id : null,
                    'customer_id' => $marked ? $marked->customer_id : null,
                    'customer_name' => $marked ? $marked->customer_name : '',
                    'ar_no' => $marked ? $marked->ar_no : '',
                    'amount' => $marked ? $marked->amount : 0,
                    'remarks' => $marked ? $marked->remarks : '',
                    'marked_at' => $marked ? $marked->marked_at : null,
                    'is_marked' => $marked ? 1 : 0,
                ];
            }
            $data = [
                'id' => $group->id,
                'group_name' => $group->group_name,
                'scheme_id' => $group->scheme_id,
                'scheme_name' => $group->scheme_name,
                'chit_value' => $group->chit_value,
                'months' => $group->months,
                'branch_id' => $group->branch_id,
                'tenant_id' => $group->tenant_id,
                'remarks' => $group->remarks,
                'members' => $members,
                'schedule' => $schedule,
            ];
            return response()->json(['status' => 'Success', 'msg' => '', 'data' => [$data]], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load group', 'data' => []], 200);
        }
    }

    public function mark_month(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $groupId = (int) $request->group_id;
            $monthNo = (int) $request->month_no;
            $enrollmentId = (int) $request->enrollment_id;
            $amount = (float) ($request->amount ?: 0);
            if (!$groupId || !$monthNo || !$enrollmentId) {
                return response()->json(['status' => 'Error', 'msg' => 'Group, month and prized customer are required'], 200);
            }
            if ($amount <= 0) {
                return response()->json(['status' => 'Error', 'msg' => 'Prized amount is required'], 200);
            }
            $group = DB::table('prized_show_groups')->where('id', $groupId)->first();
            if (!$group) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found'], 200);
            }
            if ($monthNo < 1 || $monthNo > (int) $group->months) {
                return response()->json(['status' => 'Error', 'msg' => 'Invalid month for this scheme'], 200);
            }
            $member = DB::table('prized_show_members')
                ->where('group_id', $groupId)
                ->where('enrollment_id', $enrollmentId)
                ->first();
            if (!$member) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer is not in this group'], 200);
            }
            $already = DB::table('prized_show_months')
                ->where('group_id', $groupId)
                ->where('enrollment_id', $enrollmentId)
                ->where('month_no', '!=', $monthNo)
                ->first();
            if ($already) {
                return response()->json(['status' => 'Error', 'msg' => 'This customer is already marked prized in month ' . $already->month_no], 200);
            }
            $payload = [
                'group_id' => $groupId,
                'month_no' => $monthNo,
                'enrollment_id' => $enrollmentId,
                'customer_id' => (int) $member->customer_id,
                'customer_name' => $member->customer_name,
                'ar_no' => $member->ar_no,
                'amount' => $amount,
                'remarks' => $request->remarks ?: '',
                'marked_by' => $request->marked_by ?: 0,
                'marked_at' => now(),
                'updated_at' => now(),
            ];
            $existing = DB::table('prized_show_months')->where('group_id', $groupId)->where('month_no', $monthNo)->first();
            if ($existing) {
                DB::table('prized_show_months')->where('id', $existing->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('prized_show_months')->insert($payload);
            }
            return response()->json(['status' => 'Success', 'msg' => 'Prized customer saved for month ' . $monthNo], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to mark prized month'], 200);
        }
    }

    public function clear_month(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->group_id) || empty($request->month_no)) {
                return response()->json(['status' => 'Error', 'msg' => 'Group and month are required'], 200);
            }
            DB::table('prized_show_months')
                ->where('group_id', $request->group_id)
                ->where('month_no', $request->month_no)
                ->delete();
            return response()->json(['status' => 'Success', 'msg' => 'Month cleared'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to clear month'], 200);
        }
    }

    public function delete_group(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found'], 200);
            }
            DB::table('prized_show_months')->where('group_id', $request->id)->delete();
            DB::table('prized_show_members')->where('group_id', $request->id)->delete();
            DB::table('prized_show_groups')->where('id', $request->id)->delete();
            return response()->json(['status' => 'Success', 'msg' => 'Group deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to delete group'], 200);
        }
    }

    public function report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $query = DB::table('prized_show_groups')->orderBy('id', 'desc');
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (string) $request->branch_id !== '0') {
                $query->where('branch_id', $request->branch_id);
            }
            if (!empty($request->group_id)) {
                $query->where('id', $request->group_id);
            }
            if (!empty($request->scheme_id)) {
                $query->where('scheme_id', $request->scheme_id);
            }
            $groups = $query->get();
            $rows = [];
            foreach ($groups as $group) {
                $months = DB::table('prized_show_months as m')
                    ->leftJoin('chit_details as cd', 'cd.id', '=', 'm.enrollment_id')
                    ->leftJoin('customers as c', 'c.id', '=', 'm.customer_id')
                    ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
                    ->leftJoin('employees as e', 'e.id', '=', 'm.marked_by')
                    ->where('m.group_id', $group->id)->orderBy('m.month_no')
                    ->get(['m.*', 'c.mobile_no', 'c.customer_code', 'b.branch_name', 'cd.date_introduced',
                        DB::raw("TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,''))) as marked_by_name")]);
                $enrollIds = $months->pluck('enrollment_id')->all();
                $dueMap = $this->runningDueNos($enrollIds);
                $dates = $this->auctionDates($months->map(function ($m) { return [$m->enrollment_id, $m->month_no]; })->all());
                $paidMap = $enrollIds ? DB::table('receipts')->where('status', 1)->whereIn('enrollment_id', $enrollIds)
                    ->groupBy('enrollment_id')->select('enrollment_id', DB::raw('SUM(COALESCE(received_amount,0)) as paid'))
                    ->pluck('paid', 'enrollment_id')->all() : [];
                $desk = $enrollIds && Schema::hasTable('payment_desk_requests')
                    ? DB::table('payment_desk_requests')->where('kind', 'prized')->where('status', '!=', 'rejected')
                        ->whereIn('enrollment_id', $enrollIds)->get()->keyBy('enrollment_id')
                    : collect();
                $monthRows = [];
                foreach ($months as $month) {
                    $d = $desk[$month->enrollment_id] ?? null;
                    $monthRows[] = [
                        'month_no' => $month->month_no,
                        'auction_date' => $dates[$month->enrollment_id . ':' . $month->month_no] ?? '',
                        'customer_name' => $month->customer_name,
                        'customer_code' => $month->customer_code,
                        'mobile' => $month->mobile_no,
                        'branch_name' => $month->branch_name,
                        'ar_no' => $month->ar_no,
                        'due_no' => $dueMap[$month->enrollment_id] ?? 1,
                        'joined_on' => $month->date_introduced,
                        'customer_paid' => round((float) ($paidMap[$month->enrollment_id] ?? 0), 2),
                        'amount' => $month->amount,
                        'payment_status' => $d ? $d->status : 'not_requested',
                        'payment_mode' => $d->payment_mode ?? '',
                        'payment_date' => $d->payment_date ?? '',
                        'remarks' => $month->remarks,
                        'marked_at' => $month->marked_at,
                        'marked_by_name' => $month->marked_by_name,
                    ];
                }
                $rows[] = [
                    'id' => $group->id,
                    'group_name' => $group->group_name,
                    'scheme_name' => $group->scheme_name,
                    'chit_value' => $group->chit_value,
                    'months' => $group->months,
                    'member_count' => DB::table('prized_show_members')->where('group_id', $group->id)->count(),
                    'marked_count' => count($monthRows),
                    'pending_months' => max(0, (int) $group->months - count($monthRows)),
                    'total_amount' => collect($monthRows)->sum('amount'),
                    'paid_out' => collect($monthRows)->where('payment_status', 'approved')->sum('amount'),
                    'schedule' => $monthRows,
                ];
            }
            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load report', 'data' => []], 200);
        }
    }

    /** Running due no per enrollment = latest installment whose auction date has passed (min 1). */
    private function runningDueNos(array $enrollmentIds)
    {
        $out = [];
        $ids = array_values(array_filter(array_map('intval', $enrollmentIds)));
        if (!$ids || !Schema::hasTable('commitment_auctions')) {
            return $out;
        }
        foreach (array_chunk($ids, 1000) as $chunk) {
            $rows = DB::table('commitment_auctions')
                ->whereIn('enrollment_id', $chunk)
                ->whereNull('deleted_at')
                ->whereDate('auction_date', '<=', date('Y-m-d'))
                ->groupBy('enrollment_id')
                ->select('enrollment_id', DB::raw('MAX(installment_no) as due_no'))
                ->pluck('due_no', 'enrollment_id');
            foreach ($rows as $k => $v) {
                $out[$k] = max(1, (int) $v);
            }
        }
        return $out;
    }

    private function auctionDates(array $pairs)
    {
        $out = [];
        if (!$pairs || !Schema::hasTable('commitment_auctions')) {
            return $out;
        }
        $ids = array_values(array_unique(array_map(function ($p) { return (int) $p[0]; }, $pairs)));
        $rows = DB::table('commitment_auctions')->whereIn('enrollment_id', $ids)->whereNull('deleted_at')
            ->get(['enrollment_id', 'installment_no', 'auction_date']);
        foreach ($rows as $r) {
            $out[$r->enrollment_id . ':' . $r->installment_no] = $r->auction_date;
        }
        return $out;
    }

    /**
     * SPN Auction report: who is prized and for how much, by month / auction date, scheme-wise.
     * Sources: prized show group months and prized payment desk allotments.
     */
    public function auction_report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $month = trim((string) $request->month);
            $auctionDate = trim((string) $request->auction_date);
            $branchId = (int) ($request->branch_id ?: 0);
            $schemeId = (int) ($request->scheme_id ?: 0);

            $q = DB::table('prized_show_months as m')
                ->join('prized_show_groups as g', 'g.id', '=', 'm.group_id')
                ->leftJoin('chit_details as cd', 'cd.id', '=', 'm.enrollment_id')
                ->leftJoin('customers as c', 'c.id', '=', 'm.customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
                ->leftJoin('schemes as s', 's.id', '=', 'g.scheme_id');
            if (!empty($request->tenant_id)) {
                $q->where('g.tenant_id', $request->tenant_id);
            }
            if ($branchId) {
                $q->where('cd.branch_id', $branchId);
            }
            if ($schemeId) {
                $q->where('g.scheme_id', $schemeId);
            }
            $rows = $q->get([
                'm.id', 'm.group_id', 'm.month_no', 'm.enrollment_id', 'm.customer_id', 'm.amount', 'm.remarks', 'm.marked_at',
                'g.group_name', 'g.scheme_id', 'g.months',
                's.chit_value', 's.no_of_months', 's.chit_letter',
                'cd.ar_no', 'cd.branch_id', 'cd.date_introduced',
                'c.name', 'c.last_name', 'c.mobile_no', 'c.customer_code',
                'b.branch_name',
            ]);
            $dates = $this->auctionDates($rows->map(function ($r) { return [$r->enrollment_id, $r->month_no]; })->all());

            $desk = Schema::hasTable('payment_desk_requests')
                ? DB::table('payment_desk_requests')->where('kind', 'prized')->where('status', '!=', 'rejected')
                    ->whereIn('enrollment_id', $rows->pluck('enrollment_id')->all())->get()->keyBy('enrollment_id')
                : collect();

            $out = [];
            foreach ($rows as $r) {
                $date = $dates[$r->enrollment_id . ':' . $r->month_no] ?? ($r->marked_at ? substr($r->marked_at, 0, 10) : '');
                if ($month !== '' && substr((string) $date, 0, 7) !== $month) {
                    continue;
                }
                if ($auctionDate !== '' && substr((string) $date, 0, 10) !== $auctionDate) {
                    continue;
                }
                $d = $desk[$r->enrollment_id] ?? null;
                $out[] = [
                    'group_name' => $r->group_name,
                    'scheme_id' => $r->scheme_id,
                    'scheme' => $r->chit_value ? ('Rs.' . number_format((float) $r->chit_value) . ' / ' . $r->no_of_months . 'M' . ($r->chit_letter ? ' ' . $r->chit_letter : '')) : '',
                    'chit_value' => (float) $r->chit_value,
                    'month_no' => (int) $r->month_no,
                    'auction_date' => $date ? substr((string) $date, 0, 10) : '',
                    'customer_name' => trim(($r->name ?? '') . ' ' . ($r->last_name ?? '')),
                    'customer_code' => $r->customer_code,
                    'mobile' => $r->mobile_no,
                    'ar_no' => $r->ar_no,
                    'branch_id' => $r->branch_id,
                    'branch_name' => $r->branch_name,
                    'prized_amount' => (float) $r->amount,
                    'remarks' => $r->remarks,
                    'payment_status' => $d ? $d->status : 'not_requested',
                    'paid_amount' => $d && $d->status === 'approved' ? (float) $d->net_amount : 0,
                ];
            }
            usort($out, function ($a, $b) {
                return [$a['auction_date'], $a['scheme'], $a['month_no']] <=> [$b['auction_date'], $b['scheme'], $b['month_no']];
            });

            $schemes = [];
            $monthsSummary = [];
            foreach ($out as $r) {
                $key = $r['scheme'] ?: 'Scheme ' . $r['scheme_id'];
                if (!isset($schemes[$key])) {
                    $schemes[$key] = ['scheme' => $key, 'prized' => 0, 'amount' => 0, 'paid' => 0];
                }
                $schemes[$key]['prized']++;
                $schemes[$key]['amount'] += $r['prized_amount'];
                $schemes[$key]['paid'] += $r['paid_amount'];
                $mk = substr($r['auction_date'], 0, 7) ?: 'Undated';
                if (!isset($monthsSummary[$mk])) {
                    $monthsSummary[$mk] = ['month' => $mk, 'prized' => 0, 'amount' => 0];
                }
                $monthsSummary[$mk]['prized']++;
                $monthsSummary[$mk]['amount'] += $r['prized_amount'];
            }
            ksort($monthsSummary);
            return response()->json([
                'status' => 'Success',
                'data' => $out,
                'schemes' => array_values($schemes),
                'months' => array_values($monthsSummary),
                'totals' => [
                    'prized' => count($out),
                    'amount' => array_sum(array_column($out, 'prized_amount')),
                    'paid' => array_sum(array_column($out, 'paid_amount')),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'data' => []], 200);
        }
    }

    private function normalizeMembers($members)
    {
        if (is_string($members)) {
            $decoded = json_decode($members, true);
            $members = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($members)) {
            return [];
        }
        $out = [];
        $seen = [];
        foreach ($members as $member) {
            if (!is_array($member)) {
                continue;
            }
            $enrollmentId = isset($member['enrollment_id']) ? (int) $member['enrollment_id'] : 0;
            if (!$enrollmentId || isset($seen[$enrollmentId])) {
                continue;
            }
            $seen[$enrollmentId] = true;
            $out[] = $member;
        }
        return $out;
    }
}
