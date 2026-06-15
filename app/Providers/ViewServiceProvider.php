<?php

namespace App\Providers;

use App\Models\Menu;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
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
        View::composer('layouts.sidebar', function ($view) {
            $menus = Menu::query()
                ->whereNull('parent_id')
                ->where('is_active', 1)
                ->where('scope_id', 'app_drawing') // Filter scope aplikasi aktif
                ->with(['children' => function ($query) {
                        $query->where('is_active', 1)
                              ->where('scope_id', 'app_drawing'); // Filter scope child menu
                    }])
                ->orderBy('sort_order', 'asc')
                ->get();

            $view->with('menus', $menus);
        });
    }
}
