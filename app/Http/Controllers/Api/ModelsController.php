<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\Models;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModelsController extends Controller
{
    public function data(Request $request)
    {
        $query = Models::with('customer');

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('code', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('customer', function ($q) use ($searchValue) {
                        $q->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }
        
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $sortBy = $request->get('order')[0]['column'];
            $sortDir = $request->get('order')[0]['dir'];
            $sortColumnName = $request->get('columns')[$sortBy]['data'];
            
            if ($sortColumnName == 'customer.name') {
                 $query->join('customers', 'models.customer_id', '=', 'customers.id')
                       ->orderBy('customers.name', $sortDir)
                       ->select('models.*');
            } else {
                $query->orderBy($sortColumnName, $sortDir);
            }
        }

        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);
        $totalRecords = Models::count();
        
        $models = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $models
        ]);
    }

    public function getCustomers()
    {
        $customers = Customers::where('is_active', true)->select('id', 'code', 'name')->get()->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->code . ' - ' . $customer->name,
            ];
        });
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('models')->where(function ($query) use ($request) {
                    return $query->where('customer_id', $request->customer_id);
                }),
            ],
        ], [
            'name.unique' => 'The model name has already been taken for this customer.',
        ]);

        Models::create($validated);

        return response()->json(['success' => true, 'message' => 'Model created successfully.']);
    }

    public function show(Models $model)
    {
        return response()->json($model);
    }

    public function update(Request $request, Models $model)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('models')->where(function ($query) use ($request) {
                    return $query->where('customer_id', $request->customer_id);
                })->ignore($model->id),
            ],
        ], [
            'name.unique' => 'The model name has already been taken for this customer.',
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