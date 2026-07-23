<?php
namespace App\Http\Controllers;

use App\Models\Relationship;
use Illuminate\Http\Request;
use Validator;

class RelationshipController extends Controller
{
    public function store_relationship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'relationship_name' => 'required',
            'gender' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $relationship = new Relationship();
                $relationship->relationship_name = isset($request->relationship_name) ? ($request->relationship_name) : '';
                $relationship->gender = isset($request->gender) ? ($request->gender) : '';
                $relationship->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
                $relationship->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $relationship->status = isset($request->status) ? ($request->status) : 1;
                if($relationship->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_relationship_added');
                    $response['data'] = $relationship;
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

    public function update_relationship(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'relationship_name' => 'required',
            'gender' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$relationship = Relationship::find($request->id);
					$relationship->relationship_name = isset($request->relationship_name) ? ($request->relationship_name) : '';
					$relationship->gender = isset($request->gender) ? ($request->gender) : '';
					$relationship->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($relationship->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_relationship_updated');
						$response['data'] = $relationship;
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
			   
	public function get_relationships(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $relationships = Relationship::orderBy('id','desc')->get();
            if($relationships) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $relationships;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	 
	public function list_relationships(Request $request) {
        try { 

            if(isset($request->tenant_id)) {
                $relationships = Relationship::orderBy('id','desc')->get();
            }
            else {
                $relationships = Relationship::orderBy('id','desc')->get();
            }
             $i=1;
            foreach($relationships as $relationship) {
                $relationship->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $relationships;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function delete_relationship(Request $request) {
		try {
            if(isset($request->id)) {
                $relationship = Relationship::find($request->id); 
                $relationship->deleted_by = $request->deleted_by;
                $relationship->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($relationship->save()) {
                    $relationship->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_relationship_deleted');
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
                $relationship = Relationship::find($request->id); 
                $relationship->status = ($relationship->status) ? 0 : 1;
                if($relationship->save()) {
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
