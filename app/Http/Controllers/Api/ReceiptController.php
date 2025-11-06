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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;

class ReceiptController extends Controller
{
    public function receiptList(Request $request): JsonResponse
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
                'pa.decided_by'
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
            ->leftJoin('doctype_subcategories as dsc', 'dp.doctype_subcategory_id', '=', 'dsc.id')
            ->leftJoinSub($latestPa, 'pa', function ($join) {
                $join->on('pa.revision_id', '=', 'dpr.id')
                    ->where('pa.rn', '=', 1);
            })
            ->where('dpr.revision_status', '<>', 'draft');

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
            // front-end kirim Waiting/Approved/Rejected
            $statusMap = [
                'Waiting'  => ['pending', 'waiting'], // waiting = data lama
                'Approved' => ['approved'],
                'Rejected' => ['rejected'],
            ];
            $vals = $statusMap[$request->status] ?? [];
            if ($vals) {
                $placeholders = implode(',', array_fill(0, count($vals), '?'));
                $query->whereRaw(
                    "COALESCE(pa.decision, dpr.revision_status) IN ($placeholders)",
                    $vals
                );
            }
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
            DB::raw("
                CASE COALESCE(pa.decision, dpr.revision_status)
                    WHEN 'pending'  THEN 'Waiting'
                    WHEN 'waiting'  THEN 'Waiting'  
                    WHEN 'approved' THEN 'Approved'
                    WHEN 'rejected' THEN 'Rejected'
                    ELSE COALESCE(pa.decision, dpr.revision_status)
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
            'dpr.revision_status',
            'c.code',
            'm.name',
            'dtg.name',
            'dsc.name',
            'p.part_no',
        ];
        $orderBy       = in_array($orderColumnName, $orderWhitelist, true) ? $orderColumnName : 'pa.requested_at';
        $orderDirection = in_array(strtolower($orderDir), ['asc', 'desc'], true) ? $orderDir : 'desc';

        $data = $query
            ->orderBy($orderBy, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $data->map(function ($row) {
            $row->hash = encrypt($row->id);  // hash terenkripsi untuk dipakai di Blade
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
        // 1. Tentukan revisionId sebenarnya
        if (ctype_digit($id)) {
            // URL lama /approval/92
            $revisionId = (int) $id;
        } else {
            // URL baru /approval/{hash}
            try {
                $revisionId = decrypt($id);
            } catch (DecryptException $e) {
                abort(404, 'Invalid approval ID.');
            }
        }

        // 2. Ambil data revisi
        $revision = DB::table('doc_package_revisions as dpr')
            ->where('dpr.id', $revisionId)
            ->first();

        if (!$revision) {
            abort(404, 'Approval request not found.');
        }

        // 3. Ambil info package + uploader
        $package = DB::table('doc_packages as dp')
            ->join('customers as c', 'dp.customer_id', '=', 'c.id')
            ->join('models as m', 'dp.model_id', '=', 'm.id')
            ->join('products as p', 'dp.product_id', '=', 'p.id')
            // SESUAIKAN kalau kolom user-nya beda (misal dp.created_user_id)
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

        // 4. File per kategori
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

        // 5. Activity logs dari tabel activity_logs
        $logs = $this->buildApprovalLogs($revision->package_id, $revisionId);

        // 6. Inject fallback log "uploaded" kalau belum ada
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

            // supaya urutannya tetap terbaru di atas
            $logs = $logs->sortByDesc('time_ts')->values();
        }

        // 7. Data yang dikirim ke Blade
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

        // selalu kirim hash baru ke Blade untuk dipakai approve/reject
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
}
