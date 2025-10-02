<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StampFormat; // Pastikan model StampFormat sudah ada
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StampFormatController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        // Menggunakan StampFormat model
        $query = StampFormat::query();

        // Handle Search (mencari berdasarkan prefix atau suffix)
        if ($request->has('search') && !empty($request->search['value'])) {
             $searchValue = $request->search['value'];
             $query->where(function($q) use ($searchValue) {
                 $q->where('prefix', 'like', '%' . $searchValue . '%')
                   ->orWhere('suffix', 'like', '%' . $searchValue . '%');
             });
        }


        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'prefix'; // Default sort by prefix

        // Pastikan kolom yang disortir valid
        if (in_array($sortColumn, ['prefix', 'suffix', 'is_active', 'id'])) {
            $query->orderBy($sortColumn, $sortDir);
        } else {
             $query->orderBy('prefix', $sortDir);
        }

        // Handle Pagination
        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = StampFormat::count();
        $filteredRecords = $query->count();
        $stampFormats = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => (int)$draw,
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
            // Asumsi suffix harus unik, seperti 'code' pada contoh sebelumnya
            'suffix' => 'required|string|max:50|unique:stamp_formats,suffix',
            'is_active' => 'sometimes|boolean', // Diterima sebagai 0 atau 1
        ]);

        // Jika is_active tidak ada dalam request (karena checkbox tidak dicentang), atur ke 0
        $validated['is_active'] = $request->boolean('is_active', false);

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
                // Pastikan suffix unik, kecuali untuk data ini sendiri
                Rule::unique('stamp_formats')->ignore($stampFormat->id),
            ],
            'is_active' => 'sometimes|boolean', // Diterima sebagai 0 atau 1
        ]);

        // Jika is_active tidak ada dalam request (karena checkbox tidak dicentang), atur ke 0
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
