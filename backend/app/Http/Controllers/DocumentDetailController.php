<?php
namespace App\Http\Controllers;

use App\Models\DocumentDetail;
use App\Models\AssettDetailController; 
use App\Models\LoanDocumentDetail;
use Illuminate\Http\Request;
use Validator;
use Storage;
use Lang;

class DocumentDetailController extends Controller
{
	public function store_document_detail(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'id'=>'required'
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $customer_document=new DocumentDetail(); 
                $customer_document->id = isset($request->id) ? ($request->id) : 0;
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
            catch(\Exception $e)
            {
                $response['status']="Error";
				$response['msg']=\Lang::get('api.global_error');
                return response()->json($response,401);
            }
        }
      
    }
	
	public function update_document_detail(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $customer_document=new DocumentDetail(); 
					$customer_document->id = isset($request->id) ? ($request->id) : 0;
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
	
	public function get_document_detail(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $customer_document = DocumentDetail::where($cond)->orderBy('id','desc')->get();
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
	
	public function list_document_detail(Request $request) {
        try { 
            $customer_document = DocumentDetail::all();
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
	
	public function delete_document_detail(Request $request) {
        try {
            if(isset($request->id)) {
                $customer_document = DocumentDetail::find($request->id); 
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
                $customer_document = DocumentDetail::find($request->id); 
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
	
	public function documentFileUpload(Request $request)
    {  
        try 
        { 
            if ($request->hasFile('document_path')) 
            {
                $document_path = '';    
                if ($request->file('document_path')->isValid())
                {  
                   $document_path = $request->file('document_path')->hashName();
                   $location1 = $request->file('document_path')->store('uploads/document_details');
                }
                $response['file_name'] = $document_path; 
            }
            else 
            {
                $response['file_name'] = '';
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

    public function return_document(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_document = DocumentDetail::find($request->id); 
                $customer_document->status = 2;
                $customer_document->return_date = date('Y-m-d H:i:s');
                if($customer_document->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_returned');
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

    public function remove_document(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_document = DocumentDetail::find($request->id); 
                $customer_document->status = 3;
                $customer_document->deleted_by = $request->deleted_by;
                if($customer_document->save()) {
                    $customer_document->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_removed');
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


    public function return_loan_document(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_document = LoanDocumentDetail::find($request->id); 
                $customer_document->status = 2;
                $customer_document->return_date = date('Y-m-d H:i:s');
                if($customer_document->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_returned');
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

    public function remove_loan_document(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_document = LoanDocumentDetail::find($request->id); 
                $customer_document->status = 3;
                $customer_document->deleted_by = $request->deleted_by;
                if($customer_document->save()) {
                    $customer_document->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_removed');
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
