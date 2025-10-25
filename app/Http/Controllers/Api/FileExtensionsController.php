<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileExtensions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FileExtensionsController extends Controller
{
    /**
     * Menyediakan data untuk DataTables.
     */
    public function data(Request $request)
    {
        $query = FileExtensions::query()->select([
            'file_extensions.*',
            // Gunakan subquery dengan STRING_AGG untuk mengambil kategori dari tabel pivot
            DB::raw("(SELECT STRING_AGG(cf.category_name, ', ') FROM category_file_extension cf WHERE cf.file_extension_id = file_extensions.id) as categories_list")
        ]);

        // Handle Search
        if ($request->filled('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                  ->orWhere('code', 'like', '%' . $searchValue . '%')
                  // Pencarian juga dilakukan pada hasil agregasi kategori
                  ->orWhere(DB::raw("(SELECT STRING_AGG(cf.category_name, ', ') FROM category_file_extension cf WHERE cf.file_extension_id = file_extensions.id)"), 'like', '%' . $searchValue . '%');
            });
        }

        // Handle Sorting
        $order = $request->get('order', [[1, 'asc']]);
        $orderColumnIndex = $order[0]['column'];
        $orderDir = $order[0]['dir'];
        $columns = $request->get('columns');
        $sortColumnName = $columns[$orderColumnIndex]['name'] ?? 'name';

        // Cek jika sorting berdasarkan kolom virtual 'categories_list'
        if ($sortColumnName == 'categories_list') {
            $query->orderBy(DB::raw("(SELECT STRING_AGG(cf.category_name, ', ') FROM category_file_extension cf WHERE cf.file_extension_id = file_extensions.id)"), $orderDir);
        } else {
            $query->orderBy($sortColumnName, $orderDir);
        }

        // Handle Pagination
        $totalRecords = FileExtensions::count();
        $filteredRecords = $query->count();

        $start = $request->get('start', 0);
        $perPage = $request->get('length', 10);
        $draw = $request->get('draw', 1);

        $fileExtensions = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $fileExtensions
        ]);
    }

    /**
     * Menyimpan data baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:file_extensions,code',
            'categories' => 'nullable|array',
            'categories.*' => ['required', Rule::in(['2D', '3D', 'ECN'])],
            'icon' => 'nullable|string|max:100',
            'is_viewer' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $fileExtension = FileExtensions::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'icon' => $validated['icon'],
                'is_viewer' => $request->input('is_viewer', 0),
            ]);

            if (!empty($validated['categories'])) {
                $categoriesToInsert = [];
                foreach ($validated['categories'] as $category) {
                    $categoriesToInsert[] = [
                        'file_extension_id' => $fileExtension->id,
                        'category_name' => $category,
                    ];
                }
                DB::table('category_file_extension')->insert($categoriesToInsert);
            }
        });

        return response()->json(['success' => true, 'message' => 'File Extension created successfully.']);
    }

    public function show(FileExtensions $fileExtension)
    {
        $categories = DB::table('category_file_extension')
            ->where('file_extension_id', $fileExtension->id)
            ->pluck('category_name');

        return response()->json([
            'id' => $fileExtension->id,
            'name' => $fileExtension->name,
            'code' => $fileExtension->code,
            'icon' => $fileExtension->icon,
            'is_viewer' => $fileExtension->is_viewer,
            'categories' => $categories, // Kirim daftar kategori sebagai array
        ]);
    }

    /**
     * Memperbarui data yang ada.
     */
    public function update(Request $request, FileExtensions $fileExtension)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => ['required', 'string', 'max:10', Rule::unique('file_extensions')->ignore($fileExtension->id)],
            'categories' => 'nullable|array',
            'categories.*' => ['required', Rule::in(['2D', '3D', 'ECN'])],
            'icon' => 'nullable|string|max:100',
            'is_viewer' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($fileExtension, $validated, $request) {
            $fileExtension->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'icon' => $validated['icon'],
                'is_viewer' => $request->input('is_viewer', 0),
            ]);

            // Sync categories: Hapus semua yang lama, lalu masukkan yang baru
            DB::table('category_file_extension')->where('file_extension_id', $fileExtension->id)->delete();

            if (!empty($validated['categories'])) {
                $categoriesToInsert = [];
                foreach ($validated['categories'] as $category) {
                    $categoriesToInsert[] = [
                        'file_extension_id' => $fileExtension->id,
                        'category_name' => $category,
                    ];
                }
                DB::table('category_file_extension')->insert($categoriesToInsert);
            }
        });

        return response()->json(['success' => true, 'message' => 'File Extension updated successfully.']);
    }

    /**
     * Menghapus data.
     */
    public function destroy(FileExtensions $fileExtension)
    {
        // Karena ada onDelete('cascade') di migrasi,
        // data di tabel pivot akan otomatis terhapus.
        $fileExtension->delete();
        return response()->json(['success' => true, 'message' => 'File Extension deleted successfully.']);
    }
}
