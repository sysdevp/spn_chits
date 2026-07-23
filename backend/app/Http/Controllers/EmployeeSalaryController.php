<?php
namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Lang;
use Validator;

class EmployeeSalaryController extends Controller
{
    public function store_employee_salary(Request $request)
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
				$employee_salary = new EmployeeSalary(); 
				$employee_salary->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $employee_salary->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                $employee_salary->employee_id  = isset($request->employee_id) ? $request->employee_id : 0;
                $employee_salary->salary_date  = isset($request->salary_date) ? $request->salary_date : '0000-00-00';
                $employee_salary->salary_amount  = isset($request->salary_amount) ? $request->salary_amount : 0;
                $employee_salary->created_by  = isset($request->created_by) ? $request->created_by : 0;
                $employee_salary->status = isset($request->status) ? ($request->status) : 1;
				if($employee_salary->save()) {
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_employee_salary_added');
					$response['data'] = $employee_salary;
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

    public function update_employee_salary(Request $request) {
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
                    $employee_salary = EmployeeSalary::find($request->id);
                    $employee_salary->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                    $employee_salary->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                    $employee_salary->employee_id  = isset($request->employee_id) ? $request->employee_id : 0;
                    $employee_salary->salary_date  = isset($request->salary_date) ? $request->salary_date : '0000-00-00';
                    $employee_salary->salary_amount  = isset($request->salary_amount) ? $request->salary_amount : 0;
                    $employee_salary->updated_by  = isset($request->updated_by) ? $request->updated_by : 0;
                    if($employee_salary->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_employee_salary_updated');
                        $response['data'] = $employee_salary;
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
    
    public function get_employee_salaries(Request $request) {
        try {
            $cond = $request->conditions;
            $employee_salaries = EmployeeSalary::where($cond)->orderBy('id','desc')->get();
            foreach($employee_salaries as $employee_salary) {
                $employee_salary->tenant_info = isset($employee_salary->tenant_det) ? ($employee_salary->tenant_det) : [];
                
                $employee_salary->branch_info = ($employee_salary->branch_det) ? ($employee_salary->branch_det) : [];
                
                $employee_salary->employee_info = ($employee_salary->employee_det) ? ($employee_salary->employee_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employee_salaries;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_employee_salaries(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $employee_salaries = EmployeeSalary::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $employee_salaries = EmployeeSalary::orderBy('id','desc')->get();
            } 
            $i=1;
            foreach($employee_salaries as $employee_salary) {
                $employee_salary->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $employee_salaries;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_employee_salary(Request $request) {
        try {
            if(isset($request->id)) {
                $employee_salary = EmployeeSalary::find($request->id); 
                $employee_salary->deleted_by = $request->deleted_by;
                if($employee_salary->save()) {
                    $employee_salary->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_employee_salary_deleted');
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
