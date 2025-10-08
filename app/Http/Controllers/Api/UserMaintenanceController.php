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

        // Frontend mengirim d.search = d.search.value (string)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        // Sorting
        $sortBy     = $request->input('order.0.column', 1); // default kolom "name"
        $sortDir    = $request->input('order.0.dir', 'asc');
        $columns    = $request->input('columns', []);
        $sortColumn = $columns[$sortBy]['data'] ?? 'name';
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
     * Show untuk prefilling edit.
     */
    public function show(User $user)
    {
        return response()->json($user->only(['id','name','email','nik','is_active']));
    }

    /**
     * Store user baru.
     * FRONTEND mengharapkan JSON: { success, id, data }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required','string','max:50'],
            'email'     => ['required','string','email','max:50','unique:users,email'],
            'nik'       => ['required','string','max:20','unique:users,nik'],
            'password'  => ['required','string','min:6','max:255'],
            'is_active' => ['required','boolean'],
        ]);

        // pastikan boolean -> 1/0
        $validated['is_active'] = $request->boolean('is_active') ? 1 : 0;
        $validated['password']  = Hash::make($validated['password']);

        $user = User::create($validated);

        // penting: kirim id & data untuk dipakai membuka panel User Roles
        return response()->json([
            'success' => true,
            'id'      => $user->id,
            'data'    => $user->only(['id','name','email','nik','is_active']),
        ]);
    }

    /**
     * Update user (password opsional).
     * FRONTEND mengharapkan JSON: { success, data }
     */
   public function update(Request $request, User $user)
{
    // ===== Roles-only mode (tanpa ubah field user) =====
    if ($request->has('role_ids') && !$request->has('name')) {
        $data = $request->validate([
            'role_ids'   => ['array'],
            'role_ids.*' => ['integer','exists:roles,id'],
        ]);

        $ids = collect($data['role_ids'] ?? [])->map(fn($v)=>(int)$v)->unique()->values()->all();
        // withTimestamps() di relasi akan mengisi created_at & updated_at pivot
        $user->roles()->sync($ids);

        return response()->json(['success' => true]);
    }

    // ===== Mode update biasa (nama/email/nik/dll) =====
    $validated = $request->validate([
        'name'      => ['required','string','max:50'],
        'email'     => [
            'required','string','email','max:50',
            Rule::unique('users','email')->ignore($user->id),
        ],
        'nik'       => [
            'required','string','max:20',
            Rule::unique('users','nik')->ignore($user->id),
        ],
        'password'  => ['nullable','string','min:6','max:255'],
        'is_active' => ['required','boolean'],
    ]);

    $validated['is_active'] = $request->boolean('is_active') ? 1 : 0;
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json([
        'success' => true,
        'data'    => $user->only(['id','name','email','nik','is_active']),
    ]);
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
