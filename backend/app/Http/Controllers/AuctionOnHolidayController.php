<?php
namespace App\Http\Controllers;

use App\Models\AuctionOnHoliday;
use Illuminate\Http\Request;
use Validator;

class AuctionOnHolidayController extends Controller
{
    public function store_holiday(Request $request)
    {
        $validator=Validator::make($request->all(),[
    		'tenant_id' => 'required',
            'branch_id'=>'required',
            'holiday_date'=>'required',
            'event_name'=>'required'
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $holiday=new AuctionOnHoliday(); 
                $holiday->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $holiday->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                if(isset($request->holiday_date)) {
                    $s_date = ($request->holiday_date['year']).'-'.sprintf('%02d',($request->holiday_date['month'])).'-'.sprintf('%02d',($request->holiday_date['day'])); 
                    $holiday->holiday_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $holiday->holiday_date = '0000-00-00';
                }
                $holiday->event_name = isset($request->event_name) ? ($request->event_name) : 0;   
                $holiday->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $holiday->status = isset($request->status) ? ($request->status) : 1;
                if($holiday->save()){

                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_auction_on_holiday_added');
                    $response['data']=$holiday;
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
       
    public function update_holiday(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'branch_id'=>'required',
		   'tenant_id' => 'required',
           'holiday_date'=>'required',
           'event_name'=>'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $holiday = AuctionOnHoliday::find($request->id); 
                    $holiday->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$holiday->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    if(isset($request->holiday_date)) {
                        $s_date = ($request->holiday_date['year']).'-'.sprintf('%02d',($request->holiday_date['month'])).'-'.sprintf('%02d',($request->holiday_date['day'])); 
                        $holiday->holiday_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $holiday->event_name = isset($request->event_name) ? ($request->event_name) : 0;   
                    $holiday->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($holiday->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_auction_on_holiday_updated');
                        $response['data'] =$holiday;
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

    public function get_holidays(Request $request) {
        try {
            $cond = $request->conditions; 
            $holidays = AuctionOnHoliday::where($cond)->orderBy('id','desc')->get();
            if($holidays) {
                foreach($holidays as $holiday) {
                    $holiday->branch_info = ($holiday->branch_det) ? ($holiday->branch_det) : [];
					$holiday->tenant_info = isset($holiday->tenant_det) ? ($holiday->tenant_det) : [];
                    $holiday->holiday_date = date('d/m/Y',strtotime($holiday->holiday_date)); 
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $holidays;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_holidays(Request $request) {
        try { 

            if(isset($request->tenant_id)) {
                $holidays = AuctionOnHoliday::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $holidays = AuctionOnHoliday::orderBy('id','desc')->get();
            }
             $i=1;
            foreach($holidays as $holiday) {
                $holiday->sno = $i;
                $holiday->branch_info = ($holiday->branch_det) ? ($holiday->branch_det) : [];
                $holiday->tenant_info = isset($holiday->tenant_det) ? ($holiday->tenant_det) : [];
                $holiday->holiday_date = date('d/m/Y',strtotime($holiday->holiday_date)); 
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $holidays;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_holiday(Request $request) {
        try { 
            if(isset($request->id)) { 
                $holiday = AuctionOnHoliday::find($request->id); 
                $holiday->deleted_by = $request->deleted_by; 
                $holiday->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '0';
                if($holiday->save()) { 
                    $holiday->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_auction_on_holiday_deleted');
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
                $holiday_det = AuctionOnHoliday::find($request->id); 
                $holiday_det->status = ($holiday_det->status) ? 0 : 1;
                if($holiday_det->save()) {
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

    Public function auction_day_details(Request $request)
    {
        try
        {
            $auction_date = "0000-00-00";
            if(isset($request->auction_date)) {
                $s_date = ($request->auction_date['year']).'-'.sprintf('%02d',($request->auction_date['month'])).'-'.sprintf('%02d',($request->auction_date['day'])); 
                $auction_date = date('Y-m-d',strtotime($s_date)); 
            }
            //$auction_date = $request->auction_date;
            $holiday_detatils = AuctionOnHoliday::where(array('holiday_date'=>$auction_date,'tenant_id'=>$request->tenant_id))->get();
            if(count($holiday_detatils)>0)
            {
                $event_name = $holiday_detatils[0]->event_name;
                $response['status'] = "Success";
                $response['msg'] = "Today is a ".$event_name;
                return response()->json($response, 200);
            }
            else
            {
                $response['status'] = "Success";
                $response['msg'] = "";
                return response()->json($response, 200);
            }
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
