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
        $base = PartGroups::query()
            ->with([
                'customer:id,code,name',
                'model:id,name,status_id',
                'model.status:id,name'
            ])
            ->select('part_groups.*');

        // Global Search
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
                    })
                    ->orWhereHas('model.status', function ($sq) use ($search) { // search status too
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $filteredQuery = clone $base;

        // Sorting
        $sortBy  = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortCol = $request->get('columns')[$sortBy]['data'] ?? 'code_part_group';

        $dataQuery = clone $base;

        if ($sortCol === 'customer_code') {
            $dataQuery->leftJoin('customers', 'part_groups.customer_id', '=', 'customers.id')
                ->orderBy('customers.code', $sortDir)
                ->select('part_groups.*');
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

        $totalRecords    = PartGroups::count();
        $recordsFiltered = (clone $filteredQuery)->count();
        $rows            = $dataQuery->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows->map(function ($pg) {
                $modelName  = optional($pg->model)->name ?? '-';
                $statusName = optional(optional($pg->model)->status)->name ?? 'No Status';

                return [
                    'id'                  => $pg->id,
                    'customer_code'       => optional($pg->customer)->code ?? '-',
                    'model_name'          => $modelName,    // ⬅ model name only
                    'model_status'        => $statusName,   // ⬅ status separated
                    'code_part_group'     => $pg->code_part_group,
                    'planning'            => $pg->planning,
                    'code_part_group_desc' => $pg->code_part_group_desc,
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
            'planning'           => 'required|integer',
            'code_part_group'    => 'required|string|max:10',
            'code_part_group_desc' => 'nullable|string|max:50',
        ]);

        PartGroups::create($validated);

        return response()->json(['success' => true, 'message' => 'Part Group created successfully.']);
    }

    /**
     * Show (for edit modal) — also send label so Select2 displays text directly.
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
            'planning'           => 'required|integer',
            'code_part_group'    => ['required', 'string', 'max:50'],
            'code_part_group_desc' => 'nullable|string|max:50',
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
     * (Old optional) dependent dropdown.
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

        $builder = Customers::query()->select('id', 'code');
        if ($q !== '') {
            $builder->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%");
            });
        }

        $total = (clone $builder)->count();
        $rows  = $builder->orderBy('code')->skip($skip)->take($per)->get();

        return response()->json([
            'results'    => $rows->map(fn($c) => [
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
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);

        $customerId = (int) $request->customer_id;
        $q     = trim((string) $request->get('q', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $per   = 10;
        $skip  = ($page - 1) * $per;

        $builder = Models::query()
            ->where('customer_id', $customerId)
            ->with('status:id,name') 
            ->select('id', 'name', 'status_id');

        if ($q !== '') {
            $builder->where('name', 'like', "%{$q}%");
        }

        $total = (clone $builder)->count();
        $rows  = $builder->orderBy('name')->skip($skip)->take($per)->get();

        return response()->json([
            'results'    => $rows->map(function ($m) {
                $status = $m->status->name ?? 'No Status';

                return [
                    'id'   => $m->id,
                    'text' => "{$m->name} - {$status}",  
                ];
            }),
            'pagination' => ['more' => ($skip + $per) < $total],
        ]);
    }
    /**
     * Store Master Code (Library).
     */
    public function storeMaster(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:part_group_master,code|max:50',
            'description' => 'nullable|string|max:50',
        ]);

        $master = \App\Models\PartGroupMaster::create([
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New Code added to Library successfully.',
            'data' => $master
        ]);
    }

    /**
     * Select2 Master Code (Library).
     */
    public function select2Master(Request $request)
    {
        $search = $request->q;
        $query = \App\Models\PartGroupMaster::query();

        if ($search) {
            $query->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $data = $query->limit(20)->get();

        $results = $data->map(function ($item) {
            return [
                'id' => $item->code,
                'text' => $item->code . ($item->description ? ' - ' . $item->description : '')
            ];
        });

        return response()->json([
            'results' => $results
        ]);
    }
    /**
     * DataTables for Master Library.
     */
    public function dataMaster(Request $request)
    {
        $query = \App\Models\PartGroupMaster::query();
        
        return \Illuminate\Support\Facades\DataTables::of($query)
        ->make(true);
    }
    
    public function dataMasterManual(Request $request) {
        $query = \App\Models\PartGroupMaster::query();

        if ($request->filled('search')) {
            $search = $request->input('search.value');
            if (is_null($search) && is_string($request->input('search'))) {
                 $search = $request->input('search');
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
        }
        
        // Sorting
        $cols = ['id', 'code', 'description']; 
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $colName = $cols[$sortBy] ?? 'code';
        
        $query->orderBy($colName, $sortDir);

        $totalRecords = \App\Models\PartGroupMaster::count();
        $filteredRecords = $query->count();
        
        $perPage = (int) $request->get('length', 10);
        $start = (int) $request->get('start', 0);
        
        $data = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => (int) $request->get('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Update Master Code.
     */
    public function updateMaster(Request $request, $id)
    {
        $master = \App\Models\PartGroupMaster::findOrFail($id);
        
        $request->validate([
            'code' => 'required|string|max:50|unique:part_group_master,code,'.$id,
            'description' => 'nullable|string|max:50',
        ]);

        $master->update([
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return response()->json(['success' => true, 'message' => 'Code updated successfully.']);
    }

    /**
     * Destroy Master Code.
     */
    public function destroyMaster($id)
    {
        $master = \App\Models\PartGroupMaster::findOrFail($id);
        $master->delete();
        
        return response()->json(['success' => true, 'message' => 'Code deleted successfully.']);
    }
}
