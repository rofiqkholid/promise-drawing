<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{

    public function data(Request $request)
    {
        $query = Menu::with('parent')->select('menus.*');

        $totalRecords = $query->count();



        if ($request->has('search') && !empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('route', 'like', '%' . $request->search . '%');
            });
        }

        $totalFiltered = $query->count();

        if ($request->has('order')) {
            $orderColumnIndex = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir');
            $column = $request->input('columns.' . $orderColumnIndex . '.name');

            if ($column === 'parent.title') {
                $query->leftJoin('menus as parent_menu', 'menus.parent_id', '=', 'parent_menu.id')
                    ->orderBy('parent_menu.title', $orderDirection)
                    ->select('menus.*');
            } else {
                $query->orderBy($column, $orderDirection);
            }
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        if ($request->has('length') && $request->input('length') != -1) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $menus = $query->get();

        $start = $request->input('start', 0);
        $data = $menus->map(function ($menu, $index) use ($start) {
            return [
                'DT_RowIndex' => $start + $index + 1,
                'id' => $menu->id,
                'title' => $menu->title,
                'parent_name' => $menu->parent ? $menu->parent->title : 'Main Menu',
                'route' => $menu->route,
                'icon' => $menu->icon,
                'sort_order' => $menu->sort_order,
            ];
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalRecords),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $data
        ]);
    }

    public function getParents(Request $request)
    {
        $query = Menu::whereNull('parent_id')->orderBy('title');

        if ($request->has('exclude')) {
            $query->where('id', '!=', $request->exclude);
        }

        if ($request->has('exclude')) {
            $excludedId = $request->exclude;
            $childIds = Menu::where('parent_id', $excludedId)->pluck('id');
            if ($childIds->isNotEmpty()) {
                $query->whereNotIn('id', $childIds);
            }
        }


        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|sometimes|exists:menus,id',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            if (empty($data['parent_id'])) {
                $data['parent_id'] = null;
            }

            $data['level'] = $data['parent_id'] ? (Menu::find($data['parent_id'])->level + 1) : 0;
            $data['is_active'] = $request->boolean('is_active');
            $data['is_visible'] = $request->boolean('is_visible');

            Menu::create($data);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Menu created successfully.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create menu. Details: ' . $e->getMessage()], 500);
        }
    }

    public function show(Menu $menu)
    {
        return response()->json($menu);
    }

    public function update(Request $request, Menu $menu)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'parent_id' => 'nullable|sometimes|exists:menus,id',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'sort_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            if (empty($data['parent_id'])) {
                $data['parent_id'] = null;
            }

            if (isset($data['parent_id']) && $data['parent_id'] == $menu->id) {
                return response()->json(['errors' => ['parent_id' => ['A menu cannot be its own parent.']]], 422);
            }

            $data['level'] = $data['parent_id'] ? (Menu::find($data['parent_id'])->level + 1) : 0;
            $data['is_active'] = $request->boolean('is_active');
            $data['is_visible'] = $request->boolean('is_visible');

            $menu->update($data);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Menu updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update menu. Details: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Menu $menu)
    {
        try {
            DB::beginTransaction();

            $menu->children()->update(['parent_id' => null]);

            $menu->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Menu deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete menu. Details: ' . $e->getMessage()], 500);
        }
    }
}
