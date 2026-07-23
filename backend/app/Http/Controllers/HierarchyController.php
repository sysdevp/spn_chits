<?php
namespace App\Http\Controllers;

use App\Models\Hierarchy;
use Illuminate\Http\Request;
use Validator;

class HierarchyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_hierarchy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'under_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'created_by' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $hierarchy = new Hierarchy();
                $hierarchy->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $hierarchy->under_id = isset($request->under_id) ? ($request->under_id) : 0;
                $hierarchy->name = isset($request->name) ? ($request->name) : '';
                $hierarchy->email = isset($request->email) ? ($request->email) : '';
                $hierarchy->level_id = isset($request->level_id) ? ($request->level_id) : 0;
                $hierarchy->level_value = isset($request->level_value) ? ($request->level_value) : '';
                $hierarchy->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $hierarchy->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $hierarchy->status = isset($request->status) ? ($request->status) : 1;
                if($hierarchy->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_hierarchy_added');
                    $response['data'] = $hierarchy;
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

    public function update_hierarchy(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'under_id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'created_by' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $hierarchy = Hierarchy::find($request->id); 
                    $hierarchy->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $hierarchy->under_id = isset($request->under_id) ? ($request->under_id) : 0;
                    $hierarchy->name = isset($request->name) ? ($request->name) : '';
                    $hierarchy->email = isset($request->email) ? ($request->email) : '';
                    $hierarchy->level_id = isset($request->level_id) ? ($request->level_id) : 0;
                    $hierarchy->level_value = isset($request->level_value) ? ($request->level_value) : '';
                    $hierarchy->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $hierarchy->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($hierarchy->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_hierarchy_updated');
                        $response['data'] = $hierarchy;
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

    public function get_hierarchies(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $hierarchies = Hierarchy::where($cond)->orderBy('id','desc')->get();
            foreach($hierarchies as $hierarchy) {
                $hierarchy->tenant_info = isset($hierarchy->tenant_det) ? ($hierarchy->tenant_det) : [];   
                $hierarchy->branch_info = isset($hierarchy->branch_det) ? ($hierarchy->branch_det) : [];
                $hierarchy->customer_info = isset($hierarchy->customer_det) ? ($hierarchy->customer_det) : [];
                $hierarchy->employee_info = isset($hierarchy->employee_det) ? ($hierarchy->employee_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $hierarchies;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_hierarchies(Request $request) {
        try{
             if(isset($request->tenant_id)) {
                $hierarchies = Hierarchy::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $hierarchies = Hierarchy::orderBy('id','desc')->get();
            }
           $i=1;
            foreach($hierarchies as $hierarchy) {
                $hierarchy->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $hierarchies;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_hierarchy(Request $request) {
        try {
            if(isset($request->id)) {
                $hierarchy = Hierarchy::find($request->id); 
                $hierarchy->deleted_by = $request->deleted_by;
                $hierarchy->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($hierarchy->save()) {
                    $hierarchy->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_hierarchy_deleted');
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

    public function get_under_list(Request $request)
    { 
        try {
            $cond = $request->conditions; 
            $hierarchies = Hierarchy::where($cond)->orderBy('id','desc')->get();
            foreach($hierarchies as $hierarchy)
            {
                $tmp_name ='';
                if($hierarchy->under_id!=0)
                {
                    $text = '';
                    $tmp_name = $this->set_text($hierarchy->under_id, $text);
                }
                $hierarchy->name  = $tmp_name.$hierarchy->name  ;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $hierarchies;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function set_text ($id,$text) {
        $hier = Hierarchy::find($id);
        $text  .= '|--';
        if($hier->under_id) 
        {
            $this->set_text($hier->under_id,$text);
        }
        return  $text;
    }
}
      
 

