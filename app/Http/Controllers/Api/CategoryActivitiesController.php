<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoryActivities;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryActivitiesController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = CategoryActivities::query();

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

        $totalRecords = CategoryActivities::count();
        $filteredRecords = $query->count();
        $categoryActivities = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $categoryActivities
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
            'code' => 'required|string|max:10|unique:category_activities,code',
        ]);

        CategoryActivities::create($validated);

        return response()->json(['success' => true, 'message' => 'Category Activity created successfully.']);
    }

    /**
     * Display the specified resource for editing.
     */
    public function show(CategoryActivities $categoryActivity)
    {
        return response()->json($categoryActivity);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CategoryActivities $categoryActivity)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('category_activities')->ignore($categoryActivity->id),
            ],
        ]);

        $categoryActivity->update($validated);

        return response()->json(['success' => true, 'message' => 'Category Activity updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CategoryActivities $categoryActivity)
    {
        $categoryActivity->delete();
        return response()->json(['success' => true, 'message' => 'Category Activity deleted successfully.']);
    }
}
