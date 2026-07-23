<?php

namespace App\Http\Controllers;

use App\Models\Lawyer;
use Illuminate\Http\Request;
use Validator;

class LawyerController extends Controller
{
    public function store_lawyer_name(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'lawyer_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try {                 
                $source_type = new Lawyer();
                $source_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;            
                $source_type->lawyer_name = isset($request->lawyer_name) ? ($request->lawyer_name) : ''; 
                $source_type->lawyer_percentage = isset($request->lawyer_percentage) ? ($request->lawyer_percentage) : '';  
                $source_type->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';   
                $source_type->remarks = isset($request->remarks) ? ($request->remarks) : '';               
                $source_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                
                if($source_type->save()) {                    
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_lawyer_name_added');
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

    public function get_lawyer_names(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $source_types = Lawyer::where($cond)->orderBy('id','desc')->get();
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

    public function list_lawyer_names(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $source_types = Lawyer::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $source_types = Lawyer::orderBy('id','desc')->get();
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

    public function delete_lawyer_name(Request $request) {
        try {
            if(isset($request->id)) {
                $source_type = Lawyer::find($request->id); 
                $source_type->deleted_by = $request->deleted_by;
                $source_type->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($source_type->save()) {
                    $source_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_lawyer_deleted');
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
