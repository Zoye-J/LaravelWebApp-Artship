<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewedMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_material_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->belongsTo(CourseMaterial::class, 'course_material_id');
    }
}