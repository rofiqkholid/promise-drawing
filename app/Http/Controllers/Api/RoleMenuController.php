<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Models\Menu;
use App\Models\RoleMenu;

class RoleMenuController extends Controller
{
    /**
     * Ambil seluruh menu + status permission untuk 1 user.
     * GET /role-menu/by-user/{user}
     *
     * Response:
     *  {
     *    success:true,
     *    data:[
     *      { id, title, selected, can_view, can_upload, can_download, can_delete }, ...
     *    ]
     *  }
     */
    public function byUser(User $user)
    {
        $rows = Menu::query()
            ->leftJoin('role_menu as rm', function ($j) use ($user) {
                $j->on('rm.menu_id', '=', 'menus.id')
                  ->where('rm.user_id', '=', $user->id);
            })
            ->select([
                'menus.id',
                'menus.title',
                DB::raw('CASE WHEN rm.user_id IS NULL THEN 0 ELSE 1 END AS selected'),
                DB::raw('COALESCE(rm.can_view,0)     AS can_view'),
                DB::raw('COALESCE(rm.can_upload,0)   AS can_upload'),
                DB::raw('COALESCE(rm.can_download,0) AS can_download'),
                DB::raw('COALESCE(rm.can_delete,0)   AS can_delete'),
            ])
            // SQL Server: taruh NULL di bawah
            ->orderByRaw('CASE WHEN menus.sort_order IS NULL THEN 1 ELSE 0 END, menus.sort_order, menus.title')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * Sinkronkan seluruh akses menu user.
     * POST /role-menu/by-user/{user}
     *
     * Body JSON:
     *  {
     *    "menus":[
     *      {"menu_id":1,"enabled":1,"can_view":1,"can_upload":0,"can_download":1,"can_delete":0},
     *      ...
     *    ]
     *  }
     */
    public function syncByUser(User $user, Request $request)
    {
        $data = $request->validate([
            'menus'                   => ['array'],
            'menus.*.menu_id'         => ['required', 'integer', Rule::exists('menus', 'id')],
            'menus.*.enabled'         => ['required', 'boolean'],
            'menus.*.can_view'        => ['required', 'integer', 'in:0,1'],
            'menus.*.can_upload'      => ['required', 'integer', 'in:0,1'],
            'menus.*.can_download'    => ['required', 'integer', 'in:0,1'],
            'menus.*.can_delete'      => ['required', 'integer', 'in:0,1'],
        ]);

        $items   = collect($data['menus'] ?? []);
        $enabled = $items->filter(fn ($i) => (bool) ($i['enabled'] ?? 0));
        $keepIds = $enabled->pluck('menu_id')->map(fn ($v) => (int) $v)->values()->all();

        DB::transaction(function () use ($user, $enabled, $keepIds) {
            // Hapus mapping yang tidak dikirim (dibersihkan)
            RoleMenu::where('user_id', $user->id)
                ->when(count($keepIds) > 0, fn ($q) => $q->whereNotIn('menu_id', $keepIds))
                ->delete();

            // Upsert yang aktif — pakai Eloquent agar created_at & updated_at otomatis
            foreach ($enabled as $it) {
                RoleMenu::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'menu_id' => (int) $it['menu_id'],
                    ],
                    [
                        'can_view'     => (int) $it['can_view'],
                        'can_upload'   => (int) $it['can_upload'],
                        'can_download' => (int) $it['can_download'],
                        'can_delete'   => (int) $it['can_delete'],
                    ]
                );
            }
        });

        return response()->json(['success' => true]);
    }

    /* ============================================================
     *  ----  LEGACY (per-role) DIBUAT STUB/NOT USED  ----
     * ============================================================ */
    public function dropdowns()      { return response()->json(['message' => 'Use /role-menu/by-user/{user} endpoints.'], 405); }
    public function data(Request $r) { return response()->json(['message' => 'Use /role-menu/by-user/{user} endpoints.'], 405); }

    public function store(Request $r)       { return response()->json(['message' => 'Use POST /role-menu/by-user/{user}.'], 405); }
    public function pairShow(Request $r)    { return response()->json(['message' => 'Use GET /role-menu/by-user/{user}.'], 405); }
    public function pairUpdate(Request $r)  { return response()->json(['message' => 'Use POST /role-menu/by-user/{user}.'], 405); }
    public function pairDestroy(Request $r) { return response()->json(['message' => 'Use POST /role-menu/by-user/{user}.'], 405); }

    public function index()                 { return response()->json(['message' => 'Use /role-menu/by-user/{user} endpoints.'], 405); }
    public function update(Request $r, $id) { return response()->json(['message' => 'Use POST /role-menu/by-user/{user}.'], 405); }
    public function destroy($id)            { return response()->json(['message' => 'Use POST /role-menu/by-user/{user}.'], 405); }
}
