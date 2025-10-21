<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;

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

    // === FILTER (samakan dengan listApprovals) ===
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
        // catatan: list memakai dsc.name (doctype_subcategories)
        $q->where('dsc.name', $req->category);
    }
    if ($req->filled('status') && $req->status !== 'All') {
        $statusMapping = ['Waiting' => 'pending', 'Complete' => 'approved', 'Reject' => 'rejected'];
        if (isset($statusMapping[$req->status])) {
            $q->where('dpr.revision_status', $statusMapping[$req->status]);
        }
    }

    // === AGREGASI ===
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
        'cards' => compact('total','waiting','approved','rejected'),
        'metrics' => [
            'approval_rate'  => $total ? round($approved * 100 / $total, 2) : 0.0,
            'rejection_rate' => $total ? round($rejected * 100 / $total, 2) : 0.0,
            'wip_rate'       => $total ? round($waiting  * 100 / $total, 2) : 0.0,
        ],
    ]);
}


   public function filters(Request $request): JsonResponse
{
    // === resolve customer ===
    $customerId = $request->integer('customer_id') ?: null;
    if (!$customerId && $request->filled('customer_code')) {
        $customerId = Customers::where('code', $request->get('customer_code'))->value('id');
    }

    // === models ===
    $models = $customerId
        ? Models::where('customer_id', $customerId)->orderBy('name')->get(['id','name'])
        : Models::orderBy('name')->get(['id','name']);

    // === doc types ===
    $docTypes = DoctypeGroups::orderBy('name')->get(['id','name']);

    // === resolve doc type (by name) → id, lalu ambil subcategories ===
    $docTypeName = $request->get('doc_type'); // frontend kirim value=nama
    $docTypeId   = null;
    if ($docTypeName && $docTypeName !== 'All') {
        $docTypeId = DoctypeGroups::where('name', $docTypeName)->value('id');
    }

    $categories = DocTypeSubCategories::when($docTypeId, function($q) use ($docTypeId) {
                        $q->where('doctype_group_id', $docTypeId);
                    })
                    ->orderBy('name')
                    ->get(['name']); // penting: kolom 'name'

    return response()->json([
        'customers'  => Customers::orderBy('code')->get(['id','code']),
        'models'     => $models,        // {id, name}
        'doc_types'  => $docTypes,      // {id, name}
        'categories' => $categories,    // {name} dari doctypesubcategories
        'statuses'   => collect([
            ['name' => 'Waiting'],
            ['name' => 'Complete'],
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
        $orderColumnName = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.id';
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
            $statusMapping = ['Waiting' => 'pending', 'Complete' => 'approved', 'Reject' => 'rejected'];
            if(isset($statusMapping[$request->status])) {
                $query->where('dpr.revision_status', $statusMapping[$request->status]);
            }
        }

        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
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
                DB::raw("CASE dpr.revision_status WHEN 'pending' THEN 'Waiting' WHEN 'approved' THEN 'Complete' WHEN 'rejected' THEN 'Reject' ELSE dpr.revision_status END as status")
            )
            ->orderBy($orderColumnName, $orderDir)
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
    // $id di sini adalah revision_id
    $revision = DB::table('doc_package_revisions as dpr')
        ->where('dpr.id', $id)
        ->first();

    if (!$revision) {
        abort(404, 'Approval request not found.');
    }

    // 1. Ambil Metadata Paket
    $package = DB::table('doc_packages as dp')
        ->join('customers as c', 'dp.customer_id', '=', 'c.id')
        ->join('models as m', 'dp.model_id', '=', 'm.id')
        ->join('products as p', 'dp.product_id', '=', 'p.id')
        ->where('dp.id', $revision->package_id)
        ->select(
            'c.code as customer',
            'm.name as model',
            'p.part_no'
        )
        ->first();

    // 2. Ambil semua file untuk revisi ini
    $files = DB::table('doc_package_revision_files')
        ->where('revision_id', $id)
        ->select('filename as name', 'category', 'storage_path as url')
        ->get()
        ->groupBy('category')
        ->map(function ($items) {
            return $items->map(function ($item) {
                return ['name' => $item->name, 'url' => $item->url];
            });
        })
        // =========================================================================
        // PERBAIKAN UTAMA: Ganti changeKeyCase dengan mapWithKeys
        // =========================================================================
        ->mapWithKeys(function ($items, $key) {
            return [strtolower($key) => $items];
        });

    // 3. Ambil Log Aktivitas
    $activityLogs = collect([
        ['action' => 'uploaded', 'user' => 'Uploader', 'note' => 'Initial upload for review', 'time' => '2025-10-20 08:41 AM'],
    ]);

    // 4. Susun semua data ke dalam satu array/objek `$detail`
    $detail = [
        'metadata' => [
            'customer' => $package->customer,
            'model' => $package->model,
            'part_no' => $package->part_no,
            'revision' => 'Rev-' . $revision->revision_no,
        ],
        'status' => ucfirst($revision->revision_status),
        'files' => $files,
        'activityLogs' => $activityLogs,
    ];

    // 5. Kirim data ke view
    return view('approvals.approval_detail', [
        'approvalId' => $id,
        'detail' => $detail,
    ]);
}

    /**
     * Proses menyetujui (approve) sebuah revisi.
     */
    public function approve(Request $request, $revision_id)
    {
        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')->where('id', $revision_id)->lockForUpdate()->first();
            if (!$revision || $revision->revision_status !== 'pending') {
                return response()->json(['message' => 'Revision cannot be approved.'], 422);
            }

            DB::table('doc_package_revisions')->where('id', $revision_id)->update([
                'revision_status' => 'approved',
                'updated_at' => Carbon::now(),
            ]);

            DB::table('doc_packages')->where('id', $revision->package_id)->update([
                'current_revision_id' => $revision->id,
                'current_revision_no' => $revision->revision_no,
                'updated_at' => Carbon::now(),
            ]);

            DB::table('doc_package_revisions')
                ->where('package_id', $revision->package_id)
                ->where('id', '!=', $revision_id)
                ->update(['is_obsolete' => 1]);

            DB::table('package_approvals')->where('revision_id', $revision_id)->update([
                'decided_by' => auth()->user()->id ?? 1,
                'decided_at' => Carbon::now(),
                'decision' => 'approved',
            ]);

            DB::commit();
            return response()->json(['message' => 'Revision approved successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to approve revision.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Proses menolak (reject) sebuah revisi.
     */
    public function reject(Request $request, $revision_id)
    {
        $request->validate(['note' => 'required|string|max:500']);
        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')->where('id', $revision_id)->lockForUpdate()->first();
            if (!$revision || $revision->revision_status !== 'pending') {
                return response()->json(['message' => 'Revision cannot be rejected.'], 422);
            }

            DB::table('doc_package_revisions')->where('id', $revision_id)->update([
                'revision_status' => 'rejected',
                'updated_at' => Carbon::now(),
            ]);

            DB::table('package_approvals')->where('revision_id', $revision_id)->update([
                // PERUBAHAN UTAMA: Menggunakan auth()->user()->id untuk mengambil integer ID
                'decided_by' => auth()->user()->id ?? 1,
                'decided_at' => Carbon::now(),
                'decision' => 'rejected',
                'reason' => $request->note,
            ]);

            DB::commit();
            return response()->json(['message' => 'Revision rejected successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reject revision.', 'error' => $e->getMessage()], 500);
        }
    }
}
