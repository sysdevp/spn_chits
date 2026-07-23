<?php
namespace App\Http\Controllers;

use App\Models\EmployeePayroll;
use Illuminate\Http\Request;
use Lang;
use Validator;

class EmployeePayrollController extends Controller
{
    public function store_employee_payroll(Request $request)
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
				$employee_payroll = new EmployeePayroll(); 
				$employee_payroll->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $employee_payroll->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                $employee_payroll->employee_id  = isset($request->employee_id) ? $request->employee_id : 0;
                $employee_payroll->actual_working_days  = isset($request->actual_working_days) ? $request->actual_working_days : 0;
                $employee_payroll->employee_working_days  = isset($request->employee_working_days) ? $request->employee_working_days : 0;
                $employee_payroll->monthly_salary  = isset($request->monthly_salary) ? $request->monthly_salary : 0; 
                $employee_payroll->salary_status  = isset($request->salary_status) ? $request->salary_status : 0;
                $employee_payroll->created_by  = isset($request->created_by) ? $request->created_by : 0;
                $employee_payroll->status = isset($request->status) ? ($request->status) : 1;
				if($employee_payroll->save()) {
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_employee_payroll_added');
					$response['data'] = $employee_payroll;
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
				return response()->json($response,200);
			}
		}
    }
    public function update_employee_payroll(Request $request) {
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
                if(isset($request->id)) {
                    $employee_payroll = EmployeePayroll::find($request->id);
					$employee_payroll->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                    $employee_payroll->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                    $employee_payroll->employee_id  = isset($request->employee_id) ? $request->employee_id : 0;
                    $employee_payroll->actual_working_days  = isset($request->actual_working_days) ? $request->actual_working_days : 0;
                    $employee_payroll->employee_working_days  = isset($request->employee_working_days) ? $request->employee_working_days : 0;
                    $employee_payroll->monthly_salary  = isset($request->monthly_salary) ? $request->monthly_salary : 0; 
                    $employee_payroll->salary_status  = isset($request->salary_status) ? $request->salary_status : 0;
                    $employee_payroll->updated_by  = isset($request->updated_by) ? $request->updated_by : 0;
                    if($employee_payroll->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_employee_payroll_updated');
                        $response['data'] = $employee_payroll;
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

    public function get_employee_payrolls(Request $request) {
        try {
            $cond = $request->conditions; 
            $employee_payrolls = EmployeePayroll::where($cond)->orderBy('id','desc')->get();
            foreach($employee_payrolls as $employee_payroll) {
                $employee_payroll->tenant_info = isset($employee_payroll->tenant_det) ? ($employee_payroll->tenant_det) : [];
                
                $employee_payroll->branch_info = ($employee_payroll->branch_det) ? ($employee_payroll->branch_det) : [];
                
                $employee_payroll->employee_info = ($employee_payroll->employee_det) ? ($employee_payroll->employee_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employee_payrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_employee_payrolls(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $employee_payrolls = EmployeePayroll::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $employee_payrolls = EmployeePayroll::orderBy('id','desc')->get();
            } 
            $i=1;
            foreach($employee_payrolls as $employee_payroll) {
                $employee_payroll->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $employee_payrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_employee_payroll(Request $request) {
        try {
            if(isset($request->id)) {
                $employee_payroll = EmployeePayroll::find($request->id); 
                $employee_payroll->deleted_by = $request->deleted_by;
                if($employee_payroll->save()) {
                    $employee_payroll->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_employee_payroll_deleted');
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
