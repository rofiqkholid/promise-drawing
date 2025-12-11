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
use App\Models\CustomerRevisionLabel;
use App\Models\FileExtensions;

class DrawingUploadController extends Controller
{
    protected string $disk = 'datacenter';

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

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
            ->where('is_delete', 0)
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
            'category' => 'required|integer|exists:doctype_subcategories,id',
            'ecn_no' => 'required|string|max:50',
            'revision_label_id' => 'nullable|integer|exists:customer_revision_labels,id',
            'existing_revision_id' => 'nullable|integer|exists:doc_package_revisions,id'
        ]);

        $packageId = $this->findPackageId($validated);
        $requestLabelId = $request->revision_label_id ?: null;
        $existingRevisionId = $request->input('existing_revision_id');

        if ($packageId) {
            $conflictQuery = DB::table('doc_package_revisions')
                ->where('package_id', $packageId)
                ->where('ecn_no', $request->ecn_no);

            if ($existingRevisionId) {
                $conflictQuery->where('id', '!=', $existingRevisionId);
            }

            $conflictingRevision = $conflictQuery->first();

            if ($conflictingRevision) {
                $labelName = $conflictingRevision->revision_label_id
                    ? DB::table('customer_revision_labels')->where('id', $conflictingRevision->revision_label_id)->value('label')
                    : '-- No Label --';

                return response()->json([
                    'mode' => 'locked',
                    'message' => 'ECN No. ' . htmlspecialchars($request->ecn_no) . ' is already used by another revision: ' . htmlspecialchars($labelName) . '.'
                ], 409);
            }

            if ($existingRevisionId) {
                $currentDraft = DB::table('doc_package_revisions')->where('id', $existingRevisionId)->first();

                if ($currentDraft->revision_status !== 'draft') {
                    return response()->json([
                        'mode' => 'locked',
                        'revision' => $currentDraft,
                        'message' => 'This revision is locked and cannot be edited.'
                    ], 409);
                }

                $files = DB::table('doc_package_revision_files')->where('revision_id', $currentDraft->id)
                    ->get(['id', 'filename as name', 'category', 'file_size as size'])
                    ->groupBy('category')->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

                return response()->json(['mode' => 'edit_draft', 'revision' => $currentDraft, 'files' => $files]);
            }

            $query = DB::table('doc_package_revisions')->where('package_id', $packageId);
            $maxRevision = $query->max('revision_no');
            $nextRev = is_null($maxRevision) ? 0 : $maxRevision + 1;

            return response()->json([
                'mode' => 'create_new',
                'next_rev' => $nextRev
            ]);
        }

        $nextRev = 0;
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
            'category' => 'required|integer|exists:doctype_subcategories,id',
            'partGroup' => 'required|integer|exists:part_groups,id',
            'ecn_no' => 'required|string|max:50',
            'receipt_date' => 'nullable|date',
            'revision_label_id' => 'nullable|integer|exists:customer_revision_labels,id',
            'is_finish' => 'required|in:0,1',
            'revision_no' => 'nullable|integer',
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
                'doctype_subcategory_id' => $validated['category'],
                'part_group_id' => $validated['partGroup'],
            ];
            $packageLabels = $this->getFolderLabels($packageIds);

            $packageId = $this->ensurePackage($packageIds, $packageLabels, $validated['ecn_no'] ?? null);
            $revisionId = $validated['existing_revision_id'] ?? null;

            $currentRevisionNo = 0; // Default
            $metaBase = null;

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

                $oldMeta = $this->buildMetaBaseFromDb($revToEdit, $packageIds);
                $oldRevFolder = PathBuilder::revisionFolderName($oldMeta);
                $oldRoot = PathBuilder::root($oldMeta);
                $oldRevPath = $oldRoot . '/' . $oldRevFolder;

                $updates = [];
                $needsFolderMove = false;

                $newEcn = $r->input('ecn_no');
                if ($newEcn !== $revToEdit->ecn_no) {
                    $existing = DB::table('doc_package_revisions')
                        ->where('package_id', $packageId)
                        ->where('ecn_no', $newEcn)
                        ->where('id', '!=', $revisionId)
                        ->first();
                    if ($existing) {
                        throw new \Exception("ECN No. '{$newEcn}' is already in use by another revision for this package.");
                    }
                    $updates['ecn_no'] = $newEcn;
                    $needsFolderMove = true;
                }

                $newLabelId = $r->input('revision_label_id') ?: null;
                if ($newLabelId != $revToEdit->revision_label_id) {
                    $updates['revision_label_id'] = $newLabelId;
                    $needsFolderMove = true;
                }

                $newReceiptDate = $r->input('receipt_date') ?: null;
                if ($newReceiptDate != $revToEdit->receipt_date) {
                    $updates['receipt_date'] = $newReceiptDate;
                }

                $newNote = $r->input('note');
                if ($newNote !== $revToEdit->note) {
                    $updates['note'] = $newNote;
                }

                $newIsFinish = (int)$r->input('is_finish');
                if ($newIsFinish !== (int)$revToEdit->is_finish) {
                    $updates['is_finish'] = $newIsFinish;
                }

                $currentRevisionNo = $revToEdit->revision_no;
                $metaBase = $this->buildMetaBase($r, $currentRevisionNo); // Pakai rev_no yang ada
                $newRevFolder = PathBuilder::revisionFolderName($metaBase);
                $newRoot = PathBuilder::root($metaBase);
                $newRevPath = $newRoot . '/' . $newRevFolder;

                if (!empty($updates)) {
                    $updates['updated_at'] = now();
                    DB::table('doc_package_revisions')->where('id', $revisionId)->update($updates);
                }

                if ($needsFolderMove && $oldRevPath !== $newRevPath && Storage::disk($this->disk)->exists($oldRevPath)) {
                    if (Storage::disk($this->disk)->exists($newRevPath)) {
                        throw new \Exception("Target folder '{$newRevPath}' already exists. Cannot move.");
                    }

                    Storage::disk($this->disk)->move($oldRevPath, $newRevPath);

                    $filesToUpdate = DB::table('doc_package_revision_files')->where('revision_id', $revisionId)->get();
                    foreach ($filesToUpdate as $file) {
                        $newStoragePath = str_replace($oldRevPath, $newRevPath, $file->storage_path);
                        DB::table('doc_package_revision_files')
                            ->where('id', $file->id)
                            ->update(['storage_path' => $newStoragePath, 'updated_at' => now()]);
                    }
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
                    'is_finish' => (int)$r->input('is_finish', 0),
                    'is_obsolete' => 0,
                    'created_by' => $this->getAuthUserInt(),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'is_active' => 1,
                ]);
            }

            if ($metaBase === null) {
                $metaBase = $this->buildMetaBase($r, $currentRevisionNo);
            }

            if (!empty($validated['files_to_delete'])) {
                $filesToDelete = DB::table('doc_package_revision_files')->whereIn('id', $validated['files_to_delete'])->get();
                foreach ($filesToDelete as $file) {
                    Storage::disk($this->disk)->delete($file->storage_path);
                }
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
                                'uploaded_by' => $this->getAuthUserInt(),
                                'created_at' => now(),
                                'updated_at' => now(),
                                'is_active' => 1,
                            ]);
                        }
                    }
                }
            }

            // Activity Logging 
            $package = DB::table('doc_packages')->where('id', $packageId)->first(['package_no']);
            
            // Ambil Nama Subcategory & Part Group
            $subCatName = null;
            if (!empty($packageIds['doctype_subcategory_id'])) {
                $subCatName = DB::table('doctype_subcategories')
                    ->where('id', $packageIds['doctype_subcategory_id'])
                    ->value('name');
            }
            
            $partGroupCode = DB::table('part_groups')
                ->where('id', $packageIds['part_group_id'])
                ->value('code_part_group');

            // Ambil Nama Label Revisi
            $labelName = null;
            if (!empty($validated['revision_label_id'])) {
                $labelName = DB::table('customer_revision_labels')
                    ->where('id', $validated['revision_label_id'])
                    ->value('label');
            }

            // Hitung Statistik File yang Diupload
            $fileCount = count($savedFilePaths);
            $fileTypes = collect($savedFilePaths)->map(function($path) {
                return strtoupper(pathinfo($path, PATHINFO_EXTENSION));
            })->unique()->values()->implode(', '); // Contoh: "PDF, STEP"

            // Susun Meta Data Lengkap
            $metaLogData = [
                // Snapshot Data Utama
                'part_no'         => $packageLabels['part_no'] ?? null,
                'customer_code'   => $packageLabels['customer_code'] ?? null,
                'model_name'      => $packageLabels['model_name'] ?? null,
                'doctype_group'   => $packageLabels['doctype_group_name'] ?? null,
                'part_group_code' => $partGroupCode,
                
                // Detail Revisi
                'package_no'      => $package ? $package->package_no : null,
                'revision_no'     => $currentRevisionNo,
                'ecn_no'          => $validated['ecn_no'],
                'receipt_date'    => $validated['receipt_date'] ?? null,
                'revision_label'  => $labelName,
                'doctype_subcategory' => $subCatName,
                
                'file_count'      => $fileCount,
                'file_types'      => $fileTypes,
                'note'            => $r->input('note'),
            ];

            ActivityLog::create([
                'user_id'       => $this->getAuthUserInt(),
                'activity_code' => 'UPLOAD',
                'scope_type'    => 'revision',
                'scope_id'      => $packageId,
                'revision_id'   => $revisionId,
                'meta'          => $metaLogData,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Draft has been saved successfully.', 'revision_id' => $revisionId, 'package_id' => $packageId]);
        } catch (\Throwable $e) {
            DB::rollBack();

            foreach ($savedFilePaths as $path) {
                if (Storage::disk($this->disk)->exists($path)) {
                    Storage::disk($this->disk)->delete($path);
                }
            }

            $errors = [];
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->errors();
                $message = $e->getMessage();
                $statusCode = 422;
            } else {
                $message = $e->getMessage(); // Expose the error message for debugging
                // For production, you might want to hide this: $message = 'An unexpected error occurred on the server.';
                $statusCode = 500;
            }

            Log::error('Upload failed and rolled back', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'errors' => $errors,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'message' => $message,
                'errors' => $errors,
                'exception' => config('app.debug') ? get_class($e) : null,
                'file' => config('app.debug') ? $e->getFile() : null,
                'line' => config('app.debug') ? $e->getLine() : null,
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

        $query = DB::table('models as m')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->select('m.id', 'm.name', 'ps.name as status_name')
            ->where('m.customer_id', $customer_id);

        if ($searchTerm) {
            $query->where('m.name', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();

        $groups = $query->orderBy('m.name', 'asc')
            ->offset($offset)
            ->limit($resultsPerPage)
            ->get();

        $formattedGroups = $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'text' => "{$group->name} - {$group->status_name}",
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

        $query = DB::table('products')->select('id', 'part_no', 'group_id', 'part_name')->where('model_id', $model_id)->where('is_delete', 0);

        if ($searchTerm) {
            $query->where('part_no', 'LIKE', '%' . $searchTerm . '%');
        }

        $totalCount = $query->count();
        $groups = $query->orderBy('part_no', 'asc')->offset($offset)->limit($resultsPerPage)->get();

        $formattedGroups = $groups->map(function ($group) {
            $partners = [];
            if ($group->group_id) {
                $partners = DB::table('products')
                    ->where('group_id', $group->group_id)
                    ->where('id', '!=', $group->id)
                    ->where('is_delete', 0)
                    ->pluck('part_no')
                    ->toArray();
            }

            return [
                'id' => $group->id,
                'text' => $group->part_no,
                'is_paired' => !empty($partners),
                'partner_names' => !empty($partners) ? implode(', ', $partners) : null
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

    public function getCustomerRevisionLabels(Request $request, $customerId)
    {
        $labels = CustomerRevisionLabel::where('customer_id', $customerId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'asc')
            ->get(['id', 'label as text']);

        if ($labels->isEmpty()) {
            return response()->json(['has_labels' => false, 'labels' => []]);
        }

        return response()->json(['has_labels' => true, 'labels' => $labels]);
    }

    private function ensurePackage(array $ids, array $labels, ?string $ecnNo = null): int
    {
        $q = DB::table('doc_packages')
            ->where('customer_id', $ids['customer_id'])
            ->where('model_id', $ids['model_id'])
            ->where('product_id', $ids['product_id'])
            ->where('doctype_group_id', $ids['doctype_group_id'])
            ->where('part_group_id', $ids['part_group_id'])
            ->where('is_delete', 0);

        if (!empty($ids['doctype_subcategory_id'])) {
            $q->where('doctype_subcategory_id', $ids['doctype_subcategory_id']);
        } else {
            $q->whereNull('doctype_subcategory_id');
        }

        $existing = $q->first(['id']);
        if ($existing) {
            return $existing->id;
        }

        // create package_no: SAI-{CUSTCODE}-{MODEL}-{PARTNO}-{TIMESTAMP}-{RANDOM}
        $cust = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['customer_code'] ?? 'CUST'));
        $model = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['model_name'] ?? 'MDL'));
        $part = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($labels['part_no'] ?? 'PRT'));
        
        $timestamp = Carbon::now()->format('Ymd');
        $random = strtoupper(Str::random(5));
        
        $pkgNo = sprintf('SAI-%s-%s-%s-%s-%s', $cust, $model, $part, $timestamp, $random);

        while (DB::table('doc_packages')->where('package_no', $pkgNo)->exists()) {
            $random = strtoupper(Str::random(5));
            $pkgNo = sprintf('SAI-%s-%s-%s-%s-%s', $cust, $model, $part, $timestamp, $random);
        }

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
            'is_delete' => 0,
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
        $validationMap = $this->getAllowedExtensions();

        $allExtensions = FileExtensions::get(['code', 'icon', 'icon_mime']);
        $iconMap = $allExtensions
            ->mapWithKeys(fn(FileExtensions $ext) => [
                strtolower($ext->code) => $ext->icon_src
            ])
            ->filter();

        return response()->json([
            'validation' => $validationMap,
            'icons' => $iconMap
        ]);
    }

    protected function getAllowedExtensions()
    {
        return DB::table('file_extensions as fe')
            ->join('category_file_extension as cfe', 'fe.id', '=', 'cfe.file_extension_id')
            ->select('fe.code', 'cfe.category_name')
            ->get()
            ->groupBy('category_name')
            ->map(fn($items) => $items->pluck('code')->map('strtolower')->all())
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);
    }

    private function buildMetaBaseFromDb(object $revision, array $packageIds): array
    {
        $labels = $this->getFolderLabels($packageIds);
        $partGroup = DB::table('part_groups')->where('id', $packageIds['part_group_id'])->value('code_part_group');

        $revisionLabelName = null;
        if ($revision->revision_label_id) {
            $revisionLabelName = DB::table('customer_revision_labels')->where('id', $revision->revision_label_id)->value('label');
        }

        return $labels + [
            'part_group' => $partGroup,
            'revision_label_name' => $revisionLabelName,
            'rev' => (int) $revision->revision_no,
            'ecn_no' => $revision->ecn_no,
        ];
    }

    private function buildMetaBase(Request $r, int $revNo): array
    {
        $ids = [
            'customer_id' => $r->input('customer_id', $r->input('customer')),
            'model_id' => $r->input('model_id', $r->input('model')),
            'product_id' => $r->input('partNo'),
            'doctype_group_id' => $r->input('docType'),
            'doctype_subcategory_id' => $r->input('category'),
            'part_group_id' => $r->input('partGroup'),
        ];
        $labels = $this->getFolderLabels($ids);

        $partGroup = DB::table('part_groups')->where('id', (int)($r->input('partGroup')))->value('code_part_group');

        $revisionLabelName = $r->input('revision_label_id')
            ? DB::table('customer_revision_labels')->where('id', $r->input('revision_label_id'))->value('label')
            : $r->input('label_name');

        return $labels + [
            'part_group' => $partGroup,
            'revision_label_name' => $revisionLabelName,
            'rev' => $revNo,
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
        ])->where('is_delete', 0);

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
            'package_id'  => 'required|integer|exists:doc_packages,id',
            'revision_id' => 'required|integer|exists:doc_package_revisions,id',
        ]);

        $packageId  = $r->package_id;
        $revisionId = $r->revision_id;

        // find revision
        $revision = DB::table('doc_package_revisions')->where('id', $revisionId)->first();

        if (!$revision || $revision->package_id != $packageId) {
            return response()->json(['message' => 'Revision not found for the given package.'], 422);
        }

        DB::beginTransaction();
        try {
            $now    = Carbon::now();
            $userId = $this->getAuthUserInt();

            // Set status revision to 'pending'
            DB::table('doc_package_revisions')
                ->where('id', $revision->id)
                ->update([
                    'revision_status' => 'pending',
                    'updated_at'      => $now,
                ]);

            // Reset / buat record di package_approvals
            $existingApproval = DB::table('package_approvals')
                ->where('package_id', $packageId)
                ->where('revision_id', $revision->id)
                ->where('is_active', 1)
                ->first(['id']);

            if ($existingApproval) {
                // UPDATE:jadi pending lagi
                DB::table('package_approvals')
                    ->where('id', $existingApproval->id)
                    ->update([
                        'decision'     => 'pending',
                        'reason'       => null,
                        'decided_by'   => null,
                        'decided_at'   => null,
                        'requested_by' => $userId,
                        'requested_at' => $now,
                        'updated_at'   => $now,
                    ]);
            } else {
                // INSERT: kalau belum pernah ada approval untuk revision ini
                DB::table('package_approvals')->insertGetId([
                    'package_id'    => $packageId,
                    'revision_id'   => $revision->id,
                    'requested_by'  => $userId,
                    'requested_at'  => $now,
                    'decision'      => 'pending',
                    'is_active'     => 1,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }

            // Activity Logging 
            $packageInfo = DB::table('doc_packages as dp')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
                ->where('dp.id', $packageId)
                ->where('dp.is_delete', 0)   // <---
    ->where('p.is_delete', 0)
                ->select(
                    'c.code as customer',
                    'm.name as model',
                    'p.part_no',
                    'dtg.name as doc_type',
                    'pg.code_part_group'
                )
                ->first();

            $labelName = null;
            if (!empty($revision->revision_label_id)) {
                $labelName = DB::table('customer_revision_labels')
                    ->where('id', $revision->revision_label_id)
                    ->value('label');
            }

            $metaLogData = [
                'part_no'         => $packageInfo->part_no,
                'customer_code'   => $packageInfo->customer,
                'model_name'      => $packageInfo->model,
                'doc_type'        => $packageInfo->doc_type,
                'part_group_code' => $packageInfo->code_part_group ?? '-',
                'package_id'      => $packageId,
                'revision_no'     => $revision->revision_no,
                'ecn_no'          => $revision->ecn_no,
                'revision_label'  => $labelName,
            ];

            ActivityLog::create([
                'user_id'       => $userId,
                'activity_code' => 'SUBMIT_APPROVAL',
                'scope_type'    => 'revision',
                'scope_id'      => $packageId,
                'revision_id'   => $revision->id,
                'meta'          => $metaLogData,
            ]);

            DB::commit();

            $encryptedId = str_replace('=', '-', encrypt($revisionId));

            return response()->json([
                'success'     => true,
                'message'     => 'Revision set to pending and approval requested.',
                'revision_id' => $encryptedId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to request approval', [
                'error'       => $e->getMessage(),
                'package_id'  => $packageId,
                'revision_no' => $revision->revision_no,
            ]);
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
            $q = DB::table('doc_packages')->where('customer_id', (int) $customer)->where('model_id', (int) $model)->where('product_id', (int) $partNo)->where('doctype_group_id', (int) $docType)->where('part_group_id', (int) $partGroup)->where('is_delete', 0);
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

        $revisionData = DB::table('doc_package_revisions')->where('id', $revisionId)->lockForUpdate()->first(['revision_status']);

        if (!$revisionData) {
            DB::rollBack();
            return response()->json(['message' => 'Revision not found.', 'status' => 'error', 'read-only' => 'false'], 404);
        }
        $previousStatus = $revisionData->revision_status;

        $updateCount =  DB::table('doc_package_revisions')
            ->where('id', $revisionId)
            ->update(['revision_status' => 'draft', 'updated_at' => Carbon::now()]);

        if ($updateCount !== 1) {
            DB::rollBack();
            return response()->json(['message' => 'Revision not found or error updating.', 'status' => 'error', 'read-only' => 'false'], 500);
        }

        $dbPackage = DB::table('doc_package_revisions')->where('id', $revisionId)->first(['id', 'package_id', 'revision_label_id', 'revision_no', 'ecn_no']);

        if (!$dbPackage) {
            DB::rollBack();
            return response()->json(['message' => 'Database consistency error.', 'status' => 'error', 'read-only' => 'false'], 500);
        }

        $dbCustomerRevisionLabel = DB::table('customer_revision_labels')
            ->where('id', $dbPackage->revision_label_id)
            ->first(['label']);

        $packageInfo = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->where('dp.id', $dbPackage->package_id)
            ->where('dp.is_delete', 0)   // <---
    ->where('p.is_delete', 0)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dtg.name as doc_type',
                'pg.code_part_group'
            )
            ->first();

        // Ambil Label Name
        $labelName = null;
        if ($dbPackage->revision_label_id) {
            $labelName = DB::table('customer_revision_labels')
                ->where('id', $dbPackage->revision_label_id)
                ->value('label');
        }

        // Susun Meta Data
        $metaLogData = [
            // Snapshot Data Utama
            'part_no'         => $packageInfo->part_no,
            'customer_code'   => $packageInfo->customer,
            'model_name'      => $packageInfo->model,
            'doc_type'        => $packageInfo->doc_type,
            'part_group_code' => $packageInfo->code_part_group ?? '-',
            
            // Detail Revisi & Status
            'package_id'      => $dbPackage->package_id,
            'revision_no'     => $dbPackage->revision_no,
            'ecn_no'          => $dbPackage->ecn_no,
            'revision_label'  => $labelName,
            'previous_status' => $previousStatus,
            'current_status'  => 'draft'
        ];

        ActivityLog::create([
            'user_id'       => Auth::user()->id,
            'activity_code' => 'REVISE_CONFIRM',
            'scope_type'    => 'revision',
            'scope_id'      => $dbPackage->package_id,
            'revision_id'   => $revisionId,
            'meta'          => $metaLogData,
        ]);

        DB::commit();

        return response()->json(['message' => 'Revision confirmed successfully.', 'status' => 'success', 'read-only' => 'true'], 200);
    }

    public function destroyRevision(Request $request, $id): JsonResponse
    {
        $revisionId = (int) $id;

        DB::beginTransaction();
        try {
            $revision = DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->lockForUpdate()
                ->first();

            if (!$revision) {
                throw new \Exception('Draft revision not found.', 404);
            }

            if ($revision->revision_status !== 'draft') {
                throw new \Exception('Only draft revisions can be deleted.', 422);
            }

            $package = DB::table('doc_packages')->where('id', $revision->package_id)->first();
            if (!$package) {
                throw new \Exception('Associated package not found.', 500);
            }

            // Tentukan Path Folder
            $packageIdsArray = [
                'customer_id' => $package->customer_id,
                'model_id' => $package->model_id,
                'product_id' => $package->product_id,
                'doctype_group_id' => $package->doctype_group_id,
                'doctype_subcategory_id' => $package->doctype_subcategory_id,
                'part_group_id' => $package->part_group_id,
            ];
            $metaBase = $this->buildMetaBaseFromDb($revision, $packageIdsArray);
            $revFolder = PathBuilder::revisionFolderName($metaBase);
            $root = PathBuilder::root($metaBase);
            $revisionPath = $root . '/' . $revFolder;

            $packageInfo = DB::table('doc_packages as dp')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
                ->where('dp.id', $revision->package_id)
                ->where('dp.is_delete', 0)   // <---
    ->where('p.is_delete', 0)
                ->select(
                    'c.code as customer',
                    'm.name as model',
                    'p.part_no',
                    'dtg.name as doc_type',
                    'pg.code_part_group'
                )
                ->first();

            $labelName = null;
            if ($revision->revision_label_id) {
                $labelName = DB::table('customer_revision_labels')
                    ->where('id', $revision->revision_label_id)
                    ->value('label');
            }

            $metaLogData = [
                // Snapshot Data Utama
                'part_no'         => $packageInfo->part_no,
                'customer_code'   => $packageInfo->customer,
                'model_name'      => $packageInfo->model,
                'doc_type'        => $packageInfo->doc_type,
                'part_group_code' => $packageInfo->code_part_group ?? '-',
                
                // Detail Revisi
                'package_id'      => $revision->package_id,
                'revision_no'     => $revision->revision_no,
                'ecn_no'          => $revision->ecn_no,
                'revision_label'  => $labelName,
                'revision_status' => $revision->revision_status,
                'deleted_at'     => now()->toDateTimeString(),
            ];

            // Hapus file
            DB::table('doc_package_revision_files')->where('revision_id', $revisionId)->delete();
            // Hapus revisi
            DB::table('doc_package_revisions')->where('id', $revisionId)->delete();

            $remainingRevisions = DB::table('doc_package_revisions')
                ->where('package_id', $revision->package_id)
                ->count();

            if ($remainingRevisions === 0) {
    DB::table('doc_packages')
        ->where('id', $revision->package_id)
        ->update([
            'is_delete' => 1,
            'updated_at' => now(),
        ]);

    Log::info("Package soft-deleted (last revision was draft)", [
        'package_id' => $revision->package_id
    ]);
}


            if (Storage::disk($this->disk)->exists($revisionPath)) {
                Storage::disk($this->disk)->deleteDirectory($revisionPath);
            }

            ActivityLog::create([
                'user_id' => $this->getAuthUserInt(),
                'activity_code' => 'DELETE_PACKAGE',
                'scope_type' => 'revision',
                'scope_id' => $revision->package_id,
                'revision_id' => $revisionId,
                'meta' => $metaLogData,
            ]);


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Draft revision and all associated files have been deleted.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete draft revision', [
                'error' => $e->getMessage(),
                'revision_id' => $revisionId
            ]);

            $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
