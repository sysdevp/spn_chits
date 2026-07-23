<?php
namespace App\Http\Controllers;

use App\Models\GuarantorDocumentDetail;
use Illuminate\Http\Request;
use Validator;
use Storage;
use Lang;

class GuarantorDocumentDetailController extends Controller
{
    public function store_guarantor_document(Request $request)
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
                $guarantor_document=new GuarantorDocumentDetail(); 
                $guarantor_document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
                $guarantor_document->document_name = isset($request->document_name) ? ($request->document_name) : '';
                $guarantor_document->document_no = isset($request->document_no) ? ($request->document_no) : '';  
				$guarantor_document->document_path = isset($request->document_path) ? ($request->document_path) : '';
                $guarantor_document->document_type = isset($request->document_type) ? ($request->document_type) :'';  
                $guarantor_document->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $guarantor_document->status = isset($request->status) ? ($request->status) : 1;
                if($guarantor_document->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_guarantor_document_added');
                    $response['data']=$guarantor_document;
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
	
	public function update_guarantor_document(Request $request)
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
                    $guarantor_document=new GuarantorDocumentDetailDocumentDetail(); 
					$guarantor_document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
					$guarantor_document->document_name = isset($request->document_name) ? ($request->document_name) : '';
					$guarantor_document->document_no = isset($request->document_no) ? ($request->document_no) : '';  
					$guarantor_document->document_path = isset($request->document_path) ? ($request->document_path) : '';
					$guarantor_document->document_type = isset($request->document_type) ? ($request->document_type) :'';  
					$guarantor_document->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($guarantor_document->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_guarantor_document_updated');
                        $response['data'] =$guarantor_document;
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
	
	public function get_guarantor_document(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $guarantor_document = GuarantorDocumentDetailDocumentDetail::where($cond)->orderBy('id','desc')->get();
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $guarantor_document;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function list_guarantor_document(Request $request) {
        try { 
            $guarantor_document = GuarantorDocumentDetail::all();
            $i=1;
            foreach($guarantor_document as $GuarantorDocument) {
                $GuarantorDocument->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $guarantor_document;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_guarantor_document(Request $request) {
        try {
            if(isset($request->id)) {
                $guarantor_document = GuarantorDocumentDetail::find($request->id); 
                $guarantor_document->deleted_by = $request->deleted_by;
                $guarantor_document->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($guarantor_document->save()) {
                    $guarantor_document->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_guarantor_document_deleted');
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
                $guarantor_document = GuarantorDocumentDetail::find($request->id); 
                $guarantor_document->status = ($guarantor_document->status) ? 0 : 1;
                if($guarantor_document->save()) {
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
