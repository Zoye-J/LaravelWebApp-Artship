<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has admin role
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized. Admin access required.');
        }

        // Check for specific permission if provided
        if ($permission && !$this->hasPermission($user, $permission)) {
            abort(403, 'Unauthorized. You do not have permission for this action.');
        }

        return $next($request);
    }

    /**
     * Check if user has specific permission
     */
    private function hasPermission($user, string $permission): bool
    {
        // Admin has all permissions
        if ($user->role === 'admin') {
            return true;
        }

        // Check specific permissions (can be extended with permissions table)
        $permissions = [
            'manage_users' => 'admin',
            'manage_courses' => 'admin',
            'manage_artwork' => 'admin',
            'manage_keys' => 'admin',
            'view_feedback' => 'admin',
        ];

        $requiredRole = $permissions[$permission] ?? 'admin';
        return $user->role === $requiredRole;
    }
}