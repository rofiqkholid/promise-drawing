<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocTypeSubCategories;
use App\Models\DocTypeGroups;
use App\Models\Customers;
use App\Models\DoctypeSubcategoriesAlias;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocTypeSubCategoriesController extends Controller
{

    public function data(Request $request)
    {
        $query = DocTypeSubCategories::with('docTypeGroup');


        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('doctype_group_id', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $sortBy = $request->get('order')[0]['column'] ?? 1;
        $sortDir = $request->get('order')[0]['dir'] ?? 'asc';
        $sortColumn = $request->get('columns')[$sortBy]['name'] ?? 'name';
        if ($sortColumn === 'docTypeGroup.name') {
            $query->join('doctype_groups', 'doctype_subcategories.doctype_group_id', '=', 'doctype_groups.id')
                ->orderBy('doctype_groups.name', $sortDir)
                ->select('doctype_subcategories.*');
        } else {
            $query->orderBy($sortColumn, $sortDir);
        }

        $perPage = $request->get('length', 10);
        $start = $request->get('start', 0);
        $draw = $request->get('draw', 1);

        $totalRecords = DocTypeSubCategories::count();
        $filteredRecords = $query->count();
        $docTypeSubCategories = $query->skip($start)->take($perPage)->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $docTypeSubCategories
        ]);
    }

    public function getDocTypeGroups()
    {
        $docTypeGroups = DocTypeGroups::select('id', 'name')->get();
        return response()->json($docTypeGroups);
    }

    public function getSubCategories()
    {
        $subCategories = DocTypeSubCategories::select('id', 'name')->get();
        return response()->json($subCategories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
        ]);

        DocTypeSubCategories::create($validated);

        return response()->json(['success' => true, 'message' => 'Document Type Subcategory created successfully.']);
    }

    public function show(DocTypeSubCategories $docTypeSubCategory)
    {
        return response()->json($docTypeSubCategory);
    }

    public function update(Request $request, DocTypeSubCategories $docTypeSubCategory)
    {
        $validated = $request->validate([
            'doctype_group_id' => 'required|integer|exists:doctype_groups,id',
            'name' => 'required|string|max:50',
            'description' => 'nullable|string',
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
        $customers = Customers::select('id', 'name')->get();
        return response()->json($customers);
    }


    // public function aliases(Request $request, $subcategoryId)
    // {
    //     $query = DoctypeSubcategoriesAlias::with('customer')->where('doctypesubcategory_id', $subcategoryId);

    //     // Handle Search
    //     if ($request->has('search') && !empty($request->search['value'])) {
    //         $searchValue = $request->search['value'];
    //         $query->where(function($q) use ($searchValue) {
    //             $q->where('name', 'like', '%' . $searchValue . '%')
    //               ->orWhereHas('customer', function($q) use ($searchValue) {
    //                   $q->where('name', 'like', '%' . $searchValue . '%');
    //               });
    //         });
    //     }

    //     $totalRecords = $query->count();

    //     // Handle Sorting
    //     if ($request->has('order')) {
    //         $order = $request->order[0];
    //         $sortColumn = $request->columns[$order['column']]['data'];
    //         $sortDir = $order['dir'];

    //         if ($sortColumn === 'customer') {
    //             $query->join('customers', 'doctypesubcategories_alias.customer_id', '=', 'customers.id')
    //                   ->orderBy('customers.name', $sortDir)
    //                   ->select('doctypesubcategories_alias.*');
    //         } else {
    //             $query->orderBy($sortColumn, $sortDir);
    //         }
    //     }

    //     // Handle Pagination
    //     if ($request->has('length') && $request->length != -1) {
    //         $query->skip($request->start)->take($request->length);
    //     }

    //     $aliases = $query->get();

    //     return response()->json([
    //         'draw' => $request->draw,
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $totalRecords,
    //         'data' => $aliases
    //     ]);
    // }


    // public function storeAlias(Request $request)
    // {
    //     $validated = $request->validate([
    //         'doctypesubcategory_id' => 'required|integer|exists:doctype_subcategories,id',
    //         'customer_id' => 'required|integer|exists:customers,id',
    //         'name' => 'required|string|max:50',
    //     ]);

    //     DoctypeSubcategoriesAlias::create($validated);

    //     return response()->json(['success' => true, 'message' => 'Alias created successfully.']);
    // }


    // public function showAlias(DoctypeSubcategoriesAlias $alias)
    // {
    //     return response()->json($alias);
    // }


    // public function updateAlias(Request $request, DoctypeSubcategoriesAlias $alias)
    // {
    //     $validated = $request->validate([
    //         'customer_id' => 'required|integer|exists:customers,id',
    //         'name' => 'required|string|max:50',
    //     ]);

    //     $alias->update($validated);

    //     return response()->json(['success' => true, 'message' => 'Alias updated successfully.']);
    // }


    // public function destroyAlias(DoctypeSubcategoriesAlias $alias)
    // {
    //     $alias->delete();
    //     return response()->json(['success' => true, 'message' => 'Alias deleted successfully.']);
    // }
}
