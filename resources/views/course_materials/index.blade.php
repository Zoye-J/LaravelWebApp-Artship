@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-yellow-200 mb-4">{{ $course->title }} - Materials</h1>
            <p class="text-xl text-yellow-200 max-w-3xl mx-auto">Manage all learning resources for this course</p>
            <div class="w-24 h-1 bg-orange-500 mx-auto mt-6 rounded-full"></div>
        </div>

        <!-- Materials Container -->
        <div class="bg-yellow-200 rounded-3xl shadow-2xl overflow-hidden">
            <!-- Tabs Navigation -->
            <div class="bg-orange-300 px-6">
                <div class="flex space-x-1">
                    <button onclick="showTab('videos')" 
                            id="videos-tab"
                            class="px-6 py-3 rounded-t-lg font-semibold transition-all duration-300 flex items-center bg-white text-orange-700">
                        <span class="mr-2">🎥</span> Video Lectures
                        <span class="ml-2 bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">
                            {{ $materials->where('type', 'video')->count() }}
                        </span>
                    </button>
                    <button onclick="showTab('pdfs')" 
                            id="pdfs-tab"
                            class="px-6 py-3 rounded-t-lg font-semibold transition-all duration-300 flex items-center text-white hover:text-amber-100">
                        <span class="mr-2">📄</span> PDF Materials
                        <span class="ml-2 bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-xs">
                            {{ $materials->where('type', 'pdf')->count() }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-8">
                <!-- Videos Tab -->
                <div id="videos-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($materials->where('type', 'video') as $video)
                            <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl p-6 border border-orange-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.02]">
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
                        @forelse($materials->where('type', 'pdf') as $pdf)
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 hover:scale-[1.01]">
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
                                            <span class="mr-2">👀</span> View PDF
                                        </a>
                                        
                                        @if(auth()->check() && auth()->user()->role === 'admin')
                                            <form action="{{ route('materials.destroy', $pdf->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-xl transition-all duration-300 hover:shadow-lg"
                                                        onclick="return confirm('Are you sure you want to delete this PDF?')">
                                                    🗑️
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
        <div class="mt-12 bg-white rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-orange-900 mb-6 text-center">📤 Upload New Material</h2>
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
                        📤 Upload Material
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>

<script>
// Simple tab functionality without Alpine.js
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('[id$="-content"]').forEach(el => {
        el.style.display = 'none';
    });
    
    // Remove active styles from all tabs
    document.querySelectorAll('[id$="-tab"]').forEach(el => {
        el.classList.remove('bg-white', 'text-orange-700');
        el.classList.add('text-white', 'hover:text-amber-100');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').style.display = 'block';
    
    // Add active styles to selected tab
    document.getElementById(tabName + '-tab').classList.remove('text-white', 'hover:text-amber-100');
    document.getElementById(tabName + '-tab').classList.add('bg-white', 'text-orange-700');
}

// Set initial tab to videos
document.addEventListener('DOMContentLoaded', function() {
    showTab('videos');
});
</script>
@endsection