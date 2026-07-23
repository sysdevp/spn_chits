<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuiteFilling;
use App\Models\ChitDetail;
use App\Models\Receipt;
use App\Models\BiddingDetail;
use App\Models\Customer;
use App\Models\Scheme;
use App\Models\Lawyer;
use App\Models\CaseName;
use App\Models\suiteFillingTimeline;
use DB;
use DateTime;

class SuitFillingController extends Controller
{
    public function get_suit_filling_report(Request $req){
        try {
            $sql=[];$todaydate=date('Y-m-d');
            if($req->tenent_id!='' && $req->tenent_id>0){
                $sql['tenent_id']=$req->tenent_id;
            }
            if($req->branch_id!='' && $req->branch_id>0){
                $sql['branch_id']=$req->branch_id;
            }
            if($req->group_id!='' && $req->group_id>0){
                $sql['group_id']=$req->group_id;
            }
            if($req->customer_id!='' && $req->customer_id>0){
                $sql['customer_id']=$req->customer_id;
            }
            if($req->ticket_no!='' && $req->ticket_no>0){
                $sql['ticket_no']=$req->ticket_no;
            }
            if($req->lawyer_id!='' && $req->lawyer_id>0){
                $sql['lawyer_id']=$req->lawyer_id;
            }
            if($req->group_status!='' && $req->group_status>0){
                $sql['group_status']=$req->group_status;
            }

            $suitReport=SuiteFilling::where($sql)->get();
            $data=[];$k=1;
            foreach ($suitReport as $key => $enroll) {
                $data[$key]['sno']=$k++;
                $data[$key]['id']=$enroll->id;
                $data[$key]['tno']=$enroll->ticket_no;
                $data[$key]['case_expense']=$enroll->case_expence;
                $data[$key]['group_status']=$enroll->group_status;
                $data[$key]['cust_name'] = isset($enroll->cus_det) ? ($enroll->cus_det->name) : [];
                $groupdetails=isset($enroll->group_details) ? ($enroll->group_details) : [];
                $lawyerdetails=isset($enroll->lawyer_details) ? ($enroll->lawyer_details) : [];
                
                $casedetails=isset($enroll->case_detailsvalue) ? ($enroll->case_detailsvalue) : [];
                $data[$key]['group_name'] = $groupdetails->group_name;
                $data[$key]['lawyer_name'] = '';
                if(!empty($lawyerdetails)){
                    $data[$key]['lawyer_name'] = $lawyerdetails->lawyer_name;
                }
                $data[$key]['case_name'] = '';
                if(!empty($casedetails)){
                    $data[$key]['case_name'] = $casedetails->case_name;
                }
                $chitdetails=isset($enroll->getChitDetails) ? $enroll->getChitDetails : [];
                $schemedetails=Scheme::where('id',$groupdetails->scheme_id)->get()[0];

                
                $data[$key]['chit_value']=$schemedetails->chit_value;
                $data[$key]['emiamount']=$schemedetails->monthly_due;
                
                $data[$key]['total_ins_amt']=0;
                $data[$key]['cust_paid']=0;
                $data[$key]['discount']=0;
                $data[$key]['tot_cust_paid']=0;
                $data[$key]['actual_pending']=0;
                $data[$key]['tot_pending_dividend']=0;
                $data[$key]['pending_case_expense']=0;
                $data[$key]['pending_divident_case_expense']=0;
                $data[$key]['emi_pending_month']=0;
                $data[$key]['tot_pending_days']=0;
                $data[$key]['tot_pending_months']=0;
                $data[$key]['tot_pending_days_extramonth']=0;
                $data[$key]['tot_pending_interest_gst_extramonth']=0;
                $data[$key]['tot_pending_interest_gst_dividend_extramonth']=0;

                $data[$key]['tot_pending_interest_gst']=0;
                $data[$key]['tot_pending_interest_gst_dividend']=0;
                $data[$key]['lawyer_fees']=0;
                $data[$key]['lawyer_fees_withdividend']=0;
                $data[$key]['grand_total']=0;
                $data[$key]['grand_total_divident']=0;
                $data[$key]['grand_total_extramonth']=0;
                $data[$key]['grand_total_divident_extramonth']=0;
                $data[$key]['pending_start_date']='';
                $data[$key]['current_date']='';
                $data[$key]['chitend_date']='';
                $data[$key]['double_auction']=0;
                $data[$key]['Months_Pending_Till_Last_EMI']=0;
                $data[$key]['total_no_of_month_pending']=0;
                $data[$key]['present_pending']=0;
                $data[$key]['present_pending_with_case_expense']=0;
                $data[$key]['tot_pending_dividend_with_interest']=0;
                $data[$key]['grand_present_total']=0;
                if($chitdetails->chit_type !=3){
                    $receipt=Receipt::selectRaw('sum(received_amount) as total_paid, sum(bonus) as bonus_amount,sum(penalty) as penalty_amount,sum(discount) as discount_amount')->where('enrollment_id',$enroll->enroll_id)->where('status',1)->get();
                    $paid_rec_amount = isset($receipt[0]->total_paid) ? ($receipt[0]->total_paid) : 0;
                    $bonus_amount = isset($receipt[0]->bonus_amount) ? ($receipt[0]->bonus_amount) : 0;
                    $penalty_amount = isset($receipt[0]->penalty_amount) ? ($receipt[0]->penalty_amount) : 0;
                    $discount_amount = isset($receipt[0]->discount_amount) ? ($receipt[0]->discount_amount) : 0;
                    $paid_amount = $paid_rec_amount + $bonus_amount;
                    $data[$key]['tot_cust_paid']=floatval($paid_amount)+floatval($discount_amount);
                    $data[$key]['cust_paid']=$paid_amount;
                    $data[$key]['discount']=$discount_amount;
                    if($enroll->group_status==1){
                        $data[$key]['total_ins_amt']=BiddingDetail::where('group_id','=',$enroll->group_id)->sum('current_installment_amount');

                        $paid_installment_no=1;
                        if(floatval($paid_amount)>0){
                            $paid_installment_no=round(floatval($data[$key]['total_ins_amt'])/floatval($paid_amount));
                        }
                        
                        $data[$key]['actual_pending']=floatval($data[$key]['total_ins_amt'])-floatval($data[$key]['tot_cust_paid']);
                        $data[$key]['tot_pending_dividend']=(floatval($data[$key]['chit_value'])-floatval($data[$key]['total_ins_amt']))+floatval($data[$key]['actual_pending']);
                        $data[$key]['pending_case_expense']=floatval($data[$key]['actual_pending'])+floatval($data[$key]['case_expense']);
                        $data[$key]['pending_divident_case_expense']=floatval($data[$key]['tot_pending_dividend'])+floatval($data[$key]['case_expense']);
                        $data[$key]['emi_pending_month']=round(floatval($data[$key]['actual_pending'])/floatval($data[$key]['emiamount']));

                        $pending_installemnt_startdate=BiddingDetail::where('group_id','=',$enroll->group_id)->where('installment_no','=',$paid_installment_no)->pluck('auction_date')[0];

                        $data[$key]['pending_start_date']=date('d-m-Y',strtotime($pending_installemnt_startdate));
                        $data[$key]['current_date']=date('d-m-Y',strtotime($todaydate));

                        $data[$key]['chitend_date']=date('d-m-Y',strtotime($this->calculatelastmonth_bytenure($groupdetails->starting_date,$schemedetails->no_of_months)));
 
                        $data[$key]['tot_pending_days']=$this->get_datediff($pending_installemnt_startdate,$todaydate);
                        $data[$key]['tot_pending_days_extramonth']=floatval($data[$key]['tot_pending_days'])+31;

                        $data[$key]['tot_pending_interest_gst']=round((((floatval($data[$key]['pending_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days'])))*(18/100))+(floatval($data[$key]['pending_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days'])))+floatval($data[$key]['pending_case_expense']));

                        $data[$key]['tot_pending_interest_gst_dividend']=round((((floatval($data[$key]['pending_divident_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days'])))*(18/100))+(floatval($data[$key]['pending_divident_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days'])))+floatval($data[$key]['pending_divident_case_expense']));

                        $data[$key]['tot_pending_interest_gst_extramonth']=round((((floatval($data[$key]['pending_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days_extramonth'])))*(18/100))+(floatval($data[$key]['pending_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days_extramonth'])))+floatval($data[$key]['pending_case_expense']));

                        $data[$key]['tot_pending_interest_gst_dividend_extramonth']=round((((floatval($data[$key]['pending_divident_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days_extramonth'])))*(18/100))+(floatval($data[$key]['pending_divident_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days_extramonth'])))+floatval($data[$key]['pending_divident_case_expense']));


                        $lawyerpercentage = 0;
                        if(!empty($lawyerdetails)){
                            $lawyerpercentage=$lawyerdetails->lawyer_percentage;
                            
                        }
                
                        $data[$key]['lawyer_fees']=round(floatval($data[$key]['tot_pending_interest_gst'])*(($lawyerpercentage/9)*($lawyerpercentage/100)));

                        $data[$key]['lawyer_fees_withdividend']=round(floatval($data[$key]['tot_pending_interest_gst_dividend'])*(($lawyerpercentage/9)*($lawyerpercentage/100)));

                        $data[$key]['grand_total']=round(floatval($data[$key]['tot_pending_interest_gst'])+floatval($data[$key]['lawyer_fees']));
                        $data[$key]['grand_total_divident']=round(floatval($data[$key]['tot_pending_interest_gst_dividend'])+floatval($data[$key]['lawyer_fees_withdividend']));

                        $data[$key]['grand_total_extramonth']=round(floatval($data[$key]['tot_pending_interest_gst_extramonth'])+floatval($data[$key]['lawyer_fees']));

                        $data[$key]['grand_total_divident_extramonth']=round(floatval($data[$key]['tot_pending_interest_gst_dividend_extramonth'])+floatval($data[$key]['lawyer_fees_withdividend']));
                    }else{
                        $data[$key]['double_auction']=BiddingDetail::where('group_id','=',$enroll->group_id)->count();
                        $data[$key]['total_ins_amt']=floatval($data[$key]['chit_value'])-(floatval($data[$key]['double_auction'])*floatval($data[$key]['emiamount']));

                        $data[$key]['actual_pending']=floatval($data[$key]['total_ins_amt'])-floatval($data[$key]['tot_cust_paid']);

                        $data[$key]['tot_pending_dividend']=floatval($data[$key]['chit_value'])-floatval($data[$key]['tot_cust_paid']);

                        $data[$key]['Months_Pending_Till_Last_EMI']=round(floatval($data[$key]['actual_pending'])/floatval($data[$key]['emiamount']));
                        $paid_installment_no=1;
                        if(floatval($paid_amount)>0){
                            $paid_installment_no=round(floatval($data[$key]['total_ins_amt'])/floatval($paid_amount));
                        }

                        $pending_installemnt_startdate=BiddingDetail::where('group_id','=',$enroll->group_id)->where('installment_no','=',$paid_installment_no)->pluck('auction_date');
                        if(count($pending_installemnt_startdate)>0){
                            $pending_installemnt_startdate=$pending_installemnt_startdate[0];
                        }else{
                            $pending_installemnt_startdate=$groupdetails->starting_date;
                        }
                        $data[$key]['pending_start_date']=date('d-m-Y',strtotime($pending_installemnt_startdate));
                        $data[$key]['current_date']=date('d-m-Y',strtotime($todaydate));
                        $enddate=$this->calculatelastmonth_bytenure($groupdetails->starting_date,$schemedetails->no_of_months);
                        $data[$key]['chitend_date']=date('d-m-Y',strtotime($enddate));

                        $data[$key]['tot_pending_months']=$this->get_monthdiff($todaydate,$enddate);


                        $data[$key]['total_no_of_month_pending']=floatval($data[$key]['Months_Pending_Till_Last_EMI'])-floatval($data[$key]['tot_pending_months']);
                        $data[$key]['present_pending']= floatval($data[$key]['actual_pending'])-(floatval($data[$key]['emiamount'])*floatval($data[$key]['tot_pending_months']));

                        $data[$key]['present_pending_with_case_expense']= floatval($data[$key]['present_pending'])+floatval($data[$key]['case_expense']);
                        $data[$key]['pending_case_expense']= floatval($data[$key]['actual_pending'])+floatval($data[$key]['case_expense']);
                        $data[$key]['pending_divident_case_expense']= floatval($data[$key]['tot_pending_dividend'])+floatval($data[$key]['case_expense']);

                        $data[$key]['tot_pending_days']=$this->get_datediff($pending_installemnt_startdate,$todaydate);

                        $data[$key]['tot_pending_interest_gst']=round((((floatval($data[$key]['present_pending'])*(24/100))/(365/floatval($data[$key]['tot_pending_days'])))*(18/100))+(floatval($data[$key]['present_pending'])*(24/100)/(365*floatval($data[$key]['tot_pending_days'])))+floatval($data[$key]['present_pending']));

                        $data[$key]['tot_pending_interest_gst_dividend']=round((((floatval($data[$key]['present_pending_with_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days'])))*(18/100))+(floatval($data[$key]['present_pending_with_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days'])))+floatval($data[$key]['present_pending_with_case_expense']));

                        $data[$key]['tot_pending_dividend_with_interest']=round((((floatval($data[$key]['pending_divident_case_expense'])*(24/100))/(365/floatval($data[$key]['tot_pending_days'])))*(18/100))+(floatval($data[$key]['pending_divident_case_expense'])*(24/100)/(365*floatval($data[$key]['tot_pending_days'])))+floatval($data[$key]['pending_divident_case_expense']));

                        $lawyerpercentage = 0;
                        if(!empty($lawyerdetails)){
                            $lawyerpercentage=$lawyerdetails->lawyer_percentage;
                            
                        }

                        $data[$key]['lawyer_fees']=round(floatval($data[$key]['tot_pending_interest_gst'])*(($lawyerpercentage/9)*($lawyerpercentage/100)));

                        $data[$key]['lawyer_fees_withdividend']=round(floatval($data[$key]['tot_pending_dividend_with_interest'])*(($lawyerpercentage/9)*($lawyerpercentage/100)));

                        $data[$key]['grand_present_total']=round(floatval($data[$key]['tot_pending_interest_gst'])+floatval($data[$key]['lawyer_fees']));

                        $data[$key]['grand_total']=round(floatval($data[$key]['tot_pending_interest_gst_dividend'])+floatval($data[$key]['lawyer_fees']));

                        $data[$key]['grand_total_divident']=round(floatval($data[$key]['tot_pending_dividend_with_interest'])+floatval($data[$key]['lawyer_fees_withdividend']));
                    }
                }
            }
            $response['status'] = 'Success';
            // $response['msg'] = \Lang::get('api.success_commitment_chit_added');
            $response['data'] = $data;
            return response()->json($response,200);
        }catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    function get_datediff($pendingdate,$todaydate){
        $datetime1 = new DateTime(date('Y-m-d',strtotime($pendingdate)));
        $datetime2 = new DateTime(date('Y-m-d',strtotime($todaydate)));
        $interval = $datetime1->diff($datetime2)->days;
        return $interval;
    }

    function calculatelastmonth_bytenure($startdate, $tenure){
        $startdate=date('Y-m-d',strtotime($startdate));
        return date('Y-m-d', strtotime($startdate . ' +'.floatval($tenure).' months '));
    }

    function get_monthdiff($from, $todate)
    {
        $no_months = 0;
        $first_date = $from;
        while ($from <= $todate) {
            $from = $this->nextmonthfind($from, $first_date);
            if ($from <= $todate) {
                $no_months++;
            }
        }
        return $no_months;
    }
    function nextmonthfind($lpaidamo, $first_date)
    {
        $year = floatval(date('Y', strtotime($lpaidamo)));
        $month = floatval(date('m', strtotime($lpaidamo)));
        $day = floatval(date('d', strtotime($first_date)));
        $month = $month + 1;
        if ($month == 13) {
            $year = $year + 1;
            $month = 1;
        }
        while (checkdate($month, $day, $year) == false) {
            $day = $day - 1;
        }
        $nextmonth = $year . '-' . $month . '-' . $day;
        return  Date('Y-m-d', strtotime($nextmonth));
    }

    public function edit_suit_filling(Request $req){
        try{
            $details=SuiteFilling::find($req->id);
            $details->groupname=isset($details->group_details) ? $details->group_details->group_name : '';
            $details->customername=isset($details->cus_det) ? $details->cus_det->name : '';
            if(!empty($details)){
                $response['status'] = 'Success';
                // $response['msg'] = \Lang::get('api.success_suit_file_added');
                $response['data'] = $details;
                return response()->json($response,200);
            }else{
                $response['status'] = 'Failed';
                // $response['msg'] = \Lang::get('api.suit_file_added_failed');
                // $response['data'] = $data;
                return response()->json($response,400);
            }
        }catch(\Exception $e){
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    function add_suit_filling(Request $req){
        try{
            $suite =new SuiteFilling();
            $suite->tenent_id=isset($req->tenant_id) ? ($req->tenant_id) : 0;
            $suite->branch_id=isset($req->branch_id) ? ($req->branch_id) : 0;
            $suite->group_id=isset($req->group_id) ? ($req->group_id) : 0;
            $suite->customer_id=isset($req->customer_id) ? ($req->customer_id) : 0;
            $suite->ticket_no=isset($req->ticket_no) ? ($req->ticket_no) : 0;
            $suite->enroll_id=isset($req->enroll_id) ? ($req->enroll_id) : 0;
            $suite->lawyer_id=isset($req->lawyer_id) ? ($req->lawyer_id) : 0;
            $suite->lawyer_fee=isset($req->lawyer_fee) ? ($req->lawyer_fee) : 0;
            $suite->case_stage=isset($req->case_stage) ? ($req->case_stage) : 0;
            $suite->chit_amount=isset($req->chit_amount) ? ($req->chit_amount) : 0;
            $suite->group_status=isset($req->group_status) ? ($req->group_status) : 0;
            $suite->case_expence=isset($req->case_expence) ? ($req->case_expence) : 0;
            $suite->case_status=isset($req->case_status) ? ($req->case_status) : 0;
            $suite->remarks=isset($req->remarks) ? ($req->remarks) : '';
            $suite->created_by=isset($req->created_by) ? ($req->created_by) : 0;
            
            if($suite->save()){
                $timeline=new suiteFillingTimeline();
                $timeline->s_id=$suite->id;
                $timeline->t_lawyer_id=isset($req->lawyer_id) ? ($req->lawyer_id) : 0;
                $timeline->t_lawyer_fee=isset($req->lawyer_fee) ? ($req->lawyer_fee) : 0;
                $timeline->t_case_stage=isset($req->case_stage) ? ($req->case_stage) : 0;
                $timeline->t_case_expense=isset($req->case_expence) ? ($req->case_expence) : 0;
                $timeline->t_remarks=isset($req->remarks) ? ($req->remarks) : '';
                $timeline->created_by=isset($req->created_by) ? ($req->created_by) : 0;
                $timeline->save();
                $response['status'] = 'Success';
                // $response['msg'] = \Lang::get('api.success_suit_file_added');
                $response['data'] = $suite;
                return response()->json($response,200);
            }else{
                $response['status'] = 'Failed';
                // $response['msg'] = \Lang::get('api.suit_file_added_failed');
                // $response['data'] = $data;
                return response()->json($response,400);
            }
        }catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function update_suit_filling(Request $req){
        try{
            $update_info=SuiteFilling::find($req->id);
            $update_info->lawyer_id=isset($req->lawyer_id) ? $req->lawyer_id : 0;
            $update_info->lawyer_fee=isset($req->lawyer_fee) ? $req->lawyer_fee : 0;
            $update_info->case_stage=isset($req->case_stage) ? $req->case_stage : 0;
            $update_info->case_expence=isset($req->case_expense) ? $req->case_expense : 0;
            $update_info->case_status=isset($req->case_status) ? $req->case_status : 0;
            $update_info->remarks=isset($req->remarks) ? $req->remarks : 0;
            if($update_info->save()){
                $timeline=new suiteFillingTimeline();
                $timeline->s_id=$req->id;
                $timeline->t_lawyer_id=isset($req->lawyer_id) ? ($req->lawyer_id) : 0;
                $timeline->t_lawyer_fee=isset($req->lawyer_fee) ? ($req->lawyer_fee) : 0;
                $timeline->t_case_stage=isset($req->case_stage) ? ($req->case_stage) : 0;
                $timeline->t_case_expense=isset($req->case_expense) ? ($req->case_expense) : 0;
                $timeline->t_remarks=isset($req->remarks) ? ($req->remarks) : '';
                $timeline->created_by=isset($req->created_by) ? ($req->created_by) : 0;
                $timeline->save();
                $response['status'] = 'Success';
                // $response['msg'] = \Lang::get('api.success_suit_file_added');
                $response['data'] = $update_info;
                return response()->json($response,200);
            }else{
                $response['status'] = 'Failed';
                // $response['msg'] = \Lang::get('api.suit_file_added_failed');
                // $response['data'] = $data;
                return response()->json($response,400);
            }
        }catch(\Exception $e){
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

}
