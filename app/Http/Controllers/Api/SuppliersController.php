<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Suppliers;
use App\Models\UserLinkList;    
use Illuminate\Http\Request;

use Illuminate\Validation\Rule;

class SuppliersController extends Controller
{
    /**
     * Display a listing of the resource for DataTables.
     */
    public function data(Request $request)
    {
        $query = Suppliers::query();

        // Handle Search
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        // Handle Sorting
        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['data'] ?? 'name';
        $query->orderBy($sortColumn, $sortDir);

        // Handle Pagination
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

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource for editing.
     */
    public function show(Suppliers $supplier)
    {
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Suppliers $supplier)
    {
        $supplier->delete();
        return response()->json(['success' => true, 'message' => 'Supplier deleted successfully.']);
    }

   public function linksData(Request $request, Suppliers $supplier)
    {
        $draw   = (int) $request->input('draw', 0);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        // support d.search dan d.search.value
        $searchValue = $request->input('search');
        if (is_array($searchValue)) {
            $searchValue = $request->input('search.value');
        }

        // 0=No(dummy), 1=name, 2=email, 3=action(dummy)
        $columns = [ 1 => 'name', 2 => 'email' ];
        $orderColumnIdx = (int) $request->input('order.0.column', 1);
        $orderColumn    = $columns[$orderColumnIdx] ?? 'name';
        $orderDir       = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $base = $supplier->userLinkLists()->select(['id','supplier_id','name','email','created_at']);

        $recordsTotal = (clone $base)->count();

        if (!empty($searchValue)) {
            $base->where(function ($q) use ($searchValue) {
                $q->where('name',  'like', "%{$searchValue}%")
                  ->orWhere('email','like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->orderBy($orderColumn, $orderDir)
                     ->skip($start)->take($length)->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /** CREATE (Add User Link) */
    public function storeLink(Request $request, Suppliers $supplier)
{
    // normalisasi input
    $name  = trim((string) $request->input('name'));
    $email = $request->filled('email') ? strtolower(trim($request->input('email'))) : null;

    // validasi dasar
    $validated = $request->validate([
        'name'  => ['required','string','max:100'],
        'email' => ['required','email','max:150'],
    ]);

    // CEK DUPLIKAT: (supplier_id, name, email) — treat NULL secara eksplisit
    $exists = $supplier->userLinkLists()
        ->where('name', $name)
        ->where(function($q) use ($email) {
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

    // simpan
    $link = $supplier->userLinkLists()->create([
        'name'  => $name,
        'email' => $email,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'User link added.',
        'data'    => $link,
    ]);
}

    /** EDIT (prefill) */
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

    /** UPDATE (POST + _method=PUT) */
    public function updateLink(Request $request, Suppliers $supplier, UserLinkList $link)
{
    if ((int)$link->supplier_id !== (int)$supplier->id) abort(404);

    $validated = $request->validate([
        'name'  => ['required','string','max:100'],
        'email' => ['required','email','max:150'],
    ]);

    $name  = trim((string) $validated['name']);
    $email = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;

    $exists = $supplier->userLinkLists()
        ->where('id', '!=', $link->id)
        ->where('name', $name)
        ->where(function($q) use ($email) {
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

    /** DELETE */
    public function destroyLink(Suppliers $supplier, UserLinkList $link)
    {
        if ((int)$link->supplier_id !== (int)$supplier->id) abort(404);

        $link->delete();

        return response()->json([
            'success' => true,
            'message' => 'User link deleted.',
        ]);
    }
}

  