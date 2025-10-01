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
                ->with(['children' => function ($query) {
                        $query->where('is_active', 1);
                    }])
                ->orderBy('sort_order', 'asc')
                ->get();

            $view->with('menus', $menus);
        });
    }
}
