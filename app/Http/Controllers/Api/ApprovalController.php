<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;

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
            $statusMapping = ['Waiting' => 'pending', 'Approved' => 'approved', 'Reject' => 'rejected'];
            if (isset($statusMapping[$req->status])) {
                $q->where('dpr.revision_status', $statusMapping[$req->status]);
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
            $field   = $request->get('select2');   // 'customer' | 'model' | 'doc_type' | 'category' | 'status'
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            // dependent params
            $customerCode = $request->get('customer_code'); // untuk model
            $docTypeName  = $request->get('doc_type');      // untuk category

            $total = 0;
            $items = collect();

            switch ($field) {
                case 'customer':
                    // id = code, text = code
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
                        // Abaikan filter customer kalau 'All' atau kosong
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
                        // Abaikan filter doc type kalau 'All' atau kosong
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
                        ['id' => 'Reject',   'text' => 'Reject'],
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

            // === Selalu prepend "All" pada halaman pertama ===
            // (supaya "All" selalu tersedia di dropdown tanpa harus diketik)
            if ($page === 1) {
                $items = collect([['id' => 'All', 'text' => 'All']])->merge($items);
            }

            // Hitung flag "more" untuk pagination (perhatikan item "All" di page 1)
            $effectiveTotal = $total + ($page === 1 ? 1 : 0); // tambahkan 1 untuk "All" di halaman 1
            $more = ($effectiveTotal > $page * $perPage);

            return response()->json([
                'results'    => array_values($items->toArray()),
                'pagination' => ['more' => $more]
            ]);
        }

        // ====== MODE LAMA (non-Sp
        // resolve customer
        $customerId = $request->integer('customer_id') ?: null;
        if (!$customerId && $request->filled('customer_code')) {
            $customerId = Customers::where('code', $request->get('customer_code'))->value('id');
        }

        // models
        $models = $customerId
            ? Models::where('customer_id', $customerId)->orderBy('name')->get(['id', 'name'])
            : Models::orderBy('name')->get(['id', 'name']);

        // doc types
        $docTypes = DoctypeGroups::orderBy('name')->get(['id', 'name']);

        // resolve doc_type (by name) -> id
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
            'models'     => $models,        // {id, name}
            'doc_types'  => $docTypes,      // {id, name}
            'categories' => $categories,    // {name}
            'statuses'   => collect([
                ['name' => 'Waiting'],
                ['name' => 'Approved'],
                ['name' => 'Reject'],
            ]),
        ]);
    }
    public function listApprovals(Request $request): JsonResponse
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';
        $orderColumnIndex = $request->get('order')[0]['column'] ?? 0;
        $orderColumnName = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.created_at';
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        $query = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('dpr.revision_status', '<>', 'draft');

        $recordsTotal = $query->count();

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
            $statusMapping = ['Waiting' => 'pending', 'Approved' => 'approved', 'Reject' => 'rejected'];
            if (isset($statusMapping[$request->status])) {
                $query->where('dpr.revision_status', $statusMapping[$request->status]);
            }
        }

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = $query->count();

        $data = $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'dtg.name as doc_type',
            'dsc.name as category',
            'p.part_no',
            'dpr.revision_no as revision',
            DB::raw("CASE dpr.revision_status WHEN 'pending' THEN 'Waiting' WHEN 'approved' THEN 'Approved' WHEN 'rejected' THEN 'Reject' ELSE dpr.revision_status END as status"),
            'dpr.created_at'
        )
            ->orderBy('dpr.created_at', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }


    public function showDetail($id)
    {
        // $id = revision_id
        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $id)
            ->first();

        if (!$revision) {
            abort(404, 'Approval request not found.');
        }

        // Metadata Paket
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->where('dp.id', $revision->package_id)
            ->select('c.code as customer', 'm.name as model', 'p.part_no')
            ->first();

        // Files per category (lowercase key) -> URL lewat preview controller (signed)
        $files = DB::table('doc_package_revision_files')
            ->where('revision_id', $id)
            ->select('id', 'filename as name', 'category', 'storage_path')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);
                    return ['name' => $item->name, 'url' => $url];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        // Activity Log: hanya UPLOAD untuk package ini (opsional filter per revisi)
        $logs = $this->buildApprovalLogs($revision->package_id, $revision->id);

$detail = [
    'metadata' => [
        'customer' => $package->customer,
        'model'    => $package->model,
        'part_no'  => $package->part_no,
        'revision' => 'Rev-' . $revision->revision_no,
    ],
    'status'       => match ($revision->revision_status) {
        'pending'  => 'Waiting',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        default    => ucfirst($revision->revision_status ?? 'Waiting'),
    },
    'files'        => $files,
    'activityLogs' => $logs,
];

return view('approvals.approval_detail', [
    'approvalId' => $id,
    'detail'     => $detail,
]);
    }


    public function approve(Request $request, $revision_id)
    {
        $userId = Auth::user()->id;

        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')
                ->where('id', $revision_id)->lockForUpdate()->first();

            if (!$revision || $revision->revision_status !== 'pending') {
                return response()->json(['message' => 'Revision cannot be approved.'], 422);
            }

            DB::table('doc_package_revisions')->where('id', $revision_id)->update([
                'revision_status' => 'approved',
                'updated_at'      => Carbon::now(),
            ]);

            DB::table('doc_packages')->where('id', $revision->package_id)->update([
                'current_revision_id' => $revision->id,
                'current_revision_no' => $revision->revision_no,
                'updated_at'          => Carbon::now(),
            ]);

            DB::table('doc_package_revisions')
                ->where('package_id', $revision->package_id)
                ->where('id', '!=', $revision_id)
                ->update(['is_obsolete' => 1]);

            DB::table('package_approvals')->where('revision_id', $revision_id)->update([
                'decided_by' => $userId ?? 1,
                'decided_at' => Carbon::now(),
                'decision'   => 'approved',
            ]);

            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $revision->package_id,
                'revision_id'   => $revision_id,
                'activity_code' => ActivityLog::APPROVE,
                'user_id'       => $userId,
                'meta'          => ['note' => 'Revision approved'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Revision approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to approve revision.', 'error' => $e->getMessage()], 500);
        }
    }

    public function reject(Request $request, $revision_id)
    {
        $userId = Auth::user()->id;

        $request->validate(['note' => 'required|string|max:500']);

        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')
                ->where('id', $revision_id)->lockForUpdate()->first();

            if (!$revision || $revision->revision_status !== 'pending') {
                return response()->json(['message' => 'Revision cannot be rejected.'], 422);
            }

            DB::table('doc_package_revisions')->where('id', $revision_id)->update([
                'revision_status' => 'rejected',
                'updated_at'      => Carbon::now(),
            ]);

            DB::table('package_approvals')->where('revision_id', $revision_id)->update([
                'decided_by' => $userId ?? 1,
                'decided_at' => Carbon::now(),
                'decision'   => 'rejected',
                'reason'     => $request->note,
            ]);

            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $revision->package_id,
                'revision_id'   => $revision_id,
                'activity_code' => ActivityLog::REJECT,
                'user_id'       => $userId,
                'meta'          => ['note' => $request->note],
            ]);

            DB::commit();
            return response()->json(['message' => 'Revision rejected successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reject revision.', 'error' => $e->getMessage()], 500);
        }
    }

   private function buildApprovalLogs(int $packageId, ?int $revisionId)
{
    $q = DB::table('activity_logs as al')
        ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
        ->where(function ($w) use ($packageId, $revisionId) {
            // 1) log level package (benar)
            $w->where(function ($x) use ($packageId) {
                $x->where('al.scope_type', 'package')
                  ->where('al.scope_id', $packageId);
            });

            // 2) log level revision (kalau ada yang tulis begitu)
            if (!empty($revisionId)) {
                $w->orWhere(function ($x) use ($revisionId) {
                    $x->where('al.scope_type', 'revision')
                      ->where('al.scope_id', $revisionId);
                });

                // 3) Fallback legacy (opsional): ada yang salah isi scope_id=revision_id di scope_type='package'
                $w->orWhere(function ($x) use ($revisionId) {
                    $x->where('al.scope_type', 'package')
                      ->where('al.scope_id', $revisionId);
                });
            }
        })
        // tampilkan log spesifik revisi INI atau log umum (revision_id NULL)
        ->where(function ($w) use ($revisionId) {
            if (!empty($revisionId)) {
                $w->whereNull('al.revision_id')
                  ->orWhere('al.revision_id', $revisionId);
            } else {
                $w->whereNull('al.revision_id');
            }
        })
        // urutan stabil meskipun created_at null / sama
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
            $action = str_starts_with($code, 'UPLOAD')  ? 'uploaded'
                   : (str_starts_with($code, 'APPROVE') ? 'approved'
                   : (str_starts_with($code, 'REJECT')  ? 'rejected'
                   : (str_starts_with($code, 'ROLLBACK') ? 'rollbacked'
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

public function rollback(Request $request, $revision_id): JsonResponse
{
    $userId = Auth::user()->id;

    DB::beginTransaction();
    try {
        // Kunci baris revisi agar aman
        $revision = DB::table('doc_package_revisions')
            ->where('id', $revision_id)
            ->lockForUpdate()
            ->first(['id','package_id','revision_status']);

        if (!$revision) {
            return response()->json(['message' => 'Revision not found.'], 404);
        }

        // Ubah status revisi -> pending (Waiting)
        DB::table('doc_package_revisions')
            ->where('id', $revision_id)
            ->update([
                'revision_status' => 'pending',
                'is_obsolete'     => 0,                 // biar bisa diproses lagi
                'updated_at'      => Carbon::now(),
            ]);

        // Ubah status di package_approvals -> waiting
        DB::table('package_approvals')
            ->where('revision_id', $revision_id)
            ->update([
                'decision'   => 'waiting',
                'decided_by' => null,
                'decided_at' => null,
                'reason'     => null,
                'updated_at' => Carbon::now(),
            ]);

        // Log ROLLBACK untuk Activity Log di UI
        ActivityLog::create([
            'scope_type'    => 'package',
            'scope_id'      => $revision->package_id,
            'revision_id'   => $revision_id,
            'activity_code' => 'ROLLBACK',
            'user_id'       => $userId,
            'meta'          => ['note' => 'Status set to Waiting'],
        ]);

        DB::commit();
        return response()->json(['message' => 'Status dikembalikan ke Waiting.']);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to rollback revision.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}



}
