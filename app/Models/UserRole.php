<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRole extends Model
{
    protected $table = 'user_roles';
    public $incrementing = false; // karena PK komposit
    protected $primaryKey = null;

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    /**
     * Relasi ke user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
