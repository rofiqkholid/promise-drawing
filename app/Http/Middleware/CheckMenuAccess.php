<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use Illuminate\Support\Facades\Route;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $menuId): Response
    {
        $user = $request->user();
        
        if ($user) {
            $roleMenuIds = DB::table('user_scope_roles')
                ->join('role_scope_permissions', function($join) {
                    $join->on('user_scope_roles.role_id', '=', 'role_scope_permissions.role_id')
                         ->on('user_scope_roles.scope_id', '=', 'role_scope_permissions.scope_id');
                })
                ->join('permissions', 'permissions.id', '=', 'role_scope_permissions.permission_id')
                ->where('user_scope_roles.user_id', $user->id)
                ->where('user_scope_roles.scope_id', 'app_drawing')
                ->where('permissions.permission_name', 'view')
                ->pluck('role_scope_permissions.menu_id')
                ->toArray();

            $allowedOverrides = DB::table('user_scope_permissions')
                ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
                ->where('user_scope_permissions.user_id', $user->id)
                ->where('user_scope_permissions.scope_id', 'app_drawing')
                ->where('user_scope_permissions.access_type', 'ALLOW')
                ->where('permissions.permission_name', 'view')
                ->pluck('user_scope_permissions.menu_id')
                ->toArray();

            $deniedOverrides = DB::table('user_scope_permissions')
                ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
                ->where('user_scope_permissions.user_id', $user->id)
                ->where('user_scope_permissions.scope_id', 'app_drawing')
                ->where('user_scope_permissions.access_type', 'DENY')
                ->where('permissions.permission_name', 'view')
                ->pluck('user_scope_permissions.menu_id')
                ->toArray();

            $allowedMenuIds = array_diff(array_unique(array_merge($roleMenuIds, $allowedOverrides)), $deniedOverrides);

            if (in_array($menuId, $allowedMenuIds)) {
                return $next($request);
            }

            if (!empty($allowedMenuIds)) {
                $firstMenu = Menu::whereIn('id', $allowedMenuIds)
                    ->where('is_active', '1')
                    ->whereNotNull('route')
                    ->where('route', '!=', '')
                    ->orderBy('sort_order', 'asc')
                    ->first();
                if ($firstMenu && Route::has($firstMenu->route)) {
                    return redirect()->route($firstMenu->route);
                }
            }
        }
        
        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
    }
}