<?php
namespace App\Http\Controllers;

use App\Models\CollectionAreaMapping;
use App\Models\CollectionArea;
use Illuminate\Http\Request;
use Validator; 

class CollectionAreaMappingController extends Controller
{
    public function store_collection_area_mapping(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'employee_id'=>'required',
            'collection_area_id'=>'required',
        ]); 
        if($validator->fails()){
            $response['status']="Error";
            $response['msg']=$validator->error()->first();
            return response()->json($response,401);
        }
        else {
            try { 
                $collectionareamapping=new CollectionAreaMapping(); 
                $collectionareamapping->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
                $collectionareamapping->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                $collectionareamapping->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0; 
                $collectionareamapping->collection_area_id = isset($request->collection_area_id) ? serialize($request->collection_area_id) : 0;
                $collectionareamapping->remarks = isset($request->remarks) ? ($request->remarks) : 0;  
                $collectionareamapping->created_by = isset($request->created_by) ? ($request->created_by) : 0;
                $collectionareamapping->status = isset($request->status) ? ($request->status) : 1;
                if($collectionareamapping->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_collection_area_mapping_added');
                    $response['data']=$collectionareamapping;
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
				$response['msg']=$e;
                return response()->json($response,401);
            }
        }
    }
       
    public function update_collection_area_mapping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id'=>'required',
            'branch_id'=>'required',
            'employee_id'=>'required',
            'collection_area_id'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $collectionareamapping = CollectionAreaMapping::find($request->id); 
                    $collectionareamapping->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '0';
                    $collectionareamapping->branch_id = isset($request->branch_id) ? ($request->branch_id) : 0;
                    $collectionareamapping->employee_id = isset($request->employee_id) ? ($request->employee_id) : 0;  
                    $collectionareamapping->collection_area_id = isset($request->collection_area_id) ? serialize($request->collection_area_id) : 0;
                    $collectionareamapping->remarks = isset($request->remarks) ? ($request->remarks) : 0;  
                    $collectionareamapping->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($collectionareamapping->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_collection_area_mapping_updated');
                        $response['data'] =$collectionareamapping;
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

    public function get_collection_area_mappings(Request $request) {
        try {            
            $cond = $request->conditions;  
            $collectionareamappings = CollectionAreaMapping::where($cond)->orderBy('id','desc')->get();          
            foreach($collectionareamappings as $collectionareamapping) {
				$collectionareamapping->branch_info = isset($collectionareamapping->branch_det) ? ($collectionareamapping->branch_det) : [];
				$collectionareamapping->employee_info = isset($collectionareamapping->employee_det) ? ($collectionareamapping->employee_det) : [];
				$collectionareamapping->collection_area_id = unserialize($collectionareamapping->collection_area_id);

               /* $collection_area_ids = unserialize($collectionareamapping->collection_area_id);
				$area_list = [];
				foreach($collection_area_ids as $ca_id)
				{
					$area_det = [];
					$area_det['id'] = $ca_id;
					$collection_area = CollectionArea::where('id',$ca_id)->get();
					$area_det['area_name'] = $collection_area[0]->collection_area;
					array_push($area_list,$area_det);
				};
				$collectionareamapping->area_list = $area_list;*/
            };
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $collectionareamappings;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error'); 
            return response()->json($response, 401);
        }
    }

    public function list_collection_area_mappings(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $collectionareamappings = CollectionAreaMapping::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $collectionareamappings = CollectionAreaMapping::orderBy('id','desc')->get();
            }
			
            $i=1;
			//$collection_area_details = [];
            foreach($collectionareamappings as $collectionareamapping) {
				$collectionareamapping->sno = $i;
				$collectionareamapping->branch_info = isset($collectionareamapping->branch_det) ? ($collectionareamapping->branch_det) : [];
				$collectionareamapping->employee_info = isset($collectionareamapping->employee_det) ? ($collectionareamapping->employee_det) : [];
				//$collectionareamapping->collection_area_ids = unserialize($collectionareamapping->collection_area_id);
				/*$area_list = [];
				foreach($collection_area_ids as $ca_id)
				{
					$area_det = [];
					$area_det['id'] = $ca_id;
					$collection_area = CollectionArea::where('id',$ca_id)->get();
					$area_det['area_name'] = $collection_area[0]->collection_area;
					array_push($area_list,$area_det);
				};
				
				$collectionareamapping->area_list = $area_list;*/
				$i++;
            };
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $collectionareamappings;
			
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_collection_area_mapping(Request $request) {
        try {
            if(isset($request->id)) {
                $collectionareamapping = CollectionAreaMapping::find($request->id); 
                $collectionareamapping->deleted_by = $request->deleted_by;
                $collectionareamapping->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($collectionareamapping->save()) {
                    $collectionareamapping->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_collection_area_mapping_deleted');
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
