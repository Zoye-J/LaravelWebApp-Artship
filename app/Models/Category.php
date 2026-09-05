<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptableFields;
use App\Traits\IntegrityProtected;

class Category extends Model
{
    use HasFactory, EncryptableFields, IntegrityProtected;

    protected $encryptable = ['name'];
    protected $macProtected = ['name'];

    protected $fillable = ['name'];
}