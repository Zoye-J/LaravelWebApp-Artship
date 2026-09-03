<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRating extends Model
{
    use HasFactory;
    
    // ============================================
    // PERSON 3: Added encryption traits
    // ============================================
    use EncryptableFields, IntegrityProtected;

    /**
     * PERSON 3: Define which fields need encryption
     * Note: 'rating' is integer, only 'review' needs encryption
     */
    protected $encryptable = ['review'];
    
    /**
     * PERSON 3: Define which fields need MAC verification
     */
    protected $macProtected = ['review'];

    protected $fillable = ['user_id', 'course_id', 'rating', 'review', 'viewed_at'];
    
    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }
}