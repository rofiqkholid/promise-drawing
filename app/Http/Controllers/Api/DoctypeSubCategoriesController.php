<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocTypeSubCategories;
use App\Models\DocTypeGroups;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocTypeSubCategoriesController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = DocTypeSubCategories::with('docTypeGroup');

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('docTypeGroup', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'name';
        if ($sortColumn === 'doc_type_group') {
            $query->join('doctype_groups', 'doctype_subcategories.doctype_group_id', '=', 'doctype_groups.id')
                  ->orderBy('doctype_groups.name', $sortDir)
                  ->select('doctype_subcategories.*');
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = DocTypeSubCategories::count();
        $filteredRecords = $query->count();
        $docTypeSubCategories = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $docTypeSubCategories
        ]);
    }

    /**
     * Get all document type groups for dropdown.
     */
    public function getDocTypeGroups()
    {
        $docTypeGroups = DocTypeGroups::select('id', 'name')->get();
        return response()->json($docTypeGroups);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name' => 'required|string|max:50',
        ]);

        DocTypeSubCategories::create($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Subcategory created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(DocTypeSubCategories $docTypeSubCategory)
    {
        return response()->json($docTypeSubCategory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocTypeSubCategories $docTypeSubCategory)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $docTypeSubCategory->update($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Subcategory updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocTypeSubCategories $docTypeSubCategory)
    {
        $docTypeSubCategory->delete();
        return response()->json(['success' => true, 'message' => 'Document Type Subcategory deleted successfully.']);
    }
}
