<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseRating;
use Illuminate\Http\Request;

class CourseRatingController extends Controller
{
    public function store(Request $request, Course $course)
    {
        // Check if user is enrolled
        if (!auth()->user()->courses->contains($course->id)) {
            return back()->with('error', 'You must complete the course to rate it.');
        }

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:1000'
        ]);

        // Update or create rating
        CourseRating::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'course_id' => $course->id
            ],
            [
                'rating' => $request->rating,
                'review' => $request->review
            ]
        );

        return back()->with('success', 'Thank you for your rating!');
    }

    public function markAsViewed(CourseRating $rating)
    {
        $rating->update(['viewed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function markAllViewed()
    {
        CourseRating::whereNull('viewed_at')->update(['viewed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function index()
    {
        $courses = Course::with(['ratings.user', 'enrollments'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->has('ratings') // Only courses with ratings
            ->orderBy('ratings_avg_rating', 'desc')
            ->get();

        return view('admin.feedback', compact('courses'));
    }
}