<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\ProjectStatus;
use App\Models\Models;
use Illuminate\Http\Request;

class ModelsController extends Controller
{
    public function data(Request $request)
    {
        // base query + eager load
        $query = Models::with(['customer','status']);

        // ===== Search =====
        if ($request->has('search') && !empty($request->search)) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('models.name', 'like', "%{$term}%")
                  ->orWhereHas('customer', function ($q) use ($term) {
                      $q->where('customers.name', 'like', "%{$term}%")
                        ->orWhere('customers.code', 'like', "%{$term}%");
                  })
                  ->orWhereHas('status', function ($q) use ($term) {
                      $q->where('project_status.name', 'like', "%{$term}%");
                  });
            });
        }

        // (Opsional) filter status_id langsung dari dropdown
        if ($request->filled('status_id')) {
            $query->where('models.status_id', $request->status_id);
        }

        // ===== Sorting =====
        $sortBy   = (int) $request->input('order.0.column', 0);
        $sortDir  = $request->input('order.0.dir', 'asc');
        $columns  = $request->input('columns', []);

        $sortData = $columns[$sortBy]['data'] ?? 'name';
        $sortName = $columns[$sortBy]['name'] ?? null;
        $sortKey  = $sortName === 'customer' ? 'customer' : $sortData;

        // allowlist mapping supaya aman
        $sortMap = [
            'name'          => 'models.name',
            'customer'      => 'customers.code',
            'customer.code' => 'customers.code',
            'status'        => 'project_status.name',
        ];
        $sortColumn = $sortMap[$sortKey] ?? 'models.name';

        // join dinamis hanya bila perlu
        if (str_starts_with($sortColumn, 'customers.')) {
            $query->leftJoin('customers', 'models.customer_id', '=', 'customers.id')
                  ->select('models.*');
        }
        if (str_starts_with($sortColumn, 'project_status.')) {
            $query->leftJoin('project_status', 'models.status_id', '=', 'project_status.id')
                  ->select('models.*');
        }

        $query->orderBy($sortColumn, $sortDir);

        // ===== Paging =====
        $perPage = (int) $request->get('length', 10);
        $start   = (int) $request->get('start', 0);
        $draw    = (int) $request->get('draw', 1);

        $totalRecords    = Models::count();
        $filteredRecords = (clone $query)->count();
        $models          = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $models,
        ]);
    }

    public function getCustomers()
    {
        $customers = Customers::select('id','code','name')->get()->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->code.' - '.$c->name,
        ]);
        return response()->json($customers);
    }

    public function getStatus()
    {
        return response()->json(
            ProjectStatus::select('id','name')->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name'        => 'required|string|max:50',
            'status_id'   => 'required|integer|exists:project_status,id',
        ]);

        Models::create($validated);
        return response()->json(['success' => true, 'message' => 'Model created successfully.']);
    }

    public function show(Models $model)
    {
        // kalau mau kirim relasi juga:
        $model->load(['customer','status']);
        return response()->json($model);
    }

    public function update(Request $request, Models $model)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name'        => 'required|string|max:50',
            'status_id'   => 'required|integer|exists:project_status,id', // konsisten dg store
        ]);

        $model->update($validated);
        return response()->json(['success' => true, 'message' => 'Model updated successfully.']);
    }

    public function destroy(Models $model)
    {
        $model->delete();
        return response()->json(['success' => true, 'message' => 'Model deleted successfully.']);
    }
}
