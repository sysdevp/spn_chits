<?php
namespace App\Http\Controllers;

use App\Models\PaymentType;
use Illuminate\Http\Request;
use Validator;

class PaymentTypeController extends Controller
{
    public function store_payment_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'payment_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $payment_type = new PaymentType();
                $payment_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $payment_type->payment_name = isset($request->payment_name) ? ($request->payment_name) : '';
                $payment_type->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment_type->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $payment_type->status = isset($request->status) ? ($request->status) : 1;
                if($payment_type->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_payment_type_added');
                    $response['data'] = $payment_type;
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

    public function update_payment_type(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'payment_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $p_type = PaymentType::find($request->id); 
                    $p_type->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0; 
                    $p_type->payment_name = isset($request->payment_name) ? ($request->payment_name) : '';
                    $p_type->remarks = isset($request->remarks) ? ($request->remarks) : ''; 
                    $p_type->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0; 
                    if($p_type->save()) { 
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_payment_type_updated');
                        $response['data'] = $p_type;
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

    public function get_payment_types(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $payment_types = PaymentType::where($cond)->orderBy('id','Asc')->get();
            foreach($payment_types as $payment_type) {
                $payment_type->tenant_info = isset($payment_type->tenant_det) ? ($payment_type->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $payment_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_payment_types(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $payment_types = PaymentType::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $payment_types = PaymentType::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($payment_types as $payment_type) {
                $payment_type->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payment_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_payment_type(Request $request) {
        try {
            if(isset($request->id)) {
                $payment_type = PaymentType::find($request->id); 
                $payment_type->deleted_by = $request->deleted_by;
                $payment_type->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($payment_type->save()) {
                    $payment_type->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_payment_type_deleted');
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
