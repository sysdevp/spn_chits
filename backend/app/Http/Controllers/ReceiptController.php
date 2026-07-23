<?php
namespace App\Http\Controllers;
ini_set('memory_limit','-1');

use App\Models\Receipt;
use App\Models\AccountExpense;
use App\Models\AccountHead;
use App\Models\AccountGroup;
use App\Models\ChitDetail;
use App\Models\BiddingDetail;
use App\Models\CommitmentAuction;
use App\Models\Employee;
use App\Models\Scheme;
use App\Models\City;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Customer;
use App\Models\AdvanceReceipt;
use App\Models\BeforeEnrolReceipt;
use App\Models\CashSettlement;
use App\Models\Document;
use App\Models\DocumentDetail;
use App\Models\AssetDetail;
use App\Models\BankDetail;
use App\Models\PaymentType;
use App\Models\SlabDue;
use App\Models\UpdateBranch;
use App\Models\UpdateGroup;
use App\Models\UpdateCustomer;
use App\Models\UnknownTransaction;
use App\Models\AuctionOnHoliday;
use App\Models\LogMonitoringTable;
use App\Models\Payment;
use App\Models\AgentCommissionPayment;
use App\Models\OtherCharge;
use App\Models\LeadManagement;
use App\Models\User;
use App\Models\CollectionArea;
use Illuminate\Http\Request;
use DateTime;
use Validator;
use DB; 
use Illuminate\Support\Facades\Storage;
use App\Services\{ReceiptNumberService, AccountPostingService};
//use Excel;

class ReceiptController extends Controller
{
	public function store_receipt(Request $request)
{
    try {
        if (!$request->has('Installments') || count($request->Installments) === 0) {
            return response()->json([
                'status' => 'Error',
                'msg' => __('api.no_data_found')
            ], 401);
        }

        $customer_id = 0;
        $rawCustomer = $request->customer_id ?? null;
        if (is_array($rawCustomer)) {
            if (isset($rawCustomer[0]) && is_array($rawCustomer[0]) && isset($rawCustomer[0]['customer_id'])) {
                $customer_id = (int) $rawCustomer[0]['customer_id'];
            } elseif (isset($rawCustomer['customer_id'])) {
                $customer_id = (int) $rawCustomer['customer_id'];
            }
        } elseif ($rawCustomer !== null && $rawCustomer !== '') {
            $customer_id = (int) $rawCustomer;
        }
        if ($customer_id <= 0) {
            $enrollmentId = (int) ($request->enrollment_id ?? 0);
            if ($enrollmentId > 0) {
                $customer_id = (int) (ChitDetail::where('id', $enrollmentId)->value('customer_id') ?? 0);
            }
        }
        $tenant_id = $request->tenant_id ?? 0;
        $branch_id = $request->branch_id ?? 0;
        $group_id = $request->group_id ?? 0;
        $employee_id = $request->employee_id ?? 0;
        $receipt_date = $this->formatDate($request->receipt_date);
        $receipt_time = $request->receipt_time ?? '00:00:00';
        $accounts_date = now()->format('Y-m-d');
		$type_of_collection = 'receipt';
        // Missing / empty / 0 historically meant "cash" in the UI (required Cash option id=1) but was not persisted.
        $rawPayType = $request->input('payment_type_id');
        $payment_type_id = ($rawPayType === null || $rawPayType === '' || (int) $rawPayType === 0)
            ? 1
            : (int) $rawPayType;
		$debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
		$cheque_no = $request->cheque_no ?? '';
		$cheque_date = $request->cheque_date ?? '0000-00-00';   
		$cheque_clear_return_date = $request->cheque_clear_return_date ?? '0000-00-00';   
		$bank_name_id = $request->bank_name_id ?? 0;
		$branch_name = $request->branch_name ?? '';
		$transaction_no = $request->transaction_no ?? 0;
		$transaction_date = $request->transaction_date ?? '0000-00-00';
		$transaction_bill = $request->transaction_bill ?? '';
		$transaction_book = $request->transaction_book ?? '';
		$neft_adjust_id = $request->neft_adjust_id ?? 0;
		

        $receipt_no_base = $this->generateReceiptNumber($tenant_id, $group_id);
        $receipt_identy = 'RCPT';

        foreach ($request->Installments as $index => $installment) {
            $receipt = new Receipt();
            $receipt->tenant_id = $tenant_id;
            $receipt->branch_id = $branch_id;
            $receipt->customer_id = $customer_id;
            $receipt->group_id = $group_id;
            $receipt->employee_id = $employee_id;
            $receipt->employee_branch_id = Employee::find($employee_id)->branch_id ?? 0;
            $receipt->receipt_date = $receipt_date;
            $receipt->receipt_time = $receipt_time;
            $receipt->accounts_date = $accounts_date;
            $receipt->receipt_mode = 'ERP';
            $receipt->printed = $request->printed ?? 0;
            $receipt->remarks = $request->remarks ?? '';
            $receipt->created_by = $request->created_by ?? 0;
            $receipt->status = $request->status ?? 1;
            $receipt->enrollment_id = $request->enrollment_id ?? 0;

            $receipt->auction_id = $installment['auction_id'] ?? 0;
            $receipt->installment_no = $installment['installment_no'] ?? 0;
            $receipt->pending_days = $installment['pending_days'] ?? 0;
            $receipt->bonus = $installment['bonus_inst_wise'] ?? 0;
            $receipt->discount = $installment['discount_inst_wise'] ?? 0;
            $receipt->received_amount = $installment['received_amount'] ?? 0;
            $receipt->cancel_dividend_amount = $installment['cancel_dividend_inst_wise'] ?? 0;

            $receipt->amount = $receipt->received_amount + $receipt->bonus; // Adjust based on actual calculation

            $receipt->payment_type_id = (int) $payment_type_id;
            $receipt->debit_to = $debit_to;
            $receipt->penalty = isset($installment['penalty_inst_wise']) ? (float) $installment['penalty_inst_wise'] : 0;
            $receipt->cheque_no = $cheque_no;
            $receipt->bank_name_id = (int) $bank_name_id;
            $receipt->branch_name = $branch_name;
            $receipt->transaction_no = $transaction_no;
            $receipt->cheque_date = (is_array($request->cheque_date) && isset($request->cheque_date['year']))
                ? $this->formatDate($request->cheque_date)
                : $cheque_date;
            $receipt->transaction_date = (is_array($request->transaction_date) && isset($request->transaction_date['year']))
                ? $this->formatDate($request->transaction_date)
                : $transaction_date;

            $receipt_no = $receipt_no_base . '-' . sprintf('%02d', $index + 1);
            $receipt->receipt_no = $receipt_no;
            if($receipt->received_amount > 0) {
            $receipt->save();
			

            // Account postings
            $this->postToAccountExpenses($receipt, $customer_id, $receipt_no);

            // Check full payment and update chit status if needed
            $this->checkAndUpdateChitStatus($group_id, $request->enrollment_id);
			}
        }

        return response()->json([
            'status' => 'Success',
            'receipt_no' => $receipt_no,
            'receipt_identify' => $receipt_identy,
            'msg' => __('api.success_receipt_added')
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'Error',
            'msg' => __('api.global_error'),
            'error' => $e->getMessage()
        ], 401);
    }
}

private function generateReceiptNumber($tenant_id, $group_id)
{
    if ($tenant_id == 18) {
        $group_name = Group::where('id', $group_id)->value('group_name');
        $receipt_count = Receipt::where('group_id', $group_id)->count();

        if ($receipt_count <= 1) {
            $current_rec_no = 1;
        } else {
            $latest_receipt = Receipt::where('group_id', $group_id)
                ->whereNotNull('receipt_no')
                ->latest('id')
                ->first();

            $max_no = explode('-', $latest_receipt->receipt_no);
            $current_rec_no = isset($max_no[2]) ? ((int)$max_no[2] + 1) : 1;
        }

        return 'RCPT-' . $group_name . '-' . sprintf('%05d', $current_rec_no);
    }

    return 'RCPT-' . time(); // fallback format
}

private function postToAccountExpenses($receipt, $customer_id, $receipt_no)
{
    // Get Customer Info
    $customer = Customer::where('id', $customer_id)->first(); // Use first() instead of get()
$customer_name = $customer ? ($customer->name . ' ' . $customer->last_name) : '';

    // Get Account Head ID for Cash/Bank based on payment type
    $payment_type_id = $receipt->payment_type_id;
    $debit_account_head = 0;
    $credit_account_head = 0;

    // Determine Debit and Credit Account Heads
    if ($payment_type_id == 1) { // Cash
        $debit_account_head = AccountHead::where('name', 'Cash')->value('id'); // Cash in Hand
    } elseif (in_array($payment_type_id, [2, 3, 4])) { // Bank types
        $bank_account_no = BankDetail::where('id', $receipt->bank_name_id)->value('account_number');
        $debit_account_head = AccountHead::where('bank_account_number', $bank_account_no)->value('id'); // Bank account
    } else {
        $debit_account_head = AccountHead::where('name', 'Other')->value('id'); // UPI or Others
    }

    // Credit goes to: 'Chit Fund Collection' or relevant income head
    $credit_account_head = AccountHead::where('name', 'like', '%Chit Fund Collection%')->value('id');

    // Create Remarks
    $remarks = "Being the " . ($payment_type_id == 1 ? 'cash' : 'bank') . " received from {$customer_name} as chit installment {$receipt->installment_no}";

    // 1. Debit Entry
    $debit = new AccountExpense();
    $debit->tenant_id = $receipt->tenant_id;
    $debit->branch_id = $receipt->branch_id;
    $debit->entry_date = $receipt->receipt_date;
    $debit->voucher_no = $receipt_no;
    $debit->debit_account_head = $debit_account_head ?? 0;
    $debit->debit_amount = $receipt->amount ?? 0;
    $debit->credit_account_head = 0;
    $debit->credit_amount = 0;
    $debit->remarks = $remarks;
    $debit->debit_remarks = $remarks;
    $debit->status = 1;
    $debit->save();

    // 2. Credit Entry
    $credit = new AccountExpense();
    $credit->tenant_id = $receipt->tenant_id;
    $credit->branch_id = $receipt->branch_id;
    $credit->entry_date = $receipt->receipt_date;
    $credit->voucher_no = $receipt_no;
    $credit->debit_account_head = 0;
    $credit->debit_amount = 0;
    $credit->credit_account_head = $credit_account_head ?? 0;
    $credit->credit_amount = $receipt->amount ?? 0;
    $credit->remarks = $remarks;
    $credit->status = 1;
    $credit->save();
}

private function checkAndUpdateChitStatus($group_id, $enrollment_id)
{
    $total_installments = Scheme::where('id', Group::where('id', $group_id)->value('scheme_id'))->value('no_of_months');
    $paid_installments = BiddingDetail::where('group_id', $group_id)->count();

    if ($total_installments == $paid_installments) {
        $total_paid = Receipt::where('enrollment_id', $enrollment_id)->sum(DB::raw('received_amount + bonus'));
        $expected_amount = BiddingDetail::where('group_id', $group_id)->sum('current_installment_amount');

        if ($total_paid >= $expected_amount) {
            ChitDetail::where('id', $enrollment_id)->update(['status' => 2]);
        }
    }
}

private function formatDate($dateArray)
{
    if (!$dateArray || !isset($dateArray['year'])) return now()->format('Y-m-d');
    return date('Y-m-d', strtotime("{$dateArray['year']}-{$dateArray['month']}-{$dateArray['day']}"));
}


    private function storeAdvanceReceipt($request, $receipt_no, $customer_id)
    {
        $advance = new AdvanceReceipt();
        $advance->fill($request->only([
            'tenant_id', 'branch_id', 'enrollment_id', 'employee_id', 'group_id',
            'payment_type_id', 'debit_to', 'cheque_no', 'bank_name_id', 'branch_name',
            'transaction_no', 'receipt_time', 'printed', 'remarks', 'created_by'
        ]));

        $advance->customer_id = $customer_id;
        $advance->employee_branch_id = Employee::where('id', $advance->employee_id)->value('branch_id');
        $advance->accounts_date = now()->toDateString();
        $advance->receipt_amount = $request->balance_advance_amount;
        $advance->receipt_date = $this->formatDate($request->receipt_date);
        $advance->cheque_date = $this->formatDate($request->cheque_date);
        $advance->cheque_clear_return_date = $this->formatDate($request->cheque_clear_return_date);
        $advance->transaction_date = $this->formatDate($request->transaction_date);
        $advance->receipt_mode = 'ERP';
        $advance->status = $request->payment_type_id == 2 ? 2 : ($request->status ?? 1);

        $advance->save();
        $advance->update(['receipt_no' => $receipt_no, 'recpt_excess_no' => $receipt_no]);
    }

    private function storeOtherCharge($request, $receipt_no, $customer_id)
    {
        $charge = new OtherCharge();
        $charge->fill($request->only([
            'tenant_id', 'branch_id', 'enrollment_id', 'employee_id', 'charge_type_id',
            'payment_type_id', 'debit_to', 'cheque_no', 'bank_id', 'bank_branch_name',
            'transaction_no', 'remarks', 'created_by', 'status'
        ]));

        $charge->customer_id = $customer_id;
        $charge->amount = $request->other_amount;
        $charge->received_date = $this->formatDate($request->receipt_date);
        $charge->cheque_date = $this->formatDate($request->cheque_date);
        $charge->transaction_date = $this->formatDate($request->transaction_date);
        $charge->receipt_no = $receipt_no;

        $charge->save();
    }

    private function updateChitStatusIfFullyPaid($request)
    {
        $groupId = $request->group_id;
        $enrollmentId = $request->enrollment_id;

        $biddings = BiddingDetail::where('group_id', $groupId)->get();
        if ($biddings->count() === 0) return;

        $schemeId = Group::where('id', $groupId)->value('scheme_id');
        $no_of_months = Scheme::where('id', $schemeId)->value('no_of_months');
        if ($biddings->count() != $no_of_months) return;

        $installmentsSum = $biddings->sum('current_installment_amount');

        $receipts = Receipt::where('enrollment_id', $enrollmentId)
            ->selectRaw('SUM(received_amount) as total_receipt, SUM(bonus) as total_bonus')
            ->first();

        $total_paid = ($receipts->total_receipt ?? 0) + ($receipts->total_bonus ?? 0);

        if ($total_paid >= $installmentsSum) {
            ChitDetail::where('id', $enrollmentId)->update(['status' => 2]);
        }
    }
	
    public function update_receipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->id)) {  
					$receipt = Receipt::find($request->id);
					$receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                    $receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
                    $receipt->group_id = isset($request->group_id) ? ($request->group_id) : 0;
                    $receipt->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : '';
                    $receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
                    $receipt->employee_branch_id = isset($request->employee_branch_id) ? ($request->employee_branch_id) : 0;
                    $receipt->auction_id = isset($request->auction_id) ? ($request->auction_id) : 0;
                    $receipt->receipt_no = isset($request->receipt_no) ? ($request->receipt_no) : '';
                    $receipt->pending_days = isset($request->pending_days) ? ($request->pending_days) : 0;
                    $receipt->adjust_id = isset($request->adjust_id) ? ($request->adjust_id) : 0;
                    $receipt->amount = isset($request->amount) ? ($request->amount) : 0;
                    $receipt->penalty = isset($request->penalty) ? ($request->penalty) : 0;
                    $receipt->bonus = isset($request->bonus) ? ($request->bonus) : 0;
                    $receipt->discount = isset($request->discount) ? ($request->discount) : 0;
                    $receipt->date = isset($request->date) ? ($request->date) : '0000-00-00';
                    $receipt->type_of_collection = isset($request->type_of_collection) ? ($request->type_of_collection) : '';
                    $receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                    $receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
                    $receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : '0000-00-00';
                    $receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : '0000-00-00';
                    $receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                    $receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
                    $receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                    $receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : '0000-00-00';
                    $receipt->transaction_bill = isset($request->tr_bill) ? ($request->tr_bill) : '';
                    $receipt->transaction_book = isset($request->tr_book) ? ($request->tr_book) : '';
                    $receipt->neft_adjust_id = isset($request->neft_adjust_id) ? ($request->neft_adjust_id) : 0;
                    $receipt->receipt_mode = 'ERP';
                    $receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
                    $receipt->accounts_date = isset($request->accounts_date) ? ($request->accounts_date) : '0000-00-00';
                    $receipt->printed = isset($request->printed) ? ($request->printed) : 0;
                    $receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
					$receipt->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
					if($receipt->save()) {
						$response['status'] = "Success";
						$response['msg'] = \Lang::get('api.success_receipt_updated');
						$response['data'] = $receipt;
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

    public function get_receipts(Request $request) {
        try {
            $cond = $request->conditions; 
            $receipts = Receipt::where($cond)->orderBy('id','desc')->get();
			$res_data = [];
			if(count($receipts)>0) {
				$res_data['tenant_id'] = $receipts[0]->tenant_id;
				$res_data['branch_id'] = $receipts[0]->branch_id;
				$res_data['other_branch'] = $receipts[0]->other_branch;
				$res_data['customer_id'] = $receipts[0]->customer_id;
				$res_data['enrollment_id'] = $receipts[0]->enrollment_id;
				$res_data['group_id'] = $receipts[0]->group_id;
				$res_data['ticket_no'] = $receipts[0]->ticket_no;
				$res_data['employee_id'] = $receipts[0]->employee_id;
				$res_data['receipt_date'] = ($receipts[0]->receipt_date!=0) ? date('d/m/Y',strtotime($receipts[0]->receipt_date)) : '';
				$res_data['payment_type_id'] = $receipts[0]->payment_type_id;
				$res_data['cheque_no'] = $receipts[0]->cheque_no;
				$res_data['cheque_date'] = ($receipts[0]->cheque_date!=0) ? date('d/m/Y',strtotime($receipts[0]->cheque_date)) : '';
				$res_data['bank_name_id'] = $receipts[0]->bank_name_id;
				$res_data['branch_name'] = $receipts[0]->branch_name;
				$res_data['transaction_no'] = $receipts[0]->transaction_no;
				$res_data['transaction_date'] = ($receipts[0]->transaction_date!=0) ? date('d/m/Y',strtotime($receipts[0]->transaction_date)) : '';
				$res_data['debit_to'] = $receipts[0]->debit_to;
				$res_data['amount'] = $receipts[0]->amount;
				$res_data['remarks'] = $receipts[0]->remarks;
				$pending_details = [];
				foreach($receipts as $receipt) {
					$receipt_det = [];
					$receipt_det['auction_id'] = $receipt->auction_id;
					$receipt_det['installment_no'] = $receipt->installment_no;
					$receipt_det['inst_amt_wise_pending'] = $receipt->inst_amt_wise_pending;
					$receipt_det['received_amount'] = $receipt->received_amount;
					$receipt_det['penalty_inst_wise'] = $receipt->penalty_inst_wise;
					$receipt_det['bonus_inst_wise'] = $receipt->bonus_inst_wise;
					$receipt_det['discount_inst_wise'] = $receipt->discount_inst_wise;
					$receipt_det['discount_penalty_wise'] = $receipt->discount_penalty_wise;
					$receipt_det['pending_days'] = $receipt->pending_days;
					$receipt_det['id'] = $receipt->id;
					array_push($pending_details,$receipt_det);
				}
				$res_data['pending_details'] = $pending_details;
			}
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $res_data;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_receipt(Request $request) {
		try {
			
            if(isset($request->receipt_no)) {
				$rec = explode('-',$request->receipt_no);
				if($rec[0]== 'RCPT'){
					
                $receipt_list = Receipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						
							$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
					}
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
				}
				}				else if($rec[0]== 'ADVRCPT'){
				$receipt_list = AdvanceReceipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
	                }
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
					
				}
                } else if($rec[0]== 'ADV'){
				$receipt_list = AdvanceReceipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
	                }
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
					
				}
                } else {
				$receipt_list = Receipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						
							$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
					}
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
				}	
				}
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_receipt_deleted');
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
	
	public function delete_receipt_before(Request $request) {
		try {
            if(isset($request->receipt_no)) {
				$rec = explode('-',$request->receipt_no);
                $receipt_list = BeforeEnrolReceipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
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
                $response['msg'] = \Lang::get('api.success_receipt_deleted');
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
	
	 public function delete_advance_receipt(Request $request) {
		try {
            if(isset($request->receipt_no)) {
				$rec = explode('-',$request->receipt_no);
				if($rec[0]== 'RCPT'){
					
                $receipt_list = Receipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						
							$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
					}
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
				}
				}				else if($rec[0]== 'ADVRCPT'){
				$receipt_list = AdvanceReceipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
	                }
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
					
				}
                } else if($rec[0]== 'ADV'){
				$receipt_list = AdvanceReceipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
	                }
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
					
				}
                } else {
				$receipt_list = Receipt::where('receipt_no',$request->receipt_no)->get(); 
                foreach($receipt_list as $receipt) {
	                $receipt->deleted_by = $request->deleted_by;
					$receipt->status = 0;
	                $receipt->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
	                if($receipt->save()) {
	                	$receipt->delete();
						
							$receipt_list_acc = AccountExpense::where('voucher_no',$request->receipt_no)->get(); 
							foreach($receipt_list_acc as $receipt_list_accs) {
							$receipt_list_accs->deleted_by = $request->deleted_by;
							$receipt_list_accs->status = 0;
							$receipt_list_accs->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
							$receipt_list_accs->delete();
	                }
					}
	                else {
	                	$response['status'] = 'Error';
			            $response['msg'] = \Lang::get('api.global_error');
			            return response()->json($response, 401);
	                }
				}	
				}
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_receipt_deleted');
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

    public function cheque_return(Request $request)
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
            	$receipt = Receipt::where('receipt_no',$request->id)->get();
                $rcpt_no = Receipt::where('receipt_no',$receipt[0]->receipt_no)->get();
                foreach($rcpt_no as $rcpt)
                {                  
                	$receipt_insert = Receipt::find($rcpt->id);                 
	                if(isset($request->cheque_clear_return_date)) {
	                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
	                    $receipt_insert->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
	                }
	                else {
	                    $receipt_insert->cheque_clear_return_date = '0000-00-00';
	                } 
	                $receipt_insert->remarks = isset($request->remarks) ? ($request->remarks) : '';
	                $receipt_insert->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
	                $receipt_insert->status = isset($request->status) ? ($request->status) : 3;
	                $receipt_insert->save();	                   
	            }
	            $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_cheque_returned');
                $response['data'] = $rcpt_no;
                return response()->json($response,200);
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

    public function cheque_cleared(Request $request)
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
			    $rec_no = explode('-',$request->id);
				
				if($rec_no[0] == 'ADVRCPT' || $rec_no[0] == 'ADV'){
				$rcpt_no = AdvanceReceipt::where('receipt_no',$request->id)->get();
                foreach($rcpt_no as $rcpt)
                {                  
                	$receipt_insert = AdvanceReceipt::find($rcpt->id); 
					
	                if(isset($request->cheque_clear_return_date)) {
	                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
	                    $receipt_insert->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
	                }
	                else {
	                    $receipt_insert->cheque_clear_return_date = '0000-00-00';
	                }
	                //$receipt_insert->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
	                $receipt_insert->remarks = isset($request->remarks) ? ($request->remarks) : '';
	                $receipt_insert->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
	                $receipt_insert->status = isset($request->status) ? ($request->status) : 1;
	                $receipt_insert->save();
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_cheque_cleared');
                //$response['data'] = $rcpt_no;
                return response()->json($response,200);
					
				} else {
					
				$rcpt_no = Receipt::where('receipt_no',$request->id)->get();
                foreach($rcpt_no as $rcpt)
                {                  
                	$receipt_insert = Receipt::find($rcpt->id); 
	                if(isset($request->cheque_clear_return_date)) {
	                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
	                    $receipt_insert->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
	                }
	                else {
	                    $receipt_insert->cheque_clear_return_date = '0000-00-00';
	                }
	                //$receipt_insert->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
	                $receipt_insert->remarks = isset($request->remarks) ? ($request->remarks) : '';
	                $receipt_insert->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
	                $receipt_insert->status = isset($request->status) ? ($request->status) : 1;
	                $receipt_insert->save();
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_cheque_cleared');
                //$response['data'] = $rcpt_no;
                return response()->json($response,200);
					
				}
                //$receipt = Receipt::where('id',$request->id)->get();
                
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                $response['msg'] = $e;
                return response()->json($response, 401);
            }
        }
    }
	
	public function get_enrol_details(Request $request) {
        try {
			
			$cr_date = date("Y-m-d");
            //enrollment id
            $recdate = '';
            $s_date = ($request->recdate['year']).'/'.sprintf('%02d',($request->recdate['month'])).'/'.sprintf('%02d',($request->recdate['day']));
        	$rec_date = date('Y-m-d',strtotime($s_date));
        	//$rec_date = $request->recdate;
            $enrl_id = $request->enrolid;
			
            if($rec_date == $cr_date) 
            {			
			
	            $chit_details = ChitDetail::where('id','=',$enrl_id)->orderBy('id','desc')->get();				
				$response_data = [];			
				foreach($chit_details as $cd) 
				{
					$enrollment_id = $cd->id;
					$ticket_type = $cd->ticket_type;
			        $group_id = $cd->group_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
					$total_installment_amount = $total_installment_amount * $ticket_type;
					$cd->overall_installment_amount = $total_installment_amount;
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;
					//pending_amount 
					$pending_amount = $total_installment_amount - $paid_amount;
					if($cd->tenant_id == 30){
					$cd->overall_pending_amount = 0;
					} else {
					$cd->overall_pending_amount = $pending_amount;
					 }
					
					$cd->chit_value = $chit_value;
					
					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->groupBy('installment_no')->get();
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) 
					{
						
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;					
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$inst_amt = $inst_amt * $ticket_type;
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
						
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
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
							$i++;
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
						
						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date = $check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
						$holiday_count = 0;
						if(count($auction_on_holiday)>0)
						{	
							$holiday_count += $auction_on_holiday->count();
						}

						$bonus_days_include_holiday = $bonus_days + $holiday_count;
						if($pending_days <=  $bonus_days_include_holiday)
						{ 
							//echo $pending_days ."<=". $bonus_days_include_holiday;
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
		                if($cd->customer_det->penalty_days!=0 )
		                {
		                  $inst_penalty_days = $cd->customer_det->penalty_days;
		                  $inst_penalty_percentage = $cd->customer_det->customer_penalty_interest;
						  $pen_type = "customer";
		                }
		                elseif($cd->group_det->group_penalty_days!=0 )
		                {
		                  $inst_penalty_days = $cd->group_det->group_penalty_days;
		                  $inst_penalty_percentage = $cd->group_det->group_base_penalty;
						  $pen_type = "group";
		                }
		                elseif($cd->branch_det->penalty_days!=0)
		                {
						
		                  $inst_penalty_days = $cd->branch_det->penalty_days;
		                  $inst_penalty_percentage = $cd->branch_det->branch_wise_penalty;
						  $pen_type = "branch";
		                }
		                else
		                {
		                  $inst_penalty_days = "0";
		                  $inst_penalty_percentage = "0";
						  $pen_type = "0";
		                }
                       
		                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
		                $pend_days_int = 0;    
		                $cust_det['penalty_amounts'] = 0;   
						
		                if($pending_days > $inst_penalty_days)
		                {    
						
                            if($pen_type == "branch"){
							
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
							} else if($pen_type == "group"){
								       
		                    	$pend_days_int = $pending_days * ($inst_penalty_percentage / 100);
		                    	$current_month_days = date('t'); 
		                    	$total_days_int = $pend_days_int / $current_month_days;
		                    	$penalty = $total_days_int * $ins_amt_wise_rec;
		                    	$cust_det['penalty_amounts'] = strval(round($penalty));

                            } else if($pen_type == "customer") {
								           
		                    	$pend_days_int = $pending_days * ($inst_penalty_percentage / 100);
		                    	$current_month_days = date('t'); 
		                    	$total_days_int = $pend_days_int / $current_month_days;
		                    	$penalty = $total_days_int * $ins_amt_wise_rec;
		                    	$cust_det['penalty_amounts'] = strval(round($penalty));
                      
                            } else {
                                
								$penalty = 0;
		                    	$cust_det['penalty_amounts'] = strval(round($penalty));
                            }								
	                	}    
					 
						$cust_det['installment_no'] = $installment_no;
						
						$cust_det['inst_amt_wise'] = $inst_amt;
						$cust_det['amount_paid'] = $amount_paid;
						if($cd->tenant_id == 30){
					$cust_det['inst_amt_wise_pending'] = 0;
					} else {
					$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
					 }
						
						$cust_det['pending_days'] = $pending_days;
						$cust_det['bonus_inst_wise'] = 0;
						$cust_det['penalty_inst_wise'] = 0;
						$cust_det['discount_inst_wise'] = 0;
						$cust_det['discount_penalty_wise'] = 0;
						$cust_det['auction_id'] = $auction_id;
						
						if($cd->tenant_id == 30){
					$cust_det['installment_wise_overall_pending'] = 0;
					} else {
					$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
					 }
						
						$cust_det['cancel_dividend_amount'] =0;
						if($ins_amt_wise_rec>0) {
							array_push($install_det,$cust_det);
						}	
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					if($cd->tenant_id == 30){
					$response_data['total_overall_pending_amount'] = 0;	
					} else {
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];	
					 }
					
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			} else if($rec_date != $cr_date)  
			{  
				$chit_details = ChitDetail::where('id','=',$enrl_id)->orderBy('id','desc')->get();			
				$response_data = [];			
				foreach($chit_details as $cd) {
					$enrollment_id = $cd->id;
					$ticket_type = $cd->ticket_type;
			        $group_id = $cd->group_id;
			        $branch_id = $cd->branch_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value'); 
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
					$total_installment_amount = $total_installment_amount * $ticket_type;
					$cd->overall_installment_amount = $total_installment_amount;
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;
					//pending_amount 
					$pending_amount = $total_installment_amount - $paid_amount;
					if($cd->tenant_id == 30){
					$cd->overall_pending_amount = 0;
					} else {
					$cd->overall_pending_amount = $pending_amount;
					 }
					$cd->chit_value = $chit_value;
					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
				   
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->where('auction_type','!=','duplicate')->groupBy('installment_no')->get();
					
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;					
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$inst_amt = $inst_amt * $ticket_type;
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
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');	
							$i++;
						}  			
						/*if($auc_date_inst <= $rec_date)	
						{
							$cust_det['before_auction_date'] = "Auction ";
						}	
						else{
							$cust_det['before_auction_date'] = "cc";
						}*/	
						// Branch - old bonus, penalty calculation
						$up_branch = UpdateBranch::where('branch_id',$branch_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_branches = UpdateBranch::where('id',$up_branch)->get();
						if(count($update_branches)>0)
						{
							$branch_bonus_days = isset($update_branches[0]->bonus_days) ? ($update_branches[0]->bonus_days) : 0;
							$branch_bonus_percentage = isset($update_branches[0]->bonus) ? ($update_branches[0]->bonus) : 0;
							$branch_penalty_days = isset($update_branches[0]->penalty_days) ? ($update_branches[0]->penalty_days) : 0;
		                	$branch_penalty_percentage = isset($update_branches[0]->branch_wise_penalty) ? ($update_branches[0]->branch_wise_penalty) : 0;
						}
						else
						{
							$branch_bonus_days = 0;
							$branch_bonus_percentage = 0;
							$branch_penalty_days =0;
							$branch_penalty_percentage =0;
						}

						// Group - old bonus, penalty calculation
						$up_group = UpdateGroup::where('group_id',$group_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_groups = UpdateGroup::where('id',$up_group)->get();
						if(count($update_groups)>0)
						{
							$group_bonus_days = isset($update_groups[0]->group_bonus_days) ? ($update_groups[0]->group_bonus_days) : 0; 
							$group_bonus_percentage = isset($update_groups[0]->group_base_bonus) ? ($update_groups[0]->group_base_bonus) : 0;
							$group_penalty_days = isset($update_groups[0]->group_penalty_days) ? ($update_groups[0]->group_penalty_days) : 0;
							$group_penalty_percentage = isset($update_groups[0]->group_base_penalty) ? ($update_groups[0]->group_base_penalty) : 0;
						}
						else
						{
							$group_bonus_days =0;
							$group_bonus_percentage =0;
							$group_penalty_days =0;
							$group_penalty_percentage =0;
						} 
						// Customer - old bonus, penalty calculation
						$up_customer = UpdateCustomer::where('update_customer_id',$customer_id)->where('edit_date','<=',$rec_date)->max('id');
						$update_customers = UpdateCustomer::where('id',$up_customer)->get();
						if(count($update_customers)>0)
						{
							$cust_bonus_days = isset($update_customers[0]->bonus_days) ? ($update_customers[0]->bonus_days) : 0;
							$cust_bonus_percentage = isset($update_customers[0]->customer_bonus) ? ($update_customers[0]->customer_bonus) : 0;
							$cust_penalty_days = isset($update_customers[0]->penalty_days) ? ($update_customers[0]->penalty_days) : 0;
							$cust_penalty_percentage = isset($update_customers[0]->customer_penalty_interest) ? ($update_customers[0]->customer_penalty_interest) : 0;
						}
						else
						{	
							$cust_bonus_days =0;
							$cust_bonus_percentage = 0;
							$cust_penalty_days = 0;
							$cust_penalty_percentage =0;
						}
						
						// Bonus days and percentage by heirrachy over all 
						if($cust_bonus_days!=0 && $cust_bonus_percentage!=0)
						{
						  $bonus_days = $cust_bonus_days;
						  $bonus_percentage = $cust_bonus_percentage;
						}    
						elseif($group_bonus_days!=0 && $group_bonus_percentage!=0)
						{
						  $bonus_days = $group_bonus_days;
						  $bonus_percentage = $group_bonus_percentage;
						}
						elseif($branch_bonus_days!=0 && $branch_bonus_percentage!=0)
						{
						  $bonus_days = $branch_bonus_days;
						  $bonus_percentage = $branch_bonus_percentage;
						}    
						else
						{
						  $bonus_days = 0;
						  $bonus_percentage = 0;
						} 		

						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date = $check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$rec_date])->get();
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
							//$bonus_calculation = $chit_value[0] * $bonus_percentage / 100 ;
							
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
		                if($cust_penalty_days!=0 )
		                {
		                  $inst_penalty_days = $cust_penalty_days;
		                  $inst_penalty_percentage = $cust_penalty_percentage;
		                }
		                elseif($group_penalty_days!=0 )
		                {
		                  $inst_penalty_days = $group_penalty_days;
		                  $inst_penalty_percentage = $group_penalty_percentage;
		                }
		                elseif($branch_penalty_days!=0)
		                {
		                  $inst_penalty_days = $branch_penalty_days;
		                  $inst_penalty_percentage = $branch_penalty_percentage;
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
		                  if($cd->prized_status==0)
		                  {
		                    $non_prize = isset($update_branches[0]->non_prize_subscriber_penalty) ? ($update_branches[0]->non_prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($non_prize/ 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  } 
		                  else
		                  {               
		                    $prize = isset($update_branches[0]->prize_subscriber_penalty) ? ($update_branches[0]->prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($prize / 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  }                        		  
		                }    
						
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
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					if($cd->tenant_id == 30){
					$response_data['total_overall_pending_amount'] = 0;	
					} else {
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];	
					 }
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}			
			}
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);	
		}
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_receipts(Request $request) {
        try { 
            $cond = [];
            $cond1 = [];
            $start_date = null;
            $end_date = null;
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
				$cond1['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
				$cond1['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id; 
				$cond1['group_id'] = $request->group_id; 
			}
			if(isset($request->customer_id)){
				$cond['customer_id'] = $request->customer_id; 
				$cond1['customer_id'] = $request->customer_id; 
			}
			if(isset($request->employee_id)){
				$cond['employee_id'] = $request->employee_id; 
				$cond1['employee_id'] = $request->employee_id; 
			}
			if(isset($request->payment_type_id)){
				$cond['payment_type_id'] = $request->payment_type_id; 
				$cond1['payment_type_id'] = $request->payment_type_id; 
			} 
			if(isset($request->start_date)) { 
        		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
        		$start_date = date('Y-m-d',strtotime($s_date)); 
        	}				
        	if(isset($request->end_date)) {
        		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
        		$end_date = date('Y-m-d',strtotime($s_date)); 
        	}             
			$response["start_date"] = $start_date;
			$response["end_date"] = $end_date;
			
			
			//$receipts = DB::select('call receipt_report(?,?,?,?,?,?,?,?)',array($request->tenant_id,$request->branch_id,$request->group_id,$request->customer_id,$request->employee_id,$request->payment_type_id,$start_date,$end_date));
			if($request->tenant_id == 47){
			$advance_rcpts = DB::table("advance_receipts")->Where($cond1)->Where('status','!=','0')->whereBetween('receipt_date',[$start_date,$end_date])->select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","receipt_amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status","alt_rec_no","transaction_no","transaction_date","adjust_id");

    		// $receipts =  DB::table("receipts")->Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->select("tenant_id","receipt_date","amount","receipt_passbook_mode")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		// print_r($receipts);exit;
             
			$receipts = Receipt::select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status","alt_rec_no","transaction_no","transaction_date","adjust_id")->Where($cond)->Where('payment_type_id','!=','7')->whereBetween('receipt_date',[$start_date,$end_date])->groupBy('receipt_no')->union($advance_rcpts)->orderBy('receipt_date')->get();
			}else if($request->tenant_id == 42){
			$advance_rcpts = DB::table("advance_receipts")->Where($cond1)->Where('status','!=','0')->whereBetween('receipt_date',[$start_date,$end_date])->select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","receipt_amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status");

    		// $receipts =  DB::table("receipts")->Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->select("tenant_id","receipt_date","amount","receipt_passbook_mode")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		// print_r($receipts);exit;
             
			$receipts = Receipt::select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status")->Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->groupBy('receipt_no')->union($advance_rcpts)->orderBy('receipt_date')->get();	
			}else if($request->tenant_id == 30) {
			$advance_rcpts = DB::table("advance_receipts")->Where($cond1)->Where('status','!=','0')->Where('recpt_excess_no', 'not like', "%rcpt%")->whereBetween('accounts_date',[$start_date,$end_date])->select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","receipt_amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status");

    		// $receipts =  DB::table("receipts")->Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->select("tenant_id","receipt_date","amount","receipt_passbook_mode")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		// print_r($receipts);exit;
             
			$receipts = Receipt::select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status")->Where($cond)->Where('payment_type_id','!=','7')->Where('group_id','!=','0')->whereBetween('accounts_date',[$start_date,$end_date])->groupBy('receipt_no')->union($advance_rcpts)->orderBy('accounts_date')->get();	
			} else {
			$advance_rcpts = DB::table("advance_receipts")->Where($cond1)->Where('status','!=','0')->Where('recpt_excess_no', 'not like', "%rcpt%")->whereBetween('receipt_date',[$start_date,$end_date])->select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","receipt_amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status");

    		// $receipts =  DB::table("receipts")->Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->select("tenant_id","receipt_date","amount","receipt_passbook_mode")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		// print_r($receipts);exit;
             
			$receipts = Receipt::select("enrollment_id","bank_name_id","payment_type_id","customer_id","employee_id","group_id","branch_id","tenant_id","receipt_date","amount as received_amount","cheque_no","receipt_passbook_mode","accounts_date","cheque_date","cheque_clear_return_date","cheque_debit_to","ticket_no","receipt_no","cancel_dividend_amount","status")->Where($cond)->Where('group_id','!=','0')->whereBetween('receipt_date',[$start_date,$end_date])->groupBy('receipt_no')->union($advance_rcpts)->orderBy('receipt_date')->get();	
			}
			
			// print_r($receipts);exit;
            $i=1;  
            $grand_tot_receipt_amt =0;
            $tot_receipt_amt =0;
            $grand_tot_received_amt =0;
            $tot_received_amt =0;
            $grand_tot_penalty_amount =0;
            $tot_penalty_amount =0;
            $grand_tot_bonus_amount = 0;
            $tot_bonus_amount =0;
			
            foreach($receipts as $receipt) {
				//print_r($receipt->receipt_no); echo '/';
                $receipt->sno = $i;
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
				//$receipt->branch_name = isset($receipt->branchName) ? ($receipt->branchName) : '-';
				//$receipt->group_name = isset($receipt->group_name) ? ($receipt->group_name) : '-';
				//$receipt->employee_name = isset($receipt->first_name) ? ($receipt->first_name) : '-';
				//$receipt->customer_name = isset($receipt->name) ? ($receipt->name) : '';
				$receipt->branch_name = isset($receipt->branch_det->branch_name) ? ($receipt->branch_det->branch_name) : [];
				

				$group_id = ChitDetail::where('id',$receipt->enrollment_id)->withTrashed()->pluck('group_id');
				$alt_rec_no='';
				$group_name = Group::where('id',$group_id[0])->pluck('group_name');
				$receipt->group_name = $group_name[0];
				$ticket_no = ChitDetail::where('id',$receipt->enrollment_id)->withTrashed()->pluck('ticket_no');
				$receipt->ticket_no = $ticket_no[0];
					
				
				
				
				$receipt->employee_name = isset($receipt->employee_det->first_name) ? ($receipt->employee_det->first_name) : '-';
				
				$customer_name = Customer::where('id',$receipt->customer_id)->withTrashed()->get();
				//print_r($receipt->receipt_no);echo '/';
				$receipt->customer_name = $customer_name[0]->name . ' ' . $customer_name[0]->last_name  . ' | ' . $customer_name[0]->customer_code;
				
				$receipt->enrollment_branch_name = isset($receipt->enroll_branch) ? ($receipt->enroll_branch) : [];	
				
				$receipt->payment_type_name = '-';
				
				if($request->tenant_id == 47){
				if($receipt->adjust_id > 0){
					
				$group_ids = ChitDetail::where('id',$receipt->adjust_id)->withTrashed()->pluck('group_id');
				
				$group_names = Group::where('id',$group_ids[0])->pluck('group_name');
				
				$ticket_nos = ChitDetail::where('id',$receipt->adjust_id)->withTrashed()->pluck('ticket_no');
				
				$cus_ids = ChitDetail::where('id',$receipt->adjust_id)->withTrashed()->pluck('customer_id');
				
				$cus_name = Customer::where('id',$cus_ids[0])->pluck('name');
				
				$receipt->adjusted_from = $group_names[0] . ' / ' . $ticket_nos[0] . ' / ' . $cus_name[0];
				}
				}
				if(isset($receipt->payment_type_id)) {
					$payment_type_det = PaymentType::find($receipt->payment_type_id);	
					$receipt->payment_type_name = isset($payment_type_det) ? ($payment_type_det->payment_name) : '-';
				}
				
				$receipt->bank_name = '-';
				$receipt->bank_branch_name = '-';
				if(isset($receipt->bank_name_id)) {
					$bank_det = BankDetail::find($receipt->bank_name_id);	
					$receipt->bank_name = isset($bank_det) ? ($bank_det->bank_name) : '-';
					$receipt->bank_branch_name = isset($bank_det) ? ($bank_det->bank_branch_name) : '-';
				}
				$total_paid = isset($receipt->received_amount) ? ($receipt->received_amount) : 0;
				$total_paidcancel = isset($receipt->cancel_dividend_amount) ? ($receipt->cancel_dividend_amount) : 0;
				// $total_paid+=$total_paidcancel;
				$receipt->total_paid = $total_paid;
				$bonus_amount = $receipt->bonus_amount;
				$receipt->bonus_amount = $bonus_amount;
				$penalty_amount = Receipt::Where('receipt_no',$receipt->receipt_no)->sum('penalty');
				
				$receipt->penalty_amount = $penalty_amount;
				$total_received_amount = $total_paid + $penalty_amount;
				$receipt->total_received_amount = $total_received_amount;
				$receipt->receipt_passbook_mode = $receipt->receipt_passbook_mode;
				$receipt->other_branch_info = [];
				$receipt->other_branch_name = '-';
				// print_r($total_paid);exit;
				if(($receipt->other_branch!=0) && ($receipt->enrollment_id)) {
					$other_branch_det = ChitDetail::find($receipt->enrollment_id);
					$receipt->other_branch_info = isset($other_branch_det->branch_det) ? ($other_branch_det->branch_det) : [];
					$receipt->other_branch_name = isset($other_branch_det->branch_det) ? ($other_branch_det->branch_det->branch_name) : '-';
				}
				$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '0000-00-00';
				$receipt->accounts_date = ($receipt->accounts_date!=0) ? date('d/m/Y',strtotime($receipt->accounts_date)) : '0000-00-00';
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '0000-00-00';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '0000-00-00';
				$receipt->cheque_bank_name = '-';
				if(isset($receipt->cheque_debit_to)) {
					$cheque_bank_det = BankDetail::find($receipt->cheque_debit_to);	
					$receipt->cheque_bank_name = isset($cheque_bank_det) ? ($cheque_bank_det->bank_name) : '-';
				}
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
				$tot_receipt_amt += $total_paid;
				$tot_received_amt += $total_received_amount;
				$tot_penalty_amount += $penalty_amount;
				$tot_bonus_amount += $bonus_amount;
				
            }
			$receipt_return = receipt::Where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->where('status','3')->groupBy('receipt_no')->sum('amount');
			if($receipt_return > 0){
			$receipt_return = $receipt_return;
			} else {
			$receipt_return = 0;	
			}
            $grand_tot_receipt_amt += $tot_receipt_amt;
            $grand_tot_received_amt += $tot_received_amt;
            $grand_tot_penalty_amount += $tot_penalty_amount;
            $grand_tot_bonus_amount += $tot_bonus_amount;
            $response['grand_tot_receipt_amt'] = $grand_tot_receipt_amt - $receipt_return;
            $response['grand_tot_received_amt'] = $grand_tot_received_amt - $receipt_return;
            $response['grand_tot_penalty_amount'] = $grand_tot_penalty_amount;
            $response['grand_tot_bonus_amount'] = $grand_tot_bonus_amount;
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
	
	/*public function get_dashboard(Request $request) 
	{
        try 
        { 
		    $first_day_this_month = date('Y-m-01'); 
			$last_day_this_month  = date('Y-m-t');
			$dashboards = Branch::where('id','=',$request->branch_id)->get();
			foreach($dashboards as $dashboard) {
				//group count 
				$group_count = Group::where('branch_id','=',$request->branch_id)->count();
				$dashboard->group_count = $group_count; 
				$ato_monthly = 0;
			 	if(isset($request->tenant_id)) { 
					$dashboard = [];	 
					//group count
		            if(isset($request->branch_id)) {  
						$group_count = Group::where(array('branch_id'=>$request->branch_id,'group_status'=>0))->count();
						$dashboard['group_count'] = $group_count;
			 		}
			 		else {
						$group_count = Group::where(array('tenant_id'=>$request->tenant_id,'group_status'=>0))->count();
						$dashboard['group_count'] = $group_count;
					}
					//vacant group
					if(isset($request->branch_id)) {  
						$group_vacant = Group::where(array('branch_id'=>$request->branch_id,'is_filled'=>1))->count();
						$dashboard['group_vacant'] = $group_vacant;
					} 
					else {
						$group_vacant = Group::where(array('tenant_id'=>$request->tenant_id,'is_filled'=>1))->count();
						$dashboard['group_vacant'] = $group_vacant;	
					} 
					//new group
					if(isset($request->branch_id)) {  
						$new_group_count = Group::where('branch_id','=',$request->branch_id)->whereBetween('starting_date',[$first_day_this_month,$last_day_this_month])->count();
						$dashboard['new_group_count'] = $new_group_count;
					} 
					else {
						$new_group_count = Group::where('tenant_id','=',$request->tenant_id)->whereBetween('starting_date',[$first_day_this_month,$last_day_this_month])->count();
						$dashboard['new_group_count'] = $new_group_count;	
					}
					//no of customers
					if(isset($request->branch_id)) {   
						$customer_count = Customer::where('branch_id','=',$request->branch_id)->count();
						$dashboard['customer_count'] = $customer_count;
					} 
					else {
						$customer_count = Customer::where('tenant_id','=',$request->tenant_id)->count();
						$dashboard['customer_count'] = $customer_count;
					}
					//bid payment outstanding
					if(isset($request->branch_id)) {  
						$bidding_oustanding = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('pending_release');
						$dashboard['bidding_oustanding'] = $bidding_oustanding;
					} 
					else {
						$bidding_oustanding = BiddingDetail::where('tenant_id','=',$request->tenant_id)->sum('pending_release');
						$dashboard['bidding_oustanding'] = $bidding_oustanding;	
					} 
					//this month outstanding
					$penalty_amount = 0;
					$total_paid = 0; 
					if(isset($request->branch_id)) {  
					    $this_month_collections = Receipt::selectRaw('sum(received_amount) as total_paid, sum(penalty) as penalty_amount')->where('branch_id','=',$request->branch_id)->whereBetween('receipt_date',[$first_day_this_month,$last_day_this_month])->get();
						$total_paid = $this_month_collections[0]->total_paid;
						$penalty_amount = $this_month_collections[0]->penalty_amount;
						$dashboard['over_all_collection'] = $total_paid + $penalty_amount;
					} 
					else {
						$this_month_collections = Receipt::selectRaw('sum(received_amount) as total_paid, sum(penalty) as penalty_amount')->where('tenant_id','=',$request->tenant_id)->whereBetween('receipt_date',[$first_day_this_month,$last_day_this_month])->get();
						$total_paid = $this_month_collections[0]->total_paid;
						$penalty_amount = $this_month_collections[0]->penalty_amount;
						$dashboard['over_all_collection'] = $total_paid + $penalty_amount;	
					}  
					//auction turn over_all_collection
					if(isset($request->branch_id)) {   
						$group_ato = Group::where(array('branch_id'=>$request->branch_id,'group_status'=>0,'is_first_auction_complete'=>0))->pluck('scheme_id');
						foreach($group_ato as $ato) {  
							$scheme_id = $ato;  
							$chitvalue = Scheme::where('id','=',$scheme_id)->get();  
							if(count($chitvalue)>0) { 
								$ato_monthly += $chitvalue[0]->chit_value;
							}
						} 
					}
					else {
						$group_ato = Group::where(array('tenant_id'=>$request->tenant_id,'group_status'=>0,'is_first_auction_complete'=>0))->pluck('scheme_id');
						foreach($group_ato as $ato) {
							$scheme_id = $ato;
							$chitvalue = Scheme::where('id','=',$scheme_id)->get();
							if(count($chitvalue)>0) { 
								$ato_monthly += $chitvalue[0]->chit_value;
							}
						}
					} 
					//bid payment outstanding
					if(isset($request->branch_id)) {  
						$bidding_oustanding_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('pending_release');
						$dashboard['bidding_oustanding_monthly'] = $bidding_oustanding_monthly;
					} 
					else {
						$bidding_oustanding_monthly = BiddingDetail::where('tenant_id','=',$request->tenant_id)->sum('pending_release');
						$dashboard['bidding_oustanding_monthly'] = $bidding_oustanding_monthly;	 
					} 
				   	//Bid Amount
				   	if(isset($request->branch_id)) {   
					    $bidding_amount_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('bidding_amount');
						$dashboard['bidding_amount_monthly'] = $bidding_amount_monthly;
				   	} 
				   	else {
						$bidding_amount_monthly = BiddingDetail::where('tenant_id','=',$request->tenant_id)->sum('bidding_amount');
						$dashboard['bidding_amount_monthly'] = $bidding_amount_monthly;  
				   	} 
				    //Bid Payable
					if(isset($request->branch_id)) {  
						$bidding_payable_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('customer_released');
						$dashboard['bidding_payable_monthly'] = $bidding_payable_monthly;
					} 
					else {
						$bidding_payable_monthly = BiddingDetail::where('tenant_id','=',$request->tenant_id)->sum('customer_released');
						$dashboard['bidding_payable_monthly'] = $bidding_payable_monthly;	
					}  
				} 
				else {
					$dashboard = [];
					//group count 
					$group_count = Group::where(array('group_status'=>0))->count();
					$dashboard['group_count'] = $group_count;
					//vacant group
					$group_vacant = Group::where(array('is_filled'=>1))->count();
					$dashboard['group_vacant'] = $group_vacant;
					//new group
					$new_group_count = Group::whereBetween('starting_date',[$first_day_this_month,$last_day_this_month])->count();
					$dashboard['new_group_count'] = $new_group_count;
					//no of customers
					$customer_count = Customer::orderBy('id','desc')->count();
					$dashboard['customer_count'] = $customer_count;
					//bid payment outstanding
					$bidding_oustanding = BiddingDetail::sum('pending_release');
					$dashboard['bidding_oustanding'] = $bidding_oustanding;
					//this month outstanding
					$penalty_amount = 0;
					$total_paid = 0;
				    $this_month_collections = Receipt::selectRaw('sum(received_amount) as total_paid, sum(penalty) as penalty_amount')->whereBetween('receipt_date',[$first_day_this_month,$last_day_this_month])->get();
					$total_paid = $this_month_collections[0]->total_paid;
					$penalty_amount = $this_month_collections[0]->penalty_amount;
					$dashboard['over_all_collection'] = $total_paid + $penalty_amount;
					//auction turn over_all_collection
					$group_ato = Group::where(array('group_status'=>0,'is_first_auction_complete'=>0))->pluck('scheme_id');
					foreach($group_ato as $ato) {
						$scheme_id = $ato;
						$chitvalue = Scheme::where('id','=',$scheme_id)->get();
						if(count($chitvalue)>0) { 
							$ato_monthly += $chitvalue[0]->chit_value;
						}
					};
					//bid payment outstanding
					$bidding_oustanding_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('pending_release');
					$dashboard['bidding_oustanding_monthly'] = $bidding_oustanding_monthly;
				   	//Bid Amount
				    $bidding_amount_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('bidding_amount');
					$dashboard['bidding_amount_monthly'] = $bidding_amount_monthly;
				    //Bid Payable
					$bidding_payable_monthly = BiddingDetail::where('branch_id','=',$request->branch_id)->sum('customer_released');
					$dashboard['bidding_payable_monthly'] = $bidding_payable_monthly;
			   
			 	}
		 	}
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $dashboards;
            return response()->json($response,200);
		}
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }*/

    public function get_dashboard(Request $request) 
	{
        try 
        { 
		    $first_day_this_month = date('Y-m-01'); 
			$last_day_this_month  = date('Y-m-t');
			$running_groups_count = 0;
			$vacant_groups_count = 0;
			$new_groups_count = 0;
			$customers_count = 0;
			$total_bidding_oustanding = 0;
			$current_month_collection = 0;
			$cash_in_hand = 0;
			$auction_turn_over = 0;
			$monthly_bidding_outstanding = 0;
			$monthly_bidding_amount = 0;
			$monthly_bidding_payable = 0;

			$dashboard = [];

			if(isset($request->branch_id)) { 
				$branch_list = Branch::where('id','=',$request->branch_id)->get();
				$customer_list = Customer::where('branch_id','=',$request->branch_id)->where('is_customer','=',1)->get();
				$customers_count = count($customer_list);
			}
			else if(isset($request->tenant_id)) { 
				$branch_list = Branch::where('tenant_id','=',$request->tenant_id)->get();
				$customer_list = Customer::where('tenant_id','=',$request->tenant_id)->where('is_customer','=',1)->get();
				$customers_count = count($customer_list);
			}
			else { 
				$branch_list = Branch::all();
				$customer_list = Customer::all();
				$customers_count = count($customer_list);
			} 
			foreach($branch_list as $branch) {  
				$total_paid = 0;
				$penalty_amount = 0;
				$total_paid_advance = 0;
				$total_paid_before_advance = 0;
				$group_count = Group::where('branch_id','=',$branch->id)->count();
				$running_groups_count += $group_count;
				$group_vacant = Group::where(array('branch_id'=>$branch->id,'is_filled'=>1))->count();
				$vacant_groups_count += $group_vacant;
				$new_group_count = Group::where('branch_id','=',$branch->id)->whereBetween('starting_date',[$first_day_this_month,$last_day_this_month])->count();
				$new_groups_count += $new_group_count;
				$bidding_oustanding = BiddingDetail::where('branch_id','=',$branch->id)->sum('pending_release');
				$total_bidding_oustanding += $bidding_oustanding;
				$this_month_collections = Receipt::selectRaw('sum(received_amount) as total_paid, sum(penalty) as penalty_amount')->where('branch_id','=',$branch->id)->where('status','=',1)->where('payment_type_id','!=',7)->whereBetween('receipt_date',[$first_day_this_month,$last_day_this_month])->get();
				$total_paid = $this_month_collections[0]->total_paid; 
				$penalty_amount = $this_month_collections[0]->penalty_amount;
				$this_month_collections_adv = AdvanceReceipt::selectRaw('sum(receipt_amount) as adv_total_paid')->where('payment_type_id','!=',9)->where('branch_id','=',$branch->id)->where('status','=',1)->whereBetween('receipt_date',[$first_day_this_month,$last_day_this_month])->get();
				$total_paid_advance = $this_month_collections_adv[0]->adv_total_paid; 
				$this_month_collections_before_adv = BeforeEnrolReceipt::selectRaw('sum(amount) as bef_adv_total_paid')->where('branch_id','=',$branch->id)->where('status','=',1)->whereBetween('received_date',[$first_day_this_month,$last_day_this_month])->get();
				$total_paid_before_advance = $this_month_collections_before_adv[0]->bef_adv_total_paid;
				$current_month_collection += $total_paid + $penalty_amount + $total_paid_advance + $total_paid_before_advance;
				$group_ato = Group::where(array('branch_id'=>$branch->id,'group_status'=>0,'is_first_auction_complete'=>0))->pluck('scheme_id');
				foreach($group_ato as $ato) {  
					$scheme_id = $ato;  
					$chitvalue = Scheme::where('id','=',$scheme_id)->get();  
					if(count($chitvalue)>0) { 
						$auction_turn_over += $chitvalue[0]->chit_value;
					}
				} 
				$bidding_oustanding_monthly = BiddingDetail::where('branch_id','=',$branch->id)->sum('pending_release');
				$monthly_bidding_outstanding += $bidding_oustanding_monthly; 
				$bidding_amount_monthly = BiddingDetail::where('branch_id','=',$branch->id)->sum('bidding_amount');
				$monthly_bidding_amount += $bidding_amount_monthly;	 
				$bidding_payable_monthly = BiddingDetail::where('branch_id','=',$branch->id)->sum('customer_released');
				$monthly_bidding_payable += $bidding_payable_monthly;	
				$over_all_collections = Receipt::selectRaw('sum(received_amount) as total_paid, sum(penalty) as penalty_amount')->where('branch_id','=',$branch->id)->where('status','=',1)->where('payment_type_id','=',1)->get();
				$overall_total_paid = $over_all_collections[0]->total_paid;
				$overall_penalty_amount = $over_all_collections[0]->penalty_amount;
				$overall_collections_adv = AdvanceReceipt::selectRaw('sum(receipt_amount) as adv_total_paid')->where('branch_id','=',$branch->id)->where('status','=',1)->where('payment_type_id','=',1)->get();
				$overall_total_paid_advance = $overall_collections_adv[0]->adv_total_paid;
				$overall_collections_before_adv = BeforeEnrolReceipt::selectRaw('sum(amount) as bef_adv_total_paid')->where('branch_id','=',$branch->id)->where('status','=',1)->where('payment_type_id','=',1)->get();
				$overall_total_paid_before_advance = $overall_collections_before_adv[0]->bef_adv_total_paid; 
				$cash_in_hand += $overall_total_paid + $overall_penalty_amount + $overall_total_paid_advance + $overall_total_paid_before_advance;
			}
			$dashboard['running_groups_count'] = $running_groups_count;
			$dashboard['vacant_groups_count'] = $vacant_groups_count;
			$dashboard['new_groups_count'] = $new_groups_count;
			$dashboard['customers_count'] = $customers_count;
			$dashboard['total_bidding_oustanding'] = $total_bidding_oustanding;
			$dashboard['current_month_collection'] = $current_month_collection;
			$dashboard['auction_turn_over'] = $auction_turn_over;
			$dashboard['monthly_bidding_outstanding'] = $monthly_bidding_outstanding;
			$dashboard['monthly_bidding_amount'] = $monthly_bidding_amount;
			$dashboard['monthly_bidding_payable'] = $monthly_bidding_payable;
			$dashboard['total_cash_hand'] = $cash_in_hand;
			$dashboard['total_cash_bank'] = 0;
			$dashboard['collection_outstanding'] = 0;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $dashboard;
            return response()->json($response,200);
		}
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        } 
    }
	
	// public function accounts_report(Request $request) {
    	// try { 
        	// $cond = [];
            // $end_date='';
            // $start_date = '';
        	// if(isset($request->branch_id)){
        		// $cond['branch_id'] = $request->branch_id; 
        	// }
            // $receipt = Receipt::selectRaw('*,sum(received_amount) as total_paid,sum(penalty) as penalty_amount')->where($cond)->groupby('payment_type_id')->get();
          	// $inst_det = [];
		  	// foreach($receipt as $receipts) {
			  	// $accounts = [];
				// if($receipts->payment_type_id == 1) {
					// $accounts['total_paid_cash'] = $receipts->total_paid;
					// $accounts['total_paid_penalty_cash'] = $receipts->penalty_amount;
				// } 
				// else if($receipts->payment_type_id = 2) {
					// $accounts['total_paid_cheque'] = $receipts->total_paid;
					// $accounts['total_paid_penalty_cheque'] = $receipts->penalty_amount;
				// } 
				// else if($receipts->payment_type_id = 3) {
				    // $accounts['total_paid_dd'] = $receipts->total_paid;
				    // $accounts['total_paid_penalty_dd'] = $receipts->penalty_amount;
				// } 
				// else if($receipts->payment_type_id = 4) { 
				    // $accounts['total_paid_rtgs'] = $receipts->total_paid;
				    // $accounts['total_paid_penalty_rtgs'] = $receipts->penalty_amount;
				// } 
				// else if($receipts->payment_type_id = 5) { 
				    // $accounts['total_paid_card'] = $receipts->total_paid;
				    // $accounts['total_paid_penalty_card'] = $receipts->penalty_amount;
				// }
		  	// }
            // $response['status'] = 'Success';
            // $response['msg'] = "";
            // $response['data'] = $receipt;
            // return response()->json($response,200);
        // }
        // catch(\Exception $e) {
            // $response['status'] = 'Error';
            // $response['msg'] = \Lang::get('api.global_error');
            // return response()->json($response, 401);
        // }
    // }
	
	public function get_commitment_receipt_details(Request $request) {
        try {
            $cr_date = date("Y-m-d");
            //enrollment id
            $recdate = '';
            $s_date = ($request->recdate['year']).'/'.sprintf('%02d',($request->recdate['month'])).'/'.sprintf('%02d',($request->recdate['day']));
        	$rec_date = date('Y-m-d',strtotime($s_date));
        	//$rec_date = $request->recdate;
            $enrl_id = $request->enrolid;
            if($rec_date == $cr_date) 
            {			
	            $chit_details = $chit_details = ChitDetail::where('id','=',$enrl_id)->orderBy('id','desc')->get();	
				$response_data = [];
				foreach($chit_details as $cd) { 
					$enrollment_id = $cd->id;
			        $branch_id = $cd->branch_id;
			        $customer_id = $cd->customer_id;
					$cd->scheme_info = isset($cd->scheme_det) ? ($cd->scheme_det) : [];
					$slab_types = SlabDue::where('id',$cd->slab_id)->get();   
					$slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
					$cd->slab_name = $slab_name;
					//total_installment_amount
					$total_installment_amount = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
					$cd->overall_installment_amount = $total_installment_amount;
					
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
					$bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;
					//pending_amount 				
					$pending_amount = $total_installment_amount - $paid_amount;
					$cd->overall_pending_amount = $pending_amount;
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   	//auction wise details 
					$auc_wise_inst_amts = CommitmentAuction::where('enrollment_id','=',$enrollment_id)
						->orderBy('installment_no', 'asc')
						->get();
					$i=0;
					$install_det = [];
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0; 
					$inst_amt = 0;
					$penalty =0;
					$bonus =0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];
						$inst_amt = isset($auc_wise_inst_amt->due_amount) ? ($auc_wise_inst_amt->due_amount) : 0;
	                    $auction_id = isset($auc_wise_inst_amt->id) ? ($auc_wise_inst_amt->id) : 0;
	                    $installment_no  = isset($auc_wise_inst_amt->installment_no) ? ($auc_wise_inst_amt->installment_no) : 0;
	                    $auc_date_inst  = isset($auc_wise_inst_amt->auction_date) ? ($auc_wise_inst_amt->auction_date) : 0;
	                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id,'auction_id'=>$auction_id, 'status' => 1])->get();
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
						else if($cd->branch_det->bonus_days!=0 && $cd->branch_det->bonus!=0)
						{
						  $bonus_days = $cd->branch_det->bonus_days;
						  $bonus_percentage = $cd->branch_det->bonus;              
						}    
						else
						{
						  $bonus_days = 0;
						  $bonus_percentage = 0;
						} 			
						
						$bid = CommitmentAuction::Where('enrollment_id',$enrollment_id)->max('id');
						$check_auction = CommitmentAuction::where('id',$bid)->get();
						$max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : "0000-00-00";

						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
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
						  	$bonus =0;
							$cust_det['bonus'] = 0;
						  }
						}
						else
						{
							$bonus  =0;
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
	                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
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
					$cust_det['auction_date'] = date('d-m-Y',strtotime($auc_date_inst));
					$cust_det['due_date'] = ($auc_date_inst && $auc_date_inst != '0' && $auc_date_inst != '0000-00-00')
						? date('d/m/Y', strtotime($auc_date_inst))
						: '';
					$cust_det['inst_amt_wise'] = round($inst_amt);
					$cust_det['amount_paid'] = $amount_paid;
					$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
					$cust_det['pending_days'] = $pending_days;
					$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
					$cust_det['bonus_inst_wise'] = 0;
					$cust_det['penalty_inst_wise'] = 0;
					$cust_det['discount_inst_wise'] = 0;
					$cust_det['discount_penalty_wise'] = 0;
					$cust_det['cancel_dividend'] = 100;
					$cust_det['auction_id'] = $auction_id;
					if($ins_amt_wise_rec>0) {
						array_push($install_det,$cust_det);
					} 
				    $total_penalty_amounts += $penalty;
					$total_bonus_amounts += $bonus;
				    }; 
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$cd->pending_details = $install_det;
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			} else if($rec_date != $cr_date)  
			{  
				$chit_details = $chit_details = ChitDetail::where('id','=',$enrl_id)->orderBy('id','desc')->get();	
				$response_data = [];
				foreach($chit_details as $cd) { 
					$enrollment_id = $cd->id;
			        $branch_id = $cd->branch_id;
			        $customer_id = $cd->customer_id;
					$cd->scheme_info = isset($cd->scheme_det) ? ($cd->scheme_det) : [];
					$cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
					//total_installment_amount
					$total_installment_amount = CommitmentAuction::where('enrollment_id','=',$enrollment_id)->sum('due_amount');
					$cd->overall_installment_amount = $total_installment_amount;
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
					$bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;
					//pending_amount 				
					$pending_amount = $total_installment_amount - $paid_amount;
					$cd->overall_pending_amount = $pending_amount;
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   	//auction wise details 
					$auc_wise_inst_amts = CommitmentAuction::where('enrollment_id','=',$enrollment_id)
						->orderBy('installment_no', 'asc')
						->get();
					$i=0;
					$install_det = [];
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0; 
					$inst_amt = 0;
					$penalty =0;
					$bonus =0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];
						$inst_amt = isset($auc_wise_inst_amt->due_amount) ? ($auc_wise_inst_amt->due_amount) : 0;
	                    $auction_id = isset($auc_wise_inst_amt->id) ? ($auc_wise_inst_amt->id) : 0;
	                    $installment_no  = isset($auc_wise_inst_amt->installment_no) ? ($auc_wise_inst_amt->installment_no) : 0;
	                    $auc_date_inst  = isset($auc_wise_inst_amt->auction_date) ? ($auc_wise_inst_amt->auction_date) : 0;
	                    $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id,'auction_id'=>$auction_id, 'status' => 1])->get();
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
						$ins_amt_wise_rec = $inst_amt - $amount_paid;
						if($ins_amt_wise_rec !=$inst_amt && $i==0 && $inst_amt_no !=0) {
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
						}
						// Bonus days and percentage by heirrachy over all 
						$up_branch = UpdateBranch::where('branch_id',$branch_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_branches = UpdateBranch::where('id',$up_branch)->get();
						if(count($update_branches)>0)
						{
							$branch_bonus_days = isset($update_branches[0]->bonus_days) ? ($update_branches[0]->bonus_days) : 0;
							$branch_bonus_percentage = isset($update_branches[0]->bonus) ? ($update_branches[0]->bonus) : 0;
							$branch_penalty_days = isset($update_branches[0]->penalty_days) ? ($update_branches[0]->penalty_days) : 0;
		                	$branch_penalty_percentage = isset($update_branches[0]->branch_wise_penalty) ? ($update_branches[0]->branch_wise_penalty) : 0;
						}
						else
						{
							$branch_bonus_days = 0;
							$branch_bonus_percentage = 0;
							$branch_penalty_days =0;
							$branch_penalty_percentage =0;
						}						
							
						// Customer - old bonus, penalty calculation
						$up_customer = UpdateCustomer::where('update_customer_id',$customer_id)->where('edit_date','<=',$rec_date)->max('id');
						$update_customers = UpdateCustomer::where('id',$up_customer)->get(); 
						if(count($update_customers)>0)
						{
							$cust_bonus_days = isset($update_customers[0]->bonus_days) ? ($update_customers[0]->bonus_days) : 0;
							$cust_bonus_percentage = isset($update_customers[0]->customer_bonus) ? ($update_customers[0]->customer_bonus) : 0;
							$cust_penalty_days = isset($update_customers[0]->penalty_days) ? ($update_customers[0]->penalty_days) : 0;
							$cust_penalty_percentage = isset($update_customers[0]->customer_penalty_interest) ? ($update_customers[0]->customer_penalty_interest) : 0;
						}
						else
						{	
							$cust_bonus_days =0;
							$cust_bonus_percentage = 0;
							$cust_penalty_days = 0;
							$cust_penalty_percentage =0;
						}

						// Bonus days and percentage by heirrachy over all 
						if($cust_bonus_days!=0 && $cust_bonus_percentage!=0)
						{
						  $bonus_days = $cust_bonus_days;
						  $bonus_percentage = $cust_bonus_percentage;
						}    
						elseif($branch_bonus_days!=0 && $branch_bonus_percentage!=0)
						{
						  $bonus_days = $branch_bonus_days;
						  $bonus_percentage = $branch_bonus_percentage;
						}    
						else
						{
						  $bonus_days = 0;
						  $bonus_percentage = 0;
						} 		
						
						$bid = CommitmentAuction::Where('enrollment_id',$enrollment_id)->max('id');
						$check_auction = CommitmentAuction::where('id',$bid)->get();
						$max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : "0000-00-00";
						
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
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
						  	$bonus =0;
							$cust_det['bonus'] = 0;
						  }
						}
						else
						{
							$bonus  =0;
						  	$cust_det['bonus'] = 0;
						}						
						
					// Penalty Calculation installment wise
	                if($cust_penalty_days!=0 )
	                {
	                  	$inst_penalty_days = $cust_penalty_days;
	                  	$inst_penalty_percentage = $cust_penalty_percentage;
	                }	                
	                elseif($branch_penalty_days!=0)
	                {
	                 	$inst_penalty_days = $branch_penalty_days;
	                  	$inst_penalty_percentage = $branch_penalty_percentage;
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
	                  	if($cd->prized_status==0)
	                  	{
	                    	$non_prize = isset($update_branches[0]->non_prize_subscriber_penalty) ? ($update_branches[0]->non_prize_subscriber_penalty) : 0;
	                    	$pend_days_int = $pending_days * ($non_prize/ 100);
	                    	$current_month_days = date('t'); 
	                    	$total_days_int = $pend_days_int / $current_month_days;
	                    	$penalty = $total_days_int * $ins_amt_wise_rec;
	                    	$cust_det['penalty_amounts'] = strval(round($penalty));
	                  	} 
	                  	else
	                  	{               
	                    	$prize = isset($update_branches[0]->prize_subscriber_penalty) ? ($update_branches[0]->prize_subscriber_penalty) : 0;
	                    	$pend_days_int = $pending_days * ($prize / 100);
	                    	$current_month_days = date('t'); 
	                    	$total_days_int = $pend_days_int / $current_month_days;
	                    	$penalty = $total_days_int * $ins_amt_wise_rec;
	                    	$cust_det['penalty_amounts'] = strval(round($penalty));
	                  	}                        		  
	                }      
					
					$cust_det['installment_no'] = $installment_no;
					$cust_det['auction_date'] = date('d-m-Y',strtotime($auc_date_inst));
					$cust_det['due_date'] = ($auc_date_inst && $auc_date_inst != '0' && $auc_date_inst != '0000-00-00')
						? date('d/m/Y', strtotime($auc_date_inst))
						: '';
					$cust_det['inst_amt_wise'] = round($inst_amt);
					$cust_det['amount_paid'] = $amount_paid;
					$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
					$cust_det['pending_days'] = $pending_days;
					$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
					$cust_det['bonus_inst_wise'] = 0;
					$cust_det['penalty_inst_wise'] = 0;
					$cust_det['discount_inst_wise'] = 0;
					$cust_det['discount_penalty_wise'] = 0;
					$cust_det['auction_id'] = $auction_id;
					if($ins_amt_wise_rec>0) {
						array_push($install_det,$cust_det);
					} 
				    $total_penalty_amounts += $penalty;
					$total_bonus_amounts += $bonus;
				    }; 
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$cd->pending_details = $install_det;
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			}
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);
		}
		catch(\Exception $e) { 
			$response['status'] = 'Error';
			$response['msg'] = \Lang::get('api.global_error');
			$response['msg'] = $e;
			return response()->json($response, 401);
		}
	}

	public function store_bp_adjust_receipt(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'group_id' => 'required',
			'employee_id' => 'required',
			'enrollment_id'=> 'required',
			//'adjust_id'=> 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->Installments)) 
				{  
					$count = count($request->Installments);
					$receipt_no = '';
					for($i=0; $i<$count; $i++)
					{	
				        
						$receipt = new Receipt(); 
						$receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$receipt->other_branch = isset($request->other_branch) ? ($request->other_branch) : 0;
						$receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
						$receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
						$group_id = ChitDetail::where('id','=',$request->enrollment_id)->pluck('group_id');
						$receipt->group_id = $group_id[0];
						$receipt->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : '';
						$receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
						$employee_branch_id = Employee::where('id','=',$receipt->employee_id)->pluck('branch_id');
						$receipt->employee_branch_id = $employee_branch_id[0];
						$receipt->adjust_id = isset($request->from_enrollment_id) ? ($request->from_enrollment_id) : 0;
						$receipt->amount = isset($request->amount) ? ($request->amount) : 0;
						if(isset($request->receipt_date)) {
							$r_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
							$receipt->receipt_date = date('Y-m-d',strtotime($r_date)); 
						}
						else {
							$receipt->receipt_date = '0000-00-00';
						}
						$receipt->type_of_collection = 'b.p.adj.receipt';
						$receipt->payment_type_id = 6;
						$receipt->receipt_mode = 'ERP';
						$receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
						if(isset($request->accounts_date)) {
							$a_date = ($request->accounts_date['year']).'-'.sprintf('%02d',($request->accounts_date['month'])).'-'.sprintf('%02d',($request->accounts_date['day'])); 
							$receipt->accounts_date = date('Y-m-d',strtotime($a_date)); 
						}
						else {
							$receipt->accounts_date = date('Y-m-d');
						}
						if($request->tenant_id == 47){
								
							$receipt->alt_rec_no = isset($request->alt_rec_no) ? ($request->alt_rec_no) : 0;	
							}
							if($i == 0){
						$receipt->penalty = $request->penalty_amount_tot;	
						} else {
						$receipt->penalty = 0;
						}
						$receipt->printed = isset($request->printed) ? ($request->printed) : 0;
						$receipt->auction_id = isset($request->Installments[$i]['auction_id']) ? $request->Installments[$i]['auction_id'] : '';
						$receipt->installment_no = isset($request->Installments[$i]['installment_no']) ? $request->Installments[$i]['installment_no'] : '';
						$receipt->pending_days = isset($request->Installments[$i]['pending_days']) ? $request->Installments[$i]['pending_days'] : '';
						//$receipt->penalty = isset($request->Installments[$i]['penalty_inst_wise']) ? $request->Installments[$i]['penalty_inst_wise'] : '';
						$receipt->bonus = isset($request->Installments[$i]['bonus_inst_wise']) ? $request->Installments[$i]['bonus_inst_wise'] : '';
						$receipt->discount = isset($request->Installments[$i]['discount_inst_wise']) ? $request->Installments[$i]['discount_inst_wise'] : '';
						$receipt->received_amount = isset($request->Installments[$i]['received_amount']) ? $request->Installments[$i]['received_amount'] : '';
						$receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
						$receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
						$receipt->status = isset($request->status) ? ($request->status) : 1;
						//$receipt->alt_rec_no = isset($request->alt_rec_no) ? ($request->alt_rec_no) : 0;	

						if($receipt->amount > 0 || $receipt->other_amount)
						{
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
					}
					
					if($request->tenant_id == 47){	
					$othercharge=new OtherCharge(); 
					$othercharge->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
					$othercharge->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
					$othercharge->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$othercharge->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
					$othercharge->receipt_no = $receipt_no;
					$othercharge->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
					$othercharge->charge_type_id = isset($request->charge_type_id) ? ($request->charge_type_id) : 0;
					if(isset($request->receipt_date)) {
						$s_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
						$othercharge->received_date = date('Y-m-d',strtotime($s_date)); 
					}
					else {
							// $othercharge->received_date = '0000-00-00';
					} 
					$othercharge->receipt_no = isset($request->alt_rec_no) ? ($request->alt_rec_no) : 0;	
					$othercharge->amount = isset($request->other_amount) ? ($request->other_amount) : 0 ;
					$othercharge->payment_type_id = 6;
					$othercharge->created_by = isset($request->created_by) ? ($request->created_by) : 0;
					$othercharge->status = isset($request->status) ? ($request->status) : 1;
					$othercharge->save();
					}							
					
					$bidding_id = BiddingDetail::where('enrollment_id','=',$request->from_enrollment_id)->get();
					
					$bidding = BiddingDetail::find($bidding_id[0]->id);
					
					$pending_release = $bidding->pending_release;
					$total_release = $bidding->total_release_amount;
					$pending_due_amount = $bidding->pending_due_amount;
					if($request->tenant_id == 47){
					$receipt_amount = $receipt->amount + $request->other_amount;	
					} else{
					$receipt_amount = $receipt->amount;
					}
					
					$bidding->pending_release = $pending_release - $receipt_amount;
					$bidding->total_release_amount = $total_release - $receipt_amount;
					$bidding->pending_due_amount = $pending_due_amount + $receipt_amount;
					$bidding->save();				
					
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_receipt_added');
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
				return response()->json($response, 401);
			}
		}
	}


    Public function get_prized_customer_details(Request $request)
    {
    	try
    	{
    		$cond = $request->conditions;  // group_id
    		$bidding = BiddingDetail::where($cond)->where('pending_release','!=',0)->get();
    		$response_data = [];
    		foreach($bidding as $bid)
    		{   
    			$bid->customer_info = ($bid->customer_det) ? ($bid->customer_det) : [];
    			$bid->enrollment_info = ($bid->enrollment_det) ? ($bid->enrollment_det) : [];  
    		}    		
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $bidding;
			return response()->json($response,200);    		
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

    public function get_pending_release_amount(Request $request)
    {
    	try
    	{
    		$cond = $request->conditions;  // enrollment_id
    		$bid_pending_release = BiddingDetail::where($cond)->get();    		
			$response_data['pending_release_amount'] = isset($bid_pending_release[0]->pending_release) ? ($bid_pending_release[0]->pending_release) : 0;
			if($bid_pending_release)
			{
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $response_data;
				return response()->json($response,200);
			}    		
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

   public function store_advance_receipt(Request $request)
{
    $validator = Validator::make($request->all(), [
        'tenant_id' => 'required',
        'branch_id' => 'required',
        'customer_id' => 'required',
        'employee_id' => 'required',
        'enrollment_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'Error',
            'msg' => $validator->errors()->first()
        ]);
    }

    DB::beginTransaction();
    try {
        $customer_id = isset($request->customer_id[0]['customer_id']) ? $request->customer_id[0]['customer_id'] : 0;

        $advance = new AdvanceReceipt();
        $advance->tenant_id         = $request->tenant_id;
        $advance->branch_id         = $request->branch_id;
        $advance->customer_id       = $customer_id;
        $advance->enrollment_id     = $request->enrollment_id;
        $advance->employee_id       = $request->employee_id;
        $advance->employee_branch_id = Employee::find($request->employee_id)->branch_id ?? 0;
        $advance->receipt_amount    = $request->receipt_amount ?? 0;
        $advance->receipt_date      = $this->formatDate($request->receipt_date);
        $advance->payment_type_id   = $request->payment_type_id ?? 0;
        $advance->debit_to          = $request->debit_to ?? '';
        $advance->cheque_no         = $request->cheque_no ?? '';
        $advance->cheque_date       = $this->formatDate($request->cheque_date);
        $advance->cheque_clear_return_date = $this->formatDate($request->cheque_clear_return_date);
        $advance->bank_name_id      = $request->bank_id ?? 0;
        $advance->branch_name       = $request->branch_name ?? '';
        $advance->transaction_no    = $request->transaction_no ?? '';
        $advance->transaction_date  = $this->formatDate($request->transaction_date);
        $advance->receipt_mode      = "ERP";
        $advance->receipt_time      = $request->receipt_time ?? now()->format('H:i:s');
        $advance->printed           = $request->printed ?? 0;
        $advance->remarks           = $request->remarks ?? '';
        $advance->created_by        = $request->created_by ?? 0;
        $advance->status            = ($request->payment_type_id == 2) ? 2 : ($request->status ?? 1);

        if ($advance->receipt_amount <= 0) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Receipt amount must be greater than 0'
            ]);
        }

        $advance->save();
        $advance->receipt_no = 'ADV-' . str_pad($advance->id, 5, '0', STR_PAD_LEFT);
        $advance->save();

        // Post to accounts
        $this->postAdvanceAccountExpenses($advance);

        DB::commit();
        return response()->json([
            'status' => 'Success',
            'receipt_no' => $advance->receipt_no,
            'msg' => 'Advance receipt added successfully'
        ]);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'status' => 'Error',
            'msg' => 'Sorry!! Something went wrong!',
            'error' => $e->getMessage()
        ]);
    }
}

private function postAdvanceAccountExpenses($receipt)
{
    $amount = $receipt->receipt_amount;
    $voucher_no = $receipt->receipt_no;
    $entry_date = $receipt->receipt_date;

    $customer = Customer::find($receipt->customer_id);
    $customer_name = $customer->name . ' ' . $customer->last_name;

    $debit_account_head = $this->getDebitAccountHeadId($receipt->payment_type_id, $receipt->bank_name_id);
    $credit_account_head = AccountHead::where('name', 'Chit Fund Collection')->where('tenant_id', $receipt->tenant_id)->value('id');

    if (!$credit_account_head || !$debit_account_head) {
        throw new \Exception("Invalid account head setup.");
    }

    // Debit Entry
    AccountExpense::create([
        'tenant_id' => $receipt->tenant_id,
        'branch_id' => $receipt->branch_id,
        'entry_date' => $entry_date,
        'voucher_no' => $voucher_no,
        'debit_account_head' => $debit_account_head,
        'debit_amount' => $amount,
        'credit_account_head' => 0,
        'credit_amount' => 0,
        'remarks' => "Advance amount received from {$customer_name}",
        'status' => 1,
    ]);

    // Credit Entry
    AccountExpense::create([
        'tenant_id' => $receipt->tenant_id,
        'branch_id' => $receipt->branch_id,
        'entry_date' => $entry_date,
        'voucher_no' => $voucher_no,
        'debit_account_head' => 0,
        'debit_amount' => 0,
        'credit_account_head' => $credit_account_head,
        'credit_amount' => $amount,
        'remarks' => "Advance amount received from {$customer_name}",
        'status' => 1,
    ]);
}
 

 
private function getDebitAccountHeadId($payment_type_id, $bank_id = null)
{
    // 1 = cash, 2 = cheque, 3 = UPI, 4 = online
    if ($payment_type_id == 1) {
        return AccountHead::where('name', 'Cash')->value('id');
    }
 
    if ($bank_id) {
        $bank_account = BankDetail::find($bank_id)->account_number ?? null;
        return AccountHead::where('bank_account_number', $bank_account)->value('id');
    }

    return null;
}


    Public function get_available_advance_details(Request $request)
    {
    	try
    	{
			$penalty = 0;
    		$receipts = Receipt::selectRaw('*,sum(received_amount)as total_received,sum(penalty)as penalty')->where(array('enrollment_id'=>$request->enrollment_id,'payment_type_id'=>7))->get();
    		$total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
    		$penalty = isset($receipts[0]->penalty) ? ($receipts[0]->penalty) : "0";
    		$advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$request->enrollment_id,'status'=>1))->get();
    		$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
    		$response_data['available_advance'] = $total_advance - $total_received - $penalty;
    		$response['status'] = 'Success';
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
    
	public function list_advanced_receipts(Request $request) {
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
					$receipts=AdvanceReceipt::where($cond)->where($cond1)->get();
				}
				if($request->branch_id ==0)
				{
					$receipts=AdvanceReceipt::where($cond)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=AdvanceReceipt::where($cond)->where($cond1)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=AdvanceReceipt::where($cond)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=AdvanceReceipt::where($cond)->where($cond1)->whereBetween('receipt_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=AdvanceReceipt::where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->get();
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
                $receipt->scheme_info = isset($receipt->scheme_det) ? ($receipt->scheme_det) : [];
				$receipt->group_info =[];
				$receipt->enrollment_info =[];
				if(isset($receipt->enrollment_det)){
				 $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
				 $receipt->group_info = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det) : [];	
				}
				// Group Name
				$group_name = '';
				if($receipt->enrollment_det->chit_type != 3)
				{
					$group_name = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det->group_name.'/'. $receipt->enrollment_det->ticket_no) : "";
				}
				if($receipt->enrollment_det->chit_type == 3)
				{
					$slab_types = SlabDue::where('id',$receipt->enrollment_det->slab_id)->get();   
		            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
		            $group_name = "Commitment"."/".$slab_name;
				}
				
				$receipt->group_name = $group_name;
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];	
                $receipt->collection_employee_name = isset($receipt->employee_det) ? ($receipt->employee_det->first_name.' '.$receipt->employee_det->last_name) : "";			
				// Other branch Name
				$chit_details = ChitDetail::where('id',$receipt->enrollment_id)->get();
				$receipt->collected_branch_name = isset($chit_details[0]->branch_det) ? ($chit_details[0]->branch_det->branch_name) : "";

				$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '';
				$receipt->accounts_date = ($receipt->accounts_date!=0) ? date('d/m/Y',strtotime($receipt->accounts_date)) : '';
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
                $receipt_amount = isset($receipt->receipt_amount) ? ($receipt->receipt_amount) : 0;
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
	
	public function available_advanced_receipts(Request $request) {
        try {  
            $cond = [];
            $cond1 =[];
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if($request->branch_id >0 ){
				$cond1['branch_id'] = $request->branch_id;
			}
			if(isset($request->group_id)){
				$cond['group_id'] = $request->group_id; 
			}
			if(isset($request->customer_id)){
				$cond['customer_id'] = $request->customer_id; 
			}
			
            $available_advances=ChitDetail::where($cond)->where($cond1)->where('chit_type','!=',1)->where('customer_id','!=',1)->get(); 
             
            if($request->branch_id == 0 ){
				$available_advances=ChitDetail::where($cond)->where('chit_type','!=',1)->where('customer_id','!=',1)->get(); 
			}

            $i=1;  
			if(count($available_advances)>0)
			{  
                $grand_avail_advance_amt = 0;
                $tot_avail_advance = 0;     
                $grand_adj_advance_amt = 0;
                $tot_adj_advance = 0;     
                $grand_advance_amt = 0;
                $tot_advance = 0;   
	            foreach($available_advances as $available_advance) 
	            {
	                $available_advance->sno = $i;
					$available_advance->tenant_info = isset($available_advance->tenant_det) ? ($available_advance->tenant_det) : [];
	                $available_advance->branch_name = isset($available_advance->branch_det->branch_name) ? ($available_advance->branch_det->branch_name) : [];
	                $available_advance->group_name = isset($available_advance->group_det->group_name) ? ($available_advance->group_det->group_name) : [];
					$available_advance->ticket_no = isset($available_advance->ticket_no) ? ($available_advance->ticket_no) : [];
					$available_advance->customer_name = isset($available_advance->customer_det->name) ? ($available_advance->customer_det->name) : [];
					$advance_amount = AdvanceReceipt::where(array('enrollment_id'=>$available_advance->id,'status'=>1))->sum('receipt_amount');
					$advance_adjustment = Receipt::selectRaw('*,sum(received_amount) as total_paid, sum(penalty) as total_penalty')->where(array('enrollment_id'=>$available_advance->id,'payment_type_id'=>7))->get();
					$receipt_amt = isset($advance_adjustment[0]->total_paid) ? ($advance_adjustment[0]->total_paid) : 0;
					$penalty_amt = isset($advance_adjustment[0]->total_penalty) ? ($advance_adjustment[0]->total_penalty) : 0;
					$total_amount = $receipt_amt + $penalty_amt;
					$available_advance_amount = $advance_amount - $total_amount;
					$available_advance->available_advance_amount = $available_advance_amount;
					$available_advance->advance_amount = $advance_amount;
					$available_advance->advance_adjustment = $total_amount;
	                $tot_advance += $advance_amount;
	                $tot_adj_advance += $total_amount;
	                $tot_avail_advance += $available_advance_amount;
	                $i++;
	            } 
	            $grand_avail_advance_amt +=$tot_avail_advance;
	            $grand_adj_advance_amt +=$tot_adj_advance;
	            $grand_advance_amt +=$tot_advance;

	            $avail_advance_format =0;
	            $adj_advance_format =0;
	            $grand_advance_format =0;

	            if($grand_avail_advance_amt > 0)
	            {   
	                $num = $grand_avail_advance_amt;
	                $avail_advance_format = $this->moneyFormatIndia($num);  
	            } 
	            if($grand_adj_advance_amt > 0)
	            {   
	                $num = $grand_adj_advance_amt;
	                $adj_advance_format = $this->moneyFormatIndia($num);  
	            }
	            if($grand_advance_amt > 0)
	            {   
	                $num = $grand_advance_amt;
	                $grand_advance_format = $this->moneyFormatIndia($num);  
	            }
	            $response['grand_avail_advance_amt'] = $avail_advance_format;
	            $response['grand_adj_advance_amt'] = $adj_advance_format;
	            $response['grand_advance_amt'] = $grand_advance_format;
	            $response['status'] = 'Success';
	            $response['msg'] = "";
	            $response['data'] = $available_advances;
	            return response()->json($response,200);
			}
			else {
				$available_advances = [];
	            $response['grand_avail_advance_amt'] = 0;
	            $response['grand_adj_advance_amt'] = 0;
	            $response['grand_advance_amt'] = 0;
                $response['status'] = 'Success';
				$response['msg'] = \Lang::get('api.no_data_found');
				$response['data'] = $available_advances;
		        return response()->json($response, 200);	
			}
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	
	 Public function get_auctionwise_receipt(Request $request)
    {
    	try
    	{ // id
			$cond = $request->conditions; 
			$response_data = [];
            $chit = ChitDetail::where($cond)->get();
            foreach($chit as $c)
            {            	
	            $group_id = isset($c->group_id) ? ($c->group_id) : 0;
	            $enrollment_id = isset($c->id) ? ($c->id) : 0; 
				$ticket_type = $c->ticket_type;
	            if($c->chit_type != 3)
	            {
					
		            $auction_details = BiddingDetail::selectRaw('*,sum(current_installment_amount) as current_installment_amount')->where('group_id',$group_id)->groupby('installment_no')->orderBy('id','asc')->get();
					foreach($auction_details as  $auction_detail) 
					{				
						$install_det = [];					
						$auction_id = $auction_detail->id;
						$installment_no = $auction_detail->installment_no;
						
						$inst_amt_1 = $auction_detail->current_installment_amount;
						$auction_detail->installment_amount = ($inst_amt_1 * $ticket_type);
						//next line added for customer ledger showing
						$auction_detail->current_installment_amount = ($inst_amt_1 * $ticket_type);
						$auction_detail->auction_date = date('d-m-Y',strtotime($auction_detail->auction_date));
						$receipt_details = Receipt::selectRaw('*,sum(received_amount) as received_amount')->where('installment_no',$installment_no)->where('enrollment_id',$enrollment_id)->groupby('receipt_no')->orderBy('id','asc')->get();
						
					    foreach($receipt_details as $receipt_detail) {
							
							$rect_det = [];	
							$rect_det['receipt_date'] = date('d-m-Y',strtotime($receipt_detail->receipt_date)); 
							$payment_method = PaymentType::where('id',$receipt_detail->payment_type_id)->pluck('payment_name');
							$payment_type = $payment_method[0];
							$rect_det['receipt_no'] = $receipt_detail->receipt_no . ' / ' . $payment_type;
							$rect_det['receipt_amount'] = $receipt_detail->received_amount;
							$rect_det['receipt_penalty'] = $receipt_detail->penalty + $receipt_detail->cancel_dividend_amount;
							$rect_det['receipt_bonus'] = $receipt_detail->bonus;
							$rect_det['receipt_divident'] = $receipt_detail->divident;
							
							$rect_det['net_collected_receipt_amount'] = $receipt_detail->received_amount + $receipt_detail->penalty + $receipt_detail->cancel_dividend_amount;
							array_push($install_det,$rect_det);
						}
						$auction_detail->auction_details = $install_det;
						array_push($response_data,$auction_detail);
					}             	
	            }
	            if($c->chit_type ==3)
	            {
	            	$auction_details = CommitmentAuction::where('enrollment_id',$enrollment_id)->orderBy('id','asc')->get();
					foreach($auction_details as  $auction_detail) 
					{				
						$install_det = [];					
						$auction_id = $auction_detail->id;
						$auction_detail->installment_no = $auction_detail->installment_no;
						$auction_detail->auction_date = ($auction_detail->auction_date && $auction_detail->auction_date != '0000-00-00')
							? date('d-m-Y', strtotime($auction_detail->auction_date))
							: '';
						$receipt_details = Receipt::where('auction_id',$auction_id)->where('enrollment_id',$enrollment_id)->orderBy('id','asc')->get();
					    foreach($receipt_details as $receipt_detail) {
							$rect_det = [];	
							$rect_det['receipt_date'] = ($receipt_detail->receipt_date && $receipt_detail->receipt_date != '0000-00-00')
								? date('d-m-Y', strtotime($receipt_detail->receipt_date))
								: '';
							$payment_method = PaymentType::where('id',$receipt_detail->payment_type_id)->pluck('payment_name');
							$payment_type = count($payment_method) > 0 ? $payment_method[0] : 'Cash';
							$rect_det['receipt_no'] = $receipt_detail->receipt_no . ' / ' . $payment_type;
							$rect_det['receipt_amount'] = $receipt_detail->received_amount;
							$rect_det['receipt_penalty'] = $receipt_detail->penalty;
							$rect_det['receipt_bonus'] = $receipt_detail->bonus;
							$rect_det['receipt_divident'] = $receipt_detail->divident;
							$rect_det['net_collected_receipt_amount'] = $receipt_detail->received_amount + $receipt_detail->penalty;
							array_push($install_det,$rect_det);
						}
						$auction_detail->auction_details = $install_det;
						$auction_detail->current_installment_amount = $auction_detail->due_amount;
						array_push($response_data,$auction_detail);
					}
            	}
            }
			
			$response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200);
			  }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }


    public function get_enrol_details_divident_cancel(Request $request) {
        try {
            //enrollment id
            $cr_date = date("Y-m-d");
            //enrollment id
            $recdate = '';
            $s_date = ($request->recdate['year']).'/'.sprintf('%02d',($request->recdate['month'])).'/'.sprintf('%02d',($request->recdate['day']));
        	$rec_date = date('Y-m-d',strtotime($s_date));
            $enrl_id = $request->enrolid;
            if($rec_date == $cr_date) 
            {	 
	            $chit_details = ChitDetail::where('id',$enrl_id)->orderBy('id','desc')->get();		
				$response_data = [];			
				foreach($chit_details as $cd) {
					$enrollment_id = $cd->id;
			        $group_id = $cd->group_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');				
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;				

					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					$total_divid_amount =0 ;
					$total_divi_amt = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;	
						$divident_amt =0;	
									
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$divident_amt = $auc_wise_inst_amt->divident_amount;
						$auction_id = $auc_wise_inst_amt->id;
						$auc_id  = $auc_wise_inst_amt->id;
						$installment_no  = $auc_wise_inst_amt->installment_no;
						$auc_date_inst  = $auc_wise_inst_amt->auction_date;
						$rcpts = Receipt::where('enrollment_id',$enrollment_id)->where('auction_id','=',$auc_id)->where('status',1)->get();	
						$divident = 0;
						if(count($rcpts)>0)
						{							 		
							$divident = isset($rcpts[0]->cancel_dividend_amount)? ($rcpts[0]->cancel_dividend_amount) : 0;
							$total_divi_amt = $divident_amt - $divident;
						}
						else
						{
							$total_divi_amt= $total_divi_amt + $divident_amt;
						}
						$total_divide_amount = $divident_amt - $divident;
						$cd->overall_installment_amount = $total_installment_amount + $total_divi_amt;
						//pending_amount 
						$total_install_amt = $total_installment_amount + $total_divi_amt;
						$pending_amount = $total_install_amt - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;

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
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
							// && $i==0
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = date('Y-m-d');
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							//echo $pending_days;
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = date('Y-m-d');
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							//echo $pending_days;
							$i++;
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
						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date  =$check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
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
	                if($cd->customer_det->penalty_days!=0 )
	                {
	                  $inst_penalty_days = $cd->customer_det->penalty_days;
	                  $inst_penalty_percentage = $cd->customer_det->customer_penalty_interest;
	                }
	                elseif($cd->group_det->group_penalty_days!=0)
	                {
	                  $inst_penalty_days = $cd->group_det->group_penalty_days;
	                  $inst_penalty_percentage = $cd->group_det->group_base_penalty;                  
	                }
	                elseif($cd->branch_det->penalty_days!=0 )
	                {
	                  $inst_penalty_days = $cd->branch_det->penalty_days;
	                  $inst_penalty_percentage = $cd->branch_det->branch_wise_penalty;
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
						$cust_det['inst_amt_wise'] = $inst_amt;
						$cust_det['amount_paid'] = $amount_paid;
						$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
						$cust_det['cancel_dividend_amount'] = $total_divide_amount;
						$cust_det['pending_days'] = $pending_days;
						$cust_det['bonus_inst_wise'] = 0;
						$cust_det['penalty_inst_wise'] = 0;
						$cust_det['discount_inst_wise'] = 0;
						$cust_det['discount_penalty_wise'] = 0;
						$cust_det['auction_id'] = $auction_id;
						$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus + $total_divide_amount;
						if($ins_amt_wise_rec>0) {
							array_push($install_det,$cust_det);
						}	
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$response_data['total_divident_amount'] = strval(round($total_divi_amt));
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			} else if($rec_date != $cr_date) 
            {	
	            $chit_details = ChitDetail::where('id',$enrl_id)->orderBy('id','desc')->get();		
				$response_data = [];			
				foreach($chit_details as $cd) {
					$enrollment_id = $cd->id;
			        $group_id = $cd->group_id;
			        $branch_id = $cd->branch_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');				
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;				

					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					$total_divid_amount =0 ;
					$total_divi_amt = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;	
						$divident_amt =0;	
									
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$divident_amt = $auc_wise_inst_amt->divident_amount;
						$auction_id = $auc_wise_inst_amt->id;
						$auc_id  = $auc_wise_inst_amt->id;
						$installment_no  = $auc_wise_inst_amt->installment_no;
						$auc_date_inst  = $auc_wise_inst_amt->auction_date;
						$rcpts = Receipt::where('enrollment_id',$enrollment_id)->where('auction_id','=',$auc_id)->where('status',1)->get();	
						$divident =0;
						if(count($rcpts)>0)
						{							 		
							$divident = isset($rcpts[0]->cancel_dividend_amount)? ($rcpts[0]->cancel_dividend_amount) : 0;
							$total_divi_amt = $divident_amt - $divident;
						}
						else
						{
							$total_divi_amt= $total_divi_amt + $divident_amt;
						}
						$total_divide_amount = $divident_amt - $divident;
						$cd->overall_installment_amount = $total_installment_amount + $total_divi_amt;
						//pending_amount 
						$total_install_amt = $total_installment_amount + $total_divi_amt;
						$pending_amount = $total_install_amt - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;

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
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
							// && $i==0
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						}
						// Branch - old bonus, penalty calculation
						$up_branch = UpdateBranch::where('branch_id',$branch_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_branches = UpdateBranch::where('id',$up_branch)->get();
						if(count($update_branches)>0)
						{
							$branch_bonus_days = isset($update_branches[0]->bonus_days) ? ($update_branches[0]->bonus_days) : 0;
							$branch_bonus_percentage = isset($update_branches[0]->bonus) ? ($update_branches[0]->bonus) : 0;
							$branch_penalty_days = isset($update_branches[0]->penalty_days) ? ($update_branches[0]->penalty_days) : 0;
		                	$branch_penalty_percentage = isset($update_branches[0]->branch_wise_penalty) ? ($update_branches[0]->branch_wise_penalty) : 0;
						}
						else
						{
							$branch_bonus_days = 0;
							$branch_bonus_percentage = 0;
							$branch_penalty_days =0;
							$branch_penalty_percentage =0;
						}

						// Group - old bonus, penalty calculation
						$up_group = UpdateGroup::where('group_id',$group_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_groups = UpdateGroup::where('id',$up_group)->get();
						if(count($update_groups)>0)
						{
							$group_bonus_days = isset($update_groups[0]->group_bonus_days) ? ($update_groups[0]->group_bonus_days) : 0; 
							$group_bonus_percentage = isset($update_groups[0]->group_base_bonus) ? ($update_groups[0]->group_base_bonus) : 0;
							$group_penalty_days = isset($update_groups[0]->group_penalty_days) ? ($update_groups[0]->group_penalty_days) : 0;
							$group_penalty_percentage = isset($update_groups[0]->group_base_penalty) ? ($update_groups[0]->group_base_penalty) : 0;
						}
						else
						{
							$group_bonus_days =0;
							$group_bonus_percentage =0;
							$group_penalty_days =0;
							$group_penalty_percentage =0;
						}
						// Customer - old bonus, penalty calculation
						$up_customer = UpdateCustomer::where('update_customer_id',$customer_id)->where('edit_date','<=',$rec_date)->max('id');
						$update_customers = UpdateCustomer::where('id',$up_customer)->get();
						if(count($update_customers)>0)
						{
							$cust_bonus_days = isset($update_customers[0]->bonus_days) ? ($update_customers[0]->bonus_days) : 0;
							$cust_bonus_percentage = isset($update_customers[0]->customer_bonus) ? ($update_customers[0]->customer_bonus) : 0;
							$cust_penalty_days = isset($update_customers[0]->penalty_days) ? ($update_customers[0]->penalty_days) : 0;
							$cust_penalty_percentage = isset($update_customers[0]->customer_penalty_interest) ? ($update_customers[0]->customer_penalty_interest) : 0;
						}
						else
						{	
							$cust_bonus_days =0;
							$cust_bonus_percentage = 0;
							$cust_penalty_days = 0;
							$cust_penalty_percentage =0;
						}

						// Bonus days and percentage by heirrachy over all 
						if($cust_bonus_days!=0 && $cust_bonus_percentage!=0)
						{
						  $bonus_days = $cust_bonus_days;
						  $bonus_percentage = $cust_bonus_percentage;	
						}    
						elseif($group_bonus_days!=0 && $group_bonus_percentage!=0)
						{
						  $bonus_days = $group_bonus_days;
						  $bonus_percentage = $group_bonus_percentage;
						}
						elseif($branch_bonus_days!=0 && $branch_bonus_percentage!=0)
						{
						  $bonus_days = $branch_bonus_days;
						  $bonus_percentage = $branch_bonus_percentage;
						}    
						else
						{
						  $bonus_days = 0;
						  $bonus_percentage = 0;
						} 		
						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date  =$check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$rec_date])->get();
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
		                if($cust_penalty_days!=0 && $cust_penalty_percentage!=0)
		                {
		                  $inst_penalty_days = $cust_penalty_days;
		                  $inst_penalty_percentage = $cust_penalty_percentage;
		                }
		                elseif($group_penalty_days!=0 && $group_penalty_percentage!=0)
		                {
		                  $inst_penalty_days = $group_penalty_days;
		                  $inst_penalty_percentage = $group_penalty_percentage;                  
		                }
		                elseif($branch_penalty_days!=0)
		                {
		                  $inst_penalty_days = $branch_penalty_days;
		                  $inst_penalty_percentage = 0;
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
		                  if($cd->prized_status==0)
		                  {
		                    $non_prize = isset($update_branches[0]->non_prize_subscriber_penalty) ? ($update_branches[0]->non_prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($non_prize/ 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  } 
		                  else
		                  {               
		                    $prize = isset($update_branches[0]->prize_subscriber_penalty) ? ($update_branches[0]->prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($prize / 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  }                        		  
		                }      
						
						$cust_det['installment_no'] = $installment_no;
						$cust_det['inst_amt_wise'] = $inst_amt;
						$cust_det['amount_paid'] = $amount_paid;
						$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
						$cust_det['cancel_dividend_amount'] = $total_divide_amount;
						$cust_det['pending_days'] = $pending_days;
						$cust_det['bonus_inst_wise'] = 0;
						$cust_det['penalty_inst_wise'] = 0;
						$cust_det['discount_inst_wise'] = 0;
						$cust_det['discount_penalty_wise'] = 0;
						$cust_det['auction_id'] = $auction_id;
						$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus + $total_divide_amount;
						if($ins_amt_wise_rec>0) {
							array_push($install_det,$cust_det);
						}	
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$response_data['total_divident_amount'] = strval(round($total_divi_amt));
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			}
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);	
		}
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function customer_ledger_running_enroll_info(Request $request)
    {
    	try
    	{ //customer_id
    		$cond = $request->conditions;
    		$chits =ChitDetail::where($cond)->get();
    		if(count($chits)>0)
    		{
	    		$i=1; 		
	    		$response_data= [];
	    		foreach($chits as $chit)
	    		{	    			
	    			// Group Details  			
	    			$running_group = Group::where('id',$chit->group_id)->where('group_status','=','0')->get();
	    			foreach($running_group as $group)
	    			{
	    				$details = [];  
						$details['sno'] = $i;
						$details['enrollment_id'] = isset($chit->id) ? ($chit->id) : 0;
						$details['group_name'] = isset($group->group_name) ? ($group->group_name) : "";
						$details['chit_value'] = isset($group->scheme_det->chit_value) ? ($group->scheme_det->chit_value) : '0'; 
						$details['auction_date'] = isset($group->auction_date) ? ($group->auction_date) : "00";				 
						if($chit->agent_type == 1)
						{
							$details['introducer_type'] = "Customer";
						}else if($chit->agent_type == 2)
						{
							$details['introducer_type'] = "Employee";
						}else if($chit->agent_type == 3)
						{
							$details['introducer_type'] = "Business Agent";
						} else if($chit->agent_type == 4)
						{
							$details['introducer_type'] = "Self Join";
						}else if($chit->agent_type == 5)
						{
							$details['introducer_type'] = "Others";
						}else
						{
							$details['introducer_type'] = ""; 
						}

						$details['introducer_name_info'] = [];
						if($chit->employee_agent_intro_det)
						{
							$details['introducer_name_info'] = isset($chit->employee_agent_intro_det) ? ($chit->employee_agent_intro_det->first_name.' '.$chit->employee_agent_intro_det->last_name) : [];
						}
						// Bidding Details - Installment count
						$bid = BiddingDetail::where('group_id',$chit->group_id)->count();
						$details['installments']  = isset($bid) ? ($bid) : 0;
						// Chit Taken
						$bid2 = BiddingDetail::where('enrollment_id',$chit->id)->where('group_id',$chit->group_id)->get();
						if(count($bid2)>0)
						{
							$details['chit_taken'] = "Yes";
						}
						else
						{
							$details['chit_taken'] = "No";
						}

						// Advance
						$adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('enrollment_id',$chit->id)->get();
						$total_adv = isset($adv[0]->total_adv) ? ($adv[0]->total_adv) : 0;
						$details['advance'] = $total_adv;

						// Collected 
						$receipt = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus')->where('group_id',$chit->group_id)->where('enrollment_id',$chit->id)->get();
						$total_receipt = isset($receipt[0]->total_receipt) ? ($receipt[0]->total_receipt) : 0 ;
						$total_bonus = isset($receipt[0]->total_bonus) ? ($receipt[0]->total_bonus) : 0;
						$collected = $total_receipt + $total_bonus;
						$details['collected'] =  $collected;

						// To be collected
						$bid3 = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install_amt')->where('group_id',$chit->group_id)->get();
						$install_amt = isset($bid3[0]->total_install_amt) ? ($bid3[0]->total_install_amt) : 0;
						$to_be_collected = $install_amt - $collected;
						$details['to_be_collected'] =$to_be_collected;
						array_push($response_data,$details);
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}    		
    }


    Public function customer_ledger_closed_enroll_info(Request $request)
    {
    	try
    	{    //customer_id
    		$cond = $request->conditions;	    			
    		$chits =ChitDetail::where($cond)->get();
    		if(count($chits)>0)
    		{ 		
    			$i=1; 
    			$response_data= [];
				foreach($chits as $chit)
				{						
					// Group Details  			
					$running_group = Group::where('id',$chit->group_id)->where('group_status','=','1')->get();
					foreach($running_group as $group)
					{	
						$details = [];  
						$details['sno'] = $i;
						$details['enrollment_id'] = isset($chit->id) ? ($chit->id) : 0;
						$details['group_name'] = isset($group->group_name) ? ($group->group_name) : "";
						$details['chit_value'] = isset($group->scheme_det->chit_value) ? ($group->scheme_det->chit_value) : '0'; 
						$details['auction_date'] = isset($group->auction_date) ? ($group->auction_date) : "00";				 
						if($chit->agent_type == 1)
						{
							$details['introducer_type'] = "Customer";
						}else if($chit->agent_type == 2)
						{
							$details['introducer_type'] = "Employee";
						}else if($chit->agent_type == 3)
						{
							$details['introducer_type'] = "Business Agent";
						} else if($chit->agent_type == 4)
						{
							$details['introducer_type'] = "Self Join";
						}else if($chit->agent_type == 5)
						{
							$details['introducer_type'] = "Others";
						}else
						{
							$details['introducer_type'] = ""; 
						}

						$details['introducer_name_info'] = [];
						if($chit->employee_agent_intro_det)
						{
							$details['introducer_name_info'] = isset($chit->employee_agent_intro_det) ? ($chit->employee_agent_intro_det->first_name.' '.$chit->employee_agent_intro_det->last_name) : [];
						}
						// Bidding Details - Installment count
						$bid = BiddingDetail::where('group_id',$chit->group_id)->count();
						$details['installments']  = isset($bid) ? ($bid) : 0;
						// Chit Taken
						$bid2 = BiddingDetail::where('enrollment_id',$chit->id)->where('group_id',$chit->group_id)->get();
						if(count($bid2)>0)
						{
							$details['chit_taken'] = "Yes";
						}
						else
						{
							$details['chit_taken'] = "No";
						}

						// Advance
						$adv = AdvanceReceipt::selectRaw('*,sum(receipt_amount) as total_adv')->where('enrollment_id',$chit->id)->get();
						$total_adv = isset($adv[0]->total_adv) ? ($adv[0]->total_adv) : 0;
						$details['advance'] = $total_adv;

						// Collected 
						$receipt = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus')->where('group_id',$chit->group_id)->where('enrollment_id',$chit->id)->get();
						$total_receipt = isset($receipt[0]->total_receipt) ? ($receipt[0]->total_receipt) : 0 ;
						$total_bonus = isset($receipt[0]->total_bonus) ? ($receipt[0]->total_bonus) : 0;
						$collected = $total_receipt + $total_bonus;
						$details['collected'] =  $collected;

						// To be collected
						$bid3 = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install_amt')->where('group_id',$chit->group_id)->get();
						$install_amt = isset($bid3[0]->total_install_amt) ? ($bid3[0]->total_install_amt) : 0;
						$to_be_collected = $install_amt - $collected;
						$details['to_be_collected'] =$to_be_collected;	
						array_push($response_data,$details);
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

    Public function cust_ledger_paid_and_pending(Request $request)
    {
    	try
    	{ //customer_id
    		$cond = $request->conditions;
    		$chit_details = ChitDetail::where($cond)->get();
    		if(count($chit_details)>0)
    		{	
    			$non_prized_install = 0;
    			$non_prized_collected = 0;
    			$prized_install = 0;
    			$prized_collected = 0;
    			$response_data['non_prized_paid'] = 0;
				$response_data['non_prized_pending'] = 0;
				$response_data['prized_paid'] = 0;
				$response_data['prized_pending'] = 0;
    			foreach($chit_details as $chit)
    			{	
    				// Non Prized Calculation
    				if($chit->prized_status == 0)
    				{	// bidding
    					if($chit->chit_type != 3)
    					{
	    					$bid = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install')->where('group_id',$chit->group_id)->get(); 
	    					$install_amt = isset($bid[0]->total_install) ? ($bid[0]->total_install) : 0;
	    					$non_prized_install = $non_prized_install + $install_amt;
	    					//Receipt
	    					$recpt = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus, sum(penalty) as total_penalty')->where('enrollment_id',$chit->id)->where('group_id',$chit->group_id)->get(); 
	    					$rcpt_amt = isset($recpt[0]->total_receipt) ? ($recpt[0]->total_receipt) : 0;
	    					$bonus_amt = isset($recpt[0]->total_bonus) ? ($recpt[0]->total_bonus) : 0;
	    					$total_received = $rcpt_amt + $bonus_amt;
	    					$non_prized_collected = $non_prized_collected +  $total_received;    					
	    					$non_prized_pending = $non_prized_install - $non_prized_collected;
	    					$response_data['non_prized_paid'] = $non_prized_collected;
	    					$response_data['non_prized_pending'] = $non_prized_pending;    	

    					}
    					if($chit->chit_type == 3)
    					{
    						$bid = CommitmentAuction::selectRaw('*,sum(due_amount) as total_install')->where('enrollment_id',$chit->id)->get(); 
	    					$install_amt = isset($bid[0]->total_install) ? ($bid[0]->total_install) : 0;
	    					$non_prized_install = $non_prized_install + $install_amt;

	    					//Receipt
	    					$recpt = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus, sum(penalty) as total_penalty')->where('enrollment_id',$chit->id)->get(); 
	    					$rcpt_amt = isset($recpt[0]->total_receipt) ? ($recpt[0]->total_receipt) : 0;
	    					$bonus_amt = isset($recpt[0]->total_bonus) ? ($recpt[0]->total_bonus) : 0;
	    					$total_received = $rcpt_amt + $bonus_amt;
	    					$non_prized_collected = $non_prized_collected +  $total_received;    					
	    					$non_prized_pending = $non_prized_install - $non_prized_collected;
	    					$response_data['non_prized_paid'] = $non_prized_collected;
	    					$response_data['non_prized_pending'] = $non_prized_pending;  		
    					}
    				}  
    				// Prized Calculation
    				if($chit->prized_status == 1)
    				{	// bidding
    					if($chit->chit_type != 3)
    					{
	    					$bid1 = BiddingDetail::selectRaw('*,sum(current_installment_amount) as total_install')->where('group_id',$chit->group_id)->get(); 
	    					$install_amt1 = isset($bid1[0]->total_install) ? ($bid1[0]->total_install) : 0;
	    					$prized_install = $prized_install + $install_amt1;

	    					//Receipt
	    					$recpt1 = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus, sum(penalty) as total_penalty')->where('enrollment_id',$chit->id)->where('group_id',$chit->group_id)->get(); 
	    					$rcpt_amt1 = isset($recpt1[0]->total_receipt) ? ($recpt1[0]->total_receipt) : 0;
	    					$bonus_amt1 = isset($recpt1[0]->total_bonus) ? ($recpt1[0]->total_bonus) : 0;
	    					$total_received1 = $rcpt_amt1 + $bonus_amt1;
	    					$prized_collected = $prized_collected +  $total_received1;
	    					$prized_pending = $prized_install - $prized_collected;
	    					$response_data['prized_paid'] = $prized_collected;
	    					$response_data['prized_pending'] = $prized_pending;

    					}
    					if($chit->chit_type ==3)
    					{
    						$bid1 = CommitmentAuction::selectRaw('*,sum(due_amount) as total_install')->where('enrollment_id',$chit->id)->get(); 
	    					$install_amt1 = isset($bid1[0]->total_install) ? ($bid1[0]->total_install) : 0;
	    					$prized_install = $prized_install + $install_amt1;

	    					//Receipt
	    					$recpt1 = Receipt::selectRaw('*,sum(received_amount) as total_receipt, sum(bonus) as total_bonus, sum(penalty) as total_penalty')->where('enrollment_id',$chit->id)->get(); 
	    					$rcpt_amt1 = isset($recpt1[0]->total_receipt) ? ($recpt1[0]->total_receipt) : 0;
	    					$bonus_amt1 = isset($recpt1[0]->total_bonus) ? ($recpt1[0]->total_bonus) : 0;
	    					$total_received1 = $rcpt_amt1 + $bonus_amt1;
	    					$prized_collected = $prized_collected +  $total_received1;
	    					$prized_pending = $prized_install - $prized_collected;
	    					$response_data['prized_paid'] = $prized_collected;
	    					$response_data['prized_pending'] = $prized_pending;
    					}
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

    Public function customer_ledger_customer_document_info(Request $request)
    {
    	try
    	{ //customer_id
    		$cond = $request->conditions;
    		$documents = DocumentDetail::where($cond)->get();
    		$response_data =[];
    		$total_chit_values = 0;
    		foreach($documents as $doc)
    		{	
    			
    			
    			$doc->document_path = (isset($doc->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$doc->document_path) : '';
    		
    			   				
				
    		} 
    		if(count($documents)>0)
    		{
    			$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $documents;
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

     Public function customer_ledger_guarantor_document_info(Request $request)
    {
    	try
    	{ //customer_id
    		$cond = $request->conditions;
    		$documents = Document::where($cond)->where('collected_from',2)->get();
    		$response_data =[];
    		$total_chit_values = 0;
    		foreach($documents as $doc)
    		{	
    			$doc_det = [];
    			$doc_det['document_id'] = $doc->id;
    			$doc_det['group_info'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->group_det) : []; 
    			$doc_det['scheme_info'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->group_det->scheme_det) : []; 
    			$chit_value = isset($doc->enrollment_det->group_det->scheme_det) ? ($doc->enrollment_det->group_det->scheme_det->chit_value) : []; 
    			$total_chit_values += $chit_value;
    			$doc_det['total_chit_value'] = $total_chit_values;

    			$doc_det['ticket_no'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->ticket_no) : [];  
    			$doc_det['guarantor_info'] = [];  
    			    			
				$doc_det['guarantor_info'] = isset($doc->guarantor_det) ? ($doc->guarantor_det) : [];	
				$doc_details1 = DocumentDetail::where('document_id',$doc->id)->groupBy('document_type')->where('status',1)->get();
				$pan_count1 = 0;
				$adhar_count1 = 0;
				$ration_count1 = 0;
				$pan_document_info1 = '';
				$adhar_document_info1 ='';
				$ration_document_info1 ='';
				$asset_count1 =0;
				foreach($doc_details1 as $docts1)
				{
    				if($docts1->document_type ==1)
    				{
    					$pan_counts1 = DocumentDetail::where('document_type',1)->where('document_id',$doc->id)->where('status',1)->get();
    					$pan_count1 = $pan_counts1->count();
    					$pan_document_info1 = (isset($pan_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$pan_counts1[0]->document_path) : '';
    				}
    				if($docts1->document_type ==2)
    				{
    					$adhar_counts1 = DocumentDetail::where('document_type',2)->where('document_id',$doc->id)->where('status',1)->get();
    					$adhar_count1 = $adhar_counts1->count();
    					$adhar_document_info1 = (isset($adhar_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$adhar_counts1[0]->document_path) : '';
    				}
    				if($docts1->document_type ==3)
    				{
    					$ration_counts1 = DocumentDetail::where('document_type',3)->where('document_id',$doc->id)->where('status',1)->get();
    					$ration_count1 = $ration_counts1->count();
    					$ration_document_info1 = (isset($ration_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$ration_counts1[0]->document_path) : '';
    				}
    				$doc_det['pan_document_info'] = $pan_document_info1;
    				$doc_det['adhar_document_info'] = $adhar_document_info1;
    				$doc_det['ration_document_info'] = $ration_document_info1;
    				$doc_det['pan_count'] =$pan_count1;
    				$doc_det['adhar_count'] =$adhar_count1;
    				$doc_det['ration_count'] =$ration_count1;    
				}
				$asset_det = AssetDetail::where('document_id',$doc->id)->where('status',1)->count(); 
				$asset_count1 = isset($asset_det) ? ($asset_det) : 0;
				$doc_det['asset_count'] = $asset_count1;
    			
    			array_push($response_data,$doc_det);    		
    		}
    		if(count($documents)>0)
    		{
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }


    Public function auction_wise_to_be_collected(Request $request)
    {
    	try
    	{	// id
    		$cond = $request->conditions;
    		$chits = ChitDetail::where($cond)->get();
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
						$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
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
						$cd->chit_value = $chit_value;
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
							$current_date = date('Y-m-d');
							if($ins_amt_wise_rec !=$inst_amt && $i==0 && $inst_amt_no !=0) {
								$receipt_date = $last_receipt_date_inst[0];
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
								$i++;
							} 
							else {
								$receipt_date = $auc_date_inst;
								$datetime1 = new DateTime($receipt_date);
								$datetime2 = new DateTime($current_date);
								$interval = $datetime1->diff($datetime2);
								$pending_days = $interval->format('%a');
							}
							if ($auc_date_inst && $auc_date_inst != '0000-00-00' && strtotime($auc_date_inst) > strtotime($current_date)) {
								$pending_days = 0;
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

    public function customer_ledger_relationship_det(Request $request)
    {
        try

        {	
        	$cond = $request->conditions;		
            $relation_ship = Customer::where($cond)->get();
            $response_data = [];
			if(count($relation_ship)>0)
			{ 
				foreach($relation_ship as $relation)
				{ 
					$relation_det = [];
					$relation_det['relationship_info'] = isset($relation->relationship_det) ? ($relation->relationship_det->relationship_name) : [];
					$relation_det['customer_belongs_to'] = isset($relation->customer_belongs_to_det) ? ($relation->customer_belongs_to_det->name) : [];
					array_push($response_data,$relation_det);
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
            

        {	//customer_id
        	$cond = $request->conditions;
            $relation_ship = Customer::where($cond)->get();
            $response_data = [];
            foreach($relation_ship as $relation)
            {
                $relation_det = [];
                $relation_det['relationship_info'] = isset($relation->relationship_det) ? ($relation->relationship_det->relationship_name) : [];
                $relation_det['customer_belongs_to'] = isset($relation->customer_belongs_to_det) ? ($relation->customer_belongs_to_det->name) : [];
                array_push($response_data,$relation_det);
            } 
            if(count($relation_ship)>0)
            {
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
		}
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function customer_ledger_group_advance_details(Request $request)
    {
    	try
    	{	// enrollment_id
			$cond = $request->conditions;
    		$advance_rcpts = DB::table("advance_receipts")->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","receipt_amount","employee_id","payment_type_id","receipt_no");
    		$receipts =  DB::table("receipts")->where('payment_type_id',7)->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		$response_data=[];
    		$cr_total = 0;
    		$dr_total =0;
    		$balance_avance =0;
			foreach($receipts as $rcpt) 
			{
				
				$rcpt_det = [];
				$dr =0;
				$cr =0;
				if($rcpt->payment_type_id != 7)
				{
					$rcpt_det['date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] =  $rcpt->amount;
					$dr_total = $dr_total + $rcpt->amount;
					$dr = $rcpt->amount;
					$rcpt_det['reference_by'] = "";
					$rcpt_det['receipt_amount'] = 0;
					$employee = Employee::where('id',$rcpt->employee_id)->get();
					$rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = "";
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				if($rcpt->payment_type_id == 7)
				{	
					$rcpt_det['date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] = 0;
					$rcpt_det['reference_by'] = "";
					$penalty_rec = 0;
					$cancel_dividend = 0;
					$penalty_rec = Receipt::where('receipt_no','=',$rcpt->receipt_no)->sum('penalty');
					if($penalty_rec > 0){
						$penalty_rec = $penalty_rec;
					} else {
						$penalty_rec = 0;
					}
					$cancel_dividend = Receipt::where('receipt_no','=',$rcpt->receipt_no)->sum('cancel_dividend_amount');
					if($cancel_dividend > 0){
						$cancel_dividend = $cancel_dividend;
					} else {
						$cancel_dividend = 0;
					}
					$rcpt_det['receipt_amount'] =  $rcpt->amount + $penalty_rec;
					$cr_total = $cr_total + $rcpt->amount + $penalty_rec + $cancel_dividend;
					$cr = $rcpt->amount + $penalty_rec;
					$rcpt_det['collected_by'] = "";
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = "";
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				$balance_avance = $balance_avance + $dr - $cr;
				$rcpt_det['balance_avance'] = $balance_avance;
				array_push($response_data,$rcpt_det);
			}   
			if(count($receipts)>0)
			{
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }
	
	Public function get_pigmy_details(Request $request)
    {
    	try
    	{	// enrollment_id
			$cond = $request->conditions;
    		$advance_rcpts = DB::table("pigmy_collections")->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("received_date as receipt_date","amount","employee_id","payment_type_id","receipt_no");
    		$receipts =  DB::table("receipts")->where('payment_type_id',10)->where('status',1)->where('deleted_at','=',Null)->where($cond)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		$response_data=[];
    		$cr_total = 0;
    		$dr_total =0;
    		$balance_avance =0;
			foreach($receipts as $rcpt) 
			{
				
				$rcpt_det = [];
				$dr =0;
				$cr =0;
				if($rcpt->payment_type_id != 10)
				{
					$rcpt_det['date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] =  $rcpt->amount;
					$dr_total = $dr_total + $rcpt->amount;
					$dr = $rcpt->amount;
					$rcpt_det['reference_by'] = "";
					$rcpt_det['receipt_amount'] = 0;
					$employee = Employee::where('id',$rcpt->employee_id)->get();
					$rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = "";
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				if($rcpt->payment_type_id == 10)
				{	
					$rcpt_det['date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] = 0;
					$rcpt_det['reference_by'] = "";
					$penalty_rec = 0;
					$cancel_dividend = 0;
					$penalty_rec = Receipt::where('receipt_no','=',$rcpt->receipt_no)->sum('penalty');
					if($penalty_rec > 0){
						$penalty_rec = $penalty_rec;
					} else {
						$penalty_rec = 0;
					}
					$cancel_dividend = Receipt::where('receipt_no','=',$rcpt->receipt_no)->sum('cancel_dividend_amount');
					if($cancel_dividend > 0){
						$cancel_dividend = $cancel_dividend;
					} else {
						$cancel_dividend = 0;
					}
					$rcpt_det['receipt_amount'] =  $rcpt->amount + $penalty_rec;
					$cr_total = $cr_total + $rcpt->amount + $penalty_rec + $cancel_dividend;
					$cr = $rcpt->amount + $penalty_rec;
					$rcpt_det['collected_by'] = "";
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = "";
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				$balance_avance = $balance_avance + $dr - $cr;
				$rcpt_det['balance_avance'] = $balance_avance;
				array_push($response_data,$rcpt_det);
			}   
			if(count($receipts)>0)
			{
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

    // Public function receipt_report_excel_export(Request $request)
    // {
    //     try {  
    //         $cond = [];
    //         $start_date = '';
    //         $end_date = '';
    //         if(isset($request->tenant_id)){
	// 			$cond['tenant_id'] = $request->tenant_id;
	// 		}
	// 		if(isset($request->branch_id)){
	// 			$cond['branch_id'] = $request->branch_id;
	// 		}
	// 		if(isset($request->group_id)){
	// 			$cond['group_id'] = $request->group_id; 
	// 		}
	// 		if(isset($request->customer_id)){
	// 			$cond['customer_id'] = $request->customer_id; 
	// 		}
	// 		if(isset($request->employee_id)){
	// 			$cond['employee_id'] = $request->employee_id; 
	// 		}
	// 		if(isset($request->payment_type_id)){
	// 			$cond['payment_type_id'] = $request->payment_type_id; 
	// 		} 
	// 		if(isset($request->start_date)) { 
    //     		$s_date = ($request->start_date['year']).'-'.sprintf('%02d',($request->start_date['month'])).'-'.sprintf('%02d',($request->start_date['day']));
    //     		$start_date = date('Y-m-d',strtotime($s_date)); 
    //     	}				
    //     	if(isset($request->end_date)) {
    //     		$s_date = ($request->end_date['year']).'-'.sprintf('%02d',($request->end_date['month'])).'-'.sprintf('%02d',($request->end_date['day'])); 
    //     		$end_date = date('Y-m-d',strtotime($s_date)); 
    //     	}  
	// 		$receipts = DB::select('call receipt_report(?,?,?,?,?,?,?,?)',array($request->tenant_id,$request->branch_id,$request->group_id,$request->customer_id,$request->employee_id,$request->payment_type_id,$request->start_date,$request->end_date));
	// 		$receiptArray = [];
	// 		$receiptArray[] = ['receipt_date','receipt_no','enrollment_id','customer_id'];
    //         $i=1;  
    //         foreach($receipts as $receipt) {
	// 			$receipt_det = [];
	// 			$receipt_det['receipt_date'] = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '';
	// 			$receipt_det['receipt_no'] = ($receipt->receipt_no!=0) ? date('d/m/Y',strtotime($receipt->receipt_no)) : '';
	// 			$receipt_det['enrollment_id'] = isset($receipt->enrollment_id) ? ($receipt->enrollment_id) : "";
	// 			$receipt_det['customer_id'] = isset($receipt->customer_id) ? ($receipt->customer_id) : "";
	// 			$receiptArray[] = $receipt_det;
    //         } 
    //         $date = date('d-m-Y'); 
    //         $time = time();	
    //         $file_name = $date.'_'.$time.'_receipt_report_excel';
    //        	Excel::create($file_name, function($excel) use ($receiptArray) {
    //            $excel->setTitle('Receipt List');
    //            $excel->sheet('sheet1', function($sheet) use ($receiptArray) {
    //            		$sheet->fromArray($receiptArray, null, 'A1', false, false);
    //                 $sheet->cell('A1:AA1', function($cell) {
    //                     // Set font
    //                     $cell->setFont(array(
    //                         'family'     => 'Calibri',
    //                         'size'       => '12',
    //                         'bold'       =>  true
    //                     ));
    //                 });
    //             });
    //         })->store('xlsx',storage_path('app/uploads/receipt_reports'));
    //         $filepath = env('APP_URL').Storage::url('app/uploads/receipt_reports/').$file_name.'.xlsx';
    //         if(count($receipts)>0)
    //         {
	//             $response['status'] = 'Success';
	//             $response['filepath'] = $filepath;
	//             return response()->json($response,200);
    //         }
    //         else
    //         {
    //         	$response['status'] = 'Error';
	// 			$response['msg'] = \Lang::get('api.no_data_found');
	// 	        return response()->json($response, 200);
    //         }
    //     }
    //     catch(\Exception $e) {
	// 		$response['status'] = 'Error';
	// 		$response['msg'] = \Lang::get('api.global_error');
	//         return response()->json($response, 401);
    //     }
    // }

    public function advance_receipt_cheque_return(Request $request)
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
                $receipt = AdvanceReceipt::find($request->id);
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

    public function advance_receipt_cheque_cleared(Request $request)
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
                $receipt = AdvanceReceipt::find($request->id);
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

    Public function customer_ledger_get_enrol_details(Request $request)
    {
    	try
    	{  // id
    		$cond  = $request->conditions;
    		$chits =  ChitDetail::where($cond)->get();
    		$response_data =[];
    		$response_data['group_name'] ='';
    		$response_data['ticket_no'] = '';
    		foreach($chits as $chit)
    		{
	    		if($chit->chit_type != 3)
	    		{
					$response_data['group_name'] =  isset($chit->group_det) ? ($chit->group_det->group_name) : "";
					$response_data['ticket_no'] = isset($chit->ticket_no) ? ($chit->ticket_no) : "";
	    		}
	    		if($chit->chit_type == 3)
	    		{
	    			$slab_types = SlabDue::where('id',$chit->slab_id)->get();   
		            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
		            $response_data['group_name'] = "Commitment"."-".$slab_name;
		            $response_data['ticket_no'] = "-";
	    		}    			
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
            return response()->json($response, 401);
    	}
    }
    

    public function get_enrol_details_duplicate(Request $request) {
        try {
            //enrollment id
            $cond = $request->conditions; 
            $chit_details = ChitDetail::where($cond)->orderBy('id','desc')->get();
			
			$response_data = [];
			
			foreach($chit_details as $cd) {
				$enrollment_id = $cd->id;
		        $group_id = $cd->group_id;
		        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
		        $customer_id = $cd->customer_id;
				$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
				$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
				//total_installment_amount
				$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
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
				$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
				$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
				//no installment paid 
				$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
			   //auction wise details 
				$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->groupBy('installment_no')->get();
				$i=0;
				$install_det = [];					
				$total_penalty_amounts = 0;
				$total_bonus_amounts = 0;
				foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
					$cust_det = [];		
					$penalty = 0;					
					$bonus = 0;					
					$inst_amt = $auc_wise_inst_amt->current_installment_amount;
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
	                $cust_det['penalty_percentage'] = strval($inst_penalty_percentage);
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
					$total_penalty_amounts += $penalty;
					$total_bonus_amounts += $bonus;
				};
				$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
				$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
				$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
				$cd->pending_details = $install_det;
				$cd->scheme_info = [];
				if(isset($cd->group_det)) {
					$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
				}
				if($cd) {
					array_push($response_data,$cd);
				}
			}
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);
		}
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function store_receipt_duplicate(Request $request)
    {	
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            //'group_id' => 'required',
			'employee_id' => 'required',
			'enrollment_id'=> 'required',
			//'receipt_type'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
				if(isset($request->Installments)) 
				{  

					$count = count($request->Installments);
					$receipt_no = '';
					for($i=0; $i<$count; $i++)
					{	
						$receipt = new Receipt(); 
						$receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
						$receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
						$receipt->other_branch = isset($request->other_branch) ? ($request->other_branch) : 0;
						$receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
						$receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;
						$receipt->group_id = isset($request->group_id) ? ($request->group_id) : 0;
						$receipt->ticket_no = isset($request->ticket_no) ? ($request->ticket_no) : '';
						$receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;
						$employee_branch_id = Employee::where('id','=',$receipt->employee_id)->pluck('branch_id');
						$receipt->employee_branch_id = $employee_branch_id;
						$receipt->adjust_id = isset($request->adjust_id) ? ($request->adjust_id) : 0;
						$receipt->amount = isset($request->amount) ? ($request->amount) : 0;
						if(isset($request->receipt_date)) {
							$r_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
							$receipt->receipt_date = date('Y-m-d',strtotime($r_date)); 
						}
						else {
							$receipt->receipt_date = '0000-00-00';
						}
						//$receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : "0000-00-00";
						$receipt->type_of_collection = 'receipt';
						if($request->receipt_type == 'Advanced Receipt') {
							$receipt->payment_type_id = 7;
							$receipt->type_of_collection = 'advance_receipt';
						} else {
						$receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
						$receipt->type_of_collection = 'receipt';
						}
						$receipt->debit_to	 = isset($request->debit_to	) ? ($request->debit_to	) : '';
						$receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '';
						if(isset($request->cheque_date)) {
							$c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
							$receipt->cheque_date = date('Y-m-d',strtotime($c_date)); 
						}
						else {
							$receipt->cheque_date = '0000-00-00';
						}
						//$receipt->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : "0000-00-00";
						if(isset($request->cheque_clear_return_date)) {
							$cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
							$receipt->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
						}
						else {
							$receipt->cheque_clear_return_date = '0000-00-00';
						}
						//$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
						$receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
						$receipt->branch_name = isset($request->branch_name) ? ($request->branch_name) : '';
						$receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
						if(isset($request->transaction_date)) {
							$t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
							$receipt->transaction_date = date('Y-m-d',strtotime($t_date)); 
						}
						else {
							$receipt->transaction_date = '0000-00-00';
						}
						//$receipt->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : "0000-00-00";
						$receipt->transaction_bill = isset($request->transaction_bill) ? ($request->transaction_bill) : '';
						$receipt->transaction_book = isset($request->transaction_book) ? ($request->transaction_book) : '';
						$receipt->neft_adjust_id = isset($request->neft_adjust_id) ? ($request->neft_adjust_id) : 0;
						$receipt->receipt_mode = 'ERP';
						$receipt->receipt_time = isset($request->receipt_time) ? ($request->receipt_time) : '00:00:00';
						if(isset($request->accounts_date)) {
							$a_date = ($request->accounts_date['year']).'-'.sprintf('%02d',($request->accounts_date['month'])).'-'.sprintf('%02d',($request->accounts_date['day'])); 
							$receipt->accounts_date = date('Y-m-d',strtotime($a_date)); 
						}
						else {
							$receipt->accounts_date = date('Y-m-d');
						}
						//$receipt->accounts_date = isset($request->accounts_date) ? ($request->accounts_date) : "0000-00-00";
						$receipt->printed = isset($request->printed) ? ($request->printed) : 0;
						$receipt->auction_id = isset($request->Installments[$i]['auction_id']) ? $request->Installments[$i]['auction_id'] : '';
						$receipt->installment_no = isset($request->Installments[$i]['installment_no']) ? $request->Installments[$i]['installment_no'] : '';
						$receipt->pending_days = isset($request->Installments[$i]['pending_days']) ? $request->Installments[$i]['pending_days'] : '';
						$receipt->penalty = isset($request->Installments[$i]['penalty_inst_wise']) ? $request->Installments[$i]['penalty_inst_wise'] : '';
						$receipt->bonus = isset($request->Installments[$i]['bonus_inst_wise']) ? $request->Installments[$i]['bonus_inst_wise'] : '';
						$receipt->discount = isset($request->Installments[$i]['discount_inst_wise']) ? $request->Installments[$i]['discount_inst_wise'] : '';
						$receipt->received_amount = isset($request->Installments[$i]['received_amount']) ? $request->Installments[$i]['received_amount'] : '';
						$receipt->cancel_dividend_amount = isset($request->Installments[$i]['cancel_dividend_inst_wise']) ? $request->Installments[$i]['cancel_dividend_inst_wise'] : '';						
						$receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
						$receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
						$receipt->status = isset($request->status) ? ($request->status) : 1;
						if($request->payment_type_id == 2){
							$receipt->status = isset($request->status) ? ($request->status) : 2;
						}
						if($receipt->penalty !=0 || $receipt->received_amount !=0) {
							if($receipt->save()) {
								if($receipt_no=='') {
									//sleep(10);
									$receipt_no = 'RCPT-'.sprintf('%05d', $receipt->id);
								}			
								$receipt->receipt_no = $receipt_no;
								$receipt->save();	
		                	}
	                		else {
			                    $response['status']="Error";
			                    $response['msg']=\Lang::get('api.global_error');
			                    return response()->json($response,401);
		                	}
		            	}
		            }
            		$response['status'] = 'Success';
			        $response['msg'] = \Lang::get('api.success_receipt_added');
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
				return response()->json($response, 401);
			}
		}
	}
	
	  public function receipt_print(Request $request) {
        try {
            $cond = $request->conditions; 
			$receipts1 = Receipt::selectRaw('*, sum(received_amount) as total_paid, sum(bonus) as bonus_amount, sum(penalty) as penalty_amount')->where($cond)->orderBy('id','desc')->get();
            // , sum(received_amount) as total_paid, sum(bonus) as bonus_amount, sum(penalty) as penalty_amount
            $receipts = Receipt::where($cond)->orderBy('id','desc')->get();
            
            // print_r($receipts);exit;
			
			foreach($receipts as $receipt) {
				if($receipt->tenant_id==21){
					$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
					$receipt->tenant_info->company_logo_original = isset($receipt->tenant_det->company_logo_original) ? ($receipt->tenant_det->company_logo_original) : [];           
					$receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
					$receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
					$receipt->group_info = isset($receipt->group_det) ? ($receipt->group_det) : [];
					$receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
					$ticket_type = $receipt->enrollment_det->ticket_type;
					$receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
					$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;
					$receipt->total_canceldivident = Receipt::where($cond)->orderBy('id','desc')->sum('cancel_dividend_amount');
					$total_paid = Receipt::where($cond)->orderBy('id','desc')->pluck('amount');
					$receipt->total_paid = $total_paid[0];
					$receipt->bonus_amount = Receipt::where($cond)->orderBy('id','desc')->sum('bonus');
					$receipt->penalty_amount = Receipt::where($cond)->orderBy('id','desc')->sum('penalty');
					$receipt->total_paid+=$receipt->total_canceldivident;
					$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d-m-Y',strtotime($receipt->receipt_date)) : '0000-00-00';
					$scheme_id = Group::where('id','=',$receipt->group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id[0])->pluck('chit_value');
					$receipt->chit_value = $chit_value[0];
					$receipt->inst_no = $receipt->installment_no;
					$inst_amt = Scheme::where('id','=',$scheme_id[0])->pluck('monthly_due');
					$receipt->inst_amt = ($inst_amt[0]*$ticket_type);
					$dividend_amount = BiddingDetail::where('id','=',$receipt->auction_id)->pluck('divident_amount');
					$receipt->dividend = ($dividend_amount[0]*$ticket_type);
					$receipt->gst = 0;
					$receipt->adjustment = 0;
					$receipt->bonus = 0;
					$receipt->bonus = 0;
					$receipt->penalty = 0;
					$receipt->other_amount = 0;
					$receipt->customer_info->name = $receipt->customer_det->name . ' ' . $receipt->customer_det->last_name;
				} else if($receipt->tenant_id==20){
					$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
					$receipt->tenant_info->company_logo_original = isset($receipt->tenant_det->receipts_logo) ? ($receipt->tenant_det->receipts_logo) : [];           
					$receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
					$receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
					$receipt->customer_info->name = $receipt->customer_det->name . ' ' . $receipt->customer_det->last_name; 
					
					$receipt->group_info = isset($receipt->group_det) ? ($receipt->group_det) : [];
				
				
					$receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
					$receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
					$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;
					$total_canceldivident = Receipt::where($cond)->orderBy('id','desc')->sum('cancel_dividend_amount');
					$total_paid = Receipt::where($cond)->orderBy('id','desc')->pluck('amount');
					$receipt->total_paid = $total_paid[0];
					$receipt->bonus_amount = Receipt::where($cond)->orderBy('id','desc')->sum('bonus');
					$penalty_amount = Receipt::where($cond)->orderBy('id','desc')->sum('penalty');
					$receipt->penalty_amount = $penalty_amount;
					if($receipt->enrollment_det->prized_status == 1){
						$receipt->prized_status = 'Prized';
					} else {
						$receipt->prized_status = 'Non-Prized';
					}
					
					$inst_amt = BiddingDetail::where('id','=',$receipt->auction_id)->pluck('current_installment_amount');
					$due_date = BiddingDetail::where('id','=',$receipt->auction_id)->pluck('auction_date');
					$receipt->inst_amt = $inst_amt[0] * $receipt->enrollment_det->ticket_type;	
					$receipt->due_date = date('d-m-Y', strtotime($due_date[0]));	
					$total_installment_amount = BiddingDetail::where('group_id','=',$receipt->group_id)->sum('current_installment_amount');		
					$total_installment_amount = $total_installment_amount * $receipt->enrollment_det->ticket_type;			
					//paid_amount
					$receipt_s=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$receipt->enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt_s[0]->total_paid;
					$bonus_amount = $receipt_s[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$receipt->balance_amount = $total_installment_amount - $paid_amount;
					

					//$receipt->total_paid+=$receipt->total_canceldivident;
					$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d-m-Y',strtotime($receipt->receipt_date)) : '0000-00-00';
					
				} else {
					$other_charge=0;
					$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
					$receipt->tenant_info->company_logo_original = isset($receipt->tenant_det->receipts_logo) ? ($receipt->tenant_det->receipts_logo) : [];           
					$receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
					$receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
					$receipt->customer_info->name = $receipt->customer_det->name . ' ' . $receipt->customer_det->last_name; 
					$receipt->group_info = isset($receipt->group_det) ? ($receipt->group_det) : [];
					$receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
					
					$receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
					$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;
					$receipt->total_canceldivident = Receipt::where($cond)->orderBy('id','desc')->sum('cancel_dividend_amount');
					$total_paid = Receipt::where($cond)->orderBy('id','desc')->pluck('amount');
					$receipt->total_paid = $total_paid[0];
					$receipt->bonus_amount = Receipt::where($cond)->orderBy('id','desc')->sum('bonus');
					$receipt->penalty_amount = Receipt::where($cond)->orderBy('id','desc')->sum('penalty');
					//$receipt->total_paid+=$receipt->total_canceldivident;
					$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d-m-Y',strtotime($receipt->receipt_date)) : '0000-00-00';
					$receipt->other_amount = OtherCharge::where($cond)->sum('amount'); 
					//$receipt->total_paid = $total_paid[0]+$other_charge;
				}
				
			}

			$amt = $receipt->total_paid + $receipts1[0]->penalty_amount + $receipt->other_amount;
			//amount in words
			   $number = $amt;
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

            $response['status'] = 'Success';
            $response['msg'] = '';
			$response['amount_in_words'] = $result;
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['statuse'] = $e;
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function commitment_receipt_print(Request $request) {
        try {
            $cond = $request->conditions; 
			$receipts = Receipt::where($cond)->orderBy('id','desc')->get();
			$installment_no_last_month = $receipts[0]->installment_no;
            $receipts = Receipt::selectRaw('*, sum(received_amount) as total_paid, sum(bonus) as bonus_amount, sum(penalty) as penalty_amount')->where($cond)->orderBy('id','desc')->get();
			
			foreach($receipts as $receipt) {
				$first_installment_no = $receipt->installment_no;
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
                $receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
                $receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
                $receipt->group_info = isset($receipt->group_det) ? ($receipt->group_det) : [];
                $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];
				$slab_name = isset($receipt->enrollment_det) ? ($receipt->enrollment_det->slab_due_det->slab_name) : [];		
                $receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d-m-Y',strtotime($receipt->receipt_date)) : '0000-00-00';
				$receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
				$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;				
			}
			$amt = $receipts[0]->total_paid + $receipts[0]->penalty_amount;
			//amount in words
			   $number = $amt;
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

            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $receipts;
			$response['first_installment_no'] = $first_installment_no;
			$response['last_installment_no'] = $installment_no_last_month;
			$response['slab_name'] = $slab_name;
			$response['amount_in_words'] = $result;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

   public function commitment_receipt_report(Request $request) {
        try {  
            $cond = [];
            $cond1 = [];
            $start_date = null;
            $end_date = null;
            if(isset($request->tenant_id)){
				$cond['tenant_id'] = $request->tenant_id;
			}
			if(isset($request->branch_id)){
				$cond['branch_id'] = $request->branch_id;
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
          
			$response["start_date"] = $start_date;
			$response["end_date"] = $end_date;
			if($request->branch_id >0){
				$cond1['branch_id'] = $request->branch_id;
				//$receipts = DB::select('call commitment_receipt_report(?,?,?,?,?,?,?)',array($request->tenant_id,$request->branch_id,$request->customer_id,$request->employee_id,$request->payment_type_id,$start_date,$end_date));
				$receipts = Receipt::Where($cond)->Where('group_id','0')->get();
				if($request->start_date != null){
					$receipts = Receipt::Where($cond)->Where('group_id','0')->whereBetween('receipt_date',[$start_date,$end_date])->get();
				}
			}
			

            $i=1;  
            $grand_received = 0;
            $tot_received = 0;
            $grand_paid_amt = 0;
            $tot_paid_amt = 0;
            $grand_penalty_amt = 0;
            $tot_penalty_amt = 0;
            $grand_bonus_amt = 0;
            $tot_bonus_amt = 0;
            foreach($receipts as $receipt) {
                $receipt->sno = $i;
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
				//$receipt->branch_name = isset($receipt->branchName) ? ($receipt->branchName) : '-';	
				//$receipt->slabName = isset($receipt->slab_name) ? ($receipt->slab_name) : '-';								
				//$receipt->employee_name = isset($receipt->first_name) ? ($receipt->first_name) : '-';
				//$receipt->customer_name = isset($receipt->name) ? ($receipt->name) : '';
				$receipt->branch_name = isset($receipt->branch_det->branch_name) ? ($receipt->branch_det->branch_name) : [];
				$receipt->slabName = isset($receipt->enrollment_det->slab_due_det->slab_name) ? ($receipt->enrollment_det->slab_due_det->slab_name) : '-';
				$receipt->employee_name = isset($receipt->employee_det->first_name) ? ($receipt->employee_det->first_name) : '-';
				$receipt->customer_name = $receipt->customer_det->name . ' / ' . $receipt->customer_det->customer_code;
				$receipt->enrollment_branch_name = isset($receipt->enroll_branch) ? ($receipt->enroll_branch) : [];	
				
				$receipt->payment_type_name = '-';
				if(isset($receipt->payment_type_id)) {
					$payment_type_det = PaymentType::find($receipt->payment_type_id);	
					$receipt->payment_type_name = isset($payment_type_det) ? ($payment_type_det->payment_name) : '-';
				}
				$receipt->bank_name = '-';
				$receipt->bank_branch_name = '-';
				if(isset($receipt->bank_name_id)) {
					$bank_det = BankDetail::find($receipt->bank_name_id);	
					$receipt->bank_name = isset($bank_det) ? ($bank_det->bank_name) : '-';
					$receipt->bank_branch_name = isset($bank_det) ? ($bank_det->bank_branch_name) : '-';
				}						
				$total_paid = isset($receipt->received_amount) ? ($receipt->received_amount) : 0;
				$receipt->total_paid = $total_paid;
				$bonus_amount = $receipt->bonus;
				$receipt->bonus_amount = $bonus_amount;
				$penalty_amount = $receipt->penalty;
				$receipt->penalty_amount = $penalty_amount;
				$total_received_amount = $receipt->total_paid + $receipt->penalty_amount;
				$receipt->other_branch_info = [];
				$receipt->other_branch_name = '-';
				if(($receipt->other_branch!=0) && ($receipt->enrollment_id)) {
					$other_branch_det = ChitDetail::find($receipt->enrollment_id);
					$receipt->other_branch_info = isset($other_branch_det->branch_det) ? ($other_branch_det->branch_det) : [];
					$receipt->other_branch_name = isset($other_branch_det->branch_det) ? ($other_branch_det->branch_det->branch_name) : '-';
				}
				$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '0000-00-00';
				$receipt->accounts_date = ($receipt->accounts_date!=0) ? date('d/m/Y',strtotime($receipt->accounts_date)) : '0000-00-00';
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '0000-00-00';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '0000-00-00';
				$receipt->cheque_bank_name = '-';
				if(isset($receipt->cheque_debit_to)) {
					$cheque_bank_det = BankDetail::find($receipt->cheque_debit_to);	
					$receipt->cheque_bank_name = isset($cheque_bank_det) ? ($cheque_bank_det->bank_name) : '-';					
				}				
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
				$receipt->total_received_amount = $total_received_amount;
				$receipt->total_paid = $total_paid;
				$receipt->penalty_amount = $penalty_amount;
				$receipt->bonus_amount = $bonus_amount;
                $tot_received += $total_received_amount;
                $tot_paid_amt += $total_paid;
                $tot_penalty_amt += $penalty_amount;
                $tot_bonus_amt += $bonus_amount;
                $i++;
            }
            $grand_received +=$tot_received;
            $grand_paid_amt +=$tot_paid_amt;
            $grand_penalty_amt +=$tot_penalty_amt;
            $grand_bonus_amt +=$tot_bonus_amt;

            $grand_received_format =0;
            $grand_paid_amt_format =0;
            $grand_penalty_amt_format =0;
            $grand_bonus_amt_format =0;

            if($grand_received > 0)
            {   
                $num = $grand_received;
                $grand_received_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_paid_amt > 0)
            {   
                $num = $grand_paid_amt;
                $grand_paid_amt_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_penalty_amt > 0)
            {   
                $num = $grand_penalty_amt;
                $grand_penalty_amt_format = $this->moneyFormatIndia($num);  
            } 
            if($grand_bonus_amt > 0)
            {   
                $num = $grand_bonus_amt;
                $grand_bonus_amt_format = $this->moneyFormatIndia($num);  
            } 
            $response['grand_received'] = $grand_received_format;
            $response['grand_paid_amt'] = $grand_paid_amt_format;
            $response['grand_penalty_amt'] = $grand_penalty_amt_format;
            $response['grand_bonus_amt'] = $grand_bonus_amt_format;
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
	

	Public function module_name_list(Request $request)
    {
    	try
    	{
    		$module_tables = LogMonitoringTable::orderBy('id','Asc')->get();
    		$i = 1;
    		foreach($module_tables as $module)
    		{
    			$module->sno = $i;
    			$i++;
    		}    		
    		$response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $module_tables;
            return response()->json($response,200);
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

    
    Public function log_monitoring_report(Request $request)
    {
    	try
    	{
    		$cond = [];
    		$module_name ='';
    		if(isset($request->branch_id))
    		{
    			$cond['branch_id'] = $request->branch_id;
    		}
    		if(isset($request->module_name))
    		{
    			$module_id = $request->module_name;
    		
	    		if($module_id == 1 )
	    		{
	    			$module_name = 'Receipt';
	    			$module_table = Receipt::where($cond)->where('updated_at','!=','')->withTrashed()->get();
	    		}
	    	
	    		if($module_id == 2)
	    		{
	    			$module_name = 'Customer';
	    			$module_table = Customer::where($cond)->where('updated_at','!=','')->withTrashed()->get();  
	    		}
	    		if($module_id == 3)
	    		{
	    			$module_name = 'Customer Enrollment';
	    			$module_table = ChitDetail::where($cond)->where('chit_type','!=',3)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 4)
	    		{
	    			$module_name = 'Auction Details';
	    			$module_table = BiddingDetail::where($cond)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 5)
	    		{
	    			$module_name = 'Commitment Chit';
	    			$module_table = ChitDetail::where($cond)->withTrashed()->where('chit_type','=',3)->where('updated_at','!=','')->get();    			
	    		}
	    		if($module_id == 6)
	    		{
	    			$module_name = 'Commitment Payment Details';
	    			$module_table = Payment::where($cond)->where('payment_for','=',7)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 7)
	    		{
	    			$module_name = 'Commission Payment';
	    			$module_table = AgentCommissionPayment::where($cond)->where('updated_at','!=','')->withTrashed()->get(); 
	    		}
	    		if($module_id == 8)
	    		{
	    			$module_name = 'Leads';
	    			$module_table = LeadManagement::where($cond)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 9)
	    		{
	    			$module_name = 'Enroll Amount Refund';
	    			$module_table = Payment::where($cond)->where('payment_for','=',3)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 10)
	    		{
	    			$module_name = 'Commitment Receipt';
	    			$module_table = Receipt::where($cond)->where('group_id','=',0)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 11)
	    		{
	    			$module_name = 'Advance Receipts';
	    			$module_table = AdvanceReceipt::where($cond)->where('updated_at','!=','')->withTrashed()->get();    			
	    		}
	    		if($module_id == 12)
	    		{
	    			$module_name = 'Bid Payment Adjustment Receipts';
	    			$module_table = Receipt::where($cond)->where('type_of_collection','=','b.p.adj.receipt')->where('updated_at','!=','')->withTrashed()->get(); 
	    		}
	    		if($module_id == 13)
	    		{
	    			$module_name = 'Other Charges Receipts';
	    			$module_table = OtherCharge::where($cond)->where('updated_at','!=','')->withTrashed()->get();
	    		}
    		}
    		$i = 1;
    		$response_data = [];
    		foreach($module_table as $module)
    		{
    			$log_det = [];
    			$update_by = isset($module->updated_by) ? ($module->updated_by) : '';
    			$deleted_by = isset($module->deleted_by) ? ($module->deleted_by) : '';
    			if($update_by != 0 || $deleted_by !=0)
    			{
	    			$log_det['sno'] = $i;
	    			$log_det['branch_name'] = isset($module->branch_det) ? ($module->branch_det->branch_name) : '';
	    			$log_det['module_name'] = $module_name;
	    			$log_det['updated_at'] =  isset($module->updated_at) ? (date('Y-m-d', strtotime($module->updated_at))) : "0000-00-00";
	    			// updated By
	    			$update_by = isset($module->updated_by) ? ($module->updated_by) : '';
	    			$update_user = User::where('id',$update_by)->get();
	    			$emp_id = isset($update_user[0]->employee_id) ? ($update_user[0]->employee_id) : 0;
	    			$update_employee = Employee::where('id',$emp_id)->get(); 
	    			$update_emp_full_name = isset($update_employee[0]->first_name) ? ($update_employee[0]->first_name .' '.$update_employee[0]->last_name) : '';    			
	    			$log_det['updated_by'] = $update_emp_full_name;
	    			$log_det['deleted_at'] =  isset($module->deleted_at) ? (date('Y-m-d', strtotime($module->deleted_at))) : "0000-00-00";
	    			
	    			// deleted By
	    			$deleted_by = isset($module->deleted_by) ? ($module->deleted_by) : '';
	    			$deleted_user = User::where('id',$deleted_by)->get();
	    			$emp_id1 = isset($deleted_user[0]->employee_id) ? ($deleted_user[0]->employee_id) : 0;
	    			$delete_employee = Employee::where('id',$emp_id1)->get();
	    			$delete_emp_full_name = isset($delete_employee[0]->first_name) ? ($delete_employee[0]->first_name .' '.$delete_employee[0]->last_name) : '';
	    			$log_det['deleted_by'] = $delete_emp_full_name;
	    			$log_det['deletion_remark'] = isset($module->deletion_remark) ? ($module->deletion_remark) : '';
	    			$i++;
	    			array_push($response_data,$log_det);
    			}
    		}
    		$response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $response_data;
            return response()->json($response,200);
    	}
    	catch(\Exception $e)
    	{
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] =$e;
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

    public function store_unknown_transaction(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $unknown_trans=new UnknownTransaction(); 
                $unknown_trans->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $unknown_trans->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                if(isset($request->received_date)) {
                    $s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day'])); 
                    $unknown_trans->received_date = date('Y-m-d',strtotime($s_date)); 
                }
                else {
                        // $unknown_trans->received_date = '0000-00-00';
                } 
                $unknown_trans->amount_received = isset($request->amount_received) ? ($request->amount_received) : 0 ;
                $unknown_trans->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0 ;
                $unknown_trans->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                $unknown_trans->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                $unknown_trans->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
				if(isset($request->cheque_date)) {
					$f_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
					$unknown_trans->cheque_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					// $unknown_trans->cheque_date  = '0000-00-00';
				}
                $unknown_trans->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                $unknown_trans->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
				if(isset($request->transaction_date)) {
					$f_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
					$unknown_trans->transaction_date  = date('Y-m-d',strtotime($f_date)); 
				}
				else {
					// $unknown_trans->transaction_date  = '0000-00-00';
				}
                $unknown_trans->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;
                $unknown_trans->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $unknown_trans->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $unknown_trans->status = isset($request->status) ? ($request->status) : 1;
				
                if($unknown_trans->amount_received >0)
                {
                    if($unknown_trans->save()){
                        $response['status']="Success";
                        $response['msg']=\Lang::get('api.transaction_added');
                        $response['data']=$unknown_trans;
                        return response()->json($response,200);
                    }
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    }                    
                }
                else
                {
                    $response['status']="Error";
                    $response['msg'] = "Received Amount Invalid";
                    return response()->json($response,200);
                }
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['statusss']=$e;
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
	}
	
	public function update_unknown_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'branch_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $unknown_trans = UnknownTransaction::find($request->id); 
                    $unknown_trans->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $unknown_trans->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    if(isset($request->received_date)) {
                        $s_date = ($request->received_date['year']).'-'.sprintf('%02d',($request->received_date['month'])).'-'.sprintf('%02d',($request->received_date['day'])); 
                        $unknown_trans->received_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $unknown_trans->amount_received = isset($request->amount_received) ? ($request->amount_received) : 0 ;
                    $unknown_trans->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;   
                    $unknown_trans->debit_to = isset($request->debit_to) ? ($request->debit_to) : 0 ;
                    $unknown_trans->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : 0 ;
                    if(isset($request->cheque_date)) {
                        $s_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $unknown_trans->cheque_date = date('Y-m-d',strtotime($s_date)); 
                    }
                    $unknown_trans->bank_id = isset($request->bank_id) ? ($request->bank_id) : 0 ;
                    $unknown_trans->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : 0 ;
                    if(isset($request->transaction_date)) {
                        $s_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $unknown_trans->transaction_date = date('Y-m-d',strtotime($s_date)); 
                    }
					if($request->payment_type_id == 2){
							$unknown_trans->status = isset($request->status) ? ($request->status) : 2;
						}
                    $unknown_trans->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0 ;  
                    $unknown_trans->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $unknown_trans->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($unknown_trans->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.transaction_updated');
                        $response['data'] =$unknown_trans;
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
	
	public function get_unknown_transaction(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions;
            $unknown_trnansactions = UnknownTransaction::where($cond)->orderBy('id','desc')->get();
            foreach($unknown_trnansactions as $unknown_trnansaction) {
                $unknown_trnansaction->tenant_info = isset($unknown_trnansaction->tenant_det) ? ($unknown_trnansaction->tenant_det) : [];
                $unknown_trnansaction->branch_info = isset($unknown_trnansaction->branch_det) ? ($unknown_trnansaction->branch_det) : [] ;
                $unknown_trnansaction->payment_type_info = isset($unknown_trnansaction->payment_type_det) ? ($unknown_trnansaction->payment_type_det) : [] ;
				$unknown_trnansaction->bank_info = isset($unknown_trnansaction->bank_det) ? ($unknown_trnansaction->bank_det) : [];
                $unknown_trnansaction->received_date = ($unknown_trnansaction->received_date!=0) ? date('d/m/Y',strtotime($unknown_trnansaction->received_date)) : '';
				$unknown_trnansaction->cheque_date = ($unknown_trnansaction->cheque_date!=0) ? date('d/m/Y',strtotime($unknown_trnansaction->cheque_date)) : '';
				$unknown_trnansaction->transaction_date = ($unknown_trnansaction->transaction_date!=0) ? date('d/m/Y',strtotime($unknown_trnansaction->transaction_date)) : '';
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unknown_trnansactions;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function list_unknown_transaction(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $unknown_trnansactions = UnknownTransaction::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $unknown_trnansactions = UnknownTransaction::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($unknown_trnansactions as $unknown_trnansaction) {
                $unknown_trnansaction->sno = $i;
                $unknown_trnansaction->tenant_info = isset($unknown_trnansaction->tenant_det) ? ($unknown_trnansaction->tenant_det) : [];
                $unknown_trnansaction->branch_info = isset($unknown_trnansaction->branch_det) ? ($unknown_trnansaction->branch_det) : [];
				$unknown_trnansaction->employee_info = isset($unknown_trnansaction->employee_det) ? ($unknown_trnansaction->employee_det) : [];
                $unknown_trnansaction->payment_type_info = isset($unknown_trnansaction->payment_type_det) ? ($unknown_trnansaction->payment_type_det) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $unknown_trnansactions;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_unknown_transaction(Request $request) {
        try {
            if(isset($request->id)) {
                $unknown_transaction = UnknownTransaction::find($request->id); 
                $unknown_transaction->deleted_by = $request->deleted_by;
                $unknown_transaction->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($unknown_transaction->save()) {
                    $unknown_transaction->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.transaction_deleted');
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
	
	Public function customer_statement(Request $request)
    {
    	try
    	{	// enrollment_id
			$enrl_id = $request->enrollment_id;
			$enrol = ChitDetail::with(['slab_due_det.scheme_det','scheme_det','group_det.scheme_det','customer_det'])->where('id', $enrl_id)->first();
			if(!$enrol) {
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.no_data_found');
				return response()->json($response, 200);
			}
			// Commitment statement only (as requested)
			if((int) ($enrol->chit_type ?? 0) !== 3) {
				$response['status'] = 'Error';
				$response['msg'] = 'Customer statement is available only for Commitment Chit';
				return response()->json($response, 200);
			}
			$group_id = [(int) ($enrol->group_id ?? 0)];
			$ticket_no = '-';
			$slabDet = $enrol->slab_due_det;
			if(!$slabDet && !empty($enrol->slab_id)) {
				$slabDet = SlabDue::with('scheme_det')->find($enrol->slab_id);
			}
			$slab_name = isset($slabDet) ? ($slabDet->slab_name ?? '') : '';
			$group_name = 'Commitment'.($slab_name !== '' ? ('-'.$slab_name) : '');
			$scheme = null;
			if(isset($slabDet) && isset($slabDet->scheme_det)) {
				$scheme = $slabDet->scheme_det;
			} elseif(isset($enrol->scheme_det)) {
				$scheme = $enrol->scheme_det;
			} elseif(!empty($enrol->scheme_id)) {
				$scheme = Scheme::find($enrol->scheme_id);
			}
			$chit_value = $scheme ? (float) ($scheme->chit_value ?? 0) : 0;
			$monthly_subscription = $scheme ? (float) ($scheme->monthly_due ?? 0) : 0;
			$term_period = $scheme ? (int) ($scheme->no_of_months ?? 0) : 0;
			$today = date('Y-m-d');
			$total_months = CommitmentAuction::where('enrollment_id', $enrl_id)->count();
			if($total_months <= 0 && $term_period > 0) {
				$total_months = $term_period;
			}
			$running_month = CommitmentAuction::where('enrollment_id', $enrl_id)
				->where('auction_date', '<=', $today)
				->where('auction_date', '!=', '0000-00-00')
				->count();
			$first_auction_date = CommitmentAuction::where('enrollment_id', $enrl_id)->where('installment_no', 1)->value('auction_date');
			$amount_to_be_paid = (float) CommitmentAuction::where('enrollment_id', $enrl_id)
				->where('auction_date', '<=', $today)
				->where('auction_date', '!=', '0000-00-00')
				->sum('due_amount');
			$paid_amount = (float) Receipt::where('enrollment_id', $enrl_id)->where('status', 1)->sum('received_amount');
			$due_balance = max(0, $amount_to_be_paid - $paid_amount);
			$prized_status_val = (int) ($enrol->prized_status ?? 0);
			$statement_meta = [
				'chit_value' => round($chit_value, 2),
				'monthly_subscription' => round($monthly_subscription, 2),
				'term_period' => $term_period,
				'total_months' => $total_months,
				'running_month' => $running_month,
				'slab_name' => $slab_name,
				'group_name' => $group_name,
				'ticket_no' => $ticket_no,
				'first_auction_date' => $first_auction_date ? date('d-m-Y', strtotime($first_auction_date)) : '',
				'amount_to_be_paid' => round($amount_to_be_paid, 2),
				'paid_amount' => round($paid_amount, 2),
				'due_balance' => round($due_balance, 2),
				'prized_status' => $prized_status_val,
				'prized_status_label' => $prized_status_val === 1 ? 'Prized' : 'Not Prized',
				'customer_name' => isset($enrol->customer_det) ? ($enrol->customer_det->name ?? '') : '',
				'customer_code' => isset($enrol->customer_det) ? ($enrol->customer_det->customer_code ?? '') : '',
			];
			
			// Build transaction rows in PHP (avoid SQL UNION column mismatch across tables)
			$receipts = collect();
			$receiptRows = DB::table('receipts')
				->where('payment_type_id', '!=', 7)
				->where('status', 1)
				->whereNull('deleted_at')
				->where('enrollment_id', $enrl_id)
				->orderBy('receipt_date', 'Asc')
				->get();
			foreach($receiptRows as $row) {
				$receipts->push((object) [
					'receipt_date' => $row->receipt_date,
					'amount' => $row->received_amount ?? 0,
					'receipt_passbook_mode' => $row->receipt_passbook_mode ?? 'rcpt',
					'payment_type_id' => $row->payment_type_id ?? 0,
					'receipt_no' => $row->receipt_no ?? '',
					'cheque_no' => $row->cheque_no ?? '',
					'transaction_no' => $row->transaction_no ?? '',
					'penalty' => $row->penalty ?? 0,
				]);
			}
			$advanceRows = DB::table('advance_receipts')
				->where('status', 1)
				->whereNull('deleted_at')
				->where('enrollment_id', $enrl_id)
				->orderBy('receipt_date', 'Asc')
				->get();
			foreach($advanceRows as $row) {
				$receipts->push((object) [
					'receipt_date' => $row->receipt_date,
					'amount' => $row->receipt_amount ?? 0,
					'receipt_passbook_mode' => $row->receipt_passbook_mode ?? 'advance',
					'payment_type_id' => $row->payment_type_id ?? 0,
					'receipt_no' => $row->receipt_no ?? '',
					'cheque_no' => $row->cheque_no ?? '',
					'transaction_no' => $row->transaction_no ?? '',
					'penalty' => 0,
				]);
			}
			$paymentRows = Payment::where('enrollment_id', $enrl_id)->where('status', 1)->orderBy('payment_date', 'Asc')->get();
			foreach($paymentRows as $row) {
				$receipts->push((object) [
					'receipt_date' => $row->payment_date,
					'amount' => $row->total_amount ?? 0,
					'receipt_passbook_mode' => 'payment',
					'payment_type_id' => $row->payment_type_id ?? 0,
					'receipt_no' => $row->receipt_no ?? '',
					'cheque_no' => $row->cheque_no ?? '',
					'transaction_no' => $row->transaction_no ?? '',
					'penalty' => 0,
				]);
			}
			$receipts = $receipts->sortBy('receipt_date')->values();
			
    		$response_data=[];
    		$cr_total = 0;
    		$receipt_total = 0;
    		$penalty_total = 0;
    		$total_paid_total = 0;
    		$total_charges_total =0;
    		$total_discount =0;
    		$total_received_back =0;
    		$dr_total =0;
    		$balance_avance =0;
			$i = 1;
			
			foreach($receipts as $rcpt) 
			{
				
				$rcpt_det = [];
				$dr =0;
				$cr =0;
				$rcpt_det['sno'] = $i;
				$pay_type = (int) ($rcpt->payment_type_id ?? 0);
				$pay_type_name = PaymentType::where('id', $pay_type)->pluck('payment_name');
				$pay_type_label = count($pay_type_name) > 0 ? $pay_type_name[0] : 'Cash';
				$rcpt_date = (!empty($rcpt->receipt_date) && $rcpt->receipt_date != '0000-00-00')
					? date('d-m-Y', strtotime($rcpt->receipt_date))
					: '';
					
				
				   $receipt_passbook_mode = $rcpt->receipt_passbook_mode ?? 'rcpt';
				   if($receipt_passbook_mode=="rcpt"){
					
					$rcpt_det['type'] = "receipt";
					if($pay_type == 2){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->cheque_no ?? '');	
					} else if($pay_type == 3){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->transaction_no ?? '');	
					} else {
					$pay_type_name_ori = $pay_type_label;	
					}
					
					$rcpt_det['type_shown'] = $pay_type_name_ori;
					$rcpt_det['penalty'] = $rcpt->penalty;
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = $rcpt_date; 
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$receipt_total = $receipt_total + $rcpt->amount;
					$penalty_total = $penalty_total + $rcpt_det['penalty'];
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$total_paid_total = $total_paid_total + $rcpt_det['total_paid_amount'];
					$cr_total = $cr_total + $rcpt->amount;
					$cr = $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					if($pay_type == 6){
					$total_received_back = $total_received_back + $rcpt->amount;	
					} else {
						
					}
					
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="advance"){
					   
					$rcpt_det['type'] = "receipt";
					if($pay_type == 2){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->cheque_no ?? '');	
					} else if($pay_type == 3){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->transaction_no ?? '');	
					} else {
					$pay_type_name_ori = $pay_type_label;	
					}
					$rcpt_det['type_shown'] = $pay_type_name_ori;
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$receipt_total = $receipt_total + $rcpt->amount;
					$penalty_total = $penalty_total + $rcpt_det['penalty'];
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$total_paid_total = $total_paid_total + $rcpt_det['total_paid_amount'];
					$cr_total = $cr_total + $rcpt->amount;
					$cr = $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="divident"){
					$scheme_id = Group::where('id','=',1)->pluck('scheme_id');
    				$cur_insts = Scheme::where('id','=',$scheme_id[0])->pluck('monthly_due');
					$rcpt_det['type'] = "Divident";
					$rcpt_det['type_shown'] = "Double Auction";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $cur_insts[0];
					$total_discount = $total_discount + $rcpt_det['receipt_amount'];
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$cr_total = $cr_total + $cur_insts[0];
					$cr = $cur_insts[0];
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } 
				   else if($receipt_passbook_mode=="commission"){
					$rcpt_det['type'] = "Commission";
					$rcpt_det['type_shown'] = "Being Commission Collected";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$total_received_back = $total_received_back + $rcpt->amount;
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$cr_total = $cr_total + $rcpt->amount;
					$cr = -1 *  $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="sgst"){
					$rcpt_det['type'] = "SGST";
					$rcpt_det['type_shown'] = "SGST";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$total_received_back = $total_received_back + $rcpt->amount;
					$cr_total = $cr_total + $rcpt->amount;
					$cr = -1 * $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="cgst"){
					$rcpt_det['type'] = "CGST";
					$rcpt_det['type_shown'] = "CGST";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$total_received_back = $total_received_back + $rcpt->amount;
					$cr_total = $cr_total + $rcpt->amount;
					$cr = -1 * $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="bidding_amount"){
					$rcpt_det['type'] = "Bidding";
					$rcpt_det['type_shown'] = "Being Discount Distributed";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$total_received_back = $total_received_back + $rcpt->amount;
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$cr_total = $cr_total + $rcpt->amount;
					$cr = -1 * $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else if($receipt_passbook_mode=="payment"){
					$rcpt_det['type'] = "Payment";
					if($pay_type == 2){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->cheque_no ?? '');	
					} else if($pay_type == 3){
					$pay_type_name_ori = $pay_type_label. '/' . ($rcpt->transaction_no ?? '');	
					} else {
					$pay_type_name_ori = $pay_type_label;	
					}
					$rcpt_det['type_shown'] = $pay_type_name_ori;
					$rcpt_det['type_shown'] = 'Prize Winner / ' . $pay_type_name_ori;
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$total_received_back = $total_received_back + $rcpt_det['receipt_amount'];
					$cr_total = $cr_total + $rcpt->amount;
					$cr = -1 * $rcpt->amount;
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;
				   } else {
					$rcpt_det['type'] = "-";
					$rcpt_det['type_shown'] = "receipt";
					$rcpt_det['penalty'] = "0";
					$rcpt_det['cheque_amount'] = "0";
					$rcpt_det['date'] = date('d-m-Y',strtotime($rcpt->receipt_date));
					$rcpt_det['amount'] = 0;
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$rcpt_det['total_paid_amount'] = $rcpt->amount + $rcpt_det['penalty'];
					$cr_total = $cr_total + $rcpt->amount;
					$cr = $rcpt->amount;
					$rcpt_det['return_amount'] = 0;   
					$rcpt_det['receipt_no'] = $rcpt->receipt_no;   
				   }
					 
				$balance_avance = $balance_avance + $dr + $cr;
				$rcpt_det['balance_avance'] = $balance_avance;
				array_push($response_data,$rcpt_det);
				$i++;
			}   
			$response['receipt_total'] = $receipt_total;
			$response['penalty_total'] = $penalty_total;
			$response['total_paid_total'] = $total_paid_total;
			$response['cheque_charges_total'] = $total_charges_total;
			$response['total_discount'] = $total_discount;
			$response['total_received_back'] = $total_received_back;
			$response['meta'] = $statement_meta;
			$response['status'] = 'Success';
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
	
	Public function auction_wise_to_be_collected_report(Request $request)
    {
    	try
    	{	// id
    		$group_id = $request->group_id;
    		$branch = $request->branch_id;
    		$from_no = $request->from_no;
    		$to_no = $request->to_no;
			
    		$chits = ChitDetail::where('group_id',$group_id)->where('customer_id','!=','1')->get();

    		
    		if(count($chits)>0)
    		{	

    			$response_data_array = [];
    			foreach($chits as $cd)
    			{
    				
					$response_data_array_ref = [];
    				$enrollment_id = $cd->id;
    				$ticket_no = $cd->ticket_no;
			        $group_id = $cd->group_id;
			        $collection_area_id = $cd->collection_area_id;
					$collection_agent_id = $cd->collection_agent_id;
					$branch_id = $cd->branch_id;
			        $customer_id = $cd->customer_id;
					$cust_name = Customer::where('id','=',$customer_id)->pluck('name');
					$cust_code = Customer::where('id','=',$customer_id)->pluck('customer_code');
					$cust_address = Customer::where('id','=',$customer_id)->pluck('address_line_1');
					$group_name=Group::where('id','=',$group_id)->pluck('group_name');
					$branch_name=Branch::where('id','=',$branch_id)->pluck('branch_name');
					
					if($collection_agent_id =='0'){
						
						$employee_name[0]='-';
						
					} else {
						
						$employee_name=Employee::where('id','=',$collection_agent_id)->pluck('first_name');
					}
					$collection_area[0]='';
					if($collection_area_id>0){
						$collection_area=CollectionArea::where('id','=',$collection_area_id)->pluck('collection_area');
					}
					
					
					
					$cust_mobile_no = Customer::where('id','=',$customer_id)->pluck('mobile_no');
					
			        if($cd->chit_type != 3)
			        {
						$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
						$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
						//total_installment_amount
						$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');
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
						$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
						$current_inst_amt = BiddingDetail::where('id','=',$max_bid_id)->pluck('current_installment_amount');
						//no installment paid 
						$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
					   //auction wise details 
						$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->groupBy('installment_no')->get();

						$i=0;
						//print_r($pending_amount);die();
						$k=0;
						$install_det = [];					
						$total_penalty_amounts = 0;
						$total_bonus_amounts = 0;
						foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
							$cust_det = [];		
							$penalty = 0;					
							$bonus = 0;					
							$inst_amt = $auc_wise_inst_amt->current_installment_amount;
					
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
							$cust_det['auction_date'] =  date('d-m-Y',strtotime($auc_date_inst));
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
							
							
					        
							$total_installment_amount = BiddingDetail::where('group_id','=',$cd->group_id)->sum('current_installment_amount');
    			          	$overall_installment_amount = $total_installment_amount;
							
							$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$cd->id)->where('status',1)->get();
    				        $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
    				        $bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                            $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
    				        $paid_amount = $paid_rec_amount + $bonus_amount;
							
							$to_be_collected = $overall_installment_amount - $paid_amount;
							
							
							$max_enrl_id_inst = Receipt::where('enrollment_id','=',$cd->id)->max('installment_no');
							
						    $max_bid_id_inst = BiddingDetail::where('group_id','=',$cd->group_id)->max('installment_no');	
						    $diff_no = $max_bid_id_inst - $max_enrl_id_inst;
							
						      if(floatval($installment_no)>=floatval($from_no) && floatval($installment_no)<floatval($to_no)){
						    // print_r($diff_no);exit;
							
							if($ins_amt_wise_rec!=0) {
						
							if($k==0){
							$cust_det['cust_name'] = $cust_name[0];
							$cust_det['cust_code'] = $cust_code[0];
							$cust_det['cust_address'] = $cust_address[0];
							// $cust_det['current_due'] = $curn_mon_dues;
							$cust_det['branch_name'] = $branch_name[0];
							$cust_det['group_name'] = $group_name[0];
							// print_r($group_name[0]);exit;
							$cust_det['area_name'] = '';
							$cust_det['employee_name'] = '';
							$cust_det['cust_mobile_no'] = $cust_mobile_no[0];
							$cust_det['ticket_no'] = $ticket_no;
							$cust_det['bonus_inst_wise'] = 0;
							$cust_det['penalty_inst_wise'] = 0;
							$cust_det['discount_inst_wise'] = 0;
							$cust_det['discount_penalty_wise'] = 0;
							$cust_det['to_be_collected'] = $to_be_collected;
							$cust_det['auction_id'] = $auction_id;
							$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
							} else {
							$cust_det['cust_name'] = "";
							$cust_det['cust_code'] = "";
							$cust_det['cust_address'] = "";
							$cust_det['current_due'] = "";
							$cust_det['branch_name'] = "";
							$cust_det['group_name'] = "";
							$cust_det['area_name'] = "";
							$cust_det['employee_name'] = "";
							$cust_det['cust_mobile_no'] ="";
							$cust_det['ticket_no'] = $ticket_no;
							$cust_det['bonus_inst_wise'] = "";
							$cust_det['penalty_inst_wise'] = "";
							$cust_det['discount_inst_wise'] = "";
							$cust_det['discount_penalty_wise'] = "";
							$cust_det['to_be_collected'] = "";
							$cust_det['auction_id'] = "";
							$cust_det['installment_wise_overall_pending'] = "";	

							}
							array_push($install_det,$cust_det);
							$k++;
							}
							 
							  }
							 
							$total_penalty_amounts += $penalty;
							$total_bonus_amounts += $bonus;
							
						}

						$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
						$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
						$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
						$cd->pending_details = $install_det;
							
						array_push($response_data,$cd);		
                        							
			        }
			        // print_r($response_data);
			        // print_r($response_data);exit;
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
						}
						$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
						$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
						$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
						$cd->pending_details = $install_det;
							
						array_push($response_data,$cd);		
						
			        }
					array_push($response_data_array,$cd);
				}
				// exit;
    			$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $response_data_array;
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

	public function current_month_collection(Request $request) {
		try { 
			
			  $cond = [];   
			  $cond1 = [];         
			  if($request->branch_id > 0)
			  {
				  $cond1['branch_id'] = $request->branch_id; 
			  }
			  if(isset($request->group_id)){
				  $cond['group_id'] = $request->group_id;   
			  }
			  
			  // if(isset($request->area_id)){
				 //  $cond['collection_area_id'] = $request->area_id;   
			  // }  
			  
			  if(isset($request->group_id) && $request->group_id==0){
				$enrolls = ChitDetail::where('group_id','!=',0)->where($cond1)->where('chit_type','!=',1)->where('customer_id','!=',1)->get(); 
				// print_r($enrolls);
			  } 
			  else{
				$enrolls = ChitDetail::where('group_id','=',$request->group_id)->where($cond1)->where('chit_type','!=',1)->where('customer_id','!=',1)->get(); 
			  }
			  if($request->branch_id == 0)
			  {
				  $enrolls = ChitDetail::where($cond)->where('chit_type','!=',1)->where('customer_id','!=',1)->get();
			  }
			   // exit; 

			  
			  $k=1;			
			  $tot_overal_paid = 0;
			  $grand_tot_overal_paid = 0;
			  $tot_overal_pending = 0;
			  $grand_tot_overal_pending = 0;
			  $tot_overal_penalty =0;
			  $grand_tot_overal_penalty = 0;
			  $grand_tot_overal_bonus = 0;
			  $tot_overal_bonus = 0;
			  $grand_tot_overal_discount = 0;
			  $tot_overal_discount = 0;
			  foreach($enrolls as $enroll) 
			  { 
				  $enroll->sno = $k;  
				  $paid_rec_amount = 0;   
                 
				  if($enroll->chit_type !=3)
				  {    
					  $ticket_type = $enroll->ticket_type;
					  $enroll->branch_name = isset($enroll->branch_det) ? ($enroll->branch_det->branch_name) : [];
					  $enroll->agent_name = isset($enroll->collection_agent_det) ? ($enroll->collection_agent_det->first_name) : [];
					  $enroll->group_name = isset($enroll->group_det) ? ($enroll->group_det->group_name) : [];
					  $enroll->chit_value = isset($enroll->group_det->scheme_det) ? ($enroll->group_det->scheme_det->chit_value) : [];
					  $enroll->customer_name = isset($enroll->customer_det) ? ($enroll->customer_det->name) : [];
					  $enroll->customer_code = isset($enroll->customer_det) ? ($enroll->customer_det->customer_code) : [];
					   $enroll->collection_area = isset($enroll->collection_area_det) ? ($enroll->collection_area_det->collection_area) : '-';
					  $enroll->customer_mobile = isset($enroll->customer_det) ? ($enroll->customer_det->mobile_no) : [];
					  $door_no = isset($enroll->customer_det) ? ($enroll->customer_det->door_no) : []; 
					  $buildling_name = isset($enroll->customer_det) ? ($enroll->customer_det->buildling_name) : [];
					  $address_line_1 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_1) : [];
					  $address_line_2 = isset($enroll->customer_det) ? ($enroll->customer_det->address_line_2) : [];
					  $landmark = isset($enroll->customer_det) ? ($enroll->customer_det->landmark) : [];
					  $city_name = City::where('id',$enroll->customer_det->city_id)->pluck('city_name');        
					  $pincode = isset($enroll->customer_det) ? ($enroll->customer_det->pincode) : [];


					  
					  $enroll->customer_address = $door_no.', '.$buildling_name.', '.$address_line_1.', '.$address_line_2.', '.$landmark.', '.$city_name.'-'.$pincode;
					  $group_id = isset($enroll->group_id) ? ($enroll->group_id) : 0;
					  $enrollment_id = isset($enroll->id) ? ($enroll->id) : 0;
					  $branch_id = isset($enroll->branch_id) ? ($enroll->branch_id) : 0;
					  //total_installment_amount
					  $total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','!=',0)->sum('current_installment_amount');
					  $total_installment_amount = $total_installment_amount * $ticket_type;
					  $enroll->overall_installment_amount = $total_installment_amount;
					  
					  $max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					  $curn_mon_due = BiddingDetail::where('id','=',$max_bid_id)->get();
					  $curn_mon_dues = $curn_mon_due[0]->actual_installment_amount;
					
					  //paid_amount
					  $receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					  $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
					  $bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
					  $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
					  $paid_amount = $paid_rec_amount + $bonus_amount;
					  $rec_inst_no=0;
					  $total_pending_installments =0;
					  
					  //pending installments
					  $max_rec_id_inst = Receipt::where('enrollment_id','=',$enrollment_id)->max('id');
					  
					  if($max_rec_id_inst!=''){
						  
					  $rec_inst_no = Receipt::where('id','=',$max_rec_id_inst)->pluck('installment_no');
					  $rec_last_inst_amt = Receipt::where('enrollment_id','=',$enrollment_id)->where('installment_no','=',$rec_inst_no)->sum('received_amount');
					  
					  $last_inst_amt = BiddingDetail::where('group_id','=',$group_id)->where('installment_no','=',$rec_inst_no)->pluck('current_installment_amount');
					  $total_installment_no = BiddingDetail::where('group_id','=',$group_id)->count();
					  if($rec_last_inst_amt==$last_inst_amt[0]) {
					  $total_pending_installments = $total_installment_no - $rec_inst_no[0];
					  } else {
					  $total_pending_installments = $total_installment_no - $rec_inst_no[0];
					  $total_pending_installments = $total_pending_installments + 1;
					  }
					  } else {
					  $rec_last_inst_amt = 0;
					  $last_inst_amt = 0;
					  $total_installment_no = BiddingDetail::where('group_id','=',$group_id)->count();	
					  $total_pending_installments = $total_installment_no;
					  }
					  
					  //pending_amount 
					  $pending_amount = $total_installment_amount - $paid_amount;
  
					  //auction wise details 
					  $inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count(); 
					  $auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','!=',0)->get();
					  $i=0;
					  $install_det = [];                  
					  $total_penalty_amounts = 0;
					  $total_bonus_amounts = 0;
					  $total_pending_days = 0;
					  
					  $cr_date = date('Y-m-d');
					  $inst_amt = 0;
					  foreach($auc_wise_inst_amts as $auc_wise_inst_amt) 
					  {
						  $cust_det = [];     
						  $penalty = 0;                   
						  $bonus = 0;                 
						  $inst_amt = $auc_wise_inst_amt->current_installment_amount;
						  $inst_amt = $inst_amt * $ticket_type;
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
						  if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
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
							  $i++;
						  }
						  // Bonus days and percentage by heirrachy over all 
						  if($enroll->customer_det->bonus_days!=0 && $enroll->customer_det->customer_bonus!=0)
						  {
							$bonus_days = $enroll->customer_det->bonus_days;
							$bonus_percentage = $enroll->customer_det->customer_bonus;
						  }    
						  elseif($enroll->group_det->group_bonus_days!=0 && $enroll->group_det->group_base_bonus!=0)
						  {
							$bonus_days = $enroll->group_det->group_bonus_days;
							$bonus_percentage = $enroll->group_det->group_base_bonus;
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
						  
						  $bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						  $check_auction = BiddingDetail::where('id',$bid)->get();
						  $max_auction_date = isset($check_auction[0]->auction_date) ? ($check_auction[0]->auction_date) : '0000-00-00';
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
						  elseif($enroll->group_det->group_penalty_days!=0 )
						  {
							$inst_penalty_days = $enroll->group_det->group_penalty_days;
							$inst_penalty_percentage = $enroll->group_det->group_base_penalty;
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
							  $branch=Branch::where('id',$branch_id)->get();
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
						  $total_penalty_amounts += $penalty;
						  $total_bonus_amounts += $bonus;
						 // $total_pending_installments +=$pendign_count;
					  };
					  $auc_wise_inst_id = isset($auc_wise_inst_amts[0]->id) ? ($auc_wise_inst_amts[0]->id) : 0;
					  $cus_rec_amt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where(['enrollment_id' => $enrollment_id, 'auction_id' => $auc_wise_inst_id, 'status' => 1])->get();
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
					  $auction_date = isset($auc_wise_inst_amts[0]->auction_date) ? ($auc_wise_inst_amts[0]->auction_date) : '0000-00-00';
					  $ins_amt_wise_rec1 = $inst_amt - $amount_paid;
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
					  $to_be_collected = $curn_mon_dues + $pending_amount;
					  $bonus = $total_bonus_amounts;
					  $enroll->bonus = $bonus;
					  $penalty = round($total_penalty_amounts);
					  $enroll->current_due = $curn_mon_dues;
					  $enroll->penalty = $penalty;
					  $overall_pending_amount = $pending_amount;
					  if($overall_pending_amount < 0){
						  $overall_pending_amount = 0;
					  } else {
						  $overall_pending_amount = $overall_pending_amount;
					  }
					  $enroll->overall_pending_amount = $overall_pending_amount;
					  $overall_paid_amount = round($paid_rec_amount);
					  $enroll->overall_paid_amount = $overall_paid_amount;
					  $discount = 0;
					  $enroll->discount = $discount;
					  $enroll->to_be_collected = $to_be_collected;
					  $enroll->pending_days = $pending_days1;
					  $enroll->pending_inst = $total_pending_installments;
					  
					  $last_receipt_date_inst = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','=',0)->pluck('current_installment_amount'); 
					//print_r($group_id);die();
                    $enroll->current_due = $last_receipt_date_inst[0];
				  
				  $k++;  
				  $tot_overal_paid += $overall_paid_amount;
				  $tot_overal_pending += $overall_pending_amount; 
				  $tot_overal_penalty += $penalty;
				  $tot_overal_bonus += $bonus;
				  $tot_overal_discount += $discount;
				  
				  }
			  } 
			  $grand_tot_overal_paid += $tot_overal_paid;
			  $grand_tot_overal_pending += $tot_overal_pending;
			  $grand_tot_overal_penalty += $tot_overal_penalty;
			  $grand_tot_overal_bonus += $tot_overal_bonus;
			  $grand_tot_overal_discount += $tot_overal_discount;
  
			  $grand_tot_overal_paid_format =0;
			  $grand_tot_overal_pending_format =0;
			  $grand_tot_overal_penalty_format =0;
			  $grand_tot_overal_bonus_format =0;
			  $grand_tot_overal_discount_format =0;
  
			  if($grand_tot_overal_paid > 0)
			  {   
				  $num = $grand_tot_overal_paid;
				  $grand_tot_overal_paid_format = $this->moneyFormatIndia($num);  
			  }
			  if($grand_tot_overal_pending > 0)
			  {   
				  $num = $grand_tot_overal_pending;
				  $grand_tot_overal_pending_format = $this->moneyFormatIndia($num);  
			  }
			  if($grand_tot_overal_penalty > 0)
			  {   
				  $num = $grand_tot_overal_penalty;
				  $grand_tot_overal_penalty_format = $this->moneyFormatIndia($num);  
			  }
			  if($grand_tot_overal_bonus > 0)
			  {   
				  $num = $grand_tot_overal_bonus;
				  $grand_tot_overal_bonus_format = $this->moneyFormatIndia($num);  
			  }
			  if($grand_tot_overal_discount > 0)
			  {   
				  $num = $grand_tot_overal_discount;
				  $grand_tot_overal_discount_format = $this->moneyFormatIndia($num);  
			  }
			  
			  $response['grand_tot_overal_paid'] = $grand_tot_overal_paid_format;
			  $response['grand_tot_overal_pending'] = $grand_tot_overal_pending_format;
			  $response['grand_tot_overal_penalty'] = $grand_tot_overal_penalty_format;
			  $response['grand_tot_overal_bonus'] = $grand_tot_overal_bonus_format;
			  $response['grand_tot_overal_discount'] = $grand_tot_overal_discount_format;
			  $response['status'] = 'Success';
			  $response['msg'] = "";
			  $response['data'] = $enrolls;
			  return response()->json($response,200);
		  }
		  catch(\Exception $e) {
			  $response['status'] = 'Error';
			  $response['msg'] = \Lang::get('api.global_error');
			  $response['msg'] = $e;
			  return response()->json($response, 401);
		  }
	  }

	 public function before_enrol_receipt_print(Request $request) {
        try {
            $cond = $request->conditions; 
            $receipts = BeforeEnrolReceipt::selectRaw('*, sum(amount) as total_paid')->where($cond)->orderBy('id','desc')->get();
			
			$created_by = $receipts[0]->created_by;
			
			//login id
			//$login_id = User::where('id','=',$created_by)->orderBy('id','asc')->get();
			//$login_user = $login_id[0]->employee_id;
			$employee_id = Employee::where('id','=',$created_by)->orderBy('id','asc')->get();
			$emp_name1 = $employee_id[0]->first_name;
			$emp_name2 = $employee_id[0]->last_name;
			$emp_name = $emp_name1.' '.$emp_name2;
			$response['login_employee'] = $emp_name;
			
			foreach($receipts as $receipt) {
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
                $receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
                $receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
                $receipt->group_info = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det) : [];
                $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
				$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;				
			}

			$amt = $receipts[0]->total_paid;
			//amount in words
			   $number = $amt;
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
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $receipts;
			$response['amount_in_words'] = $result;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function advance_receipt_print(Request $request) {
        try {
            $cond = $request->conditions; 
			
            $receipts = AdvanceReceipt::selectRaw('*, sum(receipt_amount) as total_paid')->where($cond)->orderBy('id','desc')->get();
			
			//$created_by = $receipts[0]->employee_id;
			
			//login id
			// $login_id = User::where('id','=',$created_by)->orderBy('id','asc')->get();
			// $login_user = $login_id[0]->employee_id;
			// $employee_id = Employee::where('id','=',$receipts[0]->employee_id)->orderBy('id','asc')->get();
			// $emp_name1 = $employee_id[0]->first_name;
			// $emp_name2 = $employee_id[0]->last_name;
			// $emp_name = $emp_name1.' '.$emp_name2;
			$response['login_employee'] = '';
			 
			foreach($receipts as $receipt) {
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];       
				$receipt->tenant_info->company_logo_original = isset($receipt->tenant_det->receipts_logo) ? ($receipt->tenant_det->receipts_logo) : [];    						
                $receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
                $receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
                $receipt->customer_info->name = $receipt->customer_det->name . ' ' . $receipt->customer_det->last_name  . ' | ' . $receipt->customer_det->customer_code;
                $receipt->group_info = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det) : [];
                $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
				$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;	
				$receipt->chit_value = isset($receipt->group_det->scheme_det) ? ($receipt->group_det->scheme_det->chit_value) : [];
				$receipt->ag_no = isset($receipt->group_det) ? ($receipt->group_det->blaw_number) : [];		
$receipt->penalty_amount= 0;		 	
$receipt->bonus_amount= 0;		 	
			}
			
			
			$amt = $receipts[0]->total_paid;
			//amount in words
			   $number = $amt;
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
			
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $receipts;
			$response['amount_in_words'] = $result;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	 Public function group_abstract_report(Request $request)
    {
    	try
    	{	// id
    		$cond = $request->group_id;
			
    		$chits = ChitDetail::where('group_id',$request->group_id)->where('customer_id','!=','1')->get();
			$l=0;
    		if(count($chits)>0)
    		{	
    			$response_data_array = [];
    			foreach($chits as $cd)
    			{
					
					$l++;
					$response_data_array_ref = [];
    				$enrollment_id = $cd->id;
    				$ticket_no = $cd->ticket_no;
			        $group_id = $cd->group_id;
			        $collection_area_id = $cd->collection_area_id;
					$collection_agent_id = $cd->collection_agent_id;
					$branch_id = $cd->branch_id;
			        $customer_id = $cd->customer_id;
					$prized_status = $cd->prized_status;
					if($prized_status == 1){
						$cd->prized_status = "Prized";
					}
					else{
						$cd->prized_status = "Non-Prized";
					}
					$cust_name = Customer::where('id','=',$customer_id)->pluck('name');
					$cust_code = Customer::where('id','=',$customer_id)->pluck('customer_code');
					$cust_address = Customer::where('id','=',$customer_id)->pluck('address_line_1');
					$group_name=Group::where('id','=',$group_id)->pluck('group_name');
					$branch_name=Branch::where('id','=',$branch_id)->pluck('branch_name');
					$cd->sno = $l;
					if($collection_agent_id =='0'){
						
						$employee_name[0]='-';
						
					} else {
						
						$employee_name=Employee::where('id','=',$collection_agent_id)->pluck('first_name');
					}
					$collection_area=CollectionArea::where('id','=',$collection_area_id)->pluck('collection_area');
					
					
					
					
					$cust_mobile_no = Customer::where('id','=',$customer_id)->pluck('mobile_no');
			        if($cd->chit_type != 3)
			        {
						$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
						$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
						//total_installment_amount
						$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->where('enrollment_id','!=',0)->sum('current_installment_amount');
						$cd->overall_installment_amount = $total_installment_amount * $cd->ticket_type;
						//paid_amount
						$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
						$paid_rec_amount = $receipt[0]->total_paid;
						$bonus_amount = $receipt[0]->bonus_amount;
						$paid_amount = $paid_rec_amount + $bonus_amount;
						$cd->overall_paid_amount = $paid_amount;
						//pending_amount 
						$pending_amount = $cd->overall_installment_amount - $paid_amount;
						///$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;
						//last auction details
						$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
						$current_inst_amt = BiddingDetail::where('id','=',$max_bid_id)->pluck('current_installment_amount');
						//no installment paid 
						$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
					   //auction wise details 
						$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
						$i=0;
						
						
						$install_det = [];					
						$total_penalty_amounts = 0;
						$total_bonus_amounts = 0;
						foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
							$cust_det = [];		
							$penalty = 0;					
							$bonus = 0;	
                            $remaining_months =0;							
							$inst_amt = $auc_wise_inst_amt->current_installment_amount * $cd->ticket_type;
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
							$cust_det['auction_date'] = date('d/m/Y',strtotime($auc_date_inst));
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
							$max_bid_id = BiddingDetail::where('group_id','=',$cd->group_id)->max('id');
					        $curn_mon_due = BiddingDetail::where('id','=',$max_bid_id)->get();
    				        $curn_mon_dues_act = $curn_mon_due[0]->actual_installment_amount;
							
							$receipts = Receipt::selectRaw('*,sum(received_amount)as total_received')->where(array('enrollment_id'=>$enrollment_id,'payment_type_id'=>7))->get();
							$total_received = isset($receipts[0]->total_received) ? ($receipts[0]->total_received) : "0";
							$advance = AdvanceReceipt::selectRaw('*,sum(receipt_amount)as total_advance')->where(array('enrollment_id'=>$enrollment_id,'status'=>1))->get();
							$total_advance = isset($advance[0]->total_advance) ? ($advance[0]->total_advance) : "0";
							$adv_avl = $total_advance - $total_received;
							$curn_mon_dues = $curn_mon_dues_act - $adv_avl;
							
							if($curn_mon_dues < 0){
								$curn_mon_dues = 0;
							} else {
								$curn_mon_dues = $curn_mon_dues;
							}
					        
							$total_installment_amount = BiddingDetail::where('group_id','=',$cd->group_id)->sum('current_installment_amount');
    			          	$overall_installment_amount = $total_installment_amount;
							
							$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount')->where('enrollment_id',$cd->id)->where('status',1)->get();
    				        $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
    				        $bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                            $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
    				        $paid_amount = $paid_rec_amount + $bonus_amount;
							
							$to_be_collected = $overall_installment_amount + $curn_mon_dues - $paid_amount;
							
							
							$cust_det['cust_name'] = $cust_name[0];
							$cust_det['cust_code'] = $cust_code[0];
							$cust_det['cust_address'] = $cust_address[0];
							
							$cust_det['prized_status'] = $prized_status;
							
							$cust_det['current_due'] = $curn_mon_dues;
							$cust_det['branch_name'] = $branch_name[0];
							
							$cust_det['group_name'] = $group_name[0];
							
							$cust_det['area_name'] = 0;
							
							
							$cust_det['employee_name'] = 0;
							$cust_det['cust_mobile_no'] = $cust_mobile_no[0];
							$cust_det['ticket_no'] = $ticket_no;
							$cust_det['bonus_inst_wise'] = 0;
							$cust_det['penalty_inst_wise'] = 0;
							$cust_det['discount_inst_wise'] = 0;
							$cust_det['discount_penalty_wise'] = 0;
							$cust_det['to_be_collected'] = $to_be_collected;
							$cust_det['auction_id'] = $auction_id;
							$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
							
							$max_enrl_id_inst = Receipt::where('enrollment_id','=',$cd->id)->max('installment_no');
						    $max_bid_id_inst = BiddingDetail::where('group_id','=',$cd->group_id)->max('installment_no');	
						    $diff_no = $max_bid_id_inst - $max_enrl_id_inst;
						      //if($diff_no>=$from_no && $diff_no<$to_no){
							//if($ins_amt_wise_rec!=0) {
							
							array_push($install_det,$cust_det);
							
						//	}
							
							  //}
							
							$total_penalty_amounts += $penalty;
							$total_bonus_amounts += $bonus;
						};
						
						$receipts = 0;
						$wordCount = 0;
						$no_of_months = 0;
						$remaining_months = 0;
						$group_scheme_id = Group::where('id',$cd->group_id)->pluck('scheme_id');
					    $no_of_months = Scheme::where('id',$group_scheme_id[0])->pluck('no_of_months');
					    $receipts=Receipt::where('enrollment_id','=',$cd->id)->where('status','=',1)->groupBy('installment_no')->get();
					    $wordCount = count($receipts);
						
						$remaining_months = $no_of_months[0] - $max_bid_id_inst;
					
						
						for($t=0;$remaining_months>$t;$t++){
							$cust_det['auction_date']="-";
							$cust_det['amount_paid']="-";
							 if($t==0){
							$cust_det['inst_amt_wise_pending']=$curn_mon_dues;
							 } else {
							$cust_det['inst_amt_wise_pending']="-";
							}
							array_push($install_det,$cust_det);	
						}
						$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
						$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
						$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
						$cd->pending_details = $install_det;
						$cd->overall_pending_amount = $pending_amount +$curn_mon_dues;
                        $cd->no_of_months = $no_of_months[0];
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
							/*if($inst_amt == $amount_paid)
							{
								$cust_det['pending_days'] = 0;
							}else
							{
								$cust_det['pending_days'] = $pending_days;
							}*/
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
					array_push($response_data_array,$cd);
				}
				
    			$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $response_data_array;
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
	public function other_receipt_print(Request $request) {
        try {
            $cond = $request->conditions; 
            $receipts = OtherCharge::selectRaw('*, sum(amount) as total_paid')->where($cond)->orderBy('id','desc')->get();
			
			foreach($receipts as $receipt) {
				$receipt->received_date = ($receipt->received_date!=0) ? date('d/m/Y',strtotime($receipt->received_date)) : '0000-00-00';
				$receipt->charges_type_name = isset($receipt->other_charges_det) ? ($receipt->other_charges_det->charge_name) : [];   
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];                    
                $receipt->branch_info = isset($receipt->branch_det) ? ($receipt->branch_det) : [];
                $receipt->customer_info = isset($receipt->customer_det) ? ($receipt->customer_det) : []; 
                $receipt->group_info = isset($receipt->group_det) ? ($receipt->group_det) : [];
				$receipt->slab_name = isset($receipt->enrollment_det->slab_due_det) ? ($receipt->enrollment_det->slab_due_det->slab_name) : [];
                $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];
				$receipt->payment_type_info = isset($receipt->payment_type_det) ? ($receipt->payment_type_det) : [] ;				
			}
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	Public function customer_ledger_nominee_document_info(Request $request)
    {
    	try
    	{ //customer_id
    		$cond = $request->conditions;
    		$documents = Document::where($cond)->where('collected_from',3)->get();
    		$response_data =[];
    		$total_chit_values = 0;
    		foreach($documents as $doc)
    		{	
    			$doc_det = [];
    			$doc_det['document_id'] = $doc->id;
    			$doc_det['group_info'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->group_det) : []; 
    			$doc_det['scheme_info'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->group_det->scheme_det) : []; 
    			$chit_value = isset($doc->enrollment_det->group_det->scheme_det) ? ($doc->enrollment_det->group_det->scheme_det->chit_value) : []; 
    			$total_chit_values += $chit_value;
    			$doc_det['total_chit_value'] = $total_chit_values;

    			$doc_det['ticket_no'] = isset($doc->enrollment_det) ? ($doc->enrollment_det->ticket_no) : [];  
    			$doc_det['nominee_info'] = [];  
    			    			
				$doc_det['nominee_info'] = isset($doc->nominee_det) ? ($doc->nominee_det) : [];	
				$doc_details1 = DocumentDetail::where('document_id',$doc->id)->groupBy('document_type')->where('status',1)->get();
				$pan_count1 = 0;
				$adhar_count1 = 0;
				$ration_count1 = 0;
				$pan_document_info1 = '';
				$adhar_document_info1 ='';
				$ration_document_info1 ='';
				$asset_count1 =0;
				foreach($doc_details1 as $docts1)
				{
    				if($docts1->document_type ==1)
    				{
    					$pan_counts1 = DocumentDetail::where('document_type',1)->where('document_id',$doc->id)->where('status',1)->get();
    					$pan_count1 = $pan_counts1->count();
    					$pan_document_info1 = (isset($pan_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$pan_counts1[0]->document_path) : '';
    				}
    				if($docts1->document_type ==2)
    				{
    					$adhar_counts1 = DocumentDetail::where('document_type',2)->where('document_id',$doc->id)->where('status',1)->get();
    					$adhar_count1 = $adhar_counts1->count();
    					$adhar_document_info1 = (isset($adhar_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$adhar_counts1[0]->document_path) : '';
    				}
    				if($docts1->document_type ==3)
    				{
    					$ration_counts1 = DocumentDetail::where('document_type',3)->where('document_id',$doc->id)->where('status',1)->get();
    					$ration_count1 = $ration_counts1->count();
    					$ration_document_info1 = (isset($ration_counts1[0]->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$ration_counts1[0]->document_path) : '';
    				}
    				$doc_det['pan_document_info'] = $pan_document_info1;
    				$doc_det['adhar_document_info'] = $adhar_document_info1;
    				$doc_det['ration_document_info'] = $ration_document_info1;
    				$doc_det['pan_count'] =$pan_count1;
    				$doc_det['adhar_count'] =$adhar_count1;
    				$doc_det['ration_count'] =$ration_count1;    
				}
				$asset_det = AssetDetail::where('document_id',$doc->id)->where('status',1)->count(); 
				$asset_count1 = isset($asset_det) ? ($asset_det) : 0;
				$doc_det['asset_count'] = $asset_count1;
    			
    			array_push($response_data,$doc_det);    		
    		}
    		if(count($documents)>0)
    		{
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

	public function cheque_hand_report(Request $request) {
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
			$cond['submit_to_bank'] = '0';
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
					$receipts=Receipt::where($cond)->where($cond1)->where('status',2)->get();
				}
				if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->where('status',2)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=Receipt::where($cond)->where($cond1)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=Receipt::where($cond)->where($cond1)->whereBetween('receipt_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->get();
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
				$receipt->cheque_debit_to = isset($receipt->bank_det) ? ($receipt->bank_det->bank_name) : "";
                $receipt->bank_branch_name = isset($receipt->bank_det->bank_branch_name) ? ($receipt->bank_det->bank_branch_name) : '';
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
                $receipt->scheme_info = isset($receipt->scheme_det) ? ($receipt->scheme_det) : [];
				$receipt->group_info =[];
				$receipt->enrollment_info =[];
				if(isset($receipt->enrollment_det)){
				 $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
				 $receipt->group_info = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det) : [];	
				}
				// Group Name
				$group_name = '';
				if($receipt->enrollment_det->chit_type != 3)
				{
					$group_name = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det->group_name.'/'. $receipt->enrollment_det->ticket_no) : "";
				}
				if($receipt->enrollment_det->chit_type == 3)
				{
					$slab_types = SlabDue::where('id',$receipt->enrollment_det->slab_id)->get();   
		            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
		            $group_name = "Commitment"."/".$slab_name;
				}
				
				$receipt->group_name = $group_name;
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];	
                $receipt->collection_employee_name = isset($receipt->employee_det) ? ($receipt->employee_det->first_name.' '.$receipt->employee_det->last_name) : "";			
				// Other branch Name
				$chit_details = ChitDetail::where('id',$receipt->enrollment_id)->get();
				$receipt->collected_branch_name = isset($chit_details[0]->branch_det) ? ($chit_details[0]->branch_det->branch_name) : "";

				$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '';
				$receipt->accounts_date = ($receipt->accounts_date!=0) ? date('d/m/Y',strtotime($receipt->accounts_date)) : '';
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '';
				$receipt->cheque_bank_info = isset($receipt->cheque_debit_to_info) ? ($receipt->cheque_debit_to_info) : [];
				$receipt->status_name =[];
				if(isset($receipt->cheque_debit_to)) {
					$cheque_bank_det = BankDetail::find($receipt->cheque_debit_to);	
					$receipt->cheque_debit_to = isset($cheque_bank_det) ? ($cheque_bank_det->bank_name) : '-';
				}
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
				$receipt->submit_to_bank = isset($receipt->submit_to_bank) ? ($receipt->submit_to_bank) : '';
                $i++;
                $receipt_amount = isset($receipt->receipt_amount) ? ($receipt->receipt_amount) : 0;
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
	
	public function cheque_bank_report(Request $request) {
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
			$cond['submit_to_bank'] = '1';
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
					$receipts=Receipt::where($cond)->where($cond1)->where('status',2)->get();
				}
				if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->where('status',2)->get();
				}                
            }
            else if(($start_date=='') && ($end_date!='')) { 
            	if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
                	$receipts=Receipt::where($cond)->where($cond1)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
				if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->where('receipt_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
				}
            }
            else { 
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }
                if($request->branch_id > 0){
					$cond1['branch_id'] = $request->branch_id;
            		$receipts=Receipt::where($cond)->where($cond1)->whereBetween('receipt_date',[$start_date,$end_date])->get();
            	} 
            	if($request->branch_id ==0)
				{
					$receipts=Receipt::where($cond)->whereBetween('receipt_date',[$start_date,$end_date])->get();
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
				$receipt->cheque_debit_to = isset($receipt->bank_det) ? ($receipt->bank_det->bank_name) : "";
                $receipt->bank_branch_name = isset($receipt->bank_det->bank_branch_name) ? ($receipt->bank_det->bank_branch_name) : '';
				$receipt->tenant_info = isset($receipt->tenant_det) ? ($receipt->tenant_det) : [];
                $receipt->scheme_info = isset($receipt->scheme_det) ? ($receipt->scheme_det) : [];
				$receipt->group_info =[];
				$receipt->enrollment_info =[];
				if(isset($receipt->enrollment_det)){
				 $receipt->enrollment_info = isset($receipt->enrollment_det) ? ($receipt->enrollment_det) : [];	
				 $receipt->group_info = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det) : [];	
				}
				// Group Name
				$group_name = '';
				if($receipt->enrollment_det->chit_type != 3)
				{
					$group_name = isset($receipt->enrollment_det->group_det) ? ($receipt->enrollment_det->group_det->group_name.'/'. $receipt->enrollment_det->ticket_no) : "";
				}
				if($receipt->enrollment_det->chit_type == 3)
				{
					$slab_types = SlabDue::where('id',$receipt->enrollment_det->slab_id)->get();   
		            $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
		            $group_name = "Commitment"."/".$slab_name;
				}
				
				$receipt->group_name = $group_name;
                $receipt->employee_info = isset($receipt->employee_det) ? ($receipt->employee_det) : [];	
                $receipt->collection_employee_name = isset($receipt->employee_det) ? ($receipt->employee_det->first_name.' '.$receipt->employee_det->last_name) : "";			
				// Other branch Name
				$chit_details = ChitDetail::where('id',$receipt->enrollment_id)->get();
				$receipt->collected_branch_name = isset($chit_details[0]->branch_det) ? ($chit_details[0]->branch_det->branch_name) : "";

				$receipt->receipt_date = ($receipt->receipt_date!=0) ? date('d/m/Y',strtotime($receipt->receipt_date)) : '';
				$receipt->accounts_date = ($receipt->accounts_date!=0) ? date('d/m/Y',strtotime($receipt->accounts_date)) : '';
				$receipt->cheque_date = ($receipt->cheque_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_date)) : '';
				$receipt->cheque_clear_return_date = ($receipt->cheque_clear_return_date!=0) ? date('d/m/Y',strtotime($receipt->cheque_clear_return_date)) : '';
				$receipt->cheque_bank_info = isset($receipt->cheque_debit_to_info) ? ($receipt->cheque_debit_to_info) : [];
				$receipt->status_name =[];
				if(isset($receipt->cheque_debit_to)) {
					$cheque_bank_det = BankDetail::find($receipt->cheque_debit_to);	
					$receipt->cheque_debit_to = isset($cheque_bank_det) ? ($cheque_bank_det->bank_name) : '-';
				}
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
				$receipt->submit_to_bank = isset($receipt->submit_to_bank) ? ($receipt->submit_to_bank) : '';
                $i++;
                $receipt_amount = isset($receipt->receipt_amount) ? ($receipt->receipt_amount) : 0;
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
	public function cheque_submit(Request $request)
    {	
       
            try { 
                $receipt = Receipt::where('id',$request->id)->get();
                $rcpt_no = Receipt::where('receipt_no',$receipt[0]->receipt_no)->get();
                foreach($rcpt_no as $rcpt)
                {                  
                	$receipt_insert = Receipt::find($rcpt->id); 
	                
	                $receipt_insert->remarks = isset($request->remarks) ? ($request->remarks) : '';
	                $receipt_insert->submit_to_bank = '1';
	                $receipt_insert->save();
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_cheque_submit');
                //$response['data'] = $rcpt_no;
                return response()->json($response,200);
            }
            catch(\Exception $e) {
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                $response['msg'] = $e;
                return response()->json($response, 401);
            }
        
    }

	public function get_enrol_details_bonus_cancel(Request $request) {
        try {
            //enrollment id
            $cr_date = date("Y-m-d");
            $rec_date = date("Y-m-d");
            //enrollment id
            $recdate = '';
            $s_date = ($request->recdate['year']).'/'.sprintf('%02d',($request->recdate['month'])).'/'.sprintf('%02d',($request->recdate['day']));
        	$rec_date = date('Y-m-d',strtotime($s_date));
            $enrl_id = $request->enrolid;
            if($rec_date == $cr_date) 
            {	 
	            $chit_details = ChitDetail::where('id',$enrl_id)->orderBy('id','desc')->get();		
				$response_data = [];			
				foreach($chit_details as $cd) {
					$enrollment_id = $cd->id;
			        $group_id = $cd->group_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');				
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;				

					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					$total_divid_amount =0 ;
					$total_divi_amt = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;	
						$divident_amt =0;	
									
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$divident_amt = $auc_wise_inst_amt->divident_amount;
						$auction_id = $auc_wise_inst_amt->id;
						$auc_id  = $auc_wise_inst_amt->id;
						$installment_no  = $auc_wise_inst_amt->installment_no;
						$auc_date_inst  = $auc_wise_inst_amt->auction_date;
						$rcpts = Receipt::where('enrollment_id',$enrollment_id)->where('auction_id','=',$auc_id)->where('status',1)->get();	
						$divident = 0;
						if(count($rcpts)>0)
						{							 		
							$divident = isset($rcpts[0]->cancel_dividend_amount)? ($rcpts[0]->cancel_dividend_amount) : 0;
							$total_divi_amt = $divident_amt - $divident;
						}
						else
						{
							$total_divi_amt= $total_divi_amt + $divident_amt;
						}
						$total_divide_amount = $divident_amt - $divident;
						$cd->overall_installment_amount = $total_installment_amount;
						//pending_amount 
						$total_install_amt = $total_installment_amount;
						$pending_amount = $total_install_amt - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;

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
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
							// && $i==0
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = date('Y-m-d');
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							//echo $pending_days;
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = date('Y-m-d');
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							//echo $pending_days;
							$i++;
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
						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date  =$check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$cr_date])->get();
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
							$monthly_amount = BiddingDetail::where('group_id','=',$group_id)->where('installment_no','=','1')->pluck('current_installment_amount');
							$bonus_calculation = $monthly_amount[0] * $bonus_percentage / 100 ; 
							$bonus = 0;
							$cust_det['bonus'] = 0;
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
	                if($cd->customer_det->penalty_days!=0 )
	                {
	                  $inst_penalty_days = $cd->customer_det->penalty_days;
	                  $inst_penalty_percentage = $cd->customer_det->customer_penalty_interest;
	                }
	                elseif($cd->group_det->group_penalty_days!=0)
	                {
	                  $inst_penalty_days = $cd->group_det->group_penalty_days;
	                  $inst_penalty_percentage = $cd->group_det->group_base_penalty;                  
	                }
	                elseif($cd->branch_det->penalty_days!=0 )
	                {
	                  $inst_penalty_days = $cd->branch_det->penalty_days;
	                  $inst_penalty_percentage = $cd->branch_det->branch_wise_penalty;
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
						$cust_det['inst_amt_wise'] = $inst_amt;
						$cust_det['amount_paid'] = $amount_paid;
						$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
						$cust_det['cancel_dividend_amount'] = 0;
						$cust_det['pending_days'] = $pending_days;
						$cust_det['bonus_inst_wise'] = 0;
						$cust_det['penalty_inst_wise'] = 0;
						$cust_det['discount_inst_wise'] = 0;
						$cust_det['discount_penalty_wise'] = 0;
						$cust_det['auction_id'] = $auction_id;
						$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
						if($ins_amt_wise_rec>0) {
							array_push($install_det,$cust_det);
						}	
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$response_data['total_divident_amount'] = strval(round($total_divi_amt));
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			} else if($rec_date != $cr_date) 
            {	
	            $chit_details = ChitDetail::where('id',$enrl_id)->orderBy('id','desc')->get();		
				$response_data = [];			
				foreach($chit_details as $cd) {
					$enrollment_id = $cd->id;
			        $group_id = $cd->group_id;
			        $branch_id = $cd->branch_id;
			        $cd->group_info = isset($cd->group_det) ? ($cd->group_det) : [];
			        $customer_id = $cd->customer_id;
					$scheme_id = Group::where('id','=',$group_id)->pluck('scheme_id');
					$chit_value = Scheme::where('id','=',$scheme_id)->pluck('chit_value');
					//total_installment_amount
					$total_installment_amount = BiddingDetail::where('group_id','=',$group_id)->sum('current_installment_amount');				
					//paid_amount
					$receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount')->where('enrollment_id',$enrollment_id)->where('status',1)->get();
					$paid_rec_amount = $receipt[0]->total_paid;
					$bonus_amount = $receipt[0]->bonus_amount;
					$paid_amount = $paid_rec_amount + $bonus_amount;
					$cd->overall_paid_amount = $paid_amount;				

					//last auction details
					$max_bid_id = BiddingDetail::where('group_id','=',$group_id)->max('id');
					$current_inst_amt = BiddingDetail::where('Id','=',$max_bid_id)->pluck('current_installment_amount');
					//no installment paid 
					$inst_amt_no = Receipt::where('enrollment_id','=',$enrollment_id)->count();
				   //auction wise details 
					$auc_wise_inst_amts = BiddingDetail::where('group_id','=',$group_id)->get();
					$i=0;
					$install_det = [];					
					$total_penalty_amounts = 0;
					$total_bonus_amounts = 0;
					$total_divid_amount =0 ;
					$total_divi_amt = 0;
					foreach($auc_wise_inst_amts as $auc_wise_inst_amt) {
						$cust_det = [];		
						$penalty = 0;					
						$bonus = 0;	
						$divident_amt =0;	
									
						$inst_amt = $auc_wise_inst_amt->current_installment_amount;
						$divident_amt = $auc_wise_inst_amt->divident_amount;
						$auction_id = $auc_wise_inst_amt->id;
						$auc_id  = $auc_wise_inst_amt->id;
						$installment_no  = $auc_wise_inst_amt->installment_no;
						$auc_date_inst  = $auc_wise_inst_amt->auction_date;
						$rcpts = Receipt::where('enrollment_id',$enrollment_id)->where('auction_id','=',$auc_id)->where('status',1)->get();	
						$divident =0;
						if(count($rcpts)>0)
						{							 		
							$divident = isset($rcpts[0]->cancel_dividend_amount)? ($rcpts[0]->cancel_dividend_amount) : 0;
							$total_divi_amt = $divident_amt - $divident;
						}
						else
						{
							$total_divi_amt= $total_divi_amt + $divident_amt;
						}
						$total_divide_amount = $divident_amt - $divident;
						$cd->overall_installment_amount = $total_installment_amount;
						//pending_amount 
						$total_install_amt = $total_installment_amount;
						$pending_amount = $total_install_amt - $paid_amount;
						$cd->overall_pending_amount = $pending_amount;
						$cd->chit_value = $chit_value;

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
						if($ins_amt_wise_rec !=$inst_amt && $inst_amt_no !=0) {
							// && $i==0
							$receipt_date = $last_receipt_date_inst[0];
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						} 
						else {
							$receipt_date = $auc_date_inst;
							$current_date = $rec_date;
							$datetime1 = new DateTime($receipt_date);
							$datetime2 = new DateTime($current_date);
							$interval = $datetime1->diff($datetime2);
							$pending_days = $interval->format('%a');
							$i++;
						}
						// Branch - old bonus, penalty calculation
						$up_branch = UpdateBranch::where('branch_id',$branch_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_branches = UpdateBranch::where('id',$up_branch)->get();
						if(count($update_branches)>0)
						{
							$branch_bonus_days = isset($update_branches[0]->bonus_days) ? ($update_branches[0]->bonus_days) : 0;
							$branch_bonus_percentage = isset($update_branches[0]->bonus) ? ($update_branches[0]->bonus) : 0;
							$branch_penalty_days = isset($update_branches[0]->penalty_days) ? ($update_branches[0]->penalty_days) : 0;
		                	$branch_penalty_percentage = isset($update_branches[0]->branch_wise_penalty) ? ($update_branches[0]->branch_wise_penalty) : 0;
						}
						else
						{
							$branch_bonus_days = 0;
							$branch_bonus_percentage = 0;
							$branch_penalty_days =0;
							$branch_penalty_percentage =0;
						}

						// Group - old bonus, penalty calculation
						$up_group = UpdateGroup::where('group_id',$group_id)->where('edit_date','<=',$rec_date)->max('id');			
						$update_groups = UpdateGroup::where('id',$up_group)->get();
						if(count($update_groups)>0)
						{
							$group_bonus_days = isset($update_groups[0]->group_bonus_days) ? ($update_groups[0]->group_bonus_days) : 0; 
							$group_bonus_percentage = isset($update_groups[0]->group_base_bonus) ? ($update_groups[0]->group_base_bonus) : 0;
							$group_penalty_days = isset($update_groups[0]->group_penalty_days) ? ($update_groups[0]->group_penalty_days) : 0;
							$group_penalty_percentage = isset($update_groups[0]->group_base_penalty) ? ($update_groups[0]->group_base_penalty) : 0;
						}
						else
						{
							$group_bonus_days =0;
							$group_bonus_percentage =0;
							$group_penalty_days =0;
							$group_penalty_percentage =0;
						}
						// Customer - old bonus, penalty calculation
						$up_customer = UpdateCustomer::where('update_customer_id',$customer_id)->where('edit_date','<=',$rec_date)->max('id');
						$update_customers = UpdateCustomer::where('id',$up_customer)->get();
						if(count($update_customers)>0)
						{
							$cust_bonus_days = isset($update_customers[0]->bonus_days) ? ($update_customers[0]->bonus_days) : 0;
							$cust_bonus_percentage = isset($update_customers[0]->customer_bonus) ? ($update_customers[0]->customer_bonus) : 0;
							$cust_penalty_days = isset($update_customers[0]->penalty_days) ? ($update_customers[0]->penalty_days) : 0;
							$cust_penalty_percentage = isset($update_customers[0]->customer_penalty_interest) ? ($update_customers[0]->customer_penalty_interest) : 0;
						}
						else
						{	
							$cust_bonus_days =0;
							$cust_bonus_percentage = 0;
							$cust_penalty_days = 0;
							$cust_penalty_percentage =0;
						}

						// Bonus days and percentage by heirrachy over all 
						if($cust_bonus_days!=0 && $cust_bonus_percentage!=0)
						{
						  $bonus_days = $cust_bonus_days;
						  $bonus_percentage = $cust_bonus_percentage;	
						}    
						elseif($group_bonus_days!=0 && $group_bonus_percentage!=0)
						{
						  $bonus_days = $group_bonus_days;
						  $bonus_percentage = $group_bonus_percentage;
						}
						elseif($branch_bonus_days!=0 && $branch_bonus_percentage!=0)
						{
						  $bonus_days = $branch_bonus_days;
						  $bonus_percentage = $branch_bonus_percentage;
						}    
						else
						{
						  $bonus_days = 0;
						  $bonus_percentage = 0;
						} 		
						$bid = BiddingDetail::Where('group_id',$group_id)->max('id');
						$check_auction = BiddingDetail::where('id',$bid)->get();
						$max_auction_date  =$check_auction[0]->auction_date;
						// Auction on holiday check for bonus
						$auction_on_holiday = AuctionOnHoliday::where('tenant_id','=',$cd->tenant_id)->whereBetween('holiday_date',[$max_auction_date,$rec_date])->get();
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
							$monthly_amount = BiddingDetail::where('group_id','=',$group_id)->where('installment_no','=','1')->pluck('current_installment_amount');
							$bonus_calculation = $monthly_amount[0] * $bonus_percentage / 100 ;  
							$bonus = 0;
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
		                if($cust_penalty_days!=0 && $cust_penalty_percentage!=0)
		                {
		                  $inst_penalty_days = $cust_penalty_days;
		                  $inst_penalty_percentage = $cust_penalty_percentage;
		                }
		                elseif($group_penalty_days!=0 && $group_penalty_percentage!=0)
		                {
		                  $inst_penalty_days = $group_penalty_days;
		                  $inst_penalty_percentage = $group_penalty_percentage;                  
		                }
		                elseif($branch_penalty_days!=0)
		                {
		                  $inst_penalty_days = $branch_penalty_days;
		                  $inst_penalty_percentage = 0;
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
		                  if($cd->prized_status==0)
		                  {
		                    $non_prize = isset($update_branches[0]->non_prize_subscriber_penalty) ? ($update_branches[0]->non_prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($non_prize/ 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  } 
		                  else
		                  {               
		                    $prize = isset($update_branches[0]->prize_subscriber_penalty) ? ($update_branches[0]->prize_subscriber_penalty) : 0;
		                    $pend_days_int = $pending_days * ($prize / 100);
		                    $current_month_days = date('t'); 
		                    $total_days_int = $pend_days_int / $current_month_days;
		                    $penalty = $total_days_int * $ins_amt_wise_rec;
		                    $cust_det['penalty_amounts'] = strval(round($penalty));
		                  }                        		  
		                }      
						
						$cust_det['installment_no'] = $installment_no;
						$cust_det['inst_amt_wise'] = $inst_amt;
						$cust_det['amount_paid'] = $amount_paid;
						$cust_det['inst_amt_wise_pending'] = $ins_amt_wise_rec;
						$cust_det['cancel_dividend_amount'] = 0;
						$cust_det['pending_days'] = $pending_days;
						$cust_det['bonus_inst_wise'] = 0;
						$cust_det['penalty_inst_wise'] = 0;
						$cust_det['discount_inst_wise'] = 0;
						$cust_det['discount_penalty_wise'] = 0;
						$cust_det['auction_id'] = $auction_id;
						$cust_det['installment_wise_overall_pending'] = $ins_amt_wise_rec + strval(round($penalty)) - $bonus;
						if($ins_amt_wise_rec>0) {
							array_push($install_det,$cust_det);
						}	
						$total_penalty_amounts += $penalty;
						$total_bonus_amounts += $bonus;
					};
					$response_data['total_penalty_amounts'] = strval(round($total_penalty_amounts));
					$response_data['total_bonus_amounts'] = strval(round($total_bonus_amounts));
					$response_data['total_overall_pending_amount'] = $pending_amount + $response_data['total_penalty_amounts'] - $response_data['total_bonus_amounts'];
					$response_data['total_divident_amount'] = strval(round($total_divi_amt));
					$cd->pending_details = $install_det;
					$cd->scheme_info = [];
					if(isset($cd->group_det)) {
						$cd->scheme_info = isset($cd->group_det->scheme_det) ? ($cd->group_det->scheme_det) : [];
					}
					if($cd) {
						array_push($response_data,$cd);
					}
				}
			}
			$response['status'] = 'Success';
			$response['msg'] = "";
			$response['data'] = $response_data;
			return response()->json($response,200);	
		}
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cash_settlement_report(Request $request) {
    	try { 
        	$cond = [];
            $cond1 =[];
            $end_date='';
            $start_date = '';        	
        	if(isset($request->employee_id)){
        		$cond['employee_id'] = $request->employee_id; 
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
                    $cash_settlements = CashSettlement::where($cond)->where($cond1)->get();
                }
                if($request->branch_id == 0){
                    $cash_settlements = CashSettlement::where($cond)->get();
                }
            }
            else if(($start_date=='') && ($end_date!='')) {
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $cash_settlements = CashSettlement::where($cond)->where($cond1)->where('settlement_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
                if($request->branch_id == 0){
                    $cash_settlements = CashSettlement::where($cond)->where('settlement_date', '<=', date('Y-m-d',strtotime($end_date)))->get();
                }
            }
            else {
                if(($start_date!='') && ($end_date=='')) {
                    $end_date = date('Y-m-d');
                }  
                if($request->branch_id > 0){
                    $cond1['branch_id'] = $request->branch_id; 
                    $cash_settlements = CashSettlement::where($cond)->where($cond1)->whereBetween('settlement_date',[$start_date,$end_date])->get();
                }
                if($request->branch_id == 0){
                    $cash_settlements = CashSettlement::where($cond)->whereBetween('settlement_date',[$start_date,$end_date])->get();
                }
            } 
    		
			
                $tot_settlement_amount = 0;   
				$i = 1;      
                foreach($cash_settlements as $cash_settlement) 
                { 
                    $cash_settlement->sno = $i;
                    $cash_settlement->tenant_info = isset($cash_settlement->tenant_det) ? ($cash_settlement->tenant_det) : [];
					$branch_name = Branch::where('id','=',$cash_settlement->branch_id)->pluck('branch_name');
                    $cash_settlement->branch_name = $branch_name[0];
                    $emp_name = Employee::where('id','=',$cash_settlement->employee_id)->pluck('first_name');
                    $cash_settlement->employee_name = $emp_name[0];
                    $tot_settlement_amount += $cash_settlement->received_amount;
                    $i++;
                }
                $response['grand_pending_amt'] = $tot_settlement_amount;
				$response['status'] = 'Success';
				$response['msg'] = "";
				$response['data'] = $cash_settlements;
				return response()->json($response,200);
			
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function getDailyCollection(Request $request)
{
    $customerId = 334;
    $branchId = 4;
    $today = now()->toDateString(); // today's date

    $auctions = DB::table('commitment_auctions')
        ->where('customer_id', $customerId)
        ->where('branch_id', $branchId)
        ->orderBy('auction_date')
        ->get();

    $data = [];
    $cumulativePaid = 0;
    $cumulativePending = 0;
    $cumulativePending_unpaid = 0;
    $cumulativePendingUptoToday = 0;

    foreach ($auctions as $index => $auction) {
        $receipts = DB::table('receipts')
            ->where('auction_id', $auction->id)
            ->get();

        $paidAmount = $receipts->sum('received_amount');
        $pendingAmount = $auction->due_amount - $paidAmount;
        $latestReceiptDate = $receipts->max('receipt_date');

        // Determine status
        $status = 'Pending';
        $lateDays = null;

        if ($auction->auction_date > $today) {
            $status = 'Future';
        } elseif ($paidAmount <= 0 && $auction->auction_date <= $today) {
            $status = 'Unpaid';
			$cumulativePending_unpaid  += max($pendingAmount, 0);
        } elseif ($pendingAmount <= 0) {
            if ($latestReceiptDate > $auction->auction_date) {
                $status = 'Late';
                $lateDays = \Carbon\Carbon::parse($latestReceiptDate)
                    ->diffInDays(\Carbon\Carbon::parse($auction->auction_date));
            } else {
                $status = 'Paid';
            }
        } elseif ($paidAmount > 0 && $pendingAmount > 0) {
            $status = 'Partial';
            if ($latestReceiptDate > $auction->auction_date) {
                $status = 'Late';
                $lateDays = \Carbon\Carbon::parse($latestReceiptDate)
                    ->diffInDays(\Carbon\Carbon::parse($auction->auction_date));
            }
        }

        $cumulativePaid += $paidAmount;
        $cumulativePending += max($pendingAmount, 0);

        if ($auction->auction_date <= $today) {
            $cumulativePendingUptoToday += max($pendingAmount, 0);
        }

        $data[] = [
            'days' => 'Day ' . ($index + 1),
            'date' => $auction->auction_date,
            'due_amount' => (float)$auction->due_amount,
            'paid_amount' => (float)$paidAmount,
            'status' => $status,
            'late_days' => $lateDays ? "{$lateDays} days late" : null,
            'pending_amount' => max($pendingAmount, 0),
            'cumulative_paid' => round($cumulativePaid, 2),
            'cumulative_pending' => round($cumulativePending, 2),
            'cumulativePending_unpaid' => round($cumulativePending_unpaid, 2),
            'is_today' => $auction->auction_date === $today,
            'is_future' => $auction->auction_date > $today
        ];
    }

    $totalPaid = array_sum(array_column($data, 'paid_amount'));
    $totalPending = array_sum(array_column($data, 'pending_amount'));
    $totalPending_unpaid = array_sum(array_column($data, 'cumulativePending_unpaid'));

    return response()->json([
        'status' => 'success',
        'customer_name' => DB::table('customers')->where('id', $customerId)->value('name'),
        'total_paid' => $totalPaid,
        'total_pending' => $totalPending,
        'totalPending_unpaid' => $totalPending_unpaid,
        'cumulative_pending_upto_today' => round($cumulativePendingUptoToday, 2),
        'entries' => $data
    ]);
} 

public function customer_statement_report(Request $request)
{
	$customerId = $request->customer_id;
    $branchId   = $request->branch_id;

    // 1) load installments (commitment_auctions) in installment order
    $installmentsRaw = DB::table('commitment_auctions')
        ->where('customer_id', $customerId)
        ->where('branch_id', $branchId)
        ->orderBy('installment_no', 'asc')
        ->get();
 
    // guard
    if ($installmentsRaw->isEmpty()) {
        return response()->json([], 200);
    }

    // Prepare installments array with runtime 'paid' tracker
    $installments = [];
    foreach ($installmentsRaw as $i) {
        $installments[] = [
            'id'             => $i->id,
            'installment_no' => $i->installment_no,
            'due_amount'     => (float) $i->due_amount,
            'paid'           => 0.0,
        ];
    }

    // Reference: first installment amount (used in dividend calculation)
    $firstInstallAmount = $installments[0]['due_amount'];

    // total due across all installments (used for PENDING)
    $totalDueOverall = 0.0;
    foreach ($installments as $it) {
        $totalDueOverall += $it['due_amount'];
    }

    // 2) load receipts (ordered by date)
    $receipts = DB::table('receipts')
        ->where('customer_id', $customerId)
        ->where('branch_id', $branchId)
        ->orderBy('receipt_date', 'asc')
        ->orderBy('id', 'asc')
        ->get();

    $result = [];
    $cumulative = 0.0;           // running sum of receipts
    $totalPayableRunning = 0.0; // running sum of net-payable for fulfilled installments
    $instIndex = 0;             // index into installments array

    $sno = 1;

    foreach ($receipts as $receipt) {
        $amountRemaining = (float) $receipt->received_amount; // amount from this receipt available to allocate
        $fulfilled = []; // installments fully completed by this receipt (list of installment arrays)

        // allocate this receipt to installments sequentially
        while ($amountRemaining > 0 && $instIndex < count($installments)) {
            $current = &$installments[$instIndex];
            $dueLeft = $current['due_amount'] - $current['paid'];

            if ($dueLeft <= 0.000001) {
                // already fully paid (safety)
                $instIndex++;
                continue;
            }

            if ($amountRemaining >= $dueLeft - 0.000001) {
                // this receipt completes the current installment
                $current['paid'] += $dueLeft;
                $amountRemaining -= $dueLeft;

                // record fulfilled installment
                $fulfilled[] = [
                    'installment_no' => $current['installment_no'],
                    'due_amount'     => $current['due_amount'],
                    // dividend computed below per fulfilled installment
                ];

                // update running TOTAL PAYABLE by adding the installment's net-payable
                // net-payable (display) is the current installment's effective amount (see below)
                $totalPayableRunning += $current['due_amount'];

                $instIndex++;
            } else {
                // partial payment only, do not mark fulfilled
                $current['paid'] += $amountRemaining;
                $amountRemaining = 0;
                // stop — this receipt is exhausted
            }
        }
        // After allocation:
        $recAmount = (float) $receipt->received_amount;
        $cumulative += $recAmount;
        $pending = $totalDueOverall - $cumulative;

        // Prepare display fields from fulfilled list:
        $instNos = [];
$instAmounts = [];
$dividends = [];
$netPayableForThisRow = 0.0;

if (!empty($fulfilled)) {
    foreach ($fulfilled as $f) {
        $instNos[] = $f['installment_no'];
        $instAmounts[] = number_format((float)$f['due_amount'], 2, '.', '');
        $div = $firstInstallAmount - (float)$f['due_amount'];
        $dividends[] = number_format($div, 2, '.', '');
        $netPayableForThisRow += (float) $f['due_amount'];
    }
} else {
    // no installment finished by this receipt
    $netPayableForThisRow = $recAmount;
}

// ✅ New rule: if installment_no and dividend are empty/zero, set Net Payable and TOTAL PAYABLE to zero
if ((empty($instNos) || implode('', $instNos) == '0') && 
    (empty($dividends) || implode('', $dividends) == '0')) {
    $netPayableForThisRow = 0;           // ⬅ change
    $totalPayableRunning = 0;            // ⬅ change
}

        // Build installments_fulfilled array for JSON (with dividend)
        $instFulfilledJson = [];
        foreach ($fulfilled as $f) {
            $instFulfilledJson[] = [
                'installment_no' => $f['installment_no'],
                'dividend'       => (float) ($firstInstallAmount - (float)$f['due_amount']),
            ];
        }

        // Add row to result (matching your required format exactly)
        $result[] = [
            'SNo'                   => $sno++,
            'Rec Date'              => $receipt->receipt_date,                         // raw date string
            'RecSeries'             => isset($receipt->receipt_series) ? $receipt->receipt_series : '',
            'Rec No'                => isset($receipt->receipt_no) ? $receipt->receipt_no : '',
            'Receipt Type'          => isset($receipt->receipt_type) ? $receipt->receipt_type : '',
            'Inst No'               => !empty($instNos) ? implode(',', $instNos) : '',
            'Inst Amount'           => !empty($instAmounts) ? implode(',', $instAmounts) : '',
            'Dividend'              => !empty($dividends) ? implode(',', $dividends) : '',
            'Net Payable'           => (string) number_format($netPayableForThisRow, 2, '.', ''), // string like your examples
            'TOTAL PAYABLE'         => $totalPayableRunning > 0 ? number_format($totalPayableRunning, 2, '.', '') : '',
            'Rec Amount'            => (string) number_format($recAmount, 2, '.', ''),
            'Cummulative'           => (float) number_format($cumulative, 2, '.', ''),
            'PENDING'               => (float) number_format($pending, 2, '.', ''),

            // keep the original small structure you required
            'date'                  => $receipt->receipt_date,
            'receipt_id'            => $receipt->id,
            'paid_amount'           => (string) number_format($recAmount, 2, '.', ''),
            'installments_fulfilled'=> $instFulfilledJson,
        ];
        // unset reference
        if (isset($current)) { unset($current); }
    }

   return response()->json([
    'status'  => 'success',
    'message' => 'Customer ledger fetched successfully',
    'data'    => $result
], 200);
}

Public function list_before_enrol_receipts_customer(Request $request)
    {
    	try
    	{	// enrollment_id
			
    		$advance_rcpts = DB::table("before_enrol_receipts")->where('status',1)->where('deleted_at','=',Null)->where('customer_id',$request->customer_id)->select("received_date as receipt_date","amount","employee_id","payment_type_id","receipt_no");
    		
			$receipts =  DB::table("receipts")->where('payment_type_id',8)->where('status',1)->where('deleted_at','=',Null)->where('customer_id',$request->customer_id)->select("receipt_date","amount","employee_id","payment_type_id","receipt_no")->union($advance_rcpts)->orderBy('receipt_date','Asc')->get();
    		
			$response_data=[];
    		$cr_total = 0;
    		$dr_total =0;
    		$balance_avance =0;
			
			foreach($receipts as $rcpt) 
			{
				
				$rcpt_det = [];
				$dr =0;
				$cr =0;
				if($rcpt->payment_type_id != 8)
				{
					$rcpt_det['receipt_date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] =  $rcpt->amount;
					$dr_total = $dr_total + $rcpt->amount;
					$dr = $rcpt->amount;
					$rcpt_det['reference_by'] = "";
					$rcpt_det['receipt_amount'] = 0;
					$employee = Employee::where('id',$rcpt->employee_id)->get();
					$rcpt_det['collected_by'] = isset($employee[0]) ? ($employee[0]->first_name." ".$employee[0]->last_name) : "" ;
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = "";
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				if($rcpt->payment_type_id == 8)
				{	
					$rcpt_det['receipt_date'] = $rcpt->receipt_date;
					$rcpt_det['receipt_no'] =  $rcpt->receipt_no;
					$rcpt_det['amount'] = 0;
					$rcpt_det['reference_by'] = "";
					$rcpt_det['receipt_amount'] =  $rcpt->amount;
					$cr_total = $cr_total + $rcpt->amount;
					$cr = $rcpt->amount;
					$rcpt_det['collected_by'] = "";
					$payment_type = PaymentType::where('id',$rcpt->payment_type_id)->get();
					$rcpt_det['retrun_by'] = ""; 
					$rcpt_det['return_amount'] = 0;
					$rcpt_det['payment_mode'] = isset($payment_type[0]->payment_name) ? ($payment_type[0]->payment_name) : "" ;
				}
				
				$balance_avance = $balance_avance + $dr - $cr;
				$rcpt_det['balance_avance'] = $balance_avance;
				array_push($response_data,$rcpt_det);
			}   
			if(count($receipts)>0)
			{
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
    		$response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
    	}
    }

}
