<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function getActiveUsersCount(): JsonResponse
    {
        try {
            $activeUsersCount = DB::table('users')
                ->where('is_active', '1')
                ->count();

            return response()->json([
                'status' => 'success',
                'count' => $activeUsersCount
            ]);
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch active users count.'
            ], 500); 
        }
    }

    public function getDocumentGroups(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('doctype_groups')
            ->select('id', 'name');

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->name
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }
    public function getSubType(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('doctype_subcategories')
            ->select('id', 'name');

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->name
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }

    public function getCustomer(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('customers')
            ->select('id', 'code');

        if ($searchTerm) {
            $query->where('code', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('code', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->code
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }

    public function getModel(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('models')
            ->select('id', 'code');

        if ($searchTerm) {
            $query->where('code', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('code', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->code
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }
    public function getPartGroup(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('part_groups')
            ->select('id', 'code_part_group');

        if ($searchTerm) {
            $query->where('code_part_group', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('code_part_group', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->code_part_group
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }
    public function getStatus(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('project_status')
            ->select('id', 'name');

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id'   => $group->id,
                'text' => $group->name
            ];
        });

        return response()->json([
            'results'     => $formattedGroups,
            'total_count' => $totalCount
        ]);
    }
}
