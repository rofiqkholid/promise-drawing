<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * Cast kolom agar bisa langsung digunakan.
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * [MODIFIKASI]
     * Ini adalah relasi many-to-many ke model User
     * melalui tabel pivot 'user_supplier' sesuai ERD Anda.
     *
     * Hapus fungsi userLinkLists() yang lama dan ganti dengan ini.
     */

    // ...
    public function users()
    {
        // Kita beritahu Laravel nama kolom yang benar
        return $this->belongsToMany(
            User::class,
            'user_supplier',
            'supplier_id',  // <-- Kunci asing untuk Suppliers (model ini)
            'user_id'       // <-- Kunci asing untuk User (model relasi)
        );
    }
    // ...
}
