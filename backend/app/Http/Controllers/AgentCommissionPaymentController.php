<?php
namespace App\Http\Controllers;

use App\Models\AgentCommissionPayment;
use App\Models\ChitDetail;
use App\Models\Group;
use App\Models\Employee;
use App\Models\AccountExpense;
use App\Models\Customer;
use Illuminate\Http\Request;
use DateTime;
use Validator;

class AgentCommissionPaymentController extends Controller
{    
    public function store_agent_commission_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_agent_id' => 'required',
            'payment_date' => 'required',
            'paid_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                if(isset($request->Commissions)) 
                {                      
                    $count = count($request->Commissions);
                    $commission_no = '';
                    for($i=0; $i<$count; $i++)
                    {   
                        $agent_commission_payment = new AgentCommissionPayment();
                        $agent_commission_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                        $agent_commission_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                        $agent_commission_payment->employee_agent_id = isset($request->employee_agent_id) ? ($request->employee_agent_id) : '0';
                        $agent_commission_payment->enrollment_id = isset($request->Commissions[$i]['enrollment_id']) ? ($request->Commissions[$i]['enrollment_id']) : '0';
                        $agent_commission_payment->customer_id = isset($request->Commissions[$i]['customer_id']) ? ($request->Commissions[$i]['customer_id']) : '0';
                        $agent_commission_payment->group_id = isset($request->Commissions[$i]['group_id']) ? ($request->Commissions[$i]['group_id']) : '0';
                        $agent_commission_payment->ticket_no = isset($request->Commissions[$i]['ticket_no']) ? ($request->Commissions[$i]['ticket_no']) : '0';
                        $agent_commission_payment->customer_commission_amount = isset($request->Commissions[$i]['customer_commission_amount']) ? ($request->Commissions[$i]['customer_commission_amount']) : '0';  
                        if(isset($request->payment_date)) {
                            $c_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                            $agent_commission_payment->payment_date = date('Y-m-d',strtotime($c_date));
                        }
                        else {
                            $agent_commission_payment->payment_date = '0000-00-00';
                        }
                        //$agent_commission_payment->payment_date = isset($request->payment_date) ? ($request->payment_date) : '0000-00-00';
                        $agent_commission_payment->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : 0;
                        $agent_commission_payment->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                        $agent_commission_payment->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '0';
                        $agent_commission_payment->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0'; 
                        if(isset($request->cheque_date)) {
                            $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                            $agent_commission_payment->cheque_date = date('Y-m-d',strtotime($c_date)); 
                        }
                        else {
                            $agent_commission_payment->cheque_date = '0000-00-00';
                        }                        //$agent_commission_payment->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : '0000-00-00';
                        if(isset($request->cheque_clear_return_date)) {
                            $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                            $agent_commission_payment->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                        }
                        else {
                            $agent_commission_payment->cheque_clear_return_date = '0000-00-00';
                        }     
                        //$agent_commission_payment->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : '0000-00-00';
                        $agent_commission_payment->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                        $agent_commission_payment->branch_name = isset($request->branch_name) ? ($request->branch_name) : '0';
                        $agent_commission_payment->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                        if(isset($request->transaction_date)) {
                            $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                            $agent_commission_payment->transaction_date = date('Y-m-d',strtotime($t_date)); 
                        }
                        else {
                            $agent_commission_payment->transaction_date = '0000-00-00';
                        }
                       // $agent_commission_payment->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : '0000-00-00';
                        $agent_commission_payment->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                        $agent_commission_payment->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                        $agent_commission_payment->status = isset($request->status) ? ($request->status) : 1;
						if($request->payment_type_id == 2){
							$agent_commission_payment->status = isset($request->status) ? ($request->status) : 2;
						}
                        if($agent_commission_payment->paid_amount > 0)
                        {
                            if($agent_commission_payment->customer_commission_amount !=0) {
                                if($agent_commission_payment->save()) {
                                    if($commission_no=='') {
                                        $commission_no = 'COMM-'.sprintf('%05d', $agent_commission_payment->id);
                                    }                                
                                    $agent_commission_payment->commission_no = $commission_no;
                                    if($agent_commission_payment->save())
                                    {
                                        if($request->payment_type_id !=2)
                                        {
                                            $chit = ChitDetail::find($agent_commission_payment->enrollment_id);
                                            $chit->paid_business_agent_commission = $chit->paid_business_agent_commission + $agent_commission_payment->customer_commission_amount;
                                            $chit->balance_business_agent_commission = $chit->balance_business_agent_commission - $agent_commission_payment->customer_commission_amount;
                                            $chit->save();   
                                        }
										
									// if($agent_commission_payment->payment_type_id == 1){
										
										// $add_exp = new AccountExpense();                
										// $add_exp->tenant_id = isset($agent_commission_payment->tenant_id) ? ($agent_commission_payment->tenant_id) : 0;
										
										// $add_exp->branch_id = isset($agent_commission_payment->branch_id) ? ($agent_commission_payment->branch_id) : 0;
										
										// $add_exp->entry_date = isset($agent_commission_payment->payment_date) ? ($agent_commission_payment->payment_date) : 0;
										
										// $add_exp->voucher_no = isset($agent_commission_payment->commission_no) ? ($agent_commission_payment->commission_no) : 0;
										
										// $add_exp->debit_account_head = 0;
										// $add_exp->debit_amount = 0;
										// $add_exp->credit_account_head = 1;
										// $add_exp->credit_amount = isset($agent_commission_payment->customer_commission_amount) ? ($agent_commission_payment->customer_commission_amount) : 0;
										// $add_exp->status = 1;
										// $add_exp->save();
									// }
									// else if(($agent_commission_payment->payment_type_id == 2) || ($agent_commission_payment->payment_type_id == 3)){
										
										// $bank_id = isset($agent_commission_payment->bank_name_id) ? ($agent_commission_payment->bank_name_id) : 0;
										// $bank = BankDetail::where('id','=',$agent_commission_payment->bank_name_id)->get();
										// foreach($bank as $banks) {
											// $bank_account_no = isset($banks->account_number) ? ($banks->account_number) : 0;
											
										// }
										// $accnt_head = AccountHead::where('bank_account_number','=',$bank_account_no)->get();
										// foreach($accnt_head as $accnt_heads) {
											// $account_head_id = isset($accnt_heads->id) ? ($accnt_heads->id) : 0;
											
										// }
										
										// $add_exp = new AccountExpense();                
										// $add_exp->tenant_id = isset($agent_commission_payment->tenant_id) ? ($agent_commission_payment->tenant_id) : 0;
										// $add_exp->branch_id = isset($agent_commission_payment->branch_id) ? ($agent_commission_payment->branch_id) : 0;
										// $add_exp->entry_date = isset($agent_commission_payment->payment_date) ? ($agent_commission_payment->payment_date) : 0;
										// $add_exp->voucher_no = isset($agent_commission_payment->receipt_no) ? ($agent_commission_payment->receipt_no) : 0;
										// $add_exp->debit_account_head = 0;
										// $add_exp->debit_amount = 0;
										// $add_exp->credit_account_head = $account_head_id;
										// $add_exp->credit_amount = isset($agent_commission_payment->customer_commission_amount) ? ($agent_commission_payment->customer_commission_amount) : 0;
										// $add_exp->status = 0;
										// $add_exp->save();
									// }
									// else {
										// $add_exp = new AccountExpense();                
										// $add_exp->tenant_id = isset($agent_commission_payment->tenant_id) ? ($agent_commission_payment->tenant_id) : 0;
										// $add_exp->branch_id = isset($agent_commission_payment->branch_id) ? ($agent_commission_payment->branch_id) : 0;
										// $add_exp->entry_date = isset($agent_commission_payment->payment_date) ? ($agent_commission_payment->payment_date) : 0;
										// $add_exp->voucher_no = isset($agent_commission_payment->commission_no) ? ($agent_commission_payment->commission_no) : 0;
										// $add_exp->debit_account_head = 0;
										// $add_exp->debit_amount = 0;
										// $add_exp->credit_account_head = 1;
										// $add_exp->credit_amount = isset($agent_commission_payment->customer_commission_amount) ? ($agent_commission_payment->customer_commission_amount) : 0;
										// $add_exp->status = 1;
										// $add_exp->save();
									// }	
                                    }
                                } 
                            }
                        }
                        else
                        {
                            $response['status'] = 'Error';
                            $response['msg'] = "Paid amount Invalid";
                            return response()->json($response, 200);
                        }
                    }
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_agent_commission_payment_added');
                    $response['data'] = $agent_commission_payment;
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

    public function update_agent_commission_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_agent_id' => 'required',
            'payment_date' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->commission_no)) {
                    $agent_commission_payment = AgentCommissionPayment::find($request->commission_no); 
                    $agent_commission_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $agent_commission_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $agent_commission_payment->employee_agent_id = isset($request->employee_agent_id) ? ($request->employee_agent_id) : '0';
                    if(isset($request->payment_date)) {
                        $c_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                        $agent_commission_payment->payment_date = date('Y-m-d',strtotime($c_date));
                    }
                    else {
                        $agent_commission_payment->payment_date = '0000-00-00';
                    }
                    $agent_commission_payment->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                    $agent_commission_payment->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                    $agent_commission_payment->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '0';
                    $agent_commission_payment->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0';
                    if(isset($request->cheque_date)) {
                        $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $agent_commission_payment->cheque_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $agent_commission_payment->cheque_date = '0000-00-00';
                    }
                    if(isset($request->cheque_clear_return_date)) {
                        $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                        $agent_commission_payment->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                    }
                    else {
                        $agent_commission_payment->cheque_clear_return_date = '0000-00-00';
                    }
                    $agent_commission_payment->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                    $agent_commission_payment->branch_name = isset($request->branch_name) ? ($request->branch_name) : '0';
                    $agent_commission_payment->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                    if(isset($request->transaction_date)) {
                        $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $agent_commission_payment->transaction_date = date('Y-m-d',strtotime($t_date)); 
                    }
                    else {
                        $agent_commission_payment->transaction_date = '0000-00-00';
                    }
                    $agent_commission_payment->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                    $agent_commission_payment->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($agent_commission_payment->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_agent_commission_payment_updated');
                        $response['data'] = $agent_commission_payment;
                        return response()->json($response,200);
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

    public function get_agent_commission_payments(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $agent_commission_payments = AgentCommissionPayment::where($cond)->orderBy('id','desc')->get();
            if($agent_commission_payments) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $i=1;
                foreach($agent_commission_payments as $agent_commission_payment) {
                    $agent_commission_payment->sno = $i;
                    $agent_commission_payment->tenant_info = isset($agent_commission_payment->tenant_det) ? ($agent_commission_payment->tenant_det) : [];
                    $agent_commission_payment->branch_info = isset($agent_commission_payment->branch_det) ? ($agent_commission_payment->branch_det) : [];
                    $agent_commission_payment->employee_agent_info = isset($agent_commission_payment->employee_agent_det) ? ($agent_commission_payment->employee_agent_det) : [];
                    $agent_commission_payment->enrollment_info = isset($agent_commission_payment->enrollment_det) ? ($agent_commission_payment->enrollment_det) : [];
                    $agent_commission_payment->group_info =  isset($agent_commission_payment->enrollment_det->group_det) ? ($agent_commission_payment->enrollment_det->group_det->group_name) : [];
                    $agent_commission_payment->customer_info =  isset($agent_commission_payment->enrollment_det->customer_det) ? ($agent_commission_payment->enrollment_det->customer_det->name) : [];
                    $agent_commission_payment->payment_type_info = isset($agent_commission_payment->payment_type_det) ? ($agent_commission_payment->payment_type_det) : [];
                    $agent_commission_payment->bank_name_det = isset($agent_commission_payment->bank_name_det) ? ($agent_commission_payment->bank_name_det) : [];
                    $agent_commission_payment->payment_date = ($agent_commission_payment->payment_date!=0) ? date('d/m/Y',strtotime($agent_commission_payment->payment_date)) : '';
                    $agent_commission_payment->cheque_date = ($agent_commission_payment->cheque_date!=0) ? date('d/m/Y',strtotime($agent_commission_payment->cheque_date)) : '';
                    $agent_commission_payment->transaction_date = ($agent_commission_payment->transaction_date!=0) ? date('d/m/Y',strtotime($agent_commission_payment->transaction_date)) : '';
                    $agent_commission_payment->paid_amount = isset($agent_commission_payment->paid_amount) ? (strval(round($agent_commission_payment->paid_amount))) : [];
                    $i++;
                }
                $response['data'] = $agent_commission_payments;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_agent_commission_payments(Request $request) {
        try { 
            //$cond = $request->conditions; 
            if(isset($request->tenant_id)) {
                $agent_commission_payments = AgentCommissionPayment::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->groupBy('commission_no')->get();
            }
            else {
                $agent_commission_payments = AgentCommissionPayment::orderBy('id','desc')->groupBy('commission_no')->get();
            }
            $i=1;
            foreach($agent_commission_payments as $agent_commission_payment) {
                $agent_commission_payment->sno = $i;
                $agent_commission_payment->branch_info = isset($agent_commission_payment->branch_det) ? ($agent_commission_payment->branch_det) : [];
                $agent_commission_payment->employee_agent_info = isset($agent_commission_payment->employee_agent_det) ? ($agent_commission_payment->employee_agent_det) : [];
                $agent_commission_payment->paid_amount = isset($agent_commission_payment->paid_amount) ? (strval(round($agent_commission_payment->paid_amount))) : [];
                $i++;
            }
            if($agent_commission_payments) {
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $agent_commission_payments;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_agent_commission_payment(Request $request) {
        try {
            if(isset($request->commission_no)) {
                $agent_commission_payment = AgentCommissionPayment::where('commission_no','=',$request->commission_no)->get();
                foreach($agent_commission_payment as $deletes)
                {
                    $deletes->deleted_by = $request->deleted_by;
                    $deletes->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                    if($deletes->save()) {
                        $deletes->delete();
                    }                    
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_agent_commission_payment_deleted');
                return response()->json($response,200);
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
            $response['msg'] = $agent_commission_payment;
            return response()->json($response, 401);
        }
    }

    public function update_status(Request $request) {
        try {
            if(isset($request->id)) {
                $agent_commission_payment = AgentCommissionPayment::find($request->id); 
                $agent_commission_payment->status = ($agent_commission_payment->status) ? 0 : 1;
                if($agent_commission_payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_status_updated');
                    return response()->json($response,200);
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

    public function get_agent_enrol_details(Request $request)
    {
        try
        {   // introducer_id, branch_id
            $cond = $request->conditions; 
            $chit_details = ChitDetail::where($cond)->where('balance_business_agent_commission','!=',0)->where('agent_type','=',3)->orderBy('id','desc')->get();
            $response_data = [];
            foreach($chit_details as $cd) 
            {   
                $enrol_det = [];
                $enrol_det['group_info'] = [];
                if(isset($cd->group_det)) {
                    $enrol_det['group_info'] = isset($cd->group_det->group_name) ? ($cd->group_det->group_name) : [];
                }
                $enrol_det['customer_info'] = [];
                if(isset($cd->customer_det)) {
                    $enrol_det['customer_info'] = isset($cd->customer_det->name) ? ($cd->customer_det->name) : [];
                }
                $enrol_det['ticket_no'] = $cd->ticket_no;
                $enrol_det['group_id'] = $cd->group_id;
                $enrol_det['customer_id'] = $cd->customer_id;
                $enrol_det['enrollment_id'] = $cd->id;
                $enrol_det['total_commission'] = $cd->total_business_agent_commission;
                $commission_payments = AgentCommissionPayment::selectRaw('*,sum(customer_commission_amount) as total_paid_commission')->where('enrollment_id',$cd->id)->where('status',1)->groupBy('commission_no')->get();
                $enrol_det['paid_commission'] = isset($commission_payments[0]->total_paid_commission) ? ($commission_payments[0]->total_paid_commission) : 0;
                
                $enrol_det['pending_commission'] = $enrol_det['total_commission'] - $enrol_det['paid_commission'];
                $chits_table = ChitDetail::selectRaw('*,sum(balance_business_agent_commission) as overall_pending')->where('agent_type','=',3)->where('balance_business_agent_commission','>',0)->where('introducer_id','=',$cd->introducer_id)->where('branch_id','=',$cd->branch_id)->groupBy('introducer_id')->get();
                $enrol_det['overall_pending'] = round($chits_table[0]->overall_pending);
                if($chit_details) {
                    array_push($response_data,$enrol_det);
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);                
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cheque_return_commision_payment(Request $request)
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
                $agent_commission_payment = AgentCommissionPayment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $agent_commission_payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $agent_commission_payment->cheque_clear_return_date = '0000-00-00';
                } 
                $agent_commission_payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $agent_commission_payment->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $agent_commission_payment->status = isset($request->status) ? ($request->status) : 3;
                if($agent_commission_payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $agent_commission_payment;
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
	
	public function cheque_cleared_commision_payment(Request $request)
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
                $agent_commission_payment = AgentCommissionPayment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $agent_commission_payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $agent_commission_payment->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $agent_commission_payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $agent_commission_payment->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $agent_commission_payment->status = isset($request->status) ? ($request->status) : 1;
                if($agent_commission_payment->save()) {
                     $chit = ChitDetail::find($agent_commission_payment->enrollment_id);
                    $chit->paid_business_agent_commission = $chit->paid_business_agent_commission + $agent_commission_payment->customer_commission_amount;
                    $chit->balance_business_agent_commission = $chit->balance_business_agent_commission - $agent_commission_payment->customer_commission_amount;
                    if($chit->save())
                    {
                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_cheque_cleared');
                        $response['data'] = $agent_commission_payment;
                        return response()->json($response,200);
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
                $response['msg'] = $e;
                return response()->json($response, 401);
            }
        }
    }
}
