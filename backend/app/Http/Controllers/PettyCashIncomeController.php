<?php
namespace App\Http\Controllers;

use App\Models\PettyCashIncome;
use App\Models\PettyCashExpense;
use Illuminate\Http\Request;
use Validator;

class PettyCashIncomeController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_petty_cash_income(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $pettycashincome=new PettyCashIncome(); 
                $pettycashincome->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $pettycashincome->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                if(isset($request->received_date)) {
                    $s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day'])); 
                    $pettycashincome->received_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                        // $pettycashincome->received_date = '0000-00-00';
                } 
                $pettycashincome->amount_received = isset($request->amount_received) ? ($request->amount_received) : 0 ;
                $pettycashincome->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                $pettycashincome->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                $pettycashincome->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
				if(isset($request->cheque_date)) {
					$f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
					$pettycashincome->cheque_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					// $pettycashincome->cheque_date  = '0000-00-00';
				}
                $pettycashincome->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                $pettycashincome->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
				if(isset($request->transaction_date)) {
					$f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
					$pettycashincome->transaction_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					// $pettycashincome->transaction_date  = '0000-00-00';
				}
                $pettycashincome->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
                $pettycashincome->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashincome->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $pettycashincome->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2){
							$pettycashincome->status = isset($request->status) ? ($request->status) : 2;
						}
                if($pettycashincome->amount_received >0)
                {
                    if($pettycashincome->save()){
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_petty_cash_income_added');
                        $response['data']=$pettycashincome;
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
                    $response['msg'] = "Received Amount Invalid";
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
    
    public function update_petty_cash_income(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'branch_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $pettycashincome = PettyCashIncome::find($request->id); 
                    $pettycashincome->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $pettycashincome->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    if(isset($request->received_date)) {
                        $s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day'])); 
                        $pettycashincome->received_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $pettycashincome->amount_received = isset($request->amount_received) ? ($request->amount_received) : 0 ;
                    $pettycashincome->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;   
                    $pettycashincome->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                    $pettycashincome->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
                    if(isset($request->cheque_date)) {
                        $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $pettycashincome->cheque_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $pettycashincome->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                    $pettycashincome->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
                    if(isset($request->transaction_date)) {
                        $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $pettycashincome->transaction_date = date('Y-m-d',strtotime($s_date)); 
                    }
					if($request->payment_type_id == 2){
							$pettycashincome->status = isset($request->status) ? ($request->status) : 2;
						}
                    $pettycashincome->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;  
                    $pettycashincome->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $pettycashincome->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($pettycashincome->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_petty_cash_income_updated');
                        $response['data'] =$pettycashincome;
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

    public function get_petty_cash_incomes(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $pettycashincomes = PettyCashIncome::where($cond)->orderBy('id','desc')->get();
            foreach($pettycashincomes as $pettycashincome) {
                $pettycashincome->tenant_info = isset($pettycashincome->tenant_det) ? ($pettycashincome->tenant_det) : [];
                $pettycashincome->branch_info = isset($pettycashincome->branch_det) ? ($pettycashincome->branch_det) : [] ;
                $pettycashincome->payment_type_info = isset($pettycashincome->payment_type_det) ? ($pettycashincome->payment_type_det) : [] ;
				$pettycashincome->bank_info = isset($pettycashincome->bank_det) ? ($pettycashincome->bank_det) : [];
                $pettycashincome->received_date = ($pettycashincome->received_date!=0) ? date('d/m/Y',strtotime($pettycashincome->received_date)) : '';
				$pettycashincome->cheque_date = ($pettycashincome->cheque_date!=0) ? date('d/m/Y',strtotime($pettycashincome->cheque_date)) : '';
				$pettycashincome->transaction_date = ($pettycashincome->transaction_date!=0) ? date('d/m/Y',strtotime($pettycashincome->transaction_date)) : '';
				
			$incomes=PettyCashIncome::where('branch_id',$pettycashincome->branch_id)->sum('amount_received');
			
            $balance_amount = 0;
                               
                $expenses=PettyCashExpense::selectRaw('sum(expenses_amount) as total_expense')->where('branch_id',$pettycashincome->branch_id)->get();
                $balance_amount = $incomes - $expenses[0]->total_expense;
            
			$pettycashincome->available_cash = $balance_amount;
            }
			
			
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $pettycashincomes;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_petty_cash_incomes(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $pettycashincomes = PettyCashIncome::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $pettycashincomes = PettyCashIncome::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($pettycashincomes as $pettycashincome) {
                $pettycashincome->sno = $i;
                $pettycashincome->tenant_info = isset($pettycashincome->tenant_det) ? ($pettycashincome->tenant_det) : [];
                $pettycashincome->branch_info = isset($pettycashincome->branch_det) ? ($pettycashincome->branch_det) : [];
                $pettycashincome->payment_type_info = isset($pettycashincome->payment_type_det) ? ($pettycashincome->payment_type_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $pettycashincomes;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_petty_cash_income(Request $request) {
        try {
            if(isset($request->id)) {
                $pettycashincome = PettyCashIncome::find($request->id); 
                $pettycashincome->deleted_by = $request->deleted_by;
                $pettycashincome->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($pettycashincome->save()) {
                    $pettycashincome->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_petty_cash_income_deleted');
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

    public function get_available_amounts(Request $request)
    {
        try
        { 
            $cond = $request->conditions;
            $incomes=PettyCashIncome::selectRaw('sum(amount_received) as total_income')->where($cond)->get();
            $balance_amount = 0;
            if(count($incomes)>0)
            {                    
                $expenses=PettyCashExpense::selectRaw('sum(expenses_amount) as total_expense')->where($cond)->get();
                $balance_amount = $incomes[0]->total_income-$expenses[0]->total_expense;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $data['balance_amount']= $balance_amount;
            $response['data'] = $data;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function update_status(Request $request) {
        try {
            if(isset($request->id)) {
                $pettycashincome = PettyCashIncome::find($request->id); 
                $pettycashincome->status = ($pettycashincome->status) ? 0 : 1;
                if($pettycashincome->save()) {
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
	
	public function cheque_return_petty_cash_income(Request $request)
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
                $pettycashincome = PettyCashIncome::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $pettycashincome->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $pettycashincome->cheque_clear_return_date = '0000-00-00';
                } 
                $pettycashincome->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashincome->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $pettycashincome->status = isset($request->status) ? ($request->status) : 3;
                if($pettycashincome->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $pettycashincome;
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
	
	public function cheque_cleared_petty_cash_income(Request $request)
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
                $pettycashincome = PettyCashIncome::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $pettycashincome->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $pettycashincome->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $pettycashincome->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $pettycashincome->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $pettycashincome->status = isset($request->status) ? ($request->status) : 1;
                if($pettycashincome->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $pettycashincome;
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
}
