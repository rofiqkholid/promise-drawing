<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Departments;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentsController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Departments::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
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

        $totalRecords = Departments::count();
        $filteredRecords = $query->count();
        $departments = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $departments
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     return view('master.departments');
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:departments,code',
        ]);

        Departments::create($validated);

        return response()->json(['success' => true, 'message' => 'Department created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(Departments $department)
    {
        return response()->json($department);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departments $department)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('departments')->ignore($department->id),
            ],
        ]);

        $department->update($validated);

        return response()->json(['success' => true, 'message' => 'Department updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Departments $department)
    {
        $department->delete();
        return response()->json(['success' => true, 'message' => 'Department deleted successfully.']);
    }
}
