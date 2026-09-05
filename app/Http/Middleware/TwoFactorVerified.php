<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TwoFactorAuth;

class TwoFactorVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->where('enabled', true)->first();

        if ($twoFactor && !session()->get('2fa_verified', false)) {
            return redirect()->route('2fa.verify')->with('error', 'Please verify your two-factor authentication.');
        }

        return $next($request);
    }
}