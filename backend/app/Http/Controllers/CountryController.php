<?php
namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Validator;

class CountryController extends Controller
{

    public function store_country(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'country_name'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                 
                $country=new Country(); 
                $country->country_name = isset($request->country_name) ? ($request->country_name) : '0';
                $country->remarks = isset($request->remarks) ? ($request->remarks) : 0;
                $country->status = isset($request->status) ? ($request->status) : 1;
                if($country->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_country_added');
                    $response['data']=$country;
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
	
	public function update_country(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_name'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                   
                    $country = Country::find($request->id); 
                    $country->country_name = isset($request->country_name) ? ($request->country_name) : '0';
                    $country->remarks = isset($request->remarks) ? ($request->remarks) : 0;
                    $country->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($country->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_country_updated');
                        $response['data'] =$country;
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
    
    public function get_countries(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $countries = Country::where($cond)->orderBy('id','desc')->get();
            foreach($countries as $country) {
                $country->tenant_info = isset($country->tenant_det) ? ($country->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $countries;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_countries(Request $request) {
        try { 
            $countries = Country::all();
            $i=1;
            foreach($countries as $country) {
                $country->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $countries;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_countries(Request $request) {
        try {
            if(isset($request->id)) {
                $country = Country::find($request->id); 
                $country->deleted_by = $request->deleted_by;
                $country->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($country->save()) {
                    $country->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_country_deleted');
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
