<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLinkList extends Model
{
    protected $table = 'user_supplier';
    protected $fillable = ['supplier_id','user_id'];
}
