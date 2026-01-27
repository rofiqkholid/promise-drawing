<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UploadController extends Controller
{
    public function listFiles(Request $request): JsonResponse
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $order = $request->get('order')[0] ?? null;

        $filterCustomer = $request->get('customer');
        $filterModel = $request->get('model');
        $filterDocType = $request->get('doc_type');
        $filterCategory = $request->get('category');
        $filterStatus = $request->get('status');

        $query = DB::table('doc_package_revisions as r')
            ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('customer_revision_labels as crl', 'r.revision_label_id', '=', 'crl.id')
            ->leftJoin('doctype_groups as dg', 'p.doctype_group_id', '=', 'dg.id')
            ->leftJoin('doctype_subcategories as sc', 'p.doctype_subcategory_id', '=', 'sc.id')
            ->leftJoin('part_groups as pg', 'p.part_group_id', '=', 'pg.id')
            ->where('p.is_delete', 0)
            ->where('pr.is_delete', 0);

        // Apply Filters
        if ($filterCustomer && $filterCustomer !== 'All') {
            $query->where('c.code', $filterCustomer);
        }
        if ($filterModel && $filterModel !== 'All') {
            $query->where('m.name', $filterModel);
        }
        if ($filterDocType && $filterDocType !== 'All') {
            $query->where('dg.name', $filterDocType);
        }
        if ($filterCategory && $filterCategory !== 'All') {
            $query->where('sc.name', $filterCategory);
        }
        if ($filterStatus && $filterStatus !== 'All') {
            $query->where('r.revision_status', $filterStatus);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('p.package_no', 'like', "%{$search}%")
                  ->orWhere('c.code', 'like', "%{$search}%")
                  ->orWhere('m.name', 'like', "%{$search}%")
                  ->orWhere('pr.part_no', 'like', "%{$search}%")
                  // Search in Partners (Grouped Products)
                  ->orWhereExists(function ($sq) use ($search) {
                      $sq->select(DB::raw(1))
                         ->from('products as pp')
                         ->whereNotNull('pp.group_id')
                         ->whereColumn('pp.group_id', 'pr.group_id')
                         ->whereColumn('pp.id', '!=', 'pr.id')
                         ->where('pp.is_delete', 0)
                         ->where(function($subSq) use ($search) {
                             $subSq->where('pp.part_no', 'like', "%{$search}%")
                                   ->orWhere('pp.part_name', 'like', "%{$search}%");
                         });
                  })
                  ->orWhere('r.revision_no', 'like', "%{$search}%")
                  ->orWhere('r.ecn_no', 'like', "%{$search}%")
                  ->orWhere('crl.label', 'like', "%{$search}%")
                  ->orWhere('dg.name', 'like', "%{$search}%")
                  ->orWhere('sc.name', 'like', "%{$search}%")
                  ->orWhere('pg.code_part_group', 'like', "%{$search}%");
            });
        }

        $filteredRecords = $query->count();

        $totalRecords = DB::table('doc_package_revisions as r')
    ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
    ->join('products as pr', 'p.product_id', '=', 'pr.id')
    
    ->where('p.is_delete', 0)
    ->where('pr.is_delete', 0)
    ->count();

        if ($order) {
            $sortBy = $order['column'];
            $direction = $order['dir'];
            $columnName = $request->get('columns')[$sortBy]['name'];

            $columnMap = [
                'package_no' => 'p.package_no',
                'customer' => 'c.code',
                'model' => 'm.name',
                'part_no' => 'pr.part_no',
                'revision_no' => 'r.revision_no',
                'uploaded_at' => 'r.created_at',
                'status' => 'r.revision_status',
                'ecn_no' => 'r.ecn_no',
                'doctype_group' => 'dg.name',
                'doctype_subcategory' => 'sc.name',
                'part_group' => 'pg.code_part_group'
            ];

            $dbColumn = $columnMap[$columnName] ?? 'r.created_at';
            $query->orderBy($dbColumn, $direction);
        } else {
            $query->orderBy('r.created_at', 'desc');
        }

        $partnersSub = DB::table('products as p2')
            ->select('group_id', DB::raw("STRING_AGG(CAST(part_no AS VARCHAR(MAX)), ',') WITHIN GROUP (ORDER BY part_no) as partners"))
            ->whereNotNull('group_id')
            ->where('is_delete', 0)
            ->groupBy('group_id');

        $data = $query->skip($start)
            ->take($length)
            ->leftJoinSub($partnersSub, 'p_extra', function ($join) {
                $join->on('pr.group_id', '=', 'p_extra.group_id');
            })
            ->select([
                'r.id',
                'p.package_no',
                'c.code as customer',
                'm.name as model',
                'pr.part_no',
                'pr.id as product_id', 
                'pr.group_id',
                'p_extra.partners as partners_raw',
                'r.revision_no',
                'r.created_at as uploaded_at',
                'r.revision_status as status',
                'r.ecn_no',
                'crl.label as revision_label_name',
                'dg.name as doctype_group',
                'sc.name as doctype_subcategory',
                'pg.code_part_group as part_group'
            ])
            ->get();

        // Include KPIs with 60-second cache to maximize performance
        $kpiStats = Cache::remember('drawing_kpi_stats_global', 60, function () {
            return DB::table('doc_package_revisions as r')
                ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
                ->join('products as pr', 'p.product_id', '=', 'pr.id')
                ->where('p.is_delete', 0)
                ->where('pr.is_delete', 0)
                ->select(
                    DB::raw('COUNT(*) as totalupload'),
                    DB::raw("COUNT(CASE WHEN r.revision_status = 'draft' THEN 1 END) as totaldraft"),
                    DB::raw("COUNT(CASE WHEN r.revision_status = 'pending' THEN 1 END) as totalpending"),
                    DB::raw("COUNT(CASE WHEN r.revision_status = 'rejected' THEN 1 END) as totalrejected")
                )
                ->first();
        });

        // Transform Data
        $rows = $data->map(function($row) {
             // Handle STRING_AGG logic: exclude the current part_no from the comma-separated list
             $partnersStr = null;
             if ($row->partners_raw) {
                 $parts = explode(',', $row->partners_raw);
                 $others = array_filter($parts, fn($p) => trim($p) !== trim($row->part_no));
                 if (!empty($others)) {
                     $partnersStr = implode(',', $others);
                 }
             }

             return [
                'id' => str_replace(['+', '/', '='], ['-', '_', '~'], encrypt($row->id)),
                'package_no' => $row->package_no,
                'customer' => $row->customer ?? '-',
                'model' => $row->model ?? '-',
                'part_no' => $row->part_no ?? '-',
                'partners' => $partnersStr,
                'revision_no' => is_null($row->revision_no) ? '0' : (string)$row->revision_no,
                'uploaded_at' => $row->uploaded_at ? date('Y-m-d H:i:s', strtotime($row->uploaded_at)) : null,
                'status' => $row->status ?? 'draft',
                'ecn_no' => $row->ecn_no ?? '-',
                'revision_label_name' => $row->revision_label_name,
                'doctype_group' => $row->doctype_group ?? '-',
                'doctype_subcategory' => $row->doctype_subcategory ?? '-',
                'part_group' => $row->part_group ?? '-'
            ];
        });

        return response()->json([
            'draw' => (int) $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows,
            'kpis' => $kpiStats // New: optimized KPI retrieval
        ]);
    }

    public function getPackageDetails(Request $request, $id): JsonResponse
    {
        try {
            $revisionId = (int) decrypt(str_replace(['-', '_', '~'], ['+', '/', '='], $id));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid package ID'], 400);
        }

        $rev = DB::table('doc_package_revisions as r')
            ->leftJoin('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('doctype_groups as dg', 'p.doctype_group_id', '=', 'dg.id')
            ->leftJoin('doctype_subcategories as sc', 'p.doctype_subcategory_id', '=', 'sc.id')
            ->leftJoin('part_groups as pg', 'p.part_group_id', '=', 'pg.id')
            ->leftJoin('customer_revision_labels as crl', 'r.revision_label_id', '=', 'crl.id')
            ->select(
                'r.id as id', 'r.receipt_date', 'r.package_id as package_id', 'p.package_no as package_no',
                'r.revision_no as revision_no', 'r.revision_status as revision_status', 'r.note as revision_note',
                'r.ecn_no as ecn_no',
                'r.revision_label_id as revision_label_id',
                'crl.label as revision_label_name',
                'r.is_obsolete as is_obsolete', 'r.is_finish', 'r.created_by as revision_created_by', 'r.created_at as created_at', 'r.updated_at as updated_at',
                'p.project_status_id', 'p.created_by as package_created_by',
                'c.id as customer_id','c.name as customer_name','c.code as customer_code',
                'm.id as model_id', DB::raw("CONCAT(m.name, ' - ', ISNULL(ps.name, '')) as model_name"),
                'pr.id as product_id','pr.part_no as part_no',
                'dg.id as docgroup_id','dg.name as docgroup_name',
                'sc.id as subcategory_id','sc.name as subcategory_name',
                'pg.id as part_group_id','pg.code_part_group'
            )
            ->where('r.id', $revisionId)
            
    ->where('p.is_delete', 0)
    ->where('pr.is_delete', 0)
            ->first();

        if (!$rev) {
            return response()->json(['error' => 'Revision not found'], 404);
        }

        $agg = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->select(DB::raw('count(*) as total_files, sum(file_size) as total_bytes'))
            ->first();

        $file_list = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->get(['id', 'filename as name', 'category', 'file_size as size'])
            ->groupBy('category')->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        return response()->json([
            'package' => $rev,
            'files' => [
                'count' => (int) ($agg->total_files ?? 0),
                'size_bytes' => (int) ($agg->total_bytes ?? 0),
            ],
            'file_list' => $file_list
        ]);
    }

    public function getKpiStats(): JsonResponse
{
    $kpiStats = DB::table('doc_package_revisions as r')
        ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
        ->join('products as pr', 'p.product_id', '=', 'pr.id')
        ->where('p.is_delete', 0)
        ->where('pr.is_delete', 0)
        ->select(
            DB::raw('COUNT(*) as totalupload'),
            DB::raw("COUNT(CASE WHEN r.revision_status = 'draft' THEN 1 END) as totaldraft"),
            DB::raw("COUNT(CASE WHEN r.revision_status = 'pending' THEN 1 END) as totalpending"),
            DB::raw("COUNT(CASE WHEN r.revision_status = 'rejected' THEN 1 END) as totalrejected")
        )
        ->first();

    return response()->json($kpiStats);
}


    public function getExportDetail($id)
    {
        try {
            $revisionId = (int) decrypt(str_replace(['-', '_', '~'], ['+', '/', '='], $id));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid package ID'], 400);
        }

        $rev = DB::table('doc_package_revisions as r')
            ->leftJoin('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('doctype_groups as dg', 'p.doctype_group_id', '=', 'dg.id')
            ->leftJoin('doctype_subcategories as sc', 'p.doctype_subcategory_id', '=', 'sc.id')
            ->leftJoin('part_groups as pg', 'p.part_group_id', '=', 'pg.id')
            ->leftJoin('customer_revision_labels as crl', 'r.revision_label_id', '=', 'crl.id')
            ->select(
                'r.id as id', 'r.receipt_date', 'r.package_id as package_id', 'p.package_no as package_no',
                'r.revision_no as revision_no', 'r.revision_status as revision_status', 'r.note as revision_note',
                'r.ecn_no as ecn_no',
                'r.revision_label_id as revision_label_id',
                'crl.label as revision_label_name',
                'r.is_obsolete as is_obsolete', 'r.created_by as revision_created_by', 'r.created_at as created_at', 'r.updated_at as updated_at',
                'p.project_status_id', 'p.created_by as package_created_by',
                'c.id as customer_id','c.name as customer_name','c.code as customer_code',
                'm.id as model_id','m.name as model_name',
                'pr.id as product_id','pr.part_no as part_no',
                'dg.id as docgroup_id','dg.name as docgroup_name',
                'sc.id as subcategory_id','sc.name as subcategory_name',
                'pg.id as part_group_id','pg.code_part_group'
            )
            ->where('r.id', $revisionId)
            
    ->where('p.is_delete', 0)
    ->where('pr.is_delete', 0)
            ->first();

        if (!$rev) {
            return response()->json(['error' => 'Revision not found'], 404);
        }

        $agg = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->select(DB::raw('count(*) as total_files, sum(file_size) as total_bytes'))
            ->first();

        $file_list = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->get(['id', 'filename as name', 'category', 'file_size as size'])
            ->groupBy('category')->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $exportId = str_replace(['+', '/', '='], ['-', '_', '~'], encrypt($rev->id));
        
        $detail = [
            'metadata' => [
                'customer' => $rev->customer_code ?? '-',
                'model' => $rev->model_name ?? '-',
                'part_no' => $rev->part_no ?? '-',
                'revision' => "Rev{$rev->revision_no}" . ($rev->revision_label_name ? " ({$rev->revision_label_name})" : ''),
                'ecn_no' => $rev->ecn_no ?? '-',
                'receipt_date' => $rev->receipt_date,
                'status' => $rev->revision_status ?? 'draft',
                'is_obsolete' => $rev->is_obsolete,
                'note' => $rev->revision_note ?? '',
                'docgroup' => $rev->docgroup_name ?? '-',
                'subcategory' => $rev->subcategory_name ?? '-',
                'part_group' => $rev->code_part_group ?? '-'
            ],
            'files' => $file_list,
            'summary' => [
                'total_files' => (int) ($agg->total_files ?? 0),
                'total_bytes' => (int) ($agg->total_bytes ?? 0),
            ]
        ];

        return view('file_management.file_export_detail', compact('exportId', 'detail'));
    }
}
