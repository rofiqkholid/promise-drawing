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

        // (Optional) filter status_id directly from dropdown
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

        // allowlist mapping for security
        $sortMap = [
            'name'          => 'models.name',
            'customer'      => 'customers.code',
            'customer.code' => 'customers.code',
            'status'        => 'project_status.name',
            'planning'      => 'models.planning',
        ];
        $sortColumn = $sortMap[$sortKey] ?? 'models.name';

        // dynamic join only when needed
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
        // important: distinct to avoid duplication from joins
        $filteredRecords = (clone $query)->distinct('models.id')->count('models.id');
        $models          = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $models,
        ]);
    }

    /*** =========================
     *  Select2 server-side (AJAX)
     *  ========================= */

    // Customer: label = code only, value = id
    public function customersSelect2(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $page    = max(1, (int)$request->get('page', 1));
        $perPage = 20;

        $builder = Customers::query()
            ->selectRaw('id, code AS text')
            ->when($q, function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code');

        $total = (clone $builder)->count();
        $items = $builder->forPage($page, $perPage)->get();

        return response()->json([
            'results'    => $items,
            'pagination' => ['more' => ($total > $page * $perPage)],
        ]);
    }

    // Status: label = name, value = id
    public function statusesSelect2(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $page    = max(1, (int)$request->get('page', 1));
        $perPage = 20;

        $builder = ProjectStatus::query()
            ->selectRaw('id, name AS text')
            ->when($q, fn($x) => $x->where('name', 'like', "%{$q}%"))
            ->orderBy('name');

        $total = (clone $builder)->count();
        $items = $builder->forPage($page, $perPage)->get();

        return response()->json([
            'results'    => $items,
            'pagination' => ['more' => ($total > $page * $perPage)],
        ]);
    }

    /*** =========================
     *  Old endpoint (optional)
     *  ========================= */

    public function getCustomers()
    {
        $customers = Customers::select('id','code')->get()->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->code, // show code only
        ]);
        return response()->json($customers);
    }

    public function getStatus()
    {
        return response()->json(
            ProjectStatus::select('id','name')->orderBy('name')->get()
        );
    }

    /*** =========================
     *  CRUD
     *  ========================= */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name'        => 'required|string|max:50',
            'status_id'   => 'required|integer|exists:project_status,id',
            'planning'    => 'required|integer|min:0',
        ]);

        Models::create($validated);
        return response()->json(['success' => true, 'message' => 'Model created successfully.']);
    }

    // Return additional fields to prefill edit modal
    public function show(Models $model)
    {
        $model->load(['customer','status']);

        return response()->json([
            'id'            => $model->id,
            'name'          => $model->name,
            'customer_id'   => $model->customer_id,
            'customer_code' => $model->customer?->code,   // Select2 label on edit (code only)
            'status_id'     => $model->status_id,
            'status_name'   => $model->status?->name,     // Select2 status label
            'planning'      => $model->planning,          // integer
        ]);
    }

    public function update(Request $request, Models $model)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name'        => 'required|string|max:50',
            'status_id'   => 'required|integer|exists:project_status,id',
            'planning'    => 'required|integer|min:0',
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
