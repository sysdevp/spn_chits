<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PettyCashExpenseType extends Model
{
	use SoftDeletes;
	
    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

	public function petty_cash_expense_det()
	{
		return $this->hasMany(PettyCashExpense::class);
	}
}
