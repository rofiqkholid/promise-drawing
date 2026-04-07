<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectStatusController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = ProjectStatus::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
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

        $totalRecords = ProjectStatus::count();
        $filteredRecords = $query->count();
        $projectStatuses = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $projectStatuses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:project_status,name',
            'description' => 'nullable|string|max:100',
        ]);

        ProjectStatus::create($validated);

        return response()->json(['success' => true, 'message' => 'Project Status created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(ProjectStatus $projectStatus)
    {
        return response()->json($projectStatus);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectStatus $projectStatus)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('project_status')->ignore($projectStatus->id),
            ],
            'description' => 'nullable|string|max:100',
        ]);

        $projectStatus->update($validated);

        return response()->json(['success' => true, 'message' => 'Project Status updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectStatus $projectStatus)
    {
        $projectStatus->delete();
        return response()->json(['success' => true, 'message' => 'Project Status deleted successfully.']);
    }
}
