<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\DocTypeSubCategories;
use App\Models\DocPackageRevisionFile;
use App\Models\Customers;
use App\Models\FileExtensions;
use App\Models\DoctypeGroups;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Mail\ShareNotification;
use Illuminate\Support\Facades\URL;
use App\Models\StampFormat;
use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;

class ShareController extends Controller
{
    public function getSuppliers()
    {
        try {
            $roles = DB::table('suppliers')
                ->select('id', 'code')
                ->orderBy('code', 'asc')
                ->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('ShareController@getRoles failed: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat daftar role'], 500);
        }
    }

    public function choiseFilter(Request $request): JsonResponse
    {
        if ($request->filled('select2')) {
            $field   = $request->get('select2');
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            $customerCode = $request->get('customer_code');
            $docTypeName  = $request->get('doc_type');

            $total = 0;
            $items = collect();

            switch ($field) {
                case 'customer':
                    $builder = DB::table('customers as c')
                        ->selectRaw('c.code AS id, c.code AS text')
                        ->when($q, fn($x) => $x->where(function ($w) use ($q) {
                            $w->where('c.code', 'like', "%{$q}%")
                                ->orWhere('c.name', 'like', "%{$q}%");
                        }))
                        ->orderBy('c.code');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'model':
                    $builder = DB::table('models as m')
                        ->join('customers as c', 'm.customer_id', '=', 'c.id')
                        ->when($customerCode && $customerCode !== 'All', function ($x) use ($customerCode) {
                            $x->where('c.code', $customerCode);
                        })
                        ->when($q, function ($x) use ($q) {
                            $x->where('m.name', 'like', "%{$q}%");
                        });
                    $namesQuery = $builder
                        ->selectRaw('m.name AS id, m.name AS text')
                        ->distinct()
                        ->orderBy('m.name');
                    $total = (clone $namesQuery)->count();
                    $items = $namesQuery->forPage($page, $perPage)->get();
                    break;

                case 'doc_type':
                    $builder = DB::table('doctype_groups as dtg')
                        ->selectRaw('dtg.name AS id, dtg.name AS text')
                        ->when($q, fn($x) => $x->where('dtg.name', 'like', "%{$q}%"))
                        ->orderBy('dtg.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'category':
                    $builder = DB::table('doctype_subcategories as dsc')
                        ->join('doctype_groups as dtg', 'dsc.doctype_group_id', '=', 'dtg.id')
                        ->selectRaw('dsc.name AS id, dsc.name AS text')
                        ->when($docTypeName && $docTypeName !== 'All', fn($x) => $x->where('dtg.name', $docTypeName))
                        ->when($q, fn($x) => $x->where('dsc.name', 'like', "%{$q}%"))
                        ->orderBy('dsc.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'status':
                    // sekarang: status = Project Status (Feasibility, SOP, dst.)
                    $builder = DB::table('project_status as ps')
                        ->selectRaw('ps.name AS id, ps.name AS text')
                        ->when(
                            $q,
                            fn($x) =>
                            $x->where('ps.name', 'like', "%{$q}%")
                        )
                        ->orderBy('ps.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                default:
                    return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }

            if ($page === 1) {
                $items = collect([['id' => 'All', 'text' => 'All']])->merge($items);
            }

            $effectiveTotal = $total + ($page === 1 ? 1 : 0);
            $more = ($effectiveTotal > $page * $perPage);

            return response()->json([
                'results'    => array_values($items->toArray()),
                'pagination' => ['more' => $more]
            ]);
        }

        $customerId = $request->integer('customer_id') ?: null;
        if (!$customerId && $request->filled('customer_code')) {
            $customerId = Customers::where('code', $request->get('customer_code'))->value('id');
        }

        $models = $customerId
            ? Models::where('customer_id', $customerId)->orderBy('name')->get(['id', 'name'])
            : Models::orderBy('name')->get(['id', 'name']);

        $docTypes = DoctypeGroups::orderBy('name')->get(['id', 'name']);

        $docTypeName = $request->get('doc_type');
        $docTypeId   = null;
        if ($docTypeName && $docTypeName !== 'All') {
            $docTypeId = DoctypeGroups::where('name', $docTypeName)->value('id');
        }

        $categories = DocTypeSubCategories::when($docTypeId, function ($q) use ($docTypeId) {
            $q->where('doctype_group_id', $docTypeId);
        })
            ->orderBy('name')
            ->get(['name']);

        return response()->json([
            'customers'  => Customers::orderBy('code')->get(['id', 'code']),
            'models'     => $models,
            'doc_types'  => $docTypes,
            'categories' => $categories,
            'statuses'   => collect([
                ['name' => 'Waiting'],
                ['name' => 'Approved'],
                ['name' => 'Rejected'],
            ]),
        ]);
    }

    public function listPackage(Request $request): JsonResponse
    {
        $start       = (int) $request->get('start', 0);
        $length      = (int) $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';

        $orderColumnIndex = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir         = $request->get('order')[0]['dir'] ?? 'desc';
        $orderColumnName  = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.created_at';

        $latestPa = DB::table('package_approvals as pa')
            ->select(
                'pa.id',
                'pa.revision_id',
                'pa.requested_at',
                'pa.decided_at',
                'pa.decision',
                'pa.decided_by'
            )
            ->selectRaw("
            ROW_NUMBER() OVER (
                PARTITION BY pa.revision_id
                ORDER BY COALESCE(pa.decided_at, pa.requested_at) DESC, pa.id DESC
            ) as rn
        ");

        $query = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id') // <--- TAMBAH INI
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft')
            ->where('dpr.revision_status', 'approved');


        $recordsTotal = (clone $query)->count();

        if ($request->filled('customer') && $request->customer !== 'All') {
            $query->where('c.code', $request->customer);
        }
        if ($request->filled('model') && $request->model !== 'All') {
            $query->where('m.name', $request->model);
        }
        if ($request->filled('doc_type') && $request->doc_type !== 'All') {
            $query->where('dtg.name', $request->doc_type);
        }
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('dsc.name', $request->category);
        }
        if ($request->filled('status') && $request->status !== 'All') {
            $query->where('ps.name', $request->status);
        }

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                    ->orWhereRaw("
                CONCAT(
                    c.code,' ',
                    m.name,' ',
                    dtg.name,' ',
                    COALESCE(dsc.name,''),' ',
                    COALESCE(p.part_no,''),' ',
                    dpr.revision_no
                ) LIKE ?
            ", ["%{$searchValue}%"]);
            });
        }

        $recordsFiltered = (clone $query)->count();

        $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'dtg.name as doc_type',
            'dsc.name as category',
            'p.part_no',
            'dpr.revision_no as revision',
            DB::raw("
            CASE COALESCE(pa.decision, dpr.revision_status)
                WHEN 'pending'  THEN 'Waiting'
                WHEN 'waiting'  THEN 'Waiting'  
                WHEN 'approved' THEN 'Approved'
                WHEN 'rejected' THEN 'Rejected'
                ELSE COALESCE(pa.decision, dpr.revision_status)
            END as status
        "),
            'dpr.share_to as share_to',
            'pa.requested_at as request_date',
            'pa.decided_at   as decision_date'
        );

        $orderWhitelist = [
            'dpr.created_at',
            'dpr.updated_at',
            'pa.requested_at',
            'pa.decided_at',
            'dpr.revision_status',
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
        ];
        $orderBy        = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'pa.requested_at';
        $orderDirection = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

        $data = $query
            ->orderBy($orderBy, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $roleMap = DB::table('suppliers')->pluck('code', 'id')->all();

        $data->transform(function ($item) use ($roleMap) {
            if (empty($item->share_to)) {
                $item->share_to = 'Not yet distributed';
                return $item;
            }

            $roleIds = json_decode($item->share_to, true);

            if (empty($roleIds) || !is_array($roleIds)) {
                $item->share_to = 'Not yet distributed';
                return $item;
            }

            $roleNames = [];
            foreach ($roleIds as $id) {
                $roleNames[] = $roleMap[$id] ?? "Unknown (ID: {$id})";
            }

            $item->share_to = implode(', ', $roleNames);

            return $item;
        });

        // 🔹 TAMBAHAN: hash untuk link ke share.detail
        $data = $data->map(function ($row) {
            // dpr.id yang Tuan select tadi adalah revision_id
            $row->hash = encrypt($row->id);
            return $row;
        });


        return response()->json([
            "draw"            => (int) $request->get('draw'),
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
        ]);
    }


    public function saveShare(Request $request)
    {
        // Validasi Input
        $validator = Validator::make($request->all(), [
            'package_id'     => 'required|integer|exists:doc_package_revisions,id',
            'supplier_ids'   => 'required|array',
            'supplier_ids.*' => 'integer|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $revisionId  = $request->input('package_id');
        $supplierIds = $request->input('supplier_ids');

        // Set Expiry (14 hari dari sekarang)
        $expiredAt = Carbon::now()->addDays(14);
        
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        if (!$userId) {
            return response()->json(['message' => 'Unauthorized user.'], 401);
        }

        DB::beginTransaction();
        try {
            // Update atau Insert ke tabel package_shares
            foreach ($supplierIds as $supplierId) {
                DB::table('package_shares')->updateOrInsert(
                    [
                        'revision_id' => $revisionId,
                        'supplier_id' => $supplierId,
                    ],
                    [
                        'department_id' => null,
                        'shared_at'     => now(),
                        'expired_at'    => $expiredAt,
                        'created_by'    => $userId,
                        'updated_at'    => now(),
                    ]
                );
            }

            // Ambil Detail Package untuk kebutuhan Log & Email
            $packageDetails = DB::table('doc_package_revisions as dpr')
                ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
                ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
                ->where('dpr.id', $revisionId)
                ->select(
                    'dp.id as package_id',
                    'c.code as customer',
                    'm.name as model',
                    'p.part_no',
                    'pg.code_part_group',
                    'dsc.name as category',
                    'dtg.name as doc_type',
                    'dpr.revision_no',
                    'dpr.ecn_no'
                )
                ->first();

            // Ambil Supplier CODE (bukan Name) untuk Log summary
            $supplierCodes = DB::table('suppliers')
                ->whereIn('id', $supplierIds)
                ->pluck('code')
                ->implode(', ');

            // Ambil User Supplier penerima SEBELUM buat Log
            $userSuppliers = DB::table('users')
                ->join('user_supplier', 'users.id', '=', 'user_supplier.user_id')
                ->join('suppliers', 'user_supplier.supplier_id', '=', 'suppliers.id')
                ->whereIn('user_supplier.supplier_id', $supplierIds)
                ->whereNotNull('users.email')
                ->select(
                    'users.id', 
                    'users.email', 
                    'users.name', 
                    'suppliers.name as supplier_name', 
                    'suppliers.code as supplier_code'
                )
                ->get();

            $sharedToString = $userSuppliers->map(function($u) {
                return "[{$u->supplier_code}] {$u->name} ({$u->email})";
            })->unique()->implode(', ');

            // Format daftar penerima untuk disimpan di Log (Snapshot)
            $recipientSnapshot = $userSuppliers->map(function($u) {
                return "{$u->name} ({$u->email})";
            })->unique()->implode(', ');

            // Buat Activity Log
            if ($packageDetails) {
                $metaLogData = [
                    'part_no'         => $packageDetails->part_no,
                    'customer_code'   => $packageDetails->customer,
                    'model_name'      => $packageDetails->model,
                    'part_group_code' => $packageDetails->code_part_group ?? '-',
                    'doc_type'        => $packageDetails->doc_type,
                    'package_id'      => $packageDetails->package_id,
                    'revision_no'     => $packageDetails->revision_no,
                    'ecn_no'          => $packageDetails->ecn_no,
                    
                    // --- Custom Data Share ---
                    'shared_to'       => $sharedToString,
                    'recipients'      => $recipientSnapshot,
                    'shared_count'    => count($supplierIds),
                    'expired_at'      => $expiredAt->format('Y-m-d')
                ];

                ActivityLog::create([
                    'user_id'       => $userId,
                    'activity_code' => 'SHARE_PACKAGE',
                    'scope_type'    => 'revision',
                    'scope_id'      => $packageDetails->package_id,
                    'revision_id'   => $revisionId,
                    'meta'          => $metaLogData,
                ]);
            }

            // Persiapan Subject Email
            $part1 = trim($packageDetails->model);
            $part2 = $packageDetails->doc_type;
            $part3 = $packageDetails->category;
            $subjectParts = array_filter([$part1, $part2, $part3], fn($v) => !is_null($v) && $v !== '');
            $emailSubject = implode(' - ', $subjectParts);

            // Ambil daftar file
            $files = DB::table('doc_package_revision_files')
                ->where('revision_id', $revisionId)
                ->pluck('filename')
                ->toArray();

            // Grouping User untuk Pengiriman Email
            $usersToEmail = [];
            foreach ($userSuppliers as $us) {
                if (!isset($usersToEmail[$us->email])) {
                    $usersToEmail[$us->email] = [
                        'name' => $us->name,
                        'email' => $us->email,
                        'supplier_names' => []
                    ];
                }
                // Cegah duplikasi nama supplier di body email jika 1 user pegang banyak supplier
                if (!in_array($us->supplier_name, $usersToEmail[$us->email]['supplier_names'])) {
                    $usersToEmail[$us->email]['supplier_names'][] = $us->supplier_name;
                }
            }

            $encryptedId = Crypt::encrypt($revisionId);

            // Kirim Email
            foreach ($usersToEmail as $userData) {
                $supplierNamesStr = implode(', ', $userData['supplier_names']);
                
                Mail::to($userData['email'])->send(new ShareNotification(
                    $userData['name'],
                    $encryptedId,
                    $emailSubject,
                    $supplierNamesStr,
                    $files
                ));
            }

            DB::commit();
            return response()->json([
                'message' => 'Package shared successfully to ' . count($supplierIds) . ' suppliers.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to share package: ' . $e->getMessage()], 500);
        }
    }


    public function showDetail(string $id)
    {
        // 1. Dekripsi hash -> revisionId (int)
        if (ctype_digit($id)) {
            $revisionId = (int) $id;
        } else {
            try {
                $revisionId = decrypt($id);
            } catch (DecryptException $e) {
                abort(404, 'Invalid share ID.');
            }
        }

        // 2. Ambil data revisi
        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $revisionId)
            ->first();

        if (!$revision) {
            abort(404, 'Shared package not found.');
        }

        // --- Tambahan: data untuk stamp ---
        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $obsoleteDate = $revision->obsolete_at
            ? Carbon::parse($revision->obsolete_at)
            : null;

        $sharedAt = $revision->shared_at
            ? Carbon::parse($revision->shared_at)
            : null;

        $lastApproval = DB::table('package_approvals as pa')
            ->leftJoin('users as u', 'u.id', '=', 'pa.decided_by')
            ->leftJoin('departments as d', 'd.id', '=', 'u.id_dept')
            ->where('pa.revision_id', $revisionId)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first([
                'u.name as approver_name',
                'd.code as dept_name'
            ]);

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDate
                ? $this->formatObsoleteDate($obsoleteDate)
                : null,
            'name' => $revision->obsolete_name
                ?? optional($lastApproval)->approver_name
                ?? '-',

            'dept' => $revision->obsolete_dept
                ?? optional($lastApproval)->dept_name
                ?? '-',
        ];

        // 3. Ambil info package (customer, model, dst)
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('users as u', 'u.id', '=', 'dp.created_by')
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dtg.name as doc_type',
                'dsc.name as category',
                'pg.code_part_group as part_group',
                'dp.created_at',
                'u.name as uploader_name'
            )
            ->first();

        if (!$package) {
            abort(404, 'Package not found.');
        }

        // ==== ACTIVITY LOG (UPLOAD + APPROVE DLL) ====
        $logs = $this->buildApprovalLogs($revision->package_id, $revisionId);

        // fallback "Uploaded" kalau belum ada
        $uploadedAt     = optional($package->created_at);
        $uploaderName   = $package->uploader_name ?? 'System';
        $hasUploadedLog = $logs->contains(fn($log) => ($log['action'] ?? '') === 'uploaded');

        if ($uploadedAt && !$hasUploadedLog) {
            $logs->push([
                'id'      => 0,
                'action'  => 'uploaded',
                'user'    => $uploaderName,
                'note'    => 'Package uploaded',
                'time'    => $uploadedAt->format('Y-m-d H:i'),
                'time_ts' => $uploadedAt->timestamp,
            ]);

            // urutkan supaya newest di atas
            $logs = $logs->sortByDesc('time_ts')->values();
        }
        // ==== END ACTIVITY LOG ====

        // 4. Ambil file-file di revisi ini, dikelompokkan per kategori
        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->select(
                'id',
                'filename as name',
                'category',
                'storage_path',
                'file_size',
                'ori_position',
                'copy_position',
                'obslt_position',
                'blocks_position'
            )
            ->get();

        // daftar extension yang dipakai
        $extList = $fileRows
            ->map(fn($r) => strtolower(pathinfo($r->name, PATHINFO_EXTENSION)))
            ->filter()
            ->unique()
            ->values();

        $extUpper = $extList->map(fn($e) => strtoupper($e));

        // ambil icon dari master file_extensions
        $extIcons = $extUpper->isEmpty()
            ? []
            : FileExtensions::whereIn('code', $extUpper)
            ->get()
            ->mapWithKeys(fn(FileExtensions $m) => [strtolower($m->code) => $m->icon_src])
            ->all();

        $files = $fileRows
            ->groupBy('category')
            ->map(function ($items) use ($extIcons) {
                return $items->map(function ($item) use ($extIcons) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);

                    $ext = strtolower(pathinfo($item->name, PATHINFO_EXTENSION));
                    $iconSrc = $extIcons[$ext] ?? null;

                    return [
                        'id'             => $item->id,
                        'name'           => $item->name,
                        'url'            => $url,
                        'icon_src'       => $iconSrc,
                        'ori_position'   => $item->ori_position,
                        'copy_position'  => $item->copy_position,
                        'obslt_position' => $item->obslt_position,
                        'size'           => $item->file_size,
                        'blocks_position' => $item->blocks_position
                            ? (is_string($item->blocks_position)
                                ? (json_decode($item->blocks_position, true) ?? [])
                                : $item->blocks_position)
                            : [],
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        // 5. Daftar supplier yang pernah di-share
        $shares = collect();

        if (!empty($revision->share_to)) {
            $ids = json_decode($revision->share_to, true);

            if (is_array($ids) && count($ids) > 0) {
                $supplierRows = DB::table('suppliers as s')
                    ->whereIn('s.id', $ids)
                    ->orderBy('s.code')
                    ->get([
                        's.code as supplier_code',
                        's.name as supplier_name',
                    ]);

                $sharedAt = $revision->shared_at ?? null;

                $shares = $supplierRows->map(function ($row) use ($sharedAt) {
                    return (object) [
                        'supplier_code' => $row->supplier_code,
                        'supplier_name' => $row->supplier_name,
                        'shared_at'     => $sharedAt,
                    ];
                });
            }
        }

        // 6. Susun data untuk Blade
        $uploadedAt = optional($package->created_at);

        $detail = [
            'metadata' => [
                'revision_id' => $revisionId,
                'customer'    => $package->customer,
                'model'       => $package->model,
                'part_group'  => $package->part_group,
                'doc_type'    => $package->doc_type,
                'category'    => $package->category,
                'part_no'     => $package->part_no,
                'revision'    => 'Rev-' . $revision->revision_no,
                'ecn_no'      => $revision->ecn_no ?? null,
                'uploader'    => $package->uploader_name ?? 'System',
                'uploaded_at' => $uploadedAt ? $uploadedAt->format('Y-m-d H:i') : null,
            ],
            'files'        => $files,
            'shares'       => $shares,
            'activityLogs' => $logs,   // <<< kirim ke Blade

            'stamp'        => [
                'receipt_date'  => $receiptDate?->toDateString(),
                'upload_date'   => $uploadDateRevision?->toDateString(),
                'obsolete_date' => $obsoleteDate?->toDateString(),
                'is_obsolete'   => $isObsolete,
                'obsolete_info' => $obsoleteStampInfo,
                'shared_at'     => $sharedAt?->toDateString(),
            ],
        ];

        // hash baru untuk link (kalau mau dipakai lagi)
        $hash = encrypt($revisionId);

        $stampFormats = StampFormat::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('file_management.share_detail', [
            'shareId'       => $hash,
            'revisionId'    => $revisionId,
            'detail'        => $detail,
            'stampFormats'  => $stampFormats,
        ]);
    }


  public function updateBlocks(Request $request, $fileId): JsonResponse
{
    $file = DocPackageRevisionFile::findOrFail($fileId);

    $validated = $request->validate([
        'page'               => 'nullable|integer|min:1',
        'blocks'             => 'nullable|array',
        'blocks.*.id'        => 'nullable|string',
        'blocks.*.u'         => 'required|numeric',
        'blocks.*.v'         => 'required|numeric',
        'blocks.*.w'         => 'required|numeric',
        'blocks.*.h'         => 'required|numeric',
        'blocks.*.rotation'  => 'nullable|numeric',
    ]);

    $page      = (int)($validated['page'] ?? 1);
    $rawBlocks = $validated['blocks'] ?? [];

    // normalisasi & clamp 0..1 biar aman
    $blocks = [];
    foreach ($rawBlocks as $i => $block) {
        $blocks[] = [
            'id'       => $block['id'] ?? ('blk-' . ($i + 1)),
            'u'        => max(0, min(1, (float) $block['u'])),
            'v'        => max(0, min(1, (float) $block['v'])),
            'w'        => max(0, min(1, (float) $block['w'])),
            'h'        => max(0, min(1, (float) $block['h'])),
            'rotation' => isset($block['rotation']) ? (float) $block['rotation'] : 0.0,
        ];
    }

    // ==== MERGE DENGAN VALUE LAMA (BACKWARD COMPATIBLE) ====
    $existing = $file->blocks_position ?: [];

    // 🔴 TAMBAHAN PENTING: kalau masih string JSON → decode dulu
    if (is_string($existing)) {
        $decoded = json_decode($existing, true);
        $existing = $decoded ?? [];
    }

    // Kalau data lama masih berupa array flat (tanpa key page) → anggap halaman 1
    if (is_array($existing) && array_is_list($existing)) {
        $existing = [
            '1' => $existing,
        ];
    }

    if (!is_array($existing)) {
        $existing = [];
    }

    // Update hanya page yang sekarang
    $existing[(string) $page] = $blocks;

    // Laravel akan simpan sebagai JSON
    $file->blocks_position = $existing;
    $file->save();

    return response()->json([
        'message' => 'Blocks position saved.',
        'page'    => $page,
        'blocks'  => $blocks,
    ]);
}





    private function buildApprovalLogs(int $packageId, ?int $revisionId)
    {
        $q = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->where(function ($w) use ($packageId, $revisionId) {
                $w->where(function ($x) use ($packageId) {
                    $x->where('al.scope_type', 'package')
                        ->where('al.scope_id', $packageId);
                });

                if (!empty($revisionId)) {
                    $w->orWhere(function ($x) use ($revisionId) {
                        $x->where('al.scope_type', 'revision')
                            ->where('al.scope_id', $revisionId);
                    });

                    $w->orWhere(function ($x) use ($revisionId) {
                        $x->where('al.scope_type', 'package')
                            ->where('al.scope_id', $revisionId);
                    });
                }
            })
            ->where(function ($w) use ($revisionId) {
                if (!empty($revisionId)) {
                    $w->whereNull('al.revision_id')
                        ->orWhere('al.revision_id', $revisionId);
                } else {
                    $w->whereNull('al.revision_id');
                }
            })
            ->orderByDesc('al.created_at')
            ->orderByDesc('al.id')
            ->limit(200);

        return $q->get([
            'al.id',
            'al.activity_code',
            'al.meta',
            'al.created_at',
            'u.name as user_name'
        ])
            ->map(function ($row) {
                $code = strtoupper($row->activity_code ?? '');
                $action =
                    (str_starts_with($code, 'UPLOAD')   ? 'uploaded'   : (str_starts_with($code, 'APPROVE')  ? 'approved'   : (str_starts_with($code, 'REJECT')   ? 'rejected'   : (str_starts_with($code, 'ROLLBACK') ? 'rollbacked' :
                                    strtolower($code ?: 'info')))));

                $meta = $row->meta;
                if (is_string($meta)) {
                    try {
                        $meta = json_decode($meta, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable) {
                        $meta = null;
                    }
                }

                return [
                    'id'      => (int) $row->id,
                    'action'  => $action,
                    'user'    => $row->user_name ?? 'System',
                    'note'    => is_array($meta) ? ($meta['note'] ?? '') : ($meta ?: ''),
                    'time'    => optional($row->created_at)->format('Y-m-d H:i'),
                    'time_ts' => optional($row->created_at)?->timestamp ?? 0,
                ];
            });
    }


    private function formatObsoleteDate(Carbon $date): string
    {
        $month = $date->format('M');       // "Oct"
        $day   = (int) $date->format('j'); // 25
        $year  = $date->format('Y');       // 2025

        if (in_array($day % 100, [11, 12, 13], true)) {
            $suffix = 'th';
        } else {
            $last = $day % 10;
            $suffix = match ($last) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return sprintf('%s.%d%s %s', $month, $day, $suffix, $year);
    }
}
