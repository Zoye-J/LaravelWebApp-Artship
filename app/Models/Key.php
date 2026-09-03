<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    use HasFactory;

    protected $fillable = [
        'algorithm',
        'purpose',
        'key_size',
        'public_key',
        'private_key',
        'fingerprint',
        'version',
        'status',
        'rotated_from_id',
        'generated_by',
        'rotated_at',
    ];

    
    protected $hidden = ['private_key'];

    protected $casts = [
        'rotated_at' => 'datetime',
    ];

    public function rotatedFrom()
    {
        return $this->belongsTo(Key::class, 'rotated_from_id');
    }

    public function rotatedTo()
    {
        return $this->hasOne(Key::class, 'rotated_from_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPurpose($query, string $algorithm, string $purpose)
    {
        return $query->where('algorithm', $algorithm)->where('purpose', $purpose);
    }
}
