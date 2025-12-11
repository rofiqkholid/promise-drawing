<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'model_id',
        'part_no',
        'part_name',
        'group_id',
        'is_delete',
    ];

    protected $table = 'products';

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_delete', 0);
        });
    }

    // 🔧 INI YANG PENTING: override delete()
     public function delete()
    {
        if (! $this->exists) {
            return false;
        }

        // 1) Soft delete semua doc_packages yang pakai product ini
        DB::table('doc_packages')
            ->where('product_id', $this->id)
            ->where('is_delete', 0)
            ->update([
                'is_delete'  => 1,
                'updated_at' => now(),
            ]);

        // 2) Soft delete product-nya sendiri
        $this->is_delete = 1;
        return $this->save();
    }

    public function realDelete()
    {
        return static::withoutGlobalScope('not_deleted')
            ->where('id', $this->id)
            ->delete();
    }

   

    public function model()
    {
        return $this->belongsTo(Models::class, 'model_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }
}
