<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActivityLogExport;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('file_management.activity_logs');
    }

    public function list(Request $request): JsonResponse
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';
        $orderColumnIndex = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        $columns = [
            0 => 'created_at',    // No (biasanya tidak disort, fallback ke date)
            1 => 'created_at',    // Date
            2 => 'user_name',     // User
            3 => 'activity_code', // Activity
            4 => 'ecn_no',        // ECN 
            5 => 'meta',          // Description
        ];

        $orderColumnName = $columns[$orderColumnIndex] ?? 'created_at';

        // Base query
        $query = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->leftJoin('doc_package_revisions as dpr', 'al.revision_id', '=', 'dpr.id')
            ->select(
                'al.id',
                'al.created_at',
                'al.activity_code',
                'al.meta',
                'u.name as user_name',
                'al.user_id',
                'dpr.ecn_no'
            );

        // Filters
        if ($request->filled('user_id') && $request->user_id !== 'All') {
            $query->where('al.user_id', $request->user_id);
        }

        if ($request->filled('activity_code') && $request->activity_code !== 'All') {
            $query->where('al.activity_code', $request->activity_code);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('al.created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('al.created_at', '<=', $request->date_end);
        }

        // Search Logic 
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('u.name', 'like', "%{$searchValue}%")
                  ->orWhere('al.activity_code', 'like', "%{$searchValue}%")
                  ->orWhere('al.meta', 'like', "%{$searchValue}%")
                  ->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%");
            });
        }

        $recordsTotal = DB::table('activity_logs')->count();
        $recordsFiltered = $query->count();

        // Ordering Logic
        if ($orderColumnName === 'user_name') {
            $query->orderBy('u.name', $orderDir);
        } elseif ($orderColumnName === 'ecn_no') {
            $query->orderBy('dpr.ecn_no', $orderDir);
        } else {
            // Default sort (activity_logs columns)
            $query->orderBy('al.' . $orderColumnName, $orderDir);
        }

        $data = $query->skip($start)
            ->take($length)
            ->get()
            ->map(function ($row) {
                $meta = json_decode($row->meta, true) ?? [];
                $row->meta = $meta;
                $row->ecn_no = $row->ecn_no ?? '-';
                return $row;
            });

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function filters(Request $request): JsonResponse
    {
        if ($request->filled('select2')) {
            $field = $request->get('select2');
            $q = trim($request->get('q', ''));
            $page = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            $items = collect();
            $total = 0;

            switch ($field) {
                case 'user':
                    $builder = DB::table('users')
                        ->select('id', 'name as text')
                        ->when($q, fn($x) => $x->where('name', 'like', "%{$q}%"))
                        ->orderBy('name');
                    
                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'activity_code':
                    $builder = DB::table('activity_logs')
                        ->select('activity_code as id', 'activity_code as text')
                        ->distinct()
                        ->when($q, fn($x) => $x->where('activity_code', 'like', "%{$q}%"))
                        ->orderBy('activity_code');

                    $total = (clone $builder)->count(); 
                    $items = $builder->forPage($page, $perPage)->get();
                    break;
            }

            if ($page === 1) {
                $items = collect([['id' => 'All', 'text' => 'All']])->merge($items);
            }

            $effectiveTotal = $total + ($page === 1 ? 1 : 0);
            $more = ($effectiveTotal > $page * $perPage);

            return response()->json([
                'results' => array_values($items->toArray()),
                'pagination' => ['more' => $more]
            ]);
        }

        return response()->json([]);
    }

    public function export(Request $request)
    {
        // Nama file dengan timestamp
        $fileName = 'activity_log_summary_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new ActivityLogExport($request), $fileName);
    }
}
