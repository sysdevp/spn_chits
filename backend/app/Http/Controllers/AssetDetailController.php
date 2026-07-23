<?php
namespace App\Http\Controllers;

use App\Models\AssetDetail;
use App\Models\LoanAssetDetail;
use Illuminate\Http\Request;
use Validator;
use Storage;
use Lang;

class AssetDetailController extends Controller
{
    public function store_asset_detail(Request $request)
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
                $guarantor_document=new AssetDetail(); 
                $guarantor_document->id = isset($request->id) ? ($request->id) : 0;
           		$guarantor_document->asset_name = isset($request->asset_name) ? ($request->asset_name) : '0';
				$guarantor_document->asset_path = isset($request->asset_path) ? ($request->asset_path) : '0';
				$guarantor_document->asset_value = isset($request->asset_value) ? ($request->asset_value) :0;   
			    $guarantor_document->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $guarantor_document->status = isset($request->status) ? ($request->status) : 1;
				//$guarantor_document->status = isset($request->status) ? ($request->status) : 1;
                if($guarantor_document->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_asset_detail_added');
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
	
	public function update_asset_detail(Request $request)
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
                    $guarantor_document=new AssetDetail(); 
					$guarantor_document->id = isset($request->id) ? ($request->id) : 0;
					$guarantor_document->asset_name = isset($request->asset_name) ? ($request->asset_name) : '0';
					$guarantor_document->asset_value = isset($request->asset_value) ? ($request->asset_value) :0; 
					$guarantor_document->asset_path = isset($request->asset_path) ? ($request->asset_path) : '0';					
					$guarantor_document->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($guarantor_document->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_asset_detail_updated');
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
	
	public function get_asset_detail(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $guarantor_document = AssetDetail::where($cond)->orderBy('id','desc')->get();
            if($guarantor_document) {
                $response['status'] = 'Success';
                $response['msg'] = '';
                $response['data'] = $guarantor_document;
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
	
	public function list_asset_detail(Request $request) {
        try { 
            $guarantor_document = AssetDetail::all();
            $i=1;
            foreach($guarantor_document as $GuarantorDocument) {
                $GuarantorDocument->sno = $i;
                $i++;
            }
            if($guarantor_document) {
                $response['status'] = 'Success';
                $response['msg'] = "";
                $response['data'] = $guarantor_document;
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
	
	public function delete_asset_detail(Request $request) {
        try {
            if(isset($request->id)) {
                $guarantor_document = AssetDetail::find($request->id); 
                $guarantor_document->deleted_by = $request->deleted_by;
                $guarantor_document->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '0';
                if($guarantor_document->save()) {
                    $guarantor_document->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_asset_detail_deleted');
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
                $guarantor_document = AssetDetail::find($request->id); 
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
	
	public function assetFileUpload(Request $request)
    {  
        try 
        { 
            if ($request->hasFile('asset_path')) 
            {
                $asset_path = '';    
                if ($request->file('asset_path')->isValid())
                {  
                   $asset_path = $request->file('asset_path')->hashName();
                   $location1 = $request->file('asset_path')->store('uploads/asset_details');
                }
                $response['file_name'] = $asset_path; 
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

    public function return_asset(Request $request)
    { 
        try {
            if(isset($request->id)) {
                $customer_asset = AssetDetail::find($request->id); 
                $customer_asset->status = 2;
                $customer_asset->return_date = date('Y-m-d H:i:s');
                if($customer_asset->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_asset_returned');
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

    public function remove_asset(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_asset = AssetDetail::find($request->id); 
                $customer_asset->status = 3;
                $customer_asset->deleted_by = $request->deleted_by;
                if($customer_asset->save()) {
                    $customer_asset->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_asset_removed');
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

    public function return_loan_asset(Request $request)
    { 
        try {
            if(isset($request->id)) {
                $customer_asset = LoanAssetDetail::find($request->id); 
                $customer_asset->status = 2;
                $customer_asset->return_date = date('Y-m-d H:i:s');
                if($customer_asset->save()) {
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_asset_returned');
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

    public function remove_loan_asset(Request $request)
    {
        try {
            if(isset($request->id)) {
                $customer_asset = LoanAssetDetail::find($request->id); 
                $customer_asset->status = 3;
                $customer_asset->deleted_by = $request->deleted_by;
                if($customer_asset->save()) {
                    $customer_asset->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_asset_removed');
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
