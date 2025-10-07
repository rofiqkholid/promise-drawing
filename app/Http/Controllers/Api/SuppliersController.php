<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuppliersController extends Controller
{
    public function data(Request $request)
    {
        $query = Suppliers::query();

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('code', 'like', '%' . $searchValue . '%');
            });
        }
        
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $sortBy = $request->get('order')[0]['column'];
            $sortDir = $request->get('order')[0]['dir'];
            $sortColumn = $request->get('columns')[$sortBy]['data'];
            $query->orderBy($sortColumn, $sortDir);
        }

        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);
        $totalRecords = Suppliers::count();
        
        $suppliers = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:suppliers,code',
            'is_active' => 'required|boolean',
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        Suppliers::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier created successfully.']);
    }

    public function show(Suppliers $supplier)
    {
        return response()->json($supplier);
    }

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
            'is_active' => 'required|boolean',
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
    }

    public function destroy(Suppliers $supplier)
    {
        $supplier->delete();
        
        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }
}