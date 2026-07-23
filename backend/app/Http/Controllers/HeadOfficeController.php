<?php
namespace App\Http\Controllers;

use App\Models\HeadOffice;
use Illuminate\Http\Request;
use Validator;
use Config;

class HeadOfficeController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_headoffice(Request $request)
    {
		  
	   $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'head_name'=>'required',
            //'head_code'=>'required',
            'email_id'=>'required'
      
	  
        ]); 
		
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                     
                $headoffice=new HeadOffice(); 
                $headoffice->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $headoffice->head_name = isset($request->head_name) ? ($request->head_name) : '0';
                $headoffice->email_id = isset($request->email_id) ? ($request->email_id) : '0';
                $headoffice->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
			     $headoffice->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
			     $headoffice->district_id = isset($request->district_id) ? ($request->district_id) : '0';
			     $headoffice->city_id = isset($request->city_id) ? ($request->city_id) : '0';
			     $headoffice->pincode = isset($request->pincode) ? ($request->pincode) : '0';
			     $headoffice->landmark = isset($request->landmark) ? ($request->landmark) : '0';
                $headoffice->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '0';
                $headoffice->phone_no = isset($request->phone_no) ? ($request->phone_no) : '0';
                $headoffice->state_id = isset($request->state_id) ? ($request->state_id) : '0';
                $headoffice->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                $headoffice->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $headoffice->status = isset($request->status) ? ($request->status) : 1;
				
                // print_r(HeadOffice::orderBy('id','desc')->get());exit;
                if($headoffice->save()){
                // print_r($headoffice);exit;
                    $head_code = Config::get('constants.auto_codes.head_office').sprintf('%05d', $headoffice->id);
                    $headoffice->head_code = $head_code;
                    if($headoffice->save()) {
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_head_office_added');
                        $response['data']=$headoffice;
                        return response()->json($response,200);
                    }
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }
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

    public function update_headoffice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'head_name'=>'required',
            'head_code'=>'required',
            'email_id'=>'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $headoffice = HeadOffice::find($request->id); 
                    $headoffice->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $headoffice->head_name = isset($request->head_name) ? ($request->head_name) : '0';
                    $headoffice->head_code = isset($request->head_code) ? ($request->head_code) : '0';
                    $headoffice->email_id = isset($request->email_id) ? ($request->email_id) : '0';
    				$headoffice->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
    				$headoffice->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
    				$headoffice->district_id = isset($request->district_id) ? ($request->district_id) : '0';
    				$headoffice->city_id = isset($request->city_id) ? ($request->city_id) : '0';
    				$headoffice->pincode = isset($request->pincode) ? ($request->pincode) : '0';
    				$headoffice->landmark = isset($request->landmark) ? ($request->landmark) : '0';
    				$headoffice->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '0';
                    $headoffice->phone_no = isset($request->phone_no) ? ($request->phone_no) : '0';
                    $headoffice->state_id = isset($request->state_id) ? ($request->state_id) : '0';
                    $headoffice->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                    $headoffice->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($headoffice->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_head_office_updated');
                        $response['data'] =$headoffice;
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

    public function get_headoffice(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $headoffices = HeadOffice::where($cond)->orderBy('id','desc')->get();
            foreach($headoffices as $headoffice) {
				
                $headoffice->tenant_info = isset($headoffice->tenant_det) ? ($headoffice->tenant_det) : [];
                $headoffice->state_info = isset($headoffice->state_det) ? ($headoffice->state_det) : [] ;
				$headoffice->district_info = isset($headoffice->district_det) ? ($headoffice->district_det) : [] ;
				$headoffice->city_info = isset($headoffice->city_det) ? ($headoffice->city_det) : [] ;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $headoffices;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_headoffice(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $headoffices = HeadOffice::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $headoffices = HeadOffice::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($headoffices as $headoffice) {
                $headoffice->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $headoffices;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_headoffice(Request $request) {
        try {
            if(isset($request->id)) {
                $headoffice = HeadOffice::find($request->id); 
                $headoffice->deleted_by = $request->deleted_by;
				$headoffice->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($headoffice->save()) {
                    $headoffice->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_head_office_deleted');
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
                $headoffice = HeadOffice::find($request->id); 
                $headoffice->status = ($headoffice->status) ? 0 : 1;
                if($headoffice->save()) {
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
