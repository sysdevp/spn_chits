<?php
namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeaveRequest;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Validator;

class EmployeeLeaveRequestController extends Controller
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
        if (Schema::hasTable('employee_leave_requests')) {
            return;
        }

        Schema::create('employee_leave_requests', function ($table) {
            $table->increments('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('employee_id')->default(0);
            $table->string('request_type', 20);
            $table->tinyInteger('leave_type')->default(0);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->string('session', 20)->nullable();
            $table->text('reason')->nullable();
            $table->string('request_status', 20)->default('pending');
            $table->integer('approved_by')->default(0);
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_remark')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function formatRow($row)
    {
        $emp = $row->employee_det;
        $branch = $row->branch_det;
        $typeLabels = array('leave' => 'Leave', 'permission' => 'Permission', 'od' => 'On Duty');
        $leaveLabels = array(0 => '', 1 => 'Paid', 2 => 'Unpaid');
        $name = '';
        if ($emp) {
            $name = trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : ''));
        }

        return array(
            'id' => (int) $row->id,
            'tenant_id' => (int) $row->tenant_id,
            'branch_id' => (int) $row->branch_id,
            'employee_id' => (int) $row->employee_id,
            'employee_name' => $name,
            'branch_name' => $branch && isset($branch->branch_name) ? $branch->branch_name : '',
            'request_type' => $row->request_type,
            'request_type_label' => isset($typeLabels[$row->request_type]) ? $typeLabels[$row->request_type] : $row->request_type,
            'leave_type' => (int) $row->leave_type,
            'leave_type_label' => isset($leaveLabels[(int) $row->leave_type]) ? $leaveLabels[(int) $row->leave_type] : '',
            'from_date' => $row->from_date,
            'to_date' => $row->to_date,
            'from_time' => $row->from_time,
            'to_time' => $row->to_time,
            'session' => $row->session,
            'reason' => $row->reason,
            'request_status' => $row->request_status,
            'approved_by' => (int) $row->approved_by,
            'approved_by_name' => $this->userDisplayName((int) $row->approved_by),
            'requested_by_name' => $name,
            'approved_at' => $row->approved_at,
            'approval_remark' => $row->approval_remark,
            'created_at' => $row->created_at ? (string) $row->created_at : '',
        );
    }

    private $userNames = array();

    /** approved_by holds a users.id from the web panel; resolve it to the linked employee's name. */
    private function userDisplayName($userId)
    {
        if (!$userId) {
            return '';
        }
        if (!array_key_exists($userId, $this->userNames)) {
            $label = '';
            try {
                $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();
                if ($user && !empty($user->employee_id)) {
                    $emp = \Illuminate\Support\Facades\DB::table('employees')->where('id', $user->employee_id)->first(['first_name', 'last_name']);
                    $label = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
                }
                if ($label === '' && $user) {
                    $label = $user->name ?? ($user->email ?? '');
                }
            } catch (\Exception $e) {
                $label = '';
            }
            $this->userNames[$userId] = $label;
        }
        return $this->userNames[$userId];
    }

    public function store_leave_request(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'tenant_id' => 'required',
            'employee_id' => 'required',
            'request_type' => 'required|in:leave,permission,od',
            'from_date' => 'required|date',
        ));

        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $type = $request->request_type;
            $fromDate = date('Y-m-d', strtotime($request->from_date));
            $toDate = $request->to_date ? date('Y-m-d', strtotime($request->to_date)) : $fromDate;

            if ($type === 'permission') {
                $toDate = $fromDate;
            }

            $row = new EmployeeLeaveRequest();
            $row->tenant_id = $request->tenant_id;
            $row->branch_id = $request->branch_id ? $request->branch_id : 0;
            $row->employee_id = $request->employee_id;
            $row->request_type = $type;
            $row->leave_type = $type === 'leave' ? (int) ($request->leave_type ? $request->leave_type : 1) : 0;
            $row->from_date = $fromDate;
            $row->to_date = $toDate;
            $row->from_time = $request->from_time ? $request->from_time : null;
            $row->to_time = $request->to_time ? $request->to_time : null;
            $row->session = $request->session ? $request->session : 'full';
            $row->reason = $request->reason ? $request->reason : '';
            $row->request_status = 'pending';
            $row->created_by = $request->created_by ? $request->created_by : ($request->user_id ? $request->user_id : 0);
            $row->status = 1;
            $row->save();

            return response()->json(array(
                'status' => 'Success',
                'msg' => 'Request submitted successfully',
                'data' => $this->formatRow($row),
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array(
                'status' => 'Error',
                'msg' => 'Unable to submit request',
                'error' => $e->getMessage(),
            ), 200);
        }
    }

    public function list_leave_requests(Request $request)
    {
        $this->switchTenantDb($request);

        try {
            $query = EmployeeLeaveRequest::with(['employee_det', 'branch_det'])->orderBy('id', 'desc');

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if ($request->for_admin) {
                // Admin inbox: all employees in the tenant
            } else {
                if ($request->branch_id) {
                    $query->where('branch_id', $request->branch_id);
                }
                if (($request->mine || $request->employee_id) && (int) $request->employee_id > 0) {
                    $query->where('employee_id', (int) $request->employee_id);
                }
            }
            if ($request->request_type) {
                $query->where('request_type', $request->request_type);
            }
            if ($request->request_status) {
                $query->where('request_status', $request->request_status);
            }

            $rows = $query->get();
            $data = array();
            foreach ($rows as $row) {
                $data[] = $this->formatRow($row);
            }

            return response()->json(array('status' => 'Success', 'msg' => '', 'data' => $data), 200);
        } catch (\Exception $e) {
            return response()->json(array(
                'status' => 'Error',
                'msg' => 'Unable to fetch requests',
                'error' => $e->getMessage(),
            ), 200);
        }
    }

    public function decide_leave_request(Request $request)
    {
        $this->switchTenantDb($request);

        $validator = Validator::make($request->all(), array(
            'id' => 'required',
            'decision' => 'required|in:approved,rejected',
        ));

        if ($validator->fails()) {
            return response()->json(array('status' => 'Error', 'msg' => $validator->errors()->first()), 200);
        }

        try {
            $row = EmployeeLeaveRequest::find($request->id);
            if (!$row) {
                return response()->json(array('status' => 'Error', 'msg' => 'Request not found'), 200);
            }
            if ($row->request_status !== 'pending') {
                return response()->json(array('status' => 'Error', 'msg' => 'Request is already ' . $row->request_status), 200);
            }

            $row->request_status = $request->decision;
            $row->approved_by = $request->approved_by ? $request->approved_by : ($request->user_id ? $request->user_id : 0);
            $row->approved_at = date('Y-m-d H:i:s');
            $row->approval_remark = $request->approval_remark ? $request->approval_remark : '';
            $row->updated_by = $row->approved_by;
            $row->save();

            if ($request->decision === 'approved') {
                $this->applyApprovedRequest($row);
            }

            return response()->json(array(
                'status' => 'Success',
                'msg' => $request->decision === 'approved' ? 'Request approved' : 'Request rejected',
                'data' => $this->formatRow($row),
            ), 200);
        } catch (\Exception $e) {
            return response()->json(array(
                'status' => 'Error',
                'msg' => 'Unable to update request',
                'error' => $e->getMessage(),
            ), 200);
        }
    }

    private function applyApprovedRequest(EmployeeLeaveRequest $row)
    {
        if ($row->request_type === 'permission') {
            return;
        }

        $from = Carbon::parse($row->from_date);
        $to = Carbon::parse($row->to_date ? $row->to_date : $row->from_date);
        $session = $row->session ? $row->session : 'full';

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $attDate = $d->format('Y-m-d');
            $att = EmployeeAttendance::where('tenant_id', $row->tenant_id)
                ->where('employee_id', $row->employee_id)
                ->where('att_date', $attDate)
                ->first();

            if (!$att) {
                $att = new EmployeeAttendance();
                $att->tenant_id = $row->tenant_id;
                $att->branch_id = $row->branch_id;
                $att->employee_id = $row->employee_id;
                $att->att_date = $attDate;
                $att->created_by = $row->approved_by;
                $att->status = 1;
            }

            if ($row->request_type === 'leave') {
                $att->attendance_type = 0;
                $att->leave_type = $row->leave_type ? $row->leave_type : 1;
                $att->forenoon_session = ($session === 'full' || $session === 'forenoon') ? 1 : 0;
                $att->afternoon_session = ($session === 'full' || $session === 'afternoon') ? 1 : 0;
                $att->remarks = 'Approved leave' . ($row->reason ? ': ' . $row->reason : '');
            } else {
                $att->attendance_type = 1;
                $att->leave_type = 0;
                $att->remarks = 'Approved OD' . ($row->reason ? ': ' . $row->reason : '');
            }
            $att->save();
        }
    }

    public function list_punch_attendances(Request $request)
    {
        $this->switchTenantDb($request);

        try {
            $query = EmployeeAttendance::with(['employee_det', 'branch_det'])->orderBy('att_date', 'desc')->orderBy('id', 'desc');
            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if ($request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }
            if ($request->employee_id) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->from_date) {
                $query->whereDate('att_date', '>=', date('Y-m-d', strtotime($request->from_date)));
            }
            if ($request->to_date) {
                $query->whereDate('att_date', '<=', date('Y-m-d', strtotime($request->to_date)));
            }

            $rows = $query->limit(300)->get();
            $data = array();
            foreach ($rows as $row) {
                $emp = $row->employee_det;
                $name = '';
                if ($emp) {
                    $name = trim(($emp->first_name ? $emp->first_name : '') . ' ' . ($emp->last_name ? $emp->last_name : ''));
                }
                $checkInLat = $row->check_in_latitude ? $row->check_in_latitude : $row->latitude;
                $checkInLng = $row->check_in_longitude ? $row->check_in_longitude : $row->longitude;
                $checkInPhoto = $row->check_in_photo ?? null;
                $checkOutPhoto = $row->check_out_photo ?? null;
                $data[] = array(
                    'id' => (int) $row->id,
                    'employee_id' => (int) $row->employee_id,
                    'employee_name' => $name,
                    'branch_id' => (int) $row->branch_id,
                    'branch_name' => $row->branch_det && isset($row->branch_det->branch_name) ? $row->branch_det->branch_name : '',
                    'att_date' => $row->att_date,
                    'att_view_date' => $row->att_date ? date('d/m/Y', strtotime($row->att_date)) : '',
                    'check_in_time' => $row->check_in_time,
                    'check_out_time' => $row->check_out_time,
                    'punch_status' => $row->punch_status ? $row->punch_status : 'none',
                    'outside_office' => (int) ($row->outside_office ?? 0),
                    'outside_office_remarks' => $row->outside_office_remarks ?? '',
                    'outside_approval_status' => $row->outside_approval_status ?? '',
                    'punch_distance_meters' => $row->punch_distance_meters !== null ? (float) $row->punch_distance_meters : null,
                    'location_name' => $row->live_location_name ?? '',
                    'check_in_latitude' => $checkInLat,
                    'check_in_longitude' => $checkInLng,
                    'check_out_latitude' => $row->check_out_latitude,
                    'check_out_longitude' => $row->check_out_longitude,
                    'check_in_photo' => $checkInPhoto,
                    'check_out_photo' => $checkOutPhoto,
                    'check_in_photo_url' => $this->buildPunchPhotoUrl($checkInPhoto),
                    'check_out_photo_url' => $this->buildPunchPhotoUrl($checkOutPhoto),
                    'map_url' => ($checkInLat && $checkInLng)
                        ? ('https://maps.google.com/maps?q=' . $checkInLat . ',' . $checkInLng . '&z=16&output=embed')
                        : '',
                );
            }

            return response()->json(array('status' => 'Success', 'msg' => '', 'data' => $data), 200);
        } catch (\Exception $e) {
            return response()->json(array(
                'status' => 'Error',
                'msg' => 'Unable to fetch punch attendance',
                'error' => $e->getMessage(),
            ), 200);
        }
    }

    private function buildPunchPhotoUrl($photo)
    {
        if (empty($photo)) {
            return '';
        }
        $photo = str_replace('\\', '/', (string) $photo);
        if (preg_match('#^https?://#i', $photo) || preg_match('#^data:#i', $photo)) {
            return $photo;
        }
        $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
        // Match employee/customer photo URL pattern used elsewhere in the app
        if (strpos($photo, 'uploads/') !== false) {
            $rel = ltrim(preg_replace('#^.*?uploads/#', 'uploads/', $photo), '/');
            return $base . '/storage/app/' . $rel;
        }
        return $base . '/storage/app/uploads/punch_photos/' . ltrim(basename($photo), '/');
    }
}
