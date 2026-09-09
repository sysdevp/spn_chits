<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendanceSetting extends Model
{
    protected $table = 'employee_attendance_settings';
    protected $fillable = [
        'tenant_id', 'branch_id', 'shift_start_time', 'shift_end_time',
        'late_grace_minutes', 'early_leave_grace_minutes', 'status',
        'created_by', 'updated_by',
    ];
}
