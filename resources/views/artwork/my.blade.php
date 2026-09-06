@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-orange-300 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">My Artwork Submissions</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($submissions as $submission)
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <img src="{{ asset('storage/' . $submission->image_path) }}" alt="{{ $submission->title }}" class="w-full h-48 object-cover rounded-xl mb-4">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $submission->title }}</h3>
                <p class="text-gray-600 mb-2">{{ $submission->description }}</p>
                <div class="text-sm text-gray-500 mb-4">
                    <p>Course: {{ $submission->course->title }}</p>
                    @if($submission->is_featured)
                        <p class="text-green-600 font-medium">⭐ Featured</p>
                    @endif
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('artwork.edit', $submission->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Edit</a>
                    <form action="{{ route('artwork.destroy', $submission->id) }}" method="POST" onsubmit="return confirm('Delete this submission?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg">Delete</button>
                    </form>
                </div>
            </div>
            @empty
                <p class="text-gray-700">You haven't submitted any artwork yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection