<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveRequest extends Model
{
    use SoftDeletes;

    protected $table = 'employee_leave_requests';

    public function tenant_det()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function branch_det()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver_det()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
