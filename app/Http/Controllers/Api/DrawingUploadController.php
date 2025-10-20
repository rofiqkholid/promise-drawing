<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Services\PathBuilder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DrawingUploadController extends Controller
{
    protected string $disk = 'datacenter';

    /** Util: ambil label master untuk segmen folder */
    protected function getFolderLabels(array $ids): array
    {
        $customer = DB::table('customers')
            ->where('id', $ids['customer_id'])
            ->first(['code']);
        $model = DB::table('models')
            ->where('id', $ids['model_id'])
            ->first(['name']);
        $product = DB::table('products')
            ->where('id', $ids['product_id'])
            ->first(['part_no']);
        $dg = DB::table('doctype_groups')
            ->where('id', $ids['doctype_group_id'])
            ->first(['name']);

        if (!$customer || !$model || !$product || !$dg) {
            Log::error('Master data tidak valid', [
                'customer_id' => $ids['customer_id'],
                'model_id' => $ids['model_id'],
                'product_id' => $ids['product_id'],
                'doctype_group_id' => $ids['doctype_group_id'],
            ]);
            abort(422, 'Master data tidak valid');
        }

        return [
            'customer_code' => $customer->code,
            'model_name' => $model->name,
            'doctype_group_name' => $dg->name,
            'part_no' => $product->part_no,
        ];
    }

    /** CHECK: deteksi existing + suggest */
    public function check(Request $r): JsonResponse
    {
        $r->validate([
            'customer' => 'required|integer|exists:customers,id',
            'model' => 'required|integer|exists:models,id',
            'partNo' => 'required|integer|exists:products,id',
            'docType' => 'required|integer|exists:doctype_groups,id',
            'category' => 'nullable|integer|exists:doctype_subcategories,id',
            'partGroup' => 'required|integer|exists:part_groups,id',
        ]);

        $ids = [
            'customer_id' => $r->customer,
            'model_id' => $r->model,
            'product_id' => $r->partNo,
            'doctype_group_id' => $r->docType,
            'doctype_subcategory_id' => $r->category,
        ];

        $labels = $this->getFolderLabels($ids);

        // Ambil code_part_group langsung
        $partGroup = DB::table('part_groups')->where('id', (int) $r->partGroup)->where('customer_id', (int) $r->customer)->where('model_id', (int) $r->model)->value('code_part_group');

        if (!$partGroup) {
            Log::error('Part Group tidak ditemukan', [
                'part_group_id' => $r->partGroup,
                'customer_id' => $r->customer,
                'model_id' => $r->model,
            ]);
            abort(422, 'Part Group tidak ditemukan untuk customer dan model yang dipilih');
        }

        $meta = $labels + ['part_group' => $partGroup];

        $revs = PathBuilder::listRevisions($this->disk, $meta);
        $exists = !empty($revs);
        $latest = $exists ? max($revs) : null;
        $suggested = $exists ? $latest + 1 : 0;

        $lastFiles = [];
        $orphanFiles = [];
        if ($latest !== null) {
            foreach (['2d', '3d', 'ecn'] as $f) {
                $dir = PathBuilder::root($meta) . "/rev{$latest}/{$f}";
                $lastFiles[$f] = Storage::disk($this->disk)->exists($dir) ? array_map('basename', Storage::disk($this->disk)->files($dir)) : [];
                // detect orphan files (present in storage but not in DB)
                $files = Storage::disk($this->disk)->exists($dir) ? Storage::disk($this->disk)->files($dir) : [];
                foreach ($files as $fp) {
                    $existsInDb = DB::table('doc_package_revision_files')->where('storage_path', $fp)->exists();
                    if (!$existsInDb) {
                        $orphanFiles[] = $fp;
                    }
                }
            }
        }

        return response()->json([
            'exists' => $exists,
            'revisions' => $revs,
            'latest_rev' => $latest,
            'suggested_rev' => $suggested,
            'last_rev_files' => $lastFiles,
            'orphan_files' => $orphanFiles,
        ]);
    }

    public function store(Request $r): JsonResponse
    {
        try {
            $r->validate([
                'customer' => 'required|integer|exists:customers,id',
                'model' => 'required|integer|exists:models,id',
                'partNo' => 'required|integer|exists:products,id',
                'docType' => 'required|integer|exists:doctype_groups,id',
                'category' => 'nullable|integer|exists:doctype_subcategories,id',
                'partGroup' => 'required|integer|exists:part_groups,id',
                'revision' => 'nullable|integer|min:0',
                'files_2d.*' => 'nullable|file|max:102400',
                'files_3d.*' => 'nullable|file|max:102400',
                'files_ecn.*' => 'nullable|file|max:102400',
                'mode' => 'required|in:auto,existing,new-rev',
                'target_rev' => 'nullable|integer|min:0',
                'conflict' => 'nullable|in:replace,append',
                'enabled_categories' => 'nullable|array',
                'enabled_categories.*' => 'in:2d,3d,ecn',
            ]);

            Log::info('Processing upload', ['request' => $r->except(['files_2d', 'files_3d', 'files_ecn'])]);

            $ids = [
                'customer_id' => $r->customer, 'model_id' => $r->model, 'product_id' => $r->partNo,
                'doctype_group_id' => $r->docType, 'doctype_subcategory_id' => $r->category, 'part_group_id' => $r->partGroup,
            ];
            $labels = $this->getFolderLabels($ids);
            $partGroup = DB::table('part_groups')->where('id', (int)$r->partGroup)->where('customer_id', (int)$r->customer)->where('model_id', (int)$r->model)->value('code_part_group');
            if (!$partGroup) {
                abort(422, 'Part Group tidak ditemukan untuk customer dan model yang dipilih');
            }
            $metaBase = $labels + ['part_group' => $partGroup];
            $mode = $r->mode;

            $projectStatusId = $r->input('project_status');
            if (empty($projectStatusId)) {
                $projectStatusId = DB::table('project_status')->value('id') ?: DB::table('project_status')->insertGetId(['name' => 'Default', 'description' => 'Default project status', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }
            $packageId = $this->ensurePackage($ids, $labels, $projectStatusId);

            $savedFilePaths = [];

            DB::beginTransaction();
            try {
                $package = DB::table('doc_packages')->where('id', $packageId)->lockForUpdate()->first();
                if (!$package) {
                    throw new \Exception("Package dengan ID {$packageId} tidak ditemukan atau tidak bisa dikunci.");
                }

                $rev = 0;
                if ($mode === 'auto' || $mode === 'new-rev') {
                    $revs = PathBuilder::listRevisions($this->disk, $metaBase);
                    if ($mode === 'auto') {
                        $rev = empty($revs) ? 0 : max($revs) + 1;
                    } else { // mode 'new-rev'
                        $suggested = empty($revs) ? 0 : max($revs) + 1;
                        $rev = $r->filled('revision') ? (int)$r->revision : $suggested;
                    }
                } elseif ($mode === 'existing') {
                    $r->validate(['target_rev' => 'required|integer|min:0']);
                    $revs = PathBuilder::listRevisions($this->disk, $metaBase);
                    if (!in_array((int)$r->target_rev, $revs)) {
                        abort(422, 'Revisi target tidak ada');
                    }
                    $rev = (int)$r->target_rev;
                }
                if ($rev < 0) $rev = 0;

                $enabledCats = $r->input('enabled_categories') ?? ['2d','3d','ecn'];
                foreach ($enabledCats as $folder) {
                    Storage::disk($this->disk)->makeDirectory(PathBuilder::root($metaBase) . "/rev{$rev}/{$folder}");
                }
                $conflict = $r->input('conflict', 'append');
                $saved = ['2d' => [], '3d' => [], 'ecn' => []];

                $saveOne = function ($file, string $docFolder) use ($metaBase, $rev, $conflict, &$saved, &$savedFilePaths) {
                    $path = null;
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $filename = preg_replace('/[<>:"?*|\x00-\x1F]/', '', $filename);
                    $meta = $metaBase + ['rev' => $rev, 'doc_folder' => $docFolder, 'filename' => $filename, 'ext' => strtolower($file->getClientOriginalExtension())];
                    $path = PathBuilder::build($meta);

                    if (Storage::disk($this->disk)->exists($path)) {
                        if ($conflict === 'replace') {
                            Storage::disk($this->disk)->delete($path);
                        } else {
                            $pi = pathinfo($path);
                            $suffix = '-' . date('YmdHis') . '-' . Str::random(5);
                            $path = $pi['dirname'] . '/' . $pi['filename'] . $suffix . '.' . $pi['extension'];
                        }
                    }
                    Storage::disk($this->disk)->putFileAs(dirname($path), $file, basename($path));

                    $savedFilePaths[] = $path;
                    $saved[$docFolder][] = $path;
                };

                foreach (['2d' => 'files_2d', '3d' => 'files_3d', 'ecn' => 'files_ecn'] as $folder => $key) {
                    if ($r->hasFile($key)) {
                        foreach (Arr::wrap($r->file($key)) as $f) {
                            if ($f) $saveOne($f, $folder);
                        }
                    }
                }

                $revision = DB::table('doc_package_revisions')->where('package_id', $packageId)->where('revision_no', $rev)->first();
                $revisionId = $revision->id ?? null;
                $revisionStatus = null;

                if ($revision && $r->filled('as_draft') && $r->as_draft == '1') {
                    DB::table('doc_package_revisions')->where('id', $revision->id)->update(['revision_status' => 'draft', 'updated_at' => Carbon::now()]);
                    DB::table('package_approvals')->where('revision_id', $revision->id)->where('is_active', 1)->update(['is_active' => 0, 'updated_at' => Carbon::now()]);
                    $revision->revision_status = 'draft';
                }

                if ($revision) {
                    if ($mode === 'existing' && $conflict === 'replace' && $revision->revision_status !== 'pending') {
                        abort(422, 'Tidak bisa replace revisi yang bukan pending');
                    }
                    if (!empty($r->input('note'))) {
                        DB::table('doc_package_revisions')->where('id', $revisionId)->update(['note' => $r->input('note'), 'updated_at' => Carbon::now()]);
                    }
                } else {
                    $labelId = $this->pickRevisionLabel((int)$ids['customer_id'], $rev);
                    $revisionStatus = $r->filled('as_draft') && $r->as_draft == '1' ? 'draft' : 'pending';
                    $revisionId = DB::table('doc_package_revisions')->insertGetId(['package_id' => $packageId, 'revision_no' => $rev, 'revision_label_id' => $labelId, 'revision_status' => $revisionStatus, 'note' => $r->input('note'), 'is_obsolete' => 0, 'created_by' => $this->getAuthUserInt(), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'is_active' => 1]);
                }

                foreach ($saved as $cat => $paths) {
                    foreach ($paths as $p) {
                        $diskPath = Storage::disk($this->disk)->path($p);
                        DB::table('doc_package_revision_files')->insert(['revision_id' => $revisionId, 'category' => strtoupper($cat), 'file_extension_id' => $this->ensureFileExtension(pathinfo($p, PATHINFO_EXTENSION)), 'filename' => basename($p), 'storage_path' => $p, 'file_size' => filesize($diskPath), 'checksum_sha256' => hash_file('sha256', $diskPath), 'uploaded_by' => $this->getAuthUserInt(), 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'is_active' => 1]);
                    }
                }

                $currentRevisionStatus = $revision->revision_status ?? $revisionStatus;
                $approvalId = null;
                if ($currentRevisionStatus !== 'draft') {
                    $existingApproval = DB::table('package_approvals')->where('revision_id', $revisionId)->where('package_id', $packageId)->where('is_active', 1)->first(['id']);
                    if ($existingApproval) {
                        $approvalId = $existingApproval->id;
                    } else {
                        $approvalId = DB::table('package_approvals')->insertGetId(['package_id' => $packageId, 'revision_id' => $revisionId, 'requested_by' => $this->getAuthUserInt(), 'requested_at' => Carbon::now(), 'is_active' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
                    }
                }

                DB::commit();

                try {
                    $subcatName = !empty($ids['doctype_subcategory_id']) ? DB::table('doctype_subcategories')->where('id', $ids['doctype_subcategory_id'])->value('name') : null;
                    $packageNo = DB::table('doc_packages')->where('id', $packageId)->value('package_no');
                    $metaLog = json_encode(['part_no' => $labels['part_no'] ?? null, 'doctype_group' => $labels['doctype_group_name'] ?? null, 'customer_code' => $labels['customer_code'] ?? null, 'model_name' => $labels['model_name'] ?? null, 'doctype_subcategory' => $subcatName, 'note' => $r->input('note') ?? null, 'package_no' => $packageNo, 'revision_no' => $rev]);
                    $existingLog = DB::table('activity_logs')->where('activity_code', 'UPLOAD')->where('scope_type', 'package')->where('scope_id', $packageId)->where('revision_id', $revisionId)->first();
                    if ($existingLog) {
                        DB::table('activity_logs')->where('id', $existingLog->id)->update(['user_id' => $this->getAuthUserInt(), 'meta' => $metaLog, 'updated_at' => Carbon::now()]);
                    } else {
                        DB::table('activity_logs')->insert(['user_id' => $this->getAuthUserInt(), 'activity_code' => 'UPLOAD', 'scope_type' => 'package', 'scope_id' => $packageId, 'revision_id' => $revisionId, 'meta' => $metaLog, 'created_at' => Carbon::now()]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to write activity log for upload', ['error' => $e->getMessage()]);
                }

            } catch (\Exception $e) {
                DB::rollBack();

                Log::error('Transaksi gagal, membersihkan file yang terupload.', ['files_to_delete' => $savedFilePaths]);
                foreach ($savedFilePaths as $path) {
                    try {
                        if (Storage::disk($this->disk)->exists($path)) {
                            Storage::disk($this->disk)->delete($path);
                        }
                    } catch (\Exception $storageEx) {
                        Log::critical('GAGAL MENGHAPUS ORPHAN FILE!', ['path' => $path, 'error' => $storageEx->getMessage()]);
                    }
                }

                throw $e;
            }

            return response()->json([
                'success' => true, 'mode' => $mode, 'rev' => $rev,
                'conflict' => $conflict, 'saved_paths' => $saved,
                'package_id' => $packageId ?? null, 'revision_id' => $revisionId ?? null,
                'approval_id' => $approvalId ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Upload gagal total', ['error' => $e->getMessage(), 'request' => $r->except(['files_2d', 'files_3d', 'files_ecn'])]);
            $statusCode = $e instanceof AuthenticationException ? 401 : 500;
            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    // Other methods tetap sama
    public function getCustomerData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('customers')->select('id', 'code');

        if ($searchTerm) {
            $query->where('code', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('code', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->code,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getModelData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $customer_id = $request->customer_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('models')->select('id', 'name')->where('customer_id', $customer_id);

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->name,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getProductData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $model_id = $request->model_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('products')->select('id', 'part_no')->where('model_id', $model_id);

        if ($searchTerm) {
            $query->where('part_no', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('part_no', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->part_no,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getDocumentGroupData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('doctype_groups')->select('id', 'name');

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->name,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getSubCategoryData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $document_group_id = $request->document_group_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('doctype_subcategories')->select('id', 'name')->where('doctype_group_id', $document_group_id);

        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->name,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getPartGroupData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $customer_id = $request->customer_id;
        $model_id = $request->model_id;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('part_groups')->select('id', 'code_part_group')->where('customer_id', $customer_id)->where('model_id', $model_id);

        if ($searchTerm) {
            $query->where('code_part_group', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        if ($totalCount === 0) {
            Log::warning('Tidak ada Part Group untuk customer dan model', [
                'customer_id' => $customer_id,
                'model_id' => $model_id,
            ]);
        }

        $groups = $query->orderBy('code_part_group', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => $group->code_part_group,
            ];
        });

        return response()->json([
            'results' => $formattedGroups,
            'total_count' => $totalCount,
        ]);
    }

    public function getProjectStatusData(Request $request): JsonResponse
    {
        $searchTerm = $request->q;
        $page = $request->page ?: 1;
        $resultsPerPage = 10;
        $offset = ($page - 1) * $resultsPerPage;

        $query = DB::table('project_status')->select('id', 'name');
        if ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('name', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($g) {
            return ['id' => $g->id, 'text' => $g->name];
        });

        return response()->json(['results' => $formattedGroups, 'total_count' => $totalCount]);
    }

    /**
     * Ensure doc_package exists, otherwise create one.
     * Returns package id.
     */
    private function ensurePackage(array $ids, array $labels, $projectStatusId = null): int
    {
        // try to find existing package by unique keys
        $q = DB::table('doc_packages')->where('customer_id', $ids['customer_id'])->where('model_id', $ids['model_id'])->where('product_id', $ids['product_id'])->where('doctype_group_id', $ids['doctype_group_id'])->where('part_group_id', $ids['part_group_id']);

        if (!empty($ids['doctype_subcategory_id'])) {
            $q->where('doctype_subcategory_id', $ids['doctype_subcategory_id']);
        } else {
            $q->whereNull('doctype_subcategory_id');
        }

        $existing = $q->first(['id']);
        if ($existing) {
            return $existing->id;
        }

        // create package_no: PKG-{CUSTCODE}-{MODEL}-{PARTNO}-{YmdHis}-{RAND4}
        $cust = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['customer_code'] ?? 'CUST'));
        $model = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['model_name'] ?? 'MDL'));
        $part = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['part_no'] ?? 'PRT'));
        $pkgNo = sprintf('PKG-%s-%s-%s-%s-%s', $cust, $model, $part, Carbon::now()->format('YmdHis'), substr(Str::upper(Str::random(6)), 0, 4));

        $now = Carbon::now();
        $insert = [
            'package_no' => $pkgNo,
            'customer_id' => $ids['customer_id'],
            'model_id' => $ids['model_id'],
            'product_id' => $ids['product_id'],
            'doctype_group_id' => $ids['doctype_group_id'],
            'doctype_subcategory_id' => $ids['doctype_subcategory_id'] ?? null,
            'part_group_id' => $ids['part_group_id'],
            'project_status_id' => $projectStatusId ?: null,
            'current_revision_no' => 0,
            'current_revision_id' => null,
            'created_by' => $this->getAuthUserInt(),
            'created_at' => $now,
            'updated_at' => $now,
            'is_active' => 1,
        ];

        return DB::table('doc_packages')->insertGetId($insert);
    }

    /** Return integer user id or null */
    private function getAuthUserInt(): ?int
    {
        $uid = Auth::id();
        if (is_numeric($uid)) {
            return (int) $uid;
        }
        // Fallback to system user id 1 if no authenticated numeric user is available
        return 1;
    }

    /** Ensure file extension exists; return id */
    private function ensureFileExtension(string $ext): int
    {
        $code = strtolower($ext);
        $row = DB::table('file_extensions')
            ->where('code', $code)
            ->orWhere('name', $code)
            ->first(['id']);
        if ($row) {
            return $row->id;
        }

        $now = Carbon::now();
        return DB::table('file_extensions')->insertGetId([
            'name' => $code,
            'code' => $code,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** Pick revision label id by customer and revision_no (match sort_order). Return null if not found. */
    private function pickRevisionLabel(int $customerId, int $revisionNo): ?int
    {
        $row = DB::table('customer_revision_labels')
            ->where('customer_id', $customerId)
            ->where('sort_order', $revisionNo)
            ->where('is_active', 1)
            ->first(['id']);
        return $row ? $row->id : null;
    }

    /**
     * Request approval for an existing package revision (set to pending).
     * Expects package_id and revision_no in request.
     */
    public function requestApproval(Request $r): JsonResponse
    {
        $r->validate([
            'package_id' => 'required|integer|exists:doc_packages,id',
            'revision_no' => 'required|integer|min:0',
        ]);

        $packageId = (int) $r->package_id;
        $revNo = (int) $r->revision_no;

        // find revision
        $revision = DB::table('doc_package_revisions')->where('package_id', $packageId)->where('revision_no', $revNo)->first();

        if (!$revision) {
            return response()->json(['message' => 'Revision not found for given package and revision number.'], 422);
        }

        // if already pending or approved, return info
        if ($revision->revision_status === 'pending') {
            return response()->json(['success' => true, 'message' => 'Revision already pending.']);
        }

        DB::beginTransaction();
        try {
            DB::table('doc_package_revisions')
                ->where('id', $revision->id)
                ->update(['revision_status' => 'pending', 'updated_at' => Carbon::now()]);

            // create approval record if not exists
            $existingApproval = DB::table('package_approvals')
                ->where('package_id', $packageId)
                ->where('revision_id', $revision->id)
                ->where('is_active', 1)
                ->first(['id']);

            if (!$existingApproval) {
                DB::table('package_approvals')->insertGetId([
                    'package_id' => $packageId,
                    'revision_id' => $revision->id,
                    'requested_by' => $this->getAuthUserInt(),
                    'requested_at' => Carbon::now(),
                    'is_active' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            // write activity log for approval request
            try {
                $metaLog = json_encode([
                    'package_id' => $packageId,
                    'revision_no' => $revNo,
                ]);
                DB::table('activity_logs')->insert([
                    'user_id' => $this->getAuthUserInt(),
                    'activity_code' => 'REQUEST_APPROVAL',
                    'scope_type' => 'revision',
                    'scope_id' => $packageId,
                    'revision_id' => $revision->id,
                    'meta' => $metaLog,
                    'created_at' => Carbon::now(),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to write activity log for requestApproval', ['error' => $e->getMessage()]);
            }

            return response()->json(['success' => true, 'message' => 'Revision set to pending and approval requested.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to request approval', ['error' => $e->getMessage(), 'package_id' => $packageId, 'revision_no' => $revNo]);
            return response()->json(['message' => 'Failed to request approval.'], 500);
        }
    }

    /**
     * Return activity logs. If metadata identifying a package is provided, filter to that package/revision.
     * Accepts POST params: customer, model, partNo, docType, category, partGroup, revision_no (optional)
     */
    public function activityLogs(Request $r): JsonResponse
    {
        $customer = $r->input('customer');
        $model = $r->input('model');
        $partNo = $r->input('partNo');
        $docType = $r->input('docType');
        $category = $r->input('category');
        $partGroup = $r->input('partGroup');
        $revisionNo = $r->input('revision_no');

        // If metadata provided, try to find matching package
        if ($customer && $model && $partNo && $docType && $partGroup) {
            $q = DB::table('doc_packages')->where('customer_id', (int) $customer)->where('model_id', (int) $model)->where('product_id', (int) $partNo)->where('doctype_group_id', (int) $docType)->where('part_group_id', (int) $partGroup);
            if ($category) {
                $q->where('doctype_subcategory_id', $category);
            } else {
                $q->whereNull('doctype_subcategory_id');
            }
            $pkg = $q->first(['id', 'package_no']);
            if ($pkg) {
                $logsQ = DB::table('activity_logs')->where(function ($q2) use ($pkg, $revisionNo) {
                    $q2->where('scope_type', 'package')->where('scope_id', $pkg->id);
                    if ($revisionNo !== null) {
                        $q2->orWhere(function ($q3) use ($pkg, $revisionNo) {
                            $q3->where('revision_id', DB::table('doc_package_revisions')->where('package_id', $pkg->id)->where('revision_no', (int) $revisionNo)->value('id'));
                        });
                    }
                });
                $logs = $logsQ->orderBy('created_at', 'desc')->limit(50)->get();
                // decode meta and include user name if available
                $logs = $logs->map(function ($row) {
                    $meta = null;
                    try {
                        $meta = $row->meta ? (is_string($row->meta) ? json_decode($row->meta, true) : $row->meta) : null;
                    } catch (\Exception $_) {
                        $meta = null;
                    }
                    $userName = null;
                    if (!empty($row->user_id)) {
                        $userName = DB::table('users')->where('id', $row->user_id)->value('name');
                    }
                    return [
                        'id' => $row->id,
                        'user_id' => $row->user_id,
                        'user_name' => $userName,
                        'activity_code' => $row->activity_code,
                        'scope_type' => $row->scope_type,
                        'scope_id' => $row->scope_id,
                        'revision_id' => $row->revision_id,
                        'meta' => $meta,
                        'created_at' => $row->created_at,
                    ];
                });
                return response()->json(['logs' => $logs]);
            }
        }

        // Fallback: return latest global logs
        $global = DB::table('activity_logs')->orderBy('created_at', 'desc')->limit(50)->get();
        $global = $global->map(function ($row) {
            $meta = null;
            try {
                $meta = $row->meta ? (is_string($row->meta) ? json_decode($row->meta, true) : $row->meta) : null;
            } catch (\Exception $_) {
                $meta = null;
            }
            $userName = null;
            if (!empty($row->user_id)) {
                $userName = DB::table('users')->where('id', $row->user_id)->value('name');
            }
            return [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'user_name' => $userName,
                'activity_code' => $row->activity_code,
                'scope_type' => $row->scope_type,
                'scope_id' => $row->scope_id,
                'revision_id' => $row->revision_id,
                'meta' => $meta,
                'created_at' => $row->created_at,
            ];
        });
        return response()->json(['logs' => $global]);
    }
}
