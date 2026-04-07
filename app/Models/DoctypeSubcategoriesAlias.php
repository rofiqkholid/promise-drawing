<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctypeSubcategoriesAlias extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'doctypesubcategories_alias';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'doctypesubcategory_id',
        'name',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function docTypeSubCategory()
    {
        return $this->belongsTo(DocTypeSubCategories::class, 'doctypesubcategory_id');
    }
}
