<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

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
    }
}