<?php
namespace App\Http\Controllers;
 
use App\Http\Requests\RegisterAuthRequest;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Session;
use Lang;
use Config;
use DB;
use Redirect;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
 
class AuthController extends Controller
{
  public $loginAfterSignUp = true;
 
 public function login(Request $request)
{
    $email = $request->email;
    $password = $request->password;

    // Step 0: Set throttle key per email
    $key = 'login:attempts:' . Str::lower($email);

    // Step 1: Check rate limiter (max 5 attempts, lock for 60 seconds)
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);
        return response()->json([
            'success' => false,
            'message' => "Too many login attempts. Try again in {$seconds} seconds."
        ], 429);
    }

    // Step 2: Validate user in admin DB (mysql1)
    $user = DB::connection('mysql1')->table('users')
        ->where('email', $email)
        ->first();

    if (!$user || $user->password !== md5($password)) {
        // Record a failed attempt
        RateLimiter::hit($key, 60);
        return response()->json([
            'success' => false,
            'message' => 'Invalid Credentials',
        ], 401);
    }

    // Step 3: Get tenant DB info
    $tenant = DB::connection('mysql1')->table('tenants')
        ->where('id', $user->tenant_id)
        ->first();

    if (!$tenant) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid Database',
        ], 401);
    }

    $tenantDb = $tenant->slag_url;

    // Step 4: Configure session + DB
    session(['tenant_db' => $tenantDb]);
    Config::set('database.connections.mysql.database', $tenantDb);
    DB::purge('mysql');
    DB::reconnect('mysql');

    // Step 5: JWT Auth in tenant DB
    $msg = $this->check_invalid_login($email, $password);
    $input = ['email' => $email, 'password' => $password];

    if (!$jwt_token = JWTAuth::claims(['tenant_db' => $tenantDb])->attempt($input)) {
        RateLimiter::hit($key, 60); // Also record failed JWT login
        return response()->json([
            'success' => false,
            'message' => $msg,
        ], 401);
    }

    // ✅ Success: clear throttle
    RateLimiter::clear($key);

    $logged_user = JWTAuth::user();

    // Step 6: Check tenant status
    if ($logged_user->tenant_id) {
        $activeTenant = Tenant::where('id', $logged_user->tenant_id)
            ->where('status', 1)
            ->first();

        if (!$activeTenant) {
            return response()->json([
                'success' => false,
                'message' => 'Inactive Login',
            ], 401);
        }
    }

    // Step 7: Final response
    // Menu permissions use users.role_id (login role), NOT employees.role_id.
    $emp_det = $logged_user->employee_id ? Employee::find($logged_user->employee_id) : null;
    $role_details = $logged_user->role_id ? Role::find($logged_user->role_id) : null;
    $tenant_details = Tenant::find($logged_user->tenant_id);

    $rolePermissionRow = null;
    if ($logged_user->role_id && $logged_user->tenant_id) {
        $rolePermissionRow = RolePermission::where('role_id', (int) $logged_user->role_id)
            ->where('tenant_id', (int) $logged_user->tenant_id)
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->first();
    }
    $roleAccess = [];
    if ($rolePermissionRow && !empty($rolePermissionRow->role_access)) {
        $decoded = @unserialize($rolePermissionRow->role_access);
        $roleAccess = is_array($decoded) ? $decoded : [];
    }

    $response = [
        'success' => true,
        'token' => $jwt_token,
        'logged_user_id' => $logged_user->id,
        'logged_employee_id' => $logged_user->employee_id,
        'logged_user_type' => $logged_user->user_type,
        'profile_img' => $emp_det && $emp_det->photo ? env('APP_URL') . Storage::url('app/uploads/employee_photos/' . $emp_det->photo) : '',
        'tenant_id' => $logged_user->tenant_id ?: '',
        'branch_id' => $logged_user->branch_id ?: '',
        'role_id' => $logged_user->role_id,
        'role_type_id' => $role_details ? $role_details->role_type_id : '',
        'role_type_name' => $role_details && $role_details->role_type_det ? $role_details->role_type_det->role_type_name : '',
        'logged_user_name' => $emp_det ? trim($emp_det->first_name . ' ' . $emp_det->last_name) : '',
        'logged_role_name' => $role_details ? $role_details->role_name : '',
        'role_permission' => $roleAccess,
        'database_name' => $tenantDb,
        'version' => $tenant_details->version ?? '',
        'sms_enable' => $tenant_details->sms_enable ?? '',
        'online_bidding_enable' => $tenant_details->online_bidding_enable ?? '',
        'renewaldate' => $tenant_details && $tenant_details->renewal_date ? date('d-m-Y', strtotime($tenant_details->renewal_date)) : '',
        'company_logo' => $tenant_details->company_logo ?? '',
        'receipt_logo' => $tenant_details->receipts_logo ?? '',
        'favicon' => $tenant_details->favicon ?? '',
        'company_name' => $tenant_details->chit_fund_name ?? '',
        'is_tenant_admin' => $logged_user->employee_id ? 0 : 1,
        'tenant_det' => $tenant_details ?? [],
    ];

    return response()->json($response);
}

  public function newlogin(){
    echo 'hai';
  }
 
  public function logout(Request $request)
  {
    session_destroy();
    $this->validate($request, [
      'token' => 'required'
    ]);
    try {
      JWTAuth::invalidate($request->token);
      return response()->json([
        'success' => true,
        'message' => \Lang::get('api.success_user_logged_out')
      ]);
    } 
    catch (JWTException $exception) {
      return response()->json([
        'success' => false,
        'message' => \Lang::get('api.user_cannot_logged_out')
      ], 500);
    }
  }
 
  public function getAuthUser(Request $request)
  {
    $this->validate($request, [
      'token' => 'required'
    ]);
    $user = JWTAuth::authenticate($request->token);
    return response()->json(['user' => $user]);
  }

  public function check_invalid_login($email,$password) { 
    DB::enableQueryLog();
    $user = User::where(array('email'=>$email))->get();
    $msg = "";
    if(count($user) == 0) {
      $msg = \Lang::get('api.incorrect_email_id');
    }
    else {
      $msg = \Lang::get('api.incorrect_password');
    } 
    $query = DB::getQueryLog();
    //dd($query);
    //dd(\DB::connection('mysql'));
    return $msg;
  }
}