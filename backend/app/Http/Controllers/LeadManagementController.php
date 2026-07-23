<?php
namespace App\Http\Controllers;

use App\Models\LeadManagement;
use App\Models\Followup;
use Illuminate\Http\Request;
use Validator;

class LeadManagementController extends Controller
{   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_lead_management(Request $request)
    {
       $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'employee_id'=>'required',
            'lead_customer_name'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $leadmanagement=new LeadManagement(); 
                $leadmanagement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $leadmanagement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $leadmanagement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                $leadmanagement->source_type_id = isset($request->source_type_id) ? ($request->source_type_id) : 0;
                $leadmanagement->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
                $leadmanagement->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
    			 $leadmanagement->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                $leadmanagement->email_id = isset($request->email_id) ? ($request->email_id) : '';
                $leadmanagement->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                $leadmanagement->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                $leadmanagement->landmark = isset($request->landmark) ? ($request->landmark) : '';
                $leadmanagement->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                $leadmanagement->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                $leadmanagement->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                $leadmanagement->pincode = isset($request->pincode) ? ($request->pincode) : '';
                $leadmanagement->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $leadmanagement->status = isset($request->status) ? ($request->status) : 1;
    			$leadmanagement->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0;
				 
                if($leadmanagement->save())
                {
                    $followups= new Followup();
                    $followups->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $followups->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $followups->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
    				if(isset($request->followup_date)) {
    					$f_date = ($request->followup_date['year']).'-'.sprintf('%02d',($request->followup_date['month'])).'-'.sprintf('%02d',($request->followup_date['day'])); 
    					$followups->followup_date  = date('Y-m-d',strtotime($f_date)); 
    				}
    				else {
    					$followups->followup_date  = '0000-00-00';
    				}
                    $followups->lead_management_id = isset($leadmanagement->id) ? ($leadmanagement->id) : 0;
    				if(isset($request->next_followup_date)) {
    					$nf_date = ($request->next_followup_date['year']).'-'.sprintf('%02d',($request->next_followup_date['month'])).'-'.sprintf('%02d',($request->next_followup_date['day'])); 
    					$followups->next_followup_date  = date('Y-m-d',strtotime($nf_date)); 
    				}
    				else {
    					// $followups->next_followup_date  = '0000-00-00';
    				}
                    $followups->remarks = isset($request->remarks) ? ($request->remarks) : '' ;
                    $followups->created_by = isset($request->created_by) ? ($request->created_by) : 0; 
                    $followups->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0; 
                    $followups->status = isset($request->status) ? ($request->status) : 1;
                    
                    if($followups->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_lead_management_added');
                        $response['data'] =$leadmanagement;
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
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        } 
    }

    public function update_lead_management(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'employee_id'=>'required',
            'lead_customer_name'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $leadmanagement = leadManagement::find($request->id); 
                    $leadmanagement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
    				$leadmanagement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
    				$leadmanagement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                    $leadmanagement->source_type_id = isset($request->source_type_id) ? ($request->source_type_id) : 0;
    				$leadmanagement->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
    				$leadmanagement->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
    				$leadmanagement->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
    				$leadmanagement->email_id = isset($request->email_id) ? ($request->email_id) : '';
    				$leadmanagement->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
    				$leadmanagement->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
    				$leadmanagement->landmark = isset($request->landmark) ? ($request->landmark) : '';
    				$leadmanagement->state_id = isset($request->state_id) ? ($request->state_id) : 0;
    				$leadmanagement->district_id = isset($request->district_id) ? ($request->district_id) : 0;
    				$leadmanagement->city_id = isset($request->city_id) ? ($request->city_id) : 0;
    				$leadmanagement->pincode = isset($request->pincode) ? ($request->pincode) : ''; 
    				$leadmanagement->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0; 
                    $leadmanagement->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0; 
                    if($leadmanagement->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_lead_management_updated');
						$response['data'] =$leadmanagement;
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

    public function get_lead_managements(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $leadmanagements = leadManagement::where($cond)->orderBy('id','desc')->get();
            foreach($leadmanagements as $leadmanagement) {
                $leadmanagement->tenant_info = isset($leadmanagement->tenant_det) ? ($leadmanagement->tenant_det) : [];
                $leadmanagement->branch_info = isset($leadmanagement->branch_det) ? ($leadmanagement->branch_det) : [] ;
                $leadmanagement->employee_info = isset($leadmanagement->employee_det) ? ($leadmanagement->employee_det) : [] ;
                $leadmanagement->state_info = isset($leadmanagement->state_det) ? ($leadmanagement->state_det) : [] ;
                $leadmanagement->district_info = isset($leadmanagement->district_det) ? ($leadmanagement->district_det) : [] ;
                $leadmanagement->city_info = isset($leadmanagement->city_det) ? ($leadmanagement->city_det) : [] ;
				$leadmanagement->followup_info = isset($leadmanagement->followup_det) ? ($leadmanagement->followup_det) : [] ;
				$leadmanagement->followupstatustype_info = isset($leadmanagement->followupstatustype_det) ? ($leadmanagement->followupstatustype_det) : [] ;
                $leadmanagement->source_type_info = isset($leadmanagement->source_type_det) ? ($leadmanagement->source_type_det) : [] ;                
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['followup_det'] = isset($Followup->followup_det) ? ($Followup->followup_det) : [];
            $response['data'] = $leadmanagements;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_lead_managements(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $leadmanagements = leadManagement::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $leadmanagements = leadManagement::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($leadmanagements as $leadmanagement) {
                $leadmanagement->tenant_info = isset($leadmanagement->tenant_det) ? ($leadmanagement->tenant_det) : [];
                $leadmanagement->branch_info = isset($leadmanagement->branch_det) ? ($leadmanagement->branch_det) : [] ;
                $leadmanagement->employee_info = isset($leadmanagement->employee_det) ? ($leadmanagement->employee_det) : [] ;
                $leadmanagement->state_info = isset($leadmanagement->state_det) ? ($leadmanagement->state_det) : [] ;
                $leadmanagement->district_info = isset($leadmanagement->district_det) ? ($leadmanagement->district_det) : [] ;
                $leadmanagement->city_info = isset($leadmanagement->city_det) ? ($leadmanagement->city_det) : [] ;
                //$response["kk"] = "ll";
                $leadmanagement->followup_info = isset($leadmanagement->followup_det) ? ($leadmanagement->followup_det) : [] ;
                $leadmanagement->followupstatustype_info = isset($leadmanagement->followupstatustype_det) ? ($leadmanagement->followupstatustype_det) : [] ;
                $leadmanagement->source_type_info = isset($leadmanagement->source_type_det) ? ($leadmanagement->source_type_det) : [] ;
				$leadmanagement->sno = $i;
                $last_followups_id = Followup::where('lead_management_id',$leadmanagement->id)->max('id');
                $last_followup = Followup::where('id',$last_followups_id)->get();
                $leadmanagement->last_followup_date = isset($last_followup[0]->next_followup_date) ? ($last_followup[0]->next_followup_date) : '0000-00-00';
				$i++;
            }
            if($leadmanagements) {
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $leadmanagements;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_lead_management(Request $request) {
        try {
            if(isset($request->id)) {
                $leadmanagement = leadManagement::find($request->id); 
                $leadmanagement->deleted_by = $request->deleted_by;
                $leadmanagement->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($leadmanagement->save()) {
                    $leadmanagement->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_lead_management_deleted');
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
