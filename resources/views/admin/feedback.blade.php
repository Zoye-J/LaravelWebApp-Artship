@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">💬 Course Feedback & Ratings</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($courses as $course)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $course->title }}</h3>
                    
                    <!-- Average Rating -->
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($course->ratings_avg_rating))
                                    ⭐
                                @else
                                    ☆
                                @endif
                            @endfor
                        </div>
                        <span class="ml-2 text-gray-600">({{ number_format($course->ratings_avg_rating, 1) }})</span>
                        <span class="ml-2 text-sm text-gray-500">{{ $course->ratings_count }} reviews</span>
                    </div>

                    <!-- Reviews -->
                    @if($course->ratings->count() > 0)
                        <div class="space-y-4 max-h-64 overflow-y-auto">
                            @foreach($course->ratings as $rating)
                                <div class="border-l-4 border-orange-500 pl-4 py-2">
                                    <div class="flex justify-between items-start">
                                        <span class="font-medium text-gray-700">{{ $rating->user->name }}</span>
                                        <div class="text-yellow-400 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $rating->rating)⭐@else☆@endif
                                            @endfor
                                        </div>
                                    </div>
                                    @if($rating->review)
                                        <p class="text-gray-600 text-sm mt-1">"{{ $rating->review }}"</p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-1">{{ $rating->created_at->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No reviews yet</p>
                    @endif
                </div>
            @endforeach
        </div>

        @if($courses->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">💬</div>
                <h3 class="text-2xl font-semibold text-gray-700 mb-4">No feedback yet</h3>
                <p class="text-gray-500">Student reviews will appear here once they rate courses.</p>
            </div>
        @endif
    </div>
</div>
@endsection