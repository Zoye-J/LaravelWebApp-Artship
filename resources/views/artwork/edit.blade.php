@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-cyan-900 via-orange-300 to-yellow-200 py-8 px-4">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <h1 class="text-3xl font-bold text-orange-900 mb-6 text-center">Edit Your Artwork</h1>

            <form action="{{ route('artwork.update', $artwork->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-orange-800 font-semibold mb-2">Artwork Title</label>
                    <input type="text" name="title" value="{{ old('title', $artwork->title) }}" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500" required>
                </div>

                <div>
                    <label class="block text-orange-800 font-semibold mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full border-2 border-orange-200 rounded-xl p-4 focus:border-orange-500 focus:ring-orange-500">{{ old('description', $artwork->description) }}</textarea>
                </div>

                <div>
                    <p class="text-orange-800 font-semibold mb-2">Current Image</p>
                    <img src="{{ asset('storage/' . $artwork->image_path) }}" class="w-full h-48 object-cover rounded-xl mb-4">
                    <label class="block text-orange-800 font-semibold mb-2">Replace Image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full border-2 border-orange-200 rounded-xl p-4">
                </div>

                <div class="text-center">
                    <button type="submit" class="bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection