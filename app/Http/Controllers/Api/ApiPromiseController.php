<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ApiPromiseController extends Controller
{
    public function apiPartNumProject(Request $request)
    {
        $data = DB::connection('sqlsrv')
            ->table('doc_packages as dp')

            ->join('products as p_origin', 'dp.product_id', '=', 'p_origin.id')

            ->join('products as p', function ($join) {
                $join->on('p_origin.group_id', '=', 'p.group_id')
                    ->orOn(function ($query) {
                        $query->whereNull('p_origin.group_id')
                            ->whereColumn('p.id', 'p_origin.id');
                    });
            })

            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('doctype_subcategories as ds', 'dp.doctype_subcategory_id', '=', 'ds.id')
            ->join('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->join('doc_package_revisions as dpr', 'dpr.id', '=', 'dp.current_revision_id')
            ->join('project_status as ps', 'm.status_id', '=', 'ps.id')

            ->where('dp.is_delete', 0)
            ->where('dpr.is_finish', 1)
            ->where('ds.name', 'Go Mfg')
            ->where('p.is_count', 1)
            ->where('p.is_delete', 0)
            ->whereNotNull('dp.current_revision_id')
            ->where('ps.name', 'Project')

            ->select([
                'c.code as customer_name',
                'm.name as model',
                'ps.name as project_status',
                'pg.code_part_group as part_group',
                'p.part_no',
                'dp.created_at'
            ])

            ->orderBy('dp.created_at', 'desc')
            ->orderBy('p.part_no', 'asc')
            ->limit(100)

            ->get();

        return response()->json($data);
    }
}
