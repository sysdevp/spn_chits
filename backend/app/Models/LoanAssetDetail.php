<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanAssetDetail extends Model
{
    use SoftDeletes;
	
	public function loan_asset_document()
	{
		return $this->belongsTo(LoanDocument::class,'document_id');
	}
}
