<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileExtensions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FileExtensionsController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = FileExtensions::query();

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

        $totalRecords = FileExtensions::count();
        $filteredRecords = $query->count();
        $fileExtensions = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $fileExtensions
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
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:file_extensions,code',
        ]);

        FileExtensions::create($validated);

        return response()->json(['success' => true, 'message' => 'File Extension created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(FileExtensions $fileExtension)
    {
        return response()->json($fileExtension);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileExtensions $fileExtension)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('file_extensions')->ignore($fileExtension->id),
            ],
        ]);

        $fileExtension->update($validated);

        return response()->json(['success' => true, 'message' => 'File Extension updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileExtensions $fileExtension)
    {
        $fileExtension->delete();
        return response()->json(['success' => true, 'message' => 'File Extension deleted successfully.']);
    }
}
