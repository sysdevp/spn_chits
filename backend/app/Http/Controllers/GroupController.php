<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Scheme;
use App\Models\Customer;
use App\Models\ChitDetail;
use App\Models\BiddingDetail;
use App\Models\Role;
use App\Models\UpdateGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use DB;

class GroupController extends Controller
{
    public function store_group(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'group_type' => 'required',
            'group_name' => 'required',
            'scheme_id' => 'required',
            'auction_date' => 'required',
            //'starting_date' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
			   //DB::enableQueryLog();
                $group_det = new Group(); 
				$group_det->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$group_det->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $group_det->group_type = isset($request->group_type) ? ($request->group_type) : 0;
                $group_det->group_name = isset($request->group_name) ? ($request->group_name) : 0;
                $group_det->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0; 
                $group_det->auction_date = isset($request->auction_date) ? ($request->auction_date) : 0;
                $group_det->auction_time = isset($request->auction_time) ? ($request->auction_time) : 0;
                //$group_det->starting_date = '';
                if(isset($request->starting_date)) {
                    $s_date = ($request->starting_date['year']).'-'.sprintf('%02d',($request->starting_date['month'])).'-'.sprintf('%02d',($request->starting_date['day'])); 
                    $group_det->starting_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $group_det->starting_date = '0000-00-00';
                }
                if(isset($request->scheme_id)) {//print_r("tyest");
                    $scheme_info = Scheme::find($request->scheme_id);
                    $no_of_months = ($scheme_info->no_of_months);
					$group_det->termination_date = strtotime("+".$no_of_months."months",strtotime($group_det->starting_date));
					$group_det->termination_date = date("Y/m/d", $group_det->termination_date);
					//print_r($group_det->termination_date);
                }
                //$group_det->first_due_date = '';
                if(isset($request->first_due_date)) {
                    $f_date = ($request->first_due_date['year']).'-'.sprintf('%02d',($request->first_due_date['month'])).'-'.sprintf('%02d',($request->first_due_date['day']));
                    $group_det->first_due_date = date('Y-m-d',strtotime($f_date)); 
                }
                else {
                    // $group_det->first_due_date = '0000-00-00';
                }
                $group_det->commission_percent = isset($request->commission_percent) ? ($request->commission_percent) : 0;
                $group_det->fd_number = isset($request->fd_number) ? ($request->fd_number) : 0;
                $group_det->total_fd = isset($request->total_fd) ? ($request->total_fd) : 0;
                $group_det->company_fd = isset($request->company_fd) ? ($request->company_fd) : 0; 
                $group_det->rate_of_interest = isset($request->rate_of_interest) ? ($request->rate_of_interest) : 0;
                //$group_det->fd_date = '';
                if(isset($request->fd_date)) {
                    $fd_date = ($request->fd_date['year']).'-'.sprintf('%02d',($request->fd_date['month'])).'-'.sprintf('%02d',($request->fd_date['day']));
                    $group_det->fd_date = date('Y-m-d',strtotime($fd_date)); 
                }
                else {
                    // $group_det->fd_date = '0000-00-00';
                }
                // print_r($group_det->fd_date);exit;
                $group_det->fd_rate = isset($request->fd_rate) ? ($request->fd_rate) : 0;
                $group_det->maturity_amount = isset($request->maturity_amount) ? ($request->maturity_amount) : 0;
                $group_det->fd_bank_id = isset($request->fd_bank_id) ? ($request->fd_bank_id) : 0;
                $group_det->fd_branch = isset($request->fd_branch) ? ($request->fd_branch) : 0;
                $group_det->pso_number = isset($request->pso_number) ? ($request->pso_number) : 0;
                //$group_det->pso_date = '';
                if(isset($request->pso_date)) {
                    $pso_date = ($request->pso_date['year']).'-'.sprintf('%02d',($request->pso_date['month'])).'-'.sprintf('%02d',($request->pso_date['day']));
                    $group_det->pso_date = date('Y-m-d',strtotime($pso_date)); 
                }
                else {
                    // $group_det->pso_date = '0000-00-00';
                }
                $group_det->blaw_number = isset($request->blaw_number) ? ($request->blaw_number) : 0;
                //$group_det->blaw_date = '';
                if(isset($request->blaw_date)) {
                    $blaw_date = ($request->blaw_date['year']).'-'.sprintf('%02d',($request->blaw_date['month'])).'-'.sprintf('%02d',($request->blaw_date['day']));
                    $group_det->blaw_date = date('Y-m-d',strtotime($blaw_date)); 
                }
                else {
                    // $group_det->blaw_date = '0000-00-00';
                }
                $group_det->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0;
                $group_det->company_chit = isset($request->company_chit) ? ($request->company_chit) : 0;
                $group_det->group_base_penalty = isset($request->group_base_penalty) ? ($request->group_base_penalty) : 0;
                $group_det->group_base_bonus = isset($request->group_base_bonus) ? ($request->group_base_bonus) : 0;
                $group_det->group_base_interest = isset($request->group_base_interest) ? ($request->group_base_interest) : 0;
                $group_det->group_base_dividend_cancel_days = isset($request->group_base_dividend_cancel_days) ? ($request->group_base_dividend_cancel_days) : 0;
                $group_det->group_penalty_days = isset($request->group_penalty_days) ? ($request->group_penalty_days) : 0;
                $group_det->group_bonus_days = isset($request->group_bonus_days) ? ($request->group_bonus_days) : 0;
                $group_det->group_base_incentive_percentage = isset($request->group_base_incentive_percentage) ? ($request->group_base_incentive_percentage) : 0;
                //$group_det->fd_renewal_date = '';
                if(isset($request->fd_renewal_date)) {
                    $fd_renewal_date = ($request->fd_renewal_date['year']).'-'.sprintf('%02d',($request->fd_renewal_date['month'])).'-'.sprintf('%02d',($request->fd_renewal_date['day']));
                    $group_det->fd_renewal_date = date('Y-m-d',strtotime($fd_renewal_date));
                }
                else {
                    // $group_det->fd_renewal_date = '0000-00-00';
                }
                //$group_det->commit_date = '';
                if(isset($request->commit_date)) {
                    $commit_date = ($request->commit_date['year']).'-'.sprintf('%02d',($request->commit_date['month'])).'-'.sprintf('%02d',($request->commit_date['day']));
                    $group_det->commit_date = date('Y-m-d',strtotime($commit_date)); 
                }
                else {
                    // $group_det->commit_date = '0000-00-00';
                }
                if(isset($request->scheme_id)) {
                    $scheme_info = Scheme::find($request->scheme_id);
                    $group_det->group_pending_ticket = ($scheme_info->members);
                }
                $group_det->is_filled = 1;
                $group_det->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $group_det->status = isset($request->status) ? ($request->status) : 1;
                if($group_det->save()) { 
				if($request->group_type != 2){	
				// print_r($group_det);exit;
					$enrol = new ChitDetail(); 
					$enrol->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$enrol->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$enrol->customer_id = 1;
					$group_id = Group::where('branch_id',$request->branch_id)->max('id');
					$enrol->group_id = $group_id;
					$enrol->ticket_no = 1;
					$enrol->prized_status = 0;
				    //$enrol->date_introduced = isset($request->date_introduced) ? ($request->date_introduced) : '0000-00-00';
                    if(isset($request->date_introduced)) {
                        $s_date = ($request->date_introduced['year']).'-'.sprintf('%02d',($request->date_introduced['month'])).'-'.sprintf('%02d',($request->date_introduced['day']));
                        $enrol->date_introduced = date('Y-m-d',strtotime($s_date));
                    }
                    else {
                        // $enrol->date_introduced = '0000-00-00';
                    }
					$enrol->chit_type = 2;
					$enrol->customer_type = 1;
					$enrol->ticket_type = 1;
					$enrol->created_by = isset($request->created_by) ? ($request->created_by) : 0;
					$enrol->status = isset($request->status) ? ($request->status) : 1;
					// print_r($enrol);exit;
				    if($enrol->save()) {
					
					$group = Group::find($enrol->group_id);
					// pending ticket calculation
					$group_pend_full_ticket = ChitDetail::where(array('group_id'=>$enrol->group_id,'ticket_type'=>'1'))->count();
					$group_pend_half_ticket = ChitDetail::where(array('group_id'=>$enrol->group_id,'ticket_type'=>'2'))->count();
                    $no_of_members = 0;
                    if(isset($group->scheme_det)) { 
					    $no_of_members = isset($group->scheme_det->members) ? ($group->scheme_det->members) : 0;
                    }
					$total_ticket_count = $group_pend_full_ticket + ($group_pend_half_ticket/2);
                    $pending_ticket_count = $no_of_members - $total_ticket_count;
					$group->group_pending_ticket = $pending_ticket_count;
					if($pending_ticket_count>0)
					{
						$group->is_filled = '1'; 
					} else {
						$group->is_filled = '0'; 
					}

					if($group->save())
					{

						$update_group = new UpdateGroup(); 
						$update_group->tenant_id = isset($group_det->tenant_id) ? ($group_det->tenant_id) : 0;
						$update_group->branch_id = isset($group_det->branch_id) ? ($group_det->branch_id) : 0;
		                $update_group->group_type = isset($group_det->group_type) ? ($group_det->group_type) : 0;
		                $update_group->group_name = isset($group_det->group_name) ? ($group_det->group_name) : 0;
		                $update_group->edit_date = date('Y-m-d');
		                $update_group->group_id = isset($group_det->id) ? ($group_det->id) : 0;
		                $update_group->scheme_id = isset($group_det->scheme_id) ? ($group_det->scheme_id) : 0; 
		                $update_group->auction_date = isset($group_det->auction_date) ? ($group_det->auction_date) : 0;
		                $update_group->auction_time = isset($group_det->auction_time) ? ($group_det->auction_time) : 0;
		                $update_group->starting_date = isset($group_det->starting_date) ? ($group_det->starting_date) : '';
		                $update_group->first_due_date = isset($group_det->first_due_date) ? ($group_det->first_due_date) : '';
		                $update_group->commission_percent = isset($group_det->commission_percent) ? ($group_det->commission_percent) : 0;
		                $update_group->fd_number = isset($group_det->fd_number) ? ($group_det->fd_number) : 0;
		                $update_group->total_fd = isset($group_det->total_fd) ? ($group_det->total_fd) : 0;
		                $update_group->company_fd = isset($group_det->company_fd) ? ($group_det->company_fd) : 0; 
		                $update_group->rate_of_interest = isset($group_det->rate_of_interest) ? ($group_det->rate_of_interest) : 0;
		                $update_group->fd_date = isset($group_det->fd_date) ? ($group_det->fd_date) : '';
		                $update_group->fd_rate = isset($group_det->fd_rate) ? ($group_det->fd_rate) : 0;
		                $update_group->maturity_amount = isset($group_det->maturity_amount) ? ($group_det->maturity_amount) : 0;
		                $update_group->fd_bank_id = isset($group_det->fd_bank_id) ? ($group_det->fd_bank_id) : 0;
		                $update_group->fd_branch = isset($group_det->fd_branch) ? ($group_det->fd_branch) : 0;
		                $update_group->pso_number = isset($group_det->pso_number) ? ($group_det->pso_number) : 0;
		                $update_group->pso_date = isset($group_det->pso_date) ? ($group_det->pso_date) : '';
		                $update_group->blaw_number = isset($group_det->blaw_number) ? ($group_det->blaw_number) : 0;
		                $update_group->blaw_date = isset($group_det->blaw_date) ? ($group_det->blaw_date) : '';
		                $update_group->cheque_no = isset($group_det->cheque_no) ? ($group_det->cheque_no) : 0;
		                $update_group->company_chit = isset($group_det->company_chit) ? ($group_det->company_chit) : 0;
		                $update_group->group_base_penalty = isset($group_det->group_base_penalty) ? ($group_det->group_base_penalty) : 0;
		                $update_group->group_base_bonus = isset($group_det->group_base_bonus) ? ($group_det->group_base_bonus) : 0;
		                $update_group->group_base_interest = isset($group_det->group_base_interest) ? ($group_det->group_base_interest) : 0;
		                $update_group->group_base_dividend_cancel_days = isset($group_det->group_base_dividend_cancel_days) ? ($group_det->group_base_dividend_cancel_days) : 0;
		                $update_group->group_penalty_days = isset($group_det->group_penalty_days) ? ($group_det->group_penalty_days) : 0;
		                $update_group->group_bonus_days = isset($group_det->group_bonus_days) ? ($group_det->group_bonus_days) : 0;
		                $update_group->group_base_incentive_percentage = isset($group_det->group_base_incentive_percentage) ? ($group_det->group_base_incentive_percentage) : 0;
		                $update_group->fd_renewal_date = isset($group_det->fd_renewal_date) ? ($group_det->fd_renewal_date) : '';
		                $update_group->commit_date = isset($group_det->commit_date) ? ($group_det->commit_date) : '';
		                
		                $update_group->group_pending_ticket = isset($group_det->group_pending_ticket) ? ($group_det->group_pending_ticket) : 0;
		                $update_group->is_filled = isset($group_det->is_filled) ? ($group_det->is_filled) : 0;
		                $update_group->created_by = isset($group_det->created_by) ? ($group_det->created_by) : 0;
		                $update_group->status = isset($group_det->status) ? ($group_det->status) : 1;
                        // print_r($group_det->fd_date);exit;
		                if($update_group->save())
		                {
							if($group_det->group_type == 6){
							$bidding_detail = new BiddingDetail(); 
							$bidding_detail->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
							$bidding_detail->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
							$bidding_detail->group_id = $group_id;
							$bidding_detail->installment_no = 1; 
							$bidding_detail->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
							$cur_insts = Scheme::where('id','=',$group_det->scheme_id)->pluck('monthly_due');
							$cur_inst = isset($cur_insts[0]) ? ($cur_insts[0]) : 0;
							$current_inst_amount = $cur_inst;
                            $bidding_detail->current_installment_amount = $current_inst_amount; 		
                            $bidding_detail->installment_amount = $current_inst_amount; 		
                            $bidding_detail->auction_date = $group_det->starting_date; 				
							$bidding_detail->remark = isset($request->remark) ? ($request->remark) : '';
							$bidding_detail->created_by = isset($request->created_by) ? ($request->created_by) : 0;
							$bidding_detail->status = isset($request->status) ? ($request->status) : 1; 
							$bidding_detail->save();
							}
							
		                   
							}
		                }
		             }
				}
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_group_added');
					$response['data'] = $group_det;
					return response()->json($response,200);
				}  else {
		                    $response['status']="Error";
		                    $response['msg']=\Lang::get('api.global_error');
		                    return response()->json($response,401);
		                }
				
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
				$response['msg'] = $e;
                return response()->json($response, 401);
            }
        }
    }


    public function update_group(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'tenant_id' => 'required',
            'branch_id' => 'required',
            'group_type' => 'required',
            'group_name' => 'required',
            'scheme_id' => 'required',
            'auction_date' => 'required',
            'starting_date' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$group_det = Group::find($request->id);
					$group_det->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$group_det->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$group_det->group_type = isset($request->group_type) ? ($request->group_type) : 0;
					$group_det->group_name = isset($request->group_name) ? ($request->group_name) : 0;
					$group_det->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
					$group_det->auction_date = isset($request->auction_date) ? ($request->auction_date) : 0;
					$group_det->auction_time = isset($request->auction_time) ? ($request->auction_time) : 0;
                    if(isset($request->starting_date)) {
                        $starting_date = ($request->starting_date['year']).'-'.sprintf('%02d',($request->starting_date['month'])).'-'.sprintf('%02d',($request->starting_date['day']));
                        $group_det->starting_date = date('Y-m-d',strtotime($starting_date)); 
                    }
                    if(isset($request->first_due_date)) {
                        $first_due_date = ($request->first_due_date['year']).'-'.sprintf('%02d',($request->first_due_date['month'])).'-'.sprintf('%02d',($request->first_due_date['day']));
                        $group_det->first_due_date = date('Y-m-d',strtotime($first_due_date)); 
                    }
					$group_det->commission_percent = isset($request->commission_percent) ? ($request->commission_percent) : '';
					$group_det->fd_number = isset($request->fd_number) ? ($request->fd_number) : '';
					$group_det->total_fd = isset($request->total_fd) ? ($request->total_fd) : '';
					$group_det->company_fd = isset($request->company_fd) ? ($request->company_fd) : '';
					$group_det->rate_of_interest = isset($request->rate_of_interest) ? ($request->rate_of_interest) : '';
                    if(isset($request->fd_date)) {
                        $fd_date = ($request->fd_date['year']).'-'.sprintf('%02d',($request->fd_date['month'])).'-'.sprintf('%02d',($request->fd_date['day']));
                        $group_det->fd_date = date('Y-m-d',strtotime($fd_date)); 
                    }
                    $group_det->fd_rate = isset($request->fd_rate) ? ($request->fd_rate) : 0;
					$group_det->maturity_amount = isset($request->maturity_amount) ? ($request->maturity_amount) : '';
					$group_det->fd_bank_id = isset($request->fd_bank_id) ? ($request->fd_bank_id) : '';
					$group_det->fd_branch = isset($request->fd_branch) ? ($request->fd_branch) : '';
					$group_det->pso_number = isset($request->pso_number) ? ($request->pso_number) : '';
                    if(isset($request->pso_date)) {
                        $pso_date = ($request->pso_date['year']).'-'.sprintf('%02d',($request->pso_date['month'])).'-'.sprintf('%02d',($request->pso_date['day']));
                        $group_det->pso_date = date('Y-m-d',strtotime($pso_date)); 
                    }
					$group_det->blaw_number = isset($request->blaw_number) ? ($request->blaw_number) : 0;
                    if(isset($request->blaw_date)) {
                        $blaw_date = ($request->blaw_date['year']).'-'.sprintf('%02d',($request->blaw_date['month'])).'-'.sprintf('%02d',($request->blaw_date['day']));
                        $group_det->blaw_date = date('Y-m-d',strtotime($blaw_date)); 
                    }
					$group_det->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0;
					$group_det->company_chit = isset($request->company_chit) ? ($request->company_chit) : 0;
					$group_det->group_base_penalty = isset($request->group_base_penalty) ? ($request->group_base_penalty) : 0;
					$group_det->group_base_bonus = isset($request->group_base_bonus) ? ($request->group_base_bonus) : 0;
					$group_det->group_base_interest = isset($request->group_base_interest) ? ($request->group_base_interest) : 0;
					$group_det->group_base_dividend_cancel_days = isset($request->group_base_dividend_cancel_days) ? ($request->group_base_dividend_cancel_days) : 0;
					$group_det->group_penalty_days = isset($request->group_penalty_days) ? ($request->group_penalty_days) : 0;
					$group_det->group_bonus_days = isset($request->group_bonus_days) ? ($request->group_bonus_days) : 0;
                    $group_det->group_base_incentive_percentage = isset($request->group_base_incentive_percentage) ? ($request->group_base_incentive_percentage) : 0;
                    if(isset($request->fd_renewal_date)) {
                        $fd_renewal_date = ($request->fd_renewal_date['year']).'-'.sprintf('%02d',($request->fd_renewal_date['month'])).'-'.sprintf('%02d',($request->fd_renewal_date['day']));
                        $group_det->fd_renewal_date = date('Y-m-d',strtotime($fd_renewal_date)); 
                    }
                    if(isset($request->commit_date)) {
                        $commit_date = ($request->commit_date['year']).'-'.sprintf('%02d',($request->commit_date['month'])).'-'.sprintf('%02d',($request->commit_date['day']));
                        $group_det->commit_date = date('Y-m-d',strtotime($commit_date)); 
                    }
					$group_det->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($group_det->save()) {
						$update_group = new UpdateGroup();
	                    $update_group->tenant_id = isset($group_det->tenant_id) ? ($group_det->tenant_id) : 0;
	                    $update_group->branch_id = isset($group_det->branch_id) ? ($group_det->branch_id) : 0;
	                    $update_group->group_type = isset($group_det->group_type) ? ($group_det->group_type) : 0;
	                    $update_group->group_name = isset($group_det->group_name) ? ($group_det->group_name) : 0;
	                    $update_group->scheme_id = isset($group_det->scheme_id) ? ($group_det->scheme_id) : 0;
	                    $update_group->edit_date = date('Y-m-d');
	                    $update_group->group_id = isset($group_det->id) ? ($group_det->id) : 0;
	                    $update_group->auction_date = isset($group_det->auction_date) ? ($group_det->auction_date) : 0;
	                    $update_group->auction_time = isset($group_det->auction_time) ? ($group_det->auction_time) : 0;
	                    $update_group->starting_date = isset($group_det->starting_date) ? ($group_det->starting_date) : "0000-00-00";
	                    $update_group->first_due_date = isset($group_det->first_due_date) ? ($group_det->first_due_date) : "0000-00-00";                        
	                    $update_group->commission_percent = isset($group_det->commission_percent) ? ($group_det->commission_percent) : '';
	                    $update_group->fd_number = isset($group_det->fd_number) ? ($group_det->fd_number) : '';
	                    $update_group->total_fd = isset($group_det->total_fd) ? ($group_det->total_fd) : '';
	                    $update_group->company_fd = isset($group_det->company_fd) ? ($group_det->company_fd) : '';
	                    $update_group->rate_of_interest = isset($group_det->rate_of_interest) ? ($group_det->rate_of_interest) : '';
	                    $update_group->fd_date = isset($group_det->fd_date) ? ($group_det->fd_date) : "0000-00-00";  
	                    $update_group->fd_rate = isset($group_det->fd_rate) ? ($group_det->fd_rate) : 0;
	                    $update_group->maturity_amount = isset($group_det->maturity_amount) ? ($group_det->maturity_amount) : '';
	                    $update_group->fd_bank_id = isset($group_det->fd_bank_id) ? ($group_det->fd_bank_id) : '';
	                    $update_group->fd_branch = isset($group_det->fd_branch) ? ($group_det->fd_branch) : '';
	                    $update_group->pso_number = isset($group_det->pso_number) ? ($group_det->pso_number) : '';
	                    $update_group->pso_date = isset($group_det->pso_date) ? ($group_det->pso_date) : "0000-00-00";  
	                    $update_group->blaw_number = isset($group_det->blaw_number) ? ($group_det->blaw_number) : 0;
	                    $update_group->blaw_date = isset($group_det->blaw_date) ? ($group_det->blaw_date) : "0000-00-00";  
	                    $update_group->cheque_no = isset($group_det->cheque_no) ? ($group_det->cheque_no) : 0;
	                    $update_group->company_chit = isset($group_det->company_chit) ? ($group_det->company_chit) : 0;
	                    $update_group->group_base_penalty = isset($group_det->group_base_penalty) ? ($group_det->group_base_penalty) : 0;
	                    $update_group->group_base_bonus = isset($group_det->group_base_bonus) ? ($group_det->group_base_bonus) : 0;
	                    $update_group->group_base_interest = isset($group_det->group_base_interest) ? ($group_det->group_base_interest) : 0;
	                    $update_group->group_base_dividend_cancel_days = isset($group_det->group_base_dividend_cancel_days) ? ($group_det->group_base_dividend_cancel_days) : 0;
	                    $update_group->group_penalty_days = isset($group_det->group_penalty_days) ? ($group_det->group_penalty_days) : 0;
	                    $update_group->group_bonus_days = isset($group_det->group_bonus_days) ? ($group_det->group_bonus_days) : 0;
	                    $update_group->group_base_incentive_percentage = isset($group_det->group_base_incentive_percentage) ? ($group_det->group_base_incentive_percentage) : 0;
	                    $update_group->fd_renewal_date = isset($group_det->fd_renewal_date) ? ($group_det->fd_renewal_date) : "0000-00-00";  
	                     $update_group->commit_date = isset($group_det->commit_date) ? ($group_det->commit_date) : "0000-00-00";
	                     $update_group->created_by = isset($group_det->created_by) ? ($group_det->created_by) : 0;
	                     $update_group->status = isset($group_det->status) ? ($group_det->status) : 1;
	                    if($update_group->save())
	                    {
    						$response['status'] = "Success";
    						$response['msg'] = \Lang::get('api.success_group_updated');
    						$response['data'] = $group_det;
    						return response()->json($response,200);
                        }
                        else {
                            $response['status']="Error";
                            $response['msg']=\Lang::get('api.global_error');
                            return response()->json($response,401);
                        }
	                }
                }
                else {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.record_not_identified');
                    return response()->json($response, 200);
                }
            }
            catch(\Exception $e) { 
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }         
			   
	public function get_groups(Request $request) {
        try {
            //id,tenant_id
            $cond = isset($request->conditions) && is_array($request->conditions) ? $request->conditions : [];
            $groups = Group::where($cond)->orderBy('id','desc')->get();
            foreach($groups as $group) {
                $group->starting_date = ($group->starting_date!=0) ? date('d/m/Y',strtotime($group->starting_date)) : '';
                $group->first_due_date = ($group->first_due_date!=0) ? date('d/m/Y',strtotime($group->first_due_date)) : '';
                $group->fd_date = ($group->fd_date!=0) ? date('d/m/Y',strtotime($group->fd_date)) : '';
                $group->pso_date = ($group->pso_date!=0) ? date('d/m/Y',strtotime($group->pso_date)) : '';
                $group->blaw_date = ($group->blaw_date!=0) ? date('d/m/Y',strtotime($group->blaw_date)) : '';
                $group->fd_renewal_date = ($group->fd_renewal_date!=0) ? date('d/m/Y',strtotime($group->fd_renewal_date)) : '';
				$group->scheme_format = $group->group_name;
                $group->commit_date = ($group->commit_date!=0) ? date('d/m/Y',strtotime($group->commit_date)) : '';
                $group->is_filled = ($group->is_filled==0) ? 'Yes' : 'No';
                $group->group_status = ($group->group_status==0) ? 'Not Closed' : 'Closed';
                $group->is_first_auction_complete = ($group->is_first_auction_complete==0) ? 'No' : 'Yes';
                $group->commission_percent = round($group->commission_percent);
                $group->total_fd = round($group->total_fd);
                $group->company_fd = round($group->company_fd);
                $group->rate_of_interest = round($group->rate_of_interest);
                $group->fd_rate = round($group->fd_rate);
                $group->maturity_amount = round($group->maturity_amount);
                $group->tenant_info = isset($group->tenant_det) ? ($group->tenant_det) : [];
                $group->branch_info = isset($group->branch_det) ? ($group->branch_det) : [];
                $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                if($group->scheme_info) { 
                    $group->scheme_info->chit_value = round($group->scheme_info->chit_value);
                    $group->scheme_info->scheme_format = isset($group->scheme_info->chit_value) ? (round($group->scheme_info->chit_value).'('.$group->scheme_info->no_of_months.' M)') : '';
                }
                $group->bank_info = isset($group->bank_det) ? $group->bank_det : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $groups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	public function list_groups(Request $request)
{
    try {

        $cond = is_array($request->conditions) ? $request->conditions : [];

        $tenantId = $cond['tenant_id'] ?? $request->tenant_id ?? null;
        $logId    = $request->log_id ?? 0;

        $groups = collect();

        // ✅ Tenant 47 Special Logic
        if ($tenantId == 47) {

            if ($logId == 0) {

                $groups = Group::where($cond)->orderBy('id', 'desc')->get();

            } else {

                $roleUser = User::where('employee_id', $logId)->first();

                $roleTypeId = null;

                if ($roleUser) {
                    $roleTypeId = Role::where('id', $roleUser->role_id)->value('role_type_id');
                }

                if ($roleTypeId == 4 && $roleUser) {

                    $groups = Group::where('branch_id', $roleUser->branch_id)
                        ->orderBy('id', 'desc')->get();

                } elseif ($roleTypeId == 1) {

                    $groups = Group::orderBy('id', 'desc')->get();

                } else {

                    $groups = Group::where('branch_id', 1)
                        ->orderBy('id', 'desc')->get();
                }
            }

        } else {

            if (!empty($tenantId)) {
                $groups = Group::where('tenant_id', $tenantId)
                    ->orderBy('id', 'desc')->get();
            } else {
                $groups = Group::orderBy('id', 'desc')->get();
            }
        }

        // ✅ Data Formatting
        $i = 1;

        foreach ($groups as $group) {

            $group->sno = $i++;

            $group->starting_date = $group->starting_date
                ? date('d/m/Y', strtotime($group->starting_date))
                : '';

            $group->tenant_info = $group->tenant_det ?? [];
            $group->branch_info = $group->branch_det ?? [];
            $group->scheme_info = $group->scheme_det ?? [];
            $group->bank_info   = $group->bank_det ?? [];

            // ✅ Scheme formatting
            if (!empty($group->scheme_info)) {
                $group->scheme_info->chit_value = round($group->scheme_info->chit_value);

                $group->scheme_info->scheme_format =
                    $group->scheme_info->chit_value . '(' .
                    $group->scheme_info->no_of_months . ' M)' .
                    '(' . $group->scheme_info->chit_letter . ')';
            }

            // ✅ Delete check
            $groupdetails = ChitDetail::where('group_id', $group->id)
                ->where('customer_id', '!=', 1)
                ->count();

            $group->is_delete = $groupdetails > 0 ? 1 : 0;

            // ✅ Status
            $group->group_status = ($group->group_status == 0)
                ? 'Non-Closed'
                : 'Closed';

            $group->group_pending_ticket = $group->group_pending_ticket ?? 0;
        }

        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => $groups
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage() // 🔥 show real error (for debugging)
        ], 500); // ❌ don't use 401 here
    }
}

	public function delete_group(Request $request) {
		try {
            if(isset($request->id)) {
                $group = Group::find($request->id); 
                $group->deleted_by = $request->deleted_by;
                $group->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($group->save()) {
                    $group->delete();

                    $chitvalue=ChitDetail::where('group_id','=',$request->id)->where('customer_id','=',1)->get();
                    if(count($chitvalue)>0){
                        $chitvalue[0]->deleted_by = $request->deleted_by;
                        $chitvalue[0]->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                        $chitvalue[0]->save();
                    }
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_group_deleted');
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            else {
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.record_not_identified');
                return response()->json($response, 200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function update_status(Request $request) {
        try {
            if(isset($request->id)) {
                $group_det = Group::find($request->id); 
                $group_det->status = ($group_det->status) ? 0 : 1;
                if($group_det->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_status_updated');
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            else {
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.record_not_identified');
                return response()->json($response, 200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function fill_group(Request $request) {
        try {
            $cond = $request->conditions;
            $dummy_customer_id = 0;
            $dummy_customer = Customer::where(array('tenant_id'=>$cond['tenant_id'],'branch_id'=>0))->get(); 
            if(count($dummy_customer)==0) {
                $dummy_customer = new Customer();
                $dummy_customer->tenant_id = $cond['tenant_id'];
                $dummy_customer->branch_id = 0;
                $dummy_customer->customer_code = 'DUMMY';
                $dummy_customer->name = 'DUMMY';
                $dummy_customer->save();
                $dummy_customer_id = $dummy_customer->id;
            }
            else {
                $dummy_customer_id = $dummy_customer[0]->id;
            } 
            $group = Group::find($cond['group_id']); 
            $vacant_count = $group->group_pending_ticket;
            if($vacant_count > 0) { 
                $total_members = isset($group->scheme_det) ? $group->scheme_det->members : 0;
                $total_tickets = $total_members*2;
                $ticket_list = ChitDetail::where(array('group_id'=>$cond['group_id']))->get();
                $assigned_list = [];
                $unassigned_list = [];
                foreach($ticket_list as $ticket) {
                    array_push($assigned_list,$ticket->ticket_no);
                }   
                for($i=2;$i<=$total_tickets;$i++) { 
                    if(!(in_array($i,$assigned_list))) {
                        array_push($unassigned_list,$i);
                    }  
                } 
                $j=0;
                while($vacant_count>0) {
                    $enrol = new ChitDetail();
                    $enrol->tenant_id = $cond['tenant_id'];
                    $enrol->branch_id = $cond['branch_id'];
                    $enrol->customer_id = $dummy_customer_id;
                    $enrol->group_id = $cond['group_id']; 
                    $enrol->ticket_no = $unassigned_list[$j];
                    $enrol->prized_status = 0;
                    $enrol->eligibility_status = 1;
                    $enrol->chit_intimation_status = 0;
                    $enrol->chit_type = 1;
                    $enrol->enrolment_type = 1; 
                    $enrol->date_introduced= date('Y-m-d');
                    if($vacant_count>=1) {
                        //full ticket
                        $enrol->ticket_type = 1;
                        $vacant_count = ($vacant_count-1);
                    }
                    else {
                        //half ticket
                        $enrol->ticket_type = '0.5';
                        $vacant_count = 0;
                    }
                    $enrol->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                    $enrol->status = 1;  
                    $enrol->save(); 
                    $j++;
                } 
                // pending ticket calculation
                $total_ticket_count = ChitDetail::where(array('group_id'=>$cond['group_id']))->sum('ticket_type');
                    $no_of_members = 0;
                    if(isset($group->scheme_det)) { 
					    $no_of_members = isset($group->scheme_det->members) ? ($group->scheme_det->members) : 0;
                    }
					//$total_ticket_count = $group_pend_full_ticket - $group_pend_half_ticket;
                    $pending_ticket_count = $no_of_members - $total_ticket_count;
                $group->group_pending_ticket = $pending_ticket_count;
                if($pending_ticket_count>0)
                {
                    $group->is_filled = '1'; 
                } else {
                    $group->is_filled = '0'; 
                }
                $group->save(); 
                if($pending_ticket_count==0) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_group_filled');
                    return response()->json($response,200);
                }
                else {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.global_error');
                    return response()->json($response, 401);
                }
            }
            else {
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.no_vacant_count');
                return response()->json($response, 401);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function fd_report(Request $request) {
        try {
            $cond = [];
            $cond1 =[];
            $start_date="";
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}						
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id; 
			}
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date)); 
				$cond['starting_date'] = $start_date; 
				
        	}
            if($start_date=='') { 
            	if($request->branch_id > 0){
					
					$groups=Group::where($cond)->get();
					
				}
				if($request->branch_id ==0)
				{
					$groups=Group::where($cond)->get();
				}                
            }
            else { 
                

            		$groups=Group::where($cond)->get();
            	
            }  
                
                $i=1;  
            foreach($groups as $group) 
            {
				$group->sno = $i;
                $group->fd_start_date = ($group->starting_date!=0) ? date('d/m/Y',strtotime($group->starting_date)) : '';
                $group->first_due_date = ($group->first_due_date!=0) ? date('d/m/Y',strtotime($group->first_due_date)) : '';
                $group->fd_date = ($group->fd_date!=0) ? date('d/m/Y',strtotime($group->fd_date)) : '';
                $group->pso_date = ($group->pso_date!=0) ? date('d/m/Y',strtotime($group->pso_date)) : '';
                $group->blaw_date = ($group->blaw_date!=0) ? date('d/m/Y',strtotime($group->blaw_date)) : '';
                $group->fd_renewal_date = ($group->fd_renewal_date!=0) ? date('d/m/Y',strtotime($group->fd_renewal_date)) : '';
                $group->commit_date = ($group->commit_date!=0) ? date('d/m/Y',strtotime($group->commit_date)) : '';
                $group->is_filled = ($group->is_filled==0) ? 'Yes' : 'No';
                $group->group_status = ($group->group_status==0) ? 'Not Closed' : 'Closed';
                $group->is_first_auction_complete = ($group->is_first_auction_complete==0) ? 'No' : 'Yes';
                $group->commission_percent = round($group->commission_percent);
                $group->total_fd_amount = round($group->total_fd);
                $group->company_fd_amount = round($group->company_fd);
                $group->rate_of_interest = round($group->rate_of_interest);
                $group->fd_rate = round($group->fd_rate);
                $group->maturity_amount = round($group->maturity_amount);
                $group->tenant_info = isset($group->tenant_det) ? ($group->tenant_det) : [];
                $group->branch_name = isset($group->branch_det) ? ($group->branch_det->branch_name) : [];
                $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                if($group->scheme_info) { 
                    $group->chit_value = round($group->scheme_info->chit_value);
                    $group->scheme_info->scheme_format = isset($group->scheme_info->chit_value) ? (round($group->scheme_info->chit_value).'('.$group->scheme_info->no_of_months.' M)') : '';
                }
                $group->bank_info = isset($group->bank_det) ? $group->bank_det : [];
				$i++;
            }	
            
           		   
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $groups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function group_starting_report(Request $request) {
        try {
            $cond = [];
            $cond1 =[];
            $start_date="";
			$end_date="";
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}						
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id; 
			}
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date));
				
        	}
			if(isset($request->end_date)) { 
        		$e_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day']));
        		$end_date = date('Y-m-d',strtotime($e_date));
				
        	}
            if($start_date=='') { 
            	if($request->branch_id > 0){
					
					$groups=Group::where($cond)->get();
					
				}
				if($request->branch_id ==0)
				{
					$groups=Group::where($cond)->whereBetween('starting_date',[$start_date,$end_date])->get();
				}                
            }
            else { 
                

            		$groups=Group::where($cond)->whereBetween('starting_date',[$start_date,$end_date])->get();
            	
            }  
                
                $i=1;  
            foreach($groups as $group) 
            {
				$group->sno = $i;
                $group->pso_date = ($group->pso_date!=0) ? date('d/m/Y',strtotime($group->pso_date)) : '';
                $group->blaw_date = ($group->blaw_date!=0) ? date('d/m/Y',strtotime($group->blaw_date)) : '';
                $group->fd_renewal_date = ($group->fd_renewal_date!=0) ? date('d/m/Y',strtotime($group->fd_renewal_date)) : '';
                $group->commit_date = ($group->commit_date!=0) ? date('d/m/Y',strtotime($group->commit_date)) : '';
                $group->is_filled = ($group->is_filled==0) ? 'Yes' : 'No';
                $group->group_status = ($group->group_status==0) ? 'Not Closed' : 'Closed';
                $group->is_first_auction_complete = ($group->is_first_auction_complete==0) ? 'No' : 'Yes';
                $group->commission_percent = round($group->commission_percent);
                $group->total_fd_amount = round($group->total_fd);
                $group->company_fd_amount = round($group->company_fd);
                $group->rate_of_interest = round($group->rate_of_interest);
                $group->fd_rate = round($group->fd_rate);
                $group->maturity_amount = round($group->maturity_amount);
                $group->tenant_info = isset($group->tenant_det) ? ($group->tenant_det) : [];
                $group->branch_name = isset($group->branch_det) ? ($group->branch_det->branch_name) : [];
                $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                if($group->scheme_info) { 
                    $group->chit_value = round($group->scheme_info->chit_value);
                    $group->scheme_info->scheme_format = isset($group->scheme_info->chit_value) ? (round($group->scheme_info->chit_value).'('.$group->scheme_info->no_of_months.' M)') : '';
                }
                $group->bank_info = isset($group->bank_det) ? $group->bank_det : [];
				$i++;
            }	
            
           		   
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $groups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function group_termination_report(Request $request) {
        try {
            $cond = [];
            $cond1 =[];
            $start_date="";
			$end_date="";
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}						
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id; 
			}
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date));
				
        	}
			if(isset($request->end_date)) { 
        		$e_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day']));
        		$end_date = date('Y-m-d',strtotime($e_date));
				
        	}
            if($start_date=='') { 
            	if($request->branch_id > 0){
					
					$groups=Group::where($cond)->get();
					
				}
				if($request->branch_id ==0)
				{
					$groups=Group::where($cond)->whereBetween('termination_date',[$start_date,$end_date])->get();
				}                
            }
            else { 
                

            		$groups=Group::where($cond)->whereBetween('termination_date',[$start_date,$end_date])->get();
            	
            }  
                
                $i=1;  
            foreach($groups as $group) 
            {
				$group->sno = $i;
                $group->pso_date = ($group->pso_date!=0) ? date('d/m/Y',strtotime($group->pso_date)) : '';
                $group->blaw_date = ($group->blaw_date!=0) ? date('d/m/Y',strtotime($group->blaw_date)) : '';
                $group->fd_renewal_date = ($group->fd_renewal_date!=0) ? date('d/m/Y',strtotime($group->fd_renewal_date)) : '';
                $group->commit_date = ($group->commit_date!=0) ? date('d/m/Y',strtotime($group->commit_date)) : '';
                $group->is_filled = ($group->is_filled==0) ? 'Yes' : 'No';
                $group->group_status = ($group->group_status==0) ? 'Not Closed' : 'Closed';
                $group->is_first_auction_complete = ($group->is_first_auction_complete==0) ? 'No' : 'Yes';
                $group->commission_percent = round($group->commission_percent);
                $group->total_fd_amount = round($group->total_fd);
                $group->company_fd_amount = round($group->company_fd);
                $group->rate_of_interest = round($group->rate_of_interest);
                $group->fd_rate = round($group->fd_rate);
                $group->maturity_amount = round($group->maturity_amount);
                $group->tenant_info = isset($group->tenant_det) ? ($group->tenant_det) : [];
                $group->branch_name = isset($group->branch_det) ? ($group->branch_det->branch_name) : [];
                $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                if($group->scheme_info) { 
                    $group->chit_value = round($group->scheme_info->chit_value);
                    $group->scheme_info->scheme_format = isset($group->scheme_info->chit_value) ? (round($group->scheme_info->chit_value).'('.$group->scheme_info->no_of_months.' M)') : '';
                }
                $group->bank_info = isset($group->bank_det) ? $group->bank_det : [];
				$i++;
            }	
            
           		   
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $groups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
