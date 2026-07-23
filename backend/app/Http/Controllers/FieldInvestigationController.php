<?php
namespace App\Http\Controllers;

use App\Models\FieldInvestigation;
use Illuminate\Http\Request;
use Validator;

class FieldInvestigationController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_field_investigation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'employee_id' => 'required',
            'investigation_date' => 'required',
            'investigation_mode' => 'required',
            'verified_type' => 'required',
            'type_of_row' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 
                $fieldinvest = new FieldInvestigation();
                $fieldinvest->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $fieldinvest->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $fieldinvest->customer_id = isset($request->customer_id) ? ($request->customer_id) : '';
                $fieldinvest->employee_id = isset($request->employee_id) ? ($request->employee_id) : '';
                $fieldinvest->investigation_date = isset($request->investigation_date) ? ($request->investigation_date) : '0000-00-00';
                $fieldinvest->investigation_mode = isset($request->investigation_mode) ? ($request->investigation_mode) : '';
                $fieldinvest->verified_type = isset($request->verified_type) ? ($request->verified_type) : '';
                $fieldinvest->comments = isset($request->comments) ? ($request->comments) :  '';
                $fieldinvest->verification_file = isset($request->verification_file) ? ($request->verification_file) : 0;
                $fieldinvest->type_of_row = isset($request->type_of_row) ? ($request->type_of_row) : '';
                $fieldinvest->reference_id = isset($request->reference_id) ? ($request->reference_id) : 0;
                $fieldinvest->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                $fieldinvest->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $fieldinvest->status = isset($request->status) ? ($request->status) : 1;
                if($fieldinvest->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_field_investigation_added');
                    $response['data'] = $fieldinvest;
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

    public function update_field_investigation(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'employee_id' => 'required',
            'investigation_date' => 'required',
            'investigation_mode' => 'required',
            'verified_type' => 'required',
            'type_of_row' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $fieldinvest = FieldInvestigation::find($request->id); 
                    $fieldinvest->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $fieldinvest->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $fieldinvest->customer_id = isset($request->customer_id) ? ($request->customer_id) : '';
                    $fieldinvest->employee_id = isset($request->employee_id) ? ($request->employee_id) : '';
                    $fieldinvest->investigation_date = isset($request->investigation_date) ? ($request->investigation_date) : '0000-00-00';
                    $fieldinvest->investigation_mode = isset($request->investigation_mode) ? ($request->investigation_mode) : '';
                    $fieldinvest->verified_type = isset($request->verified_type) ? ($request->verified_type) : '';
                    $fieldinvest->comments = isset($request->comments) ? ($request->comments) :  '';
                    $fieldinvest->verification_file = isset($request->verification_file) ? ($request->verification_file) : 0;
                    $fieldinvest->type_of_row = isset($request->type_of_row) ? ($request->type_of_row) : '';
                    $fieldinvest->reference_id = isset($request->reference_id) ? ($request->reference_id) : 0;
                    $fieldinvest->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                    $fieldinvest->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($fieldinvest->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_field_investigation_updated');
                        $response['data'] = $fieldinvest;
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

    public function get_field_investigations(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $fieldinvests = FieldInvestigation::where($cond)->orderBy('id','desc')->get();
            foreach($fieldinvests as $fieldinvest) {
                $fieldinvest->tenant_info = isset($fieldinvest->tenant_det) ? ($fieldinvest->tenant_det) : [];   
                $fieldinvest->branch_info = isset($fieldinvest->branch_det) ? ($fieldinvest->branch_det) : [];
                $fieldinvest->customer_info = isset($fieldinvest->customer_det) ? ($fieldinvest->customer_det) : [];
                $fieldinvest->employee_info = isset($fieldinvest->employee_det) ? ($fieldinvest->employee_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $fieldinvests;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_field_investigations(Request $request) {
        try{
            if(isset($request->tenant_id)) {
                $fieldinvests = FieldInvestigation::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $fieldinvests = FieldInvestigation::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($fieldinvests as $fieldinvest) {
                $fieldinvest->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $fieldinvests;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_field_investigation(Request $request) {
        try {
            if(isset($request->id)) {
                $fieldinvest = FieldInvestigation::find($request->id); 
                $fieldinvest->deleted_by = $request->deleted_by;
                $fieldinvest->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($fieldinvest->save()) {
                    $fieldinvest->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_field_investigation_deleted');
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
