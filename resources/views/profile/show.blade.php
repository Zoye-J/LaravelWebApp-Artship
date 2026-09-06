@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden border border-amber-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-600 to-orange-500 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">My Profile</h2>
            <p class="text-amber-100 text-sm">View and manage your account information</p>
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
                Profile updated successfully! ✅
            </div>
        @endif

        @if(session('status') === 'password-updated')
            <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                Password updated successfully! ✅
            </div>
        @endif

        <!-- Profile Information -->
        <div class="p-6 space-y-6">
            <!-- Name -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div>
                    <p class="text-sm text-gray-500">Full Name</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $user->name }}</p>
                    @if($user->isFieldEncryptable('name'))
                        <span class="text-xs text-green-600">🔒 Encrypted (ECC)</span>
                    @endif
                </div>
                <a href="{{ route('profile.edit') }}" class="text-amber-600 hover:text-amber-800 font-medium">
                    Edit
                </a>
            </div>

            <!-- Email -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div>
                    <p class="text-sm text-gray-500">Email Address</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $user->email }}</p>
                    @if($user->isFieldEncryptable('email'))
                        <span class="text-xs text-green-600">🔒 Encrypted (ECC)</span>
                    @endif
                </div>
                <a href="{{ route('profile.edit') }}" class="text-amber-600 hover:text-amber-800 font-medium">
                    Edit
                </a>
            </div>

            <!-- Role -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div>
                    <p class="text-sm text-gray-500">Account Type</p>
                    <p class="text-lg font-semibold text-gray-800 capitalize">{{ $user->role }}</p>
                    @if($user->role === 'admin')
                        <span class="text-xs text-blue-600">👑 Administrator</span>
                    @else
                        <span class="text-xs text-gray-500">👤 Regular User</span>
                    @endif
                </div>
                <div>
                    @if($user->role === 'admin')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            Admin
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            User
                        </span>
                    @endif
                </div>
            </div>

            <!-- Security Info -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v-1l1-1 1-1 .257-.257A6 6 0 1118 8zM6 10a2 2 0 104 0 2 2 0 00-4 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">Security Information</h3>
                        <div class="mt-1 text-sm text-amber-700 space-y-1">
                            <p>🔒 All personal data is encrypted using ECC (asymmetric encryption)</p>
                            <p>🔑 Password is hashed with salt using PBKDF2 (custom implementation)</p>
                            <p>🛡️ Data integrity verified using HMAC-SHA256</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4 pt-4">
                <a href="{{ route('profile.edit') }}" 
                   class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-medium rounded-md transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Profile
                </a>
            </div>

            <!-- Delete Account Section -->
            <div class="border-t border-red-200 pt-6 mt-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-red-800">Danger Zone</h3>
                    <p class="text-sm text-red-600 mt-1">Once you delete your account, all data will be permanently removed.</p>
                    
                    <form method="POST" action="{{ route('profile.destroy') }}" class="mt-3" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                            <div class="flex-1 w-full">
                                <input type="password" 
                                       name="password" 
                                       placeholder="Enter password to confirm" 
                                       class="w-full px-3 py-2 border border-red-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                       required>
                                @error('password')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition">
                                Delete Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection