<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLinkList extends Model
{
    protected $table = 'user_link_list';
    protected $fillable = ['supplier_id','name','email'];
}
