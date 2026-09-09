<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSkill extends Model
{
    use SoftDeletes;
    protected $table = 'employee_skills';
    protected $fillable = ['tenant_id', 'skill_name', 'category', 'description', 'status', 'created_by', 'updated_by'];
}
