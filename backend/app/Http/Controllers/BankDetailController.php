<?php
namespace App\Http\Controllers;

use App\Models\BankDetail;
use App\Models\AccountGroup;
use App\Models\AccountHead;
use Illuminate\Http\Request;
use Validator;
use Config;
//use Illuminate\Support\Facades\Input;

class BankDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store_bank(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 
                //$executionStartTime = microtime(true);      
                $bank =  BankDetail::where(array('account_number'=>$request->account_number,'branch_id'=>$request->branch_id))->get();       
                if(count($bank)>0)
                {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.account_no_already_exist');
                    return response()->json($response, 200);
                } 
                $ifsc_code =  BankDetail::where(array('ifsc_code'=>$request->ifsc_code,'branch_id'=>$request->branch_id))->get();       
                if(count($ifsc_code)>0)
                {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.ifsc_code_already_exist');
                    return response()->json($response, 200);
                } 
                else
                {
                    $bank = new BankDetail();
                    $bank->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $bank->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;                
                    $bank->bank_name = isset($request->bank_name) ? ($request->bank_name) : '0';
                    $bank->account_number = isset($request->account_number) ? ($request->account_number) : '0';
                    $bank->ifsc_code = isset($request->ifsc_code) ? ($request->ifsc_code) : '0';                
                    $bank->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '0';
                    $bank->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                    $bank->status = isset($request->status) ? ($request->status) : 1;
                    if($bank->save()) {
						
						
                        //$executionEndTime = microtime(true);
                        //$seconds = $executionEndTime - $executionStartTime;
                        //$response["exe_time"] = $seconds;
                        $bank_code = Config::get('constants.auto_codes.bank').sprintf('%05d', $bank->id);
                        $bank->bank_code = $bank_code;
                        if($bank->save()){ 
							$exp_type = new AccountHead();
							$exp_type->tenant_id = isset($bank->tenant_id) ? ($bank->tenant_id) : 0;
							
							$exp_type->branch_id = isset($bank->branch_id) ? ($bank->branch_id) : 0;
							
							$bank_name = isset($bank->bank_name) ? ($bank->bank_name) : '0';
							
							$bank_branch_name = isset($bank->bank_branch_name) ? ($bank->bank_branch_name) : '0';
							
							$exp_type->name = $bank_name .'-'. $bank_branch_name; 
							
							$exp_type->group_id = 3;
							
							$exp_type->account_number = 0;
							
							$exp_type->collection_type = 0;
							
							$exp_type->bank_account_number = isset($request->account_number) ? ($request->account_number) : '0';
							
							$exp_type->remarks = isset($bank->remarks) ? ($bank->remarks) : '0';
							
							$exp_type->created_by = isset($bank->created_by) ? ($bank->created_by) : 0;
							 
							$exp_type->status = isset($bank->status) ? ($bank->status) : 1;
							
							if($exp_type->save())
							{
								
									$response['status'] = 'Success';
									$response['msg'] = \Lang::get('api.success_bank_added');
									$response['data'] = $bank;
									return response()->json($response,200);                   
								}
								else {
									$response['status']="Error";
									//$response['msg']=\Lang::get('api.global_error');
									return response()->json($response,401);
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

    public function update_bank(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $bank =  BankDetail::where(array('account_number'=>$request->account_number,'branch_id'=>$request->branch_id))->whereNotIn('id', [$request->id])->get();       
                    if(count($bank)>0)
                    {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.account_no_already_exist');
                        return response()->json($response, 200);
                    } 
                    $bank = BankDetail::find($request->id); 
                    $bank->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $bank->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $bank->bank_name = isset($request->bank_name) ? ($request->bank_name) : '0';
                    $bank->account_number = isset($request->account_number) ? ($request->account_number) : '0';
                    $bank->ifsc_code = isset($request->ifsc_code) ? ($request->ifsc_code) : '0';
                    $bank->bank_code = isset($request->bank_code) ? ($request->bank_code) : '0';
                    $bank->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '0';
                    $bank->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($bank->save()) {
                       //$executionEndTime = microtime(true);
                        //$seconds = $executionEndTime - $executionStartTime;
                        //$response["exe_time"] = $seconds;
                        $bank_code = Config::get('constants.auto_codes.bank').sprintf('%05d', $bank->id);
                        $bank->bank_code = $bank_code;
                        if($bank->save()){ 
						
							$success_count=0;
							$account_expense_dets = AccountHead::where('bank_account_number','=',$request->account_number)->get();
							foreach($account_expense_dets as $acc_expense_dets)
							{
								if(isset($acc_expense_dets->id) && !empty($acc_expense_dets->id))
								{
									$exp_type2=AccountHead::find($acc_expense_dets->id);
								
								
									$exp_type2->tenant_id = isset($bank->tenant_id) ? ($bank->tenant_id) : 0;
									
									$bank_name = isset($bank->bank_name) ? ($bank->bank_name) : '';
									
									$bank_branch_name = isset($bank->bank_branch_name) ? ($bank->bank_branch_name) : '';
									
									$exp_type2->name = $bank_name .'-'. $bank_branch_name;
									
									$exp_type2->group_id = 3;
									
                                    $exp_type2->collection_type = 0;

                                    $exp_type2->bank_account_number = isset($request->account_number) ? ($request->account_number) : '';
									
									$exp_type2->remarks = isset($bank->remarks) ? ($bank->remarks) : '';
									
									$exp_type2->created_by = isset($bank->created_by) ? ($bank->created_by) : 0;
									 
									$exp_type2->status = isset($bank->status) ? ($bank->status) : 1;
									
									if($exp_type2->save())
									{
										
											$response['status'] = 'Success';
											$response['msg'] = \Lang::get('api.success_bank_update');
											$response['data'] = $bank;
											return response()->json($response,200);                   
									}
									else {
										$response['status']="Error";
										//$response['msg']=\Lang::get('api.global_error');
										return response()->json($response,401);
									}
							
								}
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

    public function get_banks(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $banks = BankDetail::where($cond)->orderBy('id','desc')->get();
            if($banks) {
                $i=1;
                foreach($banks as $bank) {
                    $bank->sno = $i;
                    $bank->tenant_info = isset($bank->tenant_det) ? ($bank->tenant_det) : [];
                    $bank->branch_info = isset($bank->branch_det) ? ($bank->branch_det) : [];
                    $i++;
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $banks;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_banks(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $banks = BankDetail::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $banks = BankDetail::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($banks as $bank) {
                $bank->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $banks;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_bank(Request $request) {
        try {
            if(isset($request->id)) {
                $bank = BankDetail::find($request->id); 
                $bank->deleted_by = $request->deleted_by;
                $bank->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($bank->save()) {
                    $bank->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_bank_deleted');
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
                $bank = BankDetail::find($request->id); 
                $bank->status = ($bank->status) ? 0 : 1;
                if($bank->save()) {
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
    
}
