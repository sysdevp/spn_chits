<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\UpdateBranch;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Services\UserLoginScopeService;
use Illuminate\Http\Request;
use Validator;
use Config;

class BranchController extends Controller
{
    public function store_branch(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_name' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $branch = new Branch();
                $branch->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $branch->head_office_id = isset($request->head_office_id) ? ($request->head_office_id) : '0';
                $branch->branch_name = isset($request->branch_name) ? ($request->branch_name) : 0;
                $branch->start_date = '';
                if(isset($request->start_date)) {
                    $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                    $branch->start_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $branch->start_date = '0000-00-00';
                }
                $branch->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
                $branch->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
                $branch->landmark = isset($request->landmark) ? ($request->landmark) : '0';
                $branch->state_id = isset($request->state_id) ? ($request->state_id) : '0';
                $branch->district_id = isset($request->district_id) ? ($request->district_id) : '0';
                $branch->city_id = isset($request->city_id) ? ($request->city_id) : '0';
                $branch->pincode = isset($request->pincode) ? ($request->pincode) : '0';
                $branch->latitude = isset($request->latitude) ? $request->latitude : null;
                $branch->longitude = isset($request->longitude) ? $request->longitude : null;
                $radius = isset($request->punch_radius_meters) ? (int) $request->punch_radius_meters : 50;
                $branch->punch_radius_meters = in_array($radius, [50, 100], true) ? $radius : 50;
                $branch->branch_wise_penalty = isset($request->branch_wise_penalty) ? ($request->branch_wise_penalty) : '0';
                $branch->bonus = isset($request->bonus) ? ($request->bonus) : '0';
                $branch->email_id = isset($request->email_id) ? ($request->email_id) : '0';
                $branch->phone_no = isset($request->phone_no) ? ($request->phone_no) : '0';
                $branch->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '0';
                $branch->prize_subscriber_penalty = isset($request->prize_subscriber_penalty) ? ($request->prize_subscriber_penalty) : '0';
                $branch->non_prize_subscriber_penalty = isset($request->non_prize_subscriber_penalty) ? ($request->non_prize_subscriber_penalty) : '0';
                $branch->bonus_days = isset($request->bonus_days) ? ($request->bonus_days) : '0';
                $branch->penalty_days = isset($request->penalty_days) ? ($request->penalty_days) : '0';
                $branch->business_agent_target = isset($request->business_agent_target) ? ($request->business_agent_target) : '0';
                $branch->branch_fd_total_months = isset($request->branch_fd_total_months) ? ($request->branch_fd_total_months) : '0';
                $branch->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                $branch->agent_incentive_percent = isset($request->agent_incentive_percent) ? ($request->agent_incentive_percent) : '0';
                $branch->agent_commission_percent = isset($request->agent_commission_percent) ? ($request->agent_commission_percent) : '0';
                $branch->cgst_percent = isset($request->cgst_percent) ? ($request->cgst_percent) : '0';
                $branch->sgst_percent = isset($request->sgst_percent) ? ($request->sgst_percent) : '0';
                $branch->gst_percent = isset($request->gst_percent) ? ($request->gst_percent) : '0';
                $branch->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $branch->status = isset($request->status) ? ($request->status) : 1;
                if($branch->save()) {
                // print_r($branch);exit;
                    $branch_code = Config::get('constants.auto_codes.branch').sprintf('%05d', $branch->id);
                    $branch->branch_code = $branch_code;
                    if($branch->save()) {
                    	$update_branch = new UpdateBranch();
		                $update_branch->tenant_id = isset($branch->tenant_id) ? ($branch->tenant_id) : 0;
		                $update_branch->head_office_id = isset($branch->head_office_id) ? ($branch->head_office_id) : '0';
		                $update_branch->branch_name = isset($branch->branch_name) ? ($branch->branch_name) : 0;
		                $update_branch->branch_code = isset($branch->branch_code) ? ($branch->branch_code) : 0;
		                $update_branch->start_date = isset($branch->start_date) ? ($branch->start_date) : '0000-00-00';
		                $update_branch->edit_date = date('Y-m-d');
		                $update_branch->branch_id = isset($branch->id) ? ($branch->id) : 0;
		                $update_branch->address_line_1 = isset($branch->address_line_1) ? ($branch->address_line_1) : '0';
		                $update_branch->address_line_2 = isset($branch->address_line_2) ? ($branch->address_line_2) : '0';
		                $update_branch->landmark = isset($branch->landmark) ? ($branch->landmark) : '0';
		                $update_branch->state_id = isset($branch->state_id) ? ($branch->state_id) : '0';
		                $update_branch->district_id = isset($branch->district_id) ? ($branch->district_id) : '0';
		                $update_branch->city_id = isset($branch->city_id) ? ($branch->city_id) : '0';
		                $update_branch->pincode = isset($branch->pincode) ? ($branch->pincode) : '0';
		                $update_branch->branch_wise_penalty = isset($branch->branch_wise_penalty) ? ($branch->branch_wise_penalty) : '0';
		                $update_branch->bonus = isset($branch->bonus) ? ($branch->bonus) : '0';
		                $update_branch->email_id = isset($branch->email_id) ? ($branch->email_id) : '0';
		                $update_branch->phone_no = isset($branch->phone_no) ? ($branch->phone_no) : '0';
		                $update_branch->mobile_no = isset($branch->mobile_no) ? ($branch->mobile_no) : '0';
		                $update_branch->prize_subscriber_penalty = isset($branch->prize_subscriber_penalty) ? ($branch->prize_subscriber_penalty) : '0';
		                $update_branch->non_prize_subscriber_penalty = isset($branch->non_prize_subscriber_penalty) ? ($branch->non_prize_subscriber_penalty) : '0';
		                $update_branch->bonus_days = isset($branch->bonus_days) ? ($branch->bonus_days) : '0';
		                $update_branch->penalty_days = isset($branch->penalty_days) ? ($branch->penalty_days) : '0';
		                $update_branch->business_agent_target = isset($branch->business_agent_target) ? ($branch->business_agent_target) : '0';
		                $update_branch->branch_fd_total_months = isset($branch->branch_fd_total_months) ? ($branch->branch_fd_total_months) : '0';
		                $update_branch->remarks = isset($branch->remarks) ? ($branch->remarks) : '0';
		                $update_branch->agent_incentive_percent = isset($branch->agent_incentive_percent) ? ($branch->agent_incentive_percent) : '0';
		                $update_branch->agent_commission_percent = isset($branch->agent_commission_percent) ? ($branch->agent_commission_percent) : '0';
		                $update_branch->cgst_percent = isset($branch->cgst_percent) ? ($branch->cgst_percent) : '0';
		                $update_branch->sgst_percent = isset($branch->sgst_percent) ? ($branch->sgst_percent) : '0';
		                $update_branch->gst_percent = isset($branch->gst_percent) ? ($branch->gst_percent) : '0';
		                $update_branch->created_by = isset($branch->created_by) ? ($branch->created_by) : 0;
		                $update_branch->status = isset($branch->status) ? ($branch->status) : 1;
		                if($update_branch->save())
		                {
	                        $response['status'] = 'Success';
	                        $response['msg'] = \Lang::get('api.success_branch_added');
	                        $response['data'] = $branch;
	                        return response()->json($response,200);
	                    }
	                    else {
	                        $response['status']="Error";
	                        $response['msg']=\Lang::get('api.global_error');
	                        return response()->json($response,401);
	                    }
                    }
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
               
   
	
	 // public function get_branches(Request $request) {
        // try {
			
            // if($request->conditions['tenant_id'] == 47){
			
			// $cond = $request->conditions; 
			// if($request->log_id == 0){
			// $branches = Branch::where($cond)->orderBy('id','desc')->get();	
			// } else {
			// $role_id = User::where('employee_id',$request->log_id)->pluck('role_id');
			// $branch_id = Employee::where('id',$request->log_id)->pluck('branch_id');
			// $role_type_id = Role::where('id',$role_id[0])->pluck('role_type_id');
			
			// if($role_type_id[0] == 4){
				
			// $branches = Branch::where('id',$branch_id[0])->orderBy('id','desc')->get();		
			
			// } else {
				
			// $branches = Branch::where($cond)->orderBy('id','desc')->get();		
			// }
			// }
            
            // foreach($branches as $branch) {
                // $branch->start_date = ($branch->start_date!=0) ? date('d/m/Y',strtotime($branch->start_date)) : '';
                // $branch->tenant_info = isset($branch->tenant_det) ? ($branch->tenant_det) : [];
                // $branch->branch_info = ($branch->branch_det) ? ($branch->branch_det) : [];
                // $branch->head_office_info = ($branch->head_office_det) ? ($branch->head_office_det) : [];
                // $branch->state_info = ($branch->state_det) ? ($branch->state_det) : [];
                // $branch->district_info = ($branch->district_det) ? ($branch->district_det) : [];
                // $branch->city_info = ($branch->city_det) ? ($branch->city_det) : [];    
            // }
            // $response['status'] = 'Success';
            // $response['msg'] = '';
            // $response['data'] = $branches;
            // return response()->json($response,200);	
			// } else {
				
            // $cond = $request->conditions; 
            // $branches = Branch::where($cond)->orderBy('id','desc')->get();
            // foreach($branches as $branch) {
                // $branch->start_date = ($branch->start_date!=0) ? date('d/m/Y',strtotime($branch->start_date)) : '';
                // $branch->tenant_info = isset($branch->tenant_det) ? ($branch->tenant_det) : [];
                // $branch->branch_info = ($branch->branch_det) ? ($branch->branch_det) : [];
                // $branch->head_office_info = ($branch->head_office_det) ? ($branch->head_office_det) : [];
                // $branch->state_info = ($branch->state_det) ? ($branch->state_det) : [];
                // $branch->district_info = ($branch->district_det) ? ($branch->district_det) : [];
                // $branch->city_info = ($branch->city_det) ? ($branch->city_det) : [];    
            // }
            // $response['status'] = 'Success';
            // $response['msg'] = '';
            // $response['data'] = $branches;
            // return response()->json($response,200);
			// }
        // }
        // catch(\Exception $e) { 
            // $response['status'] = 'Error';
            // $response['msg'] = \Lang::get('api.global_error');
            // return response()->json($response, 401);
        // }
    // }




public function get_branches(Request $request) {
        try {
            
            $cond = $request->conditions;
            $query = Branch::where($cond)->orderBy('id', 'desc');

            $allowedBranchIds = app(UserLoginScopeService::class)->resolveAllowedBranchIdsFromRequest($request);
            if ($allowedBranchIds !== null) {
                if (empty($allowedBranchIds)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $allowedBranchIds);
                }
            }

            $branches = $query->get();
            foreach($branches as $branch) {
                $branch->start_date = ($branch->start_date!=0) ? date('d/m/Y',strtotime($branch->start_date)) : '';
                $branch->tenant_info = isset($branch->tenant_det) ? ($branch->tenant_det) : [];
                $branch->branch_info = ($branch->branch_det) ? ($branch->branch_det) : [];
                $branch->head_office_info = ($branch->head_office_det) ? ($branch->head_office_det) : [];
                $branch->state_info = ($branch->state_det) ? ($branch->state_det) : [];
                $branch->district_info = ($branch->district_det) ? ($branch->district_det) : [];
                $branch->city_info = ($branch->city_det) ? ($branch->city_det) : [];    
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $branches;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
    public function list_branches(Request $request) {
        try { 
            $query = Branch::orderBy('id', 'desc');
            if (isset($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }

            $allowedBranchIds = app(UserLoginScopeService::class)->resolveAllowedBranchIdsFromRequest($request);
            if ($allowedBranchIds !== null) {
                if (empty($allowedBranchIds)) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('id', $allowedBranchIds);
                }
            }

            $branches = $query->get();
            $i=1;
            foreach($branches as $branch) {
                $branch->sno = $i;
                $branch->start_date = ($branch->start_date!=0) ? date('d/m/Y',strtotime($branch->start_date)) : '';
                $branch->tenant_info = isset($branch->tenant_det) ? ($branch->tenant_det) : [];
                $branch->branch_info = ($branch->branch_det) ? ($branch->branch_det) : [];
                $branch->head_office_info = ($branch->head_office_det) ? ($branch->head_office_det) : [];
                $branch->state_info = ($branch->state_det) ? ($branch->state_det) : [];
                $branch->district_info = ($branch->district_det) ? ($branch->district_det) : [];
                $branch->city_info = ($branch->city_det) ? ($branch->city_det) : [];    
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $branches;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_branch(Request $request) {
        try {
            if(isset($request->id)) {
                $branch = Branch::find($request->id); 
                $branch->deleted_by = $request->deleted_by;
                $branch->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($branch->save()) {
                    $branch->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_branch_deleted');
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
                $branch_det = Branch::find($request->id); 
                $branch_det->status = ($branch_det->status) ? 0 : 1;
                if($branch_det->save()) {
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


    public function update_branch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_name' => 'required',
            'branch_code' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                if(isset($request->id)) 
                {           
                    $branch = Branch::find($request->id);                  
                    $branch->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $branch->head_office_id = isset($request->head_office_id) ? ($request->head_office_id) : '0';
                    $branch->branch_name = isset($request->branch_name) ? ($request->branch_name) : 0; 
                    if(isset($request->start_date)) { $response["ll"]=$request->start_date['year'];
                        $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));  
                        $branch->start_date = date('Y-m-d',strtotime($s_date));
                    } 
                    else {
                        $branch->start_date = '0000-00-00';
                    }
                    $branch->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '0';
                    $branch->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '0';
                    $branch->landmark = isset($request->landmark) ? ($request->landmark) : '0';
                    $branch->branch_code = isset($request->branch_code) ? ($request->branch_code) : 0;
                    $branch->state_id = isset($request->state_id) ? ($request->state_id) : '0';
                    $branch->district_id = isset($request->district_id) ? ($request->district_id) : '0';
                    $branch->city_id = isset($request->city_id) ? ($request->city_id) : '0';
                    $branch->pincode = isset($request->pincode) ? ($request->pincode) : '0';
                    $branch->latitude = isset($request->latitude) ? $request->latitude : null;
                    $branch->longitude = isset($request->longitude) ? $request->longitude : null;
                    $radius = isset($request->punch_radius_meters) ? (int) $request->punch_radius_meters : 50;
                    $branch->punch_radius_meters = in_array($radius, [50, 100], true) ? $radius : 50;
                    $branch->branch_wise_penalty = isset($request->branch_wise_penalty) ? ($request->branch_wise_penalty) : '0';
                    $branch->bonus = isset($request->bonus) ? ($request->bonus) : '0';
                    $branch->email_id = isset($request->email_id) ? ($request->email_id) : '0';
                    $branch->phone_no = isset($request->phone_no) ? ($request->phone_no) : '0';
                    $branch->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '0';
                    $branch->prize_subscriber_penalty = isset($request->prize_subscriber_penalty) ? ($request->prize_subscriber_penalty) : '0';
                    $branch->non_prize_subscriber_penalty = isset($request->non_prize_subscriber_penalty) ? ($request->non_prize_subscriber_penalty) : '0';
                    $branch->bonus_days = isset($request->bonus_days) ? ($request->bonus_days) : '0';
                    $branch->penalty_days = isset($request->penalty_days) ? ($request->penalty_days) : '0';
                    $branch->business_agent_target = isset($request->business_agent_target) ? ($request->business_agent_target) : '0';
                    $branch->branch_fd_total_months = isset($request->branch_fd_total_months) ? ($request->branch_fd_total_months) : '0';
                    $branch->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                    $branch->agent_incentive_percent = isset($request->agent_incentive_percent) ? ($request->agent_incentive_percent) : '0';
                    $branch->cgst_percent = isset($request->cgst_percent) ? ($request->cgst_percent) : '0';
                    $branch->sgst_percent = isset($request->sgst_percent) ? ($request->sgst_percent) : '0';
                    $branch->gst_percent = isset($request->gst_percent) ? ($request->gst_percent) : '0';
                    $branch->agent_commission_percent = isset($request->agent_commission_percent) ? ($request->agent_commission_percent) : '0';
                    $branch->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($branch->save()) {
                    	$update_branch = new UpdateBranch();
                        $update_branch->tenant_id = isset($branch->tenant_id) ? ($branch->tenant_id) : 0;
                        $update_branch->head_office_id = isset($branch->head_office_id) ? ($branch->head_office_id) : '0';
                        $update_branch->branch_name = isset($branch->branch_name) ? ($branch->branch_name) : 0;
                        $update_branch->branch_id = isset($branch->id) ? ($branch->id) : 0;
                        $update_branch->branch_code = isset($branch->branch_code) ? ($branch->branch_code) : 0;
                        $update_branch->start_date = isset($branch->start_date) ? ($branch->start_date) : '0000-00-00';
                        $update_branch->edit_date = date('Y-m-d');
                        $update_branch->address_line_1 = isset($branch->address_line_1) ? ($branch->address_line_1) : '0';
                        $update_branch->address_line_2 = isset($branch->address_line_2) ? ($branch->address_line_2) : '0';
                        $update_branch->landmark = isset($branch->landmark) ? ($branch->landmark) : '0';
                        $update_branch->state_id = isset($branch->state_id) ? ($branch->state_id) : '0';
                        $update_branch->district_id = isset($branch->district_id) ? ($branch->district_id) : '0';
                        $update_branch->city_id = isset($branch->city_id) ? ($branch->city_id) : '0';
                        $update_branch->pincode = isset($branch->pincode) ? ($branch->pincode) : '0';
                        $update_branch->branch_wise_penalty = isset($branch->branch_wise_penalty) ? ($branch->branch_wise_penalty) : '0';
                        $update_branch->bonus = isset($branch->bonus) ? ($branch->bonus) : '0';
                        $update_branch->email_id = isset($branch->email_id) ? ($branch->email_id) : '0';
                        $update_branch->phone_no = isset($branch->phone_no) ? ($branch->phone_no) : '0';
                        $update_branch->mobile_no = isset($branch->mobile_no) ? ($branch->mobile_no) : '0';
                        $update_branch->prize_subscriber_penalty = isset($branch->prize_subscriber_penalty) ? ($branch->prize_subscriber_penalty) : '0';
                        $update_branch->non_prize_subscriber_penalty = isset($branch->non_prize_subscriber_penalty) ? ($branch->non_prize_subscriber_penalty) : '0';
                        $update_branch->bonus_days = isset($branch->bonus_days) ? ($branch->bonus_days) : '0';
                        $update_branch->penalty_days = isset($branch->penalty_days) ? ($branch->penalty_days) : '0';
                        $update_branch->business_agent_target = isset($branch->business_agent_target) ? ($branch->business_agent_target) : '0';
                        $update_branch->branch_fd_total_months = isset($branch->branch_fd_total_months) ? ($branch->branch_fd_total_months) : '0';
                        $update_branch->remarks = isset($branch->remarks) ? ($branch->remarks) : '0';
                        $update_branch->agent_incentive_percent = isset($branch->agent_incentive_percent) ? ($branch->agent_incentive_percent) : '0';
                        $update_branch->agent_commission_percent = isset($branch->agent_commission_percent) ? ($branch->agent_commission_percent) : '0';
                        $update_branch->cgst_percent = isset($branch->cgst_percent) ? ($branch->cgst_percent) : '0';
                        $update_branch->sgst_percent = isset($branch->sgst_percent) ? ($branch->sgst_percent) : '0';
                        $update_branch->gst_percent = isset($branch->gst_percent) ? ($branch->gst_percent) : '0';
                        $update_branch->created_by = isset($branch->created_by) ? ($branch->created_by) : 0;
                        $update_branch->status = isset($branch->status) ? ($branch->status) : 1;
                        if($update_branch->save())
                        {       
	                        $response['status'] = "Success";
	                        $response['msg'] = \Lang::get('api.success_branch_updated');
	                        $response['data'] = $branch;
	                        return response()->json($response,200);
	                    }
	                    else {
	                        $response['status']="Error";
	                        $response['msg']=\Lang::get('api.global_error');
	                        return response()->json($response,401);
	                    }
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
}
