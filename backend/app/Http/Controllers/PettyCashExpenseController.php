<?php
namespace App\Http\Controllers;

use App\Models\PettyCashExpense;
use App\Models\PettyCashIncome;
use App\Models\AccountExpense;
use App\Models\AccountGroup;
use App\Models\AccountHead;
use App\Models\Receipt;
use App\Models\ChitDetail;
use App\Models\Customer;
use App\Models\BiddingDetail;
use App\Models\Payment;
use App\Models\AdvanceReceipt;
use App\Models\AuctionOnHoliday;
use App\Models\City;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;
use DateTime;
use DatePeriod;
use DB;

class PettyCashExpenseController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_petty_cash_expense(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'expenses_amount'=>'required',
            'expenses_date'=>'required',
            'employee_id'=>'required',
            'expenses_type_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $pettycashexpense=new PettyCashExpense(); 
                $pettycashexpense->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $pettycashexpense->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $pettycashexpense->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                if(isset($request->expenses_date)) {
                    $s_date = ($request->expenses_date['year']).'-'.sprintf('%02d',($request->expenses_date['month'])).'-'.sprintf('%02d',($request->expenses_date['day'])); 
                    $pettycashexpense->expenses_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $pettycashexpense->expenses_date = '0000-00-00';
                }
                $pettycashexpense->expenses_amount = isset($request->expenses_amount) ? ($request->expenses_amount) : 0;
                $pettycashexpense->expenses_type_id = isset($request->expenses_type_id) ? ($request->expenses_type_id) : 0;  
                $pettycashexpense->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
				$pettycashexpense->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
				$pettycashexpense->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ; 
				if(isset($request->cheque_date)) {
					$f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
					$pettycashexpense->cheque_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					// $pettycashexpense->cheque_date  = '0000-00-00';
				}
				$pettycashexpense->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
				$pettycashexpense->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '' ;
				if(isset($request->transaction_date)) {
					$f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
					$pettycashexpense->transaction_date  = date('Y-m-d',strtotime($f_date));
				}
				else {
					// $pettycashexpense->transaction_date  = '0000-00-00';
				}
				$pettycashexpense->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;           
                $pettycashexpense->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashexpense->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $pettycashexpense->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2)
                {
					$pettycashexpense->status = isset($request->status) ? ($request->status) : 2;
				}
                if($pettycashexpense->expenses_amount > 0)
                {
                    if($pettycashexpense->save()){
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_petty_cash_exp_added');
                        $response['data']=$pettycashexpense;
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
                    $response['status']="Error";
                    $response['msg']= "Expenses Amount invalid";
                    return response()->json($response,200);
                }
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['statuse']=$e;
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
    }
  
    public function update_petty_cash_expense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'expenses_amount'=>'required',
            'expenses_date'=>'required',
            'employee_id'=>'required',
            'expenses_type_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) { 
                    $pettycashexpense = PettyCashExpense::find($request->id);  
                    $pettycashexpense->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0; 
                    $pettycashexpense->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $pettycashexpense->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                    if(isset($request->expenses_date)) {
                        $s_date = ($request->expenses_date['year']).'-'.sprintf('%02d',($request->expenses_date['month'])).'-'.sprintf('%02d',($request->expenses_date['day'])); 
                        $pettycashexpense->expenses_date = date('Y-m-d',strtotime($s_date));
                    }
                    $pettycashexpense->expenses_amount = isset($request->expenses_amount) ? ($request->expenses_amount) : 0;
                    $pettycashexpense->expenses_type_id = isset($request->expenses_type_id) ? ($request->expenses_type_id) : 0;        
					$pettycashexpense->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
					$pettycashexpense->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
					$pettycashexpense->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
					if(isset($request->cheque_date)) {
						$f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
						$pettycashexpense->cheque_date  = date('Y-m-d',strtotime($f_date)); 
					}
					else {
						$pettycashexpense->cheque_date  = '0000-00-00';
					}
					$pettycashexpense->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
					$pettycashexpense->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
					if(isset($request->transaction_date)) {
						$f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
						$pettycashexpense->transaction_date  = date('Y-m-d',strtotime($f_date)); 
					}
					else {
						$pettycashexpense->transaction_date  = '0000-00-00';
					}
					$pettycashexpense->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;if($request->payment_type_id == 2){
							$pettycashincome->status = isset($request->status) ? ($request->status) : 2;
						}           
                    $pettycashexpense->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $pettycashexpense->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($pettycashexpense->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_petty_cash_exp_updated');
                        $response['data'] =$pettycashexpense;
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

    public function get_petty_cash_expenses(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $pettycashexpenses = PettyCashExpense::where($cond)->orderBy('id','desc')->get();
            foreach($pettycashexpenses as $pettycashexpense) {
               $pettycashexpense->tenant_info = isset($pettycashexpense->tenant_det) ? ($pettycashexpense->tenant_det) : [];
                $pettycashexpense->branch_info = isset($pettycashexpense->branch_det) ? ($pettycashexpense->branch_det) : [] ;
                $pettycashexpense->employee_info = isset($pettycashexpense->employee_det) ? ($pettycashexpense->employee_det) : [] ;
                $pettycashexpense->expenses_type_info = isset($pettycashexpense->expenses_type_det) ? ($pettycashexpense->expenses_type_det) : [] ;
                $pettycashexpense->expenses_date = ($pettycashexpense->expenses_date!=0) ? date('d/m/Y',strtotime($pettycashexpense->expenses_date)) : '';
				$pettycashexpense->payment_type_info = isset($pettycashexpense->payment_type_det) ? ($pettycashexpense->payment_type_det) : [] ;
				$pettycashexpense->bank_info = isset($pettycashexpense->bank_det) ? ($pettycashexpense->bank_det) : [];
                $pettycashexpense->received_date = ($pettycashexpense->received_date!=0) ? date('d/m/Y',strtotime(pettycashexpense)) : '';
				$pettycashexpense->cheque_date = ($pettycashexpense->cheque_date!=0) ? date('d/m/Y',strtotime($pettycashexpense->cheque_date)) : '';
				$pettycashexpense->transaction_date = ($pettycashexpense->transaction_date!=0) ? date('d/m/Y',strtotime($pettycashexpense->transaction_date)) : '';
				
				$incomes=PettyCashIncome::where('branch_id',$pettycashexpense->branch_id)->sum('amount_received');
			
            $balance_amount = 0;
                               
                $expenses=PettyCashExpense::selectRaw('sum(expenses_amount) as total_expense')->where('branch_id',$pettycashexpense->branch_id)->get();
                $balance_amount = $incomes - $expenses[0]->total_expense;
            
			$pettycashexpense->avaliable_cash = $balance_amount;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $pettycashexpenses;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_petty_cash_expenses(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $pettycashexpenses = PettyCashExpense::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $pettycashexpenses = PettyCashExpense::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($pettycashexpenses as $pettycashexpense) {
                $pettycashexpense->sno = $i;
                $pettycashexpense->tenant_info = isset($pettycashexpense->tenant_det) ? ($pettycashexpense->tenant_det) : [];
                $pettycashexpense->branch_info = isset($pettycashexpense->branch_det) ? ($pettycashexpense->branch_det) : [];
                $pettycashexpense->employee_info = isset($pettycashexpense->employee_det) ? ($pettycashexpense->employee_det) : [];
                $pettycashexpense->expenses_type_info = isset($pettycashexpense->expenses_type_det) ? ($pettycashexpense->expenses_type_det) : [];
				$pettycashexpense->payment_type_info = isset($pettycashexpense->payment_type_det) ? ($pettycashexpense->payment_type_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $pettycashexpenses;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_petty_cash_expense(Request $request) {
        try {
            if(isset($request->id)) {
                $pettycashexpense = PettyCashExpense::find($request->id); 
                $pettycashexpense->deleted_by = $request->deleted_by;
                $pettycashexpense->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($pettycashexpense->save()) {
                    $pettycashexpense->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_petty_cash_exp_deleted');
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
                $pettycashexpense = PettyCashExpense::find($request->id); 
                $pettycashexpense->status = ($pettycashexpense->status) ? 0 : 1;
                if($pettycashexpense->save()) {
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
	
	public function cheque_return_petty_cash_expense(Request $request)
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
                $pettycashexpense = PettyCashExpense::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $pettycashexpense->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $pettycashexpense->cheque_clear_return_date = '0000-00-00';
                } 
                $pettycashexpense->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashexpense->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $pettycashexpense->status = isset($request->status) ? ($request->status) : 3;
                if($pettycashexpense->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $pettycashexpense;
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
	
	public function cheque_cleared_petty_cash_expense(Request $request)
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
                $pettycashexpense = PettyCashExpense::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $pettycashexpense->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $pettycashexpense->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $pettycashexpense->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashexpense->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $pettycashexpense->status = isset($request->status) ? ($request->status) : 1;
                if($pettycashexpense->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $pettycashexpense;
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
	
	public function store_accounts_expense(Request $request)
    { 
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
			 
				// $account_det['voucher_no'] =  AccountExpense::where(array('voucher_no'=>$request->voucher_no,'tenant_id'=>$request->tenant_id))->get();       
                // if(count($account_det['voucher_no'])>0)
                // {
                    // $response['status'] = "Error";
                    // $response['msg'] = \Lang::get('api.account_no_already_exist');
                    // return response()->json($response, 200);
                // } 
				// else{
				$credit_count = count($request->credits);
				
				if($credit_count>0){
					 
						for($i=0; $i<$credit_count; $i++)
						{	
					if($request->expense_type == 1){
						$exp_type = 3;
					} else if($request->expense_type == 3){
						$exp_type = 2;
					} else {
						$exp_type = 1;
					}
                                $account_det = new AccountExpense();
				$account_det['remarks']  = isset($request->remarks) ? $request->remarks : 0;                                                                          
                                $account_det['tenant_id'] = isset($request->tenant_id) ? $request->tenant_id : 0;
                                $account_det['branch_id']  = isset($request->branch_id) ? $request->branch_id : 0;
                                $account_det['account_type']  = $exp_type;
                               if(isset($request->entry_date)) {
									$c_date = ($request->entry_date['year']).'-'.sprintf('%02d',($request->entry_date['month'])).'-'.sprintf('%02d',($request->entry_date['day'])); 
									$account_det->entry_date = date('Y-m-d',strtotime($c_date)); 
								}
								else {
									$account_det->entry_date = '0000-00-00';
								}  
                                       
                               // $account_det['entry_date'] = isset($request->entry_date) ? $request->entry_date : 0;
							   if($request->tenant_id == 50){
								   $cnt_no = AccountExpense::where('account_type','!=',0)->count('id');
			
			if($cnt_no==0){
			$voucher_no = "SPAC-1";	
			} else {
			$max_id = AccountExpense::where('account_type','!=',0)->max('id');
			$max_voucher_no = AccountExpense::where('id',$max_id)->pluck('voucher_no');
			
			$voucher_no = $max_voucher_no[0];	
			
			$vou_no = explode('-',$voucher_no);
			
			$v_no = $vou_no[1]+1;
			$voucher_no = "SAPT-" . $v_no;	
			}
			$account_det['voucher_no'] = $voucher_no;
							   } else {
								  $account_det['voucher_no'] = isset($request->voucher_no) ? $request->voucher_no : 0; 
							   }
                                
                                $account_det['debit_account_head'] = isset($request->credits[$i]['debit_account_head']) ? $request->credits[$i]['debit_account_head'] : 0;
				$account_det['debit_remarks'] = isset($request->credits[$i]['debit_remarks']) ? $request->credits[$i]['debit_remarks'] : 0;				
                                $account_det['debit_amount'] = isset($request->credits[$i]['debit_amount']) ? $request->credits[$i]['debit_amount'] : 0;
								$account_det['credit_account_head'] = isset($request->credits[$i]['credit_account_head']) ? $request->credits[$i]['credit_account_head'] : 0;
				$account_det['credit_account_head'] = isset($request->credits[$i]['credit_account_head']) ? $request->credits[$i]['credit_account_head'] : 0;
				$account_det['credit_remarks'] = isset($request->credits[$i]['credit_remarks']) ? $request->credits[$i]['credit_remarks'] : 0;				
                                				
                                $account_det['credit_amount'] = isset($request->credits[$i]['credit_amount']) ? $request->credits[$i]['credit_amount'] : 0;
                                
                                //$account_det['remarks'] = isset($request->Employees[$i]['remarks']) ? $request->Employees[$i]['remarks'] : '';
                                $account_det['created_by']  = isset($request->created_by) ? $request->created_by : 0; 
                                $account_det['status'] = isset($request->status) ? ($request->status) : 1;
								if($request->tenant_id == 42){
								$account_det['receipt_no'] = 'yes';	
								}
								
                                $account_det ->save(); 
								
                            }
				}
							$debit_count = count($request->debits);
				
				if($debit_count>0){
					 
						for($i=0; $i<$debit_count; $i++)
						{	
					
                                $account_det = new AccountExpense();
				$account_det['remarks']  = isset($request->remarks) ? $request->remarks : 0;                                                                          
                                $account_det['tenant_id'] = isset($request->tenant_id) ? $request->tenant_id : 0;
                                $account_det['branch_id']  = isset($request->branch_id) ? $request->branch_id : 0;
                               $account_det['account_type']  = $exp_type;
                                 if(isset($request->entry_date)) {
									$c_date = ($request->entry_date['year']).'-'.sprintf('%02d',($request->entry_date['month'])).'-'.sprintf('%02d',($request->entry_date['day'])); 
									$account_det->entry_date = date('Y-m-d',strtotime($c_date)); 
								}
								else {
									$account_det->entry_date = '0000-00-00';
								}           
                               // $account_det['entry_date'] = isset($request->entry_date) ? $request->entry_date : 0;
							    if($request->tenant_id == 50){
									 $account_det['voucher_no'] = $voucher_no;
								} else {
								 $account_det['voucher_no'] = isset($request->voucher_no) ? $request->voucher_no : 0;	
								}
                               
                                $account_det['debit_account_head'] = isset($request->debits[$i]['debit_account_head']) ? $request->debits[$i]['debit_account_head'] : 0;
				$account_det['debit_remarks'] = isset($request->debits[$i]['debit_remarks']) ? $request->debits[$i]['debit_remarks'] : 0;
                                $account_det['debit_amount'] = isset($request->debits[$i]['debit_amount']) ? $request->debits[$i]['debit_amount'] : 0;
								
				$account_det['credit_account_head'] = isset($request->debits[$i]['credit_account_head']) ? $request->debits[$i]['credit_account_head'] : 0;
				$account_det['credit_remarks'] = isset($request->debits[$i]['credit_remarks']) ? $request->debits[$i]['credit_remarks'] : 0;
                                $account_det['credit_amount'] = isset($request->debits[$i]['credit_amount']) ? $request->debits[$i]['credit_amount'] : 0;
                                
                                //$account_det['remarks'] = isset($request->Employees[$i]['remarks']) ? $request->Employees[$i]['remarks'] : '';
                                $account_det['created_by']  = isset($request->created_by) ? $request->created_by : 0; 
                                $account_det['status'] = isset($request->status) ? ($request->status) : 1;
								if($request->tenant_id == 42){
								$account_det['receipt_no'] = 'yes';	
								}
								 
                                $account_det ->save(); 
                            }
				}
						$response['status'] = 'Success';
						$response['msg'] = \Lang::get('api.success_expenses_added');
						return response()->json($response,200);	
				//}	
				
				
			}		
				catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,401);
			}
		}

		}
		
	public function list_account_expenses(Request $request) {
    try {
        // Date Parsing
        if(isset($request->start_date)) {
            $s_date = $request->start_date['year'].'-'.sprintf('%02d',$request->start_date['month']).'-'.sprintf('%02d',$request->start_date['day']);
            $start_date = date('Y-m-d', strtotime($s_date));
        }
        if(isset($request->end_date)) {
            $s_date = $request->end_date['year'].'-'.sprintf('%02d',$request->end_date['month']).'-'.sprintf('%02d',$request->end_date['day']);
            $end_date = date('Y-m-d', strtotime($s_date));
        }

        $to_date = date('Y-m-d', strtotime('-1 day', strtotime($start_date)));
        $start_bal_date = "2023-04-01";

        // Opening Balance
        $head = AccountHead::where('branch_id', $request->branch_id)->where('id', $request->account_head)->first();
        $bal = $head->amount;
        $crd = $head->collection_type == 'Debit' ? 'Dr' : 'Cr';

        $open_deb_bal = AccountExpense::where('branch_id', $request->branch_id)
            ->where('debit_account_head', $request->account_head)
            ->whereBetween('entry_date', [$start_bal_date, $to_date])
            ->sum('debit_amount');

        $open_cre_bal = AccountExpense::where('branch_id', $request->branch_id)
            ->where('credit_account_head', $request->account_head)
            ->whereBetween('entry_date', [$start_bal_date, $to_date])
            ->sum('credit_amount');

        $open_cre_bal += $bal;

        $bal = $open_cre_bal - $open_deb_bal;
        $bal_crd = $bal < 0 ? 'Dr' : 'Cr';
        $bal = abs($bal);

        // Fetch all expenses (both debit and credit) in one go using OR logic
        $expenses = AccountExpense::with(['branch_det', 'account_head_det', 'debit_account_head_det'])
            ->where('branch_id', $request->branch_id)
            ->whereBetween('entry_date', [$start_date, $end_date])
            ->where(function($query) use ($request) {
                $query->where('debit_account_head', $request->account_head)
                      ->orWhere('credit_account_head', $request->account_head);
            })
            ->orderBy('id', 'desc')
            ->get();
      
        $s_no = 1;
        $total_debit = 0;
        $total_credit = 0;
        $combined_expenses = [];

        foreach($expenses as $expense) {
            $entry = clone $expense;
            $entry->sno = $s_no++;
            $entry->branch_name = isset($entry->branch_det->branch_name) ? $entry->branch_det->branch_name : '';
            $entry->name = $expense->debit_account_head == $request->account_head ? ($entry->account_head_det->name ?? '-') : '-';
            $entry->credit_name = $expense->credit_account_head == $request->account_head ? ($entry->debit_account_head_det->name ?? '-') : '-';

            $total_debit += $entry->debit_amount;
            $total_credit += $entry->credit_amount;

            $combined_expenses[] = $entry;
        }

        // Final Balance
        $closing_balance = ($bal_crd == 'Dr' ? $bal : -$bal) + $total_debit - $total_credit;
        $closing_balance_str = ($closing_balance >= 0 ? abs($closing_balance).'Dr' : abs($closing_balance).'Cr');

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'head_name' => $head->name,
            'total_debit_amounts' => $total_debit,
            'total_credit_amounts' => $total_credit,
            'opening_balance' => $bal,
            'credit_debit' => $bal_crd,
            'closing_balance' => $closing_balance_str,
            'data1' => $combined_expenses,
            'data' => $combined_expenses,
        ], 200);

    } catch(\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => 'Something went wrong',
        ], 401);
    }
}
	
	public function trial_balance(Request $request) {
        $tenant_id = 63;
    $branch_id = 4;
    $from_date = '2025-07-01';
    $to_date = '2025-07-16';

    // Base query with filters
    $query = DB::table('account_expenses')
        ->select(
            'account_heads.id as account_head_id',
            'account_heads.name as account_head_name',
            'account_groups.group_name as group_name',
            DB::raw('SUM(account_expenses.debit_amount) as total_debit'),
            DB::raw('SUM(account_expenses.credit_amount) as total_credit')
        )
        ->leftJoin('account_heads', function ($join) {
            $join->on('account_expenses.debit_account_head', '=', 'account_heads.id')
                 ->orOn('account_expenses.credit_account_head', '=', 'account_heads.id');
        })
        ->leftJoin('account_groups', 'account_heads.group_id', '=', 'account_groups.id')
        ->where('account_expenses.tenant_id', $tenant_id);

    if ($branch_id) {
        $query->where('account_expenses.branch_id', $branch_id);
    }

    if ($from_date && $to_date) {
        $query->whereBetween('account_expenses.entry_date', [$from_date, $to_date]);
    }

    $query->groupBy('account_heads.id', 'account_heads.name', 'account_groups.group_name');

    $results = $query->get();

    // Format output
    $trial_balance = [];
    foreach ($results as $row) {
        $net = $row->total_debit - $row->total_credit;
        $trial_balance[] = [
            'account_head_id' => $row->account_head_id,
            'account_head_name' => $row->account_head_name,
            'group_name' => $row->group_name,
            'debit' => $net > 0 ? $net : 0,
            'credit' => $net < 0 ? abs($net) : 0,
        ];
    }

    return response()->json([
        'status' => 'Success',
        'data' => $trial_balance
    ]);
    }
	
	public function profit_loss_expense_report(Request $request) {
        try { 
					$branch = AccountExpense::where('branch_id',$request->branch_id)->get();
					foreach($branch as $branch_s) {
						$branch_s->chit_fund_name = isset($branch_s->tenant_det->chit_fund_name) ? ($branch_s->tenant_det->chit_fund_name) : [];
						$branch_s->branch_name = isset($branch_s->branch_det->branch_name) ? ($branch_s->branch_det->branch_name) : [];
					}
		
		if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}			
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}
			 
         
			       $exp_types = AccountGroup::where('main_under','Expenses')->where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
			       $overall_details=[];
			       $details = [];
			
			
				foreach($exp_types as $exp_type) {
					
				//$details=[];	
				$exp_heads = AccountHead::where('group_id',$exp_type->id)->where('id','!=',41)->orderBy('id','desc')->get();
				$total_debit_amounts=0;
				$total_credit_amounts=0;
				$total_expense=0;
				
				
				
				foreach($exp_heads as $exp_head) {
					$head = [];
					
					$head['expense_head_name'] = $exp_head->name;
					$pettycashexpenses = AccountExpense::selectRaw('sum(debit_amount) as debit_amount')->where('branch_id',$request->branch_id)->where('debit_account_head',$exp_head->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					 
					$head['expense_debit_amount'] = $pettycashexpenses[0]->debit_amount;
					
					$pettycashexpenses = AccountExpense::selectRaw('sum(credit_amount) as credit_amount')->where('branch_id',$request->branch_id)->where('credit_account_head',$exp_head->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					$head['expense_credit_amount'] = $pettycashexpenses[0]->credit_amount;
					
					
					
					$total_expense_wise = $head['expense_debit_amount'] - $head['expense_credit_amount'];
					if($total_expense_wise < 0) {
					$head['total_expense'] = $total_expense_wise;
					} else {
					$head['total_expense'] = $total_expense_wise;
					}
					$total_debit_amounts += $head['expense_debit_amount'];
					$total_credit_amounts += $head['expense_credit_amount'];
					$total_expense += $head['total_expense'];
					
					array_push($details,$head);
				 }
				
				 $exp_type->expense_det=$details;
				 
				$exp_type['expense_total_debit']=$total_debit_amounts;
				$exp_type['expense_total_credit']=$total_credit_amounts;
				$exp_type['expense_total']=$total_expense;
				
            }
			
			
			
			$exp_types1 = AccountGroup::where('main_under','Incomes')->where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
			$details1=[];	
			$i = 1;
				foreach($exp_types1 as $exp_type1) {
					
				
				$exp_heads1 = AccountHead::where('group_id',$exp_type1->id)->orderBy('id','desc')->get();
				$total_debit_amounts1=0;
				$total_credit_amounts1=0;
				$total_income=0;
				$head1['income_debit_amount'] =0;
				$head1['income_credit_amount'] =0;
				foreach($exp_heads1 as $exp_head1) {
					
					$head1 = [];
					$head1['income_head_name'] = $exp_head1->name;
					
					$pettycashexpenses1 = AccountExpense::selectRaw('sum(debit_amount) as debit_amount')->where('branch_id',$request->branch_id)->where('debit_account_head',$exp_head1->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					$head1['income_debit_amount'] = $pettycashexpenses1[0]->debit_amount;
					 $pettycashexpenses1 = AccountExpense::selectRaw('sum(credit_amount) as credit_amount')->where('branch_id',$request->branch_id)->where('credit_account_head',$exp_head1->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					 $head1['income_credit_amount'] = $pettycashexpenses1[0]->credit_amount;
					$total_income_wise =  $head1['income_debit_amount'] - $head1['income_credit_amount'];
					if($total_income_wise < 0) {
					$head1['total_income'] = $total_income_wise;
					} else {
					$head1['total_income'] = $total_income_wise;
					}
					
					
					array_push($details1,$head1);
					$total_debit_amounts1 += $head1['income_debit_amount'];
					$total_credit_amounts1 += $head1['income_credit_amount'];
					$total_income += $head1['total_income'];
				 }
				
				  $exp_type1->income_det=$details1;
				$exp_type1['income_total_debit']=$total_debit_amounts1;
				$exp_type1['income_total_credit']=$total_credit_amounts1;
				$exp_type1['income_total']=$total_income;
				
				   $i++;
            }
			
					 
			
			$expense=[];
			$income=[];
			
			array_push($expense,$exp_type);
			array_push($income,$exp_type1);
			 array_push($overall_details,$expense);
			array_push($overall_details,$income);
			
			$profit_amount = BiddingDetail::whereBetween('auction_date',[$start_date,$end_date])->sum('commission_amount');
			$receipt_amount = Receipt::whereBetween('receipt_date',[$start_date,$end_date])->sum('received_amount');
			$advance_amount = AdvanceReceipt::whereBetween('receipt_date',[$start_date,$end_date])->sum('receipt_amount');
			$total_paid = $receipt_amount + $advance_amount;
			
			$amount_disbursed = Payment::whereBetween('payment_date',[$start_date,$end_date])->where('payment_for',1)->sum('total_amount');
			$amount_refund = Payment::whereBetween('payment_date',[$start_date,$end_date])->where('payment_for',3)->sum('total_amount');
			
			$response['companyname'] = $branch_s->chit_fund_name;
			$response['branchname'] = $branch_s->branch_name;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $overall_details;
            $response['amount_received'] = $total_paid;
            $response['commission'] = $profit_amount;
            $response['amount_disbursed'] = $amount_disbursed;
            $response['amount_refund'] = $amount_refund;
            return response()->json($response,200);
			 
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function profit_loss_income_report(Request $request) {
        try { 
            
				$exp_types = AccountGroup::where('under','Incomes')->orderBy('id','desc')->get();
			
				foreach($exp_types as $exp_type) {
				$details=[];	
				$exp_heads = AccountHead::where('group_id',$exp_type->id)->orderBy('id','desc')->get();
				$total_debit_amounts=0;
				$total_credit_amounts=0;
				foreach($exp_heads as $exp_head) {
					$head = [];
					$head['head_name'] = $exp_head->name;
					$pettycashexpenses = AccountExpense::selectRaw('sum(debit_amount) as debit_amount')->where('debit_account_head',$exp_head->id)->get();
					$head['debit_amount'] = $pettycashexpenses[0]->debit_amount;
					$pettycashexpenses = AccountExpense::selectRaw('sum(credit_amount) as credit_amount')->where('credit_account_head',$exp_head->id)->get();
					$head['credit_amount'] = $pettycashexpenses[0]->credit_amount;
					array_push($details,$head);
					$total_debit_amounts += $head['debit_amount'];
					$total_credit_amounts += $head['credit_amount'];
				 }
				$details['total_debit']=$total_debit_amounts;
				$details['total_credit']=$total_credit_amounts;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $details;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function balance_sheet_report(Request $request) {
      
		
		 try { 
		 
					// $branch = AccountExpense::where('branch_id',$request->branch_id)->get();
					// foreach($branch as $branch_s) {
						// $branch_s->chit_fund_name = isset($branch_s->tenant_det->chit_fund_name) ? ($branch_s->tenant_det->chit_fund_name) : [];
						// $branch_s->branch_name = isset($branch_s->branch_det->branch_name) ? ($branch_s->branch_det->branch_name) : [];
					// }
		
			if(isset($request->start_date)) { 
        		$s_date = '2024-07-01';
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}			
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}
			
			$exp_types_pro = AccountGroup::where('main_under','Expenses')->orderBy('id','desc')->get();
			$overall_details_pro=[];
			$details_pro = [];
			
			
				foreach($exp_types_pro as $exp_type_pro) {
					
				//$details=[];	
				$exp_heads_pro = AccountHead::where('group_id',$exp_type_pro->id)->orderBy('id','desc')->get();
				$total_debit_amounts_pro=0;
				$total_credit_amounts_pro=0;
				$total_expense_pro=0;
				
				
				
				foreach($exp_heads_pro as $exp_head_pro) {
					$head_pro = [];
					
					$head_pro['expense_head_name'] = $exp_head_pro->name;
					$pettycashexpenses_pro = AccountExpense::selectRaw('sum(debit_amount) as debit_amount')->where('branch_id',$request->branch_id)->where('debit_account_head',$exp_head_pro->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					 
					$head_pro['expense_debit_amount'] = $pettycashexpenses_pro[0]->debit_amount;
					
					$pettycashexpenses_pro = AccountExpense::selectRaw('sum(credit_amount) as credit_amount')->where('branch_id',$request->branch_id)->where('credit_account_head',$exp_head_pro->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					$head_pro['expense_credit_amount'] = $pettycashexpenses_pro[0]->credit_amount;
					
					//$pettycashexpenses->chit_fund_name = isset($pettycashexpenses->tenant_det->chit_fund_name) ? ($pettycashexpenses->tenant_det->chit_fund_name) : [];
					
					//$pettycashexpenses->branch_name = isset($pettycashexpenses->branch_det->branch_name) ? ($pettycashexpenses->branch_det->branch_name) : [];
					
					$total_expense_wise_pro = $head_pro['expense_debit_amount'] - $head_pro['expense_credit_amount'];
					if($total_expense_wise_pro < 0) {
					$head_pro['total_expense'] = $total_expense_wise_pro;
					} else {
					$head_pro['total_expense'] = $total_expense_wise_pro;
					}
					$total_debit_amounts_pro += $head_pro['expense_debit_amount'];
					$total_credit_amounts_pro += $head_pro['expense_credit_amount'];
					$total_expense_pro += $head_pro['total_expense'];
					
					array_push($details_pro,$head_pro);
				 }
				
				 $exp_type_pro->expense_det_pro=$details_pro;
				 
				$exp_type_pro['expense_total_debit']=$total_debit_amounts_pro;
				$exp_type_pro['expense_total_credit']=$total_credit_amounts_pro;
				$exp_type_pro['expense_total']=$total_expense_pro;
				
            }
			
			
			$exp_types1_pro = AccountGroup::where('main_under','Incomes')->orderBy('id','desc')->get();
			$details1_pro=[];	
				foreach($exp_types1_pro as $exp_type1_pro) {
					
				
				$exp_heads1_pro = AccountHead::where('group_id',$exp_type1_pro->id)->orderBy('id','desc')->get();
				$total_debit_amounts1_pro=0;
				$total_credit_amounts1_pro=0;
				$total_income_pro=0;
				$head1_pro['income_debit_amount'] =0;
				$head1_pro['income_credit_amount'] =0;
				foreach($exp_heads1_pro as $exp_head1_pro) {
					
					$head1_pro = [];
					$head1_pro['income_head_name'] = $exp_head1_pro->name;
					
					$pettycashexpenses1_pro = AccountExpense::selectRaw('sum(debit_amount) as debit_amount')->where('branch_id',$request->branch_id)->where('debit_account_head',$exp_head1_pro->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					$head1_pro['income_debit_amount'] = $pettycashexpenses1_pro[0]->debit_amount;
					 $pettycashexpenses1_pro = AccountExpense::selectRaw('sum(credit_amount) as credit_amount')->where('branch_id',$request->branch_id)->where('credit_account_head',$exp_head1_pro->id)->whereBetween('entry_date',[$start_date,$end_date])->get();
					 $head1_pro['income_credit_amount'] = $pettycashexpenses1_pro[0]->credit_amount;
					$total_income_wise_pro = $head1_pro['income_credit_amount'] - $head1_pro['income_debit_amount'];
					if($total_income_wise_pro < 0) {
					$head1_pro['total_income'] = $total_income_wise_pro;
					} else {
					$head1_pro['total_income'] = $total_income_wise_pro;
					}
					
					
					array_push($details1_pro,$head1_pro);
					$total_debit_amounts1_pro += $head1_pro['income_debit_amount'];
					$total_credit_amounts1_pro += $head1_pro['income_credit_amount'];
					$total_income_pro += $head1_pro['total_income'];
				 }
				
				  $exp_type1_pro->income_det_pro=$details1_pro;
				$exp_type1_pro['income_total_debit']=$total_debit_amounts1_pro;
				$exp_type1_pro['income_total_credit']=$total_credit_amounts1_pro;
				$exp_type1_pro['income_total']=$total_income_pro;
            }
			
					
			
			
			$expense_pro=[];
			$income_pro=[];
			
			array_push($expense_pro,$exp_type_pro);
			array_push($income_pro,$exp_type1_pro);
			 array_push($overall_details_pro,$expense_pro);
			array_push($overall_details_pro,$income_pro);
			
			
		
			 $response['outstanding_non_prized'] = 0;
			$response['outstanding'] = 0;
			
			$response['grand_total_cash'] = 0;
			$response['receipts_cash'] = 0;
			
			$response['grand_total_bank'] = 0;
			$response['receipts_bank'] = 0;
			$response['grand_tot_overal_pending_non_prized'] = 0;
			$response['grand_tot_overal_pending'] = 0;

			
			$response['companyname'] = 0;
			$response['branchname'] = 0;
           
           
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $overall_details_pro;
			$response['data_pro'] = $overall_details_pro;
			
            return response()->json($response,200);
			 
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function debit_credit_view(Request $request) {
        try {
            $cond = $request->conditions; 
			
			$receipts = AccountExpense::where($cond)->orderBy('id','desc')->get();
            
            
         
			foreach($receipts as $receipt) {
			if($receipt->debit_account_head != 0){
			$receipt->debit_name  = isset($receipt->debit_account_head_det->name) ? ($receipt->debit_account_head_det->name) : [];	
			$receipt->credit_name  = '';		
			} else {
			$receipt->debit_name  = '';
			if($receipt->credit_account_head == 100){
			 $receipt_customer = Receipt::where('receipt_no',$receipt->voucher_no)->pluck('customer_id');
			
			 $cus_name = Customer::where('id',$receipt_customer[0])->pluck('name');
			 $cus_last_name = Customer::where('id',$receipt_customer[0])->pluck('last_name');
			 
			 $receipt->credit_name  = $cus_name[0] . ' ' .$cus_last_name[0];

			} else {
			$receipt->credit_name  = isset($receipt->account_head_det->name) ? ($receipt->account_head_det->name) : [];	
		    }
			}
			
			}

			

            $response['status'] = 'Success';
            $response['msg'] = '';
			
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuse'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_expenses(Request $request) {
        try {
            if(isset($request->voucher_no)) {
               $receipts = AccountExpense::where('voucher_no',$request->voucher_no)->get();
            
            
         
			foreach($receipts as $receipt) {
                $receipt->deleted_by = $request->deleted_by;
                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($receipt->save()) {
					$receipt->delete();
                   
				}
                    
                }
				 $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_petty_cash_exp_deleted');
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
            return response()->json($response, 401);
        }
    }
	
	public function profitLossReport(Request $request)
{
    $tenant_id = $request->tenant_id;
    $branch_id = $request->branch_id;
    $start_date = '2025-07-01';
    $end_date = '2025-07-17';

    // Fetch account head mapping
    $heads = DB::table('account_heads as ah')
        ->join('account_groups as ag', 'ah.group_id', '=', 'ag.id')
        ->whereIn('ag.under', ['Incomes', 'Expenses'])
        ->select('ah.id', 'ah.name', 'ag.group_name', 'ag.under')
        ->get()
        ->keyBy('id');

    // Fetch all vouchers in range
    $entries = DB::table('account_expenses')
        ->where('tenant_id', $tenant_id)
        ->where('branch_id', $branch_id)
        ->whereBetween('entry_date', [$start_date, $end_date])
        ->get();

    $result = [
        'Income' => [],
        'Expense' => [],
    ];

    foreach ($entries as $entry) {
        // Debit side
        if (isset($heads[$entry->debit_account_head])) {
            $h = $heads[$entry->debit_account_head];
            $type = $h->under;
            $result[$type][] = [
                'account_head_name' => $h->name,
                'group_name' => $h->group_name,
                'debit' => (float) $entry->debit_amount,
                'credit' => 0
            ];
        }

        // Credit side
        if (isset($heads[$entry->credit_account_head])) {
            $h = $heads[$entry->credit_account_head];
            $type = $h->under;
            $result[$type][] = [
                'account_head_name' => $h->name,
                'group_name' => $h->group_name,
                'debit' => 0,
                'credit' => (float) $entry->credit_amount
            ];
        }
    }

    // Sum total by head name
    foreach (['Income', 'Expense'] as $type) {
        $result[$type] = collect($result[$type])
            ->groupBy('account_head_name')
            ->map(function ($group) {
                return [
                    'group_name' => $group->first()['group_name'],
                    'account_head_name' => $group->first()['name'],
                    'debit' => $group->sum('debit'),
                    'credit' => $group->sum('credit'),
                ];
            })
            ->values()
            ->toArray();
    }

    $total_income = collect($result['Income'])->sum(fn($r) => $r['credit'] - $r['debit']);
    $total_expense = collect($result['Expense'])->sum(fn($r) => $r['debit'] - $r['credit']);
    $net_profit = $total_income - $total_expense;

    return response()->json([
        'status' => 'Success',
        'data' => $result,
        'total_income' => $total_income,
        'total_expense' => $total_expense,
        'net_result' => $net_profit >= 0 ? 'Profit' : 'Loss',
        'net_amount' => abs($net_profit)
    ]);
}


	 public function getBalanceSheet(Request $request)
    {
        try {
            $branch_id = 4;
            $tenant_id = 63;
            $start_date = '2025-07-01';
            $end_date = '2025-07-17';

            // === Get all debit entries with account head type ===
            $debits = DB::table('account_expenses as at')
                ->join('account_heads as ah', 'at.debit_account_head', '=', 'ah.id')
                ->join('account_groups as ag', 'ah.group_id', '=', 'ag.id')
                ->where('at.tenant_id', $tenant_id)
                ->where('at.branch_id', $branch_id)
                ->whereBetween('at.entry_date', [$start_date, $end_date])
                ->select(
                    'ah.name',
                    'ag.under',
                    DB::raw('SUM(at.debit_amount) as amount')
                )
                ->groupBy('ah.name', 'ag.under')
                ->get();

            // === Get all credit entries with account head type ===
            $credits = DB::table('account_expenses as at')
                ->join('account_heads as ah', 'at.credit_account_head', '=', 'ah.id')
                ->join('account_groups as ag', 'ah.group_id', '=', 'ag.id')
                ->where('at.tenant_id', $tenant_id)
                ->where('at.branch_id', $branch_id)
                ->whereBetween('at.entry_date', [$start_date, $end_date])
                ->select(
                    'ah.name',
                    'ag.under',
                    DB::raw('SUM(at.credit_amount) as amount')
                )
                ->groupBy('ah.name', 'ag.under')
                ->get();

            // === Merge and calculate net balances per account_head ===
            $merged = [];

            foreach ($debits as $row) {
                $key = $row->name . '_' . $row->under;
                $merged[$key] = [
                    'account_head_name' => $row->name,
                    'account_type' => $row->under,
                    'balance' => $row->amount
                ];
            }

            foreach ($credits as $row) {
                $key = $row->name . '_' . $row->under;
                if (isset($merged[$key])) {
                    $merged[$key]['balance'] -= $row->amount; // debit - credit
                } else {
                    $merged[$key] = [
                        'account_head_name' => $row->name,
                        'account_type' => $row->under,
                        'balance' => -$row->amount // only credit present
                    ];
                }
            }

            // === Group by account_type ===
            $assets = [];
            $liabilities = [];
            $equity = [];
            $total_assets = $total_liabilities = $total_equity = 0;

            foreach ($merged as $row) {
                $balance = $row['balance'];
                switch ($row['account_type']) {
                    case 'Asset':
                        $assets[] = $row;
                        $total_assets += $balance;
                        break;
                    case 'Liability':
                        $liabilities[] = $row;
                        $total_liabilities += $balance;
                        break;
                    case 'Equity':
                        $equity[] = $row;
                        $total_equity += $balance;
                        break;
                }
            }

            return response()->json([
                'status' => 'Success',
                'data' => [
                    'assets' => $assets,
                    'liabilities' => $liabilities,
                    'equity' => $equity
                ],
                'totals' => [
                    'total_assets' => $total_assets,
                    'total_liabilities' => $total_liabilities,
                    'total_equity' => $total_equity,
                    'total_liabilities_equity' => $total_liabilities + $total_equity
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }


 public function bookReport(Request $request)
{
    $bookType = $request->book_type;
    $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
    $toDate = $request->to_date ?? now()->toDateString();
    $branchId = $request->branch_id;

    if ($bookType === 'cash') {
        return $this->generateCashBook($fromDate, $toDate, $branchId);
    } elseif ($bookType === 'bank') {
        return $this->generateBankBook($fromDate, $toDate, $branchId);
    }

    return response()->json(['error' => 'Invalid book type'], 400);
}

public function generateCashBook($fromDate, $toDate, $branchId)
{
    $cashHeadId = 1; // Cash account_head ID

    // Get all account heads once
    $accountHeads = AccountHead::all()->pluck('name', 'id')->mapWithKeys(function ($name, $id) {
        return [(int) $id => $name];
    })->toArray();

    // Calculate Opening Balance
    $openingBalance = AccountExpense::where(function ($q) use ($cashHeadId) {
            $q->where('debit_account_head', $cashHeadId)
              ->orWhere('credit_account_head', $cashHeadId);
        })
        ->whereDate('entry_date', '<', $fromDate)
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->get()
        ->reduce(function ($carry, $item) use ($cashHeadId) {
            if ($item->debit_account_head == $cashHeadId) {
                return $carry + (float)$item->debit_amount;
            } else {
                return $carry - (float)$item->credit_amount;
            }
        }, 0);

    // Fetch Ledger Entries
    $entries = AccountExpense::where(function ($q) use ($cashHeadId) {
            $q->where('debit_account_head', $cashHeadId)
              ->orWhere('credit_account_head', $cashHeadId);
        })
        ->whereBetween('entry_date', [$fromDate, $toDate])
        ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
        ->orderBy('entry_date')
        ->get();

    $ledger = [];
    $balance = $openingBalance;

    // Add Opening Balance
    $ledger[] = [
        'date' => $fromDate,
        'particulars' => 'Opening Balance',
        'debit' => $openingBalance >= 0 ? number_format($openingBalance, 2) : 0,
        'credit' => $openingBalance < 0 ? number_format(abs($openingBalance), 2) : 0,
        'remarks' => '',
        'balance' => number_format($balance, 2),
    ];

    // Loop through entries
    foreach ($entries as $entry) {
    $isDebit = $entry->debit_account_head == $cashHeadId;
    $amount = $isDebit ? (float)$entry->debit_amount : (float)$entry->credit_amount;
    $otherHeadId = $isDebit ? $entry->credit_account_head : $entry->debit_account_head;
    $particulars = $accountHeads[(int)$otherHeadId] ?? 'Unknown';
 
    \Log::info('CashBook Entry Debug', [
        'entry_id' => $entry->id,
        'debit_account_head' => $entry->debit_account_head,
        'credit_account_head' => $entry->credit_account_head,
        'isDebit' => $isDebit,
        'amount' => $amount,
        'otherHeadId' => $otherHeadId,
        'resolved_particulars' => $particulars,
        'all_account_heads' => $accountHeads
    ]);
        // Use specific remarks
        $remarks = $isDebit ? ($entry->debit_remarks ?: $entry->remarks) : ($entry->credit_remarks ?: $entry->remarks);

        // Update balance
        $balance += $isDebit ? $amount : -$amount;

        $ledger[] = [
            'date' => date('Y-m-d', strtotime($entry->entry_date)),
            'particulars' => $particulars,
            'debit' => $isDebit ? number_format($amount, 2) : 0,
            'credit' => !$isDebit ? number_format($amount, 2) : 0,
            'remarks' => $remarks ?? '',
            'balance' => number_format($balance, 2),
        ];
    }

    // Return response
    return response()->json([
        'status' => 'Success',
        'data' => [
            'entries' => $ledger,
            'opening_balance' => number_format($openingBalance, 2),
            'total_debit' => number_format(collect($ledger)->sum(fn($e) => (float)$e['debit']), 2),
            'total_credit' => number_format(collect($ledger)->sum(fn($e) => (float)$e['credit']), 2),
            'closing_balance' => number_format($balance, 2),
        ]
    ]);
}


private function generateBankBook($fromDate, $toDate, $branchId)
{
    $bankGroup = AccountGroup::where('name', 'Bank')->first();
    if (!$bankGroup) {
        return response()->json(['status' => 'Error', 'msg' => 'Bank group not found'], 404);
    }

    $bankHeads = AccountHead::where('account_group_id', $bankGroup->id)->get();
    $accountHeads = AccountHead::pluck('name', 'id')->toArray();

    $allEntries = [];
    $totalDebit = 0;
    $totalCredit = 0;
    $closingBalance = 0;

    foreach ($bankHeads as $bankHead) {
        $opening = AccountExpense::where(function ($q) use ($bankHead) {
                $q->where('debit_account_head', $bankHead->id)
                  ->orWhere('credit_account_head', $bankHead->id);
            })
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('entry_date', '<', $fromDate)
            ->get()
            ->reduce(function ($carry, $entry) use ($bankHead) {
                $isDebit = $entry->debit_account_head == $bankHead->id;
                return $carry + ($isDebit ? $entry->amount : -$entry->amount);
            }, 0);

        $entries = AccountExpense::where(function ($q) use ($bankHead) {
                $q->where('debit_account_head', $bankHead->id)
                  ->orWhere('credit_account_head', $bankHead->id);
            })
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('entry_date', [$fromDate, $toDate])
            ->orderBy('entry_date')
            ->get();

        $balance = $opening;

        $allEntries[] = [
            'date' => $fromDate,
            'particulars' => 'Opening Balance',
            'debit' => $opening >= 0 ? $opening : 0,
            'credit' => $opening < 0 ? abs($opening) : 0,
            'balance' => $balance,
            'account' => $bankHead->name,
        ];

        foreach ($entries as $entry) {
            $isDebit = $entry->debit_account_head == $bankHead->id;
            $amount = $entry->amount;
            $balance += $isDebit ? $amount : -$amount;

            $particularsId = $isDebit ? $entry->credit_account_head : $entry->debit_account_head;
            $particulars = $accountHeads[$particularsId] ?? 'Unknown';

            $allEntries[] = [
                'date' => $entry->entry_date,
                'particulars' => $particulars,
                'debit' => $isDebit ? $amount : 0,
                'credit' => !$isDebit ? $amount : 0,
                'balance' => $balance,
                'account' => $bankHead->name,
            ];
        }

        $totalDebit += collect($entries)->where('debit_account_head', $bankHead->id)->sum('amount');
        $totalCredit += collect($entries)->where('credit_account_head', $bankHead->id)->sum('amount');
        $closingBalance += $balance;
    }

    return response()->json([
        'status' => 'Success',
        'data' => [
            'entries' => $allEntries,
            'opening_balance' => null, // handled per bank account in UI
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'closing_balance' => $closingBalance
        ]
    ]);
}


}
