<?php

namespace App\Models;

use App\Models\Suppliers;
use App\Models\Departments;
use App\Models\LastSeen;

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
        return $this->belongsToMany(\App\Models\Role::class, 'user_scope_roles', 'user_id', 'role_id')
            ->wherePivot('scope_id', 'app_drawing');
    }

    public function hasMenuPermissionById($menuId, $permissionColumn = 'can_view')
    {
        // ICT Role (ID = 1) is Super Admin in drawing
        if ($this->roles->contains('id', 1)) {
            return true;
        }

        $permissionMap = [
            'can_view' => 'view',
            'can_upload' => 'upload',
            'can_download' => 'download',
            'can_delete' => 'delete',
            'can_create' => 'create',
            'can_edit' => 'edit',
        ];
        $permName = $permissionMap[$permissionColumn] ?? 'view';

        // 1. Direct User Override Check
        $override = \DB::table('user_scope_permissions')
            ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
            ->where('user_id', $this->id)
            ->where('scope_id', 'app_drawing')
            ->where('menu_id', $menuId)
            ->where('permissions.permission_name', $permName)
            ->select('access_type')
            ->first();

        if ($override) {
            return $override->access_type === 'ALLOW';
        }

        // 2. Role-based Permission Check
        return \DB::table('user_scope_roles')
            ->join('role_scope_permissions', function($join) {
                $join->on('user_scope_roles.role_id', '=', 'role_scope_permissions.role_id')
                     ->on('user_scope_roles.scope_id', '=', 'role_scope_permissions.scope_id');
            })
            ->join('permissions', 'permissions.id', '=', 'role_scope_permissions.permission_id')
            ->where('user_scope_roles.user_id', $this->id)
            ->where('user_scope_roles.scope_id', 'app_drawing')
            ->where('role_scope_permissions.menu_id', $menuId)
            ->where('permissions.permission_name', $permName)
            ->exists();
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
    public function lastSeen()
    {
        return $this->hasOne(LastSeen::class);
    }
}
