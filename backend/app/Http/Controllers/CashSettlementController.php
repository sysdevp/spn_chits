<?php
namespace App\Http\Controllers;

use App\Models\CashSettlement;
use Illuminate\Http\Request;
use Validator; 

class CashSettlementController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_cash_settlement(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_id' => 'required',
            'date' => 'required',
            'total_amount' => 'required',
            'received_amount' => 'required',
            'collection_employee' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 
                $cashsettlement = new CashSettlement();
                $cashsettlement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0 ;
                $cashsettlement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0 ;
                $cashsettlement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0 ;
                $cashsettlement->date = isset($request->date) ? ($request->date) : '0000-00-00';
                $cashsettlement->total_amount = isset($request->total_amount) ? ($request->total_amount) : 0 ;
                $cashsettlement->amt_1 = isset($request->amt_1) ? ($request->amt_1) : 0 ;
                $cashsettlement->amt_2 = isset($request->amt_2) ? ($request->amt_2) : 0 ;
                $cashsettlement->amt_5 = isset($request->amt_5) ? ($request->amt_5) : 0 ;
                $cashsettlement->amt_10 = isset($request->amt_10) ? ($request->amt_10) : 0 ;
                $cashsettlement->amt_20 = isset($request->amt_20) ? ($request->amt_20) : 0 ;
                $cashsettlement->amt_50 = isset($request->amt_50) ? ($request->amt_50) : 0 ;
                $cashsettlement->amt_100 = isset($request->amt_100) ? ($request->amt_100) : 0 ;
                $cashsettlement->amt_200 = isset($request->amt_200) ? ($request->amt_200) : 0 ;
                $cashsettlement->amt_500 = isset($request->amt_500) ? ($request->amt_500) : 0 ;
                $cashsettlement->amt_1000 = isset($request->amt_1000) ? ($request->amt_1000) : 0 ;
                $cashsettlement->amt_2000 = isset($request->amt_2000) ? ($request->amt_2000) : 0 ;
                $cashsettlement->received_amount = isset($request->received_amount) ? ($request->received_amount) : 0 ;
                $cashsettlement->collection_employee = isset($request->collection_employee) ? ($request->collection_employee) : 0 ;
                $cashsettlement->settlement_mode = 'ERP';
                $cashsettlement->remarks = isset($request->remarks) ? ($request->remarks) : '0' ;
                $cashsettlement->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $cashsettlement->status = isset($request->status) ? ($request->status) : 1;
                if($cashsettlement->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cash_settlement_added');
                    $response['data'] = $cashsettlement;
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

    public function update_cash_settlement(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_id' => 'required',
            'date' => 'required',
            'total_amount' => 'required',
            'received_amount' => 'required',
            'collection_employee' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $cashsettlement = CashSettlement::find($request->id); 
                    $cashsettlement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0 ;
                    $cashsettlement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0 ;
                    $cashsettlement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0 ;
                    $cashsettlement->date = isset($request->date) ? ($request->date) : '0000-00-00';
                    $cashsettlement->total_amount = isset($request->total_amount) ? ($request->total_amount) : 0 ;
                    $cashsettlement->amt_1 = isset($request->amt_1) ? ($request->amt_1) : 0 ;
                    $cashsettlement->amt_2 = isset($request->amt_2) ? ($request->amt_2) : 0 ;
                    $cashsettlement->amt_5 = isset($request->amt_5) ? ($request->amt_5) : 0 ;
                    $cashsettlement->amt_10 = isset($request->amt_10) ? ($request->amt_10) : 0 ;
                    $cashsettlement->amt_20 = isset($request->amt_20) ? ($request->amt_20) : 0 ;
                    $cashsettlement->amt_50 = isset($request->amt_50) ? ($request->amt_50) : 0 ;
                    $cashsettlement->amt_100 = isset($request->amt_100) ? ($request->amt_100) : 0 ;
                    $cashsettlement->amt_200 = isset($request->amt_200) ? ($request->amt_200) : 0 ;
                    $cashsettlement->amt_500 = isset($request->amt_500) ? ($request->amt_500) : 0 ;
                    $cashsettlement->amt_1000 = isset($request->amt_1000) ? ($request->amt_1000) : 0 ;
                    $cashsettlement->amt_2000 = isset($request->amt_2000) ? ($request->amt_2000) : 0 ;
                    $cashsettlement->received_amount = isset($request->received_amount) ? ($request->received_amount) : 0 ;
                    $cashsettlement->collection_employee = isset($request->collection_employee) ? ($request->collection_employee) : 0 ;
                    $cashsettlement->settlement_mode = 'ERP';
                    $cashsettlement->remarks = isset($request->remarks) ? ($request->remarks) : '0' ;
                    $cashsettlement->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0; 
                    if($cashsettlement->save()) { 
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_cash_settlement_updated');
                        $response['data'] = $cashsettlement;
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

    public function get_cash_settlements(Request $request) {
        try {
            //id,state_id
            $cond = $request->conditions; 
            $cashsettlements = CashSettlement::where($cond)->orderBy('id','desc')->get();
            if($cashsettlements) {
                foreach($cashsettlements as $cashsettlement) {
                    $cashsettlement->tenant_info = isset($cashsettlement->tenant_det) ? ($cashsettlement->tenant_det) : [];
                    $cashsettlement->branch_info = isset($cashsettlement->branch_det) ? ($cashsettlement->branch_det) : [];
                    $cashsettlement->employee_info = isset($cashsettlement->employee_det) ? ($cashsettlement->employee_det) : [];
                    $cashsettlement->collection_employee_info = isset($cashsettlement->collection_employee_det) ? ($cashsettlement->collection_employee_det) : [];
                }
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $cashsettlements;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_cash_settlements(Request $request) {
        try { 
            $cashsettlements = CashSettlement::orderBy('id','desc')->get();
            $i=1;
            foreach($cashsettlements as $cashsettlement) {
                $cashsettlement->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $cashsettlements;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_cash_settlement(Request $request) {
        try {
            if(isset($request->id)) {
                $cashsettlement = CashSettlement::find($request->id); 
                $cashsettlement->deleted_by = $request->deleted_by;
                $cashsettlement->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($cashsettlement->save()) {
                    $cashsettlement->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cash_settlement_deleted');
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

    /**
     * One-time approval of a cash settlement (with remarks).
     */
    public function approve_cash_settlement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'approval_remarks' => 'required',
        ]);
        if ($validator->fails()) {
            $response['status'] = 'Error';
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }

        try {
            if (!\Schema::hasColumn('cash_settlements', 'is_approved')) {
                $response['status'] = 'Error';
                $response['msg'] = 'Approval columns not found. Please run migration add_approval_columns_to_cash_settlements_table.';
                return response()->json($response, 200);
            }

            $cashsettlement = CashSettlement::find($request->id);
            if (!$cashsettlement) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.record_not_identified');
                return response()->json($response, 200);
            }

            if ((int) $cashsettlement->is_approved === 1) {
                $response['status'] = 'Error';
                $response['msg'] = 'Settlement already approved';
                return response()->json($response, 200);
            }

            $cashsettlement->is_approved = 1;
            $cashsettlement->approval_remarks = $request->approval_remarks;
            $approvedBy = 0;
            if (!empty($request->approved_by)) {
                $approvedBy = (int) $request->approved_by;
            } elseif (!empty($request->log_id)) {
                $approvedBy = (int) $request->log_id;
            }
            $cashsettlement->approved_by = $approvedBy;
            $cashsettlement->approved_at = date('Y-m-d H:i:s');
            $cashsettlement->updated_by = $approvedBy;

            if ($cashsettlement->save()) {
                $response['status'] = 'Success';
                $response['msg'] = 'Settlement approved successfully';
                $response['data'] = $cashsettlement;
                return response()->json($response, 200);
            }

            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } catch (\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['error'] = $e->getMessage();
            return response()->json($response, 401);
        }
    }
}
    
