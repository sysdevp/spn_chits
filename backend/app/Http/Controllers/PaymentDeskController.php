<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaymentDeskController extends Controller
{
    private function switchTenantDb(Request $request)
    {
        $db = $request->db ?: $request->database_name;
        if ($db) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $db);
            DB::reconnect('mysql');
        }
        $this->ensureTable();
    }

    private function ensureTable()
    {
        if (!Schema::hasTable('payment_desk_requests')) {
            Schema::create('payment_desk_requests', function ($table) {
                $table->bigIncrements('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('customer_id')->default(0);
                $table->integer('enrollment_id')->default(0);
                $table->string('kind', 20);
                $table->string('customer_name')->nullable();
                $table->string('mobile')->nullable();
                $table->string('ar_no')->nullable();
                $table->string('scheme_name')->nullable();
                $table->decimal('chit_value', 14, 2)->default(0);
                $table->string('months')->nullable();
                $table->string('start_date')->nullable();
                $table->string('end_date')->nullable();
                $table->string('prized_month')->nullable();
                $table->string('closed_month')->nullable();
                $table->string('year_label')->nullable();
                $table->integer('payment_month')->nullable();
                $table->decimal('amount', 14, 2)->default(0);
                $table->decimal('document_charges', 14, 2)->default(0);
                $table->decimal('govt_fees', 14, 2)->default(0);
                $table->decimal('deduction', 14, 2)->default(0);
                $table->decimal('net_amount', 14, 2)->default(0);
                $table->string('payment_mode')->nullable();
                $table->string('payment_date')->nullable();
                $table->string('transaction_no')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('reason')->nullable();
                $table->text('remarks')->nullable();
                $table->text('branch_split')->nullable();
                $table->text('documents')->nullable();
                $table->text('photos')->nullable();
                $table->string('home_lat')->nullable();
                $table->string('home_lng')->nullable();
                $table->string('collection_lat')->nullable();
                $table->string('collection_lng')->nullable();
                $table->string('field_status', 20)->default('pending');
                $table->string('status', 30)->default('draft');
                $table->text('approval_remarks')->nullable();
                $table->integer('created_by')->nullable();
                $table->integer('approved_by')->nullable();
                $table->timestamps();
            });
            return;
        }
        if (!Schema::hasColumn('payment_desk_requests', 'payment_month')) {
            Schema::table('payment_desk_requests', function ($table) {
                $table->integer('payment_month')->nullable()->after('year_label');
            });
        }
        if (!Schema::hasColumn('payment_desk_requests', 'document_charges')) {
            Schema::table('payment_desk_requests', function ($table) {
                $table->decimal('document_charges', 14, 2)->default(0)->after('amount');
            });
        }
        if (!Schema::hasColumn('payment_desk_requests', 'govt_fees')) {
            Schema::table('payment_desk_requests', function ($table) {
                $table->decimal('govt_fees', 14, 2)->default(0)->after('document_charges');
            });
        }
        if (!Schema::hasColumn('payment_desk_requests', 'chit_admin_by')) {
            Schema::table('payment_desk_requests', function ($table) {
                $table->integer('chit_admin_by')->nullable();
                $table->text('chit_admin_remarks')->nullable();
                $table->dateTime('chit_admin_at')->nullable();
            });
        }
        if (!Schema::hasColumn('payment_desk_requests', 'field_employee_id')) {
            Schema::table('payment_desk_requests', function ($table) {
                $table->string('source', 20)->default('web');
                $table->integer('field_employee_id')->nullable();
                $table->dateTime('field_assigned_at')->nullable();
                $table->text('field_remarks')->nullable();
                $table->dateTime('field_verified_at')->nullable();
                $table->integer('accounts_by')->nullable();
                $table->text('accounts_remarks')->nullable();
                $table->dateTime('accounts_at')->nullable();
            });
        }
    }

    /** Field verification happens before payout for prized / refund, and after payout for final closed. */
    private function fieldAfterPayout($kind)
    {
        return $kind === 'closed';
    }

    private function employeeNames(array $ids)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids || !Schema::hasTable('employees')) {
            return [];
        }
        return DB::table('employees')->whereIn('id', $ids)->get(['id', 'first_name', 'last_name'])
            ->mapWithKeys(function ($e) {
                return [$e->id => trim(($e->first_name ?? '') . ' ' . ($e->last_name ?? ''))];
            })->all();
    }

    /** Customer app requests may send only the enrollment; fill the rest from the chit. */
    private function enrollmentDefaults($enrollmentId)
    {
        $chit = DB::table('chit_details')->where('id', $enrollmentId)->first();
        if (!$chit) {
            return null;
        }
        $customer = DB::table('customers')->where('id', $chit->customer_id)->first();
        $scheme = !empty($chit->scheme_id) ? DB::table('schemes')->where('id', $chit->scheme_id)->first() : null;
        return [
            'tenant_id' => (int) ($chit->tenant_id ?? 0),
            'branch_id' => (int) ($chit->branch_id ?? 0),
            'customer_id' => (int) ($chit->customer_id ?? 0),
            'customer_name' => $customer ? trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) : '',
            'mobile' => $customer->mobile_no ?? '',
            'ar_no' => $chit->ar_no ?? '',
            'scheme_name' => $scheme ? ('Rs.' . number_format((float) $scheme->chit_value) . ' (' . $scheme->no_of_months . ' M)') : '',
            'chit_value' => $scheme->chit_value ?? 0,
            'months' => $scheme->no_of_months ?? '',
        ];
    }

    public function store_request(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $kindMap = ['prized' => 'prized', 'prize' => 'prized', 'closed' => 'closed', 'final' => 'closed', 'close' => 'closed', 'refund' => 'refund'];
            $kind = $kindMap[strtolower((string) $request->kind)] ?? '';
            if ($kind === '' || empty($request->enrollment_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer enrollment and payment type are required'], 200);
            }
            $defaults = $this->enrollmentDefaults($request->enrollment_id);
            if (!$defaults) {
                return response()->json(['status' => 'Error', 'msg' => 'Chit enrollment not found'], 200);
            }
            $source = $request->source === 'customer_app' || $request->is('*customer-payment-request')
                ? 'customer_app' : 'web';
            if (in_array($kind, ['prized', 'closed'], true)) {
                $exists = DB::table('payment_desk_requests')
                    ->where('enrollment_id', $request->enrollment_id)
                    ->where('kind', $kind)
                    ->whereNotIn('status', ['rejected'])
                    ->exists();
                if ($exists) {
                    return response()->json(['status' => 'Error', 'msg' => $kind === 'prized'
                        ? 'This customer is already prized on this chit. A new prized profile cannot be created.'
                        : 'A final closed payment already exists for this chit.'], 200);
                }
            }
            $submit = $request->action === 'submit' || $source === 'customer_app';
            $docCharges = (float) ($request->document_charges ?: 0);
            $govtFees = (float) ($request->govt_fees ?: 0);
            $amount = (float) ($request->amount ?: 0);
            $deduction = (float) ($request->deduction ?: ($docCharges + $govtFees));
            $net = (float) ($request->net_amount ?: max(0, $amount - $deduction));
            $id = DB::table('payment_desk_requests')->insertGetId([
                'tenant_id' => (int) ($request->tenant_id ?: $defaults['tenant_id']),
                'branch_id' => (int) ($request->branch_id ?: $defaults['branch_id']),
                'customer_id' => (int) ($request->customer_id ?: $defaults['customer_id']),
                'enrollment_id' => (int) $request->enrollment_id,
                'kind' => $kind,
                'source' => $source,
                'customer_name' => $request->customer_name ?: $defaults['customer_name'],
                'mobile' => $request->mobile ?: $defaults['mobile'],
                'ar_no' => $request->ar_no ?: $defaults['ar_no'],
                'scheme_name' => $request->scheme_name ?: $defaults['scheme_name'],
                'chit_value' => $request->chit_value ?: $defaults['chit_value'],
                'months' => $request->months ?: $defaults['months'],
                'start_date' => $request->start_date ?: '',
                'end_date' => $request->end_date ?: '',
                'prized_month' => $request->prized_month ?: '',
                'closed_month' => $request->closed_month ?: '',
                'year_label' => $request->year_label ?: '',
                'payment_month' => $request->payment_month ?: null,
                'amount' => $amount,
                'document_charges' => $docCharges,
                'govt_fees' => $govtFees,
                'deduction' => $deduction,
                'net_amount' => $net,
                'payment_mode' => '',
                'payment_date' => '',
                'transaction_no' => '',
                'bank_name' => '',
                'reason' => $request->reason ?: '',
                'remarks' => $request->remarks ?: '',
                'branch_split' => json_encode([]),
                'documents' => is_string($request->documents) ? $request->documents : json_encode($request->documents ?: []),
                'field_status' => 'pending',
                'status' => $submit ? 'pending_chit_admin' : 'draft',
                'created_by' => $request->created_by ?: 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json([
                'status' => 'Success',
                'msg' => $submit
                    ? 'Submitted for Corporate Chit Admin approval.'
                    : 'Draft saved',
                'id' => $id,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function list_requests(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $q = DB::table('payment_desk_requests')->orderBy('id', 'desc');
            if (!empty($request->tenant_id)) {
                $q->whereIn('tenant_id', [(int) $request->tenant_id, 0]);
            }
            if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
                $q->where('branch_id', $request->branch_id);
            }
            if (!empty($request->kind) && $request->kind !== 'all') {
                $q->where('kind', $request->kind);
            }
            if ($request->status === 'awaiting') {
                $q->whereIn('status', ['pending_chit_admin', 'pending_field', 'pending_accounts', 'pending_admin']);
            } elseif ($request->status === 'pending_accounts') {
                $q->whereIn('status', ['pending_accounts', 'pending_admin']);
            } elseif (!empty($request->status) && $request->status !== 'all') {
                $q->where('status', $request->status);
            }
            if (!empty($request->source) && $request->source !== 'all') {
                $q->where('source', $request->source);
            }
            if (!empty($request->field_only)) {
                $q->where('status', 'pending_field');
            }
            if (!empty($request->field_employee_id)) {
                $q->where('field_employee_id', (int) $request->field_employee_id);
            }
            $list = $q->limit(400)->get();
            $names = $this->employeeNames(array_merge(
                $list->pluck('created_by')->all(),
                $list->pluck('chit_admin_by')->all(),
                $list->pluck('field_employee_id')->all(),
                $list->pluck('accounts_by')->all(),
                $list->pluck('approved_by')->all()
            ));
            $branchNames = Schema::hasTable('branches')
                ? DB::table('branches')->whereIn('id', $list->pluck('branch_id')->unique()->all())->pluck('branch_name', 'id')->all()
                : [];
            $rows = $list->map(function ($row) use ($names, $branchNames) {
                $item = (array) $row;
                $item['photo_urls'] = $this->fileUrls($row->photos);
                $item['document_urls'] = $this->fileUrls($row->documents);
                $item['branch_name'] = $branchNames[$row->branch_id] ?? '';
                $item['requested_by_name'] = ($row->source ?? '') === 'customer_app'
                    ? 'Customer (App)'
                    : ($names[$row->created_by] ?? '');
                $item['chit_admin_name'] = $names[$row->chit_admin_by] ?? '';
                $item['field_employee_name'] = $names[$row->field_employee_id] ?? '';
                $item['accounts_name'] = $names[$row->accounts_by ?: $row->approved_by] ?? '';
                return $item;
            });
            $base = DB::table('payment_desk_requests');
            if (!empty($request->tenant_id)) {
                $base->whereIn('tenant_id', [(int) $request->tenant_id, 0]);
            }
            if (!empty($request->kind) && $request->kind !== 'all') {
                $base->where('kind', $request->kind);
            }
            $all = $base->get();
            return response()->json([
                'status' => 'Success',
                'data' => $rows,
                'prized_enrollment_ids' => DB::table('payment_desk_requests')
                    ->where('kind', 'prized')->where('status', '!=', 'rejected')
                    ->pluck('enrollment_id')->unique()->values(),
                'summary' => [
                    'total' => $all->count(),
                    'pending_field' => $all->where('status', 'pending_field')->count(),
                    'pending_chit_admin' => $all->where('status', 'pending_chit_admin')->count(),
                    'pending_accounts' => $all->whereIn('status', ['pending_accounts', 'pending_admin'])->count(),
                    'pending_admin' => $all->where('status', 'pending_admin')->count(),
                    'customer_app' => $all->where('source', 'customer_app')->count(),
                    'approved' => $all->where('status', 'approved')->count(),
                    'rejected' => $all->where('status', 'rejected')->count(),
                    'draft' => $all->where('status', 'draft')->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'data' => []], 200);
        }
    }

    public function field_submit(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('payment_desk_requests')->where('id', $request->id)->first();
            if (!$row || $row->status !== 'pending_field') {
                return response()->json(['status' => 'Error', 'msg' => 'This request is not waiting for field verification'], 200);
            }
            $employeeId = (int) ($request->employee_id ?: 0);
            if ($employeeId && !empty($row->field_employee_id) && (int) $row->field_employee_id !== $employeeId) {
                return response()->json(['status' => 'Error', 'msg' => 'This verification is assigned to another employee'], 200);
            }
            $photos = $request->photos;
            if (is_string($photos)) {
                $decoded = json_decode($photos, true);
                $photos = is_array($decoded) ? $decoded : array_filter(explode(',', $photos));
            }
            if (!is_array($photos) || count($photos) < 1) {
                return response()->json(['status' => 'Error', 'msg' => 'Take at least one camera photo'], 200);
            }
            if (empty($request->home_lat) || empty($request->home_lng) || empty($request->collection_lat) || empty($request->collection_lng)) {
                return response()->json(['status' => 'Error', 'msg' => 'Home pin and collection pin are required'], 200);
            }
            DB::table('payment_desk_requests')->where('id', $row->id)->update([
                'photos' => json_encode(array_values($photos)),
                'home_lat' => $request->home_lat,
                'home_lng' => $request->home_lng,
                'collection_lat' => $request->collection_lat,
                'collection_lng' => $request->collection_lng,
                'field_status' => 'submitted',
                'field_remarks' => $request->remarks ?: '',
                'field_verified_at' => date('Y-m-d H:i:s'),
                'status' => $this->fieldAfterPayout($row->kind) ? 'approved' : 'pending_accounts',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json([
                'status' => 'Success',
                'msg' => $this->fieldAfterPayout($row->kind)
                    ? 'Field verification completed. Payment closed.'
                    : 'Field verification sent to Accounts for approval and payout',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    /** Employee mobile app: only the requests assigned to this employee for field verification. */
    public function mobile_field_tasks(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->employee_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'employee_id is required', 'data' => []], 200);
            }
            $rows = DB::table('payment_desk_requests')
                ->where('status', 'pending_field')
                ->where('field_employee_id', (int) $request->employee_id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($row) {
                    $item = (array) $row;
                    $item['document_urls'] = $this->fileUrls($row->documents);
                    $item['kind_label'] = ['prized' => 'Prized Payment', 'closed' => 'Final Closed Payment', 'refund' => 'Refund Payment'][$row->kind] ?? $row->kind;
                    $item['stage_label'] = $this->fieldAfterPayout($row->kind) ? 'Post-payout verification' : 'Pre-payout verification';
                    return $item;
                });
            return response()->json(['status' => 'Success', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'data' => []], 200);
        }
    }

    /**
     * One endpoint for every approval stage, decided by the request's current status:
     * pending_chit_admin -> corporate chit admin (assigns the field employee),
     * pending_accounts / legacy pending_admin -> accounts approval and payout.
     */
    public function admin_decide(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('payment_desk_requests')->where('id', $request->id)->first();
            if (!$row) {
                return response()->json(['status' => 'Error', 'msg' => 'Request not found'], 200);
            }
            $reject = $request->decision === 'reject';
            $by = (int) ($request->approved_by ?: 0);
            $now = date('Y-m-d H:i:s');
            if ($reject && trim((string) $request->remarks) === '') {
                return response()->json(['status' => 'Error', 'msg' => 'Enter remarks to reject'], 200);
            }

            if ($row->status === 'pending_chit_admin') {
                if ($reject) {
                    DB::table('payment_desk_requests')->where('id', $row->id)->update([
                        'status' => 'rejected', 'chit_admin_by' => $by, 'chit_admin_at' => $now,
                        'chit_admin_remarks' => $request->remarks, 'approval_remarks' => $request->remarks, 'updated_at' => $now,
                    ]);
                    return response()->json(['status' => 'Success', 'msg' => 'Rejected by Corporate Chit Admin'], 200);
                }
                if (empty($request->field_employee_id)) {
                    return response()->json(['status' => 'Error', 'msg' => 'Select the field verification employee'], 200);
                }
                if ($row->kind === 'prized' && Schema::hasColumn('chit_details', 'prized_status')) {
                    DB::table('chit_details')->where('id', $row->enrollment_id)->update(['prized_status' => 1]);
                }
                $fieldDone = $row->field_status === 'submitted';
                $next = ($this->fieldAfterPayout($row->kind) || $fieldDone) ? 'pending_accounts' : 'pending_field';
                DB::table('payment_desk_requests')->where('id', $row->id)->update([
                    'status' => $next,
                    'chit_admin_by' => $by,
                    'chit_admin_at' => $now,
                    'chit_admin_remarks' => $request->remarks ?: '',
                    'field_employee_id' => (int) $request->field_employee_id,
                    'field_assigned_at' => $now,
                    'updated_at' => $now,
                ]);
                return response()->json(['status' => 'Success', 'msg' => $next === 'pending_field'
                    ? 'Approved. Sent to the selected employee for field verification.'
                    : 'Approved. Sent to Accounts for payout.'], 200);
            }

            if (in_array($row->status, ['pending_accounts', 'pending_admin'], true)) {
                if ($reject) {
                    DB::table('payment_desk_requests')->where('id', $row->id)->update([
                        'status' => 'rejected', 'accounts_by' => $by, 'accounts_at' => $now,
                        'accounts_remarks' => $request->remarks, 'approval_remarks' => $request->remarks, 'updated_at' => $now,
                    ]);
                    if ($row->kind === 'prized' && Schema::hasColumn('chit_details', 'prized_status')
                        && !DB::table('payments')->where('enrollment_id', $row->enrollment_id)->where('payment_for', 7)->where('status', 1)->exists()) {
                        DB::table('chit_details')->where('id', $row->enrollment_id)->update(['prized_status' => 0]);
                    }
                    return response()->json(['status' => 'Success', 'msg' => 'Rejected by Accounts'], 200);
                }
                $mode = trim((string) $request->payment_mode);
                if ($mode === '') {
                    return response()->json(['status' => 'Error', 'msg' => 'Select the payout mode'], 200);
                }
                if (strtolower($mode) !== 'cash' && trim((string) $request->transaction_no) === '') {
                    return response()->json(['status' => 'Error', 'msg' => 'Enter the UTR / transaction number'], 200);
                }
                $next = ($this->fieldAfterPayout($row->kind) && !empty($row->field_employee_id) && $row->field_status !== 'submitted')
                    ? 'pending_field' : 'approved';
                DB::table('payment_desk_requests')->where('id', $row->id)->update([
                    'status' => $next,
                    'payment_mode' => $mode,
                    'payment_date' => $request->payment_date ?: date('Y-m-d'),
                    'transaction_no' => $request->transaction_no ?: '',
                    'bank_name' => $request->bank_name ?: '',
                    'branch_split' => is_string($request->branch_split) ? $request->branch_split : json_encode($request->branch_split ?: []),
                    'accounts_by' => $by,
                    'accounts_at' => $now,
                    'accounts_remarks' => $request->remarks ?: '',
                    'approved_by' => $by,
                    'approval_remarks' => $request->remarks ?: '',
                    'updated_at' => $now,
                ]);
                SpnAccountsReportController::queuePayout(
                    $row->tenant_id, $row->branch_id, $row->id, $row->net_amount, $mode,
                    ucfirst($row->kind) . ' payout · ' . ($row->customer_name ?: ''), $by, $request->payment_date ?: date('Y-m-d')
                );
                return response()->json(['status' => 'Success', 'msg' => $next === 'pending_field'
                    ? 'Payout done. Sent for post-payout field verification.'
                    : 'Approved and paid out'], 200);
            }

            $stage = [
                'pending_field' => 'This request is waiting for field verification from the mobile app',
                'approved' => 'This request is already paid',
                'rejected' => 'This request was rejected',
                'draft' => 'This request is still a draft',
            ][$row->status] ?? 'This request cannot be approved now';
            return response()->json(['status' => 'Error', 'msg' => $stage], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    /** Submit a saved draft for Corporate Chit Admin approval. */
    public function submit_draft(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $updated = DB::table('payment_desk_requests')->where('id', $request->id)->where('status', 'draft')
                ->update(['status' => 'pending_chit_admin', 'updated_at' => date('Y-m-d H:i:s')]);
            return response()->json($updated
                ? ['status' => 'Success', 'msg' => 'Submitted for Corporate Chit Admin approval']
                : ['status' => 'Error', 'msg' => 'Only drafts can be submitted'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function upload_file(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['status' => 'Error', 'msg' => 'No file'], 200);
            }
            $file = $request->file('file');
            $name = 'pay_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/payment_desk', $name);
            $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
            return response()->json([
                'status' => 'Success',
                'file_name' => $name,
                'file_url' => $base . '/storage/app/uploads/payment_desk/' . $name,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function fileUrls($raw)
    {
        $names = json_decode((string) $raw, true);
        if (!is_array($names)) {
            return [];
        }
        $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
        $out = [];
        foreach ($names as $name) {
            if (!$name) {
                continue;
            }
            $out[] = ['name' => $name, 'url' => $base . '/storage/app/uploads/payment_desk/' . $name];
        }
        return $out;
    }
}
