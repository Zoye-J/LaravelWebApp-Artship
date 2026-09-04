<?php

namespace App\Models;

use App\Traits\EncryptableFields;
//use App\Traits\IntegrityProtected;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Services\CustomHashService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    //use EncryptableFields, IntegrityProtected;

    protected $encryptable = ['name', 'email'];

    //protected $macProtected = ['name', 'email'];

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

    /**

     * 
     * @param string $password
     * @return void
     */
    // Replace the setPasswordAttribute method
    public function setPasswordAttribute($password)
    {
      
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