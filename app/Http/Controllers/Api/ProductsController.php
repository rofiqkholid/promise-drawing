<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Models;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function data(Request $request)
    {
        $draw   = (int) $request->get('draw');
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $orderColIdx = (int) $request->input('order.0.column', 1);
        $orderDir    = $request->input('order.0.dir', 'asc');

        $columnsMap = [
            0 => 'p.id',
            1 => 'c.code',
            2 => 'm.name',
            3 => 'p.part_no',
            4 => 'p.part_name',
            5 => 'ps.name',
        ];
        $orderCol = $columnsMap[$orderColIdx] ?? 'c.code';

        $totalBase = DB::table('products as p')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id'); 

        $recordsTotal = (clone $totalBase)->count();

        $base = $totalBase->select([
            'p.id',
            'p.customer_id',
            'p.model_id',
            'p.part_no',
            'p.part_name',
            DB::raw("COALESCE(m.name,'') as model_name"),
            DB::raw("COALESCE(c.code,'') as customer_code"),
            DB::raw("COALESCE(ps.name,'No Status') as status_name"), 
        ]);

        // global search
        $search = $request->input('search.value', '');
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('p.part_no', 'like', "%{$search}%")
                    ->orWhere('p.part_name', 'like', "%{$search}%")
                    ->orWhere('m.name', 'like', "%{$search}%")
                    ->orWhere('c.code', 'like', "%{$search}%")
                    ->orWhere('ps.name', 'like', "%{$search}%"); 
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base->orderByRaw($orderCol . ' ' . ($orderDir === 'desc' ? 'desc' : 'asc'))
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(fn($r) => [
            'id'            => $r->id,
            'customer_id'   => $r->customer_id,
            'customer_code' => $r->customer_code,
            'model_id'      => $r->model_id,
            'model_name'    => $r->model_name,
            'status'        => $r->status_name, 
            'part_no'       => $r->part_no,
            'part_name'     => $r->part_name,
        ]);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }



    public function getModels(Request $request)
    {
        $customerId = $request->get('customer_id');
        $searchTerm = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip = ($page - 1) * $limit;

        $query = DB::connection('sqlsrv')
            ->table('models as m')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->select('m.id', 'm.name', 'm.customer_id', 'ps.name as status_name');

        if (!empty($customerId)) {
            $query->where('m.customer_id', (int)$customerId);
        }

        if ($searchTerm !== '') {
            $query->where('m.name', 'LIKE', "%{$searchTerm}%");
        }

        $totalCount = $query->count();

        $results = $query->orderBy('m.name')
            ->skip($skip)
            ->take($limit)
            ->get();

        $formattedResults = $results->map(fn($model) => [
            'id'   => $model->id,
            'text' => "{$model->name} - {$model->status_name}",
            'customer_id' => $model->customer_id,
            'status' => $model->status_name,
        ]);

        return response()->json([
            'results' => $formattedResults,
            'pagination' => [
                'more' => ($page * $limit) < $totalCount
            ]
        ]);
    }


    // ===== Select2-friendly: Customers =====
    public function getCustomers(Request $request)
    {
        $q     = trim((string) $request->get('q', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip  = ($page - 1) * $limit;

        $base = Customers::query()->select('id', 'code');
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%");
            });
        }

        // Format Select2 bila dipanggil Select2
        if ($request->has('q') || $request->has('page') || $request->wantsJson()) {
            $total = (clone $base)->count();
            $rows  = $base->orderBy('code')->skip($skip)->take($limit)->get();

            return response()->json([
                'results' => $rows->map(fn($c) => [
                    'id'   => $c->id,
                    'text' => $c->code,
                ]),
                'pagination' => ['more' => ($skip + $limit) < $total],
            ]);
        }

        // Fallback "mode lama"
        return $base->orderBy('code')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'code' => $c->code]);
    }

    public function store(Request $r)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'    => ['required', 'exists:models,id'],
            'part_no'     => ['required', 'string', 'max:20'],
            'part_name'   => ['required', 'string', 'max:50'],
        ]);

        Products::create($v);
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $row = DB::table('products as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->where('p.id', $id)
            ->selectRaw("
            p.id,
            p.customer_id,
            p.model_id,
            p.part_no,
            p.part_name,
            ISNULL(c.code, '')  AS customer_label,  -- label utk Select2 (hanya code)
            ISNULL(m.name, '')  AS model_label      -- label utk Select2 (nama model)
        ")
            ->first();

        abort_if(!$row, 404);

        return response()->json($row);
    }


    public function update(Request $r, $id)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'    => ['required', 'exists:models,id'],
            'part_no'     => ['required', 'string', 'max:20'],
            'part_name'   => ['required', 'string', 'max:50'],
        ]);

        Products::findOrFail($id)->update($v);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Products::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
