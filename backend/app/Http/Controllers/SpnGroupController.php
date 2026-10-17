<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SpnGroupController extends Controller
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
        if (!Schema::hasTable('spn_groups')) {
            Schema::create('spn_groups', function ($table) {
                $table->bigIncrements('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('scheme_id')->default(0);
                $table->integer('slab_id')->default(0);
                $table->integer('due_no')->default(0);
                $table->string('group_name');
                $table->integer('max_members')->default(0);
                $table->string('formation_mode', 10)->default('manual');
                $table->string('remarks')->nullable();
                $table->integer('status')->default(1);
                $table->integer('created_by')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('spn_group_members')) {
            Schema::create('spn_group_members', function ($table) {
                $table->bigIncrements('id');
                $table->integer('group_id')->default(0);
                $table->integer('enrollment_id')->default(0);
                $table->integer('customer_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('due_no')->default(0);
                $table->timestamps();
                $table->unique('enrollment_id');
            });
        }
    }

    private function branchIds(Request $request): array
    {
        if (!empty($request->branch_id) && (string) $request->branch_id !== '0') {
            return [(int) $request->branch_id];
        }
        if (!empty($request->branch_ids) && is_array($request->branch_ids)) {
            return array_map('intval', $request->branch_ids);
        }
        return [];
    }

    /** Running due no per enrollment = latest installment whose due date has passed (1 if none yet). */
    private function dueNoSub()
    {
        $today = Carbon::today()->toDateString();
        return DB::table('commitment_auctions')
            ->whereNull('deleted_at')
            ->where('auction_date', '<=', $today)
            ->groupBy('enrollment_id')
            ->selectRaw('enrollment_id, MAX(installment_no) as cur_due');
    }

    private function candidateQuery(Request $request)
    {
        $q = DB::table('chit_details as cd')
            ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
            ->leftJoin('slab_dues as sd', 'sd.id', '=', 'cd.slab_id')
            ->leftJoinSub($this->dueNoSub(), 'du', 'du.enrollment_id', '=', 'cd.id')
            ->where('cd.chit_type', 3)
            ->where('cd.status', 1)
            ->whereNull('cd.deleted_at')
            ->where('cd.scheme_id', (int) $request->scheme_id);
        if (!empty($request->tenant_id)) {
            $q->where('cd.tenant_id', $request->tenant_id);
        }
        $branchIds = $this->branchIds($request);
        if (!empty($branchIds)) {
            $q->whereIn('cd.branch_id', $branchIds);
        }
        if (!empty($request->slab_id)) {
            $q->where('cd.slab_id', (int) $request->slab_id);
        }
        if (!empty($request->due_no)) {
            $q->whereRaw('COALESCE(du.cur_due, 1) = ?', [(int) $request->due_no]);
        }
        return $q->select(
            'cd.id as enrollment_id', 'cd.customer_id', 'cd.branch_id', 'cd.ar_no', 'cd.slab_id',
            'b.branch_name', 'sd.slab_name', 'c.name', 'c.last_name', 'c.customer_code', 'c.mobile_no',
            DB::raw('COALESCE(du.cur_due, 1) as due_no')
        );
    }

    private function formatCandidate($row): array
    {
        return [
            'enrollment_id' => (int) $row->enrollment_id,
            'customer_id' => (int) $row->customer_id,
            'customer_name' => trim(($row->name ?? '') . ' ' . ($row->last_name ?? '')),
            'customer_code' => $row->customer_code ?? '',
            'mobile' => $row->mobile_no ?? '',
            'branch_id' => (int) $row->branch_id,
            'branch_name' => $row->branch_name ?? '',
            'slab_name' => $row->slab_name ?? '',
            'ar_no' => $row->ar_no ?? '',
            'due_no' => (int) $row->due_no,
        ];
    }

    public function available(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->scheme_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Select a scheme', 'data' => []], 200);
            }
            $scheme = DB::table('schemes')->where('id', $request->scheme_id)->first();
            $taken = DB::table('spn_group_members')->pluck('enrollment_id')->all();
            $rows = $this->candidateQuery($request)
                ->when(!empty($taken), function ($q) use ($taken) {
                    $q->whereNotIn('cd.id', $taken);
                })
                ->orderBy('due_no')->orderBy('b.branch_name')->orderBy('c.name')
                ->get()->map(function ($r) {
                    return $this->formatCandidate($r);
                })->values();

            $dueSummary = $rows->groupBy('due_no')->map(function ($g, $due) {
                return ['due_no' => (int) $due, 'count' => $g->count()];
            })->values();

            return response()->json([
                'status' => 'Success',
                'data' => $rows,
                'due_summary' => $dueSummary,
                'max_members' => $scheme ? (int) $scheme->no_of_months : 0,
                'chit_value' => $scheme ? (float) $scheme->chit_value : 0,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load customers', 'error' => $e->getMessage(), 'data' => []], 200);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->tenant_id) || empty($request->scheme_id) || empty($request->due_no)) {
                return response()->json(['status' => 'Error', 'msg' => 'Scheme and Due No are required'], 200);
            }
            $scheme = DB::table('schemes')->where('id', $request->scheme_id)->first();
            if (!$scheme) {
                return response()->json(['status' => 'Error', 'msg' => 'Scheme not found'], 200);
            }
            $max = (int) $scheme->no_of_months;
            if ($max <= 0) {
                return response()->json(['status' => 'Error', 'msg' => 'Scheme months is not set'], 200);
            }
            $dueNo = (int) $request->due_no;
            $mode = $request->formation_mode === 'auto' ? 'auto' : 'manual';

            $taken = DB::table('spn_group_members')->pluck('enrollment_id')->all();
            $candidates = $this->candidateQuery($request)
                ->when(!empty($taken), function ($q) use ($taken) {
                    $q->whereNotIn('cd.id', $taken);
                })
                ->orderBy('cd.id')->get()->keyBy('enrollment_id');

            if ($mode === 'auto') {
                $picked = $candidates->take($max)->values();
            } else {
                $ids = array_values(array_unique(array_map('intval', (array) $request->enrollment_ids)));
                if (empty($ids)) {
                    return response()->json(['status' => 'Error', 'msg' => 'Select customers for the group'], 200);
                }
                $missing = array_filter($ids, function ($id) use ($candidates) {
                    return !$candidates->has($id);
                });
                if (!empty($missing)) {
                    return response()->json(['status' => 'Error', 'msg' => 'Some selected chits are not on Due No ' . $dueNo . ' or are already in another group'], 200);
                }
                $picked = collect($ids)->map(function ($id) use ($candidates) {
                    return $candidates->get($id);
                });
            }

            if ($picked->count() < 1) {
                return response()->json(['status' => 'Error', 'msg' => 'No free chits found on Due No ' . $dueNo], 200);
            }
            if ($picked->count() > $max) {
                return response()->json(['status' => 'Error', 'msg' => 'This scheme allows only ' . $max . ' members per group'], 200);
            }

            $groupName = trim((string) $request->group_name);
            if ($groupName === '') {
                $seq = DB::table('spn_groups')->where('scheme_id', $scheme->id)->count() + 1;
                $groupName = round($scheme->chit_value) . '-' . $max . 'M-D' . $dueNo . '-G' . $seq;
            }
            if (DB::table('spn_groups')->where('tenant_id', $request->tenant_id)->where('group_name', $groupName)->exists()) {
                return response()->json(['status' => 'Error', 'msg' => 'Group name already exists'], 200);
            }

            $groupId = DB::transaction(function () use ($request, $scheme, $dueNo, $groupName, $max, $mode, $picked) {
                $branchIds = $this->branchIds($request);
                $groupId = DB::table('spn_groups')->insertGetId([
                    'tenant_id' => (int) $request->tenant_id,
                    'branch_id' => count($branchIds) === 1 ? $branchIds[0] : 0,
                    'scheme_id' => (int) $scheme->id,
                    'slab_id' => (int) ($request->slab_id ?: 0),
                    'due_no' => $dueNo,
                    'group_name' => $groupName,
                    'max_members' => $max,
                    'formation_mode' => $mode,
                    'remarks' => (string) ($request->remarks ?: ''),
                    'status' => 1,
                    'created_by' => (int) ($request->created_by ?: 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                foreach ($picked as $row) {
                    DB::table('spn_group_members')->insert([
                        'group_id' => $groupId,
                        'enrollment_id' => (int) $row->enrollment_id,
                        'customer_id' => (int) $row->customer_id,
                        'branch_id' => (int) $row->branch_id,
                        'due_no' => $dueNo,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                return $groupId;
            });

            return response()->json([
                'status' => 'Success',
                'msg' => 'Group "' . $groupName . '" created with ' . $picked->count() . ' / ' . $max . ' members',
                'data' => ['id' => $groupId, 'group_name' => $groupName],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to create group', 'error' => $e->getMessage()], 200);
        }
    }

    public function list(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $q = DB::table('spn_groups as g')
                ->leftJoin('schemes as s', 's.id', '=', 'g.scheme_id')
                ->leftJoin('slab_dues as sd', 'sd.id', '=', 'g.slab_id')
                ->leftJoin('branches as b', 'b.id', '=', 'g.branch_id')
                ->select('g.*', 's.chit_value', 's.no_of_months', 's.chit_letter', 'sd.slab_name', 'b.branch_name')
                ->orderBy('g.id', 'desc');
            if (!empty($request->tenant_id)) {
                $q->where('g.tenant_id', $request->tenant_id);
            }
            if (!empty($request->scheme_id)) {
                $q->where('g.scheme_id', $request->scheme_id);
            }
            if (!empty($request->slab_id)) {
                $q->where('g.slab_id', $request->slab_id);
            }
            if (!empty($request->due_no)) {
                $q->where('g.due_no', $request->due_no);
            }
            if (!empty($request->branch_id) && (string) $request->branch_id !== '0') {
                $bid = (int) $request->branch_id;
                $q->where(function ($w) use ($bid) {
                    $w->where('g.branch_id', $bid)
                        ->orWhereIn('g.id', DB::table('spn_group_members')->where('branch_id', $bid)->select('group_id'));
                });
            }
            if (!empty($request->search)) {
                $q->where('g.group_name', 'like', '%' . $request->search . '%');
            }
            if (!empty($request->year)) {
                $q->whereYear('g.created_at', (int) $request->year);
            }
            if (!empty($request->month)) {
                $q->whereMonth('g.created_at', (int) $request->month);
            }
            $groups = $q->get();
            $counts = DB::table('spn_group_members')->whereIn('group_id', $groups->pluck('id'))
                ->groupBy('group_id')->selectRaw('group_id, COUNT(*) as n')->pluck('n', 'group_id');

            $rows = [];
            foreach ($groups as $i => $g) {
                $members = (int) ($counts[$g->id] ?? 0);
                $rows[] = [
                    'sno' => $i + 1,
                    'id' => $g->id,
                    'group_name' => $g->group_name,
                    'branch_name' => $g->branch_id ? ($g->branch_name ?: '-') : 'All Branches',
                    'scheme_name' => round((float) $g->chit_value) . ' (' . $g->no_of_months . ' M)',
                    'slab_name' => $g->slab_name ?: 'All Slabs',
                    'due_no' => (int) $g->due_no,
                    'members' => $members,
                    'max_members' => (int) $g->max_members,
                    'vacant' => max(0, (int) $g->max_members - $members),
                    'formation_mode' => ucfirst($g->formation_mode),
                    'created_at' => $g->created_at ? date('d-m-Y', strtotime($g->created_at)) : '',
                ];
            }
            return response()->json(['status' => 'Success', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load groups', 'error' => $e->getMessage(), 'data' => []], 200);
        }
    }

    public function members(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $group = DB::table('spn_groups')->where('id', $request->id)->first();
            if (!$group) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found', 'data' => []], 200);
            }
            $paidSub = DB::table('receipts')->where('status', 1)->groupBy('enrollment_id')
                ->selectRaw('enrollment_id, SUM(received_amount) as paid');
            $rows = DB::table('spn_group_members as m')
                ->join('chit_details as cd', 'cd.id', '=', 'm.enrollment_id')
                ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
                ->leftJoin('slab_dues as sd', 'sd.id', '=', 'cd.slab_id')
                ->leftJoinSub($this->dueNoSub(), 'du', 'du.enrollment_id', '=', 'cd.id')
                ->leftJoinSub($paidSub, 'rp', 'rp.enrollment_id', '=', 'cd.id')
                ->where('m.group_id', $group->id)
                ->select('m.id as member_id', 'cd.id as enrollment_id', 'cd.customer_id', 'cd.branch_id', 'cd.ar_no', 'cd.slab_id',
                    'cd.pending_amount', 'b.branch_name', 'sd.slab_name', 'c.name', 'c.last_name', 'c.customer_code', 'c.mobile_no',
                    DB::raw('COALESCE(du.cur_due, 1) as due_no'), DB::raw('COALESCE(rp.paid, 0) as paid'))
                ->orderBy('m.id')
                ->get()
                ->map(function ($r, $i) {
                    $row = $this->formatCandidate($r);
                    $row['sno'] = $i + 1;
                    $row['member_id'] = (int) $r->member_id;
                    $row['paid'] = round((float) $r->paid, 2);
                    $row['pending'] = round((float) ($r->pending_amount ?? 0), 2);
                    return $row;
                });
            return response()->json(['status' => 'Success', 'group' => $group, 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to load members', 'error' => $e->getMessage(), 'data' => []], 200);
        }
    }

    public function add_member(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $group = DB::table('spn_groups')->where('id', $request->group_id)->first();
            if (!$group) {
                return response()->json(['status' => 'Error', 'msg' => 'Group not found'], 200);
            }
            $count = DB::table('spn_group_members')->where('group_id', $group->id)->count();
            if ($count >= (int) $group->max_members) {
                return response()->json(['status' => 'Error', 'msg' => 'Group is full (' . $group->max_members . ' members)'], 200);
            }
            if (DB::table('spn_group_members')->where('enrollment_id', $request->enrollment_id)->exists()) {
                return response()->json(['status' => 'Error', 'msg' => 'This chit is already in a group'], 200);
            }
            $req = new Request([
                'tenant_id' => $group->tenant_id,
                'scheme_id' => $group->scheme_id,
                'slab_id' => $group->slab_id,
                'due_no' => $group->due_no,
            ]);
            $row = $this->candidateQuery($req)->where('cd.id', (int) $request->enrollment_id)->first();
            if (!$row) {
                return response()->json(['status' => 'Error', 'msg' => 'Chit must be the same scheme and on Due No ' . $group->due_no], 200);
            }
            DB::table('spn_group_members')->insert([
                'group_id' => $group->id,
                'enrollment_id' => (int) $row->enrollment_id,
                'customer_id' => (int) $row->customer_id,
                'branch_id' => (int) $row->branch_id,
                'due_no' => (int) $group->due_no,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Member added'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to add member', 'error' => $e->getMessage()], 200);
        }
    }

    public function remove_member(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            DB::table('spn_group_members')->where('id', $request->member_id)->delete();
            return response()->json(['status' => 'Success', 'msg' => 'Member removed'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to remove member'], 200);
        }
    }

    public function delete(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            DB::transaction(function () use ($request) {
                DB::table('spn_group_members')->where('group_id', $request->id)->delete();
                DB::table('spn_groups')->where('id', $request->id)->delete();
            });
            return response()->json(['status' => 'Success', 'msg' => 'Group deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Unable to delete group'], 200);
        }
    }
}
