<?php

namespace App\Http\Controllers;

use App\Models\ChitDetail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentPreparationController extends Controller
{
    private function ensureTable()
    {
        if (Schema::hasTable('document_preparations')) {
            return;
        }
        Schema::create('document_preparations', function ($table) {
            $table->bigIncrements('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('customer_id')->default(0);
            $table->integer('enrollment_id')->default(0);
            $table->string('payment_kind', 20)->default('prized');
            $table->string('stage', 30)->default('ho_requested');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();
            $table->string('cheque_no')->nullable();
            $table->string('cheque_file')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('aadhar_file')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('pan_file')->nullable();
            $table->string('home_photo')->nullable();
            $table->string('nominee_name')->nullable();
            $table->string('nominee_relation')->nullable();
            $table->string('nominee_mobile')->nullable();
            $table->text('nominee_address')->nullable();
            $table->text('ho_remarks')->nullable();
            $table->text('md_remarks')->nullable();
            $table->integer('ho_by')->nullable();
            $table->integer('branch_by')->nullable();
            $table->integer('md_by')->nullable();
            $table->timestamps();
        });
    }

    public function store_request(Request $request)
    {
        try {
            $this->ensureTable();
            if (empty($request->tenant_id) || empty($request->customer_id) || empty($request->enrollment_id)) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer and enrollment are required'], 200);
            }
            $kind = $request->payment_kind === 'closed' ? 'closed' : 'prized';
            $row = DB::table('document_preparations')->insertGetId([
                'tenant_id' => $request->tenant_id,
                'branch_id' => $request->branch_id ?: 0,
                'customer_id' => $request->customer_id,
                'enrollment_id' => $request->enrollment_id,
                'payment_kind' => $kind,
                'stage' => 'ho_requested',
                'ho_remarks' => $request->ho_remarks ?: '',
                'ho_by' => $request->ho_by ?: 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'HO documentation request created', 'id' => $row], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function list_requests(Request $request)
    {
        try {
            $this->ensureTable();
            $query = DB::table('document_preparations')->orderBy('id', 'desc');
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            if (!empty($request->branch_id) && (int) $request->branch_id !== 0) {
                $query->where('branch_id', $request->branch_id);
            }
            if (!empty($request->stage)) {
                $query->where('stage', $request->stage);
            }
            if (!empty($request->payment_kind)) {
                $query->where('payment_kind', $request->payment_kind);
            }
            $rows = $query->limit(500)->get();
            $data = [];
            $i = 1;
            foreach ($rows as $row) {
                $customer = Customer::find($row->customer_id);
                $enrol = ChitDetail::find($row->enrollment_id);
                $item = (array) $row;
                $item['sno'] = $i++;
                $item['customer_name'] = $customer ? trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) : '';
                $item['mobile'] = $customer->mobile_no ?? '';
                $item['ar_no'] = $enrol->ar_no ?? ($enrol->ticket_no ?? '');
                $item['has_aadhar'] = $customer && !empty($customer->adhar_no);
                $item['has_pan'] = $customer && !empty($customer->pan_no);
                $item['cheque_url'] = $this->fileUrl($row->cheque_file ?? '');
                $item['aadhar_url'] = $this->fileUrl($row->aadhar_file ?? '');
                $item['pan_url'] = $this->fileUrl($row->pan_file ?? '');
                $item['home_url'] = $this->fileUrl($row->home_photo ?? '');
                $data[] = $item;
            }
            return response()->json(['status' => 'Success', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function branch_fill(Request $request)
    {
        try {
            $this->ensureTable();
            $row = DB::table('document_preparations')->where('id', $request->id)->first();
            if (!$row || $row->stage !== 'ho_requested') {
                return response()->json(['status' => 'Error', 'msg' => 'Request is not waiting for the branch to confirm the customer'], 200);
            }
            DB::table('document_preparations')->where('id', $row->id)->update([
                'branch_by' => $request->branch_by ?: 0,
                'stage' => 'branch_filled',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Customer confirmed. Waiting for MD approval'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function submit_payout_documents(Request $request)
    {
        try {
            $this->ensureTable();
            $row = DB::table('document_preparations')->where('id', $request->id)->first();
            if (!$row || $row->stage !== 'md_approved') {
                return response()->json(['status' => 'Error', 'msg' => 'Bank and nominee details can be filled only after MD approval'], 200);
            }
            $customer = Customer::find($row->customer_id);
            $needsAadhar = !$customer || empty($customer->adhar_no);
            $needsPan = !$customer || empty($customer->pan_no);
            if ($needsAadhar && (empty($request->aadhar_no) || empty($request->aadhar_file))) {
                return response()->json(['status' => 'Error', 'msg' => 'Aadhaar number and upload are required'], 200);
            }
            if ($needsPan && (empty($request->pan_no) || empty($request->pan_file))) {
                return response()->json(['status' => 'Error', 'msg' => 'PAN number and upload are required'], 200);
            }
            if (empty($request->bank_name) || empty($request->account_number) || empty($request->ifsc) || empty($request->cheque_file)) {
                return response()->json(['status' => 'Error', 'msg' => 'Bank details and cheque upload are required'], 200);
            }
            if (empty($request->home_photo) || empty($request->nominee_name)) {
                return response()->json(['status' => 'Error', 'msg' => 'Home photo and nominee details are required'], 200);
            }
            DB::table('document_preparations')->where('id', $row->id)->update([
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc' => $request->ifsc,
                'cheque_no' => $request->cheque_no ?: '',
                'cheque_file' => $request->cheque_file,
                'aadhar_no' => $request->aadhar_no ?: ($customer->adhar_no ?? ''),
                'aadhar_file' => $request->aadhar_file ?: '',
                'pan_no' => $request->pan_no ?: ($customer->pan_no ?? ''),
                'pan_file' => $request->pan_file ?: '',
                'home_photo' => $request->home_photo,
                'nominee_name' => $request->nominee_name,
                'nominee_relation' => $request->nominee_relation ?: '',
                'nominee_mobile' => $request->nominee_mobile ?: '',
                'nominee_address' => $request->nominee_address ?: '',
                'branch_by' => $request->branch_by ?: ($row->branch_by ?: 0),
                'stage' => 'docs_submitted',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            if ($customer) {
                if ($needsAadhar && !empty($request->aadhar_no)) {
                    $customer->adhar_no = $request->aadhar_no;
                }
                if ($needsPan && !empty($request->pan_no)) {
                    $customer->pan_no = $request->pan_no;
                }
                $customer->save();
            }
            return response()->json(['status' => 'Success', 'msg' => 'Bank, cheque, KYC, home photo and nominee saved'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function md_decide(Request $request)
    {
        try {
            $this->ensureTable();
            $row = DB::table('document_preparations')->where('id', $request->id)->first();
            if (!$row || $row->stage !== 'branch_filled') {
                return response()->json(['status' => 'Error', 'msg' => 'Waiting for branch documents before MD approval'], 200);
            }
            $stage = $request->decision === 'reject' ? 'md_rejected' : 'md_approved';
            DB::table('document_preparations')->where('id', $row->id)->update([
                'stage' => $stage,
                'md_remarks' => $request->md_remarks ?: '',
                'md_by' => $request->md_by ?: 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            return response()->json([
                'status' => 'Success',
                'msg' => $stage === 'md_approved' ? 'MD approved the documents' : 'MD rejected the documents',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    public function customer_history(Request $request)
    {
        try {
            $enrol = ChitDetail::with(['customer_det', 'slab_due_det', 'branch_det'])->find($request->enrollment_id);
            if (!$enrol) {
                return response()->json(['status' => 'Error', 'msg' => 'Enrollment not found'], 200);
            }
            $receipts = DB::table('receipts')
                ->where('enrollment_id', $enrol->id)
                ->whereNull('deleted_at')
                ->orderBy('receipt_date')
                ->get(['id', 'receipt_no', 'receipt_date', 'amount', 'payment_type_id', 'transaction_no']);
            $payments = [];
            if (Schema::hasTable('payments')) {
                $payQuery = DB::table('payments')
                    ->where('enrollment_id', $enrol->id)
                    ->whereNull('deleted_at')
                    ->orderBy('payment_date');
                $payments = $payQuery->get(['id', 'payment_date', 'total_amount', 'payment_for', 'status']);
            }
            $customer = $enrol->customer_det;
            return response()->json([
                'status' => 'Success',
                'profile' => [
                    'customer_name' => $customer ? trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')) : '',
                    'mobile' => $customer->mobile_no ?? '',
                    'aadhar' => $customer->adhar_no ?? '',
                    'pan' => $customer->pan_no ?? '',
                    'ar_no' => $enrol->ar_no ?? $enrol->ticket_no,
                    'slab' => $enrol->slab_due_det->slab_name ?? '',
                    'branch' => $enrol->branch_det->branch_name ?? '',
                    'joining' => $enrol->date_introduced ?? '',
                ],
                'receipts' => $receipts,
                'payments' => $payments,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }

    private function fileUrl($name)
    {
        if (!$name) {
            return '';
        }
        $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
        return $base . '/storage/app/uploads/document_preparation/' . $name;
    }

    public function upload_file(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json(['status' => 'Error', 'msg' => 'No file uploaded'], 200);
            }
            $file = $request->file('file');
            $name = 'doc_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/document_preparation', $name);
            $base = rtrim((string) (env('APP_URL') ?: url('/')), '/');
            return response()->json([
                'status' => 'Success',
                'file_name' => $name,
                'file_url' => $base . '/storage/app/uploads/document_preparation/' . $name,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => $e->getMessage()], 200);
        }
    }
}
