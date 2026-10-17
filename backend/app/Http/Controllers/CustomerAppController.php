<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BiddingDetail;
use App\Models\ChitDetail;
use App\Models\CollectionAreaMapping;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentDetail;
use App\Models\Employee;
use App\Models\Payment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Config;
use DB;
use Storage;
use Validator;

/**
 * Customer mobile app: referrals / new-customer interest, full profile,
 * profile documents, account support contacts and branch list.
 */
class CustomerAppController extends Controller
{
    const LEADS_TABLE = 'customer_app_leads';

    const LEAD_STATUSES = [
        0 => 'New',
        1 => 'Contacted',
        2 => 'Converted',
        3 => 'Not Interested',
    ];

    private function useTenantDb(Request $request)
    {
        if (!empty($request->db)) {
            DB::purge('mysql');
            Config::set('database.connections.mysql.database', $request->db);
            DB::reconnect('mysql');
        }
    }

    /** Tenant databases are created per client, so create the table on first use if the migration was not run. */
    private function ensureLeadsTable()
    {
        if (Schema::hasTable(self::LEADS_TABLE)) {
            return;
        }
        Schema::create(self::LEADS_TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->string('lead_type', 20)->default('referral');
            $table->integer('referrer_customer_id')->default(0);
            $table->string('name', 150);
            $table->string('mobile_no', 20);
            $table->string('location', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('status_remarks')->nullable();
            $table->integer('updated_by')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'lead_type']);
        });
    }

    private function fileUrl($folder, $name)
    {
        if (empty($name)) {
            return '';
        }
        return rtrim(env('APP_URL'), '/') . Storage::url('app/uploads/' . $folder . '/' . $name);
    }

    /** Legacy rows store "0" for blank text fields. */
    private function clean($value)
    {
        $value = trim((string) $value, " \t\n\r\0\x0B,");
        return ($value === '0' || strtolower($value) === 'null') ? '' : $value;
    }

    private function dateOrEmpty($value, $format = 'd M Y')
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        return date($format, strtotime($value));
    }

    private function ngbDate($value)
    {
        if (is_array($value) && isset($value['year'])) {
            return sprintf('%04d-%02d-%02d', $value['year'], $value['month'], $value['day']);
        }
        if (is_string($value) && $value !== '') {
            return date('Y-m-d', strtotime($value));
        }
        return '';
    }

    /* =========================================================
       REFERRALS + NEW CUSTOMER INTEREST (customer app)
       ========================================================= */

    public function store_lead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db'        => 'required',
            'lead_type' => 'required|in:referral,new_customer',
            'name'      => 'required|max:150',
            'mobile_no' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'location'  => 'required|max:255',
        ], [
            'mobile_no.regex' => 'Enter a valid 10 digit mobile number',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }

        try {
            $this->useTenantDb($request);
            $this->ensureLeadsTable();

            $referrerId = (int) ($request->referrer_customer_id ?? 0);
            $branchId = (int) ($request->branch_id ?? 0);
            $tenantId = (int) ($request->tenant_id ?? 0);
            if ($referrerId > 0) {
                $referrer = Customer::select('id', 'tenant_id', 'branch_id')->find($referrerId);
                if ($referrer) {
                    $branchId = $branchId ?: (int) $referrer->branch_id;
                    $tenantId = $tenantId ?: (int) $referrer->tenant_id;
                }
            }

            $duplicate = DB::table(self::LEADS_TABLE)
                ->where('lead_type', $request->lead_type)
                ->where('mobile_no', $request->mobile_no)
                ->where('referrer_customer_id', $referrerId)
                ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-1 day')))
                ->exists();
            if ($duplicate) {
                return response()->json([
                    'status' => 'Error',
                    'msg'    => 'This mobile number was already submitted today. Our team will contact them soon.',
                ], 200);
            }

            $id = DB::table(self::LEADS_TABLE)->insertGetId([
                'tenant_id'            => $tenantId,
                'branch_id'            => $branchId,
                'lead_type'            => $request->lead_type,
                'referrer_customer_id' => $referrerId,
                'name'                 => trim($request->name),
                'mobile_no'            => $request->mobile_no,
                'location'             => trim($request->location),
                'remarks'              => $request->remarks ?? null,
                'status'               => 0,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'status' => 'Success',
                'msg'    => $request->lead_type === 'referral'
                    ? 'Thank you! Your referral has been submitted.'
                    : 'Thank you! Our team will contact you shortly.',
                'data'   => ['id' => $id],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer app store lead error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'Error', 'msg' => 'Server Error'], 500);
        }
    }

    public function my_referrals(Request $request)
    {
        if (empty($request->db) || empty($request->customer_id)) {
            return response()->json(['status' => 'Error', 'msg' => 'Required parameters missing'], 200);
        }
        try {
            $this->useTenantDb($request);
            $this->ensureLeadsTable();

            $rows = DB::table(self::LEADS_TABLE)
                ->where('lead_type', 'referral')
                ->where('referrer_customer_id', $request->customer_id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($row) {
                    return [
                        'id'                => $row->id,
                        'name'              => $row->name,
                        'mobile_no'         => $row->mobile_no,
                        'location'          => $row->location,
                        'status'            => (int) $row->status,
                        'status_label'      => self::LEAD_STATUSES[(int) $row->status] ?? 'New',
                        'created_at_display'=> $this->dateOrEmpty($row->created_at),
                    ];
                });

            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $rows], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Server Error'], 500);
        }
    }

    /* =========================================================
       WEB REPORT (JWT)
       ========================================================= */

    public function list_leads(Request $request)
    {
        try {
            $this->ensureLeadsTable();

            $query = DB::table(self::LEADS_TABLE . ' as l')
                ->leftJoin('customers as c', 'c.id', '=', 'l.referrer_customer_id')
                ->leftJoin('branches as b', 'b.id', '=', 'l.branch_id')
                ->select(
                    'l.*',
                    'c.name as referrer_name',
                    'c.customer_code as referrer_code',
                    'c.mobile_no as referrer_mobile',
                    'b.branch_name'
                );

            if (!empty($request->tenant_id)) {
                $query->where(function ($q) use ($request) {
                    $q->where('l.tenant_id', $request->tenant_id)->orWhere('l.tenant_id', 0);
                });
            }
            if (!empty($request->branch_id)) {
                $query->where('l.branch_id', $request->branch_id);
            }
            if (!empty($request->lead_type)) {
                $query->where('l.lead_type', $request->lead_type);
            }
            if (isset($request->status) && $request->status !== '' && $request->status !== null) {
                $query->where('l.status', (int) $request->status);
            }
            $start = $this->ngbDate($request->start_date);
            $end = $this->ngbDate($request->end_date);
            if ($start !== '') {
                $query->whereDate('l.created_at', '>=', $start);
            }
            if ($end !== '') {
                $query->whereDate('l.created_at', '<=', $end);
            }

            $rows = $query->orderBy('l.id', 'desc')->get();
            $data = [];
            $i = 1;
            foreach ($rows as $row) {
                $data[] = [
                    'sno'             => $i++,
                    'id'              => $row->id,
                    'lead_type'       => $row->lead_type,
                    'lead_type_label' => $row->lead_type === 'referral' ? 'Referral' : 'New Customer (Login)',
                    'name'            => $row->name,
                    'mobile_no'       => $row->mobile_no,
                    'location'        => $row->location,
                    'remarks'         => $row->remarks,
                    'referrer_name'   => $row->referrer_name ?? '',
                    'referrer_code'   => $row->referrer_code ?? '',
                    'referrer_mobile' => $row->referrer_mobile ?? '',
                    'branch_name'     => $row->branch_name ?? '',
                    'status'          => (int) $row->status,
                    'status_label'    => self::LEAD_STATUSES[(int) $row->status] ?? 'New',
                    'status_remarks'  => $row->status_remarks,
                    'created_at'      => $this->dateOrEmpty($row->created_at, 'd-m-Y h:i A'),
                ];
            }

            return response()->json([
                'status'   => 'Success',
                'msg'      => '',
                'data'     => $data,
                'statuses' => self::LEAD_STATUSES,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer app leads report error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'Error', 'msg' => 'Sorry!! Something went wrong!'], 200);
        }
    }

    public function update_lead_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer',
            'status' => 'required|integer|in:0,1,2,3',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }
        try {
            $this->ensureLeadsTable();
            DB::table(self::LEADS_TABLE)->where('id', $request->id)->update([
                'status'         => (int) $request->status,
                'status_remarks' => $request->status_remarks ?? null,
                'updated_by'     => (int) ($request->updated_by ?? 0),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            return response()->json(['status' => 'Success', 'msg' => 'Status updated'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'msg' => 'Sorry!! Something went wrong!'], 200);
        }
    }

    /* =========================================================
       FULL PROFILE + CHIT SUMMARY
       ========================================================= */

    private function chitSummary(Request $request, $customerId)
    {
        $summary = [
            'total_chits'     => 0,
            'active_chits'    => 0,
            'completed_chits' => 0,
            'closed_chits'    => 0,
            'prized_chits'    => 0,
            'non_prized_chits'=> 0,
            'total_chit_value'=> 0,
            'total_paid'      => 0,
            'total_pending'   => 0,
            'prize_amount'    => 0,
            'prize_released'  => 0,
        ];

        $myChits = app(MobileApiController::class)->customer_my_chits($request);
        $payload = json_decode($myChits->getContent(), true);
        $items = (is_array($payload) && ($payload['status'] ?? '') === 'Success') ? ($payload['data'] ?? []) : [];

        foreach ($items as $item) {
            $summary['total_chits']++;
            $tab = $item['status_tab'] ?? '';
            if ($tab === 'running') {
                $summary['active_chits']++;
            } elseif ($tab === 'completed') {
                $summary['completed_chits']++;
            } else {
                $summary['closed_chits']++;
            }
            if (!empty($item['auction_won']) && $item['auction_won'] !== '0' && $item['auction_won'] !== false) {
                $summary['prized_chits']++;
            }
            $summary['total_chit_value'] += (float) ($item['chit_value'] ?? 0);
            $summary['total_paid'] += (float) ($item['paid_amount'] ?? 0);
            $summary['total_pending'] += (float) ($item['pending_amount'] ?? 0);
        }

        $enrolls = ChitDetail::where('customer_id', $customerId)->whereNull('deleted_at')->get();
        $enrollIds = $enrolls->pluck('id')->toArray();
        if (!empty($enrollIds)) {
            $wins = BiddingDetail::whereIn('enrollment_id', $enrollIds)->whereNull('deleted_at')->get();
            $summary['prize_amount'] = max((float) $wins->sum('total_release_amount'), (float) $enrolls->sum('total_payment_release'));
            $prizedEnrolls = $wins->pluck('enrollment_id')
                ->merge($enrolls->where('total_payment_release', '>', 0)->pluck('id'))
                ->unique()->count();
            $summary['prized_chits'] = max($summary['prized_chits'], $prizedEnrolls);
            $summary['prize_released'] = max(
                (float) Payment::whereIn('enrollment_id', $enrollIds)->whereNull('deleted_at')->sum('total_amount'),
                (float) $enrolls->sum('payment_released')
            );
        }
        $summary['non_prized_chits'] = max(0, $summary['total_chits'] - $summary['prized_chits']);

        foreach (['total_chit_value', 'total_paid', 'total_pending', 'prize_amount', 'prize_released'] as $key) {
            $summary[$key] = strval(round($summary[$key]));
        }
        return $summary;
    }

    private function addressLine($c, $prefix = '')
    {
        $parts = [
            $c->{$prefix . 'door_no'} ?? '',
            $c->{$prefix . ($prefix === '' ? 'buildling_name' : 'building_name')} ?? '',
            $c->{$prefix . 'address_line_1'} ?? '',
            $c->{$prefix . 'address_line_2'} ?? '',
            $c->{$prefix . ($prefix === '' ? 'landmark' : 'land_mark')} ?? '',
        ];
        $city = DB::table('cities')->where('id', $c->{$prefix . 'city_id'} ?? 0)->value('city_name');
        $district = DB::table('districts')->where('id', $c->{$prefix . 'district_id'} ?? 0)->value('district_name');
        $state = DB::table('states')->where('id', $c->{$prefix . 'state_id'} ?? 0)->value('state_name');
        $parts[] = $city;
        $parts[] = $district;
        $parts[] = $state;
        $pin = $c->{$prefix . 'pincode'} ?? '';
        $line = implode(', ', array_filter(array_map([$this, 'clean'], $parts), 'strlen'));
        if ($this->clean($pin) !== '') {
            $line .= ($line !== '' ? ' - ' : '') . $pin;
        }
        return $line;
    }

    private function customerDocuments($customerId)
    {
        return DocumentDetail::where('customer_id', $customerId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id'            => $d->id,
                    'document_type' => $d->document_type ?? 'Document',
                    'document_no'   => $d->document_no ?? '',
                    'document_url'  => $this->fileUrl('customer_documents', $d->document_path),
                    'uploaded_on'   => $this->dateOrEmpty($d->created_at),
                ];
            })
            ->values();
    }

    public function full_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db'          => 'required',
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }

        try {
            $this->useTenantDb($request);
            $c = Customer::find($request->customer_id);
            if (!$c) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer not found'], 200);
            }

            $branch = Branch::find($c->branch_id);
            $relationship = DB::table('relationships')->where('id', $c->relationship_id ?? 0)->value('relationship_name');
            $introducer = '';
            if (!empty($c->introducer_id)) {
                $introducer = Customer::where('id', $c->introducer_id)->value('name') ?? '';
            }
            if ($introducer === '') {
                $introducer = $c->introducer_name ?? ($c->reference_name ?? '');
            }
            $genders = [1 => 'Male', 2 => 'Female', 3 => 'Other'];

            $profile = [
                'customer_id'           => $c->id,
                'customer_code'         => $c->customer_code ?? '',
                'salutation'            => $c->saluation ?? '',
                'name'                  => trim(($c->name ?? '') . ' ' . ($c->last_name ?? '')),
                'first_name'            => $c->name ?? '',
                'last_name'             => $c->last_name ?? '',
                'photo_url'             => $this->fileUrl('customer_photos', $c->photo),
                'gender'                => $genders[(int) $c->gender] ?? '',
                'dob'                   => $this->dateOrEmpty($c->dob),
                'dob_raw'               => $this->dateOrEmpty($c->dob, 'Y-m-d'),
                'date_of_joining'       => $this->dateOrEmpty($c->doj),
                'mobile_no'             => $c->mobile_no ?? '',
                'alternate_mobile'      => $c->phone_no ?? '',
                'email_id'              => $c->email_id ?? '',
                'father_name'           => $c->father_name ?? '',
                'spouse_name'           => $c->spouse_name ?? '',
                'occupation'            => $c->occupation ?? '',
                'income'                => $c->income ? strval(round($c->income)) : '',
                'source_of_funds'       => $c->source_of_funds ?? '',
                'aadhar_no'             => $c->adhar_no ?? '',
                'pan_no'                => $c->pan_no ?? '',
                'license_no'            => $c->license_no ?? '',
                'ration_card_no'        => $c->ration_card_no ?? '',
                'family_code'           => $c->family_code ?? '',
                'relationship'          => $relationship ?? '',
                'introducer'            => $introducer,
                'residential_address'   => $this->addressLine($c),
                'communication_address' => $this->addressLine($c, 'communication_'),
                'permanent_address'     => $this->addressLine($c, 'permanent_'),
                'bank_name'             => $c->bank_name ?? '',
                'account_holder_name'   => $c->account_holder_name ?? '',
                'account_no'            => $c->account_no ?? '',
                'ifsc_code'             => $c->ifsc_code ?? '',
                'bank_branch'           => $c->bank_branch ?? '',
                'branch_name'           => $branch->branch_name ?? '',
                'branch_code'           => $branch->branch_code ?? '',
                'member_since'          => $this->dateOrEmpty($c->doj ?: $c->created_at, 'M Y'),
                'status'                => ((int) $c->status === 1) ? 'Active' : 'Inactive',
            ];
            foreach ($profile as $key => $value) {
                if ($key !== 'customer_id') {
                    $profile[$key] = $this->clean($value);
                }
            }

            return response()->json([
                'status' => 'Success',
                'msg'    => '',
                'data'   => [
                    'profile'      => $profile,
                    'chit_summary' => $this->chitSummary($request, $c->id),
                    'documents'    => $this->customerDocuments($c->id),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer full profile error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'Error', 'msg' => 'Server Error'], 500);
        }
    }

    /**
     * Multipart upload from the app: photo, aadhar_front, aadhar_back, pan_card (+ aadhar_no, pan_no).
     * Photo updates customers.photo; ID proofs are saved as customer documents (same tables the web Documents page reads).
     */
    public function upload_profile_documents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db'           => 'required',
            'customer_id'  => 'required',
            'photo'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'aadhar_front' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'aadhar_back'  => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'pan_card'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'other_document'      => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'other_document_type' => 'required_with:other_document|max:60',
            'other_document_no'   => 'nullable|max:40',
            'aadhar_no'    => ['nullable', 'regex:/^[0-9]{12}$/'],
            'pan_no'       => ['nullable', 'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/'],
        ], [
            'aadhar_no.regex' => 'Aadhaar number must be 12 digits',
            'pan_no.regex'    => 'Enter a valid PAN number (e.g. ABCDE1234F)',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }

        try {
            $this->useTenantDb($request);
            $c = Customer::find($request->customer_id);
            if (!$c) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer not found'], 200);
            }

            $updated = [];
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $file = $request->file('photo');
                $file->store('uploads/customer_photos');
                $c->photo = $file->hashName();
                $updated[] = 'photo';
            }
            if (!empty($request->aadhar_no)) {
                $c->adhar_no = $request->aadhar_no;
                $updated[] = 'aadhar_no';
            }
            if (!empty($request->pan_no)) {
                $c->pan_no = strtoupper($request->pan_no);
                $updated[] = 'pan_no';
            }
            if (!empty($updated)) {
                $c->updated_by = 0;
                $c->save();
            }

            $docs = [
                'aadhar_front' => ['Aadhaar Card (Front)', $c->adhar_no],
                'aadhar_back'  => ['Aadhaar Card (Back)', $c->adhar_no],
                'pan_card'     => ['PAN Card', $c->pan_no],
            ];
            if ($request->hasFile('other_document')) {
                $docs['other_document'] = [
                    substr(trim(strip_tags($request->other_document_type)), 0, 60) ?: 'Other Document',
                    $request->other_document_no ?? '',
                ];
            }
            $files = [];
            foreach ($docs as $field => $meta) {
                if ($request->hasFile($field) && $request->file($field)->isValid()) {
                    $files[$field] = $meta;
                }
            }

            if (!empty($files)) {
                $document = new Document();
                $document->tenant_id = $c->tenant_id ?? ($request->tenant_id ?? 0);
                $document->branch_id = $c->branch_id ?? 0;
                $document->customer_id = $c->id;
                $document->collected_employee_id = 0;
                $document->collect_date = date('Y-m-d');
                $document->collected_from = 'Mobile App';
                $document->remark = 'Uploaded by customer';
                $document->created_by = 0;
                $document->status = 1;
                $document->save();

                foreach ($files as $field => $meta) {
                    $file = $request->file($field);
                    $file->store('uploads/customer_documents');

                    DocumentDetail::where('customer_id', $c->id)
                        ->where('document_type', $meta[0])
                        ->whereNull('deleted_at')
                        ->update(['status' => 0, 'deleted_at' => date('Y-m-d H:i:s'), 'deletion_remark' => 'Replaced from customer app']);

                    $detail = new DocumentDetail();
                    $detail->document_id = $document->id;
                    $detail->customer_id = $c->id;
                    $detail->document_no = $meta[1] ?? '';
                    $detail->document_path = $file->hashName();
                    $detail->document_type = $meta[0];
                    $detail->status = 1;
                    $detail->save();
                    $updated[] = $field;
                }
            }

            if (empty($updated)) {
                return response()->json(['status' => 'Error', 'msg' => 'Nothing to update. Choose a photo or document.'], 200);
            }

            return response()->json([
                'status' => 'Success',
                'msg'    => 'Profile updated successfully',
                'data'   => [
                    'updated'   => $updated,
                    'photo_url' => $this->fileUrl('customer_photos', $c->photo),
                    'documents' => $this->customerDocuments($c->id),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer upload documents error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'Error', 'msg' => 'Upload failed. Please try again.'], 500);
        }
    }

    /** Same edit history the web "Update Customer" screen writes. */
    private function snapshotCustomer(Customer $c)
    {
        try {
            if (!Schema::hasTable('update_customers')) {
                return;
            }
            $columns = Schema::getColumnListing('update_customers');
            $row = [];
            foreach ($c->getAttributes() as $key => $value) {
                if ($key !== 'id' && in_array($key, $columns, true)) {
                    $row[$key] = $value;
                }
            }
            if (in_array('update_customer_id', $columns, true)) {
                $row['update_customer_id'] = $c->id;
            }
            if (in_array('edit_date', $columns, true)) {
                $row['edit_date'] = date('Y-m-d');
            }
            $row['created_at'] = date('Y-m-d H:i:s');
            $row['updated_at'] = date('Y-m-d H:i:s');
            DB::table('update_customers')->insert($row);
        } catch (\Exception $e) {
            \Log::warning('Customer app snapshot skipped', ['error' => $e->getMessage()]);
        }
    }

    /** Customer self-service edit: DOB, email, alternate mobile, KYC numbers and bank details. */
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db'                  => 'required',
            'customer_id'         => 'required',
            'dob'                 => 'nullable|date_format:Y-m-d|before:today',
            'email_id'            => 'nullable|email|max:150',
            'alternate_mobile'    => ['nullable', 'regex:/^[0-9]{10,12}$/'],
            'aadhar_no'           => ['nullable', 'regex:/^[0-9]{12}$/'],
            'pan_no'              => ['nullable', 'regex:/^[A-Za-z]{5}[0-9]{4}[A-Za-z]$/'],
            'license_no'          => 'nullable|max:30',
            'ration_card_no'      => 'nullable|max:30',
            'bank_name'           => 'nullable|max:150',
            'account_holder_name' => 'nullable|max:150',
            'account_no'          => ['nullable', 'regex:/^[0-9]{6,20}$/'],
            'ifsc_code'           => ['nullable', 'regex:/^[A-Za-z]{4}0[A-Za-z0-9]{6}$/'],
            'bank_branch'         => 'nullable|max:150',
        ], [
            'dob.date_format'   => 'Enter date of birth as YYYY-MM-DD',
            'dob.before'        => 'Date of birth must be in the past',
            'alternate_mobile.regex' => 'Enter a valid alternate mobile number',
            'aadhar_no.regex'   => 'Aadhaar number must be 12 digits',
            'pan_no.regex'      => 'Enter a valid PAN number (e.g. ABCDE1234F)',
            'account_no.regex'  => 'Account number must be 6 to 20 digits',
            'ifsc_code.regex'   => 'Enter a valid IFSC code (e.g. SBIN0001234)',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'Error', 'msg' => $validator->errors()->first()], 200);
        }

        try {
            $this->useTenantDb($request);
            $c = Customer::find($request->customer_id);
            if (!$c) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer not found'], 200);
            }

            $map = [
                'dob'                 => 'dob',
                'email_id'            => 'email_id',
                'alternate_mobile'    => 'phone_no',
                'aadhar_no'           => 'adhar_no',
                'pan_no'              => 'pan_no',
                'license_no'          => 'license_no',
                'ration_card_no'      => 'ration_card_no',
                'bank_name'           => 'bank_name',
                'account_holder_name' => 'account_holder_name',
                'account_no'          => 'account_no',
                'ifsc_code'           => 'ifsc_code',
                'bank_branch'         => 'bank_branch',
            ];
            $changed = [];
            foreach ($map as $input => $column) {
                if (!$request->has($input)) {
                    continue;
                }
                $value = trim((string) $request->input($input));
                if (in_array($input, ['pan_no', 'ifsc_code'], true)) {
                    $value = strtoupper($value);
                }
                if ($value === '' || $value === $this->clean($c->{$column})) {
                    continue;
                }
                $c->{$column} = $value;
                $changed[] = $input;
            }

            if (empty($changed)) {
                return response()->json(['status' => 'Error', 'msg' => 'No changes to save'], 200);
            }
            $c->updated_by = 0;
            $c->save();
            $this->snapshotCustomer($c);

            return response()->json([
                'status' => 'Success',
                'msg'    => 'Profile updated successfully',
                'data'   => ['updated' => $changed],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer app update profile error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'Error', 'msg' => 'Update failed. Please try again.'], 500);
        }
    }

    /* =========================================================
       ACCOUNT SUPPORT CONTACTS
       ========================================================= */

    private function employeeCard($emp, $roleName = '')
    {
        if (!$emp) {
            return null;
        }
        $code = !empty($emp->employee_code) ? $emp->employee_code : ('EMP-' . sprintf('%04d', $emp->id));
        return [
            'employee_id'   => $emp->id,
            'employee_code' => $code,
            'name'          => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
            'role'          => $roleName,
            'mobile_no'     => $this->clean($emp->mobile_no) ?: $this->clean($emp->phone_no ?? ''),
            'email'         => $this->clean($emp->email ?? ''),
            'photo_url'     => $this->fileUrl('employee_photos', $emp->photo ?? ''),
        ];
    }

    private function collectionOfficerFor($enroll, $roles)
    {
        if (!empty($enroll->collection_agent_id)) {
            $emp = Employee::find($enroll->collection_agent_id);
            if ($emp) {
                return $this->employeeCard($emp, $roles[$emp->role_id] ?? 'Collection Officer');
            }
        }
        $areaId = (int) ($enroll->collection_area_id ?? 0);
        if ($areaId <= 0) {
            return null;
        }
        $mappings = CollectionAreaMapping::where('branch_id', $enroll->branch_id)->whereNull('deleted_at')->get();
        foreach ($mappings as $map) {
            $areas = @unserialize($map->collection_area_id);
            if (!is_array($areas)) {
                $areas = array_map('intval', explode(',', (string) $map->collection_area_id));
            }
            foreach ($areas as $area) {
                $id = is_array($area) ? (int) ($area['item_id'] ?? 0) : (int) $area;
                if ($id === $areaId) {
                    $emp = Employee::find($map->employee_id);
                    if ($emp) {
                        return $this->employeeCard($emp, $roles[$emp->role_id] ?? 'Collection Officer');
                    }
                }
            }
        }
        return null;
    }

    public function support_contacts(Request $request)
    {
        if (empty($request->db) || empty($request->customer_id)) {
            return response()->json(['status' => 'Error', 'msg' => 'Required parameters missing'], 200);
        }
        try {
            $this->useTenantDb($request);
            $c = Customer::find($request->customer_id);
            if (!$c) {
                return response()->json(['status' => 'Error', 'msg' => 'Customer not found'], 200);
            }

            $roles = DB::table('roles')->whereNull('deleted_at')->pluck('role_name', 'id')->toArray();
            $branch = Branch::find($c->branch_id);

            $branchCard = null;
            if ($branch) {
                $branchCard = [
                    'branch_id'   => $branch->id,
                    'branch_name' => $branch->branch_name,
                    'branch_code' => $branch->branch_code ?? '',
                    'address'     => $this->branchAddress($branch),
                    'phone_no'    => $this->clean($branch->phone_no ?? ''),
                    'mobile_no'   => $this->clean($branch->mobile_no ?? ''),
                    'email_id'    => $this->clean($branch->email_id ?? ''),
                ];
            }

            $leaders = [];
            if ($branch) {
                $leaderRoleIds = [];
                foreach ($roles as $id => $name) {
                    if (preg_match('/manager|admin|\bbm\b/i', $name)) {
                        $leaderRoleIds[] = $id;
                    }
                }
                $emps = Employee::where('branch_id', $branch->id)
                    ->whereIn('role_id', $leaderRoleIds)
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->orderBy('role_id')
                    ->get();
                foreach ($emps as $emp) {
                    $leaders[] = $this->employeeCard($emp, $roles[$emp->role_id] ?? '');
                }
            }

            $officers = [];
            $enrolls = ChitDetail::where('customer_id', $c->id)->where('status', 1)->get();
            foreach ($enrolls as $enroll) {
                $card = $this->collectionOfficerFor($enroll, $roles);
                if ($card && !isset($officers[$card['employee_id']])) {
                    $officers[$card['employee_id']] = $card;
                }
            }

            return response()->json([
                'status' => 'Success',
                'msg'    => '',
                'data'   => [
                    'branch'             => $branchCard,
                    'branch_team'        => array_values(array_filter($leaders)),
                    'collection_officers'=> array_values($officers),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Customer support contacts error', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['status' => 'Error', 'msg' => 'Server Error'], 500);
        }
    }

    /* =========================================================
       BRANCH LIST (branch master)
       ========================================================= */

    private function branchAddress($b)
    {
        $city = DB::table('cities')->where('id', $b->city_id ?? 0)->value('city_name');
        $district = DB::table('districts')->where('id', $b->district_id ?? 0)->value('district_name');
        $state = DB::table('states')->where('id', $b->state_id ?? 0)->value('state_name');
        $parts = array_filter(array_map([$this, 'clean'], [
            $b->address_line_1, $b->address_line_2, $b->landmark, $city, $district, $state,
        ]), 'strlen');
        $line = implode(', ', $parts);
        if ($this->clean($b->pincode) !== '') {
            $line .= ($line !== '' ? ' - ' : '') . $b->pincode;
        }
        return $line;
    }

    public function branches(Request $request)
    {
        if (empty($request->db)) {
            return response()->json(['status' => 'Error', 'msg' => 'Required parameters missing', 'data' => []], 200);
        }
        try {
            $this->useTenantDb($request);
            $query = Branch::whereNull('deleted_at')->where(function ($q) {
                $q->where('status', 1)->orWhereNull('status');
            });
            if (!empty($request->tenant_id)) {
                $query->where('tenant_id', $request->tenant_id);
            }
            $rows = $query->orderBy('branch_name')->get();

            $cities = DB::table('cities')->pluck('city_name', 'id');
            $districts = DB::table('districts')->pluck('district_name', 'id');
            $states = DB::table('states')->pluck('state_name', 'id');

            $data = $rows->map(function ($b) use ($cities, $districts, $states) {
                $row = [
                    'branch_id'      => $b->id,
                    'branch_name'    => $b->branch_name,
                    'branch_code'    => $b->branch_code ?? '',
                    'address_line_1' => $b->address_line_1 ?? '',
                    'address_line_2' => $b->address_line_2 ?? '',
                    'landmark'       => $b->landmark ?? '',
                    'city_name'      => $cities[$b->city_id] ?? '',
                    'district_name'  => $districts[$b->district_id] ?? '',
                    'state_name'     => $states[$b->state_id] ?? '',
                    'pincode'        => $b->pincode ?? '',
                    'phone_no'       => $b->phone_no ?? '',
                    'mobile_no'      => $b->mobile_no ?? '',
                    'email_id'       => $b->email_id ?? '',
                    'latitude'       => $b->latitude ?? '',
                    'longitude'      => $b->longitude ?? '',
                ];
                foreach ($row as $key => $value) {
                    if ($key !== 'branch_id') {
                        $row[$key] = $this->clean($value);
                    }
                }
                $row['address'] = $this->branchAddress($b);
                return $row;
            })->values();

            return response()->json(['status' => 'Success', 'msg' => '', 'data' => $data], 200);
        } catch (\Exception $e) {
            \Log::error('Customer branches error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'Error', 'msg' => 'Server Error', 'data' => []], 500);
        }
    }
}
