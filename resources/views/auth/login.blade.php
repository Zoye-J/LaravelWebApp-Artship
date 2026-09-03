<x-guest-layout>
    

    <!-- Logo -->
    <div class="flex justify-center mb-4 ">
        <img src="{{ asset('images/logo.png') }}" alt="Artship Logo" class="h-28">
    </div>
    <h2 class="text-center text-brown-800 text-2xl font-semibold mb-4">Welcome Back to Artship</h2>


    <!-- Session Status -->
    <x-auth-session-status class="mb-4 text-amber-900" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="text-amber-900">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Email
            </label>
            <x-text-input id="email" class="block mt-1 w-full bg-amber-50 border border-amber-300 text-amber-900 placeholder-amber-800" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-amber-700" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Password
            </label>
            <x-text-input id="password" class="block mt-1 w-full bg-amber-50 border border-amber-300 text-amber-900 placeholder-amber-800" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-amber-700" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center text-amber-900">
                <input id="remember_me" type="checkbox" class="rounded border-amber-900 text-amber-600 " name="remember">
                <span class="ml-2 text-sm">Remember me</span>
            </label>
        </div>

        <!-- Login Button + Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-amber-700 hover:text-amber-900" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif

            <x-primary-button class="bg-orange-300 hover:bg-amber-600 text-white hover:text-amber-900">
                Log in
            </x-primary-button>
        </div>
    </form>

    <!-- Switch to Register -->
    <div class="mt-6 text-center text-sm text-amber-900">
        Don’t have an account?
        <a href="{{ route('register') }}" class="underline hover:text-amber-600 font-medium">Register here</a>
    </div>

</x-guest-layout>

 

