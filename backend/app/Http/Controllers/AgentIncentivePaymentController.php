<?php

namespace App\Http\Controllers;

use App\Models\AgentIncentivePayment;
use App\Models\ChitDetail;
use App\Models\Group;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\SlabDue;
use Illuminate\Http\Request;
use DateTime;
use Validator;

class AgentIncentivePaymentController extends Controller
{
    public function store_agent_incentive_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_agent_id' => 'required',
            'payment_date' => 'required',
            'paid_amount' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try { 
                if(isset($request->Incentive)) 
                {                    
                    $count = count($request->Incentive);
                    $commission_no = '';
                    for($i=0; $i<$count; $i++)
                    {   
                        $agent_incentive_payment = new AgentIncentivePayment();
                        $agent_incentive_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                        $agent_incentive_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                        $agent_incentive_payment->employee_agent_id = isset($request->employee_agent_id) ? ($request->employee_agent_id) : '0';
                        $agent_incentive_payment->agent_type_id  = isset($request->agent_type_id ) ? ($request->agent_type_id ) : 0; 
                        $agent_incentive_payment->enrollment_id = isset($request->Incentive[$i]['enrollment_id']) ? ($request->Incentive[$i]['enrollment_id']) : '0';
                        $agent_incentive_payment->customer_id = isset($request->Incentive[$i]['customer_id']) ? ($request->Incentive[$i]['customer_id']) : '0';
                        $agent_incentive_payment->group_id = isset($request->Incentive[$i]['group_id']) ? ($request->Incentive[$i]['group_id']) : '0';
                        $agent_incentive_payment->ticket_no = isset($request->Incentive[$i]['ticket_no']) ? ($request->Incentive[$i]['ticket_no']) : '0';
                        $agent_incentive_payment->customer_incentive_amount = isset($request->Incentive[$i]['customer_incentive_amount']) ? ($request->Incentive[$i]['customer_incentive_amount']) : '0';  
                        if(isset($request->payment_date)) {
                            $c_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                            $agent_incentive_payment->payment_date = date('Y-m-d',strtotime($c_date));
                        }
                        else {
                            $agent_incentive_payment->payment_date = '0000-00-00';
                        }
                        //$agent_incentive_payment->payment_date = isset($request->payment_date) ? ($request->payment_date) : '0000-00-00';
                        $agent_incentive_payment->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : 0;
                        $agent_incentive_payment->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                        $agent_incentive_payment->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '0';
                        $agent_incentive_payment->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0'; 
                        if(isset($request->cheque_date)) {
                            $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                            $agent_incentive_payment->cheque_date = date('Y-m-d',strtotime($c_date)); 
                        }
                        else {
                            $agent_incentive_payment->cheque_date = '0000-00-00';
                        }                        //$agent_incentive_payment->cheque_date = isset($request->cheque_date) ? ($request->cheque_date) : '0000-00-00';
                        if(isset($request->cheque_clear_return_date)) {
                            $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                            $agent_incentive_payment->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                        }
                        else {
                            $agent_incentive_payment->cheque_clear_return_date = '0000-00-00';
                        }     
                        //$agent_incentive_payment->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : '0000-00-00';
                        $agent_incentive_payment->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                        $agent_incentive_payment->branch_name = isset($request->branch_name) ? ($request->branch_name) : '0';
                        $agent_incentive_payment->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                        if(isset($request->transaction_date)) {
                            $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                            $agent_incentive_payment->transaction_date = date('Y-m-d',strtotime($t_date)); 
                        }
                        else {
                            $agent_incentive_payment->transaction_date = '0000-00-00';
                        }
                       // $agent_incentive_payment->transaction_date = isset($request->transaction_date) ? ($request->transaction_date) : '0000-00-00';
                        $agent_incentive_payment->remarks = isset($request->remarks) ? ($request->remarks) : '0';                        
                        $agent_incentive_payment->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                        $agent_incentive_payment->status = isset($request->status) ? ($request->status) : 1;
                        if($request->payment_type_id == 2){
                            $agent_incentive_payment->status = isset($request->status) ? ($request->status) : 2;
                        }

                        if($agent_incentive_payment->paid_amount > 0)
                        {
                            if($agent_incentive_payment->customer_incentive_amount >0) {                                
                                if($agent_incentive_payment->save()) {
                                    if($commission_no=='') {
                                        $commission_no = 'INCE-'.sprintf('%05d', $agent_incentive_payment->id);
                                    }                                
                                    $agent_incentive_payment->commission_no = $commission_no;
                                    if($agent_incentive_payment->save())
                                    {
                                        if($request->payment_type_id ==1)
                                        {
                                            $chit = ChitDetail::find($agent_incentive_payment->enrollment_id);
                                            $chit->paid_agent_incentive = $chit->paid_agent_incentive + $agent_incentive_payment->customer_incentive_amount;
                                            $chit->balance_agent_incentive = $chit->balance_agent_incentive - $agent_incentive_payment->customer_incentive_amount;
                                            $chit->save();
                                        }
                                    }
                                } 
                            }
                        }
                        else
                        {
                            $response['status'] = 'Error';
                            $response['msg'] = "Paid amount Invalid";
                            return response()->json($response, 200);
                        }
                    }
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_agent_incentive_payment_added');
                    $response['data'] = $agent_incentive_payment;
                    return response()->json($response,200);
                } 
            }
            catch(\Exception $e) { 
                $response['status'] = 'Error';
                $response['msg'] = \Lang::get('api.global_error');
                $response['msg'] = $e;
                return response()->json($response, 401);
            }
        }
    }

    public function update_agent_incentive_payment(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'employee_agent_id' => 'required',
            'payment_date' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->commission_no)) {
                    $agent_incentive_payment = AgentIncentivePayment::find($request->commission_no); 
                    $agent_incentive_payment->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
                    $agent_incentive_payment->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $agent_incentive_payment->employee_agent_id = isset($request->employee_agent_id) ? ($request->employee_agent_id) : '0';
                    if(isset($request->payment_date)) {
                        $c_date = ($request->payment_date['year']).'-'.sprintf('%02d',($request->payment_date['month'])).'-'.sprintf('%02d',($request->payment_date['day'])); 
                        $agent_incentive_payment->payment_date = date('Y-m-d',strtotime($c_date));
                    }
                    else {
                        $agent_incentive_payment->payment_date = '0000-00-00';
                    }
                    $agent_incentive_payment->paid_amount = isset($request->paid_amount) ? ($request->paid_amount) : '0';
                    $agent_incentive_payment->payment_type_id = isset($request->payment_type_id) ? ($request->payment_type_id) : 0;
                    $agent_incentive_payment->debit_to   = isset($request->debit_to ) ? ($request->debit_to ) : '0';
                    $agent_incentive_payment->cheque_no = isset($request->cheque_no) ? ($request->cheque_no) : '0';
                    if(isset($request->cheque_date)) {
                        $c_date = ($request->cheque_date['year']).'-'.sprintf('%02d',($request->cheque_date['month'])).'-'.sprintf('%02d',($request->cheque_date['day'])); 
                        $agent_incentive_payment->cheque_date = date('Y-m-d',strtotime($c_date)); 
                    }
                    else {
                        $agent_incentive_payment->cheque_date = '0000-00-00';
                    }
                    if(isset($request->cheque_clear_return_date)) {
                        $cc_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                        $agent_incentive_payment->cheque_clear_return_date = date('Y-m-d',strtotime($cc_date)); 
                    }
                    else {
                        $agent_incentive_payment->cheque_clear_return_date = '0000-00-00';
                    }
                    $agent_incentive_payment->bank_name_id = isset($request->bank_name_id) ? ($request->bank_name_id) : 0;
                    $agent_incentive_payment->branch_name = isset($request->branch_name) ? ($request->branch_name) : '0';
                    $agent_incentive_payment->transaction_no = isset($request->transaction_no) ? ($request->transaction_no) : 0;
                    if(isset($request->transaction_date)) {
                        $t_date = ($request->transaction_date['year']).'-'.sprintf('%02d',($request->transaction_date['month'])).'-'.sprintf('%02d',($request->transaction_date['day'])); 
                        $agent_incentive_payment->transaction_date = date('Y-m-d',strtotime($t_date)); 
                    }
                    else {
                        $agent_incentive_payment->transaction_date = '0000-00-00';
                    }
                    $agent_incentive_payment->remarks = isset($request->remarks) ? ($request->remarks) : '0';
                    $agent_incentive_payment->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($agent_incentive_payment->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_agent_incentive_payment_updated');
                        $response['data'] = $agent_incentive_payment;
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
                return response()->json($response, 401);
            }
        }
    }

    public function get_agent_incentive_payments(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $agent_commission_payments = AgentIncentivePayment::where($cond)->orderBy('id','desc')->get();
            if($agent_commission_payments) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $i=1;
                foreach($agent_incentive_payments as $agent_incentive_payment) {
                    $agent_incentive_payment->sno = $i;
                    $agent_incentive_payment->tenant_info = isset($agent_incentive_payment->tenant_det) ? ($agent_incentive_payment->tenant_det) : [];
                    $agent_incentive_payment->branch_info = isset($agent_incentive_payment->branch_det) ? ($agent_incentive_payment->branch_det) : [];
                    $agent_incentive_payment->employee_agent_info = isset($agent_incentive_payment->employee_agent_det) ? ($agent_incentive_payment->employee_agent_det) : [];
                    $agent_incentive_payment->enrollment_info = isset($agent_incentive_payment->enrollment_det) ? ($agent_incentive_payment->enrollment_det) : [];
                    $agent_incentive_payment->group_info =  isset($agent_incentive_payment->enrollment_det->group_det) ? ($agent_incentive_payment->enrollment_det->group_det->group_name) : [];
                    $agent_incentive_payment->customer_info =  isset($agent_incentive_payment->enrollment_det->customer_det) ? ($agent_incentive_payment->enrollment_det->customer_det->name) : [];
                    $agent_incentive_payment->payment_type_info = isset($agent_incentive_payment->payment_type_det) ? ($agent_incentive_payment->payment_type_det) : [];
                    $agent_incentive_payment->bank_name_det = isset($agent_incentive_payment->bank_name_det) ? ($agent_incentive_payment->bank_name_det) : [];
                    $agent_incentive_payment->payment_date = ($agent_incentive_payment->payment_date!=0) ? date('d/m/Y',strtotime($agent_incentive_payment->payment_date)) : '';
                    $agent_incentive_payment->cheque_date = ($agent_incentive_payment->cheque_date!=0) ? date('d/m/Y',strtotime($agent_incentive_payment->cheque_date)) : '';
                    $agent_incentive_payment->transaction_date = ($agent_incentive_payment->transaction_date!=0) ? date('d/m/Y',strtotime($agent_incentive_payment->transaction_date)) : '';
                    $agent_incentive_payment->paid_amount = isset($agent_incentive_payment->paid_amount) ? (strval(round($agent_incentive_payment->paid_amount))) : [];
                    $i++;
                }
                $response['data'] = $agent_incentive_payments;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_agent_incentive_payments(Request $request) {
        try { 
            //$cond = $request->conditions; 
            if(isset($request->tenant_id)) {
                $agent_incentive_payments = AgentIncentivePayment::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->groupBy('commission_no')->get();
            }
            else {
                $agent_incentive_payments = AgentIncentivePayment::orderBy('id','desc')->groupBy('commission_no')->get();
            }
            $i=1;
            foreach($agent_incentive_payments as $agent_incentive_payment) {
                $agent_incentive_payment->sno = $i;
                $agent_incentive_payment->branch_info = isset($agent_incentive_payment->branch_det) ? ($agent_incentive_payment->branch_det) : [];
                $agent_incentive_payment->employee_agent_info = isset($agent_incentive_payment->employee_agent_det) ? ($agent_incentive_payment->employee_agent_det) : [];
                $agent_incentive_payment->paid_amount = isset($agent_incentive_payment->paid_amount) ? (strval(round($agent_incentive_payment->paid_amount))) : [];
                $i++;
            }
            if($agent_incentive_payments) {
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $agent_incentive_payments;
                return response()->json($response,200);
            }
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_agent_incentive_payment(Request $request) {
        try {
            if(isset($request->commission_no)) {
                $agent_incentive_payment = AgentIncentivePayment::where('commission_no','=',$request->commission_no)->get();
                foreach($agent_incentive_payment as $deletes)
                {
                    $deletes->deleted_by = $request->deleted_by;
                    $deletes->deletion_remark = isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                    if($deletes->save()) {
                        $deletes->delete();
                    }                    
                }
                $response['status'] = 'Success';
                $response['msg'] = \Lang::get('api.success_agent_incentive_payment_deleted');
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
            $response['msg'] = $agent_incentive_payment;
            return response()->json($response, 401);
        }
    }

    public function update_status(Request $request) {
        try {
            if(isset($request->id)) {
                $agent_incentive_payment = AgentIncentivePayment::find($request->id); 
                $agent_incentive_payment->status = ($agent_incentive_payment->status) ? 0 : 1;
                if($agent_incentive_payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_status_updated');
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
            return response()->json($response, 401);
        } 
    }

    public function get_agent_incentive_enrol_details(Request $request)
    {
        try
        {   // introducer_id, branch_id, agent_type
            $cond = $request->conditions; 
            $chit_details = ChitDetail::where($cond)->where('balance_agent_incentive','!=',0)->orderBy('id','desc')->get();
            $response_data = [];
            foreach($chit_details as $cd) 
            {   
                $enrol_det = [];
                $enrol_det['group_info'] =[];
                $enrol_det['customer_info'] = [];
                if(isset($cd->customer_det)) {
                    $enrol_det['customer_info'] = isset($cd->customer_det->name) ? ($cd->customer_det->name) : [];
                }
                $enrol_det['ticket_no'] = isset($cd->ticket_no) ? ($cd->ticket_no) : 0;
                $enrol_det['group_id'] = isset($cd->group_id) ? ($cd->group_id) : 0;
                if($cd->chit_type != 3)
                {
                    if(isset($cd->group_det)) {
                        $enrol_det['group_info']= isset($cd->group_det->group_name) ? ($cd->group_det->group_name) : '';
                    }
                }
                if($cd->chit_type == 3)
                {
                    $slab_types = SlabDue::where('id',$cd->slab_id)->get();   
                    $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : "";
                    $enrol_det['group_info'] = "Commitment ".$slab_name;
                }
                $enrol_det['customer_id'] = isset($cd->customer_id) ? ($cd->customer_id) : 0;
                $enrol_det['enrollment_id'] = isset($cd->id) ? ($cd->id) : 0;
                $enrol_det['total_commission'] = isset($cd->agent_incentive) ? ($cd->agent_incentive) : 0;
                $enrol_det['paid_commission'] = isset($cd->paid_agent_incentive) ? ($cd->paid_agent_incentive) : 0;
                $enrol_det['pending_commission'] = isset($cd->balance_agent_incentive) ? ($cd->balance_agent_incentive) : 0;
                $chits_table = ChitDetail::selectRaw('*,sum(balance_agent_incentive) as overall_pending')->where('balance_agent_incentive','>',0)->where($cond)->groupBy('introducer_id')->get();
                $enrol_det['overall_pending'] = isset($chits_table[0]->overall_pending) ? (round($chits_table[0]->overall_pending)) : 0;
                if($chit_details) {
                    array_push($response_data,$enrol_det);
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
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }
    
    public function cheque_return_incentive_payment(Request $request)
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
                $agent_incentive_payment = AgentIncentivePayment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $agent_incentive_payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $agent_incentive_payment->cheque_clear_return_date = '0000-00-00';
                } 
                $agent_incentive_payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $agent_incentive_payment->cheque_return_amount = isset($request->cheque_return_amount) ? ($request->cheque_return_amount) : 0;
                $agent_incentive_payment->status = isset($request->status) ? ($request->status) : 3;
                if($agent_incentive_payment->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_cheque_returned');
                    $response['data'] = $agent_incentive_payment;
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
    
    public function cheque_cleared_incentive_payment(Request $request)
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
                $agent_incentive_payment = AgentIncentivePayment::find($request->id);
                if(isset($request->cheque_clear_return_date)) {
                    $ccr_date = ($request->cheque_clear_return_date['year']).'-'.sprintf('%02d',($request->cheque_clear_return_date['month'])).'-'.sprintf('%02d',($request->cheque_clear_return_date['day'])); 
                    $agent_incentive_payment->cheque_clear_return_date = date('Y-m-d',strtotime($ccr_date));
                }
                else {
                    $agent_incentive_payment->cheque_clear_return_date = '0000-00-00';
                }
                //$receipt->cheque_clear_return_date = isset($request->cheque_clear_return_date) ? ($request->cheque_clear_return_date) : "0000-00-00";
                $agent_incentive_payment->remarks = isset($request->remarks) ? ($request->remarks) : '';
                $agent_incentive_payment->cheque_debit_to = isset($request->cheque_debit_to) ? ($request->cheque_debit_to) : 0; 
                $agent_incentive_payment->status = isset($request->status) ? ($request->status) : 1;
                if($agent_incentive_payment->save()) {                   
                    $chit = ChitDetail::find($agent_incentive_payment->enrollment_id);
                    $chit->paid_agent_incentive = $chit->paid_agent_incentive + $agent_incentive_payment->customer_incentive_amount;
                    $chit->balance_agent_incentive = $chit->balance_agent_incentive - $agent_incentive_payment->customer_incentive_amount;
                    if($chit->save())
                    {
                        $response['status'] = 'Success';
                        $response['msg'] = \Lang::get('api.success_cheque_cleared');
                        $response['data'] = $agent_incentive_payment;
                        return response()->json($response,200);
                    } 
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

    Public function agent_type_details(Request $request)
    {
        try
        {  // agent_type_id, branch_id      
            $cond = $request->agent_type_id;
            if($cond == 1)
            {
                if($request->branch_id > 0)
                {
                    $cond1['branch_id'] = $request->branch_id;
                    $agent_list = Customer::where($cond1)->get();
                }
                if($request->branch_id == 0)
                {
                    $agent_list = Customer::orderBy('id','desc')->get();
                }
            }
            else if($cond == 2)
            {
                if($request->branch_id > 0)
                {
                    $cond1['branch_id'] = $request->branch_id;
                    $agent_list = Employee::where($cond1)->where('employee_type',1)->get();          
                }
                if($request->branch_id == 0)
                {
                    $agent_list = Employee::where('employee_type',1)->get();        
                }
                $name= isset($agent_list[0]->first_name) ?($agent_list[0]->first_name.' '.$agent_list[0]->last_name) : '';         
            }
            else if($cond == 3)
            {
                if($request->branch_id > 0)
                {
                    $cond1['branch_id'] = $request->branch_id;
                    $agent_list = Employee::where($cond1)->where('employee_type',2)->get();                    
                }
                if($request->branch_id == 0)
                {
                    $agent_list = Employee::where('employee_type',2)->get();    
                }
                $name= isset($agent_list[0]->first_name) ?($agent_list[0]->first_name.' '.$agent_list[0]->last_name) : '';  
            }
            $i=1;
            foreach($agent_list as $al)
            {
                $al->tenant_info = isset($al->tenant_det) ? ($al->tenant_det) : [];
                if($cond ==1)
                {
                    $name= isset($al->name) ?($al->name) : '';
                }
                if($cond ==2)
                {
                     $name= isset($al->first_name) ?($al->first_name.' '.$al->last_name) : '';  
                }
                if($cond ==3)
                {
                    $name= isset($al->first_name) ?($al->first_name.' '.$al->last_name) : ''; 
                }
                $al->agent_name = $name;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $agent_list;
            return response()->json($response,200);
        }
        catch(\Exception $e)
        {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
