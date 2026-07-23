<?php
namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Validator;
use Config;

class DesignationController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_designation(Request $request)
    {
         $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'designation_name'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $designation=new Designation(); 
                $designation->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
                $designation->designation_name = isset($request->designation_name) ? ($request->designation_name) : '';
                $designation->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $designation->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $designation->status = isset($request->status) ? ($request->status) : 1;
                if($designation->save()){
                    $code = Config::get('constants.auto_codes.designation').sprintf('%05d', $designation->id);
                    $designation->code = $code;
                    $designation->save();
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_designation_added');
                    $response['data']=$designation;
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
    }

    public function update_designation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'designation_name'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $designation = Designation::find($request->id); 
                    $designation->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
                    $designation->designation_name = isset($request->designation_name) ? ($request->designation_name) : '';
                    $designation->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $designation->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($designation->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_designation_updated');
                        $response['data'] =$designation;
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

    public function get_designation(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $designations = Designation::where($cond)->orderBy('id','desc')->get();
            foreach($designations as $designation) {
                $designation->tenant_info = isset($designation->tenant_det) ? ($designation->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $designations;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_designations(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $designations = Designation::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $designations = Designation::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($designations as $designation) {
                $designation->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $designations;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_designations(Request $request) {
        try {
            if(isset($request->id)) {
                $designation = Designation::find($request->id); 
                $designation->deleted_by = $request->deleted_by;
                $designation->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($designation->save()) {
                    $designation->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_designation_deleted');
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
                $designation = Designation::find($request->id); 
                $designation->status = ($designation->status) ? 0 : 1;
                if($designation->save()) {
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
