<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departments;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentsController extends Controller
{
    public function data(Request $request)
    {
        $query = Departments::query();

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
        $totalRecords = Departments::count();

        $departments = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:departments,code',
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        Departments::create($validated);

        return response()->json(['success' => true, 'message' => 'Department created successfully.']);
    }

    public function show(Departments $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Departments $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('departments')->ignore($department->id),
            ],
        ], [
            'code.unique' => 'The code has already been taken.',
        ]);

        $department->update($validated);

        return response()->json(['success' => true, 'message' => 'Department updated successfully.']);
    }

    public function destroy(Departments $department)
    {
        $department->delete();

        return response()->json(['success' => true, 'message' => 'Department deleted successfully.']);
    }
}
