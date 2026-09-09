<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeCustomerVisit extends Model
{
    use SoftDeletes;
    protected $table = 'employee_customer_visits';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'customer_id', 'lead_id',
        'visit_type', 'visit_date', 'visit_time', 'latitude', 'longitude',
        'purpose', 'outcome', 'followup_date', 'visit_status',
        'created_by', 'updated_by', 'status',
    ];

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
