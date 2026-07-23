<?php
namespace App\Http\Controllers;

use App\Models\SlabPayment;
use Illuminate\Http\Request;
use Validator;

class SlabPaymentController extends Controller
{
    public function store_slab_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'schemes_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $slab_payment = new SlabPayment();
                $slab_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $slab_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $slab_payment->schemes_id = isset($request->schemes_id) ? ($request->schemes_id) : 0;
                $slab_payment->slab_id = isset($request->slab_id) ? ($request->slab_id) : 0;
                $slab_payment->payment_1 = isset($request->payment_1) ? ($request->payment_1) : 0;
                $slab_payment->payment_2 = isset($request->payment_2) ? ($request->payment_2) : 0;
                $slab_payment->payment_3 = isset($request->payment_3) ? ($request->payment_3) : 0;
                $slab_payment->payment_4 = isset($request->payment_4) ? ($request->payment_4) : 0;
                $slab_payment->payment_5 = isset($request->payment_5) ? ($request->payment_5) : 0;
                $slab_payment->payment_6 = isset($request->payment_6) ? ($request->payment_6) : 0;
                $slab_payment->payment_7 = isset($request->payment_7) ? ($request->payment_7) : 0;
                $slab_payment->payment_8 = isset($request->payment_8) ? ($request->payment_8) : 0;
                $slab_payment->payment_9 = isset($request->payment_9) ? ($request->payment_9) : 0;
                $slab_payment->payment_10 = isset($request->payment_10) ? ($request->payment_10) : 0;
                $slab_payment->payment_11 = isset($request->payment_11) ? ($request->payment_11) : 0;
                $slab_payment->payment_12 = isset($request->payment_12) ? ($request->payment_12) : 0;
                $slab_payment->payment_13 = isset($request->payment_13) ? ($request->payment_13) : 0;
                $slab_payment->payment_14 = isset($request->payment_14) ? ($request->payment_14) : 0;
                $slab_payment->payment_15 = isset($request->payment_15) ? ($request->payment_15) : 0;
                $slab_payment->payment_16 = isset($request->payment_16) ? ($request->payment_16) : 0;
                $slab_payment->payment_17 = isset($request->payment_17) ? ($request->payment_17) : 0;
                $slab_payment->payment_18 = isset($request->payment_18) ? ($request->payment_18) : 0;
                $slab_payment->payment_19 = isset($request->payment_19) ? ($request->payment_19) : 0;
                $slab_payment->payment_20 = isset($request->payment_20) ? ($request->payment_20) : 0;
                $slab_payment->payment_21 = isset($request->payment_21) ? ($request->payment_21) : 0;
                $slab_payment->payment_22 = isset($request->payment_22) ? ($request->payment_22) : 0;
                $slab_payment->payment_23 = isset($request->payment_23) ? ($request->payment_23) : 0;
                $slab_payment->payment_24 = isset($request->payment_24) ? ($request->payment_24) : 0;
                $slab_payment->payment_25 = isset($request->payment_25) ? ($request->payment_25) : 0;
                $slab_payment->payment_26 = isset($request->payment_26) ? ($request->payment_26) : 0;
                $slab_payment->payment_27 = isset($request->payment_27) ? ($request->payment_27) : 0;
                $slab_payment->payment_28 = isset($request->payment_28) ? ($request->payment_28) : 0;
                $slab_payment->payment_29 = isset($request->payment_29) ? ($request->payment_29) : 0;
                $slab_payment->payment_30 = isset($request->payment_30) ? ($request->payment_30) : 0;
                $slab_payment->payment_31 = isset($request->payment_31) ? ($request->payment_31) : 0;
                $slab_payment->payment_32 = isset($request->payment_32) ? ($request->payment_32) : 0;
                $slab_payment->payment_33 = isset($request->payment_33) ? ($request->payment_33) : 0;
                $slab_payment->payment_34 = isset($request->payment_34) ? ($request->payment_34) : 0;
                $slab_payment->payment_35 = isset($request->payment_35) ? ($request->payment_35) : 0;
                $slab_payment->payment_36 = isset($request->payment_36) ? ($request->payment_36) : 0;
                $slab_payment->payment_37 = isset($request->payment_37) ? ($request->payment_37) : 0;
                $slab_payment->payment_38 = isset($request->payment_38) ? ($request->payment_38) : 0;
                $slab_payment->payment_39 = isset($request->payment_39) ? ($request->payment_39) : 0;
                $slab_payment->payment_40 = isset($request->payment_40) ? ($request->payment_40) : 0;
                $slab_payment->payment_41 = isset($request->payment_41) ? ($request->payment_41) : 0;
                $slab_payment->payment_42 = isset($request->payment_42) ? ($request->payment_42) : 0;
                $slab_payment->payment_43 = isset($request->payment_43) ? ($request->payment_43) : 0;
                $slab_payment->payment_44 = isset($request->payment_44) ? ($request->payment_44) : 0;
                $slab_payment->payment_45 = isset($request->payment_45) ? ($request->payment_45) : 0;
                $slab_payment->payment_46 = isset($request->payment_46) ? ($request->payment_46) : 0;
                $slab_payment->payment_47 = isset($request->payment_47) ? ($request->payment_47) : 0;
                $slab_payment->payment_48 = isset($request->payment_48) ? ($request->payment_48) : 0;
                $slab_payment->payment_49 = isset($request->payment_49) ? ($request->payment_49) : 0;
                $slab_payment->payment_50 = isset($request->payment_50) ? ($request->payment_50) : 0;
				// $slab_payment->payment_51 = isset($request->payment_51) ? ($request->payment_51) : 0;
				// $slab_payment->payment_52 = isset($request->payment_52) ? ($request->payment_52) : 0;
				// $slab_payment->payment_53 = isset($request->payment_53) ? ($request->payment_53) : 0;
				// $slab_payment->payment_54 = isset($request->payment_54) ? ($request->payment_54) : 0;
				// $slab_payment->payment_55 = isset($request->payment_55) ? ($request->payment_55) : 0;
				// $slab_payment->payment_56 = isset($request->payment_56) ? ($request->payment_56) : 0;
				// $slab_payment->payment_57 = isset($request->payment_57) ? ($request->payment_57) : 0;
				// $slab_payment->payment_58 = isset($request->payment_58) ? ($request->payment_58) : 0;
				// $slab_payment->payment_59 = isset($request->payment_59) ? ($request->payment_59) : 0;
				// $slab_payment->payment_60 = isset($request->payment_60) ? ($request->payment_60) : 0;
				// $slab_payment->payment_61 = isset($request->payment_61) ? ($request->payment_61) : 0;
				// $slab_payment->payment_62 = isset($request->payment_62) ? ($request->payment_62) : 0;
				// $slab_payment->payment_63 = isset($request->payment_63) ? ($request->payment_63) : 0;
				// $slab_payment->payment_64 = isset($request->payment_64) ? ($request->payment_64) : 0;
				// $slab_payment->payment_65 = isset($request->payment_65) ? ($request->payment_65) : 0;
				// $slab_payment->payment_66 = isset($request->payment_66) ? ($request->payment_66) : 0;
				// $slab_payment->payment_67 = isset($request->payment_67) ? ($request->payment_67) : 0;
				// $slab_payment->payment_68 = isset($request->payment_68) ? ($request->payment_68) : 0;
				// $slab_payment->payment_69 = isset($request->payment_69) ? ($request->payment_69) : 0;
				// $slab_payment->payment_70 = isset($request->payment_70) ? ($request->payment_70) : 0;
				// $slab_payment->payment_71 = isset($request->payment_71) ? ($request->payment_71) : 0;
				// $slab_payment->payment_72 = isset($request->payment_72) ? ($request->payment_72) : 0;
				// $slab_payment->payment_73 = isset($request->payment_73) ? ($request->payment_73) : 0;
				// $slab_payment->payment_74 = isset($request->payment_74) ? ($request->payment_74) : 0;
				// $slab_payment->payment_75 = isset($request->payment_75) ? ($request->payment_75) : 0;
				// $slab_payment->payment_76 = isset($request->payment_76) ? ($request->payment_76) : 0;
				// $slab_payment->payment_77 = isset($request->payment_77) ? ($request->payment_77) : 0;
				// $slab_payment->payment_78 = isset($request->payment_78) ? ($request->payment_78) : 0;
				// $slab_payment->payment_79 = isset($request->payment_79) ? ($request->payment_79) : 0;
				// $slab_payment->payment_80 = isset($request->payment_80) ? ($request->payment_80) : 0;
				// $slab_payment->payment_81 = isset($request->payment_81) ? ($request->payment_81) : 0;
				// $slab_payment->payment_82 = isset($request->payment_82) ? ($request->payment_82) : 0;
				// $slab_payment->payment_83 = isset($request->payment_83) ? ($request->payment_83) : 0;
				// $slab_payment->payment_84 = isset($request->payment_84) ? ($request->payment_84) : 0;
				// $slab_payment->payment_85 = isset($request->payment_85) ? ($request->payment_85) : 0;
				// $slab_payment->payment_86 = isset($request->payment_86) ? ($request->payment_86) : 0;
				// $slab_payment->payment_87 = isset($request->payment_87) ? ($request->payment_87) : 0;
				// $slab_payment->payment_88 = isset($request->payment_88) ? ($request->payment_88) : 0;
				// $slab_payment->payment_89 = isset($request->payment_89) ? ($request->payment_89) : 0;
				// $slab_payment->payment_90 = isset($request->payment_90) ? ($request->payment_90) : 0;
				// $slab_payment->payment_91 = isset($request->payment_91) ? ($request->payment_91) : 0;
				// $slab_payment->payment_92 = isset($request->payment_92) ? ($request->payment_92) : 0;
				// $slab_payment->payment_93 = isset($request->payment_93) ? ($request->payment_93) : 0;
				// $slab_payment->payment_94 = isset($request->payment_94) ? ($request->payment_94) : 0;
				// $slab_payment->payment_95 = isset($request->payment_95) ? ($request->payment_95) : 0;
				// $slab_payment->payment_96 = isset($request->payment_96) ? ($request->payment_96) : 0;
				// $slab_payment->payment_97 = isset($request->payment_97) ? ($request->payment_97) : 0;
				// $slab_payment->payment_98 = isset($request->payment_98) ? ($request->payment_98) : 0;
				// $slab_payment->payment_99 = isset($request->payment_99) ? ($request->payment_99) : 0;
				// $slab_payment->payment_100 = isset($request->payment_100) ? ($request->payment_100) : 0;
                $slab_payment->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $slab_payment->status = isset($request->status) ? ($request->status) : 1;
                if($slab_payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_payment_added');
                    $response['data'] = $slab_payment;
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

    public function update_slab_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'schemes_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $slab_payment = SlabPayment::find($request->id); 
                    $slab_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $slab_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $slab_payment->schemes_id = isset($request->schemes_id) ? ($request->schemes_id) : 0;
					$slab_payment->slab_id = isset($request->slab_id) ? ($request->slab_id) : 0;
                    $slab_payment->payment_1 = isset($request->payment_1) ? ($request->payment_1) : 0;
                    $slab_payment->payment_2 = isset($request->payment_2) ? ($request->payment_2) : 0;
                    $slab_payment->payment_3 = isset($request->payment_3) ? ($request->payment_3) : 0;
                    $slab_payment->payment_4 = isset($request->payment_4) ? ($request->payment_4) : 0;
                    $slab_payment->payment_5 = isset($request->payment_5) ? ($request->payment_5) : 0;
                    $slab_payment->payment_6 = isset($request->payment_6) ? ($request->payment_6) : 0;
                    $slab_payment->payment_7 = isset($request->payment_7) ? ($request->payment_7) : 0;
                    $slab_payment->payment_8 = isset($request->payment_8) ? ($request->payment_8) : 0;
                    $slab_payment->payment_9 = isset($request->payment_9) ? ($request->payment_9) : 0;
                    $slab_payment->payment_10 = isset($request->payment_10) ? ($request->payment_10) : 0;
                    $slab_payment->payment_11 = isset($request->payment_11) ? ($request->payment_11) : 0;
                    $slab_payment->payment_12 = isset($request->payment_12) ? ($request->payment_12) : 0;
                    $slab_payment->payment_13 = isset($request->payment_13) ? ($request->payment_13) : 0;
                    $slab_payment->payment_14 = isset($request->payment_14) ? ($request->payment_14) : 0;
                    $slab_payment->payment_15 = isset($request->payment_15) ? ($request->payment_15) : 0;
                    $slab_payment->payment_16 = isset($request->payment_16) ? ($request->payment_16) : 0;
                    $slab_payment->payment_17 = isset($request->payment_17) ? ($request->payment_17) : 0;
                    $slab_payment->payment_18 = isset($request->payment_18) ? ($request->payment_18) : 0;
                    $slab_payment->payment_19 = isset($request->payment_19) ? ($request->payment_19) : 0;
                    $slab_payment->payment_20 = isset($request->payment_20) ? ($request->payment_20) : 0;
                    $slab_payment->payment_21 = isset($request->payment_21) ? ($request->payment_21) : 0;
                    $slab_payment->payment_22 = isset($request->payment_22) ? ($request->payment_22) : 0;
                    $slab_payment->payment_23 = isset($request->payment_23) ? ($request->payment_23) : 0;
                    $slab_payment->payment_24 = isset($request->payment_24) ? ($request->payment_24) : 0;
                    $slab_payment->payment_25 = isset($request->payment_25) ? ($request->payment_25) : 0;
                    $slab_payment->payment_26 = isset($request->payment_26) ? ($request->payment_26) : 0;
                    $slab_payment->payment_27 = isset($request->payment_27) ? ($request->payment_27) : 0;
                    $slab_payment->payment_28 = isset($request->payment_28) ? ($request->payment_28) : 0;
                    $slab_payment->payment_29 = isset($request->payment_29) ? ($request->payment_29) : 0;
                    $slab_payment->payment_30 = isset($request->payment_30) ? ($request->payment_30) : 0;
                    $slab_payment->payment_31 = isset($request->payment_31) ? ($request->payment_31) : 0;
                    $slab_payment->payment_32 = isset($request->payment_32) ? ($request->payment_32) : 0;
                    $slab_payment->payment_33 = isset($request->payment_33) ? ($request->payment_33) : 0;
                    $slab_payment->payment_34 = isset($request->payment_34) ? ($request->payment_34) : 0;
                    $slab_payment->payment_35 = isset($request->payment_35) ? ($request->payment_35) : 0;
                    $slab_payment->payment_36 = isset($request->payment_36) ? ($request->payment_36) : 0;
                    $slab_payment->payment_37 = isset($request->payment_37) ? ($request->payment_37) : 0;
                    $slab_payment->payment_38 = isset($request->payment_38) ? ($request->payment_38) : 0;
                    $slab_payment->payment_39 = isset($request->payment_39) ? ($request->payment_39) : 0;
                    $slab_payment->payment_40 = isset($request->payment_40) ? ($request->payment_40) : 0;
                    $slab_payment->payment_41 = isset($request->payment_41) ? ($request->payment_41) : 0;
                    $slab_payment->payment_42 = isset($request->payment_42) ? ($request->payment_42) : 0;
                    $slab_payment->payment_43 = isset($request->payment_43) ? ($request->payment_43) : 0;
                    $slab_payment->payment_44 = isset($request->payment_44) ? ($request->payment_44) : 0;
                    $slab_payment->payment_45 = isset($request->payment_45) ? ($request->payment_45) : 0;
                    $slab_payment->payment_46 = isset($request->payment_46) ? ($request->payment_46) : 0;
                    $slab_payment->payment_47 = isset($request->payment_47) ? ($request->payment_47) : 0;
                    $slab_payment->payment_48 = isset($request->payment_48) ? ($request->payment_48) : 0;
                    $slab_payment->payment_49 = isset($request->payment_49) ? ($request->payment_49) : 0;
                    $slab_payment->payment_50 = isset($request->payment_50) ? ($request->payment_50) : 0;
					// $slab_payment->payment_51 = isset($request->payment_51) ? ($request->payment_51) : 0;
					// $slab_payment->payment_52 = isset($request->payment_52) ? ($request->payment_52) : 0;
					// $slab_payment->payment_53 = isset($request->payment_53) ? ($request->payment_53) : 0;
					// $slab_payment->payment_54 = isset($request->payment_54) ? ($request->payment_54) : 0;
					// $slab_payment->payment_55 = isset($request->payment_55) ? ($request->payment_55) : 0;
					// $slab_payment->payment_56 = isset($request->payment_56) ? ($request->payment_56) : 0;
					// $slab_payment->payment_57 = isset($request->payment_57) ? ($request->payment_57) : 0;
					// $slab_payment->payment_58 = isset($request->payment_58) ? ($request->payment_58) : 0;
					// $slab_payment->payment_59 = isset($request->payment_59) ? ($request->payment_59) : 0;
					// $slab_payment->payment_60 = isset($request->payment_60) ? ($request->payment_60) : 0;
					// $slab_payment->payment_61 = isset($request->payment_61) ? ($request->payment_61) : 0;
					// $slab_payment->payment_62 = isset($request->payment_62) ? ($request->payment_62) : 0;
					// $slab_payment->payment_63 = isset($request->payment_63) ? ($request->payment_63) : 0;
					// $slab_payment->payment_64 = isset($request->payment_64) ? ($request->payment_64) : 0;
					// $slab_payment->payment_65 = isset($request->payment_65) ? ($request->payment_65) : 0;
					// $slab_payment->payment_66 = isset($request->payment_66) ? ($request->payment_66) : 0;
					// $slab_payment->payment_67 = isset($request->payment_67) ? ($request->payment_67) : 0;
					// $slab_payment->payment_68 = isset($request->payment_68) ? ($request->payment_68) : 0;
					// $slab_payment->payment_69 = isset($request->payment_69) ? ($request->payment_69) : 0;
					// $slab_payment->payment_70 = isset($request->payment_70) ? ($request->payment_70) : 0;
					// $slab_payment->payment_71 = isset($request->payment_71) ? ($request->payment_71) : 0;
					// $slab_payment->payment_72 = isset($request->payment_72) ? ($request->payment_72) : 0;
					// $slab_payment->payment_73 = isset($request->payment_73) ? ($request->payment_73) : 0;
					// $slab_payment->payment_74 = isset($request->payment_74) ? ($request->payment_74) : 0;
					// $slab_payment->payment_75 = isset($request->payment_75) ? ($request->payment_75) : 0;
					// $slab_payment->payment_76 = isset($request->payment_76) ? ($request->payment_76) : 0;
					// $slab_payment->payment_77 = isset($request->payment_77) ? ($request->payment_77) : 0;
					// $slab_payment->payment_78 = isset($request->payment_78) ? ($request->payment_78) : 0;
					// $slab_payment->payment_79 = isset($request->payment_79) ? ($request->payment_79) : 0;
					// $slab_payment->payment_80 = isset($request->payment_80) ? ($request->payment_80) : 0;
					// $slab_payment->payment_81 = isset($request->payment_81) ? ($request->payment_81) : 0;
					// $slab_payment->payment_82 = isset($request->payment_82) ? ($request->payment_82) : 0;
					// $slab_payment->payment_83 = isset($request->payment_83) ? ($request->payment_83) : 0;
					// $slab_payment->payment_84 = isset($request->payment_84) ? ($request->payment_84) : 0;
					// $slab_payment->payment_85 = isset($request->payment_85) ? ($request->payment_85) : 0;
					// $slab_payment->payment_86 = isset($request->payment_86) ? ($request->payment_86) : 0;
					// $slab_payment->payment_87 = isset($request->payment_87) ? ($request->payment_87) : 0;
					// $slab_payment->payment_88 = isset($request->payment_88) ? ($request->payment_88) : 0;
					// $slab_payment->payment_89 = isset($request->payment_89) ? ($request->payment_89) : 0;
					// $slab_payment->payment_90 = isset($request->payment_90) ? ($request->payment_90) : 0;
					// $slab_payment->payment_91 = isset($request->payment_91) ? ($request->payment_91) : 0;
					// $slab_payment->payment_92 = isset($request->payment_92) ? ($request->payment_92) : 0;
					// $slab_payment->payment_93 = isset($request->payment_93) ? ($request->payment_93) : 0;
					// $slab_payment->payment_94 = isset($request->payment_94) ? ($request->payment_94) : 0;
					// $slab_payment->payment_95 = isset($request->payment_95) ? ($request->payment_95) : 0;
					// $slab_payment->payment_96 = isset($request->payment_96) ? ($request->payment_96) : 0;
					// $slab_payment->payment_97 = isset($request->payment_97) ? ($request->payment_97) : 0;
					// $slab_payment->payment_98 = isset($request->payment_98) ? ($request->payment_98) : 0;
					// $slab_payment->payment_99 = isset($request->payment_99) ? ($request->payment_99) : 0;
					// $slab_payment->payment_100 = isset($request->payment_100) ? ($request->payment_100) : 0;
                    $slab_payment->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($slab_payment->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_slab_payment_updated');
                        $response['data'] = $slab_payment;
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

    public function get_slab_payments(Request $request) { 
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $list_slab_payments = SlabPayment::where($cond)->orderBy('id','desc')->get();
            foreach($list_slab_payments as $slab_payment)
			{
                $slab_payment->tenant_info = isset($slab_payment->tenant_det) ? ($slab_payment->tenant_det) : [];
                $slab_payment->branch_info = isset($slab_payment->branch_det) ? ($slab_payment->branch_det) : [];
                $slab_payment->scheme_info = isset($slab_payment->scheme_det) ? ($slab_payment->scheme_det) : [];
                $slab_payment->slab_due_info = isset($slab_payment->slab_due_det) ? ($slab_payment->slab_due_det) : [];
                $slab_payment->no_of_months = isset($slab_payment->scheme_det) ? ($slab_payment->scheme_det->no_of_months) : 0;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_slab_payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_slab_payments(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $slab_payment_list = SlabPayment::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $slab_payment_list = SlabPayment::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($slab_payment_list as $slab_payment) {
                $slab_payment->sno = $i;
                $slab_payment->tenant_info = isset($slab_payment->tenant_det) ? ($slab_payment->tenant_det) : [];
                $slab_payment->branch_info = isset($slab_payment->branch_det) ? ($slab_payment->branch_det) : [];
                $slab_payment->scheme_info = isset($slab_payment->scheme_det) ? ($slab_payment->scheme_det) : [];
                $slab_payment->slab_due_info = isset($slab_payment->slab_due_det) ? ($slab_payment->slab_due_det) : [];               
			   $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $slab_payment_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_slab_payment(Request $request) {
        try {
            if(isset($request->id)) {
                $slab_payments = SlabPayment::find($request->id); 
                $slab_payments->deleted_by = $request->deleted_by;
                $slab_payments->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($slab_payments->save()) {
                    $slab_payments->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_slab_payment_deleted');
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
                $slab_payments = SlabPayment::find($request->id); 
                $slab_payments->status = ($slab_payments->status) ? 0 : 1;
                if($slab_payments->save()) {
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
