<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.header', function ($view) {

            $routes = Route::getRoutes();
            $menuItems = [];
            $allowedMenuIds = session('allowed_menus', []);

            if (!Auth::check()) {
                $view->with([
                    'menuItems' => [],
                    'notifUpload' => 0,
                    'notifExport' => 0,
                    'notifShare' => 0
                ]);
                return;
            }

            if (!empty($allowedMenuIds)) {
                $allDbMenus = Menu::where('is_active', '1')
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

        Carbon::macro('toSaiStampFormat', function () {
            /** @var Carbon $this */
            $day = $this->day;
            if (in_array($day % 100, [11, 12, 13], true)) {
                $suffixRaw = 'th';
            } else {
                $last = $day % 10;
                $suffixRaw = match ($last) {
                    1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th'
                };
            }
            $superscripts = ['st' => 'ˢᵗ', 'nd' => 'ⁿᵈ', 'rd' => 'ʳᵈ', 'th' => 'ᵗʰ'];
            return $this->format('M') . '.' . $day . $superscripts[$suffixRaw] . ' ' . $this->format('Y');
        });
    }
}