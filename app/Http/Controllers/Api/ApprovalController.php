<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Exports\ApprovalSummaryExport;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use Illuminate\Support\Facades\Mail;
use App\Mail\RevisionApprovedNotification;
use Illuminate\Support\Facades\Crypt;






class ApprovalController extends Controller
{
    public function kpi(Request $req)
    {
        $q = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('dpr.revision_status', '<>', 'draft');

        if ($req->filled('customer') && $req->customer !== 'All') {
            $q->where('c.code', $req->customer);
        }
        if ($req->filled('model') && $req->model !== 'All') {
            $q->where('m.name', $req->model);
        }
        if ($req->filled('doc_type') && $req->doc_type !== 'All') {
            $q->where('dtg.name', $req->doc_type);
        }
        if ($req->filled('category') && $req->category !== 'All') {
            $q->where('dsc.name', $req->category);
        }
        if ($req->filled('status') && $req->status !== 'All') {
            // kalau ada data lama 'waiting', tetap ikut
            $statusMapping = [
                'Waiting'  => ['pending', 'waiting'],
                'Approved' => ['approved'],
                'Rejected' => ['rejected'],
            ];
            $vals = $statusMapping[$req->status] ?? [];
            if ($vals) {
                $q->whereIn('dpr.revision_status', $vals);
            }
        }

        $row = $q->selectRaw("
            COUNT(*) AS total,
            SUM(CASE WHEN dpr.revision_status = 'pending'  THEN 1 ELSE 0 END) AS waiting,
            SUM(CASE WHEN dpr.revision_status = 'approved' THEN 1 ELSE 0 END) AS approved,
            SUM(CASE WHEN dpr.revision_status = 'rejected' THEN 1 ELSE 0 END) AS rejected
        ")->first();

        $total    = (int)($row->total ?? 0);
        $waiting  = (int)($row->waiting ?? 0);
        $approved = (int)($row->approved ?? 0);
        $rejected = (int)($row->rejected ?? 0);

        return response()->json([
            'cards' => compact('total', 'waiting', 'approved', 'rejected'),
            'metrics' => [
                'approval_rate'  => $total ? round($approved * 100 / $total, 2) : 0.0,
                'rejection_rate' => $total ? round($rejected * 100 / $total, 2) : 0.0,
                'wip_rate'       => $total ? round($waiting  * 100 / $total, 2) : 0.0,
            ],
        ]);
    }

    public function filters(Request $request): JsonResponse
    {
        // ====== MODE SELECT2 (server-side) ======
        if ($request->filled('select2')) {
            $field   = $request->get('select2');
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            // dependent params
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
                        ->selectRaw('m.name AS id, m.name AS text')
                        ->when($customerCode && $customerCode !== 'All', fn($x) => $x->where('c.code', $customerCode))
                        ->when($q, fn($x) => $x->where('m.name', 'like', "%{$q}%"))
                        ->orderBy('m.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
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
                    $all = collect([
                        ['id' => 'Waiting',  'text' => 'Waiting'],
                        ['id' => 'Approved', 'text' => 'Approved'],
                        ['id' => 'Rejected', 'text' => 'Rejected'],
                    ]);
                    $filtered = $q
                        ? $all->filter(fn($r) => str_contains(strtolower($r['text']), strtolower($q)))
                        : $all;
                    $total = $filtered->count();
                    $items = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
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

        // ====== MODE LAMA (non-Select2) ======
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

    public function listApprovals(Request $request): JsonResponse
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
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft');

        $recordsTotal = (clone $query)->count();

        // Filters
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
            $statusMap = [
                'Waiting'  => ['pending', 'waiting'],
                'Approved' => ['approved'],
                'Rejected' => ['rejected'],
            ];
            $vals = $statusMap[$request->status] ?? [];
            if ($vals) {
                $placeholders = implode(',', array_fill(0, count($vals), '?'));
                $query->whereRaw(
                    "COALESCE(pa.decision, dpr.revision_status) IN ($placeholders)",
                    $vals
                );
            }
        }

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                    ->orWhere('pg.code_part_group', 'like', "%{$searchValue}%")
                    ->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%")
                    ->orWhereRaw("
                CONCAT(
                  c.code,' ',
                  m.name,' ',
                  dtg.name,' ',
                  COALESCE(dsc.name,''),' ',
                  COALESCE(pg.code_part_group,''),' ',
                  COALESCE(p.part_no,''),' ',
                  COALESCE(dpr.ecn_no,''),' ',
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
            'pg.code_part_group as part_group',
            'dpr.ecn_no as ecn_no',
            'dpr.revision_no as revision',
            'dpr.receipt_date as receipt_date',   // <--- NEW
            DB::raw("
            CASE COALESCE(pa.decision, dpr.revision_status)
                WHEN 'pending'  THEN 'Waiting'
                WHEN 'waiting'  THEN 'Waiting'
                WHEN 'approved' THEN 'Approved'
                WHEN 'rejected' THEN 'Rejected'
                ELSE COALESCE(pa.decision, dpr.revision_status)
            END as status
        "),
            'pa.requested_at as request_date',
            'pa.decided_at   as decision_date'
        );

        $orderWhitelist = [
            'dpr.created_at',
            'dpr.updated_at',
            'pa.requested_at',
            'pa.decided_at',
            'dpr.receipt_date',      // <--- NEW
            'dpr.revision_status',
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
            'pg.code_part_group',
            'dpr.ecn_no',
        ];

        $orderBy        = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'pa.requested_at';
        $orderDirection = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

        $data = $query
            ->orderBy($orderBy, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $data->map(function ($row) {
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




    public function showDetail(string $id)
    {
        // 1. Tentukan revisionId sebenarnya
        if (ctype_digit($id)) {
            // URL lama /approval/92
            $revisionId = (int) $id;
        } else {
            // URL baru /approval/{hash}
            try {
                $revisionId = decrypt($id);
            } catch (DecryptException $e) {
                abort(404, 'Invalid approval ID.');
            }
        }

        // 2. Ambil data revisi
        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $revisionId)
            ->first();

        if (!$revision) {
            abort(404, 'Approval request not found.');
        }

        // --- Tambahan: data untuk stamp ---
        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        // "Date Upload" di stamp = created_at di doc_package_revisions
        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $obsoleteDate = $revision->obsolete_at
            ? Carbon::parse($revision->obsolete_at)
            : null;

        // --- /Tambahan ---

        // 3. Ambil info package + uploader
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
                'dp.created_at',
                'u.name as uploader_name',
                'dtg.name as doc_type',
                'dsc.name as category',
                'pg.code_part_group as part_group'
            )
            ->first();


        if (!$package) {
            abort(404, 'Package not found.');
        }

        // 4. File per kategori + icon berdasarkan file extension
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
                'obslt_position'
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
            $iconSrc = $extIcons[$ext] ?? null; // bisa null kalau tidak ada di master

            return [
                'id'            => $item->id,
                'name'          => $item->name,
                'url'           => $url,
                'icon_src'      => $iconSrc,
                'ori_position'  => $item->ori_position,
                'copy_position' => $item->copy_position,
                'obslt_position'=> $item->obslt_position,
                'size'          => $item->file_size, // <--- ukuran (byte) dari DB
            ];
        });
    })
    ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);


        // 5. Activity logs dari tabel activity_logs
        $logs = $this->buildApprovalLogs($revision->package_id, $revisionId);

        // 6. Inject fallback log "uploaded" kalau belum ada
        $uploaderName   = $package->uploader_name ?? 'System';
        $uploadedAt     = optional($package->created_at);
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

            // supaya urutannya tetap terbaru di atas
            $logs = $logs->sortByDesc('time_ts')->values();
        }

        // 7. Data yang dikirim ke Blade
        $detail = [
            'metadata' => [
                'customer'    => $package->customer,
                'model'       => $package->model,
                'part_group'  => $package->part_group,
                'doc_type'    => $package->doc_type,
                'category'    => $package->category,
                'ecn_no'      => $revision->ecn_no ?? null,
                'part_no'     => $package->part_no,
                'revision'    => 'Rev-' . $revision->revision_no,
                'uploader'    => $uploaderName,
                'uploaded_at' => $uploadedAt ? $uploadedAt->format('Y-m-d H:i') : null,
            ],
            'status'       => match ($revision->revision_status) {
                'pending'  => 'Waiting',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default    => ucfirst($revision->revision_status ?? 'Waiting'),
            },
            'files'        => $files,
            'activityLogs' => $logs,

            // --- Tambahan: block khusus untuk stamp di preview 2D ---
            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'obsolete_date' => $obsoleteDate?->toDateString(),
                'is_obsolete'  => $isObsolete,
            ],
        ];

        // selalu kirim hash baru ke Blade untuk dipakai approve/reject
        $hash = encrypt($revisionId);

        // --- Tambahan: ambil format label stamp (prefix/suffix) dari master ---
        $stampFormats = StampFormat::where('is_active', true)
            ->orderBy('id')
            ->get();

        return view('approvals.approval_detail', [
            'approvalId'    => $hash,
            'detail'        => $detail,
            'stampFormats'  => $stampFormats,
        ]);
    }

    public function updateFileStampPosition(Request $request, int $fileId): JsonResponse
    {
        // 0..5 sesuai mapping posisi yang kita sepakati
        $data = $request->validate([
            'ori_position'   => 'nullable|integer|min:0|max:5',
            'copy_position'  => 'nullable|integer|min:0|max:5',
            'obslt_position' => 'nullable|integer|min:0|max:5',
        ]);

        $file = DocPackageRevisionFile::find($fileId);
        if (!$file) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        // hanya update field yang dikirim
        if (array_key_exists('ori_position', $data)) {
            $file->ori_position = $data['ori_position'];
        }
        if (array_key_exists('copy_position', $data)) {
            $file->copy_position = $data['copy_position'];
        }
        if (array_key_exists('obslt_position', $data)) {
            $file->obslt_position = $data['obslt_position'];
        }

        $file->save();

        return response()->json([
            'message' => 'Stamp positions updated.',
            'data'    => [
                'id'             => $file->id,
                'ori_position'   => $file->ori_position,
                'copy_position'  => $file->copy_position,
                'obslt_position' => $file->obslt_position,
            ],
        ]);
    }



    public function approve(Request $request, string $id)
    {
        $userId = Auth::user()->id ?? 1;

        try {
            $revisionId = decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid revision.'], 404);
        }

        try {
            DB::beginTransaction();

            $revision = DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->lockForUpdate()
                ->first(['id', 'package_id', 'revision_no', 'revision_status']);

            if (!$revision) {
                DB::rollBack();
                return response()->json(['message' => 'Revision not found.'], 404);
            }
            if ($revision->revision_status === 'approved') {
                DB::rollBack();
                return response()->json(['message' => 'Revision already approved.'], 200);
            }
            if ($revision->revision_status !== 'pending') {
                DB::rollBack();
                return response()->json(['message' => 'Revision cannot be approved.'], 422);
            }

            $packageId = (int) $revision->package_id;

            $package = DB::table('doc_packages')
                ->where('id', $packageId)
                ->lockForUpdate()
                ->first(['current_revision_id', 'current_revision_no']);

            $currentNo = $package->current_revision_no ?? null;
            $revNo     = (int) $revision->revision_no;
            $isOlder   = !is_null($currentNo) && $revNo < (int) $currentNo;

            if (!$isOlder) {
                // obsolete-kan approved aktif lainnya
                DB::table('doc_package_revisions')
                    ->where('package_id', $packageId)
                    ->where('id', '!=', $revisionId)
                    ->where('revision_status', 'approved')
                    ->where('is_obsolete', 0)
                    ->update([
                        'is_obsolete' => 1,
                        'obsolete_at'  => Carbon::now(),
                        'updated_at'  => Carbon::now(),
                    ]);

                DB::table('doc_package_revisions')
                    ->where('id', $revisionId)
                    ->update([
                        'revision_status' => 'approved',
                        'is_obsolete'     => 0,
                        'obsolete_at'     => null,
                        'updated_at'      => Carbon::now(),
                    ]);


                DB::table('doc_packages')
                    ->where('id', $packageId)
                    ->update([
                        'current_revision_id' => $revision->id,
                        'current_revision_no' => $revision->revision_no,
                        'updated_at'          => Carbon::now(),
                    ]);
            } else {
                DB::table('doc_package_revisions')
                    ->where('id', $revisionId)
                    ->update([
                        'revision_status' => 'approved',
                        'is_obsolete'     => 1,
                        'obsolete_at'     => Carbon::now(),
                        'updated_at'      => Carbon::now(),
                    ]);
            }

            DB::table('package_approvals')
                ->where('revision_id', $revisionId)
                ->update([
                    'decided_by' => $userId,
                    'decided_at' => Carbon::now(),
                    'decision'   => 'approved',
                    'updated_at' => Carbon::now(),
                ]);

            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $packageId,
                'revision_id'   => $revisionId,
                'activity_code' => ActivityLog::APPROVE,
                'user_id'       => $userId,
                'meta'          => ['note' => 'Revision approved'],
            ]);

            $packageInfo = DB::table('doc_packages as dp')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
                ->where('dp.id', $packageId)
                ->select('dp.id', 'c.code as customer', 'm.name as model', 'p.part_no', 'dtg.name as doc_type', 'dsc.name as category')
                ->first();

            $filenames = DB::table('doc_package_revision_files')
                ->where('revision_id', $revisionId)
                ->orderBy('id')
                ->pluck('filename')
                ->toArray();

            $rawToken = encrypt((string) $revision->id);
            $token    = str_replace('=', '-', $rawToken);

            $downloadUrl = route('file-manager.export.detail', ['id' => $token]);

            $approvalData = [
                'revision_id'   => $revision->id,
                'revision_no'   => $revision->revision_no,
                'customer'      => $packageInfo->customer ?? '-',
                'model'         => $packageInfo->model ?? '-',
                'part_no'       => $packageInfo->part_no ?? '-',
                'doc_type'      => $packageInfo->doc_type ?? '-',
                'category'      => $packageInfo->category ?? '-',
                'approved_by'   => Auth::user()->name ?? 'System',
                'approved_at'   => now()->format('Y-m-d H:i'),
                'decision_date' => now()->format('Y-m-d H:i'),
                'comment'       => '',
                'filenames'     => $filenames,
                'download_url'  => $downloadUrl,
            ];

            try {
                $users = User::select('users.*')
                    ->distinct()
                    ->leftJoin('user_roles', 'user_roles.user_id', '=', 'users.id')
                    ->whereNotNull('users.email')
                    ->where(function ($q) {
                        $q->whereNull('user_roles.role_id')
                            ->orWhere('user_roles.role_id', '!=', 5);
                    })
                    ->get();

                foreach ($users as $user) {
                    Mail::to($user->email)->send(
                        new RevisionApprovedNotification($user, $approvalData)
                    );
                }
            } catch (\Throwable $mailEx) {
                if (!$request->boolean('confirm_without_email')) {
                    DB::rollBack();
                    return response()->json([
                        'message'            => 'Email delivery failed. Do you want to approve without sending emails?',
                        'error'              => $mailEx->getMessage(),
                        'needs_confirmation' => true,
                        'code'               => 'EMAIL_FAILED',
                    ], 409);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Revision approved successfully!']);
        } catch (QueryException $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $isUniqueViolation =
                str_contains($msg, '2627') ||
                str_contains($msg, '2601') ||
                str_contains($msg, 'IX_doc_package_revisions_one_active_approved');

            if ($isUniqueViolation) {
                return response()->json([
                    'message' => 'Revision has already been approved by someone else.',
                ], 409);
            }

            return response()->json([
                'message' => 'Failed to approve revision.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve revision.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function reject(Request $request, string $id)
    {

        $userId = Auth::user()->id ?? 1;

        // decrypt hash -> revisionId (int)
        try {
            $revisionId = decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid revision.'], 404);
        }

        $request->validate(['note' => 'required|string|max:500']);

        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->lockForUpdate()
                ->first();

            if (!$revision || $revision->revision_status !== 'pending') {
                DB::rollBack();
                return response()->json(['message' => 'Revision cannot be rejected.'], 422);
            }

            DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->update([
                    'revision_status' => 'rejected',
                    'updated_at'      => Carbon::now(),
                ]);

            DB::table('package_approvals')
                ->where('revision_id', $revisionId)
                ->update([
                    'decided_by' => $userId ?? 1,
                    'decided_at' => Carbon::now(),
                    'decision'   => 'rejected',
                    'reason'     => $request->note,
                ]);

            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $revision->package_id,
                'revision_id'   => $revisionId,
                'activity_code' => ActivityLog::REJECT,
                'user_id'       => $userId,
                'meta'          => ['note' => $request->note],
            ]);

            DB::commit();
            return response()->json(['message' => 'Revision rejected successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject revision.',
                'error'   => $e->getMessage()
            ], 500);
        }
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
                $action = str_starts_with($code, 'UPLOAD')   ? 'uploaded'
                    : (str_starts_with($code, 'APPROVE')    ? 'approved'
                        : (str_starts_with($code, 'REJECT')     ? 'rejected'
                            : (str_starts_with($code, 'ROLLBACK')   ? 'rollbacked'
                                : strtolower($code ?: 'info'))));

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

    public function rollback(Request $request, string $id): JsonResponse
    {
        $userId = Auth::user()->id ?? 1;

        // decrypt hash -> revisionId (int)
        try {
            $revisionId = decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid revision.'], 404);
        }

        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->lockForUpdate()
                ->first(['id', 'package_id', 'revision_no', 'revision_status', 'is_obsolete']);

            if (!$revision) {
                DB::rollBack();
                return response()->json(['message' => 'Revision not found.'], 404);
            }

            if (!in_array($revision->revision_status, ['approved', 'rejected'], true)) {
                DB::rollBack();
                return response()->json(['message' => 'Revision cannot be rolled back.'], 422);
            }

            $packageId = (int) $revision->package_id;

            // flag: ini revisi approved & aktif (bukan obsolete)?
            $wasActiveApproved = (
                $revision->revision_status === 'approved'
                && (int) $revision->is_obsolete === 0
            );

            // 1. selalu ubah revisi ini jadi pending + obsolete
            DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->update([
                    'revision_status' => 'pending',
                    'is_obsolete'     => 1,
                    'obsolete_at'     => Carbon::now(),   // <---
                    'updated_at'      => Carbon::now(),
                ]);


            // 2. HANYA kalau sebelumnya dia approved & aktif,
            //    baru kita aktifkan approved sebelumnya
            if ($wasActiveApproved) {
                $prev = DB::table('doc_package_revisions as r')
                    ->join('package_approvals as pa', 'pa.revision_id', '=', 'r.id')
                    ->where('r.package_id', $packageId)
                    ->where('r.id', '<>', $revisionId)
                    ->where('r.revision_status', 'approved')
                    ->orderByDesc('pa.decided_at')
                    ->lockForUpdate()
                    ->first(['r.id', 'r.revision_no']);

                if ($prev) {
                    DB::table('doc_package_revisions')
                        ->where('id', $prev->id)
                        ->update([
                            'is_obsolete' => 0,
                            'updated_at'  => Carbon::now(),
                        ]);

                    DB::table('doc_packages')
                        ->where('id', $packageId)
                        ->update([
                            'current_revision_id' => $prev->id,
                            'current_revision_no' => $prev->revision_no,
                            'updated_at'          => Carbon::now(),
                        ]);
                } else {
                    DB::table('doc_packages')
                        ->where('id', $packageId)
                        ->update([
                            'current_revision_id' => null,
                            'current_revision_no' => 0,
                            'updated_at'          => Carbon::now(),
                        ]);
                }
            }
            // kalau bukan active approved (misal obsolete / rejected),
            // doc_packages tidak diubah sama sekali

            DB::table('package_approvals')
                ->where('revision_id', $revisionId)
                ->update([
                    'decision'   => 'pending',
                    'decided_by' => null,
                    'decided_at' => null,
                    'reason'     => null,
                    'updated_at' => Carbon::now(),
                ]);

            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $packageId,
                'revision_id'   => $revisionId,
                'activity_code' => 'ROLLBACK',
                'user_id'       => $userId,
                'meta'          => [
                    'note' => 'Set current revision to Waiting; re-activated previous Approved revision if any',
                ],
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Status has been set back to Waiting.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to rollback revision.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    public function exportSummary(Request $request)
    {
        // === base query approval terakhir ===
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
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft')
            // hanya revision yang jadi current di package
            ->whereColumn('dp.current_revision_id', 'dpr.id');

        // === filter (sama seperti list) ===
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

        // === SUMMARY: hanya yang Approved ===
        $query->whereRaw("COALESCE(pa.decision, dpr.revision_status) = 'approved'");

        // === ambil data dasar ===
        $rowsDb = $query->select(
            'c.code as customer',
            'm.name as model',
            'p.part_no',
            'p.part_name as part_name',
            'dtg.name as doctype',
            'dsc.name as category',
            'pg.code_part_group as part_group',
            'dpr.created_at as upload_date'
        )
            ->orderBy('c.code')
            ->orderBy('m.name')
            ->orderBy('p.part_no')
            ->get();

        $generatedAt = Carbon::now(); // waktu file summary dibuat

        // === bentuk rows untuk export (tanpa kolom Downloaded At) ===
        $rows = [];
        foreach ($rowsDb as $r) {
            $uploadDate = $r->upload_date
                ? Carbon::parse($r->upload_date)->format('Y-m-d')
                : '';

            $rows[] = [
                $r->customer,
                $r->model,
                $r->part_no,
                $r->part_name,
                $r->doctype,
                $r->category,
                $r->part_group,
                $uploadDate, // Upload Date
            ];
        }

        // kirim juga tanggal download ke export untuk ditaruh di judul
        $export   = new ApprovalSummaryExport($rows, $generatedAt->format('Y-m-d H:i'));
        $filename = 'approval-summary-' . $generatedAt->format('Ymd_His') . '.xlsx';

        return Excel::download($export, $filename);
    }
}
