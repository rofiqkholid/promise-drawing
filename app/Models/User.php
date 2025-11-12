<?php

namespace App\Models;

use App\Models\Suppliers; // <-- TAMBAHKAN BARIS INI

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $casts = ['is_active' => 'integer'];


    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthIdentifierName()
    {
        return 'nik';
    }
    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps(); // created_at & updated_at di pivot otomatis
    }
    public function suppliers()
    {
        return $this->belongsToMany(
            Suppliers::class,
            'user_supplier', 
            'user_id',      
            'supplier_id'    
        );
    }
}
