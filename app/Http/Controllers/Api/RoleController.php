<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
    $query = Role::query()->from('roles')
        ->select(['roles.id','roles.role_name']);

    // jika dikirim user_id, ikutkan info selected
    if ($request->filled('user_id')) {
        $uid = (int) $request->user_id;
        $query->leftJoin('user_roles as ur', function($j) use ($uid){
            $j->on('ur.role_id','=','roles.id')->where('ur.user_id',$uid);
        })->addSelect(DB::raw('CASE WHEN ur.user_id IS NULL THEN 0 ELSE 1 END AS selected'));
    } else {
        $query->addSelect(DB::raw('0 AS selected'));
    }

    // optional search sederhana
    if ($request->filled('search')) {
        $s = $request->search;
        $query->where('roles.role_name','like',"%{$s}%");
    }

    // sorting aman (DataTables/Non-DataTables)
    $sortBy    = (int) $request->input('order.0.column', 1);
    $sortDir   = $request->input('order.0.dir', 'asc');
    $columns   = $request->input('columns', []);
    $fallback  = [0 => 'id', 1 => 'role_name', 2 => 'selected'];
    $candidate = $columns[$sortBy]['data'] ?? ($fallback[$sortBy] ?? 'role_name');
    $allowed   = ['id','role_name','selected'];
    $sortCol   = in_array($candidate,$allowed,true) ? $candidate : 'role_name';
    $query->orderBy($sortCol, $sortDir);

    // pagination aman
    $perPage   = (int) $request->input('length', 1000);
    $start     = (int) $request->input('start', 0);
    $draw      = (int) $request->input('draw', 1);

    $total     = Role::count();
    $filtered  = (clone $query)->count();
    $rows      = $query->skip($start)->take($perPage)->get();

    return response()->json([
        'draw'            => $draw,
        'recordsTotal'    => $total,
        'recordsFiltered' => $filtered,
        'data'            => $rows,
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
