<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceLogin extends Model
{
    use SoftDeletes;

    public function device_det()
    { 
    	return $this->belongsTo(Device::class,'device_id');
    }
}
