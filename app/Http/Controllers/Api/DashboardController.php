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
        $document_group_id = $request->document_group_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('doctype_subcategories')
            ->select('id', 'name')
            ->where('doctype_group_id', $document_group_id);

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
        $customer_id = $request->customer_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('models')
            ->select('id', 'name')
            ->where('customer_id', $customer_id);

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

    public function getDataLog(Request $request): JsonResponse
    {
        $year = $request->input('year', date('Y'));

        $data = DB::table('activity_logs')
            ->selectRaw("
                MONTH(created_at) AS month,
                SUM(CASE WHEN activity_code = 'UPLOAD' THEN 1 ELSE 0 END) AS upload_count,
                SUM(CASE WHEN activity_code = 'DOWNLOAD' THEN 1 ELSE 0 END) AS download_count
            ")
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();

        $data = $data->map(function ($item) {
            $item->upload_count = (int) $item->upload_count;
            $item->download_count = (int) $item->download_count;
            $item->month = (int) $item->month;
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'year' => $year,
            'data' => $data
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan pada server.',
            'error_detail' => $e->getMessage()
        ], 500);
    }

    public function getDataActivityLog(Request $request): JsonResponse
    {
        try {
            $data = DB::table('activity_logs')
                ->join('users', 'activity_logs.user_id', '=', 'users.id')
                ->where('activity_logs.activity_code', 'UPLOAD')
                ->select(
                    'activity_logs.id',
                    'activity_logs.user_id',
                    'users.name as user_name',
                    'activity_logs.activity_code',
                    'activity_logs.meta',
                    'activity_logs.created_at'
                )
                ->orderBy('activity_logs.created_at', 'desc')
                ->limit(4)
                ->get()
                ->map(function ($item) {
                    $item->meta = json_decode($item->meta, true);
                    return $item;
                });

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan pada server.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function getUploadMonitoringData(Request $request)
    {
        $docPackageQuery = DB::table('doc_packages AS dp')
            ->select('dp.product_id', DB::raw('COUNT(dp.id) as actual_count'))
            ->whereNotNull('dp.product_id')
            ->whereNotNull('dp.current_revision_id');

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            $docPackageQuery->whereBetween('dp.created_at', [$startDate, $endDate]);
        }

        $docPackageQuery->when($request->filled('doc_group'), function ($q) use ($request) {
            $q->where('dp.doctype_group_id', $request->doc_group);
        });

        $docPackageQuery->when($request->filled('sub_type'), function ($q) use ($request) {
            $q->where('dp.doctype_subcategory_id', $request->sub_type);
        });

        $docPackageQuery->when($request->filled('part_group'), function ($q) use ($request) {
            $q->where('dp.part_group_id', $request->part_group);
        });

        $docPackageQuery->when($request->filled('project_status'), function ($q) use ($request) {
            $q->where('dp.project_status_id', $request->project_status);
        });

        $docPackageQuery->groupBy('dp.product_id');

        $baseQuery = DB::table('products AS p')
            ->leftJoin('customers AS c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models AS m', 'p.model_id', '=', 'm.id')
            ->leftJoinSub($docPackageQuery, 'dp_counts', function ($join) {
                $join->on('p.id', '=', 'dp_counts.product_id');
            });

        $baseQuery->when($request->filled('key_word'), function ($q) use ($request) {
            $q->where('p.part_no', 'LIKE', '%' . $request->key_word . '%');
        });

        $baseQuery->when($request->filled('customer'), function ($q) use ($request) {
            $q->where('p.customer_id', $request->customer);
        });

        $baseQuery->when($request->filled('model'), function ($q) use ($request) {
            $q->where('p.model_id', $request->model);
        });

        $planColumn = 'm.planning';

        $results = $baseQuery
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                DB::raw("SUM(COALESCE($planColumn, 0)) as plan_count"),
                DB::raw('SUM(COALESCE(dp_counts.actual_count, 0)) as actual_count')
            )
            ->whereNotNull('c.code')
            ->whereNotNull('m.name')
            ->groupBy('c.code', 'm.name')
            ->orderBy('actual_count', 'desc')
            ->limit(5)
            ->get();

        $results = $results->map(function ($item) {
            $plan = floatval($item->plan_count);
            $actual = floatval($item->actual_count);
            $percentage = $plan > 0 ? ($actual / $plan) * 100 : 0;
            $item->percentage = number_format($percentage, 1);
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }


    public function getUploadMonitoringDataProject(Request $request)
    {
        $docPackageQuery = DB::table('doc_packages AS dp')
            ->select('dp.product_id', DB::raw('COUNT(dp.id) as actual_count'))
            ->whereNotNull('dp.product_id');

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            $docPackageQuery->whereBetween('dp.created_at', [$startDate, $endDate]);
        }

        if ($request->filled('doc_group')) {
            $docPackageQuery->where('dp.doctype_group_id', $request->doc_group);
        }

        if ($request->filled('sub_type')) {
            $docPackageQuery->where('dp.doctype_subcategory_id', $request->sub_type);
        }

        if ($request->filled('part_group')) {
            $docPackageQuery->where('dp.part_group_id', $request->part_group);
        }

        $docPackageQuery
            ->join('project_status AS ps', 'dp.project_status_id', '=', 'ps.id')
            ->where('ps.name', 'Project');

        $docPackageQuery->groupBy('dp.product_id');

        $baseQuery = DB::table('products AS p')
            ->leftJoin('doc_packages AS dp', 'p.id', '=', 'dp.product_id')
            ->leftJoin('customers AS c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models AS m', 'p.model_id', '=', 'm.id')
            ->leftJoin('project_status AS ps', 'dp.project_status_id', '=', 'ps.id')
            ->leftJoinSub($docPackageQuery, 'dp_counts', function ($join) {
                $join->on('p.id', '=', 'dp_counts.product_id');
            });

        if ($request->filled('key_word')) {
            $baseQuery->where('p.part_no', 'LIKE', '%' . $request->key_word . '%');
        }

        if ($request->filled('customer')) {
            $baseQuery->where('p.customer_id', $request->customer);
        }

        if ($request->filled('model')) {
            $baseQuery->where('p.model_id', $request->model);
        }

        $planColumn = 'm.planning';

        $results = $baseQuery
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                'ps.name AS project_status_name',
                DB::raw("SUM(COALESCE($planColumn, 0)) as plan_count"),
                DB::raw('SUM(COALESCE(dp_counts.actual_count, 0)) as actual_count'),
                DB::raw('ROUND((SUM(COALESCE(dp_counts.actual_count, 0)) * 100.0 / NULLIF(SUM(COALESCE(m.planning, 0)), 0)), 1) AS percentage')
            )
            ->whereNotNull('c.code')
            ->whereNotNull('dp.current_revision_id')
            ->whereNotNull('m.name')
            ->where('ps.name', 'Project')
            ->groupBy('c.code', 'm.name', 'ps.name')
            ->orderBy('actual_count', 'desc')
            ->limit(5)
            ->get();

        $results->transform(function ($item) {
            $item->percentage = number_format((float)$item->percentage, 1, '.', '');
            return $item;
        });

        return response()->json(['status' => 'success', 'data' => $results]);
    }
}
