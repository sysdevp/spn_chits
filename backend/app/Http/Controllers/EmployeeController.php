<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AgentCommissionPayment;
use App\Models\CollectionAreaMapping;
use App\Models\EmployeeMonthlySalaryDetail;
use App\Models\CollectionRemark;
use App\Models\User;
use App\Models\Remark;
use App\Models\Role;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Lang;
use Validator;

class EmployeeController extends Controller
{
    /**
     * Role IDs strictly *under* the selected login role (any depth).
     * Excludes the login role itself (no peer mapping on same role).
     *
     * Uses an in-memory parent walk instead of repeated SQL BFS so MySQL
     * string/int under_id values still match, and the subtree is consistent
     * with the hierarchy chain.
     */
    private function getStrictDescendantRoleIds($rootRoleId, $tenantId): array
    {
        $rootRoleId = (int) $rootRoleId;
        $tenantId = (int) $tenantId;

        $roles = Role::where('tenant_id', $tenantId)->get(['id', 'under_id']);
        if ($roles->isEmpty()) {
            return [];
        }

        $byId = [];
        foreach ($roles as $r) {
            $byId[(int) $r->id] = $r;
        }

        $out = [];
        foreach ($roles as $r) {
            $rid = (int) $r->id;
            if ($rid === $rootRoleId) {
                continue;
            }

            $cur = $rid;
            $visited = [];
            while ($cur && !isset($visited[$cur])) {
                $visited[$cur] = true;
                $node = isset($byId[$cur]) ? $byId[$cur] : null;
                if (!$node) {
                    break;
                }
                $p = $node->under_id;
                if ($p === null || $p === '' || (int) $p === 0) {
                    break;
                }
                $p = (int) $p;
                if ($p === $rootRoleId) {
                    $out[] = $rid;
                    break;
                }
                $cur = $p;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Role IDs strictly above the login role (walk parents: CEO, etc.).
     */
    private function getAncestorRoleIds($roleId, $tenantId): array
    {
        $ids = [];
        $node = Role::where('tenant_id', (int) $tenantId)->where('id', (int) $roleId)->first();
        if (!$node) {
            return [];
        }
        $pid = $node->under_id;
        while ($pid !== null && $pid !== '' && (int) $pid > 0) {
            $p = (int) $pid;
            $ids[] = $p;
            $parent = Role::where('tenant_id', (int) $tenantId)->where('id', $p)->first();
            if (!$parent) {
                break;
            }
            $pid = $parent->under_id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * When editing a login (user_id), limit role-tree lists to saved mappings
     * (same rules as mobile: user_branch_mapping / user_employee_mapping + primary row).
     */
    private function resolveUserLoginScope(int $userId, int $tenantId): array
    {
        $scopeBranchIds = [];
        $scopeEmployeeIds = [];

        if ($userId <= 0) {
            return ['branches' => [], 'employees' => []];
        }

        $user = User::find($userId);
        if (!$user) {
            return ['branches' => [], 'employees' => []];
        }

        $bQuery = DB::table('user_branch_mapping')
            ->where('user_id', $userId)
            ->where('status', 1);
        if ($tenantId > 0) {
            $bQuery->where('tenant_id', $tenantId);
        }
        foreach ($bQuery->pluck('branch_id') as $bid) {
            $scopeBranchIds[] = (int) $bid;
        }

        $eQuery = DB::table('user_employee_mapping')
            ->where('user_id', $userId)
            ->where('status', 1);
        if ($tenantId > 0) {
            $eQuery->where('tenant_id', $tenantId);
        }
        foreach ($eQuery->pluck('employee_id') as $eid) {
            $scopeEmployeeIds[] = (int) $eid;
        }

        if (empty($scopeBranchIds) && !empty($user->branch_id)) {
            $scopeBranchIds[] = (int) $user->branch_id;
        }
        if (empty($scopeEmployeeIds) && !empty($user->employee_id)) {
            $scopeEmployeeIds[] = (int) $user->employee_id;
        }

        return [
            'branches' => array_values(array_unique(array_filter($scopeBranchIds))),
            'employees' => array_values(array_unique(array_filter($scopeEmployeeIds))),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function applyDayAllowance($employee, Request $request)
    {
        if (!$request->has('day_allowance')) {
            return;
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('employees', 'day_allowance')) {
            \Illuminate\Support\Facades\Schema::table('employees', function ($table) {
                $table->decimal('day_allowance', 10, 2)->default(200);
            });
        }
        $v = $request->day_allowance;
        $employee->day_allowance = ($v === null || $v === '') ? 200 : (float) $v;
    }

    public function store_employee(Request $request)
    {
	  	$validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_type' => 'required',
			'first_name' => 'required',
            'mobile_no' => 'required',
			'role_id' => 'required',
			'designation_id' => 'required',
			'salary' => 'required',
			'commission' => 'required',
			'incentive' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {	
			try {
				$employee = new Employee();
				$employee->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
				$employee->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
				$employee->employee_type = isset($request->employee_type) ? $request->employee_type : 0;
				$employee->saluation = isset($request->saluation) ? $request->saluation : '';
				$employee->first_name = isset($request->first_name) ? $request->first_name : '';
				$employee->last_name = isset($request->last_name) ? $request->last_name : '';
				$employee->gender = isset($request->gender) ? $request->gender : 0;
				if(isset($request->dob)) {
                    $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                    $employee->dob = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $employee->dob = '0000-00-00';
                }
				$employee->photo = isset($request->photo) ? $request->photo : '0';
				$employee->collection_agent = ($request->collection_agent=="true") ? 1 : '0';
				$employee->phone_no = isset($request->phone_no) ? $request->phone_no : '0';
				$employee->mobile_no = isset($request->mobile_no) ? $request->mobile_no : '0';
				$employee->email = isset($request->email) ? $request->email : '0';
				$employee->adhar_card = isset($request->adhar_card) ? $request->adhar_card : '0';
				$employee->driving_license = isset($request->driving_license) ? $request->driving_license : '0';
				$employee->pan_no = isset($request->pan_no) ? $request->pan_no : '0';
				$employee->ration_card_no = isset($request->ration_card_no) ? $request->ration_card_no : '0';
				$employee->father_name = isset($request->father_name) ? $request->father_name : '';
				$employee->spouse_name = isset($request->spouse_name) ? $request->spouse_name : '';
				$employee->door_no = isset($request->door_no) ? $request->door_no : '0';
				$employee->building_name = isset($request->building_name) ? $request->building_name : '';
				$employee->address_line_1 = isset($request->address_line_1) ? $request->address_line_1 : '';
				$employee->address_line_2 = isset($request->address_line_2) ? $request->address_line_2 : '';
				$employee->landmark = isset($request->landmark) ? $request->landmark : '';
				$employee->state_id = isset($request->state_id) ? $request->state_id : 0;
				$employee->district_id = isset($request->district_id) ? $request->district_id : 0;
				$employee->city_id = isset($request->city_id) ? $request->city_id : 0;
				$employee->pincode = isset($request->pincode) ? $request->pincode : '0';
				$employee->communication_door_no = isset($request->communication_door_no) ? $request->communication_door_no : '0';
				$employee->communication_building_name = isset($request->communication_building_name) ? $request->communication_building_name : '';
				$employee->communication_address_line_1 = isset($request->communication_address_line_1) ? $request->communication_address_line_1 : '';
				$employee->communication_address_line_2 = isset($request->communication_address_line_2) ? $request->communication_address_line_2 : '';
				$employee->communication_land_mark = isset($request->communication_land_mark) ? $request->communication_land_mark : '';
				$employee->communication_state_id = isset($request->communication_state_id) ? $request->communication_state_id : 0;
				$employee->communication_district_id = isset($request->communication_district_id) ? $request->communication_district_id : 0;
				$employee->communication_city_id = isset($request->communication_city_id) ? $request->communication_city_id : 0;
				$employee->communication_pincode = isset($request->communication_pincode) ? $request->communication_pincode : '0';
				$employee->designation_id = isset($request->designation_id) ? $request->designation_id : 0;
				$employee->role_id = isset($request->role_id) ? $request->role_id : 0;
				$employee->esi = isset($request->esi) ? $request->esi : '0';
				$employee->pf = isset($request->pf) ? $request->pf : '0';
				$employee->blood_group = isset($request->blood_group) ? $request->blood_group : '0';
				$employee->qualification = isset($request->qualification) ? $request->qualification : '0';
				$employee->previous_company_details = isset($request->previous_company_details) ? $request->previous_company_details : '0';
				if(isset($request->joining_date)) {
                    $s_date = ($request->joining_date['year']).'-'.sprintf('%02d',($request->joining_date['month'])).'-'.sprintf('%02d',($request->joining_date['day'])); 
                    $employee->joining_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $employee->joining_date = '0000-00-00';
                }
				$employee->experience = isset($request->experience) ? $request->experience : '0';
				$employee->verification_on_call_by = isset($request->verification_on_call_by) ? $request->verification_on_call_by : '0';
				$employee->last_month_salary = isset($request->last_month_salary) ? $request->last_month_salary : '0';
				$employee->holder_name = isset($request->holder_name) ? $request->holder_name : '';
				$employee->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
				$employee->account_no = isset($request->account_no) ? $request->account_no : '0';
				$employee->ifsc_code = isset($request->ifsc_code) ? $request->ifsc_code : '0';
				$employee->target = isset($request->target) ? $request->target : '0';
				$employee->salary = isset($request->salary) ? $request->salary : 0;
				$employee->collection_area_id = isset($request->collection_area_id) ? $request->collection_area_id : '0';
				$this->applyDayAllowance($employee, $request);
				$employee->commission = isset($request->commission) ? $request->commission : 0;
				$employee->incentive = isset($request->incentive) ? $request->incentive : 0;
				$employee->created_by = isset($request->created_by) ? $request->created_by : 0;
                $employee->status = isset($request->status) ? ($request->status) : 1;
                // print_r($employee);exit;
				if($employee->save()) {
					if($employee->tenant_id == '30'){
					$employee->employee_code = 'RCPT-'.sprintf('%05d', $employee_code->id);
					$employee->save();	
					} else {
						
					}
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_employee_added');
					$response['data'] = $employee;
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
				$response['statusee'] = $e;
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }

    
    public function update_employee(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_type' => 'required',
			'first_name' => 'required',
            'mobile_no' => 'required',
			'role_id' => 'required',
			'designation_id' => 'required',
			'salary' => 'required',
			'commission' => 'required',
			'incentive' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $employee = Employee::find($request->id);
					$employee->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
					$employee->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
					$employee->employee_type = isset($request->employee_type) ? $request->employee_type : 0;
					$employee->saluation = isset($request->saluation) ? $request->saluation : '';
					$employee->first_name = isset($request->first_name) ? $request->first_name : '';
					$employee->last_name = isset($request->last_name) ? $request->last_name : '';
					$employee->gender = isset($request->gender) ? $request->gender : 0;
					if(isset($request->dob)) {
						$s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
						$employee->dob = date('Y-m-d',strtotime($s_date)); 
					}
					$employee->photo = isset($request->photo) ? $request->photo : '';
					$employee->collection_agent = ($request->collection_agent=="true") ? 1 : '';
					$employee->phone_no = isset($request->phone_no) ? $request->phone_no : '';
					$employee->mobile_no = isset($request->mobile_no) ? $request->mobile_no : '';
					$employee->email = isset($request->email) ? $request->email : '';
					$employee->adhar_card = isset($request->adhar_card) ? $request->adhar_card : '';
					$employee->driving_license = isset($request->driving_license) ? $request->driving_license : '';
					$employee->pan_no = isset($request->pan_no) ? $request->pan_no : '';
					$employee->ration_card_no = isset($request->ration_card_no) ? $request->ration_card_no : '';
					$employee->father_name = isset($request->father_name) ? $request->father_name : '';
					$employee->spouse_name = isset($request->spouse_name) ? $request->spouse_name : '';
					$employee->door_no = isset($request->door_no) ? $request->door_no : '';
					$employee->building_name = isset($request->building_name) ? $request->building_name : '';
					$employee->address_line_1 = isset($request->address_line_1) ? $request->address_line_1 : '';
					$employee->address_line_2 = isset($request->address_line_2) ? $request->address_line_2 : '';
					$employee->landmark = isset($request->landmark) ? $request->landmark : '';
					$employee->state_id = isset($request->state_id) ? $request->state_id : 0;
					$employee->district_id = isset($request->district_id) ? $request->district_id : 0;
					$employee->city_id = isset($request->city_id) ? $request->city_id : 0;
					$employee->pincode = isset($request->pincode) ? $request->pincode : '';
					$employee->communication_door_no = isset($request->communication_door_no) ? $request->communication_door_no : '';
					$employee->communication_building_name = isset($request->communication_building_name) ? $request->communication_building_name : '';
					$employee->communication_address_line_1 = isset($request->communication_address_line_1) ? $request->communication_address_line_1 : '';
					$employee->communication_address_line_2 = isset($request->communication_address_line_2) ? $request->communication_address_line_2 : '';
					$employee->communication_land_mark = isset($request->communication_land_mark) ? $request->communication_land_mark : '';
					$employee->communication_state_id = isset($request->communication_state_id) ? $request->communication_state_id : 0;
					$employee->communication_district_id = isset($request->communication_district_id) ? $request->communication_district_id : 0;
					$employee->communication_city_id = isset($request->communication_city_id) ? $request->communication_city_id : 0;
					$employee->communication_pincode = isset($request->communication_pincode) ? $request->communication_pincode : '';
					$employee->designation_id = isset($request->designation_id) ? $request->designation_id : 0;
					$employee->role_id = isset($request->role_id) ? $request->role_id : 0;
					$employee->esi = isset($request->esi) ? $request->esi : '';
					$employee->pf = isset($request->pf) ? $request->pf : '';
					$employee->blood_group = isset($request->blood_group) ? $request->blood_group : '';
					$employee->qualification = isset($request->qualification) ? $request->qualification : '';
					$employee->previous_company_details = isset($request->previous_company_details) ? $request->previous_company_details : '';
					if(isset($request->joining_date)) {
						$s_date = ($request->joining_date['year']).'-'.sprintf('%02d',($request->joining_date['month'])).'-'.sprintf('%02d',($request->joining_date['day'])); 
						$employee->joining_date = date('Y-m-d',strtotime($s_date)); 
					}
					$employee->experience = isset($request->experience) ? $request->experience : '';
					$employee->verification_on_call_by = isset($request->verification_on_call_by) ? $request->verification_on_call_by : '';
					$employee->last_month_salary = isset($request->last_month_salary) ? $request->last_month_salary : '';
					$employee->holder_name = isset($request->holder_name) ? $request->holder_name : '';
					$employee->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
					$employee->account_no = isset($request->account_no) ? $request->account_no : '';
					$employee->ifsc_code = isset($request->ifsc_code) ? $request->ifsc_code : '';
					$employee->target = isset($request->target) ? $request->target : '';
					$employee->salary = isset($request->salary) ? $request->salary : 0;
					$employee->collection_area_id = isset($request->collection_area_id) ? $request->collection_area_id : '';
					$this->applyDayAllowance($employee, $request);
					$employee->commission = isset($request->commission) ? $request->commission : 0;
					$employee->incentive = isset($request->incentive) ? $request->incentive : 0;
                    $employee->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($employee->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_employee_updated');
                        $response['data'] = $employee;
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function get_employees(Request $request) { 
        try {
            $cond = $request->conditions; 
			
            //$employees = Employee::where($cond)->orderBy('id','desc')->get();
			$employees = Employee::orderBy('id','desc')->get();
            $i=1;
            foreach($employees as $employee) {
            	$employee->sno = $i;
                $employee->tenant_info = isset($employee->tenant_det) ? ($employee->tenant_det) : [];
                $employee->branch_info = ($employee->branch_det) ? ($employee->branch_det) : [];
                $employee->branch_name = ($employee->branch_det) ? ($employee->branch_det->branch_name) : '';
                //$employee->emp_name = $employee->first_name.' '.$employee->last_name;
                $employee->state_info = ($employee->state_det) ? ($employee->state_det) : [];
                $employee->district_info = ($employee->district_det) ? ($employee->district_det) : [];
                $employee->city_info = ($employee->city_det) ? ($employee->city_det) : [];
                $employee->role_name = ($employee->role_det) ? ($employee->role_det->role_name) : [];
                $employee->communication_state_info = ($employee->communication_state_det) ? ($employee->communication_state_det) : [];
                $employee->communication_district_info = ($employee->communication_district_det) ? ($employee->communication_district_det) : [];
                $employee->communication_city_info = ($employee->communication_city_det) ? ($employee->communication_city_det) : [];
                $employee->designation_info = ($employee->designation_det) ? ($employee->designation_det) : [];
                $employee->role_info = ($employee->role_det) ? ($employee->role_det) : [];
                $employee->bank_info = ($employee->bank_det) ? ($employee->bank_det) : [];
				$employee->collection_area_info = isset($employee->collection_area_det) ? ($employee->collection_area_det) : [];
				$employee->dob = ($employee->dob!=0) ? date('d/m/Y',strtotime($employee->dob)) : '';
				$employee->joining_date = ($employee->joining_date!=0) ? date('d/m/Y',strtotime($employee->joining_date)) : '';
				$employee->photo_for_view = ($employee->photo!='') ? env('APP_URL').Storage::url('app/uploads/employee_photos/'.$employee->photo) : '';
				
				if($employee->tenant_id == '30'){
					$employee->full_name = $employee->first_name.' '.$employee->last_name . ' / ' . $employee->employee_code;
					} else {
					$employee->full_name = $employee->first_name.' '.$employee->last_name;	
					}
				$employee->is_delete=0;
				$userdetails=User::where('employee_id','=',$employee->id)->get();
				$collectiondetails=CollectionAreaMapping::where('employee_id','=',$employee->id)->get();
				if(count($userdetails)>0 || count($collectiondetails)>0){
					$employee->is_delete=0;
				}
				$i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employees;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function list_employees(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $employees = Employee::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $employees = Employee::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($employees as $employee) {
                $employee->sno = $i;
                $employee->tenant_info = isset($employee->tenant_det) ? ($employee->tenant_det) : [];
                $employee->branch_info = ($employee->branch_det) ? ($employee->branch_det) : [];
                $employee->state_info = ($employee->state_det) ? ($employee->state_det) : [];
                $employee->district_info = ($employee->district_det) ? ($employee->district_det) : [];
                $employee->city_info = ($employee->city_det) ? ($employee->city_det) : [];
                $employee->communication_state_info = ($employee->communication_state_det) ? ($employee->communication_state_det) : [];
                $employee->communication_district_info = ($employee->communication_district_det) ? ($employee->communication_district_det) : [];
                $employee->communication_city_info = ($employee->communication_city_det) ? ($employee->communication_city_det) : [];
                $employee->designation_info = ($employee->designation_det) ? ($employee->designation_det) : [];
                $employee->role_info = ($employee->role_det) ? ($employee->role_det) : [];
                $employee->bank_info = ($employee->bank_det) ? ($employee->bank_det) : [];
				$employee->joining_date = ($employee->joining_date!=0) ? date('d/m/Y',strtotime($employee->joining_date)) : '';
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $employees;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_employee(Request $request) {
        try {
            if(isset($request->id)) {
                $employee = Employee::find($request->id); 
                $employee->deleted_by = $request->deleted_by;
                $employee->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($employee->save()) {
                    $employee->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_employee_deleted');
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
                $employee = Employee::find($request->id); 
                $employee->status = ($employee->status) ? 0 : 1;
                if($employee->save()) {
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

    public function employee_file_upload(Request $request) {
        try {
            if ($request->hasFile('photo')) {
				$photo = '';	
	            if ($request->file('photo')->isValid())
	            {  
                   $photo = $request->file('photo')->hashName();
                   $location1 = $request->file('photo')->store('uploads/employee_photos');
               	}
				$response['file_name'] = $photo;
           }
           	else {
				$response['file_name'] = '';
			}
           	$response['status'] = 'Success';
           	return response()->json($response,200);
       	}
		catch(\Exception $e) { 
			$response['status'] = 'Error';
			$response['msg'] = \Lang::get('api.global_error');
           	return response()->json($response,200);
       	}
	}
	
	public function get_collection_employees(Request $request) {
        try {
            $cond = $request->conditions;
        	$cond['collection_agent'] = '1'; 
            $employees = Employee::where($cond)->orderBy('id','desc')->get();
            foreach($employees as $employee) {
                $employee->tenant_info = isset($employee->tenant_det) ? ($employee->tenant_det) : [];
                $employee->branch_info = ($employee->branch_det) ? ($employee->branch_det) : [];
                $employee->state_info = ($employee->state_det) ? ($employee->state_det) : [];
                $employee->district_info = ($employee->district_det) ? ($employee->district_det) : [];
                $employee->city_info = ($employee->city_det) ? ($employee->city_det) : [];
                $employee->communication_state_info = ($employee->communication_state_det) ? ($employee->communication_state_det) : [];
                $employee->communication_district_info = ($employee->communication_district_det) ? ($employee->communication_district_det) : [];
                $employee->communication_city_info = ($employee->communication_city_det) ? ($employee->communication_city_det) : [];
                $employee->designation_info = ($employee->designation_det) ? ($employee->designation_det) : [];
                $employee->role_info = ($employee->role_det) ? ($employee->role_det) : [];
                $employee->bank_info = ($employee->bank_det) ? ($employee->bank_det) : [];
				$employee->collection_area_info = isset($employee->collection_area_det) ? ($employee->collection_area_det) : [];
				$employee->dob = ($employee->dob!=0) ? date('d/m/Y',strtotime($employee->dob)) : '';
				$employee->joining_date = ($employee->joining_date!=0) ? date('d/m/Y',strtotime($employee->joining_date)) : '';
				$employee->photo_for_view = ($employee->photo!='') ? env('APP_URL').Storage::url('app/uploads/employee_photos/'.$employee->photo) : '';
				$employee->full_name = $employee->first_name.' '.$employee->last_name;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employees;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

  	public function app_store_collection_remarks(Request $request)
  	{
	    try
	    {
		    $validator=Validator::make($request->all(),[
		        'tenant_id'=>'required',
		        'remark_name'=>'required',
		    ]); 
		    if($validator->fails()){
		        $response['status']="Error";
		        $response['msg']=$validator->error()->first();
		        return response()->json($response,401);
		    }
		    else {
		        try { 
		        	$col_remark =  CollectionRemark::where(array('remark_name'=>$request->remark_name,'tenant_id'=>$request->tenant_id))->get();       
                    if(count($col_remark)>0)
                    {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.collection_remarks_already_exist');
                        return response()->json($response, 200);
                    } 
		        	$col_remark=new CollectionRemark(); 
		          	$col_remark->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
		          	$col_remark->remark_name = isset($request->remark_name) ? ($request->remark_name) : '';
		          	$col_remark->created_by = isset($request->created_by) ? ($request->created_by) : 0;
		          	$col_remark->status = isset($request->status) ? ($request->status) : 1;
		          	if($col_remark->save()){
		            	$response['status']="Success";
		            	$response['msg']=\Lang::get('api.success_collection_remark_added');
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
	    catch(\Exception $e)
	    {
	      	$response['status'] = 'Error';
	      	$response['msg'] = \Lang::get('api.global_error');
	      	return response()->json($response, 401);
	    }
  	}

  	public function app_update_collection_remarks(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
		    'remark_name'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $col_remark =  CollectionRemark::where(array('remark_name'=>$request->remark_name,'tenant_id'=>$request->tenant_id))->get();       
                    if(count($col_remark)>0)
                    {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.collection_remarks_already_exist');
                        return response()->json($response, 200);
                    } 
                    $col_remark = CollectionRemark::find($request->id); 
                   	$col_remark->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
		          	$col_remark->remark_name = isset($request->remark_name) ? ($request->remark_name) : '';
		          	$col_remark->status = isset($request->status) ? ($request->status) : 1;
                    $col_remark->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($col_remark->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_collection_remark_updated');
                        $response['data'] = $col_remark;
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

  	public function app_get_collection_remarks(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $coll_remarks = CollectionRemark::where($cond)->orderBy('id','desc')->get();
            $i=1; 
            foreach($coll_remarks as $coll_remark) {
            	$coll_remark->sno = $i;
                $coll_remark->tenant_info = isset($coll_remark->tenant_det) ? ($coll_remark->tenant_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $coll_remarks;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

  	public function app_list_collection_remarks(Request $request) 
  	{
    	try { 
	      	$collect_remark = CollectionRemark::where('tenant_id','=',$request->tenant_id)->orderBy('id','desc')->get();     
	      	$i=1; 
	      	$response_data = [];
	      	foreach($collect_remark as $cr) 
	      	{
				$cr->sno = $i;
                $cr->tenant_info = isset($cr->tenant_det) ? ($cr->tenant_det) : [];
                $i++;
	      	}
	  		$response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $collect_remark;
            return response()->json($response,200);  
    	}
	    catch(\Exception $e) {
	      	$response['status'] = 'Error';
	      	$response['msg'] = \Lang::get('api.global_error');
	      	return response()->json($response, 401);
    	}
  	}

  	Public function app_delete_collection_remarks(Request $request)
	{
		try {
            if(isset($request->id)) {
                $remark = CollectionRemark::find($request->id); 
                $remark->deleted_by = $request->deleted_by;
                $remark->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($remark->save()) {
                    $remark->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_collection_remark_deleted');
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

  	public function app_store_remarks(Request $request)
{
    try {

        $state = new Remark();

        $state->tenant_id       = $request->tenant_id ?? 0;
        $state->branch_id       = $request->branch_id ?? 0;
        $state->customer_id     = $request->customer_id ?? 0;
        $state->followup_status = $request->followup_status ?? 2;
        $state->employee_id     = $request->employee_id ?? 0;

        // Get remark name from collection_remarks table
        if(isset($request->remark_id)) {
            $remark_name = CollectionRemark::where('id', $request->remark_id)->value('remark_name');
            $state->remarks = $remark_name ?? '';
        } else {
            $state->remarks = 'test';
        }

        // Remark Date
        if(isset($request->remark_date)) {

            $s_date = $request->remark_date['year'].'-'.
                      sprintf('%02d',$request->remark_date['month']).'-'.
                      sprintf('%02d',$request->remark_date['day']);

            $state->remark_date = date('Y-m-d', strtotime($s_date));

        } else {
            $state->remark_date = '0000-00-00';
        }

        // Current Time
        $state->remark_time = date('H:i:s');

        // Next Followup Date
        if(isset($request->next_followup_date)) {

            $s_date = $request->next_followup_date['year'].'-'.
                      sprintf('%02d',$request->next_followup_date['month']).'-'.
                      sprintf('%02d',$request->next_followup_date['day']);

            $state->next_followup_date = date('Y-m-d', strtotime($s_date));

        } else {
            $state->next_followup_date = '0000-00-00';
        }

        $state->created_by = $request->created_by ?? 0;
        $state->status     = $request->status ?? 1;

        if($state->save()){

            return response()->json([
                'status' => 'Success',
                'msg' => \Lang::get('api.success_remark_added')
            ],200);

        }else{

            return response()->json([
                'status' => 'Error',
                'msg' => \Lang::get('api.global_error')
            ],401);

        }

    } catch(\Exception $e){

        return response()->json([
            'status' => 'Error',
            'msg' => $e->getMessage()
        ],500);

    }
}

	public function app_update_remarks(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
	        'customer_id'=>'required',
	        'remarks'=>'required',
	        'remark_date'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {                    
                    $remark = Remark::find($request->id); 
                   	$remark->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
		          	$remark->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
		          	$remark->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
		          	$remark->followup_status = isset($request->followup_status) ? ($request->followup_status) : 0;
		          	$remark->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
		          	$remark->remarks = isset($request->remarks) ? ($request->remarks) : 0;
		          	if(isset($request->remark_date)) {
	                    $s_date = ($request->remark_date['year']).'-'.sprintf('%02d',($request->remark_date['month'])).'-'.sprintf('%02d',($request->remark_date['day'])); 
	                    $remark->remark_date = date('Y-m-d',strtotime($s_date)); 
	                }
	                else {
	                    $remark->remark_date = '0000-00-00';
	                }
		          	$remark->remark_time = time("h:i:s");
		          	if(isset($request->next_followup_date)) {
	                    $s_date = ($request->next_followup_date['year']).'-'.sprintf('%02d',($request->next_followup_date['month'])).'-'.sprintf('%02d',($request->next_followup_date['day'])); 
	                    $remark->next_followup_date = date('Y-m-d',strtotime($s_date)); 
	                }
	                else {
	                    $remark->next_followup_date = '0000-00-00';
	                }
		          	$remark->status = isset($request->status) ? ($request->status) : 1;
                    $remark->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($remark->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_remark_updated');
                        $response['data'] = $remark;
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

	public function app_get_remarks(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $remarks = Remark::where($cond)->orderBy('id','desc')->get();
           	$i=1; 
            foreach($remarks as $remark) {
            	$remark->sno = $i;
                $remark->remark_date = ($remark->remark_date!=0) ? date('d/m/Y',strtotime($remark->remark_date)) : '';
                $remark->next_followup_date = ($remark->next_followup_date!=0) ? date('d/m/Y',strtotime($remark->next_followup_date)) : '';
                $remark->tenant_info = isset($remark->tenant_det) ? ($remark->tenant_det) : [];
                $remark->branch_info = isset($remark->branch_det) ? ($remark->branch_det) : [];
                $remark->customer_info = isset($remark->customer_det) ? ($remark->customer_det) : [];  
				$remark->employee_info = isset($remark->employee_det) ? ($remark->employee_det) : [];  
                //$remark->collection_remark_info = isset($remark->collection_remark_det) ? ($remark->collection_remark_det) : [];
		        $followup_status = isset($remark->followup_status) ? ($remark->followup_status) : 0;
		        if($followup_status ==1)
		        {
		        	$remark->followup_status_name = 'Completed';
		        }
		        else if($followup_status ==2)
		        {
		        	$remark->followup_status_name = 'Pending';
		        }
		        else
		        {
		        	$remark->followup_status_name = '';
		        }
                $i++; 
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $remarks;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function app_list_remarks(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $remarks = Remark::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $remarks = Remark::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($remarks as $remark) {
                $remark->sno = $i;
               	$remark->remark_date = ($remark->remark_date!=0) ? date('d/m/Y',strtotime($remark->remark_date)) : '';
                $remark->next_followup_date = ($remark->next_followup_date!=0) ? date('d/m/Y',strtotime($remark->next_followup_date)) : '';
                $remark->tenant_info = isset($remark->tenant_det) ? ($remark->tenant_det) : [];
                $remark->branch_info = isset($remark->branch_det) ? ($remark->branch_det) : [];
                $remark->customer_info = isset($remark->customer_det) ? ($remark->customer_det) : [];  
               // $remark->collection_remark_info = isset($remark->collection_remark_det) ? ($remark->collection_remark_det) : [];
		        $followup_status = isset($remark->followup_status) ? ($remark->followup_status) : 0;
		        if($followup_status ==1)
		        {
		        	$remark->followup_status = 'Completed';
		        }
		        else if($followup_status ==2)
		        {
		        	$remark->followup_status = 'Pending';
		        }
		        else
		        {
		        	$remark->followup_status = '';
		        }   
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $remarks;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	Public function app_delete_remarks(Request $request)
	{
		try {
            if(isset($request->id)) {
                $remark = Remark::find($request->id); 
                $remark->deleted_by = $request->deleted_by;
                $remark->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($remark->save()) {
                    $remark->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_remark_deleted');
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
	
	
	public function store_employee_monthly_salary(Request $request)
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
					
					
						for($i=0; $i<$count; $i++)
						{	
					
                                $emp_salary_det = new EmployeeMonthlySalaryDetail();
								
                                $emp_salary_det['tenant_id'] = isset($request->tenant_id) ? $request->tenant_id : 0;
                                $emp_salary_det['branch_id']  = isset($request->branch_id) ? $request->branch_id : 0;
                                $emp_salary_det['working_days']  = isset($request->working_days) ? $request->working_days : 0;
                                 if(isset($request->month_year)) {
									$c_date = ($request->month_year['year']).'-'.sprintf('%02d',($request->month_year['month'])).'-'.sprintf('%02d',($request->month_year['day'])); 
									$emp_salary_det->month_year = date('Y-m-d',strtotime($c_date)); 
								}
								else {
									$emp_salary_det->month_year = '0000-00-00';
								}           
                                //$emp_salary_det['month_year'] = isset($request->month_year) ? $request->month_year : 0;
                                $emp_salary_det['employee_id'] = isset($request->Employees[$i]['employee_id']) ? $request->Employees[$i]['employee_id'] : 0;
                                $emp_salary_det['salary'] = isset($request->Employees[$i]['salary']) ? $request->Employees[$i]['salary'] : 0;
								
                                
                                $emp_salary_det['remarks'] = isset($request->Employees[$i]['remarks']) ? $request->Employees[$i]['remarks'] : '';
                                $emp_salary_det['created_by']  = isset($request->created_by) ? $request->created_by : 0; 
                                $emp_salary_det['status'] = isset($request->status) ? ($request->status) : 1;
								 
                                $emp_salary_det->save(); 
                            }
							$response['status'] = 'Success';
						$response['msg'] = \Lang::get('api.success_employee_salary_added');
						return response()->json($response,200);	
				}
						
						
				}
				catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,401);
			}
		}

		}

		
	 public function update_employee_monthly_salary(Request $request) {
        try {
            $count = count($request->Employees);
            if($count>0) {
				for($i=0; $i<$count; $i++)
				{ 
                    $employee_salary = EmployeeMonthlySalaryDetail::find($request->Employees[$i]['id']); 
					$employee_salary->working_days  = isset($request->working_days) ? $request->working_days : 0;
					$employee_salary->salary = isset($request->Employees[$i]['salary']) ? $request->Employees[$i]['salary'] : 0;
				    $employee_salary->updated_by  = isset($request->updated_by) ? $request->updated_by : 0;  
                    $employee_salary->save();
                }
				$response['status'] = "Success";
				$response['msg'] = \Lang::get('api.success_employee_salary_updated');
				$response['data'] = $employee_salary;
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
	
	public function get_employee_salary(Request $request) {
        try { 
            $cond = $request->conditions; 
            $branch_id = $cond['branch_id'];
            $att_year = $cond['att_year'];
            $att_month = $cond['att_month'];
            $att_date = $cond['att_date'];
            $concat_date = $att_year.'-'.$att_month.'-'.$att_date;
            $att_date = date('Y-m-d',strtotime($concat_date));
            $employee_attendances = EmployeeMonthlySalaryDetail::where(array('branch_id'=>$branch_id,'month_year'=>$att_date))->get();
            foreach($employee_attendances as $employee_attendance) {
                $employee_attendance->tenant_info = isset($employee_attendance->tenant_det) ? ($employee_attendance->tenant_det) : [];
                $employee_attendance->branch_info = ($employee_attendance->branch_det) ? ($employee_attendance->branch_det) : [];
                $employee_attendance->employee_name = ($employee_attendance->employee_det) ? ($employee_attendance->employee_det->first_name.' '.$employee_attendance->employee_det->last_name) : [];
                $employee_attendance->month_year = ($employee_attendance->month_year!=0) ? date('d/m/Y',strtotime($employee_attendance->month_year)) : ''; 
                $employee_attendance= EmployeeMonthlySalaryDetail::where('employee_id',$employee_attendance->employee_id)->where('month_year',$att_date)->get();
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employee_attendances;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            //$response['msg'] = \Lang::get('api.global_error');
			$response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function list_employee_monthly_salary(Request $request) {
        try { 
          if(isset($request->tenant_id)) {
                $att_list = EmployeeMonthlySalaryDetail::where('tenant_id',$request->tenant_id)->select('tenant_id','branch_id','month_year')->distinct()->get();
            }
            else {
                $att_list = EmployeeMonthlySalaryDetail::select('tenant_id','branch_id','month_year')->distinct()->get();
            }

           
            $i=1;
            foreach($att_list as $al) {
                $al->sno = $i;
				$al->att_view_date = date('d/m/Y',strtotime($al->month_year));
                $al->att_year = date('Y',strtotime($al->month_year));
                $al->att_month = date('m',strtotime($al->month_year));
                $al->att_date = date('d',strtotime($al->month_year));
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
	public function delete_employee_salary(Request $request) {
        try {
            if(isset($request->id)) {
                $employee_attendance = EmployeeMonthlySalaryDetail::find($request->id); 
                $employee_attendance->deleted_by = $request->deleted_by;
                $employee_attendance->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($employee_attendance->save()) {
                    $employee_attendance->delete();
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

    // Returns employees for login "Map Employees". Default wide_map: all employees not in roles
    // strictly above the selected login role (excludes CEO chain), so peers/siblings (e.g. Office vs
    // Collection) appear; branch filtering is done on the client.
    public function get_employees_for_role_tree(Request $request)
    {
        try {
            $tenant_id = isset($request->tenant_id) ? (int)$request->tenant_id : 0;
            $role_id = isset($request->role_id) ? (int)$request->role_id : 0;

            if ($tenant_id <= 0 || $role_id <= 0) {
                $response['status'] = 'Error';
                $response['msg'] = 'tenant_id and role_id are required.';
                return response()->json($response, 401);
            }

            $wideMap = !$request->has('wide_map') || filter_var($request->input('wide_map'), FILTER_VALIDATE_BOOLEAN);
            $ancestorIds = $this->getAncestorRoleIds($role_id, $tenant_id);

            if ($wideMap && !empty($ancestorIds)) {
                $employees = Employee::with(['tenant_det', 'branch_det', 'role_det'])
                    ->where('tenant_id', $tenant_id)
                    ->whereNotIn('role_id', $ancestorIds)
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $roleIds = $this->getStrictDescendantRoleIds($role_id, $tenant_id);

                if (empty($roleIds)) {
                    $response['status'] = 'Success';
                    $response['msg'] = '';
                    $response['data'] = [];
                    return response()->json($response, 200);
                }

                $employees = Employee::with(['tenant_det', 'branch_det', 'role_det'])
                    ->where('tenant_id', $tenant_id)
                    ->whereIn('role_id', $roleIds)
                    ->orderBy('id', 'desc')
                    ->get();
            }

            $userId = isset($request->user_id) ? (int) $request->user_id : 0;
            $scope = $this->resolveUserLoginScope($userId, $tenant_id);
            if ($userId > 0) {
                if (!empty($scope['employees'])) {
                    $employees = $employees->whereIn('id', $scope['employees']);
                } elseif (!empty($scope['branches'])) {
                    $employees = $employees->whereIn('branch_id', $scope['branches']);
                }
            }

            foreach ($employees as $employee) {
                $employee->branch_name = ($employee->branch_det) ? $employee->branch_det->branch_name : '';
                $employee->role_name = ($employee->role_det) ? $employee->role_det->role_name : '';
                $employee->employee_name = $employee->first_name . ' ' . $employee->last_name;
                $employee->is_delete = 0;
            }

            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $employees->values();
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    // Branches that have at least one mappable employee for this login role (matches wide_map logic).
    public function get_branches_for_role_tree(Request $request)
    {
        try {
            $tenant_id = isset($request->tenant_id) ? (int)$request->tenant_id : 0;
            $role_id = isset($request->role_id) ? (int)$request->role_id : 0;

            if ($tenant_id <= 0 || $role_id <= 0) {
                $response['status'] = 'Error';
                $response['msg'] = 'tenant_id and role_id are required.';
                return response()->json($response, 401);
            }

            $wideMap = !$request->has('wide_map') || filter_var($request->input('wide_map'), FILTER_VALIDATE_BOOLEAN);
            $ancestorIds = $this->getAncestorRoleIds($role_id, $tenant_id);

            if ($wideMap && !empty($ancestorIds)) {
                $branchIds = Employee::where('tenant_id', $tenant_id)
                    ->whereNotIn('role_id', $ancestorIds)
                    ->select('branch_id')
                    ->distinct()
                    ->get()
                    ->pluck('branch_id')
                    ->toArray();
            } else {
                $roleIds = $this->getStrictDescendantRoleIds($role_id, $tenant_id);

                if (empty($roleIds)) {
                    $response['status'] = 'Success';
                    $response['msg'] = '';
                    $response['data'] = [];
                    return response()->json($response, 200);
                }

                $branchIds = Employee::where('tenant_id', $tenant_id)
                    ->whereIn('role_id', $roleIds)
                    ->select('branch_id')
                    ->distinct()
                    ->get()
                    ->pluck('branch_id')
                    ->toArray();
            }

            $userId = isset($request->user_id) ? (int) $request->user_id : 0;
            $scope = $this->resolveUserLoginScope($userId, $tenant_id);
            if ($userId > 0 && !empty($scope['branches'])) {
                $branchIds = array_values(array_intersect($branchIds, $scope['branches']));
            }

            if (empty($branchIds)) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = [];
                return response()->json($response, 200);
            }

            $branches = Branch::where('tenant_id', $tenant_id)
                ->whereIn('id', $branchIds)
                ->orderBy('id', 'desc')
                ->get();

            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $branches;
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
