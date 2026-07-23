<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountHead extends Model
{
	use SoftDeletes;
	
    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}
    public function accounts_group_det()
	{
		return $this->belongsTo(AccountGroup::class,'group_id');
	}
	public function account_expense_det()
	{
		return $this->hasMany(AccountExpense::class);
	}
}
