<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EncryptableFields;

class TwoFactorAuth extends Model
{
    use HasFactory, EncryptableFields;

    protected $encryptable = ['secret', 'backup_codes'];

    protected $table = 'two_factor_auths';

    protected $fillable = [
        'user_id',
        'secret',
        'enabled',
        'backup_codes',
        'last_verified_at',
        'first_verified_at',

    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_verified_at' => 'datetime',
        'first_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBackupCodes(): array
    {
        return json_decode($this->backup_codes, true) ?? [];
    }
}