<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PackageFormat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PackageFormatController extends Controller
{

    public function data(Request $request)
    {
        $query = PackageFormat::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('prefix', 'like', '%' . $request->search . '%')
                ->orWhere('suffix', 'like', '%' . $request->search . '%');
            });
        }

        $sortColumn = $request->input('columns.' . $request->input('order.0.column', 1) . '.data', 'prefix');
        $sortDir = $request->input('order.0.dir', 'asc');

        if (in_array($sortColumn, ['prefix', 'suffix', 'is_active'])) {
            $query->orderBy($sortColumn, $sortDir);
        }

        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);

        $totalRecords = PackageFormat::count();
        $filteredRecords = $query->count();
        $pkgFormats = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => (int)$request->get('draw', 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $pkgFormats
        ]);
    }

   
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'suffix' => 'required|string|max:50|unique:stamp_formats,suffix',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        PackageFormat::create($validated);

        return response()->json(['success' => true, 'message' => 'Package Format created successfully.']);
    }

  
    public function show(PackageFormat $pkgFormat)
    {
        return response()->json($pkgFormat);
    }

  
    public function update(Request $request, PackageFormat $pkgFormat)
    {
        $validated = $request->validate([
            'prefix' => 'required|string|max:50',
            'suffix' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pkg_formats')->ignore($pkgFormat->id),
            ],
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $pkgFormat->update($validated);

        return response()->json(['success' => true, 'message' => 'Package Format updated successfully.']);
    }

   
    public function destroy(PackageFormat $pkgFormat)
    {
        $pkgFormat->delete();
        return response()->json(['success' => true, 'message' => 'Package Format deleted successfully.']);
    }
}
