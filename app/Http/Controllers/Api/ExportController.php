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
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportController extends Controller
{
    public function kpi(Request $req)
    {
        $latestRevisionsSubquery = DB::table('doc_package_revisions')
            ->select('package_id', DB::raw('MAX(revision_no) as max_revision_no'))
            ->where('revision_status', '=', 'approved')
            ->groupBy('package_id');

        $q = DB::table('doc_package_revisions as dpr')
            ->joinSub($latestRevisionsSubquery, 'latest_revs', function ($join) {
                $join->on('dpr.package_id', '=', 'latest_revs.package_id')
                     ->on('dpr.revision_no', '=', 'latest_revs.max_revision_no');
            })
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('dpr.revision_status', '=', 'approved');

        $downloadQuery = DB::table('activity_logs as al')
            ->join('doc_package_revisions as dpr', 'al.revision_id', '=', 'dpr.id')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('al.activity_code', '=', 'DOWNLOAD');

        $totalRevisionsQuery = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id') // Join products
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->where('dpr.revision_status', '=', 'approved');

        if ($req->filled('customer') && $req->customer !== 'All') {
            $q->where('c.code', $req->customer);
            $downloadQuery->where('c.code', $req->customer);
            $totalRevisionsQuery->where('c.code', $req->customer);
        }
        if ($req->filled('model') && $req->model !== 'All') {
            $q->where('m.name', $req->model);
            $downloadQuery->where('m.name', $req->model);
            $totalRevisionsQuery->where('m.name', $req->model);
        }
        if ($req->filled('doc_type') && $req->doc_type !== 'All') {
            $q->where('dtg.name', $req->doc_type);
            $downloadQuery->where('dtg.name', $req->doc_type);
            $totalRevisionsQuery->where('dtg.name', $req->doc_type);
        }
        if ($req->filled('category') && $req->category !== 'All') {
            $q->where('dsc.name', $req->category);
            $downloadQuery->where('dsc.name', $req->category);
            $totalRevisionsQuery->where('dsc.name', $req->category);
        }

        $totalApprovedPackages = $q->count();
        $totalDownloads = $downloadQuery->count();
        $totalApprovedRevisions = $totalRevisionsQuery->count();

        return response()->json([
            'cards' => [
                'total' => $totalApprovedPackages,
                'total_download' => $totalDownloads,
                'total_revisions' => $totalApprovedRevisions
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
                    $builder = DB::table('models as m')
                        ->join('customers as c', 'm.customer_id', '=', 'c.id')
                        ->selectRaw('m.name AS id, m.name AS text')
                        ->when($customerCode && $customerCode !== 'All', fn($x) => $x->where('c.code', $customerCode))
                        ->when($q, fn($x) => $x->where('m.name', 'like', "%{$q}%"))
                        ->orderBy('m.name');

                    $total = (clone $builder)->count();
                    $items = $builder->forPage($page, $perPage)->get();
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
            'Revision' => 'dpr.revision_no',
            'ecn_no' => 'dpr.ecn_no',
            'doctype_group' => 'dtg.name',
            'doctype_subcategory' => 'dsc.name',
            'part_group' => 'pg.code_part_group',
            'uploaded_at' => 'dpr.created_at'
        ];

        $orderColumnName = $columnMap[$request->get('columns')[$orderColumnIndex]['name']] ?? 'dpr.created_at';

        $latestRevisionsSubquery = DB::table('doc_package_revisions')
            ->select('package_id', DB::raw('MAX(revision_no) as max_revision_no'))
            ->where('revision_status', '=', 'approved')
            ->groupBy('package_id');

        $query = DB::table('doc_package_revisions as dpr')
            ->joinSub($latestRevisionsSubquery, 'latest_revs', function ($join) {
                $join->on('dpr.package_id', '=', 'latest_revs.package_id')
                     ->on('dpr.revision_no', '=', 'latest_revs.max_revision_no');
            })
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.revision_status', '=', 'approved');

        $recordsTotal = $query->count();

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

        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = $query->count();

        $data = $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'p.part_no',
            'dpr.revision_no',
            'crl.label as revision_label_name',
            'dpr.ecn_no',
            'dtg.name as doctype_group',
            'dsc.name as doctype_subcategory',
            'pg.code_part_group as part_group',
            DB::raw("FORMAT(dpr.created_at, 'yyyy-MM-dd HH:mm:ss') as uploaded_at")
        )
            ->orderBy($orderColumnName, $orderDir)
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function($row) {
                $row->id = str_replace('=', '-', encrypt($row->id));
                return $row;
            });

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
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

        // $id = revision_id
        $revision = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.id', $id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select('dpr.*', 'crl.label as revision_label')
            ->first();

        if (!$revision) {
            abort(404, 'Exportable file not found or not approved.');
        }

        $revisionList = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.package_id', $revision->package_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->orderBy('dpr.revision_no', 'desc')
            ->select('dpr.id', 'dpr.revision_no', 'crl.label')
            ->get()
            ->map(function ($rev) {
                $labelText = $rev->label ? " ({$rev->label})" : "";
                return [
                    'id'   => str_replace('=', '-', encrypt($rev->id)),
                    'text' => "Rev-{$rev->revision_no}{$labelText}"
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
            ->where('dp.id', $revision->package_id)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dtg.name as doctype_group',
                'dsc.name as doctype_subcategory',
                'pg.code_part_group as part_group'
            )
            ->first();

        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'ori_position', 'copy_position', 'obslt_position')
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
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $detail = [
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
                'revision_label' => $revision->revision_label ?? null
            ],
            'classification' => [
                'doctype_group' => $package->doctype_group,
                'doctype_subcategory' => $package->doctype_subcategory ?? '-',
                'part_group' => $package->part_group ?? '-',
            ],
            'files'        => $files,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'is_obsolete'  => $isObsolete,
            ],
        ];

        $stampFormat = StampFormat::where('is_active', true)->first();

        return view('file_management.file_export_detail', [
            'exportId' => $originalEncryptedId,
            'detail'   => $detail,
            'revisionList' => $revisionList,
            'stampFormat' => $stampFormat,
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
            ->where('dpr.id', $decrypted_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select('dpr.*', 'crl.label as revision_label')
            ->first();

        if (!$revision) {
            return response()->json(['success' => false, 'message' => 'Exportable revision not found.'], 404);
        }

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
                'p.part_no',
                'dtg.name as doctype_group',
                'dsc.name as doctype_subcategory',
                'pg.code_part_group as part_group'
            )
            ->first();

        $receiptDate = $revision->receipt_date
            ? Carbon::parse($revision->receipt_date)
            : null;

        $uploadDateRevision = $revision->created_at
            ? Carbon::parse($revision->created_at)
            : null;

        $isObsolete = (bool)($revision->is_obsolete ?? 0);

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $decrypted_id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'ori_position', 'copy_position', 'obslt_position')
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
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $detail = [
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
                'revision_label' => $revision->revision_label ?? null
            ],
            'classification' => [
                'doctype_group' => $package->doctype_group,
                'doctype_subcategory' => $package->doctype_subcategory ?? '-',
                'part_group' => $package->part_group ?? '-',
            ],
            'files' => $files,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'is_obsolete'  => $isObsolete,
            ],
        ];

        $stampFormat = StampFormat::where('is_active', true)->first();

        return response()->json([
            'success'  => true,
            'pkg'      => $detail,
            'exportId' => $originalEncryptedId,
            'stampFormat' => $stampFormat,
        ]);
    }

    public function downloadFile($file_id)
    {
        set_time_limit(0);

        $file = DocPackageRevisionFile::find($file_id);

        if (!$file) {
            abort(404, 'File not found.');
        }

        $revision = DB::table('doc_package_revisions')
            ->where('id', $file->revision_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            abort(403, 'Access denied. File is not part of an approved revision.');
        }

        $path = Storage::disk('datacenter')->path($file->storage_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server storage.');
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

        $customer = Str::slug($package->customer);
        $model = Str::slug($package->model);
        $partNo = Str::slug($package->part_no);
        $ecn = Str::slug($revision->ecn_no);
        $timestamp = now()->format('Ymd');

        $zipFileName = strtoupper("{$customer}-{$model}-{$partNo}-{$ecn}-{$timestamp}") . ".zip";
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Log::error('Could not create zip file', ['path' => $zipFilePath]);
            return response()->json(['success' => false, 'message' => 'Could not create zip file on server.'], 500);
        }

        $filesAddedCount = 0;
        foreach ($files as $file) {
            $filePath = Storage::disk('datacenter')->path($file->storage_path);

            if (file_exists($filePath)) {
                $category = strtolower(trim($file->category));
                $folderInZip = 'ecn';
                if ($category === '2d') {
                    $folderInZip = '2d';
                } elseif ($category === '3d') {
                    $folderInZip = '3d';
                }

                $pathInZip = $folderInZip . '/' . $file->filename;
                $zip->addFile($filePath, $pathInZip);

                // OPTIMASI: Jangan kompres file yang sudah terkompresi
                $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'hpgl', 'tif', 'pdf', 'zip', 'rar', 'step', 'stp', 'igs', 'iges', 'catpart', 'catproduct'])) {
                    $zip->setCompressionName($pathInZip, ZipArchive::CM_STORE);
                }
                $filesAddedCount++;
            } else {
                Log::warning('File not found and skipped for zipping: ' . $filePath);
            }
        }
        $zip->close();

        if ($filesAddedCount === 0) {
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
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
                        ->where('dp.id', $revision->package_id)
                        ->select('c.code as customer', 'm.name as model', 'p.part_no', 'dp.part_group_id')
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

                    $metaLogData = [
                        'part_no' => $package->part_no,
                        'customer_code' => $package->customer,
                        'model_name' => $package->model,
                        'part_group_code' => $partGroupCode,
                        'package_id' => $revision->package_id,
                        'revision_no' => $revision->revision_no,
                        'ecn_no' => $revision->ecn_no,
                        'revision_label' => $labelName,
                        'downloaded_file' => $safe_filename
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
}
