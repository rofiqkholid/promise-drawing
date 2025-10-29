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

    public function getUploadCount(): JsonResponse
    {
        try {
            $uploadCount = DB::table('doc_packages')->count('package_no');

            return response()->json([
                'status' => 'success',
                'count' => $uploadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch upload count.',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    public function getDownloadCount(): JsonResponse
    {
        try {
            $uploadCount = DB::table('activity_logs')
            ->where('activity_code', 'DOWNLOAD')
            ->count('activity_code');

            return response()->json([
                'status' => 'success',
                'count' => $uploadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch upload count.',
                'error' => $e->getMessage() 
            ], 500);
        }
    }

    public function getDocCount(): JsonResponse
    {
        try {
            $uploadCount = DB::table('doc_package_revision_files')
            ->count('filename');

            return response()->json([
                'status' => 'success',
                'count' => $uploadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch upload count.',
                'error' => $e->getMessage() 
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
        $query = DB::table('part_groups AS pg')
            ->leftJoin('models AS m', 'pg.model_id', '=', 'm.id')
            ->leftJoin('customers AS c', 'pg.customer_id', '=', 'c.id')
            ->leftJoin('doc_packages AS dp', function ($join) {
                $join->on('dp.customer_id', '=', 'pg.customer_id')
                    ->on('dp.model_id', '=', 'pg.model_id')
                    ->on('dp.part_group_id', '=', 'pg.id');
            })
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                'pg.code_part_group AS part_group',
                'pg.planning AS plan_count',
                DB::raw('COUNT(dp.id) AS actual_count')
            );

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dp.created_at', [$startDate, $endDate])
                    ->orWhereNull('dp.created_at');
            });
        }

        $query->when($request->filled('doc_group'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_group_id', $request->doc_group)
                    ->orWhereNull('dp.doctype_group_id');
            });
        });

        $query->when($request->filled('sub_type'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_subcategory_id', $request->sub_type)
                    ->orWhereNull('dp.doctype_subcategory_id');
            });
        });

        $query->when($request->filled('part_group'), function ($q) use ($request) {
            $q->where('pg.code_part_group', $request->part_group);
        });

        $query->when($request->filled('project_status'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.project_status_id', $request->project_status)
                    ->orWhereNull('dp.project_status_id');
            });
        });

        $query->groupBy(
            'pg.code_part_group',
            'm.name',
            'c.code',
            'pg.planning'
        );

        $sortBy = $request->input('sort_by', 'plan');
        $orderColumn = $sortBy === 'actual' ? 'actual_count' : 'pg.planning';
        $query->orderBy($orderColumn, 'desc');

        $results = $query->limit(5)->get();

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
        $query = DB::table('part_groups AS pg')
            ->leftJoin('models AS m', 'pg.model_id', '=', 'm.id')
            ->leftJoin('customers AS c', 'pg.customer_id', '=', 'c.id')
            ->leftJoin('doc_packages AS dp', function ($join) {
                $join->on('dp.customer_id', '=', 'pg.customer_id')
                    ->on('dp.model_id', '=', 'pg.model_id')
                    ->on('dp.part_group_id', '=', 'pg.id');
            })
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                'pg.code_part_group AS part_group',
                'pg.planning AS plan_count',
                DB::raw('COUNT(dp.current_revision_id) AS actual_count')
            )
            ->where('m.status_id', 3);

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dp.created_at', [$startDate, $endDate])
                    ->orWhereNull('dp.created_at');
            });
        }

        $query->when($request->filled('doc_group'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_group_id', $request->doc_group)
                    ->orWhereNull('dp.doctype_group_id');
            });
        });

        $query->when($request->filled('sub_type'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_subcategory_id', $request->sub_type)
                    ->orWhereNull('dp.doctype_subcategory_id');
            });
        });

        $query->when($request->filled('part_group'), function ($q) use ($request) {
            $q->where('pg.code_part_group', $request->part_group);
        });

        $query->groupBy(
            'pg.id',
            'pg.code_part_group',
            'm.name',
            'm.status_id',
            'c.code',
            'pg.planning'
        );

        $sortBy = $request->input('sort_by', 'plan');
        $orderColumn = $sortBy === 'actual' ? 'actual_count' : 'pg.planning';
        $query->orderBy($orderColumn, 'desc');

        $results = $query->limit(5)->get();

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

    public function getUploadDashboardData(Request $request)
    {
        $query = DB::table('part_groups AS pg')
            ->leftJoin('models AS m', 'pg.model_id', '=', 'm.id')
            ->leftJoin('customers AS c', 'pg.customer_id', '=', 'c.id')
            ->leftJoin('doc_packages AS dp', function ($join) {
                $join->on('dp.customer_id', '=', 'pg.customer_id')
                    ->on('dp.model_id', '=', 'pg.model_id')
                    ->on('dp.part_group_id', '=', 'pg.id');
            })
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                'pg.code_part_group AS part_group',
                'pg.planning AS plan_count',
                DB::raw('COUNT(dp.id) AS actual_count')
            );

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dp.created_at', [$startDate, $endDate])
                    ->orWhereNull('dp.created_at');
            });
        }

        $query->when($request->filled('doc_group'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_group_id', $request->doc_group)
                    ->orWhereNull('dp.doctype_group_id');
            });
        });

        $query->when($request->filled('sub_type'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_subcategory_id', $request->sub_type)
                    ->orWhereNull('dp.doctype_subcategory_id');
            });
        });

        $query->when($request->filled('part_group'), function ($q) use ($request) {
            $q->where('pg.code_part_group', $request->part_group);
        });

        $query->when($request->filled('project_status'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.project_status_id', $request->project_status)
                    ->orWhereNull('dp.project_status_id');
            });
        });

        $query->groupBy(
            'pg.code_part_group',
            'm.name',
            'c.code',
            'pg.planning'
        );

        $sortBy = $request->input('sort_by', 'plan');
        $orderColumn = $sortBy === 'actual' ? 'actual_count' : 'pg.planning';
        $query->orderBy($orderColumn, 'desc');

        $results = $query->get();

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
    public function getUploadDashboardDataProject(Request $request)
    {
        $query = DB::table('part_groups AS pg')
            ->leftJoin('models AS m', 'pg.model_id', '=', 'm.id')
            ->leftJoin('customers AS c', 'pg.customer_id', '=', 'c.id')
            ->leftJoin('doc_packages AS dp', function ($join) {
                $join->on('dp.customer_id', '=', 'pg.customer_id')
                    ->on('dp.model_id', '=', 'pg.model_id')
                    ->on('dp.part_group_id', '=', 'pg.id');
            })
            ->select(
                'c.code AS customer_name',
                'm.name AS model_name',
                'pg.code_part_group AS part_group',
                'pg.planning AS plan_count',
                DB::raw('COUNT(dp.current_revision_id) AS actual_count')
            )
            ->where('m.status_id', 3);

        if ($request->filled('month')) {
            $month = $request->month;
            $startDate = $month . '-01 00:00:00';
            $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('dp.created_at', [$startDate, $endDate])
                    ->orWhereNull('dp.created_at');
            });
        }

        $query->when($request->filled('doc_group'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_group_id', $request->doc_group)
                    ->orWhereNull('dp.doctype_group_id');
            });
        });

        $query->when($request->filled('sub_type'), function ($q) use ($request) {
            $q->where(function ($w) use ($request) {
                $w->where('dp.doctype_subcategory_id', $request->sub_type)
                    ->orWhereNull('dp.doctype_subcategory_id');
            });
        });

        $query->when($request->filled('part_group'), function ($q) use ($request) {
            $q->where('pg.code_part_group', $request->part_group);
        });

        $query->groupBy(
            'pg.id',
            'pg.code_part_group',
            'm.name',
            'm.status_id',
            'c.code',
            'pg.planning'
        );

        $sortBy = $request->input('sort_by', 'plan');
        $orderColumn = $sortBy === 'actual' ? 'actual_count' : 'pg.planning';
        $query->orderBy($orderColumn, 'desc');

        $results = $query->get();

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
}
