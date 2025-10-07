<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Suppliers::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'name';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Suppliers::count();
        $filteredRecords = $query->count();
        $suppliers = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:suppliers,code',
            'email' => 'required|email|max:50',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        Suppliers::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(Suppliers $supplier)
    {
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suppliers $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('suppliers')->ignore($supplier->id),
            ],
            'email' => 'required|email|max:50',
            'phone' => 'required|string|max:15',
            'address' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suppliers $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }
}
