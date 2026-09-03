<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    
    // ============================================
    // PERSON 3: Added encryption traits
    // ============================================
    use EncryptableFields, IntegrityProtected;

    /**
     * PERSON 3: Define which fields need encryption
     */
    protected $encryptable = ['title', 'description', 'category', 'thumbnail'];
    
    /**
     * PERSON 3: Define which fields need MAC verification
     */
    protected $macProtected = ['title', 'description', 'category'];

    protected $fillable = ['title', 'description', 'category', 'thumbnail'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wishlistedBy()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function ratings()
    {
        return $this->hasMany(CourseRating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function totalReviews()
    {
        return $this->ratings()->count();
    }

    // ============================================
    // PERSON 3: Override save to handle encryption
    // ============================================
    public function save(array $options = [])
    {
        // Encrypt before saving
        $this->encryptFields();
        parent::save($options);
        // Generate MAC after saving with encrypted values
        $this->generateMac();
        parent::save($options);
    }
}