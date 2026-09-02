<?php

namespace App\Http\Controllers;

use App\Models\ArtworkSubmission;
use App\Models\ArtworkLike;
use App\Models\Course;
use App\Services\EncryptionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtworkController extends Controller
{
    protected $encryptionHelper;

    public function __construct()
    {
        $this->encryptionHelper = new EncryptionHelper();
    }

    public function create(Course $course)
    {
        if (!auth()->user()->courses->contains($course->id)) {
            return redirect()->back()->with('error', 'You must be enrolled in the course to submit artwork.');
        }

        return view('artwork.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Upload image
        $imagePath = $request->file('image')->store('artwork_submissions', 'public');

        // ============================================
        // PERSON 3: Data is automatically encrypted via trait
        // No need to manually encrypt here
        // ============================================
        $artwork = ArtworkSubmission::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $imagePath
        ]);

        // Check if integrity was maintained
        if ($artwork->hasIntegrityFailed()) {
            return redirect()->back()->with('error', 'Data integrity check failed. Please try again.');
        }

        return redirect()->route('courses.show', $course->id)
            ->with('success', 'Artwork submitted successfully! It will be reviewed by admins.');
    }

    public function markAsViewed(ArtworkSubmission $artwork)
    {
        $artwork->update(['viewed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllViewed()
    {
        ArtworkSubmission::whereNull('viewed_at')->update(['viewed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function index()
    {
        // ============================================
        // PERSON 3: Data is automatically decrypted via trait
        // when retrieved from database
        // ============================================
        $submissions = ArtworkSubmission::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Check for integrity failures
        foreach ($submissions as $submission) {
            if ($submission->hasIntegrityFailed()) {
                session()->flash('integrity_failure', true);
                session()->flash('integrity_failure_field', $submission->getFailedField());
                \Log::warning('Integrity failure in artwork submission', [
                    'id' => $submission->id,
                    'field' => $submission->getFailedField()
                ]);
            }
        }

        return view('artwork.index', compact('submissions'));
    }

    public function feature(ArtworkSubmission $artwork)
    {
        $artwork->update(['is_featured' => true]);
        return back()->with('success', 'Artwork featured successfully!');
    }

    public function unfeature(ArtworkSubmission $artwork)
    {
        $artwork->update(['is_featured' => false]);
        return back()->with('success', 'Artwork unfeatured successfully!');
    }

    public function toggleLike(ArtworkSubmission $artwork)
    {
        $like = ArtworkLike::where('user_id', auth()->id())
            ->where('artwork_id', $artwork->id)
            ->first();

        if ($like) {
            $like->delete();
            $isLiked = false;
        } else {
            ArtworkLike::create([
                'user_id' => auth()->id(),
                'artwork_id' => $artwork->id
            ]);
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'likes_count' => $artwork->fresh()->likes()->count(),
            'is_liked' => $isLiked
        ]);
    }
}