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

    // urutan kolom harus match dengan Blade (lihat bagian Blade di bawah)
    // 0: rownum, 1: customer_code, 2: model_name, 3: part_no, 4: part_name
    $columnsMap  = [
        0 => 'p.id',
        1 => 'c.code',
        2 => 'm.name',
        3 => 'p.part_no',
        4 => 'p.part_name',
    ];
    $orderCol    = $columnsMap[$orderColIdx] ?? 'c.code';

    $base = DB::table('products as p')
        ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
        ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
        ->select([
            'p.id',
            'p.customer_id',
            'p.model_id',
            'p.part_no',
            'p.part_name',
            DB::raw("COALESCE(m.name,'') as model_name"),
            DB::raw("COALESCE(c.code,'') as customer_code"),
        ]);

    // search global (DataTables kirim di search.value)
    $search = $request->input('search.value', '');
    if ($search !== '') {
        $base->where(function ($q) use ($search) {
            $q->where('p.part_no', 'like', "%{$search}%")
              ->orWhere('p.part_name', 'like', "%{$search}%")
              ->orWhere('m.name', 'like', "%{$search}%")
              ->orWhere('c.code', 'like', "%{$search}%");
        });
    }

    $recordsTotal    = (clone $base)->count();
    $recordsFiltered = $recordsTotal;

    $rows = $base->orderByRaw($orderCol . ' ' . ($orderDir === 'desc' ? 'desc' : 'asc'))
        ->skip($start)->take($length)->get();

    $data = $rows->map(fn($r) => [
        'id'            => $r->id,
        'customer_id'   => $r->customer_id,
        'customer_code' => $r->customer_code,  // ⬅️ kirim ke tabel
        'model_id'      => $r->model_id,
        'model_name'    => $r->model_name,
        'part_no'       => $r->part_no,
        'part_name'     => $r->part_name,
    ]);

    return response()->json([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
    ]);
}



    // Dropdown model untuk Select2
    public function getModels(Request $request)
{
    $q = Models::select('id', 'name');

    // filter by customer_id jika dikirim
    if ($request->filled('customer_id')) {
        $q->where('customer_id', $request->customer_id);
    }

    return $q->orderBy('name')->get();
}

    // Dropdown customer untuk Select2
    public function getCustomers()

    {
        return Customers::select('id', 'code')
            ->orderBy('code')
            ->get();
    }

    public function store(Request $r)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'  => ['required', 'exists:models,id'],
            'part_no'   => ['required', 'string', 'max:20'],
            'part_name' => ['required', 'string', 'max:50'],
        ]);
        Products::create($v);
        return response()->json(['success' => true]);
    }

    public function show($id)
{
    return Products::findOrFail($id, ['id','customer_id','model_id','part_no','part_name']);
}
    public function update(Request $r, $id)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'  => ['required', 'exists:models,id'],
            'part_no'   => ['required', 'string', 'max:20'],
            'part_name' => ['required', 'string', 'max:50'],
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
