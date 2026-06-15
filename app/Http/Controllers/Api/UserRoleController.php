<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = DB::table('user_scope_roles')
            ->join('users as u', 'u.id', '=', 'user_scope_roles.user_id')
            ->join('roles as r', 'r.id', '=', 'user_scope_roles.role_id')
            ->where('user_scope_roles.scope_id', 'app_drawing')
            ->select('user_scope_roles.user_id', 'user_scope_roles.role_id', 'u.name as user_name', 'u.email as user_email', 'r.role_name as role_name');

        // Search
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('u.name', 'like', "%{$s}%")
                  ->orWhere('u.email', 'like', "%{$s}%")
                  ->orWhere('r.role_name', 'like', "%{$s}%");
            });
        }

        // Sorting
        $sortBy   = $request->input('order.0.column', 1);
        $sortDir  = $request->input('order.0.dir', 'asc');
        $columns  = $request->input('columns', []);
        $sortName = $columns[$sortBy]['name'] ?? 'user';

        $sortMap = [
            'user' => 'u.name',
            'role' => 'r.role_name',
        ];
        $query->orderBy($sortMap[$sortName] ?? 'u.name', $sortDir);

        // Pagination
        $perPage = (int) $request->get('length', 10);
        $start   = (int) $request->get('start', 0);
        $draw    = (int) $request->get('draw', 1);

        $totalRecords    = DB::table('user_scope_roles')->where('scope_id', 'app_drawing')->count();
        $filteredRecords = (clone $query)->count(); // count setelah filter
        $rows = $query->skip($start)->take($perPage)->get();

        // Map back to expected properties for Datatables / frontend key names
        $rows = $rows->map(function($row) {
            return [
                'user_id' => $row->user_id,
                'role_id' => $row->role_id,
                'user' => [
                    'id' => $row->user_id,
                    'name' => $row->user_name,
                    'email' => $row->user_email
                ],
                'role' => [
                    'id' => $row->role_id,
                    'role_name' => $row->role_name
                ]
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows,
        ]);
    }

    /**
     * Get users & roles for dropdown.
     */
    public function dropdowns()
    {
        $users = User::select('id','name')->orderBy('name')->get();
        $roles = Role::select('id','role_name as name')->orderBy('role_name')->get();
        return response()->json(compact('users','roles'));
    }

    /**
     * Store a newly created mapping.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $exists = DB::table('user_scope_roles')
            ->where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->where('scope_id', 'app_drawing')
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['role_id' => ['Mapping user–role sudah ada.']]
            ], 422);
        }

        DB::table('user_scope_roles')->insert([
            'user_id' => $validated['user_id'],
            'role_id' => $validated['role_id'],
            'scope_id' => 'app_drawing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Mapping created successfully.']);
    }

    /**
     * Display the specified resource (by pair).
     */
    public function pairShow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'role_id' => 'required|integer',
        ]);

        $userRole = DB::table('user_scope_roles')
            ->where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->where('scope_id', 'app_drawing')
            ->first();

        if (!$userRole) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // Fetch related models manually to maintain structure expected by view
        $user = User::select('id', 'name', 'email')->find($userRole->user_id);
        $role = Role::select('id', 'role_name')->find($userRole->role_id);

        return response()->json([
            'user_id' => $userRole->user_id,
            'role_id' => $userRole->role_id,
            'user' => $user,
            'role' => $role
        ]);
    }

    /**
     * Update the specified resource (by pair).
     */
    public function pairUpdate(Request $request)
    {
        $validated = $request->validate([
            'original_user_id' => 'required|integer',
            'original_role_id' => 'required|integer',
            'user_id'          => 'required|integer|exists:users,id',
            'role_id'          => 'required|integer|exists:roles,id',
        ]);

        $userRole = DB::table('user_scope_roles')
            ->where('user_id', $validated['original_user_id'])
            ->where('role_id', $validated['original_role_id'])
            ->where('scope_id', 'app_drawing')
            ->first();

        if (!$userRole) {
            return response()->json(['message' => 'Original mapping not found.'], 404);
        }

        $duplicate = DB::table('user_scope_roles')
            ->where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->where('scope_id', 'app_drawing')
            ->exists();

        if ($duplicate && ($validated['user_id'] != $validated['original_user_id'] || $validated['role_id'] != $validated['original_role_id'])) {
            return response()->json([
                'errors' => ['role_id' => ['Mapping user–role sudah ada.']]
            ], 422);
        }

        DB::table('user_scope_roles')
            ->where('user_id', $validated['original_user_id'])
            ->where('role_id', $validated['original_role_id'])
            ->where('scope_id', 'app_drawing')
            ->update([
                'user_id' => $validated['user_id'],
                'role_id' => $validated['role_id'],
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Mapping updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function pairDestroy(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'role_id' => 'required',
        ]);

        $deleted = DB::table('user_scope_roles')
            ->where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->where('scope_id', 'app_drawing')
            ->delete();

        if ($deleted > 0) {
            return response()->json(['success' => true, 'message' => 'Mapping deleted.']);
        }

        return response()->json(['success' => false, 'message' => 'Mapping not found.'], 404);
    }

    public function byUser(User $user)
    {
        $roleIds = DB::table('user_scope_roles')
            ->where('user_id', $user->id)
            ->where('scope_id', 'app_drawing')
            ->pluck('role_id');
        return response()->json($roleIds);
    }

    public function sync(Request $request, User $user)
    {
        $data = $request->validate([
            'role_ids'   => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $newIds = collect($data['role_ids'] ?? [])->unique()->values();

        $current = DB::table('user_scope_roles')
            ->where('user_id', $user->id)
            ->where('scope_id', 'app_drawing')
            ->pluck('role_id');

        $toInsert = $newIds->diff($current);
        $toDelete = $current->diff($newIds);

        if ($toInsert->isNotEmpty()) {
            $rows = $toInsert->map(fn($rid) => [
                'user_id' => $user->id, 
                'role_id' => $rid,
                'scope_id' => 'app_drawing',
                'created_at' => now(),
                'updated_at' => now()
            ])->all();
            DB::table('user_scope_roles')->insert($rows);
        }
        if ($toDelete->isNotEmpty()) {
            DB::table('user_scope_roles')
                ->where('user_id', $user->id)
                ->where('scope_id', 'app_drawing')
                ->whereIn('role_id', $toDelete)
                ->delete();
        }

        return response()->json(['success' => true, 'message' => 'Roles synchronized.']);
    }
}
