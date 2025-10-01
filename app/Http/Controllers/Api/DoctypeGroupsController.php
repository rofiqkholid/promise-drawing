<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocTypeGroups;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocTypeGroupsController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = DocTypeGroups::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
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

        $totalRecords = DocTypeGroups::count();
        $filteredRecords = $query->count();
        $docTypeGroups = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $docTypeGroups
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('master.docTgroup');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:doctype_groups,name',
        ]);

        DocTypeGroups::create($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Group created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(DocTypeGroups $docTypeGroup)
    {
        return response()->json($docTypeGroup);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocTypeGroups $docTypeGroup)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('doctype_groups')->ignore($docTypeGroup->id),
            ],
        ]);

        $docTypeGroup->update($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Group updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocTypeGroups $docTypeGroup)
    {
        $docTypeGroup->delete();
        return response()->json(['success' => true, 'message' => 'Document Type Group deleted successfully.']);
    }
}
