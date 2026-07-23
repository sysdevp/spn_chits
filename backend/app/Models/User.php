<?php
namespace App\Models;
 
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\HeadOffice;
use App\Models\DeviceLogin;
use App\Models\Branch;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
	
	use SoftDeletes;
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'email', 'password', 'tenant_id', 'branch_id', 'employee_id', 'role_id', 'user_type'
    // ];
 
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password', 'remember_token',
    // ];
 
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
{
    return [
        'tenant_db' => session('tenant_db'), // or wherever you're storing tenant DB name
    ];
}
	

    public function employee_det()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function role_det()
    {
        return $this->belongsTo(Role::class,'role_id');
    }

    public function tenant_det()
    {
        return $this->belongsTo(Tenant::class,'tenant_id');
    }

    public function device_login_det()
    {
        return $this->hasMany(DeviceLogin::class);
    }

    public function branch_det()
    {
        return $this->belongsTo(Branch::class,'branch_id');
    }
	
	public function role()
{
    return $this->belongsTo(Role::class, 'role_id');
}
}