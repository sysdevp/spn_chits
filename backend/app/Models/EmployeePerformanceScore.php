<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformanceScore extends Model
{
    protected $table = 'employee_performance_scores';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'period_month',
        'collection_score', 'sales_score', 'activity_score', 'attendance_score',
        'overall_score', 'performance_rating', 'strengths', 'weaknesses',
        'training_required', 'status',
    ];

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
