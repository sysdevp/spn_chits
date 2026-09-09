<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertificate extends Model
{
    protected $table = 'employee_certificates';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'certificate_title',
        'certificate_type', 'period_month', 'issued_date', 'issued_by',
        'remarks', 'status',
    ];

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
