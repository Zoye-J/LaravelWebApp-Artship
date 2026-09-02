<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseMaterial;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\EncryptionHelper;

class CourseMaterialController extends Controller
{
    protected $encryptionHelper;

    public function __construct()
    {
        $this->encryptionHelper = new EncryptionHelper();
    }

    public function index($course_id)
    {
        $course = Course::findOrFail($course_id);
        
        // ============================================
        // PERSON 3: Data is automatically decrypted via trait
        // ============================================
        $materials = CourseMaterial::where('course_id', $course_id)
            ->latest()
            ->paginate(10);

        // Check integrity
        foreach ($materials as $material) {
            if ($material->hasIntegrityFailed()) {
                session()->flash('integrity_failure', true);
                \Log::warning('Integrity failure in course material', [
                    'id' => $material->id,
                    'field' => $material->getFailedField()
                ]);
            }
        }

        return view('course_materials.index', compact('course', 'materials'));
    }

    public function create($course_id)
    {
        $course = Course::findOrFail($course_id);
        return view('course_materials.create', compact('course'));
    }

    public function store(Request $request, $course_id)
    {
        \Log::debug('=== UPLOAD ATTEMPT START ===');
        \Log::debug('Has file: ' . ($request->hasFile('file') ? 'YES' : 'NO'));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            \Log::debug('File name: ' . $file->getClientOriginalName());
            \Log::debug('File size: ' . $file->getSize());
            \Log::debug('File mime: ' . $file->getMimeType());
        }

        \Log::debug('All form data: ', $request->all());

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:video,pdf',
                'file' => 'required|file|mimes:mp4,avi,mov,pdf|max:51200'
            ]);

            $path = $request->file('file')->store('course_materials', 'public');
            
            \Log::debug('File stored at: ' . $path);

            // ============================================
            // PERSON 3: Data is automatically encrypted via trait
            // ============================================
            $material = CourseMaterial::create([
                'course_id' => $course_id,
                'type' => $request->type,
                'title' => $request->title,
                'file_path' => $path,
                'uploaded_by' => Auth::id(),
            ]);

            if ($material->hasIntegrityFailed()) {
                return back()->with('error', 'Data integrity check failed. Please try again.');
            }

            \Log::debug('=== UPLOAD SUCCESS ===');
            return back()->with('success', 'Material uploaded successfully!');

        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile());
            \Log::error('Line: ' . $e->getLine());
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function destroy($material_id)
    {
        try {
            $material = CourseMaterial::findOrFail($material_id);
            $course_id = $material->course_id;

            \Log::debug('=== DELETE ATTEMPT START ===');
            \Log::debug('Material ID: ' . $material_id);
            \Log::debug('File path: ' . $material->file_path);
            \Log::debug('Material type: ' . $material->type);

            if ($material->file_path) {
                if (Storage::disk('public')->exists($material->file_path)) {
                    Storage::disk('public')->delete($material->file_path);
                    \Log::debug('File deleted from storage: ' . $material->file_path);
                } else {
                    \Log::debug('File not found in storage: ' . $material->file_path);
                }
            }

            $material->delete();
            \Log::debug('Database record deleted');
            \Log::debug('=== DELETE SUCCESS ===');

            return redirect()->route('courses.materials.index', $course_id)
                ->with('success', 'Material deleted successfully!');

        } catch (\Exception $e) {
            \Log::error('Delete error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile());
            \Log::error('Line: ' . $e->getLine());

            return redirect()->back()->with('error', 'Error deleting material: ' . $e->getMessage());
        }
    }
}