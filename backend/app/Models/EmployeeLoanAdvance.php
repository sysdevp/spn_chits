<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLoanAdvance extends Model
{
    use SoftDeletes;

    protected $table = 'employee_loan_advances';

    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'request_type', 'amount',
        'tenure_months', 'emi_amount', 'request_date', 'reason', 'request_status',
        'decided_by', 'decided_at', 'decision_remarks', 'created_by', 'updated_by', 'status',
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
