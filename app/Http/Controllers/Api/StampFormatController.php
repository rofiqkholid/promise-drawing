<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StampFormat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StampFormatController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = StampFormat::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('prefix', 'like', '%' . $searchValue . '%')
                    ->orWhere('suffix', 'like', '%' . $searchValue . '%');

                if (strtolower($searchValue) === 'active') {
                    $q->orWhere('is_active', true);
                } elseif (strtolower($searchValue) === 'inactive') {
                    $q->orWhere('is_active', false);
                }
            });
        }

        // Handle Sorting
        $sortColumn = $request->input('columns.' . $request->input('order.0.column', 1) . '.data', 'prefix');
        $sortDir = $request->input('order.0.dir', 'asc');

        if (in_array($sortColumn, ['prefix', 'suffix', 'is_active'])) {
            $query->orderBy($sortColumn, $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);

        $totalRecords = StampFormat::count();
        $filteredRecords = $query->count();
        $stampFormats = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => (int)$request->get('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $stampFormats
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'suffix' => 'required|string|max:50|unique:stamp_formats,suffix',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        StampFormat::create($validated);

        return response()->json(['success' => true, 'message' => 'Stamp Format created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(StampFormat $stampFormat)
    {
        return response()->json($stampFormat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StampFormat $stampFormat)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'suffix' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stamp_formats')->ignore($stampFormat->id),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $stampFormat->update($validated);

        return response()->json(['success' => true, 'message' => 'Stamp Format updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StampFormat $stampFormat)
    {
        $stampFormat->delete();
        return response()->json(['success' => true, 'message' => 'Stamp Format deleted successfully.']);
    }
}
