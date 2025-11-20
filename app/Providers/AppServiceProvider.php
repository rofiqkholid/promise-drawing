<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
            
            if (!Auth::check() || empty($allowedMenuIds)) {
                $view->with('menuItems', []);
                return;
            }

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

                    if (str_contains($uri, '{') && str_contains($uri, '}')) {
                        continue;
                    }

                    if ($name) {
                        $dbMenu = $allDbMenus->get($name);
                        
                        if (!$dbMenu) {
                            continue;
                        }
                        
                        if (!in_array($dbMenu->id, $allowedMenuIds)) {
                            continue; 
                        }
                        
                        $displayName = $dbMenu->title;
                        
                        $menuItems[] = [
                            'name' => $displayName,
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

            $view->with('menuItems', $menuItems);
        });

        // Menambahkan macro Carbon untuk format tanggal khusus "Oct.25th 2025"
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

            $superscripts = [
                'st' => 'ˢᵗ',
                'nd' => 'ⁿᵈ',
                'rd' => 'ʳᵈ',
                'th' => 'ᵗʰ',
            ];

            $suffix = $superscripts[$suffixRaw];

            // Format: Oct.25ᵗʰ 2025
            return $this->format('M') . '.' . $day . $suffix . ' ' . $this->format('Y');
        });
    }
}