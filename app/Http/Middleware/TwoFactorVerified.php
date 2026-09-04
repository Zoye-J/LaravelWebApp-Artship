<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TwoFactorAuth;

class TwoFactorVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if 2FA is enabled for this user
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->where('enabled', true)->first();
        
        if ($twoFactor) {
            // Check if 2FA is already verified in this session
            if (!session()->get('2fa_verified', false)) {
                return redirect()->route('2fa.verify')->with('error', 'Please verify your two-factor authentication.');
            }
        }

        return $next($request);
    }
}