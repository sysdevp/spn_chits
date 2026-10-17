<?php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Models\DocumentDetail;
use App\Models\AssetDetail;
use App\Models\LoanAssetDetail;
use App\Models\LoanDocument;
use App\Models\LoanDocumentDetail;
use App\Models\SlabDue;
use Lang;
use Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentController extends Controller
{
    public function store_document(Request $request)
    { 
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
				$document = new Document(); 
				$document->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $document->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                //$document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
                $document->customer_id = $customer_id; 
                $document->enrollment_id  = isset($request->enrollment_id) ? $request->enrollment_id : 0; 
                $document->collected_employee_id  = isset($request->collected_employee_id) ? $request->collected_employee_id : 0;  
				if(isset($request->collect_date)) {
					$s_date = ($request->collect_date['year']).'-'.sprintf('%02d',($request->collect_date['month'])).'-'.sprintf('%02d',($request->collect_date['day'])); 
					$document->collect_date = date('Y-m-d',strtotime($s_date)); 
				}
				else {
					// $document->collect_date = '0000-00-00';
				}
                $document->collected_from  = isset($request->collected_from) ? $request->collected_from : '';
                $document->remark  = isset($request->remark) ? $request->remark : ''; 
				$document->guarantor_id  = isset($request->guarantor_id) ? $request->guarantor_id : '0';  
                $document->created_by  = isset($request->created_by) ? $request->created_by : 0; 
                $document->status = isset($request->status) ? ($request->status) : 1;
                // print_r($document);exit;
				if($document->save()) {
					if(isset($request->DocumentDetails)) 
					{  
						$count = count($request->DocumentDetails);
						for($i=0; $i<$count; $i++)
						{
							if($request->DocumentDetails[$i]['document_path']!='') 
                            {
								$document_detail_det = [];
								$document_detail_det = new DocumentDetail();
								$document_detail_det['document_id'] = $document->id;
								$document_detail_det['customer_id'] = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
								$document_detail_det['document_no'] =  isset($request->DocumentDetails[$i]['document_no']) ? $request->DocumentDetails[$i]['document_no'] : '';
								$document_detail_det['document_path'] =  isset($request->DocumentDetails[$i]['document_path']) ? $request->DocumentDetails[$i]['document_path'] : '';'';
								$document_detail_det['document_type'] =  isset($request->DocumentDetails[$i]['document_type']) ? $request->DocumentDetails[$i]['document_type'] : '';
								$document_detail_det['status'] =  1;
								$document_detail_det->save();
							}
						}
					}
					if(isset($request->AssetDetails)) 
					{  
						$count = count($request->AssetDetails);
						for($i=0; $i<$count; $i++)
						{
							if($request->AssetDetails[$i]['asset_path']!='') {
								$asset_detail_det = [];
								$asset_detail_det = new AssetDetail();
								$asset_detail_det['document_id'] = $document->id;
								$asset_detail_det['customer_id'] = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
								$asset_detail_det['asset_name'] = isset($request->AssetDetails[$i]['asset_name']) ? $request->AssetDetails[$i]['asset_name'] : '';
								$asset_detail_det['asset_path'] = isset($request->AssetDetails[$i]['asset_path']) ? $request->AssetDetails[$i]['asset_path'] : '';
								$asset_detail_det['asset_value'] = isset($request->AssetDetails[$i]['asset_value']) ? $request->AssetDetails[$i]['asset_value'] : '0';
								$asset_detail_det['status'] =  1;
								$asset_detail_det->save();
							}
						}
					}
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_document_added');
					$response['data'] = $document;
					return response()->json($response,200);
				}
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }

    public function update_document(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $document = Document::find($request->id);
                    if (!$document) {
                        $response['status'] = "Error";
                        $response['msg'] = \Lang::get('api.record_not_identified');
                        return response()->json($response, 200);
                    }

                    if (isset($request->collected_employee_id)) {
                        $document->collected_employee_id = $request->collected_employee_id;
                    }
                    if (isset($request->remark)) {
                        $document->remark = $request->remark;
                    }
                    if (isset($request->collected_from)) {
                        $document->collected_from = $request->collected_from;
                    }
                    if (isset($request->collect_date) && is_array($request->collect_date)) {
                        $s_date = ($request->collect_date['year']).'-'.sprintf('%02d',($request->collect_date['month'])).'-'.sprintf('%02d',($request->collect_date['day']));
                        $document->collect_date = date('Y-m-d', strtotime($s_date));
                    }
                    $document->updated_by = isset($request->updated_by) ? $request->updated_by : (isset($request->created_by) ? $request->created_by : 0);
                    $document->save();

                    // Remove existing document detail rows (soft delete)
                    if (isset($request->removed_document_detail_ids) && is_array($request->removed_document_detail_ids)) {
                        foreach ($request->removed_document_detail_ids as $detailId) {
                            $detail = DocumentDetail::where('id', (int)$detailId)
                                ->where('document_id', $document->id)
                                ->first();
                            if ($detail) {
                                $detail->delete();
                            }
                        }
                    }

                    if(isset($request->DocumentDetails)) 
                    {  
                        $count = count($request->DocumentDetails);
                        for($i=0; $i<$count; $i++)
                        {
							if($request->DocumentDetails[$i]['document_path']!='') 
                            {
								$document_detail_det = new DocumentDetail();
								$document_detail_det['document_id'] = $document->id;
								$document_detail_det['customer_id'] = isset($request->customer_id[0]['customer_id'])
                                    ? $request->customer_id[0]['customer_id']
                                    : (isset($request->customer_id) ? $request->customer_id : 0);
								$document_detail_det['document_no'] =  isset($request->DocumentDetails[$i]['document_no']) ? $request->DocumentDetails[$i]['document_no'] : '';
								$document_detail_det['document_path'] =  isset($request->DocumentDetails[$i]['document_path']) ? $request->DocumentDetails[$i]['document_path'] : '';
								$document_detail_det['document_type'] =  isset($request->DocumentDetails[$i]['document_type']) ? $request->DocumentDetails[$i]['document_type'] : '';
								$document_detail_det['status'] =  1;
								$document_detail_det->save();
							}
                        }
                    }
                    if(isset($request->AssetDetails)) 
                    {  
                        $count = count($request->AssetDetails);
                        for($i=0; $i<$count; $i++)
                        {
							if($request->AssetDetails[$i]['asset_path']!='') {
								$asset_detail_det = new AssetDetail();
								$asset_detail_det['document_id'] = $document->id;
								$asset_detail_det['customer_id'] = isset($request->customer_id) ? $request->customer_id : 0;
								$asset_detail_det['asset_name'] = isset($request->AssetDetails[$i]['asset_name']) ? $request->AssetDetails[$i]['asset_name'] : '';
								$asset_detail_det['asset_path'] = isset($request->AssetDetails[$i]['asset_path']) ? $request->AssetDetails[$i]['asset_path'] : '';
								$asset_detail_det['asset_value'] = isset($request->AssetDetails[$i]['asset_value']) ? $request->AssetDetails[$i]['asset_value'] : '0';
								$asset_detail_det['status'] =  1;
								$asset_detail_det->save();
							}
                        }
                    }
                    $response['status'] = "Success";
                    $response['msg'] = \Lang::get('api.success_document_updated');
                    $response['data'] = $document;
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
                return response()->json($response, 401);
            }
        }
    }

    public function get_documents(Request $request) {
        try {
            $this->ensureVerificationColumns();
            $cond = (array) $request->conditions;
            $branchFilter = $cond['branch_id'] ?? null;
            if ($branchFilter === '' || $branchFilter === '0' || $branchFilter === 0) {
                unset($cond['branch_id']);
            }
            $docQuery = Document::where($cond);
            if (!empty($request->verification_status) || $request->verification_status === '0') {
                $docQuery->where('verification_status', (int) $request->verification_status);
            }
            $documents = $docQuery->orderBy('id','desc')->get();
            $i =1;
            foreach($documents as $document) {
                $document->sno = $i;
                $document->tenant_info = isset($document->tenant_det) ? ($document->tenant_det) : [];
                $document->branch_info = isset($document->branch_det) ? ($document->branch_det) : []; 
                $document->branch_name = isset($document->branch_det) ? ($document->branch_det->branch_name) : ''; 
                $customer = $document->customer_det;
                if (!$customer && !empty($document->customer_id)) {
                    $customer = \App\Models\Customer::withTrashed()->find($document->customer_id);
                }
                if (!$customer && $document->enrollment_det && !empty($document->enrollment_det->customer_id)) {
                    $customer = \App\Models\Customer::withTrashed()->find($document->enrollment_det->customer_id);
                }
                $document->customer_info = $customer ?: [];
                $document->customer_name = $customer ? trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) : '';
                $document->customer_code = $customer ? ($customer->customer_code ?? '') : '';
                $document->mobile_no = $customer ? ($customer->mobile_no ?? '') : '';
                $document->chit_info = $this->documentChitInfo($document, $customer);
                $document->verification_label = [0 => 'Pending', 1 => 'Approved', 2 => 'Rejected'][(int) ($document->verification_status ?? 0)] ?? 'Pending';
                $document->guarantor_info = isset($document->guarantor_det) ? ($document->guarantor_det) : [];
                $document->nominee_info = isset($document->nominee_det) ? ($document->nominee_det) : [];
                $document->document_info = []; 
				
                if(isset($document->document_det)) {
                    foreach($document->document_det as $det) {
                        $det->download_url = (isset($det->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$det->document_path) : '';
                        $det->return_date = ($det->return_date!=0) ? date('d/m/Y',strtotime($det->return_date)) : '';
                    }
                    $document->document_info = $document->document_det;
                } 
                $document->asset_info =  [];
                if(isset($document->asset_det)) {
                    foreach($document->asset_det as $det) {
                        $det->download_url = (isset($det->asset_path)) ? env('APP_URL').Storage::url('app/uploads/customer_assets/'.$det->asset_path) : '';
                        $det->return_date = ($det->return_date!=0) ? date('d/m/Y',strtotime($det->return_date)) : '';
                    }
                    $document->asset_info = $document->asset_det;
                }
				
                $document->collect_date = ($document->collect_date && $document->collect_date!='0000-00-00') ? date('d/m/Y',strtotime($document->collect_date)) : '';
                $document->return_document_date = ($document->return_document_date && $document->return_document_date!='0000-00-00' && $document->return_document_date!=0) ? date('d/m/Y',strtotime($document->return_document_date)) : '';
                $document->group_name = '';
                $enrollment = $document->enrollment_det;
                if ($enrollment) {
                    if (isset($enrollment->chit_type) && (int)$enrollment->chit_type === 3) {
                        $slab_types = SlabDue::where('id', $enrollment->slab_id)->get();
                        $slab_name = isset($slab_types[0]->slab_name) ? ($slab_types[0]->slab_name) : '';
                        $document->group_name = \App\Models\ChitDetail::formatSlabWithArNo($slab_name, $enrollment->ar_no ?? null) ?: 'Chit';
                    } elseif (isset($enrollment->group_det) && $enrollment->group_det) {
                        $document->group_name = $enrollment->group_det->group_name ?: '';
                    }
                } elseif (!empty($document->collected_from) && stripos((string)$document->collected_from, 'Mobile') !== false) {
                    $document->group_name = 'Mobile App';
                }
				
                $document->collected_emp_info = isset($document->collected_emp_det) ? ($document->collected_emp_det) : [];
                $document->collected_employee_name = isset($document->collected_emp_det) ? trim(($document->collected_emp_det->first_name ?? '').' '.($document->collected_emp_det->last_name ?? '')) : '';
                // Employee HR docs have no customer — show employee as the subject
                $isEmployeeDoc = !empty($document->collected_from) && stripos((string)$document->collected_from, 'Employee Documents') !== false;
                if ($isEmployeeDoc) {
                    $document->name = $document->collected_employee_name ?: '';
                    $document->customer_name = $document->name ?: '-';
                    $document->group_name = $document->group_name ?: 'Employee Documents';
                } else {
                    $document->name = is_string($document->customer_name) ? $document->customer_name : '';
                    if ($document->group_name === '' && !empty($document->chit_info)) {
                        $document->group_name = implode(', ', array_column($document->chit_info, 'label'));
                    }
                }
                $document->source = $document->collected_from ?: 'Web';
				
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $documents;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['error'] = $e->getMessage();
            return response()->json($response, 401);
        }
    }

    private function ensureVerificationColumns()
    {
        if (!Schema::hasTable('documents') || Schema::hasColumn('documents', 'verification_status')) {
            return;
        }
        Schema::table('documents', function ($table) {
            $table->tinyInteger('verification_status')->default(0);
            $table->integer('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('verification_remarks', 255)->nullable();
        });
    }

    /** Chits linked to the document (its own enrollment, else all active chits of the customer). */
    private function documentChitInfo($document, $customer): array
    {
        $query = \App\Models\ChitDetail::query();
        if (!empty($document->enrollment_id)) {
            $query->where('id', $document->enrollment_id);
        } elseif ($customer) {
            $query->where('customer_id', $customer->id)->where('status', 1);
        } else {
            return [];
        }
        $rows = [];
        foreach ($query->limit(10)->get() as $chit) {
            $scheme = $chit->scheme_det;
            $slab = SlabDue::find($chit->slab_id);
            $slabName = $slab ? ($slab->slab_name ?? '') : '';
            $rows[] = [
                'enrollment_id' => $chit->id,
                'label' => \App\Models\ChitDetail::formatSlabWithArNo($slabName, $chit->ar_no ?? null) ?: ('Chit #' . $chit->id),
                'ar_no' => $chit->ar_no ?? '',
                'scheme' => $scheme ? (round((float) $scheme->chit_value) . ' (' . $scheme->no_of_months . ' M)') : '',
                'chit_value' => $scheme ? round((float) $scheme->chit_value) : 0,
                'slab_name' => $slabName,
                'pending_amount' => round((float) ($chit->pending_amount ?? 0), 2),
                'date_introduced' => ($chit->date_introduced && $chit->date_introduced != '0000-00-00') ? date('d/m/Y', strtotime($chit->date_introduced)) : '',
            ];
        }
        return $rows;
    }

    public function verify_document(Request $request)
    {
        try {
            $this->ensureVerificationColumns();
            $status = (int) $request->verification_status;
            if (empty($request->id) || !in_array($status, [1, 2], true)) {
                return response()->json(['status' => 'Error', 'msg' => 'Document and decision are required'], 200);
            }
            if ($status === 2 && trim((string) $request->remarks) === '') {
                return response()->json(['status' => 'Error', 'msg' => 'Enter a reason for rejection'], 200);
            }
            $updated = DB::table('documents')->where('id', $request->id)->update([
                'verification_status' => $status,
                'verified_by' => (int) ($request->verified_by ?: 0),
                'verified_at' => now(),
                'verification_remarks' => (string) ($request->remarks ?: ''),
            ]);
            if (!$updated) {
                return response()->json(['status' => 'Error', 'msg' => 'Document not found'], 200);
            }
            return response()->json(['status' => 'Success', 'msg' => $status === 1 ? 'KYC approved' : 'KYC rejected'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => \Lang::get('api.global_error'), 'error' => $e->getMessage()], 200);
        }
    }

    public function list_documents(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $documents = Document::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $documents = Document::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($documents as $document) {
			    $document->branch_info = ($document->branch_det) ? ($document->branch_det) : [];
			    $document->customer_info = ($document->customer_det) ? ($document->customer_det) : [];
                $document->group_name = [];
                if(isset($document->enrollment_det)) {
                    $document->group_name = isset($document->enrollment_det->group_det) ? ($document->enrollment_det->group_det->group_name) : [];
                }
                $document->collected_emp_info = isset($document->collected_emp_det) ? ($document->collected_emp_det) : [];
			    $document->sno = $i;
			    $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $documents;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_document(Request $request) {
        try {
            if(isset($request->id)) {
                $document = Document::find($request->id); 
                $document->deleted_by = $request->deleted_by;
                $document->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($document->save()) {
                    $document->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_deleted');
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
            // Accept same field names used by customer/employee photo-style uploads
            $file = null;
            if ($request->hasFile('asset_path')) {
                $file = $request->file('asset_path');
            } elseif ($request->hasFile('photo')) {
                $file = $request->file('photo');
            } elseif ($request->hasFile('document')) {
                $file = $request->file('document');
            }

            if ($file && $file->isValid())
            {
                $asset_path = $file->hashName();
                if(($request->upload_type) == 'asset') {
                    $file->store('uploads/customer_assets');
                }
                else {
                    $file->store('uploads/customer_documents');
                }
                $response['file_name'] = $asset_path; 
            }
            else 
            {
                $response['file_name'] = '';
                $response['msg'] = 'No valid file received';
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
	
	public function store_loan_document(Request $request)
    { 
	    $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) {  
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {	
			try {
				$document = new LoanDocument(); 
				$document->tenant_id = isset($request->tenant_id) ? $request->tenant_id : 0;
                $document->branch_id  = isset($request->branch_id) ? $request->branch_id : 0; 
                //$document->customer_id = isset($request->customer_id) ? ($request->customer_id) : 0;
				$customer_id = isset($request->customer_id[0]['customer_id']) ? ($request->customer_id[0]['customer_id']) : 0;
				$document->customer_id = $customer_id;
                $document->enrollment_id  = isset($request->enrollment_id) ? $request->enrollment_id : 0; 
                $document->collected_employee_id  = isset($request->collected_employee_id) ? $request->collected_employee_id : 0;  
				if(isset($request->collect_date)) {
					$s_date = ($request->collect_date['year']).'-'.sprintf('%02d',($request->collect_date['month'])).'-'.sprintf('%02d',($request->collect_date['day'])); 
					$document->collect_date = date('Y-m-d',strtotime($s_date)); 
				}
				else {
					$document->collect_date = '0000-00-00';
				}
                $document->collected_from  = isset($request->collected_from) ? $request->collected_from : '';
                $document->remark  = isset($request->remark) ? $request->remark : ''; 
				$document->guarantor_id  = isset($request->guarantor_id) ? $request->guarantor_id : '';  
                $document->created_by  = isset($request->created_by) ? $request->created_by : 0; 
                $document->status = isset($request->status) ? ($request->status) : 1;
				if($document->save()) {
					if(isset($request->DocumentDetails)) 
					{  
						$count = count($request->DocumentDetails);
						for($i=0; $i<$count; $i++)
						{
							if($request->DocumentDetails[$i]['document_path']!='') 
                            {
								$document_detail_det = [];
								$document_detail_det = new LoanDocumentDetail();
								$document_detail_det['loan_document_id'] = $document->id;
								$customer_id = isset($request->customer_id[0]['customer_id']) ? $request->customer_id[0]['customer_id'] : '';
								$document_detail_det['customer_id'] = $customer_id;
								$document_detail_det['document_no'] =  isset($request->DocumentDetails[$i]['document_no']) ? $request->DocumentDetails[$i]['document_no'] : '';
								$document_detail_det['document_path'] =  isset($request->DocumentDetails[$i]['document_path']) ? $request->DocumentDetails[$i]['document_path'] : '';'';
								$document_detail_det['document_type'] =  isset($request->DocumentDetails[$i]['document_type']) ? $request->DocumentDetails[$i]['document_type'] : '';
								$document_detail_det['status'] =  1;
								$document_detail_det->save();
							}
						}
					}
					if(isset($request->AssetDetails)) 
					{  
						$count = count($request->AssetDetails);
						for($i=0; $i<$count; $i++)
						{
							if($request->AssetDetails[$i]['asset_path']!='') {
								$asset_detail_det = [];
								$asset_detail_det = new LoanAssetDetail();
								$asset_detail_det['loan_document_id'] = $document->id;
								$customer_id = isset($request->customer_id[0]['customer_id']) ? $request->customer_id[0]['customer_id'] : '';
								$asset_detail_det['customer_id'] = $customer_id;
								$asset_detail_det['asset_name'] = isset($request->AssetDetails[$i]['asset_name']) ? $request->AssetDetails[$i]['asset_name'] : '';
								$asset_detail_det['asset_path'] = isset($request->AssetDetails[$i]['asset_path']) ? $request->AssetDetails[$i]['asset_path'] : '';
								$asset_detail_det['asset_value'] = isset($request->AssetDetails[$i]['asset_value']) ? $request->AssetDetails[$i]['asset_value'] : '0';
								$asset_detail_det['status'] =  1;
								$asset_detail_det->save();
							}
						}
					}
					$response['status'] = 'Success';
					$response['msg'] = \Lang::get('api.success_document_added');
					$response['data'] = $document;
					return response()->json($response,200);
				}
			}
			catch(\Exception $e) { 
				$response['status'] = 'Error';
				$response['msg'] = \Lang::get('api.global_error');
				return response()->json($response,200);
			}
		}
    }

    public function update_loan_document(Request $request) {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required',
            'branch_id' => 'required',
        ]);
        if ($validator->fails()) { 
            $response['status'] = "Error";
            $response['msg'] = $validator->errors()->first();
            return response()->json($response, 200);
        }
        else {
            try {
                if(isset($request->id)) {
                    $document = LoanDocument::find($request->id);
                    if(isset($request->DocumentDetails)) 
                    {  
                        $count = count($request->DocumentDetails);
                        for($i=0; $i<$count; $i++)
                        {
							if($request->DocumentDetails[$i]['document_path']!='') 
                            {
								$document_detail_det = [];
								$document_detail_det = new LoanDocumentDetail();
								$document_detail_det['loan_document_id'] = $document->id;
								$document_detail_det['customer_id'] = isset($request->customer_id) ? $request->customer_id : '';
								$document_detail_det['document_no'] =  isset($request->DocumentDetails[$i]['document_no']) ? $request->DocumentDetails[$i]['document_no'] : '';
								$document_detail_det['document_path'] =  isset($request->DocumentDetails[$i]['document_path']) ? $request->DocumentDetails[$i]['document_path'] : '';
								$document_detail_det['document_type'] =  isset($request->DocumentDetails[$i]['document_type']) ? $request->DocumentDetails[$i]['document_type'] : '';
								$document_detail_det['status'] =  1;
								$document_detail_det->save();
							}
                        }
                    }
                    if(isset($request->AssetDetails)) 
                    {  
                        $count = count($request->AssetDetails);
                        for($i=0; $i<$count; $i++)
                        {
							if($request->AssetDetails[$i]['asset_path']!='') {
								$asset_detail_det = [];
								$asset_detail_det = new LoanAssetDetail();
								$asset_detail_det['loan_document_id'] = $document->id;
								$asset_detail_det['customer_id'] = isset($request->customer_id) ? $request->customer_id : '';
								$asset_detail_det['asset_name'] = isset($request->AssetDetails[$i]['asset_name']) ? $request->AssetDetails[$i]['asset_name'] : '';
								$asset_detail_det['asset_path'] = isset($request->AssetDetails[$i]['asset_path']) ? $request->AssetDetails[$i]['asset_path'] : '';
								$asset_detail_det['asset_value'] = isset($request->AssetDetails[$i]['asset_value']) ? $request->AssetDetails[$i]['asset_value'] : '0';
								$asset_detail_det['status'] =  1;
								$asset_detail_det->save();
							}
                        }
                    }
                    $response['status'] = "Success";
                    $response['msg'] = \Lang::get('api.success_document_updated');
                    $response['data'] = $document;
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
                return response()->json($response, 401);
            }
        }
    }
	
	 public function get_loan_documents(Request $request) {
        try {
            $cond = $request->conditions; 
            $documents = LoanDocument::where($cond)->orderBy('id','desc')->get();
            $i =1;
            foreach($documents as $document) {
                
                $document->sno = $i;
                $document->tenant_info = isset($document->tenant_det) ? ($document->tenant_det) : [];
                $document->branch_info = isset($document->branch_det) ? ($document->branch_det) : []; 
                $document->branch_name = isset($document->branch_det) ? ($document->branch_det->branch_name) : []; 
                $document->customer_info = isset($document->customer_det) ? ($document->customer_det) : [];
                $document->customer_name = isset($document->customer_det) ? ($document->customer_det->name) : [];
                $document->guarantor_info = isset($document->guarantor_det) ? ($document->guarantor_det) : [];
                $document->document_info = [];

                if(isset($document->loan_document_det)) {
                   
                    foreach($document->loan_document_det as $det) {
                        $det->download_url = (isset($det->document_path)) ? env('APP_URL').Storage::url('app/uploads/customer_documents/'.$det->document_path) : '';
                        $det->return_date = ($det->return_date!=0) ? date('d/m/Y',strtotime($det->return_date)) : '';
                    }
                    $document->document_info = $document->loan_document_det;
                } 
                $document->asset_info =  [];
                if(isset($document->loan_asset_det)) {
                    foreach($document->loan_asset_det as $det) {
                        $det->download_url = (isset($det->asset_path)) ? env('APP_URL').Storage::url('app/uploads/customer_assets/'.$det->asset_path) : '';
                        $det->return_date = ($det->return_date!=0) ? date('d/m/Y',strtotime($det->return_date)) : '';
                    }
                    $document->asset_info = $document->loan_asset_det;
                }

                $document->collect_date = ($document->collect_date!=0) ? date('d/m/Y',strtotime($document->collect_date)) : '';
                $document->return_document_date = ($document->return_document_date!=0) ? date('d/m/Y',strtotime($document->return_document_date)) : '';
                
                
                
                $document->collected_emp_info = isset($document->collected_emp_det) ? ($document->collected_emp_det) : [];
                $document->collected_employee_name = isset($document->collected_emp_det) ? ($document->collected_emp_det->first_name." ".$document->collected_emp_det->last_name) : [];
                $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = '';
            $response['data'] = $documents;
            return response()->json($response,200);
        }
        catch(\Exception $e) { 
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            $response['msg'] = $e;
            return response()->json($response, 401);
        }
    }

    public function list_loan_documents(Request $request) {
        try { 
            if(isset($request->tenant_id)) {
                $documents = LoanDocument::where('tenant_id',$request->tenant_id)->orderBy('id','desc')->get();
            }
            else {
                $documents = LoanDocument::orderBy('id','desc')->get();
            }
            $i=1;
            foreach($documents as $document) {
			    $document->branch_info = ($document->branch_det) ? ($document->branch_det) : [];
			    $document->customer_info = ($document->customer_det) ? ($document->customer_det) : [];
                $document->group_name = [];
                if(isset($document->enrollment_det)) {
                    $document->group_name = isset($document->enrollment_det->group_det) ? ($document->enrollment_det->group_det->group_name) : [];
                }
                $document->collected_emp_info = isset($document->collected_emp_det) ? ($document->collected_emp_det) : [];
			    $document->sno = $i;
			    $i++;
            }
            $response['status'] = 'Success';
            $response['msg'] = "";
            $response['data'] = $documents;
            return response()->json($response,200);
        }
        catch(\Exception $e) {
            $response['status'] = 'Error';
            $response['msg'] = \Lang::get('api.global_error');
            return response()->json($response, 401);
        }
    }

    public function delete_loan_document(Request $request) {
        try {
            if(isset($request->id)) {
                $document = LoanDocument::find($request->id); 
                $document->deleted_by = $request->deleted_by;
                $document->deletion_remark =  isset($request->deletion_remark) ? ($request->deletion_remark) : '';
                if($document->save()) {
                    $document->delete();
                    $response['status'] = 'Success';
                    $response['msg'] = \Lang::get('api.success_document_deleted');
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
	
	
    public function mobile_document_file_upload(Request $request)
    {
        if (!empty($request->db)) {
            \DB::purge('mysql');
            \Config::set('database.connections.mysql.database', $request->db);
            \DB::reconnect('mysql');
        }
        if (!$request->has('upload_type')) {
            $request->merge(['upload_type' => 'document']);
        }
        return $this->assetFileUpload($request);
    }

}
