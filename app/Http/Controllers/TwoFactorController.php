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
     * Show 2FA verification page (first time setup or login)
     * 
     * @return View|RedirectResponse
     */
    public function showVerify()
    {
        $userId = session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User not found.');
        }

        $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();

        if (!$twoFactor) {
            $twoFactor = TwoFactorAuth::create([
                'user_id' => $userId,
                'secret' => $this->twoFactor->generateSecret(),
                'enabled' => true,
                'backup_codes' => json_encode($this->twoFactor->generateBackupCodes())
            ]);
        }

        $secret = $twoFactor->secret;
        $isFirstTime = !$twoFactor->first_verified_at;

        return view('auth.two-factor-verify', compact('secret', 'isFirstTime', 'user'));
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $twoFactor = TwoFactorAuth::where('user_id', $userId)->first();
        if (!$twoFactor || !$twoFactor->enabled) {
            return redirect()->route('login')->with('error', '2FA not configured for this account.');
        }

        // Check backup codes first
        $backupCodes = json_decode($twoFactor->backup_codes ?? '[]', true);
        if (in_array($request->code, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$request->code]);
            $twoFactor->update([
                'backup_codes' => json_encode(array_values($backupCodes)),
                'last_verified_at' => now(),
                'first_verified_at' => $twoFactor->first_verified_at ?? now()
            ]);
            
            // Login the user
            Auth::loginUsingId($userId);
            session()->put('2fa_verified', true);
            session()->forget('2fa_user_id');
            
            return redirect()->intended('/dashboard')->with('status', 'Welcome back!');
        }

        // Check TOTP code
        if ($this->twoFactor->verifyCode($twoFactor->secret, $request->code)) {
            // Login the user
            Auth::loginUsingId($userId);
            session()->put('2fa_verified', true);
            $twoFactor->update([
                'last_verified_at' => now(),
                'first_verified_at' => $twoFactor->first_verified_at ?? now()
            ]);
            session()->forget('2fa_user_id');
            
            // Show backup codes only on first verification
            if (!$twoFactor->first_verified_at) {
                $backupCodes = json_decode($twoFactor->backup_codes, true);
                session()->flash('backup_codes', $backupCodes);
                return redirect()->intended('/dashboard')->with('info', 'Please save your backup codes for future use.');
            }
            
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Invalid verification code. Please try again.');
    }

    /**
     * Setup page for viewing secret and QR code
     */
    public function setup()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $twoFactor = TwoFactorAuth::firstOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $this->twoFactor->generateSecret(),
                'enabled' => true,
                'backup_codes' => json_encode($this->twoFactor->generateBackupCodes())
            ]
        );

        $secret = $twoFactor->secret;
        $isEnabled = $twoFactor->enabled;
        $backupCodes = json_decode($twoFactor->backup_codes ?? '[]', true);

        return view('auth.two-factor-setup', compact('secret', 'isEnabled', 'backupCodes'));
    }

    /**
     * Enable/Re-enable 2FA
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();

        if (!$twoFactor) {
            return back()->with('error', '2FA not configured.');
        }

        // Verify the code
        if (!$this->twoFactor->verifyCode($twoFactor->secret, $request->code)) {
            return back()->with('error', 'Invalid verification code. Please try again.');
        }

        // Regenerate backup codes
        $twoFactor->update([
            'backup_codes' => json_encode($this->twoFactor->generateBackupCodes()),
            'enabled' => true,
            'first_verified_at' => now()
        ]);

        return redirect()->route('profile.edit')->with('status', 'Two-factor authentication verified successfully!');
    }

    /**
     * Disable 2FA for user
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!app(\App\Services\CustomHashService::class)->check($request->password, $user->password)) {
            return back()->with('error', 'Invalid password.');
        }

        TwoFactorAuth::where('user_id', $user->id)->delete();
        
        return redirect()->route('profile.edit')->with('status', 'Two-factor authentication disabled.');
    }

    /**
     * Get QR code URL (helper method)
     */
    private function getQrCodeUrl(string $email, string $secret): string
    {
        $issuer = config('app.name', 'Artship');
        return 'otpauth://totp/' . urlencode($issuer . ':' . $email) . 
               '?secret=' . $secret . 
               '&issuer=' . urlencode($issuer) . 
               '&algorithm=SHA1&digits=6&period=30';
    }
}