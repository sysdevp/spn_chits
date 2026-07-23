<?php
namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Validator;
use DB;

class DistrictController extends Controller
{
    public function store_district(Request $request) {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required',
            'district_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 

                $district = new District();
                $district->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                $district->district_name = isset($request->district_name) ? ($request->district_name) : '';
                $district->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $district->status = isset($request->status) ? ($request->status) : 1;
                if($district->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_district_added');
                    $response['data'] = $district;
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

    public function update_district(Request $request) {
        $validator = Validator::make($request->all(), [
            'state_id' => 'required',
            'district_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $district = District::find($request->id); 
                    $district->state_id = isset($request->state_id) ? ($request->state_id) : 0; 
                    $district->district_name = isset($request->district_name) ? ($request->district_name) : '';
                    $district->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0; 
                    if($district->save()) { 
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_district_updated');
                        $response['data'] = $district;
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

    public function get_districts(Request $request) {
        try {
            //id,state_id
            $cond = $request->conditions; 
            $districts = District::where($cond)->orderBy('id','desc')->get();
            foreach($districts as $district) {
                $district->state_info = isset($district->state_det) ? ($district->state_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $districts;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_districts(Request $request) {
        try { 
            
            $districts = District::orderBy('id','desc')->get();
            $i=1;
            $districtdetails=[];
            foreach($districts as $district) {
                $district->state_name = isset($district->state_det) ? $district->state_det->state_name : [];
                if(!empty($district->state_name)){
                    $custdetails=Customer::where('district_id','=',$district->id)->get();
                    $empdetails=Employee::where('district_id','=',$district->id)->get();
                    $is_delete=0;
                    if(count($custdetails)>0 || count($empdetails)>0){
                        $is_delete=1;
                    }
                    $districtdetails[]=[
                        'id'=>$district->id,
                        'state_id'=>$district->state_id,
                        'district_name'=>$district->district_name,
                        'created_at'=>$district->created_at,
                        'created_by'=>$district->created_by,
                        'updated_at'=>$district->updated_at,
                        'updated_by'=>$district->updated_by,
                        'deleted_by'=>$district->deleted_by,
                        'deleted_at'=>$district->deleted_at,
                        'deletion_remark'=>$district->deletion_remark,
                        'status'=>$district->status,
                        'state_name'=>$district->state_name,
                        'is_delete'=>$is_delete,
                        'sno'=>$i,
                    ];
                    $i++;
                }
            }
            
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $districtdetails;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_district(Request $request) {
        try {
            if(isset($request->id)) {
                $district = District::find($request->id); 
                $district->deleted_by = $request->deleted_by;
                $district->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($district->save()) {
                    $district->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_district_deleted');
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
