<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\PigmyCollection;
use App\Models\OtherCharge;
use App\Models\AccountExpense;
use App\Models\Scheme;
use App\Models\Customer;
use App\Models\AccountHead;
use App\Models\BankDetail;
use Illuminate\Http\Request;
use Validator;
use DateTime;
use DB; 
use Illuminate\Support\Facades\Storage;

class OtherChargeController extends Controller
{
    /**
     * Persist one row in other_charges (used by Other Receipt page and add-customer chit RF).
     */
    public function saveOtherChargeReceipt(Request $request)
    {
        $amount = (float) (isset($request->amount) ? $request->amount : (isset($request->rf_amount) ? $request->rf_amount : 0));
        if ($amount <= 0) {
            return null;
        }

        $othercharge = new OtherCharge();
        $receipt_no = isset($request->receipt_no) ? $request->receipt_no : '';

        $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
        $othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
        $othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
        $othercharge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
        $othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
        if (empty($othercharge->employee_id) && !empty($request->logged_employee_id)) {
            $othercharge->employee_id = $request->logged_employee_id;
        }
        if (empty($othercharge->employee_id) && !empty($request->created_by)) {
            $othercharge->employee_id = $request->created_by;
        }
        $othercharge->charge_type_id = isset($request->charge_type_id) ? ($request->charge_type_id) : 0;

        if (isset($request->received_date) && is_array($request->received_date)) {
            $s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day']));
            $othercharge->received_date = date('Y-m-d', strtotime($s_date));
        } else {
            $othercharge->received_date = date('Y-m-d');
        }

        $othercharge->amount = $amount;
        $othercharge->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
        $othercharge->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0;
        $othercharge->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0;

        if (isset($request->cheque_date) && is_array($request->cheque_date)) {
            $f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day']));
            $othercharge->cheque_date = date('Y-m-d', strtotime($f_date));
        } else {
            $othercharge->cheque_date = '0000-00-00';
        }

        $othercharge->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0;
        $othercharge->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0;

        if (isset($request->transaction_date) && is_array($request->transaction_date)) {
            $f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day']));
            $othercharge->transaction_date = date('Y-m-d', strtotime($f_date));
        } else {
            $othercharge->transaction_date = '0000-00-00';
        }

        $othercharge->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
        $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
        $othercharge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
        $othercharge->status = isset($request->status) ? ($request->status) : 1;
        if ($request->payment_type_id == 2) {
            $othercharge->status = isset($request->status) ? ($request->status) : 2;
        }

        if (!$othercharge->save()) {
            return null;
        }

        if ($receipt_no == '') {
            $receipt_no = 'OTHREC-'.sprintf('%05d', $othercharge->id);
        }
        $othercharge->receipt_no = $receipt_no;
        $othercharge->save();

        if ($request->payment_type_id == 1) {
										$add_exp = new AccountExpense();                
										$add_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
										$add_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
										$add_exp->entry_date = $othercharge->received_date;
										$add_exp->voucher_no = $receipt_no;
										$add_exp->debit_account_head = 0;
										$add_exp->debit_amount = 0;
										$add_exp->credit_account_head = 1;
										$add_exp->credit_amount = $othercharge->amount;
										$add_exp->status = 1;
										$add_exp->save();
									}
									else if(($request->payment_type_id == 2) || ($request->payment_type_id == 3)|| ($request->payment_type_id == 5)){
										
										$bank_account_no = 0;
										$bank = BankDetail::where('id','=',$request->bank_id)->get();
										
										foreach($bank as $banks) {
											$bank_account_no = isset($banks->account_number) ? ($banks->account_number) : 0;
										} 
										
										$account_head_id = AccountHead::where('bank_account_number','=',$bank_account_no)->pluck('id');
										$credit_account_head = (!empty($account_head_id) && isset($account_head_id[0])) ? $account_head_id[0] : 1;
										
										$add_exp = new AccountExpense();                
										$add_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
										$add_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
										$add_exp->entry_date = $othercharge->received_date;
										$add_exp->voucher_no = $receipt_no;
										$add_exp->debit_account_head = 0;
										$add_exp->debit_amount = 0;
										$add_exp->credit_account_head = $credit_account_head;
										$add_exp->credit_amount = $othercharge->amount;
										$add_exp->status = 1;
										$add_exp->payment_mode = 9;
										$add_exp->save();
									}
									else {
										$add_exp = new AccountExpense();                
										$add_exp->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
										$add_exp->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
										$add_exp->entry_date = $othercharge->received_date;
										$add_exp->voucher_no = $receipt_no;
										$add_exp->debit_account_head = 0;
										$add_exp->debit_amount = 0;
										$add_exp->credit_account_head = 1;
										$add_exp->credit_amount = $othercharge->amount;
										$add_exp->status = 1;
										$add_exp->save();
									}
									
									// SMS SECTION
$amount = $othercharge->amount;

$today_date = date('Y-m-d');

$msg = "Dear Customer, Payment of $amount is Received, Receipt No $receipt_no on $today_date to Chit group New Enrollment, Thank you. More Details Call: 88079 06006 - SPN Chits Pvt Ltd";

// GET CUSTOMER MOBILE DIRECTLY FROM DB
$customer = Customer::find($othercharge->customer_id);



    $receiver = $customer->mobile_no;


// DEBUG LOG
\Log::info('SMS MOBILE NUMBER : ' . $receiver);

// SEND SMS
if (!empty($receiver)) {

    $sms_url = "https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=" . urlencode($msg);

    \Log::info('SMS URL : ' . $sms_url);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);

    $curl_error = curl_error($ch);

    curl_close($ch);

    // LOG RESPONSE
    \Log::info('SMS RESPONSE : ' . $result);

    if (!empty($curl_error)) {

        \Log::error('SMS CURL ERROR : ' . $curl_error);
    }
} else {

    \Log::error('SMS NOT SENT - MOBILE NUMBER EMPTY');
}

        return $othercharge;
    }

	 public function store_other_charge_type(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'employee_id'=>'required',
            'amount'=>'required',
            'received_date'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->errors()->first();
            return response()->json($response,401);
        }
        else {
            try {
                $othercharge = $this->saveOtherChargeReceipt($request);
                if ($othercharge) {
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_other_charge_type_added');
                    $response['data']=$othercharge;
                    $response['receipt_no']=$othercharge->receipt_no;
                    return response()->json($response,200);
                }
                $response['status']="Error";
                $response['msg']=\Lang::get('api.other_charges_amount_invalid');
                return response()->json($response,200);
            }
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
	}
    
    
	 public function update_other_charge_type(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'tenant_id'=>'required',
          'branch_id'=>'required',
          'enrollment_id'=>'required',
          'employee_id'=>'required',
          'amount'=>'required',
          'received_date'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $othercharge = OtherCharge::find($request->id); 
                    $othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$othercharge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
					$othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
					$othercharge->charge_type_id = isset($request->charge_type_id) ? ($request->charge_type_id) : 0;
					if(isset($request->received_date)) {
						$s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day'])); 
						$othercharge->received_date = date('Y-m-d',strtotime($s_date)); 
					}
					else {
							$othercharge->received_date = '0000-00-00';
					} 
					$othercharge->amount = isset($request->amount) ? ($request->amount) : 0 ;
					$othercharge->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
					$othercharge->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
					$othercharge->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
					if(isset($request->cheque_date)) {
						$f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
						$othercharge->cheque_date  = date('Y-m-d',strtotime($f_date)); 
					}
					else {
						$othercharge->cheque_date  = '0000-00-00';
					}
					$othercharge->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
					$othercharge->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
					if(isset($request->transaction_date)) {
						$f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
						$othercharge->transaction_date  = date('Y-m-d',strtotime($f_date)); 
					}
					else {
						$othercharge->transaction_date  = '0000-00-00';
					}
					$othercharge->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
					$othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $othercharge->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($othercharge->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_other_charge_type_deleted');
                        $response['data'] =$othercharge;
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
	
	public function get_other_charge_receipts(Request $request) {
        try {
            $cond = $request->conditions;
            $othercharges = OtherCharge::where($cond)->orderBy('id','desc')->get();
           
            foreach($othercharges as $othercharge) {
                $othercharge->branch_info = isset($othercharge->branch_det) ? ($othercharge->branch_det) : [];
				$othercharge->chit_info = ($othercharge->enrollment_det) ? ($othercharge->enrollment_det) : [];
				$othercharge->group_info = ($othercharge->enrollment_det->group_det) ? ($othercharge->enrollment_det->group_det) : [];
			    $othercharge->customer_info = ($othercharge->customer_det) ? ($othercharge->customer_det) : [];
			    $othercharge->employee_info = ($othercharge->employee_det) ? ($othercharge->employee_det) : [];
				$othercharge->payment_type_info = isset($othercharge->payment_type_det) ? ($othercharge->payment_type_det) : [] ;
				$othercharge->other_charges_info = isset($othercharge->other_charges_det) ? ($othercharge->other_charges_det) : [] ;
				$othercharge->bank_info = isset($othercharge->bank_det) ? ($othercharge->bank_det) : [];
				$othercharge->received_date = ($othercharge->received_date!=0) ? date('d/m/Y',strtotime($othercharge->received_date)) : '';   
                $othercharge->cheque_date = ($othercharge->cheque_date!=0) ? date('d/m/Y',strtotime($othercharge->cheque_date)) : '';  
			    $othercharge->transaction_date = ($othercharge->transaction_date!=0) ? date('d/m/Y',strtotime($othercharge->transaction_date)) : '';  
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $othercharges;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_other_charge_receipts(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $othercharges = OtherCharge::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $othercharges = OtherCharge::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($othercharges as $othercharge) {
				$othercharge->sno = $i;
				$othercharge->branch_info = isset($othercharge->branch_det) ? ($othercharge->branch_det) : [];
				$othercharge->chit_info = isset($othercharge->enrollment_det) ? ($othercharge->enrollment_det) : []; 
				$othercharge->group_info = isset($othercharge->enrollment_det->group_det) ? ($othercharge->enrollment_det->group_det) : [];
			    $othercharge->customer_info = isset($othercharge->customer_det) ? ($othercharge->customer_det) : [];
			    $othercharge->employee_info = isset($othercharge->employee_det) ? ($othercharge->employee_det) : [];
				$othercharge->payment_type_info = isset($othercharge->payment_type_det) ? ($othercharge->payment_type_det) : [] ;
				$othercharge->other_charges_info = isset($othercharge->other_charges_det) ? ($othercharge->other_charges_det) : [] ;
				$othercharge->bank_info = isset($othercharge->bank_det) ? ($othercharge->bank_det) : [];
				$othercharge->received_date = ($othercharge->received_date!=0) ? date('d/m/Y',strtotime($othercharge->received_date)) : '';   
                $othercharge->cheque_date = ($othercharge->cheque_date!=0) ? date('d/m/Y',strtotime($othercharge->cheque_date)) : '';  
			    $othercharge->transaction_date = ($othercharge->transaction_date!=0) ? date('d/m/Y',strtotime($othercharge->transaction_date)) : '';  
				$i++;               
            } 
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $othercharges;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_other_charge_receipts(Request $request) {
        try {
            if(isset($request->id)) {
                $othercharge = OtherCharge::find($request->id); 
                $othercharge->deleted_by = $request->deleted_by;
                $othercharge->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($othercharge->save()) {
                    $othercharge->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_other_receipt_charges_deleted');
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
	
    /**
     * Flatten other charge row for report grids (strings only, no empty arrays).
     */
    private function formatOtherChargeReportRow($othercharge, $sno)
    {
        $othercharge->sno = $sno;

        $othercharge->branch_name = ($othercharge->branch_det && !empty($othercharge->branch_det->branch_name))
            ? $othercharge->branch_det->branch_name : '';

        $customer = $othercharge->customer_det;
        $othercharge->customer_name = $customer
            ? trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) : '';

        $enrol = $othercharge->enrollment_det;
        $othercharge->ticket_no = ($enrol && !empty($enrol->ticket_no)) ? $enrol->ticket_no : '';

        $groupName = '';
        if ($enrol) {
            if ($enrol->group_det && !empty($enrol->group_det->group_name)) {
                $groupName = $enrol->group_det->group_name;
            } elseif ($enrol->scheme_det) {
                $groupName = !empty($enrol->scheme_det->scheme_format)
                    ? $enrol->scheme_det->scheme_format
                    : (isset($enrol->scheme_det->chit_value) ? $enrol->scheme_det->chit_value : '');
            }
        }
        $othercharge->group_name = $groupName;

        $emp = $othercharge->employee_det;
        $othercharge->employee_name = $emp
            ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';

        $othercharge->charge_type_name = ($othercharge->other_charges_det && !empty($othercharge->other_charges_det->charge_name))
            ? $othercharge->other_charges_det->charge_name
            : (empty($othercharge->charge_type_id) ? 'Registration Fee (RF)' : 'Other Charge');

        $othercharge->payment_type_name = ($othercharge->payment_type_det && !empty($othercharge->payment_type_det->payment_name))
            ? $othercharge->payment_type_det->payment_name : '';

        $othercharge->bank_name = ($othercharge->bank_det && !empty($othercharge->bank_det->bank_name))
            ? $othercharge->bank_det->bank_name : '';

        $branchName = $othercharge->bank_branch_name;
        $othercharge->bank_branch_name = ($branchName && $branchName !== '0') ? $branchName : '';

        $othercharge->cheque_no = ($othercharge->cheque_no && $othercharge->cheque_no !== '0') ? $othercharge->cheque_no : '';
        $othercharge->transaction_no = ($othercharge->transaction_no && $othercharge->transaction_no !== '0') ? $othercharge->transaction_no : '';
        $othercharge->debit_to = ($othercharge->debit_to && $othercharge->debit_to !== '0') ? $othercharge->debit_to : '';

        $othercharge->cheque_debit_to = ($othercharge->cheque_debit_to_info && !empty($othercharge->cheque_debit_to_info->bank_name))
            ? $othercharge->cheque_debit_to_info->bank_name : '';

        $othercharge->receipt_no = !empty($othercharge->receipt_no) ? $othercharge->receipt_no : '';
        $othercharge->alt_receipt_no = !empty($othercharge->alt_receipt_no) ? $othercharge->alt_receipt_no : '';

        $othercharge->received_date = ($othercharge->received_date && $othercharge->received_date != '0000-00-00')
            ? date('d/m/Y', strtotime($othercharge->received_date)) : '';
        $othercharge->cheque_date = ($othercharge->cheque_date && $othercharge->cheque_date != '0000-00-00')
            ? date('d/m/Y', strtotime($othercharge->cheque_date)) : '';
        $othercharge->transaction_date = ($othercharge->transaction_date && $othercharge->transaction_date != '0000-00-00')
            ? date('d/m/Y', strtotime($othercharge->transaction_date)) : '';
        $othercharge->cheque_clear_return_date = ($othercharge->cheque_clear_return_date && $othercharge->cheque_clear_return_date != '0000-00-00')
            ? date('d/m/Y', strtotime($othercharge->cheque_clear_return_date)) : '';

        $othercharge->cheque_return_amount = isset($othercharge->cheque_return_amount) ? $othercharge->cheque_return_amount : '';
        $othercharge->amount = isset($othercharge->amount) ? $othercharge->amount : 0;

        if ($othercharge->status == 1) {
            $othercharge->status_name = 'Active';
        } elseif ($othercharge->status == 2) {
            $othercharge->status_name = 'Pending';
        } elseif ($othercharge->status == 3) {
            $othercharge->status_name = 'Return';
        } elseif ($othercharge->status == 4) {
            $othercharge->status_name = 'Cleared';
        } elseif ($othercharge->status == 0) {
            $othercharge->status_name = 'In-Active';
        } else {
            $othercharge->status_name = '-';
        }

        $othercharge->cheque_clearance = ((int) $othercharge->payment_type_id === 2) ? 'View' : '-';

        return [
            'id' => $othercharge->id,
            'sno' => $sno,
            'branch_name' => $othercharge->branch_name,
            'employee_name' => $othercharge->employee_name,
            'customer_name' => $othercharge->customer_name,
            'charge_type_name' => $othercharge->charge_type_name,
            'group_name' => $othercharge->group_name,
            'ticket_no' => $othercharge->ticket_no,
            'received_date' => $othercharge->received_date,
            'receipt_no' => $othercharge->receipt_no,
            'alt_receipt_no' => $othercharge->alt_receipt_no,
            'amount' => $othercharge->amount,
            'payment_type_name' => $othercharge->payment_type_name,
            'payment_type_id' => $othercharge->payment_type_id,
            'cheque_no' => $othercharge->cheque_no,
            'cheque_date' => $othercharge->cheque_date,
            'cheque_clear_return_date' => $othercharge->cheque_clear_return_date,
            'cheque_return_amount' => $othercharge->cheque_return_amount,
            'bank_name' => $othercharge->bank_name,
            'bank_branch_name' => $othercharge->bank_branch_name,
            'transaction_no' => $othercharge->transaction_no,
            'transaction_date' => $othercharge->transaction_date,
            'cheque_clearance' => $othercharge->cheque_clearance,
            'cheque_debit_to' => $othercharge->cheque_debit_to,
            'debit_to' => $othercharge->debit_to,
            'status' => $othercharge->status,
            'status_name' => $othercharge->status_name,
        ];
    }

	public function other_charges_receipt_report(Request $request) {
		try { 
        	$cond = [];
        	$end_date='';
            $start_date = '';
        	if (!empty($request->tenant_id)) {
        		$cond['tenant_id'] = $request->tenant_id;
        	}
        	if(isset($request->branch_id)){
        		$cond['branch_id'] = $request->branch_id; 
        	}
        	if(isset($request->start_date) && is_array($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}				
        	if(isset($request->end_date) && is_array($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}

            $query = OtherCharge::with([
                'branch_det',
                'customer_det',
                'employee_det',
                'payment_type_det',
                'bank_det',
                'other_charges_det',
                'cheque_debit_to_info',
                'enrollment_det.group_det',
                'enrollment_det.scheme_det',
            ])->where($cond);

            if (!empty($request->customer_id)) {
                $query->where('customer_id', $request->customer_id);
            }
            if (!empty($request->charge_type_id)) {
                $query->where('charge_type_id', $request->charge_type_id);
            }

            if(($start_date=='') && ($end_date=='')) {
                 $othercharges = $query->orderBy('id', 'desc')->get();
            }
            else if(($start_date=='') && ($end_date!='')) {
                $othercharges = $query->where('received_date', '<=', date('Y-m-d',strtotime($end_date)))->orderBy('id', 'desc')->get();
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                $othercharges = $query->whereBetween('received_date',[$start_date,$end_date])->orderBy('id', 'desc')->get();
            } 
			$i=1;
			if(count($othercharges)>0) { 
                $tot_amt = 0;
                $grand_total_amt = 0;
                $reportRows = [];
            foreach($othercharges as $othercharge) {
                $row = $this->formatOtherChargeReportRow($othercharge, $i);
                $amount = (float) $row['amount'];
                $tot_amt += $amount;
                $reportRows[] = $row;
                $i++;
            } 
			$grand_total_amt += $tot_amt;
            $response['grand_total_amt'] = $grand_total_amt;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $reportRows;
            return response()->json($response,200);
			} else {
				$othercharges = [];
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $othercharges;
                $response['grand_total_amt'] = 0;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cheque_return_other_charge(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_return_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $othercharge = OtherCharge::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $othercharge->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $othercharge->cheque_clear_return_date = '0000-00-00';
                } 
                $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $othercharge->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $othercharge->status = isset($request->status) ? ($request->status) : 3;
                if($othercharge->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $othercharge;
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
	
	public function cheque_cleared_other_charge(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_debit_to' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $othercharge = OtherCharge::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $othercharge->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $othercharge->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $othercharge->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $othercharge->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $othercharge->status = isset($request->status) ? ($request->status) : 1;
                if($othercharge->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $othercharge;
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
	Public function get_before_enrol_receipts(Request $request)
    {
    	try
    	{
    		$receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('customer_id'=>$request->customer_id,'payment_type_id'=>8))->get();

    		$total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
			
    		$advance = BeforeEnrolReceipt::selectRaw('*,sum(amount)as total_advance')->where(array('customer_id'=>$request->customer_id,'status'=>1))->get();
    		$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
			
    		$response_data['available_advance_before_enrol'] = $total_advance - $total_received;
    		$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = 'Error';
            $response['statuss'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }
	

    public function list_before_enrol_receipts(Request $request) {
        try {  
            $cond = [];
            $cond1 =[];
            $start_date = '';
            $end_date = '';
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}		
			if(isset($request->customer_id)){
				$cond['customer_id'] = $request->customer_id; 
			}
			if(isset($request->employee_id)){
				$cond['employee_id'] = $request->employee_id; 
			}
			if(isset($request->payment_type_id)){
				$cond['payment_type_id'] = $request->payment_type_id; 
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
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
					$receipts=BeforeEnrolReceipt::where($cond)->where($cond1)->get();
				}
				if($request->branch_id ==0)
				{
					$receipts=BeforeEnrolReceipt::where($cond)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=BeforeEnrolReceipt::where($cond)->where($cond1)->where('received_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=BeforeEnrolReceipt::where($cond)->where('received_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=BeforeEnrolReceipt::where($cond)->where($cond1)->whereBetween('received_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=BeforeEnrolReceipt::where($cond)->whereBetween('received_date',[$start_date,$end_date])->get();
				}
            }  
            $i=1;      
            $grand_total = 0;    
            $tot_rcpt = 0;      
            foreach($receipts as $receipt) 
            {
                $receipt->sno = $i;
				$receipt->branch_name = isset($receipt->branch_det->branch_name) ? ($receipt->branch_det->branch_name) : '';
				$receipt->customer_name = isset($receipt->customer_det->name) ? ($receipt->customer_det->name) : '';
				$receipt->payment_type_name = isset($receipt->payment_type_det->payment_name) ? ($receipt->payment_type_det->payment_name) : '';
				$receipt->bank_name = isset($receipt->bank_det) ? ($receipt->bank_det->bank_name) : "";
                $receipt->bank_branch_name = isset($receipt->bank_det->bank_branch_name) ? ($receipt->bank_det->bank_branch_name) : '';
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
				
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];	
                $receipt->collection_employee_name = isset($receipt->employee_det) ? ($receipt->employee_det->first_name.' '.$receipt->employee_det->last_name) : "";			
				// Other branch Name
				
				$receipt->receipt_date = ($receipt->received_date!=0) ? date('d/m/Y',strtotime($receipt->received_date)) : '';
				
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '';
				$receipt->cheque_bank_info = isset($receipt->cheque_debit_to_info) ? ($receipt->cheque_debit_to_info) : [];
				$receipt->status_name =[];
				if($receipt->status == 1 )
				{
					$receipt->status_name = "Active";
				}	
				else if($receipt->status == 2 )
				{
					$receipt->status_name = "Pending";
				}		
				else if($receipt->status == 3 )
				{
					$receipt->status_name = "Return";
				}	
				else if($receipt->status == 0 )
				{
					$receipt->status_name = "In-Active";
				}else
				{
					$receipt->status_name = "-";
				}
				
                $i++;
                $receipt_amount = isset($receipt->amount) ? ($receipt->amount) : 0;
                $tot_rcpt += $receipt_amount;
            }	
            $grand_total += $tot_rcpt;
            $grand_total_format =0;
            if($grand_total > 0)
            {   
                $num = $grand_total;
                $grand_total_format = $this->moneyFormatIndia($num);  
            }            
            $response['grand_total'] = $grand_total_format;		   
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
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
	
	public function before_enrol_receipt_cheque_return(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_return_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $receipt = BeforeEnrolReceipt::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $receipt->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $receipt->cheque_clear_return_date = '0000-00-00';
                } 
                $receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $receipt->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $receipt->status = isset($request->status) ? ($request->status) : 3;
                if($receipt->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $receipt;
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
	
	public function before_enrol_receipt_cheque_cleared(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'cheque_clear_return_date' => 'required',
            'cheque_debit_to' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $receipt = BeforeEnrolReceipt::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $receipt->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $receipt->cheque_clear_return_date = '0000-00-00';
                }
                $receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $receipt->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $receipt->status = isset($request->status) ? ($request->status) : 1;
                if($receipt->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $receipt;
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
    public function delete_before_enrol_receipt(Request $request) {
        try {
         if(isset($request->receipt_no)) {
             
             $receipt_list = BeforeEnrolReceipt::where('receipt_no',$request->receipt_no)->get(); 
             foreach($receipt_list as $receipt) {
                 $receipt->deleted_by = $request->deleted_by;
                 $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                 if($receipt->save()) {
                     $receipt->delete();
                 }
                 else {
                     $response['status'] = 'Error';
                     $response['msg'] = \Lang::get('api.global_error');
                     return response()->json($response, 401);
                 }
             }
             $response['status'] = 'Success';
             $response['msg'] = \Lang::get('api.success_before_enrol_receipt_deleted');
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
 public function store_before_enrol_receipt(Request $request)
{
    $validator = Validator::make($request->all(), [
        'tenant_id'    => 'required',
        'branch_id'    => 'required',
        'amount'       => 'required',
        'received_date'=> 'required',
    ]);

    if ($validator->fails()) {

        $response['status'] = "Error";
        $response['msg'] = $validator->errors()->first();

        return response()->json($response, 401);
    }

    try {

        $receipt_no = '';

        $othercharge = new BeforeEnrolReceipt();

        $othercharge->tenant_id = $request->tenant_id ?? 0;
        $othercharge->branch_id = $request->branch_id ?? 0;

       

        $othercharge->customer_id = $request->customer_id;

        $othercharge->employee_id = $request->employee_id ?? 0;

        // RECEIVED DATE
        if (isset($request->received_date)) {

            $s_date =
                $request->received_date['year'] . '-' .
                sprintf('%02d', $request->received_date['month']) . '-' .
                sprintf('%02d', $request->received_date['day']);

            $othercharge->received_date = date('Y-m-d', strtotime($s_date));

        } else {

            $othercharge->received_date = '0000-00-00';
        }

        $othercharge->amount = $request->amount ?? 0;

        $othercharge->payment_type_id = $request->payment_type_id ?? 0;

        $othercharge->debit_to = $request->debit_to ?? 0;

        $othercharge->cheque_no = $request->cheque_no ?? '';

        // CHEQUE DATE
        if (!empty($request->cheque_date)) {

    if (is_array($request->cheque_date)) {

        $f_date =
            $request->cheque_date['year'] . '-' .
            sprintf('%02d', $request->cheque_date['month']) . '-' .
            sprintf('%02d', $request->cheque_date['day']);

        $othercharge->cheque_date = date('Y-m-d', strtotime($f_date));

    } else {

        $othercharge->cheque_date = date('Y-m-d', strtotime($request->cheque_date));
    }
}

        $othercharge->bank_id = $request->bank_id ?? 0;

        $othercharge->bank_branch_name = $request->bank_branch_name ?? '';

        // TRANSACTION DATE
        if (!empty($request->received_date)) {

    // ARRAY FORMAT
    if (is_array($request->received_date)) {

        $s_date =
            $request->received_date['year'] . '-' .
            sprintf('%02d', $request->received_date['month']) . '-' .
            sprintf('%02d', $request->received_date['day']);

        $othercharge->received_date = date('Y-m-d', strtotime($s_date));

    } else {

        // STRING FORMAT
        $othercharge->received_date = date('Y-m-d', strtotime($request->received_date));
    }

} else {

    $othercharge->received_date = null;
}

        $othercharge->transaction_no = $request->transaction_no ?? '';

        $othercharge->remarks = $request->remarks ?? '';

        $othercharge->created_by = $request->created_by ?? 1;

        $othercharge->status = $request->status ?? 1;

        // CHEQUE PAYMENT PENDING STATUS
        if ($request->payment_type_id == 2) {

            $othercharge->status = $request->status ?? 2;
        }

        // AMOUNT VALIDATION
        if ($othercharge->amount <= 0) {

            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.other_charges_amount_invalid');

            return response()->json($response, 200);
        }

        // SAVE RECEIPT
        if ($othercharge->save()) {

            // RECEIPT NUMBER GENERATION
            if ($receipt_no == '') {

                $current = $request->received_date['year'];

                $splitName = explode('0', $current, 2);

                $receipt_no = 'BDVRCPT-' . $splitName[1] . '-' . sprintf('%05d', $othercharge->id);
            }

            $othercharge->receipt_no = $receipt_no;

            $othercharge->save();

            // ACCOUNT ENTRY
            $add_exp = new AccountExpense();

            $add_exp->tenant_id = $othercharge->tenant_id ?? 0;

            $add_exp->branch_id = $othercharge->branch_id ?? 0;

            $add_exp->entry_date = $othercharge->received_date ?? null;

            $add_exp->voucher_no = $othercharge->receipt_no ?? '';

            $add_exp->debit_account_head = 7;

            $add_exp->debit_amount = $othercharge->amount ?? 0;

            $add_exp->credit_account_head = 0;

            $add_exp->credit_amount = 0;

            $add_exp->status = 1;

            $add_exp->save();

            // SMS SECTION
$amount = $request->amount;

$today_date = date('Y-m-d');

$msg = "Dear Customer, Payment of $amount is Received, Receipt No $receipt_no on $today_date to Chit group New Enrollment, Thank you. More Details Call: 88079 06006 - SPN Chits Pvt Ltd";

// GET CUSTOMER MOBILE DIRECTLY FROM DB
$customer = Customer::find($othercharge->customer_id);



    $receiver = $customer->mobile_no;


// DEBUG LOG
\Log::info('SMS MOBILE NUMBER : ' . $receiver);

// SEND SMS
if (!empty($receiver)) {

    $sms_url = "https://global.datagenit.com/API/sms-api.php?auth=D!~10177UgDGQ5J5tg&msisdn=$receiver&senderid=SRISPN&message=" . urlencode($msg);

    \Log::info('SMS URL : ' . $sms_url);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $sms_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $result = curl_exec($ch);

    $curl_error = curl_error($ch);

    curl_close($ch);

    // LOG RESPONSE
    \Log::info('SMS RESPONSE : ' . $result);

    if (!empty($curl_error)) {

        \Log::error('SMS CURL ERROR : ' . $curl_error);
    }
} else {

    \Log::error('SMS NOT SENT - MOBILE NUMBER EMPTY');
}

            $response['status'] = "Success";
            $response['receipt_no'] = $othercharge->receipt_no;
            $response['msg'] = \Lang::get('api.success_before_enrol_receipt_added_successfully');
            $response['data'] = $othercharge;

            return response()->json($response, 200);
        }

        $response['status'] = "Error";
        $response['msg'] = "Unable to save receipt";

        return response()->json($response, 200);

    } catch (\Exception $e) {

        \Log::error('BEFORE ENROL RECEIPT ERROR', [
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile()
        ]);

        $response['status'] = "Error";
        $response['msg'] = $e->getMessage();
        $response['line'] = $e->getLine();

        return response()->json($response, 401);
    }
}
	
	public function list_pigmy_receipts(Request $request) {
        try {  
            $cond = [];
            $cond1 =[];
            $start_date = '';
            $end_date = '';
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}		
			if(isset($request->customer_id)){
				$cond['customer_id'] = $request->customer_id; 
			}
			if(isset($request->employee_id)){
				$cond['employee_id'] = $request->employee_id; 
			}
			if(isset($request->payment_type_id)){
				$cond['payment_type_id'] = $request->payment_type_id; 
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
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
					$receipts=PigmyCollection::where($cond)->where($cond1)->get();
				}
				if($request->branch_id ==0)
				{
					$receipts=PigmyCollection::where($cond)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=PigmyCollection::where($cond)->where($cond1)->where('received_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=PigmyCollection::where($cond)->where('received_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=PigmyCollection::where($cond)->where($cond1)->whereBetween('received_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=PigmyCollection::where($cond)->whereBetween('received_date',[$start_date,$end_date])->get();
				}
            }  
            $i=1;      
            $grand_total = 0;    
            $tot_rcpt = 0;      
            foreach($receipts as $receipt) 
            {
                $receipt->sno = $i;
				$receipt->branch_name = isset($receipt->branch_det->branch_name) ? ($receipt->branch_det->branch_name) : '';
				$receipt->customer_name = isset($receipt->customer_det->name) ? ($receipt->customer_det->name) : '';
				$receipt->payment_type_name = isset($receipt->payment_type_det->payment_name) ? ($receipt->payment_type_det->payment_name) : '';
				$receipt->bank_name = isset($receipt->bank_det) ? ($receipt->bank_det->bank_name) : "";
                $receipt->bank_branch_name = isset($receipt->bank_det->bank_branch_name) ? ($receipt->bank_det->bank_branch_name) : '';
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
				
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];	
                $receipt->collection_employee_name = isset($receipt->employee_det) ? ($receipt->employee_det->first_name.' '.$receipt->employee_det->last_name) : "";			
				// Other branch Name
				
				$receipt->receipt_date = ($receipt->received_date!=0) ? date('d/m/Y',strtotime($receipt->received_date)) : '';
				
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '';
				$receipt->cheque_bank_info = isset($receipt->cheque_debit_to_info) ? ($receipt->cheque_debit_to_info) : [];
				$receipt->status_name =[];
				if($receipt->status == 1 )
				{
					$receipt->status_name = "Active";
				}	
				else if($receipt->status == 2 )
				{
					$receipt->status_name = "Pending";
				}		
				else if($receipt->status == 3 )
				{
					$receipt->status_name = "Return";
				}	
				else if($receipt->status == 0 )
				{
					$receipt->status_name = "In-Active";
				}else
				{
					$receipt->status_name = "-";
				}
				
                $i++;
                $receipt_amount = isset($receipt->amount) ? ($receipt->amount) : 0;
                $tot_rcpt += $receipt_amount;
            }	
            $grand_total += $tot_rcpt;
            $grand_total_format =0;
            if($grand_total > 0)
            {   
                $num = $grand_total;
                $grand_total_format = $this->moneyFormatIndia($num);  
            }            
            $response['grand_total'] = $grand_total_format;		   
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	
	public function get_othercharge_details(Request $request) {
		try { 
        	$cond = [];
        	$end_date='';
            $start_date = '';
        	 $cond = $request->conditions;
        	
                $othercharges = OtherCharge::where($cond)->get();
            
			$i=1;
			if(count($othercharges)>0) { 
                $tot_amt = 0;
                $grand_total_amt = 0;
            foreach($othercharges as $othercharge) {
                $othercharge->sno = $i;
                $othercharge->branch_name = isset($othercharge->branch_det->branch_name) ? ($othercharge->branch_det->branch_name) : [];
				$othercharge->ticket_no = isset($othercharge->enrollment_det->ticket_no) ? ($othercharge->enrollment_det->ticket_no) : [];
				$othercharge->group_name = isset($othercharge->enrollment_det->group_det->group_name) ? ($othercharge->enrollment_det->group_det->group_name) : [];
			    $othercharge->customer_name = isset($othercharge->customer_det->name) ? ($othercharge->customer_det->name) : [];
				$scheme_id = isset($othercharge->enrollment_det->group_det->group_name) ? ($othercharge->enrollment_det->group_det->scheme_id) : [];
				
				$chit_value = Scheme::where('id',$scheme_id)->pluck('chit_value');
				$othercharge->chit_value = $chit_value[0];
				// $first_name = ($othercharge->employee_det->first_name) ? ($othercharge->employee_det->first_name) : [];
				// $last_name = ($othercharge->employee_det->last_name) ? ($othercharge->employee_det->last_name) : [];
				// $othercharge->employee_name = $first_name.' '.$last_name;
				$othercharge->employee_name = isset($payment->employee_det) ? ($payment->employee_det->first_name.' '.$payment->employee_det->last_name) : '';
				$othercharge->payment_type_name = isset($othercharge->payment_type_det->payment_name) ? ($othercharge->payment_type_det->payment_name) : [];
				$othercharge->bank_name = isset($othercharge->bank_det->bank_name) ? ($othercharge->bank_det->bank_name) : [];
				$othercharge->received_date = ($othercharge->received_date!=0) ? date('d/m/Y',strtotime($othercharge->received_date)) : '';   
                $othercharge->cheque_date = ($othercharge->cheque_date!=0) ? date('d/m/Y',strtotime($othercharge->cheque_date)) : '';  
			    $othercharge->transaction_date = ($othercharge->transaction_date!=0) ? date('d/m/Y',strtotime($othercharge->transaction_date)) : ''; 
				
				
				$othercharge->other_charge_name = isset($othercharge->other_charges_det) ? ($othercharge->other_charges_det->charge_name) : [] ;	
				if($othercharge->status == 1 )
				{
					$othercharge->status_name = "Active";
				}	
				else if($othercharge->status == 2 )
				{
					$othercharge->status_name = "Pending";
				}		
				else if($othercharge->status == 3 )
				{
					$othercharge->status_name = "Return";
				}	
				else if($othercharge->status == 0 )
				{
					$othercharge->status_name = "In-Active";
				}else
				{
					$othercharge->status_name = "-";
				}
                $amount = isset($othercharge->amount) ? ($othercharge->amount) : 0;
                $tot_amt += $amount;
                $i++;
            } 
			$grand_total_amt += $tot_amt;
            $response['grand_total_amt'] = $grand_total_amt;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $othercharges;
            return response()->json($response,200);
			} else {
				$othercharges = [];
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $othercharges;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
