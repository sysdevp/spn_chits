<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Mail;
use Lang;
use DB;
use Config;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try { 
            if (! $token = JWTAuth::attempt($credentials)) { 
                    return response()->json(['error' => 'invalid_credentials'], 400);
            } 
        } 
        catch (JWTException $e) { 
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);
        $token = JWTAuth::fromUser($user);
        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } 
        catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } 
        catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } 
        catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }
			
	public function store_user(Request $request)
    {
		$validator=Validator::make($request->all(),[
			'tenant_id'=>'required',
			'branch_id'=>'required',
			'employee_id'=>'required',
			'email'=>'required',
			'password'=>'required',
			'role_id'=>'required'
		]); 
		if($validator->fails()){
			$response['status']="Error";
			$response['msg']=$validator->error()->first();
			return response()->json($response,401);
		}
		else {
			try { 
                // Prevent partial writes by checking duplicate email first (mysql1 has unique `email`).
                $incomingEmail = isset($request->email_id) ? (trim($request->email_id)) : '';
                $incomingEmail = strtolower($incomingEmail);
                if ($incomingEmail !== '') {
                    // mysql1.users may store email in either `email` or `email_id` depending on schema.
                    $emailExists = false;
                    try {
                        $emailExists = DB::connection('mysql1')->table('users')
                            ->where('email', $incomingEmail)
                            ->exists();
                    } catch (\Exception $e) {
                        $emailExists = false;
                    }

                    if (!$emailExists) {
                        try {
                            $emailExists = DB::connection('mysql1')->table('users')
                                ->where('email_id', $incomingEmail)
                                ->exists();
                        } catch (\Exception $e) {
                            $emailExists = false;
                        }
                    }

                    if ($emailExists) {
                        $response['status'] = "Error";
                        $response['msg'] = "Already Exist";
                        return response()->json($response, 200);
                    }
                }

				$user=new User(); 
				$user->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
				$user->branch_id = isset($request->branch_id) ? ($request->branch_id) : '';
				$user->employee_id = isset($request->employee_id) ? ($request->employee_id) : '';   
				$user->email = isset($request->email_id) ? ($request->email_id) : '';   
				$password_original = isset($request->password) ? ($request->password) : ''; 
                $user->password = Hash::make($password_original);							
				$user->role_id = isset($request->role_id) ? ($request->role_id) : '';   
				$user->user_type = isset($request->user_type) ? ($request->user_type) : '0';
				$user->created_by = isset($request->created_by) ? ($request->created_by) : '';
				$user->status = isset($request->status) ? ($request->status) : 1;
                // print_r($user);exit;
				if($user->save()){
                    // print_r($user->id);exit;
                    $tenant_id=isset($request->tenant_id) ? ($request->tenant_id) : '';
                    $branch_id=isset($request->branch_id) ? ($request->branch_id) : '';
                    $employee_id=isset($request->employee_id) ? ($request->employee_id) : '';
                    $email=isset($request->email_id) ? ($request->email_id) : '';
                    $password=isset($request->password) ? (md5($request->password)) : '';
                    $role_id=isset($request->role_id) ? ($request->role_id) : '';
                    $user_type=isset($request->user_type) ? ($request->user_type) : '0';
                    $created_by=isset($request->created_by) ? ($request->created_by) : '';
                    $status=isset($request->status) ? ($request->status) : 1;
                    $original_id=isset($user->id) ? ($user->id) : 0;

                    try {
					    DB::connection('mysql1')->insert('insert into users (tenant_id, branch_id,employee_id,email,password,role_id,user_type,created_by,status,original_id) values (?,?,?,?,?,?,?,?,?,?)',[$tenant_id,$branch_id,$employee_id,$email,$password,$role_id,$user_type,$created_by,$status,$original_id]);
                    } catch(\Exception $mysqlEx) {
                        // If mysql1 insert fails (e.g. duplicate email), remove the local saved user to avoid partial state.
                        try { $user->delete(); } catch(\Exception $ignore) {}

                        $response['status'] = "Error";
                        $response['msg'] = "Already Exist";
                        return response()->json($response, 200);
                    }

                    // Save additional multi-mappings (employees/branches) if provided.
                    $mappedEmployeeItems = isset($request->mapped_employee_ids) ? $request->mapped_employee_ids : [];
                    $mappedBranchItems = isset($request->mapped_branch_ids) ? $request->mapped_branch_ids : [];

                    $mappedEmployeeIds = [];
                    if (is_array($mappedEmployeeItems)) {
                        foreach ($mappedEmployeeItems as $it) {
                            if (is_array($it) && isset($it['item_id'])) {
                                $mappedEmployeeIds[] = (int)$it['item_id'];
                            } elseif (is_numeric($it)) {
                                $mappedEmployeeIds[] = (int)$it;
                            }
                        }
                    }

                    $mappedBranchIds = [];
                    if (is_array($mappedBranchItems)) {
                        foreach ($mappedBranchItems as $it) {
                            if (is_array($it) && isset($it['item_id'])) {
                                $mappedBranchIds[] = (int)$it['item_id'];
                            } elseif (is_numeric($it)) {
                                $mappedBranchIds[] = (int)$it;
                            }
                        }
                    }

                    // Ensure primary employee/branch are included in mappings.
                    if (!empty($employee_id)) {
                        $mappedEmployeeIds[] = (int)$employee_id;
                    }
                    if (!empty($branch_id)) {
                        $mappedBranchIds[] = (int)$branch_id;
                    }
                    $mappedEmployeeIds = array_values(array_unique(array_filter($mappedEmployeeIds)));
                    $mappedBranchIds = array_values(array_unique(array_filter($mappedBranchIds)));

                    // SaaS rule: admin DB is for login only.
                    // Save mappings in the tenant(client) database (current default connection `mysql`),
                    // using the tenant user id ($user->id).
                    if (!empty($original_id)) {
                        // Ensure idempotency for re-submits / retries.
                        try {
                            DB::table('user_employee_mapping')->where('user_id', $original_id)->delete();
                            DB::table('user_branch_mapping')->where('user_id', $original_id)->delete();
                        } catch (\Exception $ignore) {}

                        foreach ($mappedEmployeeIds as $eid) {
                            DB::table('user_employee_mapping')->insert(
                                [
                                    'tenant_id' => $tenant_id,
                                    'user_id' => $original_id,
                                    'employee_id' => $eid,
                                    'created_by' => $created_by,
                                    'status' => $status
                                ]
                            );
                        }
                        foreach ($mappedBranchIds as $bid) {
                            DB::table('user_branch_mapping')->insert(
                                [
                                    'tenant_id' => $tenant_id,
                                    'user_id' => $original_id,
                                    'branch_id' => $bid,
                                    'created_by' => $created_by,
                                    'status' => $status
                                ]
                            );
                        }
                    }

					$response['status']="Success";
					$response['msg']=\Lang::get('api.success_employee_login_added');
					$response['data']=$user;
					return response()->json($response,200);
				}
		    } 
			catch(\Exception $e){
				$response['status']="Error";
                $response['exep']=$e;
		        $response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,401);
			}
		}
	}
				
	public function update_user(Request $request)
	{		
		try { 
			if(isset($request->id)) { 
				$user = User::find($request->id);							
                if (isset($request->role_id)) {
                    $user->role_id = $request->role_id;
                }
                $password_original = isset($request->password) ? ($request->password) : ''; 
                $user->password = Hash::make($password_original);   
				$user->updated_by = isset($request->updated_by) ? ($request->updated_by) : '';
				$user->status = isset($request->status) ? ($request->status) : 1;
				if($user->save()){
 
                    $password=isset($request->password) ? (md5($request->password)) : '';
                    $updated_by=isset($request->updated_by) ? ($request->updated_by) : '';
                    $status=isset($request->status) ? ($request->status) : 1;
                    $role_id = isset($request->role_id) ? $request->role_id : $user->role_id;
                    $tenant_id = isset($user->tenant_id) ? $user->tenant_id : '';

                    DB::connection('mysql1')->update(
                        'update users set password = ?, role_id = ? where tenant_id = ? and original_id = ?',
                        [$password, $role_id, $user->tenant_id, $user->id]
                    );

                    // Sync tenant DB mappings (same rules as store_user)
                    $mappedEmployeeItems = isset($request->mapped_employee_ids) ? $request->mapped_employee_ids : [];
                    $mappedBranchItems = isset($request->mapped_branch_ids) ? $request->mapped_branch_ids : [];
                    $mappedEmployeeIds = [];
                    if (is_array($mappedEmployeeItems)) {
                        foreach ($mappedEmployeeItems as $it) {
                            if (is_array($it) && isset($it['item_id'])) {
                                $mappedEmployeeIds[] = (int)$it['item_id'];
                            } elseif (is_numeric($it)) {
                                $mappedEmployeeIds[] = (int)$it;
                            }
                        }
                    }
                    $mappedBranchIds = [];
                    if (is_array($mappedBranchItems)) {
                        foreach ($mappedBranchItems as $it) {
                            if (is_array($it) && isset($it['item_id'])) {
                                $mappedBranchIds[] = (int)$it['item_id'];
                            } elseif (is_numeric($it)) {
                                $mappedBranchIds[] = (int)$it;
                            }
                        }
                    }
                    $employee_id = isset($request->employee_id) ? (int)$request->employee_id : (int)$user->employee_id;
                    $branch_id = isset($request->branch_id) ? (int)$request->branch_id : (int)$user->branch_id;
                    if (!empty($employee_id)) {
                        $mappedEmployeeIds[] = $employee_id;
                    }
                    if (!empty($branch_id)) {
                        $mappedBranchIds[] = $branch_id;
                    }
                    $mappedEmployeeIds = array_values(array_unique(array_filter($mappedEmployeeIds)));
                    $mappedBranchIds = array_values(array_unique(array_filter($mappedBranchIds)));

                    $uid = (int)$user->id;
                    if ($uid > 0) {
                        try {
                            DB::table('user_employee_mapping')->where('user_id', $uid)->delete();
                            DB::table('user_branch_mapping')->where('user_id', $uid)->delete();
                        } catch (\Exception $ignore) {}
                        foreach ($mappedEmployeeIds as $eid) {
                            DB::table('user_employee_mapping')->insert([
                                'tenant_id' => $tenant_id,
                                'user_id' => $uid,
                                'employee_id' => $eid,
                                'created_by' => $updated_by,
                                'status' => $status,
                            ]);
                        }
                        foreach ($mappedBranchIds as $bid) {
                            DB::table('user_branch_mapping')->insert([
                                'tenant_id' => $tenant_id,
                                'user_id' => $uid,
                                'branch_id' => $bid,
                                'created_by' => $updated_by,
                                'status' => $status,
                            ]);
                        }
                    }

					$response['status']="Success";
					$response['msg']=\Lang::get('api.success_employee_login_added');
					$response['data']=$user;
					return response()->json($response,200);
				}
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
		    }
		}
		catch(\Exception $e){
			$response['status']="Error";
	        $response['msg'] = \Lang::get('api.global_error');
			return response()->json($response,401);
		}
	}				
    
    public function get_user(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $users = User::where($cond)->orderBy('id','desc')->get();
            foreach ($users as $user) {
                $user->mapped_employee_ids = [];
                $user->mapped_branch_ids = [];
                try {
                    $eRows = DB::table('user_employee_mapping')
                        ->where('user_id', $user->id)
                        ->where('status', 1)
                        ->get();
                    foreach ($eRows as $row) {
                        $eid = (int) $row->employee_id;
                        $emp = Employee::with(['role_det', 'branch_det'])->find($eid);
                        $roleName = ($emp && $emp->role_det) ? $emp->role_det->role_name : '';
                        $branchName = ($emp && $emp->branch_det) ? $emp->branch_det->branch_name : '';
                        $text = $emp ? (trim($emp->first_name . ' ' . $emp->last_name)) : ('#' . $eid);
                        $itemText = $text . ($roleName ? ' / ' . $roleName : '') . ($branchName ? ' / ' . $branchName : '');
                        $user->mapped_employee_ids[] = ['item_id' => $eid, 'item_text' => $itemText];
                    }
                    $bRows = DB::table('user_branch_mapping')
                        ->where('user_id', $user->id)
                        ->where('status', 1)
                        ->get();
                    foreach ($bRows as $row) {
                        $bid = (int) $row->branch_id;
                        $br = Branch::find($bid);
                        $user->mapped_branch_ids[] = [
                            'item_id' => $bid,
                            'item_text' => $br ? $br->branch_name : ('#' . $bid),
                        ];
                    }
                    // Fallback: primary login branch/employee (mobile uses these when mapping rows missing)
                    if (empty($user->mapped_branch_ids) && !empty($user->branch_id)) {
                        $bid = (int) $user->branch_id;
                        $br = Branch::find($bid);
                        $user->mapped_branch_ids[] = [
                            'item_id' => $bid,
                            'item_text' => $br ? $br->branch_name : ('#' . $bid),
                        ];
                    }
                    if (empty($user->mapped_employee_ids) && !empty($user->employee_id)) {
                        $eid = (int) $user->employee_id;
                        $emp = Employee::with(['role_det', 'branch_det'])->find($eid);
                        $roleName = ($emp && $emp->role_det) ? $emp->role_det->role_name : '';
                        $branchName = ($emp && $emp->branch_det) ? $emp->branch_det->branch_name : '';
                        $text = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : ('#' . $eid);
                        $itemText = $text . ($roleName ? ' / ' . $roleName : '') . ($branchName ? ' / ' . $branchName : '');
                        $user->mapped_employee_ids[] = ['item_id' => $eid, 'item_text' => $itemText];
                    }
                } catch (\Exception $ignore) {
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $users;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_users(Request $request) {
        try { 
            //$users = User::all();
             if(isset($request->tenant_id)) {
                $users = User::where('tenant_id',$request->tenant_id)->where('role_id','!=',0)->where('user_type','!=',3)->orderBy('id','desc')->get();
            }
            else {
                $users = User::where('role_id','!=',0)->where('user_type','!=',3)->orderBy('id','desc')->get();
            }
           // $users = User::where('role_id','!=',0)->orderBy('id','desc')->get();
            $i=1;
            foreach($users as $user) {
                $user->sno = $i;
                $user->employee_info = isset($user->employee_det) ? $user->employee_det : [];
				$user->role_info = isset($user->role_det) ? $user->role_det : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $users;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_user(Request $request) {
        try {
            if(isset($request->id)) {
                $otp=mt_rand(1000,9999);
                $todaydate=date('Y-m-d H:i:s');
                $user = User::find($request->id); 
                $user->deleted_by = $request->deleted_by;
                $user->email = $user->email.'.'.$otp;
                $user->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($user->save()) {
                    $user->delete();
                    DB::connection('mysql1')->update('update users set deletion_remark="'.$request->deletion_remark.'",deleted_by="'.$request->deleted_by.'",deleted_at="'.$todaydate.'",email="'.$user->email.'" where tenant_id="'.$user->tenant_id.'" and original_id="'.$user->id.'" ');
                    // DB::connection('mysql1')->update('delete users where tenant_id="'.$user->tenant_id.'" and original_id="'.$user->id.'"');

                    // User::delete('delete users where id="'.$request->id.'"');

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_user_deleted');
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
	
	public function update_user_status(Request $request) {
        try {
            if(isset($request->id)) {
                $user = User::find($request->id); 
                $user->status = ($user->status) ? 0 : 1;
                if($user->save()) {
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

    public function check_email_validation(Request $request)
    {
        try
        {
            $cond = $request->email;
            $check_email = User::where('email','=',$cond)->get();
            if(count($check_email)>0)
            {	
				$response['status'] = "Error";
                $response['msg'] = "Already Exist";
                return response()->json($response, 200);
            }
			else
			{
				$response['status'] = "Success";
                $response['msg'] = "";
                return response()->json($response, 200);
			}
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function change_password(Request $request)
	{		
		try { 
			if(isset($request->created_by)) { 
				$user = User::find($request->created_by);	
				$db_password = $user->password;
                $old_password = isset($request->old_password) ? ($request->old_password) : ''; 
                $new_password = isset($request->new_password) ? ($request->new_password) : ''; 
                $confirm_password = isset($request->confirm_password) ? ($request->confirm_password) : ''; 
                $user->password = Hash::make($confirm_password);   
				$user->updated_by = isset($request->updated_by) ? ($request->updated_by) : '';
				$user->status = isset($request->status) ? ($request->status) : 1;
				if(Hash::check($old_password, $db_password)) {
				if($confirm_password == $new_password){
					if($user->save()) {
					$new_pass=md5($new_password);
					// print_r($user);
					DB::connection('mysql1')->update('update users set password="'.$new_pass.'" where email="'.$user->email.'" ');
					$response['status']="Success";
					$response['msg']=\Lang::get('api.password_changed');
					$response['data']=$user;
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
                    return response()->json($response,401);
			   }
			   } else {
					$response['status']="Error";
                    $response['msg']=\Lang::get('api.old_password_not_match');
                    return response()->json($response,401);
				}
		    }
		}
		catch(\Exception $e){
			$response['status']="Error";
	        $response['msg'] = \Lang::get('api.global_error');
			return response()->json($response,401);
		}
	}	    
	
	public function forget_password(Request $request) {
        try { 
            if(isset($request->email)) {
                if(isset($request->slug)) {
                  $tenant_det = Tenant::where(array('slag_url'=>$request->slug))->get();
                  if(count($tenant_det)>0) {
                    $database_name = $tenant_det[0]->database_name;
                    DB::purge('mysql');
                    Config::set('database.connections.mysql.database', $tenant_det[0]->database_name);
                    DB::reconnect('mysql');
                  }
                  else {
                    return response()->json([
                      'success' => false,
                      'message' => Lang::get('api.invalid_url')
                    ], 401);
                  }
                }

              $user = User::where('email',$request->email)->get();  ;
			  $employee = Employee::where('id',$user[0]->employee_id)->get();
			  $email = $employee[0]->email; 
              $employee_name = $employee[0]->first_name.' '.$employee[0]->last_name;
              $otp=mt_rand(1000,9999); 
              $data['otp'] = $otp; 
              Mail::send(['text'=>'mail'],$data, function($message) 
              use($email,$employee_name){
                $message->to($email,$employee_name)->subject('OTP Mail');
                $message->from('gowri@mazenetsolution.com','Gowri');
              }); 

              $user_update = User::where('email',$request->email)->get();
              $user_update[0]->password = Hash::make($otp);
              if($user_update[0]->save())
              {
                  $response['status'] = "Success";
                  $response['msg'] = "OTP Send Sucessfully";
                  return response()->json($response, 200);                
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
            $response['msg'] = $e;
            return response()->json($response, 401);
        } 
    }
	
}
	