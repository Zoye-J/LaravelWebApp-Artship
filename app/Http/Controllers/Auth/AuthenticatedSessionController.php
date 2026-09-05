<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use App\Models\TwoFactorAuth;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private TwoFactorService $twoFactor;

    public function __construct(TwoFactorService $twoFactor)
    {
        $this->twoFactor = $twoFactor;
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $twoFactor = \App\Models\TwoFactorAuth::where('user_id', $user->id)->first();

        if ($twoFactor && $twoFactor->enabled) {
        Auth::logout();
        $request->session()->regenerate();   
        session()->put('2fa_user_id', $user->id);
        session()->put('2fa_verified', false);

        return redirect()->route('2fa.verify')->with('info', 'Please enter your two-factor authentication code.');
    }

        // First login ever — no 2FA configured yet, send them to set it up now
        return redirect()->route('2fa.setup')->with('info', 'Set up two-factor authentication to continue.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->forget(['2fa_verified', '2fa_user_id']);
        return redirect('/');
    }
}