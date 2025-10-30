<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function listFiles(Request $request): JsonResponse
    {
        $query = DB::table('doc_package_revisions as r')
            ->leftJoin('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('customer_revision_labels as crl', 'r.revision_label_id', '=', 'crl.id')
            ->select([
                'r.id as id',
                'p.package_no as package_no',
                'c.code as customer',
                'm.name as model',
                'pr.part_no as part_no',
                'r.revision_no as revision_no',
                'r.updated_at as uploaded_at',
                'r.revision_status as status',
                'r.ecn_no as ecn_no',
                'crl.label as revision_label_name'
            ])
            ->orderBy('r.updated_at', 'desc');

        // $perPage = $request->get('length') ?: 25;

        $rows = $query->get()->map(function($row) {
            return [
                'id' => str_replace('=', '-', encrypt($row->id)),
                'package_no' => $row->package_no,
                'customer' => $row->customer ?? '-',
                'model' => $row->model ?? '-',
                'part_no' => $row->part_no ?? '-',
                'revision_no' => is_null($row->revision_no) ? '0' : (string)$row->revision_no,
                'uploaded_at' => $row->uploaded_at ? date('Y-m-d H:i:s', strtotime($row->uploaded_at)) : null,
                'status' => $row->status ?? 'draft',
                'ecn_no' => $row->ecn_no ?? '-',
                'revision_label_name' => $row->revision_label_name
            ];
        });

        return response()->json([
            'data' => $rows
        ]);
    }

    public function getPackageDetails(Request $request, $id): JsonResponse
    {
        $revisionId = (int) decrypt(str_replace('-', '=', $id));

        $rev = DB::table('doc_package_revisions as r')
            ->leftJoin('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('doctype_groups as dg', 'p.doctype_group_id', '=', 'dg.id')
            ->leftJoin('doctype_subcategories as sc', 'p.doctype_subcategory_id', '=', 'sc.id')
            ->leftJoin('part_groups as pg', 'p.part_group_id', '=', 'pg.id')
            ->select(
                'r.id as id', 'r.package_id as package_id', 'p.package_no as package_no',
                'r.revision_no as revision_no', 'r.revision_status as revision_status', 'r.note as revision_note',
                'r.ecn_no as ecn_no',
                'r.revision_label_id as revision_label_id',
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
}
