<?php
namespace App\Http\Controllers;

use App\Models\BiddingDetail;
use App\Models\AccountExpense;
use App\Models\Group;
use App\Models\SlabDue;
use App\Models\ChitDetail;
use App\Models\SlabPayment;
use App\Models\Payment;
use App\Models\PaymentMinutes;
use App\Models\Branch;
use App\Models\Scheme;
use App\Models\Receipt;
use App\Models\Employee;
use App\Models\AdvanceReceipt;
use App\Models\GroupBiddingDetail;
use App\Models\BiddingLog;
use App\Models\MessageLog;
use App\Models\User;
use App\Models\BiddingCommissionDetail;
use App\Models\CommitmentAuction;
use App\Models\MinutesOfAuction;
use Illuminate\Http\Request;
use Validator;
use DateTime;

class BiddingDetailController extends Controller
{
    public function store_auction(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
              
                $bidding_detail = new BiddingDetail(); 
				$bidding_detail->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $bidding_detail->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $bidding_detail->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                
                $bidding_detail->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $bidding_detail->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
                $bidding_detail->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
                $bidding_detail->bidding_amount = isset($request->bidding_amount) ? ($request->bidding_amount) : 0; 
                $bidding_detail->commission_amount = isset($request->commission_amount) ? ($request->commission_amount) : 0;
                $bidding_detail->cgst = isset($request->cgst) ? ($request->cgst) : 0;
                $bidding_detail->sgst = isset($request->sgst) ? ($request->sgst) : 0;
                $bidding_detail->igst = isset($request->igst) ? ($request->igst) : 0;
                $bidding_detail->gst = isset($request->gst) ? ($request->gst) : 0;
                $bidding_detail->total_deduction = isset($request->total_deduction) ? ($request->total_deduction) : 0;
                $bidding_detail->pending_due_amount = isset($request->pending_due_amount) ? ($request->pending_due_amount) : 0;
                $bidding_detail->total_release_amount = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
                $bidding_detail->customer_released = isset($request->customer_released) ? ($request->customer_released) : 0;
                $bidding_detail->pending_release = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
                $bidding_detail->divident_amount = isset($request->divident_amount) ? ($request->divident_amount) : 0;
				$bidding_amount = isset($request->bidding_amount) ? ($request->bidding_amount) : 0;
				
				//ticket_type 
				$ticket_type_ori = ChitDetail::where('id',$bidding_detail->enrollment_id)->pluck('ticket_type');
                $bidding_detail->ticket_type = $ticket_type_ori[0];
				
				//installment_overall_ticket_type 
				$installment_overall_ticket_type = BiddingDetail::where('installment_no',$bidding_detail->installment_no)->sum('ticket_type');
				$installment_overall_ticket_type = $installment_overall_ticket_type + $ticket_type_ori[0];
                //print_r($installment_overall_ticket_type);die();
				//find the group type
				$group_details = Group::where('id',$bidding_detail->group_id)->pluck('group_type');
                $group_detail = isset($group_details[0]) ? ($group_details[0]) : '0';
				$bidding_detail->installment_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;
				
				//current Installment amount 
				if($group_detail == '6') { // 2nd due type
				if($request->installment_no==1){
				    $scheme_id = Group::where('id','=',$request->group_id)->pluck('scheme_id');
    				$cur_insts = Scheme::where('id','=',$scheme_id[0])->pluck('monthly_due');
                    $cur_inst = isset($cur_insts[0]) ? ($cur_insts[0]) : 0;
    				$current_inst_amount = $cur_inst;	
				} else {
    				$max_id = BiddingDetail::where('group_id','=',$bidding_detail->group_id)->max('id');
    				$cur_insts = BiddingDetail::where('id','=',$max_id)->pluck('installment_amount');
                    $cur_inst = isset($cur_insts[0]) ? ($cur_insts[0]) : 0;
					//acuion count 
					$auc_cnt = BiddingDetail::where('group_id','=',$bidding_detail->group_id)->where('installment_no','=',$bidding_detail->installment_no)->count('id');
					if($auc_cnt==0){
    				$current_inst_amount = $cur_inst;
					} else {
					$current_inst_amount = 0;	
					}
					$bidding_detail->auction_type = "normal"; 
				}
				$installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 	
				} 
                else if($group_detail == '5')
                { // profit type                    
                    $inst_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;    
                    $divident_amount = isset($request->divident_amount) ? ($request->divident_amount) : 0;
					$commission_amount = isset($request->commission_amount) ? ($request->commission_amount) : 0;
					$gst_amount = ($commission_amount*12)/100;
					$scheme_id = Group::where('id','=',$request->group_id)->pluck('scheme_id');
					$tot_months = Scheme::where('id','=',$scheme_id[0])->pluck('no_of_months');
					$cgst = ($gst_amount/2)/$tot_months[0];
					$sgst = ($gst_amount/2)/$tot_months[0];
                    
					$profit_amount_ori = $divident_amount;
					$bidding_detail->divident_amount = $profit_amount_ori;
					
                    $current_inst_amount =  $inst_amount + $divident_amount; 
					$bidding_detail->auction_type = isset($request->auction_type) ? ($request->auction_type) : '0'; 
					$installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 	
                } 
                else { // other types
				    
					$bidding_detail->auction_type = isset($request->auction_type) ? ($request->auction_type) : '0'; 
					if($bidding_detail->auction_type == 'duplicate'){
					$installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 	
					$installment_no = $installment_no;
					$current_inst_amount = 0;						
					} else {
					$current_inst_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;	
					$installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 					
					}
				} 
				$bidding_detail->current_installment_amount = $current_inst_amount; 
				$bidding_detail->installment_no = $installment_no; 
                if(isset($request->payment_generated_on)) {
                    $p_date = ($request->payment_generated_on['year']).'-'.sprintf('%02d',($request->payment_generated_on['month'])).'-'.sprintf('%02d',($request->payment_generated_on['day'])); 
                    $bidding_detail->payment_generated_on = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    // $bidding_detail->payment_generated_on = '0000-00-00';
                }
                if(isset($request->next_auction_date)) {
                    $p_date = ($request->next_auction_date['year']).'-'.sprintf('%02d',($request->next_auction_date['month'])).'-'.sprintf('%02d',($request->next_auction_date['day'])); 
                    $bidding_detail->next_auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    $bidding_detail->next_auction_date = '0000-00-00';
                }
                if(isset($request->auction_date)) {
                    $p_date = ($request->auction_date['year']).'-'.sprintf('%02d',($request->auction_date['month'])).'-'.sprintf('%02d',($request->auction_date['day'])); 
                    $bidding_detail->auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    $bidding_detail->auction_date = '0000-00-00';
                }
                $bidding_detail->remark = isset($request->remark) ? ($request->remark) : '0';
                $bidding_detail->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $bidding_detail->status = isset($request->status) ? ($request->status) : 1; 
				
				
                if($bidding_detail->save()) {

                    
                        
         
                        
					//customer due update                                        
					$customer_due_update = ChitDetail::where('group_id','=',$bidding_detail->group_id)->get();
					foreach($customer_due_update as $due_update) {
						$chit_id = $due_update->id;
						$due_update->current_due = $current_inst_amount;
						$due_update->save();
					};
                    // prized status update
                    $cust_prized_status = ChitDetail::where('id','=',$bidding_detail->enrollment_id)->get();
                    if(count($cust_prized_status)>0)
                    {
                        $cust_prized_status[0]->prized_status = 1;
                        $cust_prized_status[0]->save();
                    }
                    $group_details = Group::where('id',$bidding_detail->group_id)->pluck('group_type');
                    $group_detail = isset($group_details[0]) ? ($group_details[0]) : '';
                    if($group_detail == '5')
                    {
                        if($bidding_detail->auction_type == 'profit')
                        {
                            $bidding_detail->current_installment_amount =0;
                            $bidding_detail->save();
                        }
                    }
					
										
										
					$group = Group::find($request->group_id);
					if(isset($group->scheme_det)) { 
					    $no_of_members = isset($group->scheme_det->members) ? ($group->scheme_det->members) : 0;
                    }
					if($request->installment_no == $no_of_members){
						$group->group_status = "1";
					} else {
						$group->group_status = "0";
					}
					$group->save();
					if($bidding_detail->tenant_id != 42){
					$group_cus_dets = ChitDetail::where('group_id','=',$bidding_detail->group_id)->where('chit_type','=','2')->get();
					foreach($group_cus_dets as $group_cus_det) {
						$receipt_count=Receipt::where(array('enrollment_id'=>$group_cus_det->id,'payment_type_id'=>7))->count();
						if($receipt_count>0){
							$receipt_no = '';
							$receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('enrollment_id'=>$group_cus_det->id,'payment_type_id'=>7))->get();
							// print_r(count($receipts));exit;
							$total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
							$advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$group_cus_det->id,'status'=>1))->get();
							$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
							$available_advance = $total_advance - $total_received;
						
							if($available_advance > 0){
								if($current_inst_amount <= $available_advance){
								   $adjust_amount = $current_inst_amount;
								} else {
									$adjust_amount = $available_advance;
								}
							
							
								$receipt = new Receipt();
								$receipt->tenant_id = isset($group_cus_det->tenant_id) ? ($group_cus_det->tenant_id) : 0;
								$receipt->branch_id = isset($group_cus_det->branch_id) ? ($group_cus_det->branch_id) : 0;
								$receipt->customer_id = isset($group_cus_det->customer_id) ? ($group_cus_det->customer_id) : 0;
								$receipt->enrollment_id = isset($group_cus_det->id) ? ($group_cus_det->id) : 0;
								$receipt->group_id = isset($group_cus_det->group_id) ? ($group_cus_det->group_id) : 0;
								$receipt->ticket_no = isset($group_cus_det->ticket_no) ? ($group_cus_det->ticket_no) : '0';
								$receipt->amount = $adjust_amount;
							
								$receipt->receipt_date = date('Y-m-d',strtotime($p_date)); 
								$receipt->payment_type_id = 7;
								$receipt->type_of_collection = 'advance_receipt';
								$receipt->receipt_passbook_mode = 'rcpt';
								$max_id = BiddingDetail::where('group_id','=',$bidding_detail->group_id)->max('id');
								$receipt->auction_id = $max_id;
								$receipt->installment_no = $bidding_detail->installment_no;
								$receipt->pending_days = 0;
								$receipt->bonus = 0;
								$receipt->received_amount = $adjust_amount;
								$receipt->penalty = 0;
								$receipt->cancel_dividend_amount = 0;
								$receipt->discount = 0;
							
								$receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
								$receipt->status = isset($request->status) ? ($request->status) : 1;
								$receipt->other_branch = isset($request->other_branch) ? ($request->other_branch) : 0;
								$receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
							
								$receipt->employee_branch_id = 0;
							
								$receipt->adjust_id = isset($request->adjust_id) ? ($request->adjust_id) : 0;
							
								$receipt->debit_to	 = isset($request->debit_to	) ? ($request->debit_to	) : '0';
								$receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0';
								if(isset($request->cheque_date)) {
									$c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
									$receipt->cheque_date = date('Y-m-d',strtotime($c_date)); 
								}
								else {
									// $receipt->cheque_date = '0000-00-00';
								}
							
								if(isset($request->cheque_clear_return_date)) {
									$cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
									$receipt->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
								}
								else {
									// $receipt->cheque_clear_return_date = '0000-00-00';
								}
							
								$receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
								$receipt->branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '0';
								$receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
								if(isset($request->transaction_date)) {
									$t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
									$receipt->transaction_date = date('Y-m-d',strtotime($t_date)); 
								}
								else {
									// $receipt->transaction_date = '0000-00-00';
								}
							
								$receipt->transaction_bill = isset($request->transaction_bill) ? ($request->transaction_bill) : '0';
								$receipt->transaction_book = isset($request->transaction_book) ? ($request->transaction_book) : '0';
								$receipt->neft_adjust_id = isset($request->neft_adjust_id) ? ($request->neft_adjust_id) : 0;
								$receipt->receipt_mode = 'ERP';
								$receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
								if(isset($request->accounts_date)) {
									$a_date = ($request->accounts_date['year']).'-'.sprintf('%02d',($request->accounts_date['month'])).'-'.sprintf('%02d',($request->accounts_date['day'])); 
									$receipt->accounts_date = date('Y-m-d',strtotime($a_date)); 
								}
								else {
									$receipt->accounts_date = date('Y-m-d');
								}
								$receipt->remarks = isset($request->remarks) ? ($request->remarks) : '0';
								$receipt->printed = isset($request->printed) ? ($request->printed) : 0;
								
								if($receipt->save()) {
                                    if($receipt_no=='') {
                                        $receipt_no = 'RCPT-'.sprintf('%05d', $receipt->id);
                                    }		
                                    $receipt->receipt_no = $receipt_no;
                                    $receipt_identy = "RCPT";
                                    $receipt->save();
                                }
							
							
							} else {
								
							}
						}
                        else{
                            //test
                            $receipt_no = '';
                            $total_received = 0;
                            $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$group_cus_det->id,'status'=>1))->get();
							$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
							$available_advance = $total_advance - $total_received;
						
							if($available_advance > 0){
								if($current_inst_amount <= $available_advance){
								   $adjust_amount = $current_inst_amount;
								} else {
									$adjust_amount = $available_advance;
								}
							
								$receipt = new Receipt();
								$receipt->tenant_id = isset($group_cus_det->tenant_id) ? ($group_cus_det->tenant_id) : 0;
								$receipt->branch_id = isset($group_cus_det->branch_id) ? ($group_cus_det->branch_id) : 0;
								$receipt->customer_id = isset($group_cus_det->customer_id) ? ($group_cus_det->customer_id) : 0;
								$receipt->enrollment_id = isset($group_cus_det->id) ? ($group_cus_det->id) : 0;
								$receipt->group_id = isset($group_cus_det->group_id) ? ($group_cus_det->group_id) : 0;
								$receipt->ticket_no = isset($group_cus_det->ticket_no) ? ($group_cus_det->ticket_no) : '0';
								$receipt->amount = $adjust_amount;
							
								$receipt->receipt_date = date('Y-m-d',strtotime($p_date)); 
								$receipt->payment_type_id = 7;
								$receipt->type_of_collection = 'advance_receipt';
								$receipt->receipt_passbook_mode = 'rcpt';
								$max_id = BiddingDetail::where('group_id','=',$bidding_detail->group_id)->max('id');
								$receipt->auction_id = $max_id;
								$receipt->installment_no = $bidding_detail->installment_no;
								$receipt->pending_days = 0;
								$receipt->bonus = 0;
								$receipt->received_amount = $adjust_amount;
								$receipt->penalty = 0;
								$receipt->cancel_dividend_amount = 0;
								$receipt->discount = 0;
							
								$receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
								$receipt->status = isset($request->status) ? ($request->status) : 1;
								$receipt->other_branch = isset($request->other_branch) ? ($request->other_branch) : 0;
								$receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
							
								$receipt->employee_branch_id = 0;
							
								$receipt->adjust_id = isset($request->adjust_id) ? ($request->adjust_id) : 0;
							
								$receipt->debit_to	 = isset($request->debit_to	) ? ($request->debit_to	) : '0';
								$receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0';
								if(isset($request->cheque_date)) {
									$c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
									$receipt->cheque_date = date('Y-m-d',strtotime($c_date)); 
								}
								else {
									// $receipt->cheque_date = '0000-00-00';
								}
							
								if(isset($request->cheque_clear_return_date)) {
									$cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
									$receipt->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
								}
								else {
									// $receipt->cheque_clear_return_date = '0000-00-00';
								}
							
								$receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
								$receipt->branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '0';
								$receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
								if(isset($request->transaction_date)) {
									$t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
									$receipt->transaction_date = date('Y-m-d',strtotime($t_date)); 
								}
								else {
									// $receipt->transaction_date = '0000-00-00';
								}
							
								$receipt->transaction_bill = isset($request->transaction_bill) ? ($request->transaction_bill) : '0';
								$receipt->transaction_book = isset($request->transaction_book) ? ($request->transaction_book) : '0';
								$receipt->neft_adjust_id = isset($request->neft_adjust_id) ? ($request->neft_adjust_id) : 0;
								$receipt->receipt_mode = 'ERP';
								$receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
								if(isset($request->accounts_date)) {
									$a_date = ($request->accounts_date['year']).'-'.sprintf('%02d',($request->accounts_date['month'])).'-'.sprintf('%02d',($request->accounts_date['day'])); 
									$receipt->accounts_date = date('Y-m-d',strtotime($a_date)); 
								}
								else {
									$receipt->accounts_date = date('Y-m-d');
								}
								$receipt->remarks = isset($request->remarks) ? ($request->remarks) : '0';
								$receipt->printed = isset($request->printed) ? ($request->printed) : 0;
								
                                if($receipt->save()) {
                                    if($receipt_no=='') {
                                        $receipt_no = 'RCPT-'.sprintf('%05d', $receipt->id);
                                    }		
                                    $receipt->receipt_no = $receipt_no;
                                    $receipt_identy = "RCPT";
                                    $receipt->save();
                                }
							} else {
								
							}
                        }
					}
					}
                    
					if($installment_overall_ticket_type==1){
					$response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_added');
                    $response['data'] = $bidding_detail;
                    return response()->json($response,200);
					} else if($installment_overall_ticket_type<1){
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_added_half_ticket');
                    $response['data'] = $bidding_detail;
                    return response()->json($response,200);
					}
                    else{
                        $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_added');
                    $response['data'] = $bidding_detail;
                    return response()->json($response,200);
                    }
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
			// } else {
			        // $response['status'] = "Error";
                    // $response['msg'] = \Lang::get('api.failed_auction_added_half_ticket');
                    // return response()->json($response, 200);
			// }
                //}
                
					
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                $response['emsg'] = $e;
                $response['msg'] = $bidding_detail;
                return response()->json($response, 401);
            }
        }
    }

    public function update_auction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) 
                {  
				$bidding_detail = BiddingDetail::find($request->id);
				$bidding_detail->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $bidding_detail->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $bidding_detail->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                $bidding_detail->installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 
				$cus_id = ChitDetail::where('id','=',$request->enrollment_id)->pluck('customer_id');
                $bidding_detail->customer_id = $cus_id[0];
                $bidding_detail->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
                $bidding_detail->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
                $bidding_detail->bidding_amount = isset($request->bidding_amount) ? ($request->bidding_amount) : 0; 
                $bidding_detail->commission_amount = isset($request->commission_amount) ? ($request->commission_amount) : 0;
                $bidding_detail->cgst = isset($request->cgst) ? ($request->cgst) : 0;
                $bidding_detail->sgst = isset($request->sgst) ? ($request->sgst) : 0;
                $bidding_detail->igst = isset($request->igst) ? ($request->igst) : 0;
                $bidding_detail->gst = isset($request->gst) ? ($request->gst) : 0;
                $bidding_detail->total_deduction = isset($request->total_deduction) ? ($request->total_deduction) : 0;
                //$bidding_detail->pending_due_amount = isset($request->pending_due_amount) ? ($request->pending_due_amount) : 0;
                //$bidding_detail->total_release_amount = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
               // $bidding_detail->customer_released = isset($request->customer_released) ? ($request->customer_released) : 0;
                //$bidding_detail->pending_release = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
                //$bidding_detail->divident_amount = isset($request->divident_amount) ? ($request->divident_amount) : 0;
                
                //$bidding_detail->profit_amount = isset($request->profit_amount) ? ($request->profit_amount) : 0;
                $bidding_detail->auction_type = isset($request->auction_type) ? ($request->auction_type) : '0'; 
                //find the group type
                $group_details = Group::where('id',$bidding_detail->group_id)->pluck('group_type');
                $group_detail = isset($group_details[0]) ? ($group_details[0]) : '0';
                $bidding_detail->installment_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;
                //current Installment amount 
                if($group_detail == '2nd_due') {
                    $max_id = BiddingDetail::where('group_id','=',$bidding_detail->group_id)->max('id');
                    $cur_insts = BiddingDetail::where('id','=',$max_id)->pluck('installment_amount');
                    $cur_inst = isset($cur_insts[0]) ? ($cur_insts[0]) : 0;
                    $current_inst_amount = $cur_inst;
                } 
                else {
                    $current_inst_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;    
                } 
                $bidding_detail->current_installment_amount = $current_inst_amount; 
                if(isset($request->payment_generated_on)) {
                    $p_date = ($request->payment_generated_on['year']).'-'.sprintf('%02d',($request->payment_generated_on['month'])).'-'.sprintf('%02d',($request->payment_generated_on['day'])); 
                    $bidding_detail->payment_generated_on = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    $bidding_detail->payment_generated_on = '0000-00-00';
                }
                if(isset($request->next_auction_date)) {
                    $p_date = ($request->next_auction_date['year']).'-'.sprintf('%02d',($request->next_auction_date['month'])).'-'.sprintf('%02d',($request->next_auction_date['day'])); 
                    $bidding_detail->next_auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    $bidding_detail->next_auction_date = '0000-00-00';
                }
                if(isset($request->auction_date)) {
                    $p_date = ($request->auction_date['year']).'-'.sprintf('%02d',($request->auction_date['month'])).'-'.sprintf('%02d',($request->auction_date['day'])); 
                    $bidding_detail->auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    $bidding_detail->auction_date = '0000-00-00';
                }
                $bidding_detail->remark = isset($request->remark) ? ($request->remark) : '0';
					$bidding_detail->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($bidding_detail->save()) 
                    {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_auction_updated');
						$response['data'] = $bidding_detail;
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

    public function get_auctions(Request $request) {
        try {
           
			// if(isset($request->conditions['group_id']) == 0 && request->conditions['group_id'] == 0){
			// $bidding_details = BiddingDetail::where('customer_id','!=','0')->orderBy('id','desc')->get();
			// } else {
				
				if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->branch_id)){
			$bidding_details = BiddingDetail::where($cond)->where('customer_id','!=','0')->orderBy('id','desc')->get();	
			 } else {
				 $cond = $request->conditions;
			$bidding_details = BiddingDetail::where($cond)->get();	 
			 }
            
            if($bidding_details) {
                $i=1;
                foreach($bidding_details as $bidding_detail) {
				//print_r($bidding_detail->id);
					$bidding_detail->sno = $i;
                    $bidding_detail->payment_generated_on = ($bidding_detail->payment_generated_on!=0) ? date('d/m/Y',strtotime($bidding_detail->payment_generated_on)) : '';
                    $bidding_detail->next_auction_date = ($bidding_detail->next_auction_date!=0) ? date('d/m/Y',strtotime($bidding_detail->next_auction_date)) : '';
                    $bidding_detail->auction_date = ($bidding_detail->auction_date!=0) ? date('d/m/Y',strtotime($bidding_detail->auction_date)) : '';
                    $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                    $bidding_detail->branch_info = isset($bidding_detail->branch_det) ? ($bidding_detail->branch_det) : [];
                    $bidding_detail->branch_name = isset($bidding_detail->branch_det) ? ($bidding_detail->branch_det->branch_name) : '';
                    $cgst = round($bidding_detail->branch_info->cgst_percent);
                    $sgst = round($bidding_detail->branch_info->sgst_percent);
					 $bidding_detail->ticket_no = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_no) : [];
					$bidding_detail->group_info = isset($bidding_detail->group_det) ? ($bidding_detail->group_det) : [];
                    $bidding_detail->group_name = isset($bidding_detail->group_det) ? ($bidding_detail->group_det->group_name) : '';
					$bidding_detail->customer_info = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det) : [];
					
					$bidding_detail->installment_amount = isset($bidding_detail->current_installment_amount) ? ($bidding_detail->current_installment_amount) : [];
                    
					if($bidding_detail->customer_id != 0){
                    $bidding_detail->customer_name = $bidding_detail->customer_det->name . ' ' . $bidding_detail->customer_det->last_name  . ' | ' . $bidding_detail->customer_det->customer_code;
					$bidding_detail->customer_info->name = $bidding_detail->customer_det->name . ' ' . $bidding_detail->customer_det->last_name  . ' | ' . $bidding_detail->customer_det->customer_code;
					} else 
					{
					$bidding_detail->customer_name = '';	
					$bidding_detail->customer_info->name = '';
					}
					
                    $bidding_detail->enrollment_info = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det) : [];
					$paid_amount = Payment::where('bidding_id','=',$bidding_detail->id)->where('status',1)->sum('total_amount');
					$bidding_detail->paid_amount = $paid_amount;
					$bid_adv_amount = Payment::where('enrollment_id' ,'=', $bidding_detail->enrollment_id)->where('payment_for','=','2')->where('status',1)->sum('total_amount');
					$bidding_detail->bid_advance_amount = $bid_adv_amount;
					$monthly_dues = Scheme::where('id','=',$bidding_detail->group_info->scheme_id)->pluck('monthly_due');
					
                    $bidding_detail->monthly_due = ($monthly_dues[0]) ? ($monthly_dues[0]) : 0;
					
					$cgst_amount = (( $cgst / 100 ) * round($bidding_detail->commission_amount));   
					$sgst_amount = (( $sgst / 100 ) * round($bidding_detail->commission_amount));
					$gst_amount=$cgst_amount+$sgst_amount;
					
                    //part payment gst release amount calculation
                    $payment_count = Payment::where('bidding_id','=',$bidding_detail->id)->where('status',1)->count();
                    if($payment_count == 0) {
    					$bidding_detail->actual_release_Amount = ( $bidding_detail->total_release_amount - ($paid_amount + $bid_adv_amount + $gst_amount ));
    			        $bidding_detail->cgst_amount = $cgst_amount;
    			        $bidding_detail->sgst_amount = $sgst_amount;
    			        $bidding_detail->gst_amount = $gst_amount;
                    } 
                    else {
                        $bidding_detail->actual_release_Amount = ( $bidding_detail->total_release_amount - ($paid_amount + $bid_adv_amount));
                        $bidding_detail->cgst_amount = $cgst_amount;
                        $bidding_detail->sgst_amount = $sgst_amount;
                        $bidding_detail->gst_amount = $gst_amount;
                    }
					$bid_id = isset($bidding_detail->id) ? ($bidding_detail->id) : 0; 
					$customer_released = round($bidding_detail->customer_released);
					$pending_release = round($bidding_detail->pending_release);
					$payment_count = Payment::where('bidding_id','=',$bid_id)->where('status',1)->count();
					if($payment_count != 0 && $customer_released != '0' && $pending_release!=0) 
					{
						$payment = "part_payment";
					}
					else if($payment_count == 0 && $customer_released == '0') {
						$payment = "initial_payment";
					} 
					else if( $pending_release==0) {
						$payment = "no_outstanding";
					} else
					{
						$payment = "initial_payment";
					}
					
					$bidding_detail->payment_type = $payment;
                    $customer_name = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->name) : "_";
                    $ticket_no = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_no) : "";
                    $phone_no = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->mobile_no) : 0;
					$ticket_types_id = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_type) : 0;
                    if($ticket_types_id == 1)
                    {
                        $ticket_type = "full_ticket";
                    }
                    else if($ticket_types_id == 2)
                    {
                        $ticket_type = "half_ticket";
                    }else{
                        $ticket_type = "_";
                    } 
                    $bidding_detail->customer_name_det = $customer_name. " / ".$ticket_no." / ". $phone_no ." / ". $ticket_type; 
					
                    $receipt = Receipt::where('auction_id',$bidding_detail->id)->where($cond)->where('status','!=',0)->count();
                    $payment = Payment::where('bidding_id',$bidding_detail->id)->where('status','!=',0)->count();
                    if($receipt > 0 || $payment > 0)
                    {
                        $bidding_detail->auction_id = $bidding_detail->id;
                        $bidding_detail->deleted_option = "1";
                    }
                    else
                    {
                        $bidding_detail->auction_id = $bidding_detail->id;
                        $bidding_detail->deleted_option = "0";
                    }
    					$i++;
    					}
                    }
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $bidding_details;
                return response()->json($response,200);
            }
        
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_auctions(Request $request) {
        try { 		
            if(isset($request->tenant_id)) {
                $bidding_details = BiddingDetail::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $bidding_details = BiddingDetail::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($bidding_details as $bidding_detail) {
                $bidding_detail->sno = $i;
                $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                $bidding_detail->branch_info = isset($bidding_detail->branch_det) ? ($bidding_detail->branch_det) : [];
                $bidding_detail->group_info = isset($bidding_detail->group_det) ? ($bidding_detail->group_det) : [];
				$bidding_detail->customer_info = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det) :[];
                $bidding_detail->enrollment_info = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det) : [];
				$bid_id = isset($bidding_detail->id) ? ($bidding_detail->id) : 0; 
				$customer_released = round($bidding_detail->customer_released);
				$pending_release = round($bidding_detail->pending_release);
				$payment_count = Payment::where('bidding_id','=',$bid_id)->count();
				if($payment_count != 0 && $customer_released != '0' && $pending_release!=0) 
                {
					$payment = "part_payment";
				}
                else if($payment_count == 0 && $customer_released == '0') {
					$payment = "initial_payment";
				} 
                else if( $pending_release==0) {
					$payment = "no_outstanding";
				} else
                {
                    $payment = "initial_payment";
                }
				$bidding_detail->payment_type = $payment;
                $customer_name = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->name) : "_";
                $ticket_no = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_no) : "";
                $phone_no = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->mobile_no) : 0;
                
                if($bidding_detail->enrollment_det->ticket_type == 1)
                {
                    $ticket_type = "full_ticket";
                }
                else if($bidding_detail->enrollment_det->ticket_type == 2)
                {
                    $ticket_type = "half_ticket";
                }else{
                    $ticket_type = "_";
                }
                $bidding_detail->customer_name_det = $customer_name. " / ".$ticket_no." / ". $phone_no ." / ". $ticket_type;  
                $i++;
            } 
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $bidding_details;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            //$response['msg'] = \Lang::get('api.global_error');
			$response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function delete_auction(Request $request) {
		try {
            if(isset($request->id)) {
                $bidding_detail = BiddingDetail::find($request->id); 
                $bidding_detail->deleted_by = $request->deleted_by;
                $bidding_detail->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($bidding_detail->save()) {
                    $bidding_detail->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_deleted');
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
                $bidding_detail = BiddingDetail::find($request->id); 
                $bidding_detail->status = ($bidding_detail->status) ? 0 : 1;
                if($bidding_detail->save()) {
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

	public function get_bidding_group_details(Request $request) {
        try { 
            //group_id,tenant_id
			
            $cond = $request->conditions; 
           // $group = Group::find($cond['group_id']);
		   
		    $tenant_id = Group::where('id',$request->group_id)->pluck('tenant_id');
		   
		    if($tenant_id[0]==21){
			   $group = Group::find($request->group_id);
			
            if($group) 
            {
				
				$ticket_type = ChitDetail::where('id','=',$request->enrollment_id)->pluck('ticket_type');
                $group->bidding_amount = '';
                $group->dividend = 0;
                $group->installment_amount = 0;
                $group->total_dividend = 0;
                $next_auction_date = $group->auction_date."/".date('m/Y',strtotime('+1 month'));
                $group->next_auction_date = $next_auction_date;
                $group->total_release_amount = 0;
                $group->profit_amount = 0; 
				
				//calculate Auction No 
				$inct_cnt = BiddingDetail::where('group_id','=',$group->id)->count('id');
				if($inct_cnt > 0) {
				$max_inst_no = BiddingDetail::where('group_id','=',$group->id)->max('installment_no');
				$ticket_cnt = BiddingDetail::where('group_id','=',$group->id)->where('installment_no','=',$max_inst_no)->sum('ticket_type');
				if($ticket_cnt==1){
				$group->auction_no = $max_inst_no;	
				} else {
				$group->auction_no = $max_inst_no;	
				}
				} else {
				$group->auction_no = 1;	
				}
           		
				if(isset($group->scheme_det))
				{ 
					$group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
					$group->scheme_info->chit_value = round($group->scheme_info->chit_value); 
                    // group_type
                    if($group->group_type == 1)
                    {
                        $group->group_type_name = "Register";
                    } else if($group->group_type == 2)
                    {
                        $group->group_type_name = "Non_Register";
                    } else if($group->group_type == 3)
                    {
                        $group->group_type_name = "Slab_Register";
                    } else if($group->group_type == 4)
                    {
                        $group->group_type_name = "Slab_Non_Register";
                    } else if($group->group_type== 5)
                    {
                        $group->group_type_name = "Profit Chit";
                    } else if($group->group_type == 6)
                    {
                        $group->group_type_name = "2nd_due";
                    } else
                    {
                        $group->group_type_name = "_";
                    }
					//commission amount                    
                    $commison_percent = isset($group->commission_percent) ? ($group->commission_percent) : 0;
                    $commission_amount = (($commison_percent/100) * $group->scheme_info->chit_value);
                    $group->scheme_info->commission_amount = round($commission_amount);
                    $group->commission_amount = $commission_amount;
					$group->chit_value = $group->scheme_info->chit_value;
					
					//slab due calculation
					if($group->group_type =='3') { 
						$due = 'due_'.$group->auction_no;
						$slab_details = SlabDue::where('schemes_id',$group->scheme_id)->pluck($due);
                        $slab_det = ($slab_details[0]) ? ($slab_details[0]) : 0;
                        //current month due  
                        $group->installment_amount = round($slab_det);
                        //dividend calculation
                        $monthly_due = round($group->scheme_info->monthly_due);
    					$dividend = $monthly_due - round($slab_det);
                        $group->dividend = $dividend;
    					$group->scheme_info->dividend =  round ($dividend );
    					//overall dividend
    					$no_of_members = round($group->scheme_info->members);
    					$total_dividend = $no_of_members * $group->scheme_info->dividend;
                        $group->total_dividend = $total_dividend;
    					$group->scheme_info->total_dividend = round($total_dividend);
    					//customer release amount
    					$total_div_com = $group->scheme_info->total_dividend +  $group->scheme_info->commission_amount ;
                        $group->bidding_amount = $total_div_com;
    					$total_release = $group->scheme_info->chit_value - $total_div_com;
    					$group->total_release_amount = round($total_release);
						$group->chit_value = $group->scheme_info->chit_value;
					}
					else if($group->group_type =='5') {
                        //profit chit calculation 
						/*$max_id = BiddingDetail::where('group_id','=',$group->id)->max('id');
						$profit_amt = BiddingDetail::where('id','=',$max_id)->pluck('profit_amount');
                        //$profit_amount = 0;
                        $profit_amount = isset($profit_amt[0]) ? ($profit_amt[0]) : 0;
					    $group->profit_amount = round($profit_amount);*/
                        $group->installment_amount = round($group->scheme_info->monthly_due);
                        $bidding = BiddingDetail::selectRaw('*,sum(divident_amount) as total_divident_amount,sum(bidding_amount) as total_bidding, sum(chit_value) as total_chit_value')->where('group_id',$group->id)->where('auction_type','!=','profit')->where('auction_type','!=','duplicate')->get();
                       
                            // total divident amount
                        $each_member_divident_amount = isset($bidding[0]->total_divident_amount) ? ($bidding[0]->total_divident_amount) : 0;  
                        $no_of_members = isset($bidding[0]->group_det->scheme_det) ? ($bidding[0]->group_det->scheme_det->members) : 0; 
                        $total_divi_amount = $each_member_divident_amount * $no_of_members ; 

                        // profit amount   
                        $bid = BiddingDetail::selectRaw('*,sum(bidding_amount) as total_profit_bidding,sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group->id)->where('auction_type','=','profit')->where('auction_type','!=','duplicate')->get();  
                        if(count($bid)>0)
                        { 
                            $total_chit_value = isset($bid[0]->total_chit_value) ? ($bid[0]->total_chit_value) : 0; 
                            $total_bidding = isset($bid[0]->total_profit_bidding) ? ($bid[0]->total_profit_bidding) : 0; 
                            // Commission amount 
                            $tot_commission_amount = isset($bid[0]->total_commission_amount) ? ($bid[0]->total_commission_amount) : 0; 
							$tot_gst = ($tot_commission_amount * 12) / 100;
                        }  
                        else
                        {
                            $tot_commission_amount = 0;
                            $total_chit_value =0;
                            $total_bidding = 0;
							$tot_gst = 0;
                        }
                        $profit = $total_chit_value - $total_bidding;  
                        $avail_amt_A  = abs($profit - $total_divi_amount); 
                        $avail_amt = $avail_amt_A - $tot_commission_amount - $tot_gst; 
                        $available_amount = abs($avail_amt);
                        $group->profit_amount = $available_amount;
                        $chit_value = round($group->scheme_info->chit_value);
					} 
                    else {
						
						$group->commission_amount = $commission_amount * $ticket_type[0];
						$group->monthly_due = round($group->scheme_info->monthly_due) * $ticket_type[0];
						$group->chit_value = $group->scheme_info->chit_value * $ticket_type[0];
					}
				}
				$response['status'] = 'Success';
                $response['data'] = $group;
				$response['msg'] = '';
                return response()->json($response,200);
            }
			   
		   } else {
			   $group = Group::find($request->group_id);
			   
            if($group) 
            {
				
				$ticket_type = ChitDetail::where('id','=',$request->enrollment_id)->pluck('ticket_type');
                $group->bidding_amount = '';
                $group->dividend = 0;
                $group->installment_amount = 0;
                $group->total_dividend = 0;
                $next_auction_date = $group->auction_date."/".date('m/Y',strtotime('+1 month'));
                $group->next_auction_date = $next_auction_date;
                $group->total_release_amount = 0;
                $group->profit_amount = 0; 
				
				//calculate Auction No 
				$inct_cnt = BiddingDetail::where('group_id','=',$group->id)->count('id');
				
				if($inct_cnt > 0) {
                   
				$max_inst_no = BiddingDetail::where('group_id','=',$request->group_id)->where('auction_type','!=','duplicate')->max('installment_no');
                
				$ticket_cnt = BiddingDetail::where('group_id','=',$request->group_id)->where('auction_type','!=','duplicate')->where('installment_no','=',$max_inst_no)->sum('ticket_type');
				
				if($request->auction_type == 'duplicate'){
					$max_inst_no = $max_inst_no-1;
				} else {
					$max_inst_no = $max_inst_no;
				}
				
				if($tenant_id[0]== 47){
					
                if($ticket_cnt==1){
				$group->auction_no = $max_inst_no;	
				} else {
				$group->auction_no = $max_inst_no+ 1;	
				}
				} else {
				if($ticket_cnt==1){
				
				$group->auction_no = $max_inst_no + 1;	
				} else {
					
				$group->auction_no = $max_inst_no;	
				}	
				}
				
				} else {
				$group->auction_no = 1;	
				}
            
				if(isset($group->scheme_det))
				{ 
					$group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
					$group->scheme_info->chit_value = round($group->scheme_info->chit_value); 
                    // group_type
                    if($group->group_type == 1)
                    {
                        $group->group_type_name = "Register";
                    } else if($group->group_type == 2)
                    {
                        $group->group_type_name = "Non_Register";
                    } else if($group->group_type == 3)
                    {
                        $group->group_type_name = "Slab_Register";
                    } else if($group->group_type == 4)
                    {
                        $group->group_type_name = "Slab_Non_Register";
                    } else if($group->group_type== 5)
                    {
                        $group->group_type_name = "Profit Chit";
                    } else if($group->group_type == 6)
                    {
                        $group->group_type_name = "2nd_due";
                    } else
                    {
                        $group->group_type_name = "_";
                    }
					//commission amount                    
                    $commison_percent = isset($group->commission_percent) ? ($group->commission_percent) : 0;
                    $commission_amount = (($commison_percent/100) * $group->scheme_info->chit_value);
                    $group->scheme_info->commission_amount = round($commission_amount);
                    $group->commission_amount = round($commission_amount) * $ticket_type[0];
					$group->chit_value = $group->scheme_info->chit_value;
					
					//slab due calculation
					if($group->group_type =='3') { 
					
					if($tenant_id[0]== 50){
					if($group->auction_no == 1){
					$group->auction_no = $group->auction_no + 1;		
					} else {
					$group->auction_no = $group->auction_no;	
					}					
					} else {
					$group->auction_no = $group->auction_no;	
					}
						$due = 'due_'.$group->auction_no;
						$slab_details = SlabDue::where('schemes_id',$group->scheme_id)->pluck($due);
                        $slab_det = ($slab_details[0]) ? ($slab_details[0]) : 0;
						$chit_value = $group->scheme_info->chit_value;
                        //current month due  
                        $group->installment_amount = round($slab_det);
						
                        //dividend calculation
                        $monthly_due = round($group->scheme_info->monthly_due);
    					$dividend = $monthly_due - round($slab_det);
                        $group->dividend = $dividend;
    					$group->scheme_info->dividend =  round ($dividend );
						
    					//overall dividend
    					$no_of_members = round($group->scheme_info->members);
    					$total_dividend = $no_of_members * $group->scheme_info->dividend;
                        $group->total_dividend = $total_dividend;
    					$group->scheme_info->total_dividend = round($total_dividend);
						
    					//customer release amount
    					$total_div_com = $group->scheme_info->total_dividend +  $group->scheme_info->commission_amount ;
                        $group->bidding_amount = $total_div_com;
    					$total_release = $chit_value - $total_div_com;
						$payment = 'payment_'.$group->auction_no;
						
						$slab_details_payment = SlabPayment::where('schemes_id',$group->scheme_id)->pluck($payment);
						
    					$group->total_release_amount = round($slab_details_payment[0]);
						
					} else if($group->group_type =='4') { 
					if($tenant_id[0]== 50){
						
					if($group->auction_no == 1){
					$group->auction_no = $group->auction_no;		
					} else {
					$group->auction_no = $group->auction_no;	
					}					
					} else {
					$group->auction_no = $group->auction_no;	
					}
						$due = 'due_'.$group->auction_no;
						
						$slab_details = SlabDue::where('schemes_id',$group->scheme_id)->pluck($due);
						//print_r($group->scheme_id);die();
						$chit_value = $group->scheme_info->chit_value * $ticket_type[0];
                        $slab_det = ($slab_details[0]) ? ($slab_details[0]) : 0;
						$slab_det = $slab_det  * $ticket_type[0];
						
                        //current month due  
                        $group->installment_amount = round($slab_det);
                        //dividend calculation
                        $monthly_due = round($group->scheme_info->monthly_due) * $ticket_type[0];
    					$dividend = $monthly_due - round($slab_det);
                        $group->dividend = $dividend;
    					$group->scheme_info->dividend =  round ($dividend);
    					//overall dividend
						if($tenant_id[0]==27){
						$no_of_members = 20;	
						} else {
						$no_of_members = round($group->scheme_info->members);	
						}
    					
    					$total_dividend = $no_of_members * $group->scheme_info->dividend;
                        $group->total_dividend = $total_dividend;
    					$group->scheme_info->total_dividend = round($total_dividend);
						$commission_amount = $group->scheme_info->commission_amount * $ticket_type[0];
    					//customer release amount
    					$total_div_com = $group->scheme_info->total_dividend +  $commission_amount ;
                        $group->bidding_amount = $total_div_com;
    					$total_release = $chit_value - $total_div_com;
    					$group->total_release_amount = round($total_release);
						$group->chit_value = $chit_value;
					}
					else if($group->group_type =='5') {
                        //profit chit calculation 
						/*$max_id = BiddingDetail::where('group_id','=',$group->id)->max('id');
						$profit_amt = BiddingDetail::where('id','=',$max_id)->pluck('profit_amount');
                        //$profit_amount = 0;
                        $profit_amount = isset($profit_amt[0]) ? ($profit_amt[0]) : 0;
					    $group->profit_amount = round($profit_amount);*/
                        $group->installment_amount = round($group->scheme_info->monthly_due);
                        $bidding = BiddingDetail::selectRaw('*,sum(divident_amount) as total_divident_amount,sum(bidding_amount) as total_bidding, sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group->id)->where('auction_type','!=','profit')->where('auction_type','!=','duplicate')->get();
                       
                            // total divident amount
                        $each_member_divident_amount = isset($bidding[0]->total_divident_amount) ? ($bidding[0]->total_divident_amount) : 0;  
                        $total_commission_amount = isset($bidding[0]->total_commission_amount) ? ($bidding[0]->total_commission_amount) : 0;  
                        $no_of_members = isset($bidding[0]->group_det->scheme_det) ? ($bidding[0]->group_det->scheme_det->members) : 0; 
                        $total_divi_amount = $bidding[0]->total_bidding; 

                        // profit amount   
                        $bid = BiddingDetail::selectRaw('*,sum(bidding_amount) as total_profit_bidding,sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group->id)->where('auction_type','=','profit')->where('auction_type','!=','duplicate')->get();  
                        if(count($bid)>0)
                        { 
                            $total_chit_value = isset($bid[0]->total_chit_value) ? ($bid[0]->total_chit_value) : 0; 
                            $total_bidding = isset($bid[0]->total_profit_bidding) ? ($bid[0]->total_profit_bidding) : 0; 
                            // Commission amount 
                            $tot_commission_amount = isset($bid[0]->total_commission_amount) ? ($bid[0]->total_commission_amount) : 0; 
							$tot_gst = ($tot_commission_amount * 12) / 100;
                        }  
                        else
                        {
                            $tot_commission_amount = 0;
                            $total_chit_value =0;
                            $total_bidding = 0;
							$tot_gst = 0;
                        }
						
                        $profit = $total_chit_value - $total_bidding;  
                        $avail_amt_A  = abs($profit - $total_divi_amount); 
						
                        $avail_amt = $avail_amt_A - $total_commission_amount; 
                        $available_amount = abs($avail_amt);
                        $group->profit_amount = $available_amount;
						$group->monthly_due = round($group->scheme_info->monthly_due);
                        $chit_value = round($group->scheme_info->chit_value);
						
						$bidding_profit = BiddingDetail::selectRaw('*,sum(total_release_amount) as total_release_amount,sum(pending_due_amount) as pending_due_amount')->where('group_id',$group->id)->get();
						
						$interest_amount = Group::where('id',$group->id)->pluck('interest_amount');
						
						$total_release_amount = isset($bidding_profit[0]->total_release_amount) ? ($bidding_profit[0]->total_release_amount) : 0;  
                        $pending_due_amount = isset($bidding_profit[0]->pending_due_amount) ? ($bidding_profit[0]->pending_due_amount) : 0; 
                        $chit_amount = isset($bidding[0]->total_chit_value) ? ($bidding[0]->total_chit_value) : 0;  
						$total_release = $total_release_amount + $pending_due_amount;
						$avail_amount = $chit_amount - $total_release - $total_commission_amount + $interest_amount[0];
						if($avail_amount > 0){
							$avail_amount = $avail_amount;
						} else {
							$avail_amount = 0;
						}
						$group->profit_amount = $avail_amount;
						
					
					} 
                    else {
						
						$group->commission_amount = round($commission_amount * $ticket_type[0]);
						$group->monthly_due = round($group->scheme_info->monthly_due) * $ticket_type[0];
						$group->chit_value = $group->scheme_info->chit_value * $ticket_type[0];
					}
					
				}
                
				$response['status'] = 'Success';
                $response['data'] = $group;
				$response['msg'] = '';
                return response()->json($response,200);
            }
			   
		   }
            
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
    public function get_advance_bidding_details(Request $request) {
        try { 
            //enrollment_id
			$cond = $request->conditions; 
			$bidding_advance_details = Payment::selectRaw('sum(total_amount) as bidding_advance')->where($cond)->where(array("payment_for" => "2"))->get();
					
            $bidding_advance = round($bidding_advance_details[0]->bidding_advance);
		    $data["bidding_advance"] = $bidding_advance;
            $response['data'] = $data;
            $response['status'] = 'Success';
			$response['msg'] = '';
            return response()->json($response,200);
            //enrollment_id
			/*$cond = $request->conditions; 
			
			
            $test = ChitDetail::where('id','=',$cond)->get();
            $group_id = $test[0]->group_id;
            $ticket_type = $test[0]->ticket_type;
            
           //group_id,tenant_id
           
           $group = Group::find($group_id);
           if($group) 
           {
               //calculate Auction No 
               $group->auction_no = BiddingDetail::where('group_id','=',$group->id)->count() +1;
               $group->bidding_amount = '';
               $group->dividend = 0;
               $group->installment_amount = 0;
               $group->total_dividend = 0;
               $next_auction_date = $group->auction_date."/".date('m/Y',strtotime('+1 month'));
               $group->next_auction_date = $next_auction_date;
               $group->total_release_amount = 0;
               $group->profit_amount = 0; 

               if(isset($group->scheme_det))
               { 
                   $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                   $group->scheme_info->chit_value = round($group->scheme_info->chit_value); 
                   // group_type
                   if($group->group_type == 1)
                   {
                       $group->group_type_name = "Register";
                   } else if($group->group_type == 2)
                   {
                       $group->group_type_name = "Non_Register";
                   } else if($group->group_type == 3)
                   {
                       $group->group_type_name = "Slab_Register";
                   } else if($group->group_type == 4)
                   {
                       $group->group_type_name = "Slab_Non_Register";
                   } else if($group->group_type== 5)
                   {
                       $group->group_type_name = "Profit Chit";
                   } else if($group->group_type == 6)
                   {
                       $group->group_type_name = "2nd_due";
                   } else
                   {
                       $group->group_type_name = "_";
                   }
                   //commission amount                    
                   $commison_percent = isset($group->commission_percent) ? ($group->commission_percent) : 0;
                   $commison_percent1 = ($commison_percent) * ($ticket_type);
                   $commission_amount = (($commison_percent1/100) * $group->scheme_info->chit_value);
                   $group->scheme_info->commission_amount = round($commission_amount);
                   $group->commission_amount = $commission_amount;
                   //slab due calculation
                   if($group->group_type =='3') { 
                       $due = 'due_'.$group->auction_no;
                       $slab_details = SlabDue::where('schemes_id',$group->scheme_id)->pluck($due);
                       $slab_det = ($slab_details[0]) ? ($slab_details[0]) : 0;
                       //current month due  
                       $group->installment_amount = round($slab_det);
                       //dividend calculation
                       $monthly_due = round($group->scheme_info->monthly_due);
                       $dividend = $monthly_due - round($slab_det);
                       $group->dividend = $dividend;
                       $group->scheme_info->dividend =  round ($dividend );
                       //overall dividend
                       $no_of_members = round($group->scheme_info->members);
                       $total_dividend = $no_of_members * $group->scheme_info->dividend;
                       $group->total_dividend = $total_dividend;
                       $group->scheme_info->total_dividend = round($total_dividend);
                       //customer release amount
                       $total_div_com = $group->scheme_info->total_dividend +  $group->commission_amount ;
                       $group->bidding_amount = $total_div_com;
                       $total_release = $group->scheme_info->chit_value - $total_div_com;
                       $group->total_release_amount = round($total_release);
                   }
                   else if($group->group_type =='5') {
                       //profit chit calculation 
                       /*$max_id = BiddingDetail::where('group_id','=',$group->id)->max('id');
                       $profit_amt = BiddingDetail::where('id','=',$max_id)->pluck('profit_amount');
                       //$profit_amount = 0;
                       $profit_amount = isset($profit_amt[0]) ? ($profit_amt[0]) : 0;
                       $group->profit_amount = round($profit_amount);*/
                       /*$group->installment_amount = round($group->scheme_info->monthly_due);
                       $bidding = BiddingDetail::selectRaw('*,sum(divident_amount) as total_divident_amount,sum(bidding_amount) as total_bidding, sum(chit_value) as total_chit_value')->where('group_id',$group->id)->where('auction_type','!=','profit')->where('auction_type','!=','duplicate')->get();
                      
                           // total divident amount
                       $each_member_divident_amount = isset($bidding[0]->total_divident_amount) ? ($bidding[0]->total_divident_amount) : 0;  
                       $no_of_members = isset($bidding[0]->group_det->scheme_det) ? ($bidding[0]->group_det->scheme_det->members) : 0; 
                       $total_divi_amount = $each_member_divident_amount * $no_of_members ; 

                       // profit amount   
                       $bid = BiddingDetail::selectRaw('*,sum(bidding_amount) as total_profit_bidding,sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group->id)->where('auction_type','=','profit')->where('auction_type','!=','duplicate')->get();  
                       if(count($bid)>0)
                       { 
                           $total_chit_value = isset($bid[0]->total_chit_value) ? ($bid[0]->total_chit_value) : 0; 
                           $total_bidding = isset($bid[0]->total_profit_bidding) ? ($bid[0]->total_profit_bidding) : 0; 
                           // Commission amount 
                           $tot_commission_amount = isset($bid[0]->total_commission_amount) ? ($bid[0]->total_commission_amount) : 0; 
                       }  
                       else
                       {
                           $tot_commission_amount = 0;
                           $total_chit_value =0;
                           $total_bidding = 0;
                       }
                       $profit = $total_chit_value - $total_bidding;  
                       $avail_amt_A  = abs($profit - $total_divi_amount); 
                       $avail_amt = $avail_amt_A - $tot_commission_amount; 
                       $available_amount = abs($avail_amt);
                       $group->profit_amount = $available_amount;
                       $chit_value = round($group->scheme_info->chit_value);
                   } 
                   else {
                   }
               }
               $response['status'] = 'Success';
               $response['data'] = $group;
               $response['msg'] = '';
               return response()->json($response,200);
           }
           $bidding_advance_details = Payment::selectRaw('sum(total_amount) as bidding_advance')->where($cond)->where(array("
            " => "2"))->get();
                   
           $bidding_advance = round($bidding_advance_details[0]->bidding_advance);
           $data["bidding_advance"] = $bidding_advance;
           
           
           $response['data'] = $data;
           $response['msg'] = '';
           return response()->json($response,200);*/
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    
    public function get_auction_report(Request $request) {
    	try { 
        	$cond = [];
            $cond1 =[];
            $end_date='';
            $start_date = '';        	
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
                    $bidding_details = BiddingDetail::where($cond)->where($cond1)->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = BiddingDetail::where($cond)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $bidding_details = BiddingDetail::where($cond)->where($cond1)->where('auction_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = BiddingDetail::where($cond)->where('auction_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $bidding_details = BiddingDetail::where($cond)->where($cond1)->whereBetween('auction_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = BiddingDetail::where($cond)->whereBetween('auction_date',[$start_date,$end_date])->get();
                }
            } 
    		$i=1;
			if(count($bidding_details)>0) 
            {
                $grand_chit_value = 0;
                $tot_chit = 0;
                $grand_bid_amt = 0;
                $tot_bid = 0;
                $grand_commission_amt = 0;
                $tot_commission = 0;
                $grand_divi_amt = 0;
                $tot_divi = 0;
                $grand_inst_amt = 0;
                $tot_inst = 0;
                $grand_cgst_amt = 0;
                $tot_cgst = 0;
                $grand_sgst_amt = 0;
                $tot_sgst = 0;
                $grand_gst_amt = 0;
                $tot_gst = 0;
                $grand_release_amt = 0;
                $tot_release = 0;
                $grand_customer_amt = 0;
                $tot_customer = 0;        
                $grand_pending_amt = 0;
                $tot_pending = 0;                
                foreach($bidding_details as $bidding_detail) 
                { 
				    
                    $bidding_detail->sno = $i;
                    $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                    $bidding_detail->branch_name = isset($bidding_detail->branch_det->branch_name) ? ($bidding_detail->branch_det->branch_name) : [];
                    $bidding_detail->group_name = isset($bidding_detail->group_det->group_name) ? ($bidding_detail->group_det->group_name) : [];
					if($bidding_detail->customer_id != 0){
                    $bidding_detail->customer_name = $bidding_detail->customer_det->name . ' ' . $bidding_detail->customer_det->last_name;
					} else 
					{
					$bidding_detail->customer_name = '';	
					}
                    $bidding_detail->phone_no = isset($bidding_detail->customer_det->mobile_no) ? ($bidding_detail->customer_det->mobile_no) : [];
                   
                    $bidding_detail->no_of_members = isset($bidding_detail->group_det->scheme_det->members) ? ($bidding_detail->group_det->scheme_det->members) : [];
					
					$bidding_detail->customer_id= $bidding_detail->remark;
                    
                    $bidding_detail->ticket_no = isset($bidding_detail->enrollment_det->ticket_no) ? ($bidding_detail->enrollment_det->ticket_no) : [];
        			$customer_released = round($bidding_detail->customer_released);
        			$pending_release = round($bidding_detail->pending_release);

                    $chit_value = isset($bidding_detail->chit_value) ? ($bidding_detail->chit_value) : 0; 
                    $tot_chit += $chit_value;
                    $bidding_amount = isset($bidding_detail->bidding_amount) ? ($bidding_detail->bidding_amount) : 0; 
                    $tot_bid += $bidding_amount;
                    $commission_amount = isset($bidding_detail->commission_amount) ? ($bidding_detail->commission_amount) : 0; 
                    $tot_commission += $commission_amount;
                    $divident_amount = isset($bidding_detail->divident_amount) ? ($bidding_detail->divident_amount) : 0; 
                    $tot_divi += $divident_amount;
                    $installment_amount = isset($bidding_detail->installment_amount) ? ($bidding_detail->installment_amount) : 0; 
                    $tot_inst += $installment_amount;
                    $cgst = isset($bidding_detail->cgst) ? ($bidding_detail->cgst) : 0; 
                    $tot_cgst += $cgst;
                    $sgst = isset($bidding_detail->sgst) ? ($bidding_detail->sgst) : 0; 
                    $tot_sgst += $sgst;
                    $gst = isset($bidding_detail->gst) ? ($bidding_detail->gst) : 0; 
                    $tot_gst += $gst;
					$bidding_detail->installment_amount = $bidding_detail->current_installment_amount;
					
                    $total_release_amount = isset($bidding_detail->total_release_amount) ? ($bidding_detail->total_release_amount) : 0; 
                    $tot_release += $total_release_amount;
                    $tot_customer += $customer_released;
                    $tot_pending += $pending_release;
					
                    $i++;
                }
                $grand_chit_value +=$tot_chit;
                $grand_bid_amt +=$tot_bid;
                $grand_commission_amt +=$tot_commission;
                $grand_divi_amt +=$tot_divi;
                $grand_inst_amt +=$tot_inst;
                $grand_cgst_amt +=$tot_cgst;
                $grand_sgst_amt +=$tot_sgst;
                $grand_gst_amt +=$tot_gst;
                $grand_release_amt +=$tot_release;
                $grand_customer_amt +=$tot_customer;
                $grand_pending_amt +=$tot_pending;

                $grand_chit_value_format =0;
                $grand_bid_amt_format =0;
                $grand_commission_amt_format =0;
                $grand_divi_amt_format =0;
                $grand_inst_amt_format =0;
                $grand_cgst_amt_format =0;
                $grand_sgst_amt_format =0;
                $grand_gst_amt_format =0;
                $grand_release_amt_format =0;
                $grand_customer_amt_format =0;
                $grand_pending_amt_format =0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                }  
                if($grand_bid_amt > 0)
                {   
                    $num = $grand_bid_amt;
                    $grand_bid_amt_format = $this->moneyFormatIndia($num);  
                } 
                if($grand_commission_amt > 0)
                {   
                    $num = $grand_commission_amt;
                    $grand_commission_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_divi_amt > 0)
                {   
                    $num = $grand_divi_amt;
                    $grand_divi_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_inst_amt > 0)
                {   
                    $num = $grand_inst_amt;
                    $grand_inst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_cgst_amt > 0)
                {   
                    $num = $grand_cgst_amt;
                    $grand_cgst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_sgst_amt > 0)
                {   
                    $num = $grand_sgst_amt;
                    $grand_sgst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_gst_amt > 0)
                {   
                    $num = $grand_gst_amt;
                    $grand_gst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_release_amt > 0)
                {   
                    $num = $grand_release_amt;
                    $grand_release_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_customer_amt > 0)
                {   
                    $num = $grand_customer_amt;
                    $grand_customer_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_pending_amt > 0)
                {   
                    $num = $grand_pending_amt;
                    $grand_pending_amt_format = $this->moneyFormatIndia($num);
                }
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['grand_bid_amt'] = $grand_bid_amt_format;
                $response['grand_commission_amt'] = $grand_commission_amt_format;
                $response['grand_divi_amt'] = $grand_divi_amt_format;
                $response['grand_inst_amt'] = $grand_inst_amt_format;
                $response['grand_cgst_amt'] = $grand_cgst_amt_format;
                $response['grand_sgst_amt'] = $grand_sgst_amt_format;
                $response['grand_gst_amt'] = $grand_gst_amt_format;
                $response['grand_release_amt'] = $grand_release_amt_format;
                $response['grand_customer_amt'] = $grand_customer_amt_format;
                $response['grand_pending_amt'] = $grand_pending_amt_format;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $bidding_details;
				return response()->json($response,200);
			} else {
				$bidding_details = [];
                $response['grand_chit_value'] = 0;
                $response['grand_bid_amt'] = 0;
                $response['grand_commission_amt'] = 0;
                $response['grand_divi_amt'] = 0;
                $response['grand_inst_amt'] = 0;
                $response['grand_cgst_amt'] = 0;
                $response['grand_sgst_amt'] = 0;
                $response['grand_gst_amt'] = 0;
                $response['grand_release_amt'] = 0;
                $response['grand_customer_amt'] = 0;
                $response['grand_pending_amt'] = 0;
				$response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $bidding_details;
				return response()->json($response, 200);
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function get_vacant_report(Request $request) {
		try {
        	$cond = [];
            $cond1 = [];
            if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }        	
        	if(isset($request->group_id)){
        		$cond['id'] = $request->group_id; 
        	}
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $groups = Group::where($cond)->where($cond1)->where('group_pending_ticket','>',0)->get();
            }
            if($request->branch_id == 0){ 
                $groups = Group::where($cond)->where('group_pending_ticket','>',0)->get();
            }
			
			$i=1;
			if(count($groups)>0) {
		        $tot_chit_value = 0;
                $grand_tot_chit_value = 0;
                foreach($groups as $group) 
                {
                    $group->sno = $i;
                    $group->tenant_info = isset($group->tenant_det) ? ($group->tenant_det) : [];
                    $group->branch_name = isset($group->branch_det->branch_name) ? ($group->branch_det->branch_name) : [];
                    //$group->group_info = isset($group->group_det) ? ($group->group_det) : [];
                    $chit_value = isset($group->scheme_det->chit_value) ? ($group->scheme_det->chit_value) : 0;
                    $group->chit_value = $chit_value;
    				//dummy chits
    				$dummy_chits = ChitDetail::where(array("group_id"=>$group->id,"chit_type" => "1"))->count();
    				$group->dummy_chits = $dummy_chits;
                    $group->starting_date = ($group->starting_date!=0) ? date('d/m/Y',strtotime($group->starting_date)) : '';
                    $i++;
                    $tot_chit_value += $chit_value;                
                } 
                $grand_tot_chit_value +=$tot_chit_value;

                $grand_tot_chit_value_format =0;

                if($grand_tot_chit_value > 0)
                {   
                    $num = $grand_tot_chit_value;
                    $grand_tot_chit_value_format = $this->moneyFormatIndia($num);
                }
                $response['grand_tot_chit_value'] = $grand_tot_chit_value_format;
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $groups;
                return response()->json($response,200);
            }
			else {
			    $groups = [];
                $response['grand_tot_chit_value'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $groups;
		        return response()->json($response, 200);
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_commitment_payment_details(Request $request) {
        try { 
		      // enrol_id
            $cond = $request->conditions; 
            $enrol = ChitDetail::find($cond['enrol_id']);
            if($enrol) {
                $join_date = $enrol->date_introduced;
				$current_date = date('Y-m-d');                
				$datetime1 = new DateTime($join_date);
				$datetime2 = new DateTime($current_date);
				$interval = $datetime1->diff($datetime2);
				$payment_month_given = (($interval->format('%y') * 12) + $interval->format('%m'));
				$payment_month = $payment_month_given + 1;
				$payment = 'payment_'.$payment_month;
				$slab_details = SlabPayment::where('slab_id',$enrol->slab_id)->pluck($payment);             
                $slab_det = isset($slab_details[0]) ? ($slab_details[0]) : 0;
				$enrol->slab_payment = $slab_det;
				$enrol->payment_month = $payment_month;
				$bid_payment_given = Payment::where('enrollment_id' ,'=', $enrol->id)->where('payment_for','=','7')->where('status',1)->sum('total_amount');
				$enrol->bid_payment_given = $bid_payment_given;
				$enrol->pending_release = $enrol->slab_payment - $enrol->bid_payment_given;
			    $response['status'] = 'Success';
                $response['data'] = $enrol;
			    $response['msg'] = '';
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_auction_details_dashboard(Request $request)
    {
       try { 
    	    if(isset($request->tenant_id)) {
                $branches = Branch::where('tenant_id',$request->tenant_id)->get();
    	    } 
            else {
    		    $branches = Branch::orderBy('id','desc')->get();
    	    }
            $i=1;
			$today_date = date('d');
            foreach($branches as $branch) {
               $group_cnt = Group::where(array('branch_id'=>$branch->id,'group_status'=>0,'is_filled'=>0,'auction_date'=>$today_date))->count();
			   $branch->group_count = $group_cnt;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $branches;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_branch_wise_group_details(Request $request)
    {
        try { 
            $today_date = date('d');
            $groups = Group::where(array('branch_id'=>$request->branch_id,'group_status'=>0,'is_filled'=>0,'auction_date'=>$today_date))->get();
            $i=1;
            foreach($groups as $group) {
                $group->sno = $i;
                $group->branch_info = isset($group->branch_det) ? $group->branch_det : [];
                $group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
                if($group->scheme_info) { 
                    $group->scheme_info->chit_value = round($group->scheme_info->chit_value);
                }
				$auc_cnt = BiddingDetail::where('group_id',$group->id)->max('installment_no');
				$group->installment_no = $auc_cnt + 1;
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

    Public function get_profit_chit_avaiable_amount(Request $request)
    {
        try
        {   // group_id
            $cond = $request->conditions;
            $group = Group::where($cond)->get();
            $response_data = [];
            if($group[0]->group_type == '5')
            {
                $bidding = BiddingDetail::selectRaw('*,sum(divident_amount) as total_divident_amount,sum(bidding_amount) as total_bidding, sum(chit_value) as total_chit_value, sum(commission_amount) as total_commission_amount')->where('group_id',$group[0]->id)->get();
                  // total divident amount
                $each_member_divident_amount = isset($bidding[0]->total_divident_amount) ? ($bidding[0]->total_divident_amount) : 0;  
                $no_of_members = isset($bidding[0]->group_det->scheme_det) ? ($bidding[0]->group_det->scheme_det->members) : 0; 
                $total_divi_amount = $each_member_divident_amount * $no_of_members ; 

                // profit amount   
                $bid = BiddingDetail::selectRaw('*,sum(bidding_amount) as total_profit_bidding,sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group[0]->id)->where('auction_type','=','profit')->get();  
                if(count($bid)>0)
                { 
                    $total_chit_value = isset($bid[0]->total_chit_value) ? ($bid[0]->total_chit_value) : 0; 
                    $total_bidding = isset($bid[0]->total_profit_bidding) ? ($bid[0]->total_profit_bidding) : 0; 
                    // Commission amount 
                    $tot_commission_amount = isset($bid[0]->total_commission_amount) ? ($bid[0]->total_commission_amount) : 0; 
                }  
                else
                {
                    $tot_commission_amount = 0;
                    $total_chit_value =0;
                    $total_bidding = 0;
                }
                $profit = $total_chit_value - $total_bidding;  
                $avail_amt_A  = abs($profit - $total_divi_amount); 
                $avail_amt = $avail_amt_A - $tot_commission_amount; 
                $available_amount = abs($avail_amt);
                //$available_amount = $total_chit_value - $total_bidding - $total_divi_amount - $total_commission_amount;
                if($available_amount >= $total_chit_value)
                {
                    $response_data['available_amount'] = $available_amount;
                    $response_data['status_values'] = ["Profits", "Normal", "Duplicate"];
                }
                else
                {
                    $response_data['available_amount'] ="less than";
                    $response_data['status_values'] ="";
                }
            }

            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);                       
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function commitment_auction_cron(Request $request)
    {
        try
        {  
            $cond = $request->conditions;
            $cond['chit_type'] = 3;
            $enrol = ChitDetail::where($cond)->get();
            foreach($enrol as $enro)
            {                
                $date_intro = isset($enro->date_introduced) ? ($enro->date_introduced) : '0000-00-00';
                $enrl_id = isset($enro->id) ? ($enro->id) : 0;
                $slab_id = isset($enro->slab_id) ? ($enro->slab_id) : 0;
               
                $current_date = date('Y-m-d');
                $datetime1 = new DateTime($date_intro);
                $datetime2 = new DateTime($current_date);
                $interval = $datetime1->diff($datetime2);
                $payment_month_given = (($interval->format('%y') * 12) + $interval->format('%m'));
                // closed status update
                $commission_auction = CommitmentAuction::where('enrollment_id',$enrl_id)->get();  
                if(count($commission_auction)>0)
                {                    
                    $auction_count = $commission_auction->count();
                    $slab_due = SlabDue::where('id',$slab_id)->get();
                    $scheme = Scheme::where('id',$slab_due[0]->id)->get();
                    $no_of_months = isset($scheme[0]->no_of_months) ? ($scheme[0]->no_of_months) : 0;                   
                    if($no_of_months == $auction_count)
                    { 
                        $sum_amount = CommitmentAuction::selectRaw('*,sum(due_amount) as total_instal_amount')->where('enrollment_id',$enrl_id)->get();    
                        $instal_amout = $sum_amount[0]->total_instal_amount;

                        $receipt = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus, sum(penalty) as total_penalty')->where('enrollment_id',$enrl_id)->get();
                        $total_receipt = isset($receipt[0]->total_receipt) ? ($receipt[0]->total_receipt) : 0;
                        $total_bonus = isset($receipt[0]->total_bonus) ? ($receipt[0]->total_bonus) : 0;
                        $total_paid = $total_receipt + $total_bonus;
                       
                        if($instal_amout == $total_paid)
                        {                            
                            $status_update = ChitDetail::where('id','=',$enrl_id)->get();
                            if(count($status_update)>0)
                            {
                                $status_update[0]->status = 2;
                                $status_update[0]->save();
                            }                        
                        }
                    }
                }      

                $auc_cnt = CommitmentAuction::where('enrollment_id',$enrl_id)->count();
                if($auc_cnt == $payment_month_given){
                    
                } 
                else {                    
                    $max_auc = CommitmentAuction::where('enrollment_id',$enrl_id)->max('installment_no');
                    $current_auc_inst = $max_auc+1;

                    $due = 'due_'.$current_auc_inst;

                    $slab_details = SlabDue::where('id',$slab_id)->pluck($due);

                    $commit_auction = new CommitmentAuction();
                    $commit_auction->enrollment_id = $enrl_id;

                    //$commit_auction->slab_id = $slab_id;
                    $commit_auction->installment_no = $current_auc_inst;
                    $commit_auction->due_amount = $slab_details[0];
                    $commit_auction->status = 1;

                    $commit_auction->auction_date = $current_date;
                    $commit_auction->customer_id = isset($enro->customer_id) ? ($enro->customer_id) : 0;
                    $commit_auction->branch_id = isset($enro->branch_id) ? ($enro->branch_id) : 0;

                    $commit_auction->tenant_id = isset($enro->tenant_id) ? ($enro->tenant_id) : 0;
                   /* if($commit_auction->save())
                    {*/                        
                        // prized status update
                        $cust_prized_status = ChitDetail::where('id','=',$commit_auction->enrollment_id)->get();
                        if(count($cust_prized_status)>0)
                        {
                            $cust_prized_status[0]->current_due = $slab_details[0];
                            $cust_prized_status[0]->prized_status = 1;
                            //$cust_prized_status[0]->save();
                        }
                    //}                   
                }      
            }          

            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $commit_auction;
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

    public function get_auction_minutes_report(Request $request) {
    	try { 
        	$cond = [];
            $cond1 =[];
            $end_date='';
            $start_date = '';        	
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
                    $bidding_details = MinutesOfAuction::where($cond)->where($cond1)->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = MinutesOfAuction::where($cond)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $bidding_details = MinutesOfAuction::where($cond)->where($cond1)->where('auction_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = MinutesOfAuction::where($cond)->where('auction_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $bidding_details = MinutesOfAuction::where($cond)->where($cond1)->whereBetween('auction_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0){
                    $bidding_details = MinutesOfAuction::where($cond)->whereBetween('auction_date',[$start_date,$end_date])->get();
                }
            } 
    		$i=1;
			if(count($bidding_details)>0) 
            {
                $grand_chit_value = 0;
                $tot_chit = 0;
                $grand_bid_amt = 0;
                $tot_bid = 0;
                $grand_commission_amt = 0;
                $tot_commission = 0;
                $grand_divi_amt = 0;
                $tot_divi = 0;
                $grand_inst_amt = 0;
                $tot_inst = 0;
                $grand_cgst_amt = 0;
                $tot_cgst = 0;
                $grand_sgst_amt = 0;
                $tot_sgst = 0;
                $grand_gst_amt = 0;
                $tot_gst = 0;
                $grand_release_amt = 0;
                $tot_release = 0;
                $grand_customer_amt = 0;
                $tot_customer = 0;        
                $grand_pending_amt = 0;
                $tot_pending = 0;                
                foreach($bidding_details as $bidding_detail) 
                { 
                    $bidding_detail->sno = $i;
                    $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                    $bidding_detail->branch_name = isset($bidding_detail->branch_det->branch_name) ? ($bidding_detail->branch_det->branch_name) : [];
                    $bidding_detail->group_name = isset($bidding_detail->group_det->group_name) ? ($bidding_detail->group_det->group_name) : [];
                    $bidding_detail->customer_name = isset($bidding_detail->customer_det->name) ? ($bidding_detail->customer_det->name) : [];
                    $bidding_detail->phone_no = isset($bidding_detail->customer_det->mobile_no) ? ($bidding_detail->customer_det->mobile_no) : [];
                   
                    $bidding_detail->no_of_members = isset($bidding_detail->group_det->scheme_det->members) ? ($bidding_detail->group_det->scheme_det->members) : [];
                    
                    $bidding_detail->ticket_no = isset($bidding_detail->enrollment_det->ticket_no) ? ($bidding_detail->enrollment_det->ticket_no) : [];
        			$customer_released = round($bidding_detail->customer_released);
        			$pending_release = round($bidding_detail->pending_release);

                    $chit_value = isset($bidding_detail->chit_value) ? ($bidding_detail->chit_value) : 0; 
                    $tot_chit += $chit_value;
                    $bidding_amount = isset($bidding_detail->bidding_amount) ? ($bidding_detail->bidding_amount) : 0; 
                    $tot_bid += $bidding_amount;
                    $commission_amount = isset($bidding_detail->commission_amount) ? ($bidding_detail->commission_amount) : 0; 
                    $tot_commission += $commission_amount;
                    $divident_amount = isset($bidding_detail->divident_amount) ? ($bidding_detail->divident_amount) : 0; 
                    $tot_divi += $divident_amount;
                    $installment_amount = isset($bidding_detail->installment_amount) ? ($bidding_detail->installment_amount) : 0; 
                    $tot_inst += $installment_amount;
                    $cgst = isset($bidding_detail->cgst) ? ($bidding_detail->cgst) : 0; 
                    $tot_cgst += $cgst;
                    $sgst = isset($bidding_detail->sgst) ? ($bidding_detail->sgst) : 0; 
                    $tot_sgst += $sgst;
                    $gst = isset($bidding_detail->gst) ? ($bidding_detail->gst) : 0; 
                    $tot_gst += $gst;
                    $total_release_amount = isset($bidding_detail->total_release_amount) ? ($bidding_detail->total_release_amount) : 0; 
                    $tot_release += $total_release_amount;
                    $tot_customer += $customer_released;
                    $tot_pending += $pending_release;
                    $i++;
                }
                $grand_chit_value +=$tot_chit;
                $grand_bid_amt +=$tot_bid;
                $grand_commission_amt +=$tot_commission;
                $grand_divi_amt +=$tot_divi;
                $grand_inst_amt +=$tot_inst;
                $grand_cgst_amt +=$tot_cgst;
                $grand_sgst_amt +=$tot_sgst;
                $grand_gst_amt +=$tot_gst;
                $grand_release_amt +=$tot_release;
                $grand_customer_amt +=$tot_customer;
                $grand_pending_amt +=$tot_pending;

                $grand_chit_value_format =0;
                $grand_bid_amt_format =0;
                $grand_commission_amt_format =0;
                $grand_divi_amt_format =0;
                $grand_inst_amt_format =0;
                $grand_cgst_amt_format =0;
                $grand_sgst_amt_format =0;
                $grand_gst_amt_format =0;
                $grand_release_amt_format =0;
                $grand_customer_amt_format =0;
                $grand_pending_amt_format =0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                }  
                if($grand_bid_amt > 0)
                {   
                    $num = $grand_bid_amt;
                    $grand_bid_amt_format = $this->moneyFormatIndia($num);  
                } 
                if($grand_commission_amt > 0)
                {   
                    $num = $grand_commission_amt;
                    $grand_commission_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_divi_amt > 0)
                {   
                    $num = $grand_divi_amt;
                    $grand_divi_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_inst_amt > 0)
                {   
                    $num = $grand_inst_amt;
                    $grand_inst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_cgst_amt > 0)
                {   
                    $num = $grand_cgst_amt;
                    $grand_cgst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_sgst_amt > 0)
                {   
                    $num = $grand_sgst_amt;
                    $grand_sgst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_gst_amt > 0)
                {   
                    $num = $grand_gst_amt;
                    $grand_gst_amt_format = $this->moneyFormatIndia($num);
                } 
                if($grand_release_amt > 0)
                {   
                    $num = $grand_release_amt;
                    $grand_release_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_customer_amt > 0)
                {   
                    $num = $grand_customer_amt;
                    $grand_customer_amt_format = $this->moneyFormatIndia($num);
                }
                if($grand_pending_amt > 0)
                {   
                    $num = $grand_pending_amt;
                    $grand_pending_amt_format = $this->moneyFormatIndia($num);
                }
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['grand_bid_amt'] = $grand_bid_amt_format;
                $response['grand_commission_amt'] = $grand_commission_amt_format;
                $response['grand_divi_amt'] = $grand_divi_amt_format;
                $response['grand_inst_amt'] = $grand_inst_amt_format;
                $response['grand_cgst_amt'] = $grand_cgst_amt_format;
                $response['grand_sgst_amt'] = $grand_sgst_amt_format;
                $response['grand_gst_amt'] = $grand_gst_amt_format;
                $response['grand_release_amt'] = $grand_release_amt_format;
                $response['grand_customer_amt'] = $grand_customer_amt_format;
                $response['grand_pending_amt'] = $grand_pending_amt_format;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $bidding_details;
				return response()->json($response,200);
			} else {
				$bidding_details = [];
                $response['grand_chit_value'] = 0;
                $response['grand_bid_amt'] = 0;
                $response['grand_commission_amt'] = 0;
                $response['grand_divi_amt'] = 0;
                $response['grand_inst_amt'] = 0;
                $response['grand_cgst_amt'] = 0;
                $response['grand_sgst_amt'] = 0;
                $response['grand_gst_amt'] = 0;
                $response['grand_release_amt'] = 0;
                $response['grand_customer_amt'] = 0;
                $response['grand_pending_amt'] = 0;
				$response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $bidding_details;
				return response()->json($response, 200);
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    
    public function reactive_bid(Request $request) {
		try {
				
                $group = GroupBiddingDetail::find($request->id);
				
                $group->status = 2;
				
                if($group->save()) {
					
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_reactive_bid');
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function get_group_bid(Request $request) {
        try {
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->customer_id)){
				$customer_id = $request->customer_id;
			}
			else{
				$customer_id = 0;
			}
			
			if($customer_id > 0){
			$customer_id_ori = User::where('id','=',$customer_id)->pluck('employee_id');	
				
			$groups = ChitDetail::where('group_id',$request->group_id)->where('customer_id',$customer_id_ori[0])->get();
			
				foreach($groups as $group) {
				
					$group->group_name = isset($group->group_det) ? ($group->group_det->group_name) : [];
					$group->ticket_no = isset($group->ticket_no) ? ($group->ticket_no) : [];
					$group->chit_value = isset($group->group_det->scheme_det->chit_value) ? ($group->group_det->scheme_det->chit_value) : [];
					
					$groups_bids = GroupBiddingDetail::where('group_id',$group->group_id)->get();	
					foreach($groups_bids as $groups_bid) {
						$group->group_bid_id = isset($groups_bid->id) ? ($groups_bid->id) : [];
						$group->group_name = isset($groups_bid->group_det) ? ($groups_bid->group_det->group_name) : [];
						$group->chit_value = isset($groups_bid->group_det) ? ($groups_bid->group_det->scheme_det->chit_value) : [];
						$group->progress = isset($groups_bid->progress) ? ($groups_bid->progress) : [];
						$group->status1 = isset($groups_bid->status) ? ($groups_bid->status) : [];
						$group->installment_no = isset($groups_bid->installment_no) ? ($groups_bid->installment_no) : [];
				
					}
					
				}
			}
			else{
				
				$groups_bids = GroupBiddingDetail::where($cond)->get();	
				$groups = 0;
				
					foreach($groups_bids as $groups_bid) {
						$groups_bid->group_bid_id = isset($groups_bid->id) ? ($groups_bid->id) : [];
						
						$groups_bid->installment_no = isset($groups_bid->installment_no) ? ($groups_bid->installment_no) : [];
						
						$groups_bid->group_name = isset($groups_bid->group_det) ? ($groups_bid->group_det->group_name) : [];
						
						$groups_bid->chit_value = isset($groups_bid->group_det) ? ($groups_bid->group_det->scheme_det->chit_value) : [];
						
						$groups_bid->progress = isset($groups_bid->progress) ? ($groups_bid->progress) : [];
						
						$groups_bid->status = isset($groups_bid->status) ? ($groups_bid->status) : [];
						
				
					}
					
			}
			
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $groups;
			$response['data1'] = $groups_bids;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function store_online_bid(Request $request) {
        try {
			
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->customer_id)){
				
				$customer_id_ori = User::where('id','=',$request->customer_id)->pluck('employee_id');	
				$cond['customer_id'] = $customer_id_ori[0];
			}
			if(isset($request->ticket_no)){
				$cond['ticket_no'] = $request->ticket_no;
			}
			
			$groups = ChitDetail::where($cond)->get();
			$enrollment_id = $groups[0]->id;
			
			    $group_det = new BiddingLog(); 
				$group_det->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$group_det->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $group_det->group_id = isset($request->group_id) ? ($request->group_id) : 0;
				$group_det->installment_no = isset($request->installment_no) ? ($request->installment_no) : 0;
                $group_det->customer_id = $customer_id_ori[0];
                $group_det->enrollment_id = isset($enrollment_id) ? ($enrollment_id) : 0; 
                $group_det->amount = isset($request->amount) ? ($request->amount) : 0;
			
				if($group_det->save())
		                {
		                    $response['status'] = 'Success';
		                    $response['msg'] = \Lang::get('api.success_group_added');
		                    $response['data'] = $group_det;
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
	
	public function get_online_bid(Request $request) {
        try {
			
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->customer_id)){
				$customer_id_ori = User::where('id','=',$request->customer_id)->pluck('employee_id');
				$cond['customer_id'] = $customer_id_ori[0];
			}
			if(isset($request->ticket_no)){
				$cond['ticket_no'] = $request->ticket_no;
			}
			
			$groups = ChitDetail::where($cond)->get();
			$enrollment_id = $groups[0]->id;
		  
            $groups = BiddingLog::where('group_id',$request->group_id)->where('installment_no',$request->installment_no)->where('customer_id',$customer_id_ori[0])->where('enrollment_id',$enrollment_id)->orderBy('id','desc')->get();
			foreach($groups as $group) {
				$group->customer_code = isset($group->customer_det) ? ($group->customer_det->customer_code) : '';
                $group->created_at = isset($group->created_at) ? ($group->created_at) : '';
                $group->amount = isset($group->amount) ? ($group->amount) : '';
				$date = explode(" ",$group->created_at);
				$group->dateall = $date[0];
				
				
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
	
	public function store_message_bidlog(Request $request) {
        try {

		    	$group_det = new MessageLog(); 
				$group_det->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$group_det->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $group_det->group_id = isset($request->group_id) ? ($request->group_id) : 0;
				$group_det->installment_no = isset($request->installment_no) ? ($request->installment_no) : 0;
				$group_det->message = isset($request->message) ? ($request->message) : 0;
				$group_det->status = isset($request->status) ? ($request->status) : 1;
                
                
				if($group_det->save())
		                {
		                    $response['status'] = 'Success';
		                    $response['msg'] = \Lang::get('api.success_group_added');
		                    $response['data'] = $group_det;
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

    public function get_log_bid_view(Request $request) {
        try {
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->installment_no)){
				$cond['installment_no'] = $request->installment_no;
			}
			
				
				$groups_bids = BiddingLog::where($cond)->orderBy('amount','desc')->get();	
				
					foreach($groups_bids as $group) {
						$group->name = isset($group->customer_det) ? ($group->customer_det->name) : '';
						$group->customer_code = isset($group->customer_det) ? ($group->customer_det->customer_code) : '';
                $group->created_at = isset($group->created_at) ? ($group->created_at) : '';
                $group->amount = isset($group->amount) ? ($group->amount) : '';
				$date = explode(" ",$group->created_at);
				$group->dateall = $date[0];
				
					}
				
			
            $response['status'] = 'Success';
            $response['msg'] = '';
			$response['data'] = $groups_bids;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_log_bid_viewsort(Request $request) {
        try {
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->installment_no)){
				$cond['installment_no'] = $request->installment_no;
			}
			
				
				$groups_bids = BiddingLog::where($cond)->orderBy('created_at','asc')->get();	
				
					foreach($groups_bids as $group) {
						$group->name = isset($group->customer_det) ? ($group->customer_det->name) : '';
						$group->customer_code = isset($group->customer_det) ? ($group->customer_det->customer_code) : '';
                $group->created_at = isset($group->created_at) ? ($group->created_at) : '';
                $group->amount = isset($group->amount) ? ($group->amount) : '';
				$date = explode(" ",$group->created_at);
				$group->dateall = $date[0];
				
					}
				
			
            $response['status'] = 'Success';
            $response['msg'] = '';
			$response['data'] = $groups_bids;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_group_bid_inst(Request $request) {
        try {
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
			}
			if(isset($request->installment_no)){
				$cond['installment_no'] = $request->installment_no;
			}
			$groups_bids = GroupBiddingDetail::where($cond)->get();	
				
					foreach($groups_bids as $groups_bid) {
						$groups_bid->group_bid_id = isset($groups_bid->id) ? ($groups_bid->id) : [];
						
						$groups_bid->installment_no = isset($groups_bid->installment_no) ? ($groups_bid->installment_no) : [];
						
						$groups_bid->group_name = isset($groups_bid->group_det) ? ($groups_bid->group_det->group_name) : [];
						
						$groups_bid->chit_value = isset($groups_bid->group_det) ? ($groups_bid->group_det->scheme_det->chit_value) : [];
						
						$groups_bid->progress = isset($groups_bid->progress) ? ($groups_bid->progress) : [];
						
						$groups_bid->status = isset($groups_bid->status) ? ($groups_bid->status) : [];
						
				
					}
				
				
			
            $response['status'] = 'Success';
            $response['msg'] = '';
			$response['data'] = $groups_bids;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    
    public function delete_bid(Request $request) {
		try {
			
            if(isset($request->id)) {
                $bidding_detail = BiddingLog::find($request->id); 
                $bidding_detail->deleted_by = $request->deleted_by;
                $bidding_detail->status = '0';
                
                if($bidding_detail->save()) {
                    $bidding_detail->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_bid_deleted');
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
	
	public function get_completed_group_auction_details(Request $request) {
        try {
			
			$lastauction = GroupBiddingDetail::where('id',$request->id)->get();
			$group_id = $lastauction[0]->group_id;
            $installment_no = $lastauction[0]->installment_no;
		   
            $groups = BiddingDetail::where('group_id',$group_id)->where('installment_no',$installment_no)->get();
            foreach($groups as $group) {
				
                $group->customer_name = isset($group->customer_det) ? ($group->customer_det->name) : '';
				$group->installment_no = isset($group->installment_no) ? ($group->installment_no) : '';
				$group->bidding_amount = isset($group->bidding_amount) ? ($group->bidding_amount) : '';
				$group->auction_date = isset($group->auction_date) ? ($group->auction_date) : '';
				$group->mobile_no = isset($group->customer_det) ? ($group->customer_det->mobile_no) : '';
                $enrl_id = $group->enrollment_id;
                $group_ticket_no = ChitDetail::where('id',$enrl_id)->pluck('ticket_no');
				$group->ticket_no = $group_ticket_no[0];
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

    public function store_auction_minutes(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
			   
                $bidding_detail = new MinutesOfAuction(); 
				$bidding_detail->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $bidding_detail->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $bidding_detail->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                $bidding_detail->installment_no = isset($request->installment_no) ? ($request->installment_no) : 0; 
                $bidding_detail->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $bidding_detail->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
                $bidding_detail->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
                $bidding_detail->bidding_amount = isset($request->bidding_amount) ? ($request->bidding_amount) : 0; 
                $bidding_detail->commission_amount = isset($request->commission_amount) ? ($request->commission_amount) : 0;
                $bidding_detail->cgst = isset($request->cgst) ? ($request->cgst) : 0;
                $bidding_detail->sgst = isset($request->sgst) ? ($request->sgst) : 0;
                $bidding_detail->igst = isset($request->igst) ? ($request->igst) : 0;
                $bidding_detail->gst = isset($request->gst) ? ($request->gst) : 0;
                $bidding_detail->total_deduction = isset($request->total_deduction) ? ($request->total_deduction) : 0;
                $bidding_detail->pending_due_amount = isset($request->pending_due_amount) ? ($request->pending_due_amount) : 0;
                $bidding_detail->total_release_amount = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
                $bidding_detail->customer_released = isset($request->customer_released) ? ($request->customer_released) : 0;
                $bidding_detail->pending_release = isset($request->total_release_amount) ? ($request->total_release_amount) : 0;
                $bidding_detail->divident_amount = isset($request->divident_amount) ? ($request->divident_amount) : 0;
                
                $bidding_detail->profit_amount = isset($request->profit_amount) ? ($request->profit_amount) : 0;
                $bidding_detail->auction_type = isset($request->auction_type) ? ($request->auction_type) : '0'; 
				//find the group type
				$group_details = Group::where('id',$bidding_detail->group_id)->pluck('group_type');
                $group_detail = isset($group_details[0]) ? ($group_details[0]) : '';
				$bidding_detail->installment_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;
				//current Installment amount 
				if($group_detail == '6') { // 2nd due type
    				$max_id = MinutesOfAuction::where('group_id','=',$bidding_detail->group_id)->max('id');
    				$cur_insts = MinutesOfAuction::where('id','=',$max_id)->pluck('installment_amount');
                    $cur_inst = isset($cur_insts[0]) ? ($cur_insts[0]) : 0;
    				$current_inst_amount = $cur_inst;
				} 
                else if($group_detail == '5')
                { // profit type                    
                    $inst_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;    
                    $divident_amount = isset($request->divident_amount) ? ($request->divident_amount) : 0;
                    $current_inst_amount =  $inst_amount + $divident_amount; 
                } 
                else { // other types
				    $current_inst_amount = isset($request->installment_amount) ? ($request->installment_amount) : 0;	
				} 
				$bidding_detail->current_installment_amount = $current_inst_amount; 
                if(isset($request->payment_generated_on)) {
                    $p_date = ($request->payment_generated_on['year']).'-'.sprintf('%02d',($request->payment_generated_on['month'])).'-'.sprintf('%02d',($request->payment_generated_on['day'])); 
                    $bidding_detail->payment_generated_on = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    // $bidding_detail->payment_generated_on = '0000-00-00';
                }
                if(isset($request->next_auction_date)) {
                    $p_date = ($request->next_auction_date['year']).'-'.sprintf('%02d',($request->next_auction_date['month'])).'-'.sprintf('%02d',($request->next_auction_date['day'])); 
                    $bidding_detail->next_auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    // $bidding_detail->next_auction_date = '0000-00-00';
                }
                if(isset($request->auction_date)) {
                    $p_date = ($request->auction_date['year']).'-'.sprintf('%02d',($request->auction_date['month'])).'-'.sprintf('%02d',($request->auction_date['day'])); 
                    $bidding_detail->auction_date = date('Y-m-d',strtotime($p_date)); 
                }
                else {
                    // $bidding_detail->auction_date = '0000-00-00';
                }
                $bidding_detail->remark = isset($request->remark) ? ($request->remark) : '';
                $bidding_detail->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $bidding_detail->status = isset($request->status) ? ($request->status) : 1; 
                if($bidding_detail->save()) {
					//customer due update                                        
					$customer_due_update = ChitDetail::where('group_id','=',$bidding_detail->group_id)->get();
					foreach($customer_due_update as $due_update) {
						$chit_id = $due_update->id;
						$due_update->current_due = $current_inst_amount;
						$due_update->save();
					};
                    // prized status update
                    $cust_prized_status = ChitDetail::where('id','=',$bidding_detail->enrollment_id)->get();
                    if(count($cust_prized_status)>0)
                    {
                        $cust_prized_status[0]->prized_status = 1;
                        $cust_prized_status[0]->save();
                    }
                    $group_details = Group::where('id',$bidding_detail->group_id)->pluck('group_type');
                    $group_detail = isset($group_details[0]) ? ($group_details[0]) : '';
                    if($group_detail == '5')
                    {
                        if($bidding_detail->auction_type == 'profit')
                        {
                            $bidding_detail->current_installment_amount =0;
                            $bidding_detail->save();
                        }
                    }

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_added');
                    $response['data'] = $bidding_detail;
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
                $response['msge'] = $e;
                return response()->json($response, 401);
            }
        }
    }
	
	public function get_auction_minutes(Request $request) {
        try {
            $cond = $request->conditions;
            $bidding_details = MinutesOfAuction::where($cond)->orderBy('id','desc')->get();
            if($bidding_details) {
                $i=1;
                foreach($bidding_details as $bidding_detail) {
					$bidding_detail->sno = $i;
                    $bidding_detail->payment_generated_on = ($bidding_detail->payment_generated_on!=0) ? date('d/m/Y',strtotime($bidding_detail->payment_generated_on)) : '';
                    $bidding_detail->next_auction_date = ($bidding_detail->next_auction_date!=0) ? date('d/m/Y',strtotime($bidding_detail->next_auction_date)) : '';
                    $bidding_detail->auction_date = ($bidding_detail->auction_date!=0) ? date('d/m/Y',strtotime($bidding_detail->auction_date)) : '';
                    $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                    $bidding_detail->branch_info = isset($bidding_detail->branch_det) ? ($bidding_detail->branch_det) : [];
                    $bidding_detail->branch_name = isset($bidding_detail->branch_det) ? ($bidding_detail->branch_det->branch_name) : '';
                    $cgst = round($bidding_detail->branch_info->cgst_percent);
                    $sgst = round($bidding_detail->branch_info->sgst_percent);
					$bidding_detail->group_info = isset($bidding_detail->group_det) ? ($bidding_detail->group_det) : [];
                    $bidding_detail->group_name = isset($bidding_detail->group_det) ? ($bidding_detail->group_det->group_name) : '';
					$bidding_detail->customer_info = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det) : [];
                    $bidding_detail->customer_name = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->name) : '';
                    $bidding_detail->enrollment_info = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det) : [];
					$paid_amount = PaymentMinutes::where('bidding_id','=',$bidding_detail->id)->where('status',1)->sum('total_amount');
					$bidding_detail->paid_amount = $paid_amount;
					$bid_adv_amount = PaymentMinutes::where('enrollment_id' ,'=', $bidding_detail->enrollment_id)->where('payment_for','=','Bid_Advance')->where('status',1)->sum('total_amount');
					$bidding_detail->bid_advance_amount = $bid_adv_amount;
					$monthly_dues = Scheme::where('id','=',$bidding_detail->group_info->scheme_id)->pluck('monthly_due');
                    $monthly_due = ($monthly_dues[0]) ? ($monthly_dues[0]) : 0;
					$cgst_amount = (( $cgst / 100 ) * round($monthly_due));   
					$sgst_amount = (( $sgst / 100 ) * round($monthly_due));
					$gst_amount=$cgst_amount+$sgst_amount;
                    //part payment gst release amount calculation
                    $payment_count = PaymentMinutes::where('bidding_id','=',$bidding_detail->id)->where('status',1)->count();
                    if($payment_count == 0) {
    					$bidding_detail->actual_release_Amount = ( $bidding_detail->total_release_amount - ($paid_amount + $bid_adv_amount + $gst_amount ));
    			        $bidding_detail->cgst_amount = $cgst_amount;
    			        $bidding_detail->sgst_amount = $sgst_amount;
    			        $bidding_detail->gst_amount = $gst_amount;
                    } 
                    else {
                        $bidding_detail->actual_release_Amount = ( $bidding_detail->total_release_amount - ($paid_amount + $bid_adv_amount));
                        $bidding_detail->cgst_amount = $cgst_amount;
                        $bidding_detail->sgst_amount = $sgst_amount;
                        $bidding_detail->gst_amount = $gst_amount;
                    }
					$bid_id = isset($bidding_detail->id) ? ($bidding_detail->id) : 0; 
					$customer_released = round($bidding_detail->customer_released);
					$pending_release = round($bidding_detail->pending_release);
					$payment_count = PaymentMinutes::where('bidding_id','=',$bid_id)->where('status',1)->count();
					if($payment_count != 0 && $customer_released != '0' && $pending_release!=0) 
					{
						$payment = "part_payment";
					}
					else if($payment_count == 0 && $customer_released == '0') {
						$payment = "initial_payment";
					} 
					else if( $pending_release==0) {
						$payment = "no_outstanding";
					} else
					{
						$payment = "initial_payment";
					}
					$bidding_detail->payment_type = $payment;
                    $customer_name = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->name) : "_";
                    $ticket_no = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_no) : "";
                    $phone_no = isset($bidding_detail->customer_det) ? ($bidding_detail->customer_det->mobile_no) : 0;
					$ticket_types_id = isset($bidding_detail->enrollment_det) ? ($bidding_detail->enrollment_det->ticket_type) : 0;
                    if($ticket_types_id == 1)
                    {
                        $ticket_type = "full_ticket";
                    }
                    else if($ticket_types_id == 2)
                    {
                        $ticket_type = "half_ticket";
                    }else{
                        $ticket_type = "_";
                    } 
                    $bidding_detail->customer_name_det = $customer_name. " / ".$ticket_no." / ". $phone_no ." / ". $ticket_type; 
					
                    $receipt = Receipt::where('auction_id',$bidding_detail->id)->where($cond)->where('status','!=',0)->count();
                    $payment = PaymentMinutes::where('bidding_id',$bidding_detail->id)->where('status','!=',0)->count();
                    if($receipt > 0 || $payment > 0)
                    {
                        $bidding_detail->auction_id = $bidding_detail->id;
                        $bidding_detail->deleted_option = "1";
                    }
                    else
                    {
                        $bidding_detail->auction_id = $bidding_detail->id;
                        $bidding_detail->deleted_option = "0";
                    }
    					$i++;
    					}
                    }
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $bidding_details;
                return response()->json($response,200);
            }
        
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_minutes_group_details(Request $request) {
        try { 
            //group_id,tenant_id
            $cond = $request->conditions; 
            $group = Group::find($cond['group_id']);
            if($group) 
            {
				//calculate Auction No 
				$group->auction_no = MinutesOfAuction::where('group_id','=',$group->id)->count() +1;
                $group->bidding_amount = '';
                $group->dividend = 0;
                $group->installment_amount = 0;
                $group->total_dividend = 0;
                $next_auction_date = $group->auction_date."/".date('m/Y',strtotime('+1 month'));
                $group->next_auction_date = $next_auction_date;
                $group->total_release_amount = 0;
                $group->profit_amount = 0; 

				if(isset($group->scheme_det))
				{ 
					$group->scheme_info = isset($group->scheme_det) ? $group->scheme_det : [];
					$group->scheme_info->chit_value = round($group->scheme_info->chit_value); 
                    // group_type
                    if($group->group_type == 1)
                    {
                        $group->group_type_name = "Register";
                    } else if($group->group_type == 2)
                    {
                        $group->group_type_name = "Non_Register";
                    } else if($group->group_type == 3)
                    {
                        $group->group_type_name = "Slab_Register";
                    } else if($group->group_type == 4)
                    {
                        $group->group_type_name = "Slab_Non_Register";
                    } else if($group->group_type== 5)
                    {
                        $group->group_type_name = "Profit Chit";
                    } else if($group->group_type == 6)
                    {
                        $group->group_type_name = "2nd_due";
                    } else
                    {
                        $group->group_type_name = "_";
                    }
					//commission amount                    
                    $commison_percent = isset($group->commission_percent) ? ($group->commission_percent) : 0;
                    $commission_amount = (($commison_percent/100) * $group->scheme_info->chit_value);
                    $group->scheme_info->commission_amount = round($commission_amount);
                    $group->commission_amount = $commission_amount;
					//slab due calculation
					if($group->group_type =='3') { 
						$due = 'due_'.$group->auction_no;
						$slab_details = SlabDue::where('schemes_id',$group->scheme_id)->pluck($due);
                        $slab_det = ($slab_details[0]) ? ($slab_details[0]) : 0;
                        //current month due  
                        $group->installment_amount = round($slab_det);
                        //dividend calculation
                        $monthly_due = round($group->scheme_info->monthly_due);
    					$dividend = $monthly_due - round($slab_det);
                        $group->dividend = $dividend;
    					$group->scheme_info->dividend =  round ($dividend );
    					//overall dividend
    					$no_of_members = round($group->scheme_info->members);
    					$total_dividend = $no_of_members * $group->scheme_info->dividend;
                        $group->total_dividend = $total_dividend;
    					$group->scheme_info->total_dividend = round($total_dividend);
    					//customer release amount
    					$total_div_com = $group->scheme_info->total_dividend +  $group->scheme_info->commission_amount ;
                        $group->bidding_amount = $total_div_com;
    					$total_release = $group->scheme_info->chit_value - $total_div_com;
    					$group->total_release_amount = round($total_release);
					}
					else if($group->group_type =='5') {
                        //profit chit calculation 
						/*$max_id = BiddingDetail::where('group_id','=',$group->id)->max('id');
						$profit_amt = BiddingDetail::where('id','=',$max_id)->pluck('profit_amount');
                        //$profit_amount = 0;
                        $profit_amount = isset($profit_amt[0]) ? ($profit_amt[0]) : 0;
					    $group->profit_amount = round($profit_amount);*/
                        $group->installment_amount = round($group->scheme_info->monthly_due);
                        $bidding = MinutesOfAuction::selectRaw('*,sum(divident_amount) as total_divident_amount,sum(bidding_amount) as total_bidding, sum(chit_value) as total_chit_value')->where('group_id',$group->id)->where('auction_type','!=','profit')->where('auction_type','!=','duplicate')->get();
                       
                            // total divident amount
                        $each_member_divident_amount = isset($bidding[0]->total_divident_amount) ? ($bidding[0]->total_divident_amount) : 0;  
                        $no_of_members = isset($bidding[0]->group_det->scheme_det) ? ($bidding[0]->group_det->scheme_det->members) : 0; 
                        $total_divi_amount = $each_member_divident_amount * $no_of_members ; 

                        // profit amount   
                        $bid = MinutesOfAuction::selectRaw('*,sum(bidding_amount) as total_profit_bidding,sum(chit_value) as total_chit_value,sum(commission_amount) as total_commission_amount')->where('group_id',$group->id)->where('auction_type','=','profit')->where('auction_type','!=','duplicate')->get();  
                        if(count($bid)>0)
                        { 
                            $total_chit_value = isset($bid[0]->total_chit_value) ? ($bid[0]->total_chit_value) : 0; 
                            $total_bidding = isset($bid[0]->total_profit_bidding) ? ($bid[0]->total_profit_bidding) : 0; 
                            // Commission amount 
                            $tot_commission_amount = isset($bid[0]->total_commission_amount) ? ($bid[0]->total_commission_amount) : 0; 
                        }  
                        else
                        {
                            $tot_commission_amount = 0;
                            $total_chit_value =0;
                            $total_bidding = 0;
                        }
                        $profit = $total_chit_value - $total_bidding;  
                        $avail_amt_A  = abs($profit - $total_divi_amount); 
                        $avail_amt = $avail_amt_A - $tot_commission_amount; 
                        $available_amount = abs($avail_amt);
                        $group->profit_amount = $available_amount;
                        $chit_value = round($group->scheme_info->chit_value);
					} 
                    else {
					}
				}
				$response['status'] = 'Success';
                $response['data'] = $group;
				$response['msg'] = '';
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
    
    public function get_gst_included(Request $request) {
        try { 
            $branch_id = $request->branch_id;
            $commission_amount = $request->commission_amount;
            $no_of_members = $request->no_of_members;
            $installment_amount = $request->installment_amount;

            $branches = Branch::where('id',$branch_id)->orderBy('id','desc')->get();
            $gst_percent = $branches[0]->gst_percent;
            $gst_amt =  $commission_amount * ($gst_percent / 100);
            $gst_amt_divided_members = $gst_amt / $no_of_members; 

            if($request->checked == "true"){
                
                $installment_amount_final = $installment_amount + $gst_amt_divided_members;
                
            }
            else{
               
                $installment_amount_final = $installment_amount - $gst_amt_divided_members;
                $gst_amt_divided_members = 0;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['installment_amount_final'] = $installment_amount_final;
            $response['gst_amount'] = $gst_amt_divided_members;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function get_completed_bid(Request $request) {
        try {
			$cond = [];
			
			if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id;
                $cond['status'] = '1';
                
			}
			
			
				
				$groups_bids = GroupBiddingDetail::where($cond)->get();	
				
				
					foreach($groups_bids as $groups_bid) {
						
						
						$groups_bid->installment_no = isset($groups_bid->installment_no) ? ($groups_bid->installment_no) : [];
						
						$groups_bid->group_name = isset($groups_bid->group_det) ? ($groups_bid->group_det->group_name) : [];
						
						$groups_bid->chit_value = isset($groups_bid->group_det) ? ($groups_bid->group_det->scheme_det->chit_value) : [];
						
						
						
						$groups_bid->status = isset($groups_bid->status) ? ($groups_bid->status) : [];
						
				
					}
					
			
			
            $response['status'] = 'Success';
            $response['msg'] = '';
			$response['data'] = $groups_bids;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function enable_bid(Request $request) {
		try {
		
                $group = GroupBiddingDetail::find($request->id);
                $group->status = '2';
                if($group->save()) {
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_reactive_bid');
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }
	
	public function disable_bid(Request $request) {
		try {
		
                $group = GroupBiddingDetail::find($request->id);
                $group->status = '4';
                if($group->save()) {
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_reactive_bid');
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function get_max_amount_bid_details(Request $request) {
        try {
			$lastauction = GroupBiddingDetail::where('id',$request->id)->get();
			$group_id = $lastauction[0]->group_id;
            $installment_no = $lastauction[0]->installment_no;
			$status = $lastauction[0]->status;
            $groups = BiddingLog::where('group_id',$group_id)->where('installment_no',$installment_no)->max('amount');
			$getinstallment_no = $installment_no;
			$maxmessageid = MessageLog::where('group_id',$group_id)->where('installment_no',$installment_no)->max('id');
			$message = MessageLog::where('id',$maxmessageid)->pluck('message');
			$message_last = $message[0];
			
			//get bidding amount
			$bidding_amounts = BiddingLog::where('group_id',$group_id)->where('installment_no',$installment_no)->orderBy('amount','desc')->get();
			foreach($bidding_amounts as $bidding_amount) {
				
				$bidding_amount->customer_code = isset($bidding_amount->customer_det) ? ($bidding_amount->customer_det->customer_code) : '';
                $bidding_amount->name = isset($bidding_amount->customer_det) ? ($bidding_amount->customer_det->name) : '';
                $bidding_amount->created_at = isset($bidding_amount->created_at) ? ($bidding_amount->created_at) : '';
				$bidding_amount->amount = isset($bidding_amount->amount) ? ($bidding_amount->amount) : '';
			}
			
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $groups;
			$response['data1'] = $message_last;
			$response['data2'] = $bidding_amounts;
			$response['data3'] = $getinstallment_no;
			$response['data4'] = $status;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	
}
