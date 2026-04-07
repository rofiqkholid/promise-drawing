<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartGroupMaster extends Model
{
    use HasFactory;

    protected $table = 'part_group_master';
    protected $fillable = ['code', 'description'];
}
