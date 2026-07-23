<?php
namespace App\Http\Controllers;

use App\Models\CustomerDocumentDetail;
use Illuminate\Http\Request;
use Validator;
use Storage;
use Lang;

class CustomerDocumentDetailController extends Controller
{
	public function store_customer_document(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'customer_id'=>'required',
            'document_name'=>'required'
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $customer_document=new CustomerDocumentDetail(); 
                $customer_document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $customer_document->document_name = isset($request->document_name) ? ($request->document_name) : '';
                $customer_document->document_no = isset($request->document_no) ? ($request->document_no) : '';  
    			$customer_document->document_path = isset($request->document_path) ? ($request->document_path) : '';
                $customer_document->document_type = isset($request->document_type) ? ($request->document_type) :'';  
                $customer_document->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $customer_document->status = isset($request->status) ? ($request->status) : 1;
                if($customer_document->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_customer_document_added');
                    $response['data']=$customer_document;
                    return response()->json($response,200);
                }
                else {
                    $response['status']="Error";
                    $response['msg']=\Lang::get('api.global_error');
                    return response()->json($response,401);
                }
            } 
            catch(\Exception $e){
                $response['status']="Error";
                $response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
    }
	
	public function update_customer_document(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'customer_id'=>'required',
           'document_name'=>'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $customer_document=new CustomerDocumentDetail(); 
					$customer_document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$customer_document->document_name = isset($request->document_name) ? ($request->document_name) : '';
					$customer_document->document_no = isset($request->document_no) ? ($request->document_no) : '';  
					$customer_document->document_path = isset($request->document_path) ? ($request->document_path) : '';
					$customer_document->document_type = isset($request->document_type) ? ($request->document_type) :'';  
					$customer_document->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($customer_document->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_customer_document_updated');
                        $response['data'] =$customer_document;
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
	
	public function get_customer_document(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $customer_document = CustomerDocumentDetail::where($cond)->orderBy('id','desc')->get();
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $customer_document;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_customer_document(Request $request) {
        try { 
            $customer_document = CustomerDocumentDetail::all();
            $i=1;
            foreach($customer_document as $CustomerDocument) {
                $CustomerDocument->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $customer_document;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_customer_document(Request $request) {
        try {
            if(isset($request->id)) {
                $customer_document = CustomerDocumentDetail::find($request->id); 
                $customer_document->deleted_by = $request->deleted_by;
                $customer_document->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($customer_document->save()) {
                    $customer_document->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_customer_document_deleted');
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
                $customer_document = CustomerDocumentDetail::find($request->id); 
                $customer_document->status = ($customer_document->status) ? 0 : 1;
                if($customer_document->save()) {
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
}
