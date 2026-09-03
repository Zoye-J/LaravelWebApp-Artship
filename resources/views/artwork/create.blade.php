@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <h1 class="text-3xl font-bold text-orange-900 mb-6 text-center">Submit Your Artwork</h1>
            <p class="text-center text-gray-600 mb-8">for {{ $course->title }}</p>

            <form action="{{ route('artwork.store', $course->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-orange-800 font-semibold mb-2">Artwork Title</label>
                    <input type="text" name="title" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500" required>
                </div>

                <div>
                    <label class="block text-orange-800 font-semibold mb-2">Description (Optional)</label>
                    <textarea name="description" rows="4" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500" placeholder="Tell us about your artwork..."></textarea>
                </div>

                <div>
                    <label class="block text-orange-800 font-semibold mb-2">Upload Artwork Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full border-2 border-orange-200 rounded-xl p-4 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105">
                        🎨 Submit Artwork
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection