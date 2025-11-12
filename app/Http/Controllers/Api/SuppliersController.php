<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use App\Models\UserLinkList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuppliersController extends Controller
{
    public function data(Request $request)
    {
        $query = Suppliers::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%')
                    ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'name';
        $query->orderBy($sortColumn, $sortDir);

        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = Suppliers::count();
        $filteredRecords = $query->count();
        $suppliers = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:suppliers,code',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        Suppliers::create($validated);

        return response()->json(['success' => true, 'message' => 'Supplier created successfully.']);
    }

    public function show(Suppliers $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Suppliers $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('suppliers')->ignore($supplier->id),
            ],
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $supplier->update($validated);

        return response()->json(['success' => true, 'message' => 'Supplier updated successfully.']);
    }

    public function destroy(Suppliers $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }

    public function getAvailableUsers(Request $request, Suppliers $supplier)
    {
        $search = $request->input('search', '');

        $query = User::whereDoesntHave('suppliers', function ($q) use ($supplier) {
            $q->where('supplier_id', $supplier->id);
        });

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->select(['id', 'name', 'email'])
            ->take(20)
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->email . ')'
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function linksData(Request $request, Suppliers $supplier)
    {
        $draw   = (int) $request->input('draw', 0);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = $request->input('search');
        if (is_array($searchValue)) {
            $searchValue = $request->input('search.value');
        }

        $columns = [
            1 => 'users.name',
            2 => 'users.email'
        ];
        $orderColumnIdx = (int) $request->input('order.0.column', 1);
        $orderColumn    = $columns[$orderColumnIdx] ?? 'users.name';
        $orderDir       = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $base = $supplier->users()->select(['users.id', 'users.name', 'users.email']);

        $recordsTotal = (clone $base)->count();

        if (!empty($searchValue)) {
            $base->where(function ($q) use ($searchValue) {
                $q->where('users.name',  'like', "%{$searchValue}%")
                    ->orWhere('users.email', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $base)->count();
        $data = $base->orderBy($orderColumn, $orderDir)
            ->skip($start)->take($length)->get();

        return response()->json([
            'draw'              => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'              => $data,
        ]);
    }

    public function storeLink(Request $request, Suppliers $supplier)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
        ]);

        $userId = $validated['user_id'];

        $exists = $supplier->users()->where('user_id', $userId)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'User is already linked to this supplier.',
            ], 422);
        }

        $supplier->users()->attach($userId);

        return response()->json([
            'success' => true,
            'message' => 'User link added successfully.',
        ]);
    }


    public function showLink(Suppliers $supplier, UserLinkList $link)
    {
        if ((int)$link->supplier_id !== (int)$supplier->id) abort(404);

        return response()->json([
            'id'          => $link->id,
            'supplier_id' => $link->supplier_id,
            'name'        => $link->name,
            'email'       => $link->email,
        ]);
    }

    public function updateLink(Request $request, Suppliers $supplier, UserLinkList $link)
    {
        if ((int)$link->supplier_id !== (int)$supplier->id) abort(404);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $name  = trim((string) $validated['name']);
        $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;

        $exists = $supplier->userLinkLists()
            ->where('id', '!=', $link->id)
            ->where('name', $name)
            ->where(function ($q) use ($email) {
                if ($email === null) $q->whereNull('email');
                else $q->where('email', $email);
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'User link sudah ada untuk supplier ini.',
            ], 422);
        }

        $link->update([
            'name'  => $name,
            'email' => $email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User link updated.',
            'data'    => $link,
        ]);
    }

    public function destroyLink(Suppliers $supplier, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (!$supplier->users()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User link not found for this supplier.',
            ], 404);
        }

        $supplier->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'User link deleted successfully.',
        ]);
    }
}
