<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkPlan extends Model
{
    use SoftDeletes;
    protected $table = 'employee_work_plans';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'plan_date', 'title',
        'description', 'plan_status', 'created_by', 'updated_by', 'status',
    ];

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
