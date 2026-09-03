<?php

namespace App\Models;

use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
    public function setPasswordAttribute($password)
    {
        // ============================================
        // PERSON 2: Implement custom password hashing here
        // Replace Hash::make() with your implementation
        // Example: $this->attributes['password'] = CustomHash::make($password);
        // ============================================
        $this->attributes['password'] = \Hash::make($password);
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