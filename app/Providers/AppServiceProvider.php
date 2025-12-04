<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Penting untuk query builder
use Carbon\Carbon;
use App\Models\Menu;
// Tidak perlu use App\Models\File dll, kita pakai DB::table

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
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

                            $menuItems[] = [
                                'name' => $dbMenu->title,
                                'url' => route($name),
                            ];
                        }
                    }
                }

                $menuItems = collect($menuItems)
                    ->unique('url')
                    ->sortBy('name')
                    ->values()
                    ->toArray();
            }

            $user = Auth::user();
            $lastSeen = $user->lastSeen;
            
            $timeUpload = ($lastSeen && $lastSeen->last_seen_upload)
                ? $lastSeen->last_seen_upload
                : $user->created_at;

            $notifUpload = DB::table('doc_package_revisions')
                ->where('created_at', '>', $timeUpload)
                ->count();

            $timeExport = ($lastSeen && $lastSeen->last_seen_export)
                ? $lastSeen->last_seen_export
                : $user->created_at;

        
            $notifExport = DB::table('doc_packages as dp')
                ->leftJoin('package_approvals as pa', 'pa.package_id', '=', 'dp.id')
                ->where('pa.decision', 'approved')
                ->where('pa.decided_at', '>', $timeExport)
                ->count();

            $timeShare = ($lastSeen && $lastSeen->last_seen_share)
                ? $lastSeen->last_seen_share
                : $user->created_at;


            $notifShare = 0;
    

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
                    1 => 'st',
                    2 => 'nd',
                    3 => 'rd',
                    default => 'th',
                };
            }
            $superscripts = ['st' => 'ˢᵗ', 'nd' => 'ⁿᵈ', 'rd' => 'ʳᵈ', 'th' => 'ᵗʰ'];
            $suffix = $superscripts[$suffixRaw];
            return $this->format('M') . '.' . $day . $suffix . ' ' . $this->format('Y');
        });
    }
}
