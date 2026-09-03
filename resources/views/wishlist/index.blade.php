@extends('layouts.app')

@section('content')
<h2 class="text-2xl text-amber-200 font-bold mb-4">My Wishlist</h2>

@if($wishlistCourses->isEmpty())
    <p>You have no courses in your wishlist.</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($wishlistCourses as $course)
            @if($course) {{-- Check if course exists --}}
                <a href="{{ route('courses.show', $course->id) }}" class="block hover:shadow-lg transition duration-300 rounded overflow-hidden">
                    <div class="bg-white p-4 border rounded shadow h-full flex flex-col justify-between">
           
                        @if($course->thumbnail)
                            <img src="{{ asset($course->thumbnail) }}"
                                alt="Course Thumbnail"
                                class="w-full h-48 object-cover rounded mb-3 border border-gray-300">
                        @endif
                        <h3 class="text-xl font-semibold">{{ $course->title }}</h3>
                        <p class="text-sm text-amber-700 mb-1">{{ $course->category }}</p>
                        <p class="text-gray-600">{{ $course->description }}</p>
                        <form method="POST" action="{{ route('wishlist.destroy', $course->id) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600 hover:underline">Remove</button>
                        </form>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
@endif
@endsection
