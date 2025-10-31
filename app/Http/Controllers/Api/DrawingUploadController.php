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
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use App\Models\ActivityLog;
use Carbon\Carbon;

class DrawingUploadController extends Controller
{
    protected string $disk = 'datacenter';

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

        $doctypeSubcategoryName = null;
        if (!empty($ids['doctype_subcategory_id'])) {
            $doctypeSubcategoryName = DB::table('doctype_subcategories')
                ->where('id', $ids['doctype_subcategory_id'])
                ->value('name');
        }

        if (!$customer || !$model || !$product || !$dg) {
            Log::error('Master data tidak valid', [
                'customer_id' => $ids['customer_id'],
                'model_id' => $ids['model_id'],
                'product_id' => $ids['product_id'],
                'doctype_group_id' => $ids['doctype_group_id'],
            ]);
            abort(422, 'Master data tidak valid');
        }

        $result = [
            'customer_code' => $customer->code,
            'model_name' => $model->name,
            'doctype_group_name' => $dg->name,
            'part_no' => $product->part_no,
        ];

        if ($doctypeSubcategoryName) {
            $result['doctype_subcategories_name'] = $doctypeSubcategoryName;
        }

        return $result;
    }

    public function checkRevisionStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer|exists:customers,id',
            'model_id' => 'required|integer|exists:models,id',
            'partNo' => 'required|integer|exists:products,id',
            'docType' => 'required|integer|exists:doctype_groups,id',
            'partGroup' => 'required|integer|exists:part_groups,id',
            'category' => 'nullable|integer|exists:doctype_subcategories,id',
            'ecn_no' => 'required|string|max:50',
            'revision_label_id' => 'nullable|integer|exists:customer_revision_labels,id',
        ]);

        $packageId = $this->findPackageId($validated);
        $requestLabelId = $request->revision_label_id ?: null;

        if ($packageId) {
            $existingRevision = DB::table('doc_package_revisions')
                ->where('package_id', $packageId)
                ->where('ecn_no', $request->ecn_no)
                ->first(); //

            if ($existingRevision) {
                $draftLabelId = $existingRevision->revision_label_id ?: null;


                if ($draftLabelId == $requestLabelId) {

                    if ($existingRevision->revision_status === 'draft') {
                        $files = DB::table('doc_package_revision_files')->where('revision_id', $existingRevision->id)
                            ->get(['id', 'filename as name', 'category', 'file_size as size'])
                            ->groupBy('category')->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);
                        return response()->json(['mode' => 'edit_draft', 'revision' => $existingRevision, 'files' => $files]); //
                    } else {
                        $files = DB::table('doc_package_revision_files')->where('revision_id', $existingRevision->id)
                            ->get(['id', 'filename as name', 'category', 'file_size as size'])
                            ->groupBy('category')->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

                        return response()->json([
                            'mode' => 'locked',
                            'revision' => $existingRevision,
                            'files' => $files,
                            'message' => 'ECN No. ' . htmlspecialchars($request->ecn_no) . ' already exists on this label and is locked with status: ' . ucfirst($existingRevision->revision_status)
                        ], 409);
                    }

                } else {
                    $labelName = $draftLabelId
                        ? DB::table('customer_revision_labels')->where('id', $draftLabelId)->value('label')
                        : '-- No Label --';

                    return response()->json([
                        'mode' => 'locked',
                        'message' => 'ECN No. ' . htmlspecialchars($request->ecn_no) . ' is already used by a draft on a different label: ' . htmlspecialchars($labelName) . '.'
                    ], 409);
                }
            }
        }

        $nextRev = 0;
        if ($packageId) {
            $query = DB::table('doc_package_revisions')->where('package_id', $packageId);

            $maxRevision = $query->max('revision_no');
            $nextRev = is_null($maxRevision) ? 0 : $maxRevision + 1;
        }

        return response()->json([
            'mode' => 'create_new',
            'next_rev' => $nextRev
        ]);
    }

    public function store(Request $r): JsonResponse
    {
        $validated = $r->validate([
            'customer' => 'required|integer|exists:customers,id',
            'model' => 'required|integer|exists:models,id',
            'partNo' => 'required|integer|exists:products,id',
            'docType' => 'required|integer|exists:doctype_groups,id',
            'category' => 'nullable|integer|exists:doctype_subcategories,id',
            'partGroup' => 'required|integer|exists:part_groups,id',
            'ecn_no' => 'required|string|max:50',
            'receipt_date' => 'nullable|date',
            'revision_label_id' => 'nullable|integer|exists:customer_revision_labels,id',
            'existing_revision_id' => 'nullable|integer|exists:doc_package_revisions,id',
            'files_to_delete' => 'nullable|array',
            'files_to_delete.*' => 'integer|exists:doc_package_revision_files,id',
            'files_2d.*' => 'nullable|file',
            'files_3d.*' => 'nullable|file',
            'files_ecn.*' => 'nullable|file',
        ]);

        $savedFilePaths = [];

        DB::beginTransaction();
        try {
            $packageIds = [
                'customer_id' => $validated['customer'],
                'model_id' => $validated['model'],
                'product_id' => $validated['partNo'],
                'doctype_group_id' => $validated['docType'],
                'doctype_subcategory_id' => $validated['category'] ?? null,
                'part_group_id' => $validated['partGroup'],
            ];
            $packageLabels = $this->getFolderLabels($packageIds);

            $packageId = $this->ensurePackage($packageIds, $packageLabels);
            $revisionId = $validated['existing_revision_id'] ?? null;

            $currentRevisionNo = 0; // Default
            $isEditMode = (bool) $revisionId;

            if ($revisionId) {
                // EDIT MODE
                $revToEdit = DB::table('doc_package_revisions')->where('id', $revisionId)->lockForUpdate()->first();

                if (!$revToEdit) {
                    throw new \Exception("Draft revision with ID {$revisionId} not found.");
                }

                if ($revToEdit->package_id != $packageId) {
                    Log::error('Package ID mismatch during draft edit', [
                        'form_package_id' => $packageId,
                        'draft_package_id' => $revToEdit->package_id,
                        'revision_id' => $revisionId,
                        'form_package_type' => gettype($packageId),
                        'draft_package_type' => gettype($revToEdit->package_id)
                    ]);
                    throw new \Exception("Metadata mismatch. The form data does not match the draft you are editing. Please refresh the page and try again.");
                }

                if ($revToEdit->revision_status !== 'draft') throw new \Exception("Revision is locked and cannot be edited.");

                if ($r->filled('note') && $revToEdit->note !== $r->note || $r->filled('receipt_date') && $revToEdit->receipt_date !== $r->receipt_date) {
                    DB::table('doc_package_revisions')->where('id', $revisionId)->update([
                        'note' => $r->note,
                        'receipt_date' => $r->receipt_date,
                    ]);
                }

                $currentRevisionNo = $revToEdit->revision_no;

            } else {
                $revQuery = DB::table('doc_package_revisions')->where('package_id', $packageId)->lockForUpdate();
                $maxRevision = $revQuery->max('revision_no');
                $nextRevisionNo = is_null($maxRevision) ? 0 : $maxRevision + 1;

                $currentRevisionNo = $nextRevisionNo;

                $existing = DB::table('doc_package_revisions')->where('package_id', $packageId)->where('ecn_no', $validated['ecn_no'])->lockForUpdate()->exists();
                if ($existing) throw new \Exception("ECN No. has just been created. Please refresh.");

                $revisionId = DB::table('doc_package_revisions')->insertGetId([
                    'package_id' => $packageId,
                    'revision_no' => $nextRevisionNo,
                    'ecn_no' => $validated['ecn_no'],
                    'receipt_date' => $validated['receipt_date'] ?? null,
                    'revision_label_id' => $validated['revision_label_id'] ?: null,
                    'revision_status' => 'draft',
                    'note' => $r->input('note'),
                    'is_obsolete' => 0,
                    'created_by' => $this->getAuthUserInt(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_active' => 1,
                ]);
            }

            $metaBase = $this->buildMetaBase($r, $currentRevisionNo);

            if (!empty($validated['files_to_delete'])) {
                $filesToDelete = DB::table('doc_package_revision_files')->whereIn('id', $validated['files_to_delete'])->get();
                foreach($filesToDelete as $file) { Storage::disk($this->disk)->delete($file->storage_path); }
                DB::table('doc_package_revision_files')->whereIn('id', $validated['files_to_delete'])->delete();
            }

            $allowedExtensions = $this->getAllowedExtensions();
            $fileOptions = $r->input('options', []);
            foreach (['2d', '3d', 'ecn'] as $docFolder) {
                if ($r->hasFile("files_{$docFolder}")) {
                    foreach ($r->file("files_{$docFolder}") as $file) {
                        $originalExt = strtolower($file->getClientOriginalExtension());
                        if (!in_array($originalExt, $allowedExtensions->get($docFolder, []))) {
                            throw \Illuminate\Validation\ValidationException::withMessages(["files_{$docFolder}" => "Tipe file '{$originalExt}' tidak diizinkan."]);
                        }

                        $originalClientName = $file->getClientOriginalName();
                        $sanitizedOriginalName = PathBuilder::sanitizeFilename($originalClientName);

                        if (empty($sanitizedOriginalName)) {
                            $sanitizedOriginalName = Str::random(10) . '.' . $originalExt;
                        }

                        $rootPath = PathBuilder::root($metaBase);
                        $revFolder = PathBuilder::revisionFolderName($metaBase);
                        $baseDir = $rootPath . '/' . $revFolder . '/' . strtolower($docFolder);

                        $fullStoragePath = $baseDir . '/' . $sanitizedOriginalName;

                        $action = $fileOptions[$docFolder][$originalClientName] ?? 'add';

                        $existingFileRecord = null;
                        if ($action === 'replace') {
                            $existingFileRecord = DB::table('doc_package_revision_files')
                                ->where('revision_id', $revisionId)
                                ->where('storage_path', $fullStoragePath)
                                ->first();
                        }


                        if ($action === 'replace' && $existingFileRecord) {

                            Storage::disk($this->disk)->putFileAs($baseDir, $file, $sanitizedOriginalName);
                            $diskPath = Storage::disk($this->disk)->path($fullStoragePath);

                            DB::table('doc_package_revision_files')
                                ->where('id', $existingFileRecord->id)
                                ->update([
                                    'file_size' => filesize($diskPath),
                                    'checksum_sha256' => hash_file('sha256', $diskPath),
                                    'uploaded_by' => $this->getAuthUserInt(),
                                    'updated_at' => now(),
                                ]);

                        } else {

                            if ($action === 'suffix') {
                                $filenameWithoutExt = pathinfo($sanitizedOriginalName, PATHINFO_FILENAME);
                                $fileExt = pathinfo($sanitizedOriginalName, PATHINFO_EXTENSION);
                                $uniqueSuffix = '_' . uniqid();

                                $sanitizedOriginalName = $filenameWithoutExt . $uniqueSuffix . '.' . $fileExt;
                                $fullStoragePath = $baseDir . '/' . $sanitizedOriginalName;
                            }

                            Storage::disk($this->disk)->putFileAs($baseDir, $file, $sanitizedOriginalName);
                            $savedFilePaths[] = $fullStoragePath;
                            $diskPath = Storage::disk($this->disk)->path($fullStoragePath);

                            DB::table('doc_package_revision_files')->insert([
                                'revision_id' => $revisionId,
                                'category' => strtoupper($docFolder),
                                'file_extension_id' => $this->ensureFileExtension($originalExt),
                                'filename' => $sanitizedOriginalName,
                                'storage_path' => $fullStoragePath,
                                'file_size' => filesize($diskPath),
                                'checksum_sha256' => hash_file('sha256', $diskPath),
                                'uploaded_by' => $this->getAuthUserInt(), 'created_at' => now(), 'updated_at' => now(), 'is_active' => 1,
                            ]);
                        }
                    }
                }
            }

            //  Activity Logging ---
            $package = DB::table('doc_packages')->where('id', $packageId)->first(['package_no']);
            $subCatName = null;
            if (!empty($packageIds['doctype_subcategory_id'])) {
                $subCatName = DB::table('doctype_subcategories')
                                ->where('id', $packageIds['doctype_subcategory_id'])
                                ->value('name');
            }

            $labelName = null;
            if (!empty($validated['revision_label_id'])) {
                $labelName = DB::table('customer_revision_labels')
                                ->where('id', $validated['revision_label_id'])
                                ->value('label');
            }

            $partGroupCode = DB::table('part_groups')->where('id', $packageIds['part_group_id'])->value('code_part_group');

            $metaLogData = [
                'part_no' => $packageLabels['part_no'] ?? null,
                'doctype_group' => $packageLabels['doctype_group_name'] ?? null,
                'customer_code' => $packageLabels['customer_code'] ?? null,
                'model_name' => $packageLabels['model_name'] ?? null,
                'part_group_code' => $partGroupCode,
                'doctype_subcategory' => $subCatName,
                'note' => $r->input('note'),
                'package_no' => $package ? $package->package_no : null,
                'revision_no' => $currentRevisionNo,
                'ecn_no' => $validated['ecn_no'],
                'receipt_date' => $validated['receipt_date'] ?? null,
                'revision_label' => $labelName
            ];

            $activityCode = 'UPLOAD';

            ActivityLog::create([
                'user_id' => $this->getAuthUserInt(),
                'activity_code' => $activityCode,
                'scope_type' => 'revision',
                'scope_id' => $packageId,
                'revision_id' => $revisionId,
                'meta' => $metaLogData,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Draft has been saved successfully.', 'revision_id' => $revisionId, 'package_id' => $packageId]);

        }
        catch (\Exception $e) {
            DB::rollBack();

            foreach ($savedFilePaths as $path) {
                Storage::disk($this->disk)->delete($path);
            }

            $errors = [];
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->errors();
                $message = $e->getMessage();
                $statusCode = 422;
            } else {
                $message = 'An unexpected error occurred on the server.';
                $statusCode = 500;
            }

            Log::error('Upload failed and rolled back', [
                'error' => $e->getMessage(),
                'errors' => $errors,
            ]);

            return response()->json([
                'message' => $message,
                'errors' => $errors,
            ], $statusCode);
        }

    }

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

    private function ensurePackage(array $ids, array $labels): int
    {
        $q = DB::table('doc_packages')
            ->where('customer_id', $ids['customer_id'])
            ->where('model_id', $ids['model_id'])
            ->where('product_id', $ids['product_id'])
            ->where('doctype_group_id', $ids['doctype_group_id'])
            ->where('part_group_id', $ids['part_group_id']);

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
            'current_revision_no' => 0,
            'current_revision_id' => null,
            'created_by' => $this->getAuthUserInt(),
            'created_at' => $now,
            'updated_at' => $now,
            'is_active' => 1,
        ];

        return DB::table('doc_packages')->insertGetId($insert);
    }

    private function getAuthUserInt(): int
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'User is not authenticated for this action.');
        }

        return (int) $user->id;
    }

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

    private function pickRevisionLabel(int $customerId, int $revisionNo): ?int
    {
        $row = DB::table('customer_revision_labels')
            ->where('customer_id', $customerId)
            ->where('sort_order', $revisionNo)
            ->where('is_active', 1)
            ->first(['id']);
        return $row ? $row->id : null;
    }

    public function getPublicAllowedExtensions(): JsonResponse
    {
        return response()->json($this->getAllowedExtensions());
    }

    protected function getAllowedExtensions()
    {
        return DB::table('file_extensions as fe')
            ->join('category_file_extension as cfe', 'fe.id', '=', 'cfe.file_extension_id')
            ->select('fe.code', 'cfe.category_name')
            ->get()
            ->groupBy('category_name')
            ->map(fn ($items) => $items->pluck('code')->map('strtolower')->all())
            ->mapWithKeys(fn ($items, $key) => [strtolower($key) => $items]);
    }

    private function buildMetaBase(Request $r): array
    {
        $ids = [
            'customer_id' => $r->input('customer_id', $r->input('customer')),
            'model_id' => $r->input('model_id', $r->input('model')),
            'product_id' => $r->input('partNo'),
            'doctype_group_id' => $r->input('docType'),
            'doctype_subcategory_id' => $r->input('category'),
        ];
        $labels = $this->getFolderLabels($ids);
        $partGroup = DB::table('part_groups')->where('id', (int)($r->input('partGroup')))->value('code_part_group');
        $revisionLabelName = $r->input('revision_label_id')
            ? DB::table('customer_revision_labels')->where('id', $r->input('revision_label_id'))->value('label')
            : $r->input('label_name');

        return $labels + [
            'part_group' => $partGroup,
            'revision_label_name' => $revisionLabelName,
            'rev' => (int)($r->input('revision_no', $r->input('next_rev', 0))),
            'ecn_no' => $r->input('ecn_no'),
        ];
    }

    private function findPackageId(array $requestData): ?int
    {
        $q = DB::table('doc_packages')->where([
            'customer_id' => $requestData['customer_id'],
            'model_id' => $requestData['model_id'],
            'product_id' => $requestData['partNo'],
            'doctype_group_id' => $requestData['docType'],
            'part_group_id' => $requestData['partGroup'],
        ]);

        if (!empty($requestData['category'])) {
            $q->where('doctype_subcategory_id', $requestData['category']);
        } else {
            $q->whereNull('doctype_subcategory_id');
        }

        $result = $q->value('id');
        return $result ? (int)$result : null;
    }

    public function requestApproval(Request $r): JsonResponse
    {
        $r->validate([
            'package_id' => 'required|integer|exists:doc_packages,id',
            'revision_id' => 'required|integer|exists:doc_package_revisions,id',
        ]);

        $packageId = (int) $r->package_id;
        $revisionId = (int) $r->revision_id;

        // find revision
        $revision = DB::table('doc_package_revisions')->where('id', $revisionId)->first();

        if (!$revision || $revision->package_id != $packageId) {
            return response()->json(['message' => 'Revision not found for the given package.'], 422);
        }

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

            // Activity Logging ---
            $labelName = null;
            if (!empty($revision->revision_label_id)) {
                $labelName = DB::table('customer_revision_labels')
                                ->where('id', $revision->revision_label_id)
                                ->value('label');
            }

            $package = DB::table('doc_packages')->where('id', $packageId)->first(['part_group_id']);
            $partGroupCode = null;
            if ($package) {
                $partGroupCode = DB::table('part_groups')->where('id', $package->part_group_id)->value('code_part_group');
            }

            $metaLogData = [
                'package_id' => $packageId,
                'revision_no' => $revision->revision_no,
                'ecn_no' => $revision->ecn_no,
                'revision_label' => $labelName,
                'part_group_code' => $partGroupCode
            ];

            $activityCode = 'SUBMIT_APPROVAL';

            ActivityLog::create([
                'user_id' => $this->getAuthUserInt(),
                'activity_code' => $activityCode,
                'scope_type' => 'revision',
                'scope_id' => $packageId,
                'revision_id' => $revision->id,
                'meta' => $metaLogData,
            ]);

            DB::commit();

            $encryptedId = str_replace('=', '-', encrypt($revisionId));

            return response()->json([
                'success' => true,
                'message' => 'Revision set to pending and approval requested.',
                'revision_id' => $encryptedId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to request approval', ['error' => $e->getMessage(), 'package_id' => $packageId, 'revision_no' => $revision->revision_no]);
            return response()->json(['message' => 'Failed to request approval.'], 500);
        }
    }

    public function activityLogs(Request $r): JsonResponse
    {
        $customer = $r->input('customer');
        $model = $r->input('model');
        $partNo = $r->input('partNo');
        $docType = $r->input('docType');
        $category = $r->input('category');
        $partGroup = $r->input('partGroup');
        $revisionNo = $r->input('revision_no');

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

    public function checkConflicts(Request $r): JsonResponse
    {
        $validated = $r->validate([
            'customer' => 'required|integer',
            'model' => 'required|integer',
            'partNo' => 'required|integer',
            'docType' => 'required|integer',
            'category' => 'nullable|integer',
            'partGroup' => 'required|integer',
            'ecn_no' => 'required|string',
            'revision_label_id' => 'nullable|integer',
            'revision_no' => 'nullable|integer',

            'files_2d' => 'nullable|array',
            'files_3d' => 'nullable|array',
            'files_ecn' => 'nullable|array',
        ]);

        try {

            $revNo = $r->input('revision_no', 0);
            $metaBase = $this->buildMetaBase($r, $revNo);
            $rootPath = PathBuilder::root($metaBase);
            $revFolder = PathBuilder::revisionFolderName($metaBase);

            $conflicts = [
                '2d' => [],
                '3d' => [],
                'ecn' => [],
            ];

            foreach (['2d', '3d', 'ecn'] as $docFolder) {
                if (!$r->has("files_{$docFolder}")) continue;

                $baseDir = $rootPath . '/' . $revFolder . '/' . strtolower($docFolder);

                foreach ($r->input("files_{$docFolder}") as $filename) {
                    $sanitizedName = PathBuilder::sanitizeFilename($filename);
                    $fullStoragePath = $baseDir . '/' . $sanitizedName;

                    if (Storage::disk($this->disk)->exists($fullStoragePath)) {
                        $conflicts[$docFolder][] = $filename;
                    }
                }
            }

            return response()->json(['conflicts' => $conflicts]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }


    public function reviseConfirmed(Request $r): JsonResponse
    {
        $encryptedId = $r->revision_id;

        DB::beginTransaction();

        $revisionId = Crypt::decrypt(str_replace('-', '=', $encryptedId));

        $updateCount =  DB::table('doc_package_revisions')
            ->where('id', $revisionId)
            ->update(['revision_status' => 'draft', 'updated_at' => Carbon::now()]);

        if ($updateCount !== 1) {
            DB::rollBack();
            return response()->json(['message' => 'Revision not found or already in draft.', 'status' => 'error', 'read-only' => 'false'], 404);
        }

        $dbPackage = DB::table('doc_package_revisions')->where('id', $revisionId)->first(['id', 'package_id', 'revision_label_id', 'revision_no', 'ecn_no']);

        if (!$dbPackage) {
            DB::rollBack();
            return response()->json(['message' => 'Database consistency error.', 'status' => 'error', 'read-only' => 'false'], 500);
        }

        $dbCustomerRevisionLabel = DB::table('customer_revision_labels')
            ->where('id', $dbPackage->revision_label_id)
            ->first(['label']);

        $labelName = $dbCustomerRevisionLabel ? $dbCustomerRevisionLabel->label : null;

        $package = DB::table('doc_packages')->where('id', $dbPackage->package_id)->first(['part_group_id']);
        $partGroupCode = null;
        if ($package) {
            $partGroupCode = DB::table('part_groups')->where('id', $package->part_group_id)->value('code_part_group');
        }

        $metaLogData = [
            'package_id' => $dbPackage->package_id,
            'revision_no' => $dbPackage->revision_no,
            'ecn_no' => $dbPackage->ecn_no,
            'revision_label' => $labelName,
            'previous_status' => 'approved',
            'part_group_code' => $partGroupCode
        ];

        ActivityLog::create([
            'user_id' => Auth::user()->id,
            'activity_code' => 'REVISE_CONFIRM',
            'scope_type' => 'revision',
            'scope_id' => $dbPackage->package_id,
            'revision_id' => $revisionId,
            'meta' => $metaLogData,
        ]);

        DB::commit();

        return response()->json(['message' => 'Revision confirmed successfully.', 'status' => 'success', 'read-only' => 'true'], 200);
    }
}
