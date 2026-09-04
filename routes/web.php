<?php

use App\Models\ArtworkSubmission;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CourseMaterialController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MyCourseController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\CourseRatingController;
use App\Http\Controllers\KeyManagementController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
require __DIR__.'/auth.php';

Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');

Route::get('/dashboard', function () {
    try {
        // Try to get featured artworks with relationships
        $featuredArtworks = ArtworkSubmission::with(['user', 'course'])
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        logger('Found ' . $featuredArtworks->count() . ' featured artworks');
        
    } catch (\Exception $e) {
        logger('Error fetching featured artworks: ' . $e->getMessage());
        $featuredArtworks = collect([]);
    }
    
    return view('dashboard', compact('featuredArtworks'));
})->middleware(['auth'])->name('dashboard');

Route::post('/courses/{course}/rate', [CourseRatingController::class, 'store'])
    ->name('courses.rate');


// ADMIN-ONLY ROUTES (require admin role)
Route::middleware(['auth', 'admin'])->group(function () {
    // Admin course management

    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Admin material management
    Route::get('/courses/{course}/materials/create', [CourseMaterialController::class, 'create'])->name('courses.materials.create');
    Route::post('/courses/{course}/materials', [CourseMaterialController::class, 'store'])->name('courses.materials.store');
    Route::delete('/materials/{material}', [CourseMaterialController::class, 'destroy'])->name('materials.destroy');

    // Admin artwork management
    Route::get('/admin/submissions', [ArtworkController::class, 'index'])->name('artwork.index');
    Route::post('/artwork/{artwork}/feature', [ArtworkController::class, 'feature'])->name('artwork.feature');
    Route::post('/artwork/{artwork}/unfeature', [ArtworkController::class, 'unfeature'])->name('artwork.unfeature');

    Route::get('/admin/feedback', [CourseRatingController::class, 'index'])
    ->name('admin.feedback')
    ->middleware(['auth', 'admin']);
    // Artwork viewing routes
    Route::post('/artwork/{artwork}/mark-viewed', [ArtworkController::class, 'markAsViewed'])
    ->name('artwork.mark-viewed');
    Route::post('/artwork/mark-all-viewed', [ArtworkController::class, 'markAllViewed'])
    ->name('artwork.mark-all-viewed');

    // Feedback viewing routes  
    Route::post('/feedback/{rating}/mark-viewed', [CourseRatingController::class, 'markAsViewed'])
    ->name('feedback.mark-viewed');
    Route::post('/feedback/mark-all-viewed', [CourseRatingController::class, 'markAllViewed'])
    ->name('feedback.mark-all-viewed');
});


// AUTHENTICATED USER ROUTES (all logged-in users)
Route::middleware(['auth','2fa'])->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Course routes - accessible to all authenticated users
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    // Materials routes - accessible to all authenticated users
    Route::get('/courses/{course}/materials', [CourseMaterialController::class, 'index'])->name('courses.materials.index');

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{courseId}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{courseId}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // Course enrollment
    Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('/courses/{course}/unenroll', [CourseController::class, 'unenroll'])
        ->name('courses.unenroll');

    // My Courses
    Route::get('/my-courses', [MyCourseController::class, 'index'])->name('my.courses');

    Route::post('/courses/{course}/materials/{material}/view', [CourseController::class, 'markAsViewed'])
        ->name('materials.view');

    // User routes
    Route::get('/courses/{course}/artwork/create', [ArtworkController::class, 'create'])->name('artwork.create');
    Route::post('/courses/{course}/artwork', [ArtworkController::class, 'store'])->name('artwork.store');
    Route::post('/artwork/{artwork}/like', [ArtworkController::class, 'toggleLike'])->name('artwork.like');
});


Route::middleware(['auth', 'admin'])->prefix('admin/keys')->name('admin.keys.')->group(function () {
    Route::get('/', [KeyManagementController::class, 'index'])->name('index');
    Route::get('/create', [KeyManagementController::class, 'create'])->name('create');
    Route::post('/', [KeyManagementController::class, 'store'])->name('store');
    Route::get('/{key}', [KeyManagementController::class, 'show'])->name('show');
    Route::post('/{key}/rotate', [KeyManagementController::class, 'rotate'])->name('rotate');
    Route::get('/{key}/export', [KeyManagementController::class, 'exportPublicKey'])->name('export');
    Route::post('/{key}/revoke', [KeyManagementController::class, 'revoke'])->name('revoke');
});

// 2FA routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [\App\Http\Controllers\TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('2fa.disable');
});

Route::get('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'showVerify'])->name('2fa.verify');
Route::post('/2fa/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('2fa.verify.post');