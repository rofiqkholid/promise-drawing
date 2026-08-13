<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL; // Tambahan penting
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.header', function ($view) {

            if (!Auth::check()) {
                $view->with([
                    'menuItems' => [],
                    'notifUpload' => 0,
                    'notifExport' => 0,
                    'notifShare' => 0
                ]);
                return;
            }

            $user = Auth::user();
            $routes = Route::getRoutes();
            $menuItems = [];

            // Get role-based menu permissions under app_drawing
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

            // Get user-specific menu overrides (ALLOW)
            $allowedOverrides = DB::table('user_scope_permissions')
                ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
                ->where('user_scope_permissions.user_id', $user->id)
                ->where('user_scope_permissions.scope_id', 'app_drawing')
                ->where('user_scope_permissions.access_type', 'ALLOW')
                ->where('permissions.permission_name', 'view')
                ->pluck('user_scope_permissions.menu_id')
                ->toArray();

            // Get user-specific menu overrides (DENY)
            $deniedOverrides = DB::table('user_scope_permissions')
                ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
                ->where('user_scope_permissions.user_id', $user->id)
                ->where('user_scope_permissions.scope_id', 'app_drawing')
                ->where('user_scope_permissions.access_type', 'DENY')
                ->where('permissions.permission_name', 'view')
                ->pluck('user_scope_permissions.menu_id')
                ->toArray();

            $allowedMenuIds = array_diff(array_unique(array_merge($roleMenuIds, $allowedOverrides)), $deniedOverrides);

            if (!in_array(1, $allowedMenuIds) && request()->routeIs('monitoring')) {
                if (!empty($allowedMenuIds)) {
                    $firstMenu = Menu::whereIn('id', $allowedMenuIds)
                        ->where('is_active', '1')
                        ->whereNotNull('route')
                        ->where('route', '!=', '')
                        ->orderBy('sort_order', 'asc')
                        ->first();
                    if ($firstMenu && Route::has($firstMenu->route)) {
                        redirect()->route($firstMenu->route)->send();
                        exit;
                    }
                }
            }

            if (!in_array(3, $allowedMenuIds) && request()->routeIs('file-manager.upload')) {
                if (!empty($allowedMenuIds)) {
                    $firstMenu = Menu::whereIn('id', $allowedMenuIds)
                        ->where('is_active', '1')
                        ->whereNotNull('route')
                        ->where('route', '!=', '')
                        ->orderBy('sort_order', 'asc')
                        ->first();
                    if ($firstMenu && Route::has($firstMenu->route)) {
                        redirect()->route($firstMenu->route)->send();
                        exit;
                    }
                }
            }

            if (!empty($allowedMenuIds)) {
                $allDbMenus = Menu::where('is_active', '1')
                    ->where('scope_id', 'app_drawing')
                    ->get(['id', 'title', 'route'])
                    ->keyBy('route');

                foreach ($routes as $route) {
                    $middlewares = $route->gatherMiddleware();
                    $hasCheckMenu = false;
                    foreach ($middlewares as $middleware) {
                        if (is_string($middleware) && str_starts_with($middleware, 'check.menu')) {
                            $hasCheckMenu = true;
                            break;
                        }
                    }

                    if (in_array('GET', $route->methods()) && $hasCheckMenu) {
                        $name = $route->getName();
                        $uri = $route->uri();
                        if (str_contains($uri, '{') && str_contains($uri, '}')) continue;
                        if ($name) {
                            $dbMenu = $allDbMenus->get($name);
                            if (!$dbMenu) continue;
                            if (!in_array($dbMenu->id, $allowedMenuIds)) continue;
                            $menuItems[] = ['name' => $dbMenu->title, 'url' => route($name)];
                        }
                    }
                }
                $menuItems = collect($menuItems)->unique('url')->sortBy('name')->values()->toArray();
            }

            $user = Auth::user();
            $lastSeen = $user->lastSeen;

            $queryUpload = DB::table('doc_package_revisions');

            if ($lastSeen && $lastSeen->last_seen_upload) {
                $queryUpload->where('created_at', '>', $lastSeen->last_seen_upload);
            }

            $notifUpload = $queryUpload->count();
            
            $queryExport = DB::table('doc_packages as dp')
                ->join('package_approvals as pa', 'pa.package_id', '=', 'dp.id')
                ->join('doc_package_revisions as dpr', 'dp.id', '=', 'dpr.package_id')
                ->where('dpr.revision_status', 'approved');

            if ($lastSeen && $lastSeen->last_seen_export) {
                $queryExport->where('pa.decided_at', '>', $lastSeen->last_seen_export);
            }

            $notifExport = $queryExport->distinct('dpr.package_id')->count('dpr.package_id');


            $notifShare = 0;
            /* $queryShare = DB::table('shared_files')->where('target_user_id', $user->id);
            if ($lastSeen && $lastSeen->last_seen_share) {
                $queryShare->where('created_at', '>', $lastSeen->last_seen_share);
            }
            $notifShare = $queryShare->count();
            */

            $view->with([
                'menuItems'   => $menuItems,
                'notifUpload' => $notifUpload,
                'notifExport' => $notifExport,
                'notifShare'  => $notifShare
            ]);
        });
    }
}