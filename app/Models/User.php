<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\CustomHashService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    // ============================================
    // PERSON 3: Added encryption traits
    // ============================================
    use EncryptableFields, IntegrityProtected;

    /**
     * PERSON 3: Define which fields need encryption
     */
    protected $encryptable = ['name', 'email'];
    
    /**
     * PERSON 3: Define which fields need MAC verification
     */
    protected $macProtected = ['name', 'email'];

    protected $fillable = [
    'name',
    'email',
    'email_lookup',   // add this
    'password',
    'role',
];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    // ============================================
    // PERSON 2: Will handle password hashing
    // ============================================
    /**
     * PERSON 2: Replace this with custom password hashing
     * Instead of using built-in Hash::make()
     * 
     * @param string $password
     * @return void
     */
    // Replace the setPasswordAttribute method
    public function setPasswordAttribute($password)
    {
        // ============================================
        // PERSON 2: Custom password hashing from scratch
        // ============================================
        $this->attributes['password'] = app(CustomHashService::class)->make($password);
    }

    // Add method for password verification
    public function verifyPassword(string $password): bool
    {
        return app(CustomHashService::class)->check($password, $this->password);
    }
    // Relationships
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments');
    }

    public function viewedMaterials()
    {
        return $this->hasMany(ViewedMaterial::class);
    }

    public function courseRatings()
    {
        return $this->hasMany(CourseRating::class);
    }
}