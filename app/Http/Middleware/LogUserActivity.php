<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogUserActivity
{
    

    

    public function handle($request, Closure $next)
    {
        
        if (
            $request->is('admin/users_activity') || 
            $request->routeIs('livewire.message') ||    
            $request->routeIs('404')               
        ) {
            return $next($request);
        }
        if (Auth::check()) {
            $user = Auth::user();
            $route = $request->route()->getName() ?? $request->path();

            // Log user activity
            Log::info('User Activity: ' . json_encode([
                'user_role' => $user->role_name,
                'user_name' => $user->full_name,
                'user_id' => $user->id,
                'email' => $user->email,
                'route' => $route,
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'payload' => $request->all(),
                'timestamp' => now(),
            ]));
        }

        return $next($request);
    }
}
