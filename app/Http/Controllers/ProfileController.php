<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorAuth;
use App\Services\CustomHashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the user's profile (read-only view)
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Check 2FA status
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();
        $twoFactorEnabled = $twoFactor && $twoFactor->enabled;
        
        return view('profile.show', compact('user', 'twoFactorEnabled'));
    }

    /**
     * Show the profile edit form
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        
        // Check 2FA status
        $twoFactor = TwoFactorAuth::where('user_id', $user->id)->first();
        $twoFactorEnabled = $twoFactor && $twoFactor->enabled;
        
        return view('profile.edit', compact('user', 'twoFactorEnabled'));
    }

    /**
     * Update the user's profile information
     * Name is auto-encrypted via EncryptableFields trait
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update name - will be auto-encrypted by trait
        $user->name = $request->name;
        $user->save();

        // Check if integrity check passed
        if ($user->hasIntegrityFailed()) {
            return back()->with('error', 'Profile update failed due to integrity check. Please try again.');
        }

        return back()->with('status', 'profile-updated');
    }

    /**
     * Update the user's password
     * Uses custom hashing from scratch
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $hashService = app(CustomHashService::class);

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify current password using custom hash
        if (!$hashService->check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update password - will be hashed by setPasswordAttribute
        $user->password = $request->password;
        $user->save();

        return back()->with('status', 'password-updated');
    }

    /**
     * Delete the user's account
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password using custom hash
        if (!app(CustomHashService::class)->check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        Auth::logout();
        $user->delete();

        return redirect('/')->with('success', 'Account deleted successfully.');
    }
}