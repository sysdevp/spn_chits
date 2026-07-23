<?php
namespace App\Http\Controllers;

use App\Models\FollowupStatusType;
use Illuminate\Http\Request;
use Validator;

class FollowupStatusTypeController extends Controller
{
   public function store_followup_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'tenant_id' => 'required',
            'followup_status_name' => 'required',
            
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				$followup = new FollowupStatusType();
				$followup->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
				$followup->followup_status_name = isset($request->followup_status_name) ? ($request->followup_status_name) : '';
				
				$followup->created_by = isset($request->created_by) ? ($request->created_by) : 0;
				$followup->status = isset($request->status) ? ($request->status) : 1;
				if($followup->save()) {
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_followup_status_added');
					$response['data'] = $followup;
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
	
	public function update_followup_status(Request $request) {
        $validator = Validator::make($request->all(), [
			'tenant_id' => 'required',
            'followup_status_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
					$followup = FollowupStatusType::find($request->id); 
					$followup->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$followup->followup_status_name = isset($request->followup_status_name) ? ($request->followup_status_name) : '';
					$followup->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($followup->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_followup_status_updated');
						$response['data'] = $followup;
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
	
	public function get_followup_status(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $followups = FollowupStatusType::where($cond)->orderBy('id','desc')->get();
            foreach($followups as $followup) {
                $followup->tenant_info = isset($followup->tenant_det) ? ($followup->tenant_det) : [];   
               
                $followup->followup_status_type_info = isset($followup->followup_status_type_det) ? ($followup->followup_status_type_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $followups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_followup_status(Request $request) {
        try{
            if(isset($request->tenant_id)) {
                $followups = FollowupStatusType::where('tenant_id',$request->tenant_id)->get();
            }
            else {
                $followups = FollowupStatusType::all();
            }
            $i=1;
            foreach($followups as $followup) {
				$followup->tenant_info = isset($followup->tenant_det) ? ($followup->tenant_det) : []; 
                $followup->followup_status_type_info = isset($followup->followup_status_type_det) ? ($followup->followup_status_type_det) : [];
                $followup->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $followups;
            return response()->json($response,200);
		}
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_followup_status(Request $request) {
        try {
            if(isset($request->id)) {
                $followup = FollowupStatusType::find($request->id); 
                $followup->deleted_by = $request->deleted_by;
                $followup->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($followup->save()) {
                    $followup->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_followup_status_deleted');
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
