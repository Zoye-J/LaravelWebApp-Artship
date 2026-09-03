<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Enrollment;

class MyCourseController extends Controller
{
    // app/Http/Controllers/MyCourseController.php
    public function index()
    {
        $user = auth()->user();
        $courses = $user->courses ?? collect();
    
    // Get progress for each course
        $courses->each(function($course) use ($user) {
            $enrollment = Enrollment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
        
            $course->progress = $enrollment ? $enrollment->progress : 0;
        });

        return view('courses.my_courses', compact('courses'));
    }

}