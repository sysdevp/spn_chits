<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OfficeTicketController extends Controller
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
        if (!Schema::hasTable('office_tickets')) {
            Schema::create('office_tickets', function ($table) {
                $table->bigIncrements('id');
                $table->string('ticket_no', 40);
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('employee_name')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('kind', 20)->default('office_wish');
                $table->string('category', 80)->nullable();
                $table->string('subject')->nullable();
                $table->text('description')->nullable();
                $table->string('priority', 20)->default('medium');
                $table->date('from_date')->nullable();
                $table->date('to_date')->nullable();
                $table->string('status', 20)->default('open');
                $table->string('attachment')->nullable();
                $table->integer('rating')->nullable();
                $table->text('feedback')->nullable();
                $table->text('decision_remarks')->nullable();
                $table->integer('updated_by')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('office_ticket_messages')) {
            Schema::create('office_ticket_messages', function ($table) {
                $table->bigIncrements('id');
                $table->integer('ticket_id');
                $table->string('sender_name')->nullable();
                $table->string('sender_role', 40)->nullable();
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function store_ticket(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->tenant_id) || empty($request->subject) || empty($request->description)) {
                return response()->json(['status' => 'Error', 'msg' => 'Subject and description are required'], 200);
            }
            $employeeId = (int) ($request->employee_id ?: $request->user_id ?: 0);
            $kind = $request->kind === 'office_wish' ? 'office_wish' : 'complaint';
            $id = DB::table('office_tickets')->insertGetId([
                'ticket_no' => 'TMP',
                'tenant_id' => (int) $request->tenant_id,
                'branch_id' => (int) ($request->branch_id ?: 0),
                'employee_id' => $employeeId,
                'employee_name' => $request->employee_name ?: '',
                'branch_name' => $request->branch_name ?: '',
                'kind' => $kind,
                'category' => $request->category ?: 'Other Request',
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => in_array($request->priority, ['low', 'medium', 'high'], true) ? $request->priority : 'medium',
                'from_date' => $request->from_date ?: null,
                'to_date' => $request->to_date ?: null,
                'status' => 'open',
                'attachment' => $request->attachment ?: '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $prefix = $kind === 'complaint' ? 'SPN-COM' : 'SPN-OW';
            $ticketNo = $prefix . '-' . date('Y') . '-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
            DB::table('office_tickets')->where('id', $id)->update(['ticket_no' => $ticketNo]);
            $this->addTimeline($id, $request->employee_name ?: 'Employee', 'Employee', 'Ticket raised by employee.');
            $row = DB::table('office_tickets')->where('id', $id)->first();
            return response()->json(['status' => 'Success', 'msg' => 'Your request has been submitted', 'data' => $row], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function list_tickets(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $q = DB::table('office_tickets')->orderBy('id', 'desc');
            if (!empty($request->tenant_id)) {
                $q->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
                $q->where('branch_id', $request->branch_id);
            }
            if (!empty($request->mine) && (int) $request->employee_id > 0) {
                $q->where('employee_id', (int) $request->employee_id);
            }
            if (!empty($request->kind) && $request->kind !== 'all') {
                $q->where('kind', $request->kind);
            }
            if (!empty($request->status) && $request->status !== 'all') {
                $q->where('status', $request->status);
            }
            if (!empty($request->search)) {
                $term = '%' . $request->search . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('ticket_no', 'like', $term)
                        ->orWhere('subject', 'like', $term)
                        ->orWhere('employee_name', 'like', $term);
                });
            }
            $rows = $q->limit(500)->get();
            $summary = [
                'total' => $rows->count(),
                'open' => $rows->where('status', 'open')->count(),
                'in_progress' => $rows->where('status', 'in_progress')->count(),
                'approved' => $rows->where('status', 'approved')->count(),
                'resolved' => $rows->whereIn('status', ['resolved', 'closed'])->count(),
            ];
            return response()->json(['status' => 'Success', 'data' => $rows, 'summary' => $summary], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage(), 'data' => []], 200);
        }
    }

    public function get_ticket(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('office_tickets')->where('id', $request->id)->first();
            if (!$row && !empty($request->ticket_no)) {
                $row = DB::table('office_tickets')->where('ticket_no', $request->ticket_no)->first();
            }
            if (!$row) {
                return response()->json(['status' => 'Error', 'msg' => 'Ticket not found'], 200);
            }
            $messages = DB::table('office_ticket_messages')->where('ticket_id', $row->id)->orderBy('id')->get();
            return response()->json(['status' => 'Success', 'data' => $row, 'messages' => $messages], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function add_message(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            if (empty($request->id) || empty($request->message)) {
                return response()->json(['status' => 'Error', 'msg' => 'Message is required'], 200);
            }
            $this->addTimeline(
                (int) $request->id,
                $request->sender_name ?: 'User',
                $request->sender_role ?: 'Employee',
                $request->message
            );
            return response()->json(['status' => 'Success', 'msg' => 'Message sent'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function update_status(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('office_tickets')->where('id', $request->id)->first();
            if (!$row) {
                return response()->json(['status' => 'Error', 'msg' => 'Ticket not found'], 200);
            }
            $allowed = ['open', 'in_progress', 'approved', 'resolved', 'closed'];
            $status = in_array($request->status, $allowed, true) ? $request->status : $row->status;
            DB::table('office_tickets')->where('id', $row->id)->update([
                'status' => $status,
                'decision_remarks' => $request->remarks ?: $row->decision_remarks,
                'updated_by' => $request->updated_by ?: 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $label = str_replace('_', ' ', $status);
            $this->addTimeline(
                (int) $row->id,
                $request->sender_name ?: 'HR Team',
                $request->sender_role ?: 'Admin',
                'Status updated to ' . $label . ($request->remarks ? '. ' . $request->remarks : '')
            );
            return response()->json(['status' => 'Success', 'msg' => 'Ticket updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function submit_feedback(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $row = DB::table('office_tickets')->where('id', $request->id)->first();
            if (!$row) {
                return response()->json(['status' => 'Error', 'msg' => 'Ticket not found'], 200);
            }
            DB::table('office_tickets')->where('id', $row->id)->update([
                'rating' => (int) $request->rating,
                'feedback' => $request->feedback ?: '',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Thank you for the feedback'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function ticket_report(Request $request)
    {
        try {
            $this->switchTenantDb($request);
            $q = DB::table('office_tickets');
            if (!empty($request->tenant_id)) {
                $q->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
                $q->where('branch_id', $request->branch_id);
            }
            $rows = $q->get();
            $byType = [];
            $byBranch = [];
            foreach ($rows as $row) {
                $type = $row->category ?: $row->kind;
                if (!isset($byType[$type])) {
                    $byType[$type] = 0;
                }
                $byType[$type]++;
                $branch = $row->branch_name ?: 'Branch';
                if (!isset($byBranch[$branch])) {
                    $byBranch[$branch] = 0;
                }
                $byBranch[$branch]++;
            }
            $typeRows = [];
            foreach ($byType as $name => $count) {
                $typeRows[] = ['name' => $name, 'count' => $count];
            }
            $branchRows = [];
            foreach ($byBranch as $name => $count) {
                $branchRows[] = ['name' => $name, 'count' => $count];
            }
            return response()->json([
                'status' => 'Success',
                'summary' => [
                    'total' => $rows->count(),
                    'open' => $rows->where('status', 'open')->count(),
                    'in_progress' => $rows->where('status', 'in_progress')->count(),
                    'approved' => $rows->where('status', 'approved')->count(),
                    'resolved' => $rows->whereIn('status', ['resolved', 'closed'])->count(),
                ],
                'by_type' => $typeRows,
                'by_branch' => $branchRows,
                'by_status' => [
                    ['name' => 'Open', 'count' => $rows->where('status', 'open')->count()],
                    ['name' => 'In Progress', 'count' => $rows->where('status', 'in_progress')->count()],
                    ['name' => 'Approved', 'count' => $rows->where('status', 'approved')->count()],
                    ['name' => 'Resolved', 'count' => $rows->whereIn('status', ['resolved', 'closed'])->count()],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function upload_file(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['status' => 'Error', 'msg' => 'No file uploaded'], 200);
            }
            $file = $request->file('file');
            $name = 'ticket_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/office_tickets', $name);
            $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
            return response()->json([
                'status' => 'Success',
                'file_name' => $name,
                'file_url' => $base . '/storage/app/uploads/office_tickets/' . $name,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function addTimeline($ticketId, $name, $role, $message)
    {
        DB::table('office_ticket_messages')->insert([
            'ticket_id' => $ticketId,
            'sender_name' => $name,
            'sender_role' => $role,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
