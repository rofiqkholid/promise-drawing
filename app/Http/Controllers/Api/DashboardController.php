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

            $activeUsersCount = DB::connection('sqlsrv')
                ->table('users')
                ->where('is_active', 1)
                ->count();

            return response()->json([
                'status' => 'success',
                'count' => $activeUsersCount
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Could not fetch active users count.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }


    public function getUploadCount(): JsonResponse
    {
        try {
            $uploadCount = DB::connection('sqlsrv')
                ->table('doc_package_revisions')
                ->count();

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
            $downloadCount = DB::connection('sqlsrv')
                ->table('activity_logs')
                ->where('activity_code', 'DOWNLOAD')
                ->count('activity_code');

            return response()->json([
                'status' => 'success',
                'count' => $downloadCount
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
            $docCount = DB::connection('sqlsrv')
                ->table('doc_package_revision_files')
                ->count('filename');

            return response()->json([
                'status' => 'success',
                'count' => $docCount
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

        $query = DB::connection('sqlsrv')
            ->table('doctype_groups')
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

        $query = DB::connection('sqlsrv')
            ->table('doctype_subcategories')
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


    public function getCustomers(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::connection('sqlsrv')
            ->table('customers')
            ->select('id', 'code');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $totalCount = $query->count();

        $customers = $query->orderBy('code', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedResults = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'text' => $customer->code,
            ];
        });

        return response()->json([
            'results' => $formattedResults,
            'pagination' => ['more' => ($page * $resultsPerPage) < $totalCount]
        ]);
    }

    public function getModels(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::connection('sqlsrv')
            ->table('models')
            ->select(
                DB::raw('MIN(id) as id'),
                'name',
                DB::raw('MIN(customer_id) as customer_id')
            )
            ->groupBy('name');

        if ($request->filled('customer_ids')) {
            $query->whereIn('customer_id', $request->customer_ids);
        }

        if (!empty($searchTerm)) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();

        $models = $query->orderBy('name', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedResults = $models->map(function ($model) {
            return [
                'id'   => $model->id,
                'text' => $model->name,
                'customer_id' => $model->customer_id,
            ];
        });

        return response()->json([
            'results' => $formattedResults,
            'pagination' => [
                'more' => ($page * $resultsPerPage) < $totalCount
            ]
        ]);
    }



    public function getPartGroup(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::connection('sqlsrv')
            ->table('part_groups')
            ->select('code_part_group')
            ->groupBy('code_part_group');

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
                'id'   => uniqid(),
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

        $query = DB::connection('sqlsrv')
            ->table('project_status')
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

        $data = DB::connection('sqlsrv')
            ->table('activity_logs')
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
    }

    public function getDataActivityLog(Request $request): JsonResponse
    {
        try {
            $query = DB::connection('sqlsrv')
                ->table('activity_logs')
                ->join('users', 'activity_logs.user_id', '=', 'users.id')
                ->where('activity_logs.activity_code', 'UPLOAD')
                ->select(
                    'activity_logs.id',
                    'activity_logs.user_id',
                    'users.name as user_name',
                    'activity_logs.activity_code',
                    'activity_logs.meta',
                    'activity_logs.created_at'
                );

            if ($request->filled('date_start') && $request->filled('date_end')) {
                $query->whereBetween('activity_logs.created_at', [
                    $request->date_start . ' 00:00:00',
                    $request->date_end . ' 23:59:59'
                ]);
            }

            if ($request->filled('customer')) {
                $customers = $request->customer;
                $query->where(function ($q) use ($customers) {
                    foreach ($customers as $cust) {
                        $q->orWhereRaw("JSON_VALUE(activity_logs.meta, '$.customer_code') = ?", [$cust]);
                    }
                });
            }

            if ($request->filled('model')) {
                $models = $request->model;
                $query->where(function ($q) use ($models) {
                    foreach ($models as $mod) {
                        $q->orWhereRaw("JSON_VALUE(activity_logs.meta, '$.model_name') = ?", [$mod]);
                    }
                });
            }

            if ($request->filled('part_group')) {
                $partGroups = $request->part_group;
                $query->where(function ($q) use ($partGroups) {
                    foreach ($partGroups as $pg) {
                        $q->orWhereRaw("JSON_VALUE(activity_logs.meta, '$.part_group_code') = ?", [$pg]);
                    }
                });
            }


            $data = $query->orderBy('activity_logs.created_at', 'desc')
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
        // --- 1. MEMBANGUN QUERY DASAR ---
        $query = DB::connection('sqlsrv')
            ->table('doc_packages as dp')
            // Join sesuai Raw SQL yang diminta
            ->join('products as p_origin', 'dp.product_id', '=', 'p_origin.id')
            ->join('products as p', 'p_origin.group_id', '=', 'p.group_id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('doctype_subcategories as ds', 'dp.doctype_subcategory_id', '=', 'ds.id')
            ->join('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->join('doc_package_revisions as dpr', 'dpr.id', '=', 'dp.current_revision_id') // Perbaikan: dpr.id = dp.current_revision_id
            ->join('project_status as ps', 'm.status_id', '=', 'ps.id')

            // Filter Utama (Hardcoded sesuai Raw SQL)
            ->where('dp.is_delete', 0)
            ->where('p.is_count', 1)
            ->where('dpr.is_finish', 1)
            ->where('dpr.is_obsolete', 0)
            ->where('ds.name', 'Go Mfg')
            ->whereNotNull('dp.current_revision_id');

        // --- 2. LOGIKA AKUMULASI TANGGAL ---
        // Agar data terakumulasi (misal: total sampai tgl 4 adalah hasil tgl 3 + tgl 4),
        // kita HANYA membatasi batas atas (date_end). 
        // Batas bawah (date_start) tidak dipakai saat menghitung count agar history tetap terbawa.

        if ($request->filled('date_end')) {
            $query->whereDate('dp.created_at', '<=', $request->date_end);
        }

        // Catatan: Jika Anda ingin memfilter list 'Project' berdasarkan range tanggal, 
        // sebaiknya filter itu diterapkan pada 'plan' atau atribut lain, bukan pada 'actual' count-nya.

        // --- 3. FILTER DINAMIS (Dari Request) ---

        if ($request->filled('project_status') && $request->project_status !== 'ALL') {
            $query->where('ps.name', $request->project_status);
        }

        if ($request->filled('customer')) {
            // Asumsi input 'customer' adalah array
            $query->whereIn('c.code', $request->customer);
        }

        if ($request->filled('model')) {
            $query->whereIn('m.name', $request->model);
        }

        if ($request->filled('part_group')) {
            $query->whereIn('pg.code_part_group', $request->part_group);
        }

        // --- 4. SELECT & GROUPING ---
        // Melakukan count(dp.id) untuk mendapatkan total aktual terakumulasi
        $query->select([
            'c.code as customer_name',
            'm.name as model',
            'ps.name as project_status',
            'pg.code_part_group as part_group',
            'pg.planning as plan_count',
            DB::raw('COUNT(dp.id) as actual_count'), // Menghitung total baris yang lolos filter
            DB::raw('MAX(dp.created_at) as latest_upload') // Opsional: untuk melihat kapan terakhir update
        ]);

        $query->groupBy(
            'c.code',
            'm.name',
            'ps.name',
            'pg.code_part_group',
            'pg.planning'
        );

        // --- 5. SORTING ---
        // Sort dilakukan setelah grouping
        $sortBy = $request->input('sort_by', 'plan');

        if ($sortBy === 'actual') {
            $query->orderBy('actual_count', 'desc');
        } else {
            $query->orderBy('plan_count', 'desc'); // Default sort by Plan
        }

        // Secondary sort biar rapi (sesuai raw SQL yang minta part_no, tapi karena grouping kita pakai part_group)
        $query->orderBy('part_group', 'asc');

        // --- 6. EKSEKUSI & MAPPING ---
        $results = $query->get();

        $results = $results->map(function ($item) {
            $plan = (float) $item->plan_count;
            $actual = (float) $item->actual_count;

            // Hitung Persentase
            $percentage = $plan > 0 ? ($actual / $plan) * 100 : 0;
            $item->percentage = number_format($percentage, 1);

            // Alias Project Status (Sesuai request kode sebelumnya)
            if ($item->project_status === 'Feasibility Study') {
                $item->project_status = 'FS';
            } elseif ($item->project_status === 'Project') {
                $item->project_status = 'PR';
            } elseif ($item->project_status === 'Regular') {
                $item->project_status = 'RE';
            }

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }


    public function getPhaseStatus(Request $request)
    {
        $query = DB::connection('sqlsrv')
            ->table('UploadActual')
            ->select(
                'customer_name',
                'project_status',
                DB::raw('COUNT(*) as total')
            );

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        if ($request->filled('project_status') && $request->project_status !== 'ALL') {
            $query->where('project_status', $request->project_status);
        }

        if ($request->filled('customer')) {
            $query->whereIn('customer_name', $request->customer);
        }

        if ($request->filled('model')) {
            $query->whereIn('model_name', $request->model);
        }

        if ($request->filled('part_group')) {
            $query->whereIn('part_group', $request->part_group);
        }

        $query->groupBy('customer_name', 'project_status');

        $results = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    public function getDiskSpace(Request $request)
    {
        try {
            $diskPath = base_path('/');

            $totalSpace = @disk_total_space($diskPath);
            $freeSpace = @disk_free_space($diskPath);

            if ($totalSpace === false || $freeSpace === false) {
                throw new \Exception('Could not read disk space. Check server permissions or configuration.');
            }

            $usedSpace = $totalSpace - $freeSpace;

            return response()->json([
                'status' => 'success',
                'total'  => $this->formatBytes($totalSpace),
                'used'   => $this->formatBytes($usedSpace),
                'free'   => $this->formatBytes($freeSpace),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'total'  => 'N/A',
                'used'   => 'N/A',
                'free'   => 'N/A',
            ], 500);
        }
    }

    public function getSaveEnv(Request $request)
    {
        $query = DB::connection('sqlsrv')
            ->table('activity_logs as al')
            ->leftJoin('doc_package_revision_files as dprf', 'al.revision_id', '=', 'dprf.revision_id')
            ->where('al.activity_code', 'DOWNLOAD')
            ->whereIn('dprf.category', ['2D', 'ECN']);


        if ($request->filled('date_end')) {
            $query->where('al.created_at', '<=', $request->date_end . ' 23:59:59');
        }

        if ($request->filled('customer')) {
            $customers = $request->customer;
            $query->where(function ($q) use ($customers) {
                foreach ($customers as $cust) {
                    $q->orWhereRaw("JSON_VALUE(al.meta, '$.customer_code') = ?", [$cust]);
                }
            });
        }

        if ($request->filled('model')) {
            $models = $request->model;
            $query->where(function ($q) use ($models) {
                foreach ($models as $mod) {
                    $q->orWhereRaw("JSON_VALUE(al.meta, '$.model_name') = ?", [$mod]);
                }
            });
        }

        if ($request->filled('part_group')) {
            $partGroups = $request->part_group;
            $query->where(function ($q) use ($partGroups) {
                foreach ($partGroups as $pg) {
                    $q->orWhereRaw("JSON_VALUE(al.meta, '$.part_group_code') = ?", [$pg]);
                }
            });
        }

        $data = $query->selectRaw('COUNT(al.activity_code) as paper, COUNT(al.activity_code) * 186 as harga')
            ->first();

        $paperCount = $data->paper ?? 0;
        $hargaTotal = $data->harga ?? 0;

        $saveTree = $paperCount / 80000;
        $co2Reduced = $saveTree * 22;

        return response()->json([
            'paper'       => $paperCount,
            'harga'       => $hargaTotal,
            'save_tree'   => $saveTree,
            'co2_reduced' => $co2Reduced
        ]);
    }
}
