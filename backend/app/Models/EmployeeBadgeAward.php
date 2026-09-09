<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeBadgeAward extends Model
{
    protected $table = 'employee_badge_awards';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'badge_id',
        'awarded_date', 'period_month', 'reason', 'status',
    ];

    public function badge_det()
    {
        return $this->belongsTo(EmployeeBadge::class, 'badge_id');
    }

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
