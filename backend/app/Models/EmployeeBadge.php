<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeBadge extends Model
{
    use SoftDeletes;
    protected $table = 'employee_badges';
    protected $fillable = [
        'tenant_id', 'badge_name', 'badge_code', 'criteria_type', 'criteria_value',
        'icon', 'description', 'status', 'created_by', 'updated_by',
    ];
}
