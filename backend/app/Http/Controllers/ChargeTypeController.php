<?php
namespace App\Http\Controllers;

use App\Models\ChargeType;
use Illuminate\Http\Request;
use Validator;

class ChargeTypeController extends Controller
{
    public function store_charge_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'charge_name' => 'required'
        ]);

        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $charge_type = new ChargeType();
                $charge_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $charge_type->charge_name = isset($request->charge_name) ? ($request->charge_name) : '0';
                $charge_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $charge_type->status = isset($request->status) ? ($request->status) : 1;
                if($charge_type->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_charge_type_added');
                    $response['data'] = $charge_type;
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

    public function update_charge_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'charge_name' => 'required'
        ]);

        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $charge_type = ChargeType::find($request->id); 
                    $charge_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $charge_type->charge_name = isset($request->charge_name) ? ($request->charge_name) : '0';
                    $charge_type->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($charge_type->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_charge_type_updated');
                        $response['data'] = $charge_type;
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

    public function get_charge_types(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_charge_types = ChargeType::where($cond)->orderBy('id','desc')->get();
            foreach($list_charge_types as $charge_type) {
                $charge_type->tenant_info = isset($charge_type->tenant_det) ? ($charge_type->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_charge_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_charge_types(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $charge_types = ChargeType::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $charge_types = ChargeType::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($charge_types as $charge_type) {
                $charge_type->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $charge_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_charge_type(Request $request) {
        try {
            if(isset($request->id)) {
                $charge_type = ChargeType::find($request->id); 
                $charge_type->deleted_by = $request->deleted_by;
                $charge_type->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($charge_type->save()) {
                    $charge_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_charge_type_deleted');
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
                $charge_type = ChargeType::find($request->id); 
                $charge_type->status = ($charge_type->status) ? 0 : 1;
                if($charge_type->save()) {
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
