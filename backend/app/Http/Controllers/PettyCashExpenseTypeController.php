<?php
namespace App\Http\Controllers;

use App\Models\PettyCashExpenseType;
use App\Models\AccountGroup;
use App\Models\AccountHead;
use App\Models\AccountExpense;
use Illuminate\Http\Request;
use Validator;

class PettyCashExpenseTypeController extends Controller
{
    public function store_expense_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'expenses_type' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $exp_type = new PettyCashExpenseType();
                $exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $exp_type->expenses_type = isset($request->expenses_type) ? ($request->expenses_type) : '0';
                $exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $exp_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $exp_type->status = isset($request->status) ? ($request->status) : 1;
                if($exp_type->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_petty_cash_exp_type_added');
                    $response['data'] = $exp_type;
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
                $response['statuse'] = $e;
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

    public function update_expense_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'expenses_type' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $exp_type = PettyCashExpenseType::find($request->id); 
                    $exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $exp_type->expenses_type = isset($request->expenses_type) ? ($request->expenses_type) : '';
                    $exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $exp_type->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($exp_type->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_petty_cash_exp_type_updated');
                        $response['data'] = $exp_type;
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

    public function get_expense_types(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_expense_types = PettyCashExpenseType::where($cond)->orderBy('id','desc')->get();
            foreach($list_expense_types as $exp_type) {
                $exp_type->tenant_info = isset($exp_type->tenant_det) ? ($exp_type->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_expense_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_expense_types(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $exp_types = PettyCashExpenseType::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $exp_types = PettyCashExpenseType::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($exp_types as $exp_type) {
                $exp_type->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $exp_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_expense_type(Request $request) {
        try {
            if(isset($request->id)) {
                $exp_type = PettyCashExpenseType::find($request->id); 
                $exp_type->deleted_by = $request->deleted_by;
                $exp_type->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($exp_type->save()) {
                    $exp_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_petty_cash_exp_type_deleted');
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
                $exp_type = PettyCashExpenseType::find($request->id); 
                $exp_type->status = ($exp_type->status) ? 0 : 1;
                if($exp_type->save()) {
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
	
    public function store_accounting_group(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'group_name' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $exp_type = new AccountGroup();
                $exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $exp_type->group_name = isset($request->group_name) ? ($request->group_name) : '';
				$exp_type->under = isset($request->under) ? ($request->under) : '';
				if($exp_type->under == 'Incomes' ||  $exp_type->under == 'Expenses' || $exp_type->under == 'Liabilities' ||  $exp_type->under == 'Asset' ||  $exp_type->under == 'Cash' ||  $exp_type->under == 'Bank'){
				$exp_typeunder = AccountGroup::where('group_name',$request->under)->pluck('main_under');
				$exp_type->main_under = '';
                if(count($exp_typeunder)>0){
				    $exp_type->main_under = $exp_typeunder[0];
                }
				}
				else{
					$exp_typeunder = AccountGroup::where('group_name',$request->under)->pluck('main_under');
					$exp_type->main_under = $exp_typeunder[0];
					$exp_type->under = isset($request->under) ? ($request->under) : '';
				}
                $exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $exp_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $exp_type->status = isset($request->status) ? ($request->status) : 1;
				$exp_type->main_status = isset($request->main_status) ? ($request->main_status) : 0;

                if($exp_type->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_accounts_group_added');
                    $response['data'] = $exp_type;
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
                $response['statuse'] = $e;
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }
	
	public function update_accounts_group(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'group_name' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $exp_type = AccountGroup::find($request->id); 
                    $exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $exp_type->group_name = isset($request->group_name) ? ($request->group_name) : '';
					$exp_type->under = isset($request->under) ? ($request->under) : '';
					if($exp_type->under == 'Incomes' ||  $exp_type->under == 'Expenses' | $exp_type->under == 'Liabilities' ||  $exp_type->under == 'Asset' ||  $exp_type->under == 'Cash'){
					$exp_typeunder = AccountGroup::where('under',$request->under)->pluck('main_under');
					
					
					$exp_type->main_under = $exp_typeunder[0];
					}
					else{
						$exp_typeunder = AccountGroup::where('group_name',$request->under)->pluck('main_under');
					$exp_type->main_under = $exp_typeunder[0];
					$exp_type->under = isset($request->under) ? ($request->under) : '';
					}
                    $exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $exp_type->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($exp_type->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_accounts_group_updated');
                        $response['data'] = $exp_type;
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
	
	public function get_accounts_group(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_expense_types = AccountGroup::where($cond)->orderBy('id','desc')->get();
            foreach($list_expense_types as $exp_type) {
                $exp_type->tenant_info = isset($exp_type->tenant_det) ? ($exp_type->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_expense_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_accounts_group(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $exp_types = AccountGroup::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $exp_types = AccountGroup::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($exp_types as $exp_type) {
                $exp_type->sno = $i;
				
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $exp_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_accounts_group(Request $request) {
        try {
            if(isset($request->id)) {
                $exp_type = AccountGroup::find($request->id); 
                $exp_type->deleted_by = $request->deleted_by;
                $exp_type->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($exp_type->save()) {
                    $exp_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_accounts_group_deleted');
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
	
	
	public function store_accounting_head(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'group_id' => 'required',
            'name' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
			
				//$executionStartTime = microtime(true);      
                $exp_type =  AccountHead::where(array('account_number'=>$request->account_number,'tenant_id'=>$request->tenant_id))->get();       
                if(count($exp_type)>0)
                {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.account_no_already_exist');
                    return response()->json($response, 200);
                } 
                
                else
                {
					$exp_type = new AccountHead();
					$exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$exp_type->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$exp_type->group_id = isset($request->group_id) ? ($request->group_id) : '';
					$exp_type->name = isset($request->name) ? ($request->name) : '';
					$exp_type->amount = isset($request->amount) ? ($request->amount) : 0;
					$exp_type->account_number = isset($request->account_number) ? ($request->account_number) : '0';
					$exp_type->collection_type = isset($request->collection_type) ? ($request->collection_type) : '';
					$exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
					$exp_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
					$exp_type->status = isset($request->status) ? ($request->status) : 1;
					
					
					
                            // print_r($exp_type);exit;
					if($exp_type->save()) {
						$exp_type1 = AccountGroup::find($request->group_id); 
						$exp_type1->main_status = isset($request->main_status) ? ($request->main_status) : 1;
						$under =   $exp_type1->under;
						if($exp_type1->save()) {
							if(($under == 'Expenses') || ($under == 'Incomes')){
								
								if($exp_type1->save()) {
									$response['status'] = 'Success';
									$response['msg'] = \Lang::get('api.success_accounts_group_added');
									$response['data'] = $exp_type;
									return response()->json($response,200);
								}
								else {
									$response['status']="Error";
									$response['msg']=\Lang::get('api.global_error');
									return response()->json($response,401);
								}
								
							}
							else{
								
								$exp_type2 = new AccountExpense();
								$exp_type2->account_number = isset($request->account_number) ? ($request->account_number) : '0';
								$exp_type2->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
								$ldate = date('Y-m-d');
								$exp_type2->entry_date = $ldate;
								$exp_type2->branch_id = isset($request->branch_id) ? ($request->branch_id) : '0';
								$exp_type2->opening_balance = 1;
								if($exp_type->collection_type == 'Credit'){
									$exp_type2->credit_amount = isset($request->amount) ? ($request->amount) : '0';
									$exp_type2->debit_amount = 0;
								}
								else{
									$exp_type2->credit_amount = 0;
									$exp_type2->debit_amount = isset($request->amount) ? ($request->amount) : '0';
								}
                                // print_r($exp_type2);exit;
								if($exp_type2->save()) {
									$response['status'] = 'Success';
									$response['msg'] = \Lang::get('api.success_accounts_group_added');
									$response['data'] = $exp_type;
									return response()->json($response,200);
								}
								else {
									$response['status']="Error";
									$response['msg']=\Lang::get('api.global_error');
									return response()->json($response,401);
								}
							}
						}
						
					}
				}
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }
	
	public function update_accounts_head(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $exp_type = AccountHead::find($request->id); 
                    $exp_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$exp_type->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $exp_type->group_id = isset($request->group_id) ? ($request->group_id) : '';
                    $exp_type->name = isset($request->name) ? ($request->name) : '';
					$exp_type->amount = isset($request->amount) ? ($request->amount) : 0;
					$exp_type->account_number = isset($request->account_number) ? ($request->account_number) : '';
					$exp_type->collection_type = isset($request->collection_type) ? ($request->collection_type) : '';
                    $exp_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $exp_type->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($exp_type->save()) {
						$success_count=0;
						$account_expense_dets = AccountExpense::where('account_number','=',$request->account_number)->get();
						foreach($account_expense_dets as $acc_expense_dets)
						{
							if(isset($acc_expense_dets->id) && !empty($acc_expense_dets->id))
							{
								$exp_type2=AccountExpense::find($acc_expense_dets->id);
								$exp_type2->voucher_no =  0;
							$exp_type2->account_number = isset($request->account_number) ? ($request->account_number) : '';
							$exp_type2->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
							$ldate = date('Y-m-d');
							$exp_type2->entry_date = $ldate;
							$exp_type2->branch_id = isset($request->branch_id) ? ($request->branch_id) : '';
							$exp_type2->opening_balance = 1;
							if($exp_type->collection_type == 'Credit'){
								$exp_type2->credit_amount = isset($request->amount) ? ($request->amount) : '';
								$exp_type2->debit_amount = 0;
								$exp_type2->credit_account_head = $request->id;
								$exp_type2->debit_account_head = 0;
							}
							else{
								$exp_type2->credit_amount = 0;
								$exp_type2->debit_amount = isset($request->amount) ? ($request->amount) : '';
								$exp_type2->credit_account_head = 0;
								$exp_type2->debit_account_head = $request->id;
							}
							$exp_type2->save();
							$success_count++;
							}
						}
						
						
						
						 
							if($success_count >0) {
							 
							$response['status'] = "Success";
							$response['msg'] = \Lang::get('api.success_accounts_group_updated');
							$response['data'] = $exp_type;
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
	
	public function get_accounts_head(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_expense_types = AccountHead::where($cond)->orderBy('id','desc')->get();
            foreach($list_expense_types as $exp_type) {
                $exp_type->tenant_info = isset($exp_type->tenant_det) ? ($exp_type->tenant_det) : [];
				$exp_type->branch_info = isset($exp_type->branch_det) ? ($exp_type->branch_det) : [];
				$exp_type->group_name = isset($exp_type->accounts_group_det) ? ($exp_type->accounts_group_det->group_name) : [];
				$exp_type->under = isset($exp_type->accounts_group_det->under) ? ($exp_type->accounts_group_det->under) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_expense_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_accounts_head(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $exp_types = AccountHead::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $exp_types = AccountHead::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($exp_types as $exp_type) {
                $exp_type->sno = $i;
				$exp_type->group_name = isset($exp_type->accounts_group_det) ? ($exp_type->accounts_group_det->group_name) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $exp_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_accounts_head(Request $request) {
        try {
            if(isset($request->id)) {
                $exp_type = AccountHead::find($request->id); 
                $exp_type->deleted_by = $request->deleted_by;
                $exp_type->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($exp_type->save()) {
                    $exp_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_accounts_group_deleted');
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
	
	public function get_voucher_no(Request $request) {
        try {
            $cond = $request->conditions; 
			
			if($request->conditions['tenant_id']==50){
			$cnt_no = AccountExpense::where('account_type','!=',0)->count('id');
			
			if($cnt_no==0){
			$voucher_no = "SAC-1";	
			} else {
			$max_id = AccountExpense::where('account_type','>',0)->max('id');
		
			$max_voucher_no = AccountExpense::where('id',$max_id)->pluck('voucher_no');
			
			$voucher_no = $max_voucher_no[0];	
			
			$vou_no = explode('-',$voucher_no);
			
			$v_no = $vou_no[1]+1;
			$voucher_no = "SAC-" . $v_no;	
			}
				
			} else {
			$max_id = AccountExpense::max('id');
			$max_voucher_no = AccountExpense::where('id',$max_id)->pluck('voucher_no');
			$cnt_no = AccountExpense::count('id');
			if($cnt_no==0){
			$voucher_no = "DIAV-1";	
			} else {
			$voucher_no = $max_voucher_no[0];	
			
			$vou_no = explode('-',$voucher_no);
			
			$v_no = $vou_no[1]+1;
			$voucher_no = "DIAV-" . $v_no;	
			}
		}
			

            $response['status'] = 'Success';
            $response['msg'] = '';
			
            $response['data'] = $voucher_no;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuse'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	
}
