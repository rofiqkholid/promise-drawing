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
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = PartGroups::query()
            ->with(['customer', 'model'])
            ->select('part_groups.*');

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('code_part_group', 'like', '%' . $request->search . '%')
                  ->orWhere('code_part_group_desc', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', function($q) use ($request) {
                      $q->where('code', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('model', function($q) use ($request) {
                      $q->where('code', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'code_part_group';
        if ($sortColumn == 'customer_code') {
            $query->join('customers', 'part_groups.customer_id', '=', 'customers.id')
                  ->orderBy('customers.code', $sortDir);
        } elseif ($sortColumn == 'model_code') {
            $query->join('models', 'part_groups.model_id', '=', 'models.id')
                  ->orderBy('models.code', $sortDir);
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = PartGroups::count();
        $filteredRecords = $query->count();
        $partGroups = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $partGroups->map(function ($partGroup) {
                return [
                    'id' => $partGroup->id,
                    'customer_code' => $partGroup->customer ? $partGroup->customer->code : '-',
                    'model_code' => $partGroup->model ? $partGroup->model->code : '-',
                    'code_part_group' => $partGroup->code_part_group,
                    'code_part_group_desc' => $partGroup->code_part_group_desc,
                ];
            })
        ]);
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
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required|exists:models,id',
            'code_part_group' => 'required|string|max:10|unique:part_groups,code_part_group',
            'code_part_group_desc' => 'required|string|max:50',
        ]);

        PartGroups::create($validated);

        return response()->json(['success' => true, 'message' => 'Part Group created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(PartGroups $partGroup)
    {
        $partGroup->load(['customer', 'model']);
        return response()->json([
            'id' => $partGroup->id,
            'customer_id' => $partGroup->customer_id,
            'model_id' => $partGroup->model_id,
            'code_part_group' => $partGroup->code_part_group,
            'code_part_group_desc' => $partGroup->code_part_group_desc,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PartGroups $partGroup)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'model_id' => 'required|exists:models,id',
            'code_part_group' => [
                'required',
                'string',
                'max:10',
                Rule::unique('part_groups')->ignore($partGroup->id),
            ],
            'code_part_group_desc' => 'required|string|max:50',
        ]);

        $partGroup->update($validated);

        return response()->json(['success' => true, 'message' => 'Part Group updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PartGroups $partGroup)
    {
        $partGroup->delete();
        return response()->json(['success' => true, 'message' => 'Part Group deleted successfully.']);
    }

    /**
     * Get models by customer ID for dynamic dropdown.
     */
    public function getModelsByCustomer(Request $request)
    {
        $customerId = $request->query('customer_id');
        $models = Models::where('customer_id', $customerId)->select('id', 'code')->get();
        return response()->json($models);
    }
}
