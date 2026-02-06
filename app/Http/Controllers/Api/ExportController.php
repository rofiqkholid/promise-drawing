<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use App\Models\DocPackageRevisionFile;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Imagick;
use ImagickDraw;
use ImagickPixel;

class ExportController extends Controller
{
    public function kpi(Request $req)
    {
        // === PREPARE FILTERS (Avoid Code Duplication) ===
        $excludeFeasId = null;
        $feasId = $this->getFeasibilityStatusId();
        if ($feasId) {
            $user = Auth::user();
            if (!$user || !$this->userHasAnyRole($user, ['ENG'])) {
                $excludeFeasId = $feasId;
            }
        }

        $applyFilters = function ($query) use ($req, $excludeFeasId) {
            if ($req->filled('customer') && $req->customer !== 'All') {
                $query->where('c.code', $req->customer);
            }
            if ($req->filled('model') && $req->model !== 'All') {
                $query->where('m.name', $req->model);
            }
            if ($req->filled('doc_type') && $req->doc_type !== 'All') {
                $query->where('dtg.name', $req->doc_type);
            }
            if ($req->filled('category') && $req->category !== 'All') {
                $query->where('dsc.name', $req->category);
            }
            if ($req->filled('project_status') && $req->project_status !== 'All') {
                $query->where('m.status_id', $req->project_status);
            }
            if ($excludeFeasId) {
                $query->where('m.status_id', '!=', $excludeFeasId);
            }
        };

        // QUERY Stats (Total Packages & Total Revisions)

        $baseQuery = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('dpr.revision_status', '=', 'approved');

        $applyFilters($baseQuery);

        $stats = $baseQuery->selectRaw('count(*) as total_revisions, count(distinct dpr.package_id) as total_packages')
            ->first();

        // QUERY Downloads
        $downloadQuery = DB::table('activity_logs as al')
            ->join('doc_package_revisions as dpr', 'al.revision_id', '=', 'dpr.id')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('al.activity_code', '=', 'DOWNLOAD');

        $applyFilters($downloadQuery);

        $totalDownloads = $downloadQuery->count();

        return response()->json([
            'cards' => [
                'total' => $stats->total_packages ?? 0,
                'total_download' => $totalDownloads,
                'total_revisions' => $stats->total_revisions ?? 0
            ],
            'metrics' => [
                'approval_rate'  => 100.0,
                'rejection_rate' => 0.0,
                'wip_rate'       => 0.0,
            ],
        ]);
    }

    public function filters(Request $request): JsonResponse
    {
        // ====== MODE SELECT2 (server-side) ======
        if ($request->filled('select2')) {
            $field   = $request->get('select2');   // 'customer' | 'model' | 'doc_type' | 'category'
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            $customerCode = $request->get('customer_code');
            $docTypeName  = $request->get('doc_type');

            $total = 0;
            $items = collect();

            switch ($field) {
                case 'customer':
                    // id = code, text = code
                    $builder = DB::table('customers as c')
                        ->selectRaw('c.code AS id, c.code AS text')
                        ->when($q, fn($x) => $x->where(function ($w) use ($q) {
                            $w->where('c.code', 'like', "%{$q}%")
                                ->orWhere('c.name', 'like', "%{$q}%");
                        }))
                        ->orderBy('c.code');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'model':
                case 'model':
                    $base = DB::table('models as m')
                        ->join('customers as c', 'm.customer_id', '=', 'c.id')
                        ->when($customerCode && $customerCode !== 'All', fn($x) => $x->where('c.code', $customerCode))
                        ->when($q, fn($x) => $x->where('m.name', 'like', "%{$q}%"));

                    $total = $base->distinct()->count('m.name');
                    
                    $items = $base->select('m.name as id', 'm.name as text')
                        ->groupBy('m.name')
                        ->orderBy('m.name')
                        ->forPage($page, $perPage)
                        ->get();
                    break;

                case 'doc_type':
                    $builder = DB::table('doctype_groups as dtg')
                        ->selectRaw('dtg.name AS id, dtg.name AS text')
                        ->when($q, fn($x) => $x->where('dtg.name', 'like', "%{$q}%"))
                        ->orderBy('dtg.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'category':
                    $builder = DB::table('doctype_subcategories as dsc')
                        ->join('doctype_groups as dtg', 'dsc.doctype_group_id', '=', 'dtg.id')
                        ->selectRaw('dsc.name AS id, dsc.name AS text')
                        ->when($docTypeName && $docTypeName !== 'All', fn($x) => $x->where('dtg.name', $docTypeName))
                        ->when($q, fn($x) => $x->where('dsc.name', 'like', "%{$q}%"))
                        ->orderBy('dsc.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                case 'project_status':
                    $builder = DB::table('project_status')
                        ->selectRaw('id, name AS text')
                        ->when($q, fn($x) => $x->where('name', 'like', "%{$q}%"))
                        ->orderBy('name');

                    // === BATASIN FEASIBILITY DI DROPDOWN STATUS ===
                    $user   = Auth::user();
                    $feasId = $this->getFeasibilityStatusId();

                    if ($feasId) {
                        if (!$user) {
                            // tidak login -> jangan tampilkan Feasibility
                            $builder->where('id', '!=', $feasId);
                        } else {
                            // user tanpa role khusus -> tetap jangan tampilkan Feasibility
                            if (!$this->userHasAnyRole($user, ['ENG'])) {
                                $builder->where('id', '!=', $feasId);
                            }
                        }
                    }

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
                    break;

                default:
                    return response()->json(['results' => [], 'pagination' => ['more' => false]]);
            }

            if ($page === 1) {
                $items = collect([['id' => 'All', 'text' => 'All']])->merge($items);
            }

            $effectiveTotal = $total + ($page === 1 ? 1 : 0);
            $more = ($effectiveTotal > $page * $perPage);

            return response()->json([
                'results'    => array_values($items->toArray()),
                'pagination' => ['more' => $more]
            ]);
        }

        // customer
        $customerId = $request->integer('customer_id') ?: null;
        if (!$customerId && $request->filled('customer_code')) {
            $customerId = Customers::where('code', $request->get('customer_code'))->value('id');
        }

        // models
        $models = $customerId
            ? Models::where('customer_id', $customerId)->orderBy('name')->get(['id', 'name'])
            : Models::orderBy('name')->get(['id', 'name']);

        // doc types
        $docTypes = DoctypeGroups::orderBy('name')->get(['id', 'name']);

        $docTypeName = $request->get('doc_type');
        $docTypeId   = null;
        if ($docTypeName && $docTypeName !== 'All') {
            $docTypeId = DoctypeGroups::where('name', $docTypeName)->value('id');
        }

        $categories = DocTypeSubCategories::when($docTypeId, function ($q) use ($docTypeId) {
            $q->where('doctype_group_id', $docTypeId);
        })
            ->orderBy('name')
            ->get(['name']);

        return response()->json([
            'customers'  => Customers::orderBy('code')->get(['id', 'code']),
            'models'     => $models,
            'doc_types'  => $docTypes,
            'categories' => $categories,
        ]);
    }

    public function listExportableFiles(Request $request): JsonResponse
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';
        $orderColumnIndex = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        $columnMap = [
            'Package Info' => 'c.code',
            'Revision Note' => 'dpr.note',
            'Revision' => 'dpr.revision_no',
            'ecn_no' => 'dpr.ecn_no',
            'doctype_group' => 'dtg.name',
            'doctype_subcategory' => 'dsc.name',
            'part_group' => 'pg.code_part_group',
            'uploaded_at' => 'dpr.created_at',
            'size' => 'total_size' 
        ];

        // Fallback or mapped sort column
        $colName = $request->get('columns')[$orderColumnIndex]['name'] ?? 'uploaded_at';
        $orderColumnName = $columnMap[$colName] ?? 'dpr.created_at';


        if ($colName === 'size') {
            $orderColumnName = 'dpr.created_at'; 
        }
    
        $query = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.revision_status', '=', 'approved')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('doc_package_revisions as next_ver')
                    ->whereColumn('next_ver.package_id', 'dpr.package_id')
                    ->where('next_ver.revision_status', '=', 'approved')
                    ->whereColumn('next_ver.revision_no', '>', 'dpr.revision_no');
            });

        // Access Control / Feasibility
        $user   = Auth::user();
        $feasId = $this->getFeasibilityStatusId();

        if ($feasId) {
            if (!$user || !$this->userHasAnyRole($user, ['ENG'])) {
                $query->where('m.status_id', '!=', $feasId);
            }
        }

        if ($request->filled('customer') && $request->customer !== 'All') {
            $query->where('c.code', $request->customer);
        }
        if ($request->filled('model') && $request->model !== 'All') {
            $query->where('m.name', $request->model);
        }
        if ($request->filled('doc_type') && $request->doc_type !== 'All') {
            $query->where('dtg.name', $request->doc_type);
        }
        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('dsc.name', $request->category);
        }
        if ($request->filled('project_status') && $request->project_status !== 'All') {
            $query->where('m.status_id', $request->project_status);
        }

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $isDeep = strlen($searchValue) >= 3;

                // Priority Search: Part No, Partners, Customer & Model
                $q->where('p.part_no', 'like', "%{$searchValue}%")
                  ->orWhere('c.code', 'like', "{$searchValue}%")
                  ->orWhere('m.name', 'like', "%{$searchValue}%")
                  ->orWhereIn('p.group_id', function($sub) use ($searchValue) {
                      $sub->select('group_id')
                          ->from('products')
                          ->whereNotNull('group_id')
                          ->where('is_delete', 0)
                          ->where('part_no', 'like', "%{$searchValue}%");
                  });

                // Extended Metadata - Only for 3+ characters
                if ($isDeep) {
                    $q->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%")
                      ->orWhere('dpr.note', 'like', "%{$searchValue}%")
                      ->orWhere('dtg.name', 'like', "%{$searchValue}%")
                      ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                      ->orWhere('pg.code_part_group', 'like', "%{$searchValue}%");
                }
            });
        }

        // 1. FAST BASELINE COUNT (Cached for 10 mins)
        // Only count unique packages that have at least one approved revision
        $recordsTotal = Cache::remember('export_list_total_count', 600, function() {
            return DB::table('doc_package_revisions')
                ->where('revision_status', 'approved')
                ->distinct('package_id')
                ->count('package_id');
        });

        $recordsFiltered = $query->count();
        $rawData = $query->select(
            'dpr.id',
            'dpr.revision_no',
            'dpr.note',
            'dpr.ecn_no',
            'dpr.created_at',
            'c.code as customer',
            'm.name as model',
            'p.part_no',
            'p.id as product_id',
            'p.group_id',
            'crl.label as revision_label_name',
            'dtg.name as doctype_group',
            'dsc.name as doctype_subcategory',
            'pg.code_part_group as part_group'
        )
            ->orderBy($orderColumnName, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $revisionIds = $rawData->pluck('id');
        $groupIds    = $rawData->pluck('group_id')->filter()->unique();

        //Batch Calculate Sizes & Counts
        $statsMap = collect();
        if ($revisionIds->isNotEmpty()) {
            $statsMap = DB::table('doc_package_revision_files')
                ->whereIn('revision_id', $revisionIds)
                ->select('revision_id', 
                    DB::raw('SUM(file_size) as total_size'),
                    DB::raw('COUNT(*) as file_count')
                )
                ->groupBy('revision_id')
                ->get()
                ->keyBy('revision_id');
        }

        //Batch Fetch Partners
        $partnersMap = collect();
        if ($groupIds->isNotEmpty()) {
            $partnersMap = DB::table('products')
                ->whereIn('group_id', $groupIds)
                ->select('group_id', 'id', 'part_no')
                ->get()
                ->groupBy('group_id');
        }

        $formattedData = $rawData->map(function ($row) use ($partnersMap, $statsMap) {
            // Attach Size & Count from memory (Must use raw ID before encryption)
            $stats = $statsMap->get($row->id);
            $row->total_size = $stats->total_size ?? 0;
            $row->file_count = $stats->file_count ?? 0; 

            $row->id = str_replace('=', '-', encrypt($row->id));
            
            // Format Date
            $row->uploaded_at = $row->created_at ? date('Y-m-d H:i:s', strtotime($row->created_at)) : '-';

            // Attach Partners
            $row->partners = '';
            if (!empty($row->group_id) && isset($partnersMap[$row->group_id])) {
                $partnerParts = $partnersMap[$row->group_id]
                    ->where('id', '!=', $row->product_id)
                    ->pluck('part_no')
                    ->unique()
                    ->toArray();

                if (!empty($partnerParts)) {
                    // PURE DATA ONLY. No HTML here.
                    $row->partners = implode(', ', $partnerParts);
                }
            }

            return $row;
        });

        // Include KPIs with a cache to minimize heavy count queries
        $kpiData = Cache::remember('drawing_download_kpis_global', 60, function() {
             $stats = DB::table('doc_package_revisions as dpr')
                ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
                ->where('dpr.revision_status', '=', 'approved')
                ->selectRaw('count(*) as total_revisions, count(distinct dpr.package_id) as total_packages')
                ->first();

             $downloads = DB::table('activity_logs')
                ->where('activity_code', '=', 'DOWNLOAD')
                ->count();

             return [
                 'total' => $stats->total_packages ?? 0,
                 'total_revisions' => $stats->total_revisions ?? 0,
                 'total_download' => $downloads ?? 0
             ];
        });

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $formattedData,
            "kpis" => $kpiData
        ]);
    }

    public function showDetail($id)
    {
        $originalEncryptedId = $id;
        try {
            $id = decrypt(str_replace('-', '=', $id));
        } catch (\Exception $e) {
            abort(404, 'Exportable file not found or not approved.');
        }

        // $id could be revision_id or package_id
        // First, try to get the revision
        $revision = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->leftJoin('users as ou', 'ou.id', '=', 'dpr.obsolete_by')
            ->leftJoin('departments as od', 'od.id', '=', 'ou.id_dept')
            ->where('dpr.id', $id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select(
                'dpr.*',
                'crl.label as revision_label',
                'ou.name as obsolete_name',
                'od.code as obsolete_dept'
            )
            ->first();

        // If not found, try to get package and find latest revision
        if (!$revision) {
            $package = DB::table('doc_packages')->where('id', $id)->first();
            if (!$package) {
                abort(404, 'Exportable file not found or not approved.');
            }
            
            // Get latest approved revision
            $revision = DB::table('doc_package_revisions as dpr')
                ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
                ->leftJoin('users as ou', 'ou.id', '=', 'dpr.obsolete_by')
                ->leftJoin('departments as od', 'od.id', '=', 'ou.id_dept')
                ->where('dpr.package_id', $package->id)
                ->where('dpr.revision_status', '=', 'approved')
                ->orderBy('dpr.revision_no', 'desc')
                ->select(
                    'dpr.*',
                    'crl.label as revision_label',
                    'ou.name as obsolete_name',
                    'od.code as obsolete_dept'
                )
                ->first();
                
            if (!$revision) {
                abort(404, 'No approved revision found.');
            }
            
            // Update encrypted ID to the latest revision
            $originalEncryptedId = str_replace('=', '-', encrypt($revision->id));
        }

        // CEK HAK AKSES BERDASARKAN PACKAGE
        $this->abortIfNoAccessToPackage((int)$revision->package_id);

        $revisionList = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.package_id', $revision->package_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->orderBy('dpr.revision_no', 'desc')
            ->select('dpr.id', 'dpr.revision_no', 'crl.label', 'dpr.is_obsolete')
            ->get()
            ->map(function ($rev) {
                $labelText = $rev->label ? " | {$rev->label}" : "";
                return [
                    'id'   => str_replace('=', '-', encrypt($rev->id)),
                    'revision' => "Rev-{$rev->revision_no}{$labelText}",
                    'revision_no' => $rev->revision_no,
                    'is_obsolete' => (bool)($rev->is_obsolete ?? 0)
                ];
            });

        // Metadata Paket
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('users as u', 'dp.created_by', '=', 'u.id')
            ->leftJoin('departments as dept', 'u.id_dept', '=', 'dept.id')
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dtg.name as doctype_group',
                'dsc.name as doctype_subcategory',
                'pg.code_part_group as part_group',
                'dept.is_eng'
            )
            ->first();

        if (!$package) {
            abort(404, 'Package details incomplete or not found.');
        }

        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $obsoleteDate = $revision->obsolete_at
            ? Carbon::parse($revision->obsolete_at)
            : null;

        $lastApproval = DB::table('package_approvals as pa')
            ->leftJoin('users as u', 'u.id', '=', 'pa.decided_by')
            ->leftJoin('departments as d', 'd.id', '=', 'u.id_dept')
            ->where('pa.revision_id', $id)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first([
                'u.name as approver_name',
                'd.code as dept_name'
            ]);

        $obsoleteDateText = null;
        if ($obsoleteDate) {
            try {
                $obsoleteDateText = $obsoleteDate->toSaiStampFormat();
            } catch (\Exception $e) {
                $obsoleteDateText = $obsoleteDate->format('M d, Y');
            }
        }

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDateText,
            'name' => $revision->obsolete_name
                ?? optional($lastApproval)->approver_name
                ?? '-',
            'dept' => $revision->obsolete_dept
                ?? optional($lastApproval)->dept_name
                ?? '-',
        ];

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'file_size', 'ori_position', 'copy_position', 'obslt_position')
            ->get();

        $extList = $fileRows
            ->map(fn($r) => strtolower(pathinfo($r->name, PATHINFO_EXTENSION)))
            ->filter()
            ->unique()
            ->values();

        $extUpper = $extList->map(fn($e) => strtoupper($e));

        $extIcons = $extUpper->isEmpty()
            ? []
            : FileExtensions::whereIn('code', $extUpper)
            ->get()
            ->mapWithKeys(fn(FileExtensions $m) => [strtolower($m->code) => $m->icon_src])
            ->all();

        $files = $fileRows
            ->groupBy('category')
            ->map(function ($items) use ($extIcons) {
                return $items->map(function ($item) use ($extIcons) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);

                    $ext = strtolower(pathinfo($item->name, PATHINFO_EXTENSION));
                    $iconSrc = $extIcons[$ext] ?? null;

                    return [
                        'name'     => $item->name,
                        'url'      => $url,
                        'file_id'  => $item->id,
                        'icon_src' => $iconSrc,
                        'ori_position' => $item->ori_position,
                        'copy_position' => $item->copy_position,
                        'obslt_position' => $item->obslt_position,
                        'size'          => $item->file_size,
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $detail = [
            'revision_no' => $revision->revision_no,
            'note' => $revision->note ?? null,
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
                'revision_label' => $revision->revision_label ?? null,
                'ecn_no' => $revision->ecn_no,
                'doc_type' => $package->doctype_group,
                'category' => $package->doctype_subcategory,
                'part_group' => $package->part_group,
                'linked_partners' => $this->getLinkedPartners($package->part_no),
            ],
            'status'       => 'Approved',
            'files'        => $files,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'is_obsolete'  => $isObsolete,
                'obsolete_info' => $obsoleteStampInfo,
            ],
            'id' => $originalEncryptedId,
            'revisionHistory' => $revisionList,
        ];

        $stampFormat = StampFormat::where('is_active', true)->first();

        // Mendapatkan kode departemen pengguna
        $userDeptCode = null;
        $isEngineering = false;

        if (Auth::check() && Auth::user()->id_dept) {
            $dept = DB::table('departments')->where('id', Auth::user()->id_dept)->first();
            $userDeptCode = $dept->code ?? null;
            $isEngineering = (bool) ($dept->is_eng ?? false);
        }

        $userName = null;
        if (Auth::check()) {
            $userName = Auth::user()->name ?? null;
        }

        return view('file_management.file_export_detail', [
            'exportId' => $originalEncryptedId,
            'detail'   => $detail,
            'revisionList' => $revisionList,
            'stampFormat' => $stampFormat,
            'userDeptCode' => $userDeptCode,
            'isEngineering' => $isEngineering,
            'userName' => $userName,
        ]);
    }

    public function getRevisionDetailJson(Request $request, $id)
    {
        $originalEncryptedId = $id;
        try {
            $decrypted_id = decrypt(str_replace('-', '=', $id));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid revision ID.'], 404);
        }

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
            return response()->json(['success' => false, 'message' => 'Exportable revision not found.'], 404);
        }

        // CEK AKSES
        $this->abortIfNoAccessToPackage((int)$revision->package_id);

        $revisionList = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.package_id', $revision->package_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->orderBy('dpr.revision_no', 'desc')
            ->select('dpr.id', 'dpr.revision_no', 'crl.label', 'dpr.is_obsolete')
            ->get()
            ->map(function ($rev) {
                $labelText = $rev->label ? " | {$rev->label}" : "";
                return [
                    'id'   => str_replace('=', '-', encrypt($rev->id)),
                    'revision' => "Rev-{$rev->revision_no}{$labelText}",
                    'revision_no' => $rev->revision_no,
                    'is_obsolete' => (bool)($rev->is_obsolete ?? 0)
                ];
            });

        // Metadata Paket
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('users as u', 'dp.created_by', '=', 'u.id')
            ->leftJoin('departments as dept', 'u.id_dept', '=', 'dept.id')
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dtg.name as doctype_group',
                'dsc.name as doctype_subcategory',
                'pg.code_part_group as part_group',
                'dept.is_eng'
            )
            ->first();

        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $obsoleteDate = $revision->obsolete_at
            ? Carbon::parse($revision->obsolete_at)
            : null;

        $lastApproval = DB::table('package_approvals as pa')
            ->leftJoin('users as u', 'u.id', '=', 'pa.decided_by')
            ->leftJoin('departments as d', 'd.id', '=', 'u.id_dept')
            ->where('pa.revision_id', $decrypted_id)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first([
                'u.name as approver_name',
                'd.code as dept_name'
            ]);

        $obsoleteDateText = null;
        if ($obsoleteDate) {
            try {
                $obsoleteDateText = $obsoleteDate->toSaiStampFormat();
            } catch (\Exception $e) {
                $obsoleteDateText = $obsoleteDate->format('M d, Y');
            }
        }

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDateText,
            'name' => $revision->obsolete_name
                ?? optional($lastApproval)->approver_name
                ?? '-',
            'dept' => $revision->obsolete_dept
                ?? optional($lastApproval)->dept_name
                ?? '-',
        ];

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $decrypted_id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'file_size', 'ori_position', 'copy_position', 'obslt_position')
            ->get();

        $extList = $fileRows
            ->map(fn($r) => strtolower(pathinfo($r->name, PATHINFO_EXTENSION)))
            ->filter()
            ->unique()
            ->values();

        $extUpper = $extList->map(fn($e) => strtoupper($e));

        $extIcons = $extUpper->isEmpty()
            ? []
            : FileExtensions::whereIn('code', $extUpper)
            ->get()
            ->mapWithKeys(fn(FileExtensions $m) => [strtolower($m->code) => $m->icon_src])
            ->all();

        $files = $fileRows
            ->groupBy('category')
            ->map(function ($items) use ($extIcons) {
                return $items->map(function ($item) use ($extIcons) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);

                    $ext = strtolower(pathinfo($item->name, PATHINFO_EXTENSION));
                    $iconSrc = $extIcons[$ext] ?? null;

                    return [
                        'name'     => $item->name,
                        'url'      => $url,
                        'file_id'  => $item->id,
                        'icon_src' => $iconSrc,
                        'ori_position' => $item->ori_position,
                        'copy_position' => $item->copy_position,
                        'obslt_position' => $item->obslt_position,
                        'size'          => $item->file_size,
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package details incomplete or not found.'], 500);
        }

        $detail = [
            'revision_no' => $revision->revision_no,
            'note' => $revision->note ?? null,
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
                'revision_label' => $revision->revision_label ?? null,
                'ecn_no' => $revision->ecn_no,
                'doc_type' => $package->doctype_group,
                'category' => $package->doctype_subcategory,
                'part_group' => $package->part_group,
                'linked_partners' => $this->getLinkedPartners($package->part_no),
            ],
            'status'       => 'Approved',
            'files' => $files,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'is_obsolete'  => $isObsolete,
                'obsolete_info' => $obsoleteStampInfo,
            ],
            'id' => $originalEncryptedId,
            'revisionHistory' => $revisionList,
        ];

        $stampFormat = StampFormat::where('is_active', true)->first();

        $userName = null;
        if (Auth::check()) {
            $userName = Auth::user()->name ?? null;
        }

        $userDeptCode = null;
        $isEngineering = false;

        if (Auth::check() && Auth::user()->id_dept) {
            $dept = DB::table('departments')->where('id', Auth::user()->id_dept)->first();
            $userDeptCode = $dept->code ?? null;
            $isEngineering = (bool) ($dept->is_eng ?? false);
        }

        return response()->json([
            'success'  => true,
            'pkg'      => $detail,
            'exportId' => $originalEncryptedId,
            'stampFormat' => $stampFormat,
            'userName' => $userName,
            'isEngineering' => $isEngineering,
        ]);
    }

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

        // CEK AKSES
        $this->abortIfNoAccessToPackage((int)$revision->package_id);

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
                ->where('dp.id', $revision->package_id)
                ->select('c.code as customer', 'm.name as model', 'p.part_no')
                ->first();

            $stampFormat = StampFormat::where('is_active', true)->first();

            if ($package && $stampFormat) {
                try {
                    $tempPath = $this->_burnStamps($path, $file, $revision, $package, $stampFormat);

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
            return response()->json(['success' => false, 'message' => 'Package not found or not approved.'], 404);
        }

        $revision = DB::table('doc_package_revisions')
            ->where('id', $decrypted_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            return response()->json(['success' => false, 'message' => 'Package not found or not approved.'], 404);
        }

        // CEK AKSES
        $this->abortIfNoAccessToPackage((int)$revision->package_id);

        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->where('dp.id', $revision->package_id)
            ->select('c.code as customer', 'm.name as model', 'p.part_no', 'dp.part_group_id')
            ->first();

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Associated package details not found.'], 404);
        }

        $files = DocPackageRevisionFile::where('revision_id', $decrypted_id)->get();

        if ($files->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No files found for this package revision.'], 404);
        }

        $stampFormat = StampFormat::where('is_active', true)->first();
        $stampableTypes = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'pdf'];
        $canStamp = class_exists('\Imagick') && $stampFormat;

        $customer = Str::slug($package->customer);
        $model = Str::slug($package->model);
        $partNo = Str::slug($package->part_no);
        $ecn = Str::slug($revision->ecn_no);
        $timestamp = now()->format('Ymd');

        $zipFileName = strtoupper("{$customer}-{$model}-{$partNo}-{$ecn}-{$timestamp}") . ".zip";

        $zipDirectory = storage_path('app/public');
        $zipDirectory = str_replace('/', DIRECTORY_SEPARATOR, $zipDirectory); // Normalisasi path

        if (!file_exists($zipDirectory)) {
            if (!mkdir($zipDirectory, 0775, true)) {
                $errorMsg = 'Failed to create zip directory. Check permissions for storage/app.';
                Log::error($errorMsg, ['path' => $zipDirectory]);
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }
        }

        if (!is_writable($zipDirectory)) {
            $errorMsg = 'Zip directory exists but is not writable. Check permissions for storage/app/public.';
            Log::error($errorMsg, ['path' => $zipDirectory]);
            return response()->json(['success' => false, 'message' => $errorMsg], 500);
        }

        $zipFilePath = $zipDirectory . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();

        Log::info('Attempting to open ZipArchive at normalized path', ['path' => $zipFilePath]);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Log::error('ZipArchive::open() failed. Check permissions and path.', ['path' => $zipFilePath]);
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
                    $tempPath = $this->_burnStamps($filePath, $file, $revision, $package, $stampFormat);
                    if ($tempPath !== $filePath) {
                        $fileToAdd = $tempPath;
                        $deleteAfterAdd = true;
                    }
                } catch (\Exception $e) {
                    Log::error('Imagick stamp burn failed for zip', [
                        'file_id' => $file->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (file_exists($fileToAdd)) {
                $category = strtolower(trim($file->category));
                $folderInZip = 'ECN';
                if ($category === '2d') {
                    $folderInZip = '2D';
                } elseif ($category === '3d') {
                    $folderInZip = '3D';
                }

                $pathInZip = str_replace('/', DIRECTORY_SEPARATOR, $folderInZip . '/' . $file->filename);

                $zip->addFile($fileToAdd, $pathInZip);

                $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'hpgl', 'tif', 'pdf', 'zip', 'rar', 'step', 'stp', 'igs', 'iges', 'catpart', 'catproduct'])) {
                    $zip->setCompressionName($pathInZip, ZipArchive::CM_STORE);
                }

                $filesAddedCount++;

                if ($deleteAfterAdd) {
                    $tempFilesToDelete[] = $fileToAdd;
                }
            } else {
                Log::warning('File not found and skipped for zipping: ' . $filePath . ' (or temp file: ' . $fileToAdd . ')');
            }
        }

        $closeSuccess = false;
        try {
            $closeSuccess = $zip->close();
            if (!$closeSuccess) {
                Log::error('zip->close() returned false.', ['path' => $zipFilePath, 'status' => $zip->status, 'statusString' => $zip->getStatusString()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception at zip->close(). This is likely a source file issue.', ['path' => $zipFilePath, 'error' => $e->getMessage()]);
            $closeSuccess = false;
        }

        foreach ($tempFilesToDelete as $tempFile) {
            @unlink($tempFile);
        }
        if (!$closeSuccess) {
            return response()->json(['success' => false, 'message' => 'Failed to finalize zip file. Check logs for details.'], 500);
        }

        if ($filesAddedCount === 0) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }
            return response()->json(['success' => false, 'message' => 'No physical files were found to add to the zip package.'], 404);
        }

        $downloadUrl = URL::temporarySignedRoute(
            'export.get-zip',
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
                        ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                        ->where('dp.id', $revision->package_id)
                        ->select(
                            'c.code as customer', 
                            'm.name as model', 
                            'p.part_no', 
                            'dp.part_group_id',
                            'dtg.name as doc_type'
                        )
                        ->first();
                }

                if ($revision && $package) {
                    $labelName = null;
                    if (!empty($revision->revision_label_id)) {
                        $labelName = DB::table('customer_revision_labels')
                            ->where('id', $revision->revision_label_id)
                            ->value('label');
                    }
                    $partGroupCode = null;
                    if (!empty($package->part_group_id)) {
                        $partGroupCode = DB::table('part_groups')
                            ->where('id', $package->part_group_id)
                            ->value('code_part_group');
                    }

                    // Hitung Ukuran File
                    $fileBytes = file_exists($zipFilePath) ? filesize($zipFilePath) : 0;
                    // Format ke Human Readable (KB/MB)
                    $sizeFormatted = $fileBytes >= 1048576 
                        ? number_format($fileBytes / 1048576, 2) . ' MB' 
                        : number_format($fileBytes / 1024, 2) . ' KB';

                    $metaLogData = [
                        'part_no'         => $package->part_no,
                        'customer_code'   => $package->customer,
                        'model_name'      => $package->model,
                        'doc_type'        => $package->doc_type,
                        'part_group_code' => $partGroupCode,
                        'package_id'      => $revision->package_id,
                        'revision_no'     => $revision->revision_no,
                        'ecn_no'          => $revision->ecn_no,
                        'revision_label'  => $labelName,
                        'downloaded_file' => $safe_filename,
                        'file_size'       => $sizeFormatted,
                        'file_bytes'      => $fileBytes
                    ];

                    ActivityLog::create([
                        'user_id'       => Auth::user()->id,
                        'activity_code' => 'DOWNLOAD',
                        'scope_type'    => 'revision',
                        'scope_id'      => $revision->package_id,
                        'revision_id'   => $revision->id,
                        'meta'          => $metaLogData,
                    ]);
                }

            } catch (\Exception $e) {
                Log::error('Failed to create download activity log', [
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
            $calculatedWidth = max($widthTop, $widthBot, $widthCen) + $paddingHorizontal;
            $canvasWidth = (int) max($minWidth, $calculatedWidth);
            // $canvasHeight = 120; // OLD FIXED HEIGHT
            $canvasHeight = (int) ($canvasWidth * 0.35); // NEW DYNAMIC HEIGHT (Ratio 0.35 matches frontend)

            // Calculate Row Height (1/3 of total)
            $rowH = $canvasHeight / 3;

            // --- 2. SETUP KANVAS ---
            $stamp = new \Imagick();
            // OPACITY UPDATE: 0.55 to match frontend "globalAlpha = 0.55"
            $bgOpacity = 0.3; 
            $stamp->newImage($canvasWidth, $canvasHeight, new \ImagickPixel("rgba(255, 255, 255, $bgOpacity)"));
            $stamp->setImageFormat('png');

            // Gunakan warna dengan opacity untuk semua format
            $opacity = 0.4;
            if ($colorMode === 'red') {
                $borderColor = new \ImagickPixel("rgba(220, 38, 38, $opacity)"); // Merah
                $textColor   = new \ImagickPixel("rgba(185, 28, 28, $opacity)");
            } elseif ($colorMode === 'gray') {
                // Warna Abu-abu (Gray-500 / Gray-600 style)
                $borderColor = new \ImagickPixel("rgba(107, 114, 128, $opacity)");
                $textColor   = new \ImagickPixel("rgba(75, 85, 99, $opacity)");
            } else {
                // Default Blue (Blue-700 / Blue-600)
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

            // Teks Atas (Center)
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

    private function _burnStamps(string $originalPath, DocPackageRevisionFile $file, object $revision, object $package, ?StampFormat $stampFormat): string
    {
        if (!class_exists('Imagick') || !file_exists($originalPath)) {
            return $originalPath;
        }

        $userDeptCode = null;
        if (Auth::check() && Auth::user()->id_dept) {
            $dept = DB::table('departments')->where('id', Auth::user()->id_dept)->first();
            $userDeptCode = $dept->code ?? null;
        }

        $isEng = false;
        if ($userDeptCode) {
            $isEng = (bool) DB::table('departments')->where('code', $userDeptCode)->value('is_eng');
        }

        $originalStampColor = $isEng ? 'blue' : 'gray';

        try {
            // Helper Format Tanggal (Lokal)
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

            $topLine    = "Date Received : " . $receiptDateStr;
            $bottomLine = "Date Uploaded : " . $uploadDateStr;

            // --- B. STAMP CONTROL COPY ---
            $now = now();
            $datePart = $formatSaiDate($now);
            $timePart = $now->format('H:i:s');

            $deptCode = '--';
            if (Auth::check() && Auth::user()->id_dept) {
                $dept = DB::table('departments')->where('id', Auth::user()->id_dept)->first();
                if ($dept && isset($dept->code)) $deptCode = $dept->code;
            }

            $topLineCopy = "SAI / {$deptCode} / {$datePart} {$timePart}";

            $userName = '--';
            if (Auth::check()) {
                $userName = Auth::user()->name ?? '--';
            }
            $bottomLineCopy = "Downloaded By {$userName}";

            // --- POSISI STAMP ---
            $posOriginal = $this->positionIntToKey($file->ori_position ?? 0, 'bottom-left');
            $posCopy     = $this->positionIntToKey($file->copy_position ?? 1, 'bottom-center');
            $posObsolete = $this->positionIntToKey($file->obslt_position ?? 2, 'bottom-right');

            // --- GENERATE MASTER GAMBAR ---
            $stampOriginal = $this->_createStampImage('SAI-DRAWING ORIGINAL', $topLine, $bottomLine, $originalStampColor);
            $stampCopy     = $this->_createStampImage('SAI-DRAWING CONTROLLED COPY', $topLineCopy, $bottomLineCopy, 'blue');
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

            if (!$stampOriginal || !$stampCopy) {
                Log::warning('Could not create master stamp images, returning original file.');
                return $originalPath;
            }

            // 3. Proses Imagick
            $masterStampWidth = $stampOriginal->getImageWidth();
            $masterStampHeight = $stampOriginal->getImageHeight();
            $stampAspectRatio = $masterStampWidth / $masterStampHeight;

            $imagick = new \Imagick();
            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
            $isPdf = ($ext === 'pdf');
            $isTiff = in_array($ext, ['tif', 'tiff']);

            if ($isPdf) {
                $imagick->setResolution(150, 150);
            }
            $imagick->readImage($originalPath);

            $marginPercent = 0.04; // Increased margin to 4%
            $stampWidthPercent = 0.15;
            $minStampWidth = 250; // Match frontend

            // --- DEFINISI CLOSURE ---
            $processPage = function ($iteratorIndex) use (
                $imagick,
                $stampOriginal,
                $stampCopy,
                $stampObsolete,
                $isObsolete,
                $file,
                $stampAspectRatio,
                $marginPercent,
                $stampWidthPercent,
                $minStampWidth,
                $posOriginal,
                $posCopy,
                $posObsolete,
                $isTiff
            ) {
                $imagick->setIteratorIndex($iteratorIndex);
                $imgWidth = $imagick->getImageWidth();
                $imgHeight = $imagick->getImageHeight();
                $margin = max(8, (int) (min($imgWidth, $imgHeight) * $marginPercent));

                $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));
                // Add Max Width Cap (40%) to match frontend
                $maxStampWidth = (int) ($imgWidth * 0.4);
                $newStampWidth = min($newStampWidth, $maxStampWidth);
                $newStampHeight = (int) ($newStampWidth / $stampAspectRatio);

                // Resize Stamps
                $stampOrigResized = $stampOriginal->clone();
                $stampOrigResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);

                $stampCopyResized = $stampCopy->clone();
                $stampCopyResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);

                $stampObsResized = null;
                if ($isObsolete && $stampObsolete) {
                    $stampObsResized = $stampObsolete->clone();
                    $stampObsResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);
                }

                if ($isTiff) {
                    if ($imgWidth > 2500) {
                        $imagick->scaleImage(2500, 0);
                        $imgWidth = $imagick->getImageWidth();
                        $imgHeight = $imagick->getImageHeight();
                        
                        $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));
                        $newStampHeight = (int) ($newStampWidth / $stampAspectRatio);
                        
                        $stampOrigResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);
                        $stampCopyResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);
                        if ($stampObsResized) {
                            $stampObsResized->resizeImage($newStampWidth, $newStampHeight, \Imagick::FILTER_LANCZOS, 1);
                        }
                    }

                    // Convert ke RGB untuk support warna
                    $imagick->transformImageColorspace(\Imagick::COLORSPACE_SRGB);
                    $imagick->setImageType(\Imagick::IMGTYPE_TRUECOLOR);
                }

                // Composite Stamps
                list($x, $y) = $this->calculateStampCoordinates($posOriginal, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                $imagick->compositeImage($stampOrigResized, \Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

                list($xCopy, $yCopy) = $this->calculateStampCoordinates($posCopy, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                $imagick->compositeImage($stampCopyResized, \Imagick::COMPOSITE_OVER, (int)$xCopy, (int)$yCopy);

                if ($stampObsResized) {
                    list($xObs, $yObs) = $this->calculateStampCoordinates($posObsolete, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                    $imagick->compositeImage($stampObsResized, \Imagick::COMPOSITE_OVER, (int)$xObs, (int)$yObs);
                    $stampObsResized->destroy();
                }
                
                // OPTIMASI FINAL: Smart Palette 16 Warna
                if ($isTiff) {
                    $imagick->setImageFormat('tiff');
                    
                    try {
                        // Palette 16 Warna (4-bit)
                        $palette = new \Imagick();
                        $palette->newImage(1, 16, new \ImagickPixel('white')); 
                        $palette->setImageFormat('png');
                        
                        $pixelIter = $palette->getPixelIterator();
                        $colors = [
                            '#FFFFFF', // Putih (Background)
                            '#000000', // Hitam (Lines)
                            
                            // Blue Spectrum
                            '#A8C1F7', // Light Blue
                            '#608AF5', // Medium Blue (Jembtan antara Light & Dark)
                            '#2563EB', // Blue Solid
                            '#0F285E', // Dark Blue
                            
                            // Red Spectrum
                            '#F1A8A8', // Light Red
                            '#E06060', // Medium Red
                            '#DC2626', // Red Solid
                            '#580F0F', // Dark Red

                            // Gray Spectrum
                            '#C4C7CC', // Light Gray
                            '#9CA3AF', // Medium Gray
                            '#6B7280', // Gray Solid
                            '#2B2D33', // Dark Gray
                            
                            // Extra Shades for smoothness
                            '#E5E7EB', // Very Light Gray (Background noise)
                            '#111827', // Very Dark Gray (Line antialias)
                        ];
                        
                        $index = 0;
                        foreach ($pixelIter as $row => $pixels) {
                            foreach ($pixels as $column => $pixel) {
                                if (isset($colors[$index])) $pixel->setColor($colors[$index]);
                                $index++;
                            }
                            $pixelIter->syncIterator();
                        }
                        
                        // Remap Image ke 16 warna
                        $imagick->remapImage($palette, false);
                        $palette->clear();
                        
                        $imagick->setImageDepth(4); // 4-bit fits 16 colors
                    } catch (\Exception $e) {
                         $imagick->quantizeImage(16, \Imagick::COLORSPACE_RGB, 0, false, false);
                    }
                }

                $stampOrigResized->destroy();
                $stampCopyResized->destroy();
            };

            if ($isPdf || $isTiff) {
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

            // Simpan File
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0775, true);

            $outputExt = $isPdf ? 'pdf' : strtolower($imagick->getImageFormat());
            $tempPath = $tempDir . '/' . Str::uuid()->toString() . '.' . $outputExt;

            // TIFF: Gunakan kompresi ZIP (Deflate) secara global
            if ($isTiff) {
                $imagick->resetIterator();
                foreach ($imagick as $frame) {
                    $frame->setImageCompression(\Imagick::COMPRESSION_ZIP);
                    $frame->setImageCompressionQuality(90);
                }
            }

            if ($isPdf || $isTiff) {
                $imagick->writeImages($tempPath, true);
            } else {
                $imagick->writeImage($tempPath);
            }

            // Cleanup
            $imagick->clear();
            $imagick->destroy();
            $stampOriginal->clear();
            $stampOriginal->destroy();
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

    private function userHasAnyRole(User $user, array $roleNames): bool
    {
        if (!$user) {
            return false;
        }

        return DB::table('user_roles as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $user->id)
            ->whereIn('r.role_name', $roleNames) 
            ->exists();
    }

    private function abortIfNoAccessToPackage(int $packageId): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Access denied.');
        }

        $feasId = $this->getFeasibilityStatusId();

        $pkg = DB::table('doc_packages as dp')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->where('dp.id', $packageId)
            ->select('m.status_id')
            ->first();

        if (!$pkg) {
            abort(404, 'Package not found.');
        }

        // Kalau tidak ada Feasibility di master / status bukan Feasibility -> bebas diakses
        if (!$feasId || $pkg->status_id != $feasId) {
            return;
        }

        // Kalau Feasibility -> hanya role tertentu yang boleh akses
        if (!$this->userHasAnyRole($user, ['ENG'])) {
            abort(403, 'Access denied to feasibility package.');
        }
    }

    private function getLinkedPartners($partNo)
    {
        $product = DB::table('products')->where('part_no', $partNo)->first();
        if (!$product || !$product->group_id) {
            return [];
        }

        return DB::table('products')
            ->where('group_id', $product->group_id)
            ->where('id', '!=', $product->id)
            ->pluck('part_no')
            ->toArray();
    }
}
