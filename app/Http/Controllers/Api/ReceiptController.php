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

        // Support both supplier-based mapping (legacy) and role-based mapping
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
                'm.status_id',
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
            ->where('pa.revision_id', $decrypted_id)
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

        $sharedAtDate = $revision->shared_at
            ? Carbon::parse($revision->shared_at)
            : null;

        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $decrypted_id)
            ->select('id', 'filename as name', 'category', 'storage_path', 'file_size', 'ori_position', 'copy_position', 'obslt_position', 'blocks_position')
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
                        'blocks_position' => $item->blocks_position ? json_decode($item->blocks_position, true) : [],
                        'size'          => $item->file_size,
                    ];
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $detail = [
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'model_status_id' => $package->status_id,
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
                'shared_at'    => $sharedAtDate?->toDateString(),
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
            'exportId' => $decrypted_id,
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
                ->select('c.code as customer', 'm.name as model', 'm.status_id', 'p.part_no')
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
            ->select('c.code as customer', 'm.name as model', 'm.status_id', 'p.part_no', 'dp.part_group_id')
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
                } catch (\Exception $e) { continue; }
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
            $canvasHeight = 120;

            // --- 2. SETUP KANVAS ---
            $stamp = new \Imagick();
            $stamp->newImage($canvasWidth, $canvasHeight, new \ImagickPixel('transparent'));
            $stamp->setImageFormat('png');

            $opacity = 0.85;
            if ($colorMode === 'red') {
                $borderColor = new \ImagickPixel("rgba(220, 38, 38, $opacity)");
                $textColor   = new \ImagickPixel("rgba(185, 28, 28, $opacity)");
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
            $draw->line(2, 36, $canvasWidth - 2, 36);
            $draw->line(2, 84, $canvasWidth - 2, 84);

            // LOGIKA KHUSUS ARRAY (GARIS VERTIKAL)
            if (is_array($bottomLine)) {
                $midX = $canvasWidth / 2;
                $draw->line($midX, 84, $midX, 118);
            }

            $stamp->drawImage($draw);

            // --- 3. RENDER TEKS ---
            $draw->setFillColor($textColor);
            $draw->setStrokeWidth(0);

            $x_center_canvas = $canvasWidth / 2;

            // Teks Atas (Center)
            $draw->setTextAlignment(\Imagick::ALIGN_CENTER);
            $draw->setFontSize(16);
            $draw->setFontWeight(600);
            $stamp->annotateImage($draw, $x_center_canvas, 24, 0, $topLine);

            // Teks Tengah (Center)
            $draw->setFontSize(21);
            $draw->setFontWeight(900);
            $draw->setStrokeColor($textColor);
            $draw->setStrokeWidth(0.75);
            $stamp->annotateImage($draw, $x_center_canvas, 68, 0, $centerText);

            // Teks Bawah
            $draw->setStrokeWidth(0);
            $draw->setFontSize(16);
            $draw->setFontWeight(400);

            if (is_array($bottomLine)) {
                // --- MODE 2 KOLOM (Kiri & Kanan) ---

                $x_left = $canvasWidth * 0.25;
                $stamp->annotateImage($draw, $x_left, 105, 0, $bottomLine[0]);

                $x_right = $canvasWidth * 0.75;
                $stamp->annotateImage($draw, $x_right, 105, 0, $bottomLine[1]);

            } else {
                // --- MODE 1 KOLOM (Standar) ---
                $stamp->annotateImage($draw, $x_center_canvas, 105, 0, $bottomLine);
            }

            // Cleanup
            $draw->clear(); $draw->destroy();
            $dummy->clear(); $dummy->destroy();

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
                        $suffixRaw = match ($last) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
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
            $modelStatusId = $package->status_id ?? 0;

            if ($modelStatusId == 4) {
                // === LOGIKA UNCONTROLLED COPY ===
                $centerTextCopy = 'SAI-DRAWING UNCONTROLLED COPY';
                $topLineCopy    = 'SAI / PUD / For Quotation';

                // Format Shared At
                $sharedAtStr = $formatSaiDate($revision->shared_at);
                $bottomLineCopy = "Date Share : " . $sharedAtStr;

            } else {
                // === LOGIKA CONTROLLED COPY (EXISTING) ===
                $centerTextCopy = 'SAI-DRAWING CONTROLLED COPY';

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
            }

            // --- POSISI STAMP ---
            $posOriginal = $this->positionIntToKey($file->ori_position ?? 2, 'bottom-right');
            $posCopy     = $this->positionIntToKey($file->copy_position ?? 1, 'bottom-center');
            $posObsolete = $this->positionIntToKey($file->obslt_position ?? 0, 'bottom-left');

            // --- GENERATE MASTER GAMBAR ---
            // Original & Copy (Default Blue)
            $stampOriginal = $this->_createStampImage('SAI-DRAWING ORIGINAL', $topLine, $bottomLine, 'blue');
            $stampCopy     = $this->_createStampImage('SAI-DRAWING CONTROLLED COPY', $topLineCopy, $bottomLineCopy, 'blue');
            $stampObsolete = null;

            $isObsolete = (bool)($revision->is_obsolete ?? 0);
            if ($isObsolete) {
                $obsDateStr = $formatSaiDate($revision->obsolete_at);
                $topLineObsolete = "Date : {$obsDateStr}";

                $obsName = '-'; $obsDept = '-';
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
                         if ($u) { $obsName = $u->name; $d = DB::table('departments')->where('id', $u->id_dept)->value('code'); $obsDept = $d ?? '-'; }
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

            if ($isPdf) {
                $imagick->setResolution(150, 150);
            }
            $imagick->readImage($originalPath);

            $marginPercent = 0.03;
            $stampWidthPercent = 0.15;
            $minStampWidth = 224;

            // --- DEFINISI CLOSURE ---
            $processPage = function($iteratorIndex) use (
                $imagick, $stampOriginal, $stampCopy, $stampObsolete, $isObsolete, $file,
                $stampAspectRatio, $marginPercent, $stampWidthPercent, $minStampWidth,
                $posOriginal, $posCopy, $posObsolete,
            ) {
                $imagick->setIteratorIndex($iteratorIndex);
                $imgWidth = $imagick->getImageWidth();
                $imgHeight = $imagick->getImageHeight();
                $margin = max(8, (int) (min($imgWidth, $imgHeight) * $marginPercent));

                $newStampWidth = max($minStampWidth, (int) ($imgWidth * $stampWidthPercent));
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

                $stampOrigResized->destroy();
                $stampCopyResized->destroy();
            };

            // Jalankan Proses
            if ($isPdf) {
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

            if ($isPdf) {
                $imagick->writeImages($tempPath, true);
            } else {
                $imagick->writeImage($tempPath);
            }

            // Cleanup
            $imagick->clear(); $imagick->destroy();
            $stampOriginal->clear(); $stampOriginal->destroy();
            $stampCopy->clear(); $stampCopy->destroy();
            if ($stampObsolete) { $stampObsolete->clear(); $stampObsolete->destroy(); }

            return $tempPath;

        } catch (\Exception $e) {
            Log::error('Imagick stamp burn-in failed: ' . $e->getMessage(), ['file_id' => $file->id, 'path' => $originalPath]);
            return $originalPath;
        }
    }
}
