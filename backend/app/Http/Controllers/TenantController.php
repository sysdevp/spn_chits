<?php
namespace App\Http\Controllers;
ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);

use App\Models\Tenant;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use App\Models\DynamicMenu;
use App\Models\RoleType;
use App\Models\User;
use App\Models\FollowupStatusType;
use App\Models\LogMonitoringTable;
use App\Models\ChargeType;
use App\Models\CustomerBankDetail;
use App\Models\PaymentType;
use App\Models\PettyCashExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Nwidart\DbExporter\DbExportHandler;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;
use Config;
use Storage;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function store_tenant(Request $request)
    {  
        $validator=Validator::make($request->all(),[
            'chit_fund_name'=>'required',
            ///'company_logo'=>'required',
            //'receipts_logo'=>'required',
            'header_name'=>'required',
            'admin_email'=>'required',
            'admin_password'=>'required',
            'gst_no'=>'required',
            'authorized_person_name'=>'required',
            'mobile_no'=>'required',
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {
            try { 
                $is_user_exist = User::where(array('email'=>$request->admin_email))->get();
                if(count($is_user_exist)>0) {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.email_already_exist');
                    return response()->json($response, 200);
                }
                if(isset($request->is_new_database) && (!isset($request->company_db_name))) {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.database_name_not_empty');
                    return response()->json($response, 200);
                }
                if(isset($request->is_new_database) && (isset($request->company_db_name))) {
                    $is_database_exist = Tenant::withTrashed()->where(array('is_new_database' => 1,'database_name' => $request->company_db_name))->get();
                    if(count($is_database_exist)>0) {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.database_already_exist');
                        return response()->json($response, 200);
                    }
                    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  '".$request->company_db_name."'";
                    $db = DB::select($query, [$request->company_db_name]);
                    if(!empty($db)) {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.database_already_exist');
                        return response()->json($response, 200);
                    }
                }                
                $chit_fund_name = isset($request->chit_fund_name)?($request->chit_fund_name):'';
                $sms_enable = ($request->sms=="true") ? 1 : '';
				$online_bidding_enable = ($request->online_bidding=="true") ? 1 : '';
                $company_logo = isset($request->company_logo)?($request->company_logo):'';
                $receipts_logo = isset($request->receipts_logo)?($request->receipts_logo):'';
                $favicon = isset($request->favicon)?($request->favicon):'';
                $header_name = isset($request->header_name)?($request->header_name):'';
                $admin_email = isset($request->admin_email)?($request->admin_email):'';
                $admin_password = isset($request->admin_password)? Hash::make($request->admin_password) : Hash::make(Config::get('constants.super_admin_password'));
                $gst_no = isset($request->gst_no)?($request->gst_no):''; 
                $authorized_person_name = isset($request->authorized_person_name)?($request->authorized_person_name):'';
                $phone_no = isset($request->phone_no)?($request->phone_no):'';
                $mobile_no = isset($request->mobile_no)?($request->mobile_no):'';
                $database_name = isset($request->company_db_name) ? ($request->company_db_name) : '';
                $status = isset($request->status) ? ($request->status) : 1;
                $created_by = isset($request->created_by) ? ($request->created_by) : '';
                $tenant = new Tenant();
                $tenant->chit_fund_name = $chit_fund_name;
                $tenant->sms_enable = $sms_enable;
				$tenant->online_bidding_enable = $online_bidding_enable;
                $tenant->company_logo = $company_logo;
                $tenant->receipts_logo = $receipts_logo;
                $tenant->favicon = $favicon;
                $tenant->header_name = $header_name;
                $tenant->admin_email = $admin_email;
                $tenant->admin_password = $admin_password;
                $tenant->gst_no = $gst_no;
                $tenant->authorized_person_name = $authorized_person_name;
                $tenant->phone_no = $phone_no;
                $tenant->mobile_no = $mobile_no;
                $tenant->status = $status;
                $tenant->is_new_database = isset($request->is_new_database)?($request->is_new_database):0; 
                $table_list = [];
                if(($request->is_new_database) && ($request->company_db_name)) {
                    $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  '".$request->company_db_name."'";
                    $db = DB::select($query, [$request->company_db_name]);
                    if(empty($db)) {
                        //No db exist of that name
                        $country_list = Country::all();
                        $state_list = State::all();
                        $district_list = District::all();
                        $city_list = City::all();
                        $dynamic_menu_list = DynamicMenu::all();
                        $role_type_list = RoleType::all();
                        $followup_status_type_list = FollowupStatusType::all();
                        $log_monitoring_table_list = LogMonitoringTable::all();
                        $charge_types_list = ChargeType::all();
                        $customer_bank_details_list = CustomerBankDetail::all();
                        $payment_types_list = PaymentType::all();
                        $petty_cash_expense_types_list = PettyCashExpenseType::all();                               
                        DB::statement("CREATE DATABASE IF NOT EXISTS " . $request->company_db_name);
                        $tables = DB::select('SHOW TABLES');
                        $db = "Tables_in_".env('DB_DATABASE');
                        foreach($tables as $table)
                        {
                            array_push($table_list, $table->$db);
                        }
                        $column_list = [];
                        foreach($table_list as $tl) { 
                            $column_list = DB::select('DESCRIBE ' . $tl);
                            $create_column = "";
                            foreach($column_list as $cl) {
                                if($create_column!='') {
                                    $create_column .= ",";
                                }
                                if(($cl->Field=='updated_at')||($cl->Field=='deleted_at')) {
                                    $create_column .= $cl->Field.' '.$cl->Type.' '.$cl->Extra.' NULL';
                                }
                                else {
                                    $create_column .= $cl->Field.' '.$cl->Type.' '.$cl->Extra;
                                    if($cl->Key == 'PRI') {
                                        $create_column .= ' PRIMARY KEY';
                                    } 
                                    if($cl->Null=='NO') {
                                        $create_column .= ' NOT NULL';
                                    } 
                                    if($cl->Default!=null) {
                                        $create_column .= ' DEFAULT '.$cl->Default;
                                    } 
                                }
                            } 
                            DB::statement("CREATE TABLE IF NOT EXISTS ".$request->company_db_name.'.'.$tl."(".$create_column.")");
                            if($tl=='countries') {
                                foreach($country_list as $cnt) {
                                    $cnt_sql = 'insert into '.$request->company_db_name.'.countries(shortname,name,phonecode,created_at,created_by,status) values("'.$cnt->shortname.'","'.$cnt->name.'","'.$cnt->phonecode.'","'.$cnt->created_at.'",1,1)';
                                    DB::statement($cnt_sql);
                                }
                            }
                            if($tl=='states') {
                                foreach($state_list as $state) {
                                    $state_sql = 'insert into '.$request->company_db_name.'.states(country_id,state_name,created_at,created_by,status) values("'.$state->country_id.'","'.$state->state_name.'","'.$state->created_at.'",1,1)';
                                    DB::statement($state_sql);
                                }
                            }
                            if($tl=='districts') {
                                foreach($district_list as $dist) {
                                    $dist_sql = 'insert into '.$request->company_db_name.'.districts(state_id,district_name,created_at,created_by,status) values("'.$dist->state_id.'","'.$dist->district_name.'","'.$dist->created_at.'",1,1)';
                                    DB::statement($dist_sql);
                                }
                            }
                            if($tl=='cities') {
                                foreach($city_list as $city) {
                                    $city_sql = 'insert into '.$request->company_db_name.'.cities(state_id,district_id,city_name,pincode,created_at,created_by,status) values("'.$city->state_id.'","'.$city->district_id.'","'.$city->city_name.'","'.$city->pincode.'","'.$city->created_at.'",1,1)';
                                    DB::statement($city_sql);
                                }
                            }
                            if($tl=='dynamic_menus') {
                                foreach($dynamic_menu_list as $dm) {
                                    $dm_sql = 'insert into '.$request->company_db_name.'.dynamic_menus(tenant_id,menu_title,icon_class,page_type,router_link,has_submenu,tab_type,parent_menu_id,sort_order,created_at,created_by,status) values("'.$dm->tenant_id.'","'.$dm->menu_title.'","'.$dm->icon_class.'","'.$dm->page_type.'","'.$dm->router_link.'","'.$dm->has_submenu.'","'.$dm->tab_type.'","'.$dm->parent_menu_id.'","'.$dm->sort_order.'","'.$dm->created_at.'",1,1)';
                                    DB::statement($dm_sql);
                                }
                            }
                            if($tl=='role_types') {
                                foreach($role_type_list as $rt) {
                                    $rt_sql = 'insert into '.$request->company_db_name.'.role_types(role_type_name,created_at,status) values("'.$rt->role_type_name.'","'.$rt->created_at.'",1)';
                                    DB::statement($rt_sql);
                                }
                            } 
                            if($tl=='followup_status_types') {
                                foreach($followup_status_type_list as $fst) {
                                    $fst_sql = 'insert into '.$request->company_db_name.'.followup_status_types(tenant_id,followup_status_name,created_at,created_by,status) values(0,"'.$fst->followup_status_name.'","'.$fst->created_at.'",1,1)'; 
                                    DB::statement($fst_sql);
                                }
                            } 
                            if($tl=='log_monitoring_tables') {
                                foreach($log_monitoring_table_list as $lmt) {
                                    $lmt_sql = 'insert into '.$request->company_db_name.'.log_monitoring_tables(module_name,table_name,created_at,created_by,status) values("'.$lmt->module_name.'","'.$lmt->table_name.'","'.$lmt->created_at.'",1,1)';
                                    DB::statement($lmt_sql);
                                }
                            } 
                            if($tl=='charge_types') {
                                foreach($charge_types_list as $ct) {
                                    $ct_sql = 'insert into '.$request->company_db_name.'.charge_types(tenant_id,charge_name,created_at,created_by,status) values(0,"'.$ct->charge_name.'","'.$ct->created_at.'",1,1)';
                                    DB::statement($ct_sql);
                                }
                            } 
                            if($tl=='customer_bank_details') {
                                foreach($customer_bank_details_list as $cbd) {
                                    $cbd_sql = 'insert into '.$request->company_db_name.'.customer_bank_details(customer_bank_name,created_at,status) values("'.$cbd->customer_bank_name.'","'.$cbd->created_at.'",1)';
                                    DB::statement($cbd_sql);
                                }
                            } 
                            if($tl=='payment_types') {
                                foreach($payment_types_list as $pt) {
                                    $pt_sql = 'insert into '.$request->company_db_name.'.payment_types(tenant_id,payment_name,created_at,created_by,status) values(0,"'.$pt->payment_name.'","'.$pt->created_at.'",1,1)';
                                    DB::statement($pt_sql);
                                }
                            } 
                            if($tl=='petty_cash_expense_types') {
                                foreach($petty_cash_expense_types_list as $pcet) {
                                    $pcet_sql = 'insert into '.$request->company_db_name.'.petty_cash_expense_types(tenant_id,expenses_type,created_at,created_by,status) values(0,"'.$pcet->expenses_type.'","'.$pcet->created_at.'",1,1)';
                                    DB::statement($pcet_sql);
                                }
                            } 
                        } 
                        $hashed_password = Hash::make(Config::get('constants.super_admin_password'));
                        $sql = 'insert into '.$request->company_db_name.'.users(email,password,status) values("'.Config::get('constants.super_admin_email').'","'.$hashed_password.'",1)';
                        DB::statement($sql);

                        $hashed_password_2 = Hash::make($request->admin_password);
                        $sql_2 = 'insert into '.$request->company_db_name.'.users(tenant_id,email,password,status) values(1,"'.$admin_email.'","'.$hashed_password_2.'",1)';
                        DB::statement($sql_2); 
                        $slag = str_replace(' ', '-', strtolower($request->chit_fund_name));
                        $slag_url = '';
                        $s_url = $slag;
                        $i=0; 
                        while($slag_url=='') {
                            $is_slag_exist = Tenant::where(array('slag_url'=>$s_url))->get();
                            if(count($is_slag_exist)==0) {
                                $slag_url = $s_url;
                            }
                            else {
                                $i++;
                                $s_url = $slag.'-'.$i;
                                $slag_url = '';
                            }
                        }
                        $tenant_sql = 'insert into '.$request->company_db_name.'.tenants(chit_fund_name,company_logo,receipts_logo,favicon,header_name,gst_no,authorized_person_name,phone_no,mobile_no,database_name,slag_url,created_by,status,no_branches,no_groups,no_customers,no_users,start_date,renewal_date,version,sms_enable,online_bidding_enable) values("'.$chit_fund_name.'","'.$company_logo.'","'.$receipts_logo.'","'.$favicon.'","'.$header_name.'","'.$gst_no.'","'.$authorized_person_name.'","'.$phone_no.'","'.$mobile_no.'","'.$database_name.'","'.$slag_url.'",'.$created_by.',1,"'.$no_branches.'","'.$no_groups.'","'.$no_customers.'","'.$no_users.'","'.$start_date.'","'.$renewal_date.'","'.$version.'","'.$sms_enable.'","'.$online_bidding_enable.'")';
                        DB::statement($tenant_sql);
                        $sp_receipt_qry = 'create procedure '.$request->company_db_name.'.receipt_report(IN tenant_id INT,IN branch_id INT,IN group_id INT,IN customer_id INT,IN employee_id INT,IN payment_type_id INT,IN start_date DATETIME,IN end_date DATETIME) 
                        BEGIN 
                        SET @cond = CONCAT(" where r.tenant_id=",tenant_id," and r.branch_id=",branch_id); SET @JoinQry = "JOIN branches b ON r.branch_id = b.id"; SET @JoinQry = CONCAT(@JoinQry," JOIN groups g ON r.group_id = g.id"); SET @JoinQry = CONCAT(@JoinQry," JOIN customers c ON r.customer_id = c.id"); SET @JoinQry = CONCAT(@JoinQry," JOIN employees e ON r.employee_id = e.id"); SET @SelectQry = "SELECT r.*, sum(r.received_amount) as total_paid, sum(r.bonus) as bonus_amount, sum(r.penalty) as penalty_amount, b.branch_name as branchName"; SET @SelectQry = CONCAT(@SelectQry,", g.group_name"); SET @SelectQry = CONCAT(@SelectQry,", c.name"); SET @SelectQry = CONCAT(@SelectQry,", e.first_name"); IF group_id IS NOT NULL  AND group_id <> "" AND LENGTH(group_id) > 0 THEN SET @cond = CONCAT(@cond," AND r.group_id=",group_id); END IF; IF customer_id IS NOT NULL  AND customer_id <> "" AND LENGTH(customer_id) > 0 THEN SET @cond = CONCAT(@cond," AND r.customer_id=",customer_id); END IF; IF employee_id IS NOT NULL  AND employee_id <> "" AND LENGTH(employee_id) > 0 THEN  SET @cond = CONCAT(@cond," AND r.employee_id=",employee_id); END IF; IF payment_type_id IS NOT NULL  AND payment_type_id <> "" AND LENGTH(payment_type_id) > 0 THEN SET @cond = CONCAT(@cond," AND r.payment_type_id=",payment_type_id); END IF; IF (start_date IS NULL OR start_date="" OR LENGTH(start_date) <=0) AND (end_date IS NOT NULL  AND end_date <> "" AND LENGTH(end_date) > 0) THEN SET @cond = CONCAT(@cond," AND r.receipt_date<=",end_date); END IF; IF (end_date IS NULL OR end_date="" OR LENGTH(end_date) <=0) AND (start_date IS NOT NULL  AND start_date <> "" AND LENGTH(start_date) > 0) THEN SET @cond = CONCAT(@cond," AND r.receipt_date>=",start_date); END IF; SET @JoinQry = CONCAT(@JoinQry," JOIN chit_details cd ON r.enrollment_id = cd.id"); SET @SelectQry = CONCAT(@SelectQry,", cd.branch_id as enroll_branch"); SET @JoinQry = CONCAT(@JoinQry," JOIN branches b1 ON cd.branch_id = b1.id"); SET @SelectQry = CONCAT(@SelectQry,", b1.branch_name as enroll_branch"); SET @sqlText = CONCAT(@SelectQry," FROM receipts r ",@JoinQry,@cond," group by r.receipt_no"); PREPARE stmt FROM @sqlText; EXECUTE stmt; DEALLOCATE PREPARE stmt; END;';
                        DB::connection()->getPdo()->exec($sp_receipt_qry);
                        $tenant->slag_url = $slag_url;
                    }
                    else {
                        //Db already exists
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.database_already_exist1');
                        return response()->json($response, 200);
                    }
                }
                
                $tenant->database_name = $database_name; 
                if($tenant->save()){
                    if(!($request->is_new_database) && !($request->company_db_name)) {
                        $hashed_password_2 = Hash::make($request->admin_password);
                        $user_det = new User();
                        $user_det->tenant_id = $tenant->id;
                        $user_det->email = $admin_email;
                        $user_det->password = $hashed_password_2;
                        $user_det->save();
                    }
                    $response['status'] = "Success";
                    $response['msg'] = \Lang::get('api.success_tenant_added');
                    $response['data'] = $tenant;
                    return response()->json($response,200);
                }
                else {
                    $response['status'] = "Error";
                    $response['msg'] = \Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            }
            catch(\Exception $e){
                $response['status'] = "Error";
                $response['msg'] = \Lang::get('api.global_error');
                $response['msg'] = $e;
                return response()->json($response,401);
            }
        }
    }

    public function update_tenant(Request $request)
    {
        $validator = Validator::make($request->all(), [
             'chit_fund_name'=>'required',
            ///'company_logo'=>'required',
            //'receipts_logo'=>'required',
            'header_name'=>'required',
            //'admin_email'=>'required',
            //'admin_password'=>'required',
            'gst_no'=>'required',
            'authorized_person_name'=>'required',
            'mobile_no'=>'required',            
        ]);
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try {
                if(isset($request->id)){
                    $tenant=Tenant::find($request->id);
                    $tenant->chit_fund_name= isset($request->chit_fund_name)?($request->chit_fund_name): '';
                    $tenant->company_logo= isset($request->company_logo)?($request->company_logo): '';
                    $tenant->receipts_logo= isset($request->receipts_logo)?($request->receipts_logo): '';
                    $tenant->favicon= isset($request->favicon)?($request->favicon): '';
                    $tenant->version= isset($request->version)?($request->version): '';
					$tenant->sms_enable = ($request->sms=="true") ? 1 : '';
					$tenant->online_bidding_enable = ($request->online_bidding=="true") ? 1 : '';
					if(isset($request->start_date)) {
                    $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                    $tenant->start_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $tenant->start_date = '0000-00-00';
                }
                    $tenant->header_name= isset($request->header_name)?($request->header_name): '';
                    //$tenant->admin_email= isset($request->admin_email)?($request->admin_email): '';
                    //$tenant->admin_password= isset($request->admin_password)?($request->admin_password): '';
                    $tenant->gst_no= isset($request->gst_no)?($request->gst_no):''; 
                    $tenant->authorized_person_name= isset($request->authorized_person_name)?($request->authorized_person_name): '' ;
                    $tenant->phone_no= isset($request->phone_no)?($request->phone_no):'';
                    $tenant->mobile_no= isset($request->mobile_no)?($request->mobile_no):0;
                    $tenant->is_new_database= isset($request->is_new_database)?($request->is_new_database): 0; 
                    $tenant->database_name= isset($request->company_db_name)?($request->company_db_name): ''; 
					if($tenant->is_new_database == 1) {
						DB::statement("UPDATE ".$request->company_db_name.".tenants SET chit_fund_name = '".$request->chit_fund_name."', company_logo='".$request->company_logo."',receipts_logo='".$request->receipts_logo."',favicon='".$request->favicon."',header_name='".$request->header_name."',gst_no='".$request->gst_no."',authorized_person_name='".$request->authorized_person_name."',phone_no='".$request->phone_no."',mobile_no='".$request->mobile_no."',no_branches='".$request->no_branches."',no_groups='".$request->no_groups."',no_users='".$request->no_users."',no_customers='".$request->no_customers."',version='".$request->version."',start_date='".$request->start_date."',renewal_date'".$request->renewal_date."',sms_enable'".$request->sms."',online_bidding_enable'".$request->online_bidding."' where id=".$request->id);
					}
                    if($tenant->save()){
						
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.success_tenant_updated');
                        $response['data']=$tenant;
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
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
    }

    public function get_tenants(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $tenants = Tenant::where($cond)->where(array('is_new_database'=>0))->orderBy('id','desc')->get();
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $tenants;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function super_admin_get_tenants(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $tenants = Tenant::where($cond)->orderBy('id','desc')->get();
			foreach($tenants as $tenant) {
				
				$tenant->company_logo_for_view = ($tenant->company_logo!='') ? env('APP_URL').Storage::url('app/uploads/company_logo_uploads/'.$tenant->company_logo) : '';
				$tenant->receipts_logo_for_view = ($tenant->receipts_logo!='') ? env('APP_URL').Storage::url('app/uploads/receipts_logo_uploads/'.$tenant->receipts_logo) : '';
				$tenant->favicon_for_view = ($tenant->favicon!='') ? env('APP_URL').Storage::url('app/uploads/favicon_upload/'.$tenant->favicon) : '';
			}
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $tenants;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_tenants(Request $request) {
        try { 
            $tenants = Tenant::where(array('is_new_database'=>0))->orderBy('id','desc')->get();
            $i=1;
            foreach($tenants as $tenant) {
                $tenant->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $tenants;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function super_admin_list_tenants(Request $request) {
        try { 
            $tenants = Tenant::orderBy('id','desc')->get();
            $i=1;
            foreach($tenants as $tenant) {
                $tenant->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $tenants;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function delete_tenant(Request $request) {
        try {
            if(isset($request->id)) {
                $tenant = Tenant::find($request->id); 
                $tenant->deleted_by = $request->deleted_by;
                $tenant->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($tenant->save()) {
                    $tenant->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_tenant_deleted');
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
                $tenant_det = Tenant::find($request->id); 
                $tenant_det->status = ($tenant_det->status) ? 0 : 1;
                if($tenant_det->save()) {
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

    public function fileUpload(Request $request)
    {  
        try 
        { 
            if ($request->hasFile('uploaded_file')) 
            {
                $upload_path = '';    
                if ($request->file('uploaded_file')->isValid())
                {  
                    $upload_path = $request->file('uploaded_file')->hashName();
                    $location1 = '';
                    if(($request->upload_type) == 'company_logo') {
                        $location1 = $request->file('uploaded_file')->store('uploads/company_logo_uploads');
                    }
                    else if(($request->upload_type) == 'receipts_logo') {
                        $location1 = $request->file('uploaded_file')->store('uploads/receipts_logo_uploads');
                    }
					else if(($request->upload_type) == 'favicon') {
                        $location1 = $request->file('uploaded_file')->store('uploads/favicon_upload');
                    }
                    else {
                        $location1 = $request->file('uploaded_file')->store('uploads/customer_documents');
                    }
                }
                $response['file_name'] = $upload_path; 
            }
            else 
            {
                $response['file_name'] = 'aa';
            }
            $response['status'] = 'Success';
            return response()->json($response,200);
        }
        catch(\Exception $e) 
        { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response,401);
        }
    }
	
	 public function menu_list(Request $request)
    {
        $menus = DynamicMenu::where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->groupBy('parent_menu_id');

        $buildTree = function ($parentId = 0) use (&$buildTree, $menus) {
            return ($menus[$parentId] ?? collect())->map(function ($menu) use ($buildTree) {
                return [
                    'id' => $menu->id,
                    'title' => $menu->menu_title,
                    'icon' => $menu->icon_class,
                    'route' => $menu->router_link,
                    'page_type' => $menu->page_type,
                    'has_submenu' => $menu->has_submenu,
                    'children' => $buildTree($menu->id)
                ];
            })->toArray();
        };

        return response()->json($buildTree());
    }
}
