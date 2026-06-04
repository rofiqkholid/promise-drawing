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
        $user = $request->user();
        if ($user && $user->hasMenuPermissionById($menuId, 'can_view')) {
             return $next($request);
        }
        
        abort(403, 'ANDA TIDAK MEMILIKI HAK AKSES.');
    }
}