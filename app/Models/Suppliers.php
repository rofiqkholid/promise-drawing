<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    use HasFactory;

    /**
     * Nama tabel secara eksplisit.
     *
     * @var string
     */

    /**
     * Kolom yang boleh diisi secara massal.
     *
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
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

  public function userLinkLists()
{
    return $this->hasMany(\App\Models\UserLinkList::class, 'supplier_id');
}

}
