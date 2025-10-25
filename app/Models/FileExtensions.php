<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileExtensions extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'icon',
        'is_viewer',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_extensions';
}
