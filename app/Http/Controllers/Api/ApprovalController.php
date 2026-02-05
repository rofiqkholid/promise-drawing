<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Exports\ApprovalSummaryExport;
use App\Models\Customers;
use App\Models\Models;
use App\Models\DoctypeGroups;
use App\Models\Role;
use App\Models\DoctypeSubcategory;
use App\Models\DocPackageRevisionFile;
use App\Models\DocTypeSubCategories;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\StampFormat;
use App\Models\FileExtensions;
use Illuminate\Support\Facades\Mail;
use App\Mail\RevisionApprovedNotification;
use App\Mail\DeptShareNotification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;



class ApprovalController extends Controller
{
    /**
     * Membersihkan karakter kontrol non-printable (ASCII 0-31) 
     * yang dapat merusak struktur string JSON di JavaScript.
     */
    private function sanitizeForJson(array $data): array
    {
        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                // Menghapus karakter kontrol ASCII 0-31 dan 127
                // Karakter standar seperti spasi, _, -, . TETAP AMAN
                $item = preg_replace('/[\x00-\x1F\x7F]/', '', $item);
            }
        });
        return $data;
    }
    /**
     * Menentukan level approval user.
     */
   private function getApprovalLevel(User $user): int
{
    // Ambil semua role_id milik user dari tabel pivot user_roles
    $userRoleIds = DB::table('user_roles')
        ->where('user_id', $user->id)
        ->pluck('role_id')
        ->toArray();

    // Level 3: Approver 3 atau ICT (Superuser)
    if (in_array(Role::APV_3, $userRoleIds) || in_array(Role::ICT, $userRoleIds)) {
        return 3;
    }

    // Level 2: Approver 2
    if (in_array(Role::APV_2, $userRoleIds)) {
        return 2;
    }

    // Level 1: Approver 1
    if (in_array(Role::APV_1, $userRoleIds)) {
        return 1;
    }

    return 0; // Bukan approver
}

    public function kpi(Request $req)
    {
        $q = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->where('dpr.revision_status', '<>', 'draft')
            ->where('dp.is_delete', 0)
            ->where('p.is_delete', 0);

        // ========================
        // JOIN APPROVAL (WAJIB DULU)
        // ========================
        $q->leftJoin('package_approvals as pa', 'pa.revision_id', '=', 'dpr.id');

        // ========================
        // FILTERS
        // ========================
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

        if ($req->filled('status') && $req->status !== 'All') {
            if ($req->status === 'Approved') {
                $q->where('pa.decision', 'approved');
            } elseif ($req->status === 'Rejected') {
                $q->where('pa.decision', 'rejected');
            } elseif ($req->status === 'Waiting') {
                $q->where('pa.decision', 'pending');
            }
        }

        if ($req->filled('project_status')) {
            $ps = $req->project_status;
            if (strcasecmp($ps, 'All') !== 0) {
                $q->where('ps.name', $ps);
            }
        }

        // ========================
        // KPI AGGREGATION
        // ========================
        $row = $q->selectRaw("
        COUNT(*) AS total,

        SUM(
            CASE
                WHEN pa.decision = 'pending'
                     AND COALESCE(pa.lvl1, 0) = 0
                THEN 1 ELSE 0
            END
        ) AS waiting_l1,

        SUM(
            CASE
                WHEN pa.decision = 'pending'
                     AND COALESCE(pa.lvl1, 0) = 1
                     AND COALESCE(pa.lvl2, 0) = 0
                THEN 1 ELSE 0
            END
        ) AS waiting_l2,
         SUM(
    CASE
        WHEN pa.decision = 'pending'
             AND COALESCE(pa.lvl1, 0) = 1
             AND COALESCE(pa.lvl2, 0) = 1
             AND COALESCE(pa.lvl3, 0) = 0
        THEN 1 ELSE 0
    END
) AS waiting_l3,

        SUM(CASE WHEN pa.decision = 'approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN pa.decision = 'rejected' THEN 1 ELSE 0 END) AS rejected
    ")->first();

        // ========================
        // RESPONSE
        // ========================
        $total     = (int) ($row->total ?? 0);
        $waitingL1 = (int) ($row->waiting_l1 ?? 0);
        $waitingL2 = (int) ($row->waiting_l2 ?? 0);
        $waitingL3 = (int) ($row->waiting_l3 ?? 0);
        $approved  = (int) ($row->approved ?? 0);
        $rejected  = (int) ($row->rejected ?? 0);

        return response()->json([
            'cards' => [
                'total'      => $total,
                'waiting_l1' => $waitingL1,
                'waiting_l2' => $waitingL2,
                'waiting_l3' => $waitingL3,
                'approved'   => $approved,
                'rejected'   => $rejected,
            ],
            'metrics' => [
                'approval_rate'  => $total ? round($approved * 100 / $total, 2) : 0.0,
                'rejection_rate' => $total ? round($rejected * 100 / $total, 2) : 0.0,
                'wip_rate'       => $total ? round(($waitingL1 + $waitingL2 + $waitingL3) * 100 / $total, 2) : 0.0,
            ],
        ]);
    }


    public function filters(Request $request): JsonResponse
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
                        ->when($customerCode && $customerCode !== 'All', function ($x) use ($customerCode) {
                            $x->where('c.code', $customerCode);
                        })
                        ->when($q, function ($x) use ($q) {
                            $x->where('m.name', 'like', "%{$q}%");
                        });
                    $namesQuery = $builder
                        ->selectRaw('m.name AS id, m.name AS text')
                        ->distinct()
                        ->orderBy('m.name');
                    $total = (clone $namesQuery)->count();
                    $items = $namesQuery->forPage($page, $perPage)->get();
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

                case 'status':
                    $all = collect([
                        ['id' => 'Waiting L1', 'text' => 'Waiting L1'],
                        ['id' => 'Waiting L2', 'text' => 'Waiting L2'],
                        ['id' => 'Waiting L3', 'text' => 'Waiting L3'],
                        ['id' => 'Approved',   'text' => 'Approved'],
                        ['id' => 'Rejected',   'text' => 'Rejected'],
                    ]);

                    $filtered = $q
                        ? $all->filter(
                            fn($r) =>
                            str_contains(strtolower($r['text']), strtolower($q))
                        )
                        : $all;

                    $total = $filtered->count();
                    $items = $filtered
                        ->slice(($page - 1) * $perPage, $perPage)
                        ->values();
                    break;


                case 'project_status':
                    $builder = DB::table('project_status as ps')
                        ->selectRaw('ps.name AS id, ps.name AS text')
                        ->when($q, fn($x) => $x->where('ps.name', 'like', "%{$q}%"))
                        ->orderBy('ps.name');

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

        // ====== MODE LAMA (non-Select2) ======
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
            'statuses' => collect([
                ['name' => 'Waiting L1'],
                ['name' => 'Waiting L2'],
                ['name' => 'Waiting L3'],
                ['name' => 'Approved'],
                ['name' => 'Rejected'],
            ]),

        ]);
    }

    public function listApprovals(Request $request): JsonResponse
    {
        $start       = (int) $request->get('start', 0);
        $length      = (int) $request->get('length', 10);
        $searchValue = $request->get('search')['value'] ?? '';

        $orderColumnIndex = (int) ($request->get('order')[0]['column'] ?? 0);
        $orderDir         = $request->get('order')[0]['dir'] ?? 'desc';
        $orderColumnName  = $request->get('columns')[$orderColumnIndex]['name'] ?? 'dpr.created_at';

        $latestPa = DB::table('package_approvals as pa')
            ->select(
                'pa.id',
                'pa.revision_id',
                'pa.requested_at',
                'pa.decided_at',
                'pa.decision',
                'pa.decided_by',
                'pa.lvl1',
                'pa.lvl2',
                'pa.lvl3'
            )
            ->selectRaw("
        ROW_NUMBER() OVER (
          PARTITION BY pa.revision_id
          ORDER BY COALESCE(pa.decided_at, pa.requested_at) DESC, pa.id DESC
        ) as rn
    ");

        $query = DB::table('doc_package_revisions as dpr')
            ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft')

            ->where('dp.is_delete', 0)
            ->where('p.is_delete', 0);

        $recordsTotal = (clone $query)->count();

        // Filters
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
        if ($request->filled('status') && $request->status !== 'All') {

            switch ($request->status) {

                case 'Waiting L1':
                    $query->where('pa.decision', 'pending')
                        ->whereRaw('COALESCE(pa.lvl1,0) = 0');
                    break;

                case 'Waiting L2':
                    $query->where('pa.decision', 'pending')
                        ->whereRaw('COALESCE(pa.lvl1,0) = 1')
                        ->whereRaw('COALESCE(pa.lvl2,0) = 0');
                    break;
                case 'Waiting L3':
                    $query->where('pa.decision', 'pending')
                        ->whereRaw('COALESCE(pa.lvl1,0) = 1')
                        ->whereRaw('COALESCE(pa.lvl2,0) = 1')
                        ->whereRaw('COALESCE(pa.lvl3,0) = 0');
                    break;

                case 'Approved':
                    $query->where('pa.decision', 'approved');
                    break;

                case 'Rejected':
                    $query->where('pa.decision', 'rejected');
                    break;
            }
        }



        if ($request->filled('project_status')) {
            $ps = $request->project_status;
            if (strcasecmp($ps, 'All') !== 0) {
                $query->where('ps.name', $ps);
            }
        }

        if ($searchValue !== '') {
            $query->where(function ($q) use ($searchValue) {
                $q->where('c.code', 'like', "%{$searchValue}%")
                    ->orWhere('m.name', 'like', "%{$searchValue}%")
                    ->orWhere('p.part_no', 'like', "%{$searchValue}%")
                    ->orWhere('dsc.name', 'like', "%{$searchValue}%")
                    ->orWhere('pg.code_part_group', 'like', "%{$searchValue}%")
                    ->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%")
                    ->orWhereRaw("
                CONCAT(
                  c.code,' ',
                  m.name,' ',
                  dtg.name,' ',
                  COALESCE(dsc.name,''),' ',
                  COALESCE(pg.code_part_group,''),' ',
                  COALESCE(p.part_no,''),' ',
                  COALESCE(dpr.ecn_no,''),' ',
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
            'pg.code_part_group as part_group',
            'dpr.ecn_no as ecn_no',
            'dpr.revision_no as revision',
            'dpr.receipt_date as receipt_date',
            DB::raw("
CASE
    WHEN pa.decision = 'pending' AND COALESCE(pa.lvl1,0) = 0
        THEN 'Waiting L1'
    WHEN pa.decision = 'pending'
         AND COALESCE(pa.lvl1,0) = 1
         AND COALESCE(pa.lvl2,0) = 0
        THEN 'Waiting L2'
    WHEN pa.decision = 'pending'
         AND COALESCE(pa.lvl1,0) = 1
         AND COALESCE(pa.lvl2,0) = 1
         AND COALESCE(pa.lvl3,0) = 0
        THEN 'Waiting L3'
    WHEN pa.decision = 'approved'
        THEN 'Approved'
    WHEN pa.decision = 'rejected'
        THEN 'Rejected'
    ELSE 'Waiting'
END as status
"),
            'pa.requested_at as request_date',
            'pa.decided_at   as decision_date'
        );

        $orderWhitelist = [
            'dpr.created_at',
            'dpr.updated_at',
            'pa.requested_at',
            'pa.decided_at',
            'dpr.receipt_date',
            'dpr.revision_status',
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
            'pg.code_part_group',
            'dpr.ecn_no',
        ];

        $orderBy        = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'pa.requested_at';
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
            ->leftJoin('users as ou', 'ou.id', '=', 'dpr.obsolete_by')
            ->leftJoin('departments as od', 'od.id', '=', 'ou.id_dept')
            ->where('dpr.id', $revisionId)

            ->first([
                'dpr.*',
                'ou.name as obsolete_name',
                'od.code as obsolete_dept',
            ]);


        if (!$revision) {
            abort(404, 'Approval request not found.');
        }

        // ================================
        // AMBIL STATUS APPROVAL TERAKHIR
        // ================================
        $approval = DB::table('package_approvals')
            ->where('revision_id', $revisionId)
            ->orderByRaw('COALESCE(decided_at, requested_at) DESC')
            ->first();

        $approvalStatus = 'Waiting';

        if ($approval) {
            if ($approval->decision === 'approved') {
                $approvalStatus = 'Approved';
            } elseif ($approval->decision === 'rejected') {
                $approvalStatus = 'Rejected';
            } elseif ($approval->decision === 'pending') {

                if ((int) $approval->lvl1 === 0) {
                    $approvalStatus = 'Waiting L1';
                } elseif (
                    (int) $approval->lvl1 === 1 &&
                    (int) $approval->lvl2 === 0
                ) {
                    $approvalStatus = 'Waiting L2';
                } elseif (
                    (int) $approval->lvl1 === 1 &&
                    (int) $approval->lvl2 === 1 &&
                    (int) $approval->lvl3 === 0
                ) {
                    $approvalStatus = 'Waiting L3';
                }
            }
        }


        if (!$approval) {
            $approvalStatus = ucfirst($revision->revision_status ?? 'Waiting');
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
            ->where('pa.revision_id', $revisionId)
            ->orderByRaw('COALESCE(pa.decided_at, pa.requested_at) DESC')
            ->first([
                'u.name as approver_name',
                'd.code as dept_name'
            ]);

        $obsoleteStampInfo = [
            'date_raw'  => $obsoleteDate?->toDateString(),
            'date_text' => $obsoleteDate
                ? $this->formatObsoleteDate($obsoleteDate)
                : null,
            'name' => $revision->obsolete_name
                ?? optional($lastApproval)->approver_name
                ?? '-',

            'dept' => $revision->obsolete_dept
                ?? optional($lastApproval)->dept_name
                ?? '-',
        ];

        // --- /Tambahan ---

        // 3. Ambil info package + uploader
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
            ->leftJoin('users as u', 'u.id', '=', 'dp.created_by')
            ->leftJoin('departments as dept', 'u.id_dept', '=', 'dept.id')
            ->where('dp.id', $revision->package_id)
            ->where('dp.is_delete', 0)
            ->where('p.is_delete', 0)
            ->select(
                'c.code as customer',
                'm.name as model',
                'p.part_no',
                'dp.created_at',
                'u.name as uploader_name',
                'dtg.name as doc_type',
                'dsc.name as category',
                'pg.code_part_group as part_group',
                'dept.is_eng'
            )
            ->first();


        if (!$package) {
            abort(404, 'Package not found.');
        }


        $fileRows = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->select(
                'id',
                'filename as name',
                'category',
                'storage_path',
                'file_size',
                'ori_position',
                'copy_position',
                'obslt_position'
            )
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
                        'id'            => $item->id,
                        'name'          => $item->name,
                        'url'           => $url,
                        'icon_src'      => $iconSrc,
                        'ori_position'  => $item->ori_position,
                        'copy_position' => $item->copy_position,
                        'obslt_position' => $item->obslt_position,
                        'size'          => $item->file_size,
                    ];
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
                'part_group'  => $package->part_group,
                'doc_type'    => $package->doc_type,
                'category'    => $package->category,
                'ecn_no'      => $revision->ecn_no ?? null,
                'part_no'     => $package->part_no,
                'revision'    => 'Rev-' . $revision->revision_no,
                'uploader'    => $uploaderName,
                'uploaded_at' => $uploadedAt ? $uploadedAt->format('Y-m-d H:i') : null,
            ],
            'status' => $approvalStatus,

            'files'        => $files,
            'activityLogs' => $logs,

            'is_finish'    => (int) ($revision->is_finish ?? 0),

            'note'         => $revision->note ?? null,

            'stamp'        => [
                'receipt_date' => $receiptDate?->toDateString(),
                'upload_date'  => $uploadDateRevision?->toDateString(),
                'obsolete_date' => $obsoleteDate?->toDateString(),
                'is_obsolete'  => $isObsolete,
                'obsolete_info'  => $obsoleteStampInfo,
            ],
        ];

        $detail = $this->sanitizeForJson($detail);
        $hash = encrypt($revisionId);


        $stampFormats = StampFormat::where('is_active', true)
            ->orderBy('id')
            ->get();


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

        $approvalLevel = Auth::check()
            ? $this->getApprovalLevel(Auth::user())
            : 0;


        return view('approvals.approval_detail', [
            'approvalId'    => $hash,
            'detail'        => $detail,
            'stampFormats'  => $stampFormats,
            'userDeptCode'  => $userDeptCode,
            'userName'      => $userName,
            'isEngineering' => $isEngineering,
            'approval'      => $approval,
            'approvalLevel' => $approvalLevel,
        ]);
    }

    public function updateFileStampPosition(Request $request, int $fileId): JsonResponse
    {

        $data = $request->validate([
            'ori_position'   => 'nullable|integer|min:0|max:5',
            'copy_position'  => 'nullable|integer|min:0|max:5',
            'obslt_position' => 'nullable|integer|min:0|max:5',
        ]);

        $file = DocPackageRevisionFile::find($fileId);
        if (!$file) {
            return response()->json(['message' => 'File not found.'], 404);
        }


        if (array_key_exists('ori_position', $data)) {
            $file->ori_position = $data['ori_position'];
        }
        if (array_key_exists('copy_position', $data)) {
            $file->copy_position = $data['copy_position'];
        }
        if (array_key_exists('obslt_position', $data)) {
            $file->obslt_position = $data['obslt_position'];
        }

        $file->save();

        return response()->json([
            'message' => 'Stamp positions updated.',
            'data'    => [
                'id'             => $file->id,
                'ori_position'   => $file->ori_position,
                'copy_position'  => $file->copy_position,
                'obslt_position' => $file->obslt_position,
            ],
        ]);
    }



   public function approve(Request $request, string $id)
{
    $userId = Auth::user()->id ?? 1;

    try {
        $revisionId = decrypt($id);
    } catch (DecryptException $e) {
        return response()->json(['message' => 'Invalid revision.'], 404);
    }

    $user  = Auth::user();
    $level = $this->getApprovalLevel($user);

    if ($level === 0) {
        return response()->json(['message' => 'You are not authorized to approve this revision.'], 403);
    }

    try {
        DB::beginTransaction();

        $approval = DB::table('package_approvals')
            ->where('revision_id', $revisionId)
            ->lockForUpdate()
            ->first();

        if (!$approval) {
            DB::rollBack();
            return response()->json(['message' => 'Approval data not found.'], 404);
        }

        if (in_array($approval->decision, ['approved', 'rejected'], true)) {
            DB::rollBack();
            return response()->json(['message' => 'This revision is already finalized.'], 409);
        }

        // Logic Override untuk Level 3
        if ($level === 3) {
            DB::table('package_approvals')
                ->where('id', $approval->id)
                ->update([
                    'lvl1' => 1,
                    'lvl1_decided_by' => $approval->lvl1_decided_by ?? $user->id,
                    'lvl2' => 1,
                    'lvl2_decided_by' => $approval->lvl2_decided_by ?? $user->id,
                    'lvl3' => 1,
                    'lvl3_decided_by' => $user->id,
                    'decision' => 'approved',
                    'decided_by' => $user->id,
                    'decided_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
        } 
        // Logic Normal untuk Level 1
        else if ($level === 1 && !$approval->lvl1) {
            DB::table('package_approvals')
                ->where('id', $approval->id)
                ->update([
                    'lvl1' => 1,
                    'lvl1_decided_by' => $user->id,
                    'updated_at' => Carbon::now(),
                ]);
            DB::commit();
            return response()->json(['message' => 'Approved Level 1. Waiting for Level 2.']);
        } 
        // Logic Normal untuk Level 2
        else if ($level === 2) {
            if ((int) $approval->lvl1 !== 1) {
                DB::rollBack();
                return response()->json(['message' => 'Level 1 approval is required first.'], 422);
            }
            DB::table('package_approvals')
                ->where('id', $approval->id)
                ->update([
                    'lvl2' => 1,
                    'lvl2_decided_by' => $user->id,
                    'updated_at' => Carbon::now(),
                ]);
            DB::commit();
            return response()->json(['message' => 'Approved Level 2. Waiting for Level 3.']);
        } 
        else {
            DB::rollBack();
            return response()->json(['message' => 'Waiting for previous approval levels.'], 422);
        }

        // Finalisasi Dokumen (Hanya jalan jika L3 approve atau alur normal sampai L3)
        $revision = DB::table('doc_package_revisions')->where('id', $revisionId)->lockForUpdate()->first();
        $packageId = (int) $revision->package_id;
        $package = DB::table('doc_packages')->where('id', $packageId)->lockForUpdate()->first();

        $revNo = (int) $revision->revision_no;
        $currentNo = $package->current_revision_no ?? null;
        $isOlder = !is_null($currentNo) && $revNo < (int) $currentNo;

        if (!$isOlder) {
            DB::table('doc_package_revisions')
                ->where('package_id', $packageId)
                ->where('id', '!=', $revisionId)
                ->where('revision_status', 'approved')
                ->where('is_obsolete', 0)
                ->update([
                    'is_obsolete' => 1,
                    'obsolete_at' => Carbon::now(),
                    'obsolete_by' => $userId,
                    'updated_at'  => Carbon::now(),
                ]);

            DB::table('doc_package_revisions')->where('id', $revisionId)->update([
                'revision_status' => 'approved',
                'is_obsolete' => 0,
                'updated_at'  => Carbon::now(),
            ]);

            DB::table('doc_packages')->where('id', $packageId)->update([
                'current_revision_id' => $revision->id,
                'current_revision_no' => $revision->revision_no,
                'updated_at' => Carbon::now(),
            ]);
        } else {
            DB::table('doc_package_revisions')->where('id', $revisionId)->update([
                'revision_status' => 'approved',
                'is_obsolete' => 1,
                'obsolete_at' => Carbon::now(),
                'obsolete_by' => $userId,
                'updated_at'  => Carbon::now(),
            ]);
        }

        // Logging & Email
        ActivityLog::create([
            'scope_type' => 'package',
            'scope_id' => $packageId,
            'revision_id' => $revisionId,
            'activity_code' => ActivityLog::APPROVE,
            'user_id' => $userId,
            'meta' => ['note' => 'Revision approved (Full Access Mode)', 'action_status' => 'Approved'],
        ]);

        DB::commit();
        return response()->json(['message' => 'Revision approved successfully!']);

    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to approve.', 'error' => $e->getMessage()], 500);
    }
}

    public function reject(Request $request, string $id)
{
    $userId = Auth::user()->id ?? 1;

    try {
        $revisionId = decrypt($id);
    } catch (DecryptException $e) {
        return response()->json(['message' => 'Invalid revision.'], 404);
    }

    $request->validate(['note' => 'required|string|max:500']);
    $user  = Auth::user();
    $level = $this->getApprovalLevel($user);

    $approval = DB::table('package_approvals')->where('revision_id', $revisionId)->first();

    if (!$approval) {
        return response()->json(['message' => 'Approval data not found.'], 404);
    }

    if (in_array($approval->decision, ['approved', 'rejected'], true)) {
        return response()->json(['message' => 'This revision is already finalized.'], 409);
    }

    // Validasi Level (Level 3 Bypass)
    if ($level !== 3) {
        if ($approval->decision === 'pending' && (int)$approval->lvl1 === 0 && $level !== 1) {
            return response()->json(['message' => 'Unauthorized stage for Level '.$level], 403);
        }
        if ($approval->decision === 'pending' && (int)$approval->lvl1 === 1 && (int)$approval->lvl2 === 0 && $level !== 2) {
            return response()->json(['message' => 'Unauthorized stage for Level '.$level], 403);
        }
    }

    DB::beginTransaction();
    try {
        DB::table('doc_package_revisions')->where('id', $revisionId)->update([
            'revision_status' => 'rejected',
            'updated_at' => Carbon::now(),
        ]);

        $update = [
            'decision' => 'rejected',
            'decided_by' => $userId,
            'decided_at' => Carbon::now(),
            'reason' => $request->note,
            'updated_at' => Carbon::now(),
        ];

        // Tandai siapa yang melakukan reject
        if ($level === 1) { $update['lvl1'] = 1; $update['lvl1_decided_by'] = $userId; }
        if ($level === 2) { $update['lvl2'] = 1; $update['lvl2_decided_by'] = $userId; }
        if ($level === 3) { $update['lvl3'] = 1; $update['lvl3_decided_by'] = $userId; }

        DB::table('package_approvals')->where('revision_id', $revisionId)->update($update);

        ActivityLog::create([
            'scope_type' => 'package',
            'scope_id' => $approval->revision_id,
            'revision_id' => $revisionId,
            'activity_code' => ActivityLog::REJECT,
            'user_id' => $userId,
            'meta' => ['note' => $request->note, 'reject_level' => 'L'.$level, 'action_status' => 'Rejected'],
        ]);

        DB::commit();
        return response()->json(['message' => 'Revision rejected successfully!']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Failed to reject.', 'error' => $e->getMessage()], 500);
    }
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


                $action = match (true) {
                    str_starts_with($code, 'UPLOAD') => 'uploaded',
                    str_starts_with($code, 'APPROVE') => 'approved',
                    str_starts_with($code, 'REJECT') => 'rejected',
                    str_starts_with($code, 'ROLLBACK') => 'rollbacked',
                    str_starts_with($code, 'SHARE') => 'shared',
                    str_starts_with($code, 'DOWNLOAD') => 'downloaded',
                    default => strtolower($code ?: 'info')
                };


                $rawMeta = $row->meta;
                $metaArr = [];

                if (is_array($rawMeta)) {
                    $metaArr = $rawMeta;
                } elseif (is_string($rawMeta)) {
                    $decoded = json_decode($rawMeta, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $metaArr = $decoded;
                    } else {
                        $metaArr = ['note' => $rawMeta];
                    }
                }

                return [
                    'id'      => (int) $row->id,
                    'action'  => $action,
                    'user'    => $row->user_name ?? 'System',
                    'note'    => $metaArr['note'] ?? '',
                    'time'    => optional($row->created_at)->format('Y-m-d H:i'),


                    'snapshot' => [
                        'part_no'     => $metaArr['part_no'] ?? null,
                        'revision_no' => $metaArr['revision_no'] ?? null,
                        'ecn_no'      => $metaArr['ecn_no'] ?? null,
                        'customer'    => $metaArr['customer_code'] ?? null,
                        'model'       => $metaArr['model_name'] ?? null,
                    ]
                ];
            });
    }

    public function rollback(Request $request, string $id): JsonResponse
{
    $userId = Auth::user()->id ?? 1;

    try {
        $revisionId = decrypt($id);
    } catch (DecryptException $e) {
        return response()->json(['message' => 'Invalid revision.'], 404);
    }

    $approval = DB::table('package_approvals')->where('revision_id', $revisionId)->first();
    if (!$approval) return response()->json(['message' => 'Record not found.'], 404);

    $user  = Auth::user();
    $level = $this->getApprovalLevel($user);

    // Validasi Akses Rollback (Level 3 Bypass)
    if ($level !== 3) {
        if ($approval->decision === 'pending' && (int)$approval->lvl1 === 1 && (int)$approval->lvl2 === 0) {
            if ($level !== 1) return response()->json(['message' => 'Only L1 can rollback L1 approval.'], 403);
        }
        elseif ($approval->decision === 'pending' && (int)$approval->lvl1 === 1 && (int)$approval->lvl2 === 1 && (int)$approval->lvl3 === 0) {
            if ($level !== 2) return response()->json(['message' => 'Only L2 can rollback L2 approval.'], 403);
        }
        else {
            return response()->json(['message' => 'Unauthorized rollback or revision already finalized.'], 403);
        }
    }

    DB::beginTransaction();
    try {
        $revision = DB::table('doc_package_revisions')->where('id', $revisionId)->lockForUpdate()->first();
        $packageId = (int) $revision->package_id;
        $wasActiveApproved = ($revision->revision_status === 'approved' && (int)$revision->is_obsolete === 0);

        // Kembalikan status revisi ke pending
        DB::table('doc_package_revisions')->where('id', $revisionId)->update([
            'revision_status' => 'pending',
            'is_obsolete' => 1,
            'obsolete_at' => Carbon::now(),
            'obsolete_by' => $userId,
            'updated_at'  => Carbon::now(),
        ]);

        // Jika yang di-rollback adalah dokumen yang sedang aktif (Approved), cari versi sebelumnya
        if ($wasActiveApproved) {
            $prev = DB::table('doc_package_revisions as r')
                ->join('package_approvals as pa', 'pa.revision_id', '=', 'r.id')
                ->where('r.package_id', $packageId)
                ->where('r.id', '<>', $revisionId)
                ->where('r.revision_status', 'approved')
                ->orderByDesc('pa.decided_at')
                ->first(['r.id', 'r.revision_no']);

            if ($prev) {
                DB::table('doc_package_revisions')->where('id', $prev->id)->update(['is_obsolete' => 0, 'obsolete_at' => null]);
                DB::table('doc_packages')->where('id', $packageId)->update(['current_revision_id' => $prev->id, 'current_revision_no' => $prev->revision_no]);
            } else {
                DB::table('doc_packages')->where('id', $packageId)->update(['current_revision_id' => null, 'current_revision_no' => 0]);
            }
        }

        // Reset data di tabel package_approvals
        DB::table('package_approvals')->where('revision_id', $revisionId)->update([
            'decision' => 'pending',
            'decided_by' => null,
            'decided_at' => null,
            'lvl1' => 0, 'lvl1_decided_by' => null,
            'lvl2' => 0, 'lvl2_decided_by' => null,
            'lvl3' => 0, 'lvl3_decided_by' => null,
            'reason' => null,
            'updated_at' => Carbon::now(),
        ]);

        ActivityLog::create([
            'scope_type' => 'package',
            'scope_id' => $packageId,
            'revision_id' => $revisionId,
            'activity_code' => 'ROLLBACK',
            'user_id' => $userId,
            'meta' => ['note' => 'Rollback by Level '.$level],
        ]);

        DB::commit();
        return response()->json(['message' => 'Rollback successful. Status reset to Waiting L1.']);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json(['message' => 'Rollback failed.', 'error' => $e->getMessage()], 500);
    }
}

    public function share(Request $request): JsonResponse
    {
        $currentUser = Auth::user();

        $request->validate([
            'revision_id' => ['required'],
            'note'        => ['required', 'string', 'max:500'],
        ]);

        $inputRevisionId = $request->input('revision_id');
        $revisionId = null;

        if (is_numeric($inputRevisionId)) {
            $revisionId = (int) $inputRevisionId;
        } else {
            try {
                $revisionId = decrypt($inputRevisionId);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid revision ID.'], 404);
            }
        }

        DB::beginTransaction();
        try {

            $revision = DB::table('doc_package_revisions as dpr')
                ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
                ->join('customers as c', 'dp.customer_id', '=', 'c.id')
                ->join('models as m', 'dp.model_id', '=', 'm.id')
                ->join('products as p', 'dp.product_id', '=', 'p.id')
                ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
                ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
                ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
                ->where('dpr.id', $revisionId)
                ->first([
                    'dpr.id as revision_id',
                    'dpr.package_id',
                    'dpr.revision_no',
                    'dpr.revision_status',
                    'dpr.ecn_no',
                    'c.code as customer',
                    'm.name as model',
                    'p.part_no',
                    'dtg.name as doc_type',
                    'dsc.name as category',
                    'pg.code_part_group'
                ]);

            if (!$revision) {
                return response()->json(['message' => 'Revision not found.'], 404);
            }


            $deptRows = DB::table('departments')
                ->whereIn('code', ['PURCHASING', 'PUD'])
                ->get(['id', 'code']);

            if ($deptRows->isEmpty()) {
                return response()->json([
                    'message' => 'Departments (Purchasing/PUD) are not configured.',
                ], 422);
            }

            $deptIds  = $deptRows->pluck('id')->all();
            $deptCode = $deptRows->pluck('code')->implode(', ');
            $shareToDeptStr = '|' . implode('|', $deptIds) . '|';


            DB::table('doc_package_revisions')
                ->where('id', $revisionId)
                ->update([
                    'share_to_dept' => $shareToDeptStr,
                    'share_dept_at' => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);


            $recipients = User::query()
                ->whereIn('id_dept', $deptIds)
                ->whereNotNull('email')
                ->get();

            if ($recipients->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Share failed: No active users found in the target departments.',
                ], 422);
            }


            $recipientNames = $recipients->map(function ($u) {
                return "{$u->name} ({$u->email})";
            })->implode(', ');


            $shareData = [
                'customer'    => $revision->customer,
                'model'       => $revision->model,
                'part_no'     => $revision->part_no,
                'doc_type'    => $revision->doc_type,
                'category'    => $revision->category,
                'revision_no' => $revision->revision_no,
                'note'        => $request->input('note'),
                'shared_by'   => $currentUser->name ?? 'System',
                'shared_at'   => now()->format('Y-m-d H:i'),
                'dept_codes'  => $deptCode,
                'app_url'     => route('share.detail', [
                    'id'          => encrypt($revisionId),
                ]),
            ];

            foreach ($recipients as $target) {
                try {
                    Mail::to($target->email)->send(
                        new DeptShareNotification($target, $shareData)
                    );
                } catch (\Throwable $e) {
                    Log::error("Mail failed to {$target->email}: " . $e->getMessage());
                }
            }


            ActivityLog::create([
                'scope_type'    => 'package',
                'scope_id'      => $revision->package_id,
                'revision_id'   => $revisionId,
                'activity_code' => 'SHARE_INTERNAL',
                'user_id'       => $currentUser->id,
                'meta'          => [

                    'shared_by'       => $currentUser->name,
                    'sender_email'    => $currentUser->email,


                    'note'            => $request->input('note'),
                    'shared_to_dept'  => $deptCode,
                    'recipients'      => $recipientNames,
                    'recipient_count' => $recipients->count(),


                    'part_no'         => $revision->part_no,
                    'customer_code'   => $revision->customer,
                    'model_name'      => $revision->model,
                    'doc_type'        => $revision->doc_type,
                    'part_group_code' => $revision->code_part_group ?? '-',
                    'revision_no'     => $revision->revision_no,
                    'ecn_no'          => $revision->ecn_no,
                ],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Revision successfully shared to Purchasing/PUD departments.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Share Internal Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'System error occurred while sharing.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

 public function exportSummary(Request $request)
{
    // =========================================================================
    // 1. Ambil status approval terbaru (CTE/Subquery optimization)
    // =========================================================================
    // Menggunakan Window Function untuk mengambil approval terakhir per revisi
    $latestPa = DB::table('package_approvals as pa')
        ->select('pa.id', 'pa.revision_id', 'pa.requested_at', 'pa.decided_at', 'pa.decision')
        ->selectRaw("ROW_NUMBER() OVER (PARTITION BY pa.revision_id ORDER BY COALESCE(pa.decided_at, pa.requested_at) DESC, pa.id DESC) as rn");

    // =========================================================================
    // 2. Query Utama dengan Self-Join Products
    // =========================================================================
    $query = DB::table('doc_package_revisions as dpr')
        ->join('doc_packages as dp', 'dpr.package_id', '=', 'dp.id')
        ->join('customers as c', 'dp.customer_id', '=', 'c.id')
        ->join('models as m', 'dp.model_id', '=', 'm.id')
        
        // [LOGIKA PART LINKED / GROUP ID]
        // ---------------------------------------------------------------------
        // STEP A: Join ke Product REFERENSI (yang tersimpan di tabel transaksi dp)
        // Kita beri alias 'p_ref' sebagai jembatan untuk mendapatkan group_id
        ->join('products as p_ref', 'dp.product_id', '=', 'p_ref.id')

        // STEP B: Join ke Product TAMPIL (yang akan di-select)
        // Cari semua product 'p' yang memiliki group_id sama dengan 'p_ref'
        ->join('products as p', function ($join) {
            $join->on(function($q) {
                // Kondisi 1: Jika p_ref punya group_id valid, cari temannya
                $q->on('p.group_id', '=', 'p_ref.group_id')
                  ->whereNotNull('p_ref.group_id')
                  ->where('p_ref.group_id', '<>', ''); // Handle string kosong jika ada
            })
            // Kondisi 2: (OR) Jika tidak punya group, atau untuk mencakup dirinya sendiri
            ->orOn('p.id', '=', 'p_ref.id');
        })
        // ---------------------------------------------------------------------

        ->join('doctype_groups as dtg', 'dp.doctype_group_id', '=', 'dtg.id')
        ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
        ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
        ->leftJoin('part_groups as pg', 'dp.part_group_id', '=', 'pg.id')
        
        // Join ke subquery approval terakhir
        ->leftJoinSub($latestPa, 'pa', function ($join) {
            $join->on('pa.revision_id', '=', 'dpr.id')->where('pa.rn', '=', 1);
        });

    // =========================================================================
    // 3. Filter Constraint (Hardcoded)
    // =========================================================================
    $query->where('dp.is_delete', 0)
          ->where('p.is_delete', 0)  // Filter product 'TAMPIL' yang tidak didelete
          ->where('p.is_count', 1)   // Filter product 'TAMPIL' yang is_count = 1
          ->where('dpr.revision_status', '<>', 'draft')
          ->whereRaw("LOWER(COALESCE(pa.decision, dpr.revision_status)) = 'approved'");

    // =========================================================================
    // 4. Dynamic Filters (User Input)
    // =========================================================================
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
    if ($request->filled('project_status') && strcasecmp($request->project_status, 'All') !== 0) {
        $query->where('ps.name', $request->project_status);
    }

    // =========================================================================
    // 5. Select & Execution
    // =========================================================================
    // PENTING: Ambil part_no dan part_name dari alias 'p' (hasil expand), bukan 'p_ref'
    $rowsDb = $query->select([
        'c.code as customer', 
        'm.name as model', 
        'p.part_no',         // <-- Diambil dari product hasil expand group
        'p.part_name',       // <-- Diambil dari product hasil expand group
        'dtg.name as doctype', 
        'dsc.name as category', 
        'pg.code_part_group as part_group',
        'dpr.created_at as upload_date', 
        'dpr.receipt_date', 
        'dpr.ecn_no', 
        'dpr.revision_no', 
        'dpr.is_finish'
    ])
    // Urutkan berdasarkan Part No agar yang satu grup berkumpul
    ->orderBy('c.code')
    ->orderBy('m.name')
    ->orderBy('p.part_no') 
    ->orderBy('dpr.revision_no', 'asc')
    ->get();

    // =========================================================================
    // 6. Mapping Data (Logic Penandaan Revisi Terakhir)
    // =========================================================================
    $highestRevisionMap = [];
    foreach ($rowsDb as $index => $r) {
        // Buat unique key untuk menentukan revisi tertinggi per dokumen unik
        $key = $r->part_no . '|' . $r->doctype . '|' . $r->category;
        $highestRevisionMap[$key] = $index;
    }

    $rows = [];
    foreach ($rowsDb as $index => $r) {
        $key = $r->part_no . '|' . $r->doctype . '|' . $r->category;
        
        $isFinishStatus = '0';
        // Logic: Jika index ini adalah index terakhir untuk key tersebut, ambil status finish aslinya
        if ($index === $highestRevisionMap[$key]) {
            $isFinishStatus = ($r->is_finish == 1) ? '1' : '0';
        }

        $rows[] = [
            'customer'     => $r->customer,
            'model'        => $r->model,
            'part_no'      => $r->part_no,
            'part_name'    => $r->part_name,
            'doctype'      => $r->doctype,
            'category'     => $r->category,
            'ecn_no'       => $r->ecn_no ?? '-',
            'revision_no'  => $r->revision_no ?? '0',
            'part_group'   => $r->part_group ?? '-',
            'receipt_date' => $r->receipt_date ? Carbon::parse($r->receipt_date)->format('Y-m-d') : '-',
            'upload_date'  => $r->upload_date ? Carbon::parse($r->upload_date)->format('Y-m-d') : '-',
            'is_finish'    => $isFinishStatus,
        ];
    }

    // =========================================================================
    // 7. Export Excel
    // =========================================================================
    return Excel::download(
        new ApprovalSummaryExport($rows, now()->format('Y-m-d H:i')), 
        'approval-summary-' . now()->format('Ymd_His') . '.xlsx'
    );
}





    private function formatObsoleteDate(Carbon $date): string
    {
        $month = $date->format('M');
        $day   = (int) $date->format('j');
        $year  = $date->format('Y');

        if (in_array($day % 100, [11, 12, 13], true)) {
            $suffix = 'th';
        } else {
            $last = $day % 10;
            $suffix = match ($last) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return sprintf('%s.%d%s %s', $month, $day, $suffix, $year);
    }

    private function getFeasibilityStatusId(): ?int
    {
        return DB::table('project_status')
            ->where('name', 'Feasibility Study')
            ->value('id');
    }

    private function isFeasibilityPackage(int $packageId): bool
    {
        $feasId = $this->getFeasibilityStatusId();
        if (!$feasId) {
            return false;
        }

        $statusId = DB::table('doc_packages as dp')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->where('dp.id', $packageId)
            ->value('m.status_id');

        return (int) $statusId === (int) $feasId;
    }



    private function getNotificationUsersForPackage(int $packageId)
    {
        $isFeasibility = $this->isFeasibilityPackage($packageId);


        $query = User::select('users.*')
            ->distinct()
            ->leftJoin('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->whereNotNull('users.email')
            ->where(function ($q) {
                $q->whereNull('user_roles.role_id')
                    ->orWhere('user_roles.role_id', '!=', 5);
            });

        if ($isFeasibility) {

            $allowedRoles = ['ENG'];

            $query->whereExists(function ($q2) use ($allowedRoles) {
                $q2->select(DB::raw(1))
                    ->from('user_roles as ur2')
                    ->join('roles as r2', 'ur2.role_id', '=', 'r2.id')
                    ->whereColumn('ur2.user_id', 'users.id')
                    ->whereIn('r2.role_name', $allowedRoles);
            });
        }

        return $query->get();
    }
}
