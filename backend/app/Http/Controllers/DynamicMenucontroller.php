<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DynamicMenu;
use Validator;

class DynamicMenucontroller extends Controller
{   
    public function store_dynamic_menu(Request $request)
    {
         $validator=Validator::make($request->all(),[
	        'menu_title'=>'required',
	        'router_link'=>'required',
	    ]); 
	    if($validator->fails()){
	        $response['status']="Error";
	        $response['msg']=$validator->error()->first();
	        return response()->json($response,401);
	    }
        else {
            try { 
                $dynamic_menu=new DynamicMenu(); 
	            $dynamic_menu->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : 0;
	            $dynamic_menu->menu_title = isset($request->menu_title) ? ($request->menu_title) : '';
	            $dynamic_menu->icon_class = isset($request->icon_class) ? ($request->icon_class) : '';
	            $dynamic_menu->router_link = isset($request->router_link) ? ($request->router_link) : '';
	            $dynamic_menu->page_type = isset($request->page_type) ? ($request->page_type) : 1;
	            $dynamic_menu->has_submenu = isset($request->has_submenu) ? ($request->has_submenu) : 0;
	            $dynamic_menu->tab_type = isset($request->tab_type) ? ($request->tab_type) : 1;
	            $dynamic_menu->parent_menu_id = isset($request->parent_menu_id) ? ($request->parent_menu_id) : 0;
	            $dynamic_menu->sort_order = isset($request->sort_order) ? ($request->sort_order) : 0;
	            $dynamic_menu->created_by = isset($request->created_by) ? ($request->created_by) : 0;
	            $dynamic_menu->status = isset($request->status) ? ($request->status) : 1;
                if($dynamic_menu->save()){
                    $response['status']="Success";
                    $response['msg']=\Lang::get('api.success_dynamic_menu_added');
                    $response['data']=$dynamic_menu;
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

    public function update_dynamic_menu(Request $request)
    {
       $validator = Validator::make($request->all(), [
	        'menu_title'=>'required',
	        'router_link'=>'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $dynamic_menu = DynamicMenu::find($request->id); 
                    $dynamic_menu->tenant_id = isset($request->tenant_id) ? ($request->tenant_id) : '';
                    $dynamic_menu->menu_title = isset($request->menu_title) ? ($request->menu_title) : '';
    	            $dynamic_menu->icon_class = isset($request->icon_class) ? ($request->icon_class) : '';
    	            $dynamic_menu->router_link = isset($request->router_link) ? ($request->router_link) : '';
    	            $dynamic_menu->page_type = isset($request->page_type) ? ($request->page_type) : 1;
    	            $dynamic_menu->has_submenu = isset($request->has_submenu) ? ($request->has_submenu) : 0;
    	            $dynamic_menu->tab_type = isset($request->tab_type) ? ($request->tab_type) : 1;
    	            $dynamic_menu->parent_menu_id = isset($request->parent_menu_id) ? ($request->parent_menu_id) : 0;
    	            $dynamic_menu->sort_order = isset($request->sort_order) ? ($request->sort_order) : 0;
                    $dynamic_menu->updated_by = isset($request->updated_by) ? ($request->updated_by) : 0;
                    if($dynamic_menu->save()) {
                        $response['status'] = "Success";
                        $response['msg'] = \Lang::get('api.success_dynamic_menu_updated');
                        $response['data'] =$dynamic_menu;
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

    public function get_dynamic_menu(Request $request) {
        try {
            //id,tenant_id
            $cond = $request->conditions; 
            $dynamic_menus = DynamicMenu::where($cond)->orderBy('id','desc')->get();
            foreach($dynamic_menus as $dynamic_menu) {
                $dynamic_menu->tenant_info = isset($dynamic_menu->tenant_det) ? ($dynamic_menu->tenant_det) : [];
                $dynamic_menu->parent_info = isset($dynamic_menu->parent_det) ? ($dynamic_menu->parent_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $dynamic_menus;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function list_dynamic_menus(Request $request) {
        try { 
             if(isset($request->tenant_id)) {
                $dynamic_menus = DynamicMenu::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $dynamic_menus = DynamicMenu::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($dynamic_menus as $dynamic_menu) {
                $dynamic_menu->sno = $i;
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $dynamic_menus;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_dynamic_menu(Request $request) {
        try {
            if(isset($request->id)) {
                $dynamic_menu = DynamicMenu::find($request->id); 
                $dynamic_menu->deleted_by = $request->deleted_by;
                $dynamic_menu->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : 0;
                if($dynamic_menu->save()) {
                    $dynamic_menu->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_dynamic_menu_deleted');
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
                $dynamic_menu = DynamicMenu::find($request->id); 
                $dynamic_menu->status = ($dynamic_menu->status) ? 0 : 1;
                if($dynamic_menu->save()) {
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

    public function get_parent_menu_list(Request $request) {
        try {
            //id,tenant_id
            if(isset($request->conditions)) {
            	$cond = $request->conditions; 
            	$dynamic_menus = DynamicMenu::where($cond)->where(array('parent_menu_id'=>0))->orderBy('id','desc')->get();
            	if($request->for_edit) {
	            	$dynamic_menus = DynamicMenu::where($cond)->where(array('parent_menu_id'=>0))->whereNotIn('id',[$request->for_edit])->orderBy('id','desc')->get();
	            }
            }
	        else {
	           	 $dynamic_menus = DynamicMenu::where(array('parent_menu_id'=>0))->orderBy('id','desc')->get();
	           	 if($request->for_edit) {
	            	$dynamic_menus = DynamicMenu::where(array('parent_menu_id'=>0))->whereNotIn('id',[$request->for_edit])->orderBy('id','desc')->get();
	            }
	        }
            foreach($dynamic_menus as $dynamic_menu) {
                $dynamic_menu->tenant_info = isset($dynamic_menu->tenant_det) ? ($dynamic_menu->tenant_det) : [];
                $dynamic_menu->parent_info = isset($dynamic_menu->parent_det) ? ($dynamic_menu->parent_det) : [];
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $dynamic_menus;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_menu_for_access_setting(Request $request) {
        try {
            //id,tenant_id
            $dynamic_menus = DynamicMenu::where('parent_menu_id', 0)
                ->where('status', 1)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            $menu_list = [];
            foreach($dynamic_menus as $menu) {
            	$menu_det = [];
            	$menu_det['menu_id'] = $menu->id;
            	$menu_det['menu_title'] = $menu->menu_title;
            	$menu_det['page_type'] = $menu->page_type;
            	$menu_det['has_sub_menu'] = 0; 
            	if($menu->has_submenu) {
            		$sub_menu = [];
            		$menu_det['has_sub_menu'] = 1; 
            		$sub_menu_list = $this->get_sub_menu($menu->id,$sub_menu); 
            		if(count($sub_menu_list)>0) {
            			$menu_det['sub_menu_list'] = $sub_menu_list;
            		}
            	} 
            	array_push($menu_list,$menu_det);
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $menu_list;
           // print_r($menu_list);die();
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function get_sub_menu($menu_id,$sub_menu=[]) {
    	$sub_menu_list = DynamicMenu::where('parent_menu_id', $menu_id)
            ->where('status', 1)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    	if(count($sub_menu_list)>0) {
    		foreach($sub_menu_list as $sm) {
	    		$menu_det = [];
	    		$menu_det['menu_id'] = $sm->id;
	    		$menu_det['menu_title'] = $sm->menu_title;
	    		$menu_det['page_type'] = $sm->page_type;
	    		$menu_det['has_submenu'] = $sm->has_submenu;
	    		array_push($sub_menu, $menu_det);
	    		$inner_sub = [];
	    		$inner_menu_list = $this->get_sub_menu($sm->id, $inner_sub);
	    		if(count($inner_menu_list)>0) {
	    			foreach($inner_menu_list as $iml) {
			    		array_push($sub_menu, $iml);
	    			}
	    		}
	    	}
    		return $sub_menu;
    	}
    	else {
    		return [];
    	}
    }

    public function get_menu_sort_orders(Request $request) {
        try {
        	$count = 0; 
        	if(isset($request->conditions)) {
	            $cond = $request->conditions;
	            $menu_list = DynamicMenu::where($cond)->get();
	            $count = count($menu_list);
	            if($request->for_edit) {
	            	$menu_list = DynamicMenu::where($cond)->whereNotIn('id',[$request->for_edit])->orderBy('id','desc')->get();
	            }
        	}
        	else {
        		$menu_list = DynamicMenu::orderBy('id','desc')->get();
        		$count = count($menu_list);
        		if($request->for_edit) {
	            	$menu_list = DynamicMenu::whereNotIn('id',[$request->for_edit])->orderBy('id','desc')->get();
	            }
        	}
            $assigned_sort_list = [];
            $unassigned_sort_list = [];
            foreach($menu_list as $menu) {
            	if($menu->sort_order) {
                	array_push($assigned_sort_list,$menu->sort_order);
            	}
            }
            for($i=1;$i<=($count+1);$i++) {
                if(!(in_array($i,$assigned_sort_list))) {
                    array_push($unassigned_sort_list,$i);
                }
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $unassigned_sort_list;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }
}
