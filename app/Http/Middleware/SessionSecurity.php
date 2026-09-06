<?php

namespace App\Http\Middleware;

use App\Services\SessionEncryptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Binds each authenticated session to the IP/User-Agent that started it, and
 * enforces a rolling timeout — using SessionEncryptionService so the bound
 * token itself is encrypted and integrity-protected, not just a plain value
 * sitting in the session store.
 *
 * Apply this AFTER 'auth' in the route middleware stack, since it needs
 * $request->user() to be resolved already.
 */
class SessionSecurity
{
    public function __construct(private SessionEncryptionService $sessionService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();

        $token = session('secure_session_token');

        // No token yet for this session (e.g. just logged in) — issue one.
        if (!$token) {
            session(['secure_session_token' => $this->sessionService->generateSessionToken($user->id, $ip, $userAgent)]);
            return $next($request);
        }

        if (!$this->sessionService->validateSessionToken($token, $ip, $userAgent)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Your session is no longer valid — please log in again.');
        }

        // Rolling timeout: re-issue on each valid request so activity keeps the session alive.
        session(['secure_session_token' => $this->sessionService->generateSessionToken($user->id, $ip, $userAgent)]);

        return $next($request);
    }
}
