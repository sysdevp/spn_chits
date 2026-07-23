<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDetail extends Model
{
    use SoftDeletes;
	
	public function document()
	{
		return $this->belongsTo(Document::class,'document_id');
	}
}
