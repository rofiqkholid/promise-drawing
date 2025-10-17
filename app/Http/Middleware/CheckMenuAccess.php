<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $menuId): Response
    {
        $allowed_menus = $request->session()->get('allowed_menus');

        if ($allowed_menus && in_array($menuId, $allowed_menus)) {
            return $next($request);
        }

        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
    }
}