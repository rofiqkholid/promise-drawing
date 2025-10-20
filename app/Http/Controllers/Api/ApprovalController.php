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
use App\Models\DocPackageRevisionFile;

class ApprovalController extends Controller
{
    public function filters(Request $request): JsonResponse
    {
        $customerId = $request->get('customer_id');

        $models = $customerId
            ? Models::where('customer_id', $customerId)->get(['id', 'code as code'])
            : collect();

        return response()->json([
            'customers'  => Customers::get(['id', 'code as code']),
            'models'     => $models,
            'doc_types'  => DoctypeGroups::get(['id', 'name as name']),
            'categories' => DocPackageRevisionFile::distinct()->get(['category as name']),
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
            'c.name as customer',
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
                // PERUBAHAN UTAMA: Menggunakan auth()->user()->id untuk mengambil integer ID
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
