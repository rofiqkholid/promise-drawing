<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Models;
use App\Models\DocTypeSubCategories;
use App\Models\Customers;
use App\Models\DoctypeGroups;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use App\Exports\ReceiptExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use App\Models\DocPackageRevisionFile;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Imagick;
use ImagickDraw;
use ImagickPixel;

class ReceiptController extends Controller
{

    public function receiptFilters(Request $request): JsonResponse
    {
        $userId = Auth::user()->id ?? 1;
        
        // Identify User Role / Supplier ID exactly like receiptList
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
            $userRole = DB::table('user_roles')->where('user_id', $userId)->first();
            if (!$userRole) return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            $userRoleId = (string) $userRole->role_id;
        }

        if ($request->filled('select2')) {
            $field   = $request->get('select2');
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            $customerCode = $request->get('customer_code');
            $docTypeName  = $request->get('doc_type');

            // Shared logic for finding available revisions
            $base = DB::table('doc_package_revisions as dpr')
                ->join('package_shares as ps', 'dpr.id', '=', 'ps.revision_id')
                ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
                ->where('ps.supplier_id', $userRoleId)
                ->where('dpr.revision_status', 'approved');

            $items = collect();
            $totalCount = 0;

            $feasId = $this->getFeasibilityStatusId();

            switch ($field) {
                case 'customer':
                    $query = $base->join('customers as c', 'dp.customer_id', '=', 'c.id')
                        ->join('models as m_f', 'dp.model_id', '=', 'm_f.id')
                        ->where('m_f.status_id', '<>', $feasId)
                        ->when($q, fn($x) => $x->where('c.code', 'like', "%{$q}%"));
                    
                    $totalCount = (clone $query)->count(DB::raw('DISTINCT(c.code)'));

                    $items = $query->select('c.code as id', 'c.code as text')
                        ->distinct()
                        ->orderBy('c.code')
                        ->forPage($page, $perPage)
                        ->get();

                    if ($page === 1) {
                        $hasFeasibility = (clone $base)->join('models as m_fs', 'dp.model_id', '=', 'm_fs.id')
                            ->where('m_fs.status_id', $feasId)->exists();
                        if ($hasFeasibility) {
                            $items->prepend(['id' => 'FEASIBILITY STUDY', 'text' => 'FEASIBILITY STUDY']);
                        }
                    }
                    break;

                case 'model':
                    $query = $base->join('models as m', 'dp.model_id', '=', 'm.id')
                        ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                        ->where('m.status_id', '<>', $feasId)
                        ->when($customerCode && $customerCode !== 'All' && $customerCode !== 'FEASIBILITY STUDY', fn($x) => $x->where('c.code', $customerCode))
                        ->when($q, fn($x) => $x->where('m.name', 'like', "%{$q}%"));

                    if ($customerCode === 'FEASIBILITY STUDY') {
                        // If filtered by feasibility customer, only show feasibility models (masked) in this dropdown?
                        // Actually, better to just return "FEASIBILITY STUDY" as the only model option.
                        $items = collect([['id' => 'FEASIBILITY STUDY', 'text' => 'FEASIBILITY STUDY']]);
                        $totalCount = 1;
                    } else {
                        $totalCount = (clone $query)->count(DB::raw('DISTINCT(m.name)'));
                        $items = $query->select('m.name as id', 'm.name as text')
                            ->distinct()
                            ->orderBy('m.name')
                            ->forPage($page, $perPage)
                            ->get();
                    }
                    break;

                case 'doc_type':
                    $query = $base->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                        ->when($q, fn($x) => $x->where('dtg.name', 'like', "%{$q}%"));

                    $totalCount = (clone $query)->count(DB::raw('DISTINCT(dtg.name)'));

                    $items = $query->select('dtg.name as id', 'dtg.name as text')
                        ->distinct()
                        ->orderBy('dtg.name')
                        ->forPage($page, $perPage)
                        ->get();
                    break;

                case 'category':
                    $query = $base->join('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
                        ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                        ->when($docTypeName && $docTypeName !== 'All', fn($x) => $x->where('dtg.name', $docTypeName))
                        ->when($q, fn($x) => $x->where('dsc.name', 'like', "%{$q}%"));

                    $totalCount = (clone $query)->count(DB::raw('DISTINCT(dsc.name)'));

                    $items = $query->select('dsc.name as id', 'dsc.name as text')
                        ->distinct()
                        ->orderBy('dsc.name')
                        ->forPage($page, $perPage)
                        ->get();
                    break;

                default:
                    return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }

            if ($page === 1) {
                // Check if 'All' is already in items (e.g. from model logic)
                $hasAll = false;
                foreach($items as $it) {
                    if (is_array($it) && $it['id'] === 'All') $hasAll = true;
                    if (is_object($it) && $it->id === 'All') $hasAll = true;
                }
                if (!$hasAll) {
                    $items->prepend(['id' => 'All', 'text' => 'All']);
                }
            }

            $more = ($totalCount > $page * $perPage);

            return response()->json([
                'results'    => array_values($items->toArray()),
                'pagination' => ['more' => $more]
            ]);
        }

        return response()->json([
            'customers'  => [], 'models' => [], 'doc_types' => [], 'categories' => []
        ]);
    }
    public function choiseFilter(Request $request): JsonResponse
    {
        $userId = Auth::user()->id ?? 1;

        // 1. Identify Supplier ID
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
            $userRole = DB::table('user_roles')->where('user_id', $userId)->first();
            if (!$userRole) {
                return response()->json([
                    'totalReceived' => 0,
                    'totalActive' => 0,
                    'totalExpired' => 0,
                    'totalReceivedToday' => 0,
                ]);
            }
            $userRoleId = (string) $userRole->role_id;
        }

        $baseQuery = DB::table('package_shares as ps')
            ->join('doc_package_revisions as dpr', 'ps.revision_id', '=', 'dpr.id')
            ->where('ps.supplier_id', $userRoleId)
            ->where('dpr.revision_status', 'approved');

        $feasId = $this->getFeasibilityStatusId();
        // Apply filters if any
        if ($request->filled('customer') && $request->customer !== 'All') {
            $baseQuery->join('doc_packages as dp_c', 'dpr.package_id', '=', 'dp_c.id')
                ->join('customers as c_c', 'dp_c.customer_id', '=', 'c_c.id')
                ->join('models as m_c', 'dp_c.model_id', '=', 'm_c.id');
            
            if ($request->customer === 'FEASIBILITY STUDY') {
                $baseQuery->where('m_c.status_id', $feasId);
            } else {
                $baseQuery->where('c_c.code', $request->customer)
                          ->where('m_c.status_id', '<>', $feasId);
            }
        }

        if ($request->filled('model') && $request->model !== 'All') {
            if (!$request->filled('customer') || $request->customer === 'All') {
                $baseQuery->join('doc_packages as dp_m', 'dpr.package_id', '=', 'dp_m.id')
                    ->join('models as m_m', 'dp_m.model_id', '=', 'm_m.id');
            } else {
                $baseQuery->alias('m_c', 'm_m'); 
            }
            
            if ($request->model === 'FEASIBILITY STUDY') {
                $baseQuery->where('m_m.status_id', $feasId);
            } else {
                $baseQuery->where('m_m.name', $request->model)
                          ->where('m_m.status_id', '<>', $feasId);
            }
        }

        $totalReceived = (clone $baseQuery)->count();
        $totalActive   = (clone $baseQuery)
            ->where(function ($q) {
                $q->whereNull('ps.expired_at')
                    ->orWhere('ps.expired_at', '>', now());
            })->count();
        $totalExpired  = (clone $baseQuery)
            ->whereNotNull('ps.expired_at')
            ->where('ps.expired_at', '<=', now())->count();
        
        $totalReceivedToday = (clone $baseQuery)
            ->whereDate('ps.shared_at', Carbon::today())->count();

        return response()->json([
            'totalReceived'      => $totalReceived,
            'totalActive'        => $totalActive,
            'totalExpired'       => $totalExpired,
            'totalReceivedToday' => $totalReceivedToday,
        ]);
    }

    public function receiptList(Request $request)
    {
        $userId = Auth::user()->id ?? 1;

        // 1. Ambil Role ID (Supplier ID) user yang login
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
            // Fallback ke user_roles jika tidak ada di user_supplier
            $userRole = DB::table('user_roles')->where('user_id', $userId)->first();

            if (!$userRole) {
                return response()->json([
                    "draw"            => (int) $request->get('draw'),
                    "recordsTotal"    => 0,
                    "recordsFiltered" => 0,
                    "data"            => [],
                ]);
            }
            $userRoleId = (string) $userRole->role_id;
        }

        // 2. Setup Parameter DataTables
        $start       = (int) $request->get('start', 0);
        $length      = (int) $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';

        $orderColumnIndex = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir         = $request->get('order')[0]['dir'] ?? 'desc';
        $orderColumnName  = $request->get('columns')[$orderColumnIndex]['name'] ?? 'ps.shared_at';

        // 3. Subquery Approval Terakhir (Window Function)
        $latestPa = DB::table('package_approvals as pa')
            ->select(
                'pa.id',
                'pa.revision_id',
                'pa.requested_at',
                'pa.decision',
                'pa.decided_by'
            )
            ->selectRaw("
            ROW_NUMBER() OVER (
                PARTITION BY pa.revision_id
                ORDER BY pa.requested_at DESC, pa.id DESC
            ) as rn
        ");

        // 4. Query Utama (DEFINISI VARIABEL $query ADA DI SINI)
        $query = DB::table('doc_package_revisions as dpr')
            // JOIN UTAMA: Ke tabel pivot package_shares
            ->join('package_shares as ps', 'dpr.id', '=', 'ps.revision_id')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', 'dsc.id')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft')
            ->where('dpr.revision_status', 'approved');

        // Subquery for Partners
        $partnersSub = DB::table('products as p2')
            ->select('group_id', DB::raw("STRING_AGG(CAST(part_no AS VARCHAR(MAX)), ',') WITHIN GROUP (ORDER BY part_no) as partners"))
            ->whereNotNull('group_id')
            ->where('is_delete', 0)
            ->groupBy('group_id');

        $query->leftJoinSub($partnersSub, 'p_extra', function ($join) {
            $join->on('p.group_id', '=', 'p_extra.group_id');
        });

        // 5. Filter Spesifik Supplier & Expired Date
        $query->where('ps.supplier_id', $userRoleId);
        
        $accessMode = $request->get('access', 'All');
        if ($accessMode === 'active') {
            $query->where(function ($q) {
                $q->whereNull('ps.expired_at')
                  ->orWhere('ps.expired_at', '>', now());
            });
        } elseif ($accessMode === 'expired') {
            $query->whereNotNull('ps.expired_at')
                  ->where('ps.expired_at', '<=', now());
        }
        // If 'All', no expired filter applied here

        // 6. Hitung Total Record (Sebelum Filter Pencarian)
        $recordsTotal = (clone $query)->count();

        // 7. Filter Pencarian (Customer, Model, dll)
        $feasId = $this->getFeasibilityStatusId();
        if ($request->filled('customer') && $request->customer !== 'All') {
            if ($request->customer === 'FEASIBILITY STUDY') {
                $query->where('m.status_id', $feasId);
            } else {
                $query->where('c.code', $request->customer)
                      ->where('m.status_id', '<>', $feasId);
            }
        }
        if ($request->filled('model') && $request->model !== 'All') {
            if ($request->model === 'FEASIBILITY STUDY') {
                $query->where('m.status_id', $feasId);
            } else {
                $query->where('m.name', $request->model)
                      ->where('m.status_id', '<>', $feasId);
            }
        }
        if ($request->filled('doc_type') && $request->doc_type !== 'All') {
            $query->where('dtg.name', $request->doc_type);
        }
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('dsc.name', $request->category);
        }

        // 8. Global Search (Search Bar)
        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue, $feasId) {
                // When searching, we should also handle feasibility study
                // If the search term is 'feasibility', it will match via the string concat or doctype etc.
                // But we should protect real customer/model names from being searchable if they are feasibility.
                
                $q->where(function($sq) use ($searchValue, $feasId) {
                      $sq->where('m.status_id', '<>', $feasId)
                         ->where(function($ssq) use ($searchValue) {
                             $ssq->where('c.code', 'like', "%{$searchValue}%")
                                 ->orWhere('m.name', 'like', "%{$searchValue}%");
                         });
                })
                ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                ->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%")
                ->orWhereRaw("
                CONCAT(
                    CASE WHEN m.status_id = ? THEN 'FEASIBILITY STUDY' ELSE c.code END,' ',
                    CASE WHEN m.status_id = ? THEN 'FEASIBILITY STUDY' ELSE m.name END,' ',
                    dtg.name,' ',
                    COALESCE(dsc.name,''),' ',
                    COALESCE(p.part_no,''),' ',
                    dpr.revision_no
                ) LIKE ?
            ", [$feasId, $feasId, "%{$searchValue}%"]);
            });
        }

        // 9. Hitung Total Setelah Filter
        $recordsFiltered = (clone $query)->count();

        $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'm.status_id',
            'dtg.name as doctype_group',
            'dsc.name as doctype_subcategory',
            'p.part_no',
            'p_extra.partners as partners_raw',
            'dpr.ecn_no',
            'dpr.revision_no',
            'crl.label as revision_label_name',
            'ps.shared_at as received',
            'ps.expired_at'
        );

        // 11. Sorting
        $orderWhitelist = [
            'ps.shared_at', // update sorting pakai kolom tabel share
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
            'dpr.revision_no',
            'dpr.ecn_no',
        ];

        // Map request column name ke kolom DB yang benar jika masih pakai alias lama
        if ($orderColumnName === 'dpr.shared_at') {
            $orderColumnName = 'ps.shared_at';
        }

        $orderBy = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'ps.shared_at';
        $orderDirection = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

        // --- Logic Export Summary ---
        if ($request->has('export')) {
            $exportData = $query->orderBy($orderBy, $orderDirection)->get();
            $rows = [];
            $feasibilityStatusId = $this->getFeasibilityStatusId();

            foreach ($exportData as $r) {
                $isFeasibility = $feasibilityStatusId && (int)$r->status_id === (int)$feasibilityStatusId;
                $rows[] = [
                    $isFeasibility ? 'FEASIBILITY STUDY' : $r->customer,
                    $isFeasibility ? 'FEASIBILITY STUDY' : $r->model,
                    $r->part_no,
                    $r->doctype_group,
                    $r->doctype_subcategory,
                    $r->ecn_no,
                    $r->revision_no,
                    $r ? Carbon::parse($r->received)->format('Y-m-d H:i') : '-',
                    $r->expired_at ? Carbon::parse($r->expired_at)->format('Y-m-d') : 'Permanent'
                ];
            }
            return Excel::download(new ReceiptExport($rows, now()->format('Y-m-d H:i')), 'receipt_summary_' . now()->format('YmdHis') . '.xlsx');
        }

        // 12. Pagination & Execution
        $data = $query
            ->orderBy($orderBy, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        // 13. Format Data (Encrypt ID & Handle Partners)
        $feasibilityStatusId = $this->getFeasibilityStatusId();
        $data = $data->map(function ($row) use ($feasibilityStatusId) {
            $row->hash = encrypt($row->id);

            // Handle Feasibility Study Masking
            if ($feasibilityStatusId && (int)$row->status_id === (int)$feasibilityStatusId) {
                $row->customer = 'FEASIBILITY STUDY';
                $row->model = 'FEASIBILITY STUDY';
            }
            
            // Handle Partners logic
            $row->partners = null;
            if (isset($row->partners_raw) && $row->partners_raw) {
                $parts = explode(',', $row->partners_raw);
                $others = array_filter($parts, fn($p) => trim($p) !== trim($row->part_no));
                if (!empty($others)) {
                    $row->partners = implode(',', $others);
                }
            }
            
            return $row;
        });

        // 14. Get KPIs for this view
        $kpis = [
            'total'   => $recordsTotal,
            'active'  => (clone $query)->where(function($q) {
                             $q->whereNull('ps.expired_at')->orWhere('ps.expired_at', '>', now());
                         })->count(),
            'expired' => (clone $query)->whereNotNull('ps.expired_at')
                             ->where('ps.expired_at', '<=', now())->count(),
            'today'   => (clone $query)->whereDate('ps.shared_at', Carbon::today())->count(),
        ];

        // 15. Return JSON
        return response()->json([
            "draw"            => (int) $request->get('draw'),
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
            "kpis"            => $kpis,
        ]);
    }

    public function showDetail(string $id)
    {
        $revisionId = null;

        if (ctype_digit($id)) {
            $revisionId = (int) $id;
        } else {
            try {
                $revisionId = decrypt(hex2bin($id));
            } catch (\Throwable $e) {
                try {
                    $revisionId = decrypt($id);
                } catch (\Throwable $ex) {
                    try {
                        $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $id));
                        $revisionId = decrypt($decoded);
                    } catch (\Throwable $ex2) {
                        abort(404, 'Invalid approval ID.');
                    }
                }
            }
        }

        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $revisionId)
            ->first();

        if (!$revision) {
            abort(404, 'Approval request not found.');
        }

        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->leftJoin('users as u', 'u.id', '=', 'dp.created_by')
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'm.status_id',
                'p.part_no',
                'dp.created_at',
                'u.name as uploader_name'
            )
            ->first();

        if (!$package) {
            abort(404, 'Package not found.');
        }

        $files = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->select('id', 'filename as name', 'category', 'storage_path')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);
                    return ['name' => $item->name, 'url' => $url];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $logs = $this->buildApprovalLogs($revision->package_id, $revisionId);

        $uploaderName   = $package->uploader_name ?? 'System';
        $uploadedAt     = optional($package->created_at);
        $hasUploadedLog = $logs->contains(fn($log) => ($log['action'] ?? '') === 'uploaded');

        if ($uploadedAt && !$hasUploadedLog) {
            $logs->push([
                'id'      => 0,
                'action'  => 'uploaded',
                'user'    => $uploaderName,
                'note'    => 'Package uploaded',
                'time'    => $uploadedAt->format('Y-m-d H:i'),
                'time_ts' => $uploadedAt->timestamp,
            ]);

            $logs = $logs->sortByDesc('time_ts')->values();
        }

        $feasibilityStatusId = $this->getFeasibilityStatusId();
        $isFeasibility = $feasibilityStatusId && (int)$package->status_id === (int)$feasibilityStatusId;

        $detail = [
            'metadata' => [
                'customer'    => $isFeasibility ? 'FEASIBILITY STUDY' : $package->customer,
                'model'       => $isFeasibility ? 'FEASIBILITY STUDY' : $package->model,
                'part_no'     => $package->part_no,
                'revision'    => 'Rev-' . $revision->revision_no,
                'uploader'    => $uploaderName,
                'uploaded_at' => $uploadedAt ? $uploadedAt->format('Y-m-d H:i') : null,
            ],
            'status'       => match ($revision->revision_status) {
                'pending'  => 'Waiting',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                default    => ucfirst($revision->revision_status ?? 'Waiting'),
            },
            'files'        => $files,
            'activityLogs' => $logs,
        ];

        $hash = encrypt($revisionId);
        $userDeptCode = (Auth::check() && Auth::user()->id_dept) ? DB::table('departments')->where('id', Auth::user()->id_dept)->value('code') : null;

        return view('receipt.receipt_detail', [
            'receiptId' => $hash,
            'detail'     => $detail,
            'userDeptCode' => $userDeptCode
        ]);
    }

    public function receiptHistoryList(Request $request)
    {
        $userId = Auth::user()->id ?? 1;

        // ... (Logika User Role Sama seperti sebelumnya) ...
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
            $userRole = DB::table('user_roles')->where('user_id', $userId)->first();
            if (!$userRole) return response()->json(["data" => [], "recordsTotal" => 0, "recordsFiltered" => 0]);
            $userRoleId = (string) $userRole->role_id;
        }

        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';

        $query = DB::table('doc_package_revisions as dpr')
            ->join('package_shares as ps', 'dpr.id', '=', 'ps.revision_id')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', 'dsc.id')
            ->where('dpr.revision_status', 'approved');

        // Filter Expired & Milik Supplier Ini
        $query->where('ps.supplier_id', $userRoleId)
            ->whereNotNull('ps.expired_at')
            ->where('ps.expired_at', '<', now());

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $query)->count();

        $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'm.status_id',
            'dtg.name as doc_type',
            'dsc.name as category',
            'p.part_no',
            'dpr.revision_no as revision',
            'ps.shared_at',
            'ps.expired_at'
        );

        $data = $query
            ->orderBy('ps.expired_at', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        // --- TAMBAHAN PENTING: Generate Hash ---
        $feasibilityStatusId = $this->getFeasibilityStatusId();
        $data->transform(function ($row) use ($feasibilityStatusId) {
            // Gunakan bin2hex agar aman di URL
            $row->hash = bin2hex(encrypt($row->id));

            // Masking
            if ($feasibilityStatusId && (int)$row->status_id === (int)$feasibilityStatusId) {
                $row->customer = 'FEASIBILITY STUDY';
                $row->model = 'FEASIBILITY STUDY';
            }

            return $row;
        });

        return response()->json([
            "draw"            => (int) $request->get('draw'),
            "recordsTotal"    => $recordsFiltered,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
        ]);
    }

    private function buildApprovalLogs(int $packageId, ?int $revisionId)
    {
        $q = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.user_id')
            ->where(function ($w) use ($packageId, $revisionId) {
                $w->where(function ($x) use ($packageId) {
                    $x->where('al.scope_type', 'package')
                        ->where('al.scope_id', $packageId);
                });

                if (!empty($revisionId)) {
                    $w->orWhere(function ($x) use ($revisionId) {
                        $x->where('al.scope_type', 'revision')
                            ->where('al.scope_id', $revisionId);
                    });

                    $w->orWhere(function ($x) use ($revisionId) {
                        $x->where('al.scope_type', 'package')
                            ->where('al.scope_id', $revisionId);
                    });
                }
            })
            ->where(function ($w) use ($revisionId) {
                if (!empty($revisionId)) {
                    $w->whereNull('al.revision_id')
                        ->orWhere('al.revision_id', $revisionId);
                } else {
                    $w->whereNull('al.revision_id');
                }
            })
            ->orderByDesc('al.created_at')
            ->orderByDesc('al.id')
            ->limit(200);

        return $q->get([
            'al.id',
            'al.activity_code',
            'al.meta',
            'al.created_at',
            'u.name as user_name'
        ])
            ->map(function ($row) {
                $code = strtoupper($row->activity_code ?? '');
                $action = str_starts_with($code, 'UPLOAD')   ? 'uploaded'
                    : (str_starts_with($code, 'APPROVE')    ? 'approved'
                        : (str_starts_with($code, 'REJECT')     ? 'rejected'
                            : (str_starts_with($code, 'ROLLBACK')   ? 'rollbacked'
                                : strtolower($code ?: 'info'))));

                $meta = $row->meta;
                if (is_string($meta)) {
                    try {
                        $meta = json_decode($meta, true, 512, JSON_THROW_ON_ERROR);
                    } catch (\Throwable) {
                        $meta = null;
                    }
                }

                return [
                    'id'      => (int) $row->id,
                    'action'  => $action,
                    'user'    => $row->user_name ?? 'System',
                    'note'    => is_array($meta) ? ($meta['note'] ?? '') : ($meta ?: ''),
                    'time'    => optional($row->created_at)->format('Y-m-d H:i'),
                    'time_ts' => optional($row->created_at)?->timestamp ?? 0,
                ];
            });
    }

    /**
     * Get revision detail as JSON for receipt detail page
     */
    public function getRevisionDetailJson(Request $request, $id)
    {
        // 1. Dekripsi ID
        $originalEncryptedId = $id;
        try {
            $decrypted_id = decrypt(str_replace('-', '=', $id));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid revision ID.'], 404);
        }

        // 2. Cari Revisi
        $revision = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->leftJoin('users as ou', 'ou.id', '=', 'dpr.obsolete_by')
            ->leftJoin('departments as od', 'od.id', '=', 'ou.id_dept')
            ->where('dpr.id', $decrypted_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select(
                'dpr.*',
                'crl.label as revision_label',
                'ou.name as obsolete_name',
                'od.code as obsolete_dept'
            )
            ->first();

        if (!$revision) {
            return response()->json(['success' => false, 'message' => 'Receipt revision not found.'], 404);
        }

        // 3. Tentukan User Role (Supplier)
        $userId = Auth::user()->id ?? 1;
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
            $userRole = DB::table('user_roles')->where('user_id', $userId)->first();
            if (!$userRole) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            $userRoleId = (string) $userRole->role_id;
        }

        // 4. Cek Akses via Tabel Pivot (package_shares)
        $shareInfo = DB::table('package_shares')
            ->where('revision_id', $decrypted_id)
            ->where('supplier_id', $userRoleId)
            ->first();

        if (!$shareInfo) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or Link Expired.'], 403);
        }

        // Cek Expired
        if ($shareInfo->expired_at && Carbon::parse($shareInfo->expired_at)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Link Expired.'], 403);
        }

        // 5. Ambil Kode Dept Pengirim (Sharer)
        $sharerDeptCode = 'PUD'; // Default
        if ($shareInfo->created_by) {
            $sharer = DB::table('users')->where('id', $shareInfo->created_by)->first();
            if ($sharer && $sharer->id_dept) {
                $dept = DB::table('departments')->where('id', $sharer->id_dept)->first();
                if ($dept) {
                    $sharerDeptCode = $dept->code;
                }
            }
        }

        // 6. Ambil Kode Supplier Penerima
        $currentSupplierCode = DB::table('suppliers')->where('id', $userRoleId)->value('code') ?? '-';

        // 7. Ambil Detail Paket
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'm.status_id',
                'p.part_no',
                'dtg.name as doctype_group',
                'dsc.name as doctype_subcategory',
                'pg.code_part_group as part_group'
            )
            ->first();

        // 8. Format Tanggal & Stamp
        $receiptDate = $revision->receipt_date ? Carbon::parse($revision->receipt_date) : null;
        $uploadDateRevision = $revision->created_at ? Carbon::parse($revision->created_at) : null;

        // PENTING: Tanggal Share diambil dari PIVOT ($shareInfo), bukan dari tabel revisi
        $sharedAtDate = $shareInfo->shared_at ? Carbon::parse($shareInfo->shared_at) : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);
        $obsoleteDate = $revision->obsolete_at ? Carbon::parse($revision->obsolete_at) : null;

        $lastApproval = DB::table('package_approvals as pa')
            ->leftJoin('users as u', 'u.id', '=', 'pa.decided_by')
            ->leftJoin('departments as d', 'd.id', '=', 'u.id_dept')
            ->where('pa.revision_id', $decrypted_id)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first(['u.name as approver_name', 'd.code as dept_name']);

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDate ? $obsoleteDate->toSaiStampFormat() : null,
            'name' => $revision->obsolete_name ?? optional($lastApproval)->approver_name ?? '-',
            'dept' => $revision->obsolete_dept ?? optional($lastApproval)->dept_name ?? '-',
        ];

        // 9. Files
        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $decrypted_id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'file_size', 'ori_position', 'copy_position', 'obslt_position', 'blocks_position')
            ->get();

        $extList = $fileRows->map(fn($r) => strtolower(pathinfo($r->name, PATHINFO_EXTENSION)))->unique()->values();
        $extUpper = $extList->map(fn($e) => strtoupper($e));
        $extIcons = $extUpper->isEmpty() ? [] : FileExtensions::whereIn('code', $extUpper)->get()->mapWithKeys(fn($m) => [strtolower($m->code) => $m->icon_src])->all();

        $files = $fileRows->groupBy('category')->map(function ($items) use ($extIcons) {
            return $items->map(function ($item) use ($extIcons) {
                $url = URL::signedRoute('preview.file', ['id' => $item->id]);
                $ext = strtolower(pathinfo($item->name, PATHINFO_EXTENSION));
                return [
                    'name'     => $item->name,
                    'url'      => $url,
                    'file_id'  => $item->id,
                    'icon_src' => $extIcons[$ext] ?? null,
                    'ori_position' => $item->ori_position,
                    'copy_position' => $item->copy_position,
                    'obslt_position' => $item->obslt_position,
                    'blocks_position' => $item->blocks_position ? json_decode($item->blocks_position, true) : [],
                    'size'          => $item->file_size,
                ];
            });
        })->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        // 10. Susun Response Detail
        $feasibilityStatusId = $this->getFeasibilityStatusId();
        $isFeasibility = $feasibilityStatusId && (int)$package->status_id === (int)$feasibilityStatusId;

        $detail = [
            'metadata' => [
                'customer' => $isFeasibility ? 'FEASIBILITY STUDY' : $package->customer,
                'model'    => $isFeasibility ? 'FEASIBILITY STUDY' : $package->model,
                'model_status_id' => $package->status_id,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
                'revision_label' => $revision->revision_label ?? null,
                'ecn_no' => $revision->ecn_no,
                'doc_type' => $package->doctype_group,
                'category' => $package->doctype_subcategory,
                'part_group' => $package->part_group,
                'expired_at' => $shareInfo->expired_at,
            ],
            'status' => 'Approved',
            'files' => $files,
            'stamp' => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'shared_at'    => $sharedAtDate?->toDateString(), // Tanggal dari package_shares
                'is_obsolete'  => $isObsolete,
                'obsolete_info' => $obsoleteStampInfo,
            ],
        ];

        // 10. Fetch All Shared Revisions for this Package (for Version Switching)
        $revisionList = DB::table('doc_package_revisions as dpr')
            ->join('package_shares as ps', 'dpr.id', '=', 'ps.revision_id')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.package_id', $revision->package_id)
            ->where('ps.supplier_id', $userRoleId)
            ->where('dpr.revision_status', 'approved')
            ->orderBy('dpr.revision_no', 'desc')
            ->select('dpr.id', 'dpr.revision_no', 'crl.label')
            ->get()
            ->map(function ($rev) {
                return [
                    'id' => encrypt($rev->id),
                    'revision_no' => $rev->revision_no,
                    'label' => $rev->label,
                    'text' => "Revision " . $rev->revision_no . ($rev->label ? " ({$rev->label})" : "")
                ];
            });

        $stampFormat = StampFormat::where('is_active', true)->first();
        $userName = Auth::check() ? Auth::user()->name : null;

        return response()->json([
            'success' => true,
            'exportId' => $originalEncryptedId,
            'detail' => $detail,
            'revisionList' => $revisionList,
            'stampFormat' => $stampFormat,
            'userName' => $userName,

            // DATA BARU UNTUK FRONTEND
            'sharerDeptCode' => $sharerDeptCode,
            'supplierCode'   => $currentSupplierCode,
        ]);
    }

    private function getStampContext(int $revisionId, int $userId): ?array
    {
        $userIdInt = (int) $userId;

        // 1. Identifikasi Supplier ID user
        $supplierId = null;
        $userSupplier = DB::table('user_supplier')->where('user_id', $userIdInt)->first();

        if ($userSupplier) {
            $supplierId = $userSupplier->supplier_id;
        } else {
            $userRole = DB::table('user_roles')->where('user_id', $userIdInt)->first();
            $supplierId = $userRole ? $userRole->role_id : null;
        }

        if (!$supplierId) {
            return null;
        }

        // 2. Ambil Data Share (Pivot)
        $shareInfo = DB::table('package_shares')
            ->where('revision_id', $revisionId)
            ->where('supplier_id', $supplierId)
            ->first();

        if (!$shareInfo) {
            return null;
        }

        if ($shareInfo->expired_at && Carbon::parse($shareInfo->expired_at)->isPast()) {
            return null;
        }

        $sharerDeptCode = 'PUD';
        if ($shareInfo->created_by) {
            $sharer = DB::table('users')->where('id', $shareInfo->created_by)->first();
            if ($sharer && $sharer->id_dept) {
                $dept = DB::table('departments')->where('id', $sharer->id_dept)->first();
                if ($dept) {
                    $sharerDeptCode = $dept->code;
                }
            }
        }

        $supplierCode = DB::table('suppliers')->where('id', $supplierId)->value('code') ?? '-';

        return [
            'sharerDept'   => $sharerDeptCode,
            'supplierCode' => $supplierCode,
            'sharedAt'     => $shareInfo->shared_at
        ];
    }

    /**
     * Download a single file from receipt with optional stamp
     */
    public function downloadFile($file_id)
    {
        set_time_limit(0);

        $file = DocPackageRevisionFile::find($file_id);

        if (!$file) {
            abort(404, 'File not found.');
        }

        $revision = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->where('dpr.id', $file->revision_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select('dpr.*', 'dp.id as package_id')
            ->first();

        if (!$revision) {
            abort(403, 'Access denied. File is not part of an approved revision.');
        }

        $user = Auth::user();
        $userId = $user ? (int) $user->id : 0;

        $stampContext = $this->getStampContext($revision->id, $userId);

        if (!$stampContext) {
            abort(403, 'Access denied or link expired.');
        }

        $path = Storage::disk('datacenter')->path($file->storage_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server storage.');
        }

        $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
        $stampableTypes = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'pdf'];

        if (in_array($ext, $stampableTypes) && class_exists('\Imagick')) {
            $package = DB::table('doc_packages as dp')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
                ->where('dp.id', $revision->package_id)
                ->select('c.code as customer', 'm.name as model', 'm.status_id', 'p.part_no', 'ps.name as project_status_name')
                ->first();

            $stampFormat = StampFormat::where('is_active', true)->first();

            if ($package && $stampFormat) {
                try {
                    // Kirim $stampContext sebagai parameter terakhir
                    $tempPath = $this->_burnStamps($path, $file, $revision, $package, $stampFormat, $stampContext);

                    if ($tempPath !== $path) {
                        return response()->download($tempPath, $file->filename)->deleteFileAfterSend(true);
                    }
                } catch (\Exception $e) {
                    Log::error('Stamp burn-in failed for single download', [
                        'file_id' => $file_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        return response()->download($path, $file->filename);
    }

    public function preparePackageZip($revision_id)
    {
        set_time_limit(0);
        try {
            $decrypted_id = decrypt(str_replace('-', '=', $revision_id));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Package not found.'], 404);
        }

        $revision = DB::table('doc_package_revisions')
            ->where('id', $decrypted_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            return response()->json(['success' => false, 'message' => 'Package not found.'], 404);
        }

        $user = Auth::user();
        $userId = $user ? (int) $user->id : 0;

        $stampContext = $this->getStampContext($revision->id, $userId);

        if (!$stampContext) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or Link Expired.'], 403);
        }

        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->where('dp.id', $revision->package_id)
            ->select('c.code as customer', 'm.name as model', 'm.status_id', 'p.part_no', 'dp.part_group_id', 'ps.name as project_status_name')
            ->first();

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package details not found.'], 404);
        }

        $files = DocPackageRevisionFile::where('revision_id', $decrypted_id)->get();

        if ($files->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No files found for this package revision.'], 404);
        }

        $stampFormat = StampFormat::where('is_active', true)->first();
        $stampableTypes = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'pdf'];
        $canStamp = class_exists('\Imagick') && $stampFormat;

        $model = Str::slug($package->model);
        $feasibilityStatusId = $this->getFeasibilityStatusId();
        if ($feasibilityStatusId && (int)$package->status_id === (int)$feasibilityStatusId) {
             $model = 'feasibility';
        }

        $sharedAtRaw = $stampContext['sharedAt'];
        $sharedAtDate = Carbon::parse($sharedAtRaw);
        $timestampYmd = $sharedAtDate->format('Ymd');

        $sequence = ActivityLog::where('activity_code', 'SHARE_PACKAGE')
            ->whereYear('created_at', $sharedAtDate->year)
            ->whereMonth('created_at', $sharedAtDate->month)
            ->count();
            
        if ($sequence == 0) {
            $sequence = 1;
        }

        $sequenceStr = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        $zipFileName = strtoupper("SAI-{$model}-{$timestampYmd}-{$sequenceStr}") . ".zip";

        $zipDirectory = storage_path('app/public');
        $zipDirectory = str_replace('/', DIRECTORY_SEPARATOR, $zipDirectory);

        if (!file_exists($zipDirectory)) {
            if (!mkdir($zipDirectory, 0775, true)) {
                $errorMsg = 'Failed to create zip directory.';
                Log::error($errorMsg, ['path' => $zipDirectory]);
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }
        }

        if (!is_writable($zipDirectory)) {
            $errorMsg = 'Zip directory exists but is not writable.';
            Log::error($errorMsg, ['path' => $zipDirectory]);
            return response()->json(['success' => false, 'message' => $errorMsg], 500);
        }

        $zipFilePath = $zipDirectory . DIRECTORY_SEPARATOR . $zipFileName;
        $zip = new ZipArchive();

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['success' => false, 'message' => 'Could not create zip file on server.'], 500);
        }

        $filesAddedCount = 0;
        $tempFilesToDelete = [];

        foreach ($files as $file) {
            $filePath = str_replace('/', DIRECTORY_SEPARATOR, Storage::disk('datacenter')->path($file->storage_path));

            $fileToAdd = $filePath;
            $deleteAfterAdd = false;
            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));

            if ($canStamp && in_array($ext, $stampableTypes) && file_exists($filePath)) {
                try {
                    // Kirim $stampContext ke _burnStamps
                    $tempPath = $this->_burnStamps($filePath, $file, $revision, $package, $stampFormat, $stampContext);
                    if ($tempPath !== $filePath) {
                        $fileToAdd = $tempPath;
                        $deleteAfterAdd = true;
                    }
                } catch (\Exception $e) {
                    Log::error('Imagick stamp burn failed for zip', ['error' => $e->getMessage()]);
                }
            }

            if (file_exists($fileToAdd)) {
                $category = strtolower(trim($file->category));
                $folderInZip = 'ECN';
                if ($category === '2d') $folderInZip = '2D';
                elseif ($category === '3d') $folderInZip = '3D';

                $pathInZip = str_replace('/', DIRECTORY_SEPARATOR, $folderInZip . '/' . $file->filename);
                $zip->addFile($fileToAdd, $pathInZip);

                $zip->setCompressionName($pathInZip, ZipArchive::CM_STORE);
                $filesAddedCount++;

                if ($deleteAfterAdd) {
                    $tempFilesToDelete[] = $fileToAdd;
                }
            } else {
                Log::warning('File skipped: ' . $filePath);
            }
        }

        $zip->close();

        foreach ($tempFilesToDelete as $tempFile) {
            @unlink($tempFile);
        }

        if ($filesAddedCount === 0) {
            @unlink($zipFilePath);
            return response()->json(['success' => false, 'message' => 'No physical files were found.'], 404);
        }

        $downloadUrl = URL::temporarySignedRoute(
            'receipts.download-zip',
            now()->addMinutes(5),
            [
                'file_name' => $zipFileName,
                'rev_id'    => $revision_id
            ]
        );

        return response()->json([
            'success'      => true,
            'message'      => 'File prepared successfully.',
            'download_url' => $downloadUrl,
            'file_name'    => $zipFileName
        ]);
    }

    public function getPreparedZip(Request $request, $file_name)
    {
        set_time_limit(0);

        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link.');
        }

        $safe_filename = basename($file_name);
        $zipFilePath = storage_path('app/public/' . $safe_filename);

        if (!file_exists($zipFilePath)) {
            Log::warning('Prepared zip file not found for download.', ['path' => $zipFilePath]);
            abort(404, 'File not found. It may have expired or already been downloaded.');
        }

        $encrypted_rev_id = $request->get('rev_id');
        $decrypted_id = null;
        if ($encrypted_rev_id) {
            try {
                $decrypted_id = decrypt(str_replace('-', '=', $encrypted_rev_id));
            } catch (\Exception $e) {
                Log::warning('Could not decrypt rev_id for logging in getPreparedZip', ['rev_id' => $encrypted_rev_id]);
            }
        }

        if ($decrypted_id && Auth::check()) {
            try {
                $revision = DB::table('doc_package_revisions')->where('id', $decrypted_id)->first();
                $package = null;
                if ($revision) {
                    $package = DB::table('doc_packages as dp')
                        ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                        ->join('models as m', 'dp.model_id', '=', 'm.id')
                        ->join('products as p', 'dp.product_id', '=', 'p.id')
                        ->leftJoin('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                        ->where('dp.id', $revision->package_id)
                        ->select('c.code as customer', 'm.name as model', 'p.part_no', 'dp.part_group_id', 'dtg.name as doctype_group')
                        ->first();
                }

                if ($revision && $package) {
                    $labelName = null;
                    if (!empty($revision->revision_label_id)) {
                        $labelName = DB::table('customer_revision_labels')->where('id', $revision->revision_label_id)->value('label');
                    }
                    $partGroupCode = null;
                    if (!empty($package->part_group_id)) {
                        $partGroupCode = DB::table('part_groups')->where('id', $package->part_group_id)->value('code_part_group');
                    }

                    // Calculate File Size
                    $fileSize = null;
                    if (file_exists($zipFilePath)) {
                        $bytes = filesize($zipFilePath);
                        if ($bytes >= 1073741824) {
                            $fileSize = number_format($bytes / 1073741824, 2) . ' GB';
                        } elseif ($bytes >= 1048576) {
                            $fileSize = number_format($bytes / 1048576, 2) . ' MB';
                        } elseif ($bytes >= 1024) {
                            $fileSize = number_format($bytes / 1024, 2) . ' KB';
                        } else {
                            $fileSize = $bytes . ' bytes';
                        }
                    }

                    $metaLogData = [
                        'part_no' => $package->part_no,
                        'customer_code' => $package->customer,
                        'model_name' => $package->model,
                        'part_group_code' => $partGroupCode,
                        'doctype_group' => $package->doctype_group,
                        'package_id' => $revision->package_id,
                        'revision_no' => $revision->revision_no,
                        'ecn_no' => $revision->ecn_no,
                        'revision_label' => $labelName,
                        'downloaded_file' => $safe_filename,
                        'file_size' => $fileSize,
                    ];

                    ActivityLog::create([
                        'user_id' => Auth::user()->id,
                        'activity_code' => 'DOWNLOAD',
                        'scope_type' => 'revision',
                        'scope_id' => $revision->package_id,
                        'revision_id' => $revision->id,
                        'meta' => $metaLogData,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to create download activity log in getPreparedZip', [
                    'error' => $e->getMessage(),
                    'revision_id' => $decrypted_id,
                ]);
            }
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    private function positionIntToKey(?int $pos, string $default = 'bottom-right'): string
    {
        switch ($pos) {
            case 0:
                return 'bottom-left';
            case 1:
                return 'bottom-center';
            case 2:
                return 'bottom-right';
            case 3:
                return 'top-left';
            case 4:
                return 'top-center';
            case 5:
                return 'top-right';
            default:
                return $default;
        }
    }

    private function calculateStampCoordinates(string $posKey, int $imgW, int $imgH, int $stampW, int $stampH, int $margin): array
    {
        $x = 0;
        $y = 0;

        switch ($posKey) {
            case 'top-left':
                $x = $margin;
                $y = $margin;
                break;
            case 'top-center':
                $x = max($margin, (int) (($imgW - $stampW) / 2));
                $y = $margin;
                break;
            case 'top-right':
                $x = max($margin, $imgW - $stampW - $margin);
                $y = $margin;
                break;
            case 'bottom-left':
                $x = $margin;
                $y = max($margin, $imgH - $stampH - $margin);
                break;
            case 'bottom-center':
                $x = max($margin, (int) (($imgW - $stampW) / 2));
                $y = max($margin, $imgH - $stampH - $margin);
                break;
            case 'bottom-right':
            default:
                $x = max($margin, $imgW - $stampW - $margin);
                $y = max($margin, $imgH - $stampH - $margin);
                break;
        }

        // Final boundary check
        $x = max(0, min($x, $imgW - $stampW));
        $y = max(0, min($y, $imgH - $stampH));

        return [$x, $y];
    }

    private function _createStampImage(string $centerText, string $topLine, string|array $bottomLine, string $colorMode = 'blue'): ?\Imagick
    {
        try {
            $draw = new \ImagickDraw();
            $font = null;
            $fontsToTry = ['DejaVu-Sans', 'Arial', 'Helvetica', 'Verdana', 'Tahoma', 'sans-serif'];
            foreach ($fontsToTry as $fontName) {
                try {
                    $draw->setFont($fontName);
                    $font = $fontName;
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            $dummy = new \Imagick();
            $dummy->newImage(1, 1, new \ImagickPixel('transparent'));

            // --- 1. UKUR LEBAR TEKS ---

            // A. Top Line
            $draw->setFontSize(16);
            $draw->setFontWeight(600);
            $metricsTop = $dummy->queryFontMetrics($draw, $topLine);
            $widthTop = $metricsTop['textWidth'];

            // B. Bottom Line (Bisa String atau Array)
            $draw->setFontSize(16);
            $draw->setFontWeight(400); // Berat font bawah

            if (is_array($bottomLine)) {
                // Jika Array (Mode Obsolete 2 Kolom)
                // Ukur teks kiri dan kanan
                $metricsLeft = $dummy->queryFontMetrics($draw, $bottomLine[0]);
                $metricsRight = $dummy->queryFontMetrics($draw, $bottomLine[1]);

                $maxSide = max($metricsLeft['textWidth'], $metricsRight['textWidth']);
                $widthBot = ($maxSide * 2) + 20; // +20 toleransi garis tengah
            } else {
                // Jika String Biasa
                $metricsBottom = $dummy->queryFontMetrics($draw, $bottomLine);
                $widthBot = $metricsBottom['textWidth'];
            }

            // C. Center Text
            $draw->setFontSize(21);
            $draw->setFontWeight(900);
            $metricsCenter = $dummy->queryFontMetrics($draw, $centerText);
            $widthCen = $metricsCenter['textWidth'];

            // D. Tentukan Lebar Kanvas
            $paddingHorizontal = 24;
            $minWidth = 220;

            $calculatedWidth = max($widthTop, $widthBot, $widthCen) + $paddingHorizontal;
            $canvasWidth = (int) max($minWidth, $calculatedWidth);
            // $canvasHeight = 120; // OLD FIXED HEIGHT
            $canvasHeight = (int) ($canvasWidth * 0.35); // NEW DYNAMIC HEIGHT (Ratio 0.35 matches frontend)

            // Calculate Row Height (1/3 of total)
            $rowH = $canvasHeight / 3;

            // --- 2. SETUP KANVAS ---
            $stamp = new \Imagick();
            // OPACITY UPDATE: 0.3 to match frontend
            $bgOpacity = 0.3; 
            $stamp->newImage($canvasWidth, $canvasHeight, new \ImagickPixel("rgba(255, 255, 255, $bgOpacity)"));
            $stamp->setImageFormat('png');

            $opacity = 0.4; // Match frontend (0.4)
            if ($colorMode === 'red') {
                $borderColor = new \ImagickPixel("rgba(220, 38, 38, $opacity)");
                $textColor   = new \ImagickPixel("rgba(185, 28, 28, $opacity)");
            } elseif ($colorMode === 'gray') {
                $borderColor = new \ImagickPixel("rgba(107, 114, 128, $opacity)"); // Gray 500
                $textColor   = new \ImagickPixel("rgba(107, 114, 128, $opacity)");
            } else {
                $borderColor = new \ImagickPixel("rgba(37, 99, 235, $opacity)");
                $textColor   = new \ImagickPixel("rgba(29, 78, 216, $opacity)");
            }

            $draw->setStrokeColor($borderColor);
            $draw->setFillColor(new \ImagickPixel('transparent'));
            $draw->setStrokeWidth(3);
            $draw->setFont($font);

            // Gambar Border Luar
            $draw->roundRectangle(2, 2, $canvasWidth - 2, $canvasHeight - 2, 2, 2);
            // Garis Horizontal
            $line1Y = $rowH;
            $line2Y = $rowH * 2;
            $draw->line(2, $line1Y, $canvasWidth - 2, $line1Y);
            $draw->line(2, $line2Y, $canvasWidth - 2, $line2Y);

            // LOGIKA KHUSUS ARRAY (GARIS VERTIKAL)
            if (is_array($bottomLine)) {
                $splitX = $canvasWidth * 0.65; // 65% split
                $draw->line($splitX, $line2Y, $splitX, $canvasHeight - 2);
            }

            $stamp->drawImage($draw);

            // --- 3. RENDER TEKS ---
            $draw->setFillColor($textColor);
            $draw->setStrokeWidth(0);

            $x_center_canvas = $canvasWidth / 2;

            // Teks Atas (Center) - Posisi Center Vertikal Row 1
            $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
            $fontSizeTop = $rowH * 0.45;
            $draw->setFontSize($fontSizeTop);
            $draw->setFontWeight(600);
            $yTop = ($rowH / 2) + ($rowH * 0.15); 
            $stamp->annotateImage($draw, $x_center_canvas, $yTop, 0, $topLine);

            // Teks Tengah (Center) - Posisi Center Vertikal Row 2
            $fontSizeCenter = $rowH * 0.65;
            $draw->setFontSize($fontSizeCenter);
            $draw->setFontWeight(800);
            
            // Auto-scale font size if text matches canvas width (Simulate maxWidth)
            $metricsCen = $stamp->queryFontMetrics($draw, $centerText);
            $textWCen = $metricsCen['textWidth'];
            $maxWCen = $canvasWidth - 20; // Padding
            
            if ($textWCen > $maxWCen) {
                $scaleFactor = $maxWCen / $textWCen;
                $fontSizeCenter = $fontSizeCenter * $scaleFactor;
                $draw->setFontSize($fontSizeCenter);
            }

            $draw->setStrokeColor($textColor);
            $draw->setStrokeWidth(0.75);
            $yCenter = ($rowH * 1.5) + ($rowH * 0.2); // Adjust center baseline
            $stamp->annotateImage($draw, $x_center_canvas, $yCenter, 0, $centerText);

            // Teks Bawah - Posisi Center Vertikal Row 3
            $draw->setStrokeWidth(0);
            $fontSizeBottom = $rowH * 0.45;
            $draw->setFontSize($fontSizeBottom);
            $draw->setFontWeight(400);
            $yBottom = ($rowH * 2.5) + ($rowH * 0.15);

            if (is_array($bottomLine)) {
                // --- MODE 2 KOLOM (Kiri & Kanan) - 65% / 35% ---
                $splitRatio = 0.65;
                $splitX = $canvasWidth * $splitRatio;
                
                $leftWidth = $splitX;
                $rightWidth = $canvasWidth - $splitX;

                $maxColLeft = $leftWidth - 10;
                $maxColRight = $rightWidth - 10;
                
                $baseFontSize = $rowH * 0.45;

                // 1. Render Left Column (Name) - 65%
                $draw->setFontSize($baseFontSize);
                $metricsLeft = $stamp->queryFontMetrics($draw, $bottomLine[0]);
                if ($metricsLeft['textWidth'] > $maxColLeft) {
                    $scale = $maxColLeft / $metricsLeft['textWidth'];
                    $draw->setFontSize($baseFontSize * $scale);
                }
                $x_left = $leftWidth / 2; // Center of left part
                $stamp->annotateImage($draw, $x_left, $yBottom, 0, $bottomLine[0]);

                // 2. Render Right Column (Dept) - 35%
                $draw->setFontSize($baseFontSize); // Reset to base size
                $metricsRight = $stamp->queryFontMetrics($draw, $bottomLine[1]);
                if ($metricsRight['textWidth'] > $maxColRight) {
                    $scale = $maxColRight / $metricsRight['textWidth'];
                    $draw->setFontSize($baseFontSize * $scale);
                }
                $x_right = $splitX + ($rightWidth / 2); // Center of right part
                $stamp->annotateImage($draw, $x_right, $yBottom, 0, $bottomLine[1]);

            } else {
                // --- MODE 1 KOLOM (Standar) ---
                $stamp->annotateImage($draw, $x_center_canvas, $yBottom, 0, $bottomLine);
            }

            // Cleanup
            $draw->clear();
            $draw->destroy();
            $dummy->clear();
            $dummy->destroy();

            return $stamp;
        } catch (\Exception $e) {
            Log::error('Failed to create stamp image: ' . $e->getMessage());
            return null;
        }
    }

    // Perhatikan parameter terakhir $extraData = []
    private function _burnStamps(string $originalPath, DocPackageRevisionFile $file, object $revision, object $package, ?StampFormat $stampFormat, array $extraData = []): string
    {
        if (!class_exists('Imagick') || !file_exists($originalPath)) {
            return $originalPath;
        }

        try {
            // Helper Format Tanggal
            $formatSaiDate = function ($dateInput) {
                if (!$dateInput) return '-';
                try {
                    $d = Carbon::parse($dateInput);
                    $day = $d->day;
                    if (in_array($day % 100, [11, 12, 13], true)) {
                        $suffixRaw = 'th';
                    } else {
                        $last = $day % 10;
                        $suffixRaw = match ($last) {
                            1 => 'st',
                            2 => 'nd',
                            3 => 'rd',
                            default => 'th'
                        };
                    }
                    $superscripts = ['st' => 'ˢᵗ', 'nd' => 'ⁿᵈ', 'rd' => 'ʳᵈ', 'th' => 'ᵗʰ'];
                    $suffix = $superscripts[$suffixRaw] ?? $suffixRaw;
                    return $d->format('M') . '.' . $day . $suffix . ' ' . $d->format('Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            // --- A. STAMP ORIGINAL ---
            $receiptDateStr = $formatSaiDate($revision->receipt_date);
            $uploadDateStr  = $formatSaiDate($revision->created_at);
            $topLine    = 'Date Received : ' . $receiptDateStr;
            $bottomLine = 'Date Uploaded : ' . $uploadDateStr;

            // --- B. STAMP CONTROL COPY / UNCONTROLLED ---
            $modelStatusId = $package->status_id ?? 0;

            // Ambil data dari Helper ($extraData)
            $sharerDept   = $extraData['sharerDept'] ?? 'PUD';
            $supplierCode = $extraData['supplierCode'] ?? '-';
            $sharedAtRaw  = $extraData['sharedAt'] ?? $revision->shared_at;

            // --- GENERATE MASTER GAMBAR ---
            $projectStatusName = $package->project_status_name ?? '';
            $isFeasibility = strcasecmp($projectStatusName, 'Feasibility Study') === 0 || strcasecmp($projectStatusName, 'Feasibility') === 0;

            if ($modelStatusId == 4 || $isFeasibility) {
                // === UNCONTROLLED COPY ===
                $centerTextCopy = 'SAI-DRAWING UNCONTROLLED COPY';

                // Top Line: SAI / {DEPT_PENGIRIM} / For Quotation
                $topLineCopy = "SAI / {$sharerDept} / For Quotation";

                // Bottom Line: Date Share : {TANGGAL_DARI_PIVOT}
                $sharedAtStr = $formatSaiDate($sharedAtRaw);
                $bottomLineCopy = "Date Share : " . $sharedAtStr;
            } else {
                // === CONTROLLED COPY ===
                $centerTextCopy = 'SAI-DRAWING CONTROLLED COPY';

                $now = now();
                $datePart = $formatSaiDate($now);
                $timePart = $now->format('H:i:s');

                // Top Line: SAI / {DEPT_PENGIRIM} / {DATE} {TIME}
                $topLineCopy = "SAI / {$sharerDept} / {$datePart} {$timePart}";

                // Bottom Line: External - Distributed To Supplier {SUPPLIER_CODE}
                $bottomLineCopy = "External - Distributed To Supplier {$supplierCode}";
            }

            // --- POSISI STAMP ---
            $posOriginal = $this->positionIntToKey($file->ori_position ?? 0, 'bottom-left');
            $posCopy     = $this->positionIntToKey($file->copy_position ?? 1, 'bottom-center');
            $posObsolete = $this->positionIntToKey($file->obslt_position ?? 2, 'bottom-right');

            // --- GENERATE MASTER GAMBAR ---
            // $projectStatusName & $isFeasibility already defined above

            if ($isFeasibility) {
                $stampOriginal = null;
            } else {
                $stampOriginal = $this->_createStampImage('SAI-DRAWING ORIGINAL', $topLine, $bottomLine, 'gray');
            }

            $stampCopy     = $this->_createStampImage($centerTextCopy, $topLineCopy, $bottomLineCopy, 'blue');
            $stampObsolete = null;

            $isObsolete = (bool)($revision->is_obsolete ?? 0);
            if ($isObsolete) {
                $obsDateStr = $formatSaiDate($revision->obsolete_at);
                $topLineObsolete = "Date : {$obsDateStr}";

                $obsName = '-';
                $obsDept = '-';
                if (!empty($revision->obsolete_by)) {
                    $u = DB::table('users')->where('id', $revision->obsolete_by)->first(['name', 'id_dept']);
                    if ($u) {
                        $obsName = $u->name;
                        if ($u->id_dept) {
                            $d = DB::table('departments')->where('id', $u->id_dept)->value('code');
                            $obsDept = $d ?? '-';
                        }
                    }
                }
                if ($obsName === '-') {
                    $lastReject = DB::table('package_approvals')->where('revision_id', $revision->id)->where('decision', 'rejected')->orderByDesc('id')->first();
                    if ($lastReject && $lastReject->decided_by) {
                        $u = DB::table('users')->where('id', $lastReject->decided_by)->first();
                        if ($u) {
                            $obsName = $u->name;
                            $d = DB::table('departments')->where('id', $u->id_dept)->value('code');
                            $obsDept = $d ?? '-';
                        }
                    }
                }

                $bottomLineObsolete = ["Nama : {$obsName}", "Dept. : {$obsDept}"];
                $stampObsolete = $this->_createStampImage('SAI-DRAWING OBSOLETE', $topLineObsolete, $bottomLineObsolete, 'red');
            }

            if (!$stampOriginal && !$stampCopy) {
                return $originalPath;
            }

            // --- IMAGICK PROCESSING ---
            // Use stampCopy for dimensions if stampOriginal is null
            $refStamp = $stampOriginal ?? $stampCopy;
            $masterStampWidth = $refStamp->getImageWidth();
            $masterStampHeight = $refStamp->getImageHeight();
            $stampAspectRatio = $masterStampWidth / $masterStampHeight;

            $imagick = new \Imagick();
            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
            $isPdf = ($ext === 'pdf');
            $isTiff = ($ext === 'tif' || $ext === 'tiff');
            $isMultiPage = ($isPdf || $isTiff);

            // --- PREPARE BLOCKS DATA ---
            $rawBlocks = $file->blocks_position;
            if (is_string($rawBlocks)) {
                $rawBlocks = json_decode($rawBlocks, true);
            }

            $blocksData = [];
            if (is_array($rawBlocks)) {
                // Cek apakah array indexed (format lama: flat array untuk page 1)
                // atau associative (format baru: per page)
                $isIndexed = array_keys($rawBlocks) === range(0, count($rawBlocks) - 1);
                if ($isIndexed && !empty($rawBlocks)) {
                    $blocksData['1'] = $rawBlocks;
                } else {
                    $blocksData = $rawBlocks;
                }
            }

            if ($isPdf) {
                $imagick->setResolution(150, 150);
            }
            $imagick->readImage($originalPath);

            $marginPercent = 0.04; // Increased margin to 4%
            $stampWidthPercent = 0.15;
            $minStampWidth = 250; // Match frontend

            $processPage = function ($iteratorIndex) use (
                $imagick,
                $stampOriginal,
                $stampCopy,
                $stampObsolete,
                $isObsolete,
                $stampAspectRatio,
                $marginPercent,
                $stampWidthPercent,
                $minStampWidth,
                $posOriginal,
                $posCopy,
                $posObsolete,
                $isTiff,
                $blocksData
            ) {
                $imagick->setIteratorIndex($iteratorIndex);

                if ($isTiff) {
                    $imagick->setImageFormat('tiff');
                }

                // Ensure sRGB Colorspace
                if ($imagick->getImageColorspace() !== \Imagick::COLORSPACE_SRGB) {
                    $imagick->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
                }

                // Force TrueColor (supports colors like Blue/Red)
                try {
                    $imagick->setImageType(\Imagick::IMGTYPE_TRUECOLORMATTE);
                } catch (\Exception $e) {
                    $imagick->setImageType(\Imagick::IMGTYPE_TRUECOLOR);
                }

                // 3. CRITICAL: Force 8-bit depth. 
                // Many TIFFs are 1-bit (Bilevel). Without this, they revert to black/white, 
                // turning the blue stamp black and making anti-aliased edges look like thick pixelated borders.
                $imagick->setImageDepth(8);

                // 4. Set Compression (LZW is good for TIFF)
                if ($isTiff) {
                    $imagick->setCompression(\Imagick::COMPRESSION_LZW);
                }
                $imgWidth = $imagick->getImageWidth();
                $imgHeight = $imagick->getImageHeight();
                $margin = max(8, (int) (min($imgWidth, $imgHeight) * $marginPercent));

                // --- BURN BLOCKS (Redaction) ---
                $pageKey = (string)($iteratorIndex + 1);
                $pageBlocks = $blocksData[$pageKey] ?? [];

                foreach ($pageBlocks as $block) {
                    $u = $block['u'] ?? 0;
                    $v = $block['v'] ?? 0;
                    $w = $block['w'] ?? 0;
                    $h = $block['h'] ?? 0;
                    $rot = $block['rotation'] ?? 0;

                    $blockW = $w * $imgWidth;
                    $blockH = $h * $imgHeight;

                    if ($blockW <= 0 || $blockH <= 0) continue;

                    try {
                        $drawBlock = new \ImagickDraw();
                        $drawBlock->setFillColor('white');
                        $drawBlock->rectangle(0, 0, $blockW, $blockH);

                        $blockImg = new \Imagick();
                        $blockImg->newImage($blockW, $blockH, new \ImagickPixel('transparent'));
                        $blockImg->setImageFormat('png');
                        $blockImg->drawImage($drawBlock);

                        if ($rot != 0) {
                            $blockImg->rotateImage(new \ImagickPixel('transparent'), $rot);
                        }

                        // Calculate composite position (center-based)
                        // Original Center
                        $cx = ($u * $imgWidth) + ($blockW / 2);
                        $cy = ($v * $imgHeight) + ($blockH / 2);

                        // New dimensions after rotation
                        $newW = $blockImg->getImageWidth();
                        $newH = $blockImg->getImageHeight();

                        $compX = $cx - ($newW / 2);
                        $compY = $cy - ($newH / 2);

                        $imagick->compositeImage($blockImg, \Imagick::COMPOSITE_OVER, $compX, $compY);

                        $blockImg->clear();
                        $blockImg->destroy();
                        $drawBlock->clear();
                        $drawBlock->destroy();
                    } catch (\Exception $e) {
                        // Ignore block error, continue to next block/stamp
                    }
                }
                // -------------------------------

                $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));

                // Original
                if ($stampOriginal) {
                    $origW = $stampOriginal->getImageWidth();
                    $origH = $stampOriginal->getImageHeight();
                    $origRatio = $origW / $origH;
                    $newHeightOrig = (int) ($newStampWidth / $origRatio);

                    $stampOrigResized = $stampOriginal->clone();
                    $stampOrigResized->resizeImage($newStampWidth, $newHeightOrig, \Imagick::FILTER_LANCZOS, 1);

                    // Composite Original
                    list($x, $y) = $this->calculateStampCoordinates($posOriginal, $imgWidth, $imgHeight, $newStampWidth, $newHeightOrig, $margin);
                    $imagick->compositeImage($stampOrigResized, \Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

                    $stampOrigResized->clear();
                    $stampOrigResized->destroy();
                }

                // Copy
                $copyW = $stampCopy->getImageWidth();
                $copyH = $stampCopy->getImageHeight();
                $copyRatio = $copyW / $copyH;
                $newHeightCopy = (int) ($newStampWidth / $copyRatio);

                $stampCopyResized = $stampCopy->clone();
                $stampCopyResized->resizeImage($newStampWidth, $newHeightCopy, \Imagick::FILTER_LANCZOS, 1);

                // Obsolete
                $stampObsResized = null;
                $newHeightObs = 0; // Default
                if ($isObsolete && $stampObsolete) {
                    $obsW = $stampObsolete->getImageWidth();
                    $obsH = $stampObsolete->getImageHeight();
                    $obsRatio = $obsW / $obsH;
                    $newHeightObs = (int) ($newStampWidth / $obsRatio);

                    $stampObsResized = $stampObsolete->clone();
                    $stampObsResized->resizeImage($newStampWidth, $newHeightObs, \Imagick::FILTER_LANCZOS, 1);
                }

                // Composite Copy
                list($xCopy, $yCopy) = $this->calculateStampCoordinates($posCopy, $imgWidth, $imgHeight, $newStampWidth, $newHeightCopy, $margin);
                $imagick->compositeImage($stampCopyResized, \Imagick::COMPOSITE_OVER, (int)$xCopy, (int)$yCopy);

                // Composite Obsolete
                if ($stampObsResized) {
                    list($xObs, $yObs) = $this->calculateStampCoordinates($posObsolete, $imgWidth, $imgHeight, $newStampWidth, $newHeightObs, $margin);
                    $imagick->compositeImage($stampObsResized, \Imagick::COMPOSITE_OVER, (int)$xObs, (int)$yObs);
                    $stampObsResized->destroy();
                }

                $stampCopyResized->destroy();
            };

            if ($isMultiPage) {
                $totalPages = $imagick->getNumberImages();
                for ($i = 0; $i < $totalPages; $i++) {
                    $processPage($i);
                }
            } else {
                $mainImageIndex = 0;
                if ($imagick->getNumberImages() > 1) {
                    $maxResolution = 0;
                    foreach ($imagick as $i => $frame) {
                        $res = $frame->getImageWidth() * $frame->getImageHeight();
                        if ($res > $maxResolution) {
                            $maxResolution = $res;
                            $mainImageIndex = $i;
                        }
                    }
                }
                $processPage($mainImageIndex);
            }

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0775, true);

            $outputExt = $isPdf ? 'pdf' : strtolower($imagick->getImageFormat());
            $tempPath = $tempDir . '/' . Str::uuid()->toString() . '.' . $outputExt;

            if ($isMultiPage) {
                $imagick->writeImages($tempPath, true);
            } else {
                $imagick->writeImage($tempPath);
            }

            $imagick->clear();
            $imagick->destroy();
            if ($stampOriginal) {
                $stampOriginal->clear();
                $stampOriginal->destroy();
            }
            $stampCopy->clear();
            $stampCopy->destroy();
            if ($stampObsolete) {
                $stampObsolete->clear();
                $stampObsolete->destroy();
            }

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Imagick stamp burn-in failed: ' . $e->getMessage(), ['file_id' => $file->id, 'path' => $originalPath]);
            return $originalPath;
        }
    }

    private function getFeasibilityStatusId(): ?int
    {
        return DB::table('project_status')
            ->where('name', 'Feasibility Study') 
            ->value('id');
    }
}
