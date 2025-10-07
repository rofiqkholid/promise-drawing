<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Listing untuk DataTables.
     */
    public function data(Request $request)
    {
        $query = Role::query();
        $searchTerm = $request->input('search.value',
                        $request->input('search', $request->input('q')));

        if (!empty($searchTerm)) {
            $query->where('role_name', 'like', '%' . $searchTerm . '%');
        }

        // --- Sorting (whitelist kolom agar aman)
        $allowedSorts = ['role_name', 'created_at', 'updated_at', 'id'];
        $orderReq     = $request->get('order')[0] ?? null;
        $columnsReq   = $request->get('columns', []);

        $sortByIndex  = $orderReq['column'] ?? 0;
        $sortDir      = in_array(($orderReq['dir'] ?? 'asc'), ['asc','desc']) ? $orderReq['dir'] : 'asc';
        $sortColumn   = $columnsReq[$sortByIndex]['data'] ?? 'role_name';
        $sortColumn   = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'role_name';

        $query->orderBy($sortColumn, $sortDir);

        // --- Pagination (DataTables)
        $perPage = (int) $request->input('length', 10);
        $start   = (int) $request->input('start', 0);
        $draw    = (int) $request->input('draw', 1);

        $totalRecords     = Role::count();
        $filteredRecords  = (clone $query)->count();
        $roles            = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $roles,
        ]);
    }

    /**
     * Simpel: return semua role (kalau butuh dropdown).
     * Opsional: hapus jika tidak diperlukan.
     */
    public function all()
    {
        return response()->json(Role::select('id', 'role_name')->orderBy('role_name')->get());
    }

    /**
     * Store role baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'role_name'),
            ],
        ]);

        // timestamps akan terisi otomatis jika $timestamps = true di model
        Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dibuat.',
        ]);
    }

    /**
     * Tampilkan 1 role (untuk edit).
     * Gunakan route model binding: /roles/{role}
     */
    public function show(Role $role)
    {
        return response()->json($role);
    }

    /**
     * Update role.
     */
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'role_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('roles', 'role_name')->ignore($role->id),
            ],
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil diperbarui.',
        ]);
    }

    /**
     * Hapus role.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role berhasil dihapus.',
        ]);
    }
}
