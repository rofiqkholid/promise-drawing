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
     * Listing for DataTables.
     */
   public function data(Request $request)
{
    $query = Role::query()->from('roles')
        ->select(['roles.id','roles.role_name']);

    // if user_id is sent, include selected info
    if ($request->filled('user_id')) {
        $uid = (int) $request->user_id;
        $query->leftJoin('user_roles as ur', function($j) use ($uid){
            $j->on('ur.role_id','=','roles.id')->where('ur.user_id',$uid);
        })->addSelect(DB::raw('CASE WHEN ur.user_id IS NULL THEN 0 ELSE 1 END AS selected'));
    } else {
        $query->addSelect(DB::raw('0 AS selected'));
    }

    // optional simple search
    if ($request->filled('search')) {
        $s = $request->search;
        $query->where('roles.role_name','like',"%{$s}%");
    }

    // safe sorting (DataTables/Non-DataTables)
    $sortBy    = (int) $request->input('order.0.column', 1);
    $sortDir   = $request->input('order.0.dir', 'asc');
    $columns   = $request->input('columns', []);
    $fallback  = [0 => 'id', 1 => 'role_name', 2 => 'selected'];
    $candidate = $columns[$sortBy]['data'] ?? ($fallback[$sortBy] ?? 'role_name');
    $allowed   = ['id','role_name','selected'];
    $sortCol   = in_array($candidate,$allowed,true) ? $candidate : 'role_name';
    $query->orderBy($sortCol, $sortDir);

    // safe pagination
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
     * Simple: return all roles (if dropdown is needed).
     * Optional: delete if not needed.
     */
    public function all()
    {
        return response()->json(Role::select('id', 'role_name')->orderBy('role_name')->get());
    }

    /**
     * Store new role.
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

        // timestamps will be filled automatically if $timestamps = true in model
        Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
        ]);
    }

    /**
     * Show 1 role (for editing).
     * Use route model binding: /roles/{role}
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
            'message' => 'Role updated successfully.',
        ]);
    }

    /**
     * Delete role.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
        ]);
    }
}
