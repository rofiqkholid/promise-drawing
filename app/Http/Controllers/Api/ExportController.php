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
use Imagick;
use ImagickDraw;
use ImagickPixel;

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
            ->join('products as p', 'dp.product_id', '=', 'p.id')
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
            ],
            'status'       => 'Approved',
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
                'revision_label' => $revision->revision_label ?? null,
                'ecn_no' => $revision->ecn_no,
                'doc_type' => $package->doctype_group,
                'category' => $package->doctype_subcategory,
                'part_group' => $package->part_group,
            ],
            'status'       => 'Approved',
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

        $revision = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->where('dpr.id', $file->revision_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->select('dpr.*', 'dp.id as package_id')
            ->first();

        if (!$revision) {
            abort(403, 'Access denied. File is not part of an approved revision.');
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
                $folderInZip = 'ecn';
                if ($category === '2d') {
                    $folderInZip = '2d';
                } elseif ($category === '3d') {
                    $folderInZip = '3d';
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

    private function positionIntToKey(?int $pos, string $default = 'bottom-right'): string
    {
        switch ($pos) {
            case 0: return 'bottom-left';
            case 1: return 'bottom-center';
            case 2: return 'bottom-right';
            case 3: return 'top-left';
            case 4: return 'top-center';
            case 5: return 'top-right';
            default: return $default;
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

    private function _createStampImage(string $centerText, string $topLine, string $bottomLine): ?Imagick
    {
        try {
            $stamp = new Imagick();
            $canvasWidth = 336;
            $canvasHeight = 120;

            $stamp->newImage($canvasWidth, $canvasHeight, new ImagickPixel('transparent'));
            $stamp->setImageFormat('png');

            $draw = new ImagickDraw();

            $opacity = 0.85;
            $borderColor = new ImagickPixel("rgba(37, 99, 235, $opacity)");
            $textColor = new ImagickPixel("rgba(29, 78, 216, $opacity)");

            $font = null;
            $fontsToTry = [ 'DejaVu-Sans', 'Arial', 'Helvetica', 'Verdana', 'Tahoma', 'sans-serif' ];
            foreach ($fontsToTry as $fontName) {
                try {
                    $draw->setFont($fontName);
                    $font = $fontName;
                    break;
                } catch (\Exception $e) { continue; }
            }
            if ($font) { $draw->setFont($font); }

            $draw->setStrokeColor($borderColor);
            $draw->setFillColor(new ImagickPixel('transparent'));
            $draw->setStrokeWidth(3);
            $draw->roundRectangle(2, 2, $canvasWidth - 2, $canvasHeight - 2, 6, 6);
            $draw->line(2, 36, $canvasWidth - 2, 36);
            $draw->line(2, 84, $canvasWidth - 2, 84);
            $stamp->drawImage($draw);

            $draw->setFillColor($textColor);
            $draw->setTextAlignment(Imagick::ALIGN_LEFT);
            $x_center_canvas = $canvasWidth / 2; // 168

            // Teks Atas (Top Line)
            $draw->setFontSize(16);
            $draw->setFontWeight(600);
            $draw->setStrokeWidth(0);
            // Hitung metrik
            $metricsTop = $stamp->queryFontMetrics($draw, $topLine);
            $x_top = $x_center_canvas - ($metricsTop['textWidth'] / 2);
            $y_top = 27;
            $stamp->annotateImage($draw, $x_top, $y_top, 0, $topLine);

            // Teks Bawah (Bottom Line)
            $draw->setFontSize(16);
            $draw->setFontWeight(400);
            $draw->setStrokeWidth(0);
            // Hitung metrik
            $metricsBottom = $stamp->queryFontMetrics($draw, $bottomLine);
            $x_bottom = $x_center_canvas - ($metricsBottom['textWidth'] / 2);
            $y_bottom = 108;
            $stamp->annotateImage($draw, $x_bottom, $y_bottom, 0, $bottomLine);

            // Teks Tengah (Center Text)
            $draw->setFontSize(21);
            $draw->setFontWeight(900);
            $draw->setStrokeColor($textColor); // Faux-bold
            $draw->setStrokeWidth(0.75);
            $metricsCenter = $stamp->queryFontMetrics($draw, $centerText);
            $x_center = $x_center_canvas - ($metricsCenter['textWidth'] / 2);
            $y_center = ($canvasHeight / 2) + ($metricsCenter['textHeight'] / 3);
            $stamp->annotateImage($draw, $x_center, $y_center, 0, $centerText);


            $draw->clear();
            $draw->destroy();

            return $stamp;

        } catch (\Exception $e) {
            Log::error('Failed to create stamp image with Imagick: ' . $e->getMessage());
            return null;
        }
    }

    private function _burnStamps(string $originalPath, DocPackageRevisionFile $file, object $revision, object $package, ?StampFormat $stampFormat): string
    {
        if (!class_exists('Imagick') || !file_exists($originalPath)) {
            return $originalPath;
        }

        try {
            $receiptDate = $revision->receipt_date ? Carbon::parse($revision->receipt_date)->toDateString() : '';
            $uploadDate = $revision->created_at ? Carbon::parse($revision->created_at)->toDateString() : '';
            $topLine = ($stampFormat?->prefix ?: 'DATE RECEIVED') . ' : ' . $receiptDate;
            $bottomLine = ($stampFormat?->suffix ?: 'DATE UPLOADED') . ' : ' . $uploadDate;
            $isObsolete = (bool)($revision->is_obsolete ?? 0);

            $posOriginal = $this->positionIntToKey($file->ori_position ?? 2, 'bottom-right');
            $posCopy = $this->positionIntToKey($file->copy_position ?? 1, 'bottom-center');
            $posObsolete = $this->positionIntToKey($file->obslt_position ?? 0, 'bottom-left');

            // 1. Buat "Master" stempel (ukuran 336x120)
            $stampOriginal = $this->_createStampImage('ORIGINAL', $topLine, $bottomLine);
            $stampCopy = $this->_createStampImage('Control COPY', $topLine, $bottomLine);
            $stampObsolete = $isObsolete ? $this->_createStampImage('OBSOLETE', $topLine, $bottomLine) : null;

            if (!$stampOriginal || !$stampCopy) {
                Log::warning('Could not create master stamp images, returning original file.');
                return $originalPath;
            }

            // Dapatkan rasio aspek master stempel (336 / 120 = 2.8)
            $masterStampWidth = $stampOriginal->getImageWidth();
            $masterStampHeight = $stampOriginal->getImageHeight();
            $stampAspectRatio = $masterStampWidth / $masterStampHeight; // 2.8

            $imagick = new Imagick();
            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
            $isPdf = ($ext === 'pdf');

            if ($isPdf) {
                $imagick->setResolution(150, 150);
            }

            $imagick->readImage($originalPath);

            $marginPercent = 0.03; 
            // Tentukan persentase lebar stempel (misal: 15% dari lebar gambar)
            $stampWidthPercent = 0.15; 
            // Tentukan ukuran minimum stempel agar teks terbaca
            $minStampWidth = 224; // (1.0x ukuran lama)

            if ($isPdf) {
                $totalPages = $imagick->getNumberImages();
                for ($i = 0; $i < $totalPages; $i++) {
                    $imagick->setIteratorIndex($i);

                    $imgWidth = $imagick->getImageWidth();
                    $imgHeight = $imagick->getImageHeight();
                    $margin = max(8, (int) (min($imgWidth, $imgHeight) * $marginPercent));

                    // 2. Hitung ukuran stempel dinamis
                    $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));
                    $newStampHeight = (int) ($newStampWidth / $stampAspectRatio);

                    // 3. Clone dan resize stempel master
                    $stampOrigResized = $stampOriginal->clone();
                    $stampOrigResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                    
                    $stampCopyResized = $stampCopy->clone();
                    $stampCopyResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                    
                    $stampObsResized = null;
                    if ($isObsolete && $stampObsolete) {
                        $stampObsResized = $stampObsolete->clone();
                        $stampObsResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                    }

                    if ($imgWidth < $newStampWidth) {
                        Log::warning('Image width is smaller than stamp width, stamp may overlap.', [
                            'file_id' => $file->id, 'page' => $i, 'imgW' => $imgWidth, 'stampW' => $newStampWidth
                        ]);
                    }

                    // 4. Gunakan stempel yang sudah di-resize
                    list($x, $y) = $this->calculateStampCoordinates($posOriginal, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                    $imagick->compositeImage($stampOrigResized, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

                    list($xCopy, $yCopy) = $this->calculateStampCoordinates($posCopy, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                    $imagick->compositeImage($stampCopyResized, Imagick::COMPOSITE_OVER, (int)$xCopy, (int)$yCopy);

                    if ($stampObsResized) {
                        list($xObs, $yObs) = $this->calculateStampCoordinates($posObsolete, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                        $imagick->compositeImage($stampObsResized, Imagick::COMPOSITE_OVER, (int)$xObs, (int)$yObs);
                    }
                    
                    // 5. Hancurkan (destroy) clone stempel
                    $stampOrigResized->destroy();
                    $stampCopyResized->destroy();
                    if ($stampObsResized) $stampObsResized->destroy();
                }

            } else {
                // Logika yang sama untuk gambar non-PDF
                $mainImageIndex = 0;
                if ($imagick->getNumberImages() > 1) {
                    $maxResolution = 0;
                    foreach ($imagick as $i => $frame) {
                        $resolution = $frame->getImageWidth() * $frame->getImageHeight();
                        if ($resolution > $maxResolution) {
                            $maxResolution = $resolution;
                            $mainImageIndex = $i;
                        }
                    }
                }
                $imagick->setIteratorIndex($mainImageIndex);

                $imgWidth = $imagick->getImageWidth();
                $imgHeight = $imagick->getImageHeight();
                $margin = max(8, (int) (min($imgWidth, $imgHeight) * $marginPercent));

                // 2. Hitung ukuran stempel dinamis
                $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));
                $newStampHeight = (int) ($newStampWidth / $stampAspectRatio);
                
                // 3. Clone dan resize stempel master
                $stampOrigResized = $stampOriginal->clone();
                $stampOrigResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                
                $stampCopyResized = $stampCopy->clone();
                $stampCopyResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                
                $stampObsResized = null;
                if ($isObsolete && $stampObsolete) {
                    $stampObsResized = $stampObsolete->clone();
                    $stampObsResized->resizeImage($newStampWidth, $newStampHeight, Imagick::FILTER_LANCZOS, 1);
                }

                if ($imgWidth < $newStampWidth) {
                    Log::warning('Image width is smaller than stamp width, stamp may overlap.', [
                        'file_id' => $file->id, 'imgW' => $imgWidth, 'stampW' => $newStampWidth
                    ]);
                }

                // 4. Gunakan stempel yang sudah di-resize
                list($x, $y) = $this->calculateStampCoordinates($posOriginal, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                $imagick->compositeImage($stampOrigResized, Imagick::COMPOSITE_OVER, (int)$x, (int)$y);

                list($xCopy, $yCopy) = $this->calculateStampCoordinates($posCopy, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                $imagick->compositeImage($stampCopyResized, Imagick::COMPOSITE_OVER, (int)$xCopy, (int)$yCopy);

                if ($stampObsResized) {
                    list($xObs, $yObs) = $this->calculateStampCoordinates($posObsolete, $imgWidth, $imgHeight, $newStampWidth, $newStampHeight, $margin);
                    $imagick->compositeImage($stampObsResized, Imagick::COMPOSITE_OVER, (int)$xObs, (int)$yObs);
                }
                
                // 5. Hancurkan (destroy) clone stempel
                $stampOrigResized->destroy();
                $stampCopyResized->destroy();
                if ($stampObsResized) $stampObsResized->destroy();
            }

            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0775, true);
            }

            $outputExt = $isPdf ? 'pdf' : strtolower($imagick->getImageFormat());
            $tempPath = $tempDir . '/' . Str::uuid()->toString() . '.' . $outputExt;

            if ($isPdf) {
                $imagick->writeImages($tempPath, true);
            } else {
                $imagick->writeImage($tempPath);
            }

            $imagick->clear(); $imagick->destroy();
            // 6. Hancurkan stempel MASTER
            $stampOriginal->clear(); $stampOriginal->destroy();
            $stampCopy->clear(); $stampCopy->destroy();
            if ($stampObsolete) { $stampObsolete->clear(); $stampObsolete->destroy(); }

            return $tempPath;

        } catch (\Exception $e) {
            Log::error('Imagick stamp burn-in failed: ' . $e->getMessage(), ['file_id' => $file->id, 'path' => $originalPath]);
            // Hancurkan master stempel jika terjadi error
            if (isset($stampOriginal)) { $stampOriginal->clear(); $stampOriginal->destroy(); }
            if (isset($stampCopy)) { $stampCopy->clear(); $stampCopy->destroy(); }
            if (isset($stampObsolete) && $stampObsolete) { $stampObsolete->clear(); $stampObsolete->destroy(); }
            return $originalPath;
        }
    }
}
