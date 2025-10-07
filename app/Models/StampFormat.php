<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampFormat extends Model
{
    protected $table = 'stamp_formats';
    protected $fillable = ['prefix','suffix','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
