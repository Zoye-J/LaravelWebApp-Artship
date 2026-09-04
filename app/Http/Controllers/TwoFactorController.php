<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TwoFactorController extends Controller
{
    private TwoFactorService $twoFactor;

    public function __construct(TwoFactorService $twoFactor)
    {
        $this->twoFactor = $twoFactor;
    }

    /**
     * Show 2FA setup page
     */
    public function setup(): View
    {
        $user = Auth::user();
        $isEnabled = $this->twoFactor->isTwoFactorEnabled($user);
        
        if (!$isEnabled) {
            // Generate new secret
            $secret = $this->twoFactor->generateSecret();
            session()->put('2fa_secret', $secret);
        } else {
            $secret = TwoFactorAuth::where('user_id', $user->id)->value('secret');
        }
        
        return view('auth.two-factor-setup', compact('secret', 'isEnabled'));
    }

    /**
     * Enable 2FA for user
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $secret = session()->get('2fa_secret');
        if (!$secret) {
            return back()->with('error', 'Please generate a secret first.');
        }

        // Verify the code
        if (!$this->twoFactor->verifyCode($secret, $request->code)) {
            return back()->with('error', 'Invalid verification code. Please try again.');
        }

        // Enable 2FA
        $this->twoFactor->enableTwoFactor(Auth::user(), $secret);
        session()->forget('2fa_secret');

        return redirect()->route('profile.edit')->with('status', 'Two-factor authentication enabled successfully!');
    }

    /**
     * Disable 2FA for user
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Verify password first
        $request->validate([
            'password' => 'required|string',
        ]);

        // Check password
        if (!app(\App\Services\CustomHashService::class)->check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password.');
        }

        $this->twoFactor->disableTwoFactor($user);
        
        return redirect()->route('profile.edit')->with('status', 'Two-factor authentication disabled.');
    }

    /**
     * Show 2FA verification page
     */
    public function showVerify(): View
    {
        return view('auth.two-factor-verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = Auth::user();
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactor || !$twoFactor->enabled) {
            return redirect()->intended('/dashboard');
        }

        // Check backup codes first
        $backupCodes = $twoFactor->getBackupCodes();
        if (in_array($request->code, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$request->code]);
            $twoFactor->update(['backup_codes' => json_encode(array_values($backupCodes))]);
            
            session()->put('2fa_verified', true);
            return redirect()->intended('/dashboard');
        }

        // Check TOTP code
        if ($this->twoFactor->verifyCode($twoFactor->secret, $request->code)) {
            session()->put('2fa_verified', true);
            $twoFactor->update(['last_verified_at' => now()]);
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Invalid verification code.');
    }
}