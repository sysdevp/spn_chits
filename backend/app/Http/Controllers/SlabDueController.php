<?php
namespace App\Http\Controllers;

use App\Models\SlabDue;
use App\Models\ChitDetail;
use Illuminate\Http\Request;
use Validator;

class SlabDueController extends Controller
{
    public function store_slab_due(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'schemes_id' => 'required',
            'slab_name' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $slab_due = new SlabDue();
                $slab_due->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $slab_due->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $slab_due->schemes_id = isset($request->schemes_id) ? ($request->schemes_id) : 0;
                $slab_due->slab_name = isset($request->slab_name) ? ($request->slab_name) : '0';
				
                $slab_due->due_1 = isset($request->due_1) ? ($request->due_1) : 0;
                $slab_due->due_2 = isset($request->due_2) ? ($request->due_2) : 0;
                $slab_due->due_3 = isset($request->due_3) ? ($request->due_3) : 0;
                $slab_due->due_4 = isset($request->due_4) ? ($request->due_4) : 0;
                $slab_due->due_5 = isset($request->due_5) ? ($request->due_5) : 0;
                $slab_due->due_6 = isset($request->due_6) ? ($request->due_6) : 0;
                $slab_due->due_7 = isset($request->due_7) ? ($request->due_7) : 0;
                $slab_due->due_8 = isset($request->due_8) ? ($request->due_8) : 0;
                $slab_due->due_9 = isset($request->due_9) ? ($request->due_9) : 0;
                $slab_due->due_10 = isset($request->due_10) ? ($request->due_10) : 0;
                $slab_due->due_11 = isset($request->due_11) ? ($request->due_11) : 0;
                $slab_due->due_12 = isset($request->due_12) ? ($request->due_12) : 0;
                $slab_due->due_13 = isset($request->due_13) ? ($request->due_13) : 0;
                $slab_due->due_14 = isset($request->due_14) ? ($request->due_14) : 0;
                $slab_due->due_15 = isset($request->due_15) ? ($request->due_15) : 0;
                $slab_due->due_16 = isset($request->due_16) ? ($request->due_16) : 0;
                $slab_due->due_17 = isset($request->due_17) ? ($request->due_17) : 0;
                $slab_due->due_18 = isset($request->due_18) ? ($request->due_18) : 0;
                $slab_due->due_19 = isset($request->due_19) ? ($request->due_19) : 0;
                $slab_due->due_20 = isset($request->due_20) ? ($request->due_20) : 0;
                $slab_due->due_21 = isset($request->due_21) ? ($request->due_21) : 0;
                $slab_due->due_22 = isset($request->due_22) ? ($request->due_22) : 0;
                $slab_due->due_23 = isset($request->due_23) ? ($request->due_23) : 0;
                $slab_due->due_24 = isset($request->due_24) ? ($request->due_24) : 0;
                $slab_due->due_25 = isset($request->due_25) ? ($request->due_25) : 0;
                $slab_due->due_26 = isset($request->due_26) ? ($request->due_26) : 0;
                $slab_due->due_27 = isset($request->due_27) ? ($request->due_27) : 0;
                $slab_due->due_28 = isset($request->due_28) ? ($request->due_28) : 0;
                $slab_due->due_29 = isset($request->due_29) ? ($request->due_29) : 0;
                $slab_due->due_30 = isset($request->due_30) ? ($request->due_30) : 0;
                $slab_due->due_31 = isset($request->due_31) ? ($request->due_31) : 0;
                $slab_due->due_32 = isset($request->due_32) ? ($request->due_32) : 0;
                $slab_due->due_33 = isset($request->due_33) ? ($request->due_33) : 0;
                $slab_due->due_34 = isset($request->due_34) ? ($request->due_34) : 0;
                $slab_due->due_35 = isset($request->due_35) ? ($request->due_35) : 0;
                $slab_due->due_36 = isset($request->due_36) ? ($request->due_36) : 0;
                $slab_due->due_37 = isset($request->due_37) ? ($request->due_37) : 0;
                $slab_due->due_38 = isset($request->due_38) ? ($request->due_38) : 0;
                $slab_due->due_39 = isset($request->due_39) ? ($request->due_39) : 0;
                $slab_due->due_40 = isset($request->due_40) ? ($request->due_40) : 0;
                $slab_due->due_41 = isset($request->due_41) ? ($request->due_41) : 0;
                $slab_due->due_42 = isset($request->due_42) ? ($request->due_42) : 0;
                $slab_due->due_43 = isset($request->due_43) ? ($request->due_43) : 0;
                $slab_due->due_44 = isset($request->due_44) ? ($request->due_44) : 0;
                $slab_due->due_45 = isset($request->due_45) ? ($request->due_45) : 0;
                $slab_due->due_46 = isset($request->due_46) ? ($request->due_46) : 0;
                $slab_due->due_47 = isset($request->due_47) ? ($request->due_47) : 0;
                $slab_due->due_48 = isset($request->due_48) ? ($request->due_48) : 0;
                $slab_due->due_49 = isset($request->due_49) ? ($request->due_49) : 0;
                $slab_due->due_50 = isset($request->due_50) ? ($request->due_50) : 0;
				// $slab_due->due_51 = isset($request->due_51) ? ($request->due_51) : 0;
                // $slab_due->due_52 = isset($request->due_52) ? ($request->due_52) : 0;
                // $slab_due->due_53 = isset($request->due_53) ? ($request->due_53) : 0;
                // $slab_due->due_54 = isset($request->due_54) ? ($request->due_54) : 0;
                // $slab_due->due_55 = isset($request->due_55) ? ($request->due_55) : 0;
                // $slab_due->due_56 = isset($request->due_56) ? ($request->due_56) : 0;
                // $slab_due->due_57 = isset($request->due_57) ? ($request->due_57) : 0;
                // $slab_due->due_58 = isset($request->due_58) ? ($request->due_58) : 0;
                // $slab_due->due_59 = isset($request->due_59) ? ($request->due_59) : 0;
                // $slab_due->due_60 = isset($request->due_60) ? ($request->due_60) : 0;
                // $slab_due->due_61 = isset($request->due_61) ? ($request->due_61) : 0;
                // $slab_due->due_62 = isset($request->due_62) ? ($request->due_62) : 0;
                // $slab_due->due_63 = isset($request->due_63) ? ($request->due_63) : 0;
                // $slab_due->due_64 = isset($request->due_64) ? ($request->due_64) : 0;
                // $slab_due->due_65 = isset($request->due_65) ? ($request->due_65) : 0;
                // $slab_due->due_66 = isset($request->due_66) ? ($request->due_66) : 0;
                // $slab_due->due_67 = isset($request->due_67) ? ($request->due_67) : 0;
                // $slab_due->due_68 = isset($request->due_68) ? ($request->due_68) : 0;
                // $slab_due->due_69 = isset($request->due_69) ? ($request->due_69) : 0;
                // $slab_due->due_70 = isset($request->due_70) ? ($request->due_70) : 0;
                // $slab_due->due_71 = isset($request->due_71) ? ($request->due_71) : 0;
                // $slab_due->due_72 = isset($request->due_72) ? ($request->due_72) : 0;
                // $slab_due->due_73 = isset($request->due_73) ? ($request->due_73) : 0;
                // $slab_due->due_74 = isset($request->due_74) ? ($request->due_74) : 0;
                // $slab_due->due_75 = isset($request->due_75) ? ($request->due_75) : 0;
                // $slab_due->due_76 = isset($request->due_76) ? ($request->due_76) : 0;
                // $slab_due->due_77 = isset($request->due_77) ? ($request->due_77) : 0;
                // $slab_due->due_78 = isset($request->due_78) ? ($request->due_78) : 0;
                // $slab_due->due_79 = isset($request->due_79) ? ($request->due_79) : 0;
                // $slab_due->due_80 = isset($request->due_80) ? ($request->due_80) : 0;
                // $slab_due->due_81 = isset($request->due_81) ? ($request->due_81) : 0;
                // $slab_due->due_82 = isset($request->due_82) ? ($request->due_82) : 0;
                // $slab_due->due_83 = isset($request->due_83) ? ($request->due_83) : 0;
                // $slab_due->due_84 = isset($request->due_84) ? ($request->due_84) : 0;
                // $slab_due->due_85 = isset($request->due_85) ? ($request->due_85) : 0;
                // $slab_due->due_86 = isset($request->due_86) ? ($request->due_86) : 0;
                // $slab_due->due_87 = isset($request->due_87) ? ($request->due_87) : 0;
                // $slab_due->due_88 = isset($request->due_88) ? ($request->due_88) : 0;
                // $slab_due->due_89 = isset($request->due_89) ? ($request->due_89) : 0;
                // $slab_due->due_90 = isset($request->due_90) ? ($request->due_90) : 0;
                // $slab_due->due_91 = isset($request->due_91) ? ($request->due_91) : 0;
                // $slab_due->due_92 = isset($request->due_92) ? ($request->due_92) : 0;
                // $slab_due->due_93 = isset($request->due_93) ? ($request->due_93) : 0;
                // $slab_due->due_94 = isset($request->due_94) ? ($request->due_94) : 0;
                // $slab_due->due_95 = isset($request->due_95) ? ($request->due_95) : 0;
                // $slab_due->due_96 = isset($request->due_96) ? ($request->due_96) : 0;
                // $slab_due->due_97 = isset($request->due_97) ? ($request->due_97) : 0;
                // $slab_due->due_98 = isset($request->due_98) ? ($request->due_98) : 0;
                // $slab_due->due_99 = isset($request->due_99) ? ($request->due_99) : 0;
                // $slab_due->due_100 = isset($request->due_100) ? ($request->due_100) : 0;
                $slab_due->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $slab_due->status = isset($request->status) ? ($request->status) : 1;
				
                if($slab_due->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_due_added');
                    $response['data'] = $slab_due;
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

    public function update_slab_due(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'schemes_id' => 'required',
            'slab_name' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $slab_due = SlabDue::find($request->id); 
                    $slab_due->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $slab_due->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $slab_due->schemes_id = isset($request->schemes_id) ? ($request->schemes_id) : 0;
                    $slab_due->slab_name = isset($request->slab_name) ? ($request->slab_name) : '0';
                    $slab_due->due_1 = isset($request->due_1) ? ($request->due_1) : 0;
                    $slab_due->due_2 = isset($request->due_2) ? ($request->due_2) : 0;
                    $slab_due->due_3 = isset($request->due_3) ? ($request->due_3) : 0;
                    $slab_due->due_4 = isset($request->due_4) ? ($request->due_4) : 0;
                    $slab_due->due_5 = isset($request->due_5) ? ($request->due_5) : 0;
                    $slab_due->due_6 = isset($request->due_6) ? ($request->due_6) : 0;
                    $slab_due->due_7 = isset($request->due_7) ? ($request->due_7) : 0;
                    $slab_due->due_8 = isset($request->due_8) ? ($request->due_8) : 0;
                    $slab_due->due_9 = isset($request->due_9) ? ($request->due_9) : 0;
                    $slab_due->due_10 = isset($request->due_10) ? ($request->due_10) : 0;
                    $slab_due->due_11 = isset($request->due_11) ? ($request->due_11) : 0;
                    $slab_due->due_12 = isset($request->due_12) ? ($request->due_12) : 0;
                    $slab_due->due_13 = isset($request->due_13) ? ($request->due_13) : 0;
                    $slab_due->due_14 = isset($request->due_14) ? ($request->due_14) : 0;
                    $slab_due->due_15 = isset($request->due_15) ? ($request->due_15) : 0;
                    $slab_due->due_16 = isset($request->due_16) ? ($request->due_16) : 0;
                    $slab_due->due_17 = isset($request->due_17) ? ($request->due_17) : 0;
                    $slab_due->due_18 = isset($request->due_18) ? ($request->due_18) : 0;
                    $slab_due->due_19 = isset($request->due_19) ? ($request->due_19) : 0;
                    $slab_due->due_20 = isset($request->due_20) ? ($request->due_20) : 0;
                    $slab_due->due_21 = isset($request->due_21) ? ($request->due_21) : 0;
                    $slab_due->due_22 = isset($request->due_22) ? ($request->due_22) : 0;
                    $slab_due->due_23 = isset($request->due_23) ? ($request->due_23) : 0;
                    $slab_due->due_24 = isset($request->due_24) ? ($request->due_24) : 0;
                    $slab_due->due_25 = isset($request->due_25) ? ($request->due_25) : 0;
                    $slab_due->due_26 = isset($request->due_26) ? ($request->due_26) : 0;
                    $slab_due->due_27 = isset($request->due_27) ? ($request->due_27) : 0;
                    $slab_due->due_28 = isset($request->due_28) ? ($request->due_28) : 0;
                    $slab_due->due_29 = isset($request->due_29) ? ($request->due_29) : 0;
                    $slab_due->due_30 = isset($request->due_30) ? ($request->due_30) : 0;
                    $slab_due->due_31 = isset($request->due_31) ? ($request->due_31) : 0;
                    $slab_due->due_32 = isset($request->due_32) ? ($request->due_32) : 0;
                    $slab_due->due_33 = isset($request->due_33) ? ($request->due_33) : 0;
                    $slab_due->due_34 = isset($request->due_34) ? ($request->due_34) : 0;
                    $slab_due->due_35 = isset($request->due_35) ? ($request->due_35) : 0;
                    $slab_due->due_36 = isset($request->due_36) ? ($request->due_36) : 0;
                    $slab_due->due_37 = isset($request->due_37) ? ($request->due_37) : 0;
                    $slab_due->due_38 = isset($request->due_38) ? ($request->due_38) : 0;
                    $slab_due->due_39 = isset($request->due_39) ? ($request->due_39) : 0;
                    $slab_due->due_40 = isset($request->due_40) ? ($request->due_40) : 0;
                    $slab_due->due_41 = isset($request->due_41) ? ($request->due_41) : 0;
                    $slab_due->due_42 = isset($request->due_42) ? ($request->due_42) : 0;
                    $slab_due->due_43 = isset($request->due_43) ? ($request->due_43) : 0;
                    $slab_due->due_44 = isset($request->due_44) ? ($request->due_44) : 0;
                    $slab_due->due_45 = isset($request->due_45) ? ($request->due_45) : 0;
                    $slab_due->due_46 = isset($request->due_46) ? ($request->due_46) : 0;
                    $slab_due->due_47 = isset($request->due_47) ? ($request->due_47) : 0;
                    $slab_due->due_48 = isset($request->due_48) ? ($request->due_48) : 0;
                    $slab_due->due_49 = isset($request->due_49) ? ($request->due_49) : 0;
                    $slab_due->due_50 = isset($request->due_50) ? ($request->due_50) : 0;
					// $slab_due->due_51 = isset($request->due_51) ? ($request->due_51) : 0;
                // $slab_due->due_52 = isset($request->due_52) ? ($request->due_52) : 0;
                // $slab_due->due_53 = isset($request->due_53) ? ($request->due_53) : 0;
                // $slab_due->due_54 = isset($request->due_54) ? ($request->due_54) : 0;
                // $slab_due->due_55 = isset($request->due_55) ? ($request->due_55) : 0;
                // $slab_due->due_56 = isset($request->due_56) ? ($request->due_56) : 0;
                // $slab_due->due_57 = isset($request->due_57) ? ($request->due_57) : 0;
                // $slab_due->due_58 = isset($request->due_58) ? ($request->due_58) : 0;
                // $slab_due->due_59 = isset($request->due_59) ? ($request->due_59) : 0;
                // $slab_due->due_60 = isset($request->due_60) ? ($request->due_60) : 0;
                // $slab_due->due_61 = isset($request->due_61) ? ($request->due_61) : 0;
                // $slab_due->due_62 = isset($request->due_62) ? ($request->due_62) : 0;
                // $slab_due->due_63 = isset($request->due_63) ? ($request->due_63) : 0;
                // $slab_due->due_64 = isset($request->due_64) ? ($request->due_64) : 0;
                // $slab_due->due_65 = isset($request->due_65) ? ($request->due_65) : 0;
                // $slab_due->due_66 = isset($request->due_66) ? ($request->due_66) : 0;
                // $slab_due->due_67 = isset($request->due_67) ? ($request->due_67) : 0;
                // $slab_due->due_68 = isset($request->due_68) ? ($request->due_68) : 0;
                // $slab_due->due_69 = isset($request->due_69) ? ($request->due_69) : 0;
                // $slab_due->due_70 = isset($request->due_70) ? ($request->due_70) : 0;
                // $slab_due->due_71 = isset($request->due_71) ? ($request->due_71) : 0;
                // $slab_due->due_72 = isset($request->due_72) ? ($request->due_72) : 0;
                // $slab_due->due_73 = isset($request->due_73) ? ($request->due_73) : 0;
                // $slab_due->due_74 = isset($request->due_74) ? ($request->due_74) : 0;
                // $slab_due->due_75 = isset($request->due_75) ? ($request->due_75) : 0;
                // $slab_due->due_76 = isset($request->due_76) ? ($request->due_76) : 0;
                // $slab_due->due_77 = isset($request->due_77) ? ($request->due_77) : 0;
                // $slab_due->due_78 = isset($request->due_78) ? ($request->due_78) : 0;
                // $slab_due->due_79 = isset($request->due_79) ? ($request->due_79) : 0;
                // $slab_due->due_80 = isset($request->due_80) ? ($request->due_80) : 0;
                // $slab_due->due_81 = isset($request->due_81) ? ($request->due_81) : 0;
                // $slab_due->due_82 = isset($request->due_82) ? ($request->due_82) : 0;
                // $slab_due->due_83 = isset($request->due_83) ? ($request->due_83) : 0;
                // $slab_due->due_84 = isset($request->due_84) ? ($request->due_84) : 0;
                // $slab_due->due_85 = isset($request->due_85) ? ($request->due_85) : 0;
                // $slab_due->due_86 = isset($request->due_86) ? ($request->due_86) : 0;
                // $slab_due->due_87 = isset($request->due_87) ? ($request->due_87) : 0;
                // $slab_due->due_88 = isset($request->due_88) ? ($request->due_88) : 0;
                // $slab_due->due_89 = isset($request->due_89) ? ($request->due_89) : 0;
                // $slab_due->due_90 = isset($request->due_90) ? ($request->due_90) : 0;
                // $slab_due->due_91 = isset($request->due_91) ? ($request->due_91) : 0;
                // $slab_due->due_92 = isset($request->due_92) ? ($request->due_92) : 0;
                // $slab_due->due_93 = isset($request->due_93) ? ($request->due_93) : 0;
                // $slab_due->due_94 = isset($request->due_94) ? ($request->due_94) : 0;
                // $slab_due->due_95 = isset($request->due_95) ? ($request->due_95) : 0;
                // $slab_due->due_96 = isset($request->due_96) ? ($request->due_96) : 0;
                // $slab_due->due_97 = isset($request->due_97) ? ($request->due_97) : 0;
                // $slab_due->due_98 = isset($request->due_98) ? ($request->due_98) : 0;
                // $slab_due->due_99 = isset($request->due_99) ? ($request->due_99) : 0;
                // $slab_due->due_100 = isset($request->due_100) ? ($request->due_100) : 0;
                    $slab_due->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($slab_due->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_slab_due_updated');
                        $response['data'] = $slab_due;
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

    public function get_slab_dues(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_slab_dues = SlabDue::where($cond)->orderBy('id','desc')->get();
            foreach($list_slab_dues as $slab_due) {
                $slab_due->tenant_info = isset($slab_due->tenant_det) ? ($slab_due->tenant_det) : [];
                $slab_due->branch_info = isset($slab_due->branch_det) ? ($slab_due->branch_det) : [];
                $slab_due->scheme_info = isset($slab_due->scheme_det) ? ($slab_due->scheme_det) : [];
                $slab_due->no_of_months = isset($slab_due->scheme_det) ? ($slab_due->scheme_det->no_of_months) : 0;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_slab_dues;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_slab_dues(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $slab_due_list = SlabDue::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $slab_due_list = SlabDue::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($slab_due_list as $slab_due) {
                $slab_due->sno = $i;
                $slab_due->tenant_info = isset($slab_due->tenant_det) ? ($slab_due->tenant_det) : [];
                $slab_due->branch_info = isset($slab_due->branch_det) ? ($slab_due->branch_det) : [];
                $slab_due->scheme_info = isset($slab_due->scheme_det) ? ($slab_due->scheme_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $slab_due_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_slab_due(Request $request) {
        try {
            if(isset($request->id)) {
                $slab_dues = SlabDue::find($request->id); 
                $slab_dues->deleted_by = $request->deleted_by;
                $slab_dues->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($slab_dues->save()) {
                    $slab_dues->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_due_deleted');
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
                $slab_dues = SlabDue::find($request->id); 
                $slab_dues->status = ($slab_dues->status) ? 0 : 1;
                if($slab_dues->save()) {
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

    public function get_slab_type(Request $request) {
        try { 
            $slab_types = SlabDue::where('schemes_id',$request->id)->get();
            $i=1;
            foreach($slab_types as $slab_type) {
                $slab_type->sno = $i;
                $slab_type->scheme_info = isset($slab_type->scheme_det) ? ($slab_type->scheme_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $slab_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_customer_slab_type(Request $request) {
        try {
		$cond = [];
		$cond['customer_id'] = $request->id;
		$cond['chit_type'] = 3;
            // Include cancelled (soft-deleted) enrollments — used by add-commitment-enrol refund
            $slab_types = ChitDetail::withTrashed()->where($cond)->get();

            $i=1;
			$slab_list = [];

            foreach($slab_types as $slab_type) {

				$slab_due = SlabDue::withTrashed()->where('id',$slab_type->slab_id)->get();
				if(count($slab_due)>0) {
					$slab_type->sno = $i;
					$slab_type->slab_info = $slab_due[0];
					if (isset($slab_type->slab_info->slab_name)) {
						$slab_type->slab_info->slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_type->slab_info->slab_name, $slab_type->ar_no ?? null);
					}
					$i++;
					array_push($slab_list,$slab_type);
				}
                /*$slab_type->sno = $i;
                $slab_name = SlabDue::where('id',$slab_type->slab_id)->get();
                $slab_type->slab_info = $slab_name[0];
                $i++;*/
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $slab_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}