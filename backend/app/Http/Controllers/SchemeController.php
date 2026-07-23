<?php
namespace App\Http\Controllers;

use App\Models\Scheme;
use App\Models\Group;
use Illuminate\Http\Request;
use Validator;

class SchemeController extends Controller
{
    public function store_scheme(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'chit_value' => 'required',
            'no_of_months' => 'required',
            'members' => 'required',
            'monthly_due' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $scheme = new Scheme();
				$scheme->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $scheme->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
                $scheme->no_of_months = isset($request->no_of_months) ? ($request->no_of_months) : 0;
                $scheme->members = isset($request->members) ? ($request->members) : 0;
                $scheme->register_fees = isset($request->register_fees) ? ($request->register_fees) : '0';
                $scheme->enrollment_fees = isset($request->enrollment_fees) ? ($request->enrollment_fees) : '0';
                $scheme->chit_letter = isset($request->chit_letter) ? ($request->chit_letter) : '0';
                $scheme->short_term = isset($request->short_term) ? ($request->short_term) : '0';
                $scheme->monthly_due = isset($request->monthly_due) ? ($request->monthly_due) : 0;
                $scheme->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $scheme->status = isset($request->status) ? ($request->status) : 1;
                if($scheme->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_scheme_added');
                    $response['data'] = $scheme;
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
                $response['statuse'] = $e;
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

    public function update_scheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'tenant_id' => 'required',
            'chit_value' => 'required',
            'no_of_months' => 'required',
            'members' => 'required',
            'monthly_due' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {   
					$scheme = Scheme::find($request->id);
					$scheme->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$scheme->chit_value = isset($request->chit_value) ? ($request->chit_value) : 0;
					$scheme->no_of_months = isset($request->no_of_months) ? ($request->no_of_months) : 0;
					$scheme->members = isset($request->members) ? ($request->members) : 0;
					$scheme->register_fees = isset($request->register_fees) ? ($request->register_fees) : '0';
					$scheme->enrollment_fees = isset($request->enrollment_fees) ? ($request->enrollment_fees) : '0';
					$scheme->chit_letter = isset($request->chit_letter) ? ($request->chit_letter) : '0';
					$scheme->short_term = isset($request->short_term) ? ($request->short_term) : '0';
					$scheme->monthly_due = isset($request->monthly_due) ? ($request->monthly_due) : 0;
					$scheme->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0; 
					if($scheme->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_scheme_updated');
						$response['data'] = $scheme;
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
			   
	public function get_schemes(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $schemes = Scheme::where($cond)->orderBy('id','desc')->get();
            foreach($schemes as $scheme) {
                $scheme->tenant_info = isset($scheme->tenant_det) ? ($scheme->tenant_det) : [];
                $scheme->branch_info = ($scheme->branch_det) ? ($scheme->branch_det) : [];
                $scheme->chit_value = isset($scheme->chit_value) ? round($scheme->chit_value) : '';
                $scheme->scheme_format = isset($scheme->chit_value) ? (round($scheme->chit_value).'('.$scheme->no_of_months.' M) / ' . $scheme->chit_letter) : '';
                $scheme->monthly_due = isset($scheme->monthly_due) ? round($scheme->monthly_due) : '';
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $schemes;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	 
	public function list_schemes(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $schemes = Scheme::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $schemes = Scheme::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($schemes as $scheme) {
                $scheme->sno = $i;
                $scheme->chit_value = isset($scheme->chit_value) ? round($scheme->chit_value) : '';
                $scheme->scheme_format = isset($scheme->chit_value) ? (round($scheme->chit_value).'('.$scheme->no_of_months.' M) / ' . $scheme->chit_letter) : '';
                $groupdetails=Group::where('scheme_id','=',$scheme->id)->get();
                $scheme->is_delete=0;
                if(count($groupdetails)>0){
                    $scheme->is_delete=1;
                }
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $schemes;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function delete_scheme(Request $request) {
		try {
            if(isset($request->id)) {
                $scheme = Scheme::find($request->id); 
                $scheme->deleted_by = $request->deleted_by;
                $scheme->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($scheme->save()) {
                    $scheme->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_scheme_deleted');
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
                $scheme = Scheme::find($request->id); 
                $scheme->status = ($scheme->status) ? 0 : 1;
                if($scheme->save()) {
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
