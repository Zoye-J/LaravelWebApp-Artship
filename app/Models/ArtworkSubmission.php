<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtworkSubmission extends Model
{
    use HasFactory;
    
    // ============================================
    // PERSON 3: Added encryption traits
    // ============================================
    use EncryptableFields, IntegrityProtected;

    /**
     * PERSON 3: Define which fields need encryption
     */
    protected $encryptable = ['title', 'description', 'image_path'];
    
    /**
     * PERSON 3: Define which fields need MAC verification
     */
    protected $macProtected = ['title', 'description'];

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'description',
        'image_path',
        'is_featured',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'is_featured' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function likes()
    {
        return $this->hasMany(ArtworkLike::class, 'artwork_id');
    }

    public function isLikedByUser()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    public function scopeUnviewed($query)
    {
        return $query->whereNull('viewed_at');
    }
}