<?php
namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Services\RolePermissionNormalizer;
use Illuminate\Http\Request;
use Validator;

class RolePermissionController extends Controller
{
    public function store_permission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {  
                $role_permission = new RolePermission();
                $role_permission->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$role_permission->role_id = isset($request->role_id) ? ($request->role_id) : 0;
                $normalizer = new RolePermissionNormalizer();
                $permissions = $normalizer->normalize($request->Permissions ?? []);
                $role_permission->role_access = serialize($permissions);
                $role_permission->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $role_permission->status = isset($request->status) ? ($request->status) : 1;
                if($role_permission->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_role_permission_added');
                    $response['data'] = $role_permission;
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

    public function update_permission(Request $request) {
    	$validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {  
                $role_permission = RolePermission::find($request->id);
                $role_permission->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$role_permission->role_id = isset($request->role_id) ? ($request->role_id) : 0;
                $normalizer = new RolePermissionNormalizer();
                $permissions = $normalizer->normalize($request->Permissions ?? []);
                $role_permission->role_access = serialize($permissions);
                $role_permission->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                if($role_permission->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_role_permission_updated');
                    $response['data'] = $role_permission;
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

    public function get_permissions(Request $request) {
        try {
            //id,role_id
            $cond = $request->conditions; 
            $role_permissions = RolePermission::where($cond)->orderBy('id','desc')->get();
            foreach($role_permissions as $role_permission) {
                $role_permission->tenant_info = isset($role_permission->tenant_det) ? ($role_permission->tenant_det) : [];
                $role_permission->role_info = isset($role_permission->role_det) ? ($role_permission->role_det) : [];
                $role_permission->role_access = isset($role_permission->role_access) ? unserialize($role_permission->role_access) : '';
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $role_permissions;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
