<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartGroups extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_id',
        'customer_id',
        'planning',
        'code_part_group',
        'code_part_group_desc',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'part_groups';

    /**
     * Get the customer that owns the part group.
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    /**
     * Get the model that owns the part group.
     */
    public function model()
    {
        return $this->belongsTo(Models::class, 'model_id');
    }
}
