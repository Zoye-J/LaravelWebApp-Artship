@extends('layouts.app')

@section('content')
<h2 class="text-2xl font-bold mb-4">Add New Course</h2>

<form method="POST" action="{{ route('courses.store') }}" enctype="multipart/form-data">

    @csrf
    <div class="mb-3">
        <label class="block mb-1">Title</label>
        <input type="text" name="title" class="w-full p-2 border rounded" required>
    </div>

    <div class="mb-3">
        <label class="block mb-1">Category</label>
        <select name="category" class="w-full p-2 border rounded" required>
            <option value="Digital">Digital</option>
            <option value="Traditional">Traditional</option>
            <option value="Basics">Basics</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="block mb-1">Description</label>
        <textarea name="description" class="w-full p-2 border rounded"></textarea>
    </div>

    <div class="mb-4">
        <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail Image</label>
        <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
            class="mt-1 block w-full text-sm border border-gray-300 rounded p-2">
    </div>


    <button class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Create</button>
</form>
@endsection
