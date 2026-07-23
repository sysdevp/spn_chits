<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
  use SoftDeletes;

  protected $fillable = [
   'tenant_id', 'branch_id', 'group_type', 'group_name', 'scheme_id', 'auction_date', 'auction_time', 'starting_date', 'first_due_date', 'commission_percent','fd_number','total_fd','company_fd','rate_of_interest','fd_date','maturity_amount','fd_bank_id','fd_branch','pso_number','pso_date','blaw_number','blaw_date','cheque_no','company_chit','group_base_penalty','group_base_bonus','group_base_interest','group_base_dividend_cancel_days','group_penalty_days','group_bonus_days','group_base_incentive_percentage'
  ];

  public function enrol_det()
  {
    return $this->hasMany(ChitDetail::class);
  }

  public function bidding_det()
  {
    return $this->hasMany(BiddingDetail::class);
  }

  public function update_group_det()
  {
    return $this->hasMany(UpdateGroup::class);
  }

  public function tenant_det()
  {
    return $this->belongsTo(Tenant::class,'tenant_id');
  }

  public function branch_det()
  {
    return $this->belongsTo(Branch::class,'branch_id');
  }
  
	public function scheme_det()
  {
    return $this->belongsTo(Scheme::class,'scheme_id');
  }
   
  public function bank_det()
  {
    return $this->belongsTo(BankDetail::class,'fd_bank_id');
  }

	public function receipt_det()
  {
    return $this->hasMany(Receipt::class);
  }
}
