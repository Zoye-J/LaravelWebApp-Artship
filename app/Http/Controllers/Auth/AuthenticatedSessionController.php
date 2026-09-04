<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Services\CustomHashService;
use App\Models\TwoFactorAuth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Check if user has 2FA enabled
        $user = Auth::user();
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->where('enabled', true)->first();
        
        if ($twoFactor) {
            // Store user ID in session for 2FA verification
            session()->put('2fa_user_id', $user->id);
            session()->put('2fa_verified', false);
            
            // Logout user temporarily
            Auth::logout();
            $request->session()->invalidate();
            
            // Redirect to 2FA verification
            return redirect()->route('2fa.verify')->with('info', 'Please enter your two-factor authentication code.');
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
