<?php
namespace App\Http\Controllers;

use App\Models\EmployeeAttendance;
use Illuminate\Http\Request;
use Lang;
use Validator;

class EmployeeAttendanceController extends Controller
{
    public function store_employee_attendance(Request $request)
    { 
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
				$count = count($request->Employees);
				if($count>0){
					$emp_attendance_date = '0000-00-00';
					if(isset($request->att_date)) {
						$s_date = ($request->att_date['year']).'-'.sprintf('%02d',($request->att_date['month'])).'-'.sprintf('%02d',($request->att_date['day'])); 
						$emp_attendance_date = date('Y-m-d',strtotime($s_date)); 
					}
					/*$is_attendance_exist = EmployeeAttendance::where(array('branch_id'=>$request->branch_id,'att_date'=>$emp_attendance_date))->get(); 
					if(count($is_attendance_exist)>0) { 	
						$response['status'] = 'Error';
						$response['msg'] = \Lang::get('api.attendance_already_exist');
						return response()->json($response,200);
					}
					else { */
						for($i=0; $i<$count; $i++)
						{	
                            $att_det = EmployeeAttendance::where(array('tenant_id'=>$request->tenant_id,'branch_id'=>$request->branch_id,'att_date'=>$emp_attendance_date,'employee_id'=>$request->Employees[$i]['employee_id']))->get();
                            if(count($att_det)>0) {
                               // $att_det[0]->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                                //$emp_attendance_det['branch_id']  = isset($request->branch_id) ? $request->branch_id : 0;
                                            
                                //$emp_attendance_det['att_date'] = $emp_attendance_date;
                                //$emp_attendance_det['employee_id'] = isset($request->Employees[$i]['employee_id']) ? $request->Employees[$i]['employee_id'] : 0;
                                $att_det[0]->attendance_type = isset($request->Employees[$i]['attendance_type']) ? $request->Employees[$i]['attendance_type'] : 0;
                                $att_det[0]->leave_type = isset($request->Employees[$i]['leave_type']) ? $request->Employees[$i]['leave_type'] : 0;
                                $att_det[0]->afternoon_session = isset($request->Employees[$i]['afternoon_session']) ? $request->Employees[$i]['afternoon_session'] : 0;
                                $att_det[0]->forenoon_session = isset($request->Employees[$i]['forenoon_session']) ? $request->Employees[$i]['forenoon_session'] : 0;
                                $att_det[0]->remarks = isset($request->Employees[$i]['remarks']) ? $request->Employees[$i]['remarks'] : '';
                                //$att_det[0]->created_by  = isset($request->created_by) ? $request->created_by : 0; 
                                $att_det[0]->status = isset($request->status) ? ($request->status) : 1;
                                $att_det[0]->save();
                            }
                            else {
                               $emp_attendance_det = new EmployeeAttendance();
                                $emp_attendance_det['tenant_id'] = isset($request->tenant_id) ? $request->tenant_id : 0;
                                $emp_attendance_det['branch_id']  = isset($request->branch_id) ? $request->branch_id : 0;
                                            
                                $emp_attendance_det['att_date'] = $emp_attendance_date;
                                $emp_attendance_det['employee_id'] = isset($request->Employees[$i]['employee_id']) ? $request->Employees[$i]['employee_id'] : 0;
                                $emp_attendance_det['attendance_type'] = isset($request->Employees[$i]['attendance_type']) ? $request->Employees[$i]['attendance_type'] : 0;
                                $emp_attendance_det['leave_type'] = isset($request->Employees[$i]['leave_type']) ? $request->Employees[$i]['leave_type'] : 0;
                                $emp_attendance_det['afternoon_session'] = isset($request->Employees[$i]['afternoon_session']) ? $request->Employees[$i]['afternoon_session'] : 0;
                                $emp_attendance_det['forenoon_session'] = isset($request->Employees[$i]['forenoon_session']) ? $request->Employees[$i]['forenoon_session'] : 0;
                                $emp_attendance_det['remarks'] = isset($request->Employees[$i]['remarks']) ? $request->Employees[$i]['remarks'] : '';
                                $emp_attendance_det['created_by']  = isset($request->created_by) ? $request->created_by : 0; 
                                $emp_attendance_det['status'] = isset($request->status) ? ($request->status) : 1;
                                $emp_attendance_det->save(); 
                            }
							
						}
						$response['status'] = 'Success';
						$response['msg'] = \Lang::get('api.success_employee_attendance_added');
						//$response['data'] = $emp_attendance_det;
						return response()->json($response,200);	
					//}				
				}
				else{
					$response['status'] = 'Error';
					$response['msg'] = \Lang::get('api.attendance_not_found');
					return response()->json($response,401);
				}
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,401);
			}
		}
    }

    public function update_employee_attendance(Request $request) {
        try {
            $count = count($request->Employees);
            if($count>0) {
				for($i=0; $i<$count; $i++)
				{ 
                    $employee_attendance = EmployeeAttendance::find($request->Employees[$i]['id']); 
					$employee_attendance->attendance_type = isset($request->Employees[$i]['attendance_type']) ? $request->Employees[$i]['attendance_type'] : 0; 
					$employee_attendance->leave_type = isset($request->Employees[$i]['leave_type']) ? $request->Employees[$i]['leave_type'] : 0;
					$employee_attendance->afternoon_session = isset($request->Employees[$i]['afternoon_session']) ? $request->Employees[$i]['afternoon_session'] : 0;
					$employee_attendance->forenoon_session = isset($request->Employees[$i]['forenoon_session']) ? $request->Employees[$i]['forenoon_session'] : 0; 
				    $employee_attendance->updated_by  = isset($request->updated_by) ? $request->updated_by : 0;  
                    $employee_attendance->save();
                }
				$response['status'] = "Success";
				$response['msg'] = \Lang::get('api.success_employee_attendance_updated');
				$response['data'] = $employee_attendance;
				return response()->json($response,200);
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
    
    /*public function get_employee_attendances($branch_id,$att_year,$att_month,$att_date) {
        try {
            $concat_date = $att_year.'-'.$att_month.'-'.$att_date;
            $att_date = date('Y-m-d',strtotime($concat_date));
            $employee_attendances = EmployeeAttendance::where(array('branch_id'=>$branch_id,'att_date'=>$att_date))->get();
            foreach($employee_attendances as $employee_attendance) {
                $employee_attendance->tenant_info = isset($employee_attendance->tenant_det) ? ($employee_attendance->tenant_det) : [];
                $employee_attendance->branch_info = ($employee_attendance->branch_det) ? ($employee_attendance->branch_det) : [];
                $employee_attendance->employee_info = ($employee_attendance->employee_det) ? ($employee_attendance->employee_det) : [];
				$employee_attendance->att_date = ($employee_attendance->att_date!=0) ? date('d/m/Y',strtotime($employee_attendance->att_date)) : '';
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employee_attendances;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }*/

    public function get_employee_attendances(Request $request) {
        try { 
            $cond = $request->conditions; 
            $branch_id = $cond['branch_id'];
            $att_year = $cond['att_year'];
            $att_month = $cond['att_month'];
            $att_date = $cond['att_date'];
            $concat_date = $att_year.'-'.$att_month.'-'.$att_date;
            $att_date = date('Y-m-d',strtotime($concat_date));
            $employee_attendances = EmployeeAttendance::where(array('branch_id'=>$branch_id,'att_date'=>$att_date))->get();
            foreach($employee_attendances as $employee_attendance) {
                $employee_attendance->tenant_info = isset($employee_attendance->tenant_det) ? ($employee_attendance->tenant_det) : [];
                $employee_attendance->branch_info = ($employee_attendance->branch_det) ? ($employee_attendance->branch_det) : [];
                $employee_attendance->employee_info = ($employee_attendance->employee_det) ? ($employee_attendance->employee_det) : [];
                $employee_attendance->att_date = ($employee_attendance->att_date!=0) ? date('d/m/Y',strtotime($employee_attendance->att_date)) : '';  
                $employee_attendance->edit_enable = "0";
				if($employee_attendance->leave_type == 0)
                {
                    $employee_attendance->leave_type = "";
                }
                
                $id_check= EmployeeAttendance::where('employee_id',$employee_attendance->employee_id)->where('att_date',$att_date)->get();
                if(count($id_check)>0)
                {
                    $employee_attendance->edit_enable = "1";
                }
                else
                {
                    $employee_attendance->edit_enable = "0";
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employee_attendances;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_employee_attendances(Request $request) {
        try { 
          if(isset($request->tenant_id)) {
                $att_list = EmployeeAttendance::where('tenant_id',$request->tenant_id)->select('tenant_id','branch_id','att_date')->distinct()->get();
            }
            else {
                $att_list = EmployeeAttendance::select('tenant_id','branch_id','att_date')->distinct()->get();
            }

           
            $i=1;
            foreach($att_list as $al) {
                $al->sno = $i;
                $al->att_view_date = date('d/m/Y',strtotime($al->att_date));
                $al->att_year = date('Y',strtotime($al->att_date));
                $al->att_month = date('m',strtotime($al->att_date));
                $al->att_date = date('d',strtotime($al->att_date));
                $al->tenant_info = isset($al->tenant_det) ? ($al->tenant_det) : [];
                $al->branch_info = ($al->branch_det) ? ($al->branch_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $att_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response,200);
        }
    }

    public function delete_employee_attendance(Request $request) {
        try {
            if(isset($request->id)) {
                $employee_attendance = EmployeeAttendance::find($request->id); 
                $employee_attendance->deleted_by = $request->deleted_by;
                $employee_attendance->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($employee_attendance->save()) {
                    $employee_attendance->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_employee_attendance_deleted');
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
