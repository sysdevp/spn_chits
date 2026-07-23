<?php
namespace App\Http\Controllers;

use App\Models\Followup;
use App\Models\LeadManagement;
use Illuminate\Http\Request;
use Validator;

class FollowupController extends Controller
{
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store_followup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'lead_management_id' => 'required',
            'employee_id' => 'required',
            'followup_date' => 'required',
            'next_followup_date' => 'required',
            'followup_status_type_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 
                $followup = new Followup(); 
                $followup->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $followup->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $followup->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : '';
                $followup->employee_id = isset($request->employee_id) ? ($request->employee_id) : '';
                if(isset($request->followup_date)) {
					$f_date = ($request->followup_date['year']).'-'.sprintf('%02d',($request->followup_date['month'])).'-'.sprintf('%02d',($request->followup_date['day'])); 
					$followup->followup_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					$followup->followup_date  = '0000-00-00';
				}
                $followup->remarks = isset($request->remarks) ? ($request->remarks) : '';
                if(isset($request->next_followup_date)) {
					$nf_date = ($request->next_followup_date['year']).'-'.sprintf('%02d',($request->next_followup_date['month'])).'-'.sprintf('%02d',($request->next_followup_date['day'])); 
					$followup->next_followup_date  = date('Y-m-d',strtotime($nf_date)); 
				}
				else {
					$followup->next_followup_date  = '0000-00-00';
				}
                $followup->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0;
                $followup->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $followup->status = isset($request->status) ? ($request->status) : 1;
                if($followup->save()) {
					if($request->followup_status_type_id != 0){
						$lead_det = LeadManagement::find($request->lead_management_id);
						$lead_det->followup_status_type_id = $request->followup_status_type_id;
						$lead_det->save();
					}
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_followup_added');
                    $response['data'] = $followup;
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

    public function update_followup(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'lead_management_id' => 'required',
            'employee_id' => 'required',
            'followup_date' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $followup = Followup::find($request->id); 
                    $followup->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $followup->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $followup->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : '';
                    $followup->employee_id = isset($request->employee_id) ? ($request->employee_id) : '';
                    if(isset($request->followup_date)) {
						$f_date = ($request->followup_date['year']).'-'.sprintf('%02d',($request->followup_date['month'])).'-'.sprintf('%02d',($request->followup_date['day'])); 
						$followup->followup_date  = date('Y-m-d',strtotime($f_date)); 
					}
					else {
						$followup->followup_date  = '0000-00-00';
					}
                    $followup->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    if(isset($request->next_followup_date)) {
						$nf_date = ($request->next_followup_date['year']).'-'.sprintf('%02d',($request->next_followup_date['month'])).'-'.sprintf('%02d',($request->next_followup_date['day'])); 
						$followup->next_followup_date  = date('Y-m-d',strtotime($nf_date)); 
					}
					else {
						$followup->next_followup_date  = '0000-00-00';
					}
                    $followup->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0;
                    $followup->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($followup->save()) {
						if($request->followup_status_type_id != 0){
    						$lead_det = LeadManagement::find($request->lead_management_id);
    						$lead_det->followup_status_type_id = $request->followup_status_type_id;
    						$lead_det->save();
    					}
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_followup_updated');
                        $response['data'] = $followup;
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

    public function get_followups(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $followups = Followup::where($cond)->orderBy('id','desc')->get();
			$i=1;
			$latest_det = Followup::where(array('tenant_id'=>$followups[0]->tenant_id,'branch_id'=>$followups[0]->branch_id,'lead_management_id'=>$followups[0]->lead_management_id))->orderBy('followup_date', 'desc')->orderBy('id','desc')->limit(1)->get();
            foreach($followups as $followup) {
				$followup->is_latest = false;
				if(($latest_det[0]->id)== ($followup->id)) {
					$followup->is_latest = true;
				}
                $followup->tenant_info = isset($followup->tenant_det) ? ($followup->tenant_det) : [];   
                $followup->branch_info = isset($followup->branch_det) ? ($followup->branch_det) : [];
                $followup->employee_info = isset($followup->employee_det) ? ($followup->employee_det) : [];
                $followup->followup_status_type_info = isset($followup->followup_status_type_det) ? ($followup->followup_status_type_det) : [];
				$followup->lead_management_info = isset($followup->lead_management_det) ? ($followup->lead_management_det) : [];
				$followup->followup_date = ($followup->followup_date!=0) ? date('d/m/Y',strtotime($followup->followup_date)) : '';
				$followup->next_followup_date = ($followup->next_followup_date!=0) ? date('d/m/Y',strtotime($followup->next_followup_date)) : '';
				$followup->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $followups;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_followups(Request $request) {
        try{
            if(isset($request->tenant_id)) {
                $followups = Followup::where(array('tenant_id'=>$request->tenant_id,'lead_management_id'=>$request->lead_management_id))->orderBy('id','desc')->get();
            }
            else {
                $followups = Followup::where('lead_management_id',$request->lead_management_id)->orderBy('id','desc')->get();
            }
            $i=1;
            foreach($followups as $followup) {
				$followup->tenant_info = isset($followup->tenant_det) ? ($followup->tenant_det) : [];
                    $followup->branch_info = isset($followup->branch_det) ? ($followup->branch_det) : [] ;
                    $followup->employee_info = isset($followup->employee_det) ? ($followup->employee_det) : [] ;
				  $followup->followup_status_type_info = isset($followup->followup_status_type_det) ? ($followup->followup_status_type_det) : [];
                $followup->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $followups;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_followup(Request $request) {
        try {
            if(isset($request->id)) {
                $followup = Followup::find($request->id); 
                $followup->deleted_by = $request->deleted_by;
                $followup->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($followup->save()) {
                    $followup->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_followup_deleted');
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

    public function get_followup_report(Request $request) {
        try { 
            $cond = [];
            $end_date='';
            $start_date = '';
            $end_date_followup = '';
            $start_date_followup = '';
            $start_date_followup = '';
             if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id;
            }
            if(isset($request->branch_id)){
                $cond['branch_id'] = $request->branch_id; 
            }
            if(isset($request->employee_id)){
                $cond['employee_id'] = $request->employee_id; 
            }
            if(isset($request->start_date)) { 
                $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
                $start_date = date('Y-m-d',strtotime($s_date)); 
                //$start_date = $request->start_date;
            }           
            if(isset($request->end_date)) {
                $s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
                $end_date = date('Y-m-d',strtotime($s_date)); 
            }
            if(isset($request->start_date_followup)) { 
                $s_date = ($request->start_date_followup['year']).'-'.sprintf('%02d',($request->start_date_followup['month'])).'-'.sprintf('%02d',($request->start_date_followup['day']));
                $start_date_followup = date('Y-m-d',strtotime($s_date)); 
            }           
            if(isset($request->end_date_followup)) {
                $s_date = ($request->end_date_followup['year']).'-'.sprintf('%02d',($request->end_date_followup['month'])).'-'.sprintf('%02d',($request->end_date_followup['day'])); 
                $end_date_followup = date('Y-m-d',strtotime($s_date)); 
            }
            
            if(($start_date=='') && ($end_date=='') && ($start_date_followup=='') && ($end_date_followup=='')) {
                 $followups = Followup::where($cond)->get();
            }
            else if(($start_date=='') && ($end_date!='') && ($start_date_followup=='') && ($end_date_followup=='')) {
                //$cond['end_date'] = $end_date; 
                $followups = Followup::where($cond)->where('followup_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
            }
             else if(($start_date=='') && ($end_date=='') && ($start_date_followup=='') && ($end_date_followup!='')) {
                //$cond['end_date'] = $end_date; 
                $followups = Followup::where($cond)->where('next_followup_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if(($start_date_followup!='') && ($end_date_followup=='')) {
                    $end_date_followup = date('Y-m-d');
                }
                if(($start_date!='') && ($start_date_followup=='')) {
                    $followups = Followup::where($cond)->whereBetween('followup_date',[$start_date,$end_date])->get();
                } else if(($start_date=='') && ($start_date_followup!='')){
                    $followups = Followup::where($cond)->whereBetween('next_followup_date',[$start_date_followup,$end_date_followup])->get();
                } else {
                    $followups = Followup::where($cond)->whereBetween('followup_date',[$start_date,$end_date])->whereBetween('next_followup_date',[$start_date_followup,$end_date_followup])->get();
                }                
            } 
            $i=1;
            if(count($followups)>0) {
            foreach($followups as $followup) { 
                $followup->sno = $i;
                $followup->tenant_info = isset($followup->tenant_det) ? ($followup->tenant_det) : [];   
                $followup->branch_name = isset($followup->branch_det) ? ($followup->branch_det->branch_name) : '';
                $followup->employee_name = isset($followup->employee_det) ? ($followup->employee_det->first_name.' '.$followup->employee_det->last_name) : '';
                $followup->followup_status_type_info = isset($followup->followup_status_type_det) ? ($followup->followup_status_type_det) : [];
                $followup->lead_customer_name = isset($followup->lead_management_det) ? ($followup->lead_management_det->lead_customer_name) : '';
                $followup->mobile = isset($followup->lead_management_det) ? ($followup->lead_management_det->mobile_no) : 0;
                $followup->followup_date = ($followup->followup_date!=0) ? date('d/m/Y',strtotime($followup->followup_date)) : '';
                $followup->next_followup_date = ($followup->next_followup_date!=0) ? date('d/m/Y',strtotime($followup->next_followup_date)) : '';
                $followup->sno = $i++;
            }
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $followups;
                return response()->json($response,200);
            } else {
                $followups = [];
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.no_data_found');
                $response['data'] = $followups;
                return response()->json($response, 200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
}
