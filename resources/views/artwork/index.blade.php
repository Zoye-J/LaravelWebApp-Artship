@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-orange-300 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Artwork Submissions</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($submissions as $submission)
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <img src="{{ asset('storage/' . $submission->image_path) }}" alt="{{ $submission->title }}" class="w-full h-48 object-cover rounded-xl mb-4">
                
                <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $submission->title }}</h3>
                <p class="text-gray-600 mb-2">{{ $submission->description }}</p>
                
                <div class="text-sm text-gray-500 mb-4">
                    <p>By: {{ $submission->user->name }}</p>
                    <p>Course: {{ $submission->course->title }}</p>
                    <p>Likes: {{ $submission->likes_count }}</p>
                </div>

                <div class="flex space-x-2">
                    @if(!$submission->is_featured)
                    <form action="{{ route('artwork.feature', $submission->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg">Feature</button>
                    </form>
                    @else
                    <form action="{{ route('artwork.unfeature', $submission->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-lg">Unfeature</button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection