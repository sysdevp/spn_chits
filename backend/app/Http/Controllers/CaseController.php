<?php

namespace App\Http\Controllers;

use App\Models\CaseName;
use Illuminate\Http\Request;
use Validator;

class CaseController extends Controller
{
    public function store_case_name(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'case_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try {                 
                $source_type = new CaseName();
                $source_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;            
                $source_type->case_name = isset($request->case_name) ? ($request->case_name) : '0';  
                $source_type->remarks = isset($request->remarks) ? ($request->remarks) : '0';               
                $source_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                if($source_type->save()) {                    
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_case_name_added');
                    $response['data'] = $source_type;
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

    public function get_case_names(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $source_types = CaseName::where($cond)->orderBy('id','desc')->get();
            if($source_types) {
                $i=1;
                foreach($source_types as $source_type) {
                    $source_type->sno = $i;
                    $source_type->tenant_info = isset($source_type->tenant_det) ? ($source_type->tenant_det) : [];
                    $i++;
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $source_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_case_names(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $source_types = CaseName::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $source_types = CaseName::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($source_types as $source_type) {
                $source_type->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $source_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_case_name(Request $request) {
        try {
            if(isset($request->id)) {
                $source_type = CaseName::find($request->id); 
                $source_type->deleted_by = $request->deleted_by;
                $source_type->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($source_type->save()) {
                    $source_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_case_name_deleted');
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
