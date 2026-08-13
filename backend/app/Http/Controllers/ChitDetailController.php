<?php
namespace App\Http\Controllers;

use App\Models\ChitDetail;
use App\Models\Customer;
use App\Models\BiddingDetail;
use App\Models\City;
use App\Models\AdvanceReceipt;
use App\Models\AgentCommissionPayment;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\AccountHead;
use App\Models\AccountExpense;
use App\Models\Receipt;
use App\Models\ChitPledge;
use App\Models\Group;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\Sms;
use App\Models\SlabDue;
use App\Models\OtherCharge;
use App\Models\AuctionOnHoliday;
use App\Models\GiftIssue;
use App\Models\ChargeType;
use App\Models\Gift;
use App\Models\Branch;
use App\Models\AgentIncentivePayment;
use App\Models\CollectionAreaMapping;
use App\Models\CollectionArea;
use App\Models\CommitmentAuction;
use App\Models\BeforeEnrolReceipt;
use Illuminate\Http\Request;
use Validator;
use DateTime;
use DB;

class ChitDetailController extends Controller
{
    public function store_enrol(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required',
            'ticket_no' => 'required',
            'payment_type' => 'required',
            //'collection_area_id' => 'required',
            'collection_pin' => 'required',
            //'collection_agent_id' => 'required',
            'agent_type' => 'required',
            'date_introduced' => 'required',
            'ticket_type' => 'required',
            'enrolment_type' => 'required',
            'chit_type' => 'required',
           // 'customer_type' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $enrol = new ChitDetail();
				$enrol->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$enrol->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                //$enrol->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
                $enrol->customer_id = $customer_id;
                $enrol->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                $enrol->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : 0;
                $enrol->prized_status = isset($request->prized_status) ? ($request->prized_status) : 0;
                $enrol->eligibility_status = isset($request->eligibility_status) ? ($request->eligibility_status) : 0;
                $enrol->chit_intimation_status = isset($request->chit_intimation_status) ? ($request->chit_intimation_status) : '';
                $enrol->payment_type = isset($request->payment_type) ? ($request->payment_type) : 0;
                $enrol->collection_area_id = isset($request->collection_area_id) ? ($request->collection_area_id) : 0;
                $enrol->collection_pin = isset($request->collection_pin) ? ($request->collection_pin) : 0;
                $enrol->collection_agent_id = isset($request->collection_agent_id) ? ($request->collection_agent_id) : 0;
                $enrol->agent_type = isset($request->agent_type) ? ($request->agent_type) : 0;
                $enrol->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : 0;
                $enrol->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : 0;
				if($request->agent_type == 3) {
					$employee = Employee::where('id',$request->introducer_id)->pluck('commission');
					$commission_percentage = isset($employee[0]) ? ($employee[0]) : 0;	                    				
					$group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
					$scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
					$chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
					$total_business_agent_commission = ($chit_value * ( $commission_percentage / 100 ) );
					$balance_business_agent_commission = $total_business_agent_commission;
					$paid_business_agent_commission = 0;
					$enrol->total_business_agent_commission = $total_business_agent_commission;
					$enrol->balance_business_agent_commission = $balance_business_agent_commission;
					$enrol->paid_business_agent_commission = $paid_business_agent_commission;	
				}
				if($request->agent_type == 2) {
                    $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                    
                    $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }
                if($request->agent_type == 1) {
                    $employee = Customer::where('id',$request->introducer_id)->pluck('customer_incentive_percentage');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                    
                    $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }
                if($request->agent_type == 3) {
                    $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                    
                    $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }
               // $enrol->date_introduced = isset($request->date_introduced) ? ($request->date_introduced) : '0000-00-00';
                if(isset($request->date_introduced)) {
					
                    $s_date = ($request->date_introduced['year']).'-'.sprintf('%02d',($request->date_introduced['month'])).'-'.sprintf('%02d',($request->date_introduced['day'])); 
                    $enrol->date_introduced = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $enrol->date_introduced = '0000-00-00';
                } 
                $enrol->pending_amount = isset($request->pending_amount) ? ($request->pending_amount) : '0';
                $enrol->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                $enrol->penalty_amount = isset($request->penalty_amount) ? ($request->penalty_amount) : '0';
                $enrol->bonus_amount = isset($request->bonus_amount) ? ($request->bonus_amount) : '0';
                $enrol->advance_amount = isset($request->advance_amount) ? ($request->advance_amount) : '0';
                $enrol->completed_auction = isset($request->completed_auction) ? ($request->completed_auction) : '0';
                $enrol->current_due = isset($request->current_due) ? ($request->current_due) : '0';
                $enrol->next_due = isset($request->next_due) ? ($request->next_due) : '0';
                $enrol->pending_days = isset($request->pending_days) ? ($request->pending_days) : '0';
                $enrol->level = isset($request->level) ? ($request->level) : '0';
                $enrol->chit_type = isset($request->chit_type) ? ($request->chit_type) : '0';
                $enrol->customer_type = isset($request->customer_type) ? ($request->customer_type) : 0;
                $enrol->pending_installment = isset($request->pending_installment) ? ($request->pending_installment) : 0;               
                $enrol->pledge_status = isset($request->pledge_status) ? ($request->pledge_status) : 0;
                $enrol->discount_due = isset($request->discount_due) ? ($request->discount_due) : 0;
                $enrol->needed_amount = isset($request->needed_amount) ? ($request->needed_amount) : 0;
                $enrol->cancel_amount = isset($request->cancel_amount) ? ($request->cancel_amount) : 0;
                $enrol->return_amount = isset($request->return_amount) ? ($request->return_amount) : 0;
                $enrol->ticket_type = isset($request->ticket_type) ? ($request->ticket_type) : 0;
                $enrol->enrolment_type = isset($request->enrolment_type) ? ($request->enrolment_type) : 0;
                $enrol->bonus_percentage = isset($request->bonus_percentage) ? ($request->bonus_percentage) : 0;
                $enrol->penalty_percentage = isset($request->penalty_percentage) ? ($request->penalty_percentage) : 0;
                $enrol->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                $enrol->dividend_cancel = isset($request->dividend_cancel) ? ($request->dividend_cancel) : 0;
                $enrol->ticket_type = isset($request->ticket_type) ? ($request->ticket_type) : 0;
                $enrol->remarks = isset($request->remarks) ? ($request->remarks) : 0;
                $enrol->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $enrol->status = isset($request->status) ? ($request->status) : 1;
                if($enrol->save()) {
					$group = Group::find($request->group_id);
					// pending ticket calculation
					$group_pend_full_ticket = ChitDetail::where(array('group_id'=>$request->group_id))->sum('ticket_type');
                    $no_of_members = 0;
                    if(isset($group->scheme_det)) { 
					    $no_of_members = isset($group->scheme_det->members) ? ($group->scheme_det->members) : 0;
                    }
					if($request->tenant_id == 50){
						$no_of_members = $no_of_members +1;
					}else {
						$no_of_members =$no_of_members;
					}
                    $pending_ticket_count = $no_of_members - $group_pend_full_ticket;
					$group->group_pending_ticket = $pending_ticket_count;
					if($pending_ticket_count>0)
					{
						$group->is_filled = '1'; 
					} else {
						$group->is_filled = '0'; 
					}
					if($group->save()) {
    					$response['status'] = 'Success';
    					$response['msg'] = \Lang::get('api.success_enrol_added');
    					$response['data'] = $enrol;
    					return response()->json($response,200);
                    }
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }
				}
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
               $response['msg'] = \Lang::get('api.global_error');
                //$response['msg'] = $e;
                return response()->json($response, 401);
            }
        }
    }

    public function update_enrol(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required',
            'ticket_no' => 'required',
            'payment_type' => 'required',
            //'collection_area_id' => 'required',
            //'collection_pin' => 'required',
            //'collection_agent_id' => 'required',
            'agent_type' => 'required',
            'date_introduced' => 'required',
            'ticket_type' => 'required',
            'enrolment_type' => 'required',
            'chit_type' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$enrol = ChitDetail::find($request->id);
					$enrol->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $enrol->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $enrol->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                    $enrol->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                    $enrol->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : 0;
                    $enrol->prized_status = isset($request->prized_status) ? ($request->prized_status) : 0;
                    $enrol->chit_intimation_status = isset($request->chit_intimation_status) ? ($request->chit_intimation_status) : '';
                    $enrol->payment_type = isset($request->payment_type) ? ($request->payment_type) : 0;
                    $enrol->collection_area_id = isset($request->collection_area_id) ? ($request->collection_area_id) : 0;
                    $enrol->collection_pin = isset($request->collection_pin) ? ($request->collection_pin) : 0;
                    $enrol->collection_agent_id = isset($request->collection_agent_id) ? ($request->collection_agent_id) : 0;
                    $enrol->agent_type = isset($request->agent_type) ? ($request->agent_type) : 0;
                    $enrol->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : 0;
                    $enrol->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : 0;
                    if($request->agent_type == 3) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('commission');
                        $commission_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $total_business_agent_commission = ($chit_value * ( $commission_percentage / 100 ) );
                        $balance_business_agent_commission = $total_business_agent_commission;
                        $paid_business_agent_commission = 0;
                        $enrol->total_business_agent_commission = $total_business_agent_commission;
                        $enrol->balance_business_agent_commission = $balance_business_agent_commission;
                        $enrol->paid_business_agent_commission = $paid_business_agent_commission;   
                    }
                    if($request->agent_type == 2) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }
                    if($request->agent_type == 1) {
                        $employee = Customer::where('id',$request->introducer_id)->pluck('customer_incentive_percentage');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                    
                        $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }
                    if($request->agent_type == 3) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                    
                        $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }
                    
                    $enrol->date_introduced = isset($request->date_introduced) ? ($request->date_introduced) : '0000-00-00';
                    if(isset($request->date_introduced)) {
                        $s_date = ($request->date_introduced['year']).'-'.sprintf('%02d',($request->date_introduced['month'])).'-'.sprintf('%02d',($request->date_introduced['day'])); 
                        $enrol->date_introduced = date('Y-m-d',strtotime($s_date)); 
                    }
                    $enrol->pending_amount = isset($request->pending_amount) ? ($request->pending_amount) : '0';
                    $enrol->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                    $enrol->penalty_amount = isset($request->penalty_amount) ? ($request->penalty_amount) : '0';
                    $enrol->bonus_amount = isset($request->bonus_amount) ? ($request->bonus_amount) : '0';
                    $enrol->advance_amount = isset($request->advance_amount) ? ($request->advance_amount) : '0';
                    $enrol->completed_auction = isset($request->completed_auction) ? ($request->completed_auction) : '0';
                    $enrol->current_due = isset($request->current_due) ? ($request->current_due) : '0';
                    $enrol->next_due = isset($request->next_due) ? ($request->next_due) : '0';
                    $enrol->pending_days = isset($request->pending_days) ? ($request->pending_days) : '0';
                    $enrol->level = isset($request->level) ? ($request->level) : '0';
                    $enrol->chit_type = isset($request->chit_type) ? ($request->chit_type) : '0';
                    $enrol->customer_type = 1;
                    $enrol->pending_installment = isset($request->pending_installment) ? ($request->pending_installment) : 0;
                    $enrol->pledge_status = isset($request->pledge_status) ? ($request->pledge_status) : 0;
                    $enrol->discount_due = isset($request->discount_due) ? ($request->discount_due) : 0;
                    $enrol->needed_amount = isset($request->needed_amount) ? ($request->needed_amount) : 0;
                    $enrol->cancel_amount = isset($request->cancel_amount) ? ($request->cancel_amount) : 0;
                    $enrol->return_amount = isset($request->return_amount) ? ($request->return_amount) : 0;
                    $enrol->ticket_type = isset($request->ticket_type) ? ($request->ticket_type) : 0;
                    $enrol->bonus_percentage = isset($request->bonus_percentage) ? ($request->bonus_percentage) : 0;
                    $enrol->penalty_percentage = isset($request->penalty_percentage) ? ($request->penalty_percentage) : 0;
                    $enrol->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                    $enrol->dividend_cancel = isset($request->dividend_cancel) ? ($request->dividend_cancel) : 0;
                    $enrol->ticket_type = isset($request->ticket_type) ? ($request->ticket_type) : 0;
                    $enrol->enrolment_type = isset($request->enrolment_type) ? ($request->enrolment_type) : 0;                
                    $enrol->remarks = isset($request->remarks) ? ($request->remarks) : 0;
					$enrol->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($enrol->save()) { 
                        $group = Group::find($request->group_id);
                        // pending ticket calculation
                        $group_pend_full_ticket = ChitDetail::where(array('group_id'=>$request->group_id,'ticket_type'=>'1'))->count();
                        $group_pend_half_ticket = ChitDetail::where(array('group_id'=>$request->group_id,'ticket_type'=>'2'))->count();
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
                        if($group->save()) {
							$response['status'] = "Success";
							$response['msg'] = \Lang::get('api.success_enrol_updated');
							$response['data'] = $enrol;
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
               
			   
	public function get_enrolls(Request $request) {
		
        try {
            //id,tenant_id
            $cond = $request->conditions; //$response["l"] = $cond;
            $enrolls = ChitDetail::where($cond)->orderBy('id','desc')->get();
			if($enrolls) {
                $i=1;
            foreach($enrolls as $enrol) {
				
				  $enrol->sno = $i;
                  $enrol->ticket_type_percentage = $enrol->ticket_type ;
                  if($enrol->ticket_type_percentage == "1.00"){
                    $enrol->ticket_type_percentage = "1";
                  }
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
				$enrol->branch_name = isset($enrol->branch_det->branch_name) ? ($enrol->branch_det->branch_name) : '';
                //$enrol->name = isset($enrol->customer_det->name) ? ($enrol->customer_det->name) : []; 
                $enrol->customer_info =  isset($enrol->customer_det) ? ($enrol->customer_det) : []; 
				$enrol->name = $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name  . ' | ' . $enrol->customer_det->customer_code;
				$enrol->customer_info->name =  $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name  . ' | ' . $enrol->customer_det->customer_code;
				$enrol->mobile_no = isset($enrol->customer_det->mobile_no) ? ($enrol->customer_det->mobile_no) : ''; 
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->group_name = isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : '';
				$commencement_date = Group::where('id',$enrol->group_id)->pluck('starting_date');
				$enrol->commencement_date = date('d-m-Y' ,strtotime($commencement_date[0]));
				//$enrol->commencement_date = isset($enrol->group_det->group_name) ? ($enrol->group_det->blaw_date) : '';
                $enrol->is_delete=0;
                $biddingvalue=BiddingDetail::where('enrollment_id','=',$enrol->id)->get();
                $advancevalue=AdvanceReceipt::where('enrollment_id','=',$enrol->id)->get();
                $payvalue=Payment::where('enrollment_id','=',$enrol->id)->get();
                $payreceipt=Receipt::where('enrollment_id','=',$enrol->id)->get();
                if(count($biddingvalue)>0 || count($advancevalue)>0 || count($payvalue)>0 || count($payreceipt)>0){
                    $enrol->is_delete=0;
                }
                $enrol->scheme_info = [];
                if(isset($enrol->group_det)) {
                    $scheme_det = Scheme::find($enrol->group_det->scheme_id);
                    $enrol->scheme_info = isset($scheme_det) ? ($scheme_det) : [];
                }
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $enrol->collection_area_info = isset($enrol->collection_area_det) ? ($enrol->collection_area_det) : [];
                $enrol->collection_area = isset($enrol->collection_area_det) ? ($enrol->collection_area_det->collection_area) : '-';
                $enrol->date_introduced = isset($enrol->date_introduced) ? date('d/m/Y',strtotime($enrol->date_introduced)) : "0000-00-00"; 
                $enrol->introducer_full_name = '';
                $enrol->introducer_info = [];
                if($enrol->agent_type==1) { 
                    //customer
                    $introducer_det = Customer::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                    //employee
                    $introducer_det = Employee::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $enrol->introducer_info = isset($enrol->introducer_id) ? ($enrol->introducer_id) : [];
                   $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : []; 
                }
                $enrol->enrol_name = "";
                $enrol->chit_value = 0;
                if($enrol->chit_type != 3)
                {
                    if(isset($enrol->group_det)) {
                        $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no;
                         $enrol->chit_value = isset($enrol->group_det->scheme_det) ? (strval(round($enrol->group_det->scheme_det->chit_value))) : 0;
						 $enrol->no_months = isset($enrol->group_det->scheme_det) ? (strval(round($enrol->group_det->scheme_det->no_of_months))) : 0;
                    }
                }
                if($enrol->chit_type == 3)
                {
                    $slab_types = SlabDue::where('id',$enrol->slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrol->ar_no ?? null);
                    $enrol->enrol_name = "GROUP NAME:Commitment | SLAB NAME :".$slab_name;
                    $enrol->chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
                }
				 $i++;
            }
			
                }
				$issue_date = date('d-m-Y') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
			$response['date'] = $issue_date;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_enrolls_for_receipts(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $enrolls = ChitDetail::where($cond)->where('chit_type','!=',3)->orderBy('id','desc')->get();
            foreach($enrolls as $enrol) {
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : []; 
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->scheme_info = [];
                if(isset($enrol->group_det)) {
                    $scheme_det = Scheme::find($enrol->group_det->scheme_id);
                    $enrol->scheme_info = isset($scheme_det) ? ($scheme_det) : [];
                }
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $enrol->date_introduced = isset($enrol->date_introduced) ? date('d/m/Y',strtotime($enrol->date_introduced)) : "0000-00-00"; 
                $enrol->introducer_full_name = '';
                $enrol->introducer_info = [];
                if($enrol->agent_type==1) { 
                    //customer
                    $introducer_det = Customer::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                    //employee
                    $introducer_det = Employee::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $enrol->introducer_info = isset($enrol->introducer_id) ? ($enrol->introducer_id) : [];
                   $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : []; 
                }
                $enrol->enrol_name = "";                
                if(isset($enrol->group_det)) {
                    $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no;
                }        
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_enrolls_for_gaurantor(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; //$response["l"] = $cond;
            $enrolls = ChitDetail::where($cond)->orderBy('id','desc')->get();
            // ->where('chit_type','!=',3)
            foreach($enrolls as $enrol) {
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : []; 
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->scheme_info = [];
                if(isset($enrol->group_det)) {
                    $scheme_det = Scheme::find($enrol->group_det->scheme_id);
                    $enrol->scheme_info = isset($scheme_det) ? ($scheme_det) : [];
                }
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $enrol->date_introduced = isset($enrol->date_introduced) ? date('d/m/Y',strtotime($enrol->date_introduced)) : "0000-00-00"; 
                $enrol->introducer_full_name = '';
                $enrol->introducer_info = [];
                if($enrol->agent_type==1) { 
                    //customer
                    $introducer_det = Customer::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                    //employee
                    $introducer_det = Employee::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $enrol->introducer_info = isset($enrol->introducer_id) ? ($enrol->introducer_id) : [];
                   $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : []; 
                }
                $enrol->enrol_name = "";
                if($enrol->chit_type != 3)
                {
                    if(isset($enrol->group_det)) {
                        $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no;
                    }
                }
                if($enrol->chit_type == 3)
                {
                    $slab_types = SlabDue::where('id',$enrol->slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrol->ar_no ?? null);
                    $enrol->enrol_name = "GROUP NAME:Commitment | SLAB NAME :".$slab_name;
                    
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    
	 
	public function list_enrolls(Request $request) 
    {
        try { 
            if(isset($request->tenant_id)) {
                $enrolls = ChitDetail::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $enrolls = ChitDetail::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($enrolls as $enrol) {
                $enrol->sno = $i;
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : [];
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function delete_enrol(Request $request) {
		try {
            if(isset($request->id)) {
                $enrol = ChitDetail::find($request->id); 
                $enrol->deleted_by = $request->deleted_by;
                $enrol->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($enrol->save()) {
                    $enrol->delete();
                    $group = Group::find($enrol->group_id);
                    // pending ticket calculation
                    $group_pend_full_ticket = ChitDetail::where(array('group_id'=>$enrol->group_id))->sum('ticket_type');
                   
                    $no_of_members = 0;
                    if(isset($group->scheme_det)) { 
                        $no_of_members = isset($group->scheme_det->members) ? ($group->scheme_det->members) : 0;
                    }
					$no_of_members = $no_of_members +1;
                    $pending_ticket_count = $no_of_members - $group_pend_full_ticket;
                    $group->group_pending_ticket = $pending_ticket_count;
                    if($pending_ticket_count>0)
                    {
                        $group->is_filled = '1'; 
                    } else {
                        $group->is_filled = '0'; 
                    }
                    if($group->save()) {
                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_enrol_deleted');
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

    public function cancel_enrol(Request $request) {
		try {
            if(isset($request->id)) {
                $enrol = ChitDetail::find($request->id); 
                $enrol->status = $request->status;
                if($enrol->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_enrol_canceled');
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
                $enrol = ChitDetail::find($request->id); 
                $enrol->status = ($enrol->status) ? 0 : 1;
                if($enrol->save()) {
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

    public function store_dummy_subscriber(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'payment_type' => 'required',
            'collection_area_id' => 'required',
            'collection_pin' => 'required',
            'collection_agent_id' => 'required',
            'agent_type' => 'required',
            'date_introduced' => 'required',
            'introducer_name' => 'required',
            'introducer_id' => 'required',
            'ticket_type' => 'required',
            'enrolment_type' => 'required',
            'customer_type' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $enrol = new ChitDetail();
                $enrol->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $enrol->prized_status = isset($request->prized_status) ? ($request->prized_status) : 0;
                $enrol->eligibility_status = 1;
                $enrol->chit_intimation_status = isset($request->chit_intimation_status) ? ($request->chit_intimation_status) : '';
                $enrol->payment_type = isset($request->payment_type) ? ($request->payment_type) : 0;
                $enrol->collection_area_id = isset($request->collection_area_id) ? ($request->collection_area_id) : 0;
                $enrol->collection_pin = isset($request->collection_pin) ? ($request->collection_pin) : 0;
                $enrol->collection_agent_id = isset($request->collection_agent_id) ? ($request->collection_agent_id) : 0;
                $enrol->agent_type = isset($request->agent_type) ? ($request->agent_type) : 0;
                $enrol->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : 0;
                $enrol->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : 0;
                $enrol->date_introduced = isset($request->date_introduced) ? ($request->date_introduced) : '0000-00-00';
                $enrol->pending_amount = isset($request->pending_amount) ? ($request->pending_amount) : '0';
                $enrol->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                $enrol->penalty_amount = isset($request->penalty_amount) ? ($request->penalty_amount) : '0';
                $enrol->bonus_amount = isset($request->bonus_amount) ? ($request->bonus_amount) : '0';
                $enrol->advance_amount = isset($request->advance_amount) ? ($request->advance_amount) : '0';
                $enrol->completed_auction = isset($request->completed_auction) ? ($request->completed_auction) : '0';
                $enrol->current_due = isset($request->current_due) ? ($request->current_due) : '0';
                $enrol->next_due = isset($request->next_due) ? ($request->next_due) : '0';
                $enrol->pending_days = isset($request->pending_days) ? ($request->pending_days) : '0';
                $enrol->level = isset($request->level) ? ($request->level) : '0';
                $enrol->chit_type = isset($request->chit_type) ? ($request->chit_type) : '0';
                $enrol->customer_type = isset($request->customer_type) ? ($request->customer_type) : 0;
                $enrol->pending_installment = isset($request->pending_installment) ? ($request->pending_installment) : 0;
                $enrol->agent_incentive = isset($request->agent_incentive) ? ($request->agent_incentive) : 0;
                $enrol->pledge_status = isset($request->pledge_status) ? ($request->pledge_status) : 0;
                $enrol->discount_due = isset($request->discount_due) ? ($request->discount_due) : 0;
                $enrol->cancel_amount = isset($request->cancel_amount) ? ($request->cancel_amount) : 0;
                $enrol->return_amount = isset($request->return_amount) ? ($request->return_amount) : 0;
                $enrol->ticket_type = isset($request->ticket_type) ? ($request->ticket_type) : 0;
                $enrol->enrolment_type = isset($request->enrolment_type) ? ($request->enrolment_type) : 0;
                $enrol->bonus_percentage = isset($request->bonus_percentage) ? ($request->bonus_percentage) : 0;
                $enrol->penalty_percentage = isset($request->penalty_percentage) ? ($request->penalty_percentage) : 0;
                $enrol->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                $enrol->dividend_cancel = isset($request->dividend_cancel) ? ($request->dividend_cancel) : 0;
                $enrol->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $enrol->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $enrol->status = isset($request->status) ? ($request->status) : 1;
                if($enrol->save()) {
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_dummy_enrol_added');
					$response['data'] = $enrol;
					return response()->json($response,200);
				}
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }
    
    public function get_unassigned_tickets(Request $request) {
        try {
            $cond = $request->conditions; 
            $ticket_list = ChitDetail::where(array('group_id'=>$cond['group_id']))->get();
            $assigned_list = [];
            $unassigned_list = [];
			$cond['ticket_count'] = 100;
            foreach($ticket_list as $ticket) {
                array_push($assigned_list,$ticket->ticket_no);
            }
            for($i=2;$i<=$cond['ticket_count'];$i++) {
                if(!(in_array($i,$assigned_list))) {
                    array_push($unassigned_list,$i);
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unassigned_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_enroll_ticket_list(Request $request) {
        try {
            $cond = $request->conditions; 
            $ticket_list = ChitDetail::where(array('group_id'=>$cond['group_id']))->where('id', '!=' , $cond['id'])->get();
            $assigned_list = [];
            $unassigned_list = [];
            $ticket_count = 0;
            foreach($ticket_list as $ticket) {
                array_push($assigned_list,$ticket->ticket_no);
            } 
            $chit_det = ChitDetail::where(array('group_id'=>$cond['group_id']))->get();
            if(isset($chit_det[0]->group_det->scheme_det)) {
                $ticket_count = $chit_det[0]->group_det->scheme_det->members;
            }  
            for($i=2;$i<=$ticket_count;$i++) {
                if(!(in_array($i,$assigned_list))) {
                    array_push($unassigned_list,$i);
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unassigned_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_unrolled_customers(Request $request) {
        try {
            $cond = $request->conditions; 
            $enrolled_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id'],'chit_type'=>2))->get();
            $enrolled_customer_list = [];
            foreach($enrolled_customers as $enroll) {
                array_push($enrolled_customer_list,$enroll->customer_id);
            } 
            $unrolled_customers = [];
            if(count($enrolled_customer_list)>0) {
                $unrolled_customers = Customer::where(array('branch_id'=>$cond['branch_id']))->whereNotIn('id', $enrolled_customer_list)->get();
            }
            else {
                $unrolled_customers = Customer::where(array('branch_id'=>$cond['branch_id']))->get();
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unrolled_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_unrolled_customer_list(Request $request) {
        //for edit page list
        try {
            $cond = $request->conditions; 
            $enrolled_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id'],'chit_type'=>2))->where('id', '!=' , $cond['id'])->get(); 
            $enrolled_customer_list = []; 
            foreach($enrolled_customers as $enroll) {
                array_push($enrolled_customer_list,$enroll->customer_id);
            } 
            $unrolled_customers = Customer::where(array('branch_id'=>$cond['branch_id']))->whereNotIn('id', $enrolled_customer_list)->get();
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unrolled_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_enrolled_customers(Request $request) {
        try {
            $cond = $request->conditions; 
            $enrolled_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id']))->where('chit_type','!=',1)->get();
            if(isset($cond['group_id'])) {
                $enrolled_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id']))->where('chit_type','!=',1)->get();
            }
            $chit_model = new ChitDetail();
            $unique_list = $chit_model->get_unique_list($enrolled_customers,'customer_id');
			
			
            foreach($enrolled_customers as $enrol) {  
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : [];
                //$enrol->customer_info->name = $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name  . ' | ' . $enrol->customer_det->customer_code;				
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : []; 
                if(isset($enrol->group_det)) {
                    $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no; 
                }
            }  
            foreach($unique_list as $enrol) { 
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                     
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : [];
				$enrol->customer_info->name = $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name  . ' | ' . $enrol->customer_det->customer_code;
                $enrol->name = isset($enrol->customer_det) ? ($enrol->customer_det->name) : [];
                //only fountain chit name change
                
                $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                if(isset($enrol->group_det)) {
                    $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no;
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolled_customers;
            $response['unique_list'] = $unique_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_auctionable_enrolled_customers(Request $request) {
        try {

            $cond = $request->conditions; 
            $auctioned_list = BiddingDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id']))->get();
            if(isset($request->for_edit)) {
                $auctioned_list = BiddingDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id']))->whereNotIn('id',[$request->for_edit])->get();
            }
            $auctioned_id_list = [];
			
            // for without company chit hide
            foreach($auctioned_list as $auctioned) {
                if($auctioned->customer_id == 1)
                {
                    $enroll_id = '';
                }
                else
                {
                    $enroll_id = $auctioned->enrollment_id;
                }
                array_push($auctioned_id_list,$enroll_id);
            } 
            $auctionable_customers = [];
			
            if(count($auctioned_id_list)>0) {
				
                $auctionable_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id'],'chit_type'=>'2'))->whereNotIn('id', $auctioned_id_list)->get();
            }
            else {
				
                $auctionable_customers = ChitDetail::where(array('branch_id'=>$cond['branch_id'],'group_id'=>$cond['group_id'],'chit_type'=>'2'))->get();
            }
			
            foreach($auctionable_customers as $auction) { 
                $auction->tenant_info = isset($auction->tenant_det) ? ($auction->tenant_det) : [];                     
                $auction->branch_info = isset($auction->branch_det) ? ($auction->branch_det) : [];
                $auction->customer_info = isset($auction->customer_det) ? ($auction->customer_det) : [];
				//$auction->name = $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name  . ' | ' . $enrol->customer_det->customer_code;
                $auction->group_info = isset($auction->group_det) ? ($auction->group_det) : [];
                $auction->collection_agent_info = isset($auction->collection_agent_det) ? ($auction->collection_agent_det) : [];
                $customer_name = $auction->customer_det->name . ' ' . $auction->customer_det->last_name  . ' | ' . $auction->customer_det->customer_code . "_";
                $ticket_no = isset($auction->ticket_no) ? ($auction->ticket_no) : "";
                $phone_no = isset($auction->customer_det) ? ($auction->customer_det->mobile_no) : 0;
                
                if($auction->ticket_type == 1)
                {
                    $ticket_type = "full_ticket";
                }
                else if($auction->ticket_type == 2)
                {
                    $ticket_type = "half_ticket";
                }else{
                    $ticket_type = "_";
                }
                $auction->customer_name_det = $customer_name. " / ".$ticket_no." / ". $phone_no ." / ". $ticket_type;                
            }
			
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $auctionable_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_enrol_report(Request $request) 
    {
		try { 
            $cond = [];
            $cond1 = [];
            if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }        	
        	if(isset($request->group_id)){
        		$cond['group_id'] = $request->group_id; 
        	}
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $enrolls = ChitDetail::where($cond)->where($cond1)->orderBy('id','desc')->get();
            }
            if($request->branch_id == 0)
            {
                $enrolls = ChitDetail::where($cond)->orderBy('id','desc')->get();
            }
            $i=1; 
			if(count($enrolls)>0)
            {
                $grand_chit_value = 0;
                $tot_chit = 0;
                foreach($enrolls as $enrol) {	
		//print_r($enrol->id); echo '/';
                    $enrol->sno = $i;
                    $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];
                    $enrol->branch_name = isset($enrol->branch_det->branch_name) ? ($enrol->branch_det->branch_name) : [];
                    $enrol->group_name = isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : [];
                    $enrol->subscriber_name = $enrol->customer_det->name . ' ' . $enrol->customer_det->last_name;
                    $enrol->subscriber_mobile_no = isset($enrol->customer_det->mobile_no) ? ($enrol->customer_det->mobile_no) : [];
                    if($enrol->chit_type != 3)
                    {
                        $group_name =isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : '';
                        $chit_value = isset($enrol->group_det) ? ($enrol->group_det->scheme_det->chit_value) : 0;
                    }
                    if($enrol->chit_type == 3)
                    {   
                        $slab_types = SlabDue::where('id',$enrol->slab_id)->get();   
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                        $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrol->ar_no ?? null);
                        $group_name = "Commitment ".$slab_name;
                        $chit_value = isset($enrol->scheme_det->chit_value) ? ($enrol->scheme_det->chit_value) : 0;
                    }
                    $enrol->chit_value = $chit_value;
                    $enrol->group_name = $group_name;
                    $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                    $enrol->introducer_info = [];
                    if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                        $introducer_det = Employee::find($enrol->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                    }
                    else if($enrol->agent_type==1) {
                        $introducer_det = Customer::find($enrol->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                    }
                    else {
                        $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : [];
                    }
    				if($enrol->agent_type==1) {
    					$enrol->agent_type="Customer";
    				} else if($enrol->agent_type==2) {
    					$enrol->agent_type="Employee";
    				} else if($enrol->agent_type==3) {
    					$enrol->agent_type="Bussiness Agent";
    				} else if($enrol->agent_type==4) {
    					$enrol->agent_type="Self Joining";
    				} else if($enrol->agent_type==5) {
    					$enrol->agent_type="Others";
    				} else {
    					$enrol->agent_type="-";
    				}
					
					//paid_amount
                    $advance_amt = AdvanceReceipt::where('enrollment_id',$enrol->id)->sum('receipt_amount');
                    $rec_amt = Receipt::where('enrollment_id',$enrol->id)->where('payment_type_id','!=','7')->sum('received_amount');
					$receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('customer_id'=>$enrol->customer_id))->get();
			
					$total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
					
					$advance = BeforeEnrolReceipt::selectRaw('*,sum(amount)as total_advance')->where(array('customer_id'=>$enrol->customer_id,'status'=>1))->get();
					$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
					
					$available_advance_before_enrol = $total_advance - $total_received;
                    $enrol->paid_amount = $advance_amt + $rec_amt;
    				
                    $enrol->date_introduced = ($enrol->date_introduced!=0) ? date('d/m/Y',strtotime($enrol->date_introduced)) : '';

                    $tot_chit += $chit_value;
                    $i++;
                } 			 
                $grand_chit_value += $tot_chit;

                $grand_chit_value_format = 0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                } 
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $enrolls;
                return response()->json($response,200);
            }
            else
            {
				$enrolls = [];
                $response['grand_chit_value'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $enrolls;
		        return response()->json($response, 200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

   public function get_group_wise_outstanding(Request $request) {
		try { 
		if($request->tenant_id != 47){
            $cond = [];   
            $cond1 = [];         
			if($request->branch_id > 0)
            {
                $cond1['branch_id'] = $request->branch_id; 
            }
			if(isset($request->group_id)){
				$cond1['group_id'] = $request->group_id;   
			}           
            $enrolls = ChitDetail::where($cond1)->where('chit_type','!=',1)->where('status','=',1)->get();
			
			$k=1;			
            $tot_overal_paid = 0;
            $grand_tot_overal_paid = 0;
            $tot_overal_pending = 0;
            $grand_tot_overal_pending = 0;
            $tot_overal_penalty =0;
            $grand_tot_overal_penalty = 0;
            $grand_tot_overal_bonus = 0;
            $tot_overal_bonus = 0;
            $grand_tot_overal_discount = 0;
            $tot_overal_discount = 0;
			
            foreach($enrolls as $enroll) 
            { 
			 
                $enroll->sno = $k;  
                $paid_rec_amount = 0;            
                if($enroll->chit_type !=3)
                {    
			
                    $enroll->branch_name = isset($enroll->branch_det) ? ($enroll->branch_det->branch_name) : [];
    				$enroll->agent_name = isset($enroll->collection_agent_det) ? ($enroll->collection_agent_det->first_name) : [];
                    $enroll->group_name = isset($enroll->group_det) ? ($enroll->group_det->group_name) : [];
                    $enroll->chit_value = isset($enroll->group_det->scheme_det) ? ($enroll->group_det->scheme_det->chit_value) : [];
    				$enroll->customer_name = isset($enroll->customer_det) ? ($enroll->customer_det->name . ' ' . $enroll->customer_det->last_name) : [];
    				$enroll->customer_mobile = isset($enroll->customer_det) ? ($enroll->customer_det->mobile_no) : [];
    				$door_no = isset($enroll->customer_det) ? ($enroll->customer_det->door_no) : []; 
    				$buildling_name = isset($enroll->customer_det) ? ($enroll->customer_det->buildling_name) : [];
    				$address_line_1 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_1) : [];
    				$address_line_2 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_2) : [];
    				$landmark = isset($enroll->customer_det) ? ($enroll->customer_det->landmark) : [];
    				$city_name = City::where('id',$enroll->customer_det->city_id)->pluck('city_name');
    				$pincode = isset($enroll->customer_det) ? ($enroll->customer_det->pincode) : [];
    				$ticket_type = $enroll->ticket_type;
    				$enroll->customer_address = $door_no.', '.$buildling_name.', '.$address_line_1.', '.$address_line_2.', '.$landmark.', '.$city_name.'-'.$pincode;
    				$group_id = isset($enroll->group_id) ? ($enroll->group_id) : 0;
                    $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0;
                    $branch_id = isset($enroll->branch_id) ? ($enroll->branch_id) : 0;
    				//total_installment_amount
    				$total_installment_amount_ovr = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
    				//$enroll->overall_installment_amount = $total_installment_amount;
    				 $enroll->overall_installment_amount = ($total_installment_amount_ovr * $ticket_type);
				    $total_installment_amount = ($total_installment_amount_ovr * $ticket_type);
    				//paid_amount
    				$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
    				$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
    				$bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                    $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
    				$paid_amount = $paid_rec_amount + $bonus_amount;
    				$rec_inst_no=0;
					                    

					if(($enroll->agent_type==2) || ($enroll->agent_type==3)) {
                        $introducer_det = Employee::find($enroll->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enroll->agent_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                        $enroll->agent_mobile = $introducer_det->mobile_no;
                    }
                    else if($enroll->agent_type==1) {
                        $introducer_det = Customer::find($enroll->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enroll->agent_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
						$enroll->agent_mobile = $introducer_det->mobile_no;
                    }
                    else {
                        $enroll->agent_name = isset($enroll->introducer_name) ? ($enroll->introducer_name) : [];
                    }
    				
    				//pending installments
    				$max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
    				$rec_inst_no = Receipt::where('id','=',$max_rec_id_inst)->pluck('installment_no');
    				//pending_amount 
    				$pending_amount = $total_installment_amount - $paid_amount;
					

                    //auction wise details 
                    $inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count(); 
                    $auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
                    $i=0;
                    $install_det = [];                  
                    $total_penalty_amounts = 0;
                    $total_bonus_amounts = 0;
                    $total_pending_days = 0;
                    $total_pending_installments =0;
                    $cr_date = date('Y-m-d');
                    $inst_amt = 0;

                        $cust_det['installment_no'] = 0;
                        $cust_det['inst_amt_wise'] = 0;
                        $cust_det['amount_paid'] = 0;
                        $cust_det['inst_amt_wise_pending'] = 0;
                        $cust_det['pending_days'] = 0;
                        $cust_det['bonus_inst_wise'] = 0;
                        $cust_det['penalty_inst_wise'] = 0;
                        $cust_det['discount_inst_wise'] = 0;
                        $cust_det['discount_penalty_wise'] = 0;
                        $cust_det['auction_id'] = 0;
                        $cust_det['installment_wise_overall_pending'] = 0;
                        $cust_det['cancel_dividend_amount'] =0;
                      
                        
                            array_push($install_det,$cust_det);
                       
                      
                    $auc_wise_inst_id = isset($auc_wise_inst_amts[0]->id) ? ($auc_wise_inst_amts[0]->id) : 0;
                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_wise_inst_id, 'status' => 1])->get();
                    $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                    $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                    if(empty($cus_rec_amt[0])) { 
                        $amount_paid = 0;
                    } 
                    else {
                        $amount_paid_rec = $cus_rec_amt[0]->total_paid;
                        $amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
                        $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                    }
                    $auction_date = isset($auc_wise_inst_amts[0]->auction_date) ? ($auc_wise_inst_amts[0]->auction_date) : '0000-00-00';
                   
                    $bonus = $total_bonus_amounts;
                    $enroll->bonus = $bonus;
                    $penalty = round($total_penalty_amounts);
                    $enroll->penalty = $penalty;
                    $overall_pending_amount = $pending_amount;
                    $enroll->overall_pending_amount = $overall_pending_amount;
                    $overall_paid_amount = round($paid_rec_amount);
                    $enroll->overall_paid_amount = $overall_paid_amount;
                    $discount = 0;
                    $enroll->discount = $discount;
                    $enroll->pending_days = 0;
                    $enroll->pending_inst = $total_pending_installments;
					// $last_receipt_date_inst = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','=',0)->pluck('current_installment_amount'); 
                    // $enroll->current_month_pending = $last_receipt_date_inst[0];
                
                $k++;  
                $tot_overal_paid += $overall_paid_amount;
                $tot_overal_pending += $overall_pending_amount; 
                $tot_overal_penalty += $penalty;
                $tot_overal_bonus += $bonus;
                $tot_overal_discount += $discount;
                }
            } 
            $grand_tot_overal_paid += $tot_overal_paid;
            $grand_tot_overal_pending += $tot_overal_pending;
            $grand_tot_overal_penalty += $tot_overal_penalty;
            $grand_tot_overal_bonus += $tot_overal_bonus;
            $grand_tot_overal_discount += $tot_overal_discount;

            $grand_tot_overal_paid_format =0;
            $grand_tot_overal_pending_format =0;
            $grand_tot_overal_penalty_format =0;
            $grand_tot_overal_bonus_format =0;
            $grand_tot_overal_discount_format =0;

            if($grand_tot_overal_paid > 0)
            {   
                $num = $grand_tot_overal_paid;
                $grand_tot_overal_paid_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_pending > 0)
            {   
                $num = $grand_tot_overal_pending;
                $grand_tot_overal_pending_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_penalty > 0)
            {   
                $num = $grand_tot_overal_penalty;
                $grand_tot_overal_penalty_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_bonus > 0)
            {   
                $num = $grand_tot_overal_bonus;
                $grand_tot_overal_bonus_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_discount > 0)
            {   
                $num = $grand_tot_overal_discount;
                $grand_tot_overal_discount_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_tot_overal_paid'] = $grand_tot_overal_paid_format;
            $response['grand_tot_overal_pending'] = $grand_tot_overal_pending_format;
            $response['grand_tot_overal_penalty'] = $grand_tot_overal_penalty_format;
            $response['grand_tot_overal_bonus'] = $grand_tot_overal_bonus_format;
            $response['grand_tot_overal_discount'] = $grand_tot_overal_discount_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
		} else {
		$cond = [];   
            $cond1 = [];         
			if($request->branch_id > 0)
            {
                $cond1['branch_id'] = $request->branch_id; 
            }
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;   
			} 
			if(isset($request->area_id)){
				$cond['collection_area_id'] = $request->area_id;   
			}           
            $enrolls = ChitDetail::where($cond)->where($cond1)->where('chit_type','!=',1)->where('customer_id','!=',1)->get();
            if($request->branch_id == 0)
            {
                $enrolls = ChitDetail::where($cond)->where('chit_type','!=',1)->where('customer_id','!=',1)->get();
            }
			
			$k=1;			
            $tot_overal_paid = 0;
            $grand_tot_overal_paid = 0;
            $tot_overal_pending = 0;
            $grand_tot_overal_pending = 0;
            $tot_overal_penalty =0;
            $grand_tot_overal_penalty = 0;
            $grand_tot_overal_bonus = 0;
            $tot_overal_bonus = 0;
            $grand_tot_overal_discount = 0;
            $tot_overal_discount = 0;
            foreach($enrolls as $enroll) 
            { 
			
                $enroll->sno = $k;  
                $paid_rec_amount = 0;            
                if($enroll->chit_type !=3)
                {    
                    $enroll->branch_name = isset($enroll->branch_det) ? ($enroll->branch_det->branch_name) : [];
    				$enroll->agent_name = isset($enroll->collection_agent_det) ? ($enroll->collection_agent_det->first_name) : [];
                    $enroll->group_name = isset($enroll->group_det) ? ($enroll->group_det->group_name) : [];
                    $enroll->chit_value = isset($enroll->group_det->scheme_det) ? ($enroll->group_det->scheme_det->chit_value) : [];
    				$enroll->customer_name = isset($enroll->customer_det) ? ($enroll->customer_det->name) : [];
					$enroll->customer_code = isset($enroll->customer_det) ? ($enroll->customer_det->customer_code) : [];
					 $enroll->collection_area = isset($enroll->collection_area_det) ? ($enroll->collection_area_det->collection_area) : '-';
    				$enroll->customer_mobile = isset($enroll->customer_det) ? ($enroll->customer_det->mobile_no) : [];
    				$door_no = isset($enroll->customer_det) ? ($enroll->customer_det->door_no) : []; 
    				$buildling_name = isset($enroll->customer_det) ? ($enroll->customer_det->buildling_name) : [];
    				$address_line_1 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_1) : [];
    				$address_line_2 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_2) : [];
    				$landmark = isset($enroll->customer_det) ? ($enroll->customer_det->landmark) : [];
    				$city_name = City::where('id',$enroll->customer_det->city_id)->pluck('city_name');
    				$pincode = isset($enroll->customer_det) ? ($enroll->customer_det->pincode) : [];
    				$enroll->prized_status = isset($enroll->prized_status) ? ($enroll->prized_status) : [];
					
					if($enroll->prized_status == 1){
						
						$enroll->prized_status = 'Prized';
					}
					else{
						
						$enroll->prized_status = 'Non Prized';
					}
    				$enroll->customer_address = $door_no.', '.$buildling_name.', '.$address_line_1.', '.$address_line_2.', '.$landmark.', '.$city_name.'-'.$pincode;
    				$group_id = isset($enroll->group_id) ? ($enroll->group_id) : 0;
                    $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0;
                    $branch_id = isset($enroll->branch_id) ? ($enroll->branch_id) : 0;
					
    				//total_installment_amount
    				$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','!=',0)->sum('current_installment_amount');
    				$enroll->overall_installment_amount = $total_installment_amount;
					
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
    				
					$curn_mon_dues = 0;
					// $curn_mon_due = BiddingDetail::where('id','=',$max_bid_id)->get();
    				// $curn_mon_dues = $curn_mon_due[0]->actual_installment_amount;
					
    				//paid_amount
    				$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
    				$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
    				$bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                    $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
    				$paid_amount = $paid_rec_amount + $bonus_amount;
    				$rec_inst_no=0;
    				
    				//pending installments
    				$max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
    				$rec_inst_no = Receipt::where('id','=',$max_rec_id_inst)->pluck('installment_no');
    				//pending_amount 
    				$pending_amount = $total_installment_amount - $paid_amount;

                    //auction wise details 
                    $inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count(); 
                    $auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','!=',0)->get();
                    $i=0;
                    $install_det = [];                  
                    $total_penalty_amounts = 0;
                    $total_bonus_amounts = 0;
                    $total_pending_days = 0;
                    $total_pending_installments =0;
                    $cr_date = date('Y-m-d');
                    $inst_amt = 0;
                    foreach($auc_wise_inst_amts as $auc_wise_inst_amt) 
                    {
                        $cust_det = [];     
                        $penalty = 0;                   
                        $bonus = 0;                 
                        $inst_amt = $auc_wise_inst_amt->current_installment_amount;
                        $auction_id = $auc_wise_inst_amt->id;
                        $auc_id  = $auc_wise_inst_amt->id;
                        $installment_no  = $auc_wise_inst_amt->installment_no;
                        $auc_date_inst  = $auc_wise_inst_amt->auction_date;
                        $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'installment_no' => $installment_no, 'status' => 1])->get();   
                        $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                        $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                        if(empty($cus_rec_amt[0])) { 
                            $amount_paid = 0;
                        } 
                        else {
                            $amount_paid_rec = $cus_rec_amt[0]->total_paid;
                            $amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
                            $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                        }
                        $ins_amt_wise_rec = $inst_amt - $amount_paid;
                        if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
                            $receipt_date = $last_receipt_date_inst[0];
                            $current_date = date('Y-m-d');
                            $datetime1 = new DateTime($receipt_date);
                            $datetime2 = new DateTime($current_date);
                            $interval = $datetime1->diff($datetime2);
                            $pending_days = $interval->format('%a');
                            $i++;
                        } 
                        else {
                            $receipt_date = $auc_date_inst;
                            $current_date = date('Y-m-d');
                            $datetime1 = new DateTime($receipt_date);
                            $datetime2 = new DateTime($current_date);
                            $interval = $datetime1->diff($datetime2);
                            $pending_days = $interval->format('%a');
                            $i++;
                        }
						//print_r($pending_days);die();
                        // Bonus days and percentage by heirrachy over all 
                        if($enroll->customer_det->bonus_days!=0 && $enroll->customer_det->customer_bonus!=0)
                        {
                          $bonus_days = $enroll->customer_det->bonus_days;
                          $bonus_percentage = $enroll->customer_det->customer_bonus;
                        }    
                        elseif($enroll->group_det->group_bonus_days!=0 && $enroll->group_det->group_base_bonus!=0)
                        {
                          $bonus_days = $enroll->group_det->group_bonus_days;
                          $bonus_percentage = $enroll->group_det->group_base_bonus;
                        }
                        elseif($enroll->branch_det->bonus_days!=0 && $enroll->branch_det->bonus!=0)
                        {
                          $bonus_days = $enroll->branch_det->bonus_days;
                          $bonus_percentage = $enroll->branch_det->bonus;              
                        }    
                        else
                        {
                          $bonus_days = 0;
                          $bonus_percentage = 0;
                        }           
                        
                        $bid = BiddingDetail::Where('group_id',$group_id)->max('id');
                        $check_auction = BiddingDetail::where('id',$bid)->get();
                        $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                        // Auction on holiday check for bonus
                        $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$enroll->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                        $holiday_count = 0;
                        if(count($auction_on_holiday)>0)
                        {   
                            $holiday_count += $auction_on_holiday->count();
                        }

                        $bonus_days_include_holiday = $bonus_days + $holiday_count;
                        if($pending_days <=  $bonus_days_include_holiday)
                        { 
                          if($ins_amt_wise_rec==$inst_amt)
                          { 
                            $bonus_calculation = $inst_amt * $bonus_percentage / 100 ; 
                            $bonus = strval(round($bonus_calculation));
                            $cust_det['bonus'] = $bonus;
                          }
                          else
                          {
                            $cust_det['bonus'] = 0;
                          }
                        }
                        else
                        {
                          $cust_det['bonus'] = 0;
                        }
                        
                        // Penalty Calculation installment wise
                        if($enroll->customer_det->penalty_days!=0 )
                        {
                          $inst_penalty_days = $enroll->customer_det->penalty_days;
                          $inst_penalty_percentage = $enroll->customer_det->customer_penalty_interest;
                        }
                        elseif($enroll->group_det->group_penalty_days!=0 )
                        {
                          $inst_penalty_days = $enroll->group_det->group_penalty_days;
                          $inst_penalty_percentage = $enroll->group_det->group_base_penalty;
                        }
                        elseif($enroll->branch_det->penalty_days!=0)
                        {
                          $inst_penalty_days = $enroll->branch_det->penalty_days;
                          $inst_penalty_percentage = $enroll->branch_det->branch_wise_penalty;
                        }
                        else
                        {
                          $inst_penalty_days = "0";
                          $inst_penalty_percentage = "0";
                        }

                        $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                        $pend_days_int = 0;    
                        $cust_det['penalty_amounts'] = 0;   
                        
                        if($pending_days > $inst_penalty_days)
                        {           
                            $branch=Branch::where('id',$branch_id)->get();
                            if($enroll->prized_status==0)
                            {
                                $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                                $pend_days_int = $pending_days * ($non_prize/ 100);
                                $current_month_days = date('t'); 
                                $total_days_int = $pend_days_int / $current_month_days;
                                $penalty = $total_days_int * $ins_amt_wise_rec;
                                $cust_det['penalty_amounts'] = strval(round($penalty));
                            } 
                            else
                            {               
                                $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                                $pend_days_int = $pending_days * ($prize / 100);
                                $current_month_days = date('t'); 
                                $total_days_int = $pend_days_int / $current_month_days;
                                $penalty = $total_days_int * $ins_amt_wise_rec;
                                $cust_det['penalty_amounts'] = strval(round($penalty));
                            }            
                        }  
                        // Pending Installment 
                        $rcpt = Receipt::where('enrollment_id','=',$enrollment_id)->get();
                        $auc_pendign_count = 0;
                        $rcpt_pendign_count =0;
                        $recpt_auc_id  = isset($rcpt[0]->auction_id) ? ($rcpt[0]->auction_id) : 0;
                        if($recpt_auc_id != $auc_id)
                        {
                            $auc_pendign_count += 1;
                        }
                        if($amount_paid != $inst_amt)
                        {
                            if($recpt_auc_id == $auc_id)
                            {
                               $rcpt_pendign_count += 1;
                            }                        
                        }
                        $pendign_count = $auc_pendign_count + $rcpt_pendign_count;

                        $cust_det['installment_no'] = $installment_no;
                        $cust_det['inst_amt_wise'] = $inst_amt;
                        $cust_det['amount_paid'] = $amount_paid;
                        $cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
                        $cust_det['pending_days'] = $pending_days;
                        $cust_det['bonus_inst_wise'] = 0;
                        $cust_det['penalty_inst_wise'] = 0;
                        $cust_det['discount_inst_wise'] = 0;
                        $cust_det['discount_penalty_wise'] = 0;
                        $cust_det['auction_id'] = $auction_id;
                        $cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
                        $cust_det['cancel_dividend_amount'] =0;
                        if($ins_amt_wise_rec>0) {
                            array_push($install_det,$cust_det);
                        }   
                        $total_penalty_amounts += $penalty;
                        $total_bonus_amounts += $bonus;
                        $total_pending_installments +=$pendign_count;
                    };
                    $auc_wise_inst_id = isset($auc_wise_inst_amts[0]->id) ? ($auc_wise_inst_amts[0]->id) : 0;
                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_wise_inst_id, 'status' => 1])->get();
                    $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                    $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                    if(empty($cus_rec_amt[0])) { 
                        $amount_paid = 0;
                    } 
                    else {
                        $amount_paid_rec = $cus_rec_amt[0]->total_paid;
                        $amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
                        $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                    }
                    $auction_date = isset($auc_wise_inst_amts[0]->auction_date) ? ($auc_wise_inst_amts[0]->auction_date) : '0000-00-00';
                    $ins_amt_wise_rec1 = $inst_amt - $amount_paid;
                    if($ins_amt_wise_rec1 !=$inst_amt && $inst_amt_no !=0) {
                        $receipt_date = $last_receipt_date_inst[0];
                        $current_date = date('Y-m-d');
                        $datetime1 = new DateTime($receipt_date);
                        $datetime2 = new DateTime($current_date);
                        $interval = $datetime1->diff($datetime2);
                        $pending_days1 = $interval->format('%a');
                        $i++;
                    } 
                    else {
                        $receipt_date = $auction_date;
                        $current_date = date('Y-m-d');
                        $datetime1 = new DateTime($receipt_date);
                        $datetime2 = new DateTime($current_date);
                        $interval = $datetime1->diff($datetime2);
                        $pending_days1 = $interval->format('%a');
                        $i++;
                    }
                    $bonus = $total_bonus_amounts;
                    $enroll->bonus = $bonus;
                    $penalty = round($total_penalty_amounts);
                    $enroll->current_due = $curn_mon_dues;
                    $enroll->penalty = $penalty;
                    $overall_pending_amount = $pending_amount;
                    $enroll->overall_pending_amount = $overall_pending_amount;
                    $overall_paid_amount = round($paid_rec_amount);
                    $enroll->overall_paid_amount = $overall_paid_amount;
                    $discount = 0;
                    $enroll->discount = $discount;
                    $enroll->pending_days = 0;
                    $enroll->pending_inst = 0;
					
					$last_receipt_date_inst = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','=',0)->pluck('current_installment_amount'); 
					
                    $enroll->current_month_pending = $last_receipt_date_inst[0];
					
                
                $k++;  
                $tot_overal_paid += $overall_paid_amount;
                $tot_overal_pending += $overall_pending_amount; 
                $tot_overal_penalty += $penalty;
                $tot_overal_bonus += $bonus;
                $tot_overal_discount += $discount;
				
                }
            } 
            $grand_tot_overal_paid += $tot_overal_paid;
            $grand_tot_overal_pending += $tot_overal_pending;
            $grand_tot_overal_penalty += $tot_overal_penalty;
            $grand_tot_overal_bonus += $tot_overal_bonus;
            $grand_tot_overal_discount += $tot_overal_discount;

            $grand_tot_overal_paid_format =0;
            $grand_tot_overal_pending_format =0;
            $grand_tot_overal_penalty_format =0;
            $grand_tot_overal_bonus_format =0;
            $grand_tot_overal_discount_format =0;

            if($grand_tot_overal_paid > 0)
            {   
                $num = $grand_tot_overal_paid;
                $grand_tot_overal_paid_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_pending > 0)
            {   
                $num = $grand_tot_overal_pending;
                $grand_tot_overal_pending_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_penalty > 0)
            {   
                $num = $grand_tot_overal_penalty;
                $grand_tot_overal_penalty_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_bonus > 0)
            {   
                $num = $grand_tot_overal_bonus;
                $grand_tot_overal_bonus_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_overal_discount > 0)
            {   
                $num = $grand_tot_overal_discount;
                $grand_tot_overal_discount_format = $this->moneyFormatIndia($num);  
            }
			
            $response['grand_tot_overal_paid'] = $grand_tot_overal_paid_format;
            $response['grand_tot_overal_pending'] = $grand_tot_overal_pending_format;
            $response['grand_tot_overal_penalty'] = $grand_tot_overal_penalty_format;
            $response['grand_tot_overal_bonus'] = $grand_tot_overal_bonus_format;
            $response['grand_tot_overal_discount'] = $grand_tot_overal_discount_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }	
			
		}
        
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
	 }
	 
	public function store_commitment_enrol(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'payment_type' => 'required',
            //'collection_area_id' => 'required',
            'collection_pin' => 'required',
            //'collection_agent_id' => 'required',
            'agent_type' => 'required',
            'date_introduced' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                // Approval must update existing enrollment via approve-commitment-eligibility, not insert again.
                if (!empty($request->enrollment_id)) {
                    return response()->json([
                        'status' => 'Error',
                        'msg' => 'Enrollment already exists. Use approve-commitment-eligibility instead.',
                    ], 200);
                }

                $customerIdForCheck = $request->customer_id;
                if (is_array($customerIdForCheck) && isset($customerIdForCheck[0]['customer_id'])) {
                    $customerIdForCheck = $customerIdForCheck[0]['customer_id'];
                }
                if ($customerIdForCheck && !empty($request->slab_id)) {
                    $pendingEnrol = ChitDetail::where('customer_id', $customerIdForCheck)
                        ->where('slab_id', $request->slab_id)
                        ->where('chit_type', 3)
                        ->where('eligibility_status', '0')
                        ->first();
                    if ($pendingEnrol) {
                        return response()->json([
                            'status' => 'Error',
                            'msg' => 'SPN enrollment already exists for this customer and slab (pending approval).',
                            'data' => $pendingEnrol,
                        ], 200);
                    }
                }

                $enrol = new ChitDetail();                
				$enrol->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$enrol->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $enrol->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				//$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
               // $enrol->customer_id = $customer_id;
                $enrol->slab_id = isset($request->slab_id) ? ($request->slab_id) : 0;
                $enrol->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                $enrol->prized_status = isset($request->prized_status) ? ($request->prized_status) : 0;
                $enrol->eligibility_status = isset($request->eligibility_status) ? ($request->eligibility_status) : 0;
                $enrol->chit_intimation_status = isset($request->chit_intimation_status) ? ($request->chit_intimation_status) : '';
                $enrol->payment_type = isset($request->payment_type) ? ($request->payment_type) : 0;
                $enrol->collection_area_id = isset($request->collection_area_id) ? ($request->collection_area_id) : 0;
                $enrol->collection_pin = isset($request->collection_pin) ? ($request->collection_pin) : 0;
                $enrol->collection_agent_id = isset($request->collection_agent_id) ? ($request->collection_agent_id) : 0;
                $enrol->agent_type = isset($request->agent_type) ? ($request->agent_type) : 0;
                $enrol->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : 0;
                $enrol->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : 0;
				if(isset($request->due_date)) {
					$s_date = ($request->due_date['year']).'-'.sprintf('%02d',($request->due_date['month'])).'-'.sprintf('%02d',($request->due_date['day'])); 
					$enrol->due_date = date('Y-m-d',strtotime($s_date)); 
				}
				if(isset($request->date_introduced)) {
					$s_date = ($request->date_introduced['year']).'-'.sprintf('%02d',($request->date_introduced['month'])).'-'.sprintf('%02d',($request->date_introduced['day'])); 
					$enrol->date_introduced = date('Y-m-d',strtotime($s_date)); 
				}
                $enrol->pending_amount = isset($request->pending_amount) ? ($request->pending_amount) : '0';
                $enrol->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                $enrol->penalty_amount = isset($request->penalty_amount) ? ($request->penalty_amount) : '0';
                $enrol->bonus_amount = isset($request->bonus_amount) ? ($request->bonus_amount) : '0';
                $enrol->advance_amount = isset($request->advance_amount) ? ($request->advance_amount) : '0';
                $enrol->completed_auction = isset($request->completed_auction) ? ($request->completed_auction) : '0';
                $enrol->current_due = isset($request->current_due) ? ($request->current_due) : '0';
                $enrol->next_due = isset($request->next_due) ? ($request->next_due) : '0';
                $enrol->pending_days = isset($request->pending_days) ? ($request->pending_days) : '0';
                $enrol->level = isset($request->level) ? ($request->level) : '0';
                $enrol->chit_type = 3;
				
                $enrol->pending_installment = isset($request->pending_installment) ? ($request->pending_installment) : 0;
                if($request->agent_type == 3) {
                    $employee = Employee::where('id',$request->introducer_id)->pluck('commission');
					
                    $commission_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                    
                     $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
					
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $total_business_agent_commission = ($chit_value * ( $commission_percentage / 100 ) );
                    $balance_business_agent_commission = $total_business_agent_commission;
                    $paid_business_agent_commission = 0;
                    $enrol->total_business_agent_commission = $total_business_agent_commission;
                    $enrol->balance_business_agent_commission = $balance_business_agent_commission;
                    $enrol->paid_business_agent_commission = $paid_business_agent_commission;   
                }
                if($request->agent_type == 2) {
                    $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                    $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }     
                if($request->agent_type == 1) {
                    $employee = Customer::where('id',$request->introducer_id)->pluck('customer_incentive_percentage');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                    $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }       
                if($request->agent_type == 3) {
                    $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                    $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                    $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                    $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                    $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                    $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                    $balance_agent_incentive = $agent_incentive;
                    $paid_agent_incentive = 0;
                    $enrol->agent_incentive = $agent_incentive;
                    $enrol->balance_agent_incentive = $balance_agent_incentive;
                    $enrol->paid_agent_incentive = $paid_agent_incentive;   
                }        
                $enrol->pledge_status = isset($request->pledge_status) ? ($request->pledge_status) : 0;
                $enrol->discount_due = isset($request->discount_due) ? ($request->discount_due) : 0;
                $enrol->needed_amount = isset($request->needed_amount) ? ($request->needed_amount) : 0;
                $enrol->cancel_amount = isset($request->cancel_amount) ? ($request->cancel_amount) : 0;
                $enrol->return_amount = isset($request->return_amount) ? ($request->return_amount) : 0;
                $enrol->enrolment_type = isset($request->enrolment_type) ? ($request->enrolment_type) : 0;
                $enrol->bonus_percentage = isset($request->bonus_percentage) ? ($request->bonus_percentage) : 0;
                $enrol->penalty_percentage = isset($request->penalty_percentage) ? ($request->penalty_percentage) : 0;
                $enrol->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                $enrol->dividend_cancel = isset($request->dividend_cancel) ? ($request->dividend_cancel) : 0;
                $enrol->remarks = isset($request->remarks) ? ($request->remarks) : 0;
                $enrol->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $enrol->status = isset($request->status) && $request->status !== '' && $request->status !== null
                    ? (int) $request->status
                    : 1;

                if($enrol->save()) {

					$enrl_id = $enrol->id;
					$slab_id = isset($request->slab_id) ? ($request->slab_id) : 0;
					$customer_id = $enrol->customer_id;
					if (is_array($request->customer_id) && isset($request->customer_id[0]['customer_id'])) {
						$customer_id = $request->customer_id[0]['customer_id'];
					}

					/* All scheme months (past + future), not only up to today */
					$scheme_id = $enrol->scheme_id;
					if (empty($scheme_id) && $slab_id) {
						$scheme_id = SlabDue::where('id', $slab_id)->value('schemes_id');
					}
					$no_of_months = (int) Scheme::where('id', $scheme_id)->value('no_of_months');
					if ($no_of_months <= 0 && $slab_id) {
						$slabRow = SlabDue::find($slab_id);
						if ($slabRow) {
							for ($m = 1; $m <= 60; $m++) {
								$col = 'due_' . $m;
								if (!isset($slabRow->$col) || $slabRow->$col === null || $slabRow->$col === '') {
									break;
								}
								$no_of_months = $m;
							}
						}
					}

					$auction_date = $enrol->due_date;
					$onlyFirst = (
						(!empty($request->only_first_installment))
						|| ((string) ($enrol->eligibility_status ?? '1') === '0')
					);
					$maxInstallments = $onlyFirst ? 1 : $no_of_months;
					for ($i = 1; $i <= $maxInstallments; $i++) {
						$payment = 'due_' . $i;
						$due_amount = SlabDue::where('id', $slab_id)->pluck($payment);
						$amount = isset($due_amount[0]) ? ($due_amount[0]) : 0;

						$enrol_auc = new CommitmentAuction();
						$enrol_auc->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$enrol_auc->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$enrol_auc->customer_id = $customer_id;
						$enrol_auc->enrollment_id = $enrl_id;
						$enrol_auc->due_amount = $amount;
						$enrol_auc->installment_amount = $amount;
						$enrol_auc->installment_no = $i;
						$enrol_auc->auction_date = $auction_date;
						$enrol_auc->save();

						if ($i < $no_of_months) {
							$auction_date = date('Y-m-d', strtotime($auction_date . ' +1 month'));
						}
					}
					
					
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_commitment_chit_added');
					$response['data'] = $enrol;
					return response()->json($response,200);
				}
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

	public function update_commitmnent_enrol(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'payment_type' => 'required',
            //'collection_area_id' => 'required',
            'collection_pin' => 'required',
            //'collection_agent_id' => 'required',
            'agent_type' => 'required',
            'date_introduced' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$enrol = ChitDetail::find($request->id);
					$enrol->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $enrol->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $enrol->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$enrol->slab_id = isset($request->slab_id) ? ($request->slab_id) : 0;
                    $enrol->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                    $enrol->prized_status = isset($request->prized_status) ? ($request->prized_status) : 0;
                    $enrol->chit_intimation_status = isset($request->chit_intimation_status) ? ($request->chit_intimation_status) : '';
                    $enrol->payment_type = isset($request->payment_type) ? ($request->payment_type) : 0;
                    $enrol->collection_area_id = isset($request->collection_area_id) ? ($request->collection_area_id) : 0;
                    $enrol->collection_pin = isset($request->collection_pin) ? ($request->collection_pin) : 0;
                    $enrol->collection_agent_id = isset($request->collection_agent_id) ? ($request->collection_agent_id) : 0;
                    $enrol->agent_type = isset($request->agent_type) ? ($request->agent_type) : 0;
                    $enrol->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : 0;
                    $enrol->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : 0;
					$enrol->due_date = isset($request->due_date) ? ($request->due_date) : 0;
					if(isset($request->due_date)) {
						$s_date = ($request->due_date['year']).'-'.sprintf('%02d',($request->due_date['month'])).'-'.sprintf('%02d',($request->due_date['day'])); 
						$enrol->due_date = date('Y-m-d',strtotime($s_date)); 
					}
					if(isset($request->date_introduced)) {
						$s_date = ($request->date_introduced['year']).'-'.sprintf('%02d',($request->date_introduced['month'])).'-'.sprintf('%02d',($request->date_introduced['day'])); 
						$enrol->date_introduced = date('Y-m-d',strtotime($s_date)); 
					}   
                    $enrol->pending_amount = isset($request->pending_amount) ? ($request->pending_amount) : '0';
                    $enrol->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                    $enrol->penalty_amount = isset($request->penalty_amount) ? ($request->penalty_amount) : '0';
                    $enrol->bonus_amount = isset($request->bonus_amount) ? ($request->bonus_amount) : '0';
                    $enrol->advance_amount = isset($request->advance_amount) ? ($request->advance_amount) : '0';
                    $enrol->completed_auction = isset($request->completed_auction) ? ($request->completed_auction) : '0';
                    $enrol->current_due = isset($request->current_due) ? ($request->current_due) : '0';
                    $enrol->next_due = isset($request->next_due) ? ($request->next_due) : '0';
                    $enrol->pending_days = isset($request->pending_days) ? ($request->pending_days) : '0';
                    $enrol->level = isset($request->level) ? ($request->level) : '0';
                    $enrol->pending_installment = isset($request->pending_installment) ? ($request->pending_installment) : 0;
                    if($request->agent_type == 3) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('commission');
                        $commission_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = Group::where('id',$request->group_id)->pluck('scheme_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $total_business_agent_commission = ($chit_value * ( $commission_percentage / 100 ) );
                        $balance_business_agent_commission = $total_business_agent_commission;
                        $paid_business_agent_commission = 0;
                        $enrol->total_business_agent_commission = $total_business_agent_commission;
                        $enrol->balance_business_agent_commission = $balance_business_agent_commission;
                        $enrol->paid_business_agent_commission = $paid_business_agent_commission;   
                    }
                    if($request->agent_type == 2) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }
                    if($request->agent_type == 1) {
                        $employee = Customer::where('id',$request->introducer_id)->pluck('customer_incentive_percentage');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }       
                    if($request->agent_type == 3) {
                        $employee = Employee::where('id',$request->introducer_id)->pluck('incentive');
                        $incentive_percentage = isset($employee[0]) ? ($employee[0]) : 0;                        
                        $group_scheme_id = SlabDue::where('id',$request->slab_id)->pluck('schemes_id');
                        $scheme_chit_value = Scheme::where('id',$group_scheme_id[0])->pluck('chit_value');
                        $chit_value = isset($scheme_chit_value[0]) ? ($scheme_chit_value[0]) : 0;
                        $agent_incentive = ($chit_value * ( $incentive_percentage / 100 ) );
                        $balance_agent_incentive = $agent_incentive;
                        $paid_agent_incentive = 0;
                        $enrol->agent_incentive = $agent_incentive;
                        $enrol->balance_agent_incentive = $balance_agent_incentive;
                        $enrol->paid_agent_incentive = $paid_agent_incentive;   
                    }   
                    $enrol->pledge_status = isset($request->pledge_status) ? ($request->pledge_status) : 0;
                    $enrol->discount_due = isset($request->discount_due) ? ($request->discount_due) : 0;
                    $enrol->needed_amount = isset($request->needed_amount) ? ($request->needed_amount) : 0;
                    $enrol->cancel_amount = isset($request->cancel_amount) ? ($request->cancel_amount) : 0;
                    $enrol->return_amount = isset($request->return_amount) ? ($request->return_amount) : 0;
                    $enrol->bonus_percentage = isset($request->bonus_percentage) ? ($request->bonus_percentage) : 0;
                    $enrol->penalty_percentage = isset($request->penalty_percentage) ? ($request->penalty_percentage) : 0;
                    $enrol->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                    $enrol->dividend_cancel = isset($request->dividend_cancel) ? ($request->dividend_cancel) : 0;
                    $enrol->enrolment_type = isset($request->enrolment_type) ? ($request->enrolment_type) : 0;                
                    $enrol->remarks = isset($request->remarks) ? ($request->remarks) : 0;
					$enrol->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($enrol->save()) { 
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_enrol_updated');
						$response['data'] = $enrol;
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
    }

	public function get_commitment_enrolls(Request $request) {
        try {
            $cond = $request->conditions; 	
			$enrolls = ChitDetail::where($cond)->orderBy('id','desc')->get();
            foreach($enrolls as $enrol) {
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                $enrol->branch_info = ($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->customer_info = ($enrol->customer_det) ? ($enrol->customer_det) : []; 
                $enrol->scheme_info = ($enrol->scheme_det) ? ($enrol->scheme_det) : []; 
                $enrol->slab_info = ($enrol->slab_due_det) ? ($enrol->slab_due_det) : [];
                if (!empty($enrol->slab_info) && isset($enrol->slab_info->slab_name)) {
                    $enrol->slab_info->slab_name = \App\Models\ChitDetail::formatSlabWithArNo($enrol->slab_info->slab_name, $enrol->ar_no ?? null);
                } 
                $enrol->collection_agent_info = ($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $enrol->collection_area_info = ($enrol->collection_area_det) ? ($enrol->collection_area_det) : [];
                $enrol->date_introduced = ($enrol->date_introduced!=0) ? date('d/m/Y',strtotime($enrol->date_introduced)) : '';
				$enrol->due_date = ($enrol->due_date!=0) ? date('d/m/Y',strtotime($enrol->due_date)) : '';					
                $enrol->introducer_full_name = '';
                $enrol->introducer_info = [];
                if($enrol->agent_type==1) { 
                    //customer
                    $introducer_det = Customer::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $enrol->introducer_full_name = $introducer_det->name;
                }
                else if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                    //employee
                    $introducer_det = Employee::find($enrol->introducer_id);
                    $enrol->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : '';
                } else if(($enrol->agent_type==5)) { 
				$enrol->introducer_full_name = $enrol->introducer_name;
				}
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_commitment_enrolls(Request $request) 
    {
        try { 
            $cond = ['chit_type' => 3];
            if (isset($request->tenant_id) && $request->tenant_id !== '') {
                $cond['tenant_id'] = $request->tenant_id;
            }
            if (isset($request->branch_id) && $request->branch_id !== '') {
                $cond['branch_id'] = $request->branch_id;
            }
            if (isset($request->eligibility_status) && $request->eligibility_status !== '' && $request->eligibility_status !== null) {
                $cond['eligibility_status'] = $request->eligibility_status;
            }
            $enrolls = ChitDetail::where($cond)->orderBy('id', 'desc')->get();
            $i=1;
            foreach($enrolls as $enrol) {
                $enrol->sno = $i;
                $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];
                $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                $enrol->scheme_info = ($enrol->scheme_det) ? ($enrol->scheme_det) : []; 
                $enrol->slab_info = ($enrol->slab_due_det) ? ($enrol->slab_due_det) : [];
                if (!empty($enrol->slab_info) && isset($enrol->slab_info->slab_name)) {
                    $enrol->slab_info->slab_name = \App\Models\ChitDetail::formatSlabWithArNo($enrol->slab_info->slab_name, $enrol->ar_no ?? null);
                } 
                $enrol->customer_info = isset($enrol->customer_det) ? ($enrol->customer_det) : [];
                $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    /**
     * Temporary commitment chits from add-customer (eligibility_status = 0, installment 1 only).
     */
    public function store_temp_commitment_chit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'scheme_id' => 'required',
            'slab_id' => 'required',
            'rf_amount' => 'required',
            'payment_type_id' => 'required',
            'charge_type_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }
        try {
            $existingPending = ChitDetail::where('customer_id', $request->customer_id)
                ->where('slab_id', $request->slab_id)
                ->where('chit_type', 3)
                ->where('eligibility_status', '0')
                ->first();
            if ($existingPending) {
                return response()->json([
                    'status' => 'Success',
                    'msg' => 'SPN enrollment already exists for this customer and slab.',
                    'data' => $existingPending,
                ], 200);
            }

            $request->merge([
                'only_first_installment' => true,
                'eligibility_status' => 0,
                'advance_amount' => $request->rf_amount,
                'needed_amount' => 0,
                'payment_type' => $request->payment_type ?? 3,
                'agent_type' => $request->agent_type ?? 4,
                'collection_pin' => $request->collection_pin ?? 0,
                'date_introduced' => $request->date_introduced ?? [
                    'year' => (int) date('Y'),
                    'month' => (int) date('m'),
                    'day' => (int) date('d'),
                ],
                'due_date' => $request->due_date ?? [
                    'year' => (int) date('Y'),
                    'month' => (int) date('m'),
                    'day' => (int) date('d'),
                ],
                'remarks' => json_encode([
                    'payment_type_id' => $request->payment_type_id,
                    'rf_amount' => $request->rf_amount,
                    'charge_type_id' => $request->charge_type_id,
                ]),
            ]);
            $enrolResponse = $this->store_commitment_enrol($request);
            $payload = json_decode($enrolResponse->getContent(), true);
            if (
                !empty($payload['status'])
                && $payload['status'] === 'Success'
                && !empty($payload['data']['id'])
                && (float) $request->rf_amount > 0
            ) {
                $otherReq = new Request(array_merge($request->all(), [
                    'enrollment_id' => $payload['data']['id'],
                    'amount' => $request->rf_amount,
                    'employee_id' => $request->employee_id ?: $request->logged_employee_id,
                    'created_by' => $request->created_by ?: $request->logged_user_id,
                    'received_date' => $request->received_date ?: [
                        'year' => (int) date('Y'),
                        'month' => (int) date('m'),
                        'day' => (int) date('d'),
                    ],
                ]));
                try {
                    app(OtherChargeController::class)->saveOtherChargeReceipt($otherReq);
                } catch (\Exception $chargeEx) {
                    \Log::error('RF other charge save failed: ' . $chargeEx->getMessage());
                }
            }
            return $enrolResponse;
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error')], 401);
        }
    }

    /**
     * Pending SPN approval: first installment paid or near complete (>= 90%).
     */
    public function list_commitment_eligibility_pending(Request $request)
    {
        try {
            $query = ChitDetail::where('chit_type', 3)->where('eligibility_status', '0');
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id)) {
                $query->where('branch_id', $request->branch_id);
            }
            $enrolls = $query->orderBy('id', 'desc')->get();
            $rows = [];
            $i = 1;
            foreach ($enrolls as $enrol) {
                $firstAuction = CommitmentAuction::where('enrollment_id', $enrol->id)
                    ->where('installment_no', 1)
                    ->first();
                $firstDue = $firstAuction ? (float) $firstAuction->due_amount : 0;
                $paid = (float) Receipt::where('enrollment_id', $enrol->id)->sum('received_amount');
                $rfAmount = (float) ($enrol->advance_amount ?? 0);
                $target = $firstDue > 0 ? $firstDue : $rfAmount;
                if ($target <= 0) {
                    continue;
                }
                $pct = ($paid / $target) * 100;
                if ($paid < ($target * 0.9)) {
                    continue;
                }
                $enrol->sno = $i++;
                $enrol->first_installment_due = $target;
                $enrol->paid_amount_total = $paid;
                $enrol->payment_percent = round($pct, 2);
                $enrol->customer_info = $enrol->customer_det ?? [];
                $enrol->scheme_info = $enrol->scheme_det ?? [];
                $enrol->slab_info = $enrol->slab_due_det ?? [];
                if (!empty($enrol->slab_info) && isset($enrol->slab_info->slab_name)) {
                    $enrol->slab_info->slab_name = \App\Models\ChitDetail::formatSlabWithArNo($enrol->slab_info->slab_name, $enrol->ar_no ?? null);
                }
                $enrol->branch_info = $enrol->branch_det ?? [];
                $rows[] = $enrol;
            }
            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error')], 401);
        }
    }

    /**
     * Approve temp chit: update existing chit_details row only (no new insert);
     * insert remaining rows in commit_auctions (installments 2..N).
     */
    public function approve_commitment_eligibility(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required',
            'payment_type' => 'required',
            'agent_type' => 'required',
            'collection_pin' => 'required',
            'commission_amount' => 'required',
            'confirm_approval' => 'required|in:1,true,yes,on',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }
        try {
            $enrol = ChitDetail::find($request->enrollment_id);
            if (!$enrol || (int) $enrol->chit_type !== 3) {
                return response()->json(['status' => 'Error', 'msg' => 'Enrollment not found'], 200);
            }
            if ((string) $enrol->eligibility_status !== '0') {
                return response()->json(['status' => 'Error', 'msg' => 'Already approved'], 200);
            }

            $enrol->eligibility_status = 1;
            $enrol->payment_type = $request->payment_type;
            $enrol->agent_type = $request->agent_type;
            $enrol->collection_area_id = $request->collection_area_id ?? $enrol->collection_area_id;
            $enrol->collection_pin = $request->collection_pin;
            $enrol->collection_agent_id = $request->collection_agent_id ?? $enrol->collection_agent_id;
            $enrol->introducer_id = $request->introducer_id ?? $enrol->introducer_id;
            $enrol->introducer_name = $request->introducer_name ?? $enrol->introducer_name;
            $enrol->ar_no = $request->ar_no ?? $enrol->ar_no;
            $parsedIntroduced = $this->parseApiDateInput($request->date_introduced);
            if ($parsedIntroduced) {
                $enrol->date_introduced = $parsedIntroduced;
            }
            $parsedDueDate = $this->parseApiDateInput($request->due_date);
            if ($parsedDueDate) {
                $enrol->due_date = $parsedDueDate;
            }
            $commission = (float) $request->commission_amount;
            $enrol->total_business_agent_commission = $commission;
            $enrol->balance_business_agent_commission = $commission;
            $enrol->paid_business_agent_commission = 0;
            $enrol->save();

            $this->syncRemainingCommitmentAuctions($enrol);

            return response()->json([
                'status' => 'Success',
                'msg' => 'Customer approved for SPN commitment chit',
                'data' => $enrol,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => $e->getMessage() ?: \Lang::get('api.global_error'),
            ], 200);
        }
    }

    /**
     * After approval: refresh installment 1 date and insert commit_auctions 2..N only.
     */
    private function syncRemainingCommitmentAuctions(ChitDetail $enrol)
    {
        $slab_id = $enrol->slab_id;
        $scheme_id = $enrol->scheme_id;
        if (empty($scheme_id) && $slab_id) {
            $scheme_id = SlabDue::where('id', $slab_id)->value('schemes_id');
        }
        $no_of_months = (int) Scheme::where('id', $scheme_id)->value('no_of_months');
        if ($no_of_months <= 0 && $slab_id) {
            $slabRow = SlabDue::find($slab_id);
            if ($slabRow) {
                for ($m = 1; $m <= 60; $m++) {
                    $col = 'due_' . $m;
                    if (!isset($slabRow->$col) || $slabRow->$col === null || $slabRow->$col === '') {
                        break;
                    }
                    $no_of_months = $m;
                }
            }
        }

        $baseDueDate = $enrol->due_date ? date('Y-m-d', strtotime($enrol->due_date)) : date('Y-m-d');

        CommitmentAuction::where('enrollment_id', $enrol->id)
            ->where('installment_no', 1)
            ->update(['auction_date' => $baseDueDate]);

        $startInstallment = 2;
        if (!CommitmentAuction::where('enrollment_id', $enrol->id)->where('installment_no', 1)->exists()) {
            $startInstallment = 1;
        }

        for ($i = $startInstallment; $i <= $no_of_months; $i++) {
            if (CommitmentAuction::where('enrollment_id', $enrol->id)->where('installment_no', $i)->exists()) {
                continue;
            }
            $auction_date = date('Y-m-d', strtotime($baseDueDate . ' +' . ($i - 1) . ' month'));
            $payment = 'due_' . $i;
            $due_amount = SlabDue::where('id', $slab_id)->pluck($payment);
            $amount = isset($due_amount[0]) ? ($due_amount[0]) : 0;

            $enrol_auc = new CommitmentAuction();
            $enrol_auc->tenant_id = $enrol->tenant_id;
            $enrol_auc->branch_id = $enrol->branch_id;
            $enrol_auc->customer_id = $enrol->customer_id;
            $enrol_auc->enrollment_id = $enrol->id;
            $enrol_auc->due_amount = $amount;
            $enrol_auc->installment_amount = $amount;
            $enrol_auc->installment_no = $i;
            $enrol_auc->auction_date = $auction_date;
            $enrol_auc->save();
        }
    }

    /**
     * Parse ngbDatepicker object or date string from API request.
     */
    private function parseApiDateInput($dateInput)
    {
        if (is_array($dateInput) && isset($dateInput['year'])) {
            return date('Y-m-d', strtotime(
                ($dateInput['year']) . '-' . sprintf('%02d', ($dateInput['month'])) . '-' . sprintf('%02d', ($dateInput['day']))
            ));
        }
        if (is_string($dateInput) && $dateInput !== '' && $dateInput !== '[object Object]') {
            $ts = strtotime(str_replace('/', '-', $dateInput));
            if ($ts) {
                return date('Y-m-d', $ts);
            }
        }
        return null;
    }
	
	public function get_cancel_enroll_customers(Request $request) {
        try {
            $cond = $request->conditions;
            $cancel_customers = ChitDetail::where($cond)->onlyTrashed()->get();
			foreach($cancel_customers as $cancel_customer) {
			//print_r($cancel_customer->id); echo '-';
                $cancel_customer->customer_info = isset($cancel_customer->customer_det) ? ($cancel_customer->customer_det) : []; 
				$cancel_customer->customer_info->name = $cancel_customer->customer_det->name . ' ' . $cancel_customer->customer_det->last_name . '/' . $cancel_customer->ticket_no; 
                $cancel_customer->group_info = isset($cancel_customer->group_det) ? ($cancel_customer->group_det) : []; 
            } 
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $cancel_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_cancel_customers_payment_details(Request $request) {
        try {
            $cond = $request->conditions; 
			$cancel_customers = ChitDetail::where($cond)->withTrashed()->get();
			foreach($cancel_customers as $cancel_customer) {
                //advance receipts
                $before_receipt=AdvanceReceipt::selectRaw('sum(receipt_amount) as total_paid_receipt')->where('enrollment_id',$cancel_customer->id)->get();
				$before_paid_rec_amount = isset($before_receipt[0]->total_paid_receipt) ? ($before_receipt[0]->total_paid_receipt) : 0;
                $before_paid_amount = $before_paid_rec_amount;
                
                $receipt_advance=Receipt::selectRaw('sum(received_amount) as total_paid_advance')->where('enrollment_id',$cancel_customer->id)->where('payment_type_id','=',7)->get();
				$paid_rec_amount_advance = isset($receipt[0]->total_paid_advance) ? ($receipt[0]->total_paid_advance) : 0;
                $paid_amount_advance = $paid_rec_amount_advance;
                
                $advance_not_adjust = $before_paid_amount - $paid_amount_advance;
                
               //paid_amount
				$receipt=Receipt::selectRaw('sum(received_amount) as total_paid')->where('enrollment_id',$cancel_customer->id)->where('payment_type_id','!=',7)->get();
				$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
				$paid_amount = $paid_rec_amount;
				
				//penalty added 
				$cancel_penalty_amount = Payment::where(['enrollment_id' => $cancel_customer->id, 'payment_for' => 3, 'status'=>1])->sum('cancel_penalty_amount');	
				
				$cancel_customer->overall_paid_amount = $paid_amount - $cancel_penalty_amount +$advance_not_adjust;
				
			    //release amount calculation
			    $cancel_amount_payment = Payment::where(['enrollment_id' => $cancel_customer->id, 'payment_for' => 3, 'status'=>1])->sum('total_amount');	
				
			    $cancel_customer->cancel_amount_payment = $cancel_amount_payment;
            } 
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $cancel_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_commitment_enrol(Request $request) {
		try {
            if(isset($request->id)) {
                $enrol = ChitDetail::find($request->id); 
                $enrol->deleted_by = $request->deleted_by;
                $enrol->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($enrol->save()) {
                    $enrol->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_enrol_deleted');
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
	
	public function store_payment_refund(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {                
				$payment = new Payment();
				$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $payment->branch_id = isset($request->branch_id) ? $request->branch_id : '';
                $payment->customer_id = isset($request->customer_id) ? $request->customer_id : '';
                $payment->group_id = isset($request->group_id) ? $request->group_id : '';
                $payment->enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : '';
				if(isset($request->payment_date)) {
                    $s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                    $payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                }
                $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
                $payment->payment_for = "3";
                $payment->payment_type_id = isset($request->payment_type_id) ? $request->payment_type_id : 0;
                $payment->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
                $payment->cancel_penalty_amount = isset($request->penalty_amount) ? $request->penalty_amount : 0;
                $payment->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
                $payment->bank_branch_name = isset($request->bank_branch_name) ? $request->bank_branch_name : '';
                $payment->cheque_no = isset($request->cheque_no) ? $request->cheque_no : '';
				if(isset($request->cheque_date)) {
                    $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                    $payment->cheque_date = date('Y-m-d',strtotime($s_date)); 
                }
                $payment->cheque_status = isset($request->cheque_status) ? $request->cheque_status : '';
                $payment->transaction_no = isset($request->transaction_no) ? $request->transaction_no : 0;
                $payment->transaction_date = isset($request->transaction_date) ? $request->transaction_date : '0000-00-00';
				if(isset($request->transaction_date)) {
                    $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                    $payment->transaction_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $payment->transaction_date = '0000-00-00';
                }
				$payment->debit_to = isset($request->debit_to) ? $request->debit_to : '';
				$payment->created_by = isset($request->created_by) ? $request->created_by : 0;
                $payment->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2){
							$payment->status = isset($request->status) ? ($request->status) : 2;
						}
                if($request->total_amount >0)
                {
    				if($payment->save()) {
    					$response['status'] = 'Success';
    					$response['msg'] = \Lang::get('api.success_payment_added');
    					$response['data'] = $payment;
    					return response()->json($response,200);
    				}
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }                    
                }
                else
                {
                    $response['status'] = 'Error';
                    $response['msg'] = "Refund Amount Not found";
                    return response()->json($response,200); 
                }
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }
	
	public function get_payment_refund(Request $request) {
        try {
            $cond = $request->conditions; 	
            $payments = Payment::where($cond)->orderBy('id','desc')->get();
            foreach($payments as $payment) {
                $payment->tenant_info = ($payment->tenant_det) ? ($payment->tenant_det) : [];                    
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->scheme_info = ($payment->scheme_det) ? ($payment->scheme_det) : []; 
                $payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
                $payment->group_info = ($payment->group_det) ? ($payment->group_det) : []; 
            }
			$response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function payment_refund_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';
			$cond['payment_for'] = '3';         	
        	if(isset($request->group_id)){
        		$cond['group_id'] = $request->group_id; 
        	}
        	if(isset($request->start_date)) { 
        	    $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}				
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}
            if(($start_date=='') && ($end_date=='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->get();
                }
                if($request->branch_id == 0){ 
                    $payments = Payment::where($cond)->get();
                }

            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0){ 
                    $payments = Payment::where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0){ 
                    $payments = Payment::where($cond)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }

            } 
			$i=1;
			if(count($payments)>0) 
            {
                $grand_tot_amount = 0;
                $tot_amount = 0;
                foreach($payments as $payment) {
                    $payment->sno = $i;
                    $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : [];
                    $payment->group_name = isset($payment->group_det->group_name) ? ($payment->group_det->group_name) : [];
                    $payment->scheme_info = isset($payment->scheme_det) ? ($payment->scheme_det) : [];
                    $payment->enrollment_info = isset($payment->enrol_det) ? ($payment->enrol_det) : [];
                    $payment->bank_name = isset($payment->bank_det->bank_name) ? ($payment->bank_det->bank_name) : [];
                    $payment->bank_branch_name = isset($payment->bank_det->bank_branch_name) ? ($payment->bank_det->bank_branch_name) : [];
                    $payment->employee_name = isset($payment->employee_det->first_name) ? ($payment->employee_det->first_name) : [];
    				$payment->customer_name = isset($payment->customer_det->name) ? ($payment->customer_det->name) : [];
                    $payment->auction_Info = isset($payment->auction_det) ? ($payment->auction_det) : [];
                    $payment->payment_type_name = isset($payment->payment_type_det->payment_name) ? ($payment->payment_type_det->payment_name) : [];
    				if($payment->status == 1 )
    				{
    					$payment->status_name = "Active";
    				}	
    				else if($payment->status == 2 )
    				{
    					$payment->status_name = "Pending";
    				}		
    				else if($payment->status == 3 )
    				{
    					$payment->status_name = "Return";
    				}	
    				else if($payment->status == 0 )
    				{
    					$payment->status_name = "In-Active";
    				}else
    				{
    					$payment->status_name = "-";
    				}
                    $total_amount = isset($payment->total_amount) ? ($payment->total_amount) : 0;
                    $tot_amount += $total_amount; 
                    $i++;
                } 
                $grand_tot_amount +=$tot_amount;
                
                $grand_tot_amount_format =0;

                if($grand_tot_amount > 0)
                {   
                    $num = $grand_tot_amount;
                    $grand_tot_amount_format = $this->moneyFormatIndia($num);  
                }
                $response['grand_tot_amount'] = $grand_tot_amount_format;
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $payments;
                return response()->json($response,200);
			} else {
			    $payments = [];
                $response['grand_tot_amount'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $payments;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function payment_transfer_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';
				
        	
                    $payments = Payment::where('payment_for',5)->get();
                
			$i=1;
			
			if(count($payments)>0) 
            {
                $grand_tot_amount = 0;
                $tot_amount = 0;
                foreach($payments as $payment) {
                    $payment->sno = $i;
                    $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : [];
					$enrl_id = ChitDetail::where('id','=',$payment->enrollment_id)->onlyTrashed()->get();
                    $payment->group_name = $payment->group_det->group_name . ' / ' .  $enrl_id[0]->ticket_no;
                    $payment->scheme_info = isset($payment->scheme_det) ? ($payment->scheme_det) : [];
                    $payment->enrollment_info = isset($payment->enrol_det) ? ($payment->enrol_det) : [];
                    $payment->bank_name = isset($payment->bank_det->bank_name) ? ($payment->bank_det->bank_name) : [];
                    $payment->bank_branch_name = isset($payment->bank_det->bank_branch_name) ? ($payment->bank_det->bank_branch_name) : [];
                    $payment->employee_name = isset($payment->employee_det->first_name) ? ($payment->employee_det->first_name) : [];
    				$payment->customer_name = $payment->customer_det->name . ' '  . $payment->customer_det->last_name;
                    $payment->auction_Info = isset($payment->auction_det) ? ($payment->auction_det) : [];
                    $payment->payment_type_name = isset($payment->payment_type_det->payment_name) ? ($payment->payment_type_det->payment_name) : [];
                    $payment->penalty = $payment->cancel_penalty_amount;
					$bidding = Customer::where('id','=',$payment->transfer_customer_id)->get();
					$payment->transfer_customer_name = $bidding[0]->name . ' '  . $bidding[0]->last_name;
					$enrl = ChitDetail::where('id','=',$payment->transfer_enrollment_id)->get();
					$group_id = Group::where('id','=',$enrl[0]->group_id)->get();
					$payment->transfer_group = $group_id[0]->group_name;
					$payment->transfer_ticket_no = $enrl[0]->ticket_no;
					$payment->enrollment_info = isset($payment->enrol_det) ? ($payment->enrol_det) : [];
    				if($payment->status == 1 )
    				{
    					$payment->status_name = "Active";
    				}	
    				else if($payment->status == 2 )
    				{
    					$payment->status_name = "Pending";
    				}		
    				else if($payment->status == 3 )
    				{
    					$payment->status_name = "Return";
    				}	
    				else if($payment->status == 0 )
    				{
    					$payment->status_name = "In-Active";
    				}else
    				{
    					$payment->status_name = "-";
    				}
                    $total_amount = isset($payment->total_amount) ? ($payment->total_amount) : 0;
                    $tot_amount += $total_amount; 
                    $i++;
                } 
                $grand_tot_amount +=$tot_amount;
                
                $grand_tot_amount_format =0;

                if($grand_tot_amount > 0)
                {   
                    $num = $grand_tot_amount;
                    $grand_tot_amount_format = $this->moneyFormatIndia($num);  
                }
                $response['grand_tot_amount'] = $grand_tot_amount_format;
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $payments;
                return response()->json($response,200);
			} else {
			    $payments = [];
                $response['grand_tot_amount'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $payments;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
   
    public function delete_payment_refund(Request $request) {
		try {
            if(isset($request->id)) {
                $enrol = Payment::find($request->id); 
                $enrol->deleted_by = $request->deleted_by;
                $enrol->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($enrol->save()) {
                    $enrol->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_enrol_deleted');
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
                return response()->json($response, 401);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }
	
	public function get_agent_commision_payment_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';        	
        	if(isset($request->agent_id)){
        		$cond['agent_id'] = $request->agent_id; 
        	}
        	if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}			
		    if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}
            if(($start_date=='') && ($end_date=='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->where($cond1)->groupby('commission_no')->get();
                }
                if($request->branch_id == 0){
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->groupby('commission_no')->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->where($cond1)->groupby('commission_no')->get();
                }
                if($request->branch_id == 0){
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->groupby('commission_no')->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->whereBetween('payment_date',[$start_date,$end_date])->where($cond1)->groupby('commission_no')->get();
                }
                if($request->branch_id == 0){
                    $payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid')->where($cond)->whereBetween('payment_date',[$start_date,$end_date])->groupby('commission_no')->get();
                }
            } 
			$i=1;               
            $grand_paid_amt = 0;
            $tot_paid = 0;       
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->total_paid = $payment->total_paid;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->bank_branch_name = isset($payment->branch_name) ? ($payment->branch_name) : '';
                $payment->agent_name = isset($payment->employee_agent_det) ? ($payment->employee_agent_det->first_name.' '.$payment->employee_agent_det->last_name) : '';
                $payment->payment_type_name = isset($payment->payment_type_det) ? ($payment->payment_type_det->payment_name) : '';
				$payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : '';
                $payment->bank_name = isset($payment->bank_name_det) ? ($payment->bank_name_det->bank_name) : '';
                $payment->phone_no = isset($payment->employee_agent_det->mobile_no) ? ($payment->employee_agent_det->mobile_no) : '';
				if($payment->status == 1 )
				{
					$payment->status_name = "Active";
				}	
				else if($payment->status == 2 )
				{
					$payment->status_name = "Pending";
				}		
				else if($payment->status == 3 )
				{
					$payment->status_name = "Return";
				}	
				else if($payment->status == 0 )
				{
					$payment->status_name = "In-Active";
				}else
				{
					$payment->status_name = "-";
				}
                $paid_amount = isset($payment->paid_amount) ? ($payment->paid_amount) : 0;
                $tot_paid += $paid_amount;
                $i++;
            } 
            $grand_paid_amt +=$tot_paid;

            $grand_paid_amt_format =0;

            if($grand_paid_amt > 0)
            {   
                $num = $grand_paid_amt;
                $grand_paid_amt_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_paid_amt'] = $grand_paid_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function list_commision_payment_customers(Request $request) 
    {
        try { 
			$cond = [];
			$cond['commission_no'] = $request->commission_no; 
            $payments = AgentCommissionPayment::where($cond)->orderBy('id','desc')->get();
            $i=1;        
            $grand_paid_amt = 0;
            $tot_paid = 0;    
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : '';
                $payment->customer_name = isset($payment->customer_det->name) ? ($payment->customer_det->name) : '';
				$payment->agent_name = isset($payment->employee_agent_det) ? ($payment->employee_agent_det->first_name.' '.$payment->employee_agent_det->last_name) : '';
				$payment->collection_agent_name = isset($payment->collection_agent_det) ? ($payment->collection_agent_det->first_name.' '.$payment->collection_agent_det->last_name) : '';
				$payment->group_name = isset($payment->group_det) ? ($payment->group_det->group_name) : '';
                $payment->chit_value = isset($payment->scheme_det->chit_value) ? ($payment->scheme_det->chit_value) : '';
                $payment->ticket_no = isset($payment->enrollment_det->ticket_no) ? ($payment->enrollment_det->ticket_no) : '';
				$payment->payment_type_name = isset($payment->payment_type_det->payment_name) ? ($payment->payment_type_det->payment_name) : '';
                $customer_commission_amount = isset($payment->customer_commission_amount) ? ($payment->customer_commission_amount) : 0;
                $tot_paid += $customer_commission_amount;
                $i++;
            }
            $grand_paid_amt +=$tot_paid;

            $grand_paid_amt_format =0;

            if($grand_paid_amt > 0)
            {   
                $num = $grand_paid_amt;
                $grand_paid_amt_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_paid_amt'] = $grand_paid_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_agent_commision_pending_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 =[];
        	if($request->branch_id > 0){
        		$cond1['branch_id'] = $request->branch_id; 
        	}
        	if(isset($request->agent_id)){
        		$cond['agent_id'] = $request->agent_id; 
        	}
        	$cond['agent_type'] = '3';
            $payments = ChitDetail::selectRaw('*,sum(balance_business_agent_commission) as balance_business_agent_commission')->where('balance_business_agent_commission','!=',0)->where($cond)->where($cond1)->groupby('introducer_id')->get();

            if($request->branch_id == 0)
            {
                $payments = ChitDetail::selectRaw('*,sum(balance_business_agent_commission) as balance_business_agent_commission')->where('balance_business_agent_commission','!=',0)->where($cond)->groupby('introducer_id')->get();
            }
			$i=1;     
            $grand_pending_amt = 0;
            $tot_pending = 0;        
            foreach($payments as $payment) {
                $payment->sno = $i;
                $pending_commission = $payment->balance_business_agent_commission;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : [];
				$payment->agent_name = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name.' '.$payment->employee_agent_intro_det->last_name) : '';
                $payment->branch_info = isset($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->employee_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det) : [];
                $payment->employee_full_name_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name. ' '.$payment->employee_agent_intro_det->last_name) : [];
                $payment->pending_commission = $pending_commission;
                $tot_pending += $pending_commission;
                $i++;
            } 
            $grand_pending_amt +=$tot_pending;
            $grand_pending_amt_format =0;

            if($grand_pending_amt > 0)
            {   
                $num = $grand_pending_amt;
                $grand_pending_amt_format = $this->moneyFormatIndia($num);  
            } 
            $response['grand_pending_amt'] = $grand_pending_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function list_commision_pending_customers(Request $request) 
    {
        try { 	
            $cond1 =[];
            if($request->branch_id >0)
            {
                $cond1['branch_id'] = $request->branch_id;
                $payments = ChitDetail::where('introducer_id',$request->agent_id)->where($cond1)->where('balance_business_agent_commission','!=',0)->orderBy('id','desc')->get();
            }
            if($request->branch_id ==0)
            {
                $payments = ChitDetail::where('introducer_id',$request->agent_id)->where('balance_business_agent_commission','!=',0)->orderBy('id','desc')->get();
            }

            $i=1;    
            $grand_chit_amt = 0;
            $tot_chit = 0;     
            $grand_agent_commission = 0;
            $tot_agent_commission = 0;  
            $grand_paid_agent_commission = 0;
            $tot_paid_agent_commission = 0;      
            $grand_pending_agent_commission = 0;
            $tot_pending_agent_commission = 0;     
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_info = isset($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : '';
                $payment->customer_info = isset($payment->customer_det) ? ($payment->customer_det) : [];
                $payment->customer_name = isset($payment->customer_det) ? ($payment->customer_det->name) : '';
                $payment->employee_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det) : [];
                $payment->agent_name = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name.' '.$payment->employee_agent_intro_det->last_name) : '';
				$payment->group_info = isset($payment->group_det) ? ($payment->group_det) : [];
                $payment->group_name = isset($payment->group_det) ? ($payment->group_det->group_name) : '';
                $payment->scheme_info = isset($payment->scheme_det) ? ($payment->scheme_det) : [];
                $payment->ticket_no = isset($payment->ticket_no) ? ($payment->ticket_no) : '';
                $chit_value = '0';
                if($payment->chit_type !=3)
                {
                    $chit_value = isset($payment->group_det->scheme_det->chit_value) ? ($payment->group_det->scheme_det->chit_value) : '0';
                }
                if($payment->chit_type ==3)
                {
                    $chit_value = isset($payment->scheme_det->chit_value) ? ($payment->scheme_det->chit_value) : '0';
                }
                $total_business_agent_commission = isset($payment->total_business_agent_commission) ? ($payment->total_business_agent_commission) : 0;
                $paid_business_agent_commission = isset($payment->paid_business_agent_commission) ? ($payment->paid_business_agent_commission) : 0;
                $balance_business_agent_commission = isset($payment->balance_business_agent_commission) ? ($payment->balance_business_agent_commission) : 0;
                $payment->chit_value = $chit_value;
                $tot_chit += $chit_value;
                $tot_agent_commission += $total_business_agent_commission;
                $tot_paid_agent_commission += $paid_business_agent_commission;
                $tot_pending_agent_commission += $balance_business_agent_commission;
                $i++;
            }
            $grand_chit_amt +=$tot_chit;
            $grand_agent_commission +=$tot_agent_commission;
            $grand_paid_agent_commission +=$tot_paid_agent_commission;
            $grand_pending_agent_commission +=$tot_pending_agent_commission;

            $grand_chit_amt_format =0;
            $grand_agent_commission_format =0;
            $grand_paid_agent_commission_format =0;
            $grand_pending_agent_commission_format =0;

            if($grand_chit_amt > 0)
            {   
                $num = $grand_chit_amt;
                $grand_chit_amt_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_agent_commission > 0)
            {   
                $num = $grand_agent_commission;
                $grand_agent_commission_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_paid_agent_commission > 0)
            {   
                $num = $grand_paid_agent_commission;
                $grand_paid_agent_commission_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_pending_agent_commission > 0)
            {   
                $num = $grand_pending_agent_commission;
                $grand_pending_agent_commission_format = $this->moneyFormatIndia($num);  
            } 
            $response['grand_chit_amt'] = $grand_chit_amt_format;
            $response['grand_agent_commission'] = $grand_agent_commission_format;
            $response['grand_paid_agent_commission'] = $grand_paid_agent_commission_format;
            $response['grand_pending_agent_commission'] = $grand_pending_agent_commission_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function get_business_agent_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 =[];
        	$end_date='';
            $start_date = '';
            if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }        	
        	if(isset($request->employee_agent_id)){
        		$cond['introducer_id'] = $request->employee_agent_id; 
        	}
        	if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}	
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day']));
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}
            if(($start_date=='') && ($end_date=='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = ChitDetail::where($cond)->where($cond1)->where('agent_type',3)->get();
                }
                if($request->branch_id == 0){
                    $payments = ChitDetail::where($cond)->where('agent_type',3)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = ChitDetail::where($cond)->where($cond1)->where('date_introduced', '<=', date('Y-m-d',strtotime($end_date)))->where('agent_type',3)->get();
                }
                if($request->branch_id == 0){
                    $payments = ChitDetail::where($cond)->where('date_introduced', '<=', date('Y-m-d',strtotime($end_date)))->where('agent_type',3)->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id;
                    $payments = ChitDetail::where($cond)->where($cond1)->whereBetween('date_introduced',[$start_date,$end_date])->where('agent_type',3)->get();
                }
                if($request->branch_id == 0){
                    $payments = ChitDetail::where($cond)->whereBetween('date_introduced',[$start_date,$end_date])->where('agent_type',3)->get();
                }
            } 
			$i=1;
			if(count($payments)>0) 
            {
                $grand_chit_value = 0;
                $tot_chit_value = 0;  
                $grand_agent_commission = 0;
                $tot_agent_commission = 0;   
                $grand_bal_agent_commission = 0;
                $tot_bal_agent_commission = 0;     
                foreach($payments as $payment) 
                {    				
                    $payment->sno = $i;
                    $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                    $payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : [];
    				$payment->customer_name = isset($payment->customer_det) ? ($payment->customer_det->name) : [];
                    if($payment->chit_type ==3)
                    {
                        $slab_types = SlabDue::where('id',$payment->slab_id)->get();   
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                        $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $payment->ar_no ?? null);
                        $payment->group_name = "Commitment"."-".$slab_name;
                        $payment->ticket_no = "-";
                        $payment->chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
                    }
                    if($payment->chit_type !=3)
                    {
                        $payment->group_name = isset($payment->group_det) ? ($payment->group_det->group_name) : '';
                        $payment->ticket_no = isset($payment->ticket_no) ? ($payment->ticket_no) : '0';
                        $payment->chit_value = isset($payment->group_det->scheme_det) ? (strval(round($payment->group_det->scheme_det->chit_value))) : '1';
                    }  		
                    $payment->agent_name = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name.' '.$payment->employee_agent_intro_det->last_name) : [];
                    $payment->agent_mobile = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->mobile_no) : [];
                    $payment->customer_mobile = isset($payment->customer_det) ? ($payment->customer_det->mobile_no) : [];
                    $total_business_agent_commission = isset($payment->total_business_agent_commission) ? (strval(round($payment->total_business_agent_commission))) : [];
                    $balance_business_agent_commission = isset($payment->balance_business_agent_commission) ? (strval(round($payment->balance_business_agent_commission))) : [];
                    $payment->total_business_agent_commission = $total_business_agent_commission;
                    $payment->balance_business_agent_commission = $balance_business_agent_commission;
                    $chit_value= isset($payment->chit_value) ? ($payment->chit_value) : 0;
                    $tot_chit_value += $chit_value;
                    $tot_agent_commission += $total_business_agent_commission;
                    $tot_bal_agent_commission += $balance_business_agent_commission;
                    
                    $i++;
                } 
                $grand_chit_value +=$tot_chit_value;
                $grand_agent_commission +=$tot_agent_commission;
                $grand_bal_agent_commission +=$tot_bal_agent_commission;

                $grand_chit_value_format =0;
                $grand_agent_commission_format =0;
                $grand_bal_agent_commission_format =0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                } 
                if($grand_agent_commission > 0)
                {   
                    $num = $grand_agent_commission;
                    $grand_agent_commission_format = $this->moneyFormatIndia($num);  
                } 
                if($grand_bal_agent_commission > 0)
                {   
                    $num = $grand_bal_agent_commission;
                    $grand_bal_agent_commission_format = $this->moneyFormatIndia($num);  
                } 
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['grand_agent_commission'] = $grand_agent_commission_format;
                $response['grand_bal_agent_commission'] = $grand_bal_agent_commission_format;
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $payments;
                return response()->json($response,200);
			} 
            else {
				$payments = [];
                $response['grand_chit_value'] = 0;
                $response['grand_agent_commission'] = 0;
                $response['grand_bal_agent_commission'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $payments;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_enrollment_transactions(Request $request) {
        try {
            $cond = $request->conditions;
            $bidding_history = BiddingDetail::where($cond)->get();
            if((isset($request->from_sno)) && (isset($request->no_of_lines_to_print))) {
                $bidding_history = BiddingDetail::where($cond)->offset(($request->from_sno)-1)->limit($request->no_of_lines_to_print)->get();
            }
            $transaction_history = [];
            $i=1;
            foreach($bidding_history as $bh) {
                $bidding_det = [];
                $bidding_det['sno'] = $i;
                $bidding_det['transaction_date'] = date('d/m/Y');
                $bidding_det['installment_no'] = $bh->installment_no;
                $bidding_det['receipt_no'] = 0;
                $bidding_det['payment_mode'] = 'Cash';
                $bidding_det['received_amount'] = '1000';
                $bidding_det['penalty'] = '100';
                $bidding_det['bonus'] = '100';
                $bidding_det['total'] = '1000';
                $bidding_det['balance'] = '100';
                array_push($transaction_history,$bidding_det);
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $transaction_history;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function get_commitment_customers(Request $request) {
        try {
            if($request->id >0)
            {
                $commitment_customers = ChitDetail::where('branch_id',$request->id)->where('chit_type','3')->get(); 
            }
            if($request->id ==0)
            {
                $commitment_customers = ChitDetail::where('chit_type','3')->get(); 
            }
            foreach($commitment_customers as $commitment_customer) {
                $commitment_customer->customer_info = isset($commitment_customer->customer_det) ? ($commitment_customer->customer_det) : [];
				$commitment_customer->customer_info->name = $commitment_customer->customer_det->name . ' ' . $commitment_customer->customer_det->last_name . ' / ' . $commitment_customer->customer_det->customer_code;
                $customer_name = $commitment_customer->customer_det->name . ' ' . $commitment_customer->customer_det->last_name;
                $phone_no = isset($commitment_customer->customer_det) ? ($commitment_customer->customer_det->mobile_no) : 0;
                $commitment_customer->customer_name_det = $customer_name. " / ". $phone_no;
			}
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $commitment_customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cheque_return_payment_refund(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_return_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $payment = Payment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                } 
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $payment->status = isset($request->status) ? ($request->status) : 3;
                if($payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $payment;
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }
	
	public function cheque_cleared_payment_refund(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_debit_to' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $payment = Payment::find($request->id);                
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $payment->status = isset($request->status) ? ($request->status) : 1;
                if($payment->save()) {
                   
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $payment;
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                $response['msg'] =$e;
                return response()->json($response, 401);
            }
        }
    }

    public function get_enrolls_for_customer_ledger(Request $request) {
        
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $enrolls = ChitDetail::where($cond)->orderBy('id','desc')->withTrashed()->get();
            if($enrolls) {  
                $i=1;
                foreach($enrolls as $enrol) {
                    $enrol->sno = $i;
                    $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];                    
                    $enrol->branch_info = isset($enrol->branch_det) ? ($enrol->branch_det) : [];
                    $enrol->branch_name = isset($enrol->branch_det->branch_name) ? ($enrol->branch_det->branch_name) : '';
                    $enrol->name = isset($enrol->customer_det->name) ? ($enrol->customer_det->name) : []; 
                    $enrol->customer_info =  isset($enrol->customer_det) ? ($enrol->customer_det) : []; 
                    $enrol->mobile_no = isset($enrol->customer_det->mobile_no) ? ($enrol->customer_det->mobile_no) : ''; 
                    $enrol->group_info = isset($enrol->group_det) ? ($enrol->group_det) : [];
                    $enrol->group_name = isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : '';
                    $enrol->scheme_info = [];
                    if(isset($enrol->group_det)) {
                        $scheme_det = Scheme::find($enrol->group_det->scheme_id);
                        $enrol->scheme_info = isset($scheme_det) ? ($scheme_det) : [];
                    }
                    $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                    $enrol->collection_area_info = isset($enrol->collection_area_det) ? ($enrol->collection_area_det) : [];
                    $enrol->collection_area = isset($enrol->collection_area_det) ? ($enrol->collection_area_det->collection_area) : '-';
                    $enrol->date_introduced = isset($enrol->date_introduced) ? date('d/m/Y',strtotime($enrol->date_introduced)) : "0000-00-00"; 
                    $enrol->introducer_full_name = '';
                    $enrol->introducer_info = [];
                    if($enrol->agent_type==1) { 
                        //customer
                        $introducer_det = Customer::find($enrol->introducer_id);
                        $enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                    }
                    else if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                        //employee
                        $introducer_det = Employee::find($enrol->introducer_id);
                        $enrol->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                        $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                    } 
                    else
                    {
                       $enrol->introducer_info = isset($enrol->introducer_id) ? ($enrol->introducer_id) : [];
                       $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : []; 
                    }
                    $enrol->enrol_name = "";
                    $enrol->chit_value = 0;
                    if($enrol->chit_type != 3)
                    {
                        if(isset($enrol->group_det)) {
                            $enrol->enrol_name = "GROUP NAME : ".$enrol->group_det->group_name.' | TICKET NO : '.$enrol->ticket_no;
                             $enrol->chit_value = isset($enrol->group_det->scheme_det) ? (strval(round($enrol->group_det->scheme_det->chit_value))) : 0;
                        }
                    }
                    if($enrol->chit_type == 3)
                    {
                        $slab_types = SlabDue::where('id',$enrol->slab_id)->get();   
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                        $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrol->ar_no ?? null);
                        $enrol->enrol_name = "GROUP NAME:Commitment | SLAB NAME :".$slab_name;
                        $enrol->chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
                    }
                     $i++;
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_agent_incentive_payment_report(Request $request) {
        try { 
            $cond = [];
            $cond1 = [];
            $end_date='';
            $start_date = '';            
            if(isset($request->agent_id)){
                $cond['agent_id'] = $request->agent_id; 
            }
            if(isset($request->start_date)) { 
                $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                $start_date = date('Y-m-d',strtotime($s_date)); 
            }           
            if(isset($request->end_date)) {
                $s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
                $end_date = date('Y-m-d',strtotime($s_date)); 
            }
            if(($start_date=='') && ($end_date=='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->where($cond1)->groupby('commission_no')->orderBy('id','desc')->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->groupby('commission_no')->orderBy('id','desc')->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->where($cond1)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->groupby('commission_no')->orderBy('id','desc')->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->groupby('commission_no')->orderBy('id','desc')->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->where($cond1)->whereBetween('payment_date',[$start_date,$end_date])->groupby('commission_no')->orderBy('id','desc')->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = AgentIncentivePayment::selectRaw('*,sum(customer_incentive_amount) as total_paid')->where($cond)->whereBetween('payment_date',[$start_date,$end_date])->groupby('commission_no')->orderBy('id','desc')->get();
                }
            } 
            $i=1;
            $grand_paid = 0;
            $tot_paid = 0;
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->total_paid = $payment->total_paid;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->bank_branch_name = isset($payment->branch_name) ? ($payment->branch_name) : '';
                $payment->agent_name = isset($payment->employee_agent_det) ? ($payment->employee_agent_det->first_name.' '.$payment->employee_agent_det->last_name) : '';
                $payment->payment_type_name = isset($payment->payment_type_det) ? ($payment->payment_type_det->payment_name) : '';
                $payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : '';
                $payment->bank_name = isset($payment->bank_name_det) ? ($payment->bank_name_det->bank_name) : '';
                $payment->phone_no = isset($payment->employee_agent_det->mobile_no) ? ($payment->employee_agent_det->mobile_no) : '';
                if($payment->status == 1 )
                {
                    $payment->status_name = "Active";
                }   
                else if($payment->status == 2 )
                {
                    $payment->status_name = "Pending";
                }       
                else if($payment->status == 3 )
                {
                    $payment->status_name = "Return";
                }   
                else if($payment->status == 0 )
                {
                    $receipt->status_name = "In-Active";
                }else
                {
                    $receipt->status_name = "-";
                }

                // agent_type_name
                if($payment->agent_type_id == 1 )
                {
                    $payment->agent_type_name = "Customer";
                }   
                else if($payment->agent_type_id == 2 )
                {
                    $payment->agent_type_name = "Employee";
                }       
                else if($payment->agent_type_id == 3 )
                {
                    $payment->agent_type_name = "Business Agent";
                } 
                else
                {
                    $payment->agent_type_name = "-";
                } 
                $paid_amount = isset($payment->paid_amount) ? ($payment->paid_amount) : '';
                $tot_paid += $paid_amount;
                $i++;
            } 
            $grand_paid +=$tot_paid;

            $grand_paid_format =0;

            if($grand_paid > 0)
            {   
                $num = $grand_paid;
                $grand_paid_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_paid'] = $grand_paid_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function list_incentive_payment_customers(Request $request) 
    {
        try { 
            $cond = [];
            $cond['commission_no'] = $request->commission_no; 
            $payments = AgentIncentivePayment::where($cond)->orderBy('id','desc')->get();
            $i=1;
            $grand_paid = 0;
            $tot_paid = 0;
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : '';
                $payment->customer_name = isset($payment->customer_det->name) ? ($payment->customer_det->name) : '';
                $payment->agent_name = isset($payment->employee_agent_det) ? ($payment->employee_agent_det->first_name.' '.$payment->employee_agent_det->last_name) : '';
                $payment->collection_agent_name = isset($payment->collection_agent_det) ? ($payment->collection_agent_det->first_name.' '.$payment->collection_agent_det->last_name) : '';
                if($payment->enrollment_det->chit_type != 3)
                {
                    $group_name =isset($payment->enrollment_det->group_det->group_name) ? ($payment->enrollment_det->group_det->group_name) : '';
                    $chit_value = isset($enrol->enrollment_det->group_det) ? ($enrol->enrollment_det->group_det->scheme_det->chit_value) : [];
                }
                if($payment->enrollment_det->chit_type == 3)
                {   
                    $slab_types = SlabDue::where('id',$payment->enrollment_det->slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $payment->ar_no ?? null);
                    $group_name = "Commitment ".$slab_name;
                    $chit_value = isset($payment->enrollment_det->scheme_det->chit_value) ? ($payment->enrollment_det->scheme_det->chit_value) : [];
                }
                $payment->group_name = $group_name;
                $payment->chit_value = $chit_value;
                $payment->ticket_no = isset($payment->enrollment_det->ticket_no) ? ($payment->enrollment_det->ticket_no) : '';
                $payment->payment_type_name = isset($payment->payment_type_det->payment_name) ? ($payment->payment_type_det->payment_name) : '';
                // agent_type_name
                if($payment->agent_type_id == 1 )
                {
                    $payment->agent_type_name = "Customer";
                }   
                else if($payment->agent_type_id == 2 )
                {
                    $payment->agent_type_name = "Employee";
                }       
                else if($payment->agent_type_id == 3 )
                {
                    $payment->agent_type_name = "Business Agent";
                } 
                else
                {
                    $payment->agent_type_name = "-";
                } 
                $customer_incentive_amount = isset($payment->customer_incentive_amount) ? ($payment->customer_incentive_amount) : '';
                $tot_paid += $customer_incentive_amount;
                $i++;
            }
            $grand_paid +=$tot_paid;

            $grand_paid_format =0;

            if($grand_paid > 0)
            {   
                $num = $grand_paid;
                $grand_paid_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_paid'] = $grand_paid_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    
    public function get_agent_incentive_pending_report(Request $request) {
        try { 
            $cond = [];
            $cond1 = [];            
            if(isset($request->agent_id)){
                $cond['agent_id'] = $request->agent_id; 
            }
            if(isset($request->agent_type_id)){
                $cond['agent_type'] = $request->agent_type_id; 
            }
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $payments = ChitDetail::selectRaw('*,sum(balance_agent_incentive) as balance_agent_incentive')->where('balance_agent_incentive','!=',0)->where($cond)->where($cond1)->groupby('introducer_id')->get();
            }
            if($request->branch_id == 0){
                $payments = ChitDetail::selectRaw('*,sum(balance_agent_incentive) as balance_agent_incentive')->where('balance_agent_incentive','!=',0)->where($cond)->groupby('introducer_id')->get();
            }
            $i=1;
            $grand_pending = 0;
            $tot_pending = 0;
            foreach($payments as $payment) {
                $payment->sno = $i;
                $pending_commission = $payment->balance_agent_incentive;
                $payment->pending_commission = $pending_commission;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_name = isset($payment->branch_det->branch_name) ? ($payment->branch_det->branch_name) : [];
                $payment->agent_name = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name.' '.$payment->employee_agent_intro_det->last_name) : '';
                if($payment->chit_type != 3)
                {
                    if(isset($payment->group_det)) {
                        $payment->group_name = $payment->group_det->group_name;
                        $payment->chit_value = isset($payment->group_det->scheme_det) ? (strval(round($payment->group_det->scheme_det->chit_value))) : 0;
                    }
                }
                if($payment->chit_type == 3)
                {
                    $slab_types = SlabDue::where('id',$payment->slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $payment->ar_no ?? null);
                    $payment->group_name = "Commitment ".$slab_name;
                    $payment->chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
                }
                $payment->branch_info = isset($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->employee_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det) : [];
                $payment->employee_full_name_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name. ' '.$payment->employee_agent_intro_det->last_name) : [];
                 // agent_type_name
                if($payment->agent_type_id == 1 )
                {
                    $payment->agent_type_name = "Customer";
                }   
                else if($payment->agent_type_id == 2 )
                {
                    $payment->agent_type_name = "Employee";
                }       
                else if($payment->agent_type_id == 3 )
                {
                    $payment->agent_type_name = "Business Agent";
                } 
                else
                {
                    $payment->agent_type_name = "-";
                } 
                $tot_pending += $pending_commission;
                $i++;
            } 
            $grand_pending +=$tot_pending;

            $grand_pending_format =0;

            if($grand_pending > 0)
            {   
                $num = $grand_pending;
                $grand_pending_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_pending'] = $grand_pending_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function list_incentive_pending_customers(Request $request) 
    {
        try {   
            $cond1 =[];
            if($request->branch_id >0)
            {
                $cond1['branch_id'] =$request->branch_id;
                $payments = ChitDetail::where('introducer_id',$request->agent_id)->where($cond1)->where('agent_type',$request->agent_type_id)->where('balance_agent_incentive','!=',0)->orderBy('id','desc')->get();
            }
            if($request->branch_id ==0)
            {
                $payments = ChitDetail::where('introducer_id',$request->agent_id)->where('agent_type',$request->agent_type_id)->where('balance_agent_incentive','!=',0)->orderBy('id','desc')->get();
            }
            $i=1;
            $grand_chit_value = 0;
            $tot_chit = 0;
            $grand_agent_incent = 0;
            $tot_agent_incent = 0;
            $grand_paid_agent_incent = 0;
            $tot_paid_agent_incent = 0;
            $grand_balance_agent_incent = 0;
            $tot_balance_agent_incent = 0;
            foreach($payments as $payment) {
                $payment->sno = $i;
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_info = isset($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : '';
                $payment->customer_info = isset($payment->customer_det) ? ($payment->customer_det) : [];
                $payment->customer_name = isset($payment->customer_det) ? ($payment->customer_det->name) : '';
                $payment->employee_info = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det) : [];
                $payment->agent_name = isset($payment->employee_agent_intro_det) ? ($payment->employee_agent_intro_det->first_name.' '.$payment->employee_agent_intro_det->last_name) : '';
                $payment->group_info = isset($payment->group_det) ? ($payment->group_det) : [];
                $payment->group_name = isset($payment->group_det) ? ($payment->group_det->group_name) : '';
                $payment->scheme_info = isset($payment->scheme_det) ? ($payment->scheme_det) : [];
                $payment->ticket_no = isset($payment->ticket_no) ? ($payment->ticket_no) : '';
                $chit_value = '0';
                if($payment->chit_type !=3)
                {
                    $chit_value = isset($payment->group_det->scheme_det->chit_value) ? ($payment->group_det->scheme_det->chit_value) : '0';
                }
                if($payment->chit_type ==3)
                {
                    $chit_value = isset($payment->scheme_det->chit_value) ? ($payment->scheme_det->chit_value) : '0';
                }
                $payment->chit_value = $chit_value;
                $agent_incentive = isset($payment->agent_incentive) ? ($payment->agent_incentive) : '0';
                $paid_agent_incentive = isset($payment->paid_agent_incentive) ? ($payment->paid_agent_incentive) : '0';
                $balance_agent_incentive = isset($payment->balance_agent_incentive) ? ($payment->balance_agent_incentive) : '0';
                $tot_chit += $chit_value;
                $tot_agent_incent += $agent_incentive;
                $tot_paid_agent_incent += $paid_agent_incentive;
                $tot_balance_agent_incent += $balance_agent_incentive;
                $i++;
            }
            $grand_chit_value +=$tot_chit;
            $grand_agent_incent +=$tot_agent_incent;
            $grand_paid_agent_incent +=$tot_paid_agent_incent;
            $grand_balance_agent_incent +=$tot_balance_agent_incent;

            $grand_chit_value_format =0;
            $grand_agent_incent_format =0;
            $grand_paid_agent_incent_format =0;
            $grand_balance_agent_incent_format =0;

            if($grand_chit_value > 0)
            {   
                $num = $grand_chit_value;
                $grand_chit_value_format = $this->moneyFormatIndia($num);  
            }
            if($grand_agent_incent > 0)
            {   
                $num = $grand_agent_incent;
                $grand_agent_incent_format = $this->moneyFormatIndia($num);  
            }
            if($grand_paid_agent_incent > 0)
            {   
                $num = $grand_paid_agent_incent;
                $grand_paid_agent_incent_format = $this->moneyFormatIndia($num);  
            }
            if($grand_balance_agent_incent > 0)
            {   
                $num = $grand_balance_agent_incent;
                $grand_balance_agent_incent_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_chit_value'] = $grand_chit_value_format;
            $response['grand_agent_incent'] = $grand_agent_incent_format;
            $response['grand_paid_agent_incent'] = $grand_paid_agent_incent_format;
            $response['grand_balance_agent_incent'] = $grand_balance_agent_incent_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function employee_wise_outstanding(Request $request)
    {
        try
        {
            $cond = [];
            $cond1 =[];
            if(isset($request->tenant_id))
            {
                $cond['tenant_id'] = $request->tenant_id;
            }
            if($request->branch_id > 0)
            {
                $cond1['branch_id'] = $request->branch_id;
            }
            if(isset($request->employee_id))
            {
                $cond['id'] = $request->employee_id;
            }
            $employee = Employee::where($cond)->where($cond1)->where('employee_type',1)->orderBy('id','Desc')->get();
            if($request->branch_id == 0)
            {
                $employee = Employee::where($cond)->where('employee_type',1)->orderBy('id','Desc')->get();
            }
            $response_data = [];       
            $i = 1;  
            $tot_paid = 0;
            $grand_tot_paid = 0;
            $tot_pending = 0;
            $grand_tot_pending = 0;
            foreach($employee as $emp)
            {
                $emp_det = [];  
                $tot_pend_tot =0;  
                $tot_paid_tot =0;     
                $emp_det['sno'] = $i;       
                $emp_det['employee_id'] = isset($emp->id) ? ($emp->id) : 0;
                $emp_det['branch_name'] = isset($emp->branch_det) ? ($emp->branch_det->branch_name) : '';
                $emp_det['employee_name'] = isset($emp->first_name) ? ($emp->first_name.' '.$emp->last_name) : '';
                $emp_det['mobile_no']= isset($emp->mobile_no) ? ($emp->mobile_no) : 0;         
                $emp_det['overall_total_pending'] =0;
                $emp_det['overall_total_paid'] =0;       
                $coll_area_mapping = CollectionAreaMapping::where('employee_id',$emp->id)->get();
                foreach($coll_area_mapping as $map)
                {      
                    $collection_area_id = unserialize($coll_area_mapping[0]->collection_area_id);
                    $id_list = [];
                    foreach($collection_area_id as $area)
                    {      
                        array_push($id_list,$area['item_id']);
                    }     
                    $count = count($id_list);
                    for ($j=0; $j <$count ; $j++)
                    {                         
                        $chit_detail = ChitDetail::where('status','=',1)->where('collection_area_id',$id_list[$j])->get();
                        foreach($chit_detail as $entrol)
                        {           
                            $commit_total_installment =0;
                            $bid_total_installment = 0; 
                            $group_id=   $entrol->group_id;
                            $customer_id=   $entrol->customer_id;
                            $enrollment_id = $entrol->id;
                            if($entrol->chit_type == 3)
                            { 
                            $bidding = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
                            $commit_install_amt = isset($bidding) ? ($bidding) : "0";
                            $commit_total_installment =+  $commit_install_amt;
                            }                            
                            if($entrol->chit_type != 3)
                            {
                                $bidding = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install_amt')->where('group_id',$group_id)->groupBy('group_id')->get();
                              
                                $bid_install_amt = isset($bidding[0]->total_install_amt) ? ($bidding[0]->total_install_amt) : "0";
                                $bid_total_installment =+  $bid_install_amt;
                            }                                 
                            $total_install = $bid_total_installment+ $commit_total_installment;
                            $receipt=Receipt::selectRaw('*, sum(received_amount) + sum(bonus) as total_received')->where('enrollment_id',$enrollment_id)->where('employee_id',$emp->id)->where('status','=',1)->get();

                            $total_paid = isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0";
                            $total_pending = $total_install -  $total_paid;
                            $tot_pend_tot += $total_pending;
                            $tot_paid_tot += $total_paid; 
                        }                    
                    }   
                }
                $emp_det['overall_total_paid'] = $tot_paid_tot;
                $emp_det['overall_total_pending'] = $tot_pend_tot;
                $tot_paid += $tot_paid_tot;
                $tot_pending +=$tot_pend_tot;
                $i++;
                array_push($response_data,$emp_det);
            }
            $grand_tot_paid += $tot_paid;
            $grand_tot_pending += $tot_pending;

            $grand_tot_paid_format =0;
            $grand_tot_pending_format =0;

            if($grand_tot_paid > 0)
            {   
                $num = $grand_tot_paid;
                $grand_tot_paid_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_pending > 0)
            {   
                $num = $grand_tot_pending;
                $grand_tot_pending_format = $this->moneyFormatIndia($num);  
            }

            $response['grand_tot_paid'] = $grand_tot_paid_format;
            $response['grand_tot_pending'] = $grand_tot_pending_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    Public function customer_wise_employee_outstanding(Request $request)
    {
        try
        {
            $cond = [];
            if(isset($request->employee_id))
            {
                $cond['employee_id'] = $request->employee_id;
            }            
            $response_data = [];      
            $coll_area_mapping = CollectionAreaMapping::where($cond)->get();
            $tot_chit_value = 0;
            $grand_tot_chit_value = 0;
            $tot_pending_amt = 0;
            $grand_tot_pending_amt = 0;
            foreach($coll_area_mapping as $map)
            {   
                $i = 1;  
                $tot_pend_tot = 0;
                $tot_paid_tot =0;
                $collection_area_id = unserialize($coll_area_mapping[0]->collection_area_id);
                $id_list = [];
                foreach($collection_area_id as $area)
                {      
                    array_push($id_list,$area['item_id']);
                }     
                $count = count($id_list);
                for ($j=0; $j <$count ; $j++)
                {                    
                    $chit_detail = ChitDetail::where('status','=',1)->where('collection_area_id',$id_list[$j])->get();
                    foreach($chit_detail as $entrol)
                    {       
                        $chit_det = [];                             
                        $commit_total_installment =0;
                        $bid_total_installment = 0; 
                        $group_id=   isset($entrol->group_id) ? ($entrol->group_id) : 0;
                        $customer_id=   $entrol->customer_id;
                        $enrollment_id = $entrol->id;
                        $chit_det['sno'] = $i;
                        $chit_det['enrol_id'] =$enrollment_id;
                        $chit_det['cust_id'] =$customer_id;
                        $chit_det['group_id'] =$group_id;
                        $chit_det['customer_name'] = isset($entrol->customer_det) ? ($entrol->customer_det->name) : '';
                        $chit_det['mobile_no'] = isset($entrol->customer_det) ? ($entrol->customer_det->mobile_no) : '';
                        $chit_value = 0;
                        if($entrol->chit_type == 3)
                        { 
                            $slab_types = SlabDue::where('id',$entrol->slab_id)->get();   
                            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                            $chit_det['group_name'] = "Commitment | SLAB NAME :".$slab_name;
                            $chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
                            $chit_det['ticket_no'] = "-"; 

                            $bidding = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
                            $commit_install_amt = isset($bidding) ? ($bidding) : "0";
                            $commit_total_installment =+  $commit_install_amt;
                        }     
                        $chit_det['chit_value'] = $chit_value;                       
                        if($entrol->chit_type != 3)
                        {
                            if(isset($entrol->group_det)) {
                                $chit_det['group_name'] = isset($entrol->group_det->group_name) ? ($entrol->group_det->group_name) : '';
                                $chit_value = isset($entrol->group_det->scheme_det) ? (strval(round($entrol->group_det->scheme_det->chit_value))) : 0;
                                $chit_det['ticket_no'] = isset($entrol->ticket_no) ? ($entrol->ticket_no) : '-';
                            }                            
                            $chit_det['chit_value'] = $chit_value;
                            $bidding = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install_amt')->where('group_id',$group_id)->groupBy('group_id')->get();
                          
                            $bid_install_amt = isset($bidding[0]->total_install_amt) ? ($bidding[0]->total_install_amt) : "0";
                            $bid_total_installment =+  $bid_install_amt;
                        }                                 
                        $total_install = $bid_total_installment+ $commit_total_installment;
                        $receipt=Receipt::selectRaw('*, sum(received_amount) + sum(bonus) as total_received')->where('enrollment_id',$enrollment_id)->where($cond)->where('status','=',1)->get();

                        $total_paid = isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0";
                        $total_pending = $total_install -  $total_paid;
                        $tot_pend_tot += $total_pending;
                        $tot_paid_tot += $total_paid; 
                        $chit_det['pending_amount'] = $total_pending;
                        $tot_chit_value += $chit_value;
                        $tot_pending_amt += $total_pending;
                        $i++;
                        array_push($response_data,$chit_det);
                    }  
                }             
            }
            $grand_tot_chit_value += $tot_chit_value;
            $grand_tot_pending_amt += $tot_pending_amt;

            $grand_tot_chit_value_format =0;
            $grand_tot_pending_amt_format =0;

            if($grand_tot_chit_value > 0)
            {   
                $num = $grand_tot_chit_value;
                $grand_tot_chit_value_format = $this->moneyFormatIndia($num);  
            }
            if($grand_tot_pending_amt > 0)
            {   
                $num = $grand_tot_pending_amt;
                $grand_tot_pending_amt_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_tot_chit_value'] = $grand_tot_chit_value_format;
            $response['grand_tot_pending_amt'] = $grand_tot_pending_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	Public function customer_wise_enrol_details(Request $request)
    {
        try
        {
            $cond = [];            
			
			if(isset($request->customer_id)){
				$cond['customer_id'] = $request->customer_id;   
			}           
            $enrolls = ChitDetail::where($cond)->get();
            
			$k=1;			
            foreach($enrolls as $enroll) { 
                $enroll->sno = $k;               
                if($enroll->chit_type !=3)
                {    
                    $enroll->branch_name = isset($enroll->branch_det) ? ($enroll->branch_det->branch_name) : [];
                    $enroll->group_name = isset($enroll->group_det) ? ($enroll->group_det->group_name) : [];
                    $enroll->slab_name = $enroll->group_name; // ledger column label is "Slab Name"
                    $chit_value = isset($enroll->group_det->scheme_det) ? (float) ($enroll->group_det->scheme_det->chit_value) : 0;
					$enroll->chit_value = round($chit_value * (float) ($enroll->ticket_type ?? 1), 2);
					
    				$group_id = isset($enroll->group_id) ? ($enroll->group_id) : 0;

                    $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0;
                    $branch_id = isset($enroll->branch_id) ? ($enroll->branch_id) : 0;
    				//total_installment_amount
    				$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
    				$enroll->overall_installment_amount = $total_installment_amount;
					
    				if($enroll->prized_status==0)
					{
						$enroll->prized_status_customer="Non-Prized";
						$enroll->prized_amount = 0;
						$enroll->prized_pending_amount = 0;
					} else {
						
						$enroll->prized_status_customer="Prized";
						$bid_amt_det = BiddingDetail::where('enrollment_id',$enrollment_id)->get();
						
						$enroll->prized_amount = $bid_amt_det[0]->customer_released;
						
						$enroll->prized_pending_amount = $bid_amt_det[0]->pending_release;
					}
    				
    				//paid_amount
    				$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
    				$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
    				$bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                    $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
    				$paid_amount = $paid_rec_amount + $bonus_amount;
    				$rec_inst_no=0;
    				
    				//pending installments
    				$max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
    				$rec_inst_no = Receipt::where('id','=',$max_rec_id_inst)->pluck('installment_no');
					$total_installment_amount = $total_installment_amount * $enroll->ticket_type;

    				//pending_amount 
    				$pending_amount = $total_installment_amount - $paid_amount;

                    //auction wise details 
                    $inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count(); 
                    $auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
                    $i=0;
                    $install_det = [];                  
                    $total_penalty_amounts = 0;
                    $total_bonus_amounts = 0;
                    $total_pending_days = 0;
                    $total_pending_installments =0;
                    $cr_date = date('Y-m-d');
                    $inst_amt = 0;
                    foreach($auc_wise_inst_amts as $auc_wise_inst_amt) 
                    {
                        $cust_det = [];     
                        $penalty = 0;                   
                        $bonus = 0;                 
                        $inst_amt = $auc_wise_inst_amt->current_installment_amount;
                        $auction_id = $auc_wise_inst_amt->id;
                        $auc_id  = $auc_wise_inst_amt->id;
                        $installment_no  = $auc_wise_inst_amt->installment_no;
                        $auc_date_inst  = $auc_wise_inst_amt->auction_date;
                        $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_id, 'status' => 1])->get();   
                        $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                        $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                        if(empty($cus_rec_amt[0])) { 
                            $amount_paid = 0;
                        } 
                        else {
                            $amount_paid_rec = $cus_rec_amt[0]->total_paid;
                            $amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
                            $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                        }
                        $ins_amt_wise_rec = $inst_amt - $amount_paid;
                        if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
                            $receipt_date = $last_receipt_date_inst[0];
                            $current_date = date('Y-m-d');
                            $datetime1 = new DateTime($receipt_date);
                            $datetime2 = new DateTime($current_date);
                            $interval = $datetime1->diff($datetime2);
                            $pending_days = $interval->format('%a');
                            $i++;
                        } 
                        else {
                            $receipt_date = $auc_date_inst;
                            $current_date = date('Y-m-d');
                            $datetime1 = new DateTime($receipt_date);
                            $datetime2 = new DateTime($current_date);
                            $interval = $datetime1->diff($datetime2);
                            $pending_days = $interval->format('%a');
                            $i++;
                        }
                        // Bonus days and percentage by heirrachy over all 
                        if($enroll->customer_det->bonus_days!=0 && $enroll->customer_det->customer_bonus!=0)
                        {
                          $bonus_days = $enroll->customer_det->bonus_days;
                          $bonus_percentage = $enroll->customer_det->customer_bonus;
                        }    
                        elseif($enroll->group_det->group_bonus_days!=0 && $enroll->group_det->group_base_bonus!=0)
                        {
                          $bonus_days = $enroll->group_det->group_bonus_days;
                          $bonus_percentage = $enroll->group_det->group_base_bonus;
                        }
                        elseif($enroll->branch_det->bonus_days!=0 && $enroll->branch_det->bonus!=0)
                        {
                          $bonus_days = $enroll->branch_det->bonus_days;
                          $bonus_percentage = $enroll->branch_det->bonus;              
                        }    
                        else
                        {
                          $bonus_days = 0;
                          $bonus_percentage = 0;
                        }           
                        
                        $bid = BiddingDetail::Where('group_id',$group_id)->max('id');
                        $check_auction = BiddingDetail::where('id',$bid)->get();
                        $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                        // Auction on holiday check for bonus
                        $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$enroll->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                        $holiday_count = 0;
                        if(count($auction_on_holiday)>0)
                        {   
                            $holiday_count += $auction_on_holiday->count();
                        }

                        $bonus_days_include_holiday = $bonus_days + $holiday_count;
                        if($pending_days <=  $bonus_days_include_holiday)
                        { 
                          if($ins_amt_wise_rec==$inst_amt)
                          { 
                            $bonus_calculation = $inst_amt * $bonus_percentage / 100 ; 
                            $bonus = strval(round($bonus_calculation));
                            $cust_det['bonus'] = $bonus;
                          }
                          else
                          {
                            $cust_det['bonus'] = 0;
                          }
                        }
                        else
                        {
                          $cust_det['bonus'] = 0;
                        }
                        
                        // Penalty Calculation installment wise
                        if($enroll->customer_det->penalty_days!=0 )
                        {
                          $inst_penalty_days = $enroll->customer_det->penalty_days;
                          $inst_penalty_percentage = $enroll->customer_det->customer_penalty_interest;
                        }
                        elseif($enroll->group_det->group_penalty_days!=0 )
                        {
                          $inst_penalty_days = $enroll->group_det->group_penalty_days;
                          $inst_penalty_percentage = $enroll->group_det->group_base_penalty;
                        }
                        elseif($enroll->branch_det->penalty_days!=0)
                        {
                          $inst_penalty_days = $enroll->branch_det->penalty_days;
                          $inst_penalty_percentage = $enroll->branch_det->branch_wise_penalty;
                        }
                        else
                        {
                          $inst_penalty_days = "0";
                          $inst_penalty_percentage = "0";
                        }

                        $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                        $pend_days_int = 0;    
                        $cust_det['penalty_amounts'] = 0;   
                        
                        if($pending_days > $inst_penalty_days)
                        {           
                            $branch=Branch::where('id',$branch_id)->get();
                            if($enroll->prized_status==0)
                            {
                                $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                                $pend_days_int = $pending_days * ($non_prize/ 100);
                                $current_month_days = date('t'); 
                                $total_days_int = $pend_days_int / $current_month_days;
                                $penalty = $total_days_int * $ins_amt_wise_rec;
                                $cust_det['penalty_amounts'] = strval(round($penalty));
                            } 
                            else
                            {               
                                $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                                $pend_days_int = $pending_days * ($prize / 100);
                                $current_month_days = date('t'); 
                                $total_days_int = $pend_days_int / $current_month_days;
                                $penalty = $total_days_int * $ins_amt_wise_rec;
                                $cust_det['penalty_amounts'] = strval(round($penalty));
                            }            
                        }  
                        // Pending Installment 
                        $rcpt = Receipt::where('enrollment_id','=',$enrollment_id)->get();
                        $auc_pendign_count = 0;
                        $rcpt_pendign_count =0;
                        $recpt_auc_id  = isset($rcpt[0]->auction_id) ? ($rcpt[0]->auction_id) : 0;
                        if($recpt_auc_id != $auc_id)
                        {
                            $auc_pendign_count += 1;
                        }
                        if($amount_paid != $inst_amt)
                        {
                            if($recpt_auc_id == $auc_id)
                            {
                               $rcpt_pendign_count += 1;
                            }                        
                        }
                        $pendign_count = $auc_pendign_count + $rcpt_pendign_count;

                        $cust_det['installment_no'] = $installment_no;
                        $cust_det['inst_amt_wise'] = $inst_amt;
                        $cust_det['amount_paid'] = $amount_paid;
                        $cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
                        $cust_det['pending_days'] = $pending_days;
                        $cust_det['bonus_inst_wise'] = 0;
                        $cust_det['penalty_inst_wise'] = 0;
                        $cust_det['discount_inst_wise'] = 0;
                        $cust_det['discount_penalty_wise'] = 0;
                        $cust_det['auction_id'] = $auction_id;
                        $cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
                        $cust_det['cancel_dividend_amount'] =0;
                        if($ins_amt_wise_rec>0) {
                            array_push($install_det,$cust_det);
                        }   
                        $total_penalty_amounts += $penalty;
                        $total_bonus_amounts += $bonus;
                        $total_pending_installments +=$pendign_count;
                    };
                    $auc_wise_inst_id = isset($auc_wise_inst_amts[0]->id) ? ($auc_wise_inst_amts[0]->id) : 0;
                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_wise_inst_id, 'status' => 1])->get();
                    $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                    $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                    if(empty($cus_rec_amt[0])) { 
                        $amount_paid = 0;
                    } 
                    else {
                        $amount_paid_rec = $cus_rec_amt[0]->total_paid;
                        $amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
                        $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                    }
                    $auction_date = isset($auc_wise_inst_amts[0]->auction_date) ? ($auc_wise_inst_amts[0]->auction_date) : '0000-00-00';
                    $ins_amt_wise_rec1 = $inst_amt - $amount_paid;
                    if($ins_amt_wise_rec1 !=$inst_amt && $inst_amt_no !=0) {
                        $receipt_date = $last_receipt_date_inst[0];
                        $current_date = date('Y-m-d');
                        $datetime1 = new DateTime($receipt_date);
                        $datetime2 = new DateTime($current_date);
                        $interval = $datetime1->diff($datetime2);
                        $pending_days1 = $interval->format('%a');
                        $i++;
                    } 
                    else {
                        $receipt_date = $auction_date;
                        $current_date = date('Y-m-d');
                        $datetime1 = new DateTime($receipt_date);
                        $datetime2 = new DateTime($current_date);
                        $interval = $datetime1->diff($datetime2);
                        $pending_days1 = $interval->format('%a');
                        $i++;
                    }
                    $enroll->bonus = $total_bonus_amounts;
                    $enroll->penalty = round($total_penalty_amounts);
                    $enroll->pending_amount = round($pending_amount + $total_penalty_amounts);
                    $enroll->paid_amount = round($paid_rec_amount);
                    $enroll->discount = 0;
                    $enroll->pending_days = $pending_days1;
                    $enroll->pending_inst = $total_pending_installments;
                }
                else if($enroll->chit_type == 3)
                {
                    $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0;
                    $slab_types = SlabDue::where('id', $enroll->slab_id)->get();
                    $enroll->slab_name = \App\Models\ChitDetail::formatSlabWithArNo(isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : '', $enroll->ar_no ?? null);
                    $chit_value = isset($slab_types[0]->scheme_det) ? (float) ($slab_types[0]->scheme_det->chit_value) : 0;
                    $enroll->chit_value = round($chit_value, 2);

                    $total_installment_amount = CommitmentAuction::where('enrollment_id', $enrollment_id)->sum('due_amount');
                    // Paid = receipts cumulative received_amount (no bonus) by enrollment_id
                    $paid_amount = (float) Receipt::where('enrollment_id', $enrollment_id)
                        ->where('status', 1)
                        ->sum('received_amount');
                    // Pending = commitment_auctions total due_amount - paid
                    $pending_amount = max(0, (float) $total_installment_amount - $paid_amount);

                    $enroll->paid_amount = round($paid_amount, 2);
                    $enroll->pending_amount = round($pending_amount, 2);
                    $enroll->advance_amount = round((float) ($enroll->advance_amount ?? 0), 2);

                    if($enroll->prized_status == 0)
                    {
                        $enroll->prized_amount = 0;
                        $enroll->prized_pending_amount = 0;
                    }
                    else
                    {
                        $paymentReleased = Payment::where('enrollment_id', $enrollment_id)->where('status', 1)->sum('total_amount');
                        $totalRelease = (float) ($enroll->total_payment_release ?? 0);
                        if($totalRelease <= 0)
                        {
                            $totalRelease = $chit_value;
                        }
                        $enroll->prized_amount = round((float) $paymentReleased, 2);
                        $enroll->prized_pending_amount = round(max(0, $totalRelease - $paymentReleased), 2);
                    }
                }
                $k++;  
            } 
			
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function moneyFormatIndia($num)
    {       
        $nums = explode(".",$num);
        if(count($nums)>2){
            return "0";
        }
        else{
            if(count($nums)==1){
                $nums[1]="00";
            } 
            $num = $nums[0];
            $explrestunits = "" ;
            if(strlen($num)>3){
                $lastthree = substr($num, strlen($num)-3, strlen($num));
                $restunits = substr($num, 0, strlen($num)-3); 
                $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; 
                $expunit = str_split($restunits, 2);
                for($i=0; $i<sizeof($expunit); $i++){

                    if($i==0)
                    {
                        $explrestunits .= (int)$expunit[$i].","; 
                    }else{
                        $explrestunits .= $expunit[$i].",";
                    }
                }
                $thecash = $explrestunits.$lastthree; 
                return $thecash;
            } else {
                $thecash = $num;
                return $thecash;
            }  
        }
    } 
    public function store_pledge_chit(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'customer_id'=>'required',
            //'enrollment_id'=>'required',
            'pledge_customer_id'=>'required',
            //'pledge_enrollment_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                
                $chit_pledge=new ChitPledge(); 
                $chit_pledge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
                $chit_pledge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                //$chit_pledge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0; 
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
				$chit_pledge->customer_id = $customer_id;				
                $chit_pledge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
                //$chit_pledge->pledge_customer_id = isset($request->pledge_customer_id) ? ($request->pledge_customer_id) : 0;
				$pledge_customer = isset($request->pledge_customer_id[0]['customer_id']) ? ($request->pledge_customer_id[0]['customer_id']) : 0;
				$chit_pledge->pledge_customer_id = $pledge_customer;
                $chit_pledge->pledge_enrollment_id = isset($request->pledgeenrollment_id) ? ($request->pledgeenrollment_id) : 0;
                //$chit_pledge->pledge_date =  0;
				if(isset($request->pledge_date)) {
							$r_date = ($request->pledge_date['year']).'-'.sprintf('%02d',($request->pledge_date['month'])).'-'.sprintf('%02d',($request->pledge_date['day'])); 
							$chit_pledge->pledge_date = date('Y-m-d',strtotime($r_date)); 
						}
						else {
							$chit_pledge->pledge_date = '0000-00-00';
						}
               $chit_pledge->unpledge_date =  '0000-00-00';
			   $chit_pledge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $chit_pledge->status = isset($request->status) ? ($request->status) : 1;
                if($chit_pledge->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_pledge_added');
                    $response['data']=$chit_pledge;
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,200);
                }
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
    }
	
	
	public function get_pledge_chit(Request $request) {

       try {
           //id,tenant_id
            $cond = [];     

			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;   
			}   		   
			
			if($request->customer_id > 0){
				$cond['customer_id'] = $request->customer_id;   
			}        
		   
		   
           $enrolls = ChitPledge::where($cond)->orderBy('id','desc')->get();
				if($enrolls) {
							    $i=1;
								foreach($enrolls as $enrol) {
									$enrol->sno = $i;
									$enrol->branch_name = isset($enrol->branch_det->branch_name) ? ($enrol->branch_det->branch_name) : '';
									$enrol->name = isset($enrol->customer_det->name) ? ($enrol->customer_det->name) : [];
									$enrol->ticket_no = isset($enrol->enrollment_det) ? ($enrol->enrollment_det->ticket_no) : [];
									$enrol->group_name = isset($enrol->enrollment_det->group_det) ? ($enrol->enrollment_det->group_det->group_name) : []; 
									$enrol->chit_value = isset($enrol->enrollment_det->group_det) ? ($enrol->enrollment_det->group_det->scheme_det->chit_value) : []; 
									
									$enrol->pledge_ticket_no = isset($enrol->pledge_enrollment_det) ? ($enrol->pledge_enrollment_det->ticket_no) : [];
									$enrol->pledge_name = isset($enrol->pledge_customer_det->name) ? ($enrol->pledge_customer_det->name) : [];
								    $enrol->pledge_group_name = isset($enrol->pledge_enrollment_det->group_det) ? ($enrol->pledge_enrollment_det->group_det->group_name) : []; 
                                    $enrol->pledge_chit_value = isset($enrol->pledge_enrollment_det->group_det) ? ($enrol->pledge_enrollment_det->group_det->scheme_det->chit_value) : []; 
									$enrol->date_introduced = isset($enrol->date_introduced) ? date('d/m/Y',strtotime($enrol->date_introduced)) : "0000-00-00";
										if($enrol->status == 1){
											$enrol->status = 'Active';
										}
										else if($enrol->status == 2){
											$enrol->status = 'Closed';
										}
										else{
											$enrol->status = '-';
										}
									$i++;

								}

												}
					$issue_date = date('d-m-Y') ;
							   $response['status'] = 'Success';
							   $response['msg'] = '';
							   $response['data'] = $enrolls;
					$response['date'] = $issue_date;
							   return response()->json($response,200);
						   }
       catch(\Exception $e) {
           $response['status'] = 'Error';
           $response['msg'] = \Lang::get('api.global_error');
           return response()->json($response, 401);
       }
   }
   
   public function update_pledge(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'tenant_id'=>'required',
          'branch_id'=>'required',
          'enrollment_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $othercharge = ChitPledge::find($request->id); 
                    $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$othercharge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
					$othercharge->pledge_customer_id = isset($request->pledge_customer_id) ? ($request->pledge_customer_id) : 0;
					$othercharge->pledge_enrollment_id = isset($request->pledgeenrollment_id) ? ($request->pledgeenrollment_id) : 0;
					if(isset($request->pledge_date)) {
						$s_date = ($request->pledge_date['year']).'-'.sprintf('%02d',($request->pledge_date['month'])).'-'.sprintf('%02d',($request->pledge_date['day'])); 
						$othercharge->pledge_date = date('Y-m-d',strtotime($s_date)); 
					}
					else {
							$othercharge->pledge_date = '0000-00-00';
					} 
					if(isset($request->unpledge_date)) {
						$s_date = ($request->unpledge_date['year']).'-'.sprintf('%02d',($request->unpledge_date['month'])).'-'.sprintf('%02d',($request->unpledge_date['day'])); 
						$othercharge->unpledge_date = date('Y-m-d',strtotime($s_date)); 
					}
					else {
							$othercharge->unpledge_date = '0000-00-00';
					} 
					$othercharge->remarks = isset($request->remark) ? ($request->remark) : '';
                    $othercharge->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					$othercharge->status = isset($request->status) ? ($request->status) : 0;
                    if($othercharge->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_updated_pledge');
                        $response['data'] =$othercharge;
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
    }
	public function store_payment_transfer(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'tenant_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {                
				$payment = new Payment();
				$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $payment->branch_id = isset($request->branch_id) ? $request->branch_id : '';
                $payment->customer_id = isset($request->customer_id) ? $request->customer_id : '';
                $payment->group_id = isset($request->group_id) ? $request->group_id : '';
                $payment->enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : '';
				if(isset($request->payment_date)) {
                    $s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                    $payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                }
                $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
                $payment->payment_for = "5";
                
                $payment->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
                $payment->cancel_penalty_amount = isset($request->penalty_amount) ? $request->penalty_amount : 0;
                $payment->transfer_customer_id = isset($request->transfer_customer_id) ? $request->transfer_customer_id : 0;
                $payment->transfer_enrollment_id = isset($request->transfer_enrollment_id) ? $request->transfer_enrollment_id : 0;
               
				$payment->created_by = isset($request->created_by) ? $request->created_by : 0;
                $payment->status = isset($request->status) ? ($request->status) : 1;
				if($request->tenant_id == 47){
				$payment->alt_rec_no = isset($request->alt_rec_no) ? ($request->alt_rec_no) : 1;	
				}
                
				
                if($request->total_amount >0)
                {
    				if($payment->save()) {
						$advance_receipt = new AdvanceReceipt(); 
						$receipt_no =''; 
						$advance_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$advance_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						//$advance_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
						$advance_receipt->customer_id = isset($request->transfer_customer_id) ? $request->transfer_customer_id : '';
						$advance_receipt->enrollment_id = isset($request->transfer_enrollment_id) ? ($request->transfer_enrollment_id) : 0;
						$advance_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
						$advance_receipt->status = '1';
						$advance_receipt->receipt_mode = 'ERP';
						$advance_receipt->receipt_amount = isset($request->total_amount) ? ($request->total_amount) : 0;
						if(isset($request->payment_date)) {
						$s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
						$advance_receipt->receipt_date = date('Y-m-d',strtotime($s_date)); 
							}
						$advance_receipt->payment_type_id = "9";	
						$advance_receipt->transfer_from_enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : '';	
						if($advance_receipt->save()) {
                        if($receipt_no=='') {
                            $receipt_no = 'ADV-'.sprintf('%05d', $advance_receipt->id);
                        }                        
                        $advance_receipt->receipt_no = $receipt_no; 
                        if($advance_receipt->save()) { 
                          
    					$response['status'] = 'Success';
    					$response['msg'] = \Lang::get('api.success_payment_added');
    					$response['data'] = $payment;
    					return response()->json($response,200);
    				}
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }                    
                }
					}
                else
                {
                    $response['status'] = 'Error';
                    $response['msg'] = "Refund Amount Not found";
                    return response()->json($response,200); 
                }
				}
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }    
	
	Public function day_book(Request $request)
    {
		
    try
    { 
	$tot_cr = 0;
	$op = 0;
	  if(isset($request->account_date)) { 
	  $s_date = ($request->account_date['year']).'-'.sprintf('%02d',($request->account_date['month'])).'-'.sprintf('%02d',($request->account_date['day']));
	  $account_date = date('Y-m-d',strtotime($s_date)); 
      //print_r($account_date); die();
     // }
	//$account_date = $request->account_date;
	 
	  $s_date2 = ($request->account_date2['year']).'-'.sprintf('%02d',($request->account_date2['month'])).'-'.sprintf('%02d',($request->account_date2['day']));
	  $to_date = date('Y-m-d',strtotime($s_date2));
	  $test = date('Y-m-d', strtotime('-1 day', strtotime($account_date)));
	 
	 
	 $start_date = "2023-04-01";
	 $end_date = $test;
	
	 $before_advance_amount = BeforeEnrolReceipt::whereBetween('received_date',[$start_date,$end_date])->where('status',1)->where('customer_id','!=','0')->where('deleted_at','=',Null)->sum('amount');
	
     $payments_amount = Payment::where('customer_id','!=',1)->whereBetween('payment_date',[$start_date,$end_date])->where('status',1)->where('deleted_at','=',Null)->sum('total_amount');
	 
	 $others_amount = OtherCharge::whereBetween('received_date',[$start_date,$end_date])->where('status',1)->where('deleted_at','=',Null)->sum('amount');
	 
	 $advance_rcpts_amount = AdvanceReceipt::whereBetween('receipt_date',[$start_date,$end_date])->where('status',1)->where('payment_type_id','!=',9)->where('deleted_at','=',Null)->sum('receipt_amount');
	 
	 $rcpts_amount = Receipt::whereBetween('receipt_date',[$start_date,$end_date])->where('payment_type_id','!=',7)->where('payment_type_id','!=',8)->where('status',1)->where('deleted_at','=',Null)->sum('received_amount');
	 
	 $rcpts_amount_penalty = Receipt::whereBetween('receipt_date',[$start_date,$end_date])->where('payment_type_id','!=',7)->where('payment_type_id','!=',8)->where('status',1)->where('deleted_at','=',Null)->sum('penalty');
	 
	 
	 
     $expenses_cash = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('debit_account_head','=','42')->where('status',1)->where('deleted_at','=',Null)->sum('debit_amount');
	 
	 $expenses_bank1 = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('debit_account_head','=','43')->where('status',1)->where('deleted_at','=',Null)->sum('debit_amount');
	 
	 $expenses_bank2 = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('debit_account_head','=','44')->where('status',1)->where('deleted_at','=',Null)->sum('debit_amount');
	 
     $expensescredit_cash = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('credit_account_head','=','42')->where('status',1)->where('deleted_at','=',Null)->sum('credit_amount');
	 
	 $expensescredit_bank1 = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('credit_account_head','=','43')->where('status',1)->where('deleted_at','=',Null)->sum('credit_amount');
	 
	 $expensescredit_bank2 = AccountExpense::whereBetween('entry_date',[$start_date,$end_date])->where('receipt_no','=','yes')->where('credit_account_head','=','44')->where('status',1)->where('deleted_at','=',Null)->sum('credit_amount');
	 
	 $expensesdebit_payment_amt = 0;
	 $expensescredit_rec_amt = 0;
	 $opening_b = 0;
	 
	 
	 if($request->tenant_id == 42){
		
	$expensescredit_rec_amt = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('credit_account_head','!=',0)->where('account_type','=',1)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$start_date,$end_date])->sum('credit_amount');
	 
	$expensesdebit_payment_amt = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('debit_account_head','!=',0)->where('account_type','=',2)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$start_date,$end_date])->sum('debit_amount');
	$opening_b = 2238667.65;
	 }
	
	
     if($request->branch_id == 0)
     {
        $before_advance_rcpts = DB::table("before_enrol_receipts")->where('status',1)->where('deleted_at','=',Null)->where('received_date', '=', $account_date)->select("id","amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id");
	
        $payments = DB::table("payments")->where('status',1)->where('deleted_at','=',Null)->where('payment_date', '=', $account_date)->where('payment_for', '=',1)->select("id","total_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id");
        
        $expenses = DB::table("account_expenses")->where('debit_account_head','!=',0)->where('status',1)->where('deleted_at','=',Null)->where('entry_date', '=', $account_date)->select("id","debit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","debit_account_head as enrollment_id","credit_account_head as customer_id");
        
        $expensescredit = DB::table("account_expenses")->where('credit_account_head','!=',7)->where('credit_account_head','!=',1)->where('credit_account_head','!=',0)->where('status',1)->where('deleted_at','=',Null)->where('entry_date', '=', $account_date)->select("id","credit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","debit_account_head as enrollment_id","credit_account_head as customer_id");
        
        $advance_rcpts = DB::table("advance_receipts")->where('status',1)->where('deleted_at','=',Null)->where('payment_type_id','!=',9)->where('receipt_date', '=', $account_date)->select("id","receipt_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id");
        
        //$opening_balance = DB::table("closing_balances")->where('deleted_at','=',Null)->where('date', '=', $test)->select("id","amount as amount","id as receipt_no","receipt_passbook_mode","id as enrollment_id","id as customer_id");
       
        $receipts =  DB::table("receipts")->where('payment_type_id','!=',7)->where('status',1)->where('deleted_at','=',Null)->where('receipt_date', '=', $account_date)->select("id","received_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id")->unionall($advance_rcpts)->unionall($payments)->unionall($expenses)->unionall($expensescredit)->get();
        
     }
     else{
        
        $before_advance_rcpts = DB::table("before_enrol_receipts")->where('branch_id',$request->branch_id)->where('status',1)->where('deleted_at','=',Null)->whereBetween('received_date',[$account_date,$to_date])->select("id","amount as amount","receipt_no","receipt_passbook_mode","payment_type_id as enrollment_id","customer_id","payment_type_id");
        
        $payments = DB::table("payments")->where('branch_id',$request->branch_id)->where('payment_for', '=',1)->where('status',1)->where('deleted_at','=',Null)->whereBetween('payment_date',[$account_date,$to_date])->select("id","total_amount as amount","id as receipt_no","receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");
        
        // $expenses = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('debit_account_head','=',1)->where('debit_account_head','=',2)->where('debit_account_head','=',3)->where('debit_account_head','=',42)->where('account_type','!=',3)->where('account_type','!=',2)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->select("id","debit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","debit_account_head as enrollment_id","credit_account_head as customer_id","debit_account_head as payment_type_id");
        
        // $expensescredit = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('credit_account_head','!=',0)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->where('account_type','!=',3)->where('account_type','!=',2)->select("id","credit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","debit_account_head as enrollment_id","credit_account_head as customer_id","debit_account_head as payment_type_id");
		
		
		$expenses_contra = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('debit_account_head','!=',0)->where('account_type','=',3)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->select("id","debit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","credit_account_head as enrollment_id","debit_account_head as customer_id","debit_account_head as payment_type_id");
        
        $expensescredit_contra = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('credit_account_head','!=',0)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->where('account_type','=',3)->select("id","credit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","credit_account_head as enrollment_id","debit_account_head as customer_id","debit_account_head as payment_type_id");
		
		
		$expensescredit_rec = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('credit_account_head','!=',0)->where('account_type','=',1)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->select("id","credit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","credit_account_head as enrollment_id","credit_account_head as customer_id","debit_account_head as payment_type_id");
        
        $expensescredit_payment = DB::table("account_expenses")->where('branch_id',$request->branch_id)->where('receipt_no','=','yes')->where('debit_account_head','!=',0)->where('account_type','=',2)->where('status',1)->where('deleted_at','=',Null)->whereBetween('entry_date',[$account_date,$to_date])->select("id","debit_amount as amount","voucher_no as receipt_no","receipt_passbook_mode","credit_account_head as enrollment_id","debit_account_head as customer_id","debit_account_head as payment_type_id");
		
       
        $advance_rcpts = DB::table("advance_receipts")->where('branch_id',$request->branch_id)->where('payment_type_id','!=',9)->where('status',1)->where('deleted_at','=',Null)->whereBetween('receipt_date',[$account_date,$to_date])->select("id","receipt_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");
		
		$other_rec = DB::table("other_charges")->where('branch_id',$request->branch_id)->where('status',1)->where('deleted_at','=',Null)->whereBetween('received_date',[$account_date,$to_date])->select("id","amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");
         
        //$opening_balance = DB::table("closing_balances")->where('branch_id',$request->branch_id)->where('deleted_at','=',Null)->where('date', '=', $test)->select("id","amount as amount","id as receipt_no","receipt_passbook_mode","id as enrollment_id","id as customer_id");
        
        $receipts =  DB::table("receipts")->where('branch_id',$request->branch_id)->where('payment_type_id','!=',7)->where('payment_type_id','!=',8)->where('status',1)->where('deleted_at','=',Null)->whereBetween('receipt_date',[$account_date,$to_date])->select("id","received_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id","payment_type_id")->unionall($advance_rcpts)->unionall($payments)->unionall($other_rec)->unionall($expenses_contra)->unionall($expensescredit_contra)->unionall($expensescredit_payment)->unionall($expensescredit_rec)->get();
     }
 
	 
    
   
	foreach($receipts as $rcpt){
    //print_R($rcpt->receipt_no);echo '-';
	if($rcpt->receipt_passbook_mode == "expenses"){
        
	$rcpt->id = $rcpt->id;
	$debit_remarks = AccountExpense::where('id',$rcpt->id)->pluck('debit_remarks');
	$credit_remarks = AccountExpense::where('id',$rcpt->id)->pluck('credit_remarks');
	
	$rcpt->receipt_no = $rcpt->receipt_no;
	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
    
    if($rcpt->enrollment_id > 0 ){
        $account_head = AccountHead::where('id',$rcpt->enrollment_id)->pluck('name');
        $rcpt->receipt_passbook_mode = $account_head[0] . ' / ' . $debit_remarks[0] . ' / ' . $credit_remarks[0];
         $rcpt->amount = $rcpt->amount;
        $rcpt->payment_amount = '0';
		
				$rcpt->payment_type = $account_head[0];
    }
    else{
        $account_head = AccountHead::where('id',$rcpt->customer_id)->pluck('name');
        $rcpt->receipt_passbook_mode = $account_head[0] . ' / ' . $credit_remarks[0] . ' / ' . $debit_remarks[0];
       
		$rcpt->payment_type = $account_head[0];
		
		 $rcpt->payment_amount = $rcpt->amount;
        $rcpt->amount = '0';
    }
	$rcpt->customer_id = $rcpt->customer_id;
	$pen = 0;
	}  
    else if($rcpt->receipt_passbook_mode == "payment"){
        
    $rcpt->id = $rcpt->id;
	$rcpt->payment_amount = $rcpt->amount;
    $rcpt->amount = '0';
	$rcpt->receipt_no = "PVOC-" . $rcpt->receipt_no;
	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
	$rcpt->enrollment_id = $rcpt->enrollment_id;
	if($rcpt->customer_id > 0){
	$customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
	$last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
	$group_id = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('group_id');
	$ticket_no = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('ticket_no');
	if($group_id[0] > 0){
	$group_name = Group::where('id',$group_id[0])->pluck('group_name');
	}
	}
	$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->pluck('payment_name');
	$rcpt->payment_type = $payment_type[0];
    $rcpt->receipt_passbook_mode = 'Payment Entry of ' . $customer_name[0] . ' ' . $last_name[0] . '- Group name ' . $group_name[0] . ' Ticket No ' . $ticket_no[0];	
	$pen = 0;
	}
    else if($rcpt->receipt_passbook_mode == "op"){
        
        $rcpt->id = $rcpt->id;
        $rcpt->amount = $rcpt->amount;
        $op = $rcpt->amount;
        $rcpt->payment_amount = '0';
        $rcpt->receipt_no = "";
        $rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        $rcpt->enrollment_id = 0;
        $rcpt->receipt_passbook_mode = "OPENING BALANCE";	
		$pen = 0;
        } else if($rcpt->receipt_passbook_mode == "other_rec") {
			
        	$rcpt->id = $rcpt->id;
        	$rcpt->amount = $rcpt->amount;
        	$rcpt->receipt_no = $rcpt->receipt_no;
        	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        	$rcpt->enrollment_id = $rcpt->enrollment_id;
            $rcpt->receipt_passbook_mode = "";
            if($rcpt->customer_id>0){
                $customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
                $last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
                $charge_type_id = OtherCharge::where('id',$rcpt->id)->withTrashed()->pluck('charge_type_id');
                $charge_name = ChargeType::where('id',$charge_type_id[0])->withTrashed()->pluck('charge_name');
				$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->withTrashed()->pluck('payment_name');
				$rcpt->payment_type = $payment_type[0];
                $rcpt->receipt_passbook_mode = $charge_name[0] . ' of ' . $customer_name[0] . ' ' . $last_name[0];   
            }		
			$pen = 0;
    	} else {
			
        	$rcpt->id = $rcpt->id;
			if($rcpt->receipt_passbook_mode == "rcpt"){
				$penalty = Receipt::where('id',$rcpt->id)->pluck('penalty');
				$pen = $penalty[0];
			} else {
				$pen = 0;
			}
			
        	$rcpt->amount = $rcpt->amount + $pen;
        	$rcpt->receipt_no = $rcpt->receipt_no;
        	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        	$rcpt->enrollment_id = $rcpt->enrollment_id;
            $rcpt->receipt_passbook_mode = "";
			
            if($rcpt->customer_id>0){
				$group_name = '';
				$ticket_no = '';
                $customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
				$last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
				 
				if($rcpt->enrollment_id>0){
					
				$group_id = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('group_id');
				if($group_id[0]>0){
				$ticket_no = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('ticket_no');
			
				$group_name = Group::where('id',$group_id[0])->withTrashed()->pluck('group_name');
				
				$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->withTrashed()->pluck('payment_name');
				$rcpt->payment_type = $payment_type[0];
                $rcpt->receipt_passbook_mode = 'Receipt Entry of ' . $customer_name[0] . ' ' . $last_name[0] . '- Group name ' . $group_name[0] . ' Ticket No ' . $ticket_no[0];  
				} else {
				$rcpt->receipt_passbook_mode = 'Commitment Chits ';
				}
				}
			
            }		
			
    	}
	}
	
	$open_bal = $before_advance_amount + $advance_rcpts_amount + $rcpts_amount + $others_amount + $expensescredit_rec_amt + $rcpts_amount_penalty + $opening_b - $payments_amount  - $expensesdebit_payment_amt;
	
	$response['status'] = 'Success';
    $response['msg'] = "";
    $response['data'] = $receipts;
    $response['opening_balance'] = $open_bal;
    $response['start_date'] = date('d-m-Y',strtotime($account_date)) . ' to ' . date('d-m-Y',strtotime($to_date));
    return response()->json($response,200);
	}
    }
    catch(\Exception $e)
    {
    $response['status'] = 'Error';
    $response['statuss'] = $e;
	$response['msg'] = \Lang::get('api.global_error');
	return response()->json($response, 401);
    }
    
    }
	
	Public function customer_ledger_group_advance_details(Request $request){
        try{ 
            
            $cond = $request->conditions;
            $sub_amount = 0;
            $total =0;
            $cr_total = 0;
            $dr_total =0;
            $advance_rcpts = DB::table("advance_receipts")->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","receipt_amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount");
            $receipts =  DB::table("receipts")->where('status',1)->where('payment_type_id','!=',7)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
            
            if((isset($request->from_sno)) && (isset($request->no_of_lines_to_print))) {
                $advance_rcpts = DB::table("advance_receipts")->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","receipt_amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount");
                $receipts =  DB::table("receipts")->where('status',1)->where('payment_type_id','!=',7)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount")->union($advance_rcpts)->offset(($request->from_sno)-1)->limit($request->no_of_lines_to_print)->orderBy('receipt_date','Asc')->get();
                $advance_rcpts_pass = DB::table("advance_receipts")->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","receipt_amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount");
                $receipts_pass =  DB::table("receipts")->where('status',1)->where('payment_type_id','!=',7)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no","receipt_passbook_mode","penalty","bonus","installment_no","group_id","received_amount")->union($advance_rcpts_pass)->limit($request->from_sno-1)->orderBy('receipt_date','Asc')->get();
                
                foreach($receipts_pass as $rcpt_pass){
                    $dr =0;
                    $cr =0;
                    if($rcpt_pass->receipt_passbook_mode != 'rcpt'){
                        $rcpt_pass_det['penalty'] =  0;
                        $rcpt_pass_det['bonus'] =  0;
                        $rcpt_pass_det['dividend'] =  0;
                        $sub_amount = round($rcpt_pass->amount, 0);
                        $rcpt_pass_det['sub_amount'] = $sub_amount;
                        $rcpt_pass_det['amount'] = round($rcpt_pass->amount, 0);
                        $dr_total = $dr_total + $rcpt_pass->amount;
                        $dr = $rcpt_pass->amount;
                    }
                    if($rcpt_pass->receipt_passbook_mode == 'rcpt'){
                        if($rcpt_pass->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt_pass->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt_pass->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt_pass->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt_pass->group_id)->where('installment_no',$rcpt_pass->installment_no)->pluck('current_installment_amount');
                            $rcpt_pass_det['amount'] = 0;
                            $rcpt_pass_det['penalty'] = round($rcpt_pass->penalty, 0);
                            $rcpt_pass_det['bonus'] =  round($rcpt_pass->bonus, 0);
                            $rcpt_pass_det['dividend'] = round($divident[0], 0);
                            $sub_amount = -$inst_amt[0] + $rcpt_pass->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_pass_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_pass_det['receipt_amount'] =  0;
                            $cr_total = $cr_total + $rcpt_pass->amount;
                            $cr = $rcpt_pass->amount;
                        } else {
                            $rcpt_pass_det['amount'] =round($rcpt_pass->received_amount, 0);
                            $rcpt_pass_det['penalty'] = round($rcpt_pass->penalty, 0); 
                            $rcpt_pass_det['bonus'] = round($rcpt_pass->bonus, 0); 
                            $rcpt_pass_det['dividend'] =  round($divident[0], 0);
                            $sub_amount =  $rcpt_pass->received_amount + $rcpt_pass->bonus + $divident[0];
                            $rcpt_pass_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_pass_det['receipt_amount'] = round($rcpt_pass->received_amount, 0);
                            $cr_total = $cr_total + $rcpt_pass->received_amount;
                            $cr = $rcpt_pass->received_amount;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_pass_det['total'] = $total;
                }  
            }
            $response_data=[];
            $balance_avance =0;
            $k=1;
            $from_no = isset($request->from_sno) ? $request->from_sno:0;
            $transaction_history = [];
            if($from_no>1 && $from_no<=24){	
                for($k=0;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";

                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";

                    $rcpt_det['collected_by'] = "";

                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                        $divident[0]=0;
                        } else {
                        $inst_no=$rcpt->installment_no-1;
                        $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;
                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>24 && $from_no<=48){
                for($k=25;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;
                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>48 && $from_no<=72){
                for($k=49;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;
                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>72 && $from_no<=96){
                for($k=73;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>96 && $from_no<=120){
                for($k=97;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";

                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";

                    $rcpt_det['collected_by'] = "";

                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>120 && $from_no<=144){
                for($k=121;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>144 && $from_no<=168){
                for($k=145;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>168 && $from_no<=192){
                for($k=169;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>192 && $from_no<=216){
                for($k=193;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>216 && $from_no<=240){
                for($k=217;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";
                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";
                    $rcpt_det['collected_by'] = "";
                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else if($from_no>240 && $from_no<=264){
                for($k=241;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";

                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";

                    $rcpt_det['collected_by'] = "";

                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            }else if($from_no>264 && $from_no<=288){
                for($k=265;$k<($from_no);$k++){
                    $rcpt_det['date'] = "";
                    $rcpt_det['receipt_no'] =  "";
                    $rcpt_det['amount'] ="";
                    $rcpt_det['penalty'] = "";
                    $rcpt_det['bonus'] = "";
                    $rcpt_det['dividend'] =  "";
                    $rcpt_det['installment_no'] =  "";

                    $rcpt_det['sub_amount'] =  "";
                    $rcpt_det['others'] = "";
                    $rcpt_det['reference_by'] = "";
                    $rcpt_det['receipt_amount'] = "";

                    $rcpt_det['collected_by'] = "";

                    $rcpt_det['retrun_by'] = "";
                    $rcpt_det['return_amount'] = "";
                    $rcpt_det['particulars'] = "";
                    $rcpt_det['payment_mode'] = "";
                    $rcpt_det['sno'] = ".";
                    array_push($response_data,$rcpt_det);
                }
                foreach($receipts as $rcpt){
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            } else {
                foreach($receipts as $rcpt) {
                    $rcpt_det = [];
                    $dr =0;
                    $cr =0;
                    if($rcpt->receipt_passbook_mode != 'rcpt'){
                        $rcpt_det['date'] = $rcpt->receipt_date;
                        $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                        $rcpt_det['penalty'] =  0;
                        $rcpt_det['bonus'] =  0;
                        $rcpt_det['dividend'] =  0;
                        $sub_amount = round($rcpt->amount, 0);
                        $rcpt_det['sub_amount'] = $sub_amount;
                        $rcpt_det['others'] =  0;
                        $rcpt_det['amount'] = round($rcpt->amount, 0);
                        $dr_total = $dr_total + $rcpt->amount;
                        $dr = $rcpt->amount;
                        $rcpt_det['reference_by'] = "";
                        $rcpt_det['receipt_amount'] = round($rcpt->amount, 0);
                        $employee = Employee::where('id',$rcpt->employee_id)->get();
                        $rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
                        // print_r($rcpt->payment_type_id);exit;
                        $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                        $rcpt_det['retrun_by'] = "";
                        $rcpt_det['return_amount'] = 0;
                        $rcpt_det['particulars'] = "Advance";
                        $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                    }
                    if($rcpt->receipt_passbook_mode == 'rcpt'){
                        if($rcpt->installment_no==1){
                            $divident[0]=0;
                        } else {
                            $inst_no=$rcpt->installment_no-1;
                            $divident = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$inst_no)->pluck('divident_amount');
                        }
                        if($rcpt->payment_type_id==7){
                            $inst_amt = BiddingDetail::where('group_id',$rcpt->group_id)->where('installment_no',$rcpt->installment_no)->pluck('current_installment_amount');
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] = 0;
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0);
                            $rcpt_det['bonus'] =  round($rcpt->bonus, 0);
                            $rcpt_det['dividend'] = round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount = -$inst_amt[0] + $rcpt->bonus;
                            $sub_amount_tot = $inst_amt[0];
                            $rcpt_det['sub_amount'] =  round($sub_amount_tot, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] =  0;

                            $cr_total = $cr_total + $rcpt->amount;
                            $cr = $rcpt->amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        } else {
                            $rcpt_det['date'] = $rcpt->receipt_date;
                            $rcpt_det['receipt_no'] =  $rcpt->receipt_no;
                            $rcpt_det['amount'] =round($rcpt->received_amount, 0); 
                            $rcpt_det['penalty'] = round($rcpt->penalty, 0); 
                            $rcpt_det['bonus'] = round($rcpt->bonus, 0); 
                            $rcpt_det['dividend'] =  round($divident[0], 0);
                            $rcpt_det['installment_no'] =  $rcpt->installment_no;
                            $sub_amount =  $rcpt->received_amount + $rcpt->bonus + $divident[0];
                            $rcpt_det['sub_amount'] =   round($sub_amount, 0);
                            $rcpt_det['others'] =  0;
                            $rcpt_det['reference_by'] = "";
                            $rcpt_det['receipt_amount'] = round($rcpt->received_amount, 0);
                            $cr_total = $cr_total + $rcpt->received_amount;
                            $cr = $rcpt->received_amount;
                            $rcpt_det['collected_by'] = "";
                            $payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
                            $rcpt_det['retrun_by'] = "";
                            $rcpt_det['return_amount'] = 0;
                            $rcpt_det['particulars'] = "Receipt";
                            $rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
                        }
                    }
                    $total += $sub_amount;
                    $rcpt_det['total'] = $total;
                    $rcpt_det['sno'] = $k++;
                    $balance_avance = $balance_avance + $dr - $cr;
                    $rcpt_det['balance_avance'] = $balance_avance;
                    //if($rcpt->payment_type_id!=7){
                    array_push($response_data,$rcpt_det);
                    //}
                }  
            }
            if(count($receipts)>0){
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $response_data;
                return response()->json($response,200);
            } else {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.no_data_found');
                return response()->json($response, 200);
            }
        } catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function overall_business_report(Request $request) {
            try {
                if(isset($request->group_id)){
        		$cond['id'] = $request->group_id; 
				}
				if(isset($request->branch_id)){
        		$cond['branch_id'] = $request->branch_id; 
				}
                $groups = Group::where($cond)->get();
                foreach($groups as $group) {
					
                   //paid_amount
				   $paid_amount = 0;
                    $receipt=Receipt::selectRaw('sum(received_amount) as total_paid')->where('group_id',$group->id)->get();
                    $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
                    $paid_amount = $paid_rec_amount;
                   
                    $group->branch_name = isset($group->branch_det) ? ($group->branch_det->branch_name) : []; 
                    $group->completed_auction = BiddingDetail::where('group_id',$group->id)->max('installment_no');
					$no_of_tickets = Scheme::where('id',$group->scheme_id)->pluck('no_of_months');
                    $group->no_of_tickets = $no_of_tickets[0];
					$chit_value = Scheme::where('id',$group->scheme_id)->pluck('chit_value');
                    $group->chit_value = $chit_value[0];
                    $group->group_name = $group->group_name;
					$group->commission_amount = BiddingDetail::where('group_id',$group->id)->sum('commission_amount');
                    
                    //available advance
                    $available_advance = 0;
                    $total_advance = 0;
                    $total_received = 0;
                    $receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('group_id'=>$group->id,'payment_type_id'=>7))->get();
                    $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
                    $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('group_id'=>$group->id,'status'=>1))->get();
                    $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
					
                    $available_advance = $total_advance - $total_received;
                    
                    //penalty added 
                    $group->company_released = Payment::where(['group_id' => $group->id, 'payment_for' => 1, 'status'=>1])->where('customer_id','!=','1')->sum('total_amount');	
                   
                    $group->customer_paid = $paid_amount + $available_advance;
					
					$diff_amt = $group->company_released - $group->customer_paid;
					
					if($diff_amt < 0){
						$group->status = 'Loss';
					} else {
						$group->status = 'Profit';
					}
                   
                    $group->different_amt = $diff_amt;
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
		
		
		public function get_enrol_report_cancel(Request $request) 
    {
		try { 
            $cond = [];
            $cond1 = [];
            if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }        	
        	if(isset($request->group_id)){
        		$cond['group_id'] = $request->group_id; 
        	}
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $enrolls = ChitDetail::where($cond)->where($cond1)->onlyTrashed()->orderBy('id','desc')->get();
            }
            if($request->branch_id == 0)
            {
                $enrolls = ChitDetail::where($cond)->onlyTrashed()->orderBy('id','desc')->get();
            }
            $i=1;
			if(count($enrolls)>0)
            {
                $grand_chit_value = 0;
                $tot_chit = 0;
                foreach($enrolls as $enrol) {	 
                    $enrol->sno = $i;
                    $enrol->tenant_info = isset($enrol->tenant_det) ? ($enrol->tenant_det) : [];
                    $enrol->branch_name = isset($enrol->branch_det->branch_name) ? ($enrol->branch_det->branch_name) : [];
                    $enrol->group_name = isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : [];
                   // $enrol->subscriber_name = isset($enrol->customer_det->name) ? ($enrol->customer_det->name) : [];
					$enrol->subscriber_name = isset($enrol->customer_det) ? ($enrol->customer_det->name . ' ' . $enrol->customer_det->last_name) : [];
                    $enrol->subscriber_mobile_no = isset($enrol->customer_det->mobile_no) ? ($enrol->customer_det->mobile_no) : [];
                    if($enrol->chit_type != 3)
                    {
                        $group_name =isset($enrol->group_det->group_name) ? ($enrol->group_det->group_name) : '';
                        $chit_value = isset($enrol->group_det) ? ($enrol->group_det->scheme_det->chit_value) : 0;
                    }
                    if($enrol->chit_type == 3)
                    {   
                        $slab_types = SlabDue::where('id',$enrol->slab_id)->get();   
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                        $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrol->ar_no ?? null);
                        $group_name = "Commitment ".$slab_name;
                        $chit_value = isset($enrol->scheme_det->chit_value) ? ($enrol->scheme_det->chit_value) : 0;
                    }
                    $enrol->chit_value = $chit_value;
                    $enrol->group_name = $group_name;
                    $enrol->collection_agent_info = isset($enrol->collection_agent_det) ? ($enrol->collection_agent_det) : [];
                    $enrol->introducer_info = [];
                    if(($enrol->agent_type==2) || ($enrol->agent_type==3)) {
                        $introducer_det = Employee::find($enrol->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enrol->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                    }
                    else if($enrol->agent_type==1) {
                        $introducer_det = Customer::find($enrol->introducer_id);
                        //$enrol->introducer_info = isset($introducer_det) ? $introducer_det : [];
                        $enrol->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                    }
                    else {
                        $enrol->introducer_full_name = isset($enrol->introducer_name) ? ($enrol->introducer_name) : [];
                    }
    				if($enrol->agent_type==1) {
    					$enrol->agent_type="Customer";
    				} else if($enrol->agent_type==2) {
    					$enrol->agent_type="Employee";
    				} else if($enrol->agent_type==3) {
    					$enrol->agent_type="Bussiness Agent";
    				} else if($enrol->agent_type==4) {
    					$enrol->agent_type="Self Joining";
    				} else if($enrol->agent_type==5) {
    					$enrol->agent_type="Others";
    				} else {
    					$enrol->agent_type="-";
    				}
    				$before_paid_amount = 0;
					$paid_amount_advance = 0;
					$paid_amount = 0;
					$transfer_amount_payment = 0;
                    $enrol->date_introduced = ($enrol->date_introduced!=0) ? date('d/m/Y',strtotime($enrol->date_introduced)) : '';
					
					$enrol->date_cancel = ($enrol->deleted_at!=0) ? date('d/m/Y',strtotime($enrol->deleted_at)) : '';
					
					$before_receipt=AdvanceReceipt::selectRaw('sum(receipt_amount) as total_paid_receipt')->where('enrollment_id',$enrol->id)->get();
				$before_paid_rec_amount = isset($before_receipt[0]->total_paid_receipt) ? ($before_receipt[0]->total_paid_receipt) : 0;
                $before_paid_amount = $before_paid_rec_amount;
                
                $receipt_advance=Receipt::selectRaw('sum(received_amount) as total_paid_advance')->where('enrollment_id',$enrol->id)->where('payment_type_id','=',7)->get();
				$paid_rec_amount_advance = isset($receipt[0]->total_paid_advance) ? ($receipt[0]->total_paid_advance) : 0;
                $paid_amount_advance = $paid_rec_amount_advance;
                
                $advance_not_adjust = $before_paid_amount - $paid_amount_advance;
                
               //paid_amount
				$receipt=Receipt::selectRaw('sum(received_amount) as total_paid')->where('enrollment_id',$enrol->id)->where('payment_type_id','!=',7)->get();
				$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
				$paid_amount = $paid_rec_amount;
				$cancel_penalty_amount = 0;
				//penalty added 
				$cancel_penalty_amount = Payment::where(['enrollment_id' => $enrol->id, 'payment_for' => 3, 'status'=>1])->sum('cancel_penalty_amount');	
				
				$enrol->paid_amount = $paid_amount+$advance_not_adjust;
				
			    //release amount calculation
			    $cancel_amount_payment = Payment::where(['enrollment_id' => $enrol->id, 'payment_for' => 3, 'status'=>1])->sum('total_amount');	
				$transfer_amount_payment = Payment::where(['enrollment_id' => $enrol->id, 'payment_for' => 5, 'status'=>1])->sum('total_amount');	
			    $enrol->return_amount = $cancel_amount_payment + $cancel_penalty_amount + $transfer_amount_payment;

                    $tot_chit += $chit_value;
                    $i++;
                } 			 
                $grand_chit_value += $tot_chit;

                $grand_chit_value_format = 0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                } 
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $enrolls;
                return response()->json($response,200);
            }
            else
            {
				$enrolls = [];
                $response['grand_chit_value'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $enrolls;
		        return response()->json($response, 200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function get_sms_template(Request $request) {
        try {
			$cond = $request->conditions; 
            $smss = Sms::where($cond)->where('status','=',1)->orderBy('id','desc')->get();
            
            $i=1;
            foreach($smss as $sms) {
                $sms->sno = $i;
				$sms->branch_info = isset($sms->branch_det) ? ($sms->branch_det) : [];
                $sms->template_name = $sms->template_name;
				$sms->description = $sms->description;
               
                $i++;
            }
			$issue_date = date('Y-m-d') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $smss;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
		
		 public function get_sms_trigger(Request $request) {
        try {
            
			$count = count($request->customer_id);
			
			
				for($i=0; $i<$count; $i++)
				{
				$enrolls = ChitDetail::where('id','=',$request->customer_id[$i]['item_id'])->where('group_id','=',$request->group_id)->get();
				
				foreach($enrolls as $enrol) {
				$receiver = Customer::where('id','=',$enrol->customer_id)->pluck('mobile_no');
				$msg_temp = $request->remark;
				
				$receiver = '917418470181';
				$ch = curl_init("https://messageapi.in/chat/sendmessage?id=df13ababdef74f8aa331a732bc14e3cb&phone=$receiver&message=$msg_temp&name=9047388885");
								   
                                    
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            $result = curl_exec($ch);
                            curl_close($ch);
				
				}
				}
			
		   $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $enrolls;
            return response()->json($response,200);
			}
            
			 catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
        }
       
    
		
}