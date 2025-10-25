<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartGroups;
use App\Models\Customers;
use App\Models\Models;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PartGroupsController extends Controller
{
    /**
     * DataTables server-side.
     */
    public function data(Request $request)
    {
        // Base query (tanpa join), aman untuk hitung filtered count
        $base = PartGroups::query()
            ->with(['customer:id,code,name', 'model:id,name'])
            ->select('part_groups.*');

        // Global search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $base->where(function ($q) use ($search) {
                $q->where('code_part_group', 'like', "%{$search}%")
                  ->orWhere('code_part_group_desc', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('code', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('model', function ($mq) use ($search) {
                      $mq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Simpan clone untuk hitung filtered count sebelum join/order
        $filteredQuery = clone $base;

        // Sorting
        $sortBy    = $request->get('order')[0]['column'] ?? 1;
        $sortDir   = $request->get('order')[0]['dir'] ?? 'asc';
        $sortCol   = $request->get('columns')[$sortBy]['data'] ?? 'code_part_group';

        // Query yang dipakai ambil data (boleh kita modify: join + order + paginate)
        $dataQuery = clone $base;

        if ($sortCol === 'customer_code') {
            $dataQuery->leftJoin('customers', 'part_groups.customer_id', '=', 'customers.id')
                      ->orderBy('customers.code', $sortDir)
                      ->select('part_groups.*'); // pastikan kolom utama tetap part_groups.*
        } elseif ($sortCol === 'model_name') {
            $dataQuery->leftJoin('models', 'part_groups.model_id', '=', 'models.id')
                      ->orderBy('models.name', $sortDir)
                      ->select('part_groups.*');
        } else {
            $dataQuery->orderBy($sortCol, $sortDir);
        }

        // Pagination
        $perPage = (int) $request->get('length', 10);
        $start   = (int) $request->get('start', 0);
        $draw    = (int) $request->get('draw', 1);

        $totalRecords     = PartGroups::count();
        $recordsFiltered  = (clone $filteredQuery)->count(); // tanpa join: aman tidak dobel
        $rows             = $dataQuery->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows->map(function ($pg) {
                return [
                    'id'                  => $pg->id,
                    'customer_code'       => optional($pg->customer)->code ?? '-',
                    'model_name'          => optional($pg->model)->name ?? '-',
                    'code_part_group'     => $pg->code_part_group,
                    'code_part_group_desc'=> $pg->code_part_group_desc,
                ];
            }),
        ]);
    }

    /**
     * Store.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'model_id'           => 'required|exists:models,id',
            'code_part_group'    => 'required|string|max:10|unique:part_groups,code_part_group',
            'code_part_group_desc'=> 'required|string|max:50',
        ]);

        PartGroups::create($validated);

        return response()->json(['success' => true, 'message' => 'Part Group created successfully.']);
    }

    /**
     * Show (untuk modal edit) — kirim juga label supaya Select2 langsung tampilkan text.
     */
    public function show(PartGroups $partGroup)
    {
        $partGroup->load(['customer:id,code,name', 'model:id,name']);

        return response()->json([
            'id'                   => $partGroup->id,
            'customer_id'          => $partGroup->customer_id,
            'model_id'             => $partGroup->model_id,
            'customer_label'       => optional($partGroup->customer) ? ($partGroup->customer->code) : '',
            'model_label'          => optional($partGroup->model)->name ?? '',
            'code_part_group'      => $partGroup->code_part_group,
            'code_part_group_desc' => $partGroup->code_part_group_desc,
        ]);
    }

    /**
     * Update.
     */
    public function update(Request $request, PartGroups $partGroup)
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'model_id'           => 'required|exists:models,id',
            'code_part_group'    => ['required','string','max:50', Rule::unique('part_groups')->ignore($partGroup->id)],
            'code_part_group_desc'=> 'required|string|max:50',
        ]);

        $partGroup->update($validated);

        return response()->json(['success' => true, 'message' => 'Part Group updated successfully.']);
    }

    /**
     * Destroy.
     */
    public function destroy(PartGroups $partGroup)
    {
        $partGroup->delete();
        return response()->json(['success' => true, 'message' => 'Part Group deleted successfully.']);
    }

    /**
     * (Opsional lama) dropdown dependent.
     */
    public function getModelsByCustomer(Request $request)
    {
        $customerId = $request->get('customer_id');
        $models = Models::where('customer_id', $customerId)
                        ->select('id', 'name')->orderBy('name')->get();

        return response()->json($models);
    }

    /**
     * Select2 server-side: Customers
     */
    public function select2Customers(Request $request)
    {
        $q     = trim((string) $request->get('q', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $per   = 10;
        $skip  = ($page - 1) * $per;

        $builder = Customers::query()->select('id','code');
        if ($q !== '') {
            $builder->where(function ($w) use ($q) {
                $w->where('code','like',"%{$q}%")
                 ;
            });
        }

        $total = (clone $builder)->count();
        $rows  = $builder->orderBy('code')->skip($skip)->take($per)->get();

        return response()->json([
            'results'    => $rows->map(fn($c)=>[
                'id'   => $c->id,
                'text' => "{$c->code}",
            ]),
            'pagination' => ['more' => ($skip + $per) < $total],
        ]);
    }

    /**
     * Select2 server-side: Models (filter by customer_id)
     */
    public function select2Models(Request $request)
    {
        $request->validate([
            'customer_id' => ['required','integer','exists:customers,id'],
        ]);

        $customerId = (int) $request->customer_id;
        $q     = trim((string) $request->get('q', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $per   = 10;
        $skip  = ($page - 1) * $per;

        $builder = Models::query()->where('customer_id',$customerId)
                    ->select('id','name');

        if ($q !== '') {
            $builder->where('name','like',"%{$q}%");
        }

        $total = (clone $builder)->count();
        $rows  = $builder->orderBy('name')->skip($skip)->take($per)->get();

        return response()->json([
            'results'    => $rows->map(fn($m)=>[
                'id'   => $m->id,
                'text' => $m->name,
            ]),
            'pagination' => ['more' => ($skip + $per) < $total],
        ]);
    }
}
