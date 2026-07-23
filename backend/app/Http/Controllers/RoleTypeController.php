<?php
namespace App\Http\Controllers;

use App\Models\RoleType;
use Illuminate\Http\Request;

class RoleTypeController extends Controller
{
    public function get_role_types(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $role_types = RoleType::where($cond)->orderBy('id','desc')->get();
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $role_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_role_types(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $role_types = RoleType::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $role_types = RoleType::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($role_types as $type) {
                $type->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $role_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
