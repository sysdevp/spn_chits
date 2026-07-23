<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relationship extends Model
{
    use SoftDeletes;

    protected $fillable = [
       'relationship_name', 'gender'
    ];

	public function customer_det()
	{
		return $this->hasMany(Customer::class);
	}	
}
