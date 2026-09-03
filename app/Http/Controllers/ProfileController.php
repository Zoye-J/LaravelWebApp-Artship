<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // Show editable profile (Name + Password)
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    // Update name and password
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $user->password = $request->password;
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    // Optional: Show-only page (if you still want /profile/show route)
    public function show()
    {
        return view('profile.show', ['user' => Auth::user()]);
    }

    // Delete account
    public function destroy(Request $request)
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        return redirect('/')->with('success', 'Account deleted.');
    }
}
