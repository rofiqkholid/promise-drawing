<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageFormat extends Model
{
    protected $table = 'pkg_formats';
    protected $fillable = ['prefix','suffix','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
