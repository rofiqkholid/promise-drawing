<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserMaintenanceController extends Controller
{
    /**
     * DataTables server-side (search, sort, paginate).
     */
    public function data(Request $request)
    {
        $query = User::query()->select(['id','name','email','nik','is_active']);

        // Search (mengikuti JS Anda: d.search = d.search.value)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        // Sorting
        $sortBy    = $request->input('order.0.column', 1);         // default kolom "name"
        $sortDir   = $request->input('order.0.dir', 'asc');
        $columns   = $request->input('columns', []);
        $sortColumn= $columns[$sortBy]['data'] ?? 'name';
        // amankan nama kolom
        if (!in_array($sortColumn, ['name','email','nik','is_active'])) {
            $sortColumn = 'name';
        }
        $query->orderBy($sortColumn, $sortDir);

        // Pagination
        $perPage   = (int) $request->input('length', 10);
        $start     = (int) $request->input('start', 0);
        $draw      = (int) $request->input('draw', 1);

        $totalRecords    = User::count();
        $filteredRecords = (clone $query)->count();
        $users           = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $users,
        ]);
    }

    /**
     * Simpel show untuk prefilling modal edit.
     */
    public function show(User $user)
    {
        return response()->json($user->only(['id','name','email','nik','is_active']));
    }

    /**
     * Store user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:50'],
            'email'    => ['required','string','email','max:50','unique:users,email'],
            'nik'      => ['required','string','max:20','unique:users,nik'],
            'password' => ['required','string','min:6','max:255'],
            'is_active'=> ['required','boolean'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return response()->json(['success' => true, 'message' => 'User created successfully.']);
    }

    /**
     * Update user (password opsional).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => ['required','string','max:50'],
            'email' => [
                'required','string','email','max:50',
                Rule::unique('users','email')->ignore($user->id),
            ],
            'nik'   => [
                'required','string','max:20',
                Rule::unique('users','nik')->ignore($user->id),
            ],
            'password' => ['nullable','string','min:6','max:255'],
            'is_active' => ['required','boolean'],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }
}
