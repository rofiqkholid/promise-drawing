<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DwgProfile extends Model
{
    protected $table = 'dwg_profiles';

    protected $fillable = [
        'app_name',
        'app_description',
        'app_version',
        'app_logo',
        'logo_description',
    ];

    public $timestamps = true;
}
