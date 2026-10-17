<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\BiddingDetail;
use App\Models\GuarantorRelation;
use App\Models\InterestMessage;
use App\Models\ChitDetail;
use App\Models\User;
use App\Models\Nominee;
use App\Models\FamilyCode;
use App\Models\Receipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\LeadManagement;
use App\Models\UpdateCustomer;
use App\Models\CommitmentAuction;
use App\Models\Branch;
use App\Models\City;
use App\Models\AuctionOnHoliday;
use App\Models\Document;
use App\Models\DocumentDetail;
use Illuminate\Http\Request;
use Validator;
use Storage;
use Lang;
use Config;
use DateTime;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CustomerController extends Controller
{
    public function store_customer(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            /*'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'*/
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else { 
            try { 

                $bank =  Customer::where(array('mobile_no'=>$request->mobile_no))->get();       
                // if(count($bank)>0)
                // {
                    // $response['status'] = "Error";
                    // $response['msg'] = \Lang::get('Mobile No is Already Exist. Please Check your Customer List or Other Branch Customer List');
                    // return response()->json($response, 200);
                // }
                // else{
                    $tenantid = $request->tenant_id;
                    $customer = new Customer();                
                    $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $customer->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                    $customer->name = isset($request->name) ? ($request->name) : '0';
                    $cusname = $customer->name;
                    $customer->last_name = isset($request->last_name) ? ($request->last_name) : '';
					$cust_name = $customer->name . ' ' . $customer->last_name;
                    $customer->mobile_no_1 = isset($request->mobile_no_1) ? ($request->mobile_no_1) : '';
                    $customer->photo = isset($request->photo) ? ($request->photo) : '0';
                    if(isset($request->dob)) {
                        $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                        $customer->dob = date('Y-m-d',strtotime($s_date)); 
                    } 
                    else {
                        $customer->dob = '0000-00-00'; 
                    }
                    $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                    $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                    $mobileno = $customer->mobile_no;
                    $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                    $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                    $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                    $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                    $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                    $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';
                    if(isset($request->doj)) {
                        $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                        $customer->doj = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->doj = '0000-00-00';
                    }
                    $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                    $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                    $customer->door_no = isset($request->door_no) ? ($request->door_no) : '0';
                    $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                    $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                    $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                    $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                    $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                    $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                    $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                    $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                    $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '';
                    $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                    $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                    $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                    $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                    $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                    $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                    $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                    $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '';
                    $customer->permanent_door_no = isset($request->permanent_door_no) ? ($request->permanent_door_no) : '';
                    $customer->permanent_building_name = isset($request->permanent_building_name) ? ($request->permanent_building_name) : '';
                    $customer->permanent_address_line_1 = isset($request->permanent_address_line_1) ? ($request->permanent_address_line_1) : '';
                    $customer->permanent_address_line_2 = isset($request->permanent_address_line_2) ? ($request->permanent_address_line_2) : '';
                    $customer->permanent_land_mark = isset($request->permanent_land_mark) ? ($request->permanent_land_mark) : '';
                    $customer->permanent_state_id = isset($request->permanent_state_id) ? ($request->permanent_state_id) : 0;
                    $customer->permanent_district_id = isset($request->permanent_district_id) ? ($request->permanent_district_id) : 0;
                    $customer->permanent_city_id = isset($request->permanent_city_id) ? ($request->permanent_city_id) : 0;
                    $customer->permanent_pincode = isset($request->permanent_pincode) ? ($request->permanent_pincode) : '';
                    //$customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '';
                    $customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                    $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                    $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';
                    $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '';
                    $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '';
                    if(isset($request->retirement)) {
                        $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                        $customer->retirement = date('Y-m-d',strtotime($s_date));
                    }
                    else {
                        $customer->retirement = '0000-00-00';
                    }
                    $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                    $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                    $customer->income = isset($request->income) ? ($request->income) : 0;
                    $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                    $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '';
                    $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                    $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                    $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                    $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                    $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                    $customer->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : 0;
                    $customer->is_customer = isset($request->is_customer) ? ($request->is_customer) : '';
                    $customer->is_guarantor = isset($request->is_guarantor) ? ($request->is_guarantor) : '';
                    $customer->bonus_days = isset($request->bonus_days) ? ($request->bonus_days) : 0;
                    $customer->penalty_days = isset($request->penalty_days) ? ($request->penalty_days) : 0;
                    $customer->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                    $customer->status = isset($request->status) ? ($request->status) : 1; 
					
					$customer->reference_name = isset($request->reference_name) ? ($request->reference_name) : ''; 
                    $customer->family_code = isset($request->family_id) ? ($request->family_id) : '';
                    $customer->saluation = isset($request->saluation) ? ($request->saluation) : '';
                    	
					
                  
                    if($customer->save()) {
						$customer_code = $this->generateBranchCustomerCode(
                            $customer->branch_id,
                            $customer->tenant_id
                        );
                        $customer->customer_code = $customer_code;
						$customer->save();
                        if(isset($request->lead_management_id)) {
                            $lead = LeadManagement::find($request->lead_management_id);
                            $lead->is_customer = 1;
                            $lead->save();
                        }
    					$cus_id = Customer::where('branch_id','=',$request->branch_id)->max('id');
    					$customer_login=new User(); 
    					$customer_login->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
    					$customer_login->branch_id = isset($request->branch_id) ? ($request->branch_id) : '';
    					$customer_login->employee_id = $cus_id;   
    					$customer_login->email = isset($request->mobile_no) ? ($request->mobile_no) : '';   
    					$password_original = "12345"; 
    					$customer_login->password = Hash::make($password_original);							
    					$customer_login->role_id = "15";   
    					$customer_login->user_type = "3";
    					$customer_login->created_by = isset($request->created_by) ? ($request->created_by) : '';
    					$customer_login->status = isset($request->status) ? ($request->status) : 1;
                    
					   $customer_login->save();

                        $adminuser=[];
                        $tenant_id=isset($request->tenant_id) ? ($request->tenant_id) : '';
                        $branch_id=isset($request->branch_id) ? ($request->branch_id) : '';
                        $employee_id=$cus_id;
                        $email=isset($request->mobile_no) ? ($request->mobile_no) : '';
                        $password=md5($password_original);
                        $role_id="15";
                        $user_type='3';
                        $created_by=isset($request->created_by) ? ($request->created_by) : '';
                        $status=isset($request->status) ? ($request->status) : 1;

                        DB::connection('mysql1')->insert('insert into users (tenant_id, branch_id,employee_id,email,password,role_id,user_type,created_by,status,original_id) values (?,?,?,?,?,?,?,?,?,?)',[$tenant_id,$branch_id,$employee_id,$email,$password,$role_id,$user_type,$created_by,$status,$customer_login->id]);
					
                       
                       
                            $update_customer = new UpdateCustomer();                
                            $update_customer->tenant_id = isset($customer->tenant_id) ? ($customer->tenant_id) : 0;
                            $update_customer->branch_id = isset($customer->branch_id) ? ($customer->branch_id) : 0;
                            $update_customer->name = isset($customer->name) ? ($customer->name) : '';
                            $update_customer->photo = isset($customer->photo) ? ($customer->photo) : '';
                            $update_customer->customer_code = isset($customer->customer_code) ? ($customer->customer_code) : ''; 
                            $update_customer->edit_date = date('Y-m-d');
                            $update_customer->update_customer_id = isset($customer->id) ? ($customer->id) : '';
                            $update_customer->dob = isset($customer->dob) ? ($customer->dob) : '0000-00-00';
                            $update_customer->gender = isset($customer->gender) ? ($customer->gender) : 0;
                            $update_customer->mobile_no = isset($customer->mobile_no) ? ($customer->mobile_no) : '';
                            $update_customer->phone_no = isset($customer->phone_no) ? ($customer->phone_no) : '';
                            $update_customer->email_id = isset($customer->email_id) ? ($customer->email_id) : '';
                            $update_customer->adhar_no = isset($customer->adhar_no) ? ($customer->adhar_no) : '';
                            $update_customer->pan_no = isset($customer->pan_no) ? ($customer->pan_no) : '';
                            $update_customer->license_no = isset($customer->license_no) ? ($customer->license_no) : '';
                            $update_customer->ration_card_no = isset($customer->ration_card_no) ? ($customer->ration_card_no) : '';
                            $update_customer->doj = isset($customer->doj) ? ($customer->doj) : '0000-00-00';
                            $update_customer->father_name = isset($customer->father_name) ? ($customer->father_name) : '';
                            $update_customer->spouse_name = isset($customer->spouse_name) ? ($customer->spouse_name) : '';
                            $update_customer->door_no = isset($customer->door_no) ? ($customer->door_no) : '0';
                            $update_customer->buildling_name = isset($customer->buildling_name) ? ($customer->buildling_name) : '';
                            $update_customer->address_line_1 = isset($customer->address_line_1) ? ($customer->address_line_1) : '';
                            $update_customer->address_line_2 = isset($customer->address_line_2) ? ($customer->address_line_2) : '';
                            $update_customer->landmark = isset($customer->landmark) ? ($customer->landmark) : '';
                            $update_customer->state_id = isset($customer->state_id) ? ($customer->state_id) : 0;
                            $update_customer->district_id = isset($customer->district_id) ? ($customer->district_id) : 0;
                            $update_customer->city_id = isset($customer->city_id) ? ($customer->city_id) : 0;
                            $update_customer->pincode = isset($customer->pincode) ? ($customer->pincode) : '0';
                            $update_customer->communication_door_no = isset($customer->communication_door_no) ? ($customer->communication_door_no) : '';
                            $update_customer->communication_building_name = isset($customer->communication_building_name) ? ($customer->communication_building_name) : '';
                            $update_customer->communication_address_line_1 = isset($customer->communication_address_line_1) ? ($customer->communication_address_line_1) : '';
                            $update_customer->communication_address_line_2 = isset($customer->communication_address_line_2) ? ($customer->communication_address_line_2) : '';
                            $update_customer->communication_land_mark = isset($customer->communication_land_mark) ? ($customer->communication_land_mark) : '';
                            $update_customer->communication_state_id = isset($customer->communication_state_id) ? ($customer->communication_state_id) : 0;
                            $update_customer->communication_district_id = isset($customer->communication_district_id) ? ($customer->communication_district_id) : 0;
                            $update_customer->communication_city_id = isset($customer->communication_city_id) ? ($customer->communication_city_id) : 0;
                            $update_customer->communication_pincode = isset($customer->communication_pincode) ? ($customer->communication_pincode) : '';
                        
                            $update_customer->reference_type = isset($customer->reference_type) ? ($customer->reference_type) : '';
                            $update_customer->introducer_name = isset($customer->introducer_name) ? ($customer->introducer_name) : '';
                            $update_customer->introducer_id = isset($customer->introducer_id) ? ($customer->introducer_id) : '';
                            $update_customer->pf_no = isset($customer->pf_no) ? ($customer->pf_no) : '';
                            $update_customer->esi_no = isset($customer->esi_no) ? ($customer->esi_no) : '';
                            $update_customer->retirement = isset($customer->retirement) ? ($customer->retirement) : '0000-00-00';
                            $update_customer->occupation = isset($customer->occupation) ? ($customer->occupation) : 0;
                            $update_customer->source_of_funds = isset($customer->source_of_funds) ? ($customer->source_of_funds) : 0;
                            $update_customer->income = isset($customer->income) ? ($customer->income) : 0;
                            $update_customer->relationship_id = isset($customer->relationship_id) ? ($customer->relationship_id) : 0;
                            $update_customer->customer_belongs_to=isset($customer->customer_belongs_to) ? ($customer->customer_belongs_to) : '';
                            $update_customer->customer_bonus = isset($customer->customer_bonus) ? ($customer->customer_bonus) : 0;
                            $update_customer->customer_penalty_interest = isset($customer->customer_penalty_interest) ? ($customer->customer_penalty_interest) : 0;
                            $update_customer->customer_interest=isset($customer->customer_interest) ? ($customer->customer_interest) : 0;
                            $update_customer->customer_divident = isset($customer->customer_divident) ? ($customer->customer_divident) : 0;
                            $update_customer->sms = isset($customer->sms) ? ($customer->sms) : 0;
                            $update_customer->lead_management_id = isset($customer->lead_management_id) ? ($customer->lead_management_id) : 0;
                            $update_customer->is_customer = isset($customer->is_customer) ? ($customer->is_customer) : '';
                            $update_customer->is_guarantor = isset($customer->is_guarantor) ? ($customer->is_guarantor) : '';
                            $update_customer->bonus_days = isset($customer->bonus_days) ? ($customer->bonus_days) : 0;
                            $update_customer->penalty_days = isset($customer->penalty_days) ? ($customer->penalty_days) : 0;
                            $update_customer->created_by = isset($customer->created_by) ? ($customer->created_by) : 0;
                            $update_customer->status = isset($customer->status) ? ($customer->status) : 1; 
                           // $update_customer->reference_name = isset($customer->r_name) ? ($customer->r_name) : ''; 
                            if($update_customer->save()){
								
								
								  

$msg = "Welcome to SPN Chit Funds Pvt. Ltd!
 
Your Customer id is $customer_code and registered mobile no is $request->mobile_no.
 
Our Business Advisor will get in touch with you soon. Do save our number 8807906006.
 
Download our app in Google Playstore to maintain your Chit account Online
https://bit.ly/4dpVmGi - SPN Chits Pvt Ltd";


	$msg1=$msg;
		$receiver = $request->mobile_no;
		$msg_split = str_split($msg,456);
		
		  
				
            	$ch = curl_init("https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=$msg");
            
            	
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				  $result = curl_exec($ch);
				curl_close($ch);
                               
                                if (!empty($request->temp_chits) && is_array($request->temp_chits)) {
                                    $chitCtrl = app(ChitDetailController::class);
                                    foreach ($request->temp_chits as $tempChit) {
                                        $employeeId = !empty($tempChit['employee_id'])
                                            ? $tempChit['employee_id']
                                            : (isset($request->employee_id) ? $request->employee_id : 0);
                                        if (empty($employeeId) && !empty($request->logged_employee_id)) {
                                            $employeeId = $request->logged_employee_id;
                                        }
                                        $tempReq = new Request(array_merge((array) $tempChit, [
                                            'tenant_id' => $customer->tenant_id,
                                            'branch_id' => $customer->branch_id,
                                            'customer_id' => $customer->id,
                                            'collection_pin' => $customer->communication_pincode ?: 0,
                                            'employee_id' => $employeeId,
                                            'created_by' => isset($request->created_by) ? $request->created_by : 0,
                                        ]));
                                        $chitCtrl->store_temp_commitment_chit($tempReq);
                                    }
                                }

                                $response['status'] = 'Success';
                                $response['msg'] = \Lang::get('api.success_customer_added');
                                $response['data'] = $customer;
                                return response()->json($response,200);
                            } else {
                                $response['status']="Error";
                               // $response['msg']=\Lang::get('api.global_error');
                                return response()->json($response,401);
                            } 
                        
                    }
                //} 
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['exep'] = $e;
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

    public function update_customer(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            /*'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'*/
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {   
                    $customer = Customer::find($request->id);
                    $oldBranchId = (int) ($customer->branch_id ?? 0);
                    // Branch change is blocked on edit — use store-customer-branch-transfer instead
                    $newBranchId = $oldBranchId;
                    $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $customer->branch_id = $oldBranchId;
                    $customer->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
                    $customer->customer_code = isset($request->customer_code) ? ($request->customer_code) : '';
                    $customer->name = isset($request->name) ? ($request->name) : '';
                    $customer->last_name = isset($request->last_name) ? ($request->last_name) : '';
                    $customer->mobile_no_1 = isset($request->mobile_no_1) ? ($request->mobile_no_1) : '';
                    $customer->photo = isset($request->photo) ? ($request->photo) : '0';
                    if(isset($request->dob)) {
                        $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                        $customer->dob = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->dob = '0000-00-00';
                    }
                    $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                    $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                    $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                    $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                    $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                    $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                    $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                    $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';
                     if(isset($request->doj)) {
                        $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                        $customer->doj = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->doj = '0000-00-00';
                    }
                    $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                    $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                    $customer->door_no = isset($request->door_no) ? ($request->door_no) : '';
                    $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                    $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                    $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                    $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                    $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                    $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                    $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                    $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                    $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '';
                    $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                    $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                    $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                    $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                    $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                    $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                    $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                    $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '';
                    $customer->permanent_door_no = isset($request->permanent_door_no) ? ($request->permanent_door_no) : '';
                    $customer->permanent_building_name = isset($request->permanent_building_name) ? ($request->permanent_building_name) : '';
                    $customer->permanent_address_line_1 = isset($request->permanent_address_line_1) ? ($request->permanent_address_line_1) : '';
                    $customer->permanent_address_line_2 = isset($request->permanent_address_line_2) ? ($request->permanent_address_line_2) : '';
                    $customer->permanent_land_mark = isset($request->permanent_land_mark) ? ($request->permanent_land_mark) : '';
                    $customer->permanent_state_id = isset($request->permanent_state_id) ? ($request->permanent_state_id) : 0;
                    $customer->permanent_district_id = isset($request->permanent_district_id) ? ($request->permanent_district_id) : 0;
                    $customer->permanent_city_id = isset($request->permanent_city_id) ? ($request->permanent_city_id) : 0;
                    $customer->permanent_pincode = isset($request->permanent_pincode) ? ($request->permanent_pincode) : '';
                    //$customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '';
                    $customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                    $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                    $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';
                    $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '';
                    $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '';
                    if(isset($request->retirement)) {
                        $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                        $customer->retirement = date('Y-m-d',strtotime($s_date));
                    }
                    else {
                        $customer->retirement = '0000-00-00';
                    }
                    $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                    $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                    $customer->income = isset($request->income) ? ($request->income) : 0;
                    $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                    $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '';
                    $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                    $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                    $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                    $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                    $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                    $customer->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : 0;
                    $customer->is_customer = isset($request->is_customer) ? ($request->is_customer) : '';
                    $customer->is_guarantor = isset($request->is_guarantor) ? ($request->is_guarantor) : '';
                    $customer->bonus_days = isset($request->bonus_days) ? ($request->bonus_days) : 0;
                    $customer->penalty_days = isset($request->penalty_days) ? ($request->penalty_days) : 0;
                    $customer->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					
				   if($request->tenant_id == 21){
					$customer->reference_name = isset($request->r_name) ? ($request->r_name) : ''; 
					
                    //$customer->family_code = isset($request->family_id) ? ($request->family_id) : '';
					
                    $customer->saluation = isset($request->saluation) ? ($request->saluation) : '';
					
					} else {
						
					}
					
                    if($customer->save()) {
                        $update_customer = new UpdateCustomer();
                        $update_customer->tenant_id = isset($customer->tenant_id) ? ($customer->tenant_id) : 0;
                        $update_customer->branch_id = isset($customer->branch_id) ? ($customer->branch_id) : 0;
                        $update_customer->customer_code = isset($customer->customer_code) ? ($customer->customer_code) : '';
                        $update_customer->edit_date = date('Y-m-d');
                        $update_customer->update_customer_id = isset($customer->id) ? ($customer->id) : '';
                        $update_customer->name = isset($customer->name) ? ($customer->name) : '';
                        $update_customer->photo = isset($customer->photo) ? ($customer->photo) : '';
                        $update_customer->dob = isset($customer->dob) ? ($customer->dob) : '0000-00-00';
                        $update_customer->gender = isset($customer->gender) ? ($customer->gender) : 0;
                        $update_customer->mobile_no = isset($customer->mobile_no) ? ($customer->mobile_no) : '';
                        $update_customer->phone_no = isset($customer->phone_no) ? ($customer->phone_no) : '';
                        $update_customer->email_id = isset($customer->email_id) ? ($customer->email_id) : '';
                        $update_customer->adhar_no = isset($customer->adhar_no) ? ($customer->adhar_no) : '';
                        $update_customer->pan_no = isset($customer->pan_no) ? ($customer->pan_no) : '';
                        $update_customer->license_no = isset($customer->license_no) ? ($customer->license_no) : '';
                        $update_customer->ration_card_no = isset($customer->ration_card_no) ? ($customer->ration_card_no) : '';
                        $update_customer->doj = isset($customer->doj) ? ($customer->doj) : '0000-00-00';
                        $update_customer->father_name = isset($customer->father_name) ? ($customer->father_name) : '';
                        $update_customer->spouse_name = isset($customer->spouse_name) ? ($customer->spouse_name) : '';
                        $update_customer->door_no = isset($customer->door_no) ? ($customer->door_no) : '';
                        $update_customer->buildling_name = isset($customer->buildling_name) ? ($customer->buildling_name) : '';
                        $update_customer->address_line_1 = isset($customer->address_line_1) ? ($customer->address_line_1) : '';
                        $update_customer->address_line_2 = isset($customer->address_line_2) ? ($customer->address_line_2) : '';
                        $update_customer->landmark = isset($customer->landmark) ? ($customer->landmark) : '';
                        $update_customer->state_id = isset($customer->state_id) ? ($customer->state_id) : 0;
                        $update_customer->district_id = isset($customer->district_id) ? ($customer->district_id) : 0;
                        $update_customer->city_id = isset($customer->city_id) ? ($customer->city_id) : 0;
                        $update_customer->pincode = isset($customer->pincode) ? ($customer->pincode) : '';
                        $update_customer->communication_door_no = isset($customer->communication_door_no) ? ($customer->communication_door_no) : '';
                        $update_customer->communication_building_name = isset($customer->communication_building_name) ? ($customer->communication_building_name) : '';
                        $update_customer->communication_address_line_1 = isset($customer->communication_address_line_1) ? ($customer->communication_address_line_1) : '';
                        $update_customer->communication_address_line_2 = isset($customer->communication_address_line_2) ? ($customer->communication_address_line_2) : '';
                        $update_customer->communication_land_mark = isset($customer->communication_land_mark) ? ($customer->communication_land_mark) : '';
                        $update_customer->communication_state_id = isset($customer->communication_state_id) ? ($customer->communication_state_id) : 0;
                        $update_customer->communication_district_id = isset($customer->communication_district_id) ? ($customer->communication_district_id) : 0;
                        $update_customer->communication_city_id = isset($customer->communication_city_id) ? ($customer->communication_city_id) : 0;
                        $update_customer->communication_pincode = isset($customer->communication_pincode) ? ($customer->communication_pincode) : '';
                        //$update_customer->refered_by = isset($customer->refered_by) ? ($customer->refered_by) : '';
                        $update_customer->reference_type = isset($customer->reference_type) ? ($customer->reference_type) : '';
                        $update_customer->introducer_name = isset($customer->introducer_name) ? ($customer->introducer_name) : '';
                        $update_customer->introducer_id = isset($customer->introducer_id) ? ($customer->introducer_id) : '';
                        $update_customer->pf_no = isset($customer->pf_no) ? ($customer->pf_no) : '';
                        $update_customer->esi_no = isset($customer->esi_no) ? ($customer->esi_no) : '';
                        $update_customer->retirement = isset($customer->retirement) ? ($customer->retirement) : '0000-00-00';
                        $update_customer->occupation = isset($customer->occupation) ? ($customer->occupation) : 0;
                        $update_customer->source_of_funds = isset($customer->source_of_funds) ? ($customer->source_of_funds) : 0;
                        $update_customer->income = isset($customer->income) ? ($customer->income) : 0;
                        $update_customer->relationship_id = isset($customer->relationship_id) ? ($customer->relationship_id) : 0;
                        $update_customer->customer_belongs_to=isset($customer->customer_belongs_to) ? ($customer->customer_belongs_to) : '';
                        $update_customer->customer_bonus = isset($customer->customer_bonus) ? ($customer->customer_bonus) : 0;
                        $update_customer->customer_penalty_interest = isset($customer->customer_penalty_interest) ? ($customer->customer_penalty_interest) : 0;
                        $update_customer->customer_interest=isset($customer->customer_interest) ? ($customer->customer_interest) : 0;
                        $update_customer->customer_divident = isset($customer->customer_divident) ? ($customer->customer_divident) : 0;
                        $update_customer->sms = isset($customer->sms) ? ($customer->sms) : 0;
                        $update_customer->lead_management_id = isset($customer->lead_management_id) ? ($customer->lead_management_id) : 0;
                        $update_customer->is_customer = isset($customer->is_customer) ? ($customer->is_customer) : '';
                        $update_customer->is_guarantor = isset($customer->is_guarantor) ? ($customer->is_guarantor) : '';
                        $update_customer->bonus_days = isset($customer->bonus_days) ? ($customer->bonus_days) : 0;
                        $update_customer->penalty_days = isset($customer->penalty_days) ? ($customer->penalty_days) : 0;
                        $update_customer->created_by = isset($customer->created_by) ? ($customer->created_by) : 0;
                        $update_customer->status = isset($customer->status) ? ($customer->status) : 1;
                        try {
                            $update_customer->save();
                        } catch (\Exception $auditEx) {
                            \Log::warning('Customer update audit log failed: ' . $auditEx->getMessage());
                        }

                        $chitErrors = [];
                        $chitsSaved = 0;
                        if (!empty($request->temp_chits) && is_array($request->temp_chits)) {
                            $chitCtrl = app(ChitDetailController::class);
                            foreach ($request->temp_chits as $tempChit) {
                                $employeeId = !empty($tempChit['employee_id'])
                                    ? $tempChit['employee_id']
                                    : (isset($request->employee_id) ? $request->employee_id : 0);
                                if (empty($employeeId) && !empty($request->logged_employee_id)) {
                                    $employeeId = $request->logged_employee_id;
                                }
                                $tempReq = new Request(array_merge((array) $tempChit, [
                                    'tenant_id' => $customer->tenant_id,
                                    'branch_id' => $customer->branch_id,
                                    'customer_id' => $customer->id,
                                    'collection_pin' => $customer->communication_pincode ?: 0,
                                    'employee_id' => $employeeId,
                                    'logged_employee_id' => $request->logged_employee_id ?? $employeeId,
                                    'created_by' => isset($request->updated_by) ? $request->updated_by : (isset($request->created_by) ? $request->created_by : 0),
                                    'logged_user_id' => $request->logged_user_id ?? (isset($request->updated_by) ? $request->updated_by : 0),
                                ]));
                                $chitResponse = $chitCtrl->store_temp_commitment_chit($tempReq);
                                $chitPayload = json_decode($chitResponse->getContent(), true);
                                if (
                                    !empty($chitPayload['status'])
                                    && $chitPayload['status'] === 'Success'
                                    && !empty($chitPayload['data']['id'])
                                ) {
                                    $chitsSaved++;
                                } else {
                                    $chitErrors[] = $chitPayload['msg'] ?? 'Failed to save new chit';
                                }
                            }
                        }

                        if (!empty($request->temp_chits) && is_array($request->temp_chits) && $chitsSaved === 0) {
                            $response['status'] = 'Error';
                            $response['msg'] = !empty($chitErrors)
                                ? implode('; ', $chitErrors)
                                : \Lang::get('api.global_error');
                            $response['data'] = $customer;
                            return response()->json($response, 200);
                        }

                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_customer_updated');
                        if (!empty($chitErrors)) {
                            $response['msg'] .= ' (' . implode('; ', $chitErrors) . ')';
                        }
                        $response['data'] = $customer;
                        $response['chits_saved'] = $chitsSaved;
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
                return response()->json($response, 401);
            }
        }
    }

    /**
     * Transfer customer to another branch with remarks.
     * Updates customers + chit_details + before_enrol_receipts + receipts + commitment_auctions.
     */
    public function store_customer_branch_transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'to_branch_id' => 'required',
            'remarks' => 'required|min:3',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'Error',
                'msg' => $validator->errors()->first(),
            ], 200);
        }

        try {
            $this->ensureCustomerBranchTransfersTable();
            $customer = Customer::find($request->customer_id);
            if (!$customer) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer not found'], 200);
            }

            $fromBranchId = (int) ($customer->branch_id ?? 0);
            $toBranchId = (int) $request->to_branch_id;
            if ($toBranchId <= 0) {
                return response()->json(['status' => 'Error', 'msg' => 'Invalid target branch'], 200);
            }
            if ($fromBranchId === $toBranchId) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer is already in the selected branch'], 200);
            }

            DB::beginTransaction();
            $customer->branch_id = $toBranchId;
            $customer->updated_by = isset($request->transferred_by) ? $request->transferred_by : (isset($request->created_by) ? $request->created_by : 0);
            $customer->save();

            $syncCounts = $this->syncCustomerRelatedBranchIds((int) $customer->id, $toBranchId);

            $transferDate = date('Y-m-d');
            if (isset($request->transfer_date) && is_array($request->transfer_date) && !empty($request->transfer_date['year'])) {
                $transferDate = sprintf(
                    '%04d-%02d-%02d',
                    (int) $request->transfer_date['year'],
                    (int) $request->transfer_date['month'],
                    (int) $request->transfer_date['day']
                );
            } elseif (!empty($request->transfer_date) && is_string($request->transfer_date)) {
                $transferDate = date('Y-m-d', strtotime($request->transfer_date));
            }

            $transferId = DB::table('customer_branch_transfers')->insertGetId([
                'tenant_id' => (int) ($customer->tenant_id ?: ($request->tenant_id ?? 0)),
                'customer_id' => (int) $customer->id,
                'from_branch_id' => $fromBranchId,
                'to_branch_id' => $toBranchId,
                'remarks' => (string) $request->remarks,
                'transferred_by' => (int) ($request->transferred_by ?? $request->created_by ?? 0),
                'transfer_date' => $transferDate,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            DB::commit();

            $fromName = optional(Branch::find($fromBranchId))->branch_name ?: $fromBranchId;
            $toName = optional(Branch::find($toBranchId))->branch_name ?: $toBranchId;

            return response()->json([
                'status' => 'Success',
                'msg' => 'Customer transferred from ' . $fromName . ' to ' . $toName
                    . '. Related records updated (chits: ' . $syncCounts['chit_details']
                    . ', before enrol: ' . $syncCounts['before_enrol_receipts']
                    . ', receipts: ' . $syncCounts['receipts']
                    . ', auctions: ' . $syncCounts['commitment_auctions'] . ').',
                'data' => [
                    'id' => $transferId,
                    'customer_id' => (int) $customer->id,
                    'from_branch_id' => $fromBranchId,
                    'to_branch_id' => $toBranchId,
                    'sync' => $syncCounts,
                ],
            ], 200);
        } catch (\Exception $e) {
            try { DB::rollBack(); } catch (\Exception $ignore) {}
            \Log::error('store_customer_branch_transfer failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to transfer customer',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    public function list_customer_branch_transfers(Request $request)
    {
        try {
            $this->ensureCustomerBranchTransfersTable();
            $query = DB::table('customer_branch_transfers as t')
                ->leftJoin('customers as c', 'c.id', '=', 't.customer_id')
                ->leftJoin('branches as fb', 'fb.id', '=', 't.from_branch_id')
                ->leftJoin('branches as tb', 'tb.id', '=', 't.to_branch_id')
                ->orderBy('t.transfer_date', 'desc')
                ->orderBy('t.id', 'desc');

            if (!empty($request->tenant_id)) {
                $query->where('t.tenant_id', $request->tenant_id);
            }
            if (!empty($request->customer_id)) {
                $query->where('t.customer_id', $request->customer_id);
            }
            if (!empty($request->from_branch_id)) {
                $query->where('t.from_branch_id', $request->from_branch_id);
            }
            if (!empty($request->to_branch_id)) {
                $query->where('t.to_branch_id', $request->to_branch_id);
            }

            $startDate = '';
            $endDate = '';
            if (isset($request->start_date) && is_array($request->start_date) && !empty($request->start_date['year'])) {
                $startDate = sprintf(
                    '%04d-%02d-%02d',
                    (int) $request->start_date['year'],
                    (int) $request->start_date['month'],
                    (int) $request->start_date['day']
                );
            } elseif (!empty($request->start_date) && is_string($request->start_date)) {
                $startDate = date('Y-m-d', strtotime($request->start_date));
            }
            if (isset($request->end_date) && is_array($request->end_date) && !empty($request->end_date['year'])) {
                $endDate = sprintf(
                    '%04d-%02d-%02d',
                    (int) $request->end_date['year'],
                    (int) $request->end_date['month'],
                    (int) $request->end_date['day']
                );
            } elseif (!empty($request->end_date) && is_string($request->end_date)) {
                $endDate = date('Y-m-d', strtotime($request->end_date));
            }
            if ($startDate !== '' && $endDate !== '') {
                $query->whereBetween('t.transfer_date', [$startDate, $endDate]);
            } elseif ($startDate !== '') {
                $query->whereDate('t.transfer_date', '>=', $startDate);
            } elseif ($endDate !== '') {
                $query->whereDate('t.transfer_date', '<=', $endDate);
            }

            $rows = $query->select(
                't.*',
                'c.name as customer_first_name',
                'c.last_name as customer_last_name',
                'c.customer_code',
                'c.mobile_no',
                'fb.branch_name as from_branch_name',
                'tb.branch_name as to_branch_name'
            )->limit(1000)->get();

            $data = [];
            $i = 1;
            foreach ($rows as $row) {
                $data[] = [
                    'sno' => $i++,
                    'id' => (int) $row->id,
                    'customer_id' => (int) $row->customer_id,
                    'customer_name' => trim(($row->customer_first_name ?? '') . ' ' . ($row->customer_last_name ?? '')),
                    'customer_code' => $row->customer_code ?? '',
                    'mobile_no' => $row->mobile_no ?? '',
                    'from_branch_id' => (int) $row->from_branch_id,
                    'from_branch_name' => $row->from_branch_name ?? '',
                    'to_branch_id' => (int) $row->to_branch_id,
                    'to_branch_name' => $row->to_branch_name ?? '',
                    'remarks' => $row->remarks ?? '',
                    'transfer_date' => (!empty($row->transfer_date) && $row->transfer_date != '0000-00-00')
                        ? date('d-m-Y', strtotime($row->transfer_date))
                        : '',
                    'transfer_date_raw' => $row->transfer_date,
                    'transferred_by' => (int) ($row->transferred_by ?? 0),
                ];
            }

            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Unable to load branch transfer report',
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    private function ensureCustomerBranchTransfersTable()
    {
        if (Schema::hasTable('customer_branch_transfers')) {
            return;
        }
        Schema::create('customer_branch_transfers', function ($table) {
            $table->bigIncrements('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('customer_id')->default(0);
            $table->integer('from_branch_id')->default(0);
            $table->integer('to_branch_id')->default(0);
            $table->text('remarks')->nullable();
            $table->integer('transferred_by')->default(0);
            $table->date('transfer_date')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'transfer_date']);
            $table->index(['customer_id']);
        });
    }

    /**
     * When a customer moves branch, keep related transactional rows on the same branch_id.
     */
    private function syncCustomerRelatedBranchIds(int $customerId, int $newBranchId): array
    {
        $counts = [
            'chit_details' => 0,
            'before_enrol_receipts' => 0,
            'receipts' => 0,
            'commitment_auctions' => 0,
        ];

        $counts['chit_details'] = ChitDetail::where('customer_id', $customerId)
            ->update(['branch_id' => $newBranchId]);

        $counts['before_enrol_receipts'] = BeforeEnrolReceipt::where('customer_id', $customerId)
            ->update(['branch_id' => $newBranchId]);

        $counts['receipts'] = Receipt::where('customer_id', $customerId)
            ->update(['branch_id' => $newBranchId]);

        $enrollmentIds = ChitDetail::where('customer_id', $customerId)->pluck('id');
        $counts['commitment_auctions'] = CommitmentAuction::where(function ($q) use ($customerId, $enrollmentIds) {
            $q->where('customer_id', $customerId);
            if ($enrollmentIds->isNotEmpty()) {
                $q->orWhereIn('enrollment_id', $enrollmentIds);
            }
        })->update(['branch_id' => $newBranchId]);

        return $counts;
    }

    public function get_customers(Request $request) {
        try {
            $cond = $request->conditions; 
            $is_for_edit = isset($request->for_edit) ? ($request->for_edit) : 0; 
            $customers = Customer::where($cond)->where('is_customer','!=',0)->orderBy('name','asc')->get();
            if($is_for_edit) {
                $customers = Customer::where($cond)->whereNotIn('id',[$is_for_edit])->where('is_customer','!=',0)->orderBy('id','desc')->get();
            } 
            $i=1;
            foreach($customers as $customer) {
                $customer->sno = $i;
                $customer->full_name = $customer->name . ' ' . $customer->last_name . ' || ' . $customer->customer_code;
				$customer->first_name = $customer->name;
				$customer->name = $customer->name . ' ' . $customer->last_name . ' / ' . $customer->customer_code;
                $customer->formatted_name = $customer->name.' || '.$customer->customer_code;
                $customer->tenant_info = isset($customer->tenant_det) ? ($customer->tenant_det) : [];
                $customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
                $customer->scheme_format = isset($customer->scheme_det) ? $customer->scheme_det->chit_value.'('.$customer->scheme_det->no_of_months.' M)' : [];
				$customer->family_det = '';
				
                $customer->branch_name = isset($customer->branch_det) ? ($customer->branch_det->branch_name) : '';
              
                $customer->dob = ($customer->dob!=0) ? date('d/m/Y',strtotime($customer->dob)) : '';                  
                $customer->doj = ($customer->doj!=0) ? date('d/m/Y',strtotime($customer->doj)) : '';    

			    $customer->retirement = ($customer->retirement!=0) ? date('d/m/Y',strtotime($customer->retirement)) : '';                  
			    $customer->photo_for_view = ($customer->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';
                
				if($customer->tenant_id == 21){
					$customer->r_name = $customer->reference_name;  
					} else {
						
					}
                //$customer->family_id = $customer->family_code;
                $customer->saluation = $customer->saluation;
                $customer->introducer_full_name = '';
                $customer->is_delete = '0';
				//$customer->saluation = 'mister';
                $customer->introducer_info = [];
                // Static placeholders until the customer score formula is finalized.
                $customer->remarks = 'Remarks pending review';
                $customer->customer_score = 75;
                $customercount=ChitDetail::where('customer_id',$customer->id)->count();
                if($customercount>0){
                    $customer->is_delete = '1';
                }
                // print_r($customer->reference_type);
                
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuss'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_customers(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $customers = Customer::where('tenant_id',$request->tenant_id)->where('is_customer','!=',0)->orderBy('id','desc')->get();
            }
            else {
                $customers = Customer::orderBy('id','desc')->where('is_customer','!=',0)->get();
            }
            $i=1;
            foreach($customers as $customer) {
				$customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
                if($customer->photo!='') 
                {
                    $customer->photo = (isset($customer->photo)) ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';
                }
                $customer->sno = $i;
                $i++;
				
            }
			
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_customer(Request $request) {
        try {
            if(isset($request->id)) {
                // $otp=mt_rand(1000,9999);
                // $todaydate=date('Y-m-d H:i:s');
                $customer = Customer::find($request->id); 
              //  $userdetails=User::where('email',$customer->mobile_no)->get()[0];
                $customer->deleted_by = $request->deleted_by;
                //$customer->mobile_no = $customer->mobile_no.'.'.$otp;
                $customer->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
				$customer->status = 0;
                if($customer->save()) {
                    $customer->delete();

                    // $userdetails->email=$customer->mobile_no;
                    // $userdetails->save();
                    // $userdetails->delete();

                    // DB::connection('mysql1')->update('update users set deletion_remark="'.$request->deletion_remark.'",deleted_by="'.$request->deleted_by.'",deleted_at="'.$todaydate.'",email="'.$customer->mobile_no.'" where tenant_id="'.$customer->tenant_id.'" and original_id="'.$userdetails->id.'" ');

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_customer_deleted');
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
                $customer = Customer::find($request->id); 
                $customer->status = ($customer->status) ? 0 : 1;
                if($customer->save()) {
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
            if ($request->hasFile('photo')) 
            {
                $photo = '';    
                if ($request->file('photo')->isValid())
                {  
                   $photo = $request->file('photo')->hashName();
                   $location1 = $request->file('photo')->store('uploads/customer_photos');
                }
                $response['file_name'] = $photo;
                $response['file_url'] = ($photo != '') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$photo) : '';
            }
            else 
            {
                $response['file_name'] = '';
                $response['file_url'] = '';
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
	
	 public function get_customer_wise_outstanding(Request $request) {
		try { 
    		$cond = [];
    		if($request->branch_id > 0){
    			$cond['branch_id'] = $request->branch_id; 
    		}
            $customers = Customer::where($cond)->orderBy('id','desc')->get();
            if($request->branch_id ==0){
                $customers = Customer::orderBy('id','desc')->get(); 
            }
    		$i=1;
            foreach($customers as $customer) {
                $customer->sno = $i;
                $customer->tenant_info = isset($customer->tenant_det) ? ($customer->tenant_det) : [];
                $customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
    			//available groups
    			$enrol_groups = ChitDetail::where(array("customer_id"=>$customer->id))->count();
    			$customer->enrol_group_count = $enrol_groups;
    			$enrol_group_each = ChitDetail::where(array("customer_id"=>$customer->id))->get();
    			$overall_inst_amt = 0;
    			foreach($enrol_group_each as $enrol_each) {
    				$group_id = $enrol_each->group_id;
    				$overall_inst_amt = BiddingDetail::selectRaw('sum(current_installment_amount) as total_inst_amt')->where('group_id',$group_id)->get();
    				$overall_inst_amt2 = $overall_inst_amt;
    			};
    			//paid_amount
    			$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('customer_id',$customer->id)->get();
    			$paid_rec_amount = $receipt[0]->total_paid;
    			$bonus_amount = $receipt[0]->bonus_amount;
    			$paid_amount = $paid_rec_amount + $bonus_amount;
    			$cd->overall_paid_amount = $paid_amount;
    			//pending_amount 
    			$pending_amount = $total_installment_amount - $paid_amount;
    			$cd->overall_pending_amount = $pending_amount;
    			$cd->chit_value = $chit_value;
                $i++;
            } 
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $bidding_details;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function store_guarantor(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else { 
            try { 
                $customer = new Customer();                
                $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
				$customer->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $customer->name = isset($request->name) ? ($request->name) : '';
                $customer->photo = isset($request->photo) ? ($request->photo) : '';
                if(isset($request->dob)) {
                    $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                    $customer->dob = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $customer->dob = '0000-00-00';
                }
                $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';
                if(isset($request->doj)) {
                    $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                    $customer->doj = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $customer->doj = '0000-00-00';
                }
                $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                $customer->door_no = isset($request->door_no) ? ($request->door_no) : '';
                $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '';
                $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '';
                $customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '';
                /*$customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';*/
                $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '';
                $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '';
                if(isset($request->retirement)) {
                    $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                    $customer->retirement = date('Y-m-d',strtotime($s_date));
                }
                else {
                    // $customer->retirement = '0000-00-00';
                }
                $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                $customer->income = isset($request->income) ? ($request->income) : 0;
                $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '';
                $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                $customer->is_customer = isset($request->is_customer) ? ($request->is_customer) : '';
                $customer->is_guarantor = isset($request->is_guarantor) ? ($request->is_guarantor) : '';
                $customer->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $customer->status = isset($request->status) ? ($request->status) : 1;
                // print_r($customer);exit;
                if($customer->save()) {
                    $guarantor = new GuarantorRelation(); 
					$guarantor->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $guarantor->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $guarantor->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                    $guarantor->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
					$guarantor_id = Customer::where('branch_id',$request->branch_id)->max('id');
                    $guarantor->guarantor_id = $guarantor_id;
					$guarantor->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                    $guarantor->status = isset($request->status) ? ($request->status) : 1;
					$guarantor->save();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_customer_added');
                    $response['data'] = $customer;
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
	
	public function list_guarantors(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $guarantors = GuarantorRelation::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else { 
                $guarantors = GuarantorRelation::orderBy('id','desc')->get();
            } 
            $i=1; 
            foreach($guarantors as $guarantor) {
				$guarantor->branch_info = isset($guarantor->branch_det) ? ($guarantor->branch_det) : [];
				$guarantor->customer_info = isset($guarantor->customer_det) ? ($guarantor->customer_det) : [];
				$guarantor->enrollment_info = isset($guarantor->enrol_det) ? ($guarantor->enrol_det) : [];
				$guarantor->guarantor_info = isset($guarantor->guarantor_det) ? ($guarantor->guarantor_det) : [];
                if($guarantor->photo!='') 
                {
                    $guarantor->photo = (isset($guarantor->photo)) ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$guarantor->photo) : '';
                }
                $guarantor->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $guarantors;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
    public function get_guarantors(Request $request) {
        try {
            $cond = $request->conditions;
            $guarantors = GuarantorRelation::where($cond)->orderBy('id','desc')->get();
            $i=1;
            foreach($guarantors as $guarantor) {
                $guarantor->sno = $i;
                $guarantor->tenant_info = isset($guarantor->tenant_det) ? ($guarantor->tenant_det) : [];
                $guarantor->branch_info = isset($guarantor->branch_det) ? ($guarantor->branch_det) : [];
                $guarantor->branch_name = isset($guarantor->branch_det) ? ($guarantor->branch_det->branch_name) : '';
                $guarantor->customer_info = isset($guarantor->customer_det) ? ($guarantor->customer_det) : [];
                $guarantor->customer_name = isset($guarantor->customer_det) ? ($guarantor->customer_det->name) : '';
                $guarantor->enrollment_info = isset($guarantor->enrol_det) ? ($guarantor->enrol_det) : [];
				$guarantor->guarantor_info = [];
				$guarantor->state_info = [];
				$guarantor->district_info = [];
				$guarantor->city_info = [];
				$guarantor->communication_state_info = [];
				$guarantor->communication_district_info = [];
				$guarantor->communication_city_info = [];
				$guarantor->relationship_info = [];
				$guarantor->lead_management_info = [];
				$guarantor->dob = '';
				$guarantor->doj = '';
				$guarantor->retirement = '';
				$guarantor->photo_for_view = '';
				$guarantor->enrol_name = "";
                $guarantor->guarantor_name = "";
				 
				if(isset($guarantor->guarantor_det)) {
					$guarantor->guarantor_info = isset($guarantor->guarantor_det) ? ($guarantor->guarantor_det) : [];
                    $guarantor->guarantor_name = isset($guarantor->guarantor_det) ? ($guarantor->guarantor_det->name) : '';
					$guarantor->state_info = isset($guarantor->guarantor_det->state_det) ? ($guarantor->guarantor_det->state_det) : [];
					
					$guarantor->district_info = isset($guarantor->guarantor_det->district_det) ? ($guarantor->guarantor_det->district_det) : [];
					$guarantor->city_info = isset($guarantor->guarantor_det->city_det) ? ($guarantor->guarantor_det->city_det) : [];
					$guarantor->communication_state_info = isset($guarantor->guarantor_det->communication_state_det) ? ($guarantor->guarantor_det->communication_state_det) : [];
					$guarantor->communication_district_info = isset($guarantor->guarantor_det->communication_district_det) ? ($guarantor->guarantor_det->communication_district_det) : [];
					$guarantor->communication_city_info = isset($guarantor->guarantor_det->communication_city_det) ? ($guarantor->guarantor_det->communication_city_det) : [];
					$guarantor->relationship_info = isset($guarantor->guarantor_det) ? ($guarantor->guarantor_det->relationship_det) : [];
					$guarantor->lead_management_info = isset($guarantor->guarantor_det->lead_management_det) ? ($guarantor->guarantor_det->lead_management_det) : [];
					$guarantor->dob = ($guarantor->guarantor_det->dob!=0) ? date('d/m/Y',strtotime($guarantor->guarantor_det->dob)) : '';
					$guarantor->doj = ($guarantor->guarantor_det->doj!=0) ? date('d/m/Y',strtotime($guarantor->guarantor_det->doj)) : '';
					$guarantor->retirement = ($guarantor->guarantor_det->retirement!=0) ? date('d/m/Y',strtotime($guarantor->guarantor_det->retirement)) : '';
					$guarantor->photo_for_view = ($guarantor->guarantor_det->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$guarantor->guarantor_det->photo) : '';
					if(isset($guarantor->enrol_det)) {
						if(isset($guarantor->enrol_det->group_det)) {
							$guarantor->enrol_name = "GROUP NAME : ".$guarantor->enrol_det->group_det->group_name.' | TICKET NO : '.$guarantor->enrol_det->ticket_no;
						}
					}
				}
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $guarantors;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function update_guarantor(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {  
                    $guarantors = GuarantorRelation::find($request->id);
                    $customer = Customer::find($guarantors->guarantor_id);    
                    $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $customer->customer_code = isset($request->customer_code) ? ($request->customer_code) : '';
                    $customer->name = isset($request->name) ? ($request->name) : '';
                    $customer->photo = isset($request->photo) ? ($request->photo) : '';
                    if(isset($request->dob)) {
                        $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                        $customer->dob = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->dob = '0000-00-00';
                    }
                    //$customer->dob = isset($request->dob) ? ($request->dob) : '0000-00-00';
                    $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                    $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                    $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                    $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                    $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                    $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                    $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                    $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';                     
                    if(isset($request->doj)) {
                        $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                        $customer->doj = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->doj = '0000-00-00';
                    }
                    $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                    $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                    $customer->door_no = isset($request->door_no) ? ($request->door_no) : '';
                    $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                    $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                    $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                    $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                    $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                    $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                    $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                    $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                    $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '';
                    $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                    $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                    $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                    $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                    $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                    $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                    $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                    $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '';
                    $customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '';
                   /* $customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                    $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                    $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';*/
                    $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '';
                    $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '';
                    if(isset($request->retirement)) {
                        $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                        $customer->retirement = date('Y-m-d',strtotime($s_date));
                    }
                    else {
                        $customer->retirement = '0000-00-00';
                    }
                    //$customer->retirement = isset($request->retirement) ? ($request->retirement) : '0000-00-00';
                    $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                    $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                    $customer->income = isset($request->income) ? ($request->income) : 0;
                    $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                    $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '';
                    $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                    $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                    $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                    $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                    $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                    $customer->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : 0;
                    $customer->is_customer = isset($request->is_customer) ? ($request->is_customer) : '';
                    $customer->is_guarantor = isset($request->is_guarantor) ? ($request->is_guarantor) : '';
                    $customer->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($customer->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_customer_updated');
                        $response['data'] = $customer;
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
	public function delete_guarantor(Request $request) {
        try {
            if(isset($request->id)) {
                $guarantor_rel = GuarantorRelation::find($request->id); 
                $guarantor_rel->deleted_by = isset($request->deleted_by) ? ($request->deleted_by) : 0; 
                $guarantor_rel->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : ''; 

                if($guarantor_rel->save()) {
                    if($guarantor_rel->delete()) {
					$guarantor = Customer::find($guarantor_rel->guarantor_id); 
					$guarantor->deleted_by = isset($request->deleted_by) ? ($request->deleted_by) : 0;
                    $guarantor->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
					$guarantor->delete();  
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_customer_deleted');
                    return response()->json($response,200);
					}                   
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
	
	public function interest_customer_report(Request $request) {
        try {  
            $cond = [];
            $start_date = '';
            $end_date = '';
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->scheme_id)){
				$cond['scheme_id'] = $request->scheme_id;
			}
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}				
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}  
            if(($start_date=='') && ($end_date=='')) {  
                $receipts=InterestMessage::where($cond)->get();
            }
            else if(($start_date=='') && ($end_date!='')) { 
                $receipts=InterestMessage::where($cond)->where('interested_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
            	$receipts=InterestMessage::where($cond)->whereBetween('interested_date',[$start_date,$end_date])->get(); 
            }  
            $i=1;  
            foreach($receipts as $receipt) {
                $receipt->sno = $i;
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
                $receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
                $receipt->scheme_info = isset($receipt->scheme_det) ? ($receipt->scheme_det) : [];
				$receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function get_guarantor_details(Request $request)
    {
        try
        {   // customer_id
            $customers = GuarantorRelation::where('customer_id',$request->customer_id)->where('status',1)->get();            
            $response_data =[];
            foreach($customers as $cust)
            {
                $guarantor_det = [];
                $guarantor_det['id'] = $cust->guarantor_id;
                $guarantor_det['guarantor_name'] = $cust->guarantor_det->name;
                array_push($response_data,$guarantor_det);
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response,401);
        }
    }

    Public function commitment_customer_wise_outstanding(Request $request)
    {
        try{
            $cond = [];
            if($request->branch_id > 0){
                $cond['branch_id'] = $request->branch_id; 
                $enrolls = ChitDetail::where($cond)->where('chit_type','!=',1)->where('customer_id','!=',1)->where('chit_type',3);
            }
            if($request->branch_id == 0){
                $enrolls = ChitDetail::where('chit_type','!=',1)->where('customer_id','!=',1)->where('chit_type',3);
            }
            if (!empty($request->scheme_id)) {
                $enrolls = $enrolls->where('scheme_id', $request->scheme_id);
            }
            if (!empty($request->slab_id)) {
                $enrolls = $enrolls->where('slab_id', $request->slab_id);
            }
            if (!empty($request->area_id)) {
                $enrolls = $enrolls->where('collection_area_id', $request->area_id);
            }
            $enrolls = $enrolls->get();
            $b=1;     
            $tot_chit_value = 0;
            $grand_total_chit_value = 0; 
            $tot_overal_paid = 0;
            $grand_total_overal_paid = 0; 
            $tot_overal_pending = 0;
            $grand_total_overal_pending = 0; 
            $tot_penalty = 0;
            $grand_total_penalty = 0; 
            $tot_bonus = 0;
            $grand_total_bonus = 0; 
            $tot_discount = 0;
            $grand_total_discount = 0; 
            foreach($enrolls as $enroll) {
                $enroll->sno = $b;
                $enroll->branch_name = isset($enroll->branch_det) ? ($enroll->branch_det->branch_name) : [];
                $chit_value = isset($enroll->scheme_det) ? (round($enroll->scheme_det->chit_value)) : [];
                $enroll->chit_value = $chit_value;
                $enroll->customer_name = isset($enroll->customer_det) ? ($enroll->customer_det->name) : [];
                $enroll->customer_mobile = isset($enroll->customer_det) ? ($enroll->customer_det->mobile_no) : [];
                $door_no = isset($enroll->customer_det) ? ($enroll->customer_det->door_no) : [];
                $buildling_name = isset($enroll->customer_det) ? ($enroll->customer_det->buildling_name) : [];
                $address_line_1 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_1) : [];
                $address_line_2 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_2) : [];
                $landmark = isset($enroll->customer_det) ? ($enroll->customer_det->landmark) : [];
                $city_name = City::where('id',$enroll->customer_det->city_id)->pluck('city_name');
                $pincode = isset($enroll->customer_det) ? ($enroll->customer_det->pincode) : [];
                
                $enroll->customer_address = $door_no.', '.$buildling_name.', '.$address_line_1.', '.$address_line_2.', '.$landmark.', '.$city_name.'-'.$pincode;
                $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0; 
                $enroll->slab_info = ($enroll->slab_due_det) ? \App\Models\ChitDetail::formatSlabWithArNo($enroll->slab_due_det->slab_name, $enroll->ar_no ?? null) : ''; 
                $enroll->collection_area_name = ($enroll->collection_area_det) ? ($enroll->collection_area_det->collection_area) : [];
                $enroll->collection_agent_name = ($enroll->collection_agent_det) ? ($enroll->collection_agent_det->first_name.' '.$enroll->collection_agent_det->last_name) : []; 
                $enroll->refered_by_name = ($enroll->employee_agent_intro_det) ? ($enroll->employee_agent_intro_det->first_name.' '.$enroll->employee_agent_intro_det->last_name) : []; 
                $payment_type_id = ($enroll->payment_type) ? ($enroll->payment_type) :0;
                if($payment_type_id == 1)
                {
                    $enroll->payment_type = 'Daily';
                } else if($payment_type_id == 2)
                {
                    $enroll->payment_type = 'Weekly';
                } else if($payment_type_id == 3)
                {
                    $enroll->payment_type = 'Monthly';
                } else
                {
                    $enroll->payment_type = '-';
                }
                //total_installment_amount
                $total_installment_amount = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
                $enroll->overall_installment_amount = $total_installment_amount;

                //paid_amount
                $receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
                $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
                $bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
                $paid_amount = $paid_rec_amount + $bonus_amount;
                $rec_inst_no=0;
                
                //pending installments
                $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                $rec_inst_no = Receipt::where('id','=',$max_rec_id_inst)->pluck('installment_no');
                //pending_amount 
                $pending_amount = $total_installment_amount - $paid_amount;
				if($pending_amount < 0){
					$pending_amount = 0;
				}else {
					$pending_amount =$pending_amount;
				}

                //auction wise details 
                $inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count(); 
                $auc_wise_inst_amts = commitmentAuction::where('enrollment_id','=',$enrollment_id)->get();
                $i=0;
                $install_det = [];                  
                $total_penalty_amounts = 0;
                $total_bonus_amounts = 0;
                $total_pending_days = 0;
                $total_pending_installments =0;
                $auct_id = isset($auc_wise_inst_amts[0]->id) ? ($auc_wise_inst_amts[0]->id) : 0;
                $cr_date = date('Y-m-d');  
                $inst_amt = 0;  
                foreach($auc_wise_inst_amts as $auc_wise_inst_amt) 
                {
                    $cust_det = [];     
                    $penalty = 0;                   
                    $bonus = 0;                 
                    $inst_amt = isset($auc_wise_inst_amt->due_amount) ? ($auc_wise_inst_amt->due_amount) : 0;
                    $auction_id = isset($auc_wise_inst_amt->id) ? ($auc_wise_inst_amt->id) : 0;
                    $auc_id  = isset($auc_wise_inst_amt->id) ? ($auc_wise_inst_amt->id) : 0;
                    $installment_no  = isset($auc_wise_inst_amt->installment_no) ? ($auc_wise_inst_amt->installment_no) : 0;
                    $auc_date_inst  = isset($auc_wise_inst_amt->auction_date) ? ($auc_wise_inst_amt->auction_date) : 0;

                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_id, 'status' => 1])->get();   
                    $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                    $last_receipt_date_inst1 = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                    if(empty($cus_rec_amt[0])) { 
                        $amount_paid = 0;
                    } 
                    else {
                        $amount_paid_rec = isset($cus_rec_amt[0]->total_paid) ? ($cus_rec_amt[0]->total_paid) : 0;
                        $amount_paid_bonus = isset($cus_rec_amt[0]->bonus_amount) ? ($cus_rec_amt[0]->bonus_amount) : 0;
                        $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                    }
                    $ins_amt_wise_rec = $inst_amt - $amount_paid;
                    if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
                        $receipt_date = $last_receipt_date_inst1[0];
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
                        $i++;
                    } 
                    // Bonus days and percentage by heirrachy over all 
                    if($enroll->customer_det->bonus_days!=0 && $enroll->customer_det->customer_bonus!=0)
                    {
                      $bonus_days = $enroll->customer_det->bonus_days;
                      $bonus_percentage = $enroll->customer_det->customer_bonus;
                    }   
                    elseif($enroll->branch_det->bonus_days!=0 && $enroll->branch_det->bonus!=0)
                    {
                      $bonus_days = $enroll->branch_det->bonus_days;
                      $bonus_percentage = $enroll->branch_det->bonus;              
                    }    
                    else
                    {
                      $bonus_days = 0;
                      $bonus_percentage = 0;
                    }           
                    
                    $bid = CommitmentAuction::Where('enrollment_id','=',$enrollment_id)->max('id');
                    $check_auction = CommitmentAuction::where('id',$bid)->get();
                    $max_auction_date = $check_auction[0]->auction_date;
                    // Auction on holiday check for bonus
                    $auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$enroll->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
                    $holiday_count = 0;
                    if(count($auction_on_holiday)>0)
                    {   
                        $holiday_count += $auction_on_holiday->count();
                    }

                    $bonus_days_include_holiday = $bonus_days + $holiday_count;
                    if($pending_days <=  $bonus_days_include_holiday)
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
                    if($enroll->customer_det->penalty_days!=0 )
                    {
                      $inst_penalty_days = $enroll->customer_det->penalty_days;
                      $inst_penalty_percentage = $enroll->customer_det->customer_penalty_interest;
                    }
                    elseif($enroll->branch_det->penalty_days!=0)
                    {
                      $inst_penalty_days = $enroll->branch_det->penalty_days;
                      $inst_penalty_percentage = $enroll->branch_det->branch_wise_penalty;
                    }
                    else
                    {
                      $inst_penalty_days = "0";
                      $inst_penalty_percentage = "0";
                    }

                    $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
                    $pend_days_int = 0;    
                    $cust_det['penalty_amounts'] = 0;   
                    
                    if($pending_days > $inst_penalty_days)
                    {           
                        $branch=Branch::where('id',$enroll->branch_id)->get();
                        if($enroll->prized_status==0)
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
                    // Pending Installment 
                    $rcpt = Receipt::where('enrollment_id','=',$enrollment_id)->get();
                    $auc_pendign_count = 0;
                    $rcpt_pendign_count =0;
                    $recpt_auc_id  = isset($rcpt[0]->auction_id) ? ($rcpt[0]->auction_id) : 0;
                    if($recpt_auc_id != $auc_id)
                    {
                        $auc_pendign_count += 1;
                    }
                    if($amount_paid != $inst_amt)
                    {
                        if($recpt_auc_id == $auc_id)
                        {
                           $rcpt_pendign_count += 1;
                        }                        
                    }
                    $pendign_count = $auc_pendign_count + $rcpt_pendign_count;

                    $cust_det['installment_no'] = $installment_no;
                    $cust_det['inst_amt_wise'] = $inst_amt;
                    $cust_det['amount_paid'] = $amount_paid;
                    $cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
                    $cust_det['pending_days'] = $pending_days;
                    $cust_det['bonus_inst_wise'] = 0;
                    $cust_det['penalty_inst_wise'] = 0;
                    $cust_det['discount_inst_wise'] = 0;
                    $cust_det['discount_penalty_wise'] = 0;
                    $cust_det['auction_id'] = $auction_id;
                    $cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
                    $cust_det['cancel_dividend_amount'] =0;
                    if($ins_amt_wise_rec>0) {
                        array_push($install_det,$cust_det);
                    }                
                    $installment_details  = $install_det;       
                    $total_penalty_amounts += $penalty;
                    $total_bonus_amounts += $bonus;
                    $total_pending_installments +=$pendign_count;
                    $penalty_percentage = $inst_penalty_percentage;
                };
                $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auct_id, 'status' => 1])->get();
                $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
                $last_receipt_date_inst = Receipt::where('id','=',$max_rec_id_inst)->pluck('receipt_date'); 
                if(empty($cus_rec_amt[0])) { 
                    $amount_paid = 0;
                } 
                else {
                    $amount_paid_rec = isset($cus_rec_amt[0]->total_paid) ? ($cus_rec_amt[0]->total_paid) : 0;
                    $amount_paid_bonus = isset($cus_rec_amt[0]->bonus_amount) ? ($cus_rec_amt[0]->bonus_amount) : 0;
                    $amount_paid = $amount_paid_rec + $amount_paid_bonus;
                }
                $auction_date = isset($auc_wise_inst_amts[0]->auction_date) ? ($auc_wise_inst_amts[0]->auction_date) : '0000-00-00';
                $ins_amt_wise_rec1 = $inst_amt - $amount_paid;                
               $pending_days1 =0;
                if($ins_amt_wise_rec1 !=$inst_amt && $inst_amt_no !=0) {
                    $receipt_date = $last_receipt_date_inst[0];
                    $current_date = date('Y-m-d');
                    $datetime1 = new DateTime($receipt_date);
                    $datetime2 = new DateTime($current_date);
                    $interval = $datetime1->diff($datetime2);
                    $pending_days1 = $interval->format('%a');
                    $i++;
                } 
                else {
                    $receipt_date = $auction_date;
                    $current_date = date('Y-m-d');
                    $datetime1 = new DateTime($receipt_date);
                    $datetime2 = new DateTime($current_date);
                    $interval = $datetime1->diff($datetime2);
                    $pending_days1 = $interval->format('%a');
                    $i++;
                }
                $bonus = $total_bonus_amounts;
                $enroll->bonus = $bonus;
                $penalty = round($total_penalty_amounts);
                $enroll->penalty = $penalty;
                $overall_pending_amount = $pending_amount;
                $enroll->overall_pending_amount = $overall_pending_amount;
                $overall_paid_amount = round($paid_rec_amount);
                $enroll->overall_paid_amount = $overall_paid_amount;
                $discount = 0;
                $enroll->discount = $discount;
                $enroll->pending_days = $pending_days1;
                $enroll->pending_inst = $total_pending_installments;
                $b++;  
                $tot_chit_value += $chit_value;  
                $tot_overal_paid += $overall_paid_amount;  
                $tot_overal_pending += $overall_pending_amount;   
                $tot_penalty += $penalty;   
                $tot_bonus += $bonus;
                $tot_discount += $discount;
            }   
            $grand_total_chit_value += $tot_chit_value;
            $grand_total_overal_paid += $tot_overal_paid;
            $grand_total_overal_pending += $tot_overal_pending;
            $grand_total_penalty += $tot_penalty;
            $grand_total_bonus += $tot_bonus;
            $grand_total_discount += $tot_discount;

            $grand_total_chit_value_format =0;
            $grand_total_overal_paid_format =0;
            $grand_total_overal_pending_format =0;
            $grand_total_penalty_format =0;
            $grand_total_bonus_format =0;
            $grand_total_discount_format =0;

            if($grand_total_chit_value > 0)
            {   
                $num = $grand_total_chit_value;
                $grand_total_chit_value_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_overal_paid > 0)
            {   
                $num = $grand_total_overal_paid;
                $grand_total_overal_paid_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_overal_pending > 0)
            {   
                $num = $grand_total_overal_pending;
                $grand_total_overal_pending_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_penalty > 0)
            {   
                $num = $grand_total_penalty;
                $grand_total_penalty_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_bonus > 0)
            {   
                $num = $grand_total_bonus;
                $grand_total_bonus_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_discount > 0)
            {   
                $num = $grand_total_discount;
                $grand_total_discount_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_total_chit_value'] = $grand_total_chit_value_format;
            $response['grand_total_overal_paid'] = $grand_total_overal_paid_format;
            $response['grand_total_overal_pending'] = $grand_total_overal_pending_format;
            $response['grand_total_penalty'] = $grand_total_penalty_format;
            $response['grand_total_bonus'] = $grand_total_bonus_format;
            $response['grand_total_discount'] = $grand_total_discount_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $enrolls;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function moneyFormatIndia($num)
    {       
        $nums = explode(".",$num);
        if(count($nums)>2){
            return "0";
        }
        else{
            if(count($nums)==1){
                $nums[1]="00";
            } 
            $num = $nums[0];
            $explrestunits = "" ;
            if(strlen($num)>3){
                $lastthree = substr($num, strlen($num)-3, strlen($num));
                $restunits = substr($num, 0, strlen($num)-3); 
                $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; 
                $expunit = str_split($restunits, 2);
                for($i=0; $i<sizeof($expunit); $i++){

                    if($i==0)
                    {
                        $explrestunits .= (int)$expunit[$i].","; 
                    }else{
                        $explrestunits .= $expunit[$i].",";
                    }
                }
                $thecash = $explrestunits.$lastthree; 
                return $thecash;
            } else {
                $thecash = $num;
                return $thecash;
            }  
        }
    } 

    public function store_nominee(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else { 
            try { 
                $customer = new Nominee();                
                $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
				$customer->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $customer->name = isset($request->name) ? ($request->name) : '';
                $customer->photo = isset($request->photo) ? ($request->photo) : '';
                if(isset($request->dob)) {
                    $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                    $customer->dob = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $customer->dob = '0000-00-00';
                }
                $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '0';
                $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '0';
                $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '0';
                $customer->license_no = isset($request->license_no) ? ($request->license_no) : '0';
                $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '0';
                if(isset($request->doj)) {
                    $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                    $customer->doj = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    // $customer->doj = '0000-00-00';
                }
                $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                $customer->door_no = isset($request->door_no) ? ($request->door_no) : '0';
                $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                $customer->pincode = isset($request->pincode) ? ($request->pincode) : '0';
                $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '0';
                $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '0';
                $customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '0';
                /*$customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';*/
                $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '0';
                $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '0';
                if(isset($request->retirement)) {
                    $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                    $customer->retirement = date('Y-m-d',strtotime($s_date));
                }
                else {
                    // $customer->retirement = '0000-00-00';
                }
                $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                $customer->income = isset($request->income) ? ($request->income) : 0;
                $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '0';
                $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                $customer->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $customer->status = isset($request->status) ? ($request->status) : 1;
                // print_r($customer);exit;
                if($customer->save()) {
                    
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_nominee_added');
                    $response['data'] = $customer;
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
	
	public function get_nominees(Request $request) {
        try {
            $cond = $request->conditions; 
            $is_for_edit = isset($request->for_edit) ? ($request->for_edit) : 0; 
            $customers = Nominee::where($cond)->orderBy('id','desc')->get();
            if($is_for_edit) {
                $customers = Nominee::where($cond)->whereNotIn('id',[$is_for_edit])->orderBy('id','desc')->get();
            } 
            $i=1;
            foreach($customers as $customer) {
                $customer->sno = $i;
                $customer->full_name = $customer->name;
                $customer->formatted_name = $customer->name.' || '.$customer->customer_code;
                $customer->tenant_info = isset($customer->tenant_det) ? ($customer->tenant_det) : [];
                $customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
                $customer->scheme_format = isset($customer->scheme_det) ? $customer->scheme_det->chit_value.'('.$customer->scheme_det->no_of_months.' M)' : [];
                $customer->branch_name = isset($customer->branch_det) ? ($customer->branch_det->branch_name) : '';
				$customer->customer_name = isset($customer->customer_det) ? ($customer->customer_det->name) : '';
                $customer->state_info = ($customer->state_det) ? ($customer->state_det) : [];
                $customer->district_info = ($customer->district_det) ? ($customer->district_det) : [];
                $customer->city_info = ($customer->city_det) ? ($customer->city_det) : [];
                $customer->communication_state_info = ($customer->communication_state_det) ? ($customer->communication_state_det) : [];
				$customer->lead_management_info = isset($customer->lead_management_det) ? ($customer->lead_management_det) : [];
                $customer->communication_district_info = ($customer->communication_district_det) ? ($customer->communication_district_det) : [];
                $customer->communication_city_info = ($customer->communication_city_det) ? ($customer->communication_city_det) : [];
                $customer->relationship_info = ($customer->relationship_det) ? ($customer->relationship_det) : [];
                $customer->customer_belongs_to_info = ($customer->customer_belongs_to_det) ? ($customer->customer_belongs_to_det) : [];
                $customer->dob = ($customer->dob!=0) ? date('d/m/Y',strtotime($customer->dob)) : '';                  
                $customer->doj = ($customer->doj!=0) ? date('d/m/Y',strtotime($customer->doj)) : '';    
				$customer->districtname = ($customer->district_det) ? ($customer->district_det->district_name) : [];
                $customer->cityname = ($customer->city_det) ? ($customer->city_det->city_name) : [];
				if($customer->door_no == 0){
				$door_no = '';	
				} else {
				$door_no = $customer->door_no;
				}
				$customer->address_line_1 = $door_no . ' ' . $customer->buildling_name . ' ' . $customer->address_line_1 . ' ' . $customer->address_line_2 . ' ' . $customer->cityname . ' , ' . $customer->districtname;

			    $customer->retirement = ($customer->retirement!=0) ? date('d/m/Y',strtotime($customer->retirement)) : '';                  
			    $customer->photo_for_view = ($customer->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';

                $customer->introducer_full_name = '';
                $customer->introducer_info = [];
                if($customer->reference_type==1) { 
                    //customer
                    $introducer_det = Customer::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $customer->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($customer->reference_type==2) || ($customer->reference_type==3)) {
                    //employee
                    $introducer_det = Employee::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $customer->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $customer->introducer_info = isset($customer->introducer_id) ? ($customer->introducer_id) : [];
                   $customer->introducer_full_name = isset($customer->introducer_name) ? ($customer->introducer_name) : []; 
                }
                $i++;
            }
			$issue_date = date('Y-m-d') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
			$response['date'] = $issue_date;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_nominee_details(Request $request) {
        try {
            $cond = $request->customer_id; 
			
			$customers = Nominee::where('customer_id',$request->customer_id)->where('status',1)->get();   
            $i=1;
            foreach($customers as $customer) {
                $customer->sno = $i;
                $customer->full_name = $customer->name;
                $customer->formatted_name = $customer->name.' || '.$customer->customer_code;
                $customer->tenant_info = isset($customer->tenant_det) ? ($customer->tenant_det) : [];
                $customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
                $customer->scheme_format = isset($customer->scheme_det) ? $customer->scheme_det->chit_value.'('.$customer->scheme_det->no_of_months.' M)' : [];
                $customer->branch_name = isset($customer->branch_det) ? ($customer->branch_det->branch_name) : '';
				$customer->customer_name = isset($customer->customer_det) ? ($customer->customer_det->name) : '';
                $customer->state_info = ($customer->state_det) ? ($customer->state_det) : [];
                $customer->district_info = ($customer->district_det) ? ($customer->district_det) : [];
                $customer->city_info = ($customer->city_det) ? ($customer->city_det) : [];
                $customer->communication_state_info = ($customer->communication_state_det) ? ($customer->communication_state_det) : [];
				$customer->lead_management_info = isset($customer->lead_management_det) ? ($customer->lead_management_det) : [];
                $customer->communication_district_info = ($customer->communication_district_det) ? ($customer->communication_district_det) : [];
                $customer->communication_city_info = ($customer->communication_city_det) ? ($customer->communication_city_det) : [];
                $customer->relationship_info = ($customer->relationship_det) ? ($customer->relationship_det) : [];
                $customer->customer_belongs_to_info = ($customer->customer_belongs_to_det) ? ($customer->customer_belongs_to_det) : [];
                $customer->dob = ($customer->dob!=0) ? date('d/m/Y',strtotime($customer->dob)) : '';                  
                $customer->doj = ($customer->doj!=0) ? date('d/m/Y',strtotime($customer->doj)) : '';    

			    $customer->retirement = ($customer->retirement!=0) ? date('d/m/Y',strtotime($customer->retirement)) : '';                  
			    $customer->photo_for_view = ($customer->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';

                $customer->introducer_full_name = '';
                $customer->introducer_info = [];
                if($customer->reference_type==1) { 
                    //customer
                    $introducer_det = Customer::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $customer->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($customer->reference_type==2) || ($customer->reference_type==3)) {
                    //employee
                    $introducer_det = Employee::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $customer->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $customer->introducer_info = isset($customer->introducer_id) ? ($customer->introducer_id) : [];
                   $customer->introducer_full_name = isset($customer->introducer_name) ? ($customer->introducer_name) : []; 
                }
                $i++;
            }
			$issue_date = date('Y-m-d') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
			$response['date'] = $issue_date;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function update_nominee(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'state_id' => 'required',
            'district_id' => 'required',
            'city_id' => 'required',
            'communication_state_id' => 'required',
            'communication_district_id' => 'required',
            'communication_city_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {  
                    $customer = Nominee::find($request->id);    
                    $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $customer->customer_code = isset($request->customer_code) ? ($request->customer_code) : '';
                    $customer->name = isset($request->name) ? ($request->name) : '';
                    $customer->photo = isset($request->photo) ? ($request->photo) : '';
                    if(isset($request->dob)) {
                        $s_date = ($request->dob['year']).'-'.sprintf('%02d',($request->dob['month'])).'-'.sprintf('%02d',($request->dob['day'])); 
                        $customer->dob = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->dob = '0000-00-00';
                    }
                    //$customer->dob = isset($request->dob) ? ($request->dob) : '0000-00-00';
                    $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                    $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '';
                    $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                    $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                    $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                    $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                    $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                    $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';                     
                    if(isset($request->doj)) {
                        $s_date = ($request->doj['year']).'-'.sprintf('%02d',($request->doj['month'])).'-'.sprintf('%02d',($request->doj['day'])); 
                        $customer->doj = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $customer->doj = '0000-00-00';
                    }
                    $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                    $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                    $customer->door_no = isset($request->door_no) ? ($request->door_no) : '';
                    $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                    $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                    $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                    $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                    $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                    $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                    $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                    $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                    $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : '';
                    $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : '';
                    $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : '';
                    $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : '';
                    $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : '';
                    $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : 0;
                    $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : 0;
                    $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : 0;
                    $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : '';
                    $customer->refered_by = isset($request->refered_by) ? ($request->refered_by) : '';
                   /* $customer->reference_type = isset($request->reference_type) ? ($request->reference_type) : '';
                    $customer->introducer_name = isset($request->introducer_name) ? ($request->introducer_name) : '';
                    $customer->introducer_id = isset($request->introducer_id) ? ($request->introducer_id) : '';*/
                    $customer->pf_no = isset($request->pf_no) ? ($request->pf_no) : '';
                    $customer->esi_no = isset($request->esi_no) ? ($request->esi_no) : '';
                    if(isset($request->retirement)) {
                        $s_date = ($request->retirement['year']).'-'.sprintf('%02d',($request->retirement['month'])).'-'.sprintf('%02d',($request->retirement['day'])); 
                        $customer->retirement = date('Y-m-d',strtotime($s_date));
                    }
                    else {
                        $customer->retirement = '0000-00-00';
                    }
                    //$customer->retirement = isset($request->retirement) ? ($request->retirement) : '0000-00-00';
                    $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                    $customer->source_of_funds = isset($request->source_of_funds) ? ($request->source_of_funds) : 0;
                    $customer->income = isset($request->income) ? ($request->income) : 0;
                    $customer->relationship_id = isset($request->relationship_id) ? ($request->relationship_id) : 0;
                    $customer->customer_belongs_to=isset($request->customer_belongs_to) ? ($request->customer_belongs_to) : '';
                    $customer->customer_bonus = isset($request->customer_bonus) ? ($request->customer_bonus) : 0;
                    $customer->customer_penalty_interest = isset($request->customer_penalty_interest) ? ($request->customer_penalty_interest) : 0;
                    $customer->customer_interest=isset($request->customer_interest) ? ($request->customer_interest) : 0;
                    $customer->customer_divident = isset($request->customer_divident) ? ($request->customer_divident) : 0;
                    $customer->sms = isset($request->sms) ? ($request->sms) : 0;
                    $customer->lead_management_id = isset($request->lead_management_id) ? ($request->lead_management_id) : 0;
                    $customer->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($customer->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_nominee_updated');
                        $response['data'] = $customer;
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
	
	public function delete_nominee(Request $request) {
        try {
            if(isset($request->id)) {
                $guarantor_rel = Nominee::find($request->id); 
                $guarantor_rel->deleted_by = isset($request->deleted_by) ? ($request->deleted_by) : 0; 
                $guarantor_rel->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : ''; 

                if($guarantor_rel->save()) {
                    $guarantor_rel->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_customer_deleted');
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
    
    public function get_unenrollment_report(Request $request) {
        try {
            $tenant_id = $request->tenant_id; 
            $branch_id = $request->branch_id;
	    $scheme_id = $request->scheme_id; 
           if($scheme_id > 0){
		$customers = Customer::where('scheme_id',$request->scheme_id)->where('is_enrolled','=',Null)->where('is_customer','!=',0)->where('status','=',1)->orderBy('id','desc')->get();
		}
	   else if($branch_id > 0){
            $customers = Customer::where('branch_id',$request->branch_id)->where('is_enrolled','=',Null)->where('is_customer','!=',0)->where('status','=',1)->orderBy('id','desc')->get();
          
        }
        else{
            $customers = Customer::where('tenant_id',$request->tenant_id)->where('is_enrolled','=',Null)->where('is_customer','!=',0)->where('status','=',1)->orderBy('id','desc')->get();
        }
            $i=1;
$grandchitstotal=0;
            foreach($customers as $customer) {
                $customer->sno = $i;
				$cust_id = $customer->id;
				$receipts = BeforeEnrolReceipt::selectRaw('*,sum(amount)as total_received')->where(array('customer_id'=>$customer->id))->get();
				
                $amt = $receipts[0]->total_received;
				$customer->amt = $amt;
				$customer->total = $amt;
                $customer->subscriber_name = $customer->name;
		        $customer->subscriber_mobile_no = $customer->mobile_no;
                $customer->formatted_name = $customer->name.' || '.$customer->customer_code;
                $customer->tenant_info = isset($customer->tenant_det) ? ($customer->tenant_det) : [];
                $customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det) : [];
                $customer->chit_value = isset($customer->scheme_det) ? $customer->scheme_det->chit_value.'('.$customer->scheme_det->no_of_months.' M)' : [];
	            $customer->chit_value_tot = isset($customer->scheme_det) ? ($customer->scheme_det->chit_value) : [];
                
                $customer->branch_name = isset($customer->branch_det) ? ($customer->branch_det->branch_name) : '';
                $customer->state_info = ($customer->state_det) ? ($customer->state_det) : [];
                $customer->district_info = ($customer->district_det) ? ($customer->district_det) : [];
                $customer->city_info = ($customer->city_det) ? ($customer->city_det) : [];
                $customer->communication_state_info = ($customer->communication_state_det) ? ($customer->communication_state_det) : [];
				$customer->lead_management_info = isset($customer->lead_management_det) ? ($customer->lead_management_det) : [];
                $customer->communication_district_info = ($customer->communication_district_det) ? ($customer->communication_district_det) : [];
                $customer->communication_city_info = ($customer->communication_city_det) ? ($customer->communication_city_det) : [];
                $customer->relationship_info = ($customer->relationship_det) ? ($customer->relationship_det) : [];
                $customer->customer_belongs_to_info = ($customer->customer_belongs_to_det) ? ($customer->customer_belongs_to_det) : [];
                $customer->dob = ($customer->dob!=0) ? date('d/m/Y',strtotime($customer->dob)) : '';                  
                $customer->date_introduced = ($customer->doj!=0) ? date('d/m/Y',strtotime($customer->doj)) : '';    

			    $customer->retirement = ($customer->retirement!=0) ? date('d/m/Y',strtotime($customer->retirement)) : '';                  
			    $customer->photo_for_view = ($customer->photo!='') ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';

                $customer->introducer_full_name = '';
                $customer->introducer_info = [];
                if($customer->reference_type==1) { 
                    //customer
                    $introducer_det = Customer::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : [];
                    $customer->introducer_full_name = isset($introducer_det->name) ? ($introducer_det->name) : [];
                }
                else if(($customer->reference_type==2) || ($customer->reference_type==3)) {
                    //employee
                    $introducer_det = Employee::find($customer->introducer_id);
                    $customer->introducer_info = isset($introducer_det) ? $introducer_det : []; 
                    $customer->introducer_full_name = isset($introducer_det) ? $introducer_det->first_name.' '.$introducer_det->last_name : [];
                } 
                else
                {
                   $customer->introducer_info = isset($customer->introducer_id) ? ($customer->introducer_id) : [];
                   $customer->introducer_full_name = isset($customer->introducer_name) ? ($customer->introducer_name) : []; 
                }
                $i++;

	$grandchitstotal +=$customer->chit_value_tot;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
	    $response['grand_chit_value'] = $grandchitstotal;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_customers_full(Request $request) {
        try {
            
            $customers = DB::select('call Customers');
           
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_all_customers(Request $request) {
        try {

            // DB::purge('mysql');
            // Config::set('database.connections.mysql.database', 'vvrs_chits');
            // DB::reconnect('mysql');
            $i=1;
            if($request->tenant_id==11){
                $customers = DB::table('customers')->select(DB::raw('CONCAT(name, "-", address_line_1,"-",address_line_2) AS name'),'id','mobile_no')->where('is_customer','=',1)->where('status','=',1)->get();
            }
            elseif($request->tenant_id==13){
                $customers = DB::table('customers')->select(DB::raw('CONCAT(name, " / ", customer_code) AS name'),'id','mobile_no')->where('status','=',1)->where('is_customer','=',1)->get();
            }elseif($request->tenant_id==30){
                $customers = DB::table('customers')->select(DB::raw('CONCAT(name, " / ", customer_code) AS name'),'id','mobile_no','last_name')->where('status','=',1)->where('is_customer','=',1)->get();
            }else{
                $customers = DB::table('customers')->select('name','id','mobile_no','last_name')->where('is_customer','=',1)->where('status','=',1)->get();
				foreach($customers as $customer){
					$customer->name = $customer->name . ' ' . $customer->last_name . ' || ' . $customer->mobile_no;
                    $customer->sno = $i;  
                    $i++;
				}
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statusss'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

        public function store_family_code(Request $request) {
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
                $code = trim((string) $request->family_code);
                if ($code === '') {
                    return response()->json(['status' => 'Error', 'msg' => 'Family code is required'], 200);
                }
                if (FamilyCode::where('tenant_id', $request->tenant_id)->where('family_code', $code)->exists()) {
                    return response()->json(['status' => 'Error', 'msg' => 'Family code "' . $code . '" already exists'], 200);
                }
                $request->merge(['family_code' => $code]);

                $sms = new FamilyCode();                
                $sms->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $sms->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $sms->family_code = isset($request->family_code) ? ($request->family_code) : '';
                $sms->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $sms->status = isset($request->status) ? ($request->status) : 1; 
                if($sms->save()) {
                     $response['status'] = 'Success';
					 $response['msg'] = \Lang::get('api.success_family_code_added');
					 $response['data'] = $sms;
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
	
	public function update_family_code(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'branch_id'=>'required',
		   'tenant_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $holiday = FamilyCode::find($request->id); 
                    if (!$holiday) {
                        return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.record_not_identified')], 200);
                    }
                    $code = trim((string) $request->family_code);
                    if ($code === '') {
                        return response()->json(['status' => 'Error', 'msg' => 'Family code is required'], 200);
                    }
                    if (FamilyCode::where('tenant_id', $request->tenant_id)->where('family_code', $code)->where('id', '!=', $holiday->id)->exists()) {
                        return response()->json(['status' => 'Error', 'msg' => 'Family code "' . $code . '" already exists'], 200);
                    }
                    $holiday->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$holiday->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $holiday->family_code = $code;
                    $holiday->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($holiday->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_family_code_updated');
                        $response['data'] =$holiday;
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
	
	public function get_family_codes(Request $request) {
        try {
			$cond = $request->conditions; 
            $smss = FamilyCode::where($cond)->where('status','=',1)->orderBy('id','desc')->get();
            
            $i=1;
            foreach($smss as $sms) {
                $sms->sno = $i;
				$sms->branch_info = isset($sms->branch_det) ? ($sms->branch_det) : [];
                $sms->family_code = $sms->family_code;
               
                $i++;
            }
			$issue_date = date('Y-m-d') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $smss;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_family_codes(Request $request) {
        try {
			
            $q = FamilyCode::where('status','=',1);
            if (!empty($request->tenant_id)) {
                $q->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id)) {
                $q->where('branch_id', $request->branch_id);
            }
            $smss = $q->orderBy('id','desc')->get();
            $members = Customer::whereIn('family_code', $smss->pluck('id'))
                ->groupBy('family_code')->select('family_code', DB::raw('COUNT(*) as cnt'))->pluck('cnt', 'family_code');

            $i=1;
            foreach($smss as $sms) {
                $sms->sno = $i;
				$sms->branch_name = isset($sms->branch_det) ? ($sms->branch_det->branch_name) : '';
                $sms->member_count = (int) ($members[$sms->id] ?? 0);
                $i++;
            }
			$issue_date = date('Y-m-d') ;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $smss;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_family_code(Request $request) {
        try {
            if(isset($request->id)) {
                      
          $sms = FamilyCode::find($request->id); 
                $sms->deleted_by = $request->deleted_by;
                if($sms->save()) { 
                    $sms->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_family_code_deleted');
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
    
    public function mobile_store_customer(Request $request) {

        DB::purge('mysql');
      Config::set('database.connections.mysql.database', $request->db);
      DB::reconnect('mysql');

        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required'
            
           
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else { 
            try { 
                $customer = new Customer();                
                $customer->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $customer->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $customer->scheme_id = isset($request->scheme_id) ? ($request->scheme_id) : 0;
    
                $customer->name = isset($request->name) ? ($request->name) : '';
                $customer->last_name = isset($request->last_name) ? ($request->last_name) : '';
                $customer->photo = isset($request->photo) ? ($request->photo) : '';
                $customer->dob = isset($request->dob) ? $request->dob : '0000-00-00';
                $customer->gender = isset($request->gender) ? ($request->gender) : 0;
                $customer->mobile_no = isset($request->mobile_no) ? ($request->mobile_no) : '0';
                $customer->mobile_no_1 = isset($request->mobile_no_1) ? ($request->mobile_no_1) : '';
                $customer->phone_no = isset($request->phone_no) ? ($request->phone_no) : '';
                $customer->email_id = isset($request->email_id) ? ($request->email_id) : '';
                $customer->adhar_no = isset($request->adhar_no) ? ($request->adhar_no) : '';
                $customer->pan_no = isset($request->pan_no) ? ($request->pan_no) : '';
                $customer->license_no = isset($request->license_no) ? ($request->license_no) : '';
                $customer->ration_card_no = isset($request->ration_card_no) ? ($request->ration_card_no) : '';
                $customer->is_enrolled = '0';
                $customer->doj = isset($request->doj) ? ($request->doj) : date('Y-m-d');
                $customer->father_name = isset($request->father_name) ? ($request->father_name) : '';
                $customer->spouse_name = isset($request->spouse_name) ? ($request->spouse_name) : '';
                $customer->door_no = isset($request->door_no) ? ($request->door_no) : '';
                $customer->buildling_name = isset($request->buildling_name) ? ($request->buildling_name) : '';
                $customer->address_line_1 = isset($request->address_line_1) ? ($request->address_line_1) : '';
                $customer->address_line_2 = isset($request->address_line_2) ? ($request->address_line_2) : '';
                $customer->landmark = isset($request->landmark) ? ($request->landmark) : '';
                $customer->state_id = isset($request->state_id) ? ($request->state_id) : 0;
                $customer->district_id = isset($request->district_id) ? ($request->district_id) : 0;
                $customer->city_id = isset($request->city_id) ? ($request->city_id) : 0;
                $customer->pincode = isset($request->pincode) ? ($request->pincode) : '';
                $customer->communication_door_no = isset($request->communication_door_no) ? ($request->communication_door_no) : ($request->door_no ?? '');
                $customer->communication_building_name = isset($request->communication_building_name) ? ($request->communication_building_name) : ($request->buildling_name ?? '');
                $customer->communication_address_line_1 = isset($request->communication_address_line_1) ? ($request->communication_address_line_1) : ($request->address_line_1 ?? '');
                $customer->communication_address_line_2 = isset($request->communication_address_line_2) ? ($request->communication_address_line_2) : ($request->address_line_2 ?? '');
                $customer->communication_land_mark = isset($request->communication_land_mark) ? ($request->communication_land_mark) : ($request->landmark ?? '');
                $customer->communication_state_id = isset($request->communication_state_id) ? ($request->communication_state_id) : ($request->state_id ?? 0);
                $customer->communication_district_id = isset($request->communication_district_id) ? ($request->communication_district_id) : ($request->district_id ?? 0);
                $customer->communication_city_id = isset($request->communication_city_id) ? ($request->communication_city_id) : ($request->city_id ?? 0);
                $customer->communication_pincode = isset($request->communication_pincode) ? ($request->communication_pincode) : ($request->pincode ?? '');
                $customer->permanent_door_no = isset($request->permanent_door_no) ? ($request->permanent_door_no) : ($request->door_no ?? '');
                $customer->permanent_building_name = isset($request->permanent_building_name) ? ($request->permanent_building_name) : ($request->buildling_name ?? '');
                $customer->permanent_address_line_1 = isset($request->permanent_address_line_1) ? ($request->permanent_address_line_1) : ($request->address_line_1 ?? '');
                $customer->permanent_address_line_2 = isset($request->permanent_address_line_2) ? ($request->permanent_address_line_2) : ($request->address_line_2 ?? '');
                $customer->permanent_land_mark = isset($request->permanent_land_mark) ? ($request->permanent_land_mark) : ($request->landmark ?? '');
                $customer->permanent_state_id = isset($request->permanent_state_id) ? ($request->permanent_state_id) : ($request->state_id ?? 0);
                $customer->permanent_district_id = isset($request->permanent_district_id) ? ($request->permanent_district_id) : ($request->district_id ?? 0);
                $customer->permanent_city_id = isset($request->permanent_city_id) ? ($request->permanent_city_id) : ($request->city_id ?? 0);
                $customer->permanent_pincode = isset($request->permanent_pincode) ? ($request->permanent_pincode) : ($request->pincode ?? '');
                $customer->occupation = isset($request->occupation) ? ($request->occupation) : 0;
                $customer->income = isset($request->income) ? ($request->income) : 0;
                $customer->gst_no = isset($request->gst_no) ? ($request->gst_no) : '';
                $customer->is_customer = '1';
                $customer->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $customer->status = isset($request->status) ? ($request->status) : 1;
                $customer->reference_type = 2;
                $customer->introducer_id = isset($request->agent_id) ? ($request->agent_id) : (isset($request->introducer_id) ? $request->introducer_id : '0');



                if($customer->save()) {
                    if(isset($request->lead_management_id)) {
                        $lead = LeadManagement::find($request->lead_management_id);
                        $lead->is_customer = 1;
                        $lead->save();
                    }
                    $customer_code = $this->generateBranchCustomerCode(
                        $customer->branch_id,
                        $customer->tenant_id
                    );
                    $customer->customer_code = $customer_code;
                    if($customer->save())
                    {
                        $this->mobileSaveCustomerDocuments($request, $customer->id);

                        $update_customer = new UpdateCustomer();                
                        $update_customer->tenant_id = isset($customer->tenant_id) ? ($customer->tenant_id) : 0;
                        $update_customer->branch_id = isset($customer->branch_id) ? ($customer->branch_id) : 0;
                        // $update_customer->scheme_id = isset($customer->scheme_id) ? ($customer->scheme_id) : 0;
                        $update_customer->name = isset($customer->name) ? ($customer->name) : '';
                        $update_customer->photo = isset($customer->photo) ? ($customer->photo) : '';
                        $update_customer->customer_code = isset($customer->customer_code) ? ($customer->customer_code) : '0'; 
                        $update_customer->edit_date = date('Y-m-d');
                        $update_customer->update_customer_id = isset($customer->id) ? ($customer->id) : '0';
                        $update_customer->dob = isset($customer->dob) ? ($customer->dob) : '';
                        $update_customer->gender = isset($customer->gender) ? ($customer->gender) : 0;
                        $update_customer->mobile_no = isset($customer->mobile_no) ? ($customer->mobile_no) : '0';
                        $update_customer->phone_no = isset($customer->phone_no) ? ($customer->phone_no) : '0';
                        $update_customer->email_id = isset($customer->email_id) ? ($customer->email_id) : '0';
                        $update_customer->adhar_no = isset($customer->adhar_no) ? ($customer->adhar_no) : '0';
                        $update_customer->pan_no = isset($customer->pan_no) ? ($customer->pan_no) : '0';
                        $update_customer->license_no = isset($customer->license_no) ? ($customer->license_no) : '0';
                        $update_customer->ration_card_no = isset($customer->ration_card_no) ? ($customer->ration_card_no) : '0';
                        $update_customer->doj = isset($customer->doj) ? ($customer->doj) : '';
                        $update_customer->father_name = isset($customer->father_name) ? ($customer->father_name) : '';
                        $update_customer->spouse_name = isset($customer->spouse_name) ? ($customer->spouse_name) : '';
                        $update_customer->door_no = isset($customer->door_no) ? ($customer->door_no) : '0';
                        $update_customer->buildling_name = isset($customer->buildling_name) ? ($customer->buildling_name) : '0';
                        $update_customer->address_line_1 = isset($customer->address_line_1) ? ($customer->address_line_1) : '0';
                        $update_customer->address_line_2 = isset($customer->address_line_2) ? ($customer->address_line_2) : '0';
                        $update_customer->landmark = isset($customer->landmark) ? ($customer->landmark) : '0';
                        $update_customer->state_id = isset($customer->state_id) ? ($customer->state_id) : 0;
                        $update_customer->district_id = isset($customer->district_id) ? ($customer->district_id) : 0;
                        $update_customer->city_id = isset($customer->city_id) ? ($customer->city_id) : 0;
                        $update_customer->pincode = isset($customer->pincode) ? ($customer->pincode) : '0';
                        $update_customer->communication_door_no = isset($customer->communication_door_no) ? ($customer->communication_door_no) : '0';
                        $update_customer->communication_building_name = isset($customer->communication_building_name) ? ($customer->communication_building_name) : '0';
                        $update_customer->communication_address_line_1 = isset($customer->communication_address_line_1) ? ($customer->communication_address_line_1) : '0';
                        $update_customer->communication_address_line_2 = isset($customer->communication_address_line_2) ? ($customer->communication_address_line_2) : '0';
                        $update_customer->communication_land_mark = isset($customer->communication_land_mark) ? ($customer->communication_land_mark) : '';
                        $update_customer->communication_state_id = isset($customer->communication_state_id) ? ($customer->communication_state_id) : 0;
                        $update_customer->communication_district_id = isset($customer->communication_district_id) ? ($customer->communication_district_id) : 0;
                        $update_customer->communication_city_id = isset($customer->communication_city_id) ? ($customer->communication_city_id) : 0;
                        $update_customer->communication_pincode = isset($customer->communication_pincode) ? ($customer->communication_pincode) : '0';
                        //$update_customer->refered_by = isset($customer->refered_by) ? ($customer->refered_by) : '';
                        $update_customer->reference_type = 3;

                        $update_customer->introducer_id = isset($customer->introducer_id) ? ($customer->introducer_id) : '0';
                        //$update_customer->reference_type = isset($customer->reference_type) ? ($customer->reference_type) : '';
                        $update_customer->introducer_name = isset($customer->introducer_name) ? ($customer->introducer_name) : '0';
                        //$update_customer->introducer_id = isset($customer->introducer_id) ? ($customer->introducer_id) : '';
                        $update_customer->pf_no = isset($customer->pf_no) ? ($customer->pf_no) : '0';
                        $update_customer->esi_no = isset($customer->esi_no) ? ($customer->esi_no) : '0';
                        $update_customer->retirement = isset($customer->retirement) ? ($customer->retirement) : '';
                        $update_customer->occupation = isset($customer->occupation) ? ($customer->occupation) : 0;
                        $update_customer->source_of_funds = isset($customer->source_of_funds) ? ($customer->source_of_funds) : 0;
                        $update_customer->income = isset($customer->income) ? ($customer->income) : 0;
                        $update_customer->relationship_id = isset($customer->relationship_id) ? ($customer->relationship_id) : 0;
                        $update_customer->customer_belongs_to=isset($customer->customer_belongs_to) ? ($customer->customer_belongs_to) : '0';
                        $update_customer->customer_bonus = isset($customer->customer_bonus) ? ($customer->customer_bonus) : 0;
                        $update_customer->customer_penalty_interest = isset($customer->customer_penalty_interest) ? ($customer->customer_penalty_interest) : 0;
                        $update_customer->customer_interest=isset($customer->customer_interest) ? ($customer->customer_interest) : 0;
                        $update_customer->customer_divident = isset($customer->customer_divident) ? ($customer->customer_divident) : 0;
                        $update_customer->sms = isset($customer->sms) ? ($customer->sms) : 0;
                        $update_customer->lead_management_id = isset($customer->lead_management_id) ? ($customer->lead_management_id) : 0;
                        $update_customer->is_customer = isset($customer->is_customer) ? ($customer->is_customer) : '0';
                        $update_customer->is_guarantor = isset($customer->is_guarantor) ? ($customer->is_guarantor) : '0';
                        $update_customer->bonus_days = isset($customer->bonus_days) ? ($customer->bonus_days) : 0;
                        $update_customer->penalty_days = isset($customer->penalty_days) ? ($customer->penalty_days) : 0;
                        $update_customer->created_by = isset($customer->created_by) ? ($customer->created_by) : 0;
                        $update_customer->status = isset($customer->status) ? ($customer->status) : 1; 
                        if($update_customer->save())
                        {
                         
                            $response['status'] = 'Success';
                            $response['msg'] = \Lang::get('api.success_customer_added');
                            $response['customer_code'] = $customer->customer_code;
                            $response['data'] = $customer;
                            return response()->json($response,200);
                        }
                        else {
                            $response['status']="Error";
                           // $response['msg']=\Lang::get('api.global_error');
                            return response()->json($response,401);
                        } 
                    }
                }
            }
            catch(\Exception $e) {

    return response()->json([
        'status' => 'Error',
        'file'   => $e->getFile(),
        'line'   => $e->getLine(),
        'msg'    => $e->getMessage()
    ], 500);
}
        }
        
    }
	
	 public function get_customers_view(Request $request) {
        try {
            $cond = $request->conditions; 
            $customerQuery = DB::table('customers')->select('name','id','mobile_no','branch_id','scheme_id','customer_code','last_name','address_line_1','address_line_2','city_id','district_id','pincode','photo','status','created_at')->where('is_customer','=',1)->where($cond);
            if (empty($request->include_inactive)) {
                $customerQuery->where('status','=',1);
            }
            $customers = $customerQuery->orderBy('name','asc')->get();
            $i=1;
            foreach($customers as $customer) {
                $customer->sno = $i;
                $customer->full_name = $customer->name . ' ' . $customer->last_name .' || '. $customer->customer_code;
				$customer->name = $customer->name . ' ' . $customer->last_name;
                $customer->mobile_no = $customer->mobile_no;
                $customer->id = $customer->id;
                $customer->photo_url = (!empty($customer->photo)) ? env('APP_URL').Storage::url('app/uploads/customer_photos/'.$customer->photo) : '';
                $branch_name = Branch::where('id','=',$customer->branch_id)->pluck('branch_name');
                $customer->branch_name = $branch_name[0];
				$customer->address_line_1 = $customer->address_line_1;
				$customer->address_line_2 = $customer->address_line_2;
                $customer->is_delete = '0';
				$customercount=ChitDetail::where('customer_id',$customer->id)->count();
				// $cityname = City::where('id','=',$customer->city_id)->pluck('city_name');
				// $customer->cityname = $cityname[0];
                if($customercount>0){
                $customer->is_delete = '1';
                }
                
                //$customer->branch_info = isset($customer->branch_det) ? ($customer->branch_det->branch_name) : [];
                //$customer->scheme_format = isset($customer->scheme_det) ? $customer->scheme_det->chit_value.'('.$customer->scheme_det->no_of_months.' M)' : [];
				
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customers;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuss'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    /**
     * Dashboard / header typeahead: search customers by name, mobile, code, or AR No.
     */
    public function search_customers_history(Request $request)
    {
        try {
            $q = trim((string) ($request->q ?? $request->search ?? ''));
            if (mb_strlen($q) < 2) {
                return response()->json(['status' => 'Success', 'msg' => '', 'data' => []], 200);
            }

            $tenantId = 0;
            if (isset($request->conditions['tenant_id'])) {
                $tenantId = (int) $request->conditions['tenant_id'];
            } elseif (!empty($request->tenant_id)) {
                $tenantId = (int) $request->tenant_id;
            }

            $branchId = !empty($request->branch_id) ? (int) $request->branch_id : 0;

            $scopeService = app(\App\Services\UserLoginScopeService::class);
            $allowedBranches = $scopeService->resolveAllowedBranchIdsFromRequest($request);
            if ($branchId > 0) {
                if ($allowedBranches !== null && !in_array($branchId, $allowedBranches, true)) {
                    return response()->json(['status' => 'Success', 'msg' => '', 'data' => []], 200);
                }
                $branchIds = [$branchId];
            } elseif ($allowedBranches !== null) {
                $branchIds = $allowedBranches;
                if (empty($branchIds)) {
                    return response()->json(['status' => 'Success', 'msg' => '', 'data' => []], 200);
                }
            } else {
                $branchIds = null;
            }

            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

            $customerQuery = Customer::query()
                ->where('status', 1)
                ->where('is_customer', 1)
                ->where(function ($w) use ($like) {
                    $w->where('name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('mobile_no', 'like', $like)
                        ->orWhere('customer_code', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(name,''),' ',COALESCE(last_name,'')) LIKE ?", [$like]);
                });

            if ($tenantId > 0) {
                $customerQuery->where('tenant_id', $tenantId);
            }
            if ($branchIds !== null) {
                $customerQuery->whereIn('branch_id', $branchIds);
            }

            $customers = $customerQuery->orderBy('name')->limit(25)->get();

            $arQuery = ChitDetail::query()
                ->whereNotNull('ar_no')
                ->where('ar_no', '!=', '')
                ->where('ar_no', 'like', $like)
                ->where('status', 1);
            if ($tenantId > 0) {
                $arQuery->where('tenant_id', $tenantId);
            }
            if ($branchIds !== null) {
                $arQuery->whereIn('branch_id', $branchIds);
            }
            $arRows = $arQuery->orderByDesc('id')->limit(25)->get(['id', 'customer_id', 'branch_id', 'ar_no', 'slab_id']);

            $byId = [];
            foreach ($customers as $c) {
                $byId[(int) $c->id] = [
                    'id' => (int) $c->id,
                    'branch_id' => (int) $c->branch_id,
                    'customer_code' => $c->customer_code,
                    'name' => trim(($c->name ?? '') . ' ' . ($c->last_name ?? '')),
                    'mobile_no' => $c->mobile_no,
                    'matched_ar_no' => null,
                ];
            }
            foreach ($arRows as $ar) {
                $cid = (int) $ar->customer_id;
                if ($cid <= 0) {
                    continue;
                }
                if (!isset($byId[$cid])) {
                    $c = Customer::find($cid);
                    if (!$c || (int) $c->status !== 1) {
                        continue;
                    }
                    $byId[$cid] = [
                        'id' => $cid,
                        'branch_id' => (int) ($c->branch_id ?: $ar->branch_id),
                        'customer_code' => $c->customer_code,
                        'name' => trim(($c->name ?? '') . ' ' . ($c->last_name ?? '')),
                        'mobile_no' => $c->mobile_no,
                        'matched_ar_no' => $ar->ar_no,
                    ];
                } elseif (empty($byId[$cid]['matched_ar_no'])) {
                    $byId[$cid]['matched_ar_no'] = $ar->ar_no;
                }
            }

            $data = [];
            foreach ($byId as $row) {
                $branchName = '';
                if (!empty($row['branch_id'])) {
                    $br = Branch::find($row['branch_id']);
                    $branchName = $br ? ($br->branch_name ?? '') : '';
                }
                $display = trim(($row['name'] ?? '') . ' / ' . ($row['customer_code'] ?? ''));
                if (!empty($row['matched_ar_no'])) {
                    $display .= ' · AR ' . $row['matched_ar_no'];
                }
                $data[] = [
                    'id' => $row['id'],
                    'branch_id' => $row['branch_id'],
                    'branch_name' => $branchName,
                    'customer_code' => $row['customer_code'],
                    'name' => $row['name'],
                    'mobile_no' => $row['mobile_no'],
                    'matched_ar_no' => $row['matched_ar_no'],
                    'display_name' => $display,
                    'full_name' => $display,
                ];
                if (count($data) >= 20) {
                    break;
                }
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => \Lang::get('api.global_error'),
                'error' => $e->getMessage(),
            ], 200);
        }
    }
	
	public function get_unenrollment_mobile(Request $request)
{
    try {

        $query = Customer::where('status', 1)
            ->where('is_enrolled', 0)
            ->where('is_customer', '!=', 0);

       
        $customers = $query->orderBy('id', 'desc')->get();

        $data = [];
        $sno = 1;
        $grandChitValue = 0;
        $grandReceivedAmount = 0;

        foreach ($customers as $customer) {

            
            $data[] = [
                "sno" => $sno++,
                "customer_id" => $customer->id,
                "customer_code" => $customer->customer_code ?? '',
                "customer_name" => $customer->name ?? '',
                "mobile_no" => $customer->mobile_no ?? '',
                "formatted_name" => ($customer->name ?? '') . ' || ' . ($customer->customer_code ?? '')
            ];
        }

        return response()->json([
            "status" => true,
            "message" => "Unenrollment fetched successfully",
            
            "data" => $data
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            "status" => false,
            "message" => "Something went wrong",
            "error" => $e->getMessage()
        ], 500);
    }
}

    /**
     * Customer ID: SPN-{branch_code}-1000, then 1001, per branch.
     */
    private function generateBranchCustomerCode($branchId, $tenantId = null)
    {
        $branch = Branch::find($branchId);
        if (!$branch) {
            $fallbackId = max(((int) Customer::max('id')) + 1, 1000);
            return Config::get('constants.auto_codes.customer') . sprintf('%05d', $fallbackId);
        }

        $branchCode = trim((string) $branch->branch_code);
        if ($branchCode === '') {
            $branchCode = (string) $branchId;
        }

        $prefix = 'SPN-' . $branchCode . '-';

        $query = Customer::where('branch_id', $branchId)
            ->where('customer_code', 'like', $prefix . '%');

        if (!empty($tenantId)) {
            $query->where('tenant_id', $tenantId);
        }

        $maxSeq = 0;
        foreach ($query->pluck('customer_code') as $code) {
            $suffix = substr($code, strlen($prefix));
            if ($suffix !== '' && ctype_digit($suffix)) {
                $maxSeq = max($maxSeq, (int) $suffix);
            }
        }

        $next = $maxSeq + 1;
        if ($next < 1000) {
            $next = 1000;
        }

        return $prefix . sprintf('%04d', $next);
    }

    public function mobile_customer_file_upload(Request $request)
    {
        DB::purge('mysql');
        Config::set('database.connections.mysql.database', $request->db);
        DB::reconnect('mysql');
        return $this->fileUpload($request);
    }

    private function mobileSaveCustomerDocuments(Request $request, $customerId)
    {
        $docs = $request->input('document_details', []);
        if (!is_array($docs) || empty($docs)) {
            return;
        }

        $validDocs = array_values(array_filter($docs, function ($doc) {
            return is_array($doc) && !empty($doc['document_path']);
        }));
        if (empty($validDocs)) {
            return;
        }

        $document = new Document();
        $document->tenant_id = $request->tenant_id ?? 0;
        $document->branch_id = $request->branch_id ?? 0;
        $document->customer_id = $customerId;
        $document->collected_employee_id = $request->employee_id ?? ($request->agent_id ?? 0);
        $document->collect_date = date('Y-m-d');
        $document->collected_from = 'Mobile App';
        $document->remark = 'Uploaded from mobile customer form';
        $document->created_by = $request->created_by ?? 0;
        $document->status = 1;
        if (!$document->save()) {
            return;
        }

        foreach ($validDocs as $doc) {
            $detail = new DocumentDetail();
            $detail->document_id = $document->id;
            $detail->customer_id = $customerId;
            $detail->document_no = $doc['document_no'] ?? '';
            $detail->document_path = $doc['document_path'] ?? '';
            $detail->document_type = $doc['document_type'] ?? ($doc['document_name'] ?? 'Document');
            $detail->status = 1;
            $detail->save();
        }
    }

    /**
     * Customer birthday report: today / upcoming / completed (past in current year).
     */
    public function customer_birthday_report(Request $request)
    {
        try {
            $tenantId = $request->tenant_id ?: ($request->conditions['tenant_id'] ?? null);
            $branchId = $request->branch_id ?: ($request->conditions['branch_id'] ?? null);
            if (empty($tenantId) || empty($branchId)) {
                return response()->json(['status' => 'Error', 'msg' => 'tenant_id and branch_id are required'], 200);
            }

            $todayMd = date('m-d');
            $year = (int) date('Y');

            $query = Customer::where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->where('status', 1)
                ->whereNotNull('dob')
                ->where('dob', '!=', '0000-00-00')
                ->where('dob', '!=', '');

            $customers = $query->orderByRaw('DATE_FORMAT(dob, "%m-%d")')->get();

            $today = [];
            $upcoming = [];
            $completed = [];
            $iToday = 1;
            $iUp = 1;
            $iDone = 1;

            foreach ($customers as $c) {
                $dob = $c->dob;
                if (!$dob || $dob === '0000-00-00') {
                    continue;
                }
                $md = date('m-d', strtotime($dob));
                $row = [
                    'id' => $c->id,
                    'customer_id' => $c->id,
                    'customer_name' => trim(($c->name ?? '') . ' ' . ($c->last_name ?? '')),
                    'mobile_no' => $c->mobile_no ?? '',
                    'email' => $c->email ?? ($c->email_id ?? ''),
                    'customer_code' => $c->customer_code ?? ($c->cus_code ?? ''),
                    'dob' => date('d/m/Y', strtotime($dob)),
                    'dob_md' => $md,
                ];
                if ($md === $todayMd) {
                    $row['sno'] = $iToday++;
                    $today[] = $row;
                } elseif ($md > $todayMd) {
                    $row['sno'] = $iUp++;
                    $upcoming[] = $row;
                } else {
                    $row['sno'] = $iDone++;
                    $completed[] = $row;
                }
            }

            return response()->json([
                'status' => 'Success',
                'msg' => '',
                'data' => [
                    'today' => $today,
                    'upcoming' => $upcoming,
                    'completed' => $completed,
                    'year' => $year,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => $e->getMessage(),
            ], 200);
        }
    }
    
}
