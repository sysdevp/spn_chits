<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTask extends Model
{
    use SoftDeletes;

    protected $table = 'employee_tasks';

    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'title', 'description',
        'task_date', 'due_date', 'priority', 'task_status', 'assigned_by',
        'completed_at', 'employee_remark', 'created_by', 'updated_by', 'status',
    ];

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function branch_det()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
