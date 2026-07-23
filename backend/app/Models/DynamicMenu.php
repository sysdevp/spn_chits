<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DynamicMenu extends Model
{
    use SoftDeletes;

    public function tenant_det()
	{
		return $this->belongsTo(Tenant::class,'tenant_id');
	}

    public function parent_det()
	{
		return $this->belongsTo(DynamicMenu::class,'parent_menu_id');
	}
}
