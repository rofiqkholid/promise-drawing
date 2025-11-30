<?php

namespace App\Models;

use App\Models\Suppliers;
use App\Models\Departments; // <-- MODEL DEPARTMENT (SESUIKAN NAMANYA)

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $casts = [
        'is_active' => 'integer',
        'id_dept'   => 'integer', 
    ];

    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
        'is_active',
        'id_dept',   
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
            ->withTimestamps();
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

    // ==========================
    // Relasi ke Department
    // ==========================
    public function department()
    {
        // PARAM 2 = kolom FK di tabel users
        // PARAM 3 = kolom PK di tabel departments
        return $this->belongsTo(Departments::class, 'id_dept', 'id');
        // kalau PK di tabel departments namanya 'id', ganti jadi:
        // return $this->belongsTo(Departments::class, 'id_dept', 'id');
    }
}
