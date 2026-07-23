<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'role_name',
        'role_code',
        'remarks',
        // Parent role id for hierarchy
        'under_id',
    ];
	
	public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
	
	public function branch_det() 
	{
		return $this->belongsTo(Branch::class,'branch_id');
	}
	
	public function employee_det()
	{
		return $this->hasMany(Employee::class);
	}

	public function business_agent_det()
	{
		return $this->hasMany(BusinessAgent::class);
	}

	public function user_det()
	{
		return $this->hasMany(User::class);
	}

	public function role_type_det()
	{
		return $this->belongsTo(RoleType::class,'role_type_id');
	}

	public function role_permission_det()
	{
		return $this->hasOne(RolePermission::class, 'role_id')->orderBy('id', 'desc');
	}

    public function under_role_det()
    {
        return $this->belongsTo(Role::class, 'under_id');
    }
	public function permissions()
{
    return $this->hasOne(RolePermission::class, 'role_id');  // model = RolePermission
}
}
