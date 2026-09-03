@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6 max-w-4xl">
    <!-- Header -->
    <div class="bg-gradient-to-r from-orange-500 to-amber-400 text-white rounded-2xl shadow-lg p-6 mb-8">
        
        <h1 class="text-3xl font-bold">Upload New Material</h1>
        <p class="text-amber-100">Add new learning resources to {{ $course->title }}</p>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-2xl shadow-lg p-6 border border-orange-200">
        <form action="{{ route('courses.materials.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block font-semibold text-gray-700 mb-2">Title *</label>
                <input type="text" name="title" 
                       class="w-full border border-orange-300 rounded-xl p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                       placeholder="Enter material title" required>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-2">Type *</label>
                <select name="type" 
                        class="w-full border border-orange-300 rounded-xl p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition" required>
                    <option value="">Select material type</option>
                    <option value="video">Video</option>
                    <option value="pdf">PDF Document</option>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 mb-2">File *</label>
                <input type="file" name="file" 
                       class="w-full border border-orange-300 rounded-xl p-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100"
                       required>
                <p class="text-sm text-gray-500 mt-2">Supported formats: MP4, AVI, MOV, PDF | Max: 50MB</p>
            </div>

            <div class="pt-4">
                <button type="submit" 
                        class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold px-6 py-3 rounded-xl shadow-md transition transform hover:scale-105">
                    📤 Upload Material
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add this JavaScript to debug and force the link to work -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log the course ID and URL
    console.log('Course ID:', {{ $course->id }});
    console.log('Back URL:', '{{ route('courses.show', $course->id) }}');
    
    // Make sure all links work properly
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Link clicked:', this.href);
        });
    });
});
</script>
@endsection