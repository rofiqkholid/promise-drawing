<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Departments; 
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
        // 🔹 JOIN to departments to get department code
        $query = User::query()
            ->leftJoin('departments as d', 'users.id_dept', '=', 'd.id') // <-- NEW
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.nik',
                'users.is_active',
                'users.id_dept',                // <-- NEW (FK)
                'd.code as department_code',    // <-- NEW (to be displayed in the table)
            ]);

        // Frontend mengirim d.search = d.search.value (string)
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('users.name', 'like', "%{$s}%")
                  ->orWhere('users.email', 'like', "%{$s}%")
                  ->orWhere('users.nik', 'like', "%{$s}%")
                  ->orWhere('d.code', 'like', "%{$s}%"); // <-- NEW: also search by dept code
            });
        }

        // Sorting
        $sortBy  = $request->input('order.0.column', 1); // default kolom "name"
        $sortDir = $request->input('order.0.dir', 'asc');
        $columns = $request->input('columns', []);
        $sortColumn = $columns[$sortBy]['data'] ?? 'name';

        // add department_code to whitelist
        if (!in_array($sortColumn, ['name','email','nik','is_active','department_code'], true)) { // <-- EDIT
            $sortColumn = 'name';
        }

        // use mapping to avoid ambiguity (due to join)
        switch ($sortColumn) {
            case 'email':
                $query->orderBy('users.email', $sortDir);
                break;
            case 'nik':
                $query->orderBy('users.nik', $sortDir);
                break;
            case 'is_active':
                $query->orderBy('users.is_active', $sortDir);
                break;
            case 'department_code':                              // <-- NEW
                $query->orderBy('d.code', $sortDir);
                break;
            case 'name':
            default:
                $query->orderBy('users.name', $sortDir);
                break;
        }

        // Pagination
        $perPage = (int) $request->input('length', 10);
        $start   = (int) $request->input('start', 0);
        $draw    = (int) $request->input('draw', 1);

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
        // 🔹 load department relation from User model
        $user->load('department'); // <-- NEW

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'nik'             => $user->nik,
            'is_active'       => $user->is_active,
            'id_dept'         => $user->id_dept,                 // <-- NEW
            'department_code' => optional($user->department)->code, // <-- NEW (dept code)
        ]);
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
            'id_dept'   => ['required','integer','exists:departments,id'], // <-- NEW
        ]);

        // ensure boolean -> 1/0
        $validated['is_active'] = $request->boolean('is_active') ? 1 : 0;
        $validated['password']  = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->load('department'); // <-- NEW

        // important: send id & data to be used to open the User Roles panel
        return response()->json([
            'success' => true,
            'id'      => $user->id,
            'data'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'nik'             => $user->nik,
                'is_active'       => $user->is_active,
                'id_dept'         => $user->id_dept,                 // <-- NEW
                'department_code' => optional($user->department)->code, // <-- NEW
            ],
        ]);
    }

    /**
     * Update user (password opsional).
     * FRONTEND mengharapkan JSON: { success, data }
     */
    public function update(Request $request, User $user)
    {
        // ===== Roles-only mode (without changing user fields) =====
        if ($request->has('role_ids') && !$request->has('name')) {
            $data = $request->validate([
                'role_ids'   => ['array'],
                'role_ids.*' => ['integer','exists:roles,id'],
            ]);

            $ids = collect($data['role_ids'] ?? [])->map(fn($v)=>(int)$v)->unique()->values()->all();
            // withTimestamps() in relation will fill pivot's created_at & updated_at
            $user->roles()->sync($ids);

            return response()->json(['success' => true]);
        }

        // ===== Normal update mode (name/email/nik/etc) =====
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
            'id_dept'   => ['required','integer','exists:departments,id'], // <-- NEW
        ]);

        $validated['is_active'] = $request->boolean('is_active') ? 1 : 0;
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('department'); // <-- NEW

        return response()->json([
            'success' => true,
            'data'    => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'nik'             => $user->nik,
                'is_active'       => $user->is_active,
                'id_dept'         => $user->id_dept,                 // <-- NEW
                'department_code' => optional($user->department)->code, // <-- NEW
            ],
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


  

    // ...

    // ==== Select2 Department (AJAX) ====
    public function departmentsSelect2(Request $request)
    {
        $q       = trim($request->get('q', ''));
        $page    = max(1, (int) $request->get('page', 1));
        $perPage = 20;

        $builder = Departments::query()
            ->selectRaw('id, code AS text') // label = code, value = id
            ->when($q, function ($x) use ($q) {
                $x->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code');

        $total = (clone $builder)->count();
        $items = $builder->forPage($page, $perPage)->get();

        return response()->json([
            'results'    => $items,
            'pagination' => ['more' => ($total > $page * $perPage)],
        ]);
    }
}
