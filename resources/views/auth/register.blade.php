<x-guest-layout>

    <!-- Logo -->
    <div class="flex justify-center mb-4">
        <img src="{{ asset('images/logo.png') }}" alt="Artship Logo" class="h-28">
    </div>
    <h2 class="text-center text-brown-800 text-2xl font-semibold mb-4">Welcome to Artship</h2>


    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Name
            </label>
            <input id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required autofocus autocomplete="name"
                class="block mt-1 w-full bg-amber-50 text-amber-900 placeholder-amber-700 border border-amber-300 rounded-md ">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Email
            </label>
            <input id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required autocomplete="username"
                class="block mt-1 w-full bg-amber-50 text-amber-900 placeholder-amber-700 border border-amber-300 rounded-md ">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Password
            </label>
            <input id="password"
                type="password"
                name="password"
                required autocomplete="new-password"
                class="block mt-1 w-full bg-amber-50 text-amber-900 placeholder-amber-700 border border-amber-300 rounded-md ">

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-amber-900 mb-1">
                Confirm Password
            </label>
            <input id="password_confirmation"
                type="password"
                name="password_confirmation"
                required autocomplete="new-password"
                class="block mt-1 w-full bg-amber-50 text-amber-900 placeholder-amber-700 border border-amber-300 rounded-md ">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Register Button -->
        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="bg-orange-300 hover:bg-amber-600 text-white">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Switch to Login -->
    <div class="mt-6 text-center text-sm text-amber-800">
        Already have an account?
        <a href="{{ route('login') }}" class="underline hover:text-amber-600 font-medium">Login here</a>
    </div>

</x-guest-layout>

