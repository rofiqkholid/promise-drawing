<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class LogLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $feature = null): Response
    {
        if (Auth::check() && $feature) {
            $user = Auth::user();

            $dataToUpdate = [];

            switch ($feature) {
                case 'upload':
                    $dataToUpdate['last_seen_upload'] = now();
                    break;
                case 'export':
                    $dataToUpdate['last_seen_export'] = now();
                    break;
                case 'share':
                    $dataToUpdate['last_seen_share'] = now();
                    break;
            }

            $user->lastSeen()->updateOrCreate(
                ['user_id' => $user->id],
                $dataToUpdate
            );
        }

        return $next($request);
    }
}
