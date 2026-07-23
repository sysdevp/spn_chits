<?php

namespace App\Http\Controllers;

use App\Models\LoanReceipt;
use App\Models\LoanDetail;
use App\Models\ChitDetail;
use App\Models\Group;
use Illuminate\Http\Request;
use DateTime;
use Validator;

class LoanReceiptController extends Controller
{
    public function store_loan_receipt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'employee_id' => 'required',
            'receipt_date' => 'required',
            'amount' => 'required',
            'loan_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $receipt_no = '';
                $loan_receipt = new LoanReceipt();
                $loan_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $loan_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;   
                //$loan_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
				$loan_receipt->customer_id = $customer_id;  
                $loan_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;  
               // $loan_receipt->enrollment_id = isset($request->enrollment_id) ? ($request->enrollment_id) : 0;  
                $loan_receipt->loan_id = isset($request->loan_id) ? ($request->loan_id) : 0;         
                $loan_receipt->reference_grp = isset($request->reference_grp) ? ($request->reference_grp) : '';     
                $loan_receipt->amount = isset($request->amount) ? ($request->amount) : 0;  
                $loan_receipt->interest_amount = isset($request->interest_amount) ? ($request->interest_amount) : 0;  
                //$loan_receipt->receipt_date = isset($request->receipt_date) ? ($request->receipt_date) : '0000-00-00';  
                if(isset($request->receipt_date)) {
                    $c_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
                    $loan_receipt->receipt_date = date('Y-m-d',strtotime($c_date)); 
                }
                else {
                    $loan_receipt->receipt_date = '0000-00-00';
                }                
                $loan_receipt->offline = isset($request->offline) ? ($request->offline) : 0;
                $loan_receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;              
                $loan_receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
                $loan_receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : ''; 
                if(isset($request->cheque_date)) {
                    $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                    $loan_receipt->cheque_date = date('Y-m-d',strtotime($c_date)); 
                }
                else {
                    $loan_receipt->cheque_date = '0000-00-00';
                }            
                if(isset($request->cheque_clear_return_date)) {
                    $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $loan_receipt->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                }
                else {
                    $loan_receipt->cheque_clear_return_date = '0000-00-00';
                }     
                $loan_receipt->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0;
                $loan_receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                $loan_receipt->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '';
                $loan_receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                if(isset($request->transaction_date)) {
                    $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                    $loan_receipt->transaction_date = date('Y-m-d',strtotime($t_date)); 
                }
                else {
                    $loan_receipt->transaction_date = '0000-00-00';
                }
               
                $loan_receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $loan_receipt->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $loan_receipt->status = isset($request->status) ? ($request->status) : 1;
				 if($request->payment_type_id == 2)
                {
                    $loan_receipt->status = isset($request->status) ? ($request->status) : 2;
                }
                if($loan_receipt->amount >=0)
                {
                    if($loan_receipt->save()) { 
                        if($receipt_no=='') {
                            $receipt_no = 'LOAN-'.sprintf('%05d', $loan_receipt->id);
                        }           
                        $loan_receipt->receipt_no = $receipt_no;
                        if($loan_receipt->save())
                        {
                            $loan_det = LoanDetail::where('id',$request->loan_id)->get();
                            if(count($loan_det)>0)
                            {
                                $paid_amt =  isset($loan_det[0]->paid_amount) ? ($loan_det[0]->paid_amount) : 0;
                                $loan_amt =  isset($loan_det[0]->loan_amount) ? ($loan_det[0]->loan_amount) : 0;
                                $pending_amt =  isset($loan_det[0]->pending_amount) ? ($loan_det[0]->pending_amount) : 0;
                                $total_paid =$paid_amt + $loan_receipt->amount;
                                $total_pending = $loan_amt - $total_paid;
                                $loan_det[0]->paid_amount = $total_paid;
                                $loan_det[0]->pending_amount = $total_pending;
                                $loan_det[0]->save();
                            }
							if($request->tenant_id == 49)
                            {
                               $intrst_group = ChitDetail::where('id','=',$request->enrollment_id)->pluck('group_id');
								
								$cust_prized_status = Group::where('id','=',$intrst_group[0])->get();
								$interest_amount = $cust_prized_status[0]->interest_amount + $request->interest_amount;
								$cust_prized_status[0]->interest_amount = $interest_amount;
								$cust_prized_status[0]->save();
									
							
                            }
                            $response['status'] = 'Success';
                            $response['msg'] = \Lang::get('api.success_loan_receipt_added');
                            $response['data'] = $loan_receipt;
                            return response()->json($response,200);
                        }
                    } 
                    else {
                        $response['status']="Error";
                        $response['msg']=\Lang::get('api.global_error');
                        return response()->json($response,401);
                    } 
                }
            }
            catch(\Exception $e) { 
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                return response()->json($response, 401);
            }
        }
    }

    public function update_loan_receipt(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'customer_id' => 'required',
            'employee_id' => 'required',
            'receipt_date' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {                    
                    $loan_details = LoanDetail::find($request->id); 
                    $loan_receipt->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $loan_receipt->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;   
                    $loan_receipt->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;  
                    $loan_receipt->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;                     
                    $loan_receipt->loan_id = isset($request->loan_id) ? ($request->loan_id) : 0;         
                    $loan_receipt->reference_grp = isset($request->reference_grp) ? ($request->reference_grp) : '';     
                    $loan_receipt->amount = isset($request->amount) ? ($request->amount) : 0;  
                    $loan_receipt->interest_amount = isset($request->interest_amount) ? ($request->interest_amount) : 0;  
                    if(isset($request->receipt_date)) {
                        $c_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
                        $loan_receipt->receipt_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $loan_receipt->receipt_date = '0000-00-00';
                    }                
                    $loan_receipt->offline = isset($request->offline) ? ($request->offline) : 0;
                    $loan_receipt->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;              
                    $loan_receipt->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
                    $loan_receipt->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : ''; 
                    if(isset($request->cheque_date)) {
                        $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $loan_receipt->cheque_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $loan_receipt->cheque_date = '0000-00-00';
                    }            
                    if(isset($request->cheque_clear_return_date)) {
                        $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                        $loan_receipt->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                    }
                    else {
                        $loan_receipt->cheque_clear_return_date = '0000-00-00';
                    }     
                    $loan_receipt->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0;
                    $loan_receipt->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                    $loan_receipt->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '';
                    $loan_receipt->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                    if(isset($request->transaction_date)) {
                        $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $loan_receipt->transaction_date = date('Y-m-d',strtotime($t_date)); 
                    }
                    else {
                        $loan_receipt->transaction_date = '0000-00-00';
                    }
                    $loan_receipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $loan_receipt->status = isset($request->status) ? ($request->status) : 1;
                    $loan_receipt->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($loan_receipt->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_loan_receipt_updated');
                        $response['data'] = $loan_receipt;
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

     public function get_loan_receipt(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $loan_receipts = LoanReceipt::where($cond)->where('status',1)->orderBy('id','desc')->get();
            if($loan_receipts) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $i=1;
                foreach($loan_receipts as $loan_receipt) {
                    $loan_receipt->sno = $i;
                    $loan_receipt->tenant_info = isset($loan_receipt->tenant_det) ? ($loan_receipt->tenant_det) : [];
                    $loan_receipt->branch_info = isset($loan_receipt->branch_det) ? ($loan_receipt->branch_det) : [];
                    $loan_receipt->employee_info = isset($loan_receipt->employee_det) ? ($loan_receipt->employee_det) : [];
                    $loan_receipt->customer_info =  isset($loan_receipt->customer_det) ? ($loan_receipt->customer_det->name) : [];
                    $loan_receipt->loan_info =  isset($loan_receipt->loan_detail_det) ? ($loan_receipt->loan_detail_det) : [];
                    $loan_receipt->payment_type_info = isset($loan_receipt->payment_type_det) ? ($loan_receipt->payment_type_det) : [];
                    $loan_receipt->bank_name_info = isset($loan_receipt->bank_name_det) ? ($loan_receipt->bank_name_det) : [];
                    $loan_receipt->loan_date = ($loan_receipt->loan_date!=0) ? date('d/m/Y',strtotime($loan_receipt->loan_date)) : '';
                    $loan_receipt->cheque_date = ($loan_receipt->cheque_date!=0) ? date('d/m/Y',strtotime($loan_receipt->cheque_date)) : '';
                    $loan_receipt->transaction_date = ($loan_receipt->transaction_date!=0) ? date('d/m/Y',strtotime($loan_receipt->transaction_date)) : '';
                    $loan_amount =  isset($loan_receipt->loan_detail_det) ? ($loan_receipt->loan_detail_det->loan_amount) : 0;
                    $loan_receipt->loan_amount = $loan_amount;
                    $i++;
                }
                $response['data'] = $loan_receipts;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_loan_receipts(Request $request) {
        try { 
			$cond=[];
            $cond1 =[];
			if(isset($request->tenant_id))
			{
				$cond['tenant_id'] = $request->tenant_id;
			}			
			if(isset($request->customer_id))
			{
				$cond['customer_id'] = $request->customer_id;
			}
			if(isset($request->employee_id))
			{
				$cond['employee_id'] = $request->employee_id;
			}
			if(isset($request->loan_id))
			{
				$cond['loan_id'] = $request->loan_id;
			}
			if(isset($request->payment_type_id))
			{
				$cond['payment_type_id'] = $request->payment_type_id;
			}		  
            if($request->branch_id > 0)
            {
                $cond1['branch_id'] = $request->branch_id;
                $loan_receipts = LoanReceipt::where($cond)->where($cond1)->orderBy('id','desc')->get();
            }   
            if($request->branch_id == 0)
            {
                $loan_receipts = LoanReceipt::where($cond)->orderBy('id','desc')->get();
            }         
            
            $i=1;
            $grand_total_loan_amt = 0;
            $tot_loan_amt = 0;
            $grand_total_received_amt = 0;
            $tot_received_amt = 0;
            $grand_total_interest_amt = 0;
            $tot_interest_amt = 0;
            foreach($loan_receipts as $loan_receipt) {
                    $loan_receipt->sno = $i;
                    $loan_receipt->tenant_info = isset($loan_receipt->tenant_det) ? ($loan_receipt->tenant_det) : [];
                    $loan_receipt->branch_name = isset($loan_receipt->branch_det) ? ($loan_receipt->branch_det->branch_name) : [];
                    $loan_receipt->employee_name = isset($loan_receipt->employee_det) ? ($loan_receipt->employee_det->first_name.' '.$loan_receipt->employee_det->last_name) : [];
                    $loan_receipt->customer_name =  isset($loan_receipt->customer_det) ? ($loan_receipt->customer_det->name) : [];
                    $loan_amount =  isset($loan_receipt->loan_detail_det) ? ($loan_receipt->loan_detail_det->loan_amount) : 0;
                    $loan_receipt->loan_amount = $loan_amount;
                    $loan_receipt->payment_type_name = isset($loan_receipt->payment_type_det) ? ($loan_receipt->payment_type_det->payment_name) : [];
                    $loan_receipt->bank_name = isset($loan_receipt->bank_name_det) ? ($loan_receipt->bank_name_det->bank_name) : [];
                    $loan_receipt->loan_date = ($loan_receipt->loan_date!=0) ? date('d/m/Y',strtotime($loan_receipt->loan_date)) : '';
                    $loan_receipt->cheque_date = ($loan_receipt->cheque_date!=0) ? date('d/m/Y',strtotime($loan_receipt->cheque_date)) : '';
                    $loan_receipt->transaction_date = ($loan_receipt->transaction_date!=0) ? date('d/m/Y',strtotime($loan_receipt->transaction_date)) : '';
					if($loan_receipt->status == 1 )
					{
						$loan_receipt->status_name = "Active";
					}	
					else if($loan_receipt->status == 2 )
					{
						$loan_receipt->status_name = "Pending";
					}		
					else if($loan_receipt->status == 3 )
					{
						$loan_receipt->status_name = "Return";
					}	
					else if($loan_receipt->status == 0 )
					{
						$loan_receipt->status_name = "In-Active";
					}else
					{
						$loan_receipt->status_name = "-";
					}
                    $amount =  isset($loan_receipt->amount) ? ($loan_receipt->amount) : 0;
                    $interest_amount =  isset($loan_receipt->interest_amount) ? ($loan_receipt->interest_amount) : 0;
                    $tot_loan_amt += $loan_amount;
                    $tot_received_amt += $amount;
                    $tot_interest_amt += $interest_amount;
                    $i++;
                }
            $grand_total_loan_amt +=$tot_loan_amt;
            $grand_total_received_amt +=$tot_received_amt;
            $grand_total_interest_amt +=$tot_interest_amt;
            $received_format =0;
            $interest_format =0;
            $loan_format =0;
            if($grand_total_received_amt > 0)
            {   
                $num = $grand_total_received_amt;
                $received_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_interest_amt > 0)
            {   
                $num = $grand_total_interest_amt;
                $interest_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_loan_amt > 0)
            {   
                $num = $grand_total_loan_amt;
                $loan_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_total_loan_amt'] = $loan_format;
            $response['grand_total_received_amt'] = $received_format;
            $response['grand_total_interest_amt'] = $interest_format;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $loan_receipts;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_loan_receipt(Request $request) {
        try {
            if(isset($request->id)) {
                $loan_receipt = LoanReceipt::where('id','=',$request->id)->get();
                foreach($loan_receipt as $loan_rcpt)
                {
                    $loan_rcpt->deleted_by = $request->deleted_by;
                    $loan_rcpt->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                    if($loan_rcpt->save()) {
                        $loan_rcpt->delete();
                    }                    
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_loan_receipt_deleted');
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

   Public function get_customer_wise_loan_detail(Request $request)
    {
        try
        {
            // customer_id
            $cond = $request->conditions;
            $loan_det = LoanDetail::where($cond)->orderBy('id','desc')->where('status',1)->get();
            $response_data = [];
            if(count($loan_det)>0)
            {
                $total_interest = 0;
                $total_amount =0;
                foreach($loan_det as $loan)
                {  
                    $loan->loan_id = isset($loan->id) ? ($loan->id) : 0;
                    $loan->reference_grp = isset($loan->reference_grp) ? ($loan->reference_grp) : 0;
					$interest_amount = isset($loan->interest_amount) ? ($loan->interest_amount) : 0;
					$loan_amounts = isset($loan->loan_amount) ? ($loan->loan_amount) :0;
					// receipts 
					$receipts = LoanReceipt::SelectRaw('*, sum(amount) as total_amount, sum(interest_amount) as total_interest_amount ')->where('customer_id',$loan->customer_id)->where('loan_id',$loan->id)->where('status',1)->get();
                
					$paid_interest = isset($receipts[0]->total_interest_amount) ? ($receipts[0]->total_interest_amount) : 0;
					$paid_loan = isset($receipts[0]->total_amount) ? ($receipts[0]->total_amount) : 0;
                    $total_amount = $total_amount + $paid_loan;
                    $total_interest = $total_interest + $paid_interest;

                    $loan->total_paid_amount = $total_amount;
					$loan->pending_loan_amount = $loan_amounts - $total_amount;
					$loan->pending_interest = $interest_amount - $total_interest;
                }   
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $loan_det;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function get_loan_details_customer_wise(Request $request)
    {
        try
        {  // customer_id, receipt_date, loan_id
			$c_date = ($request->receipt_date['year']).'-'.sprintf('%02d',($request->receipt_date['month'])).'-'.sprintf('%02d',($request->receipt_date['day'])); 
			$rec_date = date('Y-m-d',strtotime($c_date)); 
                
            $cust_id = $request->customer_id;
            $loan_id = $request->loan_id;
            $current_date = date('Y-m-d');
            $response_data = [];
            $loan_details = LoanDetail::where('customer_id',$cust_id)->where('id',$loan_id)->get();
            foreach($loan_details as $loan)
            {
                $loan_det = [];
                $loan_id1 = $loan->id;
                $interest_type = $loan->interest_type;
                $loan_det['loan_id'] = $loan_id1;
           if($interest_type==3){
                $customer_id = $loan->customer_id;
                $loan_det['customer_id'] = $customer_id;
                $loan_date = $loan->loan_date;
                $loan_det['loan_date'] = $loan_date;
                // $datetime1 = new DateTime($loan_date);
                // $datetime2 = new DateTime($current_date);
                // $interval = $datetime1->diff($datetime2);
                // $payment_month_given = (($interval->format('%y') * 12) + $interval->format('%m'));                

                // $datetime3 = new DateTime($loan_date);
                // $datetime4 = new DateTime($rec_date);
                // $interval1 = $datetime3->diff($datetime4);
                // $receit_date_given = (($interval1->format('%y') * 12) + $interval1->format('%m'));    
				
    
                // if($payment_month_given >= $receit_date_given)
                // { 
                    $interest_per = isset($loan->interest_percentage) ? ($loan->interest_percentage) : 0;
					
                    $details_loan = LoanDetail::where('id',$loan_id1)->get();
                    $detailed_loan = [];
                    foreach($details_loan as $det_loan)
                    {
                        $list = [];
                        $loan_amount = isset($det_loan->loan_amount) ? ($det_loan->loan_amount) : 0;
                        $list['tot_loan_amount'] = $loan_amount;
                        // loan receipt 
                        $loan_receipt = LoanReceipt::selectRaw('*,sum(amount) as tot_paid_amt, sum(interest_amount) as tot_interst_amt')->where('loan_id',$det_loan->id)->where('customer_id',$det_loan->customer_id)->get();
                        $paid_amount = isset($loan_receipt[0]->tot_paid_amt) ? ($loan_receipt[0]->tot_paid_amt) : 0;
                        $paid_interest_amt = isset($loan_receipt[0]->tot_interst_amt) ? ($loan_receipt[0]->tot_interst_amt) : 0;
                                                   
                        $interest_amount = $loan_amount * $interest_per / 100;
						
                        $tot_pending_interst = $interest_amount - $paid_interest_amt;
                        $list['tot_interest_amount'] = $interest_amount ;
                        $list['paid_loan_amount'] = $paid_amount;
                        $list['paid_interest_amount'] = $paid_interest_amt;
                        $tot_pending_amt = $loan_amount - $paid_amount;
                        $list['tot_pending_loan_amt'] = $tot_pending_amt;
                        $list['tot_pending_interest_amt'] = $tot_pending_interst;
                        array_push($detailed_loan,$list);
                    }                        
                // }
                $loan_det['pending_loan_details'] = $detailed_loan;
                array_push($response_data,$loan_det);
            }      
			 else {
				 
                $customer_id = $loan->customer_id;
                $loan_det['customer_id'] = $customer_id;
                $loan_date = $loan->loan_date;
                $loan_det['loan_date'] = $loan_date;
                $datetime1 = new DateTime($loan_date);
                $datetime2 = new DateTime($current_date);
                $interval = $datetime1->diff($datetime2);
                $payment_month_given = (($interval->format('%y') * 12) + $interval->format('%m'));                

                $datetime3 = new DateTime($loan_date);
                $datetime4 = new DateTime($rec_date);
               // $interval1 = $datetime3->diff($datetime4);
                //$receit_date_given = (($interval1->format('%y') * 12) + $interval1->format('%m'));    
				
    $currentDateUnix = $datetime3;

    $weekNumbers = [];
    while ($datetime3 < $datetime4) {
		
        $weekNumbers[] = date('W', $currentDateUnix);
        $currentDateUnix = strtotime('+1 week', $currentDateUnix);
    }
	
    $receit_date_given = $weekNumbers[0];
	
                if($payment_month_given >= $receit_date_given)
                { 
                    $interest_per = isset($loan->interest_percentage) ? ($loan->interest_percentage) : 0;
					
                    $details_loan = LoanDetail::where('id',$loan_id1)->get();
                    $detailed_loan = [];
                    foreach($details_loan as $det_loan)
                    {
                        $list = [];
                        $loan_amount = isset($det_loan->loan_amount) ? ($det_loan->loan_amount) : 0;
                        $list['tot_loan_amount'] = $loan_amount;
                        // loan receipt 
                        $loan_receipt = LoanReceipt::selectRaw('*,sum(amount) as tot_paid_amt, sum(interest_amount) as tot_interst_amt')->where('loan_id',$det_loan->id)->where('customer_id',$det_loan->customer_id)->get();
                        $paid_amount = isset($loan_receipt[0]->tot_paid_amt) ? ($loan_receipt[0]->tot_paid_amt) : 0;
                        $paid_interest_amt = isset($loan_receipt[0]->tot_interst_amt) ? ($loan_receipt[0]->tot_interst_amt) : 0;
                                                   
                        $interest_amount = $loan_amount * $interest_per / 100 * $receit_date_given;
						
                        $tot_pending_interst = $interest_amount - $paid_interest_amt;
                        $list['tot_interest_amount'] = $interest_amount ;
                        $list['paid_loan_amount'] = $paid_amount;
                        $list['paid_interest_amount'] = $paid_interest_amt;
                        $tot_pending_amt = $loan_amount - $paid_amount;
                        $list['tot_pending_loan_amt'] = $tot_pending_amt;
                        $list['tot_pending_interest_amt'] = $tot_pending_interst;
                        array_push($detailed_loan,$list);
                    }                        
                }
                $loan_det['pending_loan_details'] = $detailed_loan;
                array_push($response_data,$loan_det);
			}	
			}			
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $response_data;
            return response()->json($response,200); 
        }   
		
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function cheque_return_loan(Request $request)
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
                $loanreceipt = LoanReceipt::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $loanreceipt->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $loanreceipt->cheque_clear_return_date = '0000-00-00';
                } 
                $loanreceipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $loanreceipt->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $loanreceipt->status = isset($request->status) ? ($request->status) : 3;
                if($loanreceipt->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $loanreceipt;
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
	
	public function cheque_cleared_loan(Request $request)
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
                $loanreceipt = LoanReceipt::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $loanreceipt->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $loanreceipt->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $loanreceipt->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $loanreceipt->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $loanreceipt->status = isset($request->status) ? ($request->status) : 1;
                if($loanreceipt->save()) {

                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_cleared');
                    $response['data'] = $loanreceipt;
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

    public function customer_ledger_loan_receipts(Request $request) {
        try { 
            $cond=[];
            if(isset($request->tenant_id))
            {
                $cond['tenant_id'] = $request->tenant_id;
            }
            if(isset($request->branch_id))
            {
                $cond['branch_id'] = $request->branch_id;
            }
            if(isset($request->customer_id))
            {
                $cond['customer_id'] = $request->customer_id;
            }
            if(isset($request->employee_id))
            {
                $cond['employee_id'] = $request->employee_id;
            }
            if(isset($request->loan_id))
            {
                $cond['loan_id'] = $request->loan_id;
            }
            if(isset($request->payment_type_id))
            {
                $cond['payment_type_id'] = $request->payment_type_id;
            }                   
            $loan_receipts = LoanReceipt::where($cond)->where('status',1)->orderBy('id','desc')->get();
            
            $i=1;
            $grand_total_loan_amt = 0;
            $tot_loan_amt = 0;
            $grand_total_received_amt = 0;
            $tot_received_amt = 0;
            $grand_total_interest_amt = 0;
            $tot_interest_amt = 0;
            foreach($loan_receipts as $loan_receipt) {
                    $loan_receipt->sno = $i;
                    $loan_receipt->tenant_info = isset($loan_receipt->tenant_det) ? ($loan_receipt->tenant_det) : [];
                    $loan_receipt->branch_name = isset($loan_receipt->branch_det) ? ($loan_receipt->branch_det->branch_name) : [];
                    $loan_receipt->employee_name = isset($loan_receipt->employee_det) ? ($loan_receipt->employee_det->first_name.' '.$loan_receipt->employee_det->last_name) : [];
                    $loan_receipt->customer_name =  isset($loan_receipt->customer_det) ? ($loan_receipt->customer_det->name) : [];
                    $loan_amount =  isset($loan_receipt->loan_detail_det) ? ($loan_receipt->loan_detail_det->loan_amount) : 0;
                    $loan_receipt->loan_amount = $loan_amount;
                    $loan_receipt->payment_type_name = isset($loan_receipt->payment_type_det) ? ($loan_receipt->payment_type_det->payment_name) : [];
                    $loan_receipt->bank_name = isset($loan_receipt->bank_name_det) ? ($loan_receipt->bank_name_det->bank_name) : [];
                    $loan_receipt->loan_date = ($loan_receipt->loan_date!=0) ? date('d/m/Y',strtotime($loan_receipt->loan_date)) : '';
                    $loan_receipt->cheque_date = ($loan_receipt->cheque_date!=0) ? date('d/m/Y',strtotime($loan_receipt->cheque_date)) : '';
                    $loan_receipt->transaction_date = ($loan_receipt->transaction_date!=0) ? date('d/m/Y',strtotime($loan_receipt->transaction_date)) : '';
                    if($loan_receipt->status == 1 )
                    {
                        $loan_receipt->status_name = "Active";
                    }   
                    else if($loan_receipt->status == 2 )
                    {
                        $loan_receipt->status_name = "Pending";
                    }       
                    else if($loan_receipt->status == 3 )
                    {
                        $loan_receipt->status_name = "Return";
                    }   
                    else if($loan_receipt->status == 0 )
                    {
                        $loan_receipt->status_name = "In-Active";
                    }else
                    {
                        $loan_receipt->status_name = "-";
                    }
                    $amount =  isset($loan_receipt->amount) ? ($loan_receipt->amount) : 0;
                    $interest_amount =  isset($loan_receipt->interest_amount) ? ($loan_receipt->interest_amount) : 0;
                    $tot_loan_amt += $loan_amount;
                    $tot_received_amt += $amount;
                    $tot_interest_amt += $interest_amount;
                    $i++;
                }
            $grand_total_loan_amt +=$tot_loan_amt;
            $grand_total_received_amt +=$tot_received_amt;
            $grand_total_interest_amt +=$tot_interest_amt;
            $response['grand_total_loan_amt'] = $grand_total_loan_amt;
            $response['grand_total_received_amt'] = $grand_total_received_amt;
            $response['grand_total_interest_amt'] = $grand_total_interest_amt;
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $loan_receipts;
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
}
