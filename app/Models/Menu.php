<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * Get all of the children for the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        // Relasi ke model itu sendiri, diurutkan berdasarkan sort_order
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order', 'asc');
    }
}
