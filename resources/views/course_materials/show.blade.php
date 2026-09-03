@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-yellow-200 mb-4">{{ $course->title }}</h1>
            <p class="text-xl text-yellow-200 max-w-3xl mx-auto">{{ $course->description }}</p>
            <div class="w-24 h-1 bg-orange-500 mx-auto mt-6 rounded-full"></div>
        </div>

        @if($isEnrolled)
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 max-w-2xl mx-auto">
            <h3 class="text-xl font-semibold text-orange-900 mb-4 text-center">Your Progress</h3>
            <div class="w-full bg-orange-100 rounded-full h-6 mb-3">
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 h-6 rounded-full transition-all duration-1000 ease-out" 
                    style="width: {{ $progress }}%">
                    <div class="text-xs text-white font-bold flex items-center justify-center h-full">
                        {{ $progress }}%
                    </div>
                </div>
            </div>
            <p class="text-center text-orange-700 text-sm">
                @if($progress == 0)
                Get started by watching your first video!
                @elseif($progress < 50)
                Keep going! You're making progress.
                @elseif($progress < 100)
                Great job! You're more than halfway there.
                @else
                Congratulations! You've completed this course! 🎉
                @endif
            </p>
        </div>
        @endif
        @if($isEnrolled && $progress >= 80) {{-- Only show when course is mostly complete --}}
            <div class="text-center mt-8">
                <a href="{{ route('artwork.create', $course->id) }}" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold px-8 py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 inline-block">
                    🎨 Submit Your Final Artwork
                </a>
            </div>
        @endif
        @if($isEnrolled && $progress >= 100)
            <div class="bg-white rounded-2xl shadow-lg p-6 mt-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">Rate this course</h3>
    
                <form action="{{ route('courses.rate', $course->id) }}" method="POST">
                    @csrf
        
        <!-- Star Rating -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Your rating:</label>
                        <div class="flex space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="setRating({{ $i }})" class="text-2xl rating-star" data-rating="{{ $i }}">
                                    ☆
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating-value" value="0" required>
                    </div>

        <!-- Review -->
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Your review (optional):</label>
                        <textarea name="review" rows="3" class="w-full border border-gray-300 rounded-lg p-3" 
                            placeholder="Share your experience with this course..."></textarea>
                    </div>

                    <button type="submit" class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600">
                        Submit Rating
                    </button>
                </form>
            </div>

            <script>
            function setRating(rating) {
                document.getElementById('rating-value').value = rating;
    
    // Update star display
                document.querySelectorAll('.rating-star').forEach((star, index) => {
                    star.textContent = (index + 1) <= rating ? '⭐' : '☆';
                });
            }
            </script>
            @endif
        <!-- Materials Container with Tabs -->
        <div class="bg-yellow-200 rounded-3xl shadow-2xl overflow-hidden mb-8">
            <!-- Tabs Navigation -->
            <div class="bg-orange-300 px-6">
                <div class="flex space-x-1">
                    <button onclick="showTab('videos')" 
                            id="videos-tab"
                            class="px-6 py-3 rounded-t-lg font-semibold transition-all duration-300 flex items-center bg-white text-orange-700">
                        <span class="mr-2">🎥</span> Video Lectures
                        <span class="ml-2 bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">
                            {{ $course->materials->where('type', 'video')->count() }}
                        </span>
                    </button>
                    <button onclick="showTab('pdfs')" 
                            id="pdfs-tab"
                            class="px-6 py-3 rounded-t-lg font-semibold transition-all duration-300 flex items-center text-white hover:text-amber-100">
                        <span class="mr-2">📄</span> PDF Materials
                        <span class="ml-2 bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">
                            {{ $course->materials->where('type', 'pdf')->count() }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Videos Tab -->
                <div id="videos-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($course->materials->where('type', 'video') as $video)
                            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-6 border border-orange-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.02]" 
                                data-material-id="{{ $video->id }}">
                                <div class="flex items-start mb-4">
                                    <div class="bg-orange-500 p-3 rounded-xl mr-4">
                                        <span class="text-white text-2xl">🎥</span>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-orange-900 text-lg mb-1">{{ $video->title }}</h3>
                                        <p class="text-orange-600 text-sm">Uploaded: {{ $video->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                            
                                <video controls class="w-full rounded-xl shadow-md mb-4">
                                    <source src="{{ asset('storage/' . $video->file_path) }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            
                                <div class="flex space-x-3">
                                    <a href="{{ asset('storage/' . $video->file_path) }}" target="_blank"
                                        class="flex-1 bg-orange-500 hover:bg-orange-600 text-white text-center py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg flex items-center justify-center">
                                            <span class="mr-2">▶</span> Watch Video
                                    </a>
                                
                                    @if(auth()->check() && auth()->user()->role === 'admin')
                                        <form action="{{ route('materials.destroy', $video->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-xl transition-all duration-300 hover:shadow-lg"
                                                    onclick="return confirm('Are you sure you want to delete this video?')">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-12">
                                <div class="text-6xl mb-4 text-orange-300">🎥</div>
                                <h3 class="text-2xl font-semibold text-orange-800 mb-2">No video lectures yet</h3>
                                <p class="text-orange-600 mb-6">Check back later for video content</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- PDFs Tab -->
                <div id="pdfs-content" style="display: none;">
                    <div class="grid grid-cols-1 gap-4">
                        @forelse($course->materials->where('type', 'pdf') as $pdf)
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.01]" 
                                data-material-id="{{ $pdf->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="bg-amber-500 p-3 rounded-xl mr-4">
                                            <span class="text-white text-2xl">📄</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-orange-900 text-lg">{{ $pdf->title }}</h3>
                                            <p class="text-orange-600 text-sm">Uploaded: {{ $pdf->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                
                                    <div class="flex space-x-3">
                                        <a href="{{ asset('storage/' . $pdf->file_path) }}" target="_blank"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 hover:shadow-lg flex items-center">
                                                <span class="mr-2"></span> View PDF
                                        </a>
                                    
                                        @if(auth()->check() && auth()->user()->role === 'admin')
                                            <form action="{{ route('materials.destroy', $pdf->id) }}" method="POST" class="flex items-center">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-xl transition-all duration-300 hover:shadow-lg"
                                                        onclick="return confirm('Are you sure you want to delete this PDF?')">
                                                     Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4 text-amber-300">📄</div>
                                <h3 class="text-2xl font-semibold text-orange-800 mb-2">No PDF materials yet</h3>
                                <p class="text-orange-600 mb-6">Check back later for PDF resources</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Upload Section -->
        @if(auth()->check() && auth()->user()->role === 'admin')
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-orange-900 mb-6 text-center"> Upload New Material</h2>
            <form action="{{ route('courses.materials.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-orange-800 font-semibold mb-2">Material Type</label>
                        <select name="type" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500 transition-all duration-300">
                            <option value="video">Video</option>
                            <option value="pdf">PDF Document</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-orange-800 font-semibold mb-2">Title</label>
                        <input type="text" name="title" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500 transition-all duration-300" placeholder="Enter material title" required>
                    </div>
                </div>
                
                <div>
                    <label class="block text-orange-800 font-semibold mb-2">File</label>
                    <input type="file" name="file" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500 transition-all duration-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" required>
                </div>
                
                <div class="text-center pt-4">
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105">
                        Upload Material
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
// Simple tab functionality
function showTab(tabName) {
    console.log('Showing tab:', tabName); // Debug log
    
    // Hide all content
    document.querySelectorAll('[id$="-content"]').forEach(el => {
        el.style.display = 'none';
    });
    
    // Remove active styles from all tabs
    document.querySelectorAll('[id$="-tab"]').forEach(el => {
        el.classList.remove('bg-white', 'text-orange-700');
        el.classList.add('text-white');
    });
    
    // Show selected content
    const contentElement = document.getElementById(tabName + '-content');
    if (contentElement) {
        contentElement.style.display = 'block';
    }
    
    // Add active styles to selected tab
    const tabElement = document.getElementById(tabName + '-tab');
    if (tabElement) {
        tabElement.classList.remove('text-white');
        tabElement.classList.add('bg-white', 'text-orange-700');
    }
}

// Track material views
function trackMaterialView(courseId, materialId) {
    fetch(`/courses/${courseId}/materials/${materialId}/view`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        console.log('Material viewed tracked:', data);
    })
    .catch(error => console.error('Error tracking view:', error));
}

// Set up video tracking
function setupVideoTracking() {
    // Track video plays
    document.querySelectorAll('video').forEach(video => {
        let hasTracked = false;
        
        video.addEventListener('play', function() {
            if (!hasTracked) {
                const materialContainer = this.closest('[data-material-id]');
                const materialId = materialContainer ? materialContainer.dataset.materialId : null;
                if (materialId) {
                    trackMaterialView({{ $course->id }}, materialId);
                    hasTracked = true;
                }
            }
        });
    });

    // Track PDF views
    document.querySelectorAll('a[href*=".pdf"], a[target="_blank"]').forEach(link => {
        if (link.href.includes('/storage/')) {
            link.addEventListener('click', function() {
                const materialContainer = this.closest('[data-material-id]');
                const materialId = materialContainer ? materialContainer.dataset.materialId : null;
                if (materialId) {
                    trackMaterialView({{ $course->id }}, materialId);
                }
            });
        }
    });
}

// Set initial tab to videos and setup tracking
document.addEventListener('DOMContentLoaded', function() {
    showTab('videos');
    setupVideoTracking();
});
</script>
@endsection