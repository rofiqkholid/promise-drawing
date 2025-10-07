<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocTypeSubCategories extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctype_group_id',
        'name',
        'description',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'doctype_subcategories';

    /**
     * Get the document type group that owns the subcategory.
     */
    public function docTypeGroup()
    {
        return $this->belongsTo(DocTypeGroups::class, 'doctype_group_id');
    }
}
