<?php
namespace App\Http\Controllers;

use App\Models\BusinessAgent;
use Illuminate\Http\Request;
use Validator;

class BusinessAgentController extends Controller
{
  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store_business_agent(Request $request)
  {    
    $validator=Validator::make($request->all(),[
      'tenant_id'=>'required',
      'branch_id'=>'required',
      'first_name'=>'required',
    ]); 
    if($validator->fails()){
      $response['status']="Error";
      $response['msg']=$validator->error()->first();
      return response()->json($response,401);
    }
    else {
      try { 
        $businessagent=new BusinessAgent(); 
        $businessagent->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
        $businessagent->branch_id = isset($request->branch_id) ? ($request->branch_id) : '0';
        $businessagent->saluation = isset($request->saluation) ? ($request->saluation) : '0';
        $businessagent->first_name = isset($request->first_name) ? ($request->first_name) : '0';
        $businessagent->last_name = isset($request->last_name) ? ($request->last_name) : '0';
        $businessagent->initial = isset($request->initial) ? ($request->initial) : '0';
        $businessagent->photo = isset($request->photo) ? ($request->photo) : '0';
        if(isset($request->dob)) {
          $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
          $businessagent->dob = date('Y-m-d',strtotime($s_date));
        }
        else {
          $businessagent->dob = '0000-00-00';
        }
        $businessagent->gender = isset($request->gender) ? ($request->gender) : 0;
        $businessagent->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
        $businessagent->phone_no = isset($request->phone_no) ? ($request->phone_no) : 0;
        $businessagent->email_id = isset($request->email_id) ? ($request->email_id) : '0';
        $businessagent->father_name = isset($request->father_name) ? ($request->father_name) : '0';
        $businessagent->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '0';
        $businessagent->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : 0;
        $businessagent->pan_no = isset($request->pan_no) ? ($request->pan_no) : 0;
        $businessagent->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : 0;
        $businessagent->license_no = isset($request->license_no) ? ($request->license_no) : 0;              
        if(isset($request->doj)) {
          $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
          $businessagent->doj = date('Y-m-d',strtotime($s_date));
        }
        else {
          $businessagent->doj = '0000-00-00';
        }
        $businessagent->door_no = isset($request->door_no) ? ($request->door_no) : 0;
        $businessagent->building_name = isset($request->building_name) ? ($request->building_name) : '0';
        $businessagent->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
        $businessagent->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
        $businessagent->land_mark = isset($request->land_mark) ? ($request->land_mark) : '0';
        $businessagent->state_id = isset($request->state_id) ? ($request->state_id) : '0';
        $businessagent->district_id = isset($request->district_id) ? ($request->district_id) : 0;
        $businessagent->city_id = isset($request->city_id) ? ($request->city_id) : 0;
        $businessagent->pincode = isset($request->pincode) ? ($request->pincode) : 0;
        $businessagent->designation_id = isset($request->designation_id) ? ($request->designation_id) : 0;
        $businessagent->role_id = isset($request->role_id) ? ($request->role_id) : 0;
        $businessagent->esi = isset($request->esi) ? ($request->esi) : 0;
        $businessagent->blood_group = isset($request->blood_group) ? ($request->blood_group) : 0;
        $businessagent->target_new_business = isset($request->target_new_business) ? ($request->target_new_business) : '0';
        $businessagent->target_collection = isset($request->target_collection) ? ($request->target_collection) : '0';
        $businessagent->qualification = isset($request->qualification) ? ($request->qualification) : '0';
        $businessagent->extra_qualification = isset($request->extra_qualification) ? ($request->extra_qualification) : '0';
        $businessagent->previous_company_details = isset($request->previous_company_details) ? ($request->previous_company_details) : '0';
        $businessagent->experience = isset($request->experience) ? ($request->experience) : 0;
        if(isset($request->resignation_date)) {
          $s_date = ($request->resignation_date['year']).'-'.sprintf('%02d',($request->resignation_date['month'])).'-'.sprintf('%02d',($request->resignation_date['day'])); 
          $businessagent->resignation_date = date('Y-m-d',strtotime($s_date));
        }
        else {
          $businessagent->resignation_date = '0000-00-00';
        }
        $businessagent->reason_for_resignation = isset($request->reason_for_resignation) ? ($request->reason_for_resignation) : '0';
        $businessagent->verification_on_call_by = isset($request->verification_on_call_by) ? ($request->verification_on_call_by) : '0';
        $businessagent->holder_name = isset($request->holder_name) ? ($request->holder_name) : '0';
        $businessagent->account_no = isset($request->account_no) ? ($request->account_no) : 0;
        $businessagent->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0;
        $businessagent->ifsc_code = isset($request->ifsc_code) ? ($request->ifsc_code) : 0;
        $businessagent->pf = isset($request->pf) ? ($request->pf) : 0;
        $businessagent->net_salary = isset($request->net_salary) ? ($request->net_salary) : 0;
        $businessagent->monthly_income = isset($request->monthly_income) ? ($request->monthly_income) : 0;
        $businessagent->hra = isset($request->hra) ? ($request->hra) : 0;
        $businessagent->da = isset($request->da) ? ($request->da) : 0;
        $businessagent->deduction = isset($request->deduction) ? ($request->deduction) : 0;
        $businessagent->other = isset($request->other) ? ($request->other) : 0;
        $businessagent->enrollment_fee = isset($request->enrollment_fee) ? ($request->enrollment_fee) : 0;
        $businessagent->discount_fee = isset($request->discount_fee) ? ($request->discount_fee) : 0;
        $businessagent->commission = isset($request->commission) ? ($request->commission) : 0;
        $businessagent->incentive = isset($request->incentive) ? ($request->incentive) : 0;
        $businessagent->created_by = isset($request->created_by) ? ($request->created_by) : 0;
        $businessagent->status = isset($request->status) ? ($request->status) : 1;
        if($businessagent->save()){
          $response['status']="Success";
          $response['msg']=\Lang::get('api.success_business_agent_added');
          $response['data']=$businessagent;
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

  public function update_business_agent(Request $request)
  {
    $validator = Validator::make($request->all(), [
     'tenant_id'=>'required',
     'branch_id'=>'required',
     'first_name'=>'required',
    ]);
    if ($validator->fails()) { 
      $response['status'] = "Error";
      $response['msg'] = $validator->errors()->first();
      return response()->json($response, 200);
    }
    else {
      try {
        if(isset($request->id)) {
          $businessagent = BusinessAgent::find($request->id);
          $businessagent->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
          $businessagent->branch_id = isset($request->branch_id) ? ($request->branch_id) : '0';
          $businessagent->saluation = isset($request->saluation) ? ($request->saluation) : '0';
          $businessagent->first_name = isset($request->first_name) ? ($request->first_name) : '0';
          $businessagent->last_name = isset($request->last_name) ? ($request->last_name) : '0';
          $businessagent->initial = isset($request->initial) ? ($request->initial) : '0';
          $businessagent->photo = isset($request->photo) ? ($request->photo) : '0';
          if(isset($request->dob)) {
            $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
            $businessagent->dob = date('Y-m-d',strtotime($s_date));
          }
          else {
            $businessagent->dob = '0000-00-00';
          }
          $businessagent->gender = isset($request->gender) ? ($request->gender) : 0;
          $businessagent->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
          $businessagent->phone_no = isset($request->phone_no) ? ($request->phone_no) : 0;
          $businessagent->email_id = isset($request->email_id) ? ($request->email_id) : '0';
          $businessagent->father_name = isset($request->father_name) ? ($request->father_name) : '0';
          $businessagent->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '0';
          $businessagent->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : 0;
          $businessagent->pan_no = isset($request->pan_no) ? ($request->pan_no) : 0;
          $businessagent->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : 0;
          $businessagent->license_no = isset($request->license_no) ? ($request->license_no) : 0;              
          if(isset($request->doj)) {
            $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
            $businessagent->doj = date('Y-m-d',strtotime($s_date));
          }
          else {
            $businessagent->doj = '0000-00-00';
          }
          $businessagent->door_no = isset($request->door_no) ? ($request->door_no) : 0;
          $businessagent->building_name = isset($request->building_name) ? ($request->building_name) : '0';
          $businessagent->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
          $businessagent->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
          $businessagent->land_mark = isset($request->land_mark) ? ($request->land_mark) : '0';
          $businessagent->state_id = isset($request->state_id) ? ($request->state_id) : '0';
          $businessagent->district_id = isset($request->district_id) ? ($request->district_id) : 0;
          $businessagent->city_id = isset($request->city_id) ? ($request->city_id) : 0;
          $businessagent->pincode = isset($request->pincode) ? ($request->pincode) : 0;
          $businessagent->designation_id = isset($request->designation_id) ? ($request->designation_id) : 0;
          $businessagent->role_id = isset($request->role_id) ? ($request->role_id) : 0;
          $businessagent->esi = isset($request->esi) ? ($request->esi) : 0;
          $businessagent->blood_group = isset($request->blood_group) ? ($request->blood_group) : 0;
          $businessagent->target_new_business = isset($request->target_new_business) ? ($request->target_new_business) : '0';
          $businessagent->target_collection = isset($request->target_collection) ? ($request->target_collection) : '0';
          $businessagent->qualification = isset($request->qualification) ? ($request->qualification) : '0';
          $businessagent->extra_qualification = isset($request->extra_qualification) ? ($request->extra_qualification) : '0';
          $businessagent->previous_company_details = isset($request->previous_company_details) ? ($request->previous_company_details) : '0';
          $businessagent->experience = isset($request->experience) ? ($request->experience) : 0;
          if(isset($request->resignation_date)) {
            $s_date = ($request->resignation_date['year']).'-'.sprintf('%02d',($request->resignation_date['month'])).'-'.sprintf('%02d',($request->resignation_date['day'])); 
            $businessagent->resignation_date = date('Y-m-d',strtotime($s_date));
          }
          else {
            $businessagent->resignation_date = '0000-00-00';
          }
          $businessagent->reason_for_resignation = isset($request->reason_for_resignation) ? ($request->reason_for_resignation) : '0';
          $businessagent->verification_on_call_by = isset($request->verification_on_call_by) ? ($request->verification_on_call_by) : '0';
          $businessagent->holder_name = isset($request->holder_name) ? ($request->holder_name) : '0';
          $businessagent->account_no = isset($request->account_no) ? ($request->account_no) : 0;
          $businessagent->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0;
          $businessagent->ifsc_code = isset($request->ifsc_code) ? ($request->ifsc_code) : 0;
          $businessagent->pf = isset($request->pf) ? ($request->pf) : 0;
          $businessagent->net_salary = isset($request->net_salary) ? ($request->net_salary) : 0;
          $businessagent->monthly_income = isset($request->monthly_income) ? ($request->monthly_income) : 0;
          $businessagent->hra = isset($request->hra) ? ($request->hra) : 0;
          $businessagent->da = isset($request->da) ? ($request->da) : 0;
          $businessagent->deduction = isset($request->deduction) ? ($request->deduction) : 0;
          $businessagent->other = isset($request->other) ? ($request->other) : 0;
          $businessagent->enrollment_fee = isset($request->enrollment_fee) ? ($request->enrollment_fee) : 0;
          $businessagent->discount_fee = isset($request->discount_fee) ? ($request->discount_fee) : 0;
          $businessagent->commission = isset($request->commission) ? ($request->commission) : 0;
          $businessagent->incentive = isset($request->incentive) ? ($request->incentive) : 0;
          $businessagent->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
          if($businessagent->save()) {
              $response['status'] = "Success";
              $response['msg'] = \Lang::get('api.success_business_agent_updated');
              $response['data'] =$businessagent;
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
  
  public function get_business_agent(Request $request) {
    try {
      //id,tenant_id
      $cond = $request->conditions; 
      $businessagents = BusinessAgent::where($cond)->orderBy('id','desc')->get();
      if($businessagents) {
        foreach($businessagents as $businessagent) {
          $businessagent->tenant_info = isset($businessagent->tenant_det) ? ($businessagent->tenant_det) : [];
          $businessagent->branch_info = isset($businessagent->branch_det) ? ($businessagent->branch_det) : [] ;
          $businessagent->state_info = isset($businessagent->state_det) ? ($businessagent->state_det) : [];
          $businessagent->district_info = isset($businessagent->district_det) ? ($businessagent->district_det) : [];
          $businessagent->city_info = isset($businessagent->city_det) ? ($businessagent->city_det) : [];
          $businessagent->designation_info = isset($businessagent->designation_det) ? ($businessagent->designation_det) : [];
          $businessagent->role_info = isset($businessagent->role_det) ? ($businessagent->role_det) : [];
          $businessagent->bank_info = isset($businessagent->bank_det) ? ($businessagent->bank_det) : [];
        }
        $response['status'] = 'Success';
        $response['msg'] = '';
        $response['data'] = $businessagents;
        return response()->json($response,200);
      }
    }
    catch(\Exception $e) { 
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      $response['msg'] = $e;
      return response()->json($response, 401);
    }
  }

  public function list_business_agent(Request $request) {
    try { 
      if(isset($request->tenant_id)) {
        $businessagents = BusinessAgent::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
      }
      else {
        $businessagents = BusinessAgent::orderBy('id','desc')->get();
      }
      $i=1;
      foreach($businessagents as $businessagent) {
        if($businessagent->photo!='') 
        {
          $businessagent->photo = (isset($businessagent->photo)) ? env('APP_URL').Storage::url('app/uploads/businessagent_photos/'.$businessagent->photo) : '';
        } 
        $businessagent->sno = $i;
        $i++;
      }
      $response['status'] = 'Success';
      $response['msg'] = "";
      $response['data'] = $businessagents;
      return response()->json($response,200);
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function delete_business_agent(Request $request) {
    try {
      if(isset($request->id)) {
        $businessagent = BusinessAgent::find($request->id); 
        $businessagent->deleted_by = $request->deleted_by;
        $businessagent->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
        if($businessagent->save()) {
          $businessagent->delete();
           $response['status'] = 'Success';
          $response['msg'] = \Lang::get('api.success_business_agent_deleted');
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

  public function fileUpload(Request $request)
  {  
    try 
    { 
      if ($request->hasFile('photo')) 
      {
        $photo = '';    
        if ($request->file('photo')->isValid())
        {  
          $photo = $request->file('photo')->hashName();
          $location1 = $request->file('photo')->store('uploads/businessagent_photos');
        }
        $response['file_name'] = $photo;
      }
      else 
      {
        $response['file_name'] = '';
      }
      $response['status'] = 'Success';
      return response()->json($response,200);
    }
    catch(\Exception $e) 
    { 
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response,200);
    }
  }
}
