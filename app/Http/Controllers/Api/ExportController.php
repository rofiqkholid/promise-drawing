<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportController extends Controller
{
    public function showRevisionHistory($package_id)
    {
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->where('dp.id', $package_id)
            ->select('c.code as customer_code', 'm.name as model_name', 'p.part_no')
            ->first();

        if (!$package) {
            abort(404, 'Package not found.');
        }

        return view('file_management.file_export_history', [
            'package_id' => $package_id,
            'package' => $package,
        ]);
    }

    public function listRevisionHistory(Request $request, $package_id): JsonResponse
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $orderColumnIndex = $request->get('order')[0]['column'] ?? 0;
        $orderColumnName = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.created_at';
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        $query = DB::table('doc_package_revisions as dpr')
            ->leftJoin('customer_revision_labels as crl', 'dpr.revision_label_id', '=', 'crl.id')
            ->where('dpr.package_id', $package_id)
            ->where('dpr.revision_status', '=', 'approved');

        $recordsTotal = $query->count();
        $recordsFiltered = $recordsTotal;

        $data = $query->select(
                'dpr.id',
                'dpr.revision_no',
                'dpr.ecn_no',
                'dpr.created_at as release_date',
                'crl.label as revision_label_name'
            )
            ->orderBy($orderColumnName, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function kpi(Request $req)
    {
        $q = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('doc_package_revisions as dpr')
                    ->whereColumn('dpr.package_id', 'dp.id')
                    ->where('dpr.revision_status', 'approved');
            });

        if ($req->filled('customer') && $req->customer !== 'All') {
            $q->where('c.code', $req->customer);
        }
        if ($req->filled('model') && $req->model !== 'All') {
            $q->where('m.name', $req->model);
        }
        if ($req->filled('doc_type') && $req->doc_type !== 'All') {
            $q->where('dtg.name', $req->doc_type);
        }
        if ($req->filled('category') && $req->category !== 'All') {
            $q->where('dsc.name', $req->category);
        }

        $total = $q->count();

        return response()->json([
            'cards' => [
                'total' => $total,
                'approved' => $total,
                'waiting' => 0,
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

            // dependent params
            $customerCode = $request->get('customer_code'); // untuk model
            $docTypeName  = $request->get('doc_type');      // untuk category

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

        // resolve doc_type (by name) -> id
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
        $orderColumnName = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dp.id';
        $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

        $query = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('doc_package_revisions as dpr')
                    ->whereColumn('dpr.package_id', 'dp.id')
                    ->where('dpr.revision_status', 'approved');
            });

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
                    ->orWhere('dtg.name', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = $query->count();

        $data = $query->select(
            'dp.id as package_id',
            'c.code as customer',
            'm.name as model',
            'dtg.name as doc_type',
            'dsc.name as category',
            'p.part_no',

            DB::raw("(SELECT TOP 1 dpr.revision_no
                      FROM doc_package_revisions as dpr
                      WHERE dpr.package_id = dp.id
                        AND dpr.revision_status = 'approved'
                      ORDER BY dpr.created_at DESC, dpr.id DESC) as latest_revision")
        )
            ->orderBy($orderColumnName, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            "draw" => intval($request->get('draw')),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data
        ]);
    }

    public function showDetail($id)
    {
        // $id = revision_id
        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $id)
            ->where('dpr.revision_status', '=', 'approved') // Only approved revisions
            ->first();

        if (!$revision) {
            abort(404, 'Exportable file not found or not approved.');
        }

        // Metadata Paket
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->where('dp.id', $revision->package_id)
            ->select('c.code as customer', 'm.name as model', 'p.part_no')
            ->first();

        $files = DB::table('doc_package_revision_files')
            ->where('revision_id', $id)
            ->select('id', 'filename as name', 'category', 'storage_path')
            ->get()
            ->groupBy('category')
            ->map(function ($items) {
                return $items->map(function ($item) {
                    $url = URL::signedRoute('preview.file', ['id' => $item->id]);
                    return ['name' => $item->name, 'url' => $url, 'file_id' => $item->id]; // Added file_id for download
                });
            })
            ->mapWithKeys(fn($items, $key) => [strtolower($key) => $items]);

        $detail = [
            'metadata' => [
                'customer' => $package->customer,
                'model'    => $package->model,
                'part_no'  => $package->part_no,
                'revision' => 'Rev-' . $revision->revision_no,
            ],
            'files'        => $files,
        ];

        return view('file_management.file_export_detail', [
            'exportId' => $id,
            'detail'     => $detail,
            'package_id' => $revision->package_id
        ]);
    }

    public function downloadFile($file_id)
    {
        $file = DocPackageRevisionFile::find($file_id);

        if (!$file) {
            abort(404, 'File not found.');
        }

        // Ensure the revision is approved before allowing download
        $revision = DB::table('doc_package_revisions')
            ->where('id', $file->revision_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            abort(403, 'Access denied. File is not part of an approved revision.');
        }

        $path = Storage::disk('local')->path($file->storage_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on server storage.');
        }

        return response()->download($path, $file->filename);
    }

    public function downloadPackage($revision_id)
    {
        $revision = DB::table('doc_package_revisions')
            ->where('id', $revision_id)
            ->where('revision_status', '=', 'approved')
            ->first();

        if (!$revision) {
            abort(404, 'Package not found or not approved.');
        }

        $files = DocPackageRevisionFile::where('revision_id', $revision_id)->get();

        if ($files->isEmpty()) {
            abort(404, 'No files found for this package revision.');
        }

        $zipFileName = 'package_rev_' . $revision->revision_no . '_' . now()->format('Ymd_His') . '.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName); // Store in public storage for easy access

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $filePath = Storage::disk('local')->path($file->storage_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $file->filename);
                }
            }
            $zip->close();
        } else {
            abort(500, 'Could not create zip file.');
        }

        // Return the zip file for download
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
