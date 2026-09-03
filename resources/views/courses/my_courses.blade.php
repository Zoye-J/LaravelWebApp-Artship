
@extends('layouts.app')

@section('content')
<div class="p-6 bg-orange-300 min-h-screen">
    <h1 class="text-2xl font-bold text-black mb-6">My Courses</h1>

    @if($courses->isEmpty())
        <p class="text-gray-700">You haven't enrolled in any courses yet.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="bg-white p-4 border rounded shadow h-full flex flex-col justify-between">
                    @if($course->thumbnail)
                        <img src="{{ asset($course->thumbnail) }}"
                            alt="Course Thumbnail"
                            class="w-full h-48 object-cover rounded mb-3 border border-gray-300">
                    @else
                        <div class="w-full h-48 bg-gray-200 rounded mb-3 flex items-center justify-center">
                            <span class="text-gray-500">No Image</span>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-xl font-semibold text-brown-800">{{ $course->title }}</h3>
                        <p class="text-sm text-amber-700 mb-1">{{ ucfirst($course->category) }}</p>
                        <p class="text-gray-600 mb-3">{{ $course->description }}</p>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $course->progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">{{ $course->progress }}% Complete</p>
                    </div>

                    <div class="flex flex-col space-y-2 mt-4">
                        <a href="{{ route('courses.show', $course->id) }}" 
                           class="inline-block bg-yellow-200 text-black font-semibold px-4 py-2 rounded-lg hover:bg-yellow-300 transition text-center">
                           Go to Course →
                        </a>
                        
                        <form action="{{ route('courses.unenroll', $course->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-500 text-white font-semibold px-4 py-2 rounded-lg hover:bg-red-600 transition"
                                    onclick="return confirm('Are you sure you want to unenroll from this course?')">
                                Unenroll
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection