<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;
use App\Models\ProjectStatus;

class Models extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'name',
        'status_id',
        'planning',
    ];

    /**
     * Get the customer that owns the model.
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id');
    }
}
