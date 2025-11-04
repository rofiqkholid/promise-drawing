<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\JsonResponse;

class ShareController extends Controller
{
    /**
     * Mengambil daftar semua role untuk ditampilkan di popup.
     * Asumsi: Anda memiliki tabel 'roles' dengan kolom 'id' dan 'name'.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoles()
    {
        try {
            // Mengambil data dari tabel 'roles' tanpa model
            // Asumsi 'is_active' atau semacamnya, sesuaikan jika perlu
            $roles = DB::table('roles')
                ->select('id', 'role_name')
                // ->where('is_active', 1) 
                ->orderBy('role_name', 'asc')
                ->get();

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('ShareController@getRoles failed: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memuat daftar role'], 500);
        }
    }

    /**
     * Meng-update kolom 'share_to' di tabel 'doc_packages'.
     *
     * @param Request $request
     * @param int $packageId ID dari doc_packages yang akan di-update
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateShare(Request $request, $packageId)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $roleId = $request->input('role_id');
        $userId = Auth::id(); // Ambil ID user yg melakukan aksi

        try {
            // 1. Cek apakah role_id valid (ada di tabel roles)
            $roleExists = DB::table('roles')->where('id', $roleId)->exists();
            if (!$roleExists) {
                return response()->json(['error' => 'Role ID tidak valid'], 400);
            }

            // 2. Lakukan update di tabel 'doc_packages' tanpa model
            $updateCount = DB::table('doc_packages')
                ->where('id', $packageId)
                ->update([
                    'share_to' => $roleId,
                    'updated_at' => now(),
                    'updated_by' => $userId // Asumsi Anda punya kolom ini
                ]);

            if ($updateCount == 0) {
                // Bisa jadi package ID-nya tidak ditemukan
                return response()->json(['error' => 'Doc Package tidak ditemukan'], 404);
            }

            // Ambil nama role untuk pesan sukses
            $roleName = DB::table('roles')->where('id', $roleId)->value('name');

            return response()->json([
                'success' => "Paket berhasil dibagikan ke role: {$roleName}"
            ]);
        } catch (\Exception $e) {
            Log::error("ShareController@updateShare failed for package $packageId: " . $e->getMessage());
            return response()->json(['error' => 'Update database gagal'], 500);
        }
    }

    public function listPackage(Request $request): JsonResponse
    {
        $draw = $request->get('draw', 1);
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search')['value'] ?? '';
        $order = $request->get('order')[0] ?? null;

        $query = DB::table('doc_package_revisions as r')
            ->leftJoin('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
            ->leftJoin('models as m', 'p.model_id', '=', 'm.id')
            ->leftJoin('products as pr', 'p.product_id', '=', 'pr.id')
            ->leftJoin('customer_revision_labels as crl', 'r.revision_label_id', '=', 'crl.id')
            ->leftJoin('doctype_groups as dg', 'p.doctype_group_id', '=', 'dg.id')
            ->leftJoin('doctype_subcategories as sc', 'p.doctype_subcategory_id', '=', 'sc.id')
            ->leftJoin('part_groups as pg', 'p.part_group_id', '=', 'pg.id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.package_no', 'like', "%{$search}%")
                    ->orWhere('c.code', 'like', "%{$search}%")
                    ->orWhere('m.name', 'like', "%{$search}%")
                    ->orWhere('pr.part_no', 'like', "%{$search}%")
                    ->orWhere('r.revision_no', 'like', "%{$search}%")
                    ->orWhere('r.ecn_no', 'like', "%{$search}%")
                    ->orWhere('crl.label', 'like', "%{$search}%")
                    ->orWhere('dg.name', 'like', "%{$search}%")
                    ->orWhere('sc.name', 'like', "%{$search}%")
                    ->orWhere('pg.code_part_group', 'like', "%{$search}%");
            });
        }

        $totalRecords = DB::table('doc_package_revisions')->count();

        $filteredRecords = $query->count();

        if ($order) {
            $sortBy = $order['column'];
            $direction = $order['dir'];
            $columnName = $request->get('columns')[$sortBy]['name'];

            $columnMap = [
                'package_no' => 'p.package_no',
                'customer' => 'c.code',
                'model' => 'm.name',
                'part_no' => 'pr.part_no',
                'revision_no' => 'r.revision_no',
                'uploaded_at' => 'r.created_at',
                'status' => 'r.revision_status',
                'ecn_no' => 'r.ecn_no',
                'doctype_group' => 'dg.name',
                'doctype_subcategory' => 'sc.name',
                'part_group' => 'pg.code_part_group'
            ];

            $dbColumn = $columnMap[$columnName] ?? 'r.created_at';
            $query->orderBy($dbColumn, $direction);
        } else {
            $query->orderBy('r.created_at', 'desc');
        }

        $rows = $query->skip($start)->take($length)->get([
            'r.id as id',
            'p.package_no as package_no',
            'c.code as customer',
            'm.name as model',
            'pr.part_no as part_no',
            'r.revision_no as revision_no',
            'r.created_at as uploaded_at',
            'r.revision_status as status',
            'r.ecn_no as ecn_no',
            'crl.label as revision_label_name',
            'dg.name as doctype_group',
            'sc.name as doctype_subcategory',
            'pg.code_part_group as part_group'
        ])->map(function ($row) {
            return [
                'id' => str_replace('=', '-', encrypt($row->id)),
                'package_no' => $row->package_no,
                'customer' => $row->customer ?? '-',
                'model' => $row->model ?? '-',
                'part_no' => $row->part_no ?? '-',
                'revision_no' => is_null($row->revision_no) ? '0' : (string)$row->revision_no,
                'uploaded_at' => $row->uploaded_at ? date('Y-m-d H:i:s', strtotime($row->uploaded_at)) : null,
                'status' => $row->status ?? 'draft',
                'ecn_no' => $row->ecn_no ?? '-',
                'revision_label_name' => $row->revision_label_name,
                'doctype_group' => $row->doctype_group ?? '-',
                'doctype_subcategory' => $row->doctype_subcategory ?? '-',
                'part_group' => $row->part_group ?? '-'
            ];
        });

        return response()->json([
            'draw' => (int) $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows
        ]);
    }
}
