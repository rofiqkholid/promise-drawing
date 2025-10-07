<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    // DataTables server-side
    public function data(Request $request)
{
    $draw   = (int) $request->get('draw');
    $start  = (int) $request->get('start', 0);
    $length = (int) $request->get('length', 10);
    $search = $request->input('search.value', '');

    // urut kolom: 0 No, 1 Model, 2 Part No, 3 Part Name
    $orderColIdx = (int) $request->input('order.0.column', 1);
    $orderDir    = $request->input('order.0.dir', 'asc');
    $columnsMap  = [ 0 => 'p.id', 1 => 'm.name', 2 => 'p.part_no', 3 => 'p.part_name' ];
    $orderCol    = $columnsMap[$orderColIdx] ?? 'm.name';

    $base = DB::table('products as p')
        ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
        ->select([
            'p.id','p.model_id','p.part_no','p.part_name',
            DB::raw("COALESCE(m.name,'') as model_name"), // ⬅ hanya NAME
        ]);

    $recordsTotal = (clone $base)->count();

    if ($search) {
        $base->where(function($q) use ($search){
            $q->where('p.part_no','like',"%$search%")
              ->orWhere('p.part_name','like',"%$search%")
              ->orWhere('m.name','like',"%$search%");
        });
    }

    $recordsFiltered = (clone $base)->count();

    $rows = $base->orderByRaw($orderCol.' '.($orderDir==='desc'?'desc':'asc'))
                 ->skip($start)->take($length)->get();

    $data = $rows->map(fn($r) => [
        'id'         => $r->id,
        'model_id'   => $r->model_id,
        'part_no'    => $r->part_no,
        'part_name'  => $r->part_name,
        'model_name' => $r->model_name,  // ⬅ cukup ini
    ]);

    return response()->json([
        'draw' => $draw,
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsFiltered,
        'data' => $data,
    ]);
}


    // Dropdown model untuk Select2
    public function getModels()
{
    return Models::select('id', 'name')
        ->orderBy('name')
        ->get();
}

    // ========== Resource minimal ==========
    public function index() { /* tidak dipakai (view via closure) */ }

    public function store(Request $r)
    {
        $v = $r->validate([
            'model_id'  => ['required','exists:models,id'],
            'part_no'   => ['required','string','max:20'],
            'part_name' => ['required','string','max:50'],
        ]);
        Product::create($v);
        return response()->json(['success'=>true]);
    }

    public function show($id)
    {
        return Product::findOrFail($id, ['id','model_id','part_no','part_name']);
    }

    public function update(Request $r, $id)
    {
        $v = $r->validate([
            'model_id'  => ['required','exists:models,id'],
            'part_no'   => ['required','string','max:20'],
            'part_name' => ['required','string','max:50'],
        ]);
        Product::findOrFail($id)->update($v);
        return response()->json(['success'=>true]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['success'=>true]);
    }
}
