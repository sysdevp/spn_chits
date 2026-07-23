<?php
namespace App\Http\Controllers;

use App\Models\SlabBonus;
use Illuminate\Http\Request;
use Validator;

class SlabBonusController extends Controller
{
    public function store_slab_bonus(Request $request) {
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
                $slab_bonus = new SlabBonus();
                $slab_bonus->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $slab_bonus->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $slab_bonus->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                $slab_bonus->level1_from = isset($request->level1_from) ? ($request->level1_from) : 0;
                $slab_bonus->level1_to = isset($request->level1_to) ? ($request->level1_to) : 0;
                $slab_bonus->level_1_percentage = isset($request->level_1_percentage) ? ($request->level_1_percentage) : 0;
                $slab_bonus->level2_from = isset($request->level2_from) ? ($request->level2_from) : 0;
                $slab_bonus->level2_to = isset($request->level2_to) ? ($request->level2_to) : 0;
                $slab_bonus->level_2_percentage = isset($request->level_2_percentage) ? ($request->level_2_percentage) : 0;
                $slab_bonus->level3_from = isset($request->level3_from) ? ($request->level3_from) : 0;
                $slab_bonus->level3_to = isset($request->level3_to) ? ($request->level3_to) : 0;
                $slab_bonus->level_3_percentage = isset($request->level_3_percentage) ? ($request->level_3_percentage) : 0;
                $slab_bonus->level4_from = isset($request->level4_from) ? ($request->level4_from) : 0;
                $slab_bonus->level4_to = isset($request->level4_to) ? ($request->level4_to) : 0;
                $slab_bonus->level_4_percentage = isset($request->level_4_percentage) ? ($request->level_4_percentage) : 0;
                $slab_bonus->level5_from = isset($request->level5_from) ? ($request->level5_from) : 0;
                $slab_bonus->level5_to = isset($request->level5_to) ? ($request->level5_to) : 0;
                $slab_bonus->level_5_percentage = isset($request->level_5_percentage) ? ($request->level_5_percentage) : 0;
                $slab_bonus->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $slab_bonus->status = isset($request->status) ? ($request->status) : 1;
                
                // print_r($slab_bonus);exit;
                if($slab_bonus->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_bonus_added');
                    $response['data'] = $slab_bonus;
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

    public function update_slab_bonus(Request $request) {
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
                    $slab_bonus = SlabBonus::find($request->id); 
                    $slab_bonus->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $slab_bonus->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $slab_bonus->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                    $slab_bonus->level1_from = isset($request->level1_from) ? ($request->level1_from) : 0;
                    $slab_bonus->level1_to = isset($request->level1_to) ? ($request->level1_to) : 0;
                    $slab_bonus->level_1_percentage = isset($request->level_1_percentage) ? ($request->level_1_percentage) : 0;
                    $slab_bonus->level2_from = isset($request->level2_from) ? ($request->level2_from) : 0;
                    $slab_bonus->level2_to = isset($request->level2_to) ? ($request->level2_to) : 0;
                    $slab_bonus->level_2_percentage = isset($request->level_2_percentage) ? ($request->level_2_percentage) : 0;
                    $slab_bonus->level3_from = isset($request->level3_from) ? ($request->level3_from) : 0;
                    $slab_bonus->level3_to = isset($request->level3_to) ? ($request->level3_to) : 0;
                    $slab_bonus->level_3_percentage = isset($request->level_3_percentage) ? ($request->level_3_percentage) : 0;
                    $slab_bonus->level4_from = isset($request->level4_from) ? ($request->level4_from) : 0;
                    $slab_bonus->level4_to = isset($request->level4_to) ? ($request->level4_to) : 0;
                    $slab_bonus->level_4_percentage = isset($request->level_4_percentage) ? ($request->level_4_percentage) : 0;
                    $slab_bonus->level5_from = isset($request->level5_from) ? ($request->level5_from) : 0;
                    $slab_bonus->level5_to = isset($request->level5_to) ? ($request->level5_to) : 0;
                    $slab_bonus->level_5_percentage = isset($request->level_5_percentage) ? ($request->level_5_percentage) : 0;
                    $slab_bonus->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($slab_bonus->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_slab_bonus_updated');
                        $response['data'] = $slab_bonus;
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

    public function get_slab_bonuses(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_slab_bonuses = SlabBonus::where($cond)->orderBy('id','desc')->get();
            foreach($list_slab_bonuses as $slab_bonus) {
                $slab_bonus->tenant_info = isset($slab_bonus->tenant_det) ? ($slab_bonus->tenant_det) : [];
                $slab_bonus->branch_info = isset($slab_bonus->branch_det) ? ($slab_bonus->branch_det) : [];
                $slab_bonus->scheme_info = isset($slab_bonus->scheme_det) ? ($slab_bonus->scheme_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_slab_bonuses;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_slab_bonuses(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $slab_bonus_list = SlabBonus::where('tenant_id',$request->tenant_id)->where('deleted_by','=',NULL)->withTrashed()->orderBy('id','desc')->get();
            }
            else {
                $slab_bonus_list = SlabBonus::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($slab_bonus_list as $slab_bonus) {
                $slab_bonus->sno = $i;
                $slab_bonus->tenant_info = isset($slab_bonus->tenant_det) ? ($slab_bonus->tenant_det) : [];
                $slab_bonus->branch_info = isset($slab_bonus->branch_det) ? ($slab_bonus->branch_det) : [];
                $slab_bonus->scheme_info = isset($slab_bonus->scheme_det) ? ($slab_bonus->scheme_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $slab_bonus_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_slab_bonus(Request $request) {
        try {
            if(isset($request->id)) {
                $slab_bonus = SlabBonus::withTrashed()->find($request->id); 
                
                $slab_bonus->deleted_by = $request->deleted_by;
                $slab_bonus->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($slab_bonus->save()) {
                    $slab_bonus->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_bonus_deleted');
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
                $slab_bonus = SlabBonus::find($request->id); 
                $slab_bonus->status = ($slab_bonus->status) ? 0 : 1;
                if($slab_bonus->save()) {
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
