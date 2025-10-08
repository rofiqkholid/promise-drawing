<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    protected $table = 'role_menu';

    // id sebagai PK (default Laravel), auto-increment (default)
    // protected $primaryKey = 'id';   // opsional, default sudah 'id'
    // public $incrementing = true;    // opsional, default true
    public $timestamps = false;        // TIDAK pakai created_at/updated_at

    protected $fillable = [
        'user_id',
        'menu_id',
        'can_view',
        'can_upload',
        'can_download',
        'can_delete',
    ];

    protected $casts = [
        'user_id'      => 'integer',
        'menu_id'      => 'integer',
        'can_view'     => 'integer',   // atau 'boolean' jika ingin true/false di PHP
        'can_upload'   => 'integer',
        'can_download' => 'integer',
        'can_delete'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
