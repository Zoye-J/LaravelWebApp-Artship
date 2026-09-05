<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\CustomHashService;
use App\Services\EncryptionHelper;
use App\Services\LookupService;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->input('email');
        $password = $this->input('password');

        // Generate email lookup hash
        $lookupService = app(LookupService::class);
        $emailLookup = $lookupService->emailLookup($email);
        
        // Log for debugging
        \Log::info('Login attempt', [
            'email' => $email,
            'email_lookup' => $emailLookup
        ]);
        
        // Find user by email_lookup
        $user = \App\Models\User::where('email_lookup', $emailLookup)->first();

        if (!$user) {
            \Log::warning('User not found by email_lookup', ['email_lookup' => $emailLookup]);
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Verify password with custom hash service
        $hashService = app(CustomHashService::class);
        if (!$hashService->check($password, $user->password)) {
            \Log::warning('Password verification failed', ['user_id' => $user->id]);
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        \Log::info('Login successful', ['user_id' => $user->id]);

        // Login the user
        Auth::login($user, $this->boolean('remember'));
        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
}