<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SectionHubController extends Controller
{
    private $tenant = 0;
    private $branch = 0;

    private function switchTenantDb(Request $request)
    {
        $db = $request->db ?: $request->database_name;
        if ($db) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $db);
            DB::reconnect('mysql');
        }
    }

    public function summary(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $this->tenant = (int) $request->tenant_id;
            $this->branch = (int) $request->branch_id;
            $section = (string) $request->section;
            $map = [
                'chit' => 'chit',
                'new-customers' => 'newCustomers',
                'groups' => 'groups',
                'collection' => 'collection',
                'payment' => 'payment',
                'crm' => 'crm',
                'hr' => 'hr',
                'action' => 'action',
                'ticket' => 'ticket',
                'documents' => 'documents',
                'security' => 'security',
                'reports' => 'reports',
                'today-collection' => 'todayCollection',
                'sales' => 'sales',
                'today-enrollment' => 'todayEnrollment',
                'pending-collection' => 'pendingCollection',
                'overdue-collection' => 'overdueCollection',
                'top-performers' => 'topPerformers',
                'attendance' => 'attendance',
                'birthdays' => 'birthdays',
                'tasks-targets' => 'tasksTargets',
                'expenses' => 'expenses',
                'settlement-approvals' => 'settlementApprovals',
                'corporate' => 'corporate',
            ];
            if (!isset($map[$section])) {
                return response()->json(['status' => 'Error', 'msg' => 'Unknown section'], 200);
            }
            $data = $this->{$map[$section]}();
            $data['branches'] = $this->branchList();
            return response()->json(['status' => 'Success', 'data' => $data], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    /** All branches with headline numbers, for the branch overview cards. */
    public function branch_overview(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $this->tenant = (int) $request->tenant_id;
            $this->branch = 0;
            $sixFrom = $this->months(6)[0]['key'] . '-01';
            $active = (clone $this->enrollments())->where('cd.status', 1);

            $metrics = [
                'customers' => $this->pluckBranch($this->scoped('customers', 'c'), 'c.branch_id'),
                'active' => $this->pluckBranch(clone $active, 'cd.branch_id'),
                'eligible' => Schema::hasColumn('chit_details', 'eligibility_status')
                    ? $this->pluckBranch((clone $active)->where('cd.eligibility_status', 1), 'cd.branch_id') : [],
                'closed' => $this->pluckBranch((clone $this->enrollments())->where('cd.status', 2), 'cd.branch_id'),
                'chit_value' => $this->pluckBranch((clone $active)->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id'), 'cd.branch_id', 's.chit_value'),
                'month_collection' => $this->pluckBranch((clone $this->receipts())->where('r.receipt_date', '>=', $this->monthStart()), 'r.branch_id', 'r.amount'),
                'six_collection' => $this->pluckBranch((clone $this->receipts())->where('r.receipt_date', '>=', $sixFrom), 'r.branch_id', 'r.amount'),
                'employees' => $this->pluckBranch($this->scoped('employees', 'e')->where('e.status', 1), 'e.branch_id'),
                'leads' => $this->pluckBranch($this->scoped('lead_managements', 'l'), 'l.branch_id'),
                'payments' => $this->pluckBranch($this->scoped('payments', 'p'), 'p.branch_id', 'p.total_amount'),
            ];

            $rows = [];
            foreach ($this->branchList() as $b) {
                $row = ['id' => $b->id, 'branch_name' => $b->branch_name, 'branch_code' => $b->branch_code];
                foreach ($metrics as $k => $vals) {
                    $row[$k] = (float) ($vals[$b->id] ?? 0);
                }
                $rows[] = $row;
            }
            return response()->json(['status' => 'Success', 'data' => $rows], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    /** One branch: KPI cards of every section. */
    public function branch_detail(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $this->tenant = (int) $request->tenant_id;
            $this->branch = (int) $request->branch_id;
            if (!$this->branch) {
                return response()->json(['status' => 'Error', 'msg' => 'branch_id required'], 200);
            }
            $branch = DB::table('branches')->where('id', $this->branch)->first(['id', 'branch_name', 'branch_code', 'address_line_1', 'mobile_no', 'phone_no', 'start_date']);
            $sections = [
                'chit' => 'chit', 'new-customers' => 'newCustomers', 'groups' => 'groups', 'collection' => 'collection',
                'payment' => 'payment', 'crm' => 'crm', 'hr' => 'hr', 'action' => 'action',
                'ticket' => 'ticket', 'documents' => 'documents', 'security' => 'security',
            ];
            $out = [];
            foreach ($sections as $key => $method) {
                try {
                    $d = $this->{$method}();
                    $out[$key] = ['kpis' => $d['kpis'], 'trend' => $d['trend']];
                } catch (\Throwable $e) {
                    $out[$key] = ['kpis' => [], 'trend' => ['title' => '', 'money' => false, 'rows' => []], 'error' => $e->getMessage()];
                }
            }
            return response()->json(['status' => 'Success', 'data' => ['branch' => $branch, 'sections' => $out]], 200);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function pluckBranch($query, $column, $agg = 'count')
    {
        return $query->groupBy($column)
            ->select($column . ' as bid', DB::raw($agg === 'count' ? 'COUNT(*) as v' : 'SUM(' . $agg . ') as v'))
            ->pluck('v', 'bid');
    }

    /* ---------- helpers ---------- */

    private function scoped($table, $alias = null)
    {
        $name = $alias ? $table . ' as ' . $alias : $table;
        $p = $alias ?: $table;
        $q = DB::table($name);
        if ($this->tenant && Schema::hasColumn($table, 'tenant_id')) {
            $q->where($p . '.tenant_id', $this->tenant);
        }
        if (Schema::hasColumn($table, 'deleted_at')) {
            $q->whereNull($p . '.deleted_at');
        }
        if ($this->branch && Schema::hasColumn($table, 'branch_id')) {
            $q->where($p . '.branch_id', $this->branch);
        }
        return $q;
    }

    private function branchList()
    {
        $q = DB::table('branches')->whereNull('deleted_at');
        if ($this->tenant) {
            $q->where('tenant_id', $this->tenant);
        }
        return $q->orderBy('id')->get(['id', 'branch_name', 'branch_code'])->toArray();
    }

    private function branchShort($row)
    {
        $code = trim((string) ($row->branch_code ?? ''));
        return $code !== '' ? $code : (string) $row->branch_name;
    }

    /** Group a scoped query by branch_id and map to branch labels. */
    private function byBranch($query, $column, $agg = 'count')
    {
        $rows = $query->groupBy($column)
            ->select($column . ' as bid', DB::raw($agg === 'count' ? 'COUNT(*) as v' : 'SUM(' . $agg . ') as v'))
            ->pluck('v', 'bid');
        $out = [];
        foreach ($this->branchList() as $b) {
            if ($this->branch && (int) $b->id !== $this->branch) {
                continue;
            }
            $out[] = ['label' => $this->branchShort($b), 'name' => $b->branch_name, 'value' => (float) ($rows[$b->id] ?? 0)];
        }
        return $out;
    }

    private function months($count)
    {
        $list = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $d = date('Y-m-01', strtotime(date('Y-m-01') . " -$i month"));
            $list[] = ['key' => substr($d, 0, 7), 'label' => date('M y', strtotime($d))];
        }
        return $list;
    }

    private function monthTrend($query, $dateColumn, $agg = 'count', $count = 6)
    {
        $months = $this->months($count);
        $from = $months[0]['key'] . '-01';
        $rows = $query->where($dateColumn, '>=', $from)
            ->groupBy(DB::raw("DATE_FORMAT($dateColumn, '%Y-%m')"))
            ->select(DB::raw("DATE_FORMAT($dateColumn, '%Y-%m') as m"), DB::raw($agg === 'count' ? 'COUNT(*) as v' : "SUM($agg) as v"))
            ->pluck('v', 'm');
        return array_map(function ($m) use ($rows) {
            return ['label' => $m['label'], 'value' => (float) ($rows[$m['key']] ?? 0)];
        }, $months);
    }

    private function kpi($label, $value, $sub = '', $money = false)
    {
        return ['label' => $label, 'value' => $value, 'sub' => $sub, 'money' => $money];
    }

    private function block($title, $rows, $money = false)
    {
        return ['title' => $title, 'money' => $money, 'rows' => array_values($rows)];
    }

    private function table($title, $columns, $rows, $route = '')
    {
        return ['title' => $title, 'columns' => $columns, 'rows' => $rows, 'route' => $route];
    }

    private function monthStart()
    {
        return date('Y-m-01');
    }

    private function enrollments()
    {
        return $this->scoped('chit_details', 'cd')->where('cd.chit_type', 3);
    }

    /* ---------- sections ---------- */

    private function chit()
    {
        $active = (clone $this->enrollments())->where('cd.status', 1);
        $hasElig = Schema::hasColumn('chit_details', 'eligibility_status');
        $hasPending = Schema::hasColumn('chit_details', 'pending_amount');
        $eligible = $hasElig ? (clone $active)->where('cd.eligibility_status', 1)->count() : 0;
        $activeCount = (clone $active)->count();
        $oldPending = $hasPending ? (clone $active)->where('cd.pending_amount', '>', 0)->count() : 0;
        $closed = (clone $this->enrollments())->where('cd.status', 2)->count();
        $customers = (clone $active)->distinct()->count('cd.customer_id');

        $schemeWise = (clone $active)->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->groupBy('s.chit_value')->orderBy('s.chit_value')
            ->select('s.chit_value', DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $this->shortMoney($r->chit_value), 'value' => (int) $r->v];
            })->toArray();

        $recent = (clone $this->enrollments())
            ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
            ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->orderBy('cd.id', 'desc')->limit(10)
            ->get(['cd.ar_no', 'c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name', 's.chit_value',
                $hasPending ? 'cd.pending_amount' : DB::raw('0 as pending_amount'),
                $hasElig ? 'cd.eligibility_status' : DB::raw('0 as eligibility_status'),
                'cd.status'])
            ->map(function ($r) {
                return [
                    'ar_no' => $r->ar_no ?: '-',
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'chit_value' => (float) $r->chit_value,
                    'pending' => (float) $r->pending_amount,
                    'category' => (int) $r->eligibility_status === 1 ? 'Eligible' : 'Not Eligible',
                    'status' => (int) $r->status === 1 ? 'Active' : 'Closed',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Eligible Customers', $eligible, 'Approval eligible'),
                $this->kpi('Active Customers', $activeCount, 'Running chits'),
                $this->kpi('Old Pending', $oldPending, 'Active with dues'),
                $this->kpi('Closed / Old', $closed, 'Completed or closed'),
                $this->kpi('Unique Customers', $customers, 'With active chits'),
            ],
            'branch' => $this->block('Branch Wise Active Chit Customers', $this->byBranch(clone $active, 'cd.branch_id')),
            'donut' => $this->block('Customer Status Overview', [
                ['label' => 'Eligible', 'value' => $eligible],
                ['label' => 'Not Eligible', 'value' => max(0, $activeCount - $eligible)],
                ['label' => 'Closed', 'value' => $closed],
            ]),
            'trend' => $this->block('Chit Scheme Wise Active Customers', $schemeWise),
            'table' => $this->table('Recent Chit Customers', [
                ['key' => 'ar_no', 'label' => 'AR No'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'chit_value', 'label' => 'Chit Value', 'type' => 'money'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'money'],
                ['key' => 'category', 'label' => 'Category', 'type' => 'status'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'view-commitment-chit'),
        ];
    }

    private function newCustomers()
    {
        $base = $this->scoped('customers', 'c');
        $total = (clone $base)->count();
        $month = (clone $base)->where('c.created_at', '>=', $this->monthStart())->count();
        $approvalCol = \Schema::hasColumn('customers', 'approval_status') ? 'approval_status' : 'status';
        $hasEnrolled = \Schema::hasColumn('customers', 'is_enrolled');
        $approved = (clone $base)->where('c.' . $approvalCol, 1)->count();
        $pending = $total - $approved;
        $enrolled = $hasEnrolled ? (clone $base)->where('c.is_enrolled', 1)->count() : 0;

        $cols = ['c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name', 'c.created_at', 'c.' . $approvalCol . ' as approval_flag'];
        if ($hasEnrolled) {
            $cols[] = 'c.is_enrolled';
        }
        $recent = (clone $base)->leftJoin('branches as b', 'b.id', '=', 'c.branch_id')
            ->orderBy('c.id', 'desc')->limit(10)
            ->get($cols)
            ->map(function ($r) {
                return [
                    'code' => $r->customer_code,
                    'name' => $r->name,
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'created' => $r->created_at ? date('d-m-Y', strtotime($r->created_at)) : '-',
                    'enrolled' => (int) ($r->is_enrolled ?? 0) === 1 ? 'Enrolled' : 'Not Enrolled',
                    'status' => (int) $r->approval_flag === 1 ? 'Approved' : 'Pending',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Profile Creation', $total, 'Total customer profiles'),
                $this->kpi('Monthly New Customers', $month, 'Created this month'),
                $this->kpi('Approved', $approved, 'Profile approved'),
                $this->kpi('Pending Approval', $pending, 'Waiting for approval'),
                $this->kpi('Enrolled', $enrolled, 'Joined a chit'),
            ],
            'branch' => $this->block('Branch Wise Customers', $this->byBranch(clone $base, 'c.branch_id')),
            'donut' => $this->block('Customer Verification Status', [
                ['label' => 'Enrolled', 'value' => $enrolled],
                ['label' => 'Approved (not enrolled)', 'value' => max(0, $approved - $enrolled)],
                ['label' => 'Pending', 'value' => $pending],
            ]),
            'trend' => $this->block('Monthly New Customers Trend', $this->monthTrend(clone $base, 'c.created_at', 'count', 12)),
            'table' => $this->table('Recent New Customers', [
                ['key' => 'code', 'label' => 'Customer ID'],
                ['key' => 'name', 'label' => 'Customer Name'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'created', 'label' => 'Created'],
                ['key' => 'enrolled', 'label' => 'Enrolment', 'type' => 'status'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'view-customer'),
        ];
    }

    private function groups()
    {
        $schemes = $this->scoped('schemes', 's');
        $schemeTotal = (clone $schemes)->count();
        $schemeActive = (clone $schemes)->where('s.status', 1)->count();
        $active = (clone $this->enrollments())->where('cd.status', 1);
        $members = (clone $active)->count();
        $value = (float) (clone $active)->leftJoin('schemes as sx', 'sx.id', '=', 'cd.scheme_id')->sum('sx.chit_value');
        $closed = (clone $this->enrollments())->where('cd.status', 2)->count();

        $enrolledByScheme = (clone $active)->groupBy('cd.scheme_id')->select('cd.scheme_id', DB::raw('COUNT(*) as v'))->pluck('v', 'scheme_id');

        $list = (clone $schemes)->orderBy('s.chit_value')->get(['s.id', 's.chit_value', 's.no_of_months', 's.members', 's.monthly_due', 's.status']);
        $rows = $list->map(function ($r) use ($enrolledByScheme) {
            return [
                'chit_value' => (float) $r->chit_value,
                'months' => $r->no_of_months,
                'members' => $r->members,
                'monthly_due' => (float) $r->monthly_due,
                'enrolled' => (int) ($enrolledByScheme[$r->id] ?? 0),
                'status' => (int) $r->status === 1 ? 'Active' : 'Inactive',
            ];
        })->toArray();

        $duration = $list->groupBy('no_of_months')->map(function ($g, $k) {
            return ['label' => $k . ' Months', 'value' => $g->count()];
        })->values()->toArray();

        $top = collect($rows)->sortByDesc('enrolled')->take(8)->map(function ($r) {
            return ['label' => $this->shortMoney($r['chit_value']) . ' / ' . $r['months'] . 'M', 'value' => $r['enrolled']];
        })->values()->toArray();

        return [
            'kpis' => [
                $this->kpi('Total Schemes', $schemeTotal, 'All chit schemes'),
                $this->kpi('Active Schemes', $schemeActive, 'Open for enrolment'),
                $this->kpi('Total Chit Value', $value, 'Active enrolments', true),
                $this->kpi('Total Members', $members, 'Active enrolments'),
                $this->kpi('Completed / Closed', $closed, 'Old groups'),
            ],
            'branch' => $this->block('Branch Wise Members', $this->byBranch(clone $active, 'cd.branch_id')),
            'donut' => $this->block('Schemes by Duration', $duration),
            'trend' => $this->block('Top Schemes by Members', $top),
            'table' => $this->table('Scheme List', [
                ['key' => 'chit_value', 'label' => 'Chit Value', 'type' => 'money'],
                ['key' => 'months', 'label' => 'Months'],
                ['key' => 'members', 'label' => 'Members'],
                ['key' => 'monthly_due', 'label' => 'Monthly Due', 'type' => 'money'],
                ['key' => 'enrolled', 'label' => 'Enrolled'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], array_slice($rows, 0, 12), 'view-scheme'),
        ];
    }

    private function receipts()
    {
        return $this->scoped('receipts', 'r')->where('r.status', 1);
    }

    private function collection()
    {
        $month = (float) (clone $this->receipts())->where('r.receipt_date', '>=', $this->monthStart())->sum('r.amount');
        $today = (float) (clone $this->receipts())->where('r.receipt_date', date('Y-m-d'))->sum('r.amount');
        $lastFrom = date('Y-m-01', strtotime('first day of last month'));
        $last = (float) (clone $this->receipts())->where('r.receipt_date', '>=', $lastFrom)->where('r.receipt_date', '<', $this->monthStart())->sum('r.amount');
        $other = (float) $this->scoped('other_charges', 'o')->where('o.received_date', '>=', $this->monthStart())->sum('o.amount');
        $rf = (float) $this->scoped('before_enrol_receipts', 'be')->where('be.received_date', '>=', $this->monthStart())->sum('be.amount');

        $sixFrom = $this->months(6)[0]['key'] . '-01';
        $modes = (clone $this->receipts())->where('r.receipt_date', '>=', $sixFrom)
            ->leftJoin('payment_types as pt', 'pt.id', '=', 'r.payment_type_id')
            ->groupBy('pt.payment_name')->select('pt.payment_name', DB::raw('SUM(r.amount) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->payment_name ?: 'Others', 'value' => (float) $r->v];
            })->toArray();

        $recent = (clone $this->receipts())
            ->leftJoin('customers as c', 'c.id', '=', 'r.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'r.branch_id')
            ->leftJoin('payment_types as pt', 'pt.id', '=', 'r.payment_type_id')
            ->orderBy('r.receipt_date', 'desc')->orderBy('r.id', 'desc')->limit(10)
            ->get(['r.receipt_no', 'r.receipt_date', 'c.customer_code', 'c.name', 'b.branch_name', 'r.amount', 'pt.payment_name'])
            ->map(function ($r) {
                return [
                    'receipt_no' => $r->receipt_no,
                    'date' => $r->receipt_date ? date('d-m-Y', strtotime($r->receipt_date)) : '-',
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'branch' => $r->branch_name,
                    'amount' => (float) $r->amount,
                    'mode' => $r->payment_name ?: '-',
                    'status' => 'Paid',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('This Month Collection', $month, 'Receipts this month', true),
                $this->kpi('Today Collection', $today, date('d M Y'), true),
                $this->kpi('Last Month', $last, 'Previous month total', true),
                $this->kpi('RF Collection', $rf, 'Before enrolment, this month', true),
                $this->kpi('Other Charges', $other, 'This month', true),
            ],
            'branch' => $this->block('Branch Wise Collection (Last 6 Months)', $this->byBranch((clone $this->receipts())->where('r.receipt_date', '>=', $sixFrom), 'r.branch_id', 'r.amount'), true),
            'donut' => $this->block('Payment Mode Wise (Last 6 Months)', $modes, true),
            'trend' => $this->block('Collection Trend (Last 6 Months)', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', 'r.amount'), true),
            'table' => $this->table('Recent Collection Entries', [
                ['key' => 'receipt_no', 'label' => 'Receipt No'],
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['key' => 'mode', 'label' => 'Mode'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'commitment-receipt-report'),
        ];
    }

    private function payment()
    {
        $paid = $this->scoped('payments', 'p');
        $paidTotal = (float) (clone $paid)->sum('p.total_amount');
        $paidCustomers = (clone $paid)->distinct()->count('p.customer_id');
        $hasDesk = Schema::hasTable('payment_desk_requests');
        $desk = $hasDesk ? $this->scoped('payment_desk_requests', 'd') : null;

        $deskApproved = $hasDesk ? (float) (clone $desk)->where('d.status', 'approved')->sum('d.net_amount') : 0;
        $deskApprovedCustomers = $hasDesk ? (clone $desk)->where('d.status', 'approved')->distinct()->count('d.customer_id') : 0;
        $pendingApproval = $hasDesk ? (clone $desk)->whereIn('d.status', ['pending_field', 'pending_chit_admin', 'pending_admin'])->count() : 0;
        $prized = $hasDesk ? (clone $desk)->where('d.kind', 'prized')->count() : 0;
        $refund = $hasDesk ? (clone $desk)->where('d.kind', 'refund')->count() : 0;

        $statusRows = [];
        $branch = $this->byBranch(clone $paid, 'p.branch_id', 'p.total_amount');
        $recent = [];
        if ($hasDesk) {
            $labels = ['draft' => 'Draft', 'pending_field' => 'Field Verification', 'pending_chit_admin' => 'Chit Admin Check', 'pending_admin' => 'Admin Approval', 'approved' => 'Approved', 'rejected' => 'Rejected'];
            $counts = (clone $desk)->groupBy('d.status')->select('d.status', DB::raw('COUNT(*) as v'))->pluck('v', 'status');
            foreach ($labels as $k => $l) {
                $statusRows[] = ['label' => $l, 'value' => (int) ($counts[$k] ?? 0)];
            }
            $deskBranch = (clone $desk)->where('d.status', 'approved')->groupBy('d.branch_id')
                ->select('d.branch_id', DB::raw('SUM(d.net_amount) as v'))->pluck('v', 'branch_id');
            $ids = array_map(function ($b) {
                return $b->id;
            }, array_values(array_filter($this->branchList(), function ($b) {
                return !$this->branch || (int) $b->id === $this->branch;
            })));
            foreach ($branch as $i => $row) {
                $branch[$i]['value'] += (float) ($deskBranch[$ids[$i]] ?? 0);
            }
            $recent = (clone $desk)->leftJoin('branches as b', 'b.id', '=', 'd.branch_id')
                ->orderBy('d.id', 'desc')->limit(10)
                ->get(['d.customer_name', 'd.ar_no', 'd.kind', 'b.branch_name', 'd.scheme_name', 'd.chit_value', 'd.net_amount', 'd.status', 'd.created_at'])
                ->map(function ($r) use ($labels) {
                    return [
                        'date' => $r->created_at ? date('d-m-Y', strtotime($r->created_at)) : '-',
                        'customer' => $r->customer_name . ($r->ar_no ? ' (' . $r->ar_no . ')' : ''),
                        'kind' => ucfirst((string) $r->kind),
                        'branch' => $r->branch_name,
                        'chit_value' => (float) $r->chit_value,
                        'amount' => (float) $r->net_amount,
                        'status' => $labels[$r->status] ?? ucfirst((string) $r->status),
                    ];
                })->toArray();
        }
        if (!$recent) {
            $recent = (clone $paid)->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'p.branch_id')
                ->orderBy('p.id', 'desc')->limit(10)
                ->get(['p.payment_date', 'c.name', 'b.branch_name', 'p.total_amount'])
                ->map(function ($r) {
                    return [
                        'date' => $r->payment_date ? date('d-m-Y', strtotime($r->payment_date)) : '-',
                        'customer' => $r->name,
                        'kind' => 'Payment',
                        'branch' => $r->branch_name,
                        'chit_value' => 0,
                        'amount' => (float) $r->total_amount,
                        'status' => 'Paid',
                    ];
                })->toArray();
        }

        return [
            'kpis' => [
                $this->kpi('Total Payments', $paidTotal + $deskApproved, 'Released to customers', true),
                $this->kpi('Paid Customers', $paidCustomers + $deskApprovedCustomers, 'Received payment'),
                $this->kpi('Pending Approval', $pendingApproval, 'Field + admin'),
                $this->kpi('Prized Requests', $prized, 'Payment desk'),
                $this->kpi('Refund Requests', $refund, 'Payment desk'),
            ],
            'branch' => $this->block('Branch Wise Payments', $branch, true),
            'donut' => $this->block('Payment Status Overview', $statusRows ?: [['label' => 'Paid', 'value' => (clone $paid)->count()]]),
            'trend' => $this->block('Monthly Payment Trend', $this->monthTrend(clone $paid, 'p.payment_date', 'p.total_amount'), true),
            'table' => $this->table('Recent Payments', [
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'kind', 'label' => 'Type'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'chit_value', 'label' => 'Chit Value', 'type' => 'money'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'payment-dashboard'),
        ];
    }

    private function crm()
    {
        $cust = $this->scoped('customers', 'c')->whereNotNull('c.dob');
        $today = (clone $cust)->whereRaw("DATE_FORMAT(c.dob, '%m-%d') = ?", [date('m-d')])->count();
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = date('m-d', strtotime("+$i day"));
        }
        $weekQ = (clone $cust)->whereIn(DB::raw("DATE_FORMAT(c.dob, '%m-%d')"), $days);
        $week = (clone $weekQ)->count();
        $leads = $this->scoped('lead_managements', 'l');
        $leadTotal = (clone $leads)->count();
        $converted = (clone $leads)->where('l.is_customer', 1)->count();
        $newCust = $this->scoped('customers', 'c2')->where('c2.created_at', '>=', date('Y-m-d', strtotime('-7 day')))->count();

        $sources = (clone $leads)->leftJoin('source_types as st', 'st.id', '=', 'l.source_type_id')
            ->groupBy('st.id')->select(DB::raw("MAX(st.source_type_name) as label"), DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->label ?: 'Others', 'value' => (int) $r->v];
            })->toArray();

        $birthMonths = (clone $cust)->groupBy(DB::raw('MONTH(c.dob)'))->select(DB::raw('MONTH(c.dob) as m'), DB::raw('COUNT(*) as v'))->pluck('v', 'm');
        $calendar = [];
        for ($m = 1; $m <= 12; $m++) {
            $calendar[] = ['label' => date('M', mktime(0, 0, 0, $m, 1)), 'value' => (int) ($birthMonths[$m] ?? 0)];
        }

        $recent = (clone $weekQ)->leftJoin('branches as b', 'b.id', '=', 'c.branch_id')
            ->orderBy(DB::raw("DATE_FORMAT(c.dob, '%m-%d')"))->limit(10)
            ->get(['c.name', 'c.mobile_no', 'b.branch_name', 'c.dob'])
            ->map(function ($r) {
                return [
                    'date' => date('d M', strtotime($r->dob)),
                    'name' => $r->name,
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'status' => date('m-d', strtotime($r->dob)) === date('m-d') ? 'Today' : 'Upcoming',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Today Birthday', $today, date('d M')),
                $this->kpi('Birthdays This Week', $week, 'Next 7 days'),
                $this->kpi('New Customers', $newCust, 'Last 7 days'),
                $this->kpi('Total Leads', $leadTotal, 'All lead sources'),
                $this->kpi('Converted Leads', $converted, 'Became customers'),
            ],
            'branch' => $this->block('Branch Wise Leads', $this->byBranch(clone $leads, 'l.branch_id')),
            'donut' => $this->block('Leads by Source', $sources),
            'trend' => $this->block('Birthday Calendar (Customers per Month)', $calendar),
            'table' => $this->table('Birthday This Week', [
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'name', 'label' => 'Customer Name'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'status', 'label' => 'Wish', 'type' => 'status'],
            ], $recent, 'customer-birthday-report'),
        ];
    }

    private function hr()
    {
        $emp = $this->scoped('employees', 'e');
        $total = (clone $emp)->count();
        $active = (clone $emp)->where('e.status', 1)->count();
        $newMonth = (clone $emp)->where('e.joining_date', '>=', $this->monthStart())->count();
        $todayDate = date('Y-m-d');
        $present = Schema::hasTable('employee_attendances')
            ? $this->scoped('employee_attendances', 'a')->where('a.att_date', $todayDate)->distinct()->count('a.employee_id') : 0;
        $onLeave = 0;
        $pendingLeave = 0;
        if (Schema::hasTable('employee_leave_requests') && Schema::hasColumn('employee_leave_requests', 'request_status')) {
            $lr = $this->scoped('employee_leave_requests', 'lr');
            $onLeave = (clone $lr)->where('lr.request_status', 'approved')->where('lr.from_date', '<=', $todayDate)->where('lr.to_date', '>=', $todayDate)->count();
            $pendingLeave = (clone $lr)->where('lr.request_status', 'pending')->count();
        }

        $designations = (clone $emp)->leftJoin('designations as d', 'd.id', '=', 'e.designation_id')
            ->groupBy('e.designation_id')->select(DB::raw('MAX(d.designation_name) as label'), DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->label ?: 'Others', 'value' => (int) $r->v];
            })->toArray();

        $attendance = [];
        if (Schema::hasTable('employee_attendances')) {
            $from = date('Y-m-d', strtotime('-6 day'));
            $att = $this->scoped('employee_attendances', 'a2')->where('a2.att_date', '>=', $from)
                ->groupBy('a2.att_date')->select('a2.att_date', DB::raw('COUNT(DISTINCT a2.employee_id) as v'))->pluck('v', 'att_date');
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i day"));
                $attendance[] = ['label' => date('d M', strtotime($d)), 'value' => (int) ($att[$d] ?? 0)];
            }
        }

        $recent = (clone $emp)->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')
            ->leftJoin('designations as d', 'd.id', '=', 'e.designation_id')
            ->orderBy('e.id', 'desc')->limit(10)
            ->get(['e.first_name', 'e.last_name', 'e.mobile_no', 'b.branch_name', 'd.designation_name', 'e.joining_date', 'e.status'])
            ->map(function ($r) {
                return [
                    'name' => trim($r->first_name . ' ' . $r->last_name),
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'designation' => $r->designation_name,
                    'doj' => $r->joining_date ? date('d-m-Y', strtotime($r->joining_date)) : '-',
                    'status' => (int) $r->status === 1 ? 'Active' : 'Inactive',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Total Employees', $total, 'All staff'),
                $this->kpi('Active', $active, 'Working now'),
                $this->kpi('Present Today', $present, 'Punched in'),
                $this->kpi('On Leave', $onLeave, 'Approved leave today'),
                $this->kpi('Leave Requests', $pendingLeave, 'Pending approval'),
                $this->kpi('New This Month', $newMonth, 'Joined'),
            ],
            'branch' => $this->block('Branch Wise Employees', $this->byBranch(clone $emp, 'e.branch_id')),
            'donut' => $this->block('Designation Wise', $designations),
            'trend' => $this->block('Attendance (Last 7 Days)', $attendance),
            'table' => $this->table('Employee Details', [
                ['key' => 'name', 'label' => 'Name'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'designation', 'label' => 'Designation'],
                ['key' => 'doj', 'label' => 'DOJ'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'view-employee'),
        ];
    }

    private function action()
    {
        if (!Schema::hasTable('employee_tasks')) {
            return $this->emptySection('Actions');
        }
        $t = $this->scoped('employee_tasks', 't');
        $total = (clone $t)->count();
        $pending = (clone $t)->where('t.task_status', 'pending')->count();
        $progress = (clone $t)->where('t.task_status', 'in_progress')->count();
        $done = (clone $t)->where('t.task_status', 'completed')->count();
        $overdue = (clone $t)->whereNotIn('t.task_status', ['completed', 'cancelled'])->where('t.due_date', '<', date('Y-m-d'))->count();
        $rate = $total ? round($done * 100 / $total) : 0;

        $priority = (clone $t)->groupBy('t.priority')->select('t.priority', DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => ucfirst($r->priority ?: 'normal'), 'value' => (int) $r->v];
            })->toArray();

        $recent = (clone $t)->leftJoin('employees as e', 'e.id', '=', 't.employee_id')
            ->leftJoin('branches as b', 'b.id', '=', 't.branch_id')
            ->orderBy('t.id', 'desc')->limit(10)
            ->get(['t.title', 'e.first_name', 'e.last_name', 'b.branch_name', 't.priority', 't.due_date', 't.task_status'])
            ->map(function ($r) {
                return [
                    'title' => $r->title,
                    'employee' => trim($r->first_name . ' ' . $r->last_name),
                    'branch' => $r->branch_name,
                    'priority' => ucfirst((string) $r->priority),
                    'due' => $r->due_date ? date('d-m-Y', strtotime($r->due_date)) : '-',
                    'status' => ucwords(str_replace('_', ' ', (string) $r->task_status)),
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Total Actions', $total, 'All tasks'),
                $this->kpi('New / Pending', $pending, 'Not started'),
                $this->kpi('In Progress', $progress, 'Being worked'),
                $this->kpi('Completed', $done, $rate . '% completion'),
                $this->kpi('Overdue', $overdue, 'Past due date'),
            ],
            'branch' => $this->block('Branch Wise Actions', $this->byBranch(clone $t, 't.branch_id')),
            'donut' => $this->block('Action Status Overview', [
                ['label' => 'Pending', 'value' => $pending],
                ['label' => 'In Progress', 'value' => $progress],
                ['label' => 'Completed', 'value' => $done],
                ['label' => 'Overdue', 'value' => $overdue],
            ]),
            'trend' => $this->block('Priority Wise Actions', $priority),
            'table' => $this->table('All Actions', [
                ['key' => 'title', 'label' => 'Subject'],
                ['key' => 'employee', 'label' => 'Assigned To'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'status'],
                ['key' => 'due', 'label' => 'Due Date'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'task-report'),
        ];
    }

    private function ticket()
    {
        if (!Schema::hasTable('office_tickets')) {
            return $this->emptySection('Tickets');
        }
        $t = $this->scoped('office_tickets', 't');
        $total = (clone $t)->count();
        $open = (clone $t)->where('t.status', 'open')->count();
        $progress = (clone $t)->where('t.status', 'in_progress')->count();
        $approved = (clone $t)->where('t.status', 'approved')->count();
        $resolved = (clone $t)->whereIn('t.status', ['resolved', 'closed'])->count();

        $byBranch = (clone $t)->groupBy('t.branch_name')->select('t.branch_name', DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->branch_name ?: 'Office', 'value' => (int) $r->v];
            })->toArray();
        $category = (clone $t)->groupBy('t.category')->select('t.category', DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->category ?: 'Others', 'value' => (int) $r->v];
            })->toArray();

        $recent = (clone $t)->orderBy('t.id', 'desc')->limit(10)
            ->get(['t.ticket_no', 't.created_at', 't.kind', 't.category', 't.subject', 't.priority', 't.branch_name', 't.employee_name', 't.status'])
            ->map(function ($r) {
                return [
                    'ticket_no' => $r->ticket_no,
                    'date' => $r->created_at ? date('d-m-Y', strtotime($r->created_at)) : '-',
                    'type' => ucwords(str_replace('_', ' ', (string) $r->kind)),
                    'subject' => $r->subject ?: $r->category,
                    'branch' => $r->branch_name,
                    'raised_by' => $r->employee_name,
                    'priority' => ucfirst((string) $r->priority),
                    'status' => ucwords(str_replace('_', ' ', (string) $r->status)),
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Total Tickets', $total, 'All requests'),
                $this->kpi('Open', $open, 'Waiting'),
                $this->kpi('In Progress', $progress, 'Being handled'),
                $this->kpi('Approved', $approved, 'Wishes approved'),
                $this->kpi('Resolved', $resolved, 'Resolved / closed'),
            ],
            'branch' => $this->block('Branch Wise Tickets', $byBranch),
            'donut' => $this->block('Ticket Status Overview', [
                ['label' => 'Open', 'value' => $open],
                ['label' => 'In Progress', 'value' => $progress],
                ['label' => 'Approved', 'value' => $approved],
                ['label' => 'Resolved', 'value' => $resolved],
            ]),
            'trend' => $this->block('Ticket Category Wise', $category),
            'table' => $this->table('Recent Tickets', [
                ['key' => 'ticket_no', 'label' => 'Ticket No'],
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'type', 'label' => 'Type'],
                ['key' => 'subject', 'label' => 'Subject'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'raised_by', 'label' => 'Raised By'],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'status'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'office-tickets'),
        ];
    }

    private function documents()
    {
        $d = $this->scoped('documents', 'd');
        $total = (clone $d)->count();
        $month = (clone $d)->where('d.collect_date', '>=', $this->monthStart())->count();
        $returned = (clone $d)->whereNotNull('d.return_document_date')->count();
        $loan = Schema::hasTable('loan_documents') ? $this->scoped('loan_documents')->count() : 0;
        $employee = Schema::hasTable('employee_documents') ? $this->scoped('employee_documents')->count() : 0;

        $recent = (clone $d)->leftJoin('customers as c', 'c.id', '=', 'd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'd.branch_id')
            ->orderBy('d.id', 'desc')->limit(10)
            ->get(['c.name', 'c.customer_code', 'b.branch_name', 'd.collect_date', 'd.return_document_date', 'd.collected_from'])
            ->map(function ($r) {
                return [
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'branch' => $r->branch_name,
                    'from' => $r->collected_from ?: '-',
                    'collected' => $r->collect_date ? date('d-m-Y', strtotime($r->collect_date)) : '-',
                    'returned' => $r->return_document_date ? date('d-m-Y', strtotime($r->return_document_date)) : '-',
                    'status' => $r->return_document_date ? 'Returned' : 'With Office',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Total Documents', $total + $loan + $employee, 'All categories'),
                $this->kpi('Customer Documents', $total, 'Chit customers'),
                $this->kpi('New This Month', $month, 'Collected'),
                $this->kpi('Pending Return', max(0, $total - $returned), 'Held at office'),
                $this->kpi('Employee Documents', $employee, 'HR files'),
                $this->kpi('Loan Documents', $loan, 'Loan files'),
            ],
            'branch' => $this->block('Branch Wise Documents', $this->byBranch(clone $d, 'd.branch_id')),
            'donut' => $this->block('Document Type Distribution', [
                ['label' => 'Customer', 'value' => $total],
                ['label' => 'Employee', 'value' => $employee],
                ['label' => 'Loan', 'value' => $loan],
            ]),
            'trend' => $this->block('Monthly Document Upload Trend', $this->monthTrend(clone $d, 'd.collect_date')),
            'table' => $this->table('Recent Documents', [
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'from', 'label' => 'Collected From'],
                ['key' => 'collected', 'label' => 'Collected'],
                ['key' => 'returned', 'label' => 'Returned'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'view-documents'),
        ];
    }

    private function security()
    {
        $u = $this->scoped('users', 'u');
        $total = (clone $u)->count();
        $active = (clone $u)->where('u.status', 1)->count();
        $staff = (clone $u)->where('u.user_type', '<>', 3);
        $staffCount = (clone $staff)->count();
        $customers = (clone $u)->where('u.user_type', 3)->count();
        $devices = Schema::hasTable('devices') ? $this->scoped('devices')->count() : 0;
        $logins = Schema::hasTable('device_logins') ? $this->scoped('device_logins', 'dl')->where('dl.created_at', '>=', $this->monthStart())->count() : 0;

        $roles = (clone $staff)->leftJoin('roles as r', 'r.id', '=', 'u.role_id')
            ->groupBy('u.role_id')->select(DB::raw('MAX(r.role_name) as label'), DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->label ?: 'No Role', 'value' => (int) $r->v];
            })->toArray();

        $trend = [];
        $recent = [];
        if (Schema::hasTable('device_logins')) {
            $from = date('Y-m-d', strtotime('-6 day'));
            $rows = $this->scoped('device_logins', 'dl2')->where('dl2.created_at', '>=', $from)
                ->groupBy(DB::raw('DATE(dl2.created_at)'))->select(DB::raw('DATE(dl2.created_at) as d'), DB::raw('COUNT(*) as v'))->pluck('v', 'd');
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i day"));
                $trend[] = ['label' => date('d M', strtotime($d)), 'value' => (int) ($rows[$d] ?? 0)];
            }
            $recent = $this->scoped('device_logins', 'dl3')->leftJoin('users as us', 'us.id', '=', 'dl3.user_id')
                ->leftJoin('employees as e', 'e.id', '=', 'us.employee_id')
                ->leftJoin('branches as b', 'b.id', '=', 'us.branch_id')
                ->orderBy('dl3.id', 'desc')->limit(10)
                ->get(['dl3.created_at', 'us.email', 'e.first_name', 'e.last_name', 'b.branch_name', 'dl3.otp_verification_status'])
                ->map(function ($r) {
                    return [
                        'time' => $r->created_at ? date('d-m-Y h:i A', strtotime($r->created_at)) : '-',
                        'user' => trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: $r->email,
                        'login' => $r->email,
                        'branch' => $r->branch_name ?: '-',
                        'device' => 'Mobile',
                        'status' => (int) $r->otp_verification_status === 1 ? 'Success' : 'Pending',
                    ];
                })->toArray();
        }

        return [
            'kpis' => [
                $this->kpi('Total Users', $total, 'All logins'),
                $this->kpi('Active Users', $active, 'Enabled'),
                $this->kpi('Inactive Users', $total - $active, 'Disabled'),
                $this->kpi('Staff Users', $staffCount, 'Office & field'),
                $this->kpi('Customer Logins', $customers, 'Customer app'),
                $this->kpi('Login Attempts', $logins, 'Device logins this month'),
            ],
            'branch' => $this->block('Branch Wise User Count', $this->byBranch(clone $staff, 'u.branch_id')),
            'donut' => $this->block('User Role Distribution', $roles),
            'trend' => $this->block('Device Logins (Last 7 Days) - ' . $devices . ' devices', $trend),
            'table' => $this->table('Login Activity', [
                ['key' => 'time', 'label' => 'Time'],
                ['key' => 'user', 'label' => 'Employee'],
                ['key' => 'login', 'label' => 'Login'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'device', 'label' => 'Device'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $recent, 'view-employee-login'),
        ];
    }

    private function reports()
    {
        $customers = $this->scoped('customers', 'c')->count();
        $active = (clone $this->enrollments())->where('cd.status', 1);
        $activeCount = (clone $active)->count();
        $value = (float) (clone $active)->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')->sum('s.chit_value');
        $month = (float) (clone $this->receipts())->where('r.receipt_date', '>=', $this->monthStart())->sum('r.amount');
        $employees = $this->scoped('employees', 'e')->where('e.status', 1)->count();
        $branches = count($this->branchList());

        $bands = (clone $active)->leftJoin('schemes as s2', 's2.id', '=', 'cd.scheme_id')
            ->select(DB::raw("SUM(CASE WHEN s2.chit_value >= 500000 THEN 1 ELSE 0 END) as gold"),
                DB::raw("SUM(CASE WHEN s2.chit_value >= 100000 AND s2.chit_value < 500000 THEN 1 ELSE 0 END) as silver"),
                DB::raw("SUM(CASE WHEN s2.chit_value < 100000 OR s2.chit_value IS NULL THEN 1 ELSE 0 END) as regular"))
            ->first();
        $sixFrom = $this->months(6)[0]['key'] . '-01';

        return [
            'kpis' => [
                $this->kpi('Total Customers', $customers, 'Profiles'),
                $this->kpi('Active Enrolments', $activeCount, 'Running chits'),
                $this->kpi('Monthly Collection', $month, 'This month', true),
                $this->kpi('Total Chit Value', $value, 'Active enrolments', true),
                $this->kpi('Active Employees', $employees, 'Staff'),
                $this->kpi('Total Branches', $branches, 'Offices'),
            ],
            'branch' => $this->block('Branch Wise Collection (Last 6 Months)', $this->byBranch((clone $this->receipts())->where('r.receipt_date', '>=', $sixFrom), 'r.branch_id', 'r.amount'), true),
            'donut' => $this->block('Customer Segmentation', [
                ['label' => 'Gold (5L+)', 'value' => (int) ($bands->gold ?? 0)],
                ['label' => 'Silver (1L-5L)', 'value' => (int) ($bands->silver ?? 0)],
                ['label' => 'Regular (<1L)', 'value' => (int) ($bands->regular ?? 0)],
            ]),
            'trend' => $this->block('Collection Trend', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', 'r.amount'), true),
            'table' => $this->table('', [], []),
        ];
    }

    /* ---------- dashboard card overviews ---------- */

    private function rAmt($alias = 'r')
    {
        return "COALESCE($alias.received_amount,0) + COALESCE($alias.penalty,0)";
    }

    private function dayTrend($query, $dateColumn, $agg = 'count', $days = 14)
    {
        $from = date('Y-m-d', strtotime('-' . ($days - 1) . ' day'));
        $rows = $query->where($dateColumn, '>=', $from)
            ->groupBy(DB::raw("DATE($dateColumn)"))
            ->select(DB::raw("DATE($dateColumn) as d"), DB::raw($agg === 'count' ? 'COUNT(*) as v' : "SUM($agg) as v"))
            ->pluck('v', 'd');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $out[] = ['label' => date('d M', strtotime($d)), 'value' => (float) ($rows[$d] ?? 0)];
        }
        return $out;
    }

    private function empName($first, $last)
    {
        return trim(($first ?? '') . ' ' . ($last ?? ''));
    }

    private function todayCollection()
    {
        $today = date('Y-m-d');
        $amt = $this->rAmt();
        $q = (clone $this->receipts())->where('r.receipt_date', $today);
        $total = (float) (clone $q)->sum(DB::raw($amt));
        $count = (clone $q)->count();
        $customers = (clone $q)->distinct()->count('r.customer_id');
        $rf = Schema::hasTable('before_enrol_receipts')
            ? (float) $this->scoped('before_enrol_receipts', 'be')->where('be.status', 1)->where('be.received_date', $today)->sum('be.amount') : 0;
        $adv = Schema::hasTable('advance_receipts')
            ? (float) $this->scoped('advance_receipts', 'ar')->where('ar.status', 1)->where('ar.receipt_date', $today)->sum('ar.receipt_amount') : 0;

        $modes = (clone $q)->leftJoin('payment_types as pt', 'pt.id', '=', 'r.payment_type_id')
            ->groupBy('pt.payment_name')->select('pt.payment_name', DB::raw("SUM($amt) as v"))->get()
            ->map(function ($r) {
                return ['label' => $r->payment_name ?: 'Others', 'value' => (float) $r->v];
            })->toArray();

        $rows = (clone $q)->leftJoin('customers as c', 'c.id', '=', 'r.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'r.branch_id')
            ->leftJoin('employees as e', 'e.id', '=', 'r.employee_id')
            ->leftJoin('payment_types as pt', 'pt.id', '=', 'r.payment_type_id')
            ->orderBy('r.id', 'desc')->limit(300)
            ->get(['r.receipt_no', 'c.customer_code', 'c.name', 'b.branch_name', 'e.first_name', 'e.last_name', 'pt.payment_name', DB::raw("($amt) as amt")])
            ->map(function ($r) {
                return [
                    'receipt_no' => $r->receipt_no,
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'branch' => $r->branch_name,
                    'employee' => $this->empName($r->first_name, $r->last_name) ?: 'Office',
                    'mode' => $r->payment_name ?: '-',
                    'amount' => (float) $r->amt,
                ];
            })->toArray();

        $byEmp = (clone $q)->leftJoin('employees as e', 'e.id', '=', 'r.employee_id')
            ->leftJoin('branches as b', 'b.id', '=', 'r.branch_id')
            ->groupBy('r.employee_id')
            ->select(DB::raw("MAX(TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,'')))) as name"),
                DB::raw('MAX(b.branch_name) as branch'), DB::raw('COUNT(*) as cnt'), DB::raw("SUM($amt) as v"))
            ->orderBy('v', 'desc')->get()
            ->map(function ($r) {
                return ['employee' => $r->name ?: 'Office', 'branch' => $r->branch, 'receipts' => (int) $r->cnt, 'amount' => (float) $r->v];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Today Collection', $total, $count . ' receipts', true),
                $this->kpi('Customers Paid', $customers, date('d M Y')),
                $this->kpi('RF Collection', $rf, 'Before enrolment', true),
                $this->kpi('Advance Receipts', $adv, 'Today', true),
                $this->kpi('Grand Total', $total + $rf + $adv, 'All receipts today', true),
            ],
            'branch' => $this->block('Branch Wise Collection (Today)', $this->byBranch(clone $q, 'r.branch_id', $amt), true),
            'donut' => $this->block('Payment Mode (Today)', $modes, true),
            'trend' => $this->block('Daily Collection (Last 14 Days)', $this->dayTrend(clone $this->receipts(), 'r.receipt_date', $amt), true),
            'table' => $this->table('Today Receipts', [
                ['key' => 'receipt_no', 'label' => 'Receipt No'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'employee', 'label' => 'Collected By'],
                ['key' => 'mode', 'label' => 'Mode'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
            ], $rows, 'receipt-report'),
            'extra_tables' => [
                $this->table('Employee Wise Collection (Today)', [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'receipts', 'label' => 'Receipts'],
                    ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ], $byEmp),
            ],
        ];
    }

    /** Monthly targets overlapping this month, grouped by employee. */
    private function monthTargets(array $types)
    {
        if (!Schema::hasTable('employee_targets')) {
            return collect();
        }
        $q = $this->scoped('employee_targets', 't')->where('t.status', 1)
            ->where('t.period_start', '<=', date('Y-m-t'))->where('t.period_end', '>=', $this->monthStart());
        if (Schema::hasColumn('employee_targets', 'target_type')) {
            $q->where(function ($w) use ($types) {
                $w->whereIn('t.target_type', $types);
                if (in_array('collection', $types, true)) {
                    $w->orWhereNull('t.target_type')->orWhere('t.target_type', '');
                }
            });
        }
        return $q->groupBy('t.employee_id')
            ->select('t.employee_id', DB::raw('MAX(t.branch_id) as branch_id'), DB::raw('SUM(t.target_amount) as target'), DB::raw('SUM(t.achieved_amount) as achieved'))
            ->get()->keyBy('employee_id');
    }

    private function newBusiness()
    {
        return $this->scoped('chit_details', 'cd')->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->where('cd.created_at', '>=', $this->monthStart());
    }

    private function sales()
    {
        $targets = $this->monthTargets(['new_business', 'sales']);
        $target = (float) $targets->sum('target');
        $biz = $this->newBusiness();
        $count = (clone $biz)->count();
        $value = (float) (clone $biz)->sum('s.chit_value');
        $achieved = (float) $targets->sum('achieved') ?: $value;
        $pct = $target > 0 ? round($achieved * 100 / $target) : 0;

        $seller = 'COALESCE(NULLIF(cd.introducer_id,0), cd.collection_agent_id)';
        $empSales = (clone $biz)->leftJoin('employees as e', 'e.id', '=', DB::raw($seller))
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
            ->groupBy(DB::raw($seller))
            ->select(DB::raw("$seller as eid"), DB::raw("MAX(TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,'')))) as name"),
                DB::raw('MAX(b.branch_name) as branch'), DB::raw('COUNT(*) as cnt'), DB::raw('SUM(s.chit_value) as v'))
            ->orderBy('v', 'desc')->get()
            ->map(function ($r) use ($targets) {
                $t = $targets[$r->eid] ?? null;
                $tg = $t ? (float) $t->target : 0;
                return [
                    'employee' => $r->name ?: 'Office / Walk-in',
                    'branch' => $r->branch,
                    'chits' => (int) $r->cnt,
                    'value' => (float) $r->v,
                    'target' => $tg,
                    'pct' => $tg > 0 ? round($r->v * 100 / $tg) . '%' : '-',
                ];
            })->toArray();

        $branchSales = (clone $biz)->groupBy('cd.branch_id')
            ->select('cd.branch_id', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(s.chit_value) as v'))->get()->keyBy('branch_id');
        $branchTargets = [];
        foreach ($targets as $t) {
            $branchTargets[$t->branch_id] = ($branchTargets[$t->branch_id] ?? 0) + (float) $t->target;
        }
        $branchRows = [];
        foreach ($this->branchList() as $b) {
            if ($this->branch && (int) $b->id !== $this->branch) {
                continue;
            }
            $s = $branchSales[$b->id] ?? null;
            $tg = (float) ($branchTargets[$b->id] ?? 0);
            $v = $s ? (float) $s->v : 0;
            $branchRows[] = [
                'branch' => $b->branch_name,
                'chits' => $s ? (int) $s->cnt : 0,
                'value' => $v,
                'target' => $tg,
                'pct' => $tg > 0 ? round($v * 100 / $tg) . '%' : '-',
            ];
        }
        usort($branchRows, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        $trend = $this->scoped('chit_details', 'cd2')->leftJoin('schemes as s2', 's2.id', '=', 'cd2.scheme_id');

        return [
            'kpis' => [
                $this->kpi('Sales Target', $target, 'This month', true),
                $this->kpi('Achieved', $achieved, $pct . '% of target', true),
                $this->kpi('Pending', max(0, $target - $achieved), 'To reach target', true),
                $this->kpi('New Chits Sold', $count, 'This month'),
                $this->kpi('New Business Value', $value, 'Chit value sold', true),
            ],
            'branch' => $this->block('Branch Wise Sales (This Month)', $this->byBranch(clone $biz, 'cd.branch_id', 's.chit_value'), true),
            'donut' => $this->block('Target vs Achieved', [
                ['label' => 'Achieved', 'value' => min($achieved, $target ?: $achieved)],
                ['label' => 'Pending', 'value' => max(0, $target - $achieved)],
            ], true),
            'trend' => $this->block('New Business Trend (6 Months)', $this->monthTrend($trend, 'cd2.created_at', 's2.chit_value'), true),
            'table' => $this->table('Top 3 Employees (Sales)', [
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'chits', 'label' => 'Chits Sold'],
                ['key' => 'value', 'label' => 'Sales Value', 'type' => 'money'],
                ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
                ['key' => 'pct', 'label' => 'Achieved'],
            ], array_slice($empSales, 0, 3), 'set-employee-target'),
            'extra_tables' => [
                $this->table('Top 3 Branches (Sales)', [
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'chits', 'label' => 'Chits Sold'],
                    ['key' => 'value', 'label' => 'Sales Value', 'type' => 'money'],
                    ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
                    ['key' => 'pct', 'label' => 'Achieved'],
                ], array_slice($branchRows, 0, 3)),
                $this->table('All Employees Sales (This Month)', [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'chits', 'label' => 'Chits Sold'],
                    ['key' => 'value', 'label' => 'Sales Value', 'type' => 'money'],
                    ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
                    ['key' => 'pct', 'label' => 'Achieved'],
                ], $empSales),
            ],
        ];
    }

    private function todayEnrollment()
    {
        $today = date('Y-m-d');
        $q = $this->scoped('chit_details', 'cd')->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->whereDate('cd.created_at', $today);
        $count = (clone $q)->count();
        $customers = (clone $q)->distinct()->count('cd.customer_id');
        $value = (float) (clone $q)->sum('s.chit_value');
        $month = $this->scoped('chit_details', 'cm')->where('cm.created_at', '>=', $this->monthStart())->count();

        $schemes = (clone $q)->groupBy('s.chit_value')->select('s.chit_value', DB::raw('COUNT(*) as v'))->get()
            ->map(function ($r) {
                return ['label' => $this->shortMoney($r->chit_value), 'value' => (int) $r->v];
            })->toArray();

        $seller = 'COALESCE(NULLIF(cd.introducer_id,0), cd.collection_agent_id)';
        $rows = (clone $q)->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id')
            ->leftJoin('employees as e', 'e.id', '=', DB::raw($seller))
            ->orderBy('cd.id', 'desc')->limit(300)
            ->get(['cd.ar_no', 'c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name', 's.chit_value', 's.no_of_months', 's.monthly_due', 'e.first_name', 'e.last_name', 'cd.created_at'])
            ->map(function ($r) {
                return [
                    'ar_no' => $r->ar_no ?: '-',
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'scheme' => $this->shortMoney($r->chit_value) . ' / ' . $r->no_of_months . 'M',
                    'monthly_due' => (float) $r->monthly_due,
                    'employee' => $this->empName($r->first_name, $r->last_name) ?: 'Office',
                    'time' => date('h:i A', strtotime($r->created_at)),
                ];
            })->toArray();

        $trend = $this->scoped('chit_details', 'cd2');

        return [
            'kpis' => [
                $this->kpi('Today Enrolments', $count, date('d M Y')),
                $this->kpi('New Customers', $customers, 'Joined today'),
                $this->kpi('Chit Value', $value, 'Enrolled today', true),
                $this->kpi('This Month', $month, 'Enrolments'),
            ],
            'branch' => $this->block('Branch Wise Enrolments (Today)', $this->byBranch(clone $q, 'cd.branch_id')),
            'donut' => $this->block('Scheme Wise (Today)', $schemes),
            'trend' => $this->block('Daily Enrolments (Last 14 Days)', $this->dayTrend($trend, 'cd2.created_at')),
            'table' => $this->table('Today Enrolment Details', [
                ['key' => 'ar_no', 'label' => 'AR No'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'scheme', 'label' => 'Scheme'],
                ['key' => 'monthly_due', 'label' => 'Monthly Due', 'type' => 'money'],
                ['key' => 'employee', 'label' => 'Enrolled By'],
                ['key' => 'time', 'label' => 'Time'],
            ], $rows, 'view-commitment-chit'),
        ];
    }

    /** Active enrolments with this month's due vs this month's receipts. */
    private function dueRows()
    {
        $paidSub = DB::table('receipts')->where('status', 1)->where('receipt_date', '>=', $this->monthStart())
            ->groupBy('enrollment_id')->select('enrollment_id', DB::raw('SUM(COALESCE(received_amount,0)) as paid'));
        return $this->scoped('chit_details', 'cd')->where('cd.status', 1)
            ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->leftJoinSub($paidSub, 'pm', 'pm.enrollment_id', '=', 'cd.id')
            ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id');
    }

    private function pendingCollection()
    {
        $base = $this->dueRows()->whereRaw('COALESCE(s.monthly_due,0) > COALESCE(pm.paid,0)');
        $pendingExpr = 'COALESCE(s.monthly_due,0) - COALESCE(pm.paid,0)';
        $count = (clone $base)->count();
        $amount = (float) (clone $base)->sum(DB::raw($pendingExpr));
        $due = (float) $this->dueRows()->sum('s.monthly_due');
        $collected = max(0, $due - $amount);

        $partial = (clone $base)->where('pm.paid', '>', 0)->count();
        $rows = (clone $base)->orderBy(DB::raw($pendingExpr), 'desc')->limit(300)
            ->get(['cd.ar_no', 'c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name', 's.chit_value', 's.monthly_due', 'pm.paid', DB::raw("($pendingExpr) as pending")])
            ->map(function ($r) {
                return [
                    'ar_no' => $r->ar_no ?: '-',
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'chit_value' => (float) $r->chit_value,
                    'due' => (float) $r->monthly_due,
                    'paid' => (float) $r->paid,
                    'pending' => (float) $r->pending,
                    'status' => (float) $r->paid > 0 ? 'Partially Paid' : 'Not Paid',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Pending Collection', $amount, 'This month due not yet paid', true),
                $this->kpi('Pending Customers', $count, 'Enrolments'),
                $this->kpi('This Month Due', $due, 'All active enrolments', true),
                $this->kpi('Collected Against Due', $collected, $due > 0 ? round($collected * 100 / $due) . '% collected' : '', true),
                $this->kpi('Partially Paid', $partial, 'Paid part of due'),
            ],
            'branch' => $this->block('Branch Wise Pending (This Month)', $this->byBranch(clone $base, 'cd.branch_id', $pendingExpr), true),
            'donut' => $this->block('Due Status (This Month)', [
                ['label' => 'Collected', 'value' => $collected],
                ['label' => 'Pending', 'value' => $amount],
            ], true),
            'trend' => $this->block('Collection Trend (6 Months)', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', $this->rAmt()), true),
            'table' => $this->table('Pending Collection List (This Month)', [
                ['key' => 'ar_no', 'label' => 'AR No'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'chit_value', 'label' => 'Chit Value', 'type' => 'money'],
                ['key' => 'due', 'label' => 'Due', 'type' => 'money'],
                ['key' => 'paid', 'label' => 'Paid', 'type' => 'money'],
                ['key' => 'pending', 'label' => 'Pending', 'type' => 'money'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $rows, 'this-month-unpaid-report'),
        ];
    }

    private function overdueCollection()
    {
        $hasPending = Schema::hasColumn('chit_details', 'pending_amount');
        if (!$hasPending) {
            return $this->emptySection('Overdue');
        }
        $base = $this->scoped('chit_details', 'cd')->where('cd.status', 1)->where('cd.pending_amount', '>', 0)
            ->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id')
            ->leftJoin('customers as c', 'c.id', '=', 'cd.customer_id')
            ->leftJoin('branches as b', 'b.id', '=', 'cd.branch_id');
        $count = (clone $base)->count();
        $amount = (float) (clone $base)->sum('cd.pending_amount');
        $bucketExpr = "CASE WHEN cd.pending_amount <= s.monthly_due THEN '1 Due' WHEN cd.pending_amount <= s.monthly_due * 3 THEN '2-3 Dues' ELSE '3+ Dues' END";
        $buckets = (clone $base)->groupBy(DB::raw($bucketExpr))
            ->select(DB::raw("$bucketExpr as label"), DB::raw('SUM(cd.pending_amount) as v'))->get()
            ->map(function ($r) {
                return ['label' => $r->label, 'value' => (float) $r->v];
            })->toArray();
        $high = (clone $base)->whereRaw('cd.pending_amount > s.monthly_due * 3')->count();

        $rows = (clone $base)->orderBy('cd.pending_amount', 'desc')->limit(300)
            ->get(['cd.ar_no', 'c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name', 's.chit_value', 's.monthly_due', 'cd.pending_amount'])
            ->map(function ($r) {
                $dues = (float) $r->monthly_due > 0 ? round($r->pending_amount / $r->monthly_due, 1) : 0;
                return [
                    'ar_no' => $r->ar_no ?: '-',
                    'customer' => trim(($r->name ?? '') . ($r->customer_code ? ' (' . $r->customer_code . ')' : '')),
                    'mobile' => $r->mobile_no,
                    'branch' => $r->branch_name,
                    'chit_value' => (float) $r->chit_value,
                    'monthly_due' => (float) $r->monthly_due,
                    'pending' => (float) $r->pending_amount,
                    'dues' => $dues,
                    'status' => $dues > 3 ? 'High Overdue' : 'Overdue',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Overdue Amount', $amount, 'Old dues pending', true),
                $this->kpi('Overdue Customers', $count, 'Enrolments'),
                $this->kpi('High Risk (3+ dues)', $high, 'Follow-up urgently'),
                $this->kpi('Avg Overdue', $count ? $amount / $count : 0, 'Per enrolment', true),
            ],
            'branch' => $this->block('Branch Wise Overdue', $this->byBranch(clone $base, 'cd.branch_id', 'cd.pending_amount'), true),
            'donut' => $this->block('Overdue Ageing', $buckets, true),
            'trend' => $this->block('Collection Trend (6 Months)', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', $this->rAmt()), true),
            'table' => $this->table('Overdue Customer List', [
                ['key' => 'ar_no', 'label' => 'AR No'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'chit_value', 'label' => 'Chit Value', 'type' => 'money'],
                ['key' => 'monthly_due', 'label' => 'Monthly Due', 'type' => 'money'],
                ['key' => 'pending', 'label' => 'Overdue', 'type' => 'money'],
                ['key' => 'dues', 'label' => 'No. of Dues'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $rows, 'commitmentcustomer-outstanding-report'),
        ];
    }

    private function topPerformers()
    {
        $amt = $this->rAmt();
        $month = (clone $this->receipts())->where('r.receipt_date', '>=', $this->monthStart());
        $targets = $this->monthTargets(['existing_client_collection', 'new_client_collection', 'collection']);
        $sales = $this->monthTargets(['new_business', 'sales']);
        $seller = 'COALESCE(NULLIF(cd.introducer_id,0), cd.collection_agent_id)';
        $newChits = $this->newBusiness()->groupBy(DB::raw($seller))
            ->select(DB::raw("$seller as eid"), DB::raw('COUNT(*) as cnt'), DB::raw('SUM(s.chit_value) as v'))->get()->keyBy('eid');

        $empRows = (clone $month)->where('r.employee_id', '>', 0)
            ->leftJoin('employees as e', 'e.id', '=', 'r.employee_id')
            ->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')
            ->groupBy('r.employee_id')
            ->select('r.employee_id', DB::raw("MAX(TRIM(CONCAT(COALESCE(e.first_name,''),' ',COALESCE(e.last_name,'')))) as name"),
                DB::raw('MAX(b.branch_name) as branch'), DB::raw('COUNT(*) as cnt'), DB::raw('COUNT(DISTINCT r.customer_id) as cust'), DB::raw("SUM($amt) as v"))
            ->orderBy('v', 'desc')->get()
            ->map(function ($r) use ($targets, $sales, $newChits) {
                $tg = isset($targets[$r->employee_id]) ? (float) $targets[$r->employee_id]->target : 0;
                $sl = $newChits[$r->employee_id] ?? null;
                $pct = $tg > 0 ? round($r->v * 100 / $tg) : 0;
                return [
                    'employee' => $r->name ?: ('Employee ' . $r->employee_id),
                    'branch' => $r->branch,
                    'collection' => (float) $r->v,
                    'target' => $tg,
                    'achieved' => $tg > 0 ? $pct . '%' : '-',
                    'receipts' => (int) $r->cnt,
                    'customers' => (int) $r->cust,
                    'new_chits' => $sl ? (int) $sl->cnt : 0,
                    'sales' => $sl ? (float) $sl->v : 0,
                    'why' => $this->whyBest($pct, (int) $r->cnt, $sl ? (int) $sl->cnt : 0),
                ];
            })->toArray();

        $branchColl = (clone $month)->groupBy('r.branch_id')
            ->select('r.branch_id', DB::raw("SUM($amt) as v"), DB::raw('COUNT(DISTINCT r.customer_id) as cust'), DB::raw('COUNT(*) as cnt'))->get()->keyBy('branch_id');
        $branchNew = $this->newBusiness()->groupBy('cd.branch_id')
            ->select('cd.branch_id', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(s.chit_value) as v'))->get()->keyBy('branch_id');
        $branchTarget = [];
        foreach ($targets as $t) {
            $branchTarget[$t->branch_id] = ($branchTarget[$t->branch_id] ?? 0) + (float) $t->target;
        }
        $branchRows = [];
        foreach ($this->branchList() as $b) {
            if ($this->branch && (int) $b->id !== $this->branch) {
                continue;
            }
            $c = $branchColl[$b->id] ?? null;
            $n = $branchNew[$b->id] ?? null;
            $tg = (float) ($branchTarget[$b->id] ?? 0);
            $v = $c ? (float) $c->v : 0;
            $pct = $tg > 0 ? round($v * 100 / $tg) : 0;
            $branchRows[] = [
                'branch' => $b->branch_name,
                'collection' => $v,
                'target' => $tg,
                'achieved' => $tg > 0 ? $pct . '%' : '-',
                'receipts' => $c ? (int) $c->cnt : 0,
                'customers' => $c ? (int) $c->cust : 0,
                'new_chits' => $n ? (int) $n->cnt : 0,
                'sales' => $n ? (float) $n->v : 0,
                'why' => $this->whyBest($pct, $c ? (int) $c->cnt : 0, $n ? (int) $n->cnt : 0),
            ];
        }
        usort($branchRows, function ($a, $b) {
            return $b['collection'] <=> $a['collection'];
        });

        $total = (float) (clone $month)->sum(DB::raw($amt));
        $top3 = array_slice($empRows, 0, 3);
        $top3Total = array_sum(array_column($top3, 'collection'));
        $cols = [
            ['key' => 'collection', 'label' => 'Collection', 'type' => 'money'],
            ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
            ['key' => 'achieved', 'label' => 'Achieved'],
            ['key' => 'receipts', 'label' => 'Receipts'],
            ['key' => 'customers', 'label' => 'Customers'],
            ['key' => 'new_chits', 'label' => 'New Chits'],
            ['key' => 'sales', 'label' => 'New Business', 'type' => 'money'],
            ['key' => 'why', 'label' => 'Why Best'],
        ];

        return [
            'kpis' => [
                $this->kpi('Month Collection', $total, 'All employees', true),
                $this->kpi('Top Employee', $top3 ? $top3[0]['collection'] : 0, $top3 ? $top3[0]['employee'] : '-', true),
                $this->kpi('Top Branch', $branchRows ? $branchRows[0]['collection'] : 0, $branchRows ? $branchRows[0]['branch'] : '-', true),
                $this->kpi('Top 3 Share', $total > 0 ? round($top3Total * 100 / $total) : 0, '% of total collection'),
                $this->kpi('Collection Target', (float) $targets->sum('target'), 'This month', true),
                $this->kpi('Sales Target', (float) $sales->sum('target'), 'This month', true),
            ],
            'branch' => $this->block('Branch Wise Collection (This Month)', $this->byBranch(clone $month, 'r.branch_id', $amt), true),
            'donut' => $this->block('Top 3 Employees Share', array_merge(
                array_map(function ($r) {
                    return ['label' => $r['employee'], 'value' => $r['collection']];
                }, $top3),
                [['label' => 'Others', 'value' => max(0, $total - $top3Total)]]
            ), true),
            'trend' => $this->block('Collection Trend (6 Months)', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', $amt), true),
            'table' => $this->table('Top 3 Employees — How They Are Best', array_merge([
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'branch', 'label' => 'Branch'],
            ], $cols), $top3, 'employee-collection-report'),
            'extra_tables' => [
                $this->table('Top 3 Branches — How They Are Best', array_merge([['key' => 'branch', 'label' => 'Branch']], $cols), array_slice($branchRows, 0, 3)),
                $this->table('All Employees Ranking (This Month)', array_merge([
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'branch', 'label' => 'Branch'],
                ], $cols), $empRows),
                $this->table('All Branches Ranking (This Month)', array_merge([['key' => 'branch', 'label' => 'Branch']], $cols), $branchRows),
            ],
        ];
    }

    private function whyBest($pct, $receipts, $newChits)
    {
        $parts = [];
        if ($pct > 0) {
            $parts[] = $pct . '% of target';
        }
        $parts[] = $receipts . ' receipts';
        if ($newChits > 0) {
            $parts[] = $newChits . ' new chits';
        }
        return implode(', ', $parts);
    }

    private function attendance()
    {
        $today = date('Y-m-d');
        $emp = $this->scoped('employees', 'e')->where('e.status', 1);
        $total = (clone $emp)->count();
        $hasAtt = Schema::hasTable('employee_attendances');
        $hasTime = $hasAtt && Schema::hasColumn('employee_attendances', 'check_in_time');
        $att = [];
        if ($hasAtt) {
            $cols = ['a.employee_id', 'a.attendance_type'];
            if ($hasTime) {
                $cols = array_merge($cols, ['a.check_in_time', 'a.check_out_time']);
            }
            $att = $this->scoped('employee_attendances', 'a')->where('a.att_date', $today)->orderBy('a.id')->get($cols)->keyBy('employee_id');
        }
        $leave = [];
        if (Schema::hasTable('employee_leave_requests') && Schema::hasColumn('employee_leave_requests', 'request_status')) {
            $leave = $this->scoped('employee_leave_requests', 'lr')->where('lr.request_status', 'approved')
                ->where('lr.from_date', '<=', $today)->where('lr.to_date', '>=', $today)->pluck('lr.employee_id')->flip()->toArray();
        }
        $live = [];
        if (Schema::hasTable('employee_live_locations')) {
            $live = DB::table('employee_live_locations')->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('-30 minutes')))
                ->pluck('employee_id')->flip()->toArray();
        }

        $rows = [];
        $present = 0;
        $onLeave = 0;
        $branchPresent = [];
        foreach ((clone $emp)->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')->orderBy('b.branch_name')->orderBy('e.first_name')
            ->get(['e.id', 'e.first_name', 'e.last_name', 'e.mobile_no', 'e.branch_id', 'b.branch_name']) as $e) {
            $a = $att[$e->id] ?? null;
            $isPresent = $a && (!$hasTime || !empty($a->check_in_time) || (int) $a->attendance_type === 1);
            $status = $isPresent ? 'Present' : (isset($leave[$e->id]) ? 'On Leave' : 'Absent');
            if ($isPresent) {
                $present++;
                $branchPresent[$e->branch_id] = ($branchPresent[$e->branch_id] ?? 0) + 1;
            } elseif ($status === 'On Leave') {
                $onLeave++;
            }
            $rows[] = [
                'employee' => $this->empName($e->first_name, $e->last_name),
                'mobile' => $e->mobile_no,
                'branch' => $e->branch_name,
                'in' => $a && $hasTime && $a->check_in_time ? date('h:i A', strtotime($a->check_in_time)) : '-',
                'out' => $a && $hasTime && $a->check_out_time ? date('h:i A', strtotime($a->check_out_time)) : '-',
                'live' => isset($live[$e->id]) ? 'Live' : '-',
                'status' => $status,
            ];
        }
        $absent = max(0, $total - $present - $onLeave);
        usort($rows, function ($x, $y) {
            $order = ['Absent' => 0, 'On Leave' => 1, 'Present' => 2];
            return $order[$x['status']] <=> $order[$y['status']];
        });

        $branchRows = [];
        foreach ($this->branchList() as $b) {
            if ($this->branch && (int) $b->id !== $this->branch) {
                continue;
            }
            $branchRows[] = ['label' => $this->branchShort($b), 'name' => $b->branch_name, 'value' => (float) ($branchPresent[$b->id] ?? 0)];
        }

        $trend = [];
        if ($hasAtt) {
            $from = date('Y-m-d', strtotime('-13 day'));
            $daily = $this->scoped('employee_attendances', 'a2')->where('a2.att_date', '>=', $from)
                ->groupBy('a2.att_date')->select('a2.att_date', DB::raw('COUNT(DISTINCT a2.employee_id) as v'))->pluck('v', 'att_date');
            for ($i = 13; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i day"));
                $trend[] = ['label' => date('d M', strtotime($d)), 'value' => (int) ($daily[$d] ?? 0)];
            }
        }

        return [
            'kpis' => [
                $this->kpi('Total Employees', $total, 'Active staff'),
                $this->kpi('Present', $present, $total ? round($present * 100 / $total) . '% attendance' : ''),
                $this->kpi('Absent', $absent, 'Not punched in'),
                $this->kpi('On Leave', $onLeave, 'Approved leave'),
                $this->kpi('Live Tracking', count(array_intersect_key($live, $att)), 'GPS in last 30 min'),
            ],
            'branch' => $this->block('Branch Wise Present (Today)', $branchRows),
            'donut' => $this->block('Today Attendance', [
                ['label' => 'Present', 'value' => $present],
                ['label' => 'Absent', 'value' => $absent],
                ['label' => 'On Leave', 'value' => $onLeave],
            ]),
            'trend' => $this->block('Present Count (Last 14 Days)', $trend),
            'table' => $this->table('Today Attendance — All Employees', [
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'in', 'label' => 'Check In'],
                ['key' => 'out', 'label' => 'Check Out'],
                ['key' => 'live', 'label' => 'Live GPS'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $rows, 'view-employee-attendance'),
        ];
    }

    private function birthdays()
    {
        $emp = $this->scoped('employees', 'e')->where('e.status', 1)->whereNotNull('e.dob');
        $todayMd = date('m-d');
        $todayCount = (clone $emp)->whereRaw("DATE_FORMAT(e.dob, '%m-%d') = ?", [$todayMd])->count();
        $monthQ = (clone $emp)->whereRaw('MONTH(e.dob) = ?', [date('n')]);
        $monthCount = (clone $monthQ)->count();
        $cust = $this->scoped('customers', 'c')->whereNotNull('c.dob');
        $custToday = (clone $cust)->whereRaw("DATE_FORMAT(c.dob, '%m-%d') = ?", [$todayMd])->count();
        $custMonth = (clone $cust)->whereRaw('MONTH(c.dob) = ?', [date('n')])->count();

        $rows = (clone $monthQ)->leftJoin('branches as b', 'b.id', '=', 'e.branch_id')
            ->leftJoin('designations as d', 'd.id', '=', 'e.designation_id')
            ->orderBy(DB::raw('DAY(e.dob)'))
            ->get(['e.first_name', 'e.last_name', 'e.mobile_no', 'e.dob', 'b.branch_name', 'd.designation_name'])
            ->map(function ($r) use ($todayMd) {
                $md = date('m-d', strtotime($r->dob));
                return [
                    'date' => date('d M', strtotime($r->dob)),
                    'employee' => $this->empName($r->first_name, $r->last_name),
                    'designation' => $r->designation_name,
                    'branch' => $r->branch_name,
                    'mobile' => $r->mobile_no,
                    'status' => $md === $todayMd ? 'Today' : ($md < $todayMd ? 'Completed' : 'Upcoming'),
                ];
            })->toArray();
        $custRows = (clone $cust)->whereRaw("DATE_FORMAT(c.dob, '%m-%d') = ?", [$todayMd])
            ->leftJoin('branches as b', 'b.id', '=', 'c.branch_id')->limit(200)
            ->get(['c.customer_code', 'c.name', 'c.mobile_no', 'b.branch_name'])
            ->map(function ($r) {
                return ['customer' => $r->name . ($r->customer_code ? ' (' . $r->customer_code . ')' : ''), 'mobile' => $r->mobile_no, 'branch' => $r->branch_name];
            })->toArray();

        $calendar = [];
        $byMonth = (clone $emp)->groupBy(DB::raw('MONTH(e.dob)'))->select(DB::raw('MONTH(e.dob) as m'), DB::raw('COUNT(*) as v'))->pluck('v', 'm');
        for ($m = 1; $m <= 12; $m++) {
            $calendar[] = ['label' => date('M', mktime(0, 0, 0, $m, 1)), 'value' => (int) ($byMonth[$m] ?? 0)];
        }

        return [
            'kpis' => [
                $this->kpi('Employee Birthday Today', $todayCount, date('d M')),
                $this->kpi('Employee Birthdays This Month', $monthCount, date('F')),
                $this->kpi('Customer Birthday Today', $custToday, date('d M')),
                $this->kpi('Customer Birthdays This Month', $custMonth, date('F')),
            ],
            'branch' => $this->block('Branch Wise Employee Birthdays (This Month)', $this->byBranch(clone $monthQ, 'e.branch_id')),
            'donut' => $this->block('This Month', [
                ['label' => 'Completed', 'value' => count(array_filter($rows, function ($r) { return $r['status'] === 'Completed'; }))],
                ['label' => 'Today', 'value' => $todayCount],
                ['label' => 'Upcoming', 'value' => count(array_filter($rows, function ($r) { return $r['status'] === 'Upcoming'; }))],
            ]),
            'trend' => $this->block('Employee Birthdays per Month', $calendar),
            'table' => $this->table('Employee Birthdays — ' . date('F'), [
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'designation', 'label' => 'Designation'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'mobile', 'label' => 'Mobile'],
                ['key' => 'status', 'label' => 'Wish', 'type' => 'status'],
            ], $rows, 'view-employee'),
            'extra_tables' => [
                $this->table('Customer Birthdays Today', [
                    ['key' => 'customer', 'label' => 'Customer'],
                    ['key' => 'mobile', 'label' => 'Mobile'],
                    ['key' => 'branch', 'label' => 'Branch'],
                ], $custRows, 'customer-birthday-report'),
            ],
        ];
    }

    private function tasksTargets()
    {
        $today = date('Y-m-d');
        $tasks = [];
        $taskStatus = [];
        $total = $done = 0;
        if (Schema::hasTable('employee_tasks')) {
            $t = $this->scoped('employee_tasks', 't')->where(function ($w) use ($today) {
                $w->where('t.task_date', $today)->orWhere('t.due_date', $today);
            });
            $total = (clone $t)->count();
            $done = (clone $t)->where('t.task_status', 'completed')->count();
            $taskStatus = (clone $t)->groupBy('t.task_status')->select('t.task_status', DB::raw('COUNT(*) as v'))->get()
                ->map(function ($r) {
                    return ['label' => ucwords(str_replace('_', ' ', (string) ($r->task_status ?: 'pending'))), 'value' => (int) $r->v];
                })->toArray();
            $tasks = (clone $t)->leftJoin('employees as e', 'e.id', '=', 't.employee_id')->leftJoin('branches as b', 'b.id', '=', 't.branch_id')
                ->orderBy('t.id', 'desc')->limit(300)
                ->get(['t.title', 'e.first_name', 'e.last_name', 'b.branch_name', 't.priority', 't.due_date', 't.task_status', 't.employee_remark'])
                ->map(function ($r) {
                    return [
                        'title' => $r->title,
                        'employee' => $this->empName($r->first_name, $r->last_name),
                        'branch' => $r->branch_name,
                        'priority' => ucfirst((string) $r->priority),
                        'due' => $r->due_date ? date('d-m-Y', strtotime($r->due_date)) : '-',
                        'remark' => $r->employee_remark,
                        'status' => ucwords(str_replace('_', ' ', (string) $r->task_status)),
                    ];
                })->toArray();
        }

        $targetRows = [];
        $targetTotal = $targetAch = 0;
        if (Schema::hasTable('employee_targets')) {
            $tq = $this->scoped('employee_targets', 'tg')->where('tg.status', 1)
                ->where('tg.period_start', '<=', $today)->where('tg.period_end', '>=', $today)
                ->leftJoin('employees as e', 'e.id', '=', 'tg.employee_id')->leftJoin('branches as b', 'b.id', '=', 'tg.branch_id');
            $targetTotal = (float) (clone $tq)->sum('tg.target_amount');
            $targetAch = (float) (clone $tq)->sum('tg.achieved_amount');
            $targetRows = (clone $tq)->orderBy('tg.target_amount', 'desc')->limit(300)
                ->get(['e.first_name', 'e.last_name', 'b.branch_name', 'tg.period_type', 'tg.target_type', 'tg.target_amount', 'tg.achieved_amount'])
                ->map(function ($r) {
                    $pct = (float) $r->target_amount > 0 ? round($r->achieved_amount * 100 / $r->target_amount) : 0;
                    return [
                        'employee' => $this->empName($r->first_name, $r->last_name),
                        'branch' => $r->branch_name,
                        'period' => ucfirst((string) $r->period_type),
                        'type' => ucwords(str_replace('_', ' ', (string) ($r->target_type ?: 'collection'))),
                        'target' => (float) $r->target_amount,
                        'achieved' => (float) $r->achieved_amount,
                        'pct' => $pct . '%',
                        'status' => $pct >= 100 ? 'Completed' : 'In Progress',
                    ];
                })->toArray();
        }
        $todayColl = (float) (clone $this->receipts())->where('r.receipt_date', $today)->sum(DB::raw($this->rAmt()));

        return [
            'kpis' => [
                $this->kpi('Today Tasks', $total, $done . ' completed'),
                $this->kpi('Pending Tasks', $total - $done, 'Open today'),
                $this->kpi('Active Targets', $targetTotal, count($targetRows) . ' targets running', true),
                $this->kpi('Targets Achieved', $targetAch, $targetTotal > 0 ? round($targetAch * 100 / $targetTotal) . '%' : '-', true),
                $this->kpi('Today Collection', $todayColl, 'Receipts today', true),
            ],
            'branch' => $this->block('Branch Wise Tasks (Today)', Schema::hasTable('employee_tasks')
                ? $this->byBranch($this->scoped('employee_tasks', 'tb')->where(function ($w) use ($today) {
                    $w->where('tb.task_date', $today)->orWhere('tb.due_date', $today);
                }), 'tb.branch_id') : []),
            'donut' => $this->block('Task Status (Today)', $taskStatus),
            'trend' => $this->block('Tasks (Last 14 Days)', Schema::hasTable('employee_tasks')
                ? $this->dayTrend($this->scoped('employee_tasks', 'tt'), 'tt.task_date') : []),
            'table' => $this->table('Today Tasks', [
                ['key' => 'title', 'label' => 'Task'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'priority', 'label' => 'Priority', 'type' => 'status'],
                ['key' => 'due', 'label' => 'Due'],
                ['key' => 'remark', 'label' => 'Remark'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $tasks, 'add-task'),
            'extra_tables' => [
                $this->table('Today Targets', [
                    ['key' => 'employee', 'label' => 'Employee'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'period', 'label' => 'Period'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'target', 'label' => 'Target', 'type' => 'money'],
                    ['key' => 'achieved', 'label' => 'Achieved', 'type' => 'money'],
                    ['key' => 'pct', 'label' => '%'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ], $targetRows, 'set-employee-target'),
            ],
        ];
    }

    private function expenses()
    {
        if (!Schema::hasTable('petty_cash_expenses')) {
            return $this->emptySection('Expenses');
        }
        $today = date('Y-m-d');
        $base = $this->scoped('petty_cash_expenses', 'pe')->where('pe.status', '!=', 3)
            ->leftJoin('petty_cash_expense_types as pt', 'pt.id', '=', 'pe.expenses_type_id');
        $todayQ = (clone $base)->where('pe.expenses_date', $today);
        $todayAmt = (float) (clone $todayQ)->sum('pe.expenses_amount');
        $monthQ = (clone $base)->where('pe.expenses_date', '>=', $this->monthStart());
        $monthAmt = (float) (clone $monthQ)->sum('pe.expenses_amount');
        $pendingApproval = (clone $base)->where('pe.status', 2)->count();

        $types = (clone $monthQ)->groupBy('pe.expenses_type_id')
            ->select(DB::raw('MAX(pt.expenses_type) as label'), DB::raw('SUM(pe.expenses_amount) as v'))->orderBy('v', 'desc')->get()
            ->map(function ($r) {
                return ['label' => $r->label ?: 'Other', 'value' => (float) $r->v];
            })->toArray();

        $empCol = Schema::hasColumn('petty_cash_expenses', 'emp_id') ? 'pe.emp_id' : 'pe.employee_id';
        $rows = (clone $todayQ)->leftJoin('employees as e', 'e.id', '=', $empCol)->leftJoin('branches as b', 'b.id', '=', 'pe.branch_id')
            ->orderBy('pe.id', 'desc')->limit(300)
            ->get(['pt.expenses_type', 'b.branch_name', 'e.first_name', 'e.last_name', 'pe.expenses_amount', 'pe.remarks', 'pe.status'])
            ->map(function ($r) {
                return [
                    'type' => $r->expenses_type ?: 'Other',
                    'branch' => $r->branch_name,
                    'employee' => $this->empName($r->first_name, $r->last_name) ?: '-',
                    'amount' => (float) $r->expenses_amount,
                    'remarks' => $r->remarks,
                    'status' => (int) $r->status === 2 ? 'Pending' : 'Approved',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Today Expenses', $todayAmt, count($rows) . ' entries', true),
                $this->kpi('This Month Expenses', $monthAmt, date('F'), true),
                $this->kpi('Pending Approval', $pendingApproval, 'Cheque / bank entries'),
                $this->kpi('Expense Heads', count($types), 'Used this month'),
            ],
            'branch' => $this->block('Branch Wise Expenses (This Month)', $this->byBranch(clone $monthQ, 'pe.branch_id', 'pe.expenses_amount'), true),
            'donut' => $this->block('Expense Type (This Month)', $types, true),
            'trend' => $this->block('Daily Expenses (Last 14 Days)', $this->dayTrend(clone $base, 'pe.expenses_date', 'pe.expenses_amount'), true),
            'table' => $this->table('Today Expenses', [
                ['key' => 'type', 'label' => 'Expense Type'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['key' => 'remarks', 'label' => 'Remarks'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $rows, 'view-petty-cash-expense'),
        ];
    }

    private function settlementApprovals()
    {
        if (!Schema::hasTable('cash_settlements')) {
            return $this->emptySection('Settlements');
        }
        $dateCol = Schema::hasColumn('cash_settlements', 'settlement_date') ? 'settlement_date' : 'date';
        $hasApproval = Schema::hasColumn('cash_settlements', 'is_approved');
        $base = $this->scoped('cash_settlements', 'cs');
        $pendingQ = (clone $base);
        if ($hasApproval) {
            $pendingQ->where(function ($q) {
                $q->whereNull('cs.is_approved')->orWhere('cs.is_approved', 0);
            });
        }
        $pending = (clone $pendingQ)->count();
        $pendingAmt = (float) (clone $pendingQ)->sum('cs.received_amount');
        $approvedMonth = $hasApproval ? (clone $base)->where('cs.is_approved', 1)->where('cs.' . $dateCol, '>=', $this->monthStart())->count() : 0;
        $monthAmt = (float) (clone $base)->where('cs.' . $dateCol, '>=', $this->monthStart())->sum('cs.received_amount');

        $rows = (clone $pendingQ)->leftJoin('employees as e', 'e.id', '=', 'cs.employee_id')->leftJoin('branches as b', 'b.id', '=', 'cs.branch_id')
            ->orderBy('cs.' . $dateCol, 'desc')->limit(300)
            ->get(['cs.' . $dateCol . ' as sdate', 'b.branch_name', 'e.first_name', 'e.last_name', 'cs.received_amount', 'cs.remarks'])
            ->map(function ($r) {
                return [
                    'date' => $r->sdate ? date('d-m-Y', strtotime($r->sdate)) : '-',
                    'branch' => $r->branch_name,
                    'employee' => $this->empName($r->first_name, $r->last_name),
                    'amount' => (float) $r->received_amount,
                    'remarks' => $r->remarks,
                    'status' => 'Pending',
                ];
            })->toArray();

        return [
            'kpis' => [
                $this->kpi('Pending Approvals', $pending, 'Waiting for admin'),
                $this->kpi('Pending Amount', $pendingAmt, 'Cash to approve', true),
                $this->kpi('Approved This Month', $approvedMonth, date('F')),
                $this->kpi('Settled This Month', $monthAmt, 'Total settlements', true),
            ],
            'branch' => $this->block('Branch Wise Pending Settlements', $this->byBranch(clone $pendingQ, 'cs.branch_id', 'cs.received_amount'), true),
            'donut' => $this->block('Settlement Status', $hasApproval ? [
                ['label' => 'Approved', 'value' => (clone $base)->where('cs.is_approved', 1)->count()],
                ['label' => 'Pending', 'value' => $pending],
            ] : []),
            'trend' => $this->block('Settlements (Last 14 Days)', $this->dayTrend(clone $base, 'cs.' . $dateCol, 'cs.received_amount'), true),
            'table' => $this->table('Pending Settlement Approvals', [
                ['key' => 'date', 'label' => 'Date'],
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'employee', 'label' => 'Employee'],
                ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
                ['key' => 'remarks', 'label' => 'Remarks'],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ], $rows, 'settlement-report'),
        ];
    }

    /** Company-wide summary for the corporate admin panel. */
    private function corporate()
    {
        $amt = $this->rAmt();
        $month = (clone $this->receipts())->where('r.receipt_date', '>=', $this->monthStart());
        $collection = (float) (clone $month)->sum(DB::raw($amt));
        $year = (float) (clone $this->receipts())->where('r.receipt_date', '>=', date('Y-04-01', strtotime(date('n') >= 4 ? 'now' : '-1 year')))->sum(DB::raw($amt));
        $active = $this->scoped('chit_details', 'cd')->where('cd.status', 1)->leftJoin('schemes as s', 's.id', '=', 'cd.scheme_id');
        $business = (float) (clone $active)->sum('s.chit_value');
        $paid = (float) $this->scoped('payments', 'p')->where('p.payment_date', '>=', $this->monthStart())->sum('p.total_amount');
        $expense = Schema::hasTable('petty_cash_expenses')
            ? (float) $this->scoped('petty_cash_expenses', 'pe')->where('pe.status', '!=', 3)->where('pe.expenses_date', '>=', $this->monthStart())->sum('pe.expenses_amount') : 0;

        $branchColl = (clone $month)->groupBy('r.branch_id')->select('r.branch_id', DB::raw("SUM($amt) as v"))->pluck('v', 'branch_id');
        $branchBiz = (clone $active)->groupBy('cd.branch_id')->select('cd.branch_id', DB::raw('SUM(s.chit_value) as v'), DB::raw('COUNT(*) as cnt'))->get()->keyBy('branch_id');
        $branchCust = $this->scoped('customers', 'c')->groupBy('c.branch_id')->select('c.branch_id', DB::raw('COUNT(*) as v'))->pluck('v', 'branch_id');
        $branchEmp = $this->scoped('employees', 'e')->where('e.status', 1)->groupBy('e.branch_id')->select('e.branch_id', DB::raw('COUNT(*) as v'))->pluck('v', 'branch_id');
        $rows = [];
        foreach ($this->branchList() as $b) {
            $bz = $branchBiz[$b->id] ?? null;
            $rows[] = [
                'branch' => $b->branch_name,
                'customers' => (int) ($branchCust[$b->id] ?? 0),
                'enrolments' => $bz ? (int) $bz->cnt : 0,
                'business' => $bz ? (float) $bz->v : 0,
                'collection' => (float) ($branchColl[$b->id] ?? 0),
                'employees' => (int) ($branchEmp[$b->id] ?? 0),
            ];
        }
        usort($rows, function ($a, $b) {
            return $b['collection'] <=> $a['collection'];
        });

        return [
            'kpis' => [
                $this->kpi('Running Business', $business, 'Active chit value', true),
                $this->kpi('Month Collection', $collection, date('F'), true),
                $this->kpi('Financial Year Collection', $year, 'Since 1 April', true),
                $this->kpi('Payments This Month', $paid, 'Released', true),
                $this->kpi('Expenses This Month', $expense, 'Petty cash', true),
                $this->kpi('Active Enrolments', (clone $active)->count(), count($rows) . ' branches'),
            ],
            'branch' => $this->block('Branch Wise Business (Active Chit Value)', $this->byBranch(clone $active, 'cd.branch_id', 's.chit_value'), true),
            'donut' => $this->block('Month Collection Share by Branch', array_map(function ($r) {
                return ['label' => $r['branch'], 'value' => $r['collection']];
            }, array_slice($rows, 0, 8)), true),
            'trend' => $this->block('Collection Trend (12 Months)', $this->monthTrend(clone $this->receipts(), 'r.receipt_date', $amt, 12), true),
            'table' => $this->table('Branch Performance Summary', [
                ['key' => 'branch', 'label' => 'Branch'],
                ['key' => 'customers', 'label' => 'Customers'],
                ['key' => 'enrolments', 'label' => 'Active Enrolments'],
                ['key' => 'business', 'label' => 'Running Business', 'type' => 'money'],
                ['key' => 'collection', 'label' => 'Month Collection', 'type' => 'money'],
                ['key' => 'employees', 'label' => 'Employees'],
            ], $rows, 'branch-overview'),
        ];
    }

    private function emptySection($name)
    {
        return [
            'kpis' => [$this->kpi('Total ' . $name, 0, 'No records yet')],
            'branch' => $this->block('Branch Wise ' . $name, []),
            'donut' => $this->block($name . ' Status', []),
            'trend' => $this->block($name . ' Trend', []),
            'table' => $this->table('Recent ' . $name, [], []),
        ];
    }

    private function shortMoney($v)
    {
        $v = (float) $v;
        if ($v >= 10000000) {
            return round($v / 10000000, 2) . 'Cr';
        }
        if ($v >= 100000) {
            return round($v / 100000, 2) . 'L';
        }
        if ($v >= 1000) {
            return round($v / 1000, 1) . 'K';
        }
        return (string) $v;
    }
}
