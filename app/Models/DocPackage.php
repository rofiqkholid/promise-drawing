<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class DocPackage extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        // Semua query DocPackage::… hanya ambil yang is_delete = 0
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_delete', 0);
        });
    }

    // Soft delete: set is_delete = 1
    public function delete()
    {
        if (! $this->exists) {
            return false;
        }

        $this->is_delete = 1;
        return $this->save();
    }

    // Hard delete beneran (kalau suatu saat butuh)
    public function realDelete()
    {
        return static::withoutGlobalScope('not_deleted')
            ->whereKey($this->getKey())
            ->delete();
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(DocPackageRevision::class, 'package_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Models::class, 'model_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Products::class);
    }

    public function doctypeGroup(): BelongsTo
    {
        return $this->belongsTo(DoctypeGroups::class);
    }

    public function partGroup(): BelongsTo
    {
        return $this->belongsTo(PartGroups::class);
    }

    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class);
    }
}
