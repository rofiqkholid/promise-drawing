<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Models;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModelsController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Models::with('customer');

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'name';
        if ($sortColumn === 'customer') {
            $query->join('customers', 'models.customer_id', '=', 'customers.id')
                  ->orderBy('customers.code', $sortDir)
                  ->select('models.*');
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Models::count();
        $filteredRecords = $query->count();
        $models = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $models
        ]);
    }

    /**
     * Get all customers for dropdown.
     */
    public function getCustomers()
    {
        $customers = Customers::select('id', 'code', 'name')->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->code . ' - ' . $customer->name,
            ];
        });
        return response()->json($customers);
    }

    /**
     * Display a listing of the resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:models,code',
        ]);

        Models::create($validated);

        return response()->json(['success' => true, 'message' => 'Model created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(Models $model)
    {
        return response()->json($model);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Models $model)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('models')->ignore($model->id),
            ],
        ]);

        $model->update($validated);

        return response()->json(['success' => true, 'message' => 'Model updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Models $model)
    {
        $model->delete();
        return response()->json(['success' => true, 'message' => 'Model deleted successfully.']);
    }
}
