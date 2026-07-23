<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleType extends Model
{
    use SoftDeletes;

    public function role_det()
	{
		return $this->hasMany(Role::class);
	}
}
