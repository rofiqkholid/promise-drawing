<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel; // Alias untuk menghindari konflik nama
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocPackage extends EloquentModel
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Mendapatkan semua revisi untuk paket dokumen ini.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(DocPackageRevision::class, 'package_id');
    }

    /**
     * Mendapatkan customer pemilik paket.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Mendapatkan model produk dari paket.
     */
    public function model(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Model::class, 'model_id');
    }

    /**
     * Mendapatkan produk dari paket.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Mendapatkan grup dokumen dari paket.
     */
    public function doctypeGroup(): BelongsTo
    {
        return $this->belongsTo(DoctypeGroup::class);
    }

    /**
     * Mendapatkan grup part dari paket.
     */
    public function partGroup(): BelongsTo
    {
        return $this->belongsTo(PartGroup::class);
    }

    /**
     * Mendapatkan status proyek dari paket.
     */
    public function projectStatus(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class);
    }
}
