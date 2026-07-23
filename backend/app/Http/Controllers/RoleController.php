<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Validator;
use Config;

class RoleController extends Controller
{  
    private function buildHierarchyLevelPathByMap($roleId, $rolesById): array
    {
        $path = [];
        $visited = [];
        $currentId = $roleId;
        while (!empty($currentId) && isset($rolesById[$currentId])) {
            if (isset($visited[$currentId])) {
                // Safety: cycle detected
                break;
            }
            $visited[$currentId] = true;
            $currentRole = $rolesById[$currentId];
            $path[] = $currentRole->role_name;
            $currentId = $currentRole->under_id;
        }
        return array_reverse($path);
    }

    private function buildHierarchyLevelPathByQueries($role): array
    {
        $path = [];
        $visited = [];
        $current = $role;
        $tenantId = $role->tenant_id;
        $maxDepth = 50;
        $depth = 0;

        while ($current && !empty($current->id) && $depth < $maxDepth) {
            if (isset($visited[$current->id])) {
                break; // cycle
            }
            $visited[$current->id] = true;
            $path[] = $current->role_name;

            $nextId = $current->under_id ?? null;
            if (empty($nextId) || (int)$nextId === 0) {
                break;
            }

            $current = Role::where('tenant_id', $tenantId)
                ->where('id', $nextId)
                ->first();

            $depth++;
        }

        return array_reverse($path);
    }

    public function store_role(Request $request)
    {
        // Angular may send '' for "no parent"; normalize to null for validation.
        if ($request->input('under_id') === '') {
            $request->merge(['under_id' => null]);
        }
        if ($request->input('under_role_id') === '') {
            $request->merge(['under_role_id' => null]);
        }

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'role_name' => 'required',
            'role_type_id' => 'required',
            'tenant_id' => 'required',
            'under_id' => 'nullable|integer',
            'under_role_id' => 'nullable|integer'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $role = new Role();
                $role->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                // Roles are tenant-scoped; branch is not maintained on role master.
                $role->branch_id = 0;
                $role->role_name = isset($request->role_name) ? ($request->role_name) : '';
                $role->role_type_id = isset($request->role_type_id) ? ($request->role_type_id) : '';
                $role->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $role->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $role->status = isset($request->status) ? ($request->status) : 1;

                $underId = null;
                if (isset($request->under_id) && $request->under_id !== '') {
                    $underId = (int)$request->under_id;
                } elseif (isset($request->under_role_id) && $request->under_role_id !== '') {
                    $underId = (int)$request->under_role_id;
                }
                if (!empty($underId) && $underId > 0) {
                    // Ensure "under role" belongs to same tenant
                    $underRole = Role::where('tenant_id', $role->tenant_id)->where('id', $underId)->first();
                    if (!$underRole) {
                        $response['status'] = "Error";
                        $response['msg'] = "Invalid under role selected for this tenant.";
                        return response()->json($response, 200);
                    }
                    $role->under_id = $underId;
                } else {
                    $role->under_id = null;
                }

                if($role->save()) {
					if($request->tenant_id==37){
					 $role_code = 'SBMA-' . sprintf('%05d', $role->id);	
					} else {
					 $role_code = Config::get('constants.auto_codes.role').sprintf('%05d', $role->id);	
					}
                   
                    $role->role_code = $role_code;
                    if($role->save()) {
                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_role_added');
                        $response['data'] = $role;
                        return response()->json($response,200);
                    }
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }
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

    public function update_role(Request $request)
    {
        // Angular may send '' for "no parent"; normalize to null for validation.
        if ($request->input('under_id') === '') {
            $request->merge(['under_id' => null]);
        }
        if ($request->input('under_role_id') === '') {
            $request->merge(['under_role_id' => null]);
        }

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'role_name' => 'required',
            'role_type_id' => 'required'
            ,
            'under_id' => 'nullable|integer',
            'under_role_id' => 'nullable|integer'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$role = Role::find($request->id);
					$role->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$role->role_name = isset($request->role_name) ? ($request->role_name) : '';
                    $role->role_code = isset($request->role_code) ? ($request->role_code) : '';
                    $role->role_type_id = isset($request->role_type_id) ? ($request->role_type_id) : '';
					$role->remarks = isset($request->remarks) ? ($request->remarks) : '';
					$role->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;

                    // Only update parent relationship if provided by the caller.
                    if ($request->has('under_id') || $request->has('under_role_id')) {
                        $underId = null;
                        if (isset($request->under_id) && $request->under_id !== '') {
                            $underId = (int)$request->under_id;
                        } elseif (isset($request->under_role_id) && $request->under_role_id !== '') {
                            $underId = (int)$request->under_role_id;
                        }

                        if (!empty($underId) && $underId > 0) {
                            $underRole = Role::where('tenant_id', $role->tenant_id)->where('id', $underId)->first();
                            if (!$underRole) {
                                $response['status'] = "Error";
                                $response['msg'] = "Invalid under role selected for this tenant.";
                                return response()->json($response, 200);
                            }
                            $role->under_id = $underId;
                        } else {
                            $role->under_id = null;
                        }
                    }

					if($role->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_role_updated');
						$response['data'] = $role;
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
			   
	public function get_roles(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $roles = Role::where($cond)->orderBy('id','desc')->get();
            foreach($roles as $role) {
                $role->tenant_info = isset($role->tenant_det) ? ($role->tenant_det) : [];
                $role->role_type_info = ($role->role_type_det) ? ($role->role_type_det) : [];
                $role->hierarchy_level = $this->buildHierarchyLevelPathByQueries($role);
            } 
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $roles;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }    
	 
	public function list_roles(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $roles = Role::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $roles = Role::orderBy('id','desc')->get();
            }
            $i=1;
            $rolesById = $roles->keyBy('id');
            foreach($roles as $role) {
                $role->sno = $i;
                $role->role_type_info = isset($role->role_type_det) ? ($role->role_type_det) : [];
                $role->hierarchy_level = $this->buildHierarchyLevelPathByMap($role->id, $rolesById);
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $roles;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function delete_role(Request $request) {
		try {
            if(isset($request->id)) {
                $role = Role::find($request->id); 
                $role->deleted_by = $request->deleted_by;
                $role->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($role->save()) {
                    $role->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_role_deleted');
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
                $role = Role::find($request->id); 
                $role->status = ($role->status) ? 0 : 1;
                if($role->save()) {
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

    public function get_roles_for_permission(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $existing_permissions = RolePermission::where($cond)->get();
            $existing_list = [];
            $role_id = isset($request->role_id) ? ($request->role_id) : '';
            $is_super_admin = isset($request->is_super_admin) ? ($request->is_super_admin) : false;
            foreach($existing_permissions as $perm) {
                if($is_super_admin) {
                    array_push($existing_list,$perm->role_id);
                }
                else {
                    if($role_id!='') {
                        if($role_id!=$perm->role_id) {
                            array_push($existing_list,$perm->role_id);
                        }
                    }
                    else {
                        array_push($existing_list,$perm->role_id);
                    }
                }
            }
            $roles = Role::where($cond)->orderBy('id','desc')->get();
            if(count($existing_list)>0) {
                $roles = Role::where($cond)->whereNotIn('id',$existing_list)->orderBy('id','desc')->get();
            }
            if($request->for_edit) {
                $roles = Role::where($cond)->whereIn('id',$existing_list)->orderBy('id','desc')->get();
            }
            foreach($roles as $role) {
                $role->tenant_info = isset($role->tenant_det) ? ($role->tenant_det) : [];
                $role->role_type_info = ($role->role_type_det) ? ($role->role_type_det) : [];
            } 
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $roles;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
