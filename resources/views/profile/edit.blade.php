@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-amber-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-600 to-orange-500 px-6 py-4 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-white">Edit Profile</h2>
                <p class="text-amber-100 text-sm">Update your account information</p>
            </div>
            <a href="{{ route('profile.show') }}" class="text-white hover:text-amber-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="m-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        @if(session('status') === 'profile-updated')
            <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✅ Profile updated successfully!
            </div>
        @endif

        @if(session('status') === 'password-updated')
            <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                ✅ Password updated successfully!
            </div>
        @endif

        @if($errors->any())
            <div class="m-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-6 space-y-8">
            <!-- Update Profile Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>
                <p class="text-sm text-gray-500 mb-4">Update your name. Your data will be encrypted before storage.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="name" 
                               name="name" 
                               type="text" 
                               value="{{ old('name', $user->name) }}" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
                               required autofocus>
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">🔒 This field will be encrypted with ECC before storage</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-md transition">
                            Update Name
                        </button>
                        @if(session('status') === 'profile-updated')
                            <span class="text-sm text-green-600">✓ Saved</span>
                        @endif
                    </div>
                </form>
            </div>

            <hr class="border-gray-200">

            <!-- Update Password -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                <p class="text-sm text-gray-500 mb-4">Ensure your account is using a long, random password to stay secure.</p>

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input id="current_password" 
                               name="current_password" 
                               type="password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
                               required>
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input id="password" 
                               name="password" 
                               type="password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
                               required>
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">🔑 Password will be hashed with salt using PBKDF2</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input id="password_confirmation" 
                               name="password_confirmation" 
                               type="password" 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500"
                               required>
                        @error('password_confirmation')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-md transition">
                            Update Password
                        </button>
                        @if(session('status') === 'password-updated')
                            <span class="text-sm text-green-600">✓ Password changed</span>
                        @endif
                    </div>
                </form>
            </div>

            <hr class="border-gray-200">

            <!-- 2FA Section -->
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
                        <p class="text-sm text-gray-500">Add an extra layer of security to your account</p>
                    </div>
                    <a href="{{ route('2fa.setup') }}" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition">
                        @if($twoFactorEnabled ?? false)
                            Manage 2FA
                        @else
                            Setup 2FA
                        @endif
                    </a>
                </div>
                @if($twoFactorEnabled ?? false)
                    <p class="text-sm text-green-600 mt-2">✅ Two-factor authentication is enabled</p>
                @else
                    <p class="text-sm text-gray-500 mt-2">❌ Two-factor authentication is disabled</p>
                @endif
            </div>

            <!-- Back to Profile -->
            <div class="pt-4">
                <a href="{{ route('profile.show') }}" class="text-amber-600 hover:text-amber-800 font-medium">
                    ← Back to Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection