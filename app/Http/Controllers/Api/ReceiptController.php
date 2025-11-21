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
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use App\Models\DocPackageRevisionFile;
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
        if ($request->filled('select2')) {
            $field   = $request->get('select2');
            $q       = trim($request->get('q', ''));
            $page    = max(1, (int)$request->get('page', 1));
            $perPage = 20;

            $customerCode = $request->get('customer_code');
            $docTypeName  = $request->get('doc_type');

            $total = 0;
            $items = collect();

            switch ($field) {
                case 'customer':
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

                // Blok 'case status' telah dihapus

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

        $customerId = $request->integer('customer_id') ?: null;
        if (!$customerId && $request->filled('customer_code')) {
            $customerId = Customers::where('code', $request->get('customer_code'))->value('id');
        }

        $models = $customerId
            ? Models::where('customer_id', $customerId)->orderBy('name')->get(['id', 'name'])
            : Models::orderBy('name')->get(['id', 'name']);

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

    public function receiptList(Request $request): JsonResponse
    {
        $userId = Auth::user()->id ?? 1;

        // Prefer supplier-based mapping (legacy/alternative) then fall back to role-based mapping
        $userSupplier = DB::table('user_supplier')->where('user_id', $userId)->first();
        if ($userSupplier) {
            $userRoleId = (string) $userSupplier->supplier_id;
        } else {
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

        $start       = (int) $request->get('start', 0);
        $length      = (int) $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';

        $orderColumnIndex = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir         = $request->get('order')[0]['dir'] ?? 'desc';
        $orderColumnName  = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.shared_at';

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

        $query = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', 'dsc.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft')
            ->where('dpr.revision_status', 'approved')
            ->whereNotNull('dpr.share_to');

        $query->where(function ($q) use ($userRoleId) {
            $q->where(function ($sub) use ($userRoleId) {
                $sub->whereRaw("ISJSON(dpr.share_to) > 0")
                    ->whereJsonContains('dpr.share_to', $userRoleId);
            });
        });

        $recordsTotal = (clone $query)->count();

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

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                    ->orWhereRaw("
                CONCAT(
                    c.code,' ',
                    m.name,' ',
                    dtg.name,' ',
                    COALESCE(dsc.name,''),' ',
                    COALESCE(p.part_no,''),' ',
                    dpr.revision_no
                ) LIKE ?
            ", ["%{$searchValue}%"]);
            });
        }

        $recordsFiltered = (clone $query)->count();

        $query->select(
            'dpr.id',
            'c.code as customer',
            'm.name as model',
            'dtg.name as doc_type',
            'dsc.name as category',
            'p.part_no',
            'dpr.revision_no as revision',
            'dpr.shared_at as request_date'
        );

        $orderWhitelist = [
            'dpr.shared_at',
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
        ];

        $orderBy = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'dpr.shared_at';
        $orderDirection = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

        $data = $query
            ->orderBy($orderBy, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $data->map(function ($row) {
            $row->hash = encrypt($row->id);
            return $row;
        });

        return response()->json([
            "draw"            => (int) $request->get('draw'),
            "recordsTotal"    => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data"            => $data,
        ]);
    }

    public function showDetail(string $id)
    {
        if (ctype_digit($id)) {
            $revisionId = (int) $id;
        } else {
            try {
                $revisionId = decrypt($id);
            } catch (DecryptException $e) {
                abort(404, 'Invalid approval ID.');
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

        $detail = [
            'metadata' => [
                'customer'    => $package->customer,
                'model'       => $package->model,
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

        return view('receipt.receipt_detail', [
            'receiptId' => $hash,
            'detail'     => $detail,
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
        // Accept either a numeric id or an encrypted id (from blade view)
        $decrypted_id = null;
        if (ctype_digit((string)$id)) {
            $decrypted_id = (int)$id;
        } else {
            try {
                $decrypted_id = decrypt(str_replace('-', '=', $id));
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Invalid revision ID.'], 404);
            }
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
            return response()->json(['success' => false, 'message' => 'Receipt revision not found.'], 404);
        }

        // Check if user has access to this receipt
        $userId = Auth::user()->id ?? 1;
        $userRole = DB::table('user_roles')->where('user_id', $userId)->first();

        if (!$userRole) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $userRoleId = (string) $userRole->role_id;

        // Check if revision is shared to this user
        if ($revision->share_to) {
            $sharedTo = is_string($revision->share_to) ? json_decode($revision->share_to, true) : $revision->share_to;
            if (!in_array($userRoleId, (array)$sharedTo)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
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

        $obsoleteDate = $revision->obsolete_at
            ? Carbon::parse($revision->obsolete_at)
            : null;

        $lastApproval = DB::table('package_approvals as pa')
            ->leftJoin('users as u', 'u.id', '=', 'pa.decided_by')
            ->leftJoin('departments as d', 'd.id', '=', 'u.id_dept')
            ->where('pa.revision_id', $revisionId)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first([
                'u.name as approver_name',
                'd.code as dept_name'
            ]);

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDate
                ? $obsoleteDate->toSaiStampFormat()
                : null,
            'name' => $revision->obsolete_name
                ?? optional($lastApproval)->approver_name
                ?? '-',
            'dept' => $revision->obsolete_dept
                ?? optional($lastApproval)->dept_name
                ?? '-',
        ];

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
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
            'files' => $files,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'is_obsolete'  => $isObsolete,
                'obsolete_info' => $obsoleteStampInfo,
            ],
        ];

        $stampFormat = StampFormat::where('is_active', true)->first();

        $userDeptCode = null;
        if (Auth::check() && Auth::user()->id_dept) {
            $userDeptCode = DB::table('departments')->where('id', Auth::user()->id_dept)->value('code');
        }

        $userName = null;
        if (Auth::check()) {
            $userName = Auth::user()->name;
        }

        return response()->json([
            'success' => true,
            'exportId' => $revisionId,
            'detail' => $detail,
            'revisionList' => [],
            'stampFormat' => $stampFormat,
            'userDeptCode' => $userDeptCode,
            'userName' => $userName,
        ]);
    }

    /**
     * Download a single file from receipt with optional stamp
     */
    public function downloadFile($file_id)
    {
        set_time_limit(0);

        $file = DocPackageRevisionFile::find($file_id);

        if (!$file) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $file->revision_id)
            ->where('dpr.revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            return response()->json(['error' => 'Revision not found'], 404);
        }

        $path = Storage::disk('datacenter')->path($file->storage_path);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found on storage'], 404);
        }

        $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
        $stampableTypes = ['jpg', 'jpeg', 'png', 'tif', 'tiff', 'pdf'];

        if (in_array($ext, $stampableTypes) && class_exists('\Imagick')) {
            $stampFormat = StampFormat::where('is_active', true)->first();

            if ($stampFormat && Auth::check()) {
                $tempPath = $this->_burnStamps($path, $file, $revision,
                    DB::table('doc_packages')->where('id', $revision->package_id)->first(),
                    $stampFormat);

                if ($tempPath && file_exists($tempPath)) {
                    return response()->download($tempPath, $file->filename)->deleteFileAfterSend(true);
                }
            }
        }

        return response()->download($path, $file->filename);
    }

    /**
     * Prepare zip file for download containing all files from receipt
     */
    public function preparePackageZip($revision_id)
    {
        set_time_limit(0);
        try {
            $decrypted_id = (int)$revision_id;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid revision ID.'], 404);
        }

        $revision = DB::table('doc_package_revisions')
            ->where('id', $decrypted_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            return response()->json(['success' => false, 'message' => 'Receipt revision not found.'], 404);
        }

        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->where('dp.id', $revision->package_id)
            ->select('c.code as customer', 'm.name as model', 'p.part_no')
            ->first();

        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found.'], 404);
        }

        $files = DocPackageRevisionFile::where('revision_id', $decrypted_id)->get();

        if ($files->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No files found.'], 404);
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
        $zipDirectory = str_replace('/', DIRECTORY_SEPARATOR, $zipDirectory);

        if (!file_exists($zipDirectory)) {
            mkdir($zipDirectory, 0755, true);
        }

        if (!is_writable($zipDirectory)) {
            Log::error('Zip directory not writable', ['path' => $zipDirectory]);
            return response()->json(['success' => false, 'message' => 'Storage directory not writable.'], 500);
        }

        $zipFilePath = $zipDirectory . DIRECTORY_SEPARATOR . $zipFileName;

        $zip = new ZipArchive();

        Log::info('Attempting to open ZipArchive at normalized path', ['path' => $zipFilePath]);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Log::error('ZipArchive open failed', ['path' => $zipFilePath]);
            return response()->json(['success' => false, 'message' => 'Failed to create zip file.'], 500);
        }

        $filesAddedCount = 0;
        $tempFilesToDelete = [];

        foreach ($files as $file) {
            $filePath = Storage::disk('datacenter')->path($file->storage_path);

            if (!file_exists($filePath)) {
                Log::warning('File not found in storage', ['path' => $filePath, 'file_id' => $file->id]);
                continue;
            }

            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));

            if ($canStamp && in_array($ext, $stampableTypes)) {
                $stampedPath = $this->_burnStamps($filePath, $file, $revision, $package, $stampFormat);

                if ($stampedPath && file_exists($stampedPath)) {
                    $zip->addFile($stampedPath, $file->filename);
                    $filesAddedCount++;
                    $tempFilesToDelete[] = $stampedPath;
                } else {
                    $zip->addFile($filePath, $file->filename);
                    $filesAddedCount++;
                }
            } else {
                $zip->addFile($filePath, $file->filename);
                $filesAddedCount++;
            }
        }

        $closeSuccess = false;
        try {
            $closeSuccess = $zip->close();
        } catch (\Exception $e) {
            Log::error('ZipArchive close failed', ['error' => $e->getMessage()]);
        }

        foreach ($tempFilesToDelete as $tempFile) {
            @unlink($tempFile);
        }

        if (!$closeSuccess) {
            @unlink($zipFilePath);
            return response()->json(['success' => false, 'message' => 'Failed to close zip file.'], 500);
        }

        if ($filesAddedCount === 0) {
            @unlink($zipFilePath);
            return response()->json([
                'success' => false,
                'message' => 'No files were added to the zip.',
            ], 404);
        }

        $downloadUrl = URL::temporarySignedRoute(
            'receipts.download-zip',
            now()->addHours(2),
            ['file_name' => $zipFileName, 'rev_id' => encrypt($decrypted_id)]
        );

        return response()->json([
            'success'      => true,
            'download_url' => $downloadUrl,
            'file_name'    => $zipFileName
        ]);
    }

    /**
     * Download prepared zip file
     */
    public function getPreparedZip(Request $request, $file_name)
    {
        set_time_limit(0);

        if (!$request->hasValidSignature()) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $safe_filename = basename($file_name);
        $zipFilePath = storage_path('app/public/' . $safe_filename);

        if (!file_exists($zipFilePath)) {
            return response()->json(['error' => 'Zip file not found'], 404);
        }

        $encrypted_rev_id = $request->get('rev_id');
        $decrypted_id = null;
        if ($encrypted_rev_id) {
            try {
                $decrypted_id = decrypt($encrypted_rev_id);
            } catch (\Exception $e) {
                Log::warning('Failed to decrypt revision ID', ['error' => $e->getMessage()]);
            }
        }

        if ($decrypted_id && Auth::check()) {
            DB::table('activity_logs')->insert([
                'user_id'      => Auth::user()->id ?? null,
                'activity_code' => 'DOWNLOAD_RECEIPT_ZIP',
                'scope_type'   => 'receipt',
                'scope_id'     => $decrypted_id,
                'revision_id'  => $decrypted_id,
                'meta'         => json_encode(['file_name' => $safe_filename]),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    /**
     * Helper: Convert position int to key
     */
    private function positionIntToKey(?int $pos, string $default = 'bottom-right'): string
    {
        switch ($pos) {
            case 0: return 'top-left';
            case 1: return 'top-right';
            case 2: return 'bottom-left';
            case 3: return 'bottom-right';
            default: return $default;
        }
    }

    /**
     * Calculate stamp coordinates based on position
     */
    private function calculateStampCoordinates(string $posKey, int $imgW, int $imgH, int $stampW, int $stampH, int $margin): array
    {
        $x = 0;
        $y = 0;

        switch ($posKey) {
            case 'top-left':
                $x = $margin;
                $y = $margin;
                break;
            case 'top-right':
                $x = $imgW - $stampW - $margin;
                $y = $margin;
                break;
            case 'bottom-left':
                $x = $margin;
                $y = $imgH - $stampH - $margin;
                break;
            case 'bottom-right':
            default:
                $x = $imgW - $stampW - $margin;
                $y = $imgH - $stampH - $margin;
                break;
        }

        $x = max(0, min($x, $imgW - $stampW));
        $y = max(0, min($y, $imgH - $stampH));

        return [$x, $y];
    }

    /**
     * Create stamp image
     */
    private function _createStampImage(string $centerText, string $topLine, string|array $bottomLine, string $colorMode = 'blue'): ?\Imagick
    {
        try {
            $stampW = 250;
            $stampH = 180;

            $stamp = new Imagick();
            $stamp->newImage($stampW, $stampH, new ImagickPixel('white'));
            $stamp->setImageFormat('png');

            $draw = new ImagickDraw();
            $draw->setFillColor(new ImagickPixel('white'));
            $draw->rectangle(0, 0, $stampW, $stampH);

            $borderColor = match ($colorMode) {
                'red'   => '#FF0000',
                'green' => '#00AA00',
                default => '#0066CC',
            };

            $draw->setStrokeColor(new ImagickPixel($borderColor));
            $draw->setStrokeWidth(3);
            $draw->rectangle(5, 5, $stampW - 5, $stampH - 5);

            $draw->setFont('/Windows/Fonts/arial.ttf');
            $draw->setFillColor(new ImagickPixel('#333333'));

            $draw->setFontSize(9);
            $draw->annotation(10, 25, $topLine);

            $draw->setFontSize(12);
            $draw->setFillColor(new ImagickPixel($borderColor));
            $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
            $draw->annotation($stampW / 2, 95, $centerText);

            $draw->setFontSize(9);
            $draw->setFillColor(new ImagickPixel('#333333'));
            $draw->setTextAlignment(\Imagick::ALIGN_LEFT);

            $bottomY = $stampH - 20;
            if (is_array($bottomLine)) {
                foreach ($bottomLine as $idx => $line) {
                    $draw->annotation(10, $bottomY - ($idx * 12), $line);
                }
            } else {
                $draw->annotation(10, $bottomY, $bottomLine);
            }

            $stamp->drawImage($draw);

            return $stamp;
        } catch (\Exception $e) {
            Log::error('Error creating stamp image', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Burn stamps onto document (image/PDF)
     */
    private function _burnStamps(string $originalPath, DocPackageRevisionFile $file, object $revision, object $package, ?StampFormat $stampFormat): string
    {
        if (!class_exists('Imagick') || !file_exists($originalPath)) {
            return $originalPath;
        }

        try {
            $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
            $tempPath = tempnam(sys_get_temp_dir(), 'stamp_');

            if ($ext === 'pdf') {
                $pdf = new Imagick();
                $pdf->readImage($originalPath . '[0]');
                $pdf->setImageFormat('png');
            } else {
                $pdf = new Imagick($originalPath);
            }

            $imgW = (int)$pdf->getImageWidth();
            $imgH = (int)$pdf->getImageHeight();

            $topLine = 'Date: ' . (new \DateTime())->format('Y-m-d');
            $bottomLine = 'Dept: ' . (Auth::user()->department?->code ?? '-');

            $centerText = 'SAI-DRAWING ORIGINAL';
            $stamp = $this->_createStampImage($centerText, $topLine, $bottomLine, 'blue');

            if ($stamp) {
                $stampW = (int)$stamp->getImageWidth();
                $stampH = (int)$stamp->getImageHeight();

                $posKey = $this->positionIntToKey($file->ori_position, 'bottom-right');
                [$x, $y] = $this->calculateStampCoordinates($posKey, $imgW, $imgH, $stampW, $stampH, 10);

                $pdf->compositeImage($stamp, \Imagick::COMPOSITE_OVER, $x, $y);
                $stamp->destroy();
            }

            $pdf->setImageFormat($ext === 'pdf' ? 'pdf' : 'png');
            $pdf->writeImage($tempPath);
            $pdf->destroy();

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Error burning stamps', ['error' => $e->getMessage(), 'file_id' => $file->id]);
            return $originalPath;
        }
    }
}
