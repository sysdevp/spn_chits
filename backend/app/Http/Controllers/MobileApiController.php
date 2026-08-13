<?php
namespace App\Http\Controllers;
 
use App\Http\Requests\RegisterAuthRequest;
use Carbon\Carbon;
/*use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;*/
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\HeadOffice;
use App\Models\Branch;
use App\Models\State;
use App\Models\District;
use App\Models\City;
use App\Models\LeadManagement;
use App\Models\Followup;
use App\Models\FollowupStatusType;
use App\Models\Device;
use App\Models\DeviceLogin;
use App\Models\CashSettlement;
use App\Models\Customer;
use App\Models\CollectionAreaMapping;
use App\Models\AccountExpense;
use App\Models\AccountHead;
use App\Models\ChitDetail;
use App\Models\BiddingDetail;
use App\Models\Receipt;
use App\Models\Group;
use App\Models\PaymentType;
use App\Models\RoleType;
use App\Models\PettyCashIncome;
use App\Models\CustomerBankDetail;
use App\Models\ChargeType;
use App\Models\BankDetail;
use App\Models\Scheme;
use App\Models\CommitmentAuction;
use App\Models\SlabDue;
use App\Models\InterestMessage;
use App\Models\AdvanceReceipt;
use App\Models\CollectionRemark;
use App\Models\BeforeEnrolReceipt;
use App\Models\OtherCharge;
use App\Models\PigmyCollection;
use App\Models\Remark;
use App\Models\CollectionArea; 
use App\Models\Payment;
use App\Models\EmployeeAttendance;
use App\Models\AuctionOnHoliday;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Session;
use Validator;
use Storage;
use DB;
use Mail;
use DateTime; 
Use Crypt;
use Config;
use Lang;

class MobileApiController extends Controller
{
  public function mobile_login(Request $request)
  {

    $email=$request->email;
      $pass=md5($request->password);
      $_SESSION['db']='';

      $members = DB::connection('mysql1')->table('users')->where('email','=',$email)->where('password','=',$pass)->get();
	
    if(count($members)>0){
      $tenantid=$members[0]->tenant_id;
      $tenentdetails = DB::connection('mysql1')->table('tenants')->where('id','=',$tenantid)->get();
      if(count($tenentdetails)>0){
        $_SESSION['db']=$tenentdetails[0]->slag_url;
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $_SESSION['db']);
        DB::reconnect('mysql');
      
      }else{
        return response()->json([
        'success' => false,
        'message' => 'Invalid Database',
      ], 401);
      }
    }else{
      return response()->json([
        'success' => false,
        'message' => 'Invalid Credentials',
      ], 401);
    }



    $input = $request->only('email', 'password'); 
    $jwt_token = null;
    if (!$jwt_token = JWTAuth::attempt($input)) 
    {
      return response()->json([
         'success' => false,
         'message' => 'Invalid Email or Password',
      ], 401);
    }       
    $logged_user = JWTAuth::user();

    // $user = User::where(array('email'=>$request->email))->get();
    // print_r($user);exit;
    // $is_password_matched = Hash::check($request->password, $user[0]->password);
    // $logged_user='';
    // if($is_password_matched){
    //   $logged_user=$user[0];
    // }

    // print_r($logged_user);exit;
    $logged_user_name='';
    $role_name='';
    $role_type = '';
    $tenant_name='';
    $tenant_gst_no='';
    $tenant_address_1='';
    $tenant_address_2='';
    $state_name ='';
    $district_name ='';
    $branch_name ='';
    $city_name ='';
    $tenant_mobile= '';
    $tenant_phone='';
    $employee_id = '';
    $profilephoto = '';

    if(isset($logged_user))
    {    
      $logged_user_name = isset($logged_user->employee_det) ? ($logged_user->employee_det->first_name) : '';
      $role_details = Role::find($logged_user->role_id);
      if($role_details)
      {
        $role_name = isset($logged_user->role_det) ? ($logged_user->role_det->role_name) : '';
        if($logged_user->role_det)
        {
            $role_type = $logged_user->role_det->role_type_det->role_type_name;
        }
      } 
      if(isset($logged_user->tenant_det))
      {
        $tenant_name = $logged_user->tenant_det->chit_fund_name;
        $tenant_gst_no = $logged_user->tenant_det->gst_no;           

        if(isset($logged_user->branch_det))
        {
          $tenant_address_1 = isset($logged_user->branch_det) ? ($logged_user->branch_det->address_line_1) : '';
          $tenant_address_2 = isset($logged_user->branch_det) ? ($logged_user->branch_det->address_line_2) : '';
          $branch_name = isset($logged_user->branch_det) ? ($logged_user->branch_det->branch_name) : '';
          $state_name = isset($logged_user->branch_det->state_det) ? ($logged_user->branch_det->state_det->state_name) : '';
          $district_name = isset($logged_user->branch_det->district_det) ? ($logged_user->branch_det->district_det->district_name) : '';
          $city_name = isset($logged_user->branch_det->city_det) ? ($logged_user->branch_det->city_det->city_name) : '';
          $tenant_mobile = isset($logged_user->branch_det) ? ($logged_user->branch_det->mobile_no) : '' ;
          $tenant_phone = isset($logged_user->branch_det) ? ($logged_user->branch_det->phone_no) : '';
    // print_r($logged_user->employee_id);exit;
          if($logged_user->employee_id>0){ 
            $employeedetails= Employee::find($logged_user->employee_id);
            $profilephoto = ($employeedetails->photo!='') ? env('APP_URL').Storage::url('app/uploads/employee_photos/'.$employeedetails->photo) : ''; 
          }             
        }
        if(isset($logged_user->employee_det))
        {
          $employee_id = $logged_user->employee_det->id;
        }
      }
    }
    return response()->json([
      'success' => true,
      'token' => $jwt_token,
      'logged_user_id' => $logged_user->id,
      'tenant_id' => (int)($logged_user->tenant_id==0) ? 0 : ($logged_user->tenant_id),
      'branch_id' => (int)$logged_user->branch_id,
      'role_id' => (int)$logged_user->role_id,
      'logged_user_name' => $logged_user_name,
      'branch_name' => $branch_name,
      'role_name' => $role_name,
      'role_type' => $role_type,
      'tenant_name' => $tenant_name,
      'tenant_gst_no' => $tenant_gst_no,
      'tenant_address_1' => $tenant_address_1,
      'tenant_address_2' => $tenant_address_2,
      'state_name' => $state_name,
      'district_name' => $district_name,
      'city_name' => $city_name,
      'tenant_mobile' => $tenant_mobile,
      'tenant_phone' => $tenant_phone,
      'profile_pic' => $profilephoto,
      'db' => $_SESSION['db'],
      'employee_id' => (int)$employee_id,
      'device_id' => '',
      'mobile_no' =>  ''
    ]);
    
  }


  public function mobile_list_tenants(Request $request) {
    try { 
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      if(isset($request->id)) 
      {
        $tenants = Tenant::where('id',$request->id)->orderBy('id','desc')->get();
      }
      else 
      {
        $tenants = Tenant::orderBy('id','desc')->get();
      }
       $i=1;
       $response_data = [];
      foreach($tenants as $tenant) {
          $tenent_det = []; 
          $tenent_det['sno'] = $i;
          $tenent_det['tenant_id'] = $tenant->id;
          $tenent_det['chit_fund_name'] = $tenant->chit_fund_name;
          $tenent_det['phone_no'] = $tenant->phone_no;
          $tenent_det['mobile_no'] = $tenant->mobile_no;
          $tenent_det['address_1'] = '';
          $tenent_det['address_2'] = '';
          $tenent_det['state'] = '';
          $tenent_det['district'] = '';
          $tenent_det['city'] = '';
          if(count($tenant->head_office_det)) {
            $tenent_det['address_1'] = $tenant->head_office_det[0]->address_line_1;
            $tenent_det['address_2'] = $tenant->head_office_det[0]->address_line_2;
            $tenent_det['state'] = $tenant->head_office_det[0]->state_det->state_name;
            $tenent_det['district'] = $tenant->head_office_det[0]->district_det->district_name;
            $tenent_det['city'] = $tenant->head_office_det[0]->city_det->city_name;
          }
          array_push($response_data,$tenent_det);
          $i++;
      }
      if($tenants) {
          return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
    }
  }

  public function mobile_list_branches(Request $request) 
{
    try { 

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $tenantId = $request->tenant_id ?? null;
        $userId = isset($request->user_id) ? (int) $request->user_id : 0;

        $scopeByUser = $userId > 0;
        $allowedBranchIds = null;
        $allowedEmployeeIds = null;

        if ($scopeByUser) {
            $allowedBranchIds = [];
            $allowedEmployeeIds = [];

            $bMap = DB::table('user_branch_mapping')
                ->where('user_id', $userId)
                ->where('status', 1);
            if (!empty($tenantId)) {
                $bMap->where('tenant_id', $tenantId);
            }
            foreach ($bMap->pluck('branch_id') as $bid) {
                $allowedBranchIds[] = (int) $bid;
            }

            $eMap = DB::table('user_employee_mapping')
                ->where('user_id', $userId)
                ->where('status', 1);
            if (!empty($tenantId)) {
                $eMap->where('tenant_id', $tenantId);
            }
            foreach ($eMap->pluck('employee_id') as $eid) {
                $allowedEmployeeIds[] = (int) $eid;
            }

            $user = User::find($userId);
            if ($user) {
                if (empty($allowedBranchIds) && !empty($user->branch_id)) {
                    $allowedBranchIds[] = (int) $user->branch_id;
                }
                if (empty($allowedEmployeeIds) && !empty($user->employee_id)) {
                    $allowedEmployeeIds[] = (int) $user->employee_id;
                }
            }

            $allowedBranchIds = array_values(array_unique(array_filter($allowedBranchIds)));
            $allowedEmployeeIds = array_values(array_unique(array_filter($allowedEmployeeIds)));
        }

        $query = Branch::with(['state_det', 'district_det', 'city_det'])
                        ->orderBy('id', 'desc');

        if (!empty($tenantId)) {
            $query->where('tenant_id', $tenantId);
        }

        if ($scopeByUser) {
            if (empty($allowedBranchIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $allowedBranchIds);
            }
        }

        $branches = $query->get();

        $response_data = [];
        $i = 1;

        foreach ($branches as $branch) {

            $response_data[] = [
                'sno'            => $i++,
                'branch_id'      => $branch->id,
                'tenant_id'      => $branch->tenant_id,
                'head_office_id' => $branch->head_office_id,
                'branch_name'    => $branch->branch_name,
                'branch_code'    => $branch->branch_code,
                'email_id'       => $branch->email_id,
                'phone_no'       => $branch->phone_no,
                'mobile_no'      => $branch->mobile_no,
                'address_line_1' => $branch->address_line_1,
                'address_line_2' => $branch->address_line_2,
                'landmark'       => $branch->landmark,

                // ✅ SAFE ACCESS
                'state_name'     => optional($branch->state_det)->state_name ?? '',
                'district_name'  => optional($branch->district_det)->district_name ?? '',
                'city_name'      => optional($branch->city_det)->city_name ?? '',
            ];
        }

        $employees_data = [];
        if ($scopeByUser && $allowedEmployeeIds !== null) {
            $empQuery = Employee::with(['role_det', 'branch_det'])
                ->orderBy('id', 'desc');
            if (!empty($tenantId)) {
                $empQuery->where('tenant_id', $tenantId);
            }
            if (empty($allowedEmployeeIds)) {
                $empQuery->whereRaw('1 = 0');
            } else {
                $empQuery->whereIn('id', $allowedEmployeeIds);
            }
            $j = 1;
            foreach ($empQuery->get() as $emp) {
                $employees_data[] = [
                    'sno'           => $j++,
                    'employee_id'   => $emp->id,
                    'tenant_id'     => $emp->tenant_id,
                    'branch_id'     => $emp->branch_id,
                    'first_name'    => $emp->first_name,
                    'last_name'     => $emp->last_name,
                    'mobile_no'     => $emp->mobile_no,
                    'role_name'     => optional($emp->role_det)->role_name ?? '',
                    'branch_name'   => optional($emp->branch_det)->branch_name ?? '',
                ];
            }
        }

        $out = [
            'status' => 'Success',
            'data'   => $response_data,
        ];
        if ($scopeByUser) {
            $out['employees'] = $employees_data;
        }

        return response()->json($out, 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()
        ], 500);
    }
}


public function mobile_list_users(Request $request) 
{
    try { 

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $tenantId = $request->tenant_id ?? null;
        $userId = isset($request->user_id) ? (int) $request->user_id : 0;

        $scopeByUser = $userId > 0;
        $allowedBranchIds = null;
        $allowedEmployeeIds = null;

        if ($scopeByUser) {
            $allowedBranchIds = [];
            $allowedEmployeeIds = [];

            $bMap = DB::table('user_branch_mapping')
                ->where('user_id', $userId)
                ->where('status', 1);
            if (!empty($tenantId)) {
                $bMap->where('tenant_id', $tenantId);
            }
            foreach ($bMap->pluck('branch_id') as $bid) {
                $allowedBranchIds[] = (int) $bid;
            }

            $eMap = DB::table('user_employee_mapping')
                ->where('user_id', $userId)
                ->where('status', 1);
            if (!empty($tenantId)) {
                $eMap->where('tenant_id', $tenantId);
            }
            foreach ($eMap->pluck('employee_id') as $eid) {
                $allowedEmployeeIds[] = (int) $eid;
            }

            $user = User::find($userId);
            if ($user) {
                if (empty($allowedBranchIds) && !empty($user->branch_id)) {
                    $allowedBranchIds[] = (int) $user->branch_id;
                }
                if (empty($allowedEmployeeIds) && !empty($user->employee_id)) {
                    $allowedEmployeeIds[] = (int) $user->employee_id;
                }
            }

            $allowedBranchIds = array_values(array_unique(array_filter($allowedBranchIds)));
            $allowedEmployeeIds = array_values(array_unique(array_filter($allowedEmployeeIds)));
        }

        $query = Branch::with(['state_det', 'district_det', 'city_det'])
                        ->orderBy('id', 'desc');

        if (!empty($tenantId)) {
            $query->where('tenant_id', $tenantId);
        }

        if ($scopeByUser) {
            if (empty($allowedBranchIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $allowedBranchIds);
            }
        }

        $branches = $query->get();

        $response_data = [];
        $i = 1;

        foreach ($branches as $branch) {

            $response_data[] = [
                'sno'            => $i++,
                'branch_id'      => $branch->id,
                'tenant_id'      => $branch->tenant_id,
                'head_office_id' => $branch->head_office_id,
                'branch_name'    => $branch->branch_name,
                'branch_code'    => $branch->branch_code,
                'email_id'       => $branch->email_id,
                'phone_no'       => $branch->phone_no,
                'mobile_no'      => $branch->mobile_no,
                'address_line_1' => $branch->address_line_1,
                'address_line_2' => $branch->address_line_2,
                'landmark'       => $branch->landmark,

                // ✅ SAFE ACCESS
                'state_name'     => optional($branch->state_det)->state_name ?? '',
                'district_name'  => optional($branch->district_det)->district_name ?? '',
                'city_name'      => optional($branch->city_det)->city_name ?? '',
            ];
        }

        $employees_data = [];
        if ($scopeByUser && $allowedEmployeeIds !== null) {
            $empQuery = Employee::with(['role_det', 'branch_det'])
                ->orderBy('id', 'desc');
            if (!empty($tenantId)) {
                $empQuery->where('tenant_id', $tenantId);
            }
            if (empty($allowedEmployeeIds)) {
                $empQuery->whereRaw('1 = 0');
            } else {
                $empQuery->whereIn('id', $allowedEmployeeIds);
            }
            $j = 1;
            foreach ($empQuery->get() as $emp) {
                $employees_data[] = [
                    'sno'           => $j++,
                    'employee_id'   => $emp->id,
                    'tenant_id'     => $emp->tenant_id,
                    'branch_id'     => $emp->branch_id,
                    'first_name'    => $emp->first_name,
                    'last_name'     => $emp->last_name,
                    'mobile_no'     => $emp->mobile_no,
                    'role_name'     => optional($emp->role_det)->role_name ?? '',
                    'branch_name'   => optional($emp->branch_det)->branch_name ?? '',
                ];
            }
        }

        $out = [
            'status' => 'Success',
            'data'   => $response_data,
        ];
        if ($scopeByUser) {
            $out['employees'] = $employees_data;
        }

        return response()->json($out, 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()
        ], 500);
    }
}


  public function mobile_store_lead_management(Request $request)
{
    $validator = Validator::make($request->all(), [
        'tenant_id'           => 'required',
        'branch_id'           => 'required',
        'employee_id'         => 'required',
        'lead_customer_name'  => 'required',
        'db'                  => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg'    => $validator->errors()->first()
        ], 422);
    }

    try {

        /* ============================
           DB SWITCH
        ============================ */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* ============================
           DATE FORMAT CONVERSION
        ============================ */
        $followupDate = null;
        if (is_array($request->followup_date)) {
            $followupDate = sprintf(
                '%04d-%02d-%02d',
                $request->followup_date['year'],
                $request->followup_date['month'],
                $request->followup_date['day']
            );
        }

        $nextFollowupDate = null;
        if (is_array($request->next_followup_date)) {
            $nextFollowupDate = sprintf(
                '%04d-%02d-%02d',
                $request->next_followup_date['year'],
                $request->next_followup_date['month'],
                $request->next_followup_date['day']
            );
        }

        /* ============================
           SAVE LEAD
        ============================ */
        $leadmanagement = new LeadManagement();
        $leadmanagement->tenant_id   = $request->tenant_id;
        $leadmanagement->branch_id   = $request->branch_id;
        $leadmanagement->employee_id = $request->employee_id;
        $leadmanagement->lead_customer_name = $request->lead_customer_name;
        $leadmanagement->phone_no    = $request->phone_no ?? null;
        $leadmanagement->mobile_no   = $request->mobile_no ?? null;
        $leadmanagement->email_id    = $request->email_id ?? null;
        $leadmanagement->address_line_1 = $request->address_line_1 ?? null;
        $leadmanagement->address_line_2 = $request->address_line_2 ?? null;
        $leadmanagement->landmark    = $request->landmark ?? null;
        $leadmanagement->state_id    = $request->state_id ?: null;
        $leadmanagement->district_id = $request->district_id ?: null;
        $leadmanagement->city_id     = $request->city_id ?: null;
        $leadmanagement->pincode     = $request->pincode ?: null;
        $leadmanagement->created_by  = $request->created_by ?? 0;
        $leadmanagement->status      = $request->status ?? 1;
        $leadmanagement->followup_status_type_id = $request->followup_status_type_id ?? 0;

        $leadmanagement->save();

        /* ============================
           SAVE FOLLOWUP
        ============================ */
        $followups = new Followup();
        $followups->tenant_id   = $request->tenant_id;
        $followups->branch_id   = $request->branch_id;
        $followups->employee_id = $request->employee_id;
        $followups->lead_management_id = $leadmanagement->id;
        $followups->followup_date      = $followupDate;
        $followups->next_followup_date = $nextFollowupDate;
        $followups->remarks            = $request->remarks ?? null;
        $followups->created_by         = $request->created_by ?? 0;
        $followups->followup_status_type_id = $request->followup_status_type_id ?? 0;
        $followups->status = $request->status ?? 1;
        $followups->save();

        return response()->json([
            'status' => 'Success',
            'msg'    => 'Lead saved successfully'
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()   // helpful for debugging
        ], 500);
    }
}


   public function mobile_list_leads(Request $request) 
   {
    try { 
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond =[];
      if(isset($request->employee_id))
      {
        $cond['employee_id'] = $request->employee_id;
      }
      if(isset($request->tenant_id))
      {
        $cond['tenant_id'] = $request->tenant_id;
      }      
      $leadmanagements = LeadManagement::where($cond)->get();
      $response_data = [];
      $i=1; 
      foreach($leadmanagements as $leadmanagement) 
      {           
        $lead_det = [];
        $lead_det['sno'] = $i;
        $lead_det['lead_id'] = $leadmanagement->id;
        $lead_det['tenant_id'] = $leadmanagement->tenant_id;
        $lead_det['lead_customer_name'] = $leadmanagement->lead_customer_name;
        $lead_det['phone_no'] = $leadmanagement->phone_no;
        $lead_det['mobile_no'] = $leadmanagement->mobile_no;
        $lead_det['lead_generated_on'] = $leadmanagement->created_at->format('Y-m-d');
        $lead_det['lead_last_followupdates'] = '';
        $lead_det['last_followup'] = '';
        $lead_det['next_followup_date'] = '';
        $lead_det['lead_last_followupstatus'] = '';
        $lead_det['lead_entrolled'] = '';   
        $customer_max_id = Followup::where('lead_management_id','=',$leadmanagement->id)->orderBy('created_at','asc')->get();                  
        foreach($customer_max_id as $max_id)
        { 
          $lead_det['last_followup'] =isset($max_id->followup_date) ? ($max_id->followup_date) : "0000-00-00";  
          $lead_det['next_followup_date'] =isset($max_id->next_followup_date) ? ($max_id->next_followup_date) : "0000-00-00";
        }

        $followup_order=Followup::where('lead_management_id',$leadmanagement->id)->orderBy('created_at','desc')->get(); 
        $lead_det['lead_last_followupstatus'] =isset($followup_order[0]->followup_status_type_det) ? ($followup_order[0]->followup_status_type_det->followup_status_name) : "";
         
        $followup=Followup::selectRaw('*,group_concat(DATE_FORMAT(followup_date,"%M-%d")) as dates')->where('lead_management_id',$leadmanagement->id)->groupBy('lead_management_id')->get();
        if(count($followup)>0) 
        { 
          $lead_det['lead_last_followupdates'] =$followup[0]->dates;
        }           
        $customer=Customer::where('lead_management_id',$leadmanagement->id)->get();
        if(count($customer)>0)
        {      
          $cond11=array('customer_id'=>$customer[0]->id);
          $chitdetail=ChitDetail::where($cond11)->get();
          if(count($chitdetail)>0)
          {
            $lead_det['lead_entrolled'] ='Yes';
          }
          else
          {
            $lead_det['lead_entrolled'] ='No';
          }
        }
        else
        {
          $lead_det['lead_entrolled'] ='pending';
        } 
        array_push($response_data,$lead_det);
        $i++;
      }  
      if(count($leadmanagements)>0)
      {         
        return response()->json($response_data,200);
      } 
      else {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.no_lead_found');
        return response()->json($response, 401);
      }      
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error'); 
      return response()->json($response, 401);
    }
  }

  public function otp_generate()
  {
    $otp=mt_rand(1000,9999);
    return $otp;
  }
    
 public function check_device_login(Request $request)
{
    try {

        $email = $request->email;
        $pass  = md5($request->password);
        $_SESSION['db'] = '';

        /* ================= MASTER DB LOGIN ================= */

        $members = DB::connection('mysql1')
            ->table('users')
            ->where('email', $email)
            ->where('password', $pass)
            ->get();

        if ($members->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Credentials',
            ], 401);
        }

        $tenantid = $members[0]->tenant_id;

        $tenentdetails = DB::connection('mysql1')
            ->table('tenants')
            ->where('id', $tenantid)
            ->get();

        if ($tenentdetails->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Database',
            ], 401);
        }

        $_SESSION['db'] = $tenentdetails[0]->slag_url;

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $_SESSION['db']);
        DB::reconnect('mysql');

        /* ================= APP DB LOGIN ================= */

        $user = User::where('email', $email)->get();

        if ($user->isEmpty()) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid Email or Password',
            ], 401);
        }

        if (!Hash::check($request->password, $user[0]->password)) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid Email or Password',
            ], 401);
        }

        $logged_user = $user[0];
        $user_id = $logged_user->id;

        /* ================= PREPARE OLD API VARIABLES ================= */

        $logged_user_name = '';
        $role_name = '';
        $role_type = '';
        $tenant_name = '';
        $tenant_gst_no = '';
        $tenant_address_1 = '';
        $tenant_address_2 = '';
        $state_name = '';
        $district_name = '';
        $branch_name = '';
        $city_name = '';
        $tenant_mobile = '';
        $tenant_phone = '';
        $employee_id = '';
        $profilephoto = '';
        $latest_version = '1.0';
        $critical_version = '1.0';
		
        if ($logged_user) {

            $logged_user_name = optional($logged_user->employee_det)->first_name;

            if ($logged_user->role_det) {
                $role_name = $logged_user->role_det->role_name;
                $role_type = optional($logged_user->role_det->role_type_det)->role_type_name;
            }

            if ($logged_user->tenant_det) {
                $tenant_name = $logged_user->tenant_det->chit_fund_name;
                $tenant_gst_no = $logged_user->tenant_det->gst_no;
            }

            if ($logged_user->branch_det) {
                $tenant_address_1 = $logged_user->branch_det->address_line_1;
                $tenant_address_2 = $logged_user->branch_det->address_line_2;
                $branch_name = $logged_user->branch_det->branch_name;
                $state_name = optional($logged_user->branch_det->state_det)->state_name;
                $district_name = optional($logged_user->branch_det->district_det)->district_name;
                $city_name = optional($logged_user->branch_det->city_det)->city_name;
                $tenant_mobile = $logged_user->branch_det->mobile_no;
                $tenant_phone = $logged_user->branch_det->phone_no;
            }

            if ($logged_user->employee_id > 0) {
                $employee = Employee::find($logged_user->employee_id);
                if ($employee && $employee->photo) {
                    $profilephoto = env('APP_URL') .
                        Storage::url('app/uploads/employee_photos/' . $employee->photo);
                }
            }

            $employee_id = optional($logged_user->employee_det)->id;
        }

        /* ================= DEVICE CHECK ================= */

        $devices1 = Device::where([
            'android_id' => $request->device_id,
            'verification_status' => 0
        ])->get();

        if ($devices1->count() > 0) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Device deactivated. Please contact admin.'
            ], 401);
        }

        $devices = Device::where([
            'android_id' => $request->device_id,
            'verification_status' => 1
        ])->get();

        /* ================= ACTIVE DEVICE ================= */

        if ($devices->count() > 0) {

            $device_id = $devices[0]->id;

            $device_logins = DeviceLogin::where([
                'device_id' => $device_id,
                'user_id' => $user_id
            ])->get();

            /* -------- New User on Existing Device -------- */

            if ($device_logins->count() == 0) {

                $device_login = new DeviceLogin();
                $device_login->tenant_id = $logged_user->tenant_id;
                $device_login->user_id = $user_id;
                $device_login->device_id = $device_id;
                $device_login->otp_verification_status = 0;
                $device_login->created_by = $user_id;
                $device_login->save();

                return response()->json([
                    'status' => 'New Login',
                    'msg' => 'OTP Sent',
                    'user_id' => $user_id,
                    'device_primary_id' => $device_id,
                    'otp' => '1111',
                    'db' => $_SESSION['db']
                ], 200);
            }

            /* -------- Existing Verified User -------- */

            $verified = DeviceLogin::where([
                'device_id' => $device_id,
                'user_id' => $user_id,
                'otp_verification_status' => 1
            ])->get();

            if ($verified->count() > 0) {

                // ✅ TOKEN GENERATED HERE
                $token = JWTAuth::fromUser($logged_user);

                /* ================= USER MAPPED BRANCH/EMP LIST ================= */
                $mappedBranchList = [];
                $mappedEmployeeList = [];
                try {
                    $tenantId = (int) $logged_user->tenant_id;
                    $userId = (int) $logged_user->id;

                    $allowedBranchIds = [];
                    $allowedEmployeeIds = [];

                    $bMap = DB::table('user_branch_mapping')
                        ->where('user_id', $userId)
                        ->where('tenant_id', $tenantId)
                        ->where('status', 1)
                        ->pluck('branch_id');
                    foreach ($bMap as $bid) {
                        $allowedBranchIds[] = (int) $bid;
                    }

                    $eMap = DB::table('user_employee_mapping')
                        ->where('user_id', $userId)
                        ->where('tenant_id', $tenantId)
                        ->where('status', 1)
                        ->pluck('employee_id');
                    foreach ($eMap as $eid) {
                        $allowedEmployeeIds[] = (int) $eid;
                    }

                    if (empty($allowedBranchIds) && !empty($logged_user->branch_id)) {
                        $allowedBranchIds[] = (int) $logged_user->branch_id;
                    }
                    if (empty($allowedEmployeeIds) && !empty($logged_user->employee_id)) {
                        $allowedEmployeeIds[] = (int) $logged_user->employee_id;
                    }

                    $allowedBranchIds = array_values(array_unique(array_filter($allowedBranchIds)));
                    $allowedEmployeeIds = array_values(array_unique(array_filter($allowedEmployeeIds)));

                    $branchQuery = Branch::with(['state_det', 'district_det', 'city_det'])
                        ->where('tenant_id', $tenantId)
                        ->orderBy('id', 'desc');
                    if (empty($allowedBranchIds)) {
                        $branchQuery->whereRaw('1 = 0');
                    } else {
                        $branchQuery->whereIn('id', $allowedBranchIds);
                    }
                    $i = 1;
                    foreach ($branchQuery->get() as $branch) {
                        $mappedBranchList[] = [
                            'sno'            => $i++,
                            'branch_id'      => $branch->id,
                            'tenant_id'      => $branch->tenant_id,
                            'head_office_id' => $branch->head_office_id,
                            'branch_name'    => $branch->branch_name,
                            'branch_code'    => $branch->branch_code,
                            'email_id'       => $branch->email_id,
                            'phone_no'       => $branch->phone_no,
                            'mobile_no'      => $branch->mobile_no,
                            'address_line_1' => $branch->address_line_1,
                            'address_line_2' => $branch->address_line_2,
                            'landmark'       => $branch->landmark,
                            'state_name'     => optional($branch->state_det)->state_name ?? '',
                            'district_name'  => optional($branch->district_det)->district_name ?? '',
                            'city_name'      => optional($branch->city_det)->city_name ?? '',
                        ];
                    }

                    $empQuery = Employee::with(['role_det', 'branch_det'])
                        ->where('tenant_id', $tenantId)
                        ->orderBy('id', 'desc');
                    if (empty($allowedEmployeeIds)) {
                        $empQuery->whereRaw('1 = 0');
                    } else {
                        $empQuery->whereIn('id', $allowedEmployeeIds);
                    }
                    $j = 1;
                    foreach ($empQuery->get() as $emp) {
                        $mappedEmployeeList[] = [
                            'sno'         => $j++,
                            'employee_id' => $emp->id,
                            'tenant_id'   => $emp->tenant_id,
                            'branch_id'   => $emp->branch_id,
                            'first_name'  => $emp->first_name,
                            'last_name'   => $emp->last_name,
                            'mobile_no'   => $emp->mobile_no,
                            'role_name'   => optional($emp->role_det)->role_name ?? '',
                            'branch_name' => optional($emp->branch_det)->branch_name ?? '',
                        ];
                    }
                } catch (\Exception $ignore) {
                    $mappedBranchList = [];
                    $mappedEmployeeList = [];
                }

                return response()->json([
                    'success' => true,
                    'status' => 'Success',

                    'token' => $token,

                    'logged_user_id' => $logged_user->id,
                    'tenant_id' => (int) $logged_user->tenant_id,
                    'branch_id' => (int) $logged_user->branch_id,
                    'role_id' => (int) $logged_user->role_id,

                    'logged_user_name' => $logged_user_name,
                    'branch_name' => $branch_name,
                    'role_name' => $role_name,
                    'role_type' => $role_type,

                    'tenant_name' => $tenant_name,
                    'tenant_gst_no' => $tenant_gst_no,
                    'tenant_address_1' => $tenant_address_1,
                    'tenant_address_2' => $tenant_address_2,
                    'state_name' => $state_name,
                    'district_name' => $district_name,
                    'city_name' => $city_name,
                    'tenant_mobile' => $tenant_mobile,
                    'tenant_phone' => $tenant_phone,

                    'profile_pic' => $profilephoto,
                    'employee_id' => (int) $employee_id,
                    'device_id' => $request->device_id,
                    'mobile_no' => '',
                    'db' => $_SESSION['db'],
                    'critical_version' => $critical_version,
                    'latest_version' => $latest_version,
                    'branch_list' => $mappedBranchList,
                    'employee_list' => $mappedEmployeeList,
                    'msg' => ''
                ], 200);
            }

            return response()->json([
                'status' => 'New Login',
                'msg' => 'OTP Sent',
                'user_id' => $user_id,
                'device_primary_id' => $device_id,
                'otp' => '1111',
                'db' => $_SESSION['db']
            ], 200);
        }

        /* ================= NEW DEVICE ================= */

        $device = new Device();
        $device->android_id = $request->device_id;
        $device->tenant_id = $logged_user->tenant_id;
        $device->verification_status = 1;
        $device->created_by = $user_id;
        $device->save();

        $device_login = new DeviceLogin();
        $device_login->tenant_id = $logged_user->tenant_id;
        $device_login->user_id = $user_id;
        $device_login->device_id = $device->id;
        $device_login->otp_verification_status = 0;
        $device_login->created_by = $user_id;
        $device_login->save();

        return response()->json([
            'status' => 'New Device',
            'msg' => 'OTP sent',
            'user_id' => $user_id,
            'device_primary_id' => $device->id,
            'otp' => '1111',
            'db' => $_SESSION['db']
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg' => 'Invalid Email or Password',
            'error' => $e->getMessage()
        ], 401);
    }
}



public function otp_verified(Request $request)
{
    try { 

        /* =====================
           DB SWITCH
        ===================== */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =====================
           VALIDATION
        ===================== */
        if (
            empty($request->user_id) ||
            empty($request->device_primary_id)
        ) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid request parameters'
            ], 401);
        }

        /* =====================
           FIND DEVICE LOGIN
        ===================== */
        $device_login = DeviceLogin::where([
                'device_id' => $request->device_primary_id,
                'user_id'   => $request->user_id
            ])
            ->orderBy('id','desc')
            ->first();   // ✅ NOT get()

 
        if (!$device_login) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Device login record not found'
            ], 401);
        }

        /* =====================
           VERIFY OTP
        ===================== */
        $device_login->otp_verification_status = 1;

        if ($device_login->save()) {
            return response()->json([
                'status' => 'Success',
                'msg' => 'OTP verified successfully'
            ], 200);
        }

        return response()->json([
            'status' => 'Error',
            'msg' => __('api.global_error')
        ], 401);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg' => __('api.global_error'),
            'error' => $e->getMessage()   // 🔥 TEMP: remove in production
        ], 401);
    }
}


  public function get_device_counts(Request $request)
  {
    try
    {
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $user = User::where(array('tenant_id'=>$request->tenant_id))->get();
      if($user) 
      {
        $cond = array('tenant_id'=>$user[0]->tenant_id);
        $devices = Device::where($cond)->get();
        if(count($devices)>0) 
        {
          $total_device=count($devices); // total device
          $cond1 = array('tenant_id'=>$user[0]->tenant_id,'verification_status'=>1);
          $devices1 = Device::where($cond1)->get();
          $active_device=count($devices1); // active device
          $cond2 = array('tenant_id'=>$user[0]->tenant_id,'verification_status'=>0); 
          $devices2 = Device::where($cond2)->get();
          $deactive_device=count($devices2); // deactive device
          $data['status'] = 'Success';
          $data['msg'] = '';
          $data['Total Devices'] = $total_device;
          $data['Active Devices'] = $active_device;
          $data['DeActive Devices'] = $deactive_device;
           return response()->json($data,200);
        }
        else
        {
          $data['status'] = 'Error';
          $data['msg'] = '';
          return response()->json($data,200);
        }
      }
    }
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);  
    }
  }

  public function get_devices(Request $request)
  {
    try 
    {
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id);
      $devices = Device::where($cond)->get();
      if(count($devices)>0)
      {
        $response_data = [];
        foreach($devices as $device) 
        { 
          $device_det = [];
          $device_det['device_id'] = $device->id;
          $device_det['device_name'] = $device->device_name;
          $device_det['android_id'] = $device->android_id;
          $users=User::where('id',$device->created_by)->get();
          if(count($users)>0)
          {   
            $device_det['branch_id'] = $users[0]->branch_id;
          }
          $arr=array(1=>'Yes',0=>'No');
          $device_det['isActive'] = $arr[$device->verification_status];
          array_push($response_data,$device_det);
        }
        if($devices) {
          return response()->json($response_data,200);
        }
      }
    }
    catch(\Exception $e) 
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      $response['msg'] =$e;
      return response()->json($response, 401);
    }
  }

  public function mobile_cash_settlement(Request $request) 
  {
    $validator = Validator::make($request->all(), [
      'tenant_id' => 'required',
      'branch_id' => 'required',
      'employee_id' => 'required',
      'settlement_date' => 'required',
      'total_amount' => 'required',
      'received_amount' => 'required',
      'collection_employee' => 'required',
    ]);
    if ($validator->fails()) { 
      $response['status'] = "Error";
      $response['msg'] = $validator->errors()->first();
      return response()->json($response, 200);
    }
    else {
      try { 
        DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
        $cashsettlement = new CashSettlement();
        $cashsettlement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0 ;
        $cashsettlement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0 ;
        $cashsettlement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0 ;
        $cashsettlement->settlement_date = isset($request->settlement_date) ? ($request->settlement_date) : '0000-00-00';
        $cashsettlement->total_amount = isset($request->total_amount) ? ($request->total_amount) : 0 ;
        $cashsettlement->amt_1 = isset($request->amt_1) ? ($request->amt_1) : 0 ;
        $cashsettlement->amt_2 = isset($request->amt_2) ? ($request->amt_2) : 0 ;
        $cashsettlement->amt_5 = isset($request->amt_5) ? ($request->amt_5) : 0 ;
        $cashsettlement->amt_10 = isset($request->amt_10) ? ($request->amt_10) : 0 ;
        $cashsettlement->amt_20 = isset($request->amt_20) ? ($request->amt_20) : 0 ;
        $cashsettlement->amt_50 = isset($request->amt_50) ? ($request->amt_50) : 0 ;
        $cashsettlement->amt_100 = isset($request->amt_100) ? ($request->amt_100) : 0 ;
        $cashsettlement->amt_200 = isset($request->amt_200) ? ($request->amt_200) : 0 ;
        $cashsettlement->amt_500 = isset($request->amt_500) ? ($request->amt_500) : 0 ;
        $cashsettlement->amt_1000 = isset($request->amt_1000) ? ($request->amt_1000) : 0 ;
        $cashsettlement->amt_2000 = isset($request->amt_2000) ? ($request->amt_2000) : 0 ;
        $cashsettlement->received_amount = isset($request->received_amount) ? ($request->received_amount) : 0 ;
        $cashsettlement->collection_employee = isset($request->collection_employee) ? ($request->collection_employee) : 0 ;
        $cashsettlement->settlement_mode = 'Mobile';
        $cashsettlement->remarks = isset($request->remarks) ? ($request->remarks) : '' ;
        $cashsettlement->created_by = isset($request->created_by) ? ($request->created_by) : 0;
        $cashsettlement->status = isset($request->status) ? ($request->status) : 1;
        if($cashsettlement->save()) {
          $response['status'] = 'Success';
          $response['msg'] = \Lang::get('api.success_cash_settlement_added');
          $response['data'] = $cashsettlement;
          return response()->json($response,200);
        }
      }
      catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }
    }
  }

  public function mobile_followup_status_list(Request $request)
{
    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =========================
           QUERY
        ========================= */
        $query = FollowupStatusType::query();

        if (!empty($request->tenant_id)) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $statuses = $query->orderBy('id', 'desc')->get();

        if ($statuses->isEmpty()) {
            return response()->json([
                'status' => 'Success',
                'msg'    => __('api.no_data_found'),
                'data'   => []
            ], 200);
        }

        /* =========================
           RESPONSE
        ========================= */
        $data = [];
        $i = 1;

        foreach ($statuses as $status) {
            $data[] = [
                'sno'                   => $i,
                'followup_status_id'    => $status->id,
                'followup_status_name'  => $status->followup_status_name
            ];
            $i++;
        }

        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => $data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage() // keep for debugging, remove in production
        ], 500);
    }
}


 public function mobile_state_lists(Request $request)
{
    try {

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        if ($request->filled('tenant_id')) {
            $states = State::orderBy('id', 'desc')
                           ->get();
        } else {
            $states = State::orderBy('id', 'asc')->get();
        }

        $response_data = [];
        $i = 1;

        foreach ($states as $state) {
            $response_data[] = [
                'sno'        => $i++,
                'state_id'   => $state->id,
                'country_id' => $state->country_id,
                'state_name' => $state->state_name,
            ];
        }

        return response()->json([
            'status' => 'Success',
            'data'   => $response_data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()   // helpful during debugging
        ], 500);
    }
}

  public function mobile_district_lists(Request $request)
  {
    try { 
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      if(isset($request->tenant_id)) {
        $districts = District::orderBy('id','desc')->get();
      }
      else {
        $districts = District::orderBy('id')->get();
      }
      $i=1; 
      $response_data = [];
      foreach($districts as $district) {
        $district_det = [];
        $district_det['sno'] = $i;
        $district_det['district_id'] = $district->id;
        $district_det['state_id'] = $district->state_id;
        $district_det['district_name'] = $district->district_name;
        array_push($response_data,$district_det);        
        $i++;
      }
      if($district) {          
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function mobile_city_lists(Request $request)
  {
    try { 
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      if(isset($request->tenant_id)) {
          $cities = City::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
      }
      else {
          $cities = City::orderBy('id')->get();
      }
      $i=1; 
      $response_data = [];
      foreach($cities as $city) {
        $city_det = [];
        $city_det['sno'] = $i;
        $city_det['state_id'] = $city->state_id;
        $city_det['district_id'] = $city->district_id;
        $city_det['city_id'] = $city->id;
        $city_det['city_name'] = $city->city_name;
        array_push($response_data,$city_det);        
        $i++;
      }
      if($city) {          
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function reset_password(Request $request)
  {
    try
    {
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $user = User::where(array('id'=>$request->user_id))->get();
      $user[0]->password = Hash::make($request->password);
      if($user[0]->save())
      {

        $tenant_id=isset($request->tenant_id) ? ($request->tenant_id) : '';
        $employee_id=isset($request->user_id) ? ($request->user_id) : '';
        $password=isset($request->password) ? (md5($request->password)) : '';
        

        DB::connection('mysql1')->update('update users set password="'.$password.'" where tenant_id="'.$tenant_id.'" and original_id="'.$employee_id.'" ');


        $response['status'] = 'Success';
        $response['msg'] = '';
        return response()->json($response, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = '';
      return response()->json($response, 401);
    }
  }

  public function profile(Request $request)
{
    try {

        /* -------------------------
           Dynamic DB connection
        ------------------------- */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* -------------------------
           Fetch user
        ------------------------- */
        $user = User::with([
            'employee_det:id,first_name,photo',
            'device_login_det.device_det:id,device_name'
        ])->find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'User not found',
                'data'   => []
            ], 200);
        }

        /* -------------------------
           Prepare profile data
        ------------------------- */
        $profile = [
            'user_id'       => $user->id,
            'user_name'     => '',
            'device_name'   => '',
            'profile_photo' => ''
        ];

        /* Employee details */
        if ($user->employee_det) {
            $profile['user_name'] = $user->employee_det->first_name ?? '';

            if (!empty($user->employee_det->photo)) {
                $profile['profile_photo'] =
                    env('APP_URL') .
                    Storage::url('app/uploads/employee_photos/' . $user->employee_det->photo);
            }
        }

        /* Device details */
        if (
            $user->device_login_det &&
            $user->device_login_det->count() > 0 &&
            $user->device_login_det[0]->device_det
        ) {
            $profile['device_name'] =
                $user->device_login_det[0]->device_det->device_name ?? '';
        }

        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => $profile
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => 'Something went wrong'
        ], 500);
    }
}


  public function sendmail()
  { // $user_id
    try {
     
      $otp=mt_rand(1000,9999);
      $data['otp'] = $otp;
      Mail::send(['text'=>'mail'],$data, function($message) 
      {
        $message->to('manoj_p@mazenetsolution.com','Suhail')->subject('OTP Mail');
        $message->from('androiddeveloper@mazenetsolution.com','Jeeva');
      });
      return $otp;
    }
    catch(\Exception $e){
      return false;
    }
  }

  public function mobile_get_collection_area_customers(Request $request) 
  {
    try
    {  // emp_id, tenant_id
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('employee_id'=>$request->employee_id,'tenant_id'=>$request->tenant_id);
      $collectionareamapping=CollectionAreaMapping::where($cond)->groupBy('collection_area_id')->get();
      $response_data = []; 
      $cust_id = [];
      if(count($collectionareamapping)>0)
      {
        foreach($collectionareamapping as $cm)
        { 
          $collection_area_id = unserialize($cm->collection_area_id);
          $id_list = [];
          foreach($collection_area_id as $area)
          {      
            array_push($id_list,$area['item_id']);
          }    
          $entrols=ChitDetail::whereIn('collection_area_id',$id_list)->groupBy('customer_id')->get();
          foreach($entrols as $entrol)
          { 
            $cust_id_u = $entrol->customer_id;
            if(!in_array($cust_id_u, $cust_id))
            { 
              $entrollment_det['customer_id'] = strval($entrol->customer_id);
              $entrollment_det['customer_name'] = '';
              $entrollment_det['customer_code'] = '';
              $entrollment_det['mobile_no'] = '';
              $entrollment_det['phone_no'] = '';
              if($entrol->customer_det)
              { 
                $entrollment_det['customer_name'] = $entrol->customer_det->name . '/' . $entrol->customer_det->customer_code .'/'. $entrol->customer_det->mobile_no;
                $entrollment_det['customer_code'] = $entrol->customer_det->customer_code;
                $entrollment_det['mobile_no'] = $entrol->customer_det->mobile_no;
                $entrollment_det['phone_no'] = $entrol->customer_det->phone_no;
              }
              $entrollment_det['total_installment'] = "0";  
              $entrollment_det['total_paid']= "0";
              $entrollment_det['total_pending'] = "0";
              $chit_details= ChitDetail::where('customer_id',$entrol->customer_id)->get();
              foreach($chit_details as $chit)
              { 
                $group_id=   $chit->group_id;
                $customer_id=   $chit->customer_id;
                $enrollment_id = $chit->id;
                if($chit->chit_type == 3)
                { 
                  $bidding = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
                  $install_amt = isset($bidding) ? ($bidding) : "0";
                  $total_installment = $entrollment_det['total_installment'] + $install_amt;
                  $entrollment_det['total_installment'] = strval(round($total_installment));                                
                } 
                else
                {
                  $bidding = BiddingDetail::selectRaw('sum(current_installment_amount) as total_install_amt')->where('group_id',$group_id)->groupBy('group_id')->get();
                  
                  $install_amt = isset($bidding[0]->total_install_amt) ? ($bidding[0]->total_install_amt) : "0";
                  $total_installment = $entrollment_det['total_installment'] + $install_amt;
                  $entrollment_det['total_installment'] = strval(round($total_installment));
                } 
			  }				
                $receipt=Receipt::selectRaw('*, sum(received_amount) + sum(bonus) as total_received')->where('customer_id',$customer_id)->where('status','=',1)->groupBy('customer_id')->get();
               
                $total_paid = isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0";
                $entrollment_det['total_paid'] = strval(round($total_paid));
                
                $total_pending = $entrollment_det['total_installment'] -  $entrollment_det['total_paid'];
                $entrollment_det['total_pending'] = strval(round($total_pending));
              } 
              $cust_id[] = $cust_id_u;
              array_push($response_data,$entrollment_det); 
                         
          }  
        }   
      }    
      return response()->json($response_data,200);   
    }        
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = '';
      return response()->json($response, 401);
    }
  }

  public function mobile_get_customer_wise_entrolment_details(Request $request) 
  {
    try
    {  // customer_id, tenant_id
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('customer_id'=>$request->customer_id,'tenant_id'=>$request->tenant_id);
      $entrollments=ChitDetail::where($cond)->get();
      if($entrollments)
      {
        $cr_date = date("Y-m-d");
        $response_data = [];
        $from_date =0;
        $to_date =0;
        $current_instal_amt = 0;
        foreach($entrollments as $entrol) 
        { 
          $entrollment_det = [];
          $entrollment_det['customer_id'] = strval($entrol->customer_id);
          $entrollment_det['customer_code'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->customer_code)) : '';
          $entrollment_det['customer_mobile_no'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->mobile_no)) : '';
          $entrollment_det['customer_phone_no'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->phone_no)) : '';
          $entrollment_det['entrollment_id'] = strval($entrol->id);
          $entrollment_det['group_id'] = "";
          $entrollment_det['group_name']="";
          $entrollment_det['chit_value'] = "";
          $entrollment_det['auction_date'] = "";
          $entrollment_det['ticket_no'] = "";
          $entrollment_det['install_amount'] = "";
          $entrollment_det['paid_amount'] = "";
          $entrollment_det['pending_amount'] = ""; 
          $entrollment_det['advance_amount'] = "";
          $entrollment_det['pending_days'] = "";
          $entrollment_det['bonus'] = "";
          $entrollment_det['discountonpenalty'] = "";
          $entrollment_det['discountondue'] = "";
          if($entrol->prized_status == 0)
          {
              $entrollment_det['prized_status'] = "Non-Prized";
          }
          else
          {
              $entrollment_det['prized_status'] = "Prized";
          }
          $entrollment_det['installments_det'] = "";  
         
          // Advance Amount
          $receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('enrollment_id'=>$entrol->id,'payment_type_id'=>7))->groupBy('enrollment_id')->get();
          $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
          $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$entrol->id,'status'=>1))->groupBy('enrollment_id')->get();
          $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
          $advance_amount = $total_advance - $total_received;
          $entrollment_det['advance_amount'] = strval($advance_amount);

          if($entrol->chit_type != 3)
          { //  Non-Commitment Chit  
            $entrollment_det['group_id'] = strval($entrol->group_id);
            $entrollment_det['group_name'] = isset($entrol->group_det) ? ($entrol->group_det->group_name) : "";
            $entrollment_det['ticket_no'] = strval($entrol->ticket_no);
			$chit_value = isset($entrol->group_det->scheme_det) ? (strval(round($entrol->group_det->scheme_det->chit_value))) : "0";
            $entrollment_det['chit_value'] = $chit_value * $entrol->ticket_type;
            $entrollment_det['auction_date'] = isset($entrol->group_det) ? ($entrol->group_det->auction_date) : "0000-00-00";

            // Paid amount - entroll_id wise
            $receipt=Receipt::selectRaw('sum(received_amount) as total_received')->where('enrollment_id',$entrol->id)->where('status','=',1)->groupBy('enrollment_id')->get(); 
			
            $paid = 0;
            $total_install_amount = 0;  
            
            $paid =  isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0"; 
            $entrollment_det['paid_amount'] = strval(round($paid));                       
                      
            // Installment amount -  group_id wise
            $cond1=array('group_id'=>$entrol->group_id);                    
            $bidding_details=BiddingDetail::selectRaw('*,sum(current_installment_amount) as tot_instal_amt')->where($cond1)->groupBy('group_id')->get();
              
            $total_install_amount = isset($bidding_details[0]->tot_instal_amt) ? ($bidding_details[0]->tot_instal_amt) : "0";
            $total_install_amount = $total_install_amount * $entrol->ticket_type;
			$entrollment_det['install_amount'] = strval(round($total_install_amount));
            
            $entrollment_det['pending_amount'] = strval(round($total_install_amount-$paid));
            
            $max_id=BiddingDetail::where(array('group_id'=>$entrol->group_id))->max('id');
            $current_install= BiddingDetail::where('id',$max_id)->pluck('current_installment_amount');                       
            $current_instal_amt_11 =isset($current_install[0]) ? ($current_install[0]) : 0;    
            $current_instal_amt = strval(round($current_instal_amt_11));

            if($entrollment_det['pending_amount']==$current_instal_amt)
            {   // pending_amount == current_installment_amount,  auction date 
              $cond2=array('group_id'=>$entrol->group_id);
              $bidding_det = BiddingDetail::where($cond2)->max('id');
              $auction_datess = BiddingDetail::where('id','=',$bidding_det)->pluck('auction_date');
              if(count($auction_datess)>0)
              { 
                $from_date = $auction_datess[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');
                $entrollment_det['pending_days'] = strval($days);
              }
            }
            else
            { // pending_amount != 0, receipt date
              $cond3=array('enrollment_id'=>$entrol->id);  
              $receipt_det = Receipt::where($cond3)->where('status','=',1)->max('id');
              $receipt_dates= Receipt::where('id',$receipt_det)->pluck('receipt_date');
              if(count($receipt_dates)>0)
              {  // if receipt entry greater than 0, Receipt date
                $from_date =$receipt_dates[0]; 
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');    
                $entrollment_det['pending_days'] = strval($days);
              }
              else 
              { // if receipt entry less than 0, auction date
                $cond4=array('group_id'=>$entrol->group_id); 
                $auction_dates = BiddingDetail::where($cond4)->pluck('auction_date');
                if(count($auction_dates)>0)
                { 
                  $from_date = $auction_dates[0];
                  $to_date = date('Y-m-d');
                  $date1 = new DateTime($from_date);
                  $date2 = new DateTime($to_date);
                  $interval = $date1->diff($date2);
                  $days = $interval->format('%a');
                  $entrollment_det['pending_days'] = strval($days);
                }
              }
            }      
		
            // Bonus days and percentage by heirrachy over all 
            if($entrol->customer_det->bonus_days!=0 && $entrol->customer_det->customer_bonus!=0)
            {
              $bonus_days = $entrol->customer_det->bonus_days;
              $bonus_percentage = $entrol->customer_det->customer_bonus;
            }    
            elseif($entrol->group_det->group_bonus_days!=0 && $entrol->group_det->group_base_bonus!=0)
            {
              $bonus_days = $entrol->group_det->group_bonus_days;
              $bonus_percentage = $entrol->group_det->group_base_bonus;
            }
            elseif($entrol->branch_det->bonus_days!=0 && $entrol->branch_det->bonus!=0)
            {
              $bonus_days = $entrol->branch_det->bonus_days;
              $bonus_percentage = $entrol->branch_det->bonus;              
            }    
            else
            {
              $bonus_days = 0;
              $bonus_percentage = 0;
            }       

            $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
            $check_auction = BiddingDetail::where('id',$bid11)->get();
            $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
            // Auction on holiday check for bonus
            $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
            $holiday_count = 0;
            if(count($auction_on_holiday)>0)
            { 
              $holiday_count += $auction_on_holiday->count();
            }

            $bonus_days_include_holiday = $bonus_days + $holiday_count;
            
            if($entrollment_det['pending_days'] <=  $bonus_days_include_holiday)
            { 
              if($entrollment_det['pending_amount']==$current_instal_amt)
              { 
                $bonus_calculation = $current_instal_amt * $bonus_percentage / 100 ; 
                $entrollment_det['bonus'] = strval(round($bonus_calculation));
              }
              else
              {
                $entrollment_det['bonus'] = "";
              }
            }
            else
            {
              $entrollment_det['bonus'] = "";
            }
            
            // Installment Wise 
            $install_details=BiddingDetail::where('group_id',$entrol->group_id)->get();    
            $install_det = [];
            foreach($install_details as $install_detail)
            { 
              $cust_det = [];
              $cond45 = array('auction_id'=>$install_detail->id,'enrollment_id'=>$entrol->id); 
              $receipt1=Receipt::selectRaw('sum(received_amount) as total_received')->where($cond45)->where('status','=',1)->groupBy('auction_id')->get();
			  $current_installment_amount = $install_detail->current_installment_amount * $install_detail->ticket_type;
              $pending_due = 0;
              $auction_id = 0;
              $paid_due = 0;
              $current_instal_amt1 = strval(round($current_installment_amount));
              if(count($receipt1)>0)
              {
                $paid_due = isset($receipt1[0]->total_received) ? ($receipt1[0]->total_received) : "0";
                $pending_due = $current_instal_amt1 - $paid_due;
                $auction_id = $receipt1[0]->auction_id;
              }
              else
              {  
                $pending_due =  $current_instal_amt1;
              }
              if($paid_due != $current_instal_amt1)
              {   
                $cust_det['entrollment_id'] = strval($entrol->id);
                $cust_det['auction_no'] = strval($install_detail->installment_no);
                $cust_det['bidding_id'] = strval($install_detail->id);
                $cust_det['pending_due'] = strval(round($pending_due));
                $cust_det['discount_on_due'] = "";
                $cust_det['discount_on_penalty'] = "";
                $cust_det['bonus_days'] = "";
                $cust_det['penalty_percentage'] = "";
                $cust_det['bonus_percentage'] = "";
                $cust_det['bonus_amount'] = "";
                $cust_det['instal_pending_day'] = "";
                $cust_det['paid_amount'] = strval(round($paid_due));
                $cust_det['current_installment_amount'] = strval(round($install_detail->current_installment_amount));
                $penlaty_days = $install_detail->group_det->group_penalty_days;

                if($cust_det['pending_due']==$current_instal_amt1)
                {  
                  $cond1 = array('id'=>$install_detail->id);
                  $bid= BiddingDetail::where($cond1)->max('id');
                  $auction_date1 = BiddingDetail::where('id',$bid)->get();
                  if(count($auction_date1)>0)
                  {
                    $auction_date = $auction_date1[0]->auction_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($auction_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days);
                  }  
                }
                else
                {
                  $cond2 = array('auction_id'=>$install_detail->id);
                  $receipts = Receipt::where($cond2)->where('status','=',1)->max('id');
                  $receipt_date1 = Receipt::where('id',$receipts)->get();
                  if(count($receipt_date1)>0)
                  {
                    $receipt_date = $receipt_date1[0]->receipt_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($receipt_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days); 
                  }
                  else
                  {   
                    $cond3 = array('id'=>$install_detail->id);
                 //   $bidd = BiddingDetail::where($cond3)->max('id');
                    $auction_date2 = BiddingDetail::where($cond3)->get();
                    if(count($auction_date2)>0)
                    {
                      $auction_date = $auction_date2[0]->auction_date;
                      $current_date =date('Y-m-d');
                      $date1 = new DateTime($auction_date);
                      $date2 = new DateTime($current_date);
                      $interval = $date1->diff($date2);
                      $days = $interval->format('%a');
                      $cust_det['instal_pending_day'] = strval($days); 
                    }
                  }
                }
				
               // Penalty Calculation installment wise
                // if($install_detail->enrollment_det->customer_det->penalty_days!=0 && $install_detail->enrollment_det->customer_det->customer_penalty_interest!=0)
                // {
                  // $inst_penalty_days = $install_detail->enrollment_det->customer_det->penalty_days;
                  // $inst_penalty_percentage = $install_detail->enrollment_det->customer_det->customer_penalty_interest;
                // }
                // elseif($install_detail->group_det->group_penalty_days!=0 && $install_detail->group_det->group_base_penalty!=0)
                // {
                  // $inst_penalty_days = $install_detail->group_det->group_penalty_days;
                  // $inst_penalty_percentage = $install_detail->group_det->group_base_penalty;                  
                // }
                // elseif($install_detail->branch_det->penalty_days!=0 && $install_detail->branch_det->branch_wise_penalty!=0)
                // {
                  // $inst_penalty_days = $install_detail->branch_det->penalty_days;
                  // $inst_penalty_percentage = $install_detail->branch_det->branch_wise_penalty;
                // }
                // else
                // {
                  $inst_penalty_days = "0";
                  $inst_penalty_percentage = "0";
                // }
                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                $pend_days_int = 0;    
                $cust_det['penalty_amounts'] = "";            
                if($cust_det['instal_pending_day'] > $inst_penalty_days)
                {        
                  $branch=Branch::where('id',$entrol->branch_id)->get();
                  if($entrol->prized_status==0)
                  {
                    $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                    $pend_days_int = $cust_det['instal_pending_day'] * ($non_prize/ 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  } 
                  else
                  {               
                    $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                    $pend_days_int = $cust_det['instal_pending_day'] * ($prize / 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  }                      
                }                  
                // Bonus Calculation
                // if($install_detail->enrollment_det->customer_det->bonus_days!=0 && $install_detail->enrollment_det->customer_det->customer_bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->enrollment_det->customer_det->bonus_days;
                  // $inst_bonus_percentage = $install_detail->enrollment_det->customer_det->customer_bonus;
                // }    
                // elseif($install_detail->group_det->group_bonus_days!=0 && $install_detail->group_det->group_base_bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->group_det->group_bonus_days;
                  // $inst_bonus_percentage = $install_detail->group_det->group_base_bonus;
                // }
                // elseif($install_detail->branch_det->bonus_days!=0 && $install_detail->branch_det->bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->branch_det->bonus_days;
                  // $inst_bonus_percentage = $install_detail->branch_det->bonus;
                // }    
                // else
                // {
                  $inst_bonus_days = 0;
                  $inst_bonus_percentage = 0;
                // }   
                $cust_det['bonus_days'] = strval($inst_bonus_days);
                $cust_det['bonus_percentage'] = strval($inst_bonus_percentage);

                $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
                $check_auction = BiddingDetail::where('id',$bid11)->get();
                $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                // Auction on holiday check for bonus
                $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                $holiday_count = 0;
                if(count($auction_on_holiday)>0)
                { 
                  $holiday_count += $auction_on_holiday->count();
                }

                $bonus_days_include_holiday = $inst_bonus_days + $holiday_count;
                
                if($cust_det['instal_pending_day'] <=  $bonus_days_include_holiday)
                {                 
                  if($cust_det['pending_due']==$current_instal_amt1)
                  {
                    $bonus_cal = $current_instal_amt1 * $inst_bonus_percentage / 100;
                    $cust_det['bonus_amount'] = strval(round($bonus_cal));
                  }
                  else
                  { 
                    $cust_det['bonus_amount'] = "";
                  }
                }
                else
                { 
                  $cust_det['bonus_amount'] = "";
                }
                array_push($install_det,$cust_det);
              }
            }
          }
          if($entrol->chit_type == 3)
          {  // Commitment Chit      
            $slab_types = SlabDue::where('id',$entrol->slab_id)->get();   
            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
            $entrollment_det['group_name'] = "Commitment"."-".$slab_name;
            $entrollment_det['ticket_no'] = "-";
            $entrollment_det['chit_value'] = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 
            $commitment_auction = CommitmentAuction::where('enrollment_id','=',$entrol->id)->get();
            $entrollment_det['auction_date'] = isset($commitment_auction[0]->auction_date) ? ($commitment_auction[0]->auction_date) : "0000-00-00";

            // Paid amount - entroll_id wise
            $receipt=Receipt::selectRaw('*, sum(received_amount) as total_received')->where('enrollment_id',$entrol->id)->where('status','=',1)->groupBy('enrollment_id')->get();
            $paid = 0;
            $total_install_amount = 0;  
          
            $paid =  isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0"; 
            $entrollment_det['paid_amount'] = strval(round($paid));
                  
            // Installment amount -  group_id wise
            $bidding_details=CommitmentAuction::selectRaw('*,sum(due_amount) as tot_instal_amt')->where(array('enrollment_id'=>$entrol->id))->groupBy('enrollment_id')->get();
            $total_install_amount = isset($bidding_details[0]->tot_instal_amt) ? ($bidding_details[0]->tot_instal_amt) : "0";
            $entrollment_det['install_amount'] = strval(round($total_install_amount));
            $entrollment_det['pending_amount'] = strval(round($total_install_amount - $paid));            
            $max_id=CommitmentAuction::where(array('enrollment_id'=>$entrol->id))->max('id');
            $current_install= CommitmentAuction::where('id',$max_id)->pluck('due_amount');
            $current_instal_amt_11 =isset($current_install[0]) ? ($current_install[0]) : 0;    
            $current_instal_amt = strval(round($current_instal_amt_11));

            if($entrollment_det['pending_amount']==$current_instal_amt)
            {  // pending_amount == current_installment_amount,  auction date 
              $cond2=array('enrollment_id'=>$entrol->id);
              $bidding_det = CommitmentAuction::where($cond2)->max('id');
              $auction_datess = CommitmentAuction::where('id','=',$bidding_det)->pluck('auction_date');
              if(count($auction_datess)>0)
              { 
                $from_date = $auction_datess[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');
                $entrollment_det['pending_days'] = strval($days);
              }
            }
            else
            { // pending_amount != 0, receipt date
              $cond3=array('enrollment_id'=>$entrol->id);  
              $receipt_det = Receipt::where($cond3)->where('status','=',1)->max('id');
              $receipt_dates= Receipt::where('id',$receipt_det)->pluck('receipt_date');
              if(count($receipt_dates)>0)
              {  // if receipt entry greater than 0, Receipt date
                $from_date =$receipt_dates[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');    
                $entrollment_det['pending_days'] = strval($days);
                }
                else 
                { // if receipt entry less than 0, auction date
                  $cond4=array('enrollment_id'=>$entrol->id); 
                  $auction_dates = CommitmentAuction::where($cond4)->pluck('auction_date');
                  if(count($auction_dates)>0)
                  {
                    $from_date = $auction_dates[0];
                    $to_date = date('Y-m-d');
                    $date1 = new DateTime($from_date);
                    $date2 = new DateTime($to_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $entrollment_det['pending_days'] = strval($days);
                  }
                }
              }    
              // Bonus calculation over all 
              if($entrol->customer_det->bonus_days!=0 && $entrol->customer_det->customer_bonus!=0)
              {
                $bonus_days = $entrol->customer_det->bonus_days;
                $bonus_percentage = $entrol->customer_det->customer_bonus;
              }    
              elseif($entrol->branch_det->bonus_days!=0 && $entrol->branch_det->bonus!=0)
              {
                $bonus_days = $entrol->branch_det->bonus_days;
                $bonus_percentage = $entrol->branch_det->bonus;              
              }    
              else
              {
                $bonus_days = 0;
                $bonus_percentage = 0;
              }   

              $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
              $check_auction = BiddingDetail::where('id',$bid11)->get();
              $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
              // Auction on holiday check for bonus
              $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
              $holiday_count = 0;
              if(count($auction_on_holiday)>0)
              { 
                $holiday_count += $auction_on_holiday->count();
              }

              $bonus_days_include_holiday = $bonus_days + $holiday_count;
              
              if($entrollment_det['pending_days'] <=  $bonus_days_include_holiday)
              {    
                if($entrollment_det['pending_amount']==$current_instal_amt)
                { 
                  $bonus_calculation = $current_instal_amt * $bonus_percentage / 100 ; 
                  $entrollment_det['bonus'] = strval(round($bonus_calculation));
                }
                else
                {
                  $entrollment_det['bonus'] = "";
                }
              }
              else
              {
                $entrollment_det['bonus'] = "";
              }
              // Installment Wise 
              $install_details=CommitmentAuction::where('enrollment_id',$entrol->id)->get();    
              $install_det = [];
              foreach($install_details as $install_detail)
              { 
              $cust_det = [];
              $cond45 = array('auction_id'=>$install_detail->id,'enrollment_id'=>$entrol->id); 
              $receipt1=Receipt::selectRaw('*,sum(received_amount)  as total_received')->where($cond45)->where('status','=',1)->groupBy('auction_id')->get();
              $pending_due = 0;
              $auction_id = 0;
              $paid_due = 0;
              $current_instal_amt1 = strval(round($install_detail->due_amount));
              if(count($receipt1)>0)
              {
                $paid_due = isset($receipt1[0]->total_received) ? ($receipt1[0]->total_received) : "0";
                $pending_due = $current_instal_amt1 - $paid_due;
                $auction_id = $receipt1[0]->auction_id;
              }
              else
              {  
                $pending_due =  $current_instal_amt1;
              }
              if($paid_due != $current_instal_amt1)
              {   
                $cust_det['entrollment_id'] = strval($entrol->id);
                $cust_det['auction_no'] = strval($install_detail->installment_no);
                $cust_det['bidding_id'] = strval($install_detail->id);
                $cust_det['pending_due'] = strval(round($pending_due));
                $cust_det['discount_on_due'] = "";
                $cust_det['discount_on_penalty'] = "";
                $cust_det['bonus_days'] = "0";
                $cust_det['penalty_percentage'] = "0";
                $cust_det['bonus_percentage'] = "0";
                $cust_det['bonus_amount'] = "";
                $cust_det['instal_pending_day'] = "";
                $cust_det['paid_amount'] = strval(round($paid_due));
                $cust_det['current_installment_amount'] = strval(round($install_detail->due_amount));
                $penlaty_days = "0";
                if($cust_det['pending_due']==$current_instal_amt1)
                {  
                  $cond1 = array('id'=>$install_detail->id);
                  $bid= CommitmentAuction::where($cond1)->max('id');
                  $auction_date1 = CommitmentAuction::where('id',$bid)->get();
                  if(count($auction_date1)>0)
                  {
                    $auction_date = $auction_date1[0]->auction_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($auction_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days);
                 }  
                }
                else
                {
                  $cond2 = array('auction_id'=>$install_detail->id);
                  $receipts = Receipt::where($cond2)->where('status','=',1)->max('id');
                  $receipt_date1 = Receipt::where('id',$receipts)->get();
                  if(count($receipt_date1)>0)
                  {
                    $receipt_date = $receipt_date1[0]->receipt_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($receipt_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days); 
                  }
                  else
                  {   
                    $cond3 = array('id'=>$install_detail->id);
                    $auction_date2 = CommitmentAuction::where($cond3)->get();
                    if(count($auction_date2)>0)
                    {
                      $auction_date = $auction_date2[0]->auction_date;
                      $current_date =date('Y-m-d');
                      $date1 = new DateTime($auction_date);
                      $date2 = new DateTime($current_date);
                      $interval = $date1->diff($date2);
                      $days = $interval->format('%a');
                      $cust_det['instal_pending_day'] = strval($days); 
                    }
                  }
                }
                // Penalty Calculation installment wise
                if($install_detail->customer_det->penalty_days!=0 && $install_detail->customer_det->customer_penalty_interest!=0)
                { 
                  $inst_penalty_days = $install_detail->customer_det->penalty_days;
                  $inst_penalty_percentage = $install_detail->customer_det->customer_penalty_interest;
                }   
                elseif($install_detail->branch_det->penalty_days!=0 && $install_detail->branch_det->branch_wise_penalty!=0)
                {
                  $inst_penalty_days = $install_detail->branch_det->penalty_days;
                  $inst_penalty_percentage = $install_detail->branch_det->branch_wise_penalty;
                }
                else
                {
                  $inst_penalty_days = "0";
                  $inst_penalty_percentage = "0";
                }
                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                $pend_days_int = 0;                
                $cust_det['penalty_amounts'] = "";            
                if($cust_det['instal_pending_day'] > $inst_penalty_days)
                {        
                  $branch=Branch::where('id',$entrol->branch_id)->get();
                  if($entrol->prized_status==0)
                  {
                    $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                    $pend_days_int = $cust_det['instal_pending_day'] * ($non_prize/ 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  } 
                  else
                  {               
                    $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                    $pend_days_int = $cust_det['instal_pending_day'] * ($prize / 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  }                      
                }   
                // Bonus Calculation
                if($install_detail->customer_det->bonus_days!=0 && $install_detail->customer_det->customer_bonus!=0)
                { 
                  $inst_bonus_days = $install_detail->customer_det->bonus_days;
                  $inst_bonus_percentage = $install_detail->customer_det->customer_bonus;
                }   
                elseif($install_detail->branch_det->bonus_days!=0 && $install_detail->branch_det->bonus!=0)
                {
                  $inst_bonus_days = $install_detail->branch_det->bonus_days;
                  $inst_bonus_percentage = $install_detail->branch_det->bonus;
                }    
                else
                {
                  $inst_bonus_days = 0;
                  $inst_bonus_percentage = 0;
                }   
                $cust_det['bonus_days'] = strval($inst_bonus_days);
                $cust_det['bonus_percentage'] = strval($inst_bonus_percentage);
                
                $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
                $check_auction = BiddingDetail::where('id',$bid11)->get();
                $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                // Auction on holiday check for bonus
                $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                $holiday_count = 0;
                if(count($auction_on_holiday)>0)
                { 
                  $holiday_count += $auction_on_holiday->count();
                }

                $bonus_days_include_holiday = $inst_bonus_days + $holiday_count;
                
                if($cust_det['instal_pending_day'] <=  $bonus_days_include_holiday)
                {                    
                  if($cust_det['pending_due']==$current_instal_amt1)
                  {
                    $bonus_cal = $current_instal_amt1 * $inst_bonus_percentage / 100;
                    $cust_det['bonus_amount'] = strval(round($bonus_cal));
                  }
                  else
                  { 
                    $cust_det['bonus_amount'] = "";
                  }
                }
                else
                { 
                  $cust_det['bonus_amount'] = "";
                }
                array_push($install_det,$cust_det);
              }
            }
          }   
          $entrollment_det['installments_det'] = $install_det;
          array_push($response_data,$entrollment_det);   
        } 
        if($entrollments) {
          return response()->json($response_data,200);
        }   
        else
        {
          $response['status'] = 'Error';
          $response['msg'] =\Lang::get('api.global_error');
          return response()->json($response, 401);
        }                         
      }
      else{
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }
    } 
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = '';
      return response()->json($response, 401);
    }
  }

  public function update_device_status(Request $request)
  { // tenant_id, device_id, device_status
    try {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('tenant_id'=>$request->tenant_id,'id'=>$request->device_id);
      $device=Device::where($cond)->get();
      $device[0]->verification_status =$request->device_status ;      
      if($device[0]->save())
      {
        $response['status'] = 'Success';
        $response['msg'] = 'Successfully';
        return response()->json($response, 200);
      }
      else {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }              
    }
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function get_device_users(Request $request)
  {
    try 
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'device_id'=>$request->device_id);
      $devicelogins = DeviceLogin::where($cond)->get();
      if(count($devicelogins)>0)
      {
        $response_data = [];
        foreach($devicelogins as $devicelog) 
        { 
          $device_det = [];
          $device_det['user_id'] = $devicelog->user_id;
          $device_det['device_name'] = $devicelog->device_det->device_name;
          $user=User::where('id',$devicelog->user_id)->get();
          if(count($user)>0)
          { 
            $device_det['user_name'] =$user[0]->employee_det->first_name;
            $device_det['branch_id'] =$user[0]->branch_id;
          }                   
          $arr=array(1=>'Yes',0=>'No');
          $device_det['isActive'] = $arr[$devicelog->otp_verification_status];
          array_push($response_data,$device_det);
        }
        if($devicelogins) {
          return response()->json($response_data,200);
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

  public function update_device_login_status(Request $request)
  { // tenant_id, device_primary_id, user_id, device_login_status
    try {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('tenant_id'=>$request->tenant_id,'device_id'=>$request->device_primary_id,'user_id'=>$request->user_id);
      $devicelogin=DeviceLogin::where($cond)->get();
      $devicelogin[0]->otp_verification_status =$request->device_login_status ;      
      if($devicelogin[0]->save())
      {
        $response['status'] = 'Success';
        $response['msg'] = 'Successfully';
        return response()->json($response, 200);
      }
      else {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }              
    }
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }  
  }
    
  public function show_lead_details(Request $request)
  {
    try 
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('tenant_id'=>$request->tenant_id,'id'=>$request->lead_id);
      $lead_details=LeadManagement::where($cond)->get();
      
      if(count($lead_details)>0)
      {
        $response['lead_name']=$lead_details[0]->lead_customer_name;
        $response['lead_mobile']=$lead_details[0]->mobile_no;
        $response['lead_phone']=$lead_details[0]->phone_no;
        $response['lead_email']=$lead_details[0]->email_id;
        $response['lead_address_1']=$lead_details[0]->address_line_1;
        $response['lead_address_2']=$lead_details[0]->address_line_2;
        $response['branch_id']=$lead_details[0]->branch_id;
        $response['generated_on']=$lead_details[0]->created_at->format('Y-m-d');
        $response['followup_status_type_id']=$lead_details[0]->followup_status_type_id;        
        $follow=Followup::where('lead_management_id',$lead_details[0]->id)->orderBy('created_at','desc')->get();
        if(count($follow)>0)
        {
          $response['next_followup_date']=$follow[0]->next_followup_date;
        }
        $followup_details=Followup::where('lead_management_id',$lead_details[0]->id)->get();
        $response_data =[];
        foreach($followup_details as $follow)
        {
          $follow_det= [];
          $follow_det['followup_status_type_name']=isset($follow->followup_status_type_det) ? ($follow->followup_status_type_det->followup_status_name) : '';
          $follow_det['followup_date']=isset($follow->followup_date) ? ($follow->followup_date) : '';
          $follow_det['followup_remarks']=isset($follow->remarks) ? ($follow->remarks) : '';
          array_push($response_data,$follow_det);   
        }    
        $response['last_followup_details']=$response_data;
        // enrolled_details
        $customer_tables = Customer::where('lead_management_id','=',$lead_details[0]->id)->get();
        $enroll_data = [];  
        foreach($customer_tables as $customer_table)
        {
          $chit_tables = ChitDetail::where('customer_id','=',$customer_table->id)->get();
          foreach($chit_tables as $chit_table)
          { 
            $enrol_det = [];
            if($chit_table->chit_type !=3)
            {
              $enrol_det['group_name'] = isset($chit_table->group_det) ? ($chit_table->group_det->group_name) : "";
            }
            if($chit_table->chit_type == 3)
            {
              $slab_types = SlabDue::where('id',$chit_table->slab_id)->get();   
              $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
              $enrol_det['group_name'] = "Commitment"."-".$slab_name;
            }
            $bidding = BiddingDetail::selectRaw('*,sum(current_installment_amount)as total_install')->where('group_id','=',$chit_table->group_id)->groupBy('group_id')->get();
            $total_install = isset($bidding[0]->total_install) ? ($bidding[0]->total_install) : '0';
            $receipts= Receipt::selectRaw('*,sum(received_amount) as total_received, sum(penalty) as total_penalty, sum(bonus) as total_bonus ')->where('enrollment_id','=',$chit_table->id)->where('group_id','=',$chit_table->group_id)->groupBy('enrollment_id')->get();
            // + sum(penalty)
            $received_amounts = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : '0'; 
            $penalty_amount =  isset($receipts[0]->total_penalty) ? ($receipts[0]->total_penalty) : '0';
            $bonus_amount =  isset($receipts[0]->total_bonus) ? ($receipts[0]->total_bonus) : '0';
            $total_received = $received_amounts + $penalty_amount + $bonus_amount;
            $enrol_det['total_received']= strval(round($total_received));

            $pending_amount = $total_install - $total_received;
            $enrol_det['pending_amount'] = strval(round($pending_amount));

            // Advance Amount
            $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$chit_table->id,'status'=>1))->groupBy('enrollment_id')->get();
            $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
            $advance_amount = $total_advance ;
            $enrol_det['advance_amount'] = strval(round($advance_amount));

            array_push($enroll_data,$enrol_det);
          }                    
        }
        $response['enrolled_details']= $enroll_data;
      }
      return response()->json($response,200);
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function update_lead_detail(Request $request)
  {
    try 
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('tenant_id'=>$request->tenant_id,'id'=>$request->lead_id);
      $lead_management=LeadManagement::where($cond)->get();
      if(isset($lead_management)) 
      {   
        $lead = leadManagement::find($request->lead_id); 
        $lead->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
        $lead->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
        $lead->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
        $lead->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : ''; 
        $lead->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
        $lead->phone_no = isset($request->phone_no) ? ($request->phone_no) : 0;
        $lead->email_id = isset($request->email_id) ? ($request->email_id) : '';
        $lead->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
        $lead->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : ''; 
        $lead->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id): 0;    
        $lead->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
        if($lead->save()) { 
          $followups= new Followup();
          $followups->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0; 
          $followups->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
          $followups->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
          $followups->followup_date = isset($request->followup_date) ? ($request->followup_date) : '0000-00-00';                   
          $followups->lead_management_id = isset($lead->id) ? ($lead->id) : 0;
          $followups->next_followup_date = isset($request->next_followup_date) ? ($request->next_followup_date) : '0000-00-00';
          $followups->remarks = isset($request->remarks) ? ($request->remarks) : '' ;
          $followups->created_by = isset($request->created_by) ? ($request->created_by) : 0; 
          $followups->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 0; 
          $followups->status = isset($request->status) ? ($request->status) : 1;
          $followups->save();
        }
        $response['status'] = "Success";
        $response['msg'] = "Successfully";
        return response()->json($response,200);
      }
      else{
        $response['status'] = "Success";
        $response['msg'] = \Lang::get('api.no_lead_found');
        return response()->json($response,200);
      }
    }
    catch(\Exception $e) { 
      $response['status'] = "Error";
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function entrollment_wise_group_details(Request $request)
  {
    try
    {  // customer_id, tenant_id
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=array('id'=>$request->entrollment_id,'tenant_id'=>$request->tenant_id);
      $entrollments=ChitDetail::where($cond)->get();
      if($entrollments)
      { 
        $response_data = [];
        $from_date =0;
        $to_date =0;
        $current_instal_amt = 0;
        foreach($entrollments as $entrol) 
        { 
          $entrollment_det = [];
          $cr_date = date('Y-m-d');
          $entrollment_det['customer_code'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->customer_code)) : '';
          $entrollment_det['customer_mobile_no'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->mobile_no)) : '';
          $entrollment_det['customer_phone_no'] = isset($entrol->customer_det) ? (strval($entrol->customer_det->phone_no)) : '';
          $entrollment_det['entrollment_id'] = strval($entrol->id);
          $entrollment_det['group_id'] = "";
          $entrollment_det['group_name']="";
          $entrollment_det['chit_value'] = "";
          $entrollment_det['auction_date'] = "";
          $entrollment_det['ticket_no'] = "";
          $entrollment_det['install_amount'] = "";
          $entrollment_det['paid_amount'] = "";
          $entrollment_det['pending_amount'] = ""; 
          $entrollment_det['advance_amount'] = "";
          $entrollment_det['pending_days'] = "";
          $entrollment_det['bonus'] = "";
          $entrollment_det['discountonpenalty'] = "";
          $entrollment_det['discountondue'] = "";
          if($entrol->prized_status == 0)
          {
              $entrollment_det['prized_status'] = "Non-Prized";
          }
          else
          {
              $entrollment_det['prized_status'] = "Prized";
          }
          $entrollment_det['installments_det'] = "";  
$penalty = 0;
          // Advance Amount
          $receipts = Receipt::selectRaw('*,sum(received_amount)as total_received,sum(penalty)as penalty')->where(array('enrollment_id'=>$entrol->id,'payment_type_id'=>7))->groupBy('enrollment_id')->get();
          $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
          $penalty = isset($receipts[0]->penalty) ? ($receipts[0]->penalty) : "0";
          $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$entrol->id,'status'=>1))->groupBy('enrollment_id')->get();
          $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
          $advance_amount = $total_advance - $total_received - $penalty;
          $entrollment_det['advance_amount'] = strval($advance_amount);
          
          if($entrol->chit_type != 3)
          { //  Non-Commitment Chit  

            $entrollment_det['group_id'] = strval($entrol->group_id);
            $entrollment_det['group_name'] = isset($entrol->group_det) ? ($entrol->group_det->group_name) : "";
            $entrollment_det['ticket_no'] = strval($entrol->ticket_no);
            $chit_value = isset($entrol->group_det->scheme_det) ? (strval(round($entrol->group_det->scheme_det->chit_value))) : "0";
			 $entrollment_det['chit_value'] = $chit_value * $entrol->ticket_type;
            $entrollment_det['auction_date'] = isset($entrol->group_det) ? ($entrol->group_det->auction_date) : "0000-00-00";

            // Paid amount - entroll_id wise
            $receipt=Receipt::selectRaw('sum(received_amount) as total_received')->where('enrollment_id',$entrol->id)->where('status','=',1)->groupBy('enrollment_id')->get();
            $paid = 0;
            $total_install_amount = 0;  
            
            $paid =  isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0"; 
            $entrollment_det['paid_amount'] = strval(round($paid));                       
                      
            // Installment amount -  group_id wise
            $cond1=array('group_id'=>$entrol->group_id);                    
            $bidding_details=BiddingDetail::selectRaw('*,sum(current_installment_amount) as tot_instal_amt')->where($cond1)->groupBy('group_id')->get();
              
            $total_install_amount = isset($bidding_details[0]->tot_instal_amt) ? ($bidding_details[0]->tot_instal_amt) : "0";
			$total_install_amount = $total_install_amount * $entrol->ticket_type;
            $entrollment_det['install_amount'] = strval(round($total_install_amount));
			
            
            $entrollment_det['pending_amount'] = strval(round($total_install_amount-$paid));
            
            $max_id=BiddingDetail::where(array('group_id'=>$entrol->group_id))->max('id');
            $current_install= BiddingDetail::where('id',$max_id)->pluck('current_installment_amount');                       
            $current_instal_amt_11 =isset($current_install[0]) ? ($current_install[0]) : 0;    
            $current_instal_amt = strval(round($current_instal_amt_11));

            if($entrollment_det['pending_amount']==$current_instal_amt)
            {   // pending_amount == current_installment_amount,  auction date 
              $cond2=array('group_id'=>$entrol->group_id);
              $bidding_det = BiddingDetail::where($cond2)->max('id');
              $auction_datess = BiddingDetail::where('id','=',$bidding_det)->pluck('auction_date');
              if(count($auction_datess)>0)
              { 
                $from_date = $auction_datess[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');
                $entrollment_det['pending_days'] = strval($days);
              }
            }
            else
            { // pending_amount != 0, receipt date
              $cond3=array('enrollment_id'=>$entrol->id);  
              $receipt_det = Receipt::where($cond3)->where('status','=',1)->max('id');
              $receipt_dates= Receipt::where('id',$receipt_det)->pluck('receipt_date');
              if(count($receipt_dates)>0)
              {  // if receipt entry greater than 0, Receipt date
                $from_date =$receipt_dates[0]; 
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');    
                $entrollment_det['pending_days'] = strval($days);
              }
              else 
              { // if receipt entry less than 0, auction date
                $cond4=array('group_id'=>$entrol->group_id); 
                $auction_dates = BiddingDetail::where($cond4)->pluck('auction_date');
                if(count($auction_dates)>0)
                { 
                  $from_date = $auction_dates[0];
                  $to_date = date('Y-m-d');
                  $date1 = new DateTime($from_date);
                  $date2 = new DateTime($to_date);
                  $interval = $date1->diff($date2);
                  $days = $interval->format('%a');
                  $entrollment_det['pending_days'] = strval($days);
                }
              }
            }                          
            // Bonus calculation over all 
            if($entrol->customer_det->bonus_days!=0 && $entrol->customer_det->customer_bonus!=0)
            {
              $bonus_days = $entrol->customer_det->bonus_days;
              $bonus_percentage = $entrol->customer_det->customer_bonus;
            }    
            elseif($entrol->group_det->group_bonus_days!=0 && $entrol->group_det->group_base_bonus!=0)
            {
              $bonus_days = $entrol->group_det->group_bonus_days;
              $bonus_percentage = $entrol->group_det->group_base_bonus;
            }
            elseif($entrol->branch_det->bonus_days!=0 && $entrol->branch_det->bonus!=0)
            {
              $bonus_days = $entrol->branch_det->bonus_days;
              $bonus_percentage = $entrol->branch_det->bonus;
            }    
            else
            {
              $bonus_days = 0;
              $bonus_percentage = 0;
            }               
            $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
            $check_auction = BiddingDetail::where('id',$bid11)->get();
            $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
            // Auction on holiday check for bonus
            $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
            $holiday_count = 0;
            if(count($auction_on_holiday)>0)
            { 
              $holiday_count += $auction_on_holiday->count();
            }

            $bonus_days_include_holiday = $bonus_days + $holiday_count;
            
            if($entrollment_det['pending_days'] <=  $bonus_days_include_holiday)
            {              
              if($entrollment_det['pending_amount']==$current_instal_amt)
              { 
                $bonus_calculation = $current_instal_amt * $bonus_percentage / 100 ; 
                $entrollment_det['bonus'] = strval(round($bonus_calculation));
              }
              else
              {
                $entrollment_det['bonus'] = "";
              }
            }
            else
            {
              $entrollment_det['bonus'] = "";
            }
              
            // Installment Wise 
            $install_details=BiddingDetail::where('group_id',$entrol->group_id)->where('auction_type','!=','duplicate')->groupBy('installment_no')->get();    
            $install_det = [];
            foreach($install_details as $install_detail)
            { 
              $cust_det = [];
              $cond45 = array('installment_no'=>$install_detail->installment_no,'enrollment_id'=>$entrol->id); 
              $receipt1=Receipt::selectRaw('*,sum(received_amount)  as total_received')->where($cond45)->where('status','=',1)->get();
              $pending_due = 0;
              $auction_id = 0;
              $paid_due = 0;
              $current_instal_amt1 =0;
              $current_instal_amt1 = strval(round($install_detail->current_installment_amount));
			  $current_instal_amt1 = $current_instal_amt1 * $entrol->ticket_type;
              if(count($receipt1)>0)
              {
                $paid_due = isset($receipt1[0]->total_received) ? ($receipt1[0]->total_received) : "0";
                $pending_due = $current_instal_amt1 - $paid_due;
                $auction_id = $receipt1[0]->auction_id;
              }
              else
              {  
                $pending_due =  $current_instal_amt1;
              }
              if($paid_due != $current_instal_amt1)
              {   
                $cust_det['entrollment_id'] = strval($entrol->id);
                $cust_det['auction_no'] = strval($install_detail->installment_no);
                $cust_det['bidding_id'] = strval($install_detail->id);
                $cust_det['pending_due'] = strval(round($pending_due));
                $cust_det['discount_on_due'] = "";
                $cust_det['discount_on_penalty'] = "";
                $cust_det['bonus_days'] = "";
                $cust_det['penalty_percentage'] = "";
                $cust_det['bonus_percentage'] = "";
                $cust_det['bonus_amount'] = "";
                $cust_det['instal_pending_day'] = "";
                $cust_det['paid_amount'] = strval(round($paid_due));
                $cust_det['current_installment_amount'] = strval(round($install_detail->current_installment_amount));
                $penlaty_days = $install_detail->group_det->group_penalty_days;

                if($cust_det['pending_due']==$current_instal_amt1)
                {  
                  $cond1 = array('id'=>$install_detail->id);
                  $bid= BiddingDetail::where($cond1)->max('id');
                  $auction_date1 = BiddingDetail::where('id',$bid)->get();
                  if(count($auction_date1)>0)
                  {
                    $auction_date = $auction_date1[0]->auction_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($auction_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days);
                  }  
                }
                else
                {
                  $cond2 = array('auction_id'=>$install_detail->id);
                  $receipts = Receipt::where($cond2)->where('status','=',1)->max('id');
                  $receipt_date1 = Receipt::where('id',$receipts)->get();
                  if(count($receipt_date1)>0)
                  {
                    $receipt_date = $receipt_date1[0]->receipt_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($receipt_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days); 
                  }
                  else
                  {   
                    $cond3 = array('id'=>$install_detail->id);
                 //   $bidd = BiddingDetail::where($cond3)->max('id');
                    $auction_date2 = BiddingDetail::where($cond3)->get();
                    if(count($auction_date2)>0)
                    {
                      $auction_date = $auction_date2[0]->auction_date;
                      $current_date =date('Y-m-d');
                      $date1 = new DateTime($auction_date);
                      $date2 = new DateTime($current_date);
                      $interval = $date1->diff($date2);
                      $days = $interval->format('%a');
                      $cust_det['instal_pending_day'] = strval($days); 
                    }
                  }
                }
				
               // Penalty Calculation installment wise
                // if($install_detail->enrollment_det->customer_det->penalty_days!=0 && $install_detail->enrollment_det->customer_det->customer_penalty_interest!=0)
                // {
                  // $inst_penalty_days = $install_detail->enrollment_det->customer_det->penalty_days;
                  // $inst_penalty_percentage = $install_detail->enrollment_det->customer_det->customer_penalty_interest;
                // }
                // elseif($install_detail->group_det->group_penalty_days!=0 && $install_detail->group_det->group_base_penalty!=0)
                // {
                  // $inst_penalty_days = $install_detail->group_det->group_penalty_days;
                  // $inst_penalty_percentage = $install_detail->group_det->group_base_penalty;                  
                // }
                // elseif($install_detail->branch_det->penalty_days!=0 && $install_detail->branch_det->branch_wise_penalty!=0)
                // {
                  // $inst_penalty_days = $install_detail->branch_det->penalty_days;
                  // $inst_penalty_percentage = $install_detail->branch_det->branch_wise_penalty;
                // }
                // else
                // {
                  $inst_penalty_days = "0";
                  $inst_penalty_percentage = "0";
                // }
				
                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                $pend_days_int = 0;                
                $cust_det['penalty_amounts'] = "";            
                if($cust_det['instal_pending_day'] > $inst_penalty_days)
                {        
                  $branch=Branch::where('id',$entrol->branch_id)->get();
                  if($entrol->prized_status==0)
                  {
                    $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                    $pend_days_int = $cust_det['instal_pending_day'] * ($non_prize/ 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  } 
                  else
                  {               
                    $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                    $pend_days_int = $cust_det['instal_pending_day'] * ($prize / 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  }                      
                } 
				
                // Bonus Calculation
                // if($install_detail->enrollment_det->customer_det->bonus_days!=0 && $install_detail->enrollment_det->customer_det->customer_bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->enrollment_det->customer_det->bonus_days;
                  // $inst_bonus_percentage = $install_detail->enrollment_det->customer_det->customer_bonus;
                // }    
                // elseif($install_detail->group_det->group_bonus_days!=0 && $install_detail->group_det->group_base_bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->group_det->group_bonus_days;
                  // $inst_bonus_percentage = $install_detail->group_det->group_base_bonus;
                // }
                // elseif($install_detail->branch_det->bonus_days!=0 && $install_detail->branch_det->bonus!=0)
                // {
                  // $inst_bonus_days = $install_detail->branch_det->bonus_days;
                  // $inst_bonus_percentage = $install_detail->branch_det->bonus;
                // }    
                // else
                // {
                  $inst_bonus_days = 0;
                  $inst_bonus_percentage = 0;
                // }   
                $cust_det['bonus_days'] = strval($inst_bonus_days);
                $cust_det['bonus_percentage'] = strval($inst_bonus_percentage);

                $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
                $check_auction = BiddingDetail::where('id',$bid11)->get();
                $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                // Auction on holiday check for bonus
                $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                $holiday_count = 0;
                if(count($auction_on_holiday)>0)
                { 
                  $holiday_count += $auction_on_holiday->count();
                }

                $bonus_days_include_holiday = $inst_bonus_days + $holiday_count;
                
                if($cust_det['instal_pending_day'] <=  $bonus_days_include_holiday)
                {                    
                  if($cust_det['pending_due']==$current_instal_amt1)
                  {
                    $bonus_cal = $current_instal_amt1 * $inst_bonus_percentage / 100;
                    $cust_det['bonus_amount'] = strval(round($bonus_cal));
                  }
                  else
                  { 
                    $cust_det['bonus_amount'] = "";
                  }
                }
                else
                { 
                  $cust_det['bonus_amount'] = "";
                }
                array_push($install_det,$cust_det);
              }
            }
          }
          if($entrol->chit_type ==3)
          {  // Commitment Chit 

            $slab_types = SlabDue::where('id',$entrol->slab_id)->get();   
            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
            $entrollment_det['group_name'] = "Commitment"."-".$slab_name;
            $entrollment_det['ticket_no'] = "-";
            $entrollment_det['chit_value'] = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 

            $commitment_auction = CommitmentAuction::where('enrollment_id','=',$entrol->id)->get();
            $entrollment_det['auction_date'] = isset($commitment_auction[0]->auction_date) ? ($commitment_auction[0]->auction_date) : "0000-00-00";

            // Paid amount - entroll_id wise
            $receipt=Receipt::selectRaw('*, sum(received_amount) as total_received')->where('enrollment_id',$entrol->id)->where('status','=',1)->groupBy('enrollment_id')->get();
            $paid = 0;
            $total_install_amount = 0;  
          
            $paid =  isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : "0"; 
            $entrollment_det['paid_amount'] = strval(round($paid));
                  
            // Installment amount -  group_id wise
            $bidding_details=CommitmentAuction::selectRaw('*,sum(due_amount) as tot_instal_amt')->where(array('enrollment_id'=>$entrol->id))->groupBy('enrollment_id')->get();
            $total_install_amount = isset($bidding_details[0]->tot_instal_amt) ? ($bidding_details[0]->tot_instal_amt) : "0";
            $entrollment_det['install_amount'] = strval(round($total_install_amount));
            $entrollment_det['pending_amount'] = strval(round($total_install_amount - $paid));            
            $max_id=CommitmentAuction::where(array('enrollment_id'=>$entrol->id))->max('id');
            $current_install= CommitmentAuction::where('id',$max_id)->pluck('due_amount');
            $current_instal_amt_11 =isset($current_install[0]) ? ($current_install[0]) : 0;    
            $current_instal_amt = strval(round($current_instal_amt_11));

            if($entrollment_det['pending_amount']==$current_instal_amt)
            {  // pending_amount == current_installment_amount,  auction date 
              $cond2=array('enrollment_id'=>$entrol->id);
              $bidding_det = CommitmentAuction::where($cond2)->max('id');
              $auction_datess = CommitmentAuction::where('id','=',$bidding_det)->pluck('auction_date');
              if(count($auction_datess)>0)
              { 
                $from_date = $auction_datess[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');
                $entrollment_det['pending_days'] = strval($days);
              }
            }
            else
            { // pending_amount != 0, receipt date
              $cond3=array('enrollment_id'=>$entrol->id);  
              $receipt_det = Receipt::where($cond3)->where('status','=',1)->max('id');
              $receipt_dates= Receipt::where('id',$receipt_det)->pluck('receipt_date');
              if(count($receipt_dates)>0)
              {  // if receipt entry greater than 0, Receipt date
                $from_date =$receipt_dates[0];
                $to_date = date('Y-m-d');
                $date1 = new DateTime($from_date);
                $date2 = new DateTime($to_date);
                $interval = $date1->diff($date2);
                $days = $interval->format('%a');    
                $entrollment_det['pending_days'] = strval($days);
                }
                else 
                { // if receipt entry less than 0, auction date
                  $cond4=array('enrollment_id'=>$entrol->id); 
                  $auction_dates = CommitmentAuction::where($cond4)->pluck('auction_date');
                  if(count($auction_dates)>0)
                  {
                    $from_date = $auction_dates[0];
                    $to_date = date('Y-m-d');
                    $date1 = new DateTime($from_date);
                    $date2 = new DateTime($to_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $entrollment_det['pending_days'] = strval($days);
                  }
                }
              }    
              // Bonus calculation over all 
              if($entrol->customer_det->bonus_days!=0 && $entrol->customer_det->customer_bonus!=0)
              {
                $bonus_days = $entrol->customer_det->bonus_days;
                $bonus_percentage = $entrol->customer_det->customer_bonus;
              }    
              elseif($entrol->branch_det->bonus_days!=0 && $entrol->branch_det->bonus!=0)
              {
                $bonus_days = $entrol->branch_det->bonus_days;
                $bonus_percentage = $entrol->branch_det->bonus; 
              }    
              else
              {
                $bonus_days = 0;
                $bonus_percentage = 0;
              }   

              $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
              $check_auction = BiddingDetail::where('id',$bid11)->get();
              $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
              // Auction on holiday check for bonus
              $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
              $holiday_count = 0;
              if(count($auction_on_holiday)>0)
              { 
                $holiday_count += $auction_on_holiday->count();
              }

              $bonus_days_include_holiday = $bonus_days + $holiday_count;
              
              if($entrollment_det['pending_days'] <=  $bonus_days_include_holiday)
              {                     
                if($entrollment_det['pending_amount']==$current_instal_amt)
                { 
                  $bonus_calculation = $current_instal_amt * $bonus_percentage / 100 ; 
                  $entrollment_det['bonus'] = strval(round($bonus_calculation));
                }
                else
                {
                  $entrollment_det['bonus'] = "";
                }
              }
              else
              {
                $entrollment_det['bonus'] = "";
              }
              // Installment Wise 
              $install_details=CommitmentAuction::where('enrollment_id',$entrol->id)->get();    
              $install_det = [];
              foreach($install_details as $install_detail)
              { 
              $cust_det = [];
              $cond45 = array('auction_id'=>$install_detail->id,'enrollment_id'=>$entrol->id); 
              $receipt1=Receipt::selectRaw('*,sum(received_amount)  as total_received')->where($cond45)->where('status','=',1)->groupBy('auction_id')->get();
              $pending_due = 0;
              $auction_id = 0;
              $paid_due = 0;
              $current_instal_amt1 =0;
              $current_instal_amt1 = strval(round($install_detail->due_amount));
              if(count($receipt1)>0)
              {
                $paid_due = isset($receipt1[0]->total_received) ? ($receipt1[0]->total_received) : "0";
                $pending_due = $current_instal_amt1 - $paid_due;
                $auction_id = $receipt1[0]->auction_id;
              }
              else
              {  
                $pending_due =  $current_instal_amt1;
              }
              if($paid_due != $current_instal_amt1)
              {   
                $cust_det['entrollment_id'] = strval($entrol->id);
                $cust_det['auction_no'] = strval($install_detail->installment_no);
                $cust_det['bidding_id'] = strval($install_detail->id);
                $cust_det['pending_due'] = strval(round($pending_due));
                $cust_det['discount_on_due'] = "";
                $cust_det['discount_on_penalty'] = "";
                $cust_det['bonus_days'] = "0";
                $cust_det['penalty_percentage'] = "0";
                $cust_det['bonus_percentage'] = "0";
                $cust_det['bonus_amount'] = "";
                $cust_det['instal_pending_day'] = "";
                $cust_det['paid_amount'] = strval(round($paid_due));
                $cust_det['current_installment_amount'] = strval(round($install_detail->due_amount));
                $penlaty_days = "0";
                if($cust_det['pending_due']==$current_instal_amt1)
                {  
                  $cond1 = array('id'=>$install_detail->id);
                  $bid= CommitmentAuction::where($cond1)->max('id');
                  $auction_date1 = CommitmentAuction::where('id',$bid)->get();
                  if(count($auction_date1)>0)
                  {
                    $auction_date = $auction_date1[0]->auction_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($auction_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days);
                 }  
                }
                else
                {
                  $cond2 = array('auction_id'=>$install_detail->id);
                  $receipts = Receipt::where($cond2)->where('status','=',1)->max('id');
                  $receipt_date1 = Receipt::where('id',$receipts)->get();
                  if(count($receipt_date1)>0)
                  {
                    $receipt_date = $receipt_date1[0]->receipt_date;
                    $current_date =date('Y-m-d');
                    $date1 = new DateTime($receipt_date);
                    $date2 = new DateTime($current_date);
                    $interval = $date1->diff($date2);
                    $days = $interval->format('%a');
                    $cust_det['instal_pending_day'] = strval($days); 
                  }
                  else
                  {   
                    $cond3 = array('id'=>$install_detail->id);
                    $auction_date2 = CommitmentAuction::where($cond3)->get();
                    if(count($auction_date2)>0)
                    {
                      $auction_date = $auction_date2[0]->auction_date;
                      $current_date =date('Y-m-d');
                      $date1 = new DateTime($auction_date);
                      $date2 = new DateTime($current_date);
                      $interval = $date1->diff($date2);
                      $days = $interval->format('%a');
                      $cust_det['instal_pending_day'] = strval($days); 
                    }
                  }
                }
                // Penalty Calculation installment wise
                if($install_detail->customer_det->penalty_days!=0 && $install_detail->customer_det->customer_penalty_interest!=0)
                { 
                  $inst_penalty_days = $install_detail->customer_det->penalty_days;
                  $inst_penalty_percentage = $install_detail->customer_det->customer_penalty_interest;
                }   
                elseif($install_detail->branch_det->penalty_days!=0 && $install_detail->branch_det->branch_wise_penalty!=0)
                {
                  $inst_penalty_days = $install_detail->branch_det->penalty_days;
                  $inst_penalty_percentage = $install_detail->branch_det->branch_wise_penalty;
                }
                else
                {
                  $inst_penalty_days = "0";
                  $inst_penalty_percentage = "0";
                }
                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                $pend_days_int = 0;                
                $cust_det['penalty_amounts'] = "";            
                if($cust_det['instal_pending_day'] > $inst_penalty_days)
                {        
                  $branch=Branch::where('id',$entrol->branch_id)->get();
                  if($entrol->prized_status==0)
                  {
                    $non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
                    $pend_days_int = $cust_det['instal_pending_day'] * ($non_prize/ 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  } 
                  else
                  {               
                    $prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
                    $pend_days_int = $cust_det['instal_pending_day'] * ($prize / 100);
                    $current_month_days = date('t'); 
                    $total_days_int = $pend_days_int / $current_month_days;
                    $penalty = $total_days_int * $current_instal_amt1;
                    $cust_det['penalty_amounts'] = strval(round($penalty));
                  }                      
                }  
                // Bonus Calculation
                if($install_detail->customer_det->bonus_days!=0 && $install_detail->customer_det->customer_bonus!=0)
                { 
                  $inst_bonus_days = $install_detail->customer_det->bonus_days;
                  $inst_bonus_percentage = $install_detail->customer_det->customer_bonus;
                }   
                elseif($install_detail->branch_det->bonus_days!=0 && $install_detail->branch_det->bonus!=0)
                {
                  $inst_bonus_days = $install_detail->branch_det->bonus_days;
                  $inst_bonus_percentage = $install_detail->branch_det->bonus;
                }    
                else
                {
                  $inst_bonus_days = 0;
                  $inst_bonus_percentage = 0;
                }   
                $cust_det['bonus_days'] = strval($inst_bonus_days);
                $cust_det['bonus_percentage'] = strval($inst_bonus_percentage);

                $bid11 = BiddingDetail::Where('group_id',$entrol->group_id)->max('id');
                $check_auction = BiddingDetail::where('id',$bid11)->get();
                $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
                // Auction on holiday check for bonus
                $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$entrol->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                $holiday_count = 0;
                if(count($auction_on_holiday)>0)
                { 
                  $holiday_count += $auction_on_holiday->count();
                }

                $bonus_days_include_holiday = $inst_bonus_days + $holiday_count;
                
                if($cust_det['instal_pending_day'] <=  $bonus_days_include_holiday)
                {                 
                  if($cust_det['pending_due']==$current_instal_amt1)
                  {
                    $bonus_cal = $current_instal_amt1 * $inst_bonus_percentage / 100;
                    $cust_det['bonus_amount'] = strval(round($bonus_cal));
                  }
                  else
                  { 
                    $cust_det['bonus_amount'] = "";
                  }
                }
                else
                { 
                  $cust_det['bonus_amount'] = "";
                }
                array_push($install_det,$cust_det);
              }
            }
          }   
          $entrollment_det['installments_det'] = $install_det;
          array_push($response_data,$entrollment_det);   
        } 
        if($entrollments) {
          return response()->json($response_data,200);
        }   
        else
        {
          $response['status'] = 'Error';
          $response['msg'] =\Lang::get('api.global_error');
          return response()->json($response, 401);
        }                         
      }
      else{
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }
    } 
    catch(\Exception $e){
      $response['status'] = 'Error';
      $response['msg'] = '';
      return response()->json($response, 401);
    }
  } 

  public function mobile_add_receipt(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'tenant_id' => 'required',
      'branch_id' => 'required',
      'customer_id' => 'required',
      
      'employee_id' => 'required',
      'enrollment_id'=> 'required',
    ]);
    if ($validator->fails()) { 
      $response['status'] = "Error";
      $response['msg'] = $validator->errors()->first();
      return response()->json($response, 200);
    }
    else {
      try{ 
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');
        if(isset($request->Installments)) 
        {  
          $count = count($request->Installments); 
          $receipt_no = '';
          $tenantid = $request->tenant_id;
		  if($tenantid == 42){
			$advance_receipt = new AdvanceReceipt(); 
          $receipt_no1 ="";
          
            $advance_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
            $advance_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
            $advance_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
            $customerid = $advance_receipt->customer_id;
            $pendingamount = $advance_receipt->pending_amount;
            $advance_receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
            $advance_receipt->group_id = isset($request->group_id) ? ($request->group_id) : 0;
            $advance_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;                 
            $advance_receipt->employee_branch_id = isset($request->employee_branch_id) ? ($request->employee_branch_id) : 0;
            $advance_receipt->receipt_amount = isset($request->amount) ? ($request->amount) : 0;
            $receiptamount = $advance_receipt->receipt_amount;
			$advance_receipt->accounts_date = date('Y-m-d'); 
            $advance_receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : "0000-00-00";
            $advance_receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
            $advance_receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
            $advance_receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
            $advance_receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : "0000-00-00";
            $advance_receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
            $advance_receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
            $advance_receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
            $advance_receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
            $advance_receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : "0000-00-00";                     
            $advance_receipt->receipt_mode = 'Mobile';
            $advance_receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
            $advance_receipt->printed = isset($request->printed) ? ($request->printed) : 0;
            $advance_receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
            $advance_receipt->recpt_excess_no = isset($receipt->receipt_no) ? ($receipt->receipt_no) : '';   
            $advance_receipt->offline_receipt_no = isset($request->offline_receipt_no) ? ($request->offline_receipt_no) : '';             
            $advance_receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
            $advance_receipt->status = isset($request->status) ? ($request->status) : 1;
            if($request->payment_type_id == 2){
              $advance_receipt->status = isset($request->status) ? ($request->status) : 2;
            }
            if($advance_receipt->receipt_amount !=0) {
              if($advance_receipt->save()) {
                if($receipt_no1=='') {
                  $receipt_no1 = 'ADV-'.sprintf('%05d', $advance_receipt->id);
                }                        
                $advance_receipt->receipt_no = $receipt_no1; 
                $advance_receipt->save();
				$receipt_no = $receipt_no1;
				
				if($advance_receipt->payment_type_id == 1){
						$add_exp = new AccountExpense();                
						$add_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						$add_exp->voucher_no = $receipt_no1;
						$add_exp->debit_account_head = 100;
						$add_exp->debit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 1;
						$cus_name = Customer::where('id',$customer_id)->pluck('name');
						$cus_last_name = Customer::where('id',$customer_id)->pluck('last_name');
					   
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0] . " as chit installment ";
						$add_exp->remarks = $remarks;
						$add_exp->debit_remarks = $remarks;
						$add_exp->save();
						
						$add_deb_exp = new AccountExpense();                
						$add_deb_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_deb_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_deb_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						$add_deb_exp->voucher_no = $receipt_no1;
						$add_deb_exp->debit_account_head = 0;
						$add_deb_exp->debit_amount = 0;
						$add_deb_exp->credit_account_head = 42;
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0] . " as chit installment " . $advance_receipt->installment_no;
						$add_deb_exp->remarks = $remarks;
						$add_deb_exp->credit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_deb_exp->status = 1;
						$add_deb_exp->save();
						} else if(($advance_receipt->payment_type_id == 2) || ($advance_receipt->payment_type_id == 4) || ($advance_receipt->payment_type_id == 3)){
											
						$bank_id = isset($advance_receipt->bank_name_id) ? ($advance_receipt->bank_name_id) : 0;
						$bank = BankDetail::where('id','=',$advance_receipt->bank_name_id)->get();
						foreach($bank as $banks) {
							$bank_account_no = isset($banks->account_number) ? ($banks->account_number) : 0;
						}
						$cus_name = Customer::where('id',$customer_id)->pluck('name');
						$cus_last_name = Customer::where('id',$customer_id)->pluck('last_name');
						$accnt_head = AccountHead::where('bank_account_number','=',$bank_account_no)->get();
						foreach($accnt_head as $accnt_heads) {
							$account_head_id = isset($accnt_heads->id) ? ($accnt_heads->id) : 0;
						}
						
						$add_exp = new AccountExpense();                
						$add_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						
						$add_exp->voucher_no = $receipt_no1;
						$remarks = "Being the bank amount received from " . $cus_name[0] . ' ' . $cus_last_name[0];
						$add_exp->remarks = $remarks;
						$add_exp->debit_account_head = 100;
						
						$add_exp->debit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 0;
						
						$add_exp->save();
					
						$add_deb_exp = new AccountExpense();                
						$add_deb_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_deb_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_deb_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						$add_deb_exp->voucher_no = $receipt_no1;
						
						$add_deb_exp->debit_account_head = 0;
						$add_deb_exp->debit_amount = 0;
						$remarks = "Being the bank amount received from " . $cus_name[0] . ' ' . $cus_last_name[0];
						$add_deb_exp->remarks = $remarks;
						$add_deb_exp->credit_account_head = $account_head_id;
						$add_deb_exp->credit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_deb_exp->status = 1;
						$add_deb_exp->save();
						}
						else {
						$add_exp = new AccountExpense();                
						$add_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						$add_exp->voucher_no = $receipt_no1;
						$add_exp->debit_account_head = 1;
						$add_exp->debit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 1;
						$cus_name = Customer::where('id',$customer_id)->pluck('name');
						$cus_last_name = Customer::where('id',$customer_id)->pluck('last_name');
					   
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0] . " as chit installment ";
						$add_exp->remarks = $remarks;
						$add_exp->debit_remarks = $remarks;
						$add_exp->save();	
						}
              }
              else {
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
              } 
            }
            else {
              $response['status']="Error";
              $response['msg']=\Lang::get('api.received_amt_not_found');
              return response()->json($response,401);
            }                        
            
		  } else {
          for($i=0; $i<$count; $i++)
          { 
            $receipt = new Receipt(); 
            $receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
            $receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
            $receipt->other_branch = isset($request->other_branch) ? ($request->other_branch) : 0;
            $receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
            $customerid = $receipt->customer_id;
            $pendingamount = $receipt->pending_amount;
            $receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
            $receipt->group_id = isset($request->group_id) ? ($request->group_id) : 0;
            $groupid = $receipt->group_id;
            $receipt->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : '';
            $receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
            $receipt->employee_branch_id = isset($request->employee_branch_id) ? ($request->employee_branch_id) : 0;
            $receipt->adjust_id = isset($request->adjust_id) ? ($request->adjust_id) : 0;
            $receipt->amount = isset($request->amount) ? ($request->amount) : 0;
            $receiptamount = $receipt->amount;
            $receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : '0000-00-00';         
			//$receiptdate = $receipt->receipt_date;
            $receipt->type_of_collection = 'receipt';
            $receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
            $receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
            $receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
            $receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : '0000-00-00';   
            $receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : '0000-00-00';   
            $receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
            $receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
            $receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
            $receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : '0000-00-00';
            $receipt->transaction_bill = isset($request->transaction_bill) ? ($request->transaction_bill) : '';
            $receipt->transaction_book = isset($request->transaction_book) ? ($request->transaction_book) : '';
            $receipt->neft_adjust_id = isset($request->neft_adjust_id) ? ($request->neft_adjust_id) : 0;
            $receipt->receipt_mode =  'Mobile';
            $receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
            $receipt->accounts_date = date('Y-m-d');
            $receipt->printed = isset($request->printed) ? ($request->printed) : 0;
            $receipt->auction_id = isset($request->Installments[$i]['auction_id']) ? $request->Installments[$i]['auction_id'] : '';
            $receipt->installment_no = isset($request->Installments[$i]['installment_no']) ? $request->Installments[$i]['installment_no'] : '';
            $receipt->pending_days = isset($request->Installments[$i]['pending_days']) ? $request->Installments[$i]['pending_days'] : '';
            $receipt->penalty = isset($request->Installments[$i]['penalty_inst_wise']) ? $request->Installments[$i]['penalty_inst_wise'] : '';
            $receipt->bonus = isset($request->Installments[$i]['bonus_inst_wise']) ? $request->Installments[$i]['bonus_inst_wise'] : '';
            $receipt->discount = isset($request->Installments[$i]['discount_inst_wise']) ? $request->Installments[$i]['discount_inst_wise'] : '';
            $receipt->received_amount = isset($request->Installments[$i]['received_amount']) ? $request->Installments[$i]['received_amount'] : '';
            $receipt->offline_receipt_no = isset($request->offline_receipt_no) ? ($request->offline_receipt_no) : '';
            $receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
            $receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
            $receipt->advance_available = 0;
            $receipt->status = isset($request->status) ? ($request->status) : 1;
            if($request->payment_type_id == 2){
              $receipt->status = isset($request->status) ? ($request->status) : 2;
            }
            if($receipt->penalty !=0 || $receipt->received_amount !=0) {
              if($receipt->save()) {
                if($receipt_no=='') {
                  $receipt_no = 'RCPT-'.sprintf('%05d', $receipt->id);
                }
                $receipt->receipt_no = $receipt_no;
                $receipt->save();
              }
            }               
          }
          if($request->adv_receipt_amount !=0 && $request->adv_receipt_amount !="")
          { 
            if($receipt->advance_available == 0)
            {
              $recei = Receipt::where('receipt_no',$receipt->receipt_no)->min('id');
              $rcpt_table = Receipt::Where('id',$recei)->get();
              $rcpt_table[0]->advance_available = 1;
              $rcpt_table[0]->save();
            }    
          }
          $advance_receipt = new AdvanceReceipt(); 
          $receipt_no1 ="";
          if($request->adv_receipt_amount !=0 && $request->adv_receipt_amount !=="")
          {
			
            $advance_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
            $advance_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
            $advance_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
            $customerid = $advance_receipt->customer_id;
            $pendingamount = $advance_receipt->pending_amount;
            $advance_receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
            $advance_receipt->group_id = isset($request->group_id) ? ($request->group_id) : 0;
            $advance_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;                 
            $advance_receipt->employee_branch_id = isset($request->employee_branch_id) ? ($request->employee_branch_id) : 0;
            $advance_receipt->receipt_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
            $receiptamount = $advance_receipt->receipt_amount;
			$advance_receipt->accounts_date = date('Y-m-d'); 
            $advance_receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : "0000-00-00";
            $advance_receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
            $advance_receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
            $advance_receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
            $advance_receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : "0000-00-00";
            $advance_receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
            $advance_receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
            $advance_receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
            $advance_receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
            $advance_receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : "0000-00-00";                     
            $advance_receipt->receipt_mode = 'Mobile';
            $advance_receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
            $advance_receipt->printed = isset($request->printed) ? ($request->printed) : 0;
            $advance_receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
            $advance_receipt->recpt_excess_no = isset($receipt->receipt_no) ? ($receipt->receipt_no) : '';   
            $advance_receipt->offline_receipt_no = isset($request->offline_receipt_no) ? ($request->offline_receipt_no) : '';             
            $advance_receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
            $advance_receipt->status = isset($request->status) ? ($request->status) : 1;
            if($request->payment_type_id == 2){
              $advance_receipt->status = isset($request->status) ? ($request->status) : 2;
            }
            if($advance_receipt->receipt_amount !=0) {
              if($advance_receipt->save()) {
                if($receipt_no1=='') {
                  $receipt_no1 = 'ADV-'.sprintf('%05d', $advance_receipt->id);
                }                        
                $advance_receipt->receipt_no = $receipt_no1; 
                $advance_receipt->save();
				$receipt_no = $receipt_no1;
              }
              else {
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
              } 
            }
            else {
              $response['status']="Error";
              $response['msg']=\Lang::get('api.received_amt_not_found');
              return response()->json($response,401);
            }                        
          } 
		  }
          $amount = 	$request->amount;
				$today_date = date('Y-m-d');
          
           $msg = "Dear Customer, Payment of $amount is Received, Receipt No $receipt_no on $today_date to Chit group New Enrollment, Thank you. More Details Call: 88079 06006 - SPN Chits Pvt Ltd";


	$msg1=$msg;
		//$receiver = '7418470181';
		$receiver = $receipt->customer_det->mobile_no;
		$msg_split = str_split($msg,456);
		
		  
				
            	$ch = curl_init("https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=$msg");
            
            	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				  $result = curl_exec($ch);
				curl_close($ch);
					
		//total paid amount
		  $receipts = Receipt::selectRaw('sum(received_amount)as total_received')->where('enrollment_id', $request->enrollment_id)->where('payment_type_id','!=',7)->get();
          $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
          $advance = AdvanceReceipt::selectRaw('sum(receipt_amount)as total_advance')->where('enrollment_id', $request->enrollment_id)->get();
          $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
          $total_paid_amount = $total_advance + $total_received;
         
		 
		  
          $response['status'] = 'Success';
          $response['msg'] = \Lang::get('api.success_receipt_added');
          // Return the generated number from the loop ($receipt_no), not $receipt->receipt_no: after the loop
          // $receipt is the last iteration which may be unsaved (e.g. only earlier rows had penalty/amount).
          $response['receipt_no'] = $receipt_no;
          $response['total_paid_amount'] = $total_paid_amount; 
          $response['created_at'] = date('d-m-Y h:i:sa'); 
          return response()->json($response,200);
        }
        else {
          $response['status'] = 'Error';
          $response['msg'] = \Lang::get('api.no_data_found');
          return response()->json($response, 401);
        }
      }
      catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        $response['receipt_no'] = ""; 
        return response()->json($response, 401);
      }
    }
  } 

  public function mobile_list_payment_types(Request $request)
  {
    try { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $payment_types = PaymentType::orderBy('id')->where('id','!=',6)->where('id','!=',7)->get();
      $i=1; 
      $response_data = [];
      foreach($payment_types as $payment_type) {
        $state_det = [];
        $state_det['sno'] = $i;
        $state_det['payment_type_id'] = $payment_type->id;
        $state_det['payment_name'] = $payment_type->payment_name;
        array_push($response_data,$state_det);        
        $i++;
      }
      if($payment_types) {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function get_collection_agent_dashboard(Request $request)
  {
    try
    {
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'id'=>$request->user_id);
      $user_table = User::where($cond)->get();
      $role_id = $user_table[0]->role_id;
      $emp_id = $user_table[0]->employee_id;     
      $roles= Role::where('id','=',$role_id)->get();
      $roles_type_id = $roles[0]->role_type_id;
      $role_type_name = RoleType::where('id','=',$roles_type_id)->get();
      $response_data['status'] = '';
      $response_data['msg'] = '';
      $response_data['target'] = '';
      $response_data['Acheived'] = '';
      $response_data['cash_in_hand'] = '';
      $response_data['no_of_receipt_generated'] = '';
      $payment_type_name = 0;   
	  $start_date = "2022-04-26";
	  $end_date = date('Y-m-d');	  
      if($role_type_name[0]->role_type_name == 'Collection_Agent')
      {
        $employee_tables = Employee::where('id','=',$emp_id)->get();
        $total= 0;
        $adv_counts =0;        
        $total_advance =0;
        foreach($employee_tables as $employee_table)
        {  
          $response_data['target'] = strval(round($employee_table->target));
          $fromDate2 = Carbon::now()->startOfMonth()->toDateString();
          $tillDate2 = Carbon::now()->endOfMonth()->toDateString();
          $receipt_tables = Receipt::selectRaw('*,sum(received_amount) + sum(penalty) - sum(bonus) as total_received')->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('employee_id','=',$emp_id)->where('status','=',1)->get(); 
          $acheive = isset($receipt_tables[0]->total_received) ? ($receipt_tables[0]->total_received) : "0";  
          // Advance with receipts             
          $receipt_tabless = Receipt::whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('employee_id','=',$emp_id)->where('status','=',1)->get();                
          foreach($receipt_tabless as $rcpt)
          {        
            if($rcpt->advance_available ==1)
            { 
              $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where('recpt_excess_no','=',$rcpt->receipt_no)->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('employee_id','=',$emp_id)->where('status','=',1)->get();
              $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
              $total = $total + $total_advance;
            }
          }
          // Advance without recpt_excess_no
          $adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as advances')->where('recpt_excess_no','=','')->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('employee_id','=',$emp_id)->where('status','=',1)->get();
          $advances = isset($adv[0]->advances) ? ($adv[0]->advances) : "0"; 
          $sum_achieve = $acheive + $total + $advances;
          $response_data['Acheived'] = strval(round($sum_achieve));

          $receipt_check = Receipt::where('payment_type_id','=',1)->where('status','=',1)->where('employee_id','=',$emp_id)->get();    
          $total_cash_rece =0;
          $adv_totals =0;
          $total_advance_amt =0;
          $rcpt_cash_settle = 0;
          $receipt_table1 = Receipt::selectRaw('*,sum(received_amount) + sum(penalty) as total_received')->where('employee_id','=',$emp_id)->where('payment_type_id','=',1)->where('status','=',1)->where('receipt_mode','=','Mobile')->whereBetween('receipt_date',[$start_date,$end_date])->get();           
            $total_cash_rece = isset($receipt_table1[0]->total_received) ? ($receipt_table1[0]->total_received) : 0; 
           
              
            // Advance without recpt_excess_no
            $adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as advances')->where('payment_type_id','=',1)->where('status','=',1)->where('employee_id','=',$emp_id)->where('receipt_mode','=','Mobile')->whereBetween('receipt_date',[$start_date,$end_date])->get();
            $total_cash_adv = isset($adv[0]->advances) ? ($adv[0]->advances) : 0;

            $amount = $total_cash_rece + $total_cash_adv;

              $settlement_table = CashSettlement::selectRaw('*,sum(received_amount) as total_settled_amount')->where('employee_id','=',$emp_id)->where('settlement_mode','=','Mobile')->whereBetween('settlement_date',[$start_date,$end_date])->get();
              $adv_received_settlement = isset($settlement_table[0]->total_settled_amount) ? ($settlement_table[0]->total_settled_amount) : "0";

              $adv_cash_settle = $amount - $adv_received_settlement;
            
          $response_data['cash_in_hand'] = strval(round($adv_cash_settle));
          
                        
          $today_date = date('Y-m-d');
          // Receipt Count
          $receipt_count = Receipt::where('receipt_date','=',$today_date)->where('employee_id','=',$emp_id)->where('status','=',1)->get();
          $no_of_receipts = isset($receipt_count) ? ($receipt_count->count()) : 0;
          
          // Advance Count W/o Receipt
          $advance_count = AdvanceReceipt::where('recpt_excess_no','=','')->where('receipt_date','=',$today_date)->where('employee_id','=',$emp_id)->where('status','=',1)->get();  
          $no_of_advance = isset($advance_count) ? ($advance_count->count()) : 0; 
          
          $total_counts = $no_of_receipts + $no_of_advance;
          $response_data['no_of_receipt_generated'] = strval($total_counts);

          $response_data['status'] = 'Success';
          $response_data['msg'] = '';
          return response()->json($response_data,200);
        }
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = '';
        return response()->json($response_data, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }  

  public function get_business_agent_dashboard(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'id'=>$request->user_id);
      $user_table = User::where($cond)->get();
     
      $roles= Role::where('id','=',$user_table[0]->role_id)->get();
      $role_type_name = RoleType::where('id','=',$roles[0]->role_type_id)->get();      
      $response_data['status'] = 'Success';
      $response_data['msg'] = '';           
      $response_data['target'] = '';
      $response_data['Acheived'] = '0';
      $response_data['lead_generated'] = '';
      $response_data['lead_enrolled'] = '';
      $response_data['total_vacanct'] = '';
      $total_recept_amt =0; 
      $total_install = 0; 
      // print_r($role_type_name[0]->role_type_name);exit;
      if($role_type_name[0]->role_type_name == 'Business_Agent')
      {   
        $group_table =Group::selectRaw('*,sum(group_pending_ticket) as total_pending_ticket')->where('tenant_id',$request->tenant_id)->where('status','=',1)->groupBy('tenant_id')->get();
        if(count($group_table)>0)
        { 
          $response_data['total_vacanct'] = isset($group_table[0]->total_pending_ticket) ? (strval($group_table[0]->total_pending_ticket)) : "";
        }
        else
        {
          $response_data['total_vacanct'] = "0";
        }
        $employee_table = Employee::where('id','=',$user_table[0]->employee_id)->get(); 
        $response_data['target'] = isset($employee_table[0]->target) ? (strval($employee_table[0]->target)) : "";

        $lead_management_tables = LeadManagement::where('created_by','=',$user_table[0]->id)->where('is_customer','=',1)->get();
        $i = 0;
        foreach($lead_management_tables as $lead)
        {
          $lead_cust_id = $lead->id;          
          $customer_tables = Customer::where('lead_management_id','=',$lead_cust_id)->get(); 
          foreach($customer_tables as $cust)
          {
            $cust_id = $cust->id; 
            $chit_details_tables = ChitDetail::where('customer_id','=',$cust_id)->where('introducer_id','=',$user_table[0]->employee_id)->get();            
            foreach($chit_details_tables as $chit)
            { 
              $chit_group_id = $chit->group_id;
              $enrol_id = $chit->id;
              $cust_id = $chit->customer_id;
              $comit_total_install = 0;
              $bid_total_install = 0;
              if($chit->chit_type !=3 && $chit->group_id!=0)
              {  
                $bidding_tables = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install')->SelectRaw('COUNT(installment_no)as counts')->where('group_id','=',$chit_group_id)->where('auction_type','!=','duplicate')->groupBy('group_id')->get();   
                if(count($bidding_tables)>0) {
                  if ($bidding_tables[0]->counts>=2) {   
                    $bid_total_install = isset($bidding_tables[0]->total_install) ? ($bidding_tables[0]->total_install) : "0";
                  }
                  else {
                    $bid_total_install = "0";
                  }    
                }  
                else {
                  $bid_total_install = "0";
                }   
              }
              if($chit->chit_type ==3 && $chit->group_id ==0) {      
                $commitment_auctions = CommitmentAuction::selectRaw('*,sum(due_amount) as total_install')->SelectRaw('COUNT(installment_no)as counts')->where('enrollment_id','=',$enrol_id)->groupBy('enrollment_id')->get();
                if(count($commitment_auctions)>0)
                {
                  if ($commitment_auctions[0]->counts>=2) {   
                    $comit_total_install = isset($commitment_auctions[0]->total_install) ? ($commitment_auctions[0]->total_install) : "0";
                  }
                  else {
                    $comit_total_install = "0";
                  } 
                }
                else {
                  $comit_total_install = "0";
                }
              }
              $total_install = $bid_total_install + $comit_total_install; 
              $receipt_tables = Receipt::selectRaw('*,sum(received_amount)+sum(bonus) as total_received')->where('enrollment_id','=',$enrol_id)->where('status','=',1)->groupBy('enrollment_id')->get();       
              $recept_amt = isset($receipt_tables[0]->total_received) ? ($receipt_tables[0]->total_received) : 0;
              $total_recept_amt = round($recept_amt);
              // Acheived count 
              if($total_recept_amt!=0 && $total_install!=0) 
              {
                if($total_recept_amt >= $total_install)
                { 
                  $i++;
                  $response_data['Acheived'] = strval($i);
                } 
              }  
            }
          }
        }         
         // Lead_generated Count overall
        $lead_count = LeadManagement::where('created_by','=',$user_table[0]->id)->get();
        if(count($lead_count)>0)
        {
          $count1 = $lead_count->count();
          $response_data['lead_generated'] = strval($count1);
        }
        else
        {
          $response_data['lead_generated'] = "0";
        }  
        // lead enrolled in groups over all list
        $chit_count = ChitDetail::where('introducer_id','=',$user_table[0]->employee_id)->get();
        if(count($chit_count)>0)
        {
          $count = $chit_count->count();
          $response_data['lead_enrolled'] = strval($count);
        }
        else
        {
          $response_data['lead_enrolled'] = "0";
        }
        $response_data['status'] = 'Success';
        $response_data['msg'] = '';
        return response()->json($response_data,200); 
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = '';
        return response()->json($response_data, 401);
      }
    }
    catch(\Exception $e)
    {
      $response_data['status'] = 'Error';
      $response_data['msg'] = \Lang::get('api.global_error');
      return response()->json($response_data, 401);
    }
  }

  public function get_cash_in_hand(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond= array('tenant_id'=>$request->tenant_id,'id'=>$request->user_id);
      $user_table = User::where($cond)->get();
      $emp_id = $user_table[0]->employee_id;
	  $start_date = "2022-04-26";
	  $end_date = date('Y-m-d');
     
       
            $receipt_table1 = Receipt::selectRaw('*,sum(received_amount) + sum(penalty) as total_received')->where('employee_id','=',$emp_id)->where('payment_type_id','=',1)->where('status','=',1)->where('receipt_mode','=','Mobile')->whereBetween('receipt_date',[$start_date,$end_date])->get();           
            $total_cash_rece = isset($receipt_table1[0]->total_received) ? ($receipt_table1[0]->total_received) : 0; 
           
              
            // Advance without recpt_excess_no
            $adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as advances')->where('payment_type_id','=',1)->where('status','=',1)->where('employee_id','=',$emp_id)->where('receipt_mode','=','Mobile')->whereBetween('receipt_date',[$start_date,$end_date])->get();
            $total_cash_adv = isset($adv[0]->advances) ? ($adv[0]->advances) : 0;

            $amount = $total_cash_rece + $total_cash_adv;

              $settlement_table = CashSettlement::selectRaw('*,sum(received_amount) as total_settled_amount')->where('employee_id','=',$emp_id)->where('settlement_mode','=','Mobile')->whereBetween('settlement_date',[$start_date,$end_date])->get();
              $adv_received_settlement = isset($settlement_table[0]->total_settled_amount) ? ($settlement_table[0]->total_settled_amount) : "0";

              $adv_cash_settle = $amount - $adv_received_settlement;
            
          $response_data['cash_in_hand'] = strval(round($adv_cash_settle));

      $response_data['status'] = 'Success';
      $response_data['msg'] = 'Successfully';
      return response()->json($response_data, 200);       
    }
    catch(\Exception $e)
    {
      $response_data['status'] = 'Error';
      $response_data['msg'] = \Lang::get('api.global_error');
      $response_data['cash_in_hand'] = '';
      return response()->json($response_data, 401);
    }
  }   

 public function get_admin_dashboard(Request $request)
 {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'id'=>$request->user_id);
      $user_table= User::where($cond)->get();
      if(count($user_table)>0)
      {                
        $role_id = $user_table[0]->role_id;
        $emp_id = $user_table[0]->employee_id;
        $tenant_id = $user_table[0]->tenant_id;

        $roles= Role::where('id','=',$role_id)->get();
        $roles_type_id = $roles[0]->role_type_id;
        $role_type_name = RoleType::where('id','=',$roles_type_id)->get();
        $response_data['status'] = '';
        $response_data['target'] = '';
        $response_data['Acheived'] = '';
        $acheive =0;
        $rcpt_total_advance =0;
        $advances_amt =0;
        if($role_type_name[0]->role_type_name == 'Admin')
        {   // Target Calculation
          $employee_table = Employee::selectRaw('*,sum(target) as total_target')->where('employee_type','=',1)->where('branch_id','=',$request->branch_id)->groupBY('branch_id')->get();
          $response_data['target'] = isset($employee_table[0]->total_target) ? strval($employee_table[0]->total_target) : "0";
          
          // Acheived Calculation
          $fromDate2 = Carbon::now()->startOfMonth()->toDateString();
          $tillDate2 = Carbon::now()->endOfMonth()->toDateString();
          $receipt_table = Receipt::selectRaw('*,sum(received_amount) + sum(penalty) as total_received, sum(bonus) as total_bonus')->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('status','=',1)->where('branch_id','=',$request->branch_id)->groupBy('branch_id')->get();           
          $acheive = isset($receipt_table[0]->total_received) ? ($receipt_table[0]->total_received) : 0 ;
          
          // Advance with receipts             
          $receipt_tabless = Receipt::whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('branch_id','=',$request->branch_id)->where('status','=',1)->get();  
          $total = 0;             
          foreach($receipt_tabless as $rcpt)
          {        
            if($rcpt->advance_available ==1)
            { 
              $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where('recpt_excess_no','=',$rcpt->receipt_no)->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('branch_id','=',$request->branch_id)->where('status','=',1)->get();
              $rcpt_total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : 0;
              $total = $total + $rcpt_total_advance;
            }
          }
          // Advance without recpt_excess_no
          $adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as advances')->where('recpt_excess_no','=','')->whereBetween('receipt_date',[$fromDate2,$tillDate2])->where('branch_id','=',$request->branch_id)->where('status','=',1)->get();
          $advances_amt = isset($adv[0]->advances) ? ($adv[0]->advances) : 0; 

          $sum_achieves = $acheive + $total + $advances_amt;
          $response_data['Acheived'] = strval($sum_achieves);
          // Device counts- Active, inactive, total
          $cond = array('tenant_id'=>$tenant_id);
          $devices = Device::where($cond)->get();
          if(count($devices)>0) 
          {
            $total_device=count($devices); // total device
            $cond1 = array('tenant_id'=>$tenant_id,'verification_status'=>1);
            $devices1 = Device::where($cond1)->get();
            $active_device=count($devices1); // active device
            $cond2 = array('tenant_id'=>$tenant_id,'verification_status'=>0); 
            $devices2 = Device::where($cond2)->get();
            $deactive_device=count($devices2); // deactive device
            $response_data['Total Devices'] = strval($total_device);
            $response_data['Active Devices'] = strval($active_device);
            $response_data['DeActive Devices'] = strval($deactive_device);
          }
          else
          {
            $response_data['status'] = 'Error';
            return response()->json($response_data, 401);
          }   
          // Dayclosing Details- Receipt Entry, Pettycash Income
          //cash 
          $today_date = date('Y-m-d');
          $total_adv_cash =0;
          $total_received_cash =0;
          $conds1 = array('tenant_id'=>$tenant_id,'branch_id'=>$request->branch_id);
          $receipts_table1 = Receipt::selectRaw('*,sum(received_amount)+sum(penalty)as total_received_cash')->where($conds1)->where('payment_type_id','=',1)->where('status','=',1)->where('receipt_date','=',$today_date)->get();
          $total_received_cash = isset($receipts_table1[0]->total_received_cash) ? ($receipts_table1[0]->total_received_cash) : "0";    
          $adv_table1 = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_amt')->where($conds1)->where('payment_type_id','=',1)->where('status','=',1)->where('receipt_date','=',$today_date)->get();  
          $total_adv_cash = isset($adv_table1[0]->total_adv_amt) ? ($adv_table1[0]->total_adv_amt) : "0";  
          $response_data['dayclosing_cash'] = strval($total_received_cash + $total_adv_cash); 

          // cheque
          $total_adv_cheque =0;
          $total_received_cheque =0;
          $conds2 = array('tenant_id'=>$tenant_id,'branch_id'=>$request->branch_id);
          $receipts_table2 = Receipt::selectRaw('*,sum(received_amount)+sum(penalty)as total_received_cheque')->where($conds2)->where('payment_type_id','=',2)->where('status','=',1)->where('receipt_date','=',$today_date)->get();
          $total_received_cheque = isset($receipts_table2[0]->total_received_cheque) ? ($receipts_table2[0]->total_received_cheque) : "0";      
          $adv_table2 = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_amt')->where($conds1)->where('payment_type_id','=',2)->where('status','=',1)->where('receipt_date','=',$today_date)->get();  
          $total_adv_cheque = isset($adv_table2[0]->total_adv_amt) ? ($adv_table2[0]->total_adv_amt) : "0";           
          $response_data['dayclosing_cheque'] = strval($total_received_cheque + $total_adv_cheque);
           
           // DD
          $total_adv_dd =0;
          $total_received_dd =0;
          $conds3 = array('tenant_id'=>$tenant_id,'branch_id'=>$request->branch_id);
          $receipts_table3 = Receipt::selectRaw('*,sum(received_amount)+sum(penalty)as total_received_dd')->where($conds3)->where('payment_type_id','=',3)->where('status','=',1)->where('receipt_date','=',$today_date)->get();
          $total_received_dd = isset($receipts_table3[0]->total_received_dd) ? ($receipts_table3[0]->total_received_dd) : "0"; 
          $adv_table3 = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_amt')->where($conds1)->where('payment_type_id','=',3)->where('status','=',1)->where('receipt_date','=',$today_date)->get();  
          $total_adv_dd = isset($adv_table3[0]->total_adv_amt) ? ($adv_table3[0]->total_adv_amt) : "0";                
          $response_data['dayclosing_dd'] = strval($total_received_dd + $total_adv_dd);
             
          // RTGS/NEFT
          $total_adv_rtgs =0;
          $total_received_rtgs =0;
          $conds4 = array('tenant_id'=>$tenant_id,'branch_id'=>$request->branch_id);
          $receipts_table4 = Receipt::selectRaw('*,sum(received_amount)+sum(penalty)as total_received_rtgs')->where($conds4)->where('payment_type_id','=',4)->where('status','=',1)->where('receipt_date','=',$today_date)->get();
          $total_received_rtgs = isset($receipts_table4[0]->total_received_rtgs) ? ($receipts_table4[0]->total_received_rtgs) : "0";      
          $adv_table4 = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_amt')->where($conds1)->where('payment_type_id','=',4)->where('status','=',1)->where('receipt_date','=',$today_date)->get();  
          $total_adv_rtgs = isset($adv_table4[0]->total_adv_amt) ? ($adv_table4[0]->total_adv_amt) : "0"; 
          $response_data['dayclosing_rtgs'] = strval($total_received_rtgs + $total_adv_rtgs);
            
          // card
          $total_adv_card =0;
          $total_received_card =0;
          $conds5 = array('tenant_id'=>$tenant_id,'branch_id'=>$request->branch_id);
          $receipts_table5 = Receipt::selectRaw('*,sum(received_amount)+sum(penalty)as total_received_card')->where($conds5)->where('payment_type_id','=',5)->where('status','=',1)->where('receipt_date','=',$today_date)->get();
          $total_received_card = isset($receipts_table5[0]->total_received_card) ? ($receipts_table5[0]->total_received_card) : "0";        
          $adv_table5 = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_amt')->where($conds1)->where('payment_type_id','=',5)->where('status','=',1)->where('receipt_date','=',$today_date)->get();  
          $total_adv_card = isset($adv_table5[0]->total_adv_amt) ? ($adv_table5[0]->total_adv_amt) : "0";         
          $response_data['dayclosing_card'] = strval($total_received_card + $total_adv_card);

          $response_data['status'] = 'Success';
          $response_data['msg'] = '';
          return response()->json($response_data,200);
        }
        else
        {
          $response_data['status'] = 'Error';
          $response_data['msg'] = '';
          return response()->json($response_data, 401);
        }
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = '';
        return response()->json($response_data, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');  
      return response()->json($response, 401);
    }
 }

 public function generated_receipt(Request $request)
 {
    try
    {   
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = [];
      if(isset($request->tenant_id))
      {
        $cond['tenant_id'] = $request->tenant_id;
      }
	 
      if(isset($request->user_id))
      {
        $login_tables = User::where($cond)->where('id','=',$request->user_id)->groupBy('employee_id')->get();
      }
      $response_data =[];
      if(count($login_tables)>0)
      { 
        foreach($login_tables as $login)
        {               
          $emp_id = $login->employee_id;
          $cond1 = array('employee_id'=>$emp_id,'receipt_date'=>$request->receipt_date);
          $receipts_tales = Receipt::selectRaw('*,sum(received_amount) + sum(penalty)as total_received')->where($cond1)->groupBY('receipt_no')->get();          
          foreach($receipts_tales as $receipt)
          {
            $receipt_det = [];
            $receipt_det['user_id'] = isset($login->id) ? ($login->id) : "0";
            $receipt_det['employee_name'] = isset($login->employee_det) ? ($login->employee_det->first_name.' '.$login->employee_det->last_name) : "";
            $receipt_det['customer_id'] = strval($receipt->customer_id);
            $receipt_det['customer_name'] = '';
            $customer_tables = Customer::where('id','=',$receipt->customer_id)->get();
            $receipt_det['customer_name'] = isset($customer_tables[0]->name) ? ($customer_tables[0]->name) : '';
            $receipt_det['receipt_no'] = isset($receipt->receipt_no) ? ($receipt->receipt_no) : "";
            $receipt_det['receipt_date'] = isset($receipt->receipt_date) ? ($receipt->receipt_date) : "00-00-0000";

            $total_advance = 0; 
            if($receipt->advance_available ==1)
            { 
              $advance = AdvanceReceipt::where('recpt_excess_no','=',$receipt->receipt_no)->get();
              $total_advance = isset($advance[0]->receipt_amount) ? ($advance[0]->receipt_amount) : 0;
            }
            $total_receipt = isset($receipt->total_received) ? ($receipt->total_received) : 0;
            $received_amount =  $total_advance + $total_receipt;
            $receipt_det['received_amount'] = strval(round($received_amount));
            $receipt_det['payment_mode'] = isset($receipt->payment_type_det) ? ($receipt->payment_type_det->payment_name) : "";
            $receipt_det['receipt_type'] = "Due";
            array_push($response_data,$receipt_det);
          }
          $advance_receipts = AdvanceReceipt::where($cond1)->get(); 
          foreach($advance_receipts as $adv)
          { 
            if($adv->recpt_excess_no == "")
            { 
              $adv_det = [];
              $adv_det['user_id'] = isset($login->id) ? ($login->id) : "0";
              $adv_det['employee_name'] = isset($login->employee_det) ? ($login->employee_det->first_name.' '.$login->employee_det->last_name) : "";
              $adv_det['customer_id'] = strval($adv->customer_id);
              $adv_det['customer_name'] = '';
              $customer_tables = Customer::where('id','=',$adv->customer_id)->get();
              $adv_det['customer_name'] = isset($customer_tables[0]->name) ? ($customer_tables[0]->name) : '';
              $adv_det['receipt_no'] = isset($adv->receipt_no) ? ($adv->receipt_no) : "";
              $adv_det['receipt_date'] = isset($adv->receipt_date) ? ($adv->receipt_date) : "00-00-0000";
              $received_amount = isset($adv->receipt_amount) ? ($adv->receipt_amount) : 0;
              $adv_det['received_amount'] = strval(round($received_amount));
              $adv_det['payment_mode'] = isset($adv->payment_type_det) ? ($adv->payment_type_det->payment_name) : "";
              $adv_det['receipt_type'] = "Advance";
              array_push($response_data,$adv_det);
            }
          }  
		  
		  if($request->tenant_id == 47){
			  $cond2 = array('employee_id'=>$emp_id,'received_date'=>$request->receipt_date);
		  $pigmy = PigmyCollection::where($cond2)->get(); 
          foreach($pigmy as $pig)
          { 
            
              $pigs = [];
              $pigs['user_id'] = isset($login->id) ? ($login->id) : "0";
              $pigs['employee_name'] = isset($login->employee_det) ? ($login->employee_det->first_name.' '.$login->employee_det->last_name) : "";
              $pigs['customer_id'] = strval($pig->customer_id);
              $pigs['customer_name'] = '';
              $customer_tables = Customer::where('id','=',$pig->customer_id)->get();
              $pigs['customer_name'] = isset($customer_tables[0]->name) ? ($customer_tables[0]->name) : '';
              $pigs['receipt_no'] = isset($pig->receipt_no) ? ($pig->receipt_no) : "";
              $pigs['receipt_date'] = isset($pig->received_date) ? ($pig->received_date) : "00-00-0000";
              $received_amount = isset($pig->amount) ? ($pig->amount) : 0;
              $pigs['received_amount'] = strval(round($received_amount));
              $pigs['payment_mode'] = isset($pig->payment_type_det) ? ($pig->payment_type_det->payment_name) : "";
              $pigs['receipt_type'] = "Pigmy";
              array_push($response_data,$pigs);
            
          }  
		  
		   $cond2 = array('employee_id'=>$emp_id,'received_date'=>$request->receipt_date);
		  $pigmy = OtherCharge::where($cond2)->get(); 
          foreach($pigmy as $pig)
          { 
            
              $pigs = [];
              $pigs['user_id'] = isset($login->id) ? ($login->id) : "0";
              $pigs['employee_name'] = isset($login->employee_det) ? ($login->employee_det->first_name.' '.$login->employee_det->last_name) : "";
              $pigs['customer_id'] = strval($pig->customer_id);
              $pigs['customer_name'] = '';
              $customer_tables = Customer::where('id','=',$pig->customer_id)->get();
              $pigs['customer_name'] = isset($customer_tables[0]->name) ? ($customer_tables[0]->name) : '';
              $pigs['receipt_no'] = isset($pig->receipt_no) ? ($pig->receipt_no) : "";
              $pigs['receipt_date'] = isset($pig->received_date) ? ($pig->received_date) : "00-00-0000";
              $received_amount = isset($pig->amount) ? ($pig->amount) : 0;
              $pigs['received_amount'] = strval(round($received_amount));
              $pigs['payment_mode'] = isset($pig->payment_type_det) ? ($pig->payment_type_det->payment_name) : "";
              $pigs['receipt_type'] = "Pigmy";
              array_push($response_data,$pigs);
            
          }  
		  }
          if($advance_receipts)
          {
            return response()->json($response_data,200);
          }
          else
          {
            $response_data['status'] = 'Error';
            $response_data['msg'] = \Lang::get('api.no_data_found');
            return response()->json($response_data, 200);
          }    
        }
        return response()->json($response_data,200);
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response_data, 200);
      }
    }  
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }      
 }

  public function receipt_details(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'receipt_no'=>$request->receipt_no);
      if($request->receipt_type== "Due" && $request->receipt_type!="")
      {
        $receipts = Receipt::selectRaw('*,sum(received_amount) as total_received, sum(penalty) as total_penalty, sum(bonus) as total_bonus, group_concat(installment_no) as inst_no')->where($cond)->groupBy('receipt_no')->get();
      }
      else
      {
         $receipts = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_advance')->where($cond)->groupBy('receipt_no')->get();
      }
      if(count($receipts)>0)
      {   
        $group_id = isset($receipts[0]->group_id) ? ($receipts[0]->group_id) : ""; 
        $response_data['receipt_no'] = strval($receipts[0]->receipt_no);
        $response_data['receipt_date'] = strval($receipts[0]->receipt_date);
        $response_data['receipt_time'] = strval($receipts[0]->receipt_time);
        $response_data['employee_id'] = strval($receipts[0]->employee_id);
        $response_data['employee_name'] = strval($receipts[0]->employee_det->first_name);
        $group_name = isset($receipts[0]->group_det->group_name) ? ($receipts[0]->group_det->group_name) : "";
        $response_data['groupname'] = strval($group_name);
        $ticket_no = isset($receipts[0]->ticket_no) ? ($receipts[0]->ticket_no) : "0";
        $response_data['ticketno'] = strval($ticket_no);
        $install_no = isset($receipts[0]->inst_no) ? ($receipts[0]->inst_no) : "0"; 
        $response_data['installmentno'] = strval($install_no);
        $response_data['customer_name'] = strval($receipts[0]->customer_det->name);
        $response_data['customer_code'] = strval($receipts[0]->customer_det->customer_code);
        $response_data['mobile_no'] = strval($receipts[0]->customer_det->mobile_no);
        $response_data['phone_no'] = strval($receipts[0]->customer_det->phone_no);
        if($request->receipt_type== "Due" && $request->receipt_type!="")
        {
          $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : 0;
          $total_penalty = isset($receipts[0]->total_penalty) ? ($receipts[0]->total_penalty) : 0;
          $received_amount = $total_received + $total_penalty;
          $total_advance = 0; 
          if($receipts[0]->advance_available ==1)
          {
            $advance = AdvanceReceipt::where('recpt_excess_no','=',$receipts[0]->receipt_no)->get();
            $total_advance = isset($advance[0]->receipt_amount) ? ($advance[0]->receipt_amount) : 0;
          } 
          $total_advance_amount = $total_advance + $received_amount;
          $response_data['receipt_type'] = "Due"; 
        }
        else{
          $total_advance_amount = isset($receipts[0]->total_advance) ? ($receipts[0]->total_advance) : "";
          $response_data['receipt_type'] = "Advance";
        }

        $response_data['received_amount'] = strval(round($total_advance_amount));
        $response_data['penalty_amount'] = strval(round($receipts[0]->total_penalty));
        $response_data['bonus_amount'] = strval(round($receipts[0]->total_bonus));
        $response_data['payemnt_type'] = strval($receipts[0]->payment_type_det->payment_name);
        $response_data['debit_to'] = strval($receipts[0]->debit_to);
        $response_data['cheque_no'] = strval($receipts[0]->cheque_no);
        $response_data['cheque_date'] = strval($receipts[0]->cheque_date);
        $response_data['transaction_no'] = strval($receipts[0]->transaction_no);
        $response_data['transaction_date'] = strval($receipts[0]->transaction_date);
        $response_data['bank_name'] = isset($receipts[0]->customer_bank_det) ? ($receipts[0]->customer_bank_det->customer_bank_name) : "";
        $response_data['branch_name'] = strval($receipts[0]->branch_name);
        $response_data['advance_amount'] = "0";
        $arr=array(1=>'Yes',0=>'No');
        $response_data['is_printed'] = $arr[$receipts[0]->printed];
        return response()->json($response_data, 200);
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response_data, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function printed_status_update(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'id'=>$request->user_id);
      $login_table = User::where($cond)->get();
      if(count($login_table)>0)
      { 
        $employee_id = $login_table[0]->employee_id;
        $receipts = Receipt::where('employee_id','=',$employee_id)->where('receipt_no','=',$request->receipt_no)->get();
        $receipts[0]->printed = 1;
        if($receipts[0]->save())
        {
          $response_data['status'] = 'Success';
          $response_data['msg'] = 'Successfully Updated';
          return response()->json($response_data, 200);
        }                
      }
      else
      {
        $response_data['status'] = 'Error';
        $response_data['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response_data, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function list_customer_bank_names(Request $request)
  {
    try 
    { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $customerbankdetails = BankDetail::orderBy('id','asc')->get();
      $i=1;
      $response_data = [];
      foreach($customerbankdetails as $customerbankdetail) {
        $tenent_det = []; 
        $tenent_det['customer_bank_id'] = $customerbankdetail->id;
        $tenent_det['customer_bank_name'] = $customerbankdetail->bank_name;        
        $tenent_det['bank_branch_name'] = $customerbankdetail->bank_branch_name;        
        array_push($response_data,$tenent_det);
        $i++;
      }
      if($customerbankdetails) {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) 
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function customer_signup_otp(Request $request)
  {
    try
    {
     // $request->db = 'my_chits';
	  
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
     
      $customer_table = Customer::Where('mobile_no','=',$request->user_id)->get();
      
      if(count($customer_table)>0)
      { 
        if($customer_table[0]->user_enabled == 0)
        {
          $otp=mt_rand(1000,9999);  
		  
          $employee_name = isset($customer_table[0]->name) ? ($customer_table[0]->name) : '';
          $mobile_no = isset($customer_table[0]->mobile_no) ? ($customer_table[0]->mobile_no) : '';
          $tenant_id = $customer_table[0]->tenant_id;
          $data['otp'] = $otp;
          
		  
		  $msg = "Dear Customer, Best wishes to joining us, This is your login OTP $otp, Don't share with others - SPN Chits Pvt Ltd";


	$msg1=$msg;
		$receiver = $mobile_no;
		$msg_split = str_split($msg,456);
		
		  
				
            	$ch = curl_init("https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=$msg");
            
            	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				  $result = curl_exec($ch);
				curl_close($ch);
		  
          	
		  
		       return json_encode(array('status'=>'Success','msg'=>'','customer_primary_id'=>strval($customer_table[0]->id),'otp'=>strval($otp),'customer_name'=>$customer_table[0]->name));
          
          
        }
        else
        {
          $response['status'] = 'Signin';
          $response['msg'] = "You Already Logged In Enter Password";
          return response()->json($response, 200);
        }
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['statuse'] = $e;
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function customer_update_password(Request $request)
  {
    try
    {
		//$request->db="my_chits";
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('id'=>$request->cust_primary_id);
      $customer_tables = Customer::where($cond)->get();
      $customer_tables[0]->password = Hash::make($request->password); 
      $customer_tables[0]->user_enabled = 1;    
      if($customer_tables[0]->save())
      {
        $response['status'] = 'Success';
        $response['msg'] = '';
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function customer_signin(Request $request)
  {
    try
    {
		
		//$request->db = 'my_chits';
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
	  
	  //if customer is original customer
      $cond = array('customer_code'=>$request->user_id);
      $customer = Customer::where($cond)->orWhere('mobile_no','=',$request->user_id)->get();
	  
	 
      
      if(count($customer)>0)
      {
		
        $cond1 = array('customer_code'=>$customer[0]->customer_code);
        $approved_status = Customer::where('id',1)->where('user_enabled','!=',0)->get();
		$approved_status= 1;
       // 
	   
	   if($approved_status>0)
        {
			
          $is_password_matched = Hash::check($request->password, $customer[0]->password);
		  
		  $defaultImage = url(Storage::url('app/uploads/profile.png'));

$customerImage = !empty($customer[0]->photo)
    && Storage::exists('uploads/customer_photos/'.$customer[0]->photo)
    ? url(Storage::url('uploads/customer_photos/'.$customer[0]->photo))
    : $defaultImage;
         
          if($is_password_matched)
          {  
      
	      
            return response()->json([
              'status'=>'Success',
              'msg'=>'',
              'customer_primary_id'=>strval($customer[0]->id),
              'customer_code'=>strval($customer[0]->customer_code),
              'customer_name'=>$customer[0]->name,
              'tenant_id'=>strval($customer[0]->tenant_id),
              'tenant_name'=>strval($customer[0]->tenant_det->chit_fund_name),
              'tenant_address_1'=>strval($customer[0]->branch_det->address_line_1),
              'tenant_address_2'=>strval($customer[0]->branch_det->address_line_2),
              //'state_name'=>strval($customer[0]->branch_det->state_det->state_name),
              //'district_name'=>strval($customer[0]->branch_det->district_det->district_name),
              // 'city_name'=>strval($customer[0]->branch_det->city_det->city_name),
              'pincode'=>strval($customer[0]->branch_det->pincode),
              'mobile_no'=>strval($customer[0]->mobile_no),
              'phone_no'=>strval($customer[0]->branch_det->phone_no),
              'branch_id'=>strval($customer[0]->branch_id),
              'branch_name'=>strval($customer[0]->branch_det->branch_name),
              'profile_photo' => $customerImage,
			  'cus_type'=>1,
			  'app_version'=>'1.0',
            ]);
			 
          }
          else
          {
            $response['status'] = 'Error';
            $response['msg'] = "Incorrect Password";
            return response()->json($response, 401); 
          }
        }
        else
        {
          $response['status'] = 'Signup';
          $response['msg'] = "Signup Now";
          return response()->json($response, 401);
        } 
      } 
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  } 

public function customer_my_chits(Request $request)
{
    try {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $today = date('Y-m-d');
        $response_data = [];
        $tabCounts = ['running' => 0, 'completed' => 0, 'closed' => 0];

        $enrollments = ChitDetail::with(['group_det.scheme_det', 'slab_due_det.scheme_det'])
            ->where('customer_id', $request->customer_id)
            ->where('tenant_id', $request->tenant_id)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($enrollments as $enroll) {
            $item = $this->buildMyChitListItem($enroll, $today);
            $tab = $item['status_tab'] ?? 'closed';
            if (isset($tabCounts[$tab])) {
                $tabCounts[$tab]++;
            }
            $response_data[] = $item;
        }

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => $response_data,
            'tab_counts' => $tabCounts,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('My Chits API Error', ['error' => $e->getMessage()]);

        return response()->json([
            'status' => 'Error',
            'msg' => 'Server Error'
        ], 500);
    }
}

public function customer_chit_details(Request $request)
{
    try {
        if (!$request->enrollment_id || !$request->customer_id || !$request->tenant_id || !$request->db) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing',
            ], 422);
        }

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $enroll = ChitDetail::with(['group_det.scheme_det', 'slab_due_det.scheme_det'])
            ->where('id', $request->enrollment_id)
            ->where('customer_id', $request->customer_id)
            ->where('tenant_id', $request->tenant_id)
            ->first();

        if (!$enroll) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Enrollment not found',
            ], 404);
        }

        $today = date('Y-m-d');
        $summary = $this->buildMyChitListItem($enroll, $today);

        $customer = Customer::where('id', $request->customer_id)->first();
        $memberSince = '';
        if ($customer && !empty($customer->doj) && $customer->doj !== '0000-00-00') {
            $memberSince = date('M Y', strtotime($customer->doj));
        }
        $summary['member_since'] = $memberSince;
        $summary['installments_paid_display'] =
            ($summary['paid_months'] ?? '0') . ' / ' . ($summary['total_months'] ?? '0') . ' installments paid';

        $scheduleData = $this->buildChitPaymentSchedule($enroll, $today);
        $paymentHistory = $this->buildChitPaymentHistory($enroll);

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => [
                'summary' => $summary,
                'schedule_banner' => $scheduleData['banner'],
                'payment_schedule' => $scheduleData['items'],
                'payment_history' => $paymentHistory,
            ],
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Customer Chit Details API Error', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => 'Error',
            'msg' => 'Server Error',
        ], 500);
    }
}

/**
 * Installment schedule for one enrollment (commitment_auctions or bidding_details).
 */
private function buildChitPaymentSchedule($enroll, $today)
{
    $paidAuctionIds = Receipt::where('enrollment_id', $enroll->id)
        ->where('status', 1)
        ->whereNotNull('auction_id')
        ->where('auction_id', '!=', 0)
        ->pluck('auction_id')
        ->map(function ($id) {
            return (int) $id;
        })
        ->toArray();

    $rows = [];
    if ((int) $enroll->chit_type === 3) {
        $auctions = CommitmentAuction::where('enrollment_id', $enroll->id)
            ->orderBy('installment_no', 'asc')
            ->get();
        foreach ($auctions as $auction) {
            $rows[] = [
                'id' => (int) $auction->id,
                'installment_no' => (int) $auction->installment_no,
                'due_date' => $auction->auction_date,
                'amount' => (float) $auction->due_amount,
            ];
        }
    } elseif ($enroll->group_id) {
        $bids = BiddingDetail::where('group_id', $enroll->group_id)
            ->orderBy('installment_no', 'asc')
            ->get();
        foreach ($bids as $bid) {
            $rows[] = [
                'id' => (int) $bid->id,
                'installment_no' => (int) $bid->installment_no,
                'due_date' => $bid->auction_date,
                'amount' => (float) ($bid->current_installment_amount ?? 0),
            ];
        }
    }

    $upcomingAssigned = false;
    $items = [];
    $nextUpcoming = null;

    foreach ($rows as $row) {
        $isPaid = in_array($row['id'], $paidAuctionIds, true);
        $dueDate = $row['due_date'];
        $status = 'scheduled';
        $statusLabel = 'Scheduled';

        if ($isPaid) {
            $status = 'paid';
            $statusLabel = 'Paid';
        } elseif (!$upcomingAssigned) {
            $upcomingAssigned = true;
            $status = 'upcoming';
            $statusLabel = 'Upcoming';
            $nextUpcoming = $row;
        }

        $items[] = [
            'installment_no' => strval($row['installment_no']),
            'installment_label' => 'Installment ' . $row['installment_no'],
            'due_date' => strval($dueDate),
            'due_date_display' => ($dueDate && $dueDate !== '0000-00-00')
                ? date('d M Y', strtotime($dueDate))
                : '',
            'amount' => strval(round($row['amount'])),
            'status' => $status,
            'status_label' => $statusLabel,
            'is_paid' => $isPaid ? '1' : '0',
        ];
    }

    $banner = [
        'message' => 'No upcoming installments.',
        'next_amount' => '0',
        'next_due_date_display' => '',
    ];

    if ($nextUpcoming) {
        $amt = round($nextUpcoming['amount']);
        $dateDisp = ($nextUpcoming['due_date'] && $nextUpcoming['due_date'] !== '0000-00-00')
            ? date('d M Y', strtotime($nextUpcoming['due_date']))
            : '';
        $banner = [
            'message' => "You are all set! Your next installment of ₹ {$amt} is due on {$dateDisp}.",
            'next_amount' => strval($amt),
            'next_due_date_display' => $dateDisp,
        ];
    }

    return ['banner' => $banner, 'items' => $items];
}

/**
 * Prize / release payments from payments table for enrollment.
 */
private function buildChitPaymentHistory($enroll)
{
    $payments = Payment::with('payment_type_det')
        ->where('enrollment_id', $enroll->id)
        ->orderBy('payment_date', 'desc')
        ->orderBy('id', 'desc')
        ->get();

    $history = [];
    foreach ($payments as $payment) {
        $paymentDate = $payment->payment_date;
        $history[] = [
            'payment_id' => strval($payment->id),
            'receipt_no' => strval($payment->receipt_no ?? ('PYMT-' . $payment->id)),
            'payment_date' => strval($paymentDate),
            'payment_date_display' => ($paymentDate && $paymentDate !== '0000-00-00')
                ? date('d M Y', strtotime($paymentDate))
                : '',
            'amount' => strval(round((float) $payment->total_amount)),
            'payment_type' => $payment->payment_type_det->payment_name ?? '',
            'payment_for' => strval($payment->payment_for ?? ''),
            'status' => strval($payment->status ?? ''),
        ];
    }

    return $history;
}

public function customer_download_receipts(Request $request)
{
    try {
        if (!$request->customer_id || !$request->tenant_id || !$request->db) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing',
            ], 422);
        }

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $customerId = (int) $request->customer_id;
        $tenantId = (int) $request->tenant_id;

        $chitReceipts = [];
        $receiptQuery = Receipt::with([
            'enrollment_det.slab_due_det.scheme_det',
            'enrollment_det.group_det.scheme_det',
            'payment_type_det',
            'employee_det',
            'group_det',
        ])
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            /* status != 0 alone excludes NULL in MySQL — same rule as customer_account_copy */
            ->where(function ($q) {
                $q->where('status', '!=', 0)
                    ->orWhereNull('status');
            })
            /* 7 = advance adjustment, 8 = before-enrol (separate tab); allow NULL type */
            ->where(function ($q) {
                $q->whereNull('payment_type_id')
                    ->orWhereNotIn('payment_type_id', [7, 8]);
            });

        if (!empty($request->enrollment_id)) {
            $receiptQuery->where('enrollment_id', (int) $request->enrollment_id);
        }

        $receipts = $receiptQuery
            ->orderBy('receipt_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($receipts as $receipt) {
            try {
                $chitReceipts[] = $this->formatChitReceiptRow($receipt);
            } catch (\Exception $rowEx) {
                \Log::warning('formatChitReceiptRow skipped', [
                    'receipt_id' => $receipt->id,
                    'error' => $rowEx->getMessage(),
                ]);
            }
        }

        $beforeEnrollment = [];
        $beforeRows = BeforeEnrolReceipt::with(['payment_type_det', 'employee_det', 'customer_det'])
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('received_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($beforeRows as $row) {
            $beforeEnrollment[] = $this->formatBeforeEnrolReceiptRow($row);
        }

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => [
                'chit_receipts' => $chitReceipts,
                'before_enrollment_receipts' => $beforeEnrollment,
            ],
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Customer Download Receipts API Error', [
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => 'Error',
            'msg' => 'Server Error',
        ], 500);
    }
}

private function formatChitReceiptRow($receipt)
{
    $enroll = $receipt->enrollment_det;
    $chitValue = 0;
    $slabName = '';
    $groupName = '';
    $ticketNo = '';

    if ($enroll) {
        if ((int) $enroll->chit_type === 3 && $enroll->slab_due_det) {
            $slabName = $enroll->slab_due_det->slab_name ?? '';
            if ($enroll->slab_due_det->scheme_det) {
                $chitValue = (float) $enroll->slab_due_det->scheme_det->chit_value;
            }
        } elseif ($enroll->group_det && $enroll->group_det->scheme_det) {
            $groupName = $enroll->group_det->group_name ?? '';
            $ticketNo = strval($enroll->ticket_no ?? '');
            $chitValue = (float) $enroll->group_det->scheme_det->chit_value * (float) ($enroll->ticket_type ?: 1);
        }
    }

    $amount = (float) $receipt->received_amount + (float) $receipt->penalty;
    $receiptDate = $receipt->receipt_date;
    $receiptTime = $receipt->receipt_time ?? '';

    return [
        'receipt_id' => strval($receipt->id),
        'receipt_no' => strval($receipt->receipt_no),
        'receipt_type' => 'chit_receipt',
        'enrollment_id' => strval($receipt->enrollment_id ?? ''),
        'chit_value' => strval(round($chitValue)),
        'slab_name' => $slabName,
        'group_name' => $groupName,
        'ticket_no' => $ticketNo,
        'installment_no' => strval($receipt->installment_no ?? ''),
        'receipt_date' => strval($receiptDate),
        'receipt_date_display' => ($receiptDate && $receiptDate !== '0000-00-00')
            ? date('d M Y', strtotime($receiptDate))
            : '',
        'receipt_time' => strval($receiptTime),
        'receipt_datetime_display' => $this->formatReceiptDateTime($receiptDate, $receiptTime),
        'amount' => strval(round($amount)),
        'received_amount' => strval(round($receipt->received_amount)),
        'penalty' => strval(round($receipt->penalty ?? 0)),
        'bonus' => strval(round($receipt->bonus ?? 0)),
        'payment_mode' => ($receipt->payment_type_det && $receipt->payment_type_det->payment_name)
            ? $receipt->payment_type_det->payment_name
            : '',
        'collected_by' => $this->resolveEmployeeDisplayName($receipt->employee_id),
        'created_by' => $this->resolveCreatedByName($receipt->created_by),
        'cheque_no' => strval($receipt->cheque_no ?? ''),
        'transaction_no' => strval($receipt->transaction_no ?? ''),
        'status' => strval($receipt->status ?? ''),
    ];
}

private function formatBeforeEnrolReceiptRow($receipt)
{
    $receivedDate = $receipt->received_date;
    $amount = (float) ($receipt->amount ?? 0);

    return [
        'receipt_id' => strval($receipt->id),
        'receipt_no' => strval($receipt->receipt_no ?? ''),
        'receipt_type' => 'before_enrollment',
        'receipt_date' => strval($receivedDate),
        'receipt_date_display' => ($receivedDate && $receivedDate !== '0000-00-00')
            ? date('d M Y', strtotime($receivedDate))
            : '',
        'receipt_time' => '',
        'receipt_datetime_display' => ($receivedDate && $receivedDate !== '0000-00-00')
            ? date('d M Y', strtotime($receivedDate))
            : '',
        'amount' => strval(round($amount)),
        'payment_mode' => ($receipt->payment_type_det && $receipt->payment_type_det->payment_name)
            ? $receipt->payment_type_det->payment_name
            : '',
        'collected_by' => $this->resolveEmployeeDisplayName($receipt->employee_id),
        'created_by' => $this->resolveCreatedByName($receipt->created_by),
        'cheque_no' => strval($receipt->cheque_no ?? ''),
        'transaction_no' => strval($receipt->transaction_no ?? ''),
        'remarks' => strval($receipt->remarks ?? ''),
        'status' => strval($receipt->status ?? ''),
    ];
}

/**
 * Branch address as 3 lines (address, city/district/state, pincode) for mobile.
 */
private function formatBranchClientAddress($branch)
{
    if (!$branch) {
        return '';
    }

    $line1Parts = array_values(array_filter([
        trim((string) ($branch->address_line_1 ?? '')),
        trim((string) ($branch->address_line_2 ?? '')),
        trim((string) ($branch->landmark ?? '')),
    ], function ($part) {
        return $part !== '';
    }));

    $cityName = '';
    if (!empty($branch->city_det) && !empty($branch->city_det->city_name)) {
        $cityName = trim($branch->city_det->city_name);
    }

    $districtName = '';
    if (!empty($branch->district_det) && !empty($branch->district_det->district_name)) {
        $districtName = trim($branch->district_det->district_name);
    }

    $stateName = '';
    if (!empty($branch->state_det) && !empty($branch->state_det->state_name)) {
        $stateName = trim($branch->state_det->state_name);
    }

    $line2Parts = array_values(array_filter([$cityName, $districtName, $stateName], function ($part) {
        return $part !== '';
    }));

    $pincode = trim((string) ($branch->pincode ?? ''));
    if ($pincode === '' && !empty($branch->city_det) && !empty($branch->city_det->pincode)) {
        $pincode = trim((string) $branch->city_det->pincode);
    }

    $lines = [];
    if (!empty($line1Parts)) {
        $lines[] = implode(', ', $line1Parts);
    }
    if (!empty($line2Parts)) {
        $lines[] = implode(', ', $line2Parts);
    }
    if ($pincode !== '') {
        $lines[] = $pincode;
    }

    return implode("\n", $lines);
}

private function resolveBranchClientAddress($branchId)
{
    if (!$branchId) {
        return '';
    }

    $branch = Branch::with(['city_det', 'district_det', 'state_det'])->find($branchId);
    return $this->formatBranchClientAddress($branch);
}

private function formatReceiptDateTime($date, $time)
{
    if (!$date || $date === '0000-00-00') {
        return '';
    }
    $display = date('d M Y', strtotime($date));
    if ($time && $time !== '00:00:00') {
        $display .= ' ' . date('h:i A', strtotime($time));
    }
    return $display;
}

private function resolveEmployeeDisplayName($employeeId)
{
    if (!$employeeId) {
        return '';
    }
    $emp = Employee::find($employeeId);
    if (!$emp) {
        return '';
    }
    return trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
}

private function resolveCreatedByName($createdBy)
{
    if (!$createdBy) {
        return '';
    }
    $emp = Employee::find($createdBy);
    if ($emp) {
        return trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
    }
    $user = User::find($createdBy);
    if ($user && !empty($user->employee_id)) {
        return $this->resolveEmployeeDisplayName($user->employee_id);
    }
    return strval($createdBy);
}


  public function customer_account_copy(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $response_data = [];
      $receipt_accounts = Receipt::selectRaw('*,sum(received_amount) as total_received')->where(array('customer_id'=>$request->customer_id,'tenant_id'=>$request->tenant_id))->where('status','!=',0)->where('payment_type_id','!=','7')->where('payment_type_id','!=','8')->groupBy('receipt_no')->orderBy('receipt_date','desc')->get();  

        foreach($receipt_accounts as $ra)
        {
          $account_det = [];
          $account_det['receipt_no'] = $ra->receipt_no;
          $account_det['receipt_date'] = $ra->receipt_date;
          $account_det['receipt_time'] = $ra->receipt_date;
          if($ra->group_id != 0)
          { 
            $account_det['group_name'] = isset($ra->group_det) ? ($ra->group_det->group_name) : "";
            $account_det['ticket_no'] = isset($ra->enrollment_det) ? (strval($ra->enrollment_det->ticket_no)) : "";
          } 
          else {
            $account_det['group_name'] = "Commitment";
            $account_det['ticket_no'] = "-";
          }
          $total_receiv_amt = isset($ra->total_received) ? ($ra->total_received) : 0;
          // Advance with receipts            
          $receiptss = Receipt::where('customer_id',$ra->customer_id)->where('status',1)->get();
          $total =0;          
          foreach($receiptss as $rcp)      
          {
            if($rcp->advance_available ==1)
            { 
              $advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where('recpt_excess_no','=',$ra->receipt_no)->where('status','=',1)->get();
              $total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : 0;
              $total = $total + $total_advance;
            }
          }
          $sum_received = $total + $total_receiv_amt;
          $account_det['total_received_amount'] = strval(round($sum_received));
          
          $account_det['payment_type'] = $ra->payment_type_det->payment_name;
          $account_det['receipt_type'] = "Due"; 
          $account_det['status'] ="";
          if($ra->status == 1 && $ra->payment_type_id == 2)
          {
            $account_det['status'] ="Cleared";
          }
          if($ra->status == 2 && $ra->payment_type_id == 2)
          {
            $account_det['status'] ="Not-Cleared";
          }
          // check advance table if any advance:- receipt_type ="Advance"..
          array_push($response_data,$account_det);
        }

        // Advance without recpt_excess_no
        // DB::enableQueryLog();
        $adv = AdvanceReceipt::selectRaw('*')->where('recpt_excess_no','=','')->where('customer_id','=',$request->customer_id)->where('tenant_id','=',$request->tenant_id)->where('status','=',1)->get();
      
        // print_r(count($adv));exit;
        // print_r($adv);exit;
        // print_r(DB::getQueryLog());exit;
        $i=1;
        $total_receiv_amt = 0;
        foreach($adv as $ad)
        { 
          $adv_det = [];
          $adv_det['receipt_no'] = $ad->receipt_no;
          $adv_det['receipt_date'] = $ad->receipt_date;
          $adv_det['receipt_time'] = $ad->receipt_time;
          if($ad->enrollment_det->group_id != 0)
          { 
            $adv_det['group_name'] = isset($ad->enrollment_det) ? ($ad->enrollment_det->group_det->group_name) : "";
            $adv_det['ticket_no'] = isset($ad->enrollment_det) ? (strval($ad->enrollment_det->ticket_no)) : "";
          } 
          else {
            $adv_det['group_name'] = "Commitment";
            $adv_det['ticket_no'] = "-";
          }
          // $total_receiv_amt = isset($ad->advances) ? ($ad->advances) : 0;
          // if($i==count($adv)){
            $total_receiv_amt=$ad->receipt_amount;
          // }        
          // print_r($ad->receipt_amount.'--');  
          $adv_det['total_received_amount'] = strval(round($total_receiv_amt));          
          $adv_det['payment_type'] = $ad->payment_type_det->payment_name;
          $adv_det['receipt_type'] = "Advance"; 
          $adv_det['status'] ="";
          if($ad->status == 1 && $ad->payment_type_id == 2)
          {
            $adv_det['status'] ="Cleared";
          }
          if($ad->status == 2 && $ad->payment_type_id == 2)
          {
            $adv_det['status'] ="Not-Cleared";
          }
          // check advance table if any advance:- receipt_type ="Advance"..
          array_push($response_data,$adv_det); 
          $i++;          
        }
      if($receipt_accounts)
      {
        $response['status'] = 'Success';
        $response['msg'] = "";
        $response["data"] = $response_data;
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response, 200);
      }
      if($adv)
      {
        $response['status'] = 'Success';
        $response['msg'] = "";
        $response["data"] = $response_data;
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function customer_new_commenced_chits(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      if(isset($request->tenant_id)) 
      {
        $scheme_details = Scheme::where('tenant_id','=',$request->tenant_id)->get();
      } 
      else {
        $scheme_details = Scheme::orderBy('id')->get();
      }
      $response_data = [];
      foreach($scheme_details as $sd)
      {
        $scheme_det = []; 
        $scheme_det['scheme_id'] = strval($sd->id);
        $scheme_det['scheme_name'] = $sd->short_term;
        $scheme_det['chit_value'] = strval(round($sd->chit_value));
        $scheme_det['no_of_months'] = strval($sd->no_of_months);
        $scheme_det['no_of_members'] = strval($sd->members);
        $scheme_det['monthly_due_amount'] = strval(round($sd->monthly_due));
        $groups = Group::selectRaw('*,sum(group_pending_ticket) as total_vacancts')->where('scheme_id','=',$sd->id)->get();
       // $scheme_det['total_vacanct'] = isset($groups[0]->total_vacancts) ? ($groups[0]->total_vacancts) : "0";
	   $scheme_det['total_vacanct'] = 7;
        array_push($response_data,$scheme_det);
      }
      if($scheme_details)
      {
        $response['status'] = 'Success';
        $response['msg'] = "Interest Sent Successfully";
        $response["data"] = $response_data;
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.no_data_found');
        return response()->json($response, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    } 
  }


  public function enrollment_wise_receipt_details(Request $request)
{
    try
    {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $response_data = [];

        $cond = [
            'tenant_id'     => $request->tenant_id,
            'enrollment_id' => $request->enrollment_id
        ];

        /* =========================
           INSTALLMENT AMOUNT (MONTHLY)
        ========================= */
        $enrollment = ChitDetail::with('group_det')
            ->where('id', $request->enrollment_id)
            ->first();

        $monthlyInstallment = BiddingDetail::where('group_id', $enrollment->group_id)
            ->orderBy('installment_no', 'asc')
            ->value('current_installment_amount');

        /* =========================
           NORMAL RECEIPTS
        ========================= */
        $receipts = Receipt::selectRaw('*, SUM(received_amount) as total_received')
            ->where($cond)
            ->where('payment_type_id', '!=', 7)
            ->where('payment_type_id', '!=', 8)
            ->groupBy('receipt_no')
            ->orderBy('receipt_date', 'asc')
            ->get();

        $completedInstallments = 0;

        foreach ($receipts as $rcpt)
        {
            $receipt_details = [];

            /* =========================
               BASIC INFO
            ========================= */
            $receipt_details['receipt_no']   = $rcpt->receipt_no;
            $receipt_details['receipt_date'] = $rcpt->receipt_date;

            $rec_time = explode(' ', $rcpt->created_at);
            $receipt_details['receipt_time'] = $rec_time[1] ?? "00:00";

            $receipt_details['group_name'] =
                $rcpt->chit_type != 3
                ? ($rcpt->group_det->group_name ?? "")
                : "Commitment Chit";

            $receipt_details['ticket_no'] =
                $rcpt->chit_type != 3
                ? strval($rcpt->enrollment_det->ticket_no ?? "")
                : "-";

            /* =========================
               PAID AMOUNT (WITH ADVANCE)
            ========================= */
            $paidAmount = $rcpt->total_received ?? 0;

            $receipt_details['paid_amount'] = strval(round($paidAmount));

            /* =========================
               INSTALLMENT RANGE (FIXED)
            ========================= */
            $installmentsPaid = 0;
            if ($monthlyInstallment > 0) {
                $installmentsPaid = floor($paidAmount / $monthlyInstallment);
            }

            $start = $completedInstallments + 1;
            $end   = $completedInstallments + $installmentsPaid;

            if ($installmentsPaid > 1) {
                $receipt_details['installment_no'] =
                    str_pad($start, 2, '0', STR_PAD_LEFT) . '-' .
                    str_pad($end, 2, '0', STR_PAD_LEFT);
            } else {
                $receipt_details['installment_no'] =
                    str_pad($start, 2, '0', STR_PAD_LEFT);
            }

            $completedInstallments = $end;

            /* =========================
               PENALTY / BONUS
            ========================= */
            $receipt_details['penalty'] = strval($rcpt->penalty ?? 0);
            $receipt_details['bonus']   = strval($rcpt->bonus ?? 0);

            /* =========================
               DIVIDEND (OPTIONAL DISPLAY)
            ========================= */
            $dividend = BiddingDetail::whereBetween(
                'installment_no',
                [$start, $end]
            )
            ->where('group_id', $enrollment->group_id)
            ->sum('divident_amount');

            $receipt_details['dividend'] = strval(round($dividend));

            /* =========================
               PAYMENT MODE & COLLECTOR
            ========================= */
            $receipt_details['payment_type'] =
                $rcpt->payment_type_det->payment_name ?? "";

            $receipt_details['collected_by'] = isset($rcpt->employee_det->first_name) ? ($rcpt->employee_det->first_name) : '-';

            array_push($response_data, $receipt_details);
        }

        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => $response_data
        ], 200);
    }
    catch (\Exception $e)
    {
        \Log::error('Receipt Installment Mapping Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => 'Error',
            'msg'    => \Lang::get('api.global_error')
        ], 500);
    }
}



 public function customer_interest_message(Request $request)
{
    /* =========================
       VALIDATION
    ========================= */
    $validator = Validator::make($request->all(), [
        'tenant_id'   => 'required',
        'branch_id'   => 'required',
        'customer_id' => 'required',
        'scheme_id'   => 'required',
        'db'          => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg'    => $validator->errors()->first()
        ], 200);
    }

    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =========================
           SAVE INTEREST MESSAGE
        ========================= */
        $interest_msg = new InterestMessage();
        $interest_msg->tenant_id       = $request->tenant_id;
        $interest_msg->branch_id       = $request->branch_id;
        $interest_msg->customer_id     = $request->customer_id;
        $interest_msg->scheme_id       = $request->scheme_id;
        $interest_msg->for_notification= $request->for_notification ?? 0;
        $interest_msg->action          = $request->action ?? 0;
        $interest_msg->remarks         = $request->remarks ?? '';
        $interest_msg->created_by      = $request->customer_id;
        $interest_msg->status          = $request->status ?? 1;

        if (!$interest_msg->save()) {
            return response()->json([
                'status' => 'Error',
                'msg'    => \Lang::get('api.global_error')
            ], 500);
        }

        /* =========================
           CUSTOMER DETAILS
        ========================= */
        $cust_name  = '';
        $mobile_no  = '';

        if ($interest_msg->customer_det) {
            $cust_name = $interest_msg->customer_det->name ?? '';
            $mobile_no = $interest_msg->customer_det->mobile_no ?? '';
        }

        $customer_display = trim($cust_name . ' / ' . $mobile_no);

        /* =========================
           SCHEME DETAILS
        ========================= */
        $chit_value = Scheme::where('id', $request->scheme_id)
            ->value('chit_value');

        /* =========================
           SEND MAIL
        ========================= */
        try {
            $admin_email = "abishek@mazenetsolution.com";

            $data = [
                'cust_name'  => $customer_display,
                'group_name' => $chit_value
            ];

            Mail::send(['text' => 'interest_mail'], $data, function ($message) use ($admin_email) {
                $message->to($admin_email)
                        ->subject('Customer Chit Interest Mail');
            });

        } catch (\Exception $mailEx) {
            // Mail failure should NOT cause 401
            \Log::error('Interest Mail Failed', [
                'error' => $mailEx->getMessage()
            ]);
        }

        /* =========================
           SUCCESS RESPONSE
        ========================= */
        return response()->json([
            'status'        => 'Success',
            'msg'           => '',
            'customer_name' => $customer_display,
            'customer_id'   => $interest_msg->customer_id
        ], 200);

    }
    catch (\Exception $e) {

        \Log::error('Customer Interest API Error', [
            'error' => $e->getMessage(),
            'line'  => $e->getLine(),
            'file'  => $e->getFile()
        ]);

        return response()->json([
            'status' => 'Error',
            'msg'    => 'Server Error'
        ], 500);
    }
}

  public function mobile_store_advance_receipt(Request $request)
  {   
    $validator = Validator::make($request->all(), [
        'tenant_id' => 'required',
        'branch_id' => 'required',
        'customer_id' => 'required',
        'employee_id' => 'required',
        'enrollment_id'=> 'required',
    ]);
    if ($validator->fails()) { 
        $response['status'] = "Error";
        $response['msg'] = $validator->errors()->first();
        return response()->json($response, 200);
    }
    else {
      try { 
         DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
        $advance_receipt = new AdvanceReceipt(); 
		
        $receipt_no =''; 
        $advance_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
        $tenantid = $advance_receipt->tenant_id;
        $advance_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
        $advance_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
        $customerid = $advance_receipt->customer_id;
        $advance_receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
        $advance_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
        $group_id = ChitDetail::where('id','=',$advance_receipt->enrollment_id)->pluck('group_id');
        $groupid = $group_id[0];
		$advance_receipt->group_id = $groupid;
        $employee_branch_id = Employee::where('id','=',$advance_receipt->employee_id)->pluck('branch_id');
        $advance_receipt->employee_branch_id = $employee_branch_id[0];
        $advance_receipt->receipt_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
        $receiptamount = $advance_receipt->receipt_amount;
        $pendingamount = $request->pending_amount;
		$advance_receipt->accounts_date = date('Y-m-d'); 
        $advance_receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : "0000-00-00";
        $advance_receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
        $advance_receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
        $advance_receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
        $advance_receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : "0000-00-00";
        $advance_receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
        $advance_receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
        $advance_receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
        $advance_receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
        $advance_receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : "0000-00-00";              $receiptdate = $advance_receipt->receipt_date;
        $advance_receipt->receipt_mode = "Mobile";
        $advance_receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
        $advance_receipt->recpt_excess_no = '';  
        $advance_receipt->offline_receipt_no = isset($request->offline_receipt_no) ? ($request->offline_receipt_no) : '';     
        $advance_receipt->printed = isset($request->printed) ? ($request->printed) : 0;
        $advance_receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
        $advance_receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
        $advance_receipt->status = isset($request->status) ? ($request->status) : 1;
        if($request->payment_type_id == 2){
          $advance_receipt->status = isset($request->status) ? ($request->status) : 2;
        }
        if($advance_receipt->receipt_amount !=0) {
          if($advance_receipt->save()) {
            if($receipt_no=='') {
              $receipt_no = 'ADV-'.sprintf('%05d', $advance_receipt->id);
            }                        
            $advance_receipt->receipt_no = $receipt_no; 
            if($advance_receipt->save()) { 
			
			

              if($tenantid == 13){
                //$rcpt = Receipt::where('receipt_no',$recepitnovalue)->get();
                //$grp = Group::where('id',$rcpt[0]->group_id)->get();
                $grp = Group::where('id',$groupid)->get();
                $groupname = $grp[0]->group_name;
                //$groupname = 'test';
                            $cus = Customer::where('id',$customerid)->get();
                $cusname = $cus[0]->name;
                $mobile_no = $cus[0]->mobile_no;
                $receiver = $mobile_no;
                //$receiver = "9786161151";
                
                            $msg = "Thanks for the payment of $receiptamount for your $groupname Balance: $pendingamount, KMF CHITS";
    
                            $msg1=$msg;
                            $apikey = "bXCZUkND6vy4PXqE";
                            $template_id = "1707163638385513876";
                            $sender_id = "KMFCHT";
                            $msg_split = str_split($msg,456);
                            for($i=0;$i<sizeof($msg_split);$i++)
                            {
                                
                                $msg_temp =  urlencode( $msg_split[$i] );
                                        $ch = curl_init("http://textsms.arssoftech.com/vb/apikey.php?apikey=$apikey&senderid=$sender_id&templateid=$template_id&number=$receiver&message=$msg_temp");
                                    
                                        
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                    $result = curl_exec($ch);
                                curl_close($ch);
                            }
              }  else if($tenantid == 11){
						
						if($request->payment_type_id==1){
							$payment_mode = "Cash";
						} else if($request->payment_type_id==2){
							$payment_mode = "Cheque";
						} else if($request->payment_type_id==3) {
							$payment_mode = "DD";
						} else if($request->payment_type_id==4){
							$payment_mode = "NEFT / RTGS";
						}
						$mobile_no = Customer::where('id',$request->customer_id)->pluck('mobile_no');
                        $receiver = $mobile_no[0];
						
						$groupname_id = Group::where('id',$groupid)->pluck('group_name');
						$group_name = $groupname_id[0];
						
						
									$msg = "Dear Sir / Madam Received payment of $receiptamount through $payment_mode for Group/no $group_name wide Receipt no $receipt_no Thank You, VVRS CHITFUND";
                                    //$receiver = $mobileno;
									
                                    $msg1=$msg;
                                    $msg_split = str_split($msg,456);
                                    for($i=0;$i<sizeof($msg_split);$i++){
                                        $msg_temp =  urlencode( $msg_split[$i] );
                                        $ch = curl_init("http://prodnd.bulkssms.com/httpapi/smsapi?uname=VVRSCF&password=vvrs@4320&sender=VVRSCF&receiver=$receiver&route=TA&msgtype=1&sms=$msg_temp");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                            $result = curl_exec($ch);
                                        curl_close($ch);
								}
								} else if($tenantid == 30){
                //$rcpt = Receipt::where('receipt_no',$recepitnovalue)->get();
                //$grp = Group::where('id',$rcpt[0]->group_id)->get();
                $grp = Group::where('id',$groupid)->get();
                $groupname = $grp[0]->group_name;
                //$groupname = 'test';
                            $cus = Customer::where('id',$customerid)->get();
                $cusname = $cus[0]->name;
                $mobile_no = $cus[0]->mobile_no;
				//$mobile_no = '7418470181';
                $receiver = '91' . $mobile_no;
                //$receiver = "9786161151";
                
                            $msg = "Dear $cusname, Payment of $receiptamount is Received, Receipt No $receipt_no on $receiptdate to Chit group $groupname, Thank you. More Details Call:9483900777 -www.mychit.in";
    
                            $msg1=$msg;
                            
                            $msg_split = str_split($msg,456);
                            for($i=0;$i<sizeof($msg_split);$i++)
                            {
                                
                                $msg_temp =  urlencode( $msg_split[$i] );
                                        $ch = curl_init("http://sms.visiontechtally.com/sendsms/?token=c2950b8f03d0f3d068e4708aca49b4ab&credit=2&sender=CHIFUN&message=$msg_temp&number=$receiver&templateid=1707169778993306028");
                                    
                                        
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_HEADER, 0);
                                    $result = curl_exec($ch);
                                curl_close($ch);
                            }
              }
			  else if($tenantid == 37){
						
                         $cus = Customer::where('id',$customerid)->get();
						
						$mobile_no = $cus[0]->mobile_no;
						//$mobile_no = "7418470181";
						$receiver = '91' . $mobile_no;
						
                        $msg = "Thanks! Your Chit payment was a success Rs. $receiptamount has been received.- SBMA CHITS FUNDS PVT LTD";
                        
                        $msg1=$msg;
                       
                        $msg_split = str_split($msg,456);
						//$receiver = "7418470181";
						
                        for($i=0;$i<sizeof($msg_split);$i++)
                        {
                            
                            $msg_temp =  urlencode( $msg_split[$i] );
                                   $ch = curl_init("http://smsserver9.creativepoint.in/api.php?username=sbmachit&password=700428&to=$receiver&from=SBMACF&message=$msg_temp&PEID=1501469990000038722&templateid=1507165132051035563");
								   
                                    
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                                $result = curl_exec($ch);
                            curl_close($ch);
							
                        }
                    } 
					
				if($tenantid == 42){	
				 
					if($request->payment_type_id == 1){
						
						$add_exp = new AccountExpense();            
						
						$add_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$add_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						 
						$add_exp->entry_date = isset($request->receipt_date) ? ($request->receipt_date) : 0;
						$add_exp->voucher_no = $receipt_no;
						
						$add_exp->debit_account_head = 100;
						$add_exp->debit_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 1;
						$cus_name = Customer::where('id',$customerid)->pluck('name');
						$cus_last_name = Customer::where('id',$customerid)->pluck('last_name');
					 
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0] . " as chit installment ";
						$add_exp->remarks = $remarks;
						$add_exp->debit_remarks = $remarks;
						$add_exp->save();
						
						$add_deb_exp = new AccountExpense();                
						$add_deb_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$add_deb_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$add_deb_exp->entry_date = isset($request->receipt_date) ? ($request->receipt_date) : 0;
						$add_deb_exp->voucher_no = $receipt_no;
						$add_deb_exp->debit_account_head = 0;
						$add_deb_exp->debit_amount = 0;
						$add_deb_exp->credit_account_head = 42;
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0];
						$add_deb_exp->remarks = $remarks;
						$add_deb_exp->credit_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
						$add_deb_exp->status = 1;
						$add_deb_exp->save();
						} else if(($request->payment_type_id == 2) || ($request->payment_type_id == 4) || ($request->payment_type_id == 3)){
											
						$bank_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
						$bank = BankDetail::where('id','=',$request->bank_name_id)->get();
						foreach($bank as $banks) {
							$bank_account_no = isset($banks->account_number) ? ($banks->account_number) : 0;
						}
						$cus_name = Customer::where('id',$customerid)->pluck('name');
						$cus_last_name = Customer::where('id',$customerid)->pluck('last_name');
						$accnt_head = AccountHead::where('bank_account_number','=',$bank_account_no)->get();
						foreach($accnt_head as $accnt_heads) {
							$account_head_id = isset($accnt_heads->id) ? ($accnt_heads->id) : 0;
						}
						
						$add_exp = new AccountExpense();                
						$add_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$add_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$add_exp->entry_date = isset($request->receipt_date) ? ($request->receipt_date) : 0;
						
						$add_exp->voucher_no = $receipt_no;
						$remarks = "Being the bank amount received from " . $cus_name[0] . ' ' . $cus_last_name[0];
						$add_exp->remarks = $remarks;
						$add_exp->debit_account_head = 100;
						
						$add_exp->debit_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 0;
						
						$add_exp->save();
					
						$add_deb_exp = new AccountExpense();                
						$add_deb_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$add_deb_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$add_deb_exp->entry_date = isset($request->receipt_date) ? ($request->receipt_date) : 0;
						$add_deb_exp->voucher_no = $receipt_no;
						
						$add_deb_exp->debit_account_head = 0;
						$add_deb_exp->debit_amount = 0;
						$remarks = "Being the bank amount received from " . $cus_name[0] . ' ' . $cus_last_name[0];
						$add_deb_exp->remarks = $remarks;
						$add_deb_exp->credit_account_head = $account_head_id;
						$add_deb_exp->credit_amount = isset($request->adv_receipt_amount) ? ($request->adv_receipt_amount) : 0;
						$add_deb_exp->status = 1;
						$add_deb_exp->save();
						}
						else {
						$add_exp = new AccountExpense();                
						$add_exp->tenant_id = isset($advance_receipt->tenant_id) ? ($advance_receipt->tenant_id) : 0;
						$add_exp->branch_id = isset($advance_receipt->branch_id) ? ($advance_receipt->branch_id) : 0;
						$add_exp->entry_date = isset($advance_receipt->receipt_date) ? ($advance_receipt->receipt_date) : 0;
						$add_exp->voucher_no = $receipt_no1;
						$add_exp->debit_account_head = 1;
						$add_exp->debit_amount = isset($advance_receipt->receipt_amount) ? ($advance_receipt->receipt_amount) : 0;
						$add_exp->credit_account_head = 0;
						$add_exp->credit_amount = 0;
						$add_exp->status = 1;
						$cus_name = Customer::where('id',$customer_id)->pluck('name');
						$cus_last_name = Customer::where('id',$customer_id)->pluck('last_name');
					   
						$remarks = "Being the cash received from " . $cus_name[0] . ' ' . $cus_last_name[0] . " as chit installment ";
						$add_exp->remarks = $remarks;
						$add_exp->debit_remarks = $remarks;
						$add_exp->save();	
						}
			}
              $response['status'] = 'Success';
              $response['msg'] = \Lang::get('api.success_advance_receipt_added');
              $response['receipt_no'] = $advance_receipt->receipt_no;
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
        else {
          $response['status']="Error";
          $response['msg']=\Lang::get('api.received_amt_not_found');
          return response()->json($response,200);
        }
      }            
      catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
      }
    }
  }

  public function mobile_list_groups(Request $request) 
  {
    try { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=[];
      if(isset($request->branch_id))
      {
        $cond['branch_id'] = $request->branch_id;
      }
      $groups = Group::where(array('tenant_id'=>$request->tenant_id))->where($cond)->orderBy('id','desc')->get();      
      $i=1; 
      $response_data = [];
      foreach($groups as $group) {
        $group_det = [];
        $group_det['tenant_id'] = strval($group->tenant_id);
        $group_det['branch_id'] = strval($group->branch_id);
        $group_det['group_id'] = strval($group->id);
        $group_det['group_name'] = $group->group_name;
        array_push($response_data,$group_det);
        $i++;
      }
      if($groups){          
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
    }
  }

  public function mobile_list_customers(Request $request)
{
    try {

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $query = Customer::where('tenant_id', $request->tenant_id);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $customers = $query->orderBy('id', 'desc')->get();

        $response_data = [];
        $i = 1;

        foreach ($customers as $customer) {
            $response_data[] = [
                'sno'           => $i++,
                'tenant_id'     => (string) $customer->tenant_id,
                'branch_id'     => (string) ($customer->branch_id ?? ''),
                'customer_id'   => (string) $customer->id,
                'customer_name' => $customer->name,
                'mobile_no'     => $customer->mobile_no,
                'phone_no'      => $customer->phone_no,
                'customer_code'      => $customer->customer_code,
            ];
        }

        return response()->json([
            'status' => 'Success',
            'data'   => $response_data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()   // show actual issue
        ], 500);
    }
}

  
  public function mobile_list_employees(Request $request) 
  {
    try { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond=[];
      if(isset($request->branch_id))
      {
        $cond['branch_id'] = $request->branch_id;
      }
      $employees = Employee::where(array('tenant_id'=>$request->tenant_id))->where($cond)->orderBy('id','desc')->get();      
      $i=1; 
      $response_data = [];
      foreach($employees as $employee) {
        $employee_det = [];
        $employee_det['tenant_id'] = strval($employee->tenant_id);
        $employee_det['branch_id'] = strval($employee->branch_id);
        $employee_det['employee_id'] = strval($employee->id);
        $employee_det['employee_name'] = $employee->first_name." ". $employee->last_name;
        if($employee->employee_type ==1 && $employee->collection_agent ==0)
        {
          $employee_type = "Employee";
        }
        elseif($employee->employee_type ==2)
        {
          $employee_type = "Business Agent";
        }
        elseif($employee->collection_agent ==1 && $employee->employee_type ==1)
        {
          $employee_type = "Collection Agent";
        }
        else
        {
          $employee_type  = "";
        }
        $employee_det['employee_type'] = $employee_type;
        array_push($response_data,$employee_det);
        $i++;
      }
      if($employees){          
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response, 401);
    }
  }
 
public function collection_reports(Request $request)
{
    try {

        $cond = [];
        $cond1 = [];
        $response_data = [];

        $start_date = $request->start_date ?? '';
        $end_date   = $request->end_date ?? '';

        if ($start_date == '' && $end_date == '') {
            $start_date = date('Y-m-d');
            $end_date   = date('Y-m-d');
        }

        if ($start_date != '' && $end_date == '') {
            $end_date = date('Y-m-d');
        }

        if (!empty($request->tenant_id))
            $cond['tenant_id'] = $request->tenant_id;

        if (!empty($request->branch_id))
            $cond['branch_id'] = $request->branch_id;

        if (!empty($request->customer_id))
            $cond['customer_id'] = $request->customer_id;

        if (!empty($request->employee_id))
            $cond['employee_id'] = $request->employee_id;

        if (!empty($request->group_id))
            $cond1['group_id'] = $request->group_id;

        if (!empty($request->collection_area_id))
            $cond1['collection_area_id'] = $request->collection_area_id;

        $login_tables = collect();
        $advance_receipts = collect();
        $before_receipts = collect();

        if (empty($cond1)) {

            $receipt_query = Receipt::select(
                    'receipt_no',
                    'tenant_id',
                    'branch_id',
                    'customer_id',
                    'employee_id',
                    'payment_type_id',
                    'receipt_date',
                    'advance_available'
                )
                ->selectRaw('amount as total_amount')
                ->where($cond);

            $advance_query = AdvanceReceipt::where($cond);
            $before_query  = BeforeEnrolReceipt::where($cond);

            if ($start_date == '' && $end_date != '') {
                $receipt_query->where('receipt_date', '<=', $end_date);
                $advance_query->where('receipt_date', '<=', $end_date);
                $before_query->where('received_date', '<=', $end_date);
            }
            elseif ($start_date != '' && $end_date != '') {
                $receipt_query->whereBetween('receipt_date', [$start_date, $end_date]);
                $advance_query->whereBetween('receipt_date', [$start_date, $end_date]);
                $before_query->whereBetween('received_date', [$start_date, $end_date]);
            }

            $login_tables = $receipt_query
                ->groupBy(
                    'receipt_no',
                    'tenant_id',
                    'branch_id',
                    'customer_id',
                    'employee_id',
                    'payment_type_id',
                    'receipt_date',
                    'advance_available'
                )->get();

            $advance_receipts = $advance_query->get();
            $before_receipts = $before_query->get();
        }
        else {

            $chits = ChitDetail::where($cond)->where($cond1)->get();

            foreach ($chits as $chit) {

                $receipt_query = Receipt::select(
                        'receipt_no',
                        'tenant_id',
                        'branch_id',
                        'customer_id',
                        'employee_id',
                        'payment_type_id',
                        'receipt_date',
                        'advance_available'
                    )
                    ->selectRaw('amount as total_amount')
                    ->where('enrollment_id', $chit->id);

                $advance_query = AdvanceReceipt::where('enrollment_id', $chit->id);

                if (!empty($request->employee_id)) {
                    $receipt_query->where('employee_id', $request->employee_id);
                    $advance_query->where('employee_id', $request->employee_id);
                }

                if ($start_date == '' && $end_date != '') {
                    $receipt_query->where('receipt_date', '<=', $end_date);
                    $advance_query->where('receipt_date', '<=', $end_date);
                }
                elseif ($start_date != '' && $end_date != '') {
                    $receipt_query->whereBetween('receipt_date', [$start_date, $end_date]);
                    $advance_query->whereBetween('receipt_date', [$start_date, $end_date]);
                }

                $login_tables = $login_tables->merge(
                    $receipt_query->groupBy(
                        'receipt_no',
                        'tenant_id',
                        'branch_id',
                        'customer_id',
                        'employee_id',
                        'payment_type_id',
                        'receipt_date',
                        'advance_available'
                    )->get()
                );

                $advance_receipts = $advance_receipts->merge(
                    $advance_query->get()
                );
            }

            $before_query = BeforeEnrolReceipt::where($cond);

            if ($start_date == '' && $end_date != '')
                $before_query->where('received_date', '<=', $end_date);
            elseif ($start_date != '' && $end_date != '')
                $before_query->whereBetween('received_date', [$start_date, $end_date]);

            $before_receipts = $before_query->get();
        }

        foreach ($login_tables as $login) {

            $customer = Customer::find($login->customer_id);

            $total_advance = 0;
            if ($login->advance_available == 1) {
                $advance = AdvanceReceipt::where('recpt_excess_no', $login->receipt_no)->first();
                $total_advance = $advance->receipt_amount ?? 0;
            }

            $received_amount = ($login->total_amount ?? 0) + $total_advance;

            $response_data[] = [
                'tenant_id'      => strval($login->tenant_id),
                'branch_id'      => strval($login->branch_id),
                'branch_name'    => optional($login->branch_det)->branch_name ?? '',
                'employee_name'  => optional($login->employee_det)->first_name . ' ' . optional($login->employee_det)->last_name,
                'customer_id'    => strval($login->customer_id),
                'customer_name'  => $customer->name ?? '',
                'mobile_no'      => $customer->mobile_no ?? '',
                'customer_code'  => $customer->customer_code ?? '',
                'receipt_no'     => $login->receipt_no ?? '',
                'receipt_date'   => date('d-m-Y', strtotime($login->receipt_date)) ?? '',
                'received_amount'=> strval(round($received_amount)),
                'payment_mode'   => optional($login->payment_type_det)->payment_name ?? '',
                'cheque_details' => ($login->payment_type_id == 2)
                                    ? ($login->status == 1 ? "Cleared" : "Pending")
                                    : '',
                'receipt_type'   => "Due"
            ];
        }

        foreach ($advance_receipts as $adv) {

            if ($adv->recpt_excess_no != "")
                continue;

            $customer = Customer::find($adv->customer_id);

            $response_data[] = [
                'tenant_id'      => strval($adv->tenant_id),
                'branch_id'      => strval($adv->branch_id),
                'branch_name'    => optional($adv->branch_det)->branch_name ?? '',
                'employee_name'  => optional($adv->employee_det)->first_name . ' ' . optional($adv->employee_det)->last_name,
                'customer_id'    => strval($adv->customer_id),
                'customer_name'  => $customer->name ?? '',
                'mobile_no'      => $customer->mobile_no ?? '',
                'customer_code'  => $customer->customer_code ?? '',
                'receipt_no'     => $adv->receipt_no ?? '',
                'receipt_date'   => date('d-m-Y', strtotime($adv->receipt_date)) ?? '',
                'received_amount'=> strval(round($adv->receipt_amount ?? 0)),
                'payment_mode'   => optional($adv->payment_type_det)->payment_name ?? '',
                'cheque_details' => ($adv->payment_type_id == 2)
                                    ? ($adv->status == 1 ? "Cleared" : "Pending")
                                    : '',
                'receipt_type'   => "Advance"
            ];
        }

        foreach ($before_receipts as $bef) {

            $customer = Customer::find($bef->customer_id);

            $response_data[] = [
                'tenant_id'      => strval($bef->tenant_id),
                'branch_id'      => strval($bef->branch_id),
                'branch_name'    => optional($bef->branch_det)->branch_name ?? '',
                'employee_name'  => optional($bef->employee_det)->first_name . ' ' . optional($bef->employee_det)->last_name,
                'customer_id'    => strval($bef->customer_id),
                'customer_name'  => $customer->name ?? '',
                'mobile_no'      => $customer->mobile_no ?? '',
                'customer_code'  => $customer->customer_code ?? '',
                'receipt_no'     => $bef->receipt_no ?? '',
                'receipt_date'   => date('d-m-Y', strtotime($bef->received_date)) ?? '',
                'received_amount'=> strval(round($bef->amount ?? 0)),
                'payment_mode'   => optional($bef->payment_type_det)->payment_name ?? '',
                'cheque_details' => ($bef->payment_type_id == 2)
                                    ? ($bef->status == 1 ? "Cleared" : "Pending")
                                    : '',
                'receipt_type'   => "Before Enrollment"
            ];
        }

        return response()->json($response_data, 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => \Lang::get('api.global_error')
        ], 401);
    }
}

 public function list_collection_remarks(Request $request) 
  {
    try { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $collect_remark = CollectionRemark::where('tenant_id','=',$request->tenant_id)->orderBy('id','desc')->get();     

      $i=1; 
      $response_data = [];
      foreach($collect_remark as $cr) 
      {
        $branch_det = [];
        $branch_det['collection_remarks_id'] = strval($cr->id);
        $branch_det['tenant_id'] = strval($cr->tenant_id);
        $branch_det['remark_name'] = $cr->remark_name;               
        array_push($response_data,$branch_det);
        $i++;
      }
      if($collect_remark) {          
        return response()->json($response_data,200);
      }     
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function store_remarks(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $validator=Validator::make($request->all(),[
        'tenant_id'=>'required',
        'customer_id'=>'required',
        'remarks'=>'required',
        'remark_date'=>'required',
        'remark_time'=>'required',
      ]); 
      if($validator->fails()){
          $response['status']="Error";
          $response['msg']=$validator->error()->first();
          return response()->json($response,401);
      }
      else {
        try { 
          $state=new Remark(); 
          $state->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
          $state->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
          $state->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
          $state->employee_id = isset($request->created_by) ? ($request->created_by) : 0;
          $state->remarks = isset($request->remarks) ? ($request->remarks) : 0;
          $state->remark_date = isset($request->remark_date) ? ($request->remark_date) : '0000-00-00';
          $state->remark_time = isset($request->remark_time) ? ($request->remark_time) : '00:00:00';
          $state->next_followup_date = isset($request->next_followup_date) ? ($request->next_followup_date) : '0000-00-00';
          $state->created_by = isset($request->created_by) ? ($request->created_by) : 0;
          $state->status = isset($request->status) ? ($request->status) : 1;
          if($state->save()){
            $response['status']="Success";
            $response['msg']=\Lang::get('api.success_remark_added');
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

   public function outstanding_reports(Request $request)
 {
  try
  {   
   DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
    $cond = [];
    $end_date='';
    $start_date = '';      
    if(isset($request->branch_id))
    {
      $cond['branch_id'] = $request->branch_id;
    }
    if(isset($request->group_id))
    {
      $cond['group_id'] = $request->group_id;
    } 
    if(isset($request->collection_area_id))
    {
      $cond['collection_area_id'] = $request->collection_area_id;
    }
    if(isset($request->collection_agent_id))
    {
      $cond['collection_agent_id'] = $request->collection_agent_id;
    }
    if(isset($request->tenant_id))
    {
      $cond['tenant_id'] = $request->tenant_id;
    }
	
    $login_tables = ChitDetail::where($cond)->where('customer_id','!=',1)->orderBy('id','asc')->get();
    $response_data = [];
    $cust_id = []; 
    foreach($login_tables as $log)
    {
      $cust_id_u = $log->customer_id;
      if(!in_array($cust_id_u, $cust_id))
      {
        $chit_det = [];
        $chit_det['customer_id'] = strval($log->customer_id);
        $chit_det['customer_name'] = '';
        $chit_det['customer_code'] = '';
        $chit_det['customer_mobile_no'] ='';
        $chit_det['customer_phone_no'] ='';
        if($log->customer_det){
          $chit_det['customer_name'] = $log->customer_det->name . ' ' . $log->customer_det->last_name;
          $chit_det['customer_code'] = $log->customer_det->customer_code;
          $chit_det['customer_mobile_no'] =$log->customer_det->mobile_no;
          $chit_det['customer_phone_no'] =$log->customer_det->phone_no;
        }
        $chit_det['employee_id'] = strval($log->collection_agent_id);
        $chit_det['employee_name'] = '';
        if($log->collection_agent_det){
          $chit_det['employee_name'] = $log->collection_agent_det->first_name." ".$log->collection_agent_det->last_name;
        } 
        $chit_det['branch_name'] = optional($log->branch_det)->branch_name ?? '';
        
        $total_pending =0;
        $total_installment =0;
        $bid_total_installment =0;
        $total_paid =0;
        $sum_auctions =0;
        $chit_details= ChitDetail::where('customer_id',$log->customer_id)->get();
        foreach($chit_details as $chit)
        { 
          $group_id=   $chit->group_id;
          $customer_id=   $chit->customer_id;
          $enrollment_id = $chit->id;
          if($chit->chit_type == 3)
          {  // Commitment chit
            $bidding = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
            $install_amt = isset($bidding) ? ($bidding) : 0;
            $total_installment = $total_installment + $install_amt;
          } 
          if($chit->chit_type != 3)
          { // Bidding
            $bidding = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install_amt')->where('enrollment_id','!=',0)->where('group_id',$group_id)->groupBy('group_id')->get();
            
            $bid_install_amt = isset($bidding[0]->total_install_amt) ? ($bidding[0]->total_install_amt) : 0;
            $bid_total_installment = $bid_total_installment + $bid_install_amt;  
          }      

          $sum_auctions = $total_installment + $bid_total_installment;  
          // Receipt Calculation           
          $receipt=Receipt::selectRaw('*, sum(received_amount) + sum(bonus) as total_received')->where('customer_id',$customer_id)->where('status','=',1)->groupBy('customer_id')->get();
          $total_paid = isset($receipt[0]->total_received) ? ($receipt[0]->total_received) : 0;
          $chit_det['total_paid'] = strval(round($total_paid));
          
          $total_pending = $sum_auctions - $total_paid;
          $chit_det['total_pending'] = strval(round($total_pending));
        } 
     if($total_pending > 0){
    $cust_id[] = $cust_id_u;
    array_push($response_data,$chit_det);
}
      }
    }
    if(count($login_tables)>0)
    {      
      return response()->json($response_data,200);
    }        
    else
    {
      return response()->json($response_data, 200);
    }
  }  
  catch(\Exception $e)
  {
    $response['status'] = 'Error';
    $response['msg'] = \Lang::get('api.global_error');
    return response()->json($response, 401);
  }      
 }

 Public function auction_reports(Request $request)
 {
  try
  {
     DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
    $cond = [];
    $start_date ='';
    $end_date ='';
    if(isset($request->tenant_id))
    {
      $cond['tenant_id'] = $request->tenant_id;
    }
    if(isset($request->branch_id))
    {
      $cond['branch_id'] = $request->branch_id;
    }
    if(isset($request->group_id))
    {
      $cond['group_id'] = $request->group_id;
    }
    if(isset($request->start_date)) {
      $start_date = $request->start_date;
    }       
    if(isset($request->end_date)) {
      $end_date = $request->end_date;
    } 
    if(($start_date=='') && ($end_date=='')) {
         $login_tables = BiddingDetail::where($cond)->get();
    }
    else if(($start_date=='') && ($end_date!='')) {
      $login_tables = BiddingDetail::where($cond)->where('auction_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
    }
    else {
      if(($start_date!='') && ($end_date=='')) {
          $end_date = date('Y-m-d');
      }
      $login_tables = BiddingDetail::where($cond)->whereBetween('auction_date',[$start_date,$end_date])->get();
    }  

    $response_data = [];
    foreach($login_tables as $log)
    {
      $bid_det = [];
      $bid_det['bid_primary_id'] = isset($log->id) ? (strval($log->id)) : "";
      $bid_det['customer_id'] = isset($log->customer_id) ? (strval($log->customer_id)) : "";
      $bid_det['customer_name'] = '';
	  $bid_det['chit_value'] = isset($log->enrollment_det) ? (strval($log->enrollment_det->group_det->scheme_det->chit_value)) : "0";
	  $bid_det['bidding_amount'] = isset($log->bidding_amount) ? (strval($log->bidding_amount)) : "0";
	  $bid_det['auction_date'] = isset($log->auction_date) ? date('d-m-Y',strtotime($log->auction_date)) : "0";
      if($log->customer_det)
      {
        $bid_det['customer_name'] = $log->customer_det->name;
      }
      $bid_det['group_id'] = isset($log->group_id) ? (strval($log->group_id)) : "";
      $bid_det['group_name'] = '';
      if($log->group_det)
      {
        $bid_det['group_name'] = $log->group_det->group_name;
      }
      $bid_det['ticket_no'] = '';
      if($log->enrollment_det)
      {
        $bid_det['ticket_no'] = strval($log->enrollment_det->ticket_no);
      }
      $bid_det['branch_name'] = optional($log->branch_det)->branch_name ?? '';
      $bid_det['total_release_amount'] = strval(round($log->total_release_amount));
      array_push($response_data,$bid_det);
    }
    if(count($login_tables)>0)
    {      
      return response()->json($response_data,200);
    }        
    else
    {     
      return response()->json($response_data, 200);
    }
  }
  catch(\Exception $e)
  {
    $response['status'] = 'Error';
    $response['msg'] = \Lang::get('api.global_error');
    return response()->json($response, 401);
  }
 }

 Public function commitment_payment_reports(Request $request)
 {
  try
  {
     DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
    $cond = [];
    $start_date ='';
    $end_date ='';
    if(isset($request->tenant_id))
    {
      $cond['tenant_id'] = $request->tenant_id;
    }
    if(isset($request->branch_id))
    {
      $cond['branch_id'] = $request->branch_id;
    }   
    if(isset($request->start_date)) {
      $start_date = $request->start_date;
    }       
    if(isset($request->end_date)) {
      $end_date = $request->end_date;
    } 
    if(($start_date=='') && ($end_date=='')) {
         $payments = Payment::selectRaw('*,sum(total_amount) as total_payment')->with(['branch_det','payment_type_det','customer_det'])->where($cond)->where('payment_for',7)->groupBy('customer_id')->get();
    }
    else if(($start_date=='') && ($end_date!='')) {
      $payments = Payment::selectRaw('*,sum(total_amount) as total_payment')->with(['branch_det','payment_type_det','customer_det'])->where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->where('payment_for',7)->groupBy('customer_id')->get();
    }
    else {
      if(($start_date!='') && ($end_date=='')) {
          $end_date = date('Y-m-d');
      }
      $payments =Payment::selectRaw('*,sum(total_amount) as total_payment')->with(['branch_det','payment_type_det','customer_det'])->where($cond)->whereBetween('payment_date',[$start_date,$end_date])->where('payment_for',7)->groupBy('customer_id')->get();
    }  

    $response_data = [];
    foreach($payments as $payment)
    {
      $bid_det = [];
      $bid_det['customer_id'] = isset($payment->customer_id) ? (strval($payment->customer_id)) : "";
      $bid_det['customer_name'] = '';
      if($payment->customer_det)
      {
        $bid_det['customer_name'] = $payment->customer_det->name;
      }
      //$bid_det['enrollment_id'] = isset($payment->enrollment_id) ? (strval($payment->enrollment_id)) : "";
      $bid_det['payment_type_name'] = isset($payment->payment_type_det->payment_name) ? (strval($payment->payment_type_det->payment_name)) : "";
      $bid_det['due_amount'] = strval(round($payment->total_payment));
      $bid_det['branch_name'] = optional($payment->branch_det)->branch_name ?? '';
      array_push($response_data,$bid_det);
    }
    if(count($payments)>0)
    {      
      return response()->json($response_data,200);
    }        
    else
    {
      $response_data['status'] = 'Error';
      $response_data['msg'] = \Lang::get('api.no_data_found');
      return response()->json($response_data, 200);
    }
  }
  catch(\Exception $e)
  {
    $response['status'] = 'Error';
    $response['msg'] = \Lang::get('api.global_error');
    return response()->json($response, 401);
  }
 }

 public function mobile_list_collection_area(Request $request) 
  {
    try { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      if($request->tenant_id)      
      {
        $collect_areas = CollectionArea::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get(); 
      }
      else
      {
        $collect_areas = CollectionArea::orderBy('id','desc')->get(); 
      }
      $i=1; 
      $response_data = [];
      foreach($collect_areas as $ca) {
        $customer_det = [];
        $customer_det['id'] = strval($ca->id);
        $customer_det['collection_area'] = isset($ca->collection_area) ? strval(($ca->collection_area)) : "";
        array_push($response_data,$customer_det);
        $i++;
      }           
      if($collect_areas){          
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e) {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  Public function payment_details(Request $request)
  {
    try
    {     
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'id'=>$request->group_id);
      $groups = Group::where($cond)->get();
           
      $response_data = [];
      $response_data['group_id'] =strval($groups[0]->id); 
      $response_data['group_name'] =$groups[0]->group_name; 
      $response_data['no_of_members'] = '';
      $response_data['chit_value'] = '';
      $response_data['no_of_months'] = '';
      if($groups[0]->scheme_det)
      {
        $response_data['no_of_members'] = strval($groups[0]->scheme_det->members);
        $response_data['chit_value'] = strval(round($groups[0]->scheme_det->chit_value));
        $response_data['no_of_months'] = strval(round($groups[0]->scheme_det->no_of_months));
      } 
      
      $bidding = BiddingDetail::where('group_id',$groups[0]->id)->get();
      $auction_detals = [];
      foreach($bidding as $bid)
      { 
        $auction_det = [];
        $auction_det['bid_primary_id'] = isset($bid->id) ? (strval($bid->id)) : 0;
        $auction_det['auction_no'] =isset($bid->installment_no) ? (strval($bid->installment_no)) : 0;
        $auction_det['customer_id'] = isset($bid->customer_id) ? (strval($bid->customer_id)) : "0";
        $auction_det['customer_name'] = isset($bid->customer_det) ? ($bid->customer_det->name) : "";
        $auction_det['group_id'] = isset($bid->group_id) ? (strval($bid->group_id)) : "0";
        $auction_det['group_name'] = isset($bid->group_det) ? ($bid->group_det->group_name) : "";
        $auction_det['ticket_no'] = isset($bid->enrollment_det) ? (strval($bid->enrollment_det->ticket_no)) : "0";
        $auction_det['total_release_amount'] = isset($bid->total_release_amount) ? (strval(round($bid->total_release_amount))) : "0";
        $auction_det['bidding_amount'] = isset($bid->bidding_amount) ? (strval(round($bid->bidding_amount))) : "0";
        $auction_det['commission_amount'] = isset($bid->commission_amount) ? (strval(round($bid->commission_amount))) : "0";
        $auction_det['divident_amount'] = isset($bid->divident_amount) ? (strval(round($bid->divident_amount))) : "0";
        $auction_det['installment_amount'] = isset($bid->installment_amount) ? (strval(round($bid->installment_amount))) : "0";
        $auction_det['gst'] = isset($bid->gst) ? (strval(round($bid->gst))) : "0";
        $auction_det['cgst'] = isset($bid->cgst) ? (strval(round($bid->cgst))) : "0";
        $auction_det['sgst'] = isset($bid->sgst) ? (strval(round($bid->sgst))) : "0";
        $auction_det['customer_released'] = isset($bid->customer_released) ? (strval(round($bid->customer_released))) : "0";
        $auction_det['pending_release'] = isset($bid->pending_release) ? (strval(round($bid->pending_release))) : "0";
        $auction_det['payment_status'] = '';        
        
       
        if($bid->customer_released !=0 && $bid->pending_release !=0)
        {
         $payment = Payment::where('bidding_id',$bid->id)->where('payment_for',1)->get();
         $counts = $payment->count();
          if($counts >= 1)
          {
            $auction_det['payment_status'] = "Part Payment";
          }
        } 
        if($bid->customer_released ==0 && $bid->pending_release !=0)
        {         
          $auction_det['payment_status'] = "Payment Pending";         
        }         
        if($bid->customer_released !=0 && $bid->pending_release ==0)
        {
          $payment = Payment::where('bidding_id',$bid->id)->where('payment_for',1)->get();
          $counts = $payment->count();
          if($counts == 1)
          { 
            $auction_det['payment_status'] = "Full Payment";
          }     
          if($counts > 1)
          {
             $auction_det['payment_status'] = "Part Payment";
          }       
        }        
        
        array_push($auction_detals,$auction_det);
      }
      $response_data['auction_details'] = $auction_detals;
      if($groups)
      {
        return response()->json($response_data,200);
      }
      else
      {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  Public function mobile_vacant_report(Request $request)
  {
    try
    {

       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = [];
      $response_data =[];
      if(isset($request->tenant_id))
      {
        $cond['tenant_id'] = $request->tenant_id;
      }
      if(isset($request->branch_id))
      {
        $cond['branch_id'] = $request->branch_id;
      }
      if(isset($request->group_id))
      {
        $cond['id'] = $request->group_id;
      }
      $start_date=$request->start_date;
      $end_date=$request->end_date;
      $groups = Group::where($cond)->where('group_pending_ticket','>',0)->get();
      foreach($groups as $group)
      {
        $vacant_det = [];
        $vacant_det['group_id']= isset($group->id) ? (strval($group->id)) : "0";
        $vacant_det['branch_id']= isset($group->branch_id) ? (strval($group->branch_id)) : "0";
        $vacant_det['branch_name']= isset($group->branch_det) ? ($group->branch_det->branch_name) : "";
        $vacant_det['group_name']= isset($group->group_name) ? ($group->group_name) : "";
        $vacant_det['group_starting_date']= isset($group->starting_date) ? ($group->starting_date) : "0000-00-00";
        $vacant_det['chit_value']= isset($group->scheme_det) ? (strval(round($group->scheme_det->chit_value))) : "0";
        $vacant_det['no_of_vacants']= isset($group->group_pending_ticket) ? (strval($group->group_pending_ticket)) : "0";
        $dummy_chits = ChitDetail::where(array("group_id"=>$group->id,"chit_type" => "1"))->count();
        $vacant_det['no_of_dummy_chits']= $dummy_chits;   

        array_push($response_data,$vacant_det);
      }
      if($groups)
      {
        return response()->json($response_data,200);
      }
      else
      {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['statuss'] = $e;
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function business_agent_report(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond =[];
      $start_date ='';
      $end_date ='';
      $response_data =[];
      if(isset($request->tenant_id))
      {
        $cond['tenant_id'] =$request->tenant_id;
      }
      if(isset($request->branch_id))
      {
        $cond['branch_id'] =$request->branch_id;
      }      
      $business_agent = Employee::where($cond)->where('role_id',7)->get();
      foreach($business_agent as $ba)
      {
        $agent_det =[];
        $agent_det['agent_id'] = strval($ba->id);
        $agent_det['agent_name'] = $ba->first_name." ".$ba->last_name;
        $agent_det['branch_name'] = optional($ba->branch_det)->branch_name ?? '';
        $chit = ChitDetail::selectRaw('*,sum(total_business_agent_commission) total_commission,sum(paid_business_agent_commission) as total_paid,sum(balance_business_agent_commission) as total_pending')->where('introducer_id',$ba->id)->groupBy('introducer_id')->get();
        $agent_det['total_commission_amount'] = isset($chit[0]->total_commission) ? (strval(round($chit[0]->total_commission))) : "0";
        $agent_det['paid_commission_amount'] = isset($chit[0]->total_paid) ? (strval(round($chit[0]->total_paid))) : "0";
        $agent_det['pending_commission_amount'] = isset($chit[0]->total_pending) ? (strval(round($chit[0]->total_pending))) : "0";
        array_push($response_data,$agent_det);
      }   
      if($business_agent)
      {
        return response()->json($response_data,200);
      }
      else
      {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function agent_wise_customer_details(Request $request)
  {
    try
    {
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $employee = Employee::where('id',$request->agent_id)->get();
      $response_data =[];
      
      $response_data['agent_id'] = isset($employee[0]->id) ? (strval($employee[0]->id)) : "0";
      $first_name = isset($employee[0]->first_name) ? ($employee[0]->first_name) : "";
      $last_name = isset($employee[0]->last_name) ? ($employee[0]->last_name) : "";      
      $response_data['agent_name'] = $first_name." ".$last_name;
      $response_data['phone_no'] = isset($employee[0]->phone_no) ? ($employee[0]->phone_no) : "";
      $response_data['mobile_no'] = isset($employee[0]->mobile_no) ? ($employee[0]->mobile_no) : "";
      $response_data['profile_photo']= isset($employee[0]->photo) ? env('APP_URL').Storage::url('app/uploads/employee_photos/'.$employee[0]->photo) : '';
      $chits = ChitDetail::where('introducer_id',$employee[0]->id)->get();
      $custome_details = [];
      foreach($chits as $chit)
      {
        $customer_det = [];
        $customer_det['customer_id']  = isset($chit->customer_id) ? (strval($chit->customer_id)) : "0";
        $customer_det['customer_name']  = isset($chit->customer_det->name) ? ($chit->customer_det->name) : "";
        $customer_det['group_id']  = '0';
        $customer_det['group_name']  = '';
        $customer_det['ticket_no']  = '';
        if($chit->chit_type !=3)
        {
          $customer_det['group_id']  = isset($chit->group_id) ? (strval($chit->group_id)) : "0";
          $customer_det['group_name']  = isset($chit->group_det->group_name) ? ($chit->group_det->group_name) : "";
          $customer_det['ticket_no']  = isset($chit->ticket_no) ? (strval($chit->ticket_no)) : "0";
        }
        if($chit->chit_type ==3)
        {
          $customer_det['group_id']  = "0";
          $slab_types = SlabDue::where('id',$chit->slab_id)->get();   
          $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
          $customer_det['group_name']  = "Commitment Chit/".$slab_name;
          $customer_det['ticket_no']  = "-";
        }
        $customer_det['total_commission_amount'] = isset($chit->total_business_agent_commission) ? (strval(round($chit->total_business_agent_commission))) : "0";
        $customer_det['paid_commission_amount'] = isset($chit->paid_business_agent_commission) ? (strval(round($chit->paid_business_agent_commission))) : "0";
        $customer_det['pending_commission_amount'] = isset($chit->balance_business_agent_commission) ? (strval(round($chit->balance_business_agent_commission))) : "0";
      
        array_push($custome_details,$customer_det);
      }
      $response_data['customer_details'] = $custome_details;
      if($chits)
      {
        return response()->json($response_data,200);
      }
      else
      {
        return response()->json($response_data,200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function mobile_day_closing_reports(Request $request)
  {     
    try
    { 
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $response_data = [];
      if(isset($request->start_date)) 
      {                             
         $start_date = $request->start_date;
      }
      $cond = array('branch_id'=>$request->branch_id,'tenant_id'=>$request->tenant_id);
      //cash
      $response_data['start_date'] = $start_date;
      $response_data['branch_info'] = '';
      if(isset($request->branch_id))
      {
          $branch = Branch::where('id',$request->branch_id)->get();
          $response_data['branch_info'] = $branch[0]->branch_name;
      }
      $cash = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',1)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['cash_collection'] = isset($cash[0]->total_received) ? (strval(round($cash[0]->total_received))) : "0";
      $response_data['cash_penalty'] = isset($cash[0]->total_penalty) ? (strval(round($cash[0]->total_penalty))) : "0";
      $adv_cash = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',1)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['advance_cash'] = isset($adv_cash[0]->total_adv) ? (strval(round($adv_cash[0]->total_adv))) : "0"; 
      $response_data['other_charges_cash'] = "0";

      // cheque received(Pending cheque)
      $cheque_received = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',2)->where(array('receipt_date'=>$start_date))->where($cond)->get(); 
      $response_data['cheque_received_collection'] = isset($cheque_received[0]->total_received) ? (strval(round($cheque_received[0]->total_received))) : "0";
      $response_data['cheque_penalty'] = isset($cheque_received[0]->total_penalty) ? (strval(round($cheque_received[0]->total_penalty))) : "0";
      $adv_cheque = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',2)->where(array('receipt_date'=>$start_date))->where($cond)->get();
      $response_data['advance_cheque'] = isset($adv_cheque[0]->total_adv) ? (strval(round($adv_cheque[0]->total_adv))) : "0"; 
      $response_data['other_charges_cheque'] = "0";

      // cheque cleared
      $cheque_cleared = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get(); 
      $response_data['cheque_cleared_collection'] = isset($cheque_cleared[0]->total_received) ? (strval(round($cheque_cleared[0]->total_received))) : "0";
      $response_data['cheque_cleared_penalty'] = isset($cheque_cleared[0]->total_penalty) ? (strval(round($cheque_cleared[0]->total_penalty))) : "0";  
      $adv_cheque_cleared = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['advance_cleared_cheque'] = isset($adv_cheque_cleared[0]->total_adv) ? (strval(round($adv_cheque_cleared[0]->total_adv))) : "0";                       

      //dd
      $dd = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',3)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['dd_collection'] = isset($dd[0]->total_received) ? (strval(round($dd[0]->total_received))) : "0";
      $response_data['dd_penalty'] = isset($dd[0]->total_penalty) ? (strval(round($dd[0]->total_penalty))) : "0";
      $adv_dd = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',3)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['advance_dd'] = isset($adv_dd[0]->total_adv) ? (strval(round($adv_dd[0]->total_adv))) : "0";
      $response_data['other_charges_dd'] = "0";            

      //neft
      $neft = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',4)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['neft_collection'] = isset($neft[0]->total_received) ? (strval(round($neft[0]->total_received))) : "0";
      $response_data['neft_penalty'] = isset($neft[0]->total_penalty) ? (strval(round($neft[0]->total_penalty))) : "0";
      $adv_neft = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',4)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['advance_neft'] = isset($adv_neft[0]->total_adv) ? (strval(round($adv_neft[0]->total_adv))) : "0";
      $response_data['other_charges_neft'] = "0";

      //card
      $card = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',5)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['card_collection'] = isset($card[0]->total_received) ? (strval(round($card[0]->total_received))) : "0";
      $response_data['card_penalty'] = isset($card[0]->total_penalty) ? (strval(round($card[0]->total_penalty))) : "0";
      $adv_card = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',5)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['advance_card'] = isset($adv_card[0]->total_adv) ? (strval(round($adv_card[0]->total_adv))) : "0";
      
      // B.P.Adjustments
      $bp_adjust = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',6)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['bp_adjust_collection'] = isset($bp_adjust[0]->total_received) ? (strval(round($bp_adjust[0]->total_received))) : "0";
      $response_data['bp_adjust_penalty'] = isset($bp_adjust[0]->total_penalty) ? (strval(round($bp_adjust[0]->total_penalty))) : "0";

      // Advance Adjustments
      $adv_adjustments = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',7)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['adv_adjustment_collection'] = isset($adv_adjustments[0]->total_received) ? (strval(round($adv_adjustments[0]->total_received))) : "0";
      $response_data['adv_adjustment_penalty'] = isset($adv_adjustments[0]->total_penalty) ? (strval(round($adv_adjustments[0]->total_penalty))) : "0";

      //cash_payment
      $cash_payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where('payment_type_id','=',1)->where(array('payment_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['cash_payment'] = isset($cash_payment[0]->total_payment) ? (strval(round($cash_payment[0]->total_payment))) : "0";
      //cheque_payment
      $cheque_payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where('payment_type_id','=',2)->where(array('payment_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['cheque_payment'] = isset($cheque_payment[0]->total_payment) ? (strval(round($cheque_payment[0]->total_payment))) : "0";
      //dd_payment
      $dd_payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where('payment_type_id','=',3)->where(array('payment_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['dd_payment'] = isset($dd_payment[0]->total_payment) ? (strval(round($dd_payment[0]->total_payment))) : "0";
      //neft_payment
      $neft_payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where('payment_type_id','=',4)->where(array('payment_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['neft_payment'] = isset($neft_payment[0]->total_payment) ? (strval(round($neft_payment[0]->total_payment))) : "0";
      //card_payment
      $card_payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where('payment_type_id','=',5)->where(array('payment_date'=>$start_date,'status'=>1))->where($cond)->get();
      $response_data['card_payment'] = isset($card_payment[0]->total_payment) ? (strval(round($card_payment[0]->total_payment))) : "0";

     //other branch cash 
      $response_data['other_branch_cash_collection'] = "0"; 
      $response_data['other_branch_cash_penalty'] =  "0";  
      $response_data['other_branch_cheque_collection'] = "0";
      $response_data['other_branch_cheque_penalty'] = "0";
      $response_data['other_branch_dd_collection'] = "0";
      $response_data['other_branch_dd_penalty'] ="0";
      $response_data['other_branch_neft_collection'] ="0";
      $response_data['other_branch_neft_penalty'] = "0";
      $response_data['other_branch_card_collection'] ="0";
      $response_data['other_branch_card_penalty'] ="0";
          
      $other_branch_cash1 = Receipt::where(array('receipt_date'=>$start_date))->where($cond)->get();
      foreach($other_branch_cash1 as $rcpts) 
      {   
        $chits = ChitDetail::where('id',$rcpts->enrollment_id)->get();
        if($rcpts->employee_branch_id != $chits[0]->branch_id)
        {   
          if($rcpts->payment_type_id == 1 && $rcpts->status ==1)
          {
            $total_receipts_cash = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
            $total_penalty_cash = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
            $response_data['other_branch_cash_collection'] = strval(round($response_data['other_branch_cash_collection'] + $total_receipts_cash));
            $response_data['other_branch_cash_penalty'] = strval(round($response_data['other_branch_cash_penalty'] + $total_penalty_cash));
          }

          if($rcpts->payment_type_id == 2)
          {
            $total_receipts_cheque = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
             $total_penalty_cheque = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
            $response_data['other_branch_cheque_collection'] = strval(round($response_data['other_branch_cheque_collection'] + $total_receipts_cheque));
            $response_data['other_branch_cheque_penalty'] = strval(round($response_data['other_branch_cheque_penalty'] + $total_penalty_cheque ));
          }

          if($rcpts->payment_type_id == 3 && $rcpts->status==1)
          {
            $total_receipts_dd = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
             $total_penalty_dd = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
            $response_data['other_branch_dd_collection'] = strval(round($response_data['other_branch_dd_collection'] + $total_receipts_dd));
            $response_data['other_branch_dd_penalty'] = strval(round($response_data['other_branch_dd_penalty'] + $total_penalty_dd ));
          }  

          if($rcpts->payment_type_id == 4 && $rcpts->status==1)
          {
            $total_receipts_neft = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
             $total_penalty_neft = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
            $response_data['other_branch_neft_collection'] = strval(round($response_data['other_branch_neft_collection'] + $total_receipts_neft));
            $response_data['other_branch_neft_penalty'] = strval(round($response_data['other_branch_neft_penalty'] + $total_penalty_neft ));
          }  

          if($rcpts->payment_type_id == 5 && $rcpts->status==1)
          {
            $total_receipts_card = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
             $total_penalty_card = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
            $response_data['other_branch_card_collection'] = strval(round($response_data['other_branch_card_collection'] + $total_receipts_card));
            $response_data['other_branch_card_penalty'] = strval(round($response_data['other_branch_card_penalty'] + $total_penalty_card ));
          }                     
        }
      } 

      // Total Amount
      $response_data['total_cash'] = strval(round($response_data['cash_collection']+$response_data['cash_penalty']+$response_data['other_charges_cash']+$response_data['advance_cash']));

      $response_data['total_cheque_left'] = strval(round($response_data['cheque_received_collection']+$response_data['cheque_penalty']+$response_data['other_charges_cheque']+$response_data['advance_cheque']));

      $response_data['total_cheque_right'] = strval(round($response_data['cheque_cleared_collection']+$response_data['cheque_cleared_penalty']+$response_data['advance_cleared_cheque']));

      $response_data['total_dd'] = strval(round($response_data['dd_collection']+$response_data['dd_penalty']+$response_data['other_charges_dd']+$response_data['advance_dd']));

      $response_data['total_neft'] = strval(round($response_data['neft_collection']+$response_data['neft_penalty']+$response_data['other_charges_neft']+$response_data['advance_neft']));

      $response_data['total_card'] = strval(round($response_data['card_collection']+$response_data['card_penalty']+$response_data['advance_card']));

      $response_data['total_payment'] = strval(round($response_data['cash_payment']+$response_data['cheque_payment']+$response_data['dd_payment']+$response_data['neft_payment'] +$response_data['card_payment']));     

      $response_data['total_adjustment'] = strval(round($response_data['bp_adjust_penalty']+$response_data['bp_adjust_collection']+$response_data['adv_adjustment_collection']+$response_data['adv_adjustment_penalty']));

      $response['status'] = "Success";
      $response['msg'] = "";
      $response['data'] = $response_data;
      return response()->json($response,200);
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['exep'] = $e;
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  Public function commitment_payment_details(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $chit_details = ChitDetail::where('customer_id',$request->customer_id)->where('chit_type',3)->get();
      $response_data=[];
      $response_data['customer_name'] = isset($chit_details[0]->customer_det) ? ($chit_details[0]->customer_det->name) : "";
      $response_data['customer_code'] = isset($chit_details[0]->customer_det) ? ($chit_details[0]->customer_det->customer_code) : "0";
      $response_data['phone_no'] = isset($chit_details[0]->customer_det) ? ($chit_details[0]->customer_det->phone_no) : "0";
      $response_data['mobile_no'] = isset($chit_details[0]->customer_det) ? ($chit_details[0]->customer_det->mobile_no) : "0";
      $response_data['profile_photo']= isset($chit_details[0]->customer_det) ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$chit_details[0]->customer_det->photo) : ''; 
      $group_details = [];     
      foreach($chit_details as $chit)
      {
        $chit_det = [];
        $slab_types = SlabDue::where('id',$chit->slab_id)->get();   
        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
        $chit_det['group_name'] = "Commitment"."-".$slab_name;  
        $chit_det['ticket_no'] = "-"; 
        $chit_det['chit_value'] = isset($slab_types[0]->scheme_det) ?  (strval(round($slab_types[0]->scheme_det->chit_value))) : "0";
        $chit_det['payment_paid'] = isset($chit->payment_released) ? (strval(round($chit->payment_released))) : "0";
        $chit_det['payment_pending'] = isset($chit->pending_released) ? (strval(round($chit->pending_released))) : "0";        
        $chit_det['total_payment'] = isset($chit->total_payment_release) ? (strval(round($chit->total_payment_release))) : "0";
        $chit_det['payment_status'] = "Payment Pending"; 

        if($chit->payment_released !=0 && $chit->pending_released !=0)
        {
         $payment = Payment::where('enrollment_id',$chit->id)->where('payment_for',7)->get();
         $counts = $payment->count();
          if($counts >= 1)
          {
            $chit_det['payment_status'] = "Part Payment";
          }
        } 
        if($chit->payment_released ==0 && $chit->pending_released !=0)
        {         
          $chit_det['payment_status'] = "Payment Pending";         
        }         
        if($chit->payment_released !=0 && $chit->pending_released ==0)
        {
          $payment = Payment::where('enrollment_id',$chit->id)->where('payment_for',7)->get();
          $counts = $payment->count();
          if($counts == 1)
          { 
            $chit_det['payment_status'] = "Full Payment";
          }     
          if($counts > 1)
          {
             $chit_det['payment_status'] = "Part Payment";
          }       
        }      
        array_push($group_details,$chit_det);
      }
      $response_data['group_details'] = $group_details;
      if($chit_details) 
      {
        return response()->json($response_data, 200);
      }
      else
      {
        return response()->json($response_data, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  Public function collection_agent_details(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'branch_id'=>$request->branch_id);
      $collection = Employee::where($cond)->where('employee_type','=',1)->get();
      $response_data = [];
      foreach($collection as $collect)
      {
        $emp_det = [];
        $emp_det['employee_id'] = isset($collect->id) ? (strval($collect->id)) : "0";
        $emp_det['employee_name'] = isset($collect->first_name) ? ($collect->first_name.' '.$collect->last_name) : "";
        array_push($response_data,$emp_det);
      }
      if(count($collection)>0)
      {
        return response()->json($response_data, 200);
      }
      else
      {
        return response()->json($response_data, 200);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  public function feedback_reports(Request $request)
{
    try {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $cond = [];

        if (!empty($request->tenant_id)) {
            $cond['tenant_id'] = $request->tenant_id;
        }
        if (!empty($request->branch_id)) {
            $cond['branch_id'] = $request->branch_id;
        }
        if (!empty($request->created_by)) {
            $cond['created_by'] = $request->created_by;
        }
        if (!empty($request->remark_date)) {
            $cond['remark_date'] = $request->remark_date;
        }

        // ✅ Eager load relation to avoid N+1 query
        $remarks = Remark::with(['customer_det', 'branch_det'])
            ->where($cond)
            ->get();

        $response_data = [];

        foreach ($remarks as $feedback) {

            $feedback_det = [];

            // ✅ Safe null check
            $feedback_det['customer_name'] = $feedback->customer_det->name ?? '';
            $feedback_det['customer_id']   = $feedback->customer_id ? (string)$feedback->customer_id : "0";
            $feedback_det['next_followup_date'] = $feedback->next_followup_date ?? "0000-00-00";
            $feedback_det['remark_date'] = $feedback->remark_date ?? "0000-00-00";
            $feedback_det['remarks'] = $feedback->remarks ?? '';
            $feedback_det['branch_name'] = optional($feedback->branch_det)->branch_name ?? '';

            $response_data[] = $feedback_det;
        }

        return response()->json($response_data, 200);

    } catch (\Exception $e) {

        // ✅ Log real error for debugging
        \Log::error("feedback_reports error: " . $e->getMessage());

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()   // temporarily show actual error
        ], 500);
    }
}


  public function store_collection_remarks(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
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

  public function mobile_get_employee_attendance(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('tenant_id'=>$request->tenant_id,'branch_id'=>$request->branch_id,'att_date'=>$request->attendance_date,'employee_id'=>$request->employee_id);
      $emp_att = EmployeeAttendance::where($cond)->get(); 
      if(count($emp_att)>0)
      {
        $att_entries = '1';
      }
      else
      {
        $att_entries = '0';
      }
      $response['status']="Success";
      $response['msg']= $att_entries;
      return response()->json($response,200);
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }

  Public function mobile_store_employee_attendance(Request $request)
  {
     DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
    $validator = Validator::make($request->all(), [
          'tenant_id' => 'required',
          'branch_id' => 'required',
          'att_date'  => 'required',
    ]);
    if ($validator->fails()) {  
        $response['status'] = "Error";
        $response['msg'] = $validator->errors()->first();
        return response()->json($response, 200);
    }
    else {  
      try {

        $emp_attendance_date = isset($request->att_date) ? ($request->att_date) : "";     
        $emp_id = isset($request->employee_id) ? ($request->employee_id) : "0";
        $is_attendance_exist = EmployeeAttendance::where(array('branch_id'=>$request->branch_id,'att_date'=>$emp_attendance_date,'employee_id'=>$emp_id))->get(); 
        if(count($is_attendance_exist)>0) {   
          $response['status'] = 'Error';
          $response['msg'] = \Lang::get('api.attendance_already_exist');
          return response()->json($response,200);
        }
        else {    
          $emp_attendance_det = new EmployeeAttendance();
          $emp_attendance_det['tenant_id'] = isset($request->tenant_id) ? ($request->tenant_id) : 0;
          $emp_attendance_det['branch_id']  = isset($request->branch_id) ? ($request->branch_id) : 0;                    
          $emp_attendance_det['att_date'] = $emp_attendance_date;
          $emp_attendance_det['employee_id'] = isset($request->employee_id) ? ($request->employee_id) : 0;
          $emp_attendance_det['attendance_type'] = isset($request->attendance_type) ? ($request->attendance_type) : 0;
          $emp_attendance_det['leave_type'] = isset($request->leave_type) ? ($request->leave_type) : 0;
          $emp_attendance_det['afternoon_session'] = isset($request->afternoon_session) ? ($request->afternoon_session) : 0;
          $emp_attendance_det['forenoon_session'] = isset($request->forenoon_session) ? ($request->forenoon_session) : 0;
          $emp_attendance_det['latitude'] = isset($request->latitude) ? ($request->latitude) : '';
          $emp_attendance_det['longitude'] = isset($request->longitude) ? ($request->longitude) : '';
          $emp_attendance_det['remarks'] = isset($request->remarks) ? ($request->remarks) : '';
          $emp_attendance_det['created_by']  = isset($request->created_by) ? ($request->created_by) : 0; 
          $emp_attendance_det['status'] = isset($request->status) ? ($request->status) : 1;              
          if($emp_attendance_det->save())
          {
            $response['status'] = 'Success';
            $response['msg'] = \Lang::get('api.success_employee_attendance_added');
            $response['data'] = $emp_attendance_det;
            return response()->json($response,200);                 
          }   
        }
      }
      catch(\Exception $e) { 
        $response['status'] = 'Error';
        $response['exep'] = $e;
        $response['msg'] = \Lang::get('api.global_error');
        return response()->json($response,401);
      }
    }
  }


  public function mobile_list_states(Request $request) {
        try { 
          DB::purge('mysql');
          Config::set('database.connections.mysql.database', $request->db);
          DB::reconnect('mysql');
            $states = State::all();
            $i=1;
            foreach($states as $state) {
                $state->sno = $i;
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


    public function list_schemes(Request $request) {
      DB::purge('mysql');
          Config::set('database.connections.mysql.database', $request->db);
          DB::reconnect('mysql');
        try { 
            if(isset($request->tenant_id)) {
                $schemes = Scheme::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $schemes = Scheme::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($schemes as $scheme) {
                $scheme->sno = $i;
                $scheme->chit_value = isset($scheme->chit_value) ? round($scheme->chit_value) : '';
                $scheme->scheme_format = isset($scheme->chit_value) ? (round($scheme->chit_value).'('.$scheme->no_of_months.' M)') : '';
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $schemes;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }  


    public function store_customer_advance_receipt(Request $request)
    {
      DB::purge('mysql');
          Config::set('database.connections.mysql.database', $request->db);
          DB::reconnect('mysql');
            try { 
			 
              $receipt_no = ''; 
                $othercharge=new BeforeEnrolReceipt(); 
          
                $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 30;
                $othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
      
                $othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				
                $othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
               
                $othercharge->received_date = date('Y-m-d');
                
                $othercharge->amount = isset($request->amount) ? ($request->amount) : 0 ;
                $othercharge->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                $othercharge->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                $othercharge->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
        if(isset($request->cheque_date)) {
          $f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
          $othercharge->cheque_date  = date('Y-m-d',strtotime($f_date)); 
        }
        else {
          // $othercharge->cheque_date  = '0000-00-00';
        }
		 
                $othercharge->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                $othercharge->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
        if(isset($request->transaction_date)) {
          $f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
          $othercharge->transaction_date  = date('Y-m-d',strtotime($f_date)); 
        }
        else {
          // $othercharge->transaction_date  = '0000-00-00';
        }
                $othercharge->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
                $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $othercharge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $othercharge->status = isset($request->status) ? ($request->status) : 1;
        if($request->payment_type_id == 2){
              $othercharge->status = isset($request->status) ? ($request->status) : 2;
            }
            // print_r("expression");exit;
                if($othercharge->amount > 0)
                {
                  if($othercharge->save()){
                    if($receipt_no=='') {
                      //sleep(10);
                      $receipt_no = 'BADV-'.sprintf('%05d', $othercharge->id);
                    }     
                    $othercharge->receipt_no = $receipt_no;
                    $othercharge->save();
					
				$amount = 	$request->amount;
				$today_date = date('Y-m-d');
          
           $msg = "Dear Customer, Payment of $amount is Received, Receipt No $receipt_no on $today_date to Chit group New Enrollment, Thank you. More Details Call: 88079 06006 - SPN Chits Pvt Ltd";


	$msg1=$msg;
		//$receiver = '7418470181';
		$receiver = $othercharge->customer_det->mobile_no;
		$msg_split = str_split($msg,456);
		
		  
				
            	$ch = curl_init("https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=$msg");
            
            	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				  $result = curl_exec($ch);
				curl_close($ch);
       
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_before_enrol_receipt_added_successfully');
                        $branchId = $othercharge->branch_id;
                        if (!$branchId && $othercharge->employee_id) {
                            $branchId = Employee::where('id', $othercharge->employee_id)->value('branch_id');
                        }
                        $receiptData = $othercharge->toArray();
                        $receiptData['client_address'] = $this->resolveBranchClientAddress($branchId);
                        $response['data'] = $receiptData;
                        $response['receipt_no'] = $othercharge->receipt_no;
                        return response()->json($response,200);  
                    }
                }     
                else 
                {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.other_charges_amount_invalid');
                    return response()->json($response,200);
                }             
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        
  } 
  
  public function change_password_customer(Request $request)
	{		
		try { 
			if(isset($request->cust_primary_id)) { 
				
				DB::purge('mysql');
				Config::set('database.connections.mysql.database', $request->db);
				DB::reconnect('mysql');
				$customer_tables = Customer::find($request->cust_primary_id);
				$db_password = $customer_tables->password;
                $old_password = isset($request->old_password) ? ($request->old_password) : ''; 
                $new_password = isset($request->new_password) ? ($request->new_password) : ''; 
                $confirm_password = isset($request->confirm_password) ? ($request->confirm_password) : '';    
				
				if(Hash::check($old_password, $db_password)) {
				if($confirm_password == $new_password){
					$customer_tables->password = Hash::make($new_password); 
					if($customer_tables->save()) {
					$response['status']="Success";
					$response['msg']=\Lang::get('api.password_changed');
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
                    $response['msg']=\Lang::get('api.confirm_password_not_match');
                    return response()->json($response,200);
			   }
			   } else {
					$response['status']="Error";
                    $response['msg']=\Lang::get('api.old_password_not_match');
                    return response()->json($response,200);
				}
		    }
		}
		catch(\Exception $e){
			$response['status']="Error";
	        $response['msg'] = \Lang::get('api.global_error');
			return response()->json($response,401);
		}
	}
	
	public function customer_forget_password_otp(Request $request)
  {
    try
    {
      
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('customer_code'=>$request->user_id);
      $customer_table = Customer::where($cond)->orWhere('mobile_no','=',$request->user_id)->get();
      
      if(count($customer_table)>0)
      { 
        
          $otp=mt_rand(1000,9999);
          if($customer_table[0]->email_id != '' || $customer_table[0]->email_id != 0)
          {
            $emp_email = isset($customer_table[0]->email_id) ? ($customer_table[0]->email_id) : '';
          }
          else
          {
            $emp_email = 'androiddeveloper@mazenetsolution.com';
          }
		  
          $employee_name = isset($customer_table[0]->name) ? ($customer_table[0]->name) : '';
          $mobile_no = isset($customer_table[0]->mobile_no) ? ($customer_table[0]->mobile_no) : '';
          $tenant_id = $customer_table[0]->tenant_id;
          $data['otp'] = $otp;
          $data['emp_name'] = $employee_name;
          if($tenant_id == 13){
            Mail::send(['text'=>'mail'],$data, function($message) 
            use($emp_email,$employee_name){
              $message->to($emp_email,$employee_name)->subject('OTP Mail');
              $message->from('info@mazenettech.com','Mazenet');
              $message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
			  $message->cc('manoj_p@mazenetsolution.com','Manoj');
              $message->cc('bde@mazenettech.com','Subha');
              $message->cc('kmfchit@gmail.com','KMFCHITS');
            });

            $msg = "$employee_name this is your OTP: $otp For more details call helpline no 080-456-91389 - KMFCHITS";

                $msg1=$msg;
                  $receiver = $mobile_no;
                  $apikey = "bXCZUkND6vy4PXqE";
                  $template_id = "1707163644723115429";
                  $sender_id = "KMFCHT";
                  $msg_split = str_split($msg,456);
                  for($i=0;$i<sizeof($msg_split);$i++)
                  {
                      
                      $msg_temp =  urlencode( $msg_split[$i] );
                            $ch = curl_init("http://textsms.arssoftech.com/vb/apikey.php?apikey=$apikey&senderid=$sender_id&templateid=$template_id&number=$receiver&message=$msg_temp");
                          
                            
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($ch, CURLOPT_HEADER, 0);
                        $result = curl_exec($ch);
                      curl_close($ch);
                  }

          } else if($tenant_id == 37){
		  
          Mail::send(['text'=>'mail'],$data, function($message) 
          use($emp_email,$employee_name){
            $message->to($emp_email,$employee_name)->subject('OTP Mail');
            $message->from('info@mazenettech.com','Mazenet');
            $message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
            $message->cc('manoj_p@mazenetsolution.com','Suhail');
            $message->cc('bde@mazenettech.com','Suba');
            $message->cc('info@sbma.in','SBMA');
            
            /*if($customer_table[0]->tenant_id == 13){
              $message->cc('kmfchit@gmail.com','KMFCHITS');
            }*/
          });
		  
		  $receiver = '91' . $mobile_no;
		 $name = $customer_table[0]->name;
		 
                                    $msg = "$otp is your One Time Password. Do not share this OTP with anyone. Regards - SBMA CHITS FUNDS PVT LTD";
									
                                    
                                    $msg1=$msg;
                                    $msg_split = str_split($msg,456);
                                    for($i=0;$i<sizeof($msg_split);$i++){
                                        $msg_temp =  urlencode( $msg_split[$i] );
                                        $ch = curl_init("http://smsserver9.creativepoint.in/api.php?username=sbmachit&password=700428&to=$receiver&from=SBMACF&message=$msg_temp&PEID=1501469990000038722&templateid=1507165132030662644");
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_HEADER, 0);
                                            $result = curl_exec($ch);
                                        curl_close($ch);
                                    }
		  
		       return json_encode(array('status'=>'Success','msg'=>'','customer_primary_id'=>strval($customer_table[0]->id),'otp'=>strval($otp),'customer_name'=>$customer_table[0]->name));
          }	 else if($tenantid == 57){
						
                        $receiver = '917418470181';
						
                  $msg = "Dear Subscriber, Your OTP for login is $otp. We value your relationship with Sri Vannamayil Chits, Thank U - Sri Vannamayil Chits Pvt Ltd";
                        
                        $msg_temp =  urlencode($msg);
                                   $ch = curl_init("https://messageapi.in/chat/sendmessage?id=df13ababdef74f8aa331a732bc14e3cb&phone=$receiver&message=$msg_temp");
								   
                                    
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HEADER, 0);
                            $result = curl_exec($ch);
                            curl_close($ch);
						
                        
                    }
          else{
              Mail::send(['text'=>'mail'],$data, function($message) 
          use($emp_email,$employee_name){
            $message->to($emp_email,$employee_name)->subject('OTP Mail');
            $message->from('info@mazenettech.com','Mazenet');
            $message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
            $message->cc('manoj_p@mazenetsolution.com','Suhail');
            $message->cc('bde@mazenettech.com','Subha');
            /*if($customer_table[0]->tenant_id == 13){
              $message->cc('kmfchit@gmail.com','KMFCHITS');
            }*/
          });
          }
          
            return json_encode(array('status'=>'Success','msg'=>'','customer_primary_id'=>strval($customer_table[0]->id),'otp'=>strval($otp),'customer_name'=>$customer_table[0]->name));
          
        
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['statuse'] = $e;
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }
  
  
  public function customer_register(Request $request)
  {
    try
    {
      
      DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
	  
		$leadmanagement=new LeadManagement(); 
		$leadmanagement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
		$leadmanagement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
		$leadmanagement->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
		$leadmanagement->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
		$leadmanagement->email_id = isset($request->email_id) ? ($request->email_id) : '';
		$leadmanagement->created_by = isset($request->created_by) ? ($request->created_by) : 1;
		$leadmanagement->status = isset($request->status) ? ($request->status) : 1;
		
		 
		  
		  if($leadmanagement->save()){
		  $customer_table = LeadManagement::Where('mobile_no','=',$request->mobile_no)->get();
		  $customer_table_mob = Customer::Where('mobile_no','=',$request->mobile_no)->get();
		  
		 
			if($customer_table[0]->user_enabled == 0)
			{
			  $otp=mt_rand(1000,9999);
			  if($customer_table[0]->email_id != '' && $customer_table[0]->email_id != 0)
			  {
				$emp_email = isset($customer_table[0]->email_id) ? ($customer_table[0]->email_id) : '';
			  }
			  else
			  {
				$emp_email = 'androiddeveloper@mazenetsolution.com';
			  }
			  $employee_name = isset($customer_table[0]->lead_customer_name) ? ($customer_table[0]->lead_customer_name) : '';
			  $mobile_no = isset($customer_table[0]->mobile_no) ? ($customer_table[0]->mobile_no) : '';
			  $tenant_id = $customer_table[0]->tenant_id;
			  $data['otp'] = $otp;
			  $data['emp_name'] = $employee_name;
			  if($tenant_id == 13){
				Mail::send(['text'=>'mail'],$data, function($message) 
				use($emp_email,$employee_name){
				  $message->to($emp_email,$employee_name)->subject('OTP Mail');
				  $message->from('info@mazenettech.com','Mazenet');
				  $message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
				  $message->cc('manoj_p@mazenetsolution.com','Suhail');
				  $message->cc('bde@mazenettech.com','Subha');
				  $message->cc('kmfchit@gmail.com','KMFCHITS');
				});

				$msg = "$employee_name this is your OTP: $otp For more details call helpline no 080-456-91389 - KMFCHITS";

					$msg1=$msg;
					  $receiver = $mobile_no;
					  $apikey = "bXCZUkND6vy4PXqE";
					  $template_id = "1707163644723115429";
					  $sender_id = "KMFCHT";
					  $msg_split = str_split($msg,456);
					  // for($i=0;$i<sizeof($msg_split);$i++)
					  // {
						  
						  // $msg_temp =  urlencode( $msg_split[$i] );
								// $ch = curl_init("http://textsms.arssoftech.com/vb/apikey.php?apikey=$apikey&senderid=$sender_id&templateid=$template_id&number=$receiver&message=$msg_temp");
							  
								
						  // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						  // curl_setopt($ch, CURLOPT_HEADER, 0);
							// $result = curl_exec($ch);
						  // curl_close($ch);
					  // }

			  }
			  else{
				  Mail::send(['text'=>'mail'],$data, function($message) 
			  use($emp_email,$employee_name){
				$message->to($emp_email,$employee_name)->subject('OTP Mail');
				$message->from('info@mazenettech.com','Mazenet');
				$message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
				$message->cc('manoj_p@mazenetsolution.com','Suhail');
				$message->cc('bde@mazenettech.com','Subha');
				/*if($customer_table[0]->tenant_id == 13){
				  $message->cc('kmfchit@gmail.com','KMFCHITS');
				}*/
			  });
			  }
			  
				return json_encode(array('status'=>'Success','msg'=>'','customer_primary_id'=>strval($customer_table[0]->id),'otp'=>strval($otp),'customer_name'=>$customer_table[0]->lead_customer_name));
			  
			}
			else
			{
			  $response['status'] = 'Signin';
			  $response['msg'] = "You Already Logged In Enter Password";
			  return response()->json($response, 200);
			}
		  
		  
		}
	}
		catch(\Exception $e)
		{
		  $response['status'] = 'Error';
		  $response['statuse'] = $e;
		  $response['msg'] = \Lang::get('api.global_error');
		  return response()->json($response, 401);
		}
	  }
      
	  
  public function customer_update_register_password(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('id'=>$request->cust_primary_id);
      $customer_tables = LeadManagement::where($cond)->get();
      $customer_tables[0]->password = Hash::make($request->password); 
      $customer_tables[0]->user_enabled = 1;    
      if($customer_tables[0]->save())
      {
        $response['status'] = 'Success';
        $response['msg'] = '';
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }
  
   public function update_lead_profile(Request $request)
  {
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      $cond = array('id'=>$request->cust_primary_id);
      $customer_tables = LeadManagement::where($cond)->get();
      $customer_tables[0]->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
      $customer_tables[0]->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
      $customer_tables[0]->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
      $customer_tables[0]->email_id = isset($request->email_id) ? ($request->email_id) : '';
      $customer_tables[0]->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
      $customer_tables[0]->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
      $customer_tables[0]->landmark = isset($request->landmark) ? ($request->landmark) : '';
      $customer_tables[0]->state_id = isset($request->state_id) ? ($request->state_id) : '';
      $customer_tables[0]->district_id = isset($request->district_id) ? ($request->district_id) : '';
      $customer_tables[0]->city_id = isset($request->city_id) ? ($request->city_id) : '';
      $customer_tables[0]->pincode = isset($request->pincode) ? ($request->pincode) : '';
      $customer_tables[0]->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : '';
      if($customer_tables[0]->save())
      {
        $response['status'] = 'Success';
        $response['msg'] = '';
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }
  
  
  public function customer_signup_otp_kmf(Request $request)
  {
    try
    {
      
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
      
        
          $otp=mt_rand(1000,9999);
		
          // if($customer_table[0]->email_id != '' && $customer_table[0]->email_id != 0)
          // {
            $emp_email = 'androiddeveloper@mazenetsolution.com';
          // }
          // else
          // {
            // $emp_email = 'androiddeveloper@mazenetsolution.com';
          // }
		  
          $employee_name = 'Jeeva';
         
          $data['otp'] = $otp;
          $data['emp_name'] = $employee_name;
		  
          
            Mail::send(['text'=>'mail'],$data, function($message) 
            use($emp_email,$employee_name){
              $message->to($emp_email,$employee_name)->subject('OTP Mail');
              $message->from('info@mazenettech.com','Mazenet');
              $message->cc('androiddeveloper@mazenetsolution.com','Jeeva');
              $message->cc('manoj_p@mazenetsolution.com','Jeeva');
              $message->cc('bde@mazenettech.com','Subha');
            });

            // $msg = "$employee_name this is your OTP: $otp For more details call helpline no 080-456-91389 - KMFCHITS";

                // $msg1=$msg;
                  // $receiver = $mobile_no;
                  // $apikey = "bXCZUkND6vy4PXqE";
                  // $template_id = "1707163644723115429";
                  // $sender_id = "KMFCHT";
                  // $msg_split = str_split($msg,456);
                  // for($i=0;$i<sizeof($msg_split);$i++)
                  // {
                      
                      // $msg_temp =  urlencode( $msg_split[$i] );
                            // $ch = curl_init("http://textsms.arssoftech.com/vb/apikey.php?apikey=$apikey&senderid=$sender_id&templateid=$template_id&number=$receiver&message=$msg_temp");
                          
                            
                      // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                      // curl_setopt($ch, CURLOPT_HEADER, 0);
                        // $result = curl_exec($ch);
                      // curl_close($ch);
                  // }
				  
				   $response['status'] = 'Success';
                   $response['otp'] = strval($otp);
                   $response['msg'] = '';
                   return response()->json($response, 200);
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['statuse'] = $e;
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }
  
  public function customer_signin_kmf(Request $request)
  {
	   
    try
    {
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
	 
	  //if customer is original customer
      $cond = array('customer_code'=>$request->user_id);
      $customer = Customer::where($cond)->orWhere('mobile_no','=',$request->user_id)->get();
	  
	  //if customer is lead
	  $customer_lead = LeadManagement::Where('mobile_no','=',$request->user_id)->get();
      
      if(count($customer)>0)
      {
		
        $cond1 = array('customer_code'=>$customer[0]->customer_code);
        $approved_status = Customer::where($cond1)->where('user_enabled','!=',0)->get();
        if(count($approved_status)>0)
        {
         
            return response()->json([
              'status'=>'Success',
              'msg'=>'',
              'customer_primary_id'=>strval($customer[0]->id),
              'customer_code'=>strval($customer[0]->customer_code),
              'customer_name'=>$customer[0]->name,
              'tenant_id'=>strval($customer[0]->tenant_id),
              'tenant_name'=>strval($customer[0]->tenant_det->chit_fund_name),
              'tenant_address_1'=>strval($customer[0]->branch_det->address_line_1),
              'tenant_address_2'=>strval($customer[0]->branch_det->address_line_2),
              'state_name'=>strval($customer[0]->branch_det->state_det->state_name),
              'district_name'=>strval($customer[0]->branch_det->district_det->district_name),
              // 'city_name'=>strval($customer[0]->branch_det->city_det->city_name),
              'pincode'=>strval($customer[0]->branch_det->pincode),
              'mobile_no'=>strval($customer[0]->branch_det->mobile_no),
              'phone_no'=>strval($customer[0]->branch_det->phone_no),
              'branch_id'=>strval($customer[0]->branch_id),
              'branch_name'=>strval($customer[0]->branch_det->branch_name),
              'profile_photo'=> ($customer[0]->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer[0]->photo) : '',
			  'cus_type'=>strval(1),
            ]);
			 
          
        }
        else
        {
          $response['status'] = 'Signup';
          $response['msg'] = "Signup Now";
          return response()->json($response, 401);
        } 
      } else {
		 
            return response()->json([
              'status'=>'Success',
              'msg'=>'',
              'customer_primary_id'=>0,
              'customer_code'=>0,
              'customer_name'=>'user',
              'tenant_id'=>13,
              'tenant_name'=>0,
              'tenant_address_1'=>0,
              'tenant_address_2'=>0,
              'state_name'=>0,
              'district_name'=>0,
              // 'city_name'=>strval($customer[0]->branch_det->city_det->city_name),
              'pincode'=>0,
              'mobile_no'=>0,
              'phone_no'=>0,
              'branch_id'=>0,
              'branch_name'=>0,
              'profile_photo'=>0,
              'cus_type'=>strval(2),
            ]);
			
          
        
	  }
      
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  } 
  
  public function mobile_store_lead_management_kmf(Request $request)
  {
     $validator=Validator::make($request->all(),[
      'lead_customer_name'=>'required',
    ]); 
    if($validator->fails()){
      $response['status']="Error";
      $response['msg']=$validator->error()->first();
      return response()->json($response,401);
    }
    else {
      try { 
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');
		
        $leadmanagement=new LeadManagement(); 
        $leadmanagement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 13;
        $leadmanagement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 1;
        $leadmanagement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 1;
        $leadmanagement->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
         $leadmanagement->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
        $leadmanagement->email_id = isset($request->email_id) ? ($request->email_id) : '';
        $leadmanagement->created_by = isset($request->created_by) ? ($request->created_by) : 1;
        $leadmanagement->status = isset($request->status) ? ($request->status) : 1;
        $leadmanagement->followup_status_type_id = isset($request->followup_status_type_id) ? ($request->followup_status_type_id) : 2; 
        $leadmanagement->save();
          
        $response['status'] = "Success";
        $response['msg'] = '';
        return response()->json($response,200);            
      } 
      catch(\Exception $e)
      {
        $response['status'] = "Error";
        $response['msg'] = '' ;
        return response()->json($response,401);
      }
    } 
  }
  
  public function mobile_get_collection_area_customers_vvrs(Request $request) 
  {
    
	try {
           DB::purge('mysql');
		   Config::set('database.connections.mysql.database', $request->db);
		   DB::reconnect('mysql');
		   
		   $cond=array('employee_id'=>$request->employee_id,'tenant_id'=>$request->tenant_id);
      $collectionareamapping=CollectionAreaMapping::where($cond)->groupBy('collection_area_id')->get();
      $response_data = []; 
      $cust_id = [];
     
           $customers = DB::table('customers')->select('name','id','mobile_no','customer_code')->where('is_customer','=',1)->get();
           $response_data = []; 
            $i=1;
            foreach($customers as $customer) {
              
                $entrollment_det['customer_name'] = $customer->name;
				//$customer->name = $customer->name;
                $entrollment_det['mobile_no'] = $customer->mobile_no;
                $entrollment_det['customer_id'] = $customer->id;
                $entrollment_det['customer_code'] = $customer->customer_code;
                
                $entrollment_det['phone_no'] = '0';
                $entrollment_det['total_installment'] = '0';
				if($customer->mobile_no == ' '){
				$entrollment_det['total_paid']= '0';		
				} else if($customer->mobile_no == ''){
				$entrollment_det['total_paid']= '0';		
				}else {
				$entrollment_det['total_paid']= '0';
				}
				
				if($customer->customer_code == ' '){
				$entrollment_det['total_pending'] = '0';	
				} else if($customer->customer_code == ''){
				$entrollment_det['total_pending'] = '0';	
				}else {
				$entrollment_det['total_pending'] = '0';
				}
                 array_push($response_data,$entrollment_det); 
                $i++;
               
           
		  }
		   
		
           return response()->json($response_data,200);  
        }
		
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuss'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
	
  }
  
   public function get_charge_types_mobile(Request $request) {
        try {
			
            DB::purge('mysql');
		   Config::set('database.connections.mysql.database', $request->db);
		   DB::reconnect('mysql');
			
            
            $list_charge_types = ChargeType::orderBy('id','desc')->get();
            foreach($list_charge_types as $charge_type) {
                $charge_type->tenant_info = isset($charge_type->tenant_det) ? ($charge_type->tenant_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $list_charge_types;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 Public function auction_wise_to_be_collected_mobile(Request $request)
    {
    	try
    	{	// id
			DB::purge('mysql');
		   Config::set('database.connections.mysql.database', $request->db);
		   DB::reconnect('mysql');
		   
    		$cond = $request->conditions;
    		$enrollment_id = $request->enrollment_id;
    		$chits = ChitDetail::where('id',$request->enrollment_id)->get();
    		if(count($chits)>0)
    		{	
    			$response_data =[];
    			foreach($chits as $cd)
    			{
    				$enrollment_id = $cd->id;
			        $group_id = $cd->group_id;
			        $customer_id = $cd->customer_id;
					$ticket_type = $cd->ticket_type;
			        if($cd->chit_type != 3)
			        {
						$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
						
						//total_installment_amount
						$total_installment_amount_ovr = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
						
						$total_installment_amount =($total_installment_amount_ovr * $ticket_type);
						
						$cd->overall_installment_amount = $total_installment_amount;
						//paid_amount
						$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
						$paid_rec_amount = $receipt[0]->total_paid;
						$bonus_amount = $receipt[0]->bonus_amount;
						$paid_amount = $paid_rec_amount + $bonus_amount;
						$cd->overall_paid_amount = $paid_amount;
						//pending_amount 
						$pending_amount = $total_installment_amount - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						
						//last auction details
						$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
						$current_inst_amt = BiddingDetail::where('id','=',$max_bid_id)->pluck('current_installment_amount');
						//no installment paid 
						$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
					   //auction wise details 
						$auc_wise_inst_amts = BiddingDetail::selectRaw('*,sum(current_installment_amount) as current_installment_amount')->where('group_id','=',$group_id)->groupBy('installment_no')->get();
						$i=0;
						$install_det = [];					
						$total_penalty_amounts = 0;
						$total_bonus_amounts = 0;
						foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
							$cust_det = [];		
							$penalty = 0;					
							$bonus = 0;					
							$inst_amt_ovr = $auc_wise_inst_amt->current_installment_amount;
							$inst_amt = ($inst_amt_ovr * $ticket_type);
							$auction_id = $auc_wise_inst_amt->id;
							$auc_id  = $auc_wise_inst_amt->id;
							$installment_no  = $auc_wise_inst_amt->installment_no;
							$auc_date_inst  = $auc_wise_inst_amt->auction_date;
		                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'installment_no' => $installment_no, 'status' => 1])->get();
							$max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
							$last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date');
							if(empty($cus_rec_amt[0])) { 
								$amount_paid = 0;
							} 
							else {
							  	$amount_paid_rec = $cus_rec_amt[0]->total_paid;
							  	$amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
							  	$amount_paid = $amount_paid_rec + $amount_paid_bonus;
							}
							$ins_amt_wise_rec = $inst_amt - $amount_paid;	
							if($ins_amt_wise_rec !=$inst_amt && $i==0 && $inst_amt_no !=0) {
								$receipt_date = $last_receipt_date_inst[0];
								$current_date = date('Y-m-d');
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
								$i++;
							} 
							else {
								$receipt_date = $auc_date_inst;
								$current_date = date('Y-m-d');
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
							}
							
							// Bonus days and percentage by heirrachy over all 
							if($cd->customer_det->bonus_days!=0 && $cd->customer_det->customer_bonus!=0)
							{
							  	$bonus_days = $cd->customer_det->bonus_days;
							  	$bonus_percentage = $cd->customer_det->customer_bonus;
							}    
							elseif($cd->group_det->group_bonus_days!=0 && $cd->group_det->group_base_bonus!=0)
							{
							  	$bonus_days = $cd->group_det->group_bonus_days;
							  	$bonus_percentage = $cd->group_det->group_base_bonus;
							}
							elseif($cd->branch_det->bonus_days!=0 && $cd->branch_det->bonus!=0)
							{
							  	$bonus_days = $cd->branch_det->bonus_days;
							  	$bonus_percentage = $cd->branch_det->bonus;
							}    
							else
							{
							  	$bonus_days = 0;
							  	$bonus_percentage = 0;
							} 	
							if($pending_days <=  $bonus_days)
							{ 
							  	if($ins_amt_wise_rec==$inst_amt)
							  	{ 
									$bonus_calculation = $inst_amt * $bonus_percentage / 100 ; 
									$bonus = strval(round($bonus_calculation));
									$cust_det['bonus'] = $bonus;
							  	}
							  	else
							  	{
									$cust_det['bonus'] = 0;
							  	}
							}
							else
							{
							  	$cust_det['bonus'] = 0;
							}
							
							// Penalty Calculation installment wise
			                if($cd->customer_det->penalty_days!=0 && $cd->customer_det->customer_penalty_interest!=0)
			                {
			                  	$inst_penalty_days = $cd->customer_det->penalty_days;
			                  	$inst_penalty_percentage = $cd->customer_det->customer_penalty_interest;
			                }
			                elseif($cd->group_det->group_penalty_days!=0 && $cd->group_det->group_base_penalty!=0)
			                {
			                  	$inst_penalty_days = $cd->group_det->group_penalty_days;
			                  	$inst_penalty_percentage = $cd->group_det->group_base_penalty;
			                }
			                elseif($cd->branch_det->penalty_days!=0 && $cd->branch_det->branch_wise_penalty!=0)
			                {
			                  	$inst_penalty_days = $cd->branch_det->penalty_days;
			                  	$inst_penalty_percentage = $cd->branch_det->branch_wise_penalty;
			                }
			                else
			                {
			                  	$inst_penalty_days = "0";
			                  	$inst_penalty_percentage = "0";
			                }
			                $pend_days_int = 0;    
			                $cust_det['penalty_amounts'] = 0;   
			                if($pending_days > $inst_penalty_days)
			                {     	
			                  	$branch=Branch::where('id',$cd->branch_id)->get();
			                  	if($cd->prized_status==0)
			                  	{
			                    	$non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
			                    	$pend_days_int = $pending_days * ($non_prize/ 100);
			                    	$current_month_days = date('t'); 
			                    	$total_days_int = $pend_days_int / $current_month_days;
			                    	$penalty = $total_days_int * $ins_amt_wise_rec;
			                    	$cust_det['penalty_amounts'] = strval(round($penalty));
			                  	} 
			                  	else
			                  	{               
			                    	$prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
			                    	$pend_days_int = $pending_days * ($prize / 100);
			                    	$current_month_days = date('t'); 
			                    	$total_days_int = $pend_days_int / $current_month_days;
			                    	$penalty = $total_days_int * $ins_amt_wise_rec;
			                    	$cust_det['penalty_amounts'] = strval(round($penalty));
			                  	}                        		  
			                }    
							$cust_det['installment_no'] = $installment_no;
							$cust_det['auction_date'] = $auc_date_inst;
							$cust_det['inst_amt_wise'] = $inst_amt;
							$cust_det['amount_paid'] = $amount_paid;
							$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
							if($inst_amt == $amount_paid)
							{
								$cust_det['pending_days'] = 0;
							}else
							{
								$cust_det['pending_days'] = $pending_days;
							}
							$cust_det['bonus_inst_wise'] = 0;
							$cust_det['penalty_inst_wise'] = 0;
							$cust_det['discount_inst_wise'] = 0;
							$cust_det['discount_penalty_wise'] = 0;
							$cust_det['auction_id'] = $auction_id;
							$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
							
							array_push($install_det,$cust_det);
							
							$total_penalty_amounts += $penalty;
							$total_bonus_amounts += $bonus;
						};
						$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
						$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
						$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
						$cd->pending_details = $install_det;
							
						array_push($response_data,$cd);			        	
			        }
			        if($cd->chit_type == 3)
			        {			        	
						$slab_types = SlabDue::where('id',$cd->slab_id)->get();   
			            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
			            $cd->group_name = "Commitment"."-".$slab_name;
			            $chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : "0"; 

						//total_installment_amount
						$total_installment_amount = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
						$cd->overall_installment_amount = $total_installment_amount;
						//paid_amount
						$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
						$paid_rec_amount = $receipt[0]->total_paid;
						$bonus_amount = $receipt[0]->bonus_amount;
						$paid_amount = $paid_rec_amount + $bonus_amount;
						$cd->overall_paid_amount = $paid_amount;
						//pending_amount 
						$pending_amount = $total_installment_amount - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;
						//last auction details
						$max_bid_id = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->max('id');
						$current_inst_amt = CommitmentAuction::where('id','=',$max_bid_id)->pluck('due_amount');
						//no installment paid 
						$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
					   //auction wise details 
						$auc_wise_inst_amts = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->get();
						$i=0;
						$install_det = [];					
						$total_penalty_amounts = 0;
						$total_bonus_amounts = 0;
						foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
							$cust_det = [];		
							$penalty = 0;					
							$bonus = 0;					
							$inst_amt = $auc_wise_inst_amt->due_amount;
							$auction_id = $auc_wise_inst_amt->id;
							$auc_id  = $auc_wise_inst_amt->id;
							$installment_no  = $auc_wise_inst_amt->installment_no;
							$auc_date_inst  = $auc_wise_inst_amt->auction_date;
		                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_id, 'status' => 1])->get();
							$max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
							$last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date');
							if(empty($cus_rec_amt[0])) { 
								$amount_paid = 0;
							} 
							else {
							  	$amount_paid_rec = $cus_rec_amt[0]->total_paid;
							  	$amount_paid_bonus = $cus_rec_amt[0]->bonus_amount;
							  	$amount_paid = $amount_paid_rec + $amount_paid_bonus;
							}
							$ins_amt_wise_rec = $inst_amt - $amount_paid;	
							if($ins_amt_wise_rec !=$inst_amt && $i==0 && $inst_amt_no !=0) {
								$receipt_date = $last_receipt_date_inst[0];
								$current_date = date('Y-m-d');
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
								$i++;
							} 
							else {
								$receipt_date = $auc_date_inst;
								$current_date = date('Y-m-d');
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
							}
							
							// Bonus days and percentage by heirrachy over all 
							if($cd->customer_det->bonus_days!=0 && $cd->customer_det->customer_bonus!=0)
							{
							  	$bonus_days = $cd->customer_det->bonus_days;
							  	$bonus_percentage = $cd->customer_det->customer_bonus;
							}    							
							elseif($cd->branch_det->bonus_days!=0 && $cd->branch_det->bonus!=0)
							{
							  	$bonus_days = $cd->branch_det->bonus_days;
							  	$bonus_percentage = $cd->branch_det->bonus;
							}    
							else
							{
							  	$bonus_days = 0;
							  	$bonus_percentage = 0;
							} 	
							if($pending_days <=  $bonus_days)
							{ 
							  	if($ins_amt_wise_rec==$inst_amt)
							  	{ 
									$bonus_calculation = $inst_amt * $bonus_percentage / 100 ; 
									$bonus = strval(round($bonus_calculation));
									$cust_det['bonus'] = $bonus;
							  	}
							  	else
							  	{
									$cust_det['bonus'] = 0;
							  	}
							}
							else
							{
							  	$cust_det['bonus'] = 0;
							}
							
							// Penalty Calculation installment wise
			                if($cd->customer_det->penalty_days!=0 && $cd->customer_det->customer_penalty_interest!=0)
			                {
			                  	$inst_penalty_days = $cd->customer_det->penalty_days;
			                  	$inst_penalty_percentage = $cd->customer_det->customer_penalty_interest;
			                }
			                elseif($cd->branch_det->penalty_days!=0 && $cd->branch_det->branch_wise_penalty!=0)
			                {
			                  	$inst_penalty_days = $cd->branch_det->penalty_days;
			                  	$inst_penalty_percentage = $cd->branch_det->branch_wise_penalty;
			                }
			                else
			                {
			                  	$inst_penalty_days = "0";
			                  	$inst_penalty_percentage = "0";
			                }
			                $pend_days_int = 0;    
			                $cust_det['penalty_amounts'] = 0;   
			                if($pending_days > $inst_penalty_days)
			                {     	
			                  	$branch=Branch::where('id',$cd->branch_id)->get();
			                  	if($cd->prized_status==0)
			                  	{
			                    	$non_prize = isset($branch[0]->non_prize_subscriber_penalty) ? ($branch[0]->non_prize_subscriber_penalty) : 0;
			                    	$pend_days_int = $pending_days * ($non_prize/ 100);
			                    	$current_month_days = date('t'); 
			                    	$total_days_int = $pend_days_int / $current_month_days;
			                    	$penalty = $total_days_int * $ins_amt_wise_rec;
			                    	$cust_det['penalty_amounts'] = strval(round($penalty));
			                  	} 
			                  	else
			                  	{               
			                    	$prize = isset($branch[0]->prize_subscriber_penalty) ? ($branch[0]->prize_subscriber_penalty) : 0;                     
			                    	$pend_days_int = $pending_days * ($prize / 100);
			                    	$current_month_days = date('t'); 
			                    	$total_days_int = $pend_days_int / $current_month_days;
			                    	$penalty = $total_days_int * $ins_amt_wise_rec;
			                    	$cust_det['penalty_amounts'] = strval(round($penalty));
			                  	}                        		  
			                }    
							$cust_det['installment_no'] = $installment_no;
							$cust_det['auction_date'] = $auc_date_inst;
							$cust_det['inst_amt_wise'] = $inst_amt;
							$cust_det['amount_paid'] = $amount_paid;
							$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
							if($inst_amt == $amount_paid)
							{
								$cust_det['pending_days'] = 0;
							}else
							{
								$cust_det['pending_days'] = $pending_days;
							}
							$cust_det['bonus_inst_wise'] = 0;
							$cust_det['penalty_inst_wise'] = 0;
							$cust_det['discount_inst_wise'] = 0;
							$cust_det['discount_penalty_wise'] = 0;
							$cust_det['auction_id'] = $auction_id;
							$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
							
							array_push($install_det,$cust_det);
							
							$total_penalty_amounts += $penalty;
							$total_bonus_amounts += $bonus;
						};
						$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
						$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
						$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
						$cd->pending_details = $install_det;
							
						array_push($response_data,$cd);		
			        }
				}
    			$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $response_data;
				return response()->json($response,200);
    		}
    		else
    		{
    			$response['status'] = 'Error';
	            $response['msg'] = \Lang::get('api.no_data_found');
	            return response()->json($response, 200);
    		}
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = "Error";
    		$response['msg'] = \Lang::get('api.global_error');
    		return response()->json($response,401);
    	}
    }
	
	public function store_customer_pigmy_receipt(Request $request)
    {
      DB::purge('mysql');
          Config::set('database.connections.mysql.database', $request->db);
          DB::reconnect('mysql');
		  $request->employee_id = '3';
            try { 
			 
              $receipt_no = ''; 
                $othercharge=new PigmyCollection(); 
       
                $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 30;
                $othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
      
                $othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				
                $othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
               
                $othercharge->received_date = isset($request->received_date) ? ($request->received_date) : "";
                
                $othercharge->amount = isset($request->amount) ? ($request->amount) : 0 ;
                $othercharge->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                $othercharge->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                $othercharge->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
      
		 
                $othercharge->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                $othercharge->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
       
                $othercharge->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
                $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $othercharge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $othercharge->status = isset($request->status) ? ($request->status) : 1;
      
                if($othercharge->amount > 0)
                {
					  
                  if($othercharge->save()){
					   
                    if($receipt_no=='') {
                      //sleep(10);
                      $receipt_no = 'Pigmy-'.sprintf('%05d', $othercharge->id);
                    }     
                    $othercharge->receipt_no = $receipt_no;
                    $othercharge->save();
          
          // $receiver = isset($othercharge->customer_det->mobile_no) ? ($othercharge->customer_det->mobile_no) : ''; 
          // $scheme_id = isset($othercharge->customer_det->scheme_id) ? ($othercharge->customer_det->scheme_id) : ''; 
          // $chit_value = Scheme::where('Id','=',$scheme_id)->pluck('chit_value');
          // $chit_value = $chit_value[0]; 
          
    
// $msg = "Dear Customer, payment of Rs $othercharge->amount.is Received, Receipt No $receipt_no on $othercharge->received_date to Chit group/tkt num $chit_value,Thank you , For complaints call 04256263366  - Best chits";


//   $msg1=$msg;
    
//     $msg_split = str_split($msg,456);
//     for($i=0;$i<sizeof($msg_split);$i++)
//     {
        
//         $msg_temp =  urlencode( $msg_split[$i] );
//               $ch = curl_init("https://www.instaalerts.zone/SendSMS/sendmsg.php?uname=bestgrouptr&pass=Abc@321&send=BESTGP&dest=$receiver&msg=$msg_temp");
            
              
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_HEADER, 0);
//           $result = curl_exec($ch);
//         curl_close($ch);
//     }
        
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_before_enrol_receipt_added_successfully');
                        $response['data']=$othercharge;
                        $response['receipt_no'] = $othercharge->receipt_no; 
                        return response()->json($response,200);  
                    }
                }     
                else 
                {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.other_charges_amount_invalid');
                    return response()->json($response,200);
                }             
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        
  } 
  
  public function mobile_store_other_charge_type(Request $request)
    {
		
		DB::purge('mysql');
          Config::set('database.connections.mysql.database', $request->db);
          DB::reconnect('mysql');
        $validator=Validator::make($request->all(),[
           // 'tenant_id'=>'required',
            'branch_id'=>'required',
           // 'employee_id'=>'required',
            'amount'=>'required',
            'received_date'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
			
                $othercharge=new OtherCharge(); 
                $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $othercharge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
               // $othercharge->receipt_no = isset($request->receipt_no) ? ($request->receipt_no) : 0;
                $othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                $othercharge->charge_type_id = isset($request->charge_type_id) ? ($request->charge_type_id) : 0;
				
               
                    $othercharge->received_date = isset($request->received_date) ? ($request->received_date) : "";
               
                $othercharge->amount = isset($request->amount) ? ($request->amount) : 0 ;
                $othercharge->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                $othercharge->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                $othercharge->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
				
				
                $othercharge->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                $othercharge->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
				
                $othercharge->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
                $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $othercharge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $othercharge->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2){
							$othercharge->status = isset($request->status) ? ($request->status) : 2;
						}
						
                if($othercharge->amount > 0)
                {
                    if($othercharge->save()){
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_other_charge_type_added');
                        $response['data']=$othercharge;
                        return response()->json($response,200);  
                    }
                }     
                else 
                {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.other_charges_amount_invalid');
                    return response()->json($response,200);
                }             
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['statuse']=$e;
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
	}
	
	public function mobile_store_lead_login(Request $request)
  {
     $validator=Validator::make($request->all(),[
      'mobile_no'=>'required',
    ]); 
    if($validator->fails()){
      $response['status']="Error";
      $response['msg']=$validator->error()->first();
      return response()->json($response,401);
    }
    else {
      try { 
        DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
	  
	  $bank =  LeadManagement::where(array('mobile_no'=>$request->mobile_no))->get();       
                if(count($bank)>0)
                {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('Mobile No is Already Exist.');
                    return response()->json($response, 200);
                }
                else{
	  
        $leadmanagement=new LeadManagement(); 
        $leadmanagement->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 47;
        $leadmanagement->branch_id = isset($request->branch_id) ? ($request->branch_id) : 1;
        $leadmanagement->employee_id = isset($request->employee_id) ? ($request->employee_id) : 1;
        $leadmanagement->lead_customer_name = isset($request->lead_customer_name) ? ($request->lead_customer_name) : '';
        $leadmanagement->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : 0;
        $leadmanagement->status = isset($request->status) ? ($request->status) : 1;
        $leadmanagement->followup_status_type_id = 1; 
		$leadmanagement->save();
		
						$cus_id = LeadManagement::max('id');
    					$customer_login=new User(); 
    					$customer_login->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 47;
    					$customer_login->branch_id = isset($request->branch_id) ? ($request->branch_id) : 1;
    					$customer_login->employee_id = $cus_id;   
    					$customer_login->email = isset($request->mobile_no) ? ($request->mobile_no) : '';   
    					$password_original = "12345"; 
    					$customer_login->password = Hash::make($password_original);							
    					$customer_login->role_id = "15";   
    					$customer_login->user_type = "5";
    					$customer_login->created_by = isset($request->created_by) ? ($request->created_by) : '';
    					$customer_login->status = isset($request->status) ? ($request->status) : 1;
						$customer_login->save();

                      
   
                     
        $response['status'] = "Success";
        $response['cust_primary_id'] = $cus_id;
        $response['msg'] = '';
        return response()->json($response,200);   
				}		
      } 
      catch(\Exception $e)
      {
        $response['status'] = "Error";
        $response['msg'] = '' ;
        return response()->json($response,401);
      }
    } 
  }
  
  public function lead_update_password(Request $request)
  {
    try
    {
		//$request->db="my_chits";
       DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');
	  
      $cond = array('id'=>$request->cust_primary_id);
      $customer_tables = LeadManagement::where($cond)->get();
      $customer_tables[0]->password = Hash::make($request->password); 
      $customer_tables[0]->user_enabled = 1;    
      if($customer_tables[0]->save())
      {
		  
		 $cond = array('employee_id'=>$request->cust_primary_id);
		 $cond = array('user_type'=>'5');
         $customer_tabless = User::where($cond)->get();
          $customer_tabless[0]->password = Hash::make($request->password); 
	  
        $response['status'] = 'Success';
        $response['msg'] = '';
        return response()->json($response, 200);
      }
      else
      {
        $response['status'] = 'Error';
        $response['msg'] = \Lang::get('api.not_registered_customer');
        return response()->json($response, 401);
      }
    }
    catch(\Exception $e)
    {
      $response['status'] = 'Error';
      $response['msg'] = \Lang::get('api.global_error');
      return response()->json($response, 401);
    }
  }
  

public function mobile_customer_dashboard(Request $request)
{
    try {

        if (!$request->customer_id || !$request->tenant_id || !$request->db) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing'
            ], 422);
        }

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $customerId = $request->customer_id;
        $tenantId = $request->tenant_id;
        $today = date('Y-m-d');

        $enrollments = ChitDetail::with(['group_det.scheme_det', 'slab_due_det.scheme_det'])
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->get();

        /* Total chit value: slab_id -> slab_dues -> schemes.chit_value (cumulative) */
        $totalChitValue = 0;
        foreach ($enrollments as $enroll) {
            if (!empty($enroll->slab_id) && $enroll->slab_due_det && $enroll->slab_due_det->scheme_det) {
                $totalChitValue += (float) $enroll->slab_due_det->scheme_det->chit_value;
            } elseif ((int) $enroll->chit_type !== 3 && $enroll->group_det && $enroll->group_det->scheme_det) {
                $tv = (float) $enroll->group_det->scheme_det->chit_value;
                $totalChitValue += $tv * (float) ($enroll->ticket_type ?: 1);
            }
        }

        /* Paid amount: receipts.received_amount for customer */
        $paidAmount = (float) Receipt::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->sum('received_amount');

        /* Commitment dues (all enrollments) */
        $totalDueAll = (float) CommitmentAuction::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->sum('due_amount');

        $dueTillToday = (float) CommitmentAuction::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->whereDate('auction_date', '<=', $today)
            ->sum('due_amount');

        $remainingDues = max(0, round($totalDueAll - $paidAmount));
        $pendingAmount = max(0, round($dueTillToday - $paidAmount));

        /* Upcoming due: next auction_date after today */
        $upcomingRow = CommitmentAuction::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->whereDate('auction_date', '>', $today)
            ->orderBy('auction_date', 'asc')
            ->first();

        $upcomingDueAmount = $upcomingRow ? round((float) $upcomingRow->due_amount) : 0;
        $upcomingDueDate = $upcomingRow ? $upcomingRow->auction_date : '';
        $upcomingDueDateDisplay = '';
        if ($upcomingDueDate && $upcomingDueDate !== '0000-00-00') {
            $upcomingDueDateDisplay = date('d M Y', strtotime($upcomingDueDate));
        }

        /* Active chits: status = 1, or NULL (legacy rows treated as running) */
        $activeEnrollments = ChitDetail::with(['group_det.scheme_det', 'slab_due_det.scheme_det'])
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            })
            ->orderBy('id', 'desc')
            ->get();

        $activeChitsList = [];
        foreach ($activeEnrollments as $enroll) {
            $activeChitsList[] = $this->buildDashboardActiveChit($enroll, $today);
        }

        $primaryActiveChit = count($activeChitsList) > 0 ? $activeChitsList[0] : null;

        $announcements = [
            [
                'id' => 1,
                'title' => 'Mazechit',
                'subtitle' => 'Investing in Your Future, Today',
                'image' => url('/storage/app/uploads/banner_1.png')
            ],
            [
                'id' => 2,
                'title' => 'Chit Funds',
                'subtitle' => 'Grow Your Savings',
                'image' => url('/storage/app/uploads/banner_1.png')
            ],
            [
                'id' => 3,
                'title' => 'Secure Investment',
                'subtitle' => 'Trusted & Transparent',
                'image' => url('/storage/app/uploads/banner_2.png')
            ]
        ];

        $myChitCount = $activeEnrollments->count();

        $dueCount = CommitmentAuction::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->whereDate('auction_date', '<=', $today)
            ->whereNotIn('id', function ($q) use ($customerId, $tenantId) {
                $q->select('auction_id')
                    ->from('receipts')
                    ->where('customer_id', $customerId)
                    ->where('tenant_id', $tenantId)
                    ->where('status', 1)
                    ->whereNotNull('auction_id')
                    ->where('auction_id', '!=', 0);
            })
            ->count();

        $schemeCount = DB::table('schemes')
            ->where('tenant_id', $tenantId)
            ->where('status', 1)
            ->count();

        $onTimePaymentCount = $this->countDashboardOnTimePayments($customerId, $tenantId);

        /* Referral bonus: paid incentive for enrollments this customer referred */
        $referralBonus = (float) ChitDetail::where('introducer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where('agent_type', 1)
            ->sum('paid_agent_incentive');

        /* Auction won: chit_details.payment_month has a value */
        $auctionsWon = ChitDetail::where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->whereNotNull('payment_month')
            ->where('payment_month', '!=', '')
            ->where('payment_month', '!=', '0')
            ->where('payment_month', '!=', 0)
            ->count();

        $memberSinceDisplay = '';
        $customerRow = Customer::where('id', $customerId)->where('tenant_id', $tenantId)->first();
        if ($customerRow && !empty($customerRow->doj) && $customerRow->doj !== '0000-00-00') {
            $memberSinceDisplay = date('M Y', strtotime($customerRow->doj));
        }

        $onTimeLabel = $onTimePaymentCount === 1 ? '1 Month' : ($onTimePaymentCount . ' Months');

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => [
                'overview' => [
                    'total_chit_value' => strval(round($totalChitValue)),
                    'paid_amount' => strval(round($paidAmount)),
                    'remaining_dues' => strval($remainingDues),
                    'due_till_today' => strval(round($dueTillToday)),
                    'pending_amount' => strval($pendingAmount),
                    'upcoming_due_amount' => strval($upcomingDueAmount),
                    'upcoming_due_date' => strval($upcomingDueDate),
                    'upcoming_due_date_display' => $upcomingDueDateDisplay,
                ],
                'active_chit' => $primaryActiveChit,
                'active_chits' => $activeChitsList,
                'active_chit_count' => strval($myChitCount),
                'upcoming_reminder' => [
                    'due_amount' => strval($upcomingDueAmount),
                    'due_date' => strval($upcomingDueDate),
                    'due_date_display' => $upcomingDueDateDisplay,
                    'chit_label' => $primaryActiveChit ? ($primaryActiveChit['display_name'] ?? '') : '',
                ],
                'announcements' => $announcements,
                'version' => [['app_version' => '1']],
                'my_home' => [
                    ['key' => 'my_chit', 'label' => 'My Chit', 'count' => $myChitCount],
                    ['key' => 'dues', 'label' => 'Dues', 'count' => (int) $dueCount],
                    ['key' => 'join_scheme', 'label' => 'Join Scheme', 'count' => $schemeCount],
                ],
                'bottom_stats' => [
                    'on_time_payment_count' => strval($onTimePaymentCount),
                    'on_time_payment_display' => $onTimeLabel,
                    'total_referred_bonus' => strval(round($referralBonus)),
                    'auctions_won' => strval($auctionsWon),
                    'member_since' => $memberSinceDisplay,
                    'member_since_raw' => $customerRow && !empty($customerRow->doj) ? strval($customerRow->doj) : '',
                ],
            ]
        ], 200);

    } catch (\Exception $e) {

        \Log::error('Mobile Customer Dashboard Error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return response()->json([
            'status' => 'Error',
            'msg' => 'Server Error'
        ], 500);
    }
}

/**
 * Map chit_details.status to list tab: running | completed | closed.
 */
private function getMyChitStatusTab($status)
{
    if ($status === null || $status === '' || (int) $status === 1) {
        return 'running';
    }
    if ((int) $status === 2) {
        return 'completed';
    }
    return 'closed';
}

/**
 * Full my-chits card (group + commitment enrollments).
 */
private function buildMyChitListItem($enroll, $today)
{
    $base = $this->buildDashboardActiveChit($enroll, $today);

    $receipt = Receipt::selectRaw('
            SUM(received_amount) as total_received,
            SUM(penalty) as total_penalty,
            SUM(bonus) as total_bonus
        ')
        ->where('enrollment_id', $enroll->id)
        ->where('status', 1)
        ->first();

    $paidAmount = (float) (($receipt->total_received ?? 0) + ($receipt->penalty ?? 0));
    $totalMonths = (int) ($base['total_months'] ?? 0);
    $paidMonths = (int) ($base['paid_months'] ?? 0);
    $remainingMonths = max(0, $totalMonths - $paidMonths);
    $progressPercent = $totalMonths > 0 ? (int) round(($paidMonths / $totalMonths) * 100) : 0;

    $pendingAmount = 0;
    if ((int) $enroll->chit_type === 3) {
        $totalDue = (float) CommitmentAuction::where('enrollment_id', $enroll->id)->sum('due_amount');
        $pendingAmount = max(0, $totalDue - $paidAmount);
    } elseif ($enroll->group_id) {
        $totalDue = (float) BiddingDetail::where('group_id', $enroll->group_id)->sum('current_installment_amount');
        $pendingAmount = max(0, $totalDue - $paidAmount);
    }

    $statusTab = $this->getMyChitStatusTab($enroll->status);
    $statusLabels = [
        'running' => 'Running',
        'completed' => 'Completed',
        'closed' => 'Closed',
    ];

    $chitCode = $this->resolveMyChitCode($enroll);
    $chitTypeLabel = $this->resolveMyChitTypeLabel($enroll);

    $auctionWon = !empty($enroll->payment_month)
        && $enroll->payment_month !== '0'
        && $enroll->payment_month !== 0;

    return array_merge($base, [
        'chit_code' => $chitCode,
        'chit_type_label' => $chitTypeLabel,
        'status' => $enroll->status === null ? '1' : strval($enroll->status),
        'status_tab' => $statusTab,
        'status_label' => $statusLabels[$statusTab] ?? 'Closed',
        'remaining_months' => strval($remainingMonths),
        'paid_amount' => strval(round($paidAmount)),
        'pending_amount' => strval(round($pendingAmount)),
        'progress_percent' => strval($progressPercent),
        'progress_display' => $progressPercent . '% Completed',
        'paid' => strval(round($paidAmount)),
        'installment_per_month' => $base['monthly_installment'],
        'install_amount' => $base['monthly_installment'],
        'penalty' => strval(round($receipt->total_penalty ?? 0)),
        'bonus' => strval(round($receipt->total_bonus ?? 0)),
        'auction_won' => $auctionWon ? '1' : '0',
    ]);
}

private function resolveMyChitCode($enroll)
{
    if ((int) $enroll->chit_type === 3) {
        return 'SPN' . str_pad((string) $enroll->id, 4, '0', STR_PAD_LEFT);
    }
    $ticket = trim((string) ($enroll->ticket_no ?? ''));
    if ($ticket !== '' && $ticket !== '-' && $ticket !== '0') {
        return $ticket;
    }
    $groupName = $enroll->group_det->group_name ?? '';
    if ($groupName !== '') {
        return preg_replace('/\s+/', '', $groupName);
    }
    return 'SPN' . str_pad((string) $enroll->id, 4, '0', STR_PAD_LEFT);
}

private function resolveMyChitTypeLabel($enroll)
{
    if ((int) $enroll->chit_type === 3) {
        return 'Commitment Chit';
    }
    $shortTerm = '';
    if ($enroll->group_det && $enroll->group_det->scheme_det) {
        $shortTerm = strtolower((string) ($enroll->group_det->scheme_det->short_term ?? ''));
    }
    if (strpos($shortTerm, 'quarter') !== false) {
        return 'Quarterly Chit';
    }
    return 'Monthly Chit';
}

/**
 * Count installments paid on or before due date (receipt_date <= auction due date).
 */
private function countDashboardOnTimePayments($customerId, $tenantId)
{
    $commitmentOnTime = DB::table('receipts as r')
        ->join('commitment_auctions as ca', 'ca.id', '=', 'r.auction_id')
        ->where('r.customer_id', $customerId)
        ->where('r.tenant_id', $tenantId)
        ->where('r.status', 1)
        ->whereNotNull('r.auction_id')
        ->where('r.auction_id', '!=', 0)
        ->whereRaw('DATE(r.receipt_date) <= DATE(ca.auction_date)')
        ->distinct()
        ->count('r.auction_id');

    $groupOnTime = DB::table('receipts as r')
        ->join('bidding_details as bd', 'bd.id', '=', 'r.auction_id')
        ->where('r.customer_id', $customerId)
        ->where('r.tenant_id', $tenantId)
        ->where('r.status', 1)
        ->whereNotNull('r.auction_id')
        ->where('r.auction_id', '!=', 0)
        ->whereRaw('DATE(r.receipt_date) <= DATE(bd.auction_date)')
        ->distinct()
        ->count('r.auction_id');

    return (int) ($commitmentOnTime + $groupOnTime);
}

/**
 * Build one active-chit card for dashboard (status = 1 enrollments).
 */
private function buildDashboardActiveChit($enroll, $today)
{
    $displayName = '';
    $chitValue = 0;
    $monthlyInstallment = 0;
    $totalMonths = 0;
    $paidMonths = 0;
    $nextDueDate = '';
    $nextDueDateDisplay = '';

    if ((int) $enroll->chit_type === 3) {
        $slabName = $enroll->slab_due_det->slab_name ?? '';
        $displayName = 'Commitment' . ($slabName ? '-' . $slabName : '');
        if ($enroll->slab_due_det && $enroll->slab_due_det->scheme_det) {
            $chitValue = (float) $enroll->slab_due_det->scheme_det->chit_value;
        }

        $totalMonths = CommitmentAuction::where('enrollment_id', $enroll->id)->count();
        $paidMonths = Receipt::where('enrollment_id', $enroll->id)
            ->where('status', 1)
            ->whereNotNull('auction_id')
            ->where('auction_id', '!=', 0)
            ->distinct()
            ->count('auction_id');

        $nextAuction = CommitmentAuction::where('enrollment_id', $enroll->id)
            ->whereDate('auction_date', '>', $today)
            ->orderBy('auction_date', 'asc')
            ->first();

        if ($nextAuction) {
            $nextDueDate = $nextAuction->auction_date;
            $monthlyInstallment = (float) $nextAuction->due_amount;
        } else {
            $lastAuction = CommitmentAuction::where('enrollment_id', $enroll->id)
                ->orderBy('auction_date', 'desc')
                ->first();
            if ($lastAuction) {
                $nextDueDate = $lastAuction->auction_date;
                $monthlyInstallment = (float) $lastAuction->due_amount;
            }
        }
    } else {
        $displayName = ($enroll->group_det->group_name ?? '') . '/' . strval($enroll->ticket_no ?? '');
        if ($enroll->group_det && $enroll->group_det->scheme_det) {
            $chitValue = (float) $enroll->group_det->scheme_det->chit_value * (float) ($enroll->ticket_type ?: 1);
            $totalMonths = (int) ($enroll->group_det->scheme_det->no_of_months ?? 0);
            $monthlyInstallment = (float) ($enroll->group_det->scheme_det->monthly_due ?? 0);
        }
        $paidMonths = Receipt::where('enrollment_id', $enroll->id)
            ->where('status', 1)
            ->whereNotNull('auction_id')
            ->where('auction_id', '!=', 0)
            ->distinct()
            ->count('auction_id');
        if ($totalMonths === 0) {
            $totalMonths = BiddingDetail::where('group_id', $enroll->group_id)->count();
        }
        $nextBid = BiddingDetail::where('group_id', $enroll->group_id)
            ->whereDate('auction_date', '>', $today)
            ->orderBy('auction_date', 'asc')
            ->first();
        if ($nextBid) {
            $nextDueDate = $nextBid->auction_date;
            $monthlyInstallment = (float) ($nextBid->current_installment_amount ?? $monthlyInstallment);
        }
    }

    if ($nextDueDate && $nextDueDate !== '0000-00-00') {
        $nextDueDateDisplay = date('d M Y', strtotime($nextDueDate));
    }

    return [
        'enrollment_id' => strval($enroll->id),
        'chit_type' => strval($enroll->chit_type),
        'display_name' => $displayName,
        'group_name' => $enroll->group_det->group_name ?? '',
        'ticket_no' => strval($enroll->ticket_no ?? ''),
        'chit_value' => strval(round($chitValue)),
        'total_months' => strval($totalMonths),
        'paid_months' => strval($paidMonths),
        'next_due_date' => strval($nextDueDate),
        'next_due_date_display' => $nextDueDateDisplay,
        'monthly_installment' => strval(round($monthlyInstallment)),
        'installment_progress' => strval($paidMonths) . '/' . strval($totalMonths),
    ];
}

public function customer_profile(Request $request)
{
    /* =========================
       VALIDATION
    ========================= */
    $validator = Validator::make($request->all(), [
        'tenant_id'   => 'required',
        'customer_id' => 'required',
        'db'          => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg'    => $validator->errors()->first()
        ], 200);
    }

    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =========================
           CUSTOMER PROFILE DATA
        ========================= */
        $customer = Customer::select(
                'name',
                'last_name',
                'mobile_no',
                'email_id'
            )
            ->where('id', $request->customer_id)
            ->where('tenant_id', $request->tenant_id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'Customer not found'
            ], 200);
        }

        /* =========================
           SUCCESS RESPONSE
        ========================= */
        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => [
                'name'       => $customer->name ?? '',
                'last_name'  => $customer->last_name ?? '',
                'mobile_no'  => $customer->mobile_no ?? '',
                'email_id'   => $customer->email_id ?? ''
            ]
        ], 200);

    } catch (\Exception $e) {

        \Log::error('Customer Profile API Error', [
            'error' => $e->getMessage(),
            'line'  => $e->getLine()
        ]);

        return response()->json([
            'status' => 'Error',
            'msg'    => 'Server Error'
        ], 500);
    }
}

public function customer_due_details(Request $request) 
{
    /* =========================
       VALIDATION
    ========================= */
    $validator = Validator::make($request->all(), [
        'tenant_id'   => 'required',
        'customer_id' => 'required',
        'db'          => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg' => $validator->errors()->first()
        ], 200);
    }

    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $response_data = [];

        /* =========================
           CUSTOMER ENROLLMENTS
        ========================= */
        $enrollments = ChitDetail::with(['group_det'])
            ->where('customer_id', $request->customer_id)
            ->where('tenant_id', $request->tenant_id)
            ->where('status', 1)
            ->get();

        foreach ($enrollments as $enroll)
        {
            /* =========================
               ALL INSTALLMENTS
            ========================= */
            $installments = BiddingDetail::where('group_id', $enroll->group_id)
                ->orderBy('installment_no', 'asc')
                ->get();

            if ($installments->isEmpty()) {
                continue;
            }

            /* =========================
               TOTAL INSTALLMENT COUNT & AMOUNT (NEW)
            ========================= */
            $total_installment_count  = $installments->count();
            $total_installment_amount = $installments->sum('current_installment_amount');

            /* =========================
               PAID INSTALLMENTS
            ========================= */
            $paidAuctionIds = Receipt::where('enrollment_id', $enroll->id)
                ->pluck('auction_id')
                ->toArray();

            /* =========================
               PENDING INSTALLMENTS
            ========================= */
            $pending_installments = [];
            $pending_amount = 0;

            foreach ($installments as $inst)
            {
                if (!in_array($inst->id, $paidAuctionIds)) {

                    $pending_installments[] = [
                        'installment_no' => str_pad($inst->installment_no, 2, '0', STR_PAD_LEFT),
                        'amount' => round($inst->current_installment_amount)
                    ];

                    $pending_amount += $inst->current_installment_amount;
                }
            }

            if (count($pending_installments) === 0) {
                continue;
            }

            /* =========================
               RESPONSE GROUP BLOCK
            ========================= */
            $response_data[] = [
                'group_name' => $enroll->group_det->group_name ?? '',
                'ticket_no'  => strval($enroll->ticket_no),

                // ✅ NEW FIELDS
                'total_installment_count'  => $total_installment_count,
                'total_installment_amount' => round($total_installment_amount),

                'pending_installment_count' => count($pending_installments),
                'pending_amount' => round($pending_amount),
                'pending_installments' => $pending_installments
            ];
        }

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => $response_data
        ], 200);

    } catch (\Exception $e) {

        \Log::error('Customer Due Details API Error', [
            'error' => $e->getMessage(),
            'line'  => $e->getLine()
        ]);

        return response()->json([
            'status' => 'Error',
            'msg' => 'Server Error'
        ], 500);
    }
}


		public function customer_app_version(Request $request) 
		{
			
			$response_data = [];
			
			  $response_data[] = [
						'latest_version' => '1.0',
						'min_required_version'  => '1.0'
						];
						
						 return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => $response_data
        ], 200);
		}
		
	
public function mobile_get_collection_area_customers_new(Request $request)
{
    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =========================
           RESOLVE EMPLOYEE
        ========================= */
        $empId = !empty($request->employee_id) ? (int) $request->employee_id : 0;
        $user = null;
        if (!empty($request->user_id)) {
            $userQuery = User::where('id', $request->user_id);
            if (!empty($request->tenant_id)) {
                $userQuery->where('tenant_id', $request->tenant_id);
            }
            $user = $userQuery->first();
            if ($empId <= 0 && $user && !empty($user->employee_id)) {
                $empId = (int) $user->employee_id;
            }
        }

        /* =========================
           CASH IN HAND (ADDED)
        ========================= */
        $cashInHand = "0";

        if ($empId > 0) {
            $startDate = "2022-04-26";
            $today = date('Y-m-d');

            $cashReceipts = Receipt::where('employee_id', $empId)
                ->where('payment_type_id', 1)
                ->where('status', 1)
                ->where('receipt_mode', 'Mobile')
                ->whereBetween('receipt_date', [$startDate, $today])
                ->selectRaw('SUM(received_amount + penalty) as total')
                ->value('total') ?? 0;

            $cashAdvance = AdvanceReceipt::where('employee_id', $empId)
                ->where('payment_type_id', 1)
                ->where('status', 1)
                ->where('receipt_mode', 'Mobile')
                ->whereBetween('receipt_date', [$startDate, $today])
                ->sum('receipt_amount');

            $settled = CashSettlement::where('employee_id', $empId)
                ->where('settlement_mode', 'Mobile')
                ->whereBetween('settlement_date', [$startDate, $today])
                ->sum('received_amount');

            $cashInHand = (string) round(($cashReceipts + $cashAdvance) - $settled);
        }

        /* =========================
           COLLECTION AREA MAPPED CUSTOMERS ONLY
           (same mapping as mobile_get_collection_area_customers)
        ========================= */
        $areaIds = [];
        if ($empId > 0) {
            $mapQuery = CollectionAreaMapping::where('employee_id', $empId);
            if (!empty($request->tenant_id)) {
                $mapQuery->where('tenant_id', $request->tenant_id);
            }
            $collectionAreaMappings = $mapQuery->get();
            foreach ($collectionAreaMappings as $cm) {
                $rawArea = $cm->collection_area_id;
                $parsed = @unserialize($rawArea);
                if (is_array($parsed)) {
                    foreach ($parsed as $area) {
                        if (isset($area['item_id']) && (int) $area['item_id'] > 0) {
                            $areaIds[] = (int) $area['item_id'];
                        }
                    }
                } elseif (is_numeric($rawArea) && (int) $rawArea > 0) {
                    $areaIds[] = (int) $rawArea;
                }
            }
        }
        $areaIds = array_values(array_unique(array_filter($areaIds)));

        if (empty($areaIds)) {
            return response()->json([
                'cash_in_hand' => $cashInHand,
                'overall_to_be_collected' => "0",
                'customers' => []
            ], 200);
        }

        $chitQuery = ChitDetail::whereIn('collection_area_id', $areaIds)
            ->where('customer_id', '!=', 1);
        if (!empty($request->tenant_id)) {
            $chitQuery->where('tenant_id', $request->tenant_id);
        }
        $customerIds = $chitQuery->pluck('customer_id')->unique()->values();

        if ($customerIds->isEmpty()) {
            return response()->json([
                'cash_in_hand' => $cashInHand,
                'overall_to_be_collected' => "0",
                'customers' => []
            ], 200);
        }

        $response = [];
        $overallPending = 0; // 🔥 ADDED

        /* =========================
           PROCESS IN SAFE CHUNKS
        ========================= */
        $customerIds->chunk(200)->each(function ($chunk) use (&$response, &$overallPending) {

            $ids = $chunk->toArray();

            /* =========================
               CUSTOMER BASIC INFO
            ========================= */
            $customers = Customer::whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            /* =========================
               GROUP CHIT INSTALLMENTS
            ========================= */
            $groupInstallments = DB::table('chit_details')
                ->join('bidding_details', 'bidding_details.group_id', '=', 'chit_details.group_id')
                ->whereIn('chit_details.customer_id', $ids)
                ->where('chit_details.chit_type', '!=', 3)
                ->groupBy('chit_details.customer_id')
                ->selectRaw('chit_details.customer_id, SUM(bidding_details.current_installment_amount) amt')
                ->pluck('amt', 'customer_id');

            /* =========================
               COMMITMENT INSTALLMENTS
            ========================= */
            $commitmentInstallments = DB::table('chit_details')
                ->join('commitment_auctions', 'commitment_auctions.enrollment_id', '=', 'chit_details.id')
                ->whereIn('chit_details.customer_id', $ids)
                ->where('chit_details.chit_type', 3)
                ->whereDate('commitment_auctions.auction_date', '<=', date('Y-m-d'))
                ->groupBy('chit_details.customer_id')
                ->selectRaw('chit_details.customer_id, SUM(commitment_auctions.due_amount) amt')
                ->pluck('amt', 'customer_id');

            /* =========================
               PAID AMOUNTS
            ========================= */
            $paidAmounts = Receipt::whereIn('customer_id', $ids)
                ->where('status', 1)
                ->groupBy('customer_id')
                ->selectRaw('customer_id, SUM(received_amount + bonus) paid')
                ->pluck('paid', 'customer_id');

            /* =========================
               OVERDUE CUSTOMERS
            ========================= */
            $groupOverduePayable = DB::table('chit_details')
                ->join('bidding_details', 'bidding_details.group_id', '=', 'chit_details.group_id')
                ->whereIn('chit_details.customer_id', $ids)
                ->where('chit_details.chit_type', '!=', 3)
                ->whereDate('bidding_details.auction_date', '<', date('Y-m-01'))
                ->groupBy('chit_details.customer_id')
                ->selectRaw('chit_details.customer_id, SUM(bidding_details.current_installment_amount) amt')
                ->pluck('amt', 'customer_id');

            $commitmentOverduePayable = DB::table('chit_details')
                ->join('commitment_auctions', 'commitment_auctions.enrollment_id', '=', 'chit_details.id')
                ->whereIn('chit_details.customer_id', $ids)
                ->where('chit_details.chit_type', 3)
                ->whereDate('commitment_auctions.auction_date', '<', date('Y-m-01'))
                ->groupBy('chit_details.customer_id')
                ->selectRaw('chit_details.customer_id, SUM(commitment_auctions.due_amount) amt')
                ->pluck('amt', 'customer_id');

            $overdueCustomers = [];

            foreach ($ids as $cid) {
                $totalDueTillLastMonth =
                    ($groupOverduePayable[$cid] ?? 0) +
                    ($commitmentOverduePayable[$cid] ?? 0);

                $paid = $paidAmounts[$cid] ?? 0;

                if ($totalDueTillLastMonth > 0 && $paid < $totalDueTillLastMonth) {
                    $overdueCustomers[] = $cid;
                }
            }

            /* =========================
               BUILD RESPONSE
            ========================= */
            foreach ($ids as $cid) {

                if (!isset($customers[$cid])) continue;

                $totalInstallment =
                    round(($groupInstallments[$cid] ?? 0) + ($commitmentInstallments[$cid] ?? 0));

                $paid = round($paidAmounts[$cid] ?? 0);
                $pending = $totalInstallment - $paid;
                $advance = 0;

                if ($pending < 0) {
                    $advance = abs($pending);
                    $pending = 0;
                }

                $overallPending += $pending; // 🔥 ADDED

                $response[] = [
                    'customer_id'   => (string)$cid,
                    'customer_name' => $customers[$cid]->name,
                    'customer_code' => $customers[$cid]->customer_code,
                    'mobile_no'     => $customers[$cid]->mobile_no,

                    'total_installment' => (string)$totalInstallment,
                    'total_paid'        => (string)$paid,
                    'total_pending'     => (string)$pending,
                    'advance'           => (string)$advance,

                    'overdue' => in_array($cid, $overdueCustomers) ? 1 : 0
                ];
            }

            unset(
                $customers,
                $groupInstallments,
                $commitmentInstallments,
                $paidAmounts,
                $overdueCustomers
            );
        });

        /* =========================
           FINAL RESPONSE
        ========================= */
        return response()->json([
            'cash_in_hand' => $cashInHand,
            'overall_to_be_collected' => (string) $overallPending,
            'customers' => $response
        ], 200);

    } catch (\Throwable $e) {

        \Log::error('collection_area_customers_new: '.$e->getMessage());

        return response()->json([
            'status' => 'Error',
            'msg' => 'Unable to load customers'
        ], 500);
    }
}



public function mobile_get_customer_chit_summary(Request $request)
{
    try {
        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $customerId = $request->customer_id;
        $tenantId   = $request->tenant_id;
        $today      = date('Y-m-d');
        $monthStart = date('Y-m-01');
		
		$customer = Customer::select('id', 'name', 'customer_code', 'mobile_no')
    ->where('id', $customerId)
    ->first();
	
	if (!$customer) {
    return response()->json([], 200);
}

        /* =========================
           GET ENROLLMENTS
        ========================= */
        $enrollments = ChitDetail::where([
            'customer_id' => $customerId,
            'tenant_id'   => $tenantId
        ])->get();

        if ($enrollments->isEmpty()) {
            return response()->json([], 200);
        }

        $response = [];

        foreach ($enrollments as $enroll) {

            $paid = Receipt::where([
                    'enrollment_id' => $enroll->id,
                    'status' => 1
                ])->sum('received_amount');

            /* =========================
               GROUP CHIT
            ========================= */
            if ($enroll->chit_type != 3) {

                $group = $enroll->group_det;

                $chitValue = isset($group->scheme_det)
                    ? ($group->scheme_det->chit_value * $enroll->ticket_type)
                    : 0;

                $totalInstallment = BiddingDetail::where('group_id', $enroll->group_id)
                    ->sum(DB::raw('current_installment_amount * ticket_type'));

                $pending = $totalInstallment - $paid;
                if ($pending < 0) $pending = 0;

                /* OVERDUE CHECK */
                $overdue = BiddingDetail::where('group_id', $enroll->group_id)
                    ->whereDate('auction_date', '<', $monthStart)
                    ->exists() && $pending > 0;

                $status = $pending == 0
                    ? 'Paid'
                    : ($overdue ? 'Overdue' : 'Pending');

                $response[] = [
				 'customer_id'   => (string)$customer->id,
    'customer_name' => $customer->name,
    'customer_code' => $customer->customer_code,
    'mobile_no'     => $customer->mobile_no,
                    'enrollment_id' => (string)$enroll->id,
                    'group_name'    => $group->group_name,
                    'ticket_no'     => (string)$enroll->ticket_no,
                    'chit_value'    => (string)round($chitValue),
                    'overall'       => (string)round($totalInstallment),
                    'paid'          => (string)round($paid),
                    'pending'       => (string)round($pending),
                    'status'        => $status
                ];
            }

            /* =========================
               COMMITMENT CHIT
            ========================= */
            if ($enroll->chit_type == 3) {

                $slab = SlabDue::find($enroll->slab_id);

                $chitValue = isset($slab->scheme_det)
                    ? $slab->scheme_det->chit_value
                    : 0;

                $totalInstallment = CommitmentAuction::where('enrollment_id', $enroll->id)
                    ->whereDate('auction_date', '<=', $today)
                    ->sum('due_amount');

                $pending = $totalInstallment - $paid;
                if ($pending < 0) $pending = 0;

                $overdue = CommitmentAuction::where('enrollment_id', $enroll->id)
                    ->whereDate('auction_date', '<', $monthStart)
                    ->exists() && $pending > 0;

                $status = $pending == 0
                    ? 'Paid'
                    : ($overdue ? 'Overdue' : 'Pending');

                $response[] = [
				 'customer_id'   => (string)$customer->id,
    'customer_name' => $customer->name,
    'customer_code' => $customer->customer_code,
    'mobile_no'     => $customer->mobile_no,
                    'enrollment_id' => (string)$enroll->id,
                    'group_name'    => 'Commitment - ' . ($slab->slab_name ?? ''),
                    'ticket_no'     => '-',
                    'chit_value'    => (string)round($chitValue),
                    'overall'       => (string)round($totalInstallment),
                    'paid'          => (string)round($paid),
                    'pending'       => (string)round($pending),
                    'status'        => $status
                ];
            }
        }

        return response()->json($response, 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => 'Unable to load customer chit summary'
        ], 500);
    }
}

public function mobile_today_followup_count(Request $request)
{
    try {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        // ✅ VALIDATE CORRECT PARAMS
        if (!$request->user_id || !$request->tenant_id || !$request->db) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing'
            ], 422);
        }

        // ✅ MAP users.id → employee_id
        $user = User::select('employee_id')
            ->where('id', $request->user_id)
            ->first();

        if (!$user || !$user->employee_id) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Employee mapping not found'
            ], 404);
        }

        $employeeId = $user->employee_id;
        $today = date('Y-m-d');

        // ✅ TODAY NEXT FOLLOW-UPS
        $count = Followup::whereDate('next_followup_date', $today)
            ->where('employee_id', $employeeId)
            ->where('tenant_id', $request->tenant_id)
            ->distinct('lead_management_id')
            ->count('lead_management_id');

        return response()->json([
            'status' => 'Success',
            'today_followup_count' => $count
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => $e->getMessage() // helpful while testing
        ], 500);
    }
}



public function mobile_today_followup_leads(Request $request)
{
    try {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        // ✅ VALIDATE CORRECT PARAMS (GET)
        if (empty($request->db) || empty($request->tenant_id) || empty($request->user_id)) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing',
                'received' => $request->all()
            ], 422);
        }

        // ✅ MAP users.id → employee_id
        $user = User::select('employee_id')
            ->where('id', $request->user_id)
            ->first();

        if (!$user || !$user->employee_id) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Employee mapping not found'
            ], 404);
        }

        $employeeId = $user->employee_id;
        $today = now()->toDateString();

        $leads = Followup::join(
                'lead_managements',
                'lead_managements.id',
                '=',
                'followups.lead_management_id'
            )
            ->whereDate('followups.next_followup_date', $today)
            ->where('followups.employee_id', $employeeId)
            ->where('followups.tenant_id', $request->tenant_id)
            ->select(
                'lead_managements.id as lead_id',
                'lead_managements.lead_customer_name',
                'lead_managements.mobile_no',
                'lead_managements.address_line_1',
                'followups.next_followup_date'
            )
            ->groupBy(
                'lead_managements.id',
                'lead_managements.lead_customer_name',
                'lead_managements.mobile_no',
                'lead_managements.address_line_1',
                'followups.next_followup_date'
            )
            ->orderBy('followups.next_followup_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'Success',
            'today_followup_leads' => $leads
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => $e->getMessage()
        ], 500);
    }
}
	
	public function mobile_get_customer_wise_entrolment_details_new(Request $request) 
{
    try {

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $cond = [
            'customer_id' => $request->customer_id,
            'tenant_id'   => $request->tenant_id
        ];

        // ============================
        // Load enrollments with relations
        // ============================
        $entrollments = ChitDetail::with([
            'customer_det',
            'group_det.scheme_det',
            'slab_due_det.scheme_det',
            'branch_det'
        ])->where($cond)->get();

        if ($entrollments->isEmpty()) {
            return response()->json([
                'status' => 'Error',
                'msg' => \Lang::get('api.global_error')
            ], 401);
        }

        $cr_date = date("Y-m-d");
        $response_data = [];

        // ============================
        // Collect IDs once
        // ============================
        $enrollmentIds = $entrollments->pluck('id')->toArray();
        $groupIds      = $entrollments->pluck('group_id')->filter()->unique()->toArray();
        $branchIds     = $entrollments->pluck('branch_id')->unique()->toArray();
        $tenantId      = $request->tenant_id;

        // ============================
        // Bulk Load Tables
        // ============================
        $receipts = Receipt::whereIn('enrollment_id', $enrollmentIds)
            ->where('status',1)
            ->get()
            ->groupBy('enrollment_id');

        $advanceReceipts = AdvanceReceipt::whereIn('enrollment_id', $enrollmentIds)
            ->where('status',1)
            ->get()
            ->groupBy('enrollment_id');

        $biddings = BiddingDetail::whereIn('group_id', $groupIds)
            ->orderBy('id')
            ->get()
            ->groupBy('group_id');

        $commitments = CommitmentAuction::whereIn('enrollment_id', $enrollmentIds)
            ->orderBy('id')
            ->get()
            ->groupBy('enrollment_id');

        $branches = Branch::with(['city_det', 'district_det', 'state_det'])
            ->whereIn('id', $branchIds)
            ->get()
            ->keyBy('id');

        $holidays = AuctionOnHoliday::where('tenant_id',$tenantId)->get();

        $slabIds = $entrollments->where('chit_type', 3)->pluck('slab_id')->filter()->unique()->toArray();
        $slabs = SlabDue::with('scheme_det')
            ->whereIn('id', $slabIds)
            ->get()
            ->keyBy('id');

        // ============================
        // MAIN LOOP
        // ============================
        foreach($entrollments as $entrol) {

            $install_det = [];

            // 👉 Summary counters
            $total_installment_count = 0;
            $pending_due_count       = 0;
            $total_penalty           = 0;

            $entrollment_det = [
                'customer_id' => strval($entrol->customer_id),
                'customer_code' => optional($entrol->customer_det)->customer_code ?? '',
                'customer_name' => optional($entrol->customer_det)->name ?? '',
                'customer_mobile_no' => optional($entrol->customer_det)->mobile_no ?? '',
                'customer_phone_no' => optional($entrol->customer_det)->phone_no ?? '',
                'entrollment_id' => strval($entrol->id),
                'group_id' => "",
                'group_name' => "",
                'chit_value' => "",
                'auction_date' => "",
                'ticket_no' => "",
                'install_amount' => "",
                'paid_amount' => "",
                'pending_amount' => "",
                'advance_amount' => "",
                'pending_days' => "",
                'bonus' => "",
                'discountonpenalty' => "",
                'discountondue' => "",
                'prized_status' => $entrol->prized_status == 0 ? "Non-Prized" : "Prized",

                // ✅ NEW SUMMARY FIELDS
                'total_installment_count' => "0",
                'pending_due_count'       => "0",
                'total_penalty'           => "0",

                'installments_det' => [],
                'client_address' => '',
            ];

            $branchForAddress = $branches[$entrol->branch_id] ?? $entrol->branch_det;
            $entrollment_det['client_address'] = $this->formatBranchClientAddress($branchForAddress);

            // ================= Advance =================
            $received_sum = ($receipts[$entrol->id] ?? collect())
                                ->where('payment_type_id',7)
                                ->sum('received_amount');

            $advance_sum = ($advanceReceipts[$entrol->id] ?? collect())
                                ->sum('receipt_amount');

            $entrollment_det['advance_amount'] = strval($advance_sum - $received_sum);

            // ============================================================
            // NON COMMITMENT CHIT
            // ============================================================
            if($entrol->chit_type != 3) {

                $group      = $entrol->group_det;
                $groupBids  = $biddings[$entrol->group_id] ?? collect();
                $branch     = $branches[$entrol->branch_id] ?? null;

                // 👉 total installments
                $total_installment_count = $groupBids->count();

                $entrollment_det['group_id']   = strval($entrol->group_id);
                $entrollment_det['group_name'] = optional($group)->group_name ?? "";
                $entrollment_det['ticket_no']  = strval($entrol->ticket_no);

                $chit_value = optional(optional($group)->scheme_det)->chit_value ?? 0;
                $entrollment_det['chit_value'] = strval(round($chit_value) * $entrol->ticket_type);
                $entrollment_det['auction_date'] = optional($group)->auction_date ?? "0000-00-00";

                // ===== Paid =====
                $paid = ($receipts[$entrol->id] ?? collect())->sum('received_amount');
                $entrollment_det['paid_amount'] = strval(round($paid));

                // ===== Install Amount =====
                $total_install_amount = $groupBids->sum('current_installment_amount') * $entrol->ticket_type;
                $entrollment_det['install_amount'] = strval(round($total_install_amount));
                $entrollment_det['pending_amount'] = strval(round($total_install_amount - $paid));

                // ===== Current Install =====
                $currentBid = $groupBids->last();
                $current_instal_amt = round(optional($currentBid)->current_installment_amount ?? 0);

                // ===== Pending Days =====
                if($entrollment_det['pending_amount'] == $current_instal_amt) {
                    $from_date = optional($currentBid)->auction_date;
                } else {
                    $lastReceipt = ($receipts[$entrol->id] ?? collect())->last();
                    $from_date = optional($lastReceipt)->receipt_date ?? optional($currentBid)->auction_date;
                }

                if($from_date) {
                    $days = (new DateTime($from_date))->diff(new DateTime())->format('%a');
                    $entrollment_det['pending_days'] = strval($days);
                }

                // ===== Bonus Hierarchy =====
                if(optional($entrol->customer_det)->bonus_days && optional($entrol->customer_det)->customer_bonus) {
                    $bonus_days = $entrol->customer_det->bonus_days;
                    $bonus_percentage = $entrol->customer_det->customer_bonus;
                }
                elseif(optional($group)->group_bonus_days && optional($group)->group_base_bonus) {
                    $bonus_days = $group->group_bonus_days;
                    $bonus_percentage = $group->group_base_bonus;
                }
                elseif(optional($entrol->branch_det)->bonus_days && optional($entrol->branch_det)->bonus) {
                    $bonus_days = $entrol->branch_det->bonus_days;
                    $bonus_percentage = $entrol->branch_det->bonus;
                } else {
                    $bonus_days = 0;
                    $bonus_percentage = 0;
                }

                $holiday_count = $holidays->whereBetween('holiday_date', [
                    optional($currentBid)->auction_date,
                    $cr_date
                ])->count();

                if($entrollment_det['pending_days'] <= ($bonus_days + $holiday_count) &&
                    $entrollment_det['pending_amount'] == $current_instal_amt) {

                    $bonus_calculation = $current_instal_amt * $bonus_percentage / 100;
                    $entrollment_det['bonus'] = strval(round($bonus_calculation));
                }

                // ================= Installment Loop =================
                foreach($groupBids as $install_detail) {

                    $current_installment_amount = $install_detail->current_installment_amount * $entrol->ticket_type;

                    $paid_due = ($receipts[$entrol->id] ?? collect())
                                    ->where('auction_id',$install_detail->id)
                                    ->sum('received_amount');

                    $pending_due = $current_installment_amount - $paid_due;

                    if($paid_due != $current_installment_amount) {

                        $pending_due_count++; // ✅ count pending

                        $cust_det = [];
                        $cust_det['entrollment_id'] = strval($entrol->id);
                        $cust_det['auction_no'] = strval($install_detail->installment_no);
                        $cust_det['bidding_id'] = strval($install_detail->id);
                        $cust_det['pending_due'] = strval(round($pending_due));
                        $cust_det['discount_on_due'] = "";
                        $cust_det['discount_on_penalty'] = "";
                        $cust_det['bonus_days'] = "";
                        $cust_det['penalty_percentage'] = "";
                        $cust_det['bonus_percentage'] = "";
                        $cust_det['bonus_amount'] = "";
                        $cust_det['instal_pending_day'] = "";
                        $cust_det['paid_amount'] = strval(round($paid_due));
                        $cust_det['current_installment_amount'] = strval(round($install_detail->current_installment_amount));
						

                        // Pending days
                        $fromDate = $paid_due == 0
                            ? $install_detail->auction_date
                            : optional(($receipts[$entrol->id] ?? collect())->where('auction_id',$install_detail->id)->last())->receipt_date;

                        if($fromDate) {
                            $cust_det['instal_pending_day'] =
                                strval((new DateTime($fromDate))->diff(new DateTime())->format('%a'));
                        }

                        // Penalty calculation
                        $penalty_amount = 0;
                        if($branch) {

                            $rate = $entrol->prized_status == 0
                                ? $branch->non_prize_subscriber_penalty
                                : $branch->prize_subscriber_penalty;

                            $pend_days_int = $cust_det['instal_pending_day'] * ($rate / 100);
                            $total_days_int = $pend_days_int / date('t');
                            $penalty_amount = $total_days_int * $current_installment_amount;

                            $cust_det['penalty_amounts'] = strval(round($penalty_amount));
                        } else {
                            $cust_det['penalty_amounts'] = "0";
                        }

                        $total_penalty += round($penalty_amount); // ✅ sum penalty

                        $install_det[] = $cust_det;
                    }
                }
            }

            // ============================================================
            // COMMITMENT CHIT
            // ============================================================
            if($entrol->chit_type == 3) {

                $commitmentRows = $commitments[$entrol->id] ?? collect();
                $total_installment_count = $commitmentRows->count();

                $slab = $slabs[$entrol->slab_id] ?? $entrol->slab_due_det;
                $slabName = optional($slab)->slab_name ?? '';
                $entrollment_det['group_name'] = $slabName !== ''
                    ? ('Commitment-' . $slabName)
                    : 'Commitment';
                $entrollment_det['ticket_no'] = !empty($entrol->ar_no)
                    ? strval($entrol->ar_no)
                    : '-';

                $chitValue = optional(optional($slab)->scheme_det)->chit_value ?? 0;
                $entrollment_det['chit_value'] = strval(round($chitValue));

                $firstCommitment = $commitmentRows->first();
                $entrollment_det['auction_date'] = optional($firstCommitment)->auction_date ?? '0000-00-00';

                $paid = ($receipts[$entrol->id] ?? collect())->sum('received_amount');
                $totalInstallAmount = $commitmentRows->sum('due_amount');
                $entrollment_det['paid_amount'] = strval(round($paid));
                $entrollment_det['install_amount'] = strval(round($totalInstallAmount));
                $entrollment_det['pending_amount'] = strval(round($totalInstallAmount - $paid));

                $currentCommitment = $commitmentRows->last();
                $currentInstalAmt = round(optional($currentCommitment)->due_amount ?? 0);
                if ((float) $entrollment_det['pending_amount'] == $currentInstalAmt) {
                    $fromDate = optional($currentCommitment)->auction_date;
                } else {
                    $lastReceipt = ($receipts[$entrol->id] ?? collect())->last();
                    $fromDate = optional($lastReceipt)->receipt_date ?? optional($currentCommitment)->auction_date;
                }
                if ($fromDate) {
                    $entrollment_det['pending_days'] = strval((new DateTime($fromDate))->diff(new DateTime())->format('%a'));
                }

                foreach($commitmentRows as $install_detail) {

                    $paid_due = ($receipts[$entrol->id] ?? collect())
                                    ->where('auction_id',$install_detail->id)
                                    ->sum('received_amount');

                    $current_instal_amt1 = round($install_detail->due_amount);
                    $pending_due = $current_instal_amt1 - $paid_due;

                    if($paid_due != $current_instal_amt1) {

                        $pending_due_count++; // ✅ count pending

                        $cust_det = [];
                        $cust_det['entrollment_id'] = strval($entrol->id);
                        $cust_det['auction_no'] = strval($install_detail->installment_no);
                        $cust_det['bidding_id'] = strval($install_detail->id);
                        $cust_det['pending_due'] = strval(round($pending_due));
                        $cust_det['discount_on_due'] = "";
                        $cust_det['discount_on_penalty'] = "";
                        $cust_det['bonus_days'] = "0";
                        $cust_det['penalty_percentage'] = "0";
                        $cust_det['bonus_percentage'] = "0";
                        $cust_det['bonus_amount'] = "";
                        $cust_det['instal_pending_day'] = "";
                        $cust_det['paid_amount'] = strval(round($paid_due));
                        $cust_det['current_installment_amount'] = strval(round($install_detail->due_amount));

                        // Pending days
                        $fromDate = $paid_due == 0
                            ? $install_detail->auction_date
                            : optional(($receipts[$entrol->id] ?? collect())->where('auction_id',$install_detail->id)->last())->receipt_date;

                        if($fromDate) {
                            $cust_det['instal_pending_day'] =
                                strval((new DateTime($fromDate))->diff(new DateTime())->format('%a'));
                        }

                        $cust_det['penalty_amounts'] = "0"; // commitment no penalty
                        $install_det[] = $cust_det;
                    }
                }
            }

            // ================= Summary Assign =================
            $entrollment_det['total_installment_count'] = strval($total_installment_count);
            $entrollment_det['pending_due_count']       = strval($pending_due_count);
            $entrollment_det['total_penalty']           = strval($total_penalty);
            $entrollment_det['installments_det']        = $install_det;

            $response_data[] = $entrollment_det;
        }

        return response()->json($response_data,200);

    } catch(\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => $e->getMessage()
        ], 500);
    }
}

public function receipt_details_new(Request $request)
{
    try
    {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $cond = [
            'tenant_id' => $request->tenant_id,
            'receipt_no' => $request->receipt_no
        ];

        // =====================================================
        // ✅ Detect receipt table by prefix
        // =====================================================
        $receiptPrefix = strtoupper(substr($request->receipt_no, 0, 3));
        $receipt_mode  = '';

        if ($receiptPrefix == 'RCP') {     // RCPT-xxxxx

            $receipts = Receipt::selectRaw(
                '*, 
                 SUM(received_amount) as total_received, 
                 SUM(penalty) as total_penalty, 
                 SUM(bonus) as total_bonus, 
                 GROUP_CONCAT(installment_no) as inst_no'
            )
            ->where($cond)
            ->groupBy('receipt_no')
            ->get();

            $receipt_mode = 'Due';
        } 
        elseif ($receiptPrefix == 'ADV') { // ADV-xxxxx

            $receipts = AdvanceReceipt::selectRaw(
                '*, 
                 SUM(receipt_amount) as total_advance'
            )
            ->where($cond)
            ->groupBy('receipt_no')
            ->get();

            $receipt_mode = 'Advance';
        } 
        else {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid receipt number format'
            ], 200);
        }

        // =====================================================
        // ✅ If data exists
        // =====================================================
        if(count($receipts) > 0)
        {   
            $response_data = [];

            $group_id = isset($receipts[0]->group_id) ? ($receipts[0]->group_id) : ""; 

            $response_data['receipt_no']   = strval($receipts[0]->receipt_no);
			$response_data['receipt_date'] = date('d-m-Y', strtotime($receipts[0]->created_at));
$response_data['receipt_time'] = date('h:i A', strtotime($receipts[0]->created_at));
            $response_data['employee_id']  = strval($receipts[0]->employee_id);
            $response_data['employee_name']= strval(optional($receipts[0]->employee_det)->first_name);

            

            $chit_details = ChitDetail::where('id',$receipts[0]->enrollment_id)->get();
            $response_data['ticketno'] = strval($chit_details[0]->ar_no);
			
			$group_name = SlabDue::where('id',$chit_details[0]->slab_id)->pluck('slab_name');
            $response_data['groupname'] = strval($group_name[0]);

            $install_no = isset($receipts[0]->inst_no) ? ($receipts[0]->inst_no) : "0"; 
            $response_data['installmentno'] = strval($install_no);

            $response_data['customer_name'] = strval(optional($receipts[0]->customer_det)->name);
            $response_data['customer_code'] = strval(optional($receipts[0]->customer_det)->customer_code);
            $response_data['mobile_no']     = strval(optional($receipts[0]->customer_det)->mobile_no);
            $response_data['phone_no']      = strval(optional($receipts[0]->customer_det)->phone_no);

            // =====================================================
            // ✅ Amount Calculation
            // =====================================================
            if($receipt_mode == 'Due')
            {
                $total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : 0;
                $total_penalty  = isset($receipts[0]->total_penalty) ? ($receipts[0]->total_penalty) : 0;

                $received_amount = $total_received + $total_penalty;
                $total_advance = 0; 

                if($receipts[0]->advance_available == 1)
                {
                    $advance = AdvanceReceipt::where('recpt_excess_no','=',$receipts[0]->receipt_no)->get();
                    $total_advance = isset($advance[0]->receipt_amount) ? ($advance[0]->receipt_amount) : 0;
                } 

                $total_advance_amount = $total_advance + $received_amount;
                $response_data['receipt_type'] = "Due"; 
            }
            else
            {
                $total_advance_amount = isset($receipts[0]->total_advance) ? 
                                            ($receipts[0]->total_advance) : 0;
                $response_data['receipt_type'] = "Advance";
            }

            // =====================================================
            // ✅ Output Fields
            // =====================================================
            $response_data['received_amount'] = strval(round($total_advance_amount));
            $response_data['penalty_amount']  = strval(round($receipts[0]->total_penalty ?? 0));
            $response_data['bonus_amount']    = strval(round($receipts[0]->total_bonus ?? 0));
            $response_data['payemnt_type']    = strval(optional($receipts[0]->payment_type_det)->payment_name);
            $response_data['debit_to']        = strval($receipts[0]->debit_to);
            $response_data['cheque_no']       = strval($receipts[0]->cheque_no);
            
			$response_data['receipt_date'] = date('d-m-Y', strtotime($receipts[0]->cheque_date));
            $response_data['transaction_no']  = strval($receipts[0]->transaction_no);
            $response_data['transaction_date']= strval($receipts[0]->transaction_date);
            $response_data['bank_name']        = isset($receipts[0]->customer_bank_det) ? 
                                                    ($receipts[0]->customer_bank_det->customer_bank_name) : "";
            $response_data['branch_name']      = strval($receipts[0]->branch_name);
            $response_data['advance_amount']   = "0";

            $arr = [1=>'Yes',0=>'No'];
            $response_data['is_printed'] = $arr[$receipts[0]->printed] ?? 'No';

            $branchId = isset($receipts[0]->branch_id) ? $receipts[0]->branch_id : null;
            if (!$branchId && !empty($receipts[0]->branch_det)) {
                $branchId = $receipts[0]->branch_det->id;
            }
            $response_data['client_address'] = $this->resolveBranchClientAddress($branchId);

            return response()->json($response_data, 200);
        }
        else
        {
            return response()->json([
                'status' => 'Error',
                'msg' => \Lang::get('api.no_data_found')
            ], 200);
        }
    }
    catch(\Exception $e)
    {
        return response()->json([
            'status' => 'Error',
            'msg' => \Lang::get('api.global_error')
        ], 401);
    }
}
public function feedback_customers(Request $request)
{
    try {

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $remarks = Remark::with('customer_det')
            ->where('tenant_id', $request->tenant_id)
            ->where('customer_id', $request->customer_id)
            ->orderBy('remark_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $response_data = [];

        foreach ($remarks as $feedback) {
            $response_data[] = [
                'customer_id'        => (string) ($feedback->customer_id ?? 0),
                'customer_name'      => $feedback->customer_det->name ?? '',
                'remark_date'        => date('d-m-Y', strtotime($feedback->remark_date)) ?? '0000-00-00',
                'next_followup_date' => date('d-m-Y', strtotime($feedback->next_followup_date)) ?? '0000-00-00',
                'remarks'            => $feedback->remarks ?? '',
            ];
        }

        return response()->json([
            'status' => 'Success',
            'data'   => $response_data
        ], 200);

    } catch (\Exception $e) {
        \Log::error("feedback_reports error: ".$e->getMessage());

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()
        ], 500);
    }
}

public function get_payment_report_mobile(Request $request)
{
    try {
		
		 DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $query = Payment::query()
            ->where('payment_for', '!=', 5)
            ->with([
                'group_det:id,group_name',
                'enrol_det:id,ticket_no,group_id',
                'customer_det:id,name,last_name',
                'payment_type_det:id,payment_name',
                'branch_det:id,branch_name'
            ]);

        /* -------------------------
           Filters
        ------------------------- */

        if (!empty($request->branch_id) && $request->branch_id > 0) {
            $query->where('branch_id', $request->branch_id);
        }

        if (!empty($request->group_id)) {
            $query->where('group_id', $request->group_id);
        }

        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereBetween('payment_date', [
                $request->from_date,
                $request->to_date
            ]);
        } elseif (!empty($request->from_date)) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        } elseif (!empty($request->to_date)) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        /* -------------------------
           Response Mapping
        ------------------------- */

        $data = $payments->map(function ($payment) {
            return [
                'group_name'    => $payment->group_det->group_name ?? '',
                'ticket_no'     => $payment->enrol_det->ticket_no ?? '',
                'customer_name' => trim(
                    ($payment->customer_det->name ?? '') . ' ' .
                    ($payment->customer_det->last_name ?? '')
                ),
                'amount'        => $payment->total_amount ?? 0,
                'payment_mode'  => $payment->payment_type_det->payment_name ?? '',
                'payment_date'  => $payment->payment_date
                    ? date('d-m-Y', strtotime($payment->payment_date))
                    : '',
                'branch_name'   => $payment->branch_det->branch_name ?? '',
            ];
        });

        return response()->json([
            'status' => 'Success',
            'msg'    => $data->count() ? '' : __('api.no_data_found'),
            'data'   => $data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => __('api.global_error')
        ], 500);
    }
}

public function get_enrol_report_mobile(Request $request)
{
    try {
		
		DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $query = ChitDetail::query()
            ->with([
                'group_det:id,group_name',
                'customer_det:id,name,last_name',
                'branch_det:id,branch_name'
            ])
            ->orderBy('id', 'desc');

        /* -------------------------
           Filters
        ------------------------- */

        if (!empty($request->tenant_id)) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if (!empty($request->group_id)) {
            $query->where('group_id', $request->group_id);
        }

        if (!empty($request->branch_id) && $request->branch_id > 0) {
            $query->where('branch_id', $request->branch_id);
        }

        $enrolls = $query->get();

        /* -------------------------
           Response Mapping
        ------------------------- */

        $data = $enrolls->map(function ($enrol) {

            /* Referred By */
            $referredBy = '';

            if ($enrol->agent_type == 1) {
                $cust = Customer::find($enrol->introducer_id);
                $referredBy = $cust->name ?? '';
            } elseif (in_array($enrol->agent_type, [2, 3])) {
                $emp = Employee::find($enrol->introducer_id);
                $referredBy = $emp
                    ? trim($emp->first_name . ' ' . $emp->last_name)
                    : '';
            } else {
                $referredBy = $enrol->introducer_name ?? '';
            }

            return [
                'group_name'    => $enrol->group_det->group_name ?? '',
                'ticket_no'     => $enrol->ticket_no ?? '',
                'customer_name' => trim(
                    ($enrol->customer_det->name ?? '') . ' ' .
                    ($enrol->customer_det->last_name ?? '')
                ),
                'referred_by'   => $referredBy,
                'branch_name'   => $enrol->branch_det->branch_name ?? '',
            ];
        });

        return response()->json([
            'status' => 'Success',
            'msg'    => $data->count() ? '' : __('api.no_data_found'),
            'data'   => $data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => __('api.global_error')
        ], 500);
    }
}

public function store_followup_mobile(Request $request)
{
	DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');
		
    $validator = Validator::make($request->all(), [
        'tenant_id'                 => 'required|integer',
        'branch_id'                 => 'required|integer',
        'lead_management_id'        => 'required|integer',
        'employee_id'               => 'required|integer',
        'followup_date'             => 'required|date',
        'next_followup_date'        => 'required|date',
        'followup_status_type_id'   => 'required|integer',
        'remarks'                   => 'nullable|string',
        'created_by'                => 'nullable|integer'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg'    => $validator->errors()->first()
        ], 200);
    }

    try {

        DB::beginTransaction();

        $followup = new Followup();
        $followup->tenant_id               = $request->tenant_id;
        $followup->branch_id               = $request->branch_id;
        $followup->lead_management_id      = $request->lead_management_id;
        $followup->employee_id             = $request->employee_id;
        $followup->followup_date           = $request->followup_date;
        $followup->next_followup_date      = $request->next_followup_date;
        $followup->remarks                 = $request->remarks ?? '';
        $followup->followup_status_type_id = $request->followup_status_type_id;
        $followup->created_by              = $request->created_by ?? 0;
        $followup->status                  = 1;

        $followup->save();

        /* Update lead status */
        LeadManagement::where('id', $request->lead_management_id)
            ->update([
                'followup_status_type_id' => $request->followup_status_type_id
            ]);

        DB::commit();

        return response()->json([
            'status' => 'Success',
            'msg'    => __('api.success_followup_added'),
            'data'   => [
                'followup_id'       => $followup->id,
                'lead_management_id'=> $followup->lead_management_id,
                'followup_date'     => $followup->followup_date,
                'next_followup_date'=> $followup->next_followup_date
            ]
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => 'Error',
            'msg'    => __('api.global_error')
        ], 500);
    }
}


public function get_followups_mobile(Request $request)
{
    try {
		
		DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* Validation */
        if (
            empty($request->tenant_id) ||
            empty($request->branch_id) ||
            empty($request->lead_management_id)
        ) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Required parameters missing'
            ], 200);
        }

        /* Fetch followups */
        $followups = Followup::where('tenant_id', $request->tenant_id)
            ->where('branch_id', $request->branch_id)
            ->where('lead_management_id', $request->lead_management_id)
            ->orderBy('followup_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        if ($followups->isEmpty()) {
            return response()->json([
                'status' => 'Success',
                'msg' => __('api.no_data_found'),
                'data' => []
            ], 200);
        }

        /* Latest followup ID */
        $latestId = $followups->first()->id;

        $data = [];

        foreach ($followups as $followup) {

            /* Employee name (SAFE) */
            $employeeName = '';
            if ($followup->employee_id) {
                $emp = Employee::find($followup->employee_id);
                if ($emp) {
                    $employeeName = trim($emp->first_name . ' ' . $emp->last_name);
                }
            }

            /* Status name (SAFE) */
            $statusName = '';
            if ($followup->followup_status_type_id) {
                $status = FollowupStatusType::find($followup->followup_status_type_id);
                $statusName = $status->followup_status_name ?? '';
            }

            $data[] = [
                'followup_id'        => $followup->id,
                'followup_date'      => $followup->followup_date
                                        ? date('d-m-Y', strtotime($followup->followup_date))
                                        : '',
                'next_followup_date' => $followup->next_followup_date
                                        ? date('d-m-Y', strtotime($followup->next_followup_date))
                                        : '',
                'remarks'            => $followup->remarks ?? '',
                'status_name'        => $statusName,
                'employee_name'      => $employeeName,
                'is_latest'          => ($followup->id == $latestId)
            ];
        }

        return response()->json([
            'status' => 'Success',
            'msg' => '',
            'data' => $data
        ], 200);

    } catch (\Exception $e) {

        // TEMP: return error message for debugging
        return response()->json([
            'status' => 'Error',
            'msg' => $e->getMessage()
        ], 500);
    }
}

public function get_lead_report_mobile(Request $request)
{
    try {

        /* =========================
           DB SWITCH
        ========================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* =========================
           BASE QUERY
        ========================= */
        $query = LeadManagement::where('tenant_id', $request->tenant_id);
           /* Branch filter (OPTIONAL) */
        if (!empty($request->branch_id) && $request->branch_id > 0) {
            $query->where('branch_id', $request->branch_id);
        }

        /* =========================
           DATE FILTERS
        ========================= */
        if (!empty($request->from_date) && !empty($request->to_date)) {
            $query->whereBetween(
                DB::raw('DATE(created_at)'),
                [$request->from_date, $request->to_date]
            );
        } elseif (!empty($request->from_date)) {
            $query->whereDate('created_at', '>=', $request->from_date);
        } elseif (!empty($request->to_date)) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $leads = $query->orderBy('id', 'desc')->get();

        if ($leads->isEmpty()) {
            return response()->json([
                'status' => 'Success',
                'msg'    => __('api.no_data_found'),
                'data'   => []
            ], 200);
        }

        /* =========================
           RESPONSE
        ========================= */
        $data = [];

        foreach ($leads as $lead) {

            /* Branch Name */
            $branchName = '';
            if ($lead->branch_id) {
                $branch = Branch::find($lead->branch_id);
                $branchName = $branch->branch_name ?? '';
            }

            /* Employee Name */
            $employeeName = '';
            if ($lead->employee_id) {
                $emp = Employee::find($lead->employee_id);
                if ($emp) {
                    $employeeName = trim($emp->first_name . ' ' . $emp->last_name);
                }
            }

            /* Followup Status */
            $followupStatus = '';
            if ($lead->followup_status_type_id) {
                $status = FollowupStatusType::find($lead->followup_status_type_id);
                $followupStatus = $status->status_name ?? '';
            }

            /* LAST FOLLOW-UP (IMPORTANT) */
            $lastFollowup = Followup::where('lead_management_id', $lead->id)
                ->orderBy('followup_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $data[] = [
                'lead_id'                    => $lead->id,
                'lead_customer_name'         => $lead->lead_customer_name,
                'mobile_no'                  => $lead->mobile_no ?? '',
                'branch_name'                => $branchName,
                'employee_name'              => $employeeName,
                'followup_status'            => $followupStatus,
                'last_followup_next_date'    => $lastFollowup && $lastFollowup->next_followup_date
                                                ? date('d-m-Y', strtotime($lastFollowup->next_followup_date))
                                                : '',
                'last_followup_remarks'      => $lastFollowup->remarks ?? '',
                'status'                     => ($lead->status == 1) ? 'Active' : 'Inactive',
                'created_date'               => date('d-m-Y', strtotime($lead->created_at))
            ];
        }

        return response()->json([
            'status' => 'Success',
            'msg'    => '',
            'data'   => $data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()
        ], 500);
    }
}

public function day_book_mobile(Request $request)
{
	
	  DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');
    try
    {
        $tot_cr = 0;
        $op = 0;

        if (isset($request->account_date)) {

            /* =========================
               MOBILE : ONE DATE INPUT
            ========================= */
            $account_date = $request->account_date;

            // MOBILE CHANGE
            $to_date = $account_date;

            $test = date('Y-m-d', strtotime('-1 day', strtotime($account_date)));

            // MOBILE CHANGE – FIXED FIN YEAR
            $start_date = "2025-04-01";
            $end_date   = $test;

            /* =========================
               OPENING BALANCE
            ========================= */
            $before_advance_amount = BeforeEnrolReceipt::whereBetween('received_date',[$start_date,$end_date])
                ->where('status',1)->where('customer_id','!=','0')
                ->whereNull('deleted_at')->sum('amount');

            $payments_amount = Payment::where('customer_id','!=',1)
                ->whereBetween('payment_date',[$start_date,$end_date])
                ->where('status',1)->whereNull('deleted_at')->sum('total_amount');

            $others_amount = OtherCharge::whereBetween('received_date',[$start_date,$end_date])
                ->where('status',1)->whereNull('deleted_at')->sum('amount');

            $advance_rcpts_amount = AdvanceReceipt::whereBetween('receipt_date',[$start_date,$end_date])
                ->where('status',1)->where('payment_type_id','!=',9)
                ->whereNull('deleted_at')->sum('receipt_amount');

            $rcpts_amount = Receipt::whereBetween('receipt_date',[$start_date,$end_date])
                ->where('payment_type_id','!=',7)->where('payment_type_id','!=',8)
                ->where('status',1)->whereNull('deleted_at')->sum('received_amount');

            $rcpts_amount_penalty = Receipt::whereBetween('receipt_date',[$start_date,$end_date])
                ->where('payment_type_id','!=',7)->where('payment_type_id','!=',8)
                ->where('status',1)->whereNull('deleted_at')->sum('penalty');

            $expensesdebit_payment_amt = 0;
            $expensescredit_rec_amt = 0;
            $opening_b = 0;

            if ($request->tenant_id == 42) {

                $expensescredit_rec_amt = DB::table("account_expenses")
                    ->where('branch_id',$request->branch_id)
                    ->where('receipt_no','yes')
                    ->where('credit_account_head','!=',0)
                    ->where('account_type',1)
                    ->where('status',1)
                    ->whereNull('deleted_at')
                    ->whereBetween('entry_date',[$start_date,$end_date])
                    ->sum('credit_amount');

                $expensesdebit_payment_amt = DB::table("account_expenses")
                    ->where('branch_id',$request->branch_id)
                    ->where('receipt_no','yes')
                    ->where('debit_account_head','!=',0)
                    ->where('account_type',2)
                    ->where('status',1)
                    ->whereNull('deleted_at')
                    ->whereBetween('entry_date',[$start_date,$end_date])
                    ->sum('debit_amount');

                $opening_b = 0;
            }

            /* =========================
               DAY ENTRIES
            ========================= */

            if ($request->branch_id == 0) {

                $before_advance_rcpts = DB::table("before_enrol_receipts")
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('received_date',$account_date)
                    ->select("id","amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id");

                $payments = DB::table("payments")
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('payment_date',$account_date)
                    ->where('payment_for',1)
                    ->select("id","total_amount as amount","receipt_no","receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");

                $expenses = DB::table("account_expenses")
                    ->where('debit_account_head','!=',0)
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('entry_date',$account_date)
                    ->select("id","debit_amount as amount","voucher_no as receipt_no",
                        "receipt_passbook_mode","debit_account_head as enrollment_id",
                        "credit_account_head as customer_id","debit_account_head as payment_type_id");

                $expensescredit = DB::table("account_expenses")
                    ->where('credit_account_head','!=',0)
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('entry_date',$account_date)
                    ->select("id","credit_amount as amount","voucher_no as receipt_no",
                        "receipt_passbook_mode","debit_account_head as enrollment_id",
                        "credit_account_head as customer_id","credit_account_head as payment_type_id");

                $advance_rcpts = DB::table("advance_receipts")
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('payment_type_id','!=',9)
                    ->where('receipt_date',$account_date)
                    ->select("id","receipt_amount as amount","receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");

                $receipts = DB::table("receipts")
                    ->where('payment_type_id','!=',7)
                    ->where('status',1)->whereNull('deleted_at')
                    ->where('receipt_date',$account_date)
                    ->select("id","received_amount as amount","receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id")
                    ->unionAll($advance_rcpts)
                    ->unionAll($payments)
                    ->unionAll($expenses)
                    ->unionAll($expensescredit)
                    ->get();
            }
            else {

                $before_advance_rcpts = DB::table("before_enrol_receipts")
                    ->where('branch_id',$request->branch_id)
                    ->where('status',1)->whereNull('deleted_at')
                    ->whereBetween('received_date',[$account_date,$to_date])
                    ->select("id","amount","receipt_no","receipt_passbook_mode","payment_type_id as enrollment_id","customer_id","payment_type_id");

                $payments = DB::table("payments")
                    ->where('branch_id',$request->branch_id)
                    ->where('payment_for',1)
                    ->where('status',1)->whereNull('deleted_at')
                    ->whereBetween('payment_date',[$account_date,$to_date])
                    ->select("id","total_amount as amount","id as receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");

                $advance_rcpts = DB::table("advance_receipts")
                    ->where('branch_id',$request->branch_id)
                    ->where('payment_type_id','!=',9)
                    ->where('status',1)->whereNull('deleted_at')
                    ->whereBetween('receipt_date',[$account_date,$to_date])
                    ->select("id","receipt_amount as amount","receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");

                $other_rec = DB::table("other_charges")
                    ->where('branch_id',$request->branch_id)
                    ->where('status',1)->whereNull('deleted_at')
                    ->whereBetween('received_date',[$account_date,$to_date])
                    ->select("id","amount","receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id");

                $receipts = DB::table("receipts")
                    ->where('branch_id',$request->branch_id)
                    ->where('payment_type_id','!=',7)
                    ->where('payment_type_id','!=',8)
                    ->where('status',1)->whereNull('deleted_at')
                    ->whereBetween('receipt_date',[$account_date,$to_date])
                    ->select("id","received_amount as amount","receipt_no",
                        "receipt_passbook_mode","enrollment_id","customer_id","payment_type_id")
                    ->unionAll($advance_rcpts)
                    ->unionAll($payments)
                    ->unionAll($other_rec)
                    ->get();
            }
$day_credit_total = 0;
$day_debit_total  = 0; 
            /* =========================
               FULL FOREACH (UNCHANGED)
            ========================= */
           foreach($receipts as $rcpt){
			   
    //print_R($rcpt->receipt_no);echo '-';
	if($rcpt->receipt_passbook_mode == "expenses"){
        
	$rcpt->id = $rcpt->id;
	$debit_remarks = AccountExpense::where('id',$rcpt->id)->pluck('debit_remarks');
	$credit_remarks = AccountExpense::where('id',$rcpt->id)->pluck('credit_remarks');
	
	$rcpt->receipt_no = $rcpt->receipt_no;
	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
    
    if($rcpt->enrollment_id > 0 ){
        $account_head = AccountHead::where('id',$rcpt->enrollment_id)->pluck('name');
        $rcpt->receipt_passbook_mode = $account_head[0] . ' / ' . $debit_remarks[0] . ' / ' . $credit_remarks[0];
         $rcpt->amount = $rcpt->amount;
        $rcpt->payment_amount = '0';
		$rcpt->type = 'credit';
		
				$rcpt->payment_type = $account_head[0];
    }
    else{
        $account_head = AccountHead::where('id',$rcpt->customer_id)->pluck('name');
        $rcpt->receipt_passbook_mode = $account_head[0] . ' / ' . $credit_remarks[0] . ' / ' . $debit_remarks[0];
       
		$rcpt->payment_type = $account_head[0];
		
		 $rcpt->payment_amount = $rcpt->amount;
        $rcpt->amount = '0';
		$rcpt->type = 'debit';
    }
	$rcpt->customer_id = $rcpt->customer_id;
	$pen = 0;
	}  
    else if($rcpt->receipt_passbook_mode == "payment"){
        
    $rcpt->id = $rcpt->id;
	$rcpt->payment_amount = $rcpt->amount;
    $rcpt->amount = '0';
	$rcpt->receipt_no = "PVOC-" . $rcpt->receipt_no;
	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
	$rcpt->enrollment_id = $rcpt->enrollment_id;
	$rcpt->type = 'debit';
	if($rcpt->customer_id > 0){
	$customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
	$last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
	$group_id = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('group_id');
	$ticket_no = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('ticket_no');
	if($group_id[0] > 0){
	$group_name = Group::where('id',$group_id[0])->pluck('group_name');
	}
	}
	$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->pluck('payment_name');
	$rcpt->payment_type = $payment_type[0];
    $rcpt->receipt_passbook_mode = 'Payment Entry of ' . $customer_name[0] . ' ' . $last_name[0] . '- Group name ' . $group_name[0] . ' Ticket No ' . $ticket_no[0];	
	$pen = 0;
	}
    else if($rcpt->receipt_passbook_mode == "op"){
        
        $rcpt->id = $rcpt->id;
        $rcpt->amount = $rcpt->amount;
        $op = $rcpt->amount;
        $rcpt->payment_amount = '0';
        $rcpt->receipt_no = "";
        $rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        $rcpt->enrollment_id = 0;
        $rcpt->receipt_passbook_mode = "OPENING BALANCE";	
		$pen = 0;
        } else if($rcpt->receipt_passbook_mode == "other_rec") {
			
        	$rcpt->id = $rcpt->id;
        	$rcpt->amount = $rcpt->amount;
        	$rcpt->receipt_no = $rcpt->receipt_no;
        	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        	$rcpt->enrollment_id = $rcpt->enrollment_id;
            $rcpt->receipt_passbook_mode = "";
			$rcpt->type = 'credit';
            if($rcpt->customer_id>0){
                $customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
                $last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
                $charge_type_id = OtherCharge::where('id',$rcpt->id)->withTrashed()->pluck('charge_type_id');
                $charge_name = ChargeType::where('id',$charge_type_id[0])->withTrashed()->pluck('charge_name');
				$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->withTrashed()->pluck('payment_name');
				$rcpt->payment_type = $payment_type[0];
                $rcpt->receipt_passbook_mode = $charge_name[0] . ' of ' . $customer_name[0] . ' ' . $last_name[0];   
            }		
			$pen = 0;
    	} else {
			
        	$rcpt->id = $rcpt->id;
			if($rcpt->receipt_passbook_mode == "rcpt"){
				$penalty = Receipt::where('id',$rcpt->id)->pluck('penalty');
				$pen = $penalty[0];
			} else {
				$pen = 0;
			}
			
        	$rcpt->amount = $rcpt->amount + $pen;
        	$rcpt->receipt_no = $rcpt->receipt_no;
        	$rcpt->receipt_passbook_mode = $rcpt->receipt_passbook_mode;
        	$rcpt->enrollment_id = $rcpt->enrollment_id;
            $rcpt->receipt_passbook_mode = "";
			$rcpt->type = 'credit';
            if($rcpt->customer_id>0){
				$group_name = '';
				$ticket_no = '';
                $customer_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('name');
				$last_name = Customer::where('id',$rcpt->customer_id)->withTrashed()->pluck('last_name');
				 
				if($rcpt->enrollment_id>0){
					
				$group_id = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('group_id');
				if($group_id[0]>0){
				$ticket_no = ChitDetail::where('id',$rcpt->enrollment_id)->withTrashed()->pluck('ticket_no');
			
				$group_name = Group::where('id',$group_id[0])->withTrashed()->pluck('group_name');
				
				$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->withTrashed()->pluck('payment_name');
				$rcpt->payment_type = $payment_type[0];
                $rcpt->receipt_passbook_mode = 'Receipt Entry of ' . $customer_name[0] . ' ' . $last_name[0] . '- Group name ' . $group_name[0] . ' Ticket No ' . $ticket_no[0];  
				} else {
				$rcpt->receipt_passbook_mode = 'Commitment Chits ';
				}
				}
			
            }		
			
    	}
		if (isset($rcpt->type)) {
    if ($rcpt->type == 'credit') {
        $day_credit_total += (float) $rcpt->amount;
    }
    else if ($rcpt->type == 'debit') {
        $day_debit_total += (float) $rcpt->payment_amount;
    }
}
		
	}
            }
			
			
  
            $open_bal =
                $before_advance_amount +
                $advance_rcpts_amount +
                $rcpts_amount +
                $others_amount +
                $expensescredit_rec_amt +
                $rcpts_amount_penalty +
                $opening_b -
                $payments_amount -
                $expensesdebit_payment_amt;
				
				$closing_balance = $open_bal + $day_credit_total - $day_debit_total;

            $dayBookBranchName = '';
            if (!empty($request->branch_id) && (int) $request->branch_id > 0) {
                $dayBookBranch = Branch::find($request->branch_id);
                $dayBookBranchName = $dayBookBranch->branch_name ?? '';
            }
            foreach ($receipts as $rcpt) {
                $rcpt->branch_name = $dayBookBranchName;
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'opening_balance' => $open_bal,
				'closing_balance' => $closing_balance,
                'data' => $receipts,
                'date' => date('d-m-Y',strtotime($account_date))
            ], 200);
        
    }
    catch (\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => \Lang::get('api.global_error')
        ], 401);
    }
}


public function employee_change_password(Request $request)
{
    $request->validate([
        'db'            => 'required|string',
        'tenant_id'     => 'required',
        'user_id'       => 'required|integer',
        'old_password'  => 'required',
        'password'      => 'required|min:6|different:old_password',
    ]);

    try {
        /* ===============================
           SWITCH TENANT DATABASE
        =============================== */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        DB::beginTransaction();

        /* ===============================
           FETCH USER (MAIN DB)
        =============================== */
        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'User not found'
            ], 404);
        }

        /* ===============================
           CHECK OLD PASSWORD (bcrypt)
        =============================== */
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'Old password is incorrect'
            ], 422);
        }

        /* ===============================
           CHECK OLD PASSWORD (MD5 DB)
        =============================== */
        $legacyUser = DB::connection('mysql1')
            ->table('users')
            ->where('tenant_id', $request->tenant_id)
            ->where('original_id', $request->user_id)
            ->first();

        if (!$legacyUser) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'Legacy user not found'
            ], 404);
        }

        if ($legacyUser->password !== md5($request->old_password)) {
            return response()->json([
                'status' => 'Error',
                'msg'    => 'Old password is incorrect'
            ], 422);
        }

        /* ===============================
           UPDATE PASSWORDS
        =============================== */
        $user->password = Hash::make($request->password);
        $user->save();

        DB::connection('mysql1')->table('users')
            ->where('tenant_id', $request->tenant_id)
            ->where('original_id', $request->user_id)
            ->update([
                'password' => md5($request->password)
            ]);

        DB::commit();

        return response()->json([
            'status' => 'Success',
            'msg'    => 'Password updated successfully'
        ], 200);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => 'Error',
            'msg'    => $e->getMessage()
        ], 500);
    }
}


public function forget_password_request(Request $request)
{
    try {

        if (
            empty($request->email) ||
            empty($request->device_id)
        ) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid request'
            ], 401);
        }

        /* ================= MASTER DB ================= */

        $member = DB::connection('mysql1')
            ->table('users')
            ->where('email', $request->email)
            ->first();

        if (!$member) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Email not registered'
            ], 401);
        }

        /* ================= TENANT DB ================= */

        $tenant = DB::connection('mysql1')
            ->table('tenants')
            ->where('id', $member->tenant_id)
            ->first();

        if (!$tenant) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid tenant'
            ], 401);
        }

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $tenant->slag_url);
        DB::reconnect('mysql');

        /* ================= APP USER ================= */

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'User not found'
            ], 401);
        }

        /* ================= DEVICE ================= */

        $device = Device::where([
            'android_id' => $request->device_id,
            'tenant_id'  => $user->tenant_id
        ])->first();

        if (!$device) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Device not registered'
            ], 401);
        }

        /* ================= DEVICE LOGIN (REUSE) ================= */

        $device_login = DeviceLogin::where([
            'device_id' => $device->id,
            'user_id'   => $user->id
        ])->orderBy('id','desc')->first();

        if (!$device_login) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Device login not found'
            ], 401);
        }

        /* ================= RESET OTP ================= */

        $device_login->otp_verification_status = 0;
        $device_login->updated_at = now();
        $device_login->save();

        // 🔐 Send OTP (mock)
        $otp = '1111';
		
		/* ================= EMPLOYEE ================= */

$emp = Employee::where('id', $user->employee_id)->first();

/* ================= EMAIL FALLBACK ================= */

$emp_email = (!empty($emp) && !empty($emp->email))
    ? $emp->email
    : 'androiddeveloper@mazenetsolution.com'; // common mail id

$employee_name = (!empty($emp))
    ? trim($emp->first_name . ' ' . $emp->last_name)
    : 'User';

/* ================= MAIL DATA ================= */

$data = [
    'otp'      => $otp,
    'emp_name'=> $employee_name
];

/* ================= SEND MAIL ================= */

Mail::send('mail', $data, function ($message) use ($emp_email, $employee_name) {

    $message->to($emp_email, $employee_name)
            ->subject('OTP Mail');

    $message->from(
        env('MAIL_FROM_ADDRESS', 'info@mazenettech.com'),
        env('MAIL_FROM_NAME', 'Mazenet')
    );

    $message->cc('ashokraj@mazenetsolution.com', 'Ashokraj');
});


        return response()->json([
            'status' => 'OTP Sent',
            'msg' => 'OTP sent successfully',
            'user_id' => $user->id,
            'device_primary_id' => $device->id,
            'otp' => $otp, // ❌ remove in prod
            'db' => $tenant->slag_url
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'status' => 'Error',
            'msg' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 401);
    }
}

public function forget_password_reset(Request $request)
{
    try {

        if (
            empty($request->user_id) ||
            empty($request->password) ||
            empty($request->db)
        ) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Invalid request'
            ], 401);
        }

        /* ================= TENANT DB ================= */

        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        /* ================= VERIFY OTP ================= */

        
		$device_login = DeviceLogin::where([
                'device_id' => $request->device_primary_id,
                'user_id'   => $request->user_id
            ])
            ->orderBy('id','desc')
            ->first();   // ✅ NOT get()

        if (!$device_login) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'OTP not verified'
            ], 401);
        }

        DB::beginTransaction();

        /* ================= APP PASSWORD ================= */

        $user = User::find($request->user_id);
        $user->password = Hash::make($request->password);
        $user->save();

        /* ================= MASTER PASSWORD ================= */

        DB::connection('mysql1')
            ->table('users')
            ->where('tenant_id', $user->tenant_id)
            ->where('original_id', $user->id)
            ->update([
                'password' => md5($request->password)
            ]);

        /* ================= RESET OTP STATUS ================= */
        // optional but recommended
        $device_login->otp_verification_status = 1;
        $device_login->save(); 

        DB::commit();

        return response()->json([
            'status' => 'Success',
            'msg' => 'Password reset successfully'
        ], 200);
 
    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => 'Error',
            'msg' => 'Password reset failed',
            'error' => $e->getMessage()
        ], 401);
    }
}


public function mobile_employee_dashboard(Request $request)
{
    try {

        /* ================================
           DB CONNECTION
        ================================= */
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');

        $tenantId = $request->tenant_id;
        /* users.id — same id returned as logged_user_id on mobile login */
        $userId   = isset($request->user_id) ? (int) $request->user_id : 0;
        $reqBranchId = isset($request->branch_id) ? (int) $request->branch_id : 0;
        $reqEmployeeId = isset($request->employee_id) ? (int) $request->employee_id : 0;

        if ($userId <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'user_id is required (users.id)'
            ], 200);
        }

        $today      = date('Y-m-d');
        $monthStart = date('Y-m-01');

        /* ================================
           GET USER (users table)
        ================================= */
        $user = User::select(
                    'users.id',
                    'users.employee_id',
                    'users.branch_id'
                )
                ->where('users.tenant_id', $tenantId)
                ->where('users.id', $userId)
                ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 200);
        }

        $loggedEmpId = (int) $user->employee_id;

        /* ================================
           MAPPED BRANCH / EMPLOYEE (same as mobile_list_branches)
        ================================= */
        $allowedBranchIds = [];
        $allowedEmployeeIds = [];

        $bMap = DB::table('user_branch_mapping')
            ->where('user_id', $userId)
            ->where('status', 1);
        if (!empty($tenantId)) {
            $bMap->where('tenant_id', $tenantId);
        }
        foreach ($bMap->pluck('branch_id') as $bid) {
            $allowedBranchIds[] = (int) $bid;
        }

        $eMap = DB::table('user_employee_mapping')
            ->where('user_id', $userId)
            ->where('status', 1);
        if (!empty($tenantId)) {
            $eMap->where('tenant_id', $tenantId);
        }
        foreach ($eMap->pluck('employee_id') as $eid) {
            $allowedEmployeeIds[] = (int) $eid;
        }

        if (empty($allowedBranchIds) && !empty($user->branch_id)) {
            $allowedBranchIds[] = (int) $user->branch_id;
        }
        if (empty($allowedEmployeeIds) && !empty($user->employee_id)) {
            $allowedEmployeeIds[] = (int) $user->employee_id;
        }

        $allowedBranchIds = array_values(array_unique(array_filter($allowedBranchIds)));
        $allowedEmployeeIds = array_values(array_unique(array_filter($allowedEmployeeIds)));

        if ($reqBranchId > 0 && !in_array($reqBranchId, $allowedBranchIds, true)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid branch for this user'
            ], 200);
        }

        /* Employees in scope: mapped list, optionally narrowed by branch */
        $scopedEmployeeIds = $allowedEmployeeIds;
        if ($reqBranchId > 0 && !empty($scopedEmployeeIds)) {
            $scopedEmployeeIds = Employee::where('tenant_id', $tenantId)
                ->where('branch_id', $reqBranchId)
                ->whereIn('id', $allowedEmployeeIds)
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->values()
                ->all();
        }

        if ($reqEmployeeId > 0) {
            if (!in_array($reqEmployeeId, $allowedEmployeeIds, true)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid employee for this user'
                ], 200);
            }
            if ($reqBranchId > 0) {
                $empBranchOk = Employee::where('tenant_id', $tenantId)
                    ->where('id', $reqEmployeeId)
                    ->where('branch_id', $reqBranchId)
                    ->exists();
                if (!$empBranchOk) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Employee does not belong to the selected branch'
                    ], 200);
                }
            }
            $scopedEmployeeIds = [$reqEmployeeId];
        }

        /* Branch filter on transactional rows */
        $branchIdsForQuery = [];
        if ($reqBranchId > 0) {
            $branchIdsForQuery = [$reqBranchId];
        } elseif (!empty($allowedBranchIds)) {
            $branchIdsForQuery = $allowedBranchIds;
        }

        /* ================================
           QUERY SCOPE (tenant + branch + employee)
        ================================= */
        $dashboardScope = function ($query) use ($tenantId, $branchIdsForQuery, $scopedEmployeeIds) {
            $query->where('tenant_id', $tenantId);
            if (!empty($branchIdsForQuery)) {
                $query->whereIn('branch_id', $branchIdsForQuery);
            }
            if (empty($scopedEmployeeIds)) {
                $query->whereRaw('1 = 0');
            } elseif (count($scopedEmployeeIds) === 1) {
                $query->where('employee_id', $scopedEmployeeIds[0]);
            } else {
                $query->whereIn('employee_id', $scopedEmployeeIds);
            }
        };

        /* Attendance / cash: one selected employee → use them; aggregate view → logged-in employee */
        $attendanceEmpId = (count($scopedEmployeeIds) === 1)
            ? $scopedEmployeeIds[0]
            : $loggedEmpId;

        /* ================================
           1️⃣ TODAY COLLECTION
        ================================= */
        $receiptAmt = Receipt::query()
            ->whereDate('receipt_date', $today)
            ->where($dashboardScope)
            ->selectRaw('SUM(received_amount + penalty) as total')
            ->value('total') ?? 0;

        $advanceAmt = AdvanceReceipt::query()
            ->whereDate('receipt_date', $today)
            ->where($dashboardScope)
            ->sum('receipt_amount');

        $otherAmt = OtherCharge::query()
            ->whereDate('received_date', $today)
            ->where($dashboardScope)
            ->sum('amount');

        $beforeAmt = BeforeEnrolReceipt::query()
            ->whereDate('received_date', $today)
            ->where($dashboardScope)
            ->sum('amount');

        $todaysCollection = $receiptAmt + $advanceAmt + $otherAmt + $beforeAmt;

        /* ================================
           2️⃣ RECEIPTS GENERATED (TODAY)
        ================================= */
        $receiptGenerated = Receipt::query()
            ->whereDate('receipt_date', $today)
            ->where($dashboardScope)
            ->distinct('receipt_no')
            ->count('receipt_no');

        $advanceGenerated = AdvanceReceipt::query()
            ->whereDate('receipt_date', $today)
            ->where($dashboardScope)
            ->where('recpt_excess_no', '=', '')
            ->distinct('receipt_no')
            ->count('receipt_no');

        $beforeGenerated = BeforeEnrolReceipt::query()
            ->whereDate('received_date', $today)
            ->where($dashboardScope)
            ->distinct('receipt_no')
            ->count('receipt_no');

        $receiptsGenerated = $receiptGenerated + $advanceGenerated + $beforeGenerated;

        /* ================================
           3️⃣ FOLLOWUP COUNT (TODAY)
        ================================= */
        $todayFollowup = Remark::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('next_followup_date', $today)
            ->when(!empty($branchIdsForQuery), function ($q) use ($branchIdsForQuery) {
                $q->whereIn('branch_id', $branchIdsForQuery);
            })
            ->where(function ($q) use ($scopedEmployeeIds) {
                if (empty($scopedEmployeeIds)) {
                    $q->whereRaw('1 = 0');
                } elseif (count($scopedEmployeeIds) === 1) {
                    $q->where('employee_id', $scopedEmployeeIds[0]);
                } else {
                    $q->whereIn('employee_id', $scopedEmployeeIds);
                }
            })
            ->count();

        /* ================================
           4️⃣ ATTENDANCE
        ================================= */
        $presentCount = 0;
        $absentCount = 0;
        $isAttendanceMarked = 0;

        if ($attendanceEmpId > 0) {
            $presentCount = EmployeeAttendance::where('tenant_id', $tenantId)
                ->where('employee_id', $attendanceEmpId)
                ->whereBetween('att_date', [$monthStart, $today])
                ->distinct('att_date')
                ->count('att_date');

            $totalDays = (int) date('j');
            $absentCount = max(0, $totalDays - $presentCount);

            $isAttendanceMarked = EmployeeAttendance::where('tenant_id', $tenantId)
                ->where('employee_id', $attendanceEmpId)
                ->whereDate('att_date', $today)
                ->exists() ? 1 : 0;
        }

        /* ================================
           5️⃣ COLLECTION CHART (LAST 7 DAYS)
        ================================= */
        $collectionChart = [];

        for ($i = 6; $i >= 0; $i--) {

            $date = date('Y-m-d', strtotime("-$i days"));

            $receiptChart = Receipt::query()
                ->whereDate('receipt_date', $date)
                ->where($dashboardScope)
                ->selectRaw('SUM(received_amount + penalty) as total')
                ->value('total') ?? 0;

            $advanceChart = AdvanceReceipt::query()
                ->whereDate('receipt_date', $date)
                ->where($dashboardScope)
                ->sum('receipt_amount');

            $beforeChart = BeforeEnrolReceipt::query()
                ->whereDate('received_date', $date)
                ->where($dashboardScope)
                ->sum('amount');

            $totalChart = $receiptChart + $advanceChart + $beforeChart;

            $collectionChart[] = [
                'label' => date('d', strtotime($date)),
                'value' => (int) $totalChart
            ];
        }

        $maxValue = collect($collectionChart)->max('value');
        $maxValue = ceil($maxValue / 1000) * 1000;

        /* ================================
           6️⃣ CASH IN HAND (single employee in scope)
        ================================= */
        $cashInHand = 0;

        if (count($scopedEmployeeIds) === 1) {

            $cashEmpId = $scopedEmployeeIds[0];
            $startDate = "2026-02-24";

            $cashReceipts = Receipt::where('employee_id', $cashEmpId)
                ->where('tenant_id', $tenantId)
                ->where('payment_type_id', 1)
                ->where('status', 1)
                ->where('receipt_mode', 'Mobile')
                ->whereBetween('receipt_date', [$startDate, $today])
                ->selectRaw('SUM(received_amount + penalty) as total')
                ->value('total') ?? 0;

            $cashAdvance = AdvanceReceipt::where('employee_id', $cashEmpId)
                ->where('tenant_id', $tenantId)
                ->where('payment_type_id', 1)
                ->where('status', 1)
                ->where('receipt_mode', 'Mobile')
                ->whereBetween('receipt_date', [$startDate, $today])
                ->sum('receipt_amount');

            $cashBefore = BeforeEnrolReceipt::where('employee_id', $cashEmpId)
                ->where('tenant_id', $tenantId)
                ->where('payment_type_id', 1)
                ->where('status', 1)
                ->whereBetween('received_date', [$startDate, $today])
                ->sum('amount');

            $settlement = CashSettlement::where('employee_id', $cashEmpId)
                ->where('tenant_id', $tenantId)
                ->where('settlement_mode', 'Mobile')
                ->whereBetween('settlement_date', [$startDate, $today])
                ->sum('received_amount');

            $cashInHand = ($cashReceipts + $cashAdvance + $cashBefore) - $settlement;
        }

        /* ================================
           FINAL RESPONSE
        ================================= */
        return response()->json([
            'status' => true,
            'message' => 'Dashboard fetched successfully!',
            'data' => [
                [
                    'incomeCardsData' => [
                        ['id' => 1, 'title' => 'Todays Collections', 'amount' => '₹ ' . number_format($todaysCollection)],
                        ['id' => 2, 'title' => 'Receipts Generated', 'amount' => (string)$receiptsGenerated],
                        ['id' => 3, 'title' => 'Todays Followup', 'amount' => (string)$todayFollowup],
                    ],
                    'attendanceListData' => [
                        ['id' => 1, 'title' => 'Present', 'count' => $presentCount],
                        ['id' => 2, 'title' => 'Absent', 'count' => $absentCount],
                    ],
                    'isAttendanceMarked' => $isAttendanceMarked,
                    'collectionChart' => $collectionChart,
                    'maxValue' => $maxValue,
                    'cashInHand' => (string) round($cashInHand)
                ]
            ]
        ], 200);

    } catch (\Exception $e) {

        \Log::error('Dashboard Error: '.$e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong'
        ], 500);
    }
}


public function mobile_app_version()
{
    $latest_version = '1.2';
    $critical_version = '1.1';

    return response()->json([
        'status' => 'success',
        'latest_version' => $latest_version,
        'critical_version' => $critical_version
    ]);
}

function formatDate($date, $format = 'd-m-Y')
{
    if (empty($date)) {
        return '';
    }

    return \Carbon\Carbon::parse($date)->format($format);
}


}
       

