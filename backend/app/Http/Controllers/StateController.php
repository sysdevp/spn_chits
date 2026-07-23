<?php
namespace App\Http\Controllers;

use App\Models\State;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;

use Validator;

class StateController extends Controller
{
     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_state(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'state_name'=>'required',
            'country_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $state=new State(); 
                $state->state_name = isset($request->state_name) ? ($request->state_name) : '';
                $state->country_id = isset($request->country_id) ? ($request->country_id) : '';
                $state->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $state->status = isset($request->status) ? ($request->status) : 1;
                if($state->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_state_added');
                    $response['data']=$state;
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
    
    public function update_state(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'state_name'=>'required',
            'country_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $state = State::find($request->id); 
                    $state->state_name = isset($request->state_name) ? ($request->state_name) : '';
                    $state->country_id = isset($request->country_id) ? ($request->country_id) : '';
                    $state->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($state->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_state_updated');
                        $response['data'] =$state;
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

    public function get_states(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $states = State::where($cond)->orderBy('id','desc')->get();
            foreach($states as $state) {
                $state->country_info = isset($state->country_det) ? ($state->country_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $states;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_states(Request $request) {
        try { 
            $states = State::all();
            $i=1;
            foreach($states as $state) {
                $state->sno = $i;
                $state->country_name = isset($state->country_det) ? $state->country_det->country_name : [];
                $custdetails=Customer::where('state_id','=',$state->id)->get();
                $empdetails=Employee::where('state_id','=',$state->id)->get();
                $state->is_delete=0;
                if(count($custdetails)>0 || count($empdetails)>0){
                    $state->is_delete=1;
                }
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $states;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_state(Request $request) {
        try {
            if(isset($request->id)) {
                $state = State::find($request->id); 
                $state->deleted_by = $request->deleted_by;
                $state->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($state->save()) {
                    $state->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_state_deleted');
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
