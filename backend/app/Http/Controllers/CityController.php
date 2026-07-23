<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Gift;
use App\Models\Customer;
use App\Models\Scheme;
use App\Models\Employee;
use Illuminate\Http\Request;
use Validator;

class CityController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_city(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'city_name'=>'required',
            'pincode'=>'required',
            'district_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $city = City::where(array('pincode'=>$request->pincode))->get();       
                if(count($city)>0)
                {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.pincode_already_exist');
                    return response()->json($response, 200);
                } 
                $city=new City(); 
                $city->city_name = isset($request->city_name) ? ($request->city_name) : '0';
                $city->state_id = 0;
                $city->pincode = isset($request->pincode) ? ($request->pincode) : 0;
                $city->district_id = isset($request->district_id) ? ($request->district_id) : 0;   
                $city->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $city->status = isset($request->status) ? ($request->status) : 1;
                $city->updated_by = 0;
                // print_r($city);exit;
                if($city->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_city_added');
                    $response['data']=$city;
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
       
    public function update_city(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_name'=>'required',
            'pincode'=>'required',
            'district_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $city = City::where(array('pincode'=>$request->pincode))->whereNotIn('id', [$request->id])->get();       
                    if(count($city)>0)
                    {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.pincode_already_exist');
                        return response()->json($response, 200);
                    } 
                    $city = City::find($request->id); 
                    $city->city_name = isset($request->city_name) ? ($request->city_name) : '0';
                    $city->pincode = isset($request->pincode) ? ($request->pincode) : '0';
                    $city->district_id = isset($request->district_id) ? ($request->district_id) : '0';
                    $city->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($city->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_city_updated');
                        $response['data'] =$city;
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

    public function get_cities(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $cities = City::where($cond)->orderBy('id','desc')->get();
            foreach($cities as $city) {
                $city->district_info = isset($city->district_det) ? ($city->district_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $cities;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function list_cities(Request $request) {
        try { 
		
            $cities = City::all();
            $i=1;
			
            foreach($cities as $city) {
                $city->sno = $i;
                $city->district_info = isset($city->district_det) ? $city->district_det : [];
                $custdetails=Customer::where('city_id','=',$city->id)->get();
                $empdetails=Employee::where('city_id','=',$city->id)->get();
                $city->is_delete=0;
                if(count($custdetails)>0 || count($empdetails)>0){
                    $city->is_delete=1;
                }
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $cities;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_city(Request $request) {
        try {
            if(isset($request->id)) {
                $city = City::find($request->id); 
                $city->deleted_by = $request->deleted_by;
                $city->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($city->save()) {
                    $city->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_city_deleted');
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
                $city = City::find($request->id); 
                $city->status = ($city->status) ? 0 : 1;
                if($city->save()) {
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
	
	public function store_gift(Request $request)
    {
        
            try { 
               
                $city=new Gift(); 
                $city->gift_name = isset($request->gift_name) ? ($request->gift_name) : '0';
              
                $city->price = isset($request->price) ? ($request->price) : 0;
                $city->quantity = isset($request->quantity) ? ($request->quantity) : 0;
                $city->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;   
                $city->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $city->status = isset($request->status) ? ($request->status) : 1;
                $city->updated_by = 0;
                
                if($city->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_gift_added');
                    $response['data']=$city;
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
	
	public function list_gifts(Request $request) {
        try { 
		
            $cities = Gift::all();
            $i=1;
            foreach($cities as $city) {
                $city->sno = $i;
                $chit_value = Scheme::where('id',$city->scheme_id)->pluck('chit_value');
				$city->chit_value = $chit_value[0];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $cities;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function get_gifts(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $cities = Gift::where($cond)->orderBy('id','desc')->get();
           
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $cities;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function delete_gift(Request $request) {
        try {
            if(isset($request->id)) {
                $city = Gift::find($request->id); 
                $city->deleted_by = $request->deleted_by;
                $city->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($city->save()) { 
                    $city->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_gift_deleted');
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
