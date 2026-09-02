<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Course;
use App\Services\EncryptionHelper;

class WishlistController extends Controller
{
    protected $encryptionHelper;

    public function __construct()
    {
        $this->encryptionHelper = new EncryptionHelper();
    }

    public function store(Request $request, $courseId)
    {
        if (auth()->user()->role === 'admin') {
            abort(403);
        }

        Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $courseId,
        ]);

        return back()->with('success', 'Course added to wishlist!');
    }

    public function destroy($courseId)
    {
        Wishlist::where('user_id', auth()->id())
            ->where('course_id', $courseId)
            ->delete();

        return back()->with('success', 'Course removed from wishlist.');
    }

    public function index()
    {
        if (auth()->user()->role === 'admin') {
            abort(403);
        }

        $wishlistCourses = auth()->user()
            ->wishlist()
            ->with('course')
            ->get()
            ->pluck('course');

        // Check integrity of courses in wishlist
        foreach ($wishlistCourses as $course) {
            if ($course && $course->hasIntegrityFailed()) {
                session()->flash('integrity_failure', true);
                \Log::warning('Integrity failure in wishlist course', [
                    'course_id' => $course->id,
                    'user_id' => auth()->id()
                ]);
            }
        }

        return view('wishlist.index', compact('wishlistCourses'));
    }
}