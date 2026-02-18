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
        
        // Log access attempt
        \Illuminate\Support\Facades\Log::info("CheckMenuAccess: User " . ($request->user()->id ?? 'Guest') . " accessing menu ID: $menuId. Allowed: " . json_encode($allowed_menus));

        // Broaden check to allow string or int matches
        if ($allowed_menus && (in_array($menuId, $allowed_menus) || in_array((string)$menuId, $allowed_menus) || in_array((int)$menuId, $allowed_menus))) {
             \Illuminate\Support\Facades\Log::info("CheckMenuAccess: ACCESS GRANTED for Menu ID $menuId");
             return $next($request);
        }
        
        \Illuminate\Support\Facades\Log::warning("CheckMenuAccess: ACCESS DENIED for Menu ID $menuId");
        $debugInfo = "Menu ID: $menuId . Allowed: " . json_encode($allowed_menus);
        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES. ' . $debugInfo);
    }
}