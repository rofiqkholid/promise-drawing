<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Mapping ID sesuai database
    const ICT   = 1;
    const APV_1 = 8;
    const APV_2 = 9;
    const APV_3 = 10;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';
}
