<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;


class UserRoleController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
   public function data(Request $request)
{
    $query = UserRole::query()
        ->join('users as u', 'u.id', '=', 'user_roles.user_id')
        ->join('roles as r', 'r.id', '=', 'user_roles.role_id')
        ->with(['user','role'])
        ->select('user_roles.*'); // penting: select pivot agar Eloquent tetap mengisi model

    // Search
    if ($request->filled('search')) {
        $s = $request->search;
        $query->where(function ($q) use ($s) {
            $q->where('u.name', 'like', "%{$s}%")
              ->orWhere('u.email', 'like', "%{$s}%")
              ->orWhere('r.name', 'like', "%{$s}%");
        });
    }

    // Sorting
    $sortBy   = $request->input('order.0.column', 1);
    $sortDir  = $request->input('order.0.dir', 'asc');
    $columns  = $request->input('columns', []);
    $sortName = $columns[$sortBy]['name'] ?? 'user';

    $sortMap = [
        'user' => 'u.name',
        'role' => 'r.name',
    ];
    $query->orderBy($sortMap[$sortName] ?? 'u.name', $sortDir);

    // Pagination
    $perPage = (int) $request->get('length', 10);
    $start   = (int) $request->get('start', 0);
    $draw    = (int) $request->get('draw', 1);

    $totalRecords    = UserRole::count();
    $filteredRecords = (clone $query)->count(); // count setelah filter
    $rows = $query->skip($start)->take($perPage)->get();

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

        $exists = UserRole::where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => ['role_id' => ['Mapping user–role sudah ada.']]
            ], 422);
        }

        UserRole::create($validated);

        return response()->json(['success' => true, 'message' => 'Mapping created successfully.']);
    }

    /**
     * Display the specified resource (by pair).
     * GET /user-role/pair?user_id=&role_id=
     */
    public function pairShow(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'role_id' => 'required|integer',
        ]);

        $userRole = UserRole::with(['user', 'role'])
            ->where('user_id', $request->user_id)
            ->where('role_id', $request->role_id)
            ->first();

        if (!$userRole) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($userRole);
    }

    /**
     * Update the specified resource (by pair).
     * PUT /user-role/pair
     */
    public function pairUpdate(Request $request)
    {
        $validated = $request->validate([
            'original_user_id' => 'required|integer',
            'original_role_id' => 'required|integer',
            'user_id'          => 'required|integer|exists:users,id',
            'role_id'          => 'required|integer|exists:roles,id',
        ]);

        $userRole = UserRole::where('user_id', $validated['original_user_id'])
            ->where('role_id', $validated['original_role_id'])
            ->first();

        if (!$userRole) {
            return response()->json(['message' => 'Original mapping not found.'], 404);
        }

        $duplicate = UserRole::where('user_id', $validated['user_id'])
            ->where('role_id', $validated['role_id'])
            ->exists();

        if ($duplicate && ($validated['user_id'] != $validated['original_user_id'] || $validated['role_id'] != $validated['original_role_id'])) {
            return response()->json([
                'errors' => ['role_id' => ['Mapping user–role sudah ada.']]
            ], 422);
        }

        $userRole->update([
            'user_id' => $validated['user_id'],
            'role_id' => $validated['role_id'],
        ]);

        return response()->json(['success' => true, 'message' => 'Mapping updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /user-role/pair
     */
    public function pairDestroy(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required',
        'role_id' => 'required',
    ]);

    $deleted = UserRole::where('user_id', $validated['user_id'])
        ->where('role_id', $validated['role_id'])
        ->delete();

    if ($deleted > 0) {
        return response()->json(['success' => true, 'message' => 'Mapping deleted.']);
    }

    return response()->json(['success' => false, 'message' => 'Mapping not found.'], 404);
}

}
