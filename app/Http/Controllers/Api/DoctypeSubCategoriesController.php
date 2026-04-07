<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocTypeSubCategories;
use App\Models\DocTypeGroups;
use App\Models\Customers;
use App\Models\DoctypeSubcategoriesAlias;
use Illuminate\Http\Request;

class DocTypeSubCategoriesController extends Controller
{
    public function data(Request $request)
    {
        $query = DocTypeSubCategories::with('docTypeGroup');

        if ($request->has('search') && !empty($request->search)) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhereHas('docTypeGroup', function($qq) use ($term) {
                      $qq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortName = $request->get('columns')[$sortBy]['name'] ?? 'name';

        if ($sortName === 'docTypeGroup.name') {
            $query->join('doctype_groups', 'doctype_subcategories.doctype_group_id', '=', 'doctype_groups.id')
                  ->select('doctype_subcategories.*')
                  ->orderBy('doctype_groups.name', $sortDir);
        } else {
            $query->orderBy($sortName, $sortDir);
        }

        $perPage = (int) $request->get('length', 10);
        $start   = (int) $request->get('start', 0);
        $draw    = (int) $request->get('draw', 1);

        $totalRecords    = DocTypeSubCategories::count();
        $filteredRecords = (clone $query)->count();
        $rows            = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows,
        ]);
    }

    public function getDocTypeGroups()
    {
        return response()->json(
            DocTypeGroups::select('id', 'name')->orderBy('name')->get()
        );
    }

    public function getSubCategories()
    {
        return response()->json(
            DocTypeSubCategories::select('id', 'name')->orderBy('name')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name'             => 'required|string|max:50',
            'description'      => 'nullable|string',
        ]);

        DocTypeSubCategories::create($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Subcategory created successfully.']);
    }

    public function show(DocTypeSubCategories $docTypeSubCategory)
    {
        $docTypeSubCategory->load('docTypeGroup:id,name');

        return response()->json([
            'id'                   => $docTypeSubCategory->id,
            'name'                 => $docTypeSubCategory->name,
            'description'          => $docTypeSubCategory->description,
            'doctype_group_id'     => $docTypeSubCategory->doctype_group_id,
            'doc_type_group_name'  => optional($docTypeSubCategory->docTypeGroup)->name,
        ]);
    }

    public function update(Request $request, DocTypeSubCategories $docTypeSubCategory)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name'             => 'required|string|max:50',
            'description'      => 'nullable|string',
        ]);

        $docTypeSubCategory->update($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Subcategory updated successfully.']);
    }

    public function destroy(DocTypeSubCategories $docTypeSubCategory)
    {
        $docTypeSubCategory->delete();
        return response()->json(['success' => true, 'message' => 'Document Type Subcategory deleted successfully.']);
    }

    public function getCustomers()
    {
        return response()->json(
            Customers::select('id', 'name')->where('is_active', true)->orderBy('name')->get()
        );
    }

    // ====== Aliases (DataTable server-side) ======
    public function aliases(Request $request, $subcategoryId)
    {
        $query = DoctypeSubcategoriesAlias::with('customer')->where('doctypesubcategory_id', $subcategoryId);

        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $query->where(function($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                  ->orWhereHas('customer', function($qq) use ($searchValue) {
                      $qq->where('name', 'like', "%{$searchValue}%")
                         ->orWhere('code', 'like', "%{$searchValue}%");
                  });
            });
        }

        $totalRecords    = DoctypeSubcategoriesAlias::where('doctypesubcategory_id', $subcategoryId)->count();
        $filteredRecords = (clone $query)->count();

        $sortBy    = $request->get('order')[0]['column'] ?? 1;
        $sortDir   = $request->get('order')[0]['dir'] ?? 'asc';
        $sortField = $request->get('columns')[$sortBy]['data'] ?? 'customer';

        if ($sortField === 'customer') {
            $query->join('customers', 'doctypesubcategories_alias.customer_id', '=', 'customers.id')
                  ->select('doctypesubcategories_alias.*')
                  ->orderBy('customers.name', $sortDir);
        } else {
            if (in_array($sortField, ['name'])) {
                $query->orderBy($sortField, $sortDir);
            }
        }

        if ($request->has('length') && $request->length != -1) {
            $query->skip($request->start)->take($request->length);
        }

        $rows = $query->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows,
        ]);
    }

    public function storeAlias(Request $request)
    {
        $validated = $request->validate([
            'doctypesubcategory_id' => 'required|integer|exists:doctype_subcategories,id',
            'customer_id'           => 'required|integer|exists:customers,id',
            'name'                  => 'required|string|max:50',
        ]);

        DoctypeSubcategoriesAlias::create($validated);

        return response()->json(['success' => true, 'message' => 'Alias created successfully.']);
    }

    public function showAlias(DoctypeSubcategoriesAlias $alias)
    {
        $alias->load('customer:id,code,name');

        return response()->json([
            'id'                     => $alias->id,
            'name'                   => $alias->name,
            'customer_id'            => $alias->customer_id,
            'customer_code'          => optional($alias->customer)->code,
            'customer_name'          => optional($alias->customer)->name,
            'doctypesubcategory_id'  => $alias->doctypesubcategory_id,
        ]);
    }

    public function updateAlias(Request $request, DoctypeSubcategoriesAlias $alias)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'name'        => 'required|string|max:50',
        ]);

        $alias->update($validated);

        return response()->json(['success' => true, 'message' => 'Alias updated successfully.']);
    }

    public function destroyAlias(DoctypeSubcategoriesAlias $alias)
    {
        $alias->delete();
        return response()->json(['success' => true, 'message' => 'Alias deleted successfully.']);
    }

    // ====== Select2 (server-side) ======
    public function select2Groups(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $per  = 20;

        $base = DocTypeGroups::query();
        if ($q !== '') {
            $base->where('name', 'like', "%{$q}%");
        }

        $total = (clone $base)->count();
        $rows  = $base->orderBy('name')
                      ->skip(($page - 1) * $per)
                      ->take($per)
                      ->get(['id','name']);

        return response()->json([
            'results' => $rows->map(fn($r) => ['id' => $r->id, 'text' => $r->name]),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    }

    public function select2Customers(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $per  = 20;

        $base = Customers::query(); // ->where('is_active', true) jika perlu
        if ($q !== '') {
            $base->where(function($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%");
            });
        }

        $total = (clone $base)->count();
        $rows  = $base->orderBy('code')
                      ->skip(($page - 1) * $per)
                      ->take($per)
                      ->get(['id','code','name']);

        return response()->json([
            // text = code (sesuai permintaan)
            'results' => $rows->map(fn($r) => ['id' => $r->id, 'text' => $r->code]),
            'pagination' => ['more' => ($page * $per) < $total],
        ]);
    }
}
