<?php
namespace App\Http\Controllers;

use App\Models\SlabPenalty;
use Illuminate\Http\Request;
use Validator;

class SlabPenaltyController extends Controller
{
    public function store_slab_penalty(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'scheme_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $slab_penalty = new SlabPenalty(); 
                $slab_penalty->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $slab_penalty->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $slab_penalty->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                $slab_penalty->level1_from = isset($request->level1_from) ? ($request->level1_from) : 0;
                $slab_penalty->level1_to = isset($request->level1_to) ? ($request->level1_to) : 0;
                $slab_penalty->level_1_percentage = isset($request->level_1_percentage) ? ($request->level_1_percentage) : 0;
                $slab_penalty->level2_from = isset($request->level2_from) ? ($request->level2_from) : 0;
                $slab_penalty->level2_to = isset($request->level2_to) ? ($request->level2_to) : 0;
                $slab_penalty->level_2_percentage = isset($request->level_2_percentage) ? ($request->level_2_percentage) : 0;
                $slab_penalty->level3_from = isset($request->level3_from) ? ($request->level3_from) : 0;
                $slab_penalty->level3_to = isset($request->level3_to) ? ($request->level3_to) : 0;
                $slab_penalty->level_3_percentage = isset($request->level_3_percentage) ? ($request->level_3_percentage) : 0;
                $slab_penalty->level4_from = isset($request->level4_from) ? ($request->level4_from) : 0;
                $slab_penalty->level4_to = isset($request->level4_to) ? ($request->level4_to) : 0;
                $slab_penalty->level_4_percentage = isset($request->level_4_percentage) ? ($request->level_4_percentage) : 0;
                $slab_penalty->level5_from = isset($request->level5_from) ? ($request->level5_from) : 0;
                $slab_penalty->level5_to = isset($request->level5_to) ? ($request->level5_to) : 0;
                $slab_penalty->level_5_percentage = isset($request->level_5_percentage) ? ($request->level_5_percentage) : 0;
                $slab_penalty->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $slab_penalty->status = isset($request->status) ? ($request->status) : 1;
                if($slab_penalty->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_penalty_added');
                    $response['data'] = $slab_penalty;
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

    public function update_slab_penalty(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'scheme_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $slab_penalty = SlabPenalty::find($request->id); 
                    $slab_penalty->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $slab_penalty->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $slab_penalty->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                    $slab_penalty->level1_from = isset($request->level1_from) ? ($request->level1_from) : 0;
                    $slab_penalty->level1_to = isset($request->level1_to) ? ($request->level1_to) : 0;
                    $slab_penalty->level_1_percentage = isset($request->level_1_percentage) ? ($request->level_1_percentage) : 0;
                    $slab_penalty->level2_from = isset($request->level2_from) ? ($request->level2_from) : 0;
                    $slab_penalty->level2_to = isset($request->level2_to) ? ($request->level2_to) : 0;
                    $slab_penalty->level_2_percentage = isset($request->level_2_percentage) ? ($request->level_2_percentage) : 0;
                    $slab_penalty->level3_from = isset($request->level3_from) ? ($request->level3_from) : 0;
                    $slab_penalty->level3_to = isset($request->level3_to) ? ($request->level3_to) : 0;
                    $slab_penalty->level_3_percentage = isset($request->level_3_percentage) ? ($request->level_3_percentage) : 0;
                    $slab_penalty->level4_from = isset($request->level4_from) ? ($request->level4_from) : 0;
                    $slab_penalty->level4_to = isset($request->level4_to) ? ($request->level4_to) : 0;
                    $slab_penalty->level_4_percentage = isset($request->level_4_percentage) ? ($request->level_4_percentage) : 0;
                    $slab_penalty->level5_from = isset($request->level5_from) ? ($request->level5_from) : 0;
                    $slab_penalty->level5_to = isset($request->level5_to) ? ($request->level5_to) : 0;
                    $slab_penalty->level_5_percentage = isset($request->level_5_percentage) ? ($request->level_5_percentage) : 0;
                    $slab_penalty->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($slab_penalty->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_slab_penalty_updated');
                        $response['data'] = $slab_penalty;
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

    public function get_slab_penalties(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_slab_penalties = SlabPenalty::where($cond)->orderBy('id','desc')->get();
            foreach($list_slab_penalties as $slab_penalty) {
                $slab_penalty->tenant_info = isset($slab_penalty->tenant_det) ? ($slab_penalty->tenant_det) : [];
                $slab_penalty->branch_info = isset($slab_penalty->branch_det) ? ($slab_penalty->branch_det) : [];
                $slab_penalty->scheme_info = isset($slab_penalty->scheme_det) ? ($slab_penalty->scheme_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_slab_penalties;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_slab_penalties(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $list_slab_penalties = SlabPenalty::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $list_slab_penalties = SlabPenalty::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($list_slab_penalties as $slab_penalty) {
                $slab_penalty->sno = $i;
                $slab_penalty->tenant_info = isset($slab_penalty->tenant_det) ? ($slab_penalty->tenant_det) : [];
                $slab_penalty->branch_info = isset($slab_penalty->branch_det) ? ($slab_penalty->branch_det) : [];
                $slab_penalty->scheme_info = isset($slab_penalty->scheme_det) ? ($slab_penalty->scheme_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $list_slab_penalties;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_slab_penalty(Request $request) {
        try {
            if(isset($request->id)) {
                $slab_penalty = SlabPenalty::find($request->id); 
                $slab_penalty->deleted_by = $request->deleted_by;
                $slab_penalty->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($slab_penalty->save()) {
                    $slab_penalty->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_penalty_deleted');
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
                $slab_penalty = SlabPenalty::find($request->id); 
                $slab_penalty->status = ($slab_penalty->status) ? 0 : 1;
                if($slab_penalty->save()) {
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
