<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanDocumentDetail extends Model
{
    use SoftDeletes;
	 
	public function document()
	{
		return $this->belongsTo(LoanDocument::class,'document_id');
	}
}
