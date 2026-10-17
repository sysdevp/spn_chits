<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTarget extends Model
{
    use SoftDeletes;

    protected $table = 'employee_targets';

    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'period_type', 'target_type',
        'period_start', 'period_end', 'target_amount', 'business_value', 'achieved_amount',
        'target_label', 'notes', 'created_by', 'updated_by', 'status',
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
