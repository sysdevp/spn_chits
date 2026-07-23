<?php

namespace App\Http\Controllers;

use App\Models\LoanDetail;
use App\Models\Payment;
use App\Models\ChitDetail;
use App\Models\SlabDue;
use App\Models\Receipt;
use App\Models\LoanReceipt;
use Illuminate\Http\Request;
use DateTime;
use Validator;

class LoanDetailController extends Controller
{
    public function store_loan_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'collect_employee_id' => 'required',
            'loan_date' => 'required',
            'loan_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                $loan_details = new LoanDetail();
                $loan_details->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                $loan_details->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;   
                //$loan_details->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
				$loan_details->customer_id = $customer_id;   
                $loan_details->collect_employee_id = isset($request->collect_employee_id) ? ($request->collect_employee_id) : 0; 
                $loan_details->loan_type = isset($request->loan_type) ? ($request->loan_type) : 0;  
                $loan_details->emi_months = isset($request->emi_months) ? ($request->emi_months) : 0;   
                $loan_details->reference_grp = isset($request->reference_grp) ? ($request->reference_grp) : '';
                $loan_details->loan_amount = isset($request->loan_amount) ? ($request->loan_amount) : 0;  
                $loan_details->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : 0;  
                $pending = $request->loan_amount - $request->paid_amount;
                $loan_details->pending_amount = $pending;  
                //$loan_details->loan_date = isset($request->loan_date) ? ($request->loan_date) : '0000-00-00';
                if(isset($request->loan_date)) {
                    $c_date = ($request->loan_date['year']).'-'.sprintf('%02d',($request->loan_date['month'])).'-'.sprintf('%02d',($request->loan_date['day'])); 
                    $loan_details->loan_date = date('Y-m-d',strtotime($c_date)); 
                }
                else {
                    $loan_details->loan_date = '0000-00-00';
                }
                $loan_details->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                $loan_details->interest_amount = isset($request->interest_amount) ? ($request->interest_amount) : 0;
                if(isset($request->completion_date)) {
                    $c_date = ($request->completion_date['year']).'-'.sprintf('%02d',($request->completion_date['month'])).'-'.sprintf('%02d',($request->completion_date['day'])); 
                    $loan_details->completion_date = date('Y-m-d',strtotime($c_date)); 
                }
                else {
                    $loan_details->completion_date = '0000-00-00';
                }  
                $loan_details->interest_type = isset($request->interest_type) ? ($request->interest_type) : 0;
                $loan_details->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;              
                $loan_details->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
                $loan_details->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : ''; 
                if(isset($request->cheque_date)) {
                    $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                    $loan_details->cheque_date = date('Y-m-d',strtotime($c_date)); 
                }
                else {
                    $loan_details->cheque_date = '0000-00-00';
                }            
                if(isset($request->cheque_clear_return_date)) {
                    $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $loan_details->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                }
                else {
                    $loan_details->cheque_clear_return_date = '0000-00-00';
                }    
                $loan_details->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0;
                $loan_details->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                $loan_details->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '';
                $loan_details->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                if(isset($request->transaction_date)) {
                    $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                    $loan_details->transaction_date = date('Y-m-d',strtotime($t_date)); 
                }
                else {
                    $loan_details->transaction_date = '0000-00-00';
                }
                $loan_details->remarks = isset($request->remarks) ? ($request->remarks) : '';
                if($request->payment_type_id == 2)
                {
                    $loan_details->status = isset($request->status) ? ($request->status) : 2;
                }
                $loan_details->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $loan_details->status = isset($request->status) ? ($request->status) : 1;
                if($loan_details->loan_amount >0)
                {
                    if($loan_details->save()) {
                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_loan_detail_added');
                        $response['data'] = $loan_details;
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
                    $response['status'] = 'Error';
                    $response['msg'] = \Lang::get('api.received_amt_not_found');
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

    public function update_loan_detail(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'collect_employee_id' => 'required',
            'loan_date' => 'required',
            'loan_amount' => 'required',
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
                    $loan_details->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $loan_details->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;   
                    $loan_details->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;  
                    $loan_details->collect_employee_id = isset($request->collect_employee_id) ? ($request->collect_employee_id) : 0;
                    $loan_details->loan_type = isset($request->loan_type) ? ($request->loan_type) : 0;   
                    $loan_details->emi_months = isset($request->emi_months) ? ($request->emi_months) : 0;         
                    $loan_details->reference_grp = isset($request->reference_grp) ? ($request->reference_grp) :'';     
                    $loan_details->loan_amount = isset($request->loan_amount) ? ($request->loan_amount) : 0;
					$loan_details->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : 0;  
					$pending = $request->loan_amount - $request->paid_amount;
					$loan_details->pending_amount = $pending; 
                    if(isset($request->loan_date)) {
                        $c_date = ($request->loan_date['year']).'-'.sprintf('%02d',($request->loan_date['month'])).'-'.sprintf('%02d',($request->loan_date['day'])); 
                        $loan_details->loan_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $loan_details->loan_date = '0000-00-00';
                    }
                    //$loan_details->loan_date = isset($request->loan_date) ? ($request->loan_date) : '0000-00-00';
                    $loan_details->interest_percentage = isset($request->interest_percentage) ? ($request->interest_percentage) : 0;
                    if(isset($request->completion_date)) {
                        $c_date = ($request->completion_date['year']).'-'.sprintf('%02d',($request->completion_date['month'])).'-'.sprintf('%02d',($request->completion_date['day'])); 
                        $loan_details->completion_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $loan_details->completion_date = '0000-00-00';
                    }  
                    $loan_details->interest_type = isset($request->interest_type) ? ($request->interest_type) : 0;
                    $loan_details->interest_amount = isset($request->interest_amount) ? ($request->interest_amount) : 0; 
                    $loan_details->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;              
                    $loan_details->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '';
                    $loan_details->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : ''; 
                    if(isset($request->cheque_date)) {
                        $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $loan_details->cheque_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $loan_details->cheque_date = '0000-00-00';
                    }            
                    if(isset($request->cheque_clear_return_date)) {
                        $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                        $loan_details->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                    }
                    else {
                        $loan_details->cheque_clear_return_date = '0000-00-00';
                    }     
                    $loan_details->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0;
                    $loan_details->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                    $loan_details->bank_branch_name = isset($request->bank_branch_name) ? ($request->bank_branch_name) : '';
                    $loan_details->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                    if(isset($request->transaction_date)) {
                        $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $loan_details->transaction_date = date('Y-m-d',strtotime($t_date)); 
                    }
                    else {
                        $loan_details->transaction_date = '0000-00-00';
                    }
                    $loan_details->remarks = isset($request->remarks) ? ($request->remarks) : '';
                    $loan_details->status = isset($request->status) ? ($request->status) : 1;
                    $loan_details->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($loan_details->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_loan_detail_updated');
                        $response['data'] = $loan_details;
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

     public function get_loan_detail(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $loan_details = LoanDetail::where($cond)->orderBy('id','desc')->get();
            $i=1;
            foreach($loan_details as $loan_detail) {
                $loan_detail->sno = $i;
                $loan_detail->tenant_info = isset($loan_detail->tenant_det) ? ($loan_detail->tenant_det) : [];
                $loan_detail->branch_info = isset($loan_detail->branch_det) ? ($loan_detail->branch_det) : [];
                $loan_detail->branch_name = isset($loan_detail->branch_det) ? ($loan_detail->branch_det->branch_name) : '';
                $loan_detail->customer_name = isset($loan_detail->customer_det) ? ($loan_detail->customer_det->name) : '';
                $loan_detail->employee_name = isset($loan_detail->employee_det) ? ($loan_detail->employee_det->first_name) : '';
                $loan_detail->employee_info = isset($loan_detail->employee_det) ? ($loan_detail->employee_det) : [];
                $loan_detail->customer_info =  isset($loan_detail->customer_det) ? ($loan_detail->customer_det->name) : [];
                $loan_detail->payment_type_info = isset($loan_detail->payment_type_det) ? ($loan_detail->payment_type_det) : [];
                $loan_detail->bank_name_info = isset($loan_detail->bank_name_det) ? ($loan_detail->bank_name_det) : [];
                $loan_detail->loan_date = ($loan_detail->loan_date!=0) ? date('d/m/Y',strtotime($loan_detail->loan_date)) : '';
                $loan_detail->cheque_date = ($loan_detail->cheque_date!=0) ? date('d/m/Y',strtotime($loan_detail->cheque_date)) : '';
                $loan_detail->transaction_date = ($loan_detail->transaction_date!=0) ? date('d/m/Y',strtotime($loan_detail->transaction_date)) : '';
                $loan_receipt = LoanReceipt::selectRaw('*,sum(amount) as total_receipt,sum(interest_amount) as total_interest')->where('loan_id',$loan_detail->id)->where('status',1)->get();
                $loan_receipts = isset($loan_receipt[0]->total_receipt) ? ($loan_receipt[0]->total_receipt) : 0;
                $loan_interest = isset($loan_receipt[0]->total_interest) ? ($loan_receipt[0]->total_interest) : 0;
                $loan_amount = isset($loan_detail->loan_amount) ? ($loan_detail->loan_amount) : 0;
                $interest_amount = isset($loan_detail->interest_amount) ? ($loan_detail->interest_amount) : 0;
                $loan_detail->pending_loan_amt = $loan_amount - $loan_receipts;
                $loan_detail->pending_interest_amt = $interest_amount - $loan_interest;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $loan_details;
            return response()->json($response,200);            
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_loan_details(Request $request) {
        try { 
            //$cond = $request->conditions; 
            if(isset($request->tenant_id)) {
                $loan_details = LoanDetail::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $loan_details = LoanDetail::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($loan_details as $loan_detail) {
                    $loan_detail->sno = $i;
                    $loan_detail->tenant_info = isset($loan_detail->tenant_det) ? ($loan_detail->tenant_det) : [];
                    $loan_detail->branch_info = isset($loan_detail->branch_det) ? ($loan_detail->branch_det) : [];
                    $loan_detail->employee_info = isset($loan_detail->employee_det) ? ($loan_detail->employee_det) : [];
                    $loan_detail->customer_info =  isset($loan_detail->enrollment_det->customer_det) ? ($loan_detail->enrollment_det->customer_det->name) : [];
                    $loan_detail->payment_type_info = isset($loan_detail->payment_type_det) ? ($loan_detail->payment_type_det) : [];
                    $loan_detail->bank_name_info = isset($loan_detail->bank_name_det) ? ($loan_detail->bank_name_det) : [];
                    $loan_detail->loan_date = ($loan_detail->loan_date!=0) ? date('d/m/Y',strtotime($loan_detail->loan_date)) : '';
                    $loan_detail->cheque_date = ($loan_detail->cheque_date!=0) ? date('d/m/Y',strtotime($loan_detail->cheque_date)) : '';
                    $loan_detail->transaction_date = ($loan_detail->transaction_date!=0) ? date('d/m/Y',strtotime($loan_detail->transaction_date)) : '';
                    $i++;
                }           
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $loan_details;
                return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_loan_detail(Request $request) {
        try {
            if(isset($request->id)) {
                $loan_detail = LoanDetail::where('id','=',$request->id)->get();
                foreach($loan_detail as $loan_det)
                {
                    $loan_det->deleted_by = $request->deleted_by;
                    $loan_det->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                    if($loan_det->save()) {
                        $loan_det->delete();
                    }                    
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_loan_detail_deleted');
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
            $response['msg'] = $loan_detail;
            return response()->json($response, 401);
        }
    }

    Public function alert_auction_details(Request $request)
    {
        try
        {   // customer_id
            $payment = Payment::where('customer_id',$request->customer_id)->where('tenant_id',$request->tenant_id)->get();
            $i =1;
            foreach($payment as $pay)
            {    $pay->sno =$i;
                if($pay->payment_for == 1)
                {
                    $pay->group_name = isset($pay->group_det) ? ($pay->group_det->group_name) : '';
                    $pay->chit_value = isset($pay->group_det->scheme_det) ? ($pay->group_det->scheme_det->chit_value) : 0;
                    $pay->payment_release = isset($pay->total_amount) ? ($pay->total_amount) : 0;
                    $enrol_id = isset($pay->enrollment_id) ? ($pay->enrollment_id) : '';
                    $receipt = Receipt::SelectRaw('*,sum(received_amount) as total_received_amount')->where('enrollment_id',$enrol_id)->where('status',1)->get();
                    $pay->total_receipt_amount = isset($receipt[0]->total_received_amount) ? ($receipt[0]->total_received_amount) : '';

                }                    
                if($pay->payment_for == 7)
                { 
                    $slab_id = isset($pay->enrol_det) ? ($pay->enrol_det->slab_id) : '';
                    $slab_types = SlabDue::where('id',$slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $pay->group_name = "GROUP NAME:Commitment | SLAB NAME :".$slab_name;
                    $pay->chit_value = isset($slab_types[0]->scheme_det) ? (strval(round($slab_types[0]->scheme_det->chit_value))) : 0; 
                    $pay->payment_release = isset($pay->total_amount) ? ($pay->total_amount) : 0;
                    $enrol_id = isset($pay->enrollment_id) ? ($pay->enrollment_id) : '';
                    $receipt = Receipt::SelectRaw('*,sum(received_amount) as total_received_amount')->where('enrollment_id',$enrol_id)->where('status',1)->get();
                    $pay->total_receipt_amount = isset($receipt[0]->total_received_amount) ? ($receipt[0]->total_received_amount) : '';
                }
				$i++;
            }
			$response['status'] = "Success";
            $response['data'] = $payment;
            return response()->json($response,200);           
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }


    Public function alert_loan_details(Request $request)
    {
        try
        {   // customer_id
            $loan_detail = LoanDetail::where('customer_id',$request->customer_id)->where('tenant_id',$request->tenant_id)->get();                
			$i =1;
            foreach($loan_detail as $ld)
            {     
				$ld->sno =$i;
                $ld->reference_grp_name = isset($ld->reference_grp) ? ($ld->reference_grp) : '';
                $ld->loan_amount = isset($ld->loan_amount) ? ($ld->loan_amount) : '';
                $loan_receipt = LoanReceipt::SelectRaw('*,sum(amount) as total_received_amount')->where('loan_id',$ld->id)->where('status',1)->get();
                $ld->paid_loan_amount = isset($loan_receipt[0]->total_received_amount) ? ($loan_receipt[0]->total_received_amount) : 0;
				$i++;
            }
			$response['status'] = "Success";
            $response['data'] = $loan_detail;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = "Error";
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	 public function get_loan_customer(Request $request) {
        try {
            //id,tenant_id
            $cond =[];
            $cond1 =[];
			if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
                $loan_details = LoanDetail::where($cond)->where($cond1)->groupBy('customer_id')->orderBy('id','desc')->get();    
            }
            if($request->branch_id == 0)
            {
                $loan_details = LoanDetail::where($cond)->groupBy('customer_id')->orderBy('id','desc')->get();  
            }
            $i=1;
            $grand_total_loan_amt = 0;
            $tot_loan_amt = 0;
            foreach($loan_details as $loan_detail) {
                $loan_detail->sno = $i;
                $loan_detail->tenant_info = isset($loan_detail->tenant_det) ? ($loan_detail->tenant_det) : [];
                $loan_detail->branch_name = isset($loan_detail->branch_det) ? ($loan_detail->branch_det->branch_name) : [];
                $loan_receipt = LoanDetail::SelectRaw('sum(loan_amount) as loan_amount_given')->where('customer_id',$loan_detail->customer_id)->where('status',1)->get();
				$loan_amount = isset($loan_receipt[0]->loan_amount_given) ? ($loan_receipt[0]->loan_amount_given) : 0;
                $loan_detail->loan_amount = $loan_amount;
                $loan_detail->customer_name =  isset($loan_detail->customer_det) ? ($loan_detail->customer_det->name) : [];
				$loan_detail->mobile =  isset($loan_detail->customer_det) ? ($loan_detail->customer_det->mobile_no) : [];
				$loan_detail->customer_address =  isset($loan_detail->customer_det) ? ($loan_detail->customer_det->address_line_1.''.$loan_detail->customer_det->address_line_2) : [];
                $tot_loan_amt += $loan_amount;
                $i++;
            }
            $grand_total_loan_amt +=$tot_loan_amt;

            $grand_total_loan_amt_format =0;

            if($grand_total_loan_amt > 0)
            {   
                $num = $grand_total_loan_amt;
                $grand_total_loan_amt_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_total_loan_amt'] = $grand_total_loan_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $loan_details;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    Public function loan_customer_outstanding(Request $request)
    {
        try
        {
            // customer_id, branch_id
            $cond =  [];
            $cond1 = [];
            if(isset($request->tenant_id)){
                $cond['tenant_id'] = $request->tenant_id; 
            }
            if($request->branch_id > 0){
                $cond1['branch_id'] = $request->branch_id; 
            }
            if(isset($request->customer_id)){
                $cond['customer_id'] = $request->customer_id; 
            }
            $loan_det = LoanDetail::where($cond)->where($cond1)->orderBy('id','desc')->where('status',1)->get();
            if($request->branch_id ==0)
            {
                $loan_det = LoanDetail::where($cond)->orderBy('id','desc')->where('status',1)->get();
            }
            $response_data = [];
            
            $i =1;
            $tot_loan_amt = 0;
            $grand_total_loan_amt = 0;
            $grand_total_paid_amt = 0;
            $tot_paid_amt = 0;
            $grand_total_pending_amt = 0;
            $tot_pending_amt = 0;
            $grand_total_paid_interest_amt = 0;
            $tot_paid_interest_amt = 0;
            $grand_total_pending_interest_amt = 0;
            $tot_pending_interest_amt = 0;
            foreach($loan_det as $loan)
            {  
                $total_interest = 0;
                $total_amount =0;
                $loan->sno = $i;
                $loan->branch_name = isset($loan->branch_det) ? ($loan->branch_det->branch_name) : '';
                $loan->customer_name = isset($loan->customer_det) ? ($loan->customer_det->name) : ''; 
                $loan->mobile_no = isset($loan->customer_det) ? ($loan->customer_det->mobile_no) : ''; 
                $loan->reference_grp = isset($loan->reference_grp) ? ($loan->reference_grp) : 0;
                $interest_amount = isset($loan->interest_amount) ? ($loan->interest_amount) : 0;
                $loan_amounts = isset($loan->loan_amount) ? ($loan->loan_amount) :0;
                // receipts 
                $receipts = LoanReceipt::SelectRaw('*, sum(amount) as total_amount, sum(interest_amount) as total_interest_amount ')->where('customer_id',$loan->customer_id)->where('loan_id',$loan->id)->where('status',1)->get();
            
                $paid_interest = isset($receipts[0]->total_interest_amount) ? ($receipts[0]->total_interest_amount) : 0;
                $paid_loan = isset($receipts[0]->total_amount) ? ($receipts[0]->total_amount) : 0;
                $total_amount = $total_amount + $paid_loan;
                $total_interest = $total_interest + $paid_interest;
                $loan->total_loan_amount = $loan_amounts;
                $loan->total_paid_loan_amount = $total_amount;
                $loan->total_paid_interest = $total_interest;
                $pending_loan_amount = $loan_amounts - $total_amount;
                $loan->pending_loan_amount = $pending_loan_amount; 
                $pending_interest = $interest_amount - $total_interest;
                $loan->pending_interest = $pending_interest; 
                $i++;
                $tot_loan_amt += $loan_amounts;
                $tot_paid_amt += $total_amount;
                $tot_pending_amt += $pending_loan_amount;
                $tot_paid_interest_amt += $total_interest;
                $tot_pending_interest_amt += $pending_interest;
            }   
            $grand_total_loan_amt += $tot_loan_amt;
            $grand_total_paid_amt += $tot_paid_amt;
            $grand_total_pending_amt += $tot_pending_amt;
            $grand_total_paid_interest_amt += $tot_paid_interest_amt;
            $grand_total_pending_interest_amt += $tot_pending_interest_amt;

            $grand_total_loan_amt_format =0;
            $grand_total_paid_amt_format =0;
            $grand_total_pending_amt_format =0;
            $grand_total_paid_interest_amt_format =0;
            $grand_total_pending_interest_amt_format =0;

            if($grand_total_loan_amt > 0)
            {   
                $num = $grand_total_loan_amt;
                $grand_total_loan_amt_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_paid_amt > 0)
            {   
                $num = $grand_total_paid_amt;
                $grand_total_paid_amt_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_pending_amt > 0)
            {   
                $num = $grand_total_pending_amt;
                $grand_total_pending_amt_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_paid_interest_amt > 0)
            {   
                $num = $grand_total_paid_interest_amt;
                $grand_total_paid_interest_amt_format = $this->moneyFormatIndia($num);  
            }
            if($grand_total_pending_interest_amt > 0)
            {   
                $num = $grand_total_pending_interest_amt;
                $grand_total_pending_interest_amt_format = $this->moneyFormatIndia($num);  
            }
            $response['grand_total_loan_amt'] = $grand_total_loan_amt_format;
            $response['grand_total_paid_amt'] = $grand_total_paid_amt_format;
            $response['grand_total_pending_amt'] = $grand_total_pending_amt_format;
            $response['grand_total_paid_interest_amt'] = $grand_total_paid_interest_amt_format;
            $response['grand_total_pending_interest_amt'] = $grand_total_pending_interest_amt_format;
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $loan_det;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
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

}
