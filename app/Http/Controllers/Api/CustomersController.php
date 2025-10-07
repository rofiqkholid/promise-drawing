<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomersController extends Controller
{
    public function data(Request $request)
    {
        $query = Customers::query();

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
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
        $totalRecords = Customers::count();

        $customers = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:customers,code',
            'is_active' => 'required|boolean',
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        Customers::create($validated);

        return response()->json(['success' => true, 'message' => 'Customer created successfully.']);
    }

    public function show(Customers $customer)
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customers $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('customers')->ignore($customer->id),
            ],
            'is_active' => 'required|boolean',
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        $customer->update($validated);

        return response()->json(['success' => true, 'message' => 'Customer updated successfully.']);
    }

    public function destroy(Customers $customer)
    {
        $customer->delete();

        return response()->json(['success' => true, 'message' => 'Customer deleted successfully.']);
    }
}
