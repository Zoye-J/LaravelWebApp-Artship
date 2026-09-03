<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    use HasFactory;
    
    // ============================================
    // PERSON 3: Added encryption traits
    // ============================================
    use EncryptableFields, IntegrityProtected;

    /**
     * PERSON 3: Define which fields need encryption
     */
    protected $encryptable = ['title', 'file_path'];
    
    /**
     * PERSON 3: Define which fields need MAC verification
     */
    protected $macProtected = ['title', 'file_path'];

    protected $fillable = [
        'course_id',
        'type',
        'title',
        'file_path',
        'uploaded_by',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}