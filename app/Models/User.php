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
    use EncryptableFields, IntegrityProtected;

    protected $encryptable = ['name', 'email'];
    protected $macProtected = ['name', 'email'];

    protected $fillable = [
        'name',
        'email',
        'email_lookup',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'role' => 'user',
    ];

    /**
     * Check if a field is encryptable (PUBLIC method for views)
     */
    public function isFieldEncryptable(string $field): bool
    {
        return in_array($field, $this->encryptable);
    }

    /**
     * Set password attribute with custom hashing
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = app(CustomHashService::class)->make($password);
    }

    /**
     * Verify password using custom hash
     */
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