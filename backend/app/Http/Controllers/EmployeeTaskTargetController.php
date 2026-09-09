<?php
namespace App\Http\Controllers;

use App\Models\EmployeeTask;
use App\Models\EmployeeTarget;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Validator;

class EmployeeTaskTargetController extends Controller
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
        if (!Schema::hasTable('employee_tasks')) {
            Schema::create('employee_tasks', function ($table) {
                $table->increments('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->date('task_date');
                $table->date('due_date')->nullable();
                $table->string('priority', 20)->default('medium');
                $table->string('task_status', 20)->default('pending');
                $table->integer('assigned_by')->default(0);
                $table->dateTime('completed_at')->nullable();
                $table->text('employee_remark')->nullable();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('employee_targets')) {
            Schema::create('employee_targets', function ($table) {
                $table->increments('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('target_type', 50)->default('existing_client_collection');
                $table->string('period_type', 20);
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('target_amount', 15, 2)->default(0);
                $table->decimal('achieved_amount', 15, 2)->default(0);
                $table->string('target_label', 100)->nullable()->default('Collection');
                $table->text('notes')->nullable();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
            });
        } elseif (!Schema::hasColumn('employee_targets', 'target_type')) {
            Schema::table('employee_targets', function ($table) {
                $table->string('target_type', 50)->default('existing_client_collection')->after('employee_id');
            });
        }
    }

    private function empName($row)
    {
        $emp = $row->employee_det;
        if (!$emp) {
            return '';
        }
        return trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : ''));
    }

    private function formatTask($row)
    {
        $branch = $row->branch_det;
        return array(
            'id' => (int) $row->id,
            'tenant_id' => (int) $row->tenant_id,
            'branch_id' => (int) $row->branch_id,
            'employee_id' => (int) $row->employee_id,
            'employee_name' => $this->empName($row),
            'branch_name' => $branch && isset($branch->branch_name) ? $branch->branch_name : '',
            'title' => $row->title,
            'description' => $row->description,
            'task_date' => $row->task_date,
            'due_date' => $row->due_date,
            'priority' => $row->priority,
            'task_status' => $row->task_status,
            'assigned_by' => (int) $row->assigned_by,
            'completed_at' => $row->completed_at,
            'employee_remark' => $row->employee_remark,
            'created_at' => $row->created_at ? (string) $row->created_at : '',
        );
    }

    private function formatTarget($row)
    {
        $branch = $row->branch_det;
        $amount = (float) $row->target_amount;
        $achieved = (float) $row->achieved_amount;
        $pct = $amount > 0 ? min(100, round(($achieved / $amount) * 100)) : 0;
        return array(
            'id' => (int) $row->id,
            'tenant_id' => (int) $row->tenant_id,
            'branch_id' => (int) $row->branch_id,
            'employee_id' => (int) $row->employee_id,
            'employee_name' => $this->empName($row),
            'branch_name' => $branch && isset($branch->branch_name) ? $branch->branch_name : '',
            'period_type' => $row->period_type,
            'period_start' => $row->period_start,
            'period_end' => $row->period_end,
            'target_amount' => $amount,
            'achieved_amount' => $achieved,
            'completion_percent' => $pct,
            'target_label' => $row->target_label ?: 'Collection',
            'target_type' => isset($row->target_type) ? $row->target_type : 'collection',
            'notes' => $row->notes,
            'created_at' => $row->created_at ? (string) $row->created_at : '',
        );
    }

    private function resolvePeriodDates($periodType, $anchorDate)
    {
        $d = Carbon::parse($anchorDate)->startOfDay();
        if ($periodType === 'weekly') {
            return array($d->copy()->startOfWeek()->toDateString(), $d->copy()->endOfWeek()->toDateString());
        }
        if ($periodType === 'monthly') {
            return array($d->copy()->startOfMonth()->toDateString(), $d->copy()->endOfMonth()->toDateString());
        }
        return array($d->toDateString(), $d->toDateString());
    }

    public function store_employee_task(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'tenant_id' => 'required',
            'employee_id' => 'required',
            'title' => 'required|max:255',
            'task_date' => 'required|date',
        ));
        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $tasks = $request->tasks;
            $created = array();

            if (is_array($tasks) && count($tasks) > 0) {
                foreach ($tasks as $item) {
                    if (empty($item['employee_id']) || empty($item['title'])) {
                        continue;
                    }
                    $row = new EmployeeTask();
                    $row->tenant_id = (int) $request->tenant_id;
                    $row->branch_id = (int) (isset($item['branch_id']) ? $item['branch_id'] : $request->branch_id);
                    $row->employee_id = (int) $item['employee_id'];
                    $row->title = $item['title'];
                    $row->description = isset($item['description']) ? $item['description'] : '';
                    $row->task_date = isset($item['task_date']) ? $item['task_date'] : $request->task_date;
                    $row->due_date = isset($item['due_date']) ? $item['due_date'] : null;
                    $row->priority = isset($item['priority']) ? $item['priority'] : 'medium';
                    $row->task_status = 'pending';
                    $row->assigned_by = (int) ($request->assigned_by ?: $request->created_by ?: 0);
                    $row->created_by = (int) ($request->created_by ?: 0);
                    $row->status = 1;
                    $row->save();
                    $created[] = $this->formatTask(EmployeeTask::with(array('employee_det', 'branch_det'))->find($row->id));
                    \App\Services\MobilePushService::notifyEmployee(
                        (int) $row->employee_id,
                        'New Task Assigned',
                        $row->title,
                        array('type' => 'task_assigned', 'task_id' => (string) $row->id)
                    );
                }
            } else {
                $row = new EmployeeTask();
                $row->tenant_id = (int) $request->tenant_id;
                $row->branch_id = (int) $request->branch_id;
                $row->employee_id = (int) $request->employee_id;
                $row->title = $request->title;
                $row->description = $request->description;
                $row->task_date = $request->task_date;
                $row->due_date = $request->due_date;
                $row->priority = $request->priority ?: 'medium';
                $row->task_status = 'pending';
                $row->assigned_by = (int) ($request->assigned_by ?: $request->created_by ?: 0);
                $row->created_by = (int) ($request->created_by ?: 0);
                $row->status = 1;
                $row->save();
                $created[] = $this->formatTask(EmployeeTask::with(array('employee_det', 'branch_det'))->find($row->id));
                \App\Services\MobilePushService::notifyEmployee(
                    (int) $row->employee_id,
                    'New Task Assigned',
                    $row->title,
                    array('type' => 'task_assigned', 'task_id' => (string) $row->id)
                );
            }

            return response()->json(array(
                'status' => 'Success',
                'msg' => 'Task assigned successfully',
                'data' => $created,
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function list_employee_tasks(Request $request)
    {
        $this->switchTenantDb($request);

        try {
            $query = EmployeeTask::with(array('employee_det', 'branch_det'))
                ->where('status', 1)
                ->orderBy('task_date', 'desc')
                ->orderBy('id', 'desc');

            if ($request->tenant_id) {
                $query->where('tenant_id', (int) $request->tenant_id);
            }
            if ($request->branch_id) {
                $query->where('branch_id', (int) $request->branch_id);
            }
            if ($request->employee_id) {
                $query->where('employee_id', (int) $request->employee_id);
            }
            if ($request->task_status) {
                $query->where('task_status', $request->task_status);
            }
            if ($request->task_date) {
                $query->whereDate('task_date', $request->task_date);
            }
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('task_date', array($request->from_date, $request->to_date));
            }
            if ($request->mine && $request->employee_id) {
                $query->where('employee_id', (int) $request->employee_id);
            }

            $rows = $query->limit(500)->get();
            $data = array();
            foreach ($rows as $row) {
                $data[] = $this->formatTask($row);
            }

            return response()->json(array('status' => 'Success', 'msg' => 'OK', 'data' => $data), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage(), 'data' => array()), 200);
        }
    }

    public function update_employee_task_status(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'id' => 'required',
            'task_status' => 'required|in:pending,in_progress,completed,cancelled',
        ));
        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $row = EmployeeTask::find($request->id);
            if (!$row) {
                return response()->json(array('status' => 'Error', 'msg' => 'Task not found'), 200);
            }

            $row->task_status = $request->task_status;
            if ($request->has('employee_remark')) {
                $row->employee_remark = $request->employee_remark;
            }
            if ($request->task_status === 'completed') {
                $row->completed_at = Carbon::now()->toDateTimeString();
            } else {
                $row->completed_at = null;
            }
            $row->updated_by = (int) ($request->updated_by ?: $request->user_id ?: 0);
            $row->save();

            $fresh = EmployeeTask::with(array('employee_det', 'branch_det'))->find($row->id);
            $empName = $this->empName($fresh);
            \App\Services\MobilePushService::notifyAdmins(
                (int) $row->tenant_id,
                'Task Status Updated',
                $empName . ' marked "' . $row->title . '" as ' . str_replace('_', ' ', $row->task_status),
                array('type' => 'task_status', 'task_id' => (string) $row->id),
                (int) ($request->updated_by ?: $request->user_id ?: 0)
            );
            if ((int) $row->assigned_by > 0) {
                \App\Services\MobilePushService::notifyUser(
                    (int) $row->assigned_by,
                    'Task Status Updated',
                    $empName . ' updated task: ' . $row->title,
                    array('type' => 'task_status', 'task_id' => (string) $row->id)
                );
            }
            return response()->json(array(
                'status' => 'Success',
                'msg' => 'Task status updated',
                'data' => $this->formatTask($fresh),
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function delete_employee_task(Request $request)
    {
        $this->switchTenantDb($request);
        try {
            $row = EmployeeTask::find($request->id);
            if (!$row) {
                return response()->json(array('status' => 'Error', 'msg' => 'Task not found'), 200);
            }
            $row->delete();
            return response()->json(array('status' => 'Success', 'msg' => 'Task deleted'), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function store_employee_target(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'tenant_id' => 'required',
            'employee_id' => 'required',
            'period_type' => 'required|in:daily,weekly,monthly',
            'target_amount' => 'required|numeric|min:0',
        ));
        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $anchor = $request->period_start ?: ($request->target_date ?: Carbon::today()->toDateString());
            list($start, $end) = $this->resolvePeriodDates($request->period_type, $anchor);
            if ($request->period_start && $request->period_end) {
                $start = $request->period_start;
                $end = $request->period_end;
            }

            $targets = $request->targets;
            $created = array();

            if (is_array($targets) && count($targets) > 0) {
                foreach ($targets as $item) {
                    if (empty($item['employee_id']) || !isset($item['target_amount'])) {
                        continue;
                    }
                    $pType = isset($item['period_type']) ? $item['period_type'] : $request->period_type;
                    $pAnchor = isset($item['period_start']) ? $item['period_start'] : $anchor;
                    list($pStart, $pEnd) = $this->resolvePeriodDates($pType, $pAnchor);
                    if (!empty($item['period_start']) && !empty($item['period_end'])) {
                        $pStart = $item['period_start'];
                        $pEnd = $item['period_end'];
                    }

                    $existing = EmployeeTarget::where('employee_id', (int) $item['employee_id'])
                        ->where('period_type', $pType)
                        ->where('period_start', $pStart)
                        ->where('period_end', $pEnd)
                        ->where('target_type', isset($item['target_type']) ? $item['target_type'] : ($request->target_type ?: 'existing_client_collection'))
                        ->first();

                    if ($existing) {
                        $row = $existing;
                    } else {
                        $row = new EmployeeTarget();
                        $row->created_by = (int) ($request->created_by ?: 0);
                    }

                    $row->tenant_id = (int) $request->tenant_id;
                    $row->branch_id = (int) (isset($item['branch_id']) ? $item['branch_id'] : $request->branch_id);
                    $row->employee_id = (int) $item['employee_id'];
                    $row->target_type = isset($item['target_type']) ? $item['target_type'] : ($request->target_type ?: 'collection');
                    $row->period_type = $pType;
                    $row->period_start = $pStart;
                    $row->period_end = $pEnd;
                    $row->target_amount = (float) $item['target_amount'];
                    if (isset($item['achieved_amount'])) {
                        $row->achieved_amount = (float) $item['achieved_amount'];
                    }
                    $row->target_label = isset($item['target_label']) ? $item['target_label'] : ($request->target_label ?: 'Collection');
                    $row->notes = isset($item['notes']) ? $item['notes'] : '';
                    $row->updated_by = (int) ($request->created_by ?: 0);
                    $row->status = 1;
                    $row->save();
                    $created[] = $this->formatTarget(EmployeeTarget::with(array('employee_det', 'branch_det'))->find($row->id));
                    \App\Services\MobilePushService::notifyEmployee(
                        (int) $row->employee_id,
                        'New Target Set',
                        ($row->target_label ?: 'Target') . ' ₹' . number_format((float) $row->target_amount, 0),
                        array('type' => 'target_assigned', 'target_id' => (string) $row->id)
                    );
                }
            } else {
                $targetType = $request->target_type ?: 'existing_client_collection';
                $existing = EmployeeTarget::where('employee_id', (int) $request->employee_id)
                    ->where('period_type', $request->period_type)
                    ->where('period_start', $start)
                    ->where('period_end', $end)
                    ->where('target_type', $targetType)
                    ->first();

                if ($existing) {
                    $row = $existing;
                } else {
                    $row = new EmployeeTarget();
                    $row->created_by = (int) ($request->created_by ?: 0);
                }

                $row->tenant_id = (int) $request->tenant_id;
                $row->branch_id = (int) $request->branch_id;
                $row->employee_id = (int) $request->employee_id;
                $row->target_type = $targetType;
                $row->period_type = $request->period_type;
                $row->period_start = $start;
                $row->period_end = $end;
                $row->target_amount = (float) $request->target_amount;
                if ($request->has('achieved_amount')) {
                    $row->achieved_amount = (float) $request->achieved_amount;
                }
                $row->target_label = $request->target_label ?: 'Collection';
                $row->notes = $request->notes;
                $row->updated_by = (int) ($request->created_by ?: 0);
                $row->status = 1;
                $row->save();
                $created[] = $this->formatTarget(EmployeeTarget::with(array('employee_det', 'branch_det'))->find($row->id));
                \App\Services\MobilePushService::notifyEmployee(
                    (int) $row->employee_id,
                    'New Target Set',
                    ($row->target_label ?: 'Target') . ' ₹' . number_format((float) $row->target_amount, 0),
                    array('type' => 'target_assigned', 'target_id' => (string) $row->id)
                );
            }

            return response()->json(array(
                'status' => 'Success',
                'msg' => 'Target saved successfully',
                'data' => $created,
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function list_employee_targets(Request $request)
    {
        $this->switchTenantDb($request);

        try {
            if ($request->employee_id) {
                \App\Services\EmployeeMetricsService::syncTargetProgress(
                    (int) $request->tenant_id,
                    (int) $request->employee_id,
                    $request->on_date ?: Carbon::today()->toDateString()
                );
            }
            $query = EmployeeTarget::with(array('employee_det', 'branch_det'))
                ->where('status', 1)
                ->orderBy('period_start', 'desc')
                ->orderBy('id', 'desc');

            if ($request->tenant_id) {
                $query->where('tenant_id', (int) $request->tenant_id);
            }
            if ($request->branch_id) {
                $query->where('branch_id', (int) $request->branch_id);
            }
            if ($request->employee_id) {
                $query->where('employee_id', (int) $request->employee_id);
            }
            if ($request->period_type) {
                $query->where('period_type', $request->period_type);
            }
            if ($request->on_date) {
                $d = $request->on_date;
                $query->where('period_start', '<=', $d)->where('period_end', '>=', $d);
            }

            $rows = $query->limit(500)->get();
            $data = array();
            foreach ($rows as $row) {
                $data[] = $this->formatTarget($row);
            }

            return response()->json(array('status' => 'Success', 'msg' => 'OK', 'data' => $data), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage(), 'data' => array()), 200);
        }
    }

    public function update_employee_target_progress(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'id' => 'required',
            'achieved_amount' => 'required|numeric|min:0',
        ));
        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $row = EmployeeTarget::find($request->id);
            if (!$row) {
                return response()->json(array('status' => 'Error', 'msg' => 'Target not found'), 200);
            }
            $row->achieved_amount = (float) $request->achieved_amount;
            $row->updated_by = (int) ($request->updated_by ?: $request->user_id ?: 0);
            $row->save();

            $fresh = EmployeeTarget::with(array('employee_det', 'branch_det'))->find($row->id);
            return response()->json(array(
                'status' => 'Success',
                'msg' => 'Target progress updated',
                'data' => $this->formatTarget($fresh),
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function delete_employee_target(Request $request)
    {
        $this->switchTenantDb($request);
        try {
            $row = EmployeeTarget::find($request->id);
            if (!$row) {
                return response()->json(array('status' => 'Error', 'msg' => 'Target not found'), 200);
            }
            $row->delete();
            return response()->json(array('status' => 'Success', 'msg' => 'Target deleted'), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage()), 200);
        }
    }

    public function today_task_target_summary(Request $request)
    {
        $this->switchTenantDb($request);

        try {
            $today = $request->on_date ?: Carbon::today()->toDateString();
            $employeeId = (int) $request->employee_id;
            if ($employeeId) {
                \App\Services\EmployeeMetricsService::syncTargetProgress((int) $request->tenant_id, $employeeId, $today);
            }

            $taskQuery = EmployeeTask::where('status', 1)->whereDate('task_date', $today);
            if ($employeeId) {
                $taskQuery->where('employee_id', $employeeId);
            } elseif ($request->tenant_id) {
                $taskQuery->where('tenant_id', (int) $request->tenant_id);
            }
            if ($request->branch_id && !$employeeId) {
                $taskQuery->where('branch_id', (int) $request->branch_id);
            }

            $tasks = $taskQuery->get();
            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('task_status', 'completed')->count();
            $inProgressTasks = $tasks->where('task_status', 'in_progress')->count();
            $pendingTasks = $tasks->where('task_status', 'pending')->count();
            $taskPct = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

            $taskList = array();
            foreach ($tasks as $t) {
                $taskList[] = $this->formatTask(EmployeeTask::with(array('employee_det', 'branch_det'))->find($t->id));
            }

            $targetQuery = EmployeeTarget::where('status', 1)
                ->where('period_type', 'daily')
                ->where('period_start', '<=', $today)
                ->where('period_end', '>=', $today);
            if ($employeeId) {
                $targetQuery->where('employee_id', $employeeId);
            } elseif ($request->tenant_id) {
                $targetQuery->where('tenant_id', (int) $request->tenant_id);
            }

            $targets = $targetQuery->get();
            $todayTargetAmount = 0;
            $todayAchieved = 0;
            $targetRows = array();
            foreach ($targets as $tg) {
                $todayTargetAmount += (float) $tg->target_amount;
                $todayAchieved += (float) $tg->achieved_amount;
                $targetRows[] = $this->formatTarget(EmployeeTarget::with(array('employee_det', 'branch_det'))->find($tg->id));
            }
            $targetPct = $todayTargetAmount > 0 ? min(100, round(($todayAchieved / $todayTargetAmount) * 100)) : 0;

            // Also pull weekly / monthly active for employee
            $activeTargets = array();
            if ($employeeId) {
                $active = EmployeeTarget::with(array('employee_det', 'branch_det'))
                    ->where('status', 1)
                    ->where('employee_id', $employeeId)
                    ->where('period_start', '<=', $today)
                    ->where('period_end', '>=', $today)
                    ->get();
                foreach ($active as $a) {
                    $activeTargets[] = $this->formatTarget($a);
                }
            }

            return response()->json(array(
                'status' => 'Success',
                'msg' => 'OK',
                'data' => array(
                    'today' => $today,
                    'tasks_total' => $totalTasks,
                    'tasks_completed' => $completedTasks,
                    'tasks_in_progress' => $inProgressTasks,
                    'tasks_pending' => $pendingTasks,
                    'tasks_completion_percent' => $taskPct,
                    'today_tasks' => $taskList,
                    'today_target_amount' => $todayTargetAmount,
                    'today_target_achieved' => $todayAchieved,
                    'today_target_percent' => $targetPct,
                    'today_targets' => $targetRows,
                    'active_targets' => $activeTargets,
                ),
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array('status' => 'Error', 'msg' => $e->getMessage(), 'data' => array()), 200);
        }
    }
}
