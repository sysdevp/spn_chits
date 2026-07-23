<?php
namespace App\Http\Controllers;

use App\Models\CollectionArea;
use App\Models\ChitDetail;
use Illuminate\Http\Request;
use Lang;
use Validator;

class CollectionAreaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store_collection_area(Request $request)
    { 
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'collection_area' => 'required'
			
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
            
        }
        else {	
			try {
				$collection_area = new CollectionArea(); 
				$collection_area->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
				$collection_area->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
				$collection_area->collection_area = isset($request->collection_area) ? $request->collection_area : '0';
				$collection_area->area_pincode = isset($request->area_pincode) ? $request->area_pincode : 0; 
                $collection_area->created_by = isset($request->created_by) ? $request->created_by : 0;
                $collection_area->status = isset($request->status) ? ($request->status) : 1;
				if($collection_area->save()) {
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_collection_area_added');
					$response['data'] = $collection_area;
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
				return response()->json($response,200);
			}
		}
    }

	public function update_collection_area(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
            'collection_area' => 'required'
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $collection_area = CollectionArea::find($request->id);
					$collection_area->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
					$collection_area->branch_id = isset($request->branch_id) ? $request->branch_id : 0;
					$collection_area->collection_area = isset($request->collection_area) ? $request->collection_area : '0';
					$collection_area->area_pincode = isset($request->area_pincode) ? $request->area_pincode : 0;
                    $collection_area->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($collection_area->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_collection_area_updated');
                        $response['data'] = $collection_area;
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

    public function get_collection_areas(Request $request) {
        try {
            $cond = $request->conditions; 
            $collection_areas = CollectionArea::where($cond)->orderBy('id','desc')->get();
            foreach($collection_areas as $collection_area) {
                $collection_area->tenant_info = isset($collection_area->tenant_det) ? ($collection_area->tenant_det) : [];
                $collection_area->branch_info = ($collection_area->branch_det) ? ($collection_area->branch_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $collection_areas;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

	public function list_collection_areas(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $collection_areas = CollectionArea::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $collection_areas = CollectionArea::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($collection_areas as $collection_area) {
                $collection_area->sno = $i;
				$collection_area->branch_info = isset($collection_area->branch_det) ? ($collection_area->branch_det) : [];
                $collection_area->is_delete=0;
                $chitdetails=ChitDetail::where('collection_area_id','=',$collection_area->id)->get();
                if(count($chitdetails)>0){
                    $collection_area->is_delete=1;
                }
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $collection_areas;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
	
	public function delete_collection_area(Request $request) {
        try {
            if(isset($request->id)) {
                $collection_area = CollectionArea::find($request->id); 
                $collection_area->deleted_by = $request->deleted_by;
                $collection_area->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($collection_area->save()) {
                    $collection_area->delete();
                     $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_collection_area_deleted');
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
                $collection_area = CollectionArea::find($request->id); 
                $collection_area->status = ($collection_area->status) ? 0 : 1;
                if($collection_area->save()) {
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
}
