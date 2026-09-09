<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSkillAssessment extends Model
{
    use SoftDeletes;
    protected $table = 'employee_skill_assessments';
    protected $fillable = [
        'tenant_id', 'branch_id', 'employee_id', 'skill_id', 'score',
        'strength_notes', 'weakness_notes', 'training_required', 'training_notes',
        'assessed_by', 'assessed_date', 'status', 'created_by', 'updated_by',
    ];

    public function skill_det()
    {
        return $this->belongsTo(EmployeeSkill::class, 'skill_id');
    }

    public function employee_det()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
