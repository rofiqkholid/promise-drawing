<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $fillable = ['model_id','part_no','part_name'];
    public function modelRef(){ return $this->belongsTo(Models::class, 'model_id'); }
}
