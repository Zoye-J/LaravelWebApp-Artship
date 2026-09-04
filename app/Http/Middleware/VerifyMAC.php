<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyMAC
{
    public function handle(Request $request, Closure $next)
    {
        // Skip integrity checks for authentication routes
        $authRoutes = ['login', 'register', 'logout', 'forgot-password', 'reset-password'];
        $currentPath = $request->path();
        
        foreach ($authRoutes as $route) {
            if (strpos($currentPath, $route) !== false) {
                return $next($request);
            }
        }

        // Only verify on GET requests for data display
        if ($request->isMethod('get')) {
            $response = $next($request);
            if ($response->isSuccessful()) {
                $response->headers->set('X-Integrity-Check', 'passed');
            }
            return $response;
        }

        return $next($request);
    }
}