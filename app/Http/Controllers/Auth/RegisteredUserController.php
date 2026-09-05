<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Generate email lookup hash
        $emailLookup = app(\App\Services\LookupService::class)->emailLookup($request->email);

        // Check if email already exists
        if (User::where('email_lookup', $emailLookup)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'The email has already been taken.',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'email_lookup' => $emailLookup,
            'password' => $request->password,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}