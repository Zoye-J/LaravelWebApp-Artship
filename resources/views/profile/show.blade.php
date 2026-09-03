@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Profile Settings</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- Change Name -->
    <form action="{{ route('profile.update') }}" method="POST" class="mb-6">
        @csrf
        <label class="block mb-2">Name</label>
        <input type="text" name="name" value="{{ $user->name }}" class="w-full p-2 border border-amber-300 bg-amber-50 rounded">
        @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
        <button class="mt-3 px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Update Name</button>
    </form>

    <!-- Change Password -->
    <form action="{{ route('profile.password') }}" method="POST">
        @csrf
        <label class="block mb-2">Current Password</label>
        <input type="password" name="current_password" class="w-full p-2 border border-amber-300 bg-amber-50 rounded mb-3">

        <label class="block mb-2">New Password</label>
        <input type="password" name="password" class="w-full p-2 border border-amber-300 bg-amber-50 rounded mb-3">

        <label class="block mb-2">Confirm New Password</label>
        <input type="password" name="password_confirmation" class="w-full p-2 border border-amber-300 bg-amber-50 rounded mb-3">

        @error('current_password')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
        @error('password')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror

        <button class="mt-3 px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Change Password</button>
    </form>
@endsection
