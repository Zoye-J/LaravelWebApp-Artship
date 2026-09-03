@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4">Edit Profile</h2>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="block font-semibold text-brown-800">Name</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                   class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="block font-semibold text-brown-800">New Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block font-semibold text-brown-800">Confirm Password</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
        </div>

        <button class="bg-brown-700 text-white px-4 py-2 rounded">Update</button>
    </form>
</div>
@endsection

