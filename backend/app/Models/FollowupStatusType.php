<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowupStatusType extends Model
{
    use SoftDeletes; 

    public function followup_det()
    {
    	return $this->hasMany(Followup::class);
    }
	
	public function lead_management_det()
    {
    	return $this->hasMany(LeadManagement::class);
    }
}
