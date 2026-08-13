<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\BiddingDetail;
use App\Models\ChitDetail;
use App\Models\Scheme;
use App\Models\SlabDue;
use App\Models\BankDetail;
use App\Models\Receipt;
use App\Models\AdvanceReceipt;
use App\Models\AccountExpense;
use App\Models\AccountHead;
use App\Models\PaymentType;
use App\Models\BeforeEnrolReceipt;
use App\Models\OtherCharge;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\LoanReceipt;
use App\Models\LoanDetail;
use Illuminate\Http\Request;
use Lang;
use Validator;
use DateInterval;
use DateTime;
use DatePeriod;
use DB;

class PaymentController extends Controller
{
   public function store_payment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'tenant_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => "Error",
            'msg' => $validator->errors()->first()
        ], 200);
    }

    DB::beginTransaction();
    try {
        $payment = new Payment();
        $payment->tenant_id = $request->tenant_id ?? 0;
        $payment->branch_id = $request->branch_id ?? '';
        $payment->group_id = $request->group_id ?? '';
        $payment->bidding_id = $request->bidding_id ?? '';
        $payment->customer_id = $request->customer_id ?? '';
        $payment->enrollment_id = $request->enrollment_id ?? '';
        $payment->employee_id = $request->employee_id ?? 0;
        $payment->payment_for = "1";
        $payment->payment_type_id = $request->payment_type_id ?? 0;
        $payment->total_amount = $request->customer_released ?? 0;
        $payment->bank_id = $request->bank_id ?? 0;
        $payment->bank_branch_name = $request->bank_branch_name ?? '';
        $payment->cheque_no = $request->cheque_no ?? '';
        $payment->cheque_status = $request->cheque_status ?? '';
        $payment->transaction_no = $request->transaction_no ?? '';
        $payment->debit_to = $request->debit_to ?? '';
        $payment->created_by = $request->created_by ?? 0;
        $payment->status = $request->payment_type_id == 2 ? 2 : ($request->status ?? 1);
        $payment->payment_date = $this->formatDate($request->payment_date ?? null);
        $payment->cheque_date = $this->formatDate($request->cheque_date ?? null);
        $payment->transaction_date = $this->formatDate($request->transaction_date ?? null);

        if ($payment->total_amount <= 0) {
            return response()->json([
                'status' => "Error",
                'msg' => "Release Amount Invalid"
            ], 200);
        }

        $payment->save();
        $payment->receipt_no = 'PYMT-' . $payment->id;
        $payment->save();

        // Update Bidding Table Logic
        $this->updateBiddingFromPayment($payment, $request);

        // Post to Account Expenses
        $this->postPaymentExpenses($payment);

        DB::commit();

        return response()->json([
            'status' => 'Success',
            'msg' => \Lang::get('api.success_payment_added'),
            'receipt_no' => $payment->receipt_no,
            'data' => $payment
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'Error',
            'msg' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 200);
    }
}

private function updateBiddingFromPayment($payment, $request)
{
    $bidding = BiddingDetail::find($payment->bidding_id);
    if (!$bidding) {
        throw new \Exception("Bidding record not found for ID: " . $payment->bidding_id);
    }

    $payment_count = Payment::where('bidding_id', $bidding->id)->count();
    $payment_amount = round($payment->total_amount);
    $pending_due_amount = round($request->pending_due_amount ?? 0);
    $gst = round($request->gst ?? 0);
    $cgst = round($request->cgst ?? 0);
    $sgst = round($request->sgst ?? 0);

    if ($payment_count === 1) {
        // First payment, apply GST and update amounts
        $bidding->cgst = $cgst;
        $bidding->sgst = $sgst;
        $bidding->gst = $gst;

        $bidding->pending_due_amount += $pending_due_amount;
        $bidding->total_release_amount -= $gst;
        $bidding->pending_release -= ($payment_amount + $gst);
        $bidding->customer_released += $payment_amount;
    } else {
        // Subsequent payments
        $bidding->pending_due_amount += $pending_due_amount;
        $bidding->pending_release -= $payment_amount;
        $bidding->customer_released += $payment_amount;
        // gst remains unchanged
    }

    $bidding->save();
}


private function postPaymentExpenses($payment)
{
    $voucher_no = $payment->receipt_no;
    $amount = $payment->total_amount;
    $date = $payment->payment_date;

    // Dynamic Debit (Where money goes): e.g. Customer Released
    $debit_account = $this->getAccountHeadId('Auction Settlements', $payment->tenant_id);

    // Dynamic Credit (From where money comes: Cash or Bank)
    $credit_account = $this->getCreditAccountHead($payment);

    $customer = Customer::find($payment->customer_id);
    $customer_name = $customer ? ($customer->name . ' ' . $customer->last_name) : 'Unknown';

    $remarks = "Payment made to $customer_name";

    AccountExpense::create([
        'tenant_id' => $payment->tenant_id,
        'branch_id' => $payment->branch_id,
        'entry_date' => $date,
        'voucher_no' => $voucher_no,
        'debit_account_head' => $debit_account,
        'debit_amount' => $amount,
        'credit_account_head' => 0,
        'credit_amount' => 0,
        'remarks' => $remarks,
        'status' => 1
    ]);

    AccountExpense::create([
        'tenant_id' => $payment->tenant_id,
        'branch_id' => $payment->branch_id,
        'entry_date' => $date,
        'voucher_no' => $voucher_no,
        'debit_account_head' => 0,
        'debit_amount' => 0,
        'credit_account_head' => $credit_account,
        'credit_amount' => $amount,
        'remarks' => $remarks,
        'status' => 1
    ]);
}

private function formatDate($date)
{
    if (!$date || !isset($date['year'])) return '0000-00-00';
    return sprintf('%04d-%02d-%02d', $date['year'], $date['month'], $date['day']);
}

private function getAccountHeadId($name, $tenant_id)
{
    $id = AccountHead::where('name', $name)->where('tenant_id', $tenant_id)->value('id');
    if (!$id) throw new \Exception("Account head '$name' not found");
    return $id;
}

private function getCreditAccountHead($payment)
{
    if ($payment->payment_type_id == 1) {
        return $this->getAccountHeadId('Cash', $payment->tenant_id);
    }

    $bank = BankDetail::find($payment->bank_id);
    if (!$bank) throw new \Exception("Bank not found");

    $account_head = AccountHead::where('bank_account_number', $bank->account_number)
        ->where('tenant_id', $payment->tenant_id)
        ->value('id');

    if (!$account_head) throw new \Exception("Bank account head not found");
    return $account_head;
}

    public function update_payment(Request $request) {
        $validator = Validator::make($request->all(), [
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
                    $payment = Payment::find($request->id);
					$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                    $payment->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
                    $payment->group_id = isset($request->group_id) ? $request->group_id : 0;
                    $payment->bidding_id = isset($request->bidding_id) ? $request->bidding_id : 0;
                    $payment->customer_id = isset($request->customer_id) ? $request->customer_id : 0;
                    $payment->enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : 0;
				    if(isset($request->payment_date)) {
						$s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
						$payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
                    $payment->payment_for = isset($request->payment_for) ? $request->payment_for : '';
                    $payment->payment_type_id = isset($request->payment_type_id) ? $request->payment_type_id : 0;
                    $payment->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
                    $payment->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
                    $payment->bank_branch_name = isset($request->bank_branch_name) ? $request->bank_branch_name : '';
                    $payment->cheque_no = isset($request->cheque_no) ? $request->cheque_no : '';
				    if(isset($request->cheque_date)) {
						$s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
						$payment->cheque_date = date('Y-m-d',strtotime($s_date)); 
					}
                    $payment->cheque_status = isset($request->cheque_status) ? $request->cheque_status : '';
                    $payment->transaction_no = isset($request->transaction_no) ? $request->transaction_no : 0;
				    if(isset($request->transaction_date)) {
						$s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
						$payment->transaction_date = date('Y-m-d',strtotime($s_date)); 
					}
                    $payment->debit_to = isset($request->debit_to) ? $request->debit_to : '';
                    $payment->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($payment->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_payment_updated');
                        $response['data'] = $payment;
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

    public function get_payments(Request $request) {
        try {
            $cond = $request->conditions; 
            $payments = Payment::where($cond)->orderBy('id','desc')->get();
            foreach($payments as $payment) {
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
				$payment->group_info = ($payment->group_det) ? ($payment->group_det) : [];
				$payment->chit_info = ($payment->enrol_det) ? ($payment->chit_info) : [];
			    $payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
				$payment->payment_type_info = isset($payment->payment_type_det) ? ($payment->payment_type_det) : [] ;
				$payment->bank_info = isset($payment->bank_det) ? ($payment->bank_det) : [];
				$payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';   
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';  
			    $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';  
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function getTrialBalance(Request $request)
{
    $tenant_id = 63;
    $branch_id = 4;
    $from_date = '2025-07-01';
    $to_date = '2025-07-16';

    // Base query with filters
    $query = DB::table('account_expenses')
        ->select(
            'account_heads.id as account_head_id',
            'account_heads.name as account_head_name',
            'account_groups.group_name as group_name',
            DB::raw('SUM(account_expenses.debit_amount) as total_debit'),
            DB::raw('SUM(account_expenses.credit_amount) as total_credit')
        )
        ->leftJoin('account_heads', function ($join) {
            $join->on('account_expenses.debit_account_head', '=', 'account_heads.id')
                 ->orOn('account_expenses.credit_account_head', '=', 'account_heads.id');
        })
        ->leftJoin('account_groups', 'account_heads.group_id', '=', 'account_groups.id')
        ->where('account_expenses.tenant_id', $tenant_id);

    if ($branch_id) {
        $query->where('account_expenses.branch_id', $branch_id);
    }

    if ($from_date && $to_date) {
        $query->whereBetween('account_expenses.entry_date', [$from_date, $to_date]);
    }

    $query->groupBy('account_heads.id', 'account_heads.name', 'account_groups.group_name');

    $results = $query->get();

    // Format output
    $trial_balance = [];
    foreach ($results as $row) {
        $net = $row->total_debit - $row->total_credit;
        $trial_balance[] = [
            'account_head_id' => $row->account_head_id,
            'account_head_name' => $row->account_head_name,
            'group_name' => $row->group_name,
            'debit' => $net > 0 ? $net : 0,
            'credit' => $net < 0 ? abs($net) : 0,
        ];
    }

    return response()->json([
        'status' => 'Success',
        'data' => $trial_balance
    ]);
}

	public function list_payments(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $payments = Payment::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $payments = Payment::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($payments as $payment) {
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
				$payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
                $payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';
                $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';
                $payment->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function view_payment(Request $request) {
        try { 
            $cond = $request->conditions;  
            $payments = Payment::where($cond)->orderBy('id','desc')->get(); 
            $view_det = [];
            if(count($payments)>0) {
                $view_det['voucher_no'] = $payments[0]->id;
                $view_det['company_name'] = $payments[0]->tenant_det->chit_fund_name;
                $view_det['branch_name'] = $payments[0]->branch_det->branch_name;
                $view_det['group_name'] = $payments[0]->group_det->group_name;
                $view_det['ticket_no'] = $payments[0]->enrol_det->ticket_no;
                $customer_name = $payments[0]->customer_det->name . ' ' . $payments[0]->customer_det->last_name . ' | ' . $payments[0]->customer_det->customer_code;
                $ticket_no = isset($payments[0]->enrol_det) ? ($payments[0]->enrol_det->ticket_no) : "";
                $phone_no = isset($payments[0]->customer_det) ? ($payments[0]->customer_det->mobile_no) : 0;
                
                if($payments[0]->enrol_det->ticket_type == 1)
                {
                    $ticket_type = "full_ticket";
                }
                else if($payments[0]->enrol_det->ticket_type == 2)
                {
                    $ticket_type = "half_ticket";
                }else{
                    $ticket_type = "_";
                }
                $view_det['customer_name_det'] = $customer_name. " / ".$ticket_no." / ". $phone_no ." / ". $ticket_type;  
                $view_det['customer_name'] = $payments[0]->customer_det->name . ' ' . $payments[0]->customer_det->last_name . ' | ' . $payments[0]->customer_det->customer_code;
                $view_det['chit_value'] = $payments[0]->group_det->scheme_det->chit_value * $payments[0]->enrol_det->ticket_type;
                $view_det['bidding_amount'] = $payments[0]->auction_det->bidding_amount;
                $view_det['commission_amount'] = $payments[0]->auction_det->commission_amount;
                $view_det['total_release_amount'] = $payments[0]->auction_det->total_release_amount;
                $view_det['customer_released_amount'] = $payments[0]->auction_det->customer_released; 
                $view_det['cgst'] = $payments[0]->auction_det->cgst;
                $view_det['sgst'] = $payments[0]->auction_det->sgst;
                $view_det['gst'] = $payments[0]->auction_det->gst; 
				$payment_date = Payment::where(array("bidding_id"=>$payments[0]->bidding_id,"payment_for" => "1"))->pluck('payment_date');
                $view_det['payment_date'] = date('d/m/Y',strtotime($payment_date[0])); 
                
                $bid_advance_amount = 0;
                $bidding_advance_details = Payment::selectRaw('sum(total_amount) as bidding_advance')->where(array("bidding_id"=>$payments[0]->bidding_id,"payment_for" => "2"))->get();
                $bid_advance_amount = round($bidding_advance_details[0]->bidding_advance);
                $view_det['bid_advance_amount'] = $bid_advance_amount;
                $view_det['bid_payment_adjustment'] = $payments[0]->auction_det->pending_due_amount;
                foreach($payments as $pay) {
                    $pay->payment_date = ($pay->payment_date!=0) ? date('d/m/Y',strtotime($pay->payment_date)) : '';
                    $pay->payment_type = isset($pay->payment_type_det) ? $pay->payment_type_det->payment_name : '';
                    $pay->cheque_date = ($pay->cheque_date!=0) ? date('d/m/Y',strtotime($pay->cheque_date)) : '';
                    $pay->transaction_date = ($pay->transaction_date!=0) ? date('d/m/Y',strtotime($pay->transaction_date)) : '';
                    $pay->emp_name = isset($pay->employee_det) ? $pay->employee_det->first_name.' '.$pay->employee_det->last_name : ''; 
                }
				//amount in words
			   $number = $payments[0]->auction_det->customer_released;
			   $no = floor($number);
			   $point = round($number - $no, 2) * 100;
			   $hundred = null;
			   $digits_1 = strlen($no);
			   $i = 0;
			   $str = array();
			   $words = array('0' => '', '1' => 'One', '2' => 'Two',
				'3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
				'7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
				'10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
				'13' => 'Thirteen', '14' => 'Fourteen',
				'15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
				'18' => 'Eighteen', '19' =>'Nineteen', '20' => 'Twenty',
				'30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
				'60' => 'Sixty', '70' => 'Seventy',
				'80' => 'Eighty', '90' => 'Ninety');
				$digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
				while ($i < $digits_1) {
					$divider = ($i == 2) ? 10 : 100;
					$number = floor($no % $divider);
					$no = floor($no / $divider);
					$i += ($divider == 10) ? 1 : 2;
					if ($number) {
						$plural = (($counter = count($str)) && $number > 9) ? 's' : null;
						$hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
						$str [] = ($number < 21) ? $words[$number] .
						" " . $digits[$counter] . $plural . " " . $hundred
									:
						$words[floor($number / 10) * 10]
						. " " . $words[$number % 10] . " "
						. $digits[$counter] . $plural . " " . $hundred;
					} else $str[] = null;
				}
				$str = array_reverse($str);
				$result = implode('', $str);
				$points = ($point) ?
				"." . $words[$point / 10] . " " . 
				$words[$point = $point % 10] : '';	
				
                $view_det['payments'] = $payments;
$response['amount_in_words'] = $result;				
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $view_det;
                return response()->json($response,200);
            }
            else {
                $response['status'] = "error";
                $response['msg'] = \Lang::get('api.no_payments_found');
                return response()->json($response, 200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = "error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_payment(Request $request) {
        try {
            if(isset($request->id)) {
                $payment = Payment::find($request->id); 
                $payment->deleted_by = $request->deleted_by;
                $payment->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($payment->save()) {
                    $payment->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_payment_deleted');
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

    public function get_payment_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';        	
        	if(isset($request->group_id)){
        		$cond['group_id'] = $request->group_id; 
        	}
			$cond2['payment_for'] = 1; 
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
                    $payments = Payment::where($cond)->where('payment_for','!=',5)->where($cond1)->get();
                }
                if($request->branch_id == 0){
                    $payments = Payment::where($cond)->where('payment_for','!=',5)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->where('payment_for','!=',5)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0){
                    $payments = Payment::where($cond)->where('payment_for','!=',5)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where('payment_for','!=',5)->where($cond1)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0){
                    $payments = Payment::where($cond)->where('payment_for','!=',5)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }
            } 
			$i=1;
			if(count($payments)>0) 
            {
                $tot_chit_value = 0;
                $grand_tot_chit_value = 0;
                $tot_released_amt = 0;
                $grand_tot_released_amt = 0;
                foreach($payments as $payment) {
                    $payment->sno = $i;
                    $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                    $payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : [];                
                    //$payment->chit_value = isset($payment->group_det->scheme_det->chit_value) ? ($payment->group_det->scheme_det->chit_value) : [];
    				$chit_value = 0;
    				if($payment->payment_for == 7)
    				{
                        $slab_id = isset($payment->enrol_det) ? ($payment->enrol_det->slab_id) : 0;
                        $slab_types = SlabDue::where('id',$slab_id)->get();   
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                        $slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $payment->ar_no ?? null);
                        $payment->group_name = "Commitment"."-".$slab_name;
    					$chit_value = isset($payment->enrol_det->scheme_det) ? ($payment->enrol_det->scheme_det->chit_value) : 0;
    				}
    				if($payment->payment_for != 7)
    				{
                        $payment->group_name = isset($payment->group_det) ? ($payment->group_det->group_name) : [];
    					$chit_value = isset($payment->enrol_det->group_det->scheme_det) ? ($payment->enrol_det->group_det->scheme_det->chit_value) : 0;
    				}
                    $payment->chit_value = $chit_value;
                    $payment->ticket_no = isset($payment->enrol_det) ? ($payment->enrol_det->ticket_no) : [];
                    $payment->bank_name = isset($payment->bank_det) ? ($payment->bank_det->bank_name) : [];
                    $payment->bank_branch_name = isset($payment->bank_det) ? ($payment->bank_det->bank_branch_name) : [];
    				$payment->customer_name = $payment->customer_det->name . ' ' . $payment->customer_det->last_name;
    				$payment->customer_mobile = isset($payment->customer_det) ? ($payment->customer_det->mobile_no) : [];
                    $payment->installment_no = isset($payment->auction_det) ? ($payment->auction_det->installment_no) : 0;
                    $payment->payment_type_name = isset($payment->payment_type_det) ? ($payment->payment_type_det->payment_name) : [];
    				
    				$payment->status_name =[];
    				if($payment->status == 1 )
    				{
    					$payment->status_name = "Active";
    				}	
    				else if($payment->status == 2 )
    				{
    					$payment->status_name = "Pending";
    				}		
    				else if($payment->status == 3 )
    				{
    					$payment->status_name = "Return";
    				}	
    				else if($payment->status == 0 )
    				{
    					$payment->status_name = "In-Active";
    				}else
    				{
    					$payment->status_name = "-";
    				}
                    $i++;
                    $total_amount = isset($payment->total_amount) ? ($payment->total_amount) : 0;
                    $tot_chit_value += $chit_value;
                    $tot_released_amt += $total_amount;
                } 
                $grand_tot_chit_value += $tot_chit_value;
                $grand_tot_released_amt += $tot_released_amt;

                $grand_tot_chit_value_format =0;
                $grand_tot_released_amt_format =0;

                if($grand_tot_chit_value > 0)
                {   
                    $num = $grand_tot_chit_value;
                    $grand_tot_chit_value_format = $this->moneyFormatIndia($num);
                }
                if($grand_tot_released_amt > 0)
                {   
                    $num = $grand_tot_released_amt;
                    $grand_tot_released_amt_format = $this->moneyFormatIndia($num);
                }
                $response['grand_tot_chit_value'] = $grand_tot_chit_value_format;
                $response['grand_tot_released_amt'] = $grand_tot_released_amt_format;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $payments;
				return response()->json($response,200);
			} else {
				$payments = [];
                $response['grand_tot_chit_value'] = 0;
                $response['grand_tot_released_amt'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $payments;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
  
    public function get_payment_pending_report(Request $request) {
		try { 
        	$cond = [];
            $cond1 = [];        	
        	if(isset($request->group_id)){
        		$cond['group_id'] = $request->group_id; 
        	}
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $bidding_details = BiddingDetail::where($cond)->where('pending_release','>',0)->where('customer_id','!=',0)->where($cond1)->get();
            }
            if($request->branch_id == 0){
                $bidding_details = BiddingDetail::where($cond)->where('customer_id','!=',0)->where('pending_release','>',0)->get();
            }
			$i=1;
			if(count($bidding_details)>0)
            {
                $grand_tot_chit_value = 0;
                $tot_chit_value = 0;
                $grand_tot_bidding_amount = 0;
                $tot_bidding_amount = 0;
                $grand_tot_pending_due_amount = 0;
                $tot_pending_due_amount = 0;
                $grand_tot_gst = 0;
                $tot_gst =0;
                $grand_tot_release_amount = 0;
                $tot_release_amount =0;
                $grand_tot_customer_released = 0;
                $tot_customer_released =0;
                $grand_tot_pending_release =0;
                $tot_pending_release =0;
                foreach($bidding_details as $bidding_detail) 
                {
                    $bidding_detail->sno = $i;
                    $bidding_detail->tenant_info = isset($bidding_detail->tenant_det) ? ($bidding_detail->tenant_det) : [];
                    $bidding_detail->branch_name = isset($bidding_detail->branch_det->branch_name) ? ($bidding_detail->branch_det->branch_name) : [];
                    $bidding_detail->group_name = isset($bidding_detail->group_det->group_name) ? ($bidding_detail->group_det->group_name) : [];
                    $bidding_detail->scheme_info = isset($bidding_detail->scheme_det) ? ($bidding_detail->scheme_det) : [];
                    $bidding_detail->ticket_no = isset($bidding_detail->enrollment_det->ticket_no) ? ($bidding_detail->enrollment_det->ticket_no) : [];
    				//$bidding_detail->customer_name = isset($bidding_detail->customer_det->name) ? ($bidding_detail->customer_det->name) : [];
					 $bidding_detail->customer_name = $bidding_detail->customer_det->name . ' ' . $bidding_detail->customer_det->last_name;
    				$bidding_detail->customer_mobile = isset($bidding_detail->customer_det->mobile_no) ? ($bidding_detail->customer_det->mobile_no) : [];
    				if($bidding_detail->pending_release == 0){
    					$bidding_detail->payment_type='No Outstanding'; 
    				} else {
    					$bidding_detail->payment_type='Payment Pending'; 
    				}
                    $chit_value = isset($bidding_detail->chit_value) ? ($bidding_detail->chit_value) : 0;
                    $bidding_amount = isset($bidding_detail->bidding_amount) ? ($bidding_detail->bidding_amount) : 0;
                    $pending_due_amount = isset($bidding_detail->pending_due_amount) ? ($bidding_detail->pending_due_amount) : 0;
                    $total_release_amount = isset($bidding_detail->total_release_amount) ? ($bidding_detail->total_release_amount) : 0;
                    $gst = isset($bidding_detail->gst) ? ($bidding_detail->gst) : 0;
                    $customer_released = isset($bidding_detail->customer_released) ? ($bidding_detail->customer_released) : 0;
                    $pending_release = isset($bidding_detail->pending_release) ? ($bidding_detail->pending_release) : 0;

                    $tot_chit_value += $chit_value;
                    $tot_bidding_amount += $bidding_amount;
                    $tot_pending_due_amount += $pending_due_amount;
                    $tot_gst += $gst;
                    $tot_release_amount += $total_release_amount;
                    $tot_customer_released += $customer_released;
                    $tot_pending_release += $pending_release;
                    $i++;
                } 
                $grand_tot_chit_value += $tot_chit_value;
                $grand_tot_bidding_amount += $tot_bidding_amount;
                $grand_tot_pending_due_amount += $tot_pending_due_amount;
                $grand_tot_gst += $tot_gst;
                $grand_tot_release_amount += $tot_release_amount;
                $grand_tot_customer_released += $tot_customer_released;
                $grand_tot_pending_release += $tot_pending_release;

                $grand_tot_chit_value_format =0;
                $grand_tot_bidding_amount_format =0;
                $grand_tot_pending_due_amount_format =0;
                $grand_tot_gst_format =0;
                $grand_tot_release_amount_format =0;
                $grand_tot_customer_released_format =0;
                $grand_tot_pending_release_format =0;

                if($grand_tot_chit_value > 0)
                {   
                    $num = $grand_tot_chit_value;
                    $grand_tot_chit_value_format = $this->moneyFormatIndia($num);
                }
                if($grand_tot_bidding_amount > 0)
                {   
                    $num = $grand_tot_bidding_amount;
                    $grand_tot_bidding_amount_format = $this->moneyFormatIndia($num);  
                }
                if($grand_tot_pending_due_amount > 0)
                {   
                    $num = $grand_tot_pending_due_amount;
                    $grand_tot_pending_due_amount_format = $this->moneyFormatIndia($num);  
                }
                if($grand_tot_gst > 0)
                {   
                    $num = $grand_tot_gst;
                    $grand_tot_gst_format = $this->moneyFormatIndia($num);  
                }
                if($grand_tot_release_amount > 0)
                {   
                    $num = $grand_tot_release_amount;
                    $grand_tot_release_amount_format = $this->moneyFormatIndia($num);  
                }
                if($grand_tot_customer_released > 0)
                {   
                    $num = $grand_tot_customer_released;
                    $grand_tot_customer_released_format = $this->moneyFormatIndia($num);  
                }
                if($grand_tot_pending_release > 0)
                {   
                    $num = $grand_tot_pending_release;
                    $grand_tot_pending_release_format = $this->moneyFormatIndia($num);  
                }

                $response['grand_tot_chit_value'] = $grand_tot_chit_value_format;
                $response['grand_tot_bidding_amount'] = $grand_tot_bidding_amount_format;
                $response['grand_tot_pending_due_amount'] = $grand_tot_pending_due_amount_format;
                $response['grand_tot_gst'] = $grand_tot_gst_format;
                $response['grand_tot_release_amount'] = $grand_tot_release_amount_format;
                $response['grand_tot_customer_released'] = $grand_tot_customer_released_format;
                $response['grand_tot_pending_release'] = $grand_tot_pending_release_format;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $bidding_details;
				return response()->json($response,200);
			}
			else {
				$bidding_details = [];
                $response['grand_tot_chit_value'] = 0;
                $response['grand_tot_bidding_amount'] = 0;
                $response['grand_tot_pending_due_amount'] = 0;
                $response['grand_tot_gst'] = 0;
                $response['grand_tot_release_amount'] = 0;
                $response['grand_tot_customer_released'] = 0;
                $response['grand_tot_pending_release'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $bidding_details;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function store_commitment_payment(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
				$payment = new Payment();
				$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $payment->branch_id = isset($request->branch_id) ? $request->branch_id : '';
                $payment->group_id = 0;
                //$payment->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
                $payment->customer_id = $customer_id;
				  if(isset($request->payment_date)) {
                     $s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                     $payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                 }
                 else {
                     // $payment->payment_date = '0000-00-00';
                 }
                $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
				$payment->enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : 0;
                $payment->payment_for = "7";
                $payment->payment_type_id = isset($request->payment_type_id) ? $request->payment_type_id : 0;
                $payment->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
                $payment->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
                $payment->bank_branch_name = isset($request->bank_branch_name) ? $request->bank_branch_name : '';
                $payment->cheque_no = isset($request->cheque_no) ? $request->cheque_no : '';
				 if(isset($request->cheque_date)) {
                     $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                     $payment->cheque_date = date('Y-m-d',strtotime($s_date)); 
                 }
                 else {
                     // $payment->cheque_date = '0000-00-00';
                 }
                $payment->cheque_status = isset($request->cheque_status) ? $request->cheque_status : '';
                $payment->transaction_no = isset($request->transaction_no) ? $request->transaction_no : 0;
                // $payment->transaction_date = isset($request->transaction_date) ? $request->transaction_date : '0000-00-00';
				 if(isset($request->transaction_date)) {
                     $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                     $payment->transaction_date = date('Y-m-d',strtotime($s_date)); 
                 }
                 else {
                     // $payment->transaction_date = '0000-00-00';
                 }
				$payment->debit_to = isset($request->debit_to) ? $request->debit_to : '';
				$payment->created_by = isset($request->created_by) ? $request->created_by : 0;
                $payment->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2){
							$payment->status = isset($request->status) ? ($request->status) : 2;
						}
                if($payment->total_amount >0)
                {                    
    				if($payment->save()) {
    					$enrol_update = ChitDetail::find($request->enrollment_id);
    					$enrol_update->payment_month = isset($request->payment_month) ? $request->payment_month : '';
    					$enrol_update->total_payment_release = isset($request->total_payment_release) ? $request->total_payment_release : '';
    					$customer_released = $enrol_update->payment_released;
    					$enrol_update->payment_released = $request->total_amount + $customer_released;
    					$enrol_update->pending_released = $request->total_payment_release - $enrol_update->payment_released;
    					$enrol_update->save();
    					$response['status'] = 'Success';
    					$response['msg'] = \Lang::get('api.success_payment_added');
    					$response['data'] = $payment;
    					return response()->json($response,200);
    				}
                }
                else {
                    $response['status']="Error";
                    $response['msg']="Payment release amount Invalid";
                    return response()->json($response,200);
                }
			}
			catch(\Exception $e) { 
			
				$response['status'] = 'Error';
                $response['excep'] = $e;
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }
	
	public function delete_commitment_payment(Request $request) {
		try {
            if(isset($request->id)) {
                $payment = Payment::find($request->id); 
                $payment->deleted_by = $request->deleted_by;
                $payment->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($payment->save()) {
                    $payment->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_payment_deleted');
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
	
	public function update_commitment_payment(Request $request) {
        $validator = Validator::make($request->all(), [
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
                    $payment = Payment::find($request->id);
    				$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                    $payment->branch_id = isset($request->branch_id) ? $request->branch_id : '';
                    $payment->customer_id = isset($request->customer_id) ? $request->customer_id : '';
    				if(isset($request->payment_date)) {
                        $s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                        $payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $payment->payment_date = '0000-00-00';
                    }
                    $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
                    $payment->payment_for = "7";
                    $payment->payment_type_id = isset($request->payment_type_id) ? $request->payment_type_id : 0;
                    $payment->total_amount = isset($request->total_amount) ? $request->total_amount : 0;
                    $payment->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
                    $payment->bank_branch_name = isset($request->bank_branch_name) ? $request->bank_branch_name : '';
                    $payment->cheque_no = isset($request->cheque_no) ? $request->cheque_no : '';
				    if(isset($request->cheque_date)) {
                        $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $payment->cheque_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $payment->cheque_date = '0000-00-00';
                    }
                    $payment->cheque_status = isset($request->cheque_status) ? $request->cheque_status : '';
                    $payment->transaction_no = isset($request->transaction_no) ? $request->transaction_no : 0;
                    $payment->transaction_date = isset($request->transaction_date) ? $request->transaction_date : '0000-00-00';
    				if(isset($request->transaction_date)) {
                        $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $payment->transaction_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    else {
                        $payment->transaction_date = '0000-00-00';
                    }
    				$payment->debit_to = isset($request->debit_to) ? $request->debit_to : '';
                    $payment->status = isset($request->status) ? ($request->status) : 1;
                    $payment->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($payment->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_payment_updated');
                        $response['data'] = $payment;
                        return response()->json($response,200);
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

	public function get_commitment_payments(Request $request) {
        try {
            $cond = $request->conditions; 
            $payments = Payment::where($cond)->orderBy('id','desc')->get();
            foreach($payments as $payment) {
                $payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
				$payment->group_info = ($payment->group_det) ? ($payment->group_det) : [];
				$slab_id = ChitDetail::where('id',$payment->enrollment_id)->pluck('slab_id');
				$payment->slab_name = SlabDue::where('id',$slab_id)->pluck('slab_name');
				$payment->chit_info = ($payment->enrol_det) ? ($payment->chit_info) : [];
			    $payment->customer_info = ($payment->customer_det) ? ($payment->customer_info) : [];
				
				$payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';   
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';  
			    $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';   					
               
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_commitment_payments(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $payments = Payment::where('tenant_id',$request->tenant_id)->where('payment_for','=','7')->orderBy('id','desc')->get();
            }
            else {
                $payments = Payment::where('payment_for','=','7')->orderBy('id','desc')->get();
            }
            $i=1;
            foreach($payments as $payment) {
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
                $payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
                $payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';
                $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';
                $payment->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function get_commitment_payment_report(Request $request) {
		try { 
		    $cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';
			$cond['payment_for'] = '7';         	
        	if(isset($request->customer_id)){
        		$cond['customer_id'] = $request->customer_id; 
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
                    $payments = Payment::where($cond)->where($cond1)->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = Payment::where($cond)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = Payment::where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $payments = Payment::where($cond)->where($cond1)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0)
                {
                    $payments = Payment::where($cond)->whereBetween('payment_date',[$start_date,$end_date])->get();
                }
            } 
			$i=1;	
            if(count($payments)>0)
            {
                $grand_chit_value = 0;
                $tot_chit = 0;
                $grand_paid_amt = 0;
                $tot_paid_amt = 0;
    			foreach($payments as $payment) {
    				$payment->sno = $i;
    				$payment->tenant_info = isset($payment->tenant_det) ? ($payment->tenant_det) : [];
    				$payment->branch_name = isset($payment->branch_det) ? ($payment->branch_det->branch_name) : [];
    				$payment->payment_month = isset($payment->enrol_det) ? ($payment->enrol_det->payment_month) : [];
    				$payment->bank_name = isset($payment->bank_det) ? ($payment->bank_det->bank_name) : [];
    				$payment->bank_branch_name = isset($payment->bank_det) ? ($payment->bank_det->bank_branch_name) : [];
    				$payment->customer_name = isset($payment->customer_det) ? ($payment->customer_det->name) : [];
    				$payment->payment_type_name = isset($payment->payment_type_det) ? ($payment->payment_type_det->payment_name) : [];
    				// $slab_id = ChitDetail::where('id',$payment->enrollment_id)->pluck('slab_id');
    				// $payment->slab_name = SlabDue::where('id',$slab_id)->pluck('slab_name');
    				$chits = ChitDetail::where('id',$payment->enrollment_id)->get();
    				$slab_name = SlabDue::where('id',$chits[0]->slab_id)->get();
    				$schem_id = Scheme::where('id',$chits[0]->scheme_id)->get();
    				$payment->slab_name = \App\Models\ChitDetail::formatSlabWithArNo(isset($slab_name[0]->slab_name) ? ($slab_name[0]->slab_name) : '', $chits[0]->ar_no ?? null);
    				$payment->ar_no = $chits[0]->ar_no ?? null;
    				$chit_value = isset($schem_id[0]->chit_value) ? ($schem_id[0]->chit_value) : 0;
                    $payment->chit_value = $chit_value;
    				$payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';
    				if($payment->status == 1 )
    				{
    					$payment->status_name = "Active";
    				}	
    				else if($payment->status == 2 )
    				{
    					$payment->status_name = "Pending";
    				}		
    				else if($payment->status == 3 )
    				{
    					$payment->status_name = "Return";
    				}	
    				else if($payment->status == 0 )
    				{
    					$payment->status_name = "In-Active";
    				}else
    				{
    					$payment->status_name = "-";
    				}   	
                    $total_amount = isset($payment->total_amount) ? ($payment->total_amount) : 0;
                    $tot_paid_amt += $total_amount;
                    $tot_chit += $chit_value;
    				$i++;
                }
                $grand_chit_value +=$tot_chit;
                $grand_paid_amt +=$tot_paid_amt;

                $grand_paid_amt_format =0;
                $grand_chit_value_format =0;

                if($grand_paid_amt > 0)
                {   
                    $num = $grand_paid_amt;
                    $grand_paid_amt_format = $this->moneyFormatIndia($num);  
                }
                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                }
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['grand_paid_amt'] = $grand_paid_amt_format;
    			$response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $payments;
                return response()->json($response,200);
            }
             else {
                $payments = [];
                $response['grand_chit_value'] = 0;
                $response['grand_paid_amt'] = 0;
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.no_data_found');
                $response['data'] = $payments;
                return response()->json($response, 200);    
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
			$response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
	
	public function get_commitment_pending_report(Request $request) {
		try { 
		    $cond = [];
            $cond1 = [];
        	$end_date='';
            $start_date = '';
			$cond['chit_type'] = '3';         	
        	if(isset($request->customer_id)){
        		$cond['customer_id'] = $request->customer_id; 
        	}
			if(isset($request->enrollment_id)){
        		$cond['id'] = $request->enrollment_id; 
        	}
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $pendings = ChitDetail::where($cond)->where($cond1)->where('payment_month','!=',0)->where('pending_released','!=',0)->get();
            }
            if($request->branch_id == 0)
            {
                $pendings = ChitDetail::where($cond)->where('payment_month','!=',0)->where('pending_released','!=',0)->get();
            }
			$i=1;
			if(count($pendings)>0) 
            {   
                $grand_chit_value = 0;
                $tot_chit = 0;
                $grand_to_be_pay = 0;
                $tot_to_be_pay = 0;
                $grand_pending = 0;
                $tot_pending = 0;
                $grand_released = 0;
                $tot_released = 0;
                foreach($pendings as $pending) 
                {
                    $pending->sno = $i;
                    $pending->tenant_info = isset($pending->tenant_det) ? ($pending->tenant_det) : [];
                    $pending->branch_name = isset($pending->branch_det->branch_name) ? ($pending->branch_det->branch_name) : [];
                    $chit_value = isset($pending->scheme_det->chit_value) ? ($pending->scheme_det->chit_value) : [];
                    $pending->chit_value = $chit_value;
    				$pending->customer_name = isset($pending->customer_det->name) ? ($pending->customer_det->name) : [];
    				$slab_name = SlabDue::where('id',$pending->slab_id)->get();
    				$pending->slab_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name[0]->slab_name ?? '', $pending->ar_no ?? null);
                    $pending->payment_type_name = '';
                    if(($pending->payment_type) == 1) {
                        $pending->payment_type_name = 'Daily';
                    }
                    else if(($pending->payment_type) == 2) {
                        $pending->payment_type_name = 'Weekly';
                    }
                    else if(($pending->payment_type) == 3) {
                        $pending->payment_type_name = 'Monthly';
                    }   
                    $total_payment_release = isset($pending->total_payment_release) ? ($pending->total_payment_release) : 0;
                    $pending_released = isset($pending->pending_released) ? ($pending->pending_released) : 0;
                    $payment_released = isset($pending->payment_released) ? ($pending->payment_released) : 0;
                    $tot_to_be_pay += $total_payment_release;
                    $tot_pending += $pending_released;
                    $tot_released += $payment_released;
                    $tot_chit += $chit_value;
                    $i++;
                } 
                $grand_chit_value +=$tot_chit;
                $grand_to_be_pay +=$tot_to_be_pay;
                $grand_pending +=$tot_pending;
                $grand_released +=$tot_released;

                $grand_chit_value_format =0;
                $grand_to_be_pay_format =0;
                $grand_pending_format =0;
                $grand_released_format =0;

                if($grand_chit_value > 0)
                {   
                    $num = $grand_chit_value;
                    $grand_chit_value_format = $this->moneyFormatIndia($num);  
                }
                 if($grand_to_be_pay > 0)
                {   
                    $num = $grand_to_be_pay;
                    $grand_to_be_pay_format = $this->moneyFormatIndia($num);  
                }
                 if($grand_pending > 0)
                {   
                    $num = $grand_pending;
                    $grand_pending_format = $this->moneyFormatIndia($num);  
                }
                 if($grand_released > 0)
                {   
                    $num = $grand_released;
                    $grand_released_format = $this->moneyFormatIndia($num);  
                }
                $response['grand_chit_value'] = $grand_chit_value_format;
                $response['grand_to_be_pay'] = $grand_to_be_pay_format;
                $response['grand_pending'] = $grand_pending_format;
                $response['grand_released'] = $grand_released_format;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $pendings;
				return response()->json($response,200);
            }
    		else {
    			    $pendings = [];
                    $response['grand_chit_value'] = 0;
                    $response['grand_to_be_pay'] = 0;
                    $response['grand_pending'] = 0;
                    $response['grand_released'] = 0;
                    $response['status'] = 'Success';
    				$response['msg'] = \Lang::get('api.no_data_found');
    				$response['data'] = $pendings;
    		        return response()->json($response, 200);	
    		}
		}
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function day_closing_reports(Request $request)
    { 		
        try
        { 
            $response_data = [];
            if(isset($request->start_date)) 
            {           
                $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                $start_date = date('Y-m-d',strtotime($s_date)); 
               //$start_date = $request->start_date;
            }
            $cond = array('branch_id'=>$request->branch_id);
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
			$before_adv_cash = BeforeEnrolReceipt::selectRaw('*,sum(amount) as total_adv')->where('payment_type_id','=',1)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['before_advance_cash'] = isset($before_adv_cash[0]->total_adv) ? (strval(round($before_adv_cash[0]->total_adv))) : "0"; 
			$other_receipt_cash = OtherCharge::selectRaw('*,sum(amount) as total_received')->where('payment_type_id','=',1)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['other_charges_cash'] =isset($other_receipt_cash[0]->total_received) ? (strval(round($other_receipt_cash[0]->total_received))) : "0";

            // cheque received(Pending cheque)
            $cheque_received = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',2)->where(array('receipt_date'=>$start_date))->where($cond)->get(); 
            $response_data['cheque_received_collection'] = isset($cheque_received[0]->total_received) ? (strval(round($cheque_received[0]->total_received))) : "0";
            $response_data['cheque_penalty'] = isset($cheque_received[0]->total_penalty) ? (strval(round($cheque_received[0]->total_penalty))) : "0";
            $adv_cheque = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',2)->where(array('receipt_date'=>$start_date))->where($cond)->get();
            $response_data['advance_cheque'] = isset($adv_cheque[0]->total_adv) ? (strval(round($adv_cheque[0]->total_adv))) : "0"; 
			$before_adv_cheque = BeforeEnrolReceipt::selectRaw('*,sum(amount) as total_adv')->where('payment_type_id','=',2)->where(array('received_date'=>$start_date))->where($cond)->get();
            $response_data['before_advance_cheque'] = isset($before_adv_cheque[0]->total_adv) ? (strval(round($before_adv_cheque[0]->total_adv))) : "0"; 
            //$response_data['other_charges_cheque'] = "0";
			$other_receipt_cash = OtherCharge::selectRaw('*,amount as total_received')->where('payment_type_id','=',2)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['other_charges_cheque'] =isset($other_receipt_cash[0]->total_received) ? (strval(round($other_receipt_cash[0]->total_received))) : "0";

            // cheque cleared
            $cheque_cleared = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get(); 
            $response_data['cheque_cleared_collection'] = isset($cheque_cleared[0]->total_received) ? (strval(round($cheque_cleared[0]->total_received))) : "0";
            $response_data['cheque_cleared_penalty'] = isset($cheque_cleared[0]->total_penalty) ? (strval(round($cheque_cleared[0]->total_penalty))) : "0";  
            $adv_cheque_cleared = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['advance_cleared_cheque'] = isset($adv_cheque_cleared[0]->total_adv) ? (strval(round($adv_cheque_cleared[0]->total_adv))) : "0";  
			$before_adv_cheque_cleared = BeforeEnrolReceipt::selectRaw('*,sum(amount) as total_adv')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['before_advance_cleared_cheque'] = isset($before_adv_cheque_cleared[0]->total_adv) ? (strval(round($before_adv_cheque_cleared[0]->total_adv))) : "0";			

            //dd
            $dd = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',3)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['dd_collection'] = isset($dd[0]->total_received) ? (strval(round($dd[0]->total_received))) : "0";
            $response_data['dd_penalty'] = isset($dd[0]->total_penalty) ? (strval(round($dd[0]->total_penalty))) : "0";
            $adv_dd = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',3)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['advance_dd'] = isset($adv_dd[0]->total_adv) ? (strval(round($adv_dd[0]->total_adv))) : "0";
			$before_adv_dd = BeforeEnrolReceipt::selectRaw('*,sum(amount) as total_adv')->where('payment_type_id','=',3)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['before_advance_dd'] = isset($before_adv_dd[0]->total_adv) ? (strval(round($before_adv_dd[0]->total_adv))) : "0";
           // $response_data['other_charges_dd'] = "0";    
			$other_receipt_cash = OtherCharge::selectRaw('*,amount as total_received')->where('payment_type_id','=',3)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['other_charges_dd'] =isset($other_receipt_cash[0]->total_received) ? (strval(round($other_receipt_cash[0]->total_received))) : "0";			

            //neft
            $neft = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty)as total_penalty')->where('payment_type_id','=',4)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['neft_collection'] = isset($neft[0]->total_received) ? (strval(round($neft[0]->total_received))) : "0";
            $response_data['neft_penalty'] = isset($neft[0]->total_penalty) ? (strval(round($neft[0]->total_penalty))) : "0";
            $adv_neft = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('payment_type_id','=',4)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['advance_neft'] = isset($adv_neft[0]->total_adv) ? (strval(round($adv_neft[0]->total_adv))) : "0";
			$before_adv_neft = BeforeEnrolReceipt::selectRaw('*,sum(amount) as total_adv')->where('payment_type_id','=',4)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['before_advance_neft'] = isset($before_adv_neft[0]->total_adv) ? (strval(round($before_adv_neft[0]->total_adv))) : "0";
            //$response_data['other_charges_neft'] = "0";
			$other_receipt_cash = OtherCharge::selectRaw('*,amount as total_received')->where('payment_type_id','=',4)->where(array('received_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['other_charges_neft'] =isset($other_receipt_cash[0]->total_received) ? (strval(round($other_receipt_cash[0]->total_received))) : "0";

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
                        $response_data['other_branch_cash_collection'] = 0;
                        $response_data['other_branch_cash_penalty'] = strval(round($response_data['other_branch_cash_penalty'] + $total_penalty_cash));
                    }

                    if($rcpts->payment_type_id == 2)
                    {
                        $total_receipts_cheque = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
                         $total_penalty_cheque = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
                        $response_data['other_branch_cheque_collection'] = strval(round($response_data['other_branch_cheque_collection'] + $total_receipts_cheque));
                        $response_data['other_branch_cheque_penalty'] = strval(round($response_data['other_branch_cheque_penalty'] + $total_penalty_cheque ));
                    }

                    if($rcpts->payment_type_id == 3 && $rcpts->payment_type_id==1)
                    {
                        $total_receipts_dd = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
                         $total_penalty_dd = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
                        $response_data['other_branch_dd_collection'] = strval(round($response_data['other_branch_dd_collection'] + $total_receipts_dd));
                        $response_data['other_branch_dd_penalty'] = strval(round($response_data['other_branch_dd_penalty'] + $total_penalty_dd ));
                    }  

                    if($rcpts->payment_type_id == 4 && $rcpts->payment_type_id==1)
                    {
                        $total_receipts_neft = isset($rcpts->received_amount) ? ($rcpts->received_amount) : "0";
                         $total_penalty_neft = isset($rcpts->penalty) ? ($rcpts->penalty) : "0";
                        $response_data['other_branch_neft_collection'] = strval(round($response_data['other_branch_neft_collection'] + $total_receipts_neft));
                        $response_data['other_branch_neft_penalty'] = strval(round($response_data['other_branch_neft_penalty'] + $total_penalty_neft ));
                    }  

                    if($rcpts->payment_type_id == 5 && $rcpts->payment_type_id==1)
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
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
    Public function accounts_report(Request $request)
    {
        try
        {
            $cond = [];            
            $start_date = '';
            $end_date ='';                     
            if(isset($request->start_date))
            {
                $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                $start_date = date('Y-m-d',strtotime($s_date)); 
            }
            if(isset($request->end_date))
            {
                $e_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
                $end_date = date('Y-m-d',strtotime($e_date)); 
            }
            $cond = array('branch_id'=>$request->branch_id,'tenant_id'=>$request->tenant_id);            
            $array = array();

             $interval = new DateInterval('P1D');
             $realEnd = new DateTime($end_date);
             $realEnd->add($interval); 
            
             $period = new DatePeriod(new DateTime($start_date), $interval, $realEnd);
             $response_data =[]; 
			 $i=1;
            foreach($period as $key)
            {  
                $format = 'Y-m-d';
                $from = $key->format($format);   
                $lists = []; 
                $lists['sno'] = $i; 
                $lists['receipt_date'] = ($from!='') ? date('d/m/Y',strtotime($from)) : '0000-00-00';
                $branch = Branch::where('id',$request->branch_id)->get();
                $lists['branch_name'] = $branch[0]->branch_name;
                $lists['cash_collection'] = 0;
                $lists['cash_penalty'] =0;
                $lists['cheque_collection'] =0;
                $lists['cheque_penalty'] =0;
                $lists['dd_collection'] =0;
                $lists['dd_penalty'] =0;
                $lists['neft_collection'] =0;
                $lists['neft_penalty'] =0;
                $lists['card_collection'] =0;
                $lists['card_penalty'] =0;
                $lists['bp_adjust_collection'] =0;
                $lists['bp_adjust_penalty']=0;
                $lists['adv_adjust_collection']=0;
                $lists['adv_adjust_penalty']=0;
                $lists['advance_cash_collection'] = 0;
                $lists['advance_cheque_collection'] =0;
                $lists['advance_dd_collection'] =0;
                $lists['advance_neft_collection'] =0;
                $lists['advance_card_collection'] =0;
                $lists['cheque_clear_date'] = "";
                $lists['cheque_cleared_collection'] =0;
                $lists['cheque_cleared_penalty'] =0;
                $lists['adv_cheque_clear_date'] = "";
                $lists['adv_cheque_cleared_collection'] =0;
                $lists['payment_date'] = "";
                $lists['payment_cash'] = 0;
                $lists['payment_cheque'] =0;
                $lists['payment_dd'] =0;
                $lists['payment_neft'] =0;
                $lists['payment_card'] =0;

                $receipt = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty) as total_penalty')->where($cond)->where('receipt_date',$from)->groupBy('payment_type_id')->groupBy('receipt_date')->get();                
                foreach($receipt as $rcpt)
                {                                        
                    if($rcpt->payment_type_id ==1 && $rcpt->status ==1)
                    {   
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['cash_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['cash_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;
                    }
                    if($rcpt->payment_type_id ==2)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['cheque_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['cheque_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;
                    }
                    if($rcpt->payment_type_id ==3 && $rcpt->status ==1)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['dd_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['dd_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;
                    }
                    if($rcpt->payment_type_id ==4 && $rcpt->status ==1)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['neft_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['neft_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;
                    }
                    if($rcpt->payment_type_id ==5 && $rcpt->status ==1)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['card_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['card_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;
                    }
                    if($rcpt->payment_type_id ==6 && $rcpt->status ==1)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['bp_adjust_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['bp_adjust_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;                   
                    }
                    if($rcpt->payment_type_id ==7 && $rcpt->status ==1)
                    {
                        $lists['receipt_date'] = ($rcpt->receipt_date!=0) ? date('d/m/Y',strtotime($rcpt->receipt_date)) :  '0000-00-00'; 
                        $lists['adv_adjust_collection'] = isset($rcpt->total_received) ? ($rcpt->total_received) : 0;
                        $lists['adv_adjust_penalty'] = isset($rcpt->total_penalty) ? ($rcpt->total_penalty) : 0;                    
                    }                    
                }            
                $advances = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_advance')->where($cond)->where('receipt_date',$from)->groupBy('payment_type_id')->groupBy('receipt_date')->get();
                foreach($advances as $adv) 
                {                    
                    if($adv->payment_type_id ==1 && $adv->status ==1)
                    {
                        $lists['advance_cash_collection'] = isset($adv->total_advance) ? ($adv->total_advance) : 0;
                        $lists['receipt_date'] = ($adv->receipt_date!=0) ? date('d/m/Y',strtotime($adv->receipt_date)) :  '0000-00-00'; 
                    }
                    if($adv->payment_type_id ==2)
                    {
                        $lists['advance_cheque_collection'] = isset($adv->total_advance) ? ($adv->total_advance) : 0;
                        $lists['receipt_date'] = ($adv->receipt_date!=0) ? date('d/m/Y',strtotime($adv->receipt_date)) :  '0000-00-00'; 
                    }
                    if($adv->payment_type_id ==3 && $adv->status ==1)
                    {
                        $lists['advance_dd_collection'] = isset($adv->total_advance) ? ($adv->total_advance) : 0;
                        $lists['receipt_date'] = ($adv->receipt_date!=0) ? date('d/m/Y',strtotime($adv->receipt_date)) :  '0000-00-00'; 
                    }
                    if($adv->payment_type_id ==4 && $adv->status ==1)
                    {
                        $lists['advance_neft_collection'] = isset($adv->total_advance) ? ($adv->total_advance) : 0;
                        $lists['receipt_date'] = ($adv->receipt_date!=0) ? date('d/m/Y',strtotime($adv->receipt_date)) :  '0000-00-00'; 
                    }
                    if($adv->payment_type_id ==5 && $adv->status ==1)
                    {
                        $lists['advance_card_collection'] = isset($adv->total_advance) ? ($adv->total_advance) : 0;
                        $lists['receipt_date'] = ($adv->receipt_date!=0) ? date('d/m/Y',strtotime($adv->receipt_date)) :  '0000-00-00'; 
                    }
                }

                $rcpt_clear_cheque = Receipt::selectRaw('*,sum(received_amount) as total_received,sum(penalty) as total_penalty')->where($cond)->where('cheque_clear_return_date',$from)->where(array('payment_type_id'=>2,'status'=>1))->groupBy('payment_type_id')->groupBy('cheque_clear_return_date')->get();
                foreach($rcpt_clear_cheque as $rcpt_clear)
                {
                    $lists['receipt_date'] = ($rcpt_clear->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($rcpt_clear->cheque_clear_return_date)) :  '0000-00-00'; 
                    $lists['cheque_cleared_collection'] = isset($rcpt_clear->total_received) ? ($rcpt_clear->total_received) : 0;
                    $lists['cheque_cleared_penalty'] = isset($rcpt_clear->total_penalty) ? ($rcpt_clear->total_penalty) : 0;                   
                }

                $adv_clear_cheque = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv_cleared')->where($cond)->where('cheque_clear_return_date',$from)->where(array('payment_type_id'=>2,'status'=>1))->groupBy('payment_type_id')->groupBy('cheque_clear_return_date')->get();
                foreach($adv_clear_cheque as $adv_clear)
                {
                    $lists['receipt_date'] = ($adv_clear->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($adv_clear->cheque_clear_return_date)) :  '0000-00-00'; 
                    $lists['adv_cheque_cleared_collection'] = isset($adv_clear->total_adv_cleared) ? ($adv_clear->total_adv_cleared) : 0;                   
                }

                $payment = Payment::selectRaw('*,sum(total_amount) as total_payment')->where(array('status'=>1))->where('payment_date',$from)->where($cond)->groupBy('payment_type_id')->groupBy('payment_date')->get();
                foreach($payment as $pamt)
                {                    
                    if($pamt->payment_type_id ==1 && $pamt->status ==1)
                    {
                        $lists['payment_cash'] = isset($pamt->total_payment) ? ($pamt->total_payment) : 0;
                        $lists['receipt_date'] = ($pamt->payment_date!=0) ? date('d/m/Y',strtotime($pamt->payment_date)) :  '0000-00-00'; 
                    }
                    if($pamt->payment_type_id ==2 && $pamt->status ==1)
                    {
                        $lists['payment_cheque'] = isset($pamt->total_payment) ? ($pamt->total_payment) : 0;
                        $lists['receipt_date'] = ($pamt->payment_date!=0) ? date('d/m/Y',strtotime($pamt->payment_date)) :  '0000-00-00'; 
                    }
                    if($pamt->payment_type_id ==3 && $pamt->status ==1)
                    {
                        $lists['payment_dd'] = isset($pamt->total_payment) ? ($pamt->total_payment) : 0;
                        $lists['receipt_date'] = ($pamt->payment_date!=0) ? date('d/m/Y',strtotime($pamt->payment_date)) :  '0000-00-00'; 
                    }
                    if($pamt->payment_type_id ==4 && $pamt->status ==1)
                    {
                        $lists['payment_neft'] = isset($pamt->total_payment) ? ($pamt->total_payment) : 0;
                        $lists['receipt_date'] = ($pamt->payment_date!=0) ? date('d/m/Y',strtotime($pamt->payment_date)) :  '0000-00-00'; 
                    }
                    if($pamt->payment_type_id ==5 && $pamt->status ==1)
                    {
                        $lists['payment_card'] = isset($pamt->total_payment) ? ($pamt->total_payment) : 0;
                        $lists['receipt_date'] = ($pamt->payment_date!=0) ? date('d/m/Y',strtotime($pamt->payment_date)) :  '0000-00-00'; 
                    }
                }
                array_push($response_data,$lists);
                $i++;
            }
			$response['status'] = "Success";
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }

    }
	
	Public function customer_ledger_payment_details(Request $request) {
        try { 
            // enrollment_id
            $cond = $request->conditions;
            $bidding_details = BiddingDetail::where($cond)->get();
            $bid_det =[];
            foreach($bidding_details as $bidding_detail) {
                $deductions = [];
                $deductions['net_payable'] =0;
                $deductions['total_release_amount'] = 0;
                $deductions['gst'] = $bidding_detail->gst;
                $deductions['bidding_amount'] = $bidding_detail->bidding_amount - $bidding_detail->commission_amount;
                $deductions['commission_amount'] = $bidding_detail->commission_amount;
                $deductions['pending_due_amount'] = $bidding_detail->pending_due_amount + $bidding_detail->total_deduction;
                $deductions['net_payable'] = $deductions['bidding_amount'] + $deductions['commission_amount'] + $deductions['pending_due_amount'] + $deductions['gst'];
                $deductions['total_release_amount'] = $bidding_detail->chit_value - $deductions['net_payable'];
                array_push($bid_det,$deductions);
                $bidding_detail->deductions = $bid_det;
                
                $payment_details = Payment::where('bidding_id',$bidding_detail->id)->get();
                $payment_det =[];
                $payment_amount_total = 0;
                $payment['total_amount'] =0;
                foreach($payment_details as $payment_detail) {
                    $payment = [];
                    $payment['total_amount'] = $payment_detail->total_amount;
                    $payment_method = PaymentType::where('id',$payment_detail->payment_type_id)->pluck('payment_name');
                    $payment['payment_type'] = $payment_method[0]; 
					$payment_amount_total = $payment_amount_total + $payment_detail->total_amount;
                    array_push($payment_det,$payment);
                };   
                $bidding_detail->payment_det = $payment_det;
                $bidding_detail->to_be_collected = $deductions['total_release_amount'] - $payment_amount_total;
            };
            
            
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $bidding_details;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cheque_return_commitment_payment(Request $request)
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
                $payment = Payment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                } 
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $payment->status = isset($request->status) ? ($request->status) : 3;
                if($payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $payment;
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
	
	public function cheque_cleared_commitment_payment(Request $request)
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
                $payment = Payment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $payment->status = isset($request->status) ? ($request->status) : 1;
                if($payment->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $payment;
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
	
	public function cheque_return_payment_release(Request $request)
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
                $payment = Payment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                } 
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $payment->status = isset($request->status) ? ($request->status) : 3;
                if($payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $payment;
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
	
	public function cheque_cleared_payment_release(Request $request)
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
                $payment = Payment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $payment->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $payment->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $payment->status = isset($request->status) ? ($request->status) : 1;
                if($payment->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $payment;
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

    public function loan_day_closing_reports(Request $request)
    {       
        try
        { 
            $response_data = [];
            if(isset($request->start_date)) 
            {           
                $s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day'])); 
                $start_date = date('Y-m-d',strtotime($s_date)); 
               //$start_date = $request->start_date;
            }
            $cond = array('branch_id'=>$request->branch_id);
            //cash
            $response_data['start_date'] = $start_date;
            $response_data['branch_info'] = '';
            if(isset($request->branch_id))
            {
                $branch = Branch::where('id',$request->branch_id)->get();
                $response_data['branch_info'] = $branch[0]->branch_name;
            }
            $cash = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',1)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['cash_collection'] = isset($cash[0]->total_received) ? (strval(round($cash[0]->total_received))) : "0";
            $response_data['cash_interest'] = isset($cash[0]->total_interest_amt) ? (strval(round($cash[0]->total_interest_amt))) : "0";            

            // cheque received(Pending cheque)
            $cheque_received = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',2)->where(array('receipt_date'=>$start_date))->where($cond)->get(); 
            $response_data['cheque_received_collection'] = isset($cheque_received[0]->total_received) ? (strval(round($cheque_received[0]->total_received))) : "0";
            $response_data['cheque_interest'] = isset($cheque_received[0]->total_interest_amt) ? (strval(round($cheque_received[0]->total_interest_amt))) : "0";

            // cheque cleared
            $cheque_cleared = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',2)->where(array('cheque_clear_return_date'=>$start_date,'status'=>1))->where($cond)->get(); 
            $response_data['cheque_cleared_collection'] = isset($cheque_cleared[0]->total_received) ? (strval(round($cheque_cleared[0]->total_received))) : "0";
            $response_data['cheque_cleared_interest'] = isset($cheque_cleared[0]->total_interest_amt) ? (strval(round($cheque_cleared[0]->total_interest_amt))) : "0";  

            //dd
            $dd = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',3)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['dd_collection'] = isset($dd[0]->total_received) ? (strval(round($dd[0]->total_received))) : "0";
            $response_data['dd_interest'] = isset($dd[0]->total_interest_amt) ? (strval(round($dd[0]->total_interest_amt))) : "0";

            //neft
            $neft = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',4)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['neft_collection'] = isset($neft[0]->total_received) ? (strval(round($neft[0]->total_received))) : "0";
            $response_data['neft_interest'] = isset($neft[0]->total_interest_amt) ? (strval(round($neft[0]->total_interest_amt))) : "0";

            //card
            $card = LoanReceipt::selectRaw('*,sum(amount) as total_received,sum(interest_amount)as total_interest_amt')->where('payment_type_id','=',5)->where(array('receipt_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['card_collection'] = isset($card[0]->total_received) ? (strval(round($card[0]->total_received))) : "0";
            $response_data['card_interest'] = isset($card[0]->total_interest_amt) ? (strval(round($card[0]->total_interest_amt))) : "0";

            //cash_payment
            $cash_payment = LoanDetail::selectRaw('*,sum(loan_amount) as total_payment')->where('payment_type_id','=',1)->where(array('loan_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['cash_payment'] = isset($cash_payment[0]->total_payment) ? (strval(round($cash_payment[0]->total_payment))) : "0";
            //cheque_payment
            $cheque_payment = LoanDetail::selectRaw('*,sum(loan_amount) as total_payment')->where('payment_type_id','=',2)->where(array('loan_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['cheque_payment'] = isset($cheque_payment[0]->total_payment) ? (strval(round($cheque_payment[0]->total_payment))) : "0";
            //dd_payment
            $dd_payment = LoanDetail::selectRaw('*,sum(loan_amount) as total_payment')->where('payment_type_id','=',3)->where(array('loan_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['dd_payment'] = isset($dd_payment[0]->total_payment) ? (strval(round($dd_payment[0]->total_payment))) : "0";
            //neft_payment
            $neft_payment = LoanDetail::selectRaw('*,sum(loan_amount) as total_payment')->where('payment_type_id','=',4)->where(array('loan_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['neft_payment'] = isset($neft_payment[0]->total_payment) ? (strval(round($neft_payment[0]->total_payment))) : "0";
            //card_payment
            $card_payment = LoanDetail::selectRaw('*,sum(loan_amount) as total_payment')->where('payment_type_id','=',5)->where(array('loan_date'=>$start_date,'status'=>1))->where($cond)->get();
            $response_data['card_payment'] = isset($card_payment[0]->total_payment) ? (strval(round($card_payment[0]->total_payment))) : "0";                        

            // Total Amount
            $response_data['total_cash'] = strval(round($response_data['cash_collection']+$response_data['cash_interest']));

            $response_data['total_cheque_left'] = strval(round($response_data['cheque_received_collection']+$response_data['cheque_interest']));

            $response_data['total_cheque_right'] = strval(round($response_data['cheque_cleared_collection']+$response_data['cheque_cleared_interest']));

            $response_data['total_dd'] = strval(round($response_data['dd_collection']+$response_data['dd_interest']));

            $response_data['total_neft'] = strval(round($response_data['neft_collection']+$response_data['neft_interest']));

            $response_data['total_card'] = strval(round($response_data['card_collection']+$response_data['card_interest']));

            $response_data['total_payment'] = strval(round($response_data['cash_payment']+$response_data['cheque_payment']+$response_data['dd_payment']+$response_data['neft_payment'] +$response_data['card_payment']));                

            $response['status'] = "Success";
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
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
    
    public function cheque_issue_report(Request $request) {
        try {
            $cond = [];
            $cond1 =[];
            $start_date = '';
            $end_date = '';
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}						
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id; 
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id; 
			}
			$cond['payment_type_id'] = '2';
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
					
					$receipts=Payment::where($cond)->where('status',2)->get();
					
					
				}
				if($request->branch_id ==0)
				{
					$receipts=Payment::where($cond)->where('status',2)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=Payment::where($cond)->where($cond1)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=Payment::where($cond)->where('payment_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=Payment::where($cond)->where($cond1)->whereBetween('payment_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=Payment::where($cond)->whereBetween('payment_date',[$start_date,$end_date])->get();
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
				$receipt->group_name = isset($receipt->group_det) ? ($receipt->group_det->group_name) : '';
				$receipt->received_amount = isset($receipt->total_amount) ? ($receipt->total_amount) : '';
				$receipt->cheque_no = isset($receipt->cheque_no) ? ($receipt->cheque_no) : '';
				$receipt->cheque_date = isset($receipt->cheque_date) ? ($receipt->cheque_date) : '';
				$receipt->payment_date = ($receipt->payment_date!=0) ? date('d/m/Y',strtotime($receipt->payment_date)) : '';
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
			}	
            	   
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

    public function gst_voucher(Request $request) {
		try {
            
				 $cond = [];
            $start_date = null;
            $end_date = null;
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
			}
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}				
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}             
                
                $payments = Payment::where($cond)->whereBetween('payment_date',[$start_date,$end_date])->orderBy('id','desc')->get();
           
            $i=1;
            foreach($payments as $payment) {
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
				$payment->branch_name = ($payment->branch_det) ? ($payment->branch_det->branch_name) : [];
				$payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
				$payment->customer_name = ($payment->customer_det) ? ($payment->customer_det->name) : [];
				$payment->mobile_no = ($payment->customer_det) ? ($payment->customer_det->mobile_no) : [];
				$payment->chit_value = ($payment->auction_det) ? ($payment->auction_det->chit_value) : [];
				$payment->bidding_amount = ($payment->auction_det) ? ($payment->auction_det->bidding_amount) : [];
				$payment->total_release_amount = ($payment->auction_det) ? ($payment->auction_det->total_release_amount) : [];
				$payment->gst_amount = ($payment->auction_det) ? ($payment->auction_det->gst) : [];
                $payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';
                $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';
                $payment->sno = $i;
                $i++;
            }
			if($request->id >0){
				$payments = Payment::where('id',$request->id)->get();
           
            $i=1;
            foreach($payments as $payment) {
                $payment->branch_info = ($payment->branch_det) ? ($payment->branch_det) : [];
				$payment->branch_name = ($payment->branch_det) ? ($payment->branch_det->branch_name) : [];
				$payment->customer_info = ($payment->customer_det) ? ($payment->customer_det) : [];
				$payment->customer_name = ($payment->customer_det) ? ($payment->customer_det->name) : [];
				$payment->mobile_no = ($payment->customer_det) ? ($payment->customer_det->mobile_no) : [];
				$payment->chit_value = ($payment->auction_det) ? ($payment->auction_det->chit_value) : [];
				$payment->bidding_amount = ($payment->auction_det) ? ($payment->auction_det->bidding_amount) : [];
				$payment->total_release_amount = ($payment->auction_det) ? ($payment->auction_det->total_release_amount) : [];
				$payment->gst_amount = ($payment->auction_det) ? ($payment->auction_det->gst) : [];
                $payment->payment_date = ($payment->payment_date!=0) ? date('d/m/Y',strtotime($payment->payment_date)) : '';
                $payment->cheque_date = ($payment->cheque_date!=0) ? date('d/m/Y',strtotime($payment->cheque_date)) : '';
                $payment->transaction_date = ($payment->transaction_date!=0) ? date('d/m/Y',strtotime($payment->transaction_date)) : '';
                $payment->sno = $i;
                $i++;
            }
			}
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $payments;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }

    public function store_bid_advance(Request $request)
    {
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
				$payment = new Payment();
				$payment->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $payment->branch_id = isset($request->branch_id) ? $request->branch_id : '';
				$group_id = ChitDetail::where('id','=',$request->enrollment_id)->pluck('group_id');
				$payment->group_id = $group_id[0];
                $payment->bidding_id = isset($request->bidding_id) ? $request->bidding_id : '';
                $payment->customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
                $payment->enrollment_id = isset($request->enrollment_id) ? $request->enrollment_id : '';
				if(isset($request->payment_date)) {
                    $s_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                    $payment->payment_date = date('Y-m-d',strtotime($s_date)); 
                }
                else { 
                    $payment->payment_date = '0000-00-00';
                }
                $payment->employee_id = isset($request->employee_id) ? $request->employee_id : 0;
                $payment->payment_for = "2";
                $payment->payment_type_id = isset($request->payment_type_id) ? $request->payment_type_id : 0;
                $payment->total_amount = isset($request->receipt_amount) ? $request->receipt_amount : 0;
                $payment->bank_id = isset($request->bank_id) ? $request->bank_id : 0;
                $payment->bank_branch_name = isset($request->bank_branch_name) ? $request->bank_branch_name : '';
                $payment->cheque_no = isset($request->cheque_no) ? $request->cheque_no : '';
				if(isset($request->cheque_date)) {
                    $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                    $payment->cheque_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $payment->cheque_date = '0000-00-00';
                }
                $payment->cheque_status = isset($request->cheque_status) ? $request->cheque_status : '';
                $payment->transaction_no = isset($request->transaction_no) ? $request->transaction_no : 0;
                $payment->transaction_date = isset($request->transaction_date) ? $request->transaction_date : '0000-00-00';
				if(isset($request->transaction_date)) {
                    $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                    $payment->transaction_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                    $payment->transaction_date = '0000-00-00';
                }
				$payment->debit_to = isset($request->debit_to) ? $request->debit_to : '';
				$payment->created_by = isset($request->created_by) ? $request->created_by : 0;
                $payment->status = isset($request->status) ? ($request->status) : 1;
				if($request->payment_type_id == 2){
							$payment->status = isset($request->status) ? ($request->status) : 2;
						}
                if($payment->total_amount > 0)
                {
					$payment->save();
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_payment_added');
					$response['data'] = $payment;
					return response()->json($response,200);
                }
                else
                {
                    $response['status']="Error";
                    $response['msg']= "Release Amount Invalid";
                    return response()->json($response,200);
                }
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }

}
